---
title: The pipeline
order: 30
---

# The pipeline

`Base\Social\Service\Publisher` holds the logic; the three handlers only call it.

## Rendering

`RenderPostMessage($postId, $publish = false)` → `Publisher::render()`: the post `RENDERING`,
then each enabled target not yet published rendered by `Base\Social\Service\Renderer::render()`
with `TemplateRepository::resolve($target)`. A rendering that fails (`RenderingException`) fails
its network alone, the message kept as its error. The post's state is then read from its
targets; with `$publish`, the rendered targets are handed over.

`Renderer::render(SocialPost $post, ?Template $template = null, ?Platform $platform = null): Rendering`:

- a picture is measured (ffprobe) and given back as it is;
- without a template (the post says "as it is", or the site has none) the film is measured and
  given back - no second encoding -, its cover taken;
- otherwise ffmpeg makes `<storage>/<post id>/<platform>.mp4` and its cover
  `<platform>.jpg` (the frame at `coverSecond`, at most 0.1 s before the end). A cover that fails
  does not stop the film (the network takes a frame itself) unless ffmpeg is missing.

`probe()` reads ffprobe's JSON (`Renderer::measures()`): the first video stream's size - turned
for a phone's portrait film stored lying down (rotation 90 or 270) -, the length, the weight,
whether there is sound, the MIME type.

### The ffmpeg command

`Renderer::command(array $input): list<string>` only assembles the arguments - no file is
touched - so the filter graph is read and tested as it is. One `-filter_complex`:

- the source to 1080 x 1920: `COVER` scales up and crops, `CONTAIN` scales down and pads on the
  background; `setsar=1`, `fps=<social.ffmpeg.fps>`;
- the logo scaled to its part of the width (even), laid with `overlay` 64 px from the edges;
- the band: `drawbox` at y 1380, 140 px high, then `drawtext` centred, `expansion=none`, the
  template's font or `font=Sans`, size 56 shrinking for a long text (28 at least,
  `Renderer::fontSize()`); texts and paths escaped for the option and the graph
  (`Renderer::escape()`);
- `fade` and `afade` in and out (none when longer than half the film); a film without sound
  gets a silence as long as it;
- the intro and outro brought to the same frame, rate and sound, joined with `concat`;
- H.264 High 4.1, yuv420p, `-r <fps>`, `-crf <social.ffmpeg.crf>`, AAC 160k at 48 kHz,
  `+faststart` - what Instagram, YouTube, Facebook and TikTok all take.

The filter graph of a cropped film with a logo, a band and fades, run in omnisense/omnibase's
harness image (`new Renderer()`, its defaults):

```php
use Base\Social\Enum\Fit;
use Base\Social\Enum\LogoPosition;
use Base\Social\Service\Renderer;

$command = (new Renderer())->command([
    'source' => '/in.mov', 'output' => '/out.mp4', 'duration' => 12.0,
    'fit' => Fit::COVER, 'logo' => '/logo.png', 'logoPosition' => LogoPosition::TOP_RIGHT, 'logoScale' => 0.2,
    'band' => true, 'bandText' => 'Clara Weiss: harp', 'fade' => 0.5,
]);
$i = array_search('-filter_complex', $command, true);
echo str_replace(';', ";\n", $command[$i + 1]), "\n";
echo implode(' ', array_slice($command, $i + 2)), "\n";
```

```
[0:v]scale=1080:1920:force_original_aspect_ratio=increase,crop=1080:1920,setsar=1,fps=30[base];
[1:v]scale=216:-1[logo];
[base][logo]overlay=W-w-64:64[logod];
[logod]drawbox=x=0:y=1380:w=1080:h=140:color=0x000000:t=fill,drawtext=font=Sans:text=Clara Weiss\\: harp:expansion=none:fontcolor=0xffffff:fontsize=56:x=(w-text_w)/2:y=1380+(140-text_h)/2,fade=t=in:st=0:d=0.5,fade=t=out:st=11.5:d=0.5,format=yuv420p[v];
[0:a]aresample=48000,aformat=sample_fmts=fltp:channel_layouts=stereo,afade=t=in:st=0:d=0.5,afade=t=out:st=11.5:d=0.5[a]
-map [v] -map [a] -c:v libx264 -profile:v high -level 4.1 -pix_fmt yuv420p -r 30 -crf 20 -c:a aac -b:a 160k -ar 48000 -movflags +faststart /out.mp4
```

## Checking

`Publisher::validate(SocialPostTarget $target): list<Omnipost\Model\Violation>` - what the
network would refuse, before anything is sent: the post as that network receives it
(`SocialPost::toPost()->for($platform)`) against the provider's `capabilities()`
(omnipost's `Validator`), and against the provider's own `validate()` when it has one (a reel is
9:16 on Instagram). A target not rendered yet is checked as it will be: the source's measures
and length, the template's 1080 x 1920 frame. A provider that cannot be built, or does not
publish, is a violation. The same complaint is given once.

## Publishing

`Publisher::dispatchPublish(SocialPost $post)` sends a `PublishTargetMessage` per rendered target,
with a `DelayStamp` up to `scheduledAt` when it is to come (the networks' own scheduling is not
used). `Publisher::publish(SocialPostTarget $target)`:

- a disabled target, one on its way or published is left;
- no rendering: `FAILED`, "render the post first";
- violations: `FAILED` with the reasons, nothing sent;
- otherwise the provider's `publish()` with the public addresses; the network's id kept and its
  answer applied - `PUBLISHED` (the permalink and date), `PROCESSING`, `SENT` (pending) or
  `FAILED` (its reason). An omnipost exception fails the target; an `UnauthorizedException` adds
  "connect the account again (Social › Accounts)".
- On its way, a `CheckTargetMessage` follows after `social.check.delay` seconds.

## Status checks

`Publisher::check(SocialPostTarget $target): bool` reads the status again for a target on its way:
applied as above; a network that does not answer once is asked again (logged); an unauthorised
answer fails it. After `social.check.attempts` looks still processing, it fails ("Still
processing on Instagram after 20 looks: see the account itself"). Again on its way: another
`CheckTargetMessage` after the delay.

## Retrying

`Publisher::retry(SocialPostTarget $target)` - its attempts and error cleared, rendered again
when its film is gone (else `RENDERED`), and a `PublishTargetMessage` sent.

## The public address of a film

Instagram takes no upload: it is given an address and comes for the film.
`Base\Social\Service\MediaUrls::for(SocialPostTarget $target, bool $cover = false)` gives
`/social/media/{target}/{token}.{ext}` (`social_media`), absolute, signed by
`Base\Social\Service\MediaToken`: the first 32 hex characters of an HMAC-SHA256 of the target's
id and the file's path with the kernel secret - it names that one file, and a file rendered again
gets another.

`MediaController` answers it (GET, HEAD, ranges, `Cache-Control: private, max-age=3600`,
`X-Robots-Tag: noindex, nofollow`) only when the token names the target's rendering or cover,
the extension is the file's, the file exists, and the publication is on its way or ended less
than `social.media_url_ttl` seconds ago; anything else is the same 404.
