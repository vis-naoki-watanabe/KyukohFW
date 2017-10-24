<?php
class Framework_Web_Error
{
    protected $_fatal_error_list = null;
    protected $_form_error_list = null;
    
    public function __construct()
    {
        $this->_fatal_error_list = App::newList();
        $this->_form_error_list = App::newList();
    }
    
    public function getCount()
    {
        $count = $this->getFatalError()->getCount();
        $count+= $this->getFormError()->getCount();
        return $count;
    }
    
    public function hasError()
    {
        return array(
            'fatal' => $this->getFatalError()->getCount(),
            'form'  => $this->getFormError()->getCount()
        );
    }
    
    public function getError()
    {
        return array(
            'fatal' => $this->getFatalError(),
            'form' => $this->getFormError()
        );
    }
    
    public function setFatalError($list, $value = null)
    {
        if($value !== null) {
            $this->addFatalError($list, $value);
        }
        else if($list && is_array($list)) {
            foreach($list as $id => $message) {
                $this->addFatalError($id, $message);
            }
        }
    }
    
    public function addFatalError($id, $message)
    {
        $error = new Framework_Base_Error($id, $message);
        $this->_fatal_error_list->add($error);
    }
    
    public function getFatalError()
    {
        return $this->_fatal_error_list;
    }
    
    // ================
    public function setFormError($list, $value = null)
    {
        if($value !== null) {
            $this->addFatalError($list, $value);
        }
        else if($list) {
            // 配列
            if(is_array($list)) {
                foreach($list as $id => $message) {
                    $this->addFormError($id, $message);
                }
            }
            // Framework_Web_Error
            else if($list instanceof self) {
                $this->_fatal_error_list->add($list->getFatalError());
                $this->_form_error_list->add($list->getFormError());
            }
        }
    }
    
    public function addFormError($id, $message)
    {
        $error = new Framework_Base_Error($id, $message);
        $this->_form_error_list->add($error);
    }
    
    public function getFormError()
    {
        return $this->_form_error_list;
    }
}
