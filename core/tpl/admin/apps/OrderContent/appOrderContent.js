import appCurrency from "../Currency/appCurrency.js";
import * as Model from "./modelOrderContent.js";
import main from "./viewOrderContent.js";
import {
    rowsCount,
    Inputs,
    Cleaves,
    initCleaves,
    initTable,
    initTableResults,
    // initDiscountValue,
    updateResultView,
    updateRowView,
    disableInputs,
    enableInputs,
    deActivateSaveButtons,
    appOperationButtons
} from "./viewOrderContent.js";
//
import * as Dialog from "./viewDialog.js";
import productsTable from "../AdminProductDataTable/appAdminProductDataTable.js";
import invoiceSimple from "../InvoiceSimple/appInvoiceSimple.js";
import * as bsToast from "../Toasts/appToasts.js";
//
//
function _updateLog(d) {
    let tbody = document.getElementById('tableComments').getElementsByTagName("tbody")[0];
    let row = document.createElement('tr');
    let td1 = document.createElement('th');
    let now = new Date(); // 19.12.2019
    let nowTime = formatDateTime(now);
    td1.appendChild(document.createTextNode(nowTime));
    let td2 = document.createElement('td');
    let strong = document.createElement('strong');
    strong.appendChild(document.createTextNode(d['comment']));
    td2.appendChild(strong);
    let td3 = document.createElement('td');
    td3.appendChild(document.createTextNode(d['statusName']));
    row.appendChild(td1);
    row.appendChild(td2);
    row.appendChild(td3);
    // Получаем ссылку на первый дочерний элемент
    let theFirstChild = tbody.firstChild;
    // Вставляем новый элемент перед первым дочерним элементом
    tbody.insertBefore(row, theFirstChild);
    // tbody.appendChild(row);  //в конец
}
//
//
const myToast = bsToast;

