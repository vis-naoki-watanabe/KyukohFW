<?php
class AssetController extends AbstractController
{
    public function actionBefore()
    {
        $this->view->path = dirname(__FILE__).'/../asset/'.$this->getRequest('path');
        if(!is_file($this->view->path)) return;
              
        $info = new SplFileInfo($this->view->path);
        
        // 拡張子からmime_type判定
        if($info->getExtension() == 'css') {
            $this->view->mime_type = 'text/css';
        }
        else if($info->getExtension() == 'js') {
            $this->view->mime_type = 'text/javascript';
        }
        else if($info->getExtension() == 'woff') {
            $this->view->mime_type = 'application/font-woff';
        }
        else if($info->getExtension() == 'ttf') {
            $this->view->mime_type = 'application/x-font-ttf';
        }
        else if($info->getExtension() == 'otf') {
            $this->view->mime_type = 'application/x-font-otf';
        }
        else if($info->getExtension() == 'svgf' || $info->getExtension() == 'svg') {
            $this->view->mime_type = 'image/svg+xml';
        }
        else if($info->getExtension() == 'eot') {
            $this->view->mime_type = 'application/vnd.ms-fontobject';
        }
        else {
            $this->view->mime_type = mime_content_type($this->view->path); 
        }
    }

    public function actionAfter()
    {      
    }
    
    public function javascriptAction()
    {       
        if(!is_file($this->view->path)) return;

        //header('Content-Type: text/javascript');
        header('Content-Type: '.$this->view->mime_type);
	require_once( $this->view->path );
    }

    public function cssAction()
    {
        if(!is_file($this->view->path)) return;

        //header('Content-Type: text/css');
        header('Content-Type: '.$this->view->mime_type);
	require_once( $this->view->path );
    }

    public function fontsAction()
    {
        if(!is_file($this->view->path)) return;

        //header('Content-Type: text/css');
        header('Content-Type: '.$this->view->mime_type);
	require_once( $this->view->path );
    }
    
    public function  imagesAction()
    {
        $src = $this->view->path;
        
	$target = $this->getSize($src);
        if(!is_file($src)) return;
        
	$mime_type = 'application/octet-stream';
	$info = new FInfo(FILEINFO_MIME_TYPE);
	$mime_type = $info->file($src);
	header('Content-Type: ' . $mime_type );
	
	/*
	$target = array(
	    'gd' => true,
	    'width' => 20,
	    'height' => 20
	);
	*/

	// そのまま出力
	if( $target == null )
	{
	    readfile($src);
	    exit;
	}
	
	$target['gd'] = true;
	$out = Framework_Image::resize($src, $target);
        
	ImagePNG($out);
    }
    
    private function getSize(&$src)
    {
        $ret = null;
        
        if(preg_match("/([0-9]+)x([0-9]+)/", $src, $ret) )
	{
            $size = $ret[0];
            $src = str_replace('/'.$size, '', $src);
            
            $ret = array(
                'width' => $ret[1],
                'height' => $ret[2],		
            );
        }
        
        $size = $this->getRequest( 'size' );
	if( $size )
	{
	    if(preg_match("/([0-9]+)x([0-9]+)/", $size, $ret) )
	    {
		// print_r($ret);
		$ret = array(
		    'width' => $ret[1],
		    'height' => $ret[2],		
		);
	    }
	}
	return $ret;
    }

    public function dataAction()
    {
        if(!is_file($this->view->path)) return;

	$src = $this->view->path;
        
	//$mime_type = 'application/octet-stream';
        $mime_type = $this->view->mime_type;
	if ( is_file($src) )
	{
	    $info = new FInfo(FILEINFO_MIME_TYPE);
	    $mime_type = $info->file($src);
	}
	header('Content-Type: ' . $mime_type );
	readfile($src);
/*
	$target = array(
	    'gd' => true
	);

	if( choose($params, 'w') )
	{
            $target['width'] = choose($params, 'w');
	}
	if( choose($params, 'h') )
	{
	$target['height'] = choose($params, 'h');
	}

	$out = image_resize($src, $target);

	header('Content-Type: ' . $mime_type );
	ImagePNG($out);*/
    }
}
