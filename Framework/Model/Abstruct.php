<?php
class Framework_Model_Abstruct extends Model
{
    // DBを使用しない場合のスキーマ定義
    protected $nodb_schemas = null;
    
    //protected $_model = null;
    protected static $_db = array();
    
    // 初期カラム設定
    protected static $default_schemas = array();
    // 削除フラグ
    protected static $delete_flags = null;
    
    // {{{ public static function getInstance( $id = null )
    
    /**
     * インスタンス
     * @return 
     */
    public static function getInstanceForce($id)
    {
        return self::getInstance($id, true);
    }
    public static function getInstance( $id = null, $force = false )
    {
        if($id === null) {
            return self::newModel();
        }

        $options = null;
        
        if($id && is_array($id)) {
            $options = $id;
            $id = null;
        }

        // whereが指定されてない場合は、where句とする
        if(!static::isWhere($options)) {
            $options = array(
                'where' => $options
            );
        }

        // 削除フラグ参照
        if( static::$delete_flags && !$force ) {
            $options['where'] = array_merge(App::choose($options,'where',array()), static::$delete_flags);
        }
                
        // クラス名取得
        $class_name = get_called_class();

        //App::debug($class_name);
        //App::debug($options);

        // DBから取得
        $orm = static::getModel($class_name);
        self::setOptions( $orm, $options );
        $obj = $id?$orm->find_one($id):$orm->find_one();
       
        if( $obj ) {            
            $obj->init();
            
            // キャッシュ保存
            if( $id ) {
                $cache_id = $obj->prepareCache($id);
                if($cache_id) {
                    $obj->cache->set($cache_id, $obj->toSerialize());
                }
            }
        }
        /*
        // データが存在しない場合、空のオブジェクトを生成
        else {
            $obj = new static();
        }
        */
        
        return $obj;
    }

    // }}}
    
    // optionsにwhereが含まれるか
    private static function isWhere($options)
    {
        $ret = false;
        if($options && is_array($options)) {
            foreach($options as $key => $params) {
                if(preg_match('/where/', $key)) return true;
            }
        }
        return false;
    }
    
    // {{{ public static function _getList()
    
    /**
     * リスト取得
     * @return	Framework_List
     */
    public static function _getList($options = null)
    {
        // クラス名取得
        $class_name = get_called_class();
        $orm = static::getModel($class_name);
        self::setOptions( $orm, $options );
        $list_ = $orm->find_many();
        
        // Array -> Framework_List
        $list = App::newList();
        foreach($list_ as $obj) {
            $obj->init();
            $list->add($obj);
        }
        
        return $list;   
    }

    // }}}
    
    private static function setOptions(&$orm, $options)
    {
        if( $options ) {
            if(isset($options['where'])) {
                foreach($options['where'] as $field => $value) {
                    //App::debug($field."=>".$value);
                    $orm->where($field, $value);
                }
            }
            // より小さい
            if(isset($options['where_lt'])) {
                foreach($options['where_lt'] as $field => $value) {
                    $orm->where_lt($field, $value);
                }
            }
            // より大きい
            if(isset($options['where_gt'])) {
                foreach($options['where_gt'] as $field => $value) {
                    $orm->where_gt($field, $value);
                }
            }
            // 以下
            if(isset($options['where_lte'])) {
                foreach($options['where_lte'] as $field => $value) {
                    $orm->where_lte($field, $value);
                }
            }
            // 以上
            if(isset($options['where_gte'])) {
                foreach($options['where_gte'] as $field => $value) {
                    App::debug($field."::".$value);
                    $orm->where_gte($field, $value);
                }
            }
        }
    }
    
    // {{{ public static function getList()
    
    /**
     * 削除フラグを確認してリスト取得
     * @return	Framework_List
     */
    public static function getList($options = null)
    {
        $check_delete_flag = true;
        // 削除フラグ参照
        if( static::$delete_flags ) {
            $options['where'] = array_merge(App::choose($options,'where',array()), static::$delete_flags);
        }
        return self::_getList($options);
    }

    // }}}
    
    // {{{ public static function getListAll()
    
    /**
     * 削除フラグを確認しないでリスト取得
     * @return	Framework_List
     */
    public static function getListAll($options = null)
    {
        return self::_getList($options);
    }

    // }}}

    // }}}
    
    // 初期フィールドをセット（レコードを取得しないでモデルを使用する場合)
    public static function newModel()
    {
        $model = static::_create();
        $model->id = -1;
        return $model;
    }

