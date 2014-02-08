<?
class Templating extends Base{
    
    public $namespace = array();
    
    function __construct($param, $namespace) {
        parent::__construct($param);
        $this->cursor = $this;
        $this->namespace = $namespace;
    }

    public function get($param){

        if(!is_array($param) || !isset($param['object']) || !isset($param['id'])){
            throw new Exception('Не заданы условия выборки');
        }
        return $this->findOne($param['object'], array('id' => $param['id']));
    }
    
    public function getList($param, $callback = null){
        $result = array();
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