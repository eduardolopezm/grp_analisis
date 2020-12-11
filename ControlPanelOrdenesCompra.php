<?php

/*error_reporting(E_ALL);
ini_set('display_errors', '1');

error_reporting(-1);*/
$funcion = 1371;
$PageSecurity = 3;
//$title = _('Panel de Control Ordenes de Compra');
include ('includes/session.inc');
$title = _('Panel de Control Ordenes de Compra');
include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');
include ('includes/SecurityFunctions.inc');
include ('includes/ManufacturedStockUnitcost.inc');


// Declaracion de Permisos

// Declaracion de Variables generales en la pagina
if (isset($_GET['FromDia'])) {
    $_POST['FromDia'] = $_GET['FromDia'];
} 
elseif (isset($_POST['FromDia'])) {
    $_POST['FromDia'] = $_POST['FromDia'];
} 
else {
    $_POST['FromDia'] = date("d");
}

if (isset($_GET['FromYear'])) {
    $_POST['FromYear'] = $_GET['FromYear'];
} 
elseif (isset($_POST['FromYear'])) {
    $_POST['FromYear'] = $_POST['FromYear'];
} 
else {
    $_POST['FromYear'] = date("Y");
}

if (isset($_GET['FromMes'])) {
    $_POST['FromMes'] = $_GET['FromMes'];
} 
elseif (isset($_POST['FromMes'])) {
    $_POST['FromMes'] = $_POST['FromMes'];
} 
else {
    $_POST['FromMes'] = date("m");
}

///
if (isset($_GET['ToDia'])) {
    $_POST['ToDia'] = $_GET['ToDia'];
} 
elseif (isset($_POST['ToDia'])) {
    $_POST['ToDia'] = $_POST['ToDia'];
} 
else {
    $_POST['ToDia'] = date("d");
}

if (isset($_GET['ToMes'])) {
    $_POST['ToMes'] = $_GET['ToMes'];
} 
elseif (isset($_POST['ToMes'])) {
    $_POST['ToMes'] = $_POST['ToMes'];
} 
else {
    $_POST['ToMes'] = date("m");
}

if (isset($_GET['ToYear'])) {
    $_POST['ToYear'] = $_GET['ToYear'];
} 
elseif (isset($_POST['ToYear'])) {
    $_POST['ToYear'] = $_POST['ToYear'];
} 
else {
    $_POST['ToYear'] = date("Y");
}

if (isset($_GET['legalid'])) {
    $_POST['legalid'] = $_GET['legalid'];
} 
elseif (isset($_POST['legalid'])) {
    $_POST['legalid'] = $_POST['legalid'];
} 
else {
    $_POST['legalid'] = '-1';
}

if (isset($_GET['tagref'])) {
    $_POST['tagref'] = $_GET['tagref'];
} 
elseif (isset($_POST['tagref'])) {
    $_POST['tagref'] = $_POST['tagref'];
} 
else {
    $_POST['tagref'] = '-1';
}

if (isset($_GET['proveedor'])) {
    $_POST['proveedor'] = $_GET['proveedor'];
} 
elseif (isset($_POST['proveedor'])) {
    $_POST['proveedor'] = $_POST['proveedor'];
} 
else {
    $_POST['proveedor'] = '*';
}

if (isset($_GET['folio'])) {
    $_POST['folio'] = $_GET['folio'];
} 
elseif (isset($_POST['folio'])) {
    $_POST['folio'] = $_POST['folio'];
} 
else {
    $_POST['folio'] = '*';
}

if (isset($_GET['orderno'])) {
    $_POST['orderno'] = $_GET['orderno'];
} 
elseif (isset($_POST['orderno'])) {
    $_POST['orderno'] = $_POST['orderno'];
} 
else {
    $_POST['orderno'] = '*';
}

$strFindEstatus = "";
$strSelEstatus = "";
$strFindEstatus = "";

if (isset($_POST['selStatus'])) {
    for ($i = 0; $i <= count($_POST['selStatus']) - 1; $i++) {
        $umovto = $_POST['selStatus'][$i];
        $strSelEstatus = $strSelEstatus . ',' . $umovto;
        $strFindEstatus = $strFindEstatus . '_' . $umovto . '*';
    }
    
    $strSelEstatus = substr($strSelEstatus, 1);
    $strFindEstatus = 'X' . $strFindEstatus;
} 
else {
    if (isset($_GET['findstatus'])) {
        $strFindEstatus = $_GET['findstatus'];
        $strSelEstatus = substr($strFindEstatus, 1);
        $strSelEstatus = str_replace("_", ",", $strSelEstatus);
        $strSelEstatus = str_replace("*", "", $strSelEstatus);
        $strSelEstatus = substr($strSelEstatus, 1);
    }
}

/* OBTENGO DATOS DE SELECCION DE FORMA YA SEA VIA POST O GET */

if (isset($_GET['AGENDASEMANAL'])) {
    
    $_POST['AGENDASEMANAL'] = $_GET['AGENDASEMANAL'];
}

$fechaini = rtrim($_POST['FromYear']) . '-' . rtrim($_POST['FromMes']) . '-' . rtrim($_POST['FromDia']);
$fechainic = mktime(0, 0, 0, rtrim($_POST['FromYear']), rtrim($_POST['FromMes']), rtrim($_POST['FromDia']));
$fechaini = rtrim($_POST['FromYear']) . '-' . add_ceros(rtrim($_POST['FromMes']), 2) . '-' . add_ceros(rtrim($_POST['FromDia']), 2);

$fechafin = rtrim($_POST['ToYear']) . '-' . rtrim($_POST['ToMes']) . '-' . rtrim($_POST['ToDia']);
$fechafinc = mktime(0, 0, 0, rtrim($_POST['ToYear']), rtrim($_POST['ToMes']), rtrim($_POST['ToDia']));
$fechafin = rtrim($_POST['ToYear']) . '-' . add_ceros(rtrim($_POST['ToMes']), 2) . '-' . add_ceros(rtrim($_POST['ToDia']), 2);

