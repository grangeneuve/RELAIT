<?php

require_once('workerServicesDB.php');
require_once('workerFileReader.php');
require_once('workerLogManager.php');
require_once('workerJSONReader.php');
require_once('arrayFileCreator.php');
require_once('workerMailManager.php');

include_once('../fichiersDeConf/iTypeOfFile.php');
include_once('../fichiersDeConf/iTypeOfJSONParam.php');
include_once('../fichiersDeConf/iSQLInsertStatus.php');
include_once('../fichiersDeConf/listeFederations.php');

/**
 * Classe WorkerServer
 *
 * Cette classe est le point central, du côté serveur, de la partie métier. Elle contient notamment toute la logique
 * quant à l'importation des différents fichiers de données.
 *
 * @version 1.1
 * @author Pittet David
 * @projet IAG - données lait
 */
class WorkerServer {

    private $traitementDoublons;
    private $tableauDesExploitationsImportees;

    /**
     * Constructeur de WorkerServeur.
     *
     */
    public function __construct() {
        $this->traitementDoublons = false;
        $this->tableauDesExploitationsImportees = [];
    }

    /**
     * Crée une instance de WorkerServicesDB et transmet les informations entre CtrlServer et la méthode
     * estBDConnectee()
     *
     * @return bool true si la connexion est établie, false sinon
     */
    public function estBDConnectee() {
        $workerServicesDB = new WorkerServicesDB();
        return $workerServicesDB->estBDConnectee();
    }

    /**
     * Crée une instance de WorkerFileReader et transmet les informations entre CtrlServer et la méthode
     * chargerFichierSrv()
     *
     * @param $fichierACharger le chemin complet du fichier
     * @param $nomFichier le nom du fichier dans le tableau POST
     *
     * @return string le message d'état quant à la réussite ou non du chargement
     * 
     */
    public function chargerFichierSrv($fichierACharger, $nomChampForm) {
        $workerFileReader = new WorkerFileReader();
        return $workerFileReader->chargerFichierSrv($fichierACharger, $nomChampForm);
    }

    /**
     * Crée une instance de WorkerFileReader et transmet les informations entre CtrlServer et la méthode
     * renommerFichier()
     * 
     * @param $ancienNom du fichier
     * @param $nouveauNom du fichier
     * 
     * @return le statut sur le renommage
     */
    public function renommerFichier($ancienNom, $nouveauNom) {
        $workerFileReader = new WorkerFileReader();
        return $workerFileReader->renommerFichier($ancienNom, $nouveauNom);
    }

    /**
     * Crée une instance de WorkerFileReader et transmet les informations entre le CtrlServer et la méthode
     * identifierTypeFichier()
     *
     * @param $fichierACharger le chemin complet du fichier
     *
     * @return string le message d'état quant à la réussite ou non de l'identification
     */
    public function identifierTypeFichier($fichierACharger) {
        $workerFileReader = new WorkerFileReader();
        return $workerFileReader->identifierTypeFichier($fichierACharger);
    }

    /**
     * Crée un instance de WorkerFileReader et transmet les informations entre CtrlServer et la méthode
     * deplacerFichier()
     *
     * @param $fichierADeplacer le chemin complet du fichier
     * @param $repertoireDeDestination le nom du répertoire où il faut placer le fichier
     *
     * @return bool true si le fichier a pu être déplacé, false sinon
     */
    public function deplacerFichier($fichierADeplacer, $repertoireDeDestination) {
        $workerFileReader = new WorkerFileReader();
        return $workerFileReader->deplacerFichier($fichierADeplacer, $repertoireDeDestination);
    }

