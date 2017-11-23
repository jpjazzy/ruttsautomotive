<?php
if (function_exists('mb_internal_encoding')) {
	mb_internal_encoding('UTF-8');
	mb_http_output('UTF-8');
	mb_http_input('UTF-8');
	mb_language('uni');
	mb_regex_encoding('UTF-8');
	ob_start('mb_output_handler');
}

require_once('config.php');

/*****************************************************************

	Web4Future Easiest Form2Mail.
	Copyright (C) 1998-2017 Web4Future.com All Rights Reserved. 
	http://www.Web4Future.com/

	You can include this file in any of your project, as long as you
	also include this text.

	Copyright (C) the fonts used in the captcha folder are copyright their respective owners! Please substitute with your own fonts.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 

*****************************************************************/

# DO NOT EDIT BELOW THIS LINE UNLESS YOU KNOW WHAT YOU ARE DOING ===========================================
define('F2M_VER', '3.5');

define('REMOTE_ADDR',($_SERVER['HTTP_X_FORWARDED_FOR'] == "" ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_FORWARDED_FOR'])); //not 100% accurate, but works

$w4fx = stristr(file_get_contents('blockip.txt'),REMOTE_ADDR); 

if ($w4fx !== FALSE) {
	showError('ILLEGAL EXECUTION DETECTED!');	
}
if (checkCAPTHCA()) {
	$errors = array();
	//add time on site?
	$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
	$required = explode(',', REQUIRED_FIELDS);
	foreach ($_POST as $key => $val) {
		if (in_array($key, $required)) {
			if (empty($val)) {
				$errors[] = sprintf(MSG_REQUIRED, $key);
				continue;
			}
		}
		if (is_array($val)) { 
			$val = implode(', ', $val);
			$key = str_replace('[]', '', $key);
		}
		$val = sanitizeAndCheck($val);
		if ((strcasecmp($key,"submit") != 0) && (strcasecmp($key,"captcha-response") != 0)) {
			$val = (empty($val)) ? '-' : $val;
			$w4fMessage .= "<b>$key:</b> ".nl2br($val)."<br>\n";
		}
	} // end while
	if (!empty($errors)) {
		showError($errors);		
	} else {
		sendMail($w4fMessage);
	}
} else { 
	showError('WRONG CAPTCHA!');
}

//mes can be a string or an array of errors
function showError($mes) {
	if ($mes === false) { //all is OK
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			echo json_encode(array('error' => false,'success' => true));
		} else {
			header("Location: ".THANKYOU_PAGE);
		}
		exit;
	}
	if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
		echo json_encode(array('error'=>$mes));
	} else {
		if ((HEADER != '') && file_exists(HEADER))
			include(HEADER);
		if (is_array($mes))
			$mes = implode('<br>', $mes);
		echo "<b style='color:red'>$mes</b>";
		if ((FOOTER != '') && file_exists(FOOTER))
			include(FOOTER);
	}
	exit;
}

function checkCAPTHCA() {
	if (CAPTCHA != '') {
		if (GOOGLE_RECAPTHCA_SECRET != '') {
			if (checkRECAPTHCA()) return true;
		} else {
			if ((md5($_POST['captcha-response'].SALT.md5($_SERVER['SERVER_ADDR'].$_SERVER['SERVER_NAME']).(round(time() / (COOKIE_VALID_MIN * 60)) * (COOKIE_VALID_MIN * 60))) == $_COOKIE['captcha']) && (!empty($_COOKIE['captcha']))) return true;
		}
	} else {
		return true;
	}
	return false;
}

