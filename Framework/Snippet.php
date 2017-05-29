<?php
class Framework_Snippet
{
	protected static $snippet = array();
	protected $name_ = null;
	protected $templ_ = null;
	protected $data_ = array();
//	protected $color_map_ = array();
//	protected $fcolor_map_ = array();
//	protected $color_ = array();
//	protected $color_id_ = null;
//	protected $fsize_map_ = array();
//	protected $css_map_   = array();
//	protected $fgroup_ = null;
//	protected $id_ = null;
//	protected $lines_ = array();
//	protected $lines_c_ = 0;
//	protected $rows_ = array();
//	protected $mobile_ = null;
//	protected $link_ = null;
//	protected $is_vga_ = array();
//	protected $draw_bg_ = false;
	protected static $snippet_dir_ = null; 	//スニペットディレクトリ
        
        protected $begin_ = false;
        protected $end_   = "";

        protected $id_ = 0; 
        
	// {{{ public static function &getInstance( $name )

	/**
	 * インスタンスを返却
	 * @param	string $name
	 * @return	Framework_Snippet
	 */
	public static function &getInstance( $name )
	{
            App::debug("snippet:{$name}");
		if ( ! isset( self::$snippet[$name] ) ) {
                    self::$snippet[$name] = new self( $name );
		}
                else {
                    self::$snippet[$name]->id_++;                    
                }
		$snpt = self::$snippet[$name];
                
		$snpt->reset();
		//$snpt->setColor( 'default' );
		return $snpt;
	}

	// }}}
        
        public function isFirst()
        {
            return $this->id_ === 0;
        }

	// {{{ public static function &getNewInstance( $name )

	/**
	 * 新しいインスタンスを返却
	 * @param	string $name
	 * @return	Framework_Snippet
	 */
	public static function &getNewInstance( $name )
	{
		$snpt = new self( $name );
		//$snpt->setColor( 'default' );
		return $snpt;
	}

	// }}}
	
	// {{{ protected function __construct( $name )

	public static function test()
	{
		return "abcd";
	}
        
        public function setEnd($text)
        {
            $this->end_ = $text;
        }
        
        
        public function begin()
        {
            $this->begin_ = true;
            return $this->render();
        }
        public function isBegin()
        {
            return isset($this->begin_) && $this->begin_;
        }
        public function end()
        {
            echo $this->end_;
        }
	/**
	 * コンストラクタ
	 * @param	string $name
	 * @return	void
	 */
	protected function __construct( $name )
	{
            $this->setTemplate( $name );
            $this->name_ = $name;

            /*if ( S::hasCtnr('snippet_maps') ) {
                    $maps = S::getCtnr('snippet_maps');
                    $this->color_map_  = $maps['color'];
                    $this->fcolor_map_ = $maps['fcolor'];
                    $this->fsize_map_  = $maps['fsize'];
                    $this->css_map_    = $maps['css'];
            } else {
                    $this->color_map_ = include APP_SNIPPET_PATH . self::COLOR_MAP;
                    $this->fcolor_map_ = include APP_SNIPPET_PATH . self::FCOLOR_MAP;
                    $this->fsize_map_ = include APP_SNIPPET_PATH . self::FSIZE_MAP;
                    if ( defined( 'APP_CSS_MAPPING' ) && is_readable( APP_SNIPPET_PATH . self::CSS_MAP ) ) {
                            $this->css_map_ = include APP_SNIPPET_PATH . self::CSS_MAP;
                    }
                    $maps = array(
                            'color'  => $this->color_map_,
                            'fcolor' => $this->fcolor_map_,
                            'fsize' => $this->fsize_map_,
                            'css' => $this->css_map_ );
                    S::addCtnr($maps, 'snippet_maps');
            }
            */
	}

	// }}}

	// {{{ public function reset()

	/**
	 * 可変データを元に戻す
	 * @return	Framework_Snippet
	 */
	public function reset($all_rest = false)
	{
            $this->data_ = array();
            if($all_rest) {
                $this->id_ = null;
            }
            $this->lines_ = array();
            $this->lines_c_ = 0;
            $this->draw_bg_ = false;
            $this->color_id_ = null;
            $this->color_ = array();
            $this->setTemplate( $this->name_ );
            $this->rows_ = array();
            return $this;
	}

	// }}}

	// {{{ public function render( $no_echo = false )

