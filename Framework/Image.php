<?php
class Framework_Image
{
    public function __construct()
    {
    }
    
    //アスペクト非を保った状態でリサイズ後の高さ・幅を返します。
    public static function getImageSize_($image, $target)
    {
	$resize = false;
	$info = getimagesize($image);
	$sw = $width  = $info[0];
	$sh = $height = $info[1];

	$mode = 0;

	//ターゲットサイズ
	if(isset($target['size']) && $target['size']>0)
	{
	    $target_size = $target['size'];
	}
	else
	{
	    if(isset($target['width']) && $target['width'] > 0)
	    {
		$target_size = $target['width'];
		$mode|= 0x01;
	    }

	    if(isset($target['height']) && $target['height'] > 0)
	    {
		$target_size = $target['height'];
		$mode|= 0x2;
	    }
	}

	//上限設定
	$limit = false;
	if(isset($target['limit']))
	{
	    $limit = $target['limit'];
	}

	//リサイズなし
	if(!isset($target['size']) && !isset($target['width']) && !isset($target['height']))
	{
	}
	//リサイズあり
	else
	{
	    //両サイズ指定されたので比率無視
	    if($mode == 0x03)
	    {
		if(($sw > $target['width'] && $sh > $target['height']) || !$limit)
		{
		    if($sw != $target['width'] && $sh != $target['height'])
		    {
			$sw = $target['width'];
			$sh = $target['height'];
			$resize = true;
		    }
		}
	    }
	    //比率を保つ
	    else
	    {
		if(($sw >= $sh || $mode == 0x01) && ($sw > $target_size || !$limit))
		{
			$sh = floor(($sh*$target_size)/$sw);
			$sw = $target_size;
			$resize = true;
		}
		else if(($sw < $sh || $mode == 0x10) && ($sh > $target_size || !$limit))
		{
			$sw = floor(($sw*$target_size)/$sh);
			$sh = $target_size;
			$resize = true;
		}
	    }
	}

	$ret = array(
	    "width"  => $sw,
	    "height" => $sh,
	    "resize" => $resize
	);

	return $ret;
    }
    
    public static function clipping()
    {

    }
    
    public static function resizeGd( $src, $dest_width, $dest_height, $dest = null, $clip = null )
    {
	$dest_image = null;

	if( is_file($src) )
	{
	    list($width, $height, $imageType) = getimagesize($src);
	    switch ($imageType)
	    {
		case IMAGETYPE_GIF:
		    $imageType = "gif";
		    break;
		case IMAGETYPE_JPEG:
		    $imageType = "jpeg";
		    break;
		case IMAGETYPE_PNG:
		    $imageType = "png";
		    break;
		default:
		    return false;
	    }
	    // 元画像の読み込み
	    $imageFunc = "imagecreatefrom{$imageType}";
	    $src_image = $imageFunc($src);

	    // 空画像を用意
	    $dest_image = imagecreatetruecolor($dest_width, $dest_height);
	    // 元画像を空画像にリサイズコピー
	    if( !$clip ) {
		imagecopyresampled($dest_image, $src_image, 0, 0, 0, 0, $dest_width, $dest_height, $width, $height);
	    }
	    // クリッピング
	    else {
		imagecopyresampled($dest_image, $src_image
			, 0, 0
			, $clip['x'], $clip['y']
			, $dest_width, $dest_height
			, $clip['width'], $clip['height']
		);		
	    }

	    // orientation修正
	    if( $imageType == 'jpeg' ) {
		$exif = exif_read_data($src);
		if( $exif && isset($exif['Orientation']) ) {
		    $dest_image = self::fixedOrientation($exif['Orientation'], $dest_image);
		}
	    }

	    // リサイズコピーした画像を保存
	    if( $dest )
	    {
		$imageFunc = "image{$imageType}";
		$imageFunc($dest_image, $dest);

		// メモリ上の画像データを破棄
		imagedestroy($src_image);
		imagedestroy($dest_image);

		$dest_image = $dest;
	    }
	}
	return $dest_image;
    }

