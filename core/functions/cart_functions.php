<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-08-23       #
#          http://nixby.pro              #
##########################################

// compare two configuration
function CompareConfiguration( $variants1, $variants2 ) {
    if ( count( $variants1 ) != count( $variants2 ) ) {
        return false;
    }

    foreach ( $variants1 as $variantID ) {
        $count1 = 0;
        $count2 = 0;

        for ( $i = 0; $i < count( $variants1 ); $i++ ) {
            if ( (int)$variants1[$i] == (int)$variantID ) {
                $count1++;
            }
        }

        for ( $i = 0; $i < count( $variants1 ); $i++ ) {
            if ( (int)$variants2[$i] == (int)$variantID ) {
                $count2++;
            }
        }

        if ( $count1 != $count2 ) {
            return false;
        }

    }
    return true;
}

// search configuration in session variable
function SearchConfigurationInSessionVariable( $variants, $productID ) {
    if ( isset( $_SESSION["configurations"] ) && is_array( $_SESSION["configurations"] ) && count( $_SESSION["configurations"] ) ) {
        foreach ( $_SESSION["configurations"] as $key => $value ) {
            if ( (int)$_SESSION["gids"][$key] != (int)$productID ) {
                continue;
            }

            if ( CompareConfiguration( $variants, $value ) ) {
                return (int)$key;
            }

        }}

    return -1;
}

// search configuration in database
function SearchConfigurationInDataBase( $variants, $productID ) {
    $q = db_query( "select itemID from " . SHOPPING_CARTS_TABLE .
        " where customerID=" . (int)regGetIdByLogin( $_SESSION["log"] ) );
    while ( $r = db_fetch_row( $q ) ) {
        $q1 = db_query( "select COUNT(*) from " . SHOPPING_CART_ITEMS_TABLE .
            " where productID=" . (int)$productID . " AND itemID=" . (int)$r["itemID"] );
        $r1 = db_fetch_row( $q1 );
        if ( $r1[0] != 0 ) {
            $variants_from_db = GetConfigurationByItemId( $r["itemID"] );
            if ( CompareConfiguration( $variants, $variants_from_db ) ) {
                return $r["itemID"];
            }

        }
    }
    return -1;
}

function GetConfigurationByItemId( $itemID ) {
    $q = db_query( "select variantID from " .
        SHOPPING_CART_ITEMS_CONTENT_TABLE . " where itemID=" . (int)$itemID );
    $variants = array();
    while ( $r = db_fetch_row( $q ) ) {
        $variants[] = $r["variantID"];
    }

    return $variants;
}

function InsertNewItem( $variants, $productID ) {
    db_query( "insert into " . SHOPPING_CART_ITEMS_TABLE .
        "(productID) values('" . (int)$productID . "')" );
    $itemID = db_insert_id();
    foreach ( $variants as $vars ) {
        db_query( "insert into " .
            SHOPPING_CART_ITEMS_CONTENT_TABLE . "(itemID, variantID) " .
            "values( '" . (int)$itemID . "', '" . (int)$vars . "')" );
    }
    return $itemID;
}

function InsertItemIntoCart( $itemID ) {
    db_query( "insert " . SHOPPING_CARTS_TABLE . "(customerID, itemID, Quantity)" .
        "values( '" . (int)regGetIdByLogin( $_SESSION["log"] ) . "', '" . (int)$itemID . "', 1 )" );
}

function GetStrOptions( $variants ) {
    $first_flag = true;
    $res        = "";
    foreach ( $variants as $vars ) {
        $q = db_query( "select option_value from " .
            PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE .
            " where variantID=" . (int)$vars );
        if ( $r = db_fetch_row( $q ) ) {
            if ( $first_flag ) {
                $res .= $r["option_value"];
                $first_flag = false;
            } else {
                $res .= ", " . $r["option_value"];
            }

        }
    }
    return $res;
}

function CodeItemInClient( $variants, $productID ) {
    $array   = array();
    $array[] = $productID;
    foreach ( $variants as $var ) {
        $array[] = $var;
    }

    return implode( "_", $array );
}

function DeCodeItemInClient( $str ) {
    // $variants, $productID
    $array     = explode( "_", $str );
    $productID = $array[0];
    $variants  = array();
    for ( $i = 1; $i < count( $array ); $i++ ) {
        $variants[] = $array[$i];
    }

    $res              = array();
    $res["productID"] = $productID;
    $res["variants"]  = $variants;
    return $res;
}

