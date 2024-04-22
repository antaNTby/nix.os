<?php

// number_format(
// float $num,
// int $decimals = 0,
// ?string $decimal_separator = ".",
// ?string $thousands_separator = ","
// ): string
// Форматирует число сгруппированными тысячами и, возможно, десятичными цифрами.

// round(int|float $num, int $precision = 0, int $mode = PHP_ROUND_HALF_UP): float
// Возвращает округлённое значение num с указанной точностью precision (количество цифр после запятой). precision может быть отрицательным или нулём (по умолчанию).
// _formatPrice  -вначале округляем потом форматируем
// number_format  -форматируем без округления
/*

// Пример использования оператора
$action = $_POST['action'] ?? 'default';

// Пример выше аналогичен следующему коду
if (isset($_POST['action'])) {
$action = $_POST['action'];
} else {
$action = 'default';
}

 */

// *****************************************************************************
// Purpose        round float value to 0.01 precision
// Inputs           $float_value - value to float
// Remarks
// Returns        rounded value
function roundf( $float_value ) {
    return round( 100 * $float_value ) / 100;
}

// function _formatPrice( $price, $rval = 2, $dec = '.', $term = ' ' ) {
function _formatPrice(
    $price,
    $rval = 2,
    $dec = '.',
    $thousands_separator = ''
) {
    return number_format( round( $price, $rval ), $rval, $dec, $thousands_separator );
}

function checkUNP( $string ) {
    $temp = preg_replace( "/\D/", "", $string );
    if ( strlen( $temp ) != 9 ) {
        return false;
    } else {
        return $temp;
    }
}

function myBind(
    &$a,
    $val,
    $type
) {
    $key = ':binding_' . count( $a );

    $a[] = array(
        'key'  => $key,
        'val'  => $val,
        'type' => $type,
    );
    return $key;
}

function _getCompanyPDO(
    $companyID,
    $PDO_connect
) {

    $TableCompanies   = COMPANIES_TABLE;
    $TableOrders      = ORDERS_TABLE;
    $TableInvoices    = NANO_INVOICES_TABLE;
    $TableOLDInvoices = INVOICES_TABLE;

    $companies         = [];
    $sql_query_company = "SELECT * FROM `" . COMPANIES_TABLE . "` WHERE `companyID`=" . (int)$companyID . ";";
    $companies         = adminSSP::pdo_query_assoc( $PDO_connect, $bindings, $sql_query_company, 0 );

    $company = $companies[0];

    if ( !$company["company_unp"] && !$company["company_name"] && !$company["company_adress"] && !$company["company_email"] ) {
        $company["company_unp"]  = "! ERROR !";
        $company["company_name"] = "КОМПАНИЯ НЕ СУЩЕСТВУЕТ | НЕВЕРНЫЕ РЕКВИЗИТЫ";
        $company["company_adress"] = "КОМПАНИЯ НЕ СУЩЕСТВУЕТ | НЕВЕРНЫЕ РЕКВИЗИТЫ";
        $company["company_email"] = "КОМПАНИЯ НЕ СУЩЕСТВУЕТ | НЕВЕРНЫЕ РЕКВИЗИТЫ";

        return $company;
    }

    if ( $company["company_director"] ) {

        $arr_d                          = array();
        $arr_d                          = unserialize( $company["company_director"] );
        $company["director_nominative"] = $arr_d[0];
        $company["director_genitive"]   = $arr_d[1];
        $company["director_reason"]     = $arr_d[2];
    }

    $sql_query = "
    SELECT `companyID`,`company_unp`,`company_title`,`creation_time`,`update_time`,`read_only`" .
    " FROM `" . COMPANIES_TABLE .
    "` WHERE `company_unp` =" . (int)$company["company_unp"] .
        " ORDER BY `companyID` DESC;";

    $companiesWithSameUNP = adminSSP::pdo_query_assoc( $PDO_connect, $bindings, $sql_query, 0 );

    foreach ( $companiesWithSameUNP as $key => $value ) {
        $sql_query                                       = "SELECT COUNT(`companyID`) AS `orders_count` FROM `{$TableOrders}`  WHERE `companyID`={$value['companyID']};";
        $companiesWithSameUNP[$key]["orders_count"]      = adminSSP::pdo_query_assoc( $PDO_connect, $bindings, $sql_query, 0 )[0]['orders_count'];
        $sql_query                                       = "SELECT COUNT(`sellerID`) AS `sellers_count` FROM `{$TableInvoices}`  WHERE `sellerID`={$value['companyID']};";
        $companiesWithSameUNP[$key]["sellers_count"]     = adminSSP::pdo_query_assoc( $PDO_connect, $bindings, $sql_query, 0 )[0]['sellers_count'];
        $sql_query                                       = "SELECT COUNT(`buyerID`) AS `buyers_count` FROM `{$TableInvoices}` WHERE `buyerID`={$value['companyID']};";
        $companiesWithSameUNP[$key]["buyers_count"]      = adminSSP::pdo_query_assoc( $PDO_connect, $bindings, $sql_query, 0 )[0]['buyers_count'];
        $sql_query                                       = "SELECT COUNT(`sellerID`) AS `old_sellers_count` FROM `{$TableOLDInvoices}`  WHERE `sellerID`={$value['companyID']};";
        $companiesWithSameUNP[$key]["old_sellers_count"] = adminSSP::pdo_query_assoc( $PDO_connect, $bindings, $sql_query, 0 )[0]['old_sellers_count'];
        $sql_query                                       = "SELECT COUNT(`buyerID`) AS `old_buyers_count` FROM `{$TableOLDInvoices}` WHERE `buyerID`={$value['companyID']};";
        $companiesWithSameUNP[$key]["old_buyers_count"]  = adminSSP::pdo_query_assoc( $PDO_connect, $bindings, $sql_query, 0 )[0]['old_buyers_count'];

        if ( $company["companyID"] == $value["companyID"] ) {
            $company["orders_count"]      = $companiesWithSameUNP[$key]["orders_count"];
            $company["sellers_count"]     = $companiesWithSameUNP[$key]["sellers_count"];
            $company["buyers_count"]      = $companiesWithSameUNP[$key]["buyers_count"];
            $company["old_sellers_count"] = $companiesWithSameUNP[$key]["old_sellers_count"];
            $company["old_buyers_count"]  = $companiesWithSameUNP[$key]["old_buyers_count"];

        }
    }

    $company["variants"] = array();
    $company["variants"] = $companiesWithSameUNP;

    return $company;
}

