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
    $list = array();
    foreach ($selfItem as $v){
        if(!isset($list[$v['object']])) $list[$v['object']] = array();    
        $list[$v['object']][] = $v;
    }
    
    $selfList = array();
    foreach ($tables as $v){
        if(isset($list[$v['code']])){
            $selfList[] = '<h3>' . $v['name'] . '</h3>';
            $selfList[] = '<ul>';
            foreach ($list[$v['code']] as $k => $j){
                $id = ($j['ref'] > 0) ? $j['ref'] : $j['id'];
                $item = $db->find($v['code'], array('id' => $id));
                $name = (isset($item[0]['name']) && $item[0]['name'] != '') ? $item[0]['name'] : 'undefiend-'.$j['id'];
                
                $eye = $j['hide'] == 1 ? 'icon-eye-off' : 'icon-eye';
                
                
                $selfList[] = '<li class="m-box" data-id="'.$j['id'].'">';
                $selfList[] = '<i class="'.$v['icon'].'"></i>';
                $selfList[] = '<b class="projectList-num">'.($k +1).'</b>';
                $selfList[] = '<a class="name" href="?mode=project&id='.$id.'">'.$name.'</a>';
                $selfList[] = '<span class="param-list pull-right">';
                    $selfList[] = '<a href="?mode=elem&name='.$v['code'].'&id='.$j['id'].'"><i class="icon-pencil"></i></a>';
                    $selfList[] = '<a href="?action=hideElement&id='.$j['id'].'&hide='.(int) !$j['hide'].'"><i class="'.$eye.'"></i></a>';
                    $selfList[] = '<a href="?action=removeElem&id='.$j['id'].'"><i class="icon-cancel"></i></a>';
                $selfList[] = '</span>';
                $selfList[] = '</li>';
            }
            $selfList[] = '</ul>';
        }
    }