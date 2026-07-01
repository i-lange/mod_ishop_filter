import fs from 'node:fs'
import path from 'node:path'
import yauzl from 'yauzl'
import pkg from '../../package.json' with { type: 'json' }
import { describe, expect, test } from 'vitest'

// Читает центральный каталог zip без распаковки архива в рабочую папку.
function readZipEntries(zipPath) {
  return new Promise((resolve, reject) => {
    const entries = []

    yauzl.open(zipPath, { lazyEntries: true }, (openError, zipFile) => {
      if (openError) {
        reject(openError)
        return
      }

      zipFile.readEntry()
      zipFile.on('entry', (entry) => {
        entries.push(entry.fileName)
        zipFile.readEntry()
      })
      zipFile.on('end', () => resolve(entries))
      zipFile.on('error', reject)
    })
  })
}

// Эти проверки запускаются после pnpm zip и валидируют installable archive.
describe('packaging archive', () => {
  const zipPath = path.join('build', `mod_ishop_filter-${pkg.version}.zip`)

  test('zip существует и содержит обязательные файлы расширения', async () => {
    expect(fs.existsSync(zipPath)).toBe(true)

    const entries = await readZipEntries(zipPath)

    for (const requiredFile of [
      'mod_ishop_filter.xml',
      'README.md',
      'script.php',
      'media/joomla.asset.json',
    ]) {
      expect(entries).toContain(requiredFile)
    }

    for (const requiredDirectory of ['language/', 'media/', 'services/', 'src/', 'tmpl/']) {
      expect(entries.some((entry) => entry.startsWith(requiredDirectory))).toBe(true)
    }
  })

  test('zip содержит собранные min/gzip assets и исходные SCSS', async () => {
    const entries = await readZipEntries(zipPath)

    for (const requiredAsset of [
      'media/js/front.min.js',
      'media/js/front.min.js.gz',
      'media/js/slider.min.js',
      'media/js/slider.min.js.gz',
      'media/css/front.min.css',
      'media/css/front.min.css.gz',
      'media/css/slider.min.css',
      'media/css/slider.min.css.gz',
      'media/scss/front.scss',
      'media/scss/slider.scss',
    ]) {
      expect(entries).toContain(requiredAsset)
    }
  })

  test('zip исключает development и test artifacts', async () => {
    const entries = await readZipEntries(zipPath)
    const forbiddenPrefixes = ['.git/', '.idea/', 'node_modules/', 'vendor/', 'tests/', 'coverage/', 'build/']

    for (const entry of entries) {
      expect(forbiddenPrefixes.some((prefix) => entry.startsWith(prefix)), `${entry} не должен быть в архиве`).toBe(false)
      expect(entry.endsWith('.zip'), `${entry} не должен быть вложенным zip`).toBe(false)
    }
  })
})
