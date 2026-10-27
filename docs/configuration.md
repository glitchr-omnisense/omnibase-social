---
title: Configuration reference
order: 40
---

# Configuration reference

## `social:`

`Base\Social\DependencyInjection\SocialConfiguration`, dumped with Symfony's
`YamlReferenceDumper` in omnisense/omnibase's harness image:

```yaml
social:

    # The omnipost providers the site uses, by name, in the order the back office lists them.
    providers:

        # Default:
        - instagram
    ffmpeg:

        # The ffmpeg binary: a name on the PATH, or a path.
        binary:               ffmpeg

        # The ffprobe binary.
        ffprobe:              ffprobe

        # x264 quality: lower is finer and heavier.
        crf:                  20

        # Frames per second of a rendered reel.
        fps:                  30

        # Seconds a rendering may take.
        timeout:              900

        # The band's font when the template has none.
        font:                 /usr/share/fonts/truetype/dejavu/DejaVuSans.ttf

    # Where the rendered reels and their covers are kept: <storage>/<post id>/<platform>.mp4.
    storage:              '%kernel.project_dir%/var/storage/social'
    feed:

        # Posts read from the account at each sync.
        limit:                24

        # A local copy of each thumbnail: the networks' own addresses expire.
        cache_thumbnails:     true

    # Seconds the public address of a rendered file still answers once its publication is over.
    media_url_ttl:        86400
    check:

        # Seconds between two looks at a publication the network is still processing.
        delay:                30

        # Looks before giving up.
        attempts:             20
```

| Option | Type | Default | Read by |
|---|---|---|---|
| `providers` | list of omnipost provider names | `[instagram]` | `Accounts`, `TargetType` (only those omnipost declares are used) |
| `ffmpeg.binary`, `ffmpeg.ffprobe` | string | `ffmpeg`, `ffprobe` | `Renderer` (a name on the PATH, or a path) |
| `ffmpeg.crf` | int, 0 to 51 | `20` | `Renderer` |
| `ffmpeg.fps` | int, 1 to 60 | `30` | `Renderer` |
| `ffmpeg.timeout` | int, 10 or more | `900` | `Renderer`, seconds per process |
| `ffmpeg.font` | path | DejaVu Sans | `Renderer` (used when the file exists) |
| `storage` | path | `%kernel.project_dir%/var/storage/social` | `Renderer` |
| `feed.limit` | int, 1 to 100 | `24` | `FeedSync` |
| `feed.cache_thumbnails` | bool | `true` | `FeedSync` |
| `media_url_ttl` | int, 60 or more | `86400` | `MediaController` |
| `check.delay` | int, 1 or more | `30` | `Publisher` |
| `check.attempts` | int, 1 or more | `20` | `Publisher` |

Parameters: one per key and level (`social.providers`, `social.ffmpeg.binary`,
`social.check.delay`...).

## Services

`config/services.php` autowires and autoconfigures every class of `src/` but
`DependencyInjection/`, `Entity/`, `Enum/`, `Exception/`, `Message/`, `Model/`,
`Controller/Admin/`, `Admin/` and the bundle class; `Controller/Client/` is tagged
`controller.service_arguments`; with omnibase/admin, `Controller/Admin/` and `Admin/`. The
three handlers are `#[AsMessageHandler]`, the two commands `#[AsCommand]`. `Accounts` needs
omnipost's `Registry` (its bundle registered) and the core's `SettingBagInterface`; `Publisher`
omnipost's `Validator`.
