<?php
class Framework_Base_Exception extends Exception
{
    protected $_error_code = null;

    public function __construct($message, $error_code = null)
    {
        $this->_error_code = $error_code;
        
        parent::__construct($message);
        $this->dump();        
    }
    
    public function getErrorCode()
    {
        return $this->_error_code;
    }
    
    public function dump()
    {
        App::Log()->exception($this->getMessage());
    }
}