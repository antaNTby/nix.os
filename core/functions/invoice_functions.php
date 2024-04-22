<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2019-03-03       #
#          http://nixby.pro              #
##########################################

/*

CREATE TABLE IF NOT EXISTS `" . INVOICES_TABLE . "` (
`invoiceID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID записи в таблице Контрактов',
`invoice_description` varchar(255) NOT NULL COMMENT 'Имя Компании для наглядности',
`invoice_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'дата выставления счета',
`contract_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'дата заключения договора',
`module_id` int(11) DEFAULT NULL COMMENT 'ID модуля формирования счета',
`sellerID` int(11) DEFAULT NULL COMMENT 'ID Продавца',
`buyerID` int(11) DEFAULT NULL COMMENT 'ID Покупателя',
`orderID` int(11) DEFAULT NULL COMMENT 'ID заказа',
`contractID` int(11) DEFAULT NULL COMMENT 'ID текстов договоров',
`CID` int(11) DEFAULT NULL COMMENT 'ID Валюты',
`currency_rate` float NOT NULL COMMENT 'Курс, по которому выставляем счет',
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
`showToUser` int(1) NOT NULL DEFAULT '3' COMMENT 'битовая маска печати частей контракта 0000 ext-warr-contr-inv',
PRIMARY KEY (`invoiceID`),
KEY `module_id` (`module_id`),
KEY `buyerID` (`buyerID`),
KEY `sellerID` (`sellerID`),
CONSTRAINT `UTF_invoices_ibfk_4` FOREIGN KEY (`buyerID`) REFERENCES `" . COMPANIES_TABLE . "` (`companyID`) ON DELETE SET NULL,
CONSTRAINT `UTF_invoices_ibfk_5` FOREIGN KEY (`sellerID`) REFERENCES `" . COMPANIES_TABLE . "` (`companyID`) ON DELETE SET NULL,
CONSTRAINT `UTF_invoices_ibfk_6` FOREIGN KEY (`CID`) REFERENCES `" . CURRENCY_TYPES_TABLE . "` (`CID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Таблица Счетов к заказам -invoices ::russian.php';

 */
// $where_clause = array("=",">","<",">=","<=","!=","LIKE","IN","NOT IN")
function invoiceGetInvoicesFieldsList( $searchValue, $DBFieldsList = "orderID", $primaryKey = "orderID", $where_clause = "=", $orderby_clause = "`invoice_time` DESC, `invoiceID` DESC" ) {
    $sql = "SELECT {$DBFieldsList} FROM `" . INVOICES_TABLE . "` WHERE `{$primaryKey}` {$where_clause} '" . $searchValue . "' ORDER BY {$orderby_clause}";
    $q   = db_query( $sql );
    $res = array();
    while ( $row = db_fetch_assoc( $q ) ) {
        $res[] = $row;
    }
    $result = $res;
    return $result;
}

function getNewPdfLink( $invoiceID ) {

    if ( !is_null( $invoiceID ) ) {
        $Invoice     = invoiceGet( (int)$invoiceID, "`invoiceID`, `buyerID`" );
        $q           = db_query( "SELECT `company_unp` FROM `" . COMPANIES_TABLE . "` WHERE `companyID` =" . (int)$Invoice["buyerID"] );
        $row         = db_fetch_assoc( $q );
        $company_unp = $row["company_unp"];

        $cgi = base64_encode( (int)$invoiceID + 64 );
        $cgc = base64_encode( $company_unp );

        $invoiceID_clause = "&cgi={$cgi}";

        if ( !is_null( $company_unp ) ) {
            $companyID_clause = "&cgc={$cgc}";
        } else {
            $companyID_clause = "";
        }

    } else {
        return false;
    }
    $str = <<<HTML
index.php?do=invoice_new_proc
{$invoiceID_clause}
{$companyID_clause}
HTML;
    $str = trim( $str );
    $str = str_replace( "\r\n", "", $str );
    $str = str_replace( "\r", "", $str );
    $str = str_replace( "\n", "", $str );
    // consolelog( "getLinkToShowInvoice {$invoiceID} --> {$str}" );
    // consolelog( "getLinkToShowInvoice {$invoiceID} -->".urlencode( $str ));
    return urlencode( $str );
}

