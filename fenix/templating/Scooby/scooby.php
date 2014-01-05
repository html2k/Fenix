<?
class Scooby{

    private $XML;
    private $XSL;
    private $folde;
    private $cache;
    public $PAGE;

    function __construct($param){
        return $this->init($param);
    }

    protected  function init($param){
        try {
            if(!class_exists('DOMDocument')) throw new Exception('not class \'DOMDocument\'');

            $this->folder = (isset($param['folder'])) ? $param['folder'] : '';
            $this->cache = (isset($param['cache'])) ? $param['cache'] : '';

            $this->XML = new DOMDocument('1.0', 'UTF-8');
            $this->XSL = new DOMDocument();

            /* root */
            $this->PAGE = $this->XML;
            $this->PAGE =  $this->append(false, 'page');
            $this->XML->appendChild($this->PAGE);

            return $this->PAGE;

        }catch (Exception $e){
            exit ('<pre>'.$e);
        }
    }

    public function append($to, $name, $text = false, $fragment = false){
        $to = ($to !== false) ? $to : $this->PAGE;
        if($text !== false){
            if($fragment || $this->is_html($text)){
                $st = $this->XML->createDocumentFragment();
                $st->appendXML($text);
                $el = $this->XML->createElement($name);
                $el->appendChild($st);
                $to->appendChild($el);
                return $el;
            }else{
                $el = $this->XML->createElement($name, $text);
                $to->appendChild($el);
                return $el;
            }
        }else{
            $el = $this->XML->createElement($name);
            $to->appendChild($el);
            return $el;
        }
        exit('error append element');
    }

    public function addAttr($to, $name, $text){
        try{
            if($name == '') throw new Exception('$name is empty');
            $to = ($to !== false) ? $to : $this->PAGE;

            $el = $this->XML->createAttribute($name);
            $to->appendChild($el);
            $st = $this->XML->createTextNode($text);
            $el->appendChild($st);
            return $to;

        }catch (Exception $e){
            exit ('<pre>'.$e);
        }
    }

    public function arrayToXML($to, $arr){
        try{
            foreach($arr as $k => $v){					
                if(is_array($v)){
                    if(is_string($k)){
                            $this->arrayToXML($this->append($to, $k), $v);
                    }else{
                            $this->arrayToXML($to, $v);
                    }
                }else{
                    $this->append($to, $k, $v, true);
                }
            }
        }catch (Exception $e){
            exit ('<pre>'.$e);
        }
    }

    public function showXML(){
        $this->XML->formatOutput = true;
        return $this->XML->saveXML();
    }

    public function render($path, $show = false){
        $xsl = array('<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">');

        foreach($path as $v){
            if(file_exists($this->folder . $v))
                $xsl[] = implode('', file($this->folder . $v));
        }
        $xsl[] = '</xsl:stylesheet>';

		if($show){
			return implode('', $xsl);
		}
		
        $this->XSL->loadXML(implode('', $xsl));

        $xslt = new XSLTProcessor();
        $xslt->importStylesheet($this->XSL);
        return $xslt->transformToXML($this->XML);
    }



    //-->
    public function is_html($str,$count = FALSE){ 
        $html =array(
            'A','ABBR','ACRONYM','ADDRESS','APPLET','AREA','B','BASE','BASEFONT','BDO',
            'BIG','BLOCKQUOTE','BODY','BR','BUTTON','CAPTION','CENTER','CITE','CODE','COL',
            'COLGROUP','DD','DEL','DFN','DIR','DIV','DL','DT','EM','FIELDSET','FONT','FORM',
            'FRAME','FRAMESET','H1','H2','H3','H4','H5','H6','HEAD','HR','HTML','I','IFRAME',
            'IMG','INPUT','INS','ISINDEX','KBD','LABEL','LEGEND','LI','LINK','MAP','MENU','META',
            'NOFRAMES','NOSCRIPT','OBJECT','OL','OPTGROUP','OPTION','P','PARAM','PRE','Q',
            'S','SAMP','SCRIPT','SELECT','SMALL','SPAN','STRIKE','STRONG','STYLE','SUB',
            'SUP','TABLE','TBODY','TD','TEXTAREA','TFOOT','TH','THEAD','TITLE','TR','TT','U','UL','VAR'); 
        if(preg_match_all("~(<\/?)\b(".implode('|',$html).")\b([^>]*>)~i",$str,$c)){ 
            if($count) 
                return array(TRUE, count($c[0])); 
            else 
                return TRUE; 
        }else{ 
            return FALSE; 
        } 
    }

}