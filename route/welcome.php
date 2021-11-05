<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

Route::view('/', 'welcome', [
    'version' => time(),
    'data'    => [
        'description'        => '基于ThinkPHP6.0和Layui的高效率TG助力池',
        'system_description' => '该项目主要致力于发现助力码。项目以及文档还在持续完善，请保持关注。',
    ],
    'navbar'  => [
        [
            'name'   => '首页',
            'active' => true,
            'href'   => '/',
            'target' => '_self',
        ],
        [
            'name'   => '文档',
            'active' => false,
            'href'   => '',
            'target' => '_blank',
        ],
        [
            'name'   => '演示',
            'active' => false,
            'href'   => 'https://t.me/JD_ShareCode_yuanzhu_bot',
            'target' => '_blank',
        ],
        [
            'name'   => 'TG群',
            'active' => false,
            'href'   => 'https://t.me/CODE_TG_BOT',
            'target' => '_blank',
        ],
    ],
    'feature' => [
        [
            'name'        => 'TG群管理',
            'description' => '待开发。。。',
        ],
        [
            'name'        => 'TG用户管理',
            'description' => '待开发。。。',
        ],
        [
            'name'        => '待开发。。。',
            'description' => '待开发。。。',
        ],
        [
            'name'        => '快速生成CURD模块',
            'description' => '完善的命令行开发模式, 一键生成控制器、模型、视图、JS等文件, 使开发速度快速提升。',
        ],
        [
            'name'        => '公众号&小程序模块',
            'description' => '待开发。。。',
        ],
        [
            'name'        => '插件管理模块',
            'description' => '待开发。。。',
        ],
    ],
]);