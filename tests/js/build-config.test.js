import fs from 'node:fs'
import zlib from 'node:zlib'
import { describe, expect, test } from 'vitest'

// Эти проверки запускаются после pnpm build и фиксируют ожидаемые build artifacts.
describe('build artifacts', () => {
  test('созданы CSS artifacts для front и slider', () => {
    for (const file of [
      'media/css/front.css',
      'media/css/front.min.css',
      'media/css/front.min.css.gz',
      'media/css/slider.css',
      'media/css/slider.min.css',
      'media/css/slider.min.css.gz',
    ]) {
      expect(fs.existsSync(file), `${file} должен существовать после сборки`).toBe(true)
    }
  })

  test('созданы JS artifacts для front и slider', () => {
    for (const file of [
      'media/js/front.min.js',
      'media/js/front.min.js.gz',
      'media/js/slider.min.js',
      'media/js/slider.min.js.gz',
    ]) {
      expect(fs.existsSync(file), `${file} должен существовать после сборки`).toBe(true)
    }
  })

  test('gzip artifacts распаковываются и содержат ожидаемый код', () => {
    const frontCss = zlib.gunzipSync(fs.readFileSync('media/css/front.min.css.gz')).toString('utf8')
    const frontJs = zlib.gunzipSync(fs.readFileSync('media/js/front.min.js.gz')).toString('utf8')
    const sliderJs = zlib.gunzipSync(fs.readFileSync('media/js/slider.min.js.gz')).toString('utf8')

    expect(frontCss).toContain('.mod_ishop_filter')
    expect(frontJs).toContain('option=com_ishop')
    expect(frontJs).toContain('iFilters')
    expect(sliderJs).toContain('IshopFilterSlider')
  })

  test('Vite entry configs содержат front и slider entrypoints', () => {
    const jsConfig = fs.readFileSync('vite.config.js.mts', 'utf8')
    const cssConfig = fs.readFileSync('vite.config.css.mts', 'utf8')

    expect(jsConfig).toContain("'front.js'")
    expect(jsConfig).toContain("'slider.js'")
    expect(cssConfig).toContain("'front.scss'")
    expect(cssConfig).toContain("'slider.scss'")
  })
})
