<?php
function quickcartGetPaymentSelector( $shippingID ) {
    $html = "";
    if ( $shippingID == 0 ) {
        $html = <<<HTML
        <div class="alert alert-danger alert-dismissible bg-transparent">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <strong class="h4">Внимание! Не выбран способ доставки</strong>
        </div>
        <input type="hidden" id="payment_error" name="payment_error" value="1">
HTML;
    } else {
        $html = <<<HTML
        <input type="hidden" id="payment_error" name="payment_error" value="0">
HTML;
        $moduleFiles = GetFilesInDirectory( "core/modules/payment", "php" );
        foreach ( $moduleFiles as $fileName ) {
            include $fileName;
        }

        $payment_methods = array();
        foreach ( payGetAllPaymentMethods( true ) as $payment_method ) {
            if ( $shippingID == -1 ) {
                $payment_methods[] = $payment_method;
            } else {
                foreach ( $payment_method["ShippingMethodsToAllow"] as $ShippingMethod ) {
                    if ( $shippingID == $ShippingMethod["SID"] && $ShippingMethod["allow"] ) {
                        $payment_methods[] = $payment_method;
                        break;
                    }
                }
            }
        }
        $force_select_counter = 0; /*выбираем перый пункт по умолчанию*/
        $count                = count( $payment_methods );
        if ( $count ) {
            if ( QUICK_CART_SELECT_TYPE ) {

                $html = "<select id='payment_method_select' class='form-control input-lg bg-blue' name='payment_method' onchange='payment_changed();'>";
                $html .= "<option value='0'>Выберите способ оплаты (обязательно)</option>";
                foreach ( $payment_methods as $index => $payment_method ) {
                    // $force_select_counter++;
                    $html .= "<option value='" . $payment_method["PID"] . "' title='" . htmlentities( strip_tags( $payment_method["description"] ), ENT_COMPAT, "utf-8" ) . "'" . (  (  ( $count == 1 ) or ( $force_select_counter == 1 ) ) ? ' selected' : '' ) . ">" . $payment_method["Name"] . "</option>";
                }
                $html .= "</select>";
            } else {
                $html = "<table id='payment_method_select' class='table table-bordered table-condensed table-hover' style='border-bottom:1px solid #ddd;'>";
                $html .= "<tbody>";
                foreach ( $payment_methods as $index => $payment_method ) {
                    $force_select_counter++;
                    $tdclass = ( empty( $shipping_method["shipping_costs"][0]["rate"] ) ) ? 'default' : 'text-danger';
                    $html .= "<tr>";

                    $html .= "  <td>";
                    $html .= "<div class='radio center-y' style='padding-left: 10px;'><label><input type='radio' name='payment_method' onmouseup='button_checked=this.checked;' onclick='payment_changed();' value='" . $payment_method["PID"] . "'" . (  (  ( $count == 1 ) or ( $force_select_counter == 1 ) ) ? ' checked=\"checked\"' : '' ) . "><b>" . $payment_method["Name"] . "</b> <small>" . $payment_method["description"] . "</small> </label>";
                    $html .= "    </td>";

                    $html .= "</tr>";
                }
                $html .= "</tbody>";
                $html .= "</table>";
            }

            // $html .= "<div id='helpBlockpayment_method' class='alert alert-success alert-dismissible bg-transparent'>
            //     <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
            // Способ оплаты не влияет на стоимость заказа</div>";

        } else {
            $html = "<div class='alert alert-danger bg-transparent h4 lead'>Нет вариантов оплаты...</div>";
        }
    }
    return array( "payment_methods" => $payment_methods, "html" => $html );
}

