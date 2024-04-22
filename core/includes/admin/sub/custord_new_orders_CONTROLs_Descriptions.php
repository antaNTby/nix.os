<?php
## описание контролек для страницы заказов
## C:\MyProjects\hoster.by\www.nixminsk.by\core\includes\admin\sub\custord_new_orders_CONTROLs_Descriptions.php
## core/includes/admin/sub/custord_new_orders_CONTROLs_Descriptions.php

// echo('core/includes/admin/sub/custord_new_orders_CONTROLs_Descriptions.php');

$shipping_cost  = $Order["shipping_cost"];  // CONTROL_orderID_number
$order_discount = $Order["order_discount"]; // CONTROL_orderID_number

$aka = ( stristr( $Order["order_aka"], "==" ) || (int)$Order["order_aka"] > 0 )
? "{$Order['order_aka']}"
: (int)$Order["orderID"];

$Customs         = array();
$Company_Options = array();
$Customs         = getOptionsForCompanies( $where_clause="");
//"company_unp"
// foreach ( $Customs as $_custom ) {
//     if ( count( $Customs ) > 0 ) {
//         $Company_Options[(int)$_custom["companyID"]] = $_custom["company_name"] . " / " . $_custom["company_unp"];
//     }
// }
foreach ( $Customs as $_custom ) {
    if ( count( $Customs ) > 0 ) {
        $Company_Options[(int)$_custom["companyID"]] = array(
            "Value"   => $_custom["companyID"],
            "Content" => "<strong class='text-plain-red'>" . zeroFill( $_custom["companyID"] ) . "</strong> " .
            wordwrap( $_custom["company_name"] . " УНП " . "<strong>" . $_custom["company_unp"] . "</strong>," . " <small>" . $_custom["company_adress"] . "</small>", 120, "<br>\n", true ),
            "Subtext" => $_custom["update_time"],
            "Tokens"  => $_custom["contractID"],
            "Title"   => $_custom["company_name"] . " / УНП " . $_custom["company_unp"] . "&nbsp;&nbsp; id:" . zeroFill( $_custom["companyID"] ),

        );
    }
}

$CONTROL_companyID = new ControlModel(
    "CONTROL_companyID",
    "CONTROL_SELECTPICKER",
    array(
        "header"       => "Заказчик",
        "hint"         => "Заказчик",
        // "hint"         => "",
         // "rowcol"       => "col-md-12",
         "compactlabel" => "1",
        "multiple"     => "0",
        "js"           => array(
            "liveSearch"  => 1,
            "showTick"    => 1,
            // "size"        => "auto",
             "size"        => 8,
            "width"       => '50%',
            "showContent" => 1,
            // "showSubtext" => 1,

        ),
    ),
    array(
        "complex_options" => 1,
        "options"         => $Company_Options,
        "fieldname"       => "companyID",
        "DBtable"         => ORDERS_TABLE,
        "primaryKey"      => "orderID",
        "current_value"   => (int)$Order["companyID"],
        "editID"          => $Order["orderID"],
        // "where_clause"=>"`invoiceID`={$editID}"
    )
);
$smarty->assign( "CONTROL_companyID", $CONTROL_companyID );

$CONTROL_order_aka_number = new ControlModel(
    "CONTROL_order_aka_number",
    "CONTROL_TEXT",
    array(
        "NO_defaultButton" => 0,
        "compactlabel"     => 1,
        "header"           => "Псевдономер заказа",
        "hint"             => "Псевдономер заказа (товарной части) в ORDERS_TABLE",
    ),
    array(
        "fieldname"     => "order_aka",
        "DBtable"       => ORDERS_TABLE,
        "primaryKey"    => "orderID",
        "editID"        => $Order["orderID"],
        "current_value" => $Order["order_aka"],
    )
);
$smarty->assign( "CONTROL_order_aka_number", $CONTROL_order_aka_number );
################################################################################################

$_stampsOpts = array(
    "1" => "М.П. -заполнитель",
    "2" => "Овальная печать",
    "4" => "Круглая печать",
    // "8" => "\"Особая\" печать",
 );

