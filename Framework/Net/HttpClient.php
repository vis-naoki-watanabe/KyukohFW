<?php
class Framework_Net_HttpClient
{
    // {{{ properties
    /**
     * singletonインスタンス
     */
    protected static $instance_ = null;
    /**
     * curl のハンドル
     * @var resource
     */
    protected $curl_ = null;
    /**
     * 通信結果のヘッダ部分
     * @var array
     */
    protected $headers_ = array();
    /**
     * 通信結果のボディー部分
     * @var string
     */
    protected $body_ = null;
    /**
     * baseUrl
     * @var string
     */
    protected $baseUrl_ = null;
    /**
     * 接続先 URL
     * @var string
     */
    protected $url_ = null;
    /**
     * curl_getinfo()の結果
     * @var array
     */
    protected $info_ = array();
    /**
     * info_のstdClass版
     * @var type
     */
    protected $info_object_ = null;
    /**
     * エラーを格納する配列
     * @var array
     */
    protected $error_ = array();
    /**
     * 直近のエラーコード
     * @var null
     */
    protected $errno_ = null;
    /**
     * 通信に使用するユーザエージェント
     * @var string
     */
    protected $user_agent_ = 'Synphonie';
    /**
     * デフォルトのポート番号
     * @var integer
     */
    protected $port_ = 80;
    /**
     * timeout(秒)
     * @var integer
     */
    protected $timeout_ = 120;
    /**
     * curl_muliti()を使うためのフラグ
     * @boolean
     */
    protected $multi_mode_ = false;
    // }}}
    /**
     * リクエストヘッダ
     * @var array
     */
    protected $requestHeaders_ = array();
    /**
     * リクエストヘッダを組み立てたもの
     * @var array
     */
    protected $requestHeader_ = array();
    /**
     * デフォルトのパラメータ
     * @var array
     */
    protected $defaultParameters_ = array();
    // }}}
    // {{{ protected function __construct()
    /**
     * 外部からのコンストラクタ呼び出しをブロック
     */
    protected function __construct()
    {
    }
    // }}}
    // {{{ protected function __clone()
    /**
     * cloneの呼び出しをブロック
     */
    final public function __clone()
    {
            throw new Enish_Exception( 'cannot clone this object');
    }
    // }}}
    // {{{ public static function getInstance()
    /**
     * 初回呼び出し時はinstanceを作り、2度目以降は前に作ったのを返す
     */
    public static function getInstance()
    {
            if ( !isset( self::$instance_)) {
                    self::$instance_ = new static();
            }
            return self::$instance_;
    }
    // }}}
    // {{{ public function get( $url, $params = array(), $options = array() )
    /**
     * GET リクエストを実行
     * @param	string $path		接続先 URL
     * @param	array $params	URL に付加するパラメータ
     * @param	array $options	curl のオプション
     * @return	boolean
     */
    public static function doGet($path, $params = array(), $options = array())
    {
        $obj = self::getInstance();
        $result = $obj->get($path, $params, $options);
        if($result) {
            return $obj->getBody();
        }
        return null;
    }
    public function get( $path, $params = array(), $options = array() ) {
            $this->curl_ = curl_init();
            $url = $this->baseUrl_;
            $url .= $path;
            $this->setCommonOptions();
            if ( is_array( $options ) && sizeof( $options ) > 0 ) {
                    curl_setopt_array( $this->curl_, $options );
            }
            // defaultParamをmerge
            foreach( $this->defaultParameters_ as $key => $value ) {
                    if ( !isset( $params[ $key] ) )
                            $params[$key] = $value;
            }
            if ( is_array( $params ) && sizeof( $params ) > 0 ) {
                    $url .= '?' . http_build_query( $params );
            }
            curl_setopt( $this->curl_, CURLOPT_URL, $url );
            $this->url_ = $url;
            return $this->execute();
    }
    // }}}
    // {{{ public function post( $path, $params = array(), $options = array() )
    /**
     * POS Tリクエストを実行
     * @param	string $path		接続先 URL
     * @param	array $params	URL に付加するパラメータ
     * @param	array $options	curl のオプション
     * @return	boolean
     */
    public static function doPost($path, $params = array(), $options = array())
    {
        $obj = self::getInstance();
        $result = $obj->post($path, $params, $options);
        if($result) {
            return $obj->getBody();
        } else {
            echo $obj->getError();
        }
        return null;
    }
    public function post( $path, $params = array(), $options = array() ) {
            $this->curl_ = curl_init();
            $url = $this->baseUrl_;
            $url .= $path;
            $this->setCommonOptions();
            if ( is_array( $options ) && sizeof( $options ) > 0 ) {
                    curl_setopt_array( $this->curl_, $options );
            }
            // defaultParamをmerge
            foreach( $this->defaultParameters_ as $key => $value ) {
                    if ( !isset( $params[ $key] ) )
                            $params[$key] = $value;
            }
            curl_setopt( $this->curl_, CURLOPT_POST, 1 );
            if(is_array($params)) {
                if ( is_array( $params ) && sizeof( $params ) > 0 ) {
                        $post_data = http_build_query( $params );
                        curl_setopt( $this->curl_, CURLOPT_POSTFIELDS, $post_data );
                }
            } else {
                curl_setopt( $this->curl_, CURLOPT_POSTFIELDS, $params );
            }
            curl_setopt( $this->curl_, CURLOPT_URL, $url );
            $this->url_ = $url;
            return $this->execute();
    }
    
