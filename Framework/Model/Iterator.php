<?php

class Framework_Model_Iterator implements Iterator {

	// {{{ properties
	
	/**
	 * イテレート対象の配列
	 */
	private $items_ = array();

	/**
	 * アイテム返却時のクラス名
	 */
	private $class_ = NULL;

	// }}}

	// {{{ public function __construct( $items, $class = NULL )

	/**
	 * コンストラクタ
	 *
	 * <pre>
	 * $item にはIterate対象となるデータ(配列)をセットする。
	 * $classを指定すると、current()がデータを返却する際に、そのクラス名
	 * のインスタンスを生成して、返却する。
	 * 指定しなければ、$itemsの現在の位置のデータが返却される。
	 * </pre>
	 *
	 * @param   $items
	 * @param   $class
	 * @return  void
	 */
	public function __construct( $items, $class = NULL ) {
		$this->items_ = $items;
		$this->class_ = $class;
	}

	// }}}

	// {{{ public function rewind()

	/**
	 * Iterator implementation
	 * @return	void
	 */
	public function rewind() {
		reset( $this->items_ );
	}

	// }}}

	// {{{ public function key()

	/**
	 * Iterator implementation
	 * @return	string
	 */
	public function key() {
		return key( $this->items_ );
	}

	// }}}

	// {{{ public function current()

	/**
	 * Iterator implementation
	 * @return	object
	 */
	public function current() {
		$key = key( $this->items_ );
		if ( is_null( $key ) ) {
			return $key;
		}
		if ( is_null( $this->class_ ) ) {
			return $this->items_[$key];
		} else {
			return new $this->class_( $this->items_[$key] );
		}
	}

	// }}}

	// {{{ public function next()

	/**
	 * Iterator implementation
	 * @return	void
	 */
	public function next() {
		next( $this->items_ );
	}

	// }}}

	// {{{ public function valid()

	/**
	 * Iterator implementation
	 * @returni	boolean
	 */
	public function valid() {
		return ! is_null( key( $this->items_ ) );
	}

	// }}}

}

