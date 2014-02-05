<?
    session_start();
    $isInataled = true;
    
    if(!defined('root')) define('root', $_SERVER['DOCUMENT_ROOT']);
    
    require_once root . '/fenix/function.php';
    require_once root . '/fenix/manifest.php';
    
    require_once root . '/' . $manifest['defConfig']['folder']['sys'] . '/manifest.php';
    require_once root . '/' . $manifest['defConfig']['folder']['sys'] . '/class/class_io.php';
    
    $sys_folder = '/' . $manifest['defConfig']['folder']['sys'] . '/template/';
    $io = new IO();
    
    if(!isset($_SESSION['error'])) $_SESSION['error'] = array();
    
    if(file_exists(root.'/config.php') && file_exists(root.'/hash.php')) $isInataled = false;
    
    if($isInataled && isset($_REQUEST['action'])){
        $_SESSION['error'] = array();
                
        $newConfig = array();
        foreach($manifest['defConfig']['db'] as $k => $v){
            $newConfig['db'][$k] = isset($_POST['db'][$k]) ? $_POST['db'][$k] : $v;
        }
        foreach ($manifest['defConfig']['folder'] as $k => $v){
            $newConfig['folder'][$k] = isset($_POST['folder'][$k]) ? $_POST['folder'][$k] : $v;
        }
        $newConfig['project_name'] = isset($_POST['projectName']) ? $_POST['projectName'] : 'Test';
        $newConfig['templating'] = isset($_POST['templating']) ? $_POST['templating'] : 'tpl';
        $newConfig['lang'] = isset($_POST['lang']) ? $_POST['lang'] : 'ru';
        
        
        $config = $newConfig;
        $toConfig = "<?\n" . '$config = ' . $io->arrayToString($newConfig, "") . ';';
        
        
        req($config, '/class/class_translate.php');
        req($config, '/class/class_convert_schem.php');
        
        if(!req($config, '/class/db/' . $config['db']['type'] . '.php'))
            req($config, '/class/db/mysql.php');
        
        $db = new Base($config['db']);
        if(!$db->isConnect()){
            $_SESSION['error'][] = 'Невозможно подключить к базе данных, проверьте параметры подключения';
            load_url();
            exit();
        }
        
        

        $output = array(
            'good' => array(),
            'error' => array(),
            'warning' => array()
        );
        
        
        //-> Развертывание БД
        foreach ($manifest['baseCollection'] as $k => $v){
            $db_name = $config['db']['sys_namespace'] . $k;

            $select = $db->go(array(
                'event' => 'select',
                'from' => $db_name,
                'limit' => 1
            ));

            if($select !== false){
                $output['warning'][] = 'Коллекция ' . $db_name . ' уже существует';
                continue;
            }
            if($db->createCollection(array('name' => $db_name, 'row' => $v)))
                $output['good'][] = 'Коллекция '. $db_name . ' создана';
            else 
                $output['error'][] = 'Невозможно создать коллекцию ' . $db_name;
        }
        
        
        //->Добавление пользователя
        if(trim($_POST['root']['login']) == '' || trim($_POST['root']['pass']) == '') {
            $_SESSION['error'][] = 'Незадано имя пользователя или пароль';
            load_url();
            exit();
        }
        
        if($db->find($config['db']['sys_namespace'] . 'user', array('login' => trim($_POST['root']['login'])))){
            $_SESSION['error'][] = 'Такой пользователь уже существует';
            load_url();
            exit();
        }
        $db->insert($config['db']['sys_namespace'] . 'user', array(
            'login' => trim($_POST['root']['login']),
            'pass' => hashGenerate(strtolower(trim($_POST['root']['pass']))),
            'access' => 0
        ));
        
        
        if(file_exists(root . '/config.php'))
            unlink(root.'/config.php');

        $io->in_file(root. '/config.php', $toConfig);
        
        //-> Развертывание шаблона
        $template = root.'/'.$config['folder']['template'] . '/';
        if(is_dir($template)) $io->removeDir($template);
        
        if($io->create_dir($template, 0777)){
            $io->copy(root.'/install/templating/' . $config['templating'] . '/', $template);
        }else{
            $_SESSION['error'][] = 'Невозможно создать файлы шаблонов, проверьте пожалуйста права доступа для сайта';
        }
        
        
        
        //-> Сбор данных по файловой системе
        $tree = $io->tree(root . '/');
        unset($tree['folder']);
        $tree['hash'] = array();
        foreach($tree['file'] as $v){
            if(strpos($v, '.hg') === false && strpos($v, 'install') === false)
                $tree['hash'][$v] = $io->get_hash($v);
        }
        $tree = '<?'."\n".'$hash = ' . $io->arrayToString($tree['hash'], "") . ';';
        if(file_exists(root . '/hash.php'))unlink(root.'/hash.php');
        $io->in_file(root. '/hash.php', $tree);
        
        $_SESSION['installed'] = $output;
        load_url('/');
        die();
    }
    
    require_once 'main.html';
    
    exit();