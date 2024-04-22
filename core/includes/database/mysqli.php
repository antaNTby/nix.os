<?php
#####################################
# ShopCMS: Скрипт интернет-магазина
# Copyright (c) by ADGroup
# http://shopcms.ru
#####################################

// Open phpmyadmin and goto 'More' Tab and select 'Variables' submenu. Scroll down to find sql mode. Edit sql mode and remove 'STRICT_TRANS_TABLES' Save it.
//Изменить    innodb_strict_mode  на    OFF

// Конфигурация MySQL
// Заходим в меню OSPanel: Дополнительно -> Конфигруация -> MySQL_X.X .
// Ищем строку [mysqld] и после нее вставляем sql_mode = "" ,
// затем ищем строку # InnoDB Settings
// и вставляем после неё innodb_strict_mode = OFF

// [mysqld]
// sql_mode = ""

// # InnoDB Settings
// innodb_strict_mode = OFF

class DB {
    static $link;
    static $count = 0;

    public static function connect( $host, $user, $pass, $db ) {
        @self::$link = mysqli_connect( $host, $user, $pass, $db )
        or die( 'No connect (' . mysqli_connect_errno() . ') '
            . mysqli_connect_error() );
        // mysqli_set_charset( self::$link, 'utf8' );
        mysqli_set_charset( self::$link, 'utf8mb3' );
        // self::Query("SET innodb_strict_mode=0");
    }

    public static function escape( $data ) {
        if ( is_array( $data ) ) {
            $data = array_map( 'self::escape', $data );
        } else {
            mysqli_set_charset( self::$link, 'utf8mb3' );
            $data = mysqli_real_escape_string( self::$link, $data );
        }

        return $data;
    }

    public static function Query( $sql, $print = false ) {
        self::$count++;

        $result = mysqli_query( self::$link, $sql );
        if ( $result === false || $print === 1 ) {
            $error = mysqli_error( self::$link );
            $trace = debug_backtrace();
            $out   = array( 1 => '' );

            if ( !empty( $error ) ) {
                preg_match( "#'(.+?)'#is", $error, $out );
            }

            $head = $error ? '<b style="color:red">MySQL error: </b><br>
            <b style="color:orangered">' . $error . '</b><br>' : NULL;

            $error_log = date( "Y-m-d h:i:s" ) . ' ' . $head . '
            <b>Query: </b><br>
            <pre><span style="color:#990099">'
            . str_replace( $out[1], '<b style="color:red">' . $out[1] . '</b>', $trace[0]['args'][0] )
                . '</pre></span><br>
            <b>File: </b><b style="color:#660099">' . $trace[0]['file'] . ':' . $trace[0]['line'] . '</b><br>
            <b>Line: </b><b style="color:#660099">' . $trace[0]['line'] . '</b>';
            die( $error_log );
        } else
// ..............................................
        {
            return $result;
        }

    }
}

function db_connect( $host, $user, $pass, $db ) //create connection
{
    DB::connect( $host, $user, $pass, $db );
    $r = true; //mysql_connect($host,$user,$pass);
    return $r;
}

function db_disconnect() //close connection
{
    return mysqli_close( DB::$link );
}

function db_select_db( $name ) //select database
{

    return mysqli_select_db( DB::$link, $name );
}

