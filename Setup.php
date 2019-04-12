#!/usr/bin/env php
<?php
require_once(__DIR__."../../HM-XMLRPC-Client/Client.php");
$port = system("homegear -e rpcservers | grep \"false   none\"");
$port = (integer)trim(substr($port, 45, 5));
if(!$port)
{
	echo "Homegear is not running or no RPC server without SSL is available.\n";
	exit(1);
}
$client = new \XMLRPC\Client("127.0.0.1", $port, false);

$stdin = fopen( 'php://stdin', 'r' );
$continue = "";
while(!$continue)
{
	echo "Do you want to create a new mediola-eWickler device (y/n): ";
	$continue = strtolower(trim(fgets($stdin)));
}
while($continue == "y")
{
	$serial = "";
	while(true)
	{
		$serial = "mediolaWR".str_pad(mt_rand(0, 99999999), 8, "0", STR_PAD_LEFT);
		$result = $client->send("getPeerId", array(1, $serial));
		if(count($result) == 0) break;
	}
	$peerId = $client->send("createDevice", array(254, 256, $serial, -1, -1));
	if(!$peerId)
	{
		echo "Error creating mediola-eWickler device. Please check your Homegear log.\n";
		exit(1);
	}
	while(true)
	{
	    $ewicklerAdr = "";
	    while(!$ewicklerAdr)
	    {
	        echo "ewicker ADR: ";
	        $ewicklerAdr = trim(fgets($stdin));
	    }
	    $gatewayIp = "";
	    while(!$gatewayIp)
		{
			echo "GW IP: ";
			$gatewayIp = trim(fgets($stdin));
		}
		$gatewayPassword = "";
		while(!$gatewayPassword)
		{
			echo "GW Password: ";
			$gatewayPassword = trim(fgets($stdin));
		}

		$url = "http://".urlencode($gatewayIp)."/command?XC_FNC=GetStates=&at==".urlencode($gatewayPassword);
		
		$json = @file_get_contents($url);
		if(!$json)
		{
			echo "Error: Response from Gateway is empty. Please try again.\n";
			continue;
		}
		
		$data = json_decode((string)$json,true);
		if(!$data || count($data) == 0)
		{
			echo "Error: Response from Gateway is empty.\n";
			continue;
		}
		if($data["cod"] != "404")
		{
		    if(is_array($data['XC_SUC'])){
		        for($i=0;$i <= count($data['XC_SUC'])-1;$i++) {
		            if($data['XC_SUC'][$i]['type'] == "WR" && $data['XC_SUC'][$i]['adr'] ==$ewicklerAdr){
		                $client->send("putParamset", array($peerId, 0, "MASTER", array("GATEWAY_IP" => $gatewayIp, "GATEWAY_PASSWORD" => $gatewayPassword, "EWICKLER_ADR" => $data['XC_SUC'][$i]['adr'])));
		            }
		              
		         }
		    }
	        break;
		      
		}
		//echo "error.\n";
		
	}
	echo "eWickler device created. Do you want to create an additional eWickler device (y/N): ";
	$continue ="n"; //strtolower(trim(fgets($stdin)));
}

fclose($stdin);

exit(0);
?>
