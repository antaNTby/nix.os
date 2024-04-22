<?php

/*
 * Helper functions for building a DataTables server-side processing SQL query
 *
 * The static functions in this class are just helper functions to help build
 * the SQL used in the DataTables demo server-side processing scripts. These
 * functions obviously do not represent all that can be done with server-side
 * processing, they are intentionally simple to show how it works. More complex
 * server-side processing operations will likely require a custom script.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */

// REMOVE THIS BLOCK - used for DataTables test environment only!
// $file = $_SERVER['DOCUMENT_ROOT'] . '/datatables/pdo.php';
// if ( is_file( $file ) ) {
//     include $file;
// }

class SSP {
    /**
     * Create the data output array for the DataTables rows
     *
     *  @param  array $columns Column information array
     *  @param  array $data    Data from the SQL get
     *  @return array          Formatted data in a row based format
     */
    public static function data_output( $columns, $data ) {
        $out = array();

        for ( $i = 0, $ien = count( $data ); $i < $ien; $i++ ) {
            $row = array();

            for ( $j = 0, $jen = count( $columns ); $j < $jen; $j++ ) {
                $column = $columns[$j];

                // Is there a formatter?
                if ( isset( $column['formatter'] ) ) {
                    $row[$column['dt']] = $column['formatter']( $data[$i][$column['db']], $data[$i] );
                } else {
                    $row[$column['dt']] = $data[$i][$columns[$j]['db']];
                }
            }

            $out[] = $row;
        }

        return $out;
    }

    /**
     * Database connection
     *
     * Obtain an PHP PDO connection from a connection details array
     *
     *  @param  array $conn SQL connection details. The array should have
     *    the following properties
     *     * host - host name
     *     * db   - database name
     *     * user - user name
     *     * pass - user password
     *  @return resource PDO connection
     */
    public static function db( $conn ) {
        if ( is_array( $conn ) ) {
            return self::sql_connect( $conn );
        }

        return $conn;
    }

    /**
     * Paging
     *
     * Construct the LIMIT clause for server-side processing SQL query
     *
     *  @param  array $request Data sent to server by DataTables
     *  @param  array $columns Column information array
     *  @return string SQL limit clause
     */
    public static function limit( $request, $columns ) {
        $limit = '';

        if ( isset( $request['start'] ) && $request['length'] != -1 ) {
            $limit = "LIMIT " . intval( $request['start'] ) . ", " . intval( $request['length'] );
        }

        return $limit;
    }

    /**
     * Ordering
     *
     * Construct the ORDER BY clause for server-side processing SQL query
     *
     *  @param  array $request Data sent to server by DataTables
     *  @param  array $columns Column information array
     *  @return string SQL order by clause
     */
    public static function order( $request, $columns ) {
        $order = '';

        if ( isset( $request['order'] ) && count( $request['order'] ) ) {
            $orderBy   = array();
            $dtColumns = self::pluck( $columns, 'dt' );
            for ( $i = 0, $ien = count( $request['order'] ); $i < $ien; $i++ ) {
                // Convert the column index into the column data property
                $columnIdx     = intval( $request['order'][$i]['column'] );
                $requestColumn = $request['columns'][$columnIdx];
                $columnIdx     = array_search( $requestColumn['data'], $dtColumns );
                $column        = $columns[$columnIdx];
                if ( $requestColumn['orderable'] == 'true' ) {
                    $dir = $request['order'][$i]['dir'] === 'asc' ?
                    'ASC' :
                    'DESC';
                    $orderBy[] = '`' . $column['db'] . '` ' . $dir;
                }
            }
            if ( count( $orderBy ) ) {
                $order = 'ORDER BY ' . implode( ', ', $orderBy );
            }
        }
        return $order;
    }

