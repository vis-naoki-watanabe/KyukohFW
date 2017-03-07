<?php
class Framework_Base_Array
{
    public static function findOne($array, $key, $val)
    {
        $list = App::newList($array);
        $dest = $list->findOne($key, $val);
        if( $dest ) {
            return (array)$dest;
        }
        return null;
    }
    
    public static function find($array, $key, $val)
    {
        $list = App::newList($array);
        $dest_list = $list->find($key, $val);
        if( $dest_list ) {
            $ret = array();
            foreach($dest_list as $dest) {
                $ret[] = (array)$dest;
            }
            return $ret;
        }
        return null;
    }
}