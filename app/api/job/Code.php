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
        print("<info>新的工作开始</info> \n");
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
        $tablePrefix=Config::get('database.connections.mysql.prefix');
        $tableName=$tablePrefix.$data['tableName'];
        if (!empty($data['userInfo']??'')){
            //增加pull_num
            Db::table($tableName)->where('id', $data['userInfo']['id'])->inc('pull_num')->update();
        }
        if (!empty($data['list'])){
            //增加被助力的num
            foreach ($data['list'] as $item){
                if (empty($item['id']??'')){
                    Db::table($tableName)->where('code', $item)->inc('num')->update();
                }else{
                    Db::table($tableName)->where('id', $item['id'])->inc('num')->update();
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
        //模型
        $model =new \app\api\model\Code();
        $model->setTableName($data['tableName']);
        //取出数据
        if (empty($data['userInfo']??'')){
            //第一次取
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
            //如果为空取第二次，减少pull_num（用户拉取次数）条件
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
            $redis->del('api:code:sorted:'.$data['tableName']);//索引库
            $redis->del('api:code:hash:'.$data['tableName']);//码库
            foreach ($list as $k=>$item){
                //加入集合
                $redis->zadd('api:code:sorted:'.$data['tableName'], $k,(string)$item->id);
                //将值加入hash
                $redis->hset('api:code:hash:'.$data['tableName'], (string)$item->id, (string)$item->code);
            }
            return true;
        }
        //第一次取
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
        //如果为空取第二次，减少pull_num（用户拉取次数）条件
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
        //加入用户专属码库，这样每个用户之间不会冲突
        //删除之前的数据
        $redis->del('api:code:sorted:'.$data['tableName'].'_'.$data['userInfo']['id']);//索引库
        $redis->del('api:code:hash:'.$data['tableName'].'_'.$data['userInfo']['id']);//码库
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