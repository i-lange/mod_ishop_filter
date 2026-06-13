import { afterEach, beforeEach, describe, expect, test, vi } from 'vitest'

const frontPath = '../../media/js/front.js'

// Импорт с query string сбрасывает ESM-кэш между сценариями.
async function importFront(label) {
  return import(`${frontPath}?case=${label}-${Date.now()}-${Math.random()}`)
}

// Создает минимальную DOM-разметку, совпадающую с hooks production layout.
function renderFilterForm(options = {}) {
  const {
    id = 'filter-a',
    productCount = 5,
    categoryId = 12,
    itemId = 34,
    includeExternal = true,
    append = false,
  } = options

  const html = `
    <section class="mod_ishop_filter">
      <div class="filter-count"><span class="count-value" data-count="${productCount}">${productCount}</span></div>
      <div class="filter-active-tags" data-filter-active-tags hidden></div>
      <form name="ishop_filter"
            id="${id}"
            action="https://example.test/base"
            data-category-id="${categoryId}"
            data-item-id="${itemId}"
            data-product-count="${productCount}"
            data-preview-url="/preview"
            data-reset-url="/reset"
            data-submit-template="Show %s"
            data-submit-unavailable-text="No products"
            data-filter-from-text="from"
            data-filter-to-text="to">
        <div class="filter-loading-overlay" style="display: none;"></div>
        <input type="hidden" name="manufacturers[]" value="0">
        <div class="form-check" id="brand-1-wrap">
          <label for="brand-1">Brand One</label>
          <input id="brand-1" type="checkbox" name="manufacturers[]" value="1" checked>
        </div>
        <div class="form-check" id="brand-2-wrap">
          <label for="brand-2">Brand Two</label>
          <input id="brand-2" type="checkbox" name="manufacturers[]" value="2">
        </div>
        <div class="form-check" id="warehouse-7-wrap">
          <label for="warehouse-7">Warehouse Seven</label>
          <input id="warehouse-7" type="checkbox" name="warehouses[]" value="7">
        </div>
        <div class="form-check" id="field-10-100-wrap">
          <label for="field-10-100">Red</label>
          <input id="field-10-100" type="checkbox" name="ishop_fields[10][]" value="100" checked>
        </div>
        <div class="form-check" id="field-10-101-wrap">
          <label for="field-10-101">Blue</label>
          <input id="field-10-101" type="checkbox" name="ishop_fields[10][]" value="101">
        </div>
        <span data-field-id="10" data-selected-count hidden></span>
        <label for="field-11">Boolean Field</label>
        <input id="field-11" type="checkbox" name="ishop_fields[11]" value="1">
        <span>Price:</span>
        <div class="range">
          <input class="range-min" type="number" name="min_price" value="10.6" data-filter-label="Price">
          <input class="range-max" type="number" name="max_price" value="99.2" data-filter-label="Price">
          <div class="range-slider"><div class="range-slider__line"></div><div class="range-slider__point range-slider__point--upper"></div><div class="range-slider__point range-slider__point--lower"></div></div>
        </div>
        <span>Field Range:</span>
        <div class="range">
          <input class="range-min" type="number" name="ishop_fields[12][min]" value="3.2" data-filter-label="Field Range">
          <input class="range-max" type="number" name="ishop_fields[12][max]" value="8.7" data-filter-label="Field Range">
          <div class="range-slider"><div class="range-slider__line"></div><div class="range-slider__point range-slider__point--upper"></div><div class="range-slider__point range-slider__point--lower"></div></div>
        </div>
        <input type="number" name="min_width" value="">
        <input type="text" name="ignored_text" value="">
        <input type="checkbox" name="disabled_value" value="1" disabled checked>
        <button type="button" name="button_value" value="bad">button</button>
      </form>
      ${includeExternal ? `
        <button type="submit" form="${id}" data-filter-submit aria-disabled="false">
          <span data-filter-submit-text></span>
        </button>
        <div data-filter-submit-hint hidden>hint</div>
        <button type="button" form="${id}" data-filter-reset>reset</button>
      ` : ''}
    </section>
  `

  if (append) {
    document.body.insertAdjacentHTML('beforeend', html)
  } else {
    document.body.innerHTML = html
  }
}

