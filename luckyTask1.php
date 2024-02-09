Dialplan::agi_answer("custom_dial_plan");

$log_data["app_id"]     = isset($dialplan_id) ? $dialplan_id : null;
$log_data["app_name"]   = "Custom Dial Plan";
$log_data["app_data"]   = "Custom Dial Plan";
$log_data["type"]       = "Custom Dial Plan";
$log_data["id"]         = isset($dialplan_id) ? $dialplan_id : null;
$log_data["uid"]        = time();
$log_data["time"]       = time();
Dialplan::agi_app("set", "flow_type_name=CDP", $log_data);

$user_id = 4663;
$recording_name = get_sound_path($user_id."/62d7914e4e62f"); //working number recording
$digit_timeout = 5;
$key_length = 10;
$trial = 2;

$moh = "62da715e1ddb5";
$URL = "https://webhook.site/7cd9436d-eb0d-466f-bb41-ac3b2c099e5e/";

$ringTimeout = 30;
$trunk = $agi->get_variable('agent_trunk', true);
//verbose("__________________________LUCKY-" .$digit. "_________________________________");


$tts = new \App\TextToSpeech();
$startResponse = $tts->convert("Hi there", uniqid(), 2, "en-IN", "en-IN-Standard-C");
Dialplan::agi_stream_file($startResponse["data"]);
$welcomeMessage = $tts->convert("Enter Your Number after music ends", uniqid(), 2, "en-IN", "en-IN-Standard-C");
$ApiError = false;
$input = "";

auth:
	if(!$ApiError) Dialplan::agi_stream_file($welcomeMessage["data"]);
	if(!$ApiError) $input = Dialplan::background($recording_name, ($digit_timeout * 1000), $key_length);
	if(strlen($input) != 10){
		if($trial > 0){
			$error = $tts->convert("Wrong number, try again", uniqid(), 2, "en-IN", "en-IN-Standard-C");
			Dialplan::agi_stream_file($error["data"]);
			$trial--;
			goto auth;
		}
		else {
			$endSession = $tts->convert("You lost all trials", uniqid(), 2, "en-IN", "en-IN-Standard-C");
			Dialplan::agi_stream_file($endSession["data"]);
		}
	}
	else{
		$apiResponse = getDataFromCdpClientApi($URL, [], [], "get", "json");
		
		verbose("__________________________LUCKY- " .$input. "_________________________________");
		verbose("__________________________LUCKY-PASS1_________________________________");
		
		if(!isset($apiResponse) || !isset($apiResponse["data"]["number"])){
			//Error with API
		   $ApiError = true;
		   if($trial > 0){
				$trial--;
				goto auth;
		   }
		}
		else{
			$ApiError = false;
			if($apiResponse["data"]["number"] != $input){
				if($trial > 0){
					$error = $tts->convert("Wrong number, try again", uniqid(), 2, "en-IN", "en-IN-Standard-C");
					Dialplan::agi_stream_file($error["data"]);
					$trial--;
					goto auth;
				}
				else {
					$endSession = $tts->convert("You lost all trials", uniqid(), 2, "en-IN", "en-IN-Standard-C");
					Dialplan::agi_stream_file($endSession["data"]);
				}
			}
			else{
				//music on hold
				verbose("__________________________LUCKY-PASS2_________________________________");

				//function for  music on hold for
				Dialplan::agi_app("set", "moh_class=default"); 

				verbose("__________________________LUCKY-PASS3_________________________________");

				//dial
				$dialString = Dialplan::sipModule() . "/+91". $input ."@" . $trunk;
				$dialParams = [
					'dial_str' => $dialString
				];
				Dialplan::dial($dialParams);
			}
		}
	}
	
Dialplan::agi_hangup(__FILE__ . ':' . __LINE__);
exit;