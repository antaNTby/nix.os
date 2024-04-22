<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2022-04-22       #
#         http://nixminsk.by             #
##########################################

if ( in_array( 100, checklogin() ) ) {
    $isadmin = true;
    $smarty->assign( "isadmin", "yes" );
} else {
    $isadmin = false;
}

global $selected_currency_details;
$products = array();

if ( isset( $_GET["searchstring"] ) ) {
    $searchstring = _deletePercentSymbol( validate_search_string( $_GET["searchstring"] ) );

    function _getUrlToSearchNavigate() {
        $url = "index.php?simple_search=yes&searchstring=" . rawurlencode( validate_search_string( $_GET["searchstring"] ) );
        return $url;
    }

}

$smarty->hassign( "searchstring", $searchstring );

if (
    ( isset( $_GET["simple_search"] ) && isset( $_GET["operation"] ) && $_GET["operation"] == "get_data" ) &&
    ( isset( $_POST["operation"] ) && $_POST["operation"] == "draw_DataTable" ) &&
    isset( $DT_columns_products )
) {

    if ( $_GET["show_all"] == "yes" ) {
        $_POST["start"]  = 0;
        $_POST["length"] = -1;
    } elseif ( isset( $_GET["offset"] ) ) {
        $_GET["offset"] = (int)$_GET["offset"];
        $_POST["start"] = $_GET["offset"];
    }

    $Clauses          = NANO_generateClauses( $_POST["params"] );
    $operation_result = SSP::getProductsData( $_POST, $pdo_connect, $products_table, $primaryKey, $DT_columns_products, $Clauses["DEDICATED"], $Clauses["PRICELIMITS"], $Clauses["OVERALL"] );

    header( "Content-Type: application/json; charset=utf-8" );
    die( json_encode( $operation_result ) );
}

if (
    ( isset( $_GET["simple_search"] ) && isset( $_GET["operation"] ) && $_GET["operation"] == "get_cards" ) &&
    ( isset( $_POST["operation"] ) && $_POST["operation"] == "draw_Cards" )
) {

    console( $PAGE_columns );
    $PAGE_REQUEST                       = array();
    $PAGE_REQUEST["columns"]            = array();
    $PAGE_REQUEST["columns"]            = $PAGE_columns;
    $PAGE_REQUEST["draw"]               = "0";
    $PAGE_REQUEST["start"]              = "0";
    $PAGE_REQUEST["length"]             = CONF_PRODUCTS_PER_PAGE * 0.5;
    $PAGE_REQUEST["order"][0]["column"] = SSP::idxCol( $PAGE_columns, "sort_order", "data" );
    $PAGE_REQUEST["order"][0]["dir"]    = "asc";
    $PAGE_REQUEST["order"][1]["column"] = SSP::idxCol( $PAGE_columns, "name", "data" );
    $PAGE_REQUEST["order"][1]["dir"]    = "asc";

    $AjaxParams = $_POST["params"];

    if ( $_GET["show_all"] == "yes" ) {
        $PAGE_REQUEST["start"]  = 0;
        $PAGE_REQUEST["length"] = -1;
        $offset                 = "show_all";
        $q_length               = CONF_PRODUCTS_PER_PAGE * 0.5;
    } elseif ( isset( $_GET["offset"] ) ) {
        $_GET["offset"]        = (int)$_GET["offset"];
        $PAGE_REQUEST["start"] = $_GET["offset"];
        $offset                = (int)$PAGE_REQUEST["start"];
        $q_length              = (int)$PAGE_REQUEST["length"];}

    $PAGE_REQUEST["params"] = $AjaxParams;
    $Clauses                = NANO_generateClauses( $AjaxParams );
    $operation_result       = SSP::getProductsData( $PAGE_REQUEST, $pdo_connect, $products_table, $primaryKey, $PAGE_columns, $Clauses["DEDICATED"], $Clauses["PRICELIMITS"], $Clauses["OVERALL"] );
    $products               = $operation_result["data"];
    $navigatorHtml          = "";
    $urlToNavigate          = _getUrlToSearchNavigate();
    $navigatorHtml .= getHtmlNavigator( $operation_result["recordsFiltered"], $offset, $q_length, html_spchars( $urlToNavigate . "_" ) );
    $navigatorHtml = strtr( $navigatorHtml, array( "_offset_0" => "", "_show_all" => "&show_all=yes", ".html" => "", "_offset_" => "&offset=" ) );

    $link        = "<a href=\"#\" data-action=\"show-filter\" data-bs-toggle=\"offcanvas\" data-bs-target=\"#offcanvasFilter\">отфильтровано</a>";
    $Info_string = "Найден(-ы) товар(-ы) с " . ( $operation_result['start'] + 1 ) . " до " . ( $operation_result['start'] + $operation_result['count_of_data'] ) . " из <span class='badge bg-danger'>" . ( $operation_result['recordsFiltered'] ) . "</span> товар(-ов) ( {$link} из <span class='badge bg-secondary'>" . ( $operation_result['recordsTotal'] ) . "</span> товар(-ов))";

    $smarty->assign( "Info_string", $Info_string );
    $smarty->assign( "navigatorHtml", $navigatorHtml );
    $smarty->assign( "navigatorLength", $q_length );
    $smarty->assign( "navigatorStart", $operation_result['start'] );
    $smarty->assign( "operation_result", $operation_result );
    $smarty->assign( "products_to_show", $products );
    $smarty->assign( "products_to_showc", count( $products ) );

    if ( $operation_result ) {
        $smarty->assign( "cats", $operation_result["cats"] );
        $smarty->assign( "cats_count", count( $operation_result["cats"] ) );
        $smarty->fetch( "category__cats.tpl.html" );
    }

    header( "Content-type: text/html; charset=" . DEFAULT_CHARSET_HTML );
    exit( $smarty->fetch( "category__cards_ajax.tpl.html" ) );
}