// Создает управляемый Joomla API double без настоящей сети.
function createJoomlaApi(requestHandler = () => {}, requestFactory = () => ({ abort: vi.fn() })) {
  return {
    getOptions: vi.fn((key, fallback) => {
      if (key === 'csrf.token') {
        return 'csrf.token.name'
      }

      if (key === 'system.paths') {
        return { root: '/root' }
      }

      return fallback
    }),
    request: vi.fn((options) => {
      requestHandler(options)

      return requestFactory(options)
    }),
  }
}

// Преобразует FormData в объект массивов для читаемых assertions.
function formDataEntries(formData) {
  const entries = {}

  formData.forEach((value, key) => {
    entries[key] = entries[key] || []
    entries[key].push(String(value))
  })

  return entries
}

afterEach(() => {
  vi.useRealTimers()
})

describe('front.js entrypoint', () => {
  beforeEach(() => {
    vi.restoreAllMocks()
    vi.unstubAllGlobals()
    vi.resetModules()
    document.body.innerHTML = ''
    delete window.iFilters
    delete window.IshopFilter
  })

  test('импортируется без сетевых вызовов и DOM-мутаций', async () => {
    document.body.innerHTML = '<main id="stable">Stable</main>'
    const before = document.body.innerHTML
    const joomlaApi = createJoomlaApi()
    const fetchSpy = vi.fn()
    const xhrSpy = vi.fn()

    vi.stubGlobal('Joomla', joomlaApi)
    vi.stubGlobal('fetch', fetchSpy)
    vi.stubGlobal('XMLHttpRequest', xhrSpy)

    const module = await importFront('smoke')

    expect(module.FRONT_ENTRY_MARKER).toBe('mod_ishop_filter.front')
    expect(module.IshopFilter).toBeTypeOf('function')
    expect(module.initIshopFilters).toBeTypeOf('function')
    expect(joomlaApi.request).not.toHaveBeenCalled()
    expect(fetchSpy).not.toHaveBeenCalled()
    expect(xhrSpy).not.toHaveBeenCalled()
    expect(document.body.innerHTML).toBe(before)
  })

  test('initIshopFilters не падает без формы и возвращает пустой список', async () => {
    const { initIshopFilters } = await importFront('absent')

    expect(initIshopFilters(document)).toEqual([])
  })

  test('инициализирует несколько форм и обновляет window.iFilters', async () => {
    renderFilterForm({ id: 'filter-a' })
    renderFilterForm({ id: 'filter-b', append: true })
    const { initIshopFilters } = await importFront('multi')

    const filters = initIshopFilters(document)

    expect(filters).toHaveLength(2)
    expect(filters.map((filter) => filter.formId)).toEqual(['filter-a', 'filter-b'])
    expect(window.iFilters).toBe(filters)
    expect(document.querySelector('[form="filter-a"] [data-filter-submit-text]').textContent).toBe('Show 5')
  })
})

