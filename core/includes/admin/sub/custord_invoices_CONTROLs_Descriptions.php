<?php
#
#
#
#

$CONTROL_invoice_hidden = new ControlModel(
    "CONTROL_invoice_hidden",
    "CONTROL_BITMASK",
    array(
        "header" => "Спрятать инвойс от вывода на печать клиентом",
        "hint"   => "Спрятать инвойс от вывода на печать клиентом",
    ),
    array(
        "options"       => array(
            "1" => "Спрятать для клиентов",
        ),
        "fieldname"     => "invoice_hidden",
        "DBtable"       => INVOICES_TABLE,
        "primaryKey"    => "invoiceID",
        "current_value" => (int)$Invoice["invoice_hidden"],
        "editID"        => $editID,
        "autoupdate"    => "auto",
        // "where_clause"=>"`invoiceID`={$editID}"
    )
);
$smarty->assign( "CONTROL_invoice_hidden", $CONTROL_invoice_hidden );

$CONTROL_stamp = new ControlModel(
    "CONTROL_stampsBYTE",
    "CONTROL_BITMASK",
    array(
        "header" => "Штампы и печати",
        "hint"   => "Отметьте один или несколько вариантов",
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
        "current_value" => (int)$Invoice["stampsBYTE"],
        "editID"        => $editID,
        "autoupdate"    => "auto",
        // "where_clause"=>"`invoiceID`={$editID}"
    )
);
$smarty->assign( "CONTROL_stamp", $CONTROL_stamp );

$CONTROL_showToUser = new ControlModel(
    "CONTROL_showToUser",
    "CONTROL_BITMASK",
    array(
        "header" => "Вывод на печать",
        "hint"   => "Отметьте один или несколько вариантов",

        // "compactlabel" => "1",
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
        "current_value" => (int)$Invoice["showToUser"],
        "editID"        => $editID,
        "autoupdate"    => "auto",
        // "where_clause"=>"`invoiceID`={$editID}"
    )
);
$smarty->assign( "CONTROL_showToUser", $CONTROL_showToUser );

$CONTROL_invoice_description = new ControlModel(
    "CONTROL_invoice_description",
    "CONTROL_TEXT",
    array(
        "header"       => "Комментарий|Описание",
        "rowcol"       => "col-md-12",
        "compactlabel" => "1",
    ),
    array(
        "fieldname"     => "invoice_description",
        "DBtable"       => INVOICES_TABLE,
        "primaryKey"    => "invoiceID",
        "current_value" => $Invoice["invoice_description"],
        "editID"        => $editID,
        // "where_clause"=>"`invoiceID`={$editID}"
    )
);
$smarty->assign( "CONTROL_invoice_description", $CONTROL_invoice_description );

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

$ostGetOrderStatuses = ostGetOrderStatuses();
$ostOptions          = array();
foreach ( $ostGetOrderStatuses as $key => $value ) {
    $ostOptions["{$value['statusID']}"] = "{$value['status_name']}<sub class='text-muted'>{$value['statusID']}</sub>";
}

$CONTROL_order_statusID = new ControlModel(
    "CONTROL_order_statusID",
    "CONTROL_RADIOBUTTONS",
    array(
        "header" => "Статус заказа(глобально)",
        // "compactlabel" => "1",
        // "hint"   => "Спрятать инвойс от вывода на печать клиентом",
    ),
    array(
        "options"       => ( $ostOptions ),
        "fieldname"     => "statusID",
        "DBtable"       => ORDERS_TABLE,
        "primaryKey"    => "orderID",
        "current_value" => (int)dbGetFieldData( ORDERS_TABLE, "statusID", " orderID = '" . $Invoice["orderID"] . "'" ),
        "editID"        => (int)dbGetFieldData( ORDERS_TABLE, "orderID", " orderID = '" . $Invoice["orderID"] . "'" ),
        // "autoupdate"    => "auto",
        // "where_clause"=>"`invoiceID`={$editID}"
    )
);
$smarty->assign( "CONTROL_order_statusID", $CONTROL_order_statusID );

$CONTROL_DeliveryType = new ControlModel(
    "CONTROL_DeliveryType",
    "CONTROL_RADIOBUTTONS",
    array(
        "header" => "Транспортировка",
        // "compactlabel" => "1",
        // "hint"   => "Спрятать инвойс от вывода на печать клиентом",
    ),
    array(
        "options"       => array(
            "0" => "Самовывоз Товара",
            "1" => "Доставка Товара",
        ),
        "fieldname"     => "DeliveryType",
        "DBtable"       => INVOICES_TABLE,
        "primaryKey"    => "invoiceID",
        "current_value" => $Invoice["DeliveryType"],
        "editID"        => $editID,
        "autoupdate"    => "auto",
        // "where_clause"=>"`invoiceID`={$editID}"
    )
);
$smarty->assign( "CONTROL_DeliveryType", $CONTROL_DeliveryType );

