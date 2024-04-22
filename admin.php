<?php

include_once "core/languages/nano_const.php";

/*
# session_cache_expire
#
# (PHP 4 >= 4.2.0, PHP 5, PHP 7, PHP 8)
#
# session_cache_expire — Получает и/или устанавливает срок действия текущего кеша
# Описание ¶
# session_cache_expire(?int $value = null): int|false
# session_cache_expire() возвращает текущее значение настройки session.cache_expire.
#
Срок действия сбрасывается до значения по умолчанию (180), хранящегося в session.cache_expire во время запроса. Таким образом, нужно вызывать session_cache_expire() для каждого запроса (и до вызова # session_start()).
# Список параметров ¶
#
# value
#  Если value задан и не равен null, текущее время жизни заменяется на value.
 */

session_cache_expire();

### init.php
@ini_set("session.use_trans_sid", 0);
@ini_set("session.use_cookies", 1);
@ini_set("session.use_only_cookies", 1);
@ini_set("session.auto_start", 0);
@ini_set("magic_quotes_gpc", 0);
@ini_set("magic_quotes_runtime", 0);
@ini_set("register_globals", 0);

@ini_set("display_errors", 1);
error_reporting(1);

include_once "core/includes/database/mysqli.php"; // forum

# кэшируем подключаемые классы и функции

$far_1 = array(

    "core/config/connect.inc.php",
    "core/config/language_list.php",
    "core/classes/class.SSP.php",
    "core/classes/class.virtual.shippingratecalculator.php",
    "core/classes/class.virtual.paymentmodule.php",
    // "core/config/paths.inc.php",  // ??????? только Админ
    // "core/classes/class.xmlnodex.php",  // только Админ

    // "core/classes/class.ajax.php", //только клиент
    // "core/classes/class.kcaptcha.php", //только клиент
    // "core/classes/class.xml2array.php", //только клиент
    //
);

$far_2 = glob("core/functions/*.php");
$far_3 = glob("core/functions/admin/*.php");
$far   = array_merge($far_1, $far_2, $far_3);
$cfar  = count($far);
if (file_exists("core/cache/afcache.php")) {
    include_once "core/cache/afcache.php";
} else {
    for ($n = 0; $n < $cfar; $n++) {
        include_once $far[$n];
    }
}

# кэшируем подключаемые классы и функции

define("PATH_DELIMITER", isWindows() ? ";" : ":");

$_POST   = stripslashes_deep($_POST);
$_GET    = stripslashes_deep($_GET);
$_COOKIE = stripslashes_deep($_COOKIE);

db_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME) or die(ERROR_DB_INIT);
db_select_db(DB_NAME) or die(db_error());

$pdo_connect = array(
    "user" => DB_USER,
    "pass" => DB_PASS,
    "db"   => DB_NAME,
    "host" => DB_HOST,
);

require_once "core/classes/class.adminSSP.php";
$PDO_connect = array(
    "user"    => DB_USER,
    "pass"    => DB_PASS,
    "db"      => DB_NAME,
    "host"    => DB_HOST,
    "charset" => "utf8mb3",
);

settingDefineConstants();

