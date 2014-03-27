<?
    access(0,1); // Root / Admin

    $markerList = Fx::db()->find(Fx::app()->namespace['marker']);
    $templateList = Fx::db()->find(Fx::app()->namespace['template']);

    $tableInfo = Fx::db()->tables_info();
    $tableInfo['data'] = array();
    foreach ($tableInfo['table'] as $key => $value) {
    	$tableInfo['data'][$key] = Fx::db()->extract(Fx::db()->go(array(
    		'event' => 'find',
    		'from' => $key,
    		'limit' => 25
    		)));
    }