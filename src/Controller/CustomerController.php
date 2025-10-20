<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/customers', name: 'api_customers_')]
class CustomerController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {}

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return $this->json(['error' => 'Invalid JSON data'], 400);
        }

        // Validate required fields
        if (!isset($data['name']) || !isset($data['email'])) {
            return $this->json(['error' => 'Name and email are required'], 400);
        }

        // Check if customer with this email already exists
        $existingCustomer = $this->entityManager->getRepository(Customer::class)
            ->findOneBy(['email' => $data['email']]);

        if ($existingCustomer) {
            return $this->json(['error' => 'Customer with this email already exists'], 409);
        }

        // Create new customer
        $customer = new Customer();
        $customer->setName($data['name']);
        $customer->setEmail($data['email']);

        // Validate the entity
        $errors = $this->validator->validate($customer);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Customer created successfully',
            'customer' => [
                'id' => $customer->getId(),
                'name' => $customer->getName(),
                'email' => $customer->getEmail(),
                'createdAt' => $customer->getCreatedAt()->format('Y-m-d H:i:s')
            ]
        ], 201);
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(CustomerRepository $customerRepository): JsonResponse
    {
        $customers = $customerRepository->findAll();

        $data = array_map(fn($customer) => [
            'id' => $customer->getId(),
            'name' => $customer->getName(),
            'email' => $customer->getEmail(),
            'createdAt' => $customer->getCreatedAt()->format('Y-m-d H:i:s')
        ], $customers);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id, CustomerRepository $customerRepository): JsonResponse
    {
        $customer = $customerRepository->find($id);

        if (!$customer) {
            return $this->json(['error' => 'Customer not found'], 404);
        }

        return $this->json([
            'id' => $customer->getId(),
            'name' => $customer->getName(),
            'email' => $customer->getEmail(),
            'createdAt' => $customer->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, CustomerRepository $customerRepository): JsonResponse
    {
        $customer = $customerRepository->find($id);

        if (!$customer) {
            return $this->json(['error' => 'Customer not found'], 404);
        }

        $this->entityManager->remove($customer);
        $this->entityManager->flush();

        return $this->json(['message' => 'Customer deleted successfully']);
    }
}
