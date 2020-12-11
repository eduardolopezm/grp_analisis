<?php
/**
 * Model class responsible of querying general data
* @version 1.0
*/
class General {

	private $db;

	public function __construct($db) {
		$this->db = $db;
	}

	// Valida Nombre Receptor
	public function hasName($legalname) {
		$legalname = strtolower($legalname);
		$rs = mysqli_query($this->db, "SELECT 1 FROM legalbusinessunit WHERE lower(legalname) like '$legalname%'");
		if (mysqli_fetch_array($rs)) {
			return true;
		}
		return false;
	}
	
	// valida RFC
	public function hasRfc($rfc) {
		$rfc = strtolower($rfc);
		$rs = mysqli_query($this->db, "SELECT 1 FROM legalbusinessunit WHERE lower(taxid) like '$rfc%'");
		if (mysqli_fetch_array($rs)) {
			return true;
		}
		return false;
	}
	
	// Valida Calle Receptor
	public function hasStreet($street) {
		$street = strtolower($street);	
		$rs = mysqli_query($this->db, "SELECT 1 FROM legalbusinessunit WHERE lower(address1) like '$street%'");
		if (mysqli_fetch_array($rs)) {
			return true;
		}
		return false;
	}
	
	public function hasColony($colony) {
		$colony = strtolower($colony);
		$rs = mysqli_query($this->db, "SELECT 1 FROM legalbusinessunit WHERE lower(address2) like '$colony%'");
		if (mysqli_fetch_array($rs)) {
			return true;
		}
		return false;
	}

	public function hasMunicipio($municipio) {
		$municipio = strtolower($municipio);
		$rs = mysqli_query($this->db, "SELECT 1 FROM legalbusinessunit WHERE lower(address3) like '$municipio%'");
		if (mysqli_fetch_array($rs)) {
			return true;
		}
		return false;
	}
//
	public function hasCp($cp) {
		//echo "SELECT 1 FROM {$this->table} WHERE d_codigo = '$cp' OR d_CP = '$cp'";
		$rs = mysqli_query($this->db, "SELECT 1 FROM legalbusinessunit WHERE lower(address5) = '$cp'");
		if (mysqli_fetch_array($rs)) {
			return true;
		}
		return false;
	}

	public function hasState($state) {
		$state = strtolower($state);
		$rs = mysqli_query($this->db, "SELECT 1 FROM legalbusinessunit WHERE lower(address4) like '$state%'");
		if (mysqli_fetch_array($rs)) {
			return true;
		}
		return false;
	}
	
	// validar certificado
	public function hasCertificado($certificado, $rfc) {
		$certificado = strtolower($certificado);
		$rs = mysqli_query($this->db, "SELECT 1 FROM certificaciones WHERE lower(num_serie) = '$certificado' and rfc= '$rfc'");
		//echo "SELECT 1 FROM certificaciones WHERE lower(num_serie) = '$certificado' and rfc= '$rfc'";
		if (mysqli_fetch_array($rs)){
			return true;
		}
		return false;
	}
	
	// validar certificado
	public function getCertificado($cert, $rfc, $date) {
		//echo "SELECT num_serie FROM certificaciones WHERE '$date' BETWEEN fecha_inicial AND fecha_final AND rfc = '$rfc' AND num_serie = '$cert'";		
		$rs = mysqli_query($this->db, "SELECT num_serie FROM certificaciones WHERE '$date' BETWEEN fecha_inicial AND fecha_final AND rfc = '$rfc' AND num_serie = '$cert'");
		if ($row = mysqli_fetch_array($rs)){
			return $row['num_serie'];
		}
		return null;
	}
	
	// validar certificado
	public function getCertificadoByRfc($cert, $rfc) {
		$rs = mysqli_query($this->db, "SELECT num_serie FROM certificaciones WHERE rfc = '$rfc' AND num_serie = '$cert'");
		if ($row = mysqli_fetch_array($rs)){
			return $row['num_serie'];
		}
		return null;
	}
	
