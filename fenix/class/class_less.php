<?


class Less extends lessc {

    private $io;


    function __construct($io){
        $this->io = $io;
    }

    public function treeCompile($path){
        $tree = $this->io->tree($path, 'file');

        foreach($tree['file'] as $v){
            $mime = $this->io->mime($v);
            if($mime === 'less'){
                $fileName = substr($v, 0, -4) . 'css';
                $this->checkedCompile($v, $fileName);
            }
        }
    }

}