let isCompanyChanged
let isNewCompany
let titleNewCompany
let idLastCompany
let idInvoiceCompany
let htmlCompanyVariants
let companyFieldsChanged
let pm_clear
let pm_cors_unp
let pm_fields
let pm_newcompany
let pm_readonly
let pm_reload
let pm_save
let pm_unp
let yes_selest
let FIELDS_TAB_BUTTON
let TEXT_TAB_BUTTON
let SELECT_TAB_BUTTON
let $MySelectPicker
let mySpinner

function $animateScrollToAddonForm() {
    // $('html, body').animate({
    //     scrollTop: $('#payment_addon_html').offset().top - 180
    // }, 1000, 'swing')
    return false;
}
async function initPaymentAddon(addonHtml) {
    // console.log(addonHtml)
    payment_addon_html.innerHTML = addonHtml
    // mySpinner = document.getElementById('mySpinner')
    isCompanyChanged = document.getElementById('isCompanyChanged')
    isNewCompany = document.getElementById('isNewCompany')
    titleNewCompany = document.getElementById('titleNewCompany')
    idLastCompany = document.getElementById('last_order_companyID')
    idInvoiceCompany = document.getElementById('idInvoiceCompany')
    htmlCompanyVariants = document.getElementById('DivCompanyVariants')
    htmlCompanyPeview = document.getElementById('companyPeview')
    companyFieldsChanged = document.getElementById('companyFieldsChanged')
    pm_clear = document.getElementById('pm_clear')
    pm_cors_unp = document.getElementById('pm_cors_unp')
    pm_fields = document.querySelectorAll('input[name^=pm_],textarea[name^=pm_]')
    pm_newcompany = document.getElementById('pm_newcompany')
    pm_readonly = document.getElementById('readonly_switch')
    pm_reload = document.getElementById('pm_reload')
    pm_save = document.getElementById('pm_save')
    pm_unp = document.getElementById('pm_unp')
    yes_selest = document.getElementById('yes_selest')
    FIELDS_TAB_BUTTON = document.querySelector('#myTabNav button[data-bs-target="#fields_tab"]')
    TEXT_TAB_BUTTON = document.querySelector('#myTabNav button[data-bs-target="#text_tab"]')
    SELECT_TAB_BUTTON = document.querySelector('#myTabNav button[data-bs-target="#select_tab"]')
    $MySelectPicker = $('select.selectpicker[name=selectCompany2]').selectpicker({
        title: "ddddd"
    }).selectpicker('render');
    // $('select.selectpicker[name=selectCompany2]').selectpicker('refresh');
    if (addonHtml && pm_readonly) {
        mySpinner = document.getElementById('mySpinner')[0]
        pm_readonly.addEventListener('change', (event) => {
            toggleReadonlySwitcher()
        })
        pm_save.addEventListener('click', async () => {
            asyncSaveCompany()
        })
        if (pm_cors_unp) {
            pm_cors_unp.addEventListener('click', async () => {
                let ajaxurl = 'https://www.portal.nalog.gov.by/grp/getData?unp=' + pm_unp.value + '&charset=UTF-8&type=json'
                let response = await fetch(ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json;charset=utf-8',
                        'Access-Control-Allow-Origin': '*',
                        'Access-Control-Allow-Credentials': true
                    },
                    mode: 'no-cors'
                })
                if (response) {
                    console.log(response)
                    alert("F12")
                }
            })
        }
        if (pm_newcompany) {
            pm_newcompany.addEventListener('click', () => {
                return onCreateNewCompany();
            })
        }
        pm_clear.addEventListener('click', () => {
            _clearAllPMFields()
        })
        pm_reload.addEventListener('click', async () => {
            let last_companyID = idLastCompany.value
            let $selector_companyID
            if ((yes_selest) && (yes_selest.value == 1)) {
                $selector_companyID = $('select.selectpicker[name=selectCompany2]').selectpicker().val()
            }
            if (typeof $selector_companyID === 'undefined') {
                companyID = last_companyID
            } else {
                companyID = $selector_companyID
            }
            console.log(companyID, "pm_reload")
            if (+companyID >= 1) {
                await loadCompany(companyID)
                ShowInfo('загрузка данных :: ' + companyID)
            }
        })
        document.querySelector('[name="pm_unp"]').addEventListener('input', function(event) {
            event.target.value = event.target.value.replace(/[^0-9\s]/g, '')
        })
        document.querySelector('[name="pm_okpo"]').addEventListener('input', function(event) {
            event.target.value = event.target.value.replace(/[^0-9\s]/g, '')
        })
        $MySelectPicker.on('changed.bs.select', function(e, clickedIndex, isSelected, previousValue) {
            let newVal = $MySelectPicker.val()
            if (newVal == 0) {
                onCreateNewCompany();
                let tab = new bootstrap.Tab(FIELDS_TAB_BUTTON)
                tab.show()
                _clearAllPMFields()
            } else {
                console.log(newVal)
                loadCompany(newVal)
            }
        });
        /*
            $( 'input[name^=pm_],textarea[name^=pm_]' ).on( 'change', function( event ) {
                let index = this.id.replace( 'pm_', '' )
                isCompanyChanged.value = 1
                return markFieldAsChanged( index, this.value )
            } ).on( 'blur', function() {
                return companyFieldsChanged.value = JSON.stringify( FieldsChangedByUser )
            } )
        */
        [].forEach.call(pm_fields, function(el) {
            el.addEventListener('change', function() {
                let index = this.id.replace('pm_', '')
                isCompanyChanged.value = 1
                return markFieldAsChanged(index, this.value)
            }, false)
            el.addEventListener('blur', function() {
                return companyFieldsChanged.value = JSON.stringify(FieldsChangedByUser)
            }, false)
        });
        companyFieldsChanged.value = null
        const myBootstrapTabs = __initBootstrapTabs(FIELDS_TAB_BUTTON, TEXT_TAB_BUTTON, SELECT_TAB_BUTTON)
        if (idLastCompany && idLastCompany.value > 1) {
            let lastCompany_ajax = await loadCompany(idLastCompany.value)
        } else {
            $animateScrollToAddonForm()
            ShowInfo('Заполните, пожалуйста реквизиты организации, для которой выставляется счёт', 'Данные для выставления счета', 1)
        }
    }
}

