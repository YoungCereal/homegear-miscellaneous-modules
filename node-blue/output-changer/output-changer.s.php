<?php
declare(strict_types=1);

$nodeObject;

function output(int $outputIndex, array $message)
{
    global $nodeObject;
    return $nodeObject->output($outputIndex, $message);
}

function stringToBool($string){
    return ( mb_strtoupper( trim( $string)) === mb_strtoupper ("true")) ? TRUE : FALSE;
}


class HomegearNode extends HomegearNodeBase
{
    
    private $hg = NULL;
    private $nodeInfo = NULL;
    
    public function __construct()
    {
        global $nodeObject;
        $nodeObject = $this;
        $this->hg = new \Homegear\Homegear();
    }
    
    
    function __destruct()
    {
    }
    
    //Init is called when the node is created directly after the constructor
    public function init(array $nodeInfo) : bool
    {
        //Write log entry to flows log
        $this->log(4, "init");
        $this->nodeInfo = $nodeInfo;
        return true; //True means: "init" was successful. When "false" is returned, the node will be unloaded.
    }
    
    //Start is called when all nodes are initialized
    public function start() : bool
    {
        $this->log(4, "start");
        return true; //True means: "start" was successful
    }
    
    //Executed when all config nodes are started. If you need to get settings from configuration nodes that only can return
    //settings after "start" was called, do that here.
    public function configNodesStarted()
    {
    }
    
    //Stop is called when node is unloaded directly before the destructor. You can still call RPC functions here, but you
    //shouldn't try to access other nodes anymore.
    public function stop()
    {
        $this->hg->log(4, "stop");
    }

    public function waitForStop()
    {
        $this->hg->log(4, "waitForStop");
    }
    
    
    public function input(array $nodeInfoLocal, int $inputIndex, array $message)
    {
        $this->log(3, "node-changer-input");
        $this->nodeInfo = $nodeInfoLocal;
        $outDataType = $this->nodeInfo["info"]["payloadType"];
        $outaspayload= $this->nodeInfo["info"]["aspayload"];
        
        if($outDataType =="bool"){
            $outData["payload"] = stringToBool($this->nodeInfo["info"]["payload"]);
        }elseif($outDataType =="int"){
            $outData["payload"] = intval($this->nodeInfo["info"]["payload"]);
        }elseif($outDataType =="float"){
            $outData["payload"] = floatval($this->nodeInfo["info"]["payload"]);
        }elseif($outDataType =="string"){
            $outData["payload"] = strval($this->nodeInfo["info"]["payload"]);
        }elseif($outDataType =="array"){
            $outData["payload"] = $this->nodeInfo["info"]["payload"];
        }elseif($outDataType =="struct"){
            $outData["payload"] = $this->nodeInfo["info"]["payload"];
        }
        
        if($outaspayload){
            $this->output(0, $outData);
        }else{
            $this->output(0,$outData["payload"]);
        }

       return true;
    }
    
}