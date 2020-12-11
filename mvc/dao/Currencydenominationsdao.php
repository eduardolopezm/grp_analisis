<?php
 
class Currencydenominationsdao{
	
	function Getdenominations(){
		$pathprefix = "../.././";
		require_once $pathprefix . 'core/ModeloBase.php';
		
		$UserID = $_SESSION['UserID'];
		$success = true;
		$msgerror = "";
		$typeerror = "";
		$codeerror = 0;
		$sql = "";
		$arrdenominations = array();

		$modelo = new ModeloBase;

		$sql = "SELECT c.currencydenomination, 
						c.value,
						c.image
				FROM currencydenominations c
				WHERE active = 1"; 
        
        $resp = $modelo->ejecutarsql($sql);
		$message = "";
		
		if ($resp == true and !is_array($resp)){
			$success = false;
			$message = _('No existe denominacion de monedas configuradas');

		}else{
			if ($resp == false){
				$msgerror = "ERROR EN LA CONSULTA";
				$typeerror = "MYSQL ERROR";
				$codeerror = "001";

			}else{
				
				for($xx = 0; $xx < count($resp); $xx++){
					$arrdenomination = array(
						"currencydenomination" => $resp[$xx]['currencydenomination'],
						"value" => $resp[$xx]['value'],
						"image" => $resp[$xx]['image']
					);
					//$response['data'][] = $arrdebtor;		
					array_push($arrdenominations, $arrdenomination);
				}
			}
			
		}
		
		$response['success'] = $success;
		$response['error']['msgerror'] = $msgerror;
		$response['error']['typeerror'] = $typeerror;
		//$response['error']['codeerror'] = $codeerror;
		$response['error']['codeerror'] = $sql;
		$response['data']['message'] = $message;
		$response['data'] = $arrdenominations;
		
		//var_dump($custbranch);
		//header('Content-type: application/json; charset=utf-8');
  		//return json_encode($response, JSON_FORCE_OBJECT);
  		return $response;
  		

	}



}


?>