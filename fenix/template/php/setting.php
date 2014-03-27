<?
access(0); // Root

Fx::app()->users = Fx::db()->find(Fx::app()->namespace['user']);