$CONTROL_PaymentType = new ControlModel(
    "CONTROL_PaymentType",
    "CONTROL_RADIOBUTTONS",
    array(
        "header" => "Оплата",
        // "compactlabel" => "1",
        // "hint"   => "Спрятать инвойс от вывода на печать клиентом",
    ),
    array(
        "options"       => array(
            "0" => "Поставка после оплаты",
            "1" => "Оплата после доставки",
        ),
        "fieldname"     => "PaymentType",
        "DBtable"       => INVOICES_TABLE,
        "primaryKey"    => "invoiceID",
        "current_value" => $Invoice["PaymentType"],
        "editID"        => $editID,
        "autoupdate"    => "auto",
        // "where_clause"=>"`invoiceID`={$editID}"
    )
);
$smarty->assign( "CONTROL_PaymentType", $CONTROL_PaymentType );

$CONTROL_payment_firstrpay_termin = new ControlModel(
    "CONTROL_payment_firstrpay_termin",
    "CONTROL_TEXT",
    array(
        "header"            => "Срок первого платежа",
        "input_group_addon" => " дн.",
        "textclass"         => "text-center",
        "compactlabel"      => "1",
    ),
    array(
        "fieldname"     => "payment_firstrpay_termin",
        "DBtable"       => INVOICES_TABLE,
        "primaryKey"    => "invoiceID",
        "current_value" => $Invoice["payment_firstrpay_termin"],
        "editID"        => $editID,
        "options"       => $termin_options,
        // "where_clause"=>"`invoiceID`={$editID}"
    )
);
$smarty->assign( "CONTROL_payment_firstrpay_termin", $CONTROL_payment_firstrpay_termin );
$CONTROL_payment_fullpay_termin = new ControlModel(
    "CONTROL_payment_fullpay_termin",
    "CONTROL_TEXT",
    array(
        "header"            => "Срок полного платежа",
        "input_group_addon" => " дн.",
        "textclass"         => "text-center",
        "compactlabel"      => "1",
        "helpblock"         => " [%b] - банковские дни [%n] - дни [%w] - рабочие дни [==] - произвольный текст",
    ),
    array(
        "fieldname"     => "payment_fullpay_termin",
        "DBtable"       => INVOICES_TABLE,
        "primaryKey"    => "invoiceID",
        "current_value" => $Invoice["payment_fullpay_termin"],
        "editID"        => $editID,
        "options"       => $termin_options,
        // "where_clause"=>"`invoiceID`={$editID}"
    )
);
$smarty->assign( "CONTROL_payment_fullpay_termin", $CONTROL_payment_fullpay_termin );

$CONTROL_payment_prepay = new ControlModel(
    "CONTROL_payment_prepay",
    "CONTROL_NUMBER",
    array(
        "header"            => "Процент предоплаты",
        "textclass"         => "text-right",
        "input_group_addon" => " % ",
        "reset"             => "1",
        "minus"             => "1",
        "plus"              => "1",
        "GenerateEvent"     => "1", "compactlabel" => "1",
    ),
    array(
        "step"          => 10,
        "min"           => 1,
        "max"           => 100,
        "decimals"      => 1,
        "fieldname"     => "payment_prepay",
        "DBtable"       => INVOICES_TABLE,
        "primaryKey"    => "invoiceID",
        "current_value" => (float)$Invoice["payment_prepay"],
        "editID"        => $editID,
        "options"       => array( "100" => "100", "50" => "50", "30" => "30", "20" => "20", "10" => "10", "5" => "5" ),
        // "where_clause"=>"`invoiceID`={$editID}"
    )
);
$smarty->assign( "CONTROL_payment_prepay", $CONTROL_payment_prepay );

$CONTROL_payment_transactions_count = new ControlModel(
    "CONTROL_payment_transactions_count",
    "CONTROL_NUMBER",
    array(
        "header"            => "Число платежей",
        "textclass"         => "text-right",
        "input_group_addon" => "<i class='fas fa-chart-pie'></i>",
        "reset"             => "1",
        "minus"             => "1",
        "plus"              => "1",
        "GenerateEvent"     => "1", "compactlabel" => "1",
    ),
    array(
        "step"          => 1,
        "min"           => 1,
        "max"           => 6,
        "decimals"      => 0,
        "fieldname"     => "payment_transactions_count",
        "DBtable"       => INVOICES_TABLE,
        "primaryKey"    => "invoiceID",
        "current_value" => (int)$Invoice["payment_transactions_count"],
        "editID"        => $editID,
        // "where_clause"=>"`invoiceID`={$editID}"
    )
);
$smarty->assign( "CONTROL_payment_transactions_count", $CONTROL_payment_transactions_count );

