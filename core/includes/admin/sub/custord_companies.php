<?php
// custord_companies.php

$TableCompanies   = COMPANIES_TABLE;
$TableOrders      = ORDERS_TABLE;
$TableInvoices    = NANO_INVOICES_TABLE;
$TableOLDInvoices = INVOICES_TABLE;

//orders list
if ( !strcmp( $sub, "companies" ) ) {
    //unauthorized
    if ( CONF_BACKEND_SAFEMODE != 1 && ( !isset( $_SESSION["log"] ) || !in_array( 15, $relaccess ) ) ) {
        $smarty->assign( "admin_sub_dpt", "error_forbidden.tpl.html" );
    } else {
//
        $smarty->assign( "toOrderID", 0 );

        if ( isset( $_GET["toOrderID"] ) ) {
            $smarty->assign( "toOrderID", (int)$_GET["toOrderID"] );
            $smarty->assign( "toInvoiceID", (int)$_GET["toInvoiceID"] );
            $smarty->assign( "current_sub", "Выбрать компанию для заказа #" . (int)$_GET["toOrderID"] );
        }

        if ( isset( $_GET["company_detailed"] ) && isset( $_GET["companyID"] ) && $_GET["companyID"] > 0 ) {

            $companyID = $_GET["companyID"];
            $smarty->assign( "company_detailed", true );
            if ( isset( $_GET["read_only"] ) && $_GET["read_only"] == 1 ) {
                $smarty->assign( "read_only", true );
            }
            if ( $companyID > 0 ) {
                $company = _getCompanyPDO( $companyID, $PDO_connect );
            }
            $smarty->assign( "c", $company );
        }



        $smarty->assign( "admin_sub_dpt", "custord_companies.tpl.html" );
    }
}
?>