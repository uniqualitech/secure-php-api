<?php

/* * ********************************************************************
 *
 *  VAH Class
 *  ---------------
 *  Common functions used by the VAH API.
 *
 * ******************************************************************** */

require_once('db_class.php');
require_once('db_connection.php');

class vah_class {
    /* ---------------------------------------------------------------------
      API result functions
      ---------------------------------------------------------------------- */

    public function api_error($declaration, $msg) {
        return array(
            'flag' => 'false',
            'result' => 'error',
            'declaration' => $declaration,
            'msg' => $msg
        );
    }

    // public function api_success($data = NULL) {
    //     $output = array();
    //     $output['flag'] = 'true';
    //     $output['result'] = 'success';
    //     if (isset($data))
    //         $output['data'] = $data;
    //     return $output;
    // }

    public function api_success($data = NULL,$msg) {
       $output = array();
       $output['flag'] = 'true';
       $output['result'] = 'success';
       $output['msg'] = $msg;
       if (isset($data))
           $output['data'] = $data;
       return $output;
   }

    /* ---------------------------------------------------------------------
      Declare common error output
      ---------------------------------------------------------------------- */

    public function error_api() {
        return $this->api_error('API_ERROR', 'Bad API user or secret.');
    }

    public function error_db() {
        return $this->api_error('DATABASE_ERROR', 'Unknown database error.');
    }

    public function error_invalid($fieldname) {
        return $this->api_error('INVALID_INPUT', 'Invalid or missing ' . $fieldname . '.');
    }

    public function error_notfound($item) {
        return $this->api_error('NOT_FOUND', 'Could not find ' . $item . '.');
    }

    public function error_denied($msg) {
        return $this->api_error('DENIED', $msg);
    }

    public function error_duplicate($msg) {
        return $this->api_error('DUPLICATE_ENTRY', $msg);
    }

    public function error_unknown($msg) {
        return $this->api_error('UNKNOWN_ERROR', $msg);
    }

    /* ---------------------------------------------------------------------
      Declare validation functions
      ---------------------------------------------------------------------- */

