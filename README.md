# homegear Development and Testing Modules
Homegear Miscellaneous Modules 
 
plugin for mediola Gateways V5/V5plus, tankerkoenig, googletraffic maybe more ;)


For homegear and EasySmarthome Smartha System

For the following devices:


Name | State | Version
------------ | ------------- | ------------- 
WIR eWickler | ( online for Testing ) | Self Tested Working
Somfy RTS | ( online for Testing ) | Not Tested have no Devices
FS20 | (online for Testing) |	In SelfTest Working
Intertechno | (online for Testing) | In SelfTest Working
InfraRed | (online for Testing) |	In SelfTest Working
RF | (online for Testing) |	In SelfTest Working

The modules are only to Send !
No States for Somfy,FS20,Intertecho,RF,IR
Only WIR is pulling States.


### Instructions:


## mediola folder

> #### Get Intertechno Code From FS20 Remote:
>http://GATEWAYIP/cmd?XC_FNC=SendSC&type=FS20&auth=PASSWORD



> #### Get Intertechno Code From RF Remote:
>http://GATEWAYIP/cmd?XC_FNC=SendSC&type=IT&auth=PASSWORD
>Answer:
>{"XC_SUC": {"CODE":"03000E"}}

>The Value first 3 digits in CODE convert to HEX must be copy for RF ADDRESS_CODE Field.



> #### Lern RF Code:
>http://GATEWAYIP/cmd?XC_FNC=Learn&auth=PASSWORD
>Answer:
>{"XC_SUC": {"CODE":"19082601000200000100DE58800101010000000000000100000000"}}
>The Value in CODE must be copy for RF ADDRESS_CODE Field.

> #### Learn IR Code:
>http://GATEWAYIP/cmd?XC_FNC=Learn&auth=PASSWORD
>Answer:
>{"XC_SUC": {"CODE":"19082601000200000100DE58800101010000000000000100000000"}}
>The Value in CODE must be copy for RF ADDRESS_CODE Field.


## misc folder

### For Tankerkoenig.de ( Beta )
You Need a Tankerkoenig. api Key from https://creativecommons.tankerkoenig.de/

Beta Testing.

You have 5 Channels for Stations


### For Google Traffic ( Beta )

You Need a  API Manager https://console.developers.google.com/apis/
From Google to get Traffic Data. One Key only Works once Per Day but you can use Two or More Keys for Different Routes.


This addon extends Homegear with a virtual device in device family "Miscellaneous". The device is created automatically upon installation of the Debian package. After installation you need to modify the configuration parameters of the device according to your needs. Of course you can create multiple devices.

