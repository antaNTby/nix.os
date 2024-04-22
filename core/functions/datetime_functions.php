<?php
##########################################
#        antaNT64pro ShopCMS_UTF8        #
#        © antaNT64pro, 2018-08-23       #
#           http:#nixby.pro              #
##########################################

// require_once("holidaysList.php");
$WORKINGDAYS = array( 1, 2, 3, 4, 5 ); # date format = N (1 = Monday, ...)

$HOLIDAYDAYS = array(
    "*-01-01", // новый год
    "*-01-07",// Рождество
    "*-03-08",// 8 марта
    "*-05-01",// 1 мая
    "*-05-09",// 9 мая
    "*-06-03",// День освобождения Минска
    "*-11-07",// Дегь Октябрьской революции
    "*-12-25",// Рождество каталическое
    #Пасхи
    "2019-04-21",
    "2019-04-28",

    "2020-04-12",
    "2020-04-19",

    "2021-04-04",
    "2021-05-02",

    "2022-04-17",
    "2022-04-24",

    "2023-04-09",
    "2023-04-16",
    "2024-03-31",

    "2024-05-05",
    "2024-04-20",
    #Радуницы
    "2019-05-07",
    "2020-04-28",
    "2021-05-11", //Радуница
    "2022-05-03", //Радуница
    "2023-05-25", //Радуница
); # variable and fixed holidays

$AUXHOLYDAYDAYS = array(
    "2019-05-06",
    "2019-05-08",
    "2019-11-08",

    "2020-01-02",
    "2020-04-27",

    "2021-01-08",
    "2021-05-10",

    "2022-01-02",

    "2023-01-02",
    "2023-04-24",
    "2023-05-08",
    "2023-11-06",
    "2023-11-07",
);

$WORKSAURDAYS = array(
    "2019-05-04",
    "2019-05-11",
    "2019-11-16",

    "2020-01-04",
    "2020-04-04",

    "2021-01-16",
    "2021-05-15",

    "2022-03-12",
    "2022-05-14",

    "2023-04-29",
    "2023-05-13",
    "2023-11-11",

);

$CALENDAR_BY = array( "workingDays"=>$WORKINGDAYS, "holidayDays"=>$HOLIDAYDAYS, "auxholydayDays"=>$AUXHOLYDAYDAYS, "workSaurdays"=>$WORKSAURDAYS );


function fm_getmicrotime() {
    list( $usec, $sec ) = explode( " ", microtime() );
    return ( (float)$usec + (float)$sec );
}

# *****************************************************************************
# Purpose        gets current date time in database format
# Inputs   nothing
# Remarks
# Returns        date base specific date time
function get_current_time() # gets current date and time as a string in MySQL format
{
    return strftime( "%Y-%m-%d %H:%M:%S", time() + intval( CONF_TIMEZONE ) * 3600 );
}

function get_only_time() # gets current date and time as a string in MySQL format
{
    return strftime( "%H:%M:%S", time() + intval( CONF_TIMEZONE ) * 3600 );
}

function get_yesterdate( $showtime = 1 ) {
    ( $showtime )
    ? $yesterday = strftime( "%Y-%m-%d 00:00:00", time() - 24 * 3600 + intval( CONF_TIMEZONE ) * 3600 )
    : $yesterday = strftime( "%Y-%m-%d", time() - 24 * 3600 + intval( CONF_TIMEZONE ) * 3600 );
    return $yesterday;
}
function get_daysbefore( $days = 1, $showtime = 0 ) {
    ( $showtime )
    ? $result = strftime( "%Y-%m-%d 00:00:00", time() - $days * 24 * 3600 + intval( CONF_TIMEZONE ) * 3600 )
    : $result = strftime( "%Y-%m-%d", time() - $days * 24 * 3600 + intval( CONF_TIMEZONE ) * 3600 );
    return $result;
}
function get_tomorrow( $showtime = 1 ) {
    ( $showtime )
    ? $tomorrow = strftime( "%Y-%m-%d 00:00:00", time() + 24 * 3600 + intval( CONF_TIMEZONE ) * 3600 )
    : $tomorrow = strftime( "%Y-%m-%d", time() + 24 * 3600 + intval( CONF_TIMEZONE ) * 3600 );
    return $tomorrow;
}

