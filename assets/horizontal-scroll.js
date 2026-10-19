(function () {
  "use strict";

  var media = window.matchMedia("(prefers-reduced-motion: reduce)");
  var scrollers = document.querySelectorAll(
    ".next-ticker, .next-voice-marquee, .marquee-band",
  );

  scrollers.forEach(function (scroller) {
    var track = scroller.firstElementChild;
    if (!track) return;

    var hovered = false;
    var focused = false;
    var dragging = false;
    var manualInput = false;
    var resumeTimer = null;
    var pointerStart = 0;
    var scrollStart = 0;
    var previousTime = null;
    var fractional = 0; /* accumulate sub-pixel steps that scrollLeft would truncate */
    var autoScroll = scroller.getAttribute("data-autoscroll") !== "false";
    var duration = parseFloat(scroller.getAttribute("data-scroll-duration")) || 40;
    var trackGap = parseFloat(window.getComputedStyle(track).columnGap) || 0;

    /* The author-provided copies are often narrower than a desktop viewport.
       Repeat the whole sequence until there is room for a seamless loop. */
    var sourceItems = Array.prototype.slice.call(track.children);
    function ensureLoopWidth() {
      if (!sourceItems.length) return;
      while (track.scrollWidth < scroller.clientWidth * 2) {
        sourceItems.forEach(function (item) {
          track.appendChild(item.cloneNode(true));
        });
      }
    }
    ensureLoopWidth();
    if (window.ResizeObserver) {
      new ResizeObserver(ensureLoopWidth).observe(scroller);
    } else {
      window.addEventListener("resize", ensureLoopWidth);
    }

    function isPaused() {
      return !autoScroll || dragging || manualInput || hovered || focused || media.matches || document.hidden;
    }

    scroller.addEventListener("pointerenter", function (event) {
      if (event.pointerType !== "touch") hovered = true;
    });
    scroller.addEventListener("pointerleave", function (event) {
      if (event.pointerType !== "touch") hovered = false;
    });
    scroller.addEventListener("focusin", function () {
      focused = true;
    });
    scroller.addEventListener("focusout", function (event) {
      if (!scroller.contains(event.relatedTarget)) focused = false;
    });

    scroller.addEventListener("pointerdown", function (event) {
      if (event.pointerType !== "mouse" || event.button !== 0) return;
      dragging = true;
      pointerStart = event.clientX;
      scrollStart = scroller.scrollLeft;
      scroller.classList.add("is-dragging");
      scroller.setPointerCapture(event.pointerId);
      event.preventDefault();
    });

    scroller.addEventListener("pointermove", function (event) {
      if (!dragging) return;
      scroller.scrollLeft = scrollStart - (event.clientX - pointerStart);
      wrapBothWays();
    });

    /* Seamless wrap in BOTH directions so a manual drag or swipe
       past either edge keeps looping instead of hitting a dead end. */
    function wrapBothWays() {
      var loopWidth = (track.scrollWidth + trackGap) / 2;
      if (loopWidth <= 0) return;
      if (scroller.scrollLeft >= loopWidth) {
        scroller.scrollLeft -= loopWidth;
      } else if (scroller.scrollLeft <= 0) {
        scroller.scrollLeft += loopWidth;
      }
    }
    /* scrollLeft 0 shows the same content as loopWidth, so a
       bidirectional wrap on scroll is visually invisible. */
    scroller.addEventListener("scroll", wrapBothWays, { passive: true });

    function pauseForManualInput() {
      manualInput = true;
      window.clearTimeout(resumeTimer);
      resumeTimer = window.setTimeout(function () {
        manualInput = false;
      }, 900);
    }

    scroller.addEventListener("touchstart", pauseForManualInput, { passive: true });
    scroller.addEventListener("touchmove", pauseForManualInput, { passive: true });
    scroller.addEventListener("touchend", pauseForManualInput, { passive: true });
    scroller.addEventListener("wheel", function (event) {
      pauseForManualInput();
      /* Support trackpads (deltaX), Shift+wheel, and a conventional wheel. */
      var distance = event.deltaX || event.deltaY;
      if (!distance || scroller.scrollWidth <= scroller.clientWidth) return;
      scroller.scrollLeft += distance;
      wrapBothWays();
      event.preventDefault();
    }, { passive: false });
    scroller.addEventListener("keydown", function (event) {
      var distance = event.key === "ArrowRight" ? 80 : event.key === "ArrowLeft" ? -80 : 0;
      if (!distance) return;
      pauseForManualInput();
      scroller.scrollLeft += distance;
      wrapBothWays();
      event.preventDefault();
    });

    function endDrag() {
      dragging = false;
      scroller.classList.remove("is-dragging");
    }
    scroller.addEventListener("pointerup", endDrag);
    scroller.addEventListener("pointercancel", endDrag);

    function scroll(now) {
      if (previousTime === null) previousTime = now;
      var elapsed = Math.min(now - previousTime, 50);
      previousTime = now;

      /* Include the centre flex gap so the repeated content aligns exactly. */
      var loopWidth = (track.scrollWidth + trackGap) / 2;
      if (!isPaused() && loopWidth > 0 && scroller.scrollWidth > scroller.clientWidth) {
        /* Accumulate fractional pixels; applying tiny fractions
           directly gets truncated by integer scrollLeft and the
           marquee appears to freeze. */
        fractional += (loopWidth / (duration * 1000)) * elapsed;
        if (fractional >= 1) {
          var whole = Math.floor(fractional);
          fractional -= whole;
          scroller.scrollLeft += whole;
          if (scroller.scrollLeft >= loopWidth) scroller.scrollLeft -= loopWidth;
        }
      } else {
        fractional = 0;
      }

      window.requestAnimationFrame(scroll);
    }

    window.requestAnimationFrame(scroll);
  });
})();
