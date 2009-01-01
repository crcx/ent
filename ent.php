<?php
/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
   +-----  .    .  -------
   |       |\   |     |
   +---    | \  |     |
   |       |  \ |     |
   +-----  |   \|     |
   ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
   Ent is an application providing tools and services via an
   email interface.

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

$mbox = new Mail_Mbox($file);
$mbox->open();

for ($n = 0; $n < $mbox->size(); $n++)
{
  $type = "none";
  $message = $mbox->get($n);

  preg_match('/Return-path: (.*)$/m', $message, $returns);
  $who = str_replace($filter, "", $returns[1]);

  include 'leaves/www.leaf';
  include 'leaves/lookup.leaf';
  include 'leaves/define.leaf';
  include 'leaves/weather.leaf';
  include 'leaves/news.leaf';
  include 'leaves/worldnews.leaf';
  include 'leaves/usnews.leaf';
  include 'leaves/bbcnews.leaf';
  include 'leaves/ups.leaf';
  include 'leaves/help.leaf';

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