#converts datetime provided as a string into a standard form (date format is defined in store settings)
function dtConvertToStandartForm( $datetime, $showtime = 0 ) {
    # 2004-12-30 13:25:41
    $array = explode( " ", $datetime );
    $date  = $array[0];
    $time  = $array[1];

    $dateArray = explode( "-", $date );
    $day       = $dateArray[2];
    $month     = $dateArray[1];
    $year      = $dateArray[0];

    if ( !strcmp( _getSettingOptionValue( "CONF_DATE_FORMAT" ), "MM/DD/YYYY" ) ) {
        $date = $month . "/" . $day . "/" . $year;
    } elseif ( !strcmp( _getSettingOptionValue( "CONF_DATE_FORMAT" ), "YYYY-MM-DD" ) ) {
        $date = $year . "-" . $month . "-" . $day;
    } else {
        $date = $day . "." . $month . "." . $year;
    }

    if ( $showtime == 1 ) {
        return $date . " " . $time;
    } else {
        return $date;
    }
}

#converts datetime provided as a string into an array
function dtGetParsedDateTime( $datetime ) {
    # 2004-12-30 13:25:41 - MySQL database datetime format

    $array = explode( " ", $datetime ); #divide date and time
    $date  = $array[0];
    $time  = $array[1];

    $dateArray = explode( "-", $date );

    return array(
        "day"   => (int)$dateArray[2],
        "month" => (int)$dateArray[1],
        "year"  => (int)$dateArray[0],
    );
}

#$dt is a datetime string in MySQL default format (e.g. 2005-12-25 23:59:59)
#this functions converts it to format selected in the administrative mode
function format_datetime( $dt ) {
    #$dformat = ( !strcmp( CONF_DATE_FORMAT, "DD.MM.YYYY" ) ) ? "d.m.Y H:i:s" : "m/d/Y h:i:s A";
    $dformat = ( !strcmp( CONF_DATE_FORMAT, "DD.MM.YYYY" ) ) ? "d.m.Y H:i:s" : "Y-m-d H:i:s";
    $a       = @date( $dformat, strtotime( $dt ) );
    return $a;
}
function format_datetimeOLD( $dt ) {
    $dformat = ( !strcmp( CONF_DATE_FORMAT, "DD.MM.YYYY" ) ) ? "d.m.Y H:i:s" : "m/d/Y h:i:s A";
    $a       = @date( $dformat, strtotime( $dt ) );
    return $a;
}
function format_timezone( $dt ) {
    #$dformat = ( !strcmp( CONF_DATE_FORMAT, "DD.MM.YYYY" ) ) ? "d.m.Y H:i:s" : "m/d/Y h:i:s A";
    $dformat = "Y-m-d H:i:s P e";
    $a       = @date( $dformat, strtotime( $dt ) );
    return $a;
}

#$dt is a datetime string to MySQL default format (e.g. 2005-12-25)
#this functions converts it to format selected in the administrative mode
function dtDateConvert( $dt ) {
    $dformat = ( !strcmp( CONF_DATE_FORMAT, "DD.MM.YYYY" ) ) ? "." : "/";
    $array   = explode( $dformat, $dt );
    $date    = $array[2] . "-" . $array[1] . "-" . $array[0];
    return $date;
}

function FormatAsSQL( $dt, $ShowTime = 1 ) {
    setlocale( LC_TIME, 'en_EN.UTF-8' );
    return ( $ShowTime ) ? date( "Y-m-d H:i:s", strtotime(  ( $dt ) ) ) : date( "Y-m-d", strtotime(  ( $dt ) ) );
}

function FormatJSAsSQL( $jststmp, $ShowTime = 1 ) {
    setlocale( LC_TIME, 'en_EN.UTF-8' );
    return ( $ShowTime )
    ? date( "Y-m-d H:i:s", round(  ( $jststmp / 1000 ), 3 ) )
    : date( "Y-m-d", round(  ( $jststmp / 1000 ), 3 ) );
}

