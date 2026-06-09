# AGENTS.md

## Назначение проекта

`mod_ishop_filter` - устанавливаемый модуль Joomla 6 для фильтрации товаров в категориях `com_ishop`. Модуль показывает форму фильтра, отправляет выбранные параметры в JSON endpoints компонента и перенаправляет пользователя на канонический ЧПУ URL результата фильтрации.

Модуль не является самостоятельным сайтом Joomla и работает только внутри Joomla application context вместе с `com_ishop`. Начальное состояние фильтра также берется из `com_ishop`: helper модуля загружает `CategoryModel`, `FilterRules` и `FilterAvailabilityService` компонента.

## Связанные проекты и расширения

Production-ready сайт для проверки: `magazin-gefest-new.local`.

- Windows: `c:\OSPanel\home\magazin-gefest-new.local\`
- WSL: `/mnt/c/OSPanel/home/magazin-gefest-new.local`
- Админка: `https://magazin-gefest-new.local/administrator/`
- Фронтенд: `https://magazin-gefest-new.local`

Связанные расширения в `/mnt/c/OSPanel/home/`:

- `com_ishop` - компонент интернет-магазина и основной runtime dependency фильтра. Для задач по фильтру почти всегда нужно смотреть его вместе с модулем: `CategoryModel` строит объект фильтра, `FilterController` обслуживает `filter.preview`/`filter.reset`, `FilterRules` нормализует входные поля, `FilterAvailabilityService` считает доступность значений и количество товаров.
- `mod_ishop_filter` - текущий модуль фильтра товаров.
- `mod_ishop_cart`, `mod_ishop_compare`, `mod_ishop_zone` - связанные клиентские модули на тех же страницах магазина. После изменений в верстке, JS-событиях, Bootstrap/offcanvas или общих состояниях страницы проверяйте, что они не конфликтуют с фильтром.
- `com_ishopintegro`, `plg_ishopintegrocron` - интеграции и cron, которые могут менять товары, остатки, цены, производителей, характеристики и денормализованные таблицы фильтра.
- `plg_ishopfinder` - поиск по товарам; проверяйте при изменениях SEO URL, формы фильтра, query-параметров и логики доступности товаров.
- `tpl_itheme`, `plg_ithemecsscompiler` - клиентский шаблон и компиляция его стилей. Визуальные изменения фильтра проверяйте в контексте шаблона, особенно offcanvas, Bootstrap 5.3, состояния focus/disabled и адаптивность.

Если меняете контракт формы, AJAX payload, SEO-сегменты фильтра, пагинацию, сортировку, расчет количества товаров или доступность опций, проверяйте модуль синхронно с `com_ishop`. Изолированная правка только в модуле обычно недостаточна.

## Официальный контекст Joomla 6

При изменениях сверяйтесь с официальной документацией Joomla, особенно:

- Getting Started: https://manual.joomla.org/docs/get-started/
- Technical Requirements: https://manual.joomla.org/docs/get-started/technical-requirements/
- Module Development Tutorial: https://manual.joomla.org/docs/building-extensions/modules/module-development-tutorial/
- Web Asset Manager: https://manual.joomla.org/docs/general-concepts/web-asset-manager/

## Стек и окружение

- Joomla CMS 6.x, модуль с `method="upgrade"`.
- PHP 8.3+.
- Node.js `>=24.0.0`, npm `>=11.8.0`, pnpm `>=10.3.0`.
- Bootstrap 5.3 для HTML-разметки клиентской части.
- Фронтенд-ассеты собираются через Vite 8, Sass и Lightning CSS.

## Структура проекта

