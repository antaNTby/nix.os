<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-08-23       #
#          http://nixby.pro              #
##########################################

################################################################################################################################
################################################################################################################################
#########################################                 antaNT64 functions                      ##############################
################################################################################################################################
################################################################################################################################

/**
 * Pull a particular property from each assoc. array in a numeric array,
 * returning and array of the property values from each item.
 *
 * возвращает массив размерностью с размер массива $a, где значения равны $prop или null
 *  @param  array  $a    Array to get data from
 *  @param  string $prop Property to read
 *  @return array        Array of property values
 */
####
function pluck( $a, $prop ) {
    $out = array();
    for ( $i = 0, $len = count( $a ); $i < $len; $i++ ) {
        $out[] = $a[$i][$prop];
    }
    return $out;
}

/**
 * Return a string from an array or a string
 *
 * @param  array|string $a Array to join
 * @param  string $join Glue for the concatenation
 * @return string Joined string
 */
###
function flatten( $a, $join = ' AND ' ) {
    if ( !$a ) {
        return '';
    } elseif ( $a && is_array( $a ) ) {
        return implode( $join, $a );
    }
    return $a;
}

/**
 * Возвращает номер столбца из массисва $columns с именем $name
 *  @param  array $columns Column information array
 *  @param  string $name -имя столбца, индекс которого нужно найти
 *  @param  string $dtt поле в $columns, которое выщипывается из массива

 *  @return string SQL order by clause
 *
 */

function antGetProductCodeFromComplexName( $complexName ): string{

    #   \[([^\[\]]+)\]
    #  https://toster.ru/q/279675
    //$complexname=" [195259] Samsung < EF-BT310BWEGRU > Чехол-книжка для Galaxy Tab 3 8.0 (вариант,НИКС Москва)" ;

    $my_pattern = "/\[([^\[\]]+)\]/"; // код товара в квадратных скобках
    $matches    = array();
    $string     = trim( $complexName );

    $restored_string = "";

    if ( preg_match( $my_pattern, $string ) ) {
        preg_match( $my_pattern, $string, $matches );
        $restored_string = $matches[1];
    } else {
        $restored_string = "UNKNOWN_CODE";
    }

    return $restored_string;
}

function antGetMODELFromComplexName( $complexName ) {

    #   \[([^\[\]]+)\]
    #  https://toster.ru/q/279675
    //$complexname=" [195259] Samsung < EF-BT310BWEGRU > Чехол-книжка для Galaxy Tab 3 8.0 (вариант,НИКС Москва)" ;

    $my_pattern = "/<([^<>]+)>/"; // код товара в квадратных скобках
    $matches    = array();

// TRAIN_GO_MARK_DB
    $complexName = str_replace( TRAIN_GO_MARK_DB, "", $complexName );
    $string      = html_entity_decode( $complexName );

    $restored_string = "";

    if ( preg_match( $my_pattern, $string ) ) {
        preg_match( $my_pattern, $string, $matches );
        $restored_string = $matches[1];
    } else {
        $restored_string = "ANY_MODEL";
    }

    // debugfile($matches,"{$string}==={$complexName}");
    return $restored_string;
}

if ( !function_exists( 'json_last_error_msg' ) ) {
    function json_last_error_msg() {
        static $ERRORS = array(
            JSON_ERROR_NONE           => 'No error',
            JSON_ERROR_DEPTH          => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'State mismatch (invalid or malformed JSON)',
            JSON_ERROR_CTRL_CHAR      => 'Control character error, possibly incorrectly encoded',
            JSON_ERROR_SYNTAX         => 'Syntax error',
            JSON_ERROR_UTF8           => 'Malformed UTF-8 characters, possibly incorrectly encoded',
        );

        $error = json_last_error();
        // consolelog($error);
        return isset( $ERRORS[$error] ) ? $ERRORS[$error] : 'Unknown error';
    }
}

function from_unicode( $text ) {
    return str_ireplace( array( "#U0430", "#U0431", "#U0432",
        "#U0433", "#U0434", "#U0435", "#U0451", "#U0436", "#U0437", "#U0438",
        "#U0439", "#U043A", "#U043B", "#U043C", "#U043D", "#U043E", "#U043F",
        "#U0440", "#U0441", "#U0442", "#U0443", "#U0444", "#U0445", "#U0446",
        "#U0447", "#U0448", "#U0449", "#U044A", "#U044B", "#U044C", "#U044D",
        "#U044E", "#U044F", "#U0410", "#U0411", "#U0412", "#U0413", "#U0414",
        "#U0415", "#U0401", "#U0416", "#U0417", "#U0418", "#U0419", "#U041A",
        "#U041B", "#U041C", "#U041D", "#U041E", "#U041F", "#U0420", "#U0421",
        "#U0422", "#U0423", "#U0424", "#U0425", "#U0426", "#U0427", "#U0428",
        "#U0429", "#U042A", "#U042B", "#U042C", "#U042D", "#U042E", "#U042F",
        "#U0021", "#U0027", "#U0028", "#U0029", "#U002B", "#U002C", "#U003D",
        "#U0040", "#U0060", "#U005F", "#U002D", "#U002E", "#U00A7", "#U00AB",
        "#U00BB", "#U00B0", "#U0031", "#U0032", "#U0033", "#U0034", "#U0035",
        "#U0036", "#U0037", "#U0038", "#U0039", "#U0030", "#U2116", "#U0020" ),
        array( "а", "б", "в",
            "г", "д", "е", "ё", "ж", "з", "и",
            "й", "к", "л", "м", "н", "о", "п",
            "р", "с", "т", "у", "ф", "х", "ц",
            "ч", "ш", "щ", "ъ", "ы", "ь", "э",
            "ю", "я", "А", "Б", "В", "Г", "Д",
            "Е", "Ё", "Ж", "З", "И", "Й", "К",
            "Л", "М", "Н", "О", "П", "Р", "С",
            "Т", "У", "Ф", "Х", "Ц", "Ч", "Ш",
            "Щ", "Ъ", "Ы", "Ь", "Э", "Ю", "Я",
            "!", "\'", "(", ")", "+", ",", "=",
            "@", "`", "_", "-", ".", "§", "«",
            "»", "°", "1", "2", "3", "4", "5",
            "6", "7", "8", "9", "0", "№", " " ),
        $text );
}

function adminGetErrors() {
    # code...
    $res        = array();
    $errorlines = array();
    $result     = db_query( "SELECT errors from " . ERROR_LOG_TABLE . " LIMIT 0,50" );
    while ( $errors = db_fetch_row( $result ) ) {
        $errorlines[] = $errors;
    }

    $errorlinesql = array();
    $result       = db_query( "SELECT errors from " . MYSQL_ERROR_LOG_TABLE . " LIMIT 0,50" );
    while ( $errors = db_fetch_row( $result ) ) {
        $errorlinesql[] = $errors;
    }

    $html = '<ul class="nav navbar-nav btn-group">';
    if ( $errorlines ) {
        $html .= "<a id='li_error_php' target = '_blank' href='" . ADMIN_FILE . "?dpt=reports&sub=security&show=error_php' class='btn navbar-btn btn-danger btn-xs'>";
        $html .= "php:" . count( $errorlines ) . "";
        $html .= "</a>";
    }

    if ( $errorlinesql ) {
        $html .= "<a id='li_error_sql' target = '_blank' href='" . ADMIN_FILE . "?dpt=reports&sub=security&show=error_sql' class='btn navbar-btn btn-danger btn-xs'>";
        $html .= "sql:" . count( $errorlinesql ) . "";
        $html .= "</a>";
    }
    $html .= "</ul>";
    $res = $html;
    return $res;

}

function say_to_russian( $num = 0, $name1 = 'штука', $name2 = 'штуки', $name3 = 'штук' ) {
    if ( in_array( $num % 100, array( 11, 12, 13, 14 ) ) ) {
        return $name3;
    }

    $suffix = $num % 10;
    if ( in_array( $suffix, array( 1 ) ) ) {
        return $name1;
    }

    if ( in_array( $suffix, array( 2, 3, 4 ) ) ) {
        return $name2;
    }

    return $name3;
}

# перевести число $n в строку. Число обязательно должно быть 0 < $n < 1000. $rod указывает на род суффикса (0 - женский, 1 - мужской; например, "рубль" - 1, "тысяча" - 0).
function num_to_russian_nominative( $n, $rod ) {
    $a = floor( $n / 100 );
    $b = floor(  ( $n - $a * 100 ) / 10 );
    $c = $n % 10;

    $s = "";
    switch ( $a ) {
        case 1:
            $s = "сто";
            break;
        case 2:
            $s = "двести";
            break;
        case 3:
            $s = "триста";
            break;
        case 4:
            $s = "четыреста";
            break;
        case 5:
            $s = "пятьсот";
            break;
        case 6:
            $s = "шестьсот";
            break;
        case 7:
            $s = "семьсот";
            break;
        case 8:
            $s = "восемьсот";
            break;
        case 9:
            $s = "девятьсот";
            break;
    }

    $s .= " ";
    if ( $b != 1 ) {
        switch ( $b ) {
            case 1:
                $s .= "десять";
                break;
            case 2:
                $s .= "двадцать";
                break;
            case 3:
                $s .= "тридцать";
                break;
            case 4:
                $s .= "сорок";
                break;
            case 5:
                $s .= "пятьдесят";
                break;
            case 6:
                $s .= "шестьдесят";
                break;
            case 7:
                $s .= "семьдесят";
                break;
            case 8:
                $s .= "восемьдесят";
                break;
            case 9:
                $s .= "девяносто";
                break;
        }

        $s .= " ";
        switch ( $c ) {
            case 1:
                $s .= $rod ? "один" : "одна";
                break;
            case 2:
                $s .= $rod ? "два" : "две";
                break;
            case 3:
                $s .= "три";
                break;
            case 4:
                $s .= "четыре";
                break;
            case 5:
                $s .= "пять";
                break;
            case 6:
                $s .= "шесть";
                break;
            case 7:
                $s .= "семь";
                break;
            case 8:
                $s .= "восемь";
                break;
            case 9:
                $s .= "девять";
                break;
        }
    } else {
        //...дцать
        switch ( $c ) {
            case 0:
                $s .= "десять";
                break;
            case 1:
                $s .= "одиннадцать";
                break;
            case 2:
                $s .= "двенадцать";
                break;
            case 3:
                $s .= "тринадцать";
                break;
            case 4:
                $s .= "четырнадцать";
                break;
            case 5:
                $s .= "пятнадцать";
                break;
            case 6:
                $s .= "шестнадцать";
                break;
            case 7:
                $s .= "семнадцать";
                break;
            case 8:
                $s .= "восемнадцать";
                break;
            case 9:
                $s .= "девятнадцать";
                break;
        }
    }
    return trim($s);
}

function formatsize( $arg ) {
    if ( $arg > 0 ) {
        $j   = 0;
        $ext = array( " bytes", " Kb", " Mb", " Gb", " Tb" );
        while ( $arg >= pow( 1024, $j ) ) {
            ++$j;
        }
        {
            $arg = ( round( $arg / pow( 1024, $j - 1 ) * 100 ) / 100 ) . ( $ext[$j - 1] );
        }
        return $arg;
    } else {
        return "0 Kb";
    }

}

