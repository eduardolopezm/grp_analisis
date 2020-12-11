<?php
/**
 * Movimientos de Bienes y Servicios
 *
 * @category Panel
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 31/10/2017
 * Fecha Modificación: 31/10/2017
 * Vista para los Movimientos de Bienes y Servicios
 */

// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// error_reporting(E_ALL);
// ini_set('error_log', dirname(__FILE__) . '/error_log.txt');

$PageSecurity = 5;
$funcion = 57;
include "includes/SecurityUrl.php";
include 'includes/session.inc';
$title = traeNombreFuncion($funcion, $db);
if (!isset($_POST['excel'])) {
    include 'includes/header.inc';
}
include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';
?>
<link rel="stylesheet" href="css/listabusqueda.css" />
<?php

if (isset($_GET['StockID'])) {
    $StockID = trim(strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])) {
    $StockID = trim(strtoupper($_POST['StockID']));
} else {
    $StockID = '';
}

if (isset($_GET['Barcode'])) {
    $Barcode = trim(strtoupper($_GET['Barcode']));
} elseif (isset($_POST['StockID'])) {
    $Barcode = trim(strtoupper($_POST['Barcode']));
} else {
    $Barcode = '';
}

if (!isset($_POST['fechaDesde'])) {
    $_POST['fechaDesde'] = date('d-m-Y');
}

if (!isset($_POST['fechaHasta'])) {
    $_POST['fechaHasta'] = date('d-m-Y');
}

echo '<link href="css/StockMovements.css" rel="stylesheet" type="text/css" />';

if (isset($_POST['excel'])) {
    header("Content-type: application/ms-excel");
    header("Content-Disposition: attachment; filename=Reporte.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
}

$StockIDTemp = $StockID;

if ($StockID<>'') {
    if ($_SESSION['SearchBarcode'] == 1) {
        if (isset($Barcode) and $Barcode <> "") {
            $condStockId = "(stockid='$StockID'
			OR barcode = '$Barcode')";
        } else {
            $condStockId = "stockid='$StockID'";
        }
    } else {
        $condStockId = "stockid='$StockID'";
    }
    
    $result = DB_query("SELECT description, units, stockid FROM stockmaster WHERE $condStockId", $db);
    $myrow = DB_fetch_row($result);
    if (empty($StockID) == false) {
        $StockID = trim(strtoupper($myrow[2]));
    }
}

if (isset($_POST['si'])) {
    $StockID = $_POST['StockID'];
    $loccode = $_POST['loccode'];
    $moveqty = $_POST['moveqty'];
    $serialmoveqty = $_POST['serialmoveqty'];
    $stkmoveno  = $_POST['stkmoveno'];
    $stkitmmoveno = $_POST['stkitmmoveno'];
    $serialno = $_POST['serialno'];
    
    if ($moveqty <> $serialmoveqty) {
        if (($moveqty < 0) and ($serialmoveqty>0)) {
            $serialmoveqty = -1 * $serialmoveqty;
        }
    }
    
    /*
	echo "<br>stockid: " .  $StockID;
	echo "<br>loccode: " .  $loccode;
	echo "<br>moveqty: " .  $moveqty;
	echo "<br>serialmoveqty: " .  $serialmoveqty;
	echo "<br>stkmoveno: " .  $stkmoveno;
	echo "<br>stkitmmoveno: " .  $stkitmmoveno;
	echo "<br>serialno: " .  $serialno;//
	*/
    
    $usql = "update locstock
	set quantity = quantity + " . ((-1) * $moveqty)  . "
	where stockid = '" . $StockID . "' and loccode='" . $loccode . "'";
    //echo "<br>" . $usql;
    $xresult = DB_query($usql, $db);
    
    $usql = "update stockserialitems set quantity = quantity + " . ((-1) * $serialmoveqty)  . "
	where stockid = '" . $StockID . "' and loccode='" . $loccode . "' and serialno = '" . $serialno . "'";
    //echo "<br>" . $usql;
    $xresult = DB_query($usql, $db);
    
    
    $dsql = "delete from stockmoves where stkmoveno='" .  $stkmoveno . "'";
    //echo "<br>" . $dsql;
    $xresult = DB_query($dsql, $db);
    
    $dsql = "delete from stockserialmoves where stkitmmoveno='" .  $stkitmmoveno . "'";
    //echo "<br>" . $dsql;
    $xresult = DB_query($dsql, $db);
    
    unset($loccode);
    unset($moveqty);
    unset($serialmoveqty);
    unset($stkmoveno);
    unset($stkitmmoveno);
    unset($serialno);
}