if (isset($_GET['Oper'])) {
    $_POST['Oper'] = $_GET['Oper'];
}

$friendlyLongMes = array(1 => "Enero", 2 => "Febrero", 3 => "Marzo", 4 => "Abril", 5 => "Mayo", 6 => "Junio", 7 => "Julio", 8 => "Agosto", 9 => "Septiembre", 10 => "Octubre", 11 => "Noviembre", 12 => "Diciembre");
$thislegalid = '-1';

echo "<form name='FDatosA' id='SubFrm' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'>";
echo "<table border=0 align='center'; width:0; background-color:#ffff;' border=0 width=100% nowrap>";
echo '  <tr><td align="center" colspan=2 class="texto_lista"><p align="center">
        <img src="images/tareas_config.png" height=25" width="25" title="' . _('Panel de Control para Comentario de Facturas') . '" alt="">' . ' ' . $title . '<br>
        </td>
        </tr>';
echo '</table>';

echo '<fieldset class="cssfieldset" style="width:95%">';
echo '  <legend>
                    Criterio de Consulta
        </legend>';
echo "<table border=0 style='margin-left: auto; margin-right: auto; width:300;'>";
echo "<tr><td><table width=100% >";
echo '<tr>';
echo '<td class="texto_lista">' . _('Desde:') . '</b></td>';
echo ' <td><select Name="FromDia">';
$sql = "SELECT *
        FROM cat_Days";
$dias = DB_query($sql, $db, '', '');
while ($myrowdia = DB_fetch_array($dias, $db)) {
    $diabase = $myrowdia['DiaId'];
    if (rtrim(intval($_POST['FromDia'])) == rtrim(intval($diabase))) {
        echo '<option  VALUE="' . $myrowdia['DiaId'] . '  " selected>' . $myrowdia['Dia'];
    } 
    else {
        echo '<option  VALUE="' . $myrowdia['DiaId'] . '" >' . $myrowdia['Dia'];
    }
}
echo '</select>';
echo '<select Name="FromMes">';
$sql = "SELECT *
        FROM cat_Months";
$Meses = DB_query($sql, $db);
while ($myrowMes = DB_fetch_array($Meses, $db)) {
    $Mesbase = $myrowMes['u_mes'];
    if (rtrim(intval($_POST['FromMes'])) == rtrim(intval($Mesbase))) {
        echo '<option  VALUE="' . $myrowMes['u_mes'] . '" selected>' . $myrowMes['mes'];
    } 
    else {
        echo '<option  VALUE="' . $myrowMes['u_mes'] . '" >' . $myrowMes['mes'];
    }
}
 //
echo '</select>';
echo '&nbsp;<input name="FromYear" type="text" size="4" value=' . $_POST['FromYear'] . '>';
echo '</td></tr>';

echo '<tr><td class="texto_lista">' . _('Razon Social:') . '</b></td><td><select name="legalid">';

$SQL = "SELECT legalbusinessunit.legalid,
                legalbusinessunit.legalname
        FROM sec_unegsxuser u,tags t 
            JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid
        WHERE u.tagref = t.tagref
        AND u.userid = '" . $_SESSION['UserID'] . "'
        GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY t.tagref";

$result = DB_query($SQL, $db);

echo "<option selected value='-1'>Todos las razones sociales...</option>";
while ($myrow = DB_fetch_array($result)) {
    if ($_POST['legalid'] == $myrow["legalid"]) {
        echo '<option selected value=' . $myrow['legalid'] . '>' . $myrow['legalname'];
    } 
    else {
        echo '<option value=' . $myrow['legalid'] . '>' . $myrow['legalname'];
    }
}
echo '</select></td></tr>';

echo '<tr><td class="texto_lista">' . _('Unidad de Negocios:') . '</b></td><td><select name="tagref">';

$SQL = "SELECT t.tagref, 
                t.tagdescription
        FROM sec_unegsxuser u,tags t 
        WHERE u.tagref = t.tagref 
            AND u.userid = '" . $_SESSION['UserID'] . "'
        GROUP BY t.tagref, t.tagdescription ORDER BY t.tagref";
$result = DB_query($SQL, $db);
echo "<option selected value='-1'>Todas...</option>";
while ($myrow = DB_fetch_array($result)) {
    if (isset($_POST['tagref']) and $_POST['tagref'] == $myrow["tagref"]) {
        echo '<option selected value=' . $myrow['tagref'] . '>' . $myrow['tagdescription'];
    } 
    else {
        echo '<option value=' . $myrow['tagref'] . '>' . $myrow['tagdescription'];
    }
}
echo '</select></td></tr>';

//userauto
echo "<tr><td class='texto_lista'>" . _('Usuario Autorizo') . "</b></td>";
$SQL = "SELECT www_users.userid,
                www_users.realname
        FROM www_users
        WHERE  www_users.blocked = 0
                        AND www_users.tecnoaplicadauser = 0";
$result = DB_query($SQL, $db);
echo "<td><select name='userauto'>";
echo "<option selected value='*'>Todos los usuarios</option>";
while ($myrow = DB_fetch_array($result)) {
    if (isset($_POST['userauto']) and $_POST['userauto'] == $myrow["userid"]) {
        echo '<option selected value=' . $myrow['userid'] . '>' . $myrow['realname'];
    } 
    else {
        echo '<option value=' . $myrow['userid'] . '>' . $myrow['realname'];
    }
}
echo '</select></td></tr>';
echo "</table></td>";
echo "<td><table width=100%>";

/* SELECCIONA EL RECURSO */
echo '<tr><td class="texto_lista">' . _('Hasta:') . '</b></td>';
echo ' <td><select name="ToDia">';
$sql = "SELECT *
        FROM cat_Days";

