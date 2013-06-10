# PHP DosDetector Class Documentation

Version: 1.0

Release: June 2013

Keyword: security, php, class, firewall, DoS Attack, IDS/IPS


## 1. What is this class?

This PHP Class used for preventing Denial of Service (DoS) attack to your web server written by PHP. Running this script will  monitoring all requests from an IP address and logged it into memory cache (PHP APC Caching). If an IP address sends too much request to your server, it will trigger the Intrustion Preventing System (IPS) and auto-ban this IP Address.


## 2. System Requirement

*   PHP 5.x
*   APC Cache ([Learn more..](http://en.wikipedia.org/wiki/List_of_PHP_accelerators#Alternative_PHP_Cache_.28APC.29))


## 3. Installation

- First, copy `class.dosdetector.php` file to your project, such as `./classes/` directory on your project.

- Next, including `class.docsdetector.php` file.

- Now, just create an object from this class and call `run` method before the first line of your project (usally in bootstrap, start up or index file) to start monitoring. You can pass an URL to run method in case banned IP will be redirect to passed URL. If you do not pass this parameter, banned IP will see a default message on screen. 

- Example code: 

<pre>

		//Put this in the beginning of your all page
		include_once('./classes/class.dosdetector.php');
		$myDosDetector = new DosDetector();

		//Default Running
		$myDosDetector->run();

		//Default Running with Custom Landing Page for Banned IP Access
		//$myDosDetector->run('http://url/to/your/landing/page');

		//////////////////////////////
		// YOUR SITE SOURCE CODE HERE
		//....

</pre>


## 4. Editable Properties & Constants

*   `$ignoreIpAddress`: IP Address in this array will be ignored by this detector. Usually your Company IP...
*   `PHPIDS_QUOTA_IDS_TRIGGER`: if in a second, an IP Address request more than this value will be trigger method idsWorker() in this class. You can implement you code for this function (line 161 in class.dosdetector.php) to get the notification.
*   `PHPIDS_DURATION_IPS_TRIGGER`: The number of second to check for IPS (auto-banning) trigger.
*   `PHPIDS_QUOTA_IPS_TRIGGER`: if in `PHPIDS_DURATION_IPS_TRIGGER` seconds, same IP have more request than this value will be auto-banned by system.
			</div>
			<div class="section">

## 5. Monitoring

- Only in Codecanyon Package. Buy on Codecanyon to get <code>monitor.php</code> script to monitoring. [http://codecanyon.net/item/php-dosdetector-class/4899130](http://codecanyon.net/item/php-dosdetector-class/4899130)

- This package came with a standalone script to monitor the traffic (logged by DosDtector class). You can put this script anywhere on your web server (with PHP read permission), and run this script from browser to access monitor tool. Example: http://yoursite.com/monitor.php.

- This page will show all the request (with IP Address, Time, User-Agent, Cookie status, Request URI, Referer URL) logged by DosDetector. Logged Accesses will be cached for 2 hours for performance.

- This page will show you banned IP address (from auto-ban or manual-ban IP Address). You can manual-ban an IP Address if you see that IP request too much and have weird access. Banned IP addresses will not be clear automatically. This banned list only clear by you or by clear from APC cache.

- You can detect whether a request have cookie or not, because a request without cookie is usually a search engine robot (Googlebot,..) or an automatic script crawling/flooding your website.


## Thank you!

- Support me by buying at (http://codecanyon.net/item/php-dosdetector-class/4899130)