# $table - імя табліцы
# $index_fieldname - нвзваніе унікального поля с автоінкрементом
# $time_fieldname - поле вставляемое прі созданіі запісі
# $arrNewValues - массів новых значеній
# $ignore_list - строка в которой перечіслены поля которые не нужно обновлять через запятюю
# $boolean_fields - строка в которой перечіслены поля прівязанные к чекбоксам через запятюю
# $update_id - індекс запісі которую нужно обновіть ілі создать($id=0)
function sqlInsertValuesFromArrayToDbTable( $table, $index_fieldname, $time_fieldname, $arrNewValues, $ignore_list, $boolean_fields, $update_id = 0 ) {
// debug($arrNewValues);
    if ( $update_id == 0 ) {
        db_query( "INSERT INTO `$table` (`$time_fieldname`) values (SYSDATE())" );
        $DBid = mysql_insert_id();
        $id   = (int)$DBid;
    } else {
        $id = (int)$update_id;
    }

    if ( $id >= 1 ) {
        // make checkboxses array
        $boolean_fields       = str_replace( " ", "", $boolean_fields );
        $boolean_fields_array = explode( ',', $boolean_fields );
        foreach ( $boolean_fields_array as $key => $val ) {
            $arrNewValues[$val] = ( !empty( $arrNewValues[$val] ) ) ? 1 : 0;
        }

        $db_fields_array = array();
        $db_fields_array = sqlGetFieldsNamesOfTable( $table, 0, $ignore_list );

        //update each field in DB to $_POST values
        foreach ( $arrNewValues as $field => $val ) {
            $ff = in_array( $field, $db_fields_array, true );
            if ( $ff ) {
                $value = xToText( $val );
                // $sql="UPDATE `$table` SET `$field` = '".xToText($value)."' WHERE `$index_fieldname`='$id';";
                // $sql="UPDATE `$table` SET `$field` = '".xEscSQL($value)."' WHERE `$index_fieldname`='$id';";
                $sql = "UPDATE `$table` SET `$field` = '" . ( $value ) . "' WHERE `$index_fieldname`='$id';";
                $q   = db_query( $sql );
            }
        }
        return $id;
    } else {
        return 0;
    }
}

#     $out_type=0 -Array,
#     $out_type=10 -String by ","      $out_type=11 -String by " " ,       $out_type=12 -String for Sql $out_type=13 -String_col for Sql
#     $out_type=20 - JSON by Array
function sqlGetFieldsNamesOfTable( $table, $out_type = 0, $ignore_list = "", $prefix = "" ) {
    $db_fields_array   = array();
    $ignore_list_array = array();

    $sql = "SHOW COLUMNS FROM `$table`";
    $q   = db_query( $sql );

    //get array of fieldkeys from DB table
    while ( $row = db_fetch_row( $q ) ) {
        $db_fields_array[] = $row["Field"];
        //debug($row);
    }

    // make exluded fields array
    $string_ignore_list = str_replace( " ", "", $ignore_list );
    $ignore_list_array  = explode( ',', $string_ignore_list );
    foreach ( $ignore_list_array as $key => $val ) {
        $keyToKill = array_search( $val, $db_fields_array );
        if ( $keyToKill !== false ) {
            unset( $db_fields_array[$keyToKill] );
        }
    }

    // make final fields array
    $db_fields_array = array_values( $db_fields_array );

    $result = array();
    switch ( $out_type ) {
        case 0:
            //массив полей
            $result = $db_fields_array;
            break;
        case 10:
            $result = implode( ',', $db_fields_array );
            break;
        case 11:
            $result = implode( ' ', $db_fields_array );
            break;
        case 12:
            // список названий полей `field1`,`field2`
            $result = implode( '`, `', $db_fields_array );
            $result = "`" . $result . "`";
            break;
        case 13:
            $result = implode( ", $" . $prefix, $db_fields_array );

            break;

        case 23:
            $result = implode( "', '$" . $prefix, $db_fields_array );
            $result = "VALUES ('$" . $prefix . $result . "');";
            break;

        case 14:
            $result = implode( '<br>', $db_fields_array );
        //$result
        case 15:
            $result = implode( ',<br>', $db_fields_array );
            //$result = "VALUES ($".$result.");";
            break;
        case 20:
            //JSON полей
            $result = json_encode( $db_fields_array );
            break;
        default:
            $result = $db_fields_array;
            break;
    }

    return $result;
}

// *****************************************************************************
// Purpose        Split  ut8-string to TEXT and REMark, from "#" or "//" to end as a rem
//                        function
//                        function stringSplitToTextAndRemark($str)
// Inputs           $str
// Remarks
// Returns        array("text","rem")
function stringSplitToTextAndRemark( $str ) {
    $res       = array();
    $str       = htmlspecialchars_decode( $str );
    $title_len = mb_strlen( $str );
    if ( $title_len >= 0 ) {
        $rem_start0 = ( mb_strpos( $str, "#" ) === false ) ? $title_len : mb_strpos( $str, "#" );
        $rem_start1 = ( mb_strpos( $str, "//" ) === false ) ? $title_len : mb_strpos( $str, "//" );
        $rem_start  = (  ( $rem_start0 >= $rem_start1 ) ) ? $rem_start1 : $rem_start0;
        $text_text  = mb_substr( $str, 0, $rem_start );
        $rem_text   = mb_substr( $str, $rem_start );
    }
    $res["str"]  = $str;
    $res["text"] = $text_text;
    $res["rem"]  = mb_ereg_replace( "//|#", "", $rem_text );
    return $res;
}

// *****************************************************************************
// Делает первую букву в слове Прописной
if ( !function_exists( 'mb_ucfirst' ) && extension_loaded( 'mbstring' ) ) {
    /**
     * mb_ucfirst - преобразует первый символ в верхний регистр
     * @param string $str - строка
     * @param string $encoding - кодировка, по-умолчанию UTF-8
     * @return string
     */
    function mb_ucfirst( string $str, string $encoding = 'UTF-8' ): string{
        $str = mb_ereg_replace( '^[\ ]+', '', $str );
        $str = mb_strtoupper( mb_substr( $str, 0, 1, $encoding ), $encoding ) .
        mb_substr( $str, 1, mb_strlen( $str ), $encoding );
        return $str;
    }
}

function my_mb_ucfirst( $str ) {
    $fc = mb_strtoupper( mb_substr( $str, 0, 1 ) );
    return $fc . mb_substr( $str, 1 );
}

/**
 * ucfirst UTF-8 aware function
 *
 * Делает первую букву в слове Прописной
 *
 * @param string $string
 * @return string
 * @see http://ca.php.net/ucfirst
 */
function my_ucfirst( string $string, $e = 'utf-8' ): string {
    if ( function_exists( 'mb_strtoupper' ) && function_exists( 'mb_substr' ) && !empty( $string ) ) {
        $string = mb_strtolower( $string, $e );
        $upper  = mb_strtoupper( $string, $e );
        preg_match( '#(.)#us', $upper, $matches );
        $string = $matches[1] . mb_substr( $string, 1, mb_strlen( $string, $e ), $e );
    } else {
        $string = ucfirst( $string );
    }
    return $string;
}

// function mb_myfgetcsv( $fname, $del ) {
//     $f           = fopen( $fname, "r" );
//     $res         = array();
//     $firstFlag   = true;
//     $columnCount = 0;

//     while ( $row = mb_fgetcsvs( $f, $del ) ) {
//         if ( $firstFlag ) {
//             $columnCount = count( $row );
//         }

//         $firstFlag = false;
//         while ( count( $row ) < $columnCount ) {
//             $row[] = "";
//         }

//         $res[] = $row;
//     }
//     fclose( $f );
//     return $res;
// }

// function mb_fgetcsvs( $f, $d, $q = '"' ) {
//     $list = array();
//     $st   = fgets( $f );
//     if ( $st === false || $st === null ) {
//         return $st;
//     }

//     while ( $st !== "" && $st !== false ) {
//         if ( $st[0] !== $q ) {
//             # Non-quoted.
//             list( $field ) = explode( $d, $st, 2 );
//             $st          = mb_substr( $st, mb_strlen( $field ) + mb_strlen( $d ) );
//         } else {
//             # Quoted field.
//             $st    = mb_substr( $st, 1 );
//             $field = "";
//             while ( 1 ) {
//                 # Find until finishing quote (EXCLUDING) or eol (including)
//                 preg_match( "/^((?:[^$q]+|$q$q)*)/sx", $st, $p );
//                 $part    = $p[1];
//                 $partlen = mb_strlen( $part );
//                 $st      = mb_substr( $st, mb_strlen( $p[0] ) );
//                 $field .= str_replace( $q . $q, $q, $part );
//                 if ( mb_strlen( $st ) && $st[0] === $q ) {
//                     # Found finishing quote.
//                     list( $dummy ) = explode( $d, $st, 2 );
//                     $st          = mb_substr( $st, mb_strlen( $dummy ) + mb_strlen( $d ) );
//                     break;
//                 } else {
//                     # No finishing quote - newline.
//                     $st = fgets( $f );
//                 }
//             }

//         }
//         $list[] = $field;
//     }
//     return $list;
// }

// *****************************************************************************
// Purpose        convert windows1251 to ut8
//                        function
//                        function arr_win1251_to_utf8($text)
// Inputs           $text
// Remarks
// Returns        utf_text
function iconv_all( $text ) {
    if ( is_array( $text ) ) {
        array_walk_recursive( $text, function ( &$item ) {
            $item = iconv( "WINDOWS-1251", "UTF-8", $item );
        } );
        return $text;
    }
    return iconv( "WINDOWS-1251", "UTF-8", $item );
}

// *****************************************************************************
// Purpose        convert  ut8 to windows1251
//                        function
//                        function arr_win1251_to_utf8($text)
// Inputs           $text
// Remarks
// Returns        WINDOWS-1251_text
function iconv_all_1251( $text ) {
    if ( is_array( $text ) ) {
        array_walk_recursive( $text, function ( &$item ) {
            $item = iconv( "UTF-8", "WINDOWS-1251", $item );
        } );
        return $text;
    }
    return iconv( "UTF-8", "WINDOWS-1251", $item );
}

function GetFileSize( $fn ) {
    return false;
}

