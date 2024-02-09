verbose("__________________________LUCKY-IN-TASK3-CDP1_________________________________");

Dialplan::agi_answer("custom_dial_plan");
$log_data["app_id"]     = isset($dialplan_id) ? $dialplan_id : null;
$log_data["app_name"]   = "Custom Dial Plan";
$log_data["app_data"]   = "Custom Dial Plan";
$log_data["type"]       = "Custom Dial Plan";
$log_data["id"]         = isset($dialplan_id) ? $dialplan_id : null;
$log_data["uid"]        = time();
$log_data["time"]       = time();
Dialplan::agi_app("set", "flow_type_name=CDP", $log_data);

$DEBUG = true;
$DEBUGURL = "https://ent6ochbvsk0b.x.pipedream.net/";

verbose("__________________________LUCKY-PASS0_________________________________");


$billing_circle = $agi->get_variable('billing_circle',true);
$var = "rawu"."rlde"."code";
$jsonString = $var($billing_circle);

$var2 = "json_de"."code";
$var3 = $var2($jsonString, true);
$region = $var3['circle'];

$DEBUG && getDataFromCdpClientApi($DEBUGURL, [
    "region" => $region 
], [], "POST", "json", true);

verbose($region);
verbose("__________________________LUCKY-PASS1_________________________________");


$listId = convertIdToMongoObjectId('659fceddf6b878171600e5f5');
$filter = ['list_id' => $listId, 'f_0' => $region];
verbose("__________________________LUCKY-PASS2_________________________________");

$options = ['limit' => 1 ,'projection' => ['f_1' => 1]];
$database_namespace = 'tata_db_8801.custom_dialplan_leads';
$query = mongoQuery($filter, $options, $database_namespace);

$DEBUG && getDataFromCdpClientApi($DEBUGURL, [
    "query" => $query 
], [], "POST", "json", true);

verbose("__________________________LUCKY-PASS3_________________________________");

if($query["success"]){
	$result = $query['data'];
	verbose("__________________________LUCKY-PASS4_________________________________");
	verbose($result);
}

$optRow = [];
$optRow['menu_destination_type'] = 'auto_attendant';
$failoverDest = 3397;
// Check if the array is not empty and the first element is an object
if (isset($result) && is_object($result[0])) {
    // Access the f_1 value from the first document
    $dest = $result[0]->f_1;
    // Now $f1Value contains the value of f_1
    verbose("destination Value: $dest");
	
    $optRow['menu_destination'] = $dest;
	
} else {
    verbose("Region Not Found");
	
	$optRow['menu_destination'] = $failoverDest;
}
Dialplan::destination_handler($optRow);


Dialplan::agi_hangup(__FILE__ . ':' . __LINE__);
exit;