    // Returns FALSE if email invalid; otherwise returns email address converted to lowercase.
    public function validate_email($email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return mb_strtolower($email);
        }
        return FALSE;
    }

    // Returns FALSE if phone number invalid; otherwise returns phone number with non-numeric characters stripped
    public function validate_phone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) >= 10 && strlen($phone) < 20) {
            return $phone;
        }
        return FALSE;
    }

    // Takes encrypted id - Returns FALSE if id invalid; otherwise returns id decrypted
    public function validate_id($id) {
        $decrypted_id = $this->decryptHex(ENCRYPT_KEY, $id);
        if ($this->validate_number($decrypted_id)) {
            return $decrypted_id;
        } else {
            return FALSE;
        }
    }

    // Takes encrypted secret - Returns FALSE if secret invalid; otherwise returns secret decrypted
    public function validate_secret($secret) {
        $decrypted_secret = $this->decryptHex(ENCRYPT_KEY, $secret);
        if (@preg_match("/^[a-zA-Z0-9]{15}$/", $decrypted_secret)) {
            return $decrypted_secret;
        } else {
            return FALSE;
        }
    }

    public function validate_date($date) {
        $date_part = explode('-', $date);
        if (count($date_part) != 3 ||
                strlen($date_part[0]) != 4 ||
                strlen($date_part[1]) != 2 ||
                strlen($date_part[2]) != 2 ||
                !is_numeric($date_part[0]) ||
                !is_numeric($date_part[1]) ||
                !is_numeric($date_part[2]) ||
                !checkdate($date_part[1], $date_part[2], $date_part[0])) {
            return FALSE;
        }
        return TRUE;
    }

    public function validate_number($number) {
        return is_numeric($number);
    }

    public function validate_lat_long($lat_or_long) {
        return @preg_match('/^(\-?[0-9]+(\.[0-9]+)?)$/', $lat_or_long);
    }

    public function validate_hex($hex_code) {
        return @preg_match("/^[a-f0-9]{2,}$/i", $hex_code) && !(strlen($hex_code) & 1);
    }

    /* ---------------------------------------------------------------------
      Date functions
      ---------------------------------------------------------------------- */

    public function current_date() {
        return date("Y-m-d");
    }

    public function current_time() {
        return date("H:i:s");
    }

    public function current_datetime() {
        return date("Y-m-d H:i:s");
    }

    public function offset_datetime($offset_seconds) {
        return date("Y-m-d H:i:s", time() + ($offset_seconds));
    }

    /* ---------------------------------------------------------------------
      Other functions
      ---------------------------------------------------------------------- */

    private function random_string($length) {
        $chars = 'abcdefghijklmnopqrstuvxyzABCDEFGHIJKLMNOPQRSTUVXYZ1234567890';
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $result;
    }

    public function client_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) { //Check for client IP if user is on a shared network
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { //Check if proxy passes real client IP
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    public function encryptHex($key, $text) {
        return trim(
                bin2hex(
                        mcrypt_encrypt(
                                MCRYPT_RIJNDAEL_128, $key, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(
                                        mcrypt_get_iv_size(
                                                MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB
                                        ), MCRYPT_RAND
                                )
                        )
                )
        );
    }

    public function decryptHex($key, $text) {
        if (!$this->validate_hex($text)) {
            return 'Invalid hex code.';
        }
        return trim(
                mcrypt_decrypt(
                        MCRYPT_RIJNDAEL_128, $key, pack("H*", $text), MCRYPT_MODE_ECB, mcrypt_create_iv(
                                mcrypt_get_iv_size(
                                        MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB
                                ), MCRYPT_RAND
                        )
                )
        );
    }

    /* ---------------------------------------------------------------------
      Re-usable functions
      ---------------------------------------------------------------------- */

    public function encrypt_string($string) {
        return $this->encryptHex(ENCRYPT_KEY, $string);
    }

    public function send_mail_with_headers($subject, $from, $recipient, $mess, $isHTML = FALSE, $filearr = Array(), $path = "", $replyto = '') {
        //set the message content type
        $content_type = "text/plain";
        if ($isHTML == TRUE) {
            $content_type = "text/html";
        }
        if (is_array($recipient)) {
            $to = @$recipient[0];
            $cc = @$recipient[1];
            $bcc = @$recipient[2];
        } else {
            $to = $recipient;
            $cc = "";
            $bcc = "";
        }
        //set the header
        $headers = "";
        if (count($filearr) > 0) {// USE multipart mime message to send mail with attachment
            //unique mime boundry seperater
            $mime_boundary_value = md5(uniqid(time()));
            //set the headers
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: multipart/mixed; boundary=\"$mime_boundary_value\";\r\n";
            $headers .= "If you are reading this, then upgrade your e-mail client to support MIME.\r\n";
            //set the message
            if ($mess <> "") {
                $mess = "--$mime_boundary_value\n" .
                        "Content-Type: $content_type; charset=\"iso-8859-1\"\n" .
                        "Content-Transfer-Encoding: 7bit\n\n" .
                        $mess . "\n\n";
            }
            for ($i = 0; $i < count($filearr); $i++) {
                // if the upload succeded, the file will exist
                if (file_exists($filearr[$i])) {
                    $mess .= "--$mime_boundary_value\n";
                    $mess .= "Content-Type: text/csv; name=\"{$filearr[$i]}\"\n";
                    $mess .= "Content-Disposition: attachment; filename=\"{$filearr[$i]}\"\n";
                    $mess .= "Content-Transfer-Encoding: base64\n\n";
                    //read file data
                    $file = fopen($filearr[$i], 'rb');
                    $data = fread($file, filesize($filearr[$i]));
                    fclose($file);
                    //encode file data
                    $data = chunk_split(base64_encode($data));
                    $mess .= $data . "\n\n";
                }
            }
            $mess .= "--$mime_boundary_value--\n"; //end message
        } else {
            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers.='Content-Type: ' . $content_type . '; charset=iso-8859-1' . "\r\n";
        }
        // To send HTML mail, the Content-type header must be set

     
        $headers.='From: ' . $from . "\r\n";
        //$bcc != '' ? $headers.="Bcc: $bcc \r\n" : '';
        $cc != '' ? $headers.="cc:   $cc \r\n" : '';

        $response = mail($to, $subject, $mess, $headers); //this function will send mail
    }

}

$VAH = new vah_class();
?>