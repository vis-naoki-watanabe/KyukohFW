<?php
class AppBase
{
    static protected $_config = null;
    static protected $_log = null;
    static protected $_app = null;
    
    public static function getRootPath()
    {
        return realpath(FRAMEWORK_DIR.'/../');
    }
    
    public static function getConfigObj($key)
    {
        return self::getConfig($key, true);
    }
    
    // ,区切りでキー階層指定
    public static function getConfig( $key = null, $convert_object = false )
    {
        if(!$key) {
            $ret = self::$_config;
        }
        else {
            /*
            if(preg_match("/\//", $key)) {
                $keys = explode("/",$key);
                $ret = self::$_config;
                foreach($keys as $key)
                {
                    $ret = App::choose($ret, $key);
                }
            }
            else {
                $ret = App::choose(self::$_config, $key);
            }*/
            $ret = App::choose(self::$_config, $key);
        }
        
        if(is_array($ret) && $convert_object) {
            $ret = (Object)$ret;
        }
        
        return $ret;
    }
    
    public static function createWebApplication($config=null)
    {
        return self::createApplication('Framework_Web_Application',$config);
    }

    public static function createConsoleApplication($config=null)
    {
        return self::createApplication('Framework_Console_Application',$config);
    }

    public static function createApplication($class,$config=null)
    {
        if( is_file($config) ) {
            self::$_config = require($config);
        }
        
        // ログ出力
        self::$_log = new Framework_Base_Log(App::choose(self::$_config, 'log'));
        
        self::$_app = new $class();
        return self::$_app;
    }
    
    public static function preout($message)
    {
        self::out($message, true);
    }
    
    public static function out($message, $pretag = false)
    {
        if( is_object($message) ) {
            $message = self::var_dump($message);
        }
        else if( is_array($message) ) {
            $message = print_r($message, true);
        }
        
        if($pretag) {
            $message = '<pre>'.PHP_EOL.$message.PHP_EOL.'</pre>'.PHP_EOL;
        }
        echo $message;
    }
    
    // =====================================================
    // ログ出力
    // =====================================================
    public static function log($type = null, $message = '', $prefix = "")
    {
        if( !$type ) return self::$_log;
        
        self::$_log->dump($message, $prefix, $type);
    }
    
    public static function debug($message = '', $prefix = "")
    {
        self::$_log->dump($message, $prefix, 'debug');
    }

    public static function sqlLog( $query, $time = null, $options = null )
    {
        self::$_log->sql($query, $time, $options);
    }
    
    public static function throw_ex( $message, $error_code = null )
    {
        throw self::ex($message, $error_code);
    }
    
    public static function ex( $message, $error_code = null )
    {
        return new Framework_Base_Exception( $message, $error_code );
    }
    
    // 不要文字除去or置換
    public static function strip($val, $flag = true)
    {
        if(!$val || $val == '' || is_array($val)) return $val;
        return $flag?htmlspecialchars($val, ENT_QUOTES, 'UTF-8'):$val;
    }

    // }}}

    // {{{ public static function newList()

    /**
     * 空のリストを返却する
     * @return Framework_Model_List
     */
    public static function newList($array = null)
    {
        return new Framework_Model_List($array);
    }

    // }}}
    
    public static function isStg()
    {
        return SERVER_TYPE == 'stg' || SERVER_TYPE == 'staging';
    }
    
    public static function isDev()
    {
        return SERVER_TYPE == 'debug' || SERVER_TYPE == 'dev' || SERVER_TYPE == 'builder' || SERVER_TYPE == 'developer';
    }
    
    public static function isBuilder()
    {
        returnSERVER_TYPE == 'builder';
    }
    
    const VIRTUAL_TIME_APP = 0;         // アプリの仮想日付
    const VIRTUAL_TIME_USER = 1;        // ユーザー毎の仮想日付
    public static function time($type = VIRTUAL_TIME_APP)
    {
        // アプリの仮想日付(取り敢えず実時間で返す)
        if($type == self::VIRTUAL_TIME_APP) return time();
        
        // ユーザー毎の仮想日付
        return time();
    }
    
