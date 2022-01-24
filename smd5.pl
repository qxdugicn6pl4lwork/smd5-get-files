#! /usr/bin/perl
#
# This small script generates an Seeded MD5 hash of 'secret'
# (using the seed "salt") for use as a userPassword or rootpw value.
#
use Digest::MD5;
use MIME::Base64;
$ctx = Digest::MD5->new;
#$ctx->add('@CFtK6~>81HrsU*I,Aq');
$ctx->add('cBjSldx0vbNUwboVJKA0k0O77pZopre6WKP7ffp5');
$salt = 'salt';
$ctx->add($salt);
$hashedPasswd = '{SMD5}' . encode_base64($ctx->digest . $salt ,'');
print 'userPassword: ' .  $hashedPasswd . "\n";