function getLinkToShowInvoice(
    $ModuleConfigID = DEFAULT_PAYMENT_MODULE_ID,
    $invoiceID = null,
    $orderID = null,
    $companyID = null,
    $currencyID = null,
    $order_time = null,
    $customer_email = null
) {

    if ( !is_null( $ModuleConfigID ) ) {
        $module_id_clause = "&gm={$ModuleConfigID}";
    } else {
        $module_id_clause = "";
    }
    if ( !is_null( $invoiceID ) ) {
        $invoiceID_clause = "&gi={$invoiceID}";
    } else {
        $invoiceID_clause = "";
    }
    if ( !is_null( $orderID ) ) {
        $orderID_clause = "&go={$orderID}";
    } else {
        $orderID_clause = "";
    }
    if ( !is_null( $companyID ) ) {
        $companyID_clause = "&gc={$companyID}";
    } else {
        $companyID_clause = "";
    }
    if ( !is_null( $currencyID ) ) {
        $currencyID_clause = "&cc={$currencyID}";
    } else {
        $currencyID_clause = "";
    }
    if ( !is_null( $order_time ) ) {
        $order_time_clause = "&g1={$order_time}";
    } else {
        $order_time_clause = "";
    }
    if ( !is_null( $customer_email ) ) {
        $customer_email_clause = "&g2={$customer_email}";
    } else {
        $customer_email_clause = "";
    }

    $str = <<<HTML
index.php?do=invoice_new_proc
{$invoiceID_clause}
{$module_id_clause}
{$orderID_clause}
{$companyID_clause}
{$currencyID_clause}
{$order_time_clause}
{$customer_email_clause}
HTML;

    $str = trim( $str );
    $str = str_replace( "\r\n", "", $str );
    $str = str_replace( "\r", "", $str );
    $str = str_replace( "\n", "", $str );

// consolelog("getLinkToShowInvoice {$invoiceID} --> {$str}");
    return ( $str );
}

function invoiceID_to_orderID( $invoiceID ) {
    $sql = "SELECT `orderID` FROM " . INVOICES_TABLE . " WHERE `invoiceID` = " . (int)$invoiceID . "; ";

    $q    = db_query( $sql );
    $data = array();
    while ( $row = db_fetch_assoc( $q ) ) {
        $data[] = $row;
    }
    $result = $data[0]["orderID"];
    // debugfile($data,"$invoiceID to invoiceID_to_orderID($sql)");
    return $result;
}

function orderID_to_invoiceIDs( $orderID ) {
    $sql  = "SELECT `invoiceID` FROM " . INVOICES_TABLE . " WHERE `orderID` = " . (int)$orderID . "; ";
    $q    = db_query( $sql );
    $data = array();
    while ( $row = db_fetch_assoc( $q ) ) {
        $data[] = $row;
    }
    $result = $data;
    return $result;
}

// $mpdf->charset_in = 'cp1251';
// $mpdf->charset_in = 'utf8';
// The library defines a function strcode2utf() to convert htmlentities to UTF-8 encoded text
// $mpdf->use_kwt              = true; // Default: false
function invoiceDoPDF( $html, $invoiceID, $PARAMETERS, $PDF, $filename ) {
    include_once "lib/mpdf/mpdf.php";

    $now = dtDoDates( get_current_time(), $invoiceID );

    //Кодировка | Формат | Размер шрифта | Шрифт
    //Отступы: слева | справа | сверху | снизу | шапка | подвал | ориентация

    // $mpdf = new mPDF( "utf-8", "A4", "8", "\"PT Sans\"", 20, 5, 5, 5, 0, 0, "P" );

    $mpdf = new mPDF(
        $PDF["mode"],
        $PDF["sheet-size"],
        $PDF["font-size"],
        $PDF["font-family"],
        $PDF["ml"],
        $PDF["mr"],
        $PDF["mt"],
        $PDF["mb"],
        $PDF["mh"],
        $PDF["mf"],
        $PDF["orientation"]
    );

    $mpdf->SetHTMLHeader( $PARAMETERS["Header"], "O" );
    $mpdf->AddSpotColor( "PANTONE 534 EC", 85, 65, 47, 9 );

    $mpdf->SetAuthor( xHtmlSpecialCharsDecode( $PARAMETERS["Author"] . ", " . $now["Ymd"] ) );
    $mpdf->SetCreator( xHtmlSpecialCharsDecode( $PARAMETERS["Creator"] ) );
    $mpdf->SetSubject( xHtmlSpecialCharsDecode( $PARAMETERS["Subject"] ) );
    $mpdf->SetTitle( xHtmlSpecialCharsDecode( $PARAMETERS["Title"] ) );
    $mpdf->SetKeywords( xHtmlSpecialCharsDecode( $PARAMETERS["Keywords"] ) );

    $mpdf->SetProtection( array( "print", "copy" ), "", "1" );
    $mpdf->SetDisplayMode( "fullpage", "twoleft" );
    $mpdf->SetDisplayPreferences( "/DisplayDocTitle/CenterWindow/FitWindow/HideWindowUI" );

    # WATERMARK
    if ( $PARAMETERS["ShowWatermark"] == 1 ) {
        $mpdf->SetWatermarkText( "ПРИМЕР\ОБРАЗЕЦ:: Заказ не проверен" ); // Will cope with UTF-8 encoded text
        $mpdf->watermark_font     = "dejavusansmono";                                              // Uses default font if left blank
        $mpdf->watermarkTextAlpha = 0.1;
        $mpdf->showWatermarkText  = true;
        # $mpdf->WriteFixedPosHTML( "ОБРАЗЕЦ :: Заказ не проверен", 15, 40, 100, 10, "auto" );
    }

    $mpdf->ignore_table_percents    = true;
    $mpdf->shrink_tables_to_fit     = 1;
    $mpdf->allow_charset_conversion = true;

    $fn = $PARAMETERS["Filename"];
    // $mpdf->WriteHTML( $html );
    $mpdf->WriteHTML( $html );
    // $mpdf->Output( $fn, "D" );
    // $mpdf->Output( $fn, "I" );

    if ($PARAMETERS["savefile"] == true){
        $mpdf->Output( $fn, "D" );  //загрузить файл "D_".
    } else {
        $mpdf->Output( $fn, "I" );  //открыть во вкладке "I_".
    }

        // $mpdf->Output( "I_".$fn, "I" );  //открыть во вкладке
        // $mpdf->Output( "D_".$fn, "D" );  //загрузить файл
        // $mpdf->Output( "F_".$fn, "F" );  // сохранить на сайте  save to a local file
        $mpdf->Output( "./data/files/PDF_OUT/".$fn, "F" );  // сохранить на сайте  save to a local file

}

