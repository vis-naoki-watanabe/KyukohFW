<?php
class Framework_Base_Application
{    
    // {{{ public function __construct($config=null)

    /**
     * コンストラクタ
     * @return void
     */
    public function __construct()
    {        
        // エラー出力設定
        $this->errorSetting();
                
        $this->init();
    }

    // }}}

    // {{{ public function __get( $name )

    /**
     * Getter:マジックメソッド
     * @return 
     */    
    public function __get( $name )
    {
        if( $name == 'config' ) {
            return $this->getConfig();
        }
        else {
            App::ex('method is not found: '.$name);
        }
    }

    // }}}
    
    // {{{ public function getConfig()

    /**
     * 設定を返却する
     * @return array
     */
    public function getConfig($key=null)
    {
        return App::getConfig($key);
    }

    // }}}

    // {{{ public function errorSetting()

    /**
     * エラーセッティング
     * @return void
     */
    public function errorSetting()
    {
        // エラー出力する場合
        error_reporting(ERROR_REPORTING);
        ini_set( 'display_errors', DISPLAY_ERRORS?1:0 );
    }

    // }}}
}