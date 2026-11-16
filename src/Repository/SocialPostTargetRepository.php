<?php

namespace Base\Social\Repository;

use Base\Social\Entity\SocialPostTarget;
use Base\Database\Repository\Finder\Criterion;
use Base\Database\Repository\Finder\Group;
use Base\Database\Repository\Finder\Parser;
use Base\Database\Repository\Finders;
use Base\Database\Repository\ServiceEntityRepository;

/**
 * @method SocialPostTarget|null find($id, $lockMode = null, $lockVersion = null)
 * @method SocialPostTarget|null findOneBy(array $criteria, ?array $orderBy = null)
 * @method SocialPostTarget[]    findAll()
 * @method SocialPostTarget[]    findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
 */
class SocialPostTargetRepository extends ServiceEntityRepository
{
    public function __construct(Finders $finders)
    {
        parent::__construct($finders, SocialPostTarget::class);
    }

    /** The last target that left for a network, whatever became of it: the dashboard's line. */
    public function findLastFor(string $platform): ?SocialPostTarget
    {
        return $this->findOneBy([
            'platform' => $platform,
            'enabled' => true,
            Group::of(Parser::SEPARATOR_AND, Criterion::of('updatedAt', null, Parser::OPTION_NOT_NULL)),
        ], ['updatedAt' => 'DESC']);
    }
}