function getProductByProductID( $productID ) {
    $Product = array(
        "vendorID"         => 0,
        "categoryID"       => 0,
        "productID"        => 0,
        "productPrice"     => 100,
        "in_stock"         => 1,
        "name"             => "Название товара по-умолчанию",
        "shortname"        => "Название товара по-умолчанию",
        "min_order_amount" => 1,
        "free_shipping"    => 1,
        "items_sold"       => 0,
    );

    $q = db_query( "SELECT vendorID,categoryID,productID, Price as productPrice, in_stock, name, LEFT(name,64) as shortname, min_order_amount, free_shipping, items_sold  FROM " . PRODUCTS_TABLE . " WHERE productID=" . (int)$productID );

    $Product = db_fetch_row( $q );

    $Product["myInStock"] = getMyInStock( $Product["in_stock"] );

    return $Product;
}

function getMyInStock( $d = 1 ) {

    $out  = array();
    $data = (int)$d;

    switch ( $data ) {

        case ( 0 ):

            $out["qnt"]             = 0;
            $out["qnt_to_show"]     = 0;
            $out["in_stock_string"] = "нет на складе";
            break;

        case ( -1 ):
            $out["qnt"]             = -1;
            $out["qnt_to_show"]     = 5;
            $out["in_stock_string"] = "ожидаем";
            break;

        case ( -1000 ):
            $out["qnt"]             = -1000;
            $out["qnt_to_show"]     = 0;
            $out["in_stock_string"] = "снят с продажи";
            break;

        case ( 9999 ):
            $out["qnt"]             = 1000;
            $out["qnt_to_show"]     = 1000;
            $out["in_stock_string"] = "много";

            // $out["timestamp"] = 0;
            break;

        case ( $data <= -2 ):
            $abs_d              = abs( $data );
            $out["qnt"]         = -2;
            $out["qnt_to_show"] = generateFakedInStockCount( $data );

            if ( $abs_d <= 14 ) {
                $out["in_stock_string"] = "+" . $abs_d . "&nbsp;" . say_to_russian( $abs_d, "дня", "дня", "дней" );
            } elseif (  ( $abs_d > 14 ) && ( $abs_d < 60 ) ) {
                $abs_d                  = ceil( $abs_d / 7 );
                $out["in_stock_string"] = "+" . $abs_d . "&nbsp;" . say_to_russian( $abs_d, "неделя", "недели", "недель" );
            } elseif (  ( $abs_d >= 60 ) && ( $abs_d < 1000 ) ) {
                $abs_d                  = ceil( $abs_d / 30 );
                $out["in_stock_string"] = "+" . $abs_d . "&nbsp;" . say_to_russian( $abs_d, "месяца", "месяца", "месяцев" );
            }
            break;

        case ( $data >= 1 ):
            $out["qnt"]             = $data;
            $out["qnt_to_show"]     = $data;
            $out["in_stock_string"] = "{$data} шт. на складе";
            break;

        default:
            $out["qnt"]             = $data;
            $out["qnt_to_show"]     = $data;
            $out["in_stock_string"] = "непонятно";
            break;
    }

    return $out;
}

