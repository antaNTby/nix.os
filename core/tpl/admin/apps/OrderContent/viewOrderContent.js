// viewOrderContent arrQuantity .js
import updateModel from './utils/updateModelOrderContent.js'
import updateRow from './utils/updateRow.js'
//
let controlsQuantity = document.querySelectorAll('input[data-control="Quantity"]');
let controlsPurePrice = document.querySelectorAll('input[data-control="purePrice"]');
let controlsShippingPay = document.querySelectorAll('input[data-control="shippingPay"]');
let controlsPrice = document.querySelectorAll('input[data-control="Price"]');
let controlsOutPrice = document.querySelectorAll('input[data-control="outPrice"]');
let controlsOutPriceFix = document.querySelectorAll('input[data-control="outPriceFix"]');
let controlsOutCost = document.querySelectorAll('input[data-control="outCost"]');
let controlsOutVAT_Value = document.querySelectorAll('input[data-control="outVAT_Value"]');
let controlsOutCost_WITH_VAT = document.querySelectorAll('input[data-control="outCost_WITH_VAT"]');
//
let rowsCount = +controlsQuantity.length;
//объявляем Инпуты
//
const Inputs = {
    "controlsQuantity": Array.from(controlsQuantity),
    "controlsPurePrice": Array.from(controlsPurePrice),
    "controlsShippingPay": Array.from(controlsShippingPay),
    "controlsPrice": Array.from(controlsPrice),
    "controlsOutPrice": Array.from(controlsOutPrice),
    "controlsOutPriceFix": Array.from(controlsOutPriceFix),
    "controlsOutCost": Array.from(controlsOutCost),
    "controlsOutVAT_Value": Array.from(controlsOutVAT_Value),
    "controlsOutCost_WITH_VAT": Array.from(controlsOutCost_WITH_VAT),
};
let TotalInputs = {
    "rowsCount": document.querySelector('input#countOrderedCarts'),
    "totalItemsQuantity": document.querySelector('input#totalItemsQuantity'),
    "totalCost_WITHOUT_VAT": document.querySelector('input#totalCost_WITHOUT_VAT'),
    "totalVAT_Amount": document.querySelector('input#totalVAT_Amount'),
    "totalCost_WITH_VAT": document.querySelector('input#totalCost_WITH_VAT'),
};
//объявляем Кливы
const Cleaves = {
    "cleavesQuantity": [],
    "cleavesPurePrice": [],
    "cleavesShippingPay": [],
    "cleavesPrice": [],
    "cleavesOutPrice": [],
    "cleavesOutPriceFix": [],
    "cleavesOutCost": [],
    "cleavesOutVAT_Value": [],
    "cleavesOutCost_WITH_VAT": [],
    "Totals": {}
};
const Results = {
    Strings: {
        "rowsCount": document.querySelector('span#countOrderedCarts_STRING'),
        "totalItemsQuantity": document.querySelector('span#totalItemsQuantity_STRING'),
        "totalCost_WITHOUT_VAT": document.querySelector('span#totalCost_WITHOUT_VAT_STRING'),
        "totalVAT_Amount": document.querySelector('span#totalVAT_Amount_STRING'),
        "totalCost_WITH_VAT": document.querySelector('span#totalCost_WITH_VAT_STRING'),
    },
    Spans: {
        "rowsCount": document.querySelector('span#countOrderedCarts'),
        "totalItemsQuantity": document.querySelector('span#totalItemsQuantity'),
        "totalCost_WITHOUT_VAT": document.querySelector('span#totalCost_WITHOUT_VAT'),
        "totalVAT_Amount": document.querySelector('span#totalVAT_Amount'),
        "totalCost_WITH_VAT": document.querySelector('span#totalCost_WITH_VAT'),
    }
}
const inputDiscountPercent = document.querySelector('input[data-control="discountPercent"]');
const inputDiscountValue = document.querySelector('input[data-control="discountValue"]');
const radioDiscountValue = document.querySelectorAll('input[type=radio][name="options-discountValue"]');
const cleaveDiscountValue = new Cleave(inputDiscountValue, settingsFloat2);
//
const appOperationButtons = document.querySelectorAll('button[data-app="OrderContent"][data-operation]');
const appSaveButtons = document.querySelectorAll('button[data-app="OrderContent"][data-operation][data-action="Save"]');
// const orderForm = document.querySelector('#OrderContentTable');
// const currencyForm = document.querySelector('#CurrencyTable');
//
//
//
const bsTab = new bootstrap.Tab('#myTabOrder');
const tabElements = document.querySelectorAll('a.nav-link[data-bs-toggle="tab"]');

