import { beforeEach, describe, expect, test, vi } from 'vitest'

const sliderPath = '../../media/js/slider.js'

// Импорт с query string сбрасывает ESM-кэш между сценариями.
async function importSlider(label) {
  return import(`${sliderPath}?case=${label}-${Date.now()}-${Math.random()}`)
}

// Создает полную range-разметку, которую ожидает production slider.
function renderRange(options = {}) {
  const {
    min = '0',
    max = '100',
    minValue = '20',
    maxValue = '80',
  } = options

  document.body.innerHTML = `
    <section id="scope">
      <div class="range" id="range-a">
        <input class="range-min" min="${min}" value="${minValue}">
        <input class="range-max" max="${max}" value="${maxValue}">
        <div class="range-slider">
          <div class="range-slider__line"></div>
          <button class="range-slider__point range-slider__point--upper" type="button"></button>
          <button class="range-slider__point range-slider__point--lower" type="button"></button>
        </div>
      </div>
    </section>
  `

  const track = document.querySelector('.range-slider')
  const minPoint = document.querySelector('.range-slider__point--upper')
  const maxPoint = document.querySelector('.range-slider__point--lower')

  // happy-dom не вычисляет реальные размеры, поэтому геометрию задаем явно.
  track.getBoundingClientRect = () => ({
    left: 0,
    width: 100,
    right: 100,
    top: 0,
    bottom: 10,
    height: 10,
    x: 0,
    y: 0,
    toJSON: () => ({}),
  })
  Object.defineProperty(minPoint, 'offsetWidth', { configurable: true, value: 10 })
  Object.defineProperty(maxPoint, 'offsetWidth', { configurable: true, value: 10 })

  return {
    range: document.querySelector('.range'),
    minInput: document.querySelector('.range-min'),
    maxInput: document.querySelector('.range-max'),
    track,
    line: document.querySelector('.range-slider__line'),
    minPoint,
    maxPoint,
  }
}

describe('slider.js entrypoint', () => {
  beforeEach(() => {
    vi.restoreAllMocks()
    vi.resetModules()
    document.body.innerHTML = ''
    delete window.IshopFilterSlider
  })

  test('импортируется без Joomla и экспортирует публичный API', async () => {
    document.body.innerHTML = '<main id="stable">Stable</main>'
    const before = document.body.innerHTML
    const module = await importSlider('smoke')

    expect(module.SLIDER_ENTRY_MARKER).toBe('mod_ishop_filter.slider')
    expect(module.IshopFilterSliderManager).toBeTypeOf('function')
    expect(module.initIshopFilterSliders).toBeTypeOf('function')
    expect(window.IshopFilterSlider).toBeInstanceOf(module.IshopFilterSliderManager)
    expect(document.body.innerHTML).toBe(before)
  })

  test('initIshopFilterSliders не падает без range-разметки', async () => {
    const { initIshopFilterSliders } = await importSlider('empty')

    expect(initIshopFilterSliders(document)).toBe(window.IshopFilterSlider)
  })
})

