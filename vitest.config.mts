import { defineConfig } from 'vitest/config'

// Конфигурация запускает JS-тесты без браузера и без Joomla runtime.
export default defineConfig({
  test: {
    environment: 'happy-dom',
    include: ['tests/js/**/*.test.js'],
    restoreMocks: true,
    coverage: {
      provider: 'v8',
      reporter: ['text', 'html', 'lcov'],
      include: ['media/js/front.js', 'media/js/slider.js'],
      exclude: [
        'media/js/*.min.js',
        'media/js/**/*.gz',
        'node_modules/**',
        'tests/**',
      ],
      thresholds: {
        lines: 80,
        functions: 80,
        statements: 80,
        branches: 70,
      },
    },
  },
})
