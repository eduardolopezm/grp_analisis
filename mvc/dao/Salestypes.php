<?php
  
/*CLASE DE LISTAS DE PRECIO*/
class Salestypes{
	private $pathprefix = "../.././";

	function Getsalestypes($branchcode){

		require_once $this->pathprefix . 'core/ModeloBase.php';

		$UserID = $_SESSION["UserID"];
		$success = true;
		$msgerror = "";
		$typeerror = "";
		$codeerror = 0;
		$arraySalesTypes = array();
		
		$modelo = new ModeloBase;

		// Si no tiene configuracion de lista de precios, tomar una por default
		$sql = "SELECT distinct salestypes.typeabbrev,salestypes.sales_type
				FROM salestypes WHERE salestypes.anio = '".$_SESSION['ejercicioFiscal']."'";
		$resp = $modelo->ejecutarsql($sql);
		$message = "";

		if ($resp == true and !is_array($resp)){
			$success = false; 
			$message = _('No existe registros para la busqueda del cliente');
		}else{
			if ($resp == false){
				$msgerror = "ERROR EN LA CONSULTA";
				$typeerror = "MYSQL ERROR";
				$codeerror = "001";
			}else{
				for($xx = 0; $xx < count($resp); $xx++){
					$seleccion = 0;
					$arrsalestypes = array(
							"typeabbrev" => utf8_encode($resp[$xx]['typeabbrev']),
							"sales_type" => utf8_encode($resp[$xx]['sales_type']),
							"seleccion" => $seleccion //Dato seleccionado en punto de venta
						);

					//$response['data'][] = array("0"=>$arrproduct);
					//$arrproducts[] = $arrproduct;
					array_push($arraySalesTypes, $arrsalestypes);
				}
			}
		}
		
		$response['success'] = $success;
		$response['error']['msgerror'] = $msgerror;
		$response['error']['typeerror'] = $typeerror;
		$response['error']['codeerror'] = $sql;
		$response['data']['message'] = $message;
		$response['data'] = $arraySalesTypes;
		
		//header('Content-type: application/json; charset=utf-8');
  		//return json_encode($response, JSON_FORCE_OBJECT);
  		//test
  		
  		return $response;

	}

	function GetsalestypesANTES($branchcode){

		require_once $this->pathprefix . 'core/ModeloBase.php';

		$UserID = $_SESSION["UserID"];
		$success = true;
		$msgerror = "";
		$typeerror = "";
		$codeerror = 0;
		$arraySalesTypes = array();
		
		$modelo = new ModeloBase;

		if ($branchcode == 'SinCliente' || $branchcode == '') {
			// Si no tiene configuracion de lista de precios, tomar una por default
			$sql = "SELECT distinct salestypes.typeabbrev,salestypes.sales_type
					FROM salestypes WHERE salestypes.anio = '".$_SESSION['ejercicioFiscal']."'";
			$resp = $modelo->ejecutarsql($sql);
			$message = "";

			if ($resp == true and !is_array($resp)){
				$success = false; 
				$message = _('No existe registros para la busqueda del cliente');
			}else{
				if ($resp == false){
					$msgerror = "ERROR EN LA CONSULTA";
					$typeerror = "MYSQL ERROR";
					$codeerror = "001";
				}else{
					for($xx = 0; $xx < count($resp); $xx++){
						$seleccion = 0;
						$arrsalestypes = array(
								"typeabbrev" => utf8_encode($resp[$xx]['typeabbrev']),
								"sales_type" => utf8_encode($resp[$xx]['sales_type']),
								"seleccion" => $seleccion //Dato seleccionado en punto de venta
							);

						//$response['data'][] = array("0"=>$arrproduct);
						//$arrproducts[] = $arrproduct;
						array_push($arraySalesTypes, $arrsalestypes);
					}
				}
			}
		} else {
			$tipocliente = "";
			$salestypescliente = "";
			$sql = "SELECT typeid, salestype FROM debtorsmaster WHERE debtorno='".$branchcode."'";
			$resp = $modelo->ejecutarsql($sql);
			$message = "";

			if ($resp == true and !is_array($resp)){
				$success = false; 
				$message = _('No existe registros para la busqueda del cliente');
			}else{
				if ($resp == false){
					$msgerror = "ERROR EN LA CONSULTA";
					$typeerror = "MYSQL ERROR";
					$codeerror = "001";

				}else{
					for($xx = 0; $xx < count($resp); $xx++){
						$tipocliente = utf8_encode($resp[$xx]['typeid']);
						$salestypescliente = utf8_encode($resp[$xx]['salestype']);
					}
				}
			}

			$sql = "SELECT distinct salestypes.typeabbrev,salestypes.sales_type
					FROM salestypes, sec_pricelist, salestypesxcustomer
					WHERE sec_pricelist.pricelist = salestypes.typeabbrev
					AND salestypesxcustomer.typeabbrev = salestypes.typeabbrev
					AND salestypesxcustomer.typeclient='" . $tipocliente . "'
					AND sec_pricelist.userid='" . $_SESSION ['UserID'] . "'
					OR salestypes.typeabbrev='" . $salestypescliente . "'";
			$resp = $modelo->ejecutarsql($sql);
			$message = "";

			if ($resp == true and !is_array($resp)){
				$success = false; 
				$message = _('No existe registros para la busqueda del cliente');
			}else{
				if ($resp == false){
					$msgerror = "ERROR EN LA CONSULTA";
					$typeerror = "MYSQL ERROR";
					$codeerror = "001";

				}else{
					for($xx = 0; $xx < count($resp); $xx++){
						$seleccion = 0;
						$arrsalestypes = array(
								"typeabbrev" => utf8_encode($resp[$xx]['typeabbrev']),
								"sales_type" => utf8_encode($resp[$xx]['sales_type']),
								"seleccion" => $seleccion //Dato seleccionado en punto de venta
							);

						//$response['data'][] = array("0"=>$arrproduct);
						//$arrproducts[] = $arrproduct;
						array_push($arraySalesTypes, $arrsalestypes);
					}
				}
			}
		}
		
		$response['success'] = $success;
		$response['error']['msgerror'] = $msgerror;
		$response['error']['typeerror'] = $typeerror;
		$response['error']['codeerror'] = $sql;
		$response['data']['message'] = $message;
		$response['data'] = $arraySalesTypes;
		
		//header('Content-type: application/json; charset=utf-8');
  		//return json_encode($response, JSON_FORCE_OBJECT);
  		//test
  		
  		return $response;

	}
}
?>