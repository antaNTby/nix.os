async function asyncValidateShoppingCart(event) {
    // Immediately remove current toasts without using animation
    toastr.remove()


    let RegistrationIsValid = false
    let EmailIsChecked = false
    let ShippingAndPaymentIsSelected = false
    let PaymentAddonIsValid = false
    let RESULT = false
    // console.table(
    //     [
    //         ["RegistrationIsValid", RegistrationIsValid],
    //         ["EmailIsChecked", EmailIsChecked],
    //         ["ShippingAndPaymentIsSelected", ShippingAndPaymentIsSelected],
    //         ["PaymentAddonIsValid", PaymentAddonIsValid],
    //         [" RESULT ==", RESULT]
    //     ]
    // )

    RegistrationIsValid = await asyncValidateRegistration(event)
    RESULT = RegistrationIsValid
    // console.table(
    //     [
    //         ["!!!-RegistrationIsValid", RegistrationIsValid],
    //         ["EmailIsChecked", EmailIsChecked],
    //         ["ShippingAndPaymentIsSelected", ShippingAndPaymentIsSelected],
    //         ["PaymentAddonIsValid", PaymentAddonIsValid],
    //         [" RESULT ==", RESULT]
    //     ]
    // )

    EmailIsChecked = await asyncValidateEmail()
    RESULT = RESULT && EmailIsChecked
    console.table(
        [
            ["RegistrationIsValid", RegistrationIsValid],
            ["!!!-EmailIsChecked", EmailIsChecked],
            ["ShippingAndPaymentIsSelected", ShippingAndPaymentIsSelected],
            ["PaymentAddonIsValid", PaymentAddonIsValid],
            [" RESULT ==", RESULT]
        ]
    )

    ShippingAndPaymentIsSelected = await asyncValidateShippingAndPayment()
    RESULT = RESULT && ShippingAndPaymentIsSelected
    // console.table(
    //     [
    //         ["RegistrationIsValid", RegistrationIsValid],
    //         ["EmailIsChecked", EmailIsChecked],
    //         ["!!!-ShippingAndPaymentIsSelected", ShippingAndPaymentIsSelected],
    //         ["PaymentAddonIsValid", PaymentAddonIsValid],
    //         [" RESULT ==", RESULT]
    //     ]
    // )

    if (ShippingAndPaymentIsSelected) {

        if ( document.getElementById('payment_addon_html').innerHTML.length ) {
            PaymentAddonIsValid = await asyncValidatePaymentAddon()
            RESULT = RESULT && PaymentAddonIsValid
        } else {
            RESULT = RESULT && true
        }

        // console.table(
        //     [
        //         ["RegistrationIsValid", RegistrationIsValid],
        //         ["EmailIsChecked", EmailIsChecked],
        //         ["ShippingAndPaymentIsSelected", ShippingAndPaymentIsSelected],
        //         ["!!!-PaymentAddonIsValid", PaymentAddonIsValid],
        //         [" RESULT ==", RESULT]
        //     ]
        // )
    } else{
        RESULT = RESULT && false
    }


        console.table(
            [
                ["RegistrationIsValid", RegistrationIsValid],
                ["EmailIsChecked", EmailIsChecked],
                ["ShippingAndPaymentIsSelected", ShippingAndPaymentIsSelected],
                ["PaymentAddonIsValid", PaymentAddonIsValid],
                [" RESULT ", RESULT]
            ]
        )

    if (RESULT == true) {
        console.log("return asyncProcessThisOrder()")
        asyncProcessThisOrder()
    } else {
        console.log("ShowError('Проверьте ваши данные', 'ОШИБКА')")
        ShowError('Проверьте ваши данные', 'ОШИБКА')
    }


}



function clearElementValidationState(name) {

    let selector = ''
    if (name === 'shipping') {
        selector = 'div#shipping_method_select li'
    } else if (name === 'payment') {
        selector = 'div#payment_method_select li'
    }

    [].forEach.call(document.querySelectorAll(selector), function(el) {
        if (name == 'shipping' || name == 'payment') {
            el.classList.remove('list-group-item-danger')
            el.classList.remove('list-group-item-success')
        }
    })
}

function setElementValidationTrue(name) {

    let selector = ''
    if (name === 'shipping') {
        selector = 'div#shipping_method_select li'
    } else if (name === 'payment') {
        selector = 'div#payment_method_select li'
    }

    [].forEach.call(document.querySelectorAll(selector), function(el) {
        if (name == 'shipping' || name == 'payment') {
            el.classList.remove('list-group-item-danger')
            el.classList.add('list-group-item-success')
        }
    })
}