function parseInt( $strNonNumeric ) {
    $int = (int)preg_replace( '/\D/', '', $strNonNumeric );
    return $int;
}
function parseDayType( $strNonNumeric ) {
    $int = (int)preg_replace( '/\D/', '', $strNonNumeric );

    $daytype = "%w";

    if ( stristr( $strNonNumeric, "%b" ) ) {
        $daytype = "%b";
    } elseif ( stristr( $strNonNumeric, "%n" ) ) {
        $daytype = "%n";
        $bel     = "";
    } elseif ( stristr( $strNonNumeric, "%w" ) ) {
        $daytype = "%w";
    } else {
        $daytype = "%w";
    }

    if ( stristr( $strNonNumeric, "==" ) ) {
        $s = str_replace( "{$int}", "", $strNonNumeric );
        // $s = $strNonNumeric;
        $daytype = $s;
    }

    return $daytype;
}

function DaysToStringRussian( $subject ) {

    # %b - банковские дни
    # %w - рабочие дни
    # %n - дни
    # == - без изменений

    $billions = 0;
    $millions = 0;
    $grands   = 0;
    $days     = 0;
    $n        = 0;
    $s        = "";

    preg_match( "/\d+/", $subject, $matches );

    $n = $matches[0];
    $n = floatval( $n );

    $daytype = "рабоч";

    if ( stristr( $subject, "%b" ) ) {
        $daytype = "банковск";
    } elseif ( stristr( $subject, "%n" ) ) {
        $daytype = "";
        $bel     = "";
    } elseif ( stristr( $subject, "%w" ) ) {
        $daytype = "рабоч";
    } else {
        $daytype = "рабоч";
    }

    // ( strstr($subject,"%b%") )? $daytype = "банковск";: ( strstr($subject,"%n%") )?$daytype = "";:$daytype = "рабоч";

    //разделить число на разряды: единицы, тысячи, миллионы, миллиарды (больше миллиардов не проверять :) )

    $billions = floor( $n / 1000000000 );
    $millions = floor(  ( $n - $billions * 1000000000 ) / 1000000 );
    $grands   = floor(  ( $n - $billions * 1000000000 - $millions * 1000000 ) / 1000 );
    $days     = floor(  ( $n - $billions * 1000000000 - $millions * 1000000 - $grands * 1000 ) ); //$n % 1000;

    //часы
    $hrs = round( $n * 24 - round( floor( $n ) * 24 ) );
    if ( $hrs < 10 ) {
        $hrs = (string)$hrs;
    }
    //"0".

    $s = "";

    if ( $billions > 0 ) {
        $t    = "ов";
        $temp = $billions % 10;
        if ( floor(  ( $billions % 100 ) / 10 ) != 1 ) {
            if ( $temp == 1 ) {
                $t = "";
            } elseif ( $temp >= 2 && $temp <= 4 ) {
                $t = "а";
            }
        }
        $s .= num_to_russian_nominative( $billions, 1 ) . " миллиард$t ";
    }

    if ( $millions > 0 ) {
        $t    = "ов";
        $temp = $millions % 10;
        if ( floor(  ( $millions % 100 ) / 10 ) != 1 ) {
            if ( $temp == 1 ) {
                $t = "";
            } elseif ( $temp >= 2 && $temp <= 4 ) {
                $t = "а";
            }
        }
        $s .= num_to_russian_nominative( $millions, 1 ) . " миллион$t ";
    }

    if ( $grands > 0 ) {
        $t    = "";
        $temp = $grands % 10;
        if ( floor(  ( $grands % 100 ) / 10 ) != 1 ) {
            if ( $temp == 1 ) {
                $t = "а";
            } elseif ( $temp >= 2 && $temp <= 4 ) {
                $t = "и";
            }
        }
        $s .= num_to_russian_nominative( $grands, 0 ) . " тысяч$t ";
    }

    $dn = "дн";
    if ( $days > 0 || $grands > 0 || $millions > 0 || $billions > 0 ) {
        $rub  = "ей";
        $bel  = "их";
        $temp = $days % 10;
        if ( floor(  ( $days % 100 ) / 10 ) != 1 ) {
            if ( $temp == 1 ) {
                $rub = "ь";
                $dn  = "ден";
                $bel = "ий";
            } elseif ( $temp >= 2 && $temp <= 4 ) {
                $rub = "я";
                $dn  = "дн";
                $bel = "их";
            }
        }
    } //ioiuoiiuoiu

    if ( $daytype == "" ) {
        $bel = "";
    }

    if ( $days > 0 || $grands > 0 || $millions > 0 || $billions > 0 ) {
        $s .= num_to_russian_nominative( $days, 1 ) . " ) " . $daytype . $bel . " " . $dn . $rub;
        // $s .= num_to_russian_nominative($days, 1) . " ) " . $daytype . $bel . " " . $dn . $rub . " ";
    }

    if ( $hrs > 0 ) {
        $kp   = "ов";
        $temp = $hrs % 10;
        if ( floor(  ( $hrs % 100 ) / 10 ) != 1 ) {
            if ( $temp == 1 ) {
                $kp = "";
            } elseif ( $temp >= 2 && $temp <= 4 ) {
                $kp = "а";
            }
        }
        if ( $hrs > 0 && $days == 0 ) {
            $skob = ")";
            $n    = "";
        } else {
            $skob = "";
        }

        if ( $hrs > 0 ) {
            $s .= "$hrs$skob час$kp";
        }
    }

    //теперь сделать первую букву заглавной
    // if ($days>0 || $grands>0 || $millions>0 || $billions>0) {
    //     $cnt=0; while($s[$cnt]==" ") $cnt++;
    //     $s[$cnt] = chr( ord($s[$cnt])- 32 );
    // }

    if ( stristr( $subject, "==" ) ) {
        $s = str_replace( "==", "", $subject );
        return $s;
    }

    return $n . " (" . $s;
}