- `mod_ishop_filter.xml` - манифест Joomla-модуля. Архив собирается как `mod_ishop_filter-{version}.zip`.
- `package.json` - npm metadata, scripts и версия, из которой `pack.mjs` берет имя архива.
- `script.php` - install/update script модуля.
- `services/provider.php` - DI/service provider модуля.
- `src/Dispatcher/Dispatcher.php` - dispatcher, передает данные в layout.
- `src/Helper/IshopfilterHelper.php` - подготовка начального объекта фильтра через `com_ishop`; AJAX preview/reset обслуживаются endpoints компонента `com_ishop`.
- `tmpl/default.php` - основная форма фильтра, `data-category-id`, `data-item-id`, URL endpoints, кнопки submit/reset.
- `tmpl/default_prices.php`, `default_sales.php`, `default_warehouses.php`, `default_brands.php`, `default_sizes.php` - части формы фильтра.
- `tmpl/default_fields.php`, `default_fields_range.php`, `default_fields_switch.php`, `default_fields_list.php` - части характеристик фильтра.
- `media/js/front.js` - исходный JS фильтра, AJAX-preview/reset, submit redirect, active tags и доступность опций.
- `media/js/slider.js` - исходный JS кастомного range slider для цен, размеров, веса и числовых характеристик.
- `media/js/*.min.js`, `*.min.js.gz` - сгенерированные JS-ассеты, не править вручную.
- `media/scss/front.scss`, `media/scss/slider.scss` - исходные стили.
- `media/css/*.css`, `*.min.css`, `*.gz` - сгенерированные CSS-ассеты, не править вручную.
- `media/joomla.asset.json` - декларации Joomla Web Asset Manager для `ishop_filter.front` и `ishop_filter.range`.
- `build.mjs`, `vite.config.js.mts`, `vite.config.css.mts` - сборка CSS/JS через Vite.
- `pack.mjs` - сборка установочного архива `mod_ishop_filter-{package.version}.zip`.
- `language/en-GB/*`, `language/ru-RU/*` - языковые файлы. Новые ключи добавлять в обе локали.

## Интеграция фильтра с com_ishop

Текущая схема работы:

1. `tmpl/default.php` выводит форму `name="ishop_filter"` и кладет в нее `data-category-id`, `data-item-id`, `data-preview-url`, `data-reset-url`, `data-submit-template`.
2. `media/js/front.js` слушает изменения полей, собирает выбранные значения через `FormData` и отправляет POST на `com_ishop` endpoint `filter.preview` с Joomla CSRF token.
3. В payload должны сохраняться стандартные имена полей: `manufacturers[]`, `warehouses[]`, `min_price`, `max_price`, `good_price`, `min_width`, `max_width`, `min_height`, `max_height`, `min_depth`, `max_depth`, `min_weight`, `max_weight`, `ishop_fields[...]`.
4. `filter.preview` возвращает JSON с `productCount`, `availableOptions`, `sefUrl`, `baseUrl`.
5. Модуль обновляет счетчик и текст кнопки `Показать n товаров`, отключает недоступные невыбранные значения и сохраняет `sefUrl`.
6. При submit модуль запрашивает актуальный preview, если нужно, и редиректит на `sefUrl`.
7. Reset отправляет POST на `filter.reset`, компонент очищает session-state фильтра и возвращает `baseUrl`, после чего модуль редиректит на базовый URL категории.

Важные правила:

- Не возвращайте основной AJAX на `com_ajax&module=ishop_filter`: канонический источник preview/reset - `com_ishop`.
- `category_id` для AJAX берите из `data-category-id` формы. На ЧПУ-страницах `?id=` обычно отсутствует.
- Массивы и вложенные поля отправляйте как form-urlencoded форму с исходными именами (`manufacturers[]`, `ishop_fields[12][]`, `ishop_fields[34][min]`), а не как объект, который превращает массивы в строки через `URLSearchParams(object)`.
- Ключ доступных характеристик в ответе компонента - `availableOptions.ishop_fields`.
- Формат `availableOptions`: `manufacturers` и `warehouses` - ID доступных значений; `ishop_fields[fieldId]` содержит `type=range|list|boolean`; для list значения лежат в `values`, для range - `min`/`max`.
- Выбранные значения, которые стали недоступными, оставляйте enabled, чтобы пользователь мог их снять. Отключайте только невыбранные недоступные значения.
- Диапазоны цен, габаритов, веса и числовых характеристик в модуле показываются целыми; inputs должны иметь `step="1"`, а JS должен округлять ввод перед отправкой.
- Reset фильтра должен вести на базовый URL категории без SEO-сегментов фильтра и без пагинации.
- Сортировка и пагинация остаются в логике `com_ishop`; при новом фильтре компонент должен строить `sefUrl` без старого `limitstart`.

## Команды

- `pnpm install` - установить JS-зависимости по `pnpm-lock.yaml`.
- `pnpm build` - полная сборка CSS и JS через `build.mjs`.
- `pnpm build:css` - собрать `media/css/*.css`, `*.min.css`, `*.min.css.gz`.
- `pnpm build:js` - собрать `media/js/*.min.js`, `*.min.js.gz`.
- `pnpm watch:js` - наблюдать JS-сборку.
- `pnpm watch:css` - наблюдать CSS-сборку.
- `pnpm test` - сейчас заглушка `No automated tests yet`.
- `pnpm zip` - `pnpm build` и создание установочного архива `mod_ishop_filter-{version}.zip`.
- Имя архива в `pnpm zip` строится из `package.json` через `pack.mjs`.

