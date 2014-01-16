<?

function load_url($url = false){
    // Переход на указанный url
    if($url === false) $url = $_SERVER['HTTP_REFERER'];
    else{
        $ht = explode(':', $_SERVER['HTTP_REFERER']);
        $url = $ht[0] . '://' . $_SERVER['HTTP_HOST'] . $url;
    }
    header("Location:". $url);
}

function debug($var, $bool = false){
    if(!$bool) echo '<pre>';
    var_dump($var);
    if(!$bool) die();
}

function req($config, $path){
    $path = root . '/' . $config['folder']['sys'] . $path;
    
    if(file_exists($path))
        require_once $path;
    else
        return false;
}

function byteConvert($size){
    $size = (int) $size;
    if($size > 1048576){
            return sprintf("%01.2f", $size / 1048576) . 'Мб';
    }else{
            return sprintf("%01.2f", $size / 1024) . 'Кб';
    }
}

function setError($name, $error){
    if(!isset($_SESSION['error'])) $_SESSION['error'] = array();
    $_SESSION['error'][] = array(
        'name' => $name,
        'error' => $error,
        'id' => sha1(time() + count($_SESSION['error']))
    );
}

function hashGenerate($string){
    $note_hash = array(
        'note' => array(
            'X3-.!','-U*1','9#+S','.=6A','..(&',
            '^&~@','=+_-','69%$','#"`7','1-9&',
            'F=*#','36^:','|\|/','%##?','№`08',
            '5@*&','0(8^','~!?<','>*&%','/?>,'
            ),
        'first' => '!-_-=45AsY*',
        'last' => '<-@%dHa.|'
        );
    $string = trim($string);
    $string = base64_encode($string);
    $string = convert_uuencode($string);
    $arr = str_split($string, 2);
    $note = $note_hash['note'];

    $str = $note_hash['first'];
    foreach($arr as $k => $v){
        $str .=	$note[$k].$v;
    }
    $str .= $note_hash['first'];
    $len = strlen($str);
    $arr = str_split($str, ($len / 2));
    $key = sha1($arr[1]).sha1($arr[0]);

    return $key;
}

function getIcon(){
    $str = file(sys . '/template/css/font/config.json');
    return json_decode(implode('', $str), true);
}

function loadParam($key, $paramObjectItem, $manifest, $tpl){
    $result = '';
    ob_start();
        if(file_exists($tpl)) require $tpl;
        $result = ob_get_contents();
    ob_end_clean();
    return $result;
}

function loadPath($db, $GLOB, $id){
    $result = array();
    while(true){
        if($id == 0) return $result;
        $find = $db->find($GLOB['namespace']['construct_db'], array('id' => $id));
        $id = $find[0]['parent'];
        array_unshift($result, array('id' => $find[0]['id'], 'object' => $find[0]['object'], 'ref' => $find[0]['ref']));
    }
}

function removeElem($db, $io, $GLOB, $config, $id, $object = ''){
    while (true){
        if($object == ''){
            $object = $db->find($GLOB['namespace']['construct_db'], array('$or' => array('id' => $id, 'ref' => $id)));
        }
        $db->remove($object[0]['object'], array('id' => $id));
        $db->remove($GLOB['namespace']['construct_db'], array('id' => $id));
        $path = root . '/' . $config['folder']['files'] . '/' . $id . '/';
        $io->del($path);
        
        $find = $db->find($GLOB['namespace']['construct_db'], array('parent' => $id));
        if(count($find)){
            foreach($find as $v){
                removeElem($db, $io, $GLOB, $config, $v['id'], array($v));
            }
        }else{
            break;
        }
    }
}

function copyElem($db, $io, $GLOB, $config, $id, $parent){
    $object = $db->find($GLOB['namespace']['construct_db'], array('id' => $id));
    
    $object = $object[0];
    unset($object['id']);
    $object['num'] = count($db->find($GLOB['namespace']['construct_db'], array('parent' => $parent)));
    $object['parent'] = $parent;
    $object['date'] = time();
    
    $table = $db->find($GLOB['namespace']['struct_db'], array('code' => $object['object']));
    $rows = $db->find($GLOB['namespace']['struct_td'], array('parent' => $table[0]['id']));
    
    $db->insert($GLOB['namespace']['construct_db'], $object);
    
    $newId = $db->lastId();
    $elem = $db->find($object['object'], array('id' => $id));
    $elem = $elem[0];
    $elem['id'] = $newId;
    
    $pathFrom = root . '/' . $config['folder']['files'] . '/' . $id . '/';
    $pathTo = root . '/' . $config['folder']['files'] . '/' . $newId . '/';
    if(is_dir ($pathFrom))
        $io->copy($pathFrom, $pathTo);
    
    foreach($rows as $v){
        if($v['type'] == 'file' || $v['type'] == 'image'){
            $elemFile = explode('/', $elem[$v['code']]);
            $elemFile = array_pop($elemFile);
            $elem[$v['code']] = '/' . $config['folder']['files'] . '/' . $newId . '/' . $elemFile;
        }
    }
    
    
    $db->insert($object['object'], $elem);
    
    $child = $db->find($GLOB['namespace']['construct_db'], array('parent' => $id));
    foreach($child as $v){
        copyElem($db, $io, $GLOB, $config, $v['id'], $newId);
    }
}

function resize($url, $max){
    // Получить пропорциональные размеры изображения
    /*
     * $url [string] - путь к картинке
     * $max [int] - размер на выходе
    */
    $size = (is_array($url)) ? $url : @getimagesize($url);

    $width = $size[0];
    $height = $size[1];

    if($width > $max){
            $rat = $max / $width;
    }else if($height > $max){
            $rat = $max / $height;
    }

    if($width > $max || $height > $max){
            $width = $rat * $width;
            $height = $rat * $height;
    }
    return array($width, $height, $size[0], $size[1]);
}