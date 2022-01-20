<?php
/**
 * ShareCode
 * @project jd_bot
 * @copyright
 * @author
 * @version
 * @createTime 17:01
 * @filename ShareCode.php
 * @product_name PhpStorm
 * @link
 * @example
 */

namespace app\api\controller;

use app\api\model\Code;
use app\admin\model\CodeType;
use app\admin\model\SystemCommand;
use app\api\service\CodeTableService;
use app\api\service\GetCodeService;
use app\common\controller\ApiController;
use Redis;
use think\App;
use think\facade\Cache;
use think\facade\Config;
use think\facade\Db;
use think\facade\Env;
use think\facade\Queue;

class ShareCode extends ApiController
{
    protected $codeType;
    protected $systemCommand;
    protected $getCodeService;

    public function __construct(
        App $app,
        CodeType $codeType,
        SystemCommand $systemCommand,
        GetCodeService $getCodeService
    ){
        parent::__construct($app);
        $this->codeType=$codeType;
        $this->systemCommand=$systemCommand;
        $this->getCodeService=$getCodeService;
    }

    public function getList($type=null){
        $limit=$this->request->param('number',10);
        $code=$this->request->param('code');
        if (empty($code)){
            return show(5000,'请提供您的助力码后再进行获取');
        }
        //启用数据库查询缓存
        $systemCommandCacheName='api:ShareCode:getList:systemCommand_'.$type;
        if (Cache::has($systemCommandCacheName)){
            $systemCommand=Cache::get($systemCommandCacheName);
        }else{
            $systemCommand=$this->systemCommand->where('command',$type)->find();
            Cache::set($systemCommandCacheName,$systemCommand,'259200');
        }
        if (!$systemCommand){
            return show(5000,'该助力方式不支持！');
        }
        $codeType=$systemCommand->CodeType;
        $tableName='code_'.date($codeType->storage_time).'_'.$type;
        //先检查是否存在该表
        $check = Db::query("show tables like '".Config::get('database.connections.mysql.prefix').$tableName."'");
        if (empty($check)) {
            return show(4000,'码库无码，请到tg进行添加！');
        }
        $this->model=new Code();
        $this->model->setTableName($tableName);
        //检查用户是否添加助力码
        $userInfo=$this->model->where('code',$code)
            ->field('id,tg_id,code,num,pull_num')
            ->order('pull_num','desc')
            ->order('num','asc')
            ->find();

        //判断是mysql还是redis
        if ($codeType->storage_type!='1'){
            $listCode=$this->getCodeService->mysqlType($tableName,$userInfo,$codeType,$limit);
            if ($listCode!==false){
                return show(5000,'助力程序启动失败');
            }
        }else{
            $listCode=$this->getCodeService->redisType($tableName,$userInfo,$codeType,$limit);
            if ($listCode!==false){
                return show(5000,'助力程序启动失败');
            }
        }
        return show(200,'',$listCode);
    }
}