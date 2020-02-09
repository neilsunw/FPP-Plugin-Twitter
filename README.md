My First Twitter App
---


Ever wanted to create your own Twitter App? This simple script could help you!

For more information, see my in depth tutorial:
http://iag.me/socialmedia/build-your-first-twitter-app-using-php-in-8-easy-steps/

After you have Twitter App fields (from the 8-easy steps):
  user
  consumer_key
  consumer_secret
  oauth_access_token
  oauth_access_token_secret
  
FPP v3.x setup -- Twitter Plugin:

	Plugins Needed:

	•	Matrix Tools
	•	Crontab Editor
	•	Message Queue to Matrix Overlay
	•	Twitter - Tweet Download
	•	Message Queue Aggregator for Plugins
  
  a) UI Input/Output -> MessageQueue

	•	Enable
	•	Message File: /home/fpp/media/config/FPP.FPP-Plugin-MessageQueue.db
	•	Save

[file examnple after save]
fpp@matrix:~ $ more ~/media/config/plugin.FPP-Plugin-MessageQueue
VERSION = "3.0"
MESSAGE_FILE = "%2Fhome%2Ffpp%2Fmedia%2Fconfig%2FFPP.FPP-Plugin-MessageQueue.db"
ENABLED = "ON"

b) UI Input/Output -> Twitter

	•	Enable
	•	Enter keys & access tokens
	•	Save (keys and tokens will not show in UI after saved)

[Redacted file example after save]
fpp@matrix:~/media/plugins/FPP-Plugin-MessageQueue $ more ~/media/config/plugin.Twitter
ENABLED = "ON"
SEPARATOR = ""
USER = "PiersonLights"
oauth_access_token = "93xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxTY"
oauth_access_token_secret = "G0xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx8Y"
consumer_key = "DsxxxxxxxxxxxxxxxxxxxxxKw"
consumer_secret = "Z3xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxui"
TWITTER_LAST = "1223923420339036161"
LAST_READ = "1580641071"

c) UI Input/Output -> Matrix Message

	•	Enable
	•	Matrix Name: matrix50x24
	•	Overlay Mode: 1
	•	Include Plugins in Matrix output: Twitter
	•	Font: DejaVuSans-Bold
	•	Font Size: 12
	•	Pixels per second: 5
	•	Color: yellow
	•	Save

[file example after save]
fpp@matrix:~ $ more ~/media/config/plugin.FPP-Plugin-Matrix-Message
ENABLED = "ON"
PLUGINS = "Twitter"
FONT = "DejaVuSans-Bold"
FONT_ANTIALIAS = ""
FONT_SIZE = "12"
PIXELS_PER_SECOND = "5"
COLOR = "yellow"
LAST_READ = ""
MESSAGE_TIMEOUT = "10"
MATRIX = "matrix50x24"
INCLUDE_TIME = "0"
TIME_FORMAT = "h%3Ai"
HOUR_FORMAT = "12"
OVERLAY_MODE = "1"

d) UI Input/Output -> CronEditor

	•	add two entries:

*/2 * * * * /home/fpp/media/plugins/FPP-Plugin-Twitter/getTWITTER.php
*/1 * * * * /home/fpp/media/plugins/FPP-Plugin-Matrix-Message/matrix.php

