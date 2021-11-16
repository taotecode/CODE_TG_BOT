<?php
/**
 * Code
 * @project jd_bot
 * @copyright
 * @author
 * @version
 * @createTime 14:46
 * @filename Code.php
 * @product_name PhpStorm
 * @link
 * @example
 */

namespace app\api\job;

use Redis;
use think\App;
use think\facade\Config;
use think\facade\Db;
use think\facade\Env;
use think\queue\Job;

class Code
{

    /**
     * 调用
     * @param Job $job 当前的任务对象
     * @param array|mixed|string $data 发布任务时自定义的数据
     * @throws \think\db\exception\DbException
     */
    public function fire(Job $job, $data): void
    {

        $isJobDone = $this->run_job($data);
        if ($isJobDone) {
            $job->delete();
            print("<info>已经完成并删除" . "</info>\n");
        } else if ($job->attempts() > 3) {
            print("<warn>Hello Job has been retried more than 3 times!" . "</warn>\n");
            $job->delete();
        }
    }

    /**
     * 根据消息中的数据进行实际的业务处理...
     * @param $data array|string
     * @return bool
     * @throws \think\db\exception\DbException
     */
    private function run_job($data)
    {
        print("<info>新的工作开始</info> \n");
        if (!empty($data['userInfo']??'')){
            //增加pull_num
            Db::table(Config::get('database.connections.mysql.prefix').$data['tableName'])->where('id', $data['userInfo']['id'])->inc('pull_num')->update();
        }
        if (!empty($data['list'])){
            //增加被助力的num
            foreach ($data['list'] as $item){
                if (empty($item['id']??'')){
                    Db::table(Config::get('database.connections.mysql.prefix').$data['tableName'])->where('code', $item)->inc('num')->update();
                }else{
                    Db::table(Config::get('database.connections.mysql.prefix').$data['tableName'])->where('id', $item['id'])->inc('num')->update();
                }
            }
        }
        //更新redis集合排名
        if (empty($data['codeType'])){
            return true;
        }
        if ($data['codeType']['storage_type']!="1"){
            return true;
        }
        //实例化redis
        $redis = new Redis();
        //连接
        $redis->connect(Env::get('cache.redis_host', '127.0.0.1'), 6379);
        $model =new \app\api\model\Code();
        $model->setTableName($data['tableName']);
        if (empty($data['userInfo']??'')){
            $list= $model
                ->where(['status'=>0])
                ->where('pull_num','>=',$data['codeType']['pull_number'])
                ->where('num','<=',$data['codeType']['number'])
                ->orderRaw('rand()')
                ->limit(100)
                ->order('pull_num','desc')
                ->order('num','asc')
                ->order('id','desc')
                ->select();
            if ($list->isEmpty()){
                $list= $model
                    ->where(['status'=>0])
                    ->where('num','<=',$data['codeType']['number'])
                    ->orderRaw('rand()')
                    ->limit(100)
                    ->order('pull_num','desc')
                    ->order('num','asc')
                    ->order('id','desc')
                    ->select();
            }
            //删除之前的数据
            $redis->del('api:code:sorted:'.$data['tableName']);
            $redis->del('api:code:hash:'.$data['tableName']);
            foreach ($list as $k=>$item){
                //加入集合
                $redis->zadd('api:code:sorted:'.$data['tableName'], $k,(string)$item->id);
                //将值加入hash
                $redis->hset('api:code:hash:'.$data['tableName'], (string)$item->id, (string)$item->code);
            }
            return true;
        }
        $list=$model
            ->whereNotIn('tg_id',[$data['userInfo']['tg_id']])
            ->where(['status'=>0])
            ->where('pull_num','>=',$data['codeType']['pull_number'])
            ->where('num','<=',$data['codeType']['number'])
            ->orderRaw('rand()')
            ->limit(100)
            ->order('pull_num','desc')
            ->order('num','asc')
            ->order('id','desc')
            ->select();
        if ($list->isEmpty()){
            $list=$model
                ->whereNotIn('tg_id',[$data['userInfo']['tg_id']])
                ->where(['status'=>0])
                ->where('num','<=',$data['codeType']['number'])
                ->orderRaw('rand()')
                ->limit(100)
                ->order('pull_num','desc')
                ->order('num','asc')
                ->order('id','desc')
                ->select();
        }
        //删除之前的数据
        $redis->del('api:code:sorted:'.$data['tableName'].'_'.$data['userInfo']['id']);
        $redis->del('api:code:hash:'.$data['tableName'].'_'.$data['userInfo']['id']);
        foreach ($list as $k=>$item){
            //加入集合
            $redis->zadd('api:code:sorted:'.$data['tableName'].'_'.$data['userInfo']['id'], $k,(string)$item->id);
            //将值加入hash
            $redis->hset('api:code:hash:'.$data['tableName'].'_'.$data['userInfo']['id'], (string)$item->id, (string)$item->code);
        }
        return true;
    }


    public function failed($data)
    {
        // ...任务达到最大重试次数后，失败了
        print("Warning: Job failed after max retries. job data is :" . var_export($data, true) . "\n");
    }
}