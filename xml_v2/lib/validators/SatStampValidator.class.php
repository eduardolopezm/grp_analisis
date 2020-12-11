<?php 
include_once realpath(dirname(__FILE__)) . '/SatValidator.interface.php';
include_once realpath(dirname(__FILE__)) . '/../SatXmlError.class.php';

/**
 * Class responsible of validating the XML stamps
 * @version 1.0
 */
class SatStampValidator extends SatValidator {
	
	const ERROR_TYPE = 'PCID';
	
	/**
	 * Newline constant
	 * @var String
	 */
	const NEW_LINE = "\r\n";
	/**
	 * Key Footer
	 * @var String
	 */
	const KEY_FOOTER = "-----END PUBLIC KEY-----";
	/**
	 * Certificate header
	 * @var String
	 */
	const CERT_HEADER = "-----BEGIN CERTIFICATE-----";
	/**
	 * Certificate footer
	 * @var String
	 */
	const CERT_FOOTER = "-----END CERTIFICATE-----";
	/**
	 * Size of the line
	 * @var String
	 */
	const LINE_SIZE = 64;
	/**
	 * Success message
	 * @var String
	 */
	const SUCCESS = "Verified OK";
	
	private $certificate;
	private $stamp;
	private $tempSavePath;
	private $satStr;
	
	public function __construct($tempSavePath) {
		parent::__construct();
		$this->tempSavePath = $tempSavePath;
	}
	
	public function validate() {

		if ($this->comprobante == null) {
			$error = new SatXmlError();
			$error->setCode('0');
			$error->setMessage("La validacion del sello digital no pudo ser completada");
			$error->setLevel(SatXmlError::FATAL_ERROR);
			$error->setClass(get_class($this));
			$error->setType(self::ERROR_TYPE);
			$this->addError($error);
			return false;
		}
		
		$this->cleanErrors();
		
		if ($this->validateStamp() == false) {	
			$error = new SatXmlError();
			$error->setCode('1');
			$error->setMessage("El sello digital no es valido");
			$error->setLevel(SatXmlError::ERROR);
			$error->setClass(get_class($this));
			$error->setType(self::ERROR_TYPE);
			$error->setNode('Comprobante');
			$error->setAttribute('sello');
			$error->setValue($this->stamp." <br/>".$this->satStr);
			$error->setVersion($this->comprobante->getVersion());
			$this->addError($error);
		}
		
		$timbreFiscal = $this->comprobante->getTimbreFiscal();
		if ($timbreFiscal->getUuid() != null) {
			if ($this->validateSatStamp() == false) {
				$timbreFiscal = $this->comprobante->getTimbreFiscal();
				$error = new SatXmlError();
				$error->setCode('2');
				$error->setMessage("El sello digital del SAT no es valido");
				$error->setLevel(SatXmlError::ERROR);
				$error->setClass(get_class($this));
				$error->setType(self::ERROR_TYPE);
				$error->setNode('TimbreFiscalDigital');
				$error->setAttribute('selloSAT');
				$error->setValue($timbreFiscal->getSelloSAT());
				$error->setVersion($this->comprobante->getVersion());
				$this->addError($error);
			}
		}
		
		if ($this->validateCertificateStamp() == false) {
			$noCert = $this->comprobante->getNoCertificado();
			$error = new SatXmlError();
			$error->setCode('3');
			$error->setMessage("El numero de certificado '$noCert' no corresponde al sello");
			$error->setLevel(SatXmlError::ERROR);
			$error->setClass(get_class($this));
			$error->setType(self::ERROR_TYPE);
			$error->setNode('Comprobante');
			$error->setAttribute('sello');
			$error->setValue($noCert);
			$error->setVersion($this->comprobante->getVersion());
			$this->addError($error);
		}
		
		if ($timbreFiscal->getUuid() != null) {
			if ($this->validateSatCertificateStamp() == false) {
				$timbreFiscal = $this->comprobante->getTimbreFiscal();
				$noCertSat = $timbreFiscal->getNoCertificadoSAT();
				$error = new SatXmlError();
				$error->setCode('4');
				$error->setMessage("El numero de certificado del SAT '$noCertSat' no corresponde al sello del SAT");
				$error->setLevel(SatXmlError::ERROR);
				$error->setClass(get_class($this));
				$error->setType(self::ERROR_TYPE);
				$error->setNode('TimbreFiscalDigital');
				$error->setAttribute('noCertificadoSAT');
				$error->setValue($noCertSat);
				$error->setVersion($this->comprobante->getVersion());
				$this->addError($error);
			}
		}
		
		return ($this->hasErrors() == false);
	}
	
