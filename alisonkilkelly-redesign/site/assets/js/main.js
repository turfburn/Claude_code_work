(function () {
  var header = document.querySelector('.site-header');
  var nav = document.getElementById('site-nav');
  var backdrop = document.querySelector('.nav-backdrop');
  var openBtn = document.querySelector('.menu-btn');
  var closeBtn = document.querySelector('.nav-close');

  // Header shadow once the page scrolls
  function onScroll() { header.classList.toggle('is-scrolled', window.scrollY > 8); }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  // Mobile drawer
  function setDrawer(open) {
    nav.classList.toggle('is-open', open);
    backdrop.classList.toggle('is-open', open);
    openBtn.setAttribute('aria-expanded', String(open));
    document.body.style.overflow = open ? 'hidden' : '';
    if (open) closeBtn.focus(); else openBtn.focus();
  }
  openBtn.addEventListener('click', function () { setDrawer(true); });
  closeBtn.addEventListener('click', function () { setDrawer(false); });
  backdrop.addEventListener('click', function () { setDrawer(false); });

  // Dropdowns (click/keyboard; hover on desktop)
  var toggles = document.querySelectorAll('.nav__toggle');
  function closeAll(except) {
    toggles.forEach(function (t) {
      if (t === except) return;
      t.setAttribute('aria-expanded', 'false');
      document.getElementById(t.getAttribute('aria-controls')).classList.remove('is-open');
    });
  }
  var desktop = window.matchMedia('(min-width: 1121px)');
  toggles.forEach(function (t) {
    var sub = document.getElementById(t.getAttribute('aria-controls'));
    var li = t.parentElement;
    function set(open) { t.setAttribute('aria-expanded', String(open)); sub.classList.toggle('is-open', open); }
    var hoverAt = 0;
    t.addEventListener('click', function () {
      // A mouse click right after hover-open should keep the menu open, not toggle it shut
      if (desktop.matches && Date.now() - hoverAt < 600) { set(true); return; }
      var open = t.getAttribute('aria-expanded') !== 'true';
      closeAll(t); set(open);
    });
    var timer;
    li.addEventListener('mouseenter', function () { if (!desktop.matches) return; clearTimeout(timer); closeAll(t); if (t.getAttribute('aria-expanded') !== 'true') hoverAt = Date.now(); set(true); });
    li.addEventListener('mouseleave', function () { if (!desktop.matches) return; timer = setTimeout(function () { set(false); }, 180); });
    li.addEventListener('focusout', function (e) { if (desktop.matches && !li.contains(e.relatedTarget)) set(false); });
  });
  document.addEventListener('click', function (e) { if (desktop.matches && !e.target.closest('.nav__list > li')) closeAll(); });
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    if (nav.classList.contains('is-open')) setDrawer(false);
    var open = document.querySelector('.nav__toggle[aria-expanded="true"]');
    if (open) { closeAll(); open.focus(); }
  });

  // YouTube: load the player only when clicked (faster pages, no tracking until play)
  document.querySelectorAll('.yt').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var f = document.createElement('iframe');
      f.src = 'https://www.youtube-nocookie.com/embed/' + btn.dataset.id + '?autoplay=1&rel=0';
      f.title = btn.getAttribute('aria-label') || 'Video';
      f.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
      f.allowFullscreen = true;
      btn.replaceWith(f);
    });
  });

  // Open a testimonial when linked to by anchor
  function openHash() {
    if (!location.hash) return;
    var el = document.getElementById(location.hash.slice(1));
    if (el) { var d = el.querySelector('details'); if (d) d.open = true; }
  }
  window.addEventListener('hashchange', openHash);
  openHash();
})();
