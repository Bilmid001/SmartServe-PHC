<?php
session_start();

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/database.php';

$db = new DBFlex('mysql', 'localhost', 'bilmid', 'bilmid12345', 'phchms');