// создает строковое представление суммы. Например $n = 123.10
// результат будет "Сто двадцать три рубля 10 копеек"
function SumToBYNRussian( $NumberOrString ) {
    //разделить сумма на разряды: единицы, тысячи, миллионы, миллиарды (больше миллиардов не проверять :) )
    $NumberOrString = str_replace( " ", "", $NumberOrString );
    $NumberOrString = str_replace( "", ".", $NumberOrString );

    $n = _formatPrice( $NumberOrString, $rval = 2, $dec = ".", $term = "" );

    $billions = floor( $n / 1000000000 );
    $millions = floor(  ( $n - $billions * 1000000000 ) / 1000000 );
    $grands   = floor(  ( $n - $billions * 1000000000 - $millions * 1000000 ) / 1000 );
    $roubles  = floor(  ( $n - $billions * 1000000000 - $millions * 1000000 - $grands * 1000 ) ); //$n % 1000;

    //копейки
    $kop = round( $n * 100 - round( floor( $n ) * 100 ) );
    if ( $kop < 10 ) {
        $kop = "0" . (string)$kop;
    }

    $s = "";

    if ( $billions > 0 ) {
        $t    = "ов";
        $temp = $billions % 10;
        if ( floor(  ( $billions % 100 ) / 10 ) != 1 ) {
            if ( $temp == 1 ) {
                $t = "";
            } elseif ( $temp >= 2 && $temp <= 4 ) {
                $t = "а";
            }
        }
        $s .= num_to_russian_nominative( $billions, 1 ) . " миллиард$t ";
    }

    if ( $millions > 0 ) {
        $t    = "ов";
        $temp = $millions % 10;
        if ( floor(  ( $millions % 100 ) / 10 ) != 1 ) {
            if ( $temp == 1 ) {
                $t = "";
            } elseif ( $temp >= 2 && $temp <= 4 ) {
                $t = "а";
            }
        }
        $s .= num_to_russian_nominative( $millions, 1 ) . " миллион$t ";
    }

    if ( $grands > 0 ) {
        $t    = "";
        $temp = $grands % 10;
        if ( floor(  ( $grands % 100 ) / 10 ) != 1 ) {
            if ( $temp == 1 ) {
                $t = "а";
            } elseif ( $temp >= 2 && $temp <= 4 ) {
                $t = "и";
            }
        }
        $s .= num_to_russian_nominative( $grands, 0 ) . " тысяч$t ";
    }

    if ( $roubles > 0 || $grands > 0 || $millions > 0 || $billions > 0 ) {
        $rub  = "ей";
        $bel  = "их";
        $temp = $roubles % 10;
        if ( floor(  ( $roubles % 100 ) / 10 ) != 1 ) {
            if ( $temp == 1 ) {
                $rub = "ь";
                $bel = "ий";
            } elseif ( $temp >= 2 && $temp <= 4 ) {
                $rub = "я";
                $bel = "их";
            }
        }
    } //ioiuoiiuoiu

    if ( $roubles > 0 || $grands > 0 || $millions > 0 || $billions > 0 ) {
        $s .= num_to_russian_nominative( $roubles, 1 ) . " белорусск" . $bel . " рубл" . $rub . " ";
    }

    if ( $kop > 0 ) {
        $kp   = "ек";
        $temp = $kop % 10;
        if ( floor(  ( $kop % 100 ) / 10 ) != 1 ) {
            if ( $temp == 1 ) {
                $kp = "йка";
            } elseif ( $temp >= 2 && $temp <= 4 ) {
                $kp = "йки";
            }
        }
        if ( $kop > 0 ) {
            $s .= "$kop копе$kp";
        }
    }

    // теперь сделать первую букву заглавной
    $s = mb_ucfirst( $s, $encoding = 'UTF-8' );

    return $s;
}

