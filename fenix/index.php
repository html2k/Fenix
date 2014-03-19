<?
    header("Content-Type: text/html; charset=utf-8");

    error_reporting(E_ALL);
    session_start();

    define('system_static', true);
    define('connect_to_db', true);
    define('root', $_SERVER['DOCUMENT_ROOT']);

    require_once root . '/config.php';
    define('sys', root . '/' . $config['folder']['sys']);
    define('LESS', sys . '/templating/lessphp/lessc.inc.php');

    require_once sys . '/requerid.php';

    // Logic
    if(isset($_REQUEST['action'])) 
        require_once 'action.php';
    
    
    if(!isset($_SESSION['user']))
        require_once 'template/connect.php';
    

    $_SESSION['back_param'] = $_REQUEST;
    
    // Template
    $mode = isset($_GET['mode']) ? $_GET['mode'] : 'home';
    Fx::app()->mode = $mode;

    $php = 'template/php/' . $mode . '.php';
    $tpl = 'template/tpl/' . $mode . '.html';

    Fx::ext()->compile();
    require_once 'template/main.php';


    if(isset($_GET['extension']) && $_GET['extension'] !== ''){
        $mode = $_GET['extension'];
    }

    $extUrl = '';

    $ext = Fx::ext()->get('page');
    Fx::app()->extensionMenu = array();

    if(count($ext)){
        foreach ($ext as $v) {
            Fx::app()->extensionMenu[$v['param']['code']] = $v['param']['name'];
            if($mode === $v['param']['code']){
                $extUrl = $v['url'] . $v['param']['page'];
            }
        }
    }

    if(isset($_GET['extension']) && $_GET['extension'] !== '' && $extUrl !== ''){
        $php = $extUrl . '.php';
        $tpl = $extUrl . '.html';
    }

    define('mode', $mode);


    try{
        if(file_exists($php)) require_once $php;
        ob_start();
            if(file_exists($tpl)) require_once $tpl;
            Fx::app()->content = ob_get_contents();
        ob_end_clean();
        require_once 'template/main.html';
    }catch (Exception $e){
        Fx::app()->leftMenu = '';

        $param = array(
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        );

        if($e->getCode() === 403){
            $param['text'] = 'Доступ запрещен';
        }
        if($e->getCode() === 404){
            $param['text'] = 'Такая страница не существует';
        }

        Fx::app()->content = $io->buffer(sys.'/template/error.html', $param);
        require_once 'template/main.html';
    }
    