    /**
     * Searching / Filtering
     *
     * Construct the WHERE clause for server-side processing SQL query.
     *
     * NOTE this does not match the built-in DataTables filtering which does it
     * word by word on any field. It's possible to do here performance on large
     * databases would be very poor
     *
     *  @param  array $request Data sent to server by DataTables
     *  @param  array $columns Column information array
     *  @param  array $bindings Array of values for PDO bindings, used in the
     *    sql_exec() function
     *  @return string SQL where clause
     */
    public static function filter( $request, $columns, &$bindings ) {
        $globalSearch = array();
        $columnSearch = array();
        $dtColumns    = self::pluck( $columns, 'dt' );

        if ( isset( $request['search'] ) && $request['search']['value'] != '' ) {
            $str = $request['search']['value'];

            for ( $i = 0, $ien = count( $request['columns'] ); $i < $ien; $i++ ) {
                $requestColumn = $request['columns'][$i];
                $columnIdx     = array_search( $requestColumn['data'], $dtColumns );
                $column        = $columns[$columnIdx];

                if ( $requestColumn['searchable'] == 'true' ) {
                    $binding        = self::bind( $bindings, '%' . $str . '%', PDO::PARAM_STR );
                    $globalSearch[] = "`" . $column['db'] . "` LIKE " . $binding;
                }
            }
        }

        // Individual column filtering
        if ( isset( $request['columns'] ) ) {
            for ( $i = 0, $ien = count( $request['columns'] ); $i < $ien; $i++ ) {
                $requestColumn = $request['columns'][$i];
                $columnIdx     = array_search( $requestColumn['data'], $dtColumns );
                $column        = $columns[$columnIdx];

                $str = $requestColumn['search']['value'];

                if ( $requestColumn['searchable'] == 'true' &&
                    $str != '' ) {
                    $binding        = self::bind( $bindings, '%' . $str . '%', PDO::PARAM_STR );
                    $columnSearch[] = "`" . $column['db'] . "` LIKE " . $binding;
                }
            }
        }

        // Combine the filters into a single string
        $where = '';

        if ( count( $globalSearch ) ) {
            $where = '(' . implode( ' OR ', $globalSearch ) . ')';
        }

        if ( count( $columnSearch ) ) {
            $where = $where === '' ?
            implode( ' AND ', $columnSearch ) :
            $where . ' AND ' . implode( ' AND ', $columnSearch );
        }

        if ( $where !== '' ) {
            $where = 'WHERE ' . $where;
        }

        return $where;
    }

    /**
     * Perform the SQL queries needed for an server-side processing requested,
     * utilising the helper functions of this class, limit(), order() and
     * filter() among others. The returned array is ready to be encoded as JSON
     * in response to an SSP request, or can be modified if needed before
     * sending back to the client.
     *
     *  @param  array $request Data sent to server by DataTables
     *  @param  array|PDO $conn PDO connection resource or connection parameters array
     *  @param  string $table SQL table to query
     *  @param  string $primaryKey Primary key of the table
     *  @param  array $columns Column information array
     *  @return array          Server-side processing response array
     */
    public static function simple( $request, $conn, $table, $primaryKey, $columns ) {
        $bindings = array();
        $db       = self::db( $conn );

        // Build the SQL query string from the request
        $limit = self::limit( $request, $columns );
        $order = self::order( $request, $columns );
        $where = self::filter( $request, $columns, $bindings );

        // Main query to actually get the data
        $data = self::sql_exec( $db, $bindings,
            "SELECT `" . implode( "`, `", self::pluck( $columns, 'db' ) ) . "`
             FROM `$table`
             $where
             $order
             $limit"
        );

        // Data set length after filtering
        $resFilterLength = self::sql_exec( $db, $bindings,
            "SELECT COUNT(`{$primaryKey}`)
             FROM   `$table`
             $where"
        );
        $recordsFiltered = $resFilterLength[0][0];

        // Total data set length
        $resTotalLength = self::sql_exec( $db,
            "SELECT COUNT(`{$primaryKey}`)
             FROM   `$table`"
        );
        $recordsTotal = $resTotalLength[0][0];

        /*
         * Output
         */
        $result = array(
            "draw"            => isset( $request['draw'] ) ? intval( $request['draw'] ) : 0,
            "recordsTotal"    => intval( $recordsTotal ),
            "recordsFiltered" => intval( $recordsFiltered ),
            "data"            => self::data_output( $columns, $data ),
        );
        return $result;
    }

