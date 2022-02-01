<?php

include_once('../fichiersDeConf/listeMailImportAuto.php');

require_once('phpmailer/src/PHPMailer.php');
require_once('phpmailer/src/SMTP.php');
require_once('phpmailer/src/Exception.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Classe WorkerMailManager
 *
 * Cette classe s'occupe de tout ce qui concerne l'envoi de mails.
 *
 * @version 1.1
 * @author Pittet David
 * @projet IAG - données lait
 */
class workerMailManager {

    /**
     * Envoie un email avec le contenu en paramètre. L'email est envoyé
     * à tous les adresses présentes dans le fichier listeMailImportAuto.php.
     * 
     * @param $corpsMessage contenu du mail
     */
    public function envoiMailImportAuto($corpsMessage, $logsAvecErreurs) {
        $Mail = new PHPMailer();
        $Mail->IsSMTP(); // Use SMTP
        $Mail->Host = "";
        $Mail->SMTPDebug = 0;
        $Mail->SMTPAuth = TRUE; // enable SMTP authentication
        $Mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $Mail->Port = 587;
        $Mail->Username = ''; //user logon name
        $Mail->Password = '';
        $Mail->Priority = 3; // Highest priority - Email priority (1 = High, 3 = Normal, 5 =   low)
        $Mail->CharSet = 'UTF-8';
        $Mail->Encoding = '8bit';
        $Mail->Subject = 'Importation automatique';
        $Mail->ContentType = 'text/html; charset=utf-8\r\n';
        $Mail->From = ''; //adresse mail
        $Mail->FromName = '';
        $Mail->WordWrap = 200; // RFC 2822 Compliant for Max 998 characters per line

        foreach (LST_ADRESSES_MAIL as $mail) {
            $Mail->addAddress($mail);
        }

        if ($logsAvecErreurs !== null) {
            foreach ($logsAvecErreurs as $unLog) {
                $Mail->addAttachment($unLog);
            }
        }
        
        $Mail->isHTML(TRUE);
        $corpsMessage = $corpsMessage . "<br><br><br><br><b>Machine IAG-Relait</b><br>Importation automatisée des fichiers 'Données lait' & 'Données santé'"
                . "<br><br>Institut Agricole de Grangeneuve";
        $Mail->Body = $corpsMessage;
        $Mail->AltBody = 'Mail informatif sur la machine Relait';
        $Mail->Send();
        $Mail->SmtpClose();
    }

}
