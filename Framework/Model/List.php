<?php

class Framework_Model_List implements IteratorAggregate
{

	// {{{ properties

	/**
	 * model オブジェクトを格納する配列
	 * @var array
	 */
	protected $models_ = array();

	/**
	 * 該当データ以外の総数を格納する変数
	 * @var int
	 */
	protected $total_count_ = 0;

	/**
	 * total_count_ をロックするか
	 * @var boolean
	 */
	protected $lock_total_count_ = false;

	// }}}

	// {{{ public function __construct( $list = null )

	/**
	 * コンストラクタ
	 * @param	array $list
	 * @return	void
	 */
	public function __construct( $list = null )
	{
		if ( is_array( $list ) ) {
			$this->setList( $list );
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
		//require_once 'Framework/Model/Iterator.php';
		return new Framework_Model_Iterator( $this->models_ );
	}

	// }}}

	// {{{ public function add( $model )

	/**
	 * モデルをリストの最後に追加
	 * @param	mixed $model
	 * @return	void
	 */
	public function add( $model )
	{
		if ( is_object( $model ) && get_class( $model ) == 'Framework_Model_List' ) {
			foreach ( $model as $v ) {
				$this->models_[] = $v;
				if ( ! $this->lock_total_count_ ) $this->total_count_++;
			}
/* 2011.05.31 仕様用途不明の為、コメントアウト
		} else if ( is_array( $model ) ) {
			$this->models_[] = array_merge( $this->models_, $model );
			if ( ! $this->lock_total_count_ ) $this->total_count_ += sizeof( $model );
*/
		} else {
			$this->models_[] = $model;
			if ( ! $this->lock_total_count_ ) $this->total_count_++;
		}
	}

	// }}}

	// {{{ public function first()

	/**
	 * リストの最初のデータを返却
	 * @return	object
	 */
	public function first()
	{
		return reset( $this->models_ );
	}

	// }}}

	// {{{ public function last()

	/**
	 * リストの最後のデータを返却
	 * @return	object
	 */
	public function last()
	{
		return end( $this->models_ );
	}

	// }}}

	// {{{ public function push( $model )

	/**
	 * モデルをリストの最後に追加
	 * @param	mixed $model
	 * @return	void
	 */
	public function push( $model )
	{
		$this->add( $model );
	}

	// }}}

	// {{{ public function unshift( $model )

	/**
	 * モデルをリストの最初に追加
	 * @param	mixed $model
	 * @return	void
	 */
	public function unshift( $model )
	{
		if ( is_array( $model ) ) {
			$new_models = array();
			foreach ( $model as $v ) { $new_models[] = $v; }
			foreach ( $this->models_ as $v ) { $new_models[] = $v; }
			$this->models_ = $new_models;
		} else {
			array_unshift( $this->models_, $model );
		}
	}

	// }}}

	// {{{ public function setList( $list )

	/**
	 * リストのセット
	 * @param	array $list
	 * @return	void
	 */
	public function setList( $list )
	{
		if ( is_array( $list ) ) {
                        // 要素の型が配列だったらオブジェクトに変換
                        if(count($list)>0 && is_array($list[0])) {
                            $list_ = array();
                            foreach($list as $array) {
                                $list_[] = (object)$array;
                            }
                            $list = $list_;
                        }
			$this->models_ = $list;
		}
	}

	// }}}

	// {{{ public function setTotalCount( $count )

	/**
	 * 該当データ以外の総数をセット
	 * @param	integer $count
	 * @return	void
	 */
	public function setTotalCount( $count )
	{
		$this->total_count_ = $count;
		$this->lock_total_count_ = true;
	}

	// }}}

	// {{{ public function getTotalCount()

	/**
	 * 該当データ以外の総数を返却
	 * @return	integer
	 */
	public function getTotalCount()
	{
		return $this->total_count_;
	}

	// }}}

	// {{{ public function getCount()

	/**
	 * データ件数を返却
	 * @return	integer
	 */
	public function getCount()
	{
		return sizeof( $this->models_ );
	}

	// }}}

	// {{{ public function slice( $start, $num )

	/**
	 * リストを切り取る
	 * @param	integer $start
	 * @param	integer $num
	 * @return	Framework_Model_List
	 */
	public function slice( $start, $num )
	{
		$this->models_ = array_slice( $this->models_, $start, $num );
		return $this;
	}

        public function get($index)
        {
            if( $this->getCount()>=$index ) 
            {
                $list = $this->slice($index, 1);
                if( $list ) {
                    return $list->first();
                }
                return null;
            }
            return null;
        }
	// }}}

	// {{{ public function reverse( $preserve_keys=false )

	/**
	 * リストを逆順にする
	 * @param boolean $preserve_keys
	 * @return Framework_Model_List
	 */
	public function reverse( $preserve_keys=false )
	{
		$this->models_ = array_reverse($this->models_, $preserve_keys);
		return $this;
	}

	// }}}

	// {{{ public function sort( $name, $order=SORT_DESC, $sort_flags=SORT_REGULAR )

	/**
	 * リストのソート
	 * @param string $name        パラメーター名
	 * @param integer $order      ソート順
	 * @param integer $sort_flags ソートタイプ
	 * @return Framework_Model_List
	 */
	public function sort( $name, $order=SORT_DESC, $sort_flags=SORT_REGULAR )
 	{
 		$keys = array();
 		foreach ( $this->models_ as $k => $v ) {
	 		// オーダーが正しいか?
	 		if ( $order != SORT_ASC && $order != SORT_DESC ) {
	 			S::error( 'Invalid order type' );
	 		}
 			$keys[$k] = $v->$name;
 		}

		array_multisort( $keys, $order, $sort_flags, $this->models_ );

		return $this;
 	}

 	// }}}

	// {{{ public function merge( $list )

	/**
	 * リストの末尾に指定されたリストを結合し、結合後のリストを返す
	 * @param Framework_Model_List $list
	 * @return Framework_Model_List
	 */
	public function merge( Framework_Model_List $list )
	{
		$this->setTotalCount( $this->getTotalCount() + $list->getTotalCount() );
		$this->setList( array_merge( $this->toArray( true ), $list->toArray( true ) ) );
		return $this;
	}

	// }}}

	// {{{ public function shuffle()

	/**
	 * リストをシャッフルする
	 * @return Framework_Model_List
	 */
	public function shuffle()
	{
		shuffle($this->models_);
		return $this;
	}
	// }}}

	// {{{ public function toArray( $as_object = false )

	/**
	 * Array へ変換する
	 * @param	boolean $as_object
	 * @return	array
	 */
	public function toArray( $as_object = false )
	{
		$result = array();
		foreach ( $this as $model ) {
			if ( $as_object ) {
				$result[] = $model;
			} else {
				if ( is_object($model) && get_class($model) === 'stdClass' ) {
					$result[] = (array)$model;
				} else {
					$result[] = $model->toArray();
				}
			}
		}
		return $result;
	}

	// }}}

	// {{{ public function filter()

	/**
	 * 条件を満たす要素のみのリストを返却する
	 * @param Closure $closure 要素を受け取り、条件を満たすかどうかを返却するクロージャ
	 */
	public function filter( $closure, $options = null )
	{
		if ( ! is_object( $closure ) || get_class( $closure ) !== 'Closure' ) {
			// 引数がクロージャではなかった場合エラー
			S::error( 'Argument is not closure' );
			S::ex( 'system' );
		}

		$result = new self();
		foreach( $this as $object ) {
			if ( $closure( $object, $options ) ) {
				$result->add( $object );
			}
		}
		return $result;
	}

	// }}}

        // クロージャーを簡単に使うため
        // キー単体指定 $key, $val
        // キー複数指定 array( $key=>$val, ... )
        public function find( $key, $val = null )
        {
            $closure = function( $obj ) use ( $key, $val ) {
                if( !is_array($key) ) {
                    return ($obj->$key == $val);
                } else {
                    $arr = $key;
                    $flag = true;
                    foreach( $arr as $key => $val ) {
                        if($obj->$key != $val) $flag = false;
                    }
                    return $flag;
                }
            };
            return $this->filter($closure);
        }
        
        public function findOne( $key, $val = null )
        {
            $list = $this->find($key, $val);
            if( $list && $list->getCount()>0 ) {
                return $list->first();
            }
            return null;
        }
}
