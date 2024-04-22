<?php
/**
 * @connect_module_class_name CInvoice2019
 *
 */

// Модуль формирования счетов на оплату для юридических лиц

if ( !defined( "INVOICES_TABLE" ) ) {
    define( "INVOICES_TABLE", DB_PRFX . "INVOICES" );
}

class CInvoice2019 extends PaymentModule {
    public function initVars() {

        $this->title       = "Счёт и договор antaNT64 ©2019-2022";
        $this->description = "Модуль формирования счетов на оплату для юридических лиц c 2019 по 2020 год";
        $this->sort_order  = 0;

        $this->Settings = array(
            "PM_SELLER",
            "PM_DELIVERYFROM",
            "PM_DEFAULT_CONTRACT_ID",
            "PM_CONTRACT_TYPE",
            "PM_DEFAULT_TEMPLATE",
            "PM_INVOICE_STRING",
            "PM_CONTRACT_STRING",

            //==================================================
             "PM_CURRENCY",
            "PM_CURRENCY_MARK",
            "PM_MULTIPLIER",
            "PM_NDS_RATE",
            "PM_NDS_IS_INCLUDED_IN_PRICE",
            //==================================================
             "PM_ROW_TO_PAGEBREAKE",
            "PM_PLACEHOLDER",
            "PM_WIDTH",
            "PM_LOGO",
            "PM_LOGO_AUX",
            "PM_STAMP_EMPTY",
            "PM_STAMP_MAIN",
            "PM_STAMP_AUX",

        );
    }

