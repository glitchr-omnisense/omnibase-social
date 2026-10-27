---
title: Accounts and the wall
order: 31
---

# Accounts and the wall

## The accounts

`Base\Social\Service\Accounts` keeps the site's accounts in the core's settings, in the vault
when the setting bag can (`SettingBag::secure()`), as the back office's API keys are:

| Setting | Holds |
|---|---|
| `api.social.<provider>.token` | the token, an `Omnipost\Model\Token::toArray()` as JSON |
| `api.social.<provider>.account` | who it is: id, username, name, url, picture, followers, posts, kind, when read |
| `api.social.<provider>.api_key` | a reading key, for the providers that take one (YouTube's Data API key) |

- `names()` - `social.providers` that omnipost declares, in order; `has($name)`.
- `provider($name)` - omnipost's `Registry::create()` with the settings as overrides:
  `access_token` (left out when dead with a refresh token at hand: the provider draws a new
  one), `refresh_token`, `api_key`.
- `connected($name)` - a token is kept that is alive or refreshable, or omnipost's options carry
  one.
- `saveToken()` keeps the old refresh token, scopes and account id when the new token lacks them;
  `refresh($name)` asks a refreshable provider for a new token and keeps it; `disconnect()`
  forgets the token and the account.

### Connecting a network

On `/admin/social` (`social_admin_accounts`), "connect" (POST, token `social-connect-<provider>`)
keeps a random state in the session and sends the administrator to the network's consent page
(`OAuthInterface::authorizationUrl()`). Back at `/connect/social/{provider}`
(`social_oauth_callback`), a matching state and a code are exchanged for the token, kept; a
provider with a feed then reads its account. A refused or forged answer is a flash message.

Instagram, for one artist's account: a professional account (creator or business), and a Meta
app in development mode with the artist added as an Instagram Tester - no App Review is needed
for an account of the app's own.

## The feed and the wall

`Base\Social\Service\FeedSync::sync(string $provider, ?int $limit = null): int` reads the account
(saved) and its last posts (`social.feed.limit`, 24) through a provider with a feed
(`FeedInterface`; another is an `InvalidConfigException`): new ones added, known ones updated
(caption, likes, comments, the network's fresh addresses), the thumbnail - the film's still, or
the picture - copied once into the storage when `social.feed.cache_thumbnails` (only an answer
of type `image/*`; a picture that does not answer costs the row its thumbnail and nothing more).

## Commands

| Command | Options | What it does |
|---|---|---|
| `social:sync [provider]` | `--limit`, `-l` | reads the feeds of every provider that has one, or one; an unknown provider is an error; fails (exit 1) when one failed |
| `social:refresh-tokens` | `--days=10`, `--force`, `-f` | refreshes the refreshable providers' tokens that die within that many days (Instagram's live sixty and are refreshed only while alive), or every one |

## Twig functions

`Base\Social\Twig\SocialExtension`:

| Function | Renders |
|---|---|
| `social_wall(limit = 12, provider = null)` | the account's last posts in a grid (`@Social/client/_wall.html.twig`): the local thumbnail, a film played muted on hover from the network's address while it answers, each opening the post on the network; hidden posts left out |
| `social_embed(url, consent = false)` | any public post: an Instagram post or reel (Instagram's own embed), a YouTube film (youtube-nocookie). Nothing reaches the network before a click - or at once with `consent` true, or once a `social:consent` event is dispatched on `document` |
| `social_connected(provider)` | whether the account is connected |

`SocialExtension::recognize(string $url)` reads an address - `instagram.com/<user>/p|reel|reels|tv/<id>`
or a YouTube watch, shorts, embed, live or `youtu.be` address - into `{network, permalink, id}`:

```php
use Base\Social\Entity\SocialPost;
use Base\Social\Twig\SocialExtension;

print_r(SocialPost::cleanTags(['#violin', ' bach ', 'violin', '']));
print_r(SocialExtension::recognize('https://www.instagram.com/clara/reels/C8xYz12AbC/?igsh=1'));
```

```
Array
(
    [0] => violin
    [1] => bach
)
Array
(
    [network] => instagram
    [permalink] => https://www.instagram.com/reel/C8xYz12AbC/
    [id] => C8xYz12AbC
)
```

The look is `bundles/social/css/social.css`, on the host's `--social-accent`, `--social-soft`,
`--social-surface`, `--social-radius`, `--social-gap`, `--social-columns`,
`--social-video-ratio`, `--social-embed-width` and `--social-embed-video-width`. Translations:
the domain `social` (ICU), in English, French and German.
