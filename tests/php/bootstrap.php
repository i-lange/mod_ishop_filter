<?php

declare(strict_types=1);

/**
 * Автономный bootstrap тестов: он не запускает Joomla CMS, базу данных,
 * настоящий Web Asset Manager и runtime-зависимость com_ishop.
 */

defined('_JEXEC') || define('_JEXEC', 1);
defined('JPATH_SITE') || define('JPATH_SITE', dirname(__DIR__, 2));

$autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';

if (is_file($autoload)) {
    require_once $autoload;
}

require_once __DIR__ . '/stubs/JoomlaStubs.php';
require_once __DIR__ . '/stubs/IshopStubs.php';
require_once __DIR__ . '/Support/TestDoubles.php';

spl_autoload_register(
    /**
     * Подключает production-классы модуля напрямую из src без Composer autoload.
     */
    static function (string $class): void {
        $prefix = 'Ilange\\Module\\Ishopfilter\\';
        $root = dirname(__DIR__, 2) . '/src/';

        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relative = str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        $relative = preg_replace('#^Site/#', '', $relative);
        $file = $root . $relative;

        if (is_file($file)) {
            require_once $file;
        }
    }
);

spl_autoload_register(
    /**
     * Подключает support-классы тестов из tests/php/Support.
     */
    static function (string $class): void {
        $prefix = 'Tests\\Php\\Support\\';
        $root = __DIR__ . '/Support/';

        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relative = str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        $file = $root . $relative;

        if (is_file($file)) {
            require_once $file;
        }
    }
);
