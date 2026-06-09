<?php

declare(strict_types=1);

namespace Tests\Php\Contract;

use PHPUnit\Framework\TestCase;

/**
 * Проверяет статические инварианты production-кода модуля.
 */
final class StaticContractsTest extends TestCase
{
    /**
     * Production PHP-файлы должны иметь Joomla guard.
     */
    public function testProductionPhpFilesContainJoomlaGuard(): void
    {
        foreach ($this->productionPhpFiles() as $file) {
            $contents = (string) file_get_contents($file);

            self::assertMatchesRegularExpression(
                "/defined\\('_JEXEC'\\)\\s+or\\s+(die|exit)/",
                $contents,
                'Missing Joomla guard in ' . $file
            );
        }
    }

    /**
     * JS-фильтр должен обращаться к canonical com_ishop endpoints.
     */
    public function testFrontJsUsesComIshopEndpoints(): void
    {
        $contents = (string) file_get_contents(dirname(__DIR__, 3) . '/media/js/front.js');

        self::assertStringContainsString('option=com_ishop', $contents);
        self::assertStringNotContainsString('option=com_ajax&module=ishop_filter', $contents);
        self::assertStringContainsString('Joomla.getOptions("csrf.token"', $contents);
        self::assertStringContainsString('data.availableOptions', $contents);
        self::assertStringContainsString('available.ishop_fields', $contents);
    }

    /**
     * Layout должен передавать category id в data-атрибуте формы.
     */
    public function testDefaultLayoutContainsCategoryIdDataContract(): void
    {
        $contents = (string) file_get_contents(dirname(__DIR__, 3) . '/tmpl/default.php');

        self::assertStringContainsString('data-category-id', $contents);
    }

    /**
     * Возвращает production PHP-файлы без тестовой инфраструктуры.
     */
    private function productionPhpFiles(): array
    {
        $root = dirname(__DIR__, 3);

        return array_merge(
            [$root . '/script.php', $root . '/services/provider.php'],
            glob($root . '/src/**/*.php') ?: [],
            glob($root . '/tmpl/*.php') ?: []
        );
    }
}
