// admin.js

function delay( ms ) {
    return new Promise( resolve => setTimeout( resolve, ms ) );
}


function setSelectValue(id, val) {
    document.getElementById(id).value = val;
}



function zeroPad(num, numZeros) {
    if (num == 0) return "0"
    let an = Math.abs(num);
    let digitCount = 1 + Math.floor(Math.log(an) / Math.LN10);
    if (digitCount >= numZeros) {
        return num.toString();
    }
    let zeroString = Math.pow(10, numZeros - digitCount).toString().substr(1);
    return num < 0 ? '-' + zeroString + an : zeroString + an;
}

function formatDateTime(date, $sep=' ') {

    let hh = date.getHours();
    if (hh < 10) hh = '0' + hh;
    let ii = date.getMinutes();
    if (ii < 10) ii = '0' + ii;
    let ss = date.getSeconds();
    if (ss < 10) ss = '0' + ss;


    let dd = date.getDate();
    if (dd < 10) dd = '0' + dd;

    let mm = date.getMonth() + 1;
    if (mm < 10) mm = '0' + mm;

    // let yy = date.getFullYear() % 100;
    // if (yy < 10) yy = '0' + yy;
    let yy = date.getFullYear();

    let result =  yy + '-' + mm + '-' + dd + ' '+$sep+ ' ' + hh + ':' + ii + ':' + ss;

    return result;
}

function formatDate(date, $sep=' ') {

    let dd = date.getDate();
    if (dd < 10) dd = '0' + dd;

    let mm = date.getMonth() + 1;
    if (mm < 10) mm = '0' + mm;

    // let yy = date.getFullYear() % 100;
    // if (yy < 10) yy = '0' + yy;
    let yy = date.getFullYear();

    let result =  yy + '-' + mm + '-' + dd ;

    return result;
}

function formatTime(date, $sep=' ') {

    let hh = date.getHours();
    if (hh < 10) hh = '0' + hh;
    let ii = date.getMinutes();
    if (ii < 10) ii = '0' + ii;
    let ss = date.getSeconds();
    if (ss < 10) ss = '0' + ss;

    let result =   hh + ':' + ii + ':' + ss;

    return result;
}

const settingsCurrency = {
    delimiter: ' ',
    numeral: true,
    numeralDecimalMark: '.',
    numeralDecimalScale: 4,
    numeralPositiveOnly: true,
    stripLeadingZeroes: false
};

const settingsOutPrice = {
    delimiter: ' ',
    numeral: true,
    numeralDecimalMark: '.',
    numeralDecimalScale: 2,
    numeralPositiveOnly: true,
    stripLeadingZeroes: false
};

const settingsFloat2 = {
    delimiter: ' ',
    numeral: true,
    numeralDecimalMark: '.',
    numeralDecimalScale: 2,
    stripLeadingZeroes: false
};

const settingsFloat = {
    delimiter: ' ',
    numeral: true,
    numeralDecimalMark: '.',
    numeralDecimalScale: 6,
    stripLeadingZeroes: false
};

const settingsFloatPositive = {
    delimiter: ' ',
    numeral: true,
    numeralDecimalMark: '.',
    numeralDecimalScale: 6,
    numeralPositiveOnly: true,
    stripLeadingZeroes: false
};

const settingsInteger = {
    delimiter: ' ',
    numeral: true,
    numeralDecimalMark: '.',
    numeralDecimalScale: 0,
    stripLeadingZeroes: false
};

const settingsIntegerPositive = {
    delimiter: ' ',
    numeral: true,
    numeralDecimalMark: '.',
    numeralDecimalScale: 0,
    numeralPositiveOnly: true,
    stripLeadingZeroes: false
};

function ff(number, decimals, dec_point, thousands_sep) {

    decimals = (typeof decimals == 'undefined') ? 4 : decimals;
    dec_point = (typeof dec_point == 'undefined') ? '.' : dec_point;
    thousands_sep = (typeof thousands_sep == 'undefined') ? '' : thousands_sep;
    String.prototype.format = function() {
        return this.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1");
    };
    let s = parseFloat(number).toFixed(decimals).toString().format();
    return s;
}

