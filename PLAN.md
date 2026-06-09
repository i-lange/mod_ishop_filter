# План покрытия `mod_ishop_filter` автономными тестами

Этот план выполняется последовательно. Пункт отмечается как выполненный только после реализации, локального запуска указанной проверки и ручного просмотра результата. Цель - покрыть все, что можно проверить без установки модуля в реальный проект Joomla.

## 0. Подготовка

- [x] Зафиксировать текущее состояние проекта командой `git status --short`.
  - Проверка: в рабочем дереве понятны все существующие изменения; новые изменения плана не смешиваются с чужими правками.
  - Выполнено: в рабочем дереве был только новый `PLAN.md`.

- [x] Проверить доступность инструментов: `php -v`, `composer --version`, `node -v`, `pnpm -v`.
  - Проверка: PHP не ниже 8.3, Node не ниже 24, pnpm не ниже 10.3.
  - Выполнено: PHP 8.3.31, Composer 2.8.9 через `php C:\OSPanel\data\PHP-8.3\default\composer\composer.phar`, Node v24.14.0, pnpm 11.5.0.

- [x] Выполнить текущий baseline: `pnpm build`, `pnpm test`.
  - Проверка: текущая сборка проходит; `pnpm test` пока может быть заглушкой `No automated tests yet`.
  - Выполнено: `pnpm build` прошел, текущий `pnpm test` прошел как заглушка.

- [x] Зафиксировать границы автономного покрытия в этом файле.
  - Проверка: в плане явно исключены реальные Joomla runtime, база данных, SEF router, `com_ishop` runtime и установка модуля в сайт.
  - Выполнено: раздел 13 фиксирует границы автономных тестов.

## 1. PHP test infrastructure

- [x] Добавить `composer.json` с dev-зависимостью `phpunit/phpunit`.
  - Проверка: `composer install` создает `vendor/` и lock-файл без ошибок.
  - Выполнено: `composer.json` добавлен, `composer.lock` и `vendor/` созданы через OSPanel PHP 8.3 и Composer 2.8.9.

- [x] Добавить `phpunit.xml`.
  - Должны быть suites: `unit`, `layout`, `contract`.
  - Bootstrap: `tests/php/bootstrap.php`.
  - Source: `src`, `services`, `tmpl`, `script.php`.
  - Проверка: `vendor/bin/phpunit --list-tests` запускается без fatal errors.
  - Выполнено: `vendor/bin/phpunit --list-tests` запускается без fatal errors.

- [x] Создать `tests/php/bootstrap.php`.
  - Определить `_JEXEC`.
  - Подключить Composer autoload, если он есть.
  - Подключить Joomla stubs, Ishop stubs и support-классы.
  - Добавить autoload для namespace `Ilange\Module\Ishopfilter`.
  - Проверка: `vendor/bin/phpunit --bootstrap tests/php/bootstrap.php --list-tests` не падает.
  - Выполнено: bootstrap подключает stubs/support и проходит `vendor/bin/phpunit --bootstrap tests/php/bootstrap.php --list-tests`.

- [x] Создать структуру PHP-тестов:
  - `tests/php/Unit`
  - `tests/php/Layout`
  - `tests/php/Contract`
  - `tests/php/Support`
  - `tests/php/stubs`
  - Проверка: директории существуют, `phpunit.xml` указывает на них.
  - Выполнено: директории созданы и подключены в `phpunit.xml`.

## 2. PHP stubs and support

