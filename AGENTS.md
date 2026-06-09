# AGENTS.md

## Проект

`mod_ishop_filter` - устанавливаемый site-модуль Joomla 6 для фильтрации товаров в категориях `com_ishop`. Он выводит форму фильтра, отправляет выбранные поля в JSON endpoints компонента и редиректит на канонический ЧПУ URL результата.

Модуль не работает как самостоятельный сайт: ему нужен Joomla application context и `com_ishop`. Начальное состояние фильтра helper получает из `com_ishop` через `CategoryModel`, `FilterRules` и `FilterAvailabilityService`.

## Окружение и связанные проекты

- Проверочный сайт: `https://magazin-gefest-new.local`, админка: `https://magazin-gefest-new.local/administrator/`.
- Пути сайта в Windows `C:\OSPanel\home\magazin-gefest-new.local\`.
- Соседние расширения в `C:\OSPanel\home\`: `com_ishop`, `mod_ishop_cart`, `mod_ishop_compare`, `mod_ishop_zone`, `com_ishopintegro`, `plg_ishopintegrocron`, `plg_ishopfinder`, `tpl_itheme`, `plg_ithemecsscompiler`.

Для изменений контракта формы, AJAX payload, SEO-сегментов, пагинации, сортировки, счетчиков или доступности опций проверяйте модуль вместе с `com_ishop`: `frontend/src/Controller/FilterController.php`, `FilterRules.php`, `CategoryModel.php`, `FilterAvailabilityService.php`.

## Документация и стек

- Для вопросов по библиотекам, фреймворкам, SDK/API/CLI и cloud-сервисам сначала используйте Context7 MCP.
- Для Joomla сверяйтесь с официальным manual: module development и Web Asset Manager на `https://manual.joomla.org/`.
- Стек: Joomla CMS 6.x, PHP 8.3+, Node.js `>=24.0.0`, npm `>=11.8.0`, pnpm `>=10.3.0`, Bootstrap 5.3, Vite 8, Sass, Lightning CSS.

## Важные файлы

- `mod_ishop_filter.xml` - manifest Joomla, версия installer/update.
- `package.json` - npm scripts и версия архива `mod_ishop_filter-{version}.zip`.
- `media/joomla.asset.json` - версии и assets Web Asset Manager.
- `script.php`, `services/provider.php`, `src/Dispatcher/Dispatcher.php`, `src/Helper/IshopfilterHelper.php` - install, DI, dispatcher, подготовка данных.
- `tmpl/default*.php` - форма и части фильтра.
- `media/js/front.js`, `media/js/slider.js` - исходный JS; `media/scss/*.scss` - исходные стили.
- `media/js/*.min.js`, `media/css/*.css`, `*.min.*`, `*.gz` - generated assets, вручную не править.
- `tests/php`, `tests/js` - автономные PHPUnit/Vitest тесты без полного Joomla runtime.
- `build.mjs`, `vite.config.*.mts`, `pack.mjs` - сборка и упаковка.

## Контракт фильтра

1. `tmpl/default.php` выводит `form[name="ishop_filter"]` с `data-category-id`, `data-item-id`, `data-preview-url`, `data-reset-url`, `data-submit-template`.
2. `media/js/front.js` собирает `FormData` и POST-ит на `com_ishop` endpoint `filter.preview` с Joomla CSRF token из `Joomla.getOptions("csrf.token", "")`.
3. Имена полей payload сохраняйте как есть: `manufacturers[]`, `warehouses[]`, `min_price`, `max_price`, `good_price`, `min_width`, `max_width`, `min_height`, `max_height`, `min_depth`, `max_depth`, `min_weight`, `max_weight`, `ishop_fields[...]`.
4. `filter.preview` возвращает `productCount`, `availableOptions`, `sefUrl`, `baseUrl`; модуль обновляет счетчик/кнопку, доступность значений и сохраняет `sefUrl`.
5. Submit перед редиректом обновляет preview при необходимости и ведет на `sefUrl`.
6. Reset POST-ит на `filter.reset`; компонент очищает session-state и возвращает `baseUrl`, модуль редиректит на базовый URL категории.

Правила:

- Не возвращайте основной AJAX на `com_ajax&module=ishop_filter`; источник preview/reset - `com_ishop`.
- `category_id` берите из `data-category-id`, потому что на ЧПУ страницах `?id=` обычно нет.
- Отправляйте форму с исходными именами полей через `FormData`/form-urlencoded; не превращайте массивы и вложенные поля в объект для `URLSearchParams(object)`.
- Доступные характеристики лежат в `availableOptions.ishop_fields`; для `list` значения в `values`, для `range` - `min`/`max`, типы: `range|list|boolean`.
- Недоступные выбранные значения оставляйте enabled, чтобы пользователь мог их снять; отключайте только недоступные невыбранные.
- Диапазоны цен, габаритов, веса и числовых характеристик целочисленные: `step="1"`, JS округляет ввод перед отправкой.
- Reset ведет на базовый URL категории без SEO-сегментов фильтра и пагинации.
- Сортировка и пагинация остаются в `com_ishop`; новый фильтр не должен сохранять старый `limitstart`.

## Команды

- `pnpm install` - установить JS-зависимости.
- `pnpm build`, `pnpm build:css`, `pnpm build:js` - сборка assets.
- `pnpm watch:css`, `pnpm watch:js` - watch-сборка.
- `pnpm test` - PHPUnit + Vitest; отдельно `pnpm test:php`, `pnpm test:js`, `pnpm test:coverage`, `pnpm test:all`.
- `pnpm zip` - сборка и архив `mod_ishop_filter-{package.version}.zip`.

Если `node`/`pnpm` не найдены, часто помогает PATH с `/home/pavel/.nvm/versions/node/v24.14.1/bin` первым. PHPUnit runner сам пробует `PHP_BIN`, `C:\OSPanel\modules\PHP-8.3\PHP\php.exe`, затем `php`.

## Правила изменений

- Сначала меняйте исходники: PHP в `src`, `services`, `tmpl`, `script.php`; JS в `media/js/*.js`; SCSS в `media/scss`.
- После JS/SCSS правок запускайте соответствующую сборку и включайте generated assets, если нужен устанавливаемый архив.
- Версии синхронизируйте в трех местах: `package.json`, `<version>` в `mod_ishop_filter.xml`, `version` в `media/joomla.asset.json`.
- `vite.config.css.mts` очищает `media/css` (`emptyOutDir: true`), не храните там ручные файлы.
- В PHP сохраняйте `defined('_JEXEC') or die;`, namespaced Joomla API и существующий стиль; экранируйте вывод через `htmlspecialchars()`, `Text::_()`, явные приведения типов.
- Новые POST/AJAX сценарии должны отправлять Joomla CSRF token.
- Новые assets регистрируйте в `media/joomla.asset.json`; новые entrypoints также добавляйте в `JS_ENTRY_FILES` или `SCSS_ENTRIES`.
- Bootstrap-разметка: версия 5.3 и `data-bs-*`; поддерживайте `aria-label`, `visually-hidden`, корректные `button`/`a`, focus-состояния.
- Новые языковые ключи добавляйте в обе локали: `language/en-GB` и `language/ru-RU`.

## Проверка перед сдачей

Минимум:

- `php -l` для измененных PHP-файлов.
- `node --check media/js/front.js` и/или `node --check media/js/slider.js`, если менялся JS.
- `pnpm build:js`/`pnpm build:css` или `pnpm build`, если менялись assets.
- `pnpm test`.
- `pnpm zip`, если нужен installable package.
- Проверить совпадение версий в `package.json`, `mod_ishop_filter.xml`, `media/joomla.asset.json`.

Функционально на `https://magazin-gefest-new.local`: категория без фильтра; бренды, склад, скидки, цены, габариты/вес, характеристики; preview возвращает счетчик, доступность, `sefUrl`, `baseUrl`; submit ведет на ЧПУ URL; reset на базовую категорию; прямой ЧПУ URL восстанавливает значения; пагинация, сортировка, карточка товара, корзина, checkout, поиск, login, 403/404 и offline page не ломаются.

## Ограничения

- Это только Joomla-модуль, полноценный PHP runtime вне Joomla недоступен.
- Фильтр зависит от `com_ishop` и денормализованных таблиц `#__ishop_filter_cat_{categoryId}`.
- Старый AJAX через helper модуля удален; новые доработки делайте через endpoints `com_ishop`.
