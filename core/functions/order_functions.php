<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-08-23       #
#          http://nixby.pro              #
##########################################

function updateOrderAmount( $orderID ) {
    $order = ordGetOrder( (int)$orderID );
    $new   = getMyOrderContent(
        $orderID = $orderID,
        $targetCurrencyRate = $order["currency_value"],
        $hasVatIncluded = ( $order["currency_code"] == "BNN" ) ? 1 : 0,
        $VAT_Rate = DEFAULT_VAT_RATE,
        // $precision = $order["currency_round"],
        $precision = 4,
        // $targetCurrencyCode = "BYN"
        $targetCurrencyCode = $order["currency_code"]
    );

    $sqlOrderTable = "UPDATE " . ORDERS_TABLE . " " .
        "SET " .
        "order_amount=" . $new["Total"]["totalCost_WITH_VAT"] / $new["targetCurrencyRate"] . " " .
        "WHERE orderID={$orderID}; ";
    $sql = $sqlOrderTable;
    $q2  = db_multiquery( $sql );
    return $q2;
}

function checkOrderExist( $orderID ) {

    $targetOrderID = (int)$orderID;

    $checkOrderIdSql = "SELECT count(*) FROM " . ORDERS_TABLE . " " .
        "WHERE orderID={$targetOrderID}; ";
    $q     = db_query( $checkOrderIdSql );
    $count = db_fetch_row( $q );
    $count = $count[0];
    return ( $count == 1 ) ? true : false;

}

function htmlOrderContentPreview(
    $orderID,
    $CID = CONF_DEFAULT_CURRENCY,
    $showPriceUSD = 1,
    $showBody = 1,
    $showFooter = 1
) {

    $q_count   = db_query( "SELECT COUNT(*) AS itemCount FROM " . ORDERED_CARTS_TABLE . " WHERE enabled=1 AND orderID=" . (int)$orderID );
    $row_count = db_fetch_assoc( $q_count );

    if ( $row_count["itemCount"] > 0 ) {
        $q = db_query( "SELECT name, LEFT(name,64) AS trimname , Price, Quantity, itemID, itemUnit FROM " . ORDERED_CARTS_TABLE . " WHERE enabled=1 AND orderID=" . (int)$orderID . " ORDER BY itemPriority DESC, itemID ASC" );

        $order = _getOrderById( $orderID );
        $html  = "";
        $html .= "<div class=\"text-muted\" style=\"padding:2px;margin-bottom: 0px;\">";

        $html .= "<table class=\"table table-condensed table-bordered\" style=\"margin:0;\">";

        $html .= "<thead>";
        $html .= "</thead>";

        $labelWithNDS = ( $CID == 5 ) ? " <label class='label label-success'> НДС включен </label> " : " <label class='label label-danger'> НДС не включен </label> ";

        $summ = 0;
        $ii   = 1;
        if ( $showBody ) {
            $html .= "<tbody>";
            while ( $row = db_fetch_assoc( $q ) ) {
                # code...
                // $summ += roundf( $row["Quantity"] * $row["Price"] );
                $summ += _formatPrice( $row["Quantity"] * $row["Price"], 4, ".", "" );

                $html .= "<tr>";
                $html .= "<td>{$ii}</td>";
                $html .= "<td title=\"название\" style=\"vertical-align:middle;text-align:left;font-size:80%;line-height:90%!important;font-weight:normal;\">";
                $html .= $row["trimname"];
                $html .= "</td>";
                $html .= "<td title=\"количество\" style=\"vertical-align:middle;text-align:right;font-size:80%;line-height: 80%!important;\">";
                $html .= "<small class='text-left'>{$row['Quantity']}&nbsp;{$row['itemUnit']}</small>";
                $html .= "</td>";
                if ( $showPriceUSD ) {
                    $html .= "<td title=\"Прайс\" style=\"vertical-align:middle;text-align:right;font-size:80%;\">";
                    $html .= ShowPriceInTheUnit( _formatPrice( $row["Price"], 4, ".", "" ), $CID );
                    // $html .= ShowPriceInTheUnit( $summ, $CID );
                    if ( $showPriceUSD ) {
                        $html .= " = " . invoiceUSDformat( $row["Price"], "$" );
                    }
                    $html .= "</td>";
                }
                $html .= "</tr>";
                $ii++;
            }

            $html .= "<tr>";
            $html .= "</tr>";

            $html .= "<tr>";
            $html .= "<th colspan=3 title=\"стоимость Всего товара\" style=\"vertical-align:middle;text-align:right;\"> всего &nbsp; ";
            // $html .= "{$labelWithNDS}&nbsp; &nbsp; &nbsp; ";
            $html .= ShowPriceInTheUnit( _formatPrice( $summ, 4, ".", "" ), $CID );

            if ( $showPriceUSD ) {
                $html .= " = ";
                $html .= invoiceUSDformat( $summ );
            }
            $html .= " </th>";
            $html .= "</tr>";

            $html .= "</tbody>";
        }
        if ( $showFooter ) {

            $html .= "<tfoot>";

            if ( $order["shipping_cost"] ) {
                $html .= "<tr>";
                $html .= "<th colspan=3 style=\"vertical-align:middle;text-align:right;line-height: 80%!important;\"> стоимость доставки: ";
                $html .= ShowPriceInTheUnit( _formatPrice( $order["shipping_cost"], 4, ".", "" ), $CID );

                if ( $showPriceUSD ) {
                    $html .= " = ";
                    $html .= invoiceUSDformat( $order["shipping_cost"], "$" );
                }
                $html .= " </th>";
                $html .= "</tr>";
            }

            if ( $order["order_discount"] ) {
                $SkidkaNacenka = ( $order["order_discount"] < 0 ) ? "наценка" : "скидка";
                $html .= "<tr>";
                $html .= "<th colspan=3 style=\"vertical-align:middle;text-align:right;line-height: 80%!important;\"> {$SkidkaNacenka}: ";
                $html .= $order["order_discount"] . "% = $" . round( (double)$order["order_discount"] * $order["order_amount"] ) / 100;
                $html .= " </th>";
                $html .= "</tr>";
            }
            if ( $order["order_amount"] ) {
                $html .= "<tr>";
                $html .= "<th colspan=3 style=\"vertical-align:middle;text-align:right;line-height: 80%!important;\"> ИТОГО &nbsp; ";
                $html .= "&nbsp; &nbsp; &nbsp; ";
                $html .= ShowPriceInTheUnit( _formatPrice( $order["order_amount"], 4, ".", "" ), $CID );

                if ( $showPriceUSD ) {
                    $html .= " = ";
                    $html .= invoiceUSDformat( $order["order_amount"], "$" );
                }
                $html .= " </th>";
                $html .= "</tr>";
            }
            $html .= "</tfoot>";

        }

        $html .= "</table>";
        $html .= "</div>";
    } else {
        $html .= "<p class=\"text-danger\" style=\"margin-top:2px;font-size:80%;line-height: 80%!important;\"> Нет товаров </p>";
    }

    return $html;
}

// ///STAMP
// function stampGetALLContracts() {

//     $sql = "SELECT contractID, contract_title FROM " . CONTRACTS_TABLE . " ORDER BY contractID";
//     //debug($sql);
//     $q = ( db_query( $sql ) );

//     $contracts = array();
//     while ( $row = db_fetch_row( $q ) ) {
//         $contracts[] = $row;
//     }
//     //debug($contracts);
//     return $contracts;
// }

// function stampGetContractByID( $contractID = 100 ) {

//     $res        = array();
//     $contract   = array();
//     $js         = array();
//     $contractID = (int)$contractID;

//     $sql      = "SELECT contractID, contract_title, dogovor_body, schet_body,warranty_body FROM " . CONTRACTS_TABLE . " WHERE `contractID`=$contractID LIMIT 1";
//     $q        = ( db_query( $sql ) );
//     $contract = db_fetch_row( $q );
//     $res      = $contract;
//     return $res;
// }

function ordForceInsertNewOrder(
    $ord_id = null,
    $orderData,
    $cartData,
    $invoiceData
) {
    if ( $ord_id > 0 ) {

        $orderData["orderID"]   = $newOrderID;
        $cartData["orderID"]    = $newOrderID;
        $invoiceData["orderID"] = $newOrderID;
    }
}

## выбираем старые заказы начиная с 1 января 2017 года
// `orderID`
// `customerID`
// `order_time`
// `customer_ip`
// `shipping_type`
// `payment_type`
// `customers_comment`
// `statusID`
// `shipping_cost`
// `order_discount`
// `order_amount`
// `currency_code`
// `currency_value`
// `customer_firstname`
// `customer_lastname`
// `customer_email`
// `shipping_firstname`
// `shipping_lastname`
// `shipping_country`
// `shipping_state`
// `shipping_city`
// `shipping_address`
// `billing_firstname`
// `billing_lastname`
// `billing_country`
// `billing_state`
// `billing_city`
// `billing_address`
// `cc_number`
// `cc_holdername`
// `cc_expires`
// `cc_cvv`
// `affiliateID`
// `shippingServiceInfo`
// `custlink`
// `currency_round`
// `paymethod`
// `admin_comment`
// `contractID`

function transfer_CID( $oldCID ) {
    switch ( $oldCID ) {
        case 1:      //Пересчет   =  1   0   0   =   4
            $newCID = 1; //1 Доллары США $   1   0   0   USD
            break;
        case 4:      // 4   Бел. рубли   РУБ    2.5 1   1   руб 2
            $newCID = 4; //4 Бел. рубли  руб.    2.5 1   10  BYN
            break;
        case 5:      //5 Безнал без НДС   без НДС    2.5 1   2   руб 2
            $newCID = 4; //4 Бел. рубли  руб.    2.5 1   10  BYN
            break;
        case 6: // неизв
            $newCID = 1;
            break;       //1 Доллары США $   1   0   0   USD
        case 7:      //  7   Безнал  с НДС    c НДС  3   1   3   BYN 2
            $newCID = 1; //Безнал  (c НДС)   c НДС  3   1   20  BNN 2
            break;

        default:
            $newCID = 1;
            break;
    }
    return (int)$newCID;
}

function transfer_GetCustomersData(
    $start_date = "2018-01-01 00:00:00",
    $offset = 0,
    $PARSE_IBAN = true,
    $PARSE_OLD = true
) {
# 1 переносим данные таблицы ant_customers , в UTF_customers, добавив старое ID как customer_aka
    $result            = array();
    $customersData_sql = <<<SQL
SELECT *
FROM `ant_customers`
WHERE `actions` =''
ORDER BY `customerID` ASC LIMIT {$offset},5000
;
SQL;
    $customersData = db_multiquery1251( $customersData_sql, $DB_HOST = DB_HOST, $DB_USER = DB_USER, $DB_PASS = DB_PASS, $DB_NAME = "nixby_nixbydb" );

    if ( is_array( $customersData ) ) {
        $multyquery_string = "";

        foreach ( $customersData as $key => $data ) {
            $res[]                     = $data;
            $res[$key]["customer_aka"] = (int)$data["customerID"];
            $res[$key]["CID"]          = transfer_CID( $data["CID"] );

            $orders_count = db_multiquery1251( "SELECT count(`customerID`) AS counter FROM `ant_orders` WHERE `customerID` = '{$data["customerID"]}'", $DB_HOST = DB_HOST, $DB_USER = DB_USER, $DB_PASS = DB_PASS, $DB_NAME = "nixby_nixbydb" );

            if ( $orders_count[0]["counter"] > 0 ) {
                $res[$key]["custgroupID"] = "3";
                $res[$key]["last_name"]   = $data["last_name"] . " [" . $orders_count[0]["counter"] . "]";
            } else {
                $res[$key]["custgroupID"] = "4";
            }

            unset( $res[$key]["customerID"] );

            $fieldslist = "";

            $fieldslist    = array2apostrophes( array_keys( $res[$key] ), "`" );
            $values_string = array2apostrophes( array_values( $res[$key] ), "'", "ToText" );
# ON DUPLICATE KEY
            $resres = $res;
            unset( $resres[$key]["reg_datetime"] );
            $resres[$key]["ActivationCode"] = "CLONE";
            // $resres[$key]["reg_datetime"];
            $update_string = array2binds( array_keys( $resres[$key] ), array_values( $resres[$key] ), "`", "'", "ToText" );
# ON DUPLICATE KEY

            $sql_insert_customer = <<<SQL
            INSERT INTO `UTF_customers` ({$fieldslist})
            vALUES ({$values_string})
            ON DUPLICATE KEY
            UPDATE
            {$update_string}
           ;
SQL;
###
            // ON DUPLICATE KEY
            // UPDATE
            // {$update_string}
            ###

            $multyquery_string .= $sql_insert_customer;
        }
        db_multiquery( $multyquery_string, $DB_HOST = DB_HOST, $DB_USER = DB_USER, $DB_PASS = DB_PASS, $DB_NAME = "nixby_UTF8" );

    } //if

    #2 переносим данные таблицы адресов , в UTF_customers, добавив старое ID как customer_aka

    $result1           = $res;
    $multyquery_string = "";
    $res               = array();
    $resres            = array();

    $addressesData_sql = <<<SQL
    SELECT *
    FROM `ant_customer_addresses`
    WHERE `customerID` !='1'
    ORDER BY `customerID` ASC LIMIT {$offset},5000
    ;
SQL;

    $addressesData = db_multiquery1251( $addressesData_sql, $DB_HOST = DB_HOST, $DB_USER = DB_USER, $DB_PASS = DB_PASS, $DB_NAME = "nixby_nixbydb" );

    if ( is_array( $addressesData ) ) {
        $multyquery_string = "";

        foreach ( $addressesData as $key => $data ) {
            $res[]                     = $data;
            $res[$key]["customer_aka"] = (int)$data["customerID"];

            unset( $res[$key]["addressID"] );
            unset( $res[$key]["customerID"] );

            $fieldslist = "";

            $fieldslist    = array2apostrophes( array_keys( $res[$key] ), "`" );
            $values_string = array2apostrophes( array_values( $res[$key] ), "'", "ToText" );

            $sql_insert_addresses = <<<SQL
                       INSERT INTO `UTF_customer_addresses` ({$fieldslist})
                       VALUES ({$values_string})
                       ;
SQL;

            $multyquery_string .= $sql_insert_addresses;
        }
        db_multiquery( $multyquery_string, $DB_HOST = DB_HOST, $DB_USER = DB_USER, $DB_PASS = DB_PASS, $DB_NAME = "nixby_UTF8" );

    }

    $result2           = $res;
    $multyquery_string = "";
    $res               = array();
    $resres            = array();

    #3 переносим данные таблицы  , в UTF_customers, добавив старое ID как customer_aka

    $result1           = $res;
    $multyquery_string = "";
    $res               = array();
    $resres            = array();

    $logData_sql = <<<SQL
    SELECT *
    FROM `ant_customer_log`
    WHERE `customerID` !='1'
    ORDER BY `customerID` ASC LIMIT {$offset},5000
    ;
SQL;

    $logData = db_multiquery1251( $logData_sql, $DB_HOST = DB_HOST, $DB_USER = DB_USER, $DB_PASS = DB_PASS, $DB_NAME = "nixby_nixbydb" );
    // debugfile( $logData );

    if ( is_array( $logData ) ) {
        $multyquery_string = "";

        foreach ( $logData as $key => $data ) {
            $res[]                     = $data;
            $res[$key]["customer_aka"] = (int)$data["customerID"];

            // unset( $res[$key]["addressID"] );
            unset( $res[$key]["customerID"] );

            $fieldslist = "";

            $fieldslist    = array2apostrophes( array_keys( $res[$key] ), "`" );
            $values_string = array2apostrophes( array_values( $res[$key] ), "'", "ToText" );

            $sql_insert_log = <<<SQL
                       INSERT INTO `UTF_customer_log` ({$fieldslist})
                       VaLUES ({$values_string})
                       ;
SQL;

            $multyquery_string .= $sql_insert_log;
        }
        db_multiquery( $multyquery_string, $DB_HOST = DB_HOST, $DB_USER = DB_USER, $DB_PASS = DB_PASS, $DB_NAME = "nixby_UTF8" );

    }

    $result3           = $res;
    $multyquery_string = "";
    $res               = array();
    $resres            = array();

    return $result;
}

function tranfer_LinkIt() {
    $link_sql = <<<SQL
    UPDATE `UTF_customer_addresses` ta
    INNER JOIN `UTF_customers` tc ON (ta.customer_aka=tc.customer_aka)
    SET ta.customerID=tc.customerID;


    UPDATE `UTF_customer_log` tlog
    INNER JOIN `UTF_customers` tc ON (tlog.customer_aka=tc.customer_aka)
    SET tlog.customerID=tc.customerID;

    UPDATE `UTF_customer_reg_fields_values` tregval
    INNER JOIN `UTF_customers` tc ON (tregval.customer_aka=tc.customer_aka)
    SET tregval.customerID=tc.customerID;


    UPDATE `UTF_customer_reg_fields_values_quickreg` tregval
    INNER JOIN `UTF_orders` tc ON (tregval.order_aka=tc.order_aka)
    SET tregval.orderID=tc.orderID;
SQL;

    db_multiquery( $link_sql );
}