function checkRECAPTHCA() {
	$url = 'https://www.google.com/recaptcha/api/siteverify';
	$data = array('secret'=>GOOGLE_RECAPTHCA_SECRET,'response' => $_POST['g-recaptcha-response'],'remoteip'=>REMOTE_ADDR);
	$ch = curl_init();
    $options = array(
    	CURLOPT_URL				=> $url, 		//set the URL
    	CURLOPT_POST 			=> true,		//post
        CURLOPT_RETURNTRANSFER	=> true,		// return web page
        CURLOPT_HEADER			=> false,		// don't return headers
        // CURLOPT_FOLLOWLOCATION	=> true,		// follow redirects
        // CURLOPT_ENCODING		=> '',			// handle all encodings
        // CURLOPT_CONNECTTIMEOUT	=> 30,			// timeout on connect
        // CURLOPT_TIMEOUT			=> 30,			// timeout on response
        // CURLOPT_MAXREDIRS		=> 5,			// stop after X redirects
        CURLOPT_POSTFIELDS		=> http_build_query($data), //send the data
        CURLOPT_SSL_VERIFYPEER	=> SSL_CHECK,	//verifying the peer's certificate
    );
	curl_setopt_array($ch, $options);

    $result = curl_exec($ch);
    $errno  = curl_errno( $ch );
    $errmsg = curl_error( $ch );

    $json = json_decode($result,1);
	if ($json['success']) return true;

    curl_close( $ch );    
    return false;
}

