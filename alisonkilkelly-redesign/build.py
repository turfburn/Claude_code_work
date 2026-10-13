#!/usr/bin/env python3
"""Build the AK Counseling static site.

Each file in src/content/ is an HTML fragment that starts with a metadata comment:
    <!--meta {"path": "/about/", "title": "...", "description": "..."} -->
build.py wraps it in the shared layout (header, nav, footer, contact bar) and writes
site/<path>/index.html, preserving the live site's URLs.

Usage: python3 build.py
"""
import json
import re
from pathlib import Path

ROOT = Path(__file__).parent
SRC = ROOT / "src" / "content"
OUT = ROOT / "site"
BASE_URL = "https://alisonkilkelly.com"

PHONE_DISPLAY = "608.567.4648"
PHONE_TEL = "+16085674648"
EMAIL = "AKCounselingMadison@gmail.com"
MAP_URL = "https://maps.app.goo.gl/ryx2a1LV6d1sYjUs8"
PORTAL_URL = "https://akcounselingmadison.clientsecure.me/sign-in"
FACEBOOK = "https://facebook.com/akcounseling"
LINKEDIN = "https://www.linkedin.com/in/alison-kilkelly-lcsw-5a45574"
INSTAGRAM = "https://instagram.com/akcounselingmadison"
PSYCH_TODAY = "https://www.psychologytoday.com/us/therapists/alison-kilkelly-madison-wi/195607"
GOOD_THERAPY = "https://www.goodtherapy.org/therapists/profile/alison-kilkelly-20140716"

ICONS = {
    "phone": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/></svg>',
    "text": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8v.5z"/></svg>',
    "mail": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>',
    "pin": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>',
    "calendar": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>',
    "video": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m23 7-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2"/></svg>',
    "lock": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
    "clock": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>',
    "heart": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21l7.8-7.5 1-1.1a5.5 5.5 0 0 0 0-7.8z"/></svg>',
    "user": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
    "rings": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="14" r="6"/><circle cx="15" cy="14" r="6"/><path d="m10 4 2-2 2 2"/></svg>',
    "repair": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s-8-4.5-8-11.8A4.8 4.8 0 0 1 12 6a4.8 4.8 0 0 1 8 4.2C20 17.5 12 22 12 22z"/><path d="m12 6-1.5 4 3 2-1.5 4"/></svg>',
    "chevron": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>',
    "arrow": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>',
    "menu": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>',
    "close": '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>',
    "facebook": '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 8V6.2c0-.8.2-1.2 1.4-1.2H17V2h-2.6C11.3 2 10 3.5 10 6v2H8v3h2v11h4V11h2.7l.3-3h-3z"/></svg>',
    "linkedin": '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zM3 9.5h4V21H3zM9.5 9.5h3.8v1.6h.1c.5-1 1.8-2 3.8-2 4 0 4.8 2.6 4.8 6V21h-4v-5.2c0-1.2 0-2.9-1.8-2.9s-2 1.4-2 2.8V21h-4z"/></svg>',
    "instagram": '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10zm0 8.2a3.2 3.2 0 1 1 0-6.4 3.2 3.2 0 0 1 0 6.4zM17.3 5.5a1.2 1.2 0 1 0 0 2.4 1.2 1.2 0 0 0 0-2.4zM12 2c-2.7 0-3 0-4.1.1-1 0-1.8.2-2.4.5a5 5 0 0 0-1.8 1.1A5 5 0 0 0 2.6 5.5C2.3 6.1 2.2 6.9 2.1 8 2 9 2 9.3 2 12s0 3 .1 4.1c0 1 .2 1.8.5 2.4.3.7.6 1.3 1.1 1.8.6.6 1.1.9 1.8 1.2.6.2 1.4.4 2.4.4 1.1.1 1.4.1 4.1.1s3 0 4.1-.1c1 0 1.8-.2 2.4-.4a5 5 0 0 0 1.8-1.2 5 5 0 0 0 1.2-1.8c.2-.6.4-1.4.4-2.4.1-1.1.1-1.4.1-4.1s0-3-.1-4.1c0-1-.2-1.8-.4-2.4a5 5 0 0 0-1.2-1.8 5 5 0 0 0-1.8-1.1c-.6-.3-1.4-.4-2.4-.5C15 2 14.7 2 12 2zm0 1.8c2.7 0 3 0 4 .1.9 0 1.5.2 1.8.3.5.2.8.4 1.1.7.4.4.6.7.8 1.2.1.3.3.9.3 1.8.1 1 .1 1.3.1 4s0 3-.1 4c0 .9-.2 1.5-.3 1.8-.2.5-.4.8-.8 1.1-.3.4-.6.6-1.1.8-.3.1-.9.3-1.8.3-1 .1-1.3.1-4 .1s-3 0-4-.1c-.9 0-1.5-.2-1.8-.3-.5-.2-.8-.4-1.1-.8-.4-.3-.6-.6-.8-1.1-.1-.3-.3-.9-.3-1.8-.1-1-.1-1.3-.1-4s0-3 .1-4c0-.9.2-1.5.3-1.8.2-.5.4-.8.8-1.2.3-.3.6-.5 1.1-.7.3-.1.9-.3 1.8-.3 1-.1 1.3-.1 4-.1z"/></svg>',
}