# You may use it as such:
# // header_status(500);
# // if (that_happened) {
# //     die("that happened")
# // }
# // if (something_else_happened) {
# //     die("something else happened")
# // }
# // update_database();
# // header_status(200);
#You may use the following function to send a status change:
function header_status( $statusCode ) {
    static $status_codes = null;

    if ( $status_codes === null ) {
        $status_codes = array(
            100 => "Continue",
            101 => "Switching Protocols",
            102 => "Processing",
            200 => "OK",
            201 => "Created",
            202 => "Accepted",
            203 => "Non-Authoritative Information",
            204 => "No Content",
            205 => "Reset Content",
            206 => "Partial Content",
            207 => "Multi-Status",
            300 => "Multiple Choices",
            301 => "Moved Permanently",
            302 => "Found",
            303 => "See Other",
            304 => "Not Modified",
            305 => "Use Proxy",
            307 => "Temporary Redirect",
            400 => "Bad Request",
            401 => "Unauthorized",
            402 => "Payment Required",
            403 => "Forbidden",
            404 => "Not Found",
            405 => "Method Not Allowed",
            406 => "Not Acceptable",
            407 => "Proxy Authentication Required",
            408 => "Request Timeout",
            409 => "Conflict",
            410 => "Gone",
            411 => "Length Required",
            412 => "Precondition Failed",
            413 => "Request Entity Too Large",
            414 => "Request-URI Too Long",
            415 => "Unsupported Media Type",
            416 => "Requested Range Not Satisfiable",
            417 => "Expectation Failed",
            422 => "Unprocessable Entity",
            423 => "Locked",
            424 => "Failed Dependency",
            426 => "Upgrade Required",
            500 => "Internal Server Error",
            501 => "Not Implemented",
            502 => "Bad Gateway",
            503 => "Service Unavailable",
            504 => "Gateway Timeout",
            505 => "HTTP Version Not Supported",
            506 => "Variant Also Negotiates",
            507 => "Insufficient Storage",
            509 => "Bandwidth Limit Exceeded",
            510 => "Not Extended",
        );
    }

    if ( $status_codes[$statusCode] !== null ) {
        $status_string = $statusCode . " " . $status_codes[$statusCode];
        header( $_SERVER["SERVER_PROTOCOL"] . " " . $status_string, true, $statusCode );
    }
}

# htmlspecialchars — Преобразует специальные символы в HTML-сущности
function ToText( $str ) {
    $str = htmlspecialchars( trim( $str ), ENT_QUOTES );
    return $str;
}

function xToText( $str ) {
    $str = xEscSQL( xHtmlSpecialChars( $str ) );
    return $str;
}

// просто экранируем кавычки real_escape_string() - вызывает перегрузку подключений, упрощаем до addslashes($_Data)
function xEscSQL(
    $_Data,
    $_Params = array(),
    $_Keys = array()
) {

    if ( !is_array( $_Data ) ) {
        $_Data = db_real_escape_string( $_Data );
        return $_Data;
    }
    if ( !is_array( $_Keys ) ) {
        $_Keys = array( $_Keys );
    }
    foreach ( $_Data as $__Key => $__Data ) {
        if ( count( $_Keys ) && !is_array( $__Data ) ) {
            if ( in_array( $__Key, $_Keys ) ) {
                $_Data[$__Key] = xEscSQL( $__Data, $_Params, $_Keys );
            }
        } else {
            $_Data[$__Key] = xEscSQL( $__Data, $_Params, $_Keys );
        }
    }
    return $_Data;
}