$CONTROL_delivery_termin = new ControlModel(
    "CONTROL_delivery_termin",
    "CONTROL_TEXT",
    array(
        "header"            => "Срок поставки",
        "input_group_addon" => " дн.",
        "textclass"         => "text-center", "compactlabel" => "1",
    ),
    array(
        "fieldname"     => "delivery_termin",
        "DBtable"       => INVOICES_TABLE,
        "primaryKey"    => "invoiceID",
        "current_value" => $Invoice["delivery_termin"],
        "editID"        => $editID,
        "options"       => $termin_options,
        // "where_clause"=>"`invoiceID`={$editID}"
    )
);
$smarty->assign( "CONTROL_delivery_termin", $CONTROL_delivery_termin );

$Customs         = array();
$Company_Options = array();
$Customs         = getOptionsForCompanies( $where_clause="" );
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
$CONTROL_buyerID = new ControlModel(
    "CONTROL_buyerID",
    "CONTROL_SELECTPICKER",
    array(
        "header"       => "Плательщик/покупатель",
        // "hint"         => "",
         "rowcol"       => "col-md-12",
        "compactlabel" => "1",
        "multiple"     => "0",
        "helpblock"    => "изначально из заказа, но можно поменять в счете",
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
        "current_value"   => (int)$Invoice["buyerID"],
        "editID"          => $editID,
        "editlink"          => "?dpt=custord&sub=companies&edit_mode=",
        // "where_clause"=>"`invoiceID`={$editID}"
    )

);
$CONTROL_sellerID = new ControlModel(
    "CONTROL_sellerID",
    "CONTROL_SELECTPICKER",
    array(
        "header"       => "Продавец",
        // "hint"         => "",
         "rowcol"       => "col-md-12",
        "compactlabel" => "1",
        "multiple"     => "0",
        "helpblock"    => " из модуля оплаты",
        "js"           => array(
            "liveSearch"  => 1,
            "showTick"    => 1,
            "size"        => 8,
            // "size"        => "auto",
             "width"       => '50%',
            "showContent" => 1,
        ),
    ),
    array(
        "complex_options" => 1,
        "options"         => $Company_Options,

        "fieldname"       => "sellerID",
        "DBtable"         => INVOICES_TABLE,
        "primaryKey"      => "invoiceID",
        "current_value"   => (int)$Invoice["sellerID"],
        "editID"          => $editID,
        "editlink"          => "?dpt=custord&sub=companies&edit_mode=",
        // "where_clause"=>"`invoiceID`={$editID}"
    )

);

$smarty->assign( "CONTROL_sellerID", $CONTROL_sellerID );
$smarty->assign( "CONTROL_buyerID", $CONTROL_buyerID );

$Customs          = array();
$Contract_Options = array();
$Customs          = contrGetAllContracts();
foreach ( $Customs as $_custom ) {
    if ( count( $Customs ) > 0 ) {
        $Contract_Options[(int)$_custom["contractID"]] = array(
            "Value"   => $_custom["contractID"],
            "Content" => "<strong class='text-plain-red'>" . zeroFill( $_custom["contractID"] ) . "</strong> " .
            wordwrap( $_custom["contract_title"], 120, "<br>\n", true ) .
            " <sub class='text-muted'>" . $_custom["update_time"] . "</sub>",
            "Subtext" => $_custom["update_time"],
            // "Tokens" => $_custom["contractID"],
        );
    }
}

$CONTROL_contractID = new ControlModel(
    "CONTROL_contractID",
    "CONTROL_SELECTPICKER",
    array(
        "header"       => "Текст договора",
        // "hint"         => "",
         "rowcol"       => "col-md-12",
        "compactlabel" => "1",
        "multiple"     => "0",
        "helpblock"    => " по-умолчанию из модуля оплаты, но можно индивидуальный",
        "js"           => array(
            "liveSearch"  => 1,
            "showTick"    => 1,
            "size"        => 8,
            "showContent" => 1,
            "showSubtext" => 0,
        ),
    ),
    array(
        "complex_options" => 1,
        "options"         => $Contract_Options,
        "fieldname"       => "contractID",
        "DBtable"         => INVOICES_TABLE,
        "primaryKey"      => "invoiceID",
        "current_value"   => (int)$Invoice["contractID"],
        "editID"          => $editID,
        // "where_clause"=>"`invoiceID`={$editID}"
    )
);
$smarty->assign( "CONTROL_contractID", $CONTROL_contractID );

