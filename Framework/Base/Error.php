<?php
class Framework_Base_Error extends Framework_Model_AbstractSimple
{
    protected $_id = null;
    protected $_message = null;
    protected $_params = null;
    
    public function __construct($id, $message)
    {
        $this->_id = $id;
        $this->_message = $message;
    }
    
    // エラーハンドラ関数
    public function myErrorHandler($errno, $errstr, $errfile, $errline)
    {
        if (!(error_reporting() & $errno)) {
            // error_reporting 設定に含まれていないエラーコードです
            return;
        }

        switch ($errno) {
        case E_USER_ERROR:
            $message = "<b>My ERROR</b> [$errno] $errstr<br />\n";
            $message.= "  Fatal error on line $errline in file $errfile";
            $message.= ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
            $message.= "Aborting...<br />\n";
            echo $message;
            exit(1);
            break;

        case E_USER_WARNING:
            $message = "<b>My WARNING</b> [$errno] $errstr<br />\n";
            break;

        case E_USER_NOTICE:
            $message = "<b>My NOTICE</b> [$errno] $errstr<br />\n";
            break;

        default:
            $message = "Unknown error type: [$errno] $errstr<br />\n";
            break;
        }
        
        $this->_message = $message;

        /* PHP の内部エラーハンドラを実行しません */
        return false;
    }
    
    public function setId($id)
    {
        $this->_id = $id;
    }
    public function setMessage($message)
    {
        $this->_message = $message;
    }
    public function setParams($params)
    {
        $this->_params = $params;
    }
    public function getId()
    {
        return $this->_id;
    }
    
    public function getMessage()
    {
        return $this->_message;
    }
    public function getParams()
    {
        return $this->_params;
    }
}