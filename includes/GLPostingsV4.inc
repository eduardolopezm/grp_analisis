<?php

$FirstPeriodResult = DB_query('SELECT MIN(periodno) FROM periods',$db);
$FirstPeriodRow = DB_fetch_row($FirstPeriodResult);
$CreateFrom = $FirstPeriodRow[0];

// SOLO CORRE LA PRIMERA VEZ QUE EJECUTAMOS UNA NUEVA IMPLEMENTACION
if (is_null($FirstPeriodRow[0])){
	$TransStart = DB_Txn_Begin($db);
	//There are no periods defined
	$InsertFirstPeriodResult = DB_query("INSERT INTO periods VALUES (0,'" . Date('Y-m-d',mktime(0,0,0,Date('m')+1,0,Date('Y'))) . "')",$db,_('Could not insert first period'));
	$InsertFirstPeriodResult = DB_query("INSERT INTO periods VALUES (1,'" . Date('Y-m-d',mktime(0,0,0,Date('m')+2,0,Date('Y'))) . "')",$db,_('Could not insert second period'));
	$CreateFrom=0;
	$TransCommit = DB_Txn_Commit($db);
}

$LastPeriodResult = DB_query('SELECT (MAX(periodno) + 1) FROM gltrans where month(trandate) = month(Now())',$db);
$LastPeriodRow = DB_fetch_row($LastPeriodResult);
$CreateTo = $LastPeriodRow[0];

$FirstPeriodResult = DB_query('SELECT ifnull(MIN(periodno),-1) FROM gltrans WHERE posted = 0',$db);
$FirstPeriodRow = DB_fetch_row($FirstPeriodResult);
$CreateFrom = $FirstPeriodRow[0];

if ($CreateFrom == -1){

	$CreateFrom = $CreateTo;
}


/*All the ChartDetail records should have been created now and be available to accept postings */
/*  PORdesarrollo21-12-2009 */

/* NUEVA VERSION DE PROCESO 100% DIRECTO EN SQL ! ES RAPIDO !*/

/* ENCUENTRA EL ULTIMO MOVIMIENTO CONTABLE POR POSTEAR HASTA ESTE MOMENTO...
	   ESTO PARA CONGELAR LOS UPDATES A ESTE REGISTRO EN CASO DE QUE SE INSERTE DURANTE ESTA ACTUALIZACION...*/

$errores = 0;

/** SOLO CORRER EN CASO DE PENSAR QUE HAY CUENTAS QUE FALTAN
$sql = 'select distinct gltrans.account
	FROM  gltrans LEFT JOIN chartdetails ON gltrans.account = chartdetails.accountcode
	WHERE  gltrans.posted = 0 AND chartdetails.accountcode is null
	';

$sinCuentas = DB_query($sql,$db);

$errores = 0;
$cuenta = '';
while ($myrow=DB_fetch_array($sinCuentas,$db)){
	prnMsg(_('Cuenta contable no existe en CHARTDETAILS, reportar con administrador !...(').$myrow['account'].')','error');
	$errores = $errores  + 1;
	$cuenta = $myrow['account'];
}
*/

if ($errores == 0) {
	//DB_query("LOCK TABLES gltrans WRITE",$db);
	$sql = 'SELECT max(counterindex) as maxindice
		FROM gltrans WHERE posted = 0';
	$Periods = DB_query($sql,$db);
	//DB_query("UNLOCK TABLES",$db);

	if ($myrow=DB_fetch_array($Periods,$db)){
		$lastPostForProcessing = $myrow['maxindice'];
	} else {
		$lastPostForProcessing = 0;
	}

	if ($lastPostForProcessing == null) {
		$lastPostForProcessing = 0;
	}

	//echo 'ultimo id de GLTRans:'.$lastPostForProcessing.'<br>';

	if ($lastPostForProcessing > 0) {
		DB_query("LOCK TABLES chartdetails WRITE, chartdetails as c2 WRITE, gltrans WRITE",$db);
		$sql = 'UPDATE chartdetails JOIN (SELECT accountcode, period, tagref, sum(amount) as amount,
					sum(CASE WHEN amount >=0 THEN amount ELSE 0 END) as cargos,
					sum(CASE WHEN amount <0 THEN amount*-1 ELSE 0 END) as abonos
			FROM  chartdetails as c2 JOIN gltrans ON gltrans.account = c2.accountcode AND
												gltrans.periodno = c2.period AND
												gltrans.tag = c2.tagref
			WHERE 	gltrans.posted = 0 and
				gltrans.counterindex <= '.$lastPostForProcessing.'
			GROUP BY accountcode, period, tagref) as resumen  ON resumen.accountcode = chartdetails.accountcode AND
												resumen.period = chartdetails.period AND
												resumen.tagref = chartdetails.tagref
			SET chartdetails.actual = chartdetails.actual + resumen.amount,
					chartdetails.cargos = chartdetails.cargos + resumen.cargos,
					chartdetails.abonos = chartdetails.abonos + resumen.abonos ';

		$UpdActualChartDetails = DB_query($sql,$db);
		$sql = 'UPDATE gltrans SET posted=1 WHERE gltrans.counterindex <= '.$lastPostForProcessing;
		$UpdGLTransPostedFlag = DB_query($sql,$db);
		DB_query("UNLOCK TABLES",$db);

		/* ACUMULA SALDOS HACIA ADELANTE .... !!! */
		//FCC: COMENTE ESTA FUNCION, SE CAMBIO POR UN FOR QUE RECORRE LOS PERIODOS Y ACTUALIZA EL BFWD CON LA SUMA
		//DEL BFWD + ACTUAL DEL PERIODO ANTERIOR.
		/*
		$sql = 'UPDATE chartdetails JOIN (SELECT accountcode, period, tagref, sum(amount) as amount
			FROM  chartdetails JOIN gltrans ON gltrans.account = chartdetails.accountcode AND
												gltrans.periodno < chartdetails.period AND
												gltrans.tag = chartdetails.tagref
			WHERE 	gltrans.posted = 0 and
				gltrans.counterindex <= '.$lastPostForProcessing.'
			GROUP BY accountcode, period, tagref) as resumen  ON resumen.accountcode = chartdetails.accountcode AND
												resumen.period = chartdetails.period AND
												resumen.tagref = chartdetails.tagref
			SET chartdetails.bfwd = chartdetails.bfwd + resumen.amount';

		$UpdActualChartDetails = DB_query($sql,$db);
		*/
		//FCC NUEVA FORMA DE CALCULAR EL CAMPO BFWD
		//echo "<br>1.- " . $CreateFrom;
		//echo "<br>2.- " . $CreateTo;
		for ($i = $CreateFrom; $i < $CreateTo; $i++){
			 $sql = "update chartdetails c1
			left join  chartdetails c2 on c1.accountcode = c2.accountcode
					and c1.tagref = c2.tagref
					and c1.period = (c2.period+1)
			set c1.bfwd = (ifnull(c2.bfwd,0)+ifnull(c2.actual,0))
			where c1.period = " . $i . "
			and c2.accountcode is not null";
			//**echo $sql;
			$result = DB_query($sql,$db);

		}
		/* Make the posted flag on all GL entries including and after the period selected = 0 */

	}




} else {
	prnMsg(_('NO SE PUEDEN POSTEAR MOVIMIENTOS PORQUE HAY ERRORES !...(').$cuenta.')','warn');
}
// CODIGO ESTABA COMENTADO HASTA AQUI...desarrollo- 20-12-2009
/*******************************************************/

?>