let thisOrderID = document.querySelector('input#thisOrderID').value;
let storage = localStorage.getItem('viewOrderContentLastTab'+thisOrderID);

const triggerTabList = document.querySelectorAll('#myTabOrder a.nav-link[data-bs-toggle="tab"]')



Array.prototype.slice.call(tabElements).forEach(function(tabEl, index) {
    tabEl.addEventListener('shown.bs.tab', event => {
        // console.log( event.target ); // newly activated tab
        // console.log( event.relatedTarget ); // previous active tab
        let activeTab = event.target.dataset.bsTarget;
        localStorage.setItem('viewOrderContentLastTab'+thisOrderID, activeTab); //сохраняем активную панель
    });
});

// восстанавливаем вкладку из сессионного хранилища
if (storage && storage !== "#") {
    triggerTabList.forEach(triggerEl => {
        const tabTrigger = new bootstrap.Tab(triggerEl);
        if (storage == tabTrigger._config.target) {
            tabTrigger.show();
            // bootstrap.Tab.getInstance(triggerEl).show() // Select tab by name
            return false;
        }
    });
}

//
//
//
//делаем Кливы
function initCleaves() {
    Array.prototype.slice.call(controlsQuantity).forEach(function(el, index) {
        let cleaveInput = new Cleave(el, settingsIntegerPositive);
        // cleaveInput.setRawValue(el.value);
        Cleaves.cleavesQuantity[index] = cleaveInput;
    });
    Array.prototype.slice.call(controlsPurePrice).forEach(function(el, index) {
        let cleaveInput = new Cleave(el, settingsFloatPositive);
        // cleaveInput.setRawValue(el.value);
        Cleaves.cleavesPurePrice[index] = cleaveInput;
    });
    Array.prototype.slice.call(controlsShippingPay).forEach(function(el, index) {
        let cleaveInput = new Cleave(el, settingsFloat);
        // cleaveInput.setRawValue(el.value);
        Cleaves.cleavesShippingPay[index] = cleaveInput;
    });
    Array.prototype.slice.call(controlsPrice).forEach(function(el, index) {
        let cleaveInput = new Cleave(el, settingsFloat);
        // cleaveInput.setRawValue(el.value);
        Cleaves.cleavesPrice[index] = cleaveInput;
    });
    /*in*/
    Array.prototype.slice.call(controlsOutPrice).forEach(function(el, index) {
        let cleaveInput = new Cleave(el, settingsCurrency);
        // cleaveInput.setRawValue(el.value);
        Cleaves.cleavesOutPrice[index] = cleaveInput;
    });
    Array.prototype.slice.call(controlsOutPriceFix).forEach(function(el, index) {
        let cleaveInput = new Cleave(el, settingsOutPrice);
        // cleaveInput.setRawValue(el.value);
        Cleaves.cleavesOutPriceFix[index] = cleaveInput;
    });
    /*out*/
    Array.prototype.slice.call(controlsOutCost).forEach(function(el, index) {
        let cleaveInput = new Cleave(el, settingsOutPrice);
        // cleaveInput.setRawValue(el.value);
        Cleaves.cleavesOutCost[index] = cleaveInput;
    });
    Array.prototype.slice.call(controlsOutVAT_Value).forEach(function(el, index) {
        let cleaveInput = new Cleave(el, settingsOutPrice);
        // cleaveInput.setRawValue(el.value);
        Cleaves.cleavesOutVAT_Value[index] = cleaveInput;
    });
    Array.prototype.slice.call(controlsOutCost_WITH_VAT).forEach(function(el, index) {
        let cleaveInput = new Cleave(el, settingsOutPrice);
        // cleaveInput.setRawValue(el.value);
        Cleaves.cleavesOutCost_WITH_VAT[index] = cleaveInput;
    });
    // TotalInputs
    // преобразовать в массив, затем map, затем fromEntries обратно объект
    Object.entries(TotalInputs).map(function([key, value]) {
        let settings = (key != 'Quantity' && key != 'rowsCount' && key != 'totalItemsQuantity') ? settingsOutPrice : settingsIntegerPositive;
        let cleaveInput = new Cleave(value, settings);
        // cleaveInput.setRawValue(value.value);
        Cleaves.Totals[key] = cleaveInput;
    });
}