    public static function doMultipartPost($path, $params = array(), $options = array())
    {
        $obj = self::getInstance();
        $result = $obj->multipartPost($path, $params, $options);
        if($result) {
            return $obj->getBody();
        } else {
            echo $obj->getError();
        }
        return null;
    }
    public function multipartPost( $path, $params = array(), $options = array() ) {
       
        
        $this->curl_ = curl_init();
        $url = $this->baseUrl_;
        $url .= $path;
        $this->setCommonOptions();
        if ( is_array( $options ) && sizeof( $options ) > 0 ) {
                curl_setopt_array( $this->curl_, $options );
        }
        // defaultParamをmerge
        foreach( $this->defaultParameters_ as $key => $value ) {
                if ( !isset( $params['params'][ $key] ) )
                        $params['params'][$key] = $value;
        }

        $multipart_data = $this->createMultipartData(@$params['params'], @$params['files'], @$params['datas']);
        
        curl_setopt( $this->curl_, CURLOPT_POST, 1 );
        curl_setopt( $this->curl_, CURLOPT_HTTPHEADER, $multipart_data['headers'] );
        curl_setopt( $this->curl_, CURLOPT_POSTFIELDS, implode("\r\n", $multipart_data['body'] ) );
        curl_setopt( $this->curl_, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $this->curl_, CURLOPT_URL, $url );
        $this->url_ = $url;
        return $this->execute();
    }
    // }}}
    // {{{ public function put( $path, $params = array(), $options = array() )
    /**
     * PUT リクエストを実行
     * @param	string $path		接続先 URL
     * @param	array $params	URL に付加するパラメータ
     * @param	array $options	curl のオプション
     * @return	boolean
     */
    public function put( $path, $params = array(), $options = array() ) {
            $this->curl_ = curl_init();
            $url = $this->baseUrl_;
            $url .= $path;
            $this->setCommonOptions();
            if ( is_array( $options ) && sizeof( $options ) > 0 ) {
                    curl_setopt_array( $this->curl_, $options );
            }
            // defaultParamをmerge
            foreach( $this->defaultParameters_ as $key => $value ) {
                    if ( !isset( $params[ $key] ) )
                            $params[$key] = $value;
            }
            curl_setopt( $this->curl_, CURLOPT_CUSTOMREQUEST, 'PUT' );
            if ( is_array( $params ) && sizeof( $params ) > 0 ) {
                    $post_data = http_build_query( $params );
                    curl_setopt( $this->curl_, CURLOPT_POSTFIELDS, $post_data );
            }
            curl_setopt( $this->curl_, CURLOPT_URL, $url );
            $this->url_ = $url;
            
            return $this->execute();
    }
    // }}}
    // {{{ public function delete( $path, $params = array(), $options = array() )
    /**
     * DELETE リクエストを実行
     * @param	string $path		接続先 URL
     * @param	array $params	URL に付加するパラメータ
     * @param	array $options	curl のオプション
     * @return	boolean
     */
    public function delete( $path, $params = array(), $options = array() ) {
            $this->curl_ = curl_init();
            $url = $this->baseUrl_;
            $url .= $path;
            $this->setCommonOptions();
            if ( is_array( $options ) && sizeof( $options ) > 0 ) {
                    curl_setopt_array( $this->curl_, $options );
            }
            // defaultParamをmerge
            foreach( $this->defaultParameters_ as $key => $value ) {
                    if ( !isset( $params[ $key] ) )
                            $params[$key] = $value;
            }
            curl_setopt( $this->curl_, CURLOPT_CUSTOMREQUEST, 'DELETE' );
            if ( is_array( $params ) && sizeof( $params ) > 0 ) {
                    $url .= '?' . http_build_query( $params );
            }
            curl_setopt( $this->curl_, CURLOPT_URL, $url );
            $this->url_ = $url;
            return $this->execute();
    }
    // }}}
    // {{{
    /**
     * デフォルトヘッダの取得
     */
    public function getRequestHeaders()
    {
            return $this->requestHeaders_;
    }
    // }}}
    // {{{ public function setRequestHeaders($key, $value)
    /**
     * デフォルトヘッダの設定
     *
     * @param $key
     * @param $value
     */
    public function setRequestHeaders($key, $value = null)
    {
        $headers = $key;
        if($headers && !is_array($headers)) {
            $headers = array(
                $key => $value
            );
        }
        
        foreach($headers as $header) {
            list($key, $value) = $header;
            
            // user-agentはcurl_setoptで設定するのでheaderからは抜く
            if ( strtolower( $key ) == 'user-agent') {
                    $this->setUserAgent( $value);
                    return;
            }
            if ( $value == null ) {
                    unset( $this->requestHeaders_[$key]);
            } else {
                    $this->requestHeaders_[$key] = $value;
            }
        }
        
        // headerを組み立てなおす
        $this->requestHeader_ = array();
        foreach( $this->requestHeaders_ as $k => $v ) {
                $this->requestHeader_[] = "$k: $v";
        }
    }
    // }}}
    // {{{ public function getDefaultParameters($key)
    /**
     * デフォルトパラメータの取得
     */
    public function getDefaultParameters($key)
    {
            if (isset( $this->defaultParameters_[$key]))
                    return $this->defaultParameters_[$key];
            return null;
    }
    // }}}
    // {{{ public function setDefaultParameters($key, $value)
    /**
     * デフォルトパラメータの設定
     *
     * @param $key
     * @param $value
     */
    public function setDefaultParameters($key, $value)
    {
            $this->defaultParameters_[$key] = $value;
    }
    // }}}
    // {{{ public function getHandle()
    /**
     * curlハンドルを返却
     * @return	resource
     */
    public function getHandle() {
            return $this->curl_;
    }
    // }}}
    // {{{ public function getRequestUrl()
    /**
     * リクエストした URL を返却
     * @return	string
     */
    public function getRequestUrl() {
            return $this->url_;
    }
    // }}}
    // {{{ public function getHeaders()
    /**
     * 通信結果のヘッダを返却
     * @return	array
     */
    public function getHeaders() {
            return $this->headers_;
    }
    // }}}
    // {{{ public function setHeaders( $headers )
    /**
     * 通信結果のヘッダをセット
     * @param	array $headers
     * @return	void
     */
    public function setHeaders( $headers ) {
            $this->headers_ = $headers;
    }
    // }}}
    // {{{ public function getBody()
    /**
     * 通信結果のボディーを返却
     * @return	string
     */
    public function getBody() {
            return $this->body_;
    }
    // }}}
    // {{{ public function setBody( $body )
    /**
     * 通信結果のボディーをセット
     * @param	string $body
     * @return	void
     */
    public function setBody( $body ) {
            $this->body_ = $body;
    }
    // }}}
    // {{{ public function getError()
    /**
     * エラーメッセージを返却
     * @return	array
     */
    public function getError() {
            return $this->error_;
    }
    // }}}
    // {{{ public function setError( $errmsg, $errno )
    /**
     * エラーメッセージをセット
     * @param	string $errmsg
     * @param	integer $errno
     * @return	void
     */
    public function setError( $errmsg ) {
            $this->error_ = $errmsg;
    }
    // }}}
    // {{{ public function resetError()
    /**
     * エラーメッセージをリセット
     * @return	void
     */
    public function resetError() {
            $this->error_ = array();
    }
    // }}}
    // {{{ public function getErrorNo()
    /**
     * 直近で発生したエラーのコードを返却する
     * @return null
     */
    public function getErrorNo()
    {
            return $this->errno_;
    }
    // }}}
    // {{{ protected function setErrorNo()
    /**
     * エラーコードを設定
     * @param $new_errno
     */
    protected function setErrorNo( $new_errno )
    {
            $this->errno_ = $new_errno;
    }
    // }}}
    // {{{ protected function resetErrorNo()
    /**
     * エラーコードをリセットする
     */
    protected function resetErrorNo()
    {
            $this->setErrorNo( null );
    }
    // }}}
    // {{{ public function getBaseUrl()
    public function getBaseUrl()
    {
            return $this->baseUrl_;
    }
    // }}}
    // {{{
    public function setBaseUrl( $baseUrl)
    {
            $this->baseUrl_ = $baseUrl;
    }
    // }}}
    // {{{ public function getInfo( $return_as_array = false )
    /**
     * 直近のリクエストのcurl_getinfo()を返却
     * @return	stdClass
     * @return	array
     */
    public function getInfo( $return_as_array = false ) {
            if ( $return_as_array ) {
                    return $this->info_;
            } else {
                    if ( is_null( $this->info_object_ ) ) {
                            $info_object = new stdClass();
                            foreach ( $this->info_ as $key => $val ) {
                                    $info_object->$key = $val;
                            }
                            $this->info_object_ = $info_object;
                    }
                    return $this->info_object_;
            }
    }
    // }}}
    // {{{ public function setInfo( $info )
    /**
     * 直近のリクエストのcurl_getinfo()をセット
     * @param	array $info
     * @return	void
     */
    public function setInfo( $info ) {
            $this->info_ = $info;
            $this->info_object_ = null;
    }
    // }}}
    // {{{ public function getUserAgent()
    /**
     * ユーザエージェントを返却
     * @return	string
     */
    public function getUserAgent() {
            return $this->user_agent_;
    }
    // }}}
    // {{{ public function setUserAgent( $user_agent )
    /**
     * ユーザーエージェントをセット
     * @param	string $user_agent
     * @return	void
     */
    public function setUserAgent( $user_agent ) {
            $this->user_agent_ = $user_agent;
    }
    // }}}
    // {{{ public function getTimeout()
    /**
     * timeout値を返却
     * @return	integer
     */
    public function getTimeout() {
            return $this->timeout_;
    }
    // }}}
    // {{{ public function setTimeout( $timeout )
    /**
     * timeout値をセット
     * @param	integer $timeout
     * @return	void
     */
    public function setTimeout( $timeout ) {
            $this->timeout_ = $timeout;
    }
    // }}}
    // {{{ public function getStatusCode()
    /**
     * リクエスト結果の HTTP ステータスコードを返却
     * @return	integer
     */
    public function getStatusCode() {
            return $this->getInfo()->http_code;
    }
    // }}}
    // {{{ public function getContentType()
    /**
     * リクエスト結果のコンテントタイプを返却
     * @return	string
     */
    public function getContentType() {
            return $this->getInfo()->content_type;
    }
    // }}}
    // {{{ public function isMultiMode()
    /**
     * マルチモードか?
     * @return	boolean
     */
    public function isMultiMode() {
            return $this->multi_mode_;
    }
    // }}}
    // {{{ public function setMultiMode()
    /**
     * マルチモードにセット
     * @return	void
     */
    public function setMultiMode() {
            $this->multi_mode_ = true;
    }
    // }}}
    // {{{ protected function execute()
    /**
     * 通信を実行して結果を取り込む
     * @return	boolean
     */
    protected function execute() {
            // マルチモードなら何もしない
            if ( $this->multi_mode_ ) return true;
            $this->resetError();
            $this->resetErrorNo();
            $result = curl_exec( $this->curl_ );
            $this->info_ = curl_getinfo( $this->curl_ );
            $this->info_object_ = null;
            $this->setError( curl_error( $this->curl_ ) );
            $this->setErrorNo( curl_errno( $this->curl_ ) );
            curl_close( $this->curl_ );
            // Eliminate multiple HTTP responses.
            do {
                    $parts = preg_split('|(?:\r?\n){2}|m', $result, 2);
                    $again = false;
                    if (isset($parts[1]) && preg_match("|^HTTP/1\.[01](.*?)\r\n|mi", $parts[1])) {
                            $result = $parts[1];
                            $again  = true;
                    }
            } while ($again);
            $parts = preg_split('|(?:\r?\n){2}|m', $result, 2);
            $this->headers_ = null;
            $this->body_ = null;
            if ( isset( $parts[0]))
                    $this->headers_ = preg_split( '/(\r\n|\r|\n)/', $parts[0] );
            if ( isset( $parts[1]))
                    $this->body_    = $parts[1];
            return true;
    }
    // }}}
    // {{{ protected function setCommonOptions()
    /**
     * 共通のcurlオプションをセット
     * @return	void
     */
    protected function setCommonOptions() {
            $options = array(
                    CURLOPT_HEADER         => 1,                    // ヘッダの内容も出力
                    CURLOPT_RETURNTRANSFER => 1,                    // curl_exec() の返り値を文字列で取得
                    CURLOPT_TIMEOUT        => $this->timeout_,
                    CURLOPT_USERAGENT      => $this->user_agent_,
                    CURLOPT_FAILONERROR    => false,                // 400 以上のコードが返ってきてもbodyを取得する
                    CURLOPT_FOLLOWLOCATION => 1,                    // Location ヘッダを辿る
                    CURLOPT_MAXREDIRS      => 3,                    // Location ヘッダを辿る最大値
                    CURLOPT_SSL_VERIFYPEER => 0,
                    CURLOPT_HTTPHEADER     => $this->requestHeader_,
            );
            curl_setopt_array( $this->curl_, $options );
    }
    // }}}
    
