<?php
// debug_functions.php
function valueDD( $variable ) {
    if ( !isset( $variable ) ) {
        return "undefined";
    }

    $res = "";
    if ( is_null( $variable ) ) {
        $res .= "NULL";
    } elseif ( is_array( $variable ) ) {
        $res .= "<b>array</b>";
        $res .= "<ul>";
        foreach ( $variable as $key => $value ) {
            $res .= "<li>";
            $res .= "[ " . value( $key ) . " ]=" . value( $value );
            $res .= "</li>";
        }
        $res .= "</ul>";
    } elseif ( is_int( $variable ) ) {
        $res .= "<b>integer</b>&nbsp;";
        $res .= (string)$variable;
    } elseif ( is_bool( $variable ) ) {
        $res .= "<b>bool</b>&nbsp;";
        if ( $variable ) {
            $res .= "<i>True</i>";
        } else {
            $res .= "<i>False</i>";
        }
    } elseif ( is_string( $variable ) ) {
        $res .= "<b>string</b>&nbsp;";
        $res .= "'" . (string)$variable . "'";
    } elseif ( is_float( $variable ) ) {
        $res .= "<b>float</b>&nbsp;";
        $res .= (string)$variable;
    } elseif ( is_object( $variable ) ) {
        $res .= "<b>IS_OBJECT</b>&nbsp;";
        $res .= (string)$variable;
    }

    return $res;
}

function debugfile(
    $variable,
    $remark = "",
    $showTime = false
) {
    $fp = fopen( 'errors/FILEDEBUG.JSON', 'a' );

    $remark = ( strlen( $remark ) >= 1 ) ? json_encode( xHtmlSpecialCharsDecode( $remark ) ) : false;

    // $Time   = ( $showTime ) ?  get_current_time() . " " . microtime( true ) : false;
    if ( $showTime ) {
        $err_timestamp = get_microtime();
    }

    if ( $showTime ) {
        fwrite( $fp, "$err_timestamp :: " );
    }
    if ( $remark ) {
        fwrite( $fp, "  $remark \r\n" );
    }
    fwrite( $fp, "__" . gettype( $variable ) . "__  ::  \r\n" );
    fwrite( $fp, json_encode( $variable, JSON_PRETTY_PRINT | JSON_INVALID_UTF8_IGNORE | JSON_UNESCAPED_UNICODE ) );
    fwrite( $fp, "\r\n" );
    fclose( $fp );
}

function jlog(
    $text,
    $text2 = ""
) {
    debugfile( $text );
    if ( $text2 != "" ) {
        debugfile( $text2 );
    }
    return true;
}

function get_microtime(): string{
    $t         = explode( " ", microtime() );
    $timestamp = date( "Y-m-d H:i:s", $t[1] ) . substr( (string)$t[0], 1, 4 );
    return $timestamp;
}

function cls() {
    // $t             = explode( " ", microtime() );
    // $err_timestamp = date( "Y-m-d H:i:s", $t[1] ) . substr( (string)$t[0], 1, 4 );
    $fp = fopen( 'errors/FILEDEBUG.JSON', 'w+' );
    fwrite( $fp, ">>>>>>>>>> " . get_microtime() . " <<<<<<<<<<<<<<  \r\n" );
    fclose( $fp );
}

function objectlog( $obj ) {
    if ( !$obj ) {
        fwrite( $fp, " EMPTY " . "\r\n" );
        fclose( $fp );
        return false;
    }

    $ser = serialize( $obj );

    $neser = array();
    $neser = unserialize( $ser );

    $fp = fopen( 'errors/FILEDEBUG.JSON', 'a' );
    fwrite( $fp, "\r\n" );
    fwrite( $fp, "## ========================" . "\r\n" );

    foreach ( $neser as $key => $value ) {

        if ( is_array( $value ) ) {
            fwrite( $fp, $key . " => " . $value . "\r\n" );
            fwrite( $fp, " -------START {$key}-------" . "\r\n" );
            $variable = $value;
            foreach ( $variable as $k => $v ) {
                // code...
                fwrite( $fp, " || {$k} => {$v}" . "\r\n" );
            }
            fwrite( $fp, " -------END   {$key}-------" . "\r\n" );
        } else {
            fwrite( $fp, $key . " => " . $value . "\r\n" );

        }

    }
    fwrite( $fp, " ========================== ##" . "\r\n" );
    fclose( $fp );
}

function directarray( $variable ) {

    $fp = fopen( 'errors/FILEDEBUG.JSON', 'a' );
    foreach ( $variable as $key => $value ) {
        // code...
        directlog( "{$key} => {$value}" . "\r\n" );
    }
    fclose( $fp );
}

function consolelog(
    $variable,
    $gettype = 0
) {
    $fp = fopen( 'errors/FILEDEBUG.JSON', 'a' );
    fwrite( $fp, "__" . gettype( $variable ) . "__  ::  \r\n" );
    fwrite( $fp, json_encode( xHtmlSpecialCharsDecode( $variable ), JSON_PRETTY_PRINT | JSON_INVALID_UTF8_IGNORE | JSON_UNESCAPED_UNICODE ) . "\r\n" );
    fclose( $fp );
}

function console( $variable ) {
    $fp = fopen( 'errors/FILEDEBUG.JSON', 'a' );
    fwrite( $fp, json_encode( xHtmlSpecialCharsDecode( $variable ), JSON_PRETTY_PRINT | JSON_INVALID_UTF8_IGNORE | JSON_UNESCAPED_UNICODE ) . " \r\n" );
    fclose( $fp );
}

function consoleRU( $variable ) {
    $fp = fopen( 'errors/FILEDEBUG.JSON', 'a' );
    fwrite( $fp, ( $variable ) . " \r\n" );
    fclose( $fp );
}

function ddd( $variable ) {
    // if ( is_array( $variable ) ) {
    print( "<pre class='debug'>" . print_r( $variable, true ) . "</pre>" );
    // }
}

function dd(
    $variable,
    $remark = ""
) {
    echo $remark;
    if ( !isset( $variable ) ) {
        echo ( "undefined" );
    } else {
        echo "<pre class='debug'>";
        echo ( valueDD( $variable ) . "<br>" );
        echo "</pre>";
    }
}

function directlog(
    $variable,
    $gettype = 0
) {
    $fp = fopen( 'errors/FILEDEBUG.JSON', 'a' );
    fwrite( $fp, "__" . gettype( $variable ) . "__  ::  \r\n" );
    fwrite( $fp, $variable . "\r\n" );
    fclose( $fp );
}

function errorlog(
    $string,
    $showTime = 0
) {
    $fp = fopen( 'errors/PHPERROR.py', 'a' );
    if ( $showTime ) {
        fwrite( $fp, ">>>>>>>>>> " . get_microtime() . " <<<<<<<<<<<<<<  \r\n" );
    }

    fwrite( $fp, $string . "\r\n" );
    fclose( $fp );
}

?>