<?php
/**
 * ${NAME}
 * @project jd_bot
 * @copyright ${COPYRIGHT}
 * @author ${AUTHOR}
 * @version ${VERSION}
 * @createTime 10:27
 * @filename common.php
 * @product_name PhpStorm
 * @link ${$GITURL}
 * @example
 */
function curl_post($url, $data)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}