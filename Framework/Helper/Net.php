<?php
class Framework_Helper_Net
{
    const CRLF_CODE = "\r\n";
    /*
     * http://から始まるURL( http://user:pass@host:port/path?query )
    $param: $url or array();
    array(

        'output'  => 'ファイル名',
        'url'     => 'URL',
        'method'  => 'GET' or 'POST',
        'params'  => POSTデータ,
        'headers' =>

    )

    //http関数内部でのファイル書き出しもできますが、
    //ヘッダまで含めて書き出してしまうので、保留！
    //ヘッダを飛ばして（$fpのカーソルを進めて）書き込む方法を考えてください。by Tohru Ochiai
    $method  : GET, POST, HEADのいずれか(デフォルトはGET)
    $headers : 任意の追加ヘッダ
    $post    : POSTの時に送信するデータを格納した配列("変数名"=>"値")
    */
    public static function http_getpost($options)
    {
        if(!is_array($options))
        {
            $options = array(
                'url'    => $options,
            );
        }

        $url         = App::choose($options, 'url');
        $filename    = App::choose($options, 'filename', uniqid("SPL_"));
        $output      = App::choose($options, 'output', false);
        $method      = App::choose($options, 'method', 'GET');
        $post_params = App::choose($options, 'params', array());
        $headers     = App::choose($options, 'headers', '');

        if($filename) {
            $path = dirname(__FILE__).'/../../cache/';//暫定での書き込み先
            $filename = $path.$filename;
        }

        /* URLを分解 */
        $URL = parse_url($url);

        /* クエリー */
        if (isset($URL['query'])) {
            $URL['query'] = "?".$URL['query'];
        } else {
            $URL['query'] = "";
        }

        /* デフォルトのポートは80 */
        if (!isset($URL['port'])) {
            $scheme = App::choose($URL, 'scheme');
            if($scheme == 'https') {
                $URL['port'] = 443;
            } else {
                $URL['port'] = 80;
            }
        }

        /* リクエストライン */
        $request  = $method." ".App::choose($URL, 'path','/').$URL['query']." HTTP/1.0\r\n";

        /* リクエストヘッダ */
        $request .= "Host: ".$URL['host']."\r\n";
        $request .= "User-Agent: PHP/".phpversion()."\r\n";

        /* Basic認証用のヘッダ */
        if (isset($URL['user']) && isset($URL['pass'])) {
            $request .= "Authorization: Basic ".base64_encode($URL['user'].":".$URL['pass'])."\r\n";
        }

        /* 追加ヘッダ */
        $request .= $headers;

        /* POSTの時はヘッダを追加して末尾にURLエンコードしたデータを添付 */
        if (strtoupper($method) == "POST") {
            while (list($name, $value) = each($post_params)) {
                $POST[] = $name."=".urlencode($value);
            }
            $postdata = implode("&", $POST);
            $request .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $request .= "Content-Length: ".strlen($postdata)."\r\n";
            $request .= "\r\n";
            $request .= $postdata;
        } else {
            $request .= "\r\n";
        }

        /* WEBサーバへ接続 */
        $fp = fsockopen($URL['host'], $URL['port']);

        /* 接続に失敗した時の処理 */
        if (!$fp)
        {
            $flag = false;
            die("ERROR\n");
        }
        else
        {
            $flag = true;
        }


        /* 要求データ送信 */
        fputs($fp, $request);

        /* 応答データ受信 */
        $response = "";

        // ファイル作成する
        if($output)
        {
            // 書き出し用のファイルオープン
            $fw = fopen($filename, "w");
            flock($fw, LOCK_EX);

            while (!feof($fp)) {
                $res = fgets($fp, 4096);

                // ファイル書き出し
                fputs($fw, $res);
            }

            // 書き出し用のファイルクローズ
            flock($fw, LOCK_UN);
            fclose($fw);
            $flag = true;
        }
        // ファイルを作成しない
        else
        {
            while (!feof($fp))
            {
                $response .= fgets($fp, 4096);
            }
        }

        /* 接続を終了 */
        fclose($fp);

        if($flag)
        {
            $result = array();

            if(!$output)
            {
                /* ヘッダ部分とボディ部分を分離 */
                $DATA = explode("\r\n\r\n", $response, 2);

                /* リクエストヘッダをコメントアウトして出力 */
                //echo "<!--\n".$request."\n-->\n";

                /* レスポンスヘッダをコメントアウトして出力 */
                //echo "<!--\n".$DATA[0]."\n-->\n";

                /* メッセージボディを出力 */
                //echo $DATA[0];

                //Content-Length
                if(preg_match("/Content-Length:[ \t\r\f]*([0-9]+)/", $DATA[0], $ret))
                {
                    //echo "Content-Length:".$ret[1]."<br>";
                    $result['length'] = intval( $ret[1] );
                }
                //Content-Type
                if(preg_match("/Content-Type:[ \t\r\f]*([^ \t]+)/", $DATA[0], $ret))
                {
                    //echo "Content-Type:".$ret[1]."<br>";
                    $result['type'] = $ret[1];
                }

                $result['url'] = $url;

                $result['filename'] = $filename;//tempnam("./", "SPL");

                //MIMEタイプでファイルの拡張子を作成
                $type = App::choose($result, 'type', '');
                if(preg_match("/jpeg/",$type))
                {
                    $result['filename'].= ".jpg";
                }
                else if(preg_match("/png/",$type))
                {
                    $result['filename'].= ".png";
                }
                else if(preg_match("/gif/",$type))
                {
                    $result['filename'].= ".gif";
                }
                else if(preg_match("/x-shockwave-flash/",$type))
                {
                    $result['filename'].= ".swf";
                }

                $result['data'] = $DATA[1];

                return $result;
                //ファイル実体を返すときは、フラグでなく実体が戻り値となる
            }
            else
            {
                return true;
            }
        }
        else
        {
            return false;
        }
    }
	