- [x] Добавить `tests/php/stubs/JoomlaStubs.php`.
  - Покрыть минимальные классы и методы:
    - `Joomla\CMS\Factory`
    - `Joomla\CMS\Language\Text`
    - `Joomla\CMS\HTML\HTMLHelper`
    - `Joomla\CMS\Helper\ModuleHelper`
    - `Joomla\CMS\Uri\Uri`
    - `Joomla\CMS\Installer\InstallerScript`
    - `Joomla\CMS\Installer\InstallerAdapter`
    - `Joomla\CMS\Dispatcher\AbstractModuleDispatcher`
    - `Joomla\CMS\Extension\Service\Provider\HelperFactory`
    - `Joomla\CMS\Extension\Service\Provider\ModuleDispatcherFactory`
    - `Joomla\CMS\Extension\Service\Provider\Module`
    - `Joomla\DI\Container`
    - `Joomla\DI\ServiceProviderInterface`
    - `Joomla\CMS\Session\Session`
  - Проверка: stubs позволяют подключить `services/provider.php`, `src/Dispatcher/Dispatcher.php`, `script.php` без fatal errors.
  - Выполнено: прямое подключение `services/provider.php`, `src/Dispatcher/Dispatcher.php`, `script.php`, `src/Helper/IshopfilterHelper.php` через bootstrap завершается `ok`.

- [x] Добавить `tests/php/stubs/IshopStubs.php`.
  - Покрыть минимальные классы:
    - `Ilange\Component\Ishop\Site\Service\FilterRules`
    - `Ilange\Component\Ishop\Site\Service\FilterAvailabilityService`
  - Добавить управляемые static/state hooks для проверки входных данных и возвращаемых значений.
  - Проверка: `src/Helper/IshopfilterHelper.php` подключается без наличия настоящего `com_ishop`.
  - Выполнено: helper подключается через `IshopStubs.php` без настоящего `com_ishop`.

- [x] Добавить support doubles для application, input, document, web asset manager, module boot, component boot, category model, params и layout rendering.
  - Проверка: doubles позволяют задавать input values, возвращать fake helper/model и фиксировать `enqueueMessage()`.
  - Выполнено: `FakeEnvironment` проверен через `php -r`, input/model цепочка и `enqueueMessage()` работают.

## 3. PHP unit tests

- [x] Реализовать `tests/php/Unit/ProviderTest.php`.
  - Проверить, что `services/provider.php` возвращает `ServiceProviderInterface`.
  - Проверить регистрацию `ModuleDispatcherFactory`, `HelperFactory`, `Module`.
  - Проверить namespace provider: `Ilange\Module\Ishopfilter`.
  - Проверка: `vendor/bin/phpunit --testsuite unit --filter ProviderTest`.
  - Выполнено: `ProviderTest` проходит, 3 теста и 7 assertions.

- [x] Реализовать `tests/php/Unit/DispatcherTest.php`.
  - Проверить, что `getLayoutData()` добавляет `filter`.
  - Проверить вызов helper `prepareFilter()`.
  - Проверить регистрацию `media/mod_ishop_filter/joomla.asset.json`.
  - Проверить наличие `wa` в layout data.
  - Проверить сохранение базовых ключей parent data: `module`, `app`, `input`, `params`, `template`.
  - Проверка: `vendor/bin/phpunit --testsuite unit --filter DispatcherTest`.
  - Выполнено: `DispatcherTest` проходит, 1 тест и 11 assertions.

- [x] Реализовать `tests/php/Unit/InstallerScriptTest.php`.
  - Проверить `minimumPhp = 8.3`.
  - Проверить `minimumJoomla = 6.0.0`.
  - Проверить, что constructor берет application через `Factory`.
  - Проверить `preflight()` при успешном и неуспешном parent preflight.
  - Проверить `postflight('update')`: вызывает `removeFiles()` и выводит блок с name/version/author.
  - Проверить `postflight('uninstall')`: добавляет warning message.
  - Проверка: `vendor/bin/phpunit --testsuite unit --filter InstallerScriptTest`.
  - Выполнено: `InstallerScriptTest` проходит, 6 тестов и 15 assertions.

- [x] Реализовать `tests/php/Unit/HelperTest.php` для `prepareFilter()`.
  - Проверить возврат пустого массива вне category view при наличии `id`.
  - Проверить fallback `controller` из `view`.
  - Проверить создание `CategoryModel` через `bootComponent('com_ishop')`.
  - Проверить category id из input `id`.
  - Проверить fallback category id из model state `category.id`.
  - Проверить, что при пустом filter или `empty=true` availability service не вызывается.
  - Проверить, что при валидном filter записываются `total` и `availableOptions`.
  - Проверка: `vendor/bin/phpunit --testsuite unit --filter HelperTest`.
  - Выполнено: `HelperTest` покрывает category context, fallback `view`, category id из input/state, пустой filter и вызовы availability service.

