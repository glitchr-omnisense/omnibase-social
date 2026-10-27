---
title: The back office
order: 38
---

# The back office

Loaded when omnibase/admin is installed; the CRUDs keep omnibase/admin's default permissions,
the accounts page is `ROLE_ADMIN`.

## Posts

`SocialPostCrudController` (slug `social-posts`): title, kind (reel or picture), state (read),
film, picture, caption, hashtags (typed, split on commas, spaces, semicolons), template (chosen
in a list), use the template, cover's second, hour, link, and the targets (`TargetType`: network,
enabled, caption, title, hashtags typed as a line, template - "the post's" when empty -,
Instagram's share to feed and YouTube's privacy). A new post gets every network of
`social.providers`, enabled. Filters: state, template.

Actions on the list, the record and the edit page:

- **Preview** (`/admin/social-posts/{entityId}/preview`) - each network's reel and cover (served
  by `/reel/{target}/video|cover`, whatever the state), its checks, its capabilities, its state,
  permalink and error, whether the account is connected, and a button to send one network again
  (`/retry/{target}`).
- **Render** - a `RenderPostMessage`; the worker renders.
- **Publish** (asked to confirm) - every enabled network checked first: one refusal and nothing
  leaves, the reasons listed; all rendered, the publish messages go (at the post's hour), else a
  rendering followed by the publication.

## Templates and the feed

`TemplateCrudController` (`templates`): name, default, network (one of `social.providers`), logo,
its corner and width, band, its text and colours, font, fit, background, fade, intro, outro.
Saving a default takes the flag off the other defaults of the same network.

`SocialMediaCrudController` (`social-medias`): the posts the last sync read, read-only but
`hidden`; **Show / hide** on the wall and **Open** the post on the network.

## The accounts page

`/admin/social`, in the admin's layout: per provider, the account connected (name, picture,
followers), when its token dies, how many posts are kept and when the feed was read, and the
buttons - connect, refresh the token, read the feed now, disconnect - and the reading key
(`api_key`; empty puts the configuration's back). Each is a POST with its token
(`social-<action>-<provider>`).

## The dashboard tile

`SocialWidgetType`, named `social_overview` (`@Social/admin/widget/overview.html.twig`): per
network, the account, the last target that moved and its state, when the feed was read, and a
word when a token without a refresh token dies within ten days.

```php
yield MenuItem::block('social_overview', 'Réseaux', 'fa-solid fa-share-nodes');
```
