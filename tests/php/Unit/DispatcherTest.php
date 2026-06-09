<?php

declare(strict_types=1);

namespace Tests\Php\Unit;

use Ilange\Module\Ishopfilter\Site\Dispatcher\Dispatcher;
use PHPUnit\Framework\TestCase;
use Tests\Php\Support\FakeEnvironment;
use Tests\Php\Support\ParameterBag;

/**
 * Проверяет данные, которые dispatcher передает в layout модуля.
 */
final class DispatcherTest extends TestCase
{
    /**
     * Dispatcher должен добавить filter, WebAssetManager и сохранить базовые ключи layout data.
     */
    public function testGetLayoutDataAddsFilterAndWebAssets(): void
    {
        $filter = (object) ['empty' => false, 'total' => 3];
        $environment = FakeEnvironment::install([], $filter);
        $params = new ParameterBag();
        $module = (object) ['id' => 15, 'params' => $params];
        $template = new \stdClass();
        $dispatcher = new Dispatcher($module, $environment['app'], $environment['input'], $params, $template);

        $data = $this->invokeGetLayoutData($dispatcher);

        self::assertSame($module, $data['module']);
        self::assertSame($environment['app'], $data['app']);
        self::assertSame($environment['input'], $data['input']);
        self::assertSame($params, $data['params']);
        self::assertSame($template, $data['template']);
        self::assertSame($filter, $data['filter']);
        self::assertSame($environment['webAssetManager'], $data['wa']);
        self::assertSame([['mod_ishop_filter', 'Site']], $environment['app']->bootModuleCalls);
        self::assertSame(['IshopfilterHelper'], $environment['moduleBoot']->getHelperCalls);
        self::assertSame(1, $environment['helper']->prepareFilterCalls);
        self::assertSame(
            ['media/mod_ishop_filter/joomla.asset.json'],
            $environment['webAssetManager']->registry->registryFiles
        );
    }

    /**
     * Reflection нужен только потому, что Joomla dispatcher держит getLayoutData() protected.
     */
    private function invokeGetLayoutData(Dispatcher $dispatcher): array
    {
        $method = new \ReflectionMethod($dispatcher, 'getLayoutData');
        $method->setAccessible(true);

        return $method->invoke($dispatcher);
    }
}