- [x] Расширить `HelperTest` проверкой нормализации active filters.
  - Через reflection проверить private `getActiveFilters()`.
  - Проверить numeric поля: `min_price`, `max_price`, `good_price`, размеры и вес.
  - Проверить `manufacturers`, `warehouses`.
  - Проверить mapping `active.fields` -> `ishop_fields`.
  - Проверить defaults `0` и `[]`.
  - Проверка: `vendor/bin/phpunit --testsuite unit --filter HelperTest`.
  - Выполнено: `HelperTest` проверяет numeric поля, manufacturers/warehouses, mapping `active.fields` -> `ishop_fields` и defaults.

- [x] Удалить legacy `IshopfilterHelper::getAjax()` после перехода AJAX preview/reset на endpoints `com_ishop`.
  - Удалены unit-тесты старого helper AJAX.
  - Удалены тестовые doubles/stubs под `sendJsonMessage()` и helper-level CSRF.
  - Удалены языковые ключи ошибок, которые использовались только старым endpoint.
  - Проверка: `vendor/bin/phpunit --testsuite unit --filter HelperTest`.
  - Выполнено: `HelperTest` проходит, 10 тестов и 49 assertions; весь текущий unit suite проходит, 20 тестов и 82 assertions.

## 4. PHP layout tests

- [x] Реализовать layout renderer для `tmpl/default.php` и partial layouts.
  - Renderer должен передавать fake `$module`, `$app`, `$input`, `$params`, `$template`, `$wa`, `$filter`.
  - Проверка: простой render с валидным filter возвращает HTML-строку без warnings.
  - Выполнено: `LayoutRenderer` рендерит реальный `tmpl/default.php` с partials и валидным fake filter.

- [x] Реализовать `tests/php/Layout/DefaultLayoutTest.php`.
  - Пустой `$filter` ничего не выводит.
  - `$filter->empty = true` ничего не выводит.
  - При `use_css=1` подключаются `ishop_filter.front` и `ishop_filter.range`.
  - При `use_js=1` подключаются `ishop_filter.front` и `ishop_filter.range`.
  - При `use_css=0` и `use_js=0` assets не подключаются.
  - Проверка: `vendor/bin/phpunit --testsuite layout --filter DefaultLayoutTest`.
  - Выполнено: `DefaultLayoutTest` проходит, проверяет early return и asset params.

- [x] Покрыть контракт формы в `DefaultLayoutTest`.
  - Проверить `name="ishop_filter"`.
  - Проверить `id="i-filter-{module id}"`.
  - Проверить `data-category-id`.
  - Проверить `data-item-id`.
  - Проверить `data-product-count`.
  - Проверить `data-preview-url`.
  - Проверить `data-reset-url`.
  - Проверить `data-submit-template`.
  - Проверить `data-submit-unavailable-text`.
  - Проверить CSRF token.
  - Проверка: layout suite проходит.
  - Выполнено: layout suite проходит, form data attributes и CSRF token проверены.

- [x] Покрыть endpoint-контракт в layout.
  - Preview URL содержит `option=com_ishop&task=filter.preview&format=json`.
  - Reset URL содержит `option=com_ishop&task=filter.reset&format=json`.
  - Не используется `com_ajax` как основной endpoint.
  - Проверка: layout suite проходит.
  - Выполнено: layout suite проходит, canonical endpoints `com_ishop` проверены.

- [x] Покрыть submit/reset/active-tags/loading hooks.
  - Submit button связан с формой через `form`.
  - Submit имеет `data-filter-submit`.
  - Reset имеет `data-filter-reset`.
  - Active tags container имеет `data-filter-active-tags`.
  - Loading overlay содержит status с visually-hidden text.
  - Проверка: layout suite проходит.
  - Выполнено: layout suite проходит, submit/reset/tags/loading hooks проверены.

