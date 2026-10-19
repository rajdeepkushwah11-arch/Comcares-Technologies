/* ============================================================
   COMCARES — Premium motion layer
   Runs on every page. Respects prefers-reduced-motion.
   ============================================================ */
(function () {
  "use strict";
  var doc = document;
  var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var fineHover = window.matchMedia(
    "(hover: hover) and (pointer: fine)",
  ).matches;

  /* ---------- 1. Page-load curtain fade ---------- */
  if (!reduce) {
    doc.documentElement.classList.add("nx-loaded");
  }

  /* ---------- 2. Word-by-word heading reveal ---------- */
  var HEADINGS =
    ".next-hero h1, .next-section h2, .next-dark-panel h2, .next-contact h2, .next-proof h2, .next-contact-aside h2, .next-tiers .next-live-head h2";
  if (!reduce) {
    doc.querySelectorAll(HEADINGS).forEach(function (h) {
      if (h.dataset.nxSplit) return;
      h.dataset.nxSplit = "1";
      var words = [];
      // Walk child nodes so <em> and <br> survive the split
      Array.prototype.forEach.call(h.childNodes, function (node) {
        if (node.nodeType === 3) {
          // text
          node.textContent.split(/\s+/).forEach(function (w) {
            if (w) words.push({ text: w, cls: "" });
          });
        } else if (node.nodeType === 1) {
          if (node.tagName === "BR") {
            words.push({ br: true });
            return;
          }
          node.textContent.split(/\s+/).forEach(function (w) {
            if (w)
              words.push({
                text: w,
                cls: node.tagName === "EM" ? "nx-w-em" : "",
              });
          });
        }
      });
      h.textContent = "";
      words.forEach(function (w, i) {
        if (w.br) {
          h.appendChild(doc.createElement("br"));
          return;
        }
        var span = doc.createElement("span");
        span.className = "nx-word " + w.cls;
        span.textContent = w.text;
        span.style.transitionDelay = i * 0.055 + "s";
        h.appendChild(span);
        h.appendChild(doc.createTextNode(" "));
      });
    });
  }

  /* ---------- Shared IntersectionObserver ---------- */
  var io = null;
  if ("IntersectionObserver" in window && !reduce) {
    io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("nx-in");
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.15 },
    );
  }

  function observe(el) {
    if (io) {
      io.observe(el);
    } else {
      el.classList.add("nx-in");
    }
  }

  /* Headings + cards + rows get observed */
  doc.querySelectorAll(HEADINGS).forEach(observe);
  doc
    .querySelectorAll(
      ".next-tier, .next-product-card, .next-service-card, .next-steps article, .next-contact-channels li, .next-service, .next-stat-card",
    )
    .forEach(function (el, i) {
      el.classList.add("nx-rise");
      el.style.setProperty("--nxd", Math.min((i % 4) * 0.09, 0.3) + "s");
      observe(el);
    });

  /* ---------- 3. Cursor-tracking border spotlight on cards ---------- */
  if (fineHover && !reduce) {
    var CARDS = ".next-tier, .next-product-card, .next-service-card, .next-stat-card";
    var raf = 0,
      pending = null;
    doc.addEventListener(
      "pointermove",
      function (e) {
        pending = e;
        if (raf) return;
        raf = requestAnimationFrame(function () {
          raf = 0;
          var ev = pending;
          doc.querySelectorAll(CARDS).forEach(function (card) {
            var r = card.getBoundingClientRect();
            if (
              ev.clientX < r.left - 80 ||
              ev.clientX > r.right + 80 ||
              ev.clientY < r.top - 80 ||
              ev.clientY > r.bottom + 80
            )
              return;
            card.style.setProperty("--nxmx", ev.clientX - r.left + "px");
            card.style.setProperty("--nxmy", ev.clientY - r.top + "px");
          });
        });
      },
      { passive: true },
    );
  }

  /* ---------- 3b. Stat-card progress rings ---------- */
  doc.querySelectorAll('.next-stat-card[data-stat]').forEach(function (card) {
      var pct = Math.min(parseFloat(card.getAttribute('data-stat')) / 100, 1);
      if (isNaN(pct) || pct <= 0) pct = 0.8;
      card.style.setProperty('--nxpct', pct);
  });

  /* ---------- 4b. Hero parallax orbs ---------- */
  if (fineHover && !reduce) {
    var orbs = doc.querySelectorAll('.next-orb-one, .next-orb-two');
    if (orbs.length) {
      window.addEventListener('scroll', function () {
        var y = window.scrollY;
        orbs.forEach(function (o, i) {
          o.style.transform = 'translateY(' + y * (i ? -0.12 : 0.08) + 'px)';
        });
      }, { passive: true });
    }
  }

  /* ---------- 4c. Estimator result micro-pop on change ---------- */
  var estOut = doc.getElementById('estResult');
  if (estOut && !reduce) {
    var lastVal = estOut.textContent;
    new MutationObserver(function () {
      if (estOut.textContent !== lastVal) {
        lastVal = estOut.textContent;
        estOut.classList.remove('bump');
        void estOut.offsetWidth;
        estOut.classList.add('bump');
      }
    }).observe(estOut, { childList: true, characterData: true, subtree: true });
  }

  /* ---------- 4. Magnetic buttons ---------- */
  if (fineHover && !reduce) {
    doc.querySelectorAll(".next-button, .btn").forEach(function (btn) {
      var strength = 0.28;
      btn.addEventListener("pointermove", function (e) {
        var r = btn.getBoundingClientRect();
        var dx = e.clientX - (r.left + r.width / 2);
        var dy = e.clientY - (r.top + r.height / 2);
        btn.style.transform =
          "translate(" + dx * strength + "px," + dy * strength + "px)";
        btn.style.transition = "transform .15s ease-out";
      });
      btn.addEventListener("pointerleave", function () {
        btn.style.transition = "transform .45s cubic-bezier(.2,.9,.2,1.2)";
        btn.style.transform = "";
      });
    });
  }

  /* ---------- 5. Re-trigger shine sweep on hover (pure CSS below) ---------- */
})();