function FormatAsRUS( $dt, $style = 1, $YearMark = "г." ) {
    switch ( $style ) {
        case 0:
            $res = date( "d.m.Y{$YearMark}", strtotime(  ( $dt ) ) );
            break;
        case 1:
            $res = date( "H:i d.m.Y{$YearMark}", strtotime(  ( $dt ) ) );
            break;
        case 2:
            $res = date( "H:i:s d.m.Y{$YearMark}", strtotime(  ( $dt ) ) );
            break;
        case 3:
            $res = date( "d.m.Y{$YearMark} H:i", strtotime(  ( $dt ) ) );
            break;
        case 4:
            $res = date( "d.m.Y{$YearMark} H:i:s", strtotime(  ( $dt ) ) );
            break;
        case 5:
            // setlocale(LC_TIME, 'ru_RU');
            $res = date( "d F Y{$YearMark} < D %A >", strtotime(  ( $dt ) ) );
            break;
        case 6:
            setlocale( LC_TIME, 'ru_RU.UTF-8' );
            $res = strftime( "%d %B %Y{$YearMark} < %A >", strtotime(  ( $dt ) ) );
            break;
        case 7:
            setlocale( LC_TIME, 'ru_RU.UTF-8' );
            $res = strftime( "%A, ", strtotime(  ( $dt ) ) ) . "&nbsp;" . date( "<b>d.m.Y</b> {$YearMark} в <b>H:i:s</b>", strtotime(  ( $dt ) ) );
            break;
        default:
            $res = date( "d.m.Y H:i:s", strtotime(  ( $dt ) ) );
            break;
    }

    return $res;
}
#$dt is a datetime string in MySQL default format (e.g. 2005-12-25 23:59:59)
function dtDoDates( $dt, $number_aka = 0, $YearMark = "" ) {

    # "seconds"   Числовое представление секунд   от 0 до 59
    # "minutes"   Числовое представление минут    от 0 до 59
    # "hours"     Числовое представление часов    от 0 до 23
    # "mday"  Порядковый номер дня месяца     от 1 до 31
    # "wday"  Порядковый номер дня недели     от 0 (воскресенье) до 6 (суббота)
    # "mon"   Порядковый номер месяца     от 1 до 12
    # "year"  Номер года, 4 цифры     Примеры: 1999, 2003
    # "yday"  Порядковый номер дня в году     от 0 до 365
    # "weekday"   Полное наименование дня недели  от Sunday до Saturday
    # "month"     Полное наименование месяца, например, January или March     от January до December

    // Тире (длинное)  —   &mdash;     Alt + 0151
    // Короткое (среднее) тире     –   &ndash;     Alt + 0150
    // Минус   −   &minus;
    // Дефис   -       клавиша на клавиатуре

    $result     = array();
    $result_Ymd = FormatAsSQL( $dt );
    $result_dmY = FormatAsRUS( $dt );

    $tmp = getdate( strtotime( $dt ) );

    $rmon            = array( "", "января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря" );
    $result_stringRU = str_pad( $tmp["mday"], 2, '0', STR_PAD_LEFT ) . "&nbsp;" . $rmon[$tmp["mon"]] . "&nbsp;" . $tmp["year"];

    /*str_pad( $tmp["year"], 2, '0', STR_PAD_LEFT ) . "". */

    if ( is_integer( $number_aka ) && (int)$number_aka != 0 ) {
        $result_ff4 = str_pad( $tmp["mon"], 2, '0', STR_PAD_LEFT ) . "" . str_pad( $tmp["mday"], 2, '0', STR_PAD_LEFT ) . "-" . zeroFill( $number_aka );
    } elseif ( gettype( $number_aka ) != "integer" ) {
        $result_ff4 = str_pad( $tmp["mon"], 2, '0', STR_PAD_LEFT ) . "" . str_pad( $tmp["mday"], 2, '0', STR_PAD_LEFT ) . "&mdash;" . $number_aka;
    }

// console(array($number_aka,$result_ff4));

    #." от ".$order["order_time"];
    # $result_StandartFormFull = dtConvertToStandartForm( $dt, 1 );
    # $result_StandartForm     = dtConvertToStandartForm( $dt );

    $result_DateYmd       = str_pad( $tmp["year"], 2, '0', STR_PAD_LEFT ) . "-" . str_pad( $tmp["mon"], 2, '0', STR_PAD_LEFT ) . "-" . str_pad( $tmp["mday"], 2, '0', STR_PAD_LEFT );
    $result_DatedmY       = str_pad( $tmp["mday"], 2, '0', STR_PAD_LEFT ) . "." . str_pad( $tmp["mon"], 2, '0', STR_PAD_LEFT ) . "." . str_pad( $tmp["year"], 2, '0', STR_PAD_LEFT );
    $result_Time          = str_pad( $tmp["hours"], 2, '0', STR_PAD_LEFT ) . ":" . str_pad( $tmp["minutes"], 2, '0', STR_PAD_LEFT ) . ":" . str_pad( $tmp["seconds"], 2, '0', STR_PAD_LEFT );
    $result_is_workingDay = is_workingDay( FormatAsSQL( $dt, 0 ) );
#

    $result = array(
        "array"         => array(
            "daynumber" => (int)$tmp["yday"],
            "year"      => (int)$tmp["year"],
            "month"     => (int)$tmp["mon"],
            "day"       => (int)$tmp["mday"],
            "hours"     => (int)$tmp["hours"],
            "minutes"   => (int)$tmp["minutes"],
            "seconds"   => (int)$tmp["seconds"],
            "weekday"   => $tmp["weekday"],
            "monthname" => $tmp["month"],
        ),

        "dt"            => $dt, #"03\/09\/2019 11:48:51 PM"
         "Ymd"           => $result_Ymd, #"2019-03-09 23:48:51"
         "dmY"           => $result_dmY, #"09.03.2019 23:48:51"
         "stringRU"      => $result_stringRU . $YearMark, #"9 марта 2019"
         "ff4"           => $result_ff4, # "0309-0187"
         "Date"          => $result_DateYmd, # "2019-03-06"
         "DateRU"        => $result_DatedmY, # "06.03.2019"
         "Time"          => $result_Time, # "16:37:56"
         "is_workingDay" => $result_is_workingDay, # "16:37:56"
         "ff4_string"    => $result_ff4 . " от " . "{$result_stringRU}" . "{$YearMark}", # {$contract_dates.ff4} от {$contract_dates.stringRU}{$YearMark}
    );

    # если есть == то  будет такимже как order_aka
    if ( stristr( $number_aka, "==" ) ) {
        $result["ff4_string"] = str_replace( "==", "", "{$number_aka}" );
    }

// consolelog($result);
    return $result;

}

