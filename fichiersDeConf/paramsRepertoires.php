<?php

// Informations sur les dossiers de traitement
define('REP_IMPORT_MAN', '/IMPORTATIONS/importManuel/');
define('REP_IMPORT_AUTO', '/IMPORTATIONS/importAuto/');
define('REP_SUCCES', '/IMPORTATIONS/Succes/');
define('REP_ECHEC', '/IMPORTATIONS/Echec/');
define('REP_DS', '/IMPORTATIONS/donneesSante/');
define('REP_JSON', '/fichiersDeConf/');
define('REP_LOGS_INDIV', '../LOGs/logsIndividuels');

// Fichiers importants
define('FIC_JSON_SPEC', '/fichiersDeConf/importSpec.json');
define('FIC_LST_MAIL', '/fichiersDeConf/listeMailImportAuto.php');
define('FIC_MDP_JSON', '/fichiersDeConf/jsonMdpChargement.txt');
define('FIC_MDP_MAIL', '/fichiersDeConf/lstMailMdpChargement.txt');

// Emplacement des différents fichiers de logs
define('HIST_LOG', '../LOGs/hist.log');
define('ERROR_LOG', '../LOGs/error.log');

// Nombre de lignes lues dans les différents fichiers de logs
define('TAILLE_LECTURE_LOG_ERR', 4000);
define('TAILLE_LECTURE_LOG_HIST', 4000);

?>