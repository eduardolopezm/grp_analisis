<?php
/**
 * Modificación de Póliza Manual
 *
 * @category ABC
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 06/11/2017
 * Fecha Modificación: 06/11/2017
 * Modificación de Póliza Manual
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');
// 
include('includes/DefineJournalClass.php');
$PageSecurity = 10;
include('includes/session.inc');
$funcion = 105;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
include('Jquery.inc');
$title = _('Modificación de Póliza Contable');
include('includes/header.inc');
include "includes/SecurityUrl.php";

if (isset($_GET ['typeno'])) {
    $typeno = $_GET ['typeno'];
} else if (isset($_POST ['typeno'])) {
    $typeno = $_POST ['typeno'];
} else {
    $typeno = 0;
}

if (isset($_GET ['type'])) {
    $type = $_GET ['type'];
} else if (isset($_POST ['type'])) {
    $type = $_POST ['type'];
} else {
    $type = 0;
}

if (isset($_GET ['tag'])) {
    $tag = $_GET ['tag'];
} else if (isset($_POST ['tag'])) {
    $tag = $_POST ['tag'];
} else {
    $tag = 0;
}

$tienePermisoModificar = Havepermission($_SESSION ['UserID'], 971, $db);
$perdespolizasSfecha = Havepermission($_SESSION['UserID'], 1631, $db);

// Condicion que entra cuando se da click sobre el boton Procesar
if (isset($_POST ['Process'])) {
    $x = $_POST ['totalnarrative'];  // numero de elementos a modificar en la pantalla
    
    // Recorrer registros mostrados y modificados por el usuario
    for ($i = 1; $i <= $x; $i ++) {
        $narrative = $_POST ['nom' . $i];
        $counterindex = $_POST ['counterindex' . $i];
        $amountGltrans = $_POST ['amount' . $i];
        $accountGltrans = $_POST ['gl' . $i];
        $Selcounter = $_POST ['Selcounter' . $i];
                
        $PeriodNo = GetPeriod($_POST['trandate'], $db, $_POST['tagref']);
        $fechapol = FormatDateForSQL($_POST['trandate']);
        $query = "SELECT 1 FROM chartmaster WHERE accountcode = '".$accountGltrans."'";


        $rs= DB_query($query, $db);
        
        if ($row = DB_fetch_array($rs)) {
            $query = "SELECT amount, userid FROM gltrans WHERE counterindex='" . $counterindex."'";
            $rsTmp = DB_query($query, $db);

            if ($rowTmp = DB_fetch_array($rsTmp)) {
                if ($rowTmp ['amount'] < 0) {
                    $amountGltrans = $amountGltrans * - 1;
                }
                $usuario_creo= $rowTmp['userid'];
                
                // Si el usuario esta vacio en la poliza se vuelve a traer
                if (empty($usuario_creo)) {
                    $consulta= "SELECT userid FROM gltrans_user WHERE id='".$counterindex."'";
                    $resultado= DB_query($consulta, $db);
                    
                    if ($registro= DB_fetch_array($resultado)) {
                        $usuario_creo= $registro["userid"];
                    }
                }
            }
            
            // ECHO 'VAL='.$Selcounter;
            if ($Selcounter == true) {
                $sql = "DELETE FROM  gltrans
					    WHERE counterindex='" . $counterindex . "'";
                // $result = DB_query($sql,$db,$ErrMsg);
                $result = DB_query($sql, $db, $ErrMsg);
            } else {
                    // Actualizar tabla de polizas con los datos capturados en pantalla
                    $sql = "UPDATE gltrans
						    SET narrative='" . $narrative . "',
								amount='$amountGltrans',
								account='$accountGltrans',
								userid= '".$usuario_creo."',
								lastusermod = '" . $_SESSION['UserID'] . "',
								lastdatemod = Now()
							WHERE counterindex='" . $counterindex . "'";
                    $rs = DB_query($sql, $db);
                if ($perdespolizasSfecha == 1) {
                    $sql = "UPDATE gltrans
						SET periodno = '".$PeriodNo."',
							trandate = '".$fechapol."'
						WHERE counterindex= '" . $counterindex . "'";
                    $rs = DB_query($sql, $db);
                }
                
                // buscar usuario que origino la poliza
                $userorig = "";
                
                $qry = "SELECT distinct useridorig 
						FROM logmodificapolizas
						WHERE type = '" . $type . "'
						and typeno = '" . $typeno . "'";
                $rs = DB_query($qry, $db);

                if (Db_num_rows($rs) > 0) {
                    $reg = DB_fetch_array($rs);
                    $usuario_creo = $reg ['useridorig'];
                }
                
                $sql = "INSERT INTO logmodificapolizas(type,
													typeno,
													trandate,
													periodno,
													account,
													narrative,
													amount,
													tag,
													userid,
													origtrandate,
													comentarios,
													useridorig)
									SELECT type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount,
											tag,
											'" . $_SESSION ['UserID'] . "',
											Now(),
											'Poliza Original',
											'" . $usuario_creo . "'
									FROM gltrans
									WHERE typeno = " . $typeno . "
										AND type = " . $type;
                $ErrMsg = _('No pude insertar el movimiento en contabilidad porque');
                $DbgMsg = _('El SQL que fallo para insertar el movimiento contable fue');
                $result = DB_query($sql, $db, $ErrMsg, $DbgMsg, true);
            }
        } else {
            prnMsg(_('La cuenta') . ' ' . $accountGltrans . ' ' . _('no existe en chartmaster!'), 'error');
        }
    }
    
    prnMsg(_('Se han procesado los cambios en las pólizas contables'), 'info');
}
echo "<form method='post' action=" . $_SERVER ['PHP_SELF'] . "?" . SID . " name=f1>";
//echo '<p class="page_title_text">' . ' ' . _('MODIFICACION DE CONCEPTO EN POLIZAS CONTABLES') . '</p>';

echo '<p class="page_title_text">' . ' ' . _(' Número de Póliza: ') . $typeno . '</p>';

// _('Accept')
$Sql = "SELECT type, gltrans.typeno, gltrans.tag,account, narrative,amount,tag,counterindex,typename, DATE_FORMAT(gltrans.trandate, '%d/%m/%Y') AS trandate
		from gltrans left join systypescat on gltrans.type=systypescat.typeid
		WHERE gltrans.typeno= '" . $typeno . "'
		AND type= '" . $type . "' 
		AND tag= '" . $tag . "' ";



$Result = DB_query($Sql, $db, $ErrMsg);

$x = 0;
echo '<br><table class="table table-hover">';

echo "<tr class='header-verde'>
            <th>" . _('Eliminar') . "</th>
            <th>" . _('UR') . "</th>
            <th>" . _('Tipo Póliza') . "</th>
            <th>" . _('Cuenta') . "</th>
            <th>" . _('Cargo') . "</th>
            <th>" . _('Abono') . "</th>
            <th>" . _('Concepto') . '</th>
        </tr>';

while ($myrow = DB_fetch_array($Result)) {
    if ($x == 0) {
        if ($perdespolizasSfecha == 1) {
            echo "<tr>";
            echo "<td>"._('Fecha')."</td>";
            
            if ($myrow['trandate'] = '00/00/0000') {
                $trandate = date("d/m/Y");
            } else {
                $trandate = $myrow['trandate'];
            }
            echo "<td colspan=6>";
            echo '<input type=text enabled=true onchange="selectitems()" class="datepickerRECH" name="trandate" id="trandate" value="'.$trandate.'">';
            echo "</td>";
            echo "</tr>";
        } else {
            echo "<tr style='display: none;'>";
            echo "<td>"._('Fecha')."</td>";
            echo "<td colspan=6>";
            echo '<input type=text enabled=true onchange="selectitems()" class="datepickerRECH" name="trandate" id="trandate" value="'.$myrow['trandate'].'">';
            echo "</td>";
            echo "</tr>";
        }
    }

    $x = $x + 1;
    
    $debittotal = 0;
    $credittotal = 0;
    $j = 0;
    
    echo '<tr>';
    $sql = 'SELECT tagdescription ' . 'FROM tags ' . 'WHERE tagref=' . $myrow ['tag'];
    $result = DB_query($sql, $db);
    $myrow2 = DB_fetch_row($result);
    if ($myrow ['tag'] == 0) {
        $tagdescription = 'None';
    } else {
        $tagdescription = $myrow2 [0];
    }
    if ($tienePermisoModificar) {
        echo '<td><b><font size=1 ><input type=checkbox name="Selcounter' . $x . '"></td>';
    } else {
        echo "<td></td>";
    }
    echo "<td>" . $tagdescription . "</td>";
    echo "<td>" . $myrow ['typename'] . "</td>";
    
    if ($tienePermisoModificar) {
        echo "<td>";
        //echo "<input name=gl" . $x . " type='text' value='" . $myrow ['account'] . "' />";
        echo '<component-text id="gl'.$x.'" name="gl'.$x.'" placeholder="Cuenta" title="Cuenta" value="'.$myrow ['account'].'"></component-text>';
        echo "</td>";
    } else {
        echo "<td>" . $myrow ['account'];
        echo "<input name=gl" . $x . " type='hidden' value='" . $myrow ['account'] . "' />";
        echo "</td>";
    }
    
    if ($myrow ['amount'] >= 0) {
        if ($tienePermisoModificar) {
            echo "<td>";
            //echo "<input name=amount" . $x . " type='text' value='" . $myrow ['amount'] . "' />";
            echo '<component-number id="amount'.$x.'" name="amount'.$x.'" placeholder="Cargo" title="Cargo" value="'.$myrow ['amount'].'"></component-number>';
            echo "</td><td></td>";
        } else {
            echo "<td class='number'>" . number_format($myrow ['amount'], 2);
            echo "<input name=amount" . $x . " type='hidden' value='" . ($myrow ['amount']) . "' />";
            echo '</td>';
            echo '<td></td>';
        }
        $debittotal = $debittotal + $myrow ['amount'];
    } elseif ($myrow ['amount'] < 0) {
        if ($tienePermisoModificar) {
            echo "<td></td><td>";
            //echo "<input name=amount" . $x . " type='text' value='" . ($myrow ['amount'] * - 1) . "' />";
            echo '<component-number id="amount'.$x.'" name="amount'.$x.'" placeholder="Abono" title="Abono" value="'.($myrow ['amount'] * - 1).'"></component-number>';
            echo "</td>";
        } else {
            $credit = (- 1 * $myrow ['amount']);
            echo "<td></td><td class='number'>" . number_format($credit, 2);
            echo "<input name=amount" . $x . " type='hidden' value='" . ($myrow ['amount'] * - 1) . "' />";
            echo '</td>';
        }
        $credittotal = $credittotal + $credit;
    }
    
    echo ' <td><textarea name="nom' . $x . '" cols=50 rows=2 class="form-control" >' . $myrow ['narrative'] . '</textarea>';
    echo ' <input type="hidden" name="counterindex' . $x . '" value="' . $myrow ['counterindex'] . '"></td>';
    echo '</tr>';
}
echo '</table>';

echo '<table>';
echo "<input type=hidden name=typeno  VALUE='" . $typeno . "'>";
echo "<input type=hidden name=type  VALUE='" . $type . "'>";
echo "<input type=hidden name=totalnarrative  VALUE='" . $x . "'>";
echo "<input type=hidden name=tagref  VALUE='" . $tag . "'>";
echo "<br><tr><div class='centre'>";
//echo "<input type=submit name='Process' value='" . _('Procesar') . "'>";
echo '<component-button type="submit" id="Process" name="Process" value="Procesar" class=""></component-button>';
echo "</div><br><hr><br></tr>";
echo '</table>';

echo '</form>';

include 'includes/footer_Index.inc';
?>
<script>
    
    function selectitems(){

       for (i=0;i<document.f1.elements.length;i++) 
        if(document.f1.elements[i].type == "checkbox"){ 
           document.f1.elements[i].checked=1 
        }
      }
    
</script>
