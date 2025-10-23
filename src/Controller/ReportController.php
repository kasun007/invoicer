<?php

namespace App\Controller;

use App\Repository\InvoiceRepository;
use App\Repository\CustomerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/reports', name: 'api_reports_')]
class ReportController extends AbstractController
{
    public function __construct(
        private InvoiceRepository $invoiceRepository,
        private CustomerRepository $customerRepository
    ) {}

    #[Route('/summary', name: 'summary', methods: ['GET'])]
    public function summary(): JsonResponse
    {
        // Get total invoices count
        $totalInvoices = $this->invoiceRepository->count([]);

        // Get total revenue
        $totalRevenue = $this->invoiceRepository->createQueryBuilder('i')
            ->select('SUM(i.totalAmount)')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        // Get overdue invoices
        $overdueInvoices = $this->invoiceRepository->createQueryBuilder('i')
            ->where('i.dueDate < :today')
            ->andWhere('i.status != :paid')
            ->setParameter('today', new \DateTime())
            ->setParameter('paid', 'paid')
            ->getQuery()
            ->getResult();

        $overdueCount = count($overdueInvoices);
        $overdueAmount = array_reduce($overdueInvoices, function($sum, $invoice) {
            return $sum + $invoice->getTotalAmount();
        }, 0);

        // Get invoices by status
        $statusCounts = $this->invoiceRepository->createQueryBuilder('i')
            ->select('i.status, COUNT(i.id) as count')
            ->groupBy('i.status')
            ->getQuery()
            ->getResult();

        $statusSummary = [];
        foreach ($statusCounts as $status) {
            $statusSummary[$status['status']] = (int)$status['count'];
        }

        // Get total customers
        $totalCustomers = $this->customerRepository->count([]);

        return $this->json([
            'totalInvoices' => $totalInvoices,
            'totalRevenue' => (float)$totalRevenue,
            'overdueInvoices' => [
                'count' => $overdueCount,
                'totalAmount' => (float)$overdueAmount
            ],
            'invoicesByStatus' => $statusSummary,
            'totalCustomers' => $totalCustomers,
            'generatedAt' => (new \DateTime())->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('/all-invoices', name: 'all_invoices', methods: ['GET'])]
    public function allInvoices(): JsonResponse
    {
        $invoices = $this->invoiceRepository->findAll();

        $data = [];
        foreach ($invoices as $invoice) {
            $items = [];
            foreach ($invoice->getItems() as $item) {
                $items[] = [
                    'description' => $item->getDescription(),
                    'quantity' => (float)$item->getQuantity(),
                    'unitPrice' => (float)$item->getUnitPrice(),
                    'lineTotal' => (float)$item->getLineTotal()
                ];
            }

            $data[] = [
                'id' => $invoice->getId(),
                'invoiceNumber' => $invoice->getInvoiceNumber(),
                'customer' => $invoice->getCustomer() ? [
                    'id' => $invoice->getCustomer()->getId(),
                    'name' => $invoice->getCustomer()->getName(),
                    'email' => $invoice->getCustomer()->getEmail()
                ] : null,
                'issueDate' => $invoice->getIssueDate()?->format('Y-m-d'),
                'dueDate' => $invoice->getDueDate()?->format('Y-m-d'),
                'status' => $invoice->getStatus(),
                'currency' => $invoice->getCurrency(),
                'subtotal' => (float)$invoice->getSubtotal(),
                'taxRate' => (float)$invoice->getTaxRate(),
                'taxAmount' => (float)$invoice->getTaxAmount(),
                'discountAmount' => (float)$invoice->getDiscountAmount(),
                'totalAmount' => (float)$invoice->getTotalAmount(),
                'notes' => $invoice->getNotes(),
                'items' => $items,
                'createdAt' => $invoice->getCreatedAt()?->format('Y-m-d H:i:s'),
                'updatedAt' => $invoice->getUpdatedAt()?->format('Y-m-d H:i:s')
            ];
        }

        return $this->json($data);
    }
}