function _initDiscountValue() {
    const minusChecked = +radioDiscountValue[0].checked;
    const _value = +cleaveDiscountValue.getRawValue();
    let absValue = Math.abs(_value);
    let value = 0;
    if (minusChecked == true) {
        value = -1 * absValue;
        radioDiscountValue[0].checked = true;
        radioDiscountValue[1].checked = false;
    } else {
        value = 1 * _value;
        radioDiscountValue[0].checked = false;
        radioDiscountValue[1].checked = true;
    }
    return value;
}
// считываем данные из html-шаблона
function initTable() {
    let discountValue = _initDiscountValue();
    let thisOrderID = document.querySelector('input#thisOrderID').value;
    let tempTable = {
        // "orderID": Inputs.controlsPrice[0].dataset.orderid,
        "orderID": +thisOrderID,
        "rowsCount": rowsCount,
        "discountVal": discountValue,
        "rows": [],
        // "names": [],
    };
    for (let ii = 0; ii < rowsCount; ii++) {
        let newRow = [];
        newRow = {
            "index": +ii,
            "Quantity": +Cleaves.cleavesQuantity[ii].getRawValue(),
            "PurePrice": +Cleaves.cleavesPurePrice[ii].getRawValue(),
            "ShippingPay": Cleaves.cleavesShippingPay[ii] ? +Cleaves.cleavesShippingPay[ii].getRawValue() : 0.000000,
            "Price": +Cleaves.cleavesPrice[ii].getRawValue(),
            "OutPrice": +Cleaves.cleavesOutPrice[ii].getRawValue(),
            "priority": 10 * (+rowsCount - ii),
            "itemID": +Inputs.controlsPrice[ii].dataset.itemid
        };
        tempTable.rows[ii] = { ...newRow
        };
    }
    return tempTable;
}

function initTableResults() {
    let tempTable = {
        "rows": []
    };
    for (let ii = 0; ii < rowsCount; ii++) {
        let newRow = [];
        newRow = {
            "OutCost": 0,
            "OutVAT_Value": 0,
            "OutCost_WITH_VAT": 0,
        };
        tempTable.rows[ii] = { ...newRow
        };
    }
    return tempTable;
}

function validateForm(ii, value) {
    if (+value <= 0) {
        inputDiscountPercent.classList.add('is-invalid');
        inputDiscountValue.classList.add('is-invalid');
        controlsQuantity[ii].classList.add('is-invalid');
        controlsPurePrice[ii].classList.add('is-invalid');
        controlsShippingPay[ii].classList.add('is-invalid');
        controlsPrice[ii].classList.add('is-invalid');
        controlsOutPrice[ii].classList.add('is-invalid');
        controlsOutCost[ii].classList.add('is-invalid');
        controlsOutVAT_Value[ii].classList.add('is-invalid');
        controlsOutCost_WITH_VAT[ii].classList.add('is-invalid');
        appSaveButtons.forEach(function(el) {
            el.classList.add('d-none');
        });
    }
    if (+value > 0) {
        inputDiscountPercent.classList.remove('is-invalid');
        inputDiscountValue.classList.remove('is-invalid');
        controlsQuantity[ii].classList.remove('is-invalid');
        controlsPurePrice[ii].classList.remove('is-invalid');
        controlsShippingPay[ii].classList.remove('is-invalid');
        controlsPrice[ii].classList.remove('is-invalid');
        controlsOutPrice[ii].classList.remove('is-invalid');
        controlsOutCost[ii].classList.remove('is-invalid');
        controlsOutVAT_Value[ii].classList.remove('is-invalid');
        controlsOutCost_WITH_VAT[ii].classList.remove('is-invalid');
        appSaveButtons.forEach(function(el) {
            el.classList.remove('d-none');
        });
    }
}

