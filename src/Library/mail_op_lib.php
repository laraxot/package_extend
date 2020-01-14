<?php



class MAIL_OP extends baseController
{
    public function is_email($email)
    {
        /*
          if (eregi("^([a-z0-9_\.-])+@(([a-z0-9_-])+\\.)+[a-z]{2,6}$", trim($email)))
          return 1;
          else
          return 0;
          */
        return \preg_match('|^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]{2,})+$|i', $email);
    }

    /*
    function email($mail_from, $from_name, $mail_to, $subject, $message, $file){

        $file_name = basename($file); // Get file name
        $data = file_get_contents($file); // Read file contents
        $file_contents = chunk_split(base64_encode($data)); // Encode file data into base64
        $uid = md5(time()); // Create unique boundary from timestamps
        $headers = array();
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "From: {$from_name}<{$mail_from}>";
        $headers[] = "Reply-To: {$mail_from}";
        $headers[] = "Content-Type: multipart/mixed; boundary=\"{$uid}\"";
        $headers[] = "This is a multi-part message in MIME format.";
        $headers[] = "--{$uid}";
        $headers[] = "Content-type:text/plain; charset=iso-8859-1"; // Set message content type
        $headers[] = "Content-Transfer-Encoding: 7bit";
        $headers[] = $message; // Dump message
        $headers[] = "--{$uid}";
        $headers[] = "Content-Type: application/octet-stream; name=\"{$file_name}\""; // Set content type and file name
        $headers[] = "Content-Transfer-Encoding: base64"; // Set file encoding base
        $headers[] = "Content-Disposition: attachment; filename=\"{$file_name}\""; // Set file Disposition
        $headers[] = $file_contents; // Dump file
        $headers[] = "--{$uid}--"; //End boundary
        // Send mail with header information
        if (mail($mail_to, $subject, '', implode("\r\n", $headers) ))
            return true;
    }


    $from = "mail@w3bees.com";
    $name = "W3Bees";
    $to = "name@server.com";
    $subject = "My mail subject";
    $message = "My message";
    $file = 'path/to/file';
    if(email($from, $name, $to, $subject, $message, $file)){
        echo "Success!";
    }
    else{
        echo "Error!";
    }

    */

//------------//-----------------//-------------------
//------------
}//end class
