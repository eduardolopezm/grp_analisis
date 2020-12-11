<?php

/**
 * Visualizar Reportes
 *
 * @category Reporte Estado de Variacion de Haciedna / Patrimonio
 * @package ap_grp
 * @author Desarrollo
 * @Fecha Creación: 30/10/2017
 * @Fecha Modificación: 04/06/2018
 * @Visualizar reportes conac y ldf
 */

$jreport= "";

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechainicial"]);
$fechaInicialNew = $myDateTime->format('Y-m-d');
$myDateTime = DateTime::createFromFormat('d-m-Y', $_GET["fechafinal"]);
$fechaFinalNew = $myDateTime->format('Y-m-d');

$anioReporte = date('Y');
if(isset($_GET["fechainicial"])){
   $arrAnio = explode('-', $_GET["fechainicial"]);
   $anioReporte =$arrAnio['2'];
}

$sql="SELECT  tb_c.id_nu_reportes_conac,
		tb_c.ln_reporte,
		config_reportes_.valor,
		CONCAT(`chartmaster`.`accountcode`, ' - ' , `chartmaster1`.`accountname`) AS 'accountnameprincipal',
		chartmaster.accountcode,
		CONCAT(`chartmaster`.`accountcode`, ' - ' , `chartmaster`.`accountname`) AS 'accountname',
		CASE WHEN  chartmaster.accountcode = '3.2.1' THEN coalesce(tb_ahorro_desahorro.Periodo1,0) ELSE coalesce(gltrans.Periodo1,0) END  as ejercicio_actual,
		CASE WHEN  chartmaster.accountcode = '3.2.1' THEN coalesce(tb_ahorro_desahorro.Periodo2,0) ELSE coalesce(gltrans.Periodo2,0) END  as ejercicio_anterior1,
		coalesce(gltrans.Periodo3,0) as ejercicio_anterior2
	FROM tb_cat_reportes_conac tb_c
	LEFT JOIN config_reportes_ ON tb_c.ln_reporte= config_reportes_.reporte
	LEFT JOIN chartmaster chartmaster1  on  config_reportes_.valor = chartmaster1.accountcode 
	LEFT JOIN chartmaster on config_reportes_.valor = chartmaster.groupcode
	LEFT JOIN (
				SELECT SUBSTRING_INDEX(account, '.', '3') as account,
					IFNULL(SUM(case when trandate between '".$fechaInicialNew."' and  '".$fechaFinalNew."' then gltrans.amount else 0 end), 0)  as Periodo1,
					IFNULL(SUM(case when trandate between date_add('".$fechaInicialNew."', INTERVAL -1 year) and date_add('".$fechaFinalNew."', INTERVAL -1 year) then gltrans.amount else 0 end), 0)  as Periodo2,
					IFNULL(SUM(case when trandate between date_add('".$fechaInicialNew."', INTERVAL -2 year) and date_add('".$fechaFinalNew."', INTERVAL -2 year) then gltrans.amount else 0 end), 0)  as Periodo3
			FROM  gltrans
			INNER JOIN sec_unegsxuser ON gltrans.tag = sec_unegsxuser.tagref AND sec_unegsxuser.userid ='".$_SESSION['UserID']."'
			INNER JOIN (SELECT chartmaster.accountcode  AS valor
      					FROM tb_cat_reportes_conac tb_c
      					left join config_reportes_ ON tb_c.ln_reporte= config_reportes_.reporte
      					LEFT JOIN chartmaster on config_reportes_.valor = chartmaster.groupcode
      					WHERE id_nu_reportes_conac = '7' 
      					GROUP BY chartmaster.accountcode
      					) configReport ON SUBSTRING_INDEX(account, '.', '3') = configReport.valor
			WHERE gltrans.account != ''
					AND CASE WHEN '".$_GET["tagref"]."' = '' THEN 1 = 1 ELSE gltrans.tag IN ('".$_GET["tagref"]."') END
	  				AND CASE WHEN '".$_GET["ue"]."' = '' THEN 1 = 1 ELSE gltrans.ln_ue IN ('".$_GET["ue"]."') END
	  				
      				AND gltrans.posted = 1
      				AND gltrans.periodno not LIKE '%.5'
      		GROUP BY SUBSTRING_INDEX(account, '.', '3')
      		) gltrans on gltrans.account = chartmaster.accountcode
	LEFT JOIN (SELECT '3.2.1' as account,
					IFNULL(SUM(case when trandate between '".$fechaInicialNew."' and  '".$fechaFinalNew."' then gltrans.amount else 0 end), 0)  as Periodo1,
					IFNULL(SUM(case when trandate between date_add('".$fechaInicialNew."', INTERVAL -1 year) and date_add('".$fechaFinalNew."', INTERVAL -1 year) then gltrans.amount else 0 end), 0)  as Periodo2
			FROM  gltrans
			INNER JOIN sec_unegsxuser ON gltrans.tag = sec_unegsxuser.tagref AND sec_unegsxuser.userid = '".$_SESSION['UserID']."'
			INNER JOIN (SELECT SUBSTRING_INDEX(config_reportes_.valor, '.', 3)  AS valor
      					FROM config_reportes_
      					WHERE reporte ='EstadoDeActividades'
      					GROUP BY SUBSTRING_INDEX(config_reportes_.valor, '.', 3)
      					) configReport ON SUBSTRING_INDEX(account, '.', '3') = configReport.valor
			WHERE  gltrans.account != ''
					AND CASE WHEN '".$_GET["tagref"]."' = '' THEN 1 = 1 ELSE gltrans.tag IN ('".$_GET["tagref"]."') END
	  				AND CASE WHEN '".$_GET["ue"]."' = '' THEN 1 = 1 ELSE gltrans.ln_ue IN ('".$_GET["ue"]."') END
					AND gltrans.periodno not LIKE '%.5'
      				AND gltrans.posted = 1
      	  ) tb_ahorro_desahorro on chartmaster.accountcode = CAST(tb_ahorro_desahorro.account AS CHAR CHARACTER SET utf8)
	WHERE   tb_c.id_nu_reportes_conac = '7' 
			AND tb_c.ind_activo=1
	GROUP BY  
			tb_c.id_nu_reportes_conac,
			tb_c.ln_reporte,
			config_reportes_.valor,
			chartmaster1.accountname,
			chartmaster.accountcode,
			chartmaster.accountname;";

