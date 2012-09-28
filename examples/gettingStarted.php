<?php

	require("settings.php"); //Loads in the api keys
	
	require("../phpOSM.php");

	
	//Load phpOSM class
	$osm = new phpOSM(PHPOSM_APIID,PHPOSM_TOKEN);
	
	/* Set at cache folder */
	$osm->cache = "../cache/";

	/* 
	 * You need to authorise once to get the secret and userid that you should store for future use.  
	 * 
	 * After authorising, open OSM in your browser, go to the External Access page 
	 * (in the Account dropdown menu) to give this API access to bits of your account.  
	 * 
	 * If your API is being used by others, please tell them to do this!
	 */
	$user = $osm->authorise($user_email, $user_password);
	
	print_r($user);

	/*
	 * You should get back some thing like this:
	 * 	stdClass Object
	 * 	(
	 * 	    [secret] => 00000000000000000000000000000000
	 * 	    [userid] => 0
	 * 	)
	 * Once these are saved you can just start at this point with the above secret and userid
	 * This way you don't have to store any user email or passwords
	 */ 
		
	$osm->access($user->userid, $user->secret);
	
	/*
	 * Now to check that its works, we will output what roles your have
	 */
	
	$terms = $osm->getUserRoles();
	
	print_r($terms);
	
	

?>