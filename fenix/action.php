<?

switch ($_REQUEST['action']){


    
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
        echo $io->buffer(sys . '/template/tpl/template/object_item.html', array(
            'io' => $io,
            'key' => $key,
            'value' => array(),
            'manifest' => $manifest
        ));
    break;

    case 'getGist':
        $type = isset($_POST['type']) && $_POST['type'] !== '' ? $_POST['type'] : 'string';
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


    case 'search':
        try{
            $val = $_POST['value'];

            $result = array();
            foreach($db->find($GLOB['namespace']['struct_db']) as $v){
                $table = $v['code'];

                $row = $db->extract($db->go(array(
                    'event' => 'find',
                    'from' => $table,
                    'limit' => 1
                )));
                if(!isset($row[0])) continue;
                $rows = $row[0];

                $where = array();

                foreach(array_keys($rows) as $row){
                    $where[$row] = $val;
                }

                $find = $db->search($table, $where);
                if(count($find) > 0){
                    $result[] = array(
                        'name' => $v['name'],
                        'code' => $v['code'],
                        'find' => $find
                    );
                }

            }

            header('Content-type: text/json');
            header('Content-type: application/json');
            echo json_encode($result);

        } catch (Exception $exc) {
            setSystemMessage('error', $e);
        }
    break;
}


$Extension->compile();
$extAction = $Extension->get('action');
if(count($extAction)){
    foreach ($extAction as $ext) {
        if($ext['option']['name'] === $_REQUEST['action']){
            $fileInit = $ext['url'] . $ext['option']['init'];
            if(file_exists($fileInit)){
                require_once $fileInit;
            }
            break;
        }
    }
}


$Action = new Action($db, $io, $GLOB, $config, $manifest);

$Action->test($_REQUEST, $_POST, $_GET, $_FILES);


class Action {
    function __construct($db, $io, $GLOB, $config, $manifest){
        $this->db = $db;
        $this->io = $io;
        $this->GLOB = $GLOB;
        $this->config = $config;
        $this->manifest = $manifest;
    }

    private  function isSelfMethod(){
        $backTrace = debug_backtrace();

        foreach($backTrace as $v){

            if(method_exists(get_class(), $v['function'])){

            }else{
                if(in_array($v['function'], array('require_once', 'require', 'include'))){
                    return true;
                }else{
                    return false;
                }
            }

        }
        return true;
    }

    public function test($request, $post, $get, $files){
        if(isset($request['action'])){
            if(method_exists(get_class(), ($request['action']))){

                try {
                    $this->{$request['action']}($post, $get, $files);
                } catch (Exception $e){
                    setSystemMessage('error', $e);
                }
            }
        }
    }

    public function connect($post){
        $find = $this->db->findOne($this->GLOB['namespace']['user'], array(
            'login' => $this->db->esc(trim($post['login'])),
            'pass' => hashGenerate(strtolower(trim($this->db->esc($post['password']))))
        ));

        if(is_array($find) && isset($find['login'])){
            $this->db->update($this->GLOB['namespace']['user'], array(
                'last_date' => time()
            ), array( 'id' => $find['id'] ));

            $_SESSION['user'] = array(
                'id' => $find['id'],
                'login' => $find['login'],
                'access' => $find['access']
            );
        }
        if($this->isSelfMethod()){
            load_url();
        }else{
            return $_SESSION['user'];
        }
    }


    public function disconnect(){
        session_destroy();
        load_url('/' . $this->config['folder']['sys'] . '/index.php');
    }

    public function addUser($post){
        $query = array(
            'login' => trim($post['name']),
            'pass' => hashGenerate(strtolower(trim($post['pass']))),
            'access' => $post['access']
        );


        $find = $this->db->find($this->GLOB['namespace']['user'], array('login' => $query['login']));
        if(count($find) > 0){
            throw new Exception('Такой пользователь уже существует');
        }
        $this->db->insert($this->GLOB['namespace']['user'], $query);
        $name = $query['login'];


        if($this->isSelfMethod()){
            setSystemMessage('good', 'Пользователь <b>'.$name.'</b> был добавлен');
            load_url();
        }else{
            return $query;
        }
    }