    // アプリの仮想日付
    public static function timestamp($format = 'Y-m-d H:i:s')
    {
        return date($format, self::time(self::VIRTUAL_TIME_APP));
    }
    
    // ユーザー毎の仮想日付
    public static function user_timestamp($format = 'Y-m-d H:i:s')
    {
        return date($format, self::time(self::VIRTUAL_TIME_USER));
    }
    
    // {{{ public static function snippet( $name )

    /**
     * スニペットオブジェクトを返却
     * @param	string $name
     * @return	Framework_Snippet
     */
    public static function snippet( $name )
    {
            $snpt = Framework_Snippet::getInstance( $name );
            return $snpt;
    }

    // }}}

    // 文字列のバイト数を返却
    public static function strlen( $val, $type = 'byte' )
    {
        // バイト数
        if( $type == 'byte') {
            return strlen(bin2hex($val)) / 2;
        }
        // 文字数
        else {
            return mb_strlen($val, 'UTF8');
        }
    }    
    
    // ===============================================
    // CSRF対策用
    // ===============================================    
    const SALT_ENV_NAME = 'KYUKOH_APP';
    const HASH_ALGO = 'sha256';

    public static function CSRF_generate()
    {
        App::debug("session_id:".session_id());
        if (session_id() == '') {
            //throw new \BadMethodCallException('Session is not active.');
            App::debug('Session is not active.');
            exit;
        }
        /*$salt = getenv(self::SALT_ENV_NAME);
        if ($salt === false) {
            throw new \BadMethodCallException('Environment variable ' . self::SALT_ENV_NAME . ' is not set.');
        }*/
        return hash(self::HASH_ALGO, session_id() . self::SALT_ENV_NAME);
    }

    public static function CSRF_validate($token, $throw = false, $url = null )
    {
        //echo "[1:".self::CSRF_generate()."]<br>[2:".$token."]";
        App::debug("1:".self::CSRF_generate());
        App::debug("2:".$token);

        $success = ($token!==null && (self::CSRF_generate() === $token));
        if (!$success && $throw) {
            //throw new \RuntimeException('CSRF validation failed.', 400);
            App::debug('Session is not active.');
            if( $url ) {
                header('Location: '.$url);
            }
            exit;
        }
        return $success;
    }
    
    // PHP5.4:json_encode( $val, JSON_UNESCAPED_UNICODE); 
    // PHP5.3:raw_json_encode
    // UTFをエスケープしない
    public static function raw_json_encode($input, $indent_flag = null)
    {
	return preg_replace_callback(
	    '/\\\\u([0-9a-zA-Z]{4})/',
	    function ($matches) {
		return mb_convert_encoding(pack('H*',$matches[1]),'UTF-8','UTF-16');
	    },
            $indent_flag?App::indent(json_encode($input)):json_encode($input)
	);
    }
    
    //JSONを整形する関数:JSON_PRETTY_PRINT同等
    public static function indent($json) {
    /**
     * Indents a flat JSON string to make it more human-readable.
     *
     * @param string $json The original JSON string to process.
     *
     * @return string Indented version of the original JSON string.
     */
        $result      = '';
        $pos         = 0;
        $strLen      = strlen($json);
        $indentStr   = '  ';
        $newLine     = "\n";
        $prevChar    = '';
        $outOfQuotes = true;

        for ($i=0; $i<=$strLen; $i++) {

            // Grab the next character in the string.
            $char = substr($json, $i, 1);

            // Are we inside a quoted string?
            if ($char == '"' && $prevChar != '\\') {
                $outOfQuotes = !$outOfQuotes;

            // If this character is the end of an element,
            // output a new line and indent the next line.
            } else if(($char == '}' || $char == ']') && $outOfQuotes) {
                $result .= $newLine;
                $pos --;
                for ($j=0; $j<$pos; $j++) {
                    $result .= $indentStr;
                }
            }

            // Add the character to the result string.
            $result .= $char;

            // If the last character was the beginning of an element,
            // output a new line and indent the next line.
            if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
                $result .= $newLine;
                if ($char == '{' || $char == '[') {
                    $pos ++;
                }

                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indentStr;
                }
            }

