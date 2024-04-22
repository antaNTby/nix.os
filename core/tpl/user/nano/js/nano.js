// var myScreen=document.getElementById('myScreen')
// var stickyHeader=document.getElementById('stickyHeader')
// var myW= stickyHeader.clientWidth
// alert(myW)

var globalTimeout = null
const KatzapskayaMovaLink = `<a href="#" data-action="show-filter" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFilter">отфильтровано</a>`
const KatzapskayaMova = {
    "thousands": "&nbsp;",
    "decimal": ".",
    "processing": " Подождите, пожалуйста, идет подготовка данных ",
    "search": "Фильтровать",
    "lengthMenu": "_MENU_",
    "info": "Товар(-ы) с _START_ до _END_ из <span class=\"badge bg-danger\">_TOTAL_</span> товар(-ов)",
    "infoEmpty": "Товар(-ы) с 0 до 0 из 0 товар(-ов)",
    "infoFiltered": "( " + KatzapskayaMovaLink + " из <span class=\"badge bg-secondary\">_MAX_</span> товар(-ов))",
    "infoPostFix": "",
    "loadingRecords": "Загрузка товар(-ов)...",
    "zeroRecords": "Товар(-ы) отсутствуют.",
    "emptyTable": "Нет товаров",
    "paginate": {
        "first": "&lsaquo;&lsaquo;",
        "previous": "&lsaquo;",
        "next": "&rsaquo;",
        "last": "&rsaquo;&rsaquo;"
    },
    "aria": {
        "sortAscending": ": активировать для сортировки столбца по возрастанию",
        "sortDescending": ": активировать для сортировки столбца по убыванию"
    },
    "select": {
        "rows": {
            "_": " Выбрано строк: %d ",
            "0": "Кликните по записи для выбора",
            "1": " Выбрана одна строка "
        }
    },
    "searchBuilder": {
        "add": "Добавить условие",
        "button": {
            "0": "Фильтр <i class='bi bi-list'></i> ",
            "_": " (%d) Фильтр включен "
        },
        "clearAll": "Очистить все фильтры",
        "condition": "Условие:",
        "conditions": {
            "array": {
                "contains": "Содержит",
                "empty": "Пусто",
                "equals": "Совпадает",
                "not": "НЕ",
                "notEmpty": "НЕ Пусто",
                "without": "Без"
            },
            "date": {
                "after": "После",
                "before": "До",
                "between": "Между",
                "empty": "Пусто",
                "equals": "Совпадает",
                "not": "НЕ",
                "notBetween": "Вне диапазона",
                "notEmpty": "НЕ Пусто"
            },
            "number": {
                "between": "Между",
                "notBetween": "Вне диапазона",
                "empty": "Пусто",
                "notEmpty": "НЕ Пусто",
                "equals": "Равно",
                "not": "НЕ",
                "gt": "Больше чем",
                "gte": "Больше или Равно",
                "lt": "Меньше чем",
                "lte": "Меньше или Равно"
            },
            "string": {
                "contains": "Содержит в себе",
                "empty": "Пусто",
                "endsWith": "Заканчивается на",
                "equals": "Совпадает",
                "not": "НЕ",
                "notEmpty": "НЕ Пусто",
                "startsWith": "Начинается с"
            }
        },
        "data": "Данные",
        "deleteTitle": "Убрать правило",
        "leftTitle": "Outdent criteria",
        "logicAnd": "AND",
        "logicOr": "OR",
        "rightTitle": "Indent criteria",
        "title": {
            "0": "<h4 class='text-dark text-uppercase ms-6'>Настраиваемый Фильтр</h4>",
            "_": "<h4 class='text-dark text-uppercase ms-6'>Настраиваемый Фильтр (%d)</h4>"
        },
        "value": "Значение",
        "valueJoiner": "AND"
    }
}

// устаревшую escape заменил на encodeURIComponent
function setCookie(name, value, expires, path, domain, secure) {
    // if (typeof path == 'undefined') path = "/";
    let exp = new Date();
    let oneMonthFromNow = exp.getTime() + (100 * 24 * 60 * 60 * 1000);
    exp.setTime(oneMonthFromNow);
    let curCookie = name + '=' +
        encodeURIComponent(value) + ((expires) ? '; expires=' + exp.toGMTString() : '') +
        ((path) ? '; path=' + path : '') +
        ((domain) ? '; domain=' + domain : '') +
        ((secure) ? '; SameSite=None; Secure' : ' ; flavor=choco; SameSite=Lax');

    if ((name + '=' + encodeURIComponent(value)).length <= 4000) document.cookie = curCookie;
}

