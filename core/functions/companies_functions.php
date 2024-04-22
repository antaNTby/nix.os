<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2019-01-22       #
#          http://nixby.pro              #
##########################################

/*
SELECT count(*) as re,`company_companyname`,`company_unp`,`company_adress`,`company_bank`,`company_representative`,`company_representative_reason`,`company_data`
FROM `ant__module_payment_invoice_jur`
GROUP BY `company_unp`
HAVING `company_unp`
order by re desc
LIMIT 1000
 */

function companCheckUNP( $string ) {
    $temp = preg_replace( "/\D/", "", $string );
    if ( strlen( $temp ) != 9 ) {
        return false;
    } else {
        return true;
    }
}

function companParseUNP(
    $string,
    $multiUNP = false
) {
    $line        = $string;
    $company_unp = "000000001";
    if ( $line == "" ) {
        return $company_unp;
    }

    preg_match_all( '/((УНП|Унп|унп)|(\s?))((\d{3} \d{3} \d{3})|(\d{9}))(.|,|\s)/', $line, $Matches ); //
    if ( is_array( $Matches[0] ) && count( $Matches[0] ) > 0 ) {
        $temp = preg_replace( "/\D/", "", $Matches[0][0] );
        if ( count( $Matches[0] ) == 1 ) {
            $company_unp = (int)$temp;
        } else {
            $company_unp = '999999999';
        }
    } else {
        $company_unp = '000000000';
    }

    if ( $multiUNP ) {
        $UNPs = array();
        foreach ( $Matches[0] as $key => $value ) {
            $UNPs["{$key}"][] = preg_replace( "/\D/", "", $value );
        }
        return $UNPs;
    }

    return $company_unp;
}

