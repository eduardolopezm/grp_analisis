<?php
 
class Salesorderdetailsdao{

	function Getsalesorderdetails($stockid, $tagref, $location, $currency){
		$pathprefix = "../.././";
		require_once $pathprefix . 'core/ModeloBase.php';
		
		$UserID = $_SESSION["UserID"];
		$success = true;
		$msgerror = "";
		$typeerror = "";
		$codeerror = 0;
		$sql = "";

		/*VALORES DEFAULT*/

		$modelo = new ModeloBase();
		
		$sql = "SELECT s.stockid,
					s.description,
			       	s.units,
			       	l.quantity as qty, 
			       	CASE WHEN (l.quantity - l.qtybysend) > 0 THEN 1 ELSE 0 END as available, 
			       	p.price, 
			       	IFNULL(c.avgcost,0) as cost
			    FROM stockmaster s
			    	INNER JOIN locstock l ON s.stockid=l.stockid AND l.loccode = '" . $location . "'
                	INNER JOIN locations a ON l.loccode = a.loccode
                	INNER JOIN tags t ON a.tagref = t.tagref
                	INNER JOIN prices p ON s.stockid = p.stockid and p.currabrev = '" . $currency . "'
                	LEFT JOIN stockcostsxlegal c ON s.stockid = c.stockid and c.legalid = t.legalid
                WHERE s.stockid = '" . $stockid . "'
                LIMIT 1	";

        $resp = $modelo->ejecutarsql($sql);
		$message = "";

		if ($resp == true and !is_array($resp)){
			$success = false;
			$message = _('No existe registros para la busqueda del producto ' . $stockid);

		}else{
			if ($resp == false){
				$msgerror = "ERROR EN LA CONSULTA";
				$typeerror = "MYSQL ERROR";
				$codeerror = "001";

			}else{
				for($xx = 0; $xx < 1; $xx++){
					$arrdebtor = array(
							"NumRow" => 0,
							"stockid" => $resp[$xx]['stockid'],
							"description" => $resp[$xx]['description'],
							"units" => $resp[$xx]['units'],
							"qty" => $resp[$xx]['qty'],
							"available" => $resp[$xx]['available'],
							"price" => $resp[$xx]['price'],
							"discount" => 0,
							"cost" => $resp[$xx]['cost']
					);
					$response['data'][] = $arrdebtor;
				}
			}
			
		}
		
		$response['success'] = $success;
		$response['error']['msgerror'] = $msgerror;
		$response['error']['typeerror'] = $typeerror;
		$response['error']['codeerror'] = $codeerror;
		$response['error']['codeerror'] = $sql;
		$response['data']['message'] = $message;
		
		//header('Content-type: application/json; charset=utf-8');
  		//return json_encode($response, JSON_FORCE_OBJECT);
  		
  		return $response;

	}

