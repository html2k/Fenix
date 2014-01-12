<?
    class Extension extends IO {

        private $global = array(); // Изменяемый массив с глобальными переменными для передачи в callback функции
        private $extensions = array(); // Скомпелированные расширения
        private $config = array(); // Конфиг системы
        private $isCompile = false; // если true будет пересобран массив

        function __construct($config){
            $this->config = $config;
            $this->compile();
        }

        public function compile($config = false){
            if($config !== false && is_array($config)){
                $this->config = $config;
            }

            $this->buffer(root . '/' . $this->config['folder']['extension'] . '/extensions.php', function($res){
                $this->extensions = $res;
            });

            $path = root . '/' . $this->config['folder']['extension'] . '/';
            $dir = $this->read_dir($path, 'dir');

            foreach($dir as $v){
                $files = $this->read_dir($v, 'file');

                // find MANIFEST
                foreach($files as $file){
                    if(stripos($file, 'manifest.php') !== false){
                        $this->loadExtension($file);
                        break;
                    }
                }
            }

            if($this->isCompile){
                $put = '<? return $extensionManifest = ' . $this->arrayToString($this->extensions) . '; ?>';
                $this->in_file(root . '/' . $this->config['folder']['extension'] . '/extensions.php', $put, false);
            }
        }

        // Собираем расширение и записываем манивест в общий каталог
        public function loadExtension($manifest){
            $arr = explode('/',$manifest);
            array_pop($arr);

            $this->global = array(
                'folder_name' => array_pop($arr)
            );

            $this->buffer($manifest, function($MANIFEST){


                // $this->global['folder_name'] Название папки в которой лежит расширение
                // $this->extensions Скомпелированные расширения
                // $MANIFEST Манифест текущего расширения


                if(isset($this->extensions[$this->global['folder_name']])){
                    // Если такое расширение было скомпилировано ранее проверяем
                    // манифест на изменения и если такие были запускаием пересборку если нет ничего не делаем
                    $isSame = true;
                    foreach($this->extensions[$this->global['folder_name']] as $k => $v){
                        if($k !== 'path' && $MANIFEST[$k] !== $v){
                            $isSame = false;
                            break;
                        }
                    }
                    if($isSame){ return; }
                }

                $this->isCompile = true;
                $this->extensions[$this->global['folder_name']] = $MANIFEST;
                $this->extensions[$this->global['folder_name']]['path'] = $this->global['folder_name'];
            });

            $this->global = array();
        }

        public function getExtension ($extensionName){
            $extensionPath = root . '/' . $this->config['folder']['extension'] . '/';
            $this->global = array();
            $this->global['result'] = array();

            foreach($this->extensions as $v){
                $this->global['v'] = $v;
                if(isset($v['action']) && isset($v['action'][$extensionName])){
                    $this->buffer($extensionPath . $v['path'] . '/' . $v['action'][$extensionName], function($res){

                        $this->global['v']['data'] = $res;
                        array_push($this->global['result'], $this->global['v']);

                    });
                }
            }

            return $this->global['result'];
        }


    }