function GetProductInStockCount( $productID ) {
    $q  = db_query( "SELECT in_stock FROM " . PRODUCTS_TABLE . " WHERE productID=" . (int)$productID );
    $is = db_fetch_row( $q );
    return $is[0];
}

function GetPriceProductWithOptionOLD( $variants, $productID ) {
    $q          = db_query( "SELECT Price FROM " . PRODUCTS_TABLE . " WHERE productID=" . (int)$productID );
    $r          = db_fetch_row( $q );
    $base_price = (float)$r[0];
    $full_price = (float)$base_price;
    foreach ( $variants as $vars ) {
        $q1 = db_query( "SELECT price_surplus FROM " . PRODUCTS_OPTIONS_SET_TABLE .
            " WHERE productID=" . (int)$productID . " AND variantID=" . (int)$vars );
        $r1 = db_fetch_row( $q1 );
        $full_price += $r1["price_surplus"];
    }
    return $full_price;
}

function GetPriceProductWithOption( $variants, $productID ) {
# [ДОПОЛНЕНИЕ] РАЗНАЯ СКИДКА ДЛЯ РАЗНЫХ КАТЕГОРИЙ
    $full_price = 0;
    $base_price = 0;

    # $row = db_fetch_assoc(db_query("SELECT Price FROM " . PRODUCTS_TABLE . " WHERE productID=$productID LIMIT 1"));
    $row = db_fetch_assoc( db_query( "SELECT Price, categoryID FROM " . PRODUCTS_TABLE . " WHERE productID=$productID LIMIT 1" ) );

    $base_price = CatDiscount( (float)$row['Price'], $row['categoryID'] );
    $change     = $base_price / (float)$row['Price'];

    if ( !$variants ) {
        #return $row['Price']
        $full_price = $base_price;
        return $full_price;
    }

    $row1 = db_fetch_assoc( db_query( "SELECT SUM(price_surplus) AS price_surplus FROM " . PRODUCTS_OPTIONS_SET_TABLE . " WHERE productID=$productID AND variantID IN (" . implode( ',', $variants ) . ")" ) );
    #return $row['Price'] + $row1['price_surplus'];
    $full_price = $base_price + $row1['price_surplus'] * $change;
    return $full_price;
# END [ДОПОЛНЕНИЕ] РАЗНАЯ СКИДКА ДЛЯ РАЗНЫХ КАТЕГОРИЙ
}

function GetProductIdByItemId( $itemID ) {
    $q = db_query( "SELECT productID FROM " . SHOPPING_CART_ITEMS_TABLE . " WHERE itemID=" . (int)$itemID );
    $r = db_fetch_row( $q );
    return $r["productID"];
}

