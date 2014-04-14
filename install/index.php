<?
    session_start();

    define('system_static', true);
    define('connect_to_db', true);
    define('sys', root.'/fenix');
    define('LESS', sys . '/templating/lessphp/lessc.inc.php');

    require_once sys . '/manifest.php';
    $config = $manifest['defConfig'];

    require_once sys . '/requerid.php';



    if(isset($_POST['addDBConfig'])){
        Fx::context()->config = array();
        Fx::context()->config['db'] = $_POST['db'];
        if(Fx::db()->isConnect()){
            $_SESSION['dbConfig'] = $_POST['db'];
            echo 1;
        }else{
            echo 0;
        }
        die;
    }

    if(isset($_POST['saveConfig'])){

        $dbConfig = array(
            'db' => $_SESSION['dbConfig'],
            'project_name' => $_POST['project_name'],
            'templating' => $_POST['templating']
        );

        Fx::context()->config = array_merge(Fx::context()->config, array(
            'db' => array_merge(Fx::context()->config['db'], $_SESSION['dbConfig']),
            'project_name' => $_POST['project_name'],
            'templating' => $_POST['templating']
        ));

        if(Fx::db()->isConnect()){

            $login = trim($_POST['user']['login']);
            $pass = hashGenerate(strtolower(trim($_POST['user']['pass'])));

            foreach($manifest['baseCollection'] as $k => $v){
                Fx::db()->createCollection(array(
                    'name' => Fx::context()->namespace[$k],
                    'row' => $v
                ));
            }

            $find = Fx::db()->find(Fx::context()->namespace['user'], array('login' => $login, 'pass' => $pass));
            if(count($find)){
                Fx::db()->update(Fx::context()->namespace['user'], array('access' => 0), array('id' => $find[0]['id']));
            }else{
                Fx::db()->insert(Fx::context()->namespace['user'], array('login' => $login, 'pass' => $pass, 'access' => 0));
            }

            Fx::io()->create_file(root.'/config.php');
            Fx::io()->write(root.'/config.php', '<? return $config = ' . Fx::io()->arrayToString(Fx::context()->config) .'; ?>');

            $folderFrom = root.'/install/templating/'.Fx::context()->config['templating'] . '/';
            $folderTo = root . '/' . Fx::context()->config['folder']['template'] . '/';

            if(!is_dir($folderTo))
                Fx::io()->create_dir($folderTo);

            $dir = Fx::io()->tree($folderFrom);


            foreach($dir['dir'] as $v){
                $folder = str_replace($folderFrom, '', $v);

                if(!is_dir($folderTo . $folder))
                    Fx::io()->create_dir($folderTo . $folder);
            }

            foreach($dir['file'] as $v){
                $file = explode('/', $v);

                $file = str_replace($folderFrom, '', $v);

                if(!file_exists($folderTo . $file)){
                    Fx::io()->copy($v, $folderTo . $file);
                }
            }

            load_url();
        }else{
            load_url();
        }
    }


    require_once 'main.html';