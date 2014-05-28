<?
    $lastChange = Fx::db()->extract(Fx::db()->go(array(
        'event' => 'find',
        'from' => Fx::service_context()->namespace['construct_db'],
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

    $dbInfo = Fx::db()->tables_info();


