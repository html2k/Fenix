<?
class Templating extends Base{
    
    public $namespace = array();
    
    function __construct($param, $namespace) {
        parent::__construct($param);
        $this->namespace = $namespace;
    }

    public function get($id = false, $callback){

        if($id === false || $id == '' || $id === 0){
            throw new Exception('Не заданы условия выборки');
        }

        $where = array();

        if(is_numeric($id)){
            $where = array('id' => (int) $id, 'hide' => 0);
        }else{
            $where = array('chpu' => $id, 'hide' => 0);
        }


        return $this->extract($this->go(array(
            'event' => 'find',
            'from' => $this->namespace['construct_db'],
            'where' => array('id' => (int) $id, 'hide' => 0),
            'order' => 'num'
        )), function($item){
            $objectId = (isset($item['ref']) && $item['ref'] != 0) ? $item['ref'] : $item['id'];
            $data = $this->findOn($item['object'], array('id' => $objectId));
            $v['data'] = isset($data[0]) ? $data[0] : array();
            return $v;
        });

    }
    
    public function getList($param, $order = false, $limit = false){
        $result = array();
        $find = array();
        if(is_array($param)){
            if(!isset($param['hide'])) $param['hide'] = 0;
			$find = $this->extract($this->go(array(
				'event' => 'find',
				'from' => $this->namespace['construct_db'],
				'where' => $param,
				'order' => 'num'
			)));
			
        }else if(is_numeric($param)){
			$find = $this->extract($this->go(array(
				'event' => 'find',
				'from' => $this->namespace['construct_db'],
				'where' => array('id' => (int) $param, 'hide' => 0),
				'order' => 'num'
			)));
        }else if(is_string($param)){
			$find = $this->extract($this->go(array(
				'event' => 'find',
				'from' => $this->namespace['construct_db'],
				'where' => array('chpu' => $param, 'hide' => 0),
				'order' => 'num'
			)));
        }
        
        foreach($find as $v){
            $objectId = (isset($v['ref']) && $v['ref'] != 0) ? $v['ref'] : $v['id'];
            $data = $this->find($v['object'], array('id' => $objectId));
            $v['data'] = isset($data[0]) ? $data[0] : array();
            $result[] = $v;
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
        if($url !== false && count($url)){
            $url = explode('/', $url);
            $parentId = false;
            foreach($url as $v){
                if($v[0] == '?') continue;

                $queryParem = array();
                if(is_numeric($v)){
                    $queryParem['id'] = $v;
                }else{
                    $queryParem['chpu'] = $v;
                }
                if($parentId !== false)
                    $queryParem['parent'] = $parentId;

                $find = $this->getList($queryParem);

                if(count($find) > 0){
                    $parentId = $find[0]['id'];
                    $path[] = $find[0];
                }else{
                    $path = false;
                    break;
                }
            }
        }
        return $path;
    }

}