function xHtmlSpecialChars(
    $_Data,
    $_Params = array(),
    $_Keys = array()
) {

    if ( !is_array( $_Data ) ) {
        return htmlspecialchars( $_Data, ENT_QUOTES );
    }

    if ( !is_array( $_Keys ) ) {
        $_Keys = array( $_Keys );
    }

    foreach ( $_Data as $__Key => $__Data ) {
        if ( count( $_Keys ) && !is_array( $__Data ) ) {
            if ( in_array( $__Key, $_Keys ) ) {
                $_Data[$__Key] = xHtmlSpecialChars( $__Data, $_Params, $_Keys );
            }
        } else {
            $_Data[$__Key] = xHtmlSpecialChars( $__Data, $_Params, $_Keys );
        }
    }
    return $_Data;
}

function xStripSlashesGPC( $_data ) {

    return stripslashes_deep( $_data );
}

//убирает экранирующие слеши
function stripslashes_deep( $value ) {
    $value = is_array( $value ) ?
    array_map( 'stripslashes_deep', $value ) :
    stripslashes( $value );

    return $value;
}

function html_spcharsOLD( $_data ) {
    if ( is_array( $_data ) ) {
        foreach ( $_data as $_ind => $_val ) {
            $_data[$_ind] = html_spcharsOLD( $_val );
        }
        return $_data;
    } else {
        return htmlspecialchars( $_data, ENT_QUOTES );
    }
}

// меняет '  " < > &  на сущности
function html_spchars( $value ) {
    $value = is_array( $value ) ?
    array_map( '_htmlspecialcharsENT_QUOTES', $value ) :
    htmlspecialchars( $value, ENT_QUOTES );

    return $value;
}

function _htmlspecialcharsENT_QUOTES( $a ) {
    return htmlspecialchars( $a, ENT_QUOTES );
}

function checklogin() {

    $rls = array();

    if ( isset( $_SESSION["log"] ) ) //look for user in the database

    {
        $q   = db_query( "SELECT cust_password, actions FROM " . CUSTOMERS_TABLE . " WHERE Login='" . xEscSQL( $_SESSION["log"] ) . "'" );
        $row = db_fetch_row( $q ); //found customer - check password

        if ( !$row || !isset( $_SESSION["pass"] ) || $row[0] != $_SESSION["pass"] ) //unauthorized access
        {
            unset( $_SESSION["log"] );
            unset( $_SESSION["pass"] );
        } else {

            $rls = unserialize( $row[1] );
            unset( $row );
            # fix log errors WARNING: in_array() expects parameter 2 to be array, boolean given
            if ( !is_array( $rls ) ) {
                $rls = array();
            }
        }
    }

    return $rls;
}

//show a number and selected currency sign $price is in universal currency
function show_price(
    $price,
    $custom_currency = 0,
    $code = true,
    $d = ".",
    $t = " "
) {
    global $selected_currency_details;
    //if $custom_currency != 0 show price this currency with ID = $custom_currency
    if ( $custom_currency == 0 ) {
        if ( !isset( $selected_currency_details ) || !$selected_currency_details ) //no currency found

        {
            return $price;
        }
    } else //show price in custom currency

    {

        $q = db_query( "SELECT code, currency_value, where2show, currency_iso_3, Name, roundval FROM " .
            CURRENCY_TYPES_TABLE . " WHERE CID=" . (int)$custom_currency );
        if ( $row = db_fetch_row( $q ) ) {
            $selected_currency_details = $row; //for show_price() function
        } else                             //no currency found. In this case check is there any currency type in the database

        {
            $q = db_query( "SELECT code, currency_value, where2show, roundval FROM " . CURRENCY_TYPES_TABLE );
            if ( $row = db_fetch_row( $q ) ) {
                $selected_currency_details = $row; //for show_price() function
            }
        }
    }

    //is exchange rate negative or 0?
    if ( $selected_currency_details[1] == 0 ) {
        return "";
    }

    $price = roundf( $price * $selected_currency_details[1] );
    //now show price
    $price = _formatPrice( $price, $selected_currency_details["roundval"], $d, $t );
    if ( $code ) {
        return $selected_currency_details[2] ? $price . $selected_currency_details[0] : $selected_currency_details[0] . $price;
    } else {
        return $price;
    }
}

function RN( $str ) {
    $res = str_replace( "\r\n", "  ", $str );
    // $res=preg_replace( '/(\v|\s)+/', ' ', $str );
    return $res;
}
function VS( $str ) {
    // $res=str_replace( "\r\n", "  ", $str );
    $res = preg_replace( '/(\v|\s)+/', ' ', $str );
    return $res;
}

?>