- [x] Покрыть состояние submit при нулевом и ненулевом количестве товаров.
  - При `productCount=0`: button disabled, `aria-disabled=true`, hint видим.
  - При `productCount>0`: button enabled, `aria-disabled=false`, hint hidden.
  - Проверка: layout suite проходит.
  - Выполнено: layout suite проходит, оба состояния submit проверены.

- [x] Покрыть partial `default_prices.php`.
  - Проверить поля `min_price`, `max_price`, `good_price`.
  - Проверить `step="1"` для number inputs.
  - Проверить классы и структуру, необходимые slider.
  - Проверка: layout suite проходит.
  - Выполнено: layout suite проходит, price/sale controls и slider-разметка проверены.

- [x] Покрыть partial `default_sizes.php`.
  - Проверить пары `min_width/max_width`, `min_height/max_height`, `min_depth/max_depth`, `min_weight/max_weight`.
  - Проверить `step="1"`.
  - Проверить сохранение стандартных field names.
  - Проверка: layout suite проходит.
  - Выполнено: layout suite проходит, поля габаритов/веса проверены.

- [x] Покрыть partials `default_warehouses.php` и `default_brands.php`.
  - Проверить hidden `warehouses[] = 0` и `manufacturers[] = 0`.
  - Проверить checkbox names `warehouses[]`, `manufacturers[]`.
  - Проверить active values как `checked`.
  - Проверить недоступные невыбранные значения как `disabled`.
  - Проверить выбранные недоступные значения остаются enabled.
  - Проверка: layout suite проходит.
  - Выполнено: layout suite проходит, hidden inputs, checkbox names, checked/disabled состояния проверены.

- [x] Покрыть field partials.
  - `default_fields_range.php`: `ishop_fields[id][min]`, `ishop_fields[id][max]`, `data-filter-label`, `step="1"`.
  - `default_fields_switch.php`: boolean name `ishop_fields[id]`.
  - `default_fields_list.php`: hidden `ishop_fields[id][] = 0`, checkbox `ishop_fields[id][]`, selected count hooks.
  - Проверка: layout suite проходит.
  - Выполнено: layout suite проходит, range/boolean/list field controls проверены.

- [x] Проверить escaping в layout-тестах.
  - Передать значения с HTML-special chars в titles/labels.
  - Проверить, что в output они экранированы.
  - Проверка: layout suite проходит.
  - Выполнено: layout suite проходит, escaping производителей, складов, характеристик и значений проверен.

## 5. PHP contract tests

- [x] Реализовать `tests/php/Contract/ManifestTest.php`.
  - `mod_ishop_filter.xml` валидный XML.
  - Root extension: `type="module"`, `client="site"`, `method="upgrade"`.
  - Есть `version`.
  - Есть `scriptfile`.
  - Есть namespace/autoload для `Ilange\Module\Ishopfilter`.
  - Есть files: `services`, `src`, `tmpl`, `script.php`.
  - Есть media destination `mod_ishop_filter`.
  - Есть language entries для `en-GB` и `ru-RU`.
  - Проверка: `vendor/bin/phpunit --testsuite contract --filter ManifestTest`.
  - Выполнено: `ManifestTest` проходит; manifest дополнен явным `<filename>script.php</filename>`.

- [x] Реализовать `tests/php/Contract/AssetManifestTest.php`.
  - `media/joomla.asset.json` валидный JSON.
  - Version совпадает с `package.json` и XML.
  - Есть style assets `ishop_filter.front`, `ishop_filter.range`.
  - Есть script assets `ishop_filter.front`, `ishop_filter.range`.
  - Scripts имеют `defer`.
  - `ishop_filter.front` зависит от `core` и `ishop_filter.range`.
  - Все `uri` указывают на существующие файлы.
  - Проверка: `vendor/bin/phpunit --testsuite contract --filter AssetManifestTest`.
  - Выполнено: `AssetManifestTest` проходит.

