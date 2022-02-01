<?php

include_once('../fichiersDeConf/paramsRepertoires.php');

/**
 * Classe workerJSONReader
 *
 * Cette classe s'occupe de tout ce qui concerne la lecture et la manipulation du fichier de paramètres JSON.
 *
 * @version 1.1
 * @author Pittet David
 * @projet IAG - données lait
 */
class workerJSONReader {

    private $tabDonneesJSON = null;

    /**
     * workerJSONReader constructor.
     */
    public function __construct() {
        if (file_exists('../' . FIC_JSON_SPEC)) {
            $contenuJSON = file_get_contents('../' . FIC_JSON_SPEC);
            $this->tabDonneesJSON = json_decode($contenuJSON, true);
        }
    }

    /**
     * Vérifie si le fichier de paramètres JSON est présent.
     *
     * @return bool true s'il est présent, false sinon.
     */
    public function estJSONPresent() {
        $estPresent = false;
        if (file_exists('../' . FIC_JSON_SPEC)) {
            $contenuJSON = file_get_contents('../' . FIC_JSON_SPEC);
            $this->tabDonneesJSON = json_decode($contenuJSON, true);
            $estPresent = true;
        }
        return $estPresent;
    }

    /**
     * Récupère les valeurs de 'départ' et 'longueur' dans le fichier de paramètres JSON pour le champ spécifié.
     *
     * @param $typeFichier une chaîne de caractères spécifiant le type de fichier
     * @param $positionTraitement la position du champ en cours
     *
     * @return string une chaîne de carctères concaténant le départ et la longueur
     */
    public function getDepartEtLongueur($typeFichier, $positionTraitement) {
        $tabValeurs = null;
        $tabValeurs[0] = $this->tabDonneesJSON[$typeFichier][$positionTraitement]['start'];
        $tabValeurs[1] = $this->tabDonneesJSON[$typeFichier][$positionTraitement]['length'];
        return $tabValeurs;
    }

    /**
     * Récupère le tableau de tables de destination dans le fichier de paramètres JSON pour le champ spécifié.
     *
     * @param $typeFichier une chaîne de caractères spécifiant le type de fichier
     * @param $positionTraitement la position du champ en cours
     *
     * @return Array le tableau de tables de destination
     */
    public function getTableDestination($typeFichier, $positionTraitement) {
        return $this->tabDonneesJSON[$typeFichier][$positionTraitement]['tableDest'];
    }

    /**
     * Récupère les valeurs minimum et maximum dans le fichier de paramètres JSON pour le champ spécifié.
     *
     * @param $typeFichier une chaîne de caractères spécifiant le type de fichier
     * @param $positionTraitement la position du champ en cours
     *
     * @return null
     */
    public function getMinMax($typeFichier, $positionTraitement) {
        $tabValeurs = null;
        $tabValeurs[0] = $this->tabDonneesJSON[$typeFichier][$positionTraitement]['min'];
        $tabValeurs[1] = $this->tabDonneesJSON[$typeFichier][$positionTraitement]['max'];
        return $tabValeurs;
    }

    /**
     * Récupère la valeur par défaut dans le fichier de paramètres JSON pour le champ spécifié par la position.
     *
     * @param $typeFichier une chaîne de caractères spécifiant le type de fichier
     * @param $positionTraitement la position du champ en cours
     *
     * @return string la valeur par défaut pour le champ spécifié
     */
    public function getValeurParDefaut($typeFichier, $positionTraitement) {
        $valDefaut = '';
        if ($positionTraitement <= $this->getTailleTabParams($typeFichier)) {
            if ($this->tabDonneesJSON !== null) {
                $valDefaut = $this->tabDonneesJSON[$typeFichier][$positionTraitement]['default'];
            }
        }
        return $valDefaut;
    }

    /**
     * Récupère la taille du tableau dans le JSON pour le type de fichier spécifié.
     *
     * @param $typeFichier une chaîne de caractères spécifiant le type de fichier
     *
     * @return int le nombre de paramètres dans le JSON
     */
    public function getTailleTabParams($typeFichier) {
        return sizeof($this->tabDonneesJSON[$typeFichier]) - 1;
    }