//database query
function db_query( $s ) {
    global $sc_4, $sc_8, $gmc;

    if ( isset( $gmc ) && $gmc == 1 ) {
        $sc_81 = gmts();
    }

    // $scriptv = gmts();
    $res = array();
    if ( isset( $_GET['speedtest'] ) ) {
        global $relaccess;
    }
    list( $usec, $sec ) = explode( " ", microtime() );
    $start            = (float)$usec + (float)$sec;
    $res["resource"]  = DB::Query( $s );
    if ( isset( $_GET['speedtest'] ) ) {
        if ( in_array( 100, $relaccess ) ) {
            list( $usec1, $sec1 ) = explode( " ", microtime() );
            $f                  = fopen( 'sql.txt', 'a' );
            fwrite( $f, round( (float)$usec1 + (float)$sec1 - $start, 5 ) . "/" . $s . "\n" );
            fclose( $f );}
    }

    /*
    $scriptp = gmts();
    $rom = $scriptp-$scriptv;
    print $rom." - ".$s."<br>";
     */

    if ( !$res['resource'] ) {
        $out = "ERROR: " . mysqli_errno( DB::$link ) . ":" . mysqli_error( DB::$link ) . "\nSql: " . $s . "\nLink: " . $_SERVER["REQUEST_URI"] . "\nDate: " . date( "d.m.y - H:i:s" ) . "\nDump:\n";
/*
ob_start();
var_dump($_GET);
var_dump($_POST);
$tmpa=ob_get_contents();
ob_end_clean();
$out .= $tmpa;
 */
        $out .= var_export( $_REQUEST, true );
        DB::Query( "insert into " . MYSQL_ERROR_LOG_TABLE . " (errors, tstamp) VALUES ('" . xEscSQL( ToText( $out ) ) . "', NOW())" );
        $ecount = mysqli_fetch_row( DB::Query( "select count(*) from " . MYSQL_ERROR_LOG_TABLE ) );
        $ecount = $ecount[0] - 50;
        if ( $ecount > 0 ) {
            DB::Query( "delete from " . MYSQL_ERROR_LOG_TABLE . " ORDER BY tstamp ASC LIMIT " . $ecount );
        }

        // die('Wrong database query!');
    }

    $res["columns"] = array();
    $column_index   = 0;

    while ( $xwer = @mysqli_fetch_field( $res["resource"] ) ) {

        $res["columns"][$xwer->name] = $column_index;
        $column_index++;
    }

    if ( isset( $gmc ) && $gmc == 1 ) {
        $sc_82 = gmts();
        $sc_4++;
        $sc_8 = $sc_8 + $sc_82 - $sc_81;
    }
    return $res;
}


function db_multiquery( $chunk, $DB_HOST = DB_HOST, $DB_USER = DB_USER, $DB_PASS = DB_PASS, $DB_NAME = DB_NAME, $AUTOCLOSE = 0 ) {
// console(  $chunk );
    $mysqli = new mysqli( $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME );
    $mysqli->set_charset( "utf8mb3" );
    // $mysqli->set_charset( "utf8mb4" );
    if ( mysqli_connect_errno() ) {
        consolelog( "db_multiquery :: connect_error: " . mysqli_connect_error() );
        // printf("xEscSQL :: Не удалось подключиться: %s\n", mysqli_connect_error());
        // $mysqli->close();
        exit();
    }

    $chunk = RN( $chunk );
    $query = trim( $chunk );

    $result = $mysqli->multi_query( $query );

    if ( $AUTOCLOSE ) {
        ## /* закрываем соединение */
        $mysqli->close();
    }
    return $result;
}








function db_fetch_row( $q ) //row fetching
{
    $res = mysqli_fetch_row( $q["resource"] );
    if ( $res ) {
        foreach ( $q["columns"] as $column_name => $column_index ) {
            $res[$column_name] = $res[$column_index];
        }

    }
    return $res;
}


function db_insert_id( $gen_name = "" ) //id of last inserted record

{
    return mysqli_insert_id( DB::$link );
}

function db_affected_rows( $gen_name = "" ) //id of last inserted record

{
    return mysqli_affected_rows( DB::$link );
}

function db_error() //database error message
{
    return mysqli_error( DB::$link );
}

function db_get_all_tables() {
    $q   = db_query( "show tables" );
    $res = array();
    while ( $row = db_fetch_row( $q ) ) {
        $res[] = strtolower( $row[0] );
    }

    return $res;
}

function db_get_all_ss_tables( $xmlFileName ) {
    $res               = array();
    $tables            = db_get_all_tables();
    $xmlNodeTableArray = GetXmlTableNodeArray( $xmlFileName );
    foreach ( $xmlNodeTableArray as $xmlNodeTable ) {
        $attr      = $xmlNodeTable->GetXmlNodeAttributes();
        $existFlag = false;
        foreach ( $tables as $tableName ) {
            if ( strtolower( $attr["NAME"] ) == $tableName ) {
                $existFlag = true;
            }

        }
        if ( $existFlag ) {
            $res[] = $attr["NAME"];
        }

    }
    return $res;
}

