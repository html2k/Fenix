<?

/**
 * Class HeaderSearch
 */
class HeaderSearch {


    /**
     * @param array $result
     * @param $param
     * @return array
     */
    public function run($result = array(), $param){

        if($param['method'] === 'find'){
            return $this->find($param);
        }

        return array();
    }


    public function find($param){
        $val = $param['value'];

        $result = array();

        if(is_numeric($val)){
            Fx::db()->search(Fx::context()->namespace['construct_db'], array(
                'id' => (int) $val,
                'chpu' => $val
            ));
        }

        foreach(Fx::db()->find(Fx::context()->namespace['struct_db']) as $v){
            $table = $v['code'];

            $row = Fx::db()->extract(Fx::db()->go(array(
                'event' => 'find',
                'from' => $table,
                'limit' => 1
            )));

            if(!isset($row[0])) continue;
            $rows = $row[0];

            $where = array();

            foreach(array_keys($rows) as $row){
                $where[$row] = $val;
            }

            $find = Fx::db()->search($table, $where);
            if(count($find) > 0){
                $result[] = array(
                    'name' => $v['name'],
                    'code' => $v['code'],
                    'find' => $find
                );
            }
        }
        return $result;
    }
}