# Календарь праздничных и памятных дней, которые являются нерабочими в Беларуси

# 1 января – Новый год
# 7 января – Рождество Христово (православное)
# 8 Марта – День женщин
# 9-й день после православной Пасхи - Радуница
# 1 Мая — Праздник труда
# 9 Мая — День Победы
# 3 июля — День Независимости Республики Беларусь
# 7 ноября – День Октябрьской революции
# 25 декабря – Рождество Христово (католическое)

#  Постановлением Совета Министров Республики Беларусь от 15 декабря 2018 года № 906 утвержден график переноса рабочих дней в 2019 году:
# * с понедельника, 6 мая 2019 года # - на субботу, 4 мая 2019 года;
# * со среды, 8 мая 2019 года # - на субботу, 11 мая 2019 года;
# * с пятницы, 8 ноября 2019 года# - на субботу, 16 ноября 2019 года.
function CountWorkingDays( $from, $to ) {
    global $CALENDAR_BY;
    $Calendar = $CALENDAR_BY;

    $workingDays    = $Calendar["workingDays"];
    $holidayDays    = $Calendar["holidayDays"];
    $auxholydayDays = $Calendar["auxholydayDays"];
    $workSaurdays   = $Calendar["workSaurdays"];

    $from = new DateTime( $from );
    $to   = new DateTime( $to );
    $to->modify( "+1 day" );
    $interval = new DateInterval( "P1D" );
    $periods  = new DatePeriod( $from, $interval, $to );

    $days = 0;
    foreach ( $periods as $period ) {
        if ( !in_array( $period->format( "N" ), $workingDays ) ) {
            continue;
        }

        if ( in_array( $period->format( "Y-m-d" ), $holidayDays ) ) {
            continue;
        }

        if ( in_array( $period->format( "*-m-d" ), $holidayDays ) ) {
            continue;
        }

        if ( in_array( $period->format( "Y-m-d" ), $auxholydayDays ) ) {
            continue;
        }

        if ( in_array( $period->format( "Y-m-d" ), $workSaurdays ) ) {
            $days++;
        }

        $days++;
    }
    return (int)$days;
}

