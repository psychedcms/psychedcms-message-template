<?php

declare(strict_types=1);

namespace PsychedCms\MessageTemplate\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PsychedCms\MessageTemplate\Entity\MessageTemplate;

/**
 * @extends ServiceEntityRepository<MessageTemplate>
 */
class MessageTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageTemplate::class);
    }

    public function findByKey(string $key): ?MessageTemplate
    {
        return $this->findOneBy(['key' => $key]);
    }
}
