<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2019-09-18       #
#          http://nixby.pro              #
##########################################
ini_set( 'display_errors', 1 ); // сообщения с ошибками будут показываться
error_reporting( E_ALL );       // E_ALL - отображаем ВСЕ ошибки

if ( !strcmp( $sub, "tree" ) ) {
    if ( CONF_BACKEND_SAFEMODE != 1 && ( !isset( $_SESSION["log"] ) || !in_array( 4, $relaccess ) ) ) //unauthorized
    {
        $smarty->assign( "admin_sub_dpt", "error_forbidden.tpl.html" );
    } else {

        //calculate how many products are there in root category
        $q   = db_query( "SELECT count(*) FROM " . PRODUCTS_TABLE . " WHERE categoryID=1" );
        $cnt = db_fetch_row( $q );
        $smarty->assign( "products_in_root_category", $cnt[0] );

        #update button pressed -- НАДО СДЕЛАТЬ Кнопку
        if ( isset( $_GET["force_update_gc_value"] ) ) {
            @set_time_limit( 60 * 10 );

            Redirect( ADMIN_FILE . "?dpt=catalog&sub=tree&toastr_message_onload=force_update_gc_value-Дерево категорий Обновлено" );
            die();
        }
        $vendorID = 1;
        $status = groupStatusByVendor( $vendorID );
        $smarty->assign( "status", $status );

        if ( isset( $_GET["initialization"] ) && isset( $_GET["instance"] ) ) {
            $initialization  = $_GET["initialization"];
            $instance        = $_GET["instance"];
            // $result          = null;
            $operationResult = false;

            try {
                switch ( $instance ) {
                    case "main":
                        $rootID = 1;
                        if ( isset( $_GET["id"] ) && $_GET["id"] != "#" ) {
                            $rootID = (int)$_GET["id"];
                        } else {
                            $rootID = 1;
                        }

                        $jstreeCategories = array();
                        // update_psCount( 1 );
                        $jstreeCategories = jstreeGetMainTreeData( $rootID, $updateGCV = 1 );
                        $operationResult  = convert_Categories_to_jstree( $jstreeCategories );
                        // $operationResult  = convert_Categories_to_jstree( $jstreeCategories );

                        // consolelog($operationResult);
                        break;

                    case "aux":
                        $jstreeGroups = array();
                        $limit        = 2000 + 128 + 1; //
                        # function jstreeSelectGroupsAll( $vendorID = 1, $is_published_Group = 0, $is_enabled = 1, $FILTER_PUBLISHED_PRODUCTS = 1 )
                        // groupUpdateCountsByVendor( 1 );
                        $jstreeGroups    = jstreeSelectGroupsAll( $vendorID, $limit ); //
                        $operationResult = convert_Groups_to_jstree( $jstreeGroups );
                        break;

                    case "test":
                        $operationResult = jstreeGenerateDataByRefernceItems( $_GET["table"] );
                        // $operationResult = convert_Groups_to_jstree(groupGetCategoryCompactCList( 1 ));
                        break;
                    default:
                        // $operationResult = 'UNKNOWN OPERATION :: ' . $operation;
                        $operationResult = null;
                        break;
                } // switch

                $ajaxResponse = $operationResult;
                // header_status( 200 );
                ( is_array ($operationResult) )?header_status( 200 ):header_status( 405 );
                header( "Content-Type: application/json; charset=utf-8" );
                echo json_encode( $ajaxResponse );

            } catch ( Exception $e ) {
                // Возвращаем клиенту ответ с ошибкой
                header_status( 500 );
                header( "Content-Type: application/json; charset=utf-8" );
                echo json_encode( array(
                    "code"            => "error",
                    "toastr"          => "error",
                    "message"         => $e->getMessage(),
                    "operationResult" => null,
                ) );
            }
            die();
        }

        if ( isset( $_GET["operation"] ) ) {
            $operation = $_GET["operation"];
            try {

                $result          = null;
                $operationResult = null;

                $FILTER_PUBLISHED_PRODUCTS_DEFAULT = 1; #ОТЛДАдКА
                $FILTER_PUBLISHED_PRODUCTS         = ( isset( $_GET["FILTER_PUBLISHED_PRODUCTS"] ) ) ? $_GET["FILTER_PUBLISHED_PRODUCTS"] : $FILTER_PUBLISHED_PRODUCTS_DEFAULT;
                $RECURSIVELY                       = (int)$_POST["RECURSIVELY"];
                $PROCHIE                           = (int)$_POST["PROCHIE"];

                switch ( $operation ) {

                    case "merge_twins":
                        $operationResult = jstreeOperation_mergeNodes( $_POST );
                        break;
                    case "sort_subcategories":

                        $operationResult = jstreeOperation_sortSubCategories( $_POST, $RECURSIVELY, $PROCHIE );
                        break;
                    case "set_new_sortorder":
                        $operationResult = jstreeOperation_sortSetNewSortOrder( $_POST );
                        break;

                    case "refresh_node_psCount":
                        $rootID = (int)$_POST["id"];
                        update_psCount( $rootID );
                        $operationResult = arrangeSortOrderByDefault( $rootID );
                        break;

                    case "updatePS__nixru_groups":
                        $operationResult = groupUpdateCountsByVendor( 1, $FILTER_PUBLISHED_PRODUCTS ); // пропускаем опубликованные продукты
                        break;

                    case "move_node":
                        // $operationResult = jstreeOperation_Move( $_GET );
                        $operationResult = jstreeOperation_Move( $_POST );
                        break;
                    case "transfer_node":
                        // $operationResult = jstreeOperation_TransferGroup( $_GET );
                        $operationResult = jstreeOperation_TransferGroup( $_POST );
                        break;

                    case "create_node":
                        $operationResult = jstreeOperation_Create( $_POST );
                        break;

                    case "rename_node":
                        $operationResult = jstreeOperation_Rename( $_POST );
                        break;

                    case "delete_node":
                        $operationResult = jstreeOperation_DeleteCategoryNode( $_POST );
                        break;

                    case "hide_node":
                        $operationResult = jstreeOperation_HideGroup( $_POST );
                        break;

                    case "disable_node":
                        $operationResult = jstreeOperation_EnableGroup( $_POST, $NewValueEnabled = 0 );
                        // debugfile( $operationResult,"RESULT");
                        break;

                    case "kill_trashnodes":
                        $operationResult = jstreeOperation_KillTrashNodes( $_POST );
                        break;
                    case "kill_node":
                        $operationResult = jstreeOperation_KillNode( $_POST );
                        break;

                    case "move_products":
                    case "transfer_products":
                    case "move_products_with_subcat":
                    case "transfer_products_with_subcat":
                        break;

                    // Действие по умолчанию, ничего не делаем
                    default:
                        // $operationResult = 'UNKNOWN OPERATION :: ' . $operation;
                        $operationResult = null;
                        break;
                }

                // Возвращаем клиенту успешный ответ
                if ( isset( $operationResult ) ) {
                    $ajaxResponse = array(
                        "operation"       => $operation,
                         "operationResult" => $operationResult,
                        // "POST"            => json_encode( $_POST ),
                    );

                    header_status( 200 );
                    header( "Content-Type: application/json; charset=utf-8" );
                    echo json_encode( $ajaxResponse );

                } else {
                    $ajaxResponse = array(
                        "operation"       => $operation,
                         "operationResult" => "UNKNOWN OPERATION :: " . $operation,
                        "POST"            => json_encode( $_POST ),
                    );
                    header_status( 405 );
                    header( "Content-Type: application/json; charset=utf-8" );
                    echo json_encode( $ajaxResponse );
                }

            } catch ( Exception $e ) {
                // Возвращаем клиенту ответ с ошибкой

                header_status( 500 );
                header( "Content-Type: application/json; charset=utf-8" );
                echo json_encode( array(
                     "message"         => $e->getMessage(),
                    "operationResult" => "Status:  500 Server Error",
                    "POST"            => json_encode( $_POST ),
                ) );
            }
            die();
        } // if ( isset( $_GET["operation"] ) )

        //set sub-department template
        $smarty->assign( "admin_sub_dpt", "catalog_tree.tpl.html" );
    }
}

