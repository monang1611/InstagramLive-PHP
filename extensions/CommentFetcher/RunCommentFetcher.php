<?php

require './vendor/autoload.php';
require_once './extensions/Extension.php';
require_once './extensions/CommentFetcher/CommentFetcher.php';
require_once './config.php';

use InstagramAPI\Instagram;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$ig = new Instagram(false, false);

$broadcastId = null;
foreach ($argv as $arg){
    if($idPos = strpos($arg, '-id=') !== false){
        $broadcastId = substr($arg, $idPos + 3);
        $broadcastId = explode(" ", $broadcastId)[0];
    }
}

if(!$broadcastId || empty($broadcastId)){
    die("No broadcast id sent!");
}

try {
    $ig->login(IG_USERNAME, IG_PASS);
} catch (\Exception $e) {
    echo 'Error While Logging in to Instagram: '.$e->getMessage()."\n";
    exit(0);
}

print($broadcastId);

$commentFetcher = new CommentFetcher($broadcastId, $ig);
while(true){
    sleep(10);
    $commentFetcher->printComments(true);
}