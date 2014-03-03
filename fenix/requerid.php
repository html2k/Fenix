<?
    require_once sys.'/manifest.php';
    // Alias var
    $GLOB = array();
    $GLOB['namespace'] = array();
    foreach($manifest['baseCollection'] as $k => $v){
        $GLOB['namespace'][$k] = $config['db']['sys_namespace'] . $k;
    }

    // Function
    require_once root . '/' . $config['folder']['sys'] . '/function.php';

    // Lang
    if(file_exists('lang/'.$config['lang'].'.php'))
        require_once 'lang/'.$config['lang'].'.php';
    else
        require_once 'lang/ru.php';

    // Class
    req($config, '/class/compress/cssmin.php');
    req($config, '/class/compress/jsmin.php');
    req($config, '/class/class_io.php');
    req($config, '/class/class_compress_static.php');
    req($config, '/class/class_extension.php');
    req($config, '/class/class_translate.php');
    req($config, '/class/class_convert_schem.php');

    if(!req($config, '/class/db/' . $config['db']['type'] . '.php'))
        req($config, '/class/db/mysql.php');

    $io = new IO();
    $static = new CompressStatic(sys.'/template/compress_static/', 'app', sys.'/');

    if(connect_to_db){
        $db = new Base($config['db']);
        $Extension = new Extension($GLOB['namespace'], $config, $db, $io, $static);
    }

    //->Static
    if(system_static){

        if(defined('LESS') && file_exists(LESS)){
            require_once LESS;
            $less = new lessc;
        }

        $static->addFile(sys.'/template/js/lib.js');
        $static->addFile(sys.'/template/js/datepicker.js');
        $static->addFile(sys.'/template/js/main.js');
        $static->addFile(sys.'/template/js/search.js');
        $static->addFile(sys.'/template/js/struct.js');

        $static->addFile(sys.'/template/css/reset.css');
        $static->addFile(sys.'/template/font/fontello.css', false);
        $static->addFile(sys.'/template/font/animation.css', false);
        $static->addFile(sys.'/template/css/responsive-style.css');
        $static->addFile(sys.'/template/css/style.css');
        $static->addFile(sys.'/template/css/sys.css');

        //->Static Bloks
        $static->addFile(sys.'/template/tpl/blocks/table/table.js');
        $static->addFile(sys.'/template/tpl/blocks/project/project.js');
        $static->addFile(sys.'/template/tpl/blocks/object/object.js');
        $static->addFile(sys.'/template/js/setting.js');

        $static->addFile(sys.'/template/tpl/blocks/struct/struct.css');
        $static->addFile(sys.'/template/tpl/blocks/object/object.css');
    }