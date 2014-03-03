<?

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

    public function removeUser (){
        $get = func_get_arg(1);
        $id = (int) $get['id'];
        $user = $this->db->findOne($this->GLOB['namespace']['user'], array('id' => $id));
        if($user !== false){
            $this->db->remove($this->GLOB['namespace']['user'], array('id' => $id));
            setSystemMessage('good', 'Пользователь - <b>'.$user['login'].'</b> был удален');
        }else{
            setSystemMessage('error', 'В момент удаления произошла ошибка, такого пользователя не существует');
        }
        if($this->isSelfMethod()){
            load_url();
        }else{
            return $user;
        }

    }

    public function addObject ($post){
        $SAVE_OBJECT = $this->object(array(
            'name' => $post['name'],
            'code' => $post['code'],
            'icon' => 'icon-doc',
            'show_wood' => 1,
            'show_sistem' => 1,
            'row' => array(
                array('name' => 'Имя', 'code' => 'name', 'type' => 'string'),
                array('name' => 'Описание', 'code' => 'text', 'type' => 'text'),
                array('name' => 'Изображение', 'code' => 'image', 'type' => 'image')
            )
        ));

        if($this->isSelfMethod()){
            load_url();
        }else{
            return $SAVE_OBJECT;
        }
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

        array_unshift($post['row'], array('code' => 'id', 'type' => 'int', 'size' => 11, 'index' => 'AP', 'remove_row' => 1));

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

             $this->db->insert($this->GLOB['namespace']['struct_db'], array(
                'name' => $SAVE_OBJECT['name'],
                'code' => $SAVE_OBJECT['code'],
                'icon' => $SAVE_OBJECT['icon'],
                'show_wood' => $SAVE_OBJECT['show_wood'],
                'show_sistem' => $SAVE_OBJECT['show_sistem']
            ));

            $TABLE_ID = $this->db->lastID();

            $ROWS = array_merge(array(), $SAVE_OBJECT['row']['add'], $SAVE_OBJECT['row']['change']);


            foreach($ROWS as $k => $v){
                if(isset($v['remove_row']) && $v['remove_row'] === 1){
                    continue;
                }

                $v['type'] = $v['base_type'];
                $this->db->insert($this->GLOB['namespace']['struct_td'], array(
                    'parent' => $TABLE_ID,
                    'name' => $v['base_name'],
                    'code' => $v['name'],
                    'num' => $k,
                    'type' => $v['base_type'],
                    'param' => isset($v['param']) && is_array($v['param']) ? json_encode($v['param']) : '',
                    'size' => $v['size']
                ));
            }
        }else{
            $TABLE_ID = $find['id'];


            $this->db->update($this->GLOB['namespace']['struct_db'], array(
                'name' => $SAVE_OBJECT['name'],
                'code' => $SAVE_OBJECT['code'],
                'icon' => $SAVE_OBJECT['icon'],
                'show_wood' => $SAVE_OBJECT['show_wood'],
                'show_sistem' => $SAVE_OBJECT['show_sistem']
            ), array(
                'code' => $SAVE_OBJECT['code']
            ));

            foreach($SAVE_OBJECT['row']['add'] as $v){
                $v['type'] = isset($v['base_type']) ? $v['base_type'] : isset($v['type']) ? $v['type'] : 'string';
                $v['param'] = isset($v['param']) && is_array($v['param']) ? json_encode($v['param']) : '';
                $this->db->insert($this->GLOB['namespace']['struct_td'], array(
                    'parent' => $TABLE_ID,
                    'name' => isset($v['base_name']) ? $v['base_name'] : 'undefined',
                    'code' => $v['name'],
                    'num' => $v['num'],
                    'type' => $v['base_type'],
                    'param' => $v['param'],
                    'size' => $v['size']
                ));
            }

            foreach($SAVE_OBJECT['row']['change'] as $v){
                $v['type'] = isset($v['base_type']) ? $v['base_type'] : isset($v['type']) ? $v['type'] : 'string';
                $v['param'] = isset($v['param']) && is_array($v['param']) ? json_encode($v['param']) : '';

                $this->db->update($this->GLOB['namespace']['struct_td'], array(
                    'parent' => $TABLE_ID,
                    'name' => $v['base_name'],
                    'code' => $v['name'],
                    'num' => $v['num'],
                    'type' => $v['base_type'],
                    'param' => $v['param'],
                    'size' => $v['size']
                ), array(
                    'id' => $v['id']
                ));
            }

            foreach($SAVE_OBJECT['row']['drop'] as $v){
                $this->db->remove($this->GLOB['namespace']['struct_td'], array('code' => $v, 'parent' => $TABLE_ID));
            }
        }

        if($this->isSelfMethod()){
            load_url();
        }else{
            return $SAVE_OBJECT;
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
        if(isset($post['row']) && is_array($post['row'])){
            $isRows = array();
            foreach($post['row'] as $v){

                if(in_array($v['code'], $isRows)) continue;
                $isRows[] = $v['code'];

                $row = $v;

                $row['base'] = isset($row['base']) ? $row['base'] : $v['code'];
                $row['base_name'] = isset($v['name']) ? $v['name'] : 'undefiend';
                $row['name'] = $v['code'];
                $row['type'] = isset($v['type']) ? $v['type'] : 'string';

                // Если в манифесте есть такой тип и он не равен пустой строке
                // Нужно когда создается системный тип
                $row['base_type'] = $row['type'];
                if(isset($manifestGist[$row['type']]) && $manifestGist[$row['type']] !== ''){
                    $row['type'] = $manifestGist[$row['type']]['type'];
                }

                if(!isset($row['size'])){
                    if(isset($row['param']) && $row['param']['size']){
                        $row['size'] = $row['param']['size'];
                    }else{
                        $row['size'] = '';
                    }
                }

                $CREATE_TABLE['row'][] = $row;

            }
        }

        if(isset($tables[$CREATE_TABLE['name']])){ // Если уже есть такая таблица изменяем ее;
            $CREATE_TABLE['row'] = $this->rowInArray($CREATE_TABLE['name'], $CREATE_TABLE['row']);

            if($this->db->editCollection($CREATE_TABLE) === false){
                throw new Exception('Невозможно изменить таблицу');
            }

        }else{ // Если нет, создаем
            if($this->db->createCollection($CREATE_TABLE) === false){
                throw new Exception('Невозможно создать таблицу');
            }

            $CREATE_TABLE['row'] = array('add' => $CREATE_TABLE['row'], 'change' => array(), 'drop' => array());
        }

        return $CREATE_TABLE;
    }

    public function removeObject(){
        $get = func_get_arg(1);
        $id = (int) $get['id'];
        $name = $get['name'];

        $this->db->remove($this->GLOB['namespace']['struct_db'], array('id' => $id));
        $this->db->remove($this->GLOB['namespace']['struct_td'], array('parent' => $id));
        $this->db->remove_table($name);

        if($this->isSelfMethod()){
            load_url();
        }else{
            return $get;
        }
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

            if(isset($row[$v['Field']])){ // Change
                if(isset($row[$v['Field']]['remove_row']) && $row[$v['Field']]['remove_row'] == 1){
                    unset($row[$v['Field']]);
                    continue;
                }
                $result['change'][] = $row[$v['Field']];
                unset($row[$v['Field']]);
            }else{ // Remove
                $result['drop'][] = $v['Field'];
            }

        }
        foreach($row as $k => $v){ // Add

            $result['add'][] = $v;
            unset($row[$k]);

        }

        return $result;
    }

    public function ckparam($post){

        $this->config['ckeditor_config'] = $post['param'];
        $this->io->write(root.'/config.php', '<? return $config = ' . $this->io->arrayToString($this->config) . '; ?>');

        if($this->isSelfMethod()){
            load_url();
        }else{
            return $this->config;
        }
    }

    public function clearSystemMessage (){
        $array = array();
        foreach($_SESSION['error'] as $v){
            if($v['id'] != $_POST['id']) $array[] = $v;
        }
        $_SESSION['error'] = $array;
    }

    public function search($post){
        $val = $post['value'];

        $result = array();


        if(is_numeric($val)){
            $this->db->search($this->GLOB['namespace']['construct_db'], array(
                'id' => (int) $val,
                'chpu' => $val
            ));
        }
        foreach($this->db->find($this->GLOB['namespace']['struct_db']) as $v){
            $table = $v['code'];

            $row = $this->db->extract($this->db->go(array(
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

            $find = $this->db->search($table, $where);
            if(count($find) > 0){
                $result[] = array(
                    'name' => $v['name'],
                    'code' => $v['code'],
                    'find' => $find
                );
            }
        }

        if($this->isSelfMethod()){
            header('Content-type: text/json');
            header('Content-type: application/json');
            echo json_encode($result);
        }else{
            return $result;
        }
    }


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
                $folder = root . '/' . $this->config['folder']['template'] . '/' . $v . '/';
                $file =  $folder . $name . '.' . $v;

                if(!file_exists ($folder))
                    $this->io->create_dir($folder);
                if(!file_exists ($file))
                    $this->io->create_file($file);
            }
        }
    }


    public function removeTemplate($post, $get){
        $id = (int) $get['id'];
        $this->db->remove($this->GLOB['namespace']['template'], array('id' => $id));
    }


    public function addMarker($post){
        $name = trim($post['name']);
        $find = $this->db->find($this->GLOB['namespace']['marker'], array('name' => $name));
        if(count($find) > 0) throw new Exception('Макер с таким именем уже существует');

        if(isset($_POST['id'])){
            $this->db->update($this->GLOB['namespace']['marker'], array( 'name' => $name ), array('id' => (int) $post['id']));
        }else{
            $this->db->insert($this->GLOB['namespace']['marker'], array( 'name' => $name ));
        }
    }

    public function markerAddTemplate ($post) {
        $marker = $post['marker'];
        $id = isset($post['id']) ? implode(',', $post['id']) : '';
        $this->db->update($this->GLOB['namespace']['marker'], array('template_id' => $id), array('id' => $marker));
        load_url();
    }

    public function removeMarker($post, $get) {
        $id = (int) $get['id'];
        $this->db->remove($this->GLOB['namespace']['marker'], array('id' => $id));
        load_url();
    }

    public function getItemObject($post) {
        $key = (int) $post['index'];
        echo $this->io->buffer(sys . '/template/tpl/template/object_item.html', array(
            'io' => $this->io,
            'key' => $key,
            'value' => array(),
            'manifest' => $this->manifest
        ));
    }

    public function getGist ($post){
        $type = isset($post['type']) && $post['type'] !== '' ? $post['type'] : 'string';
        $key = (int) $post['key'];
        $path = sys.'/template/tpl/gist-param/';
        if(isset($this->manifest['gist'][$type])){
            $res = array();
            foreach($this->manifest['gist'][$type]['param'] as $v){
                $file = $path . $v . '.html';
                if(file_exists($file))
                    $res[] = $this->io->buffer($file, array(
                        'key' => $key,
                        'manifest' => $this->manifest
                    ));
            }
            echo implode('', $res);
        }
    }

    public function removeElem($post, $get){
        removeElem($this->db, $this->io, $this->GLOB, $this->config, (int) $get['id']);

        if(isset($_SESSION['back_param']['id']) && $_SESSION['back_param']['id'] == $get['id']){
            load_url (rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . '/?mode=project');
            exit();
        }

        setSystemMessage('good', 'Елеменет был удален');
        load_url();
    }

    public function removeItem($post){
        foreach($post['id'] as $v){
            removeElem($this->db, $this->io, $this->GLOB, $this->config, (int) $v);
        }
    }

    public function dubleItem($post){
        $parent = (int) $post['parent'];
        foreach($post['id'] as $v){
            copyElem($this->db, $this->io, $this->GLOB, $this-> config, (int) $v, $parent);
        }

        setSystemMessage('good', 'Элемент успешно скопирован в текущую деррикторию');
    }

    public function copyItem () {
        $_SESSION['moveItem'] = array();
        $_SESSION['copyItem'] = $_POST['id'];
        setSystemMessage('good', 'Элемент успешно скопирован в текущую деррикторию');
    }

    public function moveItem() {
        $_SESSION['copyItem'] = array();
        $_SESSION['moveItem'] = $_POST['id'];
    }

    public function pasteItem($post, $get) {
        $parent = (int) $get['id'];
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
                $num = count($this->db->find($this->GLOB['namespace']['construct_db'], array('parent' => $parent)));
                $this->db->update($this->GLOB['namespace']['construct_db'], array('parent' => $parent, 'num' => $num, 'date' => time()), array('id' => (int) $v));
            }else{
                copyElem($this->db, $this->io, $this->GLOB, $this->config, (int) $v, $parent);
            }
        }
        load_url();
    }

    public function pasteItemLink($post, $get) {
        $parent = (int) $get['id'];
        if(isset($_SESSION['copyItem']) && count($_SESSION['copyItem'])){
            $id = $_SESSION['copyItem'];
            $_SESSION['copyItem'] = array();
        }else if(isset($_SESSION['moveItem']) && count($_SESSION['moveItem'])){
            $id = $_SESSION['moveItem'];
            $_SESSION['moveItem'] = array();
        }else{
            throw new Exception();
        }

        $num = count($this->db->find($this->GLOB['namespace']['construct_db'], array('parent' => $parent)));

        foreach($id as $v){
            $object = $this->db->find($this->GLOB['namespace']['construct_db'], array('id' => $v));
            $object = $object[0];

            // Ссылки нельзя копировать
            if((int) $object['ref'] > 0) continue;

            unset($object['id']);
            $object['parent'] = $parent;
            $object['ref'] = $v;
            $object['num'] = $num;
            $object['date'] = time();
            $this->db->insert($this->GLOB['namespace']['construct_db'], $object);
        }
        load_url();
    }

    public function sortElem($post) {
        $id = $post['id'];

        foreach($id as $k => $v){
            $this->db->update($this->GLOB['namespace']['construct_db'], array('num' => $k), array('id' => $v));
        }
    }

    public function hideElement($post, $get){
        $id = $get['id'];
        $this->db->update($this->GLOB['namespace']['construct_db'], array('hide' => (int) $get['hide']), array('id' => $id));
        load_url();
    }

    public function loadTable($post) {
        $count = (int) $post['count'];
        $table = $post['table'];

        $result = $this->db->extract($this->db->go(array(
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
    }

    public function editRowInTable($post) {
        $where = array();
        $update = array();

        foreach ($_POST['row'] as $v) {
            $where[$v['name']] = $v['defValue'];
            $update[$v['name']] = $v['newValue'];
        }

        $this->db->update($post['table'], $update, $where);

        echo 'Строка изменена';
    }

    public function elem($post, $get, $FILES) {
        require_once sys . '/plugin/class.upload/class.upload.php';

        $file = isset($FILES['form']) ? $FILES['form'] : array();
        $form = isset($post['form']) ? $post['form'] : array();


        $activePath = (isset($post['active_path']) && $post['active_path'] != '') ? 1 : 0;

        $updateId = (isset($post['id']) && $post['id'] != '') ? (int) $post['id'] : false;
        if($updateId !== false){
            $this->db->update($this->GLOB['namespace']['construct_db'], array(
                'chpu' => $post['chpu'],
                'active_path' => $activePath,
                'marker' => $post['marker'],
                'date' => time()
            ), array('id' => $updateId));


            $ref = $this->db->find($this->GLOB['namespace']['construct_db'], array('ref' => $updateId));
            foreach($ref as $v){
                $this->db->update($this->GLOB['namespace']['construct_db'], array(
                    'chpu' => $post['chpu'],
                    'active_path' => $post['active_path'],
                    'marker' => $post['marker'],
                    'date' => time()
                ), array('id' => $v['id']));
            }

            $lastId = $updateId;
        }else{
            $num = count($this->db->find($this->GLOB['namespace']['construct_db'], array( 'parent' => (int) $post['parent'] )));
            $this->db->insert($this->GLOB['namespace']['construct_db'], array(
                'parent' => (int) $post['parent'],
                'ref' => '',
                'object' => $post['object'],
                'chpu' => $post['chpu'],
                'num' => $num,
                'active_path' => $activePath,
                'marker' => $post['marker'],
                'date' => time()
            ));
            $form['id'] = $lastId = $this->db->lastID();
        }

        $table = $this->db->find($this->GLOB['namespace']['struct_db'], array('code' => $post['object']));
        $table = $table[0];

        $row = $this->db->find($this->GLOB['namespace']['struct_td'], array( 'parent' => $table['id'] ));
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
                $form[$k] = $this->db->esc($v);
            }
        }

        $path = root . '/' . $this->config['folder']['files'] . '/' . $lastId . '/';
        if(isset($file['tmp_name'])){
            foreach ($file['tmp_name'] as $k => $v){
                $savePath = '/' . $this->config['folder']['files'] . '/' . $lastId . '/';
                $fileName = (isset($form[$k]['name']) && $form[$k]['name'] != '') ? trim($form[$k]['name']) : $k;
                $gist = $rows[$k];

                $fn = (isset($form[$k]['url']) && $form[$k]['url'] != '') ? $form[$k]['url'] : $v['file'];
                $mime = $this->io->mime($file['name'][$k]['file'] != '' ? $file['name'][$k]['file'] : $form[$k]['url']);

                if(isset($form[$k]['url']) && $form[$k]['url'] !== ''){
                    $this->io->create_file(root. '/' . 'temporary.tmp');
                    $this->io->in_file(root. '/' . 'temporary.tmp', file_get_contents($fn));
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

        if($updateId !== false){
            $this->db->update($post['object'], $form, array('id' => $updateId));
        }else{
            $this->db->insert($post['object'], $form);
        }
        load_url();
    }
}
