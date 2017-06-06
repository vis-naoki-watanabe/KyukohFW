<?php
class AbstructController extends Framework_Controllers_Abstruct
{
    public function init()
    {
    }
    
    final public function preDispatch()
    {   
    }
    
    final public function postDispatch()
    {   
    }
    
    private function htmlspecialchars_($val)
    {
        if( !$val || $val=='' ) return $val;
        if(!is_array($val)) {
            return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
        }
        else {
            foreach($val as &$v) {
                $v = htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
            }
        }
        return $val;
    }
    
    public function getPropReq($get_token_key = null)
    {
        // トークンから取得する
        if($get_token_key) {
            $token = $this->getRequest($get_token_key);
            if($token) {
                return $this->getToken($token);
            }
        }

	$ret = array();
	$prefix = "prop_";
        $params_prefix = "params_";
        
	foreach( $this->getRequest() as $key => $val_ )
	{
            $val = str_replace("\\\"", "\"", $val_);
            $val = $this->htmlspecialchars_($val);
            
            if( !preg_match('/^prop_/',$key) ) continue;
            //$new_key = str_replace( $prefix, "", $key );
            //$ret[$new_key] = $val;
            $new_key = str_replace( $prefix, "", $key );
            // paramsはまとめる
            if( !preg_match('/^params_/',$new_key) ) {
                $ret[$new_key] = $val;
            } else {
                $new_key = str_replace( $params_prefix, "", $new_key );
                $ret['params'][$new_key] = $val;
            }
	}
        if(isset($ret['params'])) {
            $ret['params'] = App::raw_json_encode( $ret['params'] );
        }
	return $ret;
    }
}
