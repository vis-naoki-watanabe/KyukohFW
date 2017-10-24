<?php
class Framework_Web_Application extends Framework_Base_Application
{
    protected $_route = null;
    protected static $_request = null;
    protected static $_auth = null;

    // {{{ public function init()

    /**
     * 初期化
     * @return void
     */
    public function init()
    {
        // タイムゾーン
        date_default_timezone_set($this->getConfig('timezone'));
        
        //ini_set("session.gc_probability", 0);
        //ini_set("session.gc_divisor", 1000);
        //ini_set("session.use_cookies", 0);
        //ini_set("session.use_trans_sid", 1);
        session_start();
        
        $this->router();
    }

    // }}}

    // {{{ public function getControllerFull()

    /**
     * クラス名(フル)を返却する
     * @return string
     */
    public function getControllerFull($suffix = false)
    {
        return $this->_route->getControllerClass($suffix);
    }
    // }}}

    // {{{ public function getController($path = false)

    /**
     * クラス名orクラスパスを返却する
     * @return string
     */
    public function getController($path = false)
    {
        if($path) {
            return $this->_route->getControllerPath();
        }
        return $this->_route->getController();
    }
    // }}}

    // {{{ public function getAction()

    /**
     * アクション名を返却する
     * @return void
     */
    public function getAction($suffix = false)
    {
        return $this->_route->getAction($suffix);
    }
    // }}}

    // {{{ public function router()

    /**
     * ルーティング走査
     * @return void
     */
    public function router()
    {
        $router = new Framework_Base_Router();
        $router->route('', $this->getConfig() );
        
        // ルーティング
        $this->_route = $router;
     
        // リクエスト
        $request = new Framework_Web_Request($this->_route->_params);
        self::setRequest($request);
    }
    
    // }}}
    
    // {{{ public function getRequest($object = false)

    /**
     * リクエストを返却する
     * @return App_Web_Request or Array
     */
    public static function getRequest($object = false)
    {
        if( !self::$_request ) return null;
        return $object?self::$_request:self::$_request->getRequest(); 
    }    
    // }}}
    
    // {{{ public static function addRequest($array)
    
    /**
     * リクエストを追加する
     * @return void
     */
    public static function addRequest($array)
    {
        if( !self::$_request ) return null;
        self::$_request->setRequest($array); 
    }    
    // }}}
    
    // {{{ public static function removeRequest($key)
    
    /**
     * キーに合致するリクエストを削除
     * @return void
     */
    public static function removeRequest($key)
    {
        if( !self::$_request ) return null;
        self::$_request->removeRequest($key);
    }    
    // }}}
    
    public static function setRequest($request)
    {
        self::$_request = $request;
    }
    
    public static function getAuth()
    {
        $auth = new Framework_Web_Auth();
        return $auth;        
    }
    
    // {{{ public function run()

    /**
     * 
     * @return void
     */
    public function run()
    {
        $controller_name = $this->getControllerFull(true);
        $action_name = $this->getAction(true);
        $controller = new $controller_name($this->getController(), $this->getAction());

        $controller->setRender(Framework_Web_Render::getDefaultLayout(), $this->getController(true), $this->getAction());
        // エラー画面用
        $controller->setErrorLayout(Framework_Web_Render::getErrorLayout());
        $controller->run($action_name);
    }
    
    // }}}

    // {{{ public function debug()

    /**
     * アプリケーション情報を表示
     * @return void
     */
    public function getInfo()
    {
        $request = print_r($this->getRequest(), true);
$text = <<< EOM
controller: {$this->getController()}
  full:{$this->getControllerFull()}
  path:{$this->getController(true)}
action: {$this->getAction()}
request: {$request}
EOM;
        echo $text;
    }

    // }}}
}

class Auth extends Framework_Web_Auth
{
}