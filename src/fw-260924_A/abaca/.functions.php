<?php
/*
    Removal of legacy functions:
    A standard global function call is faster than a class static method call in PHP
    When you call a global function like my_function(), the PHP Zend Engine performs a quick, single-step lookup in its 
    global function table.
    
    When you call a static method like MyClass::myMethod(), PHP has to perform a multi-step process:
        • It must resolve the class name (MyClass) and check if the class exists or trigger autoloading.
        • It must look up the method inside that specific class structure.
        • It must check for inheritance visibility (public/protected) and handle Late Static Binding (static:: vs self::)
    While global functions win on a strictly technical level, the difference is measured in fractions of a microsecond 
    per call.
    Unless when building a high-frequency trading algorithm or a core loop executing millions of times per request, 
    it is better to choose organization over micro-optimization. Grouping methods logically inside classes as static 
    utilities (e.g., a Math::uuid() helper) is widely preferred for clean, discoverable code
*/
# ######################################################################################################################
#region ENV
namespace { if(!\function_exists(fw::class)){ function fw(){
    static $i; return $i ?? ($i = (function(){
        $fw = $GLOBALS['FW'];
        unset($GLOBALS['FW']);
        return $fw;
    })());
}}}
namespace _\i\fw { if(!\function_exists(env::class)){ function env(string $n){
    static $E = [];
    if(!\array_key_exists($n, $E)){
        $k = "FW_{$n}";
        $E[$n] = 
            $_ENV[$n]
            ?? (\defined($k) ? \constant($k) : null)
            ?? ((($r = \getenv($k)) !== false) ? $r : null)
            ?? $_SERVER[$k]
            ?? $_SERVER["REDIRECT_{$k}"]
            ?? $_SERVER["REDIRECT_REDIRECT_{$k}"]
            ?? null
        ;
    }
    return $E[$n];
}}}
#endregion
# ######################################################################################################################
#region SLASHES
namespace _ { if(!\function_exists(p::class)){ function p(string $expr, int $levels = 0){
    return \str_replace('\\','/', $levels ? \dirname($expr , $levels) : $expr);
}}}
namespace _ { if(!\function_exists(slashes::class)){ function slashes(string $p, string ...$px) {
    // old algo looks clean but is slower
    //return (\func_num_args() == 1)
    //    ? \strtr($p,'\\','/')
    //    : \strtr(\implode('/',\func_get_args()),'\\','/')
    //;
    
    //first one fastpath no trim
    if (!$px) {
        return \strtr($p, '\\', '/');
    }
    $out = \trim($p, "\\/ \t\n\r\0\x0B");
    foreach ($px as $seg) {
        $seg = \trim((string)$seg, "/\\ \t\n\r\0\x0B");
        if ($seg === '') {
            continue; // skip empty
        }        
        $out .= '/' . $seg;
    }
    return \strtr($out, '\\', '/');    
}}}
namespace _ { if(!\function_exists(backslashes::class)){ function backslashes(string $p, string ...$px) {
    // old algo looks clean but is slower
    // return (\func_num_args() == 1)
    //     ? \strtr($p,'/','\\')
    //     : \strtr(\implode('\\',\func_get_args()),'/','\\')
    // ;
    
