// appAdminCompaniesTable.js
// alert ("appAdminCompaniesTable.js")
import KatzapskayaMova from "../lang/defaultKatzapskayaMova.js";
import * as bsToast from "../Toasts/appToasts.js";
//
//
const app = function() {
    const myToast = bsToast;
    const selector = 'table#CompaniesDataTable';
    const btn_search = document.querySelector('button#btn_search');
    const btn_AddCompany = document.querySelector('button#btn_AddCompany');
    const btn_resetFilterUNP = document.querySelector('button#btn_resetFilterUNP');
    const tdFilterUNP = document.querySelector('td#tdFilterUNP');
    const FilterParams = () => {
        let filter = {};
        filter.searchstring = document.querySelector('input#app_searchstring').value.toString();
        filter.isFullTextSearch = +document.querySelector('input#isFullTextSearch').checked;
        return filter;
    };
    const order_array = [
        [1, 'desc'],
        [3, 'asc']
    ];
    let tableRowNo = 0;
    const searchClearButton = `&nbsp;<span class="btn btn-outline-danger btn-sm" type="button" role="button" onclick="onClearClick('.DT_searchInputEl')"><i class="bi bi-x-lg"></i></span>`;
    KatzapskayaMova.search = '<span class="form-text text-secondary">' + ' Фильтр по названию, УНП и времени_обновления ' + '</span>' + searchClearButton;

    function reloadTable(e, dt, button, config) {
        dt.ajax.reload();
    }

    const table = new DataTable(selector, {
        ajax: {
            url: checkOnUrl(document.location.href) + '&DataTable=getShortCompaniesData' + '&app=app_admincompanies',
            type: 'POST',
            data: function(d) {
                const postData = {
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
            [16 / 4 + ` строк/стр`, 16 / 2 + ` строк/стр`, 16 + ` строк/стр`, 16 * 2 + ` строк/стр`, 16 * 4 + ` строк/стр`, 16 * 8 + ` строк/стр`, 16 * 16 + ` строк/стр`] //
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
                searchable: true,
                orderable: true,
                data: 'id',
            },
            //2
            {
                searchable: true,
                orderable: true,
                data: 'title',
                className: 'text-start fw-lighter',
            },
            //3
            // {
            //     data: 'name',
            //     type: 'html',
            //     className: 'text-start fw-light',
            // },
            //4
            {
                searchable: true,
                orderable: true,
                data: 'unp',
            },
            //5
            {
                searchable: false,
                orderable: false,
                data: 'requisites',
                type: 'html',
                className: 'text-start fw-light',
            },
            // //6
            // {
            //     data: 'adress',
            // },
            //7
            {
                searchable: false,
                orderable: true,
                data: 'read_only',
                className: 'align-middle text-center allow-edit',
                defaultContent: '<i class="bi bi-lock"></i>',
                orderable: true,
                render: function(data, type, row, meta) {
                    let res = '<i class="bi bi-lock-fill"></i>';
                    if (data == 0) {
                        res = '<i class="bi bi-unlock"></i>';
                    }
                    return res;
                },
            },
            //8
            {
                searchable: false,
                orderable: false,
                data: null,
                className: 'align-middle text-center edit-item',
                // defaultContent: '<i class="bi bi-pencil-square"></i>',
                render: function(data, type, row, meta) {
                    let res = '<i class="bi bi-eye-fill" data-value="1"></i>';
                    if (row.read_only == 0) {
                        res = '<i class="bi bi-pencil-square" data-value="0"></i>';
                    }
                    return res;
                },
                orderable: false,
            },
            //9
            {
                searchable: false,
                orderable: false,
                data: null,
                className: 'align-middle text-center filter-unp',
                defaultContent: '<i class="bi bi-funnel"></i>',
                orderable: false
            },
            //10
            {
                searchable: false,
                orderable: false,
                data: null,
                className: 'align-middle text-center link-to-Order',
                defaultContent: '<i class="bi bi-cart-plus">',
                render: function(data, type, row, meta) {

                    let toOrderID = +document.getElementById('toOrderID').value;
                    let zoom = '';
                    let res = '<i class="bi bi-cart-plus' + zoom + '" data-value="' + toOrderID + '"></i>';
                    if (toOrderID > 0) {
                        zoom = ' h3 text-success';
                        res = '<i class="bi bi-cart-check' + zoom + '" data-value="' + toOrderID + '"></i>';
                    }
                    return res;
                },
                orderable: false
            },
            //11
            {
                searchable: true,
                orderable: true,
                data: 'creation_time',
                className: 'align-middle text-end',
                orderable: true
            },
            //12
            {
                searchable: true,
                orderable: true,
                data: 'update_time',
                className: 'align-middle text-end',
                orderable: true
            },
            //13
            {
                searchable: false,
                orderable: false,
                data: null,
                className: 'align-middle text-center remove-item',
                defaultContent: '<i class="bi bi-trash3-fill text-danger"></i>',
                orderable: false
            },
        ]
    });
    // Окно поиска
    document.querySelector('#app_searchstring').addEventListener('keyup', function(event) {
        let elementID = event.target.id
        let x = event.key
        if (x == 'Enter') {
            reloadTable(event, table);
        } else
        if (x == 'Escape') {
            return event.target.value = '';
        }
        return false;
    });
    // события DataTables
    // события DataTables
    table.on('dblclick', 'tbody td', function() {
        // let data = table.cell(this).render('display');
        // console.log(data);
        let data = table.cell(this).render('filter');
        console.log(data);
    });
    table.on('init.dt', function(e, settings, json) {
        tableRowNo = 0 // нумеруем строки
    });
    table.on('preXhr.dt', function(e, settings, data) {
        // $('#offcanvasFilter').block(BlockOptions)
        tableRowNo = 0 // нумеруем строки
    });
    // события DataTables
    // события DataTables
    // Edit record
    table.on('click', 'tbody tr td.edit-item', function(event) {
        event.preventDefault();
        let result = '';
        // let toOrderID = JSgetParameterByName('toOrderID');
        let toOrderID = +document.getElementById('toOrderID').value;

        let dtData = table.row(this.parentNode).data();
        if (dtData.read_only === "1") {
            result = 'admin.php' + '?dpt=custord&sub=companies' + '&company_detailed=' + +dtData.unp + '&companyID=' + +dtData.id + '&read_only=1';
        } else {
            result = 'admin.php' + '?dpt=custord&sub=companies' + '&company_detailed=' + +dtData.unp + '&companyID=' + +dtData.id;
        }
        if (+toOrderID > 0) {
            result = result + '&toOrderID=' + +toOrderID;
        }

        document.location.href = result;
        return true;
    });
    // Filter UNP
    table.on('click', 'tbody tr td.filter-unp', function(event) {
        event.preventDefault();
        let dtData = table.row(this.parentNode).data();
        // Apply a search to the second table for the demo
        table.search(dtData.unp).draw();
        btn_resetFilterUNP.classList.remove('visually-hidden');
        // table.draw();
    });
    // Link to orer
    table.on('click', 'tbody tr td.link-to-Order', function(event) {
        event.preventDefault();
        let dtData = table.row(this.parentNode).data();
        let toOrderID = +document.getElementById('toOrderID').value;
        let toInvoiceID = +document.getElementById('toInvoiceID').value;
        if (!window.confirm('Выбрать реквизиты компании \r\n' + htmlspecialchars_decode(dtData.name) + ' id:' + dtData.id + ' для заказа #' + toOrderID + ' ?')) {
            return false;
        }
        const Data = {
            'operation': 'LinkCompany',
            'companyID': dtData.id,
            'toOrderID': toOrderID,
            'toInvoiceID': toInvoiceID,
        };
        // console.log(Data)
        return appAjax(Data);
    });
    // Link to orer
    table.on('click', 'tbody tr td.allow-edit', async function(event) {
        event.preventDefault();
        let dtData = table.row(this.parentNode).data();

        let oldValue = +dtData.read_only;
        let newValue = !oldValue;
        let companyID = dtData.id;

        const Data = {
            'operation': 'SetReadonly',
            'companyID': companyID,
            'newValue': newValue,
        };

        let result = await appAjax(Data);

        if (result === 'SUCCESS') {
            table.ajax.reload(function(json) {
                console.log(result);
            }, false);
        }



    });

    // function setReadOnlyTo(setTo) {
    //     const chkSelector = 'input#checkAsDeadMan_' + thisCompanyID.value.toString();
    //     const chkDeadMan = document.querySelector(chkSelector);
    //     const chkDeadManLabel = document.querySelector(chkSelector + ' ~ label');
    //     // console.log(chkDeadManLabel, chkSelector + ' ~ label')
    //     // ..обработка
    //     if (setTo == 1) {
    //         if (chkDeadMan) {

    //             chkDeadMan.setAttribute('disabled', 'disabled');
    //             chkDeadManLabel.innerHTML = ' Защита от записи';
    //         }

    //         readonlySwitchEl.setAttribute('checked', 'checked');
    //         btn_clearAll.classList.add('disabled', 'visually-hidden');
    //         pm_fieldset.setAttribute('disabled', setTo);
    //         data_fieldset.setAttribute('disabled', setTo);
    //         readonlyLabelEl.innerHTML = '<i class="bi bi-lock-fill text-dark"></i> Защита от записи';
    //         formDataToolbar.classList.add('disabled', 'visually-hidden');
    //     }
    //     //
    //     if (setTo == 0) {
    //         if (chkDeadMan) {
    //             chkDeadMan.removeAttribute('disabled');
    //             chkDeadManLabel.innerHTML = ' Пометить на слияние'
    //         }
    //         readonlySwitchEl.removeAttribute('checked');
    //         btn_clearAll.classList.remove('disabled', 'visually-hidden');
    //         btn_save.classList.remove('disabled', 'visually-hidden');
    //         pm_fieldset.removeAttribute('disabled');
    //         data_fieldset.removeAttribute('disabled');
    //         readonlyLabelEl.innerHTML = '<i class="bi bi-unlock-fill text-muted"></i> Не защищено';
    //         formDataToolbar.classList.remove('disabled', 'visually-hidden');
    //     }
    // }

    btn_resetFilterUNP.addEventListener("click", function() {
        table.search('').draw();
        btn_resetFilterUNP.classList.add('visually-hidden');
    });
    btn_search.addEventListener("click", function() {
        document.querySelector('div.dataTables_filter input[type="search"').value = '';

    });
    btn_AddCompany.addEventListener("click", (event) => {
        event.preventDefault();
        if (!window.confirm('Уверены, хотите добавить реквизиты новой компании?')) {
            return false;
        }
        const Data = {
            'operation': 'AddCompany',
            'companyID': -1,
            'formData': [],
        };
        return appAjax(Data);
    });
    const DT_searchInputEl = document.querySelector('div.dataTables_filter input[type="search"]');
    // стилизуем DT
    $('div.dataTables_filter label').addClass('d-flex justify-content-md-between align-items-center text-primary');
    // $( 'div.dataTables_filter input[type="search"]' ).addClass( 'ms-3 w-100 border-primary' ).removeClass( 'form-control-sm' ).attr( 'placeholder', ' Нажмите Enter для подтверждения запроса' );
    DT_searchInputEl.classList.add('w-100');
    DT_searchInputEl.classList.add('ms-1');
    DT_searchInputEl.classList.add('border-secondary');
    DT_searchInputEl.classList.add('DT_searchInputEl');
    // DT_searchInputEl.classList.remove( 'form-control-sm' );
    DT_searchInputEl.setAttribute('placeholder', ' Нажмите Enter для подтверждения запроса');
    async function appAjax(params) {
        // successEl.classList.add('d-none');
        // errorEl.classList.add('d-none');
        let operation = params.operation;
        //
        let url = checkOnUrl(document.location.href);
        const Data = { ...params
        };
        const response = await fetch(url + '&operation=' + operation + '&app=app_admincompanies', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json;charset=utf-8',
            },
            body: JSON.stringify({
                Data
            }),
        });
        //
        const result = await response.text();
        //
        // На основе ответа от сервера показываем сообщения об Успехе или Ошибке
        if (result === 'SUCCESS') {
            // successEl.classList.remove('d-none');
            myToast.showSuccess();
        } else if (result === 'FAILED') {
            // errorEl.classList.remove('d-none');
            myToast.showError();
        } else {
            document.location.href = result;
        }
        return result;
    }
    return table;
};
window.onload = app();
export default app