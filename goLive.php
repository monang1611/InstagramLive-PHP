<?php
set_time_limit(0);
date_default_timezone_set('America/New_York');
require __DIR__.'/vendor/autoload.php';
require_once 'config.php';


/////// (Sorta) Config (Still Don't Touch It)  ///////
$username = IG_USERNAME;
$password = IG_PASS;
$debug = false;
$truncatedDebug = false;
//////////////////////////////////////////////////////


//Login to Instagram
logM("Logging into Instagram...");
$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);
try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}
//Main Stream Block
try {
    logM("Logged In! Creating Livestream...");
    $stream = $ig->live->create();
    $broadcastId = $stream->getBroadcastId();
    $ig->live->start($broadcastId);
    // Switch from RTMPS to RTMP upload URL, since RTMPS doesn't work well.
    $streamUploadUrl = preg_replace(
        '#^rtmps://([^/]+?):443/#ui',
        'rtmp://\1:80/',
        $stream->getUploadUrl()
    );

    //Grab the stream url as well as the stream key.
    $split = preg_split("[".$broadcastId."]", $streamUploadUrl);

    $streamUrl = $split[0];
    $streamKey = $broadcastId.$split[1];

    logM("================================ Stream URL ================================\n".$streamUrl."\n================================ Stream URL ================================");

    logM("======================== Current Stream Key ========================\n".$streamKey."\n======================== Current Stream Key ========================");

    logM("^^ Please Start Streaming in OBS/Streaming Program with the URL and Key Above ^^");

    logM("Live Stream is Ready for Commands:");
    logM("Commands:\nhelp - Prints this message\nurl - Prints Stream URL\nkey - Prints Stream Key\ninfo - Grabs Stream Info\nviewers - Grabs Stream Viewers\necomments - Enables Comments\ndcomments - Disables Comments\nstop - Stops the Live Stream");
    newCommand($ig->live, $broadcastId, $streamUrl, $streamKey);
    $ig->live->getFinalViewerList($broadcastId);
    $ig->live->end($broadcastId);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}

function chatLoop(InstagramAPI\Request\Live $live, $broadcastId) {
    echo "hi";
    return function (){};
}

/**
 * The handler for imteripting the commands passed via the command line.
 */
function newCommand(InstagramAPI\Request\Live $live, $broadcastId, $streamUrl, $streamKey) {
    print "\n> ";
    $handle = fopen ("php://stdin","r");
    $line = trim(fgets($handle));
    if($line == 'ecomments') {
        $live->enableComments($broadcastId);
        logM("Enabled Comments!");
    } elseif ($line == 'dcomments') {
        $live->disableComments($broadcastId);
        logM("Disabled Comments!");
    } elseif ($line == 'stop') {
        fclose($handle);
        //Needs this to retain, I guess?
        $live->getFinalViewerList($broadcastId);
        $live->end($broadcastId);
        logM("Stream Ended! Would you like to keep it archived? Type yes to keep it archived.");
        print "> ";
        $handle = fopen ("php://stdin","r");
        $archived = trim(fgets($handle));
        if ($archived == 'yes') {
            $live->addToPostLive($broadcastId);
            logM("Livestream added to archive!");
        }
        logM("Wrapping up and exiting...");
        exit();
    } elseif ($line == 'url') {
        logM("================================ Stream URL ================================\n".$streamUrl."\n================================ Stream URL ================================");
    } elseif ($line == 'key') {
        logM("======================== Current Stream Key ========================\n".$streamKey."\n======================== Current Stream Key ========================");
    } elseif ($line == 'info') {
        $status = $live->getInfo($broadcastId)->getStatus();
        $count = $live->getInfo($broadcastId)->getViewerCount();
        logM("Info:\nStatus: $status\nViewer Count: $count");
    } elseif ($line == 'viewers') {
        logM("Viewers:");
        $live->getInfo($broadcastId);
        foreach ($live->getViewerList($broadcastId)->getUsers() as &$cuser) {
            logM("@".$cuser->getUsername()." (".$cuser->getFullName().")");
        }
    } elseif ($line == 'help') {
        logM("Commands:\nhelp - Prints this message\nurl - Prints Stream URL\nkey - Prints Stream Key\ninfo - Grabs Stream Info\nviewers - Grabs Stream Viewers\necomments - Enables Comments\ndcomments - Disables Comments\nstop - Stops the Live Stream");
    }

    else {
       logM("Invalid Command!");
    }
    fclose($handle);
    newCommand($live, $broadcastId, $streamUrl, $streamKey);
}

function logM($message) {
    print $message."\n";
}