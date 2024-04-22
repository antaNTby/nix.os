<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-08-23       #
#          http://nixby.pro              #
##########################################


    if ( isset($visit_history) && isset($_SESSION["log"]) )
        {
                $callBackParam = array( "log" => $_SESSION["log"] );
                $visits = null;
                $offset = 0;
                $count = 0;
                $navigatorHtml = GetNavigatorHtmlNEW( "index.php?visit_history=yes", 128,
                                'stGetVisitsByLogin', $callBackParam, $visits, $offset, $count );

                $smarty->assign("navigator", $navigatorHtml );
                $smarty->assign("visits", $visits );
                $smarty->assign("count", $count );
                $smarty->assign( "PageH1", STRING_VISIT_HISTORY );
                $smarty->assign("main_content_template", "visit_history.tpl.html");
        }

?>