#$date is a date string in MySQL default format (e.g. 2005-12-25)
function is_workingDay( $date ) {
    global $CALENDAR_BY;
    $Calendar = $CALENDAR_BY;

    $workingDays    = $Calendar["workingDays"];
    $holidayDays    = $Calendar["holidayDays"];
    $auxholydayDays = $Calendar["auxholydayDays"];
    $workSaurdays   = $Calendar["workSaurdays"];

    $me     = new DateTime( $date );
    $result = false;

    if ( in_array( $me->format( "N" ), $workingDays ) ) {
        $result = true;
    }

    if ( in_array( $me->format( "Y-m-d" ), $holidayDays ) ) {
        $result = false;
    }

    if ( in_array( $me->format( "*-m-d" ), $holidayDays ) ) {
        $result = false;
    }

    if ( in_array( $me->format( "Y-m-d" ), $auxholydayDays ) ) {
        $result = false;
    }

    if ( in_array( $me->format( "Y-m-d" ), $workSaurdays ) ) {
        $result = true;
    }

    return $result;
}

# %b - банковские дни
# %w - рабочие дни
# %n - дни
# == - без изменений

function AddWorkingDay( $dateFrom, $addDay, $KindOfDay = "%w" ) {
    switch ( $KindOfDay ) {
        case "%w":
            $time_string = "18:00:00";
            break;
        case "%b":
            $time_string = "15:30:00";
            break;
        case "%n":
            $time_string = "H:i:s";
            break;
        default:
            $time_string = "18:00:00";
            break;
    }

    if ( $addDay < 0 ) {
        return date( "Y-m-d $time_string", strtotime( $dateFrom ) );

    } else {

        $from = strtotime( $dateFrom );

        $test = strtotime( "2019-03-27 08:11:11" );

        $mod       = strtotime( $dateFrom . " + " . $addDay . " days" );
        $mod_start = $mod;

        $calendarDateTo = date( "Y-m-d $time_string", $mod );

        $diff       = CountWorkingDays( FormatAsSQL( $dateFrom, 0 ), FormatAsSQL( $calendarDateTo, 0 ) );
        $diffstart  = CountWorkingDays( FormatAsSQL( $dateFrom, 0 ), FormatAsSQL( $calendarDateTo, 0 ) );
        $dateTo_new = $calendarDateTo;

        $notWorkingDays_count = 0;
        while (  ( $diff < ( $addDay + 1 ) ) and ( $notWorkingDays_count < 365 ) ) {
            $mod_new    = strtotime( $dateTo_new . " + 1 days" );
            $dateTo_new = date( "Y-m-d $time_string", $mod_new );
            $diff       = CountWorkingDays( FormatAsSQL( $dateFrom, 0 ), FormatAsSQL( $dateTo_new, 0 ) );
            $notWorkingDays_count++;
        }

        $ResultDate         = $dateTo_new;
        $calendarDays_count = _getDayBetweenDate( dtGetParsedDateTime( $dateFrom ), dtGetParsedDateTime( $ResultDate ) );

        // return json_encode( array($result["dateFrom"],$result["ResultDate"]) );
        return date( "Y-m-d H:i:s", strtotime( $ResultDate ) );
    }
}

