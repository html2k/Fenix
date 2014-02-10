<?
    session_start();

    define('system_static', true);
    define('connect_to_db', true);
    define('sys', root.'/fenix');

    require_once sys . '/manifest.php';
    $config = $manifest['defConfig'];

    require_once sys . '/requerid.php';



    if(isset($_POST['addDBConfig'])){
        $config = $_POST['db'];
        $db = new Base($config);
        if($db->isConnect()){
            $_SESSION['dbConfig'] = $config;
            echo 1;
        }else{
            echo 0;
        }
        die;
    }

    if(isset($_POST['saveConfig'])){

        $def = $manifest['defConfig'];
        $dbConfig = $_SESSION['dbConfig'];

        $config = array();
        $config['db'] = array();
        foreach($def['db'] as $k => $v){
            if(isset($dbConfig[$k]) && $dbConfig[$k] !== ''){
                $config['db'][$k] = $dbConfig[$k];
            }else{
                $config['db'][$k] = $v;
            }
        }

        $config['project_name'] = $_POST['project_name'];
        $config['templating'] = $_POST['templating'];
        $config['lang'] = $def['lang'];
        $config['folder'] = $def['folder'];

        $db = new Base($config['db']);

        if($db->isConnect()){

            $login = trim($_POST['user']['login']);
            $pass = hashGenerate(strtolower(trim($_POST['user']['pass'])));

            foreach($manifest['baseCollection'] as $k => $v){
                $db->createCollection(array(
                    'name' => $GLOB['namespace'][$k],
                    'row' => $v
                ));
            }

            $find = $db->find($GLOB['namespace']['user'], array('login' => $login, 'pass' => $pass));
            if(count($find)){
                $db->update($GLOB['namespace']['user'], array('access' => 0), array('id' => $find[0]['id']));
            }else{
                $db->insert($GLOB['namespace']['user'], array('login' => $login, 'pass' => $pass, 'access' => 0));
            }

            $io->create_file(root.'/config.php');
            $io->write(root.'/config.php', '<? return $config = ' . $io->arrayToString($config) .'; ?>');

            $folderFrom = root.'/install/templating/'.$config['templating'] . '/';
            $folderTo = root . '/' . $config['folder']['template'] . '/';

            if(!is_dir($folderTo))
                $io->create_dir($folderTo);

            $dir = $io->tree($folderFrom);


            foreach($dir['dir'] as $v){
                $folder = str_replace($folderFrom, '', $v);

                if(!is_dir($folderTo . $folder))
                    $io->create_dir($folderTo . $folder);
            }

            foreach($dir['file'] as $v){
                $file = explode('/', $v);

                $file = str_replace($folderFrom, '', $v);

                if(!file_exists($folderTo . $file)){
                    $io->copy($v, $folderTo . $file);
                }
            }

            load_url();
        }else{
            load_url();
        }
    }


    require_once 'main.html';