function translitText( $str ) {
    $tr = array(
        "А" => "A", "Б"  => "B", "В"   => "V", "Г"  => "G",
        "Д" => "D", "Е"  => "E", "Ё"   => "E", "Ж"  => "J", "З"   => "Z", "И" => "I",
        "Й" => "Y", "К"  => "K", "Л"   => "L", "М"  => "M", "Н"   => "N",
        "О" => "O", "П"  => "P", "Р"   => "R", "С"  => "S", "Т"   => "T",
        "У" => "U", "Ф"  => "F", "Х"   => "H", "Ц"  => "TS", "Ч"  => "CH",
        "Ш" => "SH", "Щ" => "SCH", "Ъ" => "", "Ы"   => "YI", "Ь"  => "",
        "Э" => "E", "Ю"  => "YU", "Я"  => "YA", "а" => "a", "б"   => "b",
        "в" => "v", "г"  => "g", "д"   => "d", "е"  => "e", "ё"   => "e", "ж" => "j",
        "з" => "z", "и"  => "i", "й"   => "y", "к"  => "k", "л"   => "l",
        "м" => "m", "н"  => "n", "о"   => "o", "п"  => "p", "р"   => "r",
        "с" => "s", "т"  => "t", "у"   => "u", "ф"  => "f", "х"   => "h",
        "ц" => "ts", "ч" => "ch", "ш"  => "sh", "щ" => "sch", "ъ" => "y",
        "ы" => "yi", "ь" => "", "э"    => "e", "ю"  => "yu", "я"  => "ya",
    );
    return strtr( $str, $tr );
}
function translitErrorText( $str ) {
    $tr = array(
        "А" => "(A)", "Б"  => "(B)", "В"   => "(V)", "Г"  => "(G)",
        "Д" => "(D)", "Е"  => "(E)", "Ё"   => "(E)", "Ж"  => "(J)", "З"   => "(Z)", "И" => "(I)",
        "Й" => "(Y)", "К"  => "(K)", "Л"   => "(L)", "М"  => "(M)", "Н"   => "(N)",
        "О" => "(O)", "П"  => "(P)", "Р"   => "(R)", "С"  => "(S)", "Т"   => "(T)",
        "У" => "(U)", "Ф"  => "(F)", "Х"   => "(H)", "Ц"  => "(TS)", "Ч"  => "(CH)",
        "Ш" => "(SH)", "Щ" => "(SCH)", "Ъ" => "(#)", "Ы"  => "(YI)", "Ь"  => "(`)",
        "Э" => "(E)", "Ю"  => "(YU)", "Я"  => "(YA)", "а" => "(a)", "б"   => "(b)",
        "в" => "(v)", "г"  => "(g)", "д"   => "(d)", "е"  => "(e)", "ё"   => "(e)", "ж" => "(j)",
        "з" => "(z)", "и"  => "(i)", "й"   => "(y)", "к"  => "(k)", "л"   => "(l)",
        "м" => "(m)", "н"  => "(n)", "о"   => "(o)", "п"  => "(p)", "р"   => "(r)",
        "с" => "(s)", "т"  => "(t)", "у"   => "(u)", "ф"  => "(f)", "х"   => "(h)",
        "ц" => "(ts)", "ч" => "(ch)", "ш"  => "(sh)", "щ" => "(sch)", "ъ" => "(y)",
        "ы" => "(yi)", "ь" => "()", "э"    => "(e)", "ю"  => "(yu)", "я"  => "(ya)",
    );
    return strtr( $str, $tr );
}

################################################################################################################################
################################################################################################################################
#########################################                 antaNT64 functions                      ##############################
################################################################################################################################
################################################################################################################################


function myfile_get_contents( $fileName ) {
    return implode( "", file( $fileName ) );
}

function correct_URL( $url, $mode = "http" ) //converts

{
    $URLprefix = trim( $url );
    $URLprefix = str_replace( "http://", "", $URLprefix );
    $URLprefix = str_replace( "https://", "", $URLprefix );
    $URLprefix = str_replace( "index.php", "", $URLprefix );
    if ( $URLprefix[strlen( $URLprefix ) - 1] == '/' ) {
        $URLprefix = substr( $URLprefix, 0, strlen( $URLprefix ) - 1 );
    }
    return ( $mode . "://" . $URLprefix . "/" );
}

// *****************************************************************************
// Purpose        sets access rights to files which uploaded with help move_uploaded_file
//                        function
// Inputs           $file_name - file name
// Remarks
// Returns        nothing
function SetRightsToUploadedFile( $file_name ) {
    @chmod( $file_name, 0666 );
}

function getmicrotime() {
    list( $usec, $sec ) = explode( " ", microtime() );
    return ( (float)$usec + (float)$sec );
}

// *****************************************************************************
// Purpose        this function works without errors ( as is_writable PHP functoin )
// Inputs           $url
// Remarks
// Returns        nothing
function IsWriteable( $fileName ) {
    $f = @fopen( $fileName, "a" );
    return !is_bool( $f );
}

// *****************************************************************************
// Purpose        redirects to other PHP page specified URL ( $url )
// Inputs           $url
// Remarks        this function uses header
// Returns        nothing
function Redirect( $url ) {
    header( "Location: " . $url );
    exit();
}

// *****************************************************************************
// Purpose        redirects to other PHP page specified URL ( $url )
// Inputs
// Remarks        if CONF_PROTECTED_CONNECTION == '1' this function uses protected ( https:// ) connection
//                        else it uses unsecure http://
//                        $url is relative URL, NOT an absolute one, e.g. index.php, index.php?productID=x, but not http://www.example.com/
// Returns        nothing
function RedirectProtected( $url ) {
    if ( CONF_PROTECTED_CONNECTION == '1' ) {
        Redirect( correct_URL( CONF_FULL_SHOP_URL, "https" ) . $url ); //redirect to HTTPS part of the website
    } else {
        Redirect( $url );
    }
    //relative URL
}

// *****************************************************************************
// Purpose        redirects to other PHP page specified URL ( $url )
// Inputs           $url
// Remarks        this function uses JavaScript client script
// Returns        nothing
function RedirectJavaScript( $url ) {
    die( "<script type='text/javascript'> window.location = '" . $url . "'; </script>" );
}



function _testExtension( $filename, $extension ) {
    if ( $extension == null || trim( $extension ) == "" ) {
        return true;
    }

    $i = strlen( $filename ) - 1;
    for ( ; $i >= 0; $i-- ) {
        if ( $filename[$i] == '.' ) {
            break;
        }
    }

    if ( $filename[$i] != '.' ) {
        return false;
    } else {
        $ext = substr( $filename, $i + 1 );
        return ( strtolower( $extension ) == strtolower( $ext ) );
    }
}

// function checklogin() {

//   $rls = array();

//   if (isset($_SESSION["log"])) //look for user in the database

//   {
//     $q = db_query("select cust_password, actions FROM ".CUSTOMERS_TABLE." WHERE Login='".xEscSQL($_SESSION["log"])."'");
//     $row = db_fetch_row($q); //found customer - check password

//     if (!$row || !isset($_SESSION["pass"]) || $row[0]!=$_SESSION["pass"]) //unauthorized access
//     {
//         unset($_SESSION["log"]);
//         unset($_SESSION["pass"]);
//         session_unregister("log"); //calling session_unregister() is required since unset() may not work on some systems
//         session_unregister("pass");

//     }else{

//         $rls = unserialize($row[1]);
//         unset($row);

//     }
//   }

//   return $rls;
// }

/*function checklogin() {

$rls = array();

if ( isset( $_SESSION["log"] ) ) //look for user in the database

{
$q   = db_query( "select cust_password, actions FROM " . CUSTOMERS_TABLE . " WHERE Login='" . xEscSQL( $_SESSION["log"] ) . "'" );
$row = db_fetch_row( $q ); //found customer - check password

if ( !$row || !isset( $_SESSION["pass"] ) || $row[0] != $_SESSION["pass"] ) //unauthorized access
{
unset( $_SESSION["log"] );
unset( $_SESSION["pass"] );
//session_unregister("log"); //calling session_unregister() is required since unset() may not work on some systems
//session_unregister("pass");
} else {

$rls = unserialize( $row[1] );
unset( $row );
# fix log errors WARNING: in_array() expects parameter 2 to be array, boolean given
if ( !is_array( $rls ) ) {
$rls = array();
}
}
}

return $rls;
}*/

// *****************************************************************************
// Purpose        gets all files in specified directory
// Inputs   $dir - full path directory
// Remarks
// Returns
function GetFilesInDirectory( $dir, $extension = "" ) {
    if ( is_dir( $dir ) ) {
        $dh = opendir( $dir );
        if ( !$dh ) {
            return false;
        }
        $files = array();
        while ( false !== ( $filename = readdir( $dh ) ) ) {
            if ( !is_dir( $dir . '/' . $filename ) && $filename != "." && $filename != ".." ) {
                if ( _testExtension( $filename, $extension ) ) {
                    $files[] = $dir . "/" . $filename;
                }
            }
        }
        return $files;
    } else {
        return false;
    }
}

// ant????
// *****************************************************************************
// Purpose        gets class name in file
// Inputs   $fileName - full file name
// Remarks        this file must contains only one class syntax valid declaration
// Returns        class name
function GetClassName( $fileName ) {
    $strContent = myfile_get_contents( $fileName );
    $_match     = array();
    $strContent = substr( $strContent, strpos( $strContent, '@connect_module_class_name' ), 100 );

    // echo ( $strContent)." __ ".( $fileName). "<br>";

    if ( preg_match( "|\@connect_module_class_name[\t ]+([0-9a-z_]*)|mi", $strContent, $_match ) ) {
        return $_match[1];
    } else {
        return false;
    }
}

function InstallModule( $module ) {
    db_query( "INSERT INTO " . MODULES_TABLE . " ( module_name ) " .
        " VALUES( '" . xEscSQL( $module->title ) . "' ) " );
}

function GetModuleId( $module ) {
    $q = db_query( "SELECT module_id FROM " . MODULES_TABLE .
        " WHERE module_name='" . xEscSQL( $module->title ) . "' " );
    $row = db_fetch_row( $q );
    return (int)$row["module_id"];
}

/*function _formatPrice( $price, $rval = 2, $dec = '.', $term = ' ' ) {
# BEGIN исправление ошибки при "отрицательном" округлении в настройке валют
#         return number_format($price, $rval, $dec, $term);
return number_format( round( $price, $rval ), $rval, $dec, $term );
# END исправление ошибки при "отрицательном" округлении в настройке валют
}
 */
//show a number and selected currency sign $price is in universal currency
/*function show_price( $price, $custom_currency = 0, $code = true, $d = ".", $t = " " ) {
global $selected_currency_details;
//if $custom_currency != 0 show price this currency with ID = $custom_currency
if ( $custom_currency == 0 ) {
if ( !isset( $selected_currency_details ) || !$selected_currency_details ) //no currency found

{
return $price;
}
} else //show price in custom currency

{

$q = db_query( "SELECT code, currency_value, where2show, currency_iso_3, Name, roundval FROM " .
CURRENCY_TYPES_TABLE . " WHERE CID=" . (int)$custom_currency );
if ( $row = db_fetch_row( $q ) ) {
$selected_currency_details = $row; //for show_price() function
} else //no currency found. In this case check is there any currency type in the database

{
$q = db_query( "SELECT code, currency_value, where2show, roundval FROM " . CURRENCY_TYPES_TABLE );
if ( $row = db_fetch_row( $q ) ) {
$selected_currency_details = $row; //for show_price() function
}
}
}

//is exchange rate negative or 0?
if ( $selected_currency_details[1] == 0 ) {
return "";
}

$price = roundf( $price * $selected_currency_details[1] );
//now show price
$price = _formatPrice( $price, $selected_currency_details["roundval"], $d, $t );
if ( $code ) {
return $selected_currency_details[2] ? $price . $selected_currency_details[0] : $selected_currency_details[0] . $price;
} else {
return $price;
}
}*/

function ShowPriceInTheUnit( $price, $currencyID ) {
    $q_currency = db_query( "SELECT currency_value, where2show, code, roundval FROM " . CURRENCY_TYPES_TABLE . " WHERE CID=" . (int)$currencyID );
    $currency   = db_fetch_row( $q_currency );
    // $price=($currencyID ==5)?$price*(100+20)/100:$price;
    $price = _formatPrice( roundf( $price * $currency["currency_value"] ), $currency["roundval"] );
    return $currency["where2show"] ? $price . $currency["code"] : $currency["code"] . $price;
}

