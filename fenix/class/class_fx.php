<?
class Fx{

    protected static  $_app;
    public static function app(){
        if(null == self::$_app){
            self::$_app = (object) array();
        }
        return self::$_app;
    }

    protected static $_db;
    public static function db(){
        if(null == self::$_db){
            self::$_db = new Templating(self::app()->config['db'], self::app()->namespace);
        }
        return self::$_db;
    }

    protected static $_io;
    public static function io(){
        if(null == self::$_io){
            self::$_io = new IO;
        }
        return self::$_io;
    }

    protected static $_less;
    public static function less(){
        if(null == self::$_less){
            self::$_less = new Less(self::io());
        }
        return self::$_less;
    }

    protected static $_extension;
    public static function ext(){
        if(null == self::$_extension){
            self::$_extension = new Fx_Extension();
        }
        return self::$_extension;
    }

}