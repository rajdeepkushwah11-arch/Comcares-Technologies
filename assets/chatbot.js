/* ============ COMCARES assistant — state-driven chat logic ============ */
/* Honest scripted assistant; talks to api/chatbot.php (PDO-backed). */
(function () {
  "use strict";
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
    /* still fully functional, animations disabled via CSS */
  }

  var root = document.getElementById("ccChat");
  if (!root) return;

  var launcher = root.querySelector(".cc-launcher");
  var panel = root.querySelector(".cc-panel");
  var body = root.querySelector(".cc-body");
  var form = root.querySelector(".cc-form");
  var input = root.querySelector(".cc-input");
  var sendBtn = root.querySelector(".cc-send");
  var closeBtn = root.querySelector(".cc-close");
  var avatar = root.querySelector(".cc-avatar");
  var statusEl = root.querySelector(".cc-titles span");
  var dot = root.querySelector(".cc-dot");
  var hint = root.querySelector(".cc-hint");
  var lastFocus = null;
  var pending = null; // AbortController for the in-flight request
  var state = "idle"; // idle | open | waiting
  var sessionId = null;

  var QUICK = [
    "What services do you offer?",
    "Tell me about School Sathi",
    "How does pricing work?",
    "How can I contact the team?",
  ];

  /* ---------- availability check (honest online dot) ---------- */
  var online = null;
  function probe() {
    var ctrl = new AbortController();
    var t = setTimeout(function () {
      ctrl.abort();
    }, 6000);
    fetch("api/chatbot.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        message: "ping",
        session_id: sessionId || undefined,
      }),
      signal: ctrl.signal,
    })
      .then(function (r) {
        clearTimeout(t);
        return r.json();
      })
      .then(function (d) {
        online = !!(d && (d.ok || (d.error && d.error.type === "validation")));
        /* 'ping' returns a fallback reply (ok) or validation error if blocked — either means reachable */
        if (d && d.session_id && !sessionId) sessionId = d.session_id;
        if (dot) dot.classList.toggle("is-online", online);
        if (statusEl)
          statusEl.textContent = online
            ? "Online — replies instantly"
            : "Currently unavailable";
      })
      .catch(function () {
        clearTimeout(t);
        online = false;
        if (dot) dot.classList.remove("is-online");
        if (statusEl) statusEl.textContent = "Currently unavailable";
      });
  }
  probe();

  /* ---------- markup helpers (safe rendering, no raw HTML injection) ---------- */
  function renderText(el, text) {
    // escape, then apply light formatting: **bold**, [label](url), newlines
    var escaped = text
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\*\*(.+?)\*\*/g, "<strong>$1</strong>")
      .replace(
        /\[([^\]]+)\]\(((?:https?:\/\/|[\w\/.\-#?=&%]+))\)/g,
        function (m, label, url) {
          var safe = /^(https?:\/\/|[\w\/.\-#?=&%]+$)/.test(url) ? url : "#";
          var ext = /^https?:\/\//.test(safe);
          return (
            '<a href="' +
            safe +
            '"' +
            (ext ? ' target="_blank" rel="noopener"' : "") +
            ">" +
            label +
            "</a>"
          );
        },
      )
      .replace(/\n/g, "<br>");
    el.innerHTML = escaped;
  }

  function addBubble(role, text, cls) {
    var div = document.createElement('div');
    div.className = "cc-msg " + role + (cls ? " " + cls : "");
    if (role === "bot") renderText(div, text);
    else div.textContent = text;
    body.appendChild(div);
    scrollToBottom();
    return div;
  }

  function scrollToBottom(force) {
    // Don't fight the user reading older messages
    var nearBottom =
      body.scrollHeight - body.scrollTop - body.clientHeight < 140;
    if (nearBottom || force) body.scrollTop = body.scrollHeight;
  }

  /* ---------- typing indicator ---------- */
  var typingEl = null;
  function showTyping() {
    typingEl = document.createElement("div");
    typingEl.className = "cc-msg bot";
    typingEl.setAttribute("aria-label", "Assistant is typing");
    typingEl.innerHTML = '<span class="cc-typing"><i></i><i></i></span>';
    body.appendChild(typingEl);
    scrollToBottom(true);
    if (avatar) avatar.classList.add("thinking");
    if (statusEl) statusEl.textContent = "Typing…";
  }
  function hideTyping() {
    if (typingEl) typingEl.remove();
    typingEl = null;
    if (avatar) avatar.classList.remove("thinking");
    if (statusEl)
      statusEl.textContent = online
        ? "Online — replies instantly"
        : "Currently unavailable";
  }

  /* ---------- quick replies ---------- */
  function showChips(container) {
    if (!container) return;
    QUICK.forEach(function (q, i) {
      var b = document.createElement("button");
      b.type = "button";
      b.className = "cc-chip";
      b.style.animationDelay = 0.08 * i + "s";
      b.textContent = q;
      b.addEventListener("click", function () {
        send(q, container);
      });
      container.appendChild(b);
    });
  }
  function clearChips() {
    root.querySelectorAll(".cc-chips").forEach(function (c) {
      c.remove();
    });
  }

  /* ---------- send / receive ---------- */
  function send(text, chipsContainer) {
    if (state === "waiting") return; // prevent duplicate submission
    text = (text || "").trim();
    if (!text) {
      input.focus();
      return;
    }

    if (chipsContainer) chipsContainer.remove();
    clearChips();
    addBubble("user", text);
    input.value = "";
    autoGrow();

    state = "waiting";
    sendBtn.disabled = true;
    sendBtn.classList.add("loading");
    showTyping();

    pending = new AbortController();
    var ctrl = pending;
    var timedOut = false;
    var timer = setTimeout(function () {
      timedOut = true;
      ctrl.abort();
    }, 20000);

    fetch("api/chatbot.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ message: text, session_id: sessionId }),
      signal: ctrl.signal,
    })
      .then(function (r) {
        clearTimeout(timer);
        return r.json().catch(function () {
          throw {
            type: "server",
            message:
              "The assistant service returned an unexpected response. Please try again.",
          };
        });
      })
      .then(function (d) {
        hideTyping();
        if (ctrl.aborted || timedOut) return;
        if (d && d.ok && typeof d.reply === "string") {
          if (d.session_id) sessionId = d.session_id;
          online = true;
          if (dot) dot.classList.add("is-online");
          var wrap = addBubble("bot", d.reply);
          var chips = document.createElement("div");
          chips.className = "cc-chips";
          wrap.insertAdjacentElement("afterend", chips);
          showChips(chips);
          state = "open";
        } else {
          showError(
            d && d.error && d.error.message
              ? d.error.message
              : "Something went wrong. Please try again.",
            text,
          );
        }
      })
      .catch(function (err) {
        clearTimeout(timer);
        hideTyping();
        if (ctrl.aborted && !timedOut) {
          /* user cancelled — restore their message for editing */
          showError(
            "Request cancelled. Your message is still below — press Enter to send again.",
            text,
            true,
          );
          input.value = text;
        } else if (timedOut) {
          online = false;
          if (dot) dot.classList.remove("is-online");
          showError(
            "The assistant took too long to respond. Please check your connection and try again.",
            text,
          );
        } else {
          online = false;
          if (dot) dot.classList.remove("is-online");
          showError(
            "Network problem — could not reach the assistant. Please try again.",
            text,
          );
        }
      })
      .finally(function () {
        pending = null;
        state = "open";
        sendBtn.disabled = false;
        sendBtn.classList.remove("loading");
      });
  }

  function showError(message, originalText, keepInput) {
    var div = addBubble("error", message, "shake");
    if (originalText && !keepInput) {
      var retry = document.createElement("button");
      retry.type = "button";
      retry.className = "cc-chip";
      retry.textContent = "↻ Retry";
      retry.addEventListener("click", function () {
        div.remove();
        send(originalText);
      });
      div.appendChild(document.createElement("br"));
      div.appendChild(retry);
    }
    scrollToBottom(true);
  }

  /* ---------- open / close with focus management ---------- */
  function openChat() {
    if (state === "open") return;
    lastFocus = document.activeElement;
    root.classList.add("open");
    launcher.setAttribute("aria-expanded", "true");
    hideHint();
    state = "open";

    if (!body.children.length) {
      // First open: greeting + chips, in a controlled sequence
      setTimeout(function () {
        addBubble(
          "bot",
          "Hi there! 👋 I'm the COMCARES assistant. Ask me about our **services**, our product **School Sathi**, **pricing**, or **how to reach** the team.",
        );
        var chips = document.createElement("div");
        chips.className = "cc-chips";
        body.insertAdjacentElement("afterend", chips);
        showChips(chips);
      }, 220);
    }
    setTimeout(function () {
      input.focus();
    }, 340);
    document.addEventListener("keydown", onEscape);
  }

  function closeChat() {
    root.classList.remove("open");
    launcher.setAttribute("aria-expanded", "false");
    if (pending) {
      pending.abort();
      pending = null;
    }
    state = "idle";
    document.removeEventListener("keydown", onEscape);
    if (lastFocus && lastFocus.focus) lastFocus.focus();
  }

  function onEscape(e) {
    if (e.key === "Escape") {
      closeChat();
    }
  }

  /* occasional welcome hint (max once per 24h, never while open) */
  function maybeHint() {
    try {
      var last = parseInt(sessionStorage.getItem("ccHintAt") || "0", 10);
      if (Date.now() - last < 24 * 3600 * 1000) return;
      setTimeout(function () {
        if (state !== "open" && hint) {
          hint.classList.add("show");
          setTimeout(hideHint, 7000);
          sessionStorage.setItem("ccHintAt", String(Date.now()));
        }
      }, 6000);
    } catch (e) {
      /* storage unavailable — skip hint */
    }
  }
  function hideHint() {
    if (hint) hint.classList.remove("show");
  }

  /* ---------- wiring ---------- */
  launcher.addEventListener("click", function () {
    state === "open" ? closeChat() : openChat();
  });
  closeBtn.addEventListener("click", closeChat);
  hint.addEventListener("click", openChat);

  form.addEventListener("submit", function (e) {
    e.preventDefault();
    send(input.value);
  });

  input.addEventListener("keydown", function (e) {
    if (e.key === "Enter" && !e.shiftKey) {
      e.preventDefault();
      send(input.value);
    }
    /* Shift+Enter inserts a newline naturally in the textarea */
  });

  function autoGrow() {
    input.style.height = "auto";
    input.style.height = Math.min(input.scrollHeight, 96) + "px";
  }
  input.addEventListener("input", autoGrow);

  /* pause breathing while tab hidden (battery friendly) */
  document.addEventListener("visibilitychange", function () {
    if (!root.classList.contains("open")) {
      launcher.style.animationPlayState = document.hidden
        ? "paused"
        : "running";
    }
  });

  maybeHint();
})();
