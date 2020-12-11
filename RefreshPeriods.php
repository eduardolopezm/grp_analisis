<?php
/**
 * Pagina de mantenimiento de periodos
 *
 * @category ABC
 * @package ap_grp
 * @author Armando Barrientos Martinez <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 02/08/2017
 * Fecha Modificación: 21/08/2017
 */
include('includes/session.inc');
$title = _('Mantenimiento Periodos');

include('includes/header.inc');

$funcion = 246;
include('includes/SecurityFunctions.inc');

$_POST['year'] = (isset($_POST['year'])) ? $_POST['year']:"";



echo "<form action='" . $_SERVER ['PHP_SELF'] . '?' . SID . "' name='FDatosB' method='post'>";

//echo '<p class="page_title_text">' . ' ' . _('Mantenimiento Periodos') . '</p>';
echo '<div class="panel panel-default">
            <div class="panel-heading mb10" role="tab" id="headingOne">
              <h4 class="panel-title row">
                <div class="col-md-6 col-xs-6 text-left">
                  <a role="button" data-toggle="collapse" data-parent="#accordion" href="#idMantenimientoPeriodos" aria-expanded="true" aria-controls="collapseOne">
                    <b>'. _('Mantenimiento Periodos') .'</b>
                  </a>
                </div>
              </h4>
            </div>';
    echo '<div id="idMantenimientoPeriodos" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">';

echo '<table>
	<tr>
	    <td><span class="generalSpan mr5">'.("Año").'</span></td>
	    <td><select class="form-control mb10" tabindex="4" name="year">';
$SQL = "select distinct year (lastdate_in_period) as year
		    from periods";
$result = DB_query($SQL, $db);

while ($myrowyear = DB_fetch_array($result)) {
    if (intval($myrowyear ['year']) == intval($_POST ['year'])) {
        echo '<option selected VALUE="' . $myrowyear ['year'] . '">' . $myrowyear ['year'] . "</option>";
    } else {
        echo '<option  VALUE="' . $myrowyear ['year'] . '">' . $myrowyear ['year'] . "</option>";
    }
}
echo '</select></td>';

echo '</table>';
echo '<br><div align="center">
<component-button type="submit" id="Search" name="Search" class="glyphicon glyphicon-search" value="Filtrar"></component-button>
<br><br>
</div></div></div><br>';
// <input class='botonVerde' tabindex='6' type='submit' name='Search' value='" . _('Buscar') . "'>

// ACTUALIZA LA BD DE ACUERDO A LOS CHECKBOX SELECCIONADOS O DESSELCCIONADOS
if (isset($_POST ['Refresh'])) {
    $SQL = "SELECT legalbusinessunit.legalname,
		    legalbusinessunit.legalid,
		    year (lastdate_in_period) as year,
		    p.periodno
        FROM legalbusinessunit
        left JOIN periodsXlegal l  ON legalbusinessunit.legalid = l.legalid
	left JOIN periods p ON  l.periodno = p.periodno
	WHERE year(lastdate_in_period)='" . $_POST ['year'] . "'
	ORDER BY legalbusinessunit.legalid,p.periodno";
    
    $SQL = "SELECT legalbusinessunit.legalname,
		   legalbusinessunit.legalid,
		   p.periodno,
		   year (lastdate_in_period) as year,
		   status
        FROM legalbusinessunit
        cross JOIN periods p 
	left JOIN periodsXlegal l  ON legalbusinessunit.legalid = l.legalid
	AND l.periodno = p.periodno
	
	WHERE year(lastdate_in_period)='" . $_POST ['year'] . "'
	ORDER BY legalbusinessunit.legalid,p.periodno";
    // echo "<br>" . $SQL;
    $Result = DB_query($SQL, $db);
    
    while ($myrow = DB_fetch_array($Result)) {
        // echo "<br>VAL: " . 'periodo_' . $myrow['legalid'] . "_" . number_format($myrow['periodno'],0);
        // echo "<br>VAL: " . $_POST['periodo_' . $myrow['legalid'] . "_" . number_format($myrow['periodno'],0)];
        if (isset($_POST['periodo_' . $myrow ['legalid'] . "_" . number_format($myrow ['periodno'], 0)]) && $_POST ['periodo_' . $myrow ['legalid'] . "_" . number_format($myrow ['periodno'], 0)] != "") {
            $seleccionado = 1;
        } else {
            $seleccionado = 0;
        }
        $_POST ['com_periodo_' . $myrow ['legalid'] . "_" . number_format($myrow ['periodno'], 0)] = (isset($_POST ['com_periodo_' . $myrow ['legalid'] . "_" . number_format($myrow ['periodno'], 0)])) ? $_POST ['com_periodo_' . $myrow ['legalid'] . "_" . number_format($myrow ['periodno'], 0)]:"";

        $original =$_POST ['com_periodo_' . $myrow ['legalid'] . "_" . number_format($myrow ['periodno'], 0)];
        // echo "<br>SEL: " . $seleccionado;
        
        $sql = "SELECT legalid,
			periodno
		FROM	periodsXlegal
		WHERE	legalid = " . $myrow ['legalid'] . "
		AND periodno = " . $myrow ['periodno'];
        $consult = DB_query($sql, $db);
        
        // echo $sql;
        
        if (DB_num_rows($consult) == 0) {
            $SQL = "INSERT INTO periodsXlegal
				(
				legalid,
				periodno,
				status
				)
			    VALUES
				(
				" . $myrow ['legalid'] . ",
				" . $myrow ['periodno'] . ",
				$seleccionado
				)";
            $result = DB_query($SQL, $db);
                        
                        $SQL2 =  "INSERT INTO periodsXlegal_log (fecha, usuario, operacion,periodno,legalid) VALUES ('".date('Y-m-d H:i:s')."', '".$_SESSION['UserID']."', 'legalid=".$myrow ['legalid']."/ periodo=".$myrow ['periodno']."/cambio a ".$seleccionado."','".$myrow ['periodno']."','".$myrow ['legalid']."')";
                        $result = DB_query($SQL2, $db);
                        
            // echo $SQL;
        } else {
            if ($original!=$seleccionado) {
                $SQL = "UPDATE periodsXlegal
                            SET status=" . $seleccionado . "
                            WHERE legalid = " . $myrow ['legalid'] . "
                            AND periodno = " . $myrow ['periodno'];
            
                $result = DB_query($SQL, $db);
            // echo $SQL;
                $SQL2 =  "INSERT INTO periodsXlegal_log (fecha, usuario, operacion,periodno,legalid) VALUES ('".date('Y-m-d H:i:s')."', '".$_SESSION['UserID']."', 'legalid=".$myrow ['legalid']."/ periodo=".$myrow ['periodno']."/cambio a ".$seleccionado."','".$myrow ['periodno']."','".$myrow ['legalid']."')";
                $result = DB_query($SQL2, $db);
            }
        }
    }
}