    /**
     * Méthode d'importation des fichiers de données lait. Le fichier à importer est dans un premier temps entièrement
     * parcouru, ceci par une autre méthode, afin de transformer son contenu en tableau, puis les enregistrements ainsi
     * stockés sont traités selon les spécifications dictées par le JSON.
     *
     * @param $repImport le répertoire dans lequel est placé le fichier à importer
     * @param $nomFichier le nom du fichier à importer
     * @param $typeFichier le type de fichier à traiter
     *
     * @return int le message d'état quant à la réussite ou non de l'importation
     */
    public function importerFichierDonneesLait($repImport, $nomFichier, $typeFichier) {
        $retourStatutImport = iFileImportStatus::EXPLOITATION_IMPOSSIBLE;
        // on crée un tableau contenant pour chaque cellule une ligne du fichier
        $tabContenuFichier = $this->creerLeTableauDesEnregistrements($repImport, $nomFichier);

        // partie importation du fichier, en utilisant le tableau créé au-dessus
        if ($tabContenuFichier !== null) {
            $workerJSONReader = new WorkerJSONReader();
            $workerServicesDB = new WorkerServicesDB();
            $tableauDesErreurs = null;
            $numeroEnregistrement = 1;

            // on crée un tableau contenant tous les noms de champ spécifiés dans le JSON
            $tabTousLesNoms = $workerJSONReader->getTousLesNomsDeParams($typeFichier);

            // dans cette boucle se fait le traitement par enregistrement
            foreach ($tabContenuFichier as $unEnregistrement) {
                $tabTableDest = array();
                // vérification avant toute chose que l'enregistrement soit valide
                $nEstPasVide = $this->estUnEnregistrementValide($unEnregistrement);
                if ($nEstPasVide) {
                    $positionTraitementParams = 0;
                    $tabDesParamsACalcul = null;
                    $tabValuesSQL = null;
                    $tableDest = '';
                    $valeurPourInsert = '';
                    $tableauGlobal = null;
                    $tabIntermediaire = null;

                    // on récupère la longueur de l'enregistrement afin de ne pas le dépasser dans le traitement par champ
                    $longeurEnregistrement = strlen($unEnregistrement);
                    // on crée un tableau contenant les types de fichiers présents dans le JSON et dont un ou plusieurs
                    // champs sont calculés
                    $tabDesTypesCalcules = $workerJSONReader->getLesTypesDeFichiersACalculer();

                    // dans cette boucle se fait le traitement par champ spécifié dans le JSON
                    $tailleTabParam = $workerJSONReader->getTailleTabParams($typeFichier);
                    while ($positionTraitementParams <= $tailleTabParam) {
                        // on récupère les valeurs de départ et de longueur du champ qui vont servir à découper l'enregistrement
                        $tabStartLength = $workerJSONReader->getDepartEtLongueur($typeFichier, $positionTraitementParams);
                        $depart = (int) $tabStartLength[0] - 1;
                        $longeur = (int) $tabStartLength[1];

                        // on extrait donc la chaîne concernée de l'enregistrement. on utilise mb_substr (multi-byte) pour n'avoir aucun problème
                        // avec les caractères spéciaux, même en UTF-8.
                        $segmentDeLEnregistrement = mb_substr($unEnregistrement, $depart, $longeur, mb_internal_encoding());

                        // on récupère la ou les tables de destination du champ en cours de traitement
                        $tableDest = $workerJSONReader->getTableDestination($typeFichier, $positionTraitementParams);

                        // si le champ est à calculer, on place le nom du champ dans un tableau spécifique, ce nom sert
                        // de clef alors que sa valeur associée correspond à la ou les opérations (+opérateurs) à effectuer.
                        // on parle ici de la partie 'paramsCalcul' de chaque champ dans le JSON
                        $estUnChampACalc = $workerJSONReader->getSiChampACalculer($typeFichier, $positionTraitementParams);
                        if ((in_array($typeFichier, $tabDesTypesCalcules)) && $estUnChampACalc) {
                            $nomElementJSON = $workerJSONReader->getNomElementJSON($typeFichier, $positionTraitementParams);
                            $tabDesParamsACalcul[$nomElementJSON] = $workerJSONReader->getParamsCalculPourLeParam($typeFichier, $positionTraitementParams);
                        }

                        // si on est dans les limites de l'enregistrement et qu'une valeur est présente, on teste qu'elle
                        // corresponde bien à ce qu'on attend qu'elle soit. sinon, on place la valeur par défaut spécifiée
                        // dans le JSON
                        if (($depart + $longeur) < $longeurEnregistrement) {
                            if (trim($segmentDeLEnregistrement, ' ') == '') {
                                $valeurPourInsert = $workerJSONReader->getValeurParDefaut($typeFichier, $positionTraitementParams);
                            } else {
                                $tabApresVerifCorrespondance = $this->verifierCorrespondanceTypeParam($typeFichier, $positionTraitementParams, $segmentDeLEnregistrement, $numeroEnregistrement, $nomFichier, $tableauDesErreurs);
                                $valeurPourInsert = $tabApresVerifCorrespondance[0];
                                $tableauDesErreurs = $tabApresVerifCorrespondance[1];
                            }
                        } else {
                            $valeurPourInsert = $workerJSONReader->getValeurParDefaut($typeFichier, $positionTraitementParams);
                        }

                        // constitution d'un tableau contenant les différentes tables de destination
                        foreach ($tableDest as $uneTable) {
                            if (!in_array($uneTable, $tabTableDest)) {
                                array_push($tabTableDest, $uneTable);
                            }
                        }

                        // on place finalement la valeur dans la structure finale
                        $tabIntermediaire[$positionTraitementParams] = array('valeur' => $valeurPourInsert, 'tableDest' => $tableDest);

                        $positionTraitementParams = $positionTraitementParams + 1;
                    }

                    // on fusionne les tableaux contenant le nom de tous les champs et toutes les valeurs spécifiées dans
                    // le JSON
                    $tableauGlobal = array_combine(array_values($tabTousLesNoms), $tabIntermediaire);
                    if (in_array($typeFichier, $tabDesTypesCalcules)) {
                        // si c'est un fichier à importer avec calculs
                        $tableauGlobal = $this->traitementDesParametresACalculer($tableauGlobal, $tabDesParamsACalcul, $typeFichier);
                        $tableauDesErreurs = $this->preparerLInsertionBD($tableauGlobal, $tabTableDest, $numeroEnregistrement, $tableauDesErreurs, $nomFichier);
                    } else {
                        // si c'est un fichier à importer sans calculs
                        $tableauDesErreurs = $this->preparerLInsertionBD($tableauGlobal, $tabTableDest, $numeroEnregistrement, $tableauDesErreurs, $nomFichier);
                    }
                } else {
                    // on logue si on est passé par un enregistrement non-valide
                    $this->inscrireLogErreur('Ligne n°' . $numeroEnregistrement . ' => enregistrement ignoré car vide', ERROR_LOG, true);
                    $this->inscrireLogErreur('Ligne n°' . $numeroEnregistrement . ' => enregistrement ignoré car vide', REP_LOGS_INDIV . '/' . date("Ymd") . '_' . $nomFichier, true);
                    $tableauDesErreurs[$i] = iSQLInsertStatus::INSERT_NOK;
                }

                $numeroEnregistrement = $numeroEnregistrement + 1;
            }

            //traitement des doublons dans la table Etat repro
            if ($this->traitementDoublons) {
                $workerServicesDB->suppressionDoublonsEtatRepro();
            }

            // une fois tous les enregistrements passés, on regarde si le string concaténé contient des valeurs de
            // retour signalant des erreurs
            $retourStatutImport = $this->verifSiErreursLorsInsert($tableauDesErreurs);
        }
        return array($retourStatutImport, $this->tableauDesExploitationsImportees);
    }

    /**
     * Ouvre un fichier valide de données lait et place chaque ligne dans une cellule d'un tableau.
     *
     * @param $repImport le répertoire temporaire où a été importé le fichier sur le serveur pour traitement
     * @param $nomFichier le nom du fichier à traiter
     *
     * @return Array le tableau contenant les enregistrements du fichier
     */
    public function creerLeTableauDesEnregistrements($repImport, $nomFichier) {
        // fopen permet d'ouvrir le fichier dont le chemin complet est fourni en paramètre
        // le paramètre 'r' spécifie que le fichier est ouvert en lecture uniquement
        $handle = @fopen('../' . $repImport . '' . $nomFichier, "r");
        $tabContenuFichier = null;
        // tant qu'on a la main sur le fichier, on le lit ligne par ligne
        if ($handle) {
            while (($buffer = fgets($handle)) !== false) {
                if (mb_detect_encoding($buffer, "UTF-8, ISO-8859-1", true) === 'ISO-8859-1') {
                    $buffer = utf8_encode($buffer);
                }
                // pour chaque ligne, on crée un grand string contenant l'ensemble de l'enregistrement qu'on place dans une des cellules
                $tabContenuFichier[] = $buffer;
            }
            // on tombe dans ce if si la lecture du fichier a été interrompue avant la fin de celui-ci
            if (!feof($handle)) {
                $this->inscrireLogHist($nomFichier . ' n\'a pas été parcouru entièrement lors de sa lecture.');
                $this->inscrireLogErreur($buffer . ' est le dernier enregistrement parcouru.', ERROR_LOG, true);
                $this->inscrireLogErreur($buffer . ' est le dernier enregistrement parcouru.', REP_LOGS_INDIV . '/' . date("Ymd") . '_' . basename($nomFichier), true);
            }
            fclose($handle);
        }
        return $tabContenuFichier;
    }

