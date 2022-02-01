<?php

include_once('../fichiersDeConf/paramsRepertoires.php');

/**
 * Classe WorkerLogManager
 *
 * Cette classe s'occupe de tout ce qui concerne la manipulation et la gestion des logs.
 *
 * @version 1.1
 * @author Pittet David
 * @projet IAG - données lait
 */
class WorkerLogManager {

    /**
     * Inscrit le message fourni dans le log d'historique avec la date du moment. La méthode employée ajoute le contenu
     * en fin de fichier.
     *
     * @param $message d'erreur
     */
    public function logHist($message) {
        // le paramètre '3' est le mode avec lequel on écrit le message dans un fichier
        error_log(date("Y-m-d H:i:s") . " => " . $message . "\r\n", 3, HIST_LOG);
    }

    /**
     * Inscrit le message fourni dans le log d'erreur avec la date du moment. La méthode employée ajoute le contenu
     * en fin de fichier. Un commutateur permet de choisir le type de contenu qui va être inscrit dans le log.
     *
     * @param $message d'erreur
     * @param $avecComplements commutateur pour la date
     */
    public function logErreur($message, $log, $avecComplements) {
        // le paramètre '3' est le mode avec lequel on écrit le message dans un fichier
        if ($avecComplements) {
            error_log(date("Y-m-d H:i:s") . " => " . $message . "\r\n", 3, $log);
        } else {
            error_log($message . "\r\n", 3, $log);
        }
    }

    /**
     * Permet d'ouvrir le fichier de log spécifié et selon le nombre de lignes spécifiées dans le constante
     * TAILLE_LECTURE_LOG. Le fichier est lu à l'envers afin d'avoir les lignes les plus récentes.
     *
     * @param $type de log à ouvrir
     *
     * @return string le contenu du fichier retourné pour affichage dans l'IHM
     */
    public function ouvrirLog($type) {
        switch ($type) {
            case "ERREUR":
                if (file_exists(ERROR_LOG)) {
                    $affichage = '';
                    $fichier = file(ERROR_LOG);
                    for ($i = max(0, count($fichier) - 1); $i >= max(0, count($fichier) - TAILLE_LECTURE_LOG_ERR); $i--) {
                        $affichage .= $fichier[$i];
                    }
                    return $affichage;
                } else {
                    return 'Pas de fichier présent';
                }
                break;
            case "HISTORIQUE":
                if (file_exists(HIST_LOG)) {
                    $affichage = '';
                    $fichier = file(HIST_LOG);
                    for ($i = max(0, count($fichier) - 1); $i >= max(0, count($fichier) - TAILLE_LECTURE_LOG_HIST); $i--) {
                        $affichage .= $fichier[$i];
                    }
                    return $affichage;
                } else {
                    return 'Pas de fichier présent';
                }
        }
    }

    /**
     * Permet de supprimer le fichier du type spécifié.
     *
     * @param $type de fichier à supprimer
     *
     * @return string le statut sur le succès ou non de la suppression
     */
    public function supprimerFichier($type) {
        switch ($type) {
            case "ERREUR":
                if (file_exists(ERROR_LOG)) {
                    if (unlink(ERROR_LOG)) {
                        return 'Suppression effectuée';
                    } else {
                        return 'Erreur lors de la suppression';
                    }
                } else {
                    return 'Pas de fichier présent';
                }
                break;
            case "JSON":
                if (file_exists('../' . FIC_JSON_SPEC)) {
                    if (unlink('../' . FIC_JSON_SPEC)) {
                        return 'Suppression effectuée';
                    } else {
                        return 'Erreur lors de la suppression';
                    }
                } else {
                    return 'Pas de fichier présent';
                }
                break;
            case "LST_MAIL":
                if (file_exists('../' . FIC_LST_MAIL)) {
                    if (unlink('../' . FIC_LST_MAIL)) {
                        return 'Suppression effectuée';
                    } else {
                        return 'Erreur lors de la suppression';
                    }
                } else {
                    return 'Pas de fichier présent';
                }
                break;
        }
    }

