<?
class Templating extends Base{
    
    public $namespace = array();
    
    function __construct($param, $namespace) {
        parent::__construct($param);
        $this->namespace = $namespace;
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
    
}