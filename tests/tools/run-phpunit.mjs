import fs from 'node:fs'
import path from 'node:path'
import { spawnSync } from 'node:child_process'

// Runner выбирает PHP с расширениями, нужными PHPUnit, не меняя глобальный PATH.
const phpCandidates = [
  process.env.PHP_BIN,
  'C:\\OSPanel\\modules\\PHP-8.3\\PHP\\php.exe',
  'php',
].filter(Boolean)

// Первый найденный кандидат используется для запуска Composer proxy PHPUnit.
const phpBin = phpCandidates.find((candidate) => {
  if (candidate === 'php') {
    return true
  }

  return fs.existsSync(candidate)
})

if (!phpBin) {
  console.error('Не найден PHP для запуска PHPUnit. Укажите путь через PHP_BIN.')
  process.exit(1)
}

const phpunitBin = path.resolve('vendor/bin/phpunit')
const args = [phpunitBin, ...process.argv.slice(2)]
const needsCoverage = args.some((arg) => arg.startsWith('--coverage'))

if (needsCoverage) {
  // Каталог coverage создается заранее, чтобы PHPUnit мог записать отчет.
  fs.mkdirSync(path.resolve('build/coverage'), { recursive: true })
}

const result = spawnSync(phpBin, args, {
  stdio: 'inherit',
  env: {
    ...process.env,
    XDEBUG_MODE: needsCoverage ? 'coverage' : (process.env.XDEBUG_MODE ?? 'off'),
  },
})

process.exit(result.status ?? 1)
