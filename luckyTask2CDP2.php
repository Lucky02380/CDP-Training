verbose("__________________________LUCKY-PASS-INCDP2_________________________________");
$log_data["app_id"]     = isset($dialplan_id) ? $dialplan_id : null;
$log_data["app_name"]   = "Custom Dial Plan";
$log_data["app_data"]   = "Custom Dial Plan";
$log_data["type"]       = "Custom Dial Plan";
$log_data["id"]         = isset($dialplan_id) ? $dialplan_id : null;
$log_data["uid"]        = time();
$log_data["time"]       = time();
Dialplan::agi_app("set", "flow_type_name=CDP", $log_data);

verbose("__________________________LUCKY-CHECK1_________________________________");
$user_id = 4663;
$recording_name = get_sound_path($user_id."/62d7914e4e62f"); //working number recording
$digit_timeout = 5;
$key_length = 10;
$trial = 2;
$apiRequest = [
    "support_no" => "1234",
    "contact_no" => ""
];

verbose("__________________________LUCKY-CHECK2_________________________________");

$URL = "https://dev-test.therealpbx.co.in:9801/api/v1/testAPIForCDP/";
verbose("__________________________LUCKY-CHECK3_________________________________");

$tts = new \App\TextToSpeech();
$welcomeResponse = $tts->convert("Please enter your 10 digit registered mobile number", uniqid(), 2, "en-IN", "en-IN-Standard-C");
$error = $tts->convert("Wrong number, try again", uniqid(), 2, "en-IN", "en-IN-Standard-C");
$endSession = $tts->convert("You have exhausted all the attempts. Thank you for calling.", uniqid(), 2, "en-IN", "en-IN-Standard-C");
verbose("__________________________LUCKY-CHECK4_________________________________");



auth:
    if($welcomeResponse["success"]) Dialplan::agi_stream_file($welcomeResponse["data"]);
	$input = Dialplan::background($recording_name, ($digit_timeout * 1000), $key_length);

	if(strlen($input) != 10){
		if($trial > 0){
			
			if($error["success"]) Dialplan::agi_stream_file($error["data"]);
			$trial--;
			goto auth;
		}
		else {
			
			if($endSession["success"]) Dialplan::agi_stream_file($endSession["data"]);
		}
	}
	else{
        $apiRequest["contact_no"] = $input;
        $apiResponse = getDataFromCdpClientApi($URL, $apiRequest, [], "post", "json");
		
		verbose("__________________________LUCKY-API_________________________________");
		verbose(gettype($apiRequest));
		verbose(gettype($apiRequest["contact_no"]));
		verbose($apiRequest);
		verbose($apiResponse);
		
		if(!isset($apiResponse) || !isset($apiResponse["data"]["status"])){
			if($trial > 0){
				if($error["success"]) Dialplan::agi_stream_file($error["data"]);
				$trial--;
				goto auth;
			}
			else {
				if($endSession["success"]) Dialplan::agi_stream_file($endSession["data"]);
			}
        }
		else{
			verbose("__________________________LUCKY-PASS3_________________________________");
			
            if($apiResponse["data"]["status"]){
                //send to supportCategory ivr after checking time condition
                $optRow['menu_destination_type'] = 'timegroup';
                $optRow['menu_destination'] = 1663;
                verbose("__________________________LUCKY-PASS4-TIMECOND_________________________________");
            }
            else{   
                //send to sales deptartment
                $optRow['menu_destination_type'] = 'ringgroup';
                $optRow['menu_destination'] = 7285;
                verbose("__________________________LUCKY-PASS4-SALESDEPT_________________________________");
            }
            Dialplan::destination_handler($optRow);
		}
	} 
		

Dialplan::agi_hangup(__FILE__ . ':' . __LINE__);
exit;