<?php


if  (!isset($_GET['automatico'])){
	include('includes/session.inc');
	$title = _('RESPOTEO CONTABLE');
	include('includes/header.inc');
	$funcion=901;
	include('includes/SecurityFunctions.inc');
	include('includes/SQL_CommonFunctions.inc');
} else {
	session_start();

	$PathPrefix = '';

	$host = "localhost";
	$mysqlport = "3306";
	$dbuser = "root";
	$dbpassword = "Elc4N742!";

	$_SESSION['DatabaseName'] = "servillantas";

	include($PathPrefix . 'includes/SQL_CommonFunctions.inc');
	include($PathPrefix . 'includes/DateFunctions.inc');
	include($PathPrefix . 'includes/ConnectDB_mysqli.inc');
}

//CONSULTA PERIODO MAXIMO Y MINIMO
$sql = 'select 	max(gltrans.periodno) as maxp,
		 min(gltrans.periodno) as minp
		 from gltrans';
$PeriodsMM = DB_queryNT($sql,$db);
if ($myrowMM=DB_fetch_array($PeriodsMM,$db)){
	$minimoperiodo = $myrowMM['minp'];
	$maximoperiodo = $myrowMM['maxp'];
}


 //CONSULTA TODOS LOS PERIODOS
 $sql = 'select gltrans.periodno, count(*)
		  from gltrans
		  group by gltrans.periodno
		  order by gltrans.periodno';

$PeriodsMM = DB_queryNT($sql,$db);
$pidx = $minimoperiodo;
$periodoAnterior = $minimoperiodo-1;
while ($myrowMM=DB_fetch_array($PeriodsMM,$db)){
	$periodos[$pidx] = $myrowMM['periodno'];
	$periodosAnt[$pidx] = $periodoAnterior;
	//echo "P:".$pidx.":".$myrowMM['periodno']." ant:".$periodoAnterior."<br>";
	$pidx = $pidx + 1;
	$periodoAnterior = $myrowMM['periodno'];
}

$sql = "UPDATE gltrans SET posted=1 WHERE posted = 0";
$UpdActualChartDetails = DB_queryNT($sql,$db);

/* Now make all the actuals 0 for all periods including and after the period from */
$sql = "UPDATE chartdetails SET actual =0, bfwd =0, cargos =0, abonos =0 ";
$UpdActualChartDetails = DB_queryNT($sql,$db);

$sql = "DELETE FROM RePostGL";
$UpdActualChartDetails = DB_queryNT($sql,$db);


/*Inserta en chartdetails cuentas faltantes por periodo y unidad de negocio*/

$sqltags = 'select tagref
		  from tags';
$Resulttags = DB_queryNT($sqltags,$db);

while ($myrowtags = DB_fetch_array($Resulttags,$db)){
	$isqlc = "INSERT INTO chartdetails
			SELECT accountcode, periodno,
					'" . $myrowtags['tagref'] . "' as tagref,
					0 as budget,
					0 as actual,
					0 as bfwd,
					0 as bfwdbudget,
					0 as cargos,  0 as abonos,
					case when SUBSTR(accountcode,1,LOCATE('.', accountcode) + 	LOCATE('.', SUBSTR(accountcode,LOCATE('.', accountcode)+1)) + LOCATE('.', SUBSTR(SUBSTR(accountcode,LOCATE('.', accountcode)+1),LOCATE('.', SUBSTR(accountcode,LOCATE('.', accountcode)+1))+1))-1) <> ''
						THEN SUBSTR(accountcode,1,
							LOCATE('.', accountcode) + 	LOCATE('.', SUBSTR(accountcode,LOCATE('.', accountcode)+1)) + LOCATE('.', SUBSTR(SUBSTR(accountcode,LOCATE('.', accountcode)+1),LOCATE('.', SUBSTR(accountcode,LOCATE('.', accountcode)+1))+1))-1)
						ELSE g.groupcodetb END secondparentgroup
			FROM (chartmaster c
				LEFT JOIN  accountgroups g ON c.group_ = g.groupname)
				CROSS JOIN periods
			WHERE (accountcode, periodno) not in (
				SELECT accountcode, period
				FROM chartdetails
				WHERE tagref = '" . $myrowtags['tagref'] . "'
			)";
	DB_queryNT($isqlc,$db);

}


$sql = "INSERT INTO RePostGL
			SELECT account,
					periodno,
				   tag, sum(amount) as amount,
				   sum(CASE WHEN amount >=0 THEN amount ELSE 0 END) as cargos,
				   sum(CASE WHEN amount <0 THEN amount*-1 ELSE 0 END) as abonos,
				   sum(amount) as actual, 0 as bfwd
		  	FROM  gltrans
				LEFT JOIN chartmaster ON gltrans.account = chartmaster.accountcode
		  	WHERE gltrans.posted = 1
			GROUP BY account, periodno, tag
		  	ORDER BY tag, account, periodno";
$UpdActualChartDetails = DB_queryNT($sql,$db);
/*
$sql = "INSERT INTO chartdetails
		SELECT RePostGL.accountcode, RePostGL.period, RePostGL.tagref, 0,0,0,0,0,0
		FROM  RePostGL
			LEFT JOIN chartdetails ON RePostGL.accountcode = chartdetails.accountcode
				AND	RePostGL.period = chartdetails.period
				AND RePostGL.tagref = chartdetails.tagref
		WHERE chartdetails.accountcode is null";
$UpdActualChartDetails = DB_queryNT($sql,$db);
*/

$sql = "UPDATE chartdetails
			JOIN RePostGL as resumen  ON resumen.accountcode = chartdetails.accountcode
				AND resumen.period = chartdetails.period
				AND resumen.tagref = chartdetails.tagref
			SET chartdetails.actual = resumen.amount,
				chartdetails.cargos = resumen.cargos,
				chartdetails.abonos = resumen.abonos";
$UpdActualChartDetails = DB_queryNT($sql,$db);

for ($i = $minimoperiodo; $i <= $pidx; $i++){
	if (strlen($periodosAnt[$i]) > 0 and strlen($periodos[$i]) > 0) {
		$sql = "UPDATE chartdetails c1
			LEFT JOIN chartdetails c2 on c1.accountcode = c2.accountcode
				AND c1.tagref = c2.tagref
			SET c1.bfwd = (c2.bfwd + c2.actual)
			WHERE c1.period=".$periodos[$i]."
				AND c2.period = " . $periodosAnt[$i] . "
				AND c2.accountcode IS NOT NULL";
		$result = DB_queryNT($sql,$db);
		echo "<BR>Periodo Actualizado...: " . $periodos[$i];
	}
}

echo "<br><BR><B>REPOSTEO FINALIZO...</B>";




?>
