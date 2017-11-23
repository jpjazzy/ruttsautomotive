<?php

require_once('config.php');

$img_h = 50;//captcha height in pixels
$loc_char = 30; // how wide is the wides character
$nr_max_bg = 10; //how many backgrounds are there in the captcha folder
$nr_max_fonts = 18;

$ce_scriu=GeneratePassword(DIGITS,NRONLY);
setcookie('captcha',md5($ce_scriu.SALT.md5($_SERVER['SERVER_ADDR'].$_SERVER['SERVER_NAME']).round(time() / (COOKIE_VALID_MIN * 60)) * (COOKIE_VALID_MIN * 60)),0,'/');

$imgname = PATH."b".rand(0, $nr_max_bg).".gif";
$img_w = $loc_char*DIGITS; // imagine width

if (GDtest()){ // GD2
	$im_sour = imagecreatefromgif ($imgname); /* Attempt to open */ 
	$im_dest = imagecreate ($img_w, $img_h);
	
	imagecopy ( $im_dest, $im_sour, 0, 0, 0, 0, $img_w, $img_h );
	$im=$im_dest;
	$clr_black = imagecolorallocate($im, 0, 0, 0);
	for ($i=1; $i <= DIGITS; $i++) {
		$size = rand(22, 28);
		$font = PATH.rand(0,$nr_max_fonts).'.ttf';
		$wi=($i-1)*$loc_char+5;
		$hi=rand($size+5,$img_h-5);
		$angle = rand(-15, 15);
		imagefttext($im, $size, $angle, $wi, $hi, $clr_black, $font, $ce_scriu[$i-1]);
	}
	header('Content-type: image/png'); 
	imagepng ($im);
}


function GeneratePassword($digits=4,$number_only=false) {
	if ($number_only==1) $c=array("0","1","2","3","4","5","6","7","8","9");
	else $c=array("a","b","c","d","e","f","g","h","j","k","m","n","p","r","s","t","u","v","w","x","y","z","2","3","4","5","6","7","8","9");
	
	for ($i=1; $i<=$digits; $i++){		
		$char = $c[rand(0,count($c)-1)];
		if (!$number_only)
			$char = (rand(0,1) == 1) ? strtoupper($char) : $char;
		$pass .= $char;
	}
	return $pass;
}

//test if GD is enabled on the server
function GDtest() {
	if (function_exists('gd_info')) { 
		$x = @gd_info(); 
		$gdv = $x["GD Version"];
		$gdv = str_replace("bundled (","", $gdv);
		$gdv = str_replace(" compatible)","", $gdv);
		$gdv = str_replace(".","", $gdv);
		$gdv1= substr($gdv,0,1);
		$gdv2= substr($gdv,1,strlen($gdv));
		$gdv = $gdv1.".".$gdv2;
		if ($gdv >= 2) {  return 1; } else { return 0; }
	} else { 
		return 0; 
	}
}