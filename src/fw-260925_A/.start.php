<?php //250911
########################################################################################################################
#region LICENSE
    /* 
                                               EPX-PAX-ATTO
    PROVIDER : KLUDE PTY LTD
    PACKAGE  : EPX-PAX
    AUTHOR   : BRIAN PINTO
    RELEASED : 2026-09-25
    
    Copyright (c) 2017-2026 Klude Pty Ltd. https://klude.com.au

    The MIT License

    Permission is hereby granted, free of charge, to any person obtaining
    a copy of this software and associated documentation files (the
    "Software"), to deal in the Software without restriction, including
    without limitation the rights to use, copy, modify, merge, publish,
    distribute, sublicense, and/or sell copies of the Software, and to
    permit persons to whom the Software is furnished to do so, subject to
    the following conditions:

    The above copyright notice and this permission notice shall be
    included in all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
    EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
    MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
    NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
    LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
    OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
    WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
    
    */
#endregion
# ######################################################################################################################
# i'd like to be a tree - pilu (._.) // please keep this line in all versions - BP
# ######################################################################################################################
namespace _ {
    #region START
    \defined('FW__MSTART') OR \define('FW__MSTART', \microtime(true));
    const JDUMP = JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES;    
    const REGEX_CLASS_FQN = '/^(([a-zA-Z_\\x80-\\xff][\\\\a-zA-Z0-9_\\x80-\\xff]*)\\\\)?([a-zA-Z_\\x80-\\xff][a-zA-Z0-9_\\x80-\\xff]*)$/';
    const REGEX_CLASS_QN = '/^[a-zA-Z_\\x80-\\xff][a-zA-Z0-9_\\x80-\\xff]*$/';
    \define('NL', empty($_SERVER['HTTP_HOST']) ? PHP_EOL : '<br>');
    \define('FW__PHP_TSP_DEFAULTS', $_SERVER['FW__PHP_TSP_DEFAULTS'] ?? [
        "handler" => "spl_autoload",
        "extensions" => \spl_autoload_extensions(),
        "path" =>  \get_include_path(),
    ]);
    \defined('FW__OB_OUT') OR \define('FW__OB_OUT', \ob_get_level());
    ($_SERVER['FW__OB_FRONT_EN'] ?? true) AND \ob_start();
    \defined('FW__OB_TOP') OR \define('FW__OB_TOP', \ob_get_level());
    \define('FW__DX', (function(){
        $raw_dx = $_SERVER['FW__DX'] ?? $_SERVER['REDIRECT_FW__DX'] ?? $_SERVER['REDIRECT_REDIRECT_FW__DX'] ?? 'F';
        $clean_dx = preg_replace('/[^0-9a-fA-F]/', '', $raw_dx) ?: 'F';
        return (int) hexdec($clean_dx);
    })());
    \define('FW__DBG', (int) ($_SERVER['FW__DBG'] ?? $_SERVER['REDIRECT_FW__DBG'] ?? $_SERVER['REDIRECT_REDIRECT_FW__DBG'] ?? 0));
    \define('FW__DBG_IS',[
        0 => FW__DBG>=0,
        1 => FW__DBG>=1,
        2 => FW__DBG>=2,
        3 => FW__DBG>=3,
        4 => FW__DBG>=4,
        5 => FW__DBG>=5,
        6 => FW__DBG>=6,
        7 => FW__DBG>=7,
        8 => FW__DBG>=8,
        9 => FW__DBG>=9,
    ]);
    \defined('FW__DX_STATS') OR \define('FW__DX_STATS', (FW__DBG_IS[9]) 
        ? [
            'MUSAGE' => memory_get_usage(),
            'RUSAGE' => getrusage(),
        ] 
        : []
    );
    \define('FW__REQ_RURP', (function(){
        if((\str_starts_with($p = \strtok($_SERVER['REQUEST_URI'],'?'), $n = $_SERVER['SCRIPT_NAME']))){
            $rurp = \substr($p,\strlen($n));
        } else if((($d = \dirname($n = $_SERVER['SCRIPT_NAME'])) == DIRECTORY_SEPARATOR)){
            $rurp =  $p;
        } else {
            $rurp = \substr($p, \strlen($d));
        }
        \define('FW__REQ_IS_SETUP', \str_starts_with($rurp,'/@'));
        return (FW__REQ_IS_SETUP) ? \substr($rurp,2) : $rurp;
    })());
    \define('FW__EPX_DIR', \str_replace('\\','/', __DIR__));
    \define('FW__SITE_DIR', \str_replace('\\','/', \dirname($_SERVER['SCRIPT_FILENAME'])));
    \define('FW__SITE_LOCAL_DIR', FW__SITE_DIR.'/.local');
}
# ##############################################################################################################################################################
namespace { (function(){
    #region DX
    1 AND \ini_set('display_errors', 0);
    1 AND \ini_set('display_startup_errors', 1);
    1 AND \ini_set('error_reporting', E_ALL);
    0 AND \error_reporting(E_ALL);
        
    $report__fn = function($ex, $title, &$uid, &$timestamp){
        $uid = uniqid();
        $f = FW__SITE_LOCAL_DIR.'/debug/'.\date('Y-m/Y-md').'-faults.html';
        \is_dir($d = \dirname($f)) OR \mkdir($d, 0777, true);
        $timestamp = \date('Y-md-Hi-s');
        $content = <<<HTML
        <pre class="log-card" style="overflow:auto; color:red;border:1px solid red;padding:5px;"><b style="font-size:1.2em">{$title}: {$ex->getMessage()}</b>
        <b>{$ex}</b>
        <i style="margin-top:10px;color:blue;">Fault: {$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}</i>
        <i style="margin-top:10px;color:blue;">Request: {$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}</i>
        <i style="margin-top:10px;color:blue;">Ref: {$uid} - {$timestamp}</i>
        </pre>
        HTML;
        \file_put_contents($f, $content, FILE_APPEND | LOCK_EX);
        \file_put_contents(\dirname($f).'/last-fault.html', $content, FILE_APPEND | LOCK_EX);
    };
    #region DX FAULT HANDLER
    $fault__fn = function($ex, $title = 'Unhandled Exception') use($report__fn){
        global $_;
        $GLOBALS['FW__SIG_ABORT'] = -1;
        $report__fn($ex, $title, $uid, $timestamp);
        while(\ob_get_level() > 0){ @\ob_end_clean(); }
        if(\str_contains(($_SERVER['HTTP_ACCEPT'] ?? ''),'text/html')){
            if(\is_numeric($n = \strtok($ex->getMessage(),':'))){
                $http_code = $n;
            } else {
                $http_code = 500;
            }
            \http_response_code($http_code);
            if(FW__DBG_IS[9]){
                print(<<<HTML
                <pre style="overflow:auto; color:red;border:1px solid red;padding:5px;">500: {$title}: <b>{$ex->getMessage()}</b>
                <b>{$ex}</b>
                <i style="margin-top:10px;color:blue;">Request: {$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}</i>
                <i style="margin-top:10px;color:blue;">Ref: {$uid} - {$timestamp}</i>
                </pre>
                HTML);
            } else {
                print(<<<HTML
                <pre style="overflow:auto; color:red;border:1px solid red;padding:5px;">500: {$title}
                <i style="margin-top:10px;color:blue;">Request: {$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}</i>
                <i style="margin-top:10px;color:blue;">Ref: {$uid} - {$timestamp}</i>
                </pre>
                HTML);
            }
            exit($http_code);
        } else {
            \http_response_code(500);
            \header('Content-Type: application/json');
            echo \json_encode([
                'status' => "error",
                'uid' => $uid,
                'message' => $ex->getMessage(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); 
            exit(500);
        }
    };
    #region DX EXCEPTION
    1 AND \set_exception_handler(function($ex) use($fault__fn){
        if(\class_exists(\_\dx::class, false) && \method_exists(\_\dx::class,'on_exception')){
            return \_\dx::on_exception($ex, $title);
        }
        $fault__fn($ex, 'Unhandled Exception');
    });
    #region DX ERROR
    1 AND \set_error_handler(function($severity, $message, $file, $line) use($fault__fn){
        if(\class_exists(\_\dx::class, false) && \method_exists(\_\dx::class,'on_error')){
            return \_\dx::on_error($severity, $message, $file, $line);
        }
        try{
            throw new \ErrorException(
                $message, 
                0,
                $severity, 
                $file, 
                $line
            );
        } catch(\Throwable $ex) { 
            if(!\defined('FW__SIG_END')){
                $fault__fn($ex, 'Unhandled Error');
            } else {
                throw $ex;
            }
        }
    });
    #region DX SHUTDOWN
    1 AND \register_shutdown_function(function() use($fault__fn){
        if(\defined('FW__SIG_END')){
            echo FW__DBG_IS[9] 
                ? "500: Root finalizer violation" 
                : "500: Internal Server Error"
            ;
            exit(1);
        } else {
            \define('FW__SIG_END', \microtime(true));
        }
        if(0 === ($GLOBALS['FW__SIG_ABORT'] ?? null)){ 
            //no nonsense exit!
            exit(); 
        }
        if(\class_exists(\_\dx::class, false) && \method_exists(\_\dx::class,'on_shutdown')){
            return \_\dx::on_shutdown();
        }
        if(FW__DBG_IS[9]){
            $f = FW__SITE_LOCAL_DIR.'/debug/shutdown.txt';
            \is_dir($d = \dirname($f)) OR \mkdir($d, 0777, true);
            \file_put_contents($f, \json_encode([
                'span' => \number_format(((\microtime(true) - FW__MSTART)), 6).'s',
                'trace' => $GLOBALS['_TRACE'] ?? [],
                'env' => $_ENV,
                'constants' => \get_defined_constants(true)['user'],
            ], \_\JDUMP));
        }
        if($error = \error_get_last()){ 
            \error_clear_last();
            try {
                throw new \ErrorException(
                    $error['message'], 
                    0,
                    $error["type"], 
                    $error["file"], 
                    $error["line"]
                );
            } catch(\Throwable $ex) {
                $fault__fn($ex, 'Unhandled Error in exit process');
            }
        }
    });
})(); }
# ##############################################################################################################################################################
namespace _ { (function(){    
    #region CONFIG
    \define('FW__DATA_DIR', FW__SITE_LOCAL_DIR.'/db');
    \file_exists($f = FW__DATA_DIR.'/.config.php') AND (function($f){ global $_; include $f; })($f);
    \define('FW__APP_DIR', (function(){
        if(FW__REQ_IS_SETUP){
            return FW__EPX_DIR.'/setup';
        } else if(!($app = $_SERVER['FW__APP'] ?? $_SERVER['REDIRECT_FW__APP'] ?? $_SERVER['REDIRECT_REDIRECT_FW__APP'] ?? 'app')){
            throw new \Exception("APP configuration is blank"); // meaning APP is set but is empty
        } else if(
            !\is_dir($dir = \strtr(FW__SITE_DIR."/@-epx/{$app}",'\\','/'))
            && !\is_dir($dir = \strtr(FW__EPX_DIR."/{$app}",'\\','/'))
        ){
            throw new \Exception("Unable to locate APP directory: {$app}");
        }
        return $dir;
    })());
    \file_exists($f = FW__APP_DIR.'/.config.php') AND (function($f){ global $_; include $f; })($f);
    \define('FW__ABACA_DIR', (function(){
        global $_;
        if(FW__REQ_IS_SETUP){
            return FW__EPX_DIR.'/abaca';
        } else if(!($abaca = $_['ABACA'] ?? 'abaca')){
            throw new \Exception("ABACA configuration is blank"); // meaning ABACA is set but is empty
        } else if(!\is_dir($dir = \strtr(FW__EPX_DIR."/{$abaca}",'\\','/'))){
            throw new \Exception("Unable to locate ABACA directory: {$abaca}");
        }
        return $dir;
    })());
    \set_time_limit((int) ($_SERVER['FW__TIMELIMIT'] ?? $_SERVER['REDIRECT_FW__TIMELIMIT'] ?? $_SERVER['REDIRECT_REDIRECT_FW__TIMELIMIT'] ?? 5));
    \date_default_timezone_set(
        $_SERVER['FW__TIMEZONE'] ?? $_SERVER['REDIRECT_FW__TIMEZONE'] ?? $_SERVER['REDIRECT_REDIRECT_FW__TIMEZONE'] ?? null 
        ?: \getenv('FW__TIMEZONE') 
        ?: 'Australia/Adelaide'
    );
    \set_include_path(\strtr(FW__APP_DIR.PATH_SEPARATOR.FW__ABACA_DIR.PATH_SEPARATOR.FW__PHP_TSP_DEFAULTS['path'],'\\','/'));
    \spl_autoload_extensions("-#.php,/-#.php");
    \spl_autoload_register();
})(); }
# ##############################################################################################################################################################
namespace _ { (function(){
    #region DISPATCHER
    global $_;
    \is_array($_) OR $_ = [];
    \is_array($GLOBALS['_TRACE'] ?? null) OR $GLOBALS['_TRACE'] = [];
    $status__fn = function(bool $ok = true, string|null $message = null){
        static $status = [];
        if(\func_num_args()){
            $status = [$ok, $message];
            $GLOBALS['_TRACE'][] = $message;
        } else {
            return $status ?? [true, 'No Messages'];
        }
    };
    $dispatch__fn = function($a, $b = null) use($status__fn){
        try {
            if(\is_int($a)){
                $__FN = $this->FN;
                return function() use($a, $b, $status__fn) {
                    while(\ob_get_level() > 0){ @\ob_end_clean(); } 
                    \http_response_code(404);
                    echo $b ?: "Error {$a}";
                    if($this->DBG >= 9){ 
                        echo NL.($status__fn)()[1]; 
                    } 
                    exit($a);
                };
            } else if(\is_callable($a)) {
                return $a;
            } else {
                throw new \Exception("Invalid dispatcher");
            }
        } finally {
            $this->ROUTE->SPAN = \number_format(((\microtime(true) - FW__MSTART)), 6).'s';
            if($this->DBG >= 9){
                $f = FW__SITE_LOCAL_DIR.'/debug/route.txt';
                \is_dir($d = \dirname($f)) OR \mkdir($d, 0777, true);
                \file_put_contents($f, \json_encode([
                    'status' => $status__fn(),
                    'trace' => $GLOBALS['_TRACE'] ?? [],
                    'fw' => (array) $this,
                    'constants' => \get_defined_constants(true)['user'],
                ], \_\JDUMP));
            }
        }
    };
    
    #region REQ
    $this->MSTART = new \DateTime(\date('Y-m-d H:i:s.'.\sprintf("%06d",(\FW__MSTART-floor(\FW__MSTART))*1000000), (int)\FW__MSTART));
    $this->DBG = FW__DBG;    
    $this->ROUTE = (object) [ 'SPAN' => 0, 'RESOLVE' => []];
    $this->REQ = (object) ['KEY' => \sha1($_SERVER['SCRIPT_FILENAME'])];
    $this->START_FILE = \str_replace('\\','/', __FILE__);
    $this->SITE_DIR = FW__SITE_DIR;
    $this->KEY = \md5($_SERVER['SCRIPT_FILENAME']);
    $this->ROOT_DIR = \strtr($_SERVER['DOCUMENT_ROOT'],'\\','/');
    $this->ROOT_URL = 
        ($_SERVER["REQUEST_SCHEME"] ?? ((\strtolower(($_SERVER['HTTPS'] ?? 'off') ?: 'off') === 'off') ? 'http' : 'https'))
        .'://'
        .$_SERVER["HTTP_HOST"]
    ;
    $this->REQ->RURP = FW__REQ_RURP;
    $this->REQ->PHP_SAPI = \php_sapi_name();
    $this->REQ->MODE = 'http';
    $this->REQ->URL = (($_SERVER["REQUEST_SCHEME"] 
        ?? ((\strtolower(($_SERVER['HTTPS'] ?? 'off') ?: 'off') === 'off') ? 'http' : 'https'))
    ).'://'.($_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']);      
    $this->REQ->URL_PARTS = \parse_url($this->REQ->URL);
    $this->REQ->METHOD = $method = $_SERVER['REQUEST_METHOD'] ?? '';
    $this->REQ->IS_GET = $is_get = !\in_array($method, ['POST','PUT','PATCH','DELETE']);
    $this->REQ->ACTION = $action = $_REQUEST['--action'] ?? null;
    $this->REQ->IS_ACTION = $is_action = ($action) ? true : false;
    $this->REQ->IS_VIEW = !$is_action;
    $this->REQ->REFERER = ($j = $_SERVER['HTTP_REFERER'] ?? null) ? \parse_url($j) : [];
    $this->REQ->IS_TOP = (($dest = $_SERVER['HTTP_SEC_FETCH_DEST'] ?? ($j ? 'document' : null)) === 'document');
    $this->REQ->IS_FRAME = ($dest == 'iframe');
    $this->REQ->IS_MINE = !$j || \str_starts_with($j, $this->REQ->URL);
    $this->REQ->IS_HTML = (\str_contains(($_SERVER['HTTP_ACCEPT'] ?? ''),'text/html'));
    $this->REQ->IS_XHR = ('xmlhttprequest' == \strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '' ));
    if((\str_starts_with($p = \strtok($_SERVER['REQUEST_URI'],'?'), $n = $_SERVER['SCRIPT_NAME']))){
        $this->SURP = \substr($p, 0, \strlen($_SERVER['SCRIPT_NAME']));
    } else if((($d = \dirname($n = $_SERVER['SCRIPT_NAME'])) == DIRECTORY_SEPARATOR)){
        $this->SURP = '';
    } else {
        $this->SURP = \substr($p, 0, \strlen($d));
    }
    $this->ROUTE->NPATH = \trim($this->REQ->RURP,'/');
    $this->SITE_URL = $this->BASE_URL = \rtrim($this->ROOT_URL.$this->SURP,'/');
    $this->CTLR_URL = \rtrim($this->BASE_URL."/".$this->ROUTE->NPATH,'/');
    
    #region SESSION
    if(\session_status() == PHP_SESSION_NONE) {
        \session_name($this->REQ->KEY); 
        \session_start();
    }
    \define('FW__CSRF', $this->CSRF =($_SESSION['--CSRF'] ?? null ?: ($_SESSION['--CSRF'] = \md5(uniqid('csrf-')))));
    if(
        \in_array($_SERVER['REQUEST_METHOD'], ['POST','PUT','PATCH','DELETE'])
        && ($a = $_REQUEST['--csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null) != ($_SESSION['--CSRF'] ?? null)
    ){
        return $dispatch__fn(406, "403: Not Acceptable");
    }
    \define('FW__FLASH', $this->FLASH = $_SESSION['--FLASH'] ?? []);
    $_SESSION['--FLASH'] = [];

    \is_array($_SESSION['--USER'] ?? null) OR $_SESSION['--USER'] = [];
    $this->USER = $_SESSION['--USER'];
    
    #region AUTH
    if(
        ($_SERVER['FW__SESSION_EN'] ?? true)
        && ($file = \stream_resolve_include_path('_/i/env/auth/authenticate-x.php'))
        && \is_callable($fn = (fn($f) => include $f)($file))
    ){
        $status__fn(true, "Auth Prempt");
        return $dispatch__fn($fn);
    }
    
    #region ROUTE
    if($file = (empty($this->REQ->NPATH) 
        ?(
            \stream_resolve_include_path($this->ROUTE->RESOLVE[] = "__/-@.php")
            ?: \stream_resolve_include_path($this->ROUTE->RESOLVE[] = "__/-@.html")
        )
        :(
            \stream_resolve_include_path($this->ROUTE->RESOLVE[] = "__/{$this->ROUTE->NPATH}/-@.php")
            ?: \stream_resolve_include_path($this->ROUTE->RESOLVE[] = "__/{$this->ROUTE->NPATH}-@.php")
            ?: \stream_resolve_include_path($this->ROUTE->RESOLVE[] = "__/{$this->ROUTE->NPATH}/-@.html")
            ?: \stream_resolve_include_path($this->ROUTE->RESOLVE[] = "__/{$this->ROUTE->NPATH}-@.html")
        )
    )){
        $status__fn(true, "Controller File found");
        $this->ROUTE->FILE = $file;
        $this->ROUTE->INITS = (function(...$path_list){
            $inits = [];
            if($f = \stream_resolve_include_path("vendor/autoload.php")){
                $inits[] = $f;
            }
            foreach($tsp = \explode(PATH_SEPARATOR,\get_include_path()) as $d){
                if(\is_file($f = "{$d}/.module.php")){
                    $inits[] = $f;
                }
                if(\is_file($f = "{$d}/.functions.php")){
                    $inits[] = $f;
                }
            }
            foreach($path_list as $path){
                $list = [];

                if(\is_string($path)){
                    $g = (($path)
                        ? "{".\array_reduce(\explode('/', $path), function($c, $i){
                                static $u;
                                if($i){
                                    $u .= '/'.$i;
                                    $c .= ','.$u;
                                }
                                return $c;
                            })."}"
                        : ''
                    )
                    .'/.ini.php';
                
                    foreach($tsp as $d){
                        foreach(\glob("{$d}{$g}", GLOB_BRACE) as $f){ 
                            $list[$f] = \substr($f, \strlen($d));
                        }
                    }
                    
                    \asort($list);
                }
                
                foreach($list as $f => $v){
                    $GLOBALS['_TRACE']['Router: Found Init'] = (string) $f;
                    $inits[] = $f;
                }
            }
            
            return $inits;
        })($p);
        return $dispatch__fn(function(){ 
            foreach($this->ROUTE->INITS ?? [] as $f){ 
                (function($f){include_once $f;})($f);
            }
            if(\is_callable($o = include $this->ROUTE->FILE ?? null)){
                $return = $o();
            } else {
                $return = $o;
            }
            if(\is_array($return) || (\is_object($return) && $return instanceof \JsonSerializable)){
                \header('Content-Type: application/json');
                echo \json_encode($return, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                exit(0);
            } else if(\is_string($return)) {
                if(\str_starts_with($return, "Error:")){
                    echo $return;
                    exit(1);
                } else {
                    echo $return;
                    exit(0);
                }
            }
        });
    } else {
        $status__fn(false, "Controller File not found");
        return $dispatch__fn(404, "404: Not Found : __/{$this->ROUTE->NPATH}");
    }
    
})->bindTo($GLOBALS['FW'] = \class_exists(fw::class) ? \fw::_() : (object)[])()(); }
# ##############################################################################################################################################################