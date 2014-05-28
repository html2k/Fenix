<?

class Lib {

    protected $db_rows = array();

    public function copyItem($parent, $id){

        $tables = Fx::db()->find(Fx::service_context()->namespace['struct_db']);
        $rows = Fx::db()->find(Fx::service_context()->namespace['struct_td']);
        $new_rows = array();

        foreach($rows as $v){
            if(!isset($new_rows[$v['parent']])){
                $new_rows[$v['parent']] = array();
            }

            $new_rows[$v['parent']][] = $v;
        }

        foreach($tables as $v){
            if(!isset($this->db_rows[$v['code']])){
                $this->db_rows[$v['code']] = array();
            }

            $v['rows'] = isset($new_rows[$v['id']]) ? $new_rows[$v['id']] : array();


            $this->db_rows[$v['code']] = $v;
        }


        $this->copyItem_($parent, $id);
    }

    private function copyItem_($parent, $id){
        $CONSTRUCT = Fx::service_context()->namespace['construct_db'];

        $copy_object = Fx::db()->findOne($CONSTRUCT, array( 'id' => $id ));
        $copy_item = Fx::db()->findOne($copy_object['object'], array( 'id' => $copy_object['id'] ));
        $rows = $this->db_rows[$copy_object['object']]['rows'];
        $back_id = $copy_object['id'];

        unset($copy_object['id']);
        $copy_object['parent'] = $parent;
        $copy_object['num'] = count(Fx::db()->find($CONSTRUCT, array('parent' => $parent)));
        Fx::db()->insert($CONSTRUCT, $copy_object);

        Fx::service_context()->copyId = $copy_item['id'] = Fx::db()->lastId();
        $this->copyFolderElement_($back_id, Fx::service_context()->copyId);

        foreach($rows as $v){
            if(in_array($v['type'], array('file', 'image'))){

                if(!isset($copy_item[$v['code']]))
                    continue;

                if(empty($copy_item[$v['code']]))
                    continue;

                $file = explode('/', $copy_item[$v['code']]);
                $file = array_pop($file);
                $file = '/' . Fx::service_context()->config['folder']['files'] . '/' . Fx::service_context()->copyId . '/' . $file;

                $copy_item[$v['code']] = file_exists(root.$file) ? $file : '';
            }
        }

        Fx::db()->insert($copy_object['object'], $copy_item);

        Fx::db()->getList(array('parent' => $id), function($item){
            Fx::lib()->copyItem(Fx::service_context()->copyId, $item['id']);
        });
    }

    private function copyFolderElement_($from, $to){
        $pathFrom = Fx::io()->path( root, Fx::service_context()->config['folder']['files'], $from );
        $pathTo = Fx::io()->path( root, Fx::service_context()->config['folder']['files'],  $to );

        if(is_dir ($pathFrom)){
            Fx::io()->copy($pathFrom, $pathTo);
        }
    }


    public function rewrite(){
        if(strlen($_SERVER['REQUEST_URI']) > 1){
            $this->saveHistoryLink();

            $url = $_SERVER['REQUEST_URI'];
            if(substr($url, -1) != '/'){
                $url .= '/';
            }

            $find = Fx::db()->findOne(Fx::service_context()->namespace['rewrite'], array(
                'from' => $url
            ));

            if($find){
                if(empty($find['to'])){
                    throw new Exception('Fuck', (int)$find['code']);
                }else{
                    $header_to = $find['to'];
                    if(strlen($header_to) < 2){
                        $protocol = strpos($_SERVER['SERVER_PROTOCOL'], 'HTTPS') !== false ? 'https' : 'http';
                        $header_to = $protocol.'://' . ((isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : $_SERVER['HTTP_HOST']) . '/';
                    }
                    header('Location: ' . $header_to, true, $find['code']);
                    exit;
                }
            }

        }
    }

    private function saveHistoryLink(){
        $history_link = Fx::db()->find(Fx::service_context()->namespace['history-link'], array(
            'link' => $_SERVER['REQUEST_URI']
        ));

        if(!count($history_link)){
            Fx::db()->insert(Fx::service_context()->namespace['history-link'], array(
                'link' => $_SERVER['REQUEST_URI']
            ));
        }
    }

}