function setElementValidationFalse(name) {

    let selector = ''
    if (name === 'shipping') {
        selector = 'div#shipping_method_select li'
    } else if (name === 'payment') {
        selector = 'div#payment_method_select li'
    }

    [].forEach.call(document.querySelectorAll(selector), function(el) {
        if (name == 'shipping' || name == 'payment') {
            el.classList.add('list-group-item-danger')
            el.classList.remove('list-group-item-success')
        }
    })
}

function asyncValidateShippingAndPayment() {
    let shippingOK = false
    let paymentOK = false

    clearElementValidationState('shipping')
    clearElementValidationState('payment')
    $inputs_shipping = document.querySelectorAll('input[name=shipping_method]')
    $inputs_shipping_checked = document.querySelectorAll('input[name=shipping_method]:checked')
    $inputs_payment = document.querySelectorAll('input[name=payment_method]')
    $inputs_payment_checked = document.querySelectorAll('input[name=payment_method]:checked')

    if ($inputs_shipping.length && !$inputs_shipping_checked.length) {
        shippingOK = false;
        ShowError('Не выбран способ доставки!', 'ОШИБКА');
        setElementValidationFalse('shipping')
    } else {
        shippingOK = true;
        setElementValidationTrue('shipping')
    }

    if ($inputs_payment.length && !$inputs_payment_checked.length) {
        paymentOK = false;
        ShowError('Не выбран способ оплаты!', 'ОШИБКА');
        setElementValidationFalse('payment')
    } else {
        paymentOK = true;
        setElementValidationTrue('payment')
    }
    // console.log("shippingOK && paymentOK", `${shippingOK} && ${paymentOK}`);
    return shippingOK && paymentOK
}

async function asyncValidateRegistration(event) {

    clearElementValidationState('shipping')
    clearElementValidationState('payment')
    cleanFormsWasValidated()

    let result = true
    let forms = document.querySelectorAll('.start-validation') //NodeList
    Array.prototype.slice.call(forms).forEach(function(form) {
        var isValid = form.checkValidity()
        if (isValid) {
            // ShowSuccess(form.title +' - валидация пройдена')
        } else {
            if (event) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('start-validation')
            ShowError(form.title + ' - валидация отклонена')
        }
        form.classList.add('was-validated')
        result = result && isValid
    })

    if (result) {
        // ShowSuccess('Все данные заполнены правильно', 'Валидация данных завершена УСПЕШНО')
    } else {
        ShowError('Ошибка во введенных данных', 'Валидация данных завершена НЕУДАЧНО')
    }

    return result
}

async function asyncValidateEmail() {
    let $user_email = $('#email').val().toString()
    let ajaxurl = '/index.php?quick_cart=yes&email_validate_nano=' + $user_email + '&function=email_validate'
    let res = await doMyAjax(ajaxurl)
    res=res.toString().trim()
    if (res === "1") {
        $('#email').addClass('is-valid').removeClass('is-invalid')
        $('#email').next('div.valid-feedback').removeClass('visually-hidden').next('div.invalid-feedback').addClass('visually-hidden')
        // ShowSuccess('OK! Email ' + $user_email)
        return true
    } else {
        $('#email').addClass('is-invalid').removeClass('is-valid')
        $('#email').next('div.valid-feedback').addClass('visually-hidden').next('div.invalid-feedback').removeClass('visually-hidden').text(res)
        ShowError('Неверный адрес электронной почты' + '<br>' + res, 'ОШИБКА')
        return false
    }
}

async function asyncValidatePaymentAddon() {
    let result = true
    let $payment_method = $('input[name=payment_method]:checked').val().toString()

    let forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function(form) {
        var isValid = form.checkValidity()
        if (isValid) {
            // ShowSuccess(form.title +' - валидация пройдена')
        } else {
            if (event) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('needs-validation')
            ShowError(form.title + ' - валидация отклонена')
        }
        form.classList.add('was-validated')
        result = result && isValid
    })

    if (result) {
        let res = await asyncSaveCompany()
        return true
    } else {
        ShowError('Ошибка во введенных данных', 'Валидация данных завершена НЕУДАЧНО')
        return false
    }

}


$(document).ready(function() {
    bindHtmlReactions()
})



async function asyncProcessThisOrder() {
    // ShowMessageLong('Через несколько сеунд вы получите счет для оплаты.', 'Успешное оформление заказа')
    ShowMessageLong('Спасибо за ваш заказ.', 'Успешное оформление заказа')
    $('form#formCart').submit()
}