SERVICES = [
    ("/couplestherapy-marriagecounseling/", "Marriage &amp; Couples Counseling", "Dating, engaged, married, co-parenting"),
    ("/therapy-coaching/", "Therapy &amp; Coaching", "Individual therapy blended with coaching"),
    ("/pre-marital-counseling/", "Pre-Marital Counseling", "The 12 most vital topics before “I do”"),
    ("/infidelity/", "Infidelity Recovery", "Heal after an affair, together or alone"),
]
TESTIMONIALS = [
    ("/what-clients-say/", "All client stories", None),
    ("/what-clients-say/#exceptional-marital-therapy", "Exceptional marital therapy", None),
    ("/what-clients-say/#depression-anxiety-parenting", "Depression, Anxiety, Parenting", None),
    ("/what-clients-say/#not-hopelessly-broken", "NOT hopelessly broken after all!", None),
    ("/what-clients-say/#abusive-significant-other", "Mentally, emotionally, physically abusive significant other", None),
    ("/what-clients-say/#alison-saved-my-life", "Alison saved my life…", None),
    ("/what-clients-say/#diminished-anxiety", "Diminished anxiety dramatically improved my outlook on life!", None),
]
NAV = [
    ("group", "Services", SERVICES),
    ("link", "/about/", "About Alison"),
    ("link", "/what-to-expect/", "What to Expect"),
    ("group", "What Clients Say", TESTIMONIALS),
    ("link", "/rates/", "Rates"),
    ("link", "/resources/", "Resources"),
    ("link", "/contact-location/", "Contact"),
]


def nav_html(path):
    out = ['<li class="nav__home-mobile"><a class="nav__link" href="/"%s>Home</a></li>' % (' aria-current="page"' if path == "/" else "")]
    for i, item in enumerate(NAV):
        if item[0] == "link":
            _, href, label = item
            cur = ' aria-current="page"' if href == path else ""
            out.append(f'<li><a class="nav__link" href="{href}"{cur}>{label}</a></li>')
        else:
            _, label, links = item
            sub_id = f"sub-{i}"
            current = any(h.split("#")[0] == path for h, _, _ in links)
            subs = []
            for href, text, desc in links:
                cur = ' aria-current="page"' if href == path else ""
                d = f"<small>{desc}</small>" if desc else ""
                subs.append(f'<li><a href="{href}"{cur}>{text}{d}</a></li>')
            out.append(
                f'<li class="{"nav__item--current" if current else ""}">'
                f'<button class="nav__toggle" type="button" aria-expanded="false" aria-controls="{sub_id}">{label}{ICONS["chevron"]}</button>'
                f'<ul class="nav__sub" id="{sub_id}">{"".join(subs)}</ul></li>'
            )
    return "\n".join(out)


