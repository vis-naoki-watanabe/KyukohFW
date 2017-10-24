<?php
class Framework_Net_HttpServer
{
    protected $request = null;
    protected $files   = null;
    
    // {{{ public function __construct($config=null)

    /**
     * コンストラクタ
     * @param Framework_Web_Request $req
     * @return void
     */
    public function __construct()
    {        
        $this->files = $_FILES;
        
        // エラー出力設定
        //$this->errorSetting();
                
        $this->init();
    }

    // }}}
    
    public function init()
    {
    }
    
    public function hasFiles()
    {
        return is_array($this->files) && count($this->files);
    }
    
    public function moveUploadFiles($path)
    {
        $ret = null;
        if($this->files) {
            $file_list = Framework_File::setFILES($this->files);

            foreach($file_list as $file) {
                if(!$ret) {
                    $ret = array();
                }
                $ret[] = $file->moveFromTemp($path, null, true);
            }
        }
        return $ret;
    }
}