# переносим заказы
function transfer_GetOrdersData(
    $start_date = "2018-01-01 00:00:00",
    $offset = 0,
    $PARSE_IBAN = true,
    $PARSE_OLD = true
) {

    $result      = array();
    $orderData   = array();
    $cartData    = array();
    $invoiceData = array();
    $companyData = array();
    $userData    = array();

    # прочитали
    $orders_sql = <<<SQL
SELECT `orderID` AS `order_aka`,
       `customerID` AS `customer_aka`,
       `order_time`,
       `customer_ip`,
       `customers_comment`,
       `statusID`,
       `shipping_cost`,
       `order_discount`,
       `order_amount`,
       `currency_code`,
       `currency_value`,
       `customer_firstname`,
       `customer_lastname`,
       `customer_email`,
       `shipping_firstname`,
       `shipping_lastname`,
       `currency_round`,
       `paymethod`,
       `admin_comment`,
       `contractID`
FROM `ant_orders`
WHERE `order_time` > '{$start_date}'
ORDER BY `order_time` DESC LIMIT {$offset},5;
SQL;
    // `order_time` < '{$start_date} 00:00:00'
    //      `orderID` >2300
    // AND `orderID` <2400
    $orderTable = db_multiquery1251( $orders_sql, $DB_HOST = DB_HOST, $DB_USER = DB_USER, $DB_PASS = DB_PASS, $DB_NAME = "nixby_nixbydb" );

    if ( is_array( $orderTable ) ) {

        foreach ( $orderTable as $key => $orderData ) {

# ЗАПИСЫВАЕМ ТАбЛИЦУ ORDERS

            $order_aka  = (int)$orderData["order_aka"];
            $order_time = $orderData["order_time"];

            $fieldslist        = "";
            $fieldslist        = array2apostrophes( array_keys( $orderData ), "`" );
            $values_string     = array2apostrophes( array_values( $orderData ), "'", "ToText" );
            $sql_insert_orders = <<<SQL
                       INSERT INTO `UTF_orders` ({$fieldslist})
                       VAlUES ({$values_string})
                       ;
SQL;

// break;

// db_query($sql_insert_orders);

            $ordered_carts_sql = <<<SQL
    SELECT *
    FROM `ant_ordered_carts`
    WHERE `orderID` = {$order_aka}
    ORDER BY `itemID` ASC;
SQL;

            $cartData = db_multiquery1251( $ordered_carts_sql, $DB_HOST = DB_HOST, $DB_USER = DB_USER, $DB_PASS = DB_PASS, $DB_NAME = "nixby_nixbydb" );

            $invoice_sql = <<<SQL
    SELECT *
    FROM `ant__module_payment_invoice_jur`
    WHERE `orderID` = {$order_aka};
SQL;
            $invoiceData = db_multiquery1251( $invoice_sql, $DB_HOST = DB_HOST, $DB_USER = DB_USER, $DB_PASS = DB_PASS, $DB_NAME = "nixby_nixbydb" );

            if ( isset( $invoiceData[0]["company_unp"] ) ) {
                $line = trim( $invoiceData[0]["company_unp"] );
                // $line                            = "12|3|Fred";
                list( $_unp, $_okpo ) = preg_split( '/ОКПО/D', $line );
                $UNP                = preg_replace( '/\D/', "", $_unp );
                $OKPO               = preg_replace( '/\D/', "", $_okpo );
                if ( strlen( $UNP ) != 9 ) {
                    $UNP = null;
                }
                $company_unp = $UNP;
            }

            if ( isset( $invoiceData[0]["company_bank"] ) ) {
                $line   = trim( $invoiceData[0]["company_bank"] );
                $params = array(
                    "orderID"     => $order_aka,
                    "order_time"  => $order_time,
                    "companyID"   => $data["conpanyID"],
                    "company_unp" => $company_unp,
                );
                $companyBankInformation = companParceBankInformation( $line, $params, true, false );
            } else {
                $companyBankInformation = array();
            }

            $result[] = array(
                "order_aka"              => $order_aka,
                "order_time"             => $order_time,
                "orderData"              => $orderData,
                "cartData"               => $cartData,
                "companyBankInformation" => $companyBankInformation,
                // "customerData"           => $customerData,
            );
        }

    }
}

function ordSelectpickerOptions( $WHERE_CLAUSE = '1' ) {
    $q    = db_query( "SELECT orderID, customerID, statusID, companyID, order_time,order_amount FROM " . ORDERS_TABLE . " WHERE $WHERE_CLAUSE;" );
    $data = array();
    while ( $row = db_fetch_assoc( $q ) ) {
        $row["status_name"]  = ostGetOrderStatusName( $row["statusID"] );
        $row["company_name"] = dbGetFieldData( COMPANIES_TABLE, "company_name", "`companyID`='" . $row["companyID"] . "'", $order_clause = "", $formatt = 1 );
        $data[]              = $row;
    }
    return $data;
}

function generateInvoiceOrderContentPreview(
    $invoiceID
) {
    $AdvancedOrderContent = array();
    $Invoice              = array();
    $Invoice              = invoiceGet( $invoiceID );

    $Order = array();
    $Order = _getOrderById( $Invoice["orderID"] );

    $ModuleConfig  = array();
    $ModuleConfig  = modGetModuleConfig( $Invoice["module_id"] );
    $PaymentModule = modGetModuleObj( $Invoice["module_id"], PAYMENT_MODULE );
    if ( !$PaymentModule ) {
        $PaymentModule = modGetModuleObj( DEFAULT_PAYMENT_MODULE_ID, PAYMENT_MODULE );
    }

    $PM_NDS_IS_INCLUDED_IN_PRICE = $PaymentModule->getModuleSettingValue( PM_NDS_IS_INCLUDED_IN_PRICE ); //module
    $PM_NDS_RATE                 = $PaymentModule->getModuleSettingValue( PM_NDS_RATE );                 //module
    $PM_MULTIPLIER               = $PaymentModule->getModuleSettingValue( PM_MULTIPLIER );
    $PM_INVOICE_STRING           = $PaymentModule->getModuleSettingValue( PM_INVOICE_STRING );
    $PM_CONTRACT_STRING          = $PaymentModule->getModuleSettingValue( PM_CONTRACT_STRING ); //module
    $PM_CONTRACT_TYPE            = $PaymentModule->getModuleSettingValue( PM_CONTRACT_TYPE );   //module

    $currencyID = $PaymentModule->getModuleSettingValue( PM_CURRENCY ); //(int)$Invoice["CID"];//module
    $Currency   = currGetCurrencyByID( $currencyID );

    $orderID             = $Invoice["orderID"];
    $Currency_rate       = $Invoice["currency_rate"];
    $shipping_cost       = $Order["shipping_cost"];
    $order_discount      = $Order["order_discount"];
    $nds_included        = $PM_NDS_IS_INCLUDED_IN_PRICE;
    $VAT_rate            = $PM_NDS_RATE;
    $Multiplier          = $PM_MULTIPLIER;
    $dec                 = $Currency["roundval"];
    $currency_iso_3      = $Currency["currency_iso_3"];
    $invoice_subdiscount = $Invoice["invoice_subdiscount"];

    $AdvancedOrderContent = ordAdvancedOrderContent(
        $orderID,
        $Currency_rate,               //module
        $shipping_cost,               // order
        $order_discount,              //order
        $PM_NDS_IS_INCLUDED_IN_PRICE, //module
        $PM_NDS_RATE,                 //module
        $PM_MULTIPLIER,               //module
        $dec,                         //currency
        $currency_iso_3,
        $invoice_subdiscount
    );

    return $AdvancedOrderContent;
}

function orderSelectLastOrderID() {
    $sql    = "SELECT `orderID` FROM `" . ORDERS_TABLE . "` ORDER BY `orderID` DESC LIMIT 1";
    $q      = db_query( $sql );
    $res    = db_fetch_assoc( $q );
    $result = $res["orderID"];
    return (int)$result;
}

function orderGetLastCompanyIDFromOrders( $customerID ) {
    ## Находим последнюю компанию в заказаз пользователя

    $sql = "SELECT `companyID` FROM " . ORDERS_TABLE . " WHERE `customerID`='{$customerID}' AND `companyID`>=1 ORDER BY `orderID` DESC LIMIT 1";
    $q   = db_query( $sql );
    $res = array();
    while ( $row = db_fetch_assoc( $q ) ) {
        $last_order_companyID = $row["companyID"];
    }
    $result = (int)$last_order_companyID;
    return $result;
}

function orderGetCompaniesForCustomer( $customerID ) {
    // $sql = "SELECT DISTINCT `companyID` FROM " . ORDERS_TABLE . " WHERE (`customerID`={$customerID}) AND (`companyID`>0) ORDER BY `orderID` DESC";
    $sql = "SELECT DISTINCT `companyID` FROM " . ORDERS_TABLE . " WHERE (`customerID`={$customerID}) AND (`companyID`>0)";

    $q   = db_query( $sql );
    $res = array();
    while ( $row = db_fetch_assoc( $q ) ) {
        $res[] = $row;
    }

    $result = $res;
    return $result;
}

function orderGetContractByOrderID( $orderID ) {
    $orderID = (int)$orderID;

    $sql                         = "SELECT contractID FROM " . ORDERS_TABLE . " WHERE `orderID`=$orderID LIMIT 1";
    $q                           = ( db_query( $sql ) );
    $row                         = db_fetch_row( $q );
    $contractID                  = $row["contractID"];
    ( !empty( $contractID ) ) ? $res = (int)$contractID : $res = CONF_STAMP_CONTRACT_ID;
    return $res;
}

function orderSetContractByOrderID(
    $orderID,
    $NewContractID = CONF_STAMP_CONTRACT_ID
) {
    $orderID       = (int)$orderID;
    $NewContractID = (int)$NewContractID;
    $sql           = "UPDATE " . ORDERS_TABLE . " SET `contractID`='$NewContractID' WHERE `orderID`='$orderID'";
    $q             = ( db_query( $sql ) );
}

################################################################################################
################################################################################################
################################################################################################
################################################################################################
################################################################################################

function ordGetCountOfOrders( $callBackParam ) {
    $res          = [];
    $where_clause = "";

    if ( isset( $callBackParam["orderStatuses"] ) ) {
        foreach ( $callBackParam["orderStatuses"] as $statusID ) {
            if ( $where_clause == "" ) {
                $where_clause .= " statusID=" . (int)$statusID;
            } else {
                $where_clause .= " OR statusID=" . (int)$statusID;
            }

        }

        if ( isset( $callBackParam["customerID"] ) ) {
            if ( $where_clause != "" ) {
                $where_clause = " customerID=" . (int)$callBackParam["customerID"] .
                    " AND ( " . $where_clause . " ) ";
            } else {
                $where_clause = " customerID=" . (int)$callBackParam["customerID"];
            }

        }

        if ( $where_clause != "" ) {
            $where_clause = " WHERE " . $where_clause;
        } else {
            $where_clause = " WHERE statusID = -1 ";
        }

    } else {
        if ( isset( $callBackParam["customerID"] ) ) {
            $where_clause .= " customerID = " . (int)$callBackParam["customerID"];
        }

        if ( isset( $callBackParam["orderID"] ) ) {
            if ( $where_clause != "" ) {
                $where_clause .= " AND orderID=" . (int)$callBackParam["orderID"];
            } else {
                $where_clause .= " orderID=" . (int)$callBackParam["orderID"];
            }

        }

        if ( $where_clause != "" ) {
            $where_clause = " WHERE " . $where_clause;
        } else {
            $where_clause = " WHERE statusID = -1 ";
        }

    }

    $sql = "SELECT COUNT(orderID) as ordersCount FROM " . ORDERS_TABLE . " " . $where_clause . " AND statusID !=0 ";
    $q   = db_query( $sql );

    $res = db_fetch_assoc( $q );
    return $res;
}
################################################################################################
################################################################################################
################################################################################################

function ordGetOrders(
    $callBackParam,
    &$count_row,
    $navigatorParams = null
) {
    global $selected_currency_details;

    if ( $navigatorParams != null ) {
        $offset         = $navigatorParams["offset"];
        $CountRowOnPage = $navigatorParams["CountRowOnPage"];
    } else {
        $offset         = 0;
        $CountRowOnPage = 0;
    }

    $where_clause = "";

    if ( isset( $callBackParam["orderStatuses"] ) ) {
        foreach ( $callBackParam["orderStatuses"] as $statusID ) {
            if ( $where_clause == "" ) {
                $where_clause .= " statusID=" . (int)$statusID;
            } else {
                $where_clause .= " OR statusID=" . (int)$statusID;
            }

        }

        if ( isset( $callBackParam["customerID"] ) ) {
            if ( $where_clause != "" ) {
                $where_clause = " customerID=" . (int)$callBackParam["customerID"] .
                    " AND ( " . $where_clause . " ) ";
            } else {
                $where_clause = " customerID=" . (int)$callBackParam["customerID"];
            }

        }

        if ( $where_clause != "" ) {
            $where_clause = " WHERE " . $where_clause;
        } else {
            $where_clause = " WHERE statusID = -1 ";
        }

    } else {
        if ( isset( $callBackParam["customerID"] ) ) {
            $where_clause .= " customerID = " . (int)$callBackParam["customerID"];
        }

        if ( isset( $callBackParam["orderID"] ) ) {
            if ( $where_clause != "" ) {
                $where_clause .= " AND orderID=" . (int)$callBackParam["orderID"];
            } else {
                $where_clause .= " orderID=" . (int)$callBackParam["orderID"];
            }

        }

        if ( $where_clause != "" ) {
            $where_clause = " WHERE " . $where_clause;
        } else {
            $where_clause = " WHERE statusID = -1 ";
        }

    }

    $order_by_clause = "";
    if ( isset( $callBackParam["sort"] ) ) {
        $order_by_clause .= " order by " . xEscSQL( $callBackParam["sort"] ) . " ";
        if ( isset( $callBackParam["direction"] ) ) {
            if ( $callBackParam["direction"] == "ASC" ) {
                $order_by_clause .= " ASC ";
            } else {
                $order_by_clause .= " DESC ";
            }

        } else {
            $order_by_clause .= " ASC ";
        }

    } else {
        $order_by_clause = " ORDER BY orderID DESC ";
    }

    $sql = "SELECT orderID, customerID, order_time, customer_ip, shipping_type, " .
        " payment_type, customers_comment, statusID, shipping_cost, order_amount, " .
        " order_discount, currency_code, currency_value, customer_email, " .
        " shipping_firstname, shipping_lastname, " .
        " shipping_country, shipping_state, shipping_city, " .
        " shipping_address, billing_firstname, billing_lastname, " .
        " billing_country, billing_state, billing_city, " .
        " billing_address, cc_number, cc_holdername, cc_expires, cc_cvv, shippingServiceInfo, currency_round, shippmethod, paymethod, companyID, order_aka, customer_aka " .
        " FROM " . ORDERS_TABLE . " " . $where_clause . " AND statusID !=0 " . $order_by_clause;
    $q = db_query( $sql );

    $res       = array();
    $i         = 0;
    $total_sum = 0;
    while ( $row = db_fetch_assoc( $q ) ) {
        if (  ( $i >= $offset && $i < $offset + $CountRowOnPage ) ||
            $navigatorParams == null ) {
            $row["OrderStatus"] = ostGetOrderStatusName( $row["statusID"] );
            $total_sum += $row["order_amount"];
            $row["order_amount"] = _formatPrice( roundf( $row["currency_value"] * $row["order_amount"] ), $row["currency_round"] ) . " " . $row["currency_code"];

            $q_orderContent = db_query( "select name, Price, Quantity, tax, load_counter, itemID, itemUnit, itemPriority FROM " .
                ORDERED_CARTS_TABLE . " where enabled=1 AND orderID=" . (int)$row["orderID"] . " ORDER BY itemPriority DESC, itemID ASC;" );

            $content = array();
            while ( $orderContentItem = db_fetch_row( $q_orderContent ) ) {
                $productID = GetProductIdByItemId( $orderContentItem["itemID"] );
                $product   = GetProduct( $productID );
                if ( $product["eproduct_filename"] != null &&
                    strlen( $product["eproduct_filename"] ) > 0 ) {
                    if ( file_exists( "core/files/" . $product["eproduct_filename"] ) ) {
                        $orderContentItem["eproduct_filename"] = $product["eproduct_filename"];
                        $orderContentItem["file_size"]         = (string)round( filesize( "core/files/" . $product["eproduct_filename"] ) / 1048576, 3 );

                        if ( isset( $callBackParam["customerID"] ) ) {
                            $custID = $callBackParam["customerID"];
                        } else {
                            $custID = -1;
                        }

                        $orderContentItem["getFileParam"] =
                            "orderID=" . $row["orderID"] . "&" .
                            "productID=" . $productID . "&" .
                            "customerID=" . $custID;

                        //additional security for non authorized customers
                        if ( $custID == -1 ) {
                            $orderContentItem["getFileParam"] .= "&order_time=" . base64_encode( $row["order_time"] );
                        }

                        $orderContentItem["getFileParam"] = cryptFileParamCrypt(
                            $orderContentItem["getFileParam"], null );
                        $orderContentItem["load_counter_remainder"] =
                            $product["eproduct_download_times"] - $orderContentItem["load_counter"];

                        $currentDate = dtGetParsedDateTime( get_current_time() );
                        $betweenDay  = _getDayBetweenDate(
                            dtGetParsedDateTime( $row["order_time"] ),
                            $currentDate );

                        $orderContentItem["day_count_remainder"] =
                            $product["eproduct_available_days"] - $betweenDay;
                        //if ( $orderContentItem["day_count_remainder"] < 0 )
                        //                $orderContentItem["day_count_remainder"] = 0;

                    }
                }

                $content[] = $orderContentItem;
            }

            $row["content"] = $content;

            $row["order_time"] = format_datetime( $row["order_time"] );

            $row["order_html"]  = htmlOrderContentPreview( $row["orderID"], 1, 0, 1, 0 );
            $row["order_html2"] = htmlOrderContentPreview( $row["orderID"], 1, 0, 0, 1 );
            // $res[]             = $row;

            $order_time_mysql = format_datetime( $row["order_time"] );

## invoice info
            $row["invoice"] = getInvoiceDataForThisOrder( $row["orderID"], $order_time_mysql, $row["customer_email"] );
## invoice info

            $res[] = $row;
        }

        $i++;
    }
    $count_row = $i;

    if ( isset( $callBackParam["customerID"] ) ) {
        if ( count( $res ) > 0 ) {
            $q = db_query( "select CID FROM " . CUSTOMERS_TABLE .
                " where customerID=" . (int)$callBackParam["customerID"] );
            $row = db_fetch_row( $q );

            if ( $row["CID"] != null && $row["CID"] != "" ) {
                $q = db_query( "select currency_value, currency_iso_3, roundval FROM " .
                    CURRENCY_TYPES_TABLE . " where CID=" . (int)$row["CID"] );
                $row                 = db_fetch_row( $q );
                $res[0]["total_sum"] = _formatPrice( roundf( $row["currency_value"] * $total_sum ), $row["roundval"] ) . " " . $row["currency_iso_3"];
            } else {
                $res[0]["total_sum"] = _formatPrice( roundf( $selected_currency_details["currency_value"] * $total_sum ), $row["roundval"] ) . " " . $selected_currency_details["currency_iso_3"];
            }
        }
    }

    return $res;
}