$dias = DB_query($sql, $db, '', '');

while ($myrowdia = DB_fetch_array($dias, $db)) {
    $diabase = $myrowdia['DiaId'];
    if (rtrim(intval($_POST['ToDia'])) == rtrim(intval($diabase))) {
        echo '<option  VALUE="' . $myrowdia['DiaId'] . '  " selected>' . $myrowdia['Dia'];
    } 
    else {
        echo '<option  VALUE="' . $myrowdia['DiaId'] . '" >' . $myrowdia['Dia'];
    }
}

echo '</select>';
echo '<select name="ToMes">';
$sql = "SELECT *
        FROM cat_Months";
$Meses = DB_query($sql, $db);
while ($myrowMes = DB_fetch_array($Meses, $db)) {
    $Mesbase = $myrowMes['u_mes'];
    if (rtrim(intval($_POST['ToMes'])) == rtrim(intval($Mesbase))) {
        echo '<option  VALUE="' . $myrowMes['u_mes'] . '" selected>' . $myrowMes['mes'];
    } 
    else {
        echo '<option  VALUE="' . $myrowMes['u_mes'] . '" >' . $myrowMes['mes'];
    }
}
 //
echo '</select>';
echo '&nbsp;<input name="ToYear" type="text" size="4" value=' . $_POST['ToYear'] . '>';
echo '</td></tr>';
echo '<tr><td nowrap class="texto_lista">' . _('Proveedor') . '</b></td><td nowrap>';
echo '&nbsp;<input name="proveedor" type="text" value=' . $_POST['proveedor'] . '>';
echo '</td>';
echo '</tr>';

/*echo '<tr><td nowrap class="texto_lista">' . _ ( 'Folio:' ) . '</b></td><td nowrap>';
echo '&nbsp;<input name="folio" type="text" value=' . $_POST ['folio'] . '>';
echo '</td>';
echo '</tr>';*/

//
echo '<tr><td nowrap class="texto_lista">' . _('Orden:') . '</b></td><td nowrap>';
echo '&nbsp;<input name="orderno" type="text" value=' . $_POST['orderno'] . '>';
echo '</td>';
echo '</tr>';

$SQL = "SELECT count(supplierid) as num FROM suppliers WHERE comprobanteFiscal is not null";
$result = DB_query($SQL, $db);
if ($row = DB_fetch_array($result)) {
    if ( $row['num'] > 0) {
        echo "<tr>";
        echo "<td nowrap class='texto_lista'>Comprobante Fiscal: </td>";
        echo "<td>";
        echo "<select name='cmbComprobanteFiscal' id='cmbComprobanteFiscal'>";
        echo "<option value='all' ".($_POST['cmbComprobanteFiscal'] == 'all' ? 'selected' : '').">Todos...</option>";
        echo "<option value='1' ".($_POST['cmbComprobanteFiscal'] == '1' ? 'selected' : '').">Si</option>";
        echo "<option value='0' ".($_POST['cmbComprobanteFiscal'] == '0' ? 'selected' : '').">No</option>";
        echo "</select>";
        echo "</td>";
        echo "</tr>";
    }
}

echo "</table></td></tr>";

/* TABLA DE SELECCION DE ESTATUS DE TAREAS */
echo "<tr><td colspan=2 style='text-align:center' align:center>";
echo '<table align="center" width="70%" height="78" border=0 class="texto_status">';
echo "<tr>";

$SQL = "SELECT idstatus,
                nombre,
                logo,
                marcainicial
        FROM purchorderscontrolstatus
        WHERE active = 1
        ORDER BY orden";

$resultGC = DB_query($SQL, $db);

$status = array();
$segundalinea = "";
$primeralinea = "";
$numveces = 0;
$conestatus = 1;
while ($myrowGC = DB_fetch_array($resultGC)) {
    
    $status[$myrowGC['idstatus']]['nombre'] = $myrowGC['nombre'];
    $status[$myrowGC['idstatus']]['logo'] = $myrowGC['logo'];
    $numveces = $numveces + 1;
    echo "<td>";
     //
    
    $strEstatus = explode(",", $strSelEstatus);
    if (strlen($strFindEstatus) > 0) {
        if (in_array($myrowGC['nombre'], $strEstatus)) {
            echo "<INPUT type=checkbox id='chkstatus" . $conestatus . "' checked name='selStatus[]' value='" . $myrowGC['nombre'] . "'>";
        } 
        else {
            echo "<INPUT type=checkbox id='chkstatus" . $conestatus . "' name='selStatus[]' value='" . $myrowGC['nombre'] . "'>";
        }
    } 
    else {
        if ($myrowGC['marcainicial'] == 1) {
            echo "<INPUT type=checkbox id='chkstatus" . $conestatus . "' checked name='selStatus[]' value='" . $myrowGC['nombre'] . "'>";
        } 
        else {
            echo "<INPUT type=checkbox id='chkstatus" . $conestatus . "' name='selStatus[]' value='" . $myrowGC['nombre'] . "'>";
        }
    }
    $conestatus = $conestatus + 1;
    echo "<img width=18 height=18 src='images/" . $myrowGC['logo'] . "' border='0' title='" . $myrowGC['nombre'] . "'>";
    echo "</td>";
    $segundalinea = $segundalinea . "<td style='font-size:9px;font-weight:bold;text-align:left'>" . $myrowGC['nombre'] . "</td>";
}

echo $primeralinea;
echo "</tr>";
echo "<tr>";
echo $segundalinea;
echo "</tr>";
echo "</table>";
echo "</td></tr>";
echo '</table>';
echo '</fieldset>';
echo '<div align="center">
        <img align=center src="images/sombra.png" width=100%>
      </div>';

$menuelem[0] = "AGENDASEMANAL";
$menuelemName[0] = "DESPLEGAR";

