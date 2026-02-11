<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Utility {
static function validateSession($creationTime,$expTimeStamp){
		$date1 = new DateTime();
		$date1->getTimestamp() ;
		//echo $creationTime ; 
		
		//echo ($creationTime-1696055000) ."---".$creationTime;exit;
		if(($creationTime-1696055000 < 0) || $date1->getTimestamp()>$expTimeStamp) {
			$response['response_code'] = '0';
			$response['response_message'] = 'Session Expired';
			$response['response_description'] = 'Session Expired';
			echo json_encode($response); exit ;

		}
		
	}

	static function tokenSession($UserData)
	{
		
		if(empty($UserData))
		{
			$res['response_code'] = '1000';
			$res['response_message'] = 'Token is expired';

			echo json_encode($res); exit;
		}else{

			return true ;
		}
		
	}
}
