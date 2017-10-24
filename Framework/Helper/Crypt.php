<?php

/**
 * 暗号化（スクランブル化）クラス
 *
 * PHP versions 4 and 5 (PHP4.3 upper)
 *
 * @author Tohru Ochiai <kyukoh@gmail.com>
 * @version $Revision: 0.2.0 $
 * @package Framework_Helper_Crypt
 * @see http://kyukoh.net/
 */

// ToDo：キーと同じ文字長しか暗号化できない
// 2016.01.05
class Framework_Helper_Crypt
{
    const TYPE_SHORT    = 'SHORT';
    const TYPE_MIDDLE   = 'MIDDLE';
    const TYPE_LONG     = 'LONG';
    
    private static function getParams($type = 'MIDDLE')
    {
        $ret = array();
        
        //使える文字を設定
        $ret['type'] = $type;

        $ret['key_set'] = array(
            self::TYPE_SHORT    => '0123456789abcdefghijklmnopqrstuvwxyz',
            self::TYPE_MIDDLE   => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            self::TYPE_LONG     => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ()*+-/.,:;!#$%&=~|^@_',
        );

        if($ret['type'] == 'SHORT')
        {
            $ret['keys'] = array(
                '1QXJ7EKDL3B569PYW4i8AFCTVSGU2oRM0NZH',
                'WTGCNYPo4ZQ02iSRLV7UE8A95XHJMDK1F6B3',
                '1ToUQ6SLB8KWJ4YZ395AEiNP07XDCRFH2MVG',
                'G3Q2FBX18ST4HEM9YoN60VUJ5DKCRiWAZP7L',
                '3DiZ0EHXPAY6WMU1QoFB9LCJNT784VSR25GK',
                'RK52FGiJH96L7YQ0VPNS81DZ4E3XTCBMWAUo',
                'RC0GEQBJPKUV87FMiZ39HWYL4562T1XDANSo',
                'NEXHZU41B5WJFT9AG7oKPiY08D6LSVCRQM23',
                'U4RSC08QXVKY6iBA32M1WHL5T7PZ9JNFDEoG',
                'N4L0GX715oP23K8CEYA9QVBMSTH6ZDiJRFWU'
            );
        }
        else if($ret['type'] == 'MIDDLE')
        {
            $ret['keys'] = array(
                'e2l3NnVYKHXcfOijgmWEtT8yABqZJ7S41upDP0zbFxM9dswGoavkrUhQ5LRC6I',
                'n57Pq9hjXVLuSFlv4HTsOQUBYJZogIbtmfM1xwAke6208GRipzracyDENW3KdC',
                'eDcGvtOMdAfCkir7HF0Y8wy31zLNgVB9jxpZT4nusWhJR65KamESUIlqQb2oXP',
                'Z3HwUslBqrKdx2VXRE6j01ecGfT9zLuM8Qkagmp7JWvNy4t5SboIhDYFOAnCPi',
                'ufjr9JHnUcbDgymV4RdXipzYeNvxKsoW5EAOP8MLZht7wB36aTFC0kQIG1ql2S',
                'ikHjxmeuv6Up2Pg4W7aISlfFXtZAMYrL8E1CwBdN3cs5JThROQz0nVyqbo9DKG',
                'ONUL1hDH089BzceAdJklx4mG5fvTgqinSCs2QbrMtaY3I7RZV6PWpjoKEFyXwu',
                '9Wj71RmQ2O6sIurcVNbE8loJiMKH3BpzdFnAxaLUh4S0CtZGkT5PegfwXvqDYy',
                'KJqu0kjZtiEWN31VPmpI8RoXOB9y2nQgdeMYraLfzwl5xsbGcCF6U4SvH7AhDT',
                '9vAKpHtgLfwhry4T5o8R2iaOnjXJFC0qSIUZkVuD1bdmsYMBPW6QlGeENxz7c3'
            );
        }
        else if($ret['type'] == 'LONG')
        {
            $ret['keys'] = array(
                '9sLQ8+X7R5e&YODWx-F6vHf;ErGp_d@KnZ|~1:mIc/.2PbT*SaAj%U3(J,tiMVu!#w4q$zloBh^y0gN)Ck=',
                '7#Dg56/toweWqSEL2FP~RnxcK)ZjfGm19&,;=kN3lbT*CsuA!4hJ8rd:OvH.%^IU(a@+$Qy|Y_zpBV0iX-M',
                'NMlhp58xvoduS;qD0t4Cn3Qw=U6AF,2K%Y-+bIi&E_XTH|J/a^OGe19)z~k$sr:fVPj7R@L#WB.g!mc*(Zy',
                'MwL0+6!mh%o_v*OBU&TW3/;JtNin@,AzEH1rQ4)2k^y7j~$:alYdVCbSu9-De58cx=Ps(#I|.FZRfgqXGpK',
                '$5#v4:ef;7r_c)^Imt26/%OdjNXQF3Bky-8LhwbEK.GCJMH1nli9@go0p*P=xuzDTZs|AVU!SaW~(q&YR+,',
                'gJHK-7mla)nVbehowDSM1NuBd:$|E.c63q(4X;PI#2k^xR@Y+_QpAvTjF50&i%8Ws,~/UrCz*GtO=f9LZ!y',
                's)YiUewu+.kv5(yT#1d8PhzgX2F%o_*E~WOQDN=p@a^cf:VrJ,R7Ab-Z3t4lHCx/S;M|0jBm&Gq6KL!9$In',
                ';O+|1asL2-hwZUW5HxzGk~fX3$(Yj_^nc!qr,TbQomBESdD.Ke78g@=J4it)*AC9V#&pM:PFyI%RNu/l06v',
                'gW_$sIyPGxY5/+,S=EU@0iVO-CKX4cRBlevjNb3h;H8D#:wn7m.J*&oaLMfZ2q1F6!^kdzt(%u|T~A)pQr9',
                ':uLq~=$@BzEjC4f3Jt#8.Y^2,*wyx_M-1skpK&IOXPogvVnR)U|T/cNZQ!lHh5em;(0GSAr9i76%dDbW+aF'
            );
        }
        return $ret;
    }

