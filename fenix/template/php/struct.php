<?
    access(0,1); // Root / Admin

    $markerList = Fx::db()->find(Fx::service_context()->namespace['marker']);
    $templateList = Fx::db()->find(Fx::service_context()->namespace['template']);

