wrk = function () {

    const url = "http://10.16.15.0/relait/php/main.php"; //à modifier en fonction

    // appel de la méthode d'importation manuelle avec une requête de type 'POST'le formulaire contenu dans data est transmis, via AJAX, à la partie serveur
    var _importManuel = function (data, succesCallback, errorCallback) {
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            contentType: false,
            processData: false,
            success: succesCallback,
            error: errorCallback
        });
    }

    // appel de la méthode d'importation automatique avec une requête type 'GET'
    var _importAuto = function (data, succesCallback, errorCallback) {
        $.ajax({
            type: "GET",
            url: "php/main.php?function=impAuto",
            data: data,
            contentType: false,
            processData: false,
            success: succesCallback,
            error: errorCallback
        });
    }

    // appel de la méthode de consultation du log d'erreur avec une requête de type 'POST'
    var _consulterLogErr = function (data, succesCallback, errorCallback) {
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            contentType: false,
            processData: false,
            success: succesCallback,
            error: errorCallback
        });
    }

    // appel de la méthode de consultation du log d'historique avec une requête de type 'POST'
    var _consulterLogHist = function (data, succesCallback, errorCallback) {
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            contentType: false,
            processData: false,
            success: succesCallback,
            error: errorCallback
        });
    }

    // appel de la méthode de suppression des données santé avec une requête de type 'POST'
    var _supprimerDonneesSante = function (data, succesCallback, errorCallback) {
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            contentType: false,
            processData: false,
            success: succesCallback,
            error: errorCallback
        });
    }

    // appel de la méthode de téléchargement d'un log avec une requête de type 'POST'
    var _telechargerLog = function (data, succesCallback, errorCallback) {
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            contentType: false,
            processData: false,
            success: succesCallback,
            error: errorCallback
        });
    }

    // appel de la méthode de téléchargement du JSON avec une requête de type 'POST'
    var _telechargerJson = function (data, succesCallback, errorCallback) {
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            contentType: false,
            processData: false,
            success: succesCallback,
            error: errorCallback
        });
    }

    // appel de la méthode de suppresion d'un log avec une requête de type 'POST'
    var _supprimerLog = function (data, succesCallback, errorCallback) {
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            contentType: false,
            processData: false,
            success: succesCallback,
            error: errorCallback
        });
    }

    // appel de la méthode d'importation du JSON avec une requête de type 'POST'
    var _importerJson = function (data, succesCallback, errorCallback) {
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            contentType: false,
            processData: false,
            success: succesCallback,
            error: errorCallback
        });
    }

    // appel de la méthode de vérification du mot de passe pour le JSON avec une requête type 'GET'
    var _verifierMdpJSON = function (mdp) {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.open("GET", 'php/main.php?function=verifierMdpJSON&mdpJ=' + mdp, false);
        xmlhttp.send();
        return xmlhttp.response;
    }

    // appel de la méthode de vérification du mot de passe pour la liste d'emails avec une requête type 'GET'
    var _verifierMdpMAIL = function (mdp) {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.open("GET", 'php/main.php?function=verifierMdpMAIL&mdpM=' + mdp, false);
        xmlhttp.send();
        return xmlhttp.response;
    }

    // appel de la méthode d'alimentation des listes déroulante (Données santé) avec une requête type 'GET'
    var _alimenterLstDonneesSante = function () {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.open("GET", "php/main.php?function=lstSante", false);
        xmlhttp.send();
        return xmlhttp.response;
    }

    // appel de la méthode de téléchargement de la liste d'emails avec une requête de type 'POST'
    var _telechargerLstMail = function (data, succesCallback, errorCallback) {
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            contentType: false,
            processData: false,
            success: succesCallback,
            error: errorCallback
        });
    }

    // appel de la méthode d'importation de la liste d'emails avec une requête de type 'POST'
    var _importerLstMail = function (data, succesCallback, errorCallback) {
        $.ajax({
            type: "POST",
            url: url,
            data: data,
            contentType: false,
            processData: false,
            success: succesCallback,
            error: errorCallback
        });
    }

    return {
        importManuel: _importManuel,
        importAuto: _importAuto,
        consulterLogErr: _consulterLogErr,
        consulterLogHist: _consulterLogHist,
        supprimerDonneesSante: _supprimerDonneesSante,
        telechargerLog: _telechargerLog,
        telechargerJson: _telechargerJson,
        telechargerLstMail: _telechargerLstMail,
        supprimerLog: _supprimerLog,
        importerJson: _importerJson,
        importerLstMail: _importerLstMail,
        verifierMdpJSON: _verifierMdpJSON,
        verifierMdpMAIL: _verifierMdpMAIL,
        alimenterLstDonneesSante: _alimenterLstDonneesSante
    }

}();