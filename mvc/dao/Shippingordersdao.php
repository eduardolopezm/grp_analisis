<?php
 
class Shippingordersdao{

	function Insertshippingorders($shippingorders){

		$pathprefix = "../.././";
		require_once $pathprefix . 'core/ModeloBase.php';
		
		$UserID = $_SESSION["UserID"];
		$success = true;
		$msgerror = "";
		$typeerror = "";
		$codeerror = 0;
		$sql = "";

		$modelo = new ModeloBase;


		$sql = "INSERT INTO shippingorders (
					shippingno,
					folio,
					orderno,
					debtortransid,
					comments,
					name,
					userid,
					deliverydate,
					shippingdate,
					tagref,
					shippingstatusid,
					flagembarquemostrador
				)
				VALUES (
					'" . $shippingorders->getShippingno() . "',
					'" . $shippingorders->getFolio() . "',
					'" . $shippingorders->getOrderno() . "',
					'" . $shippingorders->getDebtortransid() . "',
					'" . $shippingorders->getComments() . "',
					'" . $shippingorders->getName() . "',
					'" . $shippingorders->getUserid() . "',
					'" . $shippingorders->getDeliverydate() . "',
					'" . $shippingorders->getShippingdate() . "',
					'" . $shippingorders->getTagref() . "',
					'" . $shippingorders->getShippingstatusid() . "',
					'" . $shippingorders->getFlagembarquemostrador() . "'
                )";
               

		$resp = $modelo->ejecutarsql($sql);
		$message = "";

		if ($resp == true and !is_array($resp)){
			$success = false;
			$message = _('No se inserto registro en tabla de embarques');

		}else{
			if ($resp == false){
				$msgerror = "ERROR EN LA CONSULTA";
				$typeerror = "MYSQL ERROR";
				$codeerror = "001";

			}else{
				$message = _('Se inserto registro en la tabla de embarques');

			}
			
		}

		$response['success'] = $success;
		$response['error']['msgerror'] = $msgerror;
		$response['error']['typeerror'] = $typeerror;
		$response['error']['codeerror'] = $codeerror;
		$response['error']['codeerror'] = $sql;
		$response['data']['message'] = $message;
		
		return $response;
        
	}



}


?>