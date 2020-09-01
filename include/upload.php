<?php
session_cache_limiter('nocache');
date_default_timezone_set('America/Argentina/Buenos_Aires');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

$url = "https://www.google.com/recaptcha/api/siteverify";
$data = [
    'secret' => "6Lf_eMEZAAAAAMjtqaLhwwc5Uw2HrkcpHmLiuGyW",
    'response' => $_POST['token'],
    'remoteip' => $_SERVER['REMOTE_ADDR']
];

$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
    )
);

$context  = stream_context_create($options);
$response = file_get_contents($url, false, $context);
$res = json_decode($response, true);


if ($res['success'] == true) {
    

    $to = "jonathansosa@gmail.com";


    $participante = isset($_POST["participante"]) ? $_POST["participante"] : null;
    $dni_participante = isset($_POST["dni_participante"]) ? $_POST["dni_participante"] : null;
    $fecha_nacimiento_participante = isset($_POST["fecha_nacimiento_participante"]) ? $_POST["fecha_nacimiento_participante"] : null;
    $nacionalidad = isset($_POST["nacionalidad"]) ? $_POST["nacionalidad"] : null;
    $nombre_escuela = isset($_POST["nombre_escuela"]) ? $_POST["nombre_escuela"] : null;
    $responsable = isset($_POST["responsable"]) ? $_POST["responsable"] : null;
    $telefono = isset($_POST["telefono"]) ? $_POST["telefono"] : null;
    $domicilio = isset($_POST["domicilio"]) ? $_POST["domicilio"] : null;
    $email = isset($_POST["email"]) ? $_POST["email"] : null;

    $subject = 'Nuevo Participante';
    $fichero = 'participantes.csv';
    $fecha = date("Y-m-d H:i:s");
    $linea = $fecha . ";" . strtoupper($participante) . ";" . $dni_participante . ";" . $fecha_nacimiento_participante . ";" .  strtoupper($nacionalidad) . ";" . strtoupper($nombre_escuela) . ";" . strtoupper($responsable) . ";" . $telefono .  ";" . strtoupper($domicilio) .  ";" . $email . "\n";
    file_put_contents($fichero, $linea, FILE_APPEND | LOCK_EX);

    $body = "Nombre y Apellido: $participante<br><br>" ;
    $body.= "Documento: $dni_participante<br><br>";
    $body.= "Fecha de nacimiento: $fecha_nacimiento_participante<br><br>";
    $body.= "Nacionalidad: $nacionalidad<br><br>";
    $body.= "Nombre de la escuela: $nombre_escuela<br><br>";
    $body.= "Responsable: $responsable<br><br>";
    $body.="Telefono: $telefono<br><br>";
    $body.= "Domicilio: $domicilio<br><br>";
    $body.= "Email: $email<br><br>";

    $to = $to;
    $asunto = $subject;

    if (!file_exists($dni_participante)) {
        mkdir($dni_participante, 0777, true);
       
        //necesita <input name="upload[]" type="file" multiple="multiple" />
        
        $total = count($_FILES['upload']['name']);
        for( $i=0 ; $i < $total ; $i++ ) {
        $tmpFilePath = $_FILES['upload']['tmp_name'][$i];
        if ($tmpFilePath != ""){
            $newFilePath = $dni_participante."/" . $_FILES['upload']['name'][$i];
            if(move_uploaded_file($tmpFilePath, $newFilePath)) {
            }
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $mail_enviado = enviarMail($to, $asunto, $body);
          return $mail_enviado;
        }
    }else{
        echo "El participante ya cuanta con una inscripción en curso";
    }
}

function enviarMail($to, $asunto, $cuerpo)
{
    $mail = new PHPMailer(true);

    try {
        
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->isSMTP();

        $mail->Host = 'mail.fucaarte.org';
        $mail->Port = 465;
        $mail->CharSet = "UTF-8";
        $mail->SMTPSecure = 'ssl';
        $mail->SMTPAuth = true;
        $mail->Username = "no-reply@fucaarte.org";
        $mail->Password = "fED^@wsGK*QG";
      
        $mail->setFrom('no-reply@fucaarte.org', 'no-reply');
        $mail->addAddress($to); 

        $mail->isHTML(true);                                 
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpo;
        $mail->AltBody = $cuerpo;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