	function getSalesOrderDetailsByOrderNo_ANT($ordeno) {
		$pathprefix = "../.././";
		require_once $pathprefix . 'core/ModeloBase.php';
		
		$UserID = $_SESSION["UserID"];
		$success = true;
		$msgerror = "";
		$typeerror = "";
		$codeerror = 0;
		$sql = "";

		$iva = '0';

		$arrproducts = array();

		$modelo = new ModeloBase;


		$sql = "SELECT stockmaster.stockid,
				       stockmaster.description,
				       stockmaster.categoryid,
				       stockmaster.units,
				       stockmaster.mbflag,
				       stockmaster.taxcatid,
				       salesorderdetails.quantity,
				       salesorderdetails.unitprice as price,
				       salesorderdetails.discountpercent as discount,
				       salesorderdetails.fromstkloc,
				       IFNULL(locstock.quantity - locstock.ontransit,0) as available
				FROM salesorderdetails
				INNER JOIN stockmaster ON stockmaster.stockid = salesorderdetails.stkcode
				LEFT JOIN locstock ON locstock.stockid = stockmaster.stockid and locstock.loccode = salesorderdetails.fromstkloc
				WHERE orderno = '" . $ordeno . "'";
    

		$resp = $modelo->ejecutarsql($sql);
		$message = "";

		if ($resp == true and !is_array($resp)){
			$success = false;
			$message = _('No existe registros con el numero de orden ' . $ordeno);

		}else{
			if ($resp == false){
				$msgerror = "ERROR EN LA CONSULTA";
				$typeerror = "MYSQL ERROR";
				$codeerror = "001";

			}else{
				for($xx = 0; $xx < count($resp); $xx++){
					
					$arrproduct = array(
							"stockid" => ($resp[$xx]['stockid']),
							"categoryid" => ($resp[$xx]['categoryid']),
							"description" => ($resp[$xx]['description']),
							"units" => ($resp[$xx]['units']),
							"mbflag" => ($resp[$xx]['mbflag']),
							"taxcatid" => ($resp[$xx]['taxcatid']),
							"quantity" => ($resp[$xx]['quantity']),
							"price" => ($resp[$xx]['price']),
							"discount" => ($resp[$xx]['discount']*100),
							"available" => $resp[$xx]['available'],
							"subtotal" => $resp[$xx]['quantity'] * $resp[$xx]['price'], //subtotal 
							"iva" => $iva * $resp[$xx]['price'], //iva
							"total" => ($resp[$xx]['quantity'] * $resp[$xx]['price']) + ($iva * $resp[$xx]['price']) //total
						);

					array_push($arrproducts, $arrproduct);

				}
			}
			
		}
		
		$response['success'] = $success;
		$response['error']['msgerror'] = $msgerror;
		$response['error']['typeerror'] = $typeerror;
		$response['error']['codeerror'] = $codeerror;
		$response['error']['codeerror'] = $sql;
		$response['data'] = $arrproducts;
		
		//header('Content-type: application/json; charset=utf-8');
  		//return json_encode($response, JSON_FORCE_OBJECT);
  		
  		return $response;
	}

