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
use app\common\controller\ApiController;
use think\App;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Queue;

class ShareCode extends ApiController
{
    protected $codeType;
    protected $systemCommand;

    public function __construct(
        App $app,
        CodeType $codeType,
        SystemCommand $systemCommand
    ){
        parent::__construct($app);
        $this->codeType=$codeType;
        $this->systemCommand=$systemCommand;
    }

    public function getList($type=null){
        $limit=$this->request->param('number',10);
        $code=$this->request->param('code');
        if (empty($code)){
            return show(5000,'请提供您的助力码后再进行获取');
        }
        //启用数据库查询缓存
        if (Cache::has('api:ShareCode:getList:systemCommand_'.$type)){
            $systemCommand=Cache::get('api:ShareCode:getList:systemCommand_'.$type);
        }else{
            $systemCommand=$this->systemCommand->where('command',$type)->find();
            Cache::set('api:ShareCode:getList:systemCommand_'.$type,$systemCommand,'259200');
        }
        if (!$systemCommand){
            return show(5000,'该助力方式不支持！');
        }
        $codeType=$systemCommand->CodeType;
        $tableName='code_'.date($codeType->storage_time, time()).'_'.$type;
        //先检查是否存在该表
        $check = Db::query("show tables like '{$tableName}'");
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
        //没有添加助力码则随机取
        if (!$userInfo){
            $list=$this->model
                ->where(['status'=>0])
                ->where('pull_num','>=',$codeType->pull_number)
                ->where('num','<=',$codeType->number)
                ->orderRaw('rand()')
                ->limit($limit)
                ->order('pull_num','desc')
                ->order('num','asc')
                ->order('id','desc')
                ->field('code')
                ->select();
            if ($list->isEmpty()){
                $list=$this->model
                    ->where(['status'=>0])
                    ->where('num','<=',$codeType->number)
                    ->orderRaw('rand()')
                    ->limit($limit)
                    ->order('pull_num','desc')
                    ->order('num','asc')
                    ->order('id','desc')
                    ->field('code')
                    ->select();
            }
            $job_data=[
                'tableName'=>$tableName,'list'=>$list
            ];
            $lists=[];
            foreach ($list as $item){
                $lists[]=(string)$item->code;
            }
            //组合数据提交给队列任务
            return $this->actionWithCodeJob($job_data,$lists);
        }
        //取出
        $list=$this->model
            ->whereNotIn('tg_id',[$userInfo->tg_id])
            ->where(['status'=>0])
            ->where('pull_num','>=',$codeType->pull_number)
            ->where('num','<=',$codeType->number)
            ->orderRaw('rand()')
            ->limit($limit)
            ->order('pull_num','desc')
            ->order('num','asc')
            ->order('id','desc')
            ->field('id,code')
            ->select();
        if ($list->isEmpty()){
            $list=$this->model
                ->whereNotIn('tg_id',[$userInfo->tg_id])
                ->where(['status'=>0])
                ->where('num','<=',$codeType->number)
                ->orderRaw('rand()')
                ->limit($limit)
                ->order('pull_num','desc')
                ->order('num','asc')
                ->order('id','desc')
                ->field('id,code')
                ->select();
        }
        //组合数据提交给队列任务
        $job_data=[
            'userInfo'=>$userInfo,'tableName'=>$tableName,'list'=>$list
        ];
        $lists=[];
        foreach ($list as $item){
            $lists[]=(string)$item->code;
        }
        //组合数据提交给队列任务
        return $this->actionWithCodeJob($job_data,$lists);
    }

    /**
     * 消息队列调用方法
     * @param $data array 数据
     */
    protected function actionWithCodeJob($data,$lists){
        //命令行挂载 php think queue:work --queue CodeJob

        //   当轮到该任务时，系统将生成一个该类的实例，并调用其 fire 方法
        $jobHandlerClassName = 'app\\api\\job\\Code';
        // 2.当前任务归属的队列名称，如果为新队列，会自动创建
        $jobQueueName = "CodeJob";
        // 3.当前任务所需的业务数据 . 不能为 resource 类型，其他类型最终将转化为json形式的字符串
        //   ( jobData 为对象时，需要在先在此处手动序列化，否则只存储其public属性的键值对)
        $jobData = $data;
        // 4.将该任务推送到消息队列，等待对应的消费者去执行
        $isPushed = Queue::push( $jobHandlerClassName , $jobData , $jobQueueName);
        //redis 驱动时，返回值为 随机字符串|false
        if( $isPushed !== false ){
            return show(200,'',$lists);
        }
        return show(5000,'助力程序启动失败');
    }
}