<?php

	function generatePassword($length)
	{
	    $chars = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
	    $count = strlen($chars) - 1;

	    $password = array();
	    for ($i = 0; $i < $length; $i++) 
	    {
	        $n = rand(0, $count);
	        $password[] = $chars[$n];
	    }

    	return implode($password);
	}

	function sendMail($to, $subject, $message)
	{
		$headers = 'From: admin@n4sjamk.org' . "\r\n" .
    	'X-Mailer: PHP/' . phpversion();

		mail($to, $subject, $message, $headers);
	}

?>