<?php

declare(strict_types=1);

/**
 * Минимальные stubs Joomla для автономных тестов модуля.
 * Они фиксируют только контракты, которые реально использует production-код.
 */

namespace Joomla\CMS;

final class Factory
{
    private static mixed $application = null;

    /**
     * Сохраняет application double для последующих Factory::getApplication().
     */
    public static function setApplication(mixed $application): void
    {
        self::$application = $application;
    }

    /**
     * Возвращает тестовое приложение без запуска Joomla application context.
     */
    public static function getApplication(): mixed
    {
        return self::$application;
    }
}

namespace Joomla\CMS\Dispatcher;

abstract class AbstractModuleDispatcher
{
    protected object $module;
    protected mixed $app;
    protected mixed $input;
    protected mixed $params;
    protected mixed $template;

    /**
     * Хранит зависимости, которые реальный dispatcher получает от Joomla.
     */
    public function __construct(
        ?object $module = null,
        mixed $app = null,
        mixed $input = null,
        mixed $params = null,
        mixed $template = null
    ) {
        $this->module = $module ?? (object) ['id' => 1, 'params' => $params];
        $this->app = $app;
        $this->input = $input;
        $this->params = $params ?? ($this->module->params ?? null);
        $this->template = $template;
    }

    /**
     * Возвращает базовый набор ключей layout data.
     */
    protected function getLayoutData()
    {
        return [
            'module' => $this->module,
            'app' => $this->app,
            'input' => $this->input,
            'params' => $this->params,
            'template' => $this->template,
        ];
    }
}

namespace Joomla\CMS\Extension\Service\Provider;

final class HelperFactory
{
    public string $namespace;

    /**
     * Сохраняет namespace helper factory для проверки provider-контракта.
     */
    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
    }
}

final class Module
{
    /**
     * Класс-маркер для проверки регистрации Joomla module provider.
     */
}

final class ModuleDispatcherFactory
{
    public string $namespace;

    /**
     * Сохраняет namespace dispatcher factory для проверки provider-контракта.
     */
    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
    }
}

namespace Joomla\CMS\Helper;

interface HelperFactoryAwareInterface
{
    /**
     * Минимальный marker-interface для dispatcher модуля.
     */
}

trait HelperFactoryAwareTrait
{
    /**
     * Trait намеренно пустой: тестам важен только факт совместимости класса.
     */
}

final class ModuleHelper
{
    public static string $root;
    public static array $calls = [];

    /**
     * Сбрасывает путь layout root и историю вызовов.
     */
    public static function reset(): void
    {
        self::$root = dirname(__DIR__, 3);
        self::$calls = [];
    }

    /**
     * Возвращает путь к partial layout без обращения к Joomla module registry.
     */
    public static function getLayoutPath(string $module, string $layout): string
    {
        self::$calls[] = [$module, $layout];

        return self::$root . '/tmpl/' . $layout . '.php';
    }
}

ModuleHelper::reset();

namespace Joomla\CMS\HTML;

final class HTMLHelper
{
    public static array $calls = [];

    /**
     * Сбрасывает историю HTML helper вызовов.
     */
    public static function reset(): void
    {
        self::$calls = [];
    }

    /**
     * Возвращает предсказуемый CSRF input для form.token.
     */
    public static function _(string $name, mixed ...$arguments): string
    {
        self::$calls[] = [$name, $arguments];

        if ($name === 'form.token') {
            return '<input type="hidden" name="csrf.token" value="1">';
        }

        return '';
    }
}

namespace Joomla\CMS\Installer;

class InstallerAdapter
{
    private \SimpleXMLElement $manifest;

    /**
     * Хранит manifest, который installer script читает во время postflight().
     */
    public function __construct(?\SimpleXMLElement $manifest = null)
    {
        $this->manifest = $manifest ?? new \SimpleXMLElement(
            '<extension><name>mod_ishop_filter</name><version>1.0.0</version><author>Pavel Lange</author></extension>'
        );
    }

    /**
     * Возвращает manifest без реального Joomla installer.
     */
    public function getManifest(): \SimpleXMLElement
    {
        return $this->manifest;
    }
}

class InstallerScript
{
    public static bool $preflightResult = true;
    public static int $removeFilesCalls = 0;

    /**
     * Сбрасывает состояние parent-stub перед каждым тестом.
     */
    public static function reset(): void
    {
        self::$preflightResult = true;
        self::$removeFilesCalls = 0;
    }

    /**
     * Имитирует результат базового Joomla preflight().
     */
    public function preflight($type, $parent): bool
    {
        return self::$preflightResult;
    }

    /**
     * Фиксирует вызов удаления устаревших файлов без файловых операций.
     */
    protected function removeFiles(): void
    {
        self::$removeFilesCalls++;
    }
}

namespace Joomla\CMS\Language;

final class Text
{
    public static array $calls = [];
    public static array $sprintfCalls = [];
    public static array $translations = [];

    /**
     * Сбрасывает вызовы и тестовые переводы.
     */
    public static function reset(): void
    {
        self::$calls = [];
        self::$sprintfCalls = [];
        self::$translations = [];
    }

    /**
     * Устанавливает предсказуемые переводы для тестов.
     */
    public static function setTranslations(array $translations): void
    {
        self::$translations = $translations;
    }

    /**
     * Возвращает перевод ключа или сам ключ как стабильное тестовое значение.
     */
    public static function _(string $key): string
    {
        self::$calls[] = $key;

        return self::$translations[$key] ?? $key;
    }

    /**
     * Имитирует Text::sprintf() с переводом ключа перед форматированием.
     */
    public static function sprintf(string $key, mixed ...$arguments): string
    {
        self::$sprintfCalls[] = [$key, $arguments];
        $template = self::$translations[$key] ?? $key;

        return sprintf($template, ...$arguments);
    }
}

namespace Joomla\CMS\Uri;

final class Uri
{
    public static string $root = '';
    public static string $current = 'https://example.test/category';

    /**
     * Сбрасывает URL-значения к стабильным defaults.
     */
    public static function reset(): void
    {
        self::$root = '';
        self::$current = 'https://example.test/category';
    }

    /**
     * Возвращает root URL так же, как layout ожидает от Uri::root(true).
     */
    public static function root(bool $pathOnly = false): string
    {
        return self::$root;
    }

    /**
     * Возвращает объект текущего URL с методом toString().
     */
    public static function getInstance(): object
    {
        return new class {
            /**
             * Возвращает текущий URL страницы категории.
             */
            public function toString(): string
            {
                return Uri::$current;
            }
        };
    }
}

namespace Joomla\DI;

class Container
{
    public array $registeredProviders = [];

    /**
     * Записывает providers в порядке регистрации.
     */
    public function registerServiceProvider(object $provider): void
    {
        $this->registeredProviders[] = $provider;
    }
}

interface ServiceProviderInterface
{
    /**
     * Минимальный контракт Joomla service provider.
     */
    public function register(Container $container);
}