$CONTROL_purposeID = new ControlModel(
    "CONTROL_purposeID",
    "CONTROL_SELECTPICKER",
    array(
        "header"       => "Цель приобретения",
        "hint"         => "Отметьте один или несколько вариантов",
        "rowcol"       => "col-md-12",
        "compactlabel" => "1",
        "multiple"     => "1",
        "js"           => array(
            "selectedTextFormat" => "count > 1",
            "actionsBox"         => "true",
        ),
    ),
    array(
        "options"       => array(
            "0" => "для собственного потребления/производства",
            "1" => "для оптовой/розничной торговли",
            "2" => "_____________________",
            "3" => "для оптовой торговли",
            "4" => "для розничной торговли",
            "5" => "для вывоза за пределы РБ",
            "6" => "для мира во всем мире",
            "7" => "для экспорта продукции за пределы Республики Беларусь",
        ),

        "fieldname"     => "purposeID",
        "DBtable"       => INVOICES_TABLE,
        "primaryKey"    => "invoiceID",
        "current_value" => getSimpleOptionsArray( $Invoice["purposeID"] ),
        "editID"        => $editID,
        // "where_clause"=>"`invoiceID`={$editID}"
    )
);
$smarty->assign( "CONTROL_purposeID", $CONTROL_purposeID );

$CONTROL_fundingID = new ControlModel(
    "CONTROL_fundingID",
    "CONTROL_SELECTPICKER",
    // "CONTROL_SELECT",
    array(
        "header"       => "Источник финансирования",
        "hint"         => "Отметьте один или несколько вариантов",
        "rowcol"       => "col-md-12",
        "compactlabel" => "1",
        "multiple"     => "1",
        "js"           => array(
            "selectedTextFormat" => "count > 1",
            "actionsBox"         => "true",
        ),
    ),
    array(
        "options"       => array(
            "0" => "собственные средства",
            "1" => "Республиканский бюджет",
            "2" => "_____________________",
            "3" => "Казначейство РБ",
            "4" => "спонсорская помощь",
            "5" => "неизвестный источник",
            "6" => "внебюджетные средства",
        ),

        "fieldname"     => "fundingID",
        "DBtable"       => INVOICES_TABLE,
        "primaryKey"    => "invoiceID",
        "current_value" => getSimpleOptionsArray( $Invoice["fundingID"] ),
        "editID"        => $editID,
        // "where_clause"=>"`invoiceID`={$editID}"
    )
);
$smarty->assign( "CONTROL_fundingID", $CONTROL_fundingID );

$Customs          = array();
$Currency_Options = array();
$Customs          = currGetAllCurrencies();
foreach ( $Customs as $_custom ) {
    if ( count( $Customs ) > 0 ) {
        $Currency_Options[(int)$_custom["CID"]] = array(
            "Value"      => $_custom["CID"],
            "Content"    => "<strong class='text-plain-red'>" . $_custom["currency_iso_3"] . " <sub>{$_custom["CID"]}</sub></strong> " .
            $_custom["Name"] .
            ", " . $_custom["code"] . " => " . _formatPrice( $_custom["currency_value"], 4, ".", "" ) . " : $1 <small class='text-muted'>текущий курс</small>",
            "Subtext"    => _formatPrice( $_custom["currency_value"], 4, ".", "" ),

            "Content_Li" => "<strong class='text-plain-red'>" . $_custom["currency_iso_3"] . " <sub>{$_custom["CID"]}</sub></strong> " .
            $_custom["Name"] .
            ", " . $_custom["code"] . " => <span class=\"label label-danger\">" . _formatPrice( $_custom["currency_value"], 4, ".", "" ) . " : $1</span> <small class='text-muted'>текущий курс</small>" . (  ( $_custom["currency_iso_3"] == "BNN" ) ? "<label class='label label-warning pull-right'>НДС ВКЛЮЧЕН !</label>" : "" ),
            "row"        => $_custom,
        );
    }
}

$OrderCID              = currGetCurrencyByISO3( $Order["currency_code"], $FIELD_TO_RETURN = "CID" );
$current_currency_iso3 = $Currency_Options[$PaymentModule->getModuleSettingValue( PM_CURRENCY )]["row"]["currency_iso_3"];

