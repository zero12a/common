<?php

//composer require phpmailer/phpmailer
class mailObject
{
	private $username;
	private $password;
	private $host;
	private $port;

	//생성자
	function __construct($CFG){
		$this->username = $CFG["CFG_SMTP_UID"];
		$this->password = $CFG["CFG_SMTP_PWD"];
		$this->host = $CFG["CFG_SMTP_HOST"];
		$this->port = $CFG["CFG_SMTP_PORT"];
	}

	function sendMail($sender, $t_to_email,$t_to_name,$t_subject,$t_message){
		switch ($sender){
			case "GMAIL" :
				return $this->sendGmail($t_to_email,$t_to_name,$t_subject,$t_message); 
			case "NAVER" :
				return $this->sendNaver($t_to_email,$t_to_name,$t_subject,$t_message); 
			case "DAUM" :
				return $this->sendDaum($t_to_email,$t_to_name,$t_subject,$t_message); 
			case "EXCHANGE" :
				return $this->sendExchange($t_to_email,$t_to_name,$t_subject,$t_message); 
			default:
				JsonMsg("500","110","sendMail sender 명령을 찾을 수 없습니다. (sendMail no sender)");
				break;
		}
	}

	//구글 > 보안 > "보안 수준이 낮은 앱의 액세스"를 허용해 줘야함.
	//위 설정 안하면 아래와 같이 에러 발생함.
	//SMTP ERROR: Password command failed: 535-5.7.8 Username and Password not accepted. Learn more at
	//535 5.7.8  https://support.google.com/mail/?p=BadCredentials
	//550건 발송이 넘어 서자 아래와 같이 오류 발생 (https://support.google.com/a/answer/166852#zippy=%2Cfree-trial-account-limits)
	//454 4.7.0 Too many login attempts, please try again later.
	//보낸 편지함에 smtp발송 기록이 남음 (daum, naver 는 안 남음)
	private function sendGmail($t_to_email,$t_to_name,$t_subject,$t_message){
		echo "sendGmail()...........................................start" . PHP_EOL;
		$mail = new PHPMailer\PHPMailer\PHPMailer;
		$mail->SMTPDebug  = 4;
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->CharSet = 'utf-8'; 
		$mail->Encoding = "base64";
		$mail->Port = 465; 
		//$mail->CharSet = "euc-kr"; //한글문제
		//$mail->Encoding = "base64"; //한글문제
		
		$mail->Host = 'smtp.gmail.com';  // Specify main and backup server
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = $this->username;                            // SMTP username
		$mail->Password = $this->password;                           // SMTP password
		$mail->SMTPSecure = 'ssl';                            // Enable encryption, 'ssl' also accepted
		
		$mail->From = $this->username . '@gmail.com';
		$mail->FromName = "보안 관리자"; 
		//$mail->addAddress('zero12a@naver.com', 'Josh Adams');  // Add a recipient
		$mail->addAddress($t_to_email,$t_to_name);               // Name is optional
		//$mail->addAddress('zero12a@dreamwiz.com');               // Name is optional
		//$mail->addReplyTo('info@example.com', 'Information');
		//$mail->addCC('cc@example.com');
		//$mail->addBCC('bcc@example.com');
		
		$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
		//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
		//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
		$mail->isHTML(true);                                  // Set email format to HTML
		
		$mail->Subject = $t_subject; 
		$mail->Body    = $t_message;
		//$mail->AltBody = iconv("UTF-8","EUC-KR", 'AltBody 입니다. This is the body in plain text for non-HTML mail clients');
		
		try {		
			if(!$mail->send()) {
				echo "Message could not be sent.(Error : " . $mail->ErrorInfo . ")";
				return array(false, "Send fail - 1: " . $mail->ErrorInfo);
			}else{
				return array(true,"Send success");
			}
		}catch(Exception $e){
			echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";

			return array(false,"Send fail - 2: " . $mail->ErrorInfo);
		}
	}


	//1000건 발송시 734초
	//(to daum) 1000건중 1건 실패 
	//essage could not be sent.Mailer Error: SMTP connect() failed. https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting948
	//(to gmail) 1000건 모두 성공 386초
	private function sendNaver($t_to_email,$t_to_name,$t_subject,$t_message){
		echo "sendNaver()...........................................start" . PHP_EOL;
		$mail = new PHPMailer\PHPMailer\PHPMailer;
		//$mail->SMTPDebug  = 4;

		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->CharSet = 'utf-8'; 
		$mail->Encoding = "base64";
		$mail->Port = 465; 
		//$mail->CharSet = "euc-kr"; //한글문제
		//$mail->Encoding = "base64"; //한글문제
		
		$mail->Host = 'smtp.naver.com';  // Specify main and backup server
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = $this->username;                            // SMTP username
		$mail->Password = $this->password;                           // SMTP password
		$mail->SMTPSecure = 'ssl';                            // Enable encryption, 'ssl' also accepted
		
		$mail->From = $this->username . '@naver.com';
		$mail->FromName = "보안 관리자"; 
		//$mail->addAddress('zero12a@naver.com', 'Josh Adams');  // Add a recipient
		$mail->addAddress($t_to_email,$t_to_name);               // Name is optional
		//$mail->addAddress('zero12a@dreamwiz.com');               // Name is optional
		//$mail->addReplyTo('info@example.com', 'Information');
		//$mail->addCC('cc@example.com');
		//$mail->addBCC('bcc@example.com');
		
		$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
		//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
		//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
		$mail->isHTML(true);                                  // Set email format to HTML
		
		$mail->Subject = $t_subject; 
		$mail->Body    = $t_message;
		//$mail->AltBody = iconv("UTF-8","EUC-KR", 'AltBody 입니다. This is the body in plain text for non-HTML mail clients');
		
		try {		
			if(!$mail->send()) {
				echo "Message could not be sent.(Error : " . $mail->ErrorInfo . ")";
				return array(false, "Send fail - 1: " . $mail->ErrorInfo);
			}else{
				return array(true,"Send success");
			}
		}catch(Exception $e){
			echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";

			return array(false,"Send fail - 2: " . $mail->ErrorInfo);
		}
	}

