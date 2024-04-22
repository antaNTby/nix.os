<?php
if ( !strcmp($sub, "forms") )
{
define('ACCESS_LEVEL', '12'); // 12 = Общие настройки
define('MAIN_REDIRECT', ADMIN_FILE.'?dpt=modules&sub=forms');

    if (!isset($_SESSION["log"]) || !in_array(ACCESS_LEVEL,checklogin()) || CONF_BACKEND_SAFEMODE) $smarty->assign("admin_sub_dpt", "error_forbidden.tpl.html");
    else
    {
    if ( isset($_POST["save"]) )
        {
        foreach(ScanPostVariableWithId(array("enable","sort")) as $key => $val)
            db_query("UPDATE ".DB_PRFX."forms SET enable=".(isset($val['enable'])?1:0).",sort_order=".(int)$val['sort']." WHERE formID=$key");
        }

    if (isset($_GET["create"]))
        {
        }

    elseif (isset($_GET["edit"]))
        {
        $smarty->assign("form", GetForm((int)$_GET["edit"]));
        $smarty->assign("changes", GetFormChanges((int)$_GET["edit"]));
        }

    elseif (isset($_GET["delete"]))
        {
        DeleteForm((int)$_GET["delete"]);
        Redirect(MAIN_REDIRECT);
        }

    elseif (isset($_GET["create_save"]))
        {
        $formID = AddForm();
        Redirect(MAIN_REDIRECT.($formID?"&edit=$formID":"&create=yes"));
        }

    elseif (isset($_GET["edit_save"]))
        {
        UpdateForm((int)$_GET["edit_save"]);
        Redirect(MAIN_REDIRECT."&edit=".(int)$_GET["edit_save"]);
        }

    elseif (isset($_GET["add_change"]))
        {
        AddFormChange((int)$_GET["add_change"]);
        Redirect(MAIN_REDIRECT."&edit=".(int)$_GET["add_change"]);
        }

    elseif (isset($_GET["del_change"]))
        {
        DeleteFormChange((int)$_GET["del_change"]);
        Redirect(MAIN_REDIRECT."&edit=".(int)$_GET["ret"]);
        }

    else
        {
#        $smarty->assign("controls", settingCallHtmlFunctions(CONF_MODULES_FORMS));
        if ( isset($_POST["save"]) ) Redirect(MAIN_REDIRECT);
#        $smarty->assign("settings", settingGetSettings(CONF_MODULES_FORMS));
        $smarty->assign("forms", GetAllForms());
        }

    $smarty->assign("admin_sub_dpt", "modules_forms.tpl.html");
    }
}
?>
