// viewCurrency.js
import updateModel from './utils/updateModelCurrency.js';
const controlCurrencySelect = document.querySelector('select[data-control="selectCurrency"]');
const controlCurrencyRate = document.querySelector('input[data-control="targetCurrencyRate"]');
const controlVAT_Rate = document.querySelector('input#VAT_Rate');
const controlHasVATIncluded = document.querySelector('input#hasVATIncluded');
/*  CLEAVES currency*/
const cleaveCurrencyRate = new Cleave(controlCurrencyRate, settingsCurrency);
const cleaveVAT_Rate = new Cleave(controlVAT_Rate, settingsCurrency);
/*Discount*/
const inputDiscountPercent = document.querySelector('input[data-control="discountPercent"]');
const radioDiscountPercent = document.querySelectorAll('input[type=radio][name="options-discountPercent"]');
const cleaveDiscountPercent = new Cleave(inputDiscountPercent, settingsCurrency);
/*расчетный курс*/
const controlResultCurrencyValue = document.querySelector('#resultCurrencyValue');
const controlResultCurrencyValueWithVAT = document.querySelector('#resultCurrencyValueWithVAT');
const cleaveResultCurrencyValue = new Cleave(controlResultCurrencyValue, settingsCurrency);
const cleaveResultCurrencyValueWithVAT = new Cleave(controlResultCurrencyValueWithVAT, settingsCurrency);
const spanISO3 = document.querySelectorAll('span.BYN');

function updateCurrencyView(results) {

    cleaveResultCurrencyValue.setRawValue(results.currencyValue);
    cleaveResultCurrencyValueWithVAT.setRawValue(results.currencyValueWithVAT);
    cleaveCurrencyRate.setRawValue(results.currencyRate);
    cleaveVAT_Rate.setRawValue(results.VAT_Rate);
    // устанавливаем чекбокс НДС
    if (results.hasVATIncluded == 1) {
        controlHasVATIncluded.setAttribute('checked', 'checked');
        controlHasVATIncluded.checked = true;
    } else {
        controlHasVATIncluded.removeAttribute('checked');
        controlHasVATIncluded.checked = false;
    }



return true;
    // controlResultCurrencyValue.dispatchEvent(new CustomEvent('eventCurrencyValueCnanged', {
    //     bubbles: true,
    //     detail: { ...results, ...{'resUpd':"currency"}
    //     }
    // }));

}


const initialParams = function getInitialParams() {

    // обновляем объект в соответствии с загруженной страницей TPL
    let selControl = controlCurrencySelect;
    // индекс выбранного option
    let sel = selControl.selectedIndex;
    let options = selControl.options;
    let newCID = options[sel].value;
    let newCurrencyName = (options[sel].attributes['data-Name'].value);
    let newCurrencyRate = (options[sel].attributes['data-currency-value'].value);
    let _hasVATIncluded = (options[sel].attributes['data-has-vat'].value);
    let newISO3 = (options[sel].attributes['data-currency-iso3'].value);
    let newPrecision = (options[sel].attributes['data-currency-precision'].value);
    // устанавливаем чекбокс НДС
    if (_hasVATIncluded == 1) {
        controlHasVATIncluded.setAttribute('checked', 'checked');
        controlHasVATIncluded.checked = true;
    } else {
        controlHasVATIncluded.removeAttribute('checked');
        controlHasVATIncluded.checked = false;
    }
    // меняем ISO_3 валюты в span.BYN
    spanISO3.forEach(function(el) {
        el.innerHTML = newISO3;
    })
    // Обновить модель
    let params = {
        currencyID: +newCID,
        currencyName: newCurrencyName,
        currencyRate: cleaveCurrencyRate.getRawValue(),
        currencyISO3: newISO3,
        VAT_Rate: cleaveVAT_Rate.getRawValue(),
        hasVATIncluded: +controlHasVATIncluded.checked,
        precision: newPrecision,
        discountPercent: cleaveDiscountPercent.getRawValue(),
        onUpdate: 'initialParams'
    }
    return params;
}

