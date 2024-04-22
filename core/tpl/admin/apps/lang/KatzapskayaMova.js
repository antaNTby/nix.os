// KatzapskayaMova.js

const KatzapskayaMovaLink = `<a href="#" data-action="show-filter" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFilter">отфильтровано</a>`;
const KatzapskayaMova = {
    "thousands": "&nbsp;",
    "decimal": ".",
    "processing": " Подождите, пожалуйста, идет подготовка данных ",
    "search": "Фильтровать",
    "lengthMenu": "_MENU_",
    "info": "Товар(-ы) с _START_ до _END_ из <span class=\"badge bg-danger\">_TOTAL_</span> товар(-ов)",
    "infoEmpty": "Товар(-ы) с 0 до 0 из 0 товар(-ов)",
    "infoFiltered": "( " + KatzapskayaMovaLink + " из <span class=\"badge bg-secondary\">_MAX_</span> товар(-ов))",
    "infoPostFix": "",
    "loadingRecords": "Загрузка товар(-ов)...",
    "zeroRecords": "Товар(-ы) отсутствуют.",
    "emptyTable": "Нет товаров",
    "paginate": {
        "first": "&lsaquo;&lsaquo;",
        "previous": "&lsaquo;",
        "next": "&rsaquo;",
        "last": "&rsaquo;&rsaquo;"
    },
    "aria": {
        "sortAscending": ": активировать для сортировки столбца по возрастанию",
        "sortDescending": ": активировать для сортировки столбца по убыванию"
    },
    "select": {
        "rows": {
            "_": " Выбрано строк: %d ",
            "0": "Кликните по записи для выбора",
            "1": " Выбрана одна строка "
        }
    },
    "searchBuilder": {
        "add": "Добавить условие",
        "button": {
            "0": "Фильтр <i class='bi bi-list'></i> ",
            "_": " (%d) Фильтр включен "
        },
        "clearAll": "Очистить все фильтры",
        "condition": "Условие:",
        "conditions": {
            "array": {
                "contains": "Содержит",
                "empty": "Пусто",
                "equals": "Совпадает",
                "not": "НЕ",
                "notEmpty": "НЕ Пусто",
                "without": "Без"
            },
            "date": {
                "after": "После",
                "before": "До",
                "between": "Между",
                "empty": "Пусто",
                "equals": "Совпадает",
                "not": "НЕ",
                "notBetween": "Вне диапазона",
                "notEmpty": "НЕ Пусто"
            },
            "number": {
                "between": "Между",
                "notBetween": "Вне диапазона",
                "empty": "Пусто",
                "notEmpty": "НЕ Пусто",
                "equals": "Равно",
                "not": "НЕ",
                "gt": "Больше чем",
                "gte": "Больше или Равно",
                "lt": "Меньше чем",
                "lte": "Меньше или Равно"
            },
            "string": {
                "contains": "Содержит в себе",
                "empty": "Пусто",
                "endsWith": "Заканчивается на",
                "equals": "Совпадает",
                "not": "НЕ",
                "notEmpty": "НЕ Пусто",
                "startsWith": "Начинается с"
            }
        },
        "data": "Данные",
        "deleteTitle": "Убрать правило",
        "leftTitle": "Outdent criteria",
        "logicAnd": "AND",
        "logicOr": "OR",
        "rightTitle": "Indent criteria",
        "title": {
            "0": "<h4 class='text-dark text-uppercase ms-6'>Настраиваемый Фильтр</h4>",
            "_": "<h4 class='text-dark text-uppercase ms-6'>Настраиваемый Фильтр (%d)</h4>"
        },
        "value": "Значение",
        "valueJoiner": "AND"
    }
}

export default KatzapskayaMova