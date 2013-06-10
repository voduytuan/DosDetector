<?php

	//Put this in the beginning of your all page
	include_once('class.dosdetector.php');
	$myDosDetector = new DosDetector();
	
	//Default Running
	$myDosDetector->run();
	
	//Default Running with Custom Landing Page for Banned IP Access
	//$myDosDetector->run('http://url/to/your/landing/page');
	
	
	//////////////////////////////
	// YOUR SITE SOURCE CODE HERE
	//....
	
	