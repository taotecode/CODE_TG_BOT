<?php
/**
 * Test
 * @project jd_bot
 * @copyright
 * @author
 * @version
 * @createTime 16:59
 * @filename Test.php
 * @product_name PhpStorm
 * @link
 * @example
 */

namespace app\api\controller;

use app\admin\model\AuthTgGroup;
use app\admin\model\CodeType;
use app\api\service\CodeTableService;
use app\common\controller\ApiController;
use think\App;
use think\facade\Db;
use think\facade\Env;

class Test extends ApiController
{
    protected $authTgGroup;
    protected $codeTableService;
    protected $codeType;

    public function __construct(
        App $app,
        AuthTgGroup $authTgGroup,
        CodeTableService $codeTableService,
        CodeType $codeType
    ){
        parent::__construct($app);
        $this->authTgGroup = $authTgGroup;
        $this->codeTableService = $codeTableService;
        $this->codeType = $codeType;
    }

    public function index(){
        $url = 'https://api.telegram.org/bot2054984599:AAGm0zP_M3tCr_oz_E_CpsiapyidH5f854w/sendmessage';
        $data = [
            'chat_id' => $this->request->get('id'),
            'text'=>'test',
        ];
        dump(curl_post($url, $data));
    }

    public function indexw(){
        //清除超过一个月的数据库
        $codeType=$this->codeType->select();
        $month=1;
        foreach ($codeType as $key=>$val){
            $tableName='jd_code_'.date($val->storage_time, strtotime("-".$month." month")).'_'.$val->SystemCommand->command;
            $check = Db::query("show tables like '{$tableName}'");
            if (!$check){
                continue;
            }
            if (Db::execute("DROP TABLE {$tableName}")==0){
                dump('o'.$tableName);
            }else{
                dump('失败'.$tableName);
            }
            $month++;
        }
        //清除超过指定天数的数据
        foreach ($codeType as $key=>$val){
            $tableName='jd_code_'.date($val->storage_time).'_'.$val->SystemCommand->command;
            $check = Db::query("show tables like '{$tableName}'");
            if (!$check){
                continue;
            }
            $data=Db::table($tableName)->whereTime('create_time', '<=',strtotime("-".$val->code_time." day") )->delete();
            if ($data){
                dump('o'.$tableName);
            }else{
                dump('失败'.$tableName);
            }
        }
    }

/*    public function indexa(){
        $token='2054984599:AAGm0zP_M3tCr_oz_E_CpsiapyidH5f854w';
        $url = 'https://api.telegram.org/bot' . $token . '/setMyCommands';
        $data = [
            'commands'=>[['command'=>'help','description'=>'这是一条测试命令']],
        ];
        dump(json_encode($data));
        $result = curl_post($url, json_encode($data));
        $result=json_decode($result,true);
        dump($result);
        dump(__url('test.tow'));
    }

    public function tow(){
        $token='2054984599:AAGm0zP_M3tCr_oz_E_CpsiapyidH5f854w';
        $url = 'https://api.telegram.org/bot' . $token . '/getMyCommands';
        $data = [];
        $result = curl_post($url, $data);
        $result=json_decode($result,true);
        dump($result);
    }*/
}