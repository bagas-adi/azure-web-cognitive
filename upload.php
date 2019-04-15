<h1>Upload file ke Blob</h1>
<?php
  echo "awal";
  $account_name ="bagasap90";
  $account_key = "0rtBRyCS9TAJL3VuBD7Z8fleUubbG26hIw3SxKfWCAilygRs7ChYs6EC2jj4LnE80SSwfqU+qd3hsLHV48/iuw==";
  $key = base64_decode($account_key);
  $container_name = "image";
  $file_name = "HelloWorld.txt";

  echo "<br/>create auth";
  date_default_timezone_set ( 'GMT' ); 
  $date = date ( "D, j M Y H:i:s T" ); 
  $blobname = date("d-M-Y")."_".$file_name;
  $version = "2009-09-19";
  $utf8_encode_str = utf8_encode ("PUT\n\n\n0\n\n\n\n\n\n\n\n\nx-ms-date:". $date . "\nx-ms-version:".$version."\n/".$account_name."/".$container_name."\nrestype:container" ); 

  
  $signature_str = base64_encode(hash_hmac('sha256', $utf8_encode_str, $key, true));

  echo "<br/>Uploading the file";
  // Uploading blob
  $fdata = file_get_contents($file_name);

   $utfStr = "PUT\n\n\n". strlen($fdata) ."\n\ntext/plain; charset=UTF-8\n\n\n\n\n\n\nx-ms-blob-type:BlockBlob\nx-ms-date:" . $date . "\nx-ms-version:" . $version . "\n/"$account_name"/".$container_name."/" . $blobname ; 
  $header = array (
   "x-ms-blob-type: BlockBlob",
   "x-ms-date: " . $date,
   "x-ms-version: " . $version,
   "Authorization: SharedKey ".$account_name.":" . $signature_str,
   "Content-Type: text/plain; charset=UTF-8",
   "Content-Length: " . strlen($fdata),
   );
  $url =  "https://".$account_name.".blob.core.windows.net/".$container_name."/".$blobname;
  $ch = curl_init ();
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, 'PUT' );
  curl_setopt ( $ch, CURLOPT_URL, $url );
  curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
  curl_setopt ( $ch, CURLOPT_HTTPHEADER, $header);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $fdata);
  curl_setopt ( $ch, CURLOPT_HEADER, True );
  curl_exec ( $ch );
  echo "<br/>finish";
?>