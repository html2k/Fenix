<?
header("Content-Type: text/html; charset=utf-8");
error_reporting(E_ALL);

try{
    define('root', $_SERVER['DOCUMENT_ROOT']);
    if(!file_exists(root . '/config.php')){
        require_once 'install/index.php';
        die;
    }

    define('system_static', false);
    define('connect_to_db', false);
    require_once 'config.php';
    define('sys', root . '/' . $config['folder']['sys']);
    require_once sys . '/requerid.php';

    Fx::context()->path = Fx::db()->path($_SERVER['REQUEST_URI']);
    if(Fx::context()->path === false) throw new Exception('Not found', 404);

    Fx::context()->template = array();
    if(count(Fx::context()->path) > 0){
        Fx::context()->self_object = end(Fx::context()->path);
        if((int) Fx::context()->self_object['marker'] !== 0){
            $marker = Fx::db()->find(Fx::context()->namespace['marker'], array('id' => Fx::context()->self_object['marker']));
            $marker = explode(',', $marker[0]['template_id']);
            foreach($marker as $v){
                $template = Fx::db()->find(Fx::context()->namespace['template'], array('id' => $v));
                Fx::context()->template[] = $template[0]['name'];
            }
        }else{
            throw new Exception('not found', 404);
        }
    }else{
        Fx::context()->template[] = 'home';
    }

    switch (trim(strtolower(Fx::context()->config['templating']))){
        case 'twig':

            require_once Fx::context()->config['folder']['sys'] . '/templating/Twig/Autoloader.php';
            Twig_Autoloader::register();

            $loader = new Twig_Loader_Filesystem(root . '/template/');
            $twig = new Twig_Environment($loader, array(

                'debug' => true
            ));
            $twig->addExtension(new Twig_Extension_Debug());

            require_once Fx::context()->config['folder']['template'] . '/main.php';
            $render = '';
            foreach(Fx::context()->template as $v){
                $filePHP = Fx::context()->config['folder']['template'] . '/php/'.$v . '.php';
                $fileHTML = 'twig/' . $v . '.twig';

                if(file_exists($filePHP)){
                    require_once $filePHP;

                    $render .= $twig->render($fileHTML, (array) Fx::context());
                }else{
                    throw new Exception("Error Processing Request", 404);

                }
            }

            if(isset($_GET['show']) && $_GET['show'] == 'glob'){
                header('Content-Type: text/html');
                debug(Fx::context());
            }else{
                echo $render;
            }

        break;
    }

}catch (Exception $e){
    switch ($e->getCode()){
        case 404:
            header("HTTP/1.0 404 Not Found");
            header("Status: 404 Not Found");
            $errorPage = '404.html';
            if(file_exists($errorPage))
                require_once $errorPage;
            else
                echo '<h1>404</h1><p>Not found</p>';
            break;

        default :
            echo $e;
            break;
    }
}