<?php 

1 AND \file_exists($GLOBALS['FW__START']['ENV'] = \dirname($_SERVER['SCRIPT_FILENAME']).'/.local/.start-env.php') AND (function($f){ global $_; include_once $f; })($f);
if($GLOBALS['FW__START']['FW'] = ($_SERVER['FW'] ?? $_SERVER['REDIRECT_FW'] ?? $_SERVER['REDIRECT_REDIRECT_FW'] ?? '')){
    if(\is_file($GLOBALS['FW__START']['FILE'] = \dirname(__DIR__)."/fw-{$GLOBALS['FW__START']['FW']}/.start.php")){
        return include $GLOBALS['FW__START']['FILE'];
    }
}
return include '.start-fallback.php';





