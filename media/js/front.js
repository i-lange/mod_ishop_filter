/*
 * @package    mod_ishop_filter
 * @author     Pavel Lange <pavel@ilange.ru>
 * @link       https://github.com/i-lange/mod_ishop_filter
 * @copyright  (C) 2026 Pavel Lange <https://ilange.ru>
 * @license    GNU General Public License version 2 or later
 */

Joomla.request({
    url: '?option=com_ajax&module=ishop_filter&format=json',
    method: 'POST',
    headers: {'Cache-Control': 'no-cache', 'Content-Type': 'application/json'},
    data: JSON.stringify({'fields': input_data}),
    onBefore: function () {
    },
    onSuccess: function (response) {
    },
    onError: function () {
    },
    onComplete: function () {
    }
});