function _formatPriceShopCMS(number, decimals, dec_point, thousands_sep) {
    // BEGIN исправление ошибки при "отрицательном" округлении в настройке валют
    let c = Math.pow(10, decimals);
    let s = Math.round(number * c) / c;
    s = s.toString();
    let d = (s.split("."))[1];
    s = (s.split("."))[0];
    let t = '';
    thousands_sep = (typeof thousands_sep == 'undefined') ? ' ' : thousands_sep;
    dec_point = (typeof dec_point == 'undefined') ? '.' : dec_point;
    for (let i = s.length - 1; i >= 0; i--) {
        t = s.charAt(i) + t;
        if ((s.length - i) % 3 == 0 && i > 0) t = thousands_sep + t;
    }
    s = t + ((typeof d == 'undefined') ? '' : (dec_point + d));
    return s;
}









function fff(number, options) {
    decimals = options.numeralDecimalScale;
    dec_point = options.numeralDecimalMark;
    delimiter = (typeof options.delimiter == 'undefined') ? '' : options.delimiter;
    String.prototype.format = function() {
        return this.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1");
    };
    let s = parseFloat(number).toFixed(decimals).toString().format();
    return s;
}

function onClearClick(se) {
    document.querySelectorAll(se)[0].value = '';
}

function onResetClick(se) {
    document.querySelectorAll(se)[0].value = "0.00";
}

function confirmDuplicate(id, ask, url, newname) {
    if (!newname) {
        newname = 'Copy of ' + id
    };
    let temp = window.prompt(ask, newname);
    if (temp) {
        window.location = url + id;
    }
}

function confirmDelete(id, ask, url) {
    let temp = window.confirm(ask);
    if (temp) {
        window.location = url + id;
    }
}

function confirmDeletep(question, where) {
    temp = window.confirm(question);
    if (temp) {
        window.location = where;
    }
}

function confirmDeletef(id, ask) {
    let temp = window.confirm(ask);
    if (temp) {
        document.getElementById(id).submit();
    }
}

function window_refresh() {
    let link = window.location.href;
    window.location = link;
}

function open_window(link, w, h) {
    let win = "width=" + w + ",height=" + h + ",menubar=no,location=no,resizable=yes,scrollbars=yes";
    let newWin = window.open(link, 'newWin', win);
}

function checkOnUrl(url) {
    let urlArrayDot = url.split('.');
    if (urlArrayDot[urlArrayDot.length - 1] === 'html') {
        urlArrayDot.pop();
        let newUrl = urlArrayDot.join('.');
        let urlArraySlash = newUrl.split('/');
        urlArraySlash.pop();
        newUrl = urlArraySlash.join('/') + '/';
        return newUrl;
    }
    return url;
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
    let optTemp = 0;
    let i = 0;
    let noquotes = false;
    if ( typeof quoteStyle === 'undefined' ) {
        quoteStyle = 2;
    }
    string = string.toString().replace( /&lt;/g, '<' ).replace( /&gt;/g, '>' )
    let OPTS = {
        'ENT_NOQUOTES': 0,
        'ENT_HTML_QUOTE_SINGLE': 1,
        'ENT_HTML_QUOTE_DOUBLE': 2,
        'ENT_COMPAT': 2,
        'ENT_QUOTES': 3,
        'ENT_IGNORE': 4
    }
    if ( quoteStyle === 0 ) {
        noquotes = true;
    }
    if ( typeof quoteStyle !== 'number' ) {
        // Allow for a single string or an array of string flags
        quoteStyle = [].concat( quoteStyle );
        for ( i = 0; i < quoteStyle.length; i++ ) {
            // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
            if ( OPTS[ quoteStyle[ i ] ] === 0 ) {
                noquotes = true;
            } else if ( OPTS[ quoteStyle[ i ] ] ) {
                optTemp = optTemp | OPTS[ quoteStyle[ i ] ];
            }
        }
        quoteStyle = optTemp;
    }
    if ( quoteStyle & OPTS.ENT_HTML_QUOTE_SINGLE ) {
        // PHP doesn't currently escape if more than one 0, but it should:
        string = string.replace( /&#0*39;/g, "'" );
        // This would also be useful here, but not a part of PHP:
        // string = string.replace(/&apos;|&#x0*27;/g, "'");
    }
    if ( !noquotes ) {
        string = string.replace( /&quot;/g, '"' );
    }
    // Put this in last place to avoid escape being double-decoded
    string = string.replace( /&amp;/g, '&' );
    return string;
}

function SettingsSelect_change(id, dpt, sub) {
    let aSelect = $('#setting_' + id);
    let aButton = $('#btn_setting_' + id);
    let aID = aSelect.val();
    let newHref = 'admin.php?dpt=' + dpt + '&sub=' + sub + '&edit_mode=' + aID;
    aButton.attr("href", newHref);
}
// функция раскодирования адресной строки
// query string: ?foo=lorem&bar=&baz
// let foo = JSgetParameterByName('foo'); // "lorem"
// let bar = JSgetParameterByName('bar'); // "" (present with empty value)
// let baz = JSgetParameterByName('baz'); // "" (present with no value)
// let qux = JSgetParameterByName('qux'); // null (absent)
function JSgetParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, '\\$&');
    let regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, ' '));
}

