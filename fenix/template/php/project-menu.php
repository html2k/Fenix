<?
    Fx::context()->path = loadPath(Fx::context()->selfId);

    $struct_db = Fx::db()->find(Fx::service_context()->namespace['struct_db']);
    Fx::context()->struct_db = array();
    foreach($struct_db as $v){
        Fx::context()->struct_db[$v['code']] = $v;
    }


    // Левое меню
    Fx::context()->leftMenu = '';
    // Формируем левое меню
    class Path{
        public $result = array();

        function __construct($path){
            $this->path = $path;

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
            $find = $this->perform(Fx::service_context()->namespace['construct_db'], array('parent' => $id));
            foreach($find as $v){
                $active = (isset($this->path[0]) && $this->path[0]['id'] == $v['id']) ? ' class="active"' : '';

                if($active == '' && Fx::context()->struct_db[$v['object']]['show_wood'] < 1) continue;

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
                    if(Fx::context()->struct_db[$item['object']]['show_wood'] > 0){
                        $this->result[] = '<ul>';
                            $this->rec($item['id']);
                        $this->result[] = '</ul>';
                    }
                }

                $this->result[] = '</li>';
            }
        }
    }

    $leftMenu = new Path(Fx::context()->path);

    $elemId = isset($_GET['id']) ? '&parent='.$_GET['id'] : '';

    Fx::context()->create_element_button = Fx::io()->buffer(sys.'/template/tpl/blocks/project/create-element-button.html', array(
        'object' => Fx::context()->struct_db,
        'elemID' => $elemId
    ));

    Fx::context()->left_menu_items = array();
    Fx::context()->left_menu_items[] = array(
        'name' => 'Структура проекта',
        'block' => '<ul class="project-menu-list">' . $leftMenu->result . '</ul>' . Fx::context()->create_element_button,
    );

    Fx::context()->leftMenu .= Fx::io()->buffer(sys.'/template/tpl/blocks/project/project-menu.html', Fx::context()->left_menu_items);

    

    // Формируем хлебные крошки
    $crumbs = array();
    foreach(Fx::context()->path as $v){
        $find = Fx::db()->find($v['object'], array('id' => $v['id']));
        $crumbs[$v['id']] = isset($find[0]['name']) &&  $find[0]['name'] != '' ? $find[0]['name'] : 'undefiend-'.$v['id'];
    }