    public function editUser($post){

            $id = (int) $post['id'];
            $query = array(
                'login' => trim($post['name']),
                'access' => $post['access']
            );

            if($post['pass'] !== '')
                $query['pass'] = hashGenerate(strtolower(trim($_POST['pass'])));

            $this->db->update($this->GLOB['namespace']['user'], $query, array('id' => $id));
            $name = $query['login'];


        if($this->isSelfMethod()){
            setSystemMessage('good', 'Данные пользователя <b>'.$name.'</b> были изменены');
            load_url();
        }else{
            return $query;
        }
    }

    public function removeUser($post, $get){

        $id = (int) $get['id'];
        $find = $this->db->findOne($this->GLOB['namespace']['user'], array('id' => $id));
        if(is_array($find)){
            $name = $find['login'];
            $this->db->remove($this->GLOB['namespace']['user'], array('id' => $id));
        }else{
            throw new Exception('Нет такого пользователя');
        }

        if($this->isSelfMethod()){
            setSystemMessage('good', 'Пользователь <b>'.$name.'</b> был удален');
            load_url();
        }else{
            return $find;
        }
    }

    public function clearSystemMessage($post){
        $array = array();
        foreach($_SESSION['error'] as $v){
            if($v['id'] != $post['id']) $array[] = $v;
        }
        $_SESSION['error'] = $array;
    }

    public function object($post){
        /**
         * $post['name'] => Имя объекта
         * $post['code'] => Имя таблици
         * $post['icon'] => Иконка объекта
         * $post['show_sistem'] => Показывать/скрывать системные настройки
         * $post['show_wood'] => Показывать скрывать в левом меню
         * $post['row'] => Массив полей
         *
         * row[
         *  'name' => имя столбца
         *  'code' => имя стобца в таблице
         *  'type' => тип столбца
         *  'param' => Массив параметров
         * ]
         * */

        if(empty($post['name'])) throw new Exception('Не введено имя объекта');
        if(empty($post['code'])) throw new Exception('Не введен код объекта');

        foreach($post['row'] as $k => $v){
            if($v['code'] == 'id'){
                unset($post['row'][$k]);
            }
        }
        $post['row'] = array_unshift($post['row'], array(
            'code' => 'id',
            'type' => 'int',
            'size' => 11
        ));


        $SAVE_OBJECT = $this->saveObject(array(
            'name' => $post['code'],
            'row' => $post['row']
        ));

        $SAVE_OBJECT['name'] = $post['name'];
        $SAVE_OBJECT['code'] = $post['code'];

        $SAVE_OBJECT['icon'] = isset($post['icon']) ? $post['icon'] : '';
        $SAVE_OBJECT['show_sistem'] = isset($post['show_sistem']) ? $post['show_sistem'] : '';
        $SAVE_OBJECT['show_wood'] = isset($post['show_wood']) ? $post['show_wood'] : '';

        $find = $this->db->findOne($this->GLOB['namespace']['struct_db'], array('code' => $SAVE_OBJECT['code']));
        if($find === false){

            $TABLE_ID = $this->db->insert($this->GLOB['namespace']['struct_db'], array(
                'name' => $SAVE_OBJECT['name'],
                'code' => $SAVE_OBJECT['code'],
                'icon' => $SAVE_OBJECT['icon'],
                'show_wood' => $SAVE_OBJECT['show_wood'],
                'show_sistem' => $SAVE_OBJECT['show_sistem']
            ));


            foreach($SAVE_OBJECT['row'] as $k => $v){
                $v['type'] = $v['base_type'];
                $this->db->insert($this->GLOB['namespace']['struct_td'], array(
                    'parent' => $TABLE_ID,
                    'name' => $v['sys_name'],
                    'code' => $v['name'],
                    'num' => $k,
                    'type' => $v['type'],
                    'param' => json_encode($v['param']),
                    'size' => $v['size']
                ));

            }
            debug($SAVE_OBJECT);
        }else{
            $TABLE_ID = $find['id'];
            $rows = $this->db->find($this->GLOB['namespace']['struct_td'], array('parent' => $TABLE_ID));

            foreach($SAVE_OBJECT['row'] as $k => $v){
                $v['type'] = $v['base_type'];

                if(!isset($v['param'])){
                    $v['param'] = '';
                }

                $v['param'] = is_array($v['param']) ? json_encode($v['param']) : $v['param'];

                if(isset($rows[$k])){
                    $this->db->update($this->GLOB['namespace']['struct_td'], array(
                        'parent' => $TABLE_ID,
                        'name' => $v['sys_name'],
                        'code' => $v['name'],
                        'num' => $k,
                        'type' => $v['type'],
                        'param' => $v['param'],
                        'size' => $v['size']
                    ), array(
                        'id' => $rows[$k]['id']
                    ));

                    unset($rows[$k]);
                }else{
                    $this->db->insert($this->GLOB['namespace']['struct_td'], array(
                        'parent' => $TABLE_ID,
                        'name' => $v['sys_name'],
                        'code' => $v['name'],
                        'num' => $k,
                        'type' => $v['type'],
                        'param' => $v['param'],
                        'size' => $v['size']
                    ));
                }
            }

            foreach($rows as $v){
                $this->db->remove($this->GLOB['namespace']['struct_td'], array('id' => $v['id']));
            }


            if($this->isSelfMethod()){
                load_url();
            }else{
                return $SAVE_OBJECT;
            }
        }
    }

