
<html>
    <head>
        <title>Museo App Test Api</title>
        <script src='jquery-1.11.3.min.js'></script>
        <link href="../admin/assets/css/bootstrap.css" style="display: none;" rel="stylesheet">
        <script src="../admin/assets/js/sweetalert.min.js"></script>
        <script src="../admin/assets/js/jquery.min.js" ></script>
        <link rel="stylesheet" type="text/css" href="../admin/assets/css/sweetalert.css">
        <script>
            window.api_id = 'YOUR-API-KEY';
            window.api_secret = 'YOUR-API-SECRET';
            var testcases = [];


            testcases.push({
                api_id: window.api_id,
                api_secret: window.api_secret,
                api_request: '--- GENERAL SERVICES ---',
                data: {
                }
            });

            testcases.push({
                api_id: window.api_id,
                api_secret: window.api_secret,
                api_request: 'check_guest_user',
                data: {
                }
            });

            testcases.push({
                api_id: window.api_id,
                api_secret: window.api_secret,
                api_request: 'sign_up',
                data: {
                    'name':'Iroid Test',      
                    'email_id': 'test@gmail.com',
                    'date_of_birth': '01-01-1990',
                    'gender': '0:Female,1:Male',  
                    'password': '098f6bcd4621d373cade4e832627b4f6',
                    'secret': '7629660670a3ad7613e56f048e217c10' 
                }
            });

            testcases.push({
                api_id: window.api_id,
                api_secret: window.api_secret,
                api_request: 'login',
                data: {
                    'email_id': 'test@gmail.com',
                    'password': '098f6bcd4621d373cade4e832627b4f6',
                    'secret': '7629660670a3ad7613e56f048e217c10'           
                }
            });

            testcases.push({
                api_id: window.api_id,
                api_secret: window.api_secret,
                api_request: 'social_login',
                data: {
                    'social_id': '741258963214',
                    'social_type': 'facebook',
                    'email_id': 'test@gmail.com',
                    'name':'Iroid Test',
                    'date_of_birth':'01-01-1990',
                    'gender': '0:Female,1:Male',  
                    'profile_pic': 'http://www.facebook.com'
                   
                }
            }); 
          
            testcases.push({
                api_id: window.api_id,
                api_secret: window.api_secret,
                api_request: 'forgot_password',
                data: {        
                    'email_id': 'test@gmail.com'                    
                }
            });

            testcases.push({
                api_id: window.api_id,
                api_secret: window.api_secret,
                api_request: 'update_user_profile',
                data: {
                    'user_id': '1',
                    'name':'first_name u',
                    'date_of_birth':'01-01-1990',
                    'gender': '0:Female,1:Male', 
                    'profile_pic': 'profile_pic.png'              
                }
            });

            testcases.push({
                api_id: window.api_id,
                api_secret: window.api_secret,
                api_request: 'contact_us',
                data: {
                    'user_id': '1',
                    'subject': 'Subject',
                    'message': 'Please contact me !'
                }
            });

            testcases.push({
                api_id: window.api_id,
                api_secret: window.api_secret,
                api_request: 'logout_user',
                data: {
                    'user_id': '1',
                    'device_type': 'android'
                }
            });

            testcases.push({
                api_id: window.api_id,
                api_secret: window.api_secret,
                api_request: '--- MUSEUM SERVICES ---',
                data: {
                }
            });

            testcases.push({
                api_id: window.api_id,
                api_secret: window.api_secret,
                api_request: 'get_museum',
                data: {
                    'user_id':'1',      
                    'type': '1:By Area,2:Alphabetically,3:Location',
                    'latitude': '21.1702',
                    'longitude': '72.8311'
                }
            });

            testcases.push({
                api_id: window.api_id,
                api_secret: window.api_secret,
                api_request: 'get_museum_by_id',
                data: {
                    'user_id':'1',      
                    'museum_id': '1'
                }
            });

            testcases.push({
                api_id: window.api_id,
                api_secret: window.api_secret,
                api_request: 'get_diary',
                data: {
                    'user_id':'1',
                    'search_text':'Musac',
                    'type':'0:Defalt,1:By Hour,2:By Museum,3:By Exhibition,4:By Activity',
                    'start_time':'16:00',
                    'end_time':'17:00',
                }
            });

            testcases.push({
                api_id: window.api_id,
                api_secret: window.api_secret,
                api_request: 'get_diary_by_id',
                data: {
                    'user_id':'1',
                    'id':'1',
                    'type':'0:Activity,1:Exhibition'
                }
            });


            testcases.push({
                api_id: window.api_id,
                api_secret: window.api_secret,
                api_request: 'get_transport',
                data: {
                    'user_id':'1', 
                    'latitude': '21.1702',
                    'longitude': '72.8311'
                }
            });

            testcases.push({
                api_id: window.api_id,
                api_secret: window.api_secret,
                api_request: 'get_parking',
                data: {
                    'user_id':'1', 
                    'latitude': '21.1702',
                    'longitude': '72.8311'
                }
            });

            testcases.push({
                api_id: window.api_id,
                api_secret: window.api_secret,
                api_request: 'get_sponsor',
                data: {
                    'user_id':'1'
                }
            });

            testcases.push({
                api_id: window.api_id,
                api_secret: window.api_secret,
                api_request: 'get_organizer',
                data: {
                    'user_id':'1'
                }
            });

            testcases.push({
                api_id: window.api_id,
                api_secret: window.api_secret,
                api_request: 'get_banner',
                data: {
                    'user_id':'1',
                    'type':'1:Transport,2:Mi Agenda,3:Parking,4:Map'
                }
            });

            testcases.push({
                api_id: window.api_id,
                api_secret: window.api_secret,
                api_request: '--- Notification SERVICES ---',
                data: {
                }
            });

            testcases.push({
                api_id: window.api_id,
                api_secret: window.api_secret,
                api_request: 'register_for_push',
                data: {
                    'user_id': '1',
                    'device_token': '212345364',
                    'certificate_type' : '0 where 0:dev, 1:live ' 
                }
            });


            testcases.push({
                api_id: window.api_id,
                api_secret: window.api_secret,
                api_request: 'get_notification',
                data: {
                    'user_id':'1'
                }
            });

            function run_testcase() {
                try {
                    var data = JSON.parse($('.testcase').val());
                } catch (e) {
                    $('.output').val('Invalid JSON.');
                    return;
                }
                if (data) {
                    var authorization = $('.authorization').val();
                    var device_type = $('.device_type').val();
                    var device_id = $('.device_id').val();
                    var req= JSON.parse($('.testcase').val());
                    

                    if(req.api_request != 'login' && req.api_request != 'sign_up' && req.api_request != 'forgot_password' && req.api_request != 'social_login' && req.api_request != 'check_guest_user'){
                        if(authorization == ""){
                            //alert('Authorization can not be empty.');
                            swal('Api','Authorization can not be empty','warning');
                            return false;
                        }
                        if(device_type == ""){
                            //alert('Device type can not be empty.');
                            swal('Api','Device type can not be empty.','warning');
                            return false;
                        }
                        if(device_id == ""){
                            //alert('Device id can not be empty.');
                            swal('Api','Device id can not be empty.','warning');
                            return false;
                        }
                    }


                    $.ajax({
                        method: 'POST',
                        url: 'api.php',
                        headers: { 'Authorization': authorization, 'DeviceType': device_type, 'DeviceId': device_id},
                        data: JSON.parse($('.testcase').val()),
                        success: function (responsejson) {
                            if (typeof responsejson == 'string' || responsejson instanceof String) {
                                try {
                                    var output = JSON.parse(responsejson);
                                    $('.output').val(JSON.stringify(output, null, 4));
                                    if(req.api_request == 'login' || req.api_request == 'social_login'){
                                        var access_token = 'fox'+' '+output['data']['access_token'];
                                        //$('#Authorization').val(access_token);
                                        localStorage.setItem("access_token", access_token);
                                    }    
                                } catch (e) {
                                    $('.output').val(responsejson);
                                }
                            } else {
                                $('.output').val(JSON.stringify(responsejson, null, 4));
                            }
                            if ($('.output').val() == '') {
                                $('.output').val('No output.');
                            }
                        },
                        error: function (data, status, error_thrown) {
                            $('.output').val('Error: ' + error_thrown);
                        }
                    });
                }
            }
            function encrypt() {
                window.location = '?input=' + $('.testcase').val() + '&encrypt=1';
            }
            function decrypt() {
                window.location = '?input=' + $('.testcase').val() + '&decrypt=1';
            }
            function toJSON(responsejson) {
                try {
                    var response = JSON.parse(responsejson);
                    return response;
                } catch (e) {
                    return responsejson;
                }
            }
            $(document).ready(function () {
                for (var i = 0; i < testcases.length; i++) {
                    $('.select').append('<option value="' + i + '">' + testcases[i].api_request + '</option>');
                }
                $('.select').change(function () {
                    var selected_service = $('.select option:selected').text();
                    if(selected_service == "login" || selected_service == "sign_up" || selected_service == "forgot_password" || selected_service == "social_login"){
                      $('#Authorization').val('');  
                    }else{
                        var access_token =  localStorage.getItem("access_token");
                        if(access_token !=""){
                            $('#Authorization').val(access_token);
                        }
                    }
                    

                    if ($('.select').val() != -1) {
                        $('.testcase').val(JSON.stringify(testcases[$('.select').val()], null, 4));
                        $('.output').val('');
                    }
                });
            });

            $(function() {
              $(".select").select2();
            }); 

        </script>
        <style>
            input{
                height: 34px;
                padding: 6px 12px;
                font-size: 14px;
                line-height: 1.42857143;
                color: #555;
                background-color: #fff;
                background-image: none;
                border: 1px solid #ccc;
                border-radius: 4px;
            }
            .headers{
                padding: 10px;
                font-weight: 900;
            }
            ::-webkit-scrollbar {
                width: 5px;
                
            }
             
            ::-webkit-scrollbar-track {
                -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3); 
                border-radius: 10px;
            }
             
            ::-webkit-scrollbar-thumb {
                border-radius: 10px;
                -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.5); 
                    background: #cdcdcd;
            }
            textarea {
                font-family: monospace;
                width: 100%;
                min-height: 600px;
                border-radius: 5px;
            }
            .select2-container--default .select2-results>.select2-results__options{
                max-height: 595 !important;
            }
        </style>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
    </head>
    <body>
        <div class="col-md-12" style="margin-top:10px;">
            <div class="col-md-12" style="margin-bottom:10px;">
                <select size="1" class="select" style="width: 600px; height: 40px; font-size: 16px;border-radius: 5px;">
                    <option value="-1">-SELECT TESTCASE-</option>
                </select><button class="btn btn-primary" onclick="run_testcase();" style="margin-left:10px; ">Run</button>
            </div>
            <div class="col-md-6" style="margin-bottom:10px;margin-left:10px;padding: 0px;">
                 <div class="col-md-2 headers">Authorization</div><input id="Authorization" class="Authorization col-md-7" type="text" name="authorization" placeholder="[ AccessKey ]  [ AccessToken ]" value="" style="margin: 5px;">
            </div>   
            <div class="col-md-6" style="margin-bottom:10px;margin-left:10px;padding: 0px;">
                
                 <div class="col-md-2 headers">Device type</div><input class="device_type col-md-7" type="text" name="device_type" placeholder="Device type" value="ios" style="margin: 5px;">
            </div>   
            <div class="col-md-6" style="margin-bottom:10px;margin-left:10px;padding: 0px;">
                 <div class="col-md-2 headers">Device id</div><input class="device_id col-md-7" type="text" name="device_id" placeholder="Device id" value="123456" style="margin: 5px;">
            </div>
            <div class="col-md-6 get_notification" style="margin-bottom:10px;margin-left:10px;padding: 0px;display: none;">
                 <div class="col-md-12" style="padding: 10px;">   
                    <span><b>Notification Type : </b>  [ 1: add_q,2:Friend Request,3:Accept Request,4:Q comment,5:Confirm by me ]</span>
                 </div>   
            </div>
            <div class="col-md-6 get_profile" style="margin-bottom:10px;margin-left:10px;padding: 0px;display: none;">
                 <div class="col-md-12" style="padding: 10px;">   
                    <span><b>Friendship status : </b>  [ 0:Follow,1:Requested,2:Friends,3:Confirm Btn ]</span>
                 </div>   
            </div> 
            <div class="col-md-6 get_q" style="margin-bottom:10px;margin-left:10px;padding: 0px;display: none;">
                 <div class="col-md-12" style="padding: 10px;">   
                    <span><b>Rate and Archive : </b> [ 1:"Greatest of...",2:"I Liked it",3:"I Didn't Like it",4:"I Didn't Finish it" ] </span>
                 </div>  
                 <div class="col-md-12" style="padding: 10px;"> 
                    <span><b>Privacy : </b> [ 0:Everyone,1:Sender/Recipient Only ] </span>
                 </div> 
                 <div class="col-md-12" style="padding: 10px;"> 
                    <span><b>Filter : </b> [ 1:Feed,2:Sent,3:Received,4:Archived ] </span>
                 </div> 
            </div>    
            <div class="col-md-6 search_from_spotify" style="margin-bottom:10px;margin-left:10px;padding: 0px;display: none;">
                 <div class="col-md-12" style="padding: 10px;">   
                    <span><b>Note : </b>offset and limit are not requered</span>
                 </div>   
            </div>    
            <div class="col-md-6">
                <span style="font-weight: 900;">API Request:</span>
                <br>   
                <textarea class="testcase"></textarea>
            </div>
            <div class="col-md-6">
                <span style="font-weight: 900;">Response:</span>
                <br> 
                <textarea class="output"></textarea>
            </div>
        </div>
    </body>
</html>