- [x] Реализовать `tests/php/Contract/LanguageFilesTest.php`.
  - `en-GB` и `ru-RU` `.ini` имеют одинаковые ключи.
  - `en-GB` и `ru-RU` `.sys.ini` имеют одинаковые ключи.
  - Нет пустых ключей.
  - Нет дублей ключей.
  - Все ключи, используемые в PHP/layout, есть в обеих локалях.
  - Проверка: `vendor/bin/phpunit --testsuite contract --filter LanguageFilesTest`.
  - Выполнено: `LanguageFilesTest` проходит.

- [x] Реализовать `tests/php/Contract/StaticContractsTest.php`.
  - Все PHP-файлы содержат Joomla guard.
  - `media/js/front.js` использует `option=com_ishop`.
  - `media/js/front.js` не возвращает основной AJAX на `com_ajax`.
  - `media/js/front.js` отправляет Joomla CSRF token.
  - `media/js/front.js` использует `availableOptions.ishop_fields`.
  - `tmpl/default.php` содержит `data-category-id`.
  - Проверка: `vendor/bin/phpunit --testsuite contract --filter StaticContractsTest`.
  - Выполнено: `StaticContractsTest` проходит.

- [x] Запустить полный PHP suite.
  - Команда: `vendor/bin/phpunit`.
  - Проверка: все PHP-тесты проходят.
  - Выполнено: полный PHP suite проходит, 46 тестов и 239 assertions.

## 6. JS test infrastructure

- [x] Добавить JS devDependencies.
  - `vitest`
  - `happy-dom`
  - `@vitest/coverage-v8`
  - `yauzl`
  - Проверка: `pnpm install` проходит без ошибок.
  - Выполнено: `pnpm install` прошел, зависимости добавлены в `package.json` и lockfile.

- [x] Добавить `vitest.config.mts`.
  - Environment: `happy-dom`.
  - Include: `tests/js/**/*.test.js`.
  - Coverage provider: `v8`.
  - Coverage include: `media/js/front.js`, `media/js/slider.js`.
  - Exclude: minified JS, gzip, `node_modules`, `tests`.
  - Thresholds start: lines/functions/statements 80, branches 70.
  - Проверка: `pnpm vitest --run --config vitest.config.mts --help` или `pnpm test:js` запускает Vitest.
  - Выполнено: `vitest.config.mts` добавлен, `pnpm test:js` запускает Vitest.

- [x] Обновить `package.json` scripts.
  - `test:php`
  - `test:js`
  - `test:js:coverage`
  - `test`
  - `test:all`
  - Проверка: `pnpm test:js` запускается; `pnpm test` запускает PHP и JS.
  - Выполнено: `pnpm test` запускает PHPUnit через `tests/tools/run-phpunit.mjs` и Vitest.

- [x] Создать структуру JS-тестов.
  - `tests/js/front.test.js`
  - `tests/js/slider.test.js`
  - `tests/js/build-config.test.js`
  - `tests/js/packaging.test.js`
  - Проверка: Vitest видит файлы через config.
  - Выполнено: Vitest видит 4 JS test files, временный suite проходит.

## 7. JS testability refactoring

- [x] Обновить `media/js/front.js` без изменения browser behavior.
  - Экспортировать `IshopFilter`.
  - Экспортировать `initIshopFilters(root = document)`.
  - Экспортировать `FRONT_ENTRY_MARKER = 'mod_ishop_filter.front'`.
  - На `DOMContentLoaded` по-прежнему инициализировать формы и сохранять `window.iFilters`.
  - Проверка: `node --check media/js/front.js`, `pnpm build:js`.

- [x] Обновить `media/js/slider.js` без изменения browser behavior.
  - Экспортировать `IshopFilterSliderManager`.
  - Экспортировать `initIshopFilterSliders(root = document)`.
  - Экспортировать `SLIDER_ENTRY_MARKER = 'mod_ishop_filter.slider'`.
  - Сохранить `window.IshopFilterSlider`.
  - На `DOMContentLoaded` по-прежнему выполнять init.
  - Проверка: `node --check media/js/slider.js`, `pnpm build:js`.

- [x] Проверить, что generated assets обновлены сборкой, а не ручным редактированием.
  - Проверка: `git diff -- media/js/front.js media/js/slider.js media/js/front.min.js media/js/slider.min.js`.

