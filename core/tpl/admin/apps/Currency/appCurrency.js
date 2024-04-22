import * as Currency from "./modelCurrency.js";
import {
    default as initCurrencyForm,
    initialParams,
    updateCurrencyView
} from "./viewCurrency.js";

const app = function(name) {
    // alert(        import.meta.url ); // ссылка на html страницу для встроенного скрипта
    // выставляем в модель данные валюты из заказа (из выбранного конфига валюты в selectbox)
    const preloadParams = initialParams();
    Currency.setData(preloadParams);

    // обновляем модель
    const getData = Currency.getData;
    // оживляем форму Валюты
    const currencyForm = initCurrencyForm(getData);

    // СЛУШАЕМ ВНУТРЕННЕ событие
    document.addEventListener('updateCurrencyForm', (event) => {
        Currency.setData(event.detail);
        const data = Currency.getData();
        const results = Currency.getResults();
        // Update results block
        updateCurrencyView(results);
        generateExternalEvent(document, results);
    });



    // ГЕНЕРІРУЕМ ВНЕШНЕЕ СОБЫТІЕ
    function generateExternalEvent(element, data) {
        element.dispatchEvent(
            new CustomEvent('eventCurrencyValueCnanged', {
                bubbles: true,
                detail: { ...data
                }
            })
        );
    }

    return Currency.getResults();
}

// window.onload = app;
export default app