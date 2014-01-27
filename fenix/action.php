<?

switch ($_REQUEST['action']){

    case 'connect':
        $find = $db->find($GLOB['namespace']['user'], array(
            'login' => $db->esc(trim($_POST['login'])),
            'pass' => hashGenerate(strtolower(trim($db->esc($_POST['password']))))
        ));
        
        if(count($find)){
            $db->update($GLOB['namespace']['user'], array(
                'last_date' => time()
            ), array( 'id' => $find[0]['id'] ));
            
            $_SESSION['user'] = array(
                'login' => $find[0]['login'],
                'access' => $find[0]['access']
            );
        }
        load_url();
    break;
    
    case 'disconnect':
        session_destroy();
        load_url('/' . $config['folder']['sys'] . '/index.php');
    break;
    
    case 'addUser':
        $name = '';
        try {
            $query = array(
                'login' => trim($_POST['name']),
                'pass' => hashGenerate(strtolower(trim($_POST['pass']))),
                'access' => $_POST['access']
            );
            
            
            $find = $db->find($GLOB['namespace']['user'], array('login' => $query['login']));
            if(count($find) > 0){
                throw new Exception('Такой пользователь уже существует');
            }
            $db->insert($GLOB['namespace']['user'], $query);
            $name = $query['login'];
        } catch (Exception $e){
            setSystemMessage('error', $e);
        }
        setSystemMessage('good', 'Пользователь <b>'.$name.'</b> был создан');
        load_url();
    break;
    
    case 'clearSystemMessage':
        $array = array();
        foreach($_SESSION['error'] as $v){
            if($v['id'] != $_POST['id']) $array[] = $v;
        }
        $_SESSION['error'] = $array;
    break;
    
    case 'removeUser': 
        $name = '';
        try {
            $id = (int) $_GET['id'];
            $name = $db->find($GLOB['namespace']['user'], array('id' => $id));
            $name = $name[0]['login'];
            $db->remove($GLOB['namespace']['user'], array('id' => $id));
        }  catch (Exception $e){
            setSystemMessage('error', $e);
        }
        setSystemMessage('good', 'Пользователь <b>'.$name.'</b> был удален');
        load_url();
    break;

    case 'addTemplate':
        try{
            $name = trim($_POST['name']);
            $find = $db->find($GLOB['namespace']['template'], array('name' => $name));
            if(count($find) > 0) throw new Exception('Шаблон с таким именем уже существует');
            
            if(isset($_POST['id'])){
                $find = $db->find($GLOB['namespace']['template'], array('id' => (int) $_POST['id']));
                
                $db->update($GLOB['namespace']['template'], array( 'name' => $name ), array('id' => (int) $_POST['id']));
                foreach($manifest['templating'][$config['templating']] as $v){
                    $folder = root . '/' . $config['folder']['template'] . '/' . $v . '/';
                    $new_file =  $folder . $name . '.' . $v;
                    $file = $folder . $find[0]['name'] . '.' . $v;
                    
                    if(file_exists ($file))
                        rename($file, $new_file);
                }
            }else{
                $db->insert($GLOB['namespace']['template'], array( 'name' => $name ));
                foreach($manifest['templating'][$config['templating']] as $v){
                    $folder = root . '/' . $config['folder']['template'] . '/' . $v . '/';
                    $file =  $folder . $name . '.' . $v;

                    if(!file_exists ($folder))
                        $io->create_dir($folder);
                    if(!file_exists ($file))
                        $io->create_file($file);
                }
            }
        }  catch (Exception $e){
            setSystemMessage('error', $e);
        }
        load_url();
    break;
    
    case 'removeTemplate':
        try{
            $id = (int) $_GET['id'];
            $db->remove($GLOB['namespace']['template'], array('id' => $id));
        }  catch (Exception $e){
            setSystemMessage('error', $e);
        }
        load_url();
    break;

    case 'addMarker':
        try{
            $name = trim($_POST['name']);
            $find = $db->find($GLOB['namespace']['marker'], array('name' => $name));
            if(count($find) > 0) throw new Exception('Макер с таким именем уже существует');
            
            if(isset($_POST['id'])){
                $db->update($GLOB['namespace']['marker'], array( 'name' => $name ), array('id' => (int) $_POST['id']));
            }else{
                $db->insert($GLOB['namespace']['marker'], array( 'name' => $name ));
            }
        }  catch (Exception $e){
            setSystemMessage('error', $e);
        }
        load_url();
    break;
    
    case 'markerAddTemplate':
        try {
            $marker = $_POST['marker'];
            $id = isset($_POST['id']) ? implode(',', $_POST['id']) : '';
            $db->update($GLOB['namespace']['marker'], array('template_id' => $id), array('id' => $marker));
            
        }  catch (Exception $e){
            setSystemMessage('error', $e);
        }
        load_url();
    break;
    
    case 'removeMarker':
        try{
            $id = (int) $_GET['id'];
            $db->remove($GLOB['namespace']['marker'], array('id' => $id));
        }  catch (Exception $e){
            setSystemMessage('error', $e);
        }
        load_url();
    break;

    case 'object': 
        try{
            if(empty($_POST['code'])) throw new Exception('Не введен код объекта');
            if(empty($_POST['name'])) throw new Exception('Не введено имя объекта');
			
            $toDB = array(
                'name' => $_POST['name'],
                'code' => $_POST['code'],
                'icon' => $_POST['icon'],
                'show_wood' => isset($_POST['show_wood']) ? $_POST['show_wood'] : 0,
                'show_sistem' => $_POST['show_sistem']
            );

            /* Формирование таблици */
            $row = array(
                array('name' => 'id', 'type' => 'int', 'size' => 11, 'index' => 'ap')
            );
            $toTD = array();
            foreach($_POST['form']['name'] as $k => $v){
                
                $param = isset($_POST['form']['param'][$k]) ? $_POST['form']['param'][$k] : array();
                
                if(isset($param['list'])){
                    $param['list'] = json_decode($param['list']);
                }
                $manifetGist = $manifest['gist'][$_POST['form']['type'][$k]];
                $type = $manifetGist['type'];
                $size = isset($manifetGist['size']) ? $manifetGist['size'] : '';
                $size = isset($param[$k]['size']) && $param[$k]['size'] != '' ? (int) $param[$k]['size'] : $size;

                if(empty($_POST['form']['code'][$k])) throw new Exception('Не введен код поля');
                if(empty($_POST['form']['name'][$k])) throw new Exception('Не введено имя поля');
                
                $row[] = array(
                    'name' => $_POST['form']['code'][$k],
                    'type' => $manifest['gist'][$_POST['form']['type'][$k]]['type'],
                    'size' => $size
                );
                
                $toTD[] = array(
                    'id' => isset($_POST['form']['id'][$k]) && $_POST['form']['id'][$k] != '' ? $_POST['form']['id'][$k] : false,
                    'name' => $v,
                    'code' => $_POST['form']['code'][$k],
                    'num' => $k,
                    'type' => $_POST['form']['type'][$k],
                    'param' => json_encode($param),
                    'size' => $size
                );
            }
            if(isset($_POST['id'])){
                $id = (int) $_POST['id'];
                $original = $db->find($GLOB['namespace']['struct_td'], array('parent' => $id));

                $result = array('add' => array(), 'change' => array(), 'drop' => array() );
                $row = array('add' => array(), 'change' => array(), 'drop' => array() );
                $originalID = array();
                foreach($original as $v){ $originalID[$v['id']] = $v['code']; }
                
                
                $inspect = array();
                foreach($toTD as $v){
                    if($v['id'] !== false){
                        if(isset($originalID[$v['id']])){
                            $v['base'] = $originalID[$v['id']];
                            $result['change'][] = $v;
                            $inspect[] = $v['id'];
                        }
                    }else{
                        $result['add'][] = $v;
                    }
                }
                
                foreach($originalID as $k => $v){
                    if(!in_array($k, $inspect)) $result['drop'][] = array('id' => $k, 'name' => $v);
                }
                
                foreach($result as $key => $v){
                    foreach($v as $j){
                        if($key == 'drop'){
                            $row[$key][] = $j['name'];
                        }else if($key == 'change'){
                            $row[$key][] = array(
                                'base' => $j['base'],
                                'name' => $j['code'],
                                'type' => $manifest['gist'][$j['type']]['type'],
                                'size' => $j['size']
                            );
                        }else{
                            $row[$key][] = array(
                                'name' => $j['code'],
                                'type' => $manifest['gist'][$j['type']]['type'],
                                'size' => $j['size']
                            );
                        }
                    }
                }
                $db->update($GLOB['namespace']['struct_db'], $toDB, array('id' => $id));
                foreach($result['add'] as $v){
                    unset($v['id']);
                    $v['parent'] = $id;
                    $db->insert($GLOB['namespace']['struct_td'], $v);
                }
                foreach($result['change'] as $v){
                    $tId = $v['id'];
                    unset($v['id']);
                    unset($v['base']);
                    $db->update($GLOB['namespace']['struct_td'], $v, array('id' => $tId));
                }
                foreach($result['drop'] as $v){
                    $db->remove($GLOB['namespace']['struct_td'], array('id' => $v['id']));
                }
                
                $db->editCollection(array(
                    'name' => $_POST['code'],
                    'row' => $row
                ));
                
            }else{// Создание новой таблици
                $db->insert($GLOB['namespace']['struct_db'], $toDB);
                $parent = $db->lastID();
                foreach ($toTD as $v){
                    unset($v['id']);
                    $v['parent'] = $parent;
                    $db->insert($GLOB['namespace']['struct_td'], $v);
                }

                $db->createCollection(array(
                    'name' => $_POST['code'],
                    'row' => $row
                ));
            }
        }  catch (Exception $e){
            setSystemMessage('error', $e);
        }
        load_url();
    break;

    case 'addObject':
        try{
            if(empty($_POST['code'])) throw new Exception('Не введен код объекта');
            if(empty($_POST['name'])) throw new Exception('Не введено имя объекта');
            $toDB = array(
                'name' => $_POST['name'],
                'code' => $_POST['code'],
                'icon' => 'icon-doc',
                'show_wood' => 1,
                'show_sistem' => 1
            );
            $td = array(
                array('name' => 'Имя', 'code' => 'name', 'type' => 'string'),
                array('name' => 'Описание', 'code' => 'text', 'type' => 'text'),
                array('name' => 'Изображение', 'code' => 'image', 'type' => 'image')
            );
            $row = array( array('name' => 'id', 'type' => 'int', 'size' => 11, 'index' => 'ap') );
            $toTD = array();
            foreach($td as $k => $v){
                $type = isset($manifest['gist'][$v['type']]) ? $manifest['gist'][$v['type']]['type'] : 'text';
                
                

                
                $size = (isset($manifest['gist'][$v['type']]['size'])) ? $manifest['gist'][$v['type']]['size'] : '';
                $size = isset($v['size']) && $v['size'] != '' ? (int) $v['size'] : $size;

                if(empty($v['code'])) throw new Exception('Не введен код поля');
                if(empty($v['name'])) throw new Exception('Не введено имя поля');
                
                $row[] = array(
                    'name' => $v['code'],
                    'type' => $type,
                    'size' => $size
                );
                $toTD[] = array(
                    'name' => $v['name'],
                    'code' => $v['code'],
                    'num' => $k,
                    'type' => $v['type'],
                    'size' => $size
                );
            }

            $db->insert($GLOB['namespace']['struct_db'], $toDB);
            $parent = $db->lastID();
            foreach ($toTD as $v){
                $v['parent'] = $parent;
                $db->insert($GLOB['namespace']['struct_td'], $v);
            }

            $db->createCollection(array(
                'name' => $_POST['code'],
                'row' => $row
            ));
        }  catch (Exception $e){
            setSystemMessage('error', $e);
        }
        load_url();
    break;
    
    case 'removeObject':
        try{
            $id = (int) $_GET['id'];
            $name = $_GET['name'];
            
            $db->remove($GLOB['namespace']['struct_db'], array('id' => $id));
            $db->remove($GLOB['namespace']['struct_td'], array('parent' => $id));
            $db->remove_table($name);
        }  catch (Exception $e){
            setSystemMessage('error', $e);
        }
        load_url();
    break;

    case 'getItemObject':
        $key = (int) $_POST['index'];
        echo loadParam($key, array(), $manifest, sys . '/template/tpl/template/object_item.html');
    break;

    case 'getGist':
        $type = $_POST['type'];
        $key = (int) $_POST['key'];
        $path = sys.'/template/tpl/gist-param/';
        if(isset($manifest['gist'][$type])){
            $res = array();
            foreach($manifest['gist'][$type]['param'] as $v){
                $file = $path . $v . '.html';
                if(file_exists($file))
                    $res[] = loadParam($key, array(), $manifest, $file);
            }
            echo implode('', $res);
        }
    break;
    
    case 'elem':
        require_once sys . '/plugin/class.upload/class.upload.php';
                
        $file = isset($_FILES['form']) ? $_FILES['form'] : array();
        $form = isset($_POST['form']) ? $_POST['form'] : array();


        $activePath = (isset($_POST['active_path']) && $_POST['active_path'] != '') ? 1 : 0;

        $updateId = (isset($_POST['id']) && $_POST['id'] != '') ? (int) $_POST['id'] : false;
        if($updateId !== false){
            $db->update($GLOB['namespace']['construct_db'], array(
                'chpu' => $_POST['chpu'],
                'active_path' => $activePath,
                'marker' => $_POST['marker'],
                'date' => time()
            ), array('id' => $updateId));
            
            
            $ref = $db->find($GLOB['namespace']['construct_db'], array('ref' => $updateId));
            foreach($ref as $v){
                $db->update($GLOB['namespace']['construct_db'], array(
                'chpu' => $_POST['chpu'],
                'active_path' => $_POST['active_path'],
                'marker' => $_POST['marker'],
                'date' => time()
            ), array('id' => $v['id']));
            }
            
            $lastId = $updateId;
        }else{
            $num = count($db->find($GLOB['namespace']['construct_db'], array( 'parent' => (int) $_POST['parent'] )));
            $db->insert($GLOB['namespace']['construct_db'], array(
                'parent' => (int) $_POST['parent'],
                'ref' => '',
                'object' => $_POST['object'],
                'chpu' => $_POST['chpu'],
                'num' => $num,
                'active_path' => $activePath,
                'marker' => $_POST['marker'],
                'date' => time()
            ));
            $form['id'] = $lastId = $db->lastID();
        }

        $table = $db->find($GLOB['namespace']['struct_db'], array('code' => $_POST['object']));
        $table = $table[0];
        
        $row = $db->find($GLOB['namespace']['struct_td'], array( 'parent' => $table['id'] ));
        $rows = array();
        foreach($row as $v){
            $rows[$v['code']] = array(
                'id' => $v['id'],
                'size' => $v['size'],
                'type' => $v['type'],
                'param' => json_decode($v['param'], true)
            );
        }
        
        foreach($form as $k => $v){
            if(is_string($v)){
                $form[$k] = $db->esc($v);
            }
        }
        
        $path = root . '/' . $config['folder']['files'] . '/' . $lastId . '/';
        if(isset($file['tmp_name'])){
            foreach ($file['tmp_name'] as $k => $v){
                $savePath = '/' . $config['folder']['files'] . '/' . $lastId . '/';
                $fileName = (isset($form[$k]['name']) && $form[$k]['name'] != '') ? trim($form[$k]['name']) : $k;
                $gist = $rows[$k];

                $fn = (isset($form[$k]['url']) && $form[$k]['url'] != '') ? $form[$k]['url'] : $v['file'];
                $mime = $io->mime($file['name'][$k]['file'] != '' ? $file['name'][$k]['file'] : $form[$k]['url']);

                if(isset($form[$k]['url']) && $form[$k]['url'] !== ''){
                    $io->create_file(root. '/' . 'temporary.tmp');
                    $io->in_file(root. '/' . 'temporary.tmp', file_get_contents($fn));
                    $fn = root. '/' . 'temporary.tmp';
                }
                if($fn != ''){
                    if(empty($fn)) continue;

                    $IMG = new upload($fn);
                    if($IMG->uploaded){
                        $IMG->file_new_name_body = $fileName;
                        $IMG->file_new_name_ext = $mime;
                        $gistParam = $gist['param'];

                        $width = (isset($gistParam['width']) && $gistParam['width'] != '') ? $gistParam['width'] : 1000;
                        $height = (isset($gistParam['height']) && $gistParam['height'] != '') ? $gistParam['height'] : 1000;

                        // Param
                        if(isset($gist['param']['method'])){
                            switch ($gist['param']['method']){
                                case 'scale': // Подгоняем по размеру и пропорциям, без полей
                                    $IMG->image_resize  = true;
                                    $IMG->image_ratio   = true;
                                    $IMG->image_y       = $height;
                                    $IMG->image_x       = $width;
                                break;

                                case 'crop': // Точно по размеру игнорируя пропорции
                                    $IMG->image_resize      = true;
                                    $IMG->image_ratio_crop  = true;
                                    $IMG->image_y           = $height;
                                    $IMG->image_x           = $width;
                                break;

                                case 'width': // ресайзим по ширине
                                    $IMG->image_resize  = true;
                                    $IMG->image_ratio_x = true;
                                    $IMG->image_y       = $height;
                                break;

                                case 'height': // ресайзим по высоте
                                    $IMG->image_resize  = true;
                                    $IMG->image_ratio_y = true;
                                    $IMG->image_x       = $width;
                                break;
                            }
                        }
                        if(is_file($path . $fileName . '.' . $mime))
                            unlink ($path . $fileName . '.' . $mime);

                        $IMG->Process($path);
                        if(!$IMG->processed){
                            setSystemMessage('error', $IMG->error);
                        }
                        $IMG->clean();

                        if(isset($gist['param']['zip']) && $gist['param']['zip'] == 1){
                            $zip = new ZipArchive;
                            if ($zip->open($path . $fileName . '.zip', ZIPARCHIVE::CREATE)===TRUE) {
                                $zip->addFile($path . $fileName . '.' . $mime, $fileName . '.' . $mime);
                            }
                            $zip->close();

                            if(file_exists($path . $fileName . '.' . $mime))
                                unlink($path . $fileName . '.' . $mime);
                            $savePath = $savePath . $fileName . '.zip';
                        }else{
                            $savePath = $savePath . $fileName . '.' . $mime;
                        }
                    }else{
                        setSystemMessage('error', $IMG->error);
                    }
                    $form[$k] = file_exists(root . $savePath) ? $savePath : '';
                }

                if(is_array($form[$k])){
                    if(isset($form[$k]['remove']) && $form[$k]['remove'] == 1){
                        if(file_exists(root . $form[$k]['src'])){
                            unlink(root . $form[$k]['src']);
                        }
                        $form[$k] = '';
                    }else if(isset($form[$k]['src']) && isset($form[$k]['name'])){
                        $imagePath = explode('/', $form[$k]['src']);
                        $imageName = array_pop($imagePath);
                        $imageName = explode('.', $imageName);
                        $imageMime = array_pop($imageName);
                        $imageName = implode('.', $imageName);

                        if($imageName !== $form[$k]['name']){
                            $newImageName = implode('/', $imagePath) . '/' . $form[$k]['name'] . '.' . $imageMime;
                            if(is_file(root.$newImageName)) unlink (root.$newImageName);
                            rename(root . $form[$k]['src'], root . $newImageName);

                            $form[$k] = $newImageName;
                        }else{
                            $form[$k] = $form[$k]['src'];
                        }
                    }else{
                        $form[$k] = '';
                    }
                }
            }
        }
        
        
        //debug($_POST['object']);
        if($updateId !== false){
            $db->update($_POST['object'], $form, array('id' => $updateId));
        }else{
            $db->insert($_POST['object'], $form);
        }
        load_url();
    break;
    
    case 'removeElem':
        try {
            removeElem($db, $io, $GLOB, $config, (int) $_GET['id']);
            
            if(isset($_SESSION['back_param']['id']) && $_SESSION['back_param']['id'] == $_GET['id']){
                load_url (rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/?mode=project');
                exit();
            }
        } catch (Exception $e) {
            setSystemMessage('error', $e);
        }
        setSystemMessage('good', 'Елеменет был удален');
        load_url();
    break;
    
    
    case 'removeItem':
        try {
            foreach($_POST['id'] as $v){
                removeElem($db, $io, $GLOB, $config, (int) $v);
            }
        } catch (Exception $e) {
            setSystemMessage('error', $e);
        }
    break;
    
    case 'dubleItem':
        try {
            $parent = (int)$_POST['parent'];
            foreach($_POST['id'] as $v){
                copyElem($db, $io, $GLOB, $config, (int) $v, $parent);
            }
        } catch (Exception $e) {
            setSystemMessage('error', $e);
        }
        setSystemMessage('good', 'Элемент успешно скопирован в текущую деррикторию');
    break;
    
    case 'copyItem':
        try {
            $_SESSION['moveItem'] = array();
            $_SESSION['copyItem'] = $_POST['id'];
        } catch (Exception $exc) {
            setSystemMessage('error', $e);
        }
        
        setSystemMessage('good', 'Элемент успешно скопирован в текущую деррикторию');
    break;
    
    case 'moveItem':
        try {
            $_SESSION['copyItem'] = array();
            $_SESSION['moveItem'] = $_POST['id'];
        } catch (Exception $exc) {
            setSystemMessage('error', $e);
        }
    break;


    case 'pasteItem':
        try {
            $parent = (int) $_GET['id'];
            if(isset($_SESSION['copyItem']) && count($_SESSION['copyItem'])){
                $id = $_SESSION['copyItem'];
                $event = 'copy';
                $_SESSION['copyItem'] = array();
            }else if(isset($_SESSION['moveItem']) && count($_SESSION['moveItem'])){
                $id = $_SESSION['moveItem'];
                $event = 'move';
                $_SESSION['moveItem'] = array();
            }else{
                throw new Exception();
            }
            
            foreach($id as $v){
                if($event == 'move'){
                    $num = count($db->find($GLOB['namespace']['construct_db'], array('parent' => $parent)));
                    $db->update($GLOB['namespace']['construct_db'], array('parent' => $parent, 'num' => $num, 'date' => time()), array('id' => (int) $v));
                }else{
                    copyElem($db, $io, $GLOB, $config, (int) $v, $parent);
                }
            }
        } catch (Exception $e) {
            setSystemMessage('error', $e);
        }
        load_url();
    break;
    
    case 'pasteItemLink':
        try {
            $parent = (int) $_GET['id'];
            if(isset($_SESSION['copyItem']) && count($_SESSION['copyItem'])){
                $id = $_SESSION['copyItem'];
                $_SESSION['copyItem'] = array();
            }else if(isset($_SESSION['moveItem']) && count($_SESSION['moveItem'])){
                $id = $_SESSION['moveItem'];
                $_SESSION['moveItem'] = array();
            }else{
                throw new Exception();  
            }
            
            $num = count($db->find($GLOB['namespace']['construct_db'], array('parent' => $parent)));

            foreach($id as $v){
                $object = $db->find($GLOB['namespace']['construct_db'], array('id' => $v));
                $object = $object[0];
                
                // Ссылки нельзя копировать
                if((int) $object['ref'] > 0) continue;
                
                unset($object['id']);
                $object['parent'] = $parent;
                $object['ref'] = $v;
                $object['num'] = $num;
                $object['date'] = time();
                $db->insert($GLOB['namespace']['construct_db'], $object);
            }
            
        } catch (Exception $e) {
            setSystemMessage('error', $e);
        }
        load_url();
    break;
    
    case 'sortElem':
        try {
            $id = $_POST['id'];
            
            foreach($id as $k => $v){
                $db->update($GLOB['namespace']['construct_db'], array('num' => $k), array('id' => $v));
            }
            
        } catch (Exception $exc) {
            setSystemMessage('error', $e);
        }
    break;
    
	case 'hideElement':
		try {
            $id = $_GET['id'];
            
            $db->update($GLOB['namespace']['construct_db'], array('hide' => (int) $_GET['hide']), array('id' => $id));
            
        } catch (Exception $exc) {
            setSystemMessage('error', $e);
        }
		load_url();
	break;

    case 'loadTable':
        try {
            $count = (int) $_POST['count'];
            $table = $_POST['table'];
            
            $result = $db->extract($db->go(array(
                'event' => 'find',
                'from' => $table,
                'limit' => ('' . $count . ', 50')
                )));
            
            
            $result = array(
                'message' => 'Данные загружены',
                'count' => $count +50,
                'result' => $result
                );


            echo json_encode($result);
            
        } catch (Exception $exc) {
            setSystemMessage('error', $e);
        }
    break;
	
    case 'editRowInTable':
        try{

            
            $where = array();
            $update = array();

            foreach ($_POST['row'] as $v) {
                $where[$v['name']] = $v['defValue'];
                $update[$v['name']] = $v['newValue'];
            }

            $db->update($_POST['table'], $update, $where);

            echo 'Строка изменена';
        } catch (Exception $exc) {
            setSystemMessage('error', $e);
        }
    break;

}

exit();