    /**
     * The difference between this method and the `simple` one, is that you can
     * apply additional `where` conditions to the SQL queries. These can be in
     * one of two forms:
     *
     * * 'Result condition' - This is applied to the result set, but not the
     *   overall paging information query - i.e. it will not effect the number
     *   of records that a user sees they can have access to. This should be
     *   used when you want apply a filtering condition that the user has sent.
     *
     * * 'All condition' - This is applied to all queries that are made and
     *   reduces the number of records that the user can access. This should be
     *   used in conditions where you don't want the user to ever have access to
     *   particular records (for example, restricting by a login id).
     *
     *  @param  array $request Data sent to server by DataTables
     *  @param  array|PDO $conn PDO connection resource or connection parameters array
     *  @param  string $table SQL table to query
     *  @param  string $primaryKey Primary key of the table
     *  @param  array $columns Column information array
     *  @param  string $whereResult WHERE condition to apply to the result set
     *  @param  string $whereAll WHERE condition to apply to all queries
     *  @return array          Server-side processing response array
     */

    private static function __catname( $id ) {
// global $antcats;
        // $res= $antcats[(int)$id];
        global $fc;
        $res = $fc[(int)$id]["name"];
        return $res;
    }

    private static function __generateClauses( $params ) {

    }

    public static function idxCol( $columns, $needle, $prop = "dt" ) {
        $keys  = pluck( $columns, $prop );
        $index = 0;
        if (  ( $i = array_search( $needle, ( $keys ) ) ) !== FALSE ) {
            $index = $i;
        }
        return $index;
    }

    public static function getProductsData( $request, $conn, $table, $primaryKey, $columns, $whereResult = null, $wherePriceLimits = null, $whereTotal = null ) {

        $bindings         = array();
        $db               = self::db( $conn );
        $localWhereResult = array();
        $localWhereAll    = array();
        $whereTotalSql    = '';

        // Build the SQL query string from the request
        $limit = self::limit( $request, $columns );
        $order = self::order( $request, $columns );
        $where = self::filter( $request, $columns, $bindings );

        $whereResult = self::flatten( $whereResult );
        $whereTotal  = self::flatten( $whereTotal );

        if ( $whereResult ) {
            $where = $where ?
            $where . ' AND ' . $whereResult :
            'WHERE ' . $whereResult;
        }

        if ( $whereTotal ) {
            $where = $where ?
            $where . ' AND ' . $whereTotal :
            'WHERE ' . $whereTotal;
            $whereTotalSql = 'WHERE ' . $whereTotal;
        }

        if ( $wherePriceLimits ) {
            $NoPriceLimits_where = $where;
            $where               = $where ?
            $where . ' AND ' . $wherePriceLimits :
            'WHERE ' . $wherePriceLimits;
        }

        // Main query to actually get the data
        $sql_query = "SELECT `" . implode( "`, `", self::pluck( $columns, 'db' ) ) . "`
             FROM `{$table}`
             {$where}
             {$order}
             {$limit}";


        $sql_query = VS( RN( $sql_query ) );
        $data      = self::sql_exec( $db, $bindings, $sql_query );

        if ( isset( $request["params"]["categoryID"] ) ) {
            // Main query to actually get the data.categoryID
            $sql_query2 = "SELECT `categoryID`
             FROM `{$table}`
             {$where}";
            $data2 = self::sql_exec( $db, $bindings, $sql_query2 );
            $catsA = self::pluck( $data2, 'categoryID' );
            $catsB = array_unique( $catsA );
            $cats  = array();
            $ii    = 0;
            foreach ( $catsB as $key => $value ) {
                $ii++;
                $cats["{$value}"] = catname( $value );
            }
        }

        if ( $NoPriceLimits_where ) {

            $priceLimits = self::getPriceLimits( $db, $bindings, $table, $request["params"]["tpl_currency_value"], $NoPriceLimits_where );
        }

        // Data set length after filtering
        $resFilterLength = self::sql_exec( $db, $bindings,
            "SELECT COUNT(`{$primaryKey}`)
             FROM   `{$table}`
             {$where}"
        );
        $recordsFiltered = $resFilterLength[0][0];

        // Total data set length
        $resTotalLength = self::sql_exec( $db, $bindings,
            "SELECT COUNT(`{$primaryKey}`)
             FROM   `$table` " .
            $whereTotalSql
        );
        $recordsTotal = $resTotalLength[0][0];

        /*
         * Output
         */

        $res = array(
            "sql_query"       => $sql_query,
            "count_of_data"   => count( $data ),
            "start"           => $request['start'],
            "length"          => $request['length'],
            "draw"            => isset( $request['draw'] ) ? intval( $request['draw'] ) : 0,
            "recordsTotal"    => intval( $recordsTotal ),
            "recordsFiltered" => intval( $recordsFiltered ),
            "data"            => self::data_output( $columns, $data ),
            "cats"            => $cats,
            "cats_count"      => $ii,
            "priceLimits"     => $priceLimits,
            // "bindings"          => $bindings,
            // "NoPriceLimits_where" => $NoPriceLimits_where,
            // "sql_queryFile" => directlog( $sql_query ),
            // "table"           => $table,
            // "newPriceLimits"  => $priceLimits,
            // "whereResult"     => $whereResult,
            // "whereTotal"        => $whereTotal,
            // "POST" =>consolelog($request['order']),
            // "POST_col" =>consolelog($request['columns']),
        );

        return $res;

    }

