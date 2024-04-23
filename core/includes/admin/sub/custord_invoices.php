<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2020-09-01       #
#          http://nixminsk.by            #
##########################################

if ( !strcmp( $sub, "invoices" ) ) {
    if ( CONF_BACKEND_SAFEMODE != 1 && ( !isset( $_SESSION["log"] ) || !in_array( 16, $relaccess ) ) ) //unauthorized
    {
        $smarty->assign( "admin_sub_dpt", "error_forbidden.tpl.html" );
    } else {

        $EDIT_MODE = (int)( isset( $_GET["edit"] ) ) && ( (int)$_GET["edit"] > 0 );
        $smarty->assign( "EDIT_MODE", $EDIT_MODE );

        if ( $EDIT_MODE == 0 ) {
            $smarty->assign( "NOTDataTables", false );

            if ( isset( $_GET["get_filter_item"] ) ) {
                $get_filter_item = $_GET["get_filter_item"];
                $smarty->assign( "get_filter_item", $get_filter_item );
            }

            if ( isset( $_GET["get_filter_orderID"] ) ) {
                $get_filter_orderID = $_GET["get_filter_orderID"];
                $smarty->assign( "get_filter_orderID", $get_filter_orderID );
            }

            if ( isset( $_GET["get_filter_company"] ) ) {
                $get_filter_company = $_GET["get_filter_company"];
                $smarty->assign( "get_filter_company", $get_filter_company );
            }

            if ( isset( $_GET["get_start_date"] ) ) {
                $get_start_date = $_GET["get_start_date"];
                $smarty->assign( "get_start_date", $get_start_date );
            } else {
                $start_date = get_daysbefore( 180 );
                $smarty->assign( "get_start_date", $start_date );

            }

            if ( isset( $_GET["get_finish_date"] ) ) {
                $get_finish_date = $_GET["get_finish_date"];
                $smarty->assign( "get_finish_date", $get_finish_date );
            } else {
                $finish_date = get_daysbefore( -1 );
                $smarty->assign( "get_finish_date", $finish_date );
            }

            // echo "get_start_date :: {$get_start_date}";

            # ТАБЛИЦА ИНВОЙСОВ
            require "core/classes/class.SSP.php";
            // DB table to use
            $table = INVOICES_TABLE;
            // Table's primary key
            $primaryKey = "invoiceID";
            // SQL server connection information
            $sql_connect = array(
                "user" => DB_USER,
                "pass" => DB_PASS,
                "db"   => DB_NAME,
                "host" => DB_HOST,
            );

            include_once "core/includes/admin/sub/custord_invoices_DataTable.php";
            $smarty->assign( "DT_columnsJSON", json_encode( $DT_columns ) );
            $smarty->assign( "DT_columnDefsJSON", json_encode( $DT_columnDefs ) );

            # инициализируем таблицу Таблицу
            if (  ( isset( $_GET["init"] ) ) and ( !isset( $_GET["edit_mode"] ) ) ) {
                {

                    if ( isset( $_POST['start_date'] ) || isset( $_POST['end_date'] ) ) {
                        $start_date = ( isset( $_POST['start_date'] ) ) ? $_POST['start_date'] : get_daysbefore( 180 );
                        $end_date   = ( isset( $_POST['end_date'] ) )? $_POST['end_date'] : get_daysbefore( -1 );
                        } else {
                            $start_date  = get_daysbefore( 180 );
                            $finish_date = get_daysbefore( -1 );
                            // $whereResult = "";
                        }
                        $whereResult = " ( `invoice_time` >= '{$start_date}' AND `invoice_time` <= '{$end_date}' ) ";

                        if ( isset( $_POST["no_time_filter"] ) ) {
                            $whereResult = "";
                        }

                        if ( isset( $_POST['filter_orderID'] ) && ( $_POST['filter_orderID'] != "" ) ) {
                            $filter_orderID = trim( $_POST["filter_orderID"] );
                            if ( (int)$filter_orderID > 0 ) {
                                ( $whereResult == "" )
                                ? $whereResult .= " ( `orderID` LIKE '%" . $filter_orderID . "%' ) "
                                : $whereResult .= " AND ( `orderID` LIKE '%" . $filter_orderID . "%' ) ";

                                $sql_aka  = " ( SELECT `orderID` FROM `" . ORDERS_TABLE . "` WHERE `order_aka` LIKE '%" . $filter_orderID . "%'  )";
                                $sql_aka  = str_replace( "\r\n", " ", $sql_aka );
                                $q        = db_query( $sql_aka );
                                $data_aka = array();
                                while ( $row = db_fetch_assoc( $q ) ) {
                                    $data_aka[] = (int)$row["orderID"];
                                }
                                if ( is_array( $data_aka ) && count( $data_aka ) > 0 ) {
                                    $orders_list = implode( ",", $data_aka );
                                    $whereResult .= " OR ( `orderID` IN ({$orders_list}) ) ";
                                }
                            }
                            ## ( `order_aka` LIKE '%" . $filter_orderID . "%' )
                        }

                        if ( isset( $_POST['filter_item'] ) && ( $_POST['filter_item'] != "" ) ) {
                            $filter_item = trim( $_POST["filter_item"] );
                            if ( $filter_item ) {
                                $sql_item  = " ( SELECT `orderID` FROM `" . ORDERED_CARTS_TABLE . "` WHERE `name` LIKE '%" . $filter_item . "%'  )";
                                $sql_item  = str_replace( "\r\n", " ", $sql_item );
                                $q         = db_query( $sql_item );
                                $data_item = array();
                                while ( $row = db_fetch_assoc( $q ) ) {
                                    $data_item[] = (int)$row["orderID"];
                                }
                                if ( is_array( $data_item ) && count( $data_item ) > 0 ) {
                                    $orders_list = implode( ",", $data_item );
                                    ( $whereResult == "" )
                                    ? $whereResult .= " ( `orderID` IN ({$orders_list}) ) "
                                    : $whereResult .= " AND ( `orderID` IN ({$orders_list}) ) ";
                                }
                            }
                        }

                        if ( isset( $_POST['filter_company'] ) && ( $_POST['filter_company'] != "" ) ) {
                            $filter_company = trim( $_POST["filter_company"] );
                            if ( $filter_company ) {
                                $sql_company  = " ( SELECT `companyID` FROM `" . COMPANIES_TABLE . "` WHERE `company_unp` LIKE '%" . $filter_company . "%' OR `company_name` LIKE '%" . $filter_company . "%' )";
                                $sql_company  = str_replace( "\r\n", " ", $sql_company );
                                $q            = db_query( $sql_company );
                                $data_company = array();
                                while ( $row = db_fetch_assoc( $q ) ) {
                                    $data_company[] = (int)$row["companyID"];
                                }
                                if ( is_array( $data_company ) && count( $data_company ) > 0 ) {
                                    $company_list = implode( ",", $data_company );
                                    ( $whereResult == "" )
                                    ? $whereResult .= " ( `buyerID` IN ({$company_list}) ) "
                                    : $whereResult .= " AND ( `buyerID` IN ({$company_list}) ) ";
                                }
                            }
                        }

                        if ( isset( $_POST['show_all'] ) ) {
                            $whereResult = "";
                        }

                        // consolelog("DT_columnDefs:");
                        // consolelog($DT_columnDefs);
                        // consolelog("DT_columns:");
                        // consolelog($DT_columns);

                        $res = array();
                                                                                                                              // $res = SSP::complex( $_POST, $sql_connect, $table, $primaryKey, $DT_columns, null, $whereResult . "  LIMIT 1000" ); //
                        $res = SSP::complex( $_POST, $sql_connect, $table, $primaryKey, $DT_columns, $whereResult, null ); //
                        echo json_encode( $res );
                        die();
                    } ###init###
                }

            } elseif ( $EDIT_MODE > 0 ) {
                # РЕДАКТИРОВАНИЕ ИНВОЙСА
                $smarty->assign( "NOTDataTables", true );
                $editID = (int)$_GET["edit"];
                $smarty->assign( "editID", $editID );

                $Invoice = array();
                $Invoice = invoiceGet( $editID );

                if ( is_array( $Invoice ) && count( $Invoice ) ) {

                    $Order = array();
                    $Order = _getOrderById( $Invoice["orderID"] );

                    $ModuleConfig = array();
                    $ModuleConfig = modGetModuleConfig( $Invoice["module_id"] );

                    $PaymentModule = modGetModuleObj( $Invoice["module_id"], PAYMENT_MODULE );

                    if ( !$PaymentModule ) {
                        $PaymentModule = modGetModuleObj( DEFAULT_PAYMENT_MODULE_ID, PAYMENT_MODULE );
                    }

                    $currentCurrencyID = $PaymentModule->getModuleSettingValue( PM_CURRENCY ); //(int)$Invoice["CID"];//module
                    $currentCurrency   = currGetCurrencyByID( $currentCurrencyID );            //module

                    require "core/classes/class.ControlModel.php";
                    require "core/includes/admin/sub/custord_invoices_CONTROLs_Descriptions.php";


                    $default_invoice_href = urldecode( getNewPdfLink( $Invoice["invoiceID"] ) );
                    $smarty->assign( "Invoice", $Invoice );
                    $smarty->assign( "curreNT_invoiceID", $Invoice["invoiceID"] );
                    $smarty->assign( "curreNT_orderID", $Invoice["orderID"] );
                    $smarty->assign( "currency_helpblock", $currency_helpblock );

                    $smarty->assign( "default_invoice_href", $default_invoice_href );
                    $smarty->assign( "default_invoiceTable_href", "?dpt=" . $dpt . "&sub=" . $sub );
                    $smarty->assign( "default_invoiceEdit_href", "?dpt=" . $dpt . "&sub=" . $sub . "&edit=" . $editID );
                    $smarty->assign( "default_orderEdit_href", "?dpt=" . $dpt . "&sub=new_orders&orders_detailed=yes&orderID=" . $Invoice["orderID"] );
                    // $smarty->assign( "default_AddInvoice_href", $default_invoice_href );
                }

                # РЕДАКТИРОВАНИЕ ИНВОЙСА
            }

            # Ajax Operations
            if ( isset( $_POST["operation"] ) ) {

                // console( $_POST );
                if (
                    (  ( !isset( $_GET["table_mode"] ) ) and ( isset( $_GET["edit_mode"] ) ) ) or
                    (  ( isset( $_GET["table_mode"] ) ) and ( !isset( $_GET["edit_mode"] ) ) ) or 1

                ) {
                    $result          = array();
                    $result["ERROR"] = "UNKNOWN ERROR";

                    unset( $result["redirect"] );
                    unset( $result["reload_page"] );

                    $operation = $_POST["operation"];

                    $invoiceIDs    = $_POST["invoiceIDs"];
                    $orderIDs      = $_POST["orderIDs"];
                    $rows_selected = $_POST["rows_selected"];

                    $start_timestamp = $_POST["now"];

                    $DBtable      = $_POST["DBtable"];
                    $primaryKey   = $_POST["primaryKey"];
                    $fieldname    = $_POST["fieldname"];
                    $editID       = $_POST["editID"];
                    $where_clause = $_POST["where_clause"];
                    $newData      = $_POST["newData"];

                    switch ( $operation ) {

                        case "simpleFieldUpdate":

                            $_orderID = ( $fieldname != "order_aka" )
                            ? (int)dbGetFieldData( INVOICES_TABLE, "orderID", " invoiceID = '{$editID}'" )
                            : $editID;

                            if ( $fieldname == "statusID" ) {
                                ostSetOrderStatusToOrder(
                                    $orderID = $editID,
                                    $statusID = $newData,
                                    $comment = 'simpleFieldUpdate',
                                    $notify = 0
                                );
                                unset( $result["ERROR"] );
                                $result["redirect"] = "?dpt=custord&sub=new_orders&orders_detailed=yes&orderID=" . $editID;

                            } elseif ( $fieldname != "statusID" and ostGetActiveStatusById( $_orderID ) or in_array( $fieldname, array( "invoice_hidden", "invoice_description", "stampsBYTE", "showToUser" ) ) ) {
                                unset( $result["redirect"] );
                                unset( $result["reload_page"] );

                                $sql = "UPDATE `{$DBtable}` SET `{$fieldname}` = '" . $newData . "' WHERE `{$primaryKey}`=" . $editID;
                                if ( $fieldname == "order_aka" ) {
                                    $sql = "UPDATE `" . ORDERS_TABLE . "` SET `{$fieldname}` = '" . $newData . "' WHERE `{$primaryKey}`=" . $editID;
                                }

                                $res = db_query( $sql ); //invoiceIDs
                                if ( $res["mysql_affected_rows"] > 0 ) {
                                    unset( $result["ERROR"] );
                                    $result["mysql_affected_rows"] = $res["mysql_affected_rows"];

                                    if ( $fieldname == "module_id" ) {
                                        $_Conf = modGetModuleObj( $newData, PAYMENT_MODULE );
                                        if ( !$_Conf->is_installed() ) {
                                            $_Conf = modGetModuleObj( DEFAULT_PAYMENT_MODULE_ID, PAYMENT_MODULE );
                                        }
                                        $new_curr     = currGetCurrencyByID( $_Conf->getModuleSettingValue( PM_CURRENCY ) );
                                        $sql_callback = " UPDATE `{$DBtable}` SET
                                        `CID` = '{$_Conf->getModuleSettingValue( PM_CURRENCY )}',
                                        `currency_rate` = '{$new_curr["currency_value"]}',
                                        `sellerID` = '{$_Conf->getModuleSettingValue( PM_SELLER )}',
                                        `contractID` = '{$_Conf->getModuleSettingValue( PM_DEFAULT_CONTRACT_ID )}'
                                     WHERE `{$primaryKey}`= '{$editID}';";

                                        $res_callback = db_multiquery( $sql_callback ); //invoiceIDs
                                    }

                                } elseif ( (int)$res["mysql_affected_rows"] == 0 ) {
                                    unset( $result["ERROR"] );
                                    $result["ERROR"] = "Ничего не изменено";
                                } else {
                                    $result["ERROR"] = "`{$fieldname}` simpleFieldUpdate ERROR mysql_affected_rows:" . (int)$result["mysql_affected_rows"];
                                }
                                $result["sql"] = $sql;

                                if ( $fieldname == "module_id" || $fieldname == "currency_rate" || $fieldname == "invoice_subdiscount" ) {
                                    $result["reload_page"] = 1;
                                }

                                if ( $fieldname == "module_id" || $fieldname == "buyerID" ) {

                                    $new_sql    = "SELECT module_id,invoiceID,orderID,buyerID FROM `{$DBtable}` " . " WHERE `{$primaryKey}`=" . $editID;
                                    $NewInvoice = db_fetch_assoc( db_query( $new_sql ) );


                                    $result["default_invoice_href"] = urldecode( getNewPdfLink( $NewInvoice["invoiceID"] ) );
                                }

                                if ( $fieldname == "order_aka" ) {
                                    unset( $result["reload_page"] );
                                    $result["redirect"] = "?dpt=custord&sub=new_orders&orders_detailed=yes&orderID=" . $editID;
                                }

                            } else {

                                $result["ERROR"] = "Для изменения Заказ должен иметь статус: <b>" . STRING_ACTIVE_ORDER_STATUS . "</b> Ничего не изменено";
                                unset( $result["redirect"] );
                                $result["reload_page"] = 1;
                            }
                            break;

                        case "AddInvoice":
                            {

                                $invoice_data                        = invoiceGet( $invoiceIDs[0] );
                                $invoice_data["invoice_description"] = "CLONE OF " . $invoiceIDs[0];
                                $invoice_data["invoice_time"]        = get_current_time();

                                $settings_constant_name = "PM_CONTRACT_TYPE_" . $invoice_data["module_id"];
                                $CONTRACT_TYPE          = _getSettingOptionValue( $settings_constant_name );
                                if ( $CONTRACT_TYPE == 1 ) {
                                    $invoice_data["contract_time"] = $invoice_data["contract_time"]; // если договор с продлением , то дата контракта-договора равна  $invoice_data["contract_time"];
                                } else {
                                    $invoice_data["contract_time"] = $invoice_data["invoice_time"]; // если договор однократный, то дата контракта-договора от текущего момента
                                }

                                $res = invoiceAddNewInvoice( $invoice_data ); //invoiceIDs

                                if ( $res > 0 ) {
                                    unset( $result["ERROR"] );
                                    $result["new_invoiceID"] = (int)$res;
                                } else {
                                    $result["ERROR"] = "AddInvoice ERROR";
                                }

                                $result["goto_page"] = (int)$res;

                            }
                            break;

                        case "NewOrder":
                            {
                                $NO_CONTENT_COPY                     = isset( $_POST["params"] ) && ( $_POST["params"] == "NO_CONTENT_COPY" );
                                $invoice_data                        = invoiceGet( $invoiceIDs[0] );
                                $invoice_data["invoice_description"] = "NewOrder for " . $invoiceIDs[0];
                                $invoice_data["invoice_time"]        = get_current_time();

                                $settings_constant_name = "PM_CONTRACT_TYPE_" . $invoice_data["module_id"];
                                $CONTRACT_TYPE          = _getSettingOptionValue( $settings_constant_name );
                                if ( $CONTRACT_TYPE == 1 ) {
                                    $invoice_data["contract_time"] = $invoice_data["contract_time"]; // если договор с продлением , то дата контракта-договора равна  $invoice_data["contract_time"];
                                } else {
                                    $invoice_data["contract_time"] = $invoice_data["invoice_time"]; // если договор однократный, то дата контракта-договора от текущего момента
                                }

                                $order_data = _getOrderById( $invoice_data["orderID"] );

                                if ( is_array( $order_data ) && count( $order_data ) ) {

                                    $newOrder = array();
                                    $newOrder = array(
                                        // "orderID"             => $order_data["orderID"],
                                         "customerID"         => $order_data["customerID"],
                                        "order_time"         => get_current_time(),
                                        "customer_ip"        => $order_data["customer_ip"],
                                                                   // "shipping_type"       => $order_data["shipping_type"],
                                                                    // "payment_type"        => $order_data["payment_type"],
                                                                    // "customers_comment"   => $order_data["customers_comment"],
                                         "statusID"           => 3, // "Активный"
                                         "shipping_cost"      => $order_data["shipping_cost"],
                                        "order_discount"     => $order_data["order_discount"],
                                        "order_amount"       => $order_data["order_amount"],
                                        "currency_code"      => $order_data["currency_code"],
                                        "currency_value"     => $order_data["currency_value"],
                                        "customer_firstname" => $order_data["customer_firstname"],
                                        "customer_lastname"  => $order_data["customer_lastname"],
                                        "customer_email"     => $order_data["customer_email"],
                                        // "shipping_firstname"  => $order_data["shipping_firstname"],
                                         // "shipping_lastname"   => $order_data["shipping_lastname"],
                                         // "shipping_country"    => $order_data["shipping_country"],
                                         // "shipping_state"      => $order_data["shipping_state"],
                                         // "shipping_city"       => $order_data["shipping_city"],
                                         // "shipping_address"    => $order_data["shipping_address"],
                                         // "billing_firstname"   => $order_data["billing_firstname"],
                                         // "billing_lastname"    => $order_data["billing_lastname"],
                                         // "billing_country"     => $order_data["billing_country"],
                                         // "billing_state"       => $order_data["billing_state"],
                                         // "billing_city"        => $order_data["billing_city"],
                                         // "billing_address"     => $order_data["billing_address"],
                                         // "cc_number"           => $order_data["cc_number"],
                                         // "cc_holdername"       => $order_data["cc_holdername"],
                                         // "cc_expires"          => $order_data["cc_expires"],
                                         // "cc_cvv"              => $order_data["cc_cvv"],
                                         // "shippingServiceInfo" => $order_data["shippingServiceInfo"],
                                         "currency_round"     => $order_data["currency_round"],
                                        "shippmethod"        => $order_data["shippmethod"],
                                        "paymethod"          => $order_data["paymethod"],
                                        "companyID"          => $invoice_data["buyerID"],    //buyerID
                                         "contractID"         => $invoice_data["contractID"], //contractID
                                         "order_aka"          => 0,
                                        "customer_aka"       => 0,
                                        "admin_comment"      => $order_data["admin_comment"],
                                    );

                                    $fieldslist    = "";
                                    $values_string = "";
                                    $fieldslist    = array2apostrophes( array_keys( $newOrder ), "`" );
                                    $values_string = array2apostrophes( array_values( $newOrder ), "'", "ToText" );

                                    $sql_insert_orders = "INSERT INTO `" . ORDERS_TABLE . "` ({$fieldslist}) VAlUES ({$values_string})";
                                    $sql_insert_orders = trim( str_replace( "\r\n", " ", $sql_insert_orders ) );

                                    $resOrder    = db_query( $sql_insert_orders );
                                    $new_orderID = (int)db_insert_id();

                                    if ( $new_orderID > 0 ) {
                                        unset( $result["ERROR"] );
                                        $result["new_orderID"] = (int)$new_orderID;
                                    } else {
                                        $result["ERROR"] = "NEW ORDER ERROR";
                                    }

                                    if ( $new_orderID > 0 ) {

                                        if ( !$NO_CONTENT_COPY ) {

                                            $new_content    = array();
                                            $q_orderContent = db_query( "SELECT  name, Price, Quantity, tax, load_counter, itemID, itemUnit, itemPriority, enabled FROM " .
                                                ORDERED_CARTS_TABLE . " WHERE enabled=1 AND orderID=" . (int)$invoice_data["orderID"] . " ORDER BY itemPriority DESC, itemID ASC;" );
                                            $sql         = "SELECT MAX(`itemID`) FROM `" . ORDERED_CARTS_TABLE . "`;";
                                            $q           = db_query( $sql );
                                            $row         = db_fetch_row( $q );
                                            $LAST_itemID = (int)$row[0];

                                            while ( $orderContentItem = db_fetch_assoc( $q_orderContent ) ) {
                                                $LAST_itemID++;
                                                $orderContentItem["orderID"] = "{$new_orderID}"; //$new_orderID;
                                                unset( $orderContentItem["itemID"] );

                                                $orderContentItem["itemID"] = "{$LAST_itemID}";
                                                $new_content[]              = $orderContentItem;
                                            }
                                            foreach ( $new_content as $new_item ) {
                                                $fieldslist = "";
                                                // $values_string    = "";
                                                $new_item["name"] = xEscSQL( $new_item["name"] );
                                                $fieldslist       = array2apostrophes( array_keys( $new_item ), "`" );
                                                $values_string    = array2apostrophes( array_values( $new_item ), "'", "ToText" );

                                                $sql_insert_item = "INSERT INTO `" . ORDERED_CARTS_TABLE . "` ({$fieldslist}) VAlUES ({$values_string})";
                                                $sql_insert_item = trim( str_replace( "\r\n", " ", $sql_insert_item ) );
                                                db_query( $sql_insert_item );
                                            }
                                        }

                                        unset( $invoice_data["invoiceID"] );
                                        $invoice_data["orderID"] = "{$new_orderID}"; //$new_orderID;

                                        $fieldslist    = "";
                                        $values_string = "";
                                        $fieldslist    = array2apostrophes( array_keys( $invoice_data ), "`" );
                                        $values_string = array2apostrophes( array_values( $invoice_data ), "'", "ToText" );

                                        $sql_insert_invoice = "INSERT INTO `" . INVOICES_TABLE . "` ({$fieldslist}) VAlUES ({$values_string})";
                                        $sql_insert_invoice = trim( str_replace( "\r\n", " ", $sql_insert_invoice ) );
                                        $resInvoice         = db_query( $sql_insert_invoice );
                                        $new_invoiceID      = db_insert_id();
                                    }

                                }

                                if ( $new_invoiceID > 0 ) {
                                    unset( $result["ERROR"] );
                                    $result["new_invoiceID"] = (int)$new_invoiceID;
                                } else {
                                    $result["ERROR"] = "NEW INVOICE ERROR";
                                }

                                $result["params"]   = $_POST["params"];
                                $result["redirect"] = "?dpt=custord&sub=new_orders&orders_detailed=yes&orderID=" . $new_orderID;

                            }
                            break;
                        case 'RemoveInvoices':
                            {
                                $res = array();
                                if ( $invoiceIDs ) {
                                    foreach ( $invoiceIDs as $ID ) {
                                        $res[] = invoiceDeleteInvoice( $ID ); //invoiceIDs
                                    }
                                }

                                if ( count( $res ) == count( $rows_selected ) ) {
                                    unset( $result["ERROR"] );
                                    $result["deleted_invoiceIDs"] = implode( ",", $invoiceIDs );
                                } else {
                                    $result["ERROR"] = "RemoveInvoices ERROR";
                                }

                                $result["redirect"] = "?dpt=custord&sub=invoices";

                            }
                            break;

                        default:
                            # code...
                            break;

                    } ## switch ( $operation )
                    $result["timestamp"] = get_current_time();

                    // $result["timestamp_formatted"] = FormatAsRUS( $result["timestamp"], $ShowTime = 7, $YearMark = "г." )."<i class=\"fas fa-sync-alt\"></i>";
                    // ddd($result);
                    echo json_encode( $result );

                }
                die();
            } ## if ( isset( $_POST["operation"] ) )

            //set sub-department template
            $smarty->assign( "admin_sub_dpt", "custord_invoices.tpl.html" );

        }
    }

    ?>