function zeroPad( num, numZeros ) {
    if ( num == 0 ) return "0"
    var an = Math.abs( num );
    var digitCount = 1 + Math.floor( Math.log( an ) / Math.LN10 );
    if ( digitCount >= numZeros ) {
        return num.toString();
    }
    var zeroString = Math.pow( 10, numZeros - digitCount ).toString().substr( 1 );
    return num < 0 ? '-' + zeroString + an : zeroString + an;
}

function num_word( value, Words ) {
    value = Math.abs( value ) % 100;
    var num = value % 10;
    if ( value > 10 && value < 20 ) return Words[ 2 ];
    if ( num > 1 && num < 5 ) return Words[ 1 ];
    if ( num == 1 ) return Words[ 0 ];
    return Words[ 2 ];
}

function setLocationAjax( curLoc ) {
    location.href = curLoc
    // location.hash = curLoc
}

function updateURL( newUrl ) {
    if ( history.pushState ) {
        // var baseUrl = window.location.protocol + "//" + window.location.host + window.location.pathname
        // let mrUrl = newUrl.replace( "/index.php?categoryID=", "/category_" ).replace( '&operation=get_data', '.html' ).replace( /[\=\&]/g, "_" )
        // history.pushState( null, null, mrUrl )
        history.pushState( null, null, newUrl )
    } else {
        console.warn( 'History API не поддерживает ваш браузер' )
    }
    // location.hash = '#' + newUrl;
}
// включаем все поповеры
var popoverTriggerList = [].slice.call( document.querySelectorAll( '[data-bs-toggle="popover"]' ) )
var popoverList = popoverTriggerList.map( function( popoverTriggerEl ) {
    return new bootstrap.Popover( popoverTriggerEl )
} )

function onClearClick( se ) {
    document.querySelectorAll( se )[ 0 ].value = ''
}

function cleanFormsWasValidated() {
    // console.log("cleanFormsValidity")
    let forms = document.querySelectorAll( '.start-validation' )
    Array.prototype.slice.call( forms ).forEach( function( form ) {
        form.classList.remove( 'was-validated' )
    } )
}

function JSgetParameterByName( name, url ) {
    if ( !url ) url = window.location.href
    name = name.replace( /[\[\]]/g, '\\$&' )
    var regex = new RegExp( '[?&]' + name + '(=([^&#]*)|&|#|$)' )
    var results = regex.exec( url )
    if ( !results ) return null;
    if ( !results[ 2 ] ) return '';
    return decodeURIComponent( results[ 2 ].replace( /\+/g, ' ' ) );
}

function _formatFloat( number, decimals = 2, dec_point = '.', thousands_sep = ' ' ) {
    decimals = ( typeof decimals == 'undefined' ) ? 9 : decimals;
    dec_point = ( typeof dec_point == 'undefined' ) ? '.' : dec_point;
    thousands_sep = ( typeof thousands_sep == 'undefined' ) ? '' : thousands_sep;
    String.prototype.format = function() {
        return this.replace( /(\d)(?=(\d{3})+(?!\d))/g, "$1" );
    };
    s = parseFloat( number ).toFixed( decimals ).toString().format();
    return s;
}

function _formatPrice( number, decimals = 2, dec_point = '.', thousands_sep = '' ) {
    decimals = ( typeof decimals == 'undefined' ) ? 3 : decimals;
    dec_point = ( typeof dec_point == 'undefined' ) ? '.' : dec_point;
    thousands_sep = ( typeof thousands_sep == 'undefined' ) ? ' ' : thousands_sep;
    // s=number.toLocaleString("ru-ru",{minimumFractionDigits:decimals, maximumFractionDigits:decimals});
    String.prototype.format = function() {
        return this.replace( /(\d)(?=(\d{3})+(?!\d))/g, "$1" + thousands_sep );
    };
    s = parseFloat( number ).toFixed( decimals ).toString().format();
    return s;
}

