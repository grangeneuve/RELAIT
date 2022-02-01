<?php

require_once('ctrls/ctrlServer.php');

include_once('../fichiersDeConf/paramsRepertoires.php');

// on outrepasse les limites de temps internes à PHP puisque les gros imports les dépassent facilement
// 0 correspond à illimité
ini_set('max_execution_time', 0);

date_default_timezone_set('Europe/Berlin');

$ctrlServer = new CtrlServer();

if (isset($_POST['type'])) {
    // Les requêtes POST finissent dans cette branche du 'if'
    // s'il y a une valeur dans la variable $_POST, on exécute le bloc de code en conséquence
    switch ($_POST['type']) {
        case "IMPORT_MANUEL":
            // $_FILES représente le tableau de fichiers qui est envoyé dans la requête POST via le file picker
            // on récupère le nom (name) du fichier envoyé depuis l'HTML.
            if (!empty($_FILES['fichierAImp']['name'])) {
                // on construit le chemin complet en concaténant le répertoire et le nom du fichier
                $fichierACharger = '../' . REP_IMPORT_MAN . basename($_FILES['fichierAImp']['name']);
                echo $ctrlServer->traitementFichierMan($fichierACharger, 'fichierAImp');
            } else {
                echo 'Veuillez fournir un fichier svp';
            }
            break;
        case "CONSULT_LOG_ERR":
            echo $ctrlServer->ouvrirLog('ERREUR');
            break;
        case "CONSULT_LOG_HIST":
            echo $ctrlServer->ouvrirLog('HISTORIQUE');
            break;
        case "TELECHARGER_JSON":
            echo $ctrlServer->telechargerFichier('JSON');
            break;
        case "TELECHARGER_LST_MAIL":
            echo $ctrlServer->telechargerFichier('LST_MAIL');
            break;
        case "TELECHARGER_LOG_ERR":
            echo $ctrlServer->telechargerFichier('ERREUR');
            break;
        case "SUPPRIMER_LOG_ERR":
            echo $ctrlServer->supprimerFichier('ERREUR');
            break;
        case "IMPORTER_JSON":
            if (!empty($_FILES['fichierJson']['name'])) {
                $ctrlServer->supprimerFichier('JSON');
                // on construit le chemin complet en concaténant le répertoire et le nom du fichier
                $fichierACharger = '../' . REP_JSON . basename($_FILES['fichierJson']['name']);
                $retour = $ctrlServer->chargerFichierSrv($fichierACharger, 'fichierJson');
                $ctrlServer->renommerFichier('../' . REP_JSON . basename($_FILES['fichierJson']['name']), '../' . FIC_JSON_SPEC);
                switch ($retour) {
                    case iFileUploadStatus::FICHIER_CHARGE:
                        echo 'Le fichier de paramètres (JSON) a bien été remplacé.';
                        break;
                    case iFileUploadStatus::ERREUR_CHARGEMENT_FICHIER:
                        echo 'Une erreur s\'est produite lors du chargement.';
                        break;
                    case iFileUploadStatus::FICHIER_NON_CHARGE:
                        echo 'Le fichier n\'a pas été remplacé. Erreur générale.';
                        break;
                }
            } else {
                echo 'Veuillez fournir un fichier svp';
            }
            break;
        case "IMPORTER_LST_MAIL":
            if (!empty($_FILES['fichierLstMail']['name'])) {
                $ctrlServer->supprimerFichier('LST_MAIL');
                // on construit le chemin complet en concaténant le répertoire et le nom du fichier
                $fichierACharger = '../' . REP_JSON . basename($_FILES['fichierLstMail']['name']);
                $retour = $ctrlServer->chargerFichierSrv($fichierACharger, 'fichierLstMail');
                $ctrlServer->renommerFichier('../' . REP_JSON . basename($_FILES['fichierLstMail']['name']), '../' . FIC_LST_MAIL);
                switch ($retour) {
                    case iFileUploadStatus::FICHIER_CHARGE:
                        echo 'La liste d\'adresses mail a bien été remplacée.';
                        break;
                    case iFileUploadStatus::ERREUR_CHARGEMENT_FICHIER:
                        echo 'Une erreur s\'est produite lors du chargement.';
                        break;
                    case iFileUploadStatus::FICHIER_NON_CHARGE:
                        echo 'La liste n\'a pas été remplacée. Erreur générale.';
                        break;
                }
            } else {
                echo 'Veuillez fournir un fichier svp';
            }
            break;
        case "SUPPRESSION_DONNEES_SANTE":
            echo $ctrlServer->supprimerDonneesSante($_POST['fed'], $_POST['date']);
            break;
    }
} else if (isset($_GET['function'])) {
    // Les requête GET finissent dans cette branche-ci
    // s'il y a une valeur dans la variable $_GET, on exécute le bloc de code en conséquence
    switch ($_GET['function']) {
        case "lstSante":
            echo $ctrlServer->recupererLaListeDesFederations();
            echo $ctrlServer->recupererDatesImportationDonneesSante();
            break;
        case "impAuto":
            echo $ctrlServer->traitementFichierAuto();
            break;
        case "verifierMdpJSON":
            echo $ctrlServer->verificationMdp($_GET['mdpJ'], 'JSON');
            break;
        case "verifierMdpMAIL":
            echo $ctrlServer->verificationMdp($_GET['mdpM'], 'LST_MAIL');
            break;
    }
} else {
    echo "Le formulaire est nul";
}