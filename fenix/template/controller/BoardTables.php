<?

/**
 * Class BoardTables
 */
class BoardTables{

    /**
     * @param array $result
     * @param $param
     * @return array
     */
    public function run($result = array(), $param){

        if($param['method'] === 'getInfo'){
            return $this->getInfo($result);
        }elseif ($param['method'] === 'search'){
            return $this->search($result, $param);
        }elseif($param['method'] === 'removeCell'){
            return $this->removeCell($param);
        }

        return array();

    }


    /**
     * @param array $result
     * @return array
     */
    public function getInfo($result = array()){
        $result['tableInfo'] = Fx::db()->tables_info();
        $result['tables'] = array();
        $result['sys_tables'] = Fx::db()->find(Fx::service_context()->namespace['struct_db'], array(), function($item){
            return $item['code'];
        });

        foreach ($result['tableInfo']['table'] as $key => $value) {
            $result['tables'][$key] = Fx::db()->extract(Fx::db()->go(array(
                'event' => 'find',
                'from' => $key,
                'limit' => 25
            )), function($item){
                return $item;
            });
        }

        foreach($result['sys_tables'] as $k => $v){
            unset($result['sys_tables'][$k]);

            $result['sys_tables'][$v] = $result['tables'][$v];

            unset($result['tables'][$v]);
        }

        foreach(Fx::service_context()->manifest['baseCollection'] as $k => $v){
            $name = Fx::service_context()->config['db']['sys_namespace'] . $k;
            if(isset($result['tables'][$name])){
                $result['sys_tables'][$name] = $result['tables'][$name];
                unset($result['tables'][$name]);
            }
        }

        return $result;
    }


    /**
     * @param array $result
     * @param $param
     * @return array
     */
    public function search($result = array(), $param){
        $find = Fx::db()->extract(Fx::db()->go(array(
            'event' => 'find',
            'from' => $param['table'],
            'limit' => 1
        )));

        $result['keys'] = array();
        $result['find'] = array();
        $result['data'] = array();

        if(count($find) > 0){

            $result['keys'] = array_keys($find[0]);
            foreach($result['keys'] as $v){
                $result['find'][$v] = $param['value'];
            }

            $result['data'] = Fx::db()->search($param['table'], $result['find']);
        }

        return $result;
    }


    public function removeCell($post){
        foreach($post['cell'] as $v){
            if(isset($v['find'])){
                Fx::db()->remove($v['table'], $v['find']);
            }
        }
    }

}