function init(getData) {
    // главная функция инициализирует все графические элементы и вешаем на них события
    const data = getData();
    // первоначальная установка
    cleaveCurrencyRate.setRawValue(data.currencyRate);
    cleaveVAT_Rate.setRawValue(data.VAT_Rate);

    cleaveDiscountPercent.setRawValue(data.discountPercent);

    function __discountPercentOnChange() {
        const _value = +cleaveDiscountPercent.getRawValue();
        const red = +radioDiscountPercent[0].checked;
        let value = _value;
        if (red == 1) {
            value = -1 * _value;
            radioDiscountPercent[0].checked = true;
            radioDiscountPercent[1].checked = false;
        } else {
            value = 1 * _value;
            radioDiscountPercent[0].checked = false;
            radioDiscountPercent[1].checked = true;
        }
        if ((+_value >= 100) && radioDiscountPercent[1].checked) {
            value = 99.5; //нельзя скидку больше 100%
            cleaveDiscountPercent.setRawValue(value);
        }
        // Обновить модель
        updateModel(inputDiscountPercent, {
            discountPercent: value,
            onUpdate: 'inputDiscountPercent'
        });
    }


    // клив Процента
    inputDiscountPercent.addEventListener('change', __discountPercentOnChange);
    radioDiscountPercent[0].addEventListener('click', __discountPercentOnChange);
    radioDiscountPercent[1].addEventListener('click', __discountPercentOnChange);

    // прописываем события
    // select box
    controlCurrencySelect.onchange = () => {
        let selControl = controlCurrencySelect;
        // индекс выбранного option
        let sel = selControl.selectedIndex;
        let options = selControl.options;
        let newCID = options[sel].value;
        let newCurrencyName = (options[sel].attributes['data-Name'].value);
        let newCurrencyRate = options[sel].attributes['data-currency-value'].value;
        let newHasVATIncluded = options[sel].attributes['data-has-vat'].value;
        let newISO3 = options[sel].attributes['data-currency-iso3'].value;
        let newPrecision = options[sel].attributes['data-currency-precision'].value;
        // Обновить модель
        let newParams = {
            currencyID: +newCID,
            currencyName: newCurrencyName,
            currencyRate: ff(newCurrencyRate,4),
            currencyISO3: newISO3,
            VAT_Rate: cleaveVAT_Rate.getRawValue(),
            hasVATIncluded: newHasVATIncluded,
            precision: newPrecision,
            onUpdate: 'controlCurrencySelect'
        }

        updateModel(selControl, newParams);
        // меняем ISO_3 валюты в span.BYN
        spanISO3.forEach(function(el) {
            el.innerHTML = newISO3;
        })
    }
    // переключатель НДС
    controlHasVATIncluded.onchange = () => {
        let _hasVATIncluded = 0;
        /* && controlHasVATIncluded.hasAttribute('checked')*/
        if (controlHasVATIncluded.checked) {
            _hasVATIncluded = 1;
        }
        // Обновить модель
        let newParams = {
            hasVATIncluded: _hasVATIncluded,
            onUpdate: 'controlHasVATIncluded'
        }
        updateModel(controlHasVATIncluded, newParams);
    }
    // клив курса валюты
    controlCurrencyRate.addEventListener('change', function() {
        const value = +cleaveCurrencyRate.getRawValue();
        // Проверка на мин и макс цену
        if (value <= 0) {
            controlCurrencyRate.classList.add('is-invalid');
            controlResultCurrencyValue.classList.add('is-invalid');
        }
        if (value > 0) {
            controlCurrencyRate.classList.remove('is-invalid');
            controlResultCurrencyValue.classList.remove('is-invalid');
        }
        // Обновить модель
        updateModel(controlCurrencyRate, {
            currencyRate: value,
            onUpdate: 'cleaveCurrencyRate'
        });
    });
    controlCurrencyRate.addEventListener('keyup', function(event) {
        let elementID = event.target.id;
        let x = event.key;
        if (x == 'Escape') {
            event.target.value = 1.0000;
        }
    });
    // клив НДС
    controlVAT_Rate.addEventListener('change', function() {
        const value = cleaveVAT_Rate.getRawValue();
        // Обновить модель
        updateModel(controlVAT_Rate, {
            VAT_Rate: value,
            onUpdate: 'controlVAT_Rate'
        });
    });

    return {
        // cleaveCurrencyRate,
        // cleaveVAT_Rate,
        // cleaveDiscountPercent,
        // cleaveResultCurrencyValue
    }
}
export default init
export {
    initialParams,
    updateCurrencyView
}