    public static function encode($str, $type = 'MIDDLE')
    {
        $params = self::getParams($type);
        
        $ret = $str;

        $num = 0;
        if(strlen($str)>0)
        {
            for($i=0;$i<strlen($str);$i++)
            {
                $s = substr($str, $i, 1);
                //echo $s."->".ord($s)."<br>";
                $num+= ord($s);
            }
            //最終暗号化に使うキーID
            $key_id = $num%sizeof($params['keys']);
            //echo "[".$key_id."]<hr>";

            //最終暗号化に使うキーIDを暗号化する
            //※この際のキーは0番必須
            $crypt_key_id = self::main($params, $key_id, $params['keys'][0], 0);
            //echo "key_id:".$crypt_key_id."<br>";

            $ret = $crypt_key_id.self::main($params, $str, $params['keys'][$key_id], 0);
        }
        return $ret;
    }

    public static function decode($str, $type = 'MIDDLE')
    {
        $params = self::getParams($type);
        $ret = $str;

        if(strlen($str)>=2)
        {
            //キーID取得
            $key_id = self::main( $params, substr($str, 0, 1), $params['keys'][0], 1);
            //複合化
            if(@$params['keys'][$key_id]) {
                $ret = self::main( $params, substr($str, 1, strlen($str)-1), $params['keys'][$key_id], 1);
            }
        }

        return $ret;
    }

    public static function main($params, $str, $key2, $flag)
    {
        $key1 = $params['key_set'][$params['type']];
        $ret = "";

        if($flag != 0) {
            $work = $key1;
            $key1 = $key2;
            $key2 = $work;
        }

        //echo($key1."<br>".strlen($key1)."<hr>".$key2."<br>".strlen($key2)."<hr>");

        for($i=0;$i<strlen($str);$i++)
        {
            if($flag != 0) {
                $_key2 = $key2;
                $c = $i%strlen($key1);
                $_key1 = substr($key1, $c).substr($key1, 0, $c);
            } else {
                $_key1 = $key1;
                $c = $i%strlen($key2);
                $_key2 = substr($key2, $c).substr($key2, 0, $c);
            }
            //print $_key1."<br>".$_key2."<br><br>";
            $s = substr($str, $c, 1);
            $j = strpos($_key1, $s);

            if($j === FALSE)
            {
                $ret = $ret.$s;
            }
            else {
                $ret = $ret.substr($_key2, $j,1);
            }
        }
        return $ret;
    }

    //キーを新しくする場合のみ使用します。
    //キーを作成したらgetParamsメソッド['keys']に(コピペ)
    public static function init()
    {
        $params = self::getParams();
        $types = $params['key_set'];
        $keys_max = 10;
        
        header("Content-type: text/plain");
        foreach( $types as $type => $val )
        {
            echo "// ${type}" . PHP_EOL;
            for( $count=0; $count<$keys_max; $count++ )
            {
                $params = self::getParams($type);
                $key = $params['key_set'][$type];

                //▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼▼
                // キー作成：1度生成したらそれを使ってください。
                //------------------------------------------------------------------------
                srand((double)microtime()*1000000);
                $length = strlen($key);
                //初期化
                for($i=0;$i<$length;$i++)
                {
                    $swap[$i] = -1;
                }
                $i = 0;
                while(true)
                {
                    $num=round(rand(0,$length-1));
                    $flag = true;
                    for($j=0;$j<$length;$j++)
                    {
                            if($swap[$j] == $num) $flag = false;
                    }
                    if($flag)
                    {
                            $swap[$i] = $num;
                            //print $i."：".substr($key, $swap[$i], 1)."<br>";
                            $i++;
                    }
                    $flag = true;
                    for($j=0;$j<$length;$j++)
                    {
                            if($swap[$j] == -1) $flag = false;
                    }
                    if($flag) break;
                }
                $use_key = "";
                for($j=0;$j<$length;$j++)
                {
                    $use_key = $use_key.substr($key, $swap[$j], 1);
                }

                echo "'${use_key}',".PHP_EOL;
            }
        }
        //------------------------------------------------------------------------
        // キー作成
        //▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲▲
    }
}
?>