function ordGetDistributionByStatuses( $log ) {
    $q = db_query( "SELECT statusID, status_name, sort_order FROM " .
        ORDER_STATUES_TABLE . " ORDER BY sort_order, status_name" );
    $data = array();
    while ( $row = db_fetch_row( $q ) ) {
        $q1 = db_query( "SELECT count(*) FROM " . ORDERS_TABLE .
            " WHERE statusID=" . (int)$row["statusID"] . " AND " .
            " customerID=" . (int)regGetIdByLogin( $log ) );
        $row1 = db_fetch_row( $q1 );

        if ( $row["statusID"] == ostGetCanceledStatusId() ) {
            $row["status_name"] = STRING_CANCELED_ORDER_STATUS;
        }

        $item = array( "status_name" => $row["status_name"],
            "count"                     => $row1[0] );
        $data[] = $item;
    }
    return $data;
}

# Добавление ТРАНЗИТНЫХ ТОВАРОВ
function _moveSessionCartContentToOrderedCart( $orderID ) {
// debug($_SESSION["gids"]);

    $i   = 0;
    $sql = "DELETE FROM " . ORDERED_CARTS_TABLE . " WHERE enabled=1 AND orderID=" . (int)$orderID;
    db_query( $sql );

    if ( isset( $_SESSION["gids"] ) ) {
        foreach ( $_SESSION["gids"] as $productID ) {
            if ( $productID == 0 ) {
                $i++;
                continue;
            }

            $q = db_query( "select count(*) FROM " . PRODUCTS_TABLE .
                " where productID=" . (int)$productID );
            $row = db_fetch_row( $q );
            if ( $row[0] == 0 ) {
                $i++;
                continue;
            }

            // create new item
            db_query( "INSERT INTO " . SHOPPING_CART_ITEMS_TABLE .
                "(productID) VALUES('" . (int)$productID . "')" );
            $itemID = db_insert_id();

            foreach ( $_SESSION["configurations"][$i] as $vars ) {
                db_query( "INSERT INTO " .
                    SHOPPING_CART_ITEMS_CONTENT_TABLE . "(itemID, variantID) " .
                    "VALUES( '" . (int)$itemID . "', '" . (int)$vars . "')" );
            }

            # Добавление ТРАНЗИТНЫХ ТОВАРОВ
            // $q_product = db_query( "SELECT name, product_code FROM " . PRODUCTS_TABLE .
            // " WHERE productID=" . (int)$productID );
            // $product       = db_fetch_row( $q_product );

            $quantity  = $_SESSION["counts"][$i];
            $q_product = db_query( "SELECT name, product_code,in_stock FROM " . PRODUCTS_TABLE .
                " WHERE productID=" . (int)$productID );
            $product = db_fetch_row( $q_product );

            $new_in_stock = (int)$product["in_stock"];
            $in_stock     = (int)$product["in_stock"];
            $is_fake      = ( $in_stock <= 0 ) ? 1 : 0;
            #END Добавление ТРАНЗИТНЫХ ТОВАРОВ;

            $variants = array();
            foreach ( $_SESSION["configurations"][$i] as $vars ) {
                $variants[] = $vars;
            }

            $options = GetStrOptions( $variants );
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

            $price           = GetPriceProductWithOption( $variants, $productID );
            $shippingAddress = array(
                "countryID" => $_SESSION["receiver_countryID"],
                "zoneID"    => $_SESSION["receiver_zoneID"] );
            $billingAddress = array(
                "countryID" => $_SESSION["billing_countryID"],
                "zoneID"    => $_SESSION["billing_zoneID"] );

            $tax = taxCalculateTax2( $productID, $shippingAddress, $billingAddress );

            $sql_insert =
            "INSERT INTO " . ORDERED_CARTS_TABLE . " ( `itemID`, `orderID`, `name`, `Price`, `Quantity`, `tax`, `itemUnit`, `enabled` ) " .
            "VALUES( " .
            (int)$itemID . ", " .
            (int)$orderID . ", '" .
            xEscSQL( $productComplexName ) . "', '" .
            xEscSQL( $price ) . "', " .
            (int)$quantity . ", " .
            xEscSQL( $tax ) . " ," .
                "'шт.', " .
                "'1'" . " ); ";

            db_query( $sql_insert );

            $i++;
        }
    }

    unset( $_SESSION["gids"] );
    unset( $_SESSION["counts"] );
    unset( $_SESSION["configurations"] );
    session_unregister( "gids" ); //calling session_unregister() is required since unset() may not work on some systems
    session_unregister( "counts" );
    session_unregister( "configurations" );
}

function _quickOrderUnsetSession() {
    unset( $_SESSION["first_name"] );
    unset( $_SESSION["last_name"] );
    unset( $_SESSION["email"] );

    unset( $_SESSION["billing_first_name"] );
    unset( $_SESSION["billing_last_name"] );
    unset( $_SESSION["billing_state"] );
    unset( $_SESSION["billing_city"] );
    unset( $_SESSION["billing_address"] );
    unset( $_SESSION["billing_countryID"] );
    unset( $_SESSION["billing_zoneID"] );

    unset( $_SESSION["receiver_first_name"] );
    unset( $_SESSION["receiver_last_name"] );
    unset( $_SESSION["receiver_state"] );
    unset( $_SESSION["receiver_city"] );
    unset( $_SESSION["receiver_address"] );
    unset( $_SESSION["receiver_countryID"] );
    unset( $_SESSION["receiver_zoneID"] );
}

function _getOrderById( $orderID ) {
    $sql = "SELECT " .
    "        orderID, " .
    "        customerID, " .
    "        order_time, " .
    "        customer_ip, " .
    "        shipping_type, " .
    "        payment_type, " .
    "        customers_comment, " .
    "        statusID, " .
    "        shipping_cost, " .
    "        order_discount, " .
    "        order_amount, " .
    "        currency_code, " .
    "        currency_value, " .
    "        customer_firstname, " .
    "        customer_lastname, " .
    "        customer_email, " .
    "        shipping_firstname, " .
    "        shipping_lastname, " .
    "        shipping_country, " .
    "        shipping_state, " .
    "        shipping_city, " .
    "        shipping_address, " .
    "        billing_firstname, " .
    "        billing_lastname, " .
    "        billing_country, " .
    "        billing_state, " .
    "        billing_city, " .
    "        billing_address, " .
    "        cc_number, " .
    "        cc_holdername, " .
    "        cc_expires, " .
    "        cc_cvv, " .
    "        shippingServiceInfo, " .
    "        currency_round, " .
    "        shippmethod, " .
    "        paymethod, " .
    "        companyID, " .
    "        contractID, " .
    "        order_aka, " .
    "        customer_aka, " .
    "        admin_comment " .
    " FROM " . ORDERS_TABLE . " WHERE orderID=" . (int)$orderID;
    $q = db_query( $sql );
    return db_fetch_row( $q );
}

function _sendOrderNotifycationToCustomer(
    $orderID,
    &$smarty_mail,
    $email,
    $login,
    $payment_email_comments_text,
    $shipping_email_comments_text,
    $tax,
    $order_active_link
) {
    $order = _getOrderById( $orderID );
    $smarty_mail->assign( "customer_firstname", $order["customer_firstname"] );
    $smarty_mail->assign( "orderID", $order["orderID"] );
    $smarty_mail->assign( "discount", roundf( $order["order_discount"] ) );
    $smarty_mail->assign( "shipping_type", $order["shipping_type"] );
    $smarty_mail->assign( "shipping_firstname", $order["shipping_firstname"] );
    $smarty_mail->assign( "shipping_lastname", $order["shipping_lastname"] );
    $smarty_mail->assign( "shipping_country", $order["shipping_country"] );
    $smarty_mail->assign( "shipping_state", $order["shipping_state"] );
    $smarty_mail->assign( "shipping_city", $order["shipping_city"] );
    $smarty_mail->assign( "shipping_address", $order["shipping_address"] );
    $smarty_mail->assign( "shipping_cost", _formatPrice( roundf( $order["currency_value"] * $order["shipping_cost"] ), $order["currency_round"] ) . " " . $order["currency_code"] );
    $smarty_mail->assign( "order_active_link", $order_active_link );
    $smarty_mail->assign( "payment_type", $order["payment_type"] );
    $smarty_mail->assign( "billing_firstname", $order["billing_firstname"] );
    $smarty_mail->assign( "billing_lastname", $order["billing_lastname"] );
    $smarty_mail->assign( "billing_country", $order["billing_country"] );
    $smarty_mail->assign( "billing_state", $order["billing_state"] );
    $smarty_mail->assign( "billing_city", $order["billing_city"] );
    $smarty_mail->assign( "billing_address", $order["billing_address"] );
    $smarty_mail->assign( "order_amount", _formatPrice( roundf( $order["currency_value"] * $order["order_amount"] ), $order["currency_round"] ) . " " . $order["currency_code"] );
    $smarty_mail->assign( "payment_comments", $payment_email_comments_text );
    $smarty_mail->assign( "shipping_comments", $shipping_email_comments_text );
    $smarty_mail->assign( "order_total_tax", _formatPrice( roundf( $order["currency_value"] * $tax ), $order["currency_round"] ) . " " . $order["currency_code"] );
    $smarty_mail->assign( "shippingServiceInfo", $order["shippingServiceInfo"] );

    // clear cost ( without shipping, discount, tax )
    $q1                = db_query( "select Price, Quantity FROM " . ORDERED_CARTS_TABLE . " where enabled=1 AND orderID=" . (int)$orderID );
    $clear_total_price = 0;
    while ( $row = db_fetch_row( $q1 ) ) {
        $clear_total_price += $row["Price"] * $row["Quantity"];
    }

    $order_discount_ToShow = _formatPrice( roundf( $order["currency_value"] * $clear_total_price * (  ( 100 - $order["order_discount"] ) / 100 ) ), $order["currency_round"] ) . " " . $order["currency_code"];
    $smarty_mail->assign( "order_discount_ToShow", $order_discount_ToShow );

    //additional reg fields
    $addregfields = GetRegFieldsValuesByOrderID( $orderID );
    $smarty_mail->assign( "customer_add_fields", $addregfields );

    $content = ordGetOrderContent( $orderID );
    for ( $i = 0; $i < count( $content ); $i++ ) {
        $productID = GetProductIdByItemId( $content[$i]["itemID"] );
        if ( $productID == null || trim( $productID ) == "" ) {
            continue;
        }

        $q = db_query( "select  name, product_code, eproduct_filename, " .
            " eproduct_available_days, eproduct_download_times FROM " . PRODUCTS_TABLE .
            " where productID=" . (int)$productID );
        $product                     = db_fetch_row( $q );
        $content[$i]["product_code"] = $product["product_code"];

        $variants = GetConfigurationByItemId( $content[$i]["itemID"] );
        $options  = GetStrOptions( $variants );
        if ( $options != "" ) {
            $content[$i]["name"] = $product["name"] . "(" . $options . ")";
        } else {
            $content[$i]["name"] = $product["name"];
        }

        if ( strlen( $product["eproduct_filename"] ) > 0 && file_exists( "core/files/" . $product["eproduct_filename"] ) ) {
            if ( $login != null ) {
                $customerID = regGetIdByLogin( $login );
            } else {
                $customerID = -1;
            }

            $content[$i]["eproduct_filename"]       = $product["eproduct_filename"];
            $content[$i]["eproduct_available_days"] = $product["eproduct_available_days"];
            $content[$i]["eproduct_download_times"] = $product["eproduct_download_times"];
            $content[$i]["file_size"]               = (string)round( filesize( "core/files/" . $product["eproduct_filename"] ) / 1048576, 3 );

            $content[$i]["getFileParam"] =
                "orderID=" . $order["orderID"] . "&" .
                "productID=" . $productID . "&" .
                "customerID=" . $customerID;
            //additional security for non authorized customers
            if ( $customerID == -1 ) {
                $content[$i]["getFileParam"] .= "&order_time=" . base64_encode( $order["order_time"] );
            }

            $content[$i]["getFileParam"] =
                cryptFileParamCrypt( $content[$i]["getFileParam"], null );
        }
    }

    $smarty_mail->assign( "content", $content );
    $html = $smarty_mail->fetch( "customer_order_notification.tpl.html" );

    if ( CONF_ACTIVATE_ORDER ) {

        $html_active = $smarty_mail->fetch( "customer_order_activate.tpl.html" );
        xMailTxtHTMLDATA( $order["customer_email"], STRING_ORDER_ACTIVATE . " #" . $orderID . " - " . CONF_SHOP_NAME, $html_active );

    } else {

        if ( CONF_EMAIL_ORDER_SEND ) {
            xMailTxtHTMLDATA( $email, STRING_ORDER . " #" . $orderID . " - " . CONF_SHOP_NAME, $html );
        }

    }

}

function activate_order(
    $actlink,
    &$smarty_mail
) {
    $q = db_query( "select orderID, statusID FROM " . ORDERS_TABLE . " WHERE custlink='" . xEscSQL( $actlink ) . "' " );
    if ( $res = db_fetch_row( $q ) ) {
        if ( $res["statusID"] == 0 ) {
            $order = _getOrderById( $res["orderID"] );
            ostSetOrderStatusToOrder( $res["orderID"], ostGetNewOrderStatus(), '', '' );
            $smarty_mail->assign( "orderID", $res["orderID"] );
            $smarty_mail->assign( "polidesk", ADMIN_SEND_INACT_DESK2 );
            $html = $smarty_mail->fetch( "active_deactive_order.tpl.html" );
            xMailTxtHTMLDATA( CONF_ORDERS_EMAIL, ADMIN_SEND_ACT_ORDER . " #" . $res["orderID"] . " - " . CONF_SHOP_NAME, $html );
            xMailTxtHTMLDATA( $order["customer_email"], STRING_ORDER . " #" . $res["orderID"] . " - " . ADMIN_SEND_INACT_TITLE . " - " . CONF_SHOP_NAME, $html );
        }
        $succes = 1;
    } else {
        $succes = 0;
    }

    return $succes;
}