//
//
window.onload = function() {
    // инициализируем Валюту
    let aCurrency = appCurrency("OrderContent");
    // инициализируем элементы управления
    initCleaves();
    let initialTable = initTable();
    let initialResults = initTableResults();
    let names = Dialog.init();

    let productsDT = productsTable();
    // инициируем модель начальными данными
    let dataToSetIn = {
        "orderID": initialTable.orderID,
        "rowsCount": rowsCount,
        "rows": initialTable.rows,
        "res": initialResults.rows,
        "CID": +aCurrency.CID,
        "currencyValue": +aCurrency.currencyValue,
        "hasVATIncluded": +aCurrency.hasVATIncluded,
        "VAT_Rate": +aCurrency.VAT_Rate,
        "discountVal": +initialTable.discountVal,
        "names": names
    };
    Model.setData(dataToSetIn);
    Model.setRowsResults();
    //
    // Init main
    const getData = Model.getData;
    main(getData);
    //
    //  слушаем событие СТРОКА изменилась!!!
    document.addEventListener('eventUpdateRowValue', (event) => {
        Model.setRowValue(event.detail);
        const data = Model.getData();
        const res = Model.getRes();
        // Update results block
        Model.setData({
            onUpdate: "singleRowUpdate"
        });
        const totals = Model.getResults();
        updateRowView(+event.detail.index, data.rows[event.detail.index], res[event.detail.index]);
        updateResultView(totals);
    });
    //
    //  слушаем событие КонтентЗаказа Изменился!!!
    document.addEventListener('updateOrderContentForm', (event) => {
        Model.setData(event.detail);
        const data = Model.getData();
        const res = Model.getRes();
        const totals = Model.getResults();
        for (let index = 0; index < data.rowsCount; index++) {
            updateRowView(+index, data.rows[index], res[index]);
        }
        updateResultView(totals);
    });
    //
    //
    // СЛУШАЕМ внешнее событие КУРС ВАЛЮТЫ ИЗМЕНИЛСЯ!!!
    document.addEventListener('eventCurrencyValueCnanged', (event) => {
        let newData = {
            "onUpdate": "appCurrency",
            "CID": +event.detail.CID,
            "currencyValue": +event.detail.currencyValue,
            "hasVATIncluded": +event.detail.hasVATIncluded,
            "VAT_Rate": +event.detail.VAT_Rate,
        };
        Model.setData(newData);
        const data = Model.getData();
        const res = Model.getRes();
        for (let index = 0; index < data.rowsCount; index++) {
            updateRowView(+index, data.rows[index], res[index]);
        }
        const totals = Model.getResults();
        updateResultView(totals);
    });
    //
    //
    const orderForm = document.querySelector('#OrderContentTable');
    const currencyForm = document.querySelector('#CurrencyTable');

    //
    //
    appOperationButtons.forEach(function(el) {
        el.addEventListener('click', function(event) {
            event.preventDefault();
            // ============================================
            async function fetchData() {

                // document.getElementById('success').classList.add('d-none');
                // document.getElementById('error').classList.add('d-none');

                let url = checkOnUrl(document.location.href);
                let operation = el.dataset.operation;
                if (operation == "DeleteCartItem" || operation == "KillThemAll") {
                    let confirmation = window.confirm("Уверены?");
                    if (!confirmation) {
                        enableInputs(orderForm, currencyForm);
                        // enableSaveButtons();
                        return false;
                    }
                }
                let selControl = document.getElementById('status');
                // индекс выбранного option
                let sel = selControl.selectedIndex;
                let options = selControl.options;
                let statusID = options[sel].value;
                let statusName = options[sel].text;
                let Data = {
                    "statusID": +statusID
                };
                const data = Model.getData();
                const results = Model.getResults();
                const currency = appCurrency();
                const FullData = { ...data,
                    ...results.Total,
                    ...currency
                };
                Data = { ...Data,
                    ...FullData
                };
                if (operation == "DeleteCartItem") {
                    let _orderID = el.dataset.orderid;
                    let _itemID = el.dataset.itemid;
                    let _name = document.querySelector('span.content[data-itemid="' + _itemID + '"]').innerText;
                    let _Quantity = document.querySelector('input[data-control="Quantity"][data-itemid="' + _itemID + '"]').value;
                    let _Price = document.querySelector('input[data-control="Price"][data-itemid="' + _itemID + '"]').value;
                    Data = {
                        "orderID": _orderID,
                        "statusID": +statusID,
                        "itemID": _itemID,
                        "name": _name,
                        "Quantity": _Quantity,
                        "Price": _Price,
                    };
                }
                if (operation == "KillThemAll") {
                    let _orderID = el.dataset.orderid;
                    Data = {
                        "orderID": _orderID,
                        "statusID": +statusID,
                    };
                }
                if (operation == "ChangeItemProperties") {
                    let _orderID = el.dataset.orderid;
                    const modal_data = Dialog.getData();
                    Data = {
                        "statusID": +statusID,
                        ...modal_data
                    };
                }
                if (operation == "AddComment") {
                    let _orderID = el.dataset.orderid;
                    let comment_data = document.querySelector('textarea#status_comment_n').value;
                    let adminName = document.querySelector('div#navbarNavAltMarkup span#adminName').dataset.adminname;
                    Data = {
                        "orderID": _orderID,
                        "statusID": +statusID,
                        "statusName": statusName,
                        "comment": comment_data,
                        "adminName": adminName
                    };
                }
                if (operation == "AddFiveItems") {
                    let _orderID = el.dataset.orderid;
                    Data = {
                        "orderID": _orderID,
                        "statusID": +statusID,
                    };
                }
                //
                const response = await fetch(url + '&operation=' + operation + '&app=app_ordercontent', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json;charset=utf-8',
                    },
                    body: JSON.stringify({
                        Data,
                    }),
                });
                //
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

                    document.location.href = result;
                }
                if ((result === 'SUCCESS') && (operation == "ChangeItemProperties")) {
                    let modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalOrderContentItem')); // Returns a Bootstrap modal instance
                    modal.hide();
                    myToast.showSuccess();

                }
                if ((result === 'SUCCESS') && (operation == "AddComment")) {
                    document.querySelector('textarea#status_comment_n').value = '';
                    _updateLog(Data);
                    myToast.showSuccess();

                }
            } //async function fetchData
            // ============================================
            // выключаем элементы ввода
            disableInputs(orderForm, currencyForm);
            fetchData();
            enableInputs(orderForm, currencyForm);
            deActivateSaveButtons();
        }); //orderForm.addEventListener
    });
}


// document.addEventListener( "DOMContentLoaded", function() {
//     //if IsRefresh cookie exists
//     let isReloaded = sessionStorage.getItem( 'isReloaded' );
//     if ( isReloaded != null && isReloaded != "" ) {
//         //cookie exists then you refreshed this page(F5, reload button or right click and reload)
//         //SOME CODE
//         sessionStorage.removeItem( "isReloaded" );
//     } else {
//         //cookie doesnt exists then you landed on this page
//         //SOME CODE
//         sessionStorage.setItem( 'isReloaded', true );
//     }
// } );


// window.onbeforeunload = function() {


//     const DISALLOW_RELOAD = sessionStorage.getItem('DOCUMENT_IS_NOT_SAVED');

//     if (DISALLOW_RELOAD == true) {
//         return false;
//     } else {
//         return true;
//     }


// };

// window.onbeforeunload = function (evt) {
//     var message = "Document 'foo' is not saved. You will lost the changes if you leave the page.";
//     if (typeof evt == "undefined") {
//         evt = window.event;
//     }
//     if (evt) {
//         evt.returnValue = message;
//     }
//     return message;
// }

// service2@asbis.by