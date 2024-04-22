var button_checked // border border-danger rounded rounded-3

let FieldsChangedByUser = {}
const city = document.getElementById( 'city' )
const shipping_method_html = document.getElementById( 'shipping_method_html' )
const payment_method_html = document.getElementById( 'payment_method_html' )
const payment_addon_html = document.getElementById( 'payment_addon_html' )
const inputLastName = document.querySelector( '[name="last_name"]' )
const zoneID = document.getElementById( 'zoneID' )
const public_offer = document.getElementById( 'public_offer' )
const error_offer = document.getElementById( 'error_offer' )
const cartContentForm = document.getElementById( 'formCartContent' )
const main_cart_icon = document.getElementById( 'main_cart_icon' )
const main_cart_counter = document.getElementById( 'main_cart_counter' )
const main_cart_value = document.getElementById( 'main_cart_value' )
const ajax_cart = document.getElementById( 'ajax_cart' )
const buttonSubmit = document.querySelector( 'a[name="cart_submit"]' )
const Cities = {
    '101': 'БРЕСТ',
    '102': 'ВИТЕБСК',
    '103': 'ГОМЕЛЬ',
    '104': 'ГРОДНО',
    '105': 'СОЛИГОРСК',
    '106': 'МОГИЛЕВ',
    '100': 'МИНСК',
    '107': 'МИНСК',
}

function apply_public_offer( id ) {
    if ( id != 2 ) {
        if ( public_offer.value == 0 ) {
            public_offer.value = 1
            error_offer.style.display = 'none'
            buttonSubmit.classList.remove( 'disabled' )
        } else {
            public_offer.value = 0
            error_offer.style.display = 'block'
            buttonSubmit.classList.add( 'disabled' )
        }
    } else {
        if ( public_offer.value != 1 ) {
            error_offer.style.display = 'block'
            buttonSubmit.classList.add( 'disabled' )
            return false
        }
    }
}

function _bindCountInputEvents() {
    [].forEach.call( document.querySelectorAll( 'input.inputCount[type="text"]' ), function( el ) {
        el.addEventListener( 'keyup', function( event ) {
            let elementID = event.target.id
// console.log(elementID)

            let x = event.key
            if ( x == 'Enter' ) {
                return count_changed( elementID )
            } else
            if ( x == 'Escape' ) {
                return event.target.value = '1'
            } else
            if ( x == 'PageUp' ) {
                return cartplus( elementID )
            } else
            if ( x == 'PageDown' ) {
                return cartminus( elementID )
            } else {
                setTimeout( count_changed, 300, elementID ) // Привет, Джон
            }
            return false
        }, false )
        el.addEventListener( 'blur', function( event ) {
            setTimeout( count_changed, 100, event.target.id ) // Привет, Джон
        }, false )
        el.addEventListener( 'input', function( event ) {
            event.target.value = event.target.value.replace( /[^0-9\.]/g, '' )
            if ( isNaN( event.target.value ) || +event.target.value <= 0 ) event.target.value = 1
        }, false )
    } )
}

function bindAjaxReactions() {
    // прописываем события внутри таблицы контента
    _bindCountInputEvents()
}

