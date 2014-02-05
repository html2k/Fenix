<?
    error_reporting(E_ALL);
    try {
        define('root', $_SERVER['DOCUMENT_ROOT']);

        if(file_exists('install/index.php'))
            require_once 'install/index.php';
        
        // Config
        require_once 'config.php';
        require_once $config['folder']['sys'] . '/function.php';
        require_once $config['folder']['sys'] . '/manifest.php';


        // Class
        req($config, '/class/class_translate.php');
        req($config, '/class/class_convert_schem.php');
        

        $GLOB = array();
        $GLOB['namespace'] = array();
        foreach($manifest['baseCollection'] as $k => $v){
            $GLOB['namespace'][$k] = $config['db']['sys_namespace'] . $k;
        }

        
        if(!req($config, '/class/db/' . $config['db']['type'] . '.php'))
            req($config, '/class/db/mysql.php');
        
        req($config, '/class/class_templating.php');
        
        $db = new Templating($config['db'], $GLOB['namespace']);

        // Path
        $GLOB['path'] = array();
        $url = rawurldecode($_SERVER['REQUEST_URI']);
        if($url[0] == '/') $url = substr($url, 1);
        if(substr($url, -1) == '/') $url = substr ($url, 0, -1);
        if($url !== false && count($url)){
            $url = explode('/', $url);
            $parentId = false;
            foreach($url as $v){
                if($v[0] == '?') continue;

                $queryParem = array();
                if(is_numeric($v)){
                    $queryParem['id'] = $v;
                }else{
                    $queryParem['chpu'] = $v;
                }
                if($parentId !== false)
                    $queryParem['parent'] = $parentId;
                
                $find = $db->getList($queryParem);

                if(count($find) > 0){
                    $parentId = $find[0]['id'];
                    $GLOB['path'][] = $find[0];
                }else{
                    throw new Exception('Not found', 404);
                    break;
                }
            }
        }

        
        //Self object and Template list
        $GLOB['template'] = array();
        if(count($GLOB['path']) > 0){
            $GLOB['self_object'] = end($GLOB['path']);
            if($GLOB['self_object']['marker'] !== 0){
                $marker = $db->find($GLOB['namespace']['marker'], array('id' => $GLOB['self_object']['marker']));
                $marker = explode(',', $marker[0]['template_id']);
                foreach($marker as $v){
                    $template = $db->find($GLOB['namespace']['template'], array('id' => $v));
                    $GLOB['template'][] = $template[0]['name'];
                }
            }
        }else{
            $GLOB['template'][] = 'home';
        }
        
        // Templating
        $templating = trim(strtolower($config['templating']));
        switch ($templating){
            case 'scooby':
                require_once $config['folder']['sys'] . '/templating/' . $templating . '/' . $templating . '.php';
                $scooby = new Scooby(array(
                    'folder' => $config['folder']['template'],
                    'cache' => ''
                ));

                $render = array();

                
                foreach($GLOB['path'] as $v){
                    $item = $scooby->append(false, 'path');
                    $scooby->append($item, 'id', $v['id']);
                    $scooby->append($item, 'url', $db->getId($v));
                    if(isset($GLOB['data']['name'])) $scooby->append($item, 'name', $GLOB['data']['name']);
                }
                
                
                $render[] = '/main.xsl';
                $GLOB['head'] = $scooby->append(false, 'head');
                $GLOB['body'] = $scooby->append(false, 'body');

                $mainPHP = $config['folder']['template'] . '/main.php';
                if(file_exists($mainPHP))
                    require_once $mainPHP;

                foreach($GLOB['template'] as $v){
                    $filePHP = $config['folder']['template'] . '/php/'.$v . '.php';
                    if(file_exists($filePHP))
                        require_once $filePHP;
                    $GLOB[$v] = $scooby->append($GLOB['body'], $v);
                    $render[] = '/xsl/'.$v . '.xsl';
                }
                
                if(isset($_GET['show']) && $_GET['show'] == 'xml'){
                    header('Content-Type: text/xml');
                    echo $scooby->showXML();
                }else if(isset($_GET['show']) && $_GET['show'] == 'xsl'){
                    header('Content-Type: text/xml');
                    echo $scooby->render($render, true);
                }else if(isset($_GET['show']) && $_GET['show'] == 'xslt'){
                    header('Content-Type: text/xslt');
                    echo $scooby->render($render, true);
                }else if(isset($_GET['show']) && $_GET['show'] == 'glob'){
                    header('Content-Type: text/html');
                    debug($GLOB);
                }else{
                    header('Content-Type: text/html; charset=utf-8');
                    echo $scooby->render($render);
                }

            break;

            case 'twig':

                require_once $config['folder']['sys'] . '/templating/Twig/Autoloader.php';
                Twig_Autoloader::register();

                $loader = new Twig_Loader_Filesystem(root . '/template/');
                $twig = new Twig_Environment($loader, array(
                    //'cache' => $config['cache']
                ));

                require_once $config['folder']['template'] . '/main.php';
                $render = '';
                foreach($GLOB['template'] as $v){
                    $filePHP = $config['folder']['template'] . '/php/'.$v . '.php';
                    $fileHTML = 'twig/' . $v . '.twig';
                    
                    if(file_exists($filePHP)){
                        require_once $filePHP;
                        $render .= $twig->render($fileHTML, $GLOB);
                    }else{
                        throw new Exception("Error Processing Request", 404);
                        
                    }
                }
                if(isset($_GET['show']) && $_GET['show'] == 'glob'){
                    header('Content-Type: text/html');
                    debug($GLOB);
                }else{
                    echo $render;
                }
                
            break;

            case 'tpl':
                
                require_once $config['folder']['sys'] . '/templating/' . $templating . '/' . $templating . '.php';
                $tpl = new TPL($db);
                
                require_once $config['folder']['template'] . '/main.php';
                
                foreach($GLOB['template'] as $v){
                    $filePHP = $config['folder']['template'] . '/php/'.$v . '.php';
                    if(file_exists($filePHP))
                        require_once $filePHP;
                }
                
                require_once $config['folder']['template'] . '/main.html';
            break;
        }
    } catch (Exception $e) {
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