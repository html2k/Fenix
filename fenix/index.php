<?
    error_reporting(E_ALL);
    
    session_start();
    $GLOB = array();
    
    define('root', $_SERVER['DOCUMENT_ROOT']);
    require_once root . '/config.php';
    define('sys', root . '/' . $config['folder']['sys']);
    require_once 'manifest.php';
    
    // Alias var
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
    
    $db = new Base($config['db']);
    $io = new IO();
    $static = new CompressStatic(sys.'/template/compress_static/', 'app', sys.'/');

    $Extension = new Extension($GLOB['namespace'], $config, $db, $io, $static);
    
    // Logic
    if(isset($_REQUEST['action'])) 
        require_once 'action.php';
    
    
    if(!isset($_SESSION['user']))
        require_once 'template/connect.php';
    

    $_SESSION['back_param'] = $_REQUEST;
    
    // Template
    $mode = isset($_GET['mode']) ? $_GET['mode'] : 'home';
    $GLOB['mode'] = $mode;

    $php = 'template/php/' . $mode . '.php';
    $tpl = 'template/tpl/' . $mode . '.html';

    require_once 'template/main.php';
    $Extension->compile();

    if(isset($_GET['extension']) && $_GET['extension'] !== ''){
        $mode = $_GET['extension'];
    }

    $extensions = $Extension->get('page');
    $extUrl = '';
    $GLOB['extension-menu'] = array();

    foreach ($extensions as $ext) {
        $GLOB['extension-menu'][$ext['option']['code']] = $ext['option']['name'];
        if($mode === $ext['option']['code']){
            $extUrl = $ext['url'] . $ext['option']['page'];
        }
    }

    if(isset($_GET['extension']) && $_GET['extension'] !== '' && $extUrl !== ''){
        $php = $extUrl . '.php';
        $tpl = $extUrl . '.html';
    }
    
    define('mode', $mode);
    if(file_exists($php)) require_once $php;
    ob_start();
        if(file_exists($tpl)) require_once $tpl;
        $GLOB['content'] = ob_get_contents();
    ob_end_clean();
    require_once 'template/main.html';
    