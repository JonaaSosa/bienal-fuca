<?php
session_cache_limiter('nocache');
date_default_timezone_set('America/Argentina/Buenos_Aires');

error_reporting(1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

$url = "https://www.google.com/recaptcha/api/siteverify";
$data = [
    'secret' => "6Lc-AcYZAAAAAFFiffnFr_n54H8r8eXEbke4CWOV",
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

if (($res['success'] == true)) {



    if (empty($_POST['participante']))
        echo "<b>El campo participante no puede estar vacio.</b><br>";
    if (empty($_POST['dni_participante']))
        echo "<b>El campo DNI no puede estar vacio.</b><br>";
    if (empty($_POST['fecha_nacimiento_participante']))
        echo "<b>El campo fecha de nacimiento no puede estar vacio.</b><br>";
    if (empty($_POST['nacionalidad']))
        echo "<b>El campo nacionalidad no puede estar vacio.</b><br>";
    if (empty($_POST['nombre_escuela']))
        echo "<b>El campo nombre de escuela a la que asiste no puede estar vacio.</b><br>";
    if (empty($_POST['responsable']))
        echo "<b>El campo nombre y apellido del padre, madre/tutor no puede estar vacio.</b><br>";
    if (empty($_POST['telefono']))
        echo "<b>El campo número de teléfono no puede estar vacio.</b><br>";
    if (empty($_POST['domicilio']))
        echo "<b>El campo domicilio no puede estar vacio.</b><br>";

    if ((!strchr($_POST['email'], "@")) || (!strchr($_POST['email'], "."))) {
        echo "<b>El campo email no puede estar vacio</b><br>";
        $valida = false;
    }


    $participante = isset($_POST["participante"]) ? $_POST["participante"] : null;
    $dni_participante = isset($_POST["dni_participante"]) ? $_POST["dni_participante"] : null;
    $fecha_nacimiento_participante = isset($_POST["fecha_nacimiento_participante"]) ? $_POST["fecha_nacimiento_participante"] : null;
    $nacionalidad = isset($_POST["nacionalidad"]) ? $_POST["nacionalidad"] : null;
    $nombre_escuela = isset($_POST["nombre_escuela"]) ? $_POST["nombre_escuela"] : null;
    $responsable = isset($_POST["responsable"]) ? $_POST["responsable"] : null;
    $telefono = isset($_POST["telefono"]) ? $_POST["telefono"] : null;
    $domicilio = isset($_POST["domicilio"]) ? $_POST["domicilio"] : null;
    $email = isset($_POST["email"]) ? $_POST["email"] : null;
    $to =  $email;
    $subject = 'Nuevo Participante';
    $fichero = 'participantes.csv';
    $fecha = date("Y-m-d H:i:s");
    $linea = $fecha . ";" . strtoupper($participante) . ";" . $dni_participante . ";" . $fecha_nacimiento_participante . ";" .  strtoupper($nacionalidad) . ";" . strtoupper($nombre_escuela) . ";" . strtoupper($responsable) . ";" . $telefono .  ";" . strtoupper($domicilio) .  ";" . $email . "\n";
    file_put_contents($fichero, $linea, FILE_APPEND | LOCK_EX);

    $body = "Nombre y Apellido: $participante<br><br>";
    $body .= "Documento: $dni_participante<br><br>";
    $body .= "Fecha de nacimiento: $fecha_nacimiento_participante<br><br>";
    $body .= "Nacionalidad: $nacionalidad<br><br>";
    $body .= "Nombre de la escuela: $nombre_escuela<br><br>";
    $body .= "Responsable: $responsable<br><br>";
    $body .= "Telefono: $telefono<br><br>";
    $body .= "Domicilio: $domicilio<br><br>";
    $body .= "Email: $email<br><br>";

    $to = $to;
    $asunto = $subject;

    if (!file_exists($dni_participante)) {
        mkdir($dni_participante, 0777, true);

        $total = count($_FILES['upload']);
        for ($i = 0; $i < $total; $i++) {
            $tmpFilePath = $_FILES['upload']['tmp_name'][$i];
            if ($tmpFilePath != "") {
                $newFilePath = $dni_participante . "/" . $_FILES['upload']['name'][$i];
                if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                }
            }
        }

        $mail_enviado = enviarMail($to, $asunto, $body);
        return $mail_enviado;
    } else {
        echo "El participante ya cuenta con una inscripción en curso";
    }
} else {
    echo "Falla en captcha";
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
        $mail->Username = "no-responder@fucaarte.org";
        $mail->Password = "fED^@wsGK*QG";

        $mail->setFrom('no-responder@fucaarte.org', 'no-responder');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpo;
        $mail->AltBody = $cuerpo;

        $mail->send();
        return "OK";
    } catch (Exception $e) {
        return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
