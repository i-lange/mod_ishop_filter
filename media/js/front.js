/*
 * @package    mod_ishop_filter
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/mod_ishop_filter
 * @copyright  (C) 2026 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

export const FRONT_ENTRY_MARKER = "mod_ishop_filter.front";

export class IshopFilter {
  constructor(formId) {
    this.form = document.querySelector(`form[name="ishop_filter"]#${formId}`);
    if (!this.form) {
      return;
    }

    this.formId = formId;
    this.currentRequest = null;
    this.debounceTimer = null;
    this.debounceDelay = 300;
    this.previewUrl = this.form.dataset.previewUrl || this.buildEndpoint("filter.preview");
    this.resetUrl = this.form.dataset.resetUrl || this.buildEndpoint("filter.reset");
    this.submitTemplate = this.form.dataset.submitTemplate || "";
    this.submitUnavailableText = this.form.dataset.submitUnavailableText || "";
    this.filterFromText = (this.form.dataset.filterFromText || "From").toLowerCase();
    this.filterToText = (this.form.dataset.filterToText || "To").toLowerCase();
    this.sefUrl = this.form.action || window.location.href;
    this.baseUrl = "";
    this.container =
      this.form.closest(".mod_ishop_filter") ||
      this.form.closest(".offcanvas") ||
      this.form.parentElement;
    this.activeTagsContainer = null;
    this.activeTags = [];

    this.updateSubmitText(this.getInitialProductCount());
    this.bindEvents();
    this.updateSelectedCounts();
    this.activeTagsContainer = this.getAssociatedElements("[data-filter-active-tags]")[0] || null;
    this.updateActiveTags();
  }

  bindEvents() {
    const inputs = this.form.querySelectorAll("input, select, textarea");
    inputs.forEach((input) => {
      if (this.isButtonControl(input)) {
        return;
      }

      input.addEventListener("change", () => {
        if (input.type === "number") {
          this.roundNumberInput(input);
        }
        if (this.isSelectedCountControl(input)) {
          this.updateSelectedCounts();
        }
        this.updateActiveTags();
        this.debouncedSend();
      });

      if (input.type === "number" || input.type === "text") {
        input.addEventListener("input", () => {
          this.updateActiveTags();
          this.debouncedSend();
        });
        input.addEventListener("blur", () => {
          this.roundNumberInput(input);
          this.updateActiveTags();
        });
      }
    });

    this.form.addEventListener("submit", (event) => {
      event.preventDefault();
      this.submit();
    });

    this.getAssociatedElements("[data-filter-reset]").forEach((resetBtn) => {
      resetBtn.addEventListener("click", (event) => {
        event.preventDefault();
        this.reset();
      });
    });

    this.getAssociatedElements("[data-filter-active-tags]").forEach((container) => {
      container.addEventListener("click", (event) => {
        const button = event.target.closest("[data-filter-tag-index]");
        if (!button || !container.contains(button)) {
          return;
        }

        event.preventDefault();
        this.removeActiveTag(this.toInteger(button.dataset.filterTagIndex));
      });
    });

    this.refreshRangeSliders();
  }

  getAssociatedElements(selector) {
    const elements = Array.from(this.form.querySelectorAll(selector));
    const externalSelector = `[form="${this.formId}"]${selector}, [form="${this.formId}"] ${selector}`;

    if (this.container) {
      this.container.querySelectorAll(selector).forEach((element) => {
        if (!elements.includes(element)) {
          elements.push(element);
        }
      });
    }

    document.querySelectorAll(externalSelector).forEach((element) => {
      if (!elements.includes(element)) {
        elements.push(element);
      }
    });

    return elements;
  }

  buildEndpoint(task) {
    let root = "";
    if (typeof Joomla !== "undefined" && Joomla.getOptions) {
      const paths = Joomla.getOptions("system.paths", {});
      root = paths.root || "";
    }

    return `${root}/index.php?option=com_ishop&task=${task}&format=json`;
  }

  getInitialProductCount() {
    const countEl = this.getCountElement();
    if (countEl) {
      return this.toInteger(countEl.dataset.count || countEl.textContent);
    }

    return this.toInteger(this.form.dataset.productCount);
  }

  getCountElement() {
    const previous = this.form.previousElementSibling;
    const localCount = previous ? previous.querySelector(".count-value") : null;

    if (localCount) {
      return localCount;
    }

    const container =
      this.form.closest(".mod_ishop_filter") ||
      this.form.closest(".offcanvas") ||
      this.form.closest(".offcanvas-body") ||
      this.form.parentElement;

    return container ? container.querySelector(".count-value") : null;
  }

  collectFormData() {
    const formData = new FormData();
    const controls = this.form.querySelectorAll("input, select, textarea");

    controls.forEach((control) => {
      if (!this.isSuccessfulControl(control)) {
        return;
      }

      if (control.tagName === "SELECT" && control.multiple) {
        Array.from(control.selectedOptions).forEach((option) => {
          formData.append(control.name, option.value);
        });
        return;
      }

      const value = control.type === "number" ? this.roundValue(control.value) : control.value;
      if (value === null || value === "") {
        return;
      }

      formData.append(control.name, value);
    });

    const categoryId = this.getContextInteger("categoryId", "id");
    if (categoryId > 0) {
      formData.set("category_id", String(categoryId));
    }

    const itemId = this.getContextInteger("itemId", "Itemid");
    if (itemId > 0) {
      formData.set("Itemid", String(itemId));
    }

    return formData;
  }

  isSuccessfulControl(control) {
    if (!control.name || control.disabled || this.isButtonControl(control)) {
      return false;
    }

    return !((control.type === "checkbox" || control.type === "radio") && !control.checked);
  }

  isButtonControl(control) {
    return ["submit", "button", "reset", "file"].includes(control.type);
  }

  getContextInteger(datasetKey, queryKey) {
    const fromDataset = this.toInteger(this.form.dataset[datasetKey]);
    if (fromDataset > 0) {
      return fromDataset;
    }

    const urlParams = new URLSearchParams(window.location.search);
    return this.toInteger(urlParams.get(queryKey));
  }

  debouncedSend() {
    clearTimeout(this.debounceTimer);
    this.debounceTimer = setTimeout(() => this.sendAjax(), this.debounceDelay);
  }

  submit() {
    if (this.isSubmitDisabled()) {
      return;
    }

    clearTimeout(this.debounceTimer);
    this.sendAjax({ redirectOnSuccess: true });
  }

  sendAjax(options = {}) {
    const data = this.collectFormData();

    if (!data.get("category_id")) {
      if (options.redirectOnSuccess) {
        this.redirectTo(this.sefUrl);
      }
      return Promise.resolve(null);
    }

    const csrfToken = this.getCsrfToken();
    if (!csrfToken) {
      if (options.redirectOnSuccess) {
        this.redirectTo(this.sefUrl);
      }
      return Promise.resolve(null);
    }

    data.set(csrfToken, "1");
    this.abortCurrentRequest();
    this.showLoading();

    return new Promise((resolve) => {
      const request = Joomla.request({
        url: this.previewUrl,
        method: "POST",
        headers: {
          "Cache-Control": "no-cache",
          "Content-Type": "application/x-www-form-urlencoded",
        },
        data: this.encodeFormData(data),
        onSuccess: (response) => {
          const parsed = this.parseResponse(response);
          if (parsed && parsed.success && parsed.data) {
            this.updateUI(parsed.data);

            if (options.redirectOnSuccess) {
              this.openFilterResult(parsed.data.sefUrl || this.sefUrl);
            }

            resolve(parsed.data);
            return;
          }

          console.error("IshopFilter: Invalid preview response", parsed);
          if (options.redirectOnSuccess) {
            this.redirectTo(this.sefUrl);
          }
          resolve(null);
        },
        onError: (xhr) => {
          console.error("IshopFilter: AJAX request failed", xhr);
          if (options.redirectOnSuccess) {
            this.redirectTo(this.sefUrl);
          }
          resolve(null);
        },
        onComplete: () => {
          if (this.currentRequest === request) {
            this.currentRequest = null;
            this.hideLoading();
          }
        },
      });

      this.currentRequest = request;
    });
  }

  reset() {
    clearTimeout(this.debounceTimer);
    this.abortCurrentRequest();

    const categoryId = this.getContextInteger("categoryId", "id");
    const csrfToken = this.getCsrfToken();

    if (!categoryId || !csrfToken) {
      this.redirectTo(this.baseUrl || this.form.action);
      return;
    }

    const data = new FormData();
    data.set("category_id", String(categoryId));
    data.set(csrfToken, "1");

    const itemId = this.getContextInteger("itemId", "Itemid");
    if (itemId > 0) {
      data.set("Itemid", String(itemId));
    }

    this.showLoading();

    const request = Joomla.request({
      url: this.resetUrl,
      method: "POST",
      headers: {
        "Cache-Control": "no-cache",
        "Content-Type": "application/x-www-form-urlencoded",
      },
      data: this.encodeFormData(data),
      onSuccess: (response) => {
        const parsed = this.parseResponse(response);
        if (parsed && parsed.success && parsed.data && parsed.data.baseUrl) {
          this.redirectTo(parsed.data.baseUrl);
          return;
        }

        console.error("IshopFilter: Invalid reset response", parsed);
        this.redirectTo(this.baseUrl || this.form.action);
      },
      onError: (xhr) => {
        console.error("IshopFilter: Reset request failed", xhr);
        this.redirectTo(this.baseUrl || this.form.action);
      },
      onComplete: () => {
        if (this.currentRequest === request) {
          this.currentRequest = null;
          this.hideLoading();
        }
      },
    });

    this.currentRequest = request;
  }

  updateUI(data) {
    const productCount = this.toInteger(data.productCount);
    const countEl = this.getCountElement();
    if (countEl) {
      countEl.textContent = productCount;
      countEl.dataset.count = productCount;
    }

    this.form.dataset.productCount = String(productCount);
    this.updateSubmitText(productCount);
    this.sefUrl = data.sefUrl || this.sefUrl;
    this.baseUrl = data.baseUrl || this.baseUrl;

    const available = data.availableOptions || {};

    this.updateCheckboxes(
      'input[name="manufacturers[]"]',
      available.manufacturers || [],
    );

    this.updateCheckboxes(
      'input[name="warehouses[]"]',
      available.warehouses || [],
    );

    if (available.ishop_fields) {
      this.updateFields(available.ishop_fields);
    }

    if (available.price_range) {
      this.updateRangePair("min_price", "max_price", available.price_range);
    }

    if (available.sizes) {
      if (available.sizes.width) {
        this.updateRangePair("min_width", "max_width", available.sizes.width);
      }
      if (available.sizes.height) {
        this.updateRangePair("min_height", "max_height", available.sizes.height);
      }
      if (available.sizes.depth) {
        this.updateRangePair("min_depth", "max_depth", available.sizes.depth);
      }
      if (available.sizes.weight) {
        this.updateRangePair("min_weight", "max_weight", available.sizes.weight);
      }
    }

    this.refreshRangeSliders();
    this.updateActiveTags();
  }

  updateSubmitText(productCount) {
    const label = this.getAssociatedElements("[data-filter-submit-text]")[0];
    const isUnavailable = productCount === 0;

    this.getAssociatedElements("[data-filter-submit]").forEach((button) => {
      button.disabled = isUnavailable;
      button.setAttribute("aria-disabled", isUnavailable ? "true" : "false");
    });

    this.getAssociatedElements("[data-filter-submit-hint]").forEach((hint) => {
      hint.hidden = !isUnavailable;
    });

    if (!label) {
      return;
    }

    if (isUnavailable && this.submitUnavailableText) {
      label.textContent = this.submitUnavailableText;
    } else if (!this.submitTemplate) {
      label.textContent = String(productCount);
    } else {
      label.textContent = this.submitTemplate
        .replace("%s", String(productCount))
        .replace("%d", String(productCount));
    }
  }

  isSubmitDisabled() {
    return this.getAssociatedElements("[data-filter-submit]").some((button) => button.disabled);
  }

  updateCheckboxes(selector, availableIds) {
    const available = new Set((availableIds || []).map((id) => String(id)));
    const checkboxes = Array.from(this.form.querySelectorAll(selector)).filter(
      (input) => input.type !== "hidden",
    );

    checkboxes.forEach((input) => {
      const isAvailable = available.has(String(input.value));
      const isEnabled = isAvailable || input.checked;
      input.disabled = !isEnabled;

      const label =
        input.closest("label") ||
        this.form.querySelector(`label[for="${input.id}"]`);
      if (label) {
        label.classList.toggle("disabled", !isEnabled);
      }

      const parent = input.closest(".form-check") || input.closest("li");
      if (parent) {
        parent.classList.toggle("filter-option-disabled", !isEnabled);
      }
    });

    this.updateGroupVisibility(selector);
  }

  updateFields(fieldsData) {
    const returnedFieldIds = new Set(Object.keys(fieldsData));

    Object.keys(fieldsData).forEach((fieldId) => {
      const fieldInfo = fieldsData[fieldId];

      if (fieldInfo.type === "range") {
        this.updateRangePair(
          `ishop_fields[${fieldId}][min]`,
          `ishop_fields[${fieldId}][max]`,
          fieldInfo,
        );
      } else if (fieldInfo.type === "list") {
        this.updateCheckboxes(
          `input[name="ishop_fields[${fieldId}][]"]`,
          Object.keys(fieldInfo.values || {}),
        );
      } else if (fieldInfo.type === "boolean") {
        this.updateBooleanField(fieldId, true);
      }
    });

    this.disableMissingFieldOptions(returnedFieldIds);
  }

  updateSelectedCounts() {
    const selectedCounts = this.collectSelectedCounts();
    this.form.querySelectorAll("[data-selected-count]").forEach((counter) => {
      const fieldId = counter.dataset.fieldId;
      const count = selectedCounts.get(String(fieldId)) || 0;

      counter.textContent = String(count);
      counter.dataset.count = String(count);
      counter.hidden = count === 0;
      counter.classList.toggle("is-empty", count === 0);
    });
  }

  collectSelectedCounts() {
    const counts = new Map();

    this.form
      .querySelectorAll('input[name^="ishop_fields["][name$="[]"]')
      .forEach((input) => {
        const match = input.name.match(/^ishop_fields\[(\d+)]\[]$/);
        if (!match || !input.checked) {
          return;
        }

        const fieldId = match[1];
        counts.set(fieldId, (counts.get(fieldId) || 0) + 1);
      });

    return counts;
  }

  updateActiveTags() {
    if (!this.activeTagsContainer) {
      return;
    }

    this.activeTags = this.collectActiveTags();
    this.activeTagsContainer.textContent = "";
    this.activeTagsContainer.hidden = this.activeTags.length === 0;

    this.activeTags.forEach((tag, index) => {
      const button = document.createElement("button");
      button.type = "button";
      button.className = "filter-active-tag";
      button.dataset.filterTagIndex = String(index);
      button.setAttribute("aria-label", tag.label);

      const label = document.createElement("span");
      label.className = "filter-active-tag__label";
      label.textContent = tag.label;

      const icon = document.createElement("span");
      icon.className = "filter-active-tag__remove";
      icon.setAttribute("aria-hidden", "true");
      icon.textContent = "\u00d7";

      button.append(label, icon);
      this.activeTagsContainer.append(button);
    });
  }

  collectActiveTags() {
    return [
      ...this.collectCheckboxTags(),
      ...this.collectRangeTags(),
    ];
  }

  collectCheckboxTags() {
    const tags = [];

    this.form.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach((input) => {
      if (!input.name || !input.checked || input.disabled || this.isButtonControl(input)) {
        return;
      }

      const label = this.getControlLabel(input);
      if (!label) {
        return;
      }

      tags.push({
        label,
        controls: [input],
      });
    });

    return tags;
  }

  collectRangeTags() {
    const groups = new Map();

    this.form.querySelectorAll('input[type="number"], input[type="text"]').forEach((input) => {
      if (!input.name || input.disabled || this.isButtonControl(input)) {
        return;
      }

      const value = this.roundValue(input.value);
      if (value === null || value === "") {
        return;
      }

      const rangePart = this.getRangePart(input.name);
      if (!rangePart) {
        return;
      }

      const group = groups.get(rangePart.key) || {
        label: this.getRangeLabel(input),
        minInput: null,
        maxInput: null,
        minValue: "",
        maxValue: "",
      };

      if (rangePart.part === "min") {
        group.minInput = input;
        group.minValue = value;
      } else {
        group.maxInput = input;
        group.maxValue = value;
      }

      if (!group.label) {
        group.label = this.getRangeLabel(input);
      }

      groups.set(rangePart.key, group);
    });

    return Array.from(groups.values())
      .filter((group) => group.label && (group.minValue !== "" || group.maxValue !== ""))
      .map((group) => ({
        label: this.formatRangeTagLabel(group),
        controls: [group.minInput, group.maxInput].filter(Boolean),
      }));
  }

  getRangePart(name) {
    const directMatch = name.match(/^(min|max)_(.+)$/);
    if (directMatch) {
      return {
        key: directMatch[2],
        part: directMatch[1],
      };
    }

    const fieldMatch = name.match(/^ishop_fields\[(\d+)]\[(min|max)]$/);
    if (fieldMatch) {
      return {
        key: `field-${fieldMatch[1]}`,
        part: fieldMatch[2],
      };
    }

    return null;
  }

  getRangeLabel(input) {
    if (input.dataset.filterLabel) {
      return input.dataset.filterLabel.trim();
    }

    const range = input.closest(".range");
    const title = range?.previousElementSibling;
    return title ? title.textContent.replace(/:$/, "").trim() : "";
  }

  formatRangeTagLabel(group) {
    if (group.minValue !== "" && group.maxValue !== "") {
      return `${group.label} ${this.filterFromText} ${group.minValue} ${this.filterToText} ${group.maxValue}`;
    }

    if (group.minValue !== "") {
      return `${group.label} ${this.filterFromText} ${group.minValue}`;
    }

    return `${group.label} ${this.filterToText} ${group.maxValue}`;
  }

  getControlLabel(input) {
    const label =
      input.closest("label") ||
      this.form.querySelector(`label[for="${input.id}"]`);

    return label ? label.textContent.trim() : "";
  }

  removeActiveTag(index) {
    const tag = this.activeTags[index];
    if (!tag) {
      return;
    }

    tag.controls.forEach((control) => {
      if (!control) {
        return;
      }

      if (control.type === "checkbox" || control.type === "radio") {
        control.checked = false;
      } else {
        control.value = "";
      }

      control.dispatchEvent(new Event("change", { bubbles: true }));
    });

    this.updateSelectedCounts();
    this.updateActiveTags();
    this.refreshRangeSliders();
    this.debouncedSend();
  }

  isSelectedCountControl(control) {
    return control.type === "checkbox" && /^ishop_fields\[\d+]\[]$/.test(control.name || "");
  }

  updateBooleanField(fieldId, available) {
    const input = this.form.querySelector(`input[type="checkbox"][name="ishop_fields[${fieldId}]"]`);
    if (!input) {
      return;
    }

    const isEnabled = available || input.checked;
    input.disabled = !isEnabled;

    const label =
      input.closest("label") ||
      this.form.querySelector(`label[for="${input.id}"]`);
    if (label) {
      label.classList.toggle("disabled", !isEnabled);
    }

    const parent = input.closest(".form-check") || input.closest("li");
    if (parent) {
      parent.classList.toggle("filter-option-disabled", !isEnabled);
    }
  }

  disableMissingFieldOptions(returnedFieldIds) {
    const missingListIds = new Set();
    const missingBooleanIds = new Set();

    this.form.querySelectorAll('input[name^="ishop_fields["]').forEach((input) => {
      const match = input.name.match(/^ishop_fields\[(\d+)]/);
      if (!match || returnedFieldIds.has(match[1])) {
        return;
      }

      if (input.name.endsWith("[]")) {
        missingListIds.add(match[1]);
      } else if (input.type === "checkbox") {
        missingBooleanIds.add(match[1]);
      }
    });

    missingListIds.forEach((fieldId) => {
      this.updateCheckboxes(`input[name="ishop_fields[${fieldId}][]"]`, []);
    });

    missingBooleanIds.forEach((fieldId) => {
      this.updateBooleanField(fieldId, false);
    });
  }

  updateRangePair(minName, maxName, range) {
    const min = this.toInteger(range.min);
    const max = this.toInteger(range.max);
    const minInput = this.form.querySelector(`input[name="${minName}"]`);
    const maxInput = this.form.querySelector(`input[name="${maxName}"]`);

    if (minInput) {
      minInput.min = min;
      minInput.max = max;
      minInput.placeholder = min;
      minInput.step = 1;
    }

    if (maxInput) {
      maxInput.min = min;
      maxInput.max = max;
      maxInput.placeholder = max;
      maxInput.step = 1;
    }

    this.refreshRangeSliders(minInput?.closest(".range") || maxInput?.closest(".range"));
  }

  updateGroupVisibility(selector) {
    const checkboxes = Array.from(this.form.querySelectorAll(selector)).filter(
      (input) => input.type !== "hidden",
    );
    if (!checkboxes.length) {
      return;
    }

    const group =
      checkboxes[0].closest("ul")?.closest("li") ||
      checkboxes[0].closest("[data-panel]") ||
      checkboxes[0].closest("li");

    if (!group) {
      return;
    }

    const allDisabled = checkboxes.every((checkbox) => checkbox.disabled);
    group.classList.toggle("filter-group-empty", allDisabled);
  }

  roundNumberInput(input) {
    const value = this.roundValue(input.value);
    if (value !== null && value !== "") {
      input.value = value;
    }
  }

  roundValue(value) {
    if (value === null || value === undefined || value === "") {
      return "";
    }

    const number = Number(String(value).replace(",", "."));
    if (!Number.isFinite(number)) {
      return null;
    }

    return String(Math.max(0, Math.round(number)));
  }

  toInteger(value) {
    const number = Number(String(value ?? "0").replace(",", "."));
    return Number.isFinite(number) ? Math.max(0, Math.round(number)) : 0;
  }

  getCsrfToken() {
    if (typeof Joomla === "undefined" || !Joomla.getOptions) {
      return "";
    }

    return Joomla.getOptions("csrf.token", "");
  }

  encodeFormData(formData) {
    const params = new URLSearchParams();
    formData.forEach((value, key) => {
      params.append(key, value);
    });

    return params.toString();
  }

  parseResponse(response) {
    try {
      return typeof response === "string" ? JSON.parse(response) : response;
    } catch (error) {
      console.error("IshopFilter: Failed to parse response", error);
      return null;
    }
  }

  abortCurrentRequest() {
    if (this.currentRequest && typeof this.currentRequest.abort === "function") {
      this.currentRequest.abort();
    }

    this.currentRequest = null;
  }

  redirectTo(url) {
    if (url) {
      window.location.href = url;
    }
  }

  openFilterResult(url) {
    if (this.hasRawFilterQuery(url)) {
      this.submitNative();
      return;
    }

    this.redirectTo(url);
  }

  hasRawFilterQuery(url) {
    if (!url) {
      return false;
    }

    let parsedUrl;
    try {
      parsedUrl = new URL(url, window.location.origin);
    } catch (error) {
      return false;
    }

    const rawFilterKeys = [
      "good_price",
      "manufacturers",
      "max_depth",
      "max_height",
      "max_price",
      "max_weight",
      "max_width",
      "min_depth",
      "min_height",
      "min_price",
      "min_weight",
      "min_width",
      "warehouses",
    ];

    for (const key of parsedUrl.searchParams.keys()) {
      if (key === "ishop_fields" || key.startsWith("ishop_fields[")) {
        return true;
      }

      const normalizedKey = key.endsWith("[]") ? key.slice(0, -2) : key;
      if (rawFilterKeys.includes(normalizedKey)) {
        return true;
      }
    }

    return false;
  }

  submitNative() {
    this.hideLoading();
    HTMLFormElement.prototype.submit.call(this.form);
  }

  showLoading() {
    const overlay = this.form.querySelector(".filter-loading-overlay");
    if (overlay) {
      overlay.style.display = "flex";
    }
  }

  hideLoading() {
    const overlay = this.form.querySelector(".filter-loading-overlay");
    if (overlay) {
      overlay.style.display = "none";
    }
  }

  refreshRangeSliders(scope = this.form) {
    if (window.IshopFilterSlider?.refresh) {
      window.IshopFilterSlider.refresh(scope);
    }
  }
}

export function initIshopFilters(root = globalThis.document) {
  // Инициализируем только переданный DOM scope, чтобы импорт в тестах не имел побочных эффектов.
  if (!root?.querySelectorAll) {
    return [];
  }

  const forms = root.querySelectorAll('form[name="ishop_filter"]');
  const filters = [];

  forms.forEach((form) => {
    const id = form.id;
    if (id) {
      filters.push(new IshopFilter(id));
    }
  });

  if (root === globalThis.document && typeof window !== "undefined") {
    window.iFilters = filters;
  }

  return filters;
}

if (typeof window !== "undefined") {
  // Сохраняем глобальный доступ для старых интеграций и запускаем прежнюю автоинициализацию.
  window.IshopFilter = IshopFilter;
  document.addEventListener("DOMContentLoaded", () => initIshopFilters(document));
}