function quickcartGetShippingSelector( $zoneID ) {
    $html          = "";
    $htmlZoneAlert = <<<HTML
<div class="alert alert-danger alert-dismissible bg-transparent">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <strong class="h4">Внимание! Не выбран регион доставки!</strong>
</div>
HTML;

    $htmlShippingAlert = <<<HTML
<div class="alert alert-info alert-dismissible bg-transparent">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <strong>Внимание!</strong>
    Стоимость доставки зависит от вашего региона и будет включена в общую стоимость.
</div>

<div id='helpBlockpayment_method' class='alert alert-success alert-dismissible bg-transparent'>
    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
    <strong>Внимание!</strong>
    Способ оплаты не влияет на стоимость заказа
</div>

HTML;

    $terminMax = GetMaxDeliveryDelay();
    if ( $terminMax["Value"] > 1 ) {
        $htmlDelayAlert = <<<HTML
<div class="alert alert-warning alert-dismissible bg-transparent">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <strong>Внимание!</strong>
     Срок поставки товаров с пометкой " {$terminMax["in_stock_symbol"]} " может быть увеличен, по независящим от нас обстоятельствам. <em>Возможная задержка: {$terminMax["in_stock_string"]}</em>
</div>
<input type="hidden" id="payment_terminMax" name="payment_terminMax" value="{$terminMax['Value']}" />
HTML;

    } else {
        $htmlDelayAlert .= "";
    }

    if ( $zoneID || CONF_ADDRESSFORM_STATE > 0 ) {
        $resCart     = cartGetCartContent();
        $resDiscount = dscCalculateDiscount( $resCart["total_price"], isset( $_SESSION["log"] ) ? $_SESSION["log"] : "" );
        $order       = array( 'orderContent' => $resCart["cart_content"], 'order_amount' => $resCart["total_price"] - $resDiscount["discount_standart_unit"] );

        $sh_address = $zoneID ? znGetSingleZoneById( $zoneID ) : array( 'address' => '', 'zoneID' => 0, 'addressID' => 0 );

        $addresses = array( $sh_address, $sh_address );

        $moduleFiles = GetFilesInDirectory( "core/modules/shipping", "php" );
        foreach ( $moduleFiles as $fileName ) {
            include $fileName;
        }

        $shipping_methods = array();
        foreach ( shGetAllShippingMethods( $enabledOnly = true ) as $key => $shipping_method ) {

            $_ShippingModule = modGetModuleObj( $shipping_method["module_id"], SHIPPING_RATE_MODULE ); # вернет обьект модуля доставки либо NULL
            # debugfile($_ShippingModule, "($II) _ShippingModule quick_cart_functions.php:100");

            if ( !$_ShippingModule || $_ShippingModule->allow_shipping_to_address( $sh_address ) ) {
                $shipping_cost = oaGetShippingCostTakingIntoTax( $resCart, $shipping_method["SID"], $addresses, $order );

                foreach ( $shipping_cost as $subkey => $subcost ) {
                    $flag = false;
                    if ( $subcost['rate'] > 0 ) // если стоимость доставки больше нуля (sign - от модуля "Самовывоз")
                    {
                        $flag                           = true;
                        $shipping_cost[$subkey]['rate'] = show_price( $subcost['rate'] );
                    } elseif ( isset( $subcost['sign'] ) ) {
                        $flag                           = true;
                        $shipping_cost[$subkey]['rate'] = show_price( $subcost['rate'] );
                    } elseif ( count( $shipping_cost ) > 1 || $subcost['rate'] == 0 ) // если стоимость доставки не больше нуля, но есть еще варианты или доставка равна нулю (бесплатна)
                    {
                        $flag                           = true;
                        $shipping_cost[$subkey]['rate'] = '';
                    }
                }
                if ( $flag ) {
                    $shipping_method['shipping_costs'] = $shipping_cost;
                    $shipping_methods[]                = $shipping_method;
                }
            }
        }

        $count                = count( $shipping_methods );
        $force_select_counter = 0;

        if ( $count ) {

// <!-- Table -->
            $html .= "<table id='shipping_method_select' class='table table-bordered table-condensed table-hover' style='border-bottom:1px solid #edd;'>";
            $html .= "    <caption>Выберите способ доставки/отгрузки</caption>";
            $html .= "    <tbody>";
            foreach ( $shipping_methods as $index => $shipping_method ) {
                $html_SM_COSTS = "";
                // $force_select_counter++;
                $tdclass       = ( empty( $shipping_method["shipping_costs"][0]["rate"] ) ) ? "default" : "text-danger";
                $_ATTR_CHECKED = (
                    (  ( $count == 1 ) or ( $force_select_counter == 1 ) )
                    ? "checked='checked'"
                    : ""
                );
                $_SM_SID         = $shipping_method["SID"];
                $_SM_NAME        = $shipping_method["Name"];
                $_SM_DESCRIPTION = $shipping_method["description"];

                $_EVENT_ONMOUSEUP = "button_checked=this.checked;";
                $_EVENT_ONCLICK   = "shipping_changed()";

                if ( count( $shipping_method['shipping_costs'] ) == 1 ) {
                    if ( !empty( $shipping_method['shipping_costs'][0]['name'] ) ) {
                        $html_SM_COSTS .= $shipping_method['shipping_costs'][0]['name'] . '<br>';
                    }

                    $html_SM_COSTS .= empty( $shipping_method['shipping_costs'][0]['rate'] ) ? STRING_ZERO_SHIPPING : $shipping_method['shipping_costs'][0]['rate'];
                } else {
                    $html_SM_COSTS .= '<select class="form-control input-sm bg-blue" name="shServiceID[' . $shipping_method['SID'] . ']"' . ( QUICK_CART_COLNUM ? ' style="width:100px;margin-top:5px;"' : ' style="margin-top:5px;"' ) . '>';
                    foreach ( $shipping_method['shipping_costs'] as $key => $shipping_cost ) {
                        $html_SM_COSTS .= '<option value="' . ( $key + 1 ) . '">' . $shipping_cost['name'] . ' ' . $shipping_cost['rate'] . '</option>';
                    }
                    $html_SM_COSTS .= '</select>';
                }

                $html .= "        <tr>";
                $html .= "            <td>";
                $html .= "<div class='radio center-y' style='padding-left: 10px;'>";
                $html .= "<label>";
                $html .= "<input type='radio' name='shipping_method'";
                $html .= " onmouseup='{$_EVENT_ONMOUSEUP}'";
                $html .= " onclick='{$_EVENT_ONCLICK}'";
                $html .= " name='shipping_method'";
                $html .= " value='{$_SM_SID}'";
                $html .= " {$_ATTR_CHECKED}";
                $html .= " />";
                $html .= " <strong>{$_SM_NAME}</strong>";
                $html .= "<br>";
                $html .= "<em>{$_SM_DESCRIPTION}</em>";
                $html .= "</label>"; //for='shipping_method'
                $html .= "</div>";   //checkbox
                $html .= "            </td>";
                $html .= "            <td class='center-y text-right $tdclass' style='cursor:pointer;'>";
                $html .= $html_SM_COSTS;
                $html .= "            </td>";
                $html .= "        </tr>";

                // $html .= "        <tr>";
                // $html .= "            <td>Content 2</td>";
                // $html .= "        </tr>";

            }
            $html .= "    </tbody>";
            $html .= "</table>";

        } else {
            $html = "<div class='alert alert-danger bg-transparent h4 lead'>Нет вариантов доставки...</div>";
        }
    } else {
        $html = $htmlZoneAlert;
    }
    $html .= $htmlDelayAlert;
    $html .= $htmlShippingAlert;

    return array( 'shipping_methods' => $shipping_methods, 'html' => $html );
}