function makeBuyButton( data ) {
    let out_html = ''
    let form_html = ''
    let is_fake_clause = ''
    let doLoadLink = ''
    let btn_class = ''
    if ( data.productID ) {
        // let instock = data.in_stock.qnt
        let instock = data.in_stock_qnt
        if ( instock <= -1 ) {
            is_fake_clause = '&amp;is_fake=yes'
        }
        if ( instock > 0 ) {
            btn_class = 'btn btn-success btn-sm'
        } else if ( instock == -1 ) {
            btn_class = 'btn btn-secondary btn-sm'
        } else if ( instock <= -2 ) {
            btn_class = 'btn btn-outline-success btn-sm'
        }
        if ( ( instock != 0 ) && ( instock != -1000 ) ) {
            doLoadLink = "'do=cart&amp;addproduct=" + data.productID + "&amp;xcart=yes" + is_fake_clause + "&amp;multyaddcount='+document.HiddenFieldsForm_" + data.productID + ".multyaddcount.value"
            form_html = '<form class="d-flex flex-row align-items-center" name="HiddenFieldsForm_' + data.productID + '" id="HiddenFieldsForm_' + data.productID + '" action="index.php?categoryID=' + data.categoryID + '&amp;prdID=' + data.productID + '" method="post">'
            form_html += '<div class="input-group input-group-sm justify-content-center">' + '            <input type="number" min = "1" max = "' + data.qnt_to_show + '" step="1" name="multyaddcount" class="form-control text-end text-danger" value="1" style="min-width: 2rem!important;max-width: 6rem!important;">' + '                <a class="' + btn_class + '" onclick="doLoad(' + doLoadLink + ',' + data.productID + '); return false" type="button"> <i class="bi bi-cart3"></i>' + '                </a>' + '            </div>' + '</form>'
        } else if ( instock == 0 ) {
            form_html = '<div class="d-flex flex-row justify-content-center align-items-center"><a href="mailto:2842895@gmail.com?subject=Заказ&body=Запрос поставки товара: ' + data.Name + ' " class="btn btn-link text-decoration-none" title="Запросить поставку e-mail:2842895@gmail.com"><i class="bi bi-envelope-check"></i>e-mail</a></div>'
        } else {
            form_html = '<div class="d-flex flex-row justify-content-center align-items-center">' + 'Поставка прекращена' + '</div>'
        }
    }
    out_html = form_html
    return out_html
}

function newKeyUpDown( originalFunction, eventType ) {
    return function() {
        if ( "ontouchstart" in document.documentElement ) { // if it a touch device, or test here specifically for android chrome
            var $element = $( this ),
                $input = null;
            if ( /input/i.test( $element.prop( 'tagName' ) ) ) $input = $element;
            else if ( $( 'input', $element ).size() > 0 ) $input = $( $( 'input', $element ).get( 0 ) );
            if ( $input ) {
                var currentVal = $input.val(),
                    checkInterval = null;
                $input.focus( function( e ) {
                    clearInterval( checkInterval );
                    checkInterval = setInterval( function() {
                        if ( $input.val() != currentVal ) {
                            var event = jQuery.Event( eventType );
                            currentVal = $input.val();
                            event.which = event.keyCode = ( currentVal && currentVal.length > 0 ) ? currentVal.charCodeAt( currentVal.length - 1 ) : '';
                            $input.trigger( event );
                        }
                    }, 30 );
                } );
                $input.blur( function() {
                    clearInterval( checkInterval );
                } );
            }
        }
        return originalFunction.apply( this, arguments );
    }
}
$.fn.keyup = newKeyUpDown( $.fn.keyup, 'keyup' );
$.fn.keydown = newKeyUpDown( $.fn.keydown, 'keydown' );

function switchReadonly( subj, state ) {
    if ( typeof state == 'undefined' ) {
        if ( subj.hasAttribute( 'readonly' ) ) {
            subj.removeAttribute( 'readonly' )
        } else {
            subj.setAttribute( 'readonly', 'readonly' )
        }
    } else {
        if ( state == 1 ) {
            subj.setAttribute( 'readonly', 'readonly' )
        }
        if ( state == 0 ) {
            subj.removeAttribute( 'readonly' )
        }
    }
}

function cartplus( p ) {
    n = parseInt( document.getElementById( p ).value )
    document.getElementById( p ).value = n + 1
}

function cartminus( p ) {
    n = parseInt( document.getElementById( p ).value )
    if ( n > 1 ) document.getElementById( p ).value = n - 1
}