function invoiceBYformat( $price = 1, $UnitSymbol = "", $dec = 2 ) {
    $res = _formatPrice( $price, $dec, ".", " " );
    return $res . $UnitSymbol;
}
function invoiceUSDformat( $price = 1, $UnitSymbol = "", $dec = 4 ) {
    $res = _formatPrice( $price, $dec, ".", "" );
    return $UnitSymbol . $res;
}

function invoiceSelectLastInvoiceID( $orderID ) {
    $sql    = "SELECT `invoiceID` FROM `" . INVOICES_TABLE . "` WHERE `orderID` = '" . $orderID . "' ORDER BY `update_time` DESC, `invoiceID` DESC LIMIT 1";
    $q      = db_query( $sql );
    $res    = db_fetch_assoc( $q );
    $result = $res["invoiceID"];

    return (int)$result;
}

function invoiceSelectInvoicesModule( $orderID, $moduleID = DEFAULT_PAYMENT_MODULE_ID ) {
    $sql = "SELECT * FROM `" . INVOICES_TABLE . "` WHERE `orderID` = '" . $orderID . "' AND `module_id` = '" . $moduleID . "' ORDER BY `update_time` DESC, `invoiceID` DESC";
    $q   = db_query( $sql );
    $res = array();
    while ( $row = db_fetch_assoc( $q ) ) {
        $res[] = $row;
    }
    $result = $res;
    return $result;
}

function invoiceDeleteInvoicesModule( $orderID, $moduleID = DEFAULT_PAYMENT_MODULE_ID ) {
    $sql = "Delete FROM `" . INVOICES_TABLE . "` WHERE (`orderID` = '" . $orderID . "') AND (`module_id` = '" . $moduleID . "')";
    $q   = db_query( $sql );
    $res = array();
    while ( $row = db_fetch_assoc( $q ) ) {
        $res[] = $row;
    }
    $result = $res;
    return $result;
}

function invoiceDeleteInvoice( $invoiceID ) {
    $sql = "Delete FROM `" . INVOICES_TABLE . "` WHERE `invoiceID`=" . (int)$invoiceID;
    $q   = db_query( $sql );
    if ( $q ) {
        return $invoiceID;
    } else {
        return null;
    }
}

function invoiceGet( $invoiceID, $fields_list = "" ) {

    $invoiceID = (int)$invoiceID;
    if ( $field_list == "*" || $field_list == "" || is_null( $field_list ) ) {
        $sql = "SELECT * FROM `" . INVOICES_TABLE . "` WHERE `invoiceID` = $invoiceID LIMIT 1";
    } else {
        $sql = "SELECT {$fields_list} FROM `" . INVOICES_TABLE . "` WHERE `invoiceID` = $invoiceID LIMIT 1";
    }

    $q      = db_query( $sql );
    $result = db_fetch_assoc( $q );
    // dd($result);
    return $result;
}