            $prevChar = $char;
        }

        return $result;
    }
    
    // snake->Camel
    public static function camelize($str) {
        // PHPバージョンが5.4.32以上
        if(version_compare(PHP_VERSION, "5.4.32", ">=")) {
            $str = ucwords($str, '_');
            return str_replace('_', '', $str);
        }
        
        // PHPバージョンが5.4.32未満
        $arr = explode("_", $str);
        $buff = array();
        foreach($arr as $v) {
            $buff[]= ucwords($v);
        }
        return implode("", $buff);
    }

    // Camel->snake
    public static function snakize($str) {
        $str = preg_replace('/[a-z]+(?=[A-Z])|[A-Z]+(?=[A-Z][a-z])/', '\0_', $str);
        return strtolower($str);
    }

    public static function findOne($array, $key, $val)
    {
        return Framework_Base_Array::findOne($array, $key, $val);
    }
    
    public static function find($array, $key, $val)
    {
        return Framework_Base_Array::find($array, $key, $val);
    }
    
    // 配列か連想配列か
    public static function isVector(array $arr)
    {
        return array_values($arr) === $arr;
    }

    // シンプルた配列か
    public static function isPrimitiveVector($arr = null)
    {
        if(!$arr || !is_array($arr) || !self::isVector($arr)) return false;

        foreach($arr as $key => $val)
        {
          if(is_array($val)) return false;
        }
        return true;
    }
    
    // 改行コードを変換する
    public static function cr_replace()
    {
        if(func_num_args() < 1) return null;
        
        $args = func_get_args();
        if(func_num_args() == 1) {
            $dest = PHP_EOL;
            $target = func_get_arg(0);
        } else {
            $dest = func_get_arg(0);
            $target = func_get_arg(1);
        }
        return preg_replace('/(\r\n|\n|\r)/', $dest, $target);
    }
    
    // $flags: PREG_SPLIT_NO_EMPTY 空行は含めない
    public static function cr_explode($target, $flag = false)
    {
        $flags = $flag?0:PREG_SPLIT_NO_EMPTY;
        return preg_split('/(\r\n|\n|\r)/', $target, -1, $flags);
    }
    
    public static function explode($sep, $val)
    {
        if($val === null) return null;
        $val = str_replace(" ", "", $val);
        return explode($sep, $val);
    }

    /*
    public static function extractDate_test()
    {
        $list = array(
            "2017年12月13日(日) 19:00",
            "2017年12月13日(日)19:00",
            "2017年12月13日 19:00",
            "2017年12月13日19:00",
            "12月13日(日) 19:00",
            "12月13日(日)19:00",
            "12月13日(日)",
            "12月13日 19:00",
            "12月13日19:00",
            
            "2017/12/13(日) 19:00",
            "12/13(日) 19:00",
            "2017/12/13(日)19:00",
            "12/13(日)19:00",
            "2017/2/3(日)",
            "12/13(日)",
            "2017/12/13 19:00",
            "12/13 19:00",
            "2017/12/13",
            "12/13",
            
            "2017-12-13(日) 19:00",
            "12-13(日) 19:00",
            "2017-12-13(日)19:00",
            "12-13(日)19:00",
            "2017-12-13(日)",
            "12-13(日)",
            "2017-12-13 19:00",
            "12-13 19:00",
            "2017-12-13",
            "12-13"
        );
        
        foreach($list as $key => $text) {
            echo $key.":[".$text."]".PHP_EOL;
            $date = self::extractDate($text);
            extract($date, EXTR_PREFIX_SAME, "__");
            echo sprintf("%04d-%02d-%02d %02d:%02d", $year, $month, $day, $hour, $min).PHP_EOL.PHP_EOL;
        }
    }
    */
    
    public static function extractDate($text)
    {
        $year = date('Y');
        
        mb_regex_encoding("UTF-8");
        if( preg_match( "/([0-9]*)[\/|\-|年]([0-9]+)[\/|\-|月]([0-9]+)日*\(.+\)\s*([0-9]*):*([0-9]*)/u", $text, $ret ) ) {
            list($all, $year, $month, $day, $hour, $min) = $ret;
        } else if( preg_match( "/([0-9]*)[\/|\-|年]([0-9]+)[\/|\-|月]([0-9]+)日*\s*([0-9]*):*([0-9]*)/u", $text, $ret ) ) {
            list($all, $year, $month, $day, $hour, $min) = $ret;
        } else if( preg_match( "/([0-9]+)[\/|\-|月]([0-9]+)日*\(.+\)\s*([0-9]*):*([0-9]*)/u", $text, $ret ) ) {
            list($all, $month, $day, $hour, $min) = $ret;
        } else if( preg_match( "/([0-9]+)[\/|\-|月]([0-9]+)日*\s*([0-9]*):*([0-9]*)/u", $text, $ret ) ) {
            echo "[".__LINE__."]";
            list($all, $month, $day, $hour, $min) = $ret;
        } else {
            list($month, $day, $hour, $min) = array(0,0,0,0);
        }
        
        $ret = array(
            'year'  => $year!=''?$year:date('Y'),
            'month' => $month!=''?$month:0,
            'day'   => $day!=''?$day:0,
            'hour'  => $hour!=''?$hour:0,
            'min'   => $min!=''?$min:0
        );
        return $ret;
    }

    //$files_: パス単一指定 or パス複数(array)指定 
    //$files_:「*」が含まれる場合は直下のファイル一式
    public static function getFileInfo($files_)
    {
        if(!$files_) return null;
        $files = $files_;
        if(!is_array($files)) {
            $files = array($files);
        }
        
        $file_list = array();
        foreach($files as $file)
        {
            if(preg_match("/\*/", $file)) {
                foreach (glob($file) as $cnt => $v) {
                    $file_list[] = array(
                        'parent' => $file,
                        'target' => $v
                    );
                }
            }
            else {
                $file_list[] = array(
                    'target' => $file
                );
            }
        }
        
        $infos = array();
        foreach($file_list as $val)
        {
            $file = $val['target'];
            if(!file_exists($file)) {
                $infos[] = array(
                    'path' => $file,
                    'exists' => false,
                );
                continue;
            }
            
            $file_size = null;
            if(is_file($file)) {
                $file_size = filesize($file);
            }

            $perms = fileperms($file);
            $perms_oct = substr(sprintf('%o', $perms), -4);

            switch ($perms & 0xF000) {
                case 0xC000: // ソケット
                    $type_detail = 's';
                    $type = '-';
                    break;
                case 0xA000: // シンボリックリンク
                    $type_detail = 'l';
                    $type = 'l';
                    break;
                case 0x8000: // 通常のファイル
                    $type_detail = 'r';
                    $type = '-';
                    break;
                case 0x6000: // ブロックスペシャルファイル
                    $type_detail = 'b';
                    $type = '-';
                    break;
                case 0x4000: // ディレクトリ
                    $type_detail = 'd';
                    $type = 'd';
                    break;
                case 0x2000: // キャラクタスペシャルファイル
                    $type_detail = 'c';
                    $type = '-';
                    break;
                case 0x1000: // FIFO パイプ
                    $type_detail = 'p';
                    $type = '-';
                    break;
                default: // 不明
                    $type_detail = 'u';
                    $type = '-';
            }

            $permission = '';
            // 所有者
            $permission .= (($perms & 0x0100) ? 'r' : '-');
            $permission .= (($perms & 0x0080) ? 'w' : '-');
            $permission .= (($perms & 0x0040) ?
                            (($perms & 0x0800) ? 's' : 'x' ) :
                            (($perms & 0x0800) ? 'S' : '-'));

            // グループ
            $permission .= (($perms & 0x0020) ? 'r' : '-');
            $permission .= (($perms & 0x0010) ? 'w' : '-');
            $permission .= (($perms & 0x0008) ?
                          (($perms & 0x0400) ? 's' : 'x' ) :
                           (($perms & 0x0400) ? 'S' : '-'));

            // 全体
            $permission .= (($perms & 0x0004) ? 'r' : '-');
            $permission .= (($perms & 0x0002) ? 'w' : '-');
            $permission .= (($perms & 0x0001) ?
                            (($perms & 0x0200) ? 't' : 'x' ) :
                          (($perms & 0x0200) ? 'T' : '-'));

            $pathinfo = pathinfo($file);
            $owner = posix_getpwuid(fileowner($file));
            $group = posix_getgrgid(filegroup($file));
            $info = array(
                'path' => App::choose($pathinfo, 'dirname')."/".App::choose($pathinfo, 'basename'),
                'dir'  => App::choose($pathinfo, 'dirname'),
                'name' => App::choose($pathinfo, 'basename'),
                'size'           => $file_size,
                'type'           => $type,
                'type_detail'    => $type_detail,
                'permission'     => $permission,
                'permission_oct' => $perms_oct,
                'owner'          => App::choose($owner, 'name'),
                'group'          => App::choose($group, 'name'),
                'exists'         => true,
            );
            
            if(isset($val['parent'])) {
                $info['parent'] = $val['parent'];
            }
            
            $infos[] = $info;
        }
        return count($files_)==1?$infos[0]:$infos;
    }

    /**
    * バイト数をフォーマットする
    * @param integer $bytes
    * @param integer $precision
    * @param array $units
    */
   public static function formatBytes($bytes, $precision = 2, array $units = null)
   {
       if($bytes===null || $bytes == '') return '';
       
       if ( abs($bytes) < 1024 )
       {
           $precision = 0;
       }

       if ( is_array($units) === false )
       {
           $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
       }

       if ( $bytes < 0 )
       {
           $sign = '-';
           $bytes = abs($bytes);
       }
       else
       {
           $sign = '';
       }

       $exp   = floor(log($bytes) / log(1024));
       $unit  = $units[$exp];
       $bytes = $bytes / pow(1024, floor($exp));
       $bytes = sprintf('%.'.$precision.'f', $bytes);
       return $sign.$bytes.' '.$unit;
   }
   
   public static function getPath($path = '')
   {
       $root = realpath(FRAMEWORK_DIR.'/../');
       return sprintf('%s/%s', $root, $path);
   }
   
   /**
    * 
    * 正規表現でUserAgent分岐/OS
    * 
    * @param string $user_agent
    * @return string
    */
   public static function getOs($user_agent = '')
   {
       if (empty($user_agent)) {
           // ユーザエージェント
           $user_agent = $_SERVER['HTTP_USER_AGENT'];
       }

       if (preg_match('/Windows NT 10.0/', $user_agent)) {
           //$os = 'Windows 10';
           $os = 'Windows';
       } elseif (preg_match('/Windows NT 6.3/', $user_agent)) {
           //$os = 'Windows 8.1 / Windows Server 2012 R2';
           $os = 'Windows';
       } elseif (preg_match('/Windows NT 6.2/', $user_agent)) {
           //$os = 'Windows 8 / Windows Server 2012';
           $os = 'Windows';
       } elseif (preg_match('/Windows NT 6.1/', $user_agent)) {
           //$os = 'Windows 7 / Windows Server 2008 R2';
           $os = 'Windows';
       } elseif (preg_match('/Windows NT 6.0/', $user_agent)) {
           //$os = 'Windows Vista / Windows Server 2008';
           $os = 'Windows';
       } elseif (preg_match('/Windows NT 5.2/', $user_agent)) {
           //$os = 'Windows XP x64 Edition / Windows Server 2003';
           $os = 'Windows';
       } elseif (preg_match('/Windows NT 5.1/', $user_agent)) {
           //$os = 'Windows XP';
           $os = 'Windows';
       } elseif (preg_match('/Windows NT 5.0/', $user_agent)) {
           //$os = 'Windows 2000';
           $os = 'Windows';
       } elseif (preg_match('/Windows NT 4.0/', $user_agent)) {
           $os = 'Microsoft Windows NT 4.0'; 
       } elseif (preg_match('/Mac OS X ([0-9\._]+)/', $user_agent, $matches)) {
           //$os = 'Macintosh Intel ' . str_replace('_', '.', $matches[1]);
           $os = 'Macintosh';
       } elseif (preg_match('/OS ([a-z0-9_]+)/', $user_agent, $matches)) {
           //$os = 'iOS ' . str_replace('_', '.', $matches[1]);
           $os = 'iOS';
       } elseif (preg_match('/Android ([a-z0-9\.]+)/', $user_agent, $matches)) {
           //$os = 'Android ' . $matches[1];
           $os = 'Android';
       } elseif (preg_match('/Linux ([a-z0-9_]+)/', $user_agent, $matches)) {
           //$os = 'Linux ' . $matches[1];
           $os = 'Linux';
       } else {
           $os = 'unidentified';
       }

       return $os;
   }
   
    public static function isNumeric($num)
    {
        return is_numeric($num);
    }

    public static function isInt($num)
    {
        return (is_numeric($num) && intval($num)==$num);
    }

    public static function isFloat($num)
    {
        return (is_numeric($num) && floatval($num)==$num);
    }
    
    public static function filterArray($array, $allow_field_string = 'all', $default = null)
    {
        // 全て返却
        if($allow_field_string == 'all') return $array;
        // 指定フィールドがないので$defaultを返却
        if(!$allow_field_string) return $default;

        if(is_array($allow_field_string)) {
            $allow_fields = $allow_field_string;
        } else {
            $allow_fields = explode(',', $allow_field_string);
        }
        
        $ret = array();
        if($allow_fields && is_array($allow_fields)) {
            foreach($allow_fields as $id => $key) {
                if(array_key_exists($key, $array)) {
                    $ret[$key] = $array[$key];
                }
            }
        }
        return $ret;
    }
    
    // ================================================
    //  汎用ツール系関連
    // ================================================
    // {{{ public static function choose( $arr, $key, $default = null )

    /**
     * 配列内に指定したキーがあればその値を返却し、なければデフォルト値を返却する
     * @param type $arr 調べる配列
     * @param type $key キー
     * @param type $default デフォルト値(デフォルト引数はnull)
     */
    public static function choose( $arr, $key, $default = null )
    {
        if(preg_match("/\//", $key)) {
            $keys = explode("/",$key);
        } else {
            $keys = array($key);
        }
        
        $flag = true;
        $ret = $arr;
        foreach($keys as $key)
        {
            if ( $ret && is_array( $ret ) && array_key_exists( $key, $ret ) ) {
                $ret = $ret[$key];
            } else {
                $flag = false;
            }
        }
        
        return $flag?$ret:$default;
    }
    
    //以前のアルゴリズム
    public static function choose_( $arr, $key, $default = null )
    {    
        if ( is_array( $arr ) && array_key_exists( $key, $arr ) ) {
            return $arr[$key];
        }
        return $default;
    }

    // }}}
    
    public static function serialize($data, $base64_flag = false)
    {
        $serialize = serialize($data);
        if( $base64_flag ) {
            $serialize = base64_encode($serialize);
        }
        return $serialize;
    }
    
    public static function unserialize($serialize, $base64_flag = false)
    {
        if( $base64_flag ) {
            $serialize = base64_decode($serialize);
        }
        $data = unserialize($serialize);
        return $data;
    }
    
    public static function http_build_query($array, $url=null)
    {
        $query = http_build_query($array);
        $url = $url?($url.((strpos($url,'?')===false)?'?':'&')):"";
        $url.= $query;
        return $url;
    }
    
    public static function access_log()
    {
        return @$_SERVER['REQUEST_METHOD']." ".@$_SERVER['REQUEST_URI'];
    }
}