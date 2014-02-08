<?
class IO {

	function __construct(){}
	
	//-> Чтение файла
	public function read($name, $method = 'string'){
		if($method == 'string')
			return implode('', file($name));
		else
			return file($name);
	}	
		//-> Методы псевдонимы для read()
		public function to_array($name){ return $this->read($name, 'array'); }
		public function to_string($name){ return $this->read($name, 'string'); }
	
	
	//-> Получение хеша файла
	public function get_hash($name, $method = 'sha1'){
		if($method == 'sha1')
			return sha1_file($name);
		else
			return md5_file($name);
	}
	
	//-> Чтение дерриктории
	public function read_dir($name, $res = false){
		$result = array('file' => array(), 'dir' => array());
        $name .= ($name{strlen($name)-1} === '') ? '/' : '';

		if($handle = opendir($name)){
			while(false !== ($entry = readdir($handle))){
				if($entry != '.' && $entry != '..'){
					
					if(is_dir($name.$entry)) $result['dir'][] = $name.$entry.'/';

					else if(is_file($name.$entry)) $result['file'][] = $name.$entry;
				}
			}
			closedir($handle);
		}
		
		if($res !== false)
			if($res == 'dir') return $result['dir'];
			else if($res == 'file') return $result['file'];
			
		return  $result;
	}
	
	//-> Рекурсивное чтение дерриктории
	private $tree = array('file' => array(), 'dir' => array());
	public function tree($name){
		$dir = $this->read_dir($name);
		
		$this->tree['dir'] += $dir['dir'];
		$this->tree['file'] += $dir['file'];
		$this->tree__init($dir['dir']);
		
		return $this->tree;
	}
		private function tree__init($list){
			foreach($list as $v){
				$dir = $this->read_dir($v);
				$this->tree['dir'] = array_merge($this->tree['dir'], $dir['dir']);
				$this->tree['file'] = array_merge($this->tree['file'], $dir['file']);
				$this->tree__init($dir['dir']);
			}
		}
		
	//-> Создание файла
	public function create_file($name){ fclose(fopen($name, 'w+')); chmod($name, 0777); }
	public function create_dir($name, $mod = 0777){ return mkdir($name, $mod, true); }

	
	//-> Запись в файл
	public function in_file($name, $string, $to = true, $chmod = 0777){
		if(!file_exists($name)){
            $this->create_file($name);
            chmod($name, $chmod);
        }
		if($to){
			$string = $string . $this->to_string($name);
		}else{
			$string = $this->to_string($name) . $string;
		}
		file_put_contents($name, $string);
	}
	public function write($name, $string){
            if(file_exists($name) && ($handle = fopen($name, 'w'))){
                $result = fwrite($handle, $string);
                fclose($handle);
                
                return $result;
            }else{
                return false;
            }
	}
	
	//-> Копирование файла
	public function copy($from, $to = false){
            try {
                if(is_dir($from)){
                    $this->recurse_copy($from, $to);
                    return true;
                }else{
                    if(copy($from, $to)){
                        chmod($to, 0777);
                        return true;
                    }
                }
            } catch (Exception $exc) {
                setSystemMessage('error', $e);
            }
            return false;
	}
        
        private function recurse_copy($src,$dst) { 
            $dir = opendir($src); 
            @mkdir($dst); 
            while(false !== ( $file = readdir($dir)) ) { 
                if (( $file != '.' ) && ( $file != '..' )) { 
                    if ( is_dir($src . '/' . $file) ) { 
                        recurse_copy($src . '/' . $file,$dst . '/' . $file); 
                    } 
                    else { 
                        copy($src . '/' . $file,$dst . '/' . $file); 
                    } 
                } 
            } 
            closedir($dir); 
        } 

	//-> Удаление файла или деректории
	public function del($name){
		if(is_dir($name)){
			return $this->removeDir($name);
		}else if(is_file($name)){
			return unlink($name);
		}else{
			return false;
		}
	}

	//-> Удаление дерриктории целиком
	public function removeDir($name){
            if ($objs = glob($name."/*"))
                foreach($objs as $obj) is_dir($obj) ? removeDirectory($obj) : unlink($obj);
            rmdir($name);
	}

	//-> Загрузка файла
	public function load_file($file, $to){
		if(move_uploaded_file($file['tmp_name'], $to)){
			chmod($to, 0775);
			return true;
		}else{
			return false;
		}
	}

	//-> Получение расширения файла
	public function mime($file){
		$file = explode('.', $file);
		return $file[count($file) - 1];
	}
	
	//-> Массив в строку
	private $arrayString = '';
	public function arrayToString($arr, $delim = "\t"){
		$this->arrayString = "array(\n";
		$this->arrayToStingREC($arr, $delim);
		if(count($arr) > 0)
			$this->arrayString = substr($this->arrayString, 0, -2);
		$this->arrayString .= "\n".$delim.")";
		return $this->arrayString;
	}
		private function arrayToStingREC($arr, $delim = ""){
			foreach($arr as $k => $v) {
				
				if(!is_numeric($k))
					$this->arrayString .= $delim . "'" .$k . "' => ";
				else
					$this->arrayString .= $delim;
				
				if(is_array($v)) {
					$this->arrayString .= "array(\n";
					$this->arrayToStingREC($v, $delim . "\t");
					$this->arrayString .= $delim. "),\n";
				}else if(is_numeric($v)){
					$this->arrayString .= $v.",\n";
				}else{
					$this->arrayString .= "'".$v."',\n";
				}
			}
		}
	
    public function formatSizeUnits($bytes){
		if ($bytes >= 1073741824){
			$bytes = number_format($bytes / 1073741824, 2) . ' GB';
		}elseif ($bytes >= 1048576){
			$bytes = number_format($bytes / 1048576, 2) . ' MB';
		}elseif ($bytes >= 1024){
			$bytes = number_format($bytes / 1024, 2) . ' KB';
		}elseif ($bytes > 1){
			$bytes = $bytes . ' bytes';
		}elseif ($bytes == 1){
			$bytes = $bytes . ' byte';
		}else{
			$bytes = '0 bytes';
		}
		return $bytes;
    }

    public function buffer($file, $param, $callbackParam = false){
        $result = '';
        if(file_exists($file)){
            ob_start();
                $f = require $file;
                $result = ob_get_contents();
                if(is_callable($param)){
                    $result = $param($f, $callbackParam);
                }

            ob_end_clean();
        }
        return $result;
    }
}