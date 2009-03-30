<?php
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
   Ent provides services, tools, and web content to users of
   email.

   The core Ent code was written by Charles Childers and is
   gifted to the public domain.

   Some of the support code (lastrss.php and html2txt.php)
   were written by others and are bound by copyright. See the
   files for license terms.
   ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
require_once 'html2txt.php';
require_once 'url_get.php';
require_once 'lastrss.php';
require_once 'attach.php';
require_once 'leaves.php';

$message = "";
$who = "";
$type = "none";
$subject = "";
$body = "";

while(1)
{
$message = "";
$who = "";
$type = "none";
$subject = "";
$body = "";

  $mbox = imap_open ("{localhost:110/pop3/notls}INBOX", "account", "password") or die("failure");
  $headers = @imap_headers($mbox);
  $numEmails = sizeof($headers);

  /* If we have messages, loop through them. */
  if ($numEmails > 0)
  {
    $pids = array();
    $tps = array();
    for($i = 1; $i < $numEmails+1; $i++)
    {
      $mailHeader = @imap_headerinfo($mbox, $i);
      $from = $mailHeader->fromaddress;
      $who = substr(strrchr($from, "<"), 1);
      $who = substr($who, 0, strlen($who)-1);
      $message = strip_tags($mailHeader->subject);

      $pid = pcntl_fork();
      if($pid == -1)
      {
        die('could not fork');
      }
      if ($pid)
      {
        $tps[$i] = $message;
        $pids[$i] = $pid;
        echo 'x';
      }
      else
      {
        leaves();
        if ($type == "none")
        {
          $body = "Sorry, but Ent wasn't able to understand your request. ";
          $body .= "Try sending 'help' as a subject to see what requests Ent can handle.\n";
          $body .= "For the record, your request was: ".$message;
          $header = "From: Ent <entx@retroforth.org>\r\n";
          mail($who, "Sorry...", $body, $header);
        }
        exit(1);
      }
    }
    imap_delete($mbox,'1:*');
    imap_expunge($mbox);
    foreach($pids as $pid)
    {
      echo "\nWaiting for ".$pid."\n";
      pcntl_waitpid($pid, $status);
    }
    foreach($tps as $tp)
    {
      echo "Query: ". $tp . "\n";
    }
  }
  imap_close($mbox);
  echo '.';
  sleep(2);
}
?>
