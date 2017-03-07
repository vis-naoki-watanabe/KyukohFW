<?php

class Framework_Model/// implements IteratorAggregate
{
    // {{{ properties

    const DATE_ZERO = "0000-00-00 00:00:00";

    /**
     * Iterator オブジェクトのインスタンス
     * @var Iterator
     */
    protected $it_ = null;

    /**
     * config を格納する配列
     * @var array
     */
    protected $config_ = array();

    /**
     * スキーマを格納する配列
     * @var array
     */
    protected $schema_ = array();

    /**
     * データを格納する配列
     * @var array
     */
    protected $data_ = array();

    /**
     * params用データを格納する配列
     * @var array
     */
    protected $params_ = null;

    /**
     * キャッシュ生存期間
     * @var integer
     */
    protected $cache_life_time = 1800;

    // You cannot serialize or unserialize PDO instances対策
    public function __sleep()
    {
        return array('config_','data_','schema_','params_');
    }

    /**
     * 定義済みのメソッドリストを格納する配列
     * @var array
     */
    protected $methods_ = array();
    protected $getter_ = array();
    protected $setter_ = array();

    protected $cache = null;
    // }}}

    // {{{ public function __construct( $params = null )

    /**
     * コンストラクタ
     * @param	array $params
     * @return	void
     */
    public function __construct( $params = null )
    {
        //echo (sprintf('new Model: %s', get_class($this))).PHP_EOL;
        $this->_init();

        $this->cache = $this->getCache();
        $this->cache->setCacheLifeTime( $this->cache_life_time );
    }

    // }}}
    
    // {{{ public static function getInstance( $id = null, $replace_key_id = null )
    
    /**
     * インスタンス作成
     * @return 
     */
    public static function getInstance( $id = null, $replace_key_id = null )
    {
        echo "[".$id."]";
	// 空のオブジェクト
	if( $id === null ) return new static();
	
        $id_ = ($replace_key_id?$replace_key_id."_":"").$id;
	if( !isset(static::$instance[$id_]) )
	{
	    static::$instance[ $id_ ] = new static( $id, $replace_key_id );
	}
	return static::$instance[ $id_ ];
    }

    // }}} 

    // {{{ public function toArray()
    
    /**
     * 全データを返却する
     * @return array
     */
    public function toArray($object_flag = false)
    {
        if( $object_flag ) return (Object)$this->data_;
        return $this->data_;
    }

    // }}} 
    
    public function isEmpty()
    {
        return $this->empty;
    }

    protected function getCache()
    {
        $cache = new Framework_Cache();
        return $cache;
    }

    protected function prepareCache( $key = "" )
    {
        $key = sprintf( "%s.%s", $this->cache_type_, $key );

        return $key;
    }

    // {{{ public function __get( $name )
    /**
     * GETTER アクセッサ
     * @param	string $name
     * @return	mixed
     * @throws	BadMethodCallException
     */
    public function __get( $name )
    {
        $strip_flag = true;
        if(preg_match('/__(.*)__/', $name,$ret)) {
            $name = $ret[1];
            $strip_flag = false;
        }            
        // 2016.01.04追加
            $method = 'get'.ucfirst($name);
            if( method_exists( $this, $method ) ) { return $this->$method();}

            $val = '';
            if ( isset($this->getter_[$name]) ) {
                    if ( $this->getter_[$name] === 1 ) {
                            $val = isset($this->data_[$name]) ? $this->data_[$name] : null;
                    } else {
                            $getter = $this->getter_[$name];
                            $val = $this->$getter();
                    }
                    return App::strip($val, $name!='params'&&$strip_flag);
            } else {

                    // getXxx() が定義されていたら、それをコール
                    // プロパティー名の '_' は省略される
                    $getter = 'get' . str_replace( '_', '', $name );
                    if ( isset($this->methods_[$getter] ) ) {
                        $this->getter_[$name] = $getter;
                        $val = $this->$getter();

                    // スキーマに存在すれば、それを返却
                    } else if ( isset( $this->schema_[$name] ) ) {
                        $this->getter_[$name] = 1;
                        if ( isset( $this->data_[$name] ) ) {
                            $val = $this->data_[$name];
                        } else {
                            $val = null;
                        }
                    }
                    return App::strip($val, $name!='params'&&$strip_flag);
            }

            $msg = sprintf('Call to undefined method %s::%s()', get_class($this), $name);
            throw new BadMethodCallException($msg);
    }