    public function initSettingFields() {

        $this->SettingsFields["PM_SELLER"] = array(
            "settings_value"         => "1",
            "settings_title"         => "Компания, от имени которой выставляется счет",
            "settings_description"   => "Выбрать из списка ООО \"КОМПАНИЯ НИКСТЕХНО\"",
            "settings_html_function" => "setting_SELLER_SELECT(",
            "sort_order"             => 1010,
        );

        $this->SettingsFields["PM_DELIVERYFROM"] = array(
            "settings_value"         => "Сурганова, 11/2, г.Минск, Республика Беларусь",
            "settings_title"         => "Пункт погрузки",
            "settings_description"   => "Фактический адрес погрузки",
            "settings_html_function" => "setting_TEXT_BOX(0,",
            "sort_order"             => 1011,
        );
        $this->SettingsFields["PM_DEFAULT_CONTRACT_ID"] = array(
            "settings_value"         => "100",
            "settings_title"         => "Тексты Договоров - по умолчанию",
            "settings_description"   => "Используемый по-умолчанию текст шаблон договора",
            "settings_html_function" => "settingCONF_STAMP_CONTRACT_ID(",
            "sort_order"             => 1011,
        );
        $this->SettingsFields["PM_CONTRACT_TYPE"] = array(
            "settings_value"         => "0",
            "settings_title"         => "Разновидность договора",
            "settings_description"   => "-1 - без договора, 0 - однократный договор, 1 - годовой договор с автопродлением",
            "settings_html_function" => "setting_TEXT_BOX(0,",
            "sort_order"             => 1011,
        );
        $this->SettingsFields["PM_DEFAULT_TEMPLATE"] = array(
            "settings_value"         => "Payment_temlplate_empty.tpl.html",
            "settings_title"         => "Шаблон Документа - по умолчанию",
            "settings_description"   => "Используемый по-умолчанию <em>графический</em> шаблон документа <code>коммерческое, счет-фактура, счет, счет-протокол и тд</code>",
            "settings_html_function" => "setting_TEXT_BOX(0,",
            "sort_order"             => 1012,
        );
        $this->SettingsFields["PM_INVOICE_STRING"] = array(
            "settings_value"         => PM_INVOICE_STRING,
            "settings_title"         => "Наименование счета",
            "settings_description"   => "Наименование счета <em>Счет, Счет-договор, Счет-фактура, Счет-протокол и тд. </em>",
            "settings_html_function" => "setting_TEXT_BOX(0,",
            "sort_order"             => 160,
        );
        $this->SettingsFields["PM_CONTRACT_STRING"] = array(
            "settings_value"         => CONTRACT_TITLE_DEFAULT,
            "settings_title"         => "Наименование договора",
            "settings_description"   => "Наименование договора <em>Договор, Договор купли-продажи, Условия поставки и тд. </em>",
            "settings_html_function" => "setting_TEXT_BOX(0,",
            "sort_order"             => 165,
        );
        //==================================================

        $this->SettingsFields["PM_CURRENCY"] = array(
            "settings_value"         => "4",
            "settings_title"         => "Валюта - белорусские рубли без НДС",
            "settings_description"   => "Счета на оплату выписываются в белорусских рублях. Выберите из списка валют магазина белорусский рубль. При формировании счета будет использоваться значение курса рубля. Если валюта не определена, будет использован курс выбранной пользователем валюты",
            "settings_html_function" => "setting_CURRENCY_SELECT(",
            "sort_order"             => 1020,
        );
        $this->SettingsFields["PM_MULTIPLIER"] = array(
            "settings_value"         => "1.0000",
            "settings_title"         => "Дополнительный Множитель (для тендеров, для скидок)",
            "settings_description"   => "Стоимость товара будет умножена на это значение, используйте <code>0.95 для удешевления на 5%, 1.05 для подорожания на 5%</code>",
            "settings_html_function" => "setting_TEXT_BOX(1,",
            "sort_order"             => 1030,
        );
        $this->SettingsFields["PM_CURRENCY_MARK"] = array(
            "settings_value"         => "руб.коп",
            "settings_title"         => "Обозначение валюты в таблице",
            "settings_description"   => "Символы, которыми выводится валюта <strong>руб.коп</strong>",
            "settings_html_function" => "setting_TEXT_BOX(0,",
            "sort_order"             => 1040,
        );
        $this->SettingsFields["PM_NDS_IS_INCLUDED_IN_PRICE"] = array(
            "settings_value"         => "0",
            "settings_title"         => "НДС уже включен в стоимость товаров/курс ",
            "settings_description"   => "Включите эту опцию, если налог уже включен в стоимость товаров в Вашем магазине. Если же НДС не включен в стоимость и должен прибавляться дополнительно, выключите эту опцию",
            "settings_html_function" => "setting_CHECK_BOX(",
            "sort_order"             => 1050,
        );
        $this->SettingsFields["PM_NDS_RATE"] = array(
            "settings_value"         => "20",
            "settings_title"         => "Ставка НДС (%)",
            "settings_description"   => "Укажите ставку НДС в процентах. Если Вы работаете по упрощенной системе налогообложения, укажите 0",
            "settings_html_function" => "setting_TEXT_BOX(1,",
            "sort_order"             => 1060,
        );
        //==================================================
        $this->SettingsFields["PM_ROW_TO_PAGEBREAKE"] = array(
            "settings_value"         => "5",
            "settings_title"         => "Число строк до разрыва страницы",
            "settings_description"   => "Максимальное число строк товаров в таблице, после которого \"Договор\" переносится на новую страницу",
            "settings_html_function" => "setting_TEXT_BOX(2,",
            "sort_order"             => 1070,
        );
        $this->SettingsFields["PM_PLACEHOLDER"] = array(
            "settings_value"         => EMPTY_PLACEHOLDER,
            "settings_title"         => "Заполнитель",
            "settings_description"   => "Символы которые, предлагается заполнить самостоятельно, <em>____________________ (20 знаков)</em>",
            "settings_html_function" => "setting_TEXT_BOX(0,",
            "sort_order"             => 1080,
        );
        $this->SettingsFields["PM_WIDTH"] = array(
            "settings_value"         => "600",
            "settings_title"         => "Ширина Документа",
            "settings_description"   => "Базовая ширина Документа, можно использовать <code>px em ex</code>",
            "settings_html_function" => "setting_TEXT_BOX(0,",
            "sort_order"             => 1090,
        );

        $this->SettingsFields["PM_LOGO"] = array(
            "settings_value"         => CONF_LOGO_FILE,
            "settings_title"         => "Логотип Организации",
            "settings_description"   => "файл с Логотипом Организации в Шапке Счета",
            "settings_html_function" => "setting_IMAGE(",
            "sort_order"             => 1100,
        );
        $this->SettingsFields["PM_LOGO_AUX"] = array(
            "settings_value"         => CONF_LOGO_FILE,
            "settings_title"         => "Дополнительный логотип Организации",
            "settings_description"   => "файл с Дополнительным Логотипом Организации",
            "settings_html_function" => "setting_IMAGE(",
            "sort_order"             => 1110,
        );
        $this->SettingsFields["PM_STAMP_EMPTY"] = array(
            "settings_value"         => "",
            "settings_title"         => "М.П.",
            "settings_description"   => "файл c заполнителем \"М.П.\"",
            "settings_html_function" => "setting_IMAGE(",
            "sort_order"             => 1120,
        );
        $this->SettingsFields["PM_STAMP_AUX"] = array(
            "settings_value"         => "",
            "settings_title"         => "Овальная Печать с подписью",
            "settings_description"   => "файл с овальной печатью и подписью Организации",
            "settings_html_function" => "setting_IMAGE(",
            "sort_order"             => 1125,
        );
        $this->SettingsFields["PM_STAMP_MAIN"] = array(
            "settings_value"         => "",
            "settings_title"         => "Печать с подписью",
            "settings_description"   => "файл с круглой печатью и подписью директора Организации",
            "settings_html_function" => "setting_IMAGE(",
            "sort_order"             => 1130,
        );

        //создать таблицу, в которую будет записывать информацию для счета
        // - сумма к оплате в выбранной валюте

        if ( !in_array( strtolower( INVOICES_TABLE ), db_get_all_tables() ) ) {

            $sql_invoices = "
                                  CREATE TABLE IF NOT EXISTS `" . INVOICES_TABLE . "` (
                                  `invoice_description` varchar(255) NOT NULL COMMENT 'Имя Компании для наглядности',
                                  `invoice_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'дата выставления счета',
                                  `contract_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'дата заключения договора',
                                  `module_id` int(11) DEFAULT NULL COMMENT 'ID модуля формирования счета',
                                  `sellerID` int(11) DEFAULT NULL COMMENT 'ID Продавца',
                                  `buyerID` int(11) DEFAULT NULL COMMENT 'ID Покупателя',
                                  `orderID` int(11) DEFAULT NULL COMMENT 'ID заказа',
                                  `contractID` int(11) DEFAULT NULL COMMENT 'ID текстов договоров',
                                  `CID` int(11) DEFAULT NULL COMMENT 'ID Валюты',
                                  `currency_rate` float NOT NULL COMMENT 'Курс, на моментвыставления счета',
                                  `purposeID` int(2) DEFAULT '0' COMMENT 'цель покупки - код',
                                  `fundingID` int(2) DEFAULT '0' COMMENT 'источник финансировния - код',
                                  `DeliveryType` int(2) DEFAULT '0' COMMENT 'тип доставки - код: 0 -самовывоз, 1-доставка',
                                  `deliveryFrom` varchar(255) COMMENT 'Адрес погрузки',
                                  `deliveryTo` varchar(255) COMMENT 'Адрес разгрузки',
                                  `delivery_termin` float DEFAULT '10' COMMENT 'срок_поставки',
                                  `PaymentType` int(2) DEFAULT NULL COMMENT 'Оплата: 0-полная предоплата, 1-оплата по факту поставки, 2 - кредит',
                                  `payment_prepay` float DEFAULT '100' COMMENT 'Процент предоплаты',
                                  `payment_transactions_count` int(2) DEFAULT '1' COMMENT 'число платежей на которые разбтивается оплата или преодплата',
                                  `payment_firstrpay_termin` float DEFAULT '3' COMMENT 'срок поступления первого платежа',
                                  `payment_fullpay_termin` float DEFAULT '1' COMMENT 'срок полной оплаты',
                                  `stampsBYTE` int(1) NOT NULL DEFAULT '0' COMMENT 'битовая маска выставления печатей 0000 0-war-contr-inv',
                                  `showToUser` int(1) NOT NULL DEFAULT '3' COMMENT 'битовая маска печати частей контракта 0000 ext-warr-contr-inv',,
                                  PRIMARY KEY (`invoiceID`),
                                  KEY `module_id` (`module_id`),
                                  KEY `buyerID` (`buyerID`),
                                  KEY `sellerID` (`sellerID`),
                                  CONSTRAINT `UTF_invoices_ibfk_4` FOREIGN KEY (`buyerID`) REFERENCES `" . COMPANIES_TABLE . "` (`companyID`) ON DELETE SET NULL,
                                  CONSTRAINT `UTF_invoices_ibfk_5` FOREIGN KEY (`sellerID`) REFERENCES `" . COMPANIES_TABLE . "` (`companyID`) ON DELETE SET NULL,
                                  CONSTRAINT `UTF_invoices_ibfk_6` FOREIGN KEY (`CID`) REFERENCES `" . CURRENCY_TYPES_TABLE . "` (`CID`) ON DELETE SET NULL
                                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Таблица Счетов к заказам -invoices';
                                  ";
            db_query( $sql_invoices );
        }

    }

    public function payment_form_html( $Adress ) {

        $text = getInvoiceAddonHtml( $Adress );
        return $text;
    }

    //проверить правильность ввода

    // *****************************************************************************
    // Purpose        core payment processing routine
    // Inputs   $order is array with the following elements:
    //        "customer_email" - customer's email address
    //        "customer_ip" - customer IP address
    //        "order_amount" - total order amount (in conventional units)
    //        "currency_code" - currency ISO 3 code (e.g. USD, GBP, EUR)
    //        "currency_value" - currency exchange rate defined in the backend in 'Configuration' -> 'Currencies' section
    //        "shipping_info" - shipping information - array of the following data:
    //                "first_name", "last_name", "country_name", "state", "city", "address"
    //        "billing_info" - billing information - array of the following data:
    //                "first_name", "last_name", "country_name", "state", "city", "address"
    // Remarks
    public function payment_process( $order ) {
        $result = NANO_payment_process( $order );
        $_SESSION["companyID"] = $companyID;
        return $result;
    }

    // *****************************************************************************
    // Purpose        PHP code executed after order has been placed
    // Inputs
    // Remarks
    // Returns
    public function after_processing_php( $orderID ) {

        //сохранить сумму счета
        $orderID = (int)$orderID;
        $order   = ordGetOrder( $orderID );

// consolelog($order);

        if ( $order ) {
//1 -создать invoice к заказу# $orderID

            # $new_invoiceID                  = xToText( trim( $temp_invoiceID ) );                  //invoiceID (0)
            $new_invoice_description = "Новый счет  от " . get_current_time();
            $new_invoice_time        = get_current_time();
            $new_contract_time       = get_current_time();
            $new_module_id           = $this->ModuleConfigID;
            $new_sellerID            = $this->getModuleSettingValue( PM_SELLER );

            if ( !isset( $_SESSION["companyID"] ) ) {
                $new_buyerID = (int)$order["companyID"];
            } else {
                $new_buyerID = (int)$_SESSION["companyID"];
            }

            $new_orderID       = $orderID;
            $new_contractID    = $this->getModuleSettingValue( PM_DEFAULT_CONTRACT_ID );
            $new_CID           = $this->getModuleSettingValue( PM_CURRENCY );
            $new_currency_rate = currGetCurrencyRateByID( $new_CID );
            $new_purposeID     = $_POST["pm_purpose"];
            $new_fundingID     = $_POST["pm_funding"];
            $new_DeliveryType  = ( $_POST["shipping_method"] != 3 ) ? 0 : 1;
            $new_deliveryFrom  = ( !is_null( $this->getModuleSettingValue( PM_DELIVERYFROM ) ) ) ? $this->getModuleSettingValue( PM_DELIVERYFROM ) : "";
            $new_deliveryTo    = ( isset( $_POST["pm_deliveryTo"] ) && mb_strlen( trim( $_POST["pm_deliveryTo"] ) ) >= 2 )
            ? $_POST["pm_deliveryTo"]
            : ""; #$_POST["address"] . "; " . $_POST["pm_address"]; //deliveryTo (15)

            $new_invoice_data = array();
            $new_invoice_data = array(
                "invoiceID"           => (int)$new_invoiceID,
                "orderID"             => (int)$new_orderID,
                "invoice_description" => $new_invoice_description,
                "invoice_time"        => $new_invoice_time,
                "contract_time"       => $new_contract_time,
                "module_id"           => (int)$new_module_id,
                "sellerID"            => (int)$new_sellerID,
                "buyerID"             => (int)$new_buyerID,
                "contractID"          => (int)$new_contractID,
                "CID"                 => (int)$new_CID,
                "currency_rate"       => $new_currency_rate,
                "purposeID"           => (int)$new_purposeID,
                "fundingID"           => (int)$new_fundingID,
                "DeliveryType"        => $new_DeliveryType,
                "deliveryFrom"        => $new_deliveryFrom,
                "deliveryTo"          => $new_deliveryTo,
                "stampsBYTE"          => $new_stampsBYTE,
                "showToUser"          => 3,
                "update_time"         => get_current_time(),
                "invoice_subdiscount" => 0,
                "hidden"              => 0,
            );

            $my_invoice = invoiceAddNewInvoice( $new_invoice_data );

//END 1 -создать invoice к заказу# $orderID

//2 -отправить счет покупателю и админу по электронной почте

//END 2 -отправить счет покупателю по электронной почте

        } else {
// Сформировать ошибку создания заказа и предложение созвониться
            die( "Ошибка создания заказа - Свяжитесь с нами по телефону +375 29 6583031" );
        }

    }
    // *****************************************************************************
    // Purpose        html code printed after order has been placed and after_processing_php
    //                         has been executed
    // Inputs
    // Remarks
    // Returns
    public function after_processing_html( $orderID ) {

        if ( in_array( 100, checklogin() ) ) {
            $isadmin = true;
        } else {
            $isadmin = false;
        }

        $html = getHtmlSuccessOrder( $this, $orderID, $isadmin );
        return $html;

    }

    public function uninstall( $_ModuleConfigID = 0 ) {

        PaymentModule::uninstall( $_ModuleConfigID );

        if ( !count( modGetModuleConfigs( get_class( $this ) ) ) ) {

            //удалить таблицу с информацией о счетах
            // db_query( "DROP TABLE IF EXISTS " . INVOICES_TABLE );
        } else {

            $sql = 'UPDATE ' . INVOICES_TABLE . ' SET module_id=' . DEFAULT_PAYMENT_MODULE_ID . ' WHERE module_id=' . (int)$this->ModuleConfigID;
            db_query( $sql );
            // $sql = 'DELETE FROM ' . INVOICES_TABLE . ' WHERE module_id=' . (int)$this->ModuleConfigID;

        }
    }
}

?>