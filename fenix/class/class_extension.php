<?
class Fx_Extension {

    private $extensions = array();

    public function compile (){
        $folderExtension =  root . '/' . Fx::app()->config['folder']['extension'] . '/';
        $fileExtensionSave = $folderExtension . 'extension.php';
        $listExtension = Fx::io()->read_dir($folderExtension, 'dir');

        $ext = array();
        if(file_exists($fileExtensionSave)){
            ob_start();
                $ext = include $fileExtensionSave;
            ob_clean();
        }

        $Extensions = array(
            'list' => array()
        );
        if(count($listExtension)){
            foreach($listExtension as $k => $v){
                if($k === 'list') continue;

                $fileInit = $v . 'init.php';
                if(file_exists($fileInit)){
                    $sha = sha1_file($fileInit);

                    if(isset($ext[$v]) && $ext[$v]['sha'] === $sha){
                        $Extensions[$v] = $ext[$v];

                    }else{
                        $Extensions[$v] = array(
                            'sha' => $sha,
                            'ext' => $this->compileExtension($v)
                        );
                    }

                    foreach($Extensions[$v]['ext'] as $event){
                        if(!isset($Extensions['list'][$event['use']])){
                            $Extensions['list'][$event['use']] = array();
                        }
                        $event['url'] = $v;
                        $Extensions['list'][$event['use']][] = $event;
                    }
                }
            }
        }

        if(file_exists($fileExtensionSave)){
            Fx::io()->del($fileExtensionSave);
        }

        Fx::io()->create_file($fileExtensionSave);
        Fx::io()->write($fileExtensionSave,
            '<? return $Extension = '. Fx::io()->arrayToString($Extensions) . '; ?>'
        );

        $this->extensions = $Extensions;
        return $Extensions;
    }

    public function get($name){
        $result = array();
        if(isset($this->extensions['list'][$name])){
            $result = $this->extensions['list'][$name];
        }
        return $result;
    }


    public function compileExtension($url){
        $ext = new _Extension($url);
        $this->loadExtension($ext);

        foreach($ext->event as $v){
            $v['static'] = $ext->st;
            $v['url'] = $url;
        }
        return $ext->event;

    }

    public function loadExtension(&$Extension){
        ob_start();
            require $Extension->url . 'init.php';
        ob_clean();
    }

}

class _Extension {
    public  $event = array();
    public function __construct($url){
        $this->url = $url;
    }
    public function set($use, $param) {
        $this->event[] = array(
            'use' => $use,
            'param' => $param
        );
    }

    public $st = array();
    public function setStatic($url){
        $this->st[] = $url;
    }

}