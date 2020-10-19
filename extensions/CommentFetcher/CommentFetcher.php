<?php
use InstagramAPI\Instagram;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CommentFetcher
 *
 * @author Arriba-PC
 */
class CommentFetcher implements Extension {
    
    public $savedComments = [];
    private $broadcastId;
    private Instagram $ig;
    private $lastTimestamp = 0;
    private $process = null;
    
    private $templateFilePath = './extensions/CommentFetcher/Template.html';
    
    public function __construct($broadcastId, $ig){
        $this->broadcastId = $broadcastId;
        $this->ig = $ig;
    }
    
    public function getComments(){
        $this->broadcastId;
        $live = $this->ig->live;
        $comments = $live->getComments($this->broadcastId, $this->lastTimestamp);
        foreach ($comments->getComments() as &$comment) {   
            array_push($this->savedComments, $comment);
            $this->lastTimestamp = $comment->getCreatedAt();
        }
        
        print("Last timestamp for messages : ".$this->lastTimestamp);
    }
    
    public function printComments($toHtml = false){
        $this->getComments();
        $comments =  array_slice($this->savedComments, -5);
        if(!$toHtml){
            foreach ($comments as &$comment){
                $username = $comment->getUser()->getUsername();
                $message = $comment->getText();
                logM("$username : \n $message");
            }
        } else {
            $this->generateCommentsHTML($comments);
        }
    }
    
    private function generateCommentsHTML($comments){
        $commentDivs = '';
        print("\n\nGenerating comments HTML...\n\n");
        foreach ($comments as &$comment) {
            $commentDivs .= $this->generateCommentDiv($comment);
        }
        
        $myfile = fopen($this->templateFilePath, "r") or die("Unable to open file!");
        $template =  fread($myfile,filesize($this->templateFilePath));
        fclose($myfile);
        
        $templateFilled = str_replace('!COMMENTS_PLACEHOLDER!', $commentDivs, $template);
        $myfile = fopen("comments.html", "w") or die("Unable to open comments file!");
        fwrite($myfile, $templateFilled);
        fclose($myfile);
        print("\n\Comments Printed to HTML...\n\n");


    }
    
    private function generateCommentDiv(\InstagramAPI\Response\Model\Comment $comment){
        $username = $comment->getUser()->getUsername();
        $message = $comment->getText();

        $div = "<div class='comment'><div class='commentWrapper'>";
        $div .= "<p class='commentUser'>$username : </p> <p class='commentMessage'>$message </p> ";
        $div .= "</div></div>";
        return $div;
    }

    public function run() {
        $broadcastId = $this->broadcastId;
        $cmd = "php extensions/CommentFetcher/RunCommentFetcher.php -id=$broadcastId";
        $spec = array (
            0 => array('pipe', 'r'),
            1 => array('file', 'extensions/CommentFetcher/log.log', 'w'), // or 'a' to append
            2 => array('file', 'extensions/CommentFetcher/error.log', 'w'),
        );
        $this->process = proc_open( $cmd, $spec, $pipes);
    }
    
    public function stop(){
        $status = proc_get_status($this->process);
        return exec('taskkill /F /T /PID '.$status['pid']);
    }
    
    
}

