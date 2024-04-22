<?php
/**
 * Проверяем, установлен ли заданный параметр. Если один из указанных параметров 
 * не задан, вызывается die().
 *
 * @param $parameters Параметры, которые проверяются.
 */
function checkGETParametersOrDie($parameters) {
    foreach ($parameters as $parameter) {
        isset($_GET[$parameter]) || die("Please, provide '$parameter' parameter.");
    }
}
/**
 * Получаем параметры GET.
 *
 * @return GET строка параметра.
 */
function stringifyParameters() {
    $parameters = '?';
    foreach ($_GET as $key => $value) {
        $key = urlencode($key);
        $value = urlencode($value);
        $parameters .= "$key=$value&";
    }
    rtrim($parameters, '&');
    return $parameters;
}
/**
 * Создаем Curl-запрос для заданного URL-адреса.
 *
 * @param $url URL-адрес, к которому создается запрос.
 * @return Curl-запрос к url-адресу; false, если возникает ошибка.
 */
function createCurlRequest($url) {
    $curl = curl_init();
    if (!$curl) {
        return false;
    }
    $configured = curl_setopt_array($curl, [
        CURLOPT_URL => $url . stringifyParameters(),
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RETURNTRANSFER => true
    ]);
    if (!$configured) {
        return false;
    }
    return $curl;
}


function curl_server_online_check(){
    if (function_exists('curl_init')){
        @$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://grp.nalog.gov.by/api/grp-public/data?unp=100582333&charset=UTF-8&type=json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
       $result =  @curl_exec($ch);
        $errnum = curl_errno($ch);
        @curl_close($ch);
    }
    return ($errnum == "0")?json_decode($result, true, JSON_BIGINT_AS_STRING | JSON_INVALID_UTF8_IGNORE):$errnum;
    // return ($errnum == "0");
}




/*

// Здесь начинается поток.
checkGETParametersOrDie(['url']);
$url = $_GET['url'];
$curl = createCurlRequest($url);
if (!$curl) {
    die('An error occured: ' . curl_error($curl));
}
$result = curl_exec($curl);
if (!$result) {
    die('An error occured: ' . curl_error($curl));
}

echo '<div>The result of the cURL request:</div>';
echo '<hr>';
echo $result;
curl_close($curl); // Don't forget to close!

*/