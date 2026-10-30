/* ============================================================
   COMCARES — FX motion driver (assets/fx-effects.js)
   Powers the "fantastic effects" layer:
     • auto-applies shimmer / beam / float / zoom classes
     • unblur text reveals, side reveals, image zoom reveals
     • holographic tilt + sheen tracking on cards
     • cursor comet trail
     • click micro-shake on buttons
     • flicker when counters finish
   Dependency-free. Respects prefers-reduced-motion.
   ============================================================ */
(function () {
  "use strict";

  var doc = document;
  var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var fineHover = window.matchMedia(
    "(hover: hover) and (pointer: fine)",
  ).matches;

  /* ---------- Shared IntersectionObserver for fx reveals ---------- */
  var io =
    "IntersectionObserver" in window && !reduce
      ? new IntersectionObserver(
          function (entries) {
            entries.forEach(function (e) {
              if (e.isIntersecting) {
                e.target.classList.add("fx-in");
                io.unobserve(e.target);
              }
            });
          },
          { threshold: 0.18 },
        )
      : null;

  function watch(el) {
    if (io) io.observe(el);
    else el.classList.add("fx-in");
  }

  /* ---------- 1. Auto-decorate common elements ---------- */

  /* Buttons get sheen sweep */
  doc.querySelectorAll(".btn, .nx-btn, .next-button").forEach(function (b) {
    b.classList.add("fx-sheen");
  });

  /* Buttons get micro shake on click */
  doc.addEventListener("click", function (e) {
    var btn = e.target.closest(".btn, .nx-btn, .next-button");
    if (!btn || reduce) return;
    btn.classList.remove("fx-clicked");
    void btn.offsetWidth;
    btn.classList.add("fx-clicked");
    setTimeout(function () {
      btn.classList.remove("fx-clicked");
    }, 340);
  });

  /* Footer links get underline draw */
  doc
    .querySelectorAll(".site-footer a, .nx-endnote a, .footer-column a")
    .forEach(function (a) {
      a.classList.add("fx-link");
    });

  /* Cards get holographic hover */
  doc
    .querySelectorAll(
      ".card, .nx-card, .next-tier, .next-product-card, .next-service-card, .next-stat-card",
    )
    .forEach(function (c) {
      c.classList.add("fx-holo");
    });

  /* Frames with images get zoom-out reveal */
  doc
    .querySelectorAll(
      ".next-hero-figure, .nx-hero-figure, .nx-show-card img, .product-figure",
    )
    .forEach(function (f) {
      var host = f.classList.contains("fx-zoom") ? f : f.parentElement;
      if (host) {
        host.classList.add("fx-zoom");
        watch(host);
      }
    });

  /* ---------- 2. Unblur text reveal for section eyebrows & tags ---------- */
  doc
    .querySelectorAll(
      ".nx-eyebrow, .section-tag, .next-section-head p, .nx-section-tag",
    )
    .forEach(function (el, i) {
      el.classList.add("fx-scramble");
      el.style.transitionDelay = (i % 6) * 0.06 + "s";
      watch(el);
    });

  /* ---------- 3. Side reveals on grids (alternating left/right) ---------- */
  doc
    .querySelectorAll(
      ".features > *, .nx-steps > *, .next-steps article, .next-process > *, .nx-tiers .next-tier",
    )
    .forEach(function (el, i) {
      el.classList.add(i % 2 ? "fx-reveal-r" : "fx-reveal-l");
      el.style.transitionDelay = (i % 4) * 0.09 + "s";
      watch(el);
    });

  /* ---------- 4. Floating idle animation on icons & badges ---------- */
  doc
    .querySelectorAll(
      ".card h2, .nx-card-ico, .nx-card svg, .next-service-ico, .footer-brand .brand-name",
    )
    .forEach(function (el, i) {
      el.classList.add("fx-float");
      el.style.setProperty("--fx-delay", -(i % 5) * 0.8 + "s");
    });

  /* ---------- 5. Shimmer on brand name & hero ems ---------- */
  doc
    .querySelectorAll(".brand-name, .nx-hero h1 em, .gradient-text")
    .forEach(function (el) {
      el.classList.add("fx-shimmer");
    });

  /* ---------- 6. Beam border run on hero panels + tier cards when visible ---------- */
  doc
    .querySelectorAll(
      ".nx-hero-figure, .next-tier, .nx-est-panel, .nx-ring-card",
    )
    .forEach(function (el, i) {
      el.classList.add("fx-beam");
      /* stagger the running-light start */
      setTimeout(
        function () {
          el.classList.add("fx-run");
        },
        400 + i * 250,
      );
    });

  /* ---------- 7. Aurora ribbon into hero-ish sections (once) ---------- */
  var hero = doc.querySelector(".nx-hero, .next-hero, .hero");
  if (hero && !reduce) {
    var rib = doc.createElement("div");
    rib.className = "fx-ribbon";
    rib.setAttribute("aria-hidden", "true");
    hero.appendChild(rib);

    var stars = doc.createElement("div");
    stars.className = "fx-stars";
    stars.setAttribute("aria-hidden", "true");
    for (var s = 0; s < 24; s++) {
      var dot = doc.createElement("i");
      dot.style.left = Math.random() * 100 + "%";
      dot.style.top = Math.random() * 100 + "%";
      dot.style.setProperty("--fx-delay", Math.random() * 3.4 + "s");
      stars.appendChild(dot);
    }
    hero.appendChild(stars);
  }

  /* ---------- 8. Holographic tilt + sheen tracking on cards ---------- */
  if (fineHover && !reduce) {
    doc.querySelectorAll(".fx-holo").forEach(function (card) {
      card.addEventListener("mousemove", function (e) {
        var r = card.getBoundingClientRect();
        var x = (e.clientX - r.left) / r.width;
        var y = (e.clientY - r.top) / r.height;
        card.style.setProperty("--fx-hx", x * 100 + "%");
        card.style.setProperty("--fx-hy", y * 100 + "%");
        card.style.transform =
          "perspective(800px) rotateY(" +
          (x - 0.5) * 6 +
          "deg) rotateX(" +
          (0.5 - y) * 6 +
          "deg) translateY(-4px)";
      });
      card.addEventListener("mouseleave", function () {
        card.style.transform = "";
      });
    });
  }

  /* ---------- 9. Cursor comet trail ---------- */
  if (fineHover && !reduce) {
    var N = 7;
    var comets = [];
    var pos = { x: innerWidth / 2, y: innerHeight / 2 };
    var pts = [];
    for (var c = 0; c < N; c++) {
      var el = doc.createElement("span");
      el.className = "fx-comet";
      doc.body.appendChild(el);
      comets.push({ el: el, x: pos.x, y: pos.y });
      pts.push({ x: pos.x, y: pos.y });
    }
    var mx = pos.x,
      my = pos.y,
      ticking = false;
    doc.addEventListener(
      "mousemove",
      function (e) {
        mx = e.clientX;
        my = e.clientY;
        if (!ticking) {
          ticking = true;
          requestAnimationFrame(follow);
        }
      },
      { passive: true },
    );

    function follow() {
      ticking = false;
      var px = mx,
        py = my;
      comets.forEach(function (cm, i) {
        cm.x += (px - cm.x) * (0.32 - i * 0.035);
        cm.y += (py - cm.y) * (0.32 - i * 0.035);
        cm.el.style.transform =
          "translate(" +
          (cm.x - 4) +
          "px," +
          (cm.y - 4) +
          "px) scale(" +
          (1 - i * 0.12) +
          ")";
        px = cm.x;
        py = cm.y;
      });
    }
  }

  /* ---------- 10. Flicker accents after counters complete ---------- */
  doc.querySelectorAll("[data-count], .counter").forEach(function (num) {
    num.addEventListener("animationend", function () {}, { once: true });
    var check = setInterval(function () {
      if (
        num.classList.contains("done") ||
        /^\d+$/.test(num.textContent.trim())
      ) {
        clearInterval(check);
        num.classList.add("fx-flicker");
      }
    }, 300);
    setTimeout(function () {
      clearInterval(check);
    }, 8000);
  });
})();