    // }}}

    // {{{ public function __set( $name, $value )
    /**
     * SETTER アクセッサ
     * @param	string $name
     * @param	mixed $value
     * @return	void
     * @throws	BadMethodCallException
     */
    public function __set( $name, $value )
    {
        App::debug("isset:".$name);
            if ( isset($this->setter_[$name]) ) {
                if ( $this->setter_[$name] === 1 ) {
                    $this->data_[$name] = $value;
                } else {
                    $setter = $this->setter_[$name];
                    $this->$setter( $value );
                }
            } else {
                // setXxx() が定義されていたら、それをコール
                // プロパティー名の '_' は省略される
                $setter = 'set' . str_replace( '_', '', $name );
                if ( isset($this->methods_[$setter] ) ) {
                    $this->setter_[$name] = $setter;
                    $this->$setter( $value );

                // スキーマに存在すれば、それにセット
                } else if ( isset( $this->schema_[$name] ) ) {
                    $this->setter_[$name] = 1;
                    $this->data_[$name] = $value;
                } else {
                    $msg = sprintf('Call to undefined method %s::%s()', get_class($this), $name);
                    throw new BadMethodCallException($msg);
                }
            }

    }

    // }}}

    // {{{ public function __isset( $name )
    /**
     * isset アクセッサ
     * @param	string $name
     * @return	mixed
     * @throws	BadMethodCallException
     */
    public function __isset( $name )
    {
        if ( isset( $this->schema_[$name] ) ) {
            return isset( $this->data_[$name] );
        }

        $msg = sprintf('Call to undefined method %s::%s()', get_class($this), $name);
        throw new BadMethodCallException($msg);
    }

    // }}}

    // {{{ public function getData()
    /**
     * 全データを取得
     * @return array
     */
    public function getData()
    {
        return $this->data_;
    }

    // }}}

    // {{{ public function setData( $data )

    /**
     * 全データをまとめてセット
     * @param	array $data
     * @return	void
     */
    public function setData( $data )
    {
        if ( is_null( $data ) || ! is_array( $data ) ) {
            return;
        }
        
        $this->empty = false;
        foreach( $data as $k => $v ) {
            // setXxx() が定義されていたら、それをコール
            // プロパティー名の '_' は省略される
            $setter = 'set' . str_replace( '_', '', $k );
            if ( isset($this->methods_[$setter] ) ) {
                $this->$setter( $v );

            // スキーマに存在すれば、それにセット
            } else if ( isset( $this->schema_[$k] ) ) {
                $this->data_[$k] = $v;
            }
        }
    }

    // }}}

    // {{{ public function getIterator()
    /**
     * Iterator を返却 (IteratorAggregate)
     * @return Iterator
     */
    public function getIterator()
    {
        if ( is_null( $this->it_ ) ) {
                //$this->it_ = new Framework_Model_Iterator( $this->data_ );
        }
        return $this->it_;
    }

    // }}}

    // {{{ public function setIterator( $it )

    /**
     * Iterator をセット
     *
     * <pre>
     * foreach ( ... ) な状況で使用されると、getIterator() が自動的に実行される。
     * 子クラスでデータ取得処理を実装した場合、最後に必ず適切な Iterator インスタンスを
     * 生成、setIterator() する必要がある。
     *
     * 指定しなければ、Framework_Model_Iterator が使われる。
     * </pre>
     *
     * @param  Iterator $it
     * @return void
     */
    public function setIterator( $it )
    {
        $this->it_ = $it;
    }

    // }}}

    // {{{ public function getConfig()

    /**
     * config を返却
     * @return	array
     */
    public function getConfig()
    {
        return $this->config_;
    }

    // }}}

    // {{{ public function getSchema()
    /**
     * スキーマを返却
     * @return	array
     */
    public function getSchema()
    {
        return $this->schema_;
    }

    // }}}

    // {{{ protected function _init()

    /**
     * 設定初期化
     * @return	void
     */
    protected function _init()
    {
        if ( isset( $this->config_['schema'] ) ) {
                $this->schema_ = array_fill_keys($this->config_['schema'], 1);
        }
        // プロパティー宣言
        $schema = $this->getSchema();
        if( $schema && is_array($schema) ) {
            foreach( $schema as $key => $field )
            {
                $this->data_[$field] = null;
            }
        }

        foreach ( array_map('strtolower', get_class_methods($this)) as $method ) {
                $this->methods_[$method] = 1;
        }
    }