    /**
     * Crée une instance de WorkerLogManager et transmet les informations entre CtrlServer et la méthode logHist()
     *
     * @param $message à loguer
     */
    public function inscrireLogHist($message) {
        $workerLogManager = new WorkerLogManager();
        $workerLogManager->logHist($message);
    }

    /**
     * Crée une instance de WorkerLogManager et transmet les informations entre CtrlServer et la méthode logErreur()
     *
     * @param $message à loguer
     */
    public function inscrireLogErreur($message, $log, $avecComplements) {
        $workerLogManager = new WorkerLogManager();
        $workerLogManager->logErreur($message, $log, $avecComplements);
    }

    /**
     * Vérifie si l'enregistrement courant est valide
     *
     * @param $unEnregistrement l'enregistrement à vérifier
     *
     * @return bool true s'il est valide, false sinon
     */
    public function estUnEnregistrementValide($unEnregistrement) {
        if (!is_array($unEnregistrement)) {
            // cas 'Données lait'
            //les CR / LF sont enlevés de la chaîne, en plus des espaces, afin de tester si elle est vide
            $value = strlen(trim(str_replace("\r\n", '', $unEnregistrement), ' '));
            return $value == 0 ? false : true;
        } else {
            // cas 'Données Santé'
            return !array_filter($unEnregistrement) ? false : true;
        }
    }

    /**
     * Vérifie la correspondance entre le segment d'enregistrement courant et ce que le JSON dicte concernant
     * son type présumé.
     *
     * @param $typeFichier type de fichier de données
     * @param $positionTraitementParams position du traitement dans le JSON
     * @param $segmentDeLEnregistrement chaîne de caractères à traiter
     * @param $numeroEnregistrement position de l'enregistrement
     *
     * @return int|string la valeur formattée, la valeur par défaut sinon
     */
//    public function verifierCorrespondanceTypeParam($typeFichier, $positionTraitementParams, $segmentDeLEnregistrement, $numeroEnregistrement, $nomFichier) {
    public function verifierCorrespondanceTypeParam($typeFichier, $positionTraitementParams, $segmentDeLEnregistrement, $numeroEnregistrement, $nomFichier, $tableauDesErreurs) {
        $workerJSONReader = new WorkerJSONReader();
        $valeurPourInsert = $segmentDeLEnregistrement;
        $segmentDeLEnregistrement = trim($segmentDeLEnregistrement, ' ');
        // on teste si la valeur est ce qu'elle est censé être selon ce que le JSON dicte
        $typeParam = $workerJSONReader->getTypeParam($typeFichier, $positionTraitementParams);
        switch ($typeParam) {
            case iTypeOfJSONParam::TEXTE_COURT:
                if (is_string($segmentDeLEnregistrement)) {
                    $valeurPourInsert = trim($segmentDeLEnregistrement, ' ');
                } else {
                    $valeurPourInsert = $workerJSONReader->getValeurParDefaut($typeFichier, $positionTraitementParams);
                    $tableauDesErreurs[] = iSQLInsertStatus::INSERT_NOK;
                    $this->inscrireLogErreur('Ligne n°' . $numeroEnregistrement . ' => ' . $segmentDeLEnregistrement . ' n\'est pas une valeur valide par rapport au type spécifié dans le JSON(' . iTypeOfJSONParam::TEXTE_COURT . ')', ERROR_LOG, true);
                    $this->inscrireLogErreur('Ligne n°' . $numeroEnregistrement . ' => ' . $segmentDeLEnregistrement . ' n\'est pas une valeur valide par rapport au type spécifié dans le JSON(' . iTypeOfJSONParam::TEXTE_COURT . ')', REP_LOGS_INDIV . '/' . date("Ymd") . '_' . basename($nomFichier), true);
                }
                break;
            case iTypeOfJSONParam::ENTIER_LONG:
                $segmentDeLEnregistrement = (int) $segmentDeLEnregistrement;
                if (is_numeric($segmentDeLEnregistrement) && !is_float($segmentDeLEnregistrement)) {
                    if ($this->verifieSiComprisDansPlageMinMax($typeFichier, $positionTraitementParams, $segmentDeLEnregistrement)) {
                        $valeurPourInsert = $segmentDeLEnregistrement;
                    } else {
                        $valeurPourInsert = $workerJSONReader->getValeurParDefaut($typeFichier, $positionTraitementParams);
                    }
                } else {
                    $valeurPourInsert = $workerJSONReader->getValeurParDefaut($typeFichier, $positionTraitementParams);
                    $tableauDesErreurs[] = iSQLInsertStatus::INSERT_NOK;
                    $this->inscrireLogErreur('Ligne n°' . $numeroEnregistrement . ' => ' . $segmentDeLEnregistrement . ' n\'est pas une valeur valide par rapport au type spécifié dans le JSON(' . iTypeOfJSONParam::ENTIER_LONG . ')', ERROR_LOG, true);
                    $this->inscrireLogErreur('Ligne n°' . $numeroEnregistrement . ' => ' . $segmentDeLEnregistrement . ' n\'est pas une valeur valide par rapport au type spécifié dans le JSON(' . iTypeOfJSONParam::ENTIER_LONG . ')', REP_LOGS_INDIV . '/' . date("Ymd") . '_' . basename($nomFichier), true);
                }
                break;
            case iTypeOfJSONParam::DATE_HEURE:
                try {
                    // on crée une date avec la valeur fournie. si cela part en erreur, c'est que la valeur n'est pas valide
                    new DateTime($segmentDeLEnregistrement);
                    $valeurPourInsert = $segmentDeLEnregistrement;
                } catch (Exception $e) {
                    $valeurPourInsert = $workerJSONReader->getValeurParDefaut($typeFichier, $positionTraitementParams);
                    $tableauDesErreurs[] = iSQLInsertStatus::INSERT_NOK;
                    $this->inscrireLogErreur('Ligne n°' . $numeroEnregistrement . ' => ' . $segmentDeLEnregistrement . ' n\'est pas une valeur valide par rapport au type spécifié dans le JSON(' . iTypeOfJSONParam::DATE_HEURE . ')', ERROR_LOG, true);
                    $this->inscrireLogErreur('Ligne n°' . $numeroEnregistrement . ' => ' . $segmentDeLEnregistrement . ' n\'est pas une valeur valide par rapport au type spécifié dans le JSON(' . iTypeOfJSONParam::DATE_HEURE . ')', REP_LOGS_INDIV . '/' . date("Ymd") . '_' . basename($nomFichier), true);
                }
                break;
            case iTypeOfJSONParam::REEL_DOUBLE:
                $segmentDeLEnregistrement = (float) $segmentDeLEnregistrement;
                if (is_numeric($segmentDeLEnregistrement) && is_float($segmentDeLEnregistrement)) {
                    if ($this->verifieSiComprisDansPlageMinMax($typeFichier, $positionTraitementParams, $segmentDeLEnregistrement)) {
                        $valeurPourInsert = $segmentDeLEnregistrement;
                    } else {
                        $valeurPourInsert = $workerJSONReader->getValeurParDefaut($typeFichier, $positionTraitementParams);
                    }
                } else {
                    $valeurPourInsert = $workerJSONReader->getValeurParDefaut($typeFichier, $positionTraitementParams);
                    $tableauDesErreurs[] = iSQLInsertStatus::INSERT_NOK;
                    $this->inscrireLogErreur('Ligne n°' . $numeroEnregistrement . ' => ' . $segmentDeLEnregistrement . ' n\'est pas une valeur valide par rapport au type spécifié dans le JSON(' . iTypeOfJSONParam::REEL_DOUBLE . ')', ERROR_LOG, true);
                    $this->inscrireLogErreur('Ligne n°' . $numeroEnregistrement . ' => ' . $segmentDeLEnregistrement . ' n\'est pas une valeur valide par rapport au type spécifié dans le JSON(' . iTypeOfJSONParam::REEL_DOUBLE . ')', REP_LOGS_INDIV . '/' . date("Ymd") . '_' . basename($nomFichier), true);
                }
                break;
        }
        return array($valeurPourInsert, $tableauDesErreurs);
    }

