import fs from 'node:fs'

const reportPath = 'build/coverage/php-clover.xml'
const minimumStatementPercent = 70
const minimumMethodPercent = 70

// Проверка читает агрегированные Clover metrics и не зависит от HTML-отчета PHPUnit.
if (!fs.existsSync(reportPath)) {
  console.error(`Не найден PHP coverage отчет: ${reportPath}`)
  process.exit(1)
}

const xml = fs.readFileSync(reportPath, 'utf8')
const metricsMatches = [...xml.matchAll(/<metrics\b([^>]*)>/g)]
const projectMetrics = metricsMatches.at(-1)?.[1] ?? ''

// Атрибуты Clover достаются регулярным выражением, потому что здесь нужен только мягкий gate.
function readMetric(name) {
  const match = projectMetrics.match(new RegExp(`${name}="(\\d+)"`))

  return match ? Number(match[1]) : 0
}

function percent(covered, total) {
  return total === 0 ? 100 : (covered / total) * 100
}

const statements = readMetric('statements')
const coveredStatements = readMetric('coveredstatements')
const methods = readMetric('methods')
const coveredMethods = readMetric('coveredmethods')
const statementPercent = percent(coveredStatements, statements)
const methodPercent = percent(coveredMethods, methods)

console.log(`PHP coverage statements: ${statementPercent.toFixed(2)}%`)
console.log(`PHP coverage methods: ${methodPercent.toFixed(2)}%`)

if (statementPercent < minimumStatementPercent || methodPercent < minimumMethodPercent) {
  console.error(`PHP coverage gate не пройден: нужно ${minimumStatementPercent}% statements и ${minimumMethodPercent}% methods.`)
  process.exit(1)
}