function JSdeleteParameterByName(name, url) {
    let new_url;
    if (!url) {
        new_url = new URL(window.location.href);
    } else {
        new_url = new URL(url);
    }
    if (!name) {
        name = 'no_time_filter';
    }
    new_url.searchParams.delete(name);
    return window.history.pushState({}, document.title, new_url);
}
//события
// orders
function _formatPrice(number, decimals, dec_point, thousands_sep) {
    decimals = (typeof decimals == 'undefined') ? 3 : decimals;
    dec_point = (typeof dec_point == 'undefined') ? '.' : dec_point;
    thousands_sep = (typeof thousands_sep == 'undefined') ? ' ' : thousands_sep;
    // s=number.toLocaleString("ru-ru",{minimumFractionDigits:decimals, maximumFractionDigits:decimals});
    String.prototype.format = function() {
        return this.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1" + thousands_sep);
    };
    s = parseFloat(number).toFixed(decimals).toString().format();
    return s;
}

function _formatFloat(number, decimals, dec_point, thousands_sep) {
    decimals = (typeof decimals == 'undefined') ? 9 : decimals;
    dec_point = (typeof dec_point == 'undefined') ? '.' : dec_point;
    thousands_sep = (typeof thousands_sep == 'undefined') ? '' : thousands_sep;
    String.prototype.format = function() {
        return this.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1");
    };
    s = parseFloat(number).toFixed(decimals).toString().format();
    return s;
}
let click_event = new Event("click");
let change_event = new Event("change");
let keyup_event = new Event("keyup");
let input_event = new Event("input");

function _input_plus1(input_id, GenerateEvent) {
    GenerateEvent = (typeof GenerateEvent == 'undefined') ? 1 : GenerateEvent;
    n = parseFloat(document.getElementById(input_id).value);
    document.getElementById(input_id).value = (n + 1);
    if (GenerateEvent) document.getElementById(input_id).dispatchEvent(input_event);
}

function _input_minus1(input_id, GenerateEvent) {
    GenerateEvent = (typeof GenerateEvent == 'undefined') ? 1 : GenerateEvent;
    n = parseFloat(document.getElementById(input_id).value);
    if (n > 1) document.getElementById(input_id).value = (n - 1);
    if (GenerateEvent) document.getElementById(input_id).dispatchEvent(input_event);
}

function str_split(string, length) {
    let chunks, len, pos;
    string = (string == null) ? "" : string;
    length = (length == null) ? 1 : length;
    chunks = [];
    pos = 0;
    len = string.length;
    while (pos < len) {
        chunks.push(string.slice(pos, pos += length));
    }
    return chunks;
}