    public function createMultipartData($params = null, $files = null, $datas = null)
    {
        static $disallow = array("\0", "\"", "\r", "\n");
        $body = array();
        foreach ($params as $k => $v) {
            $k = str_replace($disallow, "_", $k);
            $body[] = implode("\r\n", array(
                "Content-Disposition: form-data; name=\"{$k}\"",
                "",
                filter_var($v), 
            ));
        }

        foreach ($files as $k => $v) {
            //if(false === $v = realpath(filter_var($v)))continue;
            $filename = $v;
            
            if(!is_file($v))continue;
            if(!is_readable($v))continue;
            $data = file_get_contents($v);
            
            $v = call_user_func("end", explode(DIRECTORY_SEPARATOR, $v));
            list($k, $v) = str_replace($disallow, "_", array($k, $v));
            $body[] = implode("\r\n", array(
                "Content-Disposition: form-data; name=\"{$k}\"; filename=\"{$v}\"",
                "Content-Type: ".mime_content_type($filename),
                "",
                $data,
            ));
        }
        foreach ($datas as $k => $data) {
            $type = 'text/palin';
            if(is_array($data)) {
                $type = App::choose($data, 'type', $type);
                $data = @$data['value'];
            }
            
            $body[] = implode("\r\n", array(
                "Content-Disposition: form-data; name=\"{$k}\"; filename=\"{$k}\"",
                "Content-Type: ".$type,
                "",
                $data,
            ));
        }

        do {
            $boundary = "---------------------" . md5(mt_rand() . microtime());
        } while (preg_grep("/{$boundary}/", $body));
        array_walk($body, function (&$part) use ($boundary) {
            $part = "--{$boundary}\r\n{$part}";
        });
        $body[] = "--{$boundary}--";
        $body[] = "";
        
        $curl_headers = array(
            "Expect: 100-continue",
            "Content-Type: multipart/form-data; boundary={$boundary}",
        );
            
        return array(
            'headers'   => $curl_headers,
            'body'      => $body
        );
    }
}