<?php

namespace Base\Social\Tests\Controller;

use Base\Admin\Test\CrudFieldsTestCase;
use Base\Social\Controller\Admin\Crud\SocialPostCrudController;

/**
 * The fields marked required on this bundle's back office screens, against
 * what its records need (see CrudFieldsTestCase): only those the record
 * cannot do without, all of them, an optional one able to take an empty
 * value, one field per property, a slug following its title.
 */
final class CrudRequiredFieldsTest extends CrudFieldsTestCase
{
    protected static function sourceDirectory(): string
    {
        return __DIR__.'/../../src';
    }

    protected static function skipped(): array
    {
        // Required by the bundle, missing from omnibase's harness: read where it is installed.
        return class_exists('Omnipost\Model\PostKind') ? [] : [SocialPostCrudController::class => "a post's kinds are glitchr/omnipost's, absent from this installation"];
    }
}
