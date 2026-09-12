/* ============================================================
   STUDIO GREEN — Interactions
   ============================================================ */
(function () {
  "use strict";

  /* ---- Palette variants (persisted across pages) ---- */
  var GS_PRESETS = {
    forest: { "--dark": "#193117", "--cta-bg": "#0b100b" },
    noir:   { "--dark": "#0b100b", "--cta-bg": "#193117" }
  };
  function applyGSPalette() {
    var root = document.documentElement;
    var preset = localStorage.getItem("gs.preset") || "forest";
    var vars = GS_PRESETS[preset] || GS_PRESETS.forest;
    Object.keys(vars).forEach(function (k) { root.style.setProperty(k, vars[k]); });
    var accent = localStorage.getItem("gs.accent");
    if (accent) {
      root.style.setProperty("--accent", accent);
      root.style.setProperty("--lime", accent);
    } else {
      root.style.removeProperty("--accent");
      root.style.removeProperty("--lime");
    }
  }
  window.applyGSPalette = applyGSPalette;
  applyGSPalette();

  /* ---- Nav: scrolled + over-dark-hero state ---- */
  const nav = document.querySelector(".nav");
  const hero = document.querySelector("[data-nav]") ? document.querySelector(".hero") : null;
  const startsDark = nav && nav.classList.contains("on-dark");
  const onScroll = () => {
    if (!nav) return;
    nav.classList.toggle("scrolled", window.scrollY > 24);
    if (startsDark && hero) {
      const flip = hero.offsetHeight - 90;
      nav.classList.toggle("on-dark", window.scrollY < flip);
    }
  };
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  /* ---- Scroll reveal (IO + rect fallback so nothing stays invisible) ---- */
  const reveals = Array.prototype.slice.call(document.querySelectorAll("[data-reveal]"));
  const show = (el) => el.classList.add("in");
  const sweep = () => {
    const h = window.innerHeight || document.documentElement.clientHeight;
    reveals.forEach((el) => {
      if (el.classList.contains("in")) return;
      const r = el.getBoundingClientRect();
      if (r.top < h * 0.92 && r.bottom > 0) show(el);
    });
  };
  if ("IntersectionObserver" in window && reveals.length) {
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((e) => {
          if (e.isIntersecting) { show(e.target); io.unobserve(e.target); }
        });
      },
      { threshold: 0.12, rootMargin: "0px 0px -8% 0px" }
    );
    reveals.forEach((el) => io.observe(el));
    // Fallback: rect check on load + scroll (covers offscreen/background iframes
    // where IO may not report intersections).
    window.addEventListener("load", sweep);
    window.addEventListener("scroll", sweep, { passive: true });
    setTimeout(sweep, 400);
  } else {
    reveals.forEach(show);
  }

  /* ---- Animated hero word cycler ---- */
  const cycler = document.querySelector("[data-cycle]");
  if (cycler) {
    const words = JSON.parse(cycler.getAttribute("data-cycle"));
    let i = 0;
    cycler.textContent = words[0];
    setInterval(() => {
      cycler.classList.add("is-out");
      setTimeout(() => {
        i = (i + 1) % words.length;
        cycler.textContent = words[i];
        cycler.classList.remove("is-out");
        cycler.classList.add("is-in");
        setTimeout(() => cycler.classList.remove("is-in"), 500);
      }, 380);
    }, 2600);
  }

  /* ---- Magnetic buttons ---- */
  const magnets = document.querySelectorAll("[data-magnet]");
  if (!window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
    magnets.forEach((el) => {
      el.addEventListener("mousemove", (e) => {
        const r = el.getBoundingClientRect();
        const x = e.clientX - r.left - r.width / 2;
        const y = e.clientY - r.top - r.height / 2;
        el.style.transform = `translate(${x * 0.22}px, ${y * 0.3}px)`;
      });
      el.addEventListener("mouseleave", () => {
        el.style.transform = "";
      });
    });
  }

  /* ---- Count-up stats ---- */
  const counters = document.querySelectorAll("[data-count]");
  if (counters.length && "IntersectionObserver" in window) {
    const cio = new IntersectionObserver((entries) => {
      entries.forEach((e) => {
        if (!e.isIntersecting) return;
        const el = e.target;
        const target = parseFloat(el.getAttribute("data-count"));
        const suffix = el.getAttribute("data-suffix") || "";
        const dur = 1300;
        const start = performance.now();
        const tick = (now) => {
          const p = Math.min((now - start) / dur, 1);
          const eased = 1 - Math.pow(1 - p, 3);
          const val = target % 1 === 0 ? Math.round(target * eased) : (target * eased).toFixed(1);
          el.textContent = val + suffix;
          if (p < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
        cio.unobserve(el);
      });
    }, { threshold: 0.5 });
    counters.forEach((el) => cio.observe(el));
  }

  /* ---- Stacking cards: scale + dim covered cards as the next slides over ---- */
  const stacks = Array.prototype.slice.call(document.querySelectorAll("[data-stack]"));
  if (stacks.length && !window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
    stacks.forEach((stack) => {
      const cards = Array.prototype.slice.call(stack.querySelectorAll(".stack__card"));
      const inners = cards.map((c) => c.querySelector(".stack__inner"));
      let ticking = false;
      const update = () => {
        ticking = false;
        const vh = window.innerHeight;
        for (let i = 0; i < cards.length - 1; i++) {
          const next = cards[i + 1].getBoundingClientRect();
          // progress: 0 when next card's top is at bottom of viewport, 1 when it reaches the top
          let p = (vh - next.top) / vh;
          p = Math.max(0, Math.min(1, p));
          const scale = 1 - 0.08 * p;
          const inner = inners[i];
          if (inner) {
            inner.style.transform = "scale(" + scale.toFixed(4) + ")";
            inner.style.opacity = (1 - 0.62 * p).toFixed(3);
          }
        }
        // last card never recedes
        const last = inners[inners.length - 1];
        if (last) { last.style.transform = "none"; last.style.opacity = "1"; }
      };
      const onStackScroll = () => {
        if (!ticking) { ticking = true; requestAnimationFrame(update); }
      };
      window.addEventListener("scroll", onStackScroll, { passive: true });
      window.addEventListener("resize", onStackScroll, { passive: true });
      update();
    });
  }

  /* ---- Mobile menu toggle ---- */
  const menuBtn = document.querySelector(".menu-btn");
  const drawer = document.querySelector("[data-drawer]");
  if (menuBtn && drawer) {
    menuBtn.addEventListener("click", () => {
      drawer.classList.toggle("open");
      document.body.classList.toggle("no-scroll");
    });
    drawer.querySelectorAll("a").forEach((a) =>
      a.addEventListener("click", () => {
        drawer.classList.remove("open");
        document.body.classList.remove("no-scroll");
      })
    );
  }
  /* ---- Motion system: Lenis + masked line reveals + parallax ----
     Only acts on pages using [data-lines] / [data-reveal-group] markup. */
  (function motionSystem() {
    var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    if (!reduce && window.Lenis) {
      /* Lenis drives scrolling itself, and the stylesheet's
         `html { scroll-behavior: smooth }` fights it: the browser's native
         smooth scrolling and Lenis both try to own the same gesture, which
         reads as laggy, rubber-banding movement. Turned off here rather than
         deleted from the CSS, so it still serves as the fallback when Lenis
         is absent (blocked CDN, or the src filtered off). */
      document.documentElement.style.scrollBehavior = "auto";

      var lenis = new Lenis();
      var raf = function (t) { lenis.raf(t); requestAnimationFrame(raf); };
      requestAnimationFrame(raf);
    }

    if (reduce) return;

    function split(el) {
      /* Read the words out with a note of which sat inside a .mark highlight,
         so the highlight can be put back afterwards. Rebuilding from plain
         textContent would silently drop it. */
      var items = [];
      Array.prototype.forEach.call(el.childNodes, function (node) {
        var marked = node.nodeType === 1 && node.className &&
                     (" " + node.className + " ").indexOf(" mark ") > -1;
        (node.textContent || "").split(/\s+/).forEach(function (w) {
          if (w) items.push({ word: w, mark: marked });
        });
      });
      if (!items.length) return;

      el.textContent = "";
      var probes = [];
      items.forEach(function (item, i) {
        var s = document.createElement("span");
        /* Left inline on purpose. These spans exist only to be measured, and
           an inline-block box wraps differently from the plain text it stands
           in for, so measuring them produced line breaks the real text never
           had (an orphaned word on its own line). A plain inline span does not
           affect layout at all, so the lines read back here are the ones the
           browser actually laid out. */
        s.textContent = item.word;
        el.appendChild(s);
        probes.push(s);
        if (i < items.length - 1) el.appendChild(document.createTextNode(" "));
      });

      var lines = [], top = null;
      probes.forEach(function (s, i) {
        if (s.offsetTop !== top) { lines.push([]); top = s.offsetTop; }
        lines[lines.length - 1].push(items[i]);
      });

      el.textContent = "";
      lines.forEach(function (line) {
        var mask = document.createElement("span");
        mask.className = "reveal-line";
        var inner = document.createElement("span");

        // Consecutive highlighted words share one box, so a phrase reads as a
        // single stroke rather than one block per word.
        var runs = [];
        line.forEach(function (item) {
          var last = runs[runs.length - 1];
          if (last && last.mark === item.mark) last.words.push(item.word);
          else runs.push({ mark: item.mark, words: [item.word] });
        });

        runs.forEach(function (run, ri) {
          var text = run.words.join(" ");
          if (run.mark) {
            var m = document.createElement("span");
            m.className = "mark";
            m.textContent = text;
            inner.appendChild(m);
          } else {
            inner.appendChild(document.createTextNode(text));
          }
          if (ri < runs.length - 1) inner.appendChild(document.createTextNode(" "));
        });

        mask.appendChild(inner);
        el.appendChild(mask);
      });
    }
    function startReveals() {
      Array.prototype.forEach.call(document.querySelectorAll("[data-lines]"), function (el) {
        try {
          split(el);
        } catch (e) {}
        /* Marks the element as dealt with, which un-hides it. Inside the loop
           and after a try/catch so one bad element can never leave the rest of
           the page invisible. */
        el.classList.add("sg-split");
      });
      startGroups();
    }

    /* The splitter measures where the browser put each word, so it has to run
       against the real typeface. Switzer arrives from Fontshare with
       display=swap, so splitting immediately would measure the fallback and
       bake in line breaks that are wrong once the webfont lands. Raced against
       a timer so a font that never loads cannot hold the page hidden. */
    var fontsReady = document.fonts && document.fonts.ready
      ? document.fonts.ready
      : Promise.resolve();
    var fontsTimeout = new Promise(function (resolve) { setTimeout(resolve, 1200); });

    if (window.Promise) {
      Promise.race([fontsReady, fontsTimeout]).then(startReveals);
      // Last resort: if the promise never settles, un-hide regardless.
      setTimeout(function () {
        Array.prototype.forEach.call(document.querySelectorAll("[data-lines]"), function (el) {
          el.classList.add("sg-split");
        });
      }, 2500);
    } else {
      startReveals();
    }

    function startGroups() {
    var groups = Array.prototype.slice.call(document.querySelectorAll("[data-reveal-group]"));
    groups.forEach(function (group) {
      Array.prototype.forEach.call(group.querySelectorAll(".reveal-line > span"), function (s, i) {
        s.style.transitionDelay = (i * 60) + "ms";
      });
    });
    var show = function (g) { g.classList.add("in-view"); };
    if (groups.length && "IntersectionObserver" in window) {
      var gio = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
          if (e.isIntersecting) { show(e.target); gio.unobserve(e.target); }
        });
      }, { threshold: 0.2, rootMargin: "0px 0px -8% 0px" });
      groups.forEach(function (g) { gio.observe(g); });
    } else {
      groups.forEach(show);
    }
    var groupSweep = function () {
      groups.forEach(function (g) {
        if (g.classList.contains("in-view")) return;
        var r = g.getBoundingClientRect();
        if (r.top < window.innerHeight && r.bottom > 0) show(g);
      });
    };
    setTimeout(groupSweep, 1400);
    window.addEventListener("scroll", groupSweep, { passive: true });
    }

    var px = document.querySelector("[data-parallax]");
    if (px) {
      var pxSec = px.parentElement;
      var pxTick = false;
      var pxUpdate = function () {
        pxTick = false;
        var r = pxSec.getBoundingClientRect();
        if (r.bottom < 0 || r.top > window.innerHeight) return;
        px.style.transform = "translate3d(0," + (r.top * -0.12).toFixed(1) + "px,0)";
      };
      window.addEventListener("scroll", function () {
        if (!pxTick) { pxTick = true; requestAnimationFrame(pxUpdate); }
      }, { passive: true });
      pxUpdate();
    }
  })();

  /* ---- Cookie consent banner (site-wide) ---- */
  (function cookieBanner() {
    var KEY = "gs.cookieConsent";
    try { if (localStorage.getItem(KEY)) return; } catch (e) {}
    /* Permalink comes from WordPress (see inc/assets.php) so the banner keeps
       pointing at the cookie policy whatever its slug ends up being. */
    var cookiesUrl = (window.SGData && window.SGData.cookiesUrl) || "/cookies/";
    var banner = document.createElement("div");
    banner.className = "cookie-banner";
    banner.setAttribute("role", "dialog");
    banner.setAttribute("aria-label", "Cookie notice");
    banner.setAttribute("aria-live", "polite");
    banner.innerHTML =
      '<div class="cookie-banner__inner">' +
        '<p class="cookie-banner__text">We use a few essential and analytics cookies to keep this site running smoothly and understand how it\u2019s used. Read our <a href="' + cookiesUrl + '">Cookie Policy</a>.</p>' +
        '<div class="cookie-banner__actions">' +
          '<button type="button" class="btn btn--ghost-dark" data-cookie="declined">Decline</button>' +
          '<button type="button" class="btn btn--light" data-cookie="accepted">Accept</button>' +
        '</div>' +
      '</div>';
    var mount = function () {
      document.body.appendChild(banner);
      requestAnimationFrame(function () {
        requestAnimationFrame(function () { banner.classList.add("is-visible"); });
      });
    };
    if (document.body) mount();
    else document.addEventListener("DOMContentLoaded", mount);
    banner.addEventListener("click", function (e) {
      var btn = e.target.closest ? e.target.closest("[data-cookie]") : null;
      if (!btn) return;
      try { localStorage.setItem(KEY, btn.getAttribute("data-cookie")); } catch (e2) {}
      banner.classList.remove("is-visible");
      banner.classList.add("is-leaving");
      setTimeout(function () { if (banner.parentNode) banner.parentNode.removeChild(banner); }, 600);
    });
  })();
})();