    /**
     * Vérifie si la valeur du segment de l'enregistrement se trouve entre les bornes minimum et maximum.
     *
     * @param $typeFichier le type de fichier qui est importé
     * @param $positionTraitementParams la position du paramètre en cours d'importation dans le JSON
     * @param $segmentDeLEnregistrement le segment de l'enregistrement contenant la valeur
     *
     * @return bool false si la valeur n'est pas entre les bornes, true sinon
     */
    public function verifieSiComprisDansPlageMinMax($typeFichier, $positionTraitementParams, $segmentDeLEnregistrement) {
        $workerJSONReader = new WorkerJSONReader();
        // si aucune valeur min-max ne s'applique pour le champ
        $ok = true;
        $tabMinMax = $workerJSONReader->getMinMax($typeFichier, $positionTraitementParams);
        $min = $tabMinMax[0];
        $max = $tabMinMax[1];
        if (is_string($max)) {
            $max = PHP_INT_MAX;
        }
        if (($min != -1) && ($max != -1)) {
            // si des valeurs min-max s'appliquent pour le champ, c'est-à-dire si elles sont différentes de -1
            if (!(($segmentDeLEnregistrement >= $min) && ($segmentDeLEnregistrement <= $max))) {
                //la valeur devrait se situer dans la plage mais cela n'est pas le cas
                $ok = false;
            }
        }

        return $ok;
    }

    /**
     * Pour chaque élément à calculer, effectue l'ensemble des opérations spécifiées par le JSON. Appelle pour cela
     * la méthode qui s'occupe du calcul.
     *
     * @param $tableauFusionne le tableau contenant tous les champs spécifiés par le JSON et les valeurs correspondantes
     * @param $tabDesParamsACalcul le tableau contenant les champs qui demandent à être calculés
     *
     * @return array|false le tableau contenant l'ensemble des champs et des valeurs (calculées et non-calculées)
     */
    public function traitementDesParametresACalculer($tableauGlobal, $tabDesParamsACalcul, $typeFichier) {
        if ($tabDesParamsACalcul != null) {
            $valTemp = null;
            foreach ($tabDesParamsACalcul as $unGroupeDeCalcul) {//chaque ensemble de paramètres de calcul
                foreach ($unGroupeDeCalcul as $unElementDuGroupe) {//groupe d'éléments; operateur, champ 1 ou champ 2
                    $champ1 = $unElementDuGroupe['calculChamp1'];
                    $champ2 = $unElementDuGroupe['calculChamp2'];
                    if (isset($unElementDuGroupe['criteres'])) {
                        $criteres = $unElementDuGroupe['criteres'];
                        $valTemp = $this->operationSurLesValeursACalculer($unElementDuGroupe['calculOperation'], $champ1, $champ2, $criteres, $tableauGlobal, $typeFichier);
                    } else {
                        $valTemp = $this->operationSurLesValeursACalculer($unElementDuGroupe['calculOperation'], $champ1, $champ2, null, $tableauGlobal, $typeFichier);
                    }
                    $tableauGlobal[key($tabDesParamsACalcul)]['valeur'] = $valTemp;
                }
                next($tabDesParamsACalcul);
            }
        }
        return $tableauGlobal;
    }