## 8. JS tests for `front.js`

- [x] Реализовать smoke-тесты entrypoint.
  - Импортируется без исключений.
  - Не вызывает `Joomla.request`, `fetch`, `XMLHttpRequest` при импорте.
  - Не мутирует DOM при импорте.
  - Экспортирует `IshopFilter`, `initIshopFilters`, `FRONT_ENTRY_MARKER`.
  - Не падает без global `Joomla`.
  - Проверка: `pnpm test:js -- tests/js/front.test.js`.

- [x] Покрыть инициализацию.
  - Без формы возвращает пустой список.
  - Инициализирует одну форму `form[name="ishop_filter"]`.
  - Инициализирует несколько форм независимо.
  - Ищет внешние элементы через `form="..."`.
  - Не трогает посторонние DOM-узлы.
  - Начальный submit text строится из `data-product-count`.
  - Проверка: `pnpm test:js -- tests/js/front.test.js`.

- [x] Покрыть `collectFormData()`.
  - Добавляет только successful controls.
  - Пропускает disabled controls.
  - Пропускает unchecked checkbox/radio.
  - Сохраняет имена `manufacturers[]`, `warehouses[]`, `ishop_fields[id][]`.
  - Сохраняет имена `ishop_fields[id][min]`, `ishop_fields[id][max]`.
  - Добавляет `category_id` из `data-category-id`.
  - Добавляет `Itemid` из `data-item-id`.
  - Fallback берет `id` и `Itemid` из query string.
  - Округляет number input.
  - Не отправляет пустые значения.
  - Не превращает массивы в строку через object `URLSearchParams`.
  - Проверка: `pnpm test:js -- tests/js/front.test.js`.

- [x] Покрыть AJAX preview.
  - Отправляет `POST` на `data-preview-url`.
  - Headers: `Cache-Control`, `Content-Type: application/x-www-form-urlencoded`.
  - Добавляет CSRF token из `Joomla.getOptions('csrf.token')`.
  - Вызывает `abort()` у предыдущего pending request.
  - Показывает loading overlay.
  - Скрывает loading overlay на `onComplete`.
  - Парсит JSON-строку и object response.
  - Не падает на malformed JSON.
  - На success вызывает UI update.
  - На error сохраняет прежнее состояние.
  - Без `category_id` request не отправляется.
  - Без CSRF token request не отправляется.
  - Проверка: `pnpm test:js -- tests/js/front.test.js`.

- [x] Покрыть submit.
  - Disabled submit ничего не делает.
  - Перед redirect делает актуальный preview.
  - Redirect идет на `sefUrl` из preview.
  - При bad response fallback на текущий `sefUrl`.
  - Если `sefUrl` содержит raw filter query, вызывается native submit.
  - `hasRawFilterQuery()` ловит raw keys и `ishop_fields`.
  - Проверка: `pnpm test:js -- tests/js/front.test.js`.

- [x] Покрыть reset.
  - Перед reset отменяет pending preview.
  - Отправляет `POST` на `data-reset-url`.
  - Payload содержит `category_id`, `Itemid`, CSRF.
  - На success redirect на `baseUrl`.
  - На bad response/error redirect на `baseUrl || form.action`.
  - Проверка: `pnpm test:js -- tests/js/front.test.js`.

- [x] Покрыть availability UI.
  - `updateCheckboxes()` отключает только невыбранные недоступные значения.
  - Выбранные недоступные значения остаются enabled.
  - Label получает `.disabled`.
  - Wrapper получает `.filter-option-disabled`.
  - Group получает `.filter-group-empty`, если все опции disabled.
  - Manufacturers и warehouses обрабатываются отдельно.
  - `availableOptions.ishop_fields` обрабатывает `list`, `range`, `boolean`.
  - Отсутствующие field ids отключаются.
  - Проверка: `pnpm test:js -- tests/js/front.test.js`.

