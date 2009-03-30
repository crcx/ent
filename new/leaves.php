<?php

function leaves()
{
  global $message, $who, $type, $subject, $body;

/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
   Usage:
     c2 page

   Returns:
     c2 wiki page
   ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
  if ($type == "none")
  {
    $a = preg_match('/c2 (.*)$/i', $message, $matches);
    $subject = $matches[1];
    if ($a == 1)
    {
      $type = "c2wiki";
      $html = get_url_contents("http://c2.com/cgi/wiki?" . $subject);
      $body = html2text($html);
      $header = "From: Ent <ent@retroforth.org>\r\n"; //optional headerfields
      mail($who, "c2 wiki: " . $subject, $body, $header);
    }
  }


/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
   Usage:
     define word

   Returns:
     Wiktionary definition for the requested word
   ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
  if ($type == "none")
  {
    $a = preg_match('/define (.*)$/i', $message, $matches);
    $subject = $matches[1];
    if ($a == 1)
    {
      $url = str_replace(" ", "%20", $subject);
      $type = "define";
      $html = get_url_contents("http://en.wiktionary.org/w/index.php?printable=yes&title=" . $url);
      $body = html2text($html);
      $header = "From: Ent <ent@retroforth.org>\r\n"; //optional headerfields
      mail($who, "Definition of " . $subject, $body, $header);
    }
  }


/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
   Usage:
     help

   Returns:
     Help on using Ent
   ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
  if ($type == "none")
  {
    $a = preg_match('/help(.*)$/i', $message, $matches);
    $subject = $matches[1];
    if ($a == 1)
    {
      $body = "Ent Services\n\n";
      $body .= "Send an email with one of the following in the subject line.\n\n";
      $body .= "\n- - - - CORE SERVICES - - - -\n\n";
      $body .= "c2 page\nGet a page from the c2.com wiki.\n\n";
      $body .= "define word\nGet the defintion of the requested word.\n\n";
      $body .= "help\nGet a copy of this text.\n\n";
      $body .= "lookup word or phrase\nGet a Wikipedia article on the requested word or phrase.\n\n";
      $body .= "www site\nGet the contents of the requested website.\n\n";
      $type = "help";
      $header = "From: Ent <ent@retroforth.org>\r\n"; //optional headerfields
      mail($who, "Help on using Ent" . $subject, $body, $header);
    }
  }


/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
   Usage:
     lookup [word | phrase]

   Returns:
     Wikipedia article for the requested word or phrase
   ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
  if ($type == "none")
  {
    $a = preg_match('/lookup (.*)$/i', $message, $matches);
    $subject = $matches[1];
    if ($a == 1)
    {
      $url = str_replace(" ", "%20", $subject);
      $type = "lookup";
      $html = get_url_contents("http://en.wikipedia.org/w/index.php?printable=yes&title=" . $url);
      $body = html2text($html);
      $header = "From: Ent <ent@retroforth.org>\r\n"; //optional headerfields
      mail($who, "Wikipedia: " . $subject, $body, $header);
    }
  }


/* ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
   Usage:
     www url

   Returns:
     Contents of the requested URL.
   ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ */
  if ($type == "none")
  {
    $a = preg_match('/www (.*)$/i', $message, $matches);
    $subject = $matches[1];
    if ($a == 1)
    {
      $url = str_replace(" ", "%20", $subject);
      $type = "web";
      $html = get_url_contents($url);
      $body = html2text($html);

      $b = preg_match('/[tT][wW][iI][tT][tT][eE][rR]/m', $url, $c);
      if ($b == 1)
        $body = "*** Ent now offers Twitter feeds. Just send 'twitter usename' as the subject.\n\n" . $body;
      $header = "From: Ent <ent@retroforth.org>\r\n"; //optional headerfields
      mail($who, "Content of " . $subject, $body, $header);
    }
  }

}
?>
