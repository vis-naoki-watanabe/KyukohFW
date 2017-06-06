<?php
class App_Abstruct extends Framework_Model_Abstruct
{
    const STRING_SIZE_TEXTAREA = 65535; 
    const STRING_SIZE_TEXT     = 512;
    const TIME_ZERO = '0000-00-00 00:00:00';

    // レコード作成時のスキーマ定義
    protected static $create_schemas = array(
        //'params'        => '',
        'create_time'   => 'APP_NOW()',
        'update_time'   => 'APP_NOW()',
        'delete_time'   => self::TIME_ZERO,
    );

    // レコード更新時のスキーマ定義
    protected static $update_schemas = array(
        //'params'        => '',
        'update_time'   => 'APP_NOW()',
    );

    // リスト取得の際、判定するdeleteフラグ
    protected static $delete_flags = array(
        'delete_time' => self::TIME_ZERO
    );
}
