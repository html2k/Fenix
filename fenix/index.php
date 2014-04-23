<?
    header("Content-Type: text/html; charset=utf-8");

    error_reporting(E_ALL);
    session_start();

    define('system_static', true);
    define('connect_to_db', true);
    define('root', $_SERVER['DOCUMENT_ROOT']);

    /** Настройки системы */
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
    Fx::context()->mode = $mode;

    $php = 'template/php/' . $mode . '.php';
    $tpl = 'template/tpl/' . $mode . '.html';

    Fx::ext()->compile();
    require_once 'template/main.php';

    if(isset($_GET['extension']) && $_GET['extension'] !== ''){
        $mode = $_GET['extension'];
    }

    define('mode', $mode);

    $extView = '';
    $extController = '';

    /** Загружаем расширения для страниц */
    $ext = Fx::ext()->get('page');
    Fx::context()->extensionMenu = array();
    if(count($ext)){
        foreach ($ext as $v) {
            if(isset($v['param']['name'])){
                Fx::context()->extensionMenu[$v['param']['code']] = $v['param']['name'];
            }
            if($mode === $v['param']['code']){

                if(isset($v['param']['view'])){
                    $extView = $v['url'] . $v['param']['view'];
                }

                if(isset($v['param']['controller'])){
                    $extController = $v['url'] . $v['param']['controller'];
                }

                if(isset($v['param']['static']) && is_array($v['param']['static'])){
                    $extPath = str_replace(root, '', $v['url']);
                    foreach($v['param']['static'] as $e){
                        Fx::cStatic()->set('..' . $extPath . $e, false);
                    }
                }
            }
        }
    }
    if(isset($_GET['extension']) && $_GET['extension'] !== '' && $extView !== ''){
        $php = $extController;
        $tpl = $extView;
    }

    /** Рендерим страници */
    try{

        if(file_exists($php)) require_once $php;
        ob_start();

            require_once $tpl;
            Fx::context()->content = ob_get_contents();

        ob_end_clean();


        require_once 'template/main.html';
    }catch (Exception $e){
        Fx::context()->leftMenu = '';

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

        Fx::context()->content = Fx::io()->buffer(sys.'/template/error.html', $param);
        require_once 'template/main.html';
    }
    