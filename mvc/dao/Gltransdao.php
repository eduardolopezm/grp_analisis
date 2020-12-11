<?php
 
class GLtransdao{

	function Insertgltrans($gltrans){

		$pathprefix = "../.././";
		require_once $pathprefix . 'core/ModeloBase.php';
		
		$UserID = $_SESSION['UserID'];
		$success = true;
		$msgerror = "";
		$typeerror = "";
		$codeerror = 0;
		$sql = "";

		$modelo = new ModeloBase;


		$sql = "INSERT INTO gltrans (
						type,
						typeno,
						trandate,
						cat_cuenta,
						periodno,
						account,
						narrative,
						tag,
						userid,
						rate,
						debtorno,
						branchno,
						stockid,
						qty,
						grns,
						standardcost,
						loccode,
						dateadded,
						suppno,
						purchno,
						chequeno,
						amount,
						jobref,
						bancodestino,
						rfcdestino,
						cuentadestino,
			            uuid,
						posted, 
						lastusermod,
						lastdatemod
				)
				VALUES (
					'" . $gltrans->getType() . "',
					'" . $gltrans->getTypeno() . "',
					'" . $gltrans->getTrandate() . "',
					'" . $gltrans->getCat_Cuenta() . "',
					'" . $gltrans->getPeriodno() . "',
					'" . $gltrans->getAccount() . "',
					'" . $gltrans->getNarrative() . "',
					'" . $gltrans->getTag() . "',
					'" . $gltrans->getUserid() . "',
					'" . $gltrans->getRate() . "',
					'" . $gltrans->getDebtorno() . "',
					'" . $gltrans->getBranchno() . "',
					'" . $gltrans->getStockid() . "',
					'" . $gltrans->getQty() . "',
					'" . $gltrans->getGrns() . "',
					'" . $gltrans->getStandardcost() . "',
					'" . $gltrans->getLoccode() . "',
					'" . date("Y-m-d H:i:s") . "',
					'" . $gltrans->getSuppno() . "',
					'" . $gltrans->getPurchno() . "',
					'" . $gltrans->getChequeno() . "',
					'" . $gltrans->getAmount() . "',
					'" . $gltrans->getJobref() . "',
					'" . $gltrans->getBancodestino() . "',
					'" . $gltrans->getRfcdestino() . "',
					'" . $gltrans->getCuentadestino() . "',
					'" . $gltrans->getUuid() . "',
					'" . $gltrans->getPosted() . "',
					'" . $gltrans->getLastusermod() . "',
					'" . date("Y-m-d H:i:s"). "'
		           	)"; 
               

		$resp = $modelo->ejecutarsql($sql);
		$message = "";

		if ($resp == true and !is_array($resp)){
			$success = false;
			$message = _('No se inserto registro en tabla de movimientos contables');

		}else{
			if ($resp == false){
				$msgerror = "ERROR EN LA CONSULTA";
				$typeerror = "MYSQL ERROR";
				$codeerror = "001";

			}else{
				$message = _('Se inserto registro en la tabla de movimientos contables');

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

	function Insertgltransshort($gltransshort,$modelo){

		$pathprefix = "../.././";
		
		
		$UserID = $_SESSION['UserID'];
		$success = true;
		$msgerror = "";
		$typeerror = "";
		$codeerror = 0;
		$sql = "";

		$sql = "INSERT INTO gltrans (
						type,
						typeno,
						trandate,
						cat_cuenta,
						periodno,
						account,
						narrative,
						tag,
						userid,
						rate,
						debtorno,
						branchno,
						stockid,
						qty,
						grns,
						standardcost,
						loccode,
						dateadded,
						suppno,
						purchno,
						chequeno,
						amount,
						jobref,
						bancodestino,
						rfcdestino,
						cuentadestino,
			            uuid,
						posted, 
						lastusermod,
						lastdatemod
				)
				VALUES (
					'" . $gltransshort->getType() . "',
					'" . $gltransshort->getTypeno() . "',
					'" . date("Y-m-d") . "',
					'" . $gltransshort->getCat_Cuenta() . "',
					'" . $gltransshort->getPeriodno() . "',
					'" . $gltransshort->getAccount() . "',
					'" . $gltransshort->getNarrative() . "',
					'" . $gltransshort->getTag() . "',
					'" . $gltransshort->getUserid() . "',
					'" . $gltransshort->getRate() . "',
					'" . $gltransshort->getDebtorno() . "',
					'" . $gltransshort->getBranchno() . "',
					'" . $gltransshort->getStockid() . "',
					'" . $gltransshort->getQty() . "',
					'" . $gltransshort->getGrns() . "',
					'" . $gltransshort->getStandardcost() . "',
					'" . $gltransshort->getLoccode() . "',
					'" . date("Y-m-d H:i:s") . "',
					'" . $gltransshort->getSuppno() . "',
					'" . $gltransshort->getPurchno() . "',
					'" . $gltransshort->getChequeno() . "',
					'" . $gltransshort->getAmount() . "',
					'" . $gltransshort->getJobref() . "',
					'" . $gltransshort->getBancodestino() . "',
					'" . $gltransshort->getRfcdestino() . "',
					'" . $gltransshort->getCuentadestino() . "',
					'" . $gltransshort->getUuid() . "',
					'" . $gltransshort->getPosted() . "',
					'" . $gltransshort->getLastusermod() . "',
					'" . date("Y-m-d H:i:s") . "'
		           	)"; 
               

		$resp = $modelo->ejecutarsql($sql);
		$message = "";

		$cadena = explode(' ', trim($sql));
        if ($resp == true and !is_array($resp) and ($cadena[0] != 'INSERT') and ($cadena[0] != 'UPDATE') and ($cadena[0] != 'DELETE')){
			$success = false;
			$message = _('No se inserto registro en tabla de movimientos contables');

		}else{
			if ($resp == false){
				$msgerror = "ERROR EN LA CONSULTA";
				$typeerror = "MYSQL ERROR";
				$codeerror = "001";

			}else{
				$message = _('Se inserto registro en la tabla de movimientos contables');

			}
			
		}

		$response['success'] = $success;
		$response['error']['msgerror'] = $msgerror;
		$response['error']['typeerror'] = $typeerror;
		$response['error']['codeerror'] = $codeerror;
		$response['error']['codeerror'] = $sql;
		$response['data']['message'] = $message;
		
		//return $response;
        
	}



}


?>