if (isset($_POST ['Search']) or isset($_POST ['Refresh'])) {
    $SQL = "SELECT year (lastdate_in_period) as year,
	    lastdate_in_period,
    	MONTH(lastdate_in_period) as mes,
	    periodno
	    FROM periods
	    WHERE year(lastdate_in_period)='" . $_POST ['year'] . "'";
    
    $result = DB_query($SQL, $db);
    
    $SQL = "SELECT legalbusinessunit.legalname,
		   legalbusinessunit.legalid,
		   p.periodno,
		   year (lastdate_in_period) as year,
		   status
        FROM legalbusinessunit
        cross JOIN periods p 
	left JOIN periodsXlegal l  ON legalbusinessunit.legalid = l.legalid and l.periodno = p.periodno
	
	WHERE year(lastdate_in_period)='" . $_POST ['year'] . "'
	ORDER BY legalbusinessunit.legalid,p.periodno";
    
    $Result = DB_query($SQL, $db);
    
    // //////////////////////
    // IMPRIME ENCABEZADOS//
    // ////////////////////
    echo "<table border=1 align='center' class='tableHeaderVerde'><tr><th> Dependencia </th>";
    
    while ($myrow1 = DB_fetch_array($result)) {
        $months = array (
                '1' => 'ENE',
                '2' => 'FEB',
                '3' => 'MAR',
                '4' => 'ABR',
                '5' => 'MAY',
                '6' => 'JUN',
                '7' => 'JUL',
                '8' => 'AGO',
                '9' => 'SEP',
                '10' => 'OCT',
                '11' => 'NOV',
                '12' => 'DEC'
        );
        $month = implode('<br />', str_split($months [$myrow1 ['mes']])) . "<br /><br />";
        echo "<th><a title='" . $myrow1 ['lastdate_in_period'] . "'>" . $month . number_format($myrow1 ['periodno'], 0) . "</a> </th></td>";
    }
    // ////////////////////////////
    // FIN IMPRESION ENCABEZADOS//
    // //////////////////////////
    
    $AntPeriod = '';
    $AntLegal = '';
    
    // ////////////////////////////////////////
    // IMPRIME TABLA CON U DE NEG Y CHECKBOX//
    // //////////////////////////////////////
    while ($myrow = DB_fetch_array($Result)) {
        if ($AntLegal != $myrow ['legalname']) {
            echo "<tr><td align=left;'>" . $myrow ['legalname'] . "</td>";
            $AntLegal = $myrow ['legalname'];
        }
        
        // echo "<td align=left;'>";
        if ($myrow ['status'] == 1) {
            echo "<td bgcolor='#04B404' align=left;'>"
                    . "<input type='checkbox' checked name='periodo_" . $myrow ['legalid'] . "_" . number_format($myrow ['periodno'], 0) . "' value='" . $myrow ['legalid'] . "_" . number_format($myrow ['periodno'], 0) . "'>"
                                . "<input type='hidden' checked name='com_periodo_" . $myrow ['legalid'] . "_" . number_format($myrow ['periodno'], 0) . "' value='1'></td>";
        } else {
            echo "<td bgcolor='yellow' align=left;'><input type='checkbox' name='periodo_" . $myrow ['legalid'] . "_" . number_format($myrow ['periodno'], 0) . "' value='" . $myrow ['legalid'] . "_" . number_format($myrow ['periodno'], 0) . "'></td>"
                                 . "<input type='hidden' checked name='com_periodo_" . $myrow ['legalid'] . "_" . number_format($myrow ['periodno'], 0) . "' value='0'></td>";
        }
    }
    
    echo "</table>";
    // ////////////
    // FIN TABLA//
    // //////////
    echo "<br><div class='centre'><input class='botonVerde' tabindex='6' type='submit' name='Refresh' value='" . _('Actualiza') . "'></div><br>";
}

echo "</form>";
echo "<br><br>";

include('includes/footer_Index.inc');
