<?php
/*
 * Script créant et vérifiant que les champs requis s'ajoutent bien
 */

if(!defined('INC_FROM_DOLIBARR')) {
    define('INC_FROM_CRON_SCRIPT', true);
    require('../config.php');
    $ATMdb=new TPDOdb;
    $ATMdb->debug=true;
}
else{
    $ATMdb=new TPDOdb;
    
}

global $db;

dol_include_once('/jouroff/class/jouroff.class.php');

$o=new TRH_JoursFeries($db);
$o->init_db_by_vars($ATMdb);