function addUnitToPrice( $price ) {
    global $selected_currency_details;
    $price = _formatPrice( $price, $selected_currency_details["roundval"] );
    return $selected_currency_details[2] ? $price . $selected_currency_details[0] : $selected_currency_details[0] .
        $price;
}

function ConvertPriceToUniversalUnit( $priceWithOutUnit ) {
    global $selected_currency_details;
    return (float)$priceWithOutUnit / (float)$selected_currency_details[1];
}

function show_priceWithOutUnit( $price ) {
    global $selected_currency_details;

    if ( !isset( $selected_currency_details ) || !$selected_currency_details ) //no currency found

    {
        return $price;
    }

    //is exchange rate negative or 0?
    if ( $selected_currency_details[1] == 0 ) {
        return "";
    }

    //now show price
    $price = round( 100 * $price * $selected_currency_details[1] ) / 100;
    if ( round( $price * 10 ) == $price * 10 && round( $price ) != $price ) {
        $price = "$price" . "0";
    }
    //to avoid prices like 17.5 - write 17.50 instead
    return (float)$price;
}

function show_priceWithOutUnitF( $price ) {
    global $selected_currency_details;

    if ( !isset( $selected_currency_details ) || !$selected_currency_details ) //no currency found

    {
        return $price;
    }

    //is exchange rate negative or 0?
    if ( $selected_currency_details["roundval"] == 0 ) {
        return "";
    }

    return _formatPrice( roundf( $price * $selected_currency_details[1] ), $selected_currency_details["roundval"] );
}

function getPriceUnit() {
    global $selected_currency_details;

    if ( !isset( $selected_currency_details ) || !$selected_currency_details ) //no currency found

    {
        return "";
    }
    return $selected_currency_details[0];
}

function getLocationPriceUnit() {
    global $selected_currency_details;

    if ( !isset( $selected_currency_details ) || !$selected_currency_details ) //no currency found

    {
        return true;
    }
    return $selected_currency_details[2];
}

/*
function get_current_time() //get current date and time as a string
//required to do INSERT queries of DATETIME/TIMESTAMP in different DBMSes
{
$timestamp = time();
if (DBMS == 'mssql')
// $s = strftime("%H:%M:%S %d/%m/%Y", $timestamp);
$s = strftime("%m.%d.%Y %H:%M:%S", $timestamp);
else // MYSQL or IB
$s = strftime("%Y-%m-%d %H:%M:%S", $timestamp);

return $s;
}
 */

function ShowNavigator( $a, $offset, $q, $path, &$out ) {
    //shows navigator [prev] 1 2 3 4 … [next]
    //$a - count of elements in the array, which is being navigated
    //$offset - current offset in array (showing elements [$offset ... $offset+$q])
    //$q - quantity of items per page
    //$path - link to the page (f.e: "index.php?categoryID=1&")

    if ( $a > $q ) //if all elements couldn't be placed on the page

    {
        //[prev]
        if ( $offset > 0 ) {
            $out .= "<a href=\"" . $path . "offset=" . ( $offset - $q ) . "\">&lsaquo; " . STRING_PREVIOUS .
                "</a>&nbsp;&nbsp;";
        }

        //digital links
        $k = $offset / $q;

        //not more than 4 links to the left
        $min = $k - 5;
        if ( $min < 0 ) {
            $min = 0;
        } else {
            if ( $min >= 1 ) {
                //link on the 1st page
                $out .= "<a href=\"" . $path . "offset=0\">1</a>&nbsp;&nbsp;";
                if ( $min != 1 ) {
                    $out .= "... &nbsp;&nbsp;";
                }
                ;
            }
        }

        for ( $i = $min; $i < $k; $i++ ) {
            $m = $i * $q + $q;
            if ( $m > $a ) {
                $m = $a;
            }

            $out .= "<a href=\"" . $path . "offset=" . ( $i * $q ) . "\">" . ( $i + 1 ) . "</a>&nbsp;&nbsp;";
        }

        //# of current page
        if ( strcmp( $offset, "show_all" ) ) {
            $min = $offset + $q;
            if ( $min > $a ) {
                $min = $a;
            }

            $out .= "<b>" . ( $k + 1 ) . "</b>&nbsp;&nbsp;";
        } else {
            $min = $q;
            if ( $min > $a ) {
                $min = $a;
            }

            $out .= "<a href=\"" . $path . "offset=0\">1</a>&nbsp;&nbsp;";
        }

        //not more than 5 links to the right
        $min = $k + 6;
        if ( $min > $a / $q ) {
            $min = $a / $q;
        }
        ;
        for ( $i = $k + 1; $i < $min; $i++ ) {
            $m = $i * $q + $q;
            if ( $m > $a ) {
                $m = $a;
            }

            $out .= "<a href=\"" . $path . "offset=" . ( $i * $q ) . "\">" . ( $i + 1 ) . "</a>&nbsp;&nbsp;";
        }

        if ( ceil( $min * $q ) < $a ) {
            //the last link
            if ( $min * $q < $a - $q ) {
                $out .= "... &nbsp;&nbsp;";
            }

            $out .= "<a href=\"" . $path . "offset=" . ( $a - $a % $q ) . "\">" . ( floor( $a / $q ) + 1 ) . "</a>&nbsp;&nbsp;";
        }

        //[next]
        if ( strcmp( $offset, "show_all" ) ) {
            if ( $offset < $a - $q ) {
                $out .= "<a href=\"" . $path . "offset=" . ( $offset + $q ) . "\">" . STRING_NEXT .
                    " &rsaquo;</a>&nbsp;&nbsp;";
            }
        }

        //[show all]
        // # BEGIN показать все - убираем
        if ( $a <= CONF_PRODUCTS_SHOW_ALL * CONF_PRODUCTS_PER_PAGE ) {
// # END показать все - убираем
            if ( strcmp( $offset, "show_all" ) ) {
                $out .= "|&nbsp;&nbsp;<a href=\"" . $path . "show_all=yes\">" .
                    STRING_SHOWALL . "</a>";
            } else {
                $out .= "|&nbsp;&nbsp;<b>" . STRING_SHOWALL . "</b>";
            }
        }
    }
}

function ShowNavigatorNEW( $a, $offset, $q, $path, &$out ) {
    //shows navigator [prev] 1 2 3 4 … [next]
    //$a - count of elements in the array, which is being navigated
    //$offset - current offset in array (showing elements [$offset ... $offset+$q])
    //$q - quantity of items per page
    //$path - link to the page (f.e: "index.php?categoryID=1&")

    if ( $a > $q ) //if all elements couldn't be placed on the page

    {
        //[prev]
        if ( $offset > 0 ) {
            $out .= "<li class='page-item'><a class='page-link' href=\"" . $path . "offset=" . ( $offset - $q ) . "\">&lsaquo;</a></li>";
        }

        //digital links
        $k = $offset / $q;

        //not more than 4 links to the left
        $min = $k - 4;
        if ( $min < 0 ) {
            $min = 0;
        } else {
            if ( $min >= 1 ) {
                //link on the 1st page
                $out .= "<li class='page-item'><a class='page-link' href=\"" . $path . "offset=0\">1</a></li>";
                if ( $min != 1 ) {
                    $out .= "<li class='page-item'><span class='page-link'>...</span></li>";
                }
                ;
            }
        }

        for ( $i = $min; $i < $k; $i++ ) {
            $m = $i * $q + $q;
            if ( $m > $a ) {
                $m = $a;
            }

            $out .= "<li class='page-item'><a class='page-link' href=\"" . $path . "offset=" . ( $i * $q ) . "\">" . ( $i + 1 ) . "</a></li>";
        }

        //# of current page
        if ( strcmp( $offset, "show_all" ) ) {
            $min = $offset + $q;
            if ( $min > $a ) {
                $min = $a;
            }

            $out .= "<li class='page-item active'  aria-current='page'><span  class='page-link'>" . ( $k + 1 ) . "</span></li>";
        } else {
            $min = $q;
            if ( $min > $a ) {
                $min = $a;
            }

            $out .= "<li class='page-item'><a class='page-link' href=\"" . $path . "offset=0\">1</a></li>";
        }

        //not more than 5 links to the right
        $min = $k + 5;
        if ( $min > $a / $q ) {
            $min = $a / $q;
        }
        ;
        for ( $i = $k + 1; $i < $min; $i++ ) {
            $m = $i * $q + $q;
            if ( $m > $a ) {
                $m = $a;
            }

            $out .= "<li class='page-item'><a class='page-link' href=\"" . $path . "offset=" . ( $i * $q ) . "\">" . ( $i + 1 ) . "</a></li>";
        }

        if ( ceil( $min * $q ) < $a ) {
            //the last link
            if ( $min * $q < $a - $q ) {
                $out .= "<li><span class='page-link'>...</span></li>";
            }

            $out .= "<li class='page-item'><a class='page-link' href=\"" . $path . "offset=" . ( $a - $a % $q ) . "\">" . ( floor( $a / $q ) + 1 ) . "</a></li>";
        }

        //[next]
        if ( strcmp( $offset, "show_all" ) ) {
            if ( $offset < $a - $q ) {
                $out .= "<li class='page-item'><a class='page-link' href=\"" . $path . "offset=" . ( $offset + $q ) . "\">&rsaquo;</a></li>";
            }
        }

        //[show all]
        if ( $a <= CONF_PRODUCTS_SHOW_ALL * CONF_PRODUCTS_PER_PAGE ) {
            if ( strcmp( $offset, "show_all" ) ) {
                $out .= "<li class='page-item'><a class='page-link' href=\"" . $path . "show_all=yes\">" . STRING_SHOWALL . "</a></li>";
            } else {
                $out .= "<li class='page-item active'  aria-current='page'><span class='page-link'>" . STRING_SHOWALL . "</span></li>";
            }
        }
    }

}