function deactivate_order(
    $actlink,
    &$smarty_mail
) {

    # BEGIN не даем отменить уже оплаченный заказ
    #$q = db_query("select orderID FROM ".ORDERS_TABLE." WHERE custlink='".xEscSQL($actlink)."' ");
    #if($pql = db_fetch_row($q)){
    $q = db_query( "select orderID,statusID FROM " . ORDERS_TABLE . " WHERE custlink='" . xEscSQL( $actlink ) . "' " );
    if ( $pql = db_fetch_row( $q ) && $pql["statusID"] != ostGetCompletedOrderStatus() ) {
        # END не даем отменить уже оплаченный заказ
        $order = _getOrderById( $pql["orderID"] );
        ostSetOrderStatusToOrder( $pql["orderID"], ostGetCanceledStatusId(), '', '' );
        $smarty_mail->assign( "orderID", $pql["orderID"] );
        $smarty_mail->assign( "polidesk", ADMIN_SEND_INACT_DESK1 );
        $html = $smarty_mail->fetch( "active_deactive_order.tpl.html" );
        xMailTxtHTMLDATA( CONF_ORDERS_EMAIL, ADMIN_SEND_DEACT_ORDER . " #" . $pql["orderID"] . " - " . CONF_SHOP_NAME, $html );
        xMailTxtHTMLDATA( $order["customer_email"], STRING_ORDER . " #" . $pql["orderID"] . " - " . ADMIN_SEND_INACT_TITLE . " - " . CONF_SHOP_NAME, $html );
        $succes = 1;
    } else {
        $succes = 0;
    }

    return $succes;
}

function _sendOrderNotifycationToAdmin(
    $orderID,
    &$smarty_mail,
    $tax
) {
    $order = _getOrderById( $orderID );
    $smarty_mail->assign( "order_time", format_datetime( $order["order_time"] ) );
    $smarty_mail->assign( "customer_firstname", $order["customer_firstname"] );
    $smarty_mail->assign( "customer_lastname", $order["customer_lastname"] );
    $smarty_mail->assign( "customer_email", $order["customer_email"] );
    $smarty_mail->assign( "customer_ip", $order["customer_ip"] );
    $smarty_mail->assign( "order_time", format_datetime( $order["order_time"] ) );
    $smarty_mail->assign( "customer_comments", $order["customers_comment"] );
    $smarty_mail->assign( "discount", $order["order_discount"] );
    $smarty_mail->assign( "shipping_type", $order["shipping_type"] );
    $smarty_mail->assign( "shipping_cost", _formatPrice( roundf( $order["currency_value"] * $order["shipping_cost"] ), $order["currency_round"] ) . " " . $order["currency_code"] );
    $smarty_mail->assign( "payment_type", $order["payment_type"] );
    $smarty_mail->assign( "shipping_firstname", $order["shipping_firstname"] );
    $smarty_mail->assign( "shipping_lastname", $order["shipping_lastname"] );
    $smarty_mail->assign( "shipping_country", $order["shipping_country"] );
    $smarty_mail->assign( "shipping_state", $order["shipping_state"] );
    $smarty_mail->assign( "shipping_city", $order["shipping_city"] );
    $smarty_mail->assign( "shipping_address", rtrim( $order["shipping_address"] ) );
    $smarty_mail->assign( "billing_firstname", $order["billing_firstname"] );
    $smarty_mail->assign( "billing_lastname", $order["billing_lastname"] );
    $smarty_mail->assign( "billing_country", $order["billing_country"] );
    $smarty_mail->assign( "billing_state", $order["billing_state"] );
    $smarty_mail->assign( "billing_city", $order["billing_city"] );
    $smarty_mail->assign( "billing_address", rtrim( $order["billing_address"] ) );
    $smarty_mail->assign( "order_amount", _formatPrice( roundf( $order["currency_value"] * $order["order_amount"] ), $order["currency_round"] ) . " " . $order["currency_code"] );
    $smarty_mail->assign( "orderID", $order["orderID"] );
    $smarty_mail->assign( "total_tax", _formatPrice( roundf( $order["currency_value"] * $tax ), $order["currency_round"] ) . " " . $order["currency_code"] );
    $smarty_mail->assign( "shippingServiceInfo", $order["shippingServiceInfo"] );
    $smarty_mail->assign( "tax", $tax );

    // clear cost ( without shipping, discount, tax )
    $q1                = db_query( "select Price, Quantity FROM " . ORDERED_CARTS_TABLE . " where enabled=1 AND orderID=" . (int)$orderID );
    $clear_total_price = 0;
    while ( $row = db_fetch_row( $q1 ) ) {
        $clear_total_price += $row["Price"] * $row["Quantity"];
    }

    $order_discount_ToShow = _formatPrice( roundf( $order["currency_value"] * $clear_total_price * (  ( 100 - $order["order_discount"] ) / 100 ) ), $order["currency_round"] ) . " " . $order["currency_code"];
    $smarty_mail->assign( "order_discount_ToShow", $order_discount_ToShow );

    //additional reg fields
    $addregfields = GetRegFieldsValuesByOrderID( $orderID );
    $smarty_mail->assign( "customer_add_fields", $addregfields );

    //fetch order content FROM the database
    $content = ordGetOrderContent( $orderID );
    for ( $i = 0; $i < count( $content ); $i++ ) {
        $productID = GetProductIdByItemId( $content[$i]["itemID"] );
        if ( $productID == null || trim( $productID ) == "" ) {
            continue;
        }

        $q = db_query( "select name, product_code, default_picture FROM " . PRODUCTS_TABLE .
            " where productID=" . (int)$productID );
        $product                     = db_fetch_row( $q );
        $content[$i]["product_code"] = $product["product_code"];
        $content[$i]["product_idn"]  = $productID;
        /*
        $qz = db_query("select filename FROM ".PRODUCT_PICTURES." WHERE photoID=".$product["default_picture"]." AND productID=".$productID);
        $rowz = db_fetch_row($qz);
        if (strlen($rowz["filename"])>0 && file_exists( "data/small/".$rowz["filename"]))
        $content[$i]["product_picture"] = $rowz["filename"];
        else $content[$i]["product_picture"] = null;
         */
        $variants = GetConfigurationByItemId( $content[$i]["itemID"] );
        $options  = GetStrOptions( $variants );
        if ( $options != "" ) {
            $content[$i]["name"] = $product["name"] . "(" . $options . ")";
        } else {
            $content[$i]["name"] = $product["name"];
        }

    }

    $smarty_mail->assign( "content", $content );
    $html = $smarty_mail->fetch( "admin_order_notification.tpl.html" );

    if ( !CONF_ACTIVATE_ORDER ) {
        xMailTxtHTMLDATA( CONF_ORDERS_EMAIL, STRING_ORDER . " #" . $orderID . " - " . CONF_SHOP_NAME, $html );
    } else {
        xMailTxtHTMLDATA( CONF_ORDERS_EMAIL, STRING_ORDER . " #" . $orderID . " (" . ADMIN_SEND_INACT_ORDER . ") - " . CONF_SHOP_NAME, $html );
    }

}

// *****************************************************************************
// Purpose        get order amount
// Inputs
//                                $cartContent is result of cartGetCartContent function
// Remarks
// Returns
// ant64 добавил , $companyID = 0
function ordOrderProcessing(
    $shippingMethodID,
    $paymentMethodID,
    $shippingAddressID,
    $billingAddressID,
    $shippingModuleFiles,
    $paymentModulesFiles,
    $customers_comment,
    $cc_number,
    $cc_holdername,
    $cc_expires,
    $cc_cvv,
    $log,
    $smarty_mail,
    $shServiceID,
    $companyID = 0
) {

    if ( $log != null ) {
        $customerID = regGetIdByLogin( $log );
    } else {
        $customerID = null;
    }

    if ( $log != null ) {
        $customerInfo = regGetCustomerInfo2( $log );
    } else {
        $customerInfo["first_name"]       = $_SESSION["first_name"];
        $customerInfo["last_name"]        = $_SESSION["last_name"];
        $customerInfo["Email"]            = $_SESSION["email"];
        $customerInfo["affiliationLogin"] = $_SESSION["affiliationLogin"];
    }
    $order_time        = get_current_time();
    $frandl            = mt_rand( 3, 999 );
    $order_active_link = md5( $order_time ) . $frandl;
    $customer_ip       = stGetCustomerIP_Address();
    if ( CONF_ACTIVATE_ORDER == 1 ) {
        $statusID = 0;
    } else {
        $statusID = ostGetNewOrderStatus();
    }

    $customer_affiliationLogin = isset( $customerInfo["affiliationLogin"] ) ? $customerInfo["affiliationLogin"] : '';
    $customer_email            = $customerInfo["Email"];

    $currencyID = currGetCurrentCurrencyUnitID();
    if ( $currencyID != 0 ) {
        $currentCurrency = currGetCurrencyByID( $currencyID );
        $currency_code   = $currentCurrency["currency_iso_3"];
        # в таблицу заказов вписываем цену валюты БЕЗ НДС, поэтому для "5" делим ее курс на значение НДС 20%
        $currency_value = ( $currencyID != 5 ) ? $currentCurrency["currency_value"] : (double)$currentCurrency["currency_value"] / ( 100.0000 + DEFAULT_VAT_RATE ) * 100.0000;
        $currency_round = $currentCurrency["roundval"];
    } else {
        $currency_code  = "";
        $currency_value = 1;
        $currency_round = 2;
    }

    // get shipping address
    if ( $shippingAddressID != 0 ) {
        $shippingAddress                 = regGetAddress( $shippingAddressID );
        $shippingAddressCountry          = cnGetCountryById( $shippingAddress["countryID"] );
        $shippingAddress["country_name"] = $shippingAddressCountry["country_name"];
    } else {
        $shippingCountryName           = cnGetCountryById( $_SESSION["receiver_countryID"] );
        $shippingCountryName           = $shippingCountryName["country_name"];
        $shippingAddress["first_name"] =
            $_SESSION["receiver_first_name"];
        $shippingAddress["last_name"] =
            $_SESSION["receiver_last_name"];
        $shippingAddress["country_name"] = $shippingCountryName;
        $shippingAddress["state"]        =
            $_SESSION["receiver_state"];

        $shippingAddress["city"] =
            $_SESSION["receiver_city"];
        $shippingAddress["address"] =
            $_SESSION["receiver_address"];
        $shippingAddress["zoneID"] = $_SESSION["receiver_zoneID"];
    }
    if ( is_null( $shippingAddress["state"] ) || trim( $shippingAddress["state"] ) == "" ) {
        $zone                     = znGetSingleZoneById( $shippingAddress["zoneID"] );
        $shippingAddress["state"] = $zone["zone_name"];
    }

    // get billing address
    if ( $billingAddressID != 0 ) {
        $billingAddress                 = regGetAddress( $billingAddressID );
        $billingAddressCountry          = cnGetCountryById( $billingAddress["countryID"] );
        $billingAddress["country_name"] = $billingAddressCountry["country_name"];
    } else {
        $billingCountryName             = cnGetCountryById( $_SESSION["billing_countryID"] );
        $billingCountryName             = $billingCountryName["country_name"];
        $billingAddress["first_name"]   = $_SESSION["billing_first_name"];
        $billingAddress["last_name"]    = $_SESSION["billing_last_name"];
        $billingAddress["country_name"] = $billingCountryName;
        $billingAddress["state"]        = $_SESSION["billing_state"];

        $billingAddress["city"]    = $_SESSION["billing_city"];
        $billingAddress["address"] = $_SESSION["billing_address"];
        $billingAddress["zoneID"]  = $_SESSION["billing_zoneID"];
    }
    if ( is_null( $billingAddress["state"] ) || trim( $billingAddress["state"] ) == "" ) {
        $zone                    = znGetSingleZoneById( $billingAddress["zoneID"] );
        $billingAddress["state"] = $zone["zone_name"];
    }

    $cartContent = cartGetCartContent();

    if ( $log != null ) {
        $addresses = array( $shippingAddressID, $billingAddressID );
    } else {
        $addresses = array(
            array(
                "countryID" => $_SESSION["receiver_countryID"],
                "zoneID"    => $_SESSION["receiver_zoneID"] ),
            array(
                "countryID" => $_SESSION["billing_countryID"],
                "zoneID"    => $_SESSION["billing_zoneID"] ),
        );
    }

    $orderDetails = array(
        "first_name"   => $shippingAddress["first_name"],
        "last_name"    => $shippingAddress["last_name"],
        "email"        => $customerInfo["Email"],
        "order_amount" => oaGetOrderAmountExShippingRate( $cartContent, $addresses, $log, FALSE ),
    );

    $shippingMethod               = shGetShippingMethodById( $shippingMethodID );
    $shipping_email_comments_text = $shippingMethod["email_comments_text"];
    $shippingName                 = $shippingMethod["Name"];

    $paymentMethod               = payGetPaymentMethodById( $paymentMethodID );
    $paymentName                 = $paymentMethod["Name"];
    $payment_email_comments_text = $paymentMethod["email_comments_text"];

// $shipping_costUC  =array();//ant

    if ( isset( $paymentMethod["calculate_tax"] ) && (int)$paymentMethod["calculate_tax"] == 0 ) {

        $order_amount = oaGetOrderAmount( $cartContent, $addresses,
            $shippingMethodID, $log, $orderDetails, TRUE, $shServiceID );
        $d   = oaGetDiscountPercent( $cartContent, $log );
        $tax = 0;

        $shipping_costUC  = oaGetShippingCostTakingIntoTax( $cartContent, $shippingMethodID, $addresses, $orderDetails, FALSE, $shServiceID, TRUE );
        $discount_percent = oaGetDiscountPercent( $cartContent, $log );

    } else {

        $order_amount = oaGetOrderAmount( $cartContent, $addresses,
            $shippingMethodID, $log, $orderDetails, TRUE, $shServiceID );
        $d   = oaGetDiscountPercent( $cartContent, $log );
        $tax = oaGetProductTax( $cartContent, $d, $addresses );

        $shipping_costUC  = oaGetShippingCostTakingIntoTax( $cartContent, $shippingMethodID, $addresses, $orderDetails, TRUE, $shServiceID, TRUE );
        $discount_percent = oaGetDiscountPercent( $cartContent, $log );

    }

    $shServiceInfo = '';

    if ( is_array( $shipping_costUC ) ) {
        list( $shipping_costUC ) = $shipping_costUC;
        $shServiceInfo         = $shipping_costUC['name'];
        $shipping_costUC       = $shipping_costUC['rate'];
    }

    $paymentMethod = payGetPaymentMethodById( $paymentMethodID );

    if ( $paymentMethod ) {
        $currentPaymentModule = modGetModuleObj( $paymentMethod["module_id"], PAYMENT_MODULE );
    } else {
        $currentPaymentModule = null;
    }

    if ( $currentPaymentModule != null ) {
        //define order details for payment module
        $order_payment_details = array(
            "customer_email" => $customer_email,
            "customer_ip"    => $customer_ip,
            "order_amount"   => $order_amount,
            "currency_code"  => $currency_code,
            "currency_value" => $currency_value,
            "shipping_cost"  => $shipping_costUC,
            "order_tax"      => $tax,
            "shipping_info"  => $shippingAddress,
            "billing_info"   => $billingAddress,
        );

        $process_payment_result = $currentPaymentModule->payment_process( $order_payment_details ); //gets payment processing result

        if ( !( $process_payment_result == 1 ) ) //error on payment processing
        {
            if ( isset( $_POST ) ) {
                $_SESSION["order4confirmation_post"] = $_POST;
            }

            xSaveData( 'PaymentError', $process_payment_result );
            if ( !$customerID ) {
                RedirectProtected( "index.php?order4_confirmation_quick=yes" .
                    "&shippingMethodID=" . $_GET["shippingMethodID"] .
                    "&paymentMethodID=" . $_GET["paymentMethodID"] .
                    "&shServiceID=" . $shServiceID );
            } else {
                RedirectProtected( "index.php?order4_confirmation=yes" .
                    "&shippingAddressID=" . $_GET["shippingAddressID"] . "&shippingMethodID=" . $_GET["shippingMethodID"] . "&billingAddressID=" . $_GET["billingAddressID"] . "&paymentMethodID=" . $_GET["paymentMethodID"] .
                    "&shServiceID=" . $shServiceID );
            }
            return false;
        }

    }

    $customerID = (int)$customerID;

    $sql = "INSERT  INTO " . ORDERS_TABLE .
    " ( customerID, " .
    "        order_time, " .
    "        customer_ip, " .
    "        shipping_type, " .
    "        payment_type, " .
    "        customers_comment, " .
    "        statusID, " .
    "        shipping_cost, " .
    "        order_discount, " .
    "        order_amount, " .
    "        currency_code, " .
    "        currency_value, " .
    "        customer_firstname, " .
    "        customer_lastname, " .
    "        customer_email, " .
    "        shipping_firstname, " .
    "        shipping_lastname, " .
    "        shipping_country, " .
    "        shipping_state, " .
    "        shipping_city, " .
    "        shipping_address, " .
    "        billing_firstname, " .
    "        billing_lastname, " .
    "        billing_country, " .
    "        billing_state, " .
    "        billing_city, " .
    "        billing_address, " .
    "        cc_number, " .
    "        cc_holdername, " .
    "        cc_expires, " .
    "        cc_cvv, " .
    "        affiliateID, " .
    "        shippingServiceInfo, " .
    "        custlink, " .
    "        currency_round, " .
    "        shippmethod," .
    "        paymethod," .
    "        companyID" .
    "                  ) " .
    " VALUES ( " .
    (int)$customerID . ", " .
    "'" . xEscSQL( $order_time ) . "', " .
    "'" . xToText( $customer_ip ) . "', " .
    "'" . xToText( $shippingName ) . "', " .
    "'" . xToText( $paymentName ) . "', " .
    "'" . xToText( $customers_comment ) . "', " .
    (int)$statusID . ", " .
    ( (float)$shipping_costUC ) . ", " .
    ( (float)$discount_percent ) . ", " .
    ( (float)$order_amount ) . ", " .
    "'" . xEscSQL( $currency_code ) . "', " .
    ( (float)$currency_value ) . ", " .
    "'" . xToText( $customerInfo["first_name"] ) . "', " .
    "'" . xToText( $customerInfo["last_name"] ) . "', " .
    "'" . xToText( $customer_email ) . "', " .
    "'" . xToText( $shippingAddress["first_name"] ) . "', " .
    "'" . xToText( $shippingAddress["last_name"] ) . "', " .
    "'" . xToText( $shippingAddress["country_name"] ) . "', " .
    "'" . xToText( $shippingAddress["state"] ) . "', " .
    "'" . xToText( $shippingAddress["city"] ) . "', " .
    "'" . xToText( $shippingAddress["address"] ) . "', " .
    "'" . xToText( $billingAddress["first_name"] ) . "', " .
    "'" . xToText( $billingAddress["last_name"] ) . "', " .
    "'" . xToText( $billingAddress["country_name"] ) . "', " .
    "'" . xToText( $billingAddress["state"] ) . "', " .
    "'" . xToText( $billingAddress["city"] ) . "', " .
    "'" . xToText( $billingAddress["address"] ) . "', " .
    "'" . xEscSQL( $cc_number ) . "', " .
    "'" . xToText( $cc_holdername ) . "', " .
    "'" . xEscSQL( $cc_expires ) . "', " .
    "'" . xEscSQL( $cc_cvv ) . "', " .
    "'" . ( isset( $_SESSION['refid'] ) ? $_SESSION['refid'] : regGetIdByLogin( $customer_affiliationLogin ) ) . "'," .
    "'{$shServiceInfo}', " .
    "'" . xEscSQL( $order_active_link ) . "', " .
    "'" . (int)$currency_round . "', " .
    "'" . (int)$shippingMethodID . "', " .
    "'" . (int)$paymentMethodID . "', " .
    "'" . (int)$companyID . "'" .
        " ) ";
    db_query( $sql );
    $orderID = db_insert_id( ORDERS_TABLE );

    if ( !CONF_ACTIVATE_ORDER ) {
        stChangeOrderStatus( $orderID, $statusID );
    }

    $paymentMethod = payGetPaymentMethodById( $paymentMethodID );
    if ( $paymentMethod ) {
        $currentPaymentModule = modGetModuleObj( $paymentMethod["module_id"], PAYMENT_MODULE );
//                $currentPaymentModule = payGetPaymentModuleById( $paymentMethod["module_id"], $paymentModulesFiles );
    } else {
        $currentPaymentModule = null;
    }

    # Добавление ТРАНЗИТНЫХ ТОВАРОВ ????
    //save shopping cart content to database and update in-stock information
    if ( $log != null ) {
        cartMoveContentFromShoppingCartsToOrderedCarts( $orderID,
            $shippingMethodID, $paymentMethodID,
            $shippingAddressID, $billingAddressID,
            $shippingModuleFiles, $paymentModulesFiles, $smarty_mail );
    } else //quick checkout
    {
        _moveSessionCartContentToOrderedCart( $orderID );
        //update in-stock information
        # только in_stock,
        # имя уже скорректирвано в cartMoveContentFromShoppingCartsToOrderedCarts _moveSessionCartContentToOrderedCart
        if ( $statusID != ostGetCanceledStatusId() && CONF_CHECKSTOCK ) {
            $q1 = db_query( "SELECT itemID, Quantity FROM " . ORDERED_CARTS_TABLE . " WHERE enabled=1 AND orderID=" . (int)$orderID );
            while ( $item = db_fetch_row( $q1 ) ) {
                $q2 = db_query( "SELECT productID FROM " . SHOPPING_CART_ITEMS_TABLE . " WHERE itemID=" . (int)$item["itemID"] );
                $pr = db_fetch_row( $q2 );
                if ( $pr ) {
                    # Добавление ТРАНЗИТНЫХ ТОВАРОВ
                    $q_product = db_query( "SELECT name, product_code,in_stock FROM " . PRODUCTS_TABLE .
                        " where productID=" . (int)$pr[0] );
                    $old_product = db_fetch_row( $q_product );

                    $new_in_stock  = (int)$old_product["in_stock"];
                    $in_stock      = (int)$old_product["in_stock"];
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

                    // db_query( "update " . PRODUCTS_TABLE . " set in_stock = in_stock - " . (int)$item["Quantity"] .
                    //     " where productID=" . (int)$pr[0] );
                    db_query( "update " . PRODUCTS_TABLE . " set in_stock = " . (int)$new_in_stock . " where productID=" . (int)$pr[0] );
                    #END Добавление ТРАНЗИТНЫХ ТОВАРОВ

                    $q          = db_query( "SELECT name, in_stock FROM " . PRODUCTS_TABLE . " WHERE productID=" . (int)$pr[0] );
                    $productsta = db_fetch_row( $q );
                    if ( $productsta[1] == 0 ) {
                        if ( CONF_AUTOOFF_STOCKADMIN ) {
                            db_query( "update " . PRODUCTS_TABLE . " set enabled=0 where productID=" . (int)$pr[0] );
                        }

                        if ( CONF_NOTIFY_STOCKADMIN ) {
                            $smarty_mail->assign( "productstaname", $productsta[0] );
                            $smarty_mail->assign( "productstid", $pr[0] );
                            $stockadmin = $smarty_mail->fetch( "notify_stockadmin.tpl.html" );
                            $ressta     = xMailTxtHTMLDATA( CONF_ORDERS_EMAIL, CUSTOMER_ACTIVATE_99 . " - " . CONF_SHOP_NAME, $stockadmin );
                        }
                    }
                }
            }
        }

        //now save registration form aux fields into CUSTOMER_REG_FIELDS_VALUES_TABLE_QUICKREG
        //for quick checkout orders these fields are stored separately than for registered customer (SS_customers)
        db_query( "delete FROM " . CUSTOMER_REG_FIELDS_VALUES_TABLE_QUICKREG . " where orderID=" . (int)$orderID );
        foreach ( $_SESSION as $key => $val ) {
            if ( strstr( $key, "additional_field_" ) && strlen( trim( $val ) ) > 0 ) //save information into sessions
            {
                $id = (int)str_replace( "additional_field_", "", $key );
                if ( $id > 0 ) {
                    db_query( "insert into " . CUSTOMER_REG_FIELDS_VALUES_TABLE_QUICKREG . " (orderID, reg_field_ID, reg_field_value) values (" . (int)$orderID . ", " . (int)$id . ", '" . xToText( trim( $val ) ) . "');" );
                }
            }
        }
    }

    if ( $currentPaymentModule != null ) {
        // создание счета invoice
        $currentPaymentModule->after_processing_php( $orderID );
    }

    _sendOrderNotifycationToAdmin( $orderID, $smarty_mail, $tax );

    _sendOrderNotifycationToCustomer( $orderID, $smarty_mail, $customerInfo["Email"], $log,
        $payment_email_comments_text, $shipping_email_comments_text, $tax, $order_active_link );

    if ( $log == null ) {
        _quickOrderUnsetSession();
    }

    unset( $_SESSION["order4confirmation_post"] );

    return $orderID;
}