    /**
     * マルチパートボディを組み立てて文字列としてセットする。
     * 
     * @param resource $ch cURLリソース
     * @param array $assoc 「送信する名前 => 送信する値」の形の連想配列
     * @param array $files 「送信する名前 => ファイルパス」の形の連想配列
     * @return bool 成功 or 失敗
     */
    public static function httpPost($url, array $assoc = array(), array $files = array(), array $datas = array())
    {
        $ch = curl_init($url);
        static $disallow = array("\0", "\"", "\r", "\n");
        $body = array();
        foreach ($assoc as $k => $v) {
            $k = str_replace($disallow, "_", $k);
            $body[] = implode("\r\n", array(
                "Content-Disposition: form-data; name=\"{$k}\"",
                "",
                filter_var($v), 
            ));
        }
        foreach ($files as $k => $v) {
            //if(false === $v = realpath(filter_var($v)))continue;
            if(!is_file($v))continue;
            if(!is_readable($v))continue;
            $data = file_get_contents($v);
            
            $v = call_user_func("end", explode(DIRECTORY_SEPARATOR, $v));
            list($k, $v) = str_replace($disallow, "_", array($k, $v));
            $body[] = implode("\r\n", array(
                "Content-Disposition: form-data; name=\"{$k}\"; filename=\"{$v}\"",
                "Content-Type: application/octet-stream",
                "",
                $data,
            ));
        }
        foreach ($datas as $k => $data) {
            $body[] = implode("\r\n", array(
                "Content-Disposition: form-data; name=\"{$k}\"; filename=\"{$k}\"",
                "Content-Type: application/octet-stream",
                "",
                $data,
            ));
        }
        do {
            $boundary = "---------------------" . md5(mt_rand() . microtime());
        } while (preg_grep("/{$boundary}/", $body));
        array_walk($body, function (&$part) use ($boundary) {
            $part = "--{$boundary}\r\n{$part}";
        });
        $body[] = "--{$boundary}--";
        $body[] = "";
        curl_setopt_array($ch, array(
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => implode("\r\n", $body),
            CURLOPT_HTTPHEADER => array(
                "Expect: 100-continue",
                "Content-Type: multipart/form-data; boundary={$boundary}",
            ),
        ));
        curl_exec($ch);
    }
}