<?
class Base extends ConvertSchem{
    
    private $connect = false;
            
    function __construct($param) {
        $this->init($param);
    }
    
    function init($param){
        $param['pass'] = (isset($param['pass'])) ? $param['pass'] : '';
        
        try {
            $this->connect = @mysql_connect($param['host'], $param['user'], $param['pass']);
        } catch (Exception $exc) {
            $this->connect = false;
        }
        
        if($this->connect){
            $use = mysql_select_db($param['name']);
            if($use === false){
                    mysql_select_db('information_schema');
                    $this->creat_db($param['name']);
                    mysql_select_db($param['name']);
            }


            mysql_query ("SET NAMES utf8");
            mysql_query ("set character_set_client='utf8'");
            mysql_query ("set character_set_results='utf8'");
            mysql_query ("set collation_connection='utf8_general_ci'");
        }
    }
    
    function isConnect(){ return !!$this->connect; }


    public function query($query){
        if($this->isConnect()){
            return mysql_query($query, $this->connect);
        }else{
            return false;
        }
    }

    public function show_tables(){ return $this->extract($this->query('SHOW TABLES')); }
    public function show_column($name){ return $this->extract($this->query('DESCRIBE `'.$name.'`')); }
    public function remove_table($name){ return $this->query('DROP TABLE `'.$name.'`'); }

    public function tables_info(){
        $res = $this->extract($this->query('SHOW TABLE STATUS'));
        $array = array();
        $full_size = 0;
        $full_count = 0;
        foreach ($res as $val) {
            $array[$val['Name']] = array(
                'size' => $val['Data_length'],
                'count' => $val['Rows']);
            $full_count += (int) $val['Rows'];
            $full_size += (int) $val['Data_length'];
        }

        return array(
            'table' => $array,
            'full_size' => $full_size,
            'full_count' => $full_count);
    }

    public function extract($query){
        if($query === false) return array();
        $res = array();
        while($item = mysql_fetch_assoc($query)){
			foreach($item as $k => $v){
					$item[$k] = $this->resc($v);
			}
			
		
			$res[] = $item;
		}
        return $res;
    }

    public function go($param){
        return $this->query(parent::go($param));
    }
    
    public function find($from, $option = array()){
        return $this->extract($this->query(parent::find($from, $option)));
    }
    
    public function insert($from, $insert){
        return $this->query(parent::insert($from, $insert));
    }
    
    public function update($from, $update, $where){
        return $this->query(parent::update($from, $update, $where));
    }
    
    public function remove($from, $where) {
        return $this->query(parent::remove($from, $where));
    }

    public function creat_db($name){
        return $this->query('CREATE DATABASE `'.$name.'`');
    }

    public function createCollection($param){
        return $this->query(parent::createTable($param));
    }
    
    public function editCollection($param){
        return $this->query(parent::alterTable($param));
    }
    
    public function lastID (){
        return mysql_insert_id();
    }
    
    public function esc($value){
        $value = addslashes($value);
        $value = htmlspecialchars($value);
        
        return $value;
    }
	
	public function resc($value){
		$value = stripslashes($value);
		$value = htmlspecialchars_decode($value);
		
		return $value;
	}
    
}