function _setHyphen( &$str ) {
    if ( trim( $str ) == "" || $str == null ) {
        $str = "-";
    }

}

// *****************************************************************************
// Purpose        get order by id
// Inputs
// Remarks
// Returns
function ordGetOrder( $orderID ) {

    $q = db_query( "SELECT orderID, customerID, order_time, customer_ip, " .
        " shipping_type, payment_type, customers_comment, " .
        " statusID, shipping_cost, order_discount, order_amount, " .
        " currency_code, currency_value, customer_firstname, customer_lastname, " .
        " customer_email, shipping_firstname, shipping_lastname, " .
        " shipping_country, shipping_state, shipping_city, " .
        " shipping_address, billing_firstname, billing_lastname, billing_country, " .
        " billing_state, billing_city, billing_address, " .
        " cc_number, cc_holdername, cc_expires, cc_cvv, affiliateID, shippingServiceInfo, currency_round, shippmethod, paymethod, companyID, order_aka  FROM " . ORDERS_TABLE . " WHERE orderID=" . (int)$orderID );
    $order = db_fetch_row( $q );
    if ( $order ) {
        /*_setHyphen( $order["shipping_firstname"] );
        _setHyphen( $order["customer_lastname"] );
        _setHyphen( $order["customer_email"] );
        _setHyphen( $order["shipping_firstname"] );
        _setHyphen( $order["shipping_lastname"] );
        _setHyphen( $order["shipping_country"] );
        _setHyphen( $order["shipping_state"] );
        _setHyphen( $order["shipping_city"] );
        _setHyphen( $order["shipping_address"] );
        _setHyphen( $order["billing_firstname"] );
        _setHyphen( $order["billing_lastname"] );
        _setHyphen( $order["billing_country"] );
        _setHyphen( $order["billing_state"] );
        _setHyphen( $order["billing_city"] );
        _setHyphen( $order["billing_address"] );*/

        $order["shipping_address"] = rtrim( $order["shipping_address"] );
        $order["billing_address"]  = rtrim( $order["billing_address"] );
        //CC data
        if ( CONF_BACKEND_SAFEMODE ) {
            $order["cc_number"]     = ADMIN_SAFEMODE_BLOCKED;
            $order["cc_holdername"] = ADMIN_SAFEMODE_BLOCKED;
            $order["cc_expires"]    = ADMIN_SAFEMODE_BLOCKED;
            $order["cc_cvv"]        = ADMIN_SAFEMODE_BLOCKED;
        } else {
            if ( strlen( $order["cc_number"] ) > 0 ) {
                $order["cc_number"] = cryptCCNumberDeCrypt( $order["cc_number"], null );
            }

            if ( strlen( $order["cc_holdername"] ) > 0 ) {
                $order["cc_holdername"] = cryptCCHoldernameDeCrypt( $order["cc_holdername"], null );
            }

            if ( strlen( $order["cc_expires"] ) > 0 ) {
                $order["cc_expires"] = cryptCCExpiresDeCrypt( $order["cc_expires"], null );
            }

            if ( strlen( $order["cc_cvv"] ) > 0 ) {
                $order["cc_cvv"] = cryptCCNumberDeCrypt( $order["cc_cvv"], null );
            }

        }

        //additional reg fields
        $addregfields               = GetRegFieldsValuesByOrderID( $orderID );
        $order["reg_fields_values"] = $addregfields;

        $q_status_name = db_query( "SELECT status_name FROM " . ORDER_STATUES_TABLE . " WHERE statusID=" . (int)$order["statusID"] );
        $status_name   = db_fetch_row( $q_status_name );
        $status_name   = $status_name[0];

        if ( $order["statusID"] == ostGetCanceledStatusId() ) {
            $status_name = STRING_CANCELED_ORDER_STATUS;
        }

        // clear cost ( without shipping, discount, tax )
        $q1                = db_query( "SELECT Price, Quantity FROM " . ORDERED_CARTS_TABLE . " WHERE enabled=1 AND orderID=" . (int)$orderID );
        $clear_total_price = 0;
        while ( $row = db_fetch_row( $q1 ) ) {
            $clear_total_price += $row["Price"] * $row["Quantity"];
        }

        $currency_round                        = $order["currency_round"];
        $order["clear_total_priceToShow"]      = _formatPrice( roundf( $order["currency_value"] * $clear_total_price ), $currency_round ) . " " . $order["currency_code"];
        $order["order_discount_ToShow"]        = _formatPrice( roundf( $order["currency_value"] * $clear_total_price * (  ( 100 - $order["order_discount"] ) / 100 ) ), $currency_round ) . " " . $order["currency_code"];
        $order["shipping_costToShow"]          = _formatPrice( roundf( $order["currency_value"] * $order["shipping_cost"] ), $currency_round ) . " " . $order["currency_code"];
        $order["order_amountToShow_fromOrder"] = _formatPrice( roundf( $order["currency_value"] * $order["order_amount"] ), $currency_round ) . " " . $order["currency_code"];
        $order["order_amountToShow"]           = _formatPrice( roundf( $order["currency_value"] * ( $order["order_amount"] + $order["shipping_cost"] ) ) * (  ( 100 - $order["order_discount"] ) / 100 ), $currency_round ) . " " . $order["currency_code"];

        $order["order_time_mysql"] = $order["order_time"];
        $order["order_time"]       = format_datetime( $order["order_time"] );

        $order["status_name"] = $status_name;

## invoice info
        $order["invoice"] = getInvoiceDataForThisOrder( $orderID, $order["order_time"], $order["customer_email"] );
## invoice info

    }

    return $order;
}

function getInvoiceDataForThisOrder(
    $orderID,
    $order_time_mysql,
    $customer_email
) {

    $dtDoDates = dtDoDates( $order_time_mysql, (int)$orderID );
    $ff4       = $dtDoDates["ff4"];

    $btnlink   = "";
    $invoice   = array();
    $q_invoice = db_query( "SELECT invoiceID, module_id, invoice_time, buyerID, currency_rate FROM " .
        INVOICES_TABLE . " WHERE orderID=" . (int)$orderID . " ORDER BY invoiceID" );

    while ( $row = db_fetch_assoc( $q_invoice ) ) {

        // $btnlink = getLinkToShowInvoice(
        //     $ModuleConfigID = base64_encode( (int)$row["module_id"] ),
        //     $invoiceID = null,
        //     $orderID = (int)$orderID,
        //     $companyID = null,
        //     $currencyID = null,
        //     $order_time = base64_encode( $order_time_mysql ),
        //     $customer_email = base64_encode( $customer_email )
        // );

        $btnlink = urldecode( getNewPdfLink( $row["invoiceID"] ) );

        $btnEditInvoice = <<<HTML
 <a href=
"?dpt=custord&sub=invoices&edit={$row['invoiceID']}"
> Edit</a>
HTML;

        $invoice_string = invoiceGetModuleSettingValue( (int)$row["module_id"], "PM_INVOICE_STRING" );

        $row["orderID"]          = (int)$orderID;
        $row["invoiceID"]        = (int)$row["invoiceID"];
        $row["order_time_mysql"] = FormatAsSQL( $order_time_mysql, 1 );
        $row["invoice_time_RU"]  = FormatAsRUS( $order_time_mysql, 0 );
        $row["btnlink"]          = $btnlink;
        $row["invoice_string"]   = $invoice_string;
        $row["ff4"]              = $ff4;
        $row["currency_rate"]    = (double)$row["currency_rate"];

        $row["buyer_data"] = htmlCompanyPreview( _getCompany( $row["buyerID"] ), 15 );

        $invoice[] = $row;
    }

    return $invoice;
}

