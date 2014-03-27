<?
    Fx::app()->selfId = (isset($_GET['id'])) ? (int) $_GET['id'] : false;
    Fx::app()->parent_object = isset($_GET['parent']) ? $_GET['parent'] : '';
    
    if(Fx::app()->selfId == false && Fx::app()->parent_object != '') Fx::app()->selfId = Fx::app()->parent_object;
    
    
    $pathGist = sys.'/template/tpl/gist-elem/';
    $tablParam = array();

    $tableName = isset($_GET['name']) ? $_GET['name'] : '';
    $objectValue = array();
    
    if(isset($_GET['id']) && (int) $_GET['id'] > 0){
        $selfObject = Fx::db()->find(Fx::app()->namespace['construct_db'], array( 'id' => (int) $_GET['id']));
        if(count($selfObject)){
            $tableName = $selfObject[0]['object'];
            
            $objectValue = Fx::db()->find($tableName, array('id' => $selfObject[0]['id']));
            $objectValue = $objectValue[0];
        }
    }
    
    if($tableName != ''){
        $tabl = Fx::db()->find(Fx::app()->namespace['struct_db'], array( 'code' => $tableName));
        $tablParam = $tabl[0];

    }else{
        setSystemMessage('error', 'Не заданы параметры для редактирования');
        load_url();
    }
    
    $row = Fx::db()->find(Fx::app()->namespace['struct_td'], array( 'parent' => $tablParam['id']));

    $result = array();
    foreach($row as $key => $v){
        $result[] = loadParam($key, array(
            'row' => $v,
            'value' => isset($objectValue[$v['code']]) ? $objectValue[$v['code']] : '',
        ), $manifest, $pathGist . $v['type'] . '.html');
    }
    
    // Шаблоны
    $template = Fx::db()->find(Fx::app()->namespace['marker']);
    
    // Левое меню
    require_once sys . '/template/php/project-menu.php';