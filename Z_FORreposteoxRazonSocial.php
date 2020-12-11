<?php
/**
 * Pagina de consulta de ordenes de compra
 *
 * @category ABC
 * @package ap_grp
 * @author Armando Barrientos Martinez <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 01/08/2017
 * Fecha Modificación: 18/01/2018
 */

include('includes/session.inc');
$title = _('REPOSTEO CONTABLE');
include('includes/header.inc');
$funcion = 901;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['chkReposteoNormal'])) {
    if (isset($_POST['reposteo'])) {
        $legalid = $_POST['legalid'];

        //CONSULTA PERIODO MAXIMO Y MINIMO
        $sql = "SELECT 	max(gltrans.periodno) as maxp,
					min(gltrans.periodno) as minp
				FROM gltrans
				INNER JOIN tags ON gltrans.tag = tags.tagref AND tags.legalid = '" . $legalid . "'
				INNER JOIN chartmasterxlegal ON gltrans.account = chartmasterxlegal.accountcode
				AND chartmasterxlegal.legalid = '" . $_POST['legalid'] . "'";

        //echo "<br>sql 1: ".$sql."<br>";
        $PeriodsMM = DB_queryNT($sql, $db);

        if ($myrowMM = DB_fetch_array($PeriodsMM, $db)) {
            $minimoperiodo = $myrowMM['minp'];
            $maximoperiodo = $myrowMM['maxp'];
        }

        // $_SESSION['StartDate']
        $sql = "SELECT * 
			FROM periods
			WHERE periodno >= '" . $minimoperiodo . "'
				AND lastdate_in_period < 
				(CASE WHEN MONTH(Now()) = 12 THEN CONCAT(YEAR(Now())+1,'-01-01 00:00:00.000')
					ELSE CONCAT(YEAR(Now()),MONTH(Now())+1, '-01 00:00:00.000')
				END)
	 			ORDER BY periodno";

        //echo "<br>sql 2: ".$sql."<br>";
        $PeriodsMM = DB_queryNT($sql, $db);
        $pidx = $minimoperiodo;
        $periodoAnterior = $minimoperiodo ;

        while ($myrowMM = DB_fetch_array($PeriodsMM, $db)) {
            $periodos[$pidx] = $myrowMM['periodno'];
            $periodosAnt[$pidx] = $periodoAnterior;
            //echo "P:" . $pidx . ":" . $myrowMM['periodno'] . " ant:" . $periodoAnterior . "<br>";
            $pidx = $pidx + 1;
            $periodoAnterior = $myrowMM['periodno'];
        }
        /*$sql = "UPDATE gltrans
				INNER JOIN tags ON gltrans.tag = tags.tagref AND tags.legalid = '" . $legalid . "'
				INNER JOIN chartmasterxlegal ON gltrans.account = chartmasterxlegal.accountcode
				AND chartmasterxlegal.legalid = '" . $_POST['legalid'] . "'
				SET posted=1 
				WHERE posted = 0";

        echo "<br>sql 3: ".$sql."<br>";
        $UpdActualChartDetails = DB_queryNT($sql, $db);*/

        /* Now make all the actuals 0 for all periods including and after the period from */
        $sql = "UPDATE chartdetails 
				INNER JOIN tags ON chartdetails.tagref = tags.tagref AND tags.legalid = '" . $legalid . "'
				SET chartdetails.actual = 0, 
					chartdetails.bfwd = 0, 
					chartdetails.cargos = 0, 
					chartdetails.abonos = 0 ";
        
        //echo "<br>sql 4: ".$sql."<br>";
        $UpdActualChartDetails = DB_queryNT($sql, $db);

        $sql = "DELETE FROM RePostGL";
        //echo "<br>sql delete: ".$sql."<br>";
        $UpdActualChartDetails = DB_queryNT($sql, $db);

        /* REPOSTEA TODAS LAS TRANSACCIONES DEL GLTRANS Y ACUMULALAS EN CHARTDETAILS (desarrollo) CORRE EN SEGUNDOS !!! */
        $sql = "INSERT INTO RePostGL
					SELECT account, periodno,
						tag, sum(amount) as amount,
						sum(CASE WHEN amount >=0 THEN amount ELSE 0 END) as cargos,
						sum(CASE WHEN amount <0 THEN amount*-1 ELSE 0 END) as abonos,
						sum(amount) as actual, 0 as bfwd
					FROM  gltrans 
						INNER JOIN chartmasterxlegal ON gltrans.account = chartmasterxlegal.accountcode
							AND chartmasterxlegal.legalid = '" . $_POST['legalid'] . "'
						INNER JOIN tags ON gltrans.tag = tags.tagref AND tags.legalid = '" . $_POST['legalid'] . "'
					WHERE gltrans.posted = 1
					GROUP BY account, periodno, tag
					ORDER BY tag, account, periodno";

        //echo "<br>sql 5: ".$sql."<br>";
        $UpdActualChartDetails = DB_queryNT($sql, $db);

        $sql = "INSERT INTO chartdetails
					SELECT RePostGL.accountcode, RePostGL.period, RePostGL.tagref, 0,0,0,0,0,0
					FROM  RePostGL 
						LEFT JOIN chartdetails ON RePostGL.accountcode = chartdetails.accountcode 
							AND RePostGL.period = chartdetails.period 
							AND RePostGL.tagref = chartdetails.tagref
					WHERE chartdetails.accountcode is null";
        //echo "<br>sql 6: ".$sql."<br>";
        $UpdActualChartDetails = DB_queryNT($sql, $db);

        $sql = "UPDATE chartdetails 
					JOIN RePostGL as resumen  ON resumen.accountcode = chartdetails.accountcode 
						AND resumen.period = chartdetails.period 
						AND resumen.tagref = chartdetails.tagref
				SET chartdetails.actual = resumen.amount,
					chartdetails.cargos = resumen.cargos,
					chartdetails.abonos = resumen.abonos";
        //echo "<br>sql 7: ".$sql."<br>";
        $UpdActualChartDetails = DB_queryNT($sql, $db);

        //echo 'max:' . $maximoperiodo;
        for ($i = $minimoperiodo; $i <= $pidx; $i++) {
            if (strlen($periodosAnt[$i]) > 0 and strlen($periodos[$i]) > 0 and $periodos[$i]!=$periodosAnt[$i]) {
                $sql = "UPDATE chartdetails c1 
							INNER JOIN chartmasterxlegal ON c1.accountcode = chartmasterxlegal.accountcode
								AND chartmasterxlegal.legalid = '" . $_POST['legalid'] . "'
							INNER JOIN tags ON c1.tagref = tags.tagref AND tags.legalid = '" . $_POST['legalid'] . "'
							LEFT JOIN chartdetails c2 on c1.accountcode = c2.accountcode 
								AND c1.tagref = c2.tagref
						SET c1.bfwd = (c2.bfwd + c2.actual)
						WHERE  c1.period=" . $periodos[$i] . " 
							AND c2.period = " . $periodosAnt[$i] . "
							AND c2.accountcode is not null";
                //echo "<br>sql 8: ".$sql."<br>";
                $result = DB_queryNT($sql, $db);
                //echo "periodo...:" . "<pre>" . $sql . "<BR>" . $periodos[$i] ." ".$periodosAnt[$i]. "<BR>";
            }
        }

        echo "<br><BR><B>REPOSTEO FINALIZO...</B>";
    }
} else {
    if (isset($_POST['reposteo'])) {
        $legalid = $_POST['legalid'];

        //CONSULTA PERIODO MAXIMO Y MINIMO
        /*
		$sql = "SELECT 	max(gltrans.periodno) as maxp,
					min(gltrans.periodno) as minp
				FROM gltrans
					INNER JOIN tags ON gltrans.tag = tags.tagref AND tags.legalid = '" . $legalid . "'
					INNER JOIN chartmasterxlegal ON gltrans.account = chartmasterxlegal.accountcode
							AND chartmasterxlegal.legalid = '" . $_POST['legalid'] . "'";
		echo "<br>sql 1: ".$sql."<br>";
		$PeriodsMM = DB_queryNT($sql, $db);
		if ($myrowMM = DB_fetch_array($PeriodsMM, $db)) {
		    $minimoperiodo = $myrowMM['minp'];
		    $maximoperiodo = $myrowMM['maxp'];
		}*/

        $minimoperiodo = $_POST['cmbPedriodInicio'];
        $maximoperiodo = $_POST['cmbPedriodFinal'];

        // $_SESSION['StartDate']
        $sql = "SELECT * 
			FROM periods
			WHERE periodno >= '" . $minimoperiodo . "' and periodno <= '".$maximoperiodo."'
				AND lastdate_in_period < 
				(CASE WHEN MONTH(Now()) = 12 THEN CONCAT(YEAR(Now())+1,'-01-01 00:00:00.000')
					ELSE CONCAT(YEAR(Now()),MONTH(Now())+1, '-01 00:00:00.000')
				END)
	 			ORDER BY periodno";
        //echo "<br>sql 2: ".$sql."<br>";

        $PeriodsMM = DB_queryNT($sql, $db);
        $pidx = $minimoperiodo;
        $periodoAnterior = $minimoperiodo ;
        while ($myrowMM = DB_fetch_array($PeriodsMM, $db)) {
            $periodos[$pidx] = $myrowMM['periodno'];
            $periodosAnt[$pidx] = $periodoAnterior;
            //echo "P:" . $pidx . ":" . $myrowMM['periodno'] . " ant:" . $periodoAnterior . "<br>";
            $pidx = $pidx + 1;
            $periodoAnterior = $myrowMM['periodno'];
        }

        /*$sql = "UPDATE gltrans
					INNER JOIN tags ON gltrans.tag = tags.tagref AND tags.legalid = '" . $legalid . "'
					INNER JOIN chartmasterxlegal ON gltrans.account = chartmasterxlegal.accountcode
							AND chartmasterxlegal.legalid = '" . $_POST['legalid'] . "'
				SET posted=1 
				WHERE posted = 0 and periodno between '".$minimoperiodo."' and '".$maximoperiodo."' ";

        echo "<br>sql 3: ".$sql."<br>";
        $UpdActualChartDetails = DB_queryNT($sql, $db);*/

        /* Now make all the actuals 0 for all periods including and after the period from */

        $consulta= "SELECT tagref FROM tags WHERE legalid='".$legalid."'";
        $resultado= DB_queryNT($consulta, $db);

        while ($registro= DB_fetch_array($resultado)) {
            $sql = "UPDATE chartdetails 
					INNER JOIN tags ON chartdetails.tagref = tags.tagref AND tags.legalid = '" . $legalid . "' and chartdetails.tagref='".$registro['tagref']."'
				SET chartdetails.actual = 0, 
					chartdetails.bfwd = 0, 
					chartdetails.cargos = 0, 
					chartdetails.abonos = 0 where chartdetails.period between '".$minimoperiodo."' and '".$maximoperiodo."'";

            //echo "<br>sql 4: ".$sql."<br>";
            $UpdActualChartDetails = DB_queryNT($sql, $db);
        }

        $sql = "DELETE FROM RePostGL where period between '".$minimoperiodo."' and '".$maximoperiodo."'";
        //echo "<br>sql delete: ".$sql."<br>";
        $UpdActualChartDetails = DB_queryNT($sql, $db);

        /* REPOSTEA TODAS LAS TRANSACCIONES DEL GLTRANS Y ACUMULALAS EN CHARTDETAILS (desarrollo) CORRE EN SEGUNDOS !!! */
        $sql = "INSERT INTO RePostGL
				SELECT account, periodno,
					tag, sum(amount) as amount,
					sum(CASE WHEN amount >=0 THEN amount ELSE 0 END) as cargos,
					sum(CASE WHEN amount <0 THEN amount*-1 ELSE 0 END) as abonos,
					sum(amount) as actual, 0 as bfwd
				FROM  gltrans 
				INNER JOIN chartmasterxlegal ON gltrans.account = chartmasterxlegal.accountcode
					AND chartmasterxlegal.legalid = '" . $_POST['legalid'] . "'
				INNER JOIN tags ON gltrans.tag = tags.tagref AND tags.legalid = '" . $_POST['legalid'] . "'
				WHERE gltrans.posted = 1 and gltrans.periodno BETWEEN '".$minimoperiodo."' and '".$maximoperiodo."'
				GROUP BY account, periodno, tag
				ORDER BY tag, account, periodno";

        //echo "<br>sql 5: ".$sql."<br>";
        $UpdActualChartDetails = DB_queryNT($sql, $db);

        $sql = "INSERT INTO chartdetails
				SELECT RePostGL.accountcode, RePostGL.period, RePostGL.tagref, 0,0,0,0,0,0
				FROM  RePostGL 
				LEFT JOIN chartdetails ON RePostGL.accountcode = chartdetails.accountcode 
					AND RePostGL.period = chartdetails.period 
					AND RePostGL.tagref = chartdetails.tagref
				WHERE chartdetails.accountcode is null and RePostGL.period between '".$minimoperiodo."' and '".$maximoperiodo."'";

        //echo "<br>sql 6: ".$sql."<br>";
        $UpdActualChartDetails = DB_queryNT($sql, $db);

        $sql = "UPDATE chartdetails 
				JOIN RePostGL as resumen  ON resumen.accountcode = chartdetails.accountcode 
				AND resumen.period = chartdetails.period 
				AND resumen.tagref = chartdetails.tagref
				SET chartdetails.actual = resumen.amount,
					chartdetails.cargos = resumen.cargos,
					chartdetails.abonos = resumen.abonos
				WHERE  chartdetails.period between '".$minimoperiodo."' and '".$maximoperiodo."'";

        //echo "<br>sql 7: ".$sql."<br>";
        $UpdActualChartDetails = DB_queryNT($sql, $db);

        //echo 'max:' . $maximoperiodo;

        $minimoperiodo=$minimoperiodo- 1;

        $consulta= "SELECT max(periodno) as periodo
					FROM periods
					WHERE periodno BETWEEN '".$minimoperiodo."' AND '".intval($minimoperiodo).".9'";

        $PeriodsMM = DB_queryNT($consulta, $db);

        if ($registro= DB_fetch_array($PeriodsMM, $db)) {
            $minimoperiodo= $registro["periodo"];
        }

        //$pidx = $minimoperiodo;
        /*
		$periodos[$minimoperiodo] = $minimoperiodo+1;
		$periodosAnt[$minimoperiodo] =$minimoperiodo;*/

        $sql = "SELECT * 
			FROM periods
			WHERE periodno >= '" . ($minimoperiodo ) . "' and periodno <= '".$maximoperiodo."'
				AND lastdate_in_period < 
				(CASE WHEN MONTH(Now()) = 12 THEN CONCAT(YEAR(Now())+1,'-01-01 00:00:00.000')
					ELSE CONCAT(YEAR(Now()),MONTH(Now())+1, '-01 00:00:00.000')
				END)
	 			ORDER BY periodno";

        //echo "<br>sql 2B: ".$sql."<br>";

        $PeriodsMM = DB_queryNT($sql, $db);
        $pidx = $minimoperiodo;
        $periodoAnterior = $minimoperiodo ;

        while ($myrowMM = DB_fetch_array($PeriodsMM, $db)) {
            $periodos[$pidx] = $myrowMM['periodno'];
            $periodosAnt[$pidx] = $periodoAnterior;
            //echo "P:" . $pidx . ":" . $myrowMM['periodno'] . " ant:" . $periodoAnterior . "<br>";
            $pidx = $pidx + 1;
            $periodoAnterior = $myrowMM['periodno'];
        }

        for ($i = $minimoperiodo; $i <= $pidx; $i++) {
            if (strlen($periodosAnt[$i]) > 0 and strlen($periodos[$i]) > 0 and $periodos[$i]!=$periodosAnt[$i]) {
                $sql = "UPDATE chartdetails c1 
							INNER JOIN chartmasterxlegal ON c1.accountcode = chartmasterxlegal.accountcode
								AND chartmasterxlegal.legalid = '" . $_POST['legalid'] . "'
							INNER JOIN tags ON c1.tagref = tags.tagref AND tags.legalid = '" . $_POST['legalid'] . "'
							LEFT JOIN chartdetails c2 on c1.accountcode = c2.accountcode 
								AND c1.tagref = c2.tagref
						SET c1.bfwd = (c2.bfwd + c2.actual)
						WHERE  c1.period=" . $periodos[$i] . " 
							AND c2.period = " . $periodosAnt[$i] . "
							AND c2.accountcode is not null and c1.period between '".$minimoperiodo."' and '".$maximoperiodo."'";
                //echo "<br>sql 8: ".$sql."<br>";
                $result = DB_queryNT($sql, $db);
                //echo "periodo...:" . "<pre>" . $sql . "<BR>" . $periodos[$i] ." ".$periodosAnt[$i]. "<BR>";
            }
        }

        echo "<br><BR><B>REPOSTEO FINALIZO...</B>";
    }
}