Если обычный `node`/`pnpm` не найден в PATH, в этом окружении часто доступен Node.js по `/home/pavel/.nvm/versions/node/v24.14.1/bin/node`; для pnpm может потребоваться PATH с этой директорией первым.

## Правила внесения изменений

- Сначала меняйте исходники: SCSS в `media/scss`, обычные JS entrypoints в `media/js`, PHP в `src` и `tmpl`.
- Не правьте вручную `.min.css`, `.min.js`, `.gz`, если изменение должно генерироваться сборкой.
- После изменения SCSS/JS запускайте соответствующую сборку и включайте сгенерированные assets, если нужен installable package.
- После изменений в проекте синхронно обновляйте версию расширения в трех местах: `package.json`, `<version>` в `mod_ishop_filter.xml` и `version` в `media/joomla.asset.json`. Эти значения не должны расходиться: `package.json` задает имя архива, XML-манифест задает версию для Joomla Installer/updates, asset-декларация задает версию набора Web Asset Manager.
- `vite.config.css.mts` использует `emptyOutDir: true` для `media/css`; не держите там ручные файлы, которые не должны удаляться сборкой.
- В PHP-файлах сохраняйте `defined('_JEXEC') or die;`, namespaced Joomla API (`Factory`, `HTMLHelper`, `Text`, `ModuleHelper`, `Uri`) и существующий стиль проекта.
- Экранируйте вывод: `htmlspecialchars()`, `Text::_()`, явные приведения типов для данных из params/input/model.
- Новые POST/AJAX сценарии должны использовать Joomla CSRF token. В текущем JS token берется через `Joomla.getOptions("csrf.token", "")` и отправляется в тело POST.
- Новые assets регистрируйте в `media/joomla.asset.json` с понятными `name`, `type`, `uri`, `attributes`, `dependencies`.
- Если добавляете новый JS entrypoint, обновите `JS_ENTRY_FILES` в `vite.config.js.mts` и `media/joomla.asset.json`.
- Если добавляете новый SCSS entrypoint, обновите `SCSS_ENTRIES` в `vite.config.css.mts` и `media/joomla.asset.json`.
- Для Bootstrap-разметки используйте Bootstrap 5.3 и `data-bs-*`, не Bootstrap 4.
- Поддерживайте accessibility: `aria-label`, `visually-hidden`, корректные `button`/`a`, видимые состояния focus.
- При добавлении языковых ключей обновляйте обе локали `en-GB` и `ru-RU`.
- Не меняйте контракт полей формы без одновременной проверки `com_ishop/frontend/src/Controller/FilterController.php`, `FilterRules.php` и `CategoryModel.php`.

## Проверка перед сдачей

Минимальный набор:

- `php -l` для измененных PHP-файлов.
- `node --check media/js/front.js`, если менялся JS.
- `pnpm build` или более узко `pnpm build:js`/`pnpm build:css`, если менялись только конкретные ассеты.
- `pnpm test`.
- `pnpm zip`, если нужен installable package.
- Проверить, что версии совпадают в `package.json`, `mod_ishop_filter.xml` и `media/joomla.asset.json`, а имя архива соответствует этой версии.

Функциональная проверка в Joomla 6 на `https://magazin-gefest-new.local`:

- категория без фильтра;
- изменение бренда, склада, скидки, цены, габаритов/веса и характеристик;
- AJAX-preview возвращает новый счетчик, доступность опций, `sefUrl`, `baseUrl`;
- submit ведет на ЧПУ URL с SEO-сегментами фильтра;
- reset ведет на базовый URL категории;
- прямое открытие ЧПУ URL фильтра восстанавливает активные значения;
- пагинация и сортировка работают в прежней логике;
- карточка товара, корзина, checkout, поиск, логин, 403/404 и offline page не сломаны.

## Ограничения и известные состояния

- Автономные PHP/JS-тесты есть; `pnpm test` запускает PHPUnit и Vitest.
- Это только модуль Joomla, поэтому PHP нельзя полноценно запускать вне Joomla application context.
- Основной фильтр зависит от `com_ishop` и его денормализованных таблиц `#__ishop_filter_cat_{categoryId}`.
- Старый AJAX через helper модуля удален; новые доработки делайте через endpoints `com_ishop`.
