<?php 


if(($_SESSION['--AUTH']['en'] ?? false) !== true){
    if(!($_SESSION['--AUTH']['login_in_progress'] ?? false)){
        $_SESSION['--AUTH'] = [];
        $_SESSION['--AUTH']['login_in_progress'] = 1;
        \header("Location: ". \strtok($_SERVER['REQUEST_URI'],'?'));
        exit();
    }
}
if(
    isset($_GET['--logout'])
    || isset($_GET['--signout'])
){
    $_SESSION['--AUTH'] = [];
    \header("Location: ". \strtok($_SERVER['REQUEST_URI'],'?'));
    exit();
}
\is_array($_SESSION['--AUTH'] ?? null) OR $_SESSION['--AUTH'] = [];
if($_SESSION['--AUTH']['login_in_progress'] ?? null){
    if($action = $_REQUEST['--action'] ?? null){
        if($action == 'login'){
            global $_;
            if($username = $_POST['username'] ?? false){
                $password = $_POST['password'] ?? '';
                if(
                    \class_exists($c = \_\users::class)
                    && method_exists($c, '_')
                ){
                    $user = (array) $c::_($username);
                } else if(\is_file($f = FW__DATA_DIR."/_/users/({$username})/-$.php")) {
                    $user = include $f;
                } else {
                    $users = $_['USERS'] ?? [
                        'admin' => [
                            'name' => 'Admin',
                            'password' => '`pass',
                            'panels' => ['*'],
                            'roles' => ['*'],
                        ],
                    ]; 
                    $user = $users[$username] ?? [];
                }
                if($user){
                    $pass = (($p = ($user['password'] ?? null) ?: "`")[0] == "`") 
                        ? \md5(\substr($p, 1))
                        : $p
                    ;
                    $streq__fn = function ($a, $b) {
                        //credits: https://blog.ircmaxell.com/2014/11/its-all-about-time.html
                        $ret = false;
                        if (($aL = \strlen($a)) == ($bL = \strlen($b))) {
                            $r = 0;
                            for ($i = 0; $i < $aL; $i++) {
                                $r |= (\ord($a[$i]) ^ \ord($b[$i]));
                            }
                            $ret = ($r === 0);
                        }
                        return $ret;
                    };
                    if((empty($pass) && !$password) || ($streq__fn)($pass, \md5($password ?? ''))){
                        $_SESSION['--AUTH'] = $user;
                        $_SESSION['--AUTH']['en'] = true;
                        $_SESSION['--AUTH']['username'] = $username;
                        unset($_SESSION['--AUTH']['password']);
                        $_SESSION['--FLASH']['toasts'][] = 'You have Logged in Successfully';
                    } else {
                        $_SESSION['--FLASH']['toasts'][] =  'Invalid login credentials';
                    }
                } else {
                    $_SESSION['--FLASH']['toasts'][] = 'Invalid login credentials';    
                }
            } else {
                $_SESSION['--FLASH']['toasts'][] = 'Invalid login credentials';
            } 
        } else {
            $_SESSION['--FLASH']['toasts'][] = 'Invalid login action';
        }
        while(\ob_get_level() > FW__OB_OUT){ @\ob_end_clean(); }
        \defined('_\SIG_ABORT') OR \define('_\SIG_ABORT', 0);
        \header("Location: ".\strtok($_SERVER['REQUEST_URI'],'?'));
        exit();
    } else {
        while(\ob_get_level() > FW__OB_OUT){ @\ob_end_clean(); }
        //static::abort(503, '503: Not Available: Login user interface is not available');
        $title = $_SERVER['HTTP_HOST']." | Login";
        $csrf = FW__CSRF;
        $site_url = 'Nekrit Localhost Installer';
        $toasts = \json_encode(FW__FLASH['toasts'] ?? []);
        echo <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{$title}</title>
            <style>
                *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
                body, html { width: 100vw; height: 100vh; display: flex; align-items: center; justify-content: center; font-family: Arial, sans-serif; }
                body, html { background-color: #f8f9fa; color: #212529; font-family: Arial;}
                .centered-container { width: 100%; min-width: 500px; max-width: 500px;  padding:20px; }
                .centered-container h1 { margin-bottom: 1rem; font-size: 1.5rem; font-weight: 500; text-align: center; }
                .form-label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #212529; }
                .form-control { display: block; width: 100%; padding: 0.5rem 0.75rem; font-size: 1rem; font-weight: 400; line-height: 1.5; }
                .form-control { color: #212529; background-color: #ffffff; background-clip: padding-box; border: 1px solid #ced4da; border-radius: 0.375rem; }
                .form-control { box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1); transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out; }
                .form-control:focus { border-color: #86b7fe; outline: 0; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25); }
                .btn-login { display: inline-block; font-weight: 400; text-align: center; vertical-align: middle; }
                .btn-login { cursor: pointer; user-select: none; padding: 0.375rem 0.75rem; font-size: 1rem; line-height: 1.5;}
                .btn-login { color: #0d6efd; background-color: transparent; background-clip: padding-box; border: 1px solid #0d6efd; border-radius: 0.375rem; }
                .btn-login { transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out; }
                .btn-login:hover { color: #ffffff; background-color: #0d6efd; border-color: #0d6efd; }
                .btn-login:focus { color: #ffffff; background-color: #0d6efd; border-color: #0d6efd; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25); }
                .btn-login:active { color: #ffffff; background-color: #0a58ca; border-color: #0a58ca; }
                .btn-login:disabled { color: #0d6efd; background-color: transparent; border-color: #0d6efd; opacity: 0.65; }                            
                .mb-3 { margin-bottom: 1rem; }
                .text-center { text-align: center; }
                .text-end { text-align: right; }
                #toast-container { position: fixed; top: 20px; right: 20px; z-index: 1000; display: none; }
                .toast { display: flex; align-items: center; background-color: #333; color: #fff; padding: 12px 20px; margin-bottom: 10px; }
                .toast { border-radius: 5px; opacity: 0; transition: opacity 0.5s ease, transform 0.5s ease; transform: translateY(-20px); }
                .toast.show { opacity: 1; transform: translateY(0); }
                .toast.hide { opacity: 0; transform: translateY(-20px); }                            
            </style>
        </head>
        <body>
            <div id="toast-container"></div>
            <div class="centered-container">
                <div class="text-center mb-3">
                    <h1>User Login</h1>
                    <p style="font-family: monospace">{$site_url}</p>
                </div>
                <form action="" id="form-auth" method="POST">
                    <input type="hidden" name="--action" value="login">
                    <input type="hidden" name="--auth" value="login">
                    <input type="hidden" name="--csrf" value="{$csrf}">
                    <div class="mb-3">
                        <label for="id-username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="id-username" name="username" aria-describedby="id-username-help">
                    </div>
                    <div class="mb-3">
                        <label for="id-password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="id-password" name="password" autocomplete="new-password">
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn-login">Login</button>
                    </div>
                </form>
            </div>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    var toasts = {$toasts};
                    if(toasts){
                        // Make the toast container visible
                        const toastContainer = document.getElementById('toast-container');
                        toastContainer.style.display = 'block';
                        
                        toasts.forEach((message) => {
                            if(message){
                                // Create a new toast element
                                const toast = document.createElement('div');
                                toast.classList.add('toast');
                                toast.textContent = message;

                                // Append to the toast container
                                toastContainer.appendChild(toast);

                                // Show the toast with animation
                                setTimeout(() => toast.classList.add('show'), 100);

                                // Hide the toast and container after 3 seconds
                                setTimeout(() => {
                                    toast.classList.add('hide');
                                    toast.addEventListener('transitionend', () => {
                                    toast.remove();
                                    if (toastContainer.children.length === 0) {
                                        toastContainer.style.display = 'none';
                                    }
                                    });
                                }, 3000);
                            }
                        });
                    }
                });
            </script>
        </body>
        </html>
        HTML;
        exit();
    }
}