function invoiceAddNewInvoice( $new_data = NULL ) {
    $default_data = array();
    global $EMPTY_INVOICE;
    $default_data = $EMPTY_INVOICE;

    $temp_invoiceID                  = ( isset( $new_data["invoiceID"] ) ) ? $new_data["invoiceID"] : $default_data["invoiceID"];                                                    //invoiceID (0)
    $temp_invoice_description        = ( isset( $new_data["invoice_description"] ) ) ? $new_data["invoice_description"] : $default_data["invoice_description"];                      //invoice_description (1)
    $temp_invoice_time               = ( isset( $new_data["invoice_time"] ) ) ? $new_data["invoice_time"] : get_current_time();                                                      //invoice_time (2)
    $temp_contract_time              = ( isset( $new_data["contract_time"] ) ) ? $new_data["contract_time"] : get_current_time();                                                    //contract_time (3)
    $temp_module_id                  = ( isset( $new_data["module_id"] ) ) ? $new_data["module_id"] : $default_data["module_id"];                                                    //module_id (4)
    $temp_sellerID                   = ( isset( $new_data["sellerID"] ) ) ? $new_data["sellerID"] : $default_data["sellerID"];                                                       //sellerID (5)
    $temp_buyerID                    = ( isset( $new_data["buyerID"] ) ) ? $new_data["buyerID"] : $default_data["buyerID"];                                                          //buyerID (6)
    $temp_orderID                    = ( isset( $new_data["orderID"] ) ) ? $new_data["orderID"] : $default_data["orderID"];                                                          //orderID (7)
    $temp_contractID                 = ( isset( $new_data["contractID"] ) ) ? $new_data["contractID"] : $default_data["contractID"];                                                 //contractID (8)
    $temp_CID                        = ( isset( $new_data["CID"] ) ) ? $new_data["CID"] : $default_data["CID"];                                                                      //CID (9)
    $temp_currency_rate              = ( isset( $new_data["currency_rate"] ) ) ? $new_data["currency_rate"] : $default_data["currency_rate"];                                        //currency_rate (10)
    $temp_purposeID                  = ( isset( $new_data["purposeID"] ) ) ? $new_data["purposeID"] : $default_data["purposeID"];                                                    //purposeID (11)
    $temp_fundingID                  = ( isset( $new_data["fundingID"] ) ) ? $new_data["fundingID"] : $default_data["fundingID"];                                                    //fundingID (12)
    $temp_DeliveryType               = ( isset( $new_data["DeliveryType"] ) ) ? $new_data["DeliveryType"] : $default_data["DeliveryType"];                                           //DeliveryType (13)
    $temp_deliveryFrom               = ( isset( $new_data["deliveryFrom"] ) ) ? $new_data["deliveryFrom"] : $default_data["deliveryFrom"];                                           //deliveryFrom (14)
    $temp_deliveryTo                 = ( isset( $new_data["deliveryTo"] ) ) ? $new_data["deliveryTo"] : $default_data["deliveryTo"];                                                 //deliveryTo (15)
    $temp_delivery_termin            = ( isset( $new_data["delivery_termin"] ) ) ? $new_data["delivery_termin"] : $default_data["delivery_termin"];                                  //delivery_termin (16)
    $temp_PaymentType                = ( isset( $new_data["PaymentType"] ) ) ? $new_data["PaymentType"] : $default_data["PaymentType"];                                              //PaymentType (20)
    $temp_payment_prepay             = ( isset( $new_data["payment_prepay"] ) ) ? $new_data["payment_prepay"] : $default_data["payment_prepay"];                                     //payment_prepay (21)
    $temp_payment_transactions_count = ( isset( $new_data["payment_transactions_count"] ) ) ? $new_data["payment_transactions_count"] : $default_data["payment_transactions_count"]; //payment_transactions_count (22)
    $temp_payment_firstrpay_termin   = ( isset( $new_data["payment_firstrpay_termin"] ) ) ? $new_data["payment_firstrpay_termin"] : $default_data["payment_firstrpay_termin"];       //payment_firstrpay_termin (23)
    $temp_payment_fullpay_termin     = ( isset( $new_data["payment_fullpay_termin"] ) ) ? $new_data["payment_fullpay_termin"] : $default_data["payment_fullpay_termin"];             //payment_fullpay_termin (24)
    $temp_stampsBYTE                 = ( isset( $new_data["stampsBYTE"] ) ) ? $new_data["stampsBYTE"] : $default_data["stampsBYTE"];                                                 //stampsBYTE (29)
    $temp_showToUser                 = ( isset( $new_data["showToUser"] ) ) ? $new_data["showToUser"] : $default_data["showToUser"];                                                 //showToUser (30)

    $new_invoice_description        = xToText( trim( $temp_invoice_description ) ); //invoice_description (1)
    $new_invoice_time               = xEscSQL( trim( $temp_invoice_time ) );        //invoice_time (2)
    $new_contract_time              = xEscSQL( trim( $temp_contract_time ) );       //contract_time (3)
    $new_module_id                  = (int)$temp_module_id;                    //module_id (4)
    $new_sellerID                   = (int)$temp_sellerID;                     //sellerID (5)
    $new_buyerID                    = (int)$temp_buyerID;                      //buyerID (6)
    $new_orderID                    = (int)$temp_orderID;                      //orderID (7)
    $new_contractID                 = (int)$temp_contractID;                   //contractID (8)
    $new_CID                        = (int)$temp_CID;                          //CID (9)
    $new_currency_rate              = (double)$temp_currency_rate;             //currency_rate (10)
    $new_purposeID                  = xToText( trim( $temp_purposeID ) );           //purposeID (11)
    $new_fundingID                  = xToText( trim( $temp_fundingID ) );           //fundingID (12)
    $new_DeliveryType               = xToText( trim( $temp_DeliveryType ) );        //DeliveryType (13)
    $new_deliveryFrom               = xEscSQL( trim( $temp_deliveryFrom ) );        //deliveryFrom (14)
    $new_deliveryTo                 = xEscSQL( trim( $temp_deliveryTo ) );          //deliveryTo (15)
    $new_delivery_termin            = xToText( trim( $temp_delivery_termin ) );     //delivery_termin (16)
    $new_PaymentType                = xToText( trim( $temp_PaymentType ) );         //PaymentType (20)
    $new_payment_prepay             = (float)$temp_payment_prepay;             //payment_prepay (21)
    $new_payment_transactions_count = (int)$temp_payment_transactions_count;   //payment_transactions_count (22)
    $new_payment_firstrpay_termin   = (int)$temp_payment_firstrpay_termin;     //payment_firstrpay_termin (23)
    $new_payment_fullpay_termin     = (int)$temp_payment_fullpay_termin;       //payment_fullpay_termin (24)
    $new_stampsBYTE                 = (int)$temp_stampsBYTE;                   //stampsBYTE (29)
    $new_showToUser                 = (int)$temp_showToUser;                   //showToUser (30)

    $sql_field_list = sqlGetFieldsNamesOfTable( INVOICES_TABLE, 12, "invoiceID" );

    $VALUES = "'$new_orderID','$new_invoice_description', '$new_invoice_time', '$new_contract_time', '$new_module_id', '$new_sellerID', '$new_buyerID',  '$new_contractID', '$new_CID', '$new_currency_rate', '$new_purposeID', '$new_fundingID', '$new_DeliveryType', '$new_deliveryFrom', '$new_deliveryTo', '$new_delivery_termin',  '$new_PaymentType', '$new_payment_prepay', '$new_payment_transactions_count', '$new_payment_firstrpay_termin', '$new_payment_fullpay_termin',  '$new_stampsBYTE', '$new_showToUser', NOW(),0,0";

    $sql = "INSERT INTO `" . INVOICES_TABLE . "` ( " . $sql_field_list . " ) VALUES ( " . $VALUES . " );";

    $res = db_query( $sql );

    return db_insert_id();
}