    public static function resize($image, $target, $dest_image = null)
    {
	//アルファチャンネル制御
	//imagesavealpha($img, TRUE);
	$size = self::getImageSize_($image, $target);

	if(($size['resize'] || isset($target['pecl']) || isset($target['gd']))  && is_file($image))
	{
	    //GD版（バイナリを返す
	    if( isset($target['gd']))
	    {
		$image = self::resizeGd( $image, $size['width'], $size['height']);
		$dest_image = $image;
	    }
	    //PECL版 (バイナリを返す)
	    else if(isset($target['pecl']))
	    {
		$image = new Imagick($image); 
		$image->thumbnailImage($size['width'], $size['height']);
		$dest_image = $image;
	    }
	    //コマンドライン版 (ファイル名を返す)
	    else
	    {
		$com.= IMAGE_MAGICK_PATH."convert -geometry ";
		$com.= $size['width']."x".$size['height']." ";
		$com.= $image." ".$dest_image;

		shell_exec($com);
		//echo $com;
	    }
	}
	//ファイル名を返す
	else
	{
		$dest_image = $image;
	}

	return $dest_image;
    }
    
    public static function fixedOrientation( $orientation = 1, $src )
    {
	// pathの場合（JPEGオブジェクトに変換）
	//$src = imagecreatefromjpeg($src_path);
	$dest = $src;
	// 回転は反時計回り
	switch( $orientation)
	{
	// 1:回転無し
	    case 1:
	    break;
	// 2:左右反転
	    case 2:
		$dest = self::flip( $dest, self::IMG_FLIP_VERTICAL );
	    break;
	// 3:180°回転
	    case 3:
		$dest = imagerotate( $dest, 180, 0 );
	    break;
	// 4:上下反転
	    case 4:
		$dest = self::flip( $dest, self::IMG_FLIP_HORIZONTAL );
	    break;
	// 5:90°回転した後、上下反転
	    case 5:
		$dest = imagerotate( $dest, 90, 0 );
		$dest = self::flip( $dest, self::IMG_FLIP_HORIZONTAL );
	    break;
	// 6:270°回転
	    case 6:
		$dest = imagerotate( $dest, 270, 0 );
	    break;
	// 7:90°回転した後、左右反転
	    case 7:
		$dest = imagerotate( $dest, 90, 0 );
		$dest = self::flip( $dest, self::IMG_FLIP_VERTICAL );
	    break;
	// 8:90°回転
	    case 8:
		$dest = imagerotate( $dest, 90, 0 );
	    break;
	}
	return $dest;
    }
    
    // PHP5.5はimageflipがあるが5.4以下はないので下記のメソッドが必要
    const IMG_FLIP_HORIZONTAL = 1;	    // 上下反転
    const IMG_FLIP_VERTICAL = 2;	    // 左右反転
    public static function flip( $imgsrc, $mode )
    {
	// 上下反転
	if( $mode == self::IMG_FLIP_HORIZONTAL )
	{
	    $x=imagesx($imgsrc);
	    $y=imagesy($imgsrc);
	    $flip=imagecreatetruecolor($x,$y);
	    if(imagecopyresampled($flip,$imgsrc,0,0,0,$y-1,$x,$y,$x,0-$y)){
		    return $flip;
	    }else{
		    return $imgsrc;
	    }
	}
	// 左右反転
	else if( $mode == self::IMG_FLIP_VERTICAL )
	{
	    $x=imagesx($imgsrc);
	    $y=imagesy($imgsrc);
	    $flip=imagecreatetruecolor($x,$y);
	    if(imagecopyresampled($flip,$imgsrc,0,0,$x-1,0,$x,$y,0-$x,$y)){
		    return $flip;
	    }else{
		    return $imgsrc;
	    }
	}
	return null;
    }
}