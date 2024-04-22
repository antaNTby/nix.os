<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-08-23       #
#          http://nixby.pro              #
##########################################

## СОДЕРЖАНИЕ КОРЗИНЫ В ЗАКАЗЕ _ТАБЛИЦА ЗАКАЗА _ОБРАБОТКА

$smarty->assign( "product_removed", 0 );

//     product has been removed from shopping cart

if ( isset( $_GET["product_removed"] ) ) {
    $smarty->assign( "product_removed", 1 );
    $smarty->assign( "main_content_template", "shopping_cart.tpl.html" );
}

// shopping cart
if ( isset( $this_is_a_popup_cart_window ) || isset( $_GET["shopping_cart"] ) || isset( $_POST["shopping_cart"] ) ) {

    if ( isset( $this_is_a_popup_cart_window ) ) {
        $cart_reguest = "index.php?do=cart";
    } else {
        $cart_reguest = "index.php?popup=no";
    }

    $smarty->assign( "cart_reguest", $cart_reguest );

    if ( isset( $_GET["make_more_exact_cart_content"] ) ) {
        $smarty->assign( "make_more_exact_cart_content", 1 );
    }

#######
    ###
    //add product to cart with productID=$add /*&& isset($_SESSION["variants"]) */
    if ( isset( $_GET["add2cart"] ) && $_GET["add2cart"] > 0 ) {
        if ( isset( $_SESSION["variants"] ) ) {
            $variants = $_SESSION["variants"];
            unset( $_SESSION["variants"] );
            session_unregister( "variants" ); //calling session_unregister() is required since unset() may not work on some systems
        } else {
            $variants = array();
        }
        #for ($mcn=0; $mcn<$_GET["multyaddcount"]; $mcn++) cartAddToCart( $_GET["add2cart"], $variants );
        for ( $mcn = 0; $mcn < $_GET["multyaddcount"]; $mcn += $added ) {
            if ( !$added = cartAddToCart( $_GET["add2cart"], $variants ) ) {
                break;
            }
        }

        Redirect( $cart_reguest . "&shopping_cart=yes" );
    }

#######
    ###
    //remove from cart product with productID == $remove
    if ( isset( $_GET["remove"] ) ) {

        if ( isset( $_SESSION["log"] ) ) {
            db_query( "DELETE FROM " . SHOPPING_CARTS_TABLE .
                " WHERE customerID=" . regGetIdByLogin( $_SESSION["log"] ) .
                " AND itemID=" . (int)$_GET["remove"] );
            db_query( "DELETE FROM " . SHOPPING_CART_ITEMS_TABLE . " WHERE itemID=" . (int)$_GET["remove"] );
            db_query( "DELETE FROM " . SHOPPING_CART_ITEMS_CONTENT_TABLE . " WHERE itemID=" . (int)$_GET["remove"] );
            db_query( "DELETE FROM " . ORDERED_CARTS_TABLE . " WHERE itemID=" . (int)$_GET["remove"] );
        }
        //remove from session vars
        else {
            $res = DeCodeItemInClient( $_GET["remove"] );
            $i   = SearchConfigurationInSessionVariable( $res["variants"], $res["productID"] );
            if ( $i != -1 ) {
                $_SESSION["gids"][$i] = 0;
            }

        }

        # BEGIN AJAX-пересчет корзины
        if ( !isset( $_GET['cart_recalc'] ) ) {
            Redirect( $cart_reguest . "&shopping_cart=yes" );
        }
        # END AJAX-пересчет корзины
    }

#######
    ###
    //update shopping cart content
    if ( isset( $_POST["update"] ) ) {
        foreach ( $_POST as $key => $count_to_order ) {
            if ( strstr( $key, "count_" ) ) {

                //authorized user
                if ( isset( $_SESSION["log"] ) ) {
                    $productID = GetProductIdByItemId( str_replace( "count_", "", $key ) );

                    // $is = generateFakedInStockCount($is);
                    $is      = GetProductInStockCount( $productID );
                    $fake_is = generateFakedInStockCount( $is );
                    if ( $is != 0 and $is != -1000 and $is <= -1 ) {
                        $is = $fake_is;
                    }

                    if ( $count_to_order > 0 ) {
                        //$count_to_order is a new items count in the shopping cart
                        if ( CONF_CHECKSTOCK == 1 ) {
                            $count_to_order = min( $count_to_order, $is );
                        }
                        //check stock level
                        $q = db_query( "UPDATE " . SHOPPING_CARTS_TABLE .
                            " SET Quantity=" . floor( $count_to_order ) .
                            " WHERE customerID=" .
                            regGetIdByLogin( $_SESSION["log"] ) .
                            " AND itemID=" .
                            (int)str_replace( "count_", "", $key ) );
                    } else {
                        //$count_to_order<=0 => delete item from cart
                        $q = db_query( "DELETE FROM " . SHOPPING_CARTS_TABLE . " WHERE customerID=" . regGetIdByLogin( $_SESSION["log"] ) . " AND itemID=" . (int)str_replace( "count_", "", $key ) );
                    }

                }
                //session vars
                else {
                    $res = DeCodeItemInClient( str_replace( "count_", "", $key ) );

                    //$is      = GetProductInStockCount( $res["productID"] );
                    $is      = GetProductInStockCount( $res["productID"] );
                    $fake_is = generateFakedInStockCount( $is );
                    if ( $is != 0 and $is != -1000 and $is <= -1 ) {
                        $is = $fake_is;
                    }

                    if ( $count_to_order > 0 ) {
                        $i = SearchConfigurationInSessionVariable( $res["variants"], $res["productID"] );
                        //check stock level
                        if ( CONF_CHECKSTOCK == 1 ) {
                            $count_to_order = min( $count_to_order, $is );
                        }

                        $_SESSION["counts"][$i] = floor( $count_to_order );
                    }
                    //remove
                    else {
                        $i                    = SearchConfigurationInSessionVariable( $res["variants"], $res["productID"] );
                        $_SESSION["gids"][$i] = 0;
                    }
                }
            }
        }

        # BEGIN AJAX-пересчет корзины
        if ( !isset( $_GET['cart_recalc'] ) ) {
            Redirect( $cart_reguest . "&shopping_cart=yes" );
        }
        # END AJAX-пересчет корзины
    }

#######
    ###
    //completely clear shopping cart
    if ( isset( $_GET["clear_cart"] ) ) {
        cartClearCartContent();
        Redirect( $cart_reguest . "&shopping_cart=yes" );
    }

#######
    #######
    #######

    $resCart = cartGetCartContent();

    $resDiscount      = dscCalculateDiscount( $resCart["total_price"], isset( $_SESSION["log"] ) ? $_SESSION["log"] : "" );
    $discount_value   = addUnitToPrice( $resDiscount["discount_current_unit"] );
    $discount_percent = $resDiscount["discount_percent"];

    $smarty->assign( "cart_content", $resCart["cart_content"] );
    $smarty->assign( "maxDeliveryDelayValue", $resCart["maxDeliveryDelayValue"] );
    $smarty->assign( "maxDeliveryDelayString", $resCart["maxDeliveryDelayString"] );
    $smarty->assign( "cartItemCount", $resCart["cartItemCount"] );
    $smarty->assign( "cart_amount", $resCart["total_price"] - $resDiscount["discount_standart_unit"] );
    $smarty->assign( 'cart_min', show_price( CONF_MINIMAL_ORDER_AMOUNT ) );
    $smarty->assign( "cart_total", show_price( $resCart["total_price"] - $resDiscount["discount_standart_unit"] ) );

    #switch ( CONF_DISCOUNT_TYPE )
    #// discount_prompt = 0 ( discount information is not shown )
    #// discount_prompt = 1 ( discount information is showed simply without prompt )
    #// discount_prompt = 2 ( discount information is showed with
    #//                        STRING_UNREGISTERED_CUSTOMER_DISCOUNT_PROMPT )
    #// discount_prompt = 3 ( discount information is showed with
    #//                        STRING_UNREGISTERED_CUSTOMER_COMBINED_DISCOUNT_PROMPT )

    switch ( CONF_DISCOUNT_TYPE ) {
        // discount is switched off
        case 1:
            $smarty->assign( "discount_prompt", 0 );
            break;

        // discount is based on customer group
        case 2:
            if ( isset( $_SESSION["log"] ) ) {
                $smarty->assign( "discount_value", $discount_value );
                $smarty->assign( "discount_percent", $discount_percent );
                $smarty->assign( "discount_prompt", 1 );
            } else {
                $smarty->assign( "discount_value", $discount_value );
                $smarty->assign( "discount_percent", $discount_percent );
                $smarty->assign( "discount_prompt", 2 );
            }
            break;

        // discount is calculated with help general order price
        case 3:
            $smarty->assign( "discount_prompt", 1 );
            $smarty->assign( "discount_value", $discount_value );
            $smarty->assign( "discount_percent", $discount_percent );
            break;

        // discount equals to discount is based on customer group plus
        // discount calculated with help general order price
        case 4:
            if ( isset( $_SESSION["log"] ) ) {
                $smarty->assign( "discount_prompt", 1 );
                $smarty->assign( "discount_value", $discount_value );
                $smarty->assign( "discount_percent", $discount_percent );
            } else {
                $smarty->assign( "discount_prompt", 3 );
                $smarty->assign( "discount_value", $discount_value );
                $smarty->assign( "discount_percent", $discount_percent );
            }
            break;

        // discount is calculated as MAX( discount is based on customer group,
        // discount calculated with help general order price  )
        case 5:
            if ( isset( $_SESSION["log"] ) ) {
                $smarty->assign( "discount_prompt", 1 );
                $smarty->assign( "discount_value", $discount_value );
                $smarty->assign( "discount_percent", $discount_percent );
            } else {
                $smarty->assign( "discount_prompt", 3 );
                $smarty->assign( "discount_value", $discount_value );
                $smarty->assign( "discount_percent", $discount_percent );
            }
            break;
    }

    if ( isset( $_SESSION["log"] ) ) {
        $smarty->assign( "shippingAddressID", regGetDefaultAddressIDByLogin( $_SESSION["log"] ) );
    }

# BEGIN AJAX-пересчет корзины
    // ВОТ ОНО
    if ( isset( $_GET["cart_recalc"] ) ) {
        header( "Content-type: text/html; charset=" . DEFAULT_CHARSET_HTML );
        if ( isset( $_POST["shipping_method"] ) && $_POST["shipping_method"] ) {
            $shipping_method = shGetShippingMethodById( (int)$_POST["shipping_method"] );
            $cartContent     = cartGetCartContent();
            $smarty->assign( "cartContent", $cartContent );
            $smarty->assign( "maxDeliveryDelayValue", $cartContent["maxDeliveryDelayValue"] );
            $smarty->assign( "maxDeliveryDelayString", $cartContent["maxDeliveryDelayString"] );
            $smarty->assign( "cartItemCount", $cartContent["cartItemCount"] );

            $d                   = dscCalculateDiscount( $cartContent["total_price"], $log );
            $orderAmount         = $cartContent["total_price"] * ( 1.0 - $d["discount_percent"] / 100 );
            $order               = array( "orderContent" => $cartContent["cart_content"], "order_amount" => $orderAmount );
            $shippingAddress     = $_POST["zoneID"] ? znGetSingleZoneById( $_POST["zoneID"] ) : array( "address" => "", "zoneID" => 0, "addressID" => 0 );
            $shServiceID         = isset( $_POST["shServiceID"][$_POST["shipping_method"]] ) ? (int)$_POST["shServiceID"][$_POST["shipping_method"]] : 0;
            $shipping_cost_array = oaGetShippingCostTakingIntoTax( $cartContent, $shipping_method["SID"], array( $shippingAddress, $shippingAddress ), $order );
            if ( $shipping_cost = $shipping_cost_array[$shServiceID ? $shServiceID - 1 : 0]["rate"] ) {
                $smarty->assign( "shipping_cost", show_price( $shipping_cost ) );
                $smarty->assign( "total_cost", show_price( $orderAmount + $shipping_cost ) );
                $smarty->assign( "shipping_costWithoutUnits", _formatPrice( $shipping_cost ) );
                $smarty->assign( "total_costWithoutUnits", _formatPrice( $orderAmount + $shipping_cost ) );
            }
        }
        // echo json_encode($cartContent);
        exit( $smarty->fetch( "shopping_cart_ajax.tpl.html" ) );
    }
# END AJAX-пересчет корзины

    $smarty->assign( "PageH1", CART_TITLE );
    $smarty->assign( "main_content_template", "shopping_cart.tpl.html" );
}
?>
