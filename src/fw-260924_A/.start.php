<?php //250911
########################################################################################################################
#region LICENSE
    /* 
                                               EPX-PAX-ALTO
    PROVIDER : KLUDE PTY LTD
    PACKAGE  : EPX-PAX
    AUTHOR   : BRIAN PINTO
    RELEASED : 2026-09-24
    
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
}
namespace _ { (function(){
    
    #region INIT
    global $_;
    \is_array($_) OR $_ = [];
    \is_array($GLOBALS['_TRACE'] ?? null) OR $GLOBALS['_TRACE'] = [];

    \define('FW__IS_CLI', empty($_SERVER['HTTP_HOST']));
    \define('FW__PHP_TSP_DEFAULTS', $_SERVER['FW__PHP_TSP_DEFAULTS'] ?? [
        "handler" => "spl_autoload",
        "extensions" => \spl_autoload_extensions(),
        "path" =>  \get_include_path(),
    ]);
    \define('FW__INTFC', $intfc = $_SERVER['FW__INTFC']
        ?? (FW__IS_CLI 
            ? 'cli'
            : $_SERVER['HTTP_X_REQUEST_INTERFACE'] ?? 'web'
        )
    );
    \define('FW__START_FILE', \str_replace('\\','/', __FILE__));
    \define('FW__SITE_DIR', \str_replace('\\','/', \FW__IS_CLI
        ? \realpath(\dirname($_SERVER['FW__SITE_INDEX'] ?? null ?: (\getcwd().'/index.php')))
        : \realpath(\dirname($_SERVER['SCRIPT_FILENAME']))
    ));
    \define('FW__CORE_DIR', \str_replace('\\','/', \dirname(__DIR__)));
    \define('FW__SITE_LOCAL_DIR', FW__SITE_DIR.'/.local');
    \define('FW__DATA_DIR', (function(){
        $index = \str_replace(
            ['\\','/'],
            ['~','~'],
            ($_SERVER['FW__DATA'] ?? $_SERVER['REDIRECT_FW__DATA'] ?? $_SERVER['REDIRECT_REDIRECT_FW__DATA'] ?? '')
        );
        \is_dir($d = FW__SITE_LOCAL_DIR."/db".($index ? "-{$index}" : "")) OR \mkdir($d, 0777, true);
        return $d;
    })());
    \defined('FW__OB_OUT') OR \define('FW__OB_OUT', \ob_get_level());
    (($_SERVER['FW__OB_FRONT_EN'] ?? true) && !FW__IS_CLI) AND \ob_start();
    \defined('FW__OB_TOP') OR \define('FW__OB_TOP', \ob_get_level());
    \define('FW__DX', (function(){
        $raw_dx = $_SERVER['FW__DX'] ?? $_SERVER['REDIRECT_FW__DX'] ?? $_SERVER['REDIRECT_REDIRECT_FW__DX'] ?? 'F';
        $clean_dx = preg_replace('/[^0-9a-fA-F]/', '', $raw_dx) ?: 'F';
        return (int) hexdec($clean_dx);
    })());
    \define('FW__DBG', (int) ($_SERVER['FW__DBG'] ?? $_SERVER['REDIRECT_FW__DBG'] ?? $_SERVER['REDIRECT_REDIRECT_FW__DBG'] ?? 0));
    \defined('FW__DX_STATS') OR \define('FW__DX_STATS', (FW__DBG >= 9) 
        ? [
            'MUSAGE' => memory_get_usage(),
            'RUSAGE' => getrusage(),
        ] 
        : []
    );

    1 AND \register_shutdown_function(function() {
        //no nonsense exit!
        if(\defined('FW__SIG_ABORT') && FW__SIG_ABORT === 0){ exit(); }
    });
    
    #region DX
    if(FW__DX & 0x01){
        
        1 AND \ini_set('display_errors', 0);
        1 AND \ini_set('display_startup_errors', 1);
        1 AND \ini_set('error_reporting', E_ALL);
        0 AND \error_reporting(E_ALL);
        
        $report__fn = function($ex, $title, &$uid){
            $uid = uniqid();
            $f = FW__SITE_DIR.'/.local/debug/'.\date('Y-m/Y-md').'-faults.html';
            \is_dir($d = \dirname($f)) OR \mkdir($d, 0777, true);
            $timestamp = \date('Y-md-Hi-s');
            $content = <<<HTML
            <pre class="log-card" style="overflow:auto; color:red;border:1px solid red;padding:5px;"><b style="font-size:1.2em">{$message}: {$ex->getMessage()}</b>
            <b>{$ex}</b>
            <i style="margin-top:10px;color:blue;">Fault: {$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}</i>
            <i style="margin-top:10px;color:blue;">Request: {$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}</i>
            <i style="margin-top:10px;color:blue;">Ref: {$uid} - {$timestamp}</i>
            </pre>
            HTML;
            \file_put_contents($f, $content, FILE_APPEND | LOCK_EX);
            \file_put_contents(\dirname($f).'/now-fault.html', $content, FILE_APPEND | LOCK_EX);
        };
        
        $fault__fn = function($ex, $title = 'Unhandled Exception') use($report__fn){
            global $_;
            \defined('FW__SIG_ABORT') OR \define('FW__SIG_ABORT', -1);
            $report__fn($ex, $title, $uid);
            while(\ob_get_level() > 0){ @\ob_end_clean(); }
            switch(FW__INTFC){
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
            if(FW__INTFC){
                $f = FW__SITE_DIR.'/.local/debug/shutdown.txt';
                \is_dir($d = \dirname($f)) OR \mkdir($d, 0777, true);
                \file_put_contents($f, \json_encode([
                    'span' => \number_format(((\microtime(true) - FW__MSTART)), 6).'s',
                    'trace' => $GLOBALS['_TRACE'] ?? [],
                    'fw' => (array) ($GLOBALS['FW'] ?? []),
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
    }
    
    #region ENV
    // ELOCK
    //  1: rebuild only if not existing  -- default
    //  0: rebuild if needed
    // -1: rebuild always
    \define('FW__ENV_FILE', FW__DATA_DIR."/.env-{$intfc}.php");
    if(\is_file(FW__ENV_FILE)){
        try{
            $h = fopen(FW__ENV_FILE, 'r');
            if (flock($h, LOCK_SH)) {
                try {
                    (fn() => include \FW__ENV_FILE)();
                } finally {
                    \flock($h, LOCK_UN);
                }
            } else {
                throw new \Exception("Cache error");
            }
        } finally {
            fclose($h);
        }
    }
    
    if(!\defined('FW__APP_DIR')){
        \define('FW__ENV_BUILD', true);
        global $_;
        $this->BUILD_HAS_ERRORS = 0;
        #region HTTP ROOT
        if(!empty($_SERVER['DOCUMENT_ROOT']) && \is_file($f = "{$_SERVER['DOCUMENT_ROOT']}/.local/.http-root.php")){
            include $f;
        } else {
            if(!FW__IS_CLI){
                $root_dir = \strtr($_SERVER['DOCUMENT_ROOT'], '\\','/');
                \file_put_contents($f, <<<PHP
                <?php
                1 AND empty(\$_SERVER[\$n='DOCUMENT_ROOT']) AND \$_SERVER[\$n] = "{$root_dir}";
                1 AND empty(\$_SERVER[\$n='FW__ROOT_DIR']) AND \$_SERVER[\$n]  = "{$root_dir}";
                1 AND empty(\$_SERVER[\$n='FW__ROOT_DOM']) AND \$_SERVER[\$n]  = "{$_SERVER['HTTP_HOST']}";
                1 AND !isset(\$_ENV['DB_HOSTNAME']) AND \$_ENV['DB_HOSTNAME'] = 'localhost';
                1 AND !isset(\$_ENV['DB_DATABASE']) AND \$_ENV['DB_DATABASE'] = 'default_db';
                1 AND !isset(\$_ENV['DB_USERNAME']) AND \$_ENV['DB_USERNAME'] = 'root';
                1 AND !isset(\$_ENV['DB_PASSWORD']) AND \$_ENV['DB_PASSWORD'] = 'pass';
                1 AND !isset(\$_ENV['DB_CHAR_SET']) AND \$_ENV['DB_CHAR_SET'] = 'utf8mb4';
                PHP);
                include $f;
            } else {
                for (
                    $i=0, $dx=\getcwd(); 
                    $dx && $i < 20 ; 
                    $i++, $dx = (\strchr($dx, DIRECTORY_SEPARATOR) != DIRECTORY_SEPARATOR) ? \dirname($dx) : null
                ){ 
                    if(\is_file($f = "{$dx}/.local/.http-root.php")){
                        include $f;
                        break;
                    }
                }
            }
        }
        if(empty($_SERVER['DOCUMENT_ROOT'])){
            echo "\e[93mWarning: DOCUMENT_ROOT is unknown! \e[0m\n";
        }
        #region CONFIG
        $this->PLIB_DIR = \strtr(__DIR__,'\\','/');
        $this->ROOT_DIR = \str_replace('\\','/', $_SERVER['DOCUMENT_ROOT']);
        $this->ROOT_DIR = $_SERVER['DOCUMENT_ROOT'] ?? false ?: FW__SITE_DIR;
        $this->ROOT_DIR_REAL = \strtr(\realpath($this->ROOT_DIR),'\\','/');
        \file_exists($this->CORE_CFG = $f = FW__CORE_DIR.'/.local/cfg/.config.php') AND (function($f){ global $_; include $f; })($f);
        \file_exists($this->DATA_CFG = $f = FW__DATA_DIR.'/.config.php') AND (function($f){ global $_; include $f; })($f);
        if(!($app = $_['APP']['SELECT'] ?? $_SERVER['FW__APP'] ?? $_SERVER['REDIRECT_FW__APP'] ?? $_SERVER['REDIRECT_REDIRECT_FW__APP'] ?? 'app')){
            // this gets triggered if the FW__APP is set but is empty
            FW__IS_CLI OR \http_response_code(500);
            echo FW__DBG >= 9 ? "500: APP configuration is blank" : "500: Internal Server Error";
            exit(1);
        }
        if(\str_contains($app,'/')){
            if(
                !\is_dir($app_dir = \strtr(FW__SITE_DIR."/{$app}",'\\','/'))
                && !\is_dir($app_dir = \strtr(FW__CORE_DIR."/{$app}",'\\','/'))
            ){
                FW__IS_CLI OR \http_response_code(500);
                echo FW__DBG >= 9 ? "500: Unable to locate application directory: {$app}" : "500: Internal Server Error";
                exit(1);
            }
        } else {
            if(
                !\is_dir($app_dir = \strtr(FW__SITE_DIR."/fw/{$app}",'\\','/'))
                && !\is_dir($app_dir = \strtr($this->PLIB_DIR."/{$app}",'\\','/'))
            ){
                FW__IS_CLI OR \http_response_code(500);
                echo FW__DBG >= 9 ? "500: Unable to locate application directory: {$app}" : "500: Internal Server Error";
                exit(1);
            }
        }
        $this->APP_DIR = $app_dir;
        $this->ALIB_DIR = \dirname($app_dir);
        $this->HTACCESS = \str_replace('\\','/', FW__SITE_DIR.'/.htaccess');
        \file_exists($this->APP_CFG = $f = $app_dir.'/.config.php') AND (function($f){ global $_; include $f; })($f);
        \file_exists($this->SITE_CFG = $f = FW__SITE_LOCAL_DIR.'/cfg/.config.php') AND (function($f){ global $_; include $f; })($f);
        $this->TIMELIMIT = $_['TIMELIMIT'] ?? 5;
        $this->TIMEZONE = $_['TIMEZONE'] ?? null ?: \getenv('FW_TIMEZONE') ?: 'Australia/Adelaide';
        #region LIB LIST
        $this->LIB_LIST = \iterator_to_array((function(){
            global $_;
            yield \strtr(\realpath($d = $this->ALIB_DIR), '\\','/') => \strtr($d, '\\','/');
            yield \strtr(\realpath($d = $this->PLIB_DIR), '\\','/') => \strtr($d, '\\','/');
            foreach($_['LIBRARIES'] ?? [] as $d => $en){
                if(\is_numeric($d)){ $d = $en; $en = true; }
                if($en){
                    $dir = $d;
                    if(
                        (($d[0]??'')=='/' || ($d[1]??'')==':')
                        || \is_dir($dir = FW__SITE_DIR."/{$d}")
                        || \is_dir($dir = FW__CORE_DIR."/{$d}")
                    ){
                        yield \strtr(\realpath($dir), '\\','/') => \strtr($dir, '\\','/');
                    }
                }
            }
        })());
        #region CTS LIST
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
                $d = \strtr($d,'\\','/');
                if(\str_contains($d,'/')){
                    if((($d[0]??'')=='/' || ($d[1]??'')==':')){
                        yield $d;
                    } else if(
                        \is_dir($p = FW__SITE_DIR."/{$d}")
                        || \is_dir($p = FW__CORE_DIR."/{$d}")
                    ){
                        yield $p;
                    }
                } else {
                    foreach($this->LIB_LIST as $k => $v){ 
                        if(\is_dir($p = "{$k}/{$d}")){ 
                            yield $p; 
                        }
                    }
                }
            }
            foreach(\explode(PATH_SEPARATOR,FW__PHP_TSP_DEFAULTS['path']) as $dx){
                yield $dx;
            }
        });
        #region CTS INITS
        $this->TSP_INIT = (function(){
            $tsp_init = [];
            $tsp = \array_keys($this->TSP_LIST);
            $intfc = FW__INTFC;
            foreach(\array_reverse($tsp) as $d){
                if(\is_file($f = "{$d}/.module.php")){
                    $tsp_init[\str_replace('\\','/', $f)] = true;
                }
            }
            foreach($tsp as $d){
                if(\is_file($f = "{$d}/.functions-{$intfc}.php")){
                    $tsp_init[\str_replace('\\','/', $f)] = true;
                }
                if(\is_file($f = "{$d}/.functions.php")){
                    $tsp_init[\str_replace('\\','/', $f)] = true;
                }
            }
            //* add only one autoloader - the first one you encounter
            foreach($tsp as $d){
                if(\is_file($f = "{$d}/autoload.php")){
                    $tsp_init[\str_replace('\\','/', $f)] = true;
                    break;
                }
            }
            return $tsp_init;
        })();
        #region ENV WRITE
        $format_var__fn = function($var){
            if(\is_scalar($var)){
                return \var_export($var, true);
            } else {
                //FORMAT
                static $pattern = [
                    '/=>\s\n\s*array \(/', // 1. Fix '=> array(' format
                    '/(\n\s*)\)/',         // 2. Replace closing array paren with bracket
                    '/array \(\n/',        // 3. Replace opening array() with [
                    '/\n/',                // 4. Indent all lines (replaces your str_replace)
                ];
                static $replace = [
                    '=> array (',          // No newline here
                    '$1]',                 // Keep original indentation for bracket
                    "[\n",                 // Use double quotes for real newline
                    "\n",                  // Use double quotes + 2 spaces for indentation
                ];
                return \preg_replace($pattern,$replace,\var_export($var,true));
            }
        };
        $format_txt_trace__fn = function($array){
            $flatlist = [];
            array_walk_recursive($array, function($item) use (&$flatlist) {
                $flatlist[] = $item;
            });
            return "\n# ".\implode(PHP_EOL."# ", \array_values($flatlist));
        };
        $format_var_trace__fn = function($var) use($format_var__fn){
            return \str_replace("\n","\n# ", $format_var__fn($var));
        };
        
        $BUILD_HAS_ERRORS = $this->BUILD_HAS_ERRORS ? 1 : 0;
        $build_export = <<<PHP
        \$BUILD_HAS_ERRORS = {$BUILD_HAS_ERRORS};
        \$FW__CORE_CFG = '{$this->CORE_CFG}';
        \$FW__DATA_CFG = '{$this->DATA_CFG}';
        \$FW__SITE_CFG = '{$this->SITE_CFG}';
        \$FW__APP_CFG = '{$this->APP_CFG}';
        \$FW__HTACCESS = '{$this->HTACCESS}';
        \$ELOCK = (int) (\$_SERVER["FW__ELOCK"] ?? \$_SERVER["REDIRECT_FW__ELOCK"] ?? \$_SERVER["REDIRECT_REDIRECT_FW__ELOCK"] ?? 1);
        if(\$BUILD_HAS_ERRORS){
            if(\defined("FW__ENV_BUILD")){
                if(empty(\$_SERVER['HTTP_HOST'])){
                    echo "\\e[91mENV BUILD FAULT - ELOCK {\$ELOCK}\\e[0m\\n";
                    exit(1);
                } else {
                    \http_response_code(500);
                    if(\FW__DBG >= 9){
                        echo "500: Internal Server Error: (ENV BUILD FAULT - ELOCK {\$ELOCK})";
                    } else {
                        echo "500: Internal Server Error";
                    }
                }
                exit(1);
            } else {
                return false;
            }
        }
        if(
            (match(\$ELOCK){
                default => false,
                1 => false,
                0 => ((\$e1 = filemtime(__FILE__)) < max(
                    (\$e2 = (is_file(\$FW__CORE_CFG)) ? filemtime(\$FW__CORE_CFG) : 0),
                    (\$e3 = (is_file(\$FW__DATA_CFG)) ? filemtime(\$FW__DATA_CFG) : 0),
                    (\$e4 = (is_file(\$FW__APP_CFG)) ? filemtime(\$FW__APP_CFG) : 0),
                    (\$e5 = (is_file(\$FW__SITE_CFG)) ? filemtime(\$FW__SITE_CFG) : 0),
                    (\$e6 = (is_file(\$FW__HTACCESS)) ? filemtime(\$FW__HTACCESS) : 0),
                    (\$e7 = filemtime(\$_SERVER["SCRIPT_FILENAME"]))
                )),
                -1 => true
            }) && (!\defined("FW__ENV_BUILD"))
        ){
            return false;
        }
        PHP;
        
        $cfg_export = <<<PHP
        \\set_time_limit({$format_var__fn($this->TIMELIMIT)});
        \\date_default_timezone_set({$format_var__fn($this->TIMEZONE)});
        \\define('FW__ROOT_DIR', {$format_var__fn($this->ROOT_DIR)});
        \\define('FW__ROOT_DIR_REAL', {$format_var__fn($this->ROOT_DIR_REAL)});
        \\define('FW__APP_DIR', {$format_var__fn($this->APP_DIR)});
        \\define('FW__LIB_LIST', {$format_var__fn($this->LIB_LIST)});
        \\define('FW__TSP_LIST', {$format_var__fn($this->TSP_LIST)});
        \\define('FW__TSP_INIT', {$format_var__fn($this->TSP_INIT)});
        PHP;
        $start_export = <<<PHP
        1 AND \\set_include_path(\implode(PATH_SEPARATOR, array_keys(FW__TSP_LIST)));
        1 AND \\spl_autoload_extensions("-#{$intfc}.php,/-#{$intfc}.php,-#.php,/-#.php");
        1 AND \\spl_autoload_register();
        PHP;
        $def = $_['DEF'] ?? [];
        $env = $_['ENV'] ?? [];
        $srv = $_['SRV'] ?? [];
        $def['COM'] = $_['COM'] ?? [];
        $env_export = "\n  {$format_var__fn($env)}";
        $srv_export = "\n  {$format_var__fn($srv)}";
        $def_export = '';
        foreach($def as $k => $v){
            $def_export .= "\defined('FW__{$k}') OR \define('FW__{$k}', {$format_var__fn($v)});\n";
        }
        $stamp = \date('Y-md-Hi-s');
        $trace_export = $format_txt_trace__fn( $this->_TRACE ?? []);
        $trace_export .= "\n# LIBRARIES:".$format_txt_trace__fn($this->_TRACE_LIB ?? []);
        $trace_export .= "\n# MODULES:".$format_txt_trace__fn($this->_TRACE_MOD ?? []);
        $trace_export .= "\n# CONFIG: ".$format_var_trace__fn($_);
        $contents = <<<PHP
        <?php 
        # EPX PAX Version 1.00 (C) Klude Pty Ltd.
        # Generated On: {$stamp}
        {$build_export}
        # Configuration
        {$cfg_export}
        # Defines
        {$def_export}
        # Don't override the environment 
        \$_ENV += {$env_export}
        ;
        # Override the server supplement 
        \$_SERVER['.'] = \array_replace_recursive(\n  \$_SERVER['.'] ?? [], {$srv_export}
        );
        {$start_export}
        # TRACE:
        # {$trace_export}
        PHP;
        \is_dir($d = \dirname(\FW__ENV_FILE)) OR \mkdir($d,0777,true);
        \file_put_contents(
            \FW__ENV_FILE, 
            $contents,
            LOCK_EX // prevents race when testing and you have ton of simultaneous requests
        );
        \function_exists('opcache_invalidate') AND \opcache_invalidate(FW__ENV_FILE, true);
        try{
            // prevents race for launchers when testing 
            // and you have ton of simultaneous requests
            $h = fopen(FW__ENV_FILE, 'r');
            if (flock($h, LOCK_SH)) {
                try {
                    (fn() => include \FW__ENV_FILE)();
                } finally {
                    \flock($h, LOCK_UN);
                }
            } else {
                throw new \Exception("Cache error");
            }
        } finally {
            fclose($h);
        }
    }

})->bindTo((object)[])(); }
namespace {
    #region START
    if($f = \stream_resolve_include_path('.boot.php')){
        return include $f;
    } else if(0) {
        FW__IS_CLI OR \http_response_code(500);
        echo FW__DBG >= 9 ? "500: Unable to locate .boot.php" : "500: Internal Server Error";
        exit(1);
    } else {
        // fall thru
    }
}
namespace { (function(){ 
    
    $this->MSTART = new \DateTime(\date('Y-m-d H:i:s.'.\sprintf("%06d",(\FW__MSTART-floor(\FW__MSTART))*1000000), (int)\FW__MSTART));
    $this->INTFC = FW__INTFC;
    $this->DBG = FW__DBG;    
    $this->ROUTE = (object) [ 'SPAN' => 0, 'RESOLVE' => []];
    $this->REQ = (object) [];
    $this->FN = (object) [];
    
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
    $this->START_FILE = FW__START_FILE;
    $this->SITE_DIR = FW__SITE_DIR;
    $this->KEY = \md5($_SERVER['SCRIPT_FILENAME']);
    $this->IS_CLI = FW__IS_CLI;
    $this->IS_HTTP = !$this->IS_CLI;
    $this->IS_HTML = \str_contains(($_SERVER['HTTP_ACCEPT'] ?? ''),'text/html');
    $this->ROOT_DIR = FW__ROOT_DIR;
    $this->ROOT_URL = 
        ($_SERVER["REQUEST_SCHEME"] ?? ((\strtolower(($_SERVER['HTTPS'] ?? 'off') ?: 'off') === 'off') ? 'http' : 'https'))
        .'://'
        .($_SERVER["HTTP_HOST"] ?? null ?: $_SERVER["FW__ROOT_DOM"] ?? null ?: '???.???')
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
            ($file = ($this->FN->FILE_IN)($TSP_LIST, $p = \ltrim($this->REQ->RURP,'/')))
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