toastr.options = {
    closeButton: !0,
    debug: !1,
    newestOnTop: !0,
    progressBar: !1,
    positionClass: "toast-bottom-right",
    preventDuplicates: !0,
    onclick: null,
    showDuration: "300",
    hideDuration: "1000",
    timeOut: "5000",
    extendedTimeOut: "1000",
    showEasing: "swing",
    hideEasing: "linear",
    showMethod: "fadeIn",
    hideMethod: "fadeOut"
};

function ShowError( a, b ) {
    console.log( "%c ERROR: " + a, "color:red", ( new Date ).toLocaleTimeString( "ru-ru" ) );
    toastr.error( a, b, {
        closeButton: !0,
        debug: !1,
        newestOnTop: !0,
        progressBar: !1,
        positionClass: "toast-top-left",
        preventDuplicates: !0,
        onclick: null,
        showDuration: "300",
        hideDuration: "1000",
        timeOut: "6000",
        extendedTimeOut: "12000",
        showEasing: "swing",
        hideEasing: "linear",
        showMethod: "fadeIn",
        hideMethod: "fadeOut"
    } )
}

function ShowSuccess( a, b = '', c = 0 ) {
    let myPositionClass = "toast-bottom-right"

    if ( c != 0 ) {
        myPositionClass = "toast-top-right"
    }

    console.log( "%c " + a, "color:green", ( new Date ).toLocaleTimeString( "ru-ru" ) );
    toastr.success( a, b, {
        closeButton: !0,
        debug: !1,
        newestOnTop: !0,
        progressBar: !1,
        positionClass: myPositionClass,
        preventDuplicates: !0,
        onclick: null,
        showDuration: "300",
        hideDuration: "1000",
        timeOut: "5000",
        extendedTimeOut: "1000",
        showEasing: "swing",
        hideEasing: "linear",
        showMethod: "fadeIn",
        hideMethod: "fadeOut"
    } )
}

function ShowInfo( a, b = '', c = 0 ) {

    let myPositionClass = "toast-bottom-right"

    if ( c != 0 ) {
        myPositionClass = "toast-top-right"
    }

    console.log( a, ( new Date ).toLocaleTimeString( "ru-ru" ) , myPositionClass)

    toastr.info( a, b, {
        closeButton: !0,
        debug: !1,
        newestOnTop: !0,
        progressBar: !1,
        positionClass: myPositionClass,
        preventDuplicates: !0,
        onclick: null,
        showDuration: "300",
        hideDuration: "1000",
        timeOut: "5000",
        extendedTimeOut: "1000",
        showEasing: "swing",
        hideEasing: "linear",
        showMethod: "fadeIn",
        hideMethod: "fadeOut"
    } )

}

function ShowMessageShort( a, b ) {
    console.log( a, ( new Date ).toLocaleTimeString( "ru-ru" ) );
    toastr.info( a, b, {
        closeButton: !0,
        debug: !1,
        newestOnTop: !0,
        progressBar: !1,
        positionClass: "toast-bottom-right",
        preventDuplicates: !0,
        onclick: null,
        showDuration: "100",
        hideDuration: "100",
        timeOut: "600",
        extendedTimeOut: "1000",
        showEasing: "swing",
        hideEasing: "linear",
        showMethod: "fadeIn",
        hideMethod: "fadeOut"
    } )
}

function ShowMessageLong( a, b ) {
    console.log( a, ( new Date ).toLocaleTimeString( "ru-ru" ) );
    toastr.info( a, b, {
        closeButton: !0,
        debug: !1,
        newestOnTop: !0,
        progressBar: !1,
        positionClass: "toast-top-full-width",
        preventDuplicates: !0,
        onclick: null,
        showDuration: "2000",
        hideDuration: "2000",
        timeOut: "12000",
        extendedTimeOut: "12000",
        showEasing: "swing",
        hideEasing: "linear",
        showMethod: "fadeIn",
        hideMethod: "fadeOut"
    } )
}

function ShowWarning( a, b = '', c = 0 ) {
    let myPositionClass = "toast-bottom-right"

    if ( c != 0 ) {
        myPositionClass = "toast-top-right"
    }

    console.log( a, ( new Date ).toLocaleTimeString( "ru-ru" ) );
    return toastr.warning( a, b, {
        closeButton: !0,
        debug: !1,
        newestOnTop: !0,
        progressBar: !1,
        positionClass: myPositionClass,
        preventDuplicates: !0,
        onclick: null,
        showDuration: "300",
        hideDuration: "1000",
        timeOut: "5000",
        extendedTimeOut: "1000",
        showEasing: "swing",
        hideEasing: "linear",
        showMethod: "fadeIn",
        hideMethod: "fadeOut"
    } )
}

$( document ).ready( function() {
    var a = document.getElementById( "modalAuth" );
    a && a.length && a.addEventListener( "shown.bs.modal", function( b ) {
        document.getElementById( "user_pw_id" ).setAttribute( "type", "password" )
    } )
} );