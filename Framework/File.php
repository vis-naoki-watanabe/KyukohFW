<?php
class Framework_File
{
    const DEFAULT_MIME_TYPE = "application/octet-stream";
    
    protected $path = null;
    protected $src_name = null;         // アップロード元の名前
    protected $temp = null;
    protected $type = null;
    protected $mime_type = null;
    
    public function __construct()
    {
        
    }
    
    public static function setFILES($datas)
    {
        $datas = self::separate($datas);
        $list = new Framework_Model_List();
        foreach($datas as $data) {
            $file = self::setFILE($data);
            if($file) {
                $list->add($file);
            }
        }
        return $list;
    }
    
    public static function separate($datas)
    {
        $ret = array();
        foreach($datas as $key => $data) {
            foreach($data as $prop => $val) {
                if($val) {
                     if(is_array($val)) {
                         foreach($val as $id => $v) {
                             $ret[$key."_".$id][$prop] = $v;
                         }
                     }
                     else {
                         $ret[$key."_0"][$prop] = $val;
                     }
                }
            }
        }
        $datas = array();
        foreach($ret as $key => $data) {
            list($name, $id) = explode("_", $key);
            $datas[] = array_merge(array(
                'key_name' => $name,
                'id'   => $id
            ), $data);
        }
        return $datas;
    }
    
    public static function setFILE($data)
    {
        if(@$data['tmp_name'] && is_file(@$data['tmp_name']) && filesize(@$data['tmp_name']) == @$data['size']){// && @$data['error'] === 0) {
            $file = new self();
            $file->src_name = @$data['name'];
            $file->temp = @$data['tmp_name'];
            if(@$data['type']) {
                $file->type = @$data['type'];
            }
            $file->mime_type = mime_content_type($file->temp);
            return $file;
        }
        return null;
    }
    
    public function getMimeType()
    {
        return $this->mime_type?$this->mime_type:self::DEFAULT_MIME_TYPE;
    }
    
    public function getBody()
    {
        if($this->temp && is_file($this->temp)) {
            return file_get_contents($this->temp);
        }
    }
    
    // アップロード元のファイル名やmime-typeから拡張子を判別
    public function getSrcExtension()
    {
        return self::getExtension($this->src_name, $this->temp);
    }
    
    // 引数指定のバリエーション
    //[dir], null, null
    //[dir], [filename], null
    //[dir], [filname.ext], null
    //[dir], null, [ext]
    //[dir], null, true
    //[dir], [filename], [ext]
    //[dir], [filename], true
    //[dir], [filname.ext], [ext]
    //[dir], [filname.ext], true
    // $ext: true 拡張子をつけたいが拡張子が分からない場合など
    public function moveFromTemp($dir, $filename = null, $ext = null) {
        
        $path = self::createFilePath($dir, $filename, $ext, $this->src_name, $this->temp);
        
        if($this->temp && is_uploaded_file($this->temp)) {
            $ret = move_uploaded_file($this->temp, $path);
        }

        return $ret?$path:null;
    }
    
    private static function createFilePath($dir, $filename = null, $ext = null, $src_name, $temp)
    {   
        // ディレクトリ
        $dir = str_replace('//', '/',$dir.'/');
        
        // ファイル名
        // 引数で与えらえていない場合は、アップロード元のファイル名
        if(!$filename) {
            $filename = $src_name;
        }
        
        $info = pathinfo($filename);
        // ファイル名部に分離
        $filename = $info['filename'];
        // 引数で拡張子が与えられている場合は、引数、与えられてない場合はファイ名から
        $extension = ($ext!==null)?$ext:@$info['extension'];
        if($ext === true) {
            $extension = self::getExtension($src_name, $temp);
        }
        
        $filename = $filename.($extension?'.':'').$extension;
        
        $path = $dir.$filename;
        
        return $path;
    }
    
    // ファイル名に拡張子がある場合は、ファイル名から 無い場合はmime-typeから拡張子判定
    private static function getExtension($src_name, $temp)
    {
        $info = pathinfo($src_name);
        
        $extension = @$info['extension'];
        
        if(!$extension && $temp && is_uploaded_file($temp)) {
            $mime = mime_content_type($temp);
            $buff = explode("/", $mime);
            $extension = @$buff[1];
            
            // mimeの文字列と異なる拡張子にする
            if($extension == 'plain') { $extension = 'txt'; }
        }
        
        return $extension;
    }
}