// СПРАВКА
{
    $UTF_productsSQL = <<<SQL
CREATE TABLE `PRICE_Products__nixru` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `parent_id` int(11) DEFAULT NULL,
  `Name` varchar(255) NOT NULL,
  `Col1` float NOT NULL,
  `Col2` float NOT NULL,
  `Col3` float NOT NULL,
  `Col4` float NOT NULL,
  `Col5` float NOT NULL,
  `Col6` float NOT NULL,
  `Warranty` varchar(255) NOT NULL,
  `Prefix` varchar(255) NOT NULL,
  `Weight` varchar(255) NOT NULL,
  `Volume` varchar(255) NOT NULL,
  `ApproxAmount` varchar(255) NOT NULL,
  `href` text NOT NULL,
  `vendorID` int(11) NOT NULL DEFAULT '1' COMMENT 'nix.ru',
  `product_code` int(11) NOT NULL COMMENT '#код_товара',
  `GroupID` int(11) NOT NULL,
  `GroupName` varchar(255) NOT NULL,
  `date_from_price` datetime NOT NULL COMMENT 'Время из прайса  nix.ru',
  `date_modified` datetime NOT NULL COMMENT 'Время обработки',
  `date_added` datetime NOT NULL COMMENT 'Время вставки в эту таблицу',
  `is_newproduct` int(1) NOT NULL DEFAULT '0' COMMENT 'Товар с пометкой [NEW] ',
  `is_processed` int(1) NOT NULL DEFAULT '0' COMMENT 'Товар обработан его видели и чтото сделали, не факт что пустили в каталог',
  `is_published` int(1) NOT NULL DEFAULT '0' COMMENT 'Товар попал в каталог',
  `enabled` int(1) NOT NULL DEFAULT '1' COMMENT 'Обрабатывать или Игнорировать (не парсить не обновлять)',
  `OriginalName` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vendorID_product_code` (`vendorID`,`product_code`),
  KEY `product_code` (`product_code`),
  KEY `enabled` (`enabled`),
  KEY `is_processed` (`is_processed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;

    $UTF_productsSQL = <<<SQL
CREATE TABLE `UTF_products` (
  `productID` int(11) NOT NULL AUTO_INCREMENT,
  `categoryID` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  `customers_rating` float DEFAULT '0',
  `Price` double DEFAULT NULL,
  `in_stock` int(11) DEFAULT NULL,
  `customer_votes` int(11) DEFAULT '0',
  `items_sold` int(11) NOT NULL,
  `enabled` int(11) DEFAULT NULL,
  `brief_description` text,
  `list_price` double DEFAULT NULL,
  `product_code` varchar(25) DEFAULT NULL,
  `sort_order` int(11) DEFAULT '0',
  `default_picture` int(11) DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `viewed_times` int(11) DEFAULT '0',
  `eproduct_filename` varchar(255) DEFAULT NULL,
  `eproduct_available_days` int(11) DEFAULT '5',
  `eproduct_download_times` int(11) DEFAULT '5',
  `weight` float DEFAULT '0',
  `meta_description` text,
  `meta_keywords` text,
  `free_shipping` int(11) DEFAULT '0',
  `min_order_amount` int(11) DEFAULT '1',
  `shipping_freight` double DEFAULT '0',
  `classID` int(11) DEFAULT NULL,
  `title` text,
  `vendorID` int(11) DEFAULT '0' COMMENT 'Поставщик',
  PRIMARY KEY (`productID`),
  UNIQUE KEY `vendorID_product_code` (`vendorID`,`product_code`),
  KEY `IDX_PRODUCTS1` (`categoryID`),
  KEY `date_added` (`date_added`),
  KEY `items_sold` (`items_sold`),
  KEY `sort_order_name` (`sort_order`,`name`),
  KEY `price` (`Price`),
  KEY `enabled` (`enabled`),
  KEY `name` (`name`),
  KEY `product_code` (`product_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;

// $s = "UPDATE " . PRODUCTS_TABLE . " SET " .
    // "categoryID=" . (int)$categoryID . ", " .
    // "name='" . xToText( trim( $name ) ) . "', " .
    // "Price=" . (double)$Price . ", " .
    // "description='" . xEscSQL( $description ) . "', " .
    // "in_stock=" . (int)$in_stock . ", " .
    // "customers_rating=" . (float)$customers_rating . ", " .
    // "brief_description='" . xEscSQL( $brief_description ) . "', " .
    // "list_price=" . (double)$list_price . ", " .
    // "product_code='" . xToText( trim( $product_code ) ) . "', " .
    // "sort_order=" . (int)$sort_order . ", " .
    // "date_modified='" . xEscSQL( get_current_time() ) . "', " .
    // "eproduct_filename='" . xEscSQL( $eproduct_filename ) . "', " .
    // "eproduct_available_days=" . (int)$eproduct_available_days . ", " .
    // "eproduct_download_times=" . (int)$eproduct_download_times . ",  " .
    // "weight=" . (float)$weight . ", meta_description='" . xToText( trim( $meta_description ) ) . "', " .
    // "meta_keywords='" . xToText( trim( $meta_keywords ) ) . "', free_shipping=" . (int)$free_shipping . ", " .
    // "min_order_amount = " . (int)$min_order_amount . ", " .
    // "shipping_freight = " . (double)$shipping_freight . ", " .
    // "title = '" . xToText( trim( $title ) ) . "' ";

// if ( $classID != null ) {
    //     $s .= ", classID = " . (int)$classID;
    // }

    $ph = <<<SQL
    "categoryID=" . (int)$categoryID . ", " .
    "name='" . xToText( trim( $name ) ) . "', " .
    "Price=" . (double)$Price . ", " .

    "description='" . xEscSQL( $description ) . "', " .
    "in_stock=" . (int)$in_stock . ", " .
    "customers_rating=" . (float)$customers_rating . ", " .
    "brief_description='" . xEscSQL( $brief_description ) . "', " .

    "list_price=" . (double)$list_price . ", " .
    "product_code='" . xToText( trim( $product_code ) ) . "', " .
    "sort_order=" . (int)$sort_order . ", " .
    "date_modified='" . xEscSQL( get_current_time() ) . "', " .

    "eproduct_filename='" . xEscSQL( $eproduct_filename ) . "', " .
    "eproduct_available_days=" . (int)$eproduct_available_days . ", " .
    "eproduct_download_times=" . (int)$eproduct_download_times . ",  " .
    "weight=" . (float)$weight . ", ".

    "meta_description='" . xToText( trim( $meta_description ) ) . "', " .
    "meta_keywords='" . xToText( trim( $meta_keywords ) ) . "', ".
    "free_shipping=" . (int)$free_shipping . ", " .
    "min_order_amount = " . (int)$min_order_amount . ", " .

    "shipping_freight = " . (double)$shipping_freight . ", " .
    "title = '" . xToText( trim( $title ) ) . "' ";
SQL;
}

?>