verbose("__________________________LUCKY-PASS-INCDP1_________________________________");
$log_data["app_id"]     = isset($dialplan_id) ? $dialplan_id : null;
$log_data["app_name"]   = "Custom Dial Plan";
$log_data["app_data"]   = "Custom Dial Plan";
$log_data["type"]       = "Custom Dial Plan";
$log_data["id"]         = isset($dialplan_id) ? $dialplan_id : null;
$log_data["uid"]        = time();
$log_data["time"]       = time();
Dialplan::agi_app("set", "flow_type_name=CDP", $log_data);

verbose("__________________________LUCKY-CHECK1_________________________________");
$tts = new \App\TextToSpeech();

//$welcomeResponse = $tts->convert("Welcome to Task four", uniqid(), 2, "en-IN", "en-IN-Standard-C");
//if($welcomeResponse["success"]) Dialplan::agi_stream_file($welcomeResponse["data"]);

$user_id = 4663;
$recording1 = get_sound_path($user_id."/62d7914e4e62f"); //enter you pilot number (working number)
$recording2 = get_sound_path($user_id."/60e432978e3b9"); //you have entered wrong pilot number, lets try again (new recording)
$recording3 = get_sound_path($user_id."/5fe0632a88a40"); //Thank you. kindly enter revise pilot number (abc check)
$digit_timeout = 5;
$pilotNumber_key_length = 10;
$input_key_length = 1;
$pilotNumberTrials = 2;
$API_URL = "https://dev-test.therealpbx.co.in:9801/api/v1/testAPIForCDP/";
$apiRequest = [];
$optRow = [];
$optRow['menu_destination_type'] = 'auto_attendant';
$fixedInputTrials = 2;
$getPilotNumber = true;
$pilotNumber = "";
$input = "";
verbose("__________________________LUCKY-CHECK2_________________________________");
auth:
    if($getPilotNumber){
        $inputTrials = $fixedInputTrials;
        if($pilotNumberTrials == 2) $pilotNumber = Dialplan::background($recording1, ($digit_timeout * 1000), $pilotNumber_key_length);
        else $pilotNumber = Dialplan::background($recording2, ($digit_timeout * 1000), $pilotNumber_key_length);
    }

	verbose($pilotNumber);
    verbose("__________________________LUCKY-CHECK3_________________________________");
    $recording4 = $tts->convert("Thank you, I received ".$pilotNumber.". if this is correct, press 1. To re-enter your pilot number press 2", uniqid(), 2, "en-IN", "en-IN-Standard-C");
    verbose($recording4);
	if($recording4["success"]) $input = Dialplan::background($recording4["data"], ($digit_timeout * 1000), $input_key_length);
    verbose("__________________________LUCKY-CHECK-PASS-INPUT_________________________________");
    if(isset($input) && $input == 1){ //correct PN

        verbose("__________________________LUCKY-CHECK4_________________________________");
        if(strlen($pilotNumber) == 10){

            verbose("__________________________LUCKY-CHECK5_________________________________");
            $apiRequest["caller_number"] = $pilotNumber;
            $apiResponse = getDataFromCdpClientApi($API_URL, $apiRequest, [], "post", "json");
			
			verbose($apiRequest);
			verbose($apiResponse);
            if(isset($apiResponse) && isset($apiResponse["data"]["registered"])){
                verbose($apiResponse);
                verbose("__________________________LUCKY-CHECK6_________________________________");
                $registeredDate = $apiResponse["data"]["activation_date"];
                verbose($registeredDate);
				
                //if today - regdate < 30, then call will route to ONB team, else route to IVR7 (auto_attendant)
                $today = new DateTime();
				verbose($today);
				
                $registeredDate = DateTime::createFromFormat('d-m-Y', $registeredDate);
				verbose($registeredDate);
                // Calculate the difference in days
                $daysDifference = $today->diff($registeredDate)->days;
				verbose($daysDifference);
				
				verbose("__________________________LUCKY-CHECK7_________________________________");
                // Check if the difference is less than 30 days
                if ($daysDifference < 30) {
                    //route call to ONB team
					verbose("___________________________LUCKY-PASS-DAYS____________________________");
                    $optRow['menu_destination'] = 3397;
                }
                else {
                    //route call to IVR7 (auto_attendant)
					verbose("___________________________LUCKY-FAIL-DAYS____________________________");
                    $optRow['menu_destination'] = 3396;
                }
            }
            else{
                //direct to IVR6 (auto_attendant)
                $optRow['menu_destination'] = 3400;
            }
        }
        else{
            //direct to IVR6 (auto_attendant)
            $optRow['menu_destination'] = 3400;
        }
    }
    else {
        verbose("__________________________LUCKY-CHECK-INPUT-TRIAL_________________________________");
        if(isset($input) && $input == 2){ //Re-enter PN
            if($pilotNumberTrials){
                $getPilotNumber = true;
                Dialplan::agi_stream_file($recording3);
                $pilotNumberTrials--;
				goto auth;
            }
            else{
                //direct to IVR6 (auto_attendant)
                $optRow['menu_destination'] = 3400;
            }
        }
        else{
            if($inputTrials){
				verbose("__________________LUCKY-LOOP_________________________________________");
				verbose($inputTrials);
                $inputTrials--;
                $getPilotNumber = false;
				goto auth;
            }
            else{
                //direct to IVR6 (auto_attendant)
                $optRow['menu_destination'] = 3400;
            }
        }
    }

    Dialplan::destination_handler($optRow);
 




Dialplan::agi_hangup(__FILE__ . ':' . __LINE__);
exit;