$result = DB_query($sql, $db);

// Crea instancia para generar el XML
$xml = new DOMDocument("1.0", "UTF-8");
$nodo_principal= $xml->createElement("rptEstadoVariacionHacienda");

//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//!!                                                   !!
//!! Generamos los movimientos del periodo Anterior.   !!
//!!                                                   !!
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

//Generamos el periodo anterior
$encabezado = "";
$index=0;

$sumaTotalColumna1=0;
$sumaTotalColumna2=0;
$sumaTotalColumna3=0;
$sumaTotalColumna4=0;
$sumaTotalColumna5=0;
$sumaTotalColumna6=0;

$cuenta= "";
$arrayTotalesAnterior = [];
$arrayTotalesAnteriorCuenta = [];
$contador = 0;
$indexFijo=0;

while ($myRowTotal = DB_fetch_array($result)) {
	$sumFila='0.00';

	if($myRowTotal['valor'] != $cuenta){
    	$cuenta = $myRowTotal['valor'];
    	$index=$index + 1;
    	$sumaTotalColumna1=0;
    	$indexDetalle=0;
	}

	$sumaTotalColumna1=$sumaTotalColumna1 + $myRowTotal['ejercicio_anterior1'];

	$arrayTotalesAnterior[$myRowTotal['valor']]  = $sumaTotalColumna1;

	if($myRowTotal['accountcode'] != $cuentaDetalle){
    	$cuentaDetalle = $myRowTotal['accountcode'];
    	
    	$arrayTotalesAnteriorCuenta[$myRowTotal['accountcode']][0] = "0";
    	$arrayTotalesAnteriorCuenta[$myRowTotal['accountcode']][1] = "0";
    	$arrayTotalesAnteriorCuenta[$myRowTotal['accountcode']][2] = "0";
		$arrayTotalesAnteriorCuenta[$myRowTotal['accountcode']][3] = "0";

    	if($index == "2"){
    		$indexFijo=0;

    		if($myRowTotal['accountcode'] == "3.2.1"){
    			$indexFijo = 2;
    		}elseif ($myRowTotal['accountcode'] == "3.2.2") {
    			$indexFijo = 1;
    		}elseif ($myRowTotal['accountcode'] == "3.2.3") {
    			$indexFijo = 3;
    		}else{
    			$indexFijo = 0;
    		}

    		$arrayTotalesAnteriorCuenta[$myRowTotal['accountcode']][$indexFijo] = $myRowTotal['ejercicio_anterior1'];
    	}else{
    		$arrayTotalesAnteriorCuenta[$myRowTotal['accountcode']][$indexDetalle] = $myRowTotal['ejercicio_anterior1'];
    	}

    	$indexDetalle=$indexDetalle + 1;
	}
}

