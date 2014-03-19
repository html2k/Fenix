<?

    Fx::app()->selfId = (isset($_GET['id'])) ? (int) $_GET['id'] : false;

    if(Fx::app()->selfId && !count(Fx::db()->find(Fx::app()->namespace['construct_db'], array('id' => Fx::app()->selfId)))){
        throw new Exception('not found', 404);
    }

    // Левое меню
    require_once sys . '/template/php/project-menu.php';
    
    $tables = Fx::db()->find(Fx::app()->namespace['struct_db']);
    $rowslist = Fx::db()->find(Fx::app()->namespace['struct_td']);
    $selfItem = Fx::db()->extract(Fx::db()->go(array(
        'event' => 'find',
        'from' => Fx::app()->namespace['construct_db'],
        'where' => array('parent' => Fx::app()->selfId),
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

        $item = Fx::db()->findOne($j['object'], array('id' => $id));

        if(is_array($item)){
            $selfList[] = Fx::io()->buffer(sys . '/template/tpl/blocks/project-list-item.html', array(
                'index' => $k + 1,
                'id' => $id,
                'ref' => $j['ref'],
                'num' => $j['num'],
                'object' => $j,
                'date' => date('d.m.Y G:i (s)', $j['date']),
                'data' => $item,
                'name' => (isset($item['name']) && $item['name'] != '') ? $item['name'] : 'undefiend-'.$j['id'],
                'hide-icon' => $j['hide'] == 1 ? 'icon-eye-off' : 'icon-eye',
                'icon' => $tabl[$j['object']]['icon'],
                'data-list' => $tabl[$j['object']]['rows']
            ));
        }
    }
    $selfList[] = '</ul>';