<?php
// custord_orders.php

//orders list
if ( !strcmp( $sub, "orders" ) ) {
    //unauthorized
    if ( CONF_BACKEND_SAFEMODE != 1 && ( !isset( $_SESSION["log"] ) || !in_array( 7, $relaccess ) ) ) {
        $smarty->assign( "admin_sub_dpt", "error_forbidden.tpl.html" );
    } else {

        $order_detailes = ( isset( $_POST["orders_detailed"] ) || isset( $_GET["orders_detailed"] ) );

        if ( !$order_detailes ) {
            include_once "core/includes/admin/sub/custord_orders_list.php";
        } else {

            $orderID      = (int)$_GET["orderID"];
            $isOrderExist = checkOrderExist( $orderID );

            if ( $isOrderExist && $orderID ) {
                include_once "core/includes/admin/sub/custord_orders_edit.php";
            } else {
                    Redirect( ADMIN_FILE . "?dpt=custord&sub=orders&search=1&orderID_textbox=&order_search_type=SearchByStatusID&checkbox_order_status_2=1&checkbox_order_status_3=1" );
            }




        }

        // get all currencies
        $currencies = currGetAllCurrencies();
        $smarty->assign( "currencies", $currencies );
        $smarty->assign( "DEFAULT_VAT_RATE", DEFAULT_VAT_RATE );
        $smarty->assign( "admin_sub_dpt", "custord_orders.tpl.html" );

    }
}
?>
