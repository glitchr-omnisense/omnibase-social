---
title: Installation
order: 10
---

# Installation

## Requirements

What `composer.json` requires: PHP 8.2 or later, `doctrine/orm` 2.15 or 3,
`omnisense/omnibase` 3.0 or 4.0, `omnibridge/symfony` 1.x, `omnibase/admin`,
`omnisense/omnipost` 1.x, and `symfony/config`, `dependency-injection`, `form`, `http-kernel`,
`security-bundle`, `process` and `messenger` 6, 7 or 8. Install the omnipost providers the site
publishes to (`omnipost/instagram`, `omnipost/youtube`...).

ffmpeg and ffprobe belong in the image of whatever renders - the Messenger worker, and the web
image for the preview's checks of a post not yet rendered - with a font for the band:

```dockerfile
RUN apt-get update && apt-get install -y --no-install-recommends ffmpeg fonts-dejavu-core && rm -rf /var/lib/apt/lists/*
# alpine: apk add --no-cache ffmpeg font-dejavu
```

Without them a rendering fails with a `RenderingException` saying so, shown on the target.

## The bundles

```bash
composer require omnibase/social:dev-main omnipost/instagram:1.x-dev
```

```php
// config/bundles.php
return [
    // ...
    Base\BaseBundle::class => ['all' => true],
    Base\Admin\AdminBundle::class => ['all' => true],
    Omnipost\Bridge\Symfony\OmnipostBundle::class => ['all' => true],
    Base\Social\SocialBundle::class => ['all' => true],
];
```

`SocialBundle` aliases `Base\Social\Entity\*` to `App\Entity\Social\*` and the repositories to
`App\Repository\Social\*` (the core's convention: an application declares its own class there to
take one over); its `templates/` is `@Social`, its `public/` `bundles/social/`.

omnipost's configuration holds the apps' ids and secrets; the tokens are the back office's:

```yaml
# config/packages/omnipost.yaml
omnipost:
    providers:
        instagram: { factory: instagram, options: { app_id: '%env(default::INSTAGRAM_APP_ID)%', app_secret: '%env(default::INSTAGRAM_APP_SECRET)%' } }
        youtube:   { factory: youtube, options: { client_id: '%env(default::YOUTUBE_CLIENT_ID)%', client_secret: '%env(default::YOUTUBE_CLIENT_SECRET)%', channel_id: '%env(default::YOUTUBE_CHANNEL_ID)%' } }
```

```yaml
# config/packages/social.yaml
social:
    providers: [instagram, youtube]
```

## The routes

```yaml
# config/routes.yaml
social_controller:
    resource: "@SocialBundle/src/Controller/Client"
    type: attribute
    prefix: /
social_admin:
    resource: "@SocialBundle/src/Controller/Admin/AccountsController.php"
    type: attribute
    prefix: /
```

The first imports `social_media` (the films' public address); the second the accounts page and
the OAuth callback `/connect/social/{provider}` (`social_oauth_callback`) - declare that address
in each network's app.

## Messenger

```yaml
# config/packages/messenger.yaml
framework:
    messenger:
        routing:
            Base\Social\Message\RenderPostMessage: async
            Base\Social\Message\PublishTargetMessage: async
            Base\Social\Message\CheckTargetMessage: async
```

A rendering takes a while and a network processes a reel for a minute or two. The `async`
transport must take delays (Doctrine, Redis and AMQP do; `sync://` runs the checks at once and
gives up early): scheduled posts and status checks are `DelayStamp`s.

## The site's public address

A worker has no request to take the host from: set `framework.router.default_uri` to the site's
public address, and keep `/social/media/` reachable from the internet (no access restriction,
maintenance or launch page on it) - Instagram comes for the film there.

## The schema and the public files

```bash
bin/console doctrine:migrations:diff
bin/console doctrine:migrations:migrate
bin/console assets:install
```

Tables `social_post`, `social_post_target`, `social_template`, `social_media`; the stylesheet
`bundles/social/css/social.css`.

## The crons

```cron
0 * * * *  bin/console social:sync            # the walls' posts, hourly
30 4 * * * bin/console social:refresh-tokens  # tokens that die within ten days
```
