<?php

if ( !defined( 'DEFAULT_CHARSET_HTML' ) ) {
    define( 'DEFAULT_CHARSET_HTML', 'UTF-8' );
}

if ( !defined( 'DEFAULT_CHARSET_PHP' ) ) {
    define( 'DEFAULT_CHARSET_PHP', 'UTF8' );
}

if ( !defined( 'DEFAULT_CHARSET' ) ) {
    define( 'DEFAULT_CHARSET', 'UTF8' );
}

if ( QUICK_CART_ENABLE ) {

    if ( in_array( 100, checklogin() ) ) {
        $adminislog = true;
        $smarty->assign( "isadmin", "yes" );
    } else {
        $adminislog = false;
    }

    $last_order           = array();
    $customerCompanies    = array();
    $company_last_order   = array();
    $last_order_companyID = 0;

    if ( isset( $_GET['shopping_cart'] ) ) {

        $smarty->assign( "additional_fields", GetRegFields() );
        $smarty->assign( "regions", znGetZones( QUICK_CART_COUNTRY ) );

        if (  ( isset( $_SESSION["log"] ) ) ) {

            regGetContactInfo( $_SESSION["log"], $cust_password, $email, $first_name, $last_name, $subscribed4news, $additional_field_values );

            $customerID              = regGetIdByLogin( $_SESSION["log"] );
            $additional_field_values = GetRegFieldsValuesByCustomerID( $customerID );

            $smarty->assign( "additional_field_values", $additional_field_values );
            $smarty->assign( "first_name", $first_name );
            $smarty->assign( "last_name", $last_name );
            $smarty->assign( "email", $email );
            $defaultAddressID     = regGetDefaultAddressIDByLogin( $_SESSION["log"] );
            $address              = regGetAddress( $defaultAddressID );
            $address['addressID'] = $defaultAddressID;

            ## //ant64 получаем последний заказ
            $customerCompanies = orderGetCompaniesForCustomer( $customerID ); // ant64 - сделать потом селект для пользовательских компаний
            $smarty->assign( "customerCompanies", $customerCompanies );

            $last_order_companyID = ( isset( $_SESSION["companyID"] ) && $_SESSION["companyID"] > 0 )
            ? $_SESSION["companyID"]
            : orderGetLastCompanyIDFromOrders( $customerID );
            ## //ant64 END получаем последний заказ

            $smarty->assign( "disabled", QUICK_CART_EDIT_ENABLE ? '' : ' disabled' );
        } else {

            $address = array( 'address' => '', 'zoneID' => 100, 'addressID' => 0 ); // $address = array( 'address' => '', 'zoneID' => 0, 'addressID' => 0 );
            $smarty->assign( "disabled", '' );

            $last_order_companyID = ( isset( $_SESSION["companyID"] ) && $_SESSION["companyID"] > 0 )
            ? $_SESSION["companyID"]
            : 0;
        }

        if ( $last_order_companyID > 0 ) {
            $_SESSION["companyID"] = $last_order_companyID;
        } else {
            unset( $_SESSION["companyID"] );
        }

        $smarty->assign( "last_order_companyID", $last_order_companyID );

        $smarty->assign( "address", $address );

        if ( isset( $_POST["zoneIDpreselected"] ) ) {
            $zoneIDpreselected = (int)$_POST["zoneIDpreselected"];
        }

        $shipping = NANO_GetShippingSelector( $adress["zoneID"] );

        $smarty->assign( "ShippingSelector", $shipping['html'] );

        $shipping_count = count( $shipping['shipping_methods'] );

        if ( !QUICK_CART_SHIPPING_ENABLE ) {
            $shippingID = -1;
        }
        // запрещен показ способов доставки - выводим все способы оплаты
        elseif ( $address['zoneID'] == 0 && CONF_ADDRESSFORM_STATE == 0 ) {
            $shippingID = 0;
        }
        // не выбран регион (при обязательности выбора) - выводим "не выбран способ доставки"
        elseif ( $shipping_count == 0 ) {
            $shippingID = -1;
        }
        // нет ни одного способа доставки - выводим все способы оплаты
        elseif ( $shipping_count == 1 ) {
            $shippingID = $shipping['shipping_methods'][0]['SID'];
        }
        // один способ доставки - выводим соответствующие способы оплаты
        else {
            $shippingID = 0;
        }
        // несколько способов доставки - выводим "не выбран способ доставки"

        if ( QUICK_CART_PAYMENT_ENABLE ) {

            $payment = getHtmlPaymentSelector( $shippingID );

            $smarty->assign( "PaymentSelector", $payment['html'] );
        }
    }

#######
    ###
    if ( isset( $_GET['zoneID'] ) ) {
        header( "Content-type: text/html; charset=" . DEFAULT_CHARSET_HTML );
        $shipping = NANO_GetShippingSelector( $_GET["zoneID"] );
        exit( $shipping['html'] );
    }

#######
    ###
    if ( isset( $_GET['shippingID'] ) ) {
        header( "Content-type: text/html; charset=" . DEFAULT_CHARSET_HTML );
        // $payment = quickcartGetPaymentSelector( (int)$_GET['shippingID'] );

        $payment = getHtmlPaymentSelector( (int)$_GET['shippingID'] );
        exit( $payment['html'] );
    }

#######
    ###
    if ( isset( $_GET['paymentID'] ) ) {
        header( "Content-type: text/html; charset=" . DEFAULT_CHARSET_HTML );

        // ant в этом месте понятно зарегистрированный ли пользователь делает заказ
        if ( (int)$_GET['paymentID'] && ( $paymentMethod = payGetPaymentMethodById( (int)$_GET['paymentID'] ) ) && ( $paymentModule = modGetModuleObj( $paymentMethod['module_id'], PAYMENT_MODULE ) ) ) {
            if ( isset( $_SESSION["log"] ) ) {
                // $address = array( 'BillingAddressID' => regGetDefaultAddressIDByLogin( $_SESSION["log"] ) );
                $address = array( 'BillingAddressID' => regGetDefaultAddressIDByLogin( $_SESSION["log"] ),
                    'customerID'                        => regGetIdByLogin( $_SESSION["log"] ),
                    // 'log'                               => $_SESSION['log'],
                     'adminislog'                        => $adminislog );

            } else {
                $address = array( 'countryID' => $_SESSION['billing_countryID'],
                    'zoneID'                     => $_SESSION['billing_zoneID'],
                    'first_name'                 => $_SESSION["billing_first_name"],
                    'last_name'                  => $_SESSION["billing_last_name"],
                    'city'                       => $_SESSION["billing_city"],
                    'address'                    => $_SESSION["billing_address"] );
            }
            $html = $paymentModule->payment_form_html( $address );
        } else {
            $html = '';
        }
        exit( $html );
    }

#######
    ###
    if ( isset( $_GET['addon_validate'] ) ) {
        foreach ( $_POST as $key => $val ) {
            $_POST[$key] = $val;
        }

        $paymentMethod = payGetPaymentMethodById( (int)$_GET['addon_validate'] );

        $paymentModule = modGetModuleObj( $paymentMethod['module_id'], PAYMENT_MODULE );
        // exit( (string)$paymentModule->payment_process( $order = array() ) );

        // $Result=array();
        $paymentResult = $paymentModule->payment_process( $order = array() );
        // header( "Content-Type: application/json; charset=utf-8" );
        header( "Content-Type: text/html; charset=UTF-8" );
        // exit( json_encode( $paymentResult ) );
        exit( $paymentResult );

        /*
    теоретически-требуемое содержимое массива $order можно посмотреть в class.virtual.paymentmodule.php,
    но функция payment_process() штатно реализована в одном-единственном модуле "Выставление счетов" (invoice_jur.php)
    и не использует этот массив вообще.
     */
    }

#######
    ###
    if ( isset( $_GET['loadCompanyData'] ) ) {
        $company_data = array();
        $companyID    = 0;

        if ( isset( $_SESSION["companyID"] ) ) {
            $companyID = (int)$_SESSION["companyID"];

            if ( isset( $_POST["companyID_to_load"] ) && (int)$_POST["companyID_to_load"] > 0 ) {
                $companyID = (int)$_POST["companyID_to_load"];
            }
        }

        if ( $companyID > 0 ) {

            $company_data = companyGetCompanyForInvoice( $companyID );

            if ( $company_data != array() ) {
                $CompanyVariants = NANO_sameUnpCompanies( $company_data );
                if ( count( $CompanyVariants ) ) {
                    $company_data["variants"]       = $CompanyVariants;
                    $company_data["variants_count"] = count( $CompanyVariants );
                    $company_data["selectedID"]     = $companyID;
                }

                $_SESSION["companyID"] = $companyID;
            }
        }

        header( "Content-Type: text/html; charset=UTF-8" ); /// text/html; charset=UTF-8 !!!!!
        exit( json_encode( $company_data ) );
    }

#######
    ###
    if ( isset( $_GET['saveCompanyData'] ) ) {
        $res                     = array();
        $company_fields          = array();
        $companyFieldsChanged    = array();
        $read_only               = (int)$_POST["read_only"];
        $readonly_switcher_state = (int)$_POST["readonly_switcher_state"];
        $companyID               = (int)$_POST["companyID"];
        $isCompanyChanged        = (int)$_POST["isCompanyChanged"];
        $isNewCompany            = (int)$_POST["isNewCompany"];
        $companyFieldsChanged    = ( $_POST["companyFieldsChanged"] );
        $pm_fields               = ( $_POST["pm_fields"] );

        if ( is_array( $pm_fields ) && count( $pm_fields ) > 0 ) {
            $isValidUNP = companCheckUNP( $pm_fields["pm_unp"] );

            foreach ( $pm_fields as $key => $value ) {
                $company_fields[str_replace( 'pm_', 'company_', $key )] = $value;
            }

            if ( $_POST["activeTab"] == "fields_tab" ) {
                $company_fields['company_director'] = serialize(
                    array(
                        $company_fields['company_director_nominative'],
                        $company_fields['company_director_genitive'],
                        $company_fields['company_director_reason'] )
                );
            }

            if ( $_POST["activeTab"] == "text_tab" ) {
                ##  ищем UNP
                $parsedUNP                   = companParseUNP( $pm_fields["pm_userdata"] );
                $company_fields['parsedUNP'] = $parsedUNP;
                $isValidUNP_temp             = companCheckUNP( $parsedUNP );
            }

            if (
                (  ( $_POST["activeTab"] == "fields_tab" ) && ( !$isValidUNP ) ) ||
                (  ( $_POST["activeTab"] == "text_tab" ) && ( !$isValidUNP_temp ) )
            ) {
                $error = array(
                    "ERROR"           => "Не найден УНП ( 9-тизначный цифровой код",
                    "companyID"       => $companyID,
                    "isValidUNP"      => $isValidUNP,
                    "pm_unp"          => $pm_fields["pm_unp"],
                    "pm_userdata"     => $pm_fields["pm_userdata"],
                    "parsedUNP"       => $parsedUNP,
                    "isValidUNP_temp" => $isValidUNP_temp,
                );
/*
header( "Content-Type: application/json; charset=utf-8" );
// header( "Content-Type: text/html; charset=UTF-8" );
exit( $error );*/

                $res = $error;
            }

            if ( $companyID == 0 && $isNewCompany && $isCompanyChanged ) {

                $company_fields["company_title"] .= "НОВАЯ КОМПАНИЯ Заказчика";
                $res                   = NANO_AddCompany( $company_fields );
                $_SESSION["companyID"] = $res["companyID"];
            } else {
                if ( $isadmin && $isCompanyChanged ) {
                    if ( $companyID > 0 ) {
                        $res = NANO_UpdateCompany( $company_fields, $companyID );
                    } elseif ( $companyID == 0 ) {
                        $res = NANO_AddCompany( $company_fields );
                    }
                    $_SESSION["companyID"] = $res["companyID"];
                }

                if ( !$isadmin && $isCompanyChanged ) {
                    if ( $companyID == 0 ) {
                        $company_fields["company_title"] .= " НОВАЯ КОМПАНИЯ";
                        $res = NANO_AddCompany( $company_fields );
                    } elseif ( $companyID > 0 && !$readonly_switcher_state && !$read_only ) {
                        $company_fields["company_title"] .= " Вариант для id { $companyID}";
                        $res = NANO_UpdateCompany( $company_fields, $companyID );
                    } elseif ( $companyID > 0 ) {
                        $company_fields["company_title"] .= " + " . $companyFieldsChanged;
                        $res = NANO_AddCompany( $company_fields );
                    }
                    $_SESSION["companyID"] = $res["companyID"];
                }
            }

        }

        header( "Content-Type: text/html; charset=UTF-8" );
        exit( json_encode( $res ) );
    }

# BEGIN авторегистрация
    #######
    ###
    // if ( isset( $_GET['email_validate'] ) ) {
    //     $email = trim( $_GET['email_validate'] );
    //     if ( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
    //         exit( 'Не корректный e-mail!' );
    //     }

    //     list( $user, $domain ) = explode( '@', $email, 2 );
    //     if ( !checkdnsrr( $domain, 'A' ) ) {
    //         exit( 'Не существующий домен в e-mail! Возможно вы опечатались :)' );
    //     }

    //     # !isset( $_SESSION["log"] ) - только для незалогиненых пользователей
    //     if ( !isset( $_SESSION["log"] ) ) {
    //         if ( QUICK_CART_AUTO_REGISTER
    //             && QUICK_CART_EMAIL_LOGIN
    //             && db_fetch_row( db_query( "SELECT Login FROM " . CUSTOMERS_TABLE . " WHERE Login='{$email}' LIMIT 1" ) ) ) {
    //             exit( 'Такой E-mail уже зарегистрирован как логин! Попробуйте авторизоваться заново или укажите другой Email' );
    //         }
    //     }

    //     exit();
    // }

#######
    ###
    if ( isset( $_GET['email_validate_nano'] ) ) {
        $res                 = "1";
        $email               = trim( $_GET['email_validate_nano'] );
        list( $user, $domain ) = explode( '@', $email, 2 );
        if ( $email == "" ) {
            $res = 'Не заполнен e-mail!';
        }
        elseif ( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
            $res = 'Не корректный e-mail!';
        }
        elseif ( $user && $domain && !checkdnsrr( $domain, 'A' ) ) {
            $res = 'Не существующий домен "' . $domain . '" в e-mail! Возможно вы опечатались :)';
        }
        elseif ( !isset( $_SESSION["log"] ) ) {
            if ( QUICK_CART_AUTO_REGISTER
                && QUICK_CART_EMAIL_LOGIN
                && db_fetch_row( db_query( "SELECT Login FROM " . CUSTOMERS_TABLE . " WHERE Login='{$email}' LIMIT 1" ) ) ) {
                $res = 'Такой e-mail уже зарегистрирован как логин! Попробуйте авторизоваться заново или укажите другой Email';
            }
        } else {
            $res = "1";
        }
        // header( "Content-Type: application/json; charset=utf-8" );
        header( "Content-Type: text/html; charset=UTF-8" );
        // exit ( json_encode( $res ) );
        exit( $res );
    }
# END авторегистрация

#######
    ###
    if ( isset( $_GET['send_quick_cart'] ) ) {

        // создаем непосредственно сам ORDER , Invoice создается уже в нем в функции ordOrderProcessing

        $first_name = xEscSQL( trim( $_POST['first_name'] ) );
        $last_name  = xEscSQL( trim( $_POST['last_name'] ) );
        $email      = xEscSQL( $_POST['email'] );
        $addressID  = (int)$_POST['addressID'];
        $zoneID     = isset( $_POST['zoneID'] ) ? (int)$_POST['zoneID'] : 100;
        $city       = isset( $_POST['city'] ) ? xEscSQL( trim( $_POST['city'] ) ) : 'Мiнск';
        $address    = isset( $_POST['address'] ) ? xEscSQL( str_replace( "\r\n", ", ", trim( $_POST['address'] ) ) ) : '';

        if ( isset( $_SESSION["log"] ) ) {
            $customerID = regGetIdByLogin( $_SESSION["log"] );
        } else {
            # BEGIN авторегистрация
            #    else $customerID = 0; // Заказ без регистрации.
            if ( QUICK_CART_AUTO_REGISTER ) {
                if ( QUICK_CART_EMAIL_LOGIN ) {
                    $login = ( $email != "" )
                    ? trim( $email )
                    : $login = 'email_' . generateRndCode( 6, '1234567890' );
                } else {
                    while ( true ) {
                        if ( !regIsRegister( $login = 'user_' . generateRndCode( 6, '1234567890' ) ) ) {
                            break;
                        }
                    }
                }

                $cust_password = generateRndCode( 6 );

                regRegisterCustomer( $login, $cust_password, $email, $first_name, $last_name, 0, ScanPostVariableWithId( array( "additional_field" ) ), $affiliateLogin = '' );

                regSetDefaultAddressIDByLogin( $login, regAddAddress( $first_name, $last_name, CONF_DEFAULT_COUNTRY, 0, "", "", $address, $login, $errorCode ) );
                regEmailNotification( $smarty_mail, $login, $cust_password, $email, $first_name, $last_name, 0, $additional_field_values, CONF_DEFAULT_COUNTRY, 0, "", "", $address, 0 );
                $customerID = regGetIdByLogin( $login );
            } else {
                $customerID = 0;
            }
            # END авторегистрация
        }

        if ( CONF_CHECKSTOCK && QUICK_CART_CHECKSTOCK && quickcartCheckCartInStock() ) {
            Redirect( "index.php?product_removed=yes" );
        }

        $data = ScanPostVariableWithId( array( "additional_field" ) ); // ant64
        if ( !$adminislog && isset( $_SESSION["log"] ) ) {
            //игнорируем изменение данных Админов
            if ( QUICK_CART_EDIT_ENABLE ) {
                db_query( "UPDATE " . CUSTOMERS_TABLE . " SET `first_name`='{$first_name}', `last_name`='{$last_name}', `Email`='{$email}' WHERE `customerID`={$customerID}" );
                db_query( "DELETE FROM " . CUSTOMER_REG_FIELDS_VALUES_TABLE . " WHERE `customerID`=$customerID" );
                foreach ( $data as $key => $val ) {
                    db_query( "INSERT INTO " . CUSTOMER_REG_FIELDS_VALUES_TABLE . " SET  `customerID`={$customerID},`reg_field_ID`={$key},`reg_field_value`='" . $val['additional_field'] . "'" );
                }

                db_query( "UPDATE " . CUSTOMER_ADDRESSES_TABLE . " SET `first_name`='{$first_name}', `last_name`='{$last_name}', `countryID`=" . QUICK_CART_COUNTRY . ", `zoneID`={$zoneID}, `city`='{$city}', `address`='{$address}' WHERE `addressID`={$addressID}" );
            }
        } else {
            foreach ( $data as $key => $val ) {
                $_SESSION['additional_field_' . $key] = $val['additional_field'];
            }

            $_SESSION["first_name"]         = $_SESSION["receiver_first_name"]         = $_SESSION["billing_first_name"]         = $first_name;
            $_SESSION["last_name"]          = $_SESSION["receiver_last_name"]          = $_SESSION["billing_last_name"]          = $last_name;
            $_SESSION["email"]              = $email;
            $_SESSION["receiver_countryID"] = $_SESSION["billing_countryID"] = QUICK_CART_COUNTRY;
            $_SESSION["receiver_zoneID"]    = $_SESSION["billing_zoneID"]    = $zoneID;
            $_SESSION["receiver_city"]      = $_SESSION["billing_city"]      = $city;
            $_SESSION["receiver_address"]   = $_SESSION["billing_address"]   = $address;
        }

        $shipping_method  = isset( $_POST['shipping_method'] ) ? (int)$_POST['shipping_method'] : 0;
        $payment_method   = isset( $_POST['payment_method'] ) ? (int)$_POST['payment_method'] : 0;
        $shServiceID      = isset( $_POST['shServiceID'][$_POST['shipping_method']] ) ? (int)$_POST['shServiceID'][$_POST['shipping_method']] : 0;
        $_POST['comment'] = str_replace( "\r\n", ", ", trim( $_POST['comment'] ) );

        if ( !isset( $_POST["companyID"] ) ) {
            $companyID = $_SESSION["companyID"];
        } else {
            $companyID = $_POST["companyID"];
        }

        if ( TPL != 'nano' ) {
            if ( !isset( $_POST["companyID"] ) ) {
                $companyID = $_SESSION["companyID"];
            } else {
                $companyID = $_POST["CompanyID"];
            }
        }

        $orderID = ordOrderProcessing(  // function ordOrderProcessing(
            $shipping_method,              // $shippingMethodID,
            $payment_method,               // $paymentMethodID,
            $addressID,                    // $shippingAddressID,
            $addressID,                    // $billingAddressID,
            0,                             // $shippingModuleFiles,
            0,                             // $paymentModulesFiles,
            xEscSQL( $_POST['comment'] ),    // $customers_comment,
            0,                             // $cc_number,
            0,                             // $cc_holdername,
            0,                             // $cc_expires,
            0,                             // $cc_cvv,
            $_SESSION['log'],              // $log,
            $smarty_mail,                  // $smarty_mail,
            $shServiceID,                  // $shServiceID = 0,
            $companyID                     // $companyID = 0
        );                             // )

# BEGIN авторегистрация
        if ( QUICK_CART_AUTO_REGISTER ) {
            db_query( "UPDATE " . ORDERS_TABLE . " SET `customerID`={$customerID} WHERE `orderID`={$orderID}" );
            if ( QUICK_CART_AFTER_ORDER && ( !isset( $_SESSION["log"] ) ) ) {
                $_SESSION['log']  = $login;
                $_SESSION['pass'] = cryptPasswordCrypt( $cust_password, null );
            }
        }
# END авторегистрация
        Redirect( "/index.php?quick_cart_success={$orderID}&billingID={$payment_method}&companyID={$companyID}" );
    }

    if ( isset( $_GET['quick_cart_success'] ) ) {
        if (
            (int)$_GET['billingID'] &&
            ( $paymentMethod = payGetPaymentMethodById( (int)$_GET['billingID'] ) ) &&
            ( $paymentModule = modGetModuleObj( $paymentMethod['module_id'], PAYMENT_MODULE ) )
        ) {
            $smarty->assign( "after_processing_html", $paymentModule->after_processing_html( (int)$_GET['quick_cart_success'] ) );
        }
        $smarty->assign( "main_content_template", "quick_cart.tpl.html" );
    }

}

?>