describe('IshopFilter form behavior', () => {
  beforeEach(() => {
    vi.restoreAllMocks()
    vi.unstubAllGlobals()
    vi.resetModules()
    document.body.innerHTML = ''
    delete window.iFilters
    delete window.IshopFilterSlider
  })

  test('собирает payload с исходными именами полей и округлением чисел', async () => {
    renderFilterForm()
    const { IshopFilter } = await importFront('payload')
    const filter = new IshopFilter('filter-a')

    const entries = formDataEntries(filter.collectFormData())

    expect(entries['manufacturers[]']).toEqual(['0', '1'])
    expect(entries['warehouses[]']).toBeUndefined()
    expect(entries['ishop_fields[10][]']).toEqual(['100'])
    expect(entries['ishop_fields[12][min]']).toEqual(['3'])
    expect(entries['ishop_fields[12][max]']).toEqual(['9'])
    expect(entries.min_price).toEqual(['11'])
    expect(entries.max_price).toEqual(['99'])
    expect(entries.category_id).toEqual(['12'])
    expect(entries.Itemid).toEqual(['34'])
    expect(entries.disabled_value).toBeUndefined()
    expect(entries.button_value).toBeUndefined()
    expect(entries.ignored_text).toBeUndefined()
  })

  test('берет category_id и Itemid из query string, если data-атрибуты пустые', async () => {
    window.history.pushState({}, '', '/category?id=77&Itemid=88')
    renderFilterForm({ categoryId: 0, itemId: 0 })
    const { IshopFilter } = await importFront('query-context')
    const filter = new IshopFilter('filter-a')

    const entries = formDataEntries(filter.collectFormData())

    expect(entries.category_id).toEqual(['77'])
    expect(entries.Itemid).toEqual(['88'])
  })

  test('sendAjax отправляет Joomla.request и обновляет UI при success', async () => {
    renderFilterForm()
    let requestOptions
    const joomlaApi = createJoomlaApi((options) => {
      requestOptions = options
      options.onSuccess(JSON.stringify({
        success: true,
        data: {
          productCount: 2,
          sefUrl: 'https://example.test/filter-result',
          baseUrl: 'https://example.test/base',
          availableOptions: {
            manufacturers: [1],
            warehouses: [7],
            ishop_fields: {
              10: { type: 'list', values: { 100: true } },
              11: { type: 'boolean' },
            },
            price_range: { min: 5, max: 50 },
            sizes: { width: { min: 1, max: 9 } },
          },
        },
      }))
      options.onComplete()
    })
    vi.stubGlobal('Joomla', joomlaApi)
    window.IshopFilterSlider = { refresh: vi.fn() }
    const { IshopFilter } = await importFront('ajax-success')
    const filter = new IshopFilter('filter-a')

    const result = await filter.sendAjax()

    expect(result.productCount).toBe(2)
    expect(joomlaApi.request).toHaveBeenCalledTimes(1)
    expect(requestOptions).toMatchObject({
      url: '/preview',
      method: 'POST',
      headers: {
        'Cache-Control': 'no-cache',
        'Content-Type': 'application/x-www-form-urlencoded',
      },
    })
    expect(requestOptions.data).toContain('csrf.token.name=1')
    expect(requestOptions.data).toContain('category_id=12')
    expect(document.querySelector('.count-value').textContent).toBe('2')
    expect(document.querySelector('[data-filter-submit-text]').textContent).toBe('Show 2')
    expect(document.querySelector('[name="min_price"]').min).toBe('5')
    expect(document.querySelector('[name="max_price"]').max).toBe('50')
    expect(document.querySelector('[name="min_price"]').step).toBe('1')
    expect(document.querySelector('#brand-2').disabled).toBe(true)
    expect(document.querySelector('label[for="brand-2"]').classList.contains('disabled')).toBe(true)
    expect(document.querySelector('#brand-2-wrap').classList.contains('filter-option-disabled')).toBe(true)
    expect(document.querySelector('#brand-1').disabled).toBe(false)
    expect(document.querySelector('#field-10-101').disabled).toBe(true)
    expect(window.IshopFilterSlider.refresh).toHaveBeenCalled()
  })

  test('sendAjax отменяет предыдущий запрос, обрабатывает ошибки и malformed JSON безопасно', async () => {
    renderFilterForm()
    const joomlaApi = createJoomlaApi()
    vi.stubGlobal('Joomla', joomlaApi)
    const { IshopFilter } = await importFront('ajax-error')
    const filter = new IshopFilter('filter-a')
    const abort = vi.fn()

    filter.currentRequest = { abort }

    const firstPromise = filter.sendAjax()

    expect(abort).toHaveBeenCalledTimes(1)

    const firstOptions = joomlaApi.request.mock.calls[0][0]
    firstOptions.onSuccess('{broken json')
    firstOptions.onComplete()
    await firstPromise

    expect(document.querySelector('.count-value').textContent).toBe('5')

    const secondPromise = filter.sendAjax()
    const secondOptions = joomlaApi.request.mock.calls[1][0]
    secondOptions.onError({ status: 500 })
    secondOptions.onComplete()
    await secondPromise

    expect(document.querySelector('.count-value').textContent).toBe('5')
  })

  test('sendAjax игнорирует success и redirect от устаревшего запроса', async () => {
    renderFilterForm()
    const requests = []
    const aborts = []
    const joomlaApi = createJoomlaApi(
      (options) => {
        requests.push(options)
      },
      () => {
        const request = { abort: vi.fn() }
        aborts.push(request.abort)
        return request
      },
    )
    vi.stubGlobal('Joomla', joomlaApi)
    const { IshopFilter } = await importFront('ajax-stale-response')
    const filter = new IshopFilter('filter-a')
    filter.redirectTo = vi.fn()

    const firstPromise = filter.sendAjax({ redirectOnSuccess: true })
    const secondPromise = filter.sendAjax()

    await expect(firstPromise).resolves.toBeNull()
    expect(aborts[0]).toHaveBeenCalledTimes(1)

    requests[0].onSuccess({
      success: true,
      data: { productCount: 1, sefUrl: '/stale', availableOptions: {} },
    })
    requests[0].onComplete()

    expect(document.querySelector('.count-value').textContent).toBe('5')
    expect(filter.redirectTo).not.toHaveBeenCalled()

    requests[1].onSuccess({
      success: true,
      data: { productCount: 9, sefUrl: '/fresh', availableOptions: {} },
    })
    requests[1].onComplete()

    await expect(secondPromise).resolves.toMatchObject({ productCount: 9 })
    expect(document.querySelector('.count-value').textContent).toBe('9')
    expect(filter.redirectTo).not.toHaveBeenCalled()
  })

  test('abortCurrentRequest инвалидирует запрос до вызова abort callback', async () => {
    renderFilterForm()
    const requests = []
    const joomlaApi = createJoomlaApi(
      (options) => {
        requests.push(options)
      },
      () => ({
        abort: vi.fn(() => {
          requests[0].onSuccess({
            success: true,
            data: { productCount: 1, sefUrl: '/aborted', availableOptions: {} },
          })
          requests[0].onComplete()
        }),
      }),
    )
    vi.stubGlobal('Joomla', joomlaApi)
    const { IshopFilter } = await importFront('ajax-abort-callback')
    const filter = new IshopFilter('filter-a')
    filter.redirectTo = vi.fn()

    const firstPromise = filter.sendAjax({ redirectOnSuccess: true })
    const secondPromise = filter.sendAjax()

    await expect(firstPromise).resolves.toBeNull()
    expect(document.querySelector('.count-value').textContent).toBe('5')
    expect(filter.redirectTo).not.toHaveBeenCalled()

    requests[1].onSuccess({
      success: true,
      data: { productCount: 8, sefUrl: '/fresh', availableOptions: {} },
    })
    requests[1].onComplete()

    await expect(secondPromise).resolves.toMatchObject({ productCount: 8 })
    expect(document.querySelector('.count-value').textContent).toBe('8')
  })

  test('sendAjax не показывает loading overlay, если быстрый ответ пришел до задержки', async () => {
    vi.useFakeTimers()
    renderFilterForm()
    let requestOptions
    const joomlaApi = createJoomlaApi((options) => {
      requestOptions = options
    })
    vi.stubGlobal('Joomla', joomlaApi)
    const { IshopFilter } = await importFront('ajax-loading')
    const filter = new IshopFilter('filter-a')
    const promise = filter.sendAjax()

    expect(document.querySelector('.filter-loading-overlay').style.display).toBe('none')

    requestOptions.onSuccess({ success: true, data: { productCount: 4, availableOptions: {} } })
    requestOptions.onComplete()
    await promise
    await vi.advanceTimersByTimeAsync(filter.loadingDelay)

    expect(document.querySelector('.filter-loading-overlay').style.display).toBe('none')
    expect(document.querySelector('.count-value').textContent).toBe('4')
    vi.useRealTimers()
  })

  test('sendAjax показывает loading overlay после задержки и держит минимальное время', async () => {
    vi.useFakeTimers()
    renderFilterForm()
    let requestOptions
    const joomlaApi = createJoomlaApi((options) => {
      requestOptions = options
    })
    vi.stubGlobal('Joomla', joomlaApi)
    const { IshopFilter } = await importFront('ajax-loading-min-visible')
    const filter = new IshopFilter('filter-a')
    const promise = filter.sendAjax()

    expect(document.querySelector('.filter-loading-overlay').style.display).toBe('none')

    await vi.advanceTimersByTimeAsync(filter.loadingDelay - 1)
    expect(document.querySelector('.filter-loading-overlay').style.display).toBe('none')

    await vi.advanceTimersByTimeAsync(1)
    expect(document.querySelector('.filter-loading-overlay').style.display).toBe('flex')

    requestOptions.onSuccess({ success: true, data: { productCount: 4, availableOptions: {} } })
    requestOptions.onComplete()
    await promise

    await vi.advanceTimersByTimeAsync(filter.loadingMinVisible - 1)
    expect(document.querySelector('.filter-loading-overlay').style.display).toBe('flex')

    await vi.advanceTimersByTimeAsync(1)
    expect(document.querySelector('.filter-loading-overlay').style.display).toBe('none')
    vi.useRealTimers()
  })

  test('sendAjax скрывает loading overlay сразу, если минимум видимости уже прошел', async () => {
    vi.useFakeTimers()
    renderFilterForm()
    let requestOptions
    const joomlaApi = createJoomlaApi((options) => {
      requestOptions = options
    })
    vi.stubGlobal('Joomla', joomlaApi)
    const { IshopFilter } = await importFront('ajax-loading-after-min-visible')
    const filter = new IshopFilter('filter-a')
    const promise = filter.sendAjax()

    await vi.advanceTimersByTimeAsync(filter.loadingDelay + filter.loadingMinVisible)
    expect(document.querySelector('.filter-loading-overlay').style.display).toBe('flex')

    requestOptions.onSuccess({ success: true, data: { productCount: 4, availableOptions: {} } })
    requestOptions.onComplete()
    await promise

    expect(document.querySelector('.filter-loading-overlay').style.display).toBe('none')
    vi.useRealTimers()
  })

  test('sendAjax не отправляет запрос без category_id или CSRF token', async () => {
    renderFilterForm({ categoryId: 0 })
    window.history.pushState({}, '', '/category')
    const joomlaApi = createJoomlaApi()
    vi.stubGlobal('Joomla', joomlaApi)
    const { IshopFilter } = await importFront('ajax-guards')
    const withoutCategory = new IshopFilter('filter-a')

    await withoutCategory.sendAjax()

    expect(joomlaApi.request).not.toHaveBeenCalled()

    renderFilterForm()
    joomlaApi.getOptions.mockImplementation(() => '')
    const withoutCsrf = new IshopFilter('filter-a')

    await withoutCsrf.sendAjax()

    expect(joomlaApi.request).not.toHaveBeenCalled()
  })

  test('submit делает preview и redirect на sefUrl', async () => {
    renderFilterForm()
    const joomlaApi = createJoomlaApi((options) => {
      options.onSuccess({ success: true, data: { productCount: 3, sefUrl: '/sef', availableOptions: {} } })
      options.onComplete()
    })
    vi.stubGlobal('Joomla', joomlaApi)
    const { IshopFilter } = await importFront('submit')
    const filter = new IshopFilter('filter-a')
    filter.redirectTo = vi.fn()

    await filter.submit()

    expect(filter.redirectTo).toHaveBeenCalledWith('/sef')
  })

  test('submit не выполняется при disabled button и fallback redirect работает при bad response', async () => {
    renderFilterForm({ productCount: 0 })
    const joomlaApi = createJoomlaApi()
    vi.stubGlobal('Joomla', joomlaApi)
    const { IshopFilter } = await importFront('submit-disabled')
    const disabledFilter = new IshopFilter('filter-a')

    await disabledFilter.submit()

    expect(joomlaApi.request).not.toHaveBeenCalled()

    renderFilterForm({ productCount: 5 })
    let requestOptions
    const failingJoomlaApi = createJoomlaApi((options) => {
      requestOptions = options
      options.onSuccess({ success: false })
    })
    vi.stubGlobal('Joomla', failingJoomlaApi)
    const fallbackFilter = new IshopFilter('filter-a')
    fallbackFilter.redirectTo = vi.fn()

    fallbackFilter.submit()
    requestOptions.onComplete()

    expect(fallbackFilter.redirectTo).toHaveBeenCalledWith('https://example.test/base')
  })

  test('openFilterResult вызывает native submit для raw filter query', async () => {
    renderFilterForm()
    const { IshopFilter } = await importFront('native-submit')
    const filter = new IshopFilter('filter-a')
    filter.submitNative = vi.fn()
    filter.redirectTo = vi.fn()

    filter.openFilterResult('/category?min_price=10')

    expect(filter.submitNative).toHaveBeenCalledTimes(1)
    expect(filter.redirectTo).not.toHaveBeenCalled()
  })

  test('reset отправляет reset endpoint и redirect на baseUrl', async () => {
    renderFilterForm()
    let requestOptions
    const joomlaApi = createJoomlaApi((options) => {
      requestOptions = options
      options.onSuccess({ success: true, data: { baseUrl: '/category-base' } })
    })
    vi.stubGlobal('Joomla', joomlaApi)
    const { IshopFilter } = await importFront('reset')
    const filter = new IshopFilter('filter-a')
    filter.redirectTo = vi.fn()

    filter.reset()

    expect(requestOptions.url).toBe('/reset')
    expect(requestOptions.method).toBe('POST')
    expect(requestOptions.data).toContain('category_id=12')
    expect(requestOptions.data).toContain('Itemid=34')
    expect(requestOptions.data).toContain('csrf.token.name=1')
    expect(filter.redirectTo).toHaveBeenCalledWith('/category-base')
  })

  test('reset отменяет pending preview и делает fallback redirect при bad response', async () => {
    renderFilterForm()
    let requestOptions
    const joomlaApi = createJoomlaApi((options) => {
      requestOptions = options
      options.onSuccess({ success: false })
    })
    vi.stubGlobal('Joomla', joomlaApi)
    const { IshopFilter } = await importFront('reset-fallback')
    const filter = new IshopFilter('filter-a')
    const abort = vi.fn()
    filter.currentRequest = { abort }
    filter.baseUrl = '/base-from-preview'
    filter.redirectTo = vi.fn()

    filter.reset()

    expect(abort).toHaveBeenCalledTimes(1)
    requestOptions.onComplete()
    expect(filter.redirectTo).toHaveBeenCalledWith('/base-from-preview')
  })

  test('reset делает fallback redirect при onError response', async () => {
    renderFilterForm()
    let requestOptions
    const joomlaApi = createJoomlaApi((options) => {
      requestOptions = options
    })
    vi.stubGlobal('Joomla', joomlaApi)
    const { IshopFilter } = await importFront('reset-error')
    const filter = new IshopFilter('filter-a')
    filter.redirectTo = vi.fn()

    filter.reset()
    requestOptions.onError({ status: 500 })
    requestOptions.onComplete()

    expect(filter.redirectTo).toHaveBeenCalledWith('https://example.test/base')
  })

  test('availability сохраняет выбранные недоступные значения enabled и помечает пустую группу', async () => {
    renderFilterForm()
    const { IshopFilter } = await importFront('availability')
    const filter = new IshopFilter('filter-a')

    document.querySelector('form').setAttribute('data-panel', '')
    document.querySelector('#brand-2').checked = true
    filter.updateCheckboxes('input[name="manufacturers[]"]', [])

    expect(document.querySelector('#brand-1').disabled).toBe(false)
    expect(document.querySelector('#brand-2').disabled).toBe(false)

    document.querySelector('#brand-1').checked = false
    document.querySelector('#brand-2').checked = false
    filter.updateCheckboxes('input[name="manufacturers[]"]', [])

    expect(document.querySelector('#brand-1').disabled).toBe(true)
    expect(document.querySelector('#brand-2').disabled).toBe(true)
    expect(document.querySelector('form').classList.contains('filter-group-empty')).toBe(true)
  })

  test('range UI обновляет field range и вызывает slider refresh для scope', async () => {
    renderFilterForm()
    window.IshopFilterSlider = { refresh: vi.fn() }
    const { IshopFilter } = await importFront('field-range')
    const filter = new IshopFilter('filter-a')

    filter.updateFields({
      12: { type: 'range', min: 2, max: 12 },
    })

    expect(document.querySelector('[name="ishop_fields[12][min]"]').min).toBe('2')
    expect(document.querySelector('[name="ishop_fields[12][max]"]').max).toBe('12')
    expect(document.querySelector('[name="ishop_fields[12][min]"]').step).toBe('1')
    expect(window.IshopFilterSlider.refresh).toHaveBeenCalled()
  })

  test('disableMissingFieldOptions отключает отсутствующие list и boolean поля', async () => {
    renderFilterForm()
    const { IshopFilter } = await importFront('missing-fields')
    const filter = new IshopFilter('filter-a')

    filter.updateFields({})

    expect(document.querySelector('#field-10-101').disabled).toBe(true)
    expect(document.querySelector('#field-10-100').disabled).toBe(false)
    expect(document.querySelector('#field-11').disabled).toBe(true)
    expect(document.querySelector('label[for="field-11"]').classList.contains('disabled')).toBe(true)
  })

  test('active tags создаются и удаление tag очищает связанные controls', async () => {
    vi.useFakeTimers()
    renderFilterForm()
    const { IshopFilter } = await importFront('tags')
    const filter = new IshopFilter('filter-a')
    filter.sendAjax = vi.fn(() => Promise.resolve(null))

    filter.updateActiveTags()

    expect(document.querySelector('[data-filter-active-tags]').hidden).toBe(false)
    expect([...document.querySelectorAll('.filter-active-tag__label')].map((node) => node.textContent)).toContain('Brand One')
    expect([...document.querySelectorAll('.filter-active-tag__label')].map((node) => node.textContent)).toContain('Price from 11 to 99')

    document.querySelector('[data-filter-tag-index="0"]').click()
    vi.advanceTimersByTime(300)

    expect(document.querySelector('#brand-1').checked).toBe(false)
    expect(filter.sendAjax).toHaveBeenCalled()
    vi.useRealTimers()
  })

  test('удаление range tag очищает оба input и disabled checked control не попадает в tags', async () => {
    vi.useFakeTimers()
    renderFilterForm()
    const { IshopFilter } = await importFront('range-tag')
    const filter = new IshopFilter('filter-a')
    filter.sendAjax = vi.fn(() => Promise.resolve(null))
    document.querySelector('#field-10-100').disabled = true

    filter.updateActiveTags()

    expect([...document.querySelectorAll('.filter-active-tag__label')].map((node) => node.textContent)).not.toContain('Red')

    const rangeButton = [...document.querySelectorAll('[data-filter-tag-index]')]
      .find((button) => button.textContent.includes('Price'))
    rangeButton.click()
    vi.advanceTimersByTime(300)

    expect(document.querySelector('[name="min_price"]').value).toBe('')
    expect(document.querySelector('[name="max_price"]').value).toBe('')
    expect(filter.sendAjax).toHaveBeenCalled()
    vi.useRealTimers()
  })

  test('selected counts считают только ishop_fields list checkboxes', async () => {
    renderFilterForm()
    const { IshopFilter } = await importFront('counts')
    const filter = new IshopFilter('filter-a')
    const counter = document.querySelector('[data-selected-count]')

    document.querySelector('#brand-2').checked = true
    document.querySelector('#warehouse-7').checked = true
    filter.updateSelectedCounts()

    expect(counter.textContent).toBe('1')
    expect(counter.dataset.count).toBe('1')
    expect(counter.hidden).toBe(false)

    document.querySelector('#field-10-100').checked = false
    filter.updateSelectedCounts()

    expect(counter.textContent).toBe('0')
    expect(counter.hidden).toBe(true)
  })

  test('hasRawFilterQuery распознает raw filter параметры', async () => {
    renderFilterForm()
    const { IshopFilter } = await importFront('raw-query')
    const filter = new IshopFilter('filter-a')

    expect(filter.hasRawFilterQuery('/category?manufacturers[]=1')).toBe(true)
    expect(filter.hasRawFilterQuery('/category?ishop_fields[10][]=100')).toBe(true)
    expect(filter.hasRawFilterQuery('/category?limitstart=20')).toBe(false)
  })
})