function htmlspecialchars_decode( string, quoteStyle = 3 ) {
    // eslint-disable-line camelcase
    //       discuss at: http://locutus.io/php/htmlspecialchars_decode/
    //      original by: Mirek Slugen
    //      improved by: Kevin van Zonneveld (http://kvz.io)
    //      bugfixed by: Mateusz "loonquawl" Zalega
    //      bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
    //      bugfixed by: Brett Zamir (http://brett-zamir.me)
    //      bugfixed by: Brett Zamir (http://brett-zamir.me)
    //         input by: ReverseSyntax
    //         input by: Slawomir Kaniecki
    //         input by: Scott Cariss
    //         input by: Francois
    //         input by: Ratheous
    //         input by: Mailfaker (http://www.weedem.fr/)
    //       revised by: Kevin van Zonneveld (http://kvz.io)
    // reimplemented by: Brett Zamir (http://brett-zamir.me)
    //        example 1: htmlspecialchars_decode("<p>this -&gt; &quot;</p>", 'ENT_NOQUOTES')
    //        returns 1: '<p>this -> &quot;</p>'
    //        example 2: htmlspecialchars_decode("&amp;quot;")
    //        returns 2: '&quot;'
    if ( typeof string !== "string" ) return null;
    var optTemp = 0
    var i = 0
    var noquotes = false
    if ( typeof quoteStyle === 'undefined' ) {
        quoteStyle = 2
    }
    string = string.toString().replace( /&lt;/g, '<' ).replace( /&gt;/g, '>' )
    var OPTS = {
        'ENT_NOQUOTES': 0,
        'ENT_HTML_QUOTE_SINGLE': 1,
        'ENT_HTML_QUOTE_DOUBLE': 2,
        'ENT_COMPAT': 2,
        'ENT_QUOTES': 3,
        'ENT_IGNORE': 4
    }
    if ( quoteStyle === 0 ) {
        noquotes = true
    }
    if ( typeof quoteStyle !== 'number' ) {
        // Allow for a single string or an array of string flags
        quoteStyle = [].concat( quoteStyle )
        for ( i = 0; i < quoteStyle.length; i++ ) {
            // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
            if ( OPTS[ quoteStyle[ i ] ] === 0 ) {
                noquotes = true
            } else if ( OPTS[ quoteStyle[ i ] ] ) {
                optTemp = optTemp | OPTS[ quoteStyle[ i ] ]
            }
        }
        quoteStyle = optTemp
    }
    if ( quoteStyle & OPTS.ENT_HTML_QUOTE_SINGLE ) {
        // PHP doesn't currently escape if more than one 0, but it should:
        string = string.replace( /&#0*39;/g, "'" )
        // This would also be useful here, but not a part of PHP:
        // string = string.replace(/&apos;|&#x0*27;/g, "'");
    }
    if ( !noquotes ) {
        string = string.replace( /&quot;/g, '"' )
    }
    // Put this in last place to avoid escape being double-decoded
    string = string.replace( /&amp;/g, '&' )
    return string
}
$( '#id_checkall_statuses' ).click( function() {
    if ( $( this ).is( ':checked' ) ) {
        $( '#statuses input[data-statusID]:checkbox' ).prop( 'checked', true )
        $( '#statuses span[data-statusID]' ).removeClass( 'text-muted' )
    } else {
        $( '#statuses input[data-statusID]:checkbox' ).prop( 'checked', false )
        $( '#statuses span[data-statusID]' ).addClass( 'text-muted' )
    }
} )
$( '#statuses input[data-statusID]:checkbox' ).on( 'change', function() {
    if ( $( '#statuses input[data-statusID]:checkbox:checked' ).length > 0 && $( '#statuses input[data-statusID]:checkbox:not(:checked)' ).length > 0 ) {
        document.getElementById( 'id_checkall_statuses' ).indeterminate = true
    } else {
        document.getElementById( 'id_checkall_statuses' ).indeterminate = false
    }
    let statusID = $( this ).attr( 'data-statusID' )
    if ( $( this ).is( ':checked' ) ) {
        $( '#statuses span[data-statusID ="' + statusID + '"]' ).removeClass( 'text-muted' )
    } else {
        $( '#statuses span[data-statusID ="' + statusID + '"]' ).addClass( 'text-muted' )
    }
} )
$( 'input[name="order_search_type"]' ).click( function() {
    let value = $( this ).val().toString()
    if ( $( this ).is( ':checked' ) && value === "SearchByOrderID" ) {
        $( '#statuses input[data-statusID]:checkbox' ).prop( 'disabled', true ).parents( 'list-group-item' ).addClass( 'disabled' )
        $( '#id_checkall_statuses' ).prop( 'disabled', true )
        $( '#orderID_textbox' ).removeAttr( 'disabled' )
        $( 'fieldset#SearchByOrderID' ).removeClass( 'visually-hidden' )
        $( 'fieldset#SearchByStatusID' ).addClass( 'visually-hidden' )
    }
    if ( $( this ).is( ':checked' ) && value === "SearchByStatusID" ) {
        $( '#statuses input[data-statusID]:checkbox' ).removeAttr( 'disabled' ).parents( 'list-group-item' ).removeClass( 'disabled' )
        $( '#id_checkall_statuses' ).removeAttr( 'disabled' )
        $( '#orderID_textbox' ).prop( 'disabled', true )
        $( 'fieldset#SearchByStatusID' ).removeClass( 'visually-hidden' )
        $( 'fieldset#SearchByOrderID' ).addClass( 'visually-hidden' )
    }
} )

function show_hide_password( t, id_selector ) {
    var e = document.getElementById( id_selector.toString() )
    return "password" == e.getAttribute( "type" ) ? ( t.classList.add( "view" ), e.setAttribute( "type", "text" ) ) : ( t.classList.remove( "view" ), e.setAttribute( "type", "password" ) ), !1
}

function confirmUnsubscribe() {
    temp = window.confirm( 'Вы уверены, что хотите отменить регистрацию в магазине?' );
    if ( temp ) {
        window.location = "index.php?killuser=yes";
    }
}