echo '<form method="POST" action="' . $_SERVER ['PHP_SELF'] . '?' . SID . '">';
    echo '<table>';
        echo '<tr>';
            echo '<td>' . _('Seleccione Una Razon Social:') . '</td>';
            echo '<td>';
                echo '<select name="legalid">';
                    // $SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname
                    // FROM sec_unegsxuser u,tags t
                    // JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid
                    // WHERE u.tagref = t.tagref
                    // and u.userid = '" . $_SESSION ['UserID'] . "'
                    // GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY t.tagref";
                    $SQL = "SELECT distinct legalbusinessunit.legalid,legalbusinessunit.legalname
                    FROM sec_unegsxuser u,tags t 
                    JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid
                    WHERE u.tagref = t.tagref and u.userid = '" . $_SESSION ['UserID'] . "'
                    -- GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname 
                    ORDER BY legalbusinessunit.legalid";
                    $result = DB_query($SQL, $db);

while ($myrow = DB_fetch_array($result)) {
    if (isset($_POST ['legalid']) and $_POST ['legalid'] == $myrow ["legalid"]) {
        echo '<option selected value=' . $myrow ['legalid'] . '>' . $myrow ['legalid'] . ' - ' . $myrow ['legalname'];
    } else {
        echo '<option value=' . $myrow ['legalid'] . '>' . $myrow ['legalid'] . ' - ' . $myrow ['legalname'];
    }
}
                echo '</select>';
            echo '</td>';
        echo '</tr>';

        echo '<tr>';
            echo '<td>' . _('Desde Periodo:') . '</td>';
            echo '<td>';
                echo '<select name="cmbPedriodInicio">';
                        $sql     = 'SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC';
                        $Periods = DB_query($sql, $db);