    /**
     * Effectue les opérations sur la valeur à calculer.
     *
     * @param $typeOperation le type d'opération à effectuer
     * @param $champ1 la première valeur pour traitement
     * @param $champ2 la deuxième valeur pour traitement
     * @param $criteres le tableau de paramètres employé par certains types de calculs
     * @param $tableauGlobal le tableau contenant l'ensemble des valeurs de l'enregistrement qui ne sont pas calculées
     *
     * @return false|float|int|mixed|string|null la valeur calculée pour insert dans la BD
     */
    public function operationSurLesValeursACalculer($typeOperation, $champ1, $champ2, $criteres, $tableauGlobal, $typeFichier) {
        $valRetour = null;
        $workerServicesDB = new WorkerServicesDB();
        switch ($typeOperation) {
            case "LST_EXPL_IMPORTEES":
                $valRetour = $tableauGlobal[$champ2]['valeur'];
                $tab = explode('*', $champ1);
                $expl = $workerServicesDB->recupererValeurSpecifiee($tableauGlobal[$champ2]['valeur'], $tab[0], $tab[1], $criteres);
                $explEtType = $expl . ' (' . $typeFichier . ')';
                if (!in_array($explEtType, $this->tableauDesExploitationsImportees)) {
                    $this->tableauDesExploitationsImportees[] = $explEtType;
                }
                break;
            case "VERIFICATION_EXPL_EXISTANTE":
                $tab = explode('*', $champ1);
                $valRetour = $workerServicesDB->recupererValeurSpecifiee($tableauGlobal[$champ2]['valeur'], $tab[0], $tab[1], $criteres);
                if (!empty($valRetour)) {
                    $valRetour = $tableauGlobal[$champ2]['valeur'];
                } else {

                    $valRetour = 'NULL';
                }
                break;
            case "REMPLACER":
                if (strpos($champ1, '*') != false) {// l'utilisation de l'étoile se fait pour les données santé
                    $tab = explode('*', $champ1);
                    $tableDest = $tab[0];
                    $champAObtenir = $tab[1];
                    $valeurChampFournie = $tableauGlobal[$champAObtenir]['valeur'];
                    $valRetour = $workerServicesDB->recupererValeurSpecifiee($valeurChampFournie, $tableDest, $champ2, $criteres);
                } else {// sinon, les données lait sont traitées dans le 'else'
                    $valRetour = $workerServicesDB->recupererValeurSpecifiee($tableauGlobal[$champ2]['valeur'], $champ1, $champ2, $criteres);
                }
                if ($valRetour == '') {
                    $valRetour = 'NULL';
                }
                break;
            case "NUM_TRAVAIL":
                $tableDest = $champ1;
                $champDest = $champ2;
                $nouveauNumTravail = $tableauGlobal['N° Travail']['valeur'];
                $numNational = $tableauGlobal['N° national']['valeur'];
                $ancienNumTravail = $workerServicesDB->recupererNumTravail($tableDest, $champDest, $numNational);

                if ($ancienNumTravail != '' && $nouveauNumTravail == 'NULL') {
                    // il existe déjà un numéro dans la table précédente mais pas dans l'enregistrement
                    // on le renseigne pour insert dans l'enregistrement courant
                    $valRetour = $nouveauNumTravail;
                } else if ($ancienNumTravail == '' && $nouveauNumTravail != 'NULL') {
                    // il existe un numéro de travail dans l'enregistrement mais pas la table précédente
                    // on met à jour le nouveau numéro partout
                    $valRetour = $nouveauNumTravail;
                    $workerServicesDB->miseAJourNumTravail($tableDest, $champDest, $nouveauNumTravail, $numNational);
                } else if ($ancienNumTravail != '' && $nouveauNumTravail != 'NULL') {
                    // il existe un numéro de travail aux deux endroits
                    // on remplace donc l'ancien par le nouveau
                    $valRetour = $nouveauNumTravail;
                    $workerServicesDB->miseAJourNumTravail($tableDest, $champDest, $nouveauNumTravail, $numNational);
                } else {
                    // sinon, il n'existe de numéro de travail nul part
                    $valRetour = 'NULL';
                }
                $workerServicesDB->miseAJourNumTravail('Y01', 'No_collier', $valRetour, $numNational);
                break;
            case "NUM_TRAVAIL_K10":
                $tableDest = $champ1;
                $champDest = $champ2;
                $numNational = $tableauGlobal['N° national']['valeur'];
                
                $valRetour = $workerServicesDB->recupererNumTravail($tableDest, $champDest, $numNational);
                $valRetour = ($valRetour == 0) ? 'NULL' : $valRetour;
                $workerServicesDB->miseAJourNumTravail('Etat repro', 'N° Travail', $valRetour, $numNational);
                $workerServicesDB->miseAJourNumTravail('Reproduction - IA', 'N° Travail', $valRetour, $numNational);
                break;
            case "SOUSTRACTION":
                // chaque élément de calcul est soit la date de naissance si '!' est présent dans la chaîne,
                // soit le contenu du champ mentionné est repris
                if (strpos($champ1, '!')) {
                    $premierElementCalc = $workerServicesDB->recupererDateNaissance($tableauGlobal['N° national']['valeur'], $champ1);
                } else {
                    $premierElementCalc = $tableauGlobal[$champ1]['valeur'];
                }
                if (strpos($champ2, '!')) {
                    $deuxiemeElementCalc = $workerServicesDB->recupererDateNaissance($tableauGlobal['N° national']['valeur'], $champ2);
                } else {
                    $deuxiemeElementCalc = $tableauGlobal[$champ2]['valeur'];
                }

                try {
                    $premiereDate = new DateTime($premierElementCalc);
                    $deuxiemeDate = new DateTime($deuxiemeElementCalc);

                    // les deux dates sont comparées. 'days' permet de récupérer la différence en jours.
                    $difference = $premiereDate->diff($deuxiemeDate);
                    $valRetour = $difference->days;
                } catch (Exception $e) {
                    $valRetour = 'NULL';
                }
                break;
            case "DIVISION":
                $premierElementCalc = $tableauGlobal[$champ1]['valeur'];

                if (!is_numeric($champ2)) {
                    //on prend la valeur pour le champ voulu
                    $deuxiemeElementCalc = $tableauGlobal[$champ2]['valeur'];
                } else {
                    //on prend directement la valeur spécifiée dans le JSON
                    $deuxiemeElementCalc = $champ2;
                }

                if (is_numeric($premierElementCalc) && $deuxiemeElementCalc != 0) {
                    $valRetour = $premierElementCalc / $deuxiemeElementCalc;
                } else {
                    $valRetour = 'NULL';
                }
                break;
            case "ARRONDI":
                $premierElementCalc = $tableauGlobal[$champ1]['valeur'];
                $factArrondi = (int) $champ2;
                if (is_numeric($premierElementCalc)) {
                    $valRetour = round($premierElementCalc, $factArrondi);
                } else {
                    $valRetour = 'NULL';
                }
                break;
            case "MULTIPLICATION":
                $premierElementCalc = $tableauGlobal[$champ1]['valeur'];

                if ($premierElementCalc === 'NULL') {
                    $premierElementCalc = 0;
                }

                if (!is_numeric($champ2)) {
                    $deuxiemeElementCalc = $tableauGlobal[$champ2]['valeur'];
                } else {
                    $deuxiemeElementCalc = $champ2;
                }

                if ($deuxiemeElementCalc == 0) {
                    $deuxiemeElementCalc = 1;
                }

                $valRetour = $premierElementCalc * $deuxiemeElementCalc;
                if ($valRetour == 0) {
                    $valRetour = 'NULL';
                }
                break;
            case "SOMME":
                $premierElementCalc = $tableauGlobal[$champ1]['valeur'];

                if ($premierElementCalc == 'NULL') {
                    $premierElementCalc = 0;
                }

                if (!is_numeric($champ2)) {
                    $deuxiemeElementCalc = $tableauGlobal[$champ2]['valeur'];
                } else {
                    $deuxiemeElementCalc = $champ2;
                }

                if ($deuxiemeElementCalc == 0) {
                    $deuxiemeElementCalc = 1;
                }

                $valRetour = date('Y-m-d', strtotime($premierElementCalc . ' + ' . $deuxiemeElementCalc . ' days'));
                break;
            case "COMPARAISON":
                foreach ($champ2 as $unePlageDeCalcul) {
                    $de = $unePlageDeCalcul['de'];
                    $a = $unePlageDeCalcul['a'];
                    $valeur = $unePlageDeCalcul['valeur'];
                    $valeurATester = (float) $tableauGlobal[$champ1]['valeur'];
                    if (is_string($a)) {
                        // si la valeur contenue dans le champ vaut 'infini', on la remplace par une valeur calculable
                        $a = PHP_INT_MAX;
                    }
                    if ($valeurATester >= (float) $de && $valeurATester <= (float) $a) {
                        $valRetour = $valeur;
                    }
                }
                break;
            case "VERIFICATION":
                foreach ($champ2 as $unePlageDeCalcul) {
                    $de = $unePlageDeCalcul['de'];
                    $a = $unePlageDeCalcul['a'];
                    $valeur = $unePlageDeCalcul['valeur'];
                    $valeurATester = (float) $tableauGlobal[$champ1]['valeur'];
                    if ($a == 'infini') {
                        $a = PHP_INT_MAX;
                    }
                    $de = (float) $de;
                    $a = (float) $a;

                    if (($valeurATester >= $de) && ($valeurATester <= $a) && ($valeur != '')) {
                        $valRetour = $tableauGlobal[$valeur]['valeur'];
                    }
                    if ($valRetour == '') {
                        $valRetour = 'NULL';
                    }
                }
                break;
            case "VERIFICATION_DOUBLE_CALC":
                foreach ($champ2 as $unePlageDeCalcul) {
                    $de = $unePlageDeCalcul['de'];
                    $a = $unePlageDeCalcul['a'];
                    if (is_string($a)) {
                        $a = PHP_INT_MAX;
                    }

                    $valCalc1 = $unePlageDeCalcul['champ1'];
                    $valCalc2 = $unePlageDeCalcul['champ2'];
                    $operateur = $unePlageDeCalcul['operation'];

                    $valeurATester = (float) $tableauGlobal[$champ1]['valeur'];

                    if (is_string($valCalc1)) {
                        $valCalc1 = $tableauGlobal[$valCalc1]['valeur'];
                    }
                    if (is_string($valCalc2)) {
                        $valCalc2 = $tableauGlobal[$valCalc2]['valeur'];
                    }
                    if (($valeurATester >= $de) && ($valeurATester <= $a) && ($valCalc1 != 'NULL') && ($valCalc2 != 'NULL')) {
                        $calcul = $valCalc1 . $operateur . $valCalc2;
                        eval('$valRetour = (' . $calcul . ');');
                    }
                    if ($valRetour == '') {
                        $valRetour = 'NULL';
                    }
                }
                break;
            case "CONCATENER":
                $premierElementCalc = $tableauGlobal[$champ1]['valeur'];
                $deuxiemeElementCalc = $tableauGlobal[$champ2]['valeur'];
                $concatenateur = $criteres[0];
                if ($premierElementCalc == 'NULL') {
                    $premierElementCalc = "";
                } else if ($deuxiemeElementCalc == 'NULL') {
                    $deuxiemeElementCalc = "";
                }
                $valRetour = $premierElementCalc . $concatenateur . $deuxiemeElementCalc;
                break;
            case "CTRL_VIDE":
                $contenuChamp = $tableauGlobal[$champ1]['valeur'];
                foreach ($champ2 as $unePlageDeCalcul) {
                    $terme = $unePlageDeCalcul['terme'];
                    $valeur = $unePlageDeCalcul['valeur'];

                    if ($contenuChamp == $terme) {
                        if (strpos($valeur, '#') === 0) {
                            $valRetour = preg_replace('~#~', '', $valeur, 1);
                        } else {
                            $valRetour = $tableauGlobal[$valeur]['valeur'];
                        }
                    } else {
                        $valRetour = $contenuChamp;
                    }
                }
                if ($valRetour == '') {
                    $valRetour = 'NULL';
                }
                break;
            case "TRAITEMENT_DOUBLONS":
                $this->traitementDoublons = $champ1;
                $valRetour = $tableauGlobal[$champ2]['valeur'];
                break;
        }
        return $valRetour;
    }

