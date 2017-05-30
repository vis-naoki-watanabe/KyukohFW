<?php
class Framework_Controllers_Abstruct
{
    protected $_default_action = 'index';
    protected $_variable    = null;
    protected $_render      = null;
    
    protected $_controller  = null;      /* string */
    protected $_action      = null;      /* string */
    protected $_user        = null;
    
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
        else if(!$this->_variable) {
            $this->_variable = new Framework_Web_Variable();
        }
        return $this->_variable;
    }
    
    public function __construct($controller_name = null, $action_name = null)
    {
        $this->_controller = $controller_name;
        $this->_action = $action_name;
        $this->init();
    }
    
    public function getUser() { return $this->getViewer(); }
    public function getViewer()
    {
        if(Auth::isLogin()) return Auth::getUser();
        return null;
    }
        
    // App毎のAbstructControllerでコンストラクター後に
    public function init()
    {
        // オーバーライド用
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
}