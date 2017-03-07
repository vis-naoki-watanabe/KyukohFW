<?php
class Framework_Web_Request
{
    protected $request = null;
    
    public function __construct( $route_params = array() )
    {
        $request = array(
            'request' => !$route_params?$_REQUEST:array_merge($_REQUEST,$route_params),
            'post' => $_POST,
            'query' => $_GET,
        );

        $this->request = $request;
    }
    
    // 特殊文字排除する
    public function convHtmlSpecialChars( $val )
    {
        if( !$val || $val=='' || is_array($val)) return $val;
        return htmlspecialchars( $val );
    }

    public function getQuery( $key = null, $default = null )
    {
	$params = App::choose( $this->request, 'query' );
	if( $key === null ) return $params;
	$ret = App::choose( $params, $key, $default );
        return $this->convHtmlSpecialChars($ret);
    }

    public function getPost( $key  = null, $default = null )
    {
	$params = App::choose( $this->request, 'post' );
	if( $key === null ) return $params;
	$ret = App::choose( $params, $key, $default );
        return $this->convHtmlSpecialChars($ret);
    }

    public function getRequest( $key = null, $default = null )
    {
	$params = App::choose( $this->request, 'request' );
	if( $key === null ) return $params;
	$ret = App::choose( $params, $key, $default );
        return $this->convHtmlSpecialChars($ret);
    }

    public function getParam( $key  = null, $default = null )
    {
	$params = App::choose( $this->request, 'param' );
	if( $key === null ) return $params;
	if( App::choose( App::choose( $this->request, 'param' ), $key ) !== null ) $ret = App::choose( App::choose( $this->request, 'param' ), $key );
        if(isset($ret)) { $default = $ret; }
	$ret = App::choose( App::choose( $this->request, 'request' ), $key, $default );
        return $this->convHtmlSpecialChars($ret);
    }
    
    public function __get( $name )
    {
        return $this->getParam( $name );
    }
    
    public function setRequest( $add_params = array() )
    {
	$params = App::choose( $this->request, 'request' );
	foreach( $add_params as $key => $val )
	{
	    $params[$key] = $val;
	}
	$this->request['request'] = $params;
    }

    public function getKey( $value = null )
    {
        $params = App::choose( $this->request, 'param' );
        if( $params ) {
            foreach( $params as $key => $val ) {
                return $key;
            }
        }
        return $value;
    }
}