<?php
require(dirname(__FILE__).'/AppBase.php');
require(dirname(__FILE__).'/Library.php');

/**
 * クラスオートローダー
 * @return void
 */
spl_autoload_register(function ($class_name)
{
    // $this_path = dirname(__FILE__).'/../';
    // FRAMEWORK_DIR:configで設定
    $this_path = FRAMEWORK_DIR.'/../';
    if ( !strstr($class_name, 'App_Controller') && strstr($class_name, 'Controller') && !strstr($class_name, 'Framework_') )
    {
        $class_path = str_replace('_', '/', $class_name).'.php';
        $virtual_path = $this_path."App/controllers/".$class_path;
        $real_path = realpath($virtual_path);
    }
    else
    {
	$class_path = str_replace('_', '/', $class_name).'.php';
	// Util系のライブラリの走査パス
	$arr = explode('_',$class_name);
	if( $arr[0] == 'App' ) {
            $virtual_path = $this_path.str_replace('App','App/models',$class_path);
        } else if( $arr[0] == 'Framework' ) {
            $virtual_path = $this_path.$class_path;    
        } else {
            $virtual_path = $this_path."Utils/".$class_path;
	}
	//echo "[[autoload: path:".$virtual_path."]]";
	$real_path = realpath($virtual_path);
    }

    if ( is_file($real_path) )
    {
        include $real_path;
    }
});

class App extends AppBase
{
}