#!/usr/bin/php
<?
//error_reporting(0);

$pluginName ="Twitter-Matrix";
$myPid = getmypid();

$messageQueue_Plugin = "MessageQueue";
$MESSAGE_QUEUE_PLUGIN_ENABLED=false;

$DEBUG=false;

$skipJSsettings = 1;
include_once("/opt/fpp/www/config.php");
include_once("/opt/fpp/www/common.php");
include_once("functions.inc.php");

$logFile = $settings['logDirectory']."/".$pluginName.".log";

$messageQueuePluginPath = $pluginDirectory."/".$messageQueue_Plugin."/";

$messageQueueFile = urldecode(ReadSettingFromFile("MESSAGE_FILE",$messageQueue_Plugin));

$matrixFIFO = "/home/pi/matrix";
//time between messages before sending clear line

$MESSAGE_TIMEOUT = 10;

//get the options on the command line
$MATRIX_PLUGIN_OPTIONS = $argv[1];
$MATRIX_MESSAGE_TIMEOUT = $argv[2];

if($MATRIX_MESSAGE_TIMEOUT == "" || $MATRIX_MESSAGE_TIMEOUT == null) {
	$MESSAGE_TIMEOUT = 10;
	
} else {
	$MESSAGE_TIMEOUT = (int)trim($MATRIX_MESSAGE_TIMEOUT);
}

if($MATRIX_PLUGIN_OPTIONS == "" || $MATRIX_PLUGIN_OPTIONS == null) {
	echo "Must supply options on command line";

}

//echo $messageQueueFile."\n";

if(file_exists($messageQueuePluginPath."functions.inc.php"))
        {
                include $messageQueuePluginPath."functions.inc.php";
                $MESSAGE_QUEUE_PLUGIN_ENABLED=true;

        } else {
                logEntry("Message Queue not installed, cannot use this plugin with out it");
                exit(0);
        }


if($MESSAGE_QUEUE_PLUGIN_ENABLED) {
        $queueMessages = getNewPluginMessages($MATRIX_PLUGIN_OPTIONS);
        if($queueMessages != null || $queueMessages != "") {
        	
        //print_r($queueMessages);
		outputMessages($queueMessages);
        } else {
        	logEntry("No messages file exists??");
        }
        
} else {
        logEntry("MessageQueue plugin is not enabled/installed");
}

function outputMessages($queueMessages) {

	global $matrixFIFO,$MESSAGE_TIMEOUT;

	if(count($queueMessages) <=0) {
		echo "No messages to output \n";
		return;	
	}
	for($i=0;$i<=count($queueMessages)-1;$i++) {

		$messageParts = explode("|",$queueMessages[$i]);

		//echo "0: ".$messageParts[0]."\n";
		//echo "1: ".$messageParts[1]."\n";
		//echo "2: " .$messageParts[2]."\n";
		//echo "3: ".$messageParts[3]."\n";
		
		$messageText = urldecode($messageParts[1]);

		//echo "Sending message: ".$messageText." to matrix FIFO\n";
		if(!file_exists($matrixFIFO)) {
			logEntry("No matrix fifo exists, cannot write");
			exit(0);
		}
		$cmd = "/bin/echo \"".$messageText. "\" > ".$matrixFIFO;
		exec($cmd,$output);
		//echo "sleeping ".$MESSAGE_TIMEOUT. " sending clear line then";
		sleep($MESSAGE_TIMEOUT);

		$clearLineCmd = "/bin/echo \"\" > ".$matrixFIFO;
		exec($clearLineCmd,$clearOutput);

	}


}

?>
