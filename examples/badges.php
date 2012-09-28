<?php

	require("settings.php"); //Loads in the api keys
	
	require("../phpOSM.php"); //Load in the phpOSM class

	
	//Load phpOSM class
	$osm = new phpOSM(PHPOSM_APIID,PHPOSM_TOKEN);
	
	/* Set at cache folder */
	$osm->cache = "../cache/";
	$osm->debug = true;
	$osm->usecache = false;
	
	$osm->access($user->userid, $user->secret);
	
	//section: beavers, cubs, scouts, explorers
	//badge: challenge, staged, activity,core
	
	$badges = $osm->getBadges("scouts","core");

	print_r($badges);
?>