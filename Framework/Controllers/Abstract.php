<?php
class Framework_Controllers_Abstract
{   
    protected $_default_action  = 'index';
    protected $_variable        = null;
    protected $_render          = null;
    
    protected $_controller      = null;      /* string */
    protected $_action          = null;      /* string */
    protected $_user            = null;
    protected $_pagenator       = null;
    protected $_error           = null;
    protected $_error_layout    = array();
    
    // 認証を除外するcontroller,action
    public $exclude_auth_action = null;
    
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
        /*
         * 固有メソッド
         */
        else if($name == 'user' || 
                $name == 'viewer' || 
                $name == 'paginator' || 
                $name == 'error'
        ) {
            $method = 'get'.ucfirst($name);
            return $this->$method();
        }
        return $this->getVariable();
    }
    
    /*
     * 固有メソッド
     */
    public function getUser() { return $this->getViewer(); }
    public function getViewer()
    {
        if(Auth::isLogin()) return Auth::getUser();
        return null;
    }
    
    public function getPaginator()
    {
        if(!$this->_pagenator) {
            $this->_pagenator = new Framework_Web_Paginator();
        }
        return $this->_pagenator;
    }
    
    public function getError()
    {
        if(!$this->_error) {
            $this->_error = new Framework_Web_Error();
        }
        return $this->_error;
    }
    
    public function getVariable()
    {
        if(!$this->_variable) {
            $this->_variable = new Framework_Web_Variable();
        }
        return $this->_variable;
    }
        
    // App毎のAbstractControllerでコンストラクター後に
    public function init()
    {
    }
    
    // 通常は何もしない
    public function auth()
    {
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
        // 認証実行
        $this->auth();
        
        // ① App毎のAbstractControllerでターゲットAction前にやりたい処理
        $this->preDispatch();
        // ② App_Controller毎のターゲットAction前にやりたい処理
        $this->actionBefore();
        // ③ ターゲットAction
        try {
            $this->$action();
        } catch (Framework_Base_Exception $e) {
            $this->error_render($e);
        }
        // ④ App_Controller毎のターゲットAction後にやりたい処理
        $this->actionAfter();
        // ⑤ App毎のAbstractControllerでターゲットAction後にやりたい処理
        $this->postDispatch();
    }
    
    // ① App毎のAbstractControllerでターゲットAction前にやりたい処理
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
    
    // ⑤ App毎のAbstractControllerでターゲットAction後にやりたい処理
    public function postDispatch()
    {
        // オーバーライド用
    }
    
    // TODO: requestをstaticで使用してるが
    // 参照渡し か AbstractController::$_request の方がいいのか考えてみる
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
    
    // リクエストを追加する
    public function addRequest($array)
    {
        $request = Framework_Web_Application::getRequest(true);
        Framework_Web_Application::addRequest($array);
    }
    
    // リクエストを削除する
    public function removeRequest($key)
    {
        Framework_Web_Application::removeRequest($key);
    }
    
    public function setActionRender($action)
    {
        $this->_render->setRender(null, null, $action);
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
        $this->_variable->error = $this->getError();
        
        $this->_variable->controller = $this->controller;       // viewで元のcontroller名を参照する場合：途中で置き換わった場合はorg_controllerで取り出す
        $this->_variable->action = $this->action;               // viewで元のaction名を参照する場合：途中で置き換わった場合はorg_actionで取り出す
        
        $this->_render->setVariable($this->_variable);
        $this->_render->render($action, $controller);
    }
    
    // 表示：エラー画面
    public function setErrorLayout($layout)
    {
        /*$layout = [
            'container'  => '',
            'controller' => '',
            'action'     => ''
        ];*/
        $this->_error_layout = $layout;
    }
    public function error_render($exception)
    {
        $this->_variable->error = $this->getError();
        $this->_variable->exception = $exception;
        
        $this->_variable->controller = $this->controller;       // viewで元のcontroller名を参照する場合：途中で置き換わった場合はorg_controllerで取り出す
        $this->_variable->action = $this->action;               // viewで元のaction名を参照する場合：途中で置き換わった場合はorg_actionで取り出す
        
        $this->_render->setVariable($this->_variable);
        $this->setRender(@$this->_error_layout['container'],@$this->_error_layout['controller'], @$this->_error_layout['action']);
        $this->_render->render();
    }
    
    public function redirect($path, $query = null)
    {
        if($query && is_array($query)) {
            $path.= '?'.http_build_query($query);
        }
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
        /*
        if(isset($ret['params'])) {
            $ret['params'] = App::raw_json_encode( $ret['params'] );
        }
         */
	return $ret;
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

    public function sendRawJSON($list)
    {
        $this->sendJSON($list, array("raw_result"=>true));
    }
    
    public function sendNgJSON($list)
    {
        $this->sendJSON(
                array(
                    "result" => "NG",
                    "data" => $list
                ), array("raw_result"=>true));
    }
    
    // JSON返却関連
    public function sendJSON( $list, $options = null )
    {
        if( isset($_GET["callback"]) && $_GET["callback"] != "" && !preg_match("/[^_abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789]/", $_GET["callback"]) ){
          //die('bad or missing callback');
            $this->sendJSONP( $list );exit;
        }
        
        $indent_flag = App::choose($options, 'indent', @$_REQUEST['indent']);
        // $optionsにtrueとするだけでもインデント扱い
        if($options === true) {
            $indent_flag = true;
        }
        $add_result  = !App::choose($options, 'raw_result', false);

        if($add_result) {
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
        } else {
            if(!is_array($list)) {
                $list = "[]";
            }
        }
        
        //App::debug($list);

        //header('Access-Control-Allow-Origin: *');
        header('X-Content-Type-Options: nosniff');
        //header('X-XSS-Protection: 1; mode=block');
        header("Content-type: text/plain; charset=UTF-8");
        
        $json = App::raw_json_encode($list, $indent_flag);
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