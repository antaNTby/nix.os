<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-08-23       #
#          http://nixby.pro              #
##########################################

// <head> variables definition: title, meta

// TITLE & META Keywords & META Description

// aux page => get title and META information from database
if ( isset( $_GET["show_aux_page"] ) ) {
    $page = auxpgGetAuxPage( $show_aux_page );
    if ( $page["aux_page_title"] ) {
        $page_title = $page["aux_page_title"];
    } elseif ( $page["aux_page_name"] ) {
        $page_title = $page["aux_page_name"];
    } else {
        $page_title = CONF_SHOP_NAME . " - " . CONF_DEFAULT_TITLE;
    }

    $meta_tags = "";
    if ( $page["meta_description"] != "" ) {
        $meta_tags .= "<meta name=\"description\" content=\"" . $page["meta_description"] . "\">\n";
    }

    if ( $page["meta_keywords"] != "" ) {
        $meta_tags .= "<meta name=\"keywords\" content=\"" . $page["meta_keywords"] . "\">\n";
    }

}
//  fullnews => get title
elseif ( isset( $_GET["fullnews"] ) ) {
    $fullnews_array_head = newsGetFullNewsToCustomer( $_GET["fullnews"] );
    if ( $fullnews_array_head["title"] ) {
        $page_title = $fullnews_array_head["title"];
    } else {
        $page_title = CONF_SHOP_NAME . " - " . CONF_DEFAULT_TITLE;
    }

    $meta_tags = "";
    if ( CONF_HOMEPAGE_META_DESCRIPTION != "" ) {
        $meta_tags .= "<meta name=\"description\" content=\"" . CONF_HOMEPAGE_META_DESCRIPTION . "\">\n";
    }

    if ( CONF_HOMEPAGE_META_KEYWORDS != "" ) {
        $meta_tags .= "<meta name=\"keywords\" content=\"" . CONF_HOMEPAGE_META_KEYWORDS . "\">\n";
    }

}
//not an aux page, e.g. homepage, product/category page, registration form, checkout, etc.
else {
    if ( isset( $categoryID ) && !isset( $productID ) && $categoryID > 0 ) //category page
    {
        $q = db_query( "SELECT name, title FROM " . CATEGORIES_TABLE . " WHERE categoryID=" . (int)$categoryID );
        $r = db_fetch_row( $q );
        if ( $r[1] ) {
            $page_title = $r[1];
        } elseif ( $r[0] ) {
            $page_title = $r[0];
        } else {
            $page_title = CONF_SHOP_NAME . " - " . CONF_DEFAULT_TITLE;
        }

        $meta_tags = catGetMetaTags( $categoryID );

    } elseif ( isset( $productID ) && $productID > 0 ) //product information page
    {
        $q = db_query( "SELECT name, title FROM " . PRODUCTS_TABLE . " WHERE productID=" . (int)$productID );
        $r = db_fetch_row( $q );
        if ( $r[1] ) {
            $page_title = htmlspecialchars_decode( $r[1] );
        } elseif ( $r[0] ) {
            $page_title = htmlspecialchars_decode( $r[0] );
        } else {
            $page_title = CONF_SHOP_NAME . " - " . CONF_DEFAULT_TITLE;
        }

        $meta_tags = prdGetMetaTags( $productID );
    } else // other page
    {
        $page_title = CONF_SHOP_NAME . " - " . CONF_DEFAULT_TITLE;
        $meta_tags  = "";
        if ( CONF_HOMEPAGE_META_DESCRIPTION != "" ) {
            $meta_tags .= "<meta name=\"description\" content=\"" . CONF_HOMEPAGE_META_DESCRIPTION . "\">\n";
        }

        if ( CONF_HOMEPAGE_META_KEYWORDS != "" ) {
            $meta_tags .= "<meta name=\"keywords\" content=\"" . CONF_HOMEPAGE_META_KEYWORDS . "\">\n";
        }

    }
}

$variodesign = settingSELECT_USERTEMPLATE();
$smarty->assign( "variodesign", $variodesign );
$smarty->assign( "page_title", $page_title );
$smarty->assign( "page_meta_tags", $meta_tags );

?>