	// validar certificado sat
	public function hasCertificadoSat($certificadoSat, $fechaCertificadoSat) {
		$rs = mysqli_query($this->db, "SELECT 1 FROM certificaciones 
			WHERE num_serie = '$certificadoSat'  
			AND '$fechaCertificadoSat' BETWEEN fecha_inicial AND fecha_final
			AND rfc = 'SAT970701NN3'");
		if (mysqli_fetch_array($rs)){
			return true;
		}
		return false;
	}
	
	public function get5MillarVariants() {
		$rs = mysqli_query($this->db, "SELECT LOWER(texto) FROM impuestos_variantes WHERE id_impuesto = '1'");
		$data = array();
		while ($row = mysqli_fetch_row($rs)) {
			$data[] = $row[0];
		}
		return $data;
	}
	
	public function get5MillarPercentage() {
		$rs = mysqli_query($this->db, "SELECT porcentaje FROM impuestos WHERE id = '1'");
		if ($row = mysqli_fetch_row($rs)) {
			return $row[0];
		}
		return 0;
	}
	
	public function get2MillarVariants() {
		$rs = mysqli_query($this->db, "SELECT LOWER(texto) FROM impuestos_variantes WHERE id_impuesto = '2'");
		$data = array();
		while ($row = mysqli_fetch_row($rs)) {
			$data[] = $row[0];
		}
		return $data;
	}
	
	public function get2MillarPercentage() {
		$rs = mysqli_query($this->db, "SELECT porcentaje FROM impuestos WHERE id = '2'");
		if ($row = mysqli_fetch_row($rs)) {
			return $row[0];
		}
		return 0;
	}
	
	public function get1MillarVariants() {
		$rs = mysqli_query($this->db, "SELECT LOWER(texto) FROM impuestos_variantes WHERE id_impuesto = '3'");
		$data = array();
		while ($row = mysqli_fetch_row($rs)) {
			$data[] = $row[0];
		}
		return $data;
	}
	
	public function get1MillarPercentage() {
		$rs = mysqli_query($this->db, "SELECT porcentaje FROM impuestos WHERE id = '3'");
		if ($row = mysqli_fetch_row($rs)) {
			return $row[0];
		}
		return 0;
	}
	
	public function getICICVariants() {
		$rs = mysqli_query($this->db, "SELECT LOWER(texto) FROM impuestos_variantes WHERE id_impuesto = '4'");
		$data = array();
		while ($row = mysqli_fetch_row($rs)) {
			$data[] = $row[0];
		}
		return $data;
	}
	
	public function getICICPercentage() {
		$rs = mysqli_query($this->db, "SELECT porcentaje FROM impuestos WHERE id = '4'");
		if ($row = mysqli_fetch_row($rs)) {
			return $row[0];
		}
		return 0;
	}
	
	public function getVariants() {
		$rs = mysqli_query($this->db, "SELECT texto FROM impuestos_variantes");
		return mysqli_fetch_row($rs);
	}
	
	public function getOBSVariants() {
		$rs = mysqli_query($this->db, "SELECT LOWER(texto) FROM impuestos_variantes WHERE id_impuesto = '5'");
		$data = array();
		while ($row = mysqli_fetch_row($rs)) {
			$data[] = $row[0];
		}
		return $data;
	}
	
	public function getOBSPercentage() {
		$rs = mysqli_query($this->db, "SELECT porcentaje FROM impuestos WHERE id = '5'");
		if ($row = mysqli_fetch_row($rs)) {
			return $row[0];
		}
		return 0;
	}
	
	public function getUNETEVariants() {
		$rs = mysqli_query($this->db, "SELECT LOWER(texto) FROM impuestos_variantes WHERE id_impuesto = '6'");
		$data = array();
		while ($row = mysqli_fetch_row($rs)) {
			$data[] = $row[0];
		}
		return $data;
	}
	
	public function getUNETEPercentage() {
		$rs = mysqli_query($this->db, "SELECT porcentaje FROM impuestos WHERE id = '6'");
		if ($row = mysqli_fetch_row($rs)) {
			return $row[0];
		}
		return 0;
	}
	
	public function saveUnknownCertificate($noCert, $rfc) {
		$sql = "INSERT INTO certificaciones_unknown (
				num_serie, rfc, fecha_inicial, fecha_final, estatus
			) VALUES (
				'$noCert', '$rfc', NOW(), NOW(), 'A'
			)";
		
		mysqli_query($this->db, $sql);
	}
	
	public function getvalidaExistSerieFolio($rfc, $Noaprobacion, $anio, $serie, $folio) {
		$sql = "SELECT *
			FROM FoliosCFD
			WHERE FoliosCFD.RFC = '".$rfc."'
			AND FoliosCFD.NoAprobacion = '".$Noaprobacion."'
			AND AnoAprobacion = '".$anio."'
			AND Serie = '".$serie."'
			AND '".$folio."' BETWEEN FolioInicial AND FolioFinal";
		$rs = mysqli_query($this->db, $sql);
		if ($row = mysqli_fetch_row($rs)) {
			return true;
		}
		return false;
	}
}
?>