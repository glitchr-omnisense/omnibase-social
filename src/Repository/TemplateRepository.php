<?php

namespace Base\Social\Repository;

use Base\Social\Entity\SocialPostTarget;
use Base\Social\Entity\Template;
use Base\Database\Repository\Finder\Criterion;
use Base\Database\Repository\Finder\Group;
use Base\Database\Repository\Finder\Parser;
use Base\Database\Repository\Finders;
use Base\Database\Repository\ServiceEntityRepository;

/**
 * @method Template|null find($id, $lockMode = null, $lockVersion = null)
 * @method Template|null findOneBy(array $criteria, ?array $orderBy = null)
 * @method Template[]    findAll()
 * @method Template[]    findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
 */
class TemplateRepository extends ServiceEntityRepository
{
    public function __construct(Finders $finders)
    {
        parent::__construct($finders, Template::class);
    }

    /** The site's own template, if it has one. */
    public function findDefault(): ?Template
    {
        return $this->findOneBy(['default' => true, Group::of(Parser::SEPARATOR_AND, Criterion::of('platform', null, Parser::OPTION_NULL))], ['id' => 'ASC'])
            ?? $this->findOneBy(['default' => true], ['id' => 'ASC']);
    }

    /** The template kept for one network alone. */
    public function findForPlatform(string $platform): ?Template
    {
        return $this->findOneBy(['platform' => $platform], ['default' => 'DESC', 'id' => 'ASC']);
    }

    /**
     * The template a target is rendered with: its own, else its post's, else
     * its network's, else the site's. Null: the post goes out untemplated
     * (it says so, or the site has no template yet).
     */
    public function resolve(SocialPostTarget $target): ?Template
    {
        $post = $target->getPost();
        if (null === $post || !$post->isUseTemplate()) {
            return null;
        }

        return $target->getTemplate()
            ?? $post->getTemplate()
            ?? $this->findForPlatform((string) $target->getPlatform())
            ?? $this->findDefault();
    }
}
