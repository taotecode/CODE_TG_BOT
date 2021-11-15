# 助力池-CODE_TG_BOT程序
### v2.0.2
[![Php Version](https://img.shields.io/badge/php-%3E=7.3.0-brightgreen.svg?maxAge=2592000&color=yellow)](https://github.com/php/php-src)
[![Mysql Version](https://img.shields.io/badge/mysql-%3E=5.7-brightgreen.svg?maxAge=2592000&color=orange)](https://www.mysql.com/)
[![Thinkphp Version](https://img.shields.io/badge/thinkphp-%3E=6.0.2-brightgreen.svg?maxAge=2592000)](https://github.com/top-think/framework)
[![Layui Version](https://img.shields.io/badge/layui-=2.5.5-brightgreen.svg?maxAge=2592000&color=critical)](https://github.com/sentsin/layui)
[![Layui Version](https://img.shields.io/badge/easyadmin-=2.1.6-brightgreen.svg?maxAge=2592000&color=critical)](https://github.com/zhongshaofa/easyadmin)

* TG电报交流群 [https://t.me/CODE_TG_BOT](https://t.me/CODE_TG_BOT)
* TG电报频道 [https://t.me/CODE_TG_BOT_NEW](https://t.me/CODE_TG_BOT_NEW)


### 已搭建的TG_BOT [https://t.me/JD_ShareCode_yuanzhu_bot](https://t.me/JD_ShareCode_yuanzhu_bot)

### 支持功能：
* 防白嫖
    * 通过tg机器人一键验证
    * 接口处直接验证
* 简单接口直达车
    * 接口对接简单
    * 一条链接直达
* 后台可管理
    * 支持多群组授权对接
    * 支持对码库的管理
    * 多管理员管理
* 定时清除码库

## 代码仓库

代码已开源，欢迎大家一起协作开发新功能并优化

* GitHub地址：[https://github.com/yuanzhumc/CODE_TG_BOT](https://github.com/yuanzhumc/CODE_TG_BOT)

* Gitee地址：[https://gitee.com/yuanzhumc/CODE_TG_BOT](https://gitee.com/yuanzhumc/CODE_TG_BOT)

## 安装方法
### 环境要求
* Centos>=7
* php>=7.3
* mysql>=8
### 必要软件
* crontab
* redis
* Supervisor
* composer
### 伪静态(Nginx)
其它的请参考ThinkPhp官方文档

网站目录请设置到public下
```nginx
location / {
	if (!-e $request_filename){
		rewrite  ^(.*)$  /index.php?s=$1  last;   break;
	}
}
```
```caddy1
rewrite {
    to {path} {path}/ /index.php/{uri}
}
```
### 克隆项目
```gitexclude
git clone https://github.com/yuanzhumc/CODE_TG_BOT
```
OR
```gitexclude
git clone https://gitee.com/yuanzhumc/CODE_TG_BOT
```
### 安装项目
cd到你的网站根目录，执行composer安装，或更新
```composer log
composer install -vvv
```
OR
```composer log
composer update
```
### 添加常驻任务
使用 **Supervisor** 

任务目录：你的网站根目录，如：/www/data/cd_bot/

任务执行命令
`php think queue:work`
### 添加定时任务
将下面这段ssh添加到定时任务里，每天0点执行一次
```shell
cd /你的网站根目录/
php think code_num
```
### 执行一键安装数据库
直接访问你的网站即可执行
### 配置网站项目
复制 **.example.env** 改名为.env

按照里面提示进行配置

强烈建议能配置redis的就配置，以免发生问题
### 配置后台
进入/admin后

* 打开【系统管理】
* 打开【配置管理】
* 配置机器人并一键设置Webhook(不知道怎么创建机器人请百度)

### 给机器人加命令

* 打开【码库管理】
* 打开【命令管理】

按照提示做

### 添加基础助力码

给机器人发送相关码命令，会自动创建码库

## 历史 Star 数

[![Stargazers over time](https://starchart.cc/yuanzhumc/CODE_TG_BOT.svg)](https://starchart.cc/yuanzhumc/CODE_TG_BOT)