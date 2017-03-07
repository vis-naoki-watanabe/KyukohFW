<?php

class Framework_Model_Db extends Framework_Model
{   
    const TEXT_MAX_SIZE = 65535;        // 1フィールドの最大長
            
    /**
     * DB サーバタイプ
     * 必要に応じて子クラスでオーバーライト
     * @var string
     */	
    protected $db_type_ = 'default';

    /**
     * Framework_Dbmgr インスタンス
     * @var array Framework_DbMgr
     */
    private $dbh_list_ = array();
    
    // データを取得できたか
    protected $empty = true;

    // {{{ public function __construct( $id = null )
    
    /**
     * コンストラクタ
     * @var void
     */
    public function __construct( $id = null )
    {
	parent::__construct();
	
	if( $id ) {
	    $this->get( $id );
	}
    }

    // }}}
    
    // {{{ public function dbh( $type = null, $provider_id=PROVIDER_ID, $app_name=APP_NAME )

    /**
     * DB オブジェクトを返却
     * @param   string $type
     * @param   string $app_name
     * @return  Framework_DbMgr
     */
    public function connection( $type = null )
    {
        if(!$type || $this->db_type_ == 'default' ) {
            $type = 'master';
        }
        
        $config = App::getConfig();
        $db_config = App::choose($config, 'database');
        
        $db_params = App::choose($db_config, $type);
        
        if( !$db_params ) {
            App::ex('not found db config.');
            return null;
        }

        if ( ! isset( $this->dbh_list_[$type] ) || is_null( $this->dbh_list_[$type] ) ) {
            //$this->dbh_list_[$type] = Framework_Db::getInstance($type, $db_params);
            $this->dbh_list_[$type] = Framework_Orm_Idiorm::getInstance($type, $db_params);
        }
        return $this->dbh_list_[$type];
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
    /*
	public function connect( $host, $port, $database, $user, $password, $driver=null )
	{
            if ( is_null($driver) ) $driver = 'mysql';
            $dsn = sprintf( '%s:host=%s;port=%d;dbname=%s', $driver, $host, $port, $database );
            //self::addStack( 'start connect ' . $dsn );

            $key = $dsn . '|' . $user . '|' . $password;
            //S::debug( 'connection pool key:' . $key );

            if ( isset( self::$dbh_pool_[$key] ) ) {
                $this->dbh_ = self::$dbh_pool_[$key];
                //S::debug( 'Pooling Connect to database dsn="%s"', $dsn );
            } else {
                $options = array(
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                );
                $this->dbh_ = new PDO( $dsn, $user, $password, $options );
                $this->dbh_->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
                self::$dbh_pool_[$key] = $this->dbh_;
                //S::debug( 'New Connect to database dsn="%s"', $dsn );
            }

            $this->is_connected_ = true;
            $this->config_ = array(
                'host'     => $host,
                'port'     => $port,
                'database' => $database,
                'user'     => $user,
                'password' => $password
            );
	}
*/
	// }}}

    

    // {{{ public function getTable()

    /**
     * テーブル名を返却
     * @return  string
     */
    public function getTable()
    {
        $config = $this->config_;
        return App::choose( $config, 'table', '' );
    }

    // }}}
    
    // {{{ public function getSchema()

    /**
     * スキーマを返却
     * @return  array
     */
    public function getSchema()
    {
        $config = $this->config_;
        return App::choose( $config, 'schema' );
    }

    // }}}
    
    // {{{ public function getRule()

    /**
     * スキーマを返却
     * @return  array
     */
    public function getRule()
    {
        $config = $this->config_;
        return App::choose( $config, 'rule' );
    }

    // }}}

    // {{{ public function getPrimary()

    /**
     * プライマリーキーを返却
     * @return  array
     */
    public function getPrimary()
    {
        $config = $this->config_;
        return App::choose( $config, 'primary' );
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
//sakuraサーバはapc_fetchが使えないみたい。2015.11.04
		//if ( ($ret = apc_fetch($file)) !== false ) {
			//return $ret;
		//} else {
			if ( ! is_readable( $file ) ) {
				throw new RuntimeException( 'File "' . $file . '" does not exist or is not readable.' );
			}
//			require_once 'Zend/Config/Yaml.php';
//			$ret = new Zend_Config_Yaml( $file );
			//apc_store($file, $ret);
			require_once 'Spyc.class.php';
			$ret = Spyc::YAMLLoad($file);

			return $ret;
		//}
	}

	// }}}
    public function _select( $where )
    {	
	$query = App_Abstruct::createQuery('select', $this->getTable(), null, $where );
	return $this->getByQuery( $query, $where );
    }
    
    public function getPrimaryBinds()
    {
	$schema = $this->getSchema();
	$primary = $this->getPrimary();
	$binds = array();
	foreach( $schema as $field )
	{
	    if( $primary && in_array($field, $primary) ) $binds[':'.$field] = $this->$field;
	}
        return $binds;
    }

    public function _create( $ex_fields = null )
    {
	// 更新するフィールドリスト
	$schema = $this->getSchema();
	$primary = $this->getPrimary();

	$where = array();
	$binds = array();
	foreach( $schema as $field )
	{
	    //if( $primary && in_array($field, $primary) ) $where[':'.$field] = $this->$field;
	    if( $ex_fields && in_array($field, $ex_fields) ) continue;
            $__field__ = sprintf('__%s__', $field);
	    $binds[':'.$field] = $this->$__field__;
            
            // バッファオーバーフロー対策（文字数制限）
            if( App::strlen($this->$field) > self::TEXT_MAX_SIZE ) {
                App::debug( "_create: cancel! [filed:".$field."] is text max length.".PHP_EOL."=>value:". $this->$field);
                return false;
            }
	}
	
	// NOW()の使い方が分からないので取り敢えず時刻で入れる
	$binds = $this->convertDateNow( $binds );
        App::debug($binds);
	
	$query = App_Abstruct::createQuery('insert', $this->getTable(), $binds );
	$result = $this->connection()->execute( $query, $binds );
	
	// 最後に挿入したIDを返却
	return $result;
    }

    // 特定のフィールドだけ更新、$wehereの指定がない場合はprimaryをキーとする
    public function _update( $binds = null, $where = null )
    {
	// 更新するフィールドリスト
	$schema = $this->getSchema();
	$primary = $this->getPrimary();

	if( $where === null )
	{
	    foreach( $schema as $field )
	    {
		if( $primary && in_array($field, $primary) ) $where[':'.$field] = $this->$field;
	    }
	}
        	
	// NOW()の使い方が分からないので取り敢えず時刻で入れる
	$binds = $this->convertDateNow( $binds );

	$query = App_Abstruct::createQuery('update', $this->getTable(), $binds, $where );
        $result = $this->connection()->execute( $query, $binds );
    }

    // primaryキーにより全フィールドアップデート
    public function _update_all( $ex_fields = null )
    {
	// 更新するフィールドリスト
	$schema = $this->getSchema();
	$primary = $this->getPrimary();

	$where = array();
	$binds = array();
	$after_binds = array();
	foreach( $schema as $field )
	{
	    if( $primary && in_array($field, $primary) ) $where[':'.$field] = $this->$field;
	    if( $ex_fields && in_array($field, $ex_fields) ) continue;
            
            $__field__ = sprintf('__%s__', $field);
	    $binds[':'.$field] = $this->$__field__;

            // バッファオーバーフロー対策（文字数制限）
            if( App::strlen($this->$field) > self::TEXT_MAX_SIZE ) {
                App::debug( "_create: cancel! [field:".$field."] is text max length.".PHP_EOL."=>value:". $this->$field);
                return false;
            }
	}
        	
	// NOW()の使い方が分からないので取り敢えず時刻で入れる
	$binds = $this->convertDateNow( $binds );

	$query = App_Abstruct::createQuery('update', $this->getTable(), $binds, $where );
	$result = $this->connection()->execute( $query, $binds );
    }

    // primaryキーにより全フィールドアップデート
    public function _delete_date()
    {
	// 更新するフィールドリスト
	$schema = $this->getSchema();
	$primary = $this->getPrimary();
	
	$where = array();
	$binds = array(
	    ':delete_date' => 'NOW()'
	);
	foreach( $schema as $field )
	{
	    if( $primary && in_array($field, $primary) ) {
		$where[':'.$field] = $this->$field;
		$binds[':'.$field] = $this->$field;
	    }
	}

	// NOW()の使い方が分からないので取り敢えず時刻で入れる
	$binds = $this->convertDateNow( $binds );
	
	$query = App_Abstruct::createQuery('update', $this->getTable(), $binds, $where );
	$result = $this->connection()->execute( $query, $binds );
    }
    
    public function _delete( $ex_fields = null )
    {
	// 更新するフィールドリスト
	$schema = $this->getSchema();
	$primary = $this->getPrimary();
	
	$where = array();
	$binds = array();
	// where区のフィールドだけbindに入れる
	foreach( $schema as $field )
	{
	    if( $primary && in_array($field, $primary) ) $where[':'.$field] = $this->$field;
	    else continue;
	    if( $ex_fields && in_array($field, $ex_fields) ) continue;
	    $binds[':'.$field] = $this->$field;
	}
	
	// NOW()の使い方が分からないので取り敢えず時刻で入れる
	$binds = $this->convertDateNow( $binds );

	$query = App_Abstruct::createQuery('delete', $this->getTable(), $binds, $where );
	$result = $this->connection()->execute( $query, $binds );
    }
    
    // NOW()の使い方が分からないので取り敢えず時刻で入れる
    public function convertDateNow( $binds )
    {
	$now = date('Y-m-d H:i:s');
	foreach( $binds as $key => &$val )
	{
	    if( $val === 'NOW()') $val = $now;
	}
	return $binds;
    }
    
    /*
     * ToDo: abstructの必要ある？
    public function getList()
    {
        $table = $this->getTable();
	$query = sprintf( 'SELECT * FROM %s ', $table );
        $query.= sprintf( ' where category=:category and delete_date=:delete_date');
        $query.= ' order by game_date desc, last_mod_date desc';

	$params = array(
	);

	return $this->getByQuery( $query, $params );
    }
    */
    
    public static function findOne()
    {
        $keys = func_get_args();
        $obj = new static();
        $ret = $obj->get($keys);
        return $ret;
    }

    public static function find()
    {
        $keys = func_get_args();
        $obj = new static();
        $ret = $obj->get($keys, false);
        return $ret;
    }

    public function get($keys, $single = true)
    {
        // 基本ルール（DELETE_DATEなど)付加
        $rules = array();
        if( App::choose($this->getRule(), 'get') ) {
            $rules = App::choose($this->getRule(), 'get');
        }
        
        $params = array();
        if( count($keys) > 0 ) {
            if( !is_array($keys[0]) ) {
                $primaries = $this->getPrimary();
                $values = $keys;
                $keys = array();
                foreach($primaries as $n => $primary) {
                    if(isset($values[$n]) && $values[$n] !== null) {
                        $keys[$primary] = $values[$n];
                    }
                }
            } else {
                $keys = $keys[0];
            }
        }
        
        $keys = array_merge($keys, $rules);
        $where = '';
        foreach( $keys as $key => $val ) {                
            if( $where != '' ) { $where.= ' and '; }
            $where.= "{$key}=:{$key}";
            $params[":{$key}"] = $val;
        }
        $query = sprintf( "SELECT * FROM %s", $this->getTable() );
        if( $where != '' ) {
            $query.= ' WHERE '.$where;
        }

        $result = $this->connection()->executeSelect( $query, $params );

	if( !$result || count($result) <= 0 )
	{
            $this->empty = true;
	    return null;
	}

        // 単一
        if($single) {
            $this->setData( $result[0] );
            return $this;
        }
        
        // 複数
        $list = App::newList();
	foreach( $result as $row )
	{
            $obj = new static();
            $obj->setData( $row );
            $list->add($obj);
	}
        return $list;
    }

    // {{{ public function getByQuery( $query, $params )

    /**
     * 
     * @return Framework_Model_List
     */
    public function getByQuery( $query, $params = array() )
    {
        // SQLインジェクション
        foreach( $params as &$param ) {
            $param = str_replace(";", "", $param);
        }
        unset($param);

	$result = $this->connection()->executeSelect( $query, $params );

	if( !$result || count($result) <= 0 )
	{
	    return App::newList();
	}

	if ( $result )
	{
	    $list = $this->toList( $result, count($result) );
	}

	return $list;
    }
    
    // }}}

    public function toList( $array,$total = null )
    {
        if ( !$total )
        {
            $total = sizeof( $array );
        }
        
        $list = new Framework_Model_List();
        $list->setTotalCount( $total );
        
        foreach ( $array as $row )
        {
            $model = clone $this;
            $model->setData($row);
            $list->add( $model );
        }
        return $list;
    }
    
    public function getByQuery2( $query, $params = array() )
    {
        // SQLインジェクション
        foreach( $params as &$param ) {
            $param = str_replace(";", "", $param);
        }
        unset($param);
        
	$result = $this->connection()->executeSelect( $query, $params );

	if( !$result || count($result) <= 0 )
	{
	    return App::newList();
	}

	if ( $result )
	{
	    $list = $this->toList2( $result, count($result) );
	}

	return $list;
    }
    
    public function toList2( $array,$total = null )
    {
        if ( !$total )
        {
            $total = sizeof( $array );
        }
        
        $list = new Framework_Model_List();
        $list->setTotalCount( $total );

        foreach ( $array as $row )
        {
	    $ids = $this->getPrimaryValues( $row);

	    //$model = clone $this;
	$model = $this::getInstance($ids);
	$model->setData($row);
	$list->add( $model );
        }
        return $list;
    }
    
    private function getPrimaryValues( $row )
    {
	$primary = $this->getPrimary();
	$ids = array();
	foreach( $primary as $id ) {
	    $ids[] = App::choose( $row, $id );
	}
	$id = count($primary)<=1?$ids[0]:$ids;
	return $id;
    }
    
    protected static function createQuery( $mode, $table, $binds, $where_ = null )
    {
	if( $binds )
	{
	    foreach( $binds as $key => $val)
	    {
		$params[str_replace(':','',$key)] = $val;
	    }
	}

	$query = '';
	if ( $mode == 'select' ) 
	{
	    $where = array();
	    foreach( $where_ as $key => $param )
	    {
		$where[] = sprintf('%s=%s', str_replace(":","",$key), $key);
	    }

	    $query = sprintf('select * from %s where (%s)', $table, implode(' and ', $where) );    
	}
	else if ( $mode == 'insert' )
	{
	    $query = sprintf('insert into %s (%s) values (%s)', $table
		, implode(',',array_keys($params))
		, implode(',',array_keys($binds))
	    );
	}
	else if ( $mode == 'update' )
	{
	    //'update user set muid=:muid,nickname=:nickname,provider=:nickname where uid=:uid';
	    $values = array();
	    foreach( $params as $key => $param )
	    {
		//if( $ex_fields && is_array($ex_fields) && in_array($key, $ex_fields) ) continue;
		if( $where_ && isset($where_[':'.$key]) ) continue;
		$values[] = sprintf('%s=:%s', $key, $key);
	    }

	    $query = sprintf('update %s set %s', $table, implode(',',$values) );

	    if( $where_ )
	    {
		$where = array();
		foreach( $where_ as $key => $param )
		{
		    $where[] = sprintf('%s=%s', str_replace(":","",$key), $key);
		}
		$query.= ' where ' . implode(' and ', $where);
	    }
	}
	else if ( $mode == 'delete' ) 
	{
	    $where = array();
	    foreach( $where_ as $key => $param )
	    {
		$where[] = sprintf('%s=%s', str_replace(":","",$key), $key);
	    }

	    $query = sprintf('delete from %s where (%s)', $table, implode(' and ', $where) );    
	}
	
	/*
	// mysqlファンクションの置換
	foreach( $binds as $key => $bind ) {
	    if( $bind == 'NOW()' ) {
		$query = str_replace( $key, 'NOW()', $query );
	    }
	}
	 */
	return $query;
    }
}
?>
