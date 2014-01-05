<?
    $GLOB['self_id'] = (isset($_GET['id'])) ? (int) $_GET['id'] : false;
    $GLOB['parent_object'] = isset($_GET['parent']) ? $_GET['parent'] : '';
    
    if($GLOB['self_id'] == false && $GLOB['parent_object'] != '') $GLOB['self_id'] = $GLOB['parent_object'];
    
    
    $pathGist = sys.'/template/tpl/gist-elem/';
    $tablParam = array();

    $tableName = isset($_GET['name']) ? $_GET['name'] : '';
    $objectValue = array();
    
    if(isset($_GET['id']) && (int) $_GET['id'] > 0){
        $selfObject = $db->find($GLOB['namespace']['construct_db'], array( 'id' => (int) $_GET['id']));
        if(count($selfObject)){
            $tableName = $selfObject[0]['object'];
            
            $objectValue = $db->find($tableName, array('id' => $selfObject[0]['id']));
            $objectValue = $objectValue[0];
        }
    }
    
    if($tableName != ''){
        $tabl = $db->find($GLOB['namespace']['struct_db'], array( 'code' => $tableName));
        $tablParam = $tabl[0];

    }else{
        setError('error', 'Не заданы параметры для редактирования');
        load_url();
    }
    
    $row = $db->find($GLOB['namespace']['struct_td'], array( 'parent' => $tablParam['id']));

    $result = array();
    foreach($row as $key => $v){
        $result[] = loadParam($key, array(
            'row' => $v,
            'value' => isset($objectValue[$v['code']]) ? $objectValue[$v['code']] : '',
        ), $manifest, $pathGist . $v['type'] . '.html');
    }
    
    // Шаблоны
    $template = $db->find($GLOB['namespace']['marker']);
    
    // Левое меню
    require_once sys . '/template/php/project-menu.php';