    // }}}

    // {{{ protected function hasProperty( $name )

    /**
     * 属性が存在するか?
     * @param	string $name
     * @return	boolean
     */
    protected function hasProperty( $name )
    {
        if ( isset($this->getter_[$name]) ) {
            return true;
        } else {
            // getXxx() が定義されていたら OK
            $getter = 'get' . str_replace( '_', '', $name );
            if ( isset($this->methods_[$getter] ) ) {
                    $this->getter_[$name] = $getter;
                    return true;

            // スキーマに存在すれば OK
            } else if ( isset( $this->schema_[$name] ) ) {
                    $this->getter_[$name] = 1;
                    return true;
            }
        }
        return false;
    }

    // }}}

    public function getParam( $key = null, $strip_flag = true )
    {
	//if ( $this->params_ === null ) return array();

	if ( isset( $this->data_['params'] )  && $this->data_['params'] !== null ) {
		$this->params_ = json_decode( $this->data_['params'], true );
	} else {
		$this->params_ = array();
	}
	
        $ret = array();
        if( is_array($this->params_) ) {
            foreach( $this->params_ as $key_ => $val_ ) {
                $ret[$key_] = App::strip($val_, $strip_flag);
            }
        }
	if( $key === null ) return $ret;
	
	$val = App::choose( $ret, $key );
        //App::debug($val);
        //$val = App::strip($val, $strip_flag);
        //App::debug($val);
        return $val;
    }
    
    public function letParam( $key, $val )
    {
        $this->setParam($key,$val,false);
    }
    public function setParam( $key, $val, $flag = true )
    {
        $this->updateParam( array( $key => $val ), $flag );
    }

    public function removeParam( $params = null )
    {
	$params_ = $this->getParam();

	$new_params = array();
	foreach( $params_ as $key => $param )
	{
	    if( $params && in_array($key, $params) ) continue;
	    $new_params[$key] = $param;
	}

	$buff_params = App::raw_json_encode($new_params);//json_encode( $new_params, JSON_UNESCAPED_UNICODE );
        $this->data_['params'] = str_replace("\\/","/", $buff_params);
	
	$binds = array(
	    //':uid' => $this->uid,
	    ':params' => $this->data_['params']
	);
        $binds = array_merge( $binds, $this->getPrimaryBinds());

        $this->_update( $binds );
    }
    
    public function updateParam( $params, $db_update = false )
    {	
	if( $this->getParam() && !is_array($this->getParam()) ) return null;
	if( $params && !is_array($params) ) return null;
	
	$params_ = array_merge( $this->getParam(), $params );
	//$this->data_['params'] = json_encode( $params_ ,JSON_UNESCAPED_UNICODE );
	//$this->data_['params'] = json_encode( $params_  );
	$buff_params = App::raw_json_encode( $params_);//PHP5.3以下対策
        $this->data_['params'] = str_replace("\\/","/", $buff_params);
        
	$binds = array(
	    //':uid' => $this->uid,
	    ':params' => $this->data_['params'],
	);
        if( $db_update ) {
            $binds[':last_mod_date'] = 'NOW()';
        }
        $binds = array_merge( $binds, $this->getPrimaryBinds());
        
	// DBも更新する
	if( $db_update ) {
	    $this->_update( $binds );
	}
    }
    
    /**
     * データをまとめて取得する
     * @return void
     */
    /*public function getProp()
    {
        $ret = array();
	foreach( $this->getSchema() as $key => $field )
	{
            $ret[$field] = $this->$field;
	}
        return $ret;
    }*/

    // }}}    
    
    /**
     * データをまとめてセットする
     * @return void
     */
    public function setProp( $params )
    {   
	foreach( $params as $field => $val )
	{
            // CSRFトークンのみ格納しない
            if( $field == 'csrf_token' ) continue;
	    if( $field == 'this_name' ) $field = 'name';
	    $this->$field = $val;
	}
    }

    // }}}
    
    public function getDateZero()
    {
	return "0000-00-00 00:00:00";
    }
    
    // キャッシュ関連
    public function getCacheKey( $suffix = null)
    {
	$key = get_class($this);
	$key = str_replace('_','.',$key);
	$key = strtolower($key);
	if( $suffix ) {
	    $key.= '.'.$suffix;
	}
	return $key;
    }

    public function hasData()
    {
        return $this->id && $this->id != '' && $this->id > 0;
    }
}

?>
