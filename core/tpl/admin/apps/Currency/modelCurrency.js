/*model.js*/
/*
    математическая модель валюты
*/
const controlResultCurrencyValue = document.querySelector('#resultCurrencyValue');

let data = {
    currencyID: 1,
    currencyName: 'Base',
    currencyRate: 1.0000,
    currencyISO3: 'USD',
    VAT_Rate: 20.00,
    hasVATIncluded: 0,
    precision: 4,
    discountPercent: 0
};
let results = {
    CID: data.currencyID,
};

function getData() {
    return { ...data
    }
}

function getResults() {
    return { ...results
    };
}

function setData(newData) {

    data = { ...data,
        ...newData
    }

    // расчёт курса валюты

    let res = +data.currencyRate

    if (+data.hasVATIncluded == 1) {
        res = +data.currencyRate
    } else {
        res = +data.currencyRate * ((+data.VAT_Rate + 100.0000) / 100.0000)
    }

    if (data.discountPercent != 0) {
        res = res * ((100.0000 - data.discountPercent) / 100.0000)
    }

    results = {
        // "data.currencyRate":data.currencyRate,
        currencyRate: data.currencyRate,
        currencyValue: ff((res / (+data.VAT_Rate + 100.0000) * 100.0000), 4),
        currencyValueWithVAT: ff(res, 4),
        CID: +data.currencyID,
        ISO3: data.currencyISO3,
        VAT_Rate: data.VAT_Rate,
        hasVATIncluded: +data.hasVATIncluded,
        discountPercent: data.discountPercent,
        currencyRateFloat: +data.currencyRate,
        currencyValueFloat: res / (+data.VAT_Rate + 100.0000) * 100.0000,
        currencyValueWithVATFloat: +res,
    };
}
export {
    getData,
    setData,
    getResults
}
// gfhsrtrf yew5r