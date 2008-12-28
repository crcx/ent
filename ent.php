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

   How it works:

     - You send mail to an email address
     - This code gets run somehow (I use a cron job)
     - It parses an mbox file. (/var/mail/ent for me)
     - The requests are taken from the subject line
       - LOOKUP <phrase>
         Returns the Wikipedia page for <phrase>
       - WWW <site>
         Returns the contents of <site>
       - Requests can be mixed case
     - The results are emailed back to you and your message is
       removed from the mbox

   The code uses several pieces of code written by others. See
   the individual files for copyright/license information.

   This code was written by Charles Childers and is gifted to
   the public domain.
   ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */

<?php
require_once 'mbox.php';
require_once 'html2txt.php';
require_once 'urlget.php';

//reads a mbox file
$file = '/var/mail/ent';

$filter = array("<", ">");

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

    $mbox->remove($n);
}

$mbox->close();
?>

