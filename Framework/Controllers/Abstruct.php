<?php
class Framework_Controllers_Abstruct
{   
    protected $_default_action = 'index';
    protected $_variable    = null;
    protected $_render      = null;
    
    protected $_controller  = null;      /* string */
    protected $_action      = null;      /* string */
    protected $_user        = null;
    protected $_pagenator   = null;
    
    public function __construct($controller_name = null, $action_name = null)
    {
        $this->_controller  = $controller_name;
        $this->_action      = $action_name;
        $this->_variable    = $this->getVariable();
        $this->init();
    }
    
    public function __call($name, $args)
    {
        if ( strstr($name, 'Action') )
        {
            throw App::ex('action not found:'.$name, -1);
        }
    }

    public function __get( $name )
    {
        if( $name == 'controller' ||
            $name == 'action') {
            $property = '_'.$name;
            return $this->$property;
        }
        else if( $name == 'user' ) {
            return $this->getUser();
        }
        else if( $name == 'paginator' ) {
            return $this->getPaginator();
        }
        return $this->getVariable();
    }
    
    public function getUser() { return $this->getViewer(); }
    public function getViewer()
    {
        App::debug(get_class($this)."::line:".__LINE__);
        if(Auth::isLogin()) return Auth::getUser();
        return null;
    }
        
    // App毎のAbstructControllerでコンストラクター後に
    public function init()
    {
        // auth()メソッドがある場合は、auth():認証する
        if(method_exists($this, 'auth')) {
            $this->auth();
        }
    }
    
    public function getControllerName()
    {
        return $this->_controller;
    }
    public function getActionName()
    {
        return $this->_action;
    }
    
    public function run($action)
    {
        // ① App毎のAbstructControllerでターゲットAction前にやりたい処理
        $this->preDispatch();
        // ② App_Controller毎のターゲットAction前にやりたい処理
        $this->actionBefore();
        // ③ ターゲットAction
        try {
            $this->$action();
        } catch (Framework_Base_Exception $e) {
            echo "[".$e->getErrorCode()."]".$e->getMessage();
        }
        // ④ App_Controller毎のターゲットAction後にやりたい処理
        $this->actionAfter();
        // ⑤ App毎のAbstructControllerでターゲットAction後にやりたい処理
        $this->postDispatch();
    }
    
    // ① App毎のAbstructControllerでターゲットAction前にやりたい処理
    public function preDispatch()
    {
        // オーバーライド用
    }
    
    // ② App_Controller毎のターゲットAction前にやりたい処理
    public function actionBefore()
    {
        // オーバーライド用
    }
    
    /*
    // ③ ターゲットAction
    public function targetAction()
    {
    }
    */
    
    // ④ App_Controller毎のターゲットAction後にやりたい処理
    public function actionAfter()
    {
        // オーバーライド用
    }
    
    // ⑤ App毎のAbstructControllerでターゲットAction後にやりたい処理
    public function postDispatch()
    {
        // オーバーライド用
    }
    
    // TODO: requestをstaticで使用してるが
    // 参照渡し か AbstructController::$_request の方がいいのか考えてみる
    public function getRequest($key = null, $default = null, $array = true)
    {
        $request = Framework_Web_Application::getRequest(true);

        if( $key === null ) {
            if($array) {
                return $request->getRequest();
            }
            else {
                return $request;
            }
        }
        return $request->getRequest($key, $default);
    }
    
    public function setRender($layout = null, $controller = null, $action = null)
    {
        if(!$this->_render) {
            $this->_render = new Framework_Web_Render($layout, $controller, $action);
        }
        else {
            $this->_render->setRender($layout, $controller, $action);
        }
    }
    
    public function setLayout($layout)
    {
        $this->_render->setLayout($layout);
    }
    
    // 表示
    public function render($action = null, $controller = null)
    {
        $this->_variable->paginator = $this->getPaginator();
        
        $this->_render->setVariable($this->_variable);
        $this->_render->render($action, $controller);
    }
    