$Invoice_currency_context        = $Currency_Options[(int)$Invoice["CID"]]["Content_Li"];
$Order_currency_context          = $Currency_Options[(int)$OrderCID]["Content_Li"];
$Payment_Module_currency_context = $Currency_Options[$PaymentModule->getModuleSettingValue( PM_CURRENCY )]["Content_Li"];

$Invoice_currency_rate = _formatPrice(  ( $Invoice["currency_rate"] ), 4, ".", "" );
// $Invoice_currency_rate        =  _formatPrice(currGetCurrencyRateByID( (int)$Invoice["CID"] ), 4, ".", "" )  ;
$Order_currency_rate          = _formatPrice( $Order["currency_value"], 4, ".", "" );
$Payment_Module_currency_rate = $Invoice_currency_rate;

$currency_helpblock = <<<HTML
<ul class="list-group" style="opacity: 0.75;">
    <li class="list-group-item">
        <h4 class="list-group-item-heading"><strong>курс валюты в счете</strong> <span class="label label-danger">{$Invoice_currency_rate}:1$</span></h4>
        <del><p class="list-group-item-text text-muted">{$Invoice_currency_context}</p></del>
    </li>
    <li class="list-group-item">
        <del><h4 class="list-group-item-heading">курс валюты в заказе <span class="label label-danger">{$Order_currency_rate}:$1</span></h4></del>
        <del><p class="list-group-item-text text-muted">{$Order_currency_context}</p></del>
    </li>
    <li class="list-group-item">
        <del><h4 class="list-group-item-heading">курс валюты в модуле <span class="label label-danger">{$Payment_Module_currency_rate}:1$</span></h4></del>
        <p class="list-group-item-text">{$Payment_Module_currency_context}</p>
    </li>
</ul>
HTML;

$CONTROL_currency_rate = new ControlModel(
    "CONTROL_currency_rate",
    "CONTROL_NUMBER",
    array(
        "header"        => "Валюта <span id='current_currency_iso3' name='current_currency_iso3' title='Валюта на момент заказа'>{$current_currency_iso3}
        </span>. Курс, по которому выставляется счет",
        "textclass"     => "text-right",
        "rowcol"        => "col-md-12",
        "rowcol_label"  => "col-md-12",
        "rowcol_help"   => "col-md-12 visible-md visible-lg",
        "reset"         => "1",
        "minus"         => "1",
        "plus"          => "1",
        "GenerateEvent" => "1",
        "helpblock"     => "<b>именно этот курс, участвует в расчете цен в .pdf -файле</b>",
    ),
    array(
        "step"          => 0.01,
        "min"           => 0.0001,
// "max" =>10.2,
         "decimals"      => 4,
        "fieldname"     => "currency_rate",
        "DBtable"       => INVOICES_TABLE,
        "primaryKey"    => "invoiceID",
        "current_value" => _formatPrice( $Invoice["currency_rate"], 4, ".", "" ),
        "editID"        => $editID,
        // "where_clause"=>"`invoiceID`={$editID}"
    )
);
$smarty->assign( "CONTROL_currency_rate", $CONTROL_currency_rate );

$CONTROL_invoice_subdiscount = new ControlModel(
    "CONTROL_invoice_subdiscount",
    "CONTROL_NUMBER",
    array(
        "header"            => "СКИДКА для этого Инвойса",
        "textclass"         => "text-right",
        "rowcol"            => "col-md-12",
        "rowcol_label"      => "col-md-12",
        "rowcol_help"       => "col-md-12 visible-md visible-lg",
        "reset"             => "1",
        "minus"             => "1",
        "plus"              => "1",
        "input_group_addon" => " %",
        "GenerateEvent"     => "1",
        "helpblock"         => "<b>индивидуальня скидка для инвойса</b>",
    ),
    array(
        "step"          => 1,
        "min"           => -99,
        "max"           => 99,
        "decimals"      => 2,
        "fieldname"     => "invoice_subdiscount",
        "DBtable"       => INVOICES_TABLE,
        "primaryKey"    => "invoiceID",
        "current_value" => _formatPrice( $Invoice["invoice_subdiscount"], 2, ".", "" ),
        "editID"        => $editID,
        // "where_clause"=>"`invoiceID`={$editID}"
    )
);
$smarty->assign( "CONTROL_invoice_subdiscount", $CONTROL_invoice_subdiscount );

