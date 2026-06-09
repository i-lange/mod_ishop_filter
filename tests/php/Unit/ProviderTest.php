<?php

declare(strict_types=1);

namespace Tests\Php\Unit;

use Joomla\CMS\Extension\Service\Provider\HelperFactory;
use Joomla\CMS\Extension\Service\Provider\Module;
use Joomla\CMS\Extension\Service\Provider\ModuleDispatcherFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use PHPUnit\Framework\TestCase;

/**
 * Проверяет автономный контракт service provider модуля.
 */
final class ProviderTest extends TestCase
{
    /**
     * Provider должен возвращать объект с Joomla service-provider контрактом.
     */
    public function testProviderImplementsServiceProviderInterface(): void
    {
        $provider = require dirname(__DIR__, 3) . '/services/provider.php';

        self::assertInstanceOf(ServiceProviderInterface::class, $provider);
    }

    /**
     * Регистрация должна сохранять dispatcher, helper и module providers.
     */
    public function testProviderRegistersExpectedServiceProviders(): void
    {
        $provider = require dirname(__DIR__, 3) . '/services/provider.php';
        $container = new Container();

        $provider->register($container);

        self::assertCount(3, $container->registeredProviders);
        self::assertInstanceOf(ModuleDispatcherFactory::class, $container->registeredProviders[0]);
        self::assertInstanceOf(HelperFactory::class, $container->registeredProviders[1]);
        self::assertInstanceOf(Module::class, $container->registeredProviders[2]);
    }

    /**
     * Namespace-значения фиксируют PSR-4 контракт модуля для Joomla DI.
     */
    public function testProviderUsesIshopFilterNamespaces(): void
    {
        $provider = require dirname(__DIR__, 3) . '/services/provider.php';
        $container = new Container();

        $provider->register($container);

        self::assertSame('\\Ilange\\Module\\Ishopfilter', $container->registeredProviders[0]->namespace);
        self::assertSame(
            '\\Ilange\\Module\\Ishopfilter\\Site\\Helper',
            $container->registeredProviders[1]->namespace
        );
    }
}
