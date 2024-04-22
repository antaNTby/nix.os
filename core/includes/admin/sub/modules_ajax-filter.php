<?php
if ( !strcmp($sub, "ajax-filter") )
{
define('ACCESS_LEVEL', '12'); // 12 = Общие настройки
define('MAIN_REDIRECT', ADMIN_FILE.'?dpt=modules&sub=ajax-filter');

if (!isset($_SESSION["log"]) || !in_array(ACCESS_LEVEL,checklogin()) || CONF_BACKEND_SAFEMODE) $smarty->assign("admin_sub_dpt", "error_forbidden.tpl.html");
else
    {
    if ( isset($_POST["save"]) )
        {
        $data = ScanPostVariableWithId(array("option"));
        foreach( $data as $key => $val ) db_query("UPDATE ".PRODUCT_OPTIONS_TABLE." SET ajaxfilter=".$val['option']." WHERE optionID=".(int)$key);
        }

    $data = db_query("SELECT * FROM ".PRODUCT_OPTIONS_TABLE." ORDER BY sort_order, name");
    $options = array();
    while ($row = db_fetch_assoc($data)) $options[] = $row;
    $smarty->assign("options", $options);

    $smarty->assign("controls", settingCallHtmlFunctions(CONF_MODULES_AJAX_FILTER));
    if ( isset($_POST["save"]) ) Redirect(MAIN_REDIRECT);
    $smarty->assign("settings", settingGetSettings(CONF_MODULES_AJAX_FILTER));
    $smarty->assign("admin_sub_dpt", "modules_ajax-filter.tpl.html");
    }
}
?>