if ((isset($_GET['operation'])) and ($_GET['operation'] = 'eliminar')) {
    $stkmoveno  = $_GET['stkmoveno'];
    
    $msql = "SELECT m.stockid, m.loccode, c.typename, m.qty, m.reference,
		IFNULL(s.serialno,'') AS serialno,l.locationname,
		s.stkitmmoveno,
		m.qty as moveqty,
		s.moveqty as serialmoveqty
	FROM stockmoves m
		left join stockserialmoves s on m.stkmoveno = s.stockmoveno
		left join systypescat c on m.type = c.typeid
		left join locations l on m.loccode = l.loccode
	WHERE m.stkmoveno = '" . $stkmoveno . "'";
        $mresult = DB_query($msql, $db);
    if ($mmyrow=DB_fetch_array($mresult)) {
        echo "<form name='form1' method='post' action='" . $_SERVER['PHP_SELF'] . "'>";
        echo "<table border='1' cellpadding='2' cellspacing='1' class='tableHeaderVerde w50p'>";
        echo "<tr><th colspan='2'>" . _('DESEAS ELIMIAR ESTE MOVIMIENTO') .  "?</th>";
        echo "<tr><td>" . _('ALMACÉN') .  ": </td>";
        echo "<td>" . $mmyrow['locationname'] .  "</td></tr>";
        echo "<tr><td>" . _('PRODUCTO') .  ": </td>";
        echo "<td>" . $mmyrow['stockid'] .  "</td></tr>";
        echo "<tr><td>" . _('SERIE') .  ": </td>";
        echo "<td>" . $mmyrow['serialno'] .  "</td></tr>";
        echo "<tr><td>" . _('MOVIMIENTO') .  ": </td>";
        echo "<td>" . $mmyrow['typename'] .  "</td></tr>";

        echo "<tr>";
        echo "<td style='text-align:center'>";
        echo "<input class='botonVerde' type='submit' name='si' value='SI'>";
        echo "<input type='hidden' name='StockID' VALUE='" . $mmyrow['stockid'] . "'>";
        echo "<input type='hidden' name='loccode' VALUE='" . $mmyrow['loccode'] . "'>";
        echo "<input type='hidden' name='moveqty' VALUE='" . $mmyrow['moveqty'] . "'>";
        echo "<input type='hidden' name='serialmoveqty' VALUE='" . $mmyrow['serialmoveqty'] . "'>";
        echo "<input type='hidden' name='stkmoveno' VALUE='" . $stkmoveno . "'>";
        echo "<input type='hidden' name='stkitmmoveno' VALUE='" . $mmyrow['stkitmmoveno'] . "'>";
        echo "<input type='hidden' name='serialno' VALUE='" . $mmyrow['serialno'] . "'>";
        echo "</td>";
        echo "<td style='text-align:center'>";
        echo "<input class='botonVerde' type='submit' name='no' value='NO'>";
        echo "</td>";
        echo "</tr>";

        echo "</tr>";
        echo "</table>";
        echo "</form>";
    }
    exit;
}