// *****************************************************************************
// Purpose        move cart content ( SHOPPING_CARTS_TABLE ) into ordered carts ( ORDERED_CARTS_TABLE )
// Inputs                $orderID - order ID
//                                $shippingMethodID                - shipping method ID
//                                $paymentMethodID                - payment method ID
//                                $shippingAddressID                - shipping address ID
//                                $billingAddressID                - billing address ID
//                                $shippingModuleFiles        - content core/modules/shipping directories
//                                $paymentModulesFiles        - content core/modules/payment directories
// Remarks        this funcgtion is called by ordOrderProcessing to order comete
// Returns        nothing
function cartMoveContentFromShoppingCartsToOrderedCarts( $orderID,
    $shippingMethodID, $paymentMethodID,
    $shippingAddressID, $billingAddressID,
    $shippingModuleFiles, $paymentModulesFiles, &$smarty_mail ) {
    $q        = db_query( "select statusID from " . ORDERS_TABLE . " where orderID=" . (int)$orderID );
    $order    = db_fetch_row( $q );
    $statusID = $order["statusID"];

    // select all items from SHOPPING_CARTS_TABLE
    $q_items = db_query( "SELECT itemID, Quantity FROM " .
        SHOPPING_CARTS_TABLE . " WHERE customerID=" . (int)regGetIdByLogin( $_SESSION["log"] ) );
    while ( $item = db_fetch_row( $q_items ) ) {
        $productID = GetProductIdByItemId( $item["itemID"] );
        if ( $productID == null || trim( $productID ) == "" ) {
            continue;
        }

        // get product by ID

        # Добавление ТРАНЗИТНЫХ ТОВАРОВ
        $q_product = db_query( "SELECT name, product_code,in_stock FROM " . PRODUCTS_TABLE .
            " WHERE productID=" . (int)$productID );
        $product = db_fetch_row( $q_product );

        $new_in_stock  = (int)$product["in_stock"];
        $in_stock      = (int)$product["in_stock"];
        $is_fake       = ( $in_stock <= 0 ) ? 1 : 0;
        $item_quantity = (int)$item["Quantity"];
        if ( !$is_fake ) {
            // количество на складе реальное
            // если достаточно, то уменьшаем на количество в корзине, если недостаточно то Обнуляем!
            $new_in_stock = ( $in_stock >= $item_quantity ) ? ( $in_stock - $item_quantity ) : 0;
        } else {
            // товар привозится на заказ
            // оставляем "условное количество"
            $new_in_stock = $in_stock;
        }
        #END Добавление ТРАНЗИТНЫХ ТОВАРОВ

        // get full product name ( complex product name - $productComplexName ) -
        // name with configurator options
        $variants = GetConfigurationByItemId( $item["itemID"] );
        $options  = GetStrOptions( $variants );
        if ( $options != "" ) {
            $productComplexName = $product["name"] . "(" . $options . ")";
        } else {
            $productComplexName = $product["name"];
        }

        if ( strlen( $product["product_code"] ) > 0 ) {
            $productComplexName = "[" . $product["product_code"] . "] " . $productComplexName;
        }
        # Добавление ТРАНЗИТНЫХ ТОВАРОВ
        if ( $is_fake ) {
            $productComplexName = TRAIN_GO_MARK_DB . $productComplexName;
        }
        #END Добавление ТРАНЗИТНЫХ ТОВАРОВ

        //????
        $price = GetPriceProductWithOption( $variants, $productID );
        $tax   = taxCalculateTax( $productID, $shippingAddressID, $billingAddressID );


        db_query( "INSERT INTO " . ORDERED_CARTS_TABLE .
            "(        itemID, orderID, name, " .
            "        Price, Quantity, tax, itemUnit, enabled ) " .
            "  VALUES " .
            "         (" . (int)$item["itemID"] . "," . (int)$orderID . ", '" . xEscSQL( $productComplexName ) . "', " . xEscSQL( $price ) .
            ", " . (int)$item["Quantity"] . ", " . xEscSQL( $tax ) . " , '" . STRING_ITEM . "' , 1 )" );


        if ( $statusID != ostGetCanceledStatusId() && CONF_CHECKSTOCK ) {

            # Добавление ТРАНЗИТНЫХ ТОВАРОВ
            // db_query( "update " . PRODUCTS_TABLE . " set in_stock = in_stock - " . (int)$item["Quantity"] .
            // " where productID=" . (int)$productID );
            db_query( "UPDATE " . PRODUCTS_TABLE . " SET in_stock = " . (int)$new_in_stock .
                " WHERE productID=" . (int)$productID );
            # Добавление ТРАНЗИТНЫХ ТОВАРОВ

            $q          = db_query( "select name, in_stock FROM " . PRODUCTS_TABLE . " WHERE productID=" . (int)$productID );
            $productsta = db_fetch_row( $q );
            if ( $productsta["in_stock"] == 0 ) {
                if ( CONF_AUTOOFF_STOCKADMIN ) {
                    db_query( "UPDATE " . PRODUCTS_TABLE . " SET enabled=0 WHERE productID=" . (int)$productID );
                }

                if ( CONF_NOTIFY_STOCKADMIN ) {
                    $smarty_mail->assign( "productstaname", $productsta["name"] );
                    $smarty_mail->assign( "productstid", $productID );
                    $stockadmin = $smarty_mail->fetch( "notify_stockadmin.tpl.html" );
                    $ressta     = xMailTxtHTMLDATA( CONF_ORDERS_EMAIL, CUSTOMER_ACTIVATE_99, $stockadmin );
                }
            }
        }


    }
    // db_query( "DELETE FROM " . SHOPPING_CARTS_TABLE . " WHERE customerID=" . (int)regGetIdByLogin( $_SESSION["log"] ) );
    cartClearCartContent();
}

