<?php
// Cache_Lite
require_once(dirname(__FILE__).'/../Library/Cache/Lite.php');
// ORMマッパー
require_once(dirname(__FILE__).'/../Library/Idiorm/idiorm.php');
// ActiveRecord
require_once(dirname(__FILE__).'/../Library/Idiorm/paris.php');
// Opauth
require_once(dirname(__FILE__).'/../Library/Opauth/Opauth.php');

$path = dirname(__FILE__).'/../Library/Pear/';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

// YML
require_once(dirname(__FILE__).'/../Library/Spyc.class.php');