def layout(meta, body):
    path = meta["path"]
    title = meta["title"]
    desc = meta["description"]
    canonical = BASE_URL + path
    noindex = '<meta name="robots" content="noindex">' if meta.get("noindex") else ""
    og_image = BASE_URL + "/assets/img/" + meta.get("og_image", "alison-garden-1.jpg")
    jsonld = ""
    if path == "/":
        jsonld = '<script type="application/ld+json">' + json.dumps({
            "@context": "https://schema.org",
            "@type": "ProfessionalService",
            "name": "AK Counseling, Inc.",
            "description": "Couples therapy, marriage counseling, pre-marital counseling, infidelity recovery, and individual therapy & coaching in Madison, WI.",
            "url": BASE_URL + "/",
            "telephone": "+1-608-567-4648",
            "email": EMAIL,
            "image": og_image,
            "logo": BASE_URL + "/assets/img/ak-logo.png",
            "address": {"@type": "PostalAddress", "streetAddress": "313 Price Place, Suite 205", "addressLocality": "Madison", "addressRegion": "WI", "postalCode": "53705", "addressCountry": "US"},
            "areaServed": ["Madison", "Middleton", "Fitchburg", "Verona", "Sun Prairie", "Waunakee", "Wisconsin Dells", "McFarland", "Milwaukee", "Richland Center", "Stoughton", "Mount Horeb"],
            "founder": {"@type": "Person", "name": "Alison Kilkelly", "honorificSuffix": "LCSW", "jobTitle": "Licensed Clinical Social Worker"},
            "sameAs": [FACEBOOK, LINKEDIN, INSTAGRAM, PSYCH_TODAY, GOOD_THERAPY],
        }, ensure_ascii=False) + "</script>"

    year_line = "© AK Counseling, Inc. All rights reserved."
    return f"""<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{title}</title>
<meta name="description" content="{desc}">
<link rel="canonical" href="{canonical}">
{noindex}
<meta property="og:type" content="website">
<meta property="og:site_name" content="AK Counseling, Inc.">
<meta property="og:title" content="{title}">
<meta property="og:description" content="{desc}">
<meta property="og:url" content="{canonical}">
<meta property="og:image" content="{og_image}">
<meta name="twitter:card" content="summary_large_image">
<meta name="theme-color" content="#0a434a">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/img/favicon-32.png">
<link rel="apple-touch-icon" href="/assets/img/apple-touch-icon.png">
<link rel="preload" href="/assets/fonts/fraunces-latin-full-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/figtree-latin-wght-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="/assets/css/styles.css">
{jsonld}
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>

<div class="topbar">
  <div class="wrap">
    <div class="topbar__contact">
      <a href="tel:{PHONE_TEL}">{ICONS["phone"]}Call or text {PHONE_DISPLAY}</a>
      <a href="mailto:{EMAIL}?subject=Website%20Inquiry">{ICONS["mail"]}{EMAIL}</a>
      <span class="topbar__note">In-person in Madison &amp; via Zoom</span>
    </div>
    <div class="topbar__social">
      <a class="topbar__portal" href="{PORTAL_URL}">Client portal</a>
      <a href="{FACEBOOK}" aria-label="Facebook">{ICONS["facebook"]}</a>
      <a href="{LINKEDIN}" aria-label="LinkedIn">{ICONS["linkedin"]}</a>
      <a href="{INSTAGRAM}" aria-label="Instagram">{ICONS["instagram"]}</a>
    </div>
  </div>
</div>

<header class="site-header">
  <div class="wrap">
    <a class="brand" href="/" aria-label="AK Counseling, Inc. home">
      <img src="/assets/img/ak-logo.png" width="48" height="48" alt="">
      <span class="brand__text"><span class="brand__name">AK Counseling, Inc.</span><span class="brand__tag">Couples Therapy &amp; Marriage Counseling · Madison, WI</span></span>
    </a>
    <button class="menu-btn" type="button" aria-expanded="false" aria-controls="site-nav">{ICONS["menu"]}Menu</button>
    <nav class="nav" id="site-nav" aria-label="Main">
      <button class="menu-btn nav-close" type="button" aria-label="Close menu">{ICONS["close"]}</button>
      <ul class="nav__list">
{nav_html(path)}
      </ul>
      <a class="btn btn--primary nav__cta" href="/get-started/">Get Started</a>
    </nav>
    <div class="nav-backdrop"></div>
  </div>
</header>

<main id="main">
{body}
</main>

<footer class="site-footer">
  <div class="wrap">
    <div class="footer__grid">
      <div class="footer__brand">
        <img src="/assets/img/ak-logo.png" width="64" height="64" alt="AK Counseling, Inc. logo" loading="lazy">
        <p><strong style="color:#fff">Alison Kilkelly, LCSW</strong><br>Private, trustworthy, effective therapy for couples and adults.</p>
        <p>A preferred therapist on <a href="{PSYCH_TODAY}">PsychologyToday.com</a> &amp; <a href="{GOOD_THERAPY}">GoodTherapy.org</a></p>
        <div class="footer__social">
          <a href="{FACEBOOK}" aria-label="Facebook">{ICONS["facebook"]}</a>
          <a href="{LINKEDIN}" aria-label="LinkedIn">{ICONS["linkedin"]}</a>
          <a href="{INSTAGRAM}" aria-label="Instagram">{ICONS["instagram"]}</a>
        </div>
      </div>
      <div>
        <h2>Services</h2>
        <ul>
          <li><a href="/couplestherapy-marriagecounseling/">Marriage &amp; Couples Counseling</a></li>
          <li><a href="/therapy-coaching/">Therapy &amp; Coaching</a></li>
          <li><a href="/pre-marital-counseling/">Pre-Marital Counseling</a></li>
          <li><a href="/infidelity/">Infidelity Recovery</a></li>
        </ul>
      </div>
      <div>
        <h2>Practice</h2>
        <ul>
          <li><a href="/about/">About Alison</a></li>
          <li><a href="/what-to-expect/">What to Expect</a></li>
          <li><a href="/what-clients-say/">What Clients Say</a></li>
          <li><a href="/rates/">Rates</a></li>
          <li><a href="/resources/">Resources</a></li>
          <li><a href="/get-started/">New Clients, Get Started</a></li>
          <li><a href="{PORTAL_URL}">Client Portal</a></li>
        </ul>
      </div>
      <div>
        <h2>Visit</h2>
        <ul>
          <li><a href="{MAP_URL}">313 Price Place, Suite 205<br>Madison, WI 53705</a></li>
          <li><a href="tel:{PHONE_TEL}">Call | Text {PHONE_DISPLAY}</a></li>
          <li><a href="mailto:{EMAIL}">{EMAIL}</a></li>
          <li>In-person &amp; virtual visits<br>Monday – Saturday</li>
        </ul>
      </div>
    </div>
    <div class="footer__bottom">
      <span>{year_line}</span>
      <span class="footer__crisis">In a crisis? Call or text <a href="tel:988">988</a> (Suicide &amp; Crisis Lifeline) or dial 911.</span>
    </div>
  </div>
</footer>

<nav class="mobile-bar" aria-label="Quick contact">
  <a href="tel:{PHONE_TEL}">{ICONS["phone"]}Call</a>
  <a href="sms:{PHONE_TEL}">{ICONS["text"]}Text</a>
  <a href="mailto:{EMAIL}?subject=Website%20Inquiry">{ICONS["mail"]}Email</a>
  <a class="is-cta" href="/get-started/">{ICONS["calendar"]}Book</a>
</nav>

<script src="/assets/js/main.js" defer></script>
</body>
</html>
"""


