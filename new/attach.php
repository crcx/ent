<?
function sendMessage($to, $subject, $body, $files)
{
  $headers = "From: Ent <ent@retroforth.org>\n";

  // boundary
  $semi_rand = md5(time());  $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
 
  // headers for attachment
  $headers .= "MIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";

  // multipart boundary
  $message = "This is a multi-part message in MIME format.\n\n" . "--{$mime_boundary}\n" . "Content-Type: text/plain; charset=\"iso-8859-1\"\n" . "Content-Transfer-Encoding: 7bit\n\n" . $body . "\n\n";
  $message .= "--{$mime_boundary}\n";
 
  // preparing attachments
  for($x = 0; $x < count($files); $x++)
  {
    $file = fopen($files[$x], "rb");
    $data = fread($file, filesize($files[$x]));
    fclose($file);
    $data = chunk_split(base64_encode($data));
    $message .= "Content-Type: {\"application/octet-stream\"};\n" . " name=\"$files[$x]\"\n" .
    "Content-Disposition: attachment;\n" . " filename=\"".basename($files[$x])."\"\n" .
    "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
    $message .= "--{$mime_boundary}\n";
  }
  mail($to, $subject, $message, $headers);
}

