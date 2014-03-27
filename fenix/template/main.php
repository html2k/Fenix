<?
$GLOB['menu'] = array(
    'project' => $lang['menu.project'],
    'struct' => $lang['menu.struct'],
    'setting' => $lang['menu.setting']
);


Fx::app()->url = array(
    'home' => '/' . $config['folder']['sys'] . '/index.php'
);

//-> leftMenu
Fx::app()->leftMenu = '';


$script_config = implode('', array(
    '<script>',
        'window.CONFIG_CKEDITOR = ' . json_encode($config['ckeditor_config']),
    '</script>'
));



