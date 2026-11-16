<?php

namespace Base\Social\Repository;

use Base\Social\Entity\SocialMedia;
use Base\Database\Repository\Finders;
use Base\Database\Repository\ServiceEntityRepository;

/**
 * @method SocialMedia|null find($id, $lockMode = null, $lockVersion = null)
 * @method SocialMedia|null findOneBy(array $criteria, ?array $orderBy = null)
 * @method SocialMedia[]    findAll()
 * @method SocialMedia[]    findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
 * @method mixed            maxOfFetchedAtBy(array $criteria)      the finder language: MAX(fetchedAt), null without rows
 */
class SocialMediaRepository extends ServiceEntityRepository
{
    public function __construct(Finders $finders)
    {
        parent::__construct($finders, SocialMedia::class);
    }

    /**
     * The wall: the account's last posts, the hidden ones left out - of one
     * provider, or of all.
     *
     * @return list<SocialMedia>
     */
    public function findWall(int $limit = 12, ?string $provider = null): array
    {
        $criteria = ['hidden' => false];
        if (null !== $provider) {
            $criteria['provider'] = $provider;
        }

        return $this->findBy($criteria, ['publishedAt' => 'DESC'], $limit);
    }

    public function findOneRemote(string $provider, string $remoteId): ?SocialMedia
    {
        return $this->findOneBy(['provider' => $provider, 'remoteId' => $remoteId]);
    }

    /** When the provider's feed was last read; null: never. */
    public function lastSync(string $provider): ?\DateTimeInterface
    {
        $at = $this->maxOfFetchedAtBy(['provider' => $provider]);
        if ($at instanceof \DateTimeInterface) {
            return \DateTimeImmutable::createFromInterface($at);
        }

        return $at ? new \DateTimeImmutable((string) $at) : null;
    }

    public function countFor(string $provider): int
    {
        return $this->count(['provider' => $provider]);
    }
}
