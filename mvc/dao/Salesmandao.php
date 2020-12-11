<?php
  
/*CLASE DE VENDEDORES*/
class Salesmandao{
	private $pathprefix = "../.././";

	function Getsalesman(){

		require_once $this->pathprefix . 'core/ModeloBase.php';

		$UserID = $_SESSION["UserID"];
		$success = true;
		$msgerror = "";
		$typeerror = "";
		$codeerror = 0;
		$arrsalesmen = array();
		
		$modelo = new ModeloBase;


		//$descrip = str_replace(' ', '%', $descrip);
		$sql = "SELECT salesmancode, salesmanname, usersales
				FROM salesman
				WHERE status = 'Active' and type = 1
				ORDER BY salesmanname asc";

		$resp = $modelo->ejecutarsql($sql);
		$message = "";

		if ($resp == true and !is_array($resp)){
			$success = false; 
			$message = _('No existe registros para la busqueda de vendedor');
		}else{
			if ($resp == false){
				$msgerror = "ERROR EN LA CONSULTA";
				$typeerror = "MYSQL ERROR";
				$codeerror = "001";

			}else{
				$seleccion = 0;
				for($xx = 0; $xx < count($resp); $xx++){
					$seleccion = 0;
					if ($_SESSION['UserID'] == $resp[$xx]['usersales']) {
						$seleccion = 1; //Usuario y vendedor
					}
					
					$arrsalesman = array(
							"salesmancode" => utf8_encode($resp[$xx]['salesmancode']),
							"salesmanname" => utf8_encode($resp[$xx]['salesmanname']),
							"seleccion" => $seleccion //Dato seleccionado en punto de venta
						);

					//$response['data'][] = array("0"=>$arrproduct);
					//$arrproducts[] = $arrproduct;
					array_push($arrsalesmen, $arrsalesman);

				}
			}
			
		}
		
		$response['success'] = $success;
		$response['error']['msgerror'] = $msgerror;
		$response['error']['typeerror'] = $typeerror;
		$response['error']['codeerror'] = $sql;
		$response['data']['message'] = $message;
		$response['data'] = $arrsalesmen;
		
		//header('Content-type: application/json; charset=utf-8');
  		//return json_encode($response, JSON_FORCE_OBJECT);
  		//test
  		
  		return $response;

	}


