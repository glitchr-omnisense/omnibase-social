<?php

namespace Base\Social\Repository;

use Base\Social\Entity\SocialPost;
use Base\Database\Repository\Finders;
use Base\Database\Repository\ServiceEntityRepository;

/**
 * @method SocialPost|null find($id, $lockMode = null, $lockVersion = null)
 * @method SocialPost|null findOneBy(array $criteria, ?array $orderBy = null)
 * @method SocialPost[]    findAll()
 * @method SocialPost[]    findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
 */
class SocialPostRepository extends ServiceEntityRepository
{
    public function __construct(Finders $finders)
    {
        parent::__construct($finders, SocialPost::class);
    }
}
