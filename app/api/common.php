<?php
/**
 * ${NAME}
 * @project jd_bot
 * @copyright ${COPYRIGHT}
 * @author ${AUTHOR}
 * @version ${VERSION}
 * @createTime 08:55
 * @filename common.php
 * @product_name PhpStorm
 * @link ${$GITURL}
 * @example
 */

/**
 * 响应方法
 * @param int $status 状态码
 * @param string $message 提示信息
 * @param array $data 数据
 * @param string|null $error 错误信息
 * @param int $httpStatus 页面状态码
 * @param string $type 响应方式
 * @param null $url 重定向链接
 * @return \think\response\Redirect
 */
function show(int $status, string $message='', array $data=[], string $error=null, int $httpStatus=200, string $type='json', $url=null){
    $result=[
        "status"=>$status,
        'message'=>$message,
        'result'=>$data
    ];
    if ($error){
        $result['error']=$error;
    }
    switch ($type){
        case 'jsonp':
            return jsonp($result,$httpStatus);
        case 'xml':
            return xml($result,$httpStatus);
        case 'redirect':
            return redirect($url,$httpStatus??302);
        case 'html_error':
            throw new \think\exception\HttpException($status, $message);
        case 'json':
        default:
            return json($result,$httpStatus);
    }
}

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