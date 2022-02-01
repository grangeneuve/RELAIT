<?php

include_once('../fichiersDeConf/paramsConnBD.php');
include_once('../fichiersDeConf/listeFederations.php');
include_once('../fichiersDeConf/iSQLInsertStatus.php');

/**
 * Classe WorkerServicesDB
 *
 * Cette classe s'occupe de toutes les interactions avec la base de données.
 *
 * @version 1.1
 * @author Pittet David
 * @projet IAG - données lait
 */
class WorkerServicesDB {

    private $connexion;

    /**
     * Constructeur de WorkerServicesDB. Initie la connexion à la base de données.
     *
     */
    public function __construct() {
        try {
            $dsn = 'sqlsrv:Server=' . BD_HOTE . ';Database=' . BD_NOM;
            // on déclare que la connexion se fait en UTF8 afin d'éviter les problèmes d'accents avec les tables
            $this->connexion = new PDO($dsn, '', '', array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            // set the PDO error mode to exception
            $this->connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            date_default_timezone_set('Europe/Berlin');
        } catch (PDOException $e) {
            $this->connexion = null;
            echo 'Impossible d\'établir un lien vers la BD.';
        }
    }

    /**
     * Vérifie si la connexion à la BD est établie.
     *
     * @return bool true si la BD est connectée, false sinon
     *
     */
    public function estBDConnectee() {
        return $this->connexion !== null ? true : false;
    }

    /**
     * Insère l'enregistrement dans la base de données. Utilise pour cela le nom des champs et les valeurs pour chaque
     * champ fourni en paramètre afin de constituer la requête.
     *
     * @param $tabValuesSQL tableau contenant toutes les valeurs provenant du fichier à importer
     * @param $tabTousLesNoms tableau de tous les noms de champs, correspondant aussi au nom des champs de la table SQL
     * @param $tableDInsert une chaîne de caractères spécifiant le type de fichier
     * @param $numeroEnregistrement la position de l'enregistrement dans le fichier en cours d'importation
     *
     * @return string 'succes' si la requête a pu être effectuée, 'erreur' sinon
     */
    public function insererEnregistrement($tabTousLesNoms, $tabValuesSQL, $tableDInsert, $numeroEnregistrement, $nomFichier) {
        $workerServer = new WorkerServer();
        $statutInsert = iSQLInsertStatus::INSERT_OK;
        // on échape tous les apostrophes contenues dans les différents champs
        $tabValuesSQL = str_replace("'", "''", $tabValuesSQL);
        try {
            $requeteSQL = "INSERT INTO [" . $tableDInsert . "] ([" . implode('], [', $tabTousLesNoms) . "], [" . 'DateImport' . "]) " . "VALUES ('" . implode("', '", $tabValuesSQL) . "', '" . date("Y-m-d H:i:s") . "')";
            // on modifie pour que les NULLs ne soient pas interprétés comme des strings
            $requeteSQL = str_replace("'NULL'", "NULL", $requeteSQL);
            // use exec() because no results are returned
            $this->connexion->exec($requeteSQL);
        } catch (PDOException $e) {
            $workerServer->inscrireLogErreur('Ligne n°' . $numeroEnregistrement . ' => ' . $e->getMessage(), ERROR_LOG, true);
            $workerServer->inscrireLogErreur('Ligne n°' . $numeroEnregistrement . ' => ' . $e->getMessage(), REP_LOGS_INDIV . '/' . date("Ymd") . '_' . basename($nomFichier), true);
            $statutInsert = iSQLInsertStatus::INSERT_NOK;
        }
        $conn = null;
        return $statutInsert;
    }

    /**
     * Récupère le nom textuel de l'exploitation en fonction du numéro d'exploitation.
     *
     * @param $numExpl le numéro de l'exploitation à obtenir le nom
     * @param $champCible le nom du champ dans lequel se trouve l'information
     * @param $tableCible le nom de la table où aller chercher l'information
     *
     * @return mixed|string le nom textuel de l'exploitation, vide sinon
     */
    public function recupererValeurSpecifiee($valeurATester, $tableCible, $champCible, $criteres) {
        $workerServer = new WorkerServer();
        $valeurRecherchee = '';
        $clauseWhere = '';
        $i = 0;
        foreach ($criteres as $unCritere) {
            if ($i === 0) {
                $clauseWhere = "WHERE ([$unCritere] LIKE '$valeurATester')";
            } else {
                $clauseWhere = $clauseWhere . " OR ([$unCritere] LIKE '$valeurATester')";
            }
            $i = $i + 1;
        }
        try {
            $stmt = $this->connexion->query("SELECT [$champCible] FROM [$tableCible] $clauseWhere;");
            $valeurRecherchee = $stmt->fetch();
            $valeurRecherchee = $valeurRecherchee[$champCible];
        } catch (PDOException $e) {
            
        }
        $conn = null;
        return $valeurRecherchee;
    }

    /**
     * Récupère la date de naissance d'une vache selon son numéro national.
     *
     * @param $nNational le numéro national de la vache
     * @param $contenuCible le tableau contenant les informations où aller chercher le renseignements
     *
     * @return int|mixed la date de naissance de la vache, 0 si une exception se produit
     */
    public function recupererDateNaissance($nNational, $contenuCible) {
        $workerServer = new WorkerServer();
        $dateNaissance = 0;
        $tabValeursCible = explode('!', $contenuCible);
        $tableCible = $tabValeursCible[0];
        $champCible = $tabValeursCible[1];
        try {
            $stmt = $this->connexion->query("SELECT [$champCible] FROM [$tableCible] WHERE [N° national]='$nNational';");
            $dateNaissance = $stmt->fetch();
            $dateNaissance = $dateNaissance[$champCible];
        } catch (PDOException $e) {
            
        }
        $conn = null;
        return $dateNaissance;
    }

    /**
     * Met à jour le numéro de travail en fonction du numéro national.
     *
     * @param $tableCible la table dans laquelle mettre à jour le numéro de travail
     * @param $numTravail le nouveau numéro de travail
     * @param $numNational le numéro national de la vache.
     *
     * @return int la statut sur le succès ou non de la mise à jour
     */
    public function miseAJourNumTravail($tableCible, $champCible, $numTravail, $numNational) {
        $workerServer = new WorkerServer();
        $statutInsert = iSQLInsertStatus::INSERT_OK;
        try {
            $requeteSQL = "UPDATE [$tableCible] set [$champCible]= '$numTravail' WHERE [N° national] = '$numNational';";
            // use exec() because no results are returned
            $this->connexion->exec($requeteSQL);
        } catch (PDOException $e) {
            $statutInsert = iSQLInsertStatus::INSERT_NOK;
        }
        $conn = null;
        return $statutInsert;
    }

    /**
     * Récupère le numéro de travail en fonction du numéro national.
     *
     * @param $tableCible la table dans laquelle aller chercher l'information
     * @param $numNational la numéro national de la vache
     *
     * @return int|mixed le numéro national, 0 sinon
     */
    public function recupererNumTravail($tableCible, $champCible, $numNational) {
        $workerServer = new WorkerServer();
        $numTravail = 0;
        try {
            $stmt = $this->connexion->query("SELECT [$champCible] from [$tableCible] where [N° national] = '$numNational';");
            $numTravail = $stmt->fetch();
            $numTravail = $numTravail[$champCible];
        } catch (PDOException $e) {
            
        }
        $conn = null;
        return $numTravail;
    }

    /**
     * Récupères toutes les dates d'importations différentes de la table 'Données santé'. Cette liste est utilisée
     * pour être affichée dans l'IHM.
     *
     * @return array|null la liste des dates d'importation
     */
    public function recupererDatesImportationDonneesSante() {
        $lstDatesImportation = null;
        $stmt = $this->connexion->query("SELECT DISTINCT
                                            CONVERT(date,DateImport,102)
                                        FROM
                                            [données santé]
                                        ORDER BY
                                            CONVERT(date,DateImport,102)
                                        DESC;"
        );
        $result = $stmt->fetchAll();
        if ($result != null) {
            $lstDatesImportation = array();
            $i = 0;
            foreach ($result as $uneDateDImportation) {
                $lstDatesImportation[$i] = $uneDateDImportation[0];
                $i++;
            }
        }
        return $lstDatesImportation;
    }

    /**
     * Supprime les enregistrement de la table 'Données santé' selon la fédération et la date d'importation fournis.
     * Tous les enregistrements avec une date égale ou antérieure à celle fournie sont supprimés.
     *
     * @param $federation la fédération à supprimer
     * @param $dateImport la date d'import à partir de quand supprimer
     *
     * @return string le statut de la suppression pour affichage dans l'IHM.
     */
    public function supprimerDonneesSante($federation, $dateImport) {
        $statutRetour = 'Suppression impossible';
        if ($federation !== '' && $dateImport !== '') {
            $numDeDepart = LST_FEDERATIONS[$federation];
            try {
                $requeteSQL = "DELETE FROM [Données santé] WHERE DATEDIFF(day, [DateImport],'$dateImport') >= 0 AND [N° exploitation] LIKE '$numDeDepart%';";
                // use exec() because no results are returned
                $ret = $this->connexion->exec($requeteSQL);
                if ($ret > 0) {
                    $statutRetour = 'Suppression effectuée avec succès !';
                } else {
                    $statutRetour = 'Aucun enregistrement trouvé avec cette combinaison';
                }
            } catch (PDOException $e) { }
        }
        return $statutRetour;
    }

    /**
     * Supprime les enregistrement de la table 'Etat repro' qui ne sont pas les dernières
     * inséminations de chaque rang de vêlage.
     *
     */
    public function suppressionDoublonsEtatRepro() {
        try {
            $requeteSQL = "DELETE FROM [Etat repro]
                            WHERE CONCAT([N° national], [N° IA/SN], RGV)=ANY(
                                SELECT
                                    CONCAT([N° national], [N° IA/SN], RGV)
                                FROM
                                    [Etat repro] AS D
                                WHERE
                                    [N° IA/SN] < (SELECT MAX([N° IA/SN]) FROM [Etat repro] AS T 
                                                            WHERE T.[N° national]=D.[N° national] 
                                                            AND T.RGV=D.RGV));";
            // use exec() because no results are returned
            $this->connexion->exec($requeteSQL);
        } catch (PDOException $e) { }
    }

}
