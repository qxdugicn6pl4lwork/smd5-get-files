<?php
// error_reporting(E_ALL);
// ini_set('display_errors', TRUE);
// http://www.php.net/manual/de/function.crypt.php#58805
// fixed by Joerg Neikes 24.04.2013 and added example + smd5.pl 

// Added filebase check

/*
 // To test use smd5.pl
 // http://www.openldap.org/faq/data/cache/418.html
	#! /usr/bin/perl
	#
	# This small script generates an Seeded MD5 hash of 'secret'
	# (using the seed "salt") for use as a userPassword or rootpw value.
	#
	use Digest::MD5;
	use MIME::Base64;
	$ctx = Digest::MD5->new;
	$ctx->add('secret');
	$salt = 'salt';
	$ctx->add($salt);
	$hashedPasswd = '{SMD5}' . encode_base64($ctx->digest . $salt ,'');
	print 'userPassword: ' .  $hashedPasswd . "\n";
 

*/

/*
Please set up a .htaccess file with the files to be not accessable directly:

.htaccess
DirectoryIndex smd5.php
RewriteRule .*\.(svg|m4a|mpg|mpeg|png|txt|jpg|jpeg|gif|png|bmp|flv|avi|doc|docx|pl)$ - [F,NC]
SSLOptions +StrictRequire
SSLRequireSSL

*/

/*

fail2ban rules
[Wed Apr 24 10:00:00 2013] [error] [client 10.0.0.1] smd5 login Error! Username: user Password: xyz, referer: https://sub.domain.tld/smd5/login.html
 */


// from Wicked cool PHP 2008 of O'Reilly ISBN 978-1-59327-173-2 page 43
// changed by Jörg Neikes
function make_random_chars($num_chars) {
  if ((is_numeric($num_chars)) && ($num_chars > 0) && (! is_null($num_chars))) {
  	// set blank value
	$randomchars = '';
	$accepted_chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789^°!"§$%&/()=?`´#+~*\}]|[{,;.:-_@¹²³¼½<>';
	// Seed the generator if necessarry.
//	srand(((int)((double)microtime()*1000000)) );
	mt_srand(hexdec(substr(md5(microtime()), -8)) & 0x7fffffff);
//	mt_srand(crc32(microtime()));
	for ($i = 0; $i < $num_chars; $i++) {
		$number = mt_rand(0, (strlen($accepted_chars) -1));
 		$realrand = substr($accepted_chars, $number, 1);
		$randomchars = $randomchars . $realrand ;
	}
	return $randomchars;
  }
}

// Usage
$createdsessionsecret = make_random_chars(40); // $num_chars is parapeter in ()
// echo "Your random session secret is: $createdsessionsecret";

function html2txt($document){ 
$search = array('@<script[^>]*?>.*?</script>@si',	// Strip out javascript 
		'@<[\/\!]*?[^<>]*?>@si',		// Strip out HTML tags 
		'@<style[^>]*?>.*?</style>@siU',	// Strip style tags properly 
		'@<![\s\S]*?--[ \t\n\r]*>@' );		// Strip multi-line comments including CDATA 
$text = preg_replace($search, '', $document); 
return $text; 
} 

$starthtml = <<<starthtml
<html>
<body>
starthtml;

$endhtml = <<<endhtml
</body>
</html>
endhtml;

$formsnotfilled = <<<formsnotfilled
<div align="center">
<br />
<b><u>Error</u></b>
<br />
<br />
All forms must be filled!
<br />
<br />
We send you back to login.
<br />
</div>
formsnotfilled;

$illegalchars = <<<illegalchars
<div align="center">
<br />
<b><u>Error</u></b>
<br />
<br />
You have filled in illegal chars!
<br />
<br />
We send you back to login.
<br />
</div>
illegalchars;

$logindiv = <<<logindiv
<div align="center">
<br />
<b><u>Error</u></b>
<br />
<br />
Your <b>password</b> or <b>user</b> was <b>wrong</b>.
<br />
<br />
We send you back to login.
<br />
</div>
logindiv;

