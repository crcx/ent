<?php
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
   +-----  .    .  -------
   |       |\   |     |
   +---    | \  |     |
   |       |  \ |     |
   +-----  |   \|     |
   ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
   This is a tool to provide services via email. At present it
   offers:

     - Wikipedia
     - Content of web pages
     - News feed (RSS from NPR)
     - UPS Tracking
     - A Help summary

   How it works:

     - You send mail to an email address
     - This code gets run somehow (I use a cron job)
     - It parses an mbox file. (/var/mail/ent for me)
     - The requests are taken from the subject line
     - The results are emailed back to you and your message is
       removed from the mbox

   The code uses several pieces of code written by others. See
   the individual files for copyright/license information.

   This code was written by Charles Childers and is gifted to
   the public domain.
   ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
require_once 'mbox.php';
require_once 'html2txt.php';
require_once 'url_get.php';
require_once 'lastrss.php';


//reads a mbox file
$file = '/var/mail/ent';

$filter = array("<", ">");

echo 'Using file ' . $file . "\n";

$mbox = new Mail_Mbox($file);
$mbox->open();

for ($n = 0; $n < $mbox->size(); $n++) {
    $type = "none";
    $message = $mbox->get($n);

    preg_match('/Return-path: (.*)$/m', $message, $returns);
    $who = str_replace($filter, "", $returns[1]);

    /* Websites */
    if ($type == "none")
    {
      $a = preg_match('/Subject: [wW][wW][wW] (.*)$/m', $message, $matches);
      $subject = $matches[1];
      if ($a == 1)
      {
        $url = str_replace(" ", "%20", $subject);
        $type = "web";
        $html = get_url_contents($url);
        $body = html2text($html);
        $header = "From: Ent <ent@retroforth.org>\r\n"; //optional headerfields
        mail($who, "Content of " . $subject, $body, $header);
      }
    }


    /* Wikipedia Lookup */
    if ($type == "none")
    {
      $a = preg_match('/Subject: [lL][oO][oO][kK][uU][pP] (.*)$/m', $message, $matches);
      $subject = $matches[1];
      if ($a == 1)
      {
        $url = str_replace(" ", "%20", $subject);
        $type = "lookup";
        $html = get_url_contents("http://en.wikipedia.org/w/index.php?printable=yes\&title=" . $url);
        $body = html2text($html);
        $header = "From: Ent <ent@retroforth.org>\r\n"; //optional headerfields
        mail($who, "Wikipedia: " . $subject, $body, $header);
      }
    }


    /* RSS: General News */
    if ($type == "none")
    {
      $a = preg_match('/Subject: [nN][eE][wW][sS](.*)$/m', $message, $matches);
      $subject = $matches[1];
      if ($a == 1)
      {
        $rss = new lastRSS;
        $rss->cache_dir = '/tmp';
        $rss->cache_time = 60;

        $body = "";
        $type = "rss-news";
        if ($rs = $rss->get('http://www.npr.org/rss/rss.php?id=1001'))
        {
          $body = "Feed for $rs[title]\n\n";
          foreach($rs['items'] as $item)
          {
            $body .= "== " . $item['title'] . " ==\n";
            $body .= $item['description'] . "\n\n";
          }
        }
       else
       {
         $body = "Error: Unable to load RSS feed!\n";
       }
       $header = "From: Ent <ent@retroforth.org>\r\n"; //optional headerfields
       mail($who, "Latest News" . $subject, $body, $header);
      }
    }


    /* UPS Tracking */
    if ($type == "none")
    {
      $a = preg_match('/Subject: [uU][pP][sS] (.*)$/m', $message, $matches);
      $subject = $matches[1];
      if ($a == 1)
      {
        $type = "ups";
        $html = get_url_contents("http://iship.com/trackit/track.aspx?T=1&Track=" . $subject . "&ACCT=AISHIP&TP=I&TSubmit=Submit");
        $body = html2text($html);
        $header = "From: Ent <ent@retroforth.org>\r\n"; //optional headerfields
        mail($who, "UPS Status for " . $subject, $body, $header);
      }
    }


    /* Help */
    if ($type == "none")
    {
      $a = preg_match('/Subject: [hH][eE][lL][pP](.*)$/m', $message, $matches);
      $subject = $matches[1];
      if ($a == 1)
      {
        $body = "Ent Services\n\n";
        $body .= "Send an email with one of the following in the subject line.\n\n";
        $body .= "lookup word or phrase\nGet a Wikipedia article on the requested word or phrase.\n\n";
        $body .= "www site\nGet the contents of the requested website.\n\n";
        $body .= "news\nGet the top stories from NPR.\n\n";
        $body .= "ups tracking-number\nObtain tracking information for a UPS shipment.\n\n";
        $body .= "help\nGet a copy of this text.";
        $type = "help";
        $header = "From: Ent <ent@retroforth.org>\r\n"; //optional headerfields
        mail($who, "Help on using Ent" . $subject, $body, $header);
      }
    }


    if ($type == "none")      
    {
      $body = "Sorry, but Ent wasn't able to understand your request.";
      $header = "From: Ent <ent@retroforth.org>\r\n"; //optional headerfields
      mail($who, "Sorry..." . $subject, $body, $header);
    }

    $mbox->remove($n);
}

$mbox->close();
?>

