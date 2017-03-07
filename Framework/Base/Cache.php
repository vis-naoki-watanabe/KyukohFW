<?php
class Framework_Base_Cache
{
    protected $_root_path = null;
    protected $option = null;
    protected $cache_id = null;
    
    private $cache_life_time = 1800;

    /**
     * コンストラクタ
     * @var void
     */
    public function __construct( $id = null )
    {
        $this->_root_path = FRAMEWORK_DIR.'/../';
	$this->init();
    }
    
    protected function init()
    {
        $config = App::getConfig('cache');
        
        $chacheDir = realpath($this->_root_path.App::choose($config, 'path'));
        $chacheDir.= (substr($chacheDir, -1)!='/'?'/':'');
        
	// オプション
	$this->options = array(
	    'cacheDir'                  => $chacheDir,
	    'caching'                   => 'true',          // キャッシュを有効に
	    'automaticSerialization'	=> 'true',          // 配列を保存可能に
	    'lifeTime'                  => null,            // 60*30（生存時間：30分）→cacheインスタンス時にsetLifeTimeでやらないとダメ？
	    'automaticCleaningFactor'	=> 200,		    // 自動で古いファイルを削除（1/200の確率で実行）
	    'hashedDirectoryLevel'	=> 1,		    // ディレクトリ階層の深さ（高速になる）
	);
    }
    
    // チェイン取得用
    // ToDo: 処理方法検討中
    // 取り敢えず単発で取りに行く
    public function prepare( $chache_id )
    {
	$this->cache_id = $chache_id;
    }
    
    public function setCacheLifeTime( $life_time )
    {
        $this->cache_life_time = $life_time;
    }
    
    public function set( $cache_id, $val )
    {
        App::debug($this->options);
	$cache=new Cache_Lite($this->options);
        $cache->setLifeTime($this->cache_life_time);
        //App::debug("save cache life time:".$this->cache_life_time);
        App::debug($val);
	$cache->save( $val );
	
	$cache_data=$cache->get($cache_id);
	$cache->save( $val );
	return $val;
    }
    
    public function get( $cache_id )
    {
	$cache=new Cache_Lite($this->options);
        $cache->setLifeTime($this->cache_life_time);
        //App::debug("get cache life time:".$this->cache_life_time);
	
	if( $cache_data=$cache->get($cache_id) ){
	    return $cache_data;
	} else {
	    return null;
	}
    }
    
    public function remove( $cache_id )
    {
	$cache=new Cache_Lite($this->options);
	$cache->remove( $cache_id );
    }
}