// var_dump($arrayTotalesAnterior);
// var_dump($arrayTotalesAnteriorCuenta);

$sumaColumna1=0;
$sumaColumna2=0;
$sumaColumna3=0;
$sumaColumna4=0;
$sumaColumna5=0;
$sumaColumna6=0;

DB_data_seek($result,0);
while ($myrow = DB_fetch_array($result)) {

	$sumFila='0.00';

	if($myrow['valor'] != $encabezado){
    	$encabezado = $myrow['valor'];
    	$index=$index + 1;
	}

	$nodo_hijo = $xml->createElement('cuentaMovimientos');

	$nodo_hijo->setAttribute("cuentaPrincipal", ($myrow['valor']));
   	$nodo_hijo->setAttribute("nombrePrincipal", ($myrow['accountnameprincipal']) . ' '. ($anioReporte -1 ));
	$sumFila=$myrow['ejercicio_anterior1'];
	//$sumaColumna1=$sumaColumna1 + $myrow['ejercicio_anterior1'];
	
	$nodo_hijo->setAttribute("cuenta", ($myrow['accountcode']));
    $nodo_hijo->setAttribute("nombre", ($myrow['accountname']));
    $nodo_hijo->setAttribute("val1", $arrayTotalesAnteriorCuenta[$myrow['accountcode']][0]);
    $nodo_hijo->setAttribute("val2", $arrayTotalesAnteriorCuenta[$myrow['accountcode']][1]);
    $nodo_hijo->setAttribute("val3", $arrayTotalesAnteriorCuenta[$myrow['accountcode']][2]);
    $nodo_hijo->setAttribute("val4", $arrayTotalesAnteriorCuenta[$myrow['accountcode']][3]);/*Pendiente por definir*/
    
    $nodo_hijo->setAttribute("totalFila", 	$arrayTotalesAnteriorCuenta[$myrow['accountcode']][0] +
    									    $arrayTotalesAnteriorCuenta[$myrow['accountcode']][1]+
    										$arrayTotalesAnteriorCuenta[$myrow['accountcode']][2]+
    										$arrayTotalesAnteriorCuenta[$myrow['accountcode']][3]);
    
    $sumaColumna1 +=($arrayTotalesAnteriorCuenta[$myrow['accountcode']][0]);
	$sumaColumna2 += ($arrayTotalesAnteriorCuenta[$myrow['accountcode']][1]);
    $sumaColumna3 += ($arrayTotalesAnteriorCuenta[$myrow['accountcode']][2]);
    $sumaColumna4 += "0.00";

    if($myrow['valor'] == "3.1"){

    	$nodo_hijo->setAttribute("totalColumna1", $arrayTotalesAnteriorCuenta['3.1.1'][0] + 
    											  $arrayTotalesAnteriorCuenta['3.1.1'][1] + 
    											  $arrayTotalesAnteriorCuenta['3.1.1'][2]);

	    $nodo_hijo->setAttribute("totalColumna2", $arrayTotalesAnteriorCuenta['3.1.2'][0] + 
	    										  $arrayTotalesAnteriorCuenta['3.1.2'][1] + 
	    										  $arrayTotalesAnteriorCuenta['3.1.2'][2]);

	    $nodo_hijo->setAttribute("totalColumna3", $arrayTotalesAnteriorCuenta['3.1.3'][0] + 
	    										  $arrayTotalesAnteriorCuenta['3.1.3'][1] + 
	    										  $arrayTotalesAnteriorCuenta['3.1.3'][2]);

	    $nodo_hijo->setAttribute("totalColumna4", '0.00');

	    $nodo_hijo->setAttribute("totalSumaTotalColumnas",$arrayTotalesAnterior[$myrow['valor']]);

		$nodo_principal->appendChild($nodo_hijo);
    }else if($myrow['valor'] == "3.2"){
    	$nodo_hijo->setAttribute("totalColumna1", '0.00');

	    $nodo_hijo->setAttribute("totalColumna2", $arrayTotalesAnteriorCuenta['3.2.2'][0] + 
	    										  $arrayTotalesAnteriorCuenta['3.2.2'][1] + 
	    										  $arrayTotalesAnteriorCuenta['3.2.2'][2]);

	    $nodo_hijo->setAttribute("totalColumna3", $arrayTotalesAnteriorCuenta['3.2.1'][0] + 
	    										  $arrayTotalesAnteriorCuenta['3.2.1'][1] + 
	    										  $arrayTotalesAnteriorCuenta['3.2.1'][2]);

	    $nodo_hijo->setAttribute("totalColumna4", $arrayTotalesAnteriorCuenta['3.2.3'][0] + 
	    										  $arrayTotalesAnteriorCuenta['3.2.3'][1] + 
	    										  $arrayTotalesAnteriorCuenta['3.2.3'][2]);

	    $nodo_hijo->setAttribute("totalSumaTotalColumnas",$arrayTotalesAnterior[$myrow['valor']]);

		$nodo_principal->appendChild($nodo_hijo);
    }else if($myrow['valor'] == "3.3"){
		$nodo_hijo->setAttribute("totalColumna1", '0.00');

	    $nodo_hijo->setAttribute("totalColumna2", $arrayTotalesAnteriorCuenta['3.3.2'][0] + 
	    										  $arrayTotalesAnteriorCuenta['3.3.2'][1] + 
	    										  $arrayTotalesAnteriorCuenta['3.3.2'][2]);

	    $nodo_hijo->setAttribute("totalColumna3", $arrayTotalesAnteriorCuenta['3.3.1'][0] + 
	    										  $arrayTotalesAnteriorCuenta['3.3.1'][1] + 
	    										  $arrayTotalesAnteriorCuenta['3.3.1'][2]);

	    $nodo_hijo->setAttribute("totalColumna4", $arrayTotalesAnteriorCuenta['3.3.3'][0] + 
	    										  $arrayTotalesAnteriorCuenta['3.3.3'][1] + 
	    										  $arrayTotalesAnteriorCuenta['3.3.3'][2]);

	    $nodo_hijo->setAttribute("totalSumaTotalColumnas",$arrayTotalesAnterior[$myrow['valor']]);

		$nodo_principal->appendChild($nodo_hijo);

	}
    
}