if (!isset($_POST['excel'])) {
    if (!isset($_POST['transnofil'])) {
        $_POST['transnofil'] = "*";
    }

    echo "<form action='". $_SERVER['PHP_SELF'] . "?" . SID . "' method=post>";
    ?>
    <div align="left">
      <!--Panel Busqueda-->
      <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingOne">
          <h4 class="panel-title row">
            <div class="col-md-3 col-xs-3">
              <a role="button" data-toggle="collapse" data-parent="#accordion" href="#PanelBusqueda" aria-expanded="true" aria-controls="collapseOne">
                <b>Criterios de filtrado</b>
              </a>
            </div>
          </h4>
        </div>
        <div id="PanelBusqueda" name="PanelBusqueda" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
          <div class="panel-body">
            <div class="row clearfix">
                <div class="col-md-4">
                    <div class="form-inline row">
                      <div class="col-md-3" style="vertical-align: middle;">
                          <span><label>UR: </label></span>
                      </div>
                      <div class="col-md-9">
                          <select id="selectUnidadNegocio" name="selectUnidadNegocio" class="form-control selectUnidadNegocio" onchange="fnCambioUnidadResponsableGeneral('selectUnidadNegocio','selectUnidadEjecutora')">
                            <option value="-1">Seleccionar...</option>
                          </select>
                      </div>
                    </div>
                    <br>
                    <div class="form-inline row">
                      <div class="col-md-3" style="vertical-align: middle;">
                          <span><label>UE: </label></span>
                      </div>
                      <div class="col-md-9">
                          <select id="selectUnidadEjecutora" name="selectUnidadEjecutora" class="form-control selectUnidadEjecutora">
                            <option value="-1">Seleccionar...</option>
                          </select>
                      </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-inline row">
                      <div class="col-md-3" style="vertical-align: middle;">
                          <span><label>Movimientos: </label></span>
                      </div>
                      <div class="col-md-9">
                          <select id="typefil" name="typefil" class="form-control typefil">
                            <option value="*">Seleccionar...</option>
                            <?php
                            $sql = "SELECT systypescat.typeid, systypescat.typename FROM systypescat WHERE nu_inventario_inicial = 1 OR nu_inventario_entrada = 1 OR nu_inventario_salida = 1 ORDER BY typeid ASC";
                            $result = DB_query($sql, $db);
                            while ($myrow = DB_fetch_array($result)) {
                                if ($_POST['typefil'] == $myrow['typeid']) {
                                    echo "<option selected value='".$myrow['typeid']."'>".$myrow['typeid']."-".$myrow['typename']."</option>";
                                } else {
                                    echo "<option value='".$myrow['typeid']."'>".$myrow['typeid']."-".$myrow['typename']."</option>";
                                }
                            }
                            ?>
                          </select>
                      </div>
                    </div>
                    <br>
                    <div class="form-inline row">
                      <div class="col-md-3" style="vertical-align: middle;">
                          <span><label>Almacén: </label></span>
                      </div>
                      <div class="col-md-9">
                          <select id="StockLocation" name="StockLocation" class="form-control StockLocation">
                            <option value="-1">Seleccionar...</option>
                            <?php
                            $SQL = "SELECT locations.loccode, CONCAT(locations.loccode,' - ',locations.locationname) as locationname
                            FROM locations, sec_loccxusser
                            WHERE locations.loccode=sec_loccxusser.loccode AND sec_loccxusser.userid='" . $_SESSION['UserID'] . "'
                            ORDER BY locationname";
                            $resultStkLocs = DB_query($SQL, $db);
                            while ($myrow=DB_fetch_array($resultStkLocs)) {
                                if ($myrow['loccode'] == $_POST['StockLocation']) {
                                     echo "<option selected VALUE='" . $myrow['loccode'] . "'>" . $myrow['locationname'] . "</option>";
                                } else {
                                     echo "<option VALUE='" . $myrow['loccode'] . "'>" . $myrow['locationname'] . "</option>";
                                }
                            }
                            ?>
                          </select>
                      </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <component-date-label label='Desde: ' id='fechaDesde' name='fechaDesde' placeholder='Fecha Desde' value='<?php echo $_POST["fechaDesde"]; ?>'></component-date-label>
                    <br>
                    <component-date-label label='Hasta: ' id='fechaHasta' name='fechaHasta' placeholder='Fecha Hasta' value='<?php echo $_POST["fechaHasta"]; ?>'></component-date-label>
                </div>
            </div>
            <br>
            <div class="row clearfix">
                <div class="col-md-4">
                    <component-text-label label="Clave:" id="StockID" name="StockID" placeholder="Clave" title="Clave" value="<?php echo $StockID; ?>"></component-text-label>
                </div>
                <div class="col-md-4">
                    <component-text-label label="Folio:" id="transnofil" name="transnofil" placeholder="Folio" title="Folio" value="<?php echo $_POST['transnofil']; ?>"></component-text-label>
                </div>
                <div class="col-md-4">
                </div>
            </div>
            <div class="row"></div>
            <div align="center">
              <br>
              <component-button type="submit" id="ShowMoves" name="ShowMoves" class="glyphicon glyphicon-search" value="Filtrar"></component-button>
              <component-button type="submit" id="excel" name="excel" class="glyphicon glyphicon-th" value="Exportar Excel"></component-button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php
    echo '<div class="w50p fl text-right pb5" style="display: none;">Partida';
    echo '<select id="idpartidacalculada" class="formatSelectStockMoves form-control" name="idpartidacalculada">';
    $SQLExtraStockM = "SELECT partidacalculada, ccap FROM tb_cat_partidaspresupuestales_partidaespecifica WHERE ccap in (2,3) ";
    $resultExtraStockM = DB_query($SQLExtraStockM, $db);
    while ($myrowExtraStockM=DB_fetch_array($resultExtraStockM)) {
        echo "<option VALUE='" . $myrowExtraStockM['partidacalculada'] . "'>" . $myrowExtraStockM['partidacalculada'] . "</option>";
    }
    echo '</select>';
    echo '</div>';
    if ($StockID <> '') {
        if ($_POST['SerialItem'] == '') {
            $_POST['SerialItem'] = "0";
        }
        echo "<div class='w50p fl' style='display: none;'>Número de Serie<select id='idSerialItem' class='text-left formatSelectStockMoves form-control' name='SerialItem'> ";
        $sql = 'SELECT serialno, count(*) FROM stockserialitems where stockserialitems.stockid = "'.$StockID.'" Group By serialno Order by serialno';
        $resultStkLocs = DB_query($sql, $db);
        echo "<option VALUE='0' selected>todos los numeros de serie...</option>";
        while ($myrow=DB_fetch_array($resultStkLocs)) {
            if ($myrow['serialno'] == $_POST['SerialItem']) {
                echo "<option selected value='" . $myrow['serialno'] . "'>" . $myrow['serialno'] . "</option>";
            } else {
                echo "<option value='" . $myrow['serialno'] . "'>" . $myrow['serialno'] . "</option>";
            }
        }
        echo '</select></div>';
    }

    echo '</form>';
}

echo '<div class="row"></div>';

