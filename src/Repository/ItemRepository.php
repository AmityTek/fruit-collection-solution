<?php

namespace App\Repository;

use App\Entity\Item;
use App\Enum\ItemType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    /**
     * Save or update an Item in the database.
     */
    public function save(Item $item): void
    {
        $this->getEntityManager()->persist($item);
        $this->getEntityManager()->flush();
    }

    /**
     * Remove an Item from the database.
     */
    public function remove(Item $item): void
    {
        $this->getEntityManager()->remove($item);
        $this->getEntityManager()->flush();
    }

    /**
     * Find all items of a specific type (e.g., fruits or vegetables).
     */
    public function findByType(ItemType $type): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.type = :type')
            ->setParameter('type', $type->value)
            ->orderBy('i.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search for items by name (case-insensitive).
     */
    public function searchByName(string $searchTerm): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('LOWER(i.name) LIKE LOWER(:searchTerm)')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('i.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
