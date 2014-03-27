<?

function access(){
    $arg = func_get_args();
    if(count($arg)){
        $res = false;
        foreach($arg as $access){
            if((int)$_SESSION['user']['access'] === $access){
                $res = true;
            }
        }
        if(!$res){
            throw new Exception('Доступ запрещен', 403);
        }
    }
}

function load_url($url = false){
    // Переход на указанный url

    if($url === false){
        $url = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : $_SERVER['HTTP_HOST'];
    }
    header("Location:". $url);
}

function debug(){
    echo '<pre>';
    $arg = func_get_args();
    foreach($arg as $v){
        var_dump($v);
    }
    die();
}

function req($config, $path){
    $path = root . '/' . $config['folder']['sys'] . $path;
    
    if(file_exists($path))
        require_once $path;
    else
        return false;
}

function byteConvert($bytes){
    $symbols = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
    $exp = floor(log($bytes)/log(1024));

    return sprintf('%.2f '.$symbols[$exp], ($bytes/pow(1024, floor($exp))));
}

function setSystemMessage($name, $error){
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
    $str = file(sys . '/template/font/config.json');
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


function loadPath($id){
    $result = array();
    while(true){
        if($id == 0) return $result;
        $find = Fx::db()->find(Fx::app()->namespace['construct_db'], array('id' => $id));
        if(!count($find)) return $result;
        $id = $find[0]['parent'];
        array_unshift($result, array('id' => $find[0]['id'], 'object' => $find[0]['object'], 'ref' => $find[0]['ref']));
    }
}

function removeElem($id, $object = ''){
    while (true){
        if($object == ''){
            $object = Fx::db()->find(Fx::app()->namespace['construct_db'], array('$or' => array('id' => $id, 'ref' => $id)));
        }
        Fx::db()->remove($object[0]['object'], array('id' => $id));
        Fx::db()->remove(Fx::app()->namespace['construct_db'], array('id' => $id));
        $path = root . '/' . Fx::app()->config['folder']['files'] . '/' . $id . '/';
        Fx::io()->del($path);
        
        $find = Fx::db()->find(Fx::app()->namespace['construct_db'], array('parent' => $id));
        if(count($find)){
            foreach($find as $v){
                removeElem($v['id'], array($v));
            }
        }else{
            break;
        }
    }
}

function copyElem($id, $parent){
    $object = Fx::db()->find(Fx::app()->namespace['construct_db'], array('id' => $id));
    
    $object = $object[0];
    unset($object['id']);
    $object['num'] = count(Fx::db()->find(Fx::app()->namespace['construct_db'], array('parent' => $parent)));
    $object['parent'] = $parent;
    $object['date'] = time();
    
    $table = Fx::db()->find(Fx::app()->namespace['struct_db'], array('code' => $object['object']));
    $rows = Fx::db()->find(Fx::app()->namespace['struct_td'], array('parent' => $table[0]['id']));
    
    Fx::db()->insert(Fx::app()->namespace['construct_db'], $object);
    
    $newId = Fx::db()->lastId();
    $elem = Fx::db()->find($object['object'], array('id' => $id));
    $elem = $elem[0];
    $elem['id'] = $newId;
    
    $pathFrom = root . '/' . Fx::app()->config['folder']['files'] . '/' . $id . '/';
    $pathTo = root . '/' . Fx::app()->config['folder']['files'] . '/' . $newId . '/';
    if(is_dir ($pathFrom))
        Fx::io()->copy($pathFrom, $pathTo);
    
    foreach($rows as $v){
        if($v['type'] == 'file' || $v['type'] == 'image'){
            $elemFile = explode('/', $elem[$v['code']]);
            $elemFile = array_pop($elemFile);
            $elem[$v['code']] = '/' . Fx::app()->config['folder']['files'] . '/' . $newId . '/' . $elemFile;
        }
    }
    
    
    Fx::db()->insert($object['object'], $elem);
    
    $child = Fx::db()->find(Fx::app()->namespace['construct_db'], array('parent' => $id));
    foreach($child as $v){
        copyElem(Fx::app()->config, $v['id'], $newId);
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


function staticLoad($static, $debug = false, $url = ''){
    $result = array();
    if($debug){
        foreach($static->getList('js', true) as $v){
            $result[] = '<script src="'.$url.$v.'" type="text/javascript"></script>';
        }
        foreach($static->getList('css', true) as $v){
            $result[] = '<link rel="stylesheet" type="text/css" href="'.$url.$v.'">';
        }
    }else{
        $result[] = '<link rel="stylesheet" type="text/css" href="'. $url . $static->get('css') .'?v='.$static->getVersion().'">';
        $result[] = '<script src="'.$url . $static->get('js') .'?v='.$static->getVersion().'" type="text/javascript"></script>';
    }

    return implode($result);
}