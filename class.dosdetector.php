<?php

date_default_timezone_set('Asia/Ho_Chi_Minh');

class DosDetector
{
	//IP Address will ignore the process of IDS. Ex: IP of your company, google bot, yahoo bot...
	public $ignoreIpAddress = array();
	
	//Access information will be store in this cacher, using this key
	const PHPIDS_ACCESS_KEY = 'phpids_access_key';
	
	//IPS banned IP address will be store in this cacher, using this key
	const PHPIDS_BANNEDIP_KEY = 'phpids_bannedip_key';
	
	//If this ipaddress access in this timestamp over this number
	//The IDS system will alert, base on the alert type, the administrator
	//will receive the notification
	const PHPIDS_QUOTA_IDS_TRIGGER = 20;
	const PHPIDS_QUOTA_IPS_TRIGGER = 200;
	
	// In this duration (second) from current access, if current IP access PHPIDS_QUOTA_IPS_TRIGGER times, this IP will be banned automatically.
	const PHPIDS_DURATION_IPS_TRIGGER = 5;
	
	const PHPIDS_DEFAULT_BANNED_MSG = 'You can not access this URL. Contact administrator to get more information.';
	
	function __construct()
	{
		
	}
	
	function run($landingpage = '')
	{
		//////////////////////////////////
		//KEY SUFFIX for cache rotation
		// Cache Key rotation in each hour ^^. each means, in one day, there is 24 key for each hour.
		// But, because the expire of key is 3600 (1 hour), so, there are max 2 key in mean time (because old keys will be deleted because cache expireds ^^)
		$dateInfo = getdate();
		$suffix = $dateInfo['hours'];
		
		//detect apc installed
		if($this->isApcEnable())
		{
			//access detail
			$curTime = time();
			$ipaddress = $this->getIpAddress();
			$havecookie = !empty($_COOKIE) ? 1 : 0;
			$useragent = strip_tags(substr($_SERVER['HTTP_USER_AGENT'], 0, 100));
			$uri = substr($_SERVER["REQUEST_URI"], 0, 100);
			$referer = isset($_SERVER['HTTP_REFERER']) ? substr($_SERVER["HTTP_REFERER"], 0, 100) : '';
			
			//Ignore Bot access
			if(!in_array($ipaddress, $this->ignoreIpAddress))
			{
				//////////////////////////
				//////////////////////////
				// get banned IP
				$bannedIpAddress = apc_fetch(self::PHPIDS_BANNEDIP_KEY);
				if(!$bannedIpAddress)
					$bannedIpAddress = array();
				else
				{
					if(in_array($ipaddress, $bannedIpAddress))
					{
						//YOU ARE BANNED;
						
						if($landingpage != '')
							header('location: ' . $landingpage);
						else
							die(self::PHPIDS_DEFAULT_BANNED_MSG);
					}
				}


				//thong tin access trong ngay
				$accessList = apc_fetch(self::PHPIDS_ACCESS_KEY . $suffix);

				//Update data
				//If not found this access before, create
				if(!$accessList)
				{
					$accessList = array($ipaddress => array($curTime => array(1, $havecookie, $useragent, $uri, $referer)));
				}
				else
				{
					//Neu may da truy cap
					if(isset($accessList[$ipaddress]))
					{
						//Vo cung khung thoi gian
						if(isset($accessList[$ipaddress][$curTime]))
						{
							$accessList[$ipaddress][$curTime][0]++;
						}
						else //vo o timestamp khac
						{
							$accessList[$ipaddress][$curTime] = array(1, $havecookie, $useragent, $uri, $referer);
						}
						
						
						/////////////////////
						/////////////////////////
						// IDS trigger
						if($accessList[$ipaddress][$curTime] > self::PHPIDS_QUOTA_IDS_TRIGGER)
						{
							$this->idsWorker();
						}

						///////////////////////
						/////////////////////////
						// IPS Preventation
						$rangeToCheck = self::PHPIDS_DURATION_IPS_TRIGGER;	//seconds
						$totalInPastRange = 0;
						foreach($accessList[$ipaddress] as $timestamp => $info)
						{
							//valid range to get SUM
							if($curTime - $timestamp < $rangeToCheck || $curTime == $timestamp)
							{
								$totalInPastRange += $info[0];
							}
						}

						//trigger IPS
						if($totalInPastRange > self::PHPIDS_QUOTA_IPS_TRIGGER)
						{
							//IPS Start
							//add to IPS
							if(!in_array($ipaddress, $bannedIpAddress))
							{
								$bannedIpAddress[] = $ipaddress;
								apc_store(self::PHPIDS_BANNEDIP_KEY, $bannedIpAddress);
							}
						}
						
					}
					else
					{
						//Neu chua truy cap, thi them vao danh sach da truy cap
						$accessList[$ipaddress] = array($curTime => array(1, $havecookie, $useragent, $uri, $referer));
					}
				} //end check key
				
				//Store the access info
				apc_store(self::PHPIDS_ACCESS_KEY . $suffix, $accessList, 3600);

				
			}//end check ignoreIpAddress
			
			
			
			
		}
		else
		{
			//APC not found.hehe.
		}
	}
	
