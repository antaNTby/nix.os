<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-08-23       #
#          http://nixby.pro              #
##########################################

//adds new $admin_dpt to departments list
function add_department( $admin_dpt ) {
    global $admin_departments;
    $i = 0;
    while ( $i < count( $admin_departments ) && $admin_departments[$i]["sort_order"] < $admin_dpt["sort_order"] ) {
        $i++;
    }
    for ( $j = count( $admin_departments ) - 1; $j >= $i; $j-- ) {
        $admin_departments[$j + 1] = $admin_departments[$j];
    }

    $admin_departments[$i] = $admin_dpt;

}


##
##   [
##       {
##           "dpt_id": "catalog",
##           "dpt_name": "Каталог",
##           "sub_count": 11,
##           "sub_id": [
##               "products_categories",
##               "tree",
##               "dbsync"
##           ],
##           "sub_name": [
##               "Каталог товаров",
##               "jsTREE",
##               "Администрирование БД"
##           ],
##           "sub_href": [
##               "admin.php?dpt=catalog&sub=products_categories",
##               "admin.php?dpt=catalog&sub=tree",
##               "admin.php?dpt=catalog&sub=dbsync"
##           ]
##       },
##       {
##           "dpt_id": "custord",
##           "dpt_name": "Продажи",
##           "sub_count": 8,
##           "sub_id": [
##               "new_orders",
##               "order_statuses",
##               "discounts"
##           ],
##           "sub_name": [
##               "Заказы",
##               "Статусы заказов",
##               "Система скидок"
##           ],
##           "sub_href": [
##               "admin.php?dpt=custord&sub=new_orders",
##               "admin.php?dpt=custord&sub=order_statuses",
##               "admin.php?dpt=custord&sub=discounts"
##           ]
##       },
##   ]
#№
function flatAdminDepartments( $admin_departments ): array{
    $res = [];
    if ( !is_array( $admin_departments ) ) {
        return $res;
    }
    $menu = array();
    foreach ( $admin_departments as $dpt_key => $ad ) {
        $dpt_id   = $ad["id"];
        $dpt_name = $ad["name"];

        $menu[$dpt_key]["dpt_id"]    = $dpt_id;
        $menu[$dpt_key]["dpt_name"]  = $dpt_name;
        $menu[$dpt_key]["sub_count"] = count( $ad["sub_departments"] );

        $ii = 0;
        foreach ( $ad as $key => $value ) {
            if ( $key == "sub_departments" ) {
                foreach ( $value as $k => $v ) {
                    $ii++;
                    // if ( $ii > 3 ) {
                    //     break 2;
                    // }
                    $menu[$dpt_key]["sub_id"][]   = $v["id"];
                    $menu[$dpt_key]["sub_name"][] = $v["name"];
                    $menu[$dpt_key]["sub_href"][] = ADMIN_FILE . "?dpt={$dpt_id}&sub=" . $v["id"];
                    $menu[$dpt_key]["sub_href2"][] = ADMIN_FILE . "?dpt={$dpt_id}&sub=" . $v["id"];
                }
            }
        }
    }
    $res = $menu;
    return $res;
}

function CloseWindow() {
}

// *****************************************************************************
// Purpose        gets client JavaScript to open in new window
// Inputs
// Remarks
// Returns
function OpenConfigurator( $optionID, $productID ) {
    $url = ADMIN_FILE . "?do=configurator&optionID=" . $optionID . "&productID=" . $productID;
    echo ( "<script type='text/javascript'>\n" );
    echo ( "                w=450; \n" );
    echo ( "                h=400; \n" );
    echo ( "                link='" . $url . "'; \n" );
    echo ( "                var win = 'width='+w+',height='+h+',menubar=no,location=no,resizable=yes,scrollbars=yes';\n" );
    echo ( "                wishWin = window.open(link,'wishWin',win);\n" );
    echo ( "</script>\n" );
}

// *****************************************************************************
// Purpose        gets client JavaScript to reload opener page
// Inputs
// Remarks
// Returns
function ReLoadOpener() {
    if ( $_GET["productID"] == 0 ) {
        $categoryID = $_POST["categoryID"];
    } else {
        $q          = db_query( "select categoryID from " . PRODUCTS_TABLE . " where productID=" . (int)$_GET["productID"] );
        $r          = db_fetch_row( $q );
        $categoryID = $r["categoryID"];
    }
    Redirect( ADMIN_FILE . "?dpt=catalog&sub=products_categories&categoryID=" . $categoryID . "&expandCat=" . $categoryID );
}

function ReLoadOpener2( $productID ) {
    if ( isset( $productID ) ) {
        $q          = db_query( "select categoryID from " . PRODUCTS_TABLE . " where productID=" . (int)$productID );
        $r          = db_fetch_row( $q );
        $categoryID = $r["categoryID"];
    }
    Redirect( ADMIN_FILE . "?dpt=catalog&sub=products_categories&categoryID=" . $categoryID . "&expandCat=" . $categoryID );
}

function ReLoadOpener3( $productID ) {
    if ( isset( $productID ) ) {
        Redirect( ADMIN_FILE . "?productID=" . (int)$productID . "&PhotoHideTable_hidden=1&eaction=prod" );
    }
}

//deletes all subcategories of category with categoryID=$parent
function deleteSubCategories( $parent ) {
//subcategories
    $q = db_query( "select categoryID FROM " . CATEGORIES_TABLE . " WHERE parent=" . (int)$parent . " and categoryID>1" );
    while ( $row = db_fetch_row( $q ) ) {
        deleteSubCategories( $row[0] );
        //recurrent call
    }
    $q = db_query( "DELETE FROM " . CATEGORIES_TABLE . " WHERE parent=" . (int)$parent . " and categoryID>1" );
    //move all product of this category to the root category
    $q = db_query( "UPDATE " . PRODUCTS_TABLE . " SET categoryID=1 WHERE categoryID=" . (int)$parent );
}

function category_Moves_To_Its_SubDirectories( $cid, $new_parent ) {
    $a = false;
    $q = db_query( "select categoryID FROM " . CATEGORIES_TABLE . " WHERE parent=" . (int)$cid . " and categoryID>1" );
    while ( $row = db_fetch_row( $q ) ) {
        if ( !$a ) {
            if ( $row[0] == $new_parent ) {
                return true;
            } else {
                $a = category_Moves_To_Its_SubDirectories( $row[0], $new_parent );
            }

        }
    }

    return $a;
}

function _getOptions() {
    $options = optGetOptions();
    for ( $i = 0; $i < count( $options ); $i++ ) {
        if ( isset( $_GET["categoryID"] ) ) {
            $res = schOptionIsSetToSearch( $_GET["categoryID"], $options[$i]["optionID"] );
        } else {
            $res = array( "isSet" => true, "set_arbitrarily" => 1 );
        }

        if ( $res["isSet"] ) {
            $options[$i]["isSet"]           = true;
            $options[$i]["set_arbitrarily"] = $res["set_arbitrarily"];
        } else {
            $options[$i]["isSet"]           = false;
            $options[$i]["set_arbitrarily"] = 1;
        }
        $options[$i]["variants"] = optGetOptionValues( $options[$i]["optionID"] );
        for ( $j = 0; $j < count( $options[$i]["variants"] ); $j++ ) {
            $isSet = false;
            if ( isset( $_GET["categoryID"] ) ) {
                $isSet = schVariantIsSetToSearch( $_GET["categoryID"], $options[$i]["optionID"], $options[$i]["variants"][$j]["variantID"] );
            }

            $options[$i]["variants"][$j]["isSet"] = $isSet;
        }
    }
    return $options;
}

?>