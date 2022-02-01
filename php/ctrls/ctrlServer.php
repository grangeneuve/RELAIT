<?php

require_once('workers/workerServer.php');
require_once('../fichiersDeConf/iTypeOfFile.php');
require_once('../fichiersDeConf/iFileImportStatus.php');

/**
 * Classe CtrlServer
 *
 * Cette classe est le point central, du côté serveur, de la partie 'intelligence'. Les différentes actions que la
 * partie métier exécute sont décidées ici.
 *
 * @version 1.1
 * @author Pittet David
 * @projet IAG - données lait
 */
class CtrlServer implements iTypeOfFile, iFileUploadStatus {

    /**
     * Méthode globale qui traite l'ensemble des imports de fichiers, pour la partie manuelle,
     * qu'ils soients valides ou non.
     *
     * @param $fichierACharger le chemin complet du fichier à traiter
     * @param $nomFichier le nom du fichier dans le tableau POST
     */
    public function traitementFichierMan($fichierACharger, $nomChampForm) {
        $workerServer = new WorkerServer();

        if ($workerServer->estJSONPresent()) {
            // si la connexion à la BD est établie
            if ($workerServer->estBDConnectee()) {
                // on charge le fichier dans le répertoire d'import manuel
                switch ($statutErreur = $workerServer->chargerFichierSrv($fichierACharger, $nomChampForm)) {
                    case iFileUploadStatus::FICHIER_DEJA_PRESENT:
                    case iFileUploadStatus::FICHIER_CHARGE:
                        $workerServer->inscrireLogErreur('----------------------------Fichier ' . basename($fichierACharger) . '----------------------------', ERROR_LOG, false);
                        $workerServer->inscrireLogErreur('----------------------------Fichier ' . basename($fichierACharger) . '----------------------------', REP_LOGS_INDIV . '/' . date("Ymd") . '_' . basename($fichierACharger), false);
                        $this->importerFichier($fichierACharger);
                        break;
                    case iFileUploadStatus::ERREUR_CHARGEMENT_FICHIER:
                    case iFileUploadStatus::FICHIER_NON_CHARGE:
                        $workerServer->inscrireLogHist(basename($fichierACharger) . " => fichier impossible à charger.");
                        echo 'Le fichier n\'a pas pu être chargé';
                        break;
                }
            }
        } else {
            echo 'Le fichier de paramètres (JSON) n\'est pas présent' . '<br>';
            $workerServer->inscrireLogErreur('Importation manuelle impossible, fichier de paramètres (JSON) manquant.', ERROR_LOG, true);
        }
    }

    /**
     * Transmet l'information de chargement d'un fichier sur le serveur.
     *
     * @param $fichierACharger le chemin complet du fichier à charger
     * @param $nomChampForm le nom du formulaire appelant la méthode
     *
     * @return information sur le statut de chargement
     */
    public function chargerFichierSrv($fichierACharger, $nomChampForm) {
        $workerServer = new WorkerServer();
        return $workerServer->chargerFichierSrv($fichierACharger, $nomChampForm);
    }

    /**
     * Transmet l'information de renommage d'un fichier sur le serveur.
     * 
     * @param $ancienNom du fichier
     * @param $nouveauNom du fichier
     * 
     * @return le statut sur le renommage
     */
    public function renommerFichier($ancienNom, $nouveauNom) {
        $workerServer = new WorkerServer();
        return $workerServer->renommerFichier($ancienNom, $nouveauNom);
    }

    public function retourNbrErreurImpMan() {
        $workerServer = new WorkerServer();
        $tabDesMessages = $workerServer->analyseContenuLogsIndiv();
        $information = $tabDesMessages[0];
        $attention = $tabDesMessages[1];
        $erreur = $tabDesMessages[2];
        $logsAvecErreurs = array_unique($tabDesMessages[3]);

        $corpsMessage = "<br><b>Résultats de l'importation des données santé:</b><br>";
        $corpsMessage .= "<ul><li>" . $information . " lignes de fonctionnement (information sur le nom de fichier, ...)</li>"
                . "<li>" . $attention . " lignes qui représentent des erreurs connues/attendues (duplicates, ...)</li>"
                . "<li><b>" . $erreur . " lignes d'erreurs inattendues</li></b></ul>";
        $corpsMessage .= "Veuillez consulter les logs afin d'avoir plus d'informations quant aux erreurs.<br><br>";
        
        echo $corpsMessage;
        foreach ($logsAvecErreurs as $unlog) {
            echo "<a href='RELAIT/$unlog' download style='color: red; background-color:#ffffa0'>" . 'Télécharger ' . basename($unlog) . '</a><br>';
        }
    }

