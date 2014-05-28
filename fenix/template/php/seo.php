<?
    //TODO robots.txt
    //TODO sitemap.xml
    //TODO rewrite_url
    //TODO chpu_list
    //TODO page_status

    Fx::context()->menu = array(
        'robots' => 'robots.txt',
        'sitemap' => 'sitemap.xml',
        'rewrite' => 'Редиректы',
        'history_link' => 'Страници'
    );
    Fx::context()->page = isset($_GET['page']) ? $_GET['page'] : false;