// $newCTRL = array();
foreach ( $AllInvoices as $key => $_invoice ) {

    $Currency_rate = $_invoice["currency_rate"];

    // $pmCurrencyID = $_invoice["pmCurrencyID"]; //module
    $_currCurrency  = currGetCurrencyByID( $_invoice["pmCurrencyID"] );
    $_currency_mark = $_currCurrency["currency_iso_3"];

    $PM_NDS_IS_INCLUDED_IN_PRICE = $_invoice["PM_NDS_IS_INCLUDED_IN_PRICE"]; //module
    $PM_NDS_RATE                 = $_invoice["PM_NDS_RATE"];                 //module
    $PM_MULTIPLIER               = $_invoice["PM_MULTIPLIER"];
    $PM_INVOICE_STRING           = $_invoice["PM_INVOICE_STRING"];
    $dec                         = abs( $_currCurrency["roundval"] );
    $current_currency_iso3       = $_currCurrency["currency_iso_3"];

    $invoice_subdiscount = $_invoice["invoice_subdiscount"];

    $invoice_dates = dtDoDates( $_invoice["invoice_time"], $aka, $YearMark = "г." );
    $contact_dates = dtDoDates( $_invoice["contract_time"], $aka, $YearMark = "г." );

    $CONTROL_ordercontent[$key] = new ControlModel(
        // "CONTROL_ordercontent_{$_invoice["invoiceID"]}",
        "invoiceID{$_invoice['invoiceID']}_ordercontent",
        "CONTROL_ORDERCONTENT",
        // "CONTROL_ORDERCONTENT_COMPACT",
        array( "compactlabel"     => 0,
            "header"                 => "Товарная часть",
            "hint"                   => "Курс валюты на день заказа может отличаться !!!",
            "invoice_hidden"         => $_invoice["invoice_hidden"],
            // "hideButtonEditOrder"    => 1,
             "hideblackOrderSummInfo" => 1,
            "hideSecondTable"        => 1,
            "hideSecondTableFooter"  => 0,

        ),
        array(
            "fieldname"     => "orderID",
            "DBtable"       => INVOICES_TABLE,
            "primaryKey"    => "invoiceID",
            "cm"            => "{$_currency_mark}",

            "invoiceID"     => "{$_invoice['invoiceID']}",
            "current_value" => ordAdvancedOrderContent(
                $Order["orderID"],
                $Currency_rate,               //module
                $shipping_cost,               // order
                $order_discount,              //order
                $PM_NDS_IS_INCLUDED_IN_PRICE, //module
                $PM_NDS_RATE,                 //module
                $PM_MULTIPLIER,               //module
                $dec,                         //currency
                $current_currency_iso3,       //currency
                $invoice_subdiscount          // идивидуальная скидка для инвойса, %%%
            ),
            "ff4_invoice"   => "{$PM_INVOICE_STRING}" . " " . $invoice_dates["ff4_string"],
            "ff4_contract"  => "{$PM_CONTRACT_STRING}" . " " . $contact_dates["ff4_string"],

        )
    );

    $CONTROL_invoiceToolbar[$key] = new ControlModel(
        "ToolBar_{$_invoice["invoiceID"]}",
        "CONTROL_OrdersInvoices_TOOLBAR",
        array(
            // "header" => "Вывод на печать",
             "hint"     => "XXXXXXXXXXXXXXXXX",
            "vertical" => 0,
            "compact"  => 1,
        ),
        array(

            "module_id" => (int)$_invoice["module_id"],
            "invoiceID" => (int)$_invoice["invoiceID"],
            "orderID"   => (int)$_invoice["orderID"],
            "buttons"   => array(

                "print_pdf"                 => array(
                    "name"         => "print_pdf",
                    "command"      => "print_pdf",
                    "href"         => urldecode( getNewPdfLink( (int)$_invoice["invoiceID"] ) ),
                    // "href2"         => urldecode( getNewPdfLink( (int)$_invoice["invoiceID"] ) )."&direct=true",
                     "target_blank" => "_blank",
                ),

                "save_pdf"                  => array(
                    "name"         => "save_pdf",
                    "command"      => "save_pdf",
                    "href"         => urldecode( getNewPdfLink( (int)$_invoice["invoiceID"] ) ) . "&direct=true",
                    // "href2"         => urldecode( getNewPdfLink( (int)$_invoice["invoiceID"] ) ),
                     "target_blank" => "_blank",
                ),

                "edit_invoice"              => array(
                    "name"         => "edit_invoice",
                    "command"      => "edit_invoice",
                    "href"         => ADMIN_FILE . "?dpt=custord&sub=invoices&edit=" . (int)$_invoice["invoiceID"] . "&command=" . "edit_invoice",
                    "target_blank" => "_self",
                ),
                "table_invoices"            => array(
                    "name"    => "table_invoice",
                    "command" => "table_invoice",
                    "href"    => ADMIN_FILE . "?dpt=custord&sub=invoices",
                    // "confirmtext" => "Хотите " . "table_invoice" . " ? ",
                ),
                "table_orders"              => array(
                    "name"         => "table_invoice",
                    "command"      => "table_invoice",
                    "href"         => ADMIN_FILE . "?dpt=custord&sub=new_orders",
                    // "href"    => ADMIN_FILE . "?dpt=custord&sub=new_orders&search=".$_invoice["orderID"]."&orderID_textbox=" .$_invoice["orderID"],
                     "href"         => base64_decode( _getTableUrl_encoded( $_invoice["orderID"] ) ),
                    "target_blank" => "_self",
                    // "confirmtext" => "Хотите " . "table_invoice" . " ? ",
                ),
                "filter_invoice_order"      => array(
                    "name"    => "filter_invoice_order",
                    "command" => "filter_invoice_order",
                    "href"    => ADMIN_FILE . "?dpt=custord&sub=invoices&get_filter_orderID=" . (int)$_invoice["orderID"] . "&no_time_filter=1",
                    // "confirmtext" => "Хотите " . "table_invoice" . " ? ",
                ),
                "filter_invoice_companyUNP" => array(
                    "name"    => "filter_invoice_companyUNP",
                    "command" => "filter_invoice_companyUNP",
                    "href"    => ADMIN_FILE . "?dpt=custord&sub=invoices&get_filter_company=" . dbGetFieldData(
                        $tablename = COMPANIES_TABLE,
                        $fieldname = "company_unp",
                        $where_clause = "companyID = " . (int)$_invoice["buyerID"],
                        $order_clause = "",
                        $formatt = 1
                    ) . "&no_time_filter=1",
                    // "confirmtext" => "Хотите " . "table_invoice" . " ? ",
                ),
                "edit_order"                => array(
                    "name"    => "edit_order",
                    "command" => "edit_order",
                    "href"    => ADMIN_FILE . "?dpt=custord&sub=new_orders&orders_detailed=yes&orderID=" . (int)$_invoice["orderID"],
                    // "confirmtext" => "Хотите " . "edit_order" . " ? ",
                ),
                "edit_company"              => array(
                    "name"    => "edit_company",
                    "command" => "edit_company",
                    // "href"    => "&command=" . "edit_company",
                    // "confirmtext" => "Хотите " . "edit_company" . " ? ",
                ),

                "edit_contact"              => array(
                    "name"    => "edit_contact",
                    "command" => "edit_contact",
                    // "href"    => "&command=" . "edit_contact",
                    // "confirmtext" => "Хотите " . "edit_contact" . " ? ",
                ),

                "minus_invoice"             => array(
                    "name"        => "minus_invoice",
                    "command"     => "RemoveInvoices",
                    // "href"        => "&command=" . "RemoveInvoices",
                     "confirmtext" => "Хотите " . "RemoveInvoices" . " ? ",
                ),
                "plus_invoice"              => array(
                    "name"        => "plus_invoice",
                    "command"     => "AddInvoice",
                    // "href"        => "&command=" . "AddInvoice",
                     "confirmtext" => "Хотите " . "AddInvoice" . " ? ",
                ),

                "plus_order"                => array(
                    "name"        => "plus_order",
                    "command"     => "NewOrder",
                    // "href"        => "&command=" . "NewOrder",
                     "confirmtext" => "Хотите " . "NewOrder" . " ? ",
                ),
                "remove_order"              => array(
                    "name"        => "remove_order",
                    "command"     => "remove_order",
                    // "href"        => "&command=" . "remove_order",
                     "confirmtext" => "Хотите " . "remove_order" . " ? ",
                ),
                "start_adminer"             => array(
                    "name"         => "start_adminer",
                    "command"      => "start_adminer",
                    "href"         => "adminer.php?username=" . DB_USER . "&amp;db=" . DB_NAME . "&amp;edit=" . "UTF_invoices" . "&where%5B" . "invoiceID" . "%5D=" . (int)$_invoice["invoiceID"],
                    "target_blank" => "_blank",
                    "confirmtext"  => "Хотите " . "start_adminer" . " ? ",
                ),
            ),

        )
    );

    $CONTROL_stampBYTE[$key] = new ControlModel(
        "invoiceID{$_invoice['invoiceID']}_stampsBYTE",
        "CONTROL_BITMASK",
        array(
            "header"       => "<strong>invoiceID #{$_invoice["invoiceID"]}</strong>",
            "hint"         => "Штампы и печати: Отметьте один или несколько вариантов",
            "compactlabel" => 0,
        ),
        array(
            "options"       => array(
                "1" => "М.П. -заполнитель",
                "2" => "Овальная печать",
                "4" => "Круглая печать",
                // "8" => "\"Особая\" печать",
            ),
            "fieldname"     => "stampsBYTE",
            "DBtable"       => INVOICES_TABLE,
            "primaryKey"    => "invoiceID",
            "current_value" => dbGetFieldData(
                $tablename = INVOICES_TABLE,
                $fieldname = "stampsBYTE",
                $where_clause = " `invoiceID`=" . (int)$_invoice["invoiceID"],
                $order_clause = "",
                $formatt = 1
            ),
            "editID"        => $_invoice["invoiceID"],
            "autoupdate"    => "auto",
        )
    );

    $CONTROL_showToUser[$key] = new ControlModel(
        "invoiceID{$_invoice['invoiceID']}_showToUser",
        "CONTROL_BITMASK",
        array(
            "header"       => "Вывод на печать",
            "hint"         => "Вывод на печать: Отметьте один или несколько вариантов",
            "compactlabel" => 0,
        ),
        array(
            "options"       => array(
                "1" => "ДОГОВОР",
                "2" => "СЧЕТ",
                "4" => "ГАРАНТИЯ",
                // "8" => "\"Особая\" печать",
            ),
            "fieldname"     => "showToUser",
            "DBtable"       => INVOICES_TABLE,
            "primaryKey"    => "invoiceID",
            "current_value" => dbGetFieldData(
                $tablename = INVOICES_TABLE,
                $fieldname = "showToUser",
                $where_clause = " `invoiceID`=" . (int)$_invoice["invoiceID"],
                $order_clause = "",
                $formatt = 1
            ),
            "editID"        => $_invoice["invoiceID"],
            "autoupdate"    => "auto",
        )
    );

    $CONTROL_buyerID[$key] = new ControlModel(
        "invoiceID{$_invoice['invoiceID']}_buyerID",
        "CONTROL_SELECTPICKER",
        array(
            "header"       => "",
            "hint"         => "Плательщик/покупатель",
            // "hint"         => "",
             "rowcol"       => "col-md-12",
            "compactlabel" => "0",
            "multiple"     => "0",
            "js"           => array(
                "liveSearch"  => 1,
                "showTick"    => 1,
                // "size"        => "auto",
                 "size"        => 8,
                "width"       => '50%',
                "showContent" => 1,
                // "showSubtext" => 1,

            ),
        ),
        array(
            "complex_options" => 1,
            "options"         => $Company_Options,
            "fieldname"       => "buyerID",
            "DBtable"         => INVOICES_TABLE,
            "primaryKey"      => "invoiceID",
            "current_value"   => (int)$_invoice["buyerID"],
            "editID"          => $_invoice["invoiceID"],
            "editlink"        => "?dpt=custord&sub=companies&edit_mode=",
            // "where_clause"=>"`invoiceID`={$editID}"
        )
    );
}

$smarty->assign( "CONTROL_ordercontent", $CONTROL_ordercontent );
$smarty->assign( "CONTROL_stampBYTE", $CONTROL_stampBYTE );
$smarty->assign( "CONTROL_showToUser", $CONTROL_showToUser );
$smarty->assign( "CONTROL_buyerID", $CONTROL_buyerID );
$smarty->assign( "CONTROL_invoiceToolbar", $CONTROL_invoiceToolbar );

// dd($CONTROL_invoiceToolbar);

?>