    /**
     * Partie spécifique au traitement des différents types de fichiers.
     *
     * @param $fichier le chemin du fichier à traiter
     */
    public function importerFichier($fichier) {
        $workerServer = new WorkerServer();
        $typeFichier = $workerServer->identifierTypeFichier($fichier);
        switch ($typeFichier) {
            // cas des fichiers données lait
            case is_string($typeFichier):
                if ($workerServer->estJSONPresent()) {
                    $tabStatutApresInsert = $workerServer->importerFichierDonneesLait(REP_IMPORT_MAN, basename($fichier), $typeFichier);
                    $this->traitementApresImport($tabStatutApresInsert[0], $fichier);
                    
                    $this->retourNbrErreurImpMan();
                } else {
                    echo 'Le fichier de paramètres (JSON) n\'est pas présent' . '<br>';
                }
                break;
            // cas des fichiers données santé
            case iTypeOfFile::TYPE_DS:
                if ($workerServer->estJSONPresent()) {
                    $statutApresInsert = $workerServer->importerFichierDonneesSante(REP_IMPORT_MAN, basename($fichier), 'Données Santé');
                    $this->traitementApresImport($statutApresInsert, $fichier);

                    $this->retourNbrErreurImpMan();
                } else {
                    echo "Le fichier de paramètres (JSON) n\'est pas présent" . '<br>';
                }
                break;
            // cas dans lequel on atterit si le fichier n'est pas un Y01/K03/K10 ou données santé
            // Il se voit déplacé et un message est affiché.
            case iTypeOfFile::FICHIER_INCONNU:
            case iTypeOfFile::OUVERTURE_IMPOSSIBLE:
                if ($workerServer->deplacerFichier($fichier, REP_ECHEC)) {
                    echo 'Ce fichier n\'est pas valide et a été déplacé dans le répertoire d\'échec.' . '<br>';
                } else {
                    echo 'Ce fichier n\'est pas valide mais ne peut être déplacé...' . '<br>';
                }
                $workerServer->inscrireLogHist(basename($fichier) . " => fichier ignoré car hors-contexte.");
                break;
        }
    }

    /**
     * Partie spécifique au traitement de la valeur de retour, si succès ou non il y a lors de l'importation des
     * des données.
     *
     * @param $statutApresInsert la valeur si oui ou non il y a eu des erreurs lors de l'import
     * @param $fichier le fichier en cours de traitement
     */
    public function traitementApresImport($statutApresInsert, $fichier) {
        $workerServer = new WorkerServer();
        if ($statutApresInsert === iFileImportStatus::INSERT_OK) {
            if ($workerServer->deplacerFichier($fichier, REP_SUCCES)) {
                echo 'Ce fichier valide (' . basename($fichier) . ') a été déplacé dans le répertoire de succès après insert de son contenu.' . '<br>';
            } else {
                echo 'Ce fichier valide (' . basename($fichier) . ') ne peut être déplacé après insert réussi de son contenu...' . '<br>';
            }
            $workerServer->inscrireLogHist(basename($fichier) . " => inséré avec succès.");
        } else if ($statutApresInsert === iFileImportStatus::INSERT_OK_AVEC_ERREURS) {
            if ($workerServer->deplacerFichier($fichier, REP_ECHEC)) {
                echo 'Ce fichier valide (' . basename($fichier) . ') a été déplacé dans le répertoire d\'échec après insert de son contenu. Des erreurs sont survenues. Veuillez consulter les logs' . '<br>';
            } else {
                echo 'Ce fichier valide (' . basename($fichier) . ') ne peut être déplacé après insert de son contenu...' . '<br>';
            }
            $workerServer->inscrireLogHist(basename($fichier) . " => inséré, mais certains enregistrements ignorés du fait d'erreurs.");
        } else if ($statutApresInsert === iFileImportStatus::EXPLOITATION_IMPOSSIBLE) {
            if ($workerServer->deplacerFichier($fichier, REP_ECHEC)) {
                echo 'Ce fichier (' . basename($fichier) . ') présente des erreurs. Impossible d\'insérer son contenu. Il a été placé dans le répertoire d\'échec' . '<br>';
            } else {
                echo 'Ce fichier (' . basename($fichier) . ') présentant des erreurs ne peut être déplacé...' . '<br>';
            }
            $workerServer->inscrireLogHist(basename($fichier) . " => fichier ignoré, impossible de lire son contenu.");
        }
    }

