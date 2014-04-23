<?
class bFileManager {


    /**
     * @param array $result
     * @param $param
     * @return array
     */
    public function run($result = array(), $param){

        if(isset($param['saveFile'])){
            $result = $this->saveFile($param);
        }

        if(isset($param['removeItem'])){
            $this->removeItem($param['path']);
        }

        if(isset($param['createFolder']) && isset($param['pathTo'])){
            $this->createFolder($param['createFolder'], $param['pathTo']);
        }

        if(isset($param['pathTo'])){
            $result = array_merge($result, $this->userFileList($param['pathTo']));
        }

        return $result;
    }

    protected function userFileList($to = ''){
        $path = Fx::io()->path(
            root,
            Fx::context()->config['folder']['file_manager']
        );
        if(strlen($to) > 1){

            if($to{0} === '/'){
                $to = substr($to, 1);
            }

            $path = Fx::io()->path(
                root,
                Fx::context()->config['folder']['file_manager'],
                $to
            );
        }

        if(!is_dir($path)){
            Fx::io()->create_dir($path);
        }

        $io = Fx::io()->read_dir($path);

        foreach($io['file'] as $k => $v){

            $resultArray = array();

            if($imageSize = getimagesize($v)){
                $resultArray['system_type'] = 'image';
                $resultArray['width'] = $imageSize[0];
                $resultArray['height'] = $imageSize[1];
            }else{
                $resultArray['system_type'] = 'file';
            }

            $resultArray['type'] = strtolower(Fx::io()->mime($v));
            $resultArray['size'] = filesize($v);
            $resultArray['name'] = basename($v);
            $resultArray['path'] = str_replace(root, '', $v);

            $io['file'][$k] = $resultArray;
        }

        foreach($io['dir'] as $k => $v){
            $io['dir'][$k] = array(
                'path' => str_replace(Fx::io()->path(root, Fx::context()->config['folder']['file_manager']), '', $v),
                'name' => $resultArray['name'] = basename($v)
            );
        }

        return $io;
    }

    protected function saveFile($param){
        $files = $_FILES['files'];


        if(count($files['tmp_name'])){
            $files_put_path = Fx::context()->config['folder']['file_manager'];

            if(strlen($param['pathTo']) > 1){
                $files_put_path = Fx::context()->config['folder']['file_manager'] . $param['pathTo'];
            }



            $file_paths = array();

            foreach($files['tmp_name'] as $k => $file){
                Fx::io()->load_file($file, Fx::io()->path(
                    root, $files_put_path, $files['name'][$k]
                ));

                $file_paths[] = Fx::io()->path('', $files_put_path, $files['name'][$k]);
            }

            return $file_paths;
        }

        return array(
            $_FILES['files'],
            $_FILES['files']['name'][0],
            root . Fx::context()->config['folder']['file_manager'] . '/' . $_FILES['files']['name'][0]
        );
    }

    protected function removeItem($path){
        Fx::io()->del(root.$path);
    }

    protected function createFolder($folderName, $pathTo){
        $folder = Fx::io()->path(
            root,
            Fx::context()->config['folder']['file_manager'],
            $folderName
        );

        if(strlen($pathTo) > 1){
            $folder = Fx::io()->path(
                root,
                Fx::context()->config['folder']['file_manager'],
                $pathTo,
                $folderName
            );
        }

        Fx::io()->create_dir($folder);
    }

}