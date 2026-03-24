<?php
/**
 * @package    mod_ishop_filter
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/mod_ishop_filter
 * @copyright  (C) 2026 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

use Joomla\CMS\Extension\Service\Provider\HelperFactory;
use Joomla\CMS\Extension\Service\Provider\Module;
use Joomla\CMS\Extension\Service\Provider\ModuleDispatcherFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

defined('_JEXEC') or exit;

/**
 * Класс Service Provider для модуля mod_ishop_filter
 * @since  1.0.0
 */
return new class implements ServiceProviderInterface
{
    /**
     * Регистрируем сервисы с помощью контейнера внедрения зависимостей
     * @param Container $container Контейнер DI
     * @since 1.0.0
     */
    public function register(Container $container)
    {
        $container->registerServiceProvider(new ModuleDispatcherFactory('\\Ilange\\Module\\Ishopfilter'));
        $container->registerServiceProvider(new HelperFactory('\\Ilange\\Module\\Ishopfilter\\Site\\Helper'));
        $container->registerServiceProvider(new Module);
    }
};