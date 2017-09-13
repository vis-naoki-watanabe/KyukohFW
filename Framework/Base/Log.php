<?php
class Framework_Base_Log
{
    protected $_root_path = null;
    protected $_paths = null;
    
    public function __construct($paths = null)
    {
        $this->_root_path = FRAMEWORK_DIR.'/../';
        $this->_paths = $paths;
    }

    // }}}

    public function debug( $message = '', $prefix = "" )
    {
        $this->dump($message, $prefix, 'debug');
    }

    public function exception( $message = '', $prefix = "" )
    {
        $this->dump($message, $prefix, 'exception');
    }

    public function warning( $message = '', $prefix = "" )
    {
        $this->dump($message, $prefix, 'warning');
    }

    public function error( $message = '', $prefix = "" )
    {
        $this->dump($message, $prefix, 'error');
    }

    public function dump( $message = '', $prefix = "", $log_file_name = 'debug' )
    {
        $filename = App::choose($this->_paths, $log_file_name);
        if( !$filename ) return;
        $path = $this->_root_path . $filename;
        
        if( is_object($message) ) {
            $message = self::var_dump($message);
        }
        
        $date = date('Y-m-d H:i:s');
        $prefix = $prefix?sprintf("[%s]", $prefix):"";
        
        //呼び出し元のクラス、関数の情報を知りたい場合
        $data = sprintf("[%s] %s%s", $date, $prefix, $this->getCaller());
        
        if( is_array($message) ) {
            $prefix = sprintf("[%s] %s", $date, $prefix);
            
            $message = print_r($message, true);
            $message = str_replace(array("\r\n","\r","\n"),PHP_EOL,$message);
            $data.= str_replace(PHP_EOL, PHP_EOL.$prefix, PHP_EOL.$message);
        }
        else
        {
            $data.= PHP_EOL;
            $data.= sprintf("[%s] %s%s", $date, $prefix, $message);
        }
        
        file_put_contents($path, $data . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    
    //呼び出し元情報
    public function getCaller($type = null)
    {
        $dbg = debug_backtrace();
        
        $ret = "";
        $hit = false;
        $line = "";
        if($type === null) {
            foreach($dbg as $params) {
                if($hit) {
                    $ret.= sprintf("[Log Call] %s::%s(line:%s)", @$params['class'],@$params['function'],$line);
                    break;
                }
                // AppBase::debug or AppBase::sqlの一つ前を呼び出し元とする
                if(@$params['class']=="AppBase" && @$params['function']=="debug") {
                    $hit = true;
                    $line = @$params['line'];
                }
            }
        } else if($type === "sql") {
            foreach($dbg as $params) {
                if($hit && @$params['class'] != "Framework_Model_Abstract") {
                    $ret.= sprintf("[Log Call] %s::%s(line:%s)", @$params['class'],@$params['function'],$line);
                    break;
                }
                // AppBase::debug or AppBase::sqlの一つ前を呼び出し元とする
                if(@$params['class'] == "Framework_Model_Abstract") {
                    $hit = true;
                    $line = @$params['line'];
                }
            }
        } else if($type == "functions") {
            foreach($dbg as $params) {
                $ret.= sprintf("[Log Call] %s::%s(line:%s)", @$params['class'],@$params['function'],@$params['line']);
                if($ret != "") {
                    $ret.= PHP_EOL;
                }
            }
        } else if($type = "all") {
            $ret = print_r($dbg, true);
        }
        return $ret;
    }
    
    public function sql( $query, $time = null, $options = null )
    {
        $filename = App::choose($this->_paths, 'sql');
        if( !$filename ) return;
        $path = $this->_root_path . $filename;

	//$prefix = $prefix?sprintf("[:%s:]", $prefix):"";

        $date = date('Y-m-d H:i:s');
        
        //呼び出し元のクラス、関数の情報を知りたい場合
        $data = sprintf("[%s] %s", $date, $this->getCaller("sql")).PHP_EOL;
        
        // DBから
        if( !App::choose($options, 'cache')) {
            $data.= sprintf("[%s] %s" . PHP_EOL, $date, $query );
            if( $time ) {
                $data.= sprintf('query time:%s[sec]', $time);
            }
        }
        // キャッシュから
        else {
            $data.= sprintf("[%s] [CACHE] => %s" . PHP_EOL, $date, $query );
            if(App::choose($options, 'cache_key')) {
                $data.= sprintf('sql cache key:%s', App::choose($options, 'cache_key'));
            }
        }
        
	file_put_contents($path, $data . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    
    public static function var_dump($obj)
    {
        ob_start();
        var_dump($obj);
        $ret = ob_get_contents();
        ob_end_clean();
        return $ret;
    }
}