/* Generamos la partida de los totales por columnas del reporte del periodo Anterior */
$nodo_totales = $xml->createElement('cuentaMovimientos');
$nodo_totales->setAttribute("cuentaPrincipal", $encabezado);
$nodo_totales->setAttribute("nombrePrincipal", 'HACIENDA PUBLICA / PATRIMONIO NETO FINAL' . ' ' . ($anioReporte -1) );
$nodo_totales->setAttribute("cuenta", '-1');
$nodo_totales->setAttribute("nombre", 'HACIENDA PUBLICA / PATRIMONIO NETO FINAL' . ' ' . ($anioReporte - 1) );
$nodo_totales->setAttribute("val1", $sumaColumna1);
$nodo_totales->setAttribute("val2", $sumaColumna2);
$nodo_totales->setAttribute("val3", $sumaColumna3);
$nodo_totales->setAttribute("val4", $sumaColumna4);
$nodo_totales->setAttribute("totalFila", $sumaColumna1+$sumaColumna2+$sumaColumna3+$sumaColumna4+$sumaColumna5+$sumaColumna6);
$nodo_principal->appendChild($nodo_totales);
/*************************************************************************************/

//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//!!                                                   !!
//!! Generamos los movimientos del periodo Actual.     !!
//!!                                                   !!
//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!


/* Calculamos los totales para las sumas que se realiza por encabezado */

DB_data_seek($result,0);
$cuenta= "";
$arrayTotalesActual = [];
$arrayTotalesActualCuenta = [];
$contador = 0;
$index=0;
$cuenta="";

$sumaTotalColumna1=0;
$sumaTotalColumna2=0;
$sumaTotalColumna3=0;
$sumaTotalColumna4=0;
$sumaTotalColumna5=0;
$sumaTotalColumna6=0;

