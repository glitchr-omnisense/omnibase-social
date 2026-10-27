# omnibase/social

Social publishing for the sites built on [omnisense/omnibase](https://github.com/glitchr-omnisense/omnibase):
a reel made from the site's own template - its logo, a band with a name on it, its colours,
its font, a clip before and after - sent to each network with what it says there, and the
account's feed kept for a wall on the site. It is a Symfony bundle (`Base\Social\SocialBundle`)
on the core 3 or 4; the networks are [omnisense/omnipost](https://github.com/glitchr-omnisense/omnipost)'s
providers (`instagram`, `youtube`...). This bundle knows the site - its templates, its posts,
its settings -, omnipost knows the networks.

A violinist's site posts a concert's excerpt to Instagram and YouTube from its back office; a
shop shows its last posts under its products.

The pipeline, carried by three Messenger messages:

1. **Render** - each network's film made by ffmpeg with its template (the network's own
   target's, else the post's, else the network's, else the site's), under
   `<social.storage>/<post id>/<platform>.mp4`, its cover beside it.
2. **Publish** - the post as that network receives it, checked by omnipost's `Validator`
   against the network's capabilities and the provider's own rules, then handed over; at the
   post's hour when it has one to come.
3. **Check** - the network's status read again until it says the post is online (its
   permalink kept) or why it is not.

## How this manual is organised

- [Installation](installation.md): requirements, the bundles, ffmpeg, the routes, Messenger,
  the default URI, the schema.
- [Posts, targets and templates](concepts.md): every entity and its states, the queries.
- [The pipeline](usage-publishing.md): rendering, the ffmpeg command, checking, publishing,
  the status checks, retries, the public address of a film.
- [Accounts and the wall](usage-accounts.md): connecting a network, the tokens in the vault,
  the feed, the commands, the Twig functions.
- [The back office](back-office.md): the screens, the preview, the accounts page, the tile.
- [Configuration reference](configuration.md): `social:`, the parameters, the services.
- [Testing](testing.md): the suite and what it checks.