while ($myrow = DB_fetch_array($Periods, $db)) {
    /*if (strpos($myrow['periodno'], '.5') > 0) {
        $nombreperiodo = NombreCierreAnual($myrow['lastdate_in_period']);
    } else {
        $nombreperiodo = MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
    }*/

    $nombreperiodo= fnGetMes(date("m", strtotime($myrow['lastdate_in_period'])))."-".date("Y", strtotime($myrow['lastdate_in_period']));
                            
    if (isset($_POST['FromPeriod']) and $_POST['FromPeriod'] != '') {
        if ($_POST['FromPeriod'] == $myrow['periodno']) {
            echo '<option selected VALUE=' . $myrow['periodno'] . '>' . $nombreperiodo;
        } else {
            echo '<option VALUE=' . $myrow['periodno'] . '>' . $nombreperiodo;
        }
    } else {
        if ($myrow['lastdate_in_period'] == $DefaultFromDate) {
            echo '<option selected VALUE=' . $myrow['periodno'] . '>' . $nombreperiodo;
        } else {
            echo '<option VALUE=' . $myrow['periodno'] . '>' . $nombreperiodo;
        }
    }
}
                echo '</select>';
            echo '</td>';
        echo '</tr>';

        echo '<tr>';
            echo '<td>' . _('Hasta Periodo:') . '</td>';
            echo '<td >';
                echo '<select name="cmbPedriodFinal">';
                    $RetResult = DB_data_seek($Periods, 0);