$CONTROL_deliveryFrom = new ControlModel(
    "CONTROL_deliveryFrom",
    "CONTROL_TEXT",
    array(
        "header"       => "Адрес погрузки",
        "rowcol"       => "col-md-12",
        "compactlabel" => "1",
    ),
    array(
        "fieldname"     => "deliveryFrom",
        "DBtable"       => INVOICES_TABLE,
        "primaryKey"    => "invoiceID",
        "current_value" => $Invoice["deliveryFrom"],
        "editID"        => $editID,
        // "where_clause"=>"`invoiceID`={$editID}"
    )
);
$smarty->assign( "CONTROL_deliveryFrom", $CONTROL_deliveryFrom );

$CONTROL_deliveryTo = new ControlModel(
    "CONTROL_deliveryTo",
    "CONTROL_TEXT",
    array(
        "header"       => "Адрес разгрузки",
        "rowcol"       => "col-md-12",
        "compactlabel" => "1",
    ),
    array(
        "fieldname"     => "deliveryTo",
        "DBtable"       => INVOICES_TABLE,
        "primaryKey"    => "invoiceID",
        "current_value" => $Invoice["deliveryTo"],
        "editID"        => $editID,
        // "where_clause"=>"`invoiceID`={$editID}"
    )
);
$smarty->assign( "CONTROL_deliveryTo", $CONTROL_deliveryTo );

$CONTROL_invoice_time = new ControlModel(
    "CONTROL_invoice_time",
    "CONTROL_DATETIME",
    array(
        "header"       => "Время выставления счёта-инвойса",
        // "rowcol"    => "col-md-10",
         "compactlabel" => "1",
        "inline"       => "0",
        "timepicker"   => "0",
    ),
    array(
        "fieldname"     => "invoice_time",
        "DBtable"       => INVOICES_TABLE,
        "primaryKey"    => "invoiceID",
        "current_value" => $Invoice["invoice_time"],
        "editID"        => $editID,
        "autoupdate"    => "0",
        // "where_clause"=>"`invoiceID`={$editID}"
    )
);
$smarty->assign( "CONTROL_invoice_time", $CONTROL_invoice_time );

$CONTROL_contract_time = new ControlModel(
    "CONTROL_contract_time",
    "CONTROL_DATETIME",
    array(
        "header"       => "Время оформления [первого] заказа и/или подписания договора",
        "hint"         => "Время оформления [первого] заказа и/или подписания договора [если к договору есть несколько счетов]",
        // "rowcol"    => "col-md-10",
         "compactlabel" => "1",
        "inline"       => "0",
        "timepicker"   => "0",
    ),
    array(
        "fieldname"     => "contract_time",
        "DBtable"       => INVOICES_TABLE,
        "primaryKey"    => "invoiceID",
        "current_value" => $Invoice["contract_time"],
        "editID"        => $editID,
        // "where_clause"=>"`invoiceID`={$editID}"
    )
);
$smarty->assign( "CONTROL_contract_time", $CONTROL_contract_time );

$CONTROL_update_time = new ControlModel(
    "CONTROL_update_time",
    "CONTROL_ALERT",
    array(
        "alertclass"   => "alert-info",
        "compactlabel" => 1,
        "header"       => "Время последнего изменения:",
        "hint"         => "Актуально после обновления страницы",
        "rowcol"       => "col-md-10 col-md-push-1",
    ),
    array(
        "autoupdate"    => "auto",
        "timestamp"     => "update_time",
        "fieldname"     => "update_time",
        "DBtable"       => INVOICES_TABLE,
        "primaryKey"    => "invoiceID",
        "current_value" => FormatAsRUS( $Invoice["update_time"], $ShowTime = 7, $YearMark = "г." ),
    )
);
$smarty->assign( "CONTROL_update_time", $CONTROL_update_time );

$CONTROL_orderID_number = new ControlModel(
    "CONTROL_orderID_number",
    "CONTROL_ALERT",
    array(
        "alertclass"   => " alert alert-danger",
        "compactlabel" => 1,
        "header"       => "Заказ",
        "hint"         => "Номер заказа (товарной части)",
        // "rowcol"       => "col-md-12",
    ),
    array(
        "fieldname"     => "orderID",
        "DBtable"       => INVOICES_TABLE,
        "primaryKey"    => "invoiceID",
        "current_value" => "№" . zeroFill( $Invoice["orderID"] ) . " от " . FormatAsRUS( dbGetFieldData(
            $tablename = ORDERS_TABLE,
            $fieldname = "order_time",
            $where_clause = " `orderID`=" . (int)$Invoice['orderID'],
            $order_clause = "",
            $formatt = 1
        ), "г" ),
    )
);
$smarty->assign( "CONTROL_orderID_number", $CONTROL_orderID_number );

