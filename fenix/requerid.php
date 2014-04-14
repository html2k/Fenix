<?

    require_once sys.'/manifest.php';
    require_once root . '/' . $config['folder']['sys'] . '/function.php';

    if(file_exists('lang/'.$config['lang'].'.php'))
        require_once 'lang/'.$config['lang'].'.php';
    else
        require_once 'lang/ru.php';

    req($config, '/class/class_fx.php');
    req($config, '/class/compress/cssmin.php');
    req($config, '/class/compress/jsmin.php');
    req($config, '/class/class_controller_loader.php');
    req($config, '/class/class_io.php');
    req($config, '/class/class_compress_static.php');
    req($config, '/class/class_extension.php');
    req($config, '/class/class_translate.php');
    req($config, '/class/class_convert_schem.php');
    req($config, '/templating/lessphp/lessc.inc.php');
    req($config, '/class/class_less.php');
    req($config, '/class/class_glob.php');
    if(!req($config, '/class/db/' . $config['db']['type'] . '.php'))
        req($config, '/class/db/mysql.php');
    req($config, '/class/class_templating.php');


    /** Устанлвка базового пути для контроллеров */
    Fx::cLoader()->setPath(sys . '/template/controller/');

    /** Устанлвка базового пути для статики */
    Fx::cStatic()->root(sys);

    /** Добавляем конфиги в контекст приложения */
    Fx::context()->config = $config;

    /** Добавляем манифест в контекст приложения */
    Fx::context()->manifest = $manifest;

    /** Алиасы имен бд */
    Fx::context()->namespace = array();
    foreach($manifest['baseCollection'] as $k => $v){
        Fx::context()->namespace[$k] = Fx::context()->config['db']['sys_namespace'] . $k;
    }

    if(system_static){

        Fx::cStatic()->set('template/js/jq.js');
        Fx::cStatic()->set('template/js/underscore.js');
        Fx::cStatic()->set('plugin/ckeditor/ckeditor.js');
        Fx::cStatic()->set('plugin/ckeditor/config.js');
        Fx::cStatic()->set('plugin/ckeditor/adapters/jquery.js');

        Fx::cStatic()->set('template/js/lib.js');
        Fx::cStatic()->set('template/js/datepicker.js');
        Fx::cStatic()->set('template/js/main.js');
        Fx::cStatic()->set('template/js/struct.js');

        Fx::cStatic()->set('template/tpl/blocks/table/table.js');
        Fx::cStatic()->set('template/tpl/blocks/project/project.js');
        Fx::cStatic()->set('template/tpl/blocks/object/object.js');
        Fx::cStatic()->set('template/js/setting.js');

        Fx::cStatic()->set('template/css/reset.css');
        Fx::cStatic()->set('template/font/fontello.css');
        Fx::cStatic()->set('template/font/animation.css');
        Fx::cStatic()->set('template/css/responsive-style.css');
        Fx::cStatic()->set('template/css/style.css');
        Fx::cStatic()->set('template/css/sys.css');
        Fx::cStatic()->set('template/tpl/blocks/struct/struct.css');
        Fx::cStatic()->set('template/tpl/blocks/object/object.css');

    }