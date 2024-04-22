/*model.js*/
/*
    математическая модель валюты
*/
// import ProductRow from '../../js/classes/ClassProductRow.js';
// import ProductTable from '../../js/classes/ClassProductTable.js';
let rowsCount = 1;
let data = {
    orderID: 1,
    rowsCount: rowsCount,
    CID: 1,
    currencyValue: 1.0000,
    rows: [
        /*    {
                "index": 0,
                "Quantity": 0,
                "PurePrice": 0,
                "ShippingPay": 0,
                "Price": 0,
                "OutPrice": 0,
                "priority": 0,
                "itemid": 0,
            }*/
    ],
    res: [],
    names: [],
    discountVal: 0
};
let results = {
    Total: {
        "totalCost_WITHOUT_VAT": 0,
        "totalVAT_Amount": 0,
        "totalCost_WITH_VAT": 0
    }
};

function initData(params) {
    const startData = {
        currencyValue: params.currencyValue,
        rows: params.Table,
        names: params.Names,
    };
    return { ...startData
    };
}

function getData() {
    return { ...data
    }
}

function getResults() {
    return { ...results
    };
}

function getRes() {
    return { ...data.res
    };
}

function getNames() {
    return { ...data.names
    };
}

function calcShippingPay(index) {
    let res = 0;
    let discountValuePerRow = 0;
    if (data.rowsCount > 0) {
        discountValuePerRow = -1 * data.discountVal / data.rowsCount;
        let shippingPay_i = 0;
        if ((discountValuePerRow != 0) && (data.rows[index].Quantity) > 0) {
            shippingPay_i = discountValuePerRow / data.rows[index].Quantity;
        }
        res = +shippingPay_i;
    }
    return res;
}

function setRowValue(rd) {
    let index = +rd.index;
    let field = rd.field;
    let newValue = +rd.newValue;
    if (rd.onUpdate == 'input' + field) {
        data.rows[index][field] = newValue; //устанавливаем само поле
        data.rows[index].ShippingPay = calcShippingPay(index);
        if (field == 'PurePrice') {
            let price = data.rows[index].PurePrice + data.rows[index].ShippingPay;
            data.rows[index].Price = ff(price, 8.00);
        }
        if (field == 'ShippingPay') {
            let price = data.rows[index].PurePrice + data.rows[index].ShippingPay;
            data.rows[index].Price = ff(price, 8.00);
        }
        if (field == 'Price') {
            let purePrice = (data.rows[index].Price - data.rows[index].ShippingPay);
            data.rows[index].PurePrice = ff(purePrice, 8.00);
        }
        if (field == 'OutPrice') {
            let price = ff(data.rows[index].OutPrice, 8.00) / data.currencyValue;
            data.rows[index].Price = ff(price, 8.00);
            let purePrice = data.rows[index].Price - data.rows[index].ShippingPay;
            data.rows[index].PurePrice = ff(purePrice, 8.00);
        }
        if (field != 'OutPrice') {
            data.rows[index].Price = +data.rows[index].PurePrice + data.rows[index].ShippingPay;
            data.rows[index].OutPrice = +data.rows[index].Price * data.currencyValue;
        }
    }
    data.rows[index].PurePrice = ff(data.rows[index].PurePrice, 8.00);
    data.rows[index].ShippingPay = ff(data.rows[index].ShippingPay, 8.00);
    data.rows[index].Price = ff(data.rows[index].Price, 8.00);

    data.rows[index].OutPrice = ff(data.rows[index].OutPrice, 8.00);
    setRowOut(index);
}

function setRowsResults() {
    for (let ii = 0; ii < data.rowsCount; ii++) {
        setRowOut(ii);
    }
}

