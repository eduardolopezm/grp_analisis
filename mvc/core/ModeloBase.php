<?php
/*
class ModeloBase extends EntidadBase{
	private $table;

	public function __constructor($table){
		$this->table = (string) $table;
		parent:: __constructor($table);
	}

	public function ejecutarSql($query){
		$query = $this->db()->query($query);

		if($query){
			if($query->num_rows > 1){
				while($row = $query->fetch_object()){
					$resultSet[] = row;
				}
			}elseif ($query->num_rows == 1){
				if($row = $query->fetch_object()){
					$resultSet = row;
				}
			}else{
				$result = true;
			}
		}else{
			$resultSet = false;mvc
		}
		return $resultSet;
	}

}
*/

class ModeloBase {

	private $link;

	function __construct() {

		$pathprefix = "../.././";

		//require_once($pathprefix . 'config/database.php');
		$host = "";
		if ($_SESSION['DatabaseName'] == "erpjibe" || $_SESSION['DatabaseName'] == "erpjibe_CAPA") {
			$host = "erpjibe.portalito.com";
		}else if ($_SESSION['DatabaseName'] == "erpjibe_DES") {
			$host = "erpdesarrollo.portalito.com";
		}else{
			$host = "erpdesarrollo.portalito.com";
		}

		$host = $_SERVER['HTTP_HOST'];
		
		$db_cfg =  array(
			"driver" 	=> "mysql",
			"host" 		=> $host, // "erpjibe.onaxis.mx"
			"user" 		=> "desarrollo",
			"pass" 		=> "p0rtAli70s",
			"database" 	=> $_SESSION['DatabaseName'],  // "erpjibe"
			"charset" 	=> "utf8",
			"port"		=> "3306"
		);

		$host = $_SERVER['HTTP_HOST'];

		$token = '7gx8S57qDF01iAZ5X97Ih307f0u4oRHf';
		$ch = curl_init();

		// Cambiar el ultimo parametro ya sea por configuracion o por url 
		// 
		// curl_setopt($ch, CURLOPT_URL, "http://erpdesarrollo.portalito.com/erpdistribucion/webservices/dbconn/v1/" . $token . "/dbdata/erpawmexico_DES");
		// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// curl_setopt($ch, CURLOPT_HEADER, 0);
		// $output = curl_exec($ch);
		// curl_close($ch);

		// $data = json_decode($output, true);

		// if(isset($data['error']) and $data['error'] == "false") {
		// 	$hostlocal = $data['dbinfo'][0]['ipDb'];
		// 	$dbuserlocal = $data['dbinfo'][0]['userDb'];
		// 	$dbpasswordlocal = $data['dbinfo'][0]['passDb'];
		// 	$databaselocal = $data['dbinfo'][0]['nombreDb'];
		// 	$mysqlportlocal = $data['dbinfo'][0]['portDb'];
		// } else {
		// 	$hostlocal = $db_cfg["host"];
		// 	$dbuserlocal = $db_cfg["user"];
		// 	$dbpasswordlocal = $db_cfg["pass"];
		// 	$databaselocal = $db_cfg["database"];
		// }

		$hostlocal = $db_cfg["host"];
		$dbuserlocal = $db_cfg["user"];
		$dbpasswordlocal = $db_cfg["pass"];
		$databaselocal = $db_cfg["database"];
		

		$this->link = new mysqli($hostlocal, $dbuserlocal, $dbpasswordlocal, $databaselocal);
		$_SESSION['ConnPV'] = new mysqli($hostlocal, $dbuserlocal, $dbpasswordlocal, $databaselocal);

		if ($this->link->connect_error) {
			echo 'No se pudo conectar a MySQL: ' . $this->link->connect_error;
		}

		mysqli_set_charset($this->link, "utf8");
		mysqli_set_charset($_SESSION['ConnPV'], "utf8");
	}

	public function getLink() {
		return $this->link;
	}

