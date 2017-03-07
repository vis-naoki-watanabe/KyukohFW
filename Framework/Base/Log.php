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

	//呼び出し元のクラス、関数の情報を知りたい場合
	//$dbg = debug_backtrace();
	//print_r($dbg);
        //$message.= print_r($dbg,true);
        
        if( is_object($message) ) {
            $message = self::var_dump($message);
        }
        
        $prefix = $prefix?sprintf("[%s]", $prefix):"";
        if( is_array($message) ) {
            $prefix = sprintf("[%s] %s", date('Y-m-d H:i:s'), $prefix);
            
            $message = print_r($message, true);
            $message = str_replace(array("\r\n","\r","\n"),PHP_EOL,$message);
            $data = str_replace(PHP_EOL, PHP_EOL.$prefix, PHP_EOL.$message);
        }
        else
        {
            $data = sprintf("[%s] %s%s", date('Y-m-d H:i:s'), $prefix, $message);
        }
        file_put_contents($path, $data . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    
    public function sql( $query, $time = null, $options = null )
    {
        $filename = App::choose($this->_paths, 'sql');
        if( !$filename ) return;
        $path = $this->_root_path . $filename;

	//$prefix = $prefix?sprintf("[:%s:]", $prefix):"";

        // DBから
        if( !App::choose($options, 'cache')) {
            $data = sprintf("[%s] %s" . PHP_EOL, date('Y-m-d H:i:s'), $query );
            if( $time ) {
                $data.= sprintf('query time:%s[sec]', $time);
            }
        }
        // キャッシュから
        else {
            $data = sprintf("[%s] [CACHE] => %s" . PHP_EOL, date('Y-m-d H:i:s'), $query );
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