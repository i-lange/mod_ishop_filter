<?php

declare(strict_types=1);

namespace Tests\Php\Contract;

use PHPUnit\Framework\TestCase;
use Tests\Php\Support\IniFileReader;

/**
 * Проверяет синхронность языковых файлов модуля.
 */
final class LanguageFilesTest extends TestCase
{
    /**
     * Основные .ini файлы обеих локалей должны иметь одинаковые ключи.
     */
    public function testMainLanguageFilesHaveSameKeys(): void
    {
        $en = IniFileReader::read(dirname(__DIR__, 3) . '/language/en-GB/mod_ishop_filter.ini');
        $ru = IniFileReader::read(dirname(__DIR__, 3) . '/language/ru-RU/mod_ishop_filter.ini');

        sort($en['keys']);
        sort($ru['keys']);

        self::assertSame($en['keys'], $ru['keys']);
        self::assertSame([], $en['duplicates']);
        self::assertSame([], $ru['duplicates']);
    }

    /**
     * System .sys.ini файлы обеих локалей должны иметь одинаковые ключи.
     */
    public function testSystemLanguageFilesHaveSameKeys(): void
    {
        $en = IniFileReader::read(dirname(__DIR__, 3) . '/language/en-GB/mod_ishop_filter.sys.ini');
        $ru = IniFileReader::read(dirname(__DIR__, 3) . '/language/ru-RU/mod_ishop_filter.sys.ini');

        sort($en['keys']);
        sort($ru['keys']);

        self::assertSame($en['keys'], $ru['keys']);
        self::assertSame([], $en['duplicates']);
        self::assertSame([], $ru['duplicates']);
    }

    /**
     * Все MOD_ISHOP_FILTER ключи из PHP/layout/XML должны быть в обеих локалях.
     */
    public function testUsedModuleLanguageKeysExistInBothLocales(): void
    {
        $usedKeys = $this->collectUsedModuleKeys();
        $enKeys = $this->collectLocaleKeys('en-GB');
        $ruKeys = $this->collectLocaleKeys('ru-RU');

        self::assertSame([], array_values(array_diff($usedKeys, $enKeys)), 'Missing en-GB language keys.');
        self::assertSame([], array_values(array_diff($usedKeys, $ruKeys)), 'Missing ru-RU language keys.');
    }

    /**
     * В language files не должно быть пустых ключей.
     */
    public function testLanguageFilesDoNotContainEmptyKeys(): void
    {
        foreach (glob(dirname(__DIR__, 3) . '/language/*/mod_ishop_filter*.ini') ?: [] as $file) {
            $parsed = IniFileReader::read($file);

            self::assertNotContains('', $parsed['keys'], 'Empty key found in ' . $file);
        }
    }

    /**
     * Собирает ключи локали из .ini и .sys.ini вместе.
     */
    private function collectLocaleKeys(string $locale): array
    {
        $keys = [];

        foreach (glob(dirname(__DIR__, 3) . '/language/' . $locale . '/mod_ishop_filter*.ini') ?: [] as $file) {
            $keys = array_merge($keys, IniFileReader::read($file)['keys']);
        }

        return array_values(array_unique($keys));
    }

    /**
     * Собирает используемые MOD_ISHOP_FILTER ключи из production PHP и XML.
     */
    private function collectUsedModuleKeys(): array
    {
        $root = dirname(__DIR__, 3);
        $keys = [];
        $files = array_merge(
            glob($root . '/tmpl/*.php') ?: [],
            glob($root . '/src/**/*.php') ?: [],
            [$root . '/script.php', $root . '/mod_ishop_filter.xml']
        );

        foreach ($files as $file) {
            $contents = (string) file_get_contents($file);

            if (preg_match_all('/MOD_ISHOP_FILTER[A-Z0-9_]+/', $contents, $matches)) {
                $keys = array_merge($keys, $matches[0]);
            }
        }

        sort($keys);

        return array_values(array_unique($keys));
    }
}
