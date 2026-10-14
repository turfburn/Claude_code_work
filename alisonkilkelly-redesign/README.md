# AK Counseling, Inc. — site redesign

A modernized rebuild of https://alisonkilkelly.com/ that keeps every page, its URL, and its copy, and drops the 2013-era theme.

## What the live site runs on (verified 2026-10-13)

| Layer | Finding | How it was identified |
|---|---|---|
| CMS | WordPress, installed in a `/wp/` subdirectory | `wp-json` REST API in response headers; `/wp/wp-content/…` asset paths |
| Theme | **Twenty Thirteen** (WordPress default theme from 2013) | `wp-content/themes/twentythirteen` |
| Plugins | All in One SEO 5.0.3, MonsterInsights (Google Analytics), Mobile Contact Bar, Strong Testimonials, WP Simple Pay (Stripe), Really Simple SSL | asset paths, generator tag, shortcodes, `wpm-testimonial` post type, `data-rsssl` |
| Fonts | 5 Google Fonts loaded (Cinzel, Carrois Gothic, Alata, BenchNine, Doppio One) | inline CSS |
| Hosting | nginx with an edge cache (`x-proxy-cache`, `host-header` response headers). These headers are typical of SiteGround, but that is an inference — confirm in the hosting account. | response headers |

Content: 14 pages, 0 blog posts, 17 testimonials, ~100 media files.

## What's here

```
build.py            # assembles pages: src/content/*.html + shared layout -> site/
src/content/        # one HTML fragment per page (copy lives here)
site/               # deployable output (static HTML/CSS/JS, no build step needed to host)
site-local/         # same pages with relative links, for opening from disk (demo copy)
src/wp-theme/       # WordPress theme templates + setup screen (packaged by build.py --wp)
dist/               # ak-counseling-theme.zip
  assets/css, js, img, fonts
  _redirects        # legacy-URL redirects (Netlify / Cloudflare Pages format)
  sitemap.xml, robots.txt
```

Preview locally, either way:
- **No server:** open `site-local/index.html` directly (double-click). Run `python3 build.py --local` to regenerate it.
- **With a server (matches production URLs):** `python3 -m http.server -d site 8000`, then go to http://localhost:8000.

Edit copy in `src/content/`, then run `python3 build.py --local`. Use `site/` for hosting and `site-local/` only for offline demos.

## Design system

- **Color:** derived from the AK logo. The logo's teal `#04a5b7` is used for accents, and a deeper teal `#0a6c77` for text and buttons (6.1:1 contrast on white; the logo teal on white is 3.0:1 and fails WCAG AA for body text). The logo chartreuse `#fffa0c` appears only as a soft highlighter mark on key phrases. That mark nods to Alison's stated use of "appropriate levity".
- **Type:** Fraunces (warm, soft serif) for headings, Figtree for body. Both are self-hosted under the SIL Open Font License, so no Google Fonts request is made.
- **Photography:** her real Olbrich Gardens portraits, the laughing and jumping shots, and the office photos carry the personality. Dated stock graphics were dropped: signposts, the "believe" stone, and quote images with text baked in. The Ralph Blum and Robert Louis Stevenson quotes are now live text.

## What changed vs. the live site

**Kept:** every page and URL, the page titles and meta descriptions, all body copy, all 17 testimonials verbatim, every resource video and link, the rates, policies, the Stripe deposit link, and the client-portal link.

**Structure:**
- The 12 top-level menu items are now 7 plus a "Get Started" button. The four service pages sit under a **Services** dropdown. The testimonial sub-menu is preserved, and its entries jump to anchors on What Clients Say.
- The "Click to Connect" plugin bar is now a top contact bar on desktop and a sticky Call / Text / Email / Book bar on mobile.
- Logo-image bullets are now styled lists. The rates are now price cards and a table.

**Removed:**
- Repeated keyword strings such as "Marriage Counseling Madison, WI Couples Therapy Madison, WI…" that appeared on every page. Google's spam policies treat visible keyword stuffing as a ranking risk. SEO is carried by the existing titles and descriptions, plus new canonical tags, Open Graph tags, a sitemap, and `ProfessionalService` structured data.
- A stray line on Contact: "THe ease of getting in for therapy will keep you in contact with friends more easily…"
- Images that linked to unrelated external articles. For example, the About headshot linked to a Zencare blog post.

**Added:** a footer line with the 988 crisis line. Several pages also got short section headings ("How I work", "Our method", "Questions couples often ask", "In their own words") so they can be scanned. All other copy is hers.

## Problems found on the live site (worth fixing regardless)

1. **Public Zoom passcode.** `/get-started/` publishes the standing meeting link, ID, and passcode that every session uses, so anyone can join her waiting room. The rebuild instead says the link comes with appointment confirmation. **She should also rotate the passcode.**
2. **Broken phone and email links on Contact.** The markup is `href="http://<a href=”tel:…"`, so clicking either one goes nowhere.
3. **Three internal URLs return 404.** `/contactandlocation/` is linked from the logo on every page, and `/getstarted/` and `/testimonials/` are each linked several times. The rebuild links to the right pages, and `_redirects` maps the old URLs.
4. **The deposit amount is inconsistent.** The page says "$105 deposit" and "$100 will be applied". This was kept verbatim and needs her confirmation.
5. **Testimonials disagree between pages.** The same client is a different age on the homepage and on What Clients Say: AP 45 vs. 38, JM 52 vs. 38, PM 56 vs. 46, BB 33 vs. 30. MM's quote says "4 other individual therapists" on What to Expect and "3" on the testimonials page. Each page was kept as published; she should pick one version.
6. **Four different Google Maps short links** are used for the same address. The rebuild uses one.

## Deploying

- **Static host (simplest):** point Netlify, Cloudflare Pages, or similar at `site/`. `_redirects` works there as-is.
  - The Stripe Payment Link's success and cancel URLs should go to `/deposit-confirmation/` and `/deposit-failed/`.
  - MonsterInsights analytics is not carried over. Add a GA4 or privacy-friendly analytics snippet to `build.py` if she wants it.
- **Staying on WordPress (built):** `python3 build.py --wp` writes `dist/ak-counseling-theme.zip`, a classic theme. Upload it under Appearance > Themes, then open Appearance > AK Counseling Setup. That screen applies the page designs, matching pages by URL; it backs up each page's current text, which its Undo button restores. It also builds the menu. Contact details live in Customizer > AK Counseling contact details. The flow was tested on WordPress 6.7-alpha with WordPress Playground: activate, apply, restore, re-apply.

## Not verified here

YouTube thumbnails, TED, Spotify, and the Google Maps embed could not load in the build sandbox (network policy), so those embeds were checked for markup only, not rendering.
