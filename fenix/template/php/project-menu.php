<?  
    $path = loadPath($db, $GLOB, $GLOB['self_id']);
    
    // Левое меню
    $GLOB['leftMenu'] = '<h3 onclick="window.location = \'?mode=project\'">Структура проекта</h3>';
    // Формируем левое меню
    class Path extends Base{
            public $path;
            public $ns;
            public $result = array();
            public $struct;

            function __construct($config, $path, $namespace){
                    parent::init($config);

                    $this->path = $path;
                    $this->ns = $namespace;


                    $t = $this->find($this->ns['struct_db']);
                    foreach($t as $v){
                            $this->struct[$v['code']] = $v;
                    }
                    

                    $this->rec(0);
                    $this->result = implode('', $this->result);
            }

            public function find($a, $b = array()) {
                return parent::find($a, $b);
            }

            public function perform($a, $b) {
                return parent::extract(
                    parent::go(array(
                        'event' => 'find',
                        'from' => $a,
                        'where' => $b,
                        'order' => 'num'
                        )
                    )
                );
            }

            private function rec($id){
                    $find = $this->perform($this->ns['construct_db'], array('parent' => $id));
                    foreach($find as $v){
                            $active = (isset($this->path[0]) && $this->path[0]['id'] == $v['id']) ? ' class="active"' : '';

                            if($active == '' && $this->struct[$v['object']]['show_wood'] < 1) continue;

                            $elem = $this->find($v['object'], array('id' => $v['id']));
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
    $leftMenu = new Path($config['db'], $path, $GLOB['namespace']);
    $GLOB['leftMenu'] .= '<ul class="project-menu-list">' . $leftMenu->result . '</ul>';
    
    // Добавление елемента
    $objectList = $db->find($GLOB['namespace']['struct_db']);
    $leftMenu = array();
    $leftMenu[] = '<div class="dropdown">';
    $leftMenu[] = '<span class="dropdown-name btn"><span class="btn-in">Создать</span></span>';
    $leftMenu[] = '<span class="dropdown-block">';
    $leftMenu[] = '<i class="dropdown-tail"></i>';
    $leftMenu[] = '<ul class="project-list-created">';
    
    $elemId = isset($_GET['id']) ? '&parent='.$_GET['id'] : '';
    //$elemId = isset($_GET['parent']) ? '&parent='.$_GET['parent'] : '';
    $createList = array();
    $previewList = array();
    foreach($objectList as $v){
        $previewList[] = '<a href="?mode=elem&name='.$v['code'].$elemId.'" class="previewList">';
            $previewList[] = '<i class="'.$v['icon'].'"></i>';
            $previewList[] = $v['name'];
        $previewList[] = '</a>';
        $createList[] = '<li><a href="?mode=elem&name='.$v['code'].$elemId.'"><i class="'.$v['icon'].'"></i>'  .$v['name'].'</a></li>';
    }
    $createList = implode('', $createList);
    $previewList = implode('', $previewList);
    $leftMenu[] = $createList;
    $leftMenu[] = '</ul>';
    $leftMenu[] = '</div>';
    $GLOB['leftMenu'] .= implode('', $leftMenu);



    // Формируем хлебные крошки
    $crumbs = array();
    foreach($path as $v){
        $find = $db->find($v['object'], array('id' => $v['id']));
        $crumbs[$v['id']] = isset($find[0]['name']) &&  $find[0]['name'] != '' ? $find[0]['name'] : 'undefiend-'.$v['id'];
    }