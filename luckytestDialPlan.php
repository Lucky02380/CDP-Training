Dialplan::agi_answer("custom_dial_plan");


Dialplan::agi_app("set", "flow_type_name=CDP", $log_data);

$recording_name = get_sound_path("4663/62d7914e4e62f"); //working number recording
$digit_timeout = 10;
$key_length = 5;
$digit = Dialplan::background($recording_name, ($digit_timeout * 1000), $key_length);

verbose("__________________________LUCKY-" .$digit. "_________________________________");


$tts = new \App\TextToSpeech();
//$tts->setVoice(\App\TextToSpeech::VOICE_ADITI);
$response = $tts->convert($digit, uniqid(), 2, "en-IN", "en-IN-Standard-C");


//$tts->setVoice(\App\TextToSpeech::VOICE_ADITI);
//$response = $tts->convert("Hi, welcome 8826714007 to this Custom Dialplan", uniqid(), 2, "", "")

//getDataFromCdpClientApi("https://enjf5dssyeop.x.pipedream.net/", [
//  $response
//], [], "post", "json", true, true);
 
Dialplan::agi_stream_file($response["data"], uniqid());


// 
$ringTimeout = 30;
$trunk = $agi->get_variable('agent_trunk', true);
$dialString = Dialplan::sipModule() . "/+916397444722@" . $trunk;
// 
$dialParams = [
	'dial_str' => $dialString
];
// 
Dialplan::dial($dialParams);







Dialplan::agi_hangup(__FILE__ . ':' . __LINE__);
exit;