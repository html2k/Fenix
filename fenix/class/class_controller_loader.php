<?

/**
 * Class ControllerLoader
 */
class ControllerLoader{
    private $path;


    /**
     * @param $path
     * @throws Exception
     */
    public function setPath($path){
        if(!is_dir($path)){
            throw new Exception('Переданный путь не является дирректорией');
        }
        $this->path = $path;
    }


    /**
     * @param $path
     * @return mixed
     */
    public function getPath($path){
        return $this->path;
    }


    /**
     * @param $contollerName
     * @return mixe
     */
    public function load($contollerName){
        if(isset($contollerName)){
            $controller = $this->path . $contollerName . '.php';
            if(file_exists($controller)){
                require_once $controller;
                if(class_exists($contollerName)){
                    $controllerInit = new $contollerName;
                    if(method_exists($controllerInit, 'run')){
                        return $controllerInit;
                    }
                }
            }else{
                return $this;
            }
        }
    }


    public function run($array, $post){
        return $array;
    }

}