if (!isset($_POST['BeforeDate']) or !Is_Date($_POST['BeforeDate'])) {
    $_POST['BeforeDate'] = Date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['AfterDate']) or !Is_Date($_POST['AfterDate'])) {
    $_POST['AfterDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0, 0, 0, Date("m"), 1, Date("y")));
}

if (isset($_POST['FromYear'])) {
        $FromYear=$_POST['FromYear'];
} else {
    $FromYear=date('Y');
}

if (isset($_POST['FromMes'])) {
    $FromMes=$_POST['FromMes'];
} else {
    $FromMes=1;
}
        
if (isset($_POST['FromDia'])) {
    $FromDia=$_POST['FromDia'];
} else {
    $FromDia=1;
}

if (isset($_POST['ToYear'])) {
    $ToYear=$_POST['ToYear'];
} else {
    $ToYear=date('Y');
}

if (isset($_POST['ToMes'])) {
    $ToMes=$_POST['ToMes'];
} else {
    $ToMes=date('m');
}
if (isset($_POST['ToDia'])) {
    $ToDia=$_POST['ToDia'];
} else {
    $ToDia=date('d');
}

$fechaini = date_create($_POST['fechaDesde']);
$fechaini = date_format($fechaini, 'Y-m-d');

$fechafin = date_create($_POST['fechaHasta']);
$fechafin = date_format($fechafin, 'Y-m-d');

if (isset($_POST['StockLocation'])) {
    $StockLocation = $_POST['StockLocation'];
} else {
    $StockLocation = '-1';
}

if (!isset($_POST['SerialItem'])) {
    $_POST['SerialItem'] = '0';
}

$sqlWhere = "";

if (isset($_POST['selectUnidadNegocio']) && $_POST['selectUnidadNegocio'] != '-1') {
    $sqlWhere .= " AND stockmoves.tagref = '".$_POST['selectUnidadNegocio']."' ";
}

if (isset($_POST['selectUnidadEjecutora']) && $_POST['selectUnidadEjecutora'] != '-1') {
    $sqlWhere .= " AND stockmoves.ln_ue = '".$_POST['selectUnidadEjecutora']."' ";
}

$sql = "SELECT stockmoves.stockid,
sum(stockmoves.qty) as initqty
FROM stockmoves
INNER JOIN systypescat ON stockmoves.type=systypescat.typeid
INNER JOIN stockmaster ON stockmoves.stockid=stockmaster.stockid
INNER JOIN locations ON stockmoves.loccode = locations.loccode
LEFT JOIN stockserialitems ON stockmoves.loccode = stockserialitems.loccode AND stockmoves.stockid = stockserialitems.stockid
AND ((stockmaster.controlled = 1 AND (stockserialitems.serialno = '".$_POST['SerialItem']."') AND stockserialitems.quantity >0))
WHERE (stockmoves.loccode='" . $StockLocation . "' or '-1'='".$StockLocation."')
AND stockmoves.trandate < '". $fechaini . "'
AND stockmoves.stockid = '" . $StockID . "' 
AND hidemovt=0 ".$sqlWhere."
GROUP BY stockmoves.stockid";
     
//echo $sql;
$ErrMsg = _('X The stock movements for the selected criteria could not be retrieved because') . ' - ';
$DbgMsg = _('The SQL that failed was') . ' ';
$invinicial = 0;
$MovtsResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);
if ($myrow=DB_fetch_array($MovtsResult)) {
    $invinicial = $myrow['initqty'];
}

$sql = "SELECT stockmoves.stkmoveno, stockmoves.stockid, 
systypescat.typename, 
stockmoves.type,
locations.tagref,
stockmoves.transno, 	
stockmoves.trandate, 
stockmoves.debtorno, 
stockmoves.branchcode,";

if ($_POST['SerialItem'] != "0") {
    $sql = $sql . " case when ((stockmoves.qty < 0) and (stockserialmoves.moveqty > 0))   then (stockserialmoves.moveqty*-1) else stockserialmoves.moveqty end as qty,";
} else {
    $sql = $sql . " stockmoves.qty as qty,";
}

$sql = $sql . " stockmoves.reference, 
stockmoves.price, 
stockmoves.standardcost,
stockmoves.avgcost,
stockmoves.discountpercent,
stockmoves.discountpercent1,
stockmoves.discountpercent2, 
stockmoves.newqoh, 
stockmaster.decimalplaces, 
stockmoves.loccode,
case when stockmoves.type = 25 then reverse(substring(reverse(stockmoves.reference),1,locate('-', reverse(stockmoves.reference))-2)) else '0' end  as orderno,
locations.locationname,
'' as folio,
stockmaster.materialcost,";
        
if ($_POST['SerialItem'] != "0") {
    $sql = $sql . " IFNULL(stockserialmoves.serialno,'') as serialno";
} else {
    $sql = $sql . " '' as serialno";
}

$sql = $sql . " FROM ";
    
if ($_POST['SerialItem'] != "0") {
    $sql = $sql . " stockserialmoves, ";
}
    
$sql = $sql . " stockmoves
INNER JOIN systypescat ON stockmoves.type=systypescat.typeid
INNER JOIN stockmaster ON stockmoves.stockid=stockmaster.stockid
left JOIN locations ON stockmoves.loccode = locations.loccode";

$sql = $sql . "	
WHERE (stockmoves.loccode='" . $StockLocation . "' or '-1'='".$StockLocation."')
AND stockmoves.trandate between '".$fechaini." 00:00:00' AND '".$fechafin." 23:59:59'
AND stockmoves.stockid = '" . $StockID . "'
AND hidemovt=0 ".$sqlWhere;
     
if (($_POST['SerialItem'] != "0")) {
    $sql = $sql . " and stockmoves.stkmoveno = stockserialmoves.stockmoveno
				and stockmaster.controlled = 1
			       AND (trim(stockserialmoves.serialno) = '".trim($_POST['SerialItem'], " ")."')";
}
if (isset($_POST['typefil']) and $_POST['typefil'] <> "*") {
    $sql = $sql." and stockmoves.type = '".$_POST['typefil']."'";
}
if (isset($_POST['transnofil']) and $_POST['transnofil'] <> "*") {
    $sql = $sql." and stockmoves.transno = '".$_POST['transnofil']."'";
}
$sql = $sql . " ORDER BY stockmoves.trandate,stockmoves.stkmoveno";

// echo '<pre>'.$sql;