function ShowNavigatormd( $a, $offset, $q, $path, &$out ) {
    //shows navigator [prev] 1 2 3 4 … [next]
    //$a - count of elements in the array, which is being navigated
    //$offset - current offset in array (showing elements [$offset ... $offset+$q])
    //$q - quantity of items per page
    //$path - link to the page (f.e: "index.php?categoryID=1&")

    if ( $a > $q ) //if all elements couldn't be placed on the page

    {
        //[prev]
        if ( $offset > 0 ) {
            $out .= "<li><a href=\"" . $path . "offset_" . ( $offset - $q ) . ".html\">&lsaquo;</a></li>";
        }

        //digital links
        $k = $offset / $q;

        //not more than 4 links to the left
        $min = $k - 4;
        if ( $min < 0 ) {
            $min = 0;
        } else {
            if ( $min >= 1 ) {
                //link on the 1st page
                $out .= "<li><a href=\"" . $path . "offset_0.html\">1</a></li>";
                if ( $min != 1 ) {
                    $out .= "<li><span>...</span></li>";
                }
                ;
            }
        }

        for ( $i = $min; $i < $k; $i++ ) {
            $m = $i * $q + $q;
            if ( $m > $a ) {
                $m = $a;
            }

            $out .= "<li><a href=\"" . $path . "offset_" . ( $i * $q ) . ".html\">" . ( $i + 1 ) . "</a></li>";
        }

        //# of current page
        if ( strcmp( $offset, "show_all" ) ) {
            $min = $offset + $q;
            if ( $min > $a ) {
                $min = $a;
            }

            $out .= "<li class='active'><span>" . ( $k + 1 ) . "</span></li>";
        } else {
            $min = $q;
            if ( $min > $a ) {
                $min = $a;
            }

            $out .= "<li><a href=\"" . $path . "offset_0.html\">1</a></li>";
        }

        //not more than 5 links to the right
        $min = $k + 5;
        if ( $min > $a / $q ) {
            $min = $a / $q;
        }
        ;
        for ( $i = $k + 1; $i < $min; $i++ ) {
            $m = $i * $q + $q;
            if ( $m > $a ) {
                $m = $a;
            }

            $out .= "<li><a href=\"" . $path . "offset_" . ( $i * $q ) . ".html\">" . ( $i + 1 ) . "</a></li>";
        }

        if ( ceil( $min * $q ) < $a ) {
            //the last link
            if ( $min * $q < $a - $q ) {
                $out .= "<li><span>...</span></li>";
            }

            $out .= "<li><a href=\"" . $path . "offset_" . ( $a - $a % $q ) . ".html\">" . ( floor( $a / $q ) + 1 ) . "</a></li>";
        }

        //[next]
        if ( strcmp( $offset, "show_all" ) ) {
            if ( $offset < ( $a - $q ) ) {
                $out .= "<li><a href=\"" . $path . "offset_" . ( $offset + $q ) . ".html\">&rsaquo;</a></li>";
            }
        }

        //[show all]
        // # BEGIN показать все - убираем
        if ( $a <= CONF_PRODUCTS_SHOW_ALL * CONF_PRODUCTS_PER_PAGE ) {
// # END показать все - убираем
            if ( strcmp( $offset, "show_all" ) ) {
                $out .= "<li><a href=\"" . $path . "show_all.html\" rel=\"nofollow\">" . STRING_SHOWALL . "</a></li>";
            } else {
                $out .= "<li class='active'><span>" . STRING_SHOWALL . "</span></li>";
            }

        }
    }

    $out .= "<hr>";
}

function GetNavigatorHtmlmd( $url, $countRowOnPage = CONF_PRODUCTS_PER_PAGE, $callBackFunction, $callBackParam,
    &$tableContent, &$offset, &$count, $urlflag ) {
    if ( isset( $_GET["offset"] ) ) {
        $offset = (int)$_GET["offset"];
    } else {
        $offset = 0;
    }

    $offset -= $offset % $countRowOnPage; //CONF_PRODUCTS_PER_PAGE;
    if ( $offset < 0 ) {
        $offset = 0;
    }

    $count = 0;

    if ( !isset( $_GET["show_all"] ) ) //show 'CONF_PRODUCTS_PER_PAGE' products on this page

    {
        $tableContent = $callBackFunction( $callBackParam, $count, array( "offset" => $offset, "CountRowOnPage" =>
            $countRowOnPage ) );
    } else //show all products

    {
        $tableContent = $callBackFunction( $callBackParam, $count, null );
        $offset       = "show_all";
    }

    if ( $urlflag ) {
        ShowNavigatormd( $count, $offset, $countRowOnPage, html_spchars( $url . "_" ), $out );
    } else {
        ShowNavigatorNEW( $count, $offset, $countRowOnPage, html_spchars( $url . "&" ), $out );
    }

    return $out;
}

function GetCurrentURL( $file, $exceptKeys ) {
    $res = $file;
    foreach ( $_GET as $key => $val ) {
        $exceptFlag = false;
        foreach ( $exceptKeys as $exceptKey ) {
            if ( $exceptKey == $key ) {
                $exceptFlag = true;
                break;
            }
        }

        if ( !$exceptFlag ) {
            if ( $res == $file ) {
                $res .= "?" . $key . "=" . $val;
            } else {
                $res .= "&" . $key . "=" . $val;
            }
        }
    }
    return $res;
}

function GetNavigatorHtml( $url, $countRowOnPage = CONF_PRODUCTS_PER_PAGE, $callBackFunction, $callBackParam,
    &$tableContent, &$offset, &$count ) {
    if ( isset( $_GET["offset"] ) ) {
        $offset = (int)$_GET["offset"];
    } else {
        $offset = 0;
    }

    $offset -= $offset % $countRowOnPage; //CONF_PRODUCTS_PER_PAGE;
    if ( $offset < 0 ) {
        $offset = 0;
    }

    $count = 0;

    if ( !isset( $_GET["show_all"] ) ) //show 'CONF_PRODUCTS_PER_PAGE' products on this page

    {
        $tableContent = $callBackFunction( $callBackParam, $count, array( "offset" => $offset, "CountRowOnPage" =>
            $countRowOnPage ) );
    } else //show all products

    {
        $tableContent = $callBackFunction( $callBackParam, $count, null );
        $offset       = "show_all";
    }

    ShowNavigatorNEW( $count, $offset, $countRowOnPage, html_spchars( $url . "&" ), $out );
    // ShowNavigator( $count, $offset, $countRowOnPage, html_spchars( $url . "&" ), $out );
    return $out;
}

function GetNavigatorHtmlNEW( $url, $countRowOnPage = CONF_PRODUCTS_PER_PAGE, $callBackFunction, $callBackParam,
    &$tableContent, &$offset, &$count ) {
    if ( isset( $_GET["offset"] ) ) {
        $offset = (int)$_GET["offset"];
    } else {
        $offset = 0;
    }

    $offset -= $offset % $countRowOnPage; //CONF_PRODUCTS_PER_PAGE;
    if ( $offset < 0 ) {
        $offset = 0;
    }

    $count = 0;

    if ( !isset( $_GET["show_all"] ) ) //show 'CONF_PRODUCTS_PER_PAGE' products on this page

    {
        $tableContent = $callBackFunction( $callBackParam, $count, array( "offset" => $offset, "CountRowOnPage" =>
            $countRowOnPage ) );
    } else //show all products

    {
        $tableContent = $callBackFunction( $callBackParam, $count, null );
        $offset       = "show_all";
    }

    ShowNavigatorNEW( $count, $offset, $countRowOnPage, html_spchars( $url . "&" ), $out );
    return $out;
}

function moveCartFromSession2DB() //all products in shopping cart, which are in session vars, move to the database

{
    if ( isset( $_SESSION["gids"] ) && isset( $_SESSION["log"] ) ) {
        $customerID = regGetIdByLogin( $_SESSION["log"] );
        $q          = db_query( "select itemID from " . SHOPPING_CARTS_TABLE . " where customerID=" . (int)$customerID );
        $items      = array();
        while ( $item = db_fetch_row( $q ) ) {
            $items[] = (int)$item["itemID"];
        }

        //$i=0;
        foreach ( $_SESSION["gids"] as $key => $productID ) {
            if ( $productID == 0 ) {
                continue;
            }

            // search product in current user's shopping cart content
            $itemID = null;
            for ( $j = 0; $j < count( $items ); $j++ ) {
                $q = db_query( "select count(*) from " . SHOPPING_CART_ITEMS_TABLE . " where productID=" .
                    (int)$productID . " AND itemID=" . (int)$items[$j] );
                $count = db_fetch_row( $q );
                $count = $count[0];
                if ( $count != 0 ) {
                    // compare configuration
                    $configurationFromSession = $_SESSION["configurations"][$key];
                    $configurationFromDB      = GetConfigurationByItemId( $items[$j] );
                    if ( CompareConfiguration( $configurationFromSession, $configurationFromDB ) ) {
                        $itemID = $items[$j];
                        break;
                    }
                }
            }

            if ( $itemID == null ) {
                // create new item
                db_query( "insert into " . SHOPPING_CART_ITEMS_TABLE . " (productID) values(" . (int)$productID . ")" );
                $itemID = db_insert_id();

                // set content item
                foreach ( $_SESSION["configurations"][$key] as $vars ) {
                    db_query( "insert into " . SHOPPING_CART_ITEMS_CONTENT_TABLE . " ( itemID, variantID ) " .
                        " values( " . (int)$itemID . ", " . (int)$vars . " )" );
                }

                // insert item into cart
                db_query( "insert " . SHOPPING_CARTS_TABLE . " (customerID, itemID, Quantity) values ( " .
                    (int)$customerID . ", " . (int)$itemID . ", " . (int)$_SESSION["counts"][$key] . " )" );
            } else {
                db_query( "update " . SHOPPING_CARTS_TABLE . " set Quantity=Quantity + " . (int)$_SESSION["counts"][$key] . " where customerID=" . (int)$customerID . " and itemID=" . (int)$itemID );
            }
        }

        unset( $_SESSION["gids"] );
        unset( $_SESSION["counts"] );
        unset( $_SESSION["configurations"] );
        session_unregister( "gids" ); //calling session_unregister() is required since unset() may not work on some systems
        session_unregister( "counts" );
        session_unregister( "configurations" );
    }
}

// moveCartFromSession2DB

function validate_search_string( $s ) //validates $s - is it good as a search query

{
    //exclude special SQL symbols
    $s = str_replace( "%", "", $s );
    $s = str_replace( "_", "", $s );
    //",',\
    $s = stripslashes( $s );
    $s = str_replace( "'", "\'", $s );
    return $s;
}

//validate_search_string

function string_encode( $s ) // encodes a string with a simple algorythm

{
    $result = base64_encode( $s );
    return $result;
}

function string_decode( $s ) // decodes a string encoded with string_encode()

{
    $result = base64_decode( $s );
    return $result;
}

// *****************************************************************************
// Purpose        this function creates array it containes value POST variables
// Inputs                     name array
// Remarks                if <name> is contained in $varnames, then for POST variable
//                                <name>_<id> in result array $data (see body) item is added
//                                with key <id> and POST variable <name>_<id> value
// Returns                array $data ( see Remarks )
function ScanPostVariableWithId( $varnames ) {
    $data = array();
    foreach ( $varnames as $name ) {
        foreach ( $_POST as $key => $value ) {
            if ( strstr( $key, $name . "_" ) ) {
                $key               = str_replace( $name . "_", "", $key );
                $data[$key][$name] = $value;
            }
        }
    }
    return $data;
}

function ScanFilesVariableWithId( $varnames ) {
    $data = array();
    foreach ( $varnames as $name ) {
        foreach ( $_FILES as $key => $value ) {
            if ( strstr( $key, $name . "_" ) ) {
                $key               = str_replace( $name . "_", "", $key );
                $data[$key][$name] = $value;
            }
        }
    }
    return $data;
}

// *****************************************************************************
// Purpose        this functin does also as ScanPostVariableWithId
//                        but it uses GET variables
// Inputs             see ScanPostVariableWithId
// Remarks        see ScanPostVariableWithId
// Returns        see ScanPostVariableWithId
function ScanGetVariableWithId( $varnames ) {
    $data = array();
    foreach ( $varnames as $name ) {
        foreach ( $_GET as $key => $value ) {
            if ( strstr( $key, $name . "_" ) ) {
                $key               = str_replace( $name . "_", "", $key );
                $data[$key][$name] = $value;
            }
        }
    }
    return $data;
}

function value( $variable ) {
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
        $res .= "<b>integer</b> ";
        $res .= (string)$variable;
    } elseif ( is_bool( $variable ) ) {
        $res .= "<b>bool</b> ";
        if ( $variable ) {
            $res .= "<i>True</i>";
        } else {
            $res .= "<i>False</i>";
        }
    } elseif ( is_string( $variable ) ) {
        $res .= "<b>string</b> ";
        $res .= "'" . (string)$variable . "'";
    } elseif ( is_float( $variable ) ) {
        $res .= "<b>float</b> ";
        $res .= (string)$variable;
    }

    return $res;
}

