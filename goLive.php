<?php

if (php_sapi_name() !== "cli") {
    die("You may only run this inside of the PHP Command Line! If you did run this in the command line, please report: \"".php_sapi_name()."\" to the InstagramLive-PHP Repo!");
}

set_time_limit(0);
date_default_timezone_set('America/New_York');

//Load Depends from Composer...
require __DIR__ . './vendor/autoload.php';
require __DIR__ . '/config.php';


if (IG_USERNAME == "USERNAME" || IG_PASS == "PASSWORD") {
    logM("Default Username and Passwords have not been changed! Exiting...");
    exit();
}

//Login to Instagram
logM("Logging into Instagram...");
$ig = new \InstagramFollowers\Instagram();
try {
    $ig->login(IG_USERNAME, IG_PASS, true);
} catch (\Exception $e) {
    echo 'Error While Logging in to Instagram: ' . $e->getMessage() . "\n";
    exit(0);
}

//Block Responsible for Creating the Livestream.
try {
    logM("Logged In! Creating Livestream...");
    $stream = $ig->liveRequest->create_live();
    $broadcastId = $stream->getBroadcastId();
    $ig->liveRequest->start_live($broadcastId);
    // Switch from RTMPS to RTMP upload URL, since RTMPS doesn't work well.
    $streamUploadUrl = preg_replace(
        '#^rtmps://([^/]+?):443/#ui',
        'rtmp://\1:80/',
        $stream->getUploadUrl()
    );

    //Grab the stream url as well as the stream key.
    $split = preg_split("[" . $broadcastId . "]", $streamUploadUrl);

    $streamUrl = $split[0];
    $streamKey = $broadcastId . $split[1];

    logM("================================ Stream URL ================================\n" . $streamUrl . "\n================================ Stream URL ================================");

    logM("======================== Current Stream Key ========================\n" . $streamKey . "\n======================== Current Stream Key ========================");

    logM("^^ Please Start Streaming in OBS/Streaming Program with the URL and Key Above ^^");

    logM("Live Stream is Ready for Commands:");
    newCommand($ig->liveRequest, $broadcastId, $streamUrl, $streamKey);
    logM("Something Went Super Wrong! Attempting to At-Least Clean Up!");
    $ig->liveRequest->getFinalViewerList($broadcastId);
    $ig->liveRequest->end_live($broadcastId);
} catch (\Exception $e) {
    echo 'Error While Creating Livestream: ' . $e->getMessage() . "\n";
}

/**
 * The handler for interpreting the commands passed via the command line.
 *
 * @param \InstagramFollowers\Request\LiveRequest $live
 * @param $broadcastId
 * @param $streamUrl
 * @param $streamKey
 * @throws Exception
 */
function newCommand(\InstagramFollowers\Request\LiveRequest $live, $broadcastId, $streamUrl, $streamKey) {
    print "\n> ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    if ($line == 'ecomments') {
        $live->MuteUnmuteComments(false, $broadcastId);
        logM("Enabled Comments!");
    } elseif ($line == 'dcomments') {
        $live->MuteUnmuteComments(true, $broadcastId);
        logM("Disabled Comments!");
    } elseif ($line == 'stop' || $line == 'end') {
        fclose($handle);
        //Needs this to retain, I guess?
        $live->getFinalViewerList($broadcastId);
        $live->end_live($broadcastId);
        logM("Wrapping up and exiting...");
        exit();
    } elseif ($line == 'url') {
        logM("================================ Stream URL ================================\n" . $streamUrl . "\n================================ Stream URL ================================");
    } elseif ($line == 'key') {
        logM("======================== Current Stream Key ========================\n" . $streamKey . "\n======================== Current Stream Key ========================");
    } elseif ($line == 'info') {
        $info = $live->getInfo($broadcastId);
        $status = $info->getStatus();
        $active = $info->getBroadcastStatus();
        $count = $info->getViewerCount();
        logM("Info:\nStatus: $status\nActive: $active\nViewer Count: $count");
    } elseif ($line == 'viewers') {
        logM("Viewers:");
        $live->getInfo($broadcastId);
        foreach ($live->getViewerList($broadcastId)->getUsers() as &$cuser) {
            logM("@" . $cuser->getUsername() . " (" . $cuser->getFullName() . ")");
        }
    } elseif ($line == 'help') {
        logM("Commands:\nhelp - Prints this message\nurl - Prints Stream URL\nkey - Prints Stream Key\ninfo - Grabs Stream Info\nviewers - Grabs Stream Viewers\necomments - Enables Comments\ndcomments - Disables Comments\ncomment - Posts New Comment\npin - Pins Comments\nunpin - Unpins Comments\nstop - Stops the Live Stream");
    } elseif ($line == 'comment') {
        $live->comment(getInput("Please insert your comment"), $live->create_live_response->getBroadcastId());
        logM("Finished\nCommend Id:" . $live->comment_response->getComment()->getPk());
    } elseif ($line == 'pin') {
        $live->PinUnpinLiveComment(true, $live->create_live_response->getBroadcastId(), getInput("Please insert comment Id"));
        logM("Finished.");
    } elseif ($line == 'unpin') {
        $live->PinUnpinLiveComment(false, $live->create_live_response->getBroadcastId(), getInput("Please insert comment id"));
        logM("Finished.");

    } else {
        logM("Invalid Command. Type \"help\" for help!");
    }
    fclose($handle);
    newCommand($live, $broadcastId, $streamUrl, $streamKey);
}

function getInput($message) {
    print "$message:\n> ";
    $h = fopen("php://stdin", "r");
    return trim(fgets($h));
}

/**
 * Logs a message in console but it actually uses new lines.
 */
function logM($message) {
    print $message . "\n";
}
