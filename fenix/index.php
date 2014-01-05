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
    req($config, '/class/class_io.php');
    req($config, '/class/class_extension.php');
    req($config, '/class/class_translate.php');
    req($config, '/class/class_convert_schem.php');
    
    if(!req($config, '/class/db/' . $config['db']['type'] . '.php'))
        req($config, '/class/db/mysql.php');
    
    $db = new Base($config['db']);
    $io = new IO();
    $Extension = new Extension($config);
    
    // Logic
    if(isset($_REQUEST['action'])) 
        require_once 'action.php';
    
    
    if(!isset($_SESSION['user']))
        require_once 'template/connect.php';
    
    
    $_SESSION['back_param'] = $_REQUEST;
    
    // Template
    $mode = isset($_GET['mode']) ? $_GET['mode'] : 'home';
    define('mode', $mode);
    $GLOB['mode'] = $mode;

    $php = 'template/php/' . $mode . '.php';
    $tpl = 'template/tpl/' . $mode . '.html';
    
    require_once 'template/main.php';
    if(file_exists($php)) require_once $php;
    ob_start();
        if(file_exists($tpl)) require_once $tpl;
        $GLOB['content'] = ob_get_contents();
    ob_end_clean();
    require_once 'template/main.html';
    