	private function validateStamp() {
		
		$fileId = uniqid();
		
		$this->stamp = $this->comprobante->getSello();
		$this->certificate = $this->comprobante->getCertificado();
		
		$satString = $this->comprobante->getCadenaOriginal();
		$this->satStr = $satString;
			
		$handle = fopen($this->tempSavePath . $fileId . "cad.txt", 'w');
		fwrite($handle, $satString);
		fclose($handle);
		
		$certificate = self::CERT_HEADER . self::NEW_LINE;
		$certificate .= chunk_split($this->certificate, self::LINE_SIZE, self::NEW_LINE);
		$certificate .= self::CERT_FOOTER;
			
		$handle = fopen($this->tempSavePath . $fileId . 'certificate.PEM', 'w');
		fwrite($handle, trim($certificate));
		fclose($handle);
			
		$stamp = chunk_split($this->stamp, self::LINE_SIZE, self::NEW_LINE);
			
		$handle = fopen($this->tempSavePath . $fileId . 'stamp.txt', 'w');
		fwrite($handle, trim($stamp));
		fclose($handle);
		
		$command = 'openssl x509 -in "{path}certificate.PEM" -pubkey -noout > {path}pubkey.txt';
		exec(str_replace('{path}', $this->tempSavePath . $fileId, $command));
		
		$command = 'openssl enc -base64 -d -in "{path}stamp.txt" > {path}stampbin.txt';
		exec(str_replace('{path}', $this->tempSavePath . $fileId, $command));
		
		$command = 'openssl dgst -sha1 -verify "{path}pubkey.txt" -signature "{path}stampbin.txt" "{path}cad.txt" > {path}result.txt';
		exec(str_replace('{path}', $this->tempSavePath . $fileId, $command));
		
		$resultFile = $this->tempSavePath . $fileId . 'result.txt';
		$handle = fopen($resultFile, 'r');
		$result = @fread($handle, filesize($resultFile));
		$result = trim($result);
		fclose($handle);
		
		if ($result == self::SUCCESS) {
			$this->cleanTempFiles($fileId);
			return true;
		}
		
		// Validar sello con md5 si validacion SHA1 falla, este quiere decir que el documento es del 2011 o antes.
		$command = 'openssl dgst -md5 -verify "{path}pubkey.txt" -signature "{path}stampbin.txt" "{path}cad.txt" > {path}result.txt';
		exec(str_replace('{path}', $this->tempSavePath . $fileId, $command));
		
		$resultFile = $this->tempSavePath . $fileId . 'result.txt';
		$handle = fopen($resultFile, 'r');
		$result = @fread($handle, filesize($resultFile));
		$result = trim($result);
		fclose($handle);
		
		$this->cleanTempFiles($fileId);
		
		if ($result == self::SUCCESS) {
			return true;
		}
		return false;
	}
	
	private function validateCertificateStamp() {
		
		$fileId = uniqid();
		$cert = $this->comprobante->getCertificado();
		$noCert = $this->comprobante->getNoCertificado();
		$certificate = self::CERT_HEADER . self::NEW_LINE;
		$certificate .= chunk_split($cert, self::LINE_SIZE, self::NEW_LINE);
		$certificate .= self::CERT_FOOTER;
			
		$handle = fopen($this->tempSavePath . $fileId . $noCert . '.PEM', 'w');
		fwrite($handle, trim($certificate));
		fclose($handle);
		
		$command = 'openssl x509 -inform PEM -outform DER -in "{path}' . $noCert . '.PEM" >{path}' . $noCert . '.cer';
		exec(str_replace('{path}', $this->tempSavePath . $fileId, $command));
		
		$command = 'openssl x509 -inform DER -in "{path}' . $noCert . '.cer" -serial > {path}certificate.txt';
		exec(str_replace('{path}', $this->tempSavePath . $fileId, $command));
		
		$f = fopen($this->tempSavePath . $fileId . 'certificate.txt', 'r');
		$line = fgets($f);
		fclose($f);
		
		@unlink($this->tempSavePath . $fileId . 'certificate.txt');
		@unlink($this->tempSavePath . $fileId . $noCert . '.PEM');
		@unlink($this->tempSavePath . $fileId . $noCert . '.cer');
		
		$certificado = explode('=', $line);
		
		if (count($certificado) > 1) {
			$certificado = $certificado[1];
			$certificado = $this->cleanCertificate($certificado);
			if ($certificado == $noCert) {
				return true;
			}
		}
		return false;
	}
	
