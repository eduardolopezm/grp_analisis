<?php
 
class Debtorsmasterdao{
	private $pathprefix = "../.././";

	function Searchdebtors($descrip){

		require_once $this->pathprefix . 'core/ModeloBase.php';

		$UserID = $_SESSION['UserID'];
		$success = true;
		$msgerror = "";
		$typeerror = "";
		$codeerror = 0;
		$arrproducts = array();
		$sql = "";
		$arrdebtors = array();

		$modelo = new ModeloBase;

		$descrip = str_replace(' ', '%', $descrip);
		
		$sql = "SELECT custbranch.brname,
		custbranch.contactname,
		custbranch.phoneno,
		custbranch.faxno,
		custbranch.branchcode,
		custbranch.debtorno,
		debtorsmaster.name,
		custbranch.taxid,
		debtorsmaster.currcode,
		concat(brpostaddr1,' ',debtorsmaster.numExt,' ',brpostaddr2,', ',brpostaddr3)  as ciudad,
		custbranch.braddress2 as mpio,
		debtorsmaster.blacklist,
		vehiclesbycostumer.vehicleno,
		concat(vehiclesbycostumer.plate,' / ',vehiclesbycostumer.serie,' / ',vehiclesbycostumer.numeco) as plate,
		vehiclemarks.mark,vehiclemodels.model,
		debtorsmaster.nameextra,
		custbranch.email,
		debtorsmaster.numExt,
		debtorsmaster.address1,
		debtorsmaster.address2,
		debtorsmaster.address3,
		debtorsmaster.address4,
		debtorsmaster.address5,
		debtorsmaster.address6,
		debtorsmaster.usoCFDI,
		sat_usocfdi.descripcion as usoCFDIName
		FROM custbranch 
		INNER JOIN debtorsmaster ON custbranch.debtorno = debtorsmaster.debtorno
		INNER JOIN debtortype ON debtortype.typeid=debtorsmaster.typeid
		LEFT JOIN sec_debtorxuser ON sec_debtorxuser.typeid=debtortype.typeid AND sec_debtorxuser.userid='" . $UserID . "'
		LEFT JOIN vehiclesbycostumer ON debtorsmaster.debtorno=vehiclesbycostumer.debtorno
		AND vehiclesbycostumer.branchcode=custbranch.branchcode
		LEFT JOIN vehiclemarks ON vehiclemarks.idmark=vehiclesbycostumer.idmark
		LEFT JOIN vehiclemodels ON vehiclemodels.idmodel=vehiclesbycostumer.idmodel
		LEFT JOIN sat_usocfdi ON sat_usocfdi.c_UsoCFDI = debtorsmaster.usoCFDI
		WHERE debtorsmaster.activo = 1 
		AND (custbranch.debtorno LIKE  '%" . $descrip . "%'
		OR custbranch.branchcode LIKE '%" . $descrip . "%'
		OR debtorsmaster.name LIKE '%" .  $descrip . "%')";
        
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
					$branchaddress = $resp[$xx]['address1']." No. ".$resp[$xx]['numExt']." Col. ".$resp[$xx]['address2'].", ".$resp[$xx]['address3'].", ".$resp[$xx]['address4'];
					$arrdebtor = array(
							"brname" => ($resp[$xx]['brname']),
							"contactname" => ($resp[$xx]['contactname']),
							"phoneno" => ($resp[$xx]['phoneno']),
							"faxno" => ($resp[$xx]['faxno']),
							"branchcode" => ($resp[$xx]['branchcode']),
							"debtorno" => ($resp[$xx]['debtorno']),
							"name" => ($resp[$xx]['name']),
							"taxid" => ($resp[$xx]['taxid']),
							"currcode" => ($resp[$xx]['currcode']),
							"ciudad" => ($resp[$xx]['ciudad']),
							"mpio" => ($resp[$xx]['mpio']),
							"blacklist" => ($resp[$xx]['blacklist']),
							"vehicleno" => ($resp[$xx]['vehicleno']),
							"plate" => ($resp[$xx]['plate']),
							"mark" => ($resp[$xx]['mark']),
							"model" => ($resp[$xx]['model']),
							"nameextra" => ($resp[$xx]['nameextra']),
							"email" => ($resp[$xx]['email']),
							"address" => $branchaddress,
							"usoCFDI" => ($resp[$xx]['usoCFDI']),
							"usoCFDIName" => ($resp[$xx]['usoCFDIName'])
					);
					//$response['data'][] = $arrdebtor;

					array_push($arrdebtors, $arrdebtor);
				}
			}
			
		}
		
		$response['success'] = $success;
		$response['error']['msgerror'] = $msgerror;
		$response['error']['typeerror'] = $typeerror;
		$response['error']['codeerror'] = $codeerror;
		$response['error']['codeerror'] = $sql;
		$response['data']['message'] = $message;
		$response['data'] = $arrdebtors;
		
		//header('Content-type: application/json; charset=utf-8');
  		//return json_encode($response, JSON_FORCE_OBJECT);

  		return $response;
  		

	}


}


?>