function disableInputs(orderForm, currencyForm) {
    disableSaveButtons();
    orderForm.querySelectorAll('input').forEach(function(input) {
        input.setAttribute('disabled', true);
    });
    orderForm.querySelectorAll('button').forEach(function(input) {
        input.setAttribute('disabled', true);
    });
    currencyForm.querySelectorAll('input').forEach(function(input) {
        input.setAttribute('disabled', true);
    });
    currencyForm.querySelectorAll('select').forEach(function(input) {
        input.setAttribute('disabled', true);
    });
}

function enableInputs(orderForm, currencyForm) {
    enableSaveButtons();
    orderForm.querySelectorAll('input').forEach(function(input) {
        input.removeAttribute('disabled');
    });
    orderForm.querySelectorAll('button').forEach(function(input) {
        input.removeAttribute('disabled');
    });
    currencyForm.querySelectorAll('input').forEach(function(input) {
        input.removeAttribute('disabled');
    });
    currencyForm.querySelectorAll('select').forEach(function(input) {
        input.removeAttribute('disabled');
    });
}

function disableSaveButtons() {
    appSaveButtons.forEach(function(el) {
        el.setAttribute('disabled', true);
        el.classList.add('btn-outline-secondary');
        // el.innerHTML = '<i class="bi bi-hourglass"></i>';
    });
}

function enableSaveButtons() {
    appSaveButtons.forEach(function(el) {
        el.removeAttribute('disabled');
        el.classList.remove('btn-outline-secondary');
        // el.innerHTML = '<i class="bi bi-save"></i>';
    });
}

function deActivateSaveButtons() {
    appSaveButtons.forEach(function(el) {
        el.setAttribute('disabled', true);
        el.classList.add('btn-outline-secondary');
        // el.innerHTML = '<i class="bi bi-save"></i>';
    });
}

function activateSaveButtons() {
    appSaveButtons.forEach(function(el) {
        el.removeAttribute('disabled');
        el.classList.remove('btn-outline-secondary');
        // el.innerHTML = '<i class="bi bi-save"></i>';
    });
}

function helpUpdateView(control, value, oldValue) {
    // подсказки
    let helpid = control.dataset.itemid;
    let helpcontrol = control.dataset.control;
    let helpOld = document.querySelector('div.js-collapse-old[data-control="' + helpcontrol + '"]');
    let helpSub = document.querySelector('div.js-collapse-vat[data-helpid="' + helpid + '"] small[data-help="sub"][data-control="' + helpcontrol + '"]');
    let helpSup = document.querySelector('div.js-collapse-vat[data-helpid="' + helpid + '"] small[data-help="sup"][data-control="' + helpcontrol + '"]');
    helpOld.innerText = ff(value * 100 / 100, 4);
    helpSub.innerText = ff(value * 100 / 120, 4);
    helpSup.innerText = ff(value * 120 / 100, 4);
}

