<?php

/*
 	phpOSM
	A simple php class to connect to onlinescoutmanager.co.uk
 
*/

/*
	Licence

	Copyright (c) 2011 Blue Rhinos Consulting | Andrew Milsted
	andrew@bluerhinos.co.uk | http://www.bluerhinos.co.uk

	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in
	all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	THE SOFTWARE.
	
*/

define( "TIME_ONE_MINUTE", 60);
define( "TIME_ONE_HOUR", 3600);
define( "TIME_ONE_DAY", 86400);
define( "TIME_ONE_WEEK", 604800);
define( "TIME_ONE_MONTH", 2629743);
define( "TIME_ONE_YEAR", 31556926);

class phpOSM{
	
	private $url = "https://www.onlinescoutmanager.co.uk/";
	private $apiid;
	private $token;
	private $userid;
	private $secret;
	private $baseparam;
	
	public $cache = "./cache/";
	public $usecache = true;
	
	public $debug = false;
	
	public $terms;
	public $section;
	public $term;
	
	
	function __construct($apiid, $token){
		$this->register($apiid, $token);
	}
	
	function debug_msg($msg){
		if($this->debug)
			echo "DEBUG: {$msg}\n";
	}

	function register($apiid, $token){
		$this->apiid = $apiid;	
		$this->token = $token;
		$this->baseparam = array('apiid'=>$apiid,'token'=>$token);
	}
	
	function authorise($email, $password) {
	    $params['password'] = $password; 
	    $params['email'] = $email;
	    $user = $this->query('users.php?action=authorise', $params);
		if(!isset($user->userid) || !isset($user->secret)){
			die("Authorise unsuccessful for user {$email}\n");
		}else{
			$this->debug_msg("Authorise successful for user {$email}");
		}
		$this->access($user->userid,$user->secret);
		return $user;
	}
	
	function access($userid, $secret){
		$this->userid = $userid;
		$this->secret = $secret;
		$this->baseparam['userid'] = $userid;
		$this->baseparam['secret'] = $secret;
	}
	
	function query($url, $params = array(), $cachekey = NULL, $cachetime = 0) {
		
		$this->debug_msg("Starting Query {$url}");

		if(isset($cachekey)){
  			$cachefile = "{$this->cache}$cachekey";
			if($this->usecache && file_exists($cachefile)){
					$this->debug_msg("Cache file exists - {$cachefile}");
					$cachefileage = time() - filemtime($cachefile);
					$this->debug_msg("Cache is {$cachefileage}s old, Limit is $cachetime");
					if($cachetime > $cachefileage){
						$msg = file_get_contents($cachefile);
						return json_decode($msg); 
					}		
			}else{
					$this->debug_msg("Cache file does not exists - {$cachefile}");
			}
			
		}
	    $curl_handle = curl_init();
	    curl_setopt($curl_handle, CURLOPT_URL, $this->url.$url);
	    curl_setopt($curl_handle, CURLOPT_POSTFIELDS, http_build_query(array_merge($params,$this->baseparam)));
	    curl_setopt($curl_handle, CURLOPT_POST, 1);
	    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
	    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
	    $msg = curl_exec($curl_handle);


		if(isset($cachekey)){
			$this->debug_msg("Saving to cache - {$cachefile}");
			file_put_contents($cachefile,$msg);
		}
	    return json_decode($msg);    
	}
	
	function getUserRoles(){
		return $this->userRoles = $this->query('api.php?action=getUserRoles',array(),"getUserRoles", TIME_ONE_HOUR);
	}
	
	function getSections(){
		return $this->sections = $this->query('api.php?action=getSectionConfig',array(),"getSectionConfig", TIME_ONE_HOUR);	
	}
	
	
	function getTerms(){
		$this->terms = $this->query('api.php?action=getTerms',array(),"getTerms", TIME_ONE_HOUR);
		return $this->terms;
	}
	
	function getCurrentTerm($section){
		foreach($this->terms->{$section} as $term){
			$cterm = $term->termid;
			if(strtotime($term->startdate) < time() && strtotime($term->enddate) > time())
				return $cterm;
		}
		return $cterm;
	}

	// List of badges and the data I have in the database for them - some things won't be relevant for APIs
	//badgeType = challenge/staged/activity
	
	function getBadges($type = "challenge"){
		$this->badges[$type] = $this->query("challenges.php?action=getBadgeDetails&section=scouts&badgeType=$type", array()); 	
	}
	
	function getScoutDetails(){
		$this->scoutDetails = $this->query("users.php?action=getUserDetails&sectionid={$this->section}&termid={$this->term}",array(),"getUserDetails-{$this->section}-{$this->term}",TIME_ONE_HOUR);	
	
	}
	
	function getFlexiRecord($flexid){
		return $this->query("extras.php?action=getExtraRecords&extraid={$flexid}&sectionid={$this->section}&termid={$this->term}",array(),"getFlexiRecord-{$this->section}-{$this->term}-{$flexid}",5);
	}
	
	function getFlexiRecordDetails($flexid){
		return $this->query("extras.php?action=getExtra&sectionid={$this->section}&extraid={$flexid}",array(),"getFlexiRecordDetails-{$this->section}-{$this->term}-{$flexid}",5);
	}
	
	function sortScoutDetails($key){
		$this->_objectSortKey = $key;
		uasort($this->scoutDetails->items, array($this, '_objectSort')); 	
	}
	
	function _objectSort($a,$b){
		    return strcmp($a->{$this->_objectSortKey},$b->{$this->_objectSortKey});
	}
	
}



?>