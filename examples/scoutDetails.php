<?php
	
	/* This will expose private details, so don't leave exposed on a webserver */

	require("settings.php"); //Loads in the api keys
	
	require("../phpOSM.php"); //Load in the phpOSM class

	//Load phpOSM class
	$osm = new phpOSM(PHPOSM_APIID,PHPOSM_TOKEN);
	
	/* Set at cache folder */
	$osm->cache = "../cache/";

	$osm->access($user->userid, $user->secret);
	
	$osm->getTerms(); // You need to get a list of terms
	
	$osm->section = 40; //You need to select a section

	$osm->term = $osm->getCurrentTerm($osm->section); //you now need to select the current term
	
	$osm->getScoutDetails(); //Grep the details
	
	$osm->sortScoutDetails("lastname"); //You can sort by any field

	print_r($osm->scoutDetails);

?>