<?php
$host = @$_SERVER['HTTP_HOST'];
$addr = @$_SERVER['REMOTE_ADDR'];

// 設定ファイル
$config = dirname(__FILE__).'/config/'.$host.'.php';

// フレームワークベースクラス
$app_file = dirname(__FILE__).'/Framework/App.php';
require_once($app_file);

$app = App::createWebApplication($config);
$app->run();
//$app->getInfo();

