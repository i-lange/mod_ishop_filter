<?php

declare(strict_types=1);

namespace Tests\Php\Support;

/**
 * Параметры модуля с тем же методом get(), который использует Joomla Registry.
 */
final class ParameterBag
{
    public function __construct(private array $values = [])
    {
    }

    /**
     * Возвращает значение параметра или default.
     */
    public function get(string $name, mixed $default = null): mixed
    {
        return $this->values[$name] ?? $default;
    }
}

/**
 * Input double поддерживает методы, которые вызывают helper и layout.
 */
final class FakeInput
{
    public function __construct(private array $values = [])
    {
    }

    /**
     * Возвращает сырое значение input.
     */
    public function get(string $name, mixed $default = null): mixed
    {
        return $this->values[$name] ?? $default;
    }

    /**
     * Возвращает input-значение как integer.
     */
    public function getInt(string $name, int $default = 0): int
    {
        return (int) ($this->values[$name] ?? $default);
    }
}

/**
 * Identity double возвращает уровни доступа пользователя.
 */
final class FakeIdentity
{
    public function __construct(private array $levels = [1])
    {
    }

    /**
     * Возвращает уровни просмотра для layout-проверок складов.
     */
    public function getAuthorisedViewLevels(): array
    {
        return $this->levels;
    }
}

/**
 * Registry double для проверки addRegistryFile().
 */
final class FakeWebAssetRegistry
{
    public array $registryFiles = [];

    /**
     * Записывает путь registry file, который регистрирует dispatcher.
     */
    public function addRegistryFile(string $file): void
    {
        $this->registryFiles[] = $file;
    }
}

/**
 * WebAssetManager double для layout и dispatcher тестов.
 */
final class FakeWebAssetManager
{
    public FakeWebAssetRegistry $registry;
    public array $scripts = [];
    public array $styles = [];

    public function __construct()
    {
        $this->registry = new FakeWebAssetRegistry();
    }

    /**
     * Возвращает registry double.
     */
    public function getRegistry(): FakeWebAssetRegistry
    {
        return $this->registry;
    }

    /**
     * Записывает подключенные script assets.
     */
    public function useScript(string $asset): void
    {
        $this->scripts[] = $asset;
    }

    /**
     * Записывает подключенные style assets.
     */
    public function useStyle(string $asset): void
    {
        $this->styles[] = $asset;
    }
}

/**
 * Document double возвращает один WebAssetManager.
 */
final class FakeDocument
{
    public int $getWebAssetManagerCalls = 0;

    public function __construct(private FakeWebAssetManager $webAssetManager)
    {
    }

    /**
     * Возвращает WebAssetManager double.
     */
    public function getWebAssetManager(): FakeWebAssetManager
    {
        $this->getWebAssetManagerCalls++;

        return $this->webAssetManager;
    }
}

/**
 * Category model double для helper-тестов.
 */
final class FakeCategoryModel
{
    public int $getFilterObjectCalls = 0;
    public array $getStateCalls = [];

    public function __construct(public mixed $filterObject = null, private array $state = [])
    {
    }

    /**
     * Возвращает настроенный объект фильтра.
     */
    public function getFilterObject(): mixed
    {
        $this->getFilterObjectCalls++;

        return $this->filterObject;
    }

    /**
     * Возвращает состояние модели категории.
     */
    public function getState(string $name, mixed $default = null): mixed
    {
        $this->getStateCalls[] = [$name, $default];

        return $this->state[$name] ?? $default;
    }
}

/**
 * MVC factory double для проверки createModel().
 */
final class FakeMVCFactory
{
    public array $createModelCalls = [];

    public function __construct(private FakeCategoryModel $categoryModel)
    {
    }

    /**
     * Возвращает category model и фиксирует имя модели.
     */
    public function createModel(string $name, string $client): FakeCategoryModel
    {
        $this->createModelCalls[] = [$name, $client];

        return $this->categoryModel;
    }
}

/**
 * Component double для цепочки bootComponent()->getMVCFactory().
 */
final class FakeComponent
{
    public int $getMVCFactoryCalls = 0;

    public function __construct(private FakeMVCFactory $mvcFactory)
    {
    }

    /**
     * Возвращает MVC factory double.
     */
    public function getMVCFactory(): FakeMVCFactory
    {
        $this->getMVCFactoryCalls++;

        return $this->mvcFactory;
    }
}

/**
 * Helper double для dispatcher-тестов.
 */
final class FakeFilterHelper
{
    public int $prepareFilterCalls = 0;

    public function __construct(private mixed $filter)
    {
    }

    /**
     * Возвращает filter object и фиксирует вызов helper.
     */
    public function prepareFilter(): mixed
    {
        $this->prepareFilterCalls++;

        return $this->filter;
    }
}

/**
 * Module boot double для цепочки bootModule()->getHelper().
 */
final class FakeModuleBoot
{
    public array $getHelperCalls = [];

    public function __construct(private FakeFilterHelper $helper)
    {
    }

    /**
     * Возвращает helper double по имени.
     */
    public function getHelper(string $name): FakeFilterHelper
    {
        $this->getHelperCalls[] = $name;

        return $this->helper;
    }
}

/**
 * Application double для dispatcher, helper, layout и installer script.
 */
final class FakeApp
{
    public array $bootComponentCalls = [];
    public array $bootModuleCalls = [];
    public array $messages = [];

    public function __construct(
        private FakeInput $input,
        private FakeDocument $document,
        private FakeComponent $component,
        private FakeModuleBoot $moduleBoot,
        private FakeIdentity $identity = new FakeIdentity()
    ) {
    }

