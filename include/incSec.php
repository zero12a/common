<?php

function sec_decrypt($tmp){
	return $tmp;
}

function PKCS5Pad($text, $blocksize)
{
 $pad = $blocksize - (strlen($text) % $blocksize);
 return $text . str_repeat(chr($pad), $pad);
}



function PKCS5Unpad($text)
{
 //$pad = ord($text{strlen($text)-1}); //php 7.4
 $pad = ord($text[strlen($text)-1]); //php 8
 if ($pad > strlen($text)) return $text;
 if (!strspn($text, chr($pad), strlen($text) - $pad)) return $text;
 return substr($text, 0, -1 * $pad);
}


//Encrypt Function 
function aes_encrypt_old($encrypt,$key) { 
	$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB),strlen($key)); 
	$passcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $encrypt, MCRYPT_MODE_ECB, $iv); 
	$encode = base64_encode($passcrypt); 
	return $encode; 
} 


function pkcs5_pad($text, $blocksize = 16) {
	$pad = $blocksize - (strlen($text) % $blocksize);
	return $text . str_repeat(chr($pad), $pad);
}

function pkcs5_unpad($text) {
	//$pad = ord($text{strlen($text)-1}); // php 7.4
	$pad = ord($text[strlen($text)-1]); // php 8
	if ($pad > strlen($text)) {
	  return $text;
	}
	if (!strspn($text, chr($pad), strlen($text) - $pad)) {
	  return $text;
	}
	return substr($text, 0, -1 * $pad);
  }


//$ivlen = openssl_cipher_iv_length($cipher);
//$iv = openssl_random_pseudo_bytes($ivlen);
//echo "<br> iv = " . base64_encode($iv);

function aes_encrypt($tencrypt,$tkey) { 
	global $CFG;
	//CFG_SEC_IV;
	$cipher = "aes-256-cbc";
	//$ivlen = openssl_cipher_iv_length($cipher);
	//$iv = openssl_random_pseudo_bytes($ivlen);
	$iv = base64_decode($CFG["CFG_SEC_IV"]);
	//echo "\n<br> aes_decrypt.iv = " . $CFG_SEC_IV;	
	//echo "\n<br> tkey = " . $tkey;	

    return base64_encode(openssl_encrypt(pkcs5_pad($tencrypt), $cipher, $tkey, OPENSSL_RAW_DATA, $iv));
    //store $cipher, $iv, and $tag for decryption later
    //$original_plaintext = openssl_decrypt($ciphertext, $cipher, $key, $options=0, $iv, $tag);
	//echo $original_plaintext."\n";
}

function aesEncrypt($tencrypt,$tkey,$tiv) { 
	//CFG_SEC_IV;
	$cipher = "aes-256-cbc";
	//$ivlen = openssl_cipher_iv_length($cipher);
	//$iv = openssl_random_pseudo_bytes($ivlen);
	//$iv = base64_decode($CFG["CFG_SEC_IV"]);
	//echo "\n<br> aes_decrypt.iv = " . $CFG_SEC_IV;	
	//echo "\n<br> tkey = " . $tkey;	

    return base64_encode(openssl_encrypt(pkcs5_pad($tencrypt), $cipher, $tkey, OPENSSL_RAW_DATA, base64_decode($tiv)));
    //store $cipher, $iv, and $tag for decryption later
    //$original_plaintext = openssl_decrypt($ciphertext, $cipher, $key, $options=0, $iv, $tag);
	//echo $original_plaintext."\n";
}


//Encrypt Function 
function aes_encrypt_good($tencrypt,$tkey) { 

	
	$key = pack('H*', $tkey);
    
    # show key size use either 16, 24 or 32 byte keys for AES-128, 192, 256 
    $key_size =  strlen($key);
    //alog("	Key size: " . $key_size );
	
	$ciphertext_dec = base64_decode($ciphertext_base64);

	$block_size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);

    $plaintext = PKCS5Pad($tencrypt,$block_size);

    # create a random IV to use with CBC encoding
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    
    # creates a cipher text compatible with AES (Rijndael block size = 128)
    # to keep the text confidential 
    # only suitable for encoded input that never ends with value 00h
    # (because of default zero padding)
    $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key,
                                 $plaintext, MCRYPT_MODE_CBC, $iv);

    # prepend the IV for it to be available for decryption
    $ciphertext = $iv . $ciphertext;
    
    # encode the resulting cipher text so it can be represented by a string
    $ciphertext_base64 = base64_encode($ciphertext);

	//echo  $ciphertext_base64 . "\n";
	//alog( "	ciphertext_base64 : " . $ciphertext_base64 );

	return $ciphertext_base64; 
} 



  