- [x] Покрыть range UI в `front.js`.
  - `updateRangePair()` ставит `min`, `max`, `placeholder`, `step=1`.
  - `price_range` обновляет `min_price/max_price`.
  - `sizes` обновляет width/height/depth/weight.
  - Field range обновляет `ishop_fields[id][min/max]`.
  - Вызывается `IshopFilterSlider.refresh()` с ожидаемым scope.
  - Проверка: `pnpm test:js -- tests/js/front.test.js`.

- [x] Покрыть active tags.
  - Checked checkbox/radio создает tag по label.
  - Disabled checked controls не попадают в tags.
  - Range tags форматируются через `data-filter-from-text` и `data-filter-to-text`.
  - Удаление tag снимает checkbox/radio.
  - Удаление range tag очищает inputs.
  - После удаления обновляются selected counts, tags, slider refresh и debounce preview.
  - Проверка: `pnpm test:js -- tests/js/front.test.js`.

- [x] Покрыть selected counts.
  - Считает только `ishop_fields[id][]`.
  - Показывает/скрывает `[data-selected-count]`.
  - Обновляет `data-count`.
  - Не считает manufacturers/warehouses.
  - Проверка: `pnpm test:js -- tests/js/front.test.js`.

## 9. JS tests for `slider.js`

- [x] Реализовать smoke-тесты entrypoint.
  - Импортируется без исключений.
  - Экспортирует `IshopFilterSliderManager`, `initIshopFilterSliders`, `SLIDER_ENTRY_MARKER`.
  - Не требует Joomla.
  - Не мутирует DOM без init.
  - Проверка: `pnpm test:js -- tests/js/slider.test.js`.

- [x] Покрыть initialization.
  - Без `.range` не падает.
  - Неполная range-разметка игнорируется.
  - Полная range-разметка регистрирует slider.
  - Повторный `refresh()` не навешивает дубликаты.
  - `refresh(scope)` работает для самого `.range` и для контейнера.
  - Проверка: `pnpm test:js -- tests/js/slider.test.js`.

- [x] Покрыть расчетные методы.
  - `parseBoundary()` возвращает fallback при мусоре.
  - Отрицательные значения приводятся к `0`.
  - `parseOptionalValue()` округляет дробные значения.
  - `parseOptionalValue()` поддерживает comma decimal.
  - Пустое значение возвращает `null`.
  - `coerceValue()` возвращает fallback.
  - Проверка: `pnpm test:js -- tests/js/slider.test.js`.

- [x] Покрыть DOM update.
  - `line.style.left/right` выставляются в процентах.
  - Points получают `left` в процентах.
  - При равных значениях z-index расставляется корректно.
  - Если `max <= min`, max становится `min + 1`.
  - Проверка: `pnpm test:js -- tests/js/slider.test.js`.

- [x] Покрыть input normalization.
  - Min ниже bounds очищается.
  - Max выше bounds очищается.
  - `min > max` приводит min к max.
  - Debounce input проверяется через fake timers.
  - Проверка: `pnpm test:js -- tests/js/slider.test.js`.

- [x] Покрыть drag behavior.
  - `mousedown + mousemove` меняет min/max input.
  - `touchstart + touchmove` работает и вызывает `preventDefault`.
  - Min не пересекает max с учетом ширины point.
  - Max не пересекает min.
  - Значение на нижней границе очищает min.
  - Значение на верхней границе очищает max.
  - После drag dispatches bubbling `input` event.
  - Проверка: `pnpm test:js -- tests/js/slider.test.js`.

## 10. Build and packaging tests

- [x] Реализовать `tests/js/build-config.test.js`.
  - После `pnpm build` существуют:
    - `media/css/front.css`
    - `media/css/front.min.css`
    - `media/css/front.min.css.gz`
    - `media/css/slider.css`
    - `media/css/slider.min.css`
    - `media/css/slider.min.css.gz`
    - `media/js/front.min.js`
    - `media/js/front.min.js.gz`
    - `media/js/slider.min.js`
    - `media/js/slider.min.js.gz`
  - Gzip files распаковываются.
  - CSS содержит `.mod_ishop_filter`.
  - JS содержит entry markers или exported init names.
  - Vite JS entries включают `front.js` и `slider.js`.
  - Vite SCSS entries включают `front.scss` и `slider.scss`.
  - Проверка: `pnpm build && pnpm test:js -- tests/js/build-config.test.js`.

