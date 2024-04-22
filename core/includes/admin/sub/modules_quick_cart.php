<?php
if ( !strcmp($sub, "quick_cart") )
{
define('ACCESS_LEVEL', '12'); // 12 = Общие настройки
define('MAIN_REDIRECT', ADMIN_FILE.'?dpt=modules&sub=quick_cart');

if (!isset($_SESSION["log"]) || !in_array(ACCESS_LEVEL,checklogin()) || CONF_BACKEND_SAFEMODE) $smarty->assign("admin_sub_dpt", "error_forbidden.tpl.html");
else
    {
    $smarty->assign("controls", settingCallHtmlFunctions(CONF_MODULES_QUICK_CART));
    if (isset($_POST["save"])) Redirect(MAIN_REDIRECT);
    $smarty->assign("settings", settingGetSettings(CONF_MODULES_QUICK_CART));
    $smarty->assign("admin_sub_dpt", "modules_quick_cart.tpl.html");
    }
}
# перенес в saetting_functions.php
// function settingQUICK_CART_COLNUM()
// {
//         if ( isset($_POST["save"]) && isset($_POST["settingQUICK_CART_COLNUM"]) )
//                 _setSettingOptionValue( "QUICK_CART_COLNUM", $_POST["settingQUICK_CART_COLNUM"] );

//         $selectedID = _getSettingOptionValue("QUICK_CART_COLNUM");
//         $res = "<select name='settingQUICK_CART_COLNUM'>";
//         $res .= "<option value='0'".($selectedID == '0'?" selected ":"").">Одна колонка</option>";
//         $res .= "<option value='1'".($selectedID == '1'?" selected ":"").">Две колонки</option>";
//         $res .= "</select>";
//         return $res;
// }
?>
