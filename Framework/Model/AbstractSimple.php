<?php
class Framework_Model_AbstractSimple
{   
    // {{{ public function __get( $name )
    
    /**
     * GETTER アクセッサ
     * @param	string $name
     * @return	mixed
     */
    public function __get( $name )
    {
        // メッソッドが存在する場合はメソッド実行
        $method = 'get'.App::Camelize($name);
        if( method_exists( $this, $method ) ) { 
            return $this->$method();
        }
    }

    // }}}

    // {{{ public function __set( $name )
    
    /**
     * SETTER アクセッサ
     * @param	string $name
     * @param	string $value
     * @return	mixed
     */
    public function __set($name, $value)
    {            
        // メッソッドが存在する場合はメソッド実行
        $method = 'set'.App::Camelize($name);
        if( method_exists( $this, $method ) ) {
            $this->$method($value);
        }
    }

    // }}}
}