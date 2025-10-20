<?php

namespace App\Repository;

use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Invoice>
 *
 * @method Invoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invoice[]    findAll()
 * @method Invoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    /**
     * Find invoices by status
     *
     * @param string $status
     * @return Invoice[]
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.status = :status')
            ->setParameter('status', $status)
            ->orderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find invoices by customer
     *
     * @param int $customerId
     * @return Invoice[]
     */
    public function findByCustomer(int $customerId): array
    {
        return $this->createQueryBuilder('i')
            ->join('i.customer', 'c')
            ->andWhere('c.id = :customerId')
            ->setParameter('customerId', $customerId)
            ->orderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find overdue invoices
     *
     * @return Invoice[]
     */
    public function findOverdueInvoices(): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.dueDate < :today')
            ->andWhere('i.status != :paid')
            ->andWhere('i.status != :cancelled')
            ->setParameter('today', new \DateTime())
            ->setParameter('paid', 'paid')
            ->setParameter('cancelled', 'cancelled')
            ->orderBy('i.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Generate next invoice number
     */
    public function generateInvoiceNumber(): string
    {
        $lastInvoice = $this->createQueryBuilder('i')
            ->orderBy('i.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$lastInvoice) {
            return 'INV-0001';
        }

        $lastNumber = $lastInvoice->getInvoiceNumber();
        $number = (int) substr($lastNumber, 4);
        $nextNumber = $number + 1;

        return 'INV-' . str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