//make a simple search
if ( !isset( $_GET["operation"] ) && isset( $_GET["searchstring"] ) && trim( $_GET["searchstring"] ) != "" ) {

    $PAGE_REQUEST                       = array();
    $PAGE_REQUEST["columns"]            = array();
    $PAGE_REQUEST["columns"]            = $PAGE_columns;
    $PAGE_REQUEST["draw"]               = "0";
    $PAGE_REQUEST["start"]              = "0";
    $PAGE_REQUEST["length"]             = CONF_PRODUCTS_PER_PAGE * 0.5; //CONF_PRODUCTS_PER_PAGE * 0.5
    $PAGE_REQUEST["order"][0]["column"] = SSP::idxCol( $PAGE_columns, "sort_order", "data" );
    $PAGE_REQUEST["order"][0]["dir"]    = "asc";
    $PAGE_REQUEST["order"][1]["column"] = SSP::idxCol( $PAGE_columns, "name", "data" );
    $PAGE_REQUEST["order"][1]["dir"]    = "asc";

    $InitialParams = array(
        "selectParentCategory"      => false,
        "categoryID"                => 1,
        "searchInSubcategories"     => "true",
        "cats_exclude"              => false,
        "cats_include"              => false,
        "in_stock"                  => 2,
        "searchstring"              => validate_search_string( $_GET["searchstring"] ),
        "stopwords"                 => "",
        "crosswords"                => "",
        "tpl_currency_value"        => $selected_currency_details['currency_value'],
        "price_from"                => "0",
        "price_to"                  => (int)( 100000 * $selected_currency_details['currency_value'] ),
        "SetFlagPriceFilterChanged" => "0",
    );

    if ( $_GET["show_all"] == "yes" ) {
        $PAGE_REQUEST["start"]  = 0;
        $PAGE_REQUEST["length"] = -1;
        $offset                 = "show_all";
        $q_length               = CONF_PRODUCTS_PER_PAGE * 0.5;
    } elseif ( isset( $_GET["offset"] ) ) {
        $_GET["offset"]        = (int)$_GET["offset"];
        $PAGE_REQUEST["start"] = $_GET["offset"];
        $offset                = (int)$PAGE_REQUEST["start"];
        $q_length              = (int)$PAGE_REQUEST["length"];
    }

    $PAGE_REQUEST["params"] = $InitialParams;
    $Clauses                = NANO_generateClauses( $InitialParams );
    $operation_result       = SSP::getProductsData( $PAGE_REQUEST, $pdo_connect, $products_table, $primaryKey, $PAGE_columns, $Clauses["DEDICATED"], $Clauses["PRICELIMITS"], $Clauses["OVERALL"] );
    $products               = $operation_result["data"];

    $link        = "<a href=\"#\" data-action=\"show-filter\" data-bs-toggle=\"offcanvas\" data-bs-target=\"#offcanvasFilter\">отфильтровано</a>";
    $Info_string = "Найден(-ы) товар(-ы) с " . ( $operation_result['start'] + 1 ) . " до " . ( $operation_result['start'] + $operation_result['count_of_data'] ) . " из <span class='badge bg-danger'>" . ( $operation_result['recordsFiltered'] ) . "</span> товар(-ов) ( {$link} из <span class='badge bg-secondary'>" . ( $operation_result['recordsTotal'] ) . "</span> товар(-ов))";

    $navigatorHtml = "";
    $urlToNavigate = _getUrlToSearchNavigate();
    $navigatorHtml .= getHtmlNavigator( $operation_result["recordsFiltered"], $offset, $q_length, html_spchars( $urlToNavigate . "_" ) );
    $navigatorHtml = strtr( $navigatorHtml, array( "_offset_0" => "", "_show_all" => "&show_all=yes", ".html" => "", "_offset_" => "&offset=" ) );

    $smarty->hassign( "searchstring", $searchstring );

    $smarty->assign( "Clauses", $Clauses );
    $smarty->assign( "Info_string", $Info_string );
    $smarty->assign( "operation_result", $operation_result );
    $smarty->assign( "navigatorHtml", $navigatorHtml );
    $smarty->assign( "navigatorLength", $q_length );
    $smarty->assign( "navigatorStart", $operation_result['start'] );
    $smarty->assign( "products_to_show", $products );
    $smarty->assign( "products_to_showc", count( $products ) );
    $smarty->assign( 'priceUnit', $selected_currency_details['code'] );

    $smarty->assign( "main_content_template", "search_simple.tpl.html" );
    $smarty->assign( "PageH1", "Результат поиска" );
}
?>