    //first one fastpath no trim
    if (!$px) {
        return \strtr($p, '/', '\\');
    }
    $out = \trim($p, "\\/ \t\n\r\0\x0B");
    foreach ($px as $seg) {
        $seg = \trim((string)$seg, "\\/ \t\n\r\0\x0B");
        if ($seg === '') {
            continue; // skip empty
        }        
        $out .= '\\' . $seg;
    }
    return \strtr($out, '/', '\\');    
}}}
namespace _ { if(!\function_exists(typename::class)){ function typename(string|object $p, string ...$px) {
    return (\func_num_args() == 1)
        ? (\is_object($p) 
            ? \get_class($p) 
            : \strtr($p,'/','\\')
        )
        : (\is_object($p) 
            ? \strtr(\implode('\\',[\get_class($p), ...$px]),'/','\\') 
            : \strtr(\implode('\\',\func_get_args()),'/','\\')
        )
    ;
}}}
namespace _ { if(!\function_exists(typepath::class)){ function typepath(string|object $p, string ...$px) {
    return (\func_num_args() == 1)
        ? (\is_object($p) 
            ? \get_class($p) 
            : \strtr($p,'\\','/')
        )
        : (\is_object($p) 
            ? \strtr(\implode('/',[\get_class($p), ...$px]),'\\','/') 
            : \strtr(\implode('/',\func_get_args()),'\\','/')
        )
    ;
}}}
#endregion
# ######################################################################################################################
#region PATH
namespace _\i\path { if(!\function_exists(is_rooted::class)){ function is_rooted($expr){
    return ($expr[0]??'')=='/' || ($expr[1]??'')==':';
}}}
#endregion
# ######################################################################################################################
#region DX
namespace _\i\dx { if(!\function_exists(get_caller::class)){ function get_caller($offset = 0){
    $backtrace = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 2 + $offset);
    // stupid stuff ... name of this function but file is of the previous function //BP
    // Index 0: get_caller - but file point to before this !!!!!!
    // Index 1: my_function
    // Index 2: caller of my_function (this is what we want)
    return $backtrace[1 + $offset] ?? null;
}}}
namespace _\i\dx { if(!\function_exists(path_here::class)){ function path_here(string $path) {
    if($file = \_\get_caller(-1)['file'] ?? null){
        return \dirname($file)."/{$path}";
    }
}}}
#endregion
# ######################################################################################################################
#region STR
namespace _\i\str { if(!\function_exists(render::class)){ function render(mixed $expr, bool|array $params = [], bool $texate = false){
    if(\is_bool($params)){
        $texate = $params;
        $params = [];
    }
    if(!$expr && $expr != 0){
        if($texate){
            return '';
        } else {
            echo '';
            return;
        }
    } else if(\is_scalar($expr)){
        if($texate){
            return $expr;
        } else {
            echo $expr;
            return;
        }
    }
    
    try{ 
        $texate AND \ob_start();
        if(\is_array($expr) && ($params[0] ?? null) === true){
            foreach($expr as $v){
                if(\is_scalar($v)){
                    echo $v;
                }
            }
        } else if($expr instanceof \closure) {
            \is_array($params) ? ($expr)(...$params) : ($expr)();
        } else if($expr instanceof \SplFileInfo) {
            include $expr;
        } else if($expr instanceof \_\i\prt__i){
            \is_array($params) ? $expr->prt(...$params) : ($expr)();
        } else {
            echo '<pre>'.\json_encode($expr, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).'<pre>';
        }
    } finally { 
        if($texate){
            $d = \ob_get_contents(); \ob_end_clean();  
        }  
    }
    if($texate){
        return $d; //* if returned in finally exceptions get lost
    }
}}}
namespace _\i\str { if(!\function_exists(texate::class)){ function texate(mixed $expr, array $params = []){
    return \_\render($expr, $params, true);
}}}
#endregion
# ######################################################################################################################
#region ARRAY
namespace { if(!\function_exists(array_patch_recursive::class)){ function array_patch_recursive($array,...$patches){
    static $patcher;
    if(!$patcher){
        $patcher = function(&$array, $patch) use(&$patcher){
            foreach($patch as $k => $v){
                if(isset($array[$k])){
                    if(\is_array($v) && \is_array($array[$k]) && $k[0] != '.'){
                        ($patcher)($array[$k], $v);
                    } else {
                        $array[$k] = $patch[$k];
                    }
                } else {
                    $array[$k] = $patch[$k];
                }
            }
        };
    }
    foreach($patches as $patch){
        ($patcher)($array, $patch);
    }
    return $array;
}}}
namespace { if(!\function_exists(array_purge_recursive::class)){ function array_purge_recursive(&$array, $eq = null){
    foreach($array as $k => &$v){
        if(\is_array($v)){
            \array_purge_recursive($v,$eq);
        } else if($v === $eq){
            unset($array[$k]);
        }
    }
}}}
namespace { if(!\function_exists(array_purge::class)){ function array_purge(&$array, $eq = null){
    foreach($array as $k => &$v){
        if($v === $eq){
            unset($array[$k]);
        }
    }
}}}
#endregion
# ######################################################################################################################
#region DOB
namespace _\i\data { if(!\function_exists(dob::class)){ function dob(string $path, $value = null) {
    if ($path === '' || strpos($path, "\0") !== false) {
        throw new \InvalidArgumentException('Invalid file path.');
    }

    $absolute = $path[0] === '/'
        || $path[0] === '\\'
        || preg_match('~^[A-Za-z]:[\\\\/]~', $path);

