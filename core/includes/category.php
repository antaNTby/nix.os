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

global $selected_currency_details; //code, currency_value, where2show, currency_iso_3, Name, roundval
$products = array();

if ( isset( $categoryID ) && !isset( $_GET["simple_search"] ) && !isset( $_GET["search_with_change_category_ability"] ) && !isset( $productID ) ) {

    if ( isset( $_GET["prdID"] ) ) {
        $_GET["prdID"] = (int)$_GET["prdID"];
    }

    if ( isset( $_GET["categoryID"] ) ) {
        $_GET["categoryID"] = (int)$_GET["categoryID"];
    }

    function _getUrlToNavigate( $categoryID ) {
        $url = "index.php?categoryID=" . $categoryID;

        if ( CONF_MOD_REWRITE && $url == "index.php?categoryID=" . $categoryID ) {
            $url = "category_" . $categoryID;
        }

        return $url;
    }

    //get selected category info
    $category = NANO_catGetCategoryById( $categoryID );
    $count    = 0;

    if (
        ( isset( $_GET["operation"] ) && $_GET["operation"] == "get_data" ) &&
        ( isset( $_POST["operation"] ) && $_POST["operation"] == "draw_DataTable" ) &&
        isset( $DT_columns_products )
    ) {

        $Clauses          = NANO_generateClauses( $_POST["params"] );
        $operation_result = SSP::getProductsData( $_POST, $pdo_connect, $products_table, $primaryKey, $DT_columns_products, $Clauses["DEDICATED"], $Clauses["PRICELIMITS"], $Clauses["OVERALL"] );

        header( "Content-Type: application/json; charset=utf-8" );
        die( json_encode( $operation_result ) );
    }

    if (
        ( isset( $_GET["operation"] ) && $_GET["operation"] == "get_cards" ) &&
        ( isset( $_POST["operation"] ) && $_POST["operation"] == "draw_Cards" ) &&
        isset( $PAGE_columns )
    ) {

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
            $q_length              = (int)$PAGE_REQUEST["length"];
        }

        $PAGE_REQUEST["params"] = $AjaxParams;
        $Clauses                = NANO_generateClauses( $AjaxParams );
        $operation_result       = SSP::getProductsData( $PAGE_REQUEST, $pdo_connect, $products_table, $primaryKey, $PAGE_columns, $Clauses["DEDICATED"], $Clauses["PRICELIMITS"], $Clauses["OVERALL"] );
        $products               = $operation_result["data"];
        $navigatorHtml          = "";
        $urlToNavigate          = _getUrlToNavigate( $PAGE_REQUEST["params"]["categoryID"] );
        $navigatorHtml .= getHtmlNavigator( $operation_result["recordsFiltered"], $offset, $q_length, html_spchars( $urlToNavigate . "_" ) );
        $navigatorHtml = strtr( $navigatorHtml, array( "_offset_0" => "" ) );
        // $Info_string   = "Товар(-ы) с " . ( $operation_result['start'] + 1 ) . " до " . ( $operation_result['start'] + $operation_result['count_of_data'] ) . " из <span class='badge bg-danger'>" . ( $operation_result['recordsFiltered'] ) . "</span> товар(-ов) (отфильтровано из <span class='badge bg-secondary'>" . ( $operation_result['recordsTotal'] ) . "</span> товар(-ов))";
        $link        = "<a href=\"#\" data-action=\"show-filter\" data-bs-toggle=\"offcanvas\" data-bs-target=\"#offcanvasFilter\">отфильтровано</a>";
        $Info_string = "Товар(-ы) с " . ( $operation_result['start'] + 1 ) . " до " . ( $operation_result['start'] + $operation_result['count_of_data'] ) . " из <span class='badge bg-danger'>" . ( $operation_result['recordsFiltered'] ) . "</span> товар(-ов) ( {$link} из <span class='badge bg-secondary'>" . ( $operation_result['recordsTotal'] ) . "</span> товар(-ов))";

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

    if ( !$category ) {
        header( "HTTP/1.0 404 Not Found" );
        header( "HTTP/1.1 404 Not Found" );
        header( "Status: 404 Not Found" );
        die( ERROR_404_HTML );
    } else {

        if ( !$isadmin ) {
            IncrementCategoryViewedTimes( $categoryID );
        }

        $PAGE_REQUEST                       = array();
        $PAGE_REQUEST["columns"]            = array();
        $PAGE_REQUEST["columns"]            = $PAGE_columns;
        $PAGE_REQUEST["draw"]               = "0";
        $PAGE_REQUEST["start"]              = "0";
        $PAGE_REQUEST["length"]             = CONF_PRODUCTS_PER_PAGE * 0.5; //CONF_PRODUCTS_PER_PAGE
        $PAGE_REQUEST["order"][0]["column"] = SSP::idxCol( $PAGE_columns, "sort_order", "data" );
        $PAGE_REQUEST["order"][0]["dir"]    = "asc";
        $PAGE_REQUEST["order"][1]["column"] = SSP::idxCol( $PAGE_columns, "name", "data" );
        $PAGE_REQUEST["order"][1]["dir"]    = "asc";

        $InitialParams = array(
            "selectParentCategory"      => false,
            "categoryID"                => $categoryID,
            "searchInSubcategories"     => "true",
            "cats_exclude"              => false,
            "cats_include"              => false,
            "in_stock"                  => 2,
            "searchstring"              => "",
            "stopwords"                 => "",
            "crosswords"                => "",
            "tpl_currency_value"        => $selected_currency_details['currency_value'],
            "price_from"                => "0",
            "price_to"                  => (int)( 100000 * $selected_currency_details['currency_value'] ),
            "SetFlagPriceFilterChanged" => "0",
        );

        if ( isset( $_GET["categoryID"] ) ) {
            $_GET["categoryID"]          = (int)$_GET["categoryID"];
            $InitialParams["categoryID"] = $_GET["categoryID"];
        }

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
        $Info_string = "Товар(-ы) с " . ( $operation_result['start'] + 1 ) . " до " . ( $operation_result['start'] + $operation_result['count_of_data'] ) . " из <span class='badge bg-danger'>" . ( $operation_result['recordsFiltered'] ) . "</span> товар(-ов) ( {$link} из <span class='badge bg-secondary'>" . ( $operation_result['recordsTotal'] ) . "</span> товар(-ов))";

        $navigatorHtml = "";
        $urlToNavigate = _getUrlToNavigate( $categoryID );
        $navigatorHtml .= getHtmlNavigator( $operation_result["recordsFiltered"], $offset, $q_length, html_spchars( $urlToNavigate . "_" ) );
        $navigatorHtml = strtr( $navigatorHtml, array( "_offset_0" => "" ) );

        //calculate a path to the category
        $product_category_path = catCalculatePathToCategory( $categoryID );
        $Parent                = end( $product_category_path );

        $sisters = array();
        if ( CONF_SHOW_PARENCAT && $Parent["parent"] ) {
            $sisters = getsisters( (int)$Parent["parent"] );
        }

// debugfile($sisters);

        $smarty->assign( "categoryID", $categoryID );
        $smarty->assign( "categoryName", $category["name"] );
        $smarty->assign( "Parent", $Parent );
        $smarty->assign( "product_category_path", $product_category_path );
        $smarty->assign( "sisters", $sisters );
        $smarty->assign( "subcategories_to_be_shown", catGetSubCategoriesSingleLayer( $categoryID ) );

        $smarty->assign( "Clauses", $Clauses );
        $smarty->assign( "Info_string", $Info_string );
        $smarty->assign( "operation_result", $operation_result );
        $smarty->assign( "navigatorHtml", $navigatorHtml );
        $smarty->assign( "navigatorLength", $q_length );
        $smarty->assign( "navigatorStart", $operation_result['start'] );
        $smarty->assign( "products_to_show", $products );
        $smarty->assign( "products_to_showc", count( $products ) );
        $smarty->assign( 'priceUnit', $selected_currency_details['code'] );

        $smarty->assign( "main_content_template", "category.tpl.html" );
        $smarty->assign( "PageH1", $category["name"] );

        // $smarty->assign( "categorylinkscat", getcontentcat( $categoryID ) );
        ///////////////////
        // Вывод товаров
        // 0 = Таблица
        // 1 = Витрина
        // 2 = Галерея
        ///////////////////

    }

}
?>