    public function redirect($path)
    {
        header('Location: '.$path);
        exit;
    }
    
    
    // アクション間クエリー受け渡し
    // memcacheがいいかもしれないけど取り敢えずsessionで管理
    // session_idをトークンIDっぽくダミーで受け渡し（見てない）
    public function setToken($params)
    {
        $_SESSION['memcached']['token'] = $params;
        return session_id();
    }
    
    public function getToken($key)
    {
        return $_SESSION['memcached']['token'];
    }
    
    private function htmlspecialchars_($val)
    {
        if( !$val || $val=='' ) return $val;
        if(!is_array($val)) {
            return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
        }
        else {
            foreach($val as &$v) {
                $v = htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
            }
        }
        return $val;
    }
    
    public function getPropReq($get_token_key = null)
    {
        // トークンから取得する
        if($get_token_key) {
            $token = $this->getRequest($get_token_key);
            if($token) {
                return $this->getToken($token);
            }
        }

	$ret = array();
	$prefix = "prop_";
        $params_prefix = "params_";
        
	foreach( $this->getRequest() as $key => $val_ )
	{
            $val = str_replace("\\\"", "\"", $val_);
            $val = $this->htmlspecialchars_($val);
            
            if( !preg_match('/^prop_/',$key) ) continue;
            //$new_key = str_replace( $prefix, "", $key );
            //$ret[$new_key] = $val;
            $new_key = str_replace( $prefix, "", $key );
            // paramsはまとめる
            if( !preg_match('/^params_/',$new_key) ) {
                $ret[$new_key] = $val;
            } else {
                $new_key = str_replace( $params_prefix, "", $new_key );
                $ret['params'][$new_key] = $val;
            }
	}
        if(isset($ret['params'])) {
            $ret['params'] = App::raw_json_encode( $ret['params'] );
        }
	return $ret;
    }
    
    public function getVariable()
    {
        if(!$this->_variable) {
            $this->_variable = new Framework_Web_Variable();
        }
        return $this->_variable;
    }
    
    public function getPaginator()
    {
        if(!$this->_pagenator) {
            $this->_pagenator = new Framework_Web_Paginator();
        }
        return $this->_pagenator;
    }
    
    // 継承元のクラスを返す
    public function getReflectionClass()
    {
        $trace = debug_backtrace();
        // ↓これで呼び出し元のクラスの情報を取得
        $ref = new ReflectionClass($trace[0]['object']);
        return $ref;
    }
    
    // 継承元のファイルのパスを返す
    public function getReflectionClassDir()
    {
        $ref = $this->getReflectionClass();
        return dirname($ref->getFilename());
    }

    // JSON返却関連
    public function sendJSON( $list, $options = null )
    {
        if( isset($_GET["callback"]) && $_GET["callback"] != "" && !preg_match("/[^_abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789]/", $_GET["callback"]) ){
          //die('bad or missing callback');
            $this->sendJSONP( $list );exit;
        }

        if($list) {
            $list = array(
                'result' => 'OK',
                'data'   => $list
            );
        }
        else {
            $list = array(
                'result' => 'NG'
            );
        }
        if($options) {
            $list = array_merge($list, $options);
        }
        
        //App::debug($list);

        //header('Access-Control-Allow-Origin: *');
        header('X-Content-Type-Options: nosniff');
        //header('X-XSS-Protection: 1; mode=block');
        header("Content-type: text/plain; charset=UTF-8");
        
        $json = App::raw_json_encode($list, $this->getRequest('indent'));
        $json = str_replace('\/','/',$json);
        $json = str_replace('\0','',$json);
        
        echo $json;
    }
    
    public function sendJSONP( $list )
    {        
        // JSONP用ヘッダ
        //header('Access-Control-Allow-Origin: *');
        header('X-Content-Type-Options: nosniff');
        //header('X-XSS-Protection: 1; mode=block');
        header("Content-type: text/javascript; charset=UTF-8");
        if( !isset($_GET["callback"]) || $_GET["callback"] == "" || preg_match("/[^_abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789]/", $_GET["callback"]) ){
          die('bad or missing callback');
        }
        $callback = $_GET["callback"];
        
        $json = App::raw_json_encode($list);
        $json = str_replace('\/','/',$json);
        echo $callback.'('.$json.')';
    }
}