    $f = $absolute ? $path : \FW\__DATA_DIR . "/{$path}";

    // WRITE
    if (func_num_args() > 1) {
        switch (strtolower(pathinfo($f, PATHINFO_EXTENSION))) {
            case 'pob':
                $contents = serialize($value);
                break;

            case 'json':
                $contents = json_encode(
                    $value,
                    JSON_PRETTY_PRINT
                    | JSON_UNESCAPED_SLASHES
                    | JSON_THROW_ON_ERROR
                );
                break;

            case 'php':
                $contents = "<?php\nreturn " . var_export($value, true) . ";\n";
                break;

            case 'txt':
                if (!is_scalar($value)) {
                    throw new \InvalidArgumentException(
                        'TXT files require a scalar value.'
                    );
                }
                $contents = (string) $value;
                break;

            default:
                throw new \InvalidArgumentException("Unsupported format: {$f}");
        }

        $d = dirname($f);

        if (!is_dir($d) && !@mkdir($d, 0777, true) && !is_dir($d)) {
            throw new \RuntimeException("Could not create directory: {$d}");
        }

        $handle = fopen($f, 'cb');

        if ($handle === false) {
            throw new \RuntimeException("Could not open for writing: {$f}");
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                throw new \RuntimeException("Could not lock for writing: {$f}");
            }

            if (!ftruncate($handle, 0) || !rewind($handle)) {
                throw new \RuntimeException("Could not reset file: {$f}");
            }

            $length = strlen($contents);
            $offset = 0;

            while ($offset < $length) {
                $written = fwrite($handle, substr($contents, $offset));

                if ($written === false || $written === 0) {
                    throw new \RuntimeException("Could not complete write: {$f}");
                }

                $offset += $written;
            }

            if (!fflush($handle)) {
                throw new \RuntimeException("Could not flush file: {$f}");
            }

            if (
                strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'php'
                && function_exists('opcache_invalidate')
            ) {
                opcache_invalidate($f, true);
            }

            return true;
        } finally {
            fclose($handle); // Also releases the lock.
        }
    } else {
        // READ
        if (!is_file($f)) {
            if (
                $absolute
                || !($f = stream_resolve_include_path($path))
                || !is_file($f)
            ) {
                return null;
            }
        }

        $handle = fopen($f, 'rb');

        if ($handle === false) {
            throw new \RuntimeException("Could not open for reading: {$f}");
        }

        try {
            if (!flock($handle, LOCK_SH)) {
                throw new \RuntimeException("Could not lock for reading: {$f}");
            }

            switch (strtolower(pathinfo($f, PATHINFO_EXTENSION))) {
                case 'php':
                    return (static function ($f) {
                        return include $f;
                    })($f);

                case 'pob':
                case 'json':
                case 'txt':
                    $contents = stream_get_contents($handle);

                    if ($contents === false) {
                        throw new \RuntimeException("Could not read: {$f}");
                    }
                    break;

                default:
                    throw new \InvalidArgumentException("Unsupported format: {$f}");
            }
        } finally {
            fclose($handle);
        }

        switch (strtolower(pathinfo($f, PATHINFO_EXTENSION))) {
            case 'pob':
                return unserialize($contents);

            case 'json':
                return json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

            case 'txt':
                return $contents;
        }
    }
}}}
namespace _\i\data { if(!\function_exists(user::class)){ function user(string $path, $value = null){
    $username = $_SESSION['--USER']['username'] ?? 'user';
    $file = \FW__DATA_DIR."/users/{$username}/{$path}";
    return (\func_num_args() > 1)
        ? \_\i\data\dob($file, $value)
        : \_\i\data\dob($file)
    ;
}}}
namespace _\i\data { if(!\function_exists(auth::class)){ function auth(string $username, $value = null){
    $file = \FW__DATA_DIR."/users/{$username}/-$.php";
    return (\func_num_args() > 1)
        ? \_\i\data\dob($file, $value)
        : \_\i\data\dob($file)
    ;
}}}
namespace _\i\data { if(!\function_exists(db_config::class)){ function db_config(int $index = 0, $value = null){
    $file = \FW__DATA_DIR."/db".($index ? "-{$index}" : "")."-$.php";
    return (\func_num_args() > 1)
        ? \_\i\data\dob($file, $value)
        : \_\i\data\dob($file)
    ;
}}}