// ant
// *****************************************************************************
// Purpose  get all payment methods
// Inputs
// Remarks
// Returns  nothing
function SelectAllPaymentMethods( $enabledOnly = true, $hideTender = true ) {
    $whereClause = "";
    if ( $enabledOnly ) {
        $whereClause = " WHERE Enabled=1 ";
        if ( $hideTender ) {
            $whereClause .= " AND Name NOT LIKE '%tender%' AND Name NOT LIKE '%тендер%' ";
        }
    } else {
        $whereClause = "";
        if ( $hideTender ) {
            $whereClause .= " WHERE Name NOT LIKE '%tender%' AND Name NOT LIKE '%тендер%' ";
        }
    }

    $q = db_query( "SELECT PID, Name, description, Enabled, sort_order,  " .
        " email_comments_text, module_id, calculate_tax FROM " .
        PAYMENT_TYPES_TABLE . " " . $whereClause .
        " ORDER BY sort_order" );
    $data = array();
    while ( $row = db_fetch_row( $q ) ) {
        // $row["ShippingMethodsToAllow"] = _getShippingMethodsToAllow( $row["PID"] );
        $row["hideTender"] = (string)$hideTender;
        $data[]            = $row;
    }

    $result = $data;
    // debugfile($result,"SelectAllPaymentMethods");
    return $result;
}

