<?
    Fx::app()->path = loadPath(Fx::app()->selfId);
    
    // Левое меню
    Fx::app()->leftMenu = '';
    // Формируем левое меню
    class Path extends Base{
        public $result = array();
        public $struct;

        function __construct(){

                $t = Fx::db()->find(Fx::app()->namespace['struct_db']);
                foreach($t as $v){
                        $this->struct[$v['code']] = $v;
                }
                

                $this->rec(0);
                $this->result = implode('', $this->result);
        }

        public function find($a, $b = array(), $callback = null) {
            return Fx::db()->find($a, $b, $callback);
        }

        public function perform($a, $b) {
            return Fx::db()->extract(
                Fx::db()->go(array(
                    'event' => 'find',
                    'from' => $a,
                    'where' => $b,
                    'order' => 'num'
                    )
                )
            );
        }

        private function rec($id){
            $find = $this->perform(Fx::app()->namespace['construct_db'], array('parent' => $id));
            foreach($find as $v){
                $active = (isset($this->path[0]) && $this->path[0]['id'] == $v['id']) ? ' class="active"' : '';

                if($active == '' && $this->struct[$v['object']]['show_wood'] < 1) continue;

                $elem = Fx::db()->find($v['object'], array('id' => $v['id']));
                if(!isset($elem[0])){
                    continue;
                }
                $elem = $elem[0];
                $name = isset($elem['name']) && $elem['name'] !== '' ? $elem['name'] : 'undefiend-'.$v['id'];


                $this->result[] = '<li>';
                $this->result[] = '<a'.$active.' href="?mode=project&id='.$v['id'].'">'.$name.'</a>';

                if($active != ''){
                        $item = array_shift($this->path);
                        if($this->struct[$item['object']]['show_wood'] > 0){
                                $this->result[] = '<ul>';
                                        $this->rec($item['id']);
                                $this->result[] = '</ul>';
                        }
                }

                $this->result[] = '</li>';
            }
        }
    }

    $leftMenu = new Path(Fx::app()->path);

    $objectList = Fx::db()->find(Fx::app()->namespace['struct_db']);
    $elemId = isset($_GET['id']) ? '&parent='.$_GET['id'] : '';

    Fx::app()->create_element_button = Fx::io()->buffer(sys.'/template/tpl/blocks/project/create-element-button.html', array(
        'object' => $objectList,
        'elemID' => $elemId
    ));

    Fx::app()->left_menu_items = array();
    Fx::app()->left_menu_items[] = array(
        'name' => 'Структура проекта',
        'block' => '<ul class="project-menu-list">' . $leftMenu->result . '</ul>' . Fx::app()->create_element_button,
    );

    Fx::app()->leftMenu .= Fx::io()->buffer(sys.'/template/tpl/blocks/project/project-menu.html', Fx::app()->left_menu_items);

    

    // Формируем хлебные крошки
    $crumbs = array();
    foreach(Fx::app()->path as $v){
        $find = Fx::db()->find($v['object'], array('id' => $v['id']));
        $crumbs[$v['id']] = isset($find[0]['name']) &&  $find[0]['name'] != '' ? $find[0]['name'] : 'undefiend-'.$v['id'];
    }