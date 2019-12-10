<?php
require_once './lib/PHPMailer/PHPMailerAutoload.php';

function CustMail2($t_to_email,$t_to_name,$t_subject,$t_message){
	$mail = new PHPMailer;
	
	$mail->CharSet = "euc-kr"; //한글문제
	$mail->Encoding = "base64"; //한글문제
	
	$mail->isSMTP();                                      // Set mailer to use SMTP
	$mail->Host = 'smtp.gmail.com';  // Specify main and backup server
	$mail->SMTPAuth = true;                               // Enable SMTP authentication
	$mail->Username = 'zero12a';                            // SMTP username
	$mail->Password = '0one2777';                           // SMTP password
	$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted
	
	$mail->From = 'zero2a@gmail.com';
	$mail->FromName = iconv("UTF-8", "EUC-KR", $t_to_name); 
	//$mail->addAddress('zero12a@naver.com', 'Josh Adams');  // Add a recipient
	$mail->addAddress($t_to_email);               // Name is optional
	//$mail->addAddress('zero12a@dreamwiz.com');               // Name is optional
	//$mail->addReplyTo('info@example.com', 'Information');
	//$mail->addCC('cc@example.com');
	//$mail->addBCC('bcc@example.com');
	
	$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
	//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
	//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
	$mail->isHTML(true);                                  // Set email format to HTML
	
	$mail->Subject = iconv("UTF-8", "EUC-KR", $t_subject); 
	$mail->Body    = iconv("UTF-8", "EUC-KR", $t_message);
	//$mail->AltBody = iconv("UTF-8","EUC-KR", 'AltBody 입니다. This is the body in plain text for non-HTML mail clients');
	
	if(!$mail->send()) {
	   echo 'Message could not be sent.';
	   echo 'Mailer Error: ' . $mail->ErrorInfo;
	   exit;
	}
	
	echo 'Message has been sent';
}
?>