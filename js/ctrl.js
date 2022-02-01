ctrl = function () {

    // Ensemble des méthodes pour l'importation manuelle
    var _importManuel = function () {
        // on affiche le curseur tourant et on désactive le bouton d'import pour éviter les clics inutlies
        document.getElementById("infoLog").innerHTML = "";
        document.getElementById("spinner").style.display = "block";
        document.getElementById("btnImport").disabled = true;

        // on récupère le formulaire de la page HTML
        var form = $("#formImport");
        //on le transforme en un format exploitable pour PHP via 2 tests ternaires
        var formdata = (window.FormData) ? new FormData(form[0]) : null;
        var data = (formdata !== null) ? formdata : form.serialize();
        // il est ensuite envoyé au wrk.js. l'une ou l'autre méthode d'échec ou de succès sont ensuite exécutées avec le
        // contenu de data
        wrk.importManuel(data, _importManuelSuccess, _importManuelError);
    }
    var _importManuelSuccess = function (data) {
        document.getElementById("infoLog").innerHTML += data;
        document.getElementById("spinner").style.display = "none";
        document.getElementById("btnImport").disabled = false;
    }
    var _importManuelError = function (request, status, err) {
        if (status == "timeout") {
            window.alert("Timeout");
        } else {
            window.alert("Echec à la requête");
        }
        document.getElementById("spinner").style.display = "none";
        document.getElementById("btnImport").disabled = false;
    }

    // Ensemble des méthodes pour l'importation automatique
    var _importAuto = function () {
        // on affiche le curseur tourant et on désactive le bouton d'import pour éviter les clics inutlies
        document.getElementById("infoLog").innerHTML = "";
        document.getElementById("spinner").style.display = "block";
        document.getElementById("btnImportAuto").disabled = true;

        var data;
        wrk.importAuto(data, _importAutoSuccess, _importAutoError);
    }
    var _importAutoSuccess = function (data) {
        document.getElementById('infoLog').innerHTML += data;
        document.getElementById("spinner").style.display = "none";
        document.getElementById("btnImportAuto").disabled = false;
    }
    var _importAutoError = function (request, status, err) {
        if (status == "timeout") {
            window.alert("Timeout");
        } else {
            window.alert("Echec à la requête");
        }
        document.getElementById("spinner").style.display = "none";
        document.getElementById("btnImportAuto").disabled = false;
    }

    // Ensemble des méthodes pour la consultation du log d'erreur
    var _consulterLogErr = function () {
        document.getElementById("txtLog").innerHTML = "";
        // on récupère le formulaire de la page HTML
        var form = $("#formLogErr");
        //on le transforme en un format exploitable pour PHP via 2 tests ternaires
        var formdata = (window.FormData) ? new FormData(form[0]) : null;
        var data = (formdata !== null) ? formdata : form.serialize();
        // il est ensuite envoyé au wrk.js. l'une ou l'autre méthode d'échec ou de succès sont ensuite exécutées avec le
        // contenu de data
        wrk.consulterLogErr(data, _consulterLogErrSuccess, _consulterLogErrError);
    }
    var _consulterLogErrSuccess = function (data) {
        document.getElementById("txtLog").innerHTML += data;
    }
    var _consulterLogErrError = function (request, status, err) {
        if (status == "timeout") {
            window.alert("Timeout");
        } else {
            window.alert("Echec à la requête");
        }
    }

    // Ensemble des méthodes pour la consultation du log d'historique
    var _consulterLogHist = function () {
        document.getElementById("txtLog").innerHTML = "";
        // on récupère le formulaire de la page HTML
        var form = $("#formLogHist");
        //on le transforme en un format exploitable pour PHP via 2 tests ternaires
        var formdata = (window.FormData) ? new FormData(form[0]) : null;
        var data = (formdata !== null) ? formdata : form.serialize();
        // il est ensuite envoyé au wrk.js. l'une ou l'autre méthode d'échec ou de succès sont ensuite exécutées avec le
        // contenu de data
        wrk.consulterLogErr(data, _consulterLogErrSuccess, _consulterLogErrError);
    }
    var _consulterLogHistSuccess = function (data) {
        document.getElementById("txtLog").innerHTML += data;
    }
    var _consulterLogHistError = function (request, status, err) {
        if (status == "timeout") {
            window.alert("Timeout");
        } else {
            window.alert("Echec à la requête");
        }
    }

    // Ensemble des méthodes pour le téléchargement du log d'erreur
    var _telechargerLog = function () {
        document.getElementById("infoLog").innerHTML = "";
        document.getElementById("spinner").style.display = "block";
        document.getElementById("btnTelechargement").disabled = true;
        // on récupère le formulaire de la page HTML
        var form = $("#formTelechargerLogErr");
        //on le transforme en un format exploitable pour PHP via 2 tests ternaires
        var formdata = (window.FormData) ? new FormData(form[0]) : null;
        var data = (formdata !== null) ? formdata : form.serialize();
        // il est ensuite envoyé au wrk.js. l'une ou l'autre méthode d'échec ou de succès sont ensuite exécutées avec le
        // contenu de data
        wrk.telechargerLog(data, _telechargerLogSuccess, _telechargerLogError);
    }
    var _telechargerLogSuccess = function (data) {
        document.getElementById("spinner").style.display = "none";
        document.getElementById("btnTelechargement").disabled = false;
        // on teste si le retour est vide ou non
        if (data == false) {
            document.getElementById("infoLog").innerHTML += 'Pas de fichier présent';
        } else {
            // s'il n'est pas vide, on crée la structure qui ma permettre de le télécharger
            var element = document.createElement('a');
            element.setAttribute('href', 'data:text/plain;charset=utf-8, ' + encodeURIComponent(data));
            element.setAttribute('download', "error.log");
            //the above code is equivalent to
            // <a href="path of file" download="file name">
            document.body.appendChild(element);
            //onClick property
            element.click();
            document.body.removeChild(element);
        }
    }
    var _telechargerLogError = function (request, status, err) {
        if (status == "timeout") {
            window.alert("Timeout");
        } else {
            window.alert("Echec à la requête");
        }
        document.getElementById("spinner").style.display = "none";
        document.getElementById("btnTelechargement").disabled = false;
    }

    // Ensemble des méthodes pour le téléchargement du json
    var _telechargerJson = function () {
        var mdp = document.getElementById("mdpSupprJson").value;
        if (wrk.verifierMdpJSON(mdp)) {
            document.getElementById("infoLog").innerHTML = "";
            document.getElementById("spinner").style.display = "block";
            document.getElementById("btnTelechargementJson").disabled = true;
            // on récupère le formulaire de la page HTML
            var form = $("#formTelechargerJson");
            //on le transforme en un format exploitable pour PHP via 2 tests ternaires
            var formdata = (window.FormData) ? new FormData(form[0]) : null;
            var data = (formdata !== null) ? formdata : form.serialize();
            // il est ensuite envoyé au wrk.js. l'une ou l'autre méthode d'échec ou de succès sont ensuite exécutées avec le
            // contenu de data
            wrk.telechargerJson(data, _telechargerJsonSuccess, _telechargerJsonError);
        } else {
            alert('Mot de passe invalide.');
        }
    }
    var _telechargerJsonSuccess = function (data) {
        document.getElementById("mdpSupprJson").value = "";
        document.getElementById("spinner").style.display = "none";
        document.getElementById("btnTelechargementJson").disabled = false;
        // on teste si le retour est vide ou non
        if (data == false) {
            document.getElementById("infoLog").innerHTML += 'Pas de fichier présent';
        } else {
            // s'il n'est pas vide, on crée la structure qui ma permettre de le télécharger
            var element = document.createElement('a');
            element.setAttribute('href', 'data:text/plain;charset=utf-8, ' + encodeURIComponent(data));
            element.setAttribute('download', "importSpec.json");
            //the above code is equivalent to
            // <a href="path of file" download="file name">
            document.body.appendChild(element);
            //onClick property
            element.click();
            document.body.removeChild(element);
        }
    }
    var _telechargerJsonError = function (request, status, err) {
        if (status == "timeout") {
            window.alert("Timeout");
        } else {
            window.alert("Echec à la requête");
        }
        document.getElementById("spinner").style.display = "none";
        document.getElementById("btnTelechargementJson").disabled = false;
        document.getElementById("mdpSupprJson").value = "";
    }

    // Ensemble des méthodes pour la suppression du log d'erreur
    var _supprimerLog = function () {
        if (confirm("Etes-vous sûrs ?")) {
            document.getElementById("infoLog").innerHTML = "";
            // on récupère le formulaire de la page HTML
            var form = $("#formSupprimerLogErr");
            //on le transforme en un format exploitable pour PHP via 2 tests ternaires
            var formdata = (window.FormData) ? new FormData(form[0]) : null;
            var data = (formdata !== null) ? formdata : form.serialize();
            // il est ensuite envoyé au wrk.js. l'une ou l'autre méthode d'échec ou de succès sont ensuite exécutées avec le
            // contenu de data
            wrk.supprimerLog(data, _supprimerLogSuccess, _supprimerLogError);
        } else {
            document.getElementById("infoLog").innerHTML = "Suppression annulée !";
        }

    }
    var _supprimerLogSuccess = function (data) {
        document.getElementById("infoLog").innerHTML += data;
    }
    var _supprimerLogError = function (request, status, err) {
        if (status == "timeout") {
            window.alert("Timeout");
        } else {
            window.alert("Echec à la requête");
        }
    }

    // Ensemble des méthodes pour la suppression des données santé
    var _supprimerDonneesSante = function () {
        document.getElementById("infoLog").innerHTML = "";
        // on récupère le formulaire de la page HTML
        var form = $("#formDonnSa");
        //on le transforme en un format exploitable pour PHP via 2 tests ternaires
        var formdata = (window.FormData) ? new FormData(form[0]) : null;
        var data = (formdata !== null) ? formdata : form.serialize();
        // il est ensuite envoyé au wrk.js. l'une ou l'autre méthode d'échec ou de succès sont ensuite exécutées avec le
        // contenu de data
        wrk.supprimerDonneesSante(data, _supprimerDonneesSanteSuccess, _supprimerDonneesSanteError);
    }
    var _supprimerDonneesSanteSuccess = function (data) {
        document.getElementById("infoLog").innerHTML += data;
    }
    var _supprimerDonneesSanteError = function (request, status, err) {
        if (status == "timeout") {
            window.alert("Timeout");
        } else {
            window.alert("Echec à la requête");
        }
    }

    // Ensemble des méthodes pour l'importation du json
    var _importerJson = function () {
        if (confirm("Etes-vous sûrs ? Cela va écraser le fichier de paramètres (JSON) existant !")) {
            document.getElementById("infoLog").innerHTML = "";
            var mdp = document.getElementById("mdpSupprJson").value;
            if (wrk.verifierMdpJSON(mdp)) {
                // on récupère le formulaire de la page HTML
                var form = $("#formImportJson");
                //on le transforme en un format exploitable pour PHP via 2 tests ternaires
                var formdata = (window.FormData) ? new FormData(form[0]) : null;
                var data = (formdata !== null) ? formdata : form.serialize();
                // il est ensuite envoyé au wrk.js. l'une ou l'autre méthode d'échec ou de succès sont ensuite exécutées avec le
                // contenu de data
                wrk.importerJson(data, _importerJsonSuccess, _importerJsonError);
            } else {
                alert('Mot de passe invalide.');
            }
        } else {
            document.getElementById("infoLog").innerHTML = "Suppression annulée !";
            document.getElementById("mdpSupprJson").value = "";
        }
    }
    var _importerJsonSuccess = function (data) {
        document.getElementById("infoLog").innerHTML += data;
        document.getElementById("mdpSupprJson").value = "";
    }
    var _importerJsonError = function (request, status, err) {
        if (status == "timeout") {
            window.alert("Timeout");
        } else {
            window.alert("Echec à la requête");
        }
        document.getElementById("mdpSupprJson").value = "";
    }

    // Méthode qui transmet l'appel au worker pour alimenter les deux listes déroulantes
    var _alimenterLstDonneesSante = function () {
        var ret = wrk.alimenterLstDonneesSante();
        document.getElementById('lstDonnSan').innerHTML += ret;
    }

    // Ensemble des méthodes pour le téléchargement de la liste d'adresses mail
    var _telechargerLstMail = function () {
        var mdp = document.getElementById("mdpSupprLstMail").value;
        if (wrk.verifierMdpMAIL(mdp)) {
            document.getElementById("infoLog").innerHTML = "";
            document.getElementById("spinner").style.display = "block";
            document.getElementById("btnTelechargementLstMail").disabled = true;
            // on récupère le formulaire de la page HTML
            var form = $("#formTelechargerLstMail");
            //on le transforme en un format exploitable pour PHP via 2 tests ternaires
            var formdata = (window.FormData) ? new FormData(form[0]) : null;
            var data = (formdata !== null) ? formdata : form.serialize();
            // il est ensuite envoyé au wrk.js. l'une ou l'autre méthode d'échec ou de succès sont ensuite exécutées avec le
            // contenu de data
            wrk.telechargerLstMail(data, _telechargerLstMailSuccess, _telechargerLstMailError);
        } else {
            alert('Mot de passe invalide.');
        }
    }
    var _telechargerLstMailSuccess = function (data) {
        document.getElementById("mdpSupprLstMail").value = "";
        document.getElementById("spinner").style.display = "none";
        document.getElementById("btnTelechargementLstMail").disabled = false;
        // on teste si le retour est vide ou non
        if (data == false) {
            document.getElementById("infoLog").innerHTML += 'Pas de fichier présent';
        } else {
            // s'il n'est pas vide, on crée la structure qui ma permettre de le télécharger
            var element = document.createElement('a');
            element.setAttribute('href', 'data:text/plain;charset=utf-8, ' + encodeURIComponent(data));
            element.setAttribute('download', "listeMailImportAuto.php");
            //the above code is equivalent to
            // <a href="path of file" download="file name">
            document.body.appendChild(element);
            //onClick property
            element.click();
            document.body.removeChild(element);
        }
    }
    var _telechargerLstMailError = function (request, status, err) {
        if (status == "timeout") {
            window.alert("Timeout");
        } else {
            window.alert("Echec à la requête");
        }
        document.getElementById("spinner").style.display = "none";
        document.getElementById("btnTelechargementLstMail").disabled = false;
        document.getElementById("mdpSupprLstMail").value = "";
    }

    // Ensemble des méthodes pour l'importation de la liste d'adresses mail
    var _importerLstMail = function () {
        if (confirm("Etes-vous sûrs ? Cela va écraser la liste d'adresses mail existante !")) {
            document.getElementById("infoLog").innerHTML = "";
            var mdp = document.getElementById("mdpSupprLstMail").value;
            if (wrk.verifierMdpMAIL(mdp)) {
                // on récupère le formulaire de la page HTML
                var form = $("#formImportLstMail");
                //on le transforme en un format exploitable pour PHP via 2 tests ternaires
                var formdata = (window.FormData) ? new FormData(form[0]) : null;
                var data = (formdata !== null) ? formdata : form.serialize();
                // il est ensuite envoyé au wrk.js. l'une ou l'autre méthode d'échec ou de succès sont ensuite exécutées avec le
                // contenu de data
                wrk.importerLstMail(data, _importerLstMailSuccess, _importerLstMailError);
            } else {
                alert('Mot de passe invalide.');
            }
        } else {
            document.getElementById("infoLog").innerHTML = "Suppression annulée !";
            document.getElementById("mdpSupprLstMail").value = "";
        }
    }
    var _importerLstMailSuccess = function (data) {
        document.getElementById("infoLog").innerHTML += data;
        document.getElementById("mdpSupprLstMail").value = "";
    }
    var _importerLstMailError = function (request, status, err) {
        if (status == "timeout") {
            window.alert("Timeout");
        } else {
            window.alert("Echec à la requête");
        }
        document.getElementById("mdpSupprLstMail").value = "";
    }

    return {
        importManuel: _importManuel,
        importManuelSuccess: _importManuelSuccess,
        importManuelError: _importManuelError,
        importAuto: _importAuto,
        importAutoSuccess: _importAutoSuccess,
        importAutoError: _importAutoError,
        consulterLogErr: _consulterLogErr,
        consulterLogErrSuccess: _consulterLogErrSuccess,
        consulterLogErrError: _consulterLogErrError,
        consulterLogHist: _consulterLogHist,
        consulterLogHistSuccess: _consulterLogHistSuccess,
        consulterLogHistError: _consulterLogHistError,
        telechargerLog: _telechargerLog,
        telechargerLogSuccess: _telechargerLogSuccess,
        telechargerLogError: _telechargerLogError,
        telechargerJson: _telechargerJson,
        telechargerJsonSuccess: _telechargerJsonSuccess,
        telechargerJsonError: _telechargerJsonError,
        telechargerLstMail: _telechargerLstMail,
        telechargerLstMailSuccess: _telechargerLstMailSuccess,
        telechargerLstMailError: _telechargerLstMailError,
        supprimerLog: _supprimerLog,
        supprimerLogSuccess: _supprimerLogSuccess,
        supprimerLogError: _supprimerLogError,
        supprimerDonneesSante: _supprimerDonneesSante,
        supprimerDonneesSanteSuccess: _supprimerDonneesSanteSuccess,
        supprimerDonneesSanteError: _supprimerDonneesSanteError,
        importerJson: _importerJson,
        importerJsonSuccess: _importerJsonSuccess,
        importerJsonError: _importerJsonError,
        importerLstMail: _importerLstMail,
        importerLstMailSuccess: _importerLstMailSuccess,
        importerLstMailError: _importerLstMailError,
        alimenterLstDonneesSante: _alimenterLstDonneesSante
    }

}();