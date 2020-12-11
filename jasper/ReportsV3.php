<?php

include('JasperReport.php');

// $dataBase=$_SESSION['BaseDataware'];
// error_reporting(E_ALL);
// ini_set('display_errors', '1');

//La variable params es un array con arrays de 2 posiciones
//Ejemplo array(array('PROPERTY','VALUE'));

function report($SQL1,$SQL2,$params,$LOGO,$NAME_TEMPLATE,$FORMAT,$dataBase){
	global $dbuser;
	global $dbpassword;

// 	echo "generandoreporte...";
// 	$dataBase=$_SESSION['DatabaseName'];

	$JasperReport=new JasperReport;

	$jreport=$JasperReport->compilerReport("templates/".$NAME_TEMPLATE);

	$JasperReport->addParameter("QUERY_TABLE",$SQL1);
	$JasperReport->addParameter("QUERY_INFO",$SQL2);
	$JasperReport->addParameter("LOGO",$LOGO);

	foreach($params as $parameter){
// 		echo "Map:".$parameter[0]."".$parameter[1];
		$JasperReport->addParameter($parameter[0],$parameter[1]);
	}

	if($FORMAT=='xls'){
		$JasperReport->addParameter("IS_IGNORE_PAGINATION",java("java.lang.Boolean")->TRUE);
	}


	$conexion= $JasperReport->getConexionDB($dataBase,$dbuser,$dbpassword);


	$jPrint=$JasperReport->fillReport($jreport,$JasperReport->getParameters(),$conexion);

	switch ($FORMAT) {
		case "pdf":
			$objStream=$JasperReport->exportReportPDF($jPrint);
			break;

		case "xls":
			$objStream=$JasperReport->exportReportXLS($jPrint);
			break;

		case "html":
			$name=split('/',$NAME_TEMPLATE);
			$objStream=$JasperReport->exportReportHTML($jPrint,$name[1]);
			break;
	}

	return $objStream;



}

?>
