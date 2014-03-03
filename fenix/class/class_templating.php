<?
class Templating extends Base{

    public $namespace = array();

    function __construct($param, $namespace) {
        parent::__construct($param);
        $this->cursor = $this;
        $this->namespace = $namespace;
    }

    public function get($param){
        if(is_array($param) && isset($param['object']) && isset($param['id'])){
            return $this->findOne($param['object'], array('id' => $param['id']));
        }else if(is_numeric($param) && (int) $param !== 0){
            return $this->findOne($this->namespace['construct_db'], array('id' => $param));
        }else if(is_string($param) && $param !== ''){
            return $this->findOne($this->namespace['construct_db'], array('chpu' => $param));
        }else{
            throw new Exception('Не заданы условия выборки');
        }
    }

    public function getParent($param){
        if (is_array($param) && isset($param['parent'])) {
            return $this->findOne($this->namespace['construct_db'], array('id' => $param['parent']));
        } elseif (is_numeric($param)) {
            return $this->findOne($this->namespace['construct_db'], array('id' => $param));
        } elseif (is_string($param)) {
            return $this->getParent($this->get($param));
        }else{
            throw new Exception('Не заданы условия выборки');
        }
    }

    public function getList($param, $callback = null){
        $result = array();

        if(!is_callable($callback)){
            $callback = function($item, $db){
                $item['data'] = $db->get($item);
                if($item['data'] !== false){
                    foreach($item['data'] as $k => $v){
                        $item['data'][$k] = $db->resc($v);
                    }
                }
                return $item;
            };
        }

        if(is_array($param)){
            if(!isset($param['hide'])) $param['hide'] = 0;
            $result = $this->extract($this->go(array(
                'event' => 'find',
                'from' => $this->namespace['construct_db'],
                'where' => $param,
                'order' => 'num'
            )), $callback);

        }

        return $result;
    }

    public function getId($object){
        return isset($object['chpu']) && $object['chpu'] !== '' ? $object['chpu'] : $object['id'];
    }


    public function path($url){
        $path = array();

        $url = rawurldecode($url);

        if(strpos($url, '?') !== false){
            $url = substr($url, 0, strpos($url, '?'));
        }

        if($url[0] == '/') $url = substr($url, 1);
        if(substr($url, -1) == '/') $url = substr ($url, 0, -1);

        if($url !== false && strlen($url)){
            $path = $this->loadRealPath(explode('/', $url));
        }

        if(is_array($path)){
            $path = array_reverse($path);
        }
        return $path;
    }

    private function loadRealPath($url){
        $end = $this->get(end($url));
        if($end == false) return false;

        $end = $this->getList(array('id' => $end['id']));

        $result = array();
        foreach($end as $v){
            $path = $this->pathTree($v['id']);

            foreach($path as $item){
                if((int) $item['active_path'] === 1){
                    if(!in_array($this->getId($item), $url)){
                        return false;
                    }
                }

                if(in_array($this->getId($item), $url)){
                    $item['data'] = $this->get($item);
                    $result[] = $item;
                }

            }
        }
        return $result;

    }

    private function pathTree($id, $merge = array()){
        $item = $this->get($id);
        $merge[] = $item;

        if((int)$item['parent'] === 0){
            return $merge;
        }else{
            return $this->pathTree($item['parent'], $merge);
        }
    }
}