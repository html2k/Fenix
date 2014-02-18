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
    
    public function getList($param, $callback = null){
        $result = array();

        if(!is_callable($callback)){
            $callback = function($item, $db){
                $item['data'] = $db->get($item);

                foreach($item['data'] as $k => $v){
                    $item['data'][$k] = $db->resc($v);
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
        if($url[0] == '/') $url = substr($url, 1);
        if(substr($url, -1) == '/') $url = substr ($url, 0, -1);

        if($url !== false && strlen($url)){
            $url = explode('/', $url);

            $endCHPU = end($url);
            $tree = array();
            while(true){
                if(empty($endCHPU)) break;

                $item = $this->get($endCHPU);
                if(!$item) break;

                $tree[] = $item;
                $endCHPU = $item['parent'];
            }

			foreach($tree as $k => $v){
				if((int)$v['active_path'] === 0)
				unset($tree[$k]);
			}

            if(count($tree) < count($url)) return false;

            $url = array_reverse($url);
            $k = 0;
			
            foreach($tree as $t){
				
                if((int) $t['active_path'] === 1){
                    if(isset($url[$k]) && ($url[$k] == $t['id'] || $url[$k] == $t['chpu'])){
                        $t['data'] = $this->get($t);
                        $path[] = $t;
                        $k++;
                    }else{
                        return false;
                    }
                }else{
                    if(isset($url[$k]) && ($url[$k] == $t['id'] || $url[$k] == $t['chpu'])){
                        $t['data'] = $this->get($t);
                        $path[] = $t;
                        $k++;
                    }
                }
            }
            $path = array_reverse($path);

        }
        return $path;
    }

}