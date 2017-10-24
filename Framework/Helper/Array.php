<?php
class Framework_Helper_Array
{
    protected $array_ = null;
    
    public static function getInstance($array = null)
    {
        $obj = new self();
        if($array) {
            $obj->array_ = $array;
        }
        return $obj;
    }
    
    public function remove($key, $value, $array = null)
    {   
        if(!$array ) {
            $array = $this->array_;
        }
        
        $ret = array();
        foreach($array as $arr) {
            if(array_key_exists($key, $arr) && $arr[$key]==$value) {
                continue;
            }
            
            $ret[] = $arr;
        }
        
        $this->array_ = $ret;
        
        return $ret;
    }
}
?>
