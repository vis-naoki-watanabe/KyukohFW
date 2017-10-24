<?php
class Framework_Helper_Url
{
    public static function http_build_query($query = array(), $url=null)
    {
        if(!is_array($query)) {
            $query = self::parse_query($query);
        }
        
        $base = [
            'url' => null,
            'query' => [],
            'fragment' => null
        ];
        
        if($url) {
            $base = self::parse_url($url);
        }
        
        $ret = http_build_query(array_merge($query, $base['query']));
        
        if($base['url']) {
            $ret = $base['url']."?{$ret}";
        }
        
        if($base['fragment']) {
            $ret.= "#{$base['fragment']}";
        }

        return $ret;
    }
    
    // {{{ public static function parse_url( $url )

    /**
     * urlをurl（パス）部とクエリー部（配列）に分解
     * @param string
     * return array
     */
    public static function parse_url( $url )
    {
        $buff = parse_url($url);
        
        $query = @$buff['query'];
        $fragment = @$buff['fragment'];
        if( $query ) {
            $url = str_replace('?'.$query, '', $url);
            if($fragment) {
                $url = str_replace('#'.$fragment, '', $url);
            }
            $ret = array(
                'url'   => $url,
                'query' => self::parse_query( $query ),
                'fragment' => $fragment
            );
        } else {
            $ret = array(
                'url'   => $url,
                'query' => array(),
                'fragment' => null
            );
        }
        return $ret;
    }
    
    // }}}

    // {{{ public static function parse_query( $query )

    /**
     * URLのクエリー部（配列）に分解
     * @param string
     * return array
     */
    public static function parse_query( $query )
    {
        $querys = explode('&', $query);
        $ret = array();
        foreach( $querys as $val )
        {
            $buff = explode('=', $val);
            $ret[$buff[0]] = $buff[1];
        }
        return $ret;
    }
    
    // }}}
}