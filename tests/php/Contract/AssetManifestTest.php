<?php

declare(strict_types=1);

namespace Tests\Php\Contract;

use PHPUnit\Framework\TestCase;

/**
 * Проверяет Joomla Web Asset manifest модуля.
 */
final class AssetManifestTest extends TestCase
{
    /**
     * Asset manifest должен быть валидным JSON и синхронизированным по version.
     */
    public function testAssetManifestVersionMatchesPackageAndXml(): void
    {
        $assets = $this->loadAssets();
        $package = json_decode((string) file_get_contents(dirname(__DIR__, 3) . '/package.json'), true, 512, JSON_THROW_ON_ERROR);
        $xml = simplexml_load_file(dirname(__DIR__, 3) . '/mod_ishop_filter.xml');

        self::assertSame('mod_ishop_filter', $assets['name']);
        self::assertSame($package['version'], $assets['version']);
        self::assertSame((string) $xml->version, $assets['version']);
    }

    /**
     * Manifest должен содержать style и script assets для front/range entrypoints.
     */
    public function testAssetManifestContainsExpectedAssets(): void
    {
        $assets = $this->indexAssets($this->loadAssets()['assets']);

        self::assertArrayHasKey('style:ishop_filter.front', $assets);
        self::assertArrayHasKey('style:ishop_filter.range', $assets);
        self::assertArrayHasKey('script:ishop_filter.front', $assets);
        self::assertArrayHasKey('script:ishop_filter.range', $assets);
    }

    /**
     * Script assets должны быть defer и сохранять dependency order.
     */
    public function testScriptAssetsHaveExpectedDependenciesAndAttributes(): void
    {
        $assets = $this->indexAssets($this->loadAssets()['assets']);
        $rangeScript = $assets['script:ishop_filter.range'];
        $frontScript = $assets['script:ishop_filter.front'];

        self::assertTrue($rangeScript['attributes']['defer'] ?? false);
        self::assertTrue($frontScript['attributes']['defer'] ?? false);
        self::assertContains('core', $rangeScript['dependencies'] ?? []);
        self::assertContains('core', $frontScript['dependencies'] ?? []);
        self::assertContains('ishop_filter.range', $frontScript['dependencies'] ?? []);
    }

    /**
     * Asset URI должны соответствовать существующим source-файлам проекта.
     */
    public function testAssetUrisPointToExistingFiles(): void
    {
        $root = dirname(__DIR__, 3);

        foreach ($this->loadAssets()['assets'] as $asset) {
            $basename = basename((string) $asset['uri']);
            $directory = $asset['type'] === 'style' ? 'css' : 'js';
            $sourceFile = $root . '/media/' . $directory . '/' . $basename;

            self::assertFileExists($sourceFile, 'Asset source file is missing for uri ' . $asset['uri']);
        }
    }

    /**
     * Читает asset manifest как associative array.
     */
    private function loadAssets(): array
    {
        return json_decode(
            (string) file_get_contents(dirname(__DIR__, 3) . '/media/joomla.asset.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    /**
     * Индексирует assets по type:name для простых assertions.
     */
    private function indexAssets(array $assets): array
    {
        $indexed = [];

        foreach ($assets as $asset) {
            $indexed[$asset['type'] . ':' . $asset['name']] = $asset;
        }

        return $indexed;
    }
}
