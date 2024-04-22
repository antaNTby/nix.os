<?php
################################################################################################
################################################################################################

function gmts() {
    list( $usec, $sec ) = explode( " ", microtime( true ) );
    return $usec + $sec;
}

$sc_1 = gmts();
$sc_4 = 0;
$sc_8 = 0;
$gmc  = 1;

include_once "core/languages/nano_const.php";

### конец КОНСТАНТЫ И ФУНКЦИИ

################################################################################################################################
################################################################################################################################
################################################################################################################################
################################################################################################################################
### ИНИЦИАЛИЗАЦИЯ
### include_once "core/config/init.php";
### init.php
@ini_set( "session.use_trans_sid", 0 );
@ini_set( "session.use_cookies", 1 );
@ini_set( "session.use_only_cookies", 1 );
@ini_set( "session.auto_start", 0 );
@ini_set( "magic_quotes_gpc", 0 );
@ini_set( "magic_quotes_runtime", 0 );
@ini_set( "register_globals", 0 );
### // @ini_set("display_errors", 0);
### // error_reporting(0);
### init.php
include_once "core/includes/database/mysqli.php"; // forum

# кэшируем подключаемые классы и функции
$far_1 = array(

    "core/config/connect.inc.php",
    "core/config/language_list.php",
    "core/classes/class.ajax.php",
    "core/classes/class.SSP.php",
    // "core/classes/class.kcaptcha.php",
     "core/classes/class.virtual.paymentmodule.php",
    "core/classes/class.virtual.shippingratecalculator.php",
    // "core/classes/class.xml2array.php",

);

$far_2 = glob( "core/functions/*.php" );
$far   = array_merge( $far_1, $far_2 );

$cfar            = count( $far );
$filename_fcache = "core/cache/fcache.php";
if ( file_exists( $filename_fcache ) ) {
    include_once $filename_fcache;
} else {
    for ( $n = 0; $n < $cfar; $n++ ) {
        include_once $far[$n];
    }
}
# кэшируем подключаемые классы и функции

define( 'PATH_DELIMITER', isWindows() ? ';' : ':' );

$_POST   = stripslashes_deep( $_POST );
$_GET    = stripslashes_deep( $_GET );
$_COOKIE = stripslashes_deep( $_COOKIE );

db_connect( DB_HOST, DB_USER, DB_PASS, DB_NAME ) or die( ERROR_DB_INIT );
db_select_db( DB_NAME ) or die( db_error() );
$pdo_connect = array(
    "user" => DB_USER,
    "pass" => DB_PASS,
    "db"   => DB_NAME,
    "host" => DB_HOST,
);

settingDefineConstants();

include_once "core/config/headers.php";
include_once "core/config/error_handler.php";
### конец ИНИЦИАЛИЗАЦИЯ

################################################################################################
################################################################################################
### СТАРТУЕМ СЕССИЮ
# стартуем сессию
define( "SECURITY_EXPIRE", 60 * 60 * CONF_SECURITY_EXPIRE );
session_set_save_handler( "sess_open", "sess_close", "sess_read", "sess_write", "sess_destroy", "sess_gc" );

# посылаем cookie сессии
if ( isset( $_COOKIE["PHPSESSID"] ) ) {
    if ( SECURITY_EXPIRE > 0 ) {
        set_cookie( "PHPSESSID", $_COOKIE["PHPSESSID"], time() + SECURITY_EXPIRE );
    } else {
        set_cookie( "PHPSESSID", $_COOKIE["PHPSESSID"] );
    }
}
session_set_cookie_params( SECURITY_EXPIRE );

session_start();
### конец СТАРТУЕМ СЕССИЮ

################################################################################################
################################################################################################
### Выбираем язык
//select a new language?
if ( isset( $_POST["lang"] ) ) {
    $_SESSION["current_language"] = $_POST["lang"];
}

//current language session variable
if ( !isset( $_SESSION["current_language"] ) || $_SESSION["current_language"] < 0 || $_SESSION["current_language"] >
    count( $lang_list ) ) {
    $_SESSION["current_language"] = 0;
}
//set default language
//include a language file
if ( isset( $lang_list[$_SESSION["current_language"]] ) && file_exists( "core/languages/" . $lang_list[$_SESSION["current_language"]]->
    filename ) ) {
    //include current language file
    include_once "core/languages/" . $lang_list[$_SESSION["current_language"]]->filename;
} else {
    die( "<font color=red><b>ERROR: Couldn't find language file!</b></font>" );
}
### конец Выбираем язык

