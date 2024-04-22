import updateModel from './utils/updateModelOrderContent.js'
let spansNamesElements = document.querySelectorAll('span.content[data-control="Name"]');
const spanNames = Array.from(spansNamesElements);
let rowsCount = +spanNames.length;
const modalOpenButtons = document.querySelectorAll('button[data-bs-target="#modalNameItemUnit"]');
const meModal = document.querySelector('#modalOrderContentItem');
const objModel = new bootstrap.Modal('#modalOrderContentItem', {
    // keyboard: false
});
const meItemID = document.querySelector('input#modalItemID');
const meOrderID = document.querySelector('input#modalOrderID');
const meItemName = document.querySelector('textarea#modalItemName');
const meOldName = document.querySelector('textarea#modalOldName');
const meItemUnit = document.querySelector('input#modalItemUnit');
const meItemPriority = document.querySelector('input#modaItemPriority');
const meTargetOrderID = document.querySelector('input#modalTargetOrderID');
let meData = {
    "onUpdate": "modalDialog",
    "itemID": 0,
    "orderID": 0,
    "targetOrderID": 0,
    "itemName": '',
    "oldName": '',
    "itemUnit": '',
    "itemPriority": 0,
};
let tempTable = {
    "Names": [],
};

function init() {
    meModal.addEventListener('show.bs.modal', event => {
        // do something...
        const itemID = event.relatedTarget.dataset.itemid;
        const orderID = event.relatedTarget.dataset.orderid;
        const nameElement = document.querySelector('span.content[data-control="Name"][data-itemID="' + itemID + '"]');
        const name = nameElement.innerHTML;
        const itemUnit = document.querySelector('span.content[data-control="itemUnit"][data-itemID="' + itemID + '"]').innerHTML;
        const itemPriority = document.querySelector('span.content[data-control="itemPriority"][data-itemID="' + itemID + '"]').innerHTML;
        meData = {
            "onUpdate": "modalDialog",
            "itemID": 0,
            "orderID": 0,
            "targetOrderID": 0,
            "itemName": '',
            "oldName": '',
            "itemUnit": '',
            "itemPriority": 0,
        };
        const initData = {
            "itemID": +itemID,
            "orderID": +orderID,
            "targetOrderID": +orderID,
            "itemName": name,
            "oldName": name,
            "itemUnit": itemUnit,
            "itemPriority": +itemPriority,
        }
        setHtml(initData);
        setData(initData);
    });
    setupNames();
    return tempTable;
}

function getHtml() {
    let itemID = meItemID.value;
    let orderID = meOrderID.value;
    let targetOrderID = meTargetOrderID.value;
    let itemName = meItemName.value;
    let oldName = meOldName.value;
    let itemUnit = meItemUnit.value;
    let itemPriority = meItemPriority.value;
    const d = {
        "itemID": +itemID,
        "orderID": +orderID,
        "targetOrderID": +targetOrderID,
        "itemName": itemName,
        "oldName": oldName,
        "itemUnit": itemUnit,
        "itemPriority": +itemPriority,
    }
    return d;
}

function setHtml(d) {

    console.table(d)

    d['itemName'] = htmlspecialchars_decode(d['itemName']);
    d['oldName'] = htmlspecialchars_decode(d['oldName']);
    d['itemUnit'] = htmlspecialchars_decode(d['itemUnit']);

    meItemID.value = d['itemID'];
    meOrderID.value = d['orderID'];
    meItemName.value = d['itemName'];
    meOldName.value = d['oldName'];
    meItemUnit.value = d['itemUnit'];
    meItemPriority.value = d['itemPriority'];
    //
    document.querySelector('small#modal_itemID').innerHTML = ' itemID: ' + d['itemID'];
    document.querySelector('span.content[data-control="Name"][data-itemID="' + d['itemID'] + '"]').innerHTML = d['itemName'];
    document.querySelector('span.content[data-control="itemUnit"][data-itemID="' + d['itemID'] + '"]').innerHTML = d['itemUnit'];
    document.querySelector('span.content[data-control="itemPriority"][data-itemID="' + d['itemID'] + '"]').innerHTML = d['itemPriority'];
}

function getData(newData) {
    let htmlData = getHtml();
    dropNames();
    setHtml(htmlData);
    setData(htmlData);
    setupNames();
    return { ...meData
    };
}

function dropNames() {
    tempTable = {
        "Names": [],
    };
}

function setupNames() {
    for (let ii = 0; ii < rowsCount; ii++) {
        let el = spanNames[ii];
        tempTable.Names.push({
            "index": +ii,
            "itemID": +el.dataset.itemid,
            "name": spanNames[ii].innerHTML,
        });
    }
    // console.table( "Names", tempTable.Names )
}

function setData(newData) {
    meData = { ...meData,
        ...newData
    };
}
export {
    init,
    getData,
    setData,
    modalOpenButtons,
    objModel
};