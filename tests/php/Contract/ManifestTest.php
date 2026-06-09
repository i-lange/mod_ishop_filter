<?php

declare(strict_types=1);

namespace Tests\Php\Contract;

use PHPUnit\Framework\TestCase;

/**
 * Проверяет installable XML manifest модуля.
 */
final class ManifestTest extends TestCase
{
    /**
     * XML manifest должен быть валидным и описывать site module upgrade.
     */
    public function testManifestRootAndCoreMetadataAreValid(): void
    {
        $manifest = $this->loadManifest();

        self::assertSame('module', (string) $manifest['type']);
        self::assertSame('site', (string) $manifest['client']);
        self::assertSame('upgrade', (string) $manifest['method']);
        self::assertSame('mod_ishop_filter', (string) $manifest->name);
        self::assertNotSame('', trim((string) $manifest->version));
        self::assertSame('script.php', (string) $manifest->scriptfile);
        self::assertSame('Ilange\Module\Ishopfilter', (string) $manifest->namespace);
        self::assertSame('src', (string) $manifest->namespace['path']);
    }

    /**
     * Manifest должен явно включать runtime-файлы модуля.
     */
    public function testManifestListsRequiredFiles(): void
    {
        $manifest = $this->loadManifest();
        $folders = [];
        $filenames = [];

        foreach ($manifest->files->folder as $folder) {
            $folders[] = (string) $folder;
        }

        foreach ($manifest->files->filename as $filename) {
            $filenames[] = (string) $filename;
        }

        self::assertContains('services', $folders);
        self::assertContains('src', $folders);
        self::assertContains('tmpl', $folders);
        self::assertContains('script.php', $filenames);
        self::assertSame('mod_ishop_filter', (string) $manifest->files->folder[0]['module']);
    }

    /**
     * Manifest должен описывать media destination и language-файлы обеих локалей.
     */
    public function testManifestListsMediaAndLanguages(): void
    {
        $manifest = $this->loadManifest();
        $languages = [];
        $mediaFolders = [];
        $mediaFiles = [];

        foreach ($manifest->languages->language as $language) {
            $languages[(string) $language['tag']][] = (string) $language;
        }

        foreach ($manifest->media->folder as $folder) {
            $mediaFolders[] = (string) $folder;
        }

        foreach ($manifest->media->filename as $filename) {
            $mediaFiles[] = (string) $filename;
        }

        self::assertSame('mod_ishop_filter', (string) $manifest->media['destination']);
        self::assertSame('media', (string) $manifest->media['folder']);
        self::assertContains('joomla.asset.json', $mediaFiles);
        self::assertContains('css', $mediaFolders);
        self::assertContains('js', $mediaFolders);
        self::assertContains('language/en-GB/mod_ishop_filter.ini', $languages['en-GB'] ?? []);
        self::assertContains('language/en-GB/mod_ishop_filter.sys.ini', $languages['en-GB'] ?? []);
        self::assertContains('language/ru-RU/mod_ishop_filter.ini', $languages['ru-RU'] ?? []);
        self::assertContains('language/ru-RU/mod_ishop_filter.sys.ini', $languages['ru-RU'] ?? []);
    }

    /**
     * Загружает manifest как SimpleXML и проверяет сам факт парсинга.
     */
    private function loadManifest(): \SimpleXMLElement
    {
        $manifest = simplexml_load_file(dirname(__DIR__, 3) . '/mod_ishop_filter.xml');

        self::assertInstanceOf(\SimpleXMLElement::class, $manifest);

        return $manifest;
    }
}
