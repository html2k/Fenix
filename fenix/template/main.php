<?

$GLOB['menu'] = array(
    'project' => $lang['menu.project'],
    'seo&page=robots' => $lang['menu.seo'],
    'struct' => $lang['menu.struct'],
    'setting' => $lang['menu.setting']
);


Fx::context()->url = array(
    'home' => '/' . $config['folder']['sys'] . '/index.php'
);

//-> leftMenu
Fx::context()->leftMenu = '';


$script_config = implode('', array(
    '<script>',
        'window.CONFIG_CKEDITOR = ' . json_encode($config['ckeditor_config']),
    '</script>'
));


