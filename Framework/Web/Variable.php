<?php
class Framework_Web_Variable
{
    protected $data_ = array();
    
    public function __construct()
    {
    }
    
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
        $this->data_[$name] = $value;
    }

    // }}} 
    
    // 改行コードをBR
    public static function nl2br( $val_ )
    {
	$val = $val_;
	$val = str_replace("\r\n","\n",$val);
	$val = str_replace("\r","\n",$val);
	$val = str_replace("\n","<br/>",$val);
	return $val;
    }
}