$CONTROL_order_aka_number = new ControlModel(
    "CONTROL_order_aka_number",
    "CONTROL_TEXT",
    array(
        "compactlabel" => 1,
        "header"       => "Псевдономер заказа (глобально)",
        "hint"         => "Псевдономер заказа (товарной части) в ORDERS_TABLE",
        // "rowcol"       => "col-md-12",
    ),
    array(
        "fieldname"     => "order_aka",
        "DBtable"       => ORDERS_TABLE,
        "primaryKey"    => "orderID",
        "editID"        => $Invoice["orderID"],
        "current_value" => $Order["order_aka"],
    )
);
$smarty->assign( "CONTROL_order_aka_number", $CONTROL_order_aka_number );

$orderID = (int)$Invoice['orderID'];

$aka = ( stristr( $Order["order_aka"], "==" ) || (int)$Order["order_aka"] > 0 )
? "{$Order['order_aka']}"
: (int)$Order["orderID"];

$Currency_rate       = $Invoice["currency_rate"];
$shipping_cost       = $Order["shipping_cost"];         // CONTROL_orderID_number
$order_discount      = $Order["order_discount"];        // CONTROL_orderID_number
$invoice_subdiscount = $Invoice["invoice_subdiscount"]; // CONTROL_orderID_number

$invoice_dates = dtDoDates( $Invoice["invoice_time"], $aka, $YearMark = "г." );
$contact_dates = dtDoDates( $Invoice["contract_time"], $aka, $YearMark = "г." );

$PM_NDS_IS_INCLUDED_IN_PRICE = $PaymentModule->getModuleSettingValue( PM_NDS_IS_INCLUDED_IN_PRICE ); //module
$PM_NDS_RATE                 = $PaymentModule->getModuleSettingValue( PM_NDS_RATE );                 //module
$PM_MULTIPLIER               = $PaymentModule->getModuleSettingValue( PM_MULTIPLIER );
$PM_INVOICE_STRING           = $PaymentModule->getModuleSettingValue( PM_INVOICE_STRING );
$PM_CONTRACT_STRING          = $PaymentModule->getModuleSettingValue( PM_CONTRACT_STRING );          //module
$PM_CONTRACT_TYPE          = $PaymentModule->getModuleSettingValue( PM_CONTRACT_TYPE );          //module


$dec = $currentCurrency["roundval"];

$CONTROL_ordercontent = new ControlModel(
    "CONTROL_ordercontent",
    "CONTROL_ORDERCONTENT",
    array( "compactlabel" => 0,
        "header"             => "Товарная часть",
        "hint"               => "Курс валюты на день заказа может отличаться !!!",
        "invoice_hidden"     => $Invoice["invoice_hidden"],

    ),
    array(
        "fieldname"      => "orderID",
        "DBtable"        => INVOICES_TABLE,
        "primaryKey"     => "invoiceID",
        "cm"             => $currentCurrency["code"],

        "invoiceID"      => $Invoice["invoiceID"],
        "current_value"  => ordAdvancedOrderContent(
            $orderID,
            $Currency_rate,               //module
            $shipping_cost,               // order
            $order_discount,              //order
            $PM_NDS_IS_INCLUDED_IN_PRICE, //module
            $PM_NDS_RATE,                 //module
            $PM_MULTIPLIER,               //module
            $dec,                         //currency
            $current_currency_iso3,
            $invoice_subdiscount //currency
        ),
        "ff4_invoice" => "{$PM_INVOICE_STRING}" . " " . $invoice_dates["ff4_string"],
        "ff4_contract" => "{$PM_CONTRACT_STRING}" . " " . $contact_dates["ff4_string"],
    )
);
$smarty->assign( "CONTROL_ordercontent", $CONTROL_ordercontent );