$ErrMsg = _('X The stock movements for the selected criteria could not be retrieved because') . ' - ';
$DbgMsg = _('The SQL that failed was') . ' ';
$MovtsResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);

echo '<table id="idTableItemStockMove" cellpadding=5 BORDER=1 class="w100p tableHeaderVerde">';

printf(
    "<tr><th colspan=2 style='text-align:center;font-weight:normal;'>%s</th>
    <th colspan=4 style='font-weight:normal;'>INVENTARIO INICIAL</th>
    <th style='font-weight:normal;text-align:center;'>%s</th>
    <th style='font-weight:normal;text-align:center;' colspan='3'></th>
    </tr>",
    $StockIDTemp,
    number_format($invinicial, 0)
);

$tableheader = "<tr>
<th>" . _('#') . "</th>
<th>" . _('Tipo') . "</th>
<th>" . _('Almacén') . "</th>
<th>" . _('Folio') . "</th>
<th>" . _('Fecha') . "</th>
<th>" . _('Clave del artículo') . "</th>
<th>" . _('Cantidad') . "</th>
<th>" . _('Acumulada') . "</th>
<th>" . _('Descripción') . "</th>
<th>" . _('Costo') . "</th>
</tr>";
echo $tableheader;

$j = 0;
$k=0;
$sumaqty =0;
$acumqty = $invinicial;
while ($myrow=DB_fetch_array($MovtsResult)) {
    $j=$j+1;
    $sumaqty = $sumaqty + $myrow['qty'];
    echo '<tr>';
    //echo $myrow['qty'];
    //echo $myrow['decimalplaces'].'<br>';
    $DisplayTranDate = ConvertSQLDate($myrow['trandate']);
    $acumqty = $acumqty + $myrow['qty'];
    
    if ($_POST['SerialItem'] != "0") {
        $varserie = $myrow['serialno'];
    } else {
        $varserie = "consulta a tabla de stockserialmoves";
        
        $xsql = "select serialno from stockserialmoves where stockmoveno = '"  . $myrow['stkmoveno'] . "'";
        // $xResult= DB_query($xsql, $db);
        $varserie = "";
        
        while ($xmyrow=DB_fetch_array($xResult)) {
            $varserie = $varserie . $xmyrow['serialno'] . "; ";
        }
    }
    if ($varserie != "") {
        $referencia = $myrow['reference'] . "<br>S: " . $varserie;
    } else {
        $referencia = $myrow['reference'];
    }
    $tagref= $myrow['tagref'];
    
    $SQL=" SELECT l.taxid,l.address5,t.tagdescription FROM legalbusinessunit l, tags t WHERE l.legalid=t.legalid AND tagref='".$tagref."'";
    $Result= DB_query($SQL, $db);
    if (DB_num_rows($Result)==1) {
        $myrowtags = DB_fetch_array($Result);
        $rfc=trim($myrowtags['taxid']);
        $keyfact=$myrowtags['address5'];
        $nombre=$myrowtags['tagdescription'];
    }
    $tipofac=$myrow['type'];
    $foliox=" ";
    $foliox=$myrow['folio'];
    $separa = explode('|', $foliox);
    if ($tipofac=='12') {
        $serie = $separa[1];
        $folio = $separa[0];
    } else {
        $serie = $separa[0];
        $folio = $separa[1];
    }
    
    if ($myrow['type']==10 or $myrow['type']==110) {
        if ($_SESSION['EnvioXSA']==1) {
            $pagina=$_SESSION['XSA']."xsamanager/downloadCfdWebView?serie=" . $serie . "&folio=" . $folio . "&tipo=PDF&rfc=" . $rfc . "&key=" . $keyfact ;
        } else {
            $liga = 'PDFInvoice.php?';
            $pagina=$liga.'&TransNo='.$order.'&Tagref='.$tagref.'&Type='.$myrow['type'];
        }
        
        printf(
            "<td style='text-align:center;font-weight:normal;font-size:8pt;'>%s</td><td>
            <a TARGET='_blank' href='%s'>%s</td>
            <td style='font-weight:normal;font-size:8pt;'>%s</td>
            <td style='font-weight:normal;font-size:8pt;'>%s</td>
            <td style='font-weight:normal;font-size:8pt;'>%s</td>
            <td style='font-weight:normal;font-size:8pt;'>%s&nbsp;</td>
            <td style='font-weight:normal;text-align:center;font-size:8pt;'>%s</td>
            <td style='font-weight:normal;text-align:center;font-size:8pt;'>%s</td>
            <td style='font-weight:normal;font-size:8pt;'>%s</td>
            <td style='font-weight:normal;text-align:right;font-size:8pt;'>$%s</td>
            ",
            $j,
            $pagina,
            $myrow['typename'],
            $myrow['loccode'].' '.$myrow['locationname'],
            'F:'.$myrow['folio'].'<br>ERP:'.$myrow['transno'],
            $DisplayTranDate,
            $myrow['debtorno'],
            number_format($myrow['qty'], $myrow['decimalplaces']),
            number_format($acumqty, $myrow['decimalplaces']),
            $referencia,
            number_format($myrow['standardcost'], 2)
        );
    } elseif ($myrow['type']==11) {
        printf(
            "<td style='font-weight:normal;font-size:8pt;text-align:center;'>%s</td><td>
            <a style='font-size:8pt;' TARGET='_blank' href='%s'>%s</td>
            <td style='font-weight:normal;font-size:8pt;'>%s</td>
            <td style='font-weight:normal;font-size:8pt;'>%s</td>
            <td style='font-weight:normal;font-size:8pt;'>%s</td>
            <td style='font-weight:normal;font-size:8pt;'>%s&nbsp;</td>
            <td style='font-weight:normal;font-size:8pt;text-align:center;'>%s</td>
            <td style='font-weight:normal;font-size:8pt;text-align:center;'>%s</td>
            <td style='font-weight:normal;font-size:8pt;'>%s</td>
            <td style='font-weight:normal;font-size:8pt;text-align:right;'>$%s</td>
            ",
            $j,
            $pagina,
            $myrow['typename'],
            $myrow['loccode'].' '.$myrow['locationname'],
            'F:'.$myrow['folio'].'<br>ERP:'.$myrow['transno'],
            $DisplayTranDate,
            $myrow['debtorno'],
            number_format($myrow['qty'], $myrow['decimalplaces']),
            number_format($acumqty, $myrow['decimalplaces']),
            $referencia,
            number_format($myrow['standardcost'], 2)
        );
    } elseif ($myrow['type']==25 && 1 == 2) {
        /*$stdcost = $myrow['standardcost'] - ($myrow['standardcost'] * ($myrow['discountpercent']/100));
		$stdcost = $stdcost - ($stdcost * ($myrow['discountpercent1']/100));
		$stdcost = $stdcost - ($stdcost * ($myrow['discountpercent2']/100));*/
        
        printf(
            "<td style='font-weight:normal;font-size:8pt;text-align:center;'>%s</td><td style='font-weight:normal;font-size:8pt;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;'>%s&nbsp;</td>
			<td style='font-weight:normal;font-size:8pt;text-align:center;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;text-align:center;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;text-align:right;'>$%s</td>
			",
            $j,
            $myrow['typename'],
            $myrow['loccode'].' '.$myrow['locationname'],
            'OC:'.$myrow['orderno'].'<br>REC:'.$myrow['transno'],
            $DisplayTranDate,
            $myrow['debtorno'],
            number_format($myrow['qty'], $myrow['decimalplaces']),
            number_format($acumqty, $myrow['decimalplaces']),
            $referencia,
            number_format($myrow['standardcost'], 2)
        );
    } else if ($myrow['type']==52) {
        $sql = "select folio from shippinglog where typeno = '{$myrow['transno']}' and type = '{$myrow['type']}'";
        
        $rs = DB_query($sql, $db);
        
        $folio = '';
        if ($row = DB_fetch_array($rs)) {
            $folio = $row['folio'];
        }
        
        printf(
            "<td style='font-weight:normal;font-size:8pt;text-align:center;'>%s</td><td style='font-weight:normal;font-size:8pt;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;'>%s&nbsp;</td>
			<td style='font-weight:normal;font-size:8pt;text-align:center;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;text-align:center;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;text-align:right;'>$%s</td>
			",
            $j,
            $myrow['typename'],
            $myrow['loccode'].' '.$myrow['locationname'],
            'F:'.$row['folio'].'<br>ERP:'.$myrow['transno'],
            $DisplayTranDate,
            $myrow['debtorno'],
            number_format($myrow['qty'], $myrow['decimalplaces']),
            number_format($acumqty, $myrow['decimalplaces']),
            $referencia,
            number_format($myrow['standardcost'], 2)
        );
    } else {
        printf(
            "<td style='font-weight:normal;font-size:8pt;text-align:center;'>%s</td><td style='font-weight:normal;font-size:8pt;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;text-align:center;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;text-align:center;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;text-align:center;'>%s&nbsp;</td>
			<td style='font-weight:normal;font-size:8pt;text-align:center;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;text-align:center;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;'>%s</td>
			<td style='font-weight:normal;font-size:8pt;text-align:right;'>$%s</td>
			",
            $j,
            $myrow['type'].' - '.$myrow['typename'],
            $myrow['loccode'].' '.$myrow['locationname'],
            $myrow['transno'],
            $DisplayTranDate,
            $myrow['stockid'],
            number_format($myrow['qty'], $myrow['decimalplaces']),
            number_format($acumqty, $myrow['decimalplaces']),
            $referencia,
            number_format($myrow['standardcost'], 2)
        );
    }
    // if (Havepermission($_SESSION['UserID'], 984, $db)==1) {
    //     echo "<td class='text-center' style='font-weight:normal;font-size:8pt;'>";
    //         echo "<a href='StockMovements.php?operation=eliminar&StockID=" . $StockID . "&stkmoveno=" . $myrow['stkmoveno'] . "'>Eliminar</a>";
    //     echo "</td>";
    // } else {
    //     echo "<td style='font-weight:normal;font-size:8pt;'>&nbsp;</td>";
    // }
    
    echo "</tr>";
    $DecimalPlaces = $myrow['decimalplaces'];
}

