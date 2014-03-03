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
            $text = $this->escapeEntitis($text);
            if(($fragment || $this->is_html($text)) && $text != ''){
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


    public static function escapeEntitis($string){
        $entiti = array(
            '&nbsp;' => '&#160;','&iexcl;' => '&#161;','&cent;' => '&#162;','&pound;' => '&#163;','&curren;' => '&#164;','&yen;' => '&#165;','&brvbar;' => '&#166;','&sect;' => '&#167;','&uml;' => '&#168;','&copy;' => '&#169;','&ordf;' => '&#170;','&laquo;' => '&#171;','&not;' => '&#172;','&shy;' => '&#173;','&reg;' => '&#174;','&macr;' => '&#175;','&deg;' => '&#176;','&plusmn;' => '&#177;','&sup2;' => '&#178;','&sup3;' => '&#179;','&acute;' => '&#180;','&micro;' => '&#181;','&para;' => '&#182;','&middot;' => '&#183;','&cedil;' => '&#184;','&sup1;' => '&#185;','&ordm;' => '&#186;','&raquo;' => '&#187;','&frac14;' => '&#188;','&frac12;' => '&#189;','&frac34;' => '&#190;','&iquest;' => '&#191;','&Agrave;' => '&#192;','&Aacute;' => '&#193;','&Acirc;' => '&#194;','&Atilde;' => '&#195;','&Auml;' => '&#196;','&Aring;' => '&#197;','&AElig;' => '&#198;','&Ccedil;' => '&#199;','&Egrave;' => '&#200;','&Eacute;' => '&#201;','&Ecirc;' => '&#202;','&Euml;' => '&#203;','&Igrave;' => '&#204;','&Iacute;' => '&#205;','&Icirc;' => '&#206;','&Iuml;' => '&#207;','&ETH;' => '&#208;','&Ntilde;' => '&#209;','&Ograve;' => '&#210;','&Oacute;' => '&#211;','&Ocirc;' => '&#212;','&Otilde;' => '&#213;','&Ouml;' => '&#214;','&times;' => '&#215;','&Oslash;' => '&#216;','&Ugrave;' => '&#217;','&Uacute;' => '&#218;','&Ucirc;' => '&#219;','&Uuml;' => '&#220;','&Yacute;' => '&#221;','&THORN;' => '&#222;','&szlig;' => '&#223;','&agrave;' => '&#224;','&aacute;' => '&#225;','&acirc;' => '&#226;','&atilde;' => '&#227;','&auml;' => '&#228;','&aring;' => '&#229;','&aelig;' => '&#230;','&ccedil;' => '&#231;','&egrave;' => '&#232;','&eacute;' => '&#233;','&ecirc;' => '&#234;','&euml;' => '&#235;','&igrave;' => '&#236;','&iacute;' => '&#237;','&icirc;' => '&#238;','&iuml;' => '&#239;','&eth;' => '&#240;','&ntilde;' => '&#241;','&ograve;' => '&#242;','&oacute;' => '&#243;','&ocirc;' => '&#244;','&otilde;' => '&#245;','&ouml;' => '&#246;','&divide;' => '&#247;','&oslash;' => '&#248;','&ugrave;' => '&#249;','&uacute;' => '&#250;','&ucirc;' => '&#251;','&uuml;' => '&#252;','&yacute;' => '&#253;','&thorn;' => '&#254;','&yuml;' => '&#255;','&fnof;' => '&#402;','&Alpha;' => '&#913;','&Beta;' => '&#914;','&Gamma;' => '&#915;','&Delta;' => '&#916;','&Epsilon;' => '&#917;','&Zeta;' => '&#918;','&Eta;' => '&#919;','&Theta;' => '&#920;','&Iota;' => '&#921;','&Kappa;' => '&#922;','&Lambda;' => '&#923;','&Mu;' => '&#924;','&Nu;' => '&#925;','&Xi;' => '&#926;','&Omicron;' => '&#927;','&Pi;' => '&#928;','&Rho;' => '&#929;','&Sigma;' => '&#931;','&Tau;' => '&#932;','&Upsilon;' => '&#933;','&Phi;' => '&#934;','&Chi;' => '&#935;','&Psi;' => '&#936;','&Omega;' => '&#937;','&alpha;' => '&#945;','&beta;' => '&#946;','&gamma;' => '&#947;','&delta;' => '&#948;','&epsilon;' => '&#949;','&zeta;' => '&#950;','&eta;' => '&#951;','&theta;' => '&#952;','&iota;' => '&#953;','&kappa;' => '&#954;','&lambda;' => '&#955;','&mu;' => '&#956;','&nu;' => '&#957;','&xi;' => '&#958;','&omicron;' => '&#959;','&pi;' => '&#960;','&rho;' => '&#961;','&sigmaf;' => '&#962;','&sigma;' => '&#963;','&tau;' => '&#964;','&upsilon;' => '&#965;','&phi;' => '&#966;','&chi;' => '&#967;','&psi;' => '&#968;','&omega;' => '&#969;','&thetasy;' => '&#977;','&upsih;' => '&#978;','&piv;' => '&#982;','&harr;' => '&#8596;','&crarr;' => '&#8629;','&lArr;' => '&#8656;','&uArr;' => '&#8657;','&rArr;' => '&#8658;','&dArr;' => '&#8659;','&hArr;' => '&#8660;','&forall;' => '&#8704;','&part;' => '&#8706;','&exist;' => '&#8707;','&empty;' => '&#8709;','&nabla;' => '&#8711;','&isin;' => '&#8712;','&notin;' => '&#8713;','&ni;' => '&#8715;','&prod;' => '&#8719;','&sum;' => '&#8721;','&minus;' => '&#8722;','&lowast;' => '&#8727;','&radic;' => '&#8730;','&prop;' => '&#8733;','&infin;' => '&#8734;','&ang;' => '&#8736;','&and;' => '&#8743;','&or;' => '&#8744;','&cap;' => '&#8745;','&cup;' => '&#8746;','&int;' => '&#8747;','&there4;' => '&#8756;','&sim;' => '&#8764;','&cong;' => '&#8773;','&asymp;' => '&#8776;','&ne;' => '&#8800;','&equiv;' => '&#8801;','&le;' => '&#8804;','&ge;' => '&#8805;','&sub;' => '&#8834;','&sup;' => '&#8835;','&nsub;' => '&#8836;','&sube;' => '&#8838;','&supe;' => '&#8839;','&oplus;' => '&#8853;','&otimes;' => '&#8855;','&perp;' => '&#8869;','&sdot;' => '&#8901;','&lceil;' => '&#8968;','&rceil;' => '&#8969;','&lfloor;' => '&#8970;','&rfloor;' => '&#8971;','&lang;' => '&#9001;','&rang;' => '&#9002;','&loz;' => '&#9674;','&spades;' => '&#9824;','&clubs;' => '&#9827;','&hearts;' => '&#9829;','&diams;' => '&#9830;','&quot;' => '&#34;','&amp;' => '&#38;','&lt;' => '&#60;','&gt;' => '&#62;','&OElig;' => '&#338;','&oelig;' => '&#339;','&Scaron;' => '&#352;','&scaron;' => '&#353;','&Yuml;' => '&#376;','&circ;' => '&#710;','&tilde;' => '&#732;','&ensp;' => '&#8194;','&emsp;' => '&#8195;','&thinsp;' => '&#8201;','&zwnj;' => '&#8204;','&zwj;' => '&#8205;','&lrm;' => '&#8206;','&rlm;' => '&#8207;','&ndash;' => '&#8211;','&mdash;' => '&#8212;','&lsquo;' => '&#8216;','&rsquo;' => '&#8217;','&sbquo;' => '&#8218;','&ldquo;' => '&#8220;','&rdquo;' => '&#8221;','&bdquo;' => '&#8222;','&dagger;' => '&#8224;','&Dagger;' => '&#8225;','&permil;' => '&#8240;','&lsaquo;' => '&#8249;','&rsaquo;' => '&#8250;', '&euro;' => '&#8364;',
        );
        $replace = array_values($entiti);
        $search = array_keys($entiti);
        return str_replace($search, $replace, $string);
    }

}