    public function saveObject($post){
        /**
         * $post['name'] => Имя таблици
         * $post['row'] => Массив столбцов таблици
         *
         * row[
         *  'code' => имя стобца в таблице
         *  'type' => тип столбца
         *  'size' => размер параметров
         *  'param' => Массив параметров
         * ]
         */

        if(empty($post['name'])) throw new Exception('Пустое значение имени таблици');

        $tables = $this->db->tables_info();
        $tables = $tables['table'];
        $manifestGist = $this->manifest['gist'];


        $CREATE_TABLE = array(
            'name' => $post['name'],
            'row' => array()
        );


        // Перебор входных параметров стобцов
        if(isset($post['row']['code'])){
            $isRows = array();
            foreach($post['row']['code'] as $k => $v){
                if(in_array($v, $isRows)) continue;
                $isRows[] = $v;
                $row = array('name' => $v);

                $row['type'] = isset($post['row']['type'][$k]) ? $post['row']['type'][$k] : 'string';




                // Если в манифесте есть такой тип и он не равен пустой строке
                // Нужно когда создается системный тип
                $row['base_type'] = $row['type'];
                if(isset($manifestGist[$row['type']]) && $manifestGist[$row['type']] !== ''){
                    $row['type'] = $manifestGist[$row['type']]['type'];
                }



                if(isset($post['row']['name'][$k])){
                    $row['sys_name'] = $post['row']['name'][$k];
                }

                if(isset($post['row']['param']['size'][$k])){
                    $row['size'] = $post['row']['param']['size'][$k];
                }else{
                    $row['size'] = isset($post['row']['size'][$k]) ? $post['row']['size'][$k] : '';
                }

                if(isset($post['row']['index'][$k])){
                    $row['index'] = $post['row']['index'][$k];
                }

                if(isset($post['row']['base'][$k])){
                    $row['base'] = $post['row']['base'][$k];
                }

                if(isset($post['row']['param'][$k])){
                    $row['param'] = $post['row']['param'][$k];
                }

                $CREATE_TABLE['row'][] = $row;
            }
        }

        if(isset($tables[$CREATE_TABLE['name']])){ // Если уже есть такая таблица изменяем ее;
            $ROWS = $this->rowInArray($CREATE_TABLE['name'], $CREATE_TABLE['row']);

            $this->db->editCollection(array(
                'name' => $CREATE_TABLE['name'],
                'row' => $ROWS
            ));

        }else{ // Если нет создаем

            $this->db->createCollection(array(
                'name' => $CREATE_TABLE['name'],
                'row' => $CREATE_TABLE['row']
            ));

        }


        return $CREATE_TABLE;
    }

    public function rowInArray($tableName, $row){
        /**
         * $tableName => Имя таблици
         * row[
         *  'code' => имя стобца в таблице
         *  'type' => тип столбца
         *  'size' => Массив параметров
         * ]
         */


        // Полсучаем список столбцов
        $tableRows = $this->db->show_column($tableName);

        foreach($row as $k => $v){
            $row[$v['name']] = $v;
            unset($row[$k]);
        }

        $result = array(
            'add' => array(),
            'change' => array(),
            'drop' => array()
        );

        // Проверки на наличие изменение
        foreach($tableRows as $v){

            if(isset($row[$v['Field']]) && $row[$v['Field']]['base'] !== ''){ // Change
                $result['change'][] = $row[$v['Field']];
                unset($row[$v['Field']]);
            }else if(!isset($row[$v['Field']])){ // Remove
                $result['drop'][] = $v['Field'];
            }

        }
        foreach($row as $k => $v){ // Add

            $result['add'][] = $v;
            unset($row[$k]);

        }

        return $result;
    }