echo '<tr><td colspan=6 style="text-align:right;">TOTAL : </td><td style="text-align:center;">'.number_format(($sumaqty + $invinicial), $DecimalPlaces).'</td><td colspan=3>&nbsp;</td></tr>';
echo '</table>';

if (isset($_POST['excel'])) {
    exit;
}

//INICIO DE TABLAS DE TRANSFERENCIAS
if ($StockLocation=='0') {
    $SQL=" SELECT reference, stockid,shipdate,shiploc,recloc,shipqty,recqty,
	locations.locationname as destino, l.locationname as origen
	FROM loctransfers INNER JOIN locations
	ON loctransfers.recloc=locations.loccode
	INNER JOIN locations as l
	ON loctransfers.shiploc=l.loccode
	WHERE (shipqty-recqty) > 0 /*recqty='0'*/
	AND stockid='".$StockID."'";
    $Result= DB_query($SQL, $db);
    
    if (DB_num_rows($Result) > 0) {
        echo "<table cellpadding=5 border=1>
        <tr><td colspan=6 ' cellpadding='2' ><p align='center'>" . _('TRANSFERENCIAS EN PROCESO') . "</p></td></tr>";

        $tableheader2 = "<tr>
        <th>" . _('# Linea') . "</th>
        <th>" . _('# Transferencia') . "</th>
        <th>" . _('Almacén de Origen') . "</th>
        <th>" . _('Almacén Destino') . "</th>
        <th>" . _('Fecha') . "</th>
        <th>" . _('Cantidad') . "</th>
        </tr>";
        echo $tableheader2;
        $j=1;
        $k=0;
        while ($myrow = DB_fetch_array($Result)) {
            if ($k==1) {
                echo '<tr style="font-weight:normal">';
                $k=0;
            } else {
                echo '<tr style="font-weight:normal">';
                $k=1;
            }
            printf(
                "<td style='font-weight:normal;font-size:9pt;text-align:center;'>%s</td>
                <td style='font-weight:normal;font-size:9pt;text-align:center;'>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td style='font-weight:normal;font-size:9pt;text-align:right;'>%s</td>
                </tr>",
                $j,
                $myrow['reference'],
                $myrow['origen'],
                $myrow['destino'],
                $myrow['shipdate'],
                $myrow['shipqty']
            );
                $j++;
        }
        echo "</tr>";
        echo "</table><hr>";
    }
} else {
    $SQL=" SELECT reference, stockid,shipdate,shiploc,recloc,shipqty,recqty,
		locations.locationname as destino, l.locationname as origen
		FROM loctransfers INNER JOIN locations
		ON loctransfers.recloc=locations.loccode
		INNER JOIN locations as l
		ON loctransfers.shiploc=l.loccode
		WHERE recqty='0'
		AND shiploc='".$StockLocation."'
		AND stockid='".$StockID."'";
        $Result= DB_query($SQL, $db);
    if (DB_num_rows($Result) > 0) {
        echo "<table cellpadding=5 border=1>
			<tr><td colspan=6 ' cellpadding='2' ><p align='center'>" . _('TRANSFERENCIAS DE SALIDA') . "</p></td></tr>";
          $tableheader2 = "<tr>
				  <th>" . _('# Linea') . "</th>
				  <th>" . _('# Transferencia') . "</th>
				  <th>" . _('Almacén de Origen') . "</th>
				  <th>" . _('Almacén Destino') . "</th>
				  <th>" . _('Fecha') . "</th>
				  <th>" . _('Cantidad') . "</th>
				  </tr>";
          echo $tableheader2;
              $j=1;
              $k=0; //row colour counter
              $suma1=0;
        while ($myrow = DB_fetch_array($Result)) {
            $suma1=$suma1 + $myrow['shipqty'];
            /*$j=$j+1;
            $sumaqty = $sumaqty + $myrow['qty'];
            echo '<tr>';
            $DisplayTranDate = ConvertSQLDate($myrow['trandate']);
            $acumqty = $acumqty + $myrow['qty'];*/
            if ($k==1) {
                  echo '<tr style="font-weight:normal">';
                  $k=0;
            } else {
                echo '<tr style="font-weight:normal">';
                $k=1;
            }
                printf(
                    "<td style='font-weight:normal;font-size:9pt;text-align:center;'>%s</td>
				<td style='font-weight:normal;font-size:9pt;text-align:center;'>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td style='font-weight:normal;font-size:9pt;text-align:right;'>%s</td>
				  </tr>",
                    $j,
                    $myrow['reference'],
                    $myrow['origen'],
                    $myrow['destino'],
                    $myrow['shipdate'],
                    $myrow['shipqty']
                );
              $j++;
        }
        echo "</tr>";
        echo '<tr><td colspan=5 style="text-align:right;">TOTAL : </td><td style="text-align:right;">'.number_format((-$suma1), $DecimalPlaces).'</td></tr>';
        echo "</table>";
    }
    $SQL=" SELECT reference, stockid,shipdate,shiploc,recloc,shipqty,recqty,
		locations.locationname as destino, l.locationname as origen
		FROM loctransfers INNER JOIN locations
		ON loctransfers.recloc=locations.loccode
		INNER JOIN locations as l
		ON loctransfers.shiploc=l.loccode
		WHERE recqty='0'
		AND recloc='".$StockLocation."'
		AND stockid='".$StockID."'";
        $Result= DB_query($SQL, $db);
    if (DB_num_rows($Result) > 0) {
        echo "<table cellpadding=5 border=1>
        <tr><td colspan=6 ' cellpadding='2' ><p align='center'>" . _('TRANSFERENCIAS DE ENTRADA') . "</p></td></tr>";
        $tableheader2 = "<tr>
        <th>" . _('# Linea') . "</th>
        <th>" . _('# Transferencia') . "</th>
        <th>" . _('Almacén de Origen') . "</th>
        <th>" . _('Almacén Destino') . "</th>
        <th>" . _('Fecha') . "</th>
        <th>" . _('Cantidad') . "</th>
        </tr>";
        echo $tableheader2;
        $j=1;
        $k=0;
        $suma2=0;
        while ($myrow = DB_fetch_array($Result)) {
            $suma2=$suma2 + $myrow['shipqty'];
            if ($k==1) {
                echo '<tr style="font-weight:normal" >';
                $k=0;
            } else {
                echo '<tr style="font-weight:normal">';
                $k=1;
            }
            printf(
                "<td style='font-weight:normal;font-size:9pt;text-align:center;'>%s</td>
                <td style='font-weight:normal;font-size:9pt;text-align:center;'>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td style='font-weight:normal;font-size:9pt;text-align:right;'>%s</td>
                </tr>",
                $j,
                $myrow['reference'],
                $myrow['origen'],
                $myrow['destino'],
                $myrow['shipdate'],
                $myrow['shipqty']
            );
            $j++;
            $_POST['shipqty']=$myrow['shipqty'];
            $cantidad2=$_POST['shipqty'];
        }
        echo "</tr>";
        echo '<tr><td colspan=5 style="text-align:right;">TOTAL : </td><td style="text-align:right;">'.number_format(($suma2), $DecimalPlaces).'</td></tr>';
        echo "</table>";
    }
    echo '<table border="0" align="right">
	<tr><td style="text-align:right; visibility:hidden">DISPONIBLES : </td><td style="text-align:right; visibility:hidden" colspan=7>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.round((($sumaqty + $invinicial+$suma2)-$suma1), 2).'</td><td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td><td >&nbsp;</td></tr><br>
      </table>';
}

