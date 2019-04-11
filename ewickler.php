<?php 
include_once '/var/lib/homegear/scripts/DeviceScripts/Mediola-eWickler/class.ewickler.php';

$peerId = (integer)$argv[0];
$hg = new \Homegear\Homegear();

$sharedData = new SharedData();
$sharedData->peerId = $peerId;
$sharedData->scriptId = $hg->getScriptId();
$sharedData->address = $hg->getAddress();
$thread = new EventThread($sharedData);
$thread->start();

while(!$hg->shuttingDown() && $hg->peerExists($peerId)){
    $config = $hg->getParamset($peerId, 0, "MASTER");
   
    /* Start */
    if($config["GATEWAY_IP"] == "" ){
        print_debug($hg,2, "Error: Peer does not seem to be an mediola Gateway.");
        $thread->synchronized(function($thread){ $thread->wait(5000000); }, $thread);
        continue;
    }elseif($config["EWICKLER_ADR"] == ""){
        print_debug($hg,2, "Warning: No eWickler address set.");
        $thread->synchronized(function($thread){ $thread->wait(5000000); }, $thread);
        continue;
    }elseif($config["GATEWAY_PASSWORD"] == ""){
        print_debug($hg,2, "Warning: No Gateway Passowrd.");
        $thread->synchronized(function($thread){ $thread->wait(5000000); }, $thread);
        continue;
    }
    $sharedData->interval = $config["REQUEST_INTERVAL"];
    if($sharedData->interval < 30) $sharedData->interval = 30;
    
    $url = "http://".$config['GATEWAY_IP']."/cmd?XC_FNC=GetStates&auth=".$config['GATEWAY_PASSWORD'];
    print_debug($hg,2, $url);
    for($i = 0; $i < 3; $i++){
        usleep(random_int(400, 1000));
        $json = @file_get_contents($url);
        if($json) break;
    }
    $data = json_decode((string)$json,true);
    $data_value=0;
    if(is_array($data['XC_SUC']))
        for($i=0;$i <= count($data['XC_SUC'])-1;$i++) {
            if($data['XC_SUC'][$i]['type'] == "WR" && $data['XC_SUC'][$i]['adr'] == $config["EWICKLER_ADR"])
                $data_value = $data['XC_SUC'][$i]['state'];
    }
    print_debug($hg,2, "data:".$data_value);
    if($data_value) $blind_level = hexdec(substr($data_value,2,-2));
    print_debug($hg,2, "blind_level:".$blind_level);
    if(is_integer($blind_level)){
        print_debug($hg,2, "mediola-eWickler ADR:".$config["EWICKLER_ADR"]);
        print_debug($hg,2, "LEVEL:".$blind_level);
    
        $hg->setValue($peerId, 1, "LEVEL", $blind_level);
        $hg->setValue($peerId, 1, "CURRENT_POSITION", $blind_level);
    }
    $waited = 0;
    while($waited < $sharedData->interval && !$hg->shuttingDown() && $hg->peerExists($peerId)){
        $thread->synchronized(function($thread){ $thread->wait(5000000); }, $thread);
        $waited += 5;
    }
}
$thread->join();
?>