	private function validateSatStamp() {
		
		$timbreFiscal = $this->comprobante->getTimbreFiscal();
			
		$fileId = uniqid();
		$version = $timbreFiscal->getVersion();
		$selloSAT = $timbreFiscal->getSelloSAT();
		$selloCFD = $timbreFiscal->getSelloCFD();
		$fechaTimbrado = str_replace(' ', 'T', $timbreFiscal->getFechaTimbrado());
		$certificado = $timbreFiscal->getNoCertificadoSAT(); 
		$uuid = $timbreFiscal->getUuid();
		
		$cad = "||$version|$uuid|$fechaTimbrado|$selloCFD|$certificado||";
		
		$certificatesPath = realpath(dirname(__FILE__)) . "/../sat/certificados/";
		if (file_exists($certificatesPath . $certificado . '.cer') == false) {
			ComprobanteFactory::buildCertificate($certificado);
		}
		
		$command = 'openssl x509 -inform DER -outform PEM -in "' . $certificatesPath . $certificado . '.cer" -pubkey >{path}' . $certificado . '.PEM';
		exec(str_replace('{path}', $this->tempSavePath . $fileId, $command));
		
		$handle = fopen($this->tempSavePath . $fileId . "cad.txt", 'w');
		fwrite($handle, $cad);
		fclose($handle);
		
		$command = 'openssl x509 -in "{path}' . $certificado . '.PEM" -pubkey -noout > {path}pubkey.txt';
		exec(str_replace('{path}', $this->tempSavePath . $fileId, $command));
		
		$selloSAT = chunk_split($selloSAT, self::LINE_SIZE, self::NEW_LINE);
		
		$handle = fopen($this->tempSavePath . $fileId . 'stamp.txt', 'w');
		fwrite($handle, trim($selloSAT));
		fclose($handle);
		
		$command = 'openssl enc -base64 -d -in "{path}stamp.txt" > {path}stampbin.txt';
		exec(str_replace('{path}', $this->tempSavePath . $fileId, $command));
		
		$command = 'openssl dgst -sha1 -verify "{path}pubkey.txt" -signature "{path}stampbin.txt" "{path}cad.txt" > {path}result.txt';
		exec(str_replace('{path}', $this->tempSavePath . $fileId, $command));
		
		$resultFile = $this->tempSavePath . $fileId . 'result.txt';
		$handle = fopen($resultFile, 'r');
		$result = @fread($handle, filesize($resultFile));
		$result = trim($result);
		
		fclose($handle);
		
		$this->cleanTempFilesSAT($fileId);
		
		if ($result == self::SUCCESS) {
			return true;
		}
		return false;	
	}
	
	private function validateSatCertificateStamp() {
		
		$fileId = uniqid();
		$timbreFiscal = $this->comprobante->getTimbreFiscal();
		$certificadoSat = $timbreFiscal->getNoCertificadoSAT();
		$certificatesPath = realpath(dirname(__FILE__)) . "/../sat/certificados/";
		
		$command = 'openssl x509 -inform DER -in "' . $certificatesPath . $certificadoSat . '.cer" -serial > {path}certificate.txt';
		exec(str_replace('{path}', $this->tempSavePath . $fileId, $command));
		
		$f = fopen($this->tempSavePath . $fileId . 'certificate.txt', 'r');
		$line = fgets($f);
		fclose($f);
		
		@unlink($this->tempSavePath . $fileId . 'certificate.txt');
		$certificado = explode('=', $line);
		if (count($certificado) > 1) {
			$certificado = $certificado[1];
			$certificado = $this->cleanCertificate($certificado);
			if ($certificado == $certificadoSat) {
				return true;
			}
		}
		return false;
	}
	
	private function cleanTempFiles($fileId) {
		@unlink($this->tempSavePath . $fileId . 'cad.txt');
		@unlink($this->tempSavePath . $fileId . 'certificate.PEM');
		@unlink($this->tempSavePath . $fileId . 'pubkey.txt');
		@unlink($this->tempSavePath . $fileId . 'result.txt');
		@unlink($this->tempSavePath . $fileId . 'stamp.txt');
		@unlink($this->tempSavePath . $fileId . 'stampbin.txt');
	}
	
	private function cleanTempFilesSAT($fileId) {
		$timbreFiscal = $this->comprobante->getTimbreFiscal(); 
		@unlink($this->tempSavePath . $fileId . 'cad.txt');
		@unlink($this->tempSavePath . $fileId . $timbreFiscal->getNoCertificadoSAT() . '.PEM');
		@unlink($this->tempSavePath . $fileId . 'pubkey.txt');
		@unlink($this->tempSavePath . $fileId . 'result.txt');
		@unlink($this->tempSavePath . $fileId . 'stamp.txt');
		@unlink($this->tempSavePath . $fileId . 'stampbin.txt');
	}
	
	private function cleanCertificate($certificado) {
		$certificado = trim($certificado);
		$parts = str_split($certificado, 2);
		if (empty($parts) == false) {
			$nums = array();
			foreach ($parts as $part) {
				$num = substr($part, 1, 1);
				$nums[] = $num;
			}
			return implode('', $nums);
		}
		return $certificado;
	}
	
	public function getSatStr() {
		return $this->satStr;
	}
}
?>