describe('IshopFilterSliderManager behavior', () => {
  beforeEach(() => {
    vi.restoreAllMocks()
    vi.resetModules()
    document.body.innerHTML = ''
    delete window.IshopFilterSlider
  })

  test('игнорирует неполную range-разметку', async () => {
    document.body.innerHTML = '<div class="range"><input class="range-min"></div>'
    const { IshopFilterSliderManager } = await importSlider('incomplete')
    const manager = new IshopFilterSliderManager()

    expect(() => manager.refresh(document)).not.toThrow()
    expect(manager.sliders.size).toBe(0)
  })

  test('инициализирует range и повторный refresh не дублирует state', async () => {
    const nodes = renderRange()
    const { IshopFilterSliderManager } = await importSlider('init')
    const manager = new IshopFilterSliderManager()

    manager.refresh(document.querySelector('#scope'))
    manager.refresh(nodes.range)

    expect(manager.sliders.size).toBe(1)
    expect(nodes.line.style.left).toBe('20%')
    expect(nodes.line.style.right).toBe('20%')
    expect(nodes.minPoint.style.left).toBe('20%')
    expect(nodes.maxPoint.style.left).toBe('80%')
  })

  test('расчетные методы приводят границы и значения', async () => {
    const { IshopFilterSliderManager } = await importSlider('math')
    const manager = new IshopFilterSliderManager()

    expect(manager.parseBoundary('bad', 7)).toBe(7)
    expect(manager.parseBoundary('-5', 7)).toBe(0)
    expect(manager.parseOptionalValue('')).toBeNull()
    expect(manager.parseOptionalValue('10,6')).toBe(11)
    expect(manager.parseOptionalValue('-2')).toBe(0)
    expect(manager.coerceValue('', 12)).toBe(12)
    expect(manager.toPercent(25, 0, 100)).toBe(25)
  })

  test('DOM update корректирует равные значения и max <= min', async () => {
    const nodes = renderRange({ min: '10', max: '10', minValue: '10', maxValue: '10' })
    const { IshopFilterSliderManager } = await importSlider('equal')
    const manager = new IshopFilterSliderManager()

    manager.refresh(document)

    expect(nodes.line.style.left).toBe('0%')
    expect(nodes.line.style.right).toBe('100%')
    expect(nodes.minPoint.style.zIndex).toBe('1')
    expect(nodes.maxPoint.style.zIndex).toBe('2')
  })

  test('input normalization очищает значения вне bounds и не дает min быть больше max', async () => {
    vi.useFakeTimers()
    const nodes = renderRange({ min: '10', max: '50', minValue: '70', maxValue: '20' })
    const { IshopFilterSliderManager } = await importSlider('normalize')
    const manager = new IshopFilterSliderManager()

    manager.refresh(document)
    nodes.minInput.dispatchEvent(new Event('input', { bubbles: true }))
    vi.advanceTimersByTime(250)

    expect(nodes.minInput.value).toBe('20')
    expect(nodes.maxInput.value).toBe('20')
    vi.useRealTimers()
  })

  test('input normalization очищает max выше верхней границы', async () => {
    vi.useFakeTimers()
    const nodes = renderRange({ min: '10', max: '50', minValue: '20', maxValue: '70' })
    const { IshopFilterSliderManager } = await importSlider('normalize-max')
    const manager = new IshopFilterSliderManager()

    manager.refresh(document)
    nodes.maxInput.dispatchEvent(new Event('input', { bubbles: true }))
    vi.advanceTimersByTime(250)

    expect(nodes.maxInput.value).toBe('')
    vi.useRealTimers()
  })

  test('drag min point меняет input и dispatches bubbling input event', async () => {
    const nodes = renderRange({ min: '0', max: '100', minValue: '', maxValue: '80' })
    const { IshopFilterSliderManager } = await importSlider('drag-min')
    const manager = new IshopFilterSliderManager()
    const inputListener = vi.fn()

    nodes.minInput.addEventListener('input', inputListener)
    manager.refresh(document)
    nodes.minPoint.dispatchEvent(new MouseEvent('mousedown', { bubbles: true }))
    document.dispatchEvent(new MouseEvent('mousemove', { clientX: 30, bubbles: true }))

    expect(nodes.minInput.value).toBe('30')
    expect(inputListener).toHaveBeenCalledTimes(1)
  })

  test('drag min point очищает min на нижней границе', async () => {
    const nodes = renderRange({ min: '0', max: '100', minValue: '30', maxValue: '80' })
    const { IshopFilterSliderManager } = await importSlider('drag-min-empty')
    const manager = new IshopFilterSliderManager()

    manager.refresh(document)
    nodes.minPoint.dispatchEvent(new MouseEvent('mousedown', { bubbles: true }))
    document.dispatchEvent(new MouseEvent('mousemove', { clientX: 0, bubbles: true }))

    expect(nodes.minInput.value).toBe('')
  })

  test('drag max point не пересекает min и очищает max на верхней границе', async () => {
    const nodes = renderRange({ min: '0', max: '100', minValue: '70', maxValue: '' })
    const { IshopFilterSliderManager } = await importSlider('drag-max')
    const manager = new IshopFilterSliderManager()

    manager.refresh(document)
    nodes.maxPoint.dispatchEvent(new MouseEvent('mousedown', { bubbles: true }))
    document.dispatchEvent(new MouseEvent('mousemove', { clientX: 50, bubbles: true }))

    expect(Number(nodes.maxInput.value)).toBeGreaterThanOrEqual(80)

    document.dispatchEvent(new MouseEvent('mousemove', { clientX: 100, bubbles: true }))

    expect(nodes.maxInput.value).toBe('')
  })

  test('touch drag вызывает preventDefault и обновляет min', async () => {
    const nodes = renderRange({ min: '0', max: '100', minValue: '', maxValue: '90' })
    const { IshopFilterSliderManager } = await importSlider('touch')
    const manager = new IshopFilterSliderManager()

    manager.refresh(document)
    nodes.minPoint.dispatchEvent(new Event('touchstart', { bubbles: true }))

    const touchMove = new Event('touchmove', { bubbles: true, cancelable: true })
    Object.defineProperty(touchMove, 'touches', { value: [{ clientX: 40 }] })
    document.dispatchEvent(touchMove)

    expect(touchMove.defaultPrevented).toBe(true)
    expect(nodes.minInput.value).toBe('40')
  })
})
