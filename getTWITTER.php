#!/usr/bin/php
<?
//error_reporting(0);

$pluginName ="Twitter";
$myPid = getmypid();

$messageQueue_Plugin = "FPP-Plugin-MessageQueue"; // NBP 2/2/2020 update to match plugin directory name
$MESSAGE_QUEUE_PLUGIN_ENABLED=false;

$DEBUG=false;

$skipJSsettings = 1;
include_once("/opt/fpp/www/config.php");
include_once("/opt/fpp/www/common.php");
include_once("functions.inc.php");
require("TwitterAPIExchange.php");
require ("lock.helper.php");

define('LOCK_DIR', '/tmp/');
define('LOCK_SUFFIX', $pluginName.'.lock');

$logFile = $settings['logDirectory']."/".$pluginName.".log";

$messageQueuePluginPath = $pluginDirectory."/".$messageQueue_Plugin."/";

$messageQueueFile = urldecode(ReadSettingFromFile("MESSAGE_FILE",$messageQueue_Plugin));

if(file_exists($messageQueuePluginPath."functions.inc.php"))
	{
		include $messageQueuePluginPath."functions.inc.php";
		$MESSAGE_QUEUE_PLUGIN_ENABLED=true;

	} else {
		logEntry("Message Queue Plugin not installed, some features will be disabled");
	}	



$ENABLED = urldecode(ReadSettingFromFile("ENABLED",$pluginName));


if(($pid = lockHelper::lock()) === FALSE) {
	exit(0);

}
//arg0 is  the program
//arg1 is the first argument in the registration this will be --list
//$DEBUG=true;
//echo "Enabled: ".$ENABLED."<br/> \n";


if($ENABLED != "ON" && $ENABLED != "1") {
	logEntry("Plugin Status: DISABLED Please enable in Plugin Setup to use & Restart FPPD Daemon");
	lockHelper::unlock();
	exit(0);
}




$SEPARATOR = urldecode(ReadSettingFromFile("SEPARATOR",$pluginName));
$USER = urldecode(ReadSettingFromFile("USER",$pluginName));
$OAUTH_ACCESS_TOKEN = urldecode(ReadSettingFromFile("oauth_access_token",$pluginName));
$OAUTH_ACCESS_TOKEN_SECRET = urldecode(ReadSettingFromFile("oauth_access_token_secret",$pluginName));
$CONSUMER_KEY = urldecode(ReadSettingFromFile("consumer_key",$pluginName));
$CONSUMER_SECRET = urldecode(ReadSettingFromFile("consumer_secret",$pluginName));
$TWITTER_LAST_INDEX =ReadSettingFromFile("TWITTER_LAST",$pluginName);

$lastRead = $TWITTER_LAST_INDEX;
//logEntry("connecting to twitter:");

logEntry("Twitter last index: ".$lastRead);
/** Set access tokens here - see: https://dev.twitter.com/apps/ **/
$twitterSettings = array(
'oauth_access_token' => $OAUTH_ACCESS_TOKEN,
'oauth_access_token_secret' => $OAUTH_ACCESS_TOKEN_SECRET,
'consumer_key' => $CONSUMER_KEY,
'consumer_secret' => $CONSUMER_SECRET
);


$url = "https://api.twitter.com/1.1/statuses/user_timeline.json";
//$url = "https://api.twitter.com/1.1/search/tweets.json";

//$url="https://api.twitter.com/1.1/search/tweets.json";
$requestMethod = "GET";
//if (isset($_GET['user'])) {$user = $_GET['user'];} else {$user = "iagdotme";}

$USER = trim($USER);

if(substr($USER,0,1) != "@") {
	$user ="@".$USER;
	
} else  {
	
	$user = $USER;
}
//if (isset($_GET['count'])) {$user = $_GET['count'];} else {$count = 20;}
$count=100;

//
$include_entities="0";


	logEntry("last read index: ".$lastRead);
	
	if((int)$lastRead >0 )
	{
		$org_lastRead = $lastRead;
	} else {
		$lastRead ="";
	}


