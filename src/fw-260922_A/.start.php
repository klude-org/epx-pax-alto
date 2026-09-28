<?php //250911
########################################################################################################################
#region 
    /* 
                                               EPX-PAX-ALPHA
    PROVIDER : KLUDE PTY LTD
    PACKAGE  : EPX-PAX
    AUTHOR   : BRIAN PINTO
    RELEASED : 2026-09-11
    
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
#region Start
namespace _ {

    //* Mark Start
    \defined('FW__MSTART') OR \define('FW__MSTART', \microtime(true));
    const JDUMP = JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES;    
    const REGEX_CLASS_FQN = '/^(([a-zA-Z_\\x80-\\xff][\\\\a-zA-Z0-9_\\x80-\\xff]*)\\\\)?([a-zA-Z_\\x80-\\xff][a-zA-Z0-9_\\x80-\\xff]*)$/';
    const REGEX_CLASS_QN = '/^[a-zA-Z_\\x80-\\xff][a-zA-Z0-9_\\x80-\\xff]*$/';
    \define('NL', empty($_SERVER['HTTP_HOST']) ? PHP_EOL : '<br>');

}
namespace { (function(){ 
    
    global $_;
    \is_array($_) OR $_ = [];
    \is_array($GLOBALS['_TRACE'] ?? null) OR $GLOBALS['_TRACE'] = [];
    
    \define('FW__PHP_TSP_DEFAULTS', $_SERVER['FW__PHP_TSP_DEFAULTS'] ?? [
        "handler" => "spl_autoload",
        "extensions" => \spl_autoload_extensions(),
        "path" =>  \get_include_path(),
    ]);
    \define('FW__INTFC', $_SERVER['FW__INTFC']
        ?? (empty($_SERVER['HTTP_HOST']) 
            ? 'cli'
            : $_SERVER['HTTP_X_REQUEST_INTERFACE'] ?? 'web'
        )
    );    
    \define('FW__IS_CLI', empty($_SERVER['HTTP_HOST']));
    \define('FW__SITE_DIR', \str_replace('\\','/', \FW__IS_CLI
        ? \realpath(\dirname($_SERVER['FW__SITE_INDEX'] ?? null ?: (\getcwd().'/index.php')))
        : \realpath(\dirname($_SERVER['SCRIPT_FILENAME']))
    ));
    
    \defined('FW__OB_OUT') OR \define('FW__OB_OUT', \ob_get_level());
    (($_SERVER['FW__OB_FRONT_EN'] ?? true) && !FW__IS_CLI) AND \ob_start();
    \defined('FW__OB_TOP') OR \define('FW__OB_TOP', \ob_get_level());
    \define('FW__DX', (int) ($_SERVER['FW__DX'] ?? $_SERVER['REDIRECT_FW__DX'] ?? $_SERVER['REDIRECT_REDIRECT_FW__DX'] ?? 1));
    \define('FW__DBG', (int) ($_SERVER['FW__DBG'] ?? $_SERVER['REDIRECT_FW__DBG'] ?? $_SERVER['REDIRECT_REDIRECT_FW__DBG'] ?? 0));
    \defined('FW__DX_STATS') OR \define('FW__DX_STATS', FW__DX 
        ? [
            'MUSAGE' => memory_get_usage(),
            'RUSAGE' => getrusage(),
        ] 
        : []
    );
    1 AND \register_shutdown_function(function() {
        //no nonsense exit!
        if(\defined('FW__ABORT') && FW__ABORT === 0){ exit(); }
    });    
    
    $this->MSTART = new \DateTime(\date('Y-m-d H:i:s.'.\sprintf("%06d",(\FW__MSTART-floor(\FW__MSTART))*1000000), (int)\FW__MSTART));
    $this->INTFC = FW__INTFC;
    $this->DBG = FW__DBG;    
    $this->ROUTE = (object) [ 'SPAN' => 0, 'RESOLVE' => []];
    $this->REQ = (object) [];
    $this->FN = (object) [];
    \set_include_path(\strtr(__DIR__.PATH_SEPARATOR.\get_include_path(),'\\','/'));
    \spl_autoload_extensions("-#{$this->INTFC}.php,/-#{$this->INTFC}.php,-#.php,/-#.php");
    \spl_autoload_register();
    
    
    #region DX
    if(FW__DX){
        
        1 AND \ini_set('display_errors', 0);
        1 AND \ini_set('display_startup_errors', 1);
        1 AND \ini_set('error_reporting', E_ALL);
        0 AND \error_reporting(E_ALL);
        
        $fault__fn = function($ex, $title = 'Unhandled Exception'){
            $this->FAULTS[] = [
                'message' => $ex->getMessage(),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
                'trace' => $ex->getTrace(),
            ];
            global $_;
            \defined('FW__SIG_ABORT') OR \define('FW__SIG_ABORT', -1);
            $uid = uniqid();
            $f = FW__SITE_DIR.'/.local/faults/'.\date('Y-m/Y-md').'-faults.html';
            \is_dir($d = \dirname($f)) OR \mkdir($d, 0777, true);
            if(!\is_file($f)){
                $content = <<<HTML
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>LOG {$day}</title>
                </head>
                <body id="log-container">
                <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        const container = document.getElementById('log-container');
                        const cards = Array.from(container.getElementsByClassName('log-card'));
                        const spinner = document.getElementById('loading-spinner');
                        // 1. Reverse the array of elements in memory
                        cards.reverse();
                        // 2. Re-append them (this safely reorders them in the browser window)
                        cards.forEach(card => container.appendChild(card));
                        // 3. Hide the spinner once complete
                        if (spinner) spinner.style.display = 'none';
                    });
                </script>    
                </body>
                </html>  
                HTML;
            } else {
                $content = '';
            }
            $timestamp = \date('Y-md-Hi-s');
            $content .= <<<HTML
            <pre class="log-card" style="overflow:auto; color:red;border:1px solid red;padding:5px;"><b style="font-size:1.2em">{$message}: {$ex->getMessage()}</b>
            <b>{$ex}</b>
            <i style="margin-top:10px;color:blue;">Fault: {$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}</i>
            <i style="margin-top:10px;color:blue;">Request: {$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}</i>
            <i style="margin-top:10px;color:blue;">Ref: {$uid} - {$timestamp}</i>
            </pre>
            HTML;
            \file_put_contents($f, $content, FILE_APPEND | LOCK_EX);
            while(\ob_get_level() > 0){ @\ob_end_clean(); }
            switch($this->INTFC){
                case 'cli':{
                    if(($_SERVER['FW__DBG'] ?? null) >= 9){
                        echo "\033[91m\n"
                            .$ex::class.": {$ex->getMessage()}\n"
                            ."File: {$ex->getFile()}\n"
                            ."Line: {$ex->getLine()}\n"
                            ."\033[31m{$ex}\033[0m\n"
                        ;
                    } else {
                        echo "\033[91m{$ex->getMessage()}\033[0m\n";
                    }
                    exit(1); //* ALWAYS EXIT WITH 1
                } break;
                case 'web':{
                    if(\is_numeric($n = \strtok($ex->getMessage(),':'))){
                        $http_code = $n;
                    } else {
                        $http_code = 500;
                    }
                    \http_response_code($http_code);
                    if(($_SERVER['FW__DBG'] ?? null) >= 9){
                        print(<<<HTML
                            <pre style="overflow:auto; color:red;border:1px solid red;padding:5px;">{$title}: {$uid}
                            <b>{$ex}</b>
                            <i>Request: {$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}</i>
                            <b>{$ex->getMessage()}</b>
                            </pre>
                        HTML);
                    } else {
                        print(<<<HTML
                            <pre style="overflow:auto; color:red;border:1px solid red;padding:5px;">{$title}: {$uid}
                            <i>Request: {$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}</i>
                            </pre>
                        HTML);
                    }
                    exit($http_code);
                } break;
                case 'api':
                default: {
                    \http_response_code(500);
                    \header('Content-Type: application/json');
                    echo \json_encode([
                        'status' => "error",
                        'uid' => $uid,
                        'message' => $ex->getMessage(),
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); 
                    exit(500);
                } break;
            }
            //default exit
            exit(1); //* ALWAYS EXIT WITH 1
        };        
        
        1 AND \set_exception_handler(function($ex) use($fault__fn){
            if(\class_exists(\_\dx::class, false) && \method_exists(\_\dx::class,'on_exception')){
                return \_\dx::on_exception($ex, $title);
            }
            $fault__fn($ex, 'Unhandled Exception');
        });

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
        
        1 AND \register_shutdown_function(function() use($fault__fn){
            if(\class_exists(\_\dx::class, false) && \method_exists(\_\dx::class,'on_shutdown')){
                return \_\dx::on_shutdown();
            }
            if($this->DBG >= 9){
                $f = FW__SITE_DIR.'/.local/debug/shutdown.txt';
                \is_dir($d = \dirname($f)) OR \mkdir($d, 0777, true);
                \file_put_contents($f, \json_encode([
                    'span' => \number_format(((\microtime(true) - FW__MSTART)), 6).'s',
                    'trace' => $GLOBALS['_TRACE'] ?? [],
                    'fw' => (array) $this,
                    'constants' => \get_defined_constants(true)['user'],
                ], \_\JDUMP));
            }
            //$runspan = \number_format(((\microtime(true) - FW__PSTART)), 6).'s';
            if(\defined('FW__SIG_END')){
                throw new \Exception("Invalid SIG_END setting or Duplicate call to Root Finalizer");
            } else {
                \define('FW__SIG_END', \microtime(true));
            };
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
        
        (FW__DX >= 5) AND \is_file($f = FW__SITE_DIR.'/.local-dx-config.php') AND include $f;
        (!\FW__IS_CLI && ($_SERVER['FW__DX_OBACC_EN'] ?? false)) AND \ob_start(function($buffer){ 
            static $started;
            // 1. Append the current buffer text to a file
            file_put_contents(FW__SITE_DIR.'/.local/debug/obacc.txt', $buffer, ($started ? (FILE_APPEND | LOCK_EX) : 0));
            $started = true;
            // 2. Return the buffer to echo it out / pass it to the next layer
            return $buffer;
        });
        
    }
    
    $this->FN->OB_CLEAR = function(){ while(\ob_get_level() > 0){ @\ob_end_clean(); } };
    $this->FN->STATUS = function(bool $ok = true, string|null $message = null){
        static $status = [];
        if(\func_num_args()){
            $status = [$ok, $message];
            $GLOBALS['_TRACE'][] = $message;
        } else {
            return $status ?? [true, 'No Messages'];
        }
    };
    #region DISPATCHER
    $this->FN->DISPATCH = function($a, $b = null){
        try {
            if(\is_int($a)){
                $__FN = $this->FN;
                return function() use($a, $b, $__FN) {
                    while(\ob_get_level() > 0){ @\ob_end_clean(); } 
                    FW__IS_CLI OR \http_response_code(404);
                    echo $b ?: "Error {$a}";
                    if($this->DBG >= 9){ 
                        echo NL.($__FN->STATUS)()[1]; 
                    } 
                    exit($a);
                };
            } else if(\is_callable($a)) {
                return $a;
            } else {
                throw new \Exception("Invalid dispatcher");
            }
        } finally {
            \define('FW__DATA_DIR', $this->SITE_LOCAL_DIR);
            $this->ROUTE->SPAN = \number_format(((\microtime(true) - FW__MSTART)), 6).'s';
            if($this->DBG >= 9){
                $f = FW__SITE_DIR.'/.local/debug/route.txt';
                \is_dir($d = \dirname($f)) OR \mkdir($d, 0777, true);
                \file_put_contents($f, \json_encode([
                    'status' => ($this->FN->STATUS)(),
                    'trace' => $GLOBALS['_TRACE'] ?? [],
                    'fw' => (array) $this,
                    'constants' => \get_defined_constants(true)['user'],
                ], \_\JDUMP));
            }
            unset($this->FN);
        }
    };
    #region FILE RESOLVERS
    $this->FN->DIRC_IN = function($l, $s){ foreach($l as $k => $v){ if(\is_dir($p = "{$k}/{$s}")){ return $p; }} };
    $this->FN->FILE_IN = function($l, $s){ foreach($l as $k => $v){ if(\is_file($p = "{$k}/{$s}")){ return $p; }} };
    $this->FN->FILE_RESOLVE = function($path, array|string $sfx = ''){
        if($suffixes = \is_array($sfx) ? $sfx : [$sfx]){
            foreach($suffixes as $suffix){
                if($f = (($suffix)
                    ? \stream_resolve_include_path($r[] = "{$path}/{$suffix}")
                        ?: (\stream_resolve_include_path($r[] = "{$path}{$suffix}")
                    )
                    : \stream_resolve_include_path($r[] = "{$path}")
                )){
                    return \strtr($f,'\\','/');
                }
            }
        } else if($f = \stream_resolve_include_path($r[] = "{$path}")){
            return \strtr($f,'\\','/');
        }
    
        if(\str_starts_with($path,'_/')){
            static $recurse = 0;
            try {
                if($recurse){
                    //throw new \Exception("Detected Conflux Recursion for {$path}");
                    return null;
                }
                $recurse ++;
                if($i = \strpos($path = \strtr($path,'\\','/'),'/',2)){
                    $residue = \substr($path, $i + 1);
                    $class = \strtr(\substr($path, 0, $i),'/','\\');
                } else {
                    $residue = '';
                    $class = \strtr($path,'/','\\');
                }
                $p = ($residue ? "/{$residue}" : '');
                if(\class_exists($class)){
                    do{
                        if($f = ($this->FN->FILE_RESOLVE)(\strtr($class,'\\','/').$p, $sfx)){
                            return $f;
                        }
                    } while($class = \get_parent_class($class));                            
                }
            } finally {
                $recurse --;
            }
        }
    };
    #region SUPPLY ASSET
    $this->FN->SUPPLY_ASSET = function(){
        $filepath = $this->ROUTE->FILE;
        $mime_type = match($ext = \strtolower(\pathinfo($filepath, PATHINFO_EXTENSION))){
            'html' => null,
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'json' => 'application/json',
            'jpg'  => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'html' => 'text/html',
            'txt'  => 'text/plain',
            default => \mime_content_type($filepath) ?: 'application/octet-stream',
        };
        if(empty($mime_type)) {
            \http_response_code(404);
            echo '404: Not Found: Unknown Mime Type';
            exit(1);
        }  else {
            // Set appropriate headers
            $exit = (object)[];
            $exit->headers[] = 'Content-Type: ' . $mime_type;
            $exit->headers[] = 'Cache-Control: public, max-age=86400'; // Cache for 1 day
            $exit->headers[] = 'Expires: ' . \gmdate('D, d M Y H:i:s', \time() + 86400) . ' GMT'; // 1 day in the future
            $exit->headers[] = 'Last-Modified: ' . \gmdate('D, d M Y H:i:s', \filemtime($filepath)) . ' GMT';

            $is_hard_reload = 
                (isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'no-cache') || 
                (isset($_SERVER['HTTP_PRAGMA']) && $_SERVER['HTTP_PRAGMA'] === 'no-cache')
            ;
            $is_modified_since = (
                isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
                ($a = \strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])) >= ($b = \filemtime($filepath))
            );
            
            // Do you need to send the file
            if (!$is_hard_reload && $is_modified_since){
                $exit->code = 304; // Not Modified
                $exit->file = null;
            } else {
                // Output the file content
                $exit->file = $filepath;
            }
            while(\ob_get_level() > 0){ @\ob_end_clean(); } 
            if(\is_numeric($code = $exit->code ?? null)){
                \http_response_code($code ?: 200);
            } 
            foreach($exit->headers ?? [] as $k => $v){
                if(\is_string($v)){
                    if(\is_numeric($k)){
                        \header($v);
                    } else {
                        \header("{$k}: {$v}");
                    }
                }
            }
            if($exit->file){
                \readfile($exit->file);
            }
        }
    };

    #region RURP
    $this->REQ->PHP_SAPI = \php_sapi_name();
    $this->REQ->KEY = \sha1($_SERVER['SCRIPT_FILENAME']);
    if(\FW__IS_CLI){
        if(\session_status() == PHP_SESSION_NONE) {
            if(!empty($_SERVER['FW__SESSION'] ?? null)){
                \session_id($_SERVER['FW__SESSION']);
                \session_start();
            } else {
                \session_id('FW__PHP_CLI_SESSION');
                \session_start();
            }
        }
        $_REQUEST = (function(){
            $parsed = [];
            $key = null;
            $args = \array_slice($argv = $_SERVER['argv'] ?? [], 1);
            foreach ($args as $arg) {
                if ($key !== null) {
                    $parsed[$key] = $arg;
                    $key = null;
                } else if(\str_starts_with($arg, '-')){
                    if(\str_ends_with($arg, ':')){
                        $key = \substr($arg,0,-1);
                    } else if(\str_contains($arg,':')) {
                        [$k, $v] = \explode(':', $arg);
                        $parsed[$k] = $v;
                    } else {
                        $parsed[$arg] = true;
                    }
                } else {
                    $parsed[] = $arg;
                }
            }
            if ($key !== null) {
                $parsed[$key] = true;
            }
            $parsed[0] ??= '/';
            return $parsed;
        })();
        if(!\str_starts_with(($s = $_SERVER['argv'][1] ?? ''),'-')){
            $parsed = \parse_url('/'.\ltrim($s,'/'));
            !empty($parsed['query']) AND \parse_str($parsed['query'], $_GET);
            $this->REQ->RURP = $parsed['path'];
        } else {
            $this->REQ->RURP = '/';
        }
        $this->REQ->RSSN = '';
        $this->REQ->URL = '';
        $this->REQ->MODE = 'cli';
    } else {
        if(\str_contains($p = \strtok($_SERVER['REQUEST_URI'] ?? '','?'),'/-assets/')){
            $this->ROUTE->ASSET_MODE = 1;
            $this->ROUTE->ASSET_PATH = \ltrim($p,'/');
            if(\is_file($file = $this->ROUTE->RESOLVE[] = $_SERVER['DOCUMENT_ROOT'].'/'.$this->ROUTE->ASSET_PATH)){ 
                ($this->FN->STATUS)(true, 'Asset Mode 1 File Found');
                $this->ROUTE->FILE = $file;
                return ($this->FN->DISPATCH)($this->FN->SUPPLY_ASSET);
            }
        } else {
            $this->ROUTE->ASSET_MODE = 0;
            $this->ROUTE->ASSET_PATH = null;
        }
        $this->REQ->SPFX = '/$~';
        $p = \strtok($_SERVER['REQUEST_URI'],'?');
        if((\php_sapi_name() == 'cli-server')){
            $this->REQ->MODE = 'http-standalone';
            $this->REQ->RURP = $p;
        } else if((\str_starts_with($p, $n = $_SERVER['SCRIPT_NAME']))){
            $this->REQ->RURP = \substr($p,\strlen($n));
        } else if((($d = \dirname($n = $_SERVER['SCRIPT_NAME'])) == DIRECTORY_SEPARATOR)){
            $this->REQ->RURP = $p;
        } else {
            $this->REQ->RURP = \substr($p, \strlen($d));
        }
        if(\str_starts_with($this->REQ->RURP, $this->REQ->SPFX)){
            $this->REQ->RSSN = \trim(\strtok($this->REQ->RURP,'/'), $this->REQ->SPFX);
            $this->REQ->RURP = '/'.\strtok('');
            $this->INTFC = 'frame';
        } else {
            $this->REQ->RSSN = '';
        }
    }
    
    #region SESSION
    if(\session_status() == PHP_SESSION_NONE) {
        \session_name($this->REQ->KEY); 
        if($this->REQ->RSSN){
            \session_id($this->REQ->RSSN);
        }
        \session_start();
    }
    if($this->REQ->RSSN && empty($_SESSION['--CSRF'])){
        return ($this->FN->DISPATCH)(403, "403: Unauthorized");
    }
    \define('FW__CSRF', $this->CSRF =($_SESSION['--CSRF'] ?? null ?: ($_SESSION['--CSRF'] = \md5(uniqid('csrf-')))));
    if(
        \in_array($_SERVER['REQUEST_METHOD'], ['POST','PUT','PATCH','DELETE'])
        && ($a = $_REQUEST['--csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null) != ($_SESSION['--CSRF'] ?? null)
    ){
        return ($this->FN->DISPATCH)(406, "403: Not Acceptable");
    }
    $this->FLASH = $_SESSION['--FLASH'] ?? [];
    $_SESSION['--FLASH'] = [];

    \is_array($_SESSION['--USER'] ?? null) OR $_SESSION['--USER'] = [];
    $this->USER = $_SESSION['--USER'];
    
    
    #region BEARINGS
    $this->START_FILE = \str_replace('\\','/', __FILE__);
    $this->START_DIR = \dirname($this->START_FILE);
    $this->SITE_DIR = FW__SITE_DIR;
    $this->PLIB_DIR = \dirname($this->START_DIR);
    if(!($app = $_SERVER['FW__APP'] ?? 'app')){
        // this gets triggered if the FW__APP is set but is empty
        ($this->FN->STATUS)(false, "FW__APP configuration is blank");
        return ($this->FN->DISPATCH)(500, "500: Internal Error : {$this->REQ->RURP}");
    }
    if(
        !\is_dir($this->APP_DIR = \strtr($this->SITE_DIR."/{$app}.app",'\\','/'))
        && !\is_dir($this->APP_DIR = \strtr($this->PLIB_DIR."/{$app}.app",'\\','/'))
    ){
        ($this->FN->STATUS)(false, "Unable to locate application directory: {$app}");
        return ($this->FN->DISPATCH)(500, "500: Internal Error : {$this->REQ->RURP}");
    }
    //* The primary library is the working directory to begin with
    $this->KEY = \md5($_SERVER['SCRIPT_FILENAME']);
    $this->INCP_DIR = \str_replace('\\','/',\dirname($_SERVER['SCRIPT_FILENAME']));
    $this->IS_CLI = FW__IS_CLI;
    $this->IS_HTTP = !$this->IS_CLI;
    $this->IS_HTML = \str_contains(($_SERVER['HTTP_ACCEPT'] ?? ''),'text/html');
    $this->ROOT_DIR = \str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']);
    $this->ROOT_URL = 
        ($_SERVER["REQUEST_SCHEME"] ?? ((\strtolower(($_SERVER['HTTPS'] ?? 'off') ?: 'off') === 'off') ? 'http' : 'https'))
        .'://'
        .$_SERVER['HTTP_HOST']
    ;
    $this->SURP = (function(){
        ($p = \strtok($_SERVER['REQUEST_URI'],'?'));
        if((\str_starts_with($p, $n = $_SERVER['SCRIPT_NAME']))){
            return \substr($p, 0, \strlen($_SERVER['SCRIPT_NAME']));
        } else if((($d = \dirname($n = $_SERVER['SCRIPT_NAME'])) == DIRECTORY_SEPARATOR)){
            return '';
        } else {
            return \substr($p, 0, \strlen($d));
        }
    })();
    
    #region CONFIG 
    \is_dir($this->SITE_LOCAL_DIR = $d = $this->SITE_DIR."/.local") OR \mkdir($d,0777,true);
    \is_dir($this->SITE_DATA_DIR = $d = $this->SITE_DIR."/--epx/data") OR \mkdir($d,0777,true);
    \file_exists($f = $this->APP_DIR.'/.config.php') AND (function($f){ global $_; include $f; })($f);
    \file_exists($f = $this->SITE_LOCAL_DIR.'/cfg/.config.php') AND (function($f){ global $_; include $f; })($f);
    \set_time_limit($_['TIMELIMIT'] ?? 5);
    \date_default_timezone_set($_['TIMEZONE'] ?? null ?: \getenv('FW_TIMEZONE') ?: 'Australia/Adelaide');
    $this->LIB_LIST = \iterator_to_array((function(){
        global $_;
        yield \strtr(\realpath($d = $this->PLIB_DIR), '\\','/') => \strtr($d, '\\','/');
        foreach($_['LIBRARIES'] ?? [] as $d => $en){
            if(\is_numeric($d)){ $d = $en; $en = true; }
            if($en){
                $dir = $d;
                if(
                    (($d[0]??'')=='/' || ($d[1]??'')==':')
                    || \is_dir($dir = $this->SITE_DIR."/--epx/{$d}")
                    || \is_dir($dir = \dirname($this->PLIB_DIR)."/{$d}")
                ){
                    yield \strtr(\realpath($dir), '\\','/') => \strtr($dir, '\\','/');
                }
            }
        }
    })());
    $this->TSP_LIST = (function($iter_fn){ 
        $list = [];
        foreach($iter_fn() as $dx){
            $list[\str_replace('\\','/', $dx)] = [
                'real' => \strtr(\realpath($dx),'\\','/'), 
                'webscope' => \str_starts_with($dx, $this->ROOT_DIR),
            ];
        }
        return $list;
    })(function(){
        global $_;
        yield $this->APP_DIR;
        foreach($_['MODULES'] ?? ['app','abaca'] as $d => $en){
            if(\is_numeric($d)){ $d = $en; $en = true; }
            if($dx = ($this->FN->DIRC_IN)($this->LIB_LIST, $d)){
                yield $dx;
            }
        }
        yield $this->START_DIR;
        foreach(\explode(PATH_SEPARATOR,FW__PHP_TSP_DEFAULTS['path']) as $dx){
            yield $dx;
        }
    });
    1 AND \set_include_path(\implode(PATH_SEPARATOR, array_keys($this->TSP_LIST)));
    0 AND \spl_autoload_extensions("-#{$this->INTFC}.php,/-#{$this->INTFC}.php,-#.php,/-#.php");
    0 AND \spl_autoload_register();

    #region REQ EXCESS
    if(FW__IS_CLI){
        $this->REQ->FRAME = '';
    } else {
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
        $this->REQ->IS_XHR = ('xmlhttprequest' == \strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '' ));
        $this->REQ->FRAME = $this->REQ->RSSN ? $this->REQ->SPFX.$this->REQ->RSSN : '';
    }

    #region ROUTE RESOLVE
    if(\str_starts_with($this->REQ->RURP,'/-assets/')){
        if(
            ($file = ($this->FN->FILE_IN)($this->TSP_LIST, $p = \ltrim($this->REQ->RURP,'/')))
            || ($file = ($this->FN->FILE_IN)($this->LIB_LIST, $p))
        ){
            ($this->FN->STATUS)(true, 'Asset Mode 2 File Found');
            $this->ROUTE->FILE = $file;
            return ($this->FN->DISPATCH)($this->FN->SUPPLY_ASSET);
        } else {
            ($this->FN->STATUS)(false, 'Asset Mode 2 File Failed');
            return ($this->FN->DISPATCH)(404, "404: Not Found : {$this->REQ->RURP}");
        }
    } else if(!\preg_match(
        "#^/"
            ."(?:"
                ."(?:"
                    ."(?<BASE>"
                        ."(?<PANE>(?:--)[^/\.]*)"
                        ."(?:\.(?<ROLE>[^/]*))?"
                    .")/?"
                .")?"
                ."(?<NPATH>.*)"
            .")?"
        . "$#",
        $this->REQ->RURP,
        $m
    )){
        return ($this->FN->DISPATCH)(400, "400: Bad Request: Invalid request path format: {$this->REQ->RURP}");
    } else {
        foreach(\array_filter($m, fn($k) => !is_numeric($k), \ARRAY_FILTER_USE_KEY) as $k => $v){
            $this->ROUTE->$k = $v;
        }
    }
    $this->ROUTE->PANE = $this->ROUTE->PANE ?? '';
    $this->ROUTE->ROLE = $this->ROUTE->ROLE ?? '';
    $this->ROUTE->PANEL = \trim(\strtr($this->ROUTE->PANE ?: '__', '-','_'),'/');
    $this->ROUTE->FACET = \trim($this->ROUTE->PANEL,'_');
    $this->ROUTE->BASE = ($this->REQ->FRAME ? "{$this->REQ->FRAME}/" : '').($this->ROUTE->PANE).($this->ROUTE->ROLE ? ".{$this->ROUTE->ROLE}" : '');
    if(\str_contains($this->ROUTE->NPATH,'/-assets/')){
        $this->ROUTE->ASSET_MODE = 3;
        $this->ROUTE->ASSET_PATH = $this->ROUTE->NPATH;
        $this->ROUTE->SCHEME_A = [\implode('/', \array_filter([$this->ROUTE->PANEL, $this->ROUTE->NPATH]))];
        $this->ROUTE->SCHEME_B = [\implode('/', \array_filter(['_', $this->ROUTE->NPATH ]))];
    } else if(!$this->ROUTE->ASSET_MODE && $asset_path = $_GET['-asset'] ?? null){
        $this->ROUTE->ASSET_MODE = 4;
        $this->ROUTE->ASSET_PATH = \ltrim(\urldecode($asset_path),'/');
        $this->ROUTE->SCHEME_A = [\implode('/', \array_filter([$this->ROUTE->PANEL, $this->ROUTE->NPATH, $this->ROUTE->ASSET_PATH]))];
        $this->ROUTE->SCHEME_B = [\implode('/', \array_filter(['_', $this->ROUTE->NPATH ?: '_index', $this->ROUTE->ASSET_PATH]))];
    } else {
        $this->ROUTE->ASSET_MODE = 0;
        $this->ROUTE->SCHEME_A = [
            \implode('/', \array_filter([$this->ROUTE->PANEL, $this->ROUTE->NPATH])), 
            ["-@{$this->INTFC}.php", "-@.php", "-@.html"]
        ];
        $this->ROUTE->SCHEME_B = ($this->ROUTE->NPATH)
            ? [
                \implode('/', \array_filter(['_',$this->ROUTE->NPATH])),
                ["-{$this->ROUTE->FACET}@{$this->INTFC}.php", "-{$this->ROUTE->FACET}@.php", "-{$this->ROUTE->FACET}@.html"]
            ]
            :[
                "_/_index/{$this->ROUTE->FACET}",
                ["-@{$this->INTFC}.php", "-@.php", "-@.html"]
            ]
        ;
    }
    
    if(FW__IS_CLI){
        if(\str_starts_with($this->SITE_DIR, $this->ROOT_DIR)){
            $this->SURP = \substr($this->SITE_DIR, \strlen($this->ROOT_DIR));
        } else {
            $this->SURP = false;
        }
    } else if((\php_sapi_name() == 'cli-server')){
        $this->SURP = '';
    } else {
        $p = \strtok($_SERVER['REQUEST_URI'],'?');
        if((\str_starts_with($p, $n = $_SERVER['SCRIPT_NAME']))){
            $this->SURP = \substr($p, 0, \strlen($_SERVER['SCRIPT_NAME']));
        } else if((($d = \dirname($n = $_SERVER['SCRIPT_NAME'])) == DIRECTORY_SEPARATOR)){
            $this->SURP = '';
        } else {
            $this->SURP = \substr($p, 0, \strlen($d));
        }
    }
    $this->SITE_URL = \rtrim($this->ROOT_URL.$this->SURP,'/');
    $this->BASE_URL = \rtrim($this->SITE_URL.'/'.$this->ROUTE->BASE,'/');
    $this->CTLR_URL = \rtrim($this->BASE_URL."/".$this->ROUTE->NPATH,'/');    
    
    if(
        ($_SERVER['FW__SESSION_EN'] ?? true)
        && ($file = \stream_resolve_include_path('_/i/auth/authenticate-x.php'))
        && \is_callable($fn = (fn($f) => include $f)($file))
    ){
        ($this->FN->STATUS)(true, "Auth Prempt");
        return ($this->FN->DISPATCH)($fn);
    } else if($this->ROUTE->ASSET_MODE){
        if(
            ($file = ($this->FN->FILE_RESOLVE)(...$this->ROUTE->SCHEME_A))
            || ($file = ($this->FN->FILE_RESOLVE)(...$this->ROUTE->SCHEME_B))
        ){
            ($this->FN->STATUS)(true, "Asset Mode {$this->ROUTE->ASSET_MODE} File found");
            $this->ROUTE->FILE = $file;
            return ($this->FN->DISPATCH)($this->FN->SUPPLY_ASSET);
        } else {
            ($this->FN->STATUS)(false, "Asset Mode {$this->ROUTE->ASSET_MODE} File not found");
            return ($this->FN->DISPATCH)(404, "404: Not Found : {$this->REQ->RURP}");
        }
    } else if(
        ($file = ($this->FN->FILE_RESOLVE)(...$this->ROUTE->SCHEME_A))
        || ($file = ($this->FN->FILE_RESOLVE)(...$this->ROUTE->SCHEME_B))
    ){
        ($this->FN->STATUS)(true, "Controller File found");
        $this->ROUTE->FILE = $file;
        $this->ROUTE->INITS = (function(...$path_list){
            $inits = [];
            if($f = \stream_resolve_include_path("vendor/autoload.php")){
                $inits[] = $f;
            }
            foreach($tsp = \explode(PATH_SEPARATOR,\get_include_path() ?? []) as $d){
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
        return ($this->FN->DISPATCH)(function(){ 
            foreach($this->ROUTE->INITS ?? [] as $f){ 
                (function($f){include_once $f;})($f);
            }
            if(\is_callable($o = include $this->ROUTE->FILE ?? null)){
                $return = $o();
            } else {
                $return = $o;
            }
            if(\is_array($return) || (\is_object($return) && $return instanceof \JsonSerializable)){
                FW__IS_CLI OR \header('Content-Type: application/json');
                echo \json_encode($return, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                exit(0);
            } else if(\is_string($return)) {
                if(\str_starts_with($return, "Error:")){
                    if(FW__IS_CLI){
                        print("\e[91m{$return}\e[0m\n");
                    } else {
                        echo $ret;
                    }
                    exit(1);
                } else {
                    echo $ret;
                    exit(0);
                }
            }
        });
    } else {
        ($this->FN->STATUS)(false, "Controller File not found");
        return ($this->FN->DISPATCH)(404, "404: Not Found : {$this->REQ->RURP}");
    }
    
})->bindTo($GLOBALS['FW'] = (object)[])()(); }

