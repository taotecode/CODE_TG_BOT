<?php
/**
 * GetCodeService
 * @project CODE_TG_BOT
 * @copyright
 * @author
 * @version
 * @createTime 11:00
 * @filename GetCodeService.php
 * @product_name PhpStorm
 * @link
 * @example
 */

namespace app\api\service;

use app\api\model\Code;
use Redis;
use think\facade\Env;
use think\facade\Queue;

class GetCodeService
{
    protected $model;

    /**
     * 构造方法
     * SystemLogService constructor.
     */
    public function __construct()
    {
    }

    /**
     * mysql取出码
     * @param $tableName
     * @param $userInfo
     * @param $codeType
     * @param $limit
     * @return false|mixed
     */
    public function mysqlType($tableName,$userInfo,$codeType,$limit){
        $this->model=new Code();
        $this->model->setTableName($tableName);
        //没有添加助力码则随机取
        if (!$userInfo){
            //第一次取
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
            //如果为空取第二次，减少pull_num（用户拉取次数）条件
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
            //组合队列任务数据
            $job_data=[
                'tableName'=>$tableName,'list'=>$list,'codeType'=>$codeType,
            ];
            //组合反给用户的数据
            $lists=[];
            foreach ($list as $item){
                $lists[]=(string)$item->code;
            }
            //组合数据提交给队列任务
            return $this->actionWithCodeJob($job_data,$lists);
        }
        //第一次取出
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
        //如果为空取第二次，减少pull_num（用户拉取次数）条件
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
            'userInfo'=>$userInfo,'tableName'=>$tableName,'list'=>$list,'codeType'=>$codeType,
        ];
        //组合数据提交给用户
        $lists=[];
        foreach ($list as $item){
            $lists[]=(string)$item->code;
        }
        //组合数据提交给队列任务
        return $this->actionWithCodeJob($job_data,$lists);
    }

    /**
     * redis取出码
     * @param $tableName
     * @param $userInfo
     * @param $codeType
     * @param int $limit
     * @return false|mixed|void
     */
    public function redisType($tableName,$userInfo,$codeType,$limit=10){
        //实例化redis
        $redis = new Redis();
        //连接
        $redis->connect(Env::get('cache.redis_host', '127.0.0.1'), 6379);
        if (!$userInfo){
            //取出数据
            $sortednName='api:code:sorted:'.$tableName;
            $list=$redis->zrange($sortednName,0,$limit);
            $hashName='api:code:hash:'.$tableName;
            $lists=[];
            foreach ($list as $item){
                $lists[]=$redis->hGet($hashName,$item);
            }
            //组合数据提交给队列任务
            $job_data=[
                'userInfo'=>$userInfo,'tableName'=>$tableName,'list'=>$lists,'codeType'=>$codeType,
            ];
            //组合数据提交给队列任务
            return $this->actionWithCodeJob($job_data,$lists);
        }
        $redis->zrange('set_zadd',0,$limit);
    }

    /**
     * 消息队列调用方法
     * @param $data array 队列数据
     * @param $lists mixed 返回数据
     * @return false|mixed
     */
    protected function actionWithCodeJob($data,$lists)
    {
        //TODO: 命令行挂载 php think queue:work --queue CodeJob

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
            //return show(200,'',$lists);
            return $lists;
        }
        return false;
        //return show(5000,'助力程序启动失败');
    }
}