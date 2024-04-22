/*updateModelOrderContent.js*/

// функция генерирует событие updateCurrencyForm и посылает параметры как detail

function updateModel(element, data) {
  console.table("updateModel",data)
    element.dispatchEvent(
		new CustomEvent('updateOrderContentForm', {
			bubbles: true,
			detail: {...data}
		})
	);
}

export default updateModel;