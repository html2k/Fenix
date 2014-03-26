<?
class StaticCompressor {

    private $paths = array();
    private $root = '';

    /**
     * @param $path
     * @throws Exception
     */
    public function set($path){
        $file = $this->root . '/' . $path;
        if(file_exists($file)){
            $this->paths[] = $path;
        }else{
            throw new Exception('Такой путь не существует', 404);
        }
    }


    /**
     * @param $path
     */
    public function root($path){
        $this->root = $path;
    }


    /**
     * @return array
     */
    public function getStyle(){
        $result = array();
        foreach($this->paths as $v){
            $mime = Fx::io()->mime($v);
            if(in_array($mime, array('css', 'sass', 'scss', 'less'))){
                $result[] = '<link rel="stylesheet" type="text/css" href="'.$v.'">';
            }
        }
        return $result;
    }


    /**
     * @return array
     */
    public function getScript(){
        $result = array();
        foreach($this->paths as $v){
            $mime = Fx::io()->mime($v);
            if(in_array($mime, array('js'))){
                $result[] = '<script src="'.$v.'" type="text/javascript"></script>';
            }
        }
        return $result;
    }
}

/*
class CompressStatic extends IO{

	private $static = array();
	private $folder = '/';
	private $file_name = 'test';
	public $compress_status = array();
	private $compress_list = array();
	private $cut_path = '';
	private $notCompress = array();

	function __construct($folder, $file_name, $cut_path = ''){
		$this->folder = $folder;
		$this->file_name = $file_name;
		$this->cut_path = $cut_path;

		$compress_status_file = $this->folder . $file_name . '.php';
		if(file_exists($compress_status_file)){
			$this->buffer($compress_status_file, function($res, $object){
				$object->compress_status = $res;
			}, $this);
			if(!is_array($this->compress_status)){
				$this->compress_status = false;
			}
		}
	}

	public function get($type){
		$path = str_replace($this->cut_path, '', $this->getCompress($type));
		return $path;
	}

	public function getCompress($type){
		$this->compile($type);
		return $this->folder . $this->file_name . '.'. $type;
	}

	public function addFile($file_name, $is_compress = true){
		if(file_exists($file_name)){
			$this->notCompress[$file_name] = $is_compress;
			$this->addString($file_name, file_get_contents($file_name), $this->mime($file_name));
		}
	}

	public function getList($type, $cut_puth = false){
		$keys = array_keys($this->compress_list[$type]);

		if($cut_puth){
			foreach ($keys as $key => $value) {
				$keys[$key] = str_replace($this->cut_path, '', $value);
			}
		}

		return $keys;
	}

	public function getVersion(){
		return $this->compress_status['version'];
	}

	private function addString($file, $string, $type){
		if(strlen($string)){
			if(!isset($this->compress_list[$type])){
				$this->compress_list[$type] = array();
			}

			$this->compress_list[$type][$file] = $string;
		}
	}

	private function compile($type){
		$fileCompressed = $this->folder . $this->file_name . '.' . $type;
		if(count($this->compress_list[$type])){
			$compressed = array();

			$isCompressed = false;
			foreach ($this->compress_list[$type] as $file => $value) {
				if(!$this->status($file)){
					$isCompressed = true;
					break;
				}
			}


			if($isCompressed){
				foreach ($this->compress_list[$type] as $file => $string) {
					if(isset($this->notCompress[$file]) && $this->notCompress[$file] === false){
						$compressed[] = "\n" . $string . "\n";
						$this->compress_status[$file] = sha1_file($file);
						continue;
					}
					if($type === 'css'){
						$compressed[] = CssMin::minify($string);
					}else if($type === 'js'){
						$compressed[] = JSMin::minify($string);
					}
					$this->compress_status[$file] = sha1_file($file);
				}

				$compressed = implode('', $compressed);

				$this->del($fileCompressed);
				$this->in_file($fileCompressed, $compressed);

				// $this->del($fileCompressed . '.gz');
				// $this->in_file($fileCompressed.'.gz', gzencode($compressed, 9, FORCE_DEFLATE));

				$this->compress_status['version'] = time();

				$this->del($this->folder . $this->file_name . '.php');
				$this->in_file($this->folder . $this->file_name . '.php', '<? return $compress_status = ' . $this->arrayToString($this->compress_status) . '; ?>');
			}
		}
		return $fileCompressed;
	}

	private function status($file){
		if(file_exists($file)){
			$type = $this->mime($file);

			if(isset($this->compress_status[$file])){
				return sha1_file($file) === $this->compress_status[$file];
			}
		}
		return false;
	}

}
*/