<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Entity\InvoiceItem;
use App\Entity\Customer;
use App\Repository\InvoiceRepository;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/invoices', name: 'api_invoices_')]
class InvoiceController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, InvoiceRepository $invoiceRepository, CustomerRepository $customerRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Invalid JSON data'], 400);
        }

        // Validate required fields
        $requiredFields = ['issueDate', 'dueDate'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => "Field '$field' is required"], 400);
            }
        }

        // Find customer by ID or email
        $customer = null;
        if (isset($data['customerId'])) {
            $customer = $customerRepository->find($data['customerId']);
        } elseif (isset($data['customerEmail'])) {
            $customer = $customerRepository->findOneBy(['email' => $data['customerEmail']]);
        } else {
            return $this->json(['error' => 'Either customerId or customerEmail is required'], 400);
        }

        if (!$customer) {
            return $this->json(['error' => 'Customer not found'], 404);
        }

        // Create invoice
        $invoice = new Invoice();
        $invoice->setInvoiceNumber($invoiceRepository->generateInvoiceNumber());
        $invoice->setCustomer($customer);

        try {
            $invoice->setIssueDate(new \DateTime($data['issueDate']));
            $invoice->setDueDate(new \DateTime($data['dueDate']));
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid date format. Use YYYY-MM-DD'], 400);
        }

        // Set optional fields
        if (isset($data['status'])) {
            $invoice->setStatus($data['status']);
        }
        if (isset($data['currency'])) {
            $invoice->setCurrency($data['currency']);
        }
        if (isset($data['notes'])) {
            $invoice->setNotes($data['notes']);
        }
        if (isset($data['taxRate'])) {
            $invoice->setTaxRate($data['taxRate']);
        }
        if (isset($data['discountAmount'])) {
            $invoice->setDiscountAmount($data['discountAmount']);
        }

        // Add items if provided
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $itemData) {
                if (!isset($itemData['description'], $itemData['quantity'], $itemData['unitPrice'])) {
                    return $this->json(['error' => 'Each item must have description, quantity, and unitPrice'], 400);
                }

                $item = new InvoiceItem();
                $item->setDescription($itemData['description']);
                $item->setQuantity((int)$itemData['quantity']);
                $item->setUnitPrice($itemData['unitPrice']);

                if (isset($itemData['unit'])) {
                    $item->setUnit($itemData['unit']);
                }

                $invoice->addItem($item);
            }
        }

        // Calculate totals
        $invoice->calculateTotals();

        // Validate the entity
        $errors = $this->validator->validate($invoice);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        // Persist to database
        $this->entityManager->persist($invoice);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Invoice created successfully',
            'invoice' => $this->formatInvoiceResponse($invoice)
        ], 201);
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request, InvoiceRepository $invoiceRepository): JsonResponse
    {
        $status = $request->query->get('status');
        $customerId = $request->query->get('customerId');

        if ($status) {
            $invoices = $invoiceRepository->findByStatus($status);
        } elseif ($customerId) {
            $invoices = $invoiceRepository->findByCustomer((int)$customerId);
        } else {
            $invoices = $invoiceRepository->findBy([], ['createdAt' => 'DESC']);
        }

        $invoiceData = array_map(function($invoice) {
            return $this->formatInvoiceResponse($invoice, true);
        }, $invoices);

        return $this->json(['invoices' => $invoiceData]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id, InvoiceRepository $invoiceRepository): JsonResponse
    {
        $invoice = $invoiceRepository->find($id);

        if (!$invoice) {
            return $this->json(['error' => 'Invoice not found'], 404);
        }

        return $this->json([
            'invoice' => $this->formatInvoiceResponse($invoice, true)
        ]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request, InvoiceRepository $invoiceRepository): JsonResponse
    {
        $invoice = $invoiceRepository->find($id);

        if (!$invoice) {
            return $this->json(['error' => 'Invoice not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Invalid JSON data'], 400);
        }

        // Update fields if provided
        if (isset($data['status'])) {
            $invoice->setStatus($data['status']);
        }
        if (isset($data['issueDate'])) {
            try {
                $invoice->setIssueDate(new \DateTime($data['issueDate']));
            } catch (\Exception $e) {
                return $this->json(['error' => 'Invalid issue date format. Use YYYY-MM-DD'], 400);
            }
        }
        if (isset($data['dueDate'])) {
            try {
                $invoice->setDueDate(new \DateTime($data['dueDate']));
            } catch (\Exception $e) {
                return $this->json(['error' => 'Invalid due date format. Use YYYY-MM-DD'], 400);
            }
        }
        if (isset($data['notes'])) {
            $invoice->setNotes($data['notes']);
        }
        if (isset($data['taxRate'])) {
            $invoice->setTaxRate($data['taxRate']);
        }
        if (isset($data['discountAmount'])) {
            $invoice->setDiscountAmount($data['discountAmount']);
        }

        // Recalculate totals
        $invoice->calculateTotals();

        // Validate the entity
        $errors = $this->validator->validate($invoice);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        $this->entityManager->flush();

        return $this->json([
            'message' => 'Invoice updated successfully',
            'invoice' => $this->formatInvoiceResponse($invoice)
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, InvoiceRepository $invoiceRepository): JsonResponse
    {
        $invoice = $invoiceRepository->find($id);

        if (!$invoice) {
            return $this->json(['error' => 'Invoice not found'], 404);
        }

        $this->entityManager->remove($invoice);
        $this->entityManager->flush();

        return $this->json(['message' => 'Invoice deleted successfully']);
    }

    #[Route('/overdue', name: 'overdue', methods: ['GET'])]
    public function overdue(InvoiceRepository $invoiceRepository): JsonResponse
    {
        $invoices = $invoiceRepository->findOverdueInvoices();
        $invoiceData = array_map(function($invoice) {
            return $this->formatInvoiceResponse($invoice, true);
        }, $invoices);

        return $this->json(['invoices' => $invoiceData]);
    }

    private function formatInvoiceResponse(Invoice $invoice, bool $includeItems = false): array
    {
        $customer = $invoice->getCustomer();
        $data = [
            'id' => $invoice->getId(),
            'invoiceNumber' => $invoice->getInvoiceNumber(),
            'customer' => $customer ? [
                'id' => $customer->getId(),
                'name' => $customer->getName(),
                'email' => $customer->getEmail(),
            ] : null,
            'issueDate' => $invoice->getIssueDate()?->format('Y-m-d'),
            'dueDate' => $invoice->getDueDate()?->format('Y-m-d'),
            'status' => $invoice->getStatus(),
            'subtotal' => $invoice->getSubtotal(),
            'taxAmount' => $invoice->getTaxAmount(),
            'taxRate' => $invoice->getTaxRate(),
            'discountAmount' => $invoice->getDiscountAmount(),
            'totalAmount' => $invoice->getTotalAmount(),
            'currency' => $invoice->getCurrency(),
            'notes' => $invoice->getNotes(),
            'createdAt' => $invoice->getCreatedAt()?->format('Y-m-d H:i:s'),
            'updatedAt' => $invoice->getUpdatedAt()?->format('Y-m-d H:i:s'),
        ];

        if ($includeItems) {
            $data['items'] = [];
            foreach ($invoice->getItems() as $item) {
                $data['items'][] = [
                    'id' => $item->getId(),
                    'description' => $item->getDescription(),
                    'quantity' => $item->getQuantity(),
                    'unitPrice' => $item->getUnitPrice(),
                    'lineTotal' => $item->getLineTotal(),
                    'unit' => $item->getUnit(),
                ];
            }
        }

        return $data;
    }
}