function AddWorkingDayExtended( $dateFrom, $addDay, $KindOfDay = "%w" ) {
    switch ( $KindOfDay ) {
        case "%w":
            $time_string = "18:00:00";
            break;
        case "%b":
            $time_string = "15:30:00";
            break;
        case "%n":
            $time_string = "H:i:s";
            break;
        default:
            $time_string = "18:00:00";
            break;
    }

    if ( $addDay < 0 ) {
        return date( "Y-m-d $time_string", strtotime( $dateFrom ) );

    } else {

        $from = strtotime( $dateFrom );

        $test = strtotime( "2019-03-27 08:11:11" );

        $mod       = strtotime( $dateFrom . " + " . $addDay . " days" );
        $mod_start = $mod;

        $calendarDateTo = date( "Y-m-d $time_string", $mod );

        $diff       = CountWorkingDays( FormatAsSQL( $dateFrom, 0 ), FormatAsSQL( $calendarDateTo, 0 ) );
        $diffstart  = CountWorkingDays( FormatAsSQL( $dateFrom, 0 ), FormatAsSQL( $calendarDateTo, 0 ) );
        $dateTo_new = $calendarDateTo;

        $notWorkingDays_count = 0;
        while (  ( $diff < ( $addDay + 1 ) ) and ( $notWorkingDays_count < 365 ) ) {
            $mod_new    = strtotime( $dateTo_new . " + 1 days" );
            $dateTo_new = date( "Y-m-d $time_string", $mod_new );
            $diff       = CountWorkingDays( FormatAsSQL( $dateFrom, 0 ), FormatAsSQL( $dateTo_new, 0 ) );
            $notWorkingDays_count++;
        }

        $ResultDate         = $dateTo_new;
        $calendarDays_count = _getDayBetweenDate( dtGetParsedDateTime( $dateFrom ), dtGetParsedDateTime( $ResultDate ) );

        $result["dateFrom"]             = ( $dateFrom );
        $result["addDay"]               = ( $addDay );
        $result["KindOfDay"]            = ( $KindOfDay );
        $result["time_string"]          = ( $time_string );
        $result["from"]                 = ( $from );
        $result["test"]                 = ( $test );
        $result["mod_start"]            = ( $mod_start );
        $result["mod"]                  = ( $mod );
        $result["calendarDateTo"]       = ( $calendarDateTo );
        $result["dateTo_new"]           = ( $dateTo_new );
        $result["diff"]                 = ( $diff );
        $result["diffstart"]            = ( $diffstart );
        $result["notWorkingDays_count"] = ( $notWorkingDays_count );
        $result["calendarDays_count"]   = ( $calendarDays_count );
        $result["ResultDate"]           = date( "Y-m-d H:i:s", strtotime( $ResultDate ) );

        // return json_encode( array($result["dateFrom"],$result["ResultDate"]) );
        return ( $result["ResultDate"] );
    }
}

### из order_functions.php
function mycal_days_in_month( $calendar, $month, $year ) {
    $month = (int)$month;
    $year  = (int)$year;

    if ( 1 > $month || $month > 12 ) {
        return 0;
    }

    if ( $month == 1 || $month == 3 || $month == 5 || $month == 7 || $month == 8 || $month == 10 || $month == 12 ) {
        return 31;
    } else {
        if ( $month == 2 && $year % 4 == 0 ) {
            return 29;
        } elseif ( $month == 2 && $year % 4 != 0 ) {
            return 28;
        } else {
            return 30;
        }

    }
}

function _getCountDay( $date ) {
    $countDay = 0;
    for ( $year = 1900; $year < $date["year"]; $year++ ) {
        for ( $month = 1; $month <= 12; $month++ ) {
            $countDay += mycal_days_in_month( CAL_GREGORIAN, $month, $year );
        }

    }

    for ( $month = 1; $month < $date["month"]; $month++ ) {
        $countDay += mycal_days_in_month( CAL_GREGORIAN, $month, $date["year"] );
    }

    $countDay += $date["day"];
    return $countDay;
}

# *****************************************************************************
# Purpose        gets address string
# Inputs           $date array of item
#                        "day"
#                        "month"
#                        "year"
#                $date2 must be more later $date1
# Remarks
# Returns
function _getDayBetweenDate( $date1, $date2 ) {
    if ( $date1["year"] > $date2["year"] ) {
        return -1;
    }

    if ( $date1["year"] == $date2["year"] && $date1["month"] > $date2["month"] ) {
        return -1;
    }

    if ( $date1["year"] == $date2["year"] && $date1["month"] == $date2["month"] &&
        $date1["day"] > $date2["day"] ) {
        return -1;
    }

    return _getCountDay( $date2 ) - _getCountDay( $date1 );
}

?>