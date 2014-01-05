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