function setRowOut(index) {
    let stringOutPrice = ff(data.rows[index].OutPrice, 8.00);
    let OutPrice = Math.round(stringOutPrice * 100) / 100;
    let OutCost = data.rows[index].Quantity * OutPrice;
    let OutVAT_Value = OutCost * data.VAT_Rate / 100.0000;
    let OutCost_WITH_VAT = OutCost + OutVAT_Value;
    ////
    data.res[index].OutPrice = ff(OutPrice, 8.00); //h
    data.res[index].OutCost = ff(OutCost, 2.00);
    data.res[index].OutVAT_Value = ff(OutVAT_Value, 2.00);
    data.res[index].OutCost_WITH_VAT = ff(OutCost_WITH_VAT, 2.00);
    // setData(newData);
}

function setData(newData) {
    data = { ...data,
        ...newData
    }
    if (newData.onUpdate == 'appCurrency') {
        for (let index = 0; index < data.rowsCount; index++) {
            data.rows[index].OutPrice = +data.rows[index].Price * data.currencyValue;
            data.rows[index].OutPrice = ff(data.rows[index].OutPrice, 8.00); //h
        }
    }
    if ((newData.onUpdate == 'inputDiscountValue') || (newData.onUpdate == 'inputQuantity')) {
        let discountValuePerRow = 0;
        if (data.rowsCount > 0) {
            discountValuePerRow = data.discountVal / data.rowsCount;
            for (let index = 0; index < data.rowsCount; index++) {
                let shippingPay_i = 0;
                if ((discountValuePerRow != 0) && (data.rows[index].Quantity) > 0) {
                    shippingPay_i = discountValuePerRow / data.rows[index].Quantity;
                }
                data.rows[index].ShippingPay = +shippingPay_i;
                data.rows[index].Price = +data.rows[index].PurePrice + +shippingPay_i;

                if (data.rows[index].Price < 0) {
                    data.rows[index].Price = 0.00000000;
                }
                data.rows[index].OutPrice = +data.rows[index].Price * +data.currencyValue;
                data.rows[index].PurePrice = ff(data.rows[index].PurePrice, 8.00);
                data.rows[index].ShippingPay = ff(data.rows[index].ShippingPay, 8.00);
                data.rows[index].Price = ff(data.rows[index].Price, 8.00);
                data.rows[index].OutPrice = ff(data.rows[index].OutPrice, 8.00); //h
            }
        }
    }
    setRowsResults();
    // расчитываем TOTAL
    let totalCost_WITHOUT_VAT = 0;
    let totalVAT_Amount = 0;
    let totalCost_WITH_VAT = 0;
    let totalItemsQuantity = 0;
    for (let index = 0; index < data.rowsCount; index++) {
        totalCost_WITHOUT_VAT += +data.res[index].OutCost;
        totalVAT_Amount += +data.res[index].OutVAT_Value;
        totalCost_WITH_VAT += +data.res[index].OutCost_WITH_VAT;
        totalItemsQuantity += +data.rows[index].Quantity;
    }
    results = {
        Total: {
            "rowsCount": +data.rowsCount,
            "totalItemsQuantity": +totalItemsQuantity,
            "totalCost_WITHOUT_VAT": ff(totalCost_WITHOUT_VAT, 2.00),
            "totalVAT_Amount": ff(totalVAT_Amount, 2.00),
            "totalCost_WITH_VAT": ff(totalCost_WITH_VAT, 2.00)
        },
        Strings: {
            "rowsCount": integerToWords(data.rowsCount),
            "totalItemsQuantity": integerToWords(totalItemsQuantity),
            "totalCost_WITHOUT_VAT": number_to_sumstring(totalCost_WITHOUT_VAT, data.CID),
            "totalVAT_Amount": number_to_sumstring(totalVAT_Amount, data.CID),
            "totalCost_WITH_VAT": number_to_sumstring(totalCost_WITH_VAT, data.CID),
        }
    }
}
export {
    rowsCount,
    initData,
    getData,
    setRowValue,
    setRowsResults,
    getRes,
    getResults,
    setData,
};