$filenotfound = <<<filenotfound
<div align="center">
<br />
<b><u>Error!</u></b>
<br />
<br />
The file you requested was <b><u>not</u></b> found!
<br />
<br />
We send you back to login.
<br />
</div>
filenotfound;



// $username='user';  // debug
// $userpasswd='secret'; // debug
// Password  @CFtK6~>81HrsU*I,Aq
$username=addslashes($_POST['username']);
$userpasswd=addslashes($_POST['userpasswd']);
$passwdFile='.secure';

// Check if the username is not set 
if (empty($username) or empty($userpasswd)) {
	  // error_log("smd5 login Error! Username: $username Password: $userpasswd", 0);
	  // error_log("smd5 login Error! Username: $username", 0);
	  ob_implicit_flush(true);
	  // send error for resend to login.html
 	  $buffer = str_repeat(" ", 4096);
	  echo $starthtml;
	  echo $formsnotfilled;
	  echo $endhtml;
 	  echo $buffer;
	  ob_flush();
	  sleep (5);
	  exit('<meta http-equiv="refresh" content="0; url=login.html"/>');
} 


if (!preg_match('/^[a-z\d_]{4,20}$/i', $username)) {
	ob_implicit_flush(true);
	// send error for resend to login.html
	$buffer = str_repeat(" ", 4096);
	echo $starthtml;
	echo $illegalchars;
	echo $endhtml;
	echo $buffer;
	ob_flush();
	sleep (5);
	exit('<meta http-equiv="refresh" content="0; url=login.html"/>');
	}