- [x] Реализовать `tests/js/packaging.test.js`.
  - После `pnpm zip` существует `mod_ishop_filter-{package.version}.zip`.
  - Zip содержит:
    - `mod_ishop_filter.xml`
    - `script.php`
    - `README.md`
    - `language/`
    - `media/`
    - `services/`
    - `src/`
    - `tmpl/`
  - Zip не содержит:
    - `.git/`
    - `.idea/`
    - `node_modules/`
    - `vendor/`
    - `tests/`
    - `coverage/`
    - `build/`
    - вложенные `.zip`
  - Zip содержит generated min/gz assets.
  - Zip содержит source SCSS, если это остается частью installable package.
  - Проверка: `pnpm zip && pnpm test:js -- tests/js/packaging.test.js`.

## 11. Coverage gates

- [x] Включить JS coverage.
  - Команда: `pnpm test:js:coverage`.
  - Минимальные thresholds:
    - lines: 80
    - functions: 80
    - statements: 80
    - branches: 70
  - Проверка: coverage command проходит, отчет создается в `coverage/`.

- [x] Включить PHP coverage как отдельную команду без требования Xdebug для обычного `pnpm test`.
  - Обычный `vendor/bin/phpunit` не должен требовать coverage driver.
  - Coverage запускать отдельной командой при наличии Xdebug/PCOV.
  - Проверка: обычный PHP suite проходит без coverage driver.

- [x] После стабилизации PHP-тестов добавить мягкие PHP coverage targets.
  - Рекомендуемый старт:
    - lines: 70
    - methods: 70
    - branches: не форсировать на первом этапе.
  - Проверка: threshold не блокирует разработку из-за Joomla static API, но показывает регрессии.

## 12. Final verification

- [x] Выполнить syntax checks.
  - `php -l script.php`
  - `php -l services/provider.php`
  - `php -l src/Dispatcher/Dispatcher.php`
  - `php -l src/Helper/IshopfilterHelper.php`
  - `node --check media/js/front.js`
  - `node --check media/js/slider.js`
  - Проверка: все команды проходят.

- [x] Выполнить полную сборку.
  - Команда: `pnpm build`.
  - Проверка: CSS/JS/min/gz artifacts обновлены без ошибок.

- [x] Выполнить полный тестовый набор.
  - Команда: `pnpm test`.
  - Проверка: PHP и JS suites проходят.

- [x] Выполнить coverage JS.
  - Команда: `pnpm test:js:coverage`.
  - Проверка: thresholds выдержаны.

- [x] Выполнить packaging.
  - Команда: `pnpm zip`.
  - Проверка: архив создан с версией из `package.json`.

- [x] Выполнить packaging tests.
  - Команда: `pnpm test:js -- tests/js/packaging.test.js`.
  - Проверка: архив содержит только ожидаемые installable files.

- [x] Проверить синхронизацию версий.
  - `package.json`
  - `mod_ishop_filter.xml`
  - `media/joomla.asset.json`
  - Проверка: версии совпадают.

- [x] Проверить итоговый diff.
  - Команда: `git diff --stat` и `git diff --check`.
  - Проверка: нет whitespace errors, нет случайных unrelated changes.

## 13. Что остается вне автономных тестов

- [x] Не покрывать автономными тестами реальное `Factory::getApplication()` в Joomla CMS.
- [x] Не покрывать автономными тестами реальный `bootComponent('com_ishop')`.
- [x] Не покрывать автономными тестами БД и денормализованные таблицы фильтра.
- [x] Не покрывать автономными тестами Joomla SEF router.
- [x] Не покрывать автономными тестами WebAssetManager в настоящем шаблоне.
- [x] Не покрывать автономными тестами Bootstrap offcanvas в браузере Joomla-страницы.
- [x] Не покрывать автономными тестами интеграцию с пагинацией и сортировкой `com_ishop`.

Эти проверки остаются в ручном smoke-чеклисте на реальном сайте после автономного покрытия.