function debug( $variable ) {
    if ( !isset( $variable ) ) {
        echo ( "undefined" );
    } else {
        echo "<div align=\"left\">";
        echo ( value( $variable ) . "<br>" );
        echo "</div>";
    }
}

/*function debugfile( $variable, $remark="",$showTime=true ) {
$fp = fopen('core/temp/FILEDEBUG.JSON', 'a');
echo $remark;
$remark= (strlen($remark)>=1)?json_encode(xHtmlSpecialCharsDecode($remark)):"NULL";
$Time= ($showTime)?json_encode(get_current_time()." ".microtime(true)):"NULL";
fwrite($fp, "TIME: $Time \r\n");
fwrite($fp, "REQUEST_URI: ". $_SERVER["REQUEST_URI"] ." \r\n");
fwrite($fp, "REMARK: $remark \r\n");
fwrite($fp, "VARIABLE (".gettype ( $variable ) .")\r\n");
fwrite($fp,json_encode( $variable));
fwrite($fp," \r\n");
fwrite($fp, "\r\n");
fclose($fp);
}
 */

function logfile( $text, $filename = "core/temp/logfile.txt" ) {
    $fp = fopen( $filename, "a" );
    fwrite( $fp, get_current_time() . " " . microtime( true ) . " ... " );
    fwrite( $fp, $text . "\r\n" );
    fclose( $fp );
}

function clslogfile( $filename = "core/temp/logfile.txt" ) {
// $filename = "/path/to/foo.txt";

    if ( file_exists( $filename ) ) {
        // directlog( "The file $filename exists");
        unlink( $filename );
    } else {
        directlog( "clslogfile :: The file $filename does not exist" );
    }

}


function cash_sql_query( $string ) {
    // $string = 'Какие-то данные для сжатия';
    // $gz = gzopen('core/temp/yml_to_sql_export.sql.gz','wb5');
    // $gz = gzopen('adminer.sql.gz','wb9');
    // gzwrite($gz, $string);
    // gzclose($gz);
    // console($string);
    // $fp = fopen( 'core/temp/yml_to_sql_export.sql', 'a' );
    $fp = fopen( 'adminer.sql', 'a' );
    fwrite( $fp, $string ); //." \r\n"
    fclose( $fp );
}

function writeResultToFile( $string, $fn = "MyFile", $dir = "data/files/NixRuCatalogueFolder/" ) {

    $fp = fopen( $dir . $fn . ".json", "a" );
                                   // debugfile( $dir . $fn . ".json");
    fwrite( $fp, $string . "\r\n" ); //." \r\n"
    fclose( $fp );
}

function writeResultToFileGZ( $string, $fn = "MyFile", $dir = "data/files/NixRuCatalogueFolder/" ) {
    $gz = gzopen( $dir . $fn . ".json.gz", 'a9' );
    gzwrite( $gz, $string . "\r\n" );
    gzclose( $gz );
}

function set_query( $_vars, $_request = '', $_store = false ) {

    if ( !$_request ) {
        global $_SERVER;
        $_request = $_SERVER['REQUEST_URI'];
    }

    $_anchor                   = '';
    @list( $_request, $_anchor ) = explode( '#', $_request );

    if ( strpos( $_vars, '#' ) !== false ) {
        @list( $_vars, $_anchor ) = explode( '#', $_vars );
    }

    if ( !$_vars && !$_anchor ) {
        return preg_replace( '|\?.*$|', '', $_request ) . ( $_anchor ? '#' . $_anchor :
            '' );
    } elseif ( !$_vars && $_anchor ) {
        return $_request . '#' . $_anchor;
    }

    $_rvars  = array();
    $tr_vars = explode( '&', strpos( $_request, '?' ) !== false ? preg_replace( '|.*\?|', '', $_request ) :
        '' );
    foreach ( $tr_vars as $_var ) {
        $_t = explode( '=', $_var );
        if ( $_t[0] ) {
            $_rvars[$_t[0]] = $_t[1];
        }
    }
    $tr_vars = explode( '&', preg_replace( array( '|^\&|', '|^\?|' ), '', $_vars ) );
    foreach ( $tr_vars as $_var ) {
        $_t = explode( '=', $_var );
        if ( !$_t[1] ) {
            unset( $_rvars[$_t[0]] );
        } else {
            $_rvars[$_t[0]] = $_t[1];
        }
    }
    $tr_vars = array();
    foreach ( $_rvars as $_var => $_val ) {
        $tr_vars[] = "$_var=$_val";
    }

    if ( $_store ) {
        global $_SERVER;
        $_request               = $_SERVER['REQUEST_URI'];
        $_SERVER['REQUEST_URI'] = preg_replace( '|\?.*$|', '', $_request ) . ( count( $tr_vars ) ? '?' . implode
            ( '&', $tr_vars ) : '' ) . ( $_anchor ? '#' . $_anchor : '' );
        return $_SERVER['REQUEST_URI'];
    } else {
        return preg_replace( '|\?.*$|', '', $_request ) . ( count( $tr_vars ) ? '?' . implode( '&', $tr_vars ) :
            '' ) . ( $_anchor ? '#' . $_anchor : '' );
    }
}

function getListerRange( $_pagenumber, $_totalpages, $_lister_num = 20 ) {

    if ( $_pagenumber <= 0 ) {
        return array( 'start' => 1, 'end' => 1 );
    }

    $lister_start = $_pagenumber - floor( $_lister_num / 2 );
    $lister_start = ( $lister_start + $_lister_num <= $_totalpages ? $lister_start : $_totalpages -
        $_lister_num + 1 );
    $lister_start = ( $lister_start > 0 ? $lister_start : 1 );
    $lister_end   = $lister_start + $_lister_num - 1;
    $lister_end   = ( $lister_end <= $_totalpages ? $lister_end : $_totalpages );
    return array( 'start' => $lister_start, 'end' => $lister_end );
}

/*function html_spchars( $_data ) {

if ( is_array( $_data ) ) {
foreach ( $_data as $_ind => $_val ) {
$_data[$_ind] = html_spchars( $_val );
}
return $_data;
} else {
return htmlspecialchars( $_data, ENT_QUOTES );
}
}*/

function html_amp( $_data ) {

    if ( is_array( $_data ) ) {
        foreach ( $_data as $_ind => $_val ) {
            $_data[$_ind] = strtr( $_val, array( '&' => '&amp;' ) );
        }
        return $_data;
    } else {
        return strtr( $_data, array( '&' => '&amp;' ) );
    }
}
/*# htmlspecialchars — Преобразует специальные символы в HTML-сущности
function ToText( $str ) {
$str = htmlspecialchars( trim( $str ), ENT_QUOTES );
return $str;
}*/
/*# xHtmlSpecialChars Преобразует [МАССИВЫЫ] специальные символы в HTML-сущности
function xToText( $str ) {
$str = xEscSQL( xHtmlSpecialChars( $str ) );
return $str;
}*/

function xByteMaskToInteger( $arr ) {
    $result = 0;
    if ( is_array( $arr ) ) {
        $aSum = 0;
        for ( $i = 0; $i < count( $arr ); $i++ ) {
            $aN   = $arr[$i];
            $aSum = $aSum + $aN;
        }
        $result = $aSum;
    } else {
        $result = $arr;
    }
    // debugfile($result,"functions.php:1799 xByteMaskToInteger");
    return (int)$result;
}

function xToInt( $data ) {
    return (int)$data;
}

function xToFloat( $data ) {
    return (float)$data;
}
function xToDouble( $data ) {
    return (double)$data;
}
function xEscSQL_trim( $data ) {
    return xEscSQL( trim( $data ) );
}

function xToText_trim( $data ) {
    return xToText( trim( $data ) );
}

// function xStripSlashesGPC( $_data ) {

//     if ( !get_magic_quotes_gpc() ) {
//         return $_data;
//     }

//     if ( is_array( $_data ) ) {
//         foreach ( $_data as $_ind => $_val ) {
//             $_data[$_ind] = xStripSlashesGPC( $_val );
//         }
//         return $_data;
//     }
//     return stripslashes( $_data );
// }

/**
 * Transform date from template format to DATETIME format
 *
 * @param string $_date
 * @param string $_template template for transform
 * @return string
 */
function TransformTemplateToDATE( string $_date, string $_template = '' ): string {

    if ( !$_template ) {
        $_template = CONF_DATE_FORMAT;
    }

    $day   = substr( $_date, strpos( $_template, 'DD' ), 2 );
    $month = substr( $_date, strpos( $_template, 'MM' ), 2 );
    $year  = substr( $_date, strpos( $_template, 'YYYY' ), 4 );
    return "{$year}-{$month}-{$day} ";
}

/**
 * Transform DATE to template format
 *
 * @param string $_date
 * @param string $_template template for transform
 * @return string
 */
function TransformDATEToTemplate( string $_date, string $_template = '' ): string {

    if ( !$_template ) {
        $_template = CONF_DATE_FORMAT;
    }

    preg_match( '|(\d{4})-(\d{2})-(\d{2})|', $_date, $mathes );
    unset( $mathes[0] );
    return str_replace( array( 'YYYY', 'MM', 'DD' ), $mathes, $_template );
}

/**
 * Check date in template format
 *
 * @param string $_date
 * @param string $_template template for check
 * @return bool
 */
function isTemplateDate( string $_date, string $_template = '' ): bool {

    if ( !$_template ) {
        $_template = CONF_DATE_FORMAT;
    }

    $ok = ( strlen( $_date ) == strlen( $_template ) && ( preg_replace( '|\d{2}|', '', $_date ) == str_replace
        ( array( 'MM', 'DD', 'YYYY' ), '', $_template ) ) );
    $ok = ( $ok && substr( $_date, strpos( $_template, 'DD' ), 2 ) < 32 && substr( $_date, strpos( $_template,
        'MM' ), 2 ) < 13 );
    return $ok;
}

/**
 * mail txt message from template
 * @param string email
 * @param string email subject
 * @param string template name
 */
function xMailTxt( $_Email, $_Subject, $_TemplateName, $_AssignArray = array() ) {

    if ( !$_Email ) {
        return 0;
    }

    $mailSmarty = new Smarty();
    foreach ( $_AssignArray as $_var => $_val ) {
        $mailSmarty->assign( $_var, $_val );
    }

    $_msg = $mailSmarty->fetch( 'email/' . $_TemplateName );

    include_once "core/classes/class.phpmailer.php";
    $mail = new PHPMailer();
    // include_once "core/classes/PHPMailer2.php";
    // $mail = new PHPMailer2();
    if ( !CONF_MAIL_METHOD ) {
        $mail->IsSMTP();
    } else {
        $mail->IsMail();
    }

    $mail->Host     = CONF_MAIL_HOST;
    $mail->Username = CONF_MAIL_LOGIN;
    $mail->Password = CONF_MAIL_PASS;
    $mail->SMTPAuth = true;
    $mail->From     = CONF_GENERAL_EMAIL;
    $mail->FromName = CONF_SHOP_NAME;
    $mail->CharSet  = DEFAULT_CHARSET;
    $mail->Encoding = "8bit";
    $mail->SetLanguage( "ru" );
    $mail->AddReplyTo( CONF_GENERAL_EMAIL, CONF_SHOP_NAME );
    $mail->IsHTML( true );
    $mail->Subject = $_Subject;
    $mail->Body    = $_msg;
    $mail->AltBody = ERROR_NO_TEXT_IN_MAILDATA;

    if ( preg_match( "/^[_\.a-z0-9-]{1,20}@(([a-z0-9-]+\.)+(com|net|org|mil|edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})$/is",
        $_Email ) ) {
        $mail->ClearAddresses();
        $mail->AddAddress( $_Email, '' );
        return $mail->Send();
    } else {
        return false;
    }
}

