/*
 * @package    mod_ishop_filter
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/mod_ishop_filter
 * @copyright  (C) 2026 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

class IshopFilter {
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
    this.sefUrl = this.form.action || window.location.href;
    this.baseUrl = "";

    this.updateSubmitText(this.getInitialProductCount());
    this.bindEvents();
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
        this.debouncedSend();
      });

      if (input.type === "number" || input.type === "text") {
        input.addEventListener("input", () => this.debouncedSend());
        input.addEventListener("blur", () => this.roundNumberInput(input));
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

    this.refreshRangeSliders();
  }

  getAssociatedElements(selector) {
    const elements = Array.from(this.form.querySelectorAll(selector));
    const externalSelector = `[form="${this.formId}"]${selector}, [form="${this.formId}"] ${selector}`;

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

    if ((control.type === "checkbox" || control.type === "radio") && !control.checked) {
      return false;
    }

    return true;
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
  }

  updateSubmitText(productCount) {
    const label = this.getAssociatedElements("[data-filter-submit-text]")[0];
    if (!label) {
      return;
    }

    if (!this.submitTemplate) {
      label.textContent = String(productCount);
      return;
    }

    label.textContent = this.submitTemplate
      .replace("%s", String(productCount))
      .replace("%d", String(productCount));
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

document.addEventListener("DOMContentLoaded", () => {
  const forms = document.querySelectorAll('form[name="ishop_filter"]');
  window.iFilters = [];

  forms.forEach((form) => {
    const id = form.id;
    if (id) {
      window.iFilters.push(new IshopFilter(id));
    }
  });
});