	//100건 발송시 82초(to naver)
	// (to gmail) 1000건 발송시 전체 성공 1394초 소요
	// (to daum)1000건을 다음내 다른메일로 발송시 1건실패 1354초 소요 
	// (to naver)1000건을 naver메일로 발송시 전체성공 1111초
	//다음에서 다음 발송시 본인 메일 발송은 "no search user or 휴면 고객" 에러 발생
	// 1000건 중 500건 발송시 아래와 같이 에러남.
	// Message could not be sent.Mailer Error: SMTP Error: data not accepted.SMTP server error: DATA command failed502 sendDaum()...........................................start
	private function sendDaum($t_to_email,$t_to_name,$t_subject,$t_message){
		echo "sendDaum()...........................................start" . PHP_EOL;
		$mail = new PHPMailer\PHPMailer\PHPMailer;
		//$mail->SMTPDebug  = 4;

		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->CharSet = 'utf-8'; 
		$mail->Encoding = "base64";
		$mail->Port = 465; 
		//$mail->CharSet = "euc-kr"; //한글문제
		//$mail->Encoding = "base64"; //한글문제
		
		$mail->Host = 'smtp.daum.net';  // Specify main and backup server
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = $this->username;                            // SMTP username
		$mail->Password = $this->password;                           // SMTP password
		$mail->SMTPSecure = 'ssl';                            // Enable encryption, 'ssl' also accepted
		
		$mail->From = $this->username . '@daum.net';
		$mail->FromName = "보안 관리자"; 
		//$mail->addAddress('zero12a@naver.com', 'Josh Adams');  // Add a recipient
		$mail->addAddress($t_to_email,$t_to_name);               // Name is optional
		//$mail->addAddress('zero12a@dreamwiz.com');               // Name is optional
		//$mail->addReplyTo('info@example.com', 'Information');
		//$mail->addCC('cc@example.com');
		//$mail->addBCC('bcc@example.com');
		
		$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
		//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
		//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
		$mail->isHTML(true);                                  // Set email format to HTML
		
		$mail->Subject = $t_subject; 
		$mail->Body    = $t_message;
		//$mail->AltBody = iconv("UTF-8","EUC-KR", 'AltBody 입니다. This is the body in plain text for non-HTML mail clients');
		
		try {		
			if(!$mail->send()) {
				echo "Message could not be sent.(Error : " . $mail->ErrorInfo . ")";
				return array(false, "Send fail - 1: " . $mail->ErrorInfo);
			}else{
				return array(true,"Send success");
			}
		}catch(Exception $e){
			echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";

			return array(false,"Send fail - 2: " . $mail->ErrorInfo);
		}
	}


	private function CustMail2($t_to_email,$t_to_name,$t_subject,$t_message){
		$mail = new PHPMailer;
		
		$mail->CharSet = "euc-kr"; //한글문제
		$mail->Encoding = "base64"; //한글문제
		
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'smtp.gmail.com';  // Specify main and backup server
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = '';                            // SMTP username
		$mail->Password = '';                           // SMTP password
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
			echo "Message could not be sent.(Error : " . $mail->ErrorInfo . ")";
			exit;
		}
		
		echo 'Message has been sent';
	}


	private function sendExchange($t_to_email,$t_to_name,$t_subject,$t_message){
		echo "sendExchange()...........................................start" . PHP_EOL;
		$mail = new PHPMailer\PHPMailer\PHPMailer;
		//$mail->SMTPDebug  = 4;

		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->CharSet = 'utf-8'; 
		$mail->Encoding = "base64";
		$mail->Port = $this->port; 
		$mail->SMTPOptions = array (
			'ssl' => array(
				'verify_peer'  => false,
				'verify_peer_name'  => false,
				'allow_self_signed' => true));
		
		$mail->Host = $this->host;  // Specify main and backup server
		$mail->SMTPAuth = false;                               // Enable SMTP authentication
		//$mail->Username = $this->username;                            // SMTP username
		//$mail->Password = $this->password;                           // SMTP password
		$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted
		
		$mail->From = 'info@test.com';
		$mail->FromName = "보안 관리자"; 
		//$mail->addAddress('zero12a@naver.com', 'Josh Adams');  // Add a recipient
		$mail->addAddress($t_to_email,$t_to_name);               // Name is optional
		//$mail->addAddress('zero12a@dreamwiz.com');               // Name is optional
		//$mail->addReplyTo('info@example.com', 'Information');
		//$mail->addCC('cc@example.com');
		//$mail->addBCC('bcc@example.com');
		
		//$mail->WordWrap = 50;                                 // Set word wrap to 50 characters
		//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
		//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
		$mail->isHTML(true);                                  // Set email format to HTML
		
		$mail->Subject = $t_subject; 
		$mail->Body    = $t_message;
		//$mail->AltBody = iconv("UTF-8","EUC-KR", 'AltBody 입니다. This is the body in plain text for non-HTML mail clients');
		
		try {		
			if(!$mail->send()) {
				echo "Message could not be sent.(Error : " . $mail->ErrorInfo . ")";
				return array(false, "Send fail - 1: " . $mail->ErrorInfo);
			}else{
				return array(true,"Send success");
			}
		}catch(Exception $e){
			echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";

			return array(false,"Send fail - 2: " . $mail->ErrorInfo);
		}
	}

}
?>