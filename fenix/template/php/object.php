<?
    $leftMenu = Fx::db()->find(Fx::app()->namespace['struct_db']);
    $text = array('<h3>Объекты</h3><ul>');
    foreach($leftMenu as $v){
        $active = (isset($_GET['id']) && $_GET['id'] == $v['id']) ? ' class="active"' : '';
        $text[] = '<li'.$active.'><a href="?mode=object&id='.$v['id'].'">'.$v['name'].'</a></li>';
    }
    Fx::app()->leftMenu .= implode("\n", $text) . '</ul>';

    Fx::app()->leftMenu .= '<a href="?mode=object" class="btn mt_mini"><span class="btn-in">Создать объект</span></a>';
    
    
    // Текущий объект
    $paramObject = '';
    if(isset($_GET['id']) && is_numeric($_GET['id'])){
        $id = $_GET['id'];
        $objectParam = Fx::db()->find(Fx::app()->namespace['struct_db'], array('id' => $id));
        $td = Fx::db()->find(Fx::app()->namespace['struct_td'], array( 'parent' => $id ));

        if(is_array($objectParam) && is_array($td) && count($objectParam) && count($td)){
            $len = count($td);
            $paramObject = array();
            foreach ($td as $k => $v){

                $paramObject[] = Fx::io()->buffer(sys . '/template/tpl/template/object_item.html', array(
                    'io' => $io,
                    'key' => $k,
                    'value' => $v,
                    'zIndex' => $len - $k,
                    'manifest' => $manifest
                ));

            }
            $paramObject = implode("\n", $paramObject);
        }else{
            throw new Exception('not found', 404);
        }
    }
    