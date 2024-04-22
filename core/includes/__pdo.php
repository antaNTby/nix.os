<?php

$DT_columns_products = array();
$DT_columns_products = array(
    /*1*/
    array( "db" => "product_code", "dt" => "product_code", "title" => "Артикул" ),
    array( "db" => "name", "dt" => "product_name", "title" => "Наименование", "formatter" => function ( $d, $row ) {return _formatterLinks( $d, $row );} ),
    array( "db" => "name", "dt" => "name", "title" => "Название продукта" ),
    array( "db" => "brief_description", "dt" => "brief_description", "title" => "Описание" ),
    array( "db" => "Price", "dt" => "priceOUT", "title" => "Цена", "formatter" => function ( $d, $row ) {return _formatterPrice( $d, $row, "Price" );} ),
    array( "db" => "Price", "dt" => "priceUSD", "title" => "Цена", "formatter" => function ( $d, $row ) {return _formatterPrice( $d, $row, "priceUSD" );} ),
    /*5*/
    array( "db" => "in_stock", "dt" => "in_stock_qnt", "title" => "[Кол-во]", "formatter" => function ( $d, $row ) {return _formatterInstock( $d, $row, "qnt" );} ),                 //,
    array( "db" => "in_stock", "dt" => "in_stock_string", "title" => "Наличие", "formatter" => function ( $d, $row ) {return _formatterInstock( $d, $row, "in_stock_string" );} ), //,
    array( "db" => "in_stock", "dt" => "qnt_to_show", "title" => "Кол-во", "formatter" => function ( $d, $row ) {return _formatterInstock( $d, $row, "qnt_to_show" );} ),            //,
    array( "db" => "productID", "dt" => "productID", "title" => "productID" ),
    array( "db" => "categoryID", "dt" => "categoryID", "title" => "categoryID" ),
    array( "db" => "categoryID", "dt" => "category_name", "title" => "Категория", "formatter" => function ( $d ) {return catname( $d );} ),
    array( "db" => "vendorID", "dt" => "vendorID", "title" => "vendorID" ),
    array( "db" => "sort_order", "dt" => "sort_order", "title" => "sort_order", "orderable" => true, "visible" => false, "searchable" => false ),
);

$PAGE_columns = array();
$PAGE_columns = array(
    array( "data" => "product_code", "db" => "product_code", "dt" => "product_code", "title" => "Артикул", "orderable" => true, "visible" => false, "searchable" => false ),
    array( "data" => "name", "db" => "name", "dt" => "name", "title" => "Название продукта", "orderable" => true, "visible" => true, "searchable" => false ),
    array( "data" => "brief_description", "db" => "brief_description", "dt" => "brief_description", "title" => "Описание", "orderable" => true, "visible" => false, "searchable" => false ),
    array( "data" => "Price", "db" => "Price", "dt" => "priceOUT", "title" => "Цена", "formatter" => function ( $d, $row ) {return _formatterPrice( $d, $row, "PriceWithUnit" );} ),
    array( "data" => "Price", "db" => "Price", "dt" => "priceUSD", "title" => "Цена", "formatter" => function ( $d, $row ) {return _formatterPrice( $d, $row, "priceUSD" );} ),
    array( "data" => "in_stock", "db" => "in_stock", "dt" => "in_stock_string", "title" => "Наличие", "formatter" => function ( $d, $row ) {return _formatterInstock( $d, $row, "in_stock_string" );}, "orderable" => true, "visible" => false, "searchable" => false ), //
    array( "data" => "productID", "db" => "productID", "dt" => "productID", "title" => "productID", "orderable" => true, "visible" => false, "searchable" => false ),
    array( "data" => "categoryID", "db" => "categoryID", "dt" => "categoryID", "title" => "categoryID", "orderable" => true, "visible" => false, "searchable" => false ),
    array( "data" => "vendorID", "db" => "vendorID", "dt" => "vendorID", "title" => "vendorID", "orderable" => true, "visible" => false, "searchable" => false ),
    array( "data" => "sort_order", "db" => "sort_order", "dt" => "sort_order", "title" => "sort_order", "orderable" => true, "visible" => false, "searchable" => false ),
    array( "data" => "product_code", "db" => "product_code", "dt" => "image_path", "title" => "img", "formatter" => function ( $d, $row ) {return _formatterPictures( $d, $row );} ),
    array( "data" => "in_stock", "db" => "in_stock", "dt" => "in_stock_qnt", "title" => "Наличие", "formatter" => function ( $d, $row ) {return _formatterInstock( $d, $row, "qnt" );}, "orderable" => true, "visible" => false, "searchable" => false ), //
 );

