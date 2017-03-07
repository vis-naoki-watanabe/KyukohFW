<?php
class Framework_Base_Router
{
    protected $_config = null;

    public $_sub_dir = array();
    public $_controller = '';
    public $_action = '';
    public $_routingPath = '';
    public $_params = array();
    
    public function route($path, $config = null)
    {
        $this->_config = $config;        
        $routing = $this->_getParamsRewrite('');

        return $routing;
    }
    
    // {{{ public function getController()

    /**
     * コントローラー名を返却する
     * @return string
     */
    public function getController()
    {
        return $this->_controller;
    }
    
    // }}}

    // {{{ public function getController()

    /**
     * コントローラーのクラス名を返却する
     * @return string
     */
    public function getControllerClass($suffix = false)
    {
        $params = $this->_sub_dir;
        $params[] = $this->_controller;
        return ucwords(implode('_', $params), '_').($suffix?'Controller':'');
    }
    
    // }}}

    // {{{ public function getController()

    /**
     * コントローラーのパスを返却する
     * @return string
     */
    public function getControllerPath()
    {
        $params = $this->_sub_dir;
        $params[] = $this->_controller;
        return implode('/', $params);
    }
    
    // }}}

    // {{{ public function getAction()

    /**
     * アクショッ名を返却する
     * @return string
     */
    public function getAction($suffix = false)
    {
        return $this->_action.($suffix?'Action':'');
    }
    
    // }}}

    // {{{ public function _getParamsRewrite($path)

    /**
     * ルーティング走査
     * @return void
     */
    protected function _getParamsRewrite($path)
    {
        $default  = App::choose($this->_config, 'default');
        $settings = App::choose($this->_config, 'routing');
                
        // get path from url
        $paramStr = $_SERVER['REQUEST_URI'];
        $paramStr = trim($paramStr, '/');
        $paramStr = str_replace(trim($path, '/'), '', $paramStr);
        $paramStr = str_replace('index.php', '', $paramStr);
        $paramStr = preg_replace('/\?.*/', '', $paramStr);
        $paramStr = trim($paramStr, '/');

        $controller = '';
        $action = '';
        $match = false;
        
        if( $settings ) {
            foreach($settings as $setting) {
                $urls = App::choose($setting, 'url');
                foreach ($urls as $url) {
                    $req = trim(trim($url), '/');
                    if ($req == '*') {
                        $match = true;
                    } else if ($req == '') {
                        if ($paramStr == '') {
                            $match = true;
                        }
                    } else {
                        // Segment match
                        $req = str_replace('*/', '[^/]+/', $req);
                        // Forward match
                        $req = preg_replace('|/\*$|', '/.*', $req);
                        // If setting of "request" section is contained in url, enable setting of correspond section of routing.ini
                        if (preg_match('|^' . $req . '$|', $paramStr)) {
                            $match = true;
                        }
                    }
                }
                
                // Get setting of route when a request path matches
                if ($match) {
                    foreach ($setting as $key => $val) {
                        if ($key == 'default_controller') {
                            $this->_controller = $val;
                        } else if ($key == 'default_action') {
                            $this->_action = $val;
                        } else if ($key == 'controller') {
                            $controller = $val;
                        } else if ($key == 'action') {
                            $action = $val;
                        } else if ($key == 'route') {
                            $this->_routingPath = trim($val, '/');
                        } else if($key == 'asset') {
                            $this->_controller = 'asset';
                            $this->_action = $val;
                            $this->_params['path'] = $paramStr;
                        } else {
                            $this->_params[$key] = $val;
                        }
                    }
                    break;
                }
            }
        }

        // Explode "route" setting by "/"
        //$this->_routingPath = preg_replace('/dir\//', '', $this->_routingPath);
        $routingParams = explode('/', $this->_routingPath);

        // Explode url parameter by "/"
        $params = array();
        if ($paramStr !== '' && $this->_controller != 'asset') {
            $params = explode('/', $paramStr);
        }

        $i = 0;     
        foreach ($params as $idx => $param) {
            if (array_key_exists($idx, $routingParams)) {
                $key = $routingParams[$idx];
                if ( preg_match('/dir/', $key) ) {
                    $this->_sub_dir[] = $param;
                } else if ($key == 'controller') {
                    if ($controller == '') {
                        $controller = $param;
                    }
                } else if ($key == 'action') {
                    if ($action == '') {
                        $action = $param;
                    }
                } else if (!isset($this->_params[$key]) || $this->_params[$key] = '') {
                    $this->_params[$key] = $param;
                    $i++;
                }
            } else {
                $key = $i;
                $val = $param;
                if (strpos($param, '=')) {
                    $splited = explode('=', $param);
                    $key = $splited[0];
                    $val = $splited[1];
                }
                $this->_params[$key] = $val;
                $i++;
            }
        }
        
        if ($controller != '') {
            $this->_controller = $controller;
        }
        
        if ( $this->_controller == '' ) {
            $this->_controller = App::choose($default, 'controller');
        }
        
        if ($action != '') {
            $this->_action = $action;
        }
        
        if ( $this->_action == '' ) {
            $this->_action = App::choose($default, 'action');;
        }

        if( isset($this->_params['url']) ) {
            unset($this->_params['url']);
        }
    }
    
    // }}}
}
