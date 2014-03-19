<?
    class Extension extends IO {

        private $global = array(); // Изменяемый массив с глобальными переменными для передачи в callback функции
        private $extensions = array(); // Скомпелированные расширения
        private $config = array(); // Конфиг системы
        private $namespace = array();
        private $db = false;
        private $io = false;
        private $static = false;
        private $extFolder = '';
        private $isCompile = false; // если true будет пересобран массив

        function __construct($namespace, $config, $db, $io, $static){
            $this->db = $db;
            $this->io = $io;
            $this->static = $static;
            $this->namespace = $namespace;
            $this->config = $config;
        }

        public function compile(){

            $extensionFolder = root.'/'.$this->config['folder']['extension'] . '/';
            $extensionList = $this->read_dir($extensionFolder, 'dir');

            if(count($extensionList)){
                foreach ($extensionList as $value) {
                    $files = $this->read_dir($value, 'file');
                    foreach ($files as $file) {
                        if(strpos($file, 'init.php') > -1){
                            $this->extFolder = $value;
                            $this->loadExtension($file, $this->namespace, $this, $this->db, $this->io, array(
                                'url' => $value
                            ));
                        }
                    }
                }
            }

        }

        public function set ($extensionName, $extansionOption){
            if(!isset($this->extensions[$extensionName])){
                $this->extensions[$extensionName] = array();
            }

            $this->extensions[$extensionName][] = array(
                'name' => $extensionName,
                'url' => $this->extFolder,
                'option' => $extansionOption
            );
        }

        public function get ($extensionName){
            if(isset($this->extensions[$extensionName])){
                return $this->extensions[$extensionName];
            }
        }

        public function loadExtension($file, $namespace, $Extension, $db, $io, $option){
            if(file_exists($file)){
                ob_start();
                    require $file;
                ob_end_clean();
            }
        }

        public function setStatic($url){
            $this->static->addFile($this->extFolder . $url);
        }


    }