// возвращает false если нужное количество товара есть на складе
// и true если какого-то товара на момент оформления заказа на складе меньше, чем в корзине
function quickcartCheckCartInStock() {
    $rediractflag = false;
    $cartContent  = cartGetCartContent();
    foreach ( $cartContent["cart_content"] as $cartItem ) {
        $productID = isset( $_SESSION["log"] ) ? GetProductIdByItemId( $cartItem["id"] ) : $cartItem["id"];

        # Добавление ТРАНЗИТНЫХ ТОВАРОВ
        $left     = db_fetch_row( db_query( "SELECT in_stock FROM " . PRODUCTS_TABLE . " WHERE productID=$productID LIMIT 1" ) )[0];
        $fakeleft =  generateFakedInStockCount($left);

        if ( (int)$left == 0 || (int)$left == -1000 ) {
            $rediractflag = true;
            if ( isset( $_SESSION["log"] ) ) {
                // db_query( "DELetE FROM " . SHOPPING_CARTS_TABLE . " WHERE customerID=$customerID AND itemID=" . (int)$cartItem["id"] );
                db_query( "DELETE FROM " . SHOPPING_CARTS_TABLE . " WHERE customerID=" . (int)regGetIdByLogin( $_SESSION["log"] ) . " AND itemID=" . (int)$cartItem["id"] );
                db_query( "DELETE FROM " . SHOPPING_CART_ITEMS_TABLE . " where itemID=" . (int)$cartItem["id"] );
                db_query( "DELETE FROM " . SHOPPING_CART_ITEMS_CONTENT_TABLE . " where itemID=" . (int)$cartItem["id"] );
            } else {
                $res = DeCodeItemInClient( $productID );
                $i   = SearchConfigurationInSessionVariable( $res["variants"], $res["productID"] );
                if ( $i != -1 ) {
                    $_SESSION["gids"][$i] = 0;
                }
            }
        }
    }
    return $rediractflag;
}

?>