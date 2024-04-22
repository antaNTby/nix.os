<?php

/*
elseif ( isset( $_GET["tree"] ) && isset( $_GET["jstree"] ) && ( $_GET["tree"] === "VendorTree" || ( $_GET["tree"] == "MainTree" ) ) )

 */
$GroupsTable = $GROUPS_TABLE;
$GoodsTable  = $VENDOR_GOODS_TABLE;

$Nodes = array(
    "id"     => "ERROR",
    "parent" => "#",
    "icon"   => "fas fa-exclamation-triangle text-red lead",
    "text"   => "...ERROR:: NO TREE GENERATED...",
    "type"   => "more-goods-placeholder",
);

$operation = trim( $_GET["jstree"] );

if ( isset( $_POST["VendorTreeFilter"] ) ) {
    $GoodsFilterClause = __getGoodsFilterParams( $_POST["VendorTreeFilter"] );
    $pt_order          = __filter_order( $_POST["VendorTreeFilter"]["pt_order"] );
    // $ct_order          = __filter_order($_POST["VendorTreeFilter"]["ct_order"]);
    $pt_limit = (int)( $_POST["VendorTreeFilter"]["pt_limit"] );
} elseif ( isset( $_POST["MainTreeFilter"] ) ) {
    $MainTreeFilterClause = __getGoodsFilterParams( $_POST["MainTreeFilter"] );
    $pt_order             = __filter_order( $_POST["MainTreeFilter"]["pt_order"] );
    $ct_order             = __filter_order( $_POST["MainTreeFilter"]["ct_order"] );
    $pt_limit             = (int)( $_POST["MainTreeFilter"]["pt_limit"] );

} else {
    $GoodsFilterClause    = "";
    $MainTreeFilterClause = "";
    $pt_order             = "";
    // $ct_order             = "";
    $pt_limit = "36";
}

$sql = "";

$tree_data = array();

$tree_data = $_POST["tree_data"];

