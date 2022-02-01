<?php

include_once('../fichiersDeConf/paramsRepertoires.php');
include_once('../fichiersDeConf/iTypeOfFile.php');
include_once('../fichiersDeConf/iFileUploadStatus.php');

/**
 * Classe WorkerFileReader
 *
 * Cette classe s'occupe de tout ce qui concerne la lecture et la manipulation des fichiers.
 *
 * @version 1.1
 * @author Pittet David
 * @projet IAG - données lait
 */
class WorkerFileReader implements iTypeOfFile, iFileUploadStatus {

    /**
     * Charge le fichier dans le répertoire défini. Reçoit cette information en paramètre.
     *
     * @param $fichierACharger le chemin complet du fichier où charger
     * @param $nomChampForm le nom du formulaire appelant
     *
     * @return string le message d'état quant à la réussite ou non du chargement
     */
    public function chargerFichierSrv($fichierACharger, $nomChampForm) {
        $statutChargement = iFileUploadStatus::FICHIER_NON_CHARGE;
        if (file_exists($fichierACharger)) {
            $statutChargement = iFileUploadStatus::FICHIER_DEJA_PRESENT;
        } else {
            if (move_uploaded_file($_FILES[$nomChampForm]['tmp_name'], $fichierACharger)) {
                $statutChargement = iFileUploadStatus::FICHIER_CHARGE;
            } else {
                $statutChargement = iFileUploadStatus::ERREUR_CHARGEMENT_FICHIER;
            }
        }
        return $statutChargement;
    }

    /**
     * Ouvre le fichier pour y lire les trois premiers caractères et ainsi déterminer son type. Il est aussi testé le
     * nombre de ';' présent dans une ligne du fichier, afin de s'avoir s'il s'agit d'un fichier de données santé.
     *
     * @param $fichierACharger le chemin complet du fichier à identifier
     *
     * @return string le message d'état quant à la réussite ou non de l'identification
     */
    public function identifierTypeFichier($fichierACharger) {
        $statutChargement = iTypeOfFile::FICHIER_INCONNU;
        if ($fichierATraiter = fopen($fichierACharger, "r")) {
            $typeFichier = fread($fichierATraiter, 3);
            if ($typeFichier == 'K01') {
                $typeFichier = 'Y01';
            }

            /* contient moins de 3 ; sur la première ligne */
            $compte = substr_count(fgets($fichierATraiter), ';') < 3;

            /* verif quon retrouve 3 car puis un espace */
            $format = is_string($typeFichier);
            rewind($fichierATraiter);
            $structure = substr(fread($fichierATraiter, 4), -1) == ' ';
            
            /* verifier l'extension */
            $workerServer = new WorkerServer();
            $tabTypesFichiers = $workerServer->getLesTypesDeFichiers();
            $extension = in_array($typeFichier, $tabTypesFichiers);

            /* verifier si vide */
            $estPasVide = filesize($fichierACharger) > 0;

            /* remettre le pointeur au début du fichier, évite des erreurs de count(fgetcsv) */
            rewind($fichierATraiter);

            if ($compte && $format && $structure && $extension) {
                $statutChargement = $typeFichier;
            } else if ($estPasVide) {
                if (count(fgetcsv($fichierATraiter, 0, ';', '"', '\\')) > 5) {
                    $statutChargement = iTypeOfFile::TYPE_DS;
                }
            }
            fclose($fichierATraiter);
        } else {
            $statutChargement = iTypeOfFile::OUVERTURE_IMPOSSIBLE;
        }
        return $statutChargement;
    }

    /**
     * Déplace le fichier spécifié à sa destination spécifiée. La date est ajoutée
     * pour éviter d'écraser le même fichier déjà importé.
     *
     * @param $fichierADeplacer le chemin complet du fichier où il se trouve
     * @param $repertoireDeDestination le répertoire de destination où déplacer le fichier
     *
     * @return bool true si le fichier a pu être déplacé, false sinon
     */
    public function deplacerFichier($fichierADeplacer, $repertoireDeDestination) {
        return rename($fichierADeplacer, '../' . $repertoireDeDestination . date("Ymd_His") . '_' . basename($fichierADeplacer));
    }

    /**
     * Renomme le fichier spécifié avec le nouveau nom spécifié.
     * 
     * @param $ancienNom du fichier
     * @param $nouveauNom du fichier
     * 
     * @return le statut sur le renommage
     */
    public function renommerFichier($ancienNom, $nouveauNom) {
        return rename($ancienNom, $nouveauNom);
    }

    /**
     * Vérifie la correspondance entre le mot de passe saisi et celui attendu.
     * Le mot de passe attendu se trouve dans un des fichiers de configuration. 
     * 
     * @param $mdp le mot de passe à vérifier
     * @param $type spécifie le fichier de mot de passe utilisé pour comparer
     *              avec la saisie
     * 
     * @return boolean true si les mots de passe correspondent, false sinon
     */
    public function verificationMdp($mdp, $type) {
        switch ($type) {
            case "JSON":
                $mdpOk = false;
                if ($fichierATraiter = fopen('../' . FIC_MDP_JSON, "r")) {
                    $mdpFichier = fread($fichierATraiter, filesize('../' . FIC_MDP_JSON));
                    if ($mdp == $mdpFichier) {
                        $mdpOk = true;
                    }
                }
                return $mdpOk;
                break;
            case "LST_MAIL":
                $mdpOk = false;
                if ($fichierATraiter = fopen('../' . FIC_MDP_MAIL, "r")) {
                    $mdpFichier = fread($fichierATraiter, filesize('../' . FIC_MDP_MAIL));
                    if ($mdp == $mdpFichier) {
                        $mdpOk = true;
                    }
                }
                return $mdpOk;
        }
    }

}
