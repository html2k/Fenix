<?
    $markerList = $db->find($GLOB['namespace']['marker']);
    $templateList = $db->find($GLOB['namespace']['template']);

    $tableInfo = $db->tables_info();
    $tableInfo['data'] = array();
    foreach ($tableInfo['table'] as $key => $value) {
    	$tableInfo['data'][$key] = $db->extract($db->go(array(
    		'event' => 'find',
    		'from' => $key,
    		'limit' => 25
    		)));
    }
    //debug($db->tables_info());