	/**
	 * 標準出力に出力する
	 * @param	boolean $no_echo
	 * @return	Framework_Snippet
	 */
	public function render( $no_echo = false )
	{
            ob_start();
            /* TODO: 何この処理？
            if ( ! is_null( $this->id_ ) ) {
                echo sprintf( '<a%s></a>', $this->id_ ) . "\n";
            }
             */
            include $this->templ_;
            if ( $no_echo ) {
                $content = ob_get_contents();
                ob_end_clean();
                return $content;
            } else {
                ob_end_flush();
                return $this;
            }
	}

	// }}}

	// {{{ public function __get( $name )

	/**
	 * GETTER アクセッサ
	 * @param	string $name
	 * @return	mixed
	 */
	public function __get( $name )
	{
		$name = strtolower( $name );
		if ( isset( $this->data_[$name] ) ) {
			return $this->data_[$name];
		} else {
			return null;
		}
	}

	// }}}

	// {{{ public function __set( $name, $value )

	/**
	 * SETTER アクセッサ
	 * @param	string $name
	 * @param	mixed $value
	 * @return	void
	 */
	public function __set( $name, $value )
	{
		$name = strtolower( $name );
		//if ( $name == 'fontsize' ) $name = 'fsize';
		$this->data_[$name] = $value;
	}

	// }}}

	// {{{ public function __call( $method, $args )

	/**
	 * set{$Key}($value) でセッターをコールする
	 * @param	string $method
	 * @param	array $args
	 * @return	Framework_Snippet
	 */
	public function __call( $method, $args )
	{
            $method = strtolower( $method );
            if ( substr( $method, 0, 3 ) === 'set' && isset( $args[0] ) ) {
                    $name = substr( $method, 3 );
                    if ( $name !== '' ) {
                            $this->data_[$name] = $args[0];
                    }
                    return $this;
            }
            return $this;
		//S::ex( 'system' );
	}

	// }}}
        
        public function getData()
        {
            return $this->data_;
        }

	// {{{ public function setData( $data )

	/**
	 * 可変データをまとめてセットする
	 * @param	array $data
	 * @return	Framework_Snippet
	 */
	public function setData( $data )
	{
            if ( is_array( $data ) ) {
                $this->data_ = array_merge( $this->data_, $data );
            }
            return $this;
	}

	// }}}

	// {{{ protected function choose( $name, $default )

	/**
	 * 設定データがあればそれを返却、なければ $default を返却
	 * @param	string $name
	 * @param	mixed $default
	 * @return	mixed
	 */
	protected function choose( $name, $default = null)
	{            
            if ( isset( $this->data_[$name] ) ) {
                return $this->data_[$name];
            } else {
                return $default;
            }
	}

	// }}}
        
        /*
        setOptions(array(
            'name' => 'hoge'
        ))
        setName='page';
        両方を走査
        どちらにもない場合は$default
        */
        /*protected function optChoose($name, $default = null)
        {
            $ret = $default;
            
            $options = $this->choose('options');
            if(isset($options[$name])) {
                $ret = $options[$name];
            }
            if ( isset( $this->data_[$name] ) ) {
                $ret = $this->data_[$name];
            }
            return $ret;
        }*/

	// {{{ protected function setTemplate( $name )

	/**
	 * テンプレートをセット
	 * @param	string $name
	 * @return	void
	 */
	protected function setTemplate( $name )
	{
            $path = sprintf('%s/App/views/snippets', App::getRootPath());
            $render_path = $path . '/' . $name . '.php';
/*
		// 該当ファイルがない場合、共通モジュールのsnippetを探す
		if ( ! is_readable( $render_path ) && defined('COMMON_SNIPPET_PATH') ) {
			$render_path = COMMON_SNIPPET_PATH . '/' . $name . '.php';
			if( is_dir( COMMON_SNIPPET_PATH .'/fp' ) ){
				$render_path = COMMON_SNIPPET_PATH . '/fp/' . $name . '.php';
			}
			if( MOBILE_IS_SMARTPHONE && is_dir( COMMON_SNIPPET_PATH .'/sp' ) ){
				$render_path = COMMON_SNIPPET_PATH . '/sp/' . $name . '.php';
			}
		}
*/
		if ( ! is_readable( $render_path ) ) {
			throw new RuntimeException( 'snippet render file is not found. file=' . $render_path );
		}
		$this->templ_ = $render_path;
		return $this;
	}

	// }}}
}
