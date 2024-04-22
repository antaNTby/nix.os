/*appAdminCompanyEdit*/
import * as bsToast from "../Toasts/appToasts.js";
const app = function() {
    const myToast = bsToast;
    // console.log(myToast)
    const btn_clone = document.getElementById( 'btn_clone' );
    const btn_save = document.getElementById( 'btn_save' );
    const btn_AddCompany = document.querySelector( 'button#btn_AddCompany' );
    const btn_LinkCompany = document.querySelector( 'button#btn_LinkCompany' );
    const btn_cancel = document.getElementById( 'btn_cancel' );
    const btn_clearAll = document.getElementById( 'btn_clearAll' );
    const btn_cors = document.querySelector( 'button#btn_cors' );
    const btn_corsEl = document.querySelector( '#btn_cors' );
    const btn_egr = document.querySelector( 'a[name ="bt_egr"]' );
    const btn_setDumpData = document.getElementById( 'btn_setDumpData' );
    const btn_setUserData = document.getElementById( 'btn_setUserData' );
    const btn_validate = document.getElementById( 'btn_validate' );
    const btn_ExportCompany = document.getElementById( 'btn_ExportCompany' );
    const btn_ImportCompany = document.getElementById( 'btn_ImportCompany' );
    const fileToUpload = document.getElementById( 'fileToUpload' );
    const btn_SelectFile = document.getElementById( 'btn_SelectFile' );
    const corsСontainer = document.querySelector( '#corsContainer' );
    const current_dpt = JSgetParameterByName( 'dpt' );
    const current_sub = JSgetParameterByName( 'sub' )
    const data_fieldset = document.querySelector( 'fieldset#data_fieldset' );
    const form = document.getElementById( "mainForm" );
    const formDataToolbar = document.querySelector( '#formDataToolbar' );
    const htmlCompanyVariants = document.querySelector( '#htmlCompanyVariants' );
    const pageTitleEl = document.querySelector( 'div#page-title' );
    const pm_admindataEl = document.querySelector( '#pm_admindata' );
    const pm_adressEl = document.querySelector( '#pm_adress' );
    const pm_bankEl = document.querySelector( '#pm_bank' );
    const pm_contactsEl = document.querySelector( '#pm_contacts' );
    const pm_director_genitiveEl = document.querySelector( '#pm_director_genitive' );
    const pm_director_nominativeEl = document.querySelector( '#pm_director_nominative' );
    const pm_director_reasonEl = document.querySelector( '#pm_director_reason' );
    const pm_emailEl = document.querySelector( '#pm_email' );
    const pm_fieldset = document.querySelector( 'fieldset#pm_fieldset' );
    const pm_nameEl = document.querySelector( '#pm_name' );
    const pm_okpoEl = document.querySelector( '#pm_okpo' );
    const pm_titleEl = document.querySelector( '#pm_title' );
    const pm_unpEl = document.querySelector( '#pm_unp' );
    const pm_userdataEl = document.querySelector( '#pm_userdata' );
    const readonlyLabelEl = document.querySelector( 'label[for="readonly_switch"]' );
    const readonlySwitchEl = document.querySelector( '#readonly_switch' );
    const thisCompanyID = document.querySelector( 'input#thisCompanyID' );
    const thisUNP = document.querySelector( 'input#thisUNP' );
    const updateTimeEl = document.querySelector( '#update_time' );
    // слияние
    const SampleRadios = document.querySelectorAll( 'input.form-check-input[name="Sample"]' );
    const DeadManChecks = document.querySelectorAll( 'input.form-check-input[name="DeadMan"]' );
    const btn_UniteCompanies = document.getElementById( 'btn_UniteCompanies' );
    const btn_CancelUnite = document.getElementById( 'btn_CancelUnite' );
    const ButtonsKillCompany = document.querySelectorAll( 'button[data-operation="KillCompany"]' );
    const ButtonsExportCompany = document.querySelectorAll( 'button[data-operation="ExportCompany"]' );
    const btnTerminateCompany = document.querySelector( 'button[data-operation="TerminateCompany"]' );
    // console.log(btn_ExportCompany)
    //
    //
    //
    //
    function generateCompanyVariantsHtml( data ) {
        htmlCompanyVariants.innerHTML = '';
        let countData = data.length;
        if ( data != null ) {
            htmlCompanyVariants.innerHTML = `<div class="alert alert-primary my-1" role="alert">Для УНП <strong >${data[0].company_unp}</strong> есть <strong>${countData}</strong> варианта(-ов) реквизитов</div>`;
            let Buttons = data.map( ( item ) => {
                let result = ( item.selected === "selected" ) ? `<a type="button" class="btn btn-secondary active" onclick="return loadCompany(${item.companyID})" aria-current="page"> Вариант от ${item.update_time}</a>` : `<a type="button" class="btn btn-outline-secondary " onclick="return loadCompany(${item.companyID})"> Вариант от ${item.update_time}</a>`;
                return result;
            } );
            Buttons.push( '<a type="button" class="btn btn-danger text-end px-5" id="pm_newcompany" onclick="onCreateNewCompany();"> Новый вариант </a>' );
            let variantHtml = '';
            Buttons.forEach( item => variantHtml += item );
            htmlCompanyVariants.innerHTML += '<div class="btn-group btn-group-sm" role="group">' + variantHtml;
            htmlCompanyVariants.innerHTML += '</div>';
        }
        return htmlCompanyVariants.innerHTML
    }

    function setReadOnlyTo( setTo ) {
        const chkSelector = 'input#checkAsDeadMan_' + thisCompanyID.value.toString();
        const chkDeadMan = document.querySelector( chkSelector );
        const chkDeadManLabel = document.querySelector( chkSelector + ' ~ label' );
        // console.log(chkDeadManLabel, chkSelector + ' ~ label')
        // ..обработка
        if ( setTo == 1 ) {
            if ( chkDeadMan ) {
                chkDeadMan.setAttribute( 'disabled', 'disabled' );
                chkDeadManLabel.innerHTML = ' Защита от записи';
            }
            readonlySwitchEl.setAttribute( 'checked', 'checked' );
            btn_clearAll.classList.add( 'disabled', 'visually-hidden' );
            pm_fieldset.setAttribute( 'disabled', setTo );
            data_fieldset.setAttribute( 'disabled', setTo );
            readonlyLabelEl.innerHTML = '<i class="bi bi-lock-fill text-dark"></i> Защита от записи';
            formDataToolbar.classList.add( 'disabled', 'visually-hidden' );
        }
        //
        if ( setTo == 0 ) {
            if ( chkDeadMan ) {
                chkDeadMan.removeAttribute( 'disabled' );
                chkDeadManLabel.innerHTML = ' Пометить на слияние'
            }
            readonlySwitchEl.removeAttribute( 'checked' );
            btn_clearAll.classList.remove( 'disabled', 'visually-hidden' );
            btn_save.classList.remove( 'disabled', 'visually-hidden' );
            pm_fieldset.removeAttribute( 'disabled' );
            data_fieldset.removeAttribute( 'disabled' );
            readonlyLabelEl.innerHTML = '<i class="bi bi-unlock-fill text-muted"></i> Не защищено';
            formDataToolbar.classList.remove( 'disabled', 'visually-hidden' );
        }
    }

    function _generateUserData() {
        /*   Краткие реквизиты  */
        let str = `${pm_nameEl.value} / УНП ${pm_unpEl.value}
                   ${pm_adressEl.value}
                   ${pm_bankEl.value}
                   ${pm_contactsEl.value}
                   ${pm_director_nominativeEl.value} • ${pm_director_reasonEl.value}`;
        /*   Краткие реквизиты  */
        str = str.replace( / +/g, ' ' ).trim();
        return str;
    }

    function _validate( f ) {
        let result = true;
        let isValid = f.checkValidity();
        f.classList.add( 'was-validated' );
        result = isValid;
        delay( 6000 ).then( () => _clearValidate( form ) );
        return result;
    }

    function _clearValidate( f ) {
        f.classList.remove( 'was-validated' );
        f.classList.add( 'start-validation' );
        f.classList.add( 'need-validation' );
    }

    function _onchangeInputfile( input ) {
        let file = input.files[ 0 ];
        console.log( input )
        localStorage.setItem( 'tfm_selectfile', file.name ); // filename
        localStorage.removeItem( 'tfm_path' ); //удоляем path
        localStorage.removeItem( 'tfm_token' ); //удоляем token
    }

    function enableImportButton() {
        let filename = localStorage.getItem( 'tfm_selectfile' ); // filename
        let path = localStorage.getItem( 'tfm_path' ); // path
        let token = localStorage.getItem( 'tfm_token' ); // token
        let file_method = localStorage.getItem( 'file_method' ); // file_method
        let title = 'Файл не выбран';
        if ( filename ) {
            btn_ImportCompany.classList.remove( 'disabled' );
            title = 'Выбран "' + filename + '"';
            document.querySelector( 'i#pinIcon' ).classList.add( 'bi-pin-fill' );
            document.querySelector( 'i#pinIcon' ).classList.remove( 'bi-pin-angle' );
        } else {
            btn_ImportCompany.classList.add( 'disabled' );
            document.querySelector( 'i#pinIcon' ).classList.add( 'bi-pin-angle' );
            document.querySelector( 'i#pinIcon' ).classList.remove( 'bi-pin-fill' );
        }
        btn_SelectFile.setAttribute( 'title', title );
        btn_ImportCompany.setAttribute( 'title', title );
        document.getElementById( 'selectedFileName' ).innerHTML = `<em class="trxt-muted">${title}</em>`;
    }
    //
    //
    //
    btn_validate.addEventListener( 'click', () => {
        event.preventDefault();
        form.classList.add( 'start-validation' );
        if ( !_validate( form ) ) {
            myToast.showInvalid();
            return false;
        };
    } );
    btn_cancel.addEventListener( 'click', () => {
        form.reset();
    } );
    btn_clearAll.addEventListener( 'click', () => {
        form.querySelectorAll( 'input,textarea' ).forEach( el => el.value = '' );
    } );
    btn_setDumpData.addEventListener( 'click', () => {
        pm_titleEl.value = 'Заполните данные';
        pm_nameEl.value = 'ООО "Полное Название организации"';
        pm_unpEl.value = '123123123';
        pm_okpoEl.value = '123456780000';
        pm_adressEl.value = '123456, Республика Беларусь, Могилевская область, г.Пуйло, ул.Трепливое, д.14, оф.88';
        pm_bankEl.value = 'AAAA BBBB 1111 2222 3333 4444 5555 в ОАО «Народный Активный Единый БАНК», ' + 'Адрес банка: 220004, г. Минск, пр-т Победителей Машерова, 29, BIC SWIFT : NAEBOO2X';
        pm_contactsEl.value = '+375 29 1234567';
        pm_emailEl.value = 'login@organisation.com';
        pm_director_nominativeEl.value = 'Директор Пукин Владимир Васильевич';
        pm_director_genitiveEl.value = 'Директора Пукина В.В.';
        pm_director_reasonEl.value = 'Устава Воровского Единого';
        pm_userdataEl.value = _generateUserData();
    } );
    btn_setUserData.addEventListener( 'click', () => {
        pm_userdataEl.value = _generateUserData();
    } );
    pm_emailEl.addEventListener( 'dblclick', () => {
        if ( pm_emailEl.value == '' ) {
            pm_emailEl.value = "login@organisation.com";
        }
    } );
    pm_unpEl.addEventListener( 'dblclick', () => {
        if ( pm_unpEl.value == '' ) {
            pm_unpEl.value = '111222333';
        }
    } );
    pm_titleEl.addEventListener( 'dblclick', () => {
        if ( pm_titleEl.value == '' ) {
            let today = new Date();
            pm_titleEl.value = `${pm_name.value} :: ` + formatDateTime( today, "•" );
        }
    } );
    pm_userdataEl.addEventListener( 'dblclick', () => {
        if ( pm_userdataEl.value == '' ) {
            pm_userdataEl.value = _generateUserData();
        }
    } );
    readonlySwitchEl.addEventListener( 'change', ( event ) => {
        if ( btn_CancelUnite || btn_UniteCompanies ) {
            btn_CancelUnite.click();
        }
        event.preventDefault();
        if ( !readonlySwitchEl.checked && !window.confirm( 'Уверены, хотите разрешить изменять данные?' ) ) {
            setReadOnlyTo( 1 );
            readonlySwitchEl.checked = true;
            return false;
        } else {
            //
            let setTo = 0;
            // преключение состояния
            if ( readonlySwitchEl.hasAttribute( 'checked' ) ) {
                readonlySwitchEl.removeAttribute( 'checked' );
                setTo = 0;
            } else {
                readonlySwitchEl.setAttribute( 'checked', 'checked' );
                setTo = 1;
            }
            setReadOnlyTo( setTo );
            const Data = {
                'operation': 'SetReadonly',
                'newValue': readonlySwitchEl.checked,
                'companyID': thisCompanyID.value.toString(),
            };
            return appAjax( Data );
            //
        }
    } );
    btn_save.addEventListener( 'click', ( event ) => {
        if ( readonlySwitchEl.checked ) {
            return false;
        } else {
            event.preventDefault();
            form.classList.add( 'start-validation' );
            if ( !_validate( form ) ) {
                myToast.showInvalid();
                return false;
            };
            const formData = new FormData( form );
            const Data = {
                'read_only': readonlySwitchEl.checked,
                'operation': 'SaveAll',
                'companyID': thisCompanyID.value.toString(),
                'formData': Array.from( formData ),
            };
            return appAjax( Data );
        }
    } );
    btn_clone.addEventListener( 'click', ( event ) => {
        event.preventDefault();
        if ( !window.confirm( 'Уверены, хотите создать копию реквизитов компании?' ) ) {
            return false;
        }
        form.classList.add( 'start-validation' );
        if ( !_validate( form ) ) {
            myToast.showInvalid();
        };
        const formData = new FormData( form );
        const Data = {
            'read_only': readonlySwitchEl.checked,
            'operation': 'CloneCompany',
            'companyID': thisCompanyID.value.toString(),
            'formData': Array.from( formData ),
        };
        return appAjax( Data );
    } );
    /*    Array.prototype.slice.call( ButtonsExportCompany ).forEach( function( el, index ) {
            el.addEventListener( 'click', ( event ) => {
                event.preventDefault();
                const Data = {
                    'operation': 'ExportCompany',
                    'ExportID': +event.target.dataset.export_id,
                };
                return appAjax( Data );
            } );
        } );*/
    btn_ExportCompany.addEventListener( 'click', ( event ) => {
        event.preventDefault();
        localStorage.removeItem( 'tfm_selectfile' ); //удоляем filename
        localStorage.removeItem( 'tfm_path' ); //удоляем path
        localStorage.removeItem( 'tfm_token' ); //удоляем token
        const Data = {
            'operation': 'ExportCompany',
            'ExportID': +event.target.dataset.export_id,
        };
        appAjax( Data );
        return true;
    } );
    document.getElementById( 'resetLS' ).addEventListener( 'click', ( event ) => {
        _resetLS();
    } );
    btn_ImportCompany.addEventListener( 'click', ( event ) => {
        event.preventDefault();
        let filename = localStorage.getItem( 'tfm_selectfile' ); // filename
        let path = localStorage.getItem( 'tfm_path' ); // path
        let token = localStorage.getItem( 'tfm_token' ); // token
        let file_method = localStorage.getItem( 'file_method' ); // file_method
        if ( !path ) path = 'uploads';
        const Data = {
            'fileToUpload': fileToUpload.files[ 0 ],
            'operation': 'ImportCompany',
            'filename': filename,
            'path': path,
            'token': token,
            'file_method': file_method,
        };
        if ( filename ) {
            appAjax( Data );
            _resetLS();
        } else {
            myToast.showError( 'Файл не выбран !!!' );
        }
        _resetLS();
        return true;
    } )

    function _resetLS() {
        localStorage.removeItem( 'tfm_selectfile' ); //удоляем filename
        localStorage.removeItem( 'tfm_path' ); //удоляем path
        localStorage.removeItem( 'tfm_token' ); //удоляем token
        enableImportButton();
        fileToUpload.value = "";
    }
    fileToUpload.addEventListener( 'change', ( event ) => {
        _onchangeInputfile( event.target );
        enableImportButton();
    } )
    document.getElementById( 'form_upload' ).addEventListener( "submit", function( e ) {
        e.preventDefault();
        let form = e.target;
        if ( fileToUpload.value ) {
            let data = new FormData( form );
            let request = new XMLHttpRequest();
            request.onreadystatechange = function() {
                console.log( request );
                if ( request.readyState == 4 ) {
                    if ( request.responseText == "Файл загружен." ) {
                        myToast.showSuccess();
                        btn_ImportCompany.classList.remove( 'disabled' );
                    } else {
                        myToast.showError( request.responseText );
                        btn_ImportCompany.classList.add( 'disabled' );
                    }
                    // console.error(request.responseText);
                }
            }
            request.open( form.method, form.action );
            request.send( data );
        } else {
            myToast.showError( "Файл не выбран" );
        }
    } );
    btn_AddCompany.addEventListener( "click", ( event ) => {
        event.preventDefault();
        if ( !window.confirm( 'Уверены, хотите добавить реквизиты новой компании?' ) ) {
            return false;
        }
        const Data = {
            'operation': 'AddCompany',
            'companyID': -1,
            'formData': [],
        };
        return appAjax( Data );
    } );
    btn_cors.addEventListener( "click", ( event ) => {
        event.preventDefault();
        corsСontainer.classList.add( 'visually-hidden' );
        if ( !window.confirm( 'Уверены, хотите отправить запрос на внешний сайт?' ) ) {
            return false;
        }
        if ( !form.pm_unp.checkValidity() && !form.pm_name.checkValidity() ) {
            form.pm_unp.classList.add( 'is-invalid' );
            form.pm_name.classList.add( 'is-invalid' );
            myToast.showInvalid();
            return false;
        }
        let str = document.querySelector( '#pm_unp' ).value;
        let fixed = str.replace( /[^0-9]/g, '' );
        if ( fixed.length != 9 ) {
            myToast.showError();
            return false;
        }
        let unp = fixed;
        const Data = {
            'operation': 'CorsCompany',
            'companyID': thisCompanyID.value.toString,
            'name': pm_nameEl.value, //.name,
            'unp': unp, //.unp,
        };
        return appAjax( Data );
    } );
    if ( btn_egr ) {
        btn_egr.addEventListener( "click", ( event ) => {
            event.preventDefault();
            if ( !form.pm_unp.checkValidity() && !form.pm_name.checkValidity() ) {
                form.pm_unp.classList.add( 'is-invalid' );
                form.pm_name.classList.add( 'is-invalid' );
                myToast.showInvalid();
                return false;
            }
            let str = document.querySelector( '#pm_unp' ).value;
            let fixed = str.replace( /[^0-9]/g, '' );
            if ( fixed.length != 9 ) {
                myToast.showError();
                return false;
            }
            let unp = fixed;
            let href = "https://egr.gov.by/egrmobile/information?pan=";
            let url = href + unp;
            window.open( url, '_blank' ).focus();
        } );
    }
    //
    btn_LinkCompany.addEventListener( 'click', ( event ) => {
        event.preventDefault();
        let toOrderID = +document.getElementById( 'toOrderID' ).value;
        let toInvoiceID = +document.getElementById( 'toInvoiceID' ).value;
        if ( !window.confirm( 'Выбрать реквизиты компании \r\n' + htmlspecialchars_decode( pm_nameEl.value ) + ' id:' + thisCompanyID.value + ' для счета #' + toOrderID + ' ?' ) ) {
            return false;
        }
        const Data = {
            'operation': 'LinkCompany',
            'companyID': +thisCompanyID.value,
            'toOrderID': +toOrderID,
            'toInvoiceID': +toInvoiceID,
        };
        // console.log(Data)
        return appAjax( Data );
    } );
    let ArrayToKill;
    restoreBySessionStorage();
    let isFileSelected = localStorage.getItem( 'tfm_selectfile' ); // filename
    enableImportButton();

    function restoreBySessionStorage() {
        let companySample = sessionStorage.getItem( 'companySample' );
        let jsonCompaniesToKill = sessionStorage.getItem( 'companiesToKill' );
        if ( btn_CancelUnite || btn_UniteCompanies ) {
            btn_UniteCompanies.classList.add( 'disabled' );
        }
        let temp = JSON.parse( jsonCompaniesToKill );
        if ( temp ) {
            ArrayToKill = temp;
        } else {
            ArrayToKill = [];
        }
        if ( companySample ) {
            SampleRadios.forEach( function( el, index ) {
                if ( el.value == companySample ) {
                    el.checked = true;
                }
            } );
        }
        if ( jsonCompaniesToKill && ArrayToKill.length ) {
            DeadManChecks.forEach( function( el, index ) {
                if ( ArrayToKill.includes( el.value ) ) {
                    el.checked = true;
                }
            } );
        }
        if ( btn_CancelUnite || btn_UniteCompanies ) {
            if ( sessionStorage.getItem( "companySample" ) && ArrayToKill.length ) {
                btn_UniteCompanies.classList.remove( 'disabled' );
            } else {
                btn_UniteCompanies.classList.add( 'disabled' );
            }
        }
    }
    if ( btn_CancelUnite || btn_UniteCompanies ) {
        btn_CancelUnite.addEventListener( 'click', ( event ) => {
            sessionStorage.removeItem( "companySample" );
            sessionStorage.removeItem( "companiesToKill" );
            ArrayToKill = [];
            SampleRadios.forEach( function( el, index ) {
                el.checked = false;
            } )
            DeadManChecks.forEach( function( el, index ) {
                el.checked = false;
            } )
            btn_UniteCompanies.classList.add( 'disabled' );
        } );
        btn_UniteCompanies.addEventListener( 'click', ( event ) => {
            event.preventDefault();
            btn_UniteCompanies.classList.add( 'disabled' );
            if ( !window.confirm( 'Уверены, что хотите слить компании, заменив на правльный вариант?' ) ) {
                return false;
            }
            const Data = {
                'operation': 'UniteCompanies',
                'companySample': sessionStorage.getItem( 'companySample' ),
                'companiesToKill': sessionStorage.getItem( 'companiesToKill' ),
            };
            return appAjax( Data );
        } );
    }
    btnTerminateCompany.addEventListener( 'click', ( event ) => {
        event.preventDefault();
        if ( !window.confirm( 'Уверены, что ОКОНЧАТЕЛЬНО УДАЛИТЬ КОМПАНИЮ, это то, что вы точно хотите? НЕ ВЗИРАЯ НА ЗАЩИТЫ' ) ) {
            return false;
        }
        const Data = {
            'operation': 'TerminateCompany',
            'KillID': +event.target.dataset.kill_id,
        };
        return appAjax( Data );
    } );
    Array.prototype.slice.call( ButtonsKillCompany ).forEach( function( el, index ) {
        el.addEventListener( 'click', ( event ) => {
            event.preventDefault();
            if ( !window.confirm( 'Уверены, что ОКОНЧАТЕЛЬНО УДАЛИТЬ КОМПАНИЮ, это то, что вы точно хотите?' ) ) {
                return false;
            }
            const Data = {
                'operation': 'KillCompany',
                'KillID': +event.target.dataset.kill_id,
            };
            return appAjax( Data );
        } );
    } )
    Array.prototype.slice.call( SampleRadios ).forEach( function( el, index ) {
        el.addEventListener( 'input', function( event ) {
            let indexToSurvive;
            let id = event.target.value;
            sessionStorage.setItem( 'companySample', id );
            if ( sessionStorage.getItem( "companySample" ) && ArrayToKill.length ) {
                btn_UniteCompanies.classList.remove( 'disabled' );
            } else {
                btn_UniteCompanies.classList.add( 'disabled' );
            }
        } );
    } );
    Array.prototype.slice.call( DeadManChecks ).forEach( function( el, index ) {
        el.addEventListener( 'input', function( event ) {
            let id = event.target.value;
            let indexToSurvive;
            if ( event.target.checked === true ) {
                ArrayToKill.push( id );
            }
            indexToSurvive = ArrayToKill.indexOf( id );
            if ( event.target.checked === false ) {
                ArrayToKill.splice( indexToSurvive, 1 );
            }
            sessionStorage.setItem( 'companiesToKill', JSON.stringify( ArrayToKill ) );
            if ( sessionStorage.getItem( "companySample" ) && ArrayToKill.length ) {
                btn_UniteCompanies.classList.remove( 'disabled' );
            } else {
                btn_UniteCompanies.classList.add( 'disabled' );
            }
        } );
    } );
    //
    //
    //
    async function appAjax( params ) {
        // console.log(params)
        let operation = params.operation;
        //
        let url = checkOnUrl( document.location.href );
        const Data = { ...params
        };
        const response = await fetch( url + '&operation=' + operation + '&app=app_admincompanies', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json;charset=utf-8',
            },
            body: JSON.stringify( {
                Data
            } ),
        } );
        // console.log(response)
        //
        const result = await response.text();
        //
        // На основе ответа от сервера показываем сообщения об Успехе или Ошибке
        if ( operation == "SaveAll" ) {
            updateTimeEl.innerHTML = '<i class="bi bi-stopwatch"></i> most recently...';
        }
        if ( result === 'SUCCESS' ) {
            myToast.showSuccess();
        } else if ( result === 'FAILED' ) {
            myToast.showError();
        } else {
            if ( operation == "ExportCompany" ) {
                let ans = JSON.parse( result );
                myToast.showSuccess( ans );
            } else if ( operation == "CorsCompany" ) {
                let ans = JSON.parse( result );
                answerDecode( ans, operation );
            } else {
                document.location.href = result;
            }
        }
    }

    function answerDecode( d, operation ) {
        switch ( operation ) {
            case 'CorsCompany':
                if ( d === false ) {
                    myToast.showError();
                    let html = `<tbody class="table-warning">
  <tr><th class="text-danger text-center">ДАННЫЕ ОТСУТСТВУЮТ <i class="bi bi-exclamation-triangle-fill"></i> Перейдите ПО ССЫЛКЕ <a class="btn-link" target="_blank" href="http://grp.nalog.gov.by/grp">Государственный Реестр Плательщиков Республики Беларусь</a></th></tr>
  </tbody>
   <caption for="corsContainer"> </caption>
`;
                    corsСontainer.classList.remove( 'visually-hidden' );
                    corsСontainer.innerHTML = html;
                    return false;
                }
                let isDangerClass = ( d.vkods !== "Действующий" ) ? `class="table-danger"` : `class="table-success"`;
                let html = `
<caption for="corsContainer">Поиск юридических лиц в ГРП РБ <a class="btn-link" target="_blank" href="http://grp.nalog.gov.by/grp">http://grp.nalog.gov.by/grp</a></caption>
<table class="table table-hover table-striped table-sm">
                <tbody ${isDangerClass}>
  <tr><td>vkods</td><td>Состояние плательщика</td><th>${d.vkods}</th></tr>
  <tr><td>vunp</td><td>УНП плательщика</td><th>${d.vunp}</th></tr>
  <tr><td>vnaimp</td><td>полное наименование плательщика</td><th>${d.vnaimp}</th></tr>
  <tr><td>vnaimk</td><td>краткое наименование плательщика</td><th>${d.vnaimk}</th></tr>
  <tr><td>vpadres</td><td>место нахождения юридического лицаа</td><th>${d.vpadres}</th></tr>
  <tr><td>dreg</td><td>дата постановки на учет</td><th>${d.dreg}</th></tr>
  <tr><td>nmns</td><td>код инспекции МНС</td><th>${d.nmns}</th></tr>
  <tr><td>vmns</td><td>наименование инспекции МНС</td><th>${d.vmns}</th></tr>
  <tr><td>ckodsost</td><td>код состояния плательщика</td><th>${d.ckodsost}</th></tr>
  <tr><td>dlikv</td><td>дата изменения состояния плательщика</td><th>${d.dlikv}</th></tr>
  <tr><td>vlikv</td><td>основание изменения состояния плательщика</td><th>${d.vlikv}</th></tr>
  </tbody>
</table>
<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
`;
                corsСontainer.classList.remove( 'visually-hidden' );
                corsСontainer.innerHTML = html;
                break;
            case 'SaveAll':
                break;
        }
    }
}
window.onload = app();
export default app
// vunp => 100582333
// vnaimp => Министерство по налогам и сборам Республики Беларусь
// vnaimk => МНС
// vpadres => г.Минск,ул.Советская,9
// dreg => 1994-06-30
// nmns => 104
// vmns => Инспекция МНС по Московскому району г.Минска
// ckodsost => 1
// vkods => Действующий
// dlikv => null
// vlikv => null