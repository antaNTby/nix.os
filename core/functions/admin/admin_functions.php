<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-08-23       #
#          http://nixby.pro              #
##########################################

function GetAllAdminAttributes() {
    $q    = db_query( "SELECT customerID, Login, actions FROM " . CUSTOMERS_TABLE . " WHERE actions!='' ORDER BY customerID ASC" );
    $data = array();
    while ( $row = db_fetch_row( $q ) ) {
        $row[2] = unserialize( $row[2] );
        $row["perm_count"] = count($row[2]);
        if ( in_array( 100, $row[2] ) ) {
            $data[] = $row;
        }

    }
    return $data;
}

function CheckLoginAdminNew( $login ) {
    $q    = db_query( "SELECT count(*) from " . CUSTOMERS_TABLE . " WHERE Login='" . xEscSQL( $login ) . "'" );
    $n    = db_fetch_row( $q );
    $data = $n[0];
    return $data;
}

function adminpgGetadminPage( $admin_ID ) {
    $q      = db_query( "SELECT Login, actions FROM " . CUSTOMERS_TABLE . " WHERE customerID=" . (int)$admin_ID  . " ORDER BY customerID ASC" );
    $row    = db_fetch_row( $q );
    $row[1] = unserialize( $row[1] );
    return $row;
}

function UpdateAdminRights( $edit_num, $actions ) {
    $actions[] = 100;
    $actions   = xEscSQL( serialize( $actions ) );
    db_query( "UPDATE " . CUSTOMERS_TABLE . " SET actions='" . $actions . "' WHERE customerID=" . (int)$edit_num );
}

function adminpgDeleteadmin( $admin_page_ID ) {
    db_query( "DELETE FROM " . CUSTOMERS_TABLE . " WHERE customerID=" . (int)$admin_page_ID );
}

?>