// *****************************************************************************
// Purpose        clear cart content
// Inputs
// Remarks
// Returns
function cartClearCartContent() {
    if ( isset( $_SESSION["log"] ) ) {
        db_query( "DelETE FROM " . SHOPPING_CARTS_TABLE . " WHERE customerID=" . (int)regGetIdByLogin( $_SESSION["log"] ) );
    } else {
        unset( $_SESSION["gids"] );
        unset( $_SESSION["counts"] );
        unset( $_SESSION["configurations"] );
        session_unregister( "gids" ); //calling session_unregister() is required since unset() may not work on some systems
        session_unregister( "counts" );
        session_unregister( "configurations" );
    }
}

// *****************************************************************************
// Purpose        clear cart content
// Inputs
// Remarks
// Returns
function cartGetCartContent() {
    $cart_content = array();
    $total_price  = 0;
    $freight_cost = 0;

    //get cart content from the database
    if ( isset( $_SESSION["log"] ) ) {
        $q = db_query( "SELECT itemID, Quantity FROM " . SHOPPING_CARTS_TABLE .
            " WHERE customerID=" . (int)regGetIdByLogin( $_SESSION["log"] ) );

        while ( $cart_item = db_fetch_row( $q ) ) {
            // get variants
            $variants = GetConfigurationByItemId( $cart_item["itemID"] );

            // shopping cart item
            $q_shopping_cart_item = db_query( "SELECT productID FROM " .
                SHOPPING_CART_ITEMS_TABLE . " WHERE " .
                " itemID=" . (int)$cart_item["itemID"] );
            $shopping_cart_item = db_fetch_row( $q_shopping_cart_item );
# Добавление ТРАНЗИТНЫХ ТОВАРОВ
            $q_products = db_query( "SELECT name, Price, productID, min_order_amount, shipping_freight, free_shipping, product_code,in_stock FROM " .
                PRODUCTS_TABLE . " WHERE productID=" . (int)$shopping_cart_item["productID"] );
            if ( $product = db_fetch_row( $q_products ) ) {
                $costUC = GetPriceProductWithOption( $variants,
                    $shopping_cart_item["productID"] );
# Добавление ТРАНЗИТНЫХ ТОВАРОВ
                $is_fake = ( $product["in_stock"] <= 0 ) ? 1 : 0;
# Добавление ТРАНЗИТНЫХ ТОВАРОВ
                $tmp =
                array(
                    "productID"     => $product["productID"],
                    "id"            => $cart_item["itemID"],
                    "name"          => $product["name"],
                    "quantity"      => $cart_item["Quantity"],
                    "is_fake"       => $is_fake, // Добавление ТРАНЗИТНЫХ ТОВАРОВ
                     "free_shipping" => $product["free_shipping"],
                    "costUC"        => $costUC,
                    "cost"          => show_price( $cart_item["Quantity"] * GetPriceProductWithOption( $variants, $shopping_cart_item["productID"] ) ),
                    "product_code"  => $product["product_code"],
                    "in_stock"      => $product["in_stock"],
                );

                #стоимость доставки - это СУММА $product["shipping_freight"] для каждой позиции
                // спорно !!!  -- shipping_freight - использоать только для товаров с индивидуальной доставкой - иначе 0
                $freight_cost += $cart_item["Quantity"] * $product["shipping_freight"];

                $strOptions = GetStrOptions(
                    GetConfigurationByItemId( $tmp["id"] ) );

                if ( trim( $strOptions ) != "" ) {
                    $tmp["name"] .= "  (" . $strOptions . ")";
                }

                if ( $product["min_order_amount"] > $cart_item["Quantity"] ) {
                    $tmp["min_order_amount"] = $product["min_order_amount"];
                }

                $cart_content[] = $tmp;
                $total_price += $cart_item["Quantity"] * GetPriceProductWithOption( $variants, $shopping_cart_item["productID"] );

            }
        }
    }
    //unauthorized user - get cart from session vars
    else {
        $total_price  = 0; //total cart value
        $cart_content = array();

        //shopping cart items count
        if ( isset( $_SESSION["gids"] ) ) {
            for ( $j = 0; $j < count( $_SESSION["gids"] ); $j++ ) {
                if ( $_SESSION["gids"][$j] ) {
                    $session_items[] =
                        CodeItemInClient( $_SESSION["configurations"][$j],
                        $_SESSION["gids"][$j] );
# Добавление ТРАНЗИТНЫХ ТОВАРОВ
                    $q = db_query( "SELECT name, Price, shipping_freight, free_shipping, product_code,in_stock FROM " .
                        PRODUCTS_TABLE . " WHERE productID=" . (int)$_SESSION["gids"][$j] );
                    if ( $r = db_fetch_row( $q ) ) {
# Добавление ТРАНЗИТНЫХ ТОВАРОВ
                        $is_fake = ( $r["in_stock"] <= 0 ) ? 1 : 0;
# Добавление ТРАНЗИТНЫХ ТОВАРОВ
                        $costUC = GetPriceProductWithOption(
                            $_SESSION["configurations"][$j],
                            $_SESSION["gids"][$j] ) /* * $_SESSION["counts"][$j]*/;

                        $id = $_SESSION["gids"][$j];
                        if ( count( $_SESSION["configurations"][$j] ) > 0 ) {
                            for ( $tmp1 = 0; $tmp1 < count( $_SESSION["configurations"][$j] ); $tmp1++ ) {
                                $id .= "_" . $_SESSION["configurations"][$j][$tmp1];
                            }

                        }

                        $tmp = array(
                            "productID"     => $_SESSION["gids"][$j],
                            "id"            => $id,
                            "name"          => $r["name"],
                            "quantity"      => $_SESSION["counts"][$j],
                            "is_fake"       => $is_fake, // Добавление ТРАНЗИТНЫХ ТОВАРОВ
                             "free_shipping" => $r["free_shipping"],
                            "costUC"        => $costUC,
                            "cost"          => show_price( $costUC * $_SESSION["counts"][$j] ),
                            "product_code"  => $r["product_code"],
                            "in_stock"      => $r["in_stock"],

                        );

                        $strOptions = GetStrOptions( $_SESSION["configurations"][$j] );
                        if ( trim( $strOptions ) != "" ) {
                            $tmp["name"] .= "  (" . $strOptions . ")";
                        }

                        $q_product = db_query( "SELECT min_order_amount, shipping_freight FROM " . PRODUCTS_TABLE .
                            " WHERE productID=" .
                            (int)$_SESSION["gids"][$j] );
                        $product = db_fetch_row( $q_product );
                        if ( $product["min_order_amount"] > $_SESSION["counts"][$j] ) {
                            $tmp["min_order_amount"] = $product["min_order_amount"];
                        }
                        #стоимость доставки - это СУММА $product["shipping_freight"] для каждой позиции
                        // спорно !!!  -- shipping_freight - использоать только для товаров с индивидуальной доставкой - иначе 0
                        $freight_cost += $_SESSION["counts"][$j] * $product["shipping_freight"];

                        $cart_content[] = $tmp;

                        $total_price += GetPriceProductWithOption( $_SESSION["configurations"][$j], $_SESSION["gids"][$j] ) * $_SESSION["counts"][$j];
                    }
                }
            }
        }

    }

    //определяем максимальное время доставки для  товаров в корзине
    $ShippingTermin  = 0;
    $advancedInstock = array();
    $cartItemCount   = 0;
    foreach ( $cart_content as $cartItem ) {
        $productID = $cartItem["productID"];
        $real_is   = $cartItem["in_stock"];
        $cartItemCount += $cartItem["quantity"];
        if ( $real_is <= 0 ) {
            if ( $ShippingTermin < abs( $real_is ) ) {
                $ShippingTermin  = abs( $real_is );
                $advancedInstock = Decode_row_in_stock( $real_is );
            }
        }

    }

    return array(
        "cart_content"           => $cart_content,
        "maxDeliveryDelayValue"  => $advancedInstock["Value"],
        "maxDeliveryDelayString" => $advancedInstock["in_stock_string"],
        "total_price"            => $total_price,
        "cartItemCount"          => $cartItemCount,
        "freight_cost"           => $freight_cost );

}