	/*
	 * This function will be called if the IDS trigger
	 */
	private function idsWorker()
	{
		
	}
	
	public function isApcEnable()
	{
		return extension_loaded('apc') && ini_get('apc.enabled');
	}
	
	public function getAccessList()
	{
		$accessList = array();
		if($this->isApcEnable())
		{
			for($i = 0; $i < 24; $i++)
			{
				$listInHour = apc_fetch(self::PHPIDS_ACCESS_KEY . $i);
				if(!empty($listInHour))
					$accessList = array_merge($accessList, $listInHour);
			}
		}
		
		return $accessList;
	}
	
	public function getBannedIpList()
	{
		$bannedIpList = array();
		if($this->isApcEnable())
		{
			$list = apc_fetch(self::PHPIDS_BANNEDIP_KEY);
			if(!empty($list))
				$bannedIpList = $list;
		}
		
		return $bannedIpList;
	}
	
	public function banipInsert($ipaddress)
	{
		if($this->isApcEnable())
		{
			$list = apc_fetch(self::PHPIDS_BANNEDIP_KEY);
			
			if(!empty($list) && !in_array($ipaddress, $list))
				$list[] = $ipaddress;
			elseif(empty($list))
				$list = array($ipaddress);
				
			return apc_store(self::PHPIDS_BANNEDIP_KEY, $list);
		}
		else
			return false;
	}
	
	public function banipRemove($ipaddress)
	{
		if($this->isApcEnable())
		{
			$list = apc_fetch(self::PHPIDS_BANNEDIP_KEY);
			
			if(empty($list))
				return false;
			else
			{
				$newlist = array();
				
				foreach($list as $ip)
				{
					if($ip != $ipaddress)
						$newlist[] = $ip;
				}
				
				return apc_store(self::PHPIDS_BANNEDIP_KEY, $newlist);
			}
		}
		else
			return false;
	}
	
	/**
	 * Get IP Address of current Access
	 */
	public function getIpAddress()
	{
		$ip = '';

		if($_SERVER) 
		{
			if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			{
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
			elseif(isset($_SERVER['HTTP_CLIENT_IP']))
			{
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			}
			else
			{
				$ip = $_SERVER['REMOTE_ADDR'];
			}
		} 
		else 
		{
			if(getenv('HTTP_X_FORWARDED_FOR'))
			{
				$ip = getenv('HTTP_X_FORWARDED_FOR');
			}
			elseif(getenv('HTTP_CLIENT_IP'))
			{
				$ip = getenv('HTTP_CLIENT_IP');
			}
			else
			{
				$ip = getenv('remote_addr');
			}
		}
		
		return $ip;
	}
	
	
}
?>