/* ============================================================
   STUDIO GREEN — Reveal bootstrap
   Runs in <head>, before first paint, so masked line reveals start
   hidden rather than flashing in. Kept as a file (it was inline on the
   static site) so the site's own Content-Security-Policy allows it.
   Without JS, or with reduced motion, the class is never added and all
   copy renders in its final position.
   ============================================================ */
try {
  if (!matchMedia("(prefers-reduced-motion: reduce)").matches) {
    document.documentElement.classList.add("js-reveal");
  }
} catch (e) {}
