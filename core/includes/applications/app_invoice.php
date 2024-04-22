<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-09-11       #
#          http://nixby.pro              #
##########################################

//init Smarty

require "core/smarty/Smarty.class.php"; // require "core/smarty/smarty.class.php";
$smarty = new Smarty();                 //core smarty object

//this forces Smarty to recompile design each time
if ( (int)CONF_SMARTY_FORCE_COMPILE ) {
    $smarty->force_compile = true;
}

$relaccess = checklogin();

//set Smarty include files dir
$smarty->template_dir = "core/tpl/admin";

$error = "";

// validate order and user
if ( CONF_BACKEND_SAFEMODE != 1 && !isset( $_SESSION["log"] ) || !isset( $_GET["orderID"] ) ) {
    $error = ERROR_FORBIDDEN;
} else {

    $orderID = (int)$_GET["orderID"];
    $order   = ordGetOrder( $orderID );

    $order["discount_value"] = round( (float)$order["order_discount"] * $order["clear_total_priceToShow"] ) /
        100;

    if ( !$order ) {
        $error = ERROR_CANT_FIND_REQUIRED_PAGE;
    } else {

        //order was found in the database
        //administrator is allowed to access all orders invoices
        //and if logged user is not administrator, check if order belongs to this user.

        //attempt to view orders of other customers
        if ( CONF_BACKEND_SAFEMODE != 1 && !in_array( 100, $relaccess ) && ( $order["customerID"] != regGetIdByLogin
            ( $_SESSION["log"] ) ) ) {
            $error = ERROR_FORBIDDEN;
        } else {
            // show invoice
            $orderContent = ordGetOrderContent( $orderID );
            $smarty->assign( "orderContent", $orderContent );
            $smarty->assign( "order", $order );
            $smarty->assign( "completed_order_status", ostGetCompletedOrderStatus() );
        }

    }
}
$smarty->assign( "error", $error );

//show Smarty output
// $smarty->display( "app_invoice.tpl.html" );
$smarty->display( "apps/appInvoicePrint.tpl.html" );
?>