    /**
     * Récupère le type de paramètre pour le champ concerné.
     *
     * @param $typeFichier en cours de traitement
     * @param $positionTraitement dans l'enregistrement
     *
     * @return mixed l'information du type de paramètre
     */
    public function getTypeParam($typeFichier, $positionTraitement) {
        return $this->tabDonneesJSON[$typeFichier][$positionTraitement]['type'];
    }

    /**
     * Récupère si le champ est à calculer ou non dans le fichier de paramètres JSON pour le champ spécifié.
     *
     * @param $typeFichier une chaîne de caractères spécifiant le type de fichier
     * @param $positionTraitement la position du champ en cours
     *
     * @return bool true s'il est calculé, false sinon
     */
    public function getSiChampACalculer($typeFichier, $positionTraitement) {
        return $this->tabDonneesJSON[$typeFichier][$positionTraitement]['estCalcule'];
    }

    /**
     * Récupère le nom du champ selon sa position dans la catégorie dans le fichier de paramètres JSON.
     *
     * @param $typeFichier une chaîne de caractères spécifiant le type de fichier
     * @param $positionTraitement la position du champ en cours
     *
     * @return string le nom du champ
     */
    public function getNomElementJSON($typeFichier, $positionTraitement) {
        return $this->tabDonneesJSON[$typeFichier][$positionTraitement]['specParam'];
    }

    /**
     * Récupère le tableau des paramètres de calcul dans le fichier de paramètres JSON pour le champ spécifié.
     *
     * @param $typeFichier une chaîne de caractères spécifiant le type de fichier
     * @param $positionTraitement la position du champ en cours
     *
     * @return array le tableau des paramètres de calcul
     */
    public function getParamsCalculPourLeParam($typeFichier, $positionTraitement) {
        return $this->tabDonneesJSON[$typeFichier][$positionTraitement]['paramsCalcul'];
    }

    /**
     * Récupère tous les noms de champs dans le JSON afin de constituer la requête SQL.
     *
     * @param $typeFichier une chaîne de caractères spécifiant le type de fichier
     *
     * @return object le tableau contenant tous les noms de champs
     */
    public function getTousLesNomsDeParams($typeFichier) {
        $tabTousLesNoms = null;
        $positionTraitement = 0;
        while ($positionTraitement <= $this->getTailleTabParams($typeFichier)) {
            $tabTousLesNoms[$positionTraitement] = $this->tabDonneesJSON[$typeFichier][$positionTraitement]['specParam'];
            $positionTraitement = $positionTraitement + 1;
        }
        return $tabTousLesNoms;
    }

    /**
     * Récupère tous les types de fichiers dont au moins un champ est à calculer.
     *
     * @return array le tableau des types de fichiers calculés
     */
    public function getLesTypesDeFichiersACalculer() {
        $tabDesTypesCalcules = array();
        $index = 0;
        foreach ($this->tabDonneesJSON as $typeFichier => $contenuCategorie) {
            $nomTypeFichier = $typeFichier;
            foreach ($contenuCategorie as $unChamp) {
                if (($unChamp['estCalcule'] === true) && (!in_array($nomTypeFichier, $tabDesTypesCalcules))) {
                    $tabDesTypesCalcules[$index] = $nomTypeFichier;
                    $index++;
                }
            }
        }
        return $tabDesTypesCalcules;
    }

    /**
     * Récupère toutes les grandes catégories de types de fichiers. Par exemple Y01, K03, etc.
     *
     * @return array le tableau des types de fichiers
     */
    public function getLesTypesDeFichiers() {
        $tabDesTypes = array();
        foreach ($this->tabDonneesJSON as $typeFichier => $contenuCategorie) {
            if (!in_array($typeFichier, $tabDesTypes)) {
                array_push($tabDesTypes, $typeFichier);
            }
        }
        return $tabDesTypes;
    }

}
