<?php

error_reporting(0);
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

require_once('vah_class.php');
require_once('db_class.php');


function api_request($request, $api_id, $api_secret, $data, $files) {
    global $VAH, $DB;
   
    
    /* ---------------------------------------------------------------------
      Validate API user credentials
      ---------------------------------------------------------------------- */

    if($request == "login" || $request == "social_login" || $request == "sign_up" || $request == "forgot_password") {
      $res = $DB->select("SELECT * FROM tblapi_mst WHERE api_id='$api_id' AND api_secret='$api_secret'");
      if (!$res || count($res) <= 0) {
          return $VAH->error_api();
      }
    }  
    //extract($data);

    if($data != '' ){
        
      extract($data);

    }
    switch ($request) {

        //--------------- GENERAL SERVICES --------------------//

        case 'check_guest_user':
          $resArr = array();
          $resArr['is_guest_user'] = "1";
          return $VAH->api_success($resArr, 'User verified.');
        break;
        
        case "sign_up":

          if (!isset($data['name']) || $data['name'] == '') {
            return $VAH->error_invalid('Name');
          }
          if (!isset($data['email_id']) || $data['email_id'] == '') {
            return $VAH->error_invalid('Email Id');
          }
          if (!isset($data['date_of_birth']) || $data['date_of_birth'] == '') {
            //return $VAH->error_invalid('Date of birth');
          }
          if (!isset($data['gender']) || $data['gender'] == '') {
            //return $VAH->error_invalid('Gender');
          }
          if (!isset($data['password']) || $data['password'] == '') {
            return $VAH->error_invalid('Password');
          }  
          if (!isset($data['secret']) || $data['secret'] == '') {
            return $VAH->error_invalid('Secret');
          }

          //--------------- AUTHENTICAT SECRET --------------------//

          $generated_secret= secret_generator($email_id,$password);
          if($generated_secret != $secret){
            return $VAH->error_denied("Invalid secret.");
          }

          //--------------- CHECK EMAIL ID AVAILABILITY --------------------//
        
          $resVal=$DB->SELECT("SELECT * 
                               FROM  tblusers 
                               WHERE email_id = '".$email_id."'");
          
        
          if(empty($resVal) || 
            (isset($resVal[0]['has_social_account']) && $resVal[0]['has_social_account'] == '1') && 
            (isset($resVal[0]['has_normal_account']) && $resVal[0]['has_normal_account'] != '1')){

            //--------------- CHECK EMAIL ID VALIDATION --------------------//

            if(preg_match('/\s/',$email_id) > 0){
              return $VAH->error_denied('Invalid Email');
            }


            $date_of_birth = (isset($date_of_birth) && $date_of_birth !="")? date('Y-m-d',strtotime($date_of_birth)) : "";
            $gender = (isset($gender) && $gender !="")? $gender : "";
            if(empty($resVal) || (isset($resVal[0]['has_social_account']) && $resVal[0]['has_social_account'] == '0')){

              //--------------- ADD USER --------------------//

              $user_id = $DB->INSERT("INSERT INTO tblusers
                                      SET name = '".$name."',
                                          email_id = '".$email_id."',
                                          date_of_birth = '".$date_of_birth."',
                                          gender = '".$gender."',
                                          password = '".$password."',
                                          has_normal_account = '1',
                                          created_at = '".date("Y-m-d H:i:s")."',
                                          updated_at = '".date("Y-m-d H:i:s")."'");
            }else{

              //--------------- UPDATE USER --------------------//

              $UpdateUser = $DB->UPDATE(" UPDATE tblusers
                                          SET name = '".$name."',
                                              email_id = '".$email_id."',
                                              date_of_birth = '".$date_of_birth."',
                                              gender = '".$gender."',
                                              password = '".$password."',
                                              updated_at = '".date("Y-m-d H:i:s")."',
                                              has_normal_account = '1'
                                          WHERE user_id = '".$resVal[0]['user_id']."'");
              $user_id = $resVal[0]['user_id'];

            }  

            //--------------- USER NOT CREATED --------------------//

            if(!isset($user_id))
            {
              return $VAH->error_denied('Try After Some Time later.');
            }

            //--------------- API SUCCESS RESPONSE --------------------//

            $data=$DB->SELECT("SELECT * 
                               FROM  tblusers 
                               WHERE  email_id ='". $email_id ."' 
                                      AND user_id = $user_id");
            $resArr = array();
            $resArr['user_id'] = $data[0]['user_id'];
            $resArr['name'] = (isset($data[0]['name']) && $data[0]['name'] != "") ? $data[0]['name'] : "";      
            $resArr['date_of_birth'] = (isset($data[0]['date_of_birth']) && $data[0]['date_of_birth'] != "") ? date('d-m-Y',strtotime($data[0]['date_of_birth'])) : ""; 
            $resArr['email_id'] = (isset($data[0]['email_id']) && $data[0]['email_id'] != "") ? $data[0]['email_id'] : "";                     
            $resArr['gender'] = (isset($data[0]['gender']) && $data[0]['gender'] != "") ? $data[0]['gender'] : "";
            
            if($data[0]['profile_pic'] != ""){                 
              $resArr['profile_pic'] = (isset($data[0]['profile_pic']) && $data[0]['profile_pic'] != "") ? SITE_URL_REMOTE . "/" . $data[0]['profile_pic'] : ""; 
            }else if($data[0]['social_profile_pic'] != ""){                 
              $resArr['profile_pic'] = (isset($data[0]['social_profile_pic']) && $data[0]['social_profile_pic'] != "") ? $data[0]['social_profile_pic'] : ""; 
            }else{
              $resArr['profile_pic'] = ""; 
            }

            $resArr['status']=$data[0]['status'];    
            $resArr['access_token']="";
            $resArr['created_at']=$data[0]['created_at'];
            $resArr['updated_at']=$data[0]['updated_at'];

            return $VAH->api_success($resArr, "Gracias por registrarte.");

          }else{
            return $VAH->error_denied('Dirección de correo electrónico ya está en uso. Por favor prueba con otro correo electrónico.');
          }
      
        break;

        case 'login':
          
          if (!isset($data['email_id']) || $data['email_id'] == '') {
            return $VAH->error_invalid('email_id');
          }
          if (!isset($data['password']) || $data['password'] == '') {
            return $VAH->error_invalid('password');
          }
          if (!isset($data['secret']) || $data['secret'] == '') {
            return $VAH->error_invalid('secret');
          }

          //--------------- AUTHENTICAT SECRET ---------------//

          $generated_secret= secret_generator($email_id,$password);
          if($generated_secret != $secret){
            return $VAH->error_denied("Invalid secret.");
          }

          //--------------- AUTHENTICAT USER ---------------//
              
          $data = $DB->select("SELECT * 
                               FROM tblusers
                               WHERE email_id = '" . $email_id . "'  
                                     AND password = '".$password. "' 
                                     AND status=1");
           
        
          if (count($data) == 0) {
              return $VAH->error_denied("Correo electrónico o contraseña inválidos.");
          } 

          //--------------- GENARE ACCESS TOKEN FOR API SESSION ---------------//

          $access_token = access_token();
          $access_session = date("Y-m-d H:i:s", strtotime("+7 day"));
          $access_session = strtotime($access_session);
          $headers['device_type'] = $_SERVER['HTTP_DEVICETYPE'];
          $headers['device_id'] = $_SERVER['HTTP_DEVICEID'];

          if(isset($headers['device_type']) && isset($headers['device_id'])){
           
            $get_api_session = $DB->select("SELECT * 
                                            FROM tblapi_session
                                            WHERE user_id = '".$data[0]['user_id']."' 
                                                  AND device_type = '".$headers['device_type']."'
                                                  AND device_id = '".$headers['device_id']."' ");
            
            if(count($get_api_session) > 0){

              $updateQ=$DB->UPDATE("UPDATE tblapi_session
                                    SET  access_token ='".$access_token."',
                                         access_session ='".$access_session."',
                                         updated_at ='".date("Y-m-d H:i:s")."'
                                    WHERE  user_id = '".$data[0]['user_id']."'
                                            AND device_type = '".$headers['device_type']."'
                                            AND device_id = '".$headers['device_id']."'");
            }else{
              
              $inserQ=$DB->INSERT("INSERT INTO tblapi_session
                                   SET  access_token ='".$access_token."',
                                        access_session ='".$access_session."',
                                        created_at ='".date("Y-m-d H:i:s")."',
                                        updated_at='".date("Y-m-d H:i:s")."',
                                        user_id= '".$data[0]['user_id']."',
                                        device_type = '".$headers['device_type']."',
                                        device_id = '".$headers['device_id']."'");
            }

          }else{
            return $VAH->error_denied("Tipo de dispositivo inválido o identificación del dispositivo.");
          }    

          //--------------- API SUCCESS RESPONSE ---------------//

          $resArr = array();
          $resArr['user_id'] = $data[0]['user_id'];
          $resArr['name'] = (isset($data[0]['name']) && $data[0]['name'] != "") ? $data[0]['name'] : "";      
          $resArr['date_of_birth'] = (isset($data[0]['date_of_birth']) && $data[0]['date_of_birth'] != "") ? date('d-m-Y',strtotime($data[0]['date_of_birth'])) : ""; 
          $resArr['email_id'] = (isset($data[0]['email_id']) && $data[0]['email_id'] != "") ? $data[0]['email_id'] : "";                     
          $resArr['gender'] = (isset($data[0]['gender']) && $data[0]['gender'] != "") ? $data[0]['gender'] : "";
          
          if($data[0]['profile_pic'] != ""){                 
            $resArr['profile_pic'] = (isset($data[0]['profile_pic']) && $data[0]['profile_pic'] != "") ? SITE_URL_REMOTE . "/" . $data[0]['profile_pic'] : ""; 
          }else if($data[0]['social_profile_pic'] != ""){                 
            $resArr['profile_pic'] = (isset($data[0]['social_profile_pic']) && $data[0]['social_profile_pic'] != "") ? $data[0]['social_profile_pic'] : ""; 
          }else{
            $resArr['profile_pic'] = ""; 
          }

          $resArr['status']=$data[0]['status'];    
          $resArr['access_token']="$access_token";
          $resArr['created_at']=$data[0]['created_at'];
          $resArr['updated_at']=$data[0]['updated_at'];

          return $VAH->api_success($resArr,'Iniciar sesión exitosamente.');

        break;

        case "social_login":

          if (!isset($data['social_id']) || $data['social_id']=="") {
            return $VAH->error_invalid('Social Id'); 
          }
          if (!isset($data['social_type']) || $data['social_type']=="") {
            return $VAH->error_invalid('Social Type'); 
          }
          if (!isset($data['email_id']) || $data['email_id']=="") {
            //return $VAH->error_invalid('Email Id'); 
          }
          if (!isset($data['name']) || $data['name']=="") {
            //return $VAH->error_invalid('name'); 
          }       
          if (!isset($data['date_of_birth']) || $data['date_of_birth']=="") {
            //return $VAH->error_invalid('name'); 
          }
          if (!isset($data['gender']) || $data['gender']=="") {
            //return $VAH->error_invalid('gender'); 
          }
          if (!isset($data['profile_pic']) || $data['profile_pic']=="") {
            return $VAH->error_invalid('profile_pic'); 
          } 

          $SET = "";
          $WHERE = "";
          $SOCIAL = "";
          if($social_type == 'facebook'){
            $WHERE = "facebook_social_id == $social_id";
            $SET = " facebook_social_id = '".$social_id."',";
            $SOCIAL = " facebook_social_id = '".$social_id."' ";
          }else{
            $WHERE = "instagram_social_id = $social_id ";
            $SET = " instagram_social_id = '".$social_id."',";
            $SOCIAL = " instagram_social_id = '".$social_id."' ";
          }
          $EMAIL = "";
          if(isset($email_id) && $email_id !="") {
            $EMAIL = " OR email_id = '".@$email_id."'";
          }
            
          $resVal=$DB->SELECT("SELECT * 
                               FROM tblusers 
                               WHERE $SOCIAL $EMAIL ");
          $DOB = "";
          if(isset($date_of_birth) && $date_of_birth !=""){
            $DOB = "date_of_birth = '".@date('Y-m-d',strtotime($date_of_birth))."',";
          }
          if(empty($resVal)){
             
            $user_id=$DB->INSERT("INSERT INTO tblusers
                                  SET  name = '$name',
                                       gender = '".@$gender."',
                                       $DOB
                                       $SET
                                       email_id = '$email_id',
                                       social_profile_pic = '$profile_pic',
                                       has_social_account = '1',
                                       created_at = '".date('Y-m-d H:i:s')."',
                                       updated_at = '".date('Y-m-d H:i:s')."'");
           
          }else{
          
            $flag = "0";
            if($resVal[0]['facebook_social_id'] == $social_id){
              $flag = "1";
            }else if($resVal[0]['instagram_social_id'] == $social_id){
              $flag = "1";
            }  

            $profile_delail = "";
            if(isset($email_id) && $email_id != ""){
              $user= $DB->SELECT("SELECT *
                                  FROM tblusers 
                                  WHERE email_id = '".$email_id."'
                                        AND user_id <> '".$resVal[0]['user_id']."'");
              if(count($user) > 0){
                return $VAH->error_denied("Dirección de correo electrónico ya está en uso. Por favor prueba con otro correo electrónico.");
              }
              $profile_delail .=" email_id = '$email_id',";  
            }
            if(isset($name) && $name != ""){
              $profile_delail .=" name = '$name',";  
            }
            if(isset($date_of_birth) && $date_of_birth != ""){
              $profile_delail .=" date_of_birth = '".date('Y-m-d',strtotime($date_of_birth))."',";  
            }
            if(isset($gender) && $gender != ""){
              $profile_delail .=" gender = '".@$gender."',";  
            }

            if($flag == "0"){
              $DB->UPDATE("UPDATE tblusers
                           SET   $SET
                                 $profile_delail
                                 social_profile_pic = '$profile_pic',
                                 has_social_account = '1',
                                 updated_at = '".date('Y-m-d H:i:s')."'
                          WHERE user_id = '".$resVal[0]['user_id']."'");
            }else{
              $DB->UPDATE("UPDATE tblusers
                           SET   $profile_delail
                                 social_profile_pic = '$profile_pic',
                                 updated_at = '".date('Y-m-d H:i:s')."'
                          WHERE user_id = '".$resVal[0]['user_id']."'");

            }
            $user_id= $resVal[0]['user_id'];
          }    

          //--------------- GENARE ACCESS TOKEN FOR API SESSION ---------------//

          $access_token = access_token();
          $access_session = date("Y-m-d H:i:s", strtotime("+7 day"));
          $access_session = strtotime($access_session);
          $headers['device_type'] = $_SERVER['HTTP_DEVICETYPE'];
          $headers['device_id'] = $_SERVER['HTTP_DEVICEID'];
          if(isset($headers['device_type']) && isset($headers['device_id'])){
           
            $get_api_session = $DB->select("SELECT * 
                                            FROM tblapi_session
                                            WHERE user_id = '".$user_id."' 
                                                  AND device_type = '".$headers['device_type']."'
                                                  AND device_id = '".$headers['device_id']."' ");
            
            if(count($get_api_session) > 0){

              $updateQ=$DB->UPDATE("UPDATE tblapi_session
                                    SET  access_token ='".$access_token."',
                                         access_session ='".$access_session."',
                                         updated_at ='".date("Y-m-d H:i:s")."'
                                    WHERE  user_id = '".$user_id."'
                                            AND device_type = '".$headers['device_type']."'
                                            AND device_id = '".$headers['device_id']."'");
            }else{
              
              $inserQ=$DB->INSERT("INSERT INTO tblapi_session
                                   SET  access_token ='".$access_token."',
                                        access_session ='".$access_session."',
                                        created_at ='".date("Y-m-d H:i:s")."',
                                        updated_at='".date("Y-m-d H:i:s")."',
                                        user_id= '".$user_id."',
                                        device_type = '".$headers['device_type']."',
                                        device_id = '".$headers['device_id']."'");
            }

          }else{
            return $VAH->error_denied("Tipo de dispositivo inválido o identificación del dispositivo.");
          }    
              
          $data=$DB->SELECT("SELECT * 
                             FROM tblusers 
                             WHERE user_id='$user_id' 
                                   AND status='1'");                                
          $resArr = array();
          $resArr['user_id'] = $data[0]['user_id'];
          $resArr['name'] = (isset($data[0]['name']) && $data[0]['name'] != "") ? $data[0]['name'] : "";      
          $resArr['date_of_birth'] = (isset($data[0]['date_of_birth']) && $data[0]['date_of_birth'] != "") ? date('d-m-Y',strtotime($data[0]['date_of_birth'])) : ""; 
          $resArr['email_id'] = (isset($data[0]['email_id']) && $data[0]['email_id'] != "") ? $data[0]['email_id'] : "";                     
          $resArr['gender'] = (isset($data[0]['gender']) && $data[0]['gender'] != "") ? $data[0]['gender'] : "";
          
          if($data[0]['profile_pic'] != ""){                 
            $resArr['profile_pic'] = (isset($data[0]['profile_pic']) && $data[0]['profile_pic'] != "") ? SITE_URL_REMOTE . "/" . $data[0]['profile_pic'] : ""; 
          }else if($data[0]['social_profile_pic'] != ""){                 
            $resArr['profile_pic'] = (isset($data[0]['social_profile_pic']) && $data[0]['social_profile_pic'] != "") ? $data[0]['social_profile_pic'] : ""; 
          }else{
            $resArr['profile_pic'] = ""; 
          }

          $resArr['status']=$data[0]['status'];    
          $resArr['access_token']="$access_token";
          $resArr['created_at']=$data[0]['created_at'];
          $resArr['updated_at']=$data[0]['updated_at'];
              
          return $VAH->api_success($resArr,"Successfully Sign In With Social Media.");
       

        break; 
       
        case 'forgot_password':

          if (!isset($data['email_id']) || $data['email_id'] == '') {
            return $VAH->error_invalid('email_id');
          }

          //--------------- CHECK EMAIL --------------------//
            
          $userData = $DB->select("SELECT * 
                                   FROM tblusers 
                                   WHERE  email_id = '". $email_id ."' 
                                          AND status = 1");
          
          if (count($userData) == 0) {
            return $VAH->error_denied('Usuario no registrado.');
          }else if (count($userData) > 0) {

            //--------------- SENT EMAIL FOR CHANGE PASSWORD --------------------//

            $userData = $userData[0];
            $refid = md5(rand(1000, 123456789456789));
            $isUpdate = $DB->update("UPDATE tblusers 
                                     SET ref_key = '" . $refid . "',
                                         updated_at = '" . date('Y-m-d H:i:s') . "' 
                                     WHERE email_id = '" . $userData['email_id'] . "'");
            $url = SITE_URL_REMOTE .'/'. "change_password.php?email_id=" . base64_encode($userData['email_id']) . "&ref_key=" . $refid;
            $subject = "Your App:Forgot password request";
            $from = "estuardo@aumenta.do";
            $recipient = $email_id.",test@gmail.com";

            $mess = "<b>Hello  : </b>".$userData['name']."  <br><br>"
                    ."<p>We have received the request to change the password for the user : <b>" .$userData['email_id']. " </b></p>"    
                    ."<p>To change the password, please enter the following Link : "
                    ."<a href='".$url."'>(Change Password)</a></p>"
                    ."<p>In this link you can update the password for this user.</p>"
                    ."<p><b>Note : </b></p><p>This link will be useful only once.</p>"    
                    ."<p>Your App</p>" ;  
           
            $VAH->send_mail_with_headers($subject, $from, $recipient, $mess, TRUE);

            //--------------- API SUCCESS RESPONSE ---------------//

            $resArr = array();
            return $VAH->api_success($resArr, 'Se ha enviado un enlace de restablecimiento de contraseña a su correo electrónico.');
          }

        break;

        case "update_user_profile":

          if (!isset($data['user_id']) || $data['user_id'] == '') {
              return $VAH->error_invalid('user_id');
          }
          if (!isset($data['name']) || $data['name'] == '') {
              //return $VAH->error_invalid('first_name');
          }
          if (!isset($data['date_of_birth']) || $data['date_of_birth'] == '') {
              //return $VAH->error_invalid('date_of_birth');
          }
          if (!isset($data['gender']) || $data['gender'] == '') {
              //return $VAH->error_invalid('gender');
          }

          $user=getUserData($user_id);
          if(count($user) == "0")
          {
            return $VAH->error_denied('Usuario no disponible.');
          }  

          
          //--------------- UPLOAD PROFILE PIC --------------------//

          $set = "";
          if(!empty($_FILES)){
            if(isset($user[0]['profile_pic']) && $user[0]['profile_pic'] !=""){
              if (file_exists('../'.$user[0]['profile_pic'])) {
                unlink('../'.$user[0]['profile_pic']);
              }  
            }   

            $key = 'profile_pic';
            if (file_exists($_FILES[$key]['tmp_name']) || is_uploaded_file($_FILES[$key]['tmp_name'])) {
              if (!file_exists('../uploads')) {
                mkdir('../uploads', 0777, true);
              }
              if (!file_exists('../uploads/user_profile')) {
                mkdir('../uploads/user_profile', 0777, true);
              }
              $photo =  "uploads/user_profile/".strtotime(date("Y-m-d H:i:s"))."".uniqid() ."_".$user_id."_userpic.png";
              if (file_exists("../$photo")) {
                      unlink("../$photo");
              }
              move_uploaded_file($_FILES[$key]['tmp_name'], "../$photo");
            }
            $set.=" profile_pic = '".$photo."' ,";
          }      

          if(isset($name) && $name !=""){
            $set.=" name = '".$name."' ,";
          }

          if(isset($date_of_birth) && $date_of_birth !=""){
            $set.=" date_of_birth = '".date('Y-m-d',strtotime($date_of_birth))."' ,";
          }

          if(isset($gender) && $gender !=""){
            $set.=" gender = '".$gender."' ,";
          } 

                     
          $updatedata=$DB->UPDATE("UPDATE `tblusers` 
                                   SET $set
                                       updated_at='".date("Y-m-d H:i:s")."' 
                                   WHERE `user_id`= '".$user_id."' ");

          if(!empty($updatedata)){

            $data=$DB->SELECT("SELECT * 
                               FROM tblusers 
                               WHERE  user_id = $user_id");

            //--------------- API SUCCESS RESPONSE ---------------//

            $resArr = array();
            $resArr['user_id'] = $data[0]['user_id'];
            $resArr['name'] = $data[0]['name'];
            $resArr['date_of_birth'] = (isset($data[0]['date_of_birth']) && $data[0]['date_of_birth'] != "") ? date('d-m-Y',strtotime($data[0]['date_of_birth'])) : ""; 
            $resArr['email_id'] = (isset($data[0]['email_id']) && $data[0]['email_id'] != "") ? $data[0]['email_id'] : "";                  
            $resArr['gender'] = (isset($data[0]['gender']) && $data[0]['gender'] != "") ? $data[0]['gender'] : "";     
            if($data[0]['profile_pic'] != ""){                 
              $resArr['profile_pic'] = (isset($data[0]['profile_pic']) && $data[0]['profile_pic'] != "") ? SITE_URL_REMOTE . "/" . $data[0]['profile_pic'] : ""; 
            }else if($data[0]['social_profile_pic'] != ""){                 
              $resArr['profile_pic'] = (isset($data[0]['social_profile_pic']) && $data[0]['social_profile_pic'] != "") ? $data[0]['social_profile_pic'] : ""; 
            }else{
              $resArr['profile_pic'] = ""; 
            }
            $resArr['status']=$data[0]['status']; 
            $resArr['access_token']="";
            $resArr['created_at']=$data[0]['created_at'];
            $resArr['updated_at']=$data[0]['updated_at'];

            return $VAH->api_success($resArr, "Perfil actualizado con éxito.");
          }else{
            return $VAH->error_denied("Perfil no actualizado.");
          }

        break;

        case 'register_for_push':
           
          if (!isset($data['user_id']) && !isset($data['user_id'])) {
              return $VAH->error_invalid('user_id');
          }
          if (!isset($data['device_token']) || $data['device_token'] == '') {
              return $VAH->error_invalid('device_token');
          }
          if (!isset($data['certificate_type']) || $data['certificate_type'] == '') {
              return $VAH->error_invalid('certificate_type');
          }

          $user = getUserData($user_id);
          if (count($user) == 0) {
              return $VAH->error_denied('Usuario no disponible.');
          }

          $headers['device_type'] = $_SERVER['HTTP_DEVICETYPE'];
          $headers['device_id'] = $_SERVER['HTTP_DEVICEID'];
          $pushData = $DB->select("SELECT * 
                                   FROM tblpush_user 
                                   WHERE user_id='".$user_id."' 
                                         AND device_type = '".$headers['device_type']."' 
                                         AND status=1");

          if (count($pushData) > 0) {
            $DB->delete("DELETE 
                         FROM tblpush_user 
                         WHERE push_user_id = '" . $pushData[0]['push_user_id'] . "'");
          }

          $insertPush = $DB->insert("INSERT INTO tblpush_user
                                     SET user_id = '".$user_id."',
                                         device_id = '" . $headers['device_id'] . "',
                                         device_token = '" . $device_token . "',
                                         device_type = '" . $headers['device_type'] . "',
                                         certificate_type = '" . $certificate_type . "',
                                         status = '1',
                                         created_at = '" . date("Y-m-d H:i:s") . "',
                                         updated_at = '" . date("Y-m-d H:i:s") . "'");
          
          $resArr = array();
          return $VAH->api_success($resArr,'Registrarse para empujar con éxito.');

        break; 



        case "contact_us":

          if (!isset($data['user_id']) || $data['user_id']=="") {
            return $VAH->error_invalid('user_id'); 
          }
          if (!isset($data['subject']) || $data['subject']=="") {
            return $VAH->error_invalid('subject');
          }
          if (!isset($data['message']) || $data['message']=="") {
            return $VAH->error_invalid('Message');
          }

          $user = getUserData($user_id);
          if (count($user) == 0) {
            return $VAH->error_denied('Usuario no disponible.');
          }  
            
          $mail_subject = "Your App:Contact Us";
          $from = "estuardo@aumenta.do";
          $recipient ="test@gmail.com,estuardo@aumenta.do";
          $message = "Subject: ".$subject."<br> <br> FROM: ".$user[0]['name']." ( ".$user[0]['email_id']." ) <br> <br> ".$message;
          $VAH->send_mail_with_headers($mail_subject, $from, $recipient, $message, TRUE);
          $resArr = array();             
          return $VAH->api_success($resArr,"Su solicitud será enviada, el equipo de Your App se pondrá en contacto con usted en breve.");
        

        break;

        case 'logout_user':

          if (!isset($data['user_id']) || $data['user_id'] == '') {
              return $VAH->error_invalid('user_id');
          }
          if (!isset($data['device_type']) || $data['device_type'] == '') {
              return $VAH->error_invalid('device_type');
          }

          $user = getUserData($user_id);
          if (count($user) == 0) {
              return $VAH->error_denied('Usuario no disponible.');
          }

          $resVal = $DB->select("SELECT * 
                                 FROM tblpush_user 
                                 WHERE user_id='".$user_id."' 
                                       AND device_type = '".$device_type."' 
                                       AND status=1");

          if (count($resVal) > 0) {
              $DB->delete("DELETE 
                           FROM tblpush_user 
                           WHERE push_user_id = '".$resVal[0]['push_user_id']."'");
          }
          $resArr = array();
          return $VAH->api_success($resArr,'Salir con éxito.');

        break;

        //--------------- GENERAL SERVICES END --------------------//

        //--------------- MUSEUM SERVICES --------------------//

        case "get_museum":

          if (!isset($data['user_id']) || $data['user_id'] == '') {
            return $VAH->error_invalid('user_id');
          } 
          if (!isset($data['type']) || $data['type'] == '') {
            return $VAH->error_invalid('Type');
          } 
          if (!isset($data['latitude']) || $data['latitude'] == '') {
            if (isset($data['type']) || $data['type'] == '3') {
              return $VAH->error_invalid('Latitude');
            }  
          } 
          if (!isset($data['longitude']) || $data['longitude'] == '') {
            if (isset($data['type']) || $data['type'] == '3') {
              return $VAH->error_invalid('Longitude');
            }  
          } 

          $user = getUserData($user_id);
          if (count($user) == 0) {
            return $VAH->error_denied('Usuario no disponible.');
          }
          
          $finelArr = array();


          if($type == '1'){

            //--------------- GET MUSEUM BY AREA --------------------//

            $resData =$DB->SELECT("SELECT * FROM tblarea");
            $j= 0;

            if(count($resData) > 0){
              foreach ($resData as $key => $val) {

                $resVal =$DB->SELECT("SELECT *
                                      FROM tblmuseum m
                                      LEFT JOIN tblarea a ON  a.area_id = m.area_id
                                      WHERE m.area_id = '".$val['area_id']."'");

                $resArr = array();
                if (count($resVal) > 0) {
                  $i = 0;
                  foreach ($resVal as $value) {
                    $resArr[$i]['museum_id'] = $value['museum_id'];
                    $resArr[$i]['name'] = $value['name'];          
                    $resArr[$i]['image'] = (isset($value['image']) && $value['image'] != "") ? SITE_URL_REMOTE . "/" . $value['image'] : "";
                    $resArr[$i]['distance'] = (isset($value['distance']) && $value['distance'] != "") ? number_format($value['distance'], 2, '.', ',') : "";
                    $i++;
                  }
                  $finelArr[$j]['area_id'] = $val['area_id'];
                  $finelArr[$j]['area_name'] = $val['area_name'];
                  $finelArr[$j]['number_of_museum'] = (string) count($resVal);
                  $finelArr[$j]['museum'] = $resArr;
                }
                $j++;  
              }  
            }
          }else if($type == '2'){

            //--------------- GET MUSEUM BY ALPHABET --------------------//

            $resVal =$DB->SELECT("SELECT *,
                                          (((acos(sin(( '" . @$latitude . "' *pi()/180)) * sin((m.latitude*pi()/180)) + 
                                             cos(( '" . @$latitude . "' *pi()/180)) * cos((m.latitude*pi()/180)) * 
                                             cos((( '" . @$longitude . "' - m.longitude) * pi()/180))))*180/pi())*60*1.1515*1.609344) 
                                          as distance
                                  FROM tblmuseum m
                                  LEFT JOIN tblarea a ON  a.area_id = m.area_id
                                  ORDER BY m.name");

            $resArr = array();
            if (count($resVal) > 0) {
              $i = 0;
              foreach ($resVal as $value) {
                $finelArr[$i]['museum_id'] = $value['museum_id'];
                $finelArr[$i]['name'] = $value['name'];          
                $finelArr[$i]['image'] = (isset($value['image']) && $value['image'] != "") ? SITE_URL_REMOTE . "/" . $value['image'] : "";
                $finelArr[$i]['distance'] = (isset($value['distance']) && $value['distance'] != "") ? number_format($value['distance'], 2, '.', ',') : "";
                $i++;
              }
            }
          }else if($type == '3'){

            //--------------- GET MUSEUM BY LOCATION --------------------//

            $resVal =$DB->SELECT("SELECT *,
                                          (((acos(sin(( '" . @$latitude . "' *pi()/180)) * sin((m.latitude*pi()/180)) + 
                                             cos(( '" . @$latitude . "' *pi()/180)) * cos((m.latitude*pi()/180)) * 
                                             cos((( '" . @$longitude . "' - m.longitude) * pi()/180))))*180/pi())*60*1.1515*1.609344) 
                                          as distance   
                                  FROM tblmuseum m
                                  LEFT JOIN tblarea a ON  a.area_id = m.area_id
                                  ORDER BY distance");

            $resArr = array();
            if (count($resVal) > 0) {
              $i = 0;
              foreach ($resVal as $value) {
                $finelArr[$i]['museum_id'] = $value['museum_id'];
                $finelArr[$i]['name'] = $value['name'];          
                $finelArr[$i]['image'] = (isset($value['image']) && $value['image'] != "") ? SITE_URL_REMOTE . "/" . $value['image'] : "";
                $finelArr[$i]['distance'] = (isset($value['distance']) && $value['distance'] != "") ? number_format($value['distance'], 2, '.', ',') : "";
                $i++;
              }
            }
          }        
          return $VAH->api_success($finelArr,"Museo recuperado.");

        break;

        case "get_museum_by_id":

          if (!isset($data['user_id']) || $data['user_id'] == '') {
            return $VAH->error_invalid('user_id');
          }
          if (!isset($data['museum_id']) || $data['museum_id'] == '') {
            return $VAH->error_invalid('museum_id');
          }
         
          $user = getUserData($user_id);
          if (count($user) == 0) {
              return $VAH->error_denied('User not available.');
          }

         
          $resVal = $DB->SELECT("SELECT *
                                 FROM tblmuseum m
                                 LEFT JOIN tblarea a ON  a.area_id = m.area_id
                                 WHERE museum_id = '".$museum_id."'");
          $resArr = array();
          if (count($resVal) > 0) {
            $resArr['museum_id'] = $resVal[0]['museum_id'];
            $resArr['name'] =  $resArr['name'] = (isset($resVal[0]['name']) && $resVal[0]['name'] != "") ? $resVal[0]['name'] : "";  
            $resArr['image'] = (isset($resVal[0]['image']) && $resVal[0]['image'] != "") ? SITE_URL_REMOTE . "/" . $resVal[0]['image'] : "";
            $resArr['short_description'] = (isset($resVal[0]['short_description']) && $resVal[0]['short_description'] != "") ? $resVal[0]['short_description'] : ""; 
            $resArr['description'] = (isset($resVal[0]['description']) && $resVal[0]['description'] != "") ? $resVal[0]['description'] : ""; 
            $resArr['address'] = (isset($resVal[0]['address']) && $resVal[0]['address'] != "") ? $resVal[0]['address'] : "";  
            $resArr['latitude'] = (isset($resVal[0]['latitude']) && $resVal[0]['latitude'] != "") ? $resVal[0]['latitude'] : ""; 
            $resArr['longitude'] = (isset($resVal[0]['longitude']) && $resVal[0]['longitude'] != "") ? $resVal[0]['longitude'] : "";  
            $resArr['phone'] = (isset($resVal[0]['phone']) && $resVal[0]['phone'] != "") ? $resVal[0]['phone'] : "";
            $resArr['email'] = (isset($resVal[0]['email']) && $resVal[0]['email'] != "") ? $resVal[0]['email'] : "";  
            $resArr['website'] = (isset($resVal[0]['website']) && $resVal[0]['website'] != "") ? $resVal[0]['website'] : "";
            $resArr['schedule'] = (isset($resVal[0]['schedule']) && $resVal[0]['schedule'] != "") ? $resVal[0]['schedule'] : ""; 
            $resArr['closed'] = (isset($resVal[0]['closed']) && $resVal[0]['closed'] != "") ? $resVal[0]['closed'] : ""; 
            $resArr['admission'] = (isset($resVal[0]['admission']) && $resVal[0]['admission'] != "") ? $resVal[0]['admission'] : ""; 
            $resArr['note'] = (isset($resVal[0]['note']) && $resVal[0]['note'] != "") ? $resVal[0]['note'] : ""; 
            $resArr['instagram_link'] = (isset($resVal[0]['instagram_link']) && $resVal[0]['instagram_link'] != "") ? $resVal[0]['instagram_link'] : "";
            $resArr['facebook_link'] = (isset($resVal[0]['facebook_link']) && $resVal[0]['facebook_link'] != "") ? $resVal[0]['facebook_link'] : "";  
            $resArr['twitter_link'] = (isset($resVal[0]['twitter_link']) && $resVal[0]['twitter_link'] != "") ? $resVal[0]['twitter_link'] : "";  
            $resArr['pinterest_link'] = (isset($resVal[0]['pinterest_link']) && $resVal[0]['pinterest_link'] != "") ? $resVal[0]['pinterest_link'] : "";  
            $resArr['youtube_link'] = (isset($resVal[0]['youtube_link']) && $resVal[0]['youtube_link'] != "") ? $resVal[0]['youtube_link'] : ""; 
            
            $museumVal = $DB->SELECT("SELECT *
                                     FROM tblmuseum m
                                     LEFT JOIN tblarea a ON  a.area_id = m.area_id
                                     WHERE m.area_id = '".$resVal[0]['area_id']."'
                                           AND museum_id <> '".$resVal[0]['museum_id']."'
                                     LIMIT 10");

            $museumArrr = array();
            if (count($museumVal) > 0) {
              $j = 0;
              foreach ($museumVal as  $val) {
                $museumArrr[$j]['museum_id'] = $val['museum_id'];
                $museumArrr[$j]['name'] = $val['name'];
                $museumArrr[$j]['image'] = (isset($val['image']) && $val['image'] != "") ? SITE_URL_REMOTE . "/" . $val['image'] : "";
                $j++;
              }
            } 

            $resArr['other_museum'] = (!empty($museumArrr) && count($museumArrr) > 0) ? $museumArrr : array(); 
          }    
        
          return $VAH->api_success((object) $resArr,"Museo recuperado.");
            
        break;

        case "get_diary":

          if (!isset($data['user_id']) || $data['user_id'] == '') {
            return $VAH->error_invalid('user_id');
          } 
          if (!isset($data['search_text']) || $data['search_text'] == '') {
              //return $VAH->error_invalid('Search Text');
          }
          if (!isset($data['type']) || $data['type'] == '') {
            return $VAH->error_invalid('type');
          } 
          if (!isset($data['start_time']) || $data['start_time'] == '') {
            if(isset($type) && $type =="1"){
              return $VAH->error_invalid('start_time');
            }  
          }
          if (!isset($data['end_time']) || $data['end_time'] == '') {
            if(isset($type) && $type =="1"){
              return $VAH->error_invalid('end_time');
            }  
          } 
          
          $user = getUserData($user_id);
          if (count($user) == 0) {
            return $VAH->error_denied('Usuario no disponible.');
          }
          
          $finelArr = array();
          $WHERE ="";
          $AND ="";

          if($type == "0"){
            $WHERE = "";
            if(isset($search_text) && $search_text !=""){
              $WHERE = "WHERE name Like '%".@$search_text."%'
                              OR short_description Like '%".@$search_text."%'";
            }  
            $resData =$DB->SELECT("SELECT * 
                                   FROM tblmuseum
                                   $WHERE 
                                   ORDER BY name");
           
            $j= 0;

            if(count($resData) > 0){
              foreach ($resData as  $val) {

                $activityVal =$DB->SELECT("SELECT *
                                           FROM tblactivity
                                           WHERE museum_id = '".$val['museum_id']."'");

                $activityArr = array();
                if (count($activityVal) > 0) {
                  $i = 0;
                  foreach ($activityVal as $value) {
                    $activityArr[$i]['activity_id'] = $value['activity_id'];
                    $activityArr[$i]['activity_title'] = $value['activity_title'];
                    $activityArr[$i]['start_time'] = $value['start_time'];   
                    $activityArr[$i]['end_time'] = $value['end_time'];          
                    $activityArr[$i]['activity_title'] = (isset($value['activity_title']) && $value['activity_title'] != "") ? $value['activity_title'] : "";
                    $i++;
                  }
                }

                $exhibitionVal =$DB->SELECT("SELECT *
                                             FROM tblexhibition
                                             WHERE museum_id = '".$val['museum_id']."'");

                $exhibitionArr = array();
                if (count($exhibitionVal) > 0) {
                  $i = 0;
                  foreach ($exhibitionVal as $value) {
                    $exhibitionArr[$i]['exhibition_id'] = $value['exhibition_id'];
                    $exhibitionArr[$i]['exhibition_title'] = $value['exhibition_title'];
                    $exhibitionArr[$i]['start_time'] = $value['start_time'];   
                    $exhibitionArr[$i]['end_time'] = $value['end_time']; 
                    $i++;
                  }
                }
                if(!empty($activityArr) || !empty($exhibitionArr)){
                  $finelArr[$j]['museum_id'] = $val['museum_id'];
                  $finelArr[$j]['name'] = $val['name'];
                  $finelArr[$j]['activity'] = $activityArr;
                  $finelArr[$j]['exhibition'] = $exhibitionArr;
                  $j++; 
                }  
              }  
            }
          } 
          
          if($type == "1"){
            $resData =$DB->SELECT("SELECT * 
                                   FROM tblmuseum
                                   ORDER BY name");

            $j= 0;

            if(count($resData) > 0){
              foreach ($resData as  $val) {

                $activityVal =$DB->SELECT("SELECT *
                                           FROM tblactivity
                                           WHERE museum_id = '".$val['museum_id']."'
                                                 AND start_time >= '".@$start_time."'
                                                 AND start_time <= '".@$end_time."'");

                $activityArr = array();
                if (count($activityVal) > 0) {
                  $i = 0;
                  foreach ($activityVal as $value) {
                    $activityArr[$i]['activity_id'] = $value['activity_id'];
                    $activityArr[$i]['activity_title'] = $value['activity_title'];
                    $activityArr[$i]['start_time'] = $value['start_time'];   
                    $activityArr[$i]['end_time'] = $value['end_time'];          
                    $activityArr[$i]['activity_title'] = (isset($value['activity_title']) && $value['activity_title'] != "") ? $value['activity_title'] : "";
                    $i++;
                  }
                }
                
                $exhibitionVal =$DB->SELECT("SELECT *
                                             FROM tblexhibition
                                             WHERE museum_id = '".$val['museum_id']."'
                                                   AND start_time >= '".@$start_time."'
                                                   AND end_time <= '".@$end_time."'");

                $exhibitionArr = array();
                if (count($exhibitionVal) > 0) {
                  $i = 0;
                  foreach ($exhibitionVal as $value) {
                    $exhibitionArr[$i]['exhibition_id'] = $value['exhibition_id'];
                    $exhibitionArr[$i]['exhibition_title'] = $value['exhibition_title'];
                    $exhibitionArr[$i]['start_time'] = $value['start_time'];   
                    $exhibitionArr[$i]['end_time'] = $value['end_time']; 
                    $i++;
                  }
                }
                if(!empty($activityArr) || !empty($exhibitionArr)){
                  $finelArr[$j]['museum_id'] = $val['museum_id'];
                  $finelArr[$j]['name'] = $val['name'];
                  $finelArr[$j]['activity'] = $activityArr;
                  $finelArr[$j]['exhibition'] = $exhibitionArr;
                  $j++;  
                }  
              }  
            }
          }

          if($type == "2"){

            $WHERE = "";
            if(isset($search_text) && $search_text !=""){
              $WHERE = "WHERE name Like '%".@$search_text."%'
                              OR short_description Like '%".@$search_text."%'";
            }  
            
            $resData =$DB->SELECT("SELECT * 
                                   FROM tblmuseum
                                   $WHERE
                                   ORDER BY name");
          
            $j= 0;

            if(count($resData) > 0){
              foreach ($resData as  $val) {

                $activityVal =$DB->SELECT("SELECT *
                                           FROM tblactivity
                                           WHERE museum_id = '".$val['museum_id']."'");

                $activityArr = array();
                if (count($activityVal) > 0) {
                  $i = 0;
                  foreach ($activityVal as $value) {
                    $activityArr[$i]['activity_id'] = $value['activity_id'];
                    $activityArr[$i]['activity_title'] = $value['activity_title'];
                    $activityArr[$i]['start_time'] = $value['start_time'];   
                    $activityArr[$i]['end_time'] = $value['end_time'];          
                    $activityArr[$i]['activity_title'] = (isset($value['activity_title']) && $value['activity_title'] != "") ? $value['activity_title'] : "";
                    $i++;
                  }
                }

                $exhibitionVal =$DB->SELECT("SELECT *
                                             FROM tblexhibition
                                             WHERE museum_id = '".$val['museum_id']."'");

                $exhibitionArr = array();
                if (count($exhibitionVal) > 0) {
                  $i = 0;
                  foreach ($exhibitionVal as $value) {
                    $exhibitionArr[$i]['exhibition_id'] = $value['exhibition_id'];
                    $exhibitionArr[$i]['exhibition_title'] = $value['exhibition_title'];
                    $exhibitionArr[$i]['start_time'] = $value['start_time'];   
                    $exhibitionArr[$i]['end_time'] = $value['end_time']; 
                    $i++;
                  }
                }
                if(!empty($activityArr) || !empty($exhibitionArr)){
                  $finelArr[$j]['museum_id'] = $val['museum_id'];
                  $finelArr[$j]['name'] = $val['name'];
                  $finelArr[$j]['activity'] = $activityArr;
                  $finelArr[$j]['exhibition'] = $exhibitionArr;
                  $j++;  
                }  
              }  
            }
          } 

          if($type == "3"){
            
            $resData =$DB->SELECT("SELECT * 
                                   FROM tblmuseum
                                   ORDER BY name");

            $j= 0;

            if(count($resData) > 0){
              foreach ($resData as  $val) {

               

                $activityArr = array();

                $AND = "";
                if(isset($search_text) && $search_text !=""){
                  $AND = "AND (exhibition_title Like '%".@$search_text."%'
                               OR exhibition_short_description Like '%".@$search_text."%')";
                }  

                $exhibitionVal =$DB->SELECT("SELECT *
                                             FROM tblexhibition
                                             WHERE museum_id = '".$val['museum_id']."'
                                                   $AND");

                $exhibitionArr = array();
                if (count($exhibitionVal) > 0) {
                  $i = 0;
                  foreach ($exhibitionVal as $value) {
                    $exhibitionArr[$i]['exhibition_id'] = $value['exhibition_id'];
                    $exhibitionArr[$i]['exhibition_title'] = $value['exhibition_title'];
                    $exhibitionArr[$i]['start_time'] = $value['start_time'];   
                    $exhibitionArr[$i]['end_time'] = $value['end_time']; 
                    $i++;
                  }
                }
                  
                if(!empty($activityArr) || !empty($exhibitionArr)){
                  $finelArr[$j]['museum_id'] = $val['museum_id'];
                  $finelArr[$j]['name'] = $val['name'];
                  $finelArr[$j]['activity'] = $activityArr;
                  $finelArr[$j]['exhibition'] = $exhibitionArr;
                  $j++;
                } 
              }  
            }
          }

          if($type == "4"){
            
            $resData =$DB->SELECT("SELECT * 
                                   FROM tblmuseum
                                   ORDER BY name");

            $j= 0;

            if(count($resData) > 0){
              foreach ($resData as  $val) {

               
                $AND = "";
                if(isset($search_text) && $search_text !=""){
                  $AND = "AND (activity_title Like '%".@$search_text."%'
                               OR activity_short_description Like '%".@$search_text."%')";
                }  
                $activityVal =$DB->SELECT("SELECT *
                                           FROM tblactivity
                                           WHERE museum_id = '".$val['museum_id']."'
                                                 $AND");

                $activityArr = array();
                if (count($activityVal) > 0) {
                  $i = 0;
                  foreach ($activityVal as $value) {
                    $activityArr[$i]['activity_id'] = $value['activity_id'];
                    $activityArr[$i]['activity_title'] = $value['activity_title'];
                    $activityArr[$i]['start_time'] = $value['start_time'];   
                    $activityArr[$i]['end_time'] = $value['end_time'];          
                    $activityArr[$i]['activity_title'] = (isset($value['activity_title']) && $value['activity_title'] != "") ? $value['activity_title'] : "";
                    $i++;
                  }
                }
                $exhibitionArr = array();
                  
                if(!empty($activityArr) || !empty($exhibitionArr)){
                  $finelArr[$j]['museum_id'] = $val['museum_id'];
                  $finelArr[$j]['name'] = $val['name'];
                  $finelArr[$j]['activity'] = $activityArr;
                  $finelArr[$j]['exhibition'] = $exhibitionArr;
                  $j++; 
                }
              }  
            }
          }
          
          return $VAH->api_success($finelArr,"My agenda recuperado.");

        break;

        case "get_diary_by_id":

          if (!isset($data['user_id']) || $data['user_id'] == '') {
            return $VAH->error_invalid('user_id');
          } 
          if (!isset($data['id']) || $data['id'] == '') {
            return $VAH->error_invalid('id');
          } 
          if (!isset($data['type']) || $data['type'] == '') {
            return $VAH->error_invalid('type');
          } 
          

          $user = getUserData($user_id);
          if (count($user) == 0) {
            return $VAH->error_denied('Usuario no disponible.');
          }


          if($type == "0"){
            $resVal =$DB->SELECT("SELECT *
                                  FROM tblactivity
                                  WHERE activity_id = '".$id."'");
          }else{
            $resVal =$DB->SELECT("SELECT *
                                  FROM tblexhibition
                                  WHERE exhibition_id = '".$id."'");
          }
          
          $resArr = array();
          if (count($resVal) > 0) {
            $i = 0;
            foreach ($resVal as $value) {
              if($type == "0"){
                $resArr[$i]['id'] =(isset($value['activity_id']) && $value['activity_id'] != "") ? $value['activity_id'] : "" ;
              }else{
                $resArr[$i]['id'] =(isset($value['exhibition_id']) && $value['exhibition_id'] != "") ? $value['exhibition_id'] : ""; 
              }

              $resArr[$i]['image'] =(isset($value['image']) && $value['image'] != "") ? SITE_URL_REMOTE . "/" . $value['image'] : ""; 

              if($type == "0"){
                $resArr[$i]['title'] =(isset($value['activity_title']) && $value['activity_title'] != "") ? $value['activity_title'] : "";
              }else{
                $resArr[$i]['title'] =(isset($value['exhibition_title']) && $value['exhibition_title'] != "") ? $value['exhibition_title'] : ""; 
              }

              if($type == "0"){
                $resArr[$i]['short_description'] =(isset($value['activity_short_description']) && $value['activity_short_description'] != "") ? $value['activity_short_description'] : "";
              }else{
                $resArr[$i]['short_description'] =(isset($value['exhibition_short_description']) && $value['exhibition_short_description'] != "") ? $value['exhibition_short_description'] : ""; 
              }

              if($type == "0"){
                $resArr[$i]['description'] =(isset($value['activity_description']) && $value['activity_description'] != "") ? $value['activity_description'] : "";  
              }else{
                $resArr[$i]['description'] =(isset($value['exhibition_description']) && $value['exhibition_description'] != "") ? $value['exhibition_description'] : "";  
              }

              $resArr[$i]['start_time'] =(isset($value['start_time']) && $value['start_time'] != "") ? $value['start_time'] : "";  $value['start_time'];
              $resArr[$i]['end_time'] =(isset($value['end_time']) && $value['end_time'] != "") ? $value['end_time'] : "";  $value['end_time'];
              $i++;
            }
          }
          return $VAH->api_success($resArr,"My agenda recuperado.");

        break;

        case "get_transport":

          if (!isset($data['user_id']) || $data['user_id'] == '') {
            return $VAH->error_invalid('user_id');
          } 
          if (!isset($data['latitude']) || $data['latitude'] == '') {
            return $VAH->error_invalid('Latitude');
          } 
          if (!isset($data['longitude']) || $data['longitude'] == '') {
            return $VAH->error_invalid('Longitude');
          } 
          

          $user = getUserData($user_id);
          if (count($user) == 0) {
            return $VAH->error_denied('Usuario no disponible.');
          }
          
          $resVal =$DB->SELECT("SELECT *,
                                        (((acos(sin(( '" . @$latitude . "' *pi()/180)) * sin((latitude*pi()/180)) + 
                                           cos(( '" . @$latitude . "' *pi()/180)) * cos((latitude*pi()/180)) * 
                                           cos((( '" . @$longitude . "' - longitude) * pi()/180))))*180/pi())*60*1.1515*1.609344) 
                                        as distance   
                                FROM tbltransport
                                ORDER BY distance");

          $resArr = array();
          if (count($resVal) > 0) {
            $i = 0;
            foreach ($resVal as $value) {
              $resArr[$i]['transport_id'] = $value['transport_id'];
              $resArr[$i]['transport_title'] = $value['transport_title'];
              $resArr[$i]['transport_address'] = (isset($value['transport_address']) && $value['transport_address'] != "") ? $value['transport_address'] : "";
              $resArr[$i]['latitude'] = (isset($value['latitude']) && $value['latitude'] != "") ? $value['latitude'] : "";
              $resArr[$i]['longitude'] = (isset($value['longitude']) && $value['longitude'] != "") ? $value['longitude'] : "";
              $resArr[$i]['distance'] = (isset($value['distance']) && $value['distance'] != "") ? number_format($value['distance'], 2, '.', ',') : "";
              $i++;
            }
          }

          return $VAH->api_success($resArr,"Zona recuperado.");

        break;

        case "get_parking":

          if (!isset($data['user_id']) || $data['user_id'] == '') {
            return $VAH->error_invalid('user_id');
          } 
          if (!isset($data['latitude']) || $data['latitude'] == '') {
            return $VAH->error_invalid('Latitude');
          } 
          if (!isset($data['longitude']) || $data['longitude'] == '') {
            return $VAH->error_invalid('Longitude');
          } 
          

          $user = getUserData($user_id);
          if (count($user) == 0) {
            return $VAH->error_denied('Usuario no disponible.');
          }
          
          $resVal =$DB->SELECT("SELECT *,
                                        (((acos(sin(( '" . @$latitude . "' *pi()/180)) * sin((latitude*pi()/180)) + 
                                           cos(( '" . @$latitude . "' *pi()/180)) * cos((latitude*pi()/180)) * 
                                           cos((( '" . @$longitude . "' - longitude) * pi()/180))))*180/pi())*60*1.1515*1.609344) 
                                        as distance   
                                FROM tblparking
                                ORDER BY distance");

          $resArr = array();
          if (count($resVal) > 0) {
            $i = 0;
            foreach ($resVal as $value) {
              $resArr[$i]['parking_id'] = $value['parking_id'];
              $resArr[$i]['parking_title'] = $value['parking_title'];
              $resArr[$i]['parking_address'] = (isset($value['parking_address']) && $value['parking_address'] != "") ? $value['parking_address'] : "";
              $resArr[$i]['latitude'] = (isset($value['latitude']) && $value['latitude'] != "") ? $value['latitude'] : "";
              $resArr[$i]['longitude'] = (isset($value['longitude']) && $value['longitude'] != "") ? $value['longitude'] : "";
              $resArr[$i]['distance'] = (isset($value['distance']) && $value['distance'] != "") ? number_format($value['distance'], 2, '.', ',') : "";
              $i++;
            }
          }

          return $VAH->api_success($resArr,"Parqueos recuperado.");

        break;

        case "get_sponsor":

          if (!isset($data['user_id']) || $data['user_id'] == '') {
            return $VAH->error_invalid('user_id');
          }
          

          $user = getUserData($user_id);
          if (count($user) == 0) {
            return $VAH->error_denied('Usuario no disponible.');
          }
          
          $resVal =$DB->SELECT("SELECT *
                                FROM tblsponsor");

          $resArr = array();
          if (count($resVal) > 0) {
            $i = 0;
            foreach ($resVal as $value) {
              $resArr[$i]['sponsor_id'] = $value['sponsor_id'];
              $resArr[$i]['sponsor_image'] = (isset($value['sponsor_image']) && $value['sponsor_image'] != "") ? SITE_URL_REMOTE . "/" . $value['sponsor_image'] : "";
              $resArr[$i]['sponsor_link'] = (isset($value['sponsor_link']) && $value['sponsor_link'] != "") ? $value['sponsor_link'] : "";
              $i++;
            }
          }

          return $VAH->api_success($resArr,"Parqueos recuperado.");

        break;

        case "get_organizer":

          if (!isset($data['user_id']) || $data['user_id'] == '') {
            return $VAH->error_invalid('user_id');
          }
          

          $user = getUserData($user_id);
          if (count($user) == 0) {
            return $VAH->error_denied('Usuario no disponible.');
          }
          
          $resVal =$DB->SELECT("SELECT *
                                FROM tblorganizer");

          $resArr = array();
          if (count($resVal) > 0) {
            $i = 0;
            foreach ($resVal as $value) {
              $resArr[$i]['organizer_id'] = $value['organizer_id'];
              $resArr[$i]['organizer_image'] = (isset($value['organizer_image']) && $value['organizer_image'] != "") ? SITE_URL_REMOTE . "/" . $value['organizer_image'] : "";
              $resArr[$i]['organizer_link'] = (isset($value['organizer_link']) && $value['organizer_link'] != "") ? $value['organizer_link'] : "";
              $i++;
            }
          }

          return $VAH->api_success($resArr,"Parqueos recuperado.");

        break;

        case "get_banner":

          if (!isset($data['user_id']) || $data['user_id'] == '') {
            return $VAH->error_invalid('user_id');
          }

          if (!isset($data['type']) || $data['type'] == '') {
            return $VAH->error_invalid('type');
          }
          

          $user = getUserData($user_id);
          if (count($user) == 0) {
            return $VAH->error_denied('Usuario no disponible.');
          }
          
          $resVal =$DB->SELECT("SELECT *
                                FROM tblbanner
                                WHERE type = '".$type."'");

          $resArr = array();
          if (count($resVal) > 0) {
            $i = 0;
            foreach ($resVal as $value) {
              $resArr[$i]['banner_id'] = $value['banner_id'];
              $resArr[$i]['banner_image'] = (isset($value['banner_image']) && $value['banner_image'] != "") ? SITE_URL_REMOTE . "/" . $value['banner_image'] : "";
              $i++;
            }
          }

          return $VAH->api_success($resArr,"Bandera recuperado.");

        break;

        //--------------- MUSEUM SERVICES END --------------------//

        //--------------- NOTIFICATION SERVICES --------------------//

        case "get_notification":

          if (!isset($data['user_id']) || $data['user_id'] == '') {
            return $VAH->error_invalid('user_id');
          }

          $user = getUserData($user_id);
          if (count($user) == 0) {
            return $VAH->error_denied('Usuario no disponible.');
          }
          
          $resVal =$DB->SELECT("SELECT *
                                FROM tblpush_notification
                                ORDER BY created_at DESC  
                                LIMIT 100");

          $resArr = array();
          if (count($resVal) > 0) {
            $i = 0;
            foreach ($resVal as $value) {
              $resArr[$i]['notification_id'] = $value['notification_id'];
              $resArr[$i]['message'] = (isset($value['message']) && $value['message'] != "") ? $value['message'] : "";
              $resArr[$i]['created_at'] = (isset($value['created_at']) && $value['created_at'] != "") ? $value['created_at'] : "";
              $i++;
            }
          }

          return $VAH->api_success($resArr,"Notificación recuperada.");

        break;

        //--------------- NOTIFICATION SERVICES END --------------------//

        
        //--------------- BAD API REQUEST ---------------//          
        
        default:
            return $VAH->api_error('BAD_REQUEST', 'Bad API request.');

        //--------------- BAD API REQUEST END ---------------//     
    }
}

//--------------- GET API REQUEST ---------------//          

if (isset($_REQUEST['api_request']) && $_REQUEST['api_request'] != '') {
    global $VAH, $DB;

    $data = NULL;
    if (isset($_REQUEST['data'])) {
        $data = $_REQUEST['data'];
    }
    if (!is_array($data)) {
        $data = json_decode($data, true);
    }
    if (isset($data['language'])) {
        $data['language'] = "ES";
    }

    // print_r($_SERVER);die;

    if($_SERVER['HTTP_HOST'] == "localhost"){
      $headers = apache_request_headers();
      $headers['device_type'] = $headers['DeviceType'];
      $headers['device_id'] = $headers['DeviceId'];
    }else{  
      $headers['Authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
      $headers['device_type'] = $_SERVER['HTTP_DEVICETYPE'];
      $headers['device_id'] = $_SERVER['HTTP_DEVICEID'];
    }  

    if(!isset($headers['device_type']) || $headers['device_type'] ==""){
      $retval =  $VAH->error_invalid('DeviceType');
      die(json_encode($retval));
    }
    if(!isset($headers['device_id']) || $headers['device_id'] ==""){
      $retval = $VAH->error_invalid('DeviceId');
      die(json_encode($retval));
    }

    //--------------- EXCLUDE SERVICE FROM CHECKING API SESSION  ---------------//  

    if($_REQUEST['api_request'] != "login" && 
       $_REQUEST['api_request'] != "social_login" &&
       $_REQUEST['api_request'] != "sign_up" &&
       $_REQUEST['api_request'] != "forgot_password" &&
       $_REQUEST['api_request'] != "logout" &&
       $_REQUEST['api_request'] != "check_guest_user"){

      //--------------- GET CHECK AUTHORIZATION ---------------//  

        if(!isset($headers['Authorization']) || $headers['Authorization'] ==""){
          $retval = $VAH->error_invalid('Authorization');
          die(json_encode($retval));
        }

      //--------------- CHECK AVALABILITY OF AUTHORIZATION & DEVICE TYPE & DEVICE ID ---------------//  

      if(isset($headers['Authorization']) && $headers['Authorization'] !=""  && isset($headers['device_type']) && $headers['device_type'] !="" && isset($headers['device_id']) && $headers['device_id'] !=""){



        //--------------- GET EXPLODE AUTHORIZATION ---------------//  

        $access_header = explode(' ',$headers['Authorization']);

        //--------------- GET ACCESS KEY ---------------//
        
        if(!isset($access_header[0])){
          $retval =  $VAH->error_invalid('access key');
          die(json_encode($retval));
        }
        $access_key =$access_header[0];

        //--------------- GET ACCESS TOKEN ---------------//
        
        if(!isset($access_header[1])){
          $retval =  $VAH->error_invalid('access token');
          die(json_encode($retval));  
        }
        $access_token =$access_header[1];

        //--------------- GET DEVICE TYPE & DEVICE ID ---------------//

        $device_type =$headers['device_type'];
        $device_id =$headers['device_id'];

        //--------------- VALIDET ACCESS KEY ---------------//

        if($access_key !="fox"){
          $retval =  $VAH->error_invalid('access key');
          die(json_encode($retval));
        }

        //--------------- CHECK API SESSION ---------------//

        $retval = authenticat_api_call($data['user_id'],$access_token,$device_type,$device_id);
        if(count($retval) == 0){
           $retval =  $VAH->error_denied('La sesión del usuario expira. Inicie sesión de nuevo.');
           die(json_encode($retval));
        }

      }else{
        $retval =  $VAH->error_api();
        die(json_encode($retval));
      }  
      
    }

    //--------------- API CALL ---------------//

    $retval = api_request($_REQUEST['api_request'], @$_REQUEST['api_id'], @$_REQUEST['api_secret'], $data, $_FILES);

    //--------------- API RESPONSE ---------------//

    die(json_encode($retval));
    
}

//--------------- GENERAT SECRET ---------------//

function secret_generator($email_id,$password){
  $email_id = strrev($email_id);
  $password = strrev($password);

  return md5($email_id.$password);
}

//--------------- CHECK API SESSION ---------------//

function authenticat_api_call($user_id,$access_token,$device_type,$device_id) {
    global $VAH, $DB;
    $access_session = strtotime(date('Y-m-d H:i:s'));
    $userData = $DB->select("SELECT * 
                             FROM tblapi_session 
                             WHERE user_id = '".$user_id ."'
                                   AND access_token = '".$access_token."'
                                   AND device_type = '".$device_type."'
                                   AND device_id = '".$device_id."'
                                   AND access_session >= '".$access_session."'");

    return $userData;
}

//--------------- GENERAT ACCESS TOKEN ---------------//

function access_token($length = 50) {
    $characters = '@0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

//--------------- CHECK USER AVAILBILITY ---------------//

function getUserData($user_id) {
    global $VAH, $DB;
    $userData = $DB->select("SELECT * 
                             FROM tblusers 
                             WHERE user_id = '".$user_id."' 
                                   AND status=1");
    return $userData;
}


function get_product_data($product_id){
  global $VAH, $DB;
  $resVal = $DB->SELECT("SELECT p.*,c.category_name
                         FROM tblproducts p
                         LEFT JOIN tblcategory c ON c.category_id = p.category_id
                         LEFT JOIN tblgroup g ON g.group_id = p.group_id
                         WHERE p.product_id = '".$product_id."'");
  return $resVal[0];
}

//--------------- PRODUCT PAGINATION ---------------//

function get_product_offset($last_product_id = null){

    $pagesize = 10;
    $pageindex =  0;
    $start =  intval($pageindex) * $pagesize;
    $end = $pagesize;
    $last_product_id = isset($data['last_product_id']) ? $data['last_product_id'] : '0';
    $limit = "";
   
    if($last_product_id > "0")
    {             
      return  $limit = " and p.product_id > $last_product_id LIMIT $start , $end";
    } 
    if ($last_product_id == "0"){           
      return  $limit = "LIMIT $start , $end";    
    }   
}

//--------------- PRODUCT PAGINATION ---------------//

function get_product_rating($product_id) {
    global $VAH, $DB;

    $result =$DB->SELECT("SELECT rating 
                          FROM tblproduct_rating
                          WHERE product_id = '".$product_id."'");
  $max = 0;
  $n = count($result); // get the count of comments
  foreach ($result as $rate => $count) { 
    $max = $max+$count['rating'];
  }
  if($n == 0){
    return false;
  }else{
    return $max / $n;
  } 

    //return $result;
}


?>