	public function ejecutarSql($query){

		$rs = DB_queryNT($query, $this->link);

		if(!$rs) {
			echo "ERROR";
			$response  = false;
		}else{

			$cadena = explode(' ', trim($query));
        	if (($cadena[0] == 'INSERT') or ($cadena[0] == 'UPDATE') or ($cadena[0] == 'DELETE')) {
        		if ($cadena[0] == 'INSERT'){
        			$pos = strpos($query, "debtortrans");
        			if ($pos === false) {
        				$response = true;
        			}else{
        				$response = DB_Last_Insert_ID($this->link, "debtortrans", "id");		
        			}
        			
        		}else{
        			$response = true;	
        		}

        		$Linea = "";
        		$query = "/*" . $_SESSION['UserID'] . ":(" . $_SERVER['PHP_SELF'] . ") " . $Linea . " */ " . $query;

				$AuditSQL = "INSERT INTO audittrail (transactiondate,
				userid,
				querystring)
				VALUES('" . Date('Y-m-d H:i:s') . "',
				'" . trim($_SESSION['UserID']) . "',
				'" . DB_escape_string($query, $this->link) . "')";
				$rs = DB_queryNT($AuditSQL, $this->link);
        	}else{
        		if (DB_num_rows($rs) > 1){
					while ($myrow = DB_fetch_array($rs)) {
						$response[] = $myrow;
			        }
				}elseif(DB_num_rows($rs) == 1){
					if ($myrow = DB_fetch_array($rs)) {
						$response[] = $myrow;
			        }
				}else{
					$response = true;
				}	
        	}

			
		}

		return 	$response;
		
	}


	public function getdocumentnumber($typedoc){
		$documentnumber = GetNextTransNo($typedoc, $this->link);
		return 	$documentnumber;
	}

	public function getperiodnumber($trandate, $tagref){
		$periodno = GetPeriod($trandate, $this->link, $tagref, $tagref);
		return $periodno;
	}

	public function getDocumentNext($tipofacturacion, $tagref, $area, $legaid){
		$documentnext = DocumentNext($tipofacturacion, $tagref, $area, $legaid, $this->link);
		return $documentnext;
	}

	public function getXSAInvoicing($transno, $orderno, $debtorno, $tipofacturacion, $tagref, $serie, $folio){
		$factelectronica = XSAInvoicing($transno,  $orderno, $debtorno, $tipofacturacion, $tagref, $serie, $folio, $this->link);
                                   
		return $factelectronica;
	}

	public function getgeneraXMLCFDI($factelectronica, $ingreso, $tagref, $serie, $folio, $DebtorTransID, $facturas, $orderno){
		$arrayGeneracion = generaXMLCFDI($factelectronica, $ingreso, $tagref, $serie, $folio, $DebtorTransID, $facturas, $orderno, $this->link);
		return $arrayGeneracion;
	}

	public function getgeneraXMLCFDI3_3($factelectronica, $ingreso, $tagref, $serie, $folio, $DebtorTransID, $facturas, $orderno){
		$arrayGeneracion = generaXMLCFDI3_3($factelectronica, $ingreso, $tagref, $serie, $folio, $DebtorTransID, $facturas, $orderno, $this->link);
		return $arrayGeneracion;
	}

	public function generaXMLCFDI_Impresion($factelectronica, $XMLElectronico, $tagref){
		$XMLElectronico = generaXMLCFDI_Impresion($factelectronica, $XMLElectronico, $tagref, $this->link);
		return $XMLElectronico;
	}

	public function getAgregaAddendaXML($XMLElectronico, $debtorno, $DebtorTransID){
		$XMLElectronico = AgregaAddendaXML($XMLElectronico, $debtorno, $DebtorTransID, $this->link);
		return $XMLElectronico;
	}
	
	public function getAgregaComplementoXML($XMLElectronico, $debtorno, $DebtorTransID){
		$XMLElectronico = AgregaComplementoXML($XMLElectronico, $debtorno, $DebtorTransID, $this->link);
		return $XMLElectronico;
	}

	public function getgeneraXMLIntermedio($factelectronica, $XMLElectronico, $cadenatimbre, $cantidadletra, $orderno, $tagref, $tipofacturacion, $transno){
		$array = generaXMLIntermedio($factelectronica, $XMLElectronico, $cadenatimbre, $cantidadletra, $orderno, $this->link, 1, $tagref, $tipofacturacion, $transno);
		return $array;
	}

	/*public function getPDFQuotation($legalid, $tagref, $transno, $tipocotizacion){

		$_GET['legalid'] = $legalid;
        $_GET['Tagref'] = $tagref;
        $_GET['TransNo'] = $transno;
        $_GET['tipodocto'] = $tipocotizacion;

		$doc = new pdfCotizacionTemplate();

        $pdfcode = $doc->exportPDF(1); 

		return $pdfcode;
	}*/

	function __destruct() {
        DB_close($this->link);
   	}
}
?>