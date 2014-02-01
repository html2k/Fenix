<?

    $GLOB['self_id'] = (isset($_GET['id'])) ? (int) $_GET['id'] : false;
    $GLOB['project_name'] = $config['project_name'];
    
    // Левое меню
    require_once sys . '/template/php/project-menu.php';
    
    $tables = $db->find($GLOB['namespace']['struct_db']);
    $rowslist = $db->find($GLOB['namespace']['struct_td']);
    $selfItem = $db->extract($db->go(array(
        'event' => 'find',
        'from' => $GLOB['namespace']['construct_db'],
        'where' => array('parent' => $GLOB['self_id']),
        'order' => 'num'
    )));

    $rows = array();
    foreach($rowslist as $k => $v){
        if(!isset($rows[$v['parent']])){
            $rows[$v['parent']] = array();
        }
        $rows[$v['parent']][$v['code']] = $v;
    }

    $tabl = array();
    foreach($tables as $v){
        if(isset($rows[$v['id']])){
            $v['rows'] = $rows[$v['id']];
        }
        $tabl[$v['code']] = $v;

    }

    $selfList = array();
    $selfList[] = '<ul class="js-sortable-list">';
    foreach ($selfItem as $k => $j){
        $id = ($j['ref'] > 0) ? $j['ref'] : $j['id'];
        $item = $db->find($j['object'], array('id' => $id));

        $selfList[] = $io->buffer(sys . '/template/tpl/blocks/project-list-item.html', array(
            'index' => $k + 1,
            'id' => $id,
            'ref' => $j['ref'],
            'num' => $j['num'],
            'object' => $j,
            'date' => date('d.m.Y G:i (s)', $j['date']),
            'data' => $item[0],
            'name' => (isset($item[0]['name']) && $item[0]['name'] != '') ? $item[0]['name'] : 'undefiend-'.$j['id'],
            'hide-icon' => $j['hide'] == 1 ? 'icon-eye-off' : 'icon-eye',
            'icon' => $tabl[$j['object']]['icon'],
            'data-list' => $tabl[$j['object']]['rows']
        ));
    }
    $selfList[] = '</ul>';