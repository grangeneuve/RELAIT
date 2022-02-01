<?php

include_once('../fichiersDeConf/iTypeOfFile.php');
include_once('../fichiersDeConf/iTypeOfJSONParam.php');
include_once('../fichiersDeConf/iSQLInsertStatus.php');
include_once('../fichiersDeConf/paramsRepertoires.php');

/**
 * Classe ArrayFileCreator
 *
 * Cette classe est le point névralgique de la mécanique d'importation automatique.
 *
 * @version 1.0
 * @author Pittet David
 * @projet IAG - données lait
 */
class ArrayFileCreator
{

    /**
     * Crée la 'carte' des fichiers du répertoire. Crée pour cela deux tableaux, un contenant les noms de fichiers de
     * données lait et l'autre contenant les noms de fichiers de données santé. Ce tableau est alimenté en tenant compte
     * de l'ordre dans lequel les catégories sont présentes dans le JSON.
     *
     * @return array le tableau contenant les deux tableaux de noms de fichiers
     */
    public function dresserCarteDuRepertoire()
    {
        $workerServer = new WorkerServer();
        // cette méthode interne à PHP retourne un tableau contenant les fichiers, au format string, du répertoire
        $arrayFile = array_diff(scandir('..' . REP_IMPORT_AUTO), array('..', '.'));
        $tabTypesDeFichiers = $workerServer -> getLesTypesDeFichiers();
        $arrayFileTrieDL = array();
        $arrayFileTrieDS = array();

        foreach ($tabTypesDeFichiers as $unTypeJSON) {
            // Obligé de tester avec le string puisqu'il s'agit du nom présent dans le JSON
            if (!($unTypeJSON === 'Données Santé')) {
                $arrayFileTrieDL = $this -> alimenterTab($unTypeJSON, $arrayFile, $arrayFileTrieDL);
            } else {
                $unTypeJSON = iTypeOfFile::TYPE_DS;
                $arrayFileTrieDS = $this -> alimenterTab($unTypeJSON, $arrayFile, $arrayFileTrieDS);
            }
        }
        return array($arrayFileTrieDL, $arrayFileTrieDS);
    }

    /**
     * Alimente le tableau de la carte du répertoire en fonction du type fourni par la méthode appelante. Méthode appelée
     * pour chaque type de fichier spécifié dans le JSON.
     *
     * @param $unTypeJSON le type de fichier à traiter
     * @param $arrayFile le tableau brut des fichiers du répertoire
     * @param $arrayFileTrie le tableau final des fichiers du répertoire
     *
     * @return mixed le tableau final avec les nouveaux fichiers triés ajoutés
     */
    public function alimenterTab($unTypeJSON, $arrayFile, $arrayFileTrie)
    {
        $workerServer = new WorkerServer();

        foreach ($arrayFile as $unFichier) {
            $fichierPourImport = '..' . REP_IMPORT_AUTO . $unFichier;
            $typeFichier = $workerServer -> identifierTypeFichier($fichierPourImport);
            if ($typeFichier === $unTypeJSON) {
                // pas de soucis de doublons car le système de fichiers empêche cela
                $arrayFileTrie[$unFichier] = $typeFichier;
            }
        }
        return $arrayFileTrie;
    }

    /**
     * Crée la carte des fichiers restants dans le répertoire. Après traitement automatique des fichiers valides.
     *
     * @return array le tableau des fichiers non-valides restants dans le répertoire
     */
    public function dresserCarteDesFichiersRestants()
    {
        return array_diff(scandir('..' . REP_IMPORT_AUTO), array('..', '.'));
    }

}