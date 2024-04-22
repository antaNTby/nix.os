<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-08-23       #
#          http://nixby.pro              #
##########################################

        if (!strcmp($sub, "category_viewed_times"))
        {
        if ( CONF_BACKEND_SAFEMODE != 1 && (!isset($_SESSION["log"]) || !in_array(24,$relaccess))) //unauthorized
        {
                          $smarty->assign("admin_sub_dpt", "error_forbidden.tpl.html");
                        } else {

                if(isset($_GET["clear"]) ) db_query("update ".CATEGORIES_TABLE." set viewed_times=0");

                $category_report=GetCategortyViewedTimesReport();

                $smarty->assign("categories", $category_report );

                //set sub-department template
                $smarty->assign("admin_sub_dpt", "reports_category_viewed_times.tpl.html");
        }
        }
?>