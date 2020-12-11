<?php

prnMsg(_('Ejecutando proceso de posteo de cuentas contables...'),'info');

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

$LastPeriodResult = DB_query('SELECT MAX(periodno) FROM periods',$db);
$LastPeriodRow = DB_fetch_row($LastPeriodResult);

$CreateTo = $LastPeriodRow[0];

/*All the ChartDetail records should have been created now and be available to accept postings */
/*  PORdesarrollo21-12-2009 */

/* NUEVA VERSION DE PROCESO 100% DIRECTO EN SQL ! ES RAPIDO !*/

/* ENCUENTRA EL ULTIMO MOVIMIENTO CONTABLE POR POSTEAR HASTA ESTE MOMENTO...
	   ESTO PARA CONGELAR LOS UPDATES A ESTE REGISTRO EN CASO DE QUE SE INSERTE DURANTE ESTA ACTUALIZACION...*/
$sql = 'SELECT max(counterindex) as maxindice
	FROM gltrans WHERE posted = 0';
$Periods = DB_query($sql,$db);

if ($myrow=DB_fetch_array($Periods,$db)){
	$lastPostForProcessing = $myrow['maxindice'];
} else {
	$lastPostForProcessing = 0;
}

if ($lastPostForProcessing == null) {
	$lastPostForProcessing = 0;
}

echo 'ultimo id de GLTRans:'.$lastPostForProcessing.'<br>';
$sql = 'UPDATE chartdetails JOIN (SELECT accountcode, period, tagref, sum(amount) as amount
	FROM  chartdetails JOIN gltrans ON gltrans.account = chartdetails.accountcode AND
										gltrans.periodno = chartdetails.period AND
										gltrans.tag = chartdetails.tagref
	WHERE 	gltrans.posted = 0 and
		gltrans.counterindex <= '.$lastPostForProcessing.'
	GROUP BY accountcode, period, tagref) as resumen  ON resumen.accountcode = chartdetails.accountcode AND
										resumen.period = chartdetails.period AND
										resumen.tagref = chartdetails.tagref
	SET chartdetails.actual = chartdetails.actual + resumen.amount ';

$UpdActualChartDetails = DB_query($sql,$db);

/* ACUMULA SALDOS HACIA ADELANTE .... !!! */
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

/* Make the posted flag on all GL entries including and after the period selected = 0 */
$sql = 'UPDATE gltrans SET posted=1 WHERE gltrans.counterindex <= '.$lastPostForProcessing;

$UpdGLTransPostedFlag = DB_query($sql,$db);

// CODIGO ESTABA COMENTADO HASTA AQUI...desarrollo- 20-12-2009
/*******************************************************/

?>