//Decrypt Function 
function aes_decrypt_old($decrypt,$key) { 
	$decoded = base64_decode($decrypt); 
	$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB),strlen($key)); 
	$decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $decoded, MCRYPT_MODE_ECB, $iv); 
	return $decrypted; 
} 

function is_base64($str){
    if ( base64_encode(base64_decode($str, true)) === $str){
       return true;
    } else {
       return false;
    }
}

function aes_decrypt($decrypt,$tkey) {
	global $CFG;
	//CFG_SEC_IV;

	$cipher = "aes-256-cbc";
	//$ivlen = openssl_cipher_iv_length($cipher);
	//$iv = openssl_random_pseudo_bytes($ivlen);
	$iv = base64_decode($CFG["CFG_SEC_IV"]);	
	//echo "\n<br> aes_decrypt.iv = " . $CFG_SEC_IV;	
	//$ciphertext = openssl_encrypt($tencrypt, $cipher, $tkey, $options=0, $iv, $tag=null);
	//store $cipher, $iv, and $tag for decryption later
	//echo "\n<br> decrypt = " . $decrypt;	
	//echo "\n<br> tkey = " . $tkey;		
	//echo "\n<br> plaintext = " . pkcs5_unpad(openssl_decrypt(base64_decode($decrypt), $cipher, $tkey, OPENSSL_RAW_DATA, $iv));
	return pkcs5_unpad(openssl_decrypt(base64_decode($decrypt), $cipher, $tkey, OPENSSL_RAW_DATA, $iv));
}

function aesDecrypt($decrypt,$tkey,$iv) {
	//global $CFG;
	//CFG_SEC_IV;

	$cipher = "aes-256-cbc";
	//$ivlen = openssl_cipher_iv_length($cipher);
	//$iv = openssl_random_pseudo_bytes($ivlen);
	//$iv = base64_decode($CFG["CFG_SEC_IV"]);	
	//echo "\n<br> aes_decrypt.iv = " . $CFG_SEC_IV;	
	//$ciphertext = openssl_encrypt($tencrypt, $cipher, $tkey, $options=0, $iv, $tag=null);
	//store $cipher, $iv, and $tag for decryption later
	//echo "\n<br> decrypt = " . $decrypt;	
	//echo "\n<br> tkey = " . $tkey;		
	//echo "\n<br> plaintext = " . pkcs5_unpad(openssl_decrypt(base64_decode($decrypt), $cipher, $tkey, OPENSSL_RAW_DATA, $iv));
	return pkcs5_unpad(openssl_decrypt(base64_decode($decrypt), $cipher, $tkey, OPENSSL_RAW_DATA, base64_decode($iv)));
}


function aes_decrypt_good($decrypt,$tkey) {
	
	
	if(!is_base64($decrypt))return "";

	$ciphertext_base64 = $decrypt;
	$key = pack('H*', $tkey);

	$ciphertext_dec = base64_decode($ciphertext_base64);
		
	$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
	
	# retrieves the IV, iv_size should be created using mcrypt_get_iv_size()
	$iv_dec = substr($ciphertext_dec, 0, $iv_size);

	# retrieves the cipher text (everything except the $iv_size in the front)
	$ciphertext_dec = substr($ciphertext_dec, $iv_size);

	# may remove 00h valued characters from end of plain text
	$plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key,
									$ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);

	//alog(" plaintext_dec : " . $plaintext_dec);
	return PKCS5Unpad($plaintext_dec);
}



//Passwd Hash
function pwd_hash($plain_text,$key){
	return hash("sha512",$key.$plain_text);
}


//rand 초기화
function initRand()
{
   static $randCalled = FALSE;
   if (!$randCalled)
   {
       srand((double) microtime() * 1000000);
       $randCalled = TRUE;
   }
}
?>