<?php
 
class Custbranchdao{
	private $pathprefix = "../.././";
	
	function Custbranchbyid($id){
		//$pathprefix = "../.././";
		require_once $this->pathprefix . 'core/ModeloBase.php';
		require_once $this->pathprefix . 'model/Custbranch.php';

		$UserID = $_SESSION['UserID'];
		$success = true;
		$msgerror = "";
		$typeerror = "";
		$codeerror = 0;
		$arrproducts = array();
		$sql = "";

		$modelo1 = new ModeloBase();

		$sql = "SELECT branchcode,
						debtorno,
						brname,
						taxid,
						braddress1,
						braddress2,
						braddress3,
						braddress4,
						braddress5,
						braddress6,
						lat,
						lng,
						estdeliverydays,
						area,
						salesman,
						fwddate,
						phoneno,
						faxno,
						contactname,
						email,
						lineofbusiness,
						flagworkshop,
						defaultlocation,
						taxgroupid,
						defaultshipvia,
						deliverblind,
						disabletrans,
						brpostaddr1,
						brpostaddr2,
						brpostaddr3,
						brpostaddr4,
						brpostaddr5,
						brpostaddr6,
						specialinstructions,
						custbranchcode,
						creditlimit,
						custdata1,
						custdata2,
						custdata3,
						custdata4,
						custdata5,
						custdata6,
						ruta,
						brnumint,
						brnumext,
						paymentname,
						nocuenta,
						NumeAsigCliente,
						descclientecomercial,
						descclientepropago,
						descclienteop,
						typeaddenda,
						movilno,
						nextelno,
						welcomemail,
						SectComClId,
						custpais,
						braddress7,
						DiasRevicion,
						DiasPago,
						fecha_modificacion,
						namebank,
						logocliente,
						idprospecmedcontacto,
						idproyecto,
						prefer,
						discountcard,
						typecomplement
				FROM custbranch
				WHERE branchcode = '" . $id . "'";
        
        $resp = $modelo1->ejecutarsql($sql);
		$message = "";
		$custbranch = new Custbranch;
		if ($resp == true and !is_array($resp)){
			$success = false;
			$message = _('No existe registros para la busqueda de ' . $id);

		}else{
			if ($resp == false){
				$msgerror = "ERROR EN LA CONSULTA";
				$typeerror = "MYSQL ERROR";
				$codeerror = "001";

			}else{
				
				for($xx = 0; $xx < count($resp); $xx++){
					$custbranch->setBranchcode($resp[$xx]['branchcode']);
					$custbranch->setDebtorno($resp[$xx]['debtorno']);
					$custbranch->setBrname($resp[$xx]['brname']);
					$custbranch->setTaxid($resp[$xx]['taxid']);
					$custbranch->setBraddress1($resp[$xx]['braddress1']);
					$custbranch->setBraddress2($resp[$xx]['braddress2']);
					$custbranch->setBraddress3($resp[$xx]['braddress3']);
					$custbranch->setBraddress4($resp[$xx]['braddress4']);
					$custbranch->setBraddress5($resp[$xx]['braddress5']);
					$custbranch->setBraddress6($resp[$xx]['braddress6']);
					$custbranch->setLat($resp[$xx]['lat']);
					$custbranch->setLng($resp[$xx]['lng']);
					$custbranch->setEstdeliverydays($resp[$xx]['estdeliverydays']);
					$custbranch->setArea($resp[$xx]['area']);
					$custbranch->setSalesman($resp[$xx]['salesman']);
					$custbranch->setFwddate($resp[$xx]['fwddate']);
					$custbranch->setPhoneno($resp[$xx]['phoneno']);
					$custbranch->setFaxno($resp[$xx]['faxno']);
					$custbranch->setContactname($resp[$xx]['contactname']);
					$custbranch->setEmail($resp[$xx]['email']);
					$custbranch->setLineofbusiness($resp[$xx]['lineofbusiness']);
					$custbranch->setFlagworkshop($resp[$xx]['flagworkshop']);
					$custbranch->setDefaultlocation($resp[$xx]['defaultlocation']);
					$custbranch->setTaxgroupid($resp[$xx]['taxgroupid']);
					$custbranch->setDefaultshipvia($resp[$xx]['defaultshipvia']);
					$custbranch->setDeliverblind($resp[$xx]['deliverblind']);
					$custbranch->setDisabletrans($resp[$xx]['disabletrans']);
					$custbranch->setBrpostaddr1($resp[$xx]['brpostaddr1']);
					$custbranch->setBrpostaddr2($resp[$xx]['brpostaddr2']);
					$custbranch->setBrpostaddr3($resp[$xx]['brpostaddr3']);
					$custbranch->setBrpostaddr4($resp[$xx]['brpostaddr4']);
					$custbranch->setBrpostaddr5($resp[$xx]['brpostaddr5']);
					$custbranch->setBrpostaddr6($resp[$xx]['brpostaddr6']);
					$custbranch->setSpecialinstructions($resp[$xx]['specialinstructions']);
					$custbranch->setCustbranchcode($resp[$xx]['custbranchcode']);
					$custbranch->setCreditlimit($resp[$xx]['creditlimit']);
					$custbranch->setCustdata1($resp[$xx]['custdata1']);
					$custbranch->setCustdata2($resp[$xx]['custdata2']);
					$custbranch->setCustdata3($resp[$xx]['custdata3']);
					$custbranch->setCustdata4($resp[$xx]['custdata4']);
					$custbranch->setCustdata5($resp[$xx]['custdata5']);
					$custbranch->setCustdata6($resp[$xx]['custdata6']);
					$custbranch->setRuta($resp[$xx]['ruta']);
					$custbranch->setBrnumint($resp[$xx]['brnumint']);
					$custbranch->setBrnumext($resp[$xx]['brnumext']);
					$custbranch->setPaymentname($resp[$xx]['paymentname']);
					$custbranch->setNocuenta($resp[$xx]['nocuenta']);
					$custbranch->setNumeasigcliente($resp[$xx]['NumeAsigCliente']);
					$custbranch->setDescclientecomercial($resp[$xx]['descclientecomercial']);
					$custbranch->setDescclientepropago($resp[$xx]['descclientepropago']);
					$custbranch->setDescclienteop($resp[$xx]['descclienteop']);
					$custbranch->setTypeaddenda($resp[$xx]['typeaddenda']);
					$custbranch->setMovilno($resp[$xx]['movilno']);
					$custbranch->setNextelno($resp[$xx]['nextelno']);
					$custbranch->setWelcomemail($resp[$xx]['welcomemail']);
					$custbranch->setSectcomclid($resp[$xx]['SectComClId']);
					$custbranch->setCustpais($resp[$xx]['custpais']);
					$custbranch->setBraddress7($resp[$xx]['braddress7']);
					$custbranch->setDiasrevicion($resp[$xx]['DiasRevicion']);
					$custbranch->setDiaspago($resp[$xx]['DiasPago']);
					$custbranch->setFecha_Modificacion($resp[$xx]['fecha_modificacion']);
					$custbranch->setNamebank($resp[$xx]['namebank']);
					$custbranch->setLogocliente($resp[$xx]['logocliente']);
					$custbranch->setIdprospecmedcontacto($resp[$xx]['idprospecmedcontacto']);
					$custbranch->setIdproyecto($resp[$xx]['idproyecto']);
					$custbranch->setPrefer($resp[$xx]['prefer']);
					$custbranch->setDiscountcard($resp[$xx]['discountcard']);
					$custbranch->setTypecomplement($resp[$xx]['typecomplement']);

				}
			}
			
		}
		
		/*
		$response['success'] = $success;
		$response['error']['msgerror'] = $msgerror;
		$response['error']['typeerror'] = $typeerror;
		//$response['error']['codeerror'] = $codeerror;
		$response['error']['codeerror'] = $sql;
		$response['data']['message'] = $message;
		$response['data']['object'] = (array) $custbranch;
		*/
		//var_dump($custbranch);
		//header('Content-type: application/json; charset=utf-8');
  		//return json_encode($response, JSON_FORCE_OBJECT);
  		return $custbranch;
  		

	}



}


?>