<?php

interface iFileUploadStatus
{
    const FICHIER_DEJA_PRESENT = 1;
    const FICHIER_CHARGE = 2;
    const FICHIER_NON_CHARGE = 3;

    const ERREUR_CHARGEMENT_FICHIER = -1;

}

?>