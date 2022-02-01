<?php

interface iFileImportStatus
{
    const INSERT_OK = 1;
    const INSERT_OK_AVEC_ERREURS = 2;
    const EXPLOITATION_IMPOSSIBLE = 3;

    const ERREUR_TRAITEMENT = -1;
}

?>