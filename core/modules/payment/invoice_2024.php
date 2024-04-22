<?php
/**
 * @connect_module_class_name CInvoice2024
 *
 */

// Модуль формирования счетов на оплату для юридических лиц  - это херня

if (!defined('NANO_INVOICES_TABLE')) {
    define('NANO_INVOICES_TABLE', 'nano_invoices');
}

class CInvoice2024 extends PaymentModule
{
    public function initVars()
    {

        $this->title       = "Счёт и договор ©2024";
        $this->description = "Базовый счет. ";
        $this->sort_order  = 0;

        $this->Settings = array(

            "PM_SELLER",
            "PM_CURRENCY",
            "PM_CURRENCY_MARK",
            "PM_INVOICE_STRING",
            "PM_CONTRACT_STRING",
            "PM_LOGO",
            "PM_STAMP_MAIN",
            "PM_DEFAULT_TEMPLATE",
            "PM_DEFAULT_TEMPLATE_FOLDER",
            "PM_PLACEHOLDER",
            "PM_WIDTH",

        );
    }

    public function initSettingFields()
    {

        $this->SettingsFields["PM_SELLER"] = array(
            "settings_value"         => "1",
            "settings_title"         => "Компания, от имени которой выставляется счет",
            "settings_description"   => "Выбрать из списка ООО \"КОМПАНИЯ НИКСТЕХНО\"",
            "settings_html_function" => "setting_SELLER_SELECT(",
            "sort_order"             => 1000,
        );
        $this->SettingsFields["PM_CURRENCY"] = array(
            "settings_value"         => "4",
            "settings_title"         => "Валюта - белорусские рубли без НДС",
            "settings_description"   => "Счета на оплату выписываются в белорусских рублях. Выберите из списка валют магазина белорусский рубль. При формировании счета будет использоваться значение курса рубля. Если валюта не определена, будет использован курс выбранной пользователем валюты",
            "settings_html_function" => "setting_CURRENCY_SELECT(",
            "sort_order"             => 1000,
        );
        $this->SettingsFields["PM_CURRENCY_MARK"] = array(
            "settings_value"         => "руб.коп",
            "settings_title"         => "Обозначение валюты в таблице",
            "settings_description"   => "Символы, которыми выводится валюта <strong>руб.коп</strong>",
            "settings_html_function" => "setting_TEXT_BOX(0,",
            "sort_order"             => 1000,
        );

        $this->SettingsFields["PM_INVOICE_STRING"] = array(
            "settings_value"         => "Счет-договор",
            "settings_title"         => "Наименование счета",
            "settings_description"   => "Наименование счета <em>Счет, Счет-договор, Счет-фактура, Счет-протокол и тд. </em>",
            "settings_html_function" => "setting_TEXT_BOX(0,",
            "sort_order"             => 1000,
        );
        $this->SettingsFields["PM_CONTRACT_STRING"] = array(
            "settings_value"         => "Договор",
            "settings_title"         => "Наименование договора",
            "settings_description"   => "Наименование договора <em>Договор, Договор купли-продажи, Условия поставки и тд. </em>",
            "settings_html_function" => "setting_TEXT_BOX(0,",
            "sort_order"             => 1000,
        );

        $this->SettingsFields["PM_LOGO"] = array(
            "settings_value"         => CONF_LOGO_FILE,
            "settings_title"         => "Логотип Организации",
            "settings_description"   => "файл с Логотипом Организации в Шапке Счета",
            "settings_html_function" => "setting_IMAGE(",
            "sort_order"             => 1000,
        );
        $this->SettingsFields["PM_STAMP_MAIN"] = array(
            "settings_value"         => "",
            "settings_title"         => "Печать с подписью",
            "settings_description"   => "файл с круглой печатью и подписью директора Организации",
            "settings_html_function" => "setting_IMAGE(",
            "sort_order"             => 1000,
        );

        //==================================================
        $this->SettingsFields["PM_DEFAULT_TEMPLATE"] = array(
            "settings_value"         => "Payment_temlplate_empty.tpl.html",
            "settings_title"         => "Шаблон Документа - по умолчанию",
            "settings_description"   => "Используемый по-умолчанию <em>графический</em> шаблон документа <code>коммерческое, счет-фактура, счет, счет-протокол и тд</code>",
            "settings_html_function" => "setting_TEXT_BOX(0,",
            "sort_order"             => 1000,
        );
        $this->SettingsFields["PM_DEFAULT_TEMPLATE_FOLDER"] = array(
            "settings_value"         => "core/modules/tpl",
            "settings_title"         => "ПАПКА с Шаблонами Документов",
            "settings_description"   => "Папка на сервере в которой храняться *.tpl.html",
            "settings_html_function" => "setting_TEXT_BOX(0,",
            "sort_order"             => 1000,
        );

        $this->SettingsFields["PM_PLACEHOLDER"] = array(
            "settings_value"         => "____________________",
            "settings_title"         => "Заполнитель",
            "settings_description"   => "Символы которые, предлагается заполнить самостоятельно, <em>____________________ (20 знаков)</em>",
            "settings_html_function" => "setting_TEXT_BOX(0,",
            "sort_order"             => 1000,
        );
        $this->SettingsFields["PM_WIDTH"] = array(
            "settings_value"         => "600",
            "settings_title"         => "Ширина Документа",
            "settings_description"   => "Базовая ширина Документа, можно использовать <code>px em ex</code>",
            "settings_html_function" => "setting_TEXT_BOX(0,",
            "sort_order"             => 1000,
        );
        //==================================================

        //создать таблицу, в которую будет записывать информацию для счета
        // - сумма к оплате в выбранной валюте

        if (!in_array(strtolower(NANO_INVOICES_TABLE), db_get_all_tables())) {

            $sql_invoices = "
    CREATE TABLE `nano_invoices` (
      `invoice_time` timestamp NOT NULL COMMENT 'дата выставления счета',
      `invoiceID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Первичный ключ',
      `orderID` int(11) DEFAULT NULL COMMENT 'ID заказа',
      `module_id` int(11) DEFAULT '100' COMMENT 'ID модуля формирования счета',
      `sellerID` int(11) DEFAULT NULL COMMENT 'ID Продавца',
      `buyerID` int(11) DEFAULT NULL COMMENT 'ID Покупателя',
      `currency_rate` float NOT NULL DEFAULT '1' COMMENT 'Курс без НДС и без наценок, по которому выставляем счет',
      `contractID` int(11) DEFAULT NULL COMMENT 'ID текстов договоров',
      `purposeID` varchar(255) DEFAULT '0' COMMENT 'Цели покупки - кодЫ',
      `fundingID` varchar(255) DEFAULT '0' COMMENT 'Источники финансировния - код',
      `DeliveryType` int(2) NOT NULL DEFAULT '0' COMMENT 'Тип доставки - код: 0 -самовывоз, 1-доставка',
      `PaymentType` int(2) NOT NULL DEFAULT '0' COMMENT 'Оплата: 0-полная предоплата, 1-оплата по факту поставки',
      `actuality_termin` varchar(40) DEFAULT '3' COMMENT 'срок действия счета',
      `delivery_termin` varchar(40) DEFAULT '10' COMMENT 'срок_поставки товара',
      `payment_termin` varchar(40) DEFAULT '1' COMMENT 'срок полной оплаты, равен actuality_termin, в случае 100% -ой предоплаты',
      `payment_prepay` float DEFAULT '100' COMMENT 'Процент предоплаты',
      `deliveryFrom` varchar(255) DEFAULT NULL COMMENT 'Адрес погрузки',
      `deliveryTo` varchar(255) DEFAULT NULL COMMENT 'Адрес разгрузки',
  `requisites` text COMMENT 'Реквизиты',
  `director_nominative` text COMMENT 'Именительный падеж Руководитель',
  `director_genitive` text COMMENT ' Руководитель Родительный падеж',
  `director_reason` text COMMENT 'Действует на основании',
      PRIMARY KEY (`invoiceID`),
      KEY `orderID` (`orderID`),
      KEY `buyerID` (`buyerID`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ALTER TABLE `nano_invoices`
    AUTO_INCREMENT=5000;
                                  ";
            db_query($sql_invoices);
        }

    }

/*

2023-11-07 10:49:43 MySQL error:
Cannot add or update a child row: a foreign key constraint fails (`db_openserver`.`utf_invoices`, CONSTRAINT `UTF_invoices_ibfk_6` FOREIGN KEY (`CID`) REFERENCES `UTF_currency_types` (`CID`) ON DELETE SET NULL)
Query:

INSERT INTO `UTF_invoices` ( `orderID`, `invoice_description`, `invoice_time`, `contract_time`, `module_id`, `sellerID`, `buyerID`, `contractID`, `CID`, `currency_rate`, `purposeID`, `fundingID`, `DeliveryType`, `deliveryFrom`, `deliveryTo`, `delivery_termin`, `PaymentType`, `payment_prepay`, `payment_transactions_count`, `payment_firstrpay_termin`, `payment_fullpay_termin`, `stampsBYTE`, `showToUser`, `update_time`, `invoice_subdiscount`, `invoice_hidden` ) VALUES ( '2349','Новый счет  от 2023-11-07 22:49:43', '2023-11-07 22:49:43', '2023-11-07 22:49:43', '155', '1', '2274',  '0', '0', '0', '0', '0', '0', '', '', '',  '', '0', '0', '0', '0',  '0', '3', NOW(),0,0 );

File: W:\domains\nixminsk.os\core\includes\database\mysqli.php:113
Line: 113

 */

    public function payment_form_html($Adress)
    {

        $text = getInvoiceAddonHtml($Adress);
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
    public function payment_process($order)
    {
        $result                = NANO_payment_process($order);
        $_SESSION["companyID"] = $companyID;
        return $result;
    }

    // *****************************************************************************
    // Purpose        PHP code executed after order has been placed
    // Inputs
    // Remarks
    // Returns
    public function after_processing_php($orderID)
    {

        //сохранить сумму счета
        $orderID = (int) $orderID;
        $order   = ordGetOrder($orderID);

// consolelog($order);

        if ($order) {
//1 -создать invoice к заказу# $orderID

            # $new_invoiceID                  = xToText( trim( $temp_invoiceID ) );                  //invoiceID (0)
            $new_invoice_description = "Новый счет  от " . get_current_time();
            $new_invoice_time        = get_current_time();
            $new_contract_time       = get_current_time();
            $new_module_id           = $this->ModuleConfigID;
            $new_sellerID            = $this->getModuleSettingValue(PM_SELLER);

            if (!isset($_SESSION["companyID"])) {
                $new_buyerID = (int) $order["companyID"];
            } else {
                $new_buyerID = (int) $_SESSION["companyID"];
            }

            $new_orderID       = $orderID;
            $new_contractID    = $this->getModuleSettingValue(PM_DEFAULT_CONTRACT_ID);
            $new_CID           = $this->getModuleSettingValue(PM_CURRENCY);
            $new_currency_rate = currGetCurrencyRateByID($new_CID);
            $new_purposeID     = $_POST["pm_purpose"];
            $new_fundingID     = $_POST["pm_funding"];
            $new_DeliveryType  = ($_POST["shipping_method"] != 3) ? 0 : 1;
            $new_deliveryFrom  = (!is_null($this->getModuleSettingValue(PM_DELIVERYFROM))) ? $this->getModuleSettingValue(PM_DELIVERYFROM) : "";
            $new_deliveryTo    = (isset($_POST["pm_deliveryTo"]) && mb_strlen(trim($_POST["pm_deliveryTo"])) >= 2)
            ? $_POST["pm_deliveryTo"]
            : ""; #$_POST["address"] . "; " . $_POST["pm_address"]; //deliveryTo (15)

            $new_invoice_data = array();
            $new_invoice_data = array(
                "invoiceID"           => (int) $new_invoiceID,
                "orderID"             => (int) $new_orderID,
                "invoice_description" => $new_invoice_description,
                "invoice_time"        => $new_invoice_time,
                "contract_time"       => $new_contract_time,
                "module_id"           => (int) $new_module_id,
                "sellerID"            => (int) $new_sellerID,
                "buyerID"             => (int) $new_buyerID,
                "contractID"          => (int) $new_contractID,
                "CID"                 => (int) $new_CID,
                "currency_rate"       => $new_currency_rate,
                "purposeID"           => (int) $new_purposeID,
                "fundingID"           => (int) $new_fundingID,
                "DeliveryType"        => $new_DeliveryType,
                "deliveryFrom"        => $new_deliveryFrom,
                "deliveryTo"          => $new_deliveryTo,
                "stampsBYTE"          => $new_stampsBYTE,
                "showToUser"          => 3,
                "update_time"         => get_current_time(),
                "invoice_subdiscount" => 0,
                "hidden"              => 0,
            );

            $my_invoice = invoiceAddNewInvoice($new_invoice_data);

//END 1 -создать invoice к заказу# $orderID

//2 -отправить счет покупателю и админу по электронной почте

//END 2 -отправить счет покупателю по электронной почте

        } else {
// Сформировать ошибку создания заказа и предложение созвониться
            die("Ошибка создания заказа - Свяжитесь с нами по телефону +375 29 6583031");
        }

    }
    // *****************************************************************************
    // Purpose        html code printed after order has been placed and after_processing_php
    //                         has been executed
    // Inputs
    // Remarks
    // Returns
    public function after_processing_html($orderID)
    {

        if (in_array(100, checklogin())) {
            $isadmin = true;
        } else {
            $isadmin = false;
        }

        $html = getHtmlSuccessOrder($this, $orderID, $isadmin);
        return $html;

    }

    public function uninstall($_ModuleConfigID = 0)
    {

        PaymentModule::uninstall($_ModuleConfigID);

        if (!count(modGetModuleConfigs(get_class($this)))) {

            //удалить таблицу с информацией о счетах
            // db_query( "DROP TABLE IF EXISTS " .NANO_INVOICES_TABLE );
        } else {

            $sql = 'UPDATE ' . NANO_INVOICES_TABLE . ' SET module_id=' . DEFAULT_PAYMENT_MODULE_ID . ' WHERE module_id=' . (int) $this->ModuleConfigID;
            db_query($sql);
            // $sql = 'DELETE FROM ' .NANO_INVOICES_TABLE . ' WHERE module_id=' . (int)$this->ModuleConfigID;

        }
    }
}
