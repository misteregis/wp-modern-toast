(function () {
  class ModernToast {
    static _defaults = {
      duration: ModernToastSettings.duration ?? 4000,
      allowHTML: ModernToastSettings.allowHTML ?? false,
      useTypeColor: ModernToastSettings.useTypeColor ?? false
    };

    static isPlainObject(value) {
      if (Object.prototype.toString.call(value) !== "[object Object]") {
        return false;
      }

      const proto = Object.getPrototypeOf(value);

      return proto === null || proto === Object.prototype;
    }

    static _normalizeArgs(args) {
      let options = {};

      [...args].forEach(arg => {
        if (typeof arg === "string") options.type = arg;
        else if (typeof arg === "number") options.duration = arg;
        else if (typeof arg === "boolean") options.allowHTML = arg;
        else if (this.isPlainObject(arg)) options = { ...options, ...arg };
      });

      return { ...this._defaults, ...options };
    }

    static _show(message, ...args) {
      const { type, duration, allowHTML, useTypeColor } = this._normalizeArgs(args);

      const container = document.getElementById("mt-toast-container");
      if (!container) return;

      const toast = document.createElement("div");
      toast.classList.add("toast");

      if (type) {
        toast.classList.add(type);
      }

      if (useTypeColor) {
        toast.classList.add("use-type-color");
      }

      const msg = document.createElement("div");
      msg.classList.add("toast-message");

      if (allowHTML) {
        msg.innerHTML = message;
      } else {
        msg.textContent = message;
      }

      const close = document.createElement("div");
      close.innerHTML = "&times;";
      close.style.cursor = "pointer";
      close.style.fontSize = "16px";
      close.onclick = () => removeToast(toast);

      const progress = document.createElement("div");
      progress.classList.add("toast-progress");

      toast.appendChild(msg);
      toast.appendChild(close);
      toast.appendChild(progress);
      container.appendChild(toast);

      let totalDuration = duration;
      let remaining = duration;
      let startTime = null;
      let timeoutId = null;
      let animationFrameId = null;
      let isPaused = false;

      function updateProgress() {
        const elapsed = totalDuration - remaining;
        const ratio = elapsed / totalDuration;
        progress.style.transform = `scaleX(${1 - ratio})`;
      }

      function animate() {
        const now = Date.now();
        const delta = now - startTime;
        remaining -= delta;
        startTime = now;

        if (remaining <= 0) {
          progress.style.transform = "scaleX(0)";
          removeToast(toast);
          return;
        }

        updateProgress();
        animationFrameId = requestAnimationFrame(animate);
      }

      function startTimer() {
        if (!isPaused) return;
        isPaused = false;
        startTime = Date.now();
        timeoutId = setTimeout(() => removeToast(toast), remaining);
        animationFrameId = requestAnimationFrame(animate);
      }

      function pauseTimer() {
        if (isPaused) return;
        isPaused = true;
        clearTimeout(timeoutId);
        cancelAnimationFrame(animationFrameId);
      }

      function removeToast(toastElement) {
        toastElement.classList.add("hide");
        toastElement.addEventListener("animationend", () => {
          toastElement.remove();
        });
      }

      toast.addEventListener("mouseenter", pauseTimer);
      toast.addEventListener("mouseleave", startTimer);

      isPaused = true;
      startTimer();
    }

    // API Pública
    static show = (message, ...args) => this._show(message, ...args);
    static info = (message, ...args) => this._show(message, "info", ...args);
    static success = (message, ...args) => this._show(message, "success", ...args);
    static error = (message, ...args) => this._show(message, "error", ...args);
    static warn = (message, ...args) => this._show(message, "warn", ...args);
  }

  window.ModernToast = ModernToast;
})();