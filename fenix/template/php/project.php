<?

    $GLOB['self_id'] = (isset($_GET['id'])) ? (int) $_GET['id'] : false;
    $GLOB['project_name'] = $config['project_name'];
    
    // Левое меню
    require_once sys . '/template/php/project-menu.php';
    
    $tables = $db->find($GLOB['namespace']['struct_db']);
    $selfItem = $db->extract($db->go(array(
        'event' => 'find',
        'from' => $GLOB['namespace']['construct_db'],
        'where' => array('parent' => $GLOB['self_id']),
        'order' => 'num'
    )));

    // Формируем хлебные крошки
    $crumbs = [];
    foreach($path as $v){
        $find = $db->find($v['object'], array('id' => $v['id']));
        $crumbs[$v['id']] = isset($find[0]['name']) &&  $find[0]['name'] != '' ? $find[0]['name'] : 'undefiend-'.$v['id'];
    }


//
//    $list = array();
//    foreach ($selfItem as $v){
//        if(!isset($list[$v['object']])) $list[$v['object']] = array();
//        $list[$v['object']][] = $v;
//    }


    $tabl = array();
    foreach($tables as $v){ $tabl[$v['code']] = $v; }

    $selfList = array();
    $selfList[] = '<ul>';

    foreach ($selfItem as $k => $j){
        $id = ($j['ref'] > 0) ? $j['ref'] : $j['id'];
        $item = $db->find($j['object'], array('id' => $id));
        $name = (isset($item[0]['name']) && $item[0]['name'] != '') ? $item[0]['name'] : 'undefiend-'.$j['id'];

        $eye = $j['hide'] == 1 ? 'icon-eye-off' : 'icon-eye';

        $selfList[] = '<li class="box" data-id="'.$j['id'].'" data-code="'.$j['object'].'">';
        $selfList[] = '<i class="'.$tabl[$j['object']]['icon'].'"></i>';
        $selfList[] = '<b class="project-list-num">'.($k +1).'</b>';
        $selfList[] = '<a class="name" href="?mode=project&id='.$id.'">'.$name.'</a>';
        $selfList[] = '<span class="param-list pull-right">';
            $selfList[] = '<a href="?mode=elem&name='.$j['object'].'&id='.$j['id'].'"><i class="icon-pencil"></i></a>';
            $selfList[] = '<a href="?action=hideElement&id='.$j['id'].'&hide='.(int) !$j['hide'].'"><i class="'.$eye.'"></i></a>';
            $selfList[] = '<a href="?action=removeElem&id='.$j['id'].'"><i class="icon-cancel"></i></a>';
        $selfList[] = '</span>';
        $selfList[] = '</li>';
    }

    $selfList[] = '</ul>';