    function addObject($post){
        if(empty($_POST['code'])) throw new Exception('Не введен код объекта');
        if(empty($_POST['name'])) throw new Exception('Не введено имя объекта');

        $this->object(array(
            'name' => $post['name'],
            'code' => $post['code'],
            'icon' => 'icon-doc',
            'show_sistem' => 1,
            'show_wood' => 1,
            'row' => array(
                array('name' => 'Имя', 'code' => 'name', 'type' => 'string'),
                array('name' => 'Описание', 'code' => 'text', 'type' => 'text'),
                array('name' => 'Изображение', 'code' => 'image', 'type' => 'image')
            )
        ));

        if($this->isSelfMethod()){
            load_url();
        }
    }

    // Добавление шаблона
    public function addTemplate($post){
        $name = trim($post['name']);

        $find = $this->db->find($this->GLOB['namespace']['template'], array('name' => $name));
        if(count($find) > 0) throw new Exception('Шаблон с таким именем уже существует');

        if(isset($post['id'])){
            $find = $this->db->find($this->GLOB['namespace']['template'], array('id' => (int) $post['id']));

            $this->db->update($this->GLOB['namespace']['template'], array( 'name' => $name ), array('id' => (int) $post['id']));
            foreach($this->manifest['templating'][$this->config['templating']] as $v){

                $folder = root . '/' . $this->config['folder']['template'] . '/' . $v . '/';
                $new_file =  $folder . $name . '.' . $v;
                $file = $folder . $find[0]['name'] . '.' . $v;

                if(file_exists ($file))
                    rename($file, $new_file);
            }
        }else{
            $this->db->insert($this->GLOB['namespace']['template'], array( 'name' => $name ));

            foreach($this->manifest['templating'][$this->config['templating']] as $v){
                $folder = root . '/' . $config['folder']['template'] . '/' . $v . '/';
                $file =  $folder . $name . '.' . $v;

                if(!file_exists ($folder))
                    $this->io->create_dir($folder);
                if(!file_exists ($file))
                    $this->io->create_file($file);
            }
        }

        if($this->isSelfMethod()){
            load_url();
        }else{
            return $name;
        }
    }

    // Удаление шаблона
    public function removeTemplate($post, $get) {
        $id = (int) $get['id'];
        $this->db->remove($this->GLOB['namespace']['template'], array('id' => $id));

        if($this->isSelfMethod()){
            load_url();
        }
    }

    // Создание/редактирование маркера
    public function addMarker($post) {
        $name = trim($post['name']);

        $find = $this->db->find($this->GLOB['namespace']['marker'], array('name' => $name));

        if(count($find) > 0) throw new Exception('Макер с таким именем уже существует');

        if(isset($post['id'])){
            $this->db->update($this->GLOB['namespace']['marker'], array( 'name' => $name ), array('id' => (int) $post['id']));
        }else{
            $this->db->insert($this->GLOB['namespace']['marker'], array( 'name' => $name ));
        }

        if($this->isSelfMethod()){
            load_url();
        }else{
            return $name;
        }
    }

    // Добавление шаблона в маркер
    public function markerAddTemplate($post) {
        $marker = $_POST['marker'];
        $id = isset($_POST['id']) ? implode(',', $post['id']) : '';
        $this->db->update($this->GLOB['namespace']['marker'], array('template_id' => $id), array('id' => $marker));

        if($this->isSelfMethod()){
            load_url();
        }else{
            return $post['id'];
        }
    }

    // Удаление маркера
    public function removeMarker ($post, $get) {
        $id = (int) $get['id'];
        $this->db->remove($this->GLOB['namespace']['marker'], array('id' => $id));

        if($this->isSelfMethod()){
            load_url();
        }else{
            return $id;
        }
    }



}
exit();