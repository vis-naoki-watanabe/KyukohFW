<?php
class Framework_Orm_Idiorm
{
    private static $dbhs_ = array();
    
    protected $is_connected_ = null;

    /**
     * スキーマ
     * @var array
     */
    protected $config_ = array();
    protected $dbh_ = null;    

    
    public static function getInstance($type, $config)
    {
        // 接続済みならそれを使う
        if ( isset(self::$dbhs_[$type]) ) {
            if(self::$dbhs_[$type]->is_connected_) {
                return self::$dbhs_[$type];
            } else {
                unset(self::$dbhs_[$type]);
            }
        }
        
        // 接続
        $obj = new self();
        $obj->config_ = $config;
        self::$dbhs_[$type] = $obj;
        
        return self::$dbhs_[$type];
    }

    // {{{ public function dbh()

    /**
     * PDO オブジェクトを返却
     * @return PDO
     */
    public function dbh()
    {
        if ( ! $this->is_connected_ ) {
            $this->connect(
                App::choose($this->config_, 'host'),
                App::choose($this->config_, 'port'),
                App::choose($this->config_, 'database'),
                App::choose($this->config_, 'user'),
                App::choose($this->config_, 'password'),
                App::choose($this->config_, 'driver')
            );
        }
        return $this->dbh_;
    }

    // }}}

    // {{{ public function connect( $host, $port, $database, $user, $password, $driver=null )

    /**
     * DB に接続する
     * @param	string $host
     * @param	integer $port
     * @param	string $database
     * @param	string $user
     * @param	string $password
     * @param	string $driver=null
     * @return	void
     */
    public function connect( $host, $port, $database, $user, $password, $driver=null )
    {
        if ( is_null($driver) ) $driver = 'mysql';
              
        $dsn = sprintf('mysql:dbname=%s;host=%s;port=%s;charset=utf8', $database, $host, $port);
        //ORM::configure('mysql:dbname=dbname;host=localhost:3306:/tmp/mysql.sock;charset=utf8');
        ORM::configure($dsn);
        ORM::configure('username', $user);
        ORM::configure('password', $password);
        
        // キャッシュ有効
        ORM::configure('caching', true);
        // INSERT, UPDATEでキャッシュをクリア
        ORM::configure('caching_auto_clear', true);
        
        $this->is_connected_ = true;
    }
    
    
    
    
    
    
    
        /**
	 * DB サーバタイプ
	 * @var string
	 */
	protected $db_type_ = 'default';


	/**
	 * Framework_Dbmgr インスタンス
	 * @var array Framework_DbMgr
	 */
	private $dbh_list_ = array();
	
	/**
	 * コネクションのプール
	 * @var array
	 */
	private static $dbh_pool_ = array();

	/**
	 * options
	 * @var array
	 */
	private $options_ = array();

    // {{{ public function dbh( $type = null, $provider_id=PROVIDER_ID, $app_name=APP_NAME )

	// {{{ public function isMaster()

	/**
	 * master か?
	 * @return	boolean
	 */
	public function isMaster()
	{
		return $this->is_master_;
	}

	// }}}



	// {{{ public function setMaster()

	/**
	 * master にセット
	 * @return	void
	 */
	public function setMaster()
	{
		$this->is_master_ = true;
	}

	// }}}

	// {{{ public function getError()

	/**
	 * エラーメッセージを返却
	 * @return	string
	 */
	public function getError()
	{
		return $this->error_;
	}

	// }}}

	// {{{ public function hasError()

	/**
	 * エラーメッセージがあるか?
	 * @return	boolean
	 */
	public function hasError()
	{
		return ( $this->error_ != '' );
	}

	// }}}

	// {{{ public function getConfigPath( $category, $type = null, $provider_id=PROVIDER_ID, $app_name=APP_NAME )

	/**
	 * DB/cache サーバ設定ファイルパスを返却
	 * @param   string $category
	 * @param   string $type
	 * @param   string $provider_id
	 * @param   string $app_name
	 * @return  string
	 */
        /*
	public function getConfigPath( $category, $type = null, $app_name=APP_NAME )
	{
		// DB
		if ( $category == 'dbh' ) {
			if ( is_null( $type ) ) {
				$type = $this->db_type_;
			}
			return sprintf( '%s/config/%s/%s/db.conf', ROOT_PATH, $app_name, $type );
		}
	}
*/
	// }}}



	// }}}
	
