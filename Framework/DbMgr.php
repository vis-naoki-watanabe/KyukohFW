<?php

//require_once 'Framework/Db.php';

/**
 * Framework_DbMgr クラス　
 *
 */

class Framework_DbMgr
{

	// {{{ properties

	/**
	 * Framework_Db オブジェクトを格納する配列
	 * @var array
	 */
	private $dbhs_ = array();

	/**
	 * 設定データを格納する配列
	 * @var array
	 */
	private $config_ = array();

	/**
	 * SLAVE の数
	 * @var int
	 */
	private $slave_num_ = 0;

	/**
	 * 最後に使った SLAVE の番号
	 * @var int
	 */
	private $last_slave_ = -1;

	// }}}

	// {{{ public function __construct( $config = null )

	/**
	 * コンストラクタ
	 * @param	array $config
	 * @return	void
	 */
	public function __construct( $config = null )
	{
		if ( ! is_null( $config ) ) {
			$this->setConfig( $config );
		}
	}

	// }}}

	// {{{ public function master()

	/**
	 * MASTER に接続しているオブジェクトを返却
	 * @return	Framework_Db
	 */
        /*
	public function master()
	{
		// 開発環境用指定があればそちらを使う
		if ( defined( 'APP_FORCE_DB_SECTION' ) ) {
			return $this->getDbh( APP_FORCE_DB_SECTION );
		}
		return $this->getDbh('master');
	}*/

	// }}}

	// {{{ public function slave()

	/**
	 * SLAVE に接続しているオブジェクトを返却
	 * @return	Framework_Db
	 */
        /*
	public function slave()
	{
		// 開発環境用指定があればそちらを使う
		if ( defined( 'APP_FORCE_DB_SECTION' ) ) {
			return $this->getDbh( APP_FORCE_DB_SECTION );
		}
		// main アプリ用：slave()が呼ばれても強制的にmasterを指定(application.iniで指定可)
		if ( defined( 'APP_FORCE_DB_MASTER' ) && APP_FORCE_DB_MASTER ) {
			return $this->getDbh( 'master' );
		}
		return $this->getDbh( 'slave' );
	}*/

	// }}}

	// {{{ public function getConfig()

	/**
	 * 設定データを返却
	 * @return	array
	 */
	public function getConfig()
	{
		return $this->config_;
	}

	// }}}

	// {{{ public function setConfig( $config )

	/**
	 * 設定データをセット
	 * @param	array $config
	 * @return	void
	 */
	public function setConfig( $config )
	{
		$this->config_ = $config;
                /*
		if ( isset( $config['slave'] ) ) {
			$this->slave_num_ = sizeof( $config['slave'] );
		}*/
	}

	// }}}

	// {{{ public function getDbhs()

	/**
	 * Framework_Db オブジェクト配列を返却
	 * @return	array
	 */
	public function getDbhs()
	{
		return $this->dbhs_;
	}

	// }}}

	// {{{ public function getDbh( $section )

	/**
	 * $section に接続しているオブジェクトを返却
	 * @param	string $section
	 * @return	Framework_Db
	 */
	public function getDbh( $section )
	{
		if( App::isDev() && defined( 'DB_TYPE' ) && DB_TYPE != '' ) {
                    $section = DB_TYPE;
		}
	    
		// 接続済みならそれを使う
		if ( isset( $this->dbhs_[$section] ) ) {
			return $this->dbhs_[$section];
		}
		// 未接続で設定があれば接続
		if ( isset( $this->config_[$section] ) ) {
			$cfg = $this->config_[$section];
			$dbh = new Framework_Db();
			if ( !isset($cfg['driver']) ) $cfg['driver'] = null;
			$dbh->connect( $cfg['host'], $cfg['port'], $cfg['database'], $cfg['user'], $cfg['password'], $cfg['driver'] );
			$dbh->setMaster();
			if ( isset($cfg['options']) ) $dbh->setOptions($cfg['options']);
			$this->dbh_[$section] = $dbh;
			return $dbh;
		} else {
			throw new RuntimeException('Configuration for devel DB does not exists.');
		}
	}

	// }}}

}