    /**
     * Возвращает input double.
     */
    public function getInput(): FakeInput
    {
        return $this->input;
    }

    /**
     * Возвращает document double.
     */
    public function getDocument(): FakeDocument
    {
        return $this->document;
    }

    /**
     * Возвращает component double и фиксирует имя компонента.
     */
    public function bootComponent(string $name): FakeComponent
    {
        $this->bootComponentCalls[] = $name;

        return $this->component;
    }

    /**
     * Возвращает module boot double и фиксирует имя модуля.
     */
    public function bootModule(string $name, string $client): FakeModuleBoot
    {
        $this->bootModuleCalls[] = [$name, $client];

        return $this->moduleBoot;
    }

    /**
     * Возвращает identity double.
     */
    public function getIdentity(): FakeIdentity
    {
        return $this->identity;
    }

    /**
     * Фиксирует Joomla UI message.
     */
    public function enqueueMessage(string $message, string $type): void
    {
        $this->messages[] = [$message, $type];
    }
}

/**
 * Результат выполнения layout с HTML и списком подключенных assets.
 */
final class LayoutRenderResult
{
    public function __construct(
        public string $html,
        public FakeWebAssetManager $webAssetManager,
        public FakeApp $app
    ) {
    }
}

/**
 * Выполняет tmpl/default.php в изолированном наборе переменных без Joomla CMS.
 */
final class LayoutRenderer
{
    /**
     * Рендерит layout и возвращает HTML вместе с doubles.
     */
    public function render(array $paramOverrides = [], ?object $filter = null, array $inputValues = []): LayoutRenderResult
    {
        \Joomla\CMS\Helper\ModuleHelper::reset();
        \Joomla\CMS\HTML\HTMLHelper::reset();
        \Joomla\CMS\Language\Text::reset();
        \Joomla\CMS\Uri\Uri::reset();

        $webAssetManager = new FakeWebAssetManager();
        $categoryModel = new FakeCategoryModel($filter);
        $mvcFactory = new FakeMVCFactory($categoryModel);
        $component = new FakeComponent($mvcFactory);
        $moduleBoot = new FakeModuleBoot(new FakeFilterHelper($filter));
        $input = new FakeInput(array_replace(['id' => 12, 'Itemid' => 34], $inputValues));
        $app = new FakeApp($input, new FakeDocument($webAssetManager), $component, $moduleBoot);

        \Joomla\CMS\Factory::setApplication($app);

        $defaults = [
            'layout' => 'default',
            'use_js' => 1,
            'use_css' => 1,
            'show_prices' => 1,
            'show_fields' => 1,
            'show_sales' => 1,
            'show_warehouses' => 1,
            'show_brand' => 1,
            'show_width' => 1,
            'show_height' => 1,
            'show_depth' => 1,
            'show_weight' => 1,
            'warehouses_access' => 1,
        ];

        $params = new ParameterBag(array_replace($defaults, $paramOverrides));
        $module = (object) ['id' => $paramOverrides['module_id'] ?? 101, 'params' => $params];
        $template = new \stdClass();
        $wa = $webAssetManager;

        ob_start();
        include dirname(__DIR__, 3) . '/tmpl/default.php';
        $html = (string) ob_get_clean();

        return new LayoutRenderResult($html, $webAssetManager, $app);
    }
}

/**
 * Фабрика создает согласованный набор doubles для unit-тестов.
 */
final class FakeEnvironment
{
    /**
     * Создает application double и устанавливает его в Joomla Factory.
     */
    public static function install(array $inputValues = [], mixed $filter = null, array $state = []): array
    {
        \Ilange\Component\Ishop\Site\Service\FilterRules::reset();
        \Ilange\Component\Ishop\Site\Service\FilterAvailabilityService::reset();

        $webAssetManager = new FakeWebAssetManager();
        $categoryModel = new FakeCategoryModel($filter, $state);
        $mvcFactory = new FakeMVCFactory($categoryModel);
        $component = new FakeComponent($mvcFactory);
        $helper = new FakeFilterHelper($filter);
        $moduleBoot = new FakeModuleBoot($helper);
        $input = new FakeInput($inputValues);
        $app = new FakeApp($input, new FakeDocument($webAssetManager), $component, $moduleBoot);

        \Joomla\CMS\Factory::setApplication($app);

        return [
            'app' => $app,
            'input' => $input,
            'webAssetManager' => $webAssetManager,
            'categoryModel' => $categoryModel,
            'mvcFactory' => $mvcFactory,
            'component' => $component,
            'helper' => $helper,
            'moduleBoot' => $moduleBoot,
        ];
    }
}

/**
 * Утилита чтения language-файлов Joomla с поиском дублей.
 */
final class IniFileReader
{
    /**
     * Возвращает ключи и дубли без интерпретации переводов.
     */
    public static function read(string $file): array
    {
        $keys = [];
        $duplicates = [];

        foreach (file($file, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            $trimmed = trim($line);

            if ($trimmed === '' || str_starts_with($trimmed, ';') || str_starts_with($trimmed, '#')) {
                continue;
            }

            if (!str_contains($trimmed, '=')) {
                continue;
            }

            [$key] = explode('=', $trimmed, 2);
            $key = trim($key);

            if (isset($keys[$key])) {
                $duplicates[] = $key;
            }

            $keys[$key] = true;
        }

        return [
            'keys' => array_keys($keys),
            'duplicates' => $duplicates,
        ];
    }
}
