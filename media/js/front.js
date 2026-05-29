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

    this.bindEvents();
  }

  bindEvents() {
    const inputs = this.form.querySelectorAll("input, select, textarea");
    inputs.forEach((input) => {
      if (input.type === "submit" || input.type === "button") {
        return;
      }
      input.addEventListener("change", () => this.debouncedSend());
      if (input.type === "number" || input.type === "text") {
        input.addEventListener("input", () => this.debouncedSend());
      }
    });

    const resetBtn = document.getElementById("ishop-filter-reset");
    if (resetBtn) {
      resetBtn.addEventListener("click", (e) => {
        e.preventDefault();
        this.reset();
      });
    }
  }

  collectFormData() {
    const formData = {};
    const inputs = this.form.querySelectorAll("input, select, textarea");

    inputs.forEach((input) => {
      if (input.type === "submit" || input.type === "button") {
        return;
      }
      if (input.disabled) {
        return;
      }

      const name = input.name;
      if (!name) {
        return;
      }

      if (input.type === "checkbox") {
        if (input.checked) {
          if (name.endsWith("[]")) {
            const key = name.slice(0, -2);
            if (!formData[key]) {
              formData[key] = [];
            }
            formData[key].push(input.value);
          } else {
            formData[name] = input.value;
          }
        }
      } else if (input.type === "radio") {
        if (input.checked) {
          formData[name] = input.value;
        }
      } else {
        if (name.endsWith("[]")) {
          const key = name.slice(0, -2);
          if (!formData[key]) {
            formData[key] = [];
          }
          if (input.value !== "") {
            formData[key].push(input.value);
          }
        } else {
          formData[name] = input.value;
        }
      }
    });

    const urlParams = new URLSearchParams(window.location.search);
    const id = urlParams.get("id");
    const view = urlParams.get("view") || urlParams.get("controller");
    if (id) {
      formData.category_id = parseInt(id, 10);
    }
    if (view) {
      formData.view = view;
    }

    return formData;
  }

  debouncedSend() {
    clearTimeout(this.debounceTimer);
    this.debounceTimer = setTimeout(() => this.sendAjax(), this.debounceDelay);
  }

  sendAjax() {
    const data = this.collectFormData();

    if (!data.category_id) {
      return;
    }

    const csrfToken = Joomla.getOptions("csrf.token", "");
    if (!csrfToken) {
      return;
    }

    if (this.currentRequest) {
      this.currentRequest.abort();
    }

    this.showLoading();

    const requestData = Object.assign({}, data);
    requestData[csrfToken] = 1;

    this.currentRequest = Joomla.request({
      url: "?option=com_ajax&module=ishop_filter&format=json",
      method: "POST",
      headers: {
        "Cache-Control": "no-cache",
        "Content-Type": "application/x-www-form-urlencoded",
      },
      data: new URLSearchParams(requestData).toString(),
      onSuccess: (response) => {
        try {
          const parsed =
            typeof response === "string" ? JSON.parse(response) : response;
          if (parsed.success && parsed.data) {
            this.updateUI(parsed.data);
          }
        } catch (e) {
          console.error("IshopFilter: Failed to parse response", e);
        }
      },
      onError: (xhr) => {
        console.error("IshopFilter: AJAX request failed", xhr);
      },
      onComplete: () => {
        this.currentRequest = null;
        this.hideLoading();
      },
    });
  }

  updateUI(data) {
    const productCount = data.productCount ?? 0;
    const countEl = this.form
      .closest(".offcanvas-body")
      ?.querySelector(".count-value");
    if (countEl) {
      countEl.textContent = productCount;
      countEl.dataset.count = productCount;
    }

    const available = data.availableOptions || {};

    this.updateCheckboxes(
      'input[name="manufacturers[]"]',
      available.manufacturers || [],
      (input) => input.value,
    );

    this.updateCheckboxes(
      'input[name="warehouses[]"]',
      available.warehouses || [],
      (input) => parseInt(input.value, 10),
    );

    if (available.isshop_fields) {
      this.updateFields(available.isshop_fields);
    }

    if (available.price_range) {
      this.updateRange("min_price", available.price_range.min);
      this.updateRange("max_price", available.price_range.max);
    }

    if (available.sizes) {
      if (available.sizes.width) {
        this.updateRange("min_width", available.sizes.width.min);
        this.updateRange("max_width", available.sizes.width.max);
      }
      if (available.sizes.height) {
        this.updateRange("min_height", available.sizes.height.min);
        this.updateRange("max_height", available.sizes.height.max);
      }
      if (available.sizes.depth) {
        this.updateRange("min_depth", available.sizes.depth.min);
        this.updateRange("max_depth", available.sizes.depth.max);
      }
      if (available.sizes.weight) {
        this.updateRange("min_weight", available.sizes.weight.min);
        this.updateRange("max_weight", available.sizes.weight.max);
      }
    }
  }

  updateCheckboxes(selector, availableIds, valueMapper) {
    const checkboxes = this.form.querySelectorAll(selector);
    checkboxes.forEach((input) => {
      const val = valueMapper(input);
      const isAvailable = availableIds.includes(val);
      input.disabled = !isAvailable;

      const label =
        input.closest("label") ||
        this.form.querySelector(`label[for="${input.id}"]`);
      if (label) {
        label.classList.toggle("disabled", !isAvailable);
      }

      const parentLi = input.closest("li");
      if (parentLi) {
        parentLi.classList.toggle("filter-option-disabled", !isAvailable);
      }
    });

    this.updateGroupVisibility(selector);
  }

  updateFields(fieldsData) {
    Object.keys(fieldsData).forEach((fieldId) => {
      const fieldInfo = fieldsData[fieldId];

      if (fieldInfo.type === "range") {
        const minInput = this.form.querySelector(
          `input[name="ishop_fields[${fieldId}][min]"]`,
        );
        const maxInput = this.form.querySelector(
          `input[name="ishop_fields[${fieldId}][max]"]`,
        );
        if (minInput) {
          minInput.min = fieldInfo.min;
          minInput.max = fieldInfo.max;
        }
        if (maxInput) {
          maxInput.min = fieldInfo.min;
          maxInput.max = fieldInfo.max;
        }
      } else if (fieldInfo.type === "list") {
        const availableValues = Object.keys(fieldInfo.values || {});
        const checkboxes = this.form.querySelectorAll(
          `input[name="ishop_fields[${fieldId}][]"]`,
        );
        checkboxes.forEach((input) => {
          const isAvailable = availableValues.includes(input.value);
          input.disabled = !isAvailable;
          const label =
            input.closest("label") ||
            this.form.querySelector(`label[for="${input.id}"]`);
          if (label) {
            label.classList.toggle("disabled", !isAvailable);
          }
          const parentLi = input.closest("li");
          if (parentLi) {
            parentLi.classList.toggle("filter-option-disabled", !isAvailable);
          }
        });
        this.updateGroupVisibility(`input[name="ishop_fields[${fieldId}][]"]`);
      }
    });
  }

  updateRange(name, value) {
    const input = this.form.querySelector(`input[name="${name}"]`);
    if (input) {
      if (name.startsWith("min_")) {
        input.min = value;
      } else {
        input.max = value;
      }
    }
  }

  updateGroupVisibility(selector) {
    const firstInput = this.form.querySelector(selector);
    if (!firstInput) {
      return;
    }
    const group = firstInput.closest("li");
    if (!group) {
      return;
    }
    const checkboxes = group.querySelectorAll(selector);
    const allDisabled = Array.from(checkboxes).every((cb) => cb.disabled);
    group.classList.toggle("filter-group-empty", allDisabled);
  }

  reset() {
    const inputs = this.form.querySelectorAll("input, select, textarea");
    inputs.forEach((input) => {
      if (input.type === "submit" || input.type === "button") {
        return;
      }
      if (input.type === "checkbox" || input.type === "radio") {
        input.checked = false;
      } else {
        input.value = "";
      }
      input.disabled = false;
    });

    const labels = this.form.querySelectorAll("label.disabled");
    labels.forEach((label) => label.classList.remove("disabled"));

    const disabledItems = this.form.querySelectorAll(
      ".filter-option-disabled, .filter-group-empty",
    );
    disabledItems.forEach((el) => {
      el.classList.remove("filter-option-disabled", "filter-group-empty");
    });

    clearTimeout(this.debounceTimer);
    if (this.currentRequest) {
      this.currentRequest.abort();
      this.currentRequest = null;
    }
    this.hideLoading();

    this.form.submit();
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
}

document.addEventListener("DOMContentLoaded", () => {
  const forms = document.querySelectorAll('form[name="ishop_filter"]');
  forms.forEach((form) => {
    const id = form.id;
    if (id) {
      window.iFilter = new IshopFilter(id);
    }
  });
});