function cartCheckMinOrderAmount() {
    $cart_content = cartGetCartContent();
    $cart_content = $cart_content["cart_content"];
    foreach ( $cart_content as $cart_item ) {
        if ( isset( $cart_item["min_order_amount"] ) ) {
            return false;
        }
    }

    return true;
}

function cartCheckMinTotalOrderAmount() {

    $res                   = cartGetCartContent();
    $d                     = oaGetDiscountPercent( $res, "" );
    $order["order_amount"] = $res["total_price"] - ( $res["total_price"] / 100 ) * $d;
    if ( $order["order_amount"] < CONF_MINIMAL_ORDER_AMOUNT ) {
        return false;
    } else {
        return true;
    }

}

function cartAddToCart( $productID, $variants ) {

    // $is = FakeProductInStockCount( $productID );
    $is      = GetProductInStockCount( $productID );
    $fake_is = FakeProductInStockCount( $productID );
    if ( $is != 0 and $is != -1000 and $is <= -1 ) {
        $is = $fake_is;
    }

    $q = db_query( "SELECT min_order_amount FROM " . PRODUCTS_TABLE .
        " WHERE productID=" . (int)$productID );
    $min_order_amount = db_fetch_row( $q );
    $min_order_amount = $min_order_amount[0];

    $count_to_order = 1;

    //save shopping cart in the session variables
    if ( !isset( $_SESSION["log"] ) ) {
#
        //$_SESSION["gids"] contains product IDs
        //$_SESSION["counts"] contains product quantities
        //($_SESSION["counts"][$i] corresponds to $_SESSION["gids"][$i])
        //$_SESSION["configurations"] contains variants
        //$_SESSION[gids][$i] == 0 means $i-element is 'empty'
        #

        if ( !isset( $_SESSION["gids"] ) ) {
            $_SESSION["gids"]           = array();
            $_SESSION["counts"]         = array();
            $_SESSION["configurations"] = array();
        }

        //check for current item in the current shopping cart content
        $item_index = SearchConfigurationInSessionVariable( $variants, $productID );

        if ( $item_index == -1 ) {
            $count_to_order = $min_order_amount;
        }

        //increase current product's quantity
        if ( $item_index != -1 ) {
            if ( CONF_CHECKSTOCK == 0 || $_SESSION["counts"][$item_index] + $count_to_order <= $is ) {
                $_SESSION["counts"][$item_index] += $count_to_order;
            } else {
                return false;
            }

        }
        //no item - add it to $gids array
        elseif ( CONF_CHECKSTOCK == 0 || $is >= $count_to_order ) {
            $_SESSION["gids"][]           = $productID;
            $_SESSION["counts"][]         = $count_to_order;
            $_SESSION["configurations"][] = $variants;
        } else {
            return false;
        }

    } else {
        //authorized customer - get cart from database

        $itemID = SearchConfigurationInDataBase( $variants, $productID );
        // if this configuration exists in database
        if ( $itemID != -1 ) {
            $q = db_query( "SELECT Quantity FROM " . SHOPPING_CARTS_TABLE .
                " WHERE customerID=" . (int)regGetIdByLogin( $_SESSION["log"] ) . " AND itemID=" . (int)$itemID );
            $row      = db_fetch_row( $q );
            $quantity = $row[0];
            if ( CONF_CHECKSTOCK == 0 || $quantity + $count_to_order <= $is ) {
                db_query( "UPDATE " . SHOPPING_CARTS_TABLE .
                    " SET Quantity=" . (int)( $row[0] + $count_to_order ) .
                    " WHERE customerID=" . (int)regGetIdByLogin( $_SESSION["log"] ) .
                    " AND itemID=" . (int)$itemID );
            } else {
                return false;
            }

        }
        //insert new item
        else {
            $count_to_order = $min_order_amount;
            if ( CONF_CHECKSTOCK == 0 || $is >= $count_to_order ) {
                $itemID = InsertNewItem( $variants, $productID );
                InsertItemIntoCart( $itemID );
                db_query( "UPDATE " . SHOPPING_CARTS_TABLE .
                    " SET Quantity=" . (int)$count_to_order .
                    " WHERE customerID=" . (int)regGetIdByLogin( $_SESSION["log"] ) .
                    " AND itemID=" . (int)$itemID );
            } else {
                return false;
            }

        }
    }

    #return true;
    return $count_to_order;
}

// *****************************************************************************
// Purpose
// Inputs        $customerID - customer ID
// Remarks
// Returns        returns true if cart is empty for this customer
function cartCartIsEmpty( $log ) {
    $customerID = regGetIdByLogin( $log );
    if ( (int)$customerID > 0 ) {
        $customerID = (int)$customerID;
        $q_count    = db_query( "SELECT count(*) from " . SHOPPING_CARTS_TABLE . " WHERE customerID=" . (int)$customerID );
        $count      = db_fetch_row( $q_count );
        $count      = $count[0];
        return ( $count == 0 );
    } else {
        return true;
    }

}

?>