function generateCompanyVariantsHtml(data, countData) {
    htmlCompanyVariants.innerHTML = ''
    if (data != null) {
        htmlCompanyVariants.innerHTML = `<div class="alert alert-primary my-1" role="alert">Для УНП <strong >${data[0].company_unp}</strong> есть <strong>${countData}</strong> варианта(-ов) реквизитов</div>`
        let Buttons = data.map((item) => {
            return (item.selected === "selected") ? `<a type="button" class="btn btn-secondary active" onclick="return loadCompany(${item.companyID})" aria-current="page"> Вариант от ${item.update_time}</a>` : `<a type="button" class="btn btn-outline-secondary " onclick="return loadCompany(${item.companyID})"> Вариант от ${item.update_time}</a>`
        })
        Buttons.push('<a type="button" class="btn btn-danger text-end px-5" id="pm_newcompany" onclick="onCreateNewCompany();"> Новый вариант </a>')
        let variantHtml = ''
        Buttons.forEach(item => variantHtml += item)
        htmlCompanyVariants.innerHTML += '<div class="btn-group btn-group-sm" role="group">' + variantHtml
        htmlCompanyVariants.innerHTML += '</div>'
    }
    return htmlCompanyVariants.innerHTML
}

function onCreateNewCompany() {
    htmlCompanyVariants.innerHTML = ''
    htmlCompanyPeview.innerHTML = ''
    $MySelectPicker.val(0)
    if (isCompanyChanged && isCompanyChanged.value == "0") {
        _clearAllPMFields()
    }
    idLastCompany.value = 0
    idInvoiceCompany.value = 0
    isNewCompany.value = 1
    titleNewCompany.innerHTML = 'Новая организация'
    toggleReadonlySwitcher(0)
    isCompanyChanged.value = 0
    companyFieldsChanged.value = null
}

function __initBootstrapTabs(fields_tab, text_tab, select_tab) {
    const form_fields = document.querySelector('form#formCompany_Fields')
    const form_text = document.querySelector('form#formCompany_TextArea')
    $('select.selectpicker[name=selectCompany2]').selectpicker('refresh');
    fields_tab.addEventListener('show.bs.tab', async () => {
        form_fields.classList.add('needs-validation')
        form_fields.removeAttribute('novalidate')
    })
    fields_tab.addEventListener('hide.bs.tab', async () => {
        form_fields.classList.remove('needs-validation')
        form_fields.setAttribute('novalidate', 'novalidate')
    })
    text_tab.addEventListener('show.bs.tab', async () => {
        form_text.classList.add('needs-validation')
        form_text.removeAttribute('novalidate')
    })
    text_tab.addEventListener('hide.bs.tab', async () => {
        form_text.classList.remove('needs-validation')
        form_text.setAttribute('novalidate', 'novalidate')
    })
    if (yes_selest && (yes_selest.value == 1)) {
        select_tab.addEventListener('show.bs.tab', async () => {
            // $('select.selectpicker[name=selectCompany2]')
            // .selectpicker({title: 'do New Title'})
            // .selectpicker('render');
            //чтобы сразу отобразить опции
            const spButton = document.querySelector('button[data-id="selectCompany2"]')
            spButton.addEventListener('click', (event) => {
                $('select.selectpicker[name=selectCompany2]').selectpicker('refresh')
                console.log(spButton)
            })
            form_fields.setAttribute('novalidate', 'novalidate')
            form_text.setAttribute('novalidate', 'novalidate')
        })
    }
}

