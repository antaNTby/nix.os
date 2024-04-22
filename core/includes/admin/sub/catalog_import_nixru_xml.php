<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-09-06       #
#          http://nixby.pro              #
##########################################

ini_set( 'display_errors', 1 ); // сообщения с ошибками будут показываться
error_reporting( E_ALL );       // E_ALL - отображаем ВСЕ ошибки

$CategoriesTable = CATEGORIES_TABLE;
$ProductsTable   = PRODUCTS_TABLE;
$GroupsTable     = GROUPS_TABLE__NIXRU;
$GoodsTable      = GOODS_TABLE__NIXRU;

//products and categories catalog import from MS Excel .CSV files
if ( !strcmp( $sub, "import_nixru_xml" ) ) {
    if ( CONF_BACKEND_SAFEMODE != 1 && ( !isset( $_SESSION["log"] ) || !in_array( 5, $relaccess ) ) ) //unauthorized
    {
        $smarty->assign( "admin_sub_dpt", "error_forbidden.tpl.html" );
    } else {

        ### Import From XML

        $vendorID = 1;

        $status = groupStatusByVendor( $vendorID );
        $smarty->assign( "status", $status );

        $file_XML_name = "data/files/xmlNixRu/Nix.xml";
        if ( file_exists( $file_XML_name ) ) {
            $smarty->assign( "file_exists_XML", 1 );
            // инициализация ридера
            include_once "core/classes/class.ConfigXMLReader__nixru.php";
            $reader = new ConfigXMLReader( $file_XML_name );
        }

        $Catalogue_FileName = "nix_yml_full_fmdfi232842y12.xml";
        $file_offers_name   = "data/files/xmlNixRu/{$Catalogue_FileName}";
        if ( file_exists( $file_offers_name ) ) {
            $smarty->assign( "file_exists_YML_offers", 1 );
            // инициализация ридера
            include_once "core/classes/class.ConfigXMLReader__offers.php";
            $reader2 = new ConfigXMLOffersReader( $file_offers_name );
        }

        if ( isset( $_POST["operation"] ) && isset( $_POST["priceType"] ) && isset( $_GET["importPrice"] ) ) {
            $Result = array();

            $operation = trim( $_POST["operation"] );
            $priceType = trim( $_POST["priceType"] );

            @set_time_limit( 0 );
            # обработка старых продуктов, которые уже есть в базе
            $Result["start_time"] = get_current_time();

            // global $PRICEGROUPCOUNT;
            // global $INSERTGROUPCOUNT;
            // global $UPDATEGROUPCOUNT;

            $where_clauseAll = "";
            $where_clauseAll .= "  enabled=1 ";
            $where_vendorID .= "  AND vendorID={$vendorID} ";

            $where_clause_not_published = "";
            $where_clause_not_published .= "  enabled=1 ";
            $where_clause_not_published .= "  AND is_published=0 ";
            $where_clause_not_published .= "  AND product_code IS NOT NULL ";
            $where_clause_not_published .= $where_vendorID;
            // $where_clause_not_published .= "  ORDER BY id ";
            // $where_clause_not_published .= " ; ";

            $where_clause_not_processed = "";
            $where_clause_not_processed .= "  enabled=1 ";
            $where_clause_not_processed .= "  AND is_processed=0 ";
            $where_clause_not_processed .= "  AND is_published=0 ";
            $where_clause_not_processed .= $where_vendorID;

            try {
                switch ( $operation ) {

################################################################################################
                    case "drop_is_new_group":
################################################################################################
                        $NewValue                       = (int)$_POST["param"];
                        $operationResult                = groupSet_is_new_group( $NewValue, $vendorID );
                        $Result["ERROR"]                = $operationResult["errors"];
                        $Result["SERVER_RESULT_STRING"] = $operationResult;
                        $Result["CONTINUE"]             = "reloadPage";
                        break;

################################################################################################
                    case "set_is_published_for_is_new_group":
################################################################################################
                        $NewValue                       = (int)$_POST["param"];
                        $operationResult                = groupSet_is_published_GroupsWith_is_new_group( $NewValue, $vendorID );
                        $Result["ERROR"]                = $operationResult["errors"];
                        $Result["SERVER_RESULT_STRING"] = $operationResult;
                        $Result["CONTINUE"]             = "reloadPage";
                        break;

################################################################################################
                    case "set_is_published_GroupsWith_products_to_insert":
################################################################################################
                        $NewValue                       = (int)$_POST["param"];
                        $operationResult                = groupSet_is_published_GroupsWith_products_to_insert( $NewValue, $vendorID );
                        $Result["ERROR"]                = $operationResult["errors"];
                        $Result["SERVER_RESULT_STRING"] = $operationResult;
                        $Result["CONTINUE"]             = "reloadPage";
                        break;

################################################################################################
                    case "set_is_published_GroupsWith_products_to_update":
################################################################################################
                        $NewValue                       = (int)$_POST["param"];
                        $operationResult                = groupSet_is_published_GroupsWith_products_to_update( $NewValue, $vendorID );
                        $Result["ERROR"]                = $operationResult["errors"];
                        $Result["SERVER_RESULT_STRING"] = $operationResult;
                        $Result["CONTINUE"]             = "reloadPage";
                        break;

################################################################################################
                    case "set_show_in_jstreeAll":
################################################################################################
                        $NewValue                       = (int)$_POST["param"];
                        $Result["ERROR"]                = groupSetIsPublishedAll( $NewValue, $vendorID );
                        $Result["SERVER_RESULT_STRING"] = ( $NewValue ) ? "Все группы отмечены как опубликованные" : "Все группы отмечены как НЕ опубликованные";
                        $Result["CONTINUE"]             = "reloadPage";
                        break;

################################################################################################
                    case "change_dateLowBoundUpdate":
################################################################################################
                        $NewValue = $_POST["param"];
                        if ( !empty( $NewValue ) ) {
                            $_SESSION["dateLowBoundUpdate"] = $NewValue;
                        } else {
                            $_SESSION["dateLowBoundUpdate"] = dtConvertToStandartForm( get_current_time(), $showtime = 0 );
                        }
                        $Result["ERROR"]                = false;
                        $Result["SERVER_RESULT_STRING"] = "Обновлятся будут товары моложе " . $_SESSION["dateLowBoundUpdate"];
                        $Result["CONTINUE"]             = false;
                        break;

################################################################################################
                    case "link_by_same_categoryname":
################################################################################################
                        $NewValue = (int)$_POST["param"];
                        if ( $NewValue == 1 ) {
                            // -- Добавляем categoriesID_AssocLink
                            $s = "
                            UPDATE PRICE_Groups t3
                            LEFT JOIN UTF_categories t4 ON ( t3.GroupName=t4.name )
                            SET t3.categoriesID_AssocLink = t4.categoryID,
                                t3.is_processed=4
                            WHERE t4.categoryID>1
                              AND t3.enabled = 1
                              AND t3.vendorID={$vendorID}
                              AND NOT(t3.categoriesID_AssocLink >0)
                              OR t3.is_processed=4;";

                        } elseif ( $NewValue == 0 ) {
                            // -- Убираем categoriesID_AssocLink
                            $s = "
                            UPDATE PRICE_Groups t3
                            LEFT JOIN UTF_categories t4 ON ( t3.GroupName=t4.name )
                            SET t3.categoriesID_AssocLink = 0
                            WHERE t4.categoryID>1
                              AND t3.enabled = 1
                              AND t3.vendorID={$vendorID}
                              AND t3.is_processed=4;";
                        } elseif ( $NewValue == 4 ) {
                            $s = "UPDATE PRICE_Groups t3
                                    SET t3.is_processed=0
                                    WHERE t3.is_processed=4;";
                        }
                        if ( $s != "" ) {
                            $r1                 = db_query( $s );
                            $Result["ERROR"]    = false;
                            $Result["CONTINUE"] = "reloadPage_delayed";
                        }
                        $Result["SERVER_RESULT_STRING"] = $r1["mysql_info"] . "<br>" . $r1["mysql_affected_rows"] . (  ( $NewValue ) ? " добавлено" : " сброшено" );
                        break;
################################################################################################
                    case "quick_update_by_product_code":
################################################################################################

                        if ( !empty( $_SESSION["dateLowBoundUpdate"] ) ) {
                            $dateLowBoundUpdate = $_SESSION["dateLowBoundUpdate"];
                        } else {
                            $_SESSION["dateLowBoundUpdate"] = dtConvertToStandartForm( get_current_time(), $showtime = 0 );
                            $dateLowBoundUpdate             = $_SESSION["dateLowBoundUpdate"];
                        }

                        $s1 = "";
                        $s1 .= "
                        UPDATE PRICE_Products__nixru t1
                        LEFT JOIN UTF_products t2 ON (t1.product_code = t2.product_code)
                        AND (t1.vendorID = t2.vendorID)
                        SET
                            t1.is_processed=1,
                            t1.is_published=1,
                            t2.name= CONCAT(t1.`Prefix`,' ',t1.`Name`),
                            t2.title= CONCAT('nix.by ',t1.`Name`),
                            t2.in_stock=nixru_encode_approxamount(t1.`ApproxAmount`),
                            t2.Price= ROUND(t1.`Col1`,4),
                            t2.list_price= ROUND(t1.`Col3`,4),
                            t2.date_modified= t1.date_from_price
                        WHERE t1.enabled=1
                          AND t2.enabled=1
                          AND t2.date_modified < '{$dateLowBoundUpdate} 23:59:59'
                          AND t1.date_modified < '{$dateLowBoundUpdate} 23:59:59'
                          AND t1.product_code IS NOT NULL
                          AND t2.product_code IS NOT NULL
                          AND t1.parent_id > 0
                          AND t1.parent_id NOT IN
                            (SELECT t3.id AS selected_group_id
                             FROM PRICE_Groups t3
                             WHERE ((t3.enabled = 0)
                                    OR (t3.categoriesID_AssocLink < 1))
                               AND (t3.vendorID = {$vendorID}))
                               ;";
                        $r1 = db_query( $s1 );

                        $Result["ERROR"]                = false;
                        $Result["SERVER_RESULT_STRING"] = $r1["mysql_info"] . "<br>" . $r1["mysql_affected_rows"] . " обновлено";
                        $Result["CONTINUE"]             = "reloadPage_delayed";
                        break;

################################################################################################
                    case "set_outstockAll":
################################################################################################
                        $NewValue                       = (int)$_POST["param"];
                        $Result["ERROR"]                = importUpdateInStockAll( $NewValue, $vendorID );
                        $Result["SERVER_RESULT_STRING"] = "Все товары \"отсутствуют на складе\" ";
                        $Result["CONTINUE"]             = "reloadPage";
                        break;

################################################################################################
                    case "startImportXML":
################################################################################################
                        $r0 = dbSet_publish_processed( GOODS_TABLE__NIXRU, 15, $where_clauseAll );
                        $r1 = dbSet_publish_processed( GOODS_TABLE__NIXRU, 1, $where_clauseAll );
                        $r1 = dbSet_outstock_all( GOODS_TABLE__NIXRU, 0, $where_clauseAll );
                        // $r1 = dbSet_publish_processed( GOODS_TABLE__NIXRU, 15, "" );

                        $Result["ERROR"]                = false;
                        $Result["SERVER_RESULT_STRING"] = $r1["mysql_affected_rows"] . " <br> Этап 1. Импорт <strong>\"{$file_XML_name}\"</strong> начался в " . get_current_time();
                        $Result["CONTINUE"]             = "parseXMLprice";
                        break;

################################################################################################
                    case "showInfo":
################################################################################################
                        $Result["ERROR"]                = false;
                        $Result["SERVER_RESULT_STRING"] = "НЕТ ДАННЫХ";
                        @set_time_limit( 0 );
                        # запускаем парсинг
                        $reader->parse();
                        $Result["INFORMATION"]          = ( $reader->getInformation() );
                        $Result["SERVER_RESULT_STRING"] = htmlInformation( $Result["INFORMATION"] );
                        $Result["CONTINUE"]             = "update_products_count_subcount";
                        break;

################################################################################################
                    case "update_products_count_subcount":
################################################################################################
                        $_start_time = get_current_time();
                        $param       = (int)$_POST["param"];
                        #function groupUpdateCountsByVendor( $vendorID = 1, $FILTER_PUBLISHED_PRODUCTS=-1 )
                        $FILTER_PUBLISHED_PRODUCTS = -1;
                        $products_count_subcount   = groupUpdateCountsByVendor( 1, $FILTER_PUBLISHED_PRODUCTS );
                        // debugfile( $products_count_subcount, "catalog_import_nixru_xml.php:124 products_count_subcount" );
                        $_finish_time                   = get_current_time();
                        $diff                           = strtotime( $_finish_time ) - strtotime( $_start_time );
                        $diff_seconds                   = $diff - (int)( $diff / 60 ) * 60; // Разница между (секунды)
                        $Result["SERVER_RESULT_STRING"] = "";
                        $Result["ERROR"]                = ( is_array( $products_count_subcount ) ) ? false : true;
                        $Result["SERVER_RESULT_STRING"] .= " Обработаго <strong class='badge'>" . count( $products_count_subcount ) . " Групп товаров.</strong>.";
                        // $Result["SERVER_RESULT_STRING"] .= " Затрачено <strong class='badge'>$diff_seconds сек.</strong>.";
                        $Result["CONTINUE"] = ( $param ) ? "reloadPage_delayed" : false;
                        break;

################################################################################################
                    case "parseXMLprice":
################################################################################################
                        @set_time_limit( 0 );
                        $PRICEGROUPCOUNT  = 0;
                        $INSERTGROUPCOUNT = 0;
                        $UPDATEGROUPCOUNT = 0;

                        # чтобы не тратить память на хранение ненужных элементов, мы их просто выбрасываем на каждой итерации
                        $reader->onEvent( "afterParseElement", function ( $name, $context ) {
                            $context->clearResult();
                        } );

                        $reader->onEvent( 'parseInfo', function ( $context ) {
                            global $date_from_price;
                            $getInfo         = $context->getInformation();
                            $date_from_price = ( isset( $getInfo["date_from_price"] ) ) ? $getInfo["date_from_price"] : get_current_time();
                        } );

                        $reader->onEvent( 'parseGroup', function ( $context ) {
                            global $PRICEGROUPCOUNT;
                            global $INSERTGROUPCOUNT;
                            global $UPDATEGROUPCOUNT;

                            $PRICEGROUPCOUNT++;
                            $getResult     = $context->getResult();
                            $parsed_result = $getResult['Groups'];
                            $is_new        = saveGroup( $parsed_result );
                            ( $is_new == true ) ? $INSERTGROUPCOUNT++ : $UPDATEGROUPCOUNT++; //// возвращаем 1 если это новая группа , иначе возвращаем 0
                        } );

                        $reader->onEvent( 'parseProduct', function ( $context ) {
                            $getResult     = $context->getResult();
                            $parsed_result = $getResult['Products'];
                            // if ($parsed_result["productsCount"]>200) break;
                            saveProductNixru( $parsed_result );
                        } );

                        # запускаем парсинг
                        $_SESSION["PARCING_ACTIVE"] = true;
                        $reader->parse();
                        // $Result = $reader->getResult();
                        $_SESSION["PARCING_ACTIVE"] = false;

                        $Result["ERROR"]                = false;
                        $Result["SERVER_RESULT_STRING"] = "Этап 2. Импорт <strong>\"$file_XML_name\"</strong> завершен в " . get_current_time() . "<br>";
                        $Result["SERVER_RESULT_STRING"] .= "новых групп <strong>$INSERTGROUPCOUNT</strong> " . "<br>";
                        $Result["SERVER_RESULT_STRING"] .= "существующих групп <strong>$UPDATEGROUPCOUNT</strong> " . "<br>";
                        $Result["SERVER_RESULT_STRING"] .= "Итого <strong>$PRICEGROUPCOUNT</strong> в импортируемом XML-файле ";
                        $Result["CONTINUE"] = "showInfo"; //false;//"showInfo";
                        break;

/*  antaNT 2021-01-20 -дублируется в catalog_YML_parcer.php
################################################################################################
case "OLDparseXMLoffers":
################################################################################################
@set_time_limit( 0 );

$reader2->cleardbtables();

if ( file_exists( "adminer.sql" ) ) {
unlink( "adminer.sql" );
}

# чтобы не тратить память на хранение ненужных элементов, мы их просто выбрасываем на каждой итерации
$reader2->onEvent( "afterParseElement", function ( $name, $context ) {
$context->clearResult();
} );

$reader2->onEvent( 'parseoffer', function ( $context ) {
$getResult     = $context->getResult();
$parsed_result = $getResult;
// cash_sql_query( json_encode($parsed_result));
// saveOfferToSQL( $parsed_result );

if ( !is_null( $parsed_result["info"] ) && isset( $parsed_result["info"] ) ) {
writeResultToFile( json_encode( $parsed_result["info"] ), "x_info" );
}
if ( !is_null( $parsed_result["offer"] ) && isset( $parsed_result["offer"] ) ) {
writeResultToFile( json_encode( $parsed_result["offer"] ), "x_offer" );
}
if ( !is_null( $parsed_result["pictures"] ) && isset( $parsed_result["pictures"] ) ) {
writeResultToFile( json_encode( $parsed_result["pictures"] ), "x_pictures" );
}
if ( !is_null( $parsed_result["params"] ) && isset( $parsed_result["params"] ) ) {
writeResultToFile( json_encode( $parsed_result["params"] ), "x_params" );
}

} );

// $reader2->onEvent( 'parsevendor', function ( $context ) {
//     $getResult     = $context->getResult();
//     $parsed_result = $getResult['Offer'];
//     saveBMtP( $parsed_result );
// } );

// $reader2->onEvent( 'parsepicture', function ( $context ) {
//     $getResult     = $context->getResult();
//     $parsed_result = $getResult['Offer']['pictures'];
//     // console($parsed_result);
//     // if ( !is_null( $parsed_result ) ) {
//         savePictures( $parsed_result );
//     // }//
// } );

# запускаем парсинг
$_SESSION["PARCING_ACTIVE"] = true;
$reader2->parse();
// $Result = $reader->getResult();
$_SESSION["PARCING_ACTIVE"] = false;

// $reader2->close();
$Result["ERROR"]                = false;
$Result["SERVER_RESULT_STRING"] = " 49031 парсим " . $file_offers_name;
$Result["CONTINUE"]             = false; //false;//"showInfo";

break;

 */

################################################################################################
                    case "update_publishedOLD":
################################################################################################
                        @set_time_limit( 0 );
                        # обработка старых продуктов, которые уже есть в базе
                        include_once "core/includes/admin/sub/catalog_import_nixru_xml__update_published_products.php";
                        $Result["SERVER_RESULT_STRING"] = "";
                        $Result["ERROR"]                = ( $queryUpdate["resource"] ) ? "0" : "1";
                        $Result["SERVER_RESULT_STRING"] .= "Обновлено товаров: <strong> $ii / $count_ProductsToUpdate</strong>";
                        $Result["CONTINUE"] = false;
                        break;

################################################################################################
                    case "update_samecategoryOLD":
################################################################################################
                        include_once "core/includes/admin/sub/catalog_import_nixru_xml__update_samecategory_products.php";
                        $Result["ERROR"] = ( $q_addMatchesCategoryID_is_published["resource"] ) ? "0" : "1";
                        // $Result["ERROR"] = ( $queryIs_processed["resource"] ) ? "0" : "1";
                        $Result["SERVER_RESULT_STRING"] = "Начало update_samecategory_products " . get_current_time();
                        $Result["SERVER_RESULT_STRING"] .= "<br>количесво совпадающих категорий $count_FullMatchesGroups";
                        $Result["SERVER_RESULT_STRING"] .= "товаров-ii= $ii";
                        $Result["CONTINUE"] = false;
                        break;

################################################################################################
                    case "process_insert_linked_by_AssocLink":
################################################################################################

                        // это долгий путь
                        include_once "core/includes/admin/sub/catalog_import_nixru_xml__process_insert_linked_by_AssocLink.php";

                        $Result["ERROR"]                = false;
                        $Result["PARAM"]                = $_SESSION["PASS_NUMBER"];
                        $Result["SERVER_RESULT_STRING"] = $_SESSION["PASS_NUMBER"] . " проход. Добавлено {$_SESSION['PRODUCTS_NUMBER']} товаров";
                        $Result["CONTINUE"]             = $continue_function;
                        break;

################################################################################################
                    case "process_update_by_product_code":
################################################################################################
                        // это долгий путь
                        include_once "core/includes/admin/sub/catalog_import_nixru_xml__process_update_by_product_code.php";

                        $Result["ERROR"]                = false;
                        $Result["SERVER_RESULT_STRING"] = $_SESSION["FUZE"] . " DONE";
                        $Result["PARAM"]                = $_SESSION["UPDATEPRODUCT_COUNTER"];
                        $Result["CONTINUE"]             = $continue_function;
                        break;

################################################################################################
                    case "disable_disabled":
################################################################################################
                        $param = (int)$_POST["param"];

                        $sql = "
                        UPDATE `PRICE_Products__nixru` t1
                        INNER JOIN PRICE_Groups t3 ON t1.parent_id=t3.id
                        SET t1.enabled={$param}, t1.is_processed=1, t1.is_published=1
                        WHERE t3.enabled=0;
                        ";

                        $r1 = db_query( $sql );

                        // db_multiquery( $sql, DB_HOST, DB_USER, DB_PASS, DB_NAME );
                        // $r1["mysql_info"]. "<br>". $r1["mysql_affected_rows"]. " обновлено";

                        $Result["CONTINUE"]             = "reloadPage_delayed";
                        $Result["ERROR"]                = !!$r1["res"];
                        $Result["SERVER_RESULT_STRING"] = $r1["mysql_info"] . "<br>" . " Все товары в отключенных группах " . (  ( !$param ) ? "отключены" : "включены" );
                        $Result["CONTINUE"]             = "reloadPage_delayed";
                        break;
################################################################################################
                    case "catalog_disable_disabled":
################################################################################################
                        $param = (int)$_POST["param"];

                        $sql = "
                        UPDATE `UTF_products` p
                        INNER JOIN UTF_categories c ON (p.categoryID = c.categoryID)
                        SET p.`enabled` = '{$param}'
                        WHERE c.`enabled` = '0';
                        ";

                        $r1 = db_query( $sql );

                        // db_multiquery( $sql, DB_HOST, DB_USER, DB_PASS, DB_NAME );
                        // $r1["mysql_info"]. "<br>". $r1["mysql_affected_rows"]. " обновлено";

                        $Result["CONTINUE"]             = "reloadPage_delayed";
                        $Result["ERROR"]                = !!$r1["res"];
                        $Result["SERVER_RESULT_STRING"] = $r1["mysql_info"] . "<br>" . " Все товары в отключенных категориях " . (  ( !$param ) ? "отключены" : "включены" );
                        $Result["CONTINUE"]             = "reloadPage_delayed";
                        break;
################################################################################################
                    case "KILL":
################################################################################################
                        # code...
                        $param = (int)$_POST["param"];
                        $reader->ClearDBTables( $param ); //1-для удаления и Групп и Продуктов

                        $Result["ERROR"]                = false;
                        $Result["SERVER_RESULT_STRING"] = "Очистка " . (  ( $param ) ? "таблиц PRICE_Groups, PRICE_Products__nixru " : "таблицы PRICE_Products__nixru " ) . " завершена";
                        $Result["CONTINUE"]             = false;
                        break;

################################################################################################
                    default:
################################################################################################
                        $Result["ERROR"]                = true;
                        $Result["SERVER_RESULT_STRING"] = "ОШИБКА ИМПОРТА";
                        $Result["CONTINUE"]             = false;
                        break;

################################################################################################
                } // switch

                $Result["finish_time"]  = get_current_time();
                $diff                   = -( strtotime( $Result["start_time"] ) - strtotime( $Result["finish_time"] ) );
                $Result["diff_seconds"] = $diff - (int)( $diff / 60 ) * 60;                 // Разница между (секунды)
                $Result["diff_days"]    = ( $diff - (int)( $diff / 86400 ) * 86400 ) / 86400; // Разница между (дни)

                $Result["SERVER_RESULT_STRING"] .= "<br> Начало в: " . $Result["start_time"] . ", ";
                $Result["SERVER_RESULT_STRING"] .= " Завершено в: " . $Result["finish_time"] . ", ";
                $Result["SERVER_RESULT_STRING"] .= " Затрачено " . $Result["diff_seconds"] . " сек.";

                $ajaxResponse = $Result;

                ( $Result["ERROR"] == true ) ? header_status( 405 ) : header_status( 200 );
                header( "Content-Type: application/json; charset=utf-8" );
                echo json_encode( $ajaxResponse );

            } catch ( Exception $e ) {
                // Возвращаем клиенту ответ с ошибкой
                header_status( 500 );
                header( "Content-Type: application/json; charset=utf-8" );
                echo json_encode( array(
                    "message"              => $e->getMessage(),
                    "SERVER_RESULT_STRING" => null,
                    "POST"                 => $_POST,
                    "GET"                  => $_GET,
                ) );
            }
            die();
        } //         if ( isset( $_POST["operation"]
    }

    $adminer_link = "adminer.php?username=" . DB_USER . "&amp;db=" . DB_NAME . "&amp;select=" . "PRICE_Products__nixru";

    if ( !isset( $_SESSION["dateLowBoundUpdate"] ) ) {
        $dateLowBoundUpdate = dtConvertToStandartForm( get_current_time(), $showtime = 0 );
    } else {
        $dateLowBoundUpdate = $_SESSION["dateLowBoundUpdate"];
    }

    $controlTermin = htmlDATETIMEPICKER(
        $Control_Name = "controlTermin",
        $Field_Name = "controlTermin",
        $VALUE = $dateLowBoundUpdate,
        $Title = "Обновить Цены/Наличие товаров моложе этого времени",
        $ShowDate = true,
        $ShowTime = true
    );
    $smarty->assign( "controlTermin", $controlTermin );

    $smarty->assign( "dateLowBoundUpdate", $dateLowBoundUpdate );

    $smarty->assign( "adminer_link", $adminer_link );
    $smarty->assign( "admin_sub_dpt", "import_nixru_xml.tpl.html" );

    if ( isset( $_SESSION['UPDATE_PROCESS_ACTIVE'] ) ) {
        unset( $_SESSION['UPDATE_PROCESS_ACTIVE'] );

    }
    if ( isset( $_SESSION['INSERT_PROCESS_ACTIVE'] ) ) {
        unset( $_SESSION['INSERT_PROCESS_ACTIVE'] );
        unset( $_SESSION['PASS_NUMBER'] );
        unset( $_SESSION['PRODUCTS_NUMBER'] );

        $_SESSION['default_categoryID'] = 1;
        $_SESSION['current_categoryID'] = 1;
    }

    if ( isset( $_SESSION['UPDATEPRODUCT_COUNTER'] ) ) {
        unset( $_SESSION['UPDATEPRODUCT_COUNTER'] );
    }

    if ( isset( $_SESSION['FUZE'] ) ) {
        unset( $_SESSION['FUZE'] );
    }
    if ( isset( $_SESSION['PARCING_ACTIVE'] ) ) {
        unset( $_SESSION['PARCING_ACTIVE'] );
    }

}

# ini_set( 'display_errors', 0 ); // теперь сообщений НЕ будет

?>