function companParceBankInformation(
    $string,
    $params,
    $PARSE_IBAN = true,
    $PARSE_OLD = true
) {
    $result = array();
    // $result["IBAN"] = array();
    $result["company_unp"] = $params["company_unp"];
    $result["order"]       = $params["orderID"];
    $result["order_time"]  = $params["order_time"];

    $line = ( $string );

    $line = preg_replace( "/BY73BPSB3012109484019933000/", "BY73 BPSB 3012 1094 8401 9933 0000", $line );
    $line = preg_replace( "/BY73BPSB3012109484019933000/", "BY73 BPSB 3012 1094 8401 9933 0000", $line );
    $line = preg_replace( "/BY68AKBB3604900002230000000/", "BY68 AKBB 3604 9000 0223 0000 0000", $line );
    $line = preg_replace( "/BY31BPSB3012104520119330000/", "BY31 BPSB 3012 1045 2011 9330 0000", $line );
    $line = preg_replace( "/BY70SAOMA30120007310101000933/", "BY70 SOMA 3012 0007 3101 0100 0933", $line );

    $line = preg_replace( "/AKBBBY21Х/", "AKBBBY2X", $line );
    $line = preg_replace( "/АKBBBY2X/", "AKBBBY2X", $line );
    $line = preg_replace( "/АКBBBY2X/", "AKBBBY2X", $line );
    $line = preg_replace( "/AKBB\sBY\s2Х/", "AKBBBY2X", $line );
    $line = preg_replace( "/AKBB BY 2Х/", "AKBBBY2X", $line );
    $line = preg_replace( "/АКВВВY2X/", "AKBBBY2X", $line );
    $line = preg_replace( "/AKBBBY2Х/", "AKBBBY2X", $line );
    $line = preg_replace( "/AKBBBY2х/", "AKBBBY2X", $line );
    $line = preg_replace( "/AKBBY21614/", "AKBBBY21614", $line );
    $line = preg_replace( "/AKBBВY21400/", "AKBBBY21400", $line );
    $line = preg_replace( "/AKBBVY21400/", "AKBBBY21400", $line );
    $line = preg_replace( "/AKBBВY21400/", "AKBBBY21400", $line );
    $line = preg_replace( "/АКВВВY21511/", "AKBBBY21511", $line );

    $line = preg_replace( "/ВУ15AKBB/", "BY15AKBB", $line );
    $line = preg_replace( "/ВУ13AKBB/", "BY15AKBB", $line );
    $line = preg_replace( "/В\s*Y13AKBB/", "BY15AKBB", $line );

    $line = preg_replace( "/ZEPTBY2/", "ZEPTBY2X", $line );

    $line = preg_replace( "/PJCBBY2Х/", "PJCBBY2X", $line );

    $line = preg_replace( "/BAPBBY2Х/", "BAPBBY2X", $line );

//OR ( strtotime( $param["order_time"] ) > strtotime( "2017-07-04 00:00:00" ) )
    if ( $PARSE_IBAN ) {

        // consolelog($params["order_time"]);

        ##  ищем IBAN
        preg_match_all( '/BY\d{2}(\s*\w{4} \d{4} \w{4} \w{4} \w{4} \w{4}|\w{4}\d{4}\w{16}|-\d{4}-\d{3}\w-\w{4}-\w{4}-\w{4}-\w{4})/', $line, $Matches ); //IBAN_all
        if ( is_array( $Matches[0] ) && count( $Matches[0] ) > 0 ) {
            // consolelog( $Matches[0] );
            foreach ( $Matches[0] as $key => $value ) {
                $result["IBAN"]["ACCOUNT"][] = preg_replace( "/[\s-]/", "", $value );
            }
        }

        ##  ищем SWIFT_BIC PJCBBY2X
        preg_match_all( '/ABIGBY25|ABLTBY22|AEBKBY2X|AKBBBY21100|AKBBBY21113|AKBBBY21121|AKBBBY21200|AKBBBY21214|AKBBBY21215|AKBBBY21216|AKBBBY21300|AKBBBY21302|AKBBBY21312|AKBBBY21317|AKBBBY21400|AKBBBY21402|AKBBBY21413|AKBBBY21500|AKBBBY21510|AKBBBY21511|AKBBBY21514|AKBBBY21527|AKBBBY21529|AKBBBY21601|AKBBBY21612|AKBBBY21614|AKBBBY21620|AKBBBY21633|AKBBBY21700|AKBBBY21703|AKBBBY21712|AKBBBY21714|AKBBBY21802|AKBBBY2X|ALFABY2X|ATOMBY25|BAPBBY21401|BAPBBY22424|BAPBBY23912|BAPBBY24457|BAPBBY25942|BAPBBY27458|BAPBBY2X|BBTKBY2X|BCSXBY22|BELBBY2X|BISCBY25|BITMBY25|BLBBBY2X|BLNBBY2X|BPSBBY2X|BRRBBY2X|CASHBY25|EABRKZKA|EUBKBY2X|GTBNBY22|HNRBBY2X|INEARUMM|IPBKBY2X|IRJSBY22|LOJSBY22|MMBNBY22|MTBKBY22|NBRBBY2X|OLMPBY2X|PJCBBY2X|POISBY2X|REDJBY22|RSHNBY2X|SLANBY22|SOMABY22|SSISBY25|TECNBY22|UNBSBY2X|ZEPTBY2X/', $line, $Matches ); //IBAN_all
        if ( is_array( $Matches[0] ) && count( $Matches[0] ) > 0 ) {
            // consolelog( $Matches[0] );
            foreach ( $Matches[0] as $key => $value ) {
                $result["IBAN"]["SWIFT_BIC"][] = preg_replace( "/[\s-]/", "", $value );
            }
        }

        if ( $params["company_unp"] == "600048566" ) {
            $result["IBAN"]["SWIFT_BIC"] = "AKBBBY2X";
        }
        if ( $params["company_unp"] == "600040432" ) {
            $result["IBAN"]["SWIFT_BIC"] = "BPSBBY2X";
        }
        if ( !count( $result["IBAN"]["ACCOUNT"] ) ) {
            $result["errors"][] = "IBAN account"; // xEscSQL( $string ); // ошибка в счете или в банке
        }
        if ( !count( $result["IBAN"]["SWIFT_BIC"] ) ) {
            $result["errors"][] = "SWIFT_BIC"; // xEscSQL( $string ); // ошибка в счете или в банке
        }
        if ( count( $result["IBAN"]["ACCOUNT"] ) != count( $result["IBAN"]["SWIFT_BIC"] ) ) {
            $result["errors"][] = "IBAN conformity"; // xEscSQL( $string ); // количество р\с не соответствует колическтву кодов банка
        }
    }
// OR ( !count( $result["IBAN"] ) AND strtotime( $param["order_time"] ) < strtotime( "2017-07-04 00:00:00" ) )
    if ( $PARSE_OLD ) {
        // consolelog("OLD ".$params["order_time"]);

##  ищем Р\С
        preg_match_all( '/(\d{13})/', $line, $Matches ); //МФО
        if ( is_array( $Matches[0] ) && count( $Matches[0] ) > 0 ) {
            // consolelog( $Matches[0] );
            foreach ( $Matches[0] as $key => $value ) {
                $result["OLD"]["account"][] = preg_replace( "/[\s-]/", "", $value );
            }
        }

##  ищем МФО 151501915 151501958
        preg_match_all( '/150501004|150501171|150501196|150501201|150501207|150501210|150501212|150501237|150501242|150501245|150501246|150501252|150501274|150501282|150501302|150501309|150501313|150501344|150501401|150501402|150501404|150501405|150501406|150501407|150501408|150501410|150501411|150501413|150501415|150501421|150501426|150501429|150501617|150501618|150501619|150501620|150501622|150501623|150501626|150501630|150501631|150501823|150501854|150501976|150505031|150801006|150801169|150801206|150801208|150801233|150801238|150801258|150801259|150801277|150801304|150801306|150801307|150801308|150801325|150801412|150801414|150801416|150801417|150801418|150801419|150801424|150801425|150801427|150801428|150801430|150801434|150801437|150801439|150801442|150801444|150801635|150801637|150801638|150801640|150801641|150801644|150801647|150801648|150801649|150801652|150801654|150801655|150801659|150801660|150801701|150801708|150801762|150801794|150801848|150801855|150801993|151501003|151501109|151501131|151501178|151501205|151501213|151501221|151501267|151501275|151501341|151501348|151501354|151501356|151501360|151501373|151501661|151501663|151501664|151501670|151501672|151501673|151501674|151501675|151501676|151501677|151501678|151501680|151501681|151501682|151501683|151501685|151501758|151501768|151501796|151501853|151501912|151501920|151501923|151501925|151501926|151501928|151501932|151501935|151501936|151501937|151501938|151501939|151501940|151501945|151501946|151501948|151501949|151501958|151501959|152101002|152101194|152101199|152101204|152101216|152101219|152101278|152101279|152101317|152101332|152101335|152101337|152101343|152101423|152101432|152101436|152101438|152101441|152101443|152101445|152101446|152101447|152101449|152101450|152101453|152101457|152101460|152101468|152101471|152101500|152101504|152101505|152101689|152101690|152101691|152101692|152101695|152101696|152101698|152101699|152101705|152101752|152101852|152101857|153001007|153001050|153001107|153001108|153001110|153001111|153001117|153001123|153001130|153001136|153001141|153001144|153001145|153001163|153001175|153001180|153001182|153001183|153001203|153001209|153001211|153001215|153001217|153001220|153001226|153001240|153001251|153001253|153001254|153001260|153001266|153001270|153001272|153001273|153001276|153001281|153001288|153001296|153001321|153001331|153001333|153001334|153001338|153001339|153001342|153001345|153001346|153001357|153001358|153001362|153001369|153001386|153001482|153001487|153001510|153001513|153001514|153001515|153001516|153001517|153001518|153001519|153001520|153001522|153001523|153001528|153001529|153001530|153001601|153001603|153001608|153001614|153001704|153001717|153001720|153001725|153001728|153001731|153001735|153001736|153001737|153001739|153001741|153001742|153001749|153001755|153001763|153001764|153001765|153001769|153001770|153001782|153001795|153001800|153001808|153001810|153001812|153001815|153001820|153001840|153001888|153001898|153001901|153001902|153001903|153001904|153001907|153001908|153001909|153001911|153001913|153001914|153001915|153001916|153001917|153001918|153001919|153001921|153001922|153001924|153001929|153001942|153001960|153001963|153001964|153001969|153004030|153005042|153006024|153007041|153801008|153801114|153801162|153801202|153801214|153801232|153801271|153801326|153801329|153801355|153801422|153801452|153801458|153801461|153801463|153801465|153801466|153801467|153801469|153801470|153801472|153801474|153801475|153801476|153801478|153801485|153801486|153801497|153801536|153801537|153801539|153801541|153801543|153801546|153801548|153801549|153801550|153801554|153801555|153801558|153801561|153801712|153801760|153801856|153801905|153001898|153001704|150501246|150501237|150501854|150801635|150801660|150801647|150801648|151501661|151501664|151501673|151501678|152101752|152101689|152101696|153001601|153001603|153001815|153001614|153001254|153001720|153001769|153001810|153001520|153001523|153001969|153801536|153801760|153801561|153801546|150501245|153001795|153001270|153001281|150501401|150801424|151501912|152101457|153001942|153801458|153001964|153001333|153001185|153001226|153001777|153001739|153001765|153001369|153001222|153001370|153001888|153001266|153001830|153001111|153001273|153001735|153001141|153001272|153001117|153005042|153001742|153001749|153001782|153001110|153001288|153001108|153001755|153001345|153001182|153001175|153001820/', $line, $Matches ); //МФО
        if ( is_array( $Matches[0] ) && count( $Matches[0] ) > 0 ) {
            // consolelog( $Matches[0] );
            foreach ( $Matches[0] as $key => $value ) {
                $result["OLD"]["MFO"][] = preg_replace( "/[\s-]/", "", $value );
            }
        }

##  ищем код банка
        preg_match_all( '/(код|код\.|Код|БИК|МФО|КОД|код банка)\s*(303|898|704|246|237|854|635|660|647|648|661|664|673|678|752|689|696|601|603|815|614|254|720|769|810|520|523|969|536|760|561|546|245|795|270|281|401|424|912|457|942|458|964|333|185|226|50|777|739|765|369|222|370|112|888|266|830|111|273|735|141|272|117|42|742|749|782|110|288|108|755|345|182|175|820|764|226)/', $line, $Matches ); //МФО
        # preg_match_all( '/(код|код\.|Код|БИК|МФО|КОД|код банка)\s?(153001|150501|150801|151501|153801|152101){0,1}(303|898|704|246|237|854|635|660|647|648|661|664|673|678|752|689|696|601|603|815|614|254|720|769|810|520|523|969|536|760|561|546|245|795|270|281|401|424|912|457|942|458|964|333|185|226|50|777|739|765|369|222|370|112|888|266|830|111|273|735|141|272|117|42|742|749|782|110|288|108|755|345|182|175|820|764|226)/', $line, $Matches ); //МФО
        if ( is_array( $Matches[2] ) && count( $Matches[2] ) > 0 ) {
            // consolelog( $Matches[0] );
            foreach ( $Matches[2] as $key => $value ) {
                $result["OLD"]["bankcode"][] = preg_replace( "/[\s-]/", "", $value );
            }
        }

        if ( !count( $result["OLD"]["account"] ) ) {
            $result["errors"][] = "OLD account"; // xEscSQL( $string ); // ошибка в счете или в банке
        }
        if ( !count( $result["OLD"]["MFO"] ) ) {
            $result["errors"][] = "MFO"; // xEscSQL( $string ); // ошибка в счете или в банке
        }
        if ( isset( $result["OLD"]["MFO"] ) && ( count( $result["OLD"]["account"] ) != count( $result["OLD"]["MFO"] ) ) || ( isset( $result["OLD"]["bankcode"] ) && count( $result["OLD"]["account"] ) != count( $result["OLD"]["bankcode"] ) ) ) {
            $result["errors"][] = "OLD conformity"; // xEscSQL( $string ); // количество р\с не соответствует колическтву кодов банка
        }

    }

    if ( isset( $result["errors"] ) ) {
        $result["utf8"]          = ( $string );
        $result["translitError"] = ( translitErrorText( $string ) );
        // $result["line"]          = (  ( $line ) );
        // $result["errors"]["translit"]      = xEscSQL( translitText( $string ) );
        // consolelog( $result );
    }

    // consolelog( "=============({$params["orderID"]})============" );
    return $result;
}

