verbose("__________________________LUCKY-PASS-INCDP1_________________________________");
$log_data["app_id"]     = isset($dialplan_id) ? $dialplan_id : null;
$log_data["app_name"]   = "Custom Dial Plan";
$log_data["app_data"]   = "Custom Dial Plan";
$log_data["type"]       = "Custom Dial Plan";
$log_data["id"]         = isset($dialplan_id) ? $dialplan_id : null;
$log_data["uid"]        = time();
$log_data["time"]       = time();
Dialplan::agi_app("set", "flow_type_name=CDP", $log_data);

$caller_id_number =  $agi->get_variable('caller_id_number', true);
$tts = new \App\TextToSpeech();

$digit_timeout = 5;
$key_length = 1;

$l_id = 3523;
$filter = ['l_id' => $l_id];
$options = ['limit' => 1 ,'projection' => ['f5' => 1, 'f6' => 1, 'f7' => 1, 'f8' => 1]];
$database_namespace = 'tata_db_8801.lead';

verbose("__________________________LUCKY-PASS2_________________________________");

$prefix = ["","+91","91","0"];
$index = 0;

auth:

    //$test = $tts->convert("Number is, <say-as interpret-as='telephone' google:style='zero-as-zero'>".$caller_id_number."</say-as>", uniqid(), 2, "en-IN", "en-IN-Standard-C");
    //Dialplan::agi_stream_file($test["data"], uniqid());

    $querySuccess = false;
    $number = $prefix[$index] . $caller_id_number;
    $filer['f0'] = $number;
    $query = mongoQuery($filter, $options, $database_namespace);
    $result = [];
    if($query["success"]){
        $result = $query['data'];
        verbose("__________________________LUCKY-PASS4_________________________________");
        verbose($result);
        $querySuccess = true;
		
		verbose($index);
		$result = (array)$result[0];
		verbose($result);


        if(isset($result['f5']) && isset($result['f6']) && isset($result['f7']) && isset($result['f8'])){
            $customerName = $result['f5'];
            $flightNumber = $result['f6'];
            $GateNumber = $result['f7'];
            $DepartureTime = $result['f8'];

            $recordingMessage = "Dear ". $customerName .", Your flight <say-as interpret-as='characters'>". $flightNumber ."</say-as> will now depart from ". $GateNumber .". The scheduled departure is <say-as interpret-as='time' format='hms12'>". $DepartureTime ."</say-as>. Request you to please board the gate 30 mins before the departure time.";
            $recording = $tts->convert($recordingMessage, uniqid(), 2, "en-IN", "en-IN-Standard-C");
			$ivrMessage = "To listen this message again, press 1.";
            $ivrRecording = $tts->convert($ivrMessage, uniqid(), 2, "en-IN", "en-IN-Standard-C");
			
			if($recording["success"]) Dialplan::agi_stream_file($recording["data"], uniqid());
            
            $input = "";
            if($ivrRecording["success"]) $input = Dialplan::background($ivrRecording["data"], ($digit_timeout * 1000), $key_length);
            
            if(!empty($input) && $input == 1){
				 verbose("__________________________LUCKY-PASS5_________________________________");
                if($recording["success"]) Dialplan::agi_stream_file($recording["data"], uniqid());
            }

        }
		
		
    }

    if(!$querySuccess){
        if($index != count($prefix)){
            $index++;
            goto auth;
        }
        
    }


Dialplan::agi_hangup(__FILE__ . ':' . __LINE__);
exit;