    /**
     * Méthode qui gère les appels d'insertion dans la BD en tenant compte de la/des tables de destination. Reconstruit
     * pour cela un tableau par type de table d'insertion.
     *
     * @param $tableauGlobal le tableau contenant les noms de champs, les valeurs et les tables de destination
     * @param $tabTableDest le tableau contenant la liste des tables d'insertion (valeurs uniques)
     * @param $numeroEnregistrement le numéro d'enregistrement utilisé pour logué si erreur et retrouver la position
     * @param $tableauDesErreurs le tableau contenant les erreurs ou non d'insertion. est retourné ensuite
     *
     * @return Array le tableau des erreurs afin de traiter son contenu dans la méthode appelante
     */
    public function preparerLInsertionBD($tableauGlobal, $tabTableDest, $numeroEnregistrement, $tableauDesErreurs, $nomFichier) {
        $workerServicesDB = new WorkerServicesDB();
        foreach ($tabTableDest as $uneTableDeDestination) {
            // pour chaque table de destination, on regarde si des champs doivent y être insérés

            $tabDonneesPourInsert = null;
            reset($tableauGlobal);
            foreach ($tableauGlobal as $elemTab) { // pour chaque champ, on regarde la/les tables de destination
                foreach ($elemTab['tableDest'] as $uneTable) {
                    if ($uneTable == $uneTableDeDestination) {

                        // si tel est le cas, on place la valeur et le nom du champ dans un tableau pour insert
                        $tabDonneesPourInsert[key($tableauGlobal)] = $elemTab['valeur'];
                    }
                }
                next($tableauGlobal);
            }

            // tableau pour insert qu'on sépare en deux: les champs et leurs valeurs
            $tableauDesErreurs[] = $workerServicesDB->insererEnregistrement(array_keys($tabDonneesPourInsert), array_values($tabDonneesPourInsert), $uneTableDeDestination, $numeroEnregistrement, $nomFichier);
        }
        return $tableauDesErreurs;
    }