    /**
     * Transmet l'information d'ouverture d'un log en fonction du type au workerServer.
     *
     * @param $type chaîne de caractères spécifiant le type de log
     *
     * @return string le contenu du log
     */
    public function ouvrirLog($type) {
        $workerServer = new WorkerServer();
        return $workerServer->ouvrirLog($type);
    }

    /**
     * Méthode appelée par la partie cliente pour récupérer la liste des fédérations, contenue dans une constante que le
     * workerServer s'occupe de retourner. La liste est directement constituée et affichée ici.
     *
     */
    public function recupererLaListeDesFederations() {
        $workerServer = new WorkerServer();
        $lst = $workerServer->recupererLaListeDesFederations();
        if ($lst !== null) {
            echo "<select id=\"fed\" name=\"fed\" class=\"form-control\">";
            foreach ($lst as $item) {
                echo "<option value='$item'>$item</option>";
            }
            echo "</select>";
        } else {
            echo "<select id=\"date\" name=\"date\" class=\"form-control\">";
            echo "<option value='vide'>Aucune fédération à afficher</option>";
            echo "</select>";
        }
    }

    /**
     * Méthode appelée par la partie cliente pour récupérer la liste des date d'importation dans la table 'Données Santé'
     * via le workerServer puis le workerServicesDB. La liste est directement constituée et affichée ici.
     *
     */
    public function recupererDatesImportationDonneesSante() {
        $workerServer = new WorkerServer();
        $lst = $workerServer->recupererDatesImportationDonneesSante();
        if ($lst !== null) {
            echo "<select id=\"date\" name=\"date\" class=\"form-control\">";
            foreach ($lst as $item) {
                echo "<option value='$item'>$item</option>";
            }
            echo "</select>";
        } else {
            echo "<select id=\"date\" name=\"date\" class=\"form-control\">";
            echo "<option value='vide'>Aucune date à afficher</option>";
            echo "</select>";
        }
    }

    /**
     * Transmet l'information de suppression des données santés concernées par le type de fédération et la date d'import
     * spécifiés.
     *
     * @param $federation chaîne de caractères définissant le type de fédération
     * @param $dateImport chaîne de caractères définissant la date d'import
     */
    public function supprimerDonneesSante($federation, $dateImport) {
        $workerServer = new WorkerServer();
        echo $workerServer->supprimerDonneesSante($federation, $dateImport);
    }

