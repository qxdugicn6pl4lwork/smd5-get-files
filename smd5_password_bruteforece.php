<?php
// http://www.php.net/manual/de/function.crypt.php#58805
// fixed by Joerg Neikes 22.04.2013 and added example + smd5.pl 

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

$starthtml = <<<starthtml
<html>
<body>
starthtml;

$endhtml = <<<endhtml
</body>
</html>
endhtml;

// if (!empty($username) && !empty($userpasswd)) {
if (!empty($username)) {
// test passwords in .secure file, forget the user
header( "Location: login.html" );
} else {


// $username='user';  // debug
// $userpasswd='secret'; // debug
$username=addslashes($_POST['username']);
$userpasswd=addslashes($_POST['userpasswd']);
$passwdFile='.secure';


// echo $username;
// echo $userpasswd;

$users=file($passwdFile);


if (!$user=preg_grep("/^$username/",$users))
{
    echo "User '$username' not found!";
}
else
{
    list(,$passwdInFile)=explode(':',array_pop($user));
	if (preg_match ("/{SMD5}/i", $passwdInFile)) {
	$encrypted = substr($passwdInFile, 6);
	$hash = base64_decode($encrypted);
	$salt = substr($hash,16);
	$mhashed =  mhash(MHASH_MD5, $userpasswd . $salt) ;
	$without_salt = explode($salt,$hash);
	  if ($without_salt[0] == $mhashed) {
	  session_start();
	  echo session_id();
	  // session_register("myusername");
	  // session_register("mypassword");
	   $_SESSION['username'] = $username;
// 	   $_SESSION['userpasswd'] = $$userpasswd;
// 	   header("location:login_success.php");
	  if(isset($_SESSION['username']))  {
	  //  header("location:login.html");
	  echo $starthtml;
// 	  echo "$username"; //debug
// 	  echo "$userpasswd"; // debug
	  echo "Password verified and logged in. <br />";
	  echo $endhtml;
	  }
	  if(!isset($_SESSION['username']))  {
// 	  echo "Session not set <br /> "; // debug
	  header("location:login.html");
	  }
	  } else {
	  echo $starthtml;
//	  echo "$username"; // debug
//	  echo "$userpasswd"; // debug
	  echo "Please use username and password to login!<br />";
	  echo $endhtml;
	  }
	  }
}
 }
?>
