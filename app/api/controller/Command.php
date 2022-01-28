<?php
/**
 * Command
 * @project jd_bot
 * @copyright
 * @author
 * @version
 * @createTime 20:29
 * @filename Command.php
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
use think\facade\Log;
use function Symfony\Component\Translation\t;

class Command extends ApiController
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

    /**
     * 测试命令
     * @param null $messageData
     * @param array $input
     * @param array $commandData
     * @return string
     */
    public function test($messageData=null,$input=[],$commandData=[]){
        return '这是一条测试命令:'.$messageData;
    }

    /**
     * 自定义码命令
     * @param null $messageData
     * @param array $input
     * @param array $commandData
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function code($messageData=null,$input=[],$commandData=[]){
        if (empty($messageData)) {
            return '请输入您的助力码';
        }
        //获取码类型
        $codeType=$this->codeType->where('command_id',$commandData['id'])->find();
        //设置存储时间
        $this->codeTableService->tableSuffix=date($codeType['storage_time'], time());
        //设置表名
        $this->codeTableService->tableName =$this->codeTableService->tableName.'_'.$commandData['command'];

        $chatId=$input['groupId'];
        $codes=explode("&",$messageData);
        $sqlData=[];
        $beCode=[];
        $okCode=[];
        foreach ($codes as $code){
            if (empty($code)){
                continue;
            }
            if ($this->codeTableService->query(['code'=>$code],'pet')){
                $beCode[]=$code;
                continue;
            }
            $okCode[]=$code;
            $sqlData[]=[
                'chat_id'=>$chatId,
                'tg_id'=>$input['message']['from']['id'],
                'code'=>$code,
                'num'=>0,
                'pull_num'=>0,
                'status'=>0,
                'create_time'=>time(),
            ];
        }
        if (empty($okCode)){
            return '添加成功:0'.PHP_EOL.'重复code:'.implode($beCode,PHP_EOL);
        }
        $sql=$this->codeTableService->saveALl($sqlData,'pet');
        if ($sql===true){
            return '添加成功:'.PHP_EOL.implode($okCode,PHP_EOL).PHP_EOL.'重复code:'.PHP_EOL.implode($beCode,PHP_EOL);
        }
        return  '添加失败！'.PHP_EOL.$sql;
    }
}