function get_client_ip() {
    $ipaddress = '';
    if ($_SERVER['HTTP_CLIENT_IP'])
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'].'<br />';
    if($_SERVER['HTTP_X_FORWARDED_FOR'])
        $ipaddress .= 'IP X FORWARDED FOR '.$_SERVER['HTTP_X_FORWARDED_FOR'].'<br />';
    if($_SERVER['HTTP_X_FORWARDED'])
        $ipaddress .= 'IP X FORWARDED '.$_SERVER['HTTP_X_FORWARDED'].'<br />';
    if($_SERVER['HTTP_FORWARDED_FOR'])
        $ipaddress .= 'IP FORWARDED FOR '.$_SERVER['HTTP_FORWARDED_FOR'].'<br />';
    if($_SERVER['HTTP_FORWARDED'])
        $ipaddress .= 'IP FORWARDED '.$_SERVER['HTTP_FORWARDED'].'<br />';
    if($_SERVER['REMOTE_ADDR'])
        $ipaddress .= $_SERVER['REMOTE_ADDR'].'<br />';
    if (!$ipaddress)
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

//function blockIP
function blockip($ip) {
	$h = @fopen("blockip.txt", 'a');
	@fwrite($h, $ip."\n");
	@fclose($h); 
	showError('INVALID DATA');
}

function sanitizeAndCheck($val) {
	$val = htmlspecialchars(trim($val), ENT_COMPAT, 'UTF-8');
	if ($val) {
		if (stristr($val,"Content-Type") || stristr($val,"MIME-Version") || stristr($val,"Content-Transfer-Encoding") || stristr($val,"bcc:")) {
			blockip(REMOTE_ADDR);
		}
	}
	return $val;
}

function sendMail($w4fMessage) {
	$oemail = $_POST['email'];
	$_POST['email'] = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
	if ($oemail != $_POST['email']) {
		$w4fMessage .= "<b><font color='red'>Sender Email is NOT a VALID email address!</font></b><br>\n";
		// file_put_contents('log.txt', REMOTE_ADDR."\t{$oemail}\n");
	}
	$_POST['name'] = filter_var($_POST['name'], FILTER_SANITIZE_STRING);

	$_POST['email'] = (empty($_POST['email'])) ? MY_EMAIL : $_POST['email'];
	$_POST['name'] = (empty($_POST['name'])) ? 'Form2Mail' : $_POST['name'];
    
    $replace = array("/To:/i", "/Cc:/i", "/Bcc:/i","/Content-Type:/i","/\n/");
	$_POST['name'] = preg_replace($replace, '', $_POST['name']);

	$uid = md5(uniqid(time()));
	$uid1 = md5($uid.'mime');
	$uid2 = md5($uid.'html');


	$w4fMessage = "<!DOCTYPE html><html><head><meta charset='utf-8'></head><body>\n<font face=3Dverdana size=3D2>\n".$w4fMessage."\n<font size=3D1><br>\n Sender IP: ".get_client_ip()."</font><br>\n Powered by <a href='http://www.web4future.com' target='_blank'>Web4Future Easiest Form2Mail</a></font></body></html>";

//handle attachments
	$nmessage = '';
	if (!empty(ALLOW_ATTACHMENTS)) {
		if (is_array($_FILES)) {
			foreach ($_FILES as $fname => $tfile) {
				if (is_array($tfile['name'])) {
					foreach ($tfile['name'] as $key => $tfile2) {
						//the same code as bellow
						if ($tfile['tmp_name'][$key]) {
							$nmessage .= attach($uid1,$tfile['tmp_name'][$key],$tfile['name'][$key],$tfile['size'][$key]);							
						}
					}
				} else {
					if ($tfile['tmp_name']) {						
						$nmessage .= attach($uid1,$tfile['tmp_name'],$tfile['name'],$tfile['size']);
					}
				}
			}
		}
	} else {
		//check attachments to see if user, still added them.. if so => spam => block
		if (!empty($_FILES)) {
			blockip(REMOTE_ADDR);
		}
	}
//end attachments
	$header .= "From: {$_SERVER['SERVER_NAME']} <server@".str_replace('www.', '', $_SERVER['SERVER_NAME']).">\r\n";
	$header .= "Reply-To: =?utf-8?Q?".quoted_printable_encode($_POST['name'])."?= <{$_POST['email']}>\r\n";
	$header .= "Message-ID: <". $uid ."@". str_replace("www.","",$_SERVER["SERVER_NAME"]) .">\r\n";
	$header .= "MIME-Version: 1.0\r\n";
	if ($nmessage != '') {
		$header .= "Content-Type: multipart/mixed; boundary=\"".$uid1."\"\r\n";
	} else {
		$header .= "Content-Type: multipart/alternative; boundary=\"".$uid2."\"\r\n";
	}
	$header .= "X-Priority: 3\r\n";
	$header .= "X-Mailer: PHP/" . phpversion()."\r\n";
	$header .= "User-Agent: Produced By Web4Future Easiest Form2Mail ".F2M_VER."\r\n";
	
	// $mailMessage = "This is a multi-part message in MIME format.\r\n\r\n";
	if ($nmessage != '') {
		$mailMessage .= "--".$uid1."\r\n";
		$mailMessage .= "Content-Type: multipart/alternative; boundary=\"".$uid2."\"\r\n\r\n";
	}
	$mailMessage .= "--".$uid2."\r\n";
	$mailMessage .= "Content-Type: text/plain; charset=\"UTF-8\"\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n";
	$mailMessage .= trim(strip_tags($w4fMessage))."\r\n\r\n";			
	$mailMessage .= "--".$uid2."\r\n";	
	$mailMessage .= "Content-Type: text/html; charset=\"utf-8\"\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n";			
	$mailMessage .= "$w4fMessage\r\n\r\n";
	$mailMessage .= "--".$uid2."--\r\n";	
	if ($nmessage != '') {
		$mailMessage .= $nmessage."--".$uid1."--";
	}		
	$encoded_subject = mb_encode_mimeheader(MAIL_SUBJECT, 'UTF-8', 'B', "\r\n", strlen('Subject: ')); //from http://stackoverflow.com/questions/4389676/email-from-php-has-broken-subject-header-encoding

	if (!mail(MY_EMAIL, $encoded_subject, $mailMessage,$header)) { showError("Error sending e-mail!"); }
	else { showError(false); }	
}

/**
 * attach file
 * @param  [type] $uid1     UID of multipart
 * @param  [type] $file     tmp_filename
 * @param  [type] $fileName filename
 * @param  [type] $fileSize filesize
 */
function attach($uid1,$file,$fileName,$fileSize) {
	$ext = pathinfo($fileName,PATHINFO_EXTENSION);
	if (!in_array($ext, explode(',',ALLOW_ATTACHMENTS))) {
		showError(MSG_FILE_TYPE_NOT_ALLOWED);
	}
	if ($fileSize > (FILE_SIZE*1048576)) {
		showError(MSG_FILE_SIZE);
	}
	$handle = fopen($file, "r");
		$content = fread($handle, filesize($file));
	fclose($handle);
	$content = chunk_split(base64_encode($content));		
	$name = basename(filter_var($fileName, FILTER_SANITIZE_STRING));

	$nmessage .= "--".$uid1."\r\n";
	$nmessage .= "Content-Type: application/octet-stream; name=\"".$name."\"\r\n";
	$nmessage .= "Content-Transfer-Encoding: base64\r\n";
	$nmessage .= "Content-Disposition: attachment; filename=\"".$name."\"\r\n\r\n";
	$nmessage .= $content."\r\n\r\n";
	unlink($file);
	return $nmessage;
}