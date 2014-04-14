<?
    access(0,1); // Root / Admin

    $markerList = Fx::db()->find(Fx::context()->namespace['marker']);
    $templateList = Fx::db()->find(Fx::context()->namespace['template']);

