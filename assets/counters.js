/* ==========================================================
   COMCARES — shared count-up animation
   Used by: index.php, about.php, services.php, products.php
   (loaded via includes/footer.php)
   ----------------------------------------------------------
   - Animates [data-count] elements from 0 to their target over
     ~2.5 seconds with an ease-out curve (requestAnimationFrame).
   - Also animates .next-stats strong blocks (index.php/about.php)
     whose markup is <strong>150<span>+</span></strong>: only the
     leading text node is animated, so the styled suffix span
     survives.
   - ONE animation system for the whole site. The old inline
     counter in includes/footer.php was removed to avoid
     double-animation conflicts.
   - Updates textContent in place — no injected wrapper spans —
     so gradient text-fill styles keep rendering the digits.
   - Respects prefers-reduced-motion (jumps straight to the value).
   ========================================================== */
(function () {
  "use strict";

  /* "A few seconds" — 2.5s feels premium without dragging. */
  var DURATION = 2500;

  function prefersReduced() {
    return window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  }

  /* Ease-out cubic: fast start, gentle landing — feels "professional". */
  function easeOutCubic(p) {
    return 1 - Math.pow(1 - p, 3);
  }

  /* Work out what each counter element animates.
     Returns { goal, suffix, mode, el/node } or null.
       mode "full": element owns its text entirely ([data-count]).
       mode "node": only the first text node animates, so a styled
                    suffix span survives (<strong>150<span>+</span></strong>).
  */
  function describe(el) {
    var dc = el.getAttribute("data-count");
    if (dc !== null && dc !== "") {
      var goal = parseInt(dc, 10);
      if (isNaN(goal)) return null;
      return {
        
        goal: goal,
        suffix: el.getAttribute("data-suffix") || "",
        mode: "full",
        el: el,
      };
    }
    /* Fallback: .next-stats style markup. */
    var node = el.firstChild;
    if (!node || node.nodeType !== Node.TEXT_NODE) return null;
    var match = node.nodeValue.match(/\d+/);
    if (!match) return null;
    return {
      goal: parseInt(match[0], 10),
      suffix: "",
      mode: "node",
      node: node,
    };
  }

  function getCounter(el) {
    if (typeof el._counter === "undefined") el._counter = describe(el);
    return el._counter;
  }

  function setLiveValue(counter, value) {
    if (counter.mode === "full") counter.el.textContent = String(value) + counter.suffix;
    else counter.node.nodeValue = String(value);
  }

  /* Run one full 0 -> target animation for one element. */
  function animate(el) {
    var counter = getCounter(el);
    if (!counter) return;

    /* Accessibility: expose the final value to assistive tech. */
    el.setAttribute("aria-label", counter.goal + counter.suffix);

    var start = null;
    function tick(now) {
      if (start === null) start = now;
      var p = Math.min((now - start) / DURATION, 1);
      setLiveValue(counter, Math.round(easeOutCubic(p) * counter.goal));
      if (p < 1) {
        el._rafId = requestAnimationFrame(tick);
      } else {
        setLiveValue(counter, counter.goal);
        el.classList.add("next-counted");
        el._rafId = null;
      }
    }
    setLiveValue(counter, 0);
    el._rafId = requestAnimationFrame(tick);
  }

  /* Cancel a running animation and reset the element to 0. */
  function reset(el) {
    if (el._rafId) {
      cancelAnimationFrame(el._rafId);
      el._rafId = null;
    }
    el.classList.remove("next-counted");
    var counter = getCounter(el);
    if (counter) setLiveValue(counter, 0);
  }

  function init() {
    /* One selector list covers both markup styles. */
    var counters = Array.prototype.slice.call(
      document.querySelectorAll("[data-count], .next-stats strong"),
    );
    if (!counters.length) return;

    /* Reduced motion: set final values once, no observer, no restart. */
    if (prefersReduced()) {
      counters.forEach(function (el) {
        var counter = getCounter(el);
        if (!counter) return;
        el.setAttribute("aria-label", counter.goal + counter.suffix);
        setLiveValue(counter, counter.goal);
      });
      return;
    }

    if (!("IntersectionObserver" in window)) {
      /* Ancient browsers: just set final values, no animation. */
      counters.forEach(function (el) {
        var counter = getCounter(el);
        if (counter) setLiveValue(counter, counter.goal);
      });
      return;
    }

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            /* (Re)start from 0 each time the element enters view. */
            reset(entry.target);
            animate(entry.target);
          } else {
            /* Left the viewport: cancel any running animation and
                       rewind to 0 so the next entry restarts cleanly. */
            reset(entry.target);
          }
        });
      },
      {
        threshold: 0.35 /* ~35% of the stat visible */,
      },
    );

    counters.forEach(function (el) {
      reset(el);
      observer.observe(el);
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
