// appAdminProductDataTable.js
import appCurrency from "../Currency/appCurrency.js";
import KatzapskayaMova from "../lang/KatzapskayaMova.js";
import * as bsToast from "../Toasts/appToasts.js";

const app = function() {

    const myToast =bsToast;

    const selector = 'table#AdminProductDataTable';
    const initialCurrencyValue = getCurrencyValue();
    const buttonRefresh = document.querySelector('button#refreshDT');


    const spanISO3 = document.querySelectorAll('span.BYN');
    const FilterParams = () => {
        let filter = {};
        filter.searchstring = document.querySelector('input#app_searchstring').value.toString();
        return filter;
    };
    const order_array = [
        [1, 'asc'],
        [2, 'asc']
    ];
    const thisOrderID = document.querySelector('input#thisOrderID').value;

    let tableRowNo = 0;

    const isReloaded = sessionStorage.getItem('isReloaded');

    function getCurrencyValue() {
        let hasVAT = document.querySelector('#hasVATIncluded:checked');
        let res = 1;
        if (hasVAT) {
            res = parseFloat(document.querySelector('#resultCurrencyValueWithVAT').value);
        } else {
            res = parseFloat(document.querySelector('#resultCurrencyValue').value);
        }
        return res;
    }

    function getStatusID() {
        let selControl = document.getElementById('status');
        let sel = selControl.selectedIndex;
        let options = selControl.options;
        let statusID = options[sel].value;
        let statusName = options[sel].text;
        return statusID;
    }


    function reloadTable(e, dt, button, config) {
        dt.ajax.reload();
    }

    function restoreSession() {
        let sessionSearchstring = sessionStorage.getItem('searchstring');
        let sessionTableID = sessionStorage.getItem('tableID');
        document.querySelector('#app_searchstring').value = sessionSearchstring;
        setSelectValue('selectItemSource', sessionTableID);
        let isShown = sessionStorage.getItem('isShown');
        const myCollapsible = document.querySelector('div.collapse.datatable-collapse');
        const bsCollapse = new bootstrap.Collapse('div.collapse.datatable-collapse', {
            toggle: false
        });
        if (isShown === 'true') {
            bsCollapse.show();
        } else {
            bsCollapse.hide();
        }
        sessionStorage.removeItem("isReloaded");
        sessionStorage.removeItem("isShown");
    }



    if (isReloaded) {
        restoreSession();
    }
    if (document.querySelector('#app_searchstring').value == '') {
        sessionStorage.removeItem('searchstring');
    }
    //
    sessionStorage.removeItem('tableID');
    //
    //
    //
    const table = new DataTable(selector, {
        ajax: {
            url: checkOnUrl(document.location.href) + '&operation=getDataToInsertItemToOrderedCart' + '&app=app_admin_products',
            type: 'POST',
            data: function(d) {
                const postData = {
                    // statusID: 2,
                    orderID: document.querySelector('input#thisOrderID').value,
                    currencyValue: getCurrencyValue(),
                    tableID: document.querySelector('select#selectItemSource').value,
                    params: FilterParams(),
                }
                d.DATA = {
                    //
                    ...postData
                    //
                };
            },
        },
        serverSide: true,
        processing: true,
        searchDelay: 300,
        search: {
            return: true
        },
        ordering: true,
        order: order_array,
        background: true,
        language: KatzapskayaMova,
        paging: true,
        pageLength: 8,
        lengthMenu: [
            [16 / 4, 16 / 2, 16, 16 * 2, 16 * 4, 16 * 8, 16 * 16], //
            [16 / 4 + ` тов/стр`, 16 / 2 + ` тов/стр`, 16 + ` тов/стр`, 16 * 2 + ` тов/стр`, 16 * 4 + ` тов/стр`, 16 * 8 + ` тов/стр`, 16 * 16 + ` тов/стр`] //
        ],
        columnDefs: [
            //
            {
                orderable: false,
                targets: [0, -1]
            },
            //
            {
                searchable: false,
                targets: [0, 2, 3, 5, 6, 8]
            }, {
                searchable: true,
                targets: [1, 4, 7]
            },
        ],
        columns: [
            //0
            {
                searchable: false,
                orderable: false,
                data: null,
                name: 'tableRowNo',
                title: '#',
                type: 'html',
                visible: true,
                cellType: 'th',
                className: 'text-end text-nowrap text-secondary text-small align-middle',
                // className: 'text-info text-end text-nowrap opacity-50',
                render: function(data, type, row) {
                    // нумеруем строки
                    tableRowNo++;
                    let zerofilled = zeroPad(tableRowNo, 3);
                    return zerofilled;
                },
            },
            //1
            {
                data: 'name'
            },
            //2
            {
                data: 'price',
                className: 'text-end fw-lighter'
            },
            //3
            {
                data: 'priceOUT',
                type: 'html',
                className: 'text-end fw-light',
            },
            //4
            {
                data: 'category'
            },
            //5
            {
                data: 'id'
            },
            //6
            {
                data: 'from'
            },
            //7
            {
                data: 'product_code'
            },
            //8
            {
                data: null,
                className: 'dt-center add-item',
                defaultContent: '<i class="bi bi-cart-plus h4">',
                orderable: false
            },
        ],
    });
    //
    // СЛУШАЕМ внешнее событие КУРС ВАЛЮТЫ ИЗМЕНИЛСЯ!!!
    document.addEventListener('eventCurrencyValueCnanged', (event) => {
        table.clear();
        table.ajax.reload();
        table.draw();
    });
    document.querySelector('#selectItemSource').addEventListener('change', (event) => {
        table.clear();
        table.ajax.reload();
        table.draw();
    });
    // события DataTables
    table.on('dblclick', 'tbody td', function() {
        let data = table.cell(this).render('display');
    });

    // Edit record
    table.on('click', 'tbody tr td.add-item', function(event) {
        event.preventDefault();
        let dtData = table.row(this.parentNode).data();
        let operation = "AddNewItem";
        let url = checkOnUrl(document.location.href);
        let statusID = getStatusID();
        // ============================================
        async function fetchData() {
            let tableID = document.querySelector('select#selectItemSource').value;
            let Data = {
                "orderID": thisOrderID,
                "statusID": +statusID,
                "name": dtData.name,
                "Quantity": 1,
                "Price": dtData.price,
                "tableID": tableID,
            };
            if (tableID == 1) {
                Data["productID"] = dtData.id;
            } else {
                Data["itemID"] = dtData.id;
            }
            const response = await fetch(url + '&operation=' + operation + '&app=app_ordercontent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json;charset=utf-8',
                },
                body: JSON.stringify({
                    Data,
                }),
            });
            const result = await response.text();
            // На основе ответа от сервера показываем сообщения об Успехе или Ошибке
            if (result === 'SUCCESS') {
                // document.getElementById('success').classList.remove('d-none');
                myToast.showSuccess();
            } else if (result === 'FAILED') {
                // document.getElementById('error').classList.remove('d-none');
                myToast.showError();
            } else {
                sessionStorage.setItem('isReloaded', true);
                sessionStorage.setItem('searchstring', document.querySelector('input#app_searchstring').value.toString());
                sessionStorage.setItem('tableID', document.querySelector('select#selectItemSource').value.toString());
                sessionStorage.setItem('isShown', document.querySelector('div#dataTableContainer').classList.contains('show').toString());
                // sessionStorage.setItem('pageNum', table.page());
                // sessionStorage.setItem('pageLen', table.page.len());
                document.location.href = result;
            }
        }
        if (statusID == 2 || statusID == 3) {
            fetchData(); /*отправляем запрос в app_ordercontent*/
        } else {
            alert('Измениете статус заказа на Активный');
        }
    });
    //
    table.on('init.dt', function(e, settings, json) {
        tableRowNo = 0 // нумеруем строки
    });
    table.on('preXhr.dt', function(e, settings, data) {
        // $('#offcanvasFilter').block(BlockOptions)
        tableRowNo = 0 // нумеруем строки
    });
    // КНОПКА ОБНОВЛЕНИЯ ТАБЛИЦЫ
    buttonRefresh.addEventListener('click', function(event) {
        const myCollapsible = document.querySelector('div.collapse.datatable-collapse');
        const bsCollapse = new bootstrap.Collapse('div.collapse.datatable-collapse', {
            toggle: false
        });
        bsCollapse.show();
        reloadTable(event, table);
    });
    // Окно поиска
    document.querySelector('#app_searchstring').addEventListener('keyup', function(event) {
        let elementID = event.target.id
        let x = event.key
        if (x == 'Enter') {
            const myCollapsible = document.querySelector('div.collapse.datatable-collapse');
            const bsCollapse = new bootstrap.Collapse('div.collapse.datatable-collapse', {
                toggle: false
            });
            bsCollapse.show();
            reloadTable(event, table);
        } else
        if (x == 'Escape') {
            return event.target.value = '';
        }
        return false;
    });

    // стилизуем DT
    $('div.dataTables_filter label').addClass('d-flex justify-content-md-between align-items-center text-primary')
    $('div.dataTables_filter input[type="search"]').addClass('ms-3 w-100 border-primary').removeClass('form-control-sm').attr('placeholder', ' Нажмите Enter для подтверждения запроса')

    return table;
}

// window.onload = app();

export default app