<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-09-11       #
#          http://nixby.pro              #
##########################################

// это Процессор do=cart
##  добавление товара в корзину с сайте- это не страница оформления заказа

$is_fake = ( isset( $_GET["is_fake"] ) and ( $_GET["is_fake"] == "yes" ) ) ? 1 : 0;
// $is_fake = 1;
//
//
if ( isset( $_GET["xcart"] ) || isset( $_POST["xcart"] ) ) {

    //$JsHttpRequest = & new JsHttpRequest(DEFAULT_CHARSET);
    $JsHttpRequest = new JsHttpRequest( DEFAULT_CHARSET );

    //# of selected currency
    $current_currency = isset( $_SESSION["current_currency"] ) ? $_SESSION["current_currency"] : CONF_DEFAULT_CURRENCY;
    $q                = db_query( "SELECT code, currency_value, where2show, currency_iso_3, Name, roundval FROM " . CURRENCY_TYPES_TABLE . " WHERE CID=" . (int)$current_currency );
    if ( $row = db_fetch_row( $q ) ) {
        $selected_currency_details = $row;
        //for show_price() function
    } else
    //no currency found. In this case check is there any currency type in the database
    {
        $q = db_query( "SELECT code, currency_value, where2show, roundval FROM " . CURRENCY_TYPES_TABLE );
        if ( $row = db_fetch_row( $q ) ) {
            $selected_currency_details = $row;
            //for show_price() function
        }
    }

    //should we add products to cart?
    if ( isset( $_GET["addproduct"] ) ) {
        $variants = array();
        foreach ( $_GET as $key => $val ) {
            if ( strstr( $key, "option_select_hidden_" ) ) {
                $variants[] = $val;
            }

        }
        unset( $_SESSION["variants"] );
        $_SESSION["variants"] = $variants;
        //add product to cart with productID=$add
        if ( isset( $_GET["addproduct"] ) && (int)$_GET["addproduct"] > 0

            /* && isset($_SESSION["variants"]) */
        ) {
            if ( isset( $_SESSION["variants"] ) ) {
                $variants = $_SESSION["variants"];
                unset( $_SESSION["variants"] );
                session_unregister( "variants" );
                //calling session_unregister() is required since unset() may not work on some systems
            } else {
                $variants = array();
            }
            #for ( $mcn = 0; $mcn < $_GET["multyaddcount"]; $mcn++ ) cartAddToCart(( int ) $_GET["addproduct"], $variants);

            for ( $mcn = 0; $mcn < $_GET["multyaddcount"]; $mcn += $added ) {
                if ( !$added = cartAddToCart( (int)$_GET["addproduct"], $variants ) ) {
                    break;
                }
            }

        }

        $resCart          = cartGetCartContent();
        $resDiscount      = dscCalculateDiscount( $resCart["total_price"], isset( $_SESSION["log"] ) ? $_SESSION["log"] : "" );
        $discount_value   = addUnitToPrice( $resDiscount["discount_current_unit"] );
        $discount_percent = $resDiscount["discount_percent"];
        $k                = 0;
        $cnt              = 0;

        $k = $resCart["total_price"];
        #$k = $resCart["total_price"] - $resDiscount["discount_standart_unit"]; // если хотим видеть в корзине сумму сразу со скидкой
        foreach ( $resCart["cart_content"] as $rc ) {
            $cnt += $rc["quantity"];
        }

        $productID = (int)$_GET["addproduct"];

        $GLOBALS['_RESULT'] = array(
            "shopping_cart_value"       => $k,
            "shopping_cart_value_shown" => show_price( $k ),
            "shopping_cart_items"       => $cnt,
            "shopping_cart_name"        => say_to_russian( $cnt, 'товар', 'товара', 'товаров' ),
            "is_fake"                   => $is_fake,
            "productID"                 => $productID,
            "productDATA"               => prdGetShortProductDATA( $productID ),
            "variants"                  => $variants,
        );
        // echo json_encode($GLOBALS['_RESULT']["shopping_cart_items"]);
    }
}
?>