function repairSelectpickerTitle() {
    var selectpicker = $("select.selectpicker[name=selectCompany2]");
    selectpicker.selectpicker();
    selectpicker.selectpicker({
        title: 'Выберите компанию'
    }).selectpicker('render');
    html = '';
    selectpicker.html(html);
}

function toggleReadonlySwitcher(setTo) {
    pm_clear.classList.toggle('disabled')
    pm_clear.classList.toggle('visually-hidden')
    pm_save.classList.toggle('disabled')
    pm_save.classList.toggle('visually-hidden')
    // преключение состояния
    if (typeof setTo == 'undefined') {
        if (pm_readonly.hasAttribute('checked')) {
            pm_readonly.removeAttribute('checked')
            setTo = 0
        } else {
            pm_readonly.setAttribute('checked', 'checked')
            setTo = 1
        }
    }
    // ..обработка
    if (setTo == 1) {
        pm_readonly.setAttribute('checked', 'checked')
        pm_clear.classList.add('disabled', 'visually-hidden')
        pm_save.classList.add('disabled', 'visually-hidden')
    }
    if (setTo == 0) {
        pm_readonly.removeAttribute('checked')
        pm_clear.classList.remove('disabled', 'visually-hidden')
        pm_save.classList.remove('disabled', 'visually-hidden')
    }
    if (pm_readonly.hasAttribute('checked')) {
        document.querySelector('label[for="readonly_switch"]').innerHTML = '<i class="bi bi-shield-lock-fill text-primary"></i>'
    } else {
        document.querySelector('label[for="readonly_switch"]').innerHTML = '<i class="bi bi-shield-slash text-muted"></i>'
    }
    Array.prototype.slice.call(pm_fields).forEach(function(field) {
        switchReadonly(field, setTo)
    })
}

function _clearAllPMFields() {
    Array.prototype.slice.call(pm_fields).forEach(function(field) {
        field.value = ''
    })
}

function fillCompanyData(data) {
    let companyID = htmlspecialchars_decode(data['companyID'])
    let company_name = htmlspecialchars_decode(data['company_name'])
    let company_unp = htmlspecialchars_decode(data['company_unp'])
    let company_okpo = htmlspecialchars_decode(data['company_okpo'])
    let company_adress = htmlspecialchars_decode(data['company_adress'])
    let company_title = htmlspecialchars_decode(data['company_title'])
    let company_bank = htmlspecialchars_decode(data['company_bank'])
    let company_contacts = htmlspecialchars_decode(data['company_contacts'])
    let company_director = htmlspecialchars_decode(data['company_director'])
    let company_director_nominative = htmlspecialchars_decode(data['company_director_nominative'])
    let company_director_genitive = htmlspecialchars_decode(data['company_director_genitive'])
    let company_director_reason = htmlspecialchars_decode(data['company_director_reason'])
    document.querySelector('textarea[name=pm_name]').value = company_name
    document.querySelector('input[name=pm_unp]').value = company_unp
    document.querySelector('input[name=pm_okpo]').value = company_okpo
    document.querySelector('textarea[name=pm_adress]').value = company_adress
    document.querySelector('textarea[name=pm_title]').value = company_title
    document.querySelector('textarea[name=pm_bank]').value = company_bank
    document.querySelector('textarea[name=pm_contacts]').value = company_contacts
    document.querySelector('input[name=pm_director_nominative]').value = company_director_nominative
    document.querySelector('input[name=pm_director_genitive]').value = company_director_genitive
    document.querySelector('input[name=pm_director_reason]').value = company_director_reason
    titleNewCompany.innerHTML = `${company_name} / УНП ${company_unp} <sub class="opacity-25"> ${companyID}</sub>`
    idInvoiceCompany.value = companyID
    isCompanyChanged.value = 0
    isNewCompany.value = 0
    toggleReadonlySwitcher(+data["read_only"])
}