function xMailTxtHTML( $_Email, $_Subject, $_Text, $castmail = CONF_GENERAL_EMAIL, $castname = CONF_SHOP_NAME ) {

    if ( !$_Email ) {
        return 0;
    }

    include_once "core/classes/class.phpmailer.php";
    $mail = new PHPMailer();
    // include_once "core/classes/PHPMailer2.php";
    // $mail = new PHPMailer2();
    if ( !CONF_MAIL_METHOD ) {
        $mail->IsSMTP();
    } else {
        $mail->IsMail();
    }

    $mail->Host     = CONF_MAIL_HOST;
    $mail->Username = CONF_MAIL_LOGIN;
    $mail->Password = CONF_MAIL_PASS;
    $mail->SMTPAuth = true;
    # BEGIN исправляем отправку email
    #$mail->From = $castmail;
    $mail->From   = CONF_GENERAL_EMAIL;
    $mail->Sender = CONF_GENERAL_EMAIL;
    # END исправляем отправку email
    $mail->FromName = $castname;
    $mail->CharSet  = DEFAULT_CHARSET;
    $mail->Encoding = "8bit";
    $mail->SetLanguage( "ru" );
    $mail->AddReplyTo( $castmail, $castname );
    $mail->IsHTML( false );
    $mail->Subject = $_Subject;
    $mail->Body    = $_Text;

    if ( preg_match( "/^[_\.a-z0-9-]{1,20}@(([a-z0-9-]+\.)+(com|net|org|mil|edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})$/is",
        $_Email ) ) {
        $mail->ClearAddresses();
        $mail->AddAddress( $_Email, '' );
        return $mail->Send();
    } else {
        return false;
    }
}

function xMailTxtHTMLDATA( $_Email, $_Subject, $_Text, $castmail = CONF_GENERAL_EMAIL, $castname = CONF_SHOP_NAME ) {

    if ( !$_Email ) {
        return 0;
    }

    include_once "core/classes/class.phpmailer.php";
    $mail = new PHPMailer();
    // include_once "core/classes/PHPMailer2.php";
    // $mail = new PHPMailer2();
    if ( !CONF_MAIL_METHOD ) {
        $mail->IsSMTP();
    } else {
        $mail->IsMail();
    }

    $mail->Host     = CONF_MAIL_HOST;
    $mail->Username = CONF_MAIL_LOGIN;
    $mail->Password = CONF_MAIL_PASS;
    $mail->SMTPAuth = true;
    # BEGIN исправляем отправку email
    #$mail->From = $castmail;
    $mail->From   = CONF_GENERAL_EMAIL;
    $mail->Sender = CONF_GENERAL_EMAIL;
    # END исправляем отправку email
    $mail->FromName = $castname;
    $mail->CharSet  = DEFAULT_CHARSET;
    $mail->Encoding = "8bit";
    $mail->SetLanguage( "ru" );
    $mail->AddReplyTo( $castmail, $castname );
    $mail->IsHTML( true );
    $mail->Subject = $_Subject;
    $mail->Body    = $_Text;
    $mail->AltBody = ERROR_NO_TEXT_IN_MAILDATA;

    if ( preg_match( "/^[_\.a-z0-9-]{1,20}@(([a-z0-9-]+\.)+(com|net|org|mil|edu|gov|arpa|info|biz|inc|name|[a-z]{2})|[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})$/is",
        $_Email ) ) {
        $mail->ClearAddresses();
        $mail->AddAddress( $_Email, '' );
        return $mail->Send();
    } else {
        return false;
    }
}

function _deleteHTML_Elements( $str ) {
    $search = array( "'&(deg|#176);'i", "'&(nbsp|#160);'i", "'&(ndash|#8211);'i", "'&(mdash|#8212);'i", "'&(bull|#149);'i", "'&(quot|#34|#034);'i", "'&(amp|#38|#038);'i", "'&(lt|#60|#060);'i", "'&(gt|#62|#062);'i", "'&(apos|#39|#039);'i", "'&(minus|#45|#045);'i", "'&(circ|#94|#094);'i", "'&(sup2|#178);'i", "'&(tilde|#126);'i", "'&(Scaron|#138);'i", "'&(lsaquo|#139);'i", "'&(OElig|#140);'i", "'&(lsquo|#145);'i", "'&(rsquo|#146);'i", "'&(ldquo|#147);'i", "'&(rdquo|#148);'i", "'&(ndash|#150);'i", "'&(mdash|#151);'i", "'&(tilde|#152);'i", "'&(trade|#153);'i", "'&(scaron|#154);'i", "'&(rsaquo|#155);'i", "'&(oelig|#156);'i", "'&(Yuml|#159);'i", "'&(yuml|#255);'i", "'&(OElig|#338);'i", "'&(oelig|#339);'i", "'&(Scaron|#352);'i", "'&(scaron|#353);'i", "'&(Yuml|#376);'i", "'&(fnof|#402);'i", "'&(circ|#710);'i", "'&(tilde|#732);'i", "'&(Alpha|#913);'i", "'&(Beta|#914);'i", "'&(Gamma|#915);'i", "'&(Delta|#916);'i", "'&(Epsilon|#917);'i", "'&(Zeta|#918);'i", "'&(Eta|#919);'i", "'&(Theta|#920);'i", "'&(Iota|#921);'i", "'&(Kappa|#922);'i", "'&(Lambda|#923);'i", "'&(Mu|#924);'i", "'&(Nu|#925);'i", "'&(Xi|#926);'i", "'&(Omicron|#927);'i", "'&(Pi|#928);'i", "'&(Rho|#929);'i", "'&(Sigma|#931);'i", "'&(Tau|#932);'i", "'&(Upsilon|#933);'i", "'&(Phi|#934);'i", "'&(Chi|#935);'i", "'&(Psi|#936);'i", "'&(Omega|#937);'i", "'&(alpha|#945);'i", "'&(beta|#946);'i", "'&(gamma|#947);'i", "'&(delta|#948);'i", "'&(epsilon|#949);'i", "'&(zeta|#950);'i", "'&(eta|#951);'i", "'&(theta|#952);'i", "'&(iota|#953);'i", "'&(kappa|#954);'i", "'&(lambda|#955);'i", "'&(mu|#956);'i", "'&(nu|#957);'i", "'&(xi|#958);'i", "'&(omicron|#959);'i", "'&(pi|#960);'i", "'&(rho|#961);'i", "'&(sigmaf|#962);'i", "'&(sigma|#963);'i", "'&(tau|#964);'i", "'&(upsilon|#965);'i", "'&(phi|#966);'i", "'&(chi|#967);'i", "'&(psi|#968);'i", "'&(omega|#969);'i", "'&(thetasym|#977);'i", "'&(upsih|#978);'i", "'&(piv|#982);'i", "'&(ensp|#8194);'i", "'&(emsp|#8195);'i", "'&(thinsp|#8201);'i", "'&(zwnj|#8204);'i", "'&(zwj|#8205);'i", "'&(lrm|#8206);'i", "'&(rlm|#8207);'i", "'&(lsquo|#8216);'i", "'&(rsquo|#8217);'i", "'&(sbquo|#8218);'i", "'&(ldquo|#8220);'i", "'&(rdquo|#8221);'i", "'&(bdquo|#8222);'i", "'&(dagger|#8224);'i", "'&(Dagger|#8225);'i", "'&(bull|#8226);'i", "'&(hellip|#8230);'i", "'&(permil|#8240);'i", "'&(prime|#8242);'i", "'&(Prime|#8243);'i", "'&(lsaquo|#8249);'i", "'&(rsaquo|#8250);'i", "'&(oline|#8254);'i", "'&(frasl|#8260);'i", "'&(euro|#8364);'i", "'&(image|#8465);'i", "'&(weierp|#8472);'i", "'&(real|#8476);'i", "'&(trade|#8482);'i", "'&(alefsym|#8501);'i", "'&(larr|#8592);'i", "'&(uarr|#8593);'i", "'&(rarr|#8594);'i", "'&(darr|#8595);'i", "'&(harr|#8596);'i", "'&(crarr|#8629);'i", "'&(lArr|#8656);'i", "'&(uArr|#8657);'i", "'&(rArr|#8658);'i", "'&(dArr|#8659);'i", "'&(hArr|#8660);'i", "'&(forall|#8704);'i", "'&(part|#8706);'i", "'&(exist|#8707);'i", "'&(empty|#8709);'i", "'&(nabla|#8711);'i", "'&(isin|#8712);'i", "'&(notin|#8713);'i", "'&(ni|#8715);'i", "'&(prod|#8719);'i", "'&(sum|#8721);'i", "'&(minus|#8722);'i", "'&(lowast|#8727);'i", "'&(radic|#8730);'i", "'&(prop|#8733);'i", "'&(infin|#8734);'i", "'&(ang|#8736);'i", "'&(and|#8743);'i", "'&(or|#8744);'i", "'&(cap|#8745);'i", "'&(cup|#8746);'i", "'&(int|#8747);'i", "'&(there4|#8756);'i", "'&(sim|#8764);'i", "'&(cong|#8773);'i", "'&(asymp|#8776);'i", "'&(ne|#8800);'i", "'&(equiv|#8801);'i", "'&(le|#8804);'i", "'&(ge|#8805);'i", "'&(sub|#8834);'i", "'&(sup|#8835);'i", "'&(nsub|#8836);'i", "'&(sube|#8838);'i", "'&(supe|#8839);'i", "'&(oplus|#8853);'i", "'&(otimes|#8855);'i", "'&(perp|#8869);'i", "'&(sdot|#8901);'i", "'&(lceil|#8968);'i", "'&(rceil|#8969);'i", "'&(lfloor|#8970);'i", "'&(rfloor|#8971);'i", "'&(lang|#9001);'i", "'&(rang|#9002);'i", "'&(loz|#9674);'i", "'&(spades|#9824);'i", "'&(clubs|#9827);'i", "'&(hearts|#9829);'i", "'&(diams|#9830);'i", "'&(copy|#169);'i", "'&(reg|#174);'i", "'&(pound|#163);'i", "'&(laquo|#171);'i", "'&(raquo|#187);'i", "'&(sect|#167);'i", "!\s+!" );

    $replace = array( "d", " ", "_", "-", "-", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", " " );

    return trim( strtr( preg_replace( $search, $replace, $str ), array( "\"" => "", "'" => "", "<" => "", ">" => "", "&" => "", " ," => "," ) ) );
}

/**
 * replace newline symbols to &lt;br /&gt;
 * @param mixed data for action
 * @param array which elements test
 * @return mixed
 */
function xNl2Br( $_Data, $_Key = array() ) {

    if ( !is_array( $_Data ) ) {
        return nl2br( $_Data );
    }

    if ( !is_array( $_Key ) ) {
        $_Key = array( $_Key );
    }

    foreach ( $_Data as $__Key => $__Data ) {
        if ( count( $_Key ) && !is_array( $__Data ) ) {
            if ( in_array( $__Key, $_Key ) ) {
                $_Data[$__Key] = xNl2Br( $__Data, $_Key );
            }
        } else {
            $_Data[$__Key] = xNl2Br( $__Data, $_Key );
        }
    }
    return $_Data;
}

function xStrReplace( $_Search, $_Replace, $_Data, $_Key = array() ) {

    if ( !is_array( $_Data ) ) {
        return str_replace( $_Search, $_Replace, $_Data );
    }

    if ( !is_array( $_Key ) ) {
        $_Key = array( $_Key );
    }

    foreach ( $_Data as $__Key => $__Data ) {
        if ( count( $_Key ) && !is_array( $__Data ) ) {
            if ( in_array( $__Key, $_Key ) ) {
                $_Data[$__Key] = xStrReplace( $_Search, $_Replace, $__Data, $_Key );
            }
        } else {
            $_Data[$__Key] = xStrReplace( $_Search, $_Replace, $__Data, $_Key );
        }
    }
    return $_Data;
}

function xHtmlSpecialCharsDecode( $_Data, $_Params = array(), $_Key = array() ) {
// if (is_null($_Data)){
    //         consolelog($_Data);
    //         return "null";
    // }

    if ( !is_array( $_Data ) ) {
        if ( is_string( $_Data ) ) {
            return html_entity_decode( $_Data, ENT_QUOTES );
        } else {
            return "";
        }
    }

    if ( !is_array( $_Key ) ) {
        $_Key = array( $_Key );
    }

    foreach ( $_Data as $__Key => $__Data ) {
        if ( count( $_Key ) && !is_array( $__Data ) ) {
            if ( in_array( $__Key, $_Key ) ) {
                $_Data[$__Key] = xHtmlSpecialCharsDecode( $__Data, $_Params, $_Key );
            }
        } else {
            $_Data[$__Key] = xHtmlSpecialCharsDecode( $__Data, $_Params, $_Key );
        }
    }
    return $_Data;
}

/*function xHtmlSpecialChars( $_Data, $_Params = array(), $_Key = array() ) {

if ( !is_array( $_Data ) ) {
return htmlspecialchars( $_Data, ENT_QUOTES );
}

if ( !is_array( $_Key ) ) {
$_Key = array( $_Key );
}

foreach ( $_Data as $__Key => $__Data ) {
if ( count( $_Key ) && !is_array( $__Data ) ) {
if ( in_array( $__Key, $_Key ) ) {
$_Data[$__Key] = xHtmlSpecialChars( $__Data, $_Params, $_Key );
}
} else {
$_Data[$__Key] = xHtmlSpecialChars( $__Data, $_Params, $_Key );
}
}
return $_Data;
}*/

/*// просто экранируем кавычки real_escape_string() - вызывает перегрузку подключений, упрощаем до addslashes($_Data)
function xEscSQL( $_Data, $_Params = array(), $_Key = array() ) {

if ( !is_array( $_Data ) ) {
// $_Data = $mysqli->real_escape_string($_Data);
// $_Data = htmlspecialchars(addslashes($_Data));
// $_Data = addslashes($_Data);  // просто экранируем кавычки
$_Data = mysql_real_escape_string( $_Data );
return  $_Data;
}

if ( !is_array( $_Key ) ) {
$_Key = array( $_Key );
}

foreach ( $_Data as $__Key => $__Data ) {
if ( count( $_Key ) && !is_array( $__Data ) ) {
if ( in_array( $__Key, $_Key ) ) {
$_Data[$__Key] = xEscSQL( $__Data, $_Params, $_Key );
}
} else {
$_Data[$__Key] = xEscSQL( $__Data, $_Params, $_Key );
}
}
return $_Data;
}*/

function xEscSQL__withDB( $_Data, $_Params = array(), $_Key = array() ) {

    if ( !is_array( $_Data ) ) {
        $mysqli = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_NAME );
        /* проверка соединения */
        if ( mysqli_connect_errno() ) {
            $mysqli->close();
            exit();
        }
        $_Data = $mysqli->real_escape_string( $_Data );
        $mysqli->close();

        return ( $_Data );
    }

    if ( !is_array( $_Key ) ) {
        $_Key = array( $_Key );
    }

    foreach ( $_Data as $__Key => $__Data ) {
        if ( count( $_Key ) && !is_array( $__Data ) ) {
            if ( in_array( $__Key, $_Key ) ) {
                $_Data[$__Key] = xEscSQL( $__Data, $_Params, $_Key );
            }
        } else {
            $_Data[$__Key] = xEscSQL( $__Data, $_Params, $_Key );
        }
    }
    return $_Data;
}

