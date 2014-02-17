<?

$manifest_id = array('name' => 'id', 'type' => 'int', 'size' => 11, 'index' => 'AP');

$manifest = array(
    'baseCollection' => array(
        'user' => array(
            $manifest_id,
            array('name' => 'login',     'type' => 'string', 'size' => 250),
            array('name' => 'pass',      'type' => 'string', 'size' => 250),
            array('name' => 'last_date', 'type' => 'int',    'size' => 11),
            array('name' => 'access',    'type' => 'int',    'size' => 2),
        ),
        'struct_db' => array(
            $manifest_id,
            array('name' => 'name',         'type' => 'string', 'size' => 128),
            array('name' => 'code',         'type' => 'string', 'size' => 128),
            array('name' => 'submission',   'type' => 'int',    'size' => 4),
            array('name' => 'icon',         'type' => 'string', 'size' => 128),
            array('name' => 'show_wood',    'type' => 'int',    'size' => 2),
            array('name' => 'base_marker',  'type' => 'int',    'size' => 2),
            array('name' => 'show_sistem',  'type' => 'int',    'size' => 2),
            array('name' => 'ref',          'type' => 'int',    'size' => 11)
        ),
        'struct_td' => array(
            $manifest_id,
            array('name' => 'parent',   'type' => 'int',    'size' => 11),
            array('name' => 'name',     'type' => 'string', 'size' => 128),
            array('name' => 'code',     'type' => 'string', 'size' => 128),
            array('name' => 'num',      'type' => 'int',    'size' => 3),
            array('name' => 'type',     'type' => 'string', 'size' => 128),
            array('name' => 'param',    'type' => 'string', 'size' => 128),
            array('name' => 'size',     'type' => 'int',    'size' => 3)
        ),
        'construct_db' => array(
            $manifest_id,
            array('name' => 'parent',       'type' => 'int',    'size' => 11),
            array('name' => 'ref',          'type' => 'int',    'size' => 11),
            array('name' => 'object',       'type' => 'string', 'size' => 128),
            array('name' => 'num',          'type' => 'int',    'size' => 11),
            array('name' => 'chpu',         'type' => 'string', 'size' => 128),
            array('name' => 'hide',         'type' => 'int',    'size' => 2),
            array('name' => 'active_path',  'type' => 'int',    'size' => 2),
            array('name' => 'marker',       'type' => 'int',    'size' => 3),
            array('name' => 'date',         'type' => 'int',    'size' => 11)
        ),
        'marker' => array(
            $manifest_id,
            array('name' => 'template_id',  'type' => 'string', 'size' => 128),
            array('name' => 'name',         'type' => 'string', 'size' => 128)
        ),
        'template' => array(
            $manifest_id,
            array('name' => 'name',     'type' => 'string', 'size' => 128)
        ),
        'moduls' => array(
            $manifest_id,
            array('name' => 'name',     'type' => 'string', 'size' => 128),
            array('name' => 'code',     'type' => 'string', 'size' => 128),
            array('name' => 'icon',     'type' => 'string', 'size' => 128),
            array('name' => 'access',   'type' => 'int', 'size' => 2),
            array('name' => 'ver',      'type' => 'string', 'size' => 128),
            array('name' => 'type',     'type' => 'string', 'size' => 128)
        )
    ),
    'access' => array(
        'root',
        'admin',
        'user'
    ),
    'templating' => array(
        'scooby' => array('xsl', 'php'),
        'twig' => array('twig', 'php'),
        'tpl' => array('html', 'php')
    ),
    
    'gist' => array(
        'string' => array( // Строка
            'name' => 'Строка',
            'type' => 'string',
            'size' => 128,
            'param' => array( 'size' )
        ),
        'text' => array( // Текст
            'name' => 'Текст',
            'type' => 'text',
            'param' => array()
        ),
        'file' => array( // Файл
            'name' => 'Файл',
            'type' => 'string',
            'size' => 128,
            'param' => array( 'size', 'zip' )
        ),
        'image' => array( // Изображение
            'name' => 'Изображение',
            'type' => 'string',
            'size' => 128,
            'param' => array( 'size', 'scale', 'method', 'position' )
        ),
        'list_radio' => array( // Радио кнопки
            'name' => 'Список значений',
            'type' => 'int',
            'size' => 11,
            'param' => array( 'list' )
        ),
        'list_checked' => array( // Чекбоксы
            'name' => 'Чекбоксы',
            'type' => 'int',
            'size' => 11,
            'param' => array( 'list' )
        ),
        'list_options' => array( // Выподающий список
            'name' => 'Выпадающий список',
            'type' => 'int',
            'size' => 11,
            'param' => array( 'list' )
        ),
        'date' => array( // Поле даты
            'name' => 'Дата',
            'type' => 'int',
            'size' => 11,
            'param' => array()
        )
    ),
    'defConfig' => array(
        'db' => array(
            'type' => 'mysql',
            'name' => 'fenix',
            'user' => 'root',
            'pass' => '',
            'port' => '',
            'host' => '',
            'sys_namespace' => '_systems_'
        ),

        'project_name' => 'Test',

        'templating' => 'scooby',
        'lang' => 'ru',
        'folder' => array(
            'sys' => 'fenix',
            'template' => 'template',
            'files' => 'files',
            'extension' => 'ext'
        )
    )
);