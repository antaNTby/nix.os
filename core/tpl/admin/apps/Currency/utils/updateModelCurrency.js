/*updateModelCurrency.js*/

// функция генерирует событие updateCurrencyForm и посылает параметры как detail
function updateModel(element, data) {
    element.dispatchEvent(
		new CustomEvent('updateCurrencyForm', {
			bubbles: true,
			detail: {...data}
		})
	);
}


export default updateModel