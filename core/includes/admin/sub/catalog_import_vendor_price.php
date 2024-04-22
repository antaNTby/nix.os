<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2021-01-29       #
#          http://nixminsk.by            #
##########################################

// console($_SERVER);
/*
SELECT SQL_CALC_FOUND_ROWS COUNT(DISTINCT `GroupName`) as GROUPS_count, `date_modified`
FROM `PRICE_Groups`
GROUP BY `date_modified`
0919
 */

/*

{
"operation": "CREATE_NODE_OPERATION",
"event": "create_node",
"temp_node_id": "j1_1",
"temp_parent": "55",
"temp_position": 1,
"node_parents": [
"55",
"40",
"#"
],
"text": "New node",
"type": "folder-empty",
"to_pos": "last"
}

 */

ini_set( 'display_errors', 1 ); // сообщения с ошибками будут показываться
error_reporting( E_ALL );       // E_ALL - отображаем ВСЕ ошибки

db_clearGarbageSymbols();


// $smarty->assign( "NOTDataTables", true );

//products and categories catalog import from MS Excel .CSV files
if ( !strcmp( $sub, "import_vendor_price" ) ) {

    if ( CONF_BACKEND_SAFEMODE != 1 && ( !isset( $_SESSION["log"] ) || !in_array( 5, $relaccess ) ) ) //unauthorized
    {
        $smarty->assign( "admin_sub_dpt", "error_forbidden.tpl.html" );
    } else {

        function __qty2in_stock( $d, $params = array() ) {
            $qty = $d;
            switch ( $qty ) {
                case '****':
                    $in_stock = 101;
                    break;
                case '***':
                    $in_stock = 11;
                    break;
                case '**':
                    $in_stock = 4;
                    break;
                case '*':
                    $in_stock = 2;
                    break;

                default:

                    $in_stock = (int)$qty;
                    $qty      = "";
                    break;
            }
            return roundf( $in_stock );
        }

        $VENDOR_GOODS_TABLE = "PRICE_GOODS__jetby";
        $GROUPS_TABLE       = "PRICE_GROUPS";
        $file_XML_name      = "data/files/xmlNixRu/data.xml";

        $vendorID = 4;

        $SpecialFolders = array();
        for ( $i = 55; $i < 70; $i++ ) {
            $SpecialFolders[] = "{$i}";
        }
        $SpecialFolders[] = "555";

        $smarty->assign( "special_nodes_list", implode( ",", $SpecialFolders ) );

        $file_info = array();
        #проверяем наличие файла в папке на сайте
        $file_info = getParsedFileInfo( $file_XML_name, $vendorID = 4 );

// db_clearGarbageSymbols();

        if ( file_exists( $file_XML_name ) && !isset( $file_info["ERROR"] ) ) {

            $smarty->assign( "file_exists_XML", 1 );

            $file = file_get_contents( $file_XML_name, FILE_USE_INCLUDE_PATH );
            $data = ( str_replace( '<price:NixMinsk', '<price', $file ) );
            $data = ( str_replace( '</price:NixMinsk>', '</price>', $data ) );
            $file = file_put_contents( $file_XML_name, $data );

            // инициализация ридера
            include_once "core/classes/class.ConfigXMLReader__jetby.php";
            $reader = new ConfigXMLReaderJet( $file_XML_name );
            $reader->setVendor( (int)$_GET["vendorID"] );

        }

        #количество Гуудс до обновления
        $sql = "SELECT COUNT(*) AS `before_count` FROM `{$VENDOR_GOODS_TABLE}`;";
        $sql = RN( $sql );
        $q   = db_query( $sql );
        $row = db_fetch_assoc( $q );

/*        {
            include_once "core/classes/class.db.php";
            $_default_user = DB_USER;
            $_default_pass = DB_PASS;
            $_default_db   = DB_NAME;
            $_default_host = DB_HOST;

            $DB = new db( $_default_host, $_default_user, $_default_pass, $_default_db );

            $sql2                          = "SELECT * FROM `{$VENDOR_GOODS_TABLE}`;";

            $file_info["before_count_new"] = $before_count->numRows();
        }*/

        $file_info["before_count"]   = $row["before_count"];
        $file_info["parsed_count"]   = "N/A";
        $file_info["removed_count"]  = "N/A";
        $file_info["updated_count"]  = "N/A";
        $file_info["inserted_count"] = "N/A";
        $file_info["after_count"]    = "N/A";

        #количество корневых групп
        $sql = "SELECT COUNT(*) AS `root_groups_count` FROM `{$GROUPS_TABLE}`
        WHERE `parent` IS NULL AND `vendorID` = '" . $vendorID . "' AND `group_code` LIKE '%" . $vendorID * 100000 . "';";
        $sql                 = RN( $sql );
        $q                   = db_query( $sql );
        $rootres             = db_fetch_assoc( $q );
        $isset_root_category = ( (int)$rootres["root_groups_count"] == 1 );
        $smarty->assign( "isset_root_category", $isset_root_category );

        // console( $root_groups_count );

        $sql = "SELECT DISTINCT `man` as brandname FROM `{$VENDOR_GOODS_TABLE}` ORDER BY `man`;";
        $q   = db_query( $sql );
        while ( $row = db_fetch_assoc( $q ) ) {
            $ind              = strtolower( trim( $row["brandname"] ) );
            $title            = trim( $row["brandname"] );
            $brands["{$ind}"] = $title;
        }
        $smarty->assign( "brands", $brands );

        $sql = "SELECT DISTINCT `article` as modelname FROM `{$VENDOR_GOODS_TABLE}` ORDER BY `man`;";
        $q   = db_query( $sql );
        while ( $row = db_fetch_assoc( $q ) ) {
            $ind              = strtolower( trim( $row["modelname"] ) );
            $title            = trim( $row["modelname"] );
            $models["{$ind}"] = $title;
        }
        $smarty->assign( "models", $models );

        $sql = "SELECT `id` as group_id, IF (`description` LIKE '%Level 1%',UPPER(`name`), `name`) AS category, `group_code`, `parent` FROM `{$GROUPS_TABLE}` ORDER BY `sort_order`;";
        $q   = db_query( $sql );
        while ( $row = db_fetch_assoc( $q ) ) {
            $ind                  = (int)$row["group_id"];
            $title                = trim( $row["category"] );
            $categories["{$ind}"] = $title . " ID:" . (int)$row["group_code"] . "";
        }
        $smarty->assign( "categories", $categories );

        $smarty->assign( "file_info", $file_info );
        $adminer_link = "adminer.php?username=" . DB_USER . "&amp;db=" . DB_NAME . "&amp;select=" . "{$VENDOR_GOODS_TABLE}";
        $smarty->assign( "adminer_link", $adminer_link );

    }

    if ( isset( $_POST["operation"] ) && $_POST["operation"] == "initDataTable" && isset( $_GET["initDataTable"] ) ) {
        // DB table to use
        $table = $VENDOR_GOODS_TABLE;
        // Table's primary key
        $primaryKey = "id";
        // SQL server connection information
        $sql_connect = array(
            "user" => DB_USER,
            "pass" => DB_PASS,
            "db"   => DB_NAME,
            "host" => DB_HOST,
        );
        $DT_columns = array(
            array( "db" => "group_id", "dt" => "group_id" ),
            array( "db" => "good_code", "dt" => "good_code" ),
            array( "db" => "name", "dt" => "name" ),
            array( "db" => "price", "dt" => "price" ),
            array(
                "db"        => "qty",
                "dt"        => "qty",
                "formatter" => function ( $d, $row ) {
                    return __qty2in_stock( $d, $row );
                },
            ),
            // array( "db" => "ean", "dt" => "ean" ),
            // array( "db" => "timestamp", "dt" => "timestamp" ),
            array( "db" => "man", "dt" => "man" ),
            array( "db" => "article", "dt" => "article" ),
            array( "db" => "id", "dt" => "id" ),
            array( "db" => "process_flag", "dt" => "process_flag" ),
            array( "db" => "insert_flag", "dt" => "insert_flag" ),
            array( "db" => "transfer_flag", "dt" => "transfer_flag" ),
            array( "db" => "enabled", "dt" => "enabled" ),
        );

        $whereResult_clause = __getGoodsFilterParams( $_POST["VendorTreeFilter"] );
        require "core/classes/class.SSP.php";
        $res = SSP::complex( $_POST, $sql_connect, $table, $primaryKey, $DT_columns, $whereResult_clause, null ); //
        echo json_encode( $res );
        die();

    } elseif ( isset( $_GET["tree"] ) && isset( $_GET["jstree"] ) && ( $_GET["tree"] === "VendorTree" || ( $_GET["tree"] == "MainTree" ) ) ) {
        # ant_jstree_operations.php
        include_once "core/includes/admin/sub/ant_jstree_operations.php";
        # ant_jstree_operations.php

    } elseif ( isset( $_POST["ajax"] ) && ( $_POST["ajax"] == "ajax_{$sub}" ) && isset( $_POST["operation"] ) && isset( $_GET["vendorID"] ) && isset( $_GET["dpt"] ) && isset( $_GET["sub"] ) && file_exists( $file_XML_name ) ) {

        $operation_result          = array();
        $operation_result["start"] = get_current_time();

        $operation_result["update_info_table"] = 0;

        $operation = trim( $_POST["operation"] );

        try {
            switch ( $operation ) {

                case "GetFileInfo":
                    # code...

                    @set_time_limit( 0 );

                    # запускаем парсинг
                    $reader->parse();

                    $parse_result = ( $reader->getInformation() );

                    $operation_result["try"]                     = $operation;
                    $operation_result["file_info"]               = $file_info;
                    $operation_result["update_info_table"]       = 1;
                    $operation_result["file_info"]["goods_cnt"]  = $parse_result["goods_cnt"];
                    $operation_result["file_info"]["groups_cnt"] = $parse_result["groups_cnt"];

                    break;
                case "InsertDefaultCategoriesToGROUPS_TABLE":

                    $data                                             = array();
                    $_result["InsertDefaultCategoriesToGROUPS_TABLE"] =
                        insertRootGroups( $data, $vendorID = (int)$_GET["vendorID"], $source_parent = 1 );

                    if ( !is_array( $data ) or !$data ) {
                        // return false;
                        $operation_result["ERROR"] = $data;
                        break;
                    }
                    // consolelog($data);

                    saveNewGROUPS( $data, $vendorID = (int)$_GET["vendorID"] );

                    $operation_result["InsertDefaultCategoriesToGROUPS_TABLE"] = $data;
                    // $LAST_RES .= $sql . "<br> <em>" . $q["mysql_info"] . "</em> <br> ";

                    break;

                /*
                INSERT_FLAG
                0-removed
                1-inserted
                2-updated
                 */
                case "ParseItems":
                    # code...
                    @set_time_limit( 0 );
                    $process_id = 1;
                    #обнуляем таблицу
                    $VENDOR_GOODS_TABLE = "PRICE_GOODS__jetby";
                    $LAST_RES           = ":: STARTED \$process_id = 1 >_ <br>";
#количество Гуудс до обновления
                    $sql = "
SELECT COUNT(*) AS `before_count`
FROM `{$VENDOR_GOODS_TABLE}`;
";
                    $sql = RN( $sql );
                    $q   = db_query( $sql );
                    $LAST_RES .= $sql . "<br> <em>" . $q["mysql_info"] . "</em> <br> ";
                    $row                                           = db_fetch_assoc( $q );
                    $operation_result["file_info"]["before_count"] = $row["before_count"];

# обнуляем счетчики
                    $sql = "
UPDATE `{$VENDOR_GOODS_TABLE}`
SET
    `qty`=0,
    `process_flag`='{$process_id}',
    `insert_flag`='0';
 ";
                    $sql = RN( $sql );
                    $q   = db_query( $sql );
                    $LAST_RES .= $sql . "<br> <em>" . $q["mysql_info"] . "</em> <br> ";

# ЗАПУСКАЕМ ЧТЕНИЕ ФАЙЛА
                    $GOODS_PARSED_COUNT = 0;
                    # чтобы не тратить память на хранение ненужных элементов, мы их просто выбрасываем на каждой итерации
                    $reader->onEvent( "afterParseElement", function ( $name, $context ) {
                        $context->clearResult();
                    } );

                    $reader->onEvent( 'parseitem', function ( $context ) {
                        global $GOODS_PARSED_COUNT;
                        $getResult     = $context->getResult();
                        $parsed_result = $getResult["Goods"];
                        saveNewGood( $parsed_result, $vendorID = 4, $INSERT_ONLY_NEW = 0 );
                        $GOODS_PARSED_COUNT++;
                        // if ($GOODS_PARSED_COUNT>5) die();
                    } );
                    # запускаем парсинг
                    $reader->parse();

                    $operation_result["file_info"]["parsed_count"] = "{$GOODS_PARSED_COUNT}";
# ЗАПУСКАЕМ ЧТЕНИЕ ФАЙЛА

#ЗАПРАШИВАЕМ СТАТИСТИКУ ОБНОВЛЕНИЯ
                    $sql = "
SELECT COUNT(*) AS `count`,
       `insert_flag`
FROM `{$VENDOR_GOODS_TABLE}`
GROUP BY `insert_flag` ASC;
 ";
                    $sql = RN( $sql );
                    $q   = db_query( $sql );
                    $LAST_RES .= $sql . "<br> <em>" . $q["mysql_info"] . "</em> <br> ";
                    while ( $row = db_fetch_assoc( $q ) ) {
                        $res[] = $row;
                    }
                    ;
                    $operation_result["file_info"]["removed_count"]  = 0;
                    $operation_result["file_info"]["updated_count"]  = 0;
                    $operation_result["file_info"]["inserted_count"] = 0;

                    foreach ( $res as $state ) {

                        if ( $state["insert_flag"] == "0" ) {
                            $operation_result["file_info"]["removed_count"] = $state["count"];
                        }
                        if ( $state["insert_flag"] == "1" ) {
                            $operation_result["file_info"]["inserted_count"] = $state["count"];
                        }
                        if ( $state["insert_flag"] == "2" ) {
                            $operation_result["file_info"]["updated_count"] = $state["count"];
                        }

                    }

                    #количество Гуудс после обновления
                    $sql = "
SELECT COUNT(*) AS `after_count`
FROM `{$VENDOR_GOODS_TABLE}`;
 ";
                    $sql = RN( $sql );
                    $q   = db_query( $sql );
                    $LAST_RES .= $sql . "<br> <em>" . $q["mysql_info"] . "</em> <br> ";
                    $row                                          = db_fetch_assoc( $q );
                    $operation_result["file_info"]["after_count"] = $row["after_count"];

# ЗАВЕРШАЕМ ПАРСИНГ И УДАЛЯЕМ ФАЙЛ
                    $sql = "
                    UPDATE `{$VENDOR_GOODS_TABLE}`
                    SET `process_flag` ='0'
                    WHERE `vendorID` = '{$vendorID}'
                    ;

                     ";

                    $sql = RN( $sql );
                    $q   = db_query( $sql );
                    $row = db_fetch_assoc( $q );
                    $LAST_RES .= $sql . "<br> <em>" . $q["mysql_info"] . "</em> <br> ";

                    $operation_result["remove_file"] = "no";

                    if ( file_exists( $file_XML_name ) &&
                        isset( $_POST["delete_file_from_server_after_parcing"] ) &&
                        ( (int)$_POST["delete_file_from_server_after_parcing"] == 1 )
                    ) {
                        unlink( $file_XML_name );
                        $smarty->assign( "file_exists_XML", 0 );
                        $operation_result["remove_file"] = "yes";
                    }

                    // console( $q );

                    $LAST_RES .= " \$process_id = 1  FINISHED ::";
# ЗАВЕРШАЕМ ПАРСИНГ И УДАЛЯЕМ ФАЙЛ

                    $operation_result["try"] = $operation;

                    $operation_result["update_info_table"]         = 1;
                    $operation_result["file_info"]["Groups_Count"] = "1";
                    $operation_result["mysql_info"]                = $LAST_RES;
                    // console( $operation_result );
                    break;

                default:
                    # code...
                    break;
            }

            $operation_result["finish"] = get_current_time();

            // ( is_array( $operation_result ) ) ? header_status( 200 ) : header_status( 405 ); // !!! не забывать заголовок
            header( "Content-Type: application/json; charset=utf-8" );
            echo json_encode( $operation_result );

        } catch ( Exception $e ) {

            // Возвращаем клиенту ответ с ошибкой
            header_status( 500 );

            header( "Content-Type: application/json; charset=utf-8" );

            echo json_encode( array(
                "message"          => $e->getMessage(),
                "Exception"        => "catch Exception " . $GOODS_PARSED_COUNT . " GOODS_PARSED_COUNT",
                // "SERVER_RESULT_STRING" => null,
                 "POST"             => $_POST,
                "GET"              => $_GET,
                "operation_result" => $operation_result,
            ) );
        }

        die();

    } else {

        $smarty->assign( "admin_sub_dpt", "import_vendor_price.tpl.html" );
    }

}
ini_set( 'display_errors', 0 ); // теперь сообщений  будет

?>