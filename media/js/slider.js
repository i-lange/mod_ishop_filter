/*
 * @package    mod_ishop_filter
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/mod_ishop_filter
 * @copyright  (C) 2026 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

export const SLIDER_ENTRY_MARKER = "mod_ishop_filter.slider";

export class IshopFilterSliderManager {
  constructor() {
    this.sliders = new Map();
  }

  init(scope = document) {
    this.refresh(scope);
  }

  refresh(scope = document) {
    if (!scope) {
      return;
    }

    const ranges = scope.matches?.(".range") ? [scope] : Array.from(scope.querySelectorAll?.(".range") || []);
    ranges.forEach((range) => this.setup(range));
  }

  setup(range) {
    if (!range) {
      return;
    }

    if (this.sliders.has(range)) {
      this.sliders.get(range)?.refresh();
      return;
    }

    const minInput = range.querySelector(".range-min");
    const maxInput = range.querySelector(".range-max");
    const track = range.querySelector(".range-slider");
    const line = range.querySelector(".range-slider__line");
    const minPoint = range.querySelector(".range-slider__point--upper");
    const maxPoint = range.querySelector(".range-slider__point--lower");

    if (!minInput || !maxInput || !track || !line || !minPoint || !maxPoint) {
      return;
    }

    const state = {
      minInput,
      maxInput,
      track,
      line,
      minPoint,
      maxPoint,
      inputTimeout: null,
      observer: null,
    };

    const getBounds = () => {
      let min = this.parseBoundary(minInput.min, 0);
      let max = this.parseBoundary(maxInput.max, min + 1);

      if (max <= min) {
        max = min + 1;
      }

      return { min, max };
    };

    const update = () => {
      const bounds = getBounds();
      const minValue = this.coerceValue(minInput.value, bounds.min);
      const maxValue = this.coerceValue(maxInput.value, bounds.max);
      const span = bounds.max - bounds.min;

      if (span <= 0) {
        return;
      }

      const minPercent = this.toPercent(minValue, bounds.min, span);
      const maxPercent = this.toPercent(maxValue, bounds.min, span);

      line.style.left = `${minPercent}%`;
      line.style.right = `${100 - maxPercent}%`;
      minPoint.style.left = `${minPercent}%`;
      maxPoint.style.left = `${maxPercent}%`;

      if (minValue === maxValue) {
        minPoint.style.zIndex = "1";
        maxPoint.style.zIndex = "2";
      } else {
        minPoint.style.zIndex = "";
        maxPoint.style.zIndex = "";
      }
    };

    const normalizeInputs = () => {
      const bounds = getBounds();
      let minValue = this.parseOptionalValue(minInput.value);
      let maxValue = this.parseOptionalValue(maxInput.value);

      if (minValue === null || minValue < bounds.min) {
        minInput.value = "";
        minValue = bounds.min;
      }

      if (maxValue === null || maxValue > bounds.max) {
        maxInput.value = "";
        maxValue = bounds.max;
      }

      if (minValue > maxValue) {
        minInput.value = String(maxValue);
      }

      update();
    };

    const handleInput = () => {
      clearTimeout(state.inputTimeout);
      state.inputTimeout = setTimeout(normalizeInputs, 250);
    };

    const handleDrag = (event, point) => {
      const rect = track.getBoundingClientRect();
      const width = rect.width;

      if (width <= 0) {
        return;
      }

      const bounds = getBounds();
      const clientX = event.touches ? event.touches[0].clientX : event.clientX;
      const pointWidth = minPoint.offsetWidth || 0;
      let percent = ((clientX - rect.left) / width) * 100;
      percent = Math.max(0, Math.min(100, percent));

      const value = Math.round(bounds.min + (percent / 100) * (bounds.max - bounds.min));

      if (point === minPoint) {
        this.setMinValue(minInput, maxInput, bounds, value, percent, pointWidth, width);
        this.dispatchInput(minInput);
      } else {
        this.setMaxValue(minInput, maxInput, bounds, value, percent, pointWidth, width);
        this.dispatchInput(maxInput);
      }

      update();
    };

    minInput.addEventListener("input", handleInput);
    maxInput.addEventListener("input", handleInput);
    this.addDragListeners(minPoint, handleDrag);
    this.addDragListeners(maxPoint, handleDrag);

    if (typeof ResizeObserver !== "undefined") {
      state.observer = new ResizeObserver(update);
      state.observer.observe(track);
    }

    state.refresh = update;
    this.sliders.set(range, state);
    update();
  }

  addDragListeners(point, handleDrag) {
    const onMouseDown = () => {
      const onMouseMove = (event) => handleDrag(event, point);
      const onMouseUp = () => document.removeEventListener("mousemove", onMouseMove);

      document.addEventListener("mousemove", onMouseMove);
      document.addEventListener("mouseup", onMouseUp, { once: true });
    };

    const onTouchStart = () => {
      const onTouchMove = (event) => {
        event.preventDefault();
        handleDrag(event, point);
      };
      const onTouchEnd = () => document.removeEventListener("touchmove", onTouchMove);

      document.addEventListener("touchmove", onTouchMove, { passive: false });
      document.addEventListener("touchend", onTouchEnd, { once: true });
    };

    point.addEventListener("mousedown", onMouseDown);
    point.addEventListener("touchstart", onTouchStart, { passive: false });
  }

  setMinValue(minInput, maxInput, bounds, value, percent, pointWidth, width) {
    const maxValue = this.coerceValue(maxInput.value, bounds.max);
    const maxPercent = this.toPercent(maxValue, bounds.min, bounds.max - bounds.min);
    const safePercent = maxPercent - (pointWidth / width) * 100;
    const safeValue = Math.round(bounds.min + (safePercent / 100) * (bounds.max - bounds.min));

    minInput.value = String(percent > safePercent ? safeValue : value);

    if (value === bounds.min) {
      minInput.value = "";
    }
  }

  setMaxValue(minInput, maxInput, bounds, value, percent, pointWidth, width) {
    const minValue = this.coerceValue(minInput.value, bounds.min);
    const minPercent = this.toPercent(minValue, bounds.min, bounds.max - bounds.min);
    const safePercent = minPercent + (pointWidth / width) * 100;
    const safeValue = Math.round(bounds.min + (safePercent / 100) * (bounds.max - bounds.min));

    maxInput.value = String(percent < safePercent ? safeValue : value);

    if (value === bounds.max) {
      maxInput.value = "";
    }
  }

  toPercent(value, min, span) {
    return ((value - min) / span) * 100;
  }

  parseBoundary(value, fallback) {
    const parsed = Number.parseInt(String(value ?? ""), 10);
    return Number.isFinite(parsed) ? Math.max(0, parsed) : fallback;
  }

  parseOptionalValue(value) {
    if (value === null || value === undefined || value === "") {
      return null;
    }

    const parsed = Number(String(value).replace(",", "."));
    return Number.isFinite(parsed) ? Math.max(0, Math.round(parsed)) : null;
  }

  coerceValue(value, fallback) {
    const parsed = this.parseOptionalValue(value);
    return parsed === null ? fallback : parsed;
  }

  dispatchInput(input) {
    input.dispatchEvent(new Event("input", { bubbles: true }));
  }
}

if (typeof window !== "undefined") {
  // Глобальный менеджер остается тем же API, который использует front.js и существующая разметка.
  window.IshopFilterSlider = window.IshopFilterSlider || new IshopFilterSliderManager();
}

export function initIshopFilterSliders(root = globalThis.document) {
  // Функция нужна тестам и повторной инициализации отдельных DOM-фрагментов.
  if (!root || typeof window === "undefined") {
    return null;
  }

  window.IshopFilterSlider.init(root);

  return window.IshopFilterSlider;
}

if (typeof document !== "undefined") {
  // Сохраняем прежнее поведение entrypoint при загрузке страницы.
  document.addEventListener("DOMContentLoaded", () => {
    initIshopFilterSliders(document);
  });
}
