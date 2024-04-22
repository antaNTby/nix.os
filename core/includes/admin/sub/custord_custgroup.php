<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-08-23       #
#          http://nixby.pro              #
##########################################

if ( !strcmp( $sub, "custgroup" ) ) //show registered customers list
{
    if ( CONF_BACKEND_SAFEMODE != 1 && ( !isset( $_SESSION["log"] ) || !in_array( 32, $relaccess ) ) ) //unauthorized
    {
        $smarty->assign( "admin_sub_dpt", "error_forbidden.tpl.html" );
    } else {

# BEGIN Discount-for-Categories
        db_query( "CREATE TABLE IF NOT EXISTS " . DB_PRFX . "custgroups_category (
            custcatID   INT(11) NOT NULL AUTO_INCREMENT,
            categoryID  INT(11),
            custgroupID INT(11),
            discount    INT(11),
            PRIMARY KEY (custcatID),
            UNIQUE KEY custcat (custgroupID,categoryID)
            ) ENGINE=MYISAM" ) or die( mysql_error() );

        if ( isset( $_GET["catdelete"] ) ) {
            if ( CONF_BACKEND_SAFEMODE ) //this action is forbidden when SAFE MODE is ON
            {
                Redirect( ADMIN_FILE . "?dpt=custord&sub=custgroup&safemode=yes" );
            }
            db_query( "DELETE FROM " . DB_PRFX . "custgroups_category WHERE custcatID=" . $_GET["catdelete"] );
            Redirect( ADMIN_FILE . "?dpt=custord&sub=custgroup" );
        }
# END Discount-for-Categories
        if ( isset( $_GET["delete"] ) ) {
            if ( CONF_BACKEND_SAFEMODE ) //this action is forbidden when SAFE MODE is ON
            {
                Redirect( ADMIN_FILE . "?dpt=custord&sub=custgroup&safemode=yes" );
            }

            DeleteCustGroup( $_GET["delete"] );
            Redirect( ADMIN_FILE . "?dpt=custord&sub=custgroup" );
        }

        if ( isset( $_POST["save_custgroups"] ) ) {
            if ( CONF_BACKEND_SAFEMODE ) //this action is forbidden when SAFE MODE is ON
            {
                Redirect( ADMIN_FILE . "?dpt=custord&sub=custgroup&safemode=yes" );
            }
# BEGIN Discount-for-Categories
            if ( $_POST['discount_category'] != "0" && $_POST['discount_group'] != "0" ) {
                db_query( "REPLACE INTO " . DB_PRFX . "custgroups_category SET categoryID=" . $_POST['discount_category'] . ", custgroupID=" . $_POST['discount_group'] . ", discount=" . $_POST['new_custgroup_discount'] );
            }

# END Discount-for-Categories
            // add new group
            if ( trim( $_POST["new_custgroup_name"] ) != "" ) {
                AddCustGroup(
                    $_POST["new_custgroup_name"], $_POST["new_custgroup_discount"],
                    $_POST["new_sort_order"] );
            }

            // update groups
            $data = array();
            foreach ( $_POST as $key => $val ) {
# BEGIN Discount-for-Categories
                if ( strstr( $key, "custcat_" ) ) {
                    $key = str_replace( "custcat_", "", $key );
                    db_query( "UPDATE " . DB_PRFX . "custgroups_category SET discount=$val WHERE custcatID=$key" );
                }
# END Discount-for-Categories
                if ( strstr( $key, "custgroup_name_" ) ) {
                    $key                          = str_replace( "custgroup_name_", "", $key );
                    $data[$key]["custgroup_name"] = $val;
                }
                if ( strstr( $key, "custgroup_discount_" ) ) {
                    $key                              = str_replace( "custgroup_discount_", "", $key );
                    $data[$key]["custgroup_discount"] = $val;
                }
                if ( strstr( $key, "sort_order_" ) ) {
                    $key                      = str_replace( "sort_order_", "", $key );
                    $data[$key]["sort_order"] = $val;
                }
            }

            foreach ( $data as $key => $val ) {
                UpdateCustGroup(
                    $key,
                    $val["custgroup_name"],
                    $val["custgroup_discount"],
                    $val["sort_order"] );
            }

            Redirect( ADMIN_FILE . "?dpt=custord&sub=custgroup" );

        }

        // get all groups
        $custgroups = GetAllCustGroups();
# BEGIN Discount-for-Categories
        foreach ( $custgroups as $key => $val ) {
            $data = db_query( "SELECT cc.custcatID, cc.discount, c.name FROM " . DB_PRFX . "custgroups_category AS cc
                  JOIN " . CATEGORIES_TABLE . " AS c USING(categoryID)
                  WHERE custgroupID=" . $val['custgroupID'] );
            while ( $row = db_fetch_assoc( $data ) ) {
                $custgroups[$key]['cat_discount'][] = array( 'custcatID' => $row['custcatID'], 'name' => $row['name'], 'discount' => $row['discount'] );
            }

        }
        $smarty->assign( "custgroups", $custgroups );

        for ( $i = 0; $i < count( $cats ); $i++ ) {
            for ( $j = 0; $j < $cats[$i]["level"]; $j++ ) {
                $cats[$i]["name"] = "&nbsp;&nbsp;" . $cats[$i]["name"];
            }
        }
        $smarty->assign( "cats", $cats );

# END Discount-for-Categories

        //set sub template
        $smarty->assign( "admin_sub_dpt", "custord_custgroup.tpl.html" );
    }
}
?>
