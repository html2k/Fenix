<?
    $extWidget = $Extension->get('home.widget');

    $extApp = $Extension->get('home.app');


    $lastChange = $db->extract($db->go(array(
        'event' => 'find',
        'from' => $GLOB['namespace']['construct_db'],
        'order' => '`date` DESC',
        'limit' => 15
    )), function($item, $db){

        $data = $db->findOne($item['object'], array('id' => $item['id']));

        return array(
            'date' => date('d.m.Y G:i', $item['date']),
            'id' => $item['id'],
            'name' => (isset($data['name']) && $data['name'] !== '' ? $data['name'] : 'undefiend-' . $item['id']),
            'url' => '?mode=elem&id='.$item['id']
        );

    });


