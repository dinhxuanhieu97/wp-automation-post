<?php
/*
 * mu-plugin: Vá REST Authorization header bị LiteSpeed strip.
 * Tool đăng bài gửi credential qua header "X-Authorization" (không bị strip);
 * đoạn này dựng lại Authorization / PHP_AUTH_USER / PHP_AUTH_PW cho WordPress.
 * Xoá file này = trở về mặc định, không ảnh hưởng gì khác.
 */
(function () {
    $auth = '';
    foreach (array('HTTP_X_AUTHORIZATION', 'HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION') as $k) {
        if (!empty($_SERVER[$k])) { $auth = $_SERVER[$k]; break; }
    }
    if ($auth && stripos($auth, 'Basic ') === 0) {
        $_SERVER['HTTP_AUTHORIZATION'] = $auth;
        if (empty($_SERVER['PHP_AUTH_USER'])) {
            $d = base64_decode(substr($auth, 6));
            if ($d !== false && strpos($d, ':') !== false) {
                list($u, $p) = explode(':', $d, 2);
                $_SERVER['PHP_AUTH_USER'] = $u;
                $_SERVER['PHP_AUTH_PW']   = $p;
            }
        }
    }
})();