function previewCompanyData(data) {
    htmlCompanyPeview.innerHTML = ''
    let html = ''
    html += '<ul class="list-group list-group-flush">'
    html += '<li class="list-group-item fw-bold">'
    html += ' ' + data.company_name + ' / УНП ' + data.company_unp + ' <sub class="opacity-25">id:' + data.companyID + '</sub>'
    html += '</li">'
    html += '<li class="list-group-item">'
    html += '<strong>Адрес:</strong> ' + data.company_adress
    html += '</li">'
    html += '<li class="list-group-item">'
    html += '<strong>Банк:</strong> ' + data.company_bank
    html += '</li">'
    html += '<li class="list-group-item">'
    html += '<strong>Контакты:</strong> ' + data.company_contacts
    html += '</li">'
    html += '<li class="list-group-item">'
    html += '<strong>Руководитель:</strong> ' + data.company_director_nominative + '<br>'
    html += '<strong>действует на основании :</strong> ' + data.company_director_reason + '<br>'
    html += '</li">'
    html += '<li class="list-group-item">'
    html += '<strong>Дата последнего изменения:</strong> ' + data.update_time
    html += '</li">'
    html += '</ul">'
    htmlCompanyPeview.innerHTML = html
}

function markFieldAsChanged(index, newValue) {
    companyFieldsChanged.value = null
    if (typeof index !== 'undefined' && (index !== 'unp')) {
        FieldsChangedByUser[index] = newValue
    }
}
async function loadCompany(companyID) {
    let ajaxurl = '/index.php?shopping_cart=yes&loadCompanyData=yes'
    let reqData = {
        'companyID_to_load': +companyID
    }
    let json_data = await doMyAjax(ajaxurl, reqData, reqType = 'POST')
    if (json_data) {
        htmlCompanyVariants.innerHTML = ''
        company_data = JSON.parse(json_data)
        let variants_data = company_data.variants
        if (isNaN(company_data.company_unp)) {
            onCreateNewCompany()
            // ShowWarning('Реквизиты Такой Организации  не найдены в нашей БД id:' + companyID, 'ошибка')
            ShowInfo('Заполните, пожалуйста, Реквизиты Вашей Организации', 'Данные для выставления счета', 0)
        } else {
            fillCompanyData(company_data) // заполняем поля формы данными из БД
            previewCompanyData(company_data) // выводим превью для селекта
            idLastCompany.value = companyID
            if (variants_data && (variants_data.length > 1)) {
                generateCompanyVariantsHtml(variants_data, company_data.variants_count) // ВАРИАНТЫ УНП
            } else {
                generateCompanyVariantsHtml()
            }
            ShowInfo('загружены реквизиты :' + company_data.company_name + companyID, 'Данные для выставления счета', 0)
        }
    }
}
async function asyncSaveCompany() {
    const activeTab = document.querySelector('div#myTabContent div.tab-pane.active.show').id
    let pm_fields = {}
    const fields = document.querySelectorAll('div#' + activeTab + ' input[name^=pm_],div#' + activeTab + ' textarea[name^=pm_]')
    Array.prototype.slice.call(fields).forEach(function(field) {
        pm_fields[field.name] = field.value
    })
    const companyID = +idInvoiceCompany.value
    const ajaxurl = '/index.php?shopping_cart=yes&saveCompanyData=' + companyID
    const create_new_company = +isNewCompany.value
    const filds_is_changed = companyFieldsChanged.value
    const company_is_changed = +isCompanyChanged.value
    const readonly_pm_readonly_state = +document.getElementById('readonly_switch').hasAttribute('checked')
    if (+company_is_changed == 1) {
        let reqData = {}
        reqData = {
            'saveCompanyData': 'yes',
            'activeTab': activeTab,
            'companyID': +companyID,
            'companyFieldsChanged': filds_is_changed,
            'pm_fields': pm_fields,
            'isCompanyChanged': company_is_changed,
            'readonly_pm_readonly_state': readonly_pm_readonly_state,
            'isNewCompany': create_new_company,
            // 'FieldsChangedByUser':FieldsChangedByUser
        }
        let json_data = await doMyAjax(ajaxurl, reqData, reqType = 'POST')
        if (json_data) {
            answer = JSON.parse(json_data)
            if (answer && !answer.ERROR) {
                idInvoiceCompany.value = +answer.companyID
                companyFieldsChanged.value = null
                isCompanyChanged.value = 0
                ShowSuccess('сохранены реквизиты :' + answer.companyID, answer.mysql_info)
                isNewCompany.value = 0
                mySpinner.classList.add('visually-hidden')
                if (create_new_company) {
                    return loadCompany(+answer.companyID)
                } else return +answer.companyID
            } else {
                pm_unp.classList.add('is-invalid')
                if (pm_unp.value == '') pm_unp.value = +answer.error_unp
                if (activeTab = 'text_tab') document.getElementById('pm_userdata').classList.add('is-invalid')
                mySpinner.classList.add('visually-hidden')
                ShowError('реквизиты не сохранены' + answer.companyID, answer.ERROR)
                return false
            }
        }
    } else {
        ShowInfo('реквизиты не менялись ' + companyID)
    }
}