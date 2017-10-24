<?php
class Framework_Web_Render
{
    private $_layout = null;
    private $_controller = null;
    private $_action = null;
    private $_variable = null;
    
    public static function getDefaultLayout()
    {
        return App::choose(App::getConfig('views'), 'default_layout');
    }
    
    public static function getErrorLayout()
    {
        return App::choose(App::getConfig('views'), 'error_layout', array());
    }

    public function __construct( $layout, $controller = null, $action = null )
    {
        if( $layout && is_array($layout) ) {
            $params = $layout;

            if(isset($params['layout'])) {
                $this->_layout = $params['layout'];
            }

            if(isset($params['controller'])) {
                $this->_controller = $params['controller'];
            }

            if(isset($params['action'])) {
                $this->_action = $params['action'];
            }
        }
        else {
            $this->setRender($layout, $controller, $action);
        }
    }
    
    public function setRender($layout = null, $controller = null, $action = null)
    {
        if($layout) {
            $this->_layout = $layout;
        }
        if($controller) {
            $this->_controller = $controller;
        }
        if($action) {
            $this->_action = $action;
        }
    }

    public function setVariable($variable)
    {
        $this->_variable = $variable;
    }
    
    public function __get( $name )
    {
        if($name == 'org_action') {
            return $this->_variable->action;
        }
        else if($name == 'org_controller') {
            return $this->_variable->controller;
        }
        else if($name == 'action') {
            return $this->_action;
        }
        else if($name == 'controller') {
            return $this->_controller;
        }
        else if(!$this->_variable) {
            $this->_variable = new Framework_Web_Variable();
        }
        return $this->_variable->$name;
    }

    private function getLayoutPath($layout = null)
    {
        $root_path = App::choose(App::getConfig('views'), 'path');
        
        $layout = $layout?$layout:$this->_layout;
        return sprintf('%s/%s.php', $root_path, $layout);
    }

    private function getViewPath($action = null, $controller = null)
    {
        $root_path = App::choose(App::getConfig('views'), 'path');
        
        $controller = $controller?$controller:$this->_controller;
        $action = $action?$action:$this->_action;
        return sprintf('%s/%s/%s.php', $root_path, $controller, $action);
    }
    
    private function contents()
    {
        $this->template();
    }
    
    private function template($path=null)
    {
        // コンテンツを表示
        if($path === null) {
            $path = $this->getViewPath();
        }
        // 指定テンプレートを表示
        else {
            $root_path = App::choose(App::getConfig('views'), 'path');
            $path = sprintf('%s/%s', $root_path,$path.'.php');
        }
        if(is_file($path)) {
            require_once($path);
        }
        // レンダーファイル無し
        else {
            App::debug("not found:".$path);
        }
    }
    
    public function setLayout($layout)
    {
        $this->_layout = $layout;
    }
    
    public function render($action = null, $controller = null)
    {
        if($action) {
            $this->_action = $action;
        }
        
        if($controller) {
            $this->_controller = $controller;
        }
        
        $layout = sprintf('%s', $this->getLayoutPath());
        
        ob_start();
        require_once($layout);
        
        $buffer = ob_get_contents();
        ob_end_clean();

        echo $buffer;
    }
}