    public static function _create($schema = null, $save_flag = true)
    {
        $org_schema = $schema;
        
        // クラス名取得
        $class_name = get_called_class();
                
        $obj = static::getModel($class_name)->create();
        $obj->init();
        
        // 必須(初期)カラムがない場合は挿入
        foreach(static::$create_schemas as $key => $val) {
            if( !isset($schema[$key]) ) {
                $schema[$key] = $val;
            }
        }        
        unset($schema['id']);
        
        // $org_shemaがnullの場合は初期フィールド
        if(!$org_schema) {
            $schema = array_merge($obj->getSchemasFields(), $schema);
        }
        
        foreach($schema as $key => &$val) {
            if( $val == 'APP_NOW()' ) {
                $val = App::timestamp();
            }
            else if( $val == 'USER_NOW()' ) {
                $val = App::user_timestamp();
            }
            if($key == 'params' && is_array($val)) {
                $val = App::raw_json_encode($val);
            } 
        }
        unset($val);

        // 値セット
        $obj->set($schema);

        // レコード作成
        if( $org_schema !== null && $save_flag ) {
            $obj->save();
        }

        return $obj;
    }
    
    public function create($schema = null, $save_flag = true)
    {
        if($schema === null) {
            $schema = $this->toArray();
            
             // idフィールド削除
            if($save_flag) {
                if(isset($schema[static::$_id_column]) && $schema[static::$_id_column]<0) {
                    unset($schema[static::$_id_column]);
                }
            }
        }

        // トランザクション開始
        // メインモデル(レコード)作成
        $this->_create($schema, $save_flag);
        
        // 関連テーブル挿入
        
        // トランザクション完了
    }
    
    public function _update($schema = null, $save_flag = true)
    {
        /*
        if($schema) {
            foreach($schema as $key => $val)
            {
                if($key == 'params' && is_array($val)) {
                    $val = App::raw_json_encode($val);
                }
                $this->$key = $val;
            }
        }
        */
        if($save_flag) {
            $this->save();
        }
    }
    
    // オーバーライド用
    public function update($schema = null, $save_flag = true)
    {
        if($schema === null) {
            $schema = $this->toArray();
        }
        
        // トランザクション開始
        // メインモデル(レコード)作成
        $this->_update($schema, $save_flag);
        
        // 関連テーブル挿入

        // トランザクション完了
    }
    
    // DBには書き込まない
    public function setSchemas($schemas)
    {
        $this->update($schemas, false);
    }
    
    // 削除フラグ
    public function delete($save_flag = true)
    {
        $this->delete_time = App::timestamp();
        if( $save_flag ) {
            $this->save();
        }
    }
    
    //論理削除
    public function logicDelete($save_flag = true)
    {
        return $this->orm->delete();
    }
    
    // 初期カラム追加
    protected function addDefaultSchemas($schemas)
    {
        static::$default_schemas = array_merge(static::$default_schemas,$schemas);
    }
    // 初期カラム
    protected function setDefaultSchemas($schemas)
    {
        static::$default_schemas = $schemas;
    }
    
    
    // Factoryで取得するModel(ORM)を返却
    public static function getModel($name)
    {
        $config_ = App::getConfig('database');
        
        // TODO: connection_nameをdefault以外にするとエラーになる？
        $connection_name = 'default';
        if(!isset(self::$_db[$connection_name])) {

            $config = App::choose($config_, $connection_name);
            
            $host       = App::choose($config,'host'    );  //'mysql023.phy.lolipop.lan';
            $port       = App::choose($config,'port'    );  //3306;
            $database   = App::choose($config,'database');  //'LAA0415127-develop';
            $user       = App::choose($config,'user'    );  //'LAA0415127';
            $password   = App::choose($config,'password');  //'tohru0113';        
            $dsn = sprintf('mysql:dbname=%s;host=%s;port=%s;charset=utf8', $database, $host, $port);
            //ORM::configure('mysql:dbname=dbname;host=localhost:3306:/tmp/mysql.sock;charset=utf8');
            ORM::configure($dsn, null, $connection_name);
            ORM::configure('username', $user);
            ORM::configure('password', $password);
            
            // キャッシュ有効
            $caching_flag = App::choose($config,'sql_caching',false);
            ORM::configure('caching', $caching_flag);
            // INSERT, UPDATEでキャッシュをクリア
            ORM::configure('caching_auto_clear', $caching_flag);
            
            // ロガー
            ORM::configure('logging', true);
            ORM::configure('logger', 'App::sqlLog');
            
            self::$_db[$connection_name] = true;
        }
        
        return Model::factory($name, $connection_name);
    }

