<?
/**
 * Class BoardTables
 */
class bRewrite{

    /**
     * @param array $ctx
     * @param $param
     * @return array
     */
    public function run($ctx = array(), $param){

        if(isset($param['controllerAction']) && $param['controllerAction'] === 'saveList'){

            Fx::db()->remove(Fx::service_context()->namespace['rewrite']);

            foreach($param['option'] as $v){
                if($v['from'] !== ''){
                    $v['from'] = $this->normalizeString($v['from']);
                    if($v['to']){
                        $v['to'] = $this->normalizeString($v['to']);
                    }
                    Fx::db()->insert(Fx::service_context()->namespace['rewrite'], $v);
                }
            }

        }else{
            $ctx = array_merge($ctx, array(
                'dict' => Fx::service_context()->dict, //Наполняем контекст словарем
                'list' => Fx::db()->find(Fx::service_context()->namespace['rewrite'])
            ));
        }

        return $ctx;
    }

    protected function normalizeString($string){
        if($string{0} !== '/'){
            $string = '/' . $string;
        }
        if(substr($string, -1) !== '/'){
            $string .= '/';
        }

        return $string;
    }



}