    public static function getPriceLimits( $db, $bindings, $table, $currency_value, $where_clause ) {
        $priceLimits = array();

        $sql_query = "SELECT
            MIN(Price) AS price_from,
            MAX(Price) AS price_to,
            COUNT(productID) AS recordsFiltered
            FROM `{$table}`
            {$where_clause}";
        $sql_query = VS( RN( $sql_query ) );

        $R           = self::sql_exec_assoc( $db, $bindings, $sql_query );
        $priceLimits = $R[0];

        $Min = minLimit( $priceLimits["price_from"] );
        $Max = maxLimit( $priceLimits["price_to"] );

        $FloatPrice_from_out = $priceLimits["price_from"] * $currency_value;
        $price_from_out      = roundf( $FloatPrice_from_out, 2 );
        $FloatPrice_to_out   = $priceLimits["price_to"] * $currency_value;
        $price_to_out        = roundf( $FloatPrice_to_out, 2 );

        $Min_out = minLimit( $price_from_out );
        $Max_out = maxLimit( $price_to_out );

        $priceLimits["price_from_out"] = $price_from_out;
        $priceLimits["price_to_out"]   = $price_to_out;
        $priceLimits["Min_out"]        = $Min_out;
        $priceLimits["Max_out"]        = $Max_out;

        // $priceLimits["getPriceLimits_query"] = $sql_query;

        return $priceLimits;
    }

    /**
     * Connect to the database
     * @param  array $sql_details SQL server connection details array, with the
     *   properties:
     *     * host - host name
     *     * db   - database name
     *     * user - user name
     *     * pass - user password
    'dblib:host=host;dbname=db;charset=UTF8', $user, $pwd
     * @return resource Database connection handle
     */
    public static function sql_connect( $sql_details ) {
        try {
            $db = @new PDO(
                "mysql:host={$sql_details['host']};dbname={$sql_details['db']};charset=UTF8",
                $sql_details['user'],
                $sql_details['pass'],
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                )
            );
        } catch ( PDOException $e ) {
            self::fatal(
                "An error occurred while connecting to the database. " .
                "The error reported by the server was: " . $e->getMessage()
            );
        }

