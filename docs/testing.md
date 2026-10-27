---
title: Testing
order: 60
---

# Testing

```bash
vendor/bin/phpunit                                                     # checked out with its dependencies
php vendor/bin/phpunit -c vendor/omnibase/social/phpunit.xml.dist      # from an application
```

The tests need no kernel. omnisense/omnibase's harness image does not install omnisense/omnipost:
there, its checkouts go on the autoloader with a prepended file that registers the PSR-4
namespaces of every `composer.json` found under `/fam/*/*/`:

```php
<?php
// prepend-families.php
$loader = require '/srv/demo/vendor/autoload.php';
foreach (glob('/fam/*/*/composer.json') ?: [] as $file) {
    $c = json_decode((string) file_get_contents($file), true) ?: [];
    foreach ($c['autoload']['psr-4'] ?? [] as $ns => $path) {
        if (!isset($loader->getPrefixesPsr4()[$ns])) {
            $loader->addPsr4($ns, dirname($file).'/'.trim((string) $path, './'));
        }
    }
}
```

```bash
docker run --rm --network none \
    -v "$PWD":/srv/demo/vendor/omnibase/social:ro -v ~/Sites/omnipost:/fam/omnipost:ro -v /path/to/tools:/tools:ro \
    --entrypoint sh omnisense/omnibase-harness -c \
    'cd /srv/demo && APP_ENV=test php -d auto_prepend_file=/tools/prepend-families.php vendor/bin/phpunit -c vendor/omnibase/social/phpunit.xml.dist --do-not-cache-result'
```

```
......................                                            22 / 22 (100%)

OK (22 tests, 153 assertions)
```

| File | What |
|---|---|
| `Service/RendererCommandTest` | a cropped film with logo, band and intro; a contained one with an outro, without sound or logo; a plain one has no concat and maps the dressed streams; the cover command and the helpers |
| `Service/MediaUrlsTest` | the token round-trips; the address carries the target, the token and the extension |
| `Entity/SocialPostTest` | `toPost()` builds the canonical post and a variant per network; `for()` applies a target's caption, title, hashtags and rendering; an empty override keeps the post's and a picture is an image; the state follows the networks; the file's address closes a day after the end |
| `Controller/Admin/CrudFormsTest` | the template's enums through `EnumType`, rendered and translated, named on its detail page; a post takes its targets as objects and chooses its template in a list |
| `Controller/CrudRequiredFieldsTest` | the CRUDs' required fields |
