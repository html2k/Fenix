<?
class TPL {
    
    function __construct(){}
    
    public function load($tpl, $var = array()){
        $result = '';
        
        ob_start();
            if($var && is_callable($var)){
                $var();
            }
            if(file_exists($tpl)) require $tpl;
            $result = ob_get_contents();
        ob_end_clean();
        
        return $result;
    }
    
}