while ($myrow = DB_fetch_array($Periods, $db)) {
    /*if (strpos($myrow['periodno'], '.5') > 0) {
        $nombreperiodo = NombreCierreAnual($myrow['lastdate_in_period']);
    } else {
        $nombreperiodo = MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
    }*/

    $nombreperiodo= fnGetMes(date("m", strtotime($myrow['lastdate_in_period'])))."-".date("Y", strtotime($myrow['lastdate_in_period']));

        //."-".date("Y", strtotime($myrow['lastdate_in_period'])));

    if ($myrow['periodno'] == $DefaultToPeriod) {
        echo '<option selected VALUE=' . $myrow['periodno'] . '>' . $nombreperiodo;
    } else {
        echo '<option VALUE =' . $myrow['periodno'] . '>' . $nombreperiodo;
    }
}
                echo '</select>';
            echo '</td>';
        echo '</tr>';

        echo '<tr>';
            echo '<td colspan="2" style="text-align:center">';
                echo '<input type="checkbox" name="chkReposteoNormal" value="1"> Reposteo Normal';
            echo '</td>';
        echo '</tr>';


        echo '<tr>';
            echo '<td colspan="2" style="text-align:center">';
                echo '<input type="submit" Name="reposteo" Value="' . _('Repostear') . '">';
            echo '</td>';
        echo '</tr>';
        echo '<tr>';
            echo '<td colspan="2" style="text-align:left">';
                echo '<a href="Z_InsertaCuentasChartdetailsxRazonSocial.php" target="_blank">';
                    echo "INSERTAR CUENTAS EN CHARTDETAILS";
                echo "</a>";
            echo '</td>';
        echo '</tr>';
    echo '</table>';
echo '</form>';

include 'includes/footer_Index.inc';