	function getSalesOrderDetailsByOrderNo($ordeno){

		$pathprefix = "../.././";
		require_once $pathprefix . 'core/ModeloBase.php';
		
		$UserID = $_SESSION["UserID"];
		$success = true;
		$msgerror = "";
		$typeerror = "";
		$codeerror = 0;
		$sql = "";

		$iva = '0';

		$arrproducts = array();

		$modelo = new ModeloBase;

		$sql = "SELECT s.stockid,  s.categoryid, s.description, s.units, s.mbflag, s.taxcatid, taxauthrates.taxrate , salesorderdetails.unitprice as price, IFNULL(c.avgcost,0) as cost, salesorders.ordertype as typeabbrev, 999999999 as available, s.disminuye_ingreso, salesorderdetails.discountpercent as discount, salesorderdetails.quantity, salesorderdetails.id_administracion_contratos, DATE_FORMAT(salesorders.orddate, '%d-%m-%Y') as orddate
		FROM salesorderdetails
		JOIN salesorders ON salesorders.orderno = salesorderdetails.orderno
		JOIN stockmaster s ON s.stockid = salesorderdetails.stkcode
		LEFT JOIN stockcostsxlegal c ON s.stockid = c.stockid
		LEFT JOIN legalbusinessunit l ON c.legalid = l.legalid
		LEFT JOIN tags t ON l.legalid = t.legalid and t.tagref = salesorders.tagref
		LEFT JOIN taxauthrates ON taxauthrates.taxcatid = s.taxcatid
		WHERE s.tipo_dato = 2
		AND salesorderdetails.orderno = '".$ordeno."'";

		$resp = $modelo->ejecutarsql($sql);
		$message = "";

		if ($resp == true and !is_array($resp)){
			$success = false;
			$message = _('No existe registros para la busqueda de ' . $descrip);
		}else{
			if ($resp == false){
				$msgerror = "ERROR EN LA CONSULTA";
				$typeerror = "MYSQL ERROR";
				$codeerror = "001";
			}else{
				for($xx = 0; $xx < count($resp); $xx++){
					$stockid = $resp[$xx]['stockid'];
					$quantity = $resp[$xx]['quantity'];
					$orddate = strtotime($resp[$xx]['orddate']);
					$orddateVal = strtotime('05-02-2020');

					//Obtener datos de las listas de precios
					$arrListas = array();
					$sql = "SELECT prices.currabrev, prices.price, salestypes.typeabbrev, salestypes.sales_type, prices.norango_inicial, prices.norango_final, prices.isRango
					FROM salestypes
					LEFT JOIN prices ON prices.typeabbrev = salestypes.typeabbrev and prices.stockid = '".$stockid."'
					GROUP BY salestypes.typeabbrev";
					$resp2 = $modelo->ejecutarsql($sql);
					if ($resp2 == false){
						$msgerror = "ERROR EN LA CONSULTA";
						$typeerror = "MYSQL ERROR";
						$codeerror = "002";
					}else{
						for($yy = 0; $yy < count($resp2); $yy++){
							if ($orddate < $orddateVal) {
								$resp2[$yy]['price'] = number_format($resp[$xx]['price'], 2, '.', '');
							}
							$datosLista = array(
								"currabrev" => $resp2[$yy]['currabrev'],
								"price" => ($resp2[$yy]['price'] != "" ? $resp2[$yy]['price'] : 0),
								"typeabbrev" => $resp2[$yy]['typeabbrev'],
								"sales_type" => $resp2[$yy]['sales_type'],
								"norango_inicial" => $resp2[$yy]['norango_inicial'],
								"norango_final" => $resp2[$yy]['norango_final'],
								"isRango" => $resp2[$yy]['isRango']
							);

							array_push($arrListas, $datosLista);
						}
					}

					$iva = $resp[$xx]['taxrate'];

					if (empty($resp[$xx]['disminuye_ingreso'])) {
						$resp[$xx]['disminuye_ingreso'] = 0;
					}

					if ($resp[$xx]['disminuye_ingreso'] == '1') {
						// $quantity = abs($quantity) * -1;
					}

					if (empty($resp[$xx]['id_administracion_contratos'])) {
						$resp[$xx]['id_administracion_contratos'] = 0;
					}
					
					$arrproduct = array(
							"NumRow" => 0,
							"stockid" => $stockid,
							"categoryid" => ( ($resp[$xx]['categoryid']) ),
							"description" => $resp[$xx]['description'],
							"units" => ( ($resp[$xx]['units']) ),
							"mbflag" => ( ($resp[$xx]['mbflag']) ),
							"taxcatid" => ( ($resp[$xx]['taxcatid']) ),
							"quantity" => $quantity,
							"price" => $resp[$xx]['price'], //Precio para realizar operaciones
							"priceFormat" => number_format($resp[$xx]['price'], 2, ',', ' '), //Mostrar precio formateado
							"discount" => ($resp[$xx]['discount']*100),
							"salestype" => $resp[$xx]['typeabbrev'],
							"cost" => $resp[$xx]['cost'],
							"available" => $resp[$xx]['available'],
							"disminuye_ingreso" => $resp[$xx]['disminuye_ingreso'],
							"taxrate" => $iva,
							"vacio" => "", //seleccionar cotizacion no visualiza datos
							"subtotal" => $quantity * $resp[$xx]['price'],
							"iva" => $iva * $resp[$xx]['price'],
							"total" => ($quantity * $resp[$xx]['price']) + ($iva * $resp[$xx]['price']),
							"idcontrato" => $resp[$xx]['id_administracion_contratos'],
							"listasPrecio" => $arrListas
						);

					//$response['data'][] = array("0"=>$arrproduct);
					//$arrproducts[] = $arrproduct;
					array_push($arrproducts, $arrproduct);
				}
			}
		}
		
		$response['success'] = $success;
		$response['error']['msgerror'] = $msgerror;
		$response['error']['typeerror'] = $typeerror;
		$response['error']['codeerror'] = $sql;
		$response['data']['message'] = $message;
		$response['data'] = $arrproducts;
		
		//header('Content-type: application/json; charset=utf-8');
  		//return json_encode($response, JSON_FORCE_OBJECT);
  		
  		return $response;
	}
}
?>