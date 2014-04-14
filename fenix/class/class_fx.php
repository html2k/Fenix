<?

/**
 * Синглтоны, базовый класс приложения
 * Class Fx
 */
class Fx{


    protected static  $_context;

    /**
     * @return object
     */
    public static function context(){
        if(null == self::$_context){
            self::$_context = (object) array();
        }
        return self::$_context;
    }


    protected static $_db;

    /**
     * @return Templating
     */
    public static function db(){
        if(null == self::$_db){
            self::$_db = new Templating(self::context()->config['db'], self::context()->namespace);
        }
        return self::$_db;
    }


    protected static $_io;

    /**
     * @return IO
     */
    public static function io(){
        if(null == self::$_io){
            self::$_io = new IO;
        }
        return self::$_io;
    }


    protected static $_less;

    /**
     * @return Less
     */
    public static function less(){
        if(null == self::$_less){
            self::$_less = new Less(self::io());
        }
        return self::$_less;
    }


    protected static $_extension;

    /**
     * @return Fx_Extension
     */
    public static function ext(){
        if(null == self::$_extension){
            self::$_extension = new Fx_Extension();
        }
        return self::$_extension;
    }


    protected  static $_StaticCompressor;

    /**
     * @return StaticCompressor
     */
    public  static function cStatic(){
        if(null == self::$_StaticCompressor){
            self::$_StaticCompressor = new StaticCompressor();
        }
        return self::$_StaticCompressor;
    }


    protected static $_controllerLoadre;

    /**
     * @return ControllerLoader
     */
    public static function cLoader(){
        if(null == self::$_controllerLoadre){
            self::$_controllerLoadre = new ControllerLoader();
        }
        return self::$_controllerLoadre;
    }

    protected static $_action;

    /**
     * @return Action
     */
    public static function action(){
        if(null == self::$_action){
            self::$_action = new Action();
        }
        return self::$_action;
    }
}