function ordAdvancedOrderContent(
    $orderID,
    $Currency_rate,
    $shipping_cost = 0,
    $order_discount = 0,
    $nds_included = 0,
    $VAT_rate = 20,
    $Multiplier = 1,
    $dec = 2,
    $currency_iso_3 = "BYN",
    $invoice_subdiscount = 0
) {

    if ( $nds_included == 1 ) {
        $OUT_rate = (double)$Currency_rate;
    } else {
        $OUT_rate = (double)$Currency_rate * ( 100.0000 + $VAT_rate ) / 100.0000;
    }

    $DiscountPercent = ( (double)$order_discount + (double)$invoice_subdiscount );

    // debugfile($DiscountPercent," func $DiscountPercent = ( $order_discount + $invoice_subdiscount );");

    $OUT_rate = ( $DiscountPercent != 0 ) ? ( $OUT_rate * ( 100 - $DiscountPercent ) / 100 ) : $OUT_rate;

    #### $OUT_rate -- курс по которому выводится счет

    $order_content = ordGetOrderContent( $orderID );
    $order_qty     = 0;
    # размазываем стоимость доставки по всем строчкам
    if ( count( $order_content ) > 0 ) {
        $shipping_cost_per_row = (double)( $shipping_cost / count( $order_content ) );
    } else {
        $shipping_cost_per_row = 0;
    }
    # END размазываем стоимость доставки по всем строчкам
    $delayMAX = 0;
    foreach ( $order_content as $key => $val ) {
        $itemDelay    = array();
        $shipping_pay = 0;
        $order_qty += $val["Quantity"];

        $ClearPrice = (double)$order_content[$key]["Price"];

        # размазываем стоимость доставки по всем товарам в строке
        if (  ( $shipping_cost_per_row > 0 ) && ( $val["Quantity"] != 0 ) ) {
            $shipping_pay = (double)( $shipping_cost_per_row / $val["Quantity"] );
            $Price_USD    = $ClearPrice + $shipping_pay;
        } else {
            $shipping_pay = 0;
            $Price_USD    = $ClearPrice;
        }
        # END размазываем стоимость доставки по всем товарам в строке

        $Price_OUT   = number_format(  ( $Multiplier * $Price_USD * $OUT_rate / ( 100.0000 + $VAT_rate ) * 100.0000 ), $dec, ".", "" );
        $Cost_OUT    = ( $Price_OUT * $val["Quantity"] );
        $VATsumm_OUT = number_format(  ( $Cost_OUT * $VAT_rate / ( 100.0000 ) ), $dec, ".", "" );
        $Total_OUT   = $Cost_OUT + $VATsumm_OUT;

        $string_name = $val["name"];

        // $my_pattern          = "/[\[]][\w_.-]+[\]/";
        // $my_pattern          = "/\[([^\[\]]+)\]/";
        // $matches=array();
        // preg_match_all( $my_pattern, $string_name, $matches );
        // debugfile($matches,"{$string_name}");

        $order_content[$key]["product_code"] = antGetProductCodeFromComplexName( $string_name );
        $order_content[$key]["model"]        = antGetMODELFromComplexName( $string_name );

        $search                            = array( $order_content[$key]["product_code"], "[]" );
        $order_content[$key]["clear_name"] = str_replace( $search, "", $string_name );

        $search = array( "<>", "< >" );

        $order_content[$key]["Quantity"]     = $val["Quantity"];
        $order_content[$key]["ClearPrice"]   = _formatPrice( (double)$ClearPrice, 5 ); //ClearPrice
        $order_content[$key]["shipping_pay"] = _formatPrice( (double)$shipping_pay, 5 );
        $order_content[$key]["Price_USD"]    = (double)$Price_USD;

        // $order_content[$key]["Price_OUT"] = invoiceBYformat( $Price_OUT );
        // $order_content[$key]["Cost_OUT"]  = invoiceBYformat( $Cost_OUT );
        // $order_content[$key]["VATsumm_OUT"]   = invoiceBYformat( $VATsumm_OUT );
        // $order_content[$key]["Total_OUT"] = invoiceBYformat( $Total_OUT );

        $order_content[$key]["Price_OUT"]   = _formatPrice( $Price_OUT, $dec, ".", "" );
        $order_content[$key]["Cost_OUT"]    = _formatPrice( $Cost_OUT, $dec, ".", "" );
        $order_content[$key]["VATsumm_OUT"] = _formatPrice( $VATsumm_OUT, $dec, ".", "" );
        $order_content[$key]["Total_OUT"]   = _formatPrice( $Total_OUT, $dec, ".", "" );

        $order_content[$key]["itemPriority"] = $val["itemPriority"];

        $TOTALwithOutVAT += (double)strtr( $order_content[$key]["Cost_OUT"], array( "," => "", " " => "" ) );
        $TOTALamountVAT += (double)strtr( $order_content[$key]["VATsumm_OUT"], array( "," => "", " " => "" ) );
        $TOTALwithVAT += (double)strtr( $order_content[$key]["Total_OUT"], array( "," => "", " " => "" ) );

        // $TOTALamountVAT=invoiceBYformat( $TOTALamountVAT ) ;
        // $TOTALwithOutVAT=invoiceBYformat( $TOTALwithOutVAT ) ;
        // $TOTALwithVAT=invoiceBYformat( $TOTALwithVAT ) ;
        $TOTALwithOutVAT = _formatPrice( $TOTALwithOutVAT, $dec, ".", "" );
        $TOTALamountVAT  = _formatPrice( $TOTALamountVAT, $dec, ".", "" );
        $TOTALwithVAT    = _formatPrice( $TOTALwithVAT, $dec, ".", "" );

        # товары под заказ подсчет максимального времени доставки Только есть твары с текстом 'STRING_INSTOCK_TRAIN_GO', 'Под заказ'
        $INSTOCK_TRAIN_GO_FLAG = ( strlen( stristr(  ( $order_content[$key]["name"] ), STRING_INSTOCK_TRAIN_GO ) ) >= 1 );

        if ( $INSTOCK_TRAIN_GO_FLAG ) {
            $productID                           = (int)$order_content[$key]["pr_item"];
            $order_content[$key]["real_instock"] = GetProductInStockCount( $productID );
            $order_content[$key]["fake_instock"] = generateFakedInStockCount( $order_content[$key]["real_instock"] );

            $itemDelay                          = Decode_row_in_stock( $order_content[$key]["real_instock"] );
            $order_content[$key]["DelayString"] = $itemDelay["in_stock_string"];
            $order_content[$key]["DelayValue"]  = $itemDelay["Value"];

            $delay_temp = $itemDelay["Value"];
            if ( $delay_temp > $delayMAX ) {
                $delayMAX = $delay_temp;
            }
        }
    }

    $ShippingTerminExtension = Decode_row_in_stock( -$delayMAX ); //максимальная задержка

    # Стоимость доставки из заказа
    $shipping_rate = (double)$shipping_cost * $OUT_rate;
    # Скидка из заказа
    // $order["discount_value"] = round( (double)$order["order_discount"] * $TOTALwithVAT ) / 100;
    // $discount_value          = $order["discount_value"];
    $discount_value = round( (double)$order_discount * $TOTALwithVAT ) / 100;

    $res     = array();
    $strings = array();
    $strings = array(
        "TOTALamountVAT_STRING"  => SumToBYNRussian( $TOTALamountVAT ),
        "TOTALwithOutVAT_STRING" => SumToBYNRussian( $TOTALwithOutVAT ),
        "TOTALwithVAT_STRING"    => SumToBYNRussian( $TOTALwithVAT ),
    );

    if ( $currency_iso_3 == "USD" ) {

        $strings["TOTALamountVAT_STRING"]  = " по курсу Нацонального банка Республики Беларусь";
        $strings["TOTALwithOutVAT_STRING"] = " по курсу Нацонального банка Республики Беларусь";
        $strings["TOTALwithVAT_STRING"]    = " по курсу Нацонального банка Республики Беларусь";

    }

    if ( $currency_iso_3 == "EUR" ) {

        $strings["TOTALamountVAT_STRING"]  = " по курсу Нацонального банка Республики Беларусь";
        $strings["TOTALwithOutVAT_STRING"] = " по курсу Нацонального банка Республики Беларусь";
        $strings["TOTALwithVAT_STRING"]    = " по курсу Нацонального банка Республики Беларусь";
    }

    // if ( $OUT_rate != 0 && $nds_included == 0) {
    //     $TOTALwithOutVAT_USD = $TOTALwithOutVAT / $OUT_rate*(100+$VAT_rate)/100;
    //     $TOTALamountVAT_USD  = $TOTALamountVAT / $OUT_rate*(100+$VAT_rate)/100;
    //     $TOTALwithVAT_USD    = $TOTALwithVAT / $OUT_rate*(100+$VAT_rate)/100;
    // } elseif( $OUT_rate != 0 && $nds_included == 1) {
    //     $TOTALwithOutVAT_USD = $TOTALwithOutVAT / $OUT_rate;
    //     $TOTALamountVAT_USD  = $TOTALamountVAT / $OUT_rate;
    //     $TOTALwithVAT_USD    = $TOTALwithVAT / $OUT_rate;
    // }

    if ( $OUT_rate != 0 ) {
        $TOTALwithOutVAT_USD = $TOTALwithOutVAT / $OUT_rate * ( 100 + $VAT_rate ) / 100;
        $TOTALamountVAT_USD  = $TOTALamountVAT / $OUT_rate * ( 100 + $VAT_rate ) / 100;
        $TOTALwithVAT_USD    = $TOTALwithVAT / $OUT_rate * ( 100 + $VAT_rate ) / 100;
    }

    $res = array(
        "order_content"           => $order_content,

        "delayMAX"                => $delayMAX,
        "order_qty"               => $order_qty,
        "Currency_rate"           => invoiceUSDformat( $Currency_rate ),
        "iso_3"                   => "{$currency_iso_3}",
        "OUT_rate"                => invoiceUSDformat( $OUT_rate ),
        "VAT_rate"                => _formatPrice( $VAT_rate, 2, ".", "" ),
        // _formatPrice( $price, $dec, ".", " " );

        "TOTALwithOutVAT"         => $TOTALwithOutVAT,
        "TOTALamountVAT"          => $TOTALamountVAT,
        "TOTALwithVAT"            => $TOTALwithVAT,

        "TOTALwithOutVAT_USD"     => invoiceUSDformat( $TOTALwithOutVAT_USD ),
        "TOTALamountVAT_USD"      => invoiceUSDformat( $TOTALamountVAT_USD ),
        "TOTALwithVAT_USD"        => invoiceUSDformat( $TOTALwithVAT_USD ),

        "ShippingTerminExtension" => $ShippingTerminExtension,
        "shipping_rate"           => _formatPrice(  ( $shipping_rate ), $dec, ".", "" ),
        "shipping_cost"           => _formatPrice(  ( $shipping_cost ), $dec, ".", "" ),
        "order_discount"          => invoiceUSDformat( $order_discount ),
        "discount_value"          => _formatPrice(  ( $discount_value ), $dec, ".", "" ),
        "DiscountPercent"         => _formatPrice( $DiscountPercent, $dec, ".", "" ),
        "invoice_subdiscount"     => _formatPrice( $invoice_subdiscount, $dec, ".", "" ),
        "strings"                 => $strings,

    );

    return $res;
}

function ordGetOrderContent( $orderID ) {
    $q              = db_query( "SELECT name, LEFT(name,64) as shortname, Price, Quantity, tax, load_counter, itemID, itemUnit, order_aka, itemPriority FROM " . ORDERED_CARTS_TABLE . " WHERE enabled=1 AND orderID=" . (int)$orderID . " ORDER BY itemPriority DESC, itemID ASC;" );
    $q_order        = db_query( "SELECT currency_code, currency_value, customerID, order_time, currency_round  FROM " . ORDERS_TABLE . " WHERE orderID=" . (int)$orderID );
    $order          = db_fetch_row( $q_order );
    $currency_code  = $order["currency_code"];
    $currency_value = $order["currency_value"];
    $currency_round = $order["currency_round"];
    $data           = array();

    while ( $row = db_fetch_row( $q ) ) {
        $productID      = GetProductIdByItemId( $row["itemID"] );
        $row["pr_item"] = $productID;
        $product        = GetProduct( $productID );

        if ( !isset( $row["itemUnit"] ) ) {
            $row["itemUnit"] = "шт";
        }
        if ( !isset( $row["itemPriority"] ) ) {
            $row["itemPriority"] = "0";
        }

        if ( $product["eproduct_filename"] != null &&
            $product["eproduct_filename"] != "" ) {
            if ( file_exists( "core/files/" . $product["eproduct_filename"] ) ) {
                $row["eproduct_filename"] = $product["eproduct_filename"];
                $row["file_size"]         = (string)round( filesize( "core/files/" . $product["eproduct_filename"] ) / 1048576, 3 );

                if ( $order["customerID"] != null ) {
                    $custID = $order["customerID"];
                } else {
                    $custID = -1;
                }

                $row["getFileParam"] =
                    "orderID=" . $orderID . "&" .
                    "productID=" . $productID . "&" .
                    "customerID=" . $custID;

                //additional security for non authorized customers
                if ( $custID == -1 ) {
                    $row["getFileParam"] .= "&order_time=" . base64_encode( $order["order_time"] );
                }

                $row["getFileParam"] = cryptFileParamCrypt(
                    $row["getFileParam"], null );
                $row["load_counter_remainder"] =
                    $product["eproduct_download_times"] - $row["load_counter"];

                $currentDate = dtGetParsedDateTime( get_current_time() );
                $betweenDay  = _getDayBetweenDate(
                    dtGetParsedDateTime( $order["order_time"] ),
                    $currentDate );

                $row["day_count_remainder"] = $product["eproduct_available_days"] - $betweenDay;

            }
        }

        $row["PriceToShow"] = _formatPrice( roundf( $currency_value * $row["Price"] * $row["Quantity"] ), $currency_round ) . " " . $currency_code;
        $row["PriceOne"]    = _formatPrice( roundf( $currency_value * $row["Price"] ), $currency_round ) . " " . $currency_code;
        $data[]             = $row;
    }
    return $data;
}

// *****************************************************************************
// Purpose        deletes  order
// Inputs
// Remarks        this function deletes canceled orders only
// Returns
function ordDeleteOrder( $orderID ) {
    $q   = db_query( "SELECT statusID FROM " . ORDERS_TABLE . " WHERE orderID=" . (int)$orderID );
    $row = db_fetch_row( $q );
    if ( $row["statusID"] != ostGetCanceledStatusId() ) {
        return;
    }

    db_query( "DELETE FROM " . ORDERED_CARTS_TABLE . " WHERE orderID=" . (int)$orderID );
    db_query( "DELETE FROM " . ORDERS_TABLE . " WHERE orderID=" . (int)$orderID );
    db_query( "DELETE FROM " . ORDER_STATUS_CHANGE_LOG_TABLE . " WHERE orderID=" . (int)$orderID );
// ant
    db_query( "DELETE FROM " . INVOICES_TABLE . " WHERE orderID=" . (int)$orderID );

}

function DelOrdersBySDL( $statusdel ) {
    $q = db_query( "SELECT orderID FROM " . ORDERS_TABLE . " WHERE statusID=" . (int)$statusdel );
    while ( $row = db_fetch_row( $q ) ) {
        db_query( "DELETE FROM " . ORDERED_CARTS_TABLE . " WHERE orderID=" . (int)$row["orderID"] );
        db_query( "DELETE FROM " . ORDERS_TABLE . " WHERE orderID=" . (int)$row["orderID"] );
        db_query( "DELETE FROM " . ORDER_STATUS_CHANGE_LOG_TABLE . " WHERE orderID=" . (int)$row["orderID"] );
        // ant
        db_query( "DELETE FROM " . INVOICES_TABLE . " WHERE orderID=" . (int)$orderID );
    }
}

