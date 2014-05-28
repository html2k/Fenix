<?
access(0); // Root

Fx::context()->users = Fx::db()->find(Fx::service_context()->namespace['user']);