function xEscSQLOLD( $_Data, $_Params = array(), $_Key = array() ) {

    if ( !is_array( $_Data ) ) {
###        debug($_Data); //UTF
        return mysql_real_escape_string( $_Data );
        // return htmlspecialchars(addslashes($_Data));
    }

    if ( !is_array( $_Key ) ) {
        $_Key = array( $_Key );
    }

    foreach ( $_Data as $__Key => $__Data ) {
        if ( count( $_Key ) && !is_array( $__Data ) ) {
            if ( in_array( $__Key, $_Key ) ) {
                $_Data[$__Key] = xEscSQL( $__Data, $_Params, $_Key );
            }
        } else {
            $_Data[$__Key] = xEscSQL( $__Data, $_Params, $_Key );
        }
    }
    return $_Data;
}

function xEscapeSQLstring( $_Data, $_Params = array(), $_Key = array() ) {
    return xEscSQL( $_Data, $_Params, $_Key );
}

function xSaveData( $_ID, $_Data, $_TimeControl = 0 ) {

    if ( !session_is_registered( '_xSAVE_DATA' ) ) {
        session_register( '_xSAVE_DATA' );
        $_SESSION['_xSAVE_DATA'] = array();
    }

    if ( intval( $_TimeControl ) ) {
        $_SESSION['_xSAVE_DATA'][$_ID] = array( $_ID . '_DATA' => $_Data, $_ID . '_TIME_CTRL' => array( 'timetag' =>
            time(), 'timelimit' => $_TimeControl ) );
    } else {
        $_SESSION['_xSAVE_DATA'][$_ID] = $_Data;
    }
}

function xPopData( $_ID ) {

    if ( !isset( $_SESSION['_xSAVE_DATA'][$_ID] ) ) {
        return null;
    }

    if ( is_array( $_SESSION['_xSAVE_DATA'][$_ID] ) ) {
        if ( isset( $_SESSION['_xSAVE_DATA'][$_ID][$_ID . '_TIME_CTRL'] ) ) {
            if (  ( $_SESSION['_xSAVE_DATA'][$_ID][$_ID . '_TIME_CTRL']['timetag'] + $_SESSION['_xSAVE_DATA'][$_ID][$_ID .
                '_TIME_CTRL']['timelimit'] ) < time() ) {
                return null;
            } else {

                $Return = $_SESSION['_xSAVE_DATA'][$_ID][$_ID . '_DATA'];
                unset( $_SESSION['_xSAVE_DATA'][$_ID] );
                return $Return;
            }
        }
    }

    $Return = $_SESSION['_xSAVE_DATA'][$_ID];
    unset( $_SESSION['_xSAVE_DATA'][$_ID] );
    return $Return;
}

function xDataExists( $_ID ) {

    if ( !isset( $_SESSION['_xSAVE_DATA'][$_ID] ) ) {
        return 0;
    }

    if ( is_array( $_SESSION['_xSAVE_DATA'][$_ID] ) ) {
        if ( isset( $_SESSION['_xSAVE_DATA'][$_ID][$_ID . '_TIME_CTRL'] ) ) {
            if (  ( $_SESSION['_xSAVE_DATA'][$_ID][$_ID . '_TIME_CTRL']['timetag'] + $_SESSION['_xSAVE_DATA'][$_ID][$_ID .
                '_TIME_CTRL']['timelimit'] ) >= time() ) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return 1;
        }
    } else {
        return 1;
    }
}

function xGetData( $_ID ) {

    if ( !isset( $_SESSION['_xSAVE_DATA'][$_ID] ) ) {
        return null;
    }

    if ( is_array( $_SESSION['_xSAVE_DATA'][$_ID] ) ) {
        if ( isset( $_SESSION['_xSAVE_DATA'][$_ID][$_ID . '_TIME_CTRL'] ) ) {
            if (  ( $_SESSION['_xSAVE_DATA'][$_ID][$_ID . '_TIME_CTRL']['timetag'] + $_SESSION['_xSAVE_DATA'][$_ID][$_ID .
                '_TIME_CTRL']['timelimit'] ) < time() ) {
                return null;
            } else {

                $Return = $_SESSION['_xSAVE_DATA'][$_ID][$_ID . '_DATA'];
                return $Return;
            }
        }
    }

    $Return = $_SESSION['_xSAVE_DATA'][$_ID];
    return $Return;
}

function generateRndCode( $_RndLength, $_RndCodes = 'qwertyuiopasdfghjklzxcvbnm0123456789' ) {

    $l_name = '';
    $top    = strlen( $_RndCodes ) - 1;
    srand( (double)microtime() * 1000000 );
    for ( $j = 0; $j < $_RndLength; $j++ ) {
        $l_name .= $_RndCodes[rand( 0, $top )];
    }

    return $l_name;
}

function generateCSStimer( $link_id = "adddoc", $link_seconds = 10 ) {
    $css_timer = "";
    $css_timer .= <<<HTML
<div class="CSStimer_container hidden-xs hidden-sm" id="container_{$link_id}" title="Кликните мышкой чтобы остановить переход">
<ol class="clock" id="clock_{$timer_id}">
  <li class="hour">
    <div class="ten">
      <span>0</span>
      <span>1</span>
      <span>2</span>
      <span>3</span>
      <span>4</span>
      <span>5</span>
      <span>6</span>
      <span>7</span>
      <span>8</span>
      <span>9</span>
      <span>0</span>
    </div>
    <div class="one">
      <span>0</span>
      <span>1</span>
      <span>2</span>
      <span>3</span>
      <span>4</span>
      <span>5</span>
      <span>6</span>
      <span>7</span>
      <span>8</span>
      <span>9</span>
      <span>0</span>
    </div>
  </li>
  <li class="min">
    <div class="ten">
      <span>0</span>
      <span>1</span>
      <span>2</span>
      <span>3</span>
      <span>4</span>
      <span>5</span>
      <span>0</span>
    </div>
    <div class="one">
      <span>0</span>
      <span>1</span>
      <span>2</span>
      <span>3</span>
      <span>4</span>
      <span>5</span>
      <span>6</span>
      <span>7</span>
      <span>8</span>
      <span>9</span>
      <span>0</span>
    </div>
  </li>
  <li class="sec">
    <div class="ten">
      <span>0</span>
      <span>1</span>
      <span>2</span>
      <span>3</span>
      <span>4</span>
      <span>5</span>
      <span>0</span>
    </div>
    <div class="one">
      <span>0</span>
      <span>1</span>
      <span>2</span>
      <span>3</span>
      <span>4</span>
      <span>5</span>
      <span>6</span>
      <span>7</span>
      <span>8</span>
      <span>9</span>
      <span>0</span>
    </div>
  </li>
</ol>
</div>
HTML;

    return $css_timer;
}

function zeroFill( $num, $cnt = 4 ) {
    # code...
    return str_pad( $num, $cnt, '0', STR_PAD_LEFT );
}

?>