echo '<table width=100% border=0 cellspacing=0 cellpadding=0 style="text-align:center;margin:0;vertical-align:bottom">';
echo '<tr style="margin:0;vertical-align:bottom"><td cellspacing=0 style="text-align:center;margin:0">';

for ($idxmenu = 0; $idxmenu < count($menuelem); $idxmenu++) {
    if (isset($_POST[$menuelem[$idxmenu]]) or isset($_GET[$menuelem[$idxmenu]])) {
        if (isset($_GET[$menuelem[$idxmenu]])) {
            $_POST[$menuelem[$idxmenu]] = $_GET[$menuelem[$idxmenu]];
        }
        echo '<input type="submit" class="styled-button-10" name="' . $menuelem[$idxmenu] . '" value="' . $menuelemName[$idxmenu] . '" />';
    } 
    else {
        echo '<input type="submit" class="styled-button-8" name="' . $menuelem[$idxmenu] . '" value="' . $menuelemName[$idxmenu] . '" />';
    }
}

//Abir la pagina para subir xml sin orden de compra
if (Havepermission($_SESSION['UserID'], 1473, $db) == 1) {
    echo '<input type="button" onclick="newPage(\'' . $rootpath . '/SubirXMLWOUTPO.php\')" class="styled-button-8" name="XMLSINOC" value="XML SIN OC" />';
}

//
echo '<tr>';
echo '<td>';
echo '</td>';
echo '</tr>';
if ((isset($_POST[$menuelem[1]]) or isset($_GET[$menuelem[1]])) && isset($_POST['proyectoid']) && $_POST['proyectoid'] != - 1) {
    $periodo = glsnombremeslargo($FromMes) . ' ' . $FromYear;
    echo '&nbsp;&nbsp;&nbsp;<a target="_blank" href="' . $_SERVER['PHP_SELF'] . '?pdf=1&periodo=' . $periodo . '&proyectoid=' . $_POST['proyectoid'] . '"><img title="Exportar a PDF" src="images/PDF.gif"></a>';
}
echo '</td></tr>';
echo '</table>';

echo '<table cellspacing=0 border=0 width=100% bordercolor=#192d3c cellpadding=0 style="background-color:#192d3c">';
echo '<tr height=8>';
echo "<br>";

echo "</form>";

echo "<form name='FDatosB' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'>";

$ligabotones = "";
for ($idxmenu = 0; $idxmenu < count($menuelem); $idxmenu++) {
    if (isset($_POST[$menuelem[$idxmenu]])) {
        echo '<input type="hidden" name="' . $menuelem[$idxmenu] . '" value="' . $menuelemName[$idxmenu] . '" />';
        $ligabotones = "&" . $menuelem[$idxmenu] . "=1";
    }
}
echo '</td></tr>';
echo '</table>';

