<?
$GLOB['menu'] = array(
    'project' => $lang['menu.project'],
    'struct' => $lang['menu.struct'],
    'setting' => $lang['menu.setting']
);


$GLOB['url'] = array(
    'home' => '/' . $config['folder']['sys'] . '/index.php'
);


//--> topMenu
$str = array();
foreach($GLOB['menu'] as $k => $v){
    $current = (mode == $k) ? ' class="current"' : '';
    $str[] = '<li'.$current.'><a href="?mode='.$k.'">'.$v.'</a></li>';
}
$GLOB['topMenu'] = implode("\n", $str);



//-> leftMenu
$GLOB['leftMenu'] = '';




//->Static
$static->addFile(sys.'/template/js/jq.js');
$static->addFile(sys.'/template/js/lib.js');
$static->addFile(sys.'/template/js/datepicker.js');
$static->addFile(sys.'/plugin/ckeditor/ckeditor.js', false);
$static->addFile(sys.'/plugin/ckeditor/config.js');
$static->addFile(sys.'/plugin/ckeditor/adapters/jquery.js');
$static->addFile(sys.'/template/js/main.js');
$static->addFile(sys.'/template/js/struct.js');

$static->addFile(sys.'/template/css/reset.css');
$static->addFile(sys.'/template/font/fontello.css');
$static->addFile(sys.'/template/font/animation.css', false);
$static->addFile(sys.'/template/css/responsive-style.css');
$static->addFile(sys.'/template/css/style.css');
$static->addFile(sys.'/template/css/sys.css');



	//->Bloks
	$static->addFile(sys.'/template/tpl/blocks/table/table.js');
	$static->addFile(sys.'/template/tpl/blocks/project/project.js');


	$static->addFile(sys.'/template/tpl/blocks/struct/struct.css');

	




