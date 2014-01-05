<?php

class ConvertSchem extends Translate{
    
    private $system_alias = array(
        'string' => 'varchar',
        'text' => 'text',
        'int' => 'int'
    );
            
    function __construct() {}
    
    public function createTable($option){
        $this->index = array();
        $result = array();
        foreach($option['row'] as $val) $result[] = $this->convertRow ($val);
        $result = 'CREATE TABLE `' . $option['name'] . "`(\n" . implode(",\n", $result) . "\n) DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci";
        return $result;
    }
    
    
    /**
     * name - имя поля
     * type - тип поля
     * size - число, но не везде будет выставлено в связи с особенностями бд
     * index - A (AUTO_INCREMENT), U (UNIQUE), I (INDEX), P (PRIMARY KEY)
     * null - true/false (true)
     */
    
    public function alterTable($option){
        $this->index = array();
        $result = array();
        $result = 'ALTER TABLE `' . $option['name'] . "` ";
        $param = $option['row'];
        
        if(count($param['change']))
            foreach($param['change'] as $v){ $result .= 'CHANGE `'.$v['base'].'` '. $this->convertRow ($v) .', '; }
        if(count($param['add']))
            foreach($param['add'] as $v){ $result .= 'ADD '. $this->convertRow ($v) .', '; }
        if(count($param['drop']))
            foreach($param['drop'] as $v){ $result .= 'DROP COLUMN `'.$v.'`, '; }
        $result = substr($result, 0, -2);
        return $result;
    }
    
    public $index = array();
    public function convertRow($option){
        try{
            $alias = array(
                // Числа
                'bigint', 'numeric', 'bit', 'smallint',
                'decimal', 'smallmoney', 'int', 'tinyint',
                'money',
                
                // Приблизительные числа
                'float', 'real',
                
                // Дата и время
                'Date', 'datetimeoffset', 'datetime2', 'smalldatetime',
                'datetime', 'time',
                
                // Символьные строки
                'char', 'varchar', 'text',
                
                // Символьные строки в Юникоде
                'nchar', 'nvarchar', 'ntext',
                
                // Двоичные данные
                'binary', 'varbinary', 'image',
                
                // Прочие типы данных
                'timestamp', 'hierarchyid', 'uniqueidentifier', 'sql_variant',
                'xml', 'cursor', 'table'
            );
            
            $name = $option['name'];
            $type = trim(strtolower($option['type']));
            $size = $option['size'];
            $index = (isset($option['index'])) ? $option['index'] : '';
            $null = (isset($option['null'])) ? $option['null'] : true;
            
            if(isset($this->system_alias[$type])) $type = $this->system_alias[$type];
            if(!in_array($type, $alias)) throw 'NO TYPE';
            
            $result = '`' . $name . '` ' . $type;
            if(is_numeric($size)) $result .= ' ('. $size .')';

            $result .= $null ? ' NOT NULL' : '';
            
            if(strlen($index) > 0){
                $index = trim(strtolower($index));
                $index = str_split($index);
                
                foreach($index as $v){
                    if($v == 'a'){
                        $this->index[] = array($name, 'AUTO_INCREMENT');
                        $result .= ' AUTO_INCREMENT';
                    }else if($v == 'u'){
                        $this->index[] = array($name, 'UNIQUE');
                        $result .= ' UNIQUE';
                    }else if($v == 'i'){
                        $this->index[] = array($name, 'INDEX');
                        $result .= ' INDEX';
                    }else if($v == 'p'){
                        $this->index[] = array($name, 'PRIMARY KEY');
                        $result .= ' PRIMARY KEY';
                    }
                }
            }
            
            return $result;
            
        }  catch (Exception $e){
            echo $e;
            return false;
        }
    }
}