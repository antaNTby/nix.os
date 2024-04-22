<?php
// custord_orders_edit.php

if ( isset( $_GET["delete"] ) ) {

    ordDeleteOrder( (int)$_GET["orderID"] );
    Redirect( base64_decode( $_GET["urlToReturn"] ) );
}

if ( isset( $_POST["set_status"] ) ) {

    if ( (int)$_POST["status"] != -1 ) {
        ostSetOrderStatusToOrder( (int)$_GET["orderID"],
            $_POST["status"],
            isset( $_POST['status_comment'] ) ? $_POST['status_comment'] : '',
            isset( $_POST['notify_customer'] ) ? $_POST['notify_customer'] : '' );
    }

    Redirect( ADMIN_FILE . "?dpt=custord&sub=orders&orders_detailed=yes&orderID=" . (int)$_GET["orderID"] . "&urlToReturn=" . $_GET["urlToReturn"] );
}

if ( isset( $_GET["urlToReturn"] ) ) {
    $smarty->assign( "encodedUrlToReturn", $_GET["urlToReturn"] );
}

if ( isset( $_GET["urlToReturn"] ) ) {
    $smarty->hassign( "urlToReturn", base64_decode( $_GET["urlToReturn"] ) );
}

// $order = ordGetOrder( (int)$_GET["orderID"] );
$order = getNanoOrder( (int)$_GET["orderID"] );

$MyOrderContent = getMyOrderContent(
    $orderID = (int)$_GET["orderID"],
    $targetCurrencyRate = $order["currency_value"],
    $hasVatIncluded = ( $order["currency_code"] == "BNN" ) ? 1 : 0,
    $VAT_Rate = DEFAULT_VAT_RATE,
    // $precision = $order["currency_round"],
    $precision = 4,
    // $targetCurrencyCode = "BYN"
    $targetCurrencyCode = $order["currency_code"]
);

$companyID = (int)$order["companyID"];

if ( $companyID > 0 ) {
    $company = _getCompanyPDO( $companyID, $PDO_connect );
}

$invoices = chooseLastInvoices( $orderID );
$invoice=$invoices[0];

// jlog($invoice);

if ( !$invoice["buyerID"] ) {

    $company["requisites"]          = $invoice["requisites"];
    $company["director_nominative"] = $invoice["director_nominative"];
    $company["director_genitive"]   = $invoice["director_genitive"];
    $company["director_reason"]     = $invoice["director_reason"];
    $company["buyerID"]             = 0;

} else {

    $company["companyID"]   =$invoice["buyerID"] ;
    $company["requisites"] = <<<HTML
{$company["company_name"]} / УНП {$company["company_unp"]}
{$company["company_adress"]}
{$company["company_bank"]}
{$company["company_contacts"]}
HTML;

}


$sellerID = 1;
 $seller = _getCompanyPDO( $sellerID, $PDO_connect );

$termin_options = array(
    "1%w"   => "1 рабоч. день",
    "2%w"   => "2 рабоч. дня",
    "3%w"   => "3 рабоч. дня",
    "5%w"   => "5 рабоч. дней",
    "10%w"  => "10 рабоч. дней",
    "30%w"  => "30 рабоч. дней",
    "60%w"  => "60 рабоч. дней",
    "180%w" => "180 рабоч. дней",
    "365%w" => "365 рабоч. дней",
    "1%b"   => "1 банк. день",
    "2%b"   => "2 банк. дня",
    "3%b"   => "3 банк. дня",
    "5%b"   => "5 банк. дней",
    "10%b"  => "10 банк. дней",
    "30%b"  => "30 банк. дней",
    "60%b"  => "60 банк. дней",
    "180%b" => "180 банк. дней",
    "365%b" => "365 банк. дней",
    "1%n"   => "1 календ. день",
    "2%n"   => "2 календ. дня",
    "3%n"   => "3 календ. дня",
    "5%n"   => "5 календ. дней",
    "10%n"  => "10 календ. дней",
    "30%n"  => "30 календ. дней",
    "60%n"  => "60 календ. дней",
    "180%n" => "180 календ. дней",
    "365%n" => "365 календ. дней",
);

$payment_options = array(
"100" => "100%",
 "50" => "50%",
 "30" => "30%",
 "20" => "20%",
 "10" => "10%",
 "5" => "5%"
 );


$smarty->assign( "c", $company );
$smarty->assign( "s", $seller );
$smarty->assign( "termin_options", $termin_options );
$smarty->assign( "payment_options", $payment_options );


$order_status_report = xNl2Br( stGetOrderStatusReport( (int)$_GET["orderID"], 1 ) );
$order_statuses      = ostGetOrderStatuses();

$smarty->assign( "cancledOrderStatus", ostGetCanceledStatusId() );
$smarty->assign( "completed_order_status", ostGetCompletedOrderStatus() );

$smarty->assign( "order", $order );
$smarty->assign( "invoice", $invoice );

$smarty->assign( "MyOrderContent", $MyOrderContent );
$smarty->assign( "orderedCarts", $MyOrderContent["OrderedCarts"] );

$smarty->assign( "https_connection_flag", 1 );
$smarty->assign( "order_status_report", $order_status_report );
$smarty->assign( "order_statuses", $order_statuses );
$smarty->assign( "order_detailed", 1 );

?>