    /**
     * Mécanique de traitement automatique des fichiers. Le tableau représentant les fichiers du répertoire est constitué,
     * puis l'appel des différentes méthodes métiers permet de les traiter les uns après les autres. Il existe deux tableaux,
     * un pour les données lait, l'autre pour les données santé puisque les méthodes d'import ne sont pas les mêmes.
     *
     */
    public function traitementFichierAuto() {
        $workerServer = new WorkerServer();
        if ($workerServer->estJSONPresent()) {
            $arrayFiles = $workerServer->dresserCarteDuRepertoire();
            $arrayDL = $arrayFiles[0];
            $arrayDS = $arrayFiles[1];

            $tableauDesExploitationsImportees = [];

            if (sizeof($arrayDL) > 0 || sizeof($arrayDS) > 0) {
                foreach ($arrayDL as $unFichier => $typeFichier) {
                    $workerServer->inscrireLogErreur('----------------------------Fichier ' . basename($unFichier) . '----------------------------', ERROR_LOG, false);
                    $workerServer->inscrireLogErreur('----------------------------Fichier ' . basename($unFichier) . '----------------------------', REP_LOGS_INDIV . '/' . date("Ymd") . '_' . basename($unFichier), false);
                    $tabStatutApresInsert = $workerServer->importerFichierDonneesLait(REP_IMPORT_AUTO, $unFichier, $typeFichier);
                    $this->traitementApresImport($tabStatutApresInsert[0], '../' . REP_IMPORT_AUTO . $unFichier);
                    $tableauDesExploitationsImportees = $tabStatutApresInsert[1];
                }
                foreach ($arrayDS as $unFichier => $typeFichier) {
                    if ($workerServer->deplacerFichier('../' . REP_IMPORT_AUTO . $unFichier, REP_DS)) {
                        echo 'Ce fichier de données santé (' . basename($unFichier) . ') et a été déplacé dans le répertoire dédié.' . '<br>';
                    } else {
                        echo 'Ce fichier de données santé (' . basename($unFichier) . ') ne peut être déplacé...' . '<br>';
                    }
                    $workerServer->inscrireLogHist(basename($unFichier) . " => fichier mis de côté dans le répertoire dédié (Données santé).");
                }
                $arrayFilesRestants = $workerServer->dresserCarteDesFichiersRestants();
                foreach ($arrayFilesRestants as $unFichierAutre) {
                    if ($workerServer->deplacerFichier('../' . REP_IMPORT_AUTO . $unFichierAutre, REP_ECHEC)) {
                        echo 'Ce fichier n\'est pas valide (' . basename($unFichierAutre) . ') et a été déplacé dans le répertoire d\'échec.' . '<br>';
                    } else {
                        echo 'Ce fichier n\'est pas valide (' . basename($unFichierAutre) . ') mais ne peut être déplacé...' . '<br>';
                    }
                    $workerServer->inscrireLogHist(basename($unFichierAutre) . " => fichier ignoré car hors-contexte.");
                }

                $tabDesMessages = $workerServer->analyseContenuLogsIndiv();
                $information = $tabDesMessages[0];
                $attention = $tabDesMessages[1];
                $erreur = $tabDesMessages[2];
                $logsAvecErreurs = $tabDesMessages[3];
                $corpsMessage = "<b>Résultats de l'importation automatique de la dernière nuit</b><br><br>";
                $corpsMessage .= "Lors de la dernière série d'importation automatique, sont à dénombrer:<ul><li>" . $information .
                        " lignes de fonctionnement (information sur le nom de fichier, ...)</li><li>" . $attention . " lignes qui"
                        . " représentent des erreurs connues/attendues (duplicates, ...)</li><li><b>" . $erreur .
                        " lignes d'erreurs inattendues</li></b></ul>";
                $corpsMessage .= "Veuillez consulter les logs afin d'avoir plus d'informations quant aux erreurs.<br><br>";

                $corpsMessage .= "Les exploitations suivantes ont été traitées:<ul>";
                foreach ($tableauDesExploitationsImportees as $uneExploitation) {
                    $corpsMessage .= "<li>" . $uneExploitation . "</li>";
                }
                $corpsMessage .= "</ul>";

                $corpsMessage .= "<br><i>Pour rappel, ces chiffres sont un décompte effectué à partir des"
                        . " lignes générées dans les différents logs.</i>";
                $workerServer->envoiMailImportAuto($corpsMessage, $logsAvecErreurs);
            } else {
                $corpsMessage = "Une importation automatique a été demandée, mais il se trouve qu'il n'y a pas de fichiers à traitier automatiquement.";
                $workerServer->envoiMailImportAuto($corpsMessage, null);
                echo 'Aucun fichier à traiter automatiquement.';
            }
        } else {
            $nbFichiersEnAttente = count($workerServer->dresserCarteDesFichiersRestants());
            $corpsMessage = "Une importation automatique a été demandée, mais il se trouve que le fichier de paramètres (JSON) n'est pas présent.<br>";
            $corpsMessage .= "Il reste <b>" . $nbFichiersEnAttente . " fichier(s)</b> (valide(s) ou non) en attente d'importation.";
            $workerServer->envoiMailImportAuto($corpsMessage, null);
            $workerServer->inscrireLogHist('Importation automatique impossible, fichier de paramètres (JSON) manquant.');
            echo 'Le fichier de paramètres (JSON) n\'est pas présent' . '<br>';
        }
    }

    /**
     * Transmet l'information de suppression d'un fichier parmi les fichiers de fonctionnement.
     *
     * @param chaîne de caractères spécifiant le type de fichier
     *
     * @return string le statut sur la suppression
     */
    public function supprimerFichier($type) {
        $workerServer = new WorkerServer();
        return $workerServer->supprimerFichier($type);
    }

    /**
     * Transmet l'information de téléchargement d'un fichier parmi les fichiers de fonctionnement.
     *
     * @param chaîne de caractères spécifiant le type de fichier
     *
     * @return string le contenu du fichier qui va être téléchargé dans la partie cliente
     */
    public function telechargerFichier($type) {
        $workerServer = new WorkerServer();
        return $workerServer->telechargerFichier($type);
    }

    /**
     * Transmet l'information de téléchargement d'un log en fonction du type au workerServer.
     *
     * @param $mdp chaîne de caractères spécifiant le type de log
     *
     * @return string le contenu du log qui va être téléchargé dans la partie cliente
     */
    public function verificationMdp($mdp, $type) {
        $workerServer = new WorkerServer();
        return $workerServer->verificationMdp($mdp, $type);
    }

}
