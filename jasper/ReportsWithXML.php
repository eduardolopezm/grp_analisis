<?php

include('JasperReport.php');
	
	/*
	 
	 if($_SESSION['UserID']=='admin'){
                error_reporting(E_ALL);
                ini_set('display_errors', '1');
                ini_set('log_errors', 1);
                ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
        }
     //*/// //  
     

/**
 * 
 * @param string $xml Cadena de archivo XML
 * @param integer $fiscal 
 * @param string $logo Logo que se va a mostrar en la factura
 * @param string $nombre Nombre del tipo de documento que se va a imprmir
 * @param integer $type Tipo de documento 
 * @param integer $cancelado Valor para mostrar la factura cancelada
 * @param integer $copias Numero de copias de la factura
 * @param string $tipo Tipo de documento fiscal
 * @param integer $flag Bandera para el formato del logo
 * @param string $facturacliente Numero que representa el archivo de factura que se va a mostrar
 */

function reportXML($xml,$fiscal,$logo,$nombre,$type,$cancelado=0,$copias=1,$tipo="comprobante",$flag,$facturacliente=0,$mensaje="", $pagares="",$confJasper){
	//echo $facturacliente;
	//echo $mensaje;
	//echo $pagares;
	
	$JasperReport= new JasperReport($confJasper);
	
	if($tipo=="nomina"){
		$jreport=$JasperReport->compilerReport("nomina/Principal");
		$JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile()."/nomina/");
	}else{
		$jreport=$JasperReport->compilerReport("facturacion/Principal");
		$JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile()."/facturacion/");
	}

	$JasperReport->addParameter("FISCAL",$fiscal);
	$JasperReport->addParameter("LOGO",$logo);
	$JasperReport->addParameter("NOMBRE",$nombre);
	$JasperReport->addParameter("TYPE",$type);
	$JasperReport->addParameter("CANCELADO",$cancelado);
	$JasperReport->addParameter("COPIAS",$copias);
	$JasperReport->addParameter("flaglogo",$flag);
	$JasperReport->addParameter("facturacliente",$facturacliente);
	$JasperReport->addParameter("mensajelegal",$mensaje);
	$JasperReport->addParameter("pagares",$pagares);
	
	
	$datasource=$JasperReport->getDataSource(utf8_encode($xml));
	$jPrint=$JasperReport->fillReport($jreport,$JasperReport->getParameters(),$datasource);

	return $JasperReport->exportReportPDF($jPrint);
} 


function reportXML3($xml, $fiscal, $logo, $nombre, $type, $cancelado = 0, $copias = 1, $tipo = "comprobante", $flag = '0', $facturacliente = 0, $mensaje = "", $pagares = "", $foliointerno = "0", $logoalterno = null, $mostrardescuento = 1, $orderno = '', $emision = 0, $branchcode="",$DesgloseIVA=0,$MostrarCodigoCliente=1,$confJasper) {
		// print_r($confJasper);
		// exit();
		$JasperReport = new JasperReport ($confJasper);

		if($_SESSION['DatabaseName'] == "gruposervillantas_DES" or $_SESSION['DatabaseName'] == "gruposervillantas_CAPA" or $_SESSION['DatabaseName'] == "gruposervillantas") {
			$jreport = $JasperReport->compilerReport ( "facturacion/FacturacionServillantas_33" );
		}else if ($_SESSION['DatabaseName'] == "erpawmexico_DES" or $_SESSION['DatabaseName'] == "erpawmexico_CAPA" or $_SESSION['DatabaseName'] == "erpawmexico") {
			$jreport = $JasperReport->compilerReport ( "facturacion/FacturacionAW_33" );
		}
		else{
			$jreport = $JasperReport->compilerReport ( "facturacion/Facturacion33" );
		}
		//$jreport = $JasperReport->compilerReport ( "facturacion/prueba33" );

		$JasperReport->addParameter ( "SUBREPORT_DIR", $JasperReport->getPathFile () . "facturacion/" );
		//$jreport = $JasperReport->compilerReport ( "facturacion/Conceptos1d_3.3" );

	//}

	//Validacion para mostrar direccion del cliente
	$arrayDataBase = array('erppisumma','erppisumma_CAPA','erppisumma_DES','erpjibe_DES','erpjibe','erpjibe_CAPA','ap_grp_demo','ap_grp','ap_grp_de','ap_grp_demo_de');
	$MostrarDirCliente=0;
	if(in_array($_SESSION['DatabaseName'], $arrayDataBase)){
		$MostrarDirCliente=1;
	}

	//Validacion para mostrar direccion de la razon social
	//
	$arrayDataBaseDirRZ = array('erptycqsa_CAPA' );
	$MostrarDirRZ="0";
	if(in_array($_SESSION['DatabaseName'], $arrayDataBaseDirRZ)){
		$MostrarDirRZ="1";
	}

	$type = (int) $type;
	$JasperReport->addParameter ( "FISCAL", $fiscal );
	$JasperReport->addParameter ( "LOGO", $logo );
	$JasperReport->addParameter ( "NOMBRE", $nombre);
	$JasperReport->addParameter ( "TYPE", $type );
	$JasperReport->addParameter ( "CANCELADO", $cancelado );
	$JasperReport->addParameter ( "COPIAS", $copias );
	$JasperReport->addParameter ( "flaglogo", $flag );
	$JasperReport->addParameter ( "facturacliente", $facturacliente );
	$JasperReport->addParameter ( "mensajelegal", $mensaje );
	$JasperReport->addParameter ( "pagares", $pagares );
	$JasperReport->addParameter ( "foliointerno", $foliointerno );
	$JasperReport->addParameter ( "logoalterno", $logoalterno );
	$JasperReport->addParameter ( "mostrardescuento", $mostrardescuento );
	$JasperReport->addParameter ( "orderno", $orderno );
	$JasperReport->addParameter ( "emision", $emision );
	$JasperReport->addParameter ( "branchcode", $branchcode );
    $JasperReport->addParameter ("DesgloseIVA",$DesgloseIVA);
    $JasperReport->addParameter ("MostrarCodigoCliente",$MostrarCodigoCliente);
	$JasperReport->addParameter ("MostrarDirCliente",$MostrarDirCliente);
	$JasperReport->addParameter ("mostrarDirRazonSocial",$MostrarDirRZ);
	
	$datasource = $JasperReport->getDataSource (utf8_encode($xml));
	
	$jPrint = $JasperReport->fillReport ($jreport, $JasperReport->getParameters (), $datasource );

	return $JasperReport->exportReportPDF ( $jPrint );
}
?>
