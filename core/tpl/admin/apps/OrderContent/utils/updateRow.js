/*updateModelOrderContent.js*/

// функция генерирует событие updateCurrencyForm и посылает параметры как detail

function updateRow(element, data) {
  console.log("data.onUpdate disp event ",data.onUpdate)
    element.dispatchEvent(
		new CustomEvent('eventUpdateRowValue', {
			bubbles: true,
			detail: {...data}
		})
	);
}

export default updateRow;