    // }}}


    /**
     * キャッシュ
     * @var integer
     */
    protected $_cache = null;
    /**
     * キャッシュ生存期間
     * @var integer
     */
    protected $_cache_life_time = 1800;

    protected $_cache_type = null;
    protected $_cache_name = null;

    // {{{ public function init()
    
    /**
     * @param	
     * @return	
     */  
    public function init()
    {
        // キャッシュ生成
        $this->_cache = $this->getCache();
        $this->_cache->setCacheLifeTime( $this->_cache_life_time );
        
        // オーバーライド用
        $this->preDispatch();
    }

    // }}}
    
    // ① モデル実態初期でやりたいこと
    public function preDispatch()
    {
        // オーバーライド用
    }

    // {{{ protected function getCache()
    
    /**
     * @param	
     * @return	
     */
    protected function getCache()
    {
        $cache = new Framework_Base_Cache();
        return $cache;
    }

    // }}}
    
    public function toSerialize()
    {
        return App::serialize($this);
    }

    // {{{ protected function prepareCache( $key = "" )
    
    /**
     * @param	
     * @return	
     */
    protected function prepareCache( $key = "" )
    {
        if(!$this->_cache_type || !$this->_cache_name) return null;
        
        $key = sprintf( "%s.%s.%s", $this->_cache_type, $this->_cache_name, $key );

        return $key;
    }

    // }}}
    
    // {{{ public function __get( $name )
    
    /**
     * GETTER アクセッサ
     * @param	string $name
     * @return	mixed
     * @throws	BadMethodCallException
     */
    public function __get( $name )
    {
        if(preg_match('/__(.*)__/', $name,$ret)) {
            $name = $ret[1];
        }
        
        // メッソッドが存在する場合はメソッド実行
        //$method = 'get'.ucfirst($name);
        $method = 'get'.App::Camelize($name);
        if( method_exists( $this, $method ) ) { return $this->$method();}
        
        // プロパティ返却
        return !$this->isEmpty()?$this->orm->get($name):null;
    }

    // }}}
    
    public function __set($name, $value)
    {
        if(!$this->isEmpty()) {
            
            // メッソッドが存在する場合はメソッド実行
            //$method = 'get'.ucfirst($name);
            $method = 'set'.App::Camelize($name);
            if( method_exists( $this, $method ) ) {
                $this->$method($value);
            }
            else {
                $this->orm->set($name, $value);
            }
        }
    }
    
    // {{{ public function getParams($key = null, $to_array = false)
    
    /**
     * Params GETTER アクセッサ
     * @param	string $name
     * @return	
     */
    public function getParamsArray($key=null)
    {
        return $this->getParams($key, true);
    }
    public function getParams($key = null, $to_array = true)
    {

        if(!$this->orm) return array();
        
        $json = $this->orm->get('params');

        $obj = $json?json_decode($json, $to_array):null;
        if(!$key || !$obj) return $obj;
        
        // array
        if( $to_array) {
            return App::choose($obj, $key);
        }
        else {
            return property_exists($obj, $key)?$obj->$key:null;
        }
    }

    // }}}

    // {{{ public function setParams($key, $val = null, $save_flag = true)
    
    /**
     * Params SETTER アクセッサ
     * @param	string $key or array
     * @param   string $val or null
     * @param   boolean
     * @return	
     */
    public function addParams()
    {
        $args = func_get_args();
        if(count($args) < 1) return false;
        
        $params = $this->getParams();
        
        $add_params = array();
        if(is_array($args[0])) {
            $add_params = $args[0];
            $save_flag = App::choose($args, 1, false);
        } else {
            $key = App::choose($args, 0);
            if($key !== null) {
                $add_params[$key] = App::choose($args, 1);
            }
            $save_flag = App::choose($args, 2, false);
        }
        
        $params = array_merge($params, $add_params);
        $this->setParams($params, $save_flag);
    }

    public function removeParams($key, $save_flag = false)
    {
        $params = $this->getParams();
        if(isset($params[$key])) {
            unset($params[$key]);
        }
        $this->setParams($params, $save_flag);
    }
    // DBに反映しない
    /*
    public function setModelParams($params)
    {
        $this->setParams($params, false);
    }*/
    
