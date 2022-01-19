<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    require libraries.'/PHPMailer/Exception.php';
    require libraries.'/PHPMailer/PHPMailer.php';
    require libraries.'/PHPMailer/SMTP.php';
    function EnviarCorreo($CorreoDestino,$NombreDestino,$Asunto,$Mensaje){
        $MiCorreo='';
        $MiNombre='Mi Empresa';
        $MiClave='';
        $mail = new PHPMailer(true);
        try {
            $mail->SMTPDebug  = 0;
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $MiCorreo;
            $mail->Password   = $MiClave;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->setFrom($MiCorreo,$MiNombre);
            $mail->addAddress($CorreoDestino,$NombreDestino);
            $mail->isHTML(true);
            $mail->Subject    = $Asunto;
            $mail->AddEmbeddedImage(libraries.'/PHPMailer/Logo.jpg','logo_php','logo','base64','image/jpg');
            $mail->Body       = $Mensaje;
            $mail->CharSet    = 'UTF-8';
            $mail->Encoding   = 'base64';
            $mail->send();
        } catch (Exception $e) {
            //echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            return false;
        }
        return true;
    }