if (!defined('CONF_LOGO_FILE')) {
    db_query("INSERT " . SETTINGS_TABLE . " SET
                  settings_groupID=2,
                  settings_constant_name='CONF_LOGO_FILE',
                  settings_value='logo/logo64.png',
                  settings_title='файл с Логотипом по-умолчанию',
                  settings_description='файл с Логотипом по-умолчанию',
                  settings_html_function='setting_IMAGE(',
                  sort_order=01");
}

include_once "core/config/headers.php";
include_once "core/config/error_handler.php";
### конец ИНИЦИАЛИЗАЦИЯ

################################################################################################
################################################################################################
### СТАРТУЕМ СЕССИЮ
# стартуем сессию
define("SECURITY_EXPIRE", 60 * 60 * CONF_SECURITY_EXPIRE);
session_set_save_handler("sess_open", "sess_close", "sess_read", "sess_write", "sess_destroy", "sess_gc");

# посылаем cookie сессии
if (isset($_COOKIE["PHPSESSID"])) {
    if (SECURITY_EXPIRE > 0) {
        set_cookie("PHPSESSID", $_COOKIE["PHPSESSID"], time() + SECURITY_EXPIRE);
    } else {
        set_cookie("PHPSESSID", $_COOKIE["PHPSESSID"]);
    }
}
session_set_cookie_params(SECURITY_EXPIRE);

session_start();
### конец СТАРТУЕМ СЕССИЮ

################################################################################################
################################################################################################

### Выбираем язык
//select a new language?
####   нет в ADMIN
####  if ( isset( $_POST["lang"] ) ) {
####      $_SESSION["current_language"] = $_POST["lang"];
####  }

//current language session variable
if (!isset($_SESSION["current_language"]) || $_SESSION["current_language"] < 0 || $_SESSION["current_language"] >
    count($lang_list)) {
    $_SESSION["current_language"] = 0;
}
//set default language
//include a language file
if (isset($lang_list[$_SESSION["current_language"]]) && file_exists("core/languages/" . $lang_list[$_SESSION["current_language"]]->
    filename)) {
    //include current language file
    include_once "core/languages/" . $lang_list[$_SESSION["current_language"]]->filename;
} else {
    die("<font color=red><b>ERROR: Couldn't find language file!</b></font>");
}
### конецВыбираем язык

### BEGIN кэшируем в файл клиентский список категорий
## CATALOG CATEGORIES CACHE
$categories = array();
$filename_categoriesadmincache="core/temp/categories_admin_cache.txt";
if (file_exists($filename_categoriesadmincache) && ($cache = file_get_contents($filename_categoriesadmincache)) && $categories = unserialize($cache)) {
    foreach ($categories as $row) {
        $fc[(int) $row["categoryID"]] = $row;
        $mc[(int) $row["categoryID"]] = (int) $row["parent"];
    }
} else {
    $categories = array();
    # END кэшируем в файл клиентский список категорий

    $q  = db_query("SELECT categoryID, name, products_count, products_count_admin, parent, picture, subcount FROM " . CATEGORIES_TABLE . " ORDER BY sort_order, name");
    $fc = array(); //parents
    $mc = array(); //parents
    while ($row = db_fetch_row($q)) {
        $fc[(int) $row["categoryID"]] = $row;
        $mc[(int) $row["categoryID"]] = (int) $row["parent"];
        # BEGIN кэшируем в файл клиентский список категорий
        $categories[] = $row;
    }
    file_put_contents($filename_categoriesadmincache, serialize($categories));
### file_put_contents( "core/temp/categories_json.json", json_encode( $categories ) );
}
### END кэшируем в файл клиентский список категорий

### ОТРАБОТКА ПРОЦЕССОРОВ
if (isset($_GET["app"])) {

    $myApps = [];
    $pathApplications="core/includes/applications/";
    $myApps = glob("{$pathApplications}app_*.php");
    if (in_array($pathApplications . $_GET["app"] . ".php", $myApps)) {
        $appFilename = $pathApplications . $_GET["app"] . ".php";
        if (file_exists($appFilename)) {
            include_once $pathApplications . $_GET["app"] . ".php"; #2
        }
    } else {
        header("HTTP/1.0 404 Not Found");
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        die(ERROR_404_HTML);
    }
}
if (isset($_GET["do"])) {

    $myProcesses = [];
    $myProcesses = glob("core/includes/processor/*.php");
    $pathProcessor="core/includes/processor/";
    if (in_array($pathProcessor . $_GET["do"] . ".php", $myProcesses)) {
        $appFilename = $pathProcessor . $_GET["do"] . ".php";
        if (file_exists($appFilename)) {
            include_once $pathProcessor . $_GET["do"] . ".php"; #2
        }
    } else {
        header("HTTP/1.0 404 Not Found");
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        die(ERROR_404_HTML);
    }
}
### конец ОТРАБОТКА ПРОЦЕССОРОВ

### ОСНОВНАЯ РАБОТА
################################################################################################################################
################################################################################################################################
################################################################################################################################
################################################################################################################################
if (!isset($_GET["do"]) && !isset($_GET["app"])) {

    $relaccess = checklogin();

    if (CONF_BACKEND_SAFEMODE != 1 && (!isset($_SESSION["log"]) || !in_array(100, $relaccess))) {
        if (isset($_POST["user_login"]) && isset($_POST["user_pw"])) {
            if (regAuthenticate($_POST["user_login"], $_POST["user_pw"])) {
                Redirect(set_query("&__tt="));
            }
            die(ERROR_FORBIDDEN);
        }
        die(ERROR_FORBIDDEN);
    }

    $eaction = isset($_REQUEST["eaction"]) ? $_REQUEST["eaction"] : "";

#######   $eaction == "cat"
    if ($eaction == "cat") {

        die();
    }

####### $eaction == "prod"
    if ($eaction == "prod") {

        die();
    }

####### $eaction == "prod"
    if ($eaction == "upload_images") {

        die();
    }

###########

####### $eaction default

###########

    $cats = catGetCategoryCListMin();

    ### //init Smarty
    require_once "core/smarty/Smarty.class.php"; // require "core/smarty/smarty.class.php";
    $smarty      = new Smarty(); //core smarty object
    $smarty_mail = new Smarty(); //for e-mails

    ### if ((int)CONF_SMARTY_FORCE_COMPILE){ TRUE } //this forces Smarty to recompile design each time someone runs index.php
    $smarty->force_compile      = true;
    $smarty_mail->force_compile = true;

    // set Smarty include files dir
    $smarty->template_dir      = "core/tpl";
    $smarty_mail->template_dir = "core/tpl";

    $smarty->compile_id      = "NA"; // у кэш-файлов разных шаблонов будут разные имена (префиксы).
    $smarty_mail->compile_id = "NAMAIL"; // у кэш-файлов разных шаблонов будут разные имена (префиксы).

###########

    //validate data to avoid SQL injections
    if (isset($_GET["customerID"])) {
        $_GET["customerID"] = (int) $_GET["customerID"];
    }
    if (isset($_GET["settings_groupID"])) {
        $_GET["settings_groupID"] = (int) $_GET["settings_groupID"];
    }
    if (isset($_GET["orderID"])) {
        $_GET["orderID"] = (int) $_GET["orderID"];
    }
    if (isset($_GET["answer"])) {
        $_GET["answer"] = (int) $_GET["answer"];
    }
    if (isset($_GET["productID"])) {
        $_GET["productID"] = (int) $_GET["productID"];
    }
    if (isset($_GET["categoryID"])) {
        $_GET["categoryID"] = (int) $_GET["categoryID"];
    }
    if (isset($_GET["countryID"])) {
        $_GET["countryID"] = (int) $_GET["countryID"];
    }
    if (isset($_GET["delete"])) {
        $_GET["delete"] = (int) $_GET["delete"];
    }
    if (isset($_GET["setting_up"])) {
        $_GET["setting_up"] = (int) $_GET["setting_up"];
    }

###########

    //define department and subdepartment
    if (!isset($_GET["dpt"])) {
        $dpt = isset($_POST["dpt"]) ? $_POST["dpt"] : "";
    } else {
        $dpt = $_GET["dpt"];
    }

    if (!isset($_GET["sub"])) {
        if (isset($_POST["sub"])) {
            $sub = $_POST["sub"];
        }
    } else {
        $sub = $_GET["sub"];
    }

###########

    //show safe mode warning
    if (isset($_GET["safemode"])) {
        $smarty->assign("safemode", ADMIN_SAFEMODE_WARNING);
    }

###########

    //define smarty template
    $smarty->assign("admin_main_content_template", "default.tpl.html");
    $smarty->assign("current_dpt", $dpt);

    if (isset($_SESSION["log"])) {
        $smarty->assign("admintempname", $_SESSION["log"]);
    }

    // $q  = db_query( "select categoryID, name, products_count, products_count_admin, parent, picture, subcount FROM " . CATEGORIES_TABLE . " ORDER BY sort_order, name" );
    // $fc = array(); //parents
    // $mc = array(); //parents
    // while ( $row = db_fetch_row( $q ) ) {
    // $fc[(int)$row["categoryID"]] = $row;
    // $mc[(int)$row["categoryID"]] = (int)$row["parent"];
    // }

    $admin_departments = array();

###########

    // includes all .php files from core/includes/ dir
    $includes_dir = opendir("core/includes/admin");
    $file_count   = 0;
    while ($inc_file = readdir($includes_dir)) {
        if (strstr($inc_file, ".php")) {
            include_once "core/includes/admin/" . $inc_file;
            $file_count++;
        }
    }
    closedir($includes_dir);

    ###########

    // в случае смыны дизайна переписываем кэш????
    if (defined("UPDATEDESIGND")) {
        $path    = "core/cache";
        $handled = opendir($path);
        while (false !== ($file = readdir($handled))) {
            if ($file != ".htaccess" && $file != "." && $file != "..") {
                unlink($path . "/" . $file);
            }
        }
        closedir($handled);
    }

    //get new orders count
    $q = db_query("select count(*) from " . ORDERS_TABLE . " WHERE statusID=" . (int) CONF_NEW_ORDER_STATUS);
    $n = db_fetch_row($q);
    $smarty->assign("new_orders_count", $n[0]);

    $past   = time() - CONF_ONLINE_EXPIRE * 60;
    $result = db_query("select count(*) from " . ONLINE_TABLE . " WHERE time > '" . xEscSQL($past) . "'");
    $u      = db_fetch_row($result);
    $smarty->assign("online_users", $u[0]);

    if (isset($sub)) {
        $smarty->assign("current_sub", $sub);
    }
    $smarty->assign("admin_departments", $admin_departments);
    $smarty->assign("admin_departments_count", $file_count);

    $antMenu = flatAdminDepartments($admin_departments);
    $smarty->assign("antMenu", $antMenu);
###########

    //show Smarty output
    $smarty->display("admin/admin.tpl.html");

} #### ( !isset( $_GET["do"] ) )