/*echo "<br><br>";
echo "<br><a href='$rootpath/StockStatus.php?" . SID . "&StockID=$StockIDTemp'>" . _('Mostrar Estatus de Inventario') . '</a>';
echo "<br><a href='$rootpath/StockUsage.php?" . SID . "&StockID=$StockID&StockLocation=" . $_POST['StockLocation'] . "'>" . _('Mostrar Uso de Inventario') . '</a>';
echo "<br><a href='$rootpath/SelectSalesOrder.php?" . SID . "&SelectedStockItem=$StockID&StockLocation=" . $_POST['StockLocation'] . "'>" . _('Buscar Pedidos de Venta Pendientes') . '</a>';
echo "<br><a href='$rootpath/SelectCompletedOrder.php?" . SID . "&SelectedStockItem=$StockID'>" . _('Buscar Pedidos de Venta Completados') . '</a>';
*/
echo "<br>";
include('includes/footer_Index.inc');
?>
<script language=javascript>
fnFormatoSelectGeneral("#StockLocation");
fnFormatoSelectGeneral("#idFromDia");
fnFormatoSelectGeneral("#idFromMes");
fnFormatoSelectGeneral("#idToDia");
fnFormatoSelectGeneral("#idToMes");
fnFormatoSelectGeneral("#idSerialItem");
fnFormatoSelectGeneral("#typefil");
fnFormatoSelectGeneral("#movProd");
fnFormatoSelectGeneral("#idpartidacalculada");