$API_TYPE="TWEETS";
//$API_TYPE = "TIMELINE";
//$getfield = "?q=".$user."&since_id=".$lastRead."&include_entities=".$include_entities;

//echo "lastREad: ".$lastRead."\n";
switch ($API_TYPE) {
	
	case "TIMELINE":
		if($lastRead !=0 || $lastRead != "") {
			
		
			$getfield = "?screen_name=".$user."&since_id=".$lastRead;
		} else {
			$getfield = "?screen_name=".$user;
		}
		break;
		
	case "TWEETS":
		if($lastRead !=0 || $lastRead != "") {
				
			$getfield = "?q=".$user."&since_id=".$lastRead."&include_entities=".$include_entities;
		} else {
			$getfield = "?q=".$user."&include_entities=".$include_entities;
		}
		break;
}

logEntry("Twitter search: ".$getfield);

//echo "API TYPE: ".$API_TYPE."\n";
//echo "url: ".$url."\n";

//echo "GetField: ".$getfield."\n";

$twitter = new TwitterAPIExchange($twitterSettings);
$string = json_decode($twitter->setGetfield($getfield)
->buildOauth($url, $requestMethod)
->performRequest(),$assoc = TRUE);
//if($string["errors"][0]["message"] != "") { // NBP -- PHP notice, undefined index: errors (?) -- hack comment out to avoid
//  LogEntry("Twitter Error:Sorry, there was a problem. Twitter returned the following error message:".$string[errors][0]["message"]);
//  lockHelper::unlock();
//  exit(0);
//}
//print_r($string);


switch ($API_TYPE) {
	
	case "TWEETS":
		//$tweetCount = count($string['statuses']);
		$tweetCount = count($string);
		break;
		
	case "TIMELINE":
		$tweetCount = count($string);
		break;
}


//echo "message count: ".$tweetCount."\n";
$tweetIndex=0;

for($tweetIndex=0;$tweetIndex<=$tweetCount-1;$tweetIndex++) {
	

	switch ($API_TYPE) {
		
		case "TWEETS":
			//$fromUser = $string['statuses'][$tweetIndex]['user']['screen_name'];
			//$fromUser = $string['statuses'][$tweetIndex];
			
			//print_r($fromUser);
			
			
			//$tweetText = $string['statuses'][$tweetIndex]['text'];
			
			$tweetText = $string[$tweetIndex]['text'];
			$fromUser = $string[$tweetIndex]['user']['screen_name'];
			
			break;
			
		case "TIMELINE":
			
			$tweetText = $string[$tweetIndex]['text'];
			$fromUser = $string[$tweetIndex]['user']['screen_name'];
			
			break;
	}
	
	
	
	$tweetText = preg_replace('/'.$user.'/', '', $tweetText);
	//echo "Tweet from: ".$fromUser." ".$tweetText."\n";
	$messageText = urlencode($tweetText);
	
	//write the index of the message as the PLUGIN DATA as you need this to search!
	
	addNewMessage($messageText,$pluginName,$string[$tweetIndex]['id_str']);
	
	//echo "sleeping to test lock";
	//sleep(30);
	
	
}

    //get the max ID and writ it as last read! :)
    
    //in the case of timeline, it's the FIRST returned index. as they are returned in reverse/latest order

    //update the last index if the count > 0
    if($tweetCount>0 ) {
        switch ($API_TYPE) { // NBP 2/2/2020 -- moved switch inside if($tweetCount>0) -- fix PHP notice

            case "TWEETS":
                //$lastRead = $string['search_metadata']['max_id_str'];
                $lastRead = $string[0]['id_str'];
                break;

            case "TIMELINE":
                $lastRead = $string[0]['id_str'];
                break;

        }
        WriteSettingToFile("TWITTER_LAST",$lastRead,$pluginName);
        logEntry("Writing Twitter last index: ".$lastRead);
    }
   
    // echo "Last read: ".$lastRead."\n";
  
    lockHelper::unlock();
?>
