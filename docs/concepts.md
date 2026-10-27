---
title: Posts, targets and templates
order: 20
---

# Posts, targets and templates

## `SocialPost`

What goes out (`social_post`):

| Property | Column | Meaning |
|---|---|---|
| `video` | upload, video, 1 GB | the film as it was shot |
| `image` | upload, image, 8 MB | or a picture |
| `kind` | `string(16)`, `reel` | an omnipost `PostKind` value: `reel` or `image` in the back office |
| `caption` | `text` | |
| `title` | `string(255)` | for the networks that want one (YouTube) |
| `tags` | `json` | hashtags, kept without `#`, blanks or doubles (`SocialPost::cleanTags()`) |
| `template` | many-to-one `Template`, `SET NULL` | |
| `useTemplate` | `boolean`, `true` | off: the film goes out as it is, not encoded again |
| `coverSecond` | `float`, `0` | the frame the cover is taken at |
| `scheduledAt` | `datetime`, nullable | the hour it leaves; the site holds the message back until then |
| `link` | `string(255)` | a URL |
| `state` | `PostState` | read from its targets |
| `targets` | one-to-many `SocialPostTarget`, cascade, orphan removal | one per network |

`refreshState()`: no enabled target `DRAFT`; one sent or processing `PUBLISHING`; all published
`DONE`; one failed (and none on its way) `FAILED`; some published `PUBLISHING`; all rendered
`READY`; else `DRAFT`. `PostState` also has `RENDERING`, set while it renders.

`toPost(?callable $urls = null): Omnipost\Model\Post` gives omnipost's canonical post (the first
network's rendering as its media) and, per enabled network, a `Variant` with the target's own
caption, title, hashtags, rendering, cover and options. `$urls` gives each target's public
address (`fn (SocialPostTarget $target, bool $cover): string`); without it the local path stands
in - enough to validate, not to publish. The hour is not passed on to the networks.

## `SocialPostTarget`

A post on one network (`social_post_target`, unique on post and platform):

| Property | Meaning |
|---|---|
| `platform` | an omnipost `Platform` value - and the name of the provider that serves it |
| `enabled` | whether it goes there |
| `caption`, `title`, `tags`, `template` | what it says there when not what the post says; empty: the post's |
| `options` | the network's extras: `share_to_feed` (Instagram), `privacy` (`public`, `unlisted`, `private`: YouTube) |
| `renderedPath`, `coverPath`, `duration`, `width`, `height`, `size` | what the renderer made |
| `publicationId`, `permalink`, `publishedAt` | what the network said |
| `state` | `TargetState` |
| `error` | why it failed |
| `attempts` | looks at the network's status so far |
| `updatedAt` | when the state last moved |

`TargetState`: `PENDING`, `RENDERED`, `SENT`, `PROCESSING`, `PUBLISHED`, `FAILED`; `isFinal()`
(published or failed), `isInFlight()` (sent or processing). `rendered(Rendering)` records a
rendering and clears what a previous publication left; `fail(string)`;
`isMediaOpen(int $ttl = 86400)` - the public address answers while the publication is on its
way and `$ttl` seconds after it ended; `toMedia()` - the rendering as an omnipost `Media`.

## `Template`

The site's identity on a reel (`social_template`):

| Property | Default | Meaning |
|---|---|---|
| `name` | | not blank |
| `default` (`is_default`) | `false` | the site's own; saving one as default takes the flag off the others of the same platform |
| `platform` | `null` | a template for that network alone |
| `logo` | | a picture (8 MB) |
| `logoPosition` | `TOP_RIGHT` | `TOP_LEFT`, `TOP_RIGHT`, `BOTTOM_LEFT`, `BOTTOM_RIGHT`, `CENTER` |
| `logoScale` | `0.2` | the logo's width as a part of the frame's, 0.1 to 0.5 |
| `band` | `false` | a band across the lower third |
| `bandText` | | its text (the artist's name), 120 at most |
| `bandColor`, `textColor` | `#000000`, `#ffffff` | `#rrggbb` or `#rrggbbaa` |
| `font` | | a TTF or OTF (8 MB); none: `social.ffmpeg.font` |
| `background` | `#000000` | behind a film shown whole |
| `intro`, `outro` | | clips before and after (200 MB) |
| `fit` | `COVER` | `COVER`: cropped to fill 9:16; `CONTAIN`: whole, letterboxed |
| `fadeSeconds` | `0.5` | fades in and out, 0 to 5 |

`TemplateRepository::resolve(SocialPostTarget)` - the template a target is rendered with: none
when the post says "as it is", else the target's, the post's, the network's
(`findForPlatform()`), the site's (`findDefault()`: default with no platform, else any default).

## `SocialMedia`

One of the account's own posts as the last sync read it - what the wall shows
(`social_media`, unique on provider and remote id): `provider`, `remoteId`, `kind`, `permalink`,
`caption`, `publishedAt`, `thumbnail` (a local copy, 8 MB: the networks' own addresses expire),
`mediaUrl`, `videoUrl` (the network's, they may expire), `likes`, `comments`, `hidden` (kept off
the wall), `fetchedAt`.

`SocialMediaRepository`: `findWall(int $limit = 12, ?string $provider = null)` (not hidden,
newest first), `findOneRemote()`, `lastSync(string $provider)`, `countFor()`.
`SocialPostTargetRepository::findLastFor(string $platform)` - the last target that moved.