function main(getData) {
    let data = getData();
    let rows_count = data.rowsCount;
    //sss
    for (let ii = 0; ii < rows_count; ii++) {
        //
        //
        Cleaves.cleavesQuantity[ii].setRawValue(data.rows[ii].Quantity);
        controlsQuantity[ii].addEventListener('input', function() {
            const value = Cleaves.cleavesQuantity[ii].getRawValue();
            // Проверка на мин и макс цену
            validateForm(ii, value);
            // Обновить модель
            updateRow(controlsQuantity[ii], {
                newValue: +value,
                field: "Quantity",
                index: ii,
                onUpdate: 'inputQuantity',
            });
        });
        //
        Cleaves.cleavesPurePrice[ii].setRawValue(ff(data.rows[ii].PurePrice, 8.00));
        controlsPurePrice[ii].addEventListener('change', function() {
            const value = Cleaves.cleavesPurePrice[ii].getRawValue();
            // Проверка на мин и макс цену
            validateForm(ii, value);
            // Обновить модель
            updateRow(controlsPurePrice[ii], {
                newValue: +value,
                field: "PurePrice",
                index: ii,
                onUpdate: 'inputPurePrice',
            });
        });
        //
        Cleaves.cleavesShippingPay[ii].setRawValue(ff(data.rows[ii].ShippingPay, 8.00));
        controlsShippingPay[ii].addEventListener('change', function() {
            const value = Cleaves.cleavesShippingPay[ii].getRawValue();
            // Проверка на мин и макс цену
            validateForm(ii, value);
            // Обновить модель
            updateRow(controlsShippingPay[ii], {
                newValue: +value,
                field: "ShippingPay",
                index: ii,
                onUpdate: 'inputShippingPay',
            });
        });
        //
        Cleaves.cleavesPrice[ii].setRawValue(ff(data.rows[ii].Price, 8.00));
        controlsPrice[ii].addEventListener('change', function() {
            const value = Cleaves.cleavesPrice[ii].getRawValue();
            // Проверка на мин и макс цену
            validateForm(ii, value);
            helpUpdateView(this, value);
            // Обновить модель
            updateRow(controlsPrice[ii], {
                newValue: +value,
                field: "Price",
                index: ii,
                onUpdate: 'inputPrice',
            });
        })
        //
        Cleaves.cleavesOutPrice[ii].setRawValue(ff(data.rows[ii].OutPrice, 8.00));
        Cleaves.cleavesOutPriceFix[ii].setRawValue(ff(data.rows[ii].OutPrice, 2.00));
        controlsOutPrice[ii].addEventListener('change', function() {
            const value = Cleaves.cleavesOutPrice[ii].getRawValue();
            // Проверка на мин и макс цену
            validateForm(ii, value);
            helpUpdateView(this, value);
            // Обновить модель
            updateRow(controlsOutPrice[ii], {
                newValue: +value,
                field: "OutPrice",
                index: ii,
                onUpdate: 'inputOutPrice',
            });
        });
        //
        //
    }
    // клив Скидки
    inputDiscountValue.addEventListener('change', discountValueOnChange);
    radioDiscountValue[0].addEventListener('click', clickPlusMinus);
    radioDiscountValue[1].addEventListener('click', clickPlusMinus);
}

function discountValueOnChange() {
    const _value = +cleaveDiscountValue.getRawValue();
    let absValue = Math.abs(_value);
    let isPositive = true;
    let value = 0;
    if (_value >= 0) {
        isPositive = true;
    } else {
        isPositive = false;
    }
    if (isPositive == false) {
        value = -1 * absValue;
        radioDiscountValue[0].checked = true;
        radioDiscountValue[1].checked = false;
    } else {
        value = 1 * absValue;
        radioDiscountValue[0].checked = false;
        radioDiscountValue[1].checked = true;
    }
    // cleaveDiscountValue.setRawValue( value );
    // Обновить модель
    if (absValue == 0) {
        value = 0
    };
    updateModel(inputDiscountValue, {
        discountVal: value,
        onUpdate: 'inputDiscountValue'
    });
}

function clickPlusMinus() {
    const _value = +cleaveDiscountValue.getRawValue();
    const minusChecked = +radioDiscountValue[0].checked;
    let absValue = Math.abs(_value);
    let value = 0;
    if (minusChecked == true) {
        value = -1 * absValue;
        radioDiscountValue[0].checked = true;
        radioDiscountValue[1].checked = false;
    } else {
        value = 1 * absValue;
        radioDiscountValue[0].checked = false;
        radioDiscountValue[1].checked = true;
    }
    if (absValue == 0) {
        value = 0
    };
    cleaveDiscountValue.setRawValue(value);
    updateModel(inputDiscountValue, {
        discountVal: value,
        onUpdate: 'inputDiscountValue'
    });
}