function number_to_sumstring(num, CID) {

    switch (+CID) {
        // switch(ISO3){
        case 3:
            // case 'RUR':
            // return num.toPhraseRus('RUB');
            return num.toPhraseRus('RUR');
            break;
        case 4:
        case 5:
            // case 'BYN':
            return num.toPhraseRus('BYN');
            break;
        case 1:
            // case 'USD':
            return num.toPhraseRus('USD');
            break;
        case 2:
            // case 'EUR':
            return num.toPhraseRus('EUR');
            break;
        case -1:
            // case 'XYZ':
            return num.toPhraseRus('INTEGER');
            break;
        default:
            return num.toPhraseRus();
            break;
    }



}

function number_to_sumstringOLD(num, CID) {
    CID = (typeof CID == 'undefined') ? "2" : CID;
    // ISO3 = ( typeof ISO3 == 'undefined' ) ? "BYN" : ISO3;
    let morph = function(number, titles) {
        let cases = [2, 0, 1, 1, 1, 2];
        return titles[((number % 100) > 4 && (number % 100) < 20) ? 2 : cases[Math.min((number % 10), 5)]];
    };
    let def_translite = {
        null: 'ноль',
        a1: ['один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'],
        a2: ['одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'],
        a10: ['десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать'],
        a20: ['двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто'],
        a100: ['сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот'],
        ukop: ['копейка', 'копейки', 'копеек'],
        uXZkop: ['х/з', 'х/з', 'х/з'],
        ucent: ['цент', 'цента', 'центов'],
        ueucent: ['евроцент', 'евроцента', 'евроцентов'],
        ur: ['рубль', 'рубля', 'рублей'],
        uXZr: ['Х/З', 'Х/З', 'Х/З'],
        uru: ['российский рубль', 'российских рубля', 'российских рублей'],
        urb: ['белорусский рубль', 'белорусских рубля', 'белорусских рублей'],
        usd: ['Доллар', 'Доллара', 'Долларов'],
        ueu: ['Евро', 'Евро', 'Евро'],
        u3: ['тысяча', 'тысячи', 'тысяч'],
        u2: ['миллион', 'миллиона', 'миллионов'],
        u1: ['миллиард', 'миллиарда', 'миллиардов'],
    }
    let i1, i2, i3, kop, out, rub, v, zeros, _ref, _ref1, _ref2, ax;
    _ref = parseFloat(num).toFixed(2).split('.'), rub = _ref[0], kop = _ref[1];
    let leading_zeros = 12 - rub.length;
    if (leading_zeros < 0) {
        return false;
    }
    zeros = [];
    while (leading_zeros--) {
        zeros.push('0');
    }
    rub = zeros.join('') + rub;
    out = [];
    if (rub > 0) {
        // Разбиваем число по три символа
        _ref1 = str_split(rub, 3);
        for (let i = -1; i < _ref1.length; i++) {
            v = _ref1[i];
            if (!(v > 0)) continue;
            _ref2 = str_split(v, 1), i1 = parseInt(_ref2[0]), i2 = parseInt(_ref2[1]), i3 = parseInt(_ref2[2]);
            out.push(def_translite.a100[i1 - 1]); // 1xx-9xx
            ax = (i + 1 == 3) ? 'a2' : 'a1';
            if (i2 > 1) {
                out.push(def_translite.a20[i2 - 2] + (i3 > 0 ? ' ' + def_translite[ax][i3 - 1] : '')); // 20-99
            } else {
                out.push(i2 > 0 ? def_translite.a10[i3] : def_translite[ax][i3 - 1]); // 10-19 | 1-9
            }
            if (_ref1.length > i + 1) {
                let name = def_translite['u' + (i + 1)];
                out.push(morph(v, name));
            }
        }
    } else {
        out.push(def_translite.null);
    }
    // Дописываем название "рубли"
    // Дописываем название "копейка"
    // console.warn( CID );
    switch (CID) {
        // switch(ISO3){
        case '3':
            // case 'RUR':
            out.push(morph(rub, def_translite.uru)); // Российских рублей
            out.push(kop + ' ' + morph(kop, def_translite.ukop));
            break;
        case '4':
        case '5':
            // case 'BYN':
            out.push(morph(rub, def_translite.urb)); // белорусских рублей
            out.push(kop + ' ' + morph(kop, def_translite.ukop));
            break;
        case '1':
            // case 'USD':
            out.push(morph(rub, def_translite.usd)); // долларов
            out.push(kop + ' ' + morph(kop, def_translite.ucent));
            break;
        case '2':
            // case 'EUR':
            out.push(morph(rub, def_translite.ueu)); // Евро
            out.push(kop + ' ' + morph(kop, def_translite.ueucent));
            break;
        case '-1':
            // case 'XYZ':
            out.push(morph(rub, def_translite.uXZr)); // Евро
            out.push(kop + ' ' + morph(kop, def_translite.uXZkop));
            break;
        default:
            out.push(morph(rub, def_translite.ur)); // просто рублей
            out.push(kop + ' ' + morph(kop, def_translite.ukop));
            break;
    }
    // Объединяем маcсив в строку, удаляем лишние пробелы и возвращаем результат
    result = out.join(' ').replace(RegExp(' {2,}', 'g'), ' ').trimLeft();
    return result.charAt(0).toUpperCase() + result.slice(1); // Первую букву заглавной
}