    /**
     * Permet de télécharger le fichier du type spécifié. Il est téléchargé dans son intégralité.
     *
     * @param $type de fichier à télécharger
     *
     * @return bool le statut sur le succès ou non du téléchargement
     */
    public function telechargerFichier($type) {
        switch ($type) {
            case "ERREUR":
                if (file_exists(ERROR_LOG)) {
                    // la seule méthode qui peut lire le fichier, peu importe son nombre de lignes, parmi les nombreuses
                    // possibilités offertes par PHP
                    if ($fh = fopen(ERROR_LOG, 'r')) {
                        while (!feof($fh)) {
                            $line = fgets($fh);
                            echo $line;
                        }
                        fclose($fh);
                    }
                } else {
                    return false;
                }
                break;
            case "JSON":
                if (file_exists('../' . FIC_JSON_SPEC)) {
                    // la seule méthode qui peut lire le fichier, peu importe son nombre de lignes, parmi les nombreuses
                    // possibilités offertes par PHP
                    if ($fh = fopen('../' . FIC_JSON_SPEC, 'r')) {
                        while (!feof($fh)) {
                            $line = fgets($fh);
                            echo $line;
                        }
                        fclose($fh);
                    }
                } else {
                    return false;
                }
                break;
            case "LST_MAIL":
                if (file_exists('../' . FIC_LST_MAIL)) {
                    // la seule méthode qui peut lire le fichier, peu importe son nombre de lignes, parmi les nombreuses
                    // possibilités offertes par PHP
                    if ($fh = fopen('../' . FIC_LST_MAIL, 'r')) {
                        while (!feof($fh)) {
                            $line = fgets($fh);
                            echo $line;
                        }
                        fclose($fh);
                    }
                } else {
                    return false;
                }
                break;
        }
    }

    /**
     * Récupère les logs individuels du jour et calcule le nombre d'erreurs de
     * chaque type.
     * 
     * @return tableau contenant le nombre d'erreurs pour chaque type
     */
    public function analyseContenuLogsIndiv() {
        $dateAjd = new DateTime(date("Y-m-d H:i:s"));
        $tabLogsIndividuels = array_diff(scandir(REP_LOGS_INDIV, SCANDIR_SORT_NONE), array('..', '.'));
        $tabLogsDuJour = array();
        $tabLogsDuJourAvecErreurs = array();
        $info = 0;
        $warning = 0;
        $error = 0;

        foreach ($tabLogsIndividuels as $unFichierDeLog) {
            $dateDernModifBrut = filemtime(REP_LOGS_INDIV . '/' . $unFichierDeLog);
            $dateDernModif = new DateTime(date("Y-m-d H:i:s", $dateDernModifBrut));
            $interval = (array) date_diff($dateAjd, $dateDernModif);
            $interval = array_slice($interval, 0, 6);
            $y = $interval['y'];
            $m = $interval['m'];
            $d = $interval['d'];
            $h = $interval['h'];
            $i = $interval['i'];
            $s = $interval['s'];
            if (($y == 0) && ($m == 0) && ($d == 0) && ($h == 0) && ($i <= 15)) {
                array_push($tabLogsDuJour, $unFichierDeLog);
            }
        }

        foreach ($tabLogsDuJour as $unNomDeLog) {
            $tabLignesDuFichier = $this->dresseLaCarteDesErreursDuLog(REP_LOGS_INDIV, $unNomDeLog);
            foreach ($tabLignesDuFichier as $uneLigne) {
                switch ($uneLigne) {
                    case strpos($uneLigne, '[SQL Server]Cannot insert duplicate key row in object') !== false:
                        $warning++;
                        break;
                    case strpos($uneLigne, '----------------------------') !== false:
                        $info++;
                        break;
                    default:
                        $error++;                      
                        if (!in_array($unNomDeLog, $tabLogsDuJourAvecErreurs)) {
                            array_push($tabLogsDuJourAvecErreurs, REP_LOGS_INDIV . '/' . $unNomDeLog);
                        }
                }
            }
        }
        return array($info, $warning, $error, $tabLogsDuJourAvecErreurs);
    }

    /**
     * Ouvre un fichier fourni en paramètres et place chaque ligne dans une cellule d'un tableau.
     * 
     * @param $repImport le répertoire temporaire où a été importé le fichier sur le serveur pour traitement
     * @param $nomFichier le nom du fichier à traiter
     * 
     * @return tableau contenant les enregistrements du fichier
     */
    public function dresseLaCarteDesErreursDuLog($repImport, $nomFichier) {
        // fopen permet d'ouvrir le fichier dont le chemin complet est fourni en paramètre
        // le paramètre 'r' spécifie que le fichier est ouvert en lecture uniquement
        $handle = @fopen($repImport . '/' . $nomFichier, "r");
        $tabContenuFichier = null;
        $numeroEnregistrementndex = 0;
        // tant qu'on a la main sur le fichier, on le lit ligne par ligne
        if ($handle) {
            while (($buffer = fgets($handle)) !== false) {
                // pour chaque ligne, on crée un grand string contenant l'ensemble de l'enregistrement qu'on place dans une des cellules
                $tabContenuFichier[$numeroEnregistrementndex] = $buffer;
                $numeroEnregistrementndex = $numeroEnregistrementndex + 1;
            }
            // on tombe dans ce if si la lecture du fichier a été interrompue avant la fin de celui-ci
            if (!feof($handle)) {
                $this->inscrireLogHist($nomFichier . ' Ce log n\'a pas été parcouru entièrement en vu de l\'envoi du mail de récapitulation.');
            }
            fclose($handle);
        }
        return $tabContenuFichier;
    }

}
