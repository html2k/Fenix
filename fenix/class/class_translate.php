<?
class Translate {
    private $option;
    
    function __construct() {}

    public function go($option){
        return self::translate($option);
    }
    
    public function find($from, $option = array()){
        $find = array(
            'from' => $from,
            'event' => 'select',  
        );
        if(count($option)) $find['where'] = $option;
        return self::go($find);
    }
    public function insert($from, $insert){
        return self::go(array(
            'event' => 'insert',
            'from' => $from,
            'insert' => $insert
        ));
    }
    public function update($from, $update, $where){
        return self::go(array(
            'event' => 'update',
            'from' => $from,
            'update' => $update,
            'where' => $where
        ));
    }
    
    public function remove($from, $where){
        return self::go(array(
            'event' => 'delete',
            'from' => $from,
            'where' => $where
        ));
    }

    public function translate($option){
        $result = array();
        
        if(isset($option['event'])){
            $event = self::translate_event ($option['event'], $option['from']);
            if($event == false) exit('Error query');
            else $result[] = $event;
        }
        
        if(isset($option['col']))
            $result[] = self::translate_col ($option['col']);
        else if($option['event'] === 'select' || $option['event'] === 'find')
            $result[] = '*';
        
        if(isset($option['from']) && $option['event'] !== 'insert' && $option['event'] !== 'update')
            $result[] = self::translate_from ($option['from']);
        
        if(isset($option['insert'])){
            $result[] = self::translate_insert ($option['insert']);
            return implode(' ', $result);
        }
        
        if(isset($option['update']))
            $result[] = self::translate_update ($option['update']);
        
        if(isset($option['where'])){
            self::$translate_where_var = array();
            
            $tWvar = self::translate_where ($option['where']);
            $last_elem = $tWvar[count($tWvar) -1];
            if($last_elem === '||' || $last_elem === '&') array_pop ($tWvar);
            
            $result[] = 'WHERE ' . implode(' ', $tWvar);
        }
        
        if(isset($option['order']))
            $result[] = self::translate_order ($option['order']);
        
        if(isset($option['limit']))
            $result[] = self::translate_limit ($option['limit']);
        
        
        
        return implode(' ', $result);
    }
    
    
    
    protected function translate_event($option, $from){
        switch ($option){
            case 'find':
            case 'select':
                return 'SELECT';
            break;
        
            case 'insert':
                return 'INSERT INTO `'.$from.'`';
            break;
        
            case 'update':
                return 'UPDATE `'.$from.'` SET';
            break;
        
            case 'remove':
            case 'delete':
            case 'del':
                return 'DELETE';
            break;
        
            default :
                return false;
            break;
        }
    }
    
    protected function translate_insert($option){
        $key = array_keys($option);
        $val = array_values($option);
        foreach ($key as $k => $v) $key[$k] = '`'.$v.'`';
        foreach ($val as $k => $v) $val[$k] = (is_numeric ($v)) ? $v : '\''. $v .'\'';
        return '(' . implode(', ', $key) . ') VALUES (' .implode(', ', $val). ')';
    }
    
    protected function translate_update($option){
        $result = array();
        foreach ($option as $key => $val)
            $result[] = '`' . $key . '` = ' . ((is_numeric($val)) ? $val : '\'' . $val . '\'');
        return implode(', ', $result);
    }

    protected function translate_col($option){
        $result = array();
        if(is_array($option))
            foreach($option as $val) $result[] = '`'.$val.'`';
        else
            $result[] = '`'. $option .'`';
        return implode(' ', $result);
    }
    
    protected function translate_from($option){
        return 'FROM `' . $option . '`';
    }

    protected static $translate_where_var;
    protected function translate_where($option, $back_val = '', $back_key = ''){
        
        $alias = array('$and' => 'AND', '$or' => 'OR', '$lt' => '<', '$lte' => '<=', '$gt' => '>', '$gte' => '>=', '$ne' => '!=');
        $bKey = $back_key;
        $bVal = $back_val;
        $lastElement = end($option);
        $each = 0;
        
        foreach($option as $key => $val){
            if($each > 0) self::$translate_where_var[] = isset($alias[$back_key]) ? $alias[$back_key] : $alias['$and'];
            
            
            if($key === '$or'){
                $bKey = $key;
                if(is_array($val)){
                    
                    self::$translate_where_var[] = '(';
                    self::translate_where($val, $bVal, $bKey);
                    self::$translate_where_var[] = ')';
                    
                    //if($each + 1 < $len)
                    self::$translate_where_var[] = isset($alias[$back_key]) ? $alias[$back_key] : $alias['$and'];
                    
                }
                continue;
            }
            
            if(is_array($val)){
                if (!is_numeric($key) && !isset($alias[$key])) $bVal = $key;
                
                self::$translate_where_var[] = '(';
                self::translate_where($val, $bVal, $bKey);
                self::$translate_where_var[] = ')';
                
                if($lastElement !== $val)
                    self::$translate_where_var[] = isset($alias[$back_key]) ? $alias[$back_key] : $alias['$and'];
                
                continue;
            }
            
            
            if($back_val !== '')
                self::$translate_where_var[] = '`' . $back_val . '`';
            
            self::$translate_where_var[] = (isset($alias[$key])) ? $alias[$key] : (is_numeric($key) ? '=' : '`' .$key . '` =');
            
            self::$translate_where_var[] = is_numeric($val) ? $val : '\'' . $val . '\'';

            $each++;
        }
        
        return self::$translate_where_var;
        
    }
    protected function translate_limit($option){
        return 'LIMIT ' . $option;
    }
    protected function translate_order($option){
        return 'ORDER BY '. $option;
    }
    
}