const ErrorToastEl = document.querySelector( '#myError' );
const SuccessToastEl = document.querySelector( '#mySuccess' );
const myInvalidEl = document.querySelector( '#myInvalid' );
const timeElements = document.querySelectorAll( '.showTime' );
let showError = {};
let showSuccess = {};
let showInvalid = {};
let option = {};
let toastElList = {};
let toastList = {};
const IS_ALLOW = true;
const IS_ALLOW_DANGER = true;
// const IS_ALLOW = false;
document.addEventListener( "DOMContentLoaded", function() {
    let MESS = '';
    let btnSuccess = document.getElementById( "btnSuccess" );
    let btnDanger = document.getElementById( "btnDanger" );
    // Create toast instance
    let myError = new bootstrap.Toast( ErrorToastEl );
    let mySuccess = new bootstrap.Toast( SuccessToastEl );
    let myInvalidForm = new bootstrap.Toast( myInvalidEl );
    btnDanger.addEventListener( "click", function() {
        // myError.show();
        showError( "myError" )
    } );
    btnSuccess.addEventListener( "click", function() {
        // mySuccess.show();
        showSuccess( "jjjjjj" )
    } );
    toastElList = document.querySelectorAll( '.toast' ); //  все html класса тост
    toastList = [ ...toastElList ].map( toastEl => new bootstrap.Toast( toastEl, option ) ); // массив объекьов
    /* выводим одно и тоже время выполнения для всех toastElList*/
    // [].forEach.call(toastElList, function(El) {
    // El.addEventListener('show.bs.toast', () => {
    // timeElements.forEach(function(el) {
    // el.innerHTML = nowLocale();
    // });
    // });
    // });
    /* выводим СВОЁ время выполнения для всех toastElList*/
    [].forEach.call( toastElList, function( El ) {
        El.addEventListener( 'show.bs.toast', () => {
            // console.info( MESS );
            let thisTimeEl = El.querySelector( '.showTime' );
            let thisMessage = El.querySelector( '.message-body' );
            let today = new Date();
            thisTimeEl.innerHTML = formatTime( today );
            if ( MESS ) thisMessage.innerHTML = MESS;
        } );
    } );
    showError = function( mess ) {
        MESS = mess;
        if ( IS_ALLOW_DANGER ) myError.show();
    }
    showSuccess = function( mess ) {
        MESS = mess;
        if ( IS_ALLOW ) mySuccess.show();
    }
    showInvalid = function( mess ) {
        MESS = mess;
        if ( IS_ALLOW ) myInvalidForm.show();
    }

    function nowLocale() {
        // создаем новый объект `Date`
        let today = new Date();
        // получаем дату и время
        let nowLocale = today.toLocaleString();
        return nowLocale;
    }
} );
// ErrorToastEl.addEventListener('show.bs.toast', () => {
//     timeElements.forEach(function(el) {
//         el.innerHTML = nowLocale();
//     });
// });
// SuccessToastEl.addEventListener('show.bs.toast', () => {
//     timeElements.forEach(function(el) {
//         el.innerHTML = nowLocale();
//     });
// });
export {
    showError,
    showSuccess,
    showInvalid,
    toastElList,
    toastList
}
/*
Events
Event   Description
hide.bs.toast   This event is fired immediately when the hide instance method has been called.
hidden.bs.toast     This event is fired when the toast has finished being hidden from the user.
show.bs.toast   This event fires immediately when the show instance method is called.
shown.bs.toast  This event is fired when the toast has been made visible to the user.

const myToastEl = document.getElementById('myToast')
myToastEl.addEventListener('hidden.bs.toast', () => {
  // do something...
})


*/