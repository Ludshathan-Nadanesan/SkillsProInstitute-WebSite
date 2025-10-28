<?php
class PathEncryptor {
    private static $key = "SkillPro@2025"; // change to a long random key
    private static $iv;

    public static function init() {
        // IV must be exactly 16 bytes
        self::$iv = substr(hash('sha256', self::$key), 0, 16);
    }

    public static function encrypt($path) {
        self::init();
        $encrypted = openssl_encrypt($path, 'AES-256-CBC', self::$key, 0, self::$iv);
        return urlencode(base64_encode($encrypted));
    }

    public static function decrypt($encrypted) {
        self::init();
        return openssl_decrypt(base64_decode(urldecode($encrypted)), 'AES-256-CBC', self::$key, 0, self::$iv);
    }
}
?>