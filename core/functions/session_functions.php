<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-08-23       #
#          http://nixby.pro              #
##########################################

function sess_open( $save_path, $session_name ) {
    return true;
}

function sess_close() {
    return true;
}

function sess_read( $key ) {
    $r = db_query( "SELECT data, IP FROM " . SESSION_TABLE . " WHERE id='" . addslashes( $key ) . "'" );
    if ( !$r ) {
        return "";
    } else {
        $result = db_fetch_row( $r );
        if ( !empty( $result ) ) {
            if ( CONF_SECURE_SESSIONS ) {
                if ( stGetCustomerIP_Address() != $result[1] ) {
                    db_query( "DELETE FROM " . SESSION_TABLE . " WHERE id='" . addslashes( $key ) . "'" );
                    return "";
                }
            }
            return $result[0];
        } else {
            return "";
        }
    }
}

function sess_write( $key, $val ) {
    db_query( "REPLACE INTO " . SESSION_TABLE . " VALUES ('" . addslashes( $key ) . "', '" . addslashes( $val ) . "', UNIX_TIMESTAMP() + " . SECURITY_EXPIRE . ", '" . addslashes( stGetCustomerIP_Address() ) . "', '" . addslashes( $_SERVER["HTTP_REFERER"] ) . "', '" . addslashes( $_SERVER["HTTP_USER_AGENT"] ) . "', '" . addslashes( $_SERVER["REQUEST_URI"] ) . "')" );
    return true;
}

function sess_destroy( $key ) {
    db_query( "DELETE FROM " . SESSION_TABLE . " WHERE id='" . addslashes( $key ) . "'" );
    return true;
}

function sess_gc( $maxlifetime ) {
    db_query( "DELETE FROM " . SESSION_TABLE . " WHERE expire < UNIX_TIMESTAMP()" );
    return true;
}

?>