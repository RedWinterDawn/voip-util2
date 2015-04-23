<?php

function showHtmlStripLinks($url){
    $curl = curl_init($url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,1);
    curl_setopt($curl,CURLOPT_TIMEOUT,2);
    $result = curl_exec($curl);
    $curl_errno = curl_errno($curl);
    $curl_error = curl_error($curl);
    curl_close();
    if ($curl_errno > 0) {
        echo "cURL Error ($curl_errno): $curl_error\n";
    } else {
        $result = str_replace("<A", "<I", $result);
        $result = str_replace("</A", "</I", $result);
        $result = str_replace("<a", "<i", $result);
        $result = str_replace("</a", "</i", $result);
		$result = str_replace('<a href=','<a xref=', $result);
        echo $result;
    }
}

showHtmlStripLinks("http://10.125.252.170:2812/");