################################################################################################
################################################################################################
### ОТРАБОТКА ПРОЦЕССОРОВ
if ( isset( $_GET["do"] ) ) {
    if ( in_array( 'core/includes/processor/' . $_GET["do"] . '.php', glob( "core/includes/processor/*.php" ) ) ) {
        include_once "core/includes/processor/" . $_GET["do"] . ".php";
    } else {
        header( "HTTP/1.0 404 Not Found" );
        header( "HTTP/1.1 404 Not Found" );
        header( "Status: 404 Not Found" );
        die( ERROR_404_HTML );
    }
}
### конец ОТРАБОТКА ПРОЦЕССОРОВ

### ОСНОВНАЯ РАБОТА
################################################################################################################################
################################################################################################################################
################################################################################################################################
################################################################################################################################
if ( !isset( $_GET["do"] ) ) {

#######
    ###
    ### //init Smarty
    require_once "core/smarty/Smarty.class.php"; // require "core/smarty/smarty.class.php";
    $smarty      = new Smarty();                 //core smarty object
    $smarty_mail = new Smarty();                 //for e-mails

    ### if ((int)CONF_SMARTY_FORCE_COMPILE){ TRUE } //this forces Smarty to recompile design each time someone runs index.php
    $smarty->force_compile      = true;
    $smarty_mail->force_compile = true;

    $relaccess = checklogin();

#######
    ###
    ### //# of selected currency
    $current_currency = isset( $_SESSION["current_currency"] ) ? $_SESSION["current_currency"] : CONF_DEFAULT_CURRENCY;
    $smarty->assign( "current_currency", $current_currency );
    $q = db_query( "SELECT code, currency_value, where2show, currency_iso_3, Name, roundval FROM " . CURRENCY_TYPES_TABLE . " WHERE CID=" . (int)$current_currency );
    if ( $row = db_fetch_row( $q ) ) {
        $smarty->assign( "currency_name", $row[0] );
        $selected_currency_details = $row; //for show_price() function
    } else {
        //no currency found. In this case check is there any currency type in the database
        $q = db_query( "SELECT code, currency_value, where2show, roundval FROM " . CURRENCY_TYPES_TABLE );
        if ( $row = db_fetch_row( $q ) ) {
            $smarty->assign( "currency_name", $row[0] );
            $selected_currency_details = $row; //for show_price() function
        }
    }
    $smarty->assign( "currency_roundval", $selected_currency_details["roundval"] );

#######
    ###
    //set $categoryID
    if ( isset( $_GET["categoryID"] ) || isset( $_POST["categoryID"] ) ) {
        $categoryID = isset( $_GET["categoryID"] ) ? $_GET["categoryID"] : $_POST["categoryID"];
        $categoryID = (int)$categoryID;
    }
    // else $categoryID = 1;

#######
    ###
    // set $productID
    if ( isset( $_GET["productID"] ) || isset( $_POST["productID"] ) ) {
        $productID = isset( $_GET["productID"] ) ? $_GET["productID"] : $_POST["productID"];
        $productID = (int)$productID;
    }

#######
    ###
    //and DIFFERENT VARS...
    {

        if ( isset( $_GET["register"] ) || isset( $_POST["register"] ) ) {
            $register = isset( $_GET["register"] ) ? $_GET["register"] : $_POST["register"];
        }
        if ( isset( $_GET["update_details"] ) || isset( $_POST["update_details"] ) ) {
            $update_details = isset( $_GET["update_details"] ) ? $_GET["update_details"] : $_POST["update_details"];
        }
        if ( isset( $_GET["order"] ) || isset( $_POST["order"] ) ) {
            $order = isset( $_GET["order"] ) ? $_GET["order"] : $_POST["order"];
        }
        if ( isset( $_GET["order_without_billing_address"] ) || isset( $_POST["order_without_billing_address"] ) ) {
            $order_without_billing_address = isset( $_GET["order_without_billing_address"] ) ? $_GET["order_without_billing_address"] : $_POST["order_without_billing_address"];
        }
        if ( isset( $_GET["check_order"] ) || isset( $_POST["check_order"] ) ) {
            $check_order = isset( $_GET["check_order"] ) ? $_GET["check_order"] : $_POST["check_order"];
        }
        if ( isset( $_GET["proceed_ordering"] ) || isset( $_POST["proceed_ordering"] ) ) {
            $proceed_ordering = isset( $_GET["proceed_ordering"] ) ? $_GET["proceed_ordering"] : $_POST["proceed_ordering"];
        }
        if ( isset( $_GET["update_customer_info"] ) || isset( $_POST["update_customer_info"] ) ) {
            $update_customer_info = isset( $_GET["update_customer_info"] ) ? $_GET["update_customer_info"] : $_POST["update_customer_info"];
        }
        if ( isset( $_GET["show_aux_page"] ) || isset( $_POST["show_aux_page"] ) ) {
            $show_aux_page = isset( $_GET["show_aux_page"] ) ? $_GET["show_aux_page"] : $_POST["show_aux_page"];
        }
        if ( isset( $_GET["visit_history"] ) || isset( $_POST["visit_history"] ) ) {
            $visit_history = 1;
        }
        if ( isset( $_GET["order_history"] ) || isset( $_POST["order_history"] ) ) {
            $order_history = 1;
        }
        if ( isset( $_GET["address_book"] ) || isset( $_POST["address_book"] ) ) {
            $address_book = 1;
        }
        if ( isset( $_GET["address_editor"] ) || isset( $_POST["address_editor"] ) ) {
            $address_editor = isset( $_GET["address_editor"] ) ? $_GET["address_editor"] : $_POST["address_editor"];
        }
        if ( isset( $_GET["add_new_address"] ) || isset( $_POST["add_new_address"] ) ) {
            $add_new_address = isset( $_GET["add_new_address"] ) ? $_GET["add_new_address"] : $_POST["add_new_address"];
        }
        if ( isset( $_GET["contact_info"] ) || isset( $_POST["contact_info"] ) ) {
            $contact_info = isset( $_GET["contact_info"] ) ? $_GET["contact_info"] : $_POST["contact_info"];
        }
        if ( isset( $_GET["comparison_products"] ) || isset( $_POST["comparison_products"] ) ) {
            $comparison_products = 1;
        }
        if ( isset( $_GET["register_authorization"] ) || isset( $_POST["register_authorization"] ) ) {
            $register_authorization = 1;
        }
        if ( isset( $_GET["page_not_found"] ) || isset( $_POST["page_not_found"] ) ) {
            $page_not_found = 1;
        }
        if ( isset( $_GET["news"] ) || isset( $_POST["news"] ) ) {
            $news = 1;
        }
        if ( isset( $_GET["quick_register"] ) ) {
            $quick_register = 1;
        }
        if ( isset( $_GET["order2_shipping_quick"] ) ) {
            $order2_shipping_quick = 1;
        }
        if ( isset( $_GET["order3_billing_quick"] ) ) {
            $order3_billing_quick = 1;
        }
        if ( isset( $_GET["order2_shipping"] ) || isset( $_POST["order2_shipping"] ) ) {
            $order2_shipping = 1;
        }
        if ( isset( $_GET["order3_billing"] ) || isset( $_POST["order3_billing"] ) ) {
            $order3_billing = 1;
        }
        if ( isset( $_GET["change_address"] ) || isset( $_POST["change_address"] ) ) {
            $change_address = 1;
        }
        if ( isset( $_GET["order4_confirmation"] ) || isset( $_POST["order4_confirmation"] ) ) {
            $order4_confirmation = 1;
        }
        if ( isset( $_GET["order4_confirmation_quick"] ) || isset( $_POST["order4_confirmation_quick"] ) ) {
            $order4_confirmation_quick = 1;
        }
        if ( isset( $_GET["order_detailed"] ) || isset( $_POST["order_detailed"] ) ) {
            $order_detailed = isset( $_GET["order_detailed"] ) ? $_GET["order_detailed"] : $_POST["order_detailed"];
        }
        if ( isset( $_GET["p_order_detailed"] ) || isset( $_POST["p_order_detailed"] ) ) {
            $p_order_detailed = isset( $_GET["p_order_detailed"] ) ? $_GET["p_order_detailed"] : $_POST["p_order_detailed"];
        }
        //end and DIFFERENT VARS...
    }

#######
    ###
    // VOTE
    if ( !isset( $_SESSION["vote_completed"] ) ) {
        $_SESSION["vote_completed"] = array();
    }

#######
    ###
    if ( isset( $_COOKIE["COOKIE_POLICY"] ) ) {
        if ( $_COOKIE["COOKIE_POLICY"] == 1 ) {
            $smarty->assign( "cookie_policy_show", 0 );
            $_COOKIE["COOKIE_POLICY"] = set_cookie( "COOKIE_POLICY", 1, time() + ( 100 * 24 * 60 * 60 * 1000 * 6 ) );
        } else {
            $smarty->assign( "cookie_policy_show", 1 );
        }
    } else {
        $smarty->assign( "cookie_policy_show", 1 );
    }

#######
    ###
    // OFFSET
    //checking for proper $offset init
    $offset = isset( $_GET["offset"] ) ? $_GET["offset"] : 0;
    if ( $offset < 0 || $offset % CONF_PRODUCTS_PER_PAGE ) {
        $offset = 0;
    }

#######
    ###
    // Вывод товаров
    if ( isset( $_COOKIE["view_type"] ) ) {
        // если есть сохраненное значение вьютайпа
        $view_type = (int)$_COOKIE["view_type"];
        $smarty->assign( "view_type", $view_type );
    } else {
        $view_type = CONF_INITIAL_VIEWTYPE;
    }
    // if ( isset( $_SESSION["view_type"] ) ) {
    //     // если есть сохраненное значение вьютайпа
    //     $view_type = (int)$_SESSION["view_type"];
    //     $smarty->assign( "view_type", $view_type );
    // } else {
    //     $view_type = CONF_INITIAL_VIEWTYPE;
    // }

    $smarty->assign( "view_type", $view_type ); // по-умолчанию если первый раз на странице

    if ( isset( $_GET["change_view_type"] ) && isset( $_POST["view_type"] ) ) {
        // запрос на  смену вьютайпа

        if ( isset( $_POST["operation"] ) && ( $_POST["operation"] == "change_view_type" ) ) {
            $view_type            = (int)$_POST["view_type"];
            $_COOKIE["view_type"] = $view_type;
            $_COOKIE["view_type"] = set_cookie( "view_type", $view_type, time() + SECURITY_EXPIRE * 14 );

        } else {
            $view_type            = CONF_INITIAL_VIEWTYPE;
            $_COOKIE["view_type"] = CONF_INITIAL_VIEWTYPE;
            $_COOKIE["view_type"] = set_cookie( "view_type", $view_type, time() + SECURITY_EXPIRE * 14 );
        }
        $smarty->assign( "view_type", (int)$_COOKIE["view_type"] );

        $new_offset = floor( $_POST['myOffset'] / CONF_PRODUCTS_PER_PAGE ) * CONF_PRODUCTS_PER_PAGE;

        $offset_clause = "";

        if ( isset( $_POST["is_SearchPage"] ) && $_POST["is_SearchPage"] == "true" ) {
            $urlToNewView  = "index.php?simple_search=yes&searchstring=" . rawurlencode( validate_search_string( $_POST["searchstring"] ) );
            $offset_clause = "&offset=";
            if ( $_POST['myOffset'] == "show_all" ) {
                $offset_clause = "&show_all=yes";
            }
        } else {
            $urlToNewView = "index.php?categoryID=" . (int)$_POST["tpl_categoryID"];
            if ( CONF_MOD_REWRITE && $urlToNewView == "index.php?categoryID=" . (int)$_POST["tpl_categoryID"] ) {
                $urlToNewView = "category_" . (int)$_POST["tpl_categoryID"];
            }
            $offset_clause .= "offset_";
            if ( $_POST['myOffset'] == "show_all" ) {
                $offset_clause = "show_all";
            }
        }

        if ( $_POST['myOffset'] != "show_all" ) {
            $offset_clause .= $new_offset;
        }

        if ( isset( $_POST["is_SearchPage"] ) && $_POST["is_SearchPage"] == "true" ) {
            $urlToNewView = $urlToNewView . $offset_clause;
            $urlToNewView = strtr( $urlToNewView, array( "_&offset=0" => "", ".html" => "" ) );
            // $urlToNewView = rawurlencode( $urlToNewView );
        } else {
            $urlToNewView = html_spchars( $urlToNewView . "_" ) . $offset_clause;
            $urlToNewView = strtr( $urlToNewView, array( "_offset_0" => "" ) );
            $urlToNewView = $urlToNewView . ".html";
            // $urlToNewView = rawurlencode( $urlToNewView );
        }

        $res = $urlToNewView;

        header( "Content-Type: application/json; charset=utf-8" );
        die( json_encode( $res ) );
    }

#######
    ###
    //to rollout categories navigation table
    if ( isset( $productID ) ) {
        $q = db_query( "SELECT categoryID FROM " . PRODUCTS_TABLE . " WHERE productID=" . (int)$productID );
        $r = db_fetch_row( $q );
        if ( $r ) {
            $categoryID = $r[0];
        }
    }

#######
    ###
    // CUSTOM_DESIGN
    # костыль подменяем темплейт для разработки #
    $_SESSION['CUSTOM_DESIGN'] = "nano";

    if ( isset( $_POST["change_design"] ) ) {
        $_SESSION['CUSTOM_DESIGN'] = $_POST["change_design"];
    }

    if ( isset( $_SESSION["CUSTOM_DESIGN"] ) ) {
        $smarty->template_dir = "core/tpl/user/" . $_SESSION["CUSTOM_DESIGN"];
        define( 'TPL', $_SESSION["CUSTOM_DESIGN"] );
    } else {
        $smarty->template_dir = "core/tpl/user/" . CONF_DEFAULT_TEMPLATE;
        define( 'TPL', CONF_DEFAULT_TEMPLATE );
    }

    $smarty_mail->template_dir = "core/tpl/email";
    $smarty->compile_id        = TPL;    // у кэш-файлов разных шаблонов будут разные имена (префиксы).
    $smarty_mail->compile_id   = "MAIL"; // у кэш-файлов разных шаблонов будут разные имена (префиксы).

#######
    ###
    //fetch CURRENCY TYPES from database
    $q          = db_query( "SELECT CID, Name, code, currency_value, where2show, roundval, currency_iso_3 FROM " . CURRENCY_TYPES_TABLE . " ORDER BY sort_order" );
    $currencies = array();
    while ( $row = db_fetch_row( $q ) ) {
        $currencies[] = $row;
    }

    $smarty->assign( "currencies", $currencies );
    $smarty->assign( "currencies_count", count( $currencies ) );
    //ant
    $smarty->assign( "currency_roundval", $selected_currency_details["roundval"] );
    $smarty->assign( "currency_details", $selected_currency_details );

#######
    ###
    //LANGUAGE
    $smarty->assign( "lang_list", $lang_list );
    if ( isset( $_SESSION["current_language"] ) ) {
        $smarty->assign( "current_language", $_SESSION["current_language"] );
    }

#######
    ###
    //LOG
    if ( isset( $_SESSION["log"] ) ) {
        $smarty->assign( "log", $_SESSION["log"] );
    }

#######
    ###
    // HIDDEN IN THE CUSTOMER SURVEY FORM
    // - following vars are used as hidden in the customer survey form
    if ( isset( $categoryID ) ) {
        $smarty->assign( "categoryID", $categoryID );
    }
    if ( isset( $productID ) ) {
        $smarty->assign( "productID", $productID );
    }
    if ( isset( $_GET["currency"] ) ) {
        $smarty->assign( "currency", $_GET["currency"] );
    }
    if ( isset( $_GET["user_details"] ) ) {
        $smarty->assign( "user_details", $_GET["user_details"] );
    }
    if ( isset( $_GET["aux_page"] ) ) {
        $smarty->assign( "aux_page", $_GET["aux_page"] );
    }
    if ( isset( $_GET["show_price"] ) ) {
        $smarty->assign( "show_price", $_GET["show_price"] );
    }
    if ( isset( $_GET["searchstring"] ) ) {
        $smarty->hassign( "searchstring", $_GET["searchstring"] );
    }
    if ( isset( $register ) ) {
        $smarty->assign( "register", $register );
    }
    if ( isset( $order ) ) {
        $smarty->assign( "order", $order );
    }
    if ( isset( $check_order ) ) {
        $smarty->assign( "check_order", $check_order );
    }

#######
    ###
    //SET DEFUALT main_content template to homepage
    $smarty->assign( "main_content_template", "home.tpl.html" );
    // $smarty->assign( "main_content_template", "shopping_cart.tpl.html" );

#######
    ###
    ## CATALOG CATEGORIES CACHE
    $categories = array();
    # BEGIN кэшируем в файл клиентский список категорий
    $filename_categoriesindexcache = "core/temp/categories_index_cache.txt";
    if ( file_exists( $filename_categoriesindexcache ) && ( $cache = file_get_contents( $filename_categoriesindexcache ) ) && $categories = unserialize( $cache ) ) {
        foreach ( $categories as $row ) {
            $fc[(int)$row["categoryID"]] = $row;
            $mc[(int)$row["categoryID"]] = (int)$row["parent"];
        }
    } else {
        $categories = array();
        # END кэшируем в файл клиентский список категорий

        $q  = db_query( "SELECT categoryID, name, products_count, products_count_admin, parent, picture, subcount FROM " . CATEGORIES_TABLE . " ORDER BY sort_order, name" );
        $fc = array(); //parents
        $mc = array(); //parents
        while ( $row = db_fetch_row( $q ) ) {
            $fc[(int)$row["categoryID"]] = $row;
            $mc[(int)$row["categoryID"]] = (int)$row["parent"];
            # BEGIN кэшируем в файл клиентский список категорий
            $categories[] = $row;
        }
        file_put_contents( $filename_categoriesindexcache, serialize( $categories ) );
        // file_put_contents( "core/temp/categories_json.json", json_encode( $categories ) );
        # END кэшируем в файл клиентский список категорий
    }

    $cats = catGetCategoryCListMin();

#######
    ###
    ### PHP CACHE
    //include all .php files from core/includes/ dir or from cache
    $filename_incache = "";
    if ( 1 || (int)CONF_SMARTY_FORCE_COMPILE ) {
        if ( file_exists( $filename_incache ) ) {
            unlink( $filename_incache );
        }
        if ( file_exists( $filename_fcache ) ) {
            unlink( $filename_fcache );
        }

        $fls  = glob( "core/includes/*.php" );
        $cfls = count( $fls );
        for ( $zc = 0; $zc < $cfls; $zc++ ) {
            include_once $fls[$zc];
        }
    } else {
        if ( file_exists( $filename_incache ) ) {
            include_once $filename_incache;
        } else {
            ob_start();
            for ( $n = 0; $n < $cfar; $n++ ) {
                readfile( $far[$n] );
            }

            $_res = ob_get_contents();
            ob_end_clean();
            $fh = fopen( $filename_fcache, 'w' );
            fwrite( $fh, $_res );
            fclose( $fh );
            unset( $_res );

            $fls  = glob( "core/includes/*.php" );
            $cfls = count( $fls );
            ob_start();
            for ( $i = 0; $i < $cfls; $i++ ) {
                readfile( $fls[$i] );
            }

            $_res = ob_get_contents();
            ob_end_clean();
            $fh = fopen( $filename_incache, 'w' );
            fwrite( $fh, $_res );
            fclose( $fh );
            unset( $_res );
            include_once $filename_incache;
        }
    }

#######
    ###
    //show admin a administrative mode link
    if ( isset( $_SESSION["log"] ) && in_array( 100, $relaccess ) ) {
        $smarty->assign( "isadmin", "yes" );
        $adminislog = true;
    } else {
        $adminislog = false;
    }

    $exploerrors = "";

#######
    ###
    // errors
    if ( file_exists( "install.php" ) ) {
        $exploerrors .= WARNING_DELETE_INSTALL_PHP;
    }

    if ( !is_writable( "core/cache" ) ) {
        die( WARNING_WRONG_CHMOD );
    }

    $RGLBLS = @ini_get( 'register_globals' );
    if ( strtolower( $RGLBLS ) == "on" || (int)$RGLBLS == 1 ) {
        die( WARNING_REGISTER_GLOBALS );
    }

    $smarty->assign( "exploerrors", $exploerrors );

#######
    ###
    //  $tmpb = array();

#######
    ###
    // getmicrotime
    $sc_2 = getmicrotime();
    $sr_1 = $sc_2 - $sc_1 - $sc_8;

################################################################################################
    ################################################################################################
    ################################################################################################
    ################################################################################################

    //show Smarty output
    $smarty->display( "index.tpl.html" );

################################################################################################
    ################################################################################################
    ################################################################################################
    ################################################################################################

    // if ( $adminislog && CONF_DISPLAY_INFO == 1 ) {
    if ( CONF_DISPLAY_INFO == 1 ) {
        $sr3  = getmicrotime();
        $sr_2 = $sr3 - $sc_2;
        $sr_3 = $sr3 - $sc_1;
        $sr_1 = number_format( round( $sr_1, 3 ), 3, '.', '' );
        $sr_2 = number_format( round( $sr_2, 3 ), 3, '.', '' );
        $sr_3 = number_format( round( $sr_3, 3 ), 3, '.', '' );
        $sc_8 = number_format( round( $sc_8, 3 ), 3, '.', '' );

        $_SESSION["tgenexe"]     = $sr_1;
        $_SESSION["tgencompile"] = $sr_2;
        $_SESSION["tgendb"]      = $sc_8;
        $_SESSION["tgenall"]     = $sr_3;
        $_SESSION["tgensql"]     = $sc_4;
    }

}
################################################################################################################################
################################################################################################################################
################################################################################################################################
################################################################################################################################
### конец ОСНОВНАЯ РАБОТА
