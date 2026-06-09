<?php

declare(strict_types=1);

namespace Tests\Php\Unit;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Language\Text;
use PHPUnit\Framework\TestCase;
use Tests\Php\Support\FakeEnvironment;

/**
 * Проверяет install/update script без реального Joomla installer.
 */
final class InstallerScriptTest extends TestCase
{
    /**
     * Подключает global-класс installer script один раз для всего test case.
     */
    public static function setUpBeforeClass(): void
    {
        require_once dirname(__DIR__, 3) . '/script.php';
    }

    /**
     * Сбрасывает stubs перед каждым тестом installer script.
     */
    protected function setUp(): void
    {
        InstallerScript::reset();
        Text::reset();
    }

    /**
     * Installer script должен фиксировать минимальные версии PHP и Joomla.
     */
    public function testMinimumVersionsMatchManifestRequirements(): void
    {
        $script = $this->createScript();

        self::assertSame('8.3', $this->readProtectedProperty($script, 'minimumPhp'));
        self::assertSame('6.0.0', $this->readProtectedProperty($script, 'minimumJoomla'));
    }

    /**
     * Constructor должен брать application через Joomla Factory.
     */
    public function testConstructorReadsApplicationFromFactory(): void
    {
        $environment = FakeEnvironment::install();
        $script = new \Mod_IshopfilterInstallerScript();

        self::assertSame($environment['app'], Factory::getApplication());
        self::assertSame($environment['app'], $this->readProtectedProperty($script, 'app'));
    }

    /**
     * preflight должен вернуть false, если parent Joomla preflight отклоняет операцию.
     */
    public function testPreflightReturnsFalseWhenParentPreflightFails(): void
    {
        $script = $this->createScript();
        InstallerScript::$preflightResult = false;

        self::assertFalse($script->preflight('update', new InstallerAdapter()));
    }

    /**
     * preflight должен продолжить установку, если parent Joomla preflight успешен.
     */
    public function testPreflightReturnsTrueWhenParentPreflightPasses(): void
    {
        $script = $this->createScript();
        InstallerScript::$preflightResult = true;

        self::assertTrue($script->preflight('install', new InstallerAdapter()));
    }

    /**
     * postflight(update) должен удалить устаревшие файлы и вывести install summary.
     */
    public function testPostflightUpdateRemovesFilesAndPrintsSummary(): void
    {
        $script = $this->createScript();
        $manifest = new \SimpleXMLElement(
            '<extension><name>mod_ishop_filter</name><version>9.8.7</version><author>Pavel Lange</author></extension>'
        );

        ob_start();
        $result = $script->postflight('update', new InstallerAdapter($manifest));
        $output = (string) ob_get_clean();

        self::assertTrue($result);
        self::assertSame(1, InstallerScript::$removeFilesCalls);
        self::assertStringContainsString('mod_ishop_filter', $output);
        self::assertStringContainsString('9.8.7', $output);
        self::assertStringContainsString('Pavel Lange', $output);
        self::assertStringContainsString('https://github.com/i-lange/mod_ishop_filter', $output);
    }

    /**
     * postflight(uninstall) должен добавить warning-сообщение в application.
     */
    public function testPostflightUninstallEnqueuesWarningMessage(): void
    {
        $environment = FakeEnvironment::install();
        $script = new \Mod_IshopfilterInstallerScript();

        self::assertTrue($script->postflight('uninstall', new InstallerAdapter()));
        self::assertSame(1, InstallerScript::$removeFilesCalls);
        self::assertSame(
            [['MOD_ISHOP_FILTER_XML_UNINSTALL_OK', 'warning']],
            $environment['app']->messages
        );
    }

    /**
     * Создает script с уже установленным fake application.
     */
    private function createScript(): \Mod_IshopfilterInstallerScript
    {
        FakeEnvironment::install();

        return new \Mod_IshopfilterInstallerScript();
    }

    /**
     * Читает protected-свойство через reflection без изменения production API.
     */
    private function readProtectedProperty(object $object, string $property): mixed
    {
        $reflection = new \ReflectionProperty($object, $property);
        $reflection->setAccessible(true);

        return $reflection->getValue($object);
    }
}