    /**
     * Vérifie si le tableau contient des erreurs, ce qui signifierait que des erreurs ont eu lieu lors
     * de l'importaiton dans la BD. Est utilisé pour retourné le bon message du côté de l'IHM.
     *
     * @param $tableauDesErreurs le tableau contenant les valeurs après insert
     *
     * @return int|string la constante de statut d'erreur à l'insertion du fichier
     */
    public function verifSiErreursLorsInsert($tableauDesErreurs) {
        return in_array(iSQLInsertStatus::INSERT_NOK, $tableauDesErreurs) ? iFileImportStatus::INSERT_OK_AVEC_ERREURS : iFileImportStatus::INSERT_OK;
    }

    /**
     * Transmet l'information d'ouverture d'un log en fonction du type au workerLogManager.
     *
     * @param $type chaîne de caractères spécifiant le type de log
     *
     * @return string le contenu du log pour affichage dans l'IHM
     */
    public function ouvrirLog($type) {
        $workerLogManager = new WorkerLogManager();
        return $workerLogManager->ouvrirLog($type);
    }

    /**
     * Méthode d'importation des fichiers de données santé. Le fichier à importer est dans un premier temps entièrement
     * parcouru, ceci par une autre méthode, afin de transformer son contenu en tableau, puis les enregistrements ainsi
     * stockés sont traités selon les spécifications dictées par le JSON.
     *
     * @param $repImport le répertoire dans lequel est placé le fichier à importer
     * @param $nomFichier le nom du fichier à importer
     * @param $typeFichier le type de fichier à traiter
     *
     * @return int le message d'état quant à la réussite ou non de l'importation
     */
    public function importerFichierDonneesSante($repImport, $nomFichier, $typeFichier) {
        $retourStatutImport = iFileImportStatus::EXPLOITATION_IMPOSSIBLE;
        // on crée un tableau contenant pour chaque cellule une ligne du fichier
        $tabContenuFichier = $this->creerLeTableauDesEnregistrementsCSV($repImport, $nomFichier);

        // partie importation du fichier, en utilisant le tableau créé au-dessus
        if ($tabContenuFichier !== null) {
            $workerServicesDB = new WorkerServicesDB();
            $workerJSONReader = new WorkerJSONReader();
            $tableauDesErreurs = null;
            $numeroEnregistrement = 1;

            // on crée un tableau contenant tous les noms de champ spécifiés dans le JSON
            $tabTousLesNoms = $workerJSONReader->getTousLesNomsDeParams($typeFichier);

            // dans cette boucle se fait le traitement, par enregistrement
            foreach ($tabContenuFichier as $unEnregistrement) {
                $numTravail = '';
                $numNational = '';
                $nEstPasVide = $this->estUnEnregistrementValide($unEnregistrement);
                if ($nEstPasVide) {
                    $positionTraitementParams = 0;
                    $tabDesParamsACalcul = null;
                    $tabValuesSQL = null;
                    $tabTableDest = array();

                    $tableDest = '';
                    $valeurPourInsert = '';
                    $tableauGlobal = null;
                    $tabIntermediaire = null;

                    $tailleTabEnregistrement = count($unEnregistrement);
                    $tabDesTypesCalcules = $workerJSONReader->getLesTypesDeFichiersACalculer();

                    while ($positionTraitementParams < count($tabTousLesNoms)) {
                        if ($positionTraitementParams < $tailleTabEnregistrement) {
                            $unChamp = $unEnregistrement[$positionTraitementParams];
                        }
                        $tableDest = $workerJSONReader->getTableDestination($typeFichier, $positionTraitementParams);

                        $estUnChampACalc = $workerJSONReader->getSiChampACalculer($typeFichier, $positionTraitementParams);
                        if ((in_array($typeFichier, $tabDesTypesCalcules)) && $estUnChampACalc) {
                            $nomElementJSON = $workerJSONReader->getNomElementJSON($typeFichier, $positionTraitementParams);
                            $tabDesParamsACalcul[$nomElementJSON] = $workerJSONReader->getParamsCalculPourLeParam($typeFichier, $positionTraitementParams);
                        }

                        if ($positionTraitementParams < $tailleTabEnregistrement) {
                            if ($unChamp == '') {
                                $valeurPourInsert = $workerJSONReader->getValeurParDefaut($typeFichier, $positionTraitementParams);
                            } else {
                                $tabApresVerifCorrespondance = $this->verifierCorrespondanceTypeParam($typeFichier, $positionTraitementParams, $unChamp, $numeroEnregistrement, $nomFichier, $tableauDesErreurs);
                                $valeurPourInsert = $tabApresVerifCorrespondance[0];
                                $tableauDesErreurs = $tabApresVerifCorrespondance[1];
                            }
                        } else {
                            $valeurPourInsert = $workerJSONReader->getValeurParDefaut($typeFichier, $positionTraitementParams);
                        }

                        // constitution d'un tableau contenant les différentes tables de destination
                        foreach ($tableDest as $uneTable) {
                            if (!in_array($uneTable, $tabTableDest)) {
                                array_push($tabTableDest, $uneTable);
                            }
                        }

                        $tabIntermediaire[$positionTraitementParams] = array('valeur' => $valeurPourInsert, 'tableDest' => $tableDest);
                        $positionTraitementParams++;
                    }
                    $tableauGlobal = array_combine(array_values($tabTousLesNoms), $tabIntermediaire);

                    if (in_array($typeFichier, $tabDesTypesCalcules)) {
                        // si c'est un fichier à importer avec calculs
                        $tableauGlobal = $this->traitementDesParametresACalculer($tableauGlobal, $tabDesParamsACalcul, $typeFichier);
                        $tableauDesErreurs = $this->preparerLInsertionBD($tableauGlobal, $tabTableDest, $numeroEnregistrement, $tableauDesErreurs, $nomFichier);
                    } else {
                        // si c'est un fichier à importer sans calculs
                        $tableauDesErreurs = $this->preparerLInsertionBD($tableauGlobal, $tabTableDest, $numeroEnregistrement, $tableauDesErreurs, $nomFichier);
                    }
                } else {
                    //on logue si on est passé par un enregistrement vide
                    $this->inscrireLogErreur('Ligne n°' . $numeroEnregistrement . ' => enregistrement ignoré car vide', ERROR_LOG, true);
                    $this->inscrireLogErreur('Ligne n°' . $numeroEnregistrement . ' => enregistrement ignoré car vide', REP_LOGS_INDIV . '/' . date("Ymd") . '_' . $nomFichier, true);
                    $tableauDesErreurs[] = iSQLInsertStatus::INSERT_NOK;
                }

                $numeroEnregistrement = $numeroEnregistrement + 1;
            }
            // une fois tous les enregistrements passés, on regarde si le string concaténé contient des valeurs signalant des erreurs
            $retourStatutImport = $this->verifSiErreursLorsInsert($tableauDesErreurs);
        }
        return $retourStatutImport;
    }

