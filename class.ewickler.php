<?php
/* Class to share data between the main script and the event thread.*/
define('DEBUG_MEDIOLA_WR', false);

function print_debug($hg,$level,$msg) {
    if(defined('DEBUG_MEDIOLA_WR') && DEBUG_MEDIOLA_WR){
        $hg->log($level, $msg);
    }
}

define("cmd_channel", "01");
define("cmd_up", "01");
define("cmd_down", "02");
define("cmd_stop", "03");
define("cmd_level", "07");
define("control_motor", "01");
define("control_lighting", "02");
define("cmd_url", "/cmd?XC_FNC=SendSc&type=WR&data=");
define("cmd_url_password", "&auth=");

class SharedData extends Threaded{
    public $scriptId = 0;
    public $peerId = 0;
    public $interval = 60;
    public function run() {}
}

class EventThread extends Thread{
    private $sharedData;

	public function __construct($sharedData){
		$this->sharedData = $sharedData;
	}
	
	public function sendCommand($hg,$cmdUrl){
	    usleep(random_int(400, 1000));
	    print_debug($hg,2, "CMD_URL:".$cmdUrl);
	    $json = @file_get_contents($cmdUrl);
	    $data = json_decode(trim(substr($json,8)),true);
	    if(!$data){
	        print_debug($hg,2, "mediola-eWickler Gateway connection error");
	    }
	}

	public function run(){
		/* http://192.168.XX.XX/cmd?XC_FNC=SendSc&type=WR&data=01XXXXXXXX0102&at=XXXXXXXXXXXXXX */	
		$hg = new \Homegear\Homegear();
		if($hg->registerThread($this->sharedData->scriptId) === false){
		    print_debug($hg,2,"Could not register thread.");
		    return;
		}
		$hg->subscribePeer($this->sharedData->peerId);

    	while(!$hg->shuttingDown() && $hg->peerExists($this->sharedData->peerId)){
			$result = $hg->pollEvent();
			$config = $hg->getParamset($this->sharedData->peerId, 0, "MASTER");
			if($result["TYPE"] == "event" && $result["PEERID"] == $this->sharedData->peerId){
			   
				if($result["VARIABLE"] == "REQUEST"){
					$this->sharedData->interval = 0;
					$this->synchronized(function($thread){ $thread->notify(); }, $this);
				}elseif($result["VARIABLE"] == 'LEVEL' && $hg->getValue($this->sharedData->peerId, 1, "LEVEL") != $hg->getValue($this->sharedData->peerId, 1, "CURRENT_POSITION")){
				    $this->sharedData->interval = 0;
				    $cmdUrl="http://".$config['GATEWAY_IP'].cmd_url.control_motor.$config['EWICKLER_ADR'].cmd_channel.cmd_level.dechex($hg->getValue($this->sharedData->peerId, 1, "LEVEL")).cmd_url_password.$config['GATEWAY_PASSWORD'];
				    $this->sendCommand($hg,$cmdUrl);
			        $this->synchronized(function($thread){ $thread->notify(); }, $this);
				}elseif($result["VARIABLE"] == 'UP'){
				    $this->sharedData->interval = 0;
				    $cmdUrl="http://".$config['GATEWAY_IP'].cmd_url.control_motor.$config['EWICKLER_ADR'].cmd_channel.cmd_up.cmd_url_password.$config['GATEWAY_PASSWORD'];
			        $this->sendCommand($hg,$cmdUrl);
			        $this->synchronized(function($thread){ $thread->notify(); }, $this);
			    }elseif($result["VARIABLE"] == 'DOWN'){
			        $this->sharedData->interval = 0;
			        $cmdUrl="http://".$config['GATEWAY_IP'].cmd_url.control_motor.$config['EWICKLER_ADR'].cmd_channel.cmd_down.cmd_url_password.$config['GATEWAY_PASSWORD'];
			        $this->sendCommand($hg,$cmdUrl);
			        $this->synchronized(function($thread){ $thread->notify(); }, $this);
			    }elseif($result["VARIABLE"] == 'STOP'){
			        $this->sharedData->interval = 0;
			        $cmdUrl="http://".$config['GATEWAY_IP'].cmd_url.control_motor.$config['EWICKLER_ADR'].cmd_channel.cmd_stop.cmd_url_password.$config['GATEWAY_PASSWORD'];
			        $this->sendCommand($hg,$cmdUrl);
			        $this->synchronized(function($thread){ $thread->notify(); }, $this);
			    }
			}elseif($result["TYPE"] == "updateDevice" && $result["PEERID"] == $this->sharedData->peerId){
				$this->sharedData->interval = 0;
				$this->synchronized(function($thread){ $thread->notify(); }, $this);
			}
		}
	}
}
?>