// DB table to use
$products_table = PRODUCTS_TABLE;
// Table's primary key
$primaryKey = "productID";


function _formatterLinks( $name, $row ) {
    if ( in_array( 100, checklogin() ) ) {
        $isadmin = true;
    } else {
        $isadmin = false;
    }
    $out = "";

    $link = ( CONF_MOD_REWRITE == 1 )
    ? "product_{$row['productID']}.html"
    : "index.php?productID={$row['productID']}";

    $out = <<<HTML
<a href="{$link}" class="fw-lighter fs-lh-1 pname">{$name}</a>
HTML;

    if ( $isadmin ) {
        $link = "/admin.php?productID={$row['productID']}&eaction=prod";
        $out .= <<<HTML
<a href="{$link}" class="adminlink px-1 opacity-25"><i class="bi bi-apple"></i></a>
HTML;
    }
    return $out;
}



function _formatterPrice( $d, $row, $index ) {
    if ( in_array( 100, checklogin() ) ) {
        $isadmin = true;
    } else {
        $isadmin = false;
    }
    global $selected_currency_details; //code, currency_value, where2show, currency_iso_3, Name, roundval
    $out = array();

# [ДОПОЛНЕНИЕ] РАЗНАЯ СКИДКА ДЛЯ РАЗНЫХ КАТЕГОРИЙ
    $price = CatDiscount( $d, $row["categoryID"] );
# END[ДОПОЛНЕНИЕ] РАЗНАЯ СКИДКА ДЛЯ РАЗНЫХ КАТЕГОРИЙ

    // $price           = $d;

    $out["priceUSD"] = _formatPrice( roundf( $price ), $selected_currency_details["roundval"], '.', '' );
    if ( !isset( $selected_currency_details ) || !$selected_currency_details ) {
        //no currency found
        $out["Price"] = $out["priceUSD"];
    } else {
        $out["Price"] = _formatPrice( roundf( $price * $selected_currency_details["currency_value"] ), $selected_currency_details["roundval"], '.', '' );
    }
    //is exchange rate negative or 0?
    if ( $selected_currency_details["roundval"] <= 0 ) {
        $out["Price"] = 0;
    }

    $out["PriceWithUnit"] = addUnitToPrice( $out["Price"] );

    $res = $out["{$index}"];
    return $res;
}


function _formatterInstock( $d, $row, $index ) {
    if ( in_array( 100, checklogin() ) ) {
        $isadmin = true;
    } else {
        $isadmin = false;
    }
    $out  = array();
    $data = (int)$d;

    switch ( $data ) {

        case ( 0 ):

            $out["qnt"]             = 0;
            $out["qnt_to_show"]     = 0;
            $out["in_stock_string"] = "нет на складе";
            // $out["timestamp"] = 0;
            break;

        case ( -1 ):
            $out["qnt"]             = -1;
            $out["qnt_to_show"]     = 5;
            $out["in_stock_string"] = "ожидаем";
            // $out["timestamp"] = -1;
            break;

        case ( -1000 ):
            $out["qnt"]             = -1000;
            $out["qnt_to_show"]     = 0;
            $out["in_stock_string"] = "снят с продажи";

            // $out["timestamp"] = 0;
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
            // $out["timestamp"] = time() + $data * 24 * 3600 + intval( CONF_TIMEZONE ) * 3600;

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
            // $out["timestamp"] = time() + intval( CONF_TIMEZONE ) * 3600;
            break;

        default:
            $out["qnt"]             = $data;
            $out["qnt_to_show"]     = $data;
            $out["in_stock_string"] = "непонятно";
            // $out["timestamp"] = time() + intval( CONF_TIMEZONE ) * 3600;
            break;
    }

    if ( $isadmin ) {
        $out["in_stock_db"] = $data;
    }

    $out["qnt"]         = $out["qnt"];
    $out["qnt_to_show"] = $out["qnt_to_show"];

    $res = $out["{$index}"];
    return $res;
}


function _formatterPictures( $d, $row ) {
    $res           = "data/images/empty.gif";
    $nixru_picture = dbGetFieldData( "PRICE_Pictures", "img_url", " `img_url` LIKE '%2254%' and `offer_id`='" . $d . "' LIMIT 1" );
    if ( !strlen( $nixru_picture ) ) {
        // $res = "data/images/empty.gif";
        $res = "";
    } else {
        $res = $nixru_picture;
    }
    return $res;
}


?>