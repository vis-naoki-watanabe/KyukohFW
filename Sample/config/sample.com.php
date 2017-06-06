<?php
define('FRAMEWORK_DIR', dirname(__FILE__));
define('DISPLAY_ERRORS', 'Off');
define('ERROR_REPORTING', E_ALL|E_STRICT);
define('SERVER_TYPE', 'release');
define('CACHE_PATH', '');

include ('config.php');

return $shared_config+[    
    'timezone' => 'Asia/Tokyo',
    
    // 出力ログファイル名（出力しない場合はコメントアウト)
    'log' => [
        'debug'     => 'logs/misc/debug.log',
        'sql'       => 'logs/misc/sql.log',
        'exception' => 'logs/misc/exception.log',
        'warning'   => 'logs/misc/warning.log',
        'error'     => 'logs/misc/error.log',
    ],
    
    'default' => [
        'controller' => 'index',
        'action' => 'index',
    ],
    
    // URLルーティング
    'routing' => [
        // [任意] sample1
//        [
//            'url' => ['hoge/user/index/*','hoge/user/*'],
//            'route' => 'dir/controller/action/UID/UNAME',
//        ],
        // [任意] sample2
//        [
//            'url' => ['test/user/*','test/my/*'],
//            'route' => 'controller/action/user_id/user_name',
//        ],
        
        [
            'url' => ['modules/*/*'],
            'route' => 'dir/controller/action',
        ],
        [
            'url' => ['modules/*'],
            'route' => 'dir/controller',
        ],
        [
            'url' => ['js/*'],
            'asset' => 'javascript',
        ],        
        [
            'url' => ['images/*'],
            'asset' => 'images',
        ],        
        [
            'url' => ['css/*'],
            'asset' => 'css',
        ],
        [
            'url' => ['fonts/*'],
            'asset' => 'fonts',
        ],                   
        [
            'url' => ['data/*'],
            'asset' => 'data',
        ],        
        [
            'url' => ['*/*/*'],
            'route' =>  'controller/action/id'
        ],
        // default
        [
            'url' => ['*/*'],
            'route' =>  'controller/action'
        ],
        // only_controller
        [
            'url' => ['*'],
            'route' =>  'controller'
        ],
    ],
    
    'cache' => [
        'path' => 'cache/'
    ],
    
    // データベースセッティング
    'database' => [
        'default' => [
            'host'          => 'IPアドレス',
            'port'          => 'ポート',
            'database'      => 'DB名',
            'user'          => 'ユーザー名',
            'password'      => 'パスワード',
            'sql_caching'   => true,
        ],
        'master' => [
            'host'          => 'IPアドレス',
            'port'          => 'ポート',
            'database'      => 'DB名',
            'user'          => 'ユーザー名',
            'password'      => 'パスワード',
            'sql_caching'   => true,
        ],
        'slave' => [
            'host'          => 'IPアドレス',
            'port'          => 'ポート',
            'database'      => 'DB名',
            'user'          => 'ユーザー名',
            'password'      => 'パスワード',
            'sql_caching'   => true,
        ],
    ],

    // ビュー(レンダー)設定
    'views' => [
        'path' => 'App/views',
        'default_layout' => 'layouts/main/container',
    ],
];