        return $db;
    }

    /**
     * Execute an SQL query on the database
     *
     * @param  resource $db  Database handler
     * @param  array    $bindings Array of PDO binding values from bind() to be
     *   used for safely escaping strings. Note that this can be given as the
     *   SQL query string if no bindings are required.
     * @param  string   $sql SQL query to execute.
     * @return array         Result from the query (all rows)
     */

    public static function sql_exec( $db, $bindings, $sql = null ) {
        // Argument shifting
        if ( $sql === null ) {
            $sql = $bindings;
        }

        $stmt = $db->prepare( $sql );

        // Bind parameters
        if ( is_array( $bindings ) ) {
            for ( $i = 0, $ien = count( $bindings ); $i < $ien; $i++ ) {
                $binding = $bindings[$i];
                $stmt->bindValue( $binding['key'], $binding['val'], $binding['type'] );
            }
        }

        // Execute
        try {
            $stmt->execute();
        } catch ( PDOException $e ) {
            directlog( "SSP:: function sql_exec :: An SQL error:" );
            directlog( $sql );
            self::fatal( "SSP:: function sql_exec :: An SQL error occurred: " . $e->getMessage() );
        }

        // Return all
        return $stmt->fetchAll( PDO::FETCH_BOTH );
        // return $stmt->fetchAll( PDO::FETCH_ASSOC );
    }
    public static function sql_exec_assoc( $db, $bindings, $sql = null ) {
        // Argument shifting
        if ( $sql === null ) {
            $sql = $bindings;
        }

        $stmt = $db->prepare( $sql );
        //echo $sql;

        // Bind parameters
        if ( is_array( $bindings ) ) {
            for ( $i = 0, $ien = count( $bindings ); $i < $ien; $i++ ) {
                $binding = $bindings[$i];
                $stmt->bindValue( $binding['key'], $binding['val'], $binding['type'] );
            }
        }

        // Execute
        try {
            $stmt->execute();
        } catch ( PDOException $e ) {
            self::fatal( "SSP:: function sql_exec_assoc :: An SQL error occurred: " . $e->getMessage() );
        }

        // Return all
        // return $stmt->fetchAll( PDO::FETCH_BOTH );
        return $stmt->fetchAll( PDO::FETCH_ASSOC );
    }

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Internal methods
     */

    /**
     * Throw a fatal error.
     *
     * This writes out an error message in a JSON string which DataTables will
     * see and show to the user in the browser.
     *
     * @param  string $msg Message to send to the client
     */
    public static function fatal( $msg ) {
        echo json_encode( array(
            "error" => $msg,
        ) );
        exit( 0 );
    }

    /**
     * Создает массив  [key, val,type] key= :binding_#
     * Create a PDO binding key which can be used for escaping variables safely
     * when executing a query with sql_exec()
     *
     * @param  array &$a    Array of bindings
     * @param  *      $val  Value to bind
     * @param  int    $type PDO field type
     * @return string       Bound key to be used in the SQL where this parameter
     *   would be used.
     */
    public static function bind( &$a, $val, $type ) {
        $key = ':binding_' . count( $a );

        $a[] = array(
            'key'  => $key,
            'val'  => $val,
            'type' => $type,
        );
        return $key;
    }

    /**
     * переделывает ассоциативный массив в нумерованный вида 0=>a[0]["проперти"]
     * Pull a particular property from each assoc. array in a numeric array,
     * returning and array of the property values from each item.
     *
     *  @param  array  $a    Array to get data from
     *  @param  string $prop Property to read
     *  @return array        Array of property values
     */
    public static function pluck( $a, $prop ) {
        $out = array();
        for ( $i = 0, $len = count( $a ); $i < $len; $i++ ) {
            $out[] = $a[$i][$prop];
        }
        return $out;
    }

    /**
     * Return a string from an array or a string
     *  разматывает массисв строку вида a[0] AND a[1] AND a[2]
     * @param  array|string $a Array to join
     * @param  string $join Glue for the concatenation
     * @return string Joined string
     */
    public static function flatten( $a, $join = ' AND ' ) {
        if ( !$a ) {
            return '';
        } elseif ( $a && is_array( $a ) ) {
            return implode( $join, $a );
        }
        return $a;
    }

    /**
     * Simplesonic - Denis Nossevitch 2017-03-01
     * Perform SQL queries needed for a server-side processing request involving
     * a very large table. Utilizes the helper functions of this class similar
     * to simple(). Demands primary key, whether or not it is requested as a
     * column. This allows for values with large offsets (when viewing the
     * rows 10,000 - 11,000 for example) to load just as quickly as initial rows.
     * Returns JSON encodable response to an SSP request.
     *
     *  @param  array   $request        Data sent to server by DataTables
     *  @param  array   $sql_details    SQL connection details - see sql_connect()
     *  @param  string  $table          SQL table to query
     *  @param  string  $primary        Key Primary key of the table
     *  @param  array   $columns        Column information array
     *  @return array                   Server-side processing response array
     */
    // public function simplesonic() {
    public function simplesonic( $request, $conn, $table, $primaryKey, $columns ) {

        $request     = $this->params['request'];
        $sql_details = $this->params['sql_details'];
        $table       = $this->params['table'];
        $primaryKey  = $this->params['primaryKey'];
        $columns     = $this->params['columns'];
        $selectWhere = $this->params['selectWhere'];

        $bindings = array();
        $db       = self::sql_connect( $sql_details );

        // Build the SQL query string from the request
        $limit = $this->limit( $request, $columns );
        $order = $this->order( $request, $columns );
        $where = $this->filter( $request, $columns, $bindings, $selectWhere );

        // Determine primary key, requested as column or not
        $cols = $columns;
        foreach ( $cols as &$c ) {
            if ( $c['db'] == $primaryKey ) {
                $c['db']      = 't.' . $c['db'];
                $is_requested = TRUE;
                $pk           = "";
            }
        }
        if ( !isset( $is_requested ) ) {
            $pk = $primaryKey;
        }

        // Main query to actually get the data
        $sql_query = "SELECT $pk " . implode( ", ", self::pluck( $cols, 'db' ) ) . "
            FROM (
                SELECT $primaryKey
                FROM $table
                $where
                $order
                $limit
            ) q
            JOIN $table t
            ON t.$primaryKey = q.$primaryKey";

        $data = self::sql_exec( $db, $bindings, $sql_query );

        // Data set length after filtering
        $resFilterLength = self::sql_exec( $db, $bindings,
            "SELECT COUNT(*) FROM " . $table . " " . $where
        );
        $recordsFiltered = $resFilterLength[0][0];

        // Total data set length
        $length_query = "SELECT COUNT(`{$primaryKey}`) FROM " . $table;
        $length_query .= ( $selectWhere !== FALSE ) ? " WHERE " . $selectWhere : "";

        $resTotalLength = self::sql_exec( $db, $bindings, $length_query );
        $recordsTotal   = $resTotalLength[0][0];

        /*
         * Output
         */
        return array(
            "sql_query"       => $sql_query,
            // "draw"            => intval( $request['draw'] ),
             "draw"            => isset( $request['draw'] ) ? intval( $request['draw'] ) : 0,
            "recordsTotal"    => intval( $recordsTotal ),
            "recordsFiltered" => intval( $recordsFiltered ),
            "data"            => self::data_output( $columns, $data ),
        );
    }

    /**
     *  Modified the ssp class to use join and group by. The queries themselves seem to run fine.
     * But the pagination attributes 'recordTotal' and 'recordsFiltered' are now messed up.
     *  @param  array $request Data sent to server by DataTables
     *  @param  array|PDO $conn PDO connection resource or connection parameters array
     *  @param  string $table SQL table to query
     *  @param  string $primaryKey Primary key of the table
     *  @param  array $columns Column information array
     *  @param  string $whereResult WHERE condition to apply to the result set
     *  @param  string $whereAll WHERE condition to apply to all queries
     *  @return array          Server-side processing response array
     **/
    public static function complex_grouping( $request, $conn, $table, $primaryKey, $columns, $whereResult = null, $whereAll = null, $whereCondition, $joinCondition = null, $selectQuery = null ) {
        //echo $whereCondition;
        $bindings         = array();
        $db               = self::db( $conn );
        $localWhereResult = array();
        $localWhereAll    = array();
        $whereAllSql      = '';
        // Build the SQL query string from the request
        $limit       = self::limit( $request, $columns );
        $order       = self::order( $request, $columns );
        $where       = self::filter( $request, $columns, $bindings );
        $whereResult = self::_flatten( $whereResult );
        $whereAll    = self::_flatten( $whereAll );
        if ( $whereResult ) {
            $where = $where ?
            $where . ' AND ' . $whereResult :
            'WHERE ' . $whereResult;
        }
        if ( $whereAll ) {
            $where = $where ?
            $where . ' AND ' . $whereAll :
            'WHERE ' . $whereAll;
            $whereAllSql = 'WHERE ' . $whereAll;
        }
        $whereCondition = preg_replace( '/(\v|\s)+/', ' ', $whereCondition );
        //echo $whereCondition . "<br/><br/>";
        // Main query to actually get the data
        //echo $where;
        //$query = "SELECT ".implode(", ", self::pluck($columns, 'db'))." FROM `$table` $joinCondition $whereCondition $order $limit";
        if ( isset( $selectQuery ) ) {
            $query = "$selectQuery FROM `$table` $joinCondition $whereCondition $order $limit";
        } else {
            $query = "SELECT " . implode( ", ", self::pluck( $columns, 'db' ) ) . " FROM `$table` $joinCondition $whereCondition $order $limit";
        }
        //echo $query;
        //echo $whereCondition;
        //echo $query;
        $data = self::sql_exec( $db, $bindings, $query );
        // Data set length after filtering
        //print_r($data);
        $query2 = "SELECT COUNT({$primaryKey}) FROM $table $joinCondition $whereCondition";
        //echo $query2;
        $resFilterLength = self::sql_exec( $db, $bindings, $query2 );
        $recordsFiltered = $resFilterLength[0][0];
        // Total data set length
        $query3 = "SELECT COUNT({$primaryKey}) FROM `$table` $joinCondition" . $whereAllSql;
        //echo $query3;
        $resTotalLength = self::sql_exec( $db, $bindings, $query3 );
        $recordsTotal   = $resTotalLength[0][0];
        /*
         * Output
         */
        return array(
            "draw"            => isset( $request['draw'] ) ?
            intval( $request['draw'] ) :
            0,
            "recordsTotal"    => intval( $recordsTotal ),
            "recordsFiltered" => intval( $recordsFiltered ),
            "data"            => self::data_output( $columns, $data ),
        );
    }

    /**
     * The difference between this method and the `simple` one, is that you can
     * apply additional `where` conditions to the SQL queries. These can be in
     * one of two forms:
     *
     * * 'Result condition' - This is applied to the result set, but not the
     *   overall paging information query - i.e. it will not effect the number
     *   of records that a user sees they can have access to. This should be
     *   used when you want apply a filtering condition that the user has sent.
     * * 'All condition' - This is applied to all queries that are made and
     *   reduces the number of records that the user can access. This should be
     *   used in conditions where you don't want the user to ever have access to
     *   particular records (for example, restricting by a login id).
     *
     *  @param  array $request Data sent to server by DataTables
     *  @param  array|PDO $conn PDO connection resource or connection parameters array
     *  @param  string $table SQL table to query
     *  @param  string $primaryKey Primary key of the table
     *  @param  array $columns Column information array
     *  @param  string $whereResult WHERE condition to apply to the result set
     *  @param  string $whereAll WHERE condition to apply to all queries
     *  @return array          Server-side processing response array
     */
    public static function complex( $request, $conn, $table, $primaryKey, $columns, $whereResult = null, $whereAll = null ) {
        $bindings         = array();
        $db               = self::db( $conn );
        $localWhereResult = array();
        $localWhereAll    = array();
        $whereAllSql      = '';

        // Build the SQL query string from the request
        $limit = self::limit( $request, $columns );
        $order = self::order( $request, $columns );
        $where = self::filter( $request, $columns, $bindings );

        $whereResult = self::flatten( $whereResult );
        $whereAll    = self::flatten( $whereAll );

        if ( $whereResult ) {
            $where = $where ?
            $where . ' AND ' . $whereResult :
            'WHERE ' . $whereResult;
        }

        if ( $whereAll ) {
            $where = $where ?
            $where . ' AND ' . $whereAll :
            'WHERE ' . $whereAll;

            $whereAllSql = 'WHERE ' . $whereAll;
        }

        // Main query to actually get the data
        $sql_query = "SELECT `" . implode( "`, `", self::pluck( $columns, 'db' ) ) . "`
             FROM `$table`
             $where
             $order
             $limit";
        $sql_query = str_replace( "\r\n", " ", $sql_query );

        $data = self::sql_exec( $db, $bindings, $sql_query );

        // Data set length after filtering
        $resFilterLength = self::sql_exec( $db, $bindings,
            "SELECT COUNT(`{$primaryKey}`)
             FROM   `$table`
             $where"
        );
        $recordsFiltered = $resFilterLength[0][0];

        // Total data set length
        $resTotalLength = self::sql_exec( $db, $bindings,
            "SELECT COUNT(`{$primaryKey}`)
             FROM   `$table` " .
            $whereAllSql
        );
        $recordsTotal = $resTotalLength[0][0];

        /*
         * Output
         */

        return array(
            "sql_query"       => $sql_query,
            "whereResult"     => $whereResult,
            "whereAll"        => $whereAll,
            "draw"            => isset( $request['draw'] ) ? intval( $request['draw'] ) : 0,
            "recordsTotal"    => intval( $recordsTotal ),
            "recordsFiltered" => intval( $recordsFiltered ),
            "data"            => self::data_output( $columns, $data ),

        );
    }
}