function companGetAllCompanies() {

    $q    = db_query( "SELECT * FROM " . COMPANIES_TABLE );
    $data = array();
    while ( $row = db_fetch_assoc( $q ) ) {
        $data[] = $row;
    }

    return $data;
}

function getOptionsForCompanies( $where_clause = "" ) {

    $order_by_clause = " ORDER BY `company_name`,`update_time` DESC,`companyID` DESC";
    $q               = db_query( RN( "SELECT `companyID`,`company_name`,`company_unp`,`company_adress`,`company_bank`,`company_director`,`update_time`
     FROM `" . COMPANIES_TABLE . "` {$where_clause} {$order_by_clause} " ) );
    $data = array();
    while ( $row = db_fetch_assoc( $q ) ) {
        $data[] = $row;
    }
    return $data;
}

function companGetAllCompaniesGrouped( $Field ) {

    $q    = db_query( "SELECT * FROM " . COMPANIES_TABLE . " GROUP BY $Field ORDER BY `companyID`,`company_name`" );
    $data = array();
    while ( $row = db_fetch_assoc( $q ) ) {
        $data[] = $row;
    }

    return $data;
}

function _getCompanyNameOnly( $companyID ) {
    $q   = db_query( "SELECT `company_name` FROM " . COMPANIES_TABLE . " WHERE `companyID` ='$companyID';" );
    $row = db_fetch_assoc( $q );
    $res = $row["company_name"];
    return $res;
}

function companGetOptionsForSelectInput( $Field ) {

    $q    = db_query( "SELECT `companyID`,`company_name`,`company_unp`,`company_okpo`,`update_time` FROM " . COMPANIES_TABLE . " ORDER BY `company_unp`,`$Field`" );
    $data = array();
    while ( $row = db_fetch_assoc( $q ) ) {
        $data[] = $row;
    }

    return $data;
}

function _getCompanyPreview( $company ) {
    $out = "";
    $out .= "<b>УНП " . $company['company_unp'] . "</b>. ";
    if ( strlen( $company['company_okpo'] ) > 2 ) {
        $out .= "<b>ОКПО " . $company['company_okpo'] . "</b>. ";
    }
    $out .= $company['company_adress'] . " <sub>[ " . $company['companyID'] . " ]</sub> ";
    $res = ( $out );
    return $res;
}

function htmlCompanyPreview(
    $company,
    $style = 0
) {

    switch ( $style ) {

        case 0:
            $out = "<div class='compact'>";
            $out .= "УНП <strong>" . $company['company_unp'] . "</strong> : ";
            if ( strlen( $company['company_okpo'] ) > 2 ) {
                $out .= "ОКПО <strong>" . $company['company_okpo'] . "</strong> : ";
            }
            $out .= "<strong>" . $company['company_name'] . "</strong> <sub>[ " . $company['companyID'] . " ]</sub> ";
            $out .= "<em>" . $company['company_adress'] . "</em> <br>";
            $out .= "<button class='btn btn-default btn-xs pull-right' onclick =\"LocateToCompany(" . $company['companyID'] . ")\"><span class='glyphicon glyphicon-edit'></span></button>";
            $out .= "</div>";
            break;

        case 1:
            $out = "";
            $out .= "<b>УНП " . $company['company_unp'] . "</b>. " . $company['company_adress'] . " <sub>[ " . $company['companyID'] . " ]</sub> ";
            break;
        case 2:
            $out = "<div class='compact'>";
            $out .= "УНП <strong>" . $company['company_unp'] . "</strong> ";
            $out .= "<sub>[ " . $company['companyID'] . " ]</sub>" . " <em>" . $company['company_adress'] . "</em>";
            // $out .= "<button class='btn btn-default btn-xs' onclick =\"LocateToCompanies(".$company['companyID'].")\">righclick</button>";
            $out .= "</div>";
            break;

        case 26:
            $out = <<<HTML
УНП {$company["company_unp"]} {$company["company_adress"]} {$company["company_bank"]}
HTML;
            break;

        case 15:
            $out = <<<HTML
{$company["company_name"]} / УНП {$company["company_unp"]}.<br>
{$company["company_adress"]}.
HTML;

            break;
    }
    $res = ( $out );

    return $res;
}

function companUpdateField(
    $companyID,
    $fieldDB,
    $new_value
) {
    $companyID = (int)$companyID;
// debug ($fieldDB);
    $update_time_field = "";
    if ( $fieldDB == "read_only" ) {
        $update_time_field = "";
    } elseif ( $fieldDB == "company_title" ) {
        $update_time_field = "";
    } else {
        $update_time_field = ", `update_time` = NOW()";
    }

    switch ( $fieldDB ) {
        case ( "company_data" ):
            # code...
            $NewFieldValue = xEscSQL( $new_value );
            break;
        case ( "company_admin" ):
            # code...
            $NewFieldValue = xEscSQL( $new_value );
            break;
        case ( "read_only" ):
            # code...
            $NewFieldValue = (int)$new_value;
            break;
        // case ( "company_data" ):
        //     # code...
        //     $NewFieldValue = xEscSQL( $new_value );
        //     break;

        default:
            # code...
            $NewFieldValue = xToText( $new_value );
            break;

    }
    $sql = "UPDATE `" . COMPANIES_TABLE .
    "` SET `$fieldDB` = '" . $NewFieldValue . "'" . $update_time_field . " WHERE `companyID` = " . (int)$companyID . ";";

    $res = db_query( $sql );

    $result            = array();
    $result["SQL"]     = $sql;
    $result["fieldDB"] = $fieldDB;
    $result["result"]  = array( "value" => 1, "message" => "Success Update $fieldDB ID: $companyID" );

    // $result["res"] = $res;

    return $result;
}

// $companyID        = companyID;
// $company_title    = company_title;
// $company_name     = company_name;
// $company_unp      = company_unp;
// $company_adress   = company_adress;
// $company_bank     = company_bank;
// $company_contacts = company_contacts;
// $company_director = company_director;
// $company_data     = company_data;
// $company_admin     = company_admin;
// $read_only        = read_only;
// $update_time      = update_time;

// $companyID        = $new_data["companyID"];
// $company_title    = xToText( $new_data["company_title"] );
// $company_name     = xToText( $new_data["company_name"] );
// $company_unp      = preg_replace( '/\D/', "", xToText( $new_data["company_unp"] ) );
// $company_adress   = xToText( $new_data["company_adress"] );
// $company_bank     = xToText( $new_data["company_bank"] );
// $company_contacts = xToText( $new_data["company_contacts"] );
// $company_director = xToText( $new_data["company_director"] );
// $company_data     = xEscSQL( $new_data["company_data"] );
// $company_admin     = xEscSQL( $new_data["company_admin"] );
// $read_only        = (int)$new_data["read_only"];
// $update_time      = date( 'Y-m-d H:i:s' );

function companAddNew( $new_data = NULL ) {
    // return $new_data;

    global $EMPTY_COMPANY;
    if ( is_null( $new_data ) ) {
        $new_data = $EMPTY_COMPANY;
    }

    $company_title    = xToText( trim( $new_data["company_title"] ) );
    $company_name     = xToText( trim( $new_data["company_name"] ) );
    $company_unp      = xToText( trim( preg_replace( '/\D/', "", $new_data["company_unp"] ) ) );
    $company_okpo     = xToText( trim( preg_replace( '/\D/', "", $new_data["company_okpo"] ) ) );
    $company_adress   = xToText( trim( $new_data["company_adress"] ) );
    $company_bank     = xToText( trim( $new_data["company_bank"] ) );
    $company_contacts = xToText(  ( $new_data["company_contacts"] ) );
    $company_director = serialize( $new_data["company_director"] );
    $company_data     = xEscSQL( $new_data["company_data"] );
    $company_admin    = xEscSQL( $new_data["company_admin"] );
    $read_only        = (int)$new_data["read_only"];

    // $sql_field_list = "company_title,company_name,company_unp,company_adress,company_bank,company_contacts,company_data,read_only";
    $sql_field_list = "`company_title`, `company_name`, `company_unp`, `company_okpo`, `company_adress`, `company_bank`, `company_contacts`, `company_director`, `company_data`, `company_admin`, `read_only`,`update_time`";

    $sql = "INSERT INTO " . COMPANIES_TABLE . " ( " . $sql_field_list . " ) VALUES ('$company_title', '$company_name', '$company_unp', '$company_okpo', '$company_adress', '$company_bank', '$company_contacts', '$company_director', '$company_data', '$company_admin', '$read_only', NOW() );";
    db_query( $sql );

    return db_insert_id();
    // return $DUMMY_COMPANY;
}

function companImportFromCSV(
    $file_name = "core/temp/utf8_ company.csv",
    $delimiter = ";"
) {
    return false; // !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    $data = myfgetcsv( $file_name, $delimiter );
    // $data = fgetcsv( $file_name, $delimiter );
    // $data = mb_myfgetcsv( $file_name, $delimiter );
    if ( !count( $data ) ) {
        die( ERROR_CANT_READ_FILE );
    } else {

        $data_size = count( $data );

        for ( $i = 1; $i < $data_size; $i++ ) {

            $new = array();
            $row = array();

            $row = $data[$i];

            $arr_d    = array();
            $arr_d[0] = htmlspecialchars_decode( $row[4] ) . " => именит. падеж. ";
            $arr_d[1] = htmlspecialchars_decode( $row[4] );
            $arr_d[2] = htmlspecialchars_decode( $row[5] );

            $row[0] = str_replace( '&quot;', '"', $row[0] );

            $new["company_title"] = ( "IMPORT " . htmlspecialchars_decode( $row[0] ) );
            $new["company_name"]  = ( htmlspecialchars_decode( $row[0] ) );

            $new["company_unp"] = ( trim( $row[1] ) );

            $new["company_adress"]   = ( htmlspecialchars_decode( $row[2] ) );
            $new["company_bank"]     = ( htmlspecialchars_decode( $row[3] ) );
            $new["company_contacts"] = ( "______________________________" );

            $new["company_director"] = ( $arr_d );

            $new["company_data"]  = ( trim( $row[6] ) );
            $new["company_admin"] = ( trim( $row[6] ) );
            $new["read_only"]     = 0;

            companAddNew( $new );

            // $result=$row;
            $result = $data_size . " records has been imported";
        }

    }
    return $result;
}

function _getCompany( $companyID ) {

    $q   = db_query( "SELECT * FROM `" . COMPANIES_TABLE . "` WHERE `companyID` =" . (int)$companyID );
    $row = db_fetch_assoc( $q );
    return $row;
}


function companyGetCompanyForInvoice( $companyID ) {
// $result=array();
    global $EMPTY_COMPANY;
    $result = array();
    $result = $EMPTY_COMPANY;

    if ( $companyID == 0 ) {
        $result = $EMPTY_COMPANY;
    } else {

        $filelds_list = "`read_only`, `companyID`, `company_title`, `company_name`, `company_unp`, `company_okpo`, `company_adress`, `company_bank`, `company_contacts`, `company_director`, `update_time`";

        $q   = db_query( "SELECT $filelds_list FROM `" . COMPANIES_TABLE . "` WHERE `companyID` =" . (int)$companyID );
        $row = db_fetch_assoc( $q );

        $arr_d = array();
        $arr_d = unserialize( $row["company_director"] );

        $row["company_director0"] = $arr_d[0];
        $row["company_director1"] = $arr_d[1];
        $row["company_director2"] = $arr_d[2];

        $row["company_director_nominative"] = $arr_d[0];
        $row["company_director_genitive"]   = $arr_d[1];
        $row["company_director_reason"]     = $arr_d[2];

        $result = $row;

    }

    return $result;
}

function companLoadCompanyToEdit( $companyID ) {

    $q   = db_query( "SELECT * FROM `" . COMPANIES_TABLE . "` WHERE `companyID` =" . (int)$companyID );
    $row = db_fetch_assoc( $q );

    // $row["company_director"] = ( $row["company_director"] != "" ) ? unserialize( $row["company_director"] ) : array();
    // $res["company_director0"] = $res["company_director"][0];
    // $res["company_director1"] = $res["company_director"][1];
    // $res["company_director2"] = $res["company_director"][2];

    $res = $row;

    $arr_d = array();
    $arr_d = unserialize( $row["company_director"] );

    if ( !is_array( $arr_d ) ) {
        $arr_d    = array();
        $arr_d[0] = $row["company_director"];
        $arr_d[1] = $row["company_director"];
        $arr_d[2] = $row["company_director"];
    }
    $res["company_director"] = $arr_d;

    return $res;
}

function companUpdateCompanyFromPost(
    $companyID,
    $post
) {

    $res = array();
    global $DUMMY_COMPANY;
    $default_company = $DUMMY_COMPANY;

    $q   = db_query( "SELECT `read_only` FROM `" . COMPANIES_TABLE . "` WHERE`companyID` =" . (int)$companyID );
    $row = db_fetch_assoc( $q );

    if ( $row["read_only"] == 1 ) {
        $res = array( "value" => 0, "message" => "Error Update Info: Read Only" );
        return $res;
    }

    $new["company_title"]       = isset( $post["company_title"] ) ? xToText( trim( $post["company_title"] ) ) : xToText( trim( $default_company["company_title"] ) );
    $new["company_name"]        = isset( $post["company_name"] ) ? xToText( trim( $post["company_name"] ) ) : xToText( trim( $default_company["company_name"] ) );
    $new["company_unp"]         = isset( $post["company_unp"] ) ? xToText( trim( $post["company_unp"] ) ) : xToText( trim( $default_company["company_unp"] ) );
    $new["company_okpo"]        = isset( $post["company_okpo"] ) ? xToText( trim( $post["company_okpo"] ) ) : xToText( trim( $default_company["company_okpo"] ) );
    $new["company_adress"]      = isset( $post["company_adress"] ) ? xToText( trim( $post["company_adress"] ) ) : xToText( trim( $default_company["company_adress"] ) );
    $new["company_bank"]        = isset( $post["company_bank"] ) ? xToText( trim( $post["company_bank"] ) ) : xToText( trim( $default_company["company_bank"] ) );
    $new["company_contacts"]    = isset( $post["company_contacts"] ) ? xToText( trim( $post["company_contacts"] ) ) : xToText( trim( $default_company["company_contacts"] ) );
    $new["company_director"][0] = isset( $post["company_director0"] ) ? $post["company_director0"] : $default_company["company_director"][0];
    $new["company_director"][1] = isset( $post["company_director1"] ) ? $post["company_director1"] : $default_company["company_director"][1];
    $new["company_director"][2] = isset( $post["company_director2"] ) ? $post["company_director2"] : $default_company["company_director"][2];

    $new["company_director"] = isset( $new["company_director"] ) ? serialize( $new["company_director"] ) : serialize( $default_company["company_director"] );

    $new["company_data"]  = isset( $post["company_data"] ) ? xEscSQL( trim( $post["company_data"] ) ) : xEscSQL( trim( $default_company["company_data"] ) );
    $new["company_admin"] = isset( $post["company_admin"] ) ? xEscSQL( trim( $post["company_admin"] ) ) : xEscSQL( trim( $default_company["company_admin"] ) );

    $new["read_only"] = ( $post["read_only"] == 1 ) ? 1 : 0;

    $sql = "UPDATE `" . COMPANIES_TABLE . "` SET " .
    "`company_title` = '" . $new["company_title"] . "', " .
    "`company_name` = '" . $new["company_name"] . "', " .
    "`company_unp` = '" . $new["company_unp"] . "', " .
    "`company_okpo` = '" . $new["company_okpo"] . "', " .
    "`company_adress` = '" . $new["company_adress"] . "', " .
    "`company_bank` = '" . $new["company_bank"] . "', " .
    "`company_contacts` = '" . $new["company_contacts"] . "', " .
    "`company_director` = '" . $new["company_director"] . "', " .
    "`company_data` = '" . $new["company_data"] . "', " .
    "`company_admin` = '" . $new["company_admin"] . "', " .
    "`read_only` = '" . $new["read_only"] . "', " .
    "`update_time` = NOW() WHERE `companyID` = " . (int)$companyID . ";";
    $q = db_query( $sql );

    $new["result"] = array( "value" => 1, "message" => "Success Update Info: ID: $companyID" );

    $res = $new;

    return $res;
}

function companDeleteCompany( $companyID ) {

    $q   = db_query( "DELETE FROM `" . COMPANIES_TABLE . "` WHERE `read_only`=0 AND `companyID` =" . (int)$companyID );
    $row = db_fetch_assoc( $q );
    return $companyID;
}

function companDuplicate( $companyID ) {

    $old       = _getCompany( $companyID );
    $companyID = (int)$companyID;

    $new_title    = "Copy of $companyID " . $old["company_name"];
    $new_name     = xToText( trim( $old["company_name"] ) );
    $new_unp      = xToText( trim( $old["company_unp"] ) );
    $new_okpo     = xToText( trim( $old["company_okpo"] ) );
    $new_adress   = xToText( trim( $old["company_adress"] ) );
    $new_bank     = xToText( trim( $old["company_bank"] ) );
    $new_contacts = xToText( trim( $old["company_contacts"] ) );
    $new_director = trim( $old["company_director"] );
    $new_data     = trim( $old["company_data"] );
    $new_admin    = trim( $old["company_admin"] );

    $sql_field_list = "`companyID`, `company_title`, `company_name`, `company_unp`, `company_okpo`, `company_adress`, `company_bank`, `company_contacts`, `company_director`, `company_data`, `company_admin`, `read_only`,`update_time`";

    $sql = "
INSERT INTO `" . COMPANIES_TABLE . "` ( {$sql_field_list} )
SELECT LAST_INSERT_ID(), '{$new_title}', '{$new_name}', '{$new_unp}', '{$new_okpo}', '{$new_adress}', '{$new_bank}', '{$new_contacts}', '{$new_director}', '{$new_data}', '{$new_admin}', '0', NOW()
FROM `" . COMPANIES_TABLE . "`
WHERE `companyID` = $companyID
LIMIT 1;
";
    db_query( $sql );
    return db_insert_id();
}

?>