	// {{{ public function query
	/**
	 * SQL(Selectなど)を実行して 結果の配列を返す
	 * @param String $sql 		SQL文
	 * @param Array  $bindings 	bindingパラメータ
	 */
	public function executeSelect( $sql , $bindings = array() ) 
	{
            $dbh  = $this->dbh();
            //$dbh->setAttribute(PDO::ATTR_TIMEOUT, self::TIMEOUT_SELECT);
            $stmt = $dbh->prepare( $sql );

            // ログに記録
            //$time = self::addStack( $sql, $bindings );
            $now = microtime(true);
            $sql_ = $sql;
            foreach( $bindings as $key => $val  )
            {
                $sql_ = str_replace($key, $val, $sql_ );
            }
            //App::sqlLog( $sql_, null );

            // SQL を実行
            $result = $stmt->execute( $bindings ) ;
            if ( !$result ) {
                    $this->error_ = $stmt->errorInfo();
                    $this->clear();
                    throw new RuntimeException( 'stmt failed to execute:' . $stmt->errorInfo() );
            }

            $result = $stmt->fetchAll( PDO::FETCH_ASSOC );
            // ログに記録
            App::sqlLog( $sql, $bindings , microtime(true)-$now );
            $stmt->closeCursor();
            $this->clear();

            return $result;
	}
	// }}}

/**
	 * SQL(Insert,Update,Deleteなど)を実行する。結果の配列は返さない
	 * @param	string $sql 		SQL文
	 * @param	array $bindings 	bindingパラメータ
	 */
	public function execute( $sql, $bindings = array() )
	{
		$dbh = $this->dbh();
		//$dbh->setAttribute( PDO::ATTR_TIMEOUT, self::TIMEOUT_UPDATE );
		$stmt = $dbh->prepare( $sql );

		// ログに記録
		//$time = self::addStack( $sql, $bindings );
		App::sqlLog( $sql, $bindings );

		// SQL を実行
		$result = $stmt->execute( $bindings ) ;
		if ( !$result ) {
			$this->error_ = $stmt->errorInfo();
			throw new RuntimeException( 'stmt failed to execute:' . $stmt->errorInfo() );
		}

		// ログに記録
		//S::sqllog( $this->config_['database'], $sql, $bindings , microtime(true)-$time );

		// 最後に挿入したID
		$last_insert_id = $dbh->lastInsertId();
		// 作用した行数を取得する
		$this->last_affected_rows_ = $stmt->rowCount();
		$stmt->closeCursor();
		$this->clear();

		return $result?$last_insert_id:null;
	}
	// }}}

	// {{{ public function selectOne( $where = null )

	/**
	 * 1 行取得する SELECT 文を実行
	 * @param	array $where
	 * @return	mixed
	 */
	public function selectOne( $where = null )
	{
		$result = $this->select( $where );
		if ( is_array( $result ) ) {
			$result = array_shift( $result );
		}
		return $result;
	}

	// }}}

	// {{{ public function readConfig( $file )

	/**
	 * 設定を読み込むんで返却
	 * @param	string $file
	 * @return	Zend_Config
	 * @thrwos	RuntimeException
	 */
	public function readConfig( $file )
	{
		//if ( ($ret = apc_fetch($file)) !== false ) {
		//	return $ret;
		//} else {
		//	if ( ! is_readable( $file ) ) {
		//		throw new RuntimeException( 'File "' . $file . '" does not exist or is not readable.' );
		//	}
//			require_once 'Zend/Config/Yaml.php';
//			$ret = new Zend_Config_Yaml( $file );
			//apc_store($file, $ret);
			require_once 'Spyc.class.php';
			$ret = Spyc::YAMLLoad($file);

			return $ret;
		//}/
	}

	// }}}
	
	// {{{ protected function executeOptions()

	/**
	 * SET オプションを実行
	 * @return void
	 */
	protected function executeOptions()
	{
		foreach ( $this->options_ as $k => $v ) {
			$this->dbh_->exec(sprintf('SET %s="%s"', $k, $v));
		}
	}

	// }}}
	
	// {{{ protected function clear()

	/**
	 * クエリー関連設定データをクリア
	 * @return	void
	 */
	public function clear()
	{
		$this->fields_   = null;
		$this->table_    = null;
		$this->order_by_ = null;
		$this->limit_    = null;
		$this->offset_   = null;
	}

	// }}}
}