// check if user exists
$users=file($passwdFile);
if (!$user=preg_grep("/^$username/",$users))
{
	  error_log("smd5 login Error! Username: $username Password: $userpasswd", 0);
	  // error_log("smd5 login Error! Username: $username", 0);
	  ob_implicit_flush(true);
	  // send error for resend to login.html
 	  $buffer = str_repeat(" ", 4096);
	  echo $starthtml;
	  echo $logindiv;
	  echo $endhtml;
 	  echo $buffer;
	  ob_flush();
	  sleep (5);
	  exit('<meta http-equiv="refresh" content="0; url=login.html"/>');
}
else
{
	html2txt($username); // change all to plaintext 
	html2txt($userpasswd); // change all to plaintext
// check smd5 password
    list(,$passwdInFile)=explode(':',array_pop($user));
	if (preg_match ("/{SMD5}/i", $passwdInFile)) {
	$encrypted = substr($passwdInFile, 6);
	$hash = base64_decode($encrypted);
	$salt = substr($hash,16);
	$mhashed =  mhash(MHASH_MD5, $userpasswd . $salt) ;
	$without_salt = explode($salt,$hash);
	  if ($without_salt[0] == $mhashed) {
	   // make secure sessions
	   if(session_id() == '') { // session
	  $setime=30;
	  session_start();
	  session_regenerate_id();
	  setcookie(session_name(),session_id(),time()+$setime);
	  $_SESSION['XjmNFMndVu52xyVcQcfznDT2TFxuvI7xTkSFpbgi'] = $createdsessionsecret ;
//	  echo session_id(); // debug
// //	   $_SESSION['username'] = $username; // do not use sensible data
// // 	   $_SESSION['userpasswd'] = $userpasswd;  // do not use sensible data
// 	  echo "$username"; //debug
// 	  echo "$userpasswd"; // debug
	  
	  // If Session is set load file
//	  if(isset($_SESSION['username']))  { // bad practice, wen don't need the username in cookies
	  if(isset($_SESSION['XjmNFMndVu52xyVcQcfznDT2TFxuvI7xTkSFpbgi']))  { 
	  $file = 'file.txt';
	  if (file_exists($file)) {
	  // test if file exists
//	  $file = =$_GET['file']; // for use the GET variable
	  $ftype = substr($file, -4); // Set the Content-type by ending
	  switch ($ftype) {
	  // switch filetypes
	  // gif
	  case '.gif':
	  $contenttype = 'image/gif';
	  break;
	  // jpg
	  case '.jpg':
	  $contenttype = 'image/jpeg';
	  break;
	  case 'jpeg':
	  $contenttype = 'image/jpeg';
	  break;
	  // png
	  case '.png':
	  $contenttype = 'image/png';
	  break;
	  case '.bmp':
	  $contenttype = 'image/bmp';
	  break;
	  // svg
	  case '.svg':
	  $contenttype = 'image/svg';
	  break;
	  // MP3
	  case '.mp3':
	  $contenttype = 'audio/mpeg3'; 
	  break;
	  // wav
	  case '.wav':
	  $contenttype = 'audio/wav';
	  break;
	  // m4a
	  case '.m4a':
	  $contenttype = 'audio/m4a';
	  break;
	  // mpg
	  case '.mpg':
	  $contenttype = 'video/mpeg';
	  break;
	  case 'mpeg':
	  $contenttype = 'video/mpeg';
	  break;
	  // avi
	  case '.avi':
	  $contenttype = 'video/avi';
	  break;
	  // flv
	  case '.flv';
	  $contenttype = 'video/flv';
	  break;
	  // docx
	  case '.doc':
	  $contenttype = 'application/msword';
	  break;
	  case 'docx':
	  $contenttype = 'application/msword';
	  break;
	  // pdf
	  case '.pdf':
	  $contenttype = 'application/pdf';
	  break;
	  // rtf
	  case '.rtf':
	  $contenttype = 'application/rtf';
	  break;
	  // txt
	  case '.txt':
	  $contenttype = 'application/txt';
	  break;
}
// switch filetypes
// show file with php
//	  echo $_SESSION['XjmNFMndVu52xyVcQcfznDT2TFxuvI7xTkSFpbgi']; // debug
	  header("Cache-Control: private");
	  header("Pragma: no-cache");
	  header("Expires: 0");
 	  header("Content-Description: File Transfer");
	  header("Content-type:" . $contenttype);
	  header("Content-Disposition: Attachment; filename=$file");
	  header( "Content-Length: " . filesize($file) );
 	  header("Content-Transfer-Encoding: binary");
	  flush(); 
//  	  readfile($file); // when using big files don't use readfile
	  $fp = fopen($file, "r");
	  while (!feof($fp))
	  {
	    echo fread($fp, 65536);
	    flush(); // this is essential for large downloads
	  } 
	  fclose($fp);
	  ob_start();
	  session_destroy();
//	  unset( $_SESSION['userName']);
//	  unset( $_SESSION['userPasswd']);
	  ob_end_flush();
	  }
	  else {
	  // test if file exists
	  // If the file is not found write error
	  error_log("smd5 file Error! file $file not found!", 0);
	  // error_log("smd5 login Error! Username: $username", 0);
	  ob_implicit_flush(true);
	  // send error for resend to login.html
	  $buffer = str_repeat(" ", 4096);
	  echo $starthtml;
	  echo $filenotfound;
	  echo $endhtml;
	  echo $buffer;
	  ob_flush();
	  sleep (5);
	  exit('<meta http-equiv="refresh" content="0; url=login.html"/>');
	  }
	  }
	  }
	  } else {
	  // If wrong password is set write error
	  error_log("smd5 login Error! Username: $username Password: $userpasswd", 0);
	  // error_log("smd5 login Error! Username: $username", 0);
	  ob_implicit_flush(true);
	  // send error for resend to login.html
 	  $buffer = str_repeat(" ", 4096);
	  echo $starthtml;
	  echo $logindiv;
	  echo $endhtml;
 	  echo $buffer;
	  ob_flush();
	  sleep (5);
	  exit('<meta http-equiv="refresh" content="0; url=login.html"/>');
	  }
	  }

	  if(!isset($_SESSION['XjmNFMndVu52xyVcQcfznDT2TFxuvI7xTkSFpbgi']))  {
// 	  echo "Session not set <br /> "; // debug

	  ob_implicit_flush(true);
	  // send error for resend to login.html
 	  $buffer = str_repeat(" ", 4096);
	  echo $starthtml;
	  echo $logindiv;
	  echo $endhtml;
 	  echo $buffer;
	  ob_flush();
	  sleep (5);
	  exit('<meta http-equiv="refresh" content="0; url=login.html"/>');
	  }
}
?>
