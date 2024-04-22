<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-08-23       #
#          http://nixby.pro              #
##########################################

function error_reporting_log( $error_num, $error_var, $error_file, $error_line ) {
    $error_write = false;
    switch ( $error_num ) {

        case 1:
            $error_desc  = "ERROR";
            $error_write = true;
            break;

        case 2:
            $error_desc  = "WARNING";
            $error_write = true;
            break;

        case 4:
            $error_desc  = "PARSE";
            $error_write = true;
            break;

        case 8:
            $error_desc  = "NOTICE";
            $error_write = false;
            break;
    }

    if ( $error_write ) {
        if ( strpos( $error_file, "mysqli.php" ) == false && strpos( $error_file, "smarty" ) == false ) {

            $out           = "";
            $t   = explode( " ", microtime() );
            $err_timestamp = date( "Y-m-d H:i:s", $t[1] ) . substr( (string)$t[0], 1, 4 );

            $err_dump = "";
            ob_start();
            var_dump( $_GET );
            var_dump( $_POST );
            $tmpa = ob_get_contents();
            ob_end_clean();
            $err_dump .= $tmpa;

            phperrorlog( $err_timestamp ." @ " .$error_var ." \r\n " .$error_file.":".$error_line );
            // $out =$err_timestamp ." @ " .$error_var ."  " .$error_file.":".$error_line. " ====".  $err_dump;
            // $sql = "insert into " . ERROR_LOG_TABLE . " (errors, tstamp) VALUES ('" . xEscSQL(  ( $out ) ) . "', NOW())";
            // db_query( $sql );
            // $ecount = db_fetch_row( db_query( "SELECT count(*) FROM " . ERROR_LOG_TABLE ) );
            // $ecount = $ecount[0] - 100;
            // if ( $ecount > 0 ) {
            //     db_query( "DELETE FROM " . ERROR_LOG_TABLE . " ORDER BY tstamp ASC LIMIT " . $ecount );
            // }

        }
    }
}

function phperrorlog( $variable ) {
    $fp = fopen( 'errors/PHPERROR.py', 'a' );
    // $fp = fopen( 'errors\PHPERROR.py', 'a' );

    // fwrite( $fp, "\r\n" );
    fwrite( $fp, " phperrorlog \r\n" );
    fwrite( $fp, "$variable" );
    fwrite( $fp, "\r\n" );
    // fwrite( $fp, "======================== ======================== ======================== \r\n" );
    fclose( $fp );
}

set_error_handler( 'error_reporting_log' );
error_reporting( E_ALL & ~E_NOTICE );
?>