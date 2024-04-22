// import Datepicker from '/lib/vanillajs-datepickerInvoice/js/datepicker.js';
// import ru from '/lib/vanillajs-datepicker/js/locales/ru.js';
import * as bsToast from "../Toasts/appToasts.js";
import appCurrency from "../Currency/appCurrency.js";
//
//
//

const app = function() {
    const myToast = bsToast;
    const optionsInvoiceDate = {
        // ...options
        buttonClass: 'btn',
        clearButton: true,
        daysOfWeekHighlighted: [0, 6],
        // format: 'yyyy-mm-dd |  DD, dd MM',
        showDaysOfWeek: true,
        showOnFocus: false,
        title: 'Даты выставления счёта, заказа или контракта',
        todayButton: true,
        todayHighlight: true,
        weekStart: 1,
        // language: 'ru',
        language: 'by',
        updateOnBlur: false,
    };

    const btn_CreateInvoice = document.querySelector('button[name="btn_CreateInvoice"]');
    const btn_LoadInvoice = document.querySelector('button[name="btn_LoadInvoice"]');
    const btn_SaveInvoice = document.querySelector('button[name="btn_SaveInvoice"]');
    const btn_SetInvoiceDefault = document.querySelector('button[name="btn_SetInvoiceDefault"]');

    const formParams = document.getElementById("invoiceParamsForm");
    const invoiceDateEl = document.querySelector('input[name="invoiceDate"]');
    const orderDateEl = document.querySelector('input[name="orderDate"]');
    // const contractDateEl = document.querySelector('input[name="contractDate"]');
    const datepickerInvoice = new Datepicker(invoiceDateEl, optionsInvoiceDate);
    const datepickerOrder = new Datepicker(orderDateEl, optionsInvoiceDate);
    // const datepickerContract = new Datepicker(contractDateEl, optionsInvoiceDate);
    const btn_submitInvoiceDate = document.querySelector('button[name="btn_submitInvoiceDate"]');
    const btn_submitOrderDate = document.querySelector('button[name="btn_submitOrderDate"]');
    const btn_submitContractDate = document.querySelector('button[name="btn_submitContractDate"]');

    var REQUISITES_CHANGED = false;
    const formCompany = document.getElementById("invoiceCompanyForm");
    const requisitesInputs = formCompany.querySelectorAll('textarea[name^="pm_"],input[name^="pm_"]');
    const bar_req1 = document.querySelector('#save_requisites')
    const pm_fieldset = document.querySelector('fieldset#pm_fieldset');
    const btn_save_requisites = document.querySelector('button[name="btn_save_requisites"]');
    const useCustomRequisitesEl = document.querySelector('input[name="useCustomrequisites"]');
    const btn_cancel = document.getElementById('btn_cancel');


    function SaveInvoiceMain(event) {
        let _orderID = event.target.dataset.orderid;
        let _invoiceID = event.target.dataset.invoiceid;
        if (event.originalTarget.nodeName == 'I') {
            _orderID = event.target.parentElement.dataset.orderid;
            _invoiceID = event.target.parentElement.dataset.invoiceid;
        } // если кликнули по иконке
        let _buyerID = document.querySelector('h5[data-buyerID]').dataset.buyerid;
        let aCurrency = appCurrency("CreateInvoice");
        let CID = aCurrency.CID;
        let currencyValue = aCurrency.currencyValue;

        const Data = {
            'operation': 'SaveInvoiceAll',
            'orderID': +_orderID,
            'invoiceID': +_invoiceID,
            'requisites': document.getElementById("pm_requisites").value,
            'director_nominative': document.getElementById("pm_director_nominative").value,
            'director_genitive': document.getElementById("pm_director_genitive").value,
            'director_reason': document.getElementById("pm_director_reason").value,
            'purposeID': +document.getElementById("purposeID").value,
            'fundingID': +document.getElementById("fundingID").value,
            'deliveryFrom': document.getElementById("deliveryFrom").value,
            'deliveryTo': document.getElementById("deliveryTo").value,
            'clientTransport': +document.getElementById("clientTransport").checked,
            'hosterTransport': +document.getElementById("hosterTransport").checked,
            'payThenGet': +document.getElementById("payThenGet").checked,
            'getThenPay': +document.getElementById("getThenPay").checked,
            'invoiceDate': document.getElementById("invoiceDate").value,
            'orderDate': document.getElementById("orderDate").value,
            // 'contractDate': document.getElementById("contractDate").value,
            'actuality_termin': document.getElementById("actuality_termin").value,
            'delivery_termin': document.getElementById("delivery_termin").value,
            'payment_termin': document.getElementById("payment_termin").value,
            'payment_prepay': document.getElementById("payment_prepay").value,
            'CID': +CID,
            'currencyValue': currencyValue,
            'sellerID': 1,
            'buyerID': +_buyerID,
        };
        appAjax(Data);
        REQUISITES_CHANGED = false;
    }

    async function appAjax(params) {
        console.log(params)
        let operation = params.operation;
        let orderID = params.orderID;
        //
        let url = checkOnUrl('admin.php?dpt=custord&sub=orders&orders_detailed=yes&orderID=' + +orderID + '&operation=' + operation + '&app=app_invoicesimple');
        const Data = { ...params
        };
        const returnDataOperations = ["CreateInvoice", "LoadInvoice", "SetInvoiceDefault"];
        let isReturnDataOperation = returnDataOperations.includes(operation);
        // console.log(isReturnDataOperation)
        const response = await fetch(url, {
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
        // console.info(response, result)
        //
        // На основе ответа от сервера показываем сообщения об Успехе или Ошибке
        if (result === 'SUCCESS') {
            myToast.showSuccess();
        } else if (result === 'FAILED') {
            myToast.showError();
        } else {
            if (isReturnDataOperation) {
                let ans = JSON.parse(result);
                answerDecode(ans, operation);

            } else {
                document.location.href = result;
            }
        }
    }

    function answerDecode(d, operation) {

        // switch (operation) {
        //     case 'CreateInvoice':
        //         break;
        //     case 'SetInvoiceDefault':
        //         break;
        //     case 'LoadInvoice':
        //         break;
        // }

        if (d) {


            console.log(operation + " " + d.DeliveryType + " " + d.PaymentType)

            document.getElementById('HasNlotInvoiceContainer').classList.add('visually-hidden');

            document.getElementById("pm_requisites").value = d.requisites;
            document.getElementById("pm_director_nominative").value = d.director_nominative;
            document.getElementById("pm_director_genitive").value = d.director_genitive;
            document.getElementById("pm_director_reason").value = d.director_reason;
            document.getElementById("purposeID").value = d.purposeID;
            document.getElementById("fundingID").value = d.fundingID;
            document.getElementById("deliveryFrom").value = d.deliveryFrom;
            document.getElementById("deliveryTo").value = d.deliveryTo;
            document.getElementById("clientTransport").checked = (+d.DeliveryType == 0) ? true : false;
            document.getElementById("hosterTransport").checked = (+d.DeliveryType == 1) ? true : false;
            document.getElementById("payThenGet").checked = (+d.PaymentType == 0) ? true : false;
            document.getElementById("getThenPay").checked = (+d.PaymentType == 1) ? true : false;
            datepickerInvoice.setDate(d.invoice_time);
            datepickerOrder.setDate(d.order_time);
            // datepickerContract.setDate(d.contract_time);
            document.getElementById("actuality_termin").value = d.actuality_termin;
            document.getElementById("delivery_termin").value = d.delivery_termin;
            document.getElementById("payment_termin").value = d.payment_termin;
            document.getElementById("payment_prepay").value = d.payment_prepay;

            document.getElementById('HasInvoiceContainer').classList.remove('visually-hidden');

        } else {
            myToast.showError();
        }

    }


    pm_fieldset.addEventListener('input', function(event) {
        if (bar_req1.classList.contains('visually-hidden')) {
            bar_req1.classList.remove('visually-hidden');
        }
        useCustomRequisitesEl.checked = true;
    });
    //
    requisitesInputs.forEach((el, index) => {
        el.addEventListener('change', (event) => {
            REQUISITES_CHANGED = true;
        })
    });
    //
    useCustomRequisitesEl.addEventListener('change', function(event) {

        let _orderID = btn_SaveInvoice.dataset.orderid;
        let _invoiceID = btn_SaveInvoice.dataset.invoiceid;
        let _buyerID = document.querySelector('h5[data-buyerID]').dataset.buyerid;

        if (event.target.checked == true) {

            requisitesInputs.forEach((el, index) => {
                el.removeAttribute('disabled');
                el.removeAttribute('readonly');
            });

            document.querySelector('h5[data-buyerID]').dataset.buyerid = 0;
            bar_req1.classList.remove('visually-hidden');
        }

        if (event.target.checked == false) {

            document.querySelector('h5[data-buyerID]').dataset.buyerid = btn_CreateInvoice.dataset.companyid;

            requisitesInputs.forEach((el, index) => {
                el.setAttribute('disabled', 'disabled');
                el.setAttribute('readonly', 'readonly');
            });

            bar_req1.classList.add('visually-hidden');
        }


        const Data = {
            'operation': 'UpdateRequisites',
            'orderID': +_orderID,
            'buyerID': +_buyerID,
            'invoiceID': +_invoiceID,
            'requisites': document.getElementById("pm_requisites").value,
            'director_nominative': document.getElementById("pm_director_nominative").value,
            'director_genitive': document.getElementById("pm_director_genitive").value,
            'director_reason': document.getElementById("pm_director_reason").value,
        };

        if (REQUISITES_CHANGED) {
            appAjax(Data);
            REQUISITES_CHANGED = false;
        }


    });

    //
    btn_cancel.addEventListener('click', () => {
        formCompany.reset();
    });
    //
    btn_save_requisites.addEventListener('click', () => {
        let _orderID = event.target.dataset.orderid;
        let _invoiceID = event.target.dataset.invoiceid;
        let _buyerID = document.querySelector('h5[data-buyerID]').dataset.buyerid;
        if (event.originalTarget.nodeName == 'I') {
            _orderID = event.target.parentElement.dataset.orderid;
            _invoiceID = event.target.parentElement.dataset.invoiceid;
        } // если кликнули по иконке
        const Data = {
            'operation': 'UpdateRequisites',
            'orderID': +_orderID,
            'buyerID': +_buyerID,
            'invoiceID': +_invoiceID,
            'requisites': document.getElementById("pm_requisites").value,
            'director_nominative': document.getElementById("pm_director_nominative").value,
            'director_genitive': document.getElementById("pm_director_genitive").value,
            'director_reason': document.getElementById("pm_director_reason").value,
        };
        appAjax(Data);
    });
//
    document.getElementById('btn_restore_company_from_order').addEventListener('click', () => {
        let _orderID = event.target.dataset.orderid;
        let _invoiceID = event.target.dataset.invoiceid;

        if (event.originalTarget.nodeName == 'I') {
            _orderID = event.target.parentElement.dataset.orderid;
            _invoiceID = event.target.parentElement.dataset.invoiceid;
        } // если кликнули по иконке
        const Data = {
            'operation': 'RestoreCompanyFromOrder',
            'orderID': +_orderID,
            'invoiceID': +_invoiceID,
        };
        appAjax(Data);
    });


    //
    btn_CreateInvoice.addEventListener('click', (event) => {

        let _orderID = event.target.dataset.orderid;
        let _invoiceID = event.target.dataset.invoiceid;
        let _companyID = event.target.dataset.companyid;
        // let _companyID = document.querySelector('h5[data-buyerID]').dataset.buyerid;
        if (event.originalTarget.nodeName == 'I') {
            _orderID = event.target.parentElement.dataset.orderid;
            _invoiceID = event.target.parentElement.dataset.invoiceid;
            // _companyID = event.target.parentElement.dataset.companyid;
        } // если кликнули по иконке

        let aCurrency = appCurrency("CreateInvoice");
        let CID = aCurrency.CID;
        let currencyValue = aCurrency.currencyValue;
        const Data = {
            'operation': 'CreateInvoice',
            'orderID': +_orderID,
            'CID': +CID,
            'currencyValue': currencyValue,
            'sellerID': 1,
            'buyerID': _companyID,

        }
        appAjax(Data);
    });
    //
    btn_SaveInvoice.addEventListener('click', (event) => {
        SaveInvoiceMain(event);
    });
    //
    btn_LoadInvoice.addEventListener('click', (event) => {
        let _orderID = event.target.dataset.orderid;
        let _invoiceID = event.target.dataset.invoiceid;
        if (event.originalTarget.nodeName == 'I') {
            _orderID = event.target.parentElement.dataset.orderid;
            _invoiceID = event.target.parentElement.dataset.invoiceid;
        } // если кликнули по иконке
        const Data = {
            'operation': 'LoadInvoice',
            'orderID': +_orderID,
            'invoiceID': +_invoiceID
        }
        appAjax(Data);
    });
    //
    btn_SetInvoiceDefault.addEventListener('click', (event) => {
        let _orderID = event.target.dataset.orderid;
        let _invoiceID = event.target.dataset.invoiceid;
        let _companyID = document.querySelector('h5[data-buyerID]').dataset.buyerid;
        if (event.originalTarget.nodeName == 'I') {
            _orderID = event.target.parentElement.dataset.orderid;
            _invoiceID = event.target.parentElement.dataset.invoiceid;
        } // если кликнули по иконке
        const Data = {
            'operation': 'SetInvoiceDefault',
            'orderID': +_orderID,
            'invoiceID': +_invoiceID,
            'buyerID': +_companyID
        }
        appAjax(Data);
    });
    //
    datepickerInvoice.changeDate = () => {
        console.info(datepickerInvoice.getDate());
    }
    //
    btn_submitInvoiceDate.addEventListener('click', function(event) {
        event.preventDefault();
        if (!window.confirm('Изменить дату выставления счета?')) {
            return false;
        }
        let _orderID = event.target.dataset.orderid;
        const Data = {
            'operation': 'SetInvoiceDate',
            'orderID': +_orderID,
            'date': datepickerInvoice.getDate('yyyy-mm-dd') + ' 12:01:00',
        };
        appAjax(Data);
    });
    //
    btn_submitOrderDate.addEventListener('click', function(event) {
        event.preventDefault();
        if (!window.confirm('Изменить дату выставления счета?')) {
            return false;
        }
        let _orderID = event.target.dataset.orderid;
        const Data = {
            'operation': 'SetOrderDate',
            'orderID': +_orderID,
            'date': datepickerOrder.getDate('yyyy-mm-dd') + ' 12:00:00',
        };
        appAjax(Data);
    });

}

window.onload = app();
// export default app
export default app