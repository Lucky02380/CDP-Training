verbose("__________________________LUCKY-PASS-INCDP1_________________________________");
$log_data["app_id"]     = isset($dialplan_id) ? $dialplan_id : null;
$log_data["app_name"]   = "Custom Dial Plan";
$log_data["app_data"]   = "Custom Dial Plan";
$log_data["type"]       = "Custom Dial Plan";
$log_data["id"]         = isset($dialplan_id) ? $dialplan_id : null;
$log_data["uid"]        = time();
$log_data["time"]       = time();
Dialplan::agi_app("set", "flow_type_name=CDP", $log_data);

verbose("__________________________LUCKY-PASS0_________________________________");

$URL = "https://dev-test.therealpbx.co.in:9801/api/v1/testAPIForCDP/";
$caller_id_number =  $agi->get_variable('caller_id_number', true);
verbose("__________________________LUCKY-PASS0A_________________________________");
$apiRequest = [
    "support_no" => "1234",
    "contact_no" => $caller_id_number
];

verbose($apiRequest);
verbose("__________________________LUCKY-PASS-API1_________________________________");
$apiResponse = getDataFromCdpClientApi($URL, $apiRequest, [], "post", "json");
verbose("__________________________LUCKY-PASS-API2_________________________________");
   
verbose($apiResponse);
if($apiResponse && isset($apiResponse["data"]["status"])){
	$optRow['menu_destination_type'] = 'ivr';
	verbose("__________________________LUCKY-PASS1_________________________________");
	if($apiResponse["data"]["status"]){
		//send to supportCategory ivr
		$optRow['menu_destination'] = 3108;
		verbose("__________________________LUCKY-PASS2-SUPPORTIVR_________________________________");
	}
	else{   
		//send to checkCallerStatus ivr
		$optRow['menu_destination'] = 3109;
		verbose("__________________________LUCKY-PASS2-CHECKREGNUMBER_________________________________");
	}
	Dialplan::destination_handler($optRow);
}

Dialplan::agi_hangup(__FILE__ . ':' . __LINE__);
exit;