function updateResultView(d) {
    Cleaves.Totals["rowsCount"].setRawValue(d.Total.rowsCount);
    Cleaves.Totals["totalItemsQuantity"].setRawValue(d.Total.totalItemsQuantity);
    Cleaves.Totals["totalCost_WITHOUT_VAT"].setRawValue(ff(d.Total.totalCost_WITHOUT_VAT, 2.00));
    Cleaves.Totals["totalVAT_Amount"].setRawValue(ff(d.Total.totalVAT_Amount, 2.00));
    Cleaves.Totals["totalCost_WITH_VAT"].setRawValue(ff(d.Total.totalCost_WITH_VAT, 2.00));
    //
    //
    Results.Strings["rowsCount"].innerText = d.Strings.rowsCount;
    Results.Strings["totalItemsQuantity"].innerText = d.Strings.totalItemsQuantity;
    Results.Strings["totalCost_WITHOUT_VAT"].innerText = d.Strings.totalCost_WITHOUT_VAT;
    Results.Strings["totalVAT_Amount"].innerText = d.Strings.totalVAT_Amount;
    Results.Strings["totalCost_WITH_VAT"].innerText = d.Strings.totalCost_WITH_VAT;
    //
    // Results.Spans["rowsCount"].innerText = d.Total.rowsCount;
    // Results.Spans["totalItemsQuantity"].innerText = d.Total.totalItemsQuantity;
    // Results.Spans["totalCost_WITHOUT_VAT"].innerText = d.Total.totalCost_WITHOUT_VAT;
    // Results.Spans["totalVAT_Amount"].innerText = d.Total.totalVAT_Amount;
    // Results.Spans["totalCost_WITH_VAT"].innerText = d.Total.totalCost_WITH_VAT;
    Results.Spans["rowsCount"].innerText = Cleaves.Totals.rowsCount.getFormattedValue();
    Results.Spans["totalItemsQuantity"].innerText = Cleaves.Totals.totalItemsQuantity.getFormattedValue();
    Results.Spans["totalCost_WITHOUT_VAT"].innerText = Cleaves.Totals.totalCost_WITHOUT_VAT.getFormattedValue();
    Results.Spans["totalVAT_Amount"].innerText = Cleaves.Totals.totalVAT_Amount.getFormattedValue();
    Results.Spans["totalCost_WITH_VAT"].innerText = Cleaves.Totals.totalCost_WITH_VAT.getFormattedValue();
    hideFetchMessage();

    function hideFetchMessage() {
        // document.getElementById('success').classList.add('d-none');
        // document.getElementById('error').classList.add('d-none');
    }
    activateSaveButtons();
}

function updateRowView(index, row, res) {
    Cleaves.cleavesOutCost_WITH_VAT[index].setRawValue(res.OutCost_WITH_VAT);
    //data
    Cleaves.cleavesQuantity[index].setRawValue(row.Quantity); //Quantity
    Cleaves.cleavesPurePrice[index].setRawValue(ff(row.PurePrice, 8.00)); //PurePrice
    Cleaves.cleavesShippingPay[index].setRawValue(ff(row.ShippingPay, 8.00)); //ShippingPay
    Cleaves.cleavesPrice[index].setRawValue(ff(row.Price, 8.00)); //Price
    //
    Cleaves.cleavesOutPrice[index].setRawValue(ff(row.OutPrice, 4)); //OutPrice
    let stringOutPrice = ff(row.OutPrice, 8.00);
    let OutPrice = Math.round(stringOutPrice * 100) / 100;
    Cleaves.cleavesOutPriceFix[index].setRawValue(ff(OutPrice, 2.00)); //OutPrice
    //
    // res
    Cleaves.cleavesOutCost[index].setRawValue(ff(res.OutCost, 2.00));
    Cleaves.cleavesOutVAT_Value[index].setRawValue(ff(res.OutVAT_Value, 2.00));
    Cleaves.cleavesOutCost_WITH_VAT[index].setRawValue(ff(res.OutCost_WITH_VAT, 2.00));
    // проверяем
    let rows_count = Cleaves.Totals["rowsCount"].getRawValue();
    for (let ii = 0; ii < rows_count; ii++) {
        let price = Cleaves.cleavesPrice[ii].getRawValue();
        price = ff(price, 8.00);
        validateForm(ii, price);
    }
}
export default main
export {
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
}
/*

index
Quantity
PurePrice
ShippingPay
Price
OutPrice
priority

*/