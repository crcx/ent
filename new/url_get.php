<?
function get_url_contents($url){
        $crl = curl_init();
        $timeout = 8;
        curl_setopt ($crl, CURLOPT_URL,$url);
        curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
        $ret = curl_exec($crl);
        curl_close($crl);
        return $ret;
}

function download($file, $url)
{
  system("rm -f ". $file);
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  $fp = fopen($file, 'wb');
  curl_setopt($ch, CURLOPT_FILE, $fp);
  curl_exec ($ch);
  curl_close ($ch);
  fclose($fp);
}

?>
