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

use app\admin\model\AuthTgBind;
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
    protected $authTgBind;

    public function __construct(
        App $app,
        AuthTgGroup $authTgGroup,
        CodeTableService $codeTableService,
        CodeType $codeType,
        AuthTgBind $authTgBind
    ){
        parent::__construct($app);
        $this->authTgGroup = $authTgGroup;
        $this->codeTableService = $codeTableService;
        $this->codeType = $codeType;
        $this->authTgBind = $authTgBind;
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
            if ($this->codeTableService->query(['code'=>$code],$commandData['command'])){
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
        $sql=$this->codeTableService->saveALl($sqlData,$commandData['command']);
        if ($sql===true){
            return '添加成功:'.PHP_EOL.implode($okCode,PHP_EOL).PHP_EOL.'重复code:'.PHP_EOL.implode($beCode,PHP_EOL);
        }
        return  '添加失败！'.PHP_EOL.$sql;
    }

    public function go(){
        $a=null;
        $aa="1&43";
        dd($this->delBind($aa,['message'=>['from'=>['id'=>'1685931237']],'groupId'=>'123']));
    }

    /**
     * 我的码库
     * @param null $messageData
     * @param array $input
     * @param array $commandData
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function codeList($messageData=null,$input=[],$commandData=[]){
        $tg_id=$input['message']['from']['id'];
        $code=null;
        if (!empty($messageData)){
            $code=$messageData;
        }
        //获取码类型
        $codeType=$this->codeType->select();

        $oneCode=null;
        $allCode=null;

        //检查这些码库是否存在
        foreach ($codeType as $key=>$val){
            //设置存储时间
            $this->codeTableService->tableSuffix=date($val['storage_time'], time());
            //设置表名
            $this->codeTableService->tableName =$this->codeTableService->tableName.'_'.$val->SystemCommand['command'];
            if ($this->codeTableService->query(['code'=>$code,'tg_id'=>$tg_id],$val->SystemCommand['command'])){
                $oneCode .=$val->SystemCommand['command'].":".$code.PHP_EOL;
            }
            $data=$this->codeTableService->queryAll(['tg_id'=>$tg_id],$val->SystemCommand['command']);
            foreach ($data as $k=>$v){
                $allCode.=$val->SystemCommand['command'].":".$v['code'].PHP_EOL;
            }

        }
        if (!empty($code)){
            if (empty($oneCode)){
                return "您的码库中没有这个码子";
            }
            return $oneCode."存在码库中";
        }

        if ($allCode===null||empty($allCode)){
            return "您的码库中没有任何码子";
        }
        return $allCode."存在码库中";
    }

    /**
     * 码库总数
     * @param null $messageData
     * @param array $input
     * @param array $commandData
     * @return string|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function codeTotal($messageData=null,$input=[],$commandData=[]){
        $countStr=null;
        //获取码类型
        $codeType=$this->codeType->select();
        foreach ($codeType as $key=>$val){
            //设置存储时间
            $this->codeTableService->tableSuffix=date($val['storage_time'], time());
            //设置表名
            $this->codeTableService->tableName =$this->codeTableService->tableName.'_'.$val->SystemCommand['command'];
            $data=$this->codeTableService->count([],null);
            $countStr.=$val->SystemCommand['command'].":".$data.PHP_EOL;
        }
        return $countStr;
    }

    /**
     * 绑定JD账号
     * @param null $messageData
     * @param array $input
     * @param array $commandData
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function bind($messageData=null,$input=[],$commandData=[]){
        $chatId=$input['groupId'];
        $tg_id=$input['message']['from']['id'];
        $bindData=$this->authTgBind->where(['chat_id'=>$chatId,'tg_id'=>$tg_id])->find();
        if (empty($messageData)){
            if (empty($bindData)){
                return "您当前还未绑定任何账号！";
            }
            $bindArray=json_decode($bindData['bind'],true);
            $dataStr=null;
            foreach ($bindArray as $datum){
                $dataStr .=$datum.PHP_EOL;
            }
            return $dataStr."以上是您绑定的账号";
        }
        $binds=explode("&",$messageData);
        if (empty($bindData)){
            if (count($binds)>=6){
                return "每个用户最多绑定5条账号！";
            }
            $addBind=['chat_id'=>$chatId,'tg_id'=>$tg_id,'bind'=>json_encode($binds)];
            if ($this->authTgBind->insert($addBind)){
                return "添加成功！";
            }
            return "添加失败，请联系管理员";
        }

        $bindArray=json_decode($bindData['bind'],true);
        if (count($bindArray)+count($binds)>=6){
            return "每个用户最多绑定5条账号！";
        }
        foreach ($binds as $bind){
            if (in_array($bind,$bindArray)){
                continue;
            }
            $bindArray[]=$bind;
        }
        if (empty($bindArray)){
            return "您所提交的数据已经存在";
        }
        if ($this->authTgBind->where(['chat_id'=>$chatId,'tg_id'=>$tg_id])->update(['bind'=>json_encode($bindArray)])){
            return "添加成功！";
        }
        return "添加失败，请联系管理员";
    }

    /**
     * 解绑JD账号
     * @param null $messageData
     * @param array $input
     * @param array $commandData
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delBind($messageData=null,$input=[],$commandData=[]){
        if (empty($messageData)){
            return "请输入要解绑的账号！";
        }
        $binds=explode("&",$messageData);
        $chatId=$input['groupId'];
        $tg_id=$input['message']['from']['id'];
        $bindData=$this->authTgBind->where(['chat_id'=>$chatId,'tg_id'=>$tg_id])->find();
        if (empty($bindData)){
            return "您未绑定任何账号！";
        }
        $bindArray=json_decode($bindData['bind'],true);
        foreach ($binds as $bind){
            foreach ($bindArray as $k=>$v){
                if ($bind==$v){
                    unset($bindArray[$k]);
                }
            }
        }
        if (empty($bindArray)){
            $returnStr="解绑完毕";
        }else{
            $returnStr="解绑完毕，以下是您剩余绑定的数据".PHP_EOL.implode(PHP_EOL,$bindArray);
        }
        if ($this->authTgBind->where(['chat_id'=>$chatId,'tg_id'=>$tg_id])->update(['bind'=>json_encode($bindArray)])){
            return $returnStr;
        }
        return "解绑失败，请联系管理员";
    }
}