try {

    $START_PROCESS = fm_getmicrotime();
    switch ( $operation ) {

        case "GET_TREE":

            $Nodes = array();

            if ( $_GET["tree"] == "MainTree" ) {

                $Nodes = array();

                $params["pt_filter_clause"] = $MainTreeFilterClause;
                $params["pt_order"]         = $pt_order;
                $params["ct_order"]         = $ct_order;
                $params["pt_limit"]         = 1;
                $params["vendorID"]         = 4;

                $Nodes = recursivelyGetCatalogueNode( "#", $params );

            } elseif ( $_GET["tree"] == "VendorTree" ) {
                $params = array(
                    "GoodsFilterClause" => $GoodsFilterClause,
                    "goodsLIMIT"        => $pt_limit,
                    "GoodsTable"        => $GoodsTable,
                    "groupsLIMIT"       => 2000,
                    "GroupsTable"       => $GroupsTable,
                    "root_folder_id"    => 40,
                    "SKIP_GOODS"        => false,
                    "SKIP_GROUPS"       => false,
                    "SpecialFolders"    => $SpecialFolders,
                    "temp_folder_id"    => 555,
                    "vendorID"          => 4,
                );
                $Nodes = GetGoodsTree( $params );
            }

            $FINISH_PROCESS = fm_getmicrotime();
            $Nodes[]        = array(
                // "id"     => "timer",
                 "id"     => "timer" . $_GET["tree"],
                "parent" => "#",
                "text"   => "{$_GET["tree"]} {$operation} " . roundf(  ( $FINISH_PROCESS - $START_PROCESS ) * 1000 ) . " msec {$FINISH_PROCESS}",
                "type"   => "more-goods-placeholder",
                "icon"   => "fas fa-stopwatch",
            );

            $res = $Nodes;
            break;

        case "get_node":
            $Nodes = array();

            $node_id = $_POST["id"];

            if ( $_GET["tree"] == "MainTree" ) {
                if (  ( $node_id == "#" ) || ( (int)$_POST["id"] == 0 ) ) {
                    $node_id = 1;
                }

                $params["pt_filter_clause"] = $MainTreeFilterClause;
                $params["pt_order"]         = $pt_order;
                $params["ct_order"]         = $ct_order;
                $params["pt_limit"]         = $pt_limit;
                $params["vendorID"]         = 4;

                $Nodes = recursivelyGetCatalogueNode( $node_id, $params );

                $FINISH_PROCESS = fm_getmicrotime();

            } elseif ( $_GET["tree"] == "VendorTree" ) {
                $params = array(
                    "GoodsFilterClause" => $GoodsFilterClause,
                    "goodsLIMIT"        => $pt_limit,
                    "GoodsTable"        => $GoodsTable,
                    "groupsLIMIT"       => 2000,
                    "GroupsTable"       => $GroupsTable,
                    "root_folder_id"    => 40,
                    "SKIP_GOODS"        => false,
                    "SKIP_GROUPS"       => false,
                    "SpecialFolders"    => $SpecialFolders,
                    "temp_folder_id"    => 555,
                    "vendorID"          => 4,
                );
                // $START_PROCESS  = fm_getmicrotime();
                // debugfile($params);
                $Nodes          = recursivelyGetNode( $node_id, $params );
                $FINISH_PROCESS = fm_getmicrotime();
            }

            $res = $Nodes;
            break;

        case "CREATE_NODE_OPERATION":

            if ( $tree_data["type"] == "folder-empty" ) {
                if ( $_GET["tree"] == "MainTree" ) {
                    $d = array(
                        "description" => "NEW (" . FormatJSAsSQL( $tree_data["timestamp"], $ShowTime = 1 ) . "): " . $tree_data["temp_node_id"],
                        "enabled"     => 1,
                        "name"        => $tree_data["text"],
                        "parent"      => $tree_data["temp_parent"],
                        "sort_order"  => ( $tree_data["to_pos"] == "last" ) ? 10000 : 10,
                        "title"       => $tree_data["text"],
                        // "vendorID"               => $vendorID,
                        // "categoriesID_AssocLink" => 0,
                        // "group_code"                => $tree_data["timestamp"],
                        // "insert_flag"            => 1,
                        // "process_flag"           => 0,
                        // "transfer_flag"          => 0,
                    );
                    // `allow_products_comparison` int(11) DEFAULT '0',
                    // `allow_products_search` int(11) DEFAULT '1',
                    // `categoryID` int(11) NOT NULL AUTO_INCREMENT,
                    // `description` text,
                    // `enabled` int(11) DEFAULT '1' COMMENT 'Показывать категорию',
                    // `meta_description` text,
                    // `meta_keywords` text,
                    // `name` varchar(255) DEFAULT NULL,
                    // `parent` int(11) DEFAULT NULL,
                    // `picture` varchar(30) DEFAULT NULL,
                    // `products_count_admin` int(11) DEFAULT NULL,
                    // `products_count` int(11) DEFAULT NULL,
                    // `show_subcategories_products` int(11) DEFAULT '1',
                    // `sort_order` int(11) DEFAULT '0',
                    // `subcount` int(11) DEFAULT '0',
                    // `title` text,
                    // `viewed_times` int(11) DEFAULT '0',

                    $FoldersTable = CATEGORIES_TABLE;
                } else {
                    $d = array(
                        "parent"                 => $tree_data["temp_parent"],
                        "group_code"             => $tree_data["timestamp"],
                        "name"                   => $tree_data["text"],
                        "description"            => "NEW (" . FormatJSAsSQL( $tree_data["timestamp"], $ShowTime = 1 ) . "): " . $tree_data["temp_node_id"],
                        "sort_order"             => ( $tree_data["to_pos"] == "last" ) ? 10000 : 10,
                        "process_flag"           => '0',
                        "insert_flag"            => '1',
                        "transfer_flag"          => '0',
                        "enabled"                => '1',
                        "categoriesID_AssocLink" => 0,
                        "vendorID"               => $vendorID,
                    );
                    $FoldersTable = $GroupsTable;
                }

                $fieldslist    = "";
                $values_string = "";

                $fieldslist = array2apostrophes( array_keys( $d ), "`" );
                foreach ( $d as $key => $value ) {
                    $values_string = array2apostrophes( array_values( $d ), "'", "ToText" );
                }

                $sql_insert_folder = "";
                $sql_insert_folder .= "INSERT INTO `{$FoldersTable}` ({$fieldslist}) VALUES ({$values_string}); ";
                $q         = db_query( RN( $sql_insert_folder ) );
                $insert_id = db_insert_id();

            } else {
                throw new Exception( "Error Processing Request tree_data[\"type\"] == \"folder-empty\" ::CREATE_NODE_OPERATION", 1 );
            }

            // directlog( $sql_insert_folder );
            if ( $insert_id > 0 ) {

                $res = array(
                    "id"  => $insert_id,
                    "sql" => $sql_insert_folder,
                );

            } else {
                throw new Exception( "Error Processing Request insert_id ::CREATE_NODE_OPERATION", 1 );
            }

            $res = array(
                "id"  => $insert_id,
                "sql" => $sql_insert_folder,
            );
            break;

        case "RENAME_NODE_OPERATION":
            if ( $_GET["tree"] == "MainTree" ) {
                $sql_rename_node = "UPDATE `" . CATEGORIES_TABLE . "` SET `name` ='{$tree_data["new_text"]}', `title` ='{$tree_data["new_text"]}' WHERE `categoryID` = '" . (int)$tree_data["node_id"] . "' ";
            } else {
                $sql_rename_node = "UPDATE `{$GroupsTable}` SET `name` ='{$tree_data["new_text"]}' WHERE `vendorID`= '{$vendorID}' AND `id` = '" . (int)$tree_data["node_id"] . "' ";
            }
            if ( $tree_data["node_id"] > 0 ) {

                $q   = db_query( RN( $sql_rename_node ) );
                $res = array(
                    "tree_data" => $tree_data,
                    "id"        => $tree_data["node_id"],
                    "new_text"  => $tree_data["new_text"],
                    "sql"       => $sql_rename_node,
                );
            }else {
                throw new Exception( "Error Processing Request node_id ::RENAME_NODE_OPERATION", 1 );
            }

            break;

        case 'TRANSFER_PRODUCT':
            if ( $_GET["tree"] == "MainTree" && $tree_data["is_multi"] == true ) {
                $d = array(
                    "description" => "NEW (" . FormatJSAsSQL( $tree_data["timestamp"], $ShowTime = 1 ) . "): " . $tree_data["originalnode_id"],
                    "enabled"     => 1,
                    "name"        => $tree_data["text"],
                    "categoryID"  => $tree_data["new_parent"],
                    "sort_order"  => ( $tree_data["to_pos"] == "last" ) ? 10000 : 10,
                    "title"       => $tree_data["text"],

                );

                $sql_get_goods_data = "SELECT * FROM `{$GoodsTable}` WHERE `id`='" . abs( $tree_data['originalnode_id'] ) . "';";
                $q                  = db_query( $sql_get_goods_data );
                $good_data          = db_fetch_assoc( $q );
                // function AddProduct
                $newP = array(
                    "categoryID"              => $tree_data["new_parent"],
                    "name"                    => $good_data["name"],
                    "Price"                   => roundf( $good_data["price"] * 1.16 ),
                    "description"             => "",
                    "in_stock"                => __qty2in_stock( $good_data["qty"] ),
                    "brief_description"       => "",
                    "list_price"              => roundf( $good_data["price"] * 1.00 ),
                    "product_code"            => $good_data["good_code"] . "." . $vendorID,
                    "sort_order"              => ( $tree_data["to_pos"] == "last" ) ? 10000 : 10,
                    "ProductIsProgram"        => $ProductIsProgram,
                    "eproduct_filename"       => null,
                    "eproduct_available_days" => null,
                    "eproduct_download_times" => null,
                    "weight"                  => 0.0,
                    "meta_description"        => "",
                    "meta_keywords"           => "",
                    "free_shipping"           => 0,
                    "min_order_amount"        => 1,
                    "shipping_freight"        => 0.0,
                    "classID"                 => CONF_DEFAULT_TAX_CLASS,
                    "title"                   => CONF_SHOP_URL . " купить " . $good_data["name"],
                    "updateGCV"               => 0,
                    "vendorID"                => $vendorID,
                    "AS_SQL"                  => 0,
                );
                // console( $newP );
                $productID = prdAddProductFromData( $newP, $updateGCV = 0, $AS_SQL = 0 );

                /*
                // function _importExtraOptionValues( $row, $productID, $updated_extra_option )
                // PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE
                if ( !defined( 'PRODUCT_OPTIONS_TABLE' ) ) {
                define( 'PRODUCT_OPTIONS_TABLE', 'UTF_product_options' );}
                if ( !defined( 'PRODUCT_OPTIONS_VALUES_TABLE' ) ) {
                define( 'PRODUCT_OPTIONS_VALUES_TABLE', 'UTF_product_options_values' );}
                if ( !defined( 'PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE' ) ) {
                define( 'PRODUCTS_OPTIONS_VALUES_VARIANTS_TABLE', 'UTF_products_opt_val_variants' );
                }
                if ( !defined( 'PRODUCTS_OPTIONS_SET_TABLE' ) ) {
                define( 'PRODUCTS_OPTIONS_SET_TABLE', 'UTF_product_options_set' );
                }
                 */

                $VendorOptions = array(

                    "1" => array( "Vendor" => "Minsk4" ),
                    "2" => array( "Brand" => ( trim( $good_data["man"] ) ) ),
                    "3" => array( "Model" => ( trim( $good_data["article"] ) ) ),
                    // "4" => array( "Type" => -1 ),
                     "5" => array( "ean" => ( trim( $good_data["ean"] ) ) ),

                );

                setOptonValuesForTransferedProduct( $productID, $VendorOptions );

                $sql_aftertransfer = "";

                $res = array(
                    "id"             => -1 * $productID,
                    "tree_container" => $tree_data["target_tree"],
                    "sql"            => $sql_transfer_good,
                );
            }
            break;

        case 'MOVE_PRODUCT':

            if ( !$tree_data["is_multi"] ) {
                # code...
                if ( $_GET["tree"] == "MainTree" ) {
                    $sql_move_product = "
                            UPDATE `" . PRODUCTS_TABLE . "` SET `categoryID` ='{$tree_data["new_parent"]}'
                            WHERE
                            `productID` = '" . abs( $tree_data["node_id"] ) . "'
                            AND `categoryID` = '{$tree_data["old_parent"]}' ";
                } else {

                    $sql_move_product = "
                            UPDATE `" . $GoodsTable . "` SET `group_id` ='{$tree_data["new_parent"]}'
                            WHERE `vendorID`= '{$vendorID}'
                            AND `id` = '" . abs( $tree_data["node_id"] ) . "'
                            AND `group_id` = '{$tree_data["old_parent"]}' ";
                }

                $q = db_query( RN( $sql_move_product ) );

                if ( !!$q ) {
                    $res = array(
                        "new_group_id" => $tree_data["new_parent"],
                        "old_group_id" => $tree_data["old_parent"],
                        "sql"          => $sql_move_product,
                    );
                }
            } else {

                throw new Exception( "Error Processing Request::MOVE_PRODUCT", 1 );

                $res = array(
                    "new_group_id" => $tree_data["new_parent"],
                    "old_group_id" => $tree_data["old_parent"],
                    "sqlERROR"     => $sql_move_product,
                );
            }

            break;

        case 'MOVE_FOLDER':

            if ( !$tree_data["is_multi"] ) {

                if ( $_GET["tree"] == "MainTree" ) {
                    $sql_move_folder = "
                            UPDATE  `" . CATEGORIES_TABLE . "`
                            SET `parent` ='{$tree_data["new_parent"]}'
                            WHERE
                            `categoryID` = '" . (int)$tree_data["node_id"] . "'
                            AND `parent` = '{$tree_data["old_parent"]}' ";
                } else {
                    $sql_move_folder = "
                            UPDATE `{$GroupsTable}`
                            SET `parent` ='{$tree_data["new_parent"]}'
                            WHERE `vendorID`= '{$vendorID}'
                            AND `id` = '" . (int)$tree_data["node_id"] . "'
                            AND `parent` = '{$tree_data["old_parent"]}' ";
                }

                $q = db_query( $sql_move_folder );
                if ( !!$q ) {
                    $res = array(
                        "new_category_parent_id" => $tree_data["new_parent"],
                        "old_category_parent_id" => $tree_data["old_parent"],
                        "sql"                    => $sql_move_folder,
                    );
                }
            } else {

                throw new Exception( "Error Processing Request::MOVE_FOLDER", 1 );

                $res = array(
                    "new_group_id" => $tree_data["new_parent"],
                    "old_group_id" => $tree_data["old_parent"],
                    "sql"          => $sql_move_product,
                );
            }
            break;

        case "OTHER_NODE_OPERATION":

            $res              = array();
            $res["tree_data"] = $tree_data;
            $res["operation"] = $operation;

            $TargetTree = $_GET["tree"];

            $GroupsTable = $GROUPS_TABLE;
            $GoodsTable  = $VENDOR_GOODS_TABLE;

            $sql           = "";
            $node_id       = $tree_data["node_id"];
            $tree_action   = trim( $tree_data["action"] );
            $res["action"] = $tree_action;
            if ( $node_id < 0 ) {
                throw new Exception( 'Товар не может быть родительским узлом' );
            } elseif ( $node_id > 0 ) {

                $Sql_Array = generateSqlStringArray( $node_id, $GoodsTable, $GroupsTable, $TargetTree, $vendorID );

                if ( isset( $Sql_Array[$tree_action]["child_good"] ) ) {
                    $sql .= ( $Sql_Array[$tree_action]["child_good"] );
                }
                if ( isset( $Sql_Array[$tree_action]["child_folder"] ) ) {
                    $sql .= ( $Sql_Array[$tree_action]["child_folder"] );
                }
            } else {
                throw new Exception( " неведома зверушка" );
            }

            if ( $sql != "" ) {
                // directlog( $sql );
                $q = db_mysqli( $sql );
            } else {
                throw new Exception( "Пустой запрос" );
            }

            if ( !!$q ) {
                $res["sql"] = $sql;
            } else {
                throw new Exception( "Ошибка при выполнении запроса \r\n {$sql}" );
            }
            break;

        case "DELETE_NODE_OPERATION":

            $res              = array();
            $res["tree_data"] = $tree_data;
            $res["operation"] = $operation;

            $TargetTree = $_GET["tree"];

            $GroupsTable = $GROUPS_TABLE;
            $GoodsTable  = $VENDOR_GOODS_TABLE;

            $node_id       = $tree_data["node_id"];
            $tree_action   = trim( $tree_data["action"] );
            $res["action"] = $tree_action;
            $sql           = "";

            if ( $node_id < 0 ) {
                # скрытие/удаление одного товара , у него не может быть детей
                $_node     = $node_id;
                $Sql_Array = generateSqlStringArray( $_node, $GoodsTable, $GroupsTable, $TargetTree, $vendorID );
                if ( isset( $Sql_Array[$tree_action]["single_good"] ) ) {
                    $sql .= trim( $Sql_Array[$tree_action]["single_good"] );
                }
                if ( $sql != "" ) {
                    $q = db_mysqli( $sql );

                    if ( !!$q ) {
                        $res["single_good"] = $_node;
                        $res["sql"]         = $sql;
                    }
                } else {
                    throw new Exception( " Пустой запрос" );
                }
            } elseif ( $node_id > 0 ) {
                # скрытие/удаление одной папки , у неё могут быть дети, которые могут буть как товарами так и папками
                $Children_d = $tree_data["node_children_d"];
                if ( is_array( $Children_d ) && count( $Children_d ) > 0 ) {
                    $ChildrenReversed = array_reverse( $Children_d );
                    foreach ( $ChildrenReversed as $child_id ) {
                        # удаляем детей-папок
                        $Sql_Array = generateSqlStringArray( $child_id, $GoodsTable, $GroupsTable, $TargetTree, $vendorID );
                        if ( $child_id < 0 ) {
                            # скрытие/удаление одного товара , у него не может быть детей
                            if ( isset( $Sql_Array[$tree_action]["single_good"] ) ) {
                                $sql .= ( $Sql_Array[$tree_action]["single_good"] );
                            }
                            $res["single_good"][] = $child_id;
                        } elseif ( $child_id > 0 ) {

                            if ( isset( $Sql_Array[$tree_action]["single_folder"] ) ) {
                                $sql .= ( $Sql_Array[$tree_action]["single_folder"] );
                            }
                            $res["folder"][] = $child_id;
                        } else {
                            throw new Exception( " неведома зверушка" );
                        }
                    }
                }
                # скрытие/удаление уже самой папки у которой нет детей
                $Sql_Array = generateSqlStringArray( $node_id, $GoodsTable, $GroupsTable, $TargetTree, $vendorID );
                if ( isset( $Sql_Array[$tree_action]["single_folder"] ) ) {
                    $sql .= ( $Sql_Array[$tree_action]["single_folder"] );
                }
                $res["node_folder"] = $node_id;
            } else {
                $res = array(
                    "root_error" => "Это корень или левая папка или левый товар",
                    "node_id"    => $node_id,
                );
                throw new Exception( " неведома зверушка" );
            }
            if ( $sql != "" ) {
                $q = db_mysqli( $sql );
            } else {
                throw new Exception( " Пустой запрос" );
            }

            if ( is_array( $Children_d ) && count( $Children_d ) > 0 ) {
                $res["ChildrenReversed"] = implode( ", ", $ChildrenReversed );
            }

            if ( !!$q ) {
                $res["sql"] = $sql;
            } else {
                throw new Exception( "Ошибка при выполнении запроса \r\n {$sql}" );
            }
            break;

        case "DELETE_TRANFEREDNODE_OPERATION":

            $res              = array();
            $res["tree_data"] = $tree_data;
            $res["operation"] = $operation;

            $TargetTree  = $_GET["tree"];
            $GroupsTable = $GROUPS_TABLE;
            $GoodsTable  = $VENDOR_GOODS_TABLE;

            $node_id       = $tree_data["node_id"];
            $tree_action   = trim( $tree_data["action"] );
            $res["action"] = $tree_action;
            $sql           = "";

            if ( $node_id < 0 ) {
                # скрытие/удаление одного товара , у него не может быть детей
                $_node     = $node_id;
                $Sql_Array = generateSqlStringArray( $_node, $GoodsTable, $GroupsTable, $TargetTree, $vendorID );
                if ( isset( $Sql_Array[$tree_action]["single_good"] ) ) {
                    $sql .= trim( $Sql_Array[$tree_action]["single_good"] );
                }
                if ( $sql != "" ) {
                    $q = db_mysqli( $sql );

                    if ( !!$q ) {
                        $res["single_good"] = $_node;
                        $res["sql"]         = $sql;
                    }
                } else {
                    throw new Exception( " Пустой запрос" );
                }
            } elseif ( $node_id > 0 ) {
                # скрытие/удаление одной папки , у неё могут быть дети, которые могут буть как товарами так и папками
                $Children_d = $tree_data["node_children_d"];
                if ( is_array( $Children_d ) && count( $Children_d ) > 0 ) {
                    $ChildrenReversed = array_reverse( $Children_d );
                    foreach ( $ChildrenReversed as $child_id ) {
                        # удаляем детей-папок
                        $Sql_Array = generateSqlStringArray( $child_id, $GoodsTable, $GroupsTable, $TargetTree, $vendorID );
                        if ( $child_id < 0 ) {
                            # скрытие/удаление одного товара , у него не может быть детей
                            if ( isset( $Sql_Array[$tree_action]["single_good"] ) ) {
                                $sql .= ( $Sql_Array[$tree_action]["single_good"] );
                            }
                            $res["single_good"][] = $child_id;
                        } elseif ( $child_id > 0 ) {

                            if ( isset( $Sql_Array[$tree_action]["single_folder"] ) ) {
                                $sql .= ( $Sql_Array[$tree_action]["single_folder"] );
                            }
                            $res["folder"][] = $child_id;
                        } else {
                            throw new Exception( " неведома зверушка" );
                        }
                    }
                }
                # скрытие/удаление уже самой папки у которой нет детей
                $Sql_Array = generateSqlStringArray( $node_id, $GoodsTable, $GroupsTable, $TargetTree, $vendorID );
                if ( isset( $Sql_Array[$tree_action]["single_folder"] ) ) {
                    $sql .= ( $Sql_Array[$tree_action]["single_folder"] );
                }
                $res["node_folder"] = $node_id;
            } else {
                $res = array(
                    "root_error" => "Это корень или левая папка или левый товар",
                    "node_id"    => $node_id,
                );
                throw new Exception( " неведома зверушка" );
            }

            if ( $sql != "" ) {
                $q = db_mysqli( $sql );
            } else {
                throw new Exception( " Пустой запрос" );
            }

            if ( is_array( $Children_d ) && count( $Children_d ) > 0 ) {
                $res["ChildrenReversed"] = implode( ", ", $ChildrenReversed );
            }

            if ( !!$q ) {
                $res["sql"] = $sql;
            } else {
                throw new Exception( "Ошибка при выполнении запроса \r\n {$sql}" );
            }

            break;

        default:
            throw new Exception( "Ошибка LALALA {$sql}" );

            break;
    }

    ( $res ) ? header_status( 200 ) : header_status( 403 ); // !!! не забывать заголовок
    if ( !$res && $operation == "get_node" ) {
        // throw new Exception( " Пустой ответ" );
        header_status( 204 );
    }

    header( "Content-Type: application/json; charset=utf-8" );
    echo json_encode( $res );

} catch ( Exception $e ) {
    // Возвращаем клиенту ответ с ошибкой
    header_status( 416 );
    header( "Content-Type: application/json; charset=utf-8" );
    echo json_encode( array(
        "message"         => $e->getMessage(),
        "operationResult" => null,
        "operation"       => $operation,
    ) );
}

die();