    public function setParams()//$key, $val = null, $save_flag = true)
    {
        $args = func_get_args();
        if(count($args) < 1) return false;
        
        if(is_array($args[0])) {
            $params = $args[0];
            $save_flag = App::choose($args, 1, false);
        } else {
            $key = App::choose($args, 0);
            
            $params = $this->getParams();
            if($key !== null) {
                $params[$key] = App::choose($args, 1);
            }
            $save_flag = App::choose($args, 2, false);
        }

        $json = App::raw_json_encode($params);
        if(static::$_table) {
            $this->orm->set('params', $json);
            if($save_flag) {
                $this->save();
            }
        }
    }
    
    public function getJsonParams()
    {
        $params = $this->getParams();
        return App::raw_json_encode($params);
    }

    public function setJsonParams($json)
    {
        $params = json_decode($json, true);
        $this->setParams($params);
    }
    // }}}
    
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
    
    public function isDirty()
    {
        
    }
    
    // }}}
    
    // {{{ public static function begin()
    
    /**
     * トランザクション開始
     */
    public static function begin()
    {
        ORM::get_db()->beginTransaction();
    }
    
    // }}}
    
    // {{{ public static function rollBack()
    
    /**
     * トランザクションロールバック
     */
    public static function rollBack()
    {
        ORM::get_db()->rollBack();
    }
    
    // }}}
    
    // {{{ public static function commit()
    
    /**
     * トランザクション確定
     */
    public static function commit()
    {
        ORM::get_db()->commit();
    }
    
    // }}}

    // {{{ public function getAssociations($associaton_table, $associaton_id, $method)
    
    /**
     * 連携テーブル経由で連携するマスターを取得する
     * @param $associaton_table 連携クラス名
     * @param $associaton_id 連携ID
     * @param $method マスターを取得するメソッド名(連携クラスに定義)
     * @return	Framework_List
     */
    public function getAssociations($associaton_table, $associaton_id, $method = null, $options = null)
    {
        if(is_array($associaton_id)) {
            $orm = $this->has_many($associaton_table, $associaton_id[0], $associaton_id[1]);            
        } else {
            $orm = $this->has_many($associaton_table, $associaton_id);
        }
        
        // 削除フラグ参照
        if( static::$delete_flags ) {
            $options['where'] = array_merge(App::choose($options,'where',array()), static::$delete_flags);
        }
        
        self::setOptions( $orm, $options );
        $list_ = $orm->find_many();
        $list = App::newList();

        if($list_) {
            foreach($list_ as $obj) {
                if( $method ) {
                    $list->add($obj->$method($options));
                } else {
                    $list->add($obj);
                }
            }
        }
        return $list;
    }
    
    public function getListByRelation($target_table, $target_column)
    {
        $options['where'] = array(
            $target_column => $this->$target_column
        );
        return $target_table::getList($options);
    }

    // }}}
    
    public function belongsToFindOne($belong_table, $belong_id, $options = null)
    {
        $orm = $this->belongs_to($belong_table, $belong_id);
        // 削除フラグ参照
        if( static::$delete_flags ) {
            $options['where'] = array_merge(App::choose($options,'where',array()), static::$delete_flags);
        }
        self::setOptions( $orm, $options );
        return $orm->find_one();
    }
    
    
    // {{{ public function getAssociationOne($associaton_table, $associaton_id, $method)
    
    /**
     * 連携テーブル経由で連携するマスターを取得する
     * @param $associaton_table 連携クラス名
     * @param $associaton_id 連携ID
     * @param $method マスターを取得するメソッド名(連携クラスに定義)
     * @return	$methodで取得できるクラス
     */
    public function getAssociationOne($associaton_table, $associaton_id, $method, $options = null)
    {
        $obj = $this->has_many($associaton_table, $associaton_id)->find_one();
        return $obj->$method($options);
    }

    // }}}
    
    public function getSchemasFields()
    {
        return $this->getSchemas(false);
    }
    
    // キー配列を返却する or 
    public function getSchemas($value = true)
    {
        $default = null;
        
        // クラス名取得
        $class_name = get_called_class();
        
        // DBから取得
        $orm = static::getModel($class_name);
        
        if( $this->nodb_schemas ) {
            $fields = $this->nodb_schemas;
        } else {
            $fields = $this->orm->getSchemas();
        }
        
        // キーと空値配列を返却する
        if( $fields && count($fields)>0 && !$value) {
            $values = array_fill(0, count($fields), $default);
            return array_combine($fields, $values);
        }
        
        // キー（フィールド)配列のみ返却する
        return $fields;
    }
    
    public function toArray()
    {
        $ret = array();
        $fields = array_keys($this->getSchemasFields());

        foreach($fields as $field)
        {
            $ret[$field] = $this->$field;
        }
        return $ret;
    }
}