    /**
     * Ouvre un fichier valide de données santé et place chaque ligne dans une cellule d'un tableau.
     *
     * @param $repImport le répertoire temporaire où a été importé le fichier sur le serveur pour traitement
     * @param $nomFichier le nom du fichier à traiter
     *
     * @return Array le tableau contenant les enregistrements du fichier
     */
    public function creerLeTableauDesEnregistrementsCSV($repImport, $nomFichier) {
        // fopen permet d'ouvrir le fichier dont le chemin complet est fourni en paramètre
        // le paramètre 'r' spécifie que le fichier est ouvert en lecture uniquement
        $handle = @fopen('../' . $repImport . '' . $nomFichier, "r");
        $tabContenuFichier = null;
        // tant qu'on a la main sur le fichier, on le lit ligne par ligne
        if ($handle) {
            while (($buffer = fgetcsv($handle, 0, ';', '\\')) !== false) {
                $bufferConverti = null;
                //on regroupe en une chaîne de caractère le contenu de buffer. buffer est un tableau des champs de la ligne.
                $enregistrement = implode('', $buffer);

                if (mb_detect_encoding($enregistrement, "UTF-8, ISO-8859-1", true) === 'ISO-8859-1') {
                    //$buffer = utf8_encode($buffer);
                    foreach ($buffer as $cell) {
                        $bufferConverti[] = utf8_encode($cell);
                    }
                } else {
                    $bufferConverti = $buffer;
                }

                // pour chaque ligne, on récupère la ligne du CSV découpée et convertie en tableau pour chaque enregistrement
                $tabContenuFichier[] = $bufferConverti;
            }
            // on tombe dans ce if si la lecture du fichier a été interrompue avant la fin de celui-ci
            if (!feof($handle)) {
                $this->inscrireLogHist($nomFichier . ' n\'a pas été parcouru entièrement lors de sa lecture.');
                $this->inscrireLogErreur($buffer . ' est le dernier enregistrement parcouru.', ERROR_LOG, true);
                $this->inscrireLogErreur($buffer . ' est le dernier enregistrement parcouru.', REP_LOGS_INDIV . '/' . date("Ymd") . '_' . basename($nomFichier), true);
            }
            fclose($handle);
        }
        return $tabContenuFichier;
    }

    /**
     * Lit la constante contenant la liste des fédérations et la retourne pour affichage dans l'IHM.
     *
     * @return array la liste des fédérations
     */
    public function recupererLaListeDesFederations() {
        return array_keys(LST_FEDERATIONS);
    }

    /**
     * Transmet l'information de récupération des dates d'importation de données santé au workerServicesDB().
     *
     * @return Array|null le tableau des dates d'importation
     */
    public function recupererDatesImportationDonneesSante() {
        $workerServicesDB = new WorkerServicesDB();
        return $workerServicesDB->recupererDatesImportationDonneesSante();
    }

    /**
     * Transmet l'information de suppression des données santé en fonction de la fédération et de la date d'import.
     *
     * @param $federation la fédération concernée
     * @param $dateImport la date d'importation à parti de quand supprimer
     *
     * @return string le statut après suppression pour affichage dans l'IHM.
     */
    public function supprimerDonneesSante($federation, $dateImport) {
        $workerServicesDB = new WorkerServicesDB();
        return $workerServicesDB->supprimerDonneesSante($federation, $dateImport);
    }

    /**
     * Transmet l'information pour dresser la carte des fichiers valides du répertoire pour import automatique.
     *
     * @return Array le tableau des fichiers valides du dossier
     */
    public function dresserCarteDuRepertoire() {
        $arrayFileCreator = new ArrayFileCreator();
        return $arrayFileCreator->dresserCarteDuRepertoire();
    }

    /**
     * Transmet l'information pour dresser la carte des fichiers restants après import des fichiers valides.
     *
     * @return Array le tableau des fichiers non-valides restants dans le dossier
     */
    public function dresserCarteDesFichiersRestants() {
        $arrayFileCreator = new ArrayFileCreator();
        return $arrayFileCreator->dresserCarteDesFichiersRestants();
    }

    /**
     * Transmet l'information de tous les types de fichiers présents dans le JSON. Par exemple Y01, K03, etc.
     *
     * @return Array le tableau des types de fichiers
     */
    public function getLesTypesDeFichiers() {
        $workerJSONReader = new WorkerJSONReader();
        return $workerJSONReader->getLesTypesDeFichiers();
    }

    /**
     * Transmet l'information de suppression d'un fichier des fichiers de fonctionnement.
     *
     * @param $type chaîne de caractères spécifiant le type de fichier
     *
     * @return string le statut sur la suppression pour affichage dans l'IHM
     */
    public function supprimerFichier($type) {
        $workerLogManager = new WorkerLogManager();
        return $workerLogManager->supprimerFichier($type);
    }

    /**
     * Transmet l'information de téléchargement d'un fichier en fonction du type au workerLogManager.
     *
     * @param $type chaîne de caractères spécifiant le type de fichier
     *
     * @return string le contenu du fichier qui va être téléchargé dans la partie cliente
     */
    public function telechargerFichier($type) {
        $workerLogManager = new WorkerLogManager();
        return $workerLogManager->telechargerFichier($type);
    }

    /**
     * Transmet l'information de la présence ou non du fichier JSON.
     *
     * @return bool true s'il est présent, false sinon
     */
    public function estJSONPresent() {
        $workerJSONReader = new WorkerJSONReader();
        return $workerJSONReader->estJSONPresent();
    }

    /**
     * Transmet l'information de la présence ou non du fichier JSON.
     *
     * @return bool true s'il est présent, false sinon
     */
    public function verificationMdp($mdp, $type) {
        $workerFileReader = new WorkerFileReader();
        return $workerFileReader->verificationMdp($mdp, $type);
    }

    /**
     * Transmet l'information de l'envoi d'un mail suite à une importation automatique.
     * 
     * @param $corpsMessage le contenu du mail
     */
    public function envoiMailImportAuto($corpsMessage, $logsAvecErreurs) {
        $workerMailManager = new workerMailManager();
        return $workerMailManager->envoiMailImportAuto($corpsMessage, $logsAvecErreurs);
    }

    /**
     * Transmet l'information d'analyse du contenu des logs par fichiers.
     * 
     * @return tableau contenant le nombre d'erreurs pour chaque type
     */
    public function analyseContenuLogsIndiv() {
        $workerLogManager = new WorkerLogManager();
        return $workerLogManager->analyseContenuLogsIndiv();
    }

}
