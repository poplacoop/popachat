
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/phpmailer/phpmailer/src/Exception.php';
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';


function sendMail($from,$to,$cc,$bcc,$subject,$content,$attach=""){
    $mailMsg= $content;
    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->Mailer = "smtp";

    //$mail->SMTPDebug  = 1;  
    $mail->SMTPAuth   = TRUE;
    $mail->SMTPSecure = "tls";
    $mail->Port       = 587;
    
    $mail->Host = 'smtp.gmail.com';
    $mail->Username = "poplacoop@gmail.com";
    $mail->Password = '02201616';

    $mail->addReplyTo($from, $from);
    $mail->Sender=$from; 
    $mail->setFrom($from);
    
    $toTbl=explode(",",$to);
    foreach ($toTbl as $to){
        $mail->addCC($to);
    }
    
    
    $ccTbl=explode(",",$cc);
    foreach ($ccTbl as $cc){
        $mail->addCC($cc);
    }

    $mail->CharSet = 'UTF-8';   
    $mail->addBCC ("didier.cransac03@gmail.com");
    $mail->addBCC ($bcc);
    $mail->Subject = $subject;
    //$content=utf8_decode($content);
    //$content = mb_encode_mimeheader($content,"UTF-8");
    //$content = mb_encode_mimeheader($content);
    $mail->Body = $content;
    if ($attach!=""){
        foreach ($attach as $file){
            $mail->addAttachment($file[0],$file[1]);
        }
    }
    
    
    if(!$mail->Send()) {
      echo "Error while sending Email.<br>";
      var_dump($mail);
      return false;
    } 
    else {
      $mailMsg.= "<br>Email sent successfully<br>";
      return $mailMsg;
    }

}
//sendMail($from,$to,$cc,$bcc,$subject,$content,$attach="")
//sendMail("famcransac@free.fr","didier.cransac@free.fr","","","Un essai très pénible...","...super réussi bonnes fêtes de pâques");
?>