function bindHtmlReactions() {
    // прописываем события кнопкам вне аякс таблицы
    _bindCountInputEvents()

    inputLastName.addEventListener( 'input', function( event ) {
        event.target.value = event.target.value.replace( /[^0-9\s()+#]/g, '' )
    } )
    buttonSubmit.addEventListener( 'click', function( event ) {
        asyncValidateShoppingCart( event )
    } )
}


async function count_changed( $elementID ) {
    elementID = $elementID.replace( '#', '' ).toString()
    let qty = Number( document.getElementById( elementID ).value )
    // var qty = parseInt($(elementID).val())
    if ( ( isNaN( qty ) ) || ( +qty <= 0 ) ) {
        qty = 0
    }
    document.getElementById( elementID ).value = +qty
    // console.log(document.getElementById(elementID))
    let req = {}
    await cart_content_update( 'fadeToggle' )
    return true
}

async function cart_content_update( show_spinner = true ) {
    if ( !ajax_cart ) {
        // не установлена AJAX-cart -> return
        ShowError( 'не установлена AJAX-cart' )
        return false
    }
    let reqData = $( '#formCartContent,#formCart' ).serialize()
    let ajaxurl = '/index.php?shopping_cart=yes&cart_recalc=yes' + '&function=cart_content_update'
    if ( show_spinner === 'fadeToggle' ) {
        $( 'formCartContent' ).fadeToggle( '150' ).fadeToggle( '150' )
    } else if ( show_spinner ) {
        cartContentForm.innerHTML = SPINNER_SHOW
    } else {
        cartContentForm.innerHTML = ''
    }
    let shopping_cart_ajax = await doMyAjax( ajaxurl, reqData, 'POST' )
    if ( shopping_cart_ajax.length ) {
        cartContentForm.innerHTML = shopping_cart_ajax
        let el = document.getElementById( 'cartItemCount' )
        doShoppingCartBriefInfo( el.dataset.num )
        bindAjaxReactions()
    }
}
async function region_changed() {
    city.value = null
    let ajaxurl = '/index.php?quick_cart=yes&zoneID=' + zoneID.value + '&function=region_changed'
    shipping_method_html.innerHTML = SPINNER_SHOW
    let shipping_method_ajax = await doMyAjax( ajaxurl )
    if ( shipping_method_ajax.length ) {
        shipping_method_html.innerHTML = shipping_method_ajax
        await shipping_changed()
    }
    city.value = Cities[ `${zoneID.value}` ]
}
async function shipping_changed() {
    // if (button_checked) return
    let shippingID
    let shippingButtons = document.querySelectorAll( 'input[name=shipping_method]' )
    let shippingButtonChecked = document.querySelectorAll( 'input[name=shipping_method]:checked' )
    let CONF_ADDRESSFORM_STATE_eq_0 = document.getElementById( 'CONF_ADDRESSFORM_STATE_eq_0' ).value
    if ( CONF_ADDRESSFORM_STATE_eq_0 && document.getElementById( 'zoneID' ).value == 0 ) {
        shippingID = 0 // не выбран регион (при обязательности выбора)
    } else if ( !shippingButtons || ( !shippingButtons.length ) ) {
        shippingID = -1 // ни одного баттона нет
    } else if ( !shippingButtonChecked || ( !shippingButtonChecked.length ) ) {
        shippingID = 0 // ни одного баттона не выбрано
    } else shippingID = shippingButtonChecked[ 0 ].value // баттон выбран
    let ajaxurl = '/index.php?quick_cart=yes&shippingID=' + shippingID + '&function=shipping_changed'
    payment_method_html.innerHTML = SPINNER_SHOW
    let payment_method_ajax = await doMyAjax( ajaxurl )
    if ( payment_method_ajax.length ) {
        payment_method_html.innerHTML = payment_method_ajax
        cart_content_update()
        payment_changed()
    } else {
        payment_addon_html.innerHTML = null
        return false
    }
    clearElementValidationState( 'shipping' )
}


async function payment_changed() {
    let addon_ajax

    // if (button_checked) return
    let shippingButtonChecked = document.querySelectorAll( 'input[name=shipping_method]:checked' )
    let paymentButtonChecked = document.querySelectorAll( 'input[name=payment_method]:checked' )
    if ( !shippingButtonChecked || ( !shippingButtonChecked.length ) ) {
        setElementValidationFalse( 'shipping' )
        ShowError( 'Не выбран метод доставки' )
        return false
    }
    if ( !paymentButtonChecked || ( !paymentButtonChecked.length ) ) {
        // ни одного баттона нет или ни один не выбран
        payment_addon_html.innerHTML = null
        return false
    }



    let ajaxurl = '/index.php?quick_cart=yes&paymentID=' + paymentButtonChecked[ 0 ].value + '&function=payment_changed'

    payment_addon_html.innerHTML = SPINNER_SHOW

    addon_ajax = await doMyAjax( ajaxurl )

    if ( addon_ajax.length ) {
        initPaymentAddon( addon_ajax )
        // $('select.selectpicker[name=selectCompany2]').selectpicker('refresh');
    } else {
        ShowInfo( 'Дополнительных данных не требуется' )
    }

    clearElementValidationState( 'payment' )
}