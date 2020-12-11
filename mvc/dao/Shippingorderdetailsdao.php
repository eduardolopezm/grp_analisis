<?php
 
class Shippingorderdetailsdao{

	function Insertshippingorderdetails($shippingorderdetails){

		$pathprefix = "../.././";
		require_once $pathprefix . 'core/ModeloBase.php';
		
		$UserID = $_SESSION["UserID"];
		$success = true;
		$msgerror = "";
		$typeerror = "";
		$codeerror = 0;
		$sql = "";

		$modelo = new ModeloBase;


		$sql =	"INSERT INTO shippingorderdetails(
						shippingno,
						stkmoveno,
						stockid, 
						qty,
						qty_sent, 
						price,
						loccode,
						avgcost
					)
					SELECT " . $transnoentrega . ",
						stkmoveno,
						stockmoves.stockid,
						qty*-1,
						0,
						price,
						loccode,
						stockmoves.standardcost
					FROM stockmoves 
						INNER JOIN stockmaster on stockmaster.stockid=stockmoves.stockid
					WHERE stockmaster.mbflag!='D' 
						AND type=" . $tipodefacturacion . " AND transno=" . $InvoiceNo . " AND narrative NOT Like '%Ensamble%'";
               

		$resp = $modelo->ejecutarsql($sql);
		$message = "";

		if ($resp == true and !is_array($resp)){
			$success = false;
			$message = _('No se inserto registro en tabla de detalles de embarques');

		}else{
			if ($resp == false){
				$msgerror = "ERROR EN LA CONSULTA";
				$typeerror = "MYSQL ERROR";
				$codeerror = "001";

			}else{
				$message = _('Se inserto registro en la tabla de detalles de embarques');

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