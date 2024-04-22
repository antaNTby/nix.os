<?php

if ( !strcmp( $sub, "shipping" ) ) {
    //unauthorized
    if (  ( !isset( $_SESSION["log"] ) || !in_array( 20, $relaccess ) ) ) {
        $smarty->assign( "admin_sub_dpt", "error_forbidden.tpl.html" );
    } else {

        //update was successful
        if ( isset( $_GET["save_successful"] ) ) {
            $smarty->assign( "save_successful", ADMIN_UPDATE_SUCCESSFUL );
        }
        $moduleFiles = GetFilesInDirectory( "core/modules/shipping", "php" );


        foreach ( $moduleFiles as $fileName ) {
            include $fileName;
        }

#############
        ##### ИЗМЕНЕНИЕ НАСТРОЕК
        /*
        $_GET["setting_up"]  - это ID экземпляра установленного модуля
        http://new.nixminsk.by/admin.php?dpt=modules&sub=payment&setting_up=96
        для этого запроса все КОНСТАНТЫ будут иметь постфикс _96
        Компания, от имени которой выставляется счет PM_SELLER_961086:1
        Пункт погрузки PM_DELIVERYFROM_961087:1
        Наименование договора PM_CONTRACT_STRING_961092:1
         */

        if ( isset( $_GET["setting_up"] ) ) {


            $ModuleConfig = modGetModuleConfig( $_GET["setting_up"] );
            #### получаем описание модуля c которым будем работать
            ####     __array__  ::
            ####     {
            ####         "0": "121",
            ####         "1": "Фиксированная стоимость доставки (121)",
            ####         "2": "CShippingModuleFixed",
            ####         "module_id": "121",
            ####         "module_name": "Фиксированная стоимость доставки (121)",
            ####         "ModuleClassName": "CShippingModuleFixed"
            ####     }

            if ( $ModuleConfig['ModuleClassName'] ) {

                eval( '$shipping_module = new ' . $ModuleConfig['ModuleClassName'] . '(' . $_GET["setting_up"] . ');' ); ## создаем экземпляр класса с НУЖНЫМИ НАСТРОЙКАМИ - выполняем код php $shipping_module = new CShippingModuleFixed(96)

            } else {

                foreach ( $moduleFiles as $fileName ) {
                    $module    = null;
                    $className = GetClassName( $fileName );
                    if ( !$className ) {
                        continue;
                    }

                    eval( "\$module = new " . $className . "();" ); ## создаем экземпляр класса "ПО УМОЛЧАНИЮ"?????


                    if ( $module->get_id() == $_GET["setting_up"] ) {
                        $shipping_module = $module;
                        break;
                    }
                }
            }


                $settings  = array();
                $controls  = array();
            if ( $shipping_module ) {

                $constants = $shipping_module->settings_list();


                foreach ( $constants as $constant ) {
                    $settings[] = settingGetSetting( $constant );
                    $controls[] = settingCallHtmlFunction( $constant );
                }
            }

            if ( isset( $_POST['save'] ) ) {
                // Redirect( set_query( 'Pustishka=' ) ); ///// АФИГЕТЬ
                Redirect( set_query( 'putinHuylo=' ) ); ///// АФИГЕТЬ
            }

            $smarty->assign( "settings", $settings );
            $smarty->assign( "controls", $controls );

            $smarty->assign( "shipping_module", $shipping_module );
            $smarty->assign( "constant_managment", 1 );
        }

#############
        ##### ОБЩАЯ СТРАНИЦА
        if ( !isset( $_GET["setting_up"] ) ) {


            $shipping_configs = modGetAllInstalledModuleObjs( SHIPPING_RATE_MODULE );
            foreach ( $shipping_configs as $_Ind => $_Conf ) {

                $shipping_configs[$_Ind] = array(
                    'ConfigID'        => $_Conf->get_id(),
                    'ConfigName'      => $_Conf->title,
                    'ConfigClassName' => get_class( $_Conf ),
                );
            }
            $shipping_modules            = array();
            $shipping_methods_by_modules = array();
            foreach ( $moduleFiles as $fileName ) {
                $className = GetClassName( $fileName );
                if ( !$className ) {
                    continue;
                }

                eval( "\$shippingModule = new " . $className . "();" );
                $shipping_modules[]            = $shippingModule;
                $shipping_methods_by_modules[] = shGetShippingMethodsByModule( $shippingModule );
            }

            function cmpShObjs( $a, $b ) {
                return strcmp( $a->title, $b->title ); //Возвращает -1, если string1 меньше string2, 1, если string1 больше string2, и 0, если строки равны.
            }

            usort( $shipping_modules, "cmpShObjs" ); //usort — Сортирует массив по значениям используя пользовательскую функцию для сравнения элементов Функция всегда возвращает true.

            if ( isset( $_GET["install"] ) ) {

                $shipping_modules[(int)$_GET["install"]]->install();
                Redirect( ADMIN_FILE . "?dpt=modules&sub=shipping" );
            }

            if ( isset( $_GET["uninstall"] ) ) {

                $ModuleConfig = modGetModuleConfig( $_GET["uninstall"] );
                if ( $ModuleConfig['ModuleClassName'] ) {

                    modUninstallModuleConfig( $_GET["uninstall"] );
                } else {

                    foreach ( $shipping_configs as $_tModConf ) {

                        if ( $_tModConf['ConfigID'] == (int)$_GET["uninstall"] ) {

                            eval( '$_tModConf = new ' . $_tModConf['ConfigClassName'] . '();' );
                            $_tModConf->uninstall();
                            break;
                        }
                    }
                }
                Redirect( ADMIN_FILE . "?dpt=modules&sub=shipping" );
            }

            $smarty->assign( "shipping_modules", $shipping_modules );
            $smarty->assign( "shipping_methods_by_modules", $shipping_methods_by_modules );
            $smarty->assign( "shipping_configs", $shipping_configs );
        }

        $smarty->assign( "admin_sub_dpt", "modules_shipping.tpl.html" );
    }
}
?>