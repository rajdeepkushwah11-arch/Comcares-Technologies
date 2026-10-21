/* ============================================================
   COMCARES — Homepage animation layer (assets/index.js)
   Self-contained, dependency-free. Respects prefers-reduced-motion.
   ============================================================ */
(function () {
  "use strict";

  var doc = document;
  var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var fineHover = window.matchMedia(
    "(hover: hover) and (pointer: fine)",
  ).matches;

  /* ---------- 0. Preloader ---------- */
  var pre = doc.getElementById("nxPreloader");
  function dismissPreloader() {
    if (!pre || pre.classList.contains("is-done")) return;
    pre.classList.add("is-done");
    /* start hero word reveal only after the curtain lifts */
    doc.querySelectorAll(".nx-hero h1 .nx-word").forEach(function (w, i) {
      setTimeout(
        function () {
          w.classList.add("in");
        },
        80 + i * 70,
      );
    });
  }
  if (pre) {
    if (reduce) {
      dismissPreloader();
    } else {
      window.addEventListener("load", function () {
        setTimeout(dismissPreloader, 500);
      });
      /* safety net: never trap the user behind the curtain */
      setTimeout(dismissPreloader, 3500);
    }
  }

  /* ---------- 1. Split headings into words ---------- */
  var HEADINGS = ".nx-hero h1, .nx-section h2, .nx-cta h2";
  doc.querySelectorAll(HEADINGS).forEach(function (h) {
    if (h.dataset.nxSplit) return;
    h.dataset.nxSplit = "1";
    var words = [];
    Array.prototype.forEach.call(h.childNodes, function (node) {
      if (node.nodeType === 3) {
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
    words.forEach(function (w) {
      if (w.br) {
        h.appendChild(doc.createElement("br"));
        return;
      }
      var span = doc.createElement("span");
      span.className = "nx-word " + w.cls;
      span.textContent = w.text;
      h.appendChild(span);
      h.appendChild(doc.createTextNode(" "));
    });
  });

  /* the em gradient animation must still apply to split words */
  doc.querySelectorAll(".nx-w-em").forEach(function (el) {
    el.style.background =
      "linear-gradient(100deg, var(--nx-accent) 10%, var(--nx-accent-2) 60%, var(--nx-accent-3) 100%)";
    el.style.webkitBackgroundClip = "text";
    el.style.backgroundClip = "text";
    el.style.webkitTextFillColor = "transparent";
  });

  /* ---------- 2. IntersectionObserver reveal system ---------- */
  var io =
    "IntersectionObserver" in window && !reduce
      ? new IntersectionObserver(
          function (entries) {
            entries.forEach(function (e) {
              if (e.isIntersecting) {
                e.target.classList.add("in");
                io.unobserve(e.target);
              }
            });
          },
          { threshold: 0.15 },
        )
      : null;

  function observe(el) {
    if (io) io.observe(el);
    else el.classList.add("in");
  }

  /* reveal targets: sections, cards, steps */
  doc
    .querySelectorAll(
      ".nx-reveal, .nx-card, .nx-step, .nx-show-card, .nx-ring-card, .nx-voice",
    )
    .forEach(function (el, i) {
      if (el.classList.contains("nx-voice"))
        return; /* marquee items stay put */
      el.classList.add("nx-reveal");
      el.style.setProperty("--nxd", (i % 4) * 0.08 + "s");
      observe(el);
    });

  /* ---------- 3. Count-up numbers ---------- */
  function easeOutCubic(p) {
    return 1 - Math.pow(1 - p, 3);
  }

  function animateCount(el) {
    var goal = parseInt(el.getAttribute("data-count"), 10);
    if (isNaN(goal)) return;
    var suffix = el.getAttribute("data-suffix") || "";
    el.setAttribute("aria-label", goal + suffix);
    var start = null;
    function tick(now) {
      if (start === null) start = now;
      var p = Math.min((now - start) / 2200, 1);
      el.textContent = Math.round(easeOutCubic(p) * goal) + suffix;
      if (p < 1) requestAnimationFrame(tick);
      else {
        el.textContent = goal + suffix;
        el.classList.add("done");
      }
    }
    el.textContent = "0" + suffix;
    requestAnimationFrame(tick);
  }

  var counters = doc.querySelectorAll("[data-count]");
  if (reduce || !("IntersectionObserver" in window)) {
    counters.forEach(function (el) {
      el.textContent =
        el.getAttribute("data-count") + (el.getAttribute("data-suffix") || "");
      el.classList.add("done");
    });
  } else {
    var cio = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (e) {
          if (e.isIntersecting) {
            animateCount(e.target);
            cio.unobserve(e.target);
          }
        });
      },
      { threshold: 0.4 },
    );
    counters.forEach(function (el) {
      cio.observe(el);
    });
  }

  /* ---------- 4. Ring stat cards ---------- */
  doc.querySelectorAll(".nx-ring-card").forEach(function (card) {
    var stat = parseFloat(card.getAttribute("data-stat")) || 100;
    /* map the stat onto a 0..1 fraction of the ring, clamped */
    var pct = Math.min(stat / 100, 1);
    if (pct <= 0) pct = 0.8;
    card.style.setProperty("--dash", (207.3 * (1 - pct)).toFixed(1));
  });

  /* ---------- 5. Cursor spotlight + sheen targets on cards ---------- */
  if (fineHover && !reduce) {
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
          doc.querySelectorAll(".nx-card").forEach(function (card) {
            var r = card.getBoundingClientRect();
            if (
              ev.clientX < r.left - 90 ||
              ev.clientX > r.right + 90 ||
              ev.clientY < r.top - 90 ||
              ev.clientY > r.bottom + 90
            )
              return;
            card.style.setProperty("--mx", ev.clientX - r.left + "px");
            card.style.setProperty("--my", ev.clientY - r.top + "px");
          });
        });
      },
      { passive: true },
    );
  }

  /* ---------- 6. 3D tilt on showcase cards ---------- */
  if (fineHover && !reduce) {
    doc.querySelectorAll(".nx-show-card").forEach(function (card) {
      card.addEventListener("mousemove", function (e) {
        var r = card.getBoundingClientRect();
        var x = (e.clientX - r.left) / r.width - 0.5;
        var y = (e.clientY - r.top) / r.height - 0.5;
        card.style.transform =
          "perspective(900px) rotateX(" +
          -y * 7 +
          "deg) rotateY(" +
          x * 9 +
          "deg) translateY(-5px)";
      });
      card.addEventListener("mouseleave", function () {
        card.style.transition = "transform .5s cubic-bezier(.22,.8,.24,1)";
        card.style.transform = "";
        setTimeout(function () {
          card.style.transition = "";
        }, 500);
      });
    });
  }

  /* ---------- 7. Magnetic buttons ---------- */
  if (fineHover && !reduce) {
    doc.querySelectorAll(".nx-btn").forEach(function (btn) {
      btn.addEventListener("pointermove", function (e) {
        var r = btn.getBoundingClientRect();
        var dx = e.clientX - (r.left + r.width / 2);
        var dy = e.clientY - (r.top + r.height / 2);
        btn.style.transition = "transform .15s ease-out";
        btn.style.transform =
          "translate(" + dx * 0.25 + "px," + dy * 0.25 + "px)";
      });
      btn.addEventListener("pointerleave", function () {
        btn.style.transition = "transform .45s cubic-bezier(.22,.8,.24,1.2)";
        btn.style.transform = "";
      });
    });
  }

  /* ---------- 8. Button ripple ink ---------- */
  doc.querySelectorAll(".nx-btn").forEach(function (btn) {
    btn.addEventListener("click", function (e) {
      if (reduce) return;
      var r = btn.getBoundingClientRect();
      var d = Math.max(r.width, r.height) * 2;
      var ink = doc.createElement("span");
      ink.className = "nx-ink";
      ink.style.width = ink.style.height = d + "px";
      ink.style.left = e.clientX - r.left - d / 2 + "px";
      ink.style.top = e.clientY - r.top - d / 2 + "px";
      btn.appendChild(ink);
      setTimeout(function () {
        ink.remove();
      }, 700);
    });
  });

  /* ---------- 9. Hero parallax on scroll ---------- */
  if (!reduce) {
    var aurora = doc.querySelectorAll(".nx-aurora-1, .nx-aurora-2");
    if (aurora.length) {
      window.addEventListener(
        "scroll",
        function () {
          var y = window.scrollY;
          aurora.forEach(function (o, i) {
            o.style.translate = "0 " + y * (i ? -0.1 : 0.06) + "px";
          });
        },
        { passive: true },
      );
    }
  }

  /* ---------- 10. Scroll progress bar + back to top ---------- */
  var bar = doc.getElementById("nxProgress");
  var toTop = doc.getElementById("nxToTop");
  function onScroll() {
    var h = doc.documentElement;
    var max = h.scrollHeight - h.clientHeight;
    var pct = max > 0 ? (h.scrollTop / max) * 100 : 0;
    if (bar) bar.style.width = pct + "%";
    if (toTop) toTop.classList.toggle("show", h.scrollTop > 600);
  }
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();
  if (toTop) {
    toTop.addEventListener("click", function (e) {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: reduce ? "auto" : "smooth" });
    });
  }

  /* ---------- 11. Smooth anchor scrolling ---------- */
  doc.querySelectorAll('a[href^="#"]').forEach(function (a) {
    a.addEventListener("click", function (e) {
      var id = a.getAttribute("href");
      if (id.length < 2) return;
      var target = doc.querySelector(id);
      if (!target) return;
      e.preventDefault();
      target.scrollIntoView({
        behavior: reduce ? "auto" : "smooth",
        block: "start",
      });
    });
  });

  /* ---------- 12. FAQ accordion (details + animation) ---------- */
  doc.querySelectorAll(".nx-faq-item").forEach(function (item) {
    var summary = item.querySelector("summary");
    if (!summary) return;
    summary.addEventListener("click", function () {
      /* brief class to allow CSS transitions on the panel */
      item.classList.add("nx-faq-touched");
    });
  });

  /* ---------- 13. Estimator ---------- */
  var pages = doc.getElementById("nxPages");
  var speed = doc.getElementById("nxSpeed");
  var pills = doc.querySelectorAll(".nx-pill");
  var out = doc.getElementById("nxResult");
  if (pages && speed && out) {
    var speedNames = ["Standard", "Fast-track", "Rush"];
    var BASE = 25000,
      PER_UNIT = 15000;
    var SPEED_MULT = [1, 1.25, 1.55];
    var mult = 1;

    /* Indian numbering: 1,00,000 style */
    function inr(n) {
      var rounded = Math.round(n / 500) * 500;
      var s = String(rounded);
      var last3 = s.slice(-3);
      var rest = s.slice(0, -3);
      if (rest)
        last3 = rest.replace(/\B(?=(\d{2})+(?!\d))/g, ",") + "," + last3;
      return "\u20B9" + last3;
    }

    function paintFill(slider) {
      var min = parseFloat(slider.min),
        max = parseFloat(slider.max);
      var val = parseFloat(slider.value);
      var pct = ((val - min) / (max - min)) * 100;
      slider.style.setProperty("--fill", pct + "%");
    }

    function estimate() {
      var units = parseInt(pages.value, 10);
      var low =
        (BASE + units * PER_UNIT) *
        mult *
        SPEED_MULT[parseInt(speed.value, 10)];
      var high = low * 1.45;
      doc.getElementById("nxPagesOut").textContent = units;
      doc.getElementById("nxSpeedOut").textContent =
        speedNames[parseInt(speed.value, 10)];
      out.textContent = inr(low) + " \u2013 " + inr(high);
      paintFill(pages);
      paintFill(speed);
    }

    pages.addEventListener("input", estimate);
    speed.addEventListener("input", estimate);
    pills.forEach(function (p) {
      p.addEventListener("click", function () {
        pills.forEach(function (q) {
          q.classList.remove("active");
        });
        p.classList.add("active");
        mult = parseFloat(p.getAttribute("data-mult")) || 1;
        estimate();
      });
    });
    estimate();
  }
})();