function db_delete_table( $tableName ) {
    db_query( "DROP table " . $tableName );
}

function db_delete_all_tables() {
    $tableArray = db_get_all_tables();
    foreach ( $tableArray as $tableName ) {
        db_query( "DROP table " . $tableName );
    }

}

function db_add_column( $tableName, $columnName, $type, $default, $nullable ) {
    if ( $nullable ) {
        $nullableStr = " NULL ";
    } else {
        $nullableStr = " NOT NULL ";
    }

    if ( $default != null ) {
        db_query( "alter table " . $tableName . " add column " . $columnName . " $type " . $nullableStr .
            " default " . $default );
    } else {
        db_query( "alter table " . $tableName . " add column " . $columnName . " $type " . $nullableStr );
    }

}

function db_rename_column( $tableName, $oldColumnName, $newColumnName, $type, $default, $nullable ) {
    if ( $nullable ) {
        $nullableStr = " NULL ";
    } else {
        $nullableStr = " NOT NULL ";
    }

    if ( $default != null ) {
        db_query( "alter table " . $tableName . " change " . $oldColumnName . " " .
            $newColumnName . " " . $type . " " . $nullableStr . " default " . $default );
    } else {
        db_query( "alter table " . $tableName . " change " . $oldColumnName . " " .
            $newColumnName . " " . $type . " " . $nullableStr );
    }

}

function db_delete_column( $tableName, $columnName ) {
    db_query( "alter table " . $tableName . " drop column " . $columnName );
}

function db_getColumns( $_TableName ) {

    $Columns = array();
    $sql     = '
                SHOW COLUMNS FROM `' . $_TableName . '`
        ';
    $Result = db_query( $sql );
    if ( !db_num_rows( $Result["resource"] ) ) {
        return $Columns;
    }

    while ( $_Row = db_fetch_row( $Result ) ) {

        $Columns[strtolower( $_Row['Field'] )] = $_Row;
    }
    return $Columns;
}
function db_num_rows( $_result ) {

    return mysqli_num_rows( $_result );
}

function db_get_server_info() {
    $res = mysqli_get_server_info( DB::$link );
    return $res;
}

function db_real_escape_string( $value ) {

    // $res = mysqli_real_escape_string( DB::$link, $value );
    $res = DB::escape( $value );
    return $res;

}

function db_phquery() {

    $args = func_get_args();
    $tmpl = array_shift( $args );
    $sql  = sql_placeholder_ex( $tmpl, $args, $error );
    if ( $sql === false ) {
        $sql = PLACEHOLDER_ERROR_PREFIX . $error;
    }

    return db_query( $sql );
}

function db_fetch_assoc( $Result ) {

    return mysqli_fetch_assoc( $Result['resource'] );
}

function db_data_seek( $Result ) {

    return mysqli_data_seek( $Result['resource'] );
}

/*
$formatt=0 последнее значение
$formatt=1 @return string Joined string
$formatt=2  массив
 */
###
function dbGetFieldData(
    $tablename,
    $fieldname,
    $where_clause,
    $order_clause = "",
    $formatt = 1
) {
    $sql = "SELECT `$fieldname` FROM `$tablename` WHERE $where_clause;";
    if ( $order_clause != "" ) {
        $sql = "SELECT `$fieldname` FROM `$tablename` WHERE $where_clause ORDER BY $order_clause;";
    }
    $q   = db_query( $sql, 0 );
    $res = null;
    if ( $formatt == 0 ) {
        $row = db_fetch_row( $q );
        $res = $row[0];
    } else {
        $data = array();
        while ( $row = db_fetch_assoc( $q ) ) {
            $data[] = $row[$fieldname]; //добавляем элемент $row в  массив
            switch ( $formatt ) {
                case 1:
                    $res = flatten( $data, "," );
                    break;
                case 2:
                    $res = array();
                    $res = $data;
                    break;
            }
        }
    }
    $result = $res;
    return $result;
}

?>