while ($myRowTotal = DB_fetch_array($result)) {
	$sumFila='0.00';
	if($myRowTotal['valor'] != $cuenta){
    	$cuenta = $myRowTotal['valor'];
    	$index=$index + 1;
    	$sumaTotalColumna1=0;
    	$indexDetalle=0;
	}

	$sumaTotalColumna1=$sumaTotalColumna1 + $myRowTotal['ejercicio_actual'];

	$arrayTotalesActual[$myRowTotal['valor']]  = $sumaTotalColumna1;

	if($myRowTotal['accountcode'] != $cuentaDetalle){
    	$cuentaDetalle = $myRowTotal['accountcode'];
    	
    	$arrayTotalesActualCuenta[$myRowTotal['accountcode']][0] = "0";
    	$arrayTotalesActualCuenta[$myRowTotal['accountcode']][1] = "0";
    	$arrayTotalesActualCuenta[$myRowTotal['accountcode']][2] = "0";
    	$arrayTotalesActualCuenta[$myRowTotal['accountcode']][3] = "0";

    	if($index == "2"){
    		$indexFijo=0;

    		if($myRowTotal['accountcode'] == "3.2.1"){
    			$indexFijo = 2;
    		}elseif ($myRowTotal['accountcode'] == "3.2.2") {
    			$indexFijo = 1;
    		}elseif ($myRowTotal['accountcode'] == "3.2.3") {
    			$indexFijo = 3;
    		}else{
    			$indexFijo = 0;
    		}

    		$arrayTotalesActualCuenta[$myRowTotal['accountcode']][$indexFijo] = $myRowTotal['ejercicio_actual'];
    	}else{
    		$arrayTotalesActualCuenta[$myRowTotal['accountcode']][$indexDetalle] = $myRowTotal['ejercicio_actual'];
    	}

    	$indexDetalle=$indexDetalle + 1;
	}
}

// var_dump($arrayTotalesActual);
// var_dump($arrayTotalesActualCuenta);
/*******************************************************************************/

/* Acomodamos la infomacion para el perido actual*/

DB_data_seek($result,0);
$encabezado = "";
$index=0;

$sumaColumna11=0;
$sumaColumna22=0;
$sumaColumna33=0;
$sumaColumna44=0;
$sumaColumna55=0;
$sumaColumna66=0;