	function Stockbyid($id){

		require_once $this->pathprefix . 'core/ModeloBase.php';
		require_once $this->pathprefix . 'model/Stockmaster.php';

		$UserID = $_SESSION["UserID"];
		$success = true;
		$msgerror = "";
		$typeerror = "";
		$codeerror = 0;
		$sql = "";

		$modelo = new ModeloBase;

		$sql = "SELECT stockid,
						spes,
						categoryid,
						description,
						longdescription,
						manufacturer,
						stockautor,
						units,
						mbflag,
						lastcurcostdate,
						actualcost,
						lastcost,
						materialcost,
						labourcost,
						overheadcost,
						lowestlevel,
						discontinued,
						controlled,
						enviarmasisa,
						eoq,
						volume,
						kgs,
						barcode,
						discountcategory,
						taxcatid,
						taxcatidret,
						serialised,
						appendfile,
						perishable,
						decimalplaces,
						nextserialno,
						pansize,
						shrinkfactor,
						netweight,
						idclassproduct,
						stocksupplier,
						securitypoint,
						idetapaflujo,
						OrigenCountry,
						OrigenDate,
						stockupdate,
						pkg_type,
						isbn,
						grade,
						subject,
						deductibleflag,
						u_typeoperation,
						typeoperationdiot,
						height,
						width,
						large,
						fichatecnica,
						percentfactorigi,
						inpdfgroup,
						flagadvance,
						eq_conversion_factor,
						eq_stockid,
						flagcommission,
						fijo,
						fecha_modificacion,
						unitequivalent,
						factorconversionpaq,
						factorconversionpz,
						stockneodata,
						purchgroup,
						idjerarquia,
						addunits,
						secuunits,
						recipeunits,
						factorrecipe,
						addcategory,
						deliverydays,
						tolerancedays,
						estatusstock,
						eq_conversion_costo,
						extracolone,
						extracoltwo,
						extracolthree,
						unitstemporal,
						SAPActualiza,
						depreciacion,
						valor_inicial
					FROM stockmaster
					WHERE stockid = '" . $id . "'";
        
        $resp = $modelo->ejecutarsql($sql);
		$message = "";

		if ($resp == true and !is_array($resp)){
			$success = false;
			$message = _('No existe registros para la busqueda de ' . $id);

		}else{
			if ($resp == false){
				$msgerror = "ERROR EN LA CONSULTA";
				$typeerror = "MYSQL ERROR";
				$codeerror = "001";

			}else{
				
				$stockmaster = new Stockmaster;
				for($xx = 0; $xx < count($resp); $xx++){
					$stockmaster->setStockid($resp[$xx]['stockid']);
					$stockmaster->setSpes($resp[$xx]['spes']);
					$stockmaster->setCategoryid($resp[$xx]['categoryid']);
					$stockmaster->setDescription($resp[$xx]['description']);
					$stockmaster->setLongdescription($resp[$xx]['longdescription']);
					$stockmaster->setManufacturer($resp[$xx]['manufacturer']);
					$stockmaster->setStockautor($resp[$xx]['stockautor']);
					$stockmaster->setUnits($resp[$xx]['units']);
					$stockmaster->setMbflag($resp[$xx]['mbflag']);
					$stockmaster->setLastcurcostdate($resp[$xx]['lastcurcostdate']);
					$stockmaster->setActualcost($resp[$xx]['actualcost']);
					$stockmaster->setLastcost($resp[$xx]['lastcost']);
					$stockmaster->setMaterialcost($resp[$xx]['materialcost']);
					$stockmaster->setLabourcost($resp[$xx]['labourcost']);
					$stockmaster->setOverheadcost($resp[$xx]['overheadcost']);
					$stockmaster->setLowestlevel($resp[$xx]['lowestlevel']);
					$stockmaster->setDiscontinued($resp[$xx]['discontinued']);
					$stockmaster->setControlled($resp[$xx]['controlled']);
					$stockmaster->setEnviarmasisa($resp[$xx]['enviarmasisa']);
					$stockmaster->setEoq($resp[$xx]['eoq']);
					$stockmaster->setVolume($resp[$xx]['volume']);
					$stockmaster->setKgs($resp[$xx]['kgs']);
					$stockmaster->setBarcode($resp[$xx]['barcode']);
					$stockmaster->setDiscountcategory($resp[$xx]['discountcategory']);
					$stockmaster->setTaxcatid($resp[$xx]['taxcatid']);
					$stockmaster->setTaxcatidret($resp[$xx]['taxcatidret']);
					$stockmaster->setSerialised($resp[$xx]['serialised']);
					$stockmaster->setAppendfile($resp[$xx]['appendfile']);
					$stockmaster->setPerishable($resp[$xx]['perishable']);
					$stockmaster->setDecimalplaces($resp[$xx]['decimalplaces']);
					$stockmaster->setNextserialno($resp[$xx]['nextserialno']);
					$stockmaster->setPansize($resp[$xx]['pansize']);
					$stockmaster->setShrinkfactor($resp[$xx]['shrinkfactor']);
					$stockmaster->setNetweight($resp[$xx]['netweight']);
					$stockmaster->setIdclassproduct($resp[$xx]['idclassproduct']);
					$stockmaster->setStocksupplier($resp[$xx]['stocksupplier']);
					$stockmaster->setSecuritypoint($resp[$xx]['securitypoint']);
					$stockmaster->setIdetapaflujo($resp[$xx]['idetapaflujo']);
					$stockmaster->setOrigencountry($resp[$xx]['OrigenCountry']);
					$stockmaster->setOrigendate($resp[$xx]['OrigenDate']);
					$stockmaster->setStockupdate($resp[$xx]['stockupdate']);
					$stockmaster->setPkg_Type($resp[$xx]['pkg_type']);
					$stockmaster->setIsbn($resp[$xx]['isbn']);
					$stockmaster->setGrade($resp[$xx]['grade']);
					$stockmaster->setSubject($resp[$xx]['subject']);
					$stockmaster->setDeductibleflag($resp[$xx]['deductibleflag']);
					$stockmaster->setU_Typeoperation($resp[$xx]['u_typeoperation']);
					$stockmaster->setTypeoperationdiot($resp[$xx]['typeoperationdiot']);
					$stockmaster->setHeight($resp[$xx]['height']);
					$stockmaster->setWidth($resp[$xx]['width']);
					$stockmaster->setLarge($resp[$xx]['large']);
					$stockmaster->setFichatecnica($resp[$xx]['fichatecnica']);
					$stockmaster->setPercentfactorigi($resp[$xx]['percentfactorigi']);
					$stockmaster->setInpdfgroup($resp[$xx]['inpdfgroup']);
					$stockmaster->setFlagadvance($resp[$xx]['flagadvance']);
					$stockmaster->setEq_Conversion_Factor($resp[$xx]['eq_conversion_factor']);
					$stockmaster->setEq_Stockid($resp[$xx]['eq_stockid']);
					$stockmaster->setFlagcommission($resp[$xx]['flagcommission']);
					$stockmaster->setFijo($resp[$xx]['fijo']);
					$stockmaster->setFecha_Modificacion($resp[$xx]['fecha_modificacion']);
					$stockmaster->setUnitequivalent($resp[$xx]['unitequivalent']);
					$stockmaster->setFactorconversionpaq($resp[$xx]['factorconversionpaq']);
					$stockmaster->setFactorconversionpz($resp[$xx]['factorconversionpz']);
					$stockmaster->setStockneodata($resp[$xx]['stockneodata']);
					$stockmaster->setPurchgroup($resp[$xx]['purchgroup']);
					$stockmaster->setIdjerarquia($resp[$xx]['idjerarquia']);
					$stockmaster->setAddunits($resp[$xx]['addunits']);
					$stockmaster->setSecuunits($resp[$xx]['secuunits']);
					$stockmaster->setRecipeunits($resp[$xx]['recipeunits']);
					$stockmaster->setFactorrecipe($resp[$xx]['factorrecipe']);
					$stockmaster->setAddcategory($resp[$xx]['addcategory']);
					$stockmaster->setDeliverydays($resp[$xx]['deliverydays']);
					$stockmaster->setTolerancedays($resp[$xx]['tolerancedays']);
					$stockmaster->setEstatusstock($resp[$xx]['estatusstock']);
					$stockmaster->setEq_Conversion_Costo($resp[$xx]['eq_conversion_costo']);
					$stockmaster->setExtracolone($resp[$xx]['extracolone']);
					$stockmaster->setExtracoltwo($resp[$xx]['extracoltwo']);
					$stockmaster->setExtracolthree($resp[$xx]['extracolthree']);
					$stockmaster->setUnitstemporal($resp[$xx]['unitstemporal']);
					$stockmaster->setSapactualiza($resp[$xx]['SAPActualiza']);
					$stockmaster->setDepreciacion($resp[$xx]['depreciacion']);
					$stockmaster->setValor_Inicial($resp[$xx]['valor_inicial']);
					
					//$response['data'][] = $stockmaster;
				}
			}
			
		}
		
		$response['success'] = $success;
		$response['error']['msgerror'] = $msgerror;
		$response['error']['typeerror'] = $typeerror;
		$response['error']['codeerror'] = $codeerror;
		$response['error']['codeerror'] = $sql;
		$response['data']['message'] = $message;
		$response['data']['object'] = (array)$stockmaster;
		
		//header('Content-type: application/json; charset=utf-8');
  		//echo json_encode($response, JSON_FORCE_OBJECT);
  		
  		return $response;

	}


	


}


?>