function ModuleError( $ERROR, $DIE = true, $STATUS = 500 ) {
    consoleRU( get_current_time() . " $STATUS " . $ERROR );
    header_status( $STATUS );
    header( "Content-type: text/html; charset=" . DEFAULT_CHARSET_HTML );
    if ( $DIE ) {
        die( " $STATUS " . $ERROR );
    } else {
        echo ( " $STATUS " . $ERROR . "<br>" );
    }
}

function invoiceGetModuleSettingValue( $module_id = DEFAULT_PAYMENT_MODULE_ID, $settings_constant_name = "PM_INVOICE_STRING" ) {
    $_getSettingOptionValue = NULL;
    try {
        $_getSettingOptionValue = _getSettingOptionValue( $settings_constant_name . "_" . $module_id );
        return $_getSettingOptionValue;
    } catch ( Exception $e ) {
        header( $_SERVER["SERVER_PROTOCOL"] . ' 500 Server Error' );
        header( 'Status:  500 Server Error' );
        echo $e->getMessage();
        return false;
    }

}
function invoiceUpdate__module_id( $New_moduleID = DEFAULT_PAYMENT_MODULE_ID, $orderID, $invoiceID ) {
    $sql = "UPDATE " . INVOICES_TABLE . " SET module_id=" . (int)$New_moduleID . " WHERE orderID=" . (int)$orderID . " AND invoiceID=" . (int)$invoiceID;
    db_query( $sql );
}

function invoiceGetOrderData( $orderID ) {
    # code...
    $res = array();
    $q   = db_query( "SELECT count(*) FROM " . ORDERS_TABLE . " WHERE orderID=" . (int)$orderID );
    $r   = db_fetch_row( $q );
    if ( $r[0] != 0 ) {
        $q                  = db_query( "SELECT orderID, customerID, statusID, companyID, order_time,order_amount, order_aka FROM " . ORDERS_TABLE . " WHERE orderID=" . (int)$orderID );
        $row                = db_fetch_assoc( $q );
        $res                = $row;
        $res["status_name"] = ostGetOrderStatusName( (int)$row["statusID"] );
        $res["error"]       = NULL;

    } else {
        $res["error"] = "Нет в БД";

    }
    // debugfile( $res, "invoiceGetOrderData");
    $result = $res;
    return $result;
}

function htmlInvoicesForThisOrderID( $primaryID, $InvoicesForThisOrderID, $type = 1 ) {
    $html                      = "";
    $other_invoiceedit_buttons = "";
    if ( $type == 1 ) {
        if ( count( $InvoicesForThisOrderID ) > 1 ) {
            $html .= "<h6>Этот заказ встречается в следующих счетах</h6>";
            $html .= "<div class='btn-toolbar'>";
            foreach ( $InvoicesForThisOrderID as $key => $value ) {
                # code...
                $html .= '<a type="button" class="btn ' . (  ( $primaryID == $value["invoiceID"] ) ? ' btn-info active' : 'btn-default' ) . ' btn-xs" title="Редактировать инвойс" aria-action="invoicesDT" href="?dpt=custord&amp;sub=invoices&amp;edit=' . $value["invoiceID"] . '"><span class="text-white glyphicon glyphicon-edit"></span>&nbsp;ID&nbsp;' . ( $value["invoiceID"] ) . '</a>';
            }

            $html .= "</div>";
        } elseif ( count( $InvoicesForThisOrderID ) == 1 ) {
            $html = "<h6>Этот заказ встречается только в этом счете</h6>";
        } else {
            $html = "<h6>Этот заказ НЕ встречается ни в одном счете</h6>";
        }
    }
    if ( $type == 2 ) {
        if ( count( $InvoicesForThisOrderID ) > 1 ) {
            foreach ( $InvoicesForThisOrderID as $key => $value ) {
                $other_invoiceedit_buttons .= <<<HTML
                <li class="{$hidden_class}">
                <a
                id="edit_invoice_{$value['invoiceID']}"
                class="{$active}"
                href="?dpt=custord&amp;sub=invoices&amp;edit={$value['invoiceID']}"
                target="blank"
                title="Редактировать инвойс"
                >
                <span class="{$text_class}">редактировать "$temp_id" PDF-cчёт<span class="glyphicon glyphicon-edit"></span></span>
                </a></li>
HTML;
            }
        }
        $htmlInvoice_buttons = <<<HTML
                    <!-- Split button -->
                    <div class="btn-group">
                   -- {$default_invoice_button} --
                      <button type="button" class="btn btn-default dropdown-toggle {$hide_variants}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                      </button>
                      <ul class="dropdown-menu">
                        {$other_invoiceedit_buttons}
                      </ul>
                    </div>
HTML;
    }
    return $html;
}

?>