while ($myrow = DB_fetch_array($result)) {

    $sumFila='0.00';

	if($myrow['valor'] != $encabezado){
    	$encabezado = $myrow['valor'];
    	$index=$index + 1;
	}

	$nodo_hijo = $xml->createElement('cuentaMovimientos');

	$nodo_hijo->setAttribute("cuentaPrincipal", ($myrow['valor']));
   	$nodo_hijo->setAttribute("nombrePrincipal", ($myrow['accountnameprincipal']) . ' '. ($anioReporte ));
	$sumFila=$myrow['ejercicio_anterior1'];
	//$sumaColumna1=$sumaColumna1 + $myrow['ejercicio_anterior1'];
	
	$nodo_hijo->setAttribute("cuenta", ($myrow['accountcode']));
    $nodo_hijo->setAttribute("nombre", ($myrow['accountname']));
    $nodo_hijo->setAttribute("val1", $arrayTotalesActualCuenta[$myrow['accountcode']][0]);
    $nodo_hijo->setAttribute("val2", $arrayTotalesActualCuenta[$myrow['accountcode']][1]);
    $nodo_hijo->setAttribute("val3", $arrayTotalesActualCuenta[$myrow['accountcode']][2]);
    $nodo_hijo->setAttribute("val4", $arrayTotalesActualCuenta[$myrow['accountcode']][3]);/*Pendiente por definir*/
    
    $nodo_hijo->setAttribute("totalFila", 	$arrayTotalesActualCuenta[$myrow['accountcode']][0] +
    									    $arrayTotalesActualCuenta[$myrow['accountcode']][1]+
    										$arrayTotalesActualCuenta[$myrow['accountcode']][2]+
    										$arrayTotalesActualCuenta[$myrow['accountcode']][3]);
    
    $sumaColumna11 +=($arrayTotalesActualCuenta[$myrow['accountcode']][0]);
	$sumaColumna22 += ($arrayTotalesActualCuenta[$myrow['accountcode']][1]);
    $sumaColumna33 += ($arrayTotalesActualCuenta[$myrow['accountcode']][2]);
    $sumaColumna44 += "0.00";

    if($myrow['valor'] == "3.1"){

    	$nodo_hijo->setAttribute("totalColumna1", $arrayTotalesActualCuenta['3.1.1'][0] + 
    											  $arrayTotalesActualCuenta['3.1.1'][1] + 
    											  $arrayTotalesActualCuenta['3.1.1'][2]);
	    $nodo_hijo->setAttribute("totalColumna2", $arrayTotalesActualCuenta['3.1.2'][0] + 
	    										  $arrayTotalesActualCuenta['3.1.2'][1] + 
	    										  $arrayTotalesActualCuenta['3.1.2'][2]);
	    $nodo_hijo->setAttribute("totalColumna3", $arrayTotalesActualCuenta['3.1.3'][0] + 
	    										  $arrayTotalesActualCuenta['3.1.3'][1] + 
	    										  $arrayTotalesActualCuenta['3.1.3'][2]);
	    $nodo_hijo->setAttribute("totalColumna4", '0.00');

	    $nodo_hijo->setAttribute("totalSumaTotalColumnas",$arrayTotalesActual[$myrow['valor']]);

		$nodo_principal->appendChild($nodo_hijo);
    }else if($myrow['valor'] == "3.2"){
    	$nodo_hijo->setAttribute("totalColumna1", '0.00');

	    $nodo_hijo->setAttribute("totalColumna2", $arrayTotalesActualCuenta['3.2.2'][0] + 
	    										  $arrayTotalesActualCuenta['3.2.2'][1] + 
	    										  $arrayTotalesActualCuenta['3.2.2'][2]);
	    $nodo_hijo->setAttribute("totalColumna3", $arrayTotalesActualCuenta['3.2.1'][0] + 
	    										  $arrayTotalesActualCuenta['3.2.1'][1] + 
	    										  $arrayTotalesActualCuenta['3.2.1'][2]);
	    $nodo_hijo->setAttribute("totalColumna4", $arrayTotalesActualCuenta['3.2.3'][0] + 
	    										  $arrayTotalesActualCuenta['3.2.3'][1] + 
												  $arrayTotalesActualCuenta['3.2.3'][2]);
		$nodo_hijo->setAttribute("totalColumna4", $arrayTotalesActualCuenta['3.2.3'][0] + 
	    										  $arrayTotalesActualCuenta['3.2.3'][1] + 
	    										  $arrayTotalesActualCuenta['3.2.3'][2]);										  
	    $nodo_hijo->setAttribute("totalSumaTotalColumnas",$arrayTotalesActual[$myrow['valor']]);

		$nodo_principal->appendChild($nodo_hijo);
    }else if($myrow['valor'] == "3.3"){
		$nodo_hijo->setAttribute("totalColumna1", '0.00');

	    $nodo_hijo->setAttribute("totalColumna2", $arrayTotalesActualCuenta['3.3.2'][0] + 
	    										  $arrayTotalesActualCuenta['3.3.2'][1] + 
	    										  $arrayTotalesActualCuenta['3.3.2'][2]);
	    $nodo_hijo->setAttribute("totalColumna3", $arrayTotalesActualCuenta['3.3.1'][0] + 
	    										  $arrayTotalesActualCuenta['3.3.1'][1] + 
	    										  $arrayTotalesActualCuenta['3.3.1'][2]);
	    $nodo_hijo->setAttribute("totalColumna4", $arrayTotalesActualCuenta['3.3.3'][0] + 
	    										  $arrayTotalesActualCuenta['3.3.3'][1] + 
												  $arrayTotalesActualCuenta['3.3.3'][2]);
		$nodo_hijo->setAttribute("totalColumna4", $arrayTotalesActualCuenta['3.3.3'][0] + 
	    										  $arrayTotalesActualCuenta['3.3.3'][1] + 
	    										  $arrayTotalesActualCuenta['3.3.3'][2]);										  
	    $nodo_hijo->setAttribute("totalSumaTotalColumnas",$arrayTotalesActual[$myrow['valor']]);

		$nodo_principal->appendChild($nodo_hijo);
	}

	
	$nodo_principal->appendChild($nodo_hijo);
}

