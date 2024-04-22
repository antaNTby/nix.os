<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2019-01-22       #
#          http://nixby.pro              #
##########################################

### НАСТРОЙКА ТАБЛИЦЫ
$_titles = array(
    "invoiceID",
    "Заказ&nbsp;#",
    "Товары <br> актуальный курс " . invoiceUSDformat( currGetCurrencyRateByID( CONF_DEFAULT_CURRENCY ) ) . " без НДС",
    "Статус заказа",
    "Тип счета",
    "Счет от",
    "Договор от",
    "Поставщик",
    "Покупатель",
    "update_time",
    "Комментарий",
    "Ссылка",
    // "Таблица товаров",
 );
$DT_fields = array(
    "invoiceID",           //0
     "orderID",             //1
     "invoiceID", //"orderID",             //2
     "orderID",             //3
     "module_id",           //4
     "invoice_time",        //5
     "contract_time",       //6
     "sellerID",            //7
     "buyerID",             //8
     "update_time",         //9
     "invoice_description", //10
     "module_id",           //11
     // "invoiceID",           //12
 );
$_visible = array(
    "invoiceID",
    "orderID",
    "module_id",
    "invoice_time",
    // "contract_time",
     // "sellerID",
     "buyerID",
    // "update_time",
     "invoice_description",
);
$_orderable = array(
    "invoiceID",
    "orderID",
    "module_id",
    "invoice_time",
    "contract_time",
    "sellerID",
    "buyerID",
    "update_time",
    "invoice_description",
);
$_searchable = array(
    "invoiceID",
    "orderID",
    "module_id",
    // "invoice_time",
     // "contract_time",
     // "sellerID",
     "buyerID",
    "update_time",
    "invoice_description",
);
$_Left = array(
    "sellerID",
    "buyerID",
    "invoice_description",
    "module_id",
);
$_Center = array(
    "orderID",
);
$_Right = array(
    "_all",
);
$_th = array(
    // "invoiceID",
     "orderID",
);
$DT_columns             = array();
$DT_columnDefs          = array();
$left_targets           = array();
$center_targets         = array();
$right_targets          = array();
$th_targets             = array();
$render_targets         = array();
$DT_columnDefs_separate = array();
$auxData                = array();
$ii                     = 0;
foreach ( $DT_fields as $key => $field ) {
    # Array of database columns which should be read and sent back to DataTables.
    # The `db` parameter represents the column name in the database, while the `dt`
    # parameter represents the DataTables column identifier. In this case object
    # parameter names
    $DT_columns[$key]["db"] = $field;
    // $DT_columns[$key]["dt"]    = $field;
    $DT_columns[$key]["dt"]    = $ii;
    $DT_columns[$key]["title"] = $_titles[$ii];
    if ( in_array( $field, $_visible ) ) {
        $DT_columns[$key]["visible"] = true;
    } else {
        $DT_columns[$key]["visible"] = false;
    }
    if ( in_array( $field, $_orderable ) ) {
        $DT_columns[$key]["orderable"] = true;
    } else {
        $DT_columns[$key]["orderable"] = false;
    }
    if ( in_array( $field, $_searchable ) ) {
        $DT_columns[$key]["searchable"] = true;
    } else {
        $DT_columns[$key]["searchable"] = false;
    }

    # FORMATTER
    switch ( $field ) {
        case "sellerID":
            $DT_columns[$key]["formatter"] = function ( $d, $row ) {
                return htmlCompanyPreview( _getCompany( $d ), 0 );
            };
            break;
        case "buyerID":
            $DT_columns[$key]["formatter"] = function ( $d, $row ) {
                return htmlCompanyPreview( _getCompany( $d ), 0 );
            };
            break;
        case ( "orderID" and $ii == 1 ):
            $DT_columns[$key]["formatter"] = function ( $d, $row ) {
                return zeroFill( $d );
            };
            break;

        case ( "orderID" and $ii == 12 ):
            $DT_columns[$key]["formatter"] = function ( $d, $row ) {
                $html_ii2 = htmlOrderContentPreview( $d, 1, 0, 1, 1 ); // в Долларах
                ## $html_ii2=htmlOrderContentPreview( $d, 5, $showPriceUSD = 1, $showBody = 1, $showFooter = 0 );// в Рублях с НДС
                return $html_ii2;
                // return "==";
            };
            break;

        case ( "invoiceID" and $ii == 2 ):

            $DT_columns[$key]["formatter"] = function ( $d, $row ) {
                $AdvancedOrderContent = generateInvoiceOrderContentPreview( $d );

                $smarty0                = new Smarty();
                $smarty0->template_dir  = "core/tpl/common/";
                // $smarty0->template_dir  = "core/tpl/user/";
                $smarty0->force_compile = true;
                $smarty0->assign( 'AdvancedOrderContent', $AdvancedOrderContent );
                $html = $smarty0->fetch( 'AdvancedOrderContent.tpl.html' );
                return $html;
            };

            break;

        case ( "orderID" and $ii == 3 ):
            $DT_columns[$key]["formatter"] = function ( $d, $row ) {
                $auxData = invoiceGetOrderData( $d );
                $res     = ( $auxData["error"] ) ? $auxData["error"] : "<sub class='hidden'>" . $auxData["statusID"] . "</sub> " . $auxData["status_name"];
                return $res;
            };
            break;

        case ( "module_id" and $ii == 4 ):
            $DT_columns[$key]["formatter"] = function ( $d, $row ) {
                return "<sub>$d</sub> " . invoiceGetModuleSettingValue( $d, "PM_INVOICE_STRING" );
            };
            break;
        case ( "module_id" and $ii == 11 ):
            $DT_columns[$key]["formatter"] = function ( $d, $row ) {

                $module_name = invoiceGetModuleSettingValue( $d, "PM_INVOICE_STRING" );

                $html = $d;
                $html .= "<br>";
                // $html .= "http://www.nixminsk.by/index.php?do=invoice_new_proc&gm=OTQ=&go=1339&g1=MjAyMC0wOS0xNCAxNDozNzozNg==&g2=YW50YU5ULnByb0BnbWFpbC5jb20=";

                // $default_invoice_href = getLinkToShowInvoice(
                //     $ModuleConfigID = base64_encode( $row["module_id"] ),
                //     $invoiceID = $row["invoiceID"],
                //     $orderID = $row["orderID"],
                //     $companyID = $row["buyerID"],
                //     $currencyID = null,
                //     $order_time = null,
                //     $customer_email = null
                // );

                $default_invoice_href = urldecode( getNewPdfLink( $row["invoiceID"] ) );

                $default_invoicePDF_button = <<<HTML
                    <a id="link_default" class="btn btn-primary btn-link" href="{$default_invoice_href}" target="_blank">
                    {$module_name}.pdf
                    <span class="sub-left-icon font-s text-dark pin-bl">{$icon_print}</span>
                    <span class="main-button-icon font-l text-red">{$icon_pdf}</span>
                    <span class="text-dark">&nbsp;Вывести .pdf</span>
                    </a>
HTML;
                $html = "";
                // $html .= "{$default_invoicePDF_button}";
                $html .= "{$default_invoice_href}";

                return $html;

            };
            break;
    }

    if ( in_array( $field, $_Left ) ) {
        // $targets[] = $ii;
        array_push( $left_targets, (int)$ii );
    }
    if ( in_array( $field, $_Center ) ) {
        // $targets[] = $ii;
        array_push( $center_targets, (int)$ii );
    }

    $Align = array(
        array( "className" => "text-left", "targets" => $left_targets ),
        array( "className" => "text-center", "targets" => $center_targets ),
        array( "className" => "text-right", "targets" => "_all" ),
    );
    // $DT_columnDefs_separate["className-text-left"]   = $left_targets;
    // $DT_columnDefs_separate["className-text-center"] = $center_targets;
    // $DT_columnDefs_separate["className-text-right"]  = "_all";

    if ( in_array( $field, $_th ) ) {
        array_push( $th_targets, (int)$ii );
    }
    $TH = array(
        array( "cellType" => "th", "targets" => $th_targets ),
    );
    // $DT_columnDefs_separate["cellType-th"] = $th_targets;

    $ii++;
}