// *****************************************************************************
// Purpose        gets summarize order info to
// Inputs
// Remarks
// Returns
function getOrderSummarize(

    $shippingMethodID,
    $paymentMethodID,
    $shippingAddressID,
    $billingAddressID,
    $shippingModuleFiles,
    $paymentModulesFiles,
    $shServiceID = 0
) {
    // result this function
    $sumOrderContent = array();

    $q                           = db_query( "SELECT email_comments_text FROM " . PAYMENT_TYPES_TABLE . " where PID=" . (int)$paymentMethodID );
    $payment_email_comments_text = db_fetch_row( $q );
    $payment_email_comments_text = $payment_email_comments_text[0];

    $q                            = db_query( "SELECT email_comments_text FROM " . SHIPPING_METHODS_TABLE . " where SID=" . (int)$shippingMethodID );
    $shipping_email_comments_text = db_fetch_row( $q );
    $shipping_email_comments_text = $shipping_email_comments_text[0];

    $cartContent = cartGetCartContent();
    $pred_total  = oaGetClearPrice( $cartContent );

    if ( isset( $_SESSION["log"] ) ) {
        $log = $_SESSION["log"];
    } else {
        $log = null;
    }

    $d        = oaGetDiscountPercent( $cartContent, $log );
    $discount = $pred_total / 100 * $d;

    // ordering with registration
    if ( $shippingAddressID != 0 || isset( $log ) ) {
        $addresses        = array( $shippingAddressID, $billingAddressID );
        $shipping_address = regGetAddressStr( $shippingAddressID );
        $billing_address  = regGetAddressStr( $billingAddressID );
        $shaddr           = regGetAddress( $shippingAddressID );
        $sh_firstname     = $shaddr["first_name"];
        $sh_lastname      = $shaddr["last_name"];
    } else //quick checkout
    {
        if ( !isset( $_SESSION["receiver_countryID"] ) || !isset( $_SESSION["receiver_zoneID"] ) ) {
            return NULL;
        }

        $shippingAddress = array(
            "countryID" => $_SESSION["receiver_countryID"],
            "zoneID"    => $_SESSION["receiver_zoneID"] );
        $billingAddress = array(
            "countryID" => $_SESSION["billing_countryID"],
            "zoneID"    => $_SESSION["billing_zoneID"] );
        $addresses        = array( $shippingAddress, $billingAddress );
        $shipping_address = quickOrderGetReceiverAddressStr();
        $billing_address  = quickOrderGetBillingAddressStr();

        $sh_firstname = $_SESSION["receiver_first_name"];
        $sh_lastname  = $_SESSION["receiver_last_name"];

    }

    foreach ( $cartContent["cart_content"] as $cartItem ) {
        // if conventional ordering
        if ( $shippingAddressID != 0 ) {
            $productID       = GetProductIdByItemId( $cartItem["id"] );
            $cartItem["tax"] = taxCalculateTax( $productID, $addresses[0], $addresses[1] );
        } else // if quick ordering
        {
            $productID       = $cartItem["id"];
            $cartItem["tax"] = taxCalculateTax2( $productID, $addresses[0], $addresses[1] );
        }
        $sumOrderContent[] = $cartItem;
    }

    $shipping_method = shGetShippingMethodById( $shippingMethodID );
    if ( !$shipping_method ) {
        $shipping_name = "-";
    } else {
        $shipping_name = $shipping_method["Name"];
    }

    $payment_method = payGetPaymentMethodById( $paymentMethodID );
    if ( !$payment_method ) {
        $payment_name = "-";
    } else {
        $payment_name = $payment_method["Name"];
    }

    //do not calculate tax for this payment type!
    if ( isset( $payment_method["calculate_tax"] ) && (int)$payment_method["calculate_tax"] == 0 ) {
        foreach ( $sumOrderContent as $key => $val ) {
            $sumOrderContent[$key]["tax"] = 0;
        }

        $orderDetails = array(
            "first_name"   => $sh_firstname,
            "last_name"    => $sh_lastname,
            "email"        => "",
            "order_amount" => oaGetOrderAmountExShippingRate( $cartContent, $addresses, $log, FALSE, $shServiceID ),
        );

        $tax           = 0;
        $total         = oaGetOrderAmount( $cartContent, $addresses, $shippingMethodID, $log, $orderDetails, FALSE, $shServiceID );
        $shipping_cost = oaGetShippingCostTakingIntoTax( $cartContent, $shippingMethodID, $addresses, $orderDetails, FALSE, $shServiceID );
    } else {
        $orderDetails = array(
            "first_name"   => $sh_firstname,
            "last_name"    => $sh_lastname,
            "email"        => "",
            "order_amount" => oaGetOrderAmountExShippingRate( $cartContent, $addresses, $log, FALSE ),
        );

        $tax           = oaGetProductTax( $cartContent, $d, $addresses );
        $total         = oaGetOrderAmount( $cartContent, $addresses, $shippingMethodID, $log, $orderDetails, TRUE, $shServiceID );
        $shipping_cost = oaGetShippingCostTakingIntoTax( $cartContent, $shippingMethodID, $addresses, $orderDetails, TRUE, $shServiceID );
    }

    $tServiceInfo = null;
    if ( is_array( $shipping_cost ) ) {

        $_T            = array_shift( $shipping_cost );
        $tServiceInfo  = $_T['name'];
        $shipping_cost = $_T['rate'];
    }

    $payment_form_html = "";
    $paymentModule     = modGetModuleObj( $payment_method["module_id"], PAYMENT_MODULE );
    if ( $paymentModule ) {

        $order   = array();
        $address = array();
        if ( $shippingAddressID != 0 ) {
            $payment_form_html = $paymentModule->payment_form_html( array( 'BillingAddressID' => $billingAddressID ) );
        } else {
            $payment_form_html = $paymentModule->payment_form_html( array(
                'countryID'  => $_SESSION['billing_countryID'],
                'zoneID'     => $_SESSION['billing_zoneID'],
                'first_name' => $_SESSION["billing_first_name"],
                'last_name'  => $_SESSION["billing_last_name"],
                'city'       => $_SESSION["billing_city"],
                'address'    => $_SESSION["billing_address"],
            ) );
        }
    }

    $resultInfo = array( "sumOrderContent" => $sumOrderContent,
        "discount"                            => $discount,
        "discount_percent"                    => $d,
        "discount_show"                       => show_price( $discount ),
        "pred_total_disc"                     => show_price(  ( $pred_total * (  ( 100 - $d ) / 100 ) ) ),
        "pred_total"                          => show_price( $pred_total ),
        "totalTax"                            => show_price( $tax ),
        "totalTaxUC"                          => $tax,
        "shipping_address"                    => $shipping_address,
        "billing_address"                     => $billing_address,
        "shipping_name"                       => $shipping_name,
        "payment_name"                        => $payment_name,
        "shipping_cost"                       => show_price( $shipping_cost ),
        "shipping_costUC"                     => $shipping_cost,
        "payment_form_html"                   => $payment_form_html,
        "total"                               => show_price( $total ),
        "totalUC"                             => $total,
        "payment_email_comments_text"         => $payment_email_comments_text,
        "shipping_email_comments_text"        => $shipping_email_comments_text,
        "orderContentCartProductsCount"       => count( $sumOrderContent ),
        "shippingServiceInfo"                 => $tServiceInfo );

    // debugfile( $resultInfo . "getOrderSummarize core/functions/order_functions.php:1679" );

    return $resultInfo;
}

// *****************************************************************************
// Purpose
// Inputs
// Remarks
// Returns
//                -1         access denied
//                0        success, access granted and load_counter has been incremented
//                1        access granted but count downloading is exceeded eproduct_download_times in PRODUCTS_TABLE
//                2        access granted but available days are exhausted to download product
//                3        it is not downloadable product
//                4        order is not ready
function ordAccessToLoadFile(
    $orderID,
    $productID,
    &$pathToProductFile,
    &$productFileShortName
) {
    $order   = ordGetOrder( $orderID );
    $product = GetProduct( $productID );

    if ( strlen( $product["eproduct_filename"] ) == 0 || !file_exists( "core/files/" . $product["eproduct_filename"] ) || $product["eproduct_filename"] == null ) {
        return 4;
    }

    if ( (int)$order["statusID"] != (int)ostGetCompletedOrderStatus() ) {
        return 3;
    }

    $orderContent = ordGetOrderContent( $orderID );
    foreach ( $orderContent as $item ) {
        if ( GetProductIdByItemId( $item["itemID"] ) == $productID ) {
            if ( $item["load_counter"] < $product["eproduct_download_times"] ||
                $product["eproduct_download_times"] == 0 ) {
                $date1 = dtGetParsedDateTime( $order["order_time_mysql"] ); //$order["order_time"]
                $date2 = dtGetParsedDateTime( get_current_time() );

                $countDay = _getDayBetweenDate( $date1, $date2 );

                if ( $countDay >= $product["eproduct_available_days"] ) {
                    return 2;
                }

                if ( $product["eproduct_download_times"] != 0 ) {
                    db_query( "update " . ORDERED_CARTS_TABLE .
                        " set load_counter=load_counter+1 " .
                        " where enabled=1 AND itemID=" . (int)$item["itemID"] . " AND orderID=" . (int)$orderID );
                }
                $pathToProductFile    = "core/files/" . $product["eproduct_filename"];
                $productFileShortName = $product["eproduct_filename"];
                return 0;
            } else {
                return 1;
            }

        }
    }
    return -1;
}

/*
orderID
customerID
order_time
customer_ip
shipping_type
payment_type
customers_comment
statusID
shipping_cost
order_discount
order_amount
currency_code
currency_value
customer_firstname
customer_lastname
customer_email
shippingServiceInfo
custlink
currency_round
shippmethod
paymethod
companyID
contractID
admin_comment
 */

function getNanoOrder( $orderID ) {
    global $PDO_connect;
    $result = array();
    $Table  = ORDERS_TABLE;

    $dataPDO = [
        "orderID"             => $orderID,
        "customerID"          => null,
        "order_time"          => null,
        "customer_ip"         => null,
        "shipping_type"       => null,
        "payment_type"        => null,
        "customers_comment"   => null,
        "statusID"            => null,
        "shipping_cost"       => null,
        "order_discount"      => null,
        "order_amount"        => null,
        "currency_code"       => null,
        "currency_value"      => null,
        "customer_firstname"  => null,
        "customer_lastname"   => null,
        "customer_email"      => null,
        "shippingServiceInfo" => null,
        "custlink"            => null,
        "currency_round"      => null,
        "shippmethod"         => null,
        "paymethod"           => null,
        "companyID"           => null,
        "contractID"          => null,
        "admin_comment"       => null,
    ];

    $bindings = array();
    // $dataPDO_Keys = array_keys( $dataPDO );
    $dataPDO_Keys = array( "orderID" );
    $i_keys       = array(
        "orderID",
        "customerID",
        "customer_ip",
        "statusID",
        "currency_round",
        "shippmethod",
        "paymethod",
        "companyID",
        "contractID",
    );

    for ( $ii = 0; $ii < count( $dataPDO_Keys ); $ii++ ) {
        $bindings[$ii]["key"]  = $dataPDO_Keys[$ii];
        $bindings[$ii]["val"]  = $dataPDO["$dataPDO_Keys[$ii]"];
        $bindings[$ii]["type"] = PDO::PARAM_STR;
        if ( in_array( $dataPDO_Keys[$ii], $i_keys ) ) {
            $bindings[$ii]["type"] = PDO::PARAM_INT;
        }
    }

    $sql_query =
        "
    SELECT

    `orderID`,
    `customerID`,
    `order_time`,
    `customer_ip`,
    `shipping_type`,
    `payment_type`,
    `customers_comment`,
    `statusID`,
    `shipping_cost`,
    `order_discount`,
    `order_amount`,
    `currency_code`,
    `currency_value`,
    `customer_firstname`,
    `customer_lastname`,
    `customer_email`,
    `shippingServiceInfo`,
    `custlink`,
    `currency_round`,
    `shippmethod`,
    `paymethod`,
    `companyID`,
    `contractID`,
    `admin_comment`

    FROM `{$Table}`
    WHERE `orderID`=:orderID;
    ";

    $res = array();
    $res = adminSSP::pdo_query_assoc( $PDO_connect, $bindings, $sql_query, 0 ); //

    $order = $res[0];

    // $order["shipping_address"] = rtrim( $order["shipping_address"] );
    // $order["billing_address"]  = rtrim( $order["billing_address"] );
    //additional reg fields
    $addregfields               = GetRegFieldsValuesByOrderID( $orderID );
    $order["reg_fields_values"] = $addregfields;

    $q_status_name = db_query( "SELECT status_name FROM " . ORDER_STATUES_TABLE . " WHERE statusID=" . (int)$order["statusID"] );
    $status_name   = db_fetch_row( $q_status_name );
    $status_name   = $status_name[0];

    if ( $order["statusID"] == ostGetCanceledStatusId() ) {
        $status_name = STRING_CANCELED_ORDER_STATUS;
    }

    // clear cost ( without shipping, discount, tax )
    $q1                = db_query( "SELECT Price, Quantity FROM " . ORDERED_CARTS_TABLE . " WHERE enabled=1 AND orderID=" . (int)$orderID );
    $clear_total_price = 0;
    while ( $row = db_fetch_row( $q1 ) ) {
        $clear_total_price += $row["Price"] * $row["Quantity"];
    }

    $currency_round = $order["currency_round"];

    $order["clear_total_priceToShow"]      = _formatPrice( roundf( $order["currency_value"] * $clear_total_price ), $currency_round ) . " " . $order["currency_code"];
    $order["order_discount_ToShow"]        = _formatPrice( roundf( $order["currency_value"] * $clear_total_price * (  ( 100 - $order["order_discount"] ) / 100 ) ), $currency_round ) . " " . $order["currency_code"];
    $order["shipping_costToShow"]          = _formatPrice( roundf( $order["currency_value"] * $order["shipping_cost"] ), $currency_round ) . " " . $order["currency_code"];
    $order["order_amountToShow_fromOrder"] = _formatPrice( roundf( $order["currency_value"] * $order["order_amount"] ), $currency_round ) . " " . $order["currency_code"];
    $order["order_amountToShow"]           = _formatPrice( roundf( $order["currency_value"] * ( $order["order_amount"] + $order["shipping_cost"] ) ) * (  ( 100 - $order["order_discount"] ) / 100 ), $currency_round ) . " " . $order["currency_code"];

    $order["order_time_mysql"] = $order["order_time"];
    $order["order_time"]       = format_datetime( $order["order_time"] );
    $order["status_name"]      = $status_name;

    $result = $order;

    // jlog( $order );
    return $result;
}

/*
ORDERS_TABLE
orderID
customerID
order_time
customer_ip
shipping_type
payment_type
customers_comment
statusID
shipping_cost
order_discount
order_amount
currency_code
currency_value
customer_firstname
customer_lastname
customer_email
shipping_firstname
shipping_lastname
shipping_country
shipping_state
shipping_city
shipping_address
billing_firstname
billing_lastname
billing_country
billing_state
billing_city
billing_address
cc_number
cc_holdername
cc_expires
cc_cvv
affiliateID
shippingServiceInfo
custlink
currency_round
shippmethod
paymethod
companyID
contractID
order_aka
customer_aka
admin_comment
 */