/* Generamos la partida de los totales por columnas del reporte del periodo actual */
$nodo_totales = $xml->createElement('cuentaMovimientos');
$nodo_totales->setAttribute("cuentaPrincipal", $encabezado);
$nodo_totales->setAttribute("nombrePrincipal", 'HACIENDA PUBLICA / PATRIMONIO NETO FINAL' . ' ' . ($anioReporte) );
$nodo_totales->setAttribute("cuenta", '-1');
$nodo_totales->setAttribute("nombre", 'HACIENDA PUBLICA / PATRIMONIO NETO FINAL' . ' ' . ($anioReporte) );
$nodo_totales->setAttribute("val1", $sumaColumna11);
$nodo_totales->setAttribute("val2", $sumaColumna22);
$nodo_totales->setAttribute("val3", $sumaColumna33);
$nodo_totales->setAttribute("val4", $sumaColumna44);
$nodo_totales->setAttribute("totalFila", ($sumaColumna11)+($sumaColumna22)+($sumaColumna33)+($sumaColumna44));
$nodo_principal->appendChild($nodo_totales);

/*******************************************************************************/

/* Guardamos la estructura del xml*/
$xml->appendChild($nodo_principal);  
$cadena_xml= $xml->saveXML(); // cadena string del xml

// echo $cadena_xml;
// echo htmlentities($cadena_xml);
// exit();

/* Instancia para generar el pdf con jasper */
$JasperReport = new JasperReport($confJasper);
$rutaReporte = "/conac/rptEstadoVariacionHaciendaPatrimonio";
$rutaReporte = ( !$XLS ? $rutaReporte : ( file_exists($path.str_replace("../", "", $rutaReporte).$XLS.".jasper") ? $rutaReporte.$XLS : $rutaReporte) );
$jreport = $JasperReport->compilerReport($rutaReporte);

$logo_legal = $_SESSION['LogoFile'];
$JasperReport->addParameter("SUBREPORT_DIR", $JasperReport->getPathFile() . "../jasper/conac/");
$JasperReport->addParameter("imagen", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/".$logo_legal))));
$JasperReport->addParameter("imagenEstado", (str_replace('/jasper/', '', ($JasperReport->getPathFile()."/images/estadoTamp.jpeg"))));

$JasperReport->addParameter ( "parRangoDeFechas", "Desde 1 Enero del " . $_GET["anio"] . " al 31 de diciembre del " . $_GET["anio"]);

if (isset($_GET["anio"])) {
    $JasperReport->addParameter("anioreporte", (int)$_GET["anio"]);
}

if (isset($_GET["tagref"])) {
    $JasperReport->addParameter("tagref", $_GET["tagref"]);
}

if (isset($_GET["ue"])) {
    if ($_GET["ue"] == '') {
        $_GET["ue"] = '';
    }
    $JasperReport->addParameter("ue", $_GET["ue"]);
}

$gerenciaDescripcion = "";

if (isset($_GET["ue"])) {
    $arrUE = explode(",", $_GET["ue"]);

    if( count($arrUE) >1){
        $gerenciaDescripcion="Consolidado de gerencias (".$_GET["ue"].")";

        if($_GET['totalue'] == count($arrUE)){
            $gerenciaDescripcion = "Consolidado"; 
        }

    }else{
        $SQL = "SELECT desc_ue FROM tb_cat_unidades_ejecutoras WHERE ur = '".$_GET["tagref"]."' AND ue = '".$_GET["ue"]."'";
        $result = DB_query($SQL,$db);
        if($result){
            $myrow = DB_fetch_array($result);
            $gerenciaDescripcion = $myrow['desc_ue'];
        }
	}
	

	
	
}

if (empty($gerenciaDescripcion)) {
    $gerenciaDescripcion = "";
}

$JasperReport->addParameter("fechaReporteNew", "" . fnFormatoFecha($_GET["fechainicial"]. " 00:00:00",$_GET["fechafinal"]. " 23:59:59"));

$JasperReport->addParameter("descripcionUE", "".$gerenciaDescripcion);

$JasperReport->addParameter("parEntePublico", "".$_GET["entepublico"]);

// echo htmlentities($cadena_xml);
// exit();

$datasource= $JasperReport->getDataSourceXML(($cadena_xml), "/rptEstadoVariacionHacienda/cuentaMovimientos");
$jPrint= $JasperReport->fillReport($jreport, $JasperReport->getParameters(), $datasource);

$pdfBytes = ( $XLS ? $JasperReport->exportReportXLS($jPrint) : $JasperReport->exportReportPDF($jPrint) );

header('Content-type: application/'.( $XLS ? "vnd.ms-excel" : "pdf" ));
header('Content-Length: ' . strlen($pdfBytes));
header('Content-Disposition: inline; filename='.$_GET['nombreArchivo'].".".( $XLS ? "xls" : "pdf" ));
echo $pdfBytes;

?>