if (isset($_POST['AGENDASEMANAL']) and $_POST['AGENDASEMANAL'] == 'DESPLEGAR') {
    echo '<table cellspacing=0 border=1 bordercolor=lightgray cellpadding=0 colspan=2 style="margin-top:0">';
    $headertit = "<tr>";
    $headertit = $headertit . "<td rowspan=2 class=titulos_principales>" . _('#') . "</td>";
    $headertit = $headertit . "<td rowspan=2 class=titulos_principales>" . _('Orden') . "</td>";
    $headertit = $headertit . "<td rowspan=2 class=titulos_principales>" . _('Almacen') . "</td>";
    $headertit = $headertit . "<td rowspan=2 class=titulos_principales>" . _('Fecha') . "</td>";
    $headertit = $headertit . "<td rowspan=2 class=titulos_principales>" . _('Fecha <br> Autorizo') . "</td>";
    $headertit = $headertit . "<td rowspan=2 class=titulos_principales>" . _('Usuario Autorizo') . "</td>";
    $headertit = $headertit . "<td rowspan=2 class=titulos_principales>" . _('Proveedor') . "</td>";
    $headertit = $headertit . "<td rowspan=2 class=titulos_principales>" . _('RFC') . "</td>";
    $headertit = $headertit . "<td rowspan=2 class=titulos_principales>" . _('Moneda') . "</td>";
    $headertit = $headertit . "<td rowspan=2 class=titulos_principales>" . _('Total') . "</td>";
    $headertit = $headertit . "<td rowspan=2 class=titulos_principales>" . _('P.V.') . "</td>";
    $headertit = $headertit . "<td rowspan=2 class=titulos_principales nowrap>" . _('Estatus') . "</td>";
    $headertit = $headertit . "<td rowspan=2 class=titulos_principales>" . _('Folio ERP') . "</td>";
    $headertit = $headertit . "<td rowspan=2 class=titulos_principales>" . _('Ref') . "</td>";
    $headertit = $headertit . "<td rowspan=1 colspan=3 class=titulos_principales>" . _('Accion') . "</td>";
    $headertit = $headertit . "</tr>";
    $headertit = $headertit . "<tr>";
    $headertit = $headertit . '<td class=titulos_principales><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Imprimir Orden Compra') . '" alt="Imprimir"></td>';
    $headertit = $headertit . "<td class=titulos_principales>" . _('Adjuntos') . "</td>";
    $headertit = $headertit . "<td class=titulos_principales>" . _('Alta <br> Factura') . "</td>";
    $headertit = $headertit . "</tr>";
    echo $headertit;
    $SaldoInicial1 = 0;
    $SaldoInicial1_CONFIRMADO = 0;
    
    $posiciones = substr_count($strSelEstatus, ",");
    
    if ($_POST['proveedor'] != '*') {
        $prov = " AND suppname LIKE '%" . $_POST['proveedor'] . "%' ";
    } 
    if ($_POST['folio'] != '*' && $_POST['folio'] != '') {
        $foli1 = "";
    }
    if ($_POST['orderno'] != '*' && $_POST['orderno'] != '') {
        $orderno = "AND  orderno= '" . $_POST['orderno'] . "' ";
        $fechaSQL1 = "";
        $fechaSQL2 = "";
    } 
    else {
        $fechaSQL1 = " AND compras.trandate between '" . $fechaini . "' and '" . $fechafin . "' ";
        $fechaSQL2 = " AND purchorders.orddate BETWEEN '" . $fechaini . "' AND '" . $fechafin . "' ";
    }
    
    //$orderno
    if ($_POST['tagref'] != '-1') {
        $unidad_negocio = "AND tagref= '" . $_POST['tagref'] . "' ";
    }
    if ($_POST['legalid'] != '-1') {
        $razon_social = "AND  legalid= '" . $_POST['legalid'] . "' ";
    }
    if ($_POST['userauto'] != '*') {
        $usuario_auto = "AND  autorizausuario= '" . $_POST['userauto'] . "' ";
    }

    $SQLComprobanteFiscal = "";
    if (isset($_POST['cmbComprobanteFiscal']) and $_POST['cmbComprobanteFiscal'] != "all") {
        $SQLComprobanteFiscal = " AND comprobanteFiscal = '".$_POST['cmbComprobanteFiscal']."' ";
    }

    $status = explode(',', $strSelEstatus);
    
    $SQL = "SELECT *
            FROM
              (SELECT supptrans_concentrado.conorderno AS orderno,
                      tags.tagname AS locationname,
                      DATE_FORMAT(compras.trandate, '%d-%m-%Y') AS fechasol,
                      NULL AS autorizausuario,
                      NULL AS userauto,
                      suppliers.suppname,
                      suppliers.currcode,
                      ABS(SUM(CASE WHEN invtext IS NOT NULL THEN 0 ELSE CASE WHEN cat_cuenta = 'PROVEEDOR'
                        AND supptrans_concentrado.amount < 0 THEN supptrans_concentrado.amount ELSE 0 END END)) AS total,
                      NULL AS requisitionno,
                      systypescat.typename AS status,
                      NULL AS loccode,
                      suppliers.supplierid,
                      tags.tagref,
                      tags.tagdescription,
                      tags.legalid,
                      'reembolso' AS tipo,
                      supptrans_concentrado.typeorderno,
                      suppliers.comprobanteFiscal,
                      suppliers.taxid
               FROM supptrans_concentrado
               INNER JOIN systypescat ON supptrans_concentrado.typeorderno = systypescat.typeid
               LEFT JOIN supptrans AS compras ON compras.type = supptrans_concentrado.typecargo
               AND compras.transno = supptrans_concentrado.transnocargo
               LEFT JOIN supptrans AS reembolso ON reembolso.type = supptrans_concentrado.typeabono
               AND reembolso.transno = supptrans_concentrado.transnoabono
               LEFT JOIN tags ON compras.tagref = tags.tagref
               LEFT JOIN banktrans ON supptrans_concentrado.transnobank=banktrans.transno
               AND supptrans_concentrado.typebank=banktrans.type
               INNER JOIN suppliers ON compras.supplierno= suppliers.supplierid
               INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref=tags.tagref
               AND sec_unegsxuser.userid='".$_SESSION['UserID']."'
               WHERE 1=1 " . $fechaSQL1 . $SQLComprobanteFiscal . "
               GROUP BY conorderno
               UNION SELECT purchorders.orderno,
                            locations.locationname,
                            DATE_FORMAT(purchorders.orddate, '%d/%m/%Y') AS fechasol,
                            purchorders.autorizausuario,
                            www_users.realname AS userauto,
                            suppliers.suppname,
                            purchorders.currcode,
                            NULL AS total,
                            purchorders.requisitionno,
                            purchorders.status,
                            locations.loccode,
                            purchorders.supplierno,
                            tags.tagref,
                            tags.tagdescription,
                            tags.legalid,
                            'compra' AS tipo,
                            NULL AS typeorderno,
                            suppliers.comprobanteFiscal,
                            suppliers.taxid
               FROM purchorders
               INNER JOIN purchorderdetails ON purchorderdetails.orderno = purchorders.orderno
               INNER JOIN tags ON purchorders.tagref = tags.tagref
               INNER JOIN locations ON purchorders.intostocklocation = locations.loccode
               INNER JOIN suppliers ON purchorders.supplierno = suppliers.supplierid
               INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref=tags.tagref
               AND sec_unegsxuser.userid='".$_SESSION['UserID']."'
               INNER JOIN www_users ON www_users.userid = purchorders.autorizausuario
               WHERE purchorders.status IN ('Authorised',
                                            'Completed')
                 AND (ABS(purchorderdetails.quantityord - purchorderdetails.quantityrecd) >= 0)
                 AND (purchorderdetails.quantityrecd > 0) " . $fechaSQL2 . ") a
            WHERE 1 = 1 $prov $orderno $unidad_negocio $razon_social $usuario_auto $SQLComprobanteFiscal
            ORDER BY tagref,
                     fechasol";

    $result = DB_query($SQL, $db);
    $friendlymes = array(1 => "Ene", 2 => "Feb", 3 => "Mar", 4 => "Abr", 5 => "May", 6 => "Jun", 7 => "Jul", 8 => "Ago", 9 => "Sep", 10 => "Oct", 11 => "Nov", 12 => "Dic");
    $friendlylongmes = array(1 => "Enero", 2 => "Febrero", 3 => "Marzo", 4 => "Abril", 5 => "Mayo", 6 => "Junio", 7 => "Julio", 8 => "Agosto", 9 => "Septiembre", 10 => "Octubre", 11 => "Noviembre", 12 => "Diciembre");
    $friendlyday = array(0 => "Lun", 1 => "Mar", 2 => "Mie", 3 => "Jue", 4 => "Vie", 5 => "Sab", 6 => "Dom");
    $friendlylongday = array(0 => "Lunes", 1 => "Martes", 2 => "Miercoles", 3 => "Jueves", 4 => "Viernes", 5 => "Sabado", 6 => "Domingo");
    
    $diaanterior = '';
    $I = 0;
    $kolor = 0;
    $letra = "10px";
    $indices = 1;
    $repetidor = 0;
    $tagant = 0;
    $kolorfondoant = "1f95c3";
    $colorletraant = "#FFFFFF";
    $flagcolor = 0;
    
    while ($myrow = DB_fetch_array($result)) 
    {
        $sql = "SELECT SUM(purchorderdetails.quantityord * purchorderdetails.unitprice) AS total,
                       sum(CASE WHEN purchorderdetails.quantityord < purchorderdetails.qtyinvoiced THEN 0 ELSE purchorderdetails.quantityord - purchorderdetails.qtyinvoiced END) AS productosfacturados,
                       purchorderdetails.orderno
                FROM purchorderdetails
                WHERE purchorderdetails.orderno = '" . $myrow['orderno'] . "' 
                GROUP BY purchorderdetails.orderno";
        
        $rs = DB_query($sql, $db);
        $detalleproductos = DB_fetch_array($rs);

        $estado = '';
        $filtro_estado = '';

        if($myrow['status'] == 'Completed' and $detalleproductos['productosfacturados'] == 0) {
            $estado = 'Facturada';
            $filtro_estado = 'Facturada';
        } else if ($myrow['status'] == 'Completed' and $detalleproductos['productosfacturados'] != 0) {
            $estado = 'Por Facturar';
            $filtro_estado = 'Por Facturar';
        } else if($myrow['status'] != 'Completed' and $myrow['status'] != 'Authorised') {
            $estado = $myrow['status'];
            $filtro_estado = 'Facturada';
        } else {
            $estado = 'Parcialemente</br>Surtida';
            $filtro_estado = 'Por Facturar';
        }

        if(!in_array($filtro_estado, $status)) {
            continue;
        }


        $sql = "SELECT logpurchorderstatus.userid,
                       MAX(DATE_FORMAT(logpurchorderstatus.registerdate, '%d/%m/%Y')) AS fecha,
                       logpurchorderstatus.orderno
                FROM logpurchorderstatus
                INNER JOIN purchorders ON logpurchorderstatus.orderno = purchorders.orderno
                WHERE logpurchorderstatus.status = 'Authorised'
                  AND logpurchorderstatus.orderno = '" . $myrow['orderno'] . "'
                GROUP BY logpurchorderstatus.orderno
                ORDER BY logpurchorderstatus.registerdate DESC";
        $rs = DB_query($sql, $db);
        $detalleautorizacion = DB_fetch_array($rs);
        
        $SQLgetFolio = "SELECT DISTINCT transno,
                                        suppreference AS folio,
                                        reffiscal,
                                        supptrans.id
                        FROM supptrans
                        INNER JOIN supptransdetails ON supptrans.id=supptransdetails.supptransid
                        WHERE supptransdetails.orderno='" . $myrow['orderno'] . "'
                          AND supptrans.ovamount <> 0
                        ORDER BY detailid DESC LIMIT 1";
        $resultgetfolio = DB_query($SQLgetFolio, $db);
        $getFolios = DB_fetch_array($resultgetfolio);
        $folioref = $getFolios['folio'];
        $transnoref = $getFolios['transno'];
        $reffiscal = $getFolios['reffiscal'];
        // $propietarioid = $myrow['supplierid'];
        $propietarioid=$getFolios['id'];
        if ($_POST['folio'] != '' and $_POST['folio'] != '*') {
            if ($transnoref == $_POST['folio']) {
                $procesar = true;
            } 
            else {
                $procesar = false;
            }
        } 
        else {
            $procesar = true;
        }
        if ($procesar) {
            if ($tagant != $myrow['tagref']) {
                echo "<tr border=1 bgcolor='" . $kolorfondoant . "' style='background-color:" . $kolorfondoant . ";color:" . $colorletraant . ";vertical-align:top'>";
                echo "<td  colspan=16 nowrap bgcolor='" . $kolorfondoant . "' style='background-color:" . $kolorfondoant . ";color:" . $colorletraant . ";vertical-align:top;' class='texto_normal2'>" . $myrow['tagref'] . " - " . $myrow['tagdescription'] . "</td>";
                echo "</tr>";
                $tagant = $myrow['tagref'];
            }
            if ($flagcolor == 1) {
                $kolorfondo = "#CCCCCC";
                $flagcolor = 0;
            } 
            elseif ($flagcolor == 0) {
                $kolorfondo = "#EEEEEE";
                $flagcolor = 1;
            }
            
            /* Validacion para identificar si tiene xml agregados */
            $sqlval = "SELECT XmlsProveedores.idXmls
                    FROM XmlsProveedores
                    WHERE XmlsProveedores.orderno = '" . $myrow['orderno'] . "'";
            $resultval = DB_query($sqlval, $db);
            $tienedocumentos = DB_num_rows($resultval);
            $idxml = 0;
            if ($tienedocumentos > 0) {
                $row_xmlproveedores = DB_fetch_array($resultval);
                $idxml = $row_xmlproveedores['idXmls'];
                $imgadjunto = "subir_xml2_23x23.png";
            } 
            else {
                $imgadjunto = "subir_xml_23x23.png";
            }
            
            echo "<tr border=1 bgcolor='" . $kolorfondo . "' style='background-color:" . $kolorfondo . ";vertical-align:top'>";
            
            // hoja_23x23 PuenteAgregaProductosOCXML.php
            
            if ($myrow['tipo'] == 'reembolso') {
                $PrintPurchOrder = '<a href="' . $rootpath . '/MantenimientoReembolsos.php?' . SID . '&pdf=1&orderno=' . $myrow['orderno'] . '&tipo=' . $myrow['typeorderno'] . '&legalid=' . $myrow['legalid'] . '" target="_blank"><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Imprimir Reembolso') . '" alt="Imprimir"></a>';
            } 
            else {
                $PrintPurchOrder = '<a target="_blank" href="' . $rootpath . '/PO_PDFPurchOrder.php?' . SID . 'OrderNo=' . $myrow['orderno'] . '&tipodocto=25&Tagref=' . $myrow['tagref'] . '&legalid=' . $myrow['legalid'] . '"><img src="' . $rootpath . '/css/' . $theme . '/images/printer.png" title="' . _('Imprimir Orden Compra') . '" alt="Imprimir"></a>';
            }
            
            $SubirArchivo = '<a target="_blank" href="' . $rootpath . '/SubirXMLCPOC.php?' . SID . 'NoOrden=' . $myrow['orderno'] . '&tipodocto=25&Tagref=' . $myrow['tagref'] . '&foilofiscal=' . $reffiscal . '&propietarioid=' . $propietarioid . '"><img src="' . $rootpath . '/images/' . $imgadjunto . '" title="' . _('Subir Archivo XML') . '" alt="Subir Archivo XML"></a>';
            $sqld = "SELECT uuid,
                           total,
                           folio,
                           DATE_FORMAT(fechaemision,'%d')AS dia,
                           DATE_FORMAT(fechaemision,'%m')AS mes,
                           DATE_FORMAT(fechaemision,'%Y')AS YEAR
                    FROM `proveedor_factura`
                    WHERE idsolicitud='" . $myrow['orderno'] . "'
                    ORDER BY id DESC LIMIT 1";
            $resultd = DB_query($sqld, $db);
            $folio = "";
            
            $fuerapanel = false; 

            if (DB_num_rows($resultd) > 0) {
                $myrowd = DB_fetch_array($resultd);
                $uuid = $myrowd['uuid'];
                $total = $myrowd['total'];
                $folio = $myrowd['folio'];
                $fechaemision = $myrowd['fechaemision'];
                $dia = $myrowd['dia'];
                $mes = $myrowd['mes'];
                $year = $myrowd['year'];
            } 
            else if($reffiscal != '') {
                $uuid = $reffiscal;
                $folio = $folioref;
                $fuerapanel = true;
            }
            else {
                $folio = "";
                $uuid = '';
                $total = '';
                $folio = '';
                $fechaemision = '';
                $dia = '';
                $mes = '';
                $year = '';
            }

            $sqlprofac = "SELECT if(sum(qtyinvoiced) = sum(quantityord), 1, 0) AS completo
                        FROM purchorderdetails
                        WHERE orderno = '" . $myrow['orderno'] . "'";
            $rsqlprofac = DB_query($sqlprofac, $db);
            $productosfact = DB_fetch_row($rsqlprofac);

            if(!empty($uuid) and $productosfact[0] == 0) {
                $mostrarlink = true;
            }
            else {
                $mostrarlink = false;
            }

            if ($tienedocumentos >= 1 and empty($uuid) ){
                $PageFactura = 'XML Invalido o Cancelado';
            }
            else if ( $mostrarlink and $tienedocumentos >= 1) {
                $sqlmostraralta = "SELECT if(txmls.xmls = tfacturas.facturas, 0, 1) AS mostraralta
                                    FROM
                                      (SELECT count(folio) AS xmls
                                       FROM `proveedor_factura`
                                       WHERE idsolicitud='" . $myrow['orderno'] . "') AS txmls,

                                      (SELECT count(DISTINCT suppreference) AS facturas
                                       FROM supptrans
                                       INNER JOIN supptransdetails ON supptrans.id=supptransdetails.supptransid
                                       INNER JOIN proveedor_factura ON proveedor_factura.folio = supptrans.suppreference
                                       WHERE supptransdetails.orderno='" . $myrow['orderno'] . "'
                                         AND supptrans.ovamount <> 0) AS tfacturas";
                $rsqlmostraralta = DB_query($sqlmostraralta, $db);
                $mostraralta = DB_fetch_array($rsqlmostraralta);
                if($mostraralta['mostraralta'] == 1) {

                    $sqlerror = "SELECT COUNT(idxmlobserv) AS terrores
                                FROM xml_observaciones
                                INNER JOIN xml_catalogo_error ON xml_catalogo_error.CodigoError = xml_observaciones.codigoerror
                                AND xml_catalogo_error.tipo = xml_observaciones.tipo
                                WHERE xml_observaciones.idxml ='" . $idxml . "'
                                  AND (xml_catalogo_error.status > 1
                                       OR xml_catalogo_error.status ='Error')";
                    $rs_sqlerror = DB_query($sqlerror, $db);
                    $row_sqlerror = DB_fetch_array($rs_sqlerror);

                    if($row_sqlerror['terrores'] > 0) {
                        $PageFactura = "Xml con " . $row_sqlerror['terrores'] . " errores.";
                    } else {
                        $PageFactura = '<a target="_blank" href="' . $rootpath . '/SupplierInvoice.php?' . SID . '&SupplierID=' . $myrow['supplierid'] . '&unidadnegocio=' . $myrow['tagref'] . '&uuid=' . $uuid . '&folio=' . $folio . '&total=' . $total . '&dia=' . $dia . '&mes=' . $mes . '&year=' . $year . '&CPOC=yes&orderno=' . $myrow['orderno'] . '&panel=1"><img src="' . $rootpath . '/images/hoja_23x23.png" title="' . _('Alta Factura') . '" alt="Imprimir"></a>';
                    }
                }
                else {
                    $PageFactura = 'Subir Xml';
                }
            } 
            else if ( !empty($folioref) and !empty($folio) and !empty($uuid) ) {  
                if($fuerapanel) {
                    $PageFactura = 'Facturada Fuera de Panel';
                }
                else {
                    $PageFactura = 'Facturada';
                }
                
                $sqlfacturas = "SELECT DISTINCT transno,
                                                suppreference AS folio,
                                                ovamount
                                FROM supptrans
                                INNER JOIN supptransdetails ON supptrans.id=supptransdetails.supptransid
                                WHERE supptransdetails.orderno='" . $myrow['orderno'] . "'
                                  AND supptrans.ovamount <> 0
                                ORDER BY detailid DESC";
                $rsqlfacturas = DB_query($sqlfacturas,$db);
                if(DB_num_rows($rsqlfacturas) > 0) {
                    $transnoref = '';
                    $folio = '';
                    while($facturas = DB_fetch_array($rsqlfacturas)) {
                        $transnoref .= $facturas['transno'] . '<br/>';
                        $folio .= $facturas['folio'] . '<br/>';
                    }
                }
            }     
            else if ($tienedocumentos == 0){
                $PageFactura = 'Sin XML';
            }

            if($estado == '') {
                $estadofactura = $myrow['status'];
            }
            else {
                $estadofactura = $estado;
            }
            
            // echo 'archivo'. $SubirArchivo;
            echo "<td bgcolor='" . $kolorfondo . "' style='background-color:" . $kolorfondo . ";vertical-align:top;' class='texto_normal2'>" . $indices . "</td>";
            echo "<td bgcolor='" . $kolorfondo . "' style='background-color:" . $kolorfondo . ";vertical-align:top;' class='texto_normal2'>" . $myrow['orderno'] . "</td>";
            echo "<td bgcolor='" . $kolorfondo . "' style='background-color:" . $kolorfondo . ";vertical-align:top;' class='texto_normal2'>" . $myrow['locationname'] . "</td>";
            echo "<td bgcolor='" . $kolorfondo . "' style='background-color:" . $kolorfondo . ";vertical-align:top;' class='texto_normal2'>" . $myrow['fechasol'] . "</td>";
            echo "<td bgcolor='" . $kolorfondo . "' style='background-color:" . $kolorfondo . ";vertical-align:top;' class='texto_normal2'>" . $detalleautorizacion['fecha'] . "</td>";
            echo "<td bgcolor='" . $kolorfondo . "' style='background-color:" . $kolorfondo . ";vertical-align:top;' class='texto_normal2'>" . $myrow['userauto'] . "</td>";
            echo "<td bgcolor='" . $kolorfondo . "' style='background-color:" . $kolorfondo . ";vertical-align:top;' class='texto_normal2'>" . $myrow['suppname'] . "</td>";
            echo "<td bgcolor='" . $kolorfondo . "' style='background-color:" . $kolorfondo . ";vertical-align:top;' class='texto_normal2'>" . $myrow['taxid'] . "</td>";
            echo "<td bgcolor='" . $kolorfondo . "' style='background-color:" . $kolorfondo . ";vertical-align:top;' class='texto_normal2'>" . $myrow['currcode'] . "</td>";
            echo "<td bgcolor='" . $kolorfondo . "' style='background-color:" . $kolorfondo . ";vertical-align:top;text-rigth;' class='texto_normal2'>" . number_format($detalleproductos['total'], 2) . "</td>";
            echo "<td bgcolor='" . $kolorfondo . "' style='background-color:" . $kolorfondo . ";vertical-align:top;' class='texto_normal2'>" . $myrow['requisitionno'] . "</td>";
            echo "<td bgcolor='" . $kolorfondo . "' style='background-color:" . $kolorfondo . ";vertical-align:top;' class='texto_normal2'>" . $estadofactura . "</td>";
            echo "<td bgcolor='" . $kolorfondo . "' style='background-color:" . $kolorfondo . ";vertical-align:top;' class='texto_normal2'>" . $transnoref . "</td>";
            echo "<td bgcolor='" . $kolorfondo . "' style='background-color:" . $kolorfondo . ";vertical-align:top;' class='texto_normal2'>" . $folio . "</td>";
            echo "<td bgcolor='" . $kolorfondo . "' style='background-color:" . $kolorfondo . ";vertical-align:top;' class='texto_normal2'>" . $PrintPurchOrder . "</td>";
            echo "<td bgcolor='" . $kolorfondo . "' style='background-color:" . $kolorfondo . ";vertical-align:top;text-aling:center;' class='texto_normal2'>" . $SubirArchivo . "</td>";
            echo "<td bgcolor='" . $kolorfondo . "' style='background-color:" . $kolorfondo . ";vertical-align:top;text-aling:center;' class='texto_normal2'>" . $PageFactura . "</td>";
            echo "</tr>";
            $indices = $indices + 1;
        }
        
        // }
        
    }
    
    /*
     * echo "<tr>"; echo "<td colspan=3 bgcolor='".$kolorfondo."' style='background-color:".$kolorfondo.";vertical-align:top;' class='texto_normal2'>"._('Total Documentos')."</td>"; echo "<td colspan=7 bgcolor='".$kolorfondo."' style='background-color:".$kolorfondo.";vertical-align:top;' class='numero_normal2'>".$indices."</td>"; echo "</tr>";
    */
    echo "</table>";
    echo '<table cellspacing=0 border=0 bordercolor=lightgray cellpadding=0 colspan=2 style="margin-top:0">';
    echo '<tr><td style="text-align:center">';
    echo "<br>";
    
    $ligabotones = "";
    for ($idxmenu = 0; $idxmenu < count($menuelem); $idxmenu++) {
        if (isset($_POST[$menuelem[$idxmenu]])) {
            echo '<input type="hidden" name="' . $menuelem[$idxmenu] . '" value="' . $menuelemName[$idxmenu] . '" />';
            $ligabotones = "&" . $menuelem[$idxmenu] . "=1";
        }
    }
    echo '</td></tr>';
    echo '</table>';
}
echo '<br>';
 // agenda semanal
include ('includes/footer.inc');
?>

<script language="JavaScript">
function newPage(url){
window.open(url,"_blank");
}
</script>