def expand(body):
    """Small template helpers usable inside content fragments."""
    # {{icon:name}}
    body = re.sub(r"\{\{icon:(\w+)\}\}", lambda m: ICONS[m.group(1)], body)
    # {{yt:VIDEO_ID|Title}} -> click-to-load YouTube facade
    def yt(m):
        vid, label = m.group(1), m.group(2)
        return (f'<div class="embed"><button class="yt" type="button" data-id="{vid}" aria-label="Play video: {label}" '
                f'style="background-image:url(https://i.ytimg.com/vi/{vid}/hqdefault.jpg)"><span></span></button></div>')
    body = re.sub(r"\{\{yt:([\w-]+)\|([^}]+)\}\}", yt, body)
    consts = {"PHONE_DISPLAY": PHONE_DISPLAY, "PHONE_TEL": PHONE_TEL, "EMAIL": EMAIL, "MAP_URL": MAP_URL,
              "PORTAL_URL": PORTAL_URL, "PSYCH_TODAY": PSYCH_TODAY, "GOOD_THERAPY": GOOD_THERAPY, "FACEBOOK": FACEBOOK}
    body = re.sub(r"\{\{(\w+)\}\}", lambda m: consts[m.group(1)], body)
    return body


def main():
    pages = []
    for f in sorted(SRC.glob("*.html")):
        text = f.read_text(encoding="utf-8")
        m = re.match(r"\s*<!--meta\s+(\{.*?\})\s*-->", text, re.S)
        if not m:
            raise SystemExit(f"{f.name}: missing <!--meta {{...}} --> header")
        meta = json.loads(m.group(1))
        body = expand(text[m.end():].strip())
        dest = OUT / meta["path"].strip("/") / "index.html" if meta["path"] != "/" else OUT / "index.html"
        dest.parent.mkdir(parents=True, exist_ok=True)
        dest.write_text(layout(meta, body), encoding="utf-8")
        pages.append(meta)
        print("built", meta["path"])

    urls = [p["path"] for p in pages if not p.get("noindex")]
    (OUT / "sitemap.xml").write_text(
        '<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">\n'
        + "".join(f"  <url><loc>{BASE_URL}{u}</loc></url>\n" for u in urls) + "</urlset>\n", encoding="utf-8")
    (OUT / "robots.txt").write_text(f"User-agent: *\nAllow: /\nSitemap: {BASE_URL}/sitemap.xml\n", encoding="utf-8")


if __name__ == "__main__":
    main()