<?php
if (isset($_POST['selectUnidadNegocio']) && $_POST['selectUnidadNegocio'] != '-1') {
    ?>
    fnSeleccionarDatosSelect("selectUnidadNegocio", "<?php echo $_POST['selectUnidadNegocio']; ?>");
    <?php
}

if (isset($_POST['selectUnidadEjecutora']) && $_POST['selectUnidadEjecutora'] != '-1') {
    ?>
    fnSeleccionarDatosSelect("selectUnidadEjecutora", "<?php echo $_POST['selectUnidadEjecutora']; ?>");
    <?php
}
?>

$( document ).ready(function() {
    fnBusquedaFiltro();
});
function fnBusquedaFiltro() {
    dataObj = { 
            option: 'mostrarProductos'
          };
    $.ajax({
      async:false,
      cache:false,
      method: "POST",
      dataType:"json",
      url: "modelo/selectProductModelo.php",
      data: dataObj
    })
    .done(function( data ) {
        //console.log(data);
        if(data.result) {
            fnBusquedaFiltroFormato(data.contenido.datos);
        }else{
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se Obtuvieron los Productos</p>');
        }
    })
    .fail(function(result) {
        console.log( result );
    });
}

function fnBusquedaFiltroFormato(jsonData) {
    // console.log("busqueda fnBusquedaCog");
    // console.log("jsonData: "+JSON.stringify(jsonData));
    $( "#StockID").autocomplete({
        source: jsonData,
        select: function( event, ui ) {
            
            $( this ).val( ui.item.value + "");
            $( "#StockID" ).val( ui.item.value );

            return false;
        }
    })
    .autocomplete( "instance" )._renderItem = function( ul, item ) {

        return $( "<li>" )
        .append( "<a>" + item.texto + "</a>" )
        .appendTo( ul );

    };
}
</script> 