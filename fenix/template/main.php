<?
$GLOB['menu'] = array(
    'project' => $lang['menu.project'],
    'struct' => $lang['menu.struct'],
    'setting' => $lang['menu.setting']
);


$GLOB['url'] = array(
    'home' => '/' . $config['folder']['sys'] . '/index.php'
);

//-> leftMenu
$GLOB['leftMenu'] = '';




//->Static
$static->addFile(sys.'/template/js/lib.js');
$static->addFile(sys.'/template/js/datepicker.js');
$static->addFile(sys.'/template/js/main.js');
$static->addFile(sys.'/template/js/search.js');
$static->addFile(sys.'/template/js/struct.js');

$static->addFile(sys.'/template/css/reset.css');
$static->addFile(sys.'/template/font/fontello.css', false);
$static->addFile(sys.'/template/font/animation.css', false);
$static->addFile(sys.'/template/css/responsive-style.css');
$static->addFile(sys.'/template/css/style.css');
$static->addFile(sys.'/template/css/sys.css');



	//->Bloks
	$static->addFile(sys.'/template/tpl/blocks/table/table.js');
	$static->addFile(sys.'/template/tpl/blocks/project/project.js');
    $static->addFile(sys.'/template/tpl/blocks/object/object.js');
    $static->addFile(sys.'/template/js/setting.js');



	$static->addFile(sys.'/template/tpl/blocks/struct/struct.css');
    $static->addFile(sys.'/template/tpl/blocks/object/object.css');

	




