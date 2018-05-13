# InstagramLive-PHP
A PHP script that allows for you to go live on Instagram with any streaming program that supports RTMP!

# Note
This has been only tested on Windows, I have no clue if this works on UNIX-Based Systems. Feel free to try though!

Additionally, I've only tested this in OBS. So I highly recommend using it

# Setup
If you are running this after the first setup, work from step six on...

1. Install PHP, of course...
2. [Install Composer](https://getcomposer.org/download/)
3. Clone the Repository
4. Run ```composer require mgp25/instagram-php react/child-process``` in the cloned folder
5. Edit the Username and Password inside of `config.php` to your instagram details
6. Run the `goLive.php` script. (`php -f goLive.php`)
7. Copy you Stream-URL and Stream-Key and paste them into your streaming software. [See OBS-Setup](https://github.com/JRoy/InstagramLive-PHP#obs-setup)

# OBS-Setup
1. Go to the "Stream" section of your OBS Settings 
2. Set "Stream Type" to "Custom Streaming Server"
3. Set the "URL" field to the stream url you got from the script
4. Set the "Stream key" field to the stream key you got from the script
5. Make Sure "Use Authentication" is **unchecked** and press "OK"
6. Start Streaming in OBS
7. To stop streaming, run the "stop" command in your terminal and then press "Stop Streaming" in OBS
* Note: To emulate the exact content being sent to Instagram, set your OBS canvas size to 720x1280. This can be done by going to Settings->Video and editing Base Canvas Resolution to "720x1280".

# FAQ
#### OBS gives a "Failed to connect" error
This is mostly due to an invalid stream key: The stream key changes **every** time you start a new stream so it must be replaced in OBS every time.
#### I've stopped streaming but Instagram still shows me as live
This is due to you not running the "stop" command inside the script. You cannot just close the command window to make Instagram stop streaming, you must run the stop command in the script. If you *do* close the command window however, start it again and just run the stop command, this should stop Instagram from listing to live content.
#### I get an error inside of Instagram when archiving my story
This is usually due to archiving a stream that had no content (video). Just delete the archive and be go on with your day.

# Donate
If you would like to donate to me because you find what I do useful and would like to support me, you can do so through this methods:

Patreon: https://www.patreon.com/JRoy

PayPal.me: https://www.paypal.me/JoshuaRoy1

Bitcoin: `32J2AqJBDY1VLq6wfZcLrTYS8fCcHHVDKD`