$payment_configs = modGetAllInstalledModuleObjs( PAYMENT_MODULE );
if ( count( $payment_configs ) > 0 ) {

    foreach ( $payment_configs as $_Ind => $_Conf ) {

// consolelog( "{$_Conf->get_id()}");
        if ( $_Conf->is_installed() ) {
// ddd($_Conf);

            // $payment_configs[$_Ind]["description"] = $paymentType[0]["description"];

            $payment_configs[$_Ind] = array(
                "ConfigID"                    => $_Conf->get_id(),
                "ConfigName"                  => $_Conf->title,
                "ConfigClassName"             => get_class( $_Conf ),

                "PM_CURRENCY"                 => currGetCurrencyByID( $_Conf->getModuleSettingValue( PM_CURRENCY ) ),
                "PM_NDS_IS_INCLUDED_IN_PRICE" => $_Conf->getModuleSettingValue( PM_NDS_IS_INCLUDED_IN_PRICE ),
                "PM_NDS_RATE"                 => $_Conf->getModuleSettingValue( PM_NDS_RATE ),
                "PM_MULTIPLIER"               => $_Conf->getModuleSettingValue( PM_MULTIPLIER ),
                "PM_INVOICE_STRING"           => $_Conf->getModuleSettingValue( PM_INVOICE_STRING ),
                "PM_CONTRACT_STRING"          => $_Conf->getModuleSettingValue( PM_CONTRACT_STRING ),
                "PM_SELLER"                   => $_Conf->getModuleSettingValue( PM_SELLER ),
                "PM_DEFAULT_TEMPLATE"         => $_Conf->getModuleSettingValue( PM_DEFAULT_TEMPLATE ),
                "PM_DEFAULT_CONTRACT_ID "     => $_Conf->getModuleSettingValue( PM_DEFAULT_CONTRACT_ID ),
            );

            $paymentType                           = payGetPaymentMethodBy_module_id( $_Conf->get_id() );
            $payment_configs[$_Ind]["description"] = $paymentType["description"];
        }
    }

    $PaymentModule_options = array();
    foreach ( $payment_configs as $_custom ) {
        $module_description = "";
        ( $_custom['PM_INVOICE_STRING'] == $_custom['PM_CONTRACT_STRING'] ) ?
        $module_description .= $_custom['PM_INVOICE_STRING'] :
        $module_description .= $_custom['PM_INVOICE_STRING'] . " + " . $_custom['PM_CONTRACT_STRING'];
        $module_description .= ": ";
        ( $PM_MULTIPLIER != 1 ) ? $module_description .= " Множитель = {$_custom['PM_MULTIPLIER']}; " : "";
        $module_description .= "{$_custom['PM_CURRENCY']['Name']},{$_custom['PM_CURRENCY']['code']} <b>{$_custom['PM_CURRENCY']['currency_iso_3']}</b> ";
        $module_description .= _formatPrice( $_custom['PM_CURRENCY']['currency_value'], 4, ".", "" ) . ":1 ; ";
        ( $_custom['PM_NDS_IS_INCLUDED_IN_PRICE'] ) ?
        $module_description .= " Включен" . "<b> НДС {$_custom['PM_NDS_RATE']}% </b>" . " в курс валюты" :
        $module_description .= " НДС НЕ включен" . " в курс валюты";
        $module_description .= "; ";
        if ( $_custom['PM_SELLER'] != 1 ) {
            $module_description .= _getCompanyNameOnly( $_custom['PM_SELLER'] );
        }
        $module_description .= ( $_custom['PM_DEFAULT_TEMPLATE'] ) . "+" . $_custom['PM_DEFAULT_CONTRACT_ID '];

        $PaymentModule_options["{$_custom['ConfigID']}"] = array(
            "Value"   => $_custom["ConfigID"],
            "Content" => "<strong class='text-plain-red'>" . $_custom["ConfigID"] . "</strong> " . $module_description,
            "Tokens"  => $_custom["ConfigID"],
            "Title"   => "{$_custom["ConfigID"]} {$_custom["description"]}",
            "Subtext" => $_custom["ConfigClassName"],
            // "row"     => $_custom,
        );
    }

    $CONTROL_PAYMENTMODULE = new ControlModel(
        "CONTROL_PAYMENTMODULE",
        "CONTROL_SELECTPICKER",
        // "CONTROL_SELECT",
        array(
            "header"       => "Модуль выставляемого счета",
            "rowcol"       => "col-md-12",
            "disabled"     => 0,
            "compactlabel" => "1",
            "js"           => array(
                "liveSearch"  => 0,
                "showTick"    => 1,
                "size"        => 8,
                // "size"        => "auto",
                 "width"       => '50%',
                "showContent" => 1,
            ),
        ),
        array(
            "complex_options" => 1,
            "options"         => $PaymentModule_options,

            "fieldname"       => "module_id",
            "DBtable"         => INVOICES_TABLE,
            "primaryKey"      => "invoiceID",
            "current_value"   => (int)$Invoice["module_id"],
            "editID"          => $editID,
            // "where_clause"=>"`invoiceID`={$editID}"
        )
    );
}
$smarty->assign( "CONTROL_PAYMENTMODULE", $CONTROL_PAYMENTMODULE );

#
#C:\MyProjects\hoster.by\www.nixminsk.by\core\tpl\admin\controls\CONTROL_ORDERCONTENT.tpl.html
#
#

?>