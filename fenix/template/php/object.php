<?
    function getValue($db, $name){
        return (isset($object[$value])) ? $object[$value] : '';
    }
    
    
    
    $leftMenu = $db->find($GLOB['namespace']['struct_db']);
    $text = array('<h3>Объекты</h3><ul>');
    foreach($leftMenu as $v){
        $active = (isset($_GET['id']) && $_GET['id'] == $v['id']) ? ' class="active"' : '';
        $text[] = '<li'.$active.'><a href="?mode=object&id='.$v['id'].'">'.$v['name'].'</a></li>';
    }
    $GLOB['leftMenu'] .= implode("\n", $text) . '</ul>';
    
    $GLOB['leftMenu'] .= '<a href="?mode=object" class="btn mt_mini"><span class="btn-in">Создать объект</span></a>';
    
    
    // Текущий объект
    $paramObject = '';
    if(isset($_GET['id']) && is_numeric($_GET['id'])){
        $id = $_GET['id'];
        $objectParam = $db->find($GLOB['namespace']['struct_db'], array('id' => $id));
        $td = $db->find($GLOB['namespace']['struct_td'], array( 'parent' => $id ));
        $paramObject = array();
        foreach ($td as $k => $v){
            $paramObject[] = loadParam($k, $v, $manifest, sys . '/template/tpl/template/object_item.html');
        }
        $paramObject = implode("\n", $paramObject);
    }
    