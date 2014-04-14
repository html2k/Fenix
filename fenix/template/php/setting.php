<?
access(0); // Root

Fx::context()->users = Fx::db()->find(Fx::context()->namespace['user']);