function integerToWords(number) {
    return number.toPhraseRus('INTEGER');
}


// сумма прописью для чисел от 0 до 999 триллионов
// можно передать параметр "валюта": RUB,USD,EUR (по умолчанию RUB)
Number.prototype.toPhraseRus = function(c) {


    let x = this.toFixed(2);


    if (x < 0 || x > 999999999999999.99) return false;


    let currency = 'BYN';
    let isInteger = false;


    if (typeof(c) == 'string') {

        currency = c.trim().toUpperCase();


        if (currency == 'INTEGER') {
            isInteger = true;
            x = this.toFixed(0);
            currency = 'EUR';
        }
    }



    if (currency == 'BYN') {
        currency = 'BYN';
    }

    if (currency != 'BYN' && currency != 'RUB' && currency != 'RUR' && currency != 'USD' && currency != 'EUR') return false;

    let groups = new Array();

    groups[0] = new Array();
    groups[1] = new Array();
    groups[2] = new Array();
    groups[3] = new Array();
    groups[4] = new Array();
    groups[9] = new Array();
    // рубли
    // по умолчанию
    groups[0][-1] = {
        'BYN': "белоруских рублей",
        'RUR': 'рашистских рублей',
        'RUB': 'рублей',
        'USD': 'долларов США',
        'EUR': 'евро'
    };
    //исключения
    groups[0][1] = {
        'BYN': "белорусский рубль",
        'RUR': 'рашистский рубль',
        'RUB': 'рубль',
        'USD': 'доллар США',
        'EUR': 'евро'
    };
    groups[0][2] = {
        'BYN': "белорусских рубля",
        'RUR': 'рашистских рубля',
        'RUB': 'рубля',
        'USD': 'доллара США',
        'EUR': 'евро'
    };
    groups[0][3] = {
        'BYN': "белорусских рубля",
        'RUR': 'рашистских рубля',
        'RUB': 'рубля',
        'USD': 'доллара США',
        'EUR': 'евро'
    };
    groups[0][4] = {
        'BYN': "белорусских рубля",
        'RUR': 'рашистских рубля',
        'RUB': 'рубля',
        'USD': 'доллара США',
        'EUR': 'евро'
    };




    if (isInteger == true) {

        groups[0][-1] = {
            'BYN': "",
            'RUR': '',
            'RUB': '',
            'USD': '',
            'EUR': ''
        };
        //исключения
        groups[0][1] = {
            'BYN': "",
            'RUR': '',
            'RUB': '',
            'USD': '',
            'EUR': ''
        };
        groups[0][2] = {
            'BYN': "",
            'RUR': '',
            'RUB': '',
            'USD': '',
            'EUR': ''
        };
        groups[0][3] = {
            'BYN': "",
            'RUR': '',
            'RUB': '',
            'USD': '',
            'EUR': ''
        };
        groups[0][4] = {
            'BYN': "",
            'RUR': '',
            'RUB': '',
            'USD': '',
            'EUR': ''
        };
    }






    // тысячи
    // по умолчанию
    groups[1][-1] = 'тысяч';
    //исключения
    groups[1][1] = 'тысяча';
    groups[1][2] = 'тысячи';
    groups[1][3] = 'тысячи';
    groups[1][4] = 'тысячи';
    // миллионы
    // по умолчанию
    groups[2][-1] = 'миллионов';
    //исключения
    groups[2][1] = 'миллион';
    groups[2][2] = 'миллиона';
    groups[2][3] = 'миллиона';
    groups[2][4] = 'миллиона';
    // миллиарды
    // по умолчанию
    groups[3][-1] = 'миллиардов';
    //исключения
    groups[3][1] = 'миллиард';
    groups[3][2] = 'миллиарда';
    groups[3][3] = 'миллиарда';
    groups[3][4] = 'миллиарда';
    // триллионы
    // по умолчанию
    groups[4][-1] = 'триллионов';
    //исключения
    groups[4][1] = 'триллион';
    groups[4][2] = 'триллиона';
    groups[4][3] = 'триллиона';
    groups[4][4] = 'триллиона';
    // копейки
    // по умолчанию
    groups[9][-1] = {
        'BYN': "копеек",
        'RUR': 'хутин Пуйло',
        'RUB': 'копеек',
        'USD': 'центов',
        'EUR': 'центов'
    };
    //исключения
    groups[9][1] = {
        'BYN': "копейка",
        'RUR': 'хутин Пуйло',
        'RUB': 'копейка',
        'USD': 'цент',
        'EUR': 'евроцент'
    };
    groups[9][2] = {
        'BYN': "копейки",
        'RUR': 'хутин Пуйло',
        'RUB': 'копейки',
        'USD': 'цента',
        'EUR': 'евроцента'
    };
    groups[9][3] = {
        'BYN': "копейки",
        'RUR': 'хутин Пуйло',
        'RUB': 'копейки',
        'USD': 'цента',
        'EUR': 'евроцента'
    };
    groups[9][4] = {
        'BYN': "копейки",
        'RUR': 'хутин Пуйло',
        'RUB': 'копейки',
        'USD': 'цента',
        'EUR': 'евроцента'
    };

    if (isInteger == true) {

        groups[9][-1] = {
            'BYN': "",
            'RUR': 'хутин Пуйло',
            'RUB': '',
            'USD': '',
            'EUR': ''
        };
        //исключения
        groups[9][1] = {
            'BYN': "",
            'RUR': 'хутин Пуйло',
            'RUB': '',
            'USD': '',
            'EUR': ''
        };
        groups[9][2] = {
            'BYN': "",
            'RUR': 'хутин Пуйло',
            'RUB': '',
            'USD': '',
            'EUR': ''
        };
        groups[9][3] = {
            'BYN': "",
            'RUR': 'хутин Пуйло',
            'RUB': '',
            'USD': '',
            'EUR': ''
        };
        groups[9][4] = {
            'BYN': "",
            'RUR': 'хутин Пуйло',
            'RUB': '',
            'USD': '',
            'EUR': ''
        };

    }




    // цифры и числа
    // либо просто строка, либо 4 строки в хэше
    let names = new Array();
    names[1] = {
        0: 'один',
        1: 'одна',
        2: 'один',
        3: 'один',
        4: 'один'
    };
    names[2] = {
        0: 'два',
        1: 'две',
        2: 'два',
        3: 'два',
        4: 'два'
    };
    names[3] = 'три';
    names[4] = 'четыре';
    names[5] = 'пять';
    names[6] = 'шесть';
    names[7] = 'семь';
    names[8] = 'восемь';
    names[9] = 'девять';
    names[10] = 'десять';
    names[11] = 'одиннадцать';
    names[12] = 'двенадцать';
    names[13] = 'тринадцать';
    names[14] = 'четырнадцать';
    names[15] = 'пятнадцать';
    names[16] = 'шестнадцать';
    names[17] = 'семнадцать';
    names[18] = 'восемнадцать';
    names[19] = 'девятнадцать';
    names[20] = 'двадцать';
    names[30] = 'тридцать';
    names[40] = 'сорок';
    names[50] = 'пятьдесят';
    names[60] = 'шестьдесят';
    names[70] = 'семьдесят';
    names[80] = 'восемьдесят';
    names[90] = 'девяносто';
    names[100] = 'сто';
    names[200] = 'двести';
    names[300] = 'триста';
    names[400] = 'четыреста';
    names[500] = 'пятьсот';
    names[600] = 'шестьсот';
    names[700] = 'семьсот';
    names[800] = 'восемьсот';
    names[900] = 'девятьсот';
    let r = '';
    let i, j;
    let y = Math.floor(x);
    // если НЕ ноль рублей
    if (y > 0) {

        // выделим тройки с руб., тыс., миллионами, миллиардами и триллионами
        let t = new Array();

        for (i = 0; i <= 4; i++) {
            t[i] = y % 1000;
            y = Math.floor(y / 1000);
        }


        let d = new Array();
        // выделим в каждой тройке сотни, десятки и единицы
        for (i = 0; i <= 4; i++) {
            d[i] = new Array();
            d[i][0] = t[i] % 10; // единицы
            d[i][10] = t[i] % 100 - d[i][0]; // десятки
            d[i][100] = t[i] - d[i][10] - d[i][0]; // сотни
            d[i][11] = t[i] % 100; // две правых цифры в виде числа
        }


        for (i = 4; i >= 0; i--) {
            if (t[i] > 0) {
                if (names[d[i][100]]) r += ' ' + ((typeof(names[d[i][100]]) == 'object') ? (names[d[i][100]][i]) : (names[d[i][100]]));
                if (names[d[i][11]]) r += ' ' + ((typeof(names[d[i][11]]) == 'object') ? (names[d[i][11]][i]) : (names[d[i][11]]));
                else {
                    if (names[d[i][10]]) r += ' ' + ((typeof(names[d[i][10]]) == 'object') ? (names[d[i][10]][i]) : (names[d[i][10]]));
                    if (names[d[i][0]]) r += ' ' + ((typeof(names[d[i][0]]) == 'object') ? (names[d[i][0]][i]) : (names[d[i][0]]));
                }
                if (names[d[i][11]]) // если существует числительное
                    j = d[i][11];
                else j = d[i][0];
                if (groups[i][j]) {
                    if (i == 0) r += ' ' + groups[i][j][currency];
                    else r += ' ' + groups[i][j];
                } else {
                    if (i == 0) r += ' ' + groups[i][-1][currency];
                    else r += ' ' + groups[i][-1];
                }
            }
        }


        if (t[0] == 0) {
            r += ' ' + groups[0][-1][currency];
        }


    } else {
        r = 'Ноль ' + groups[0][-1][currency];
    }

    if (isInteger != true) {

        y = ((x - Math.floor(x)) * 100).toFixed();
        if (y < 10) y = '0' + y;
        r = r.trim();
        r = r.substr(0, 1).toUpperCase() + r.substr(1);
        r += ' ' + y;
        y = y * 1;
        if (names[y]) // если существует числительное
            j = y;
        else j = y % 10;

        if (groups[9][j]) {
            r += ' ' + groups[9][j][currency];
        } else {
            r += ' ' + groups[9][-1][currency];
        }
    }

    return r;
}