<?php

class Session {

    public static function start() {
        session_start();
    }

    public static function set($name, $value) {
        $_SESSION[$name] = $value;
    }

    public static function get($name, $def = false) {
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        } else {
            return ($def !== false) ? $def : false;
        }
    }

    public static function del($name) {
        unset($_SESSION[$name]);
    }

    public static function destroy($restart = false) {
        session_unset();
        session_destroy();
        if ($restart) session_start();
    }

}