function getMyOrderContent(
    $orderID,
    $targetCurrencyRate,
    $hasVATIncluded = 0,
    $VAT_Rate = DEFAULT_VAT_RATE,
    $precision = 4,
    $targetCurrencyCode = "BYN"
) {

    $q_order              = db_query( "SELECT `order_discount`, `shipping_cost`, `currency_code`, `currency_value`, `customerID`, `order_time`, `currency_round`, `companyID`  FROM " . ORDERS_TABLE . " WHERE orderID=" . (int)$orderID );
    $OrderData            = db_fetch_row( $q_order );
    $orderDiscountPercent = (double)$OrderData["order_discount"];
    $orderShippingCost    = (double)$OrderData["shipping_cost"];

    $orderCurrencyCode  = $OrderData["currency_code"];
    $orderCurrencyValue = (double)$OrderData["currency_value"];
    $orderCurrencyRound = (double)$OrderData["currency_round"];

    $targetCurrencyRate = abs( $targetCurrencyRate );
    if ( $hasVATIncluded == 1 ) {
        $outCurrencyRate = (double)$targetCurrencyRate;
    } else {
        $outCurrencyRate = (double)$targetCurrencyRate * ( 100.0000 + $VAT_Rate ) / 100.0000;
    }

    $discountPercent = (double)$orderDiscountPercent;
    $outCurrencyRate = ( $discountPercent != 0 ) ? ( $outCurrencyRate * ( 100.00000 - $discountPercent ) / 100.00000 ) : $outCurrencyRate;

    $discountValue = -1 * (double)$orderShippingCost; // если не задаем требуемую скидку то берем ее из заказа

    $OrderedCarts = [];
    $sql          = "SELECT itemID, name, LEFT(name,64) as shortname, Price, Quantity, itemUnit, itemPriority FROM " . ORDERED_CARTS_TABLE . " WHERE enabled=1 AND orderID=" . (int)$orderID . " ORDER BY itemPriority DESC, itemID ASC";
    $q            = db_query( $sql );

    while ( $row = db_fetch_row( $q ) ) {

        $productID = GetProductIdByItemId( $row["itemID"] );

        $Product = getProductByProductID( $productID );

        $row["productID"] = $productID ?? null;

        $row["min_order_amount"] = $Product["min_order_amount"] ?? 1;
        $row["real_instock"]     = $Product["myInStock"]["qnt"] ?? 1;
        $row["fake_instock"]     = $Product["myInStock"]["qnt_to_show"] ?? 1;
        $row["in_stock_string"]  = $Product["myInStock"]["in_stock_string"] ?? "непонятно";

        $row["itemUnit"]     = $row["itemUnit"] ?? "шт.";
        $row["itemPriority"] = $row["itemPriority"] ?? 0;

        $name = $row["name"];

        $product_code_regex        = antGetProductCodeFromComplexName( $name );
        $row["product_code_regex"] = $product_code_regex;

        $model_regex = antGetMODELFromComplexName( $name );

        $srch                = array( $product_code_regex, "[]", "<>", "< >" );
        $name                = str_replace( $srch, "", $name );
        $row["pureItemName"] = str_replace( TRAIN_GO_MARK_DB, "", $name );
        $row["pureModel"]    = str_replace( $srch, "", $model_regex );

        $row["PriceToShow"] = _formatPrice( roundf( $orderCurrencyValue * $row["Price"] * $row["Quantity"] ), $orderCurrencyRound ) . " " . $orderCurrencyCode;
        $row["PriceOne"]    = _formatPrice( roundf( $orderCurrencyValue * $row["Price"] ), $orderCurrencyRound ) . " " . $orderCurrencyCode;
        $OrderedCarts[]     = $row;
    }

    # размазываем стоимость доставки по всем строчкам
    $totalItemsQuantity = 0;

    #$discountValue   = -  (double)$orderShippingCost;
    # если нету дополнительной скидки в заказе то при платной доставке discountValue будет не скидкой а наценкой за доставку
    if ( count( $OrderedCarts ) > 0 ) {
        # умножаем на -1 чтобы при платной доставке цена увеличивалась [$discountValue <-- ОТРИЦАТЕЛЬНАЯ]
        $discountValuePerRow = (double)( $discountValue / count( $OrderedCarts ) );
    } else {
        $discountValuePerRow = 0;
    }
    # END размазываем стоимость доставки по всем строчкам
    $maximalShippingDelayValue = 0;

    foreach ( $OrderedCarts as $key => $val ) {
        $itemDelay   = array();
        $shippingPay = 0; //

        $itemQuantity = $val["Quantity"];
        $totalItemsQuantity += $itemQuantity;

        $purePrice = (double)$OrderedCarts[$key]["Price"];
        $Price     = $purePrice;

        # размазываем стоимость доставки по всем товарам в строке
        if (  ( $discountValuePerRow != 0 ) && ( $itemQuantity > 0 ) ) {
            $shippingPay = -1 * (double)( $discountValuePerRow / $itemQuantity );
            $Price       = $purePrice + $shippingPay;
        }

        # END размазываем стоимость доставки по всем товарам в строке

        $outPrice         = number_format(  ( $Price * $outCurrencyRate / ( 100.0000 + $VAT_Rate ) * 100.0000 ), 4, ".", "" );
        $outPriceRounded  = number_format( $outPrice, 2, ".", "" );
        $outCost          = $outPriceRounded * $itemQuantity;
        $outVAT_Value     = number_format(  ( $outCost * $VAT_Rate / ( 100.0000 ) ), 4, ".", "" );
        $outCost_WITH_VAT = $outCost + $outVAT_Value;

        $uePrice         = number_format(  ( $Price / ( 100.0000 + $VAT_Rate ) * 100.0000 ), 4, ".", "" );
        $ueCost          = ( $uePrice * $itemQuantity );
        $ueVAT_Value     = number_format(  ( $ueCost * $VAT_Rate / ( 100.0000 ) ), 4, ".", "" );
        $ueCost_WITH_VAT = $ueCost + $ueVAT_Value;

        $OrderedCarts[$key]["itemPriority"] = $val["itemPriority"];
        $OrderedCarts[$key]["Quantity"]     = $itemQuantity;

        $OrderedCarts[$key]["purePrice"]   = _formatPrice( (double)$purePrice, 4 ); //purePrice
        $OrderedCarts[$key]["shippingPay"] = _formatPrice( (double)$shippingPay, 4 );
        $OrderedCarts[$key]["Price"]       = _formatPrice( (double)$Price, 4 );

        $OrderedCarts[$key]["outPrice"]         = _formatPrice( $outPrice, 2 );
        $OrderedCarts[$key]["outCost"]          = _formatPrice( $outCost, 2 );
        $OrderedCarts[$key]["outVAT_Value"]     = _formatPrice( $outVAT_Value, 2 );
        $OrderedCarts[$key]["outCost_WITH_VAT"] = _formatPrice( $outCost_WITH_VAT, 2 );

        $totalCost_WITHOUT_VAT += (double)strtr( $OrderedCarts[$key]["outCost"], array( "," => "", " " => "" ) );
        $total_VAT_Amount += (double)strtr( $OrderedCarts[$key]["outVAT_Value"], array( "," => "", " " => "" ) );
        $totalCost_WITH_VAT += (double)strtr( $OrderedCarts[$key]["outCost_WITH_VAT"], array( "," => "", " " => "" ) );

        $totalCost_WITHOUT_VAT = _formatPrice( $totalCost_WITHOUT_VAT, 2 );
        $total_VAT_Amount      = _formatPrice( $total_VAT_Amount, 2 );
        $totalCost_WITH_VAT    = _formatPrice( $totalCost_WITH_VAT, 2 );

        $OrderedCarts[$key]["delayString"] = $OrderedCarts[$key]["in_stock_string"];
        $OrderedCarts[$key]["delayValue"]  = $OrderedCarts[$key]["real_instock"];

        $delay_temp = ( $OrderedCarts[$key]["delayValue"] > 0 ) ? 0 : $OrderedCarts[$key]["delayValue"];
        if ( $delay_temp < $maximalShippingDelayValue ) {
            $maximalShippingDelayValue = $delay_temp;
        }

    }

    $maximalShippingDelayString = getMyInStock( $maximalShippingDelayValue )["in_stock_string"]; //максимальная задержка
    # Стоимость доставки из заказа
    $outDiscountValue = (double)$discountValue * $outCurrencyRate;
    # Проценты скидок
    $totalOrderDiscountPercent = round( (double)$orderDiscountPercent * $totalCost_WITH_VAT ) / 100.0000;
    $totalDiscountPercent      = round( (double)$discountPercent * $totalCost_WITH_VAT ) / 100.0000;

// countOrderedCarts
    $countOrderedCarts = count( $OrderedCarts );

    $res          = array();
    $totalStrings = array();
    $totalStrings = array(
        "total_VAT_Amount_STRING"      => SumToBYNRussian( $total_VAT_Amount ),
        "totalCost_WITHOUT_VAT_STRING" => SumToBYNRussian( $totalCost_WITHOUT_VAT ),
        "totalCost_WITH_VAT_STRING"    => SumToBYNRussian( $totalCost_WITH_VAT ),
        "totalItemsQuantity_STRING"    => num_to_russian_nominative( $totalItemsQuantity, 0 ),
        "countOrderedCarts_STRING"     => num_to_russian_nominative( $countOrderedCarts, 0 ),
    );
    if (  ( $targetCurrencyCode == "USD" ) or ( $targetCurrencyCode == "EUR" ) ) {
        $totalStrings["totalCost_WITHOUT_VAT_STRING"] = " по курсу Национального банка Республики Беларусь";
        $totalStrings["total_VAT_Amount_STRING"]      = " по курсу Национального банка Республики Беларусь";
        $totalStrings["totalCost_WITH_VAT_STRING"]    = " по курсу Национального банка Республики Беларусь";
    }

    if ( $outCurrencyRate != 0 ) {
        $usdTotalCost_WITHOUT_VAT = $totalCost_WITHOUT_VAT / $outCurrencyRate * ( 100.0000 + $VAT_Rate ) / 100.0000;
        $usdTotal_VAT_Amount      = $total_VAT_Amount / $outCurrencyRate * ( 100.0000 + $VAT_Rate ) / 100.0000;
        $usdTotalCost_WITH_VAT    = $totalCost_WITH_VAT / $outCurrencyRate * ( 100.0000 + $VAT_Rate ) / 100.0000;
    }

    $res = array(
        "orderID"                    => $orderID,
        "countOrderedCarts"          => $countOrderedCarts,
        "OrderedCarts"               => $OrderedCarts,

        "Total"                      => array(
            "totalCost_WITHOUT_VAT"    => $totalCost_WITHOUT_VAT,
            "totalVAT_Amount"          => $total_VAT_Amount,
            "totalCost_WITH_VAT"       => $totalCost_WITH_VAT,
            "totalItemsQuantity"       => $totalItemsQuantity,
            "usdTotal_VAT_Amount"      => _formatPrice( $usdTotal_VAT_Amount, 4 ),
            "usdTotalCost_WITHOUT_VAT" => _formatPrice( $usdTotalCost_WITHOUT_VAT, 4 ),
            "usdTotalCost_WITH_VAT"    => _formatPrice( $usdTotalCost_WITH_VAT, 4 ),
            "totalStrings"             => $totalStrings,
        ),

        "Discount"                   => array(

            "orderShippingCost"         => _formatPrice( $orderShippingCost, 4 ),
            "discountValue"             => _formatPrice( $discountValue, 4 ),
            "outDiscountValue"          => _formatPrice( $outDiscountValue, 4 ),

            "orderDiscountPercent"      => _formatPrice( $orderDiscountPercent, 4 ),
            "discountPercent"           => _formatPrice( $discountPercent, 4 ),

            "totalOrderDiscountPercent" => _formatPrice( $totalOrderDiscountPercent, 4 ),
            "totalDiscountPercent"      => _formatPrice( $totalDiscountPercent, 4 ),

        ),

        "targetCurrencyRate"         => _formatPrice( $targetCurrencyRate, 4 ),
        "hasVATIncluded"             => ( $hasVATIncluded == 1 ) ? 1 : 0,
        "VAT_Rate"                   => _formatPrice( $VAT_Rate, 4 ),
        "outCurrencyRate"            => _formatPrice( $outCurrencyRate, 4 ),
        "targetCurrencyCode"         => $targetCurrencyCode,
        "maximalShippingDelayValue"  => $maximalShippingDelayValue,
        "maximalShippingDelayString" => $maximalShippingDelayString,

        "orderCurrencyCode"          => $orderCurrencyCode,
        "orderCurrencyValue"         => $orderCurrencyValue,
        "orderCurrencyRound"         => $orderCurrencyRound,

    );
    return $res;
}

function newNanoInvoice(
    $orderID,
    $currencyValue,
    $companyID = 1
) {

    $sql_create_table = "
    CREATE TABLE `nano_invoices` (
      `invoice_time` timestamp NOT NULL COMMENT 'дата выставления счета',
      `invoiceID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Первичный ключ',
      `orderID` int(11) DEFAULT NULL COMMENT 'ID заказа',
      `module_id` int(11) DEFAULT '100' COMMENT 'ID модуля формирования счета',
      `sellerID` int(11) DEFAULT NULL COMMENT 'ID Продавца',
      `buyerID` int(11) DEFAULT NULL COMMENT 'ID Покупателя',
      `currency_rate` float NOT NULL DEFAULT '1' COMMENT 'Курс без НДС и без наценок, по которому выставляем счет',
      `contractID` int(11) DEFAULT NULL COMMENT 'ID текстов договоров',
      `purposeID` varchar(255) DEFAULT '0' COMMENT 'Цели покупки - кодЫ',
      `fundingID` varchar(255) DEFAULT '0' COMMENT 'Источники финансировния - код',
      `DeliveryType` int(2) NOT NULL DEFAULT '0' COMMENT 'Тип доставки - код: 0 -самовывоз, 1-доставка',
      `PaymentType` int(2) NOT NULL DEFAULT '0' COMMENT 'Оплата: 0-полная предоплата, 1-оплата по факту поставки',
      `actuality_termin` varchar(40) DEFAULT '3' COMMENT 'срок действия счета',
      `delivery_termin` varchar(40) DEFAULT '10' COMMENT 'срок_поставки товара',
      `payment_termin` varchar(40) DEFAULT '1' COMMENT 'срок полной оплаты, равен actuality_termin, в случае 100% -ой предоплаты',
      `payment_prepay` float DEFAULT '100' COMMENT 'Процент предоплаты',
      `deliveryFrom` varchar(255) DEFAULT NULL COMMENT 'Адрес погрузки',
      `deliveryTo` varchar(255) DEFAULT NULL COMMENT 'Адрес разгрузки',
      `requisites` text COMMENT 'Реквизиты',
      `director_nominative` text COMMENT 'Именительный падеж Руководитель',
      `director_genitive` text COMMENT ' Руководитель Родительный падеж',
      `director_reason` text COMMENT 'Действует на основании',
      PRIMARY KEY (`invoiceID`),
      KEY `orderID` (`orderID`),
      KEY `buyerID` (`buyerID`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ALTER TABLE `nano_invoices`
    AUTO_INCREMENT=5000;
    ";

    global $PDO_connect;

    $Table = NANO_INVOICES_TABLE;

    $dataPDO = [
        "orderID"          => $orderID,
        "module_id"        => 100,
        "sellerID"         => 1,
        "buyerID"          => $companyID,
        "currency_rate"    => 1,
        "contractID"       => 100,
        "purposeID"        => 0,
        "fundingID"        => 0,
        "DeliveryType"     => 0,
        "PaymentType"      => 0,
        "actuality_termin" => "1",
        "delivery_termin"  => "10",
        "payment_termin"   => "1",
        "payment_prepay"   => 100.00,
        "deliveryFrom"     => "",
        "deliveryTo"       => "",
    ];

    $bindings     = array();
    $dataPDO_Keys = array_keys( $dataPDO );
    $i_keys       = array(
        "module_id",
        "invoiceID",
        "orderID",
        "sellerID",
        "buyerID",
        "contractID",
        "purposeID",
        "fundingID",
        "CID",
        "DeliveryType",
        "PaymentType",
    );

    for ( $ii = 0; $ii < count( $dataPDO_Keys ); $ii++ ) {
        $bindings[$ii]["key"]  = $dataPDO_Keys[$ii];
        $bindings[$ii]["val"]  = $dataPDO["$dataPDO_Keys[$ii]"];
        $bindings[$ii]["type"] = PDO::PARAM_STR;
        if ( in_array( $dataPDO_Keys[$ii], $i_keys ) ) {
            $bindings[$ii]["type"] = PDO::PARAM_INT;
        }
    }

    $sql_query =
        "
INSERT INTO `{$Table}` SET

`invoice_time` = NOW(),
`orderID` = :orderID,
`module_id` = :module_id,
`sellerID` = :sellerID,
`buyerID` = :buyerID,
`currency_rate` = :currency_rate,
`contractID` = :contractID,
`purposeID` = :purposeID,
`fundingID` = :fundingID,
`DeliveryType` = :DeliveryType,
`PaymentType` = :PaymentType,
`actuality_termin` = :actuality_termin,
`delivery_termin` = :delivery_termin,
`payment_termin` = :payment_termin,
`payment_prepay` = :payment_prepay,
`deliveryFrom` = :deliveryFrom,
`deliveryTo` = :deliveryTo;
";

    $res = array();
    $res = adminSSP::pdo_query_assoc( $PDO_connect, $bindings, $sql_query, 2 ); //

    $lastID = (int)$res;
    return $lastID;

}

function getNanoInvoice( $invoiceID ) {
    global $PDO_connect;
    $TableInvoices = NANO_INVOICES_TABLE;
    $sql_query     = "SELECT * FROM `$TableInvoices` WHERE `invoiceID`=:invoiceID;";
    $bindings      = array(
        [
            "key"  => "invoiceID",
            "val"  => $invoiceID,
            "type" => PDO::PARAM_INT,
        ],
    );

    $res = array();
    $res = adminSSP::pdo_query_assoc( $PDO_connect, $bindings, $sql_query, 0 ); //

    $result = $res[0];
    return $result;
}

function get_ff4(
    $dt,
    $orderID = 1000,
    $invoiceID = null

) {

    $res        = array();
    $tmp        = getdate( strtotime( $dt ) );
    $result_ff4 = str_pad( $tmp["mon"], 2, '0', STR_PAD_LEFT ) . "" . str_pad( $tmp["mday"], 2, '0', STR_PAD_LEFT ) . "-" . zeroFill( $orderID );

    if ( $invoiceID > 0 ) {
        $ff4_full = $result_ff4 . "@" . zeroFill( $invoiceID );
    }

    // $res["time_mysql"] = FormatAsSQL( $dt, 1 );
    // $res["time_RU"]    = FormatAsRUS( $dt, 0 );
    $res["ff4"]      = $result_ff4;
    $res["ff4_full"] = $ff4_full;
    return $res;
}

function chooseLastInvoices(
    $orderID,
    $LIMIT = 1
) {

    global $PDO_connect;
    $TableInvoices = NANO_INVOICES_TABLE;
    $sql_query     = "SELECT * FROM `$TableInvoices` WHERE `orderID`=:orderID ORDER BY `invoiceID` DESC LIMIT $LIMIT;";

    $bindings = array(
        [
            "key"  => "orderID",
            "val"  => $orderID,
            "type" => PDO::PARAM_INT,
        ],
    );

    $res = array();
    $res = adminSSP::pdo_query_assoc( $PDO_connect, $bindings, $sql_query, 0 ); //

    if ( !$res ) {

        $result = [];

    } else {

        $result = $res;

    }

    $result[0]["antaNT64"] = "chooseLastInvoices( {$res[0]['invoiceID']} ) ";

    // jlog( $result );
    return $result;
}

?>