#$DT_columnDef - настройки по умолчанию для таблицы классы и сстили
$DT_columnDefs = array_merge( $Align, $TH );

# финальные кастомные настройки
$DT_columns[2]["searchable"]  = false; // запрещаем поиск в таблице товаров
$DT_columns[2]["orderable"]   = false; // запрещаем orderable в таблице товаров
// $DT_columns[2]["visible"]     = false; // запрещаем orderable в таблице товаров
$DT_columns[3]["visible"]     = false; // запрещаем visible в таблице товаров
$DT_columns[10]["visible"]    = false; // запрещаем visible в таблице товаров module_id
$DT_columns[11]["visible"]    = true;  // запрещаем visible в таблице товаров module_id
$DT_columns[11]["searchable"] = false; // запрещаем поиск в таблице товаров
$DT_columns[11]["orderable"]  = false; // запрещаем orderable в таблице товаров
                                       // $DT_columns[11]["className"]  = "details-control"; // запрещаем orderable в таблице товаров



// $new_col=new stdClass();//create a new
// $new_col=(
//     "class" -> 'details-control',
//     "orderable" -> false,
//     "data" -> null,
//     "db" -> null,
//     "dt" -> null,
// );

// "class" => 'details-control',
// "orderable" => false,
// "data" => null,
// "db" => null,
// "dt" => null,

// $new_col = (object) array(
//    "className" => 'details-control',
//     "orderable" => false,
//     // "data" => null,
//     // "db" => null,
//     "targets" => array(12),
// );

// array_push( $DT_column, $new_col );
// array_push( $DT_columnDefs, $new_col );

# $DT_columns[2]["visible"] = false;    // запрещаем visible в таблице товаров
# $DT_columns[0]["visible"] = false;    // запрещаем visible в таблице товаров

// consolelog($DT_columns);

$smarty->assign( "DT_columnsJSON", json_encode( $DT_columns ) );
$smarty->assign( "DT_columnDefsJSON", json_encode( $DT_columnDefs ) );

### НАСТРОЙКА ТАБЛИЦЫ

?>