<?php

include ('includes/session.inc');
$funcion = 1273;
include ('includes/SecurityFunctions.inc');

if (! isset ( $_POST ['PrintEXCEL'] )) {
	include ('includes/header.inc');
}

echo '<script language="JavaScript">';
    echo 'function autocheck(objeto){';
            echo 'alert("Valor: " + objeto.name);';
    echo '}';
echo '</script>';


if (isset ( $_POST ['FromYear'] )) {
	$FromYear = $_POST ['FromYear'];
} else {
	$FromYear = date ( 'Y' );
}
if (isset ( $_POST ['FromMes'] )) {
	$FromMes = $_POST ['FromMes'];
} else {
	$FromMes = date ( 'm' );
}

if (isset ( $_POST ['FromDia'] )) {
	$FromDia = $_POST ['FromDia'];
} else {
	$FromDia = date ( 'd' );
}

if (isset ( $_POST ['ToYear'] )) {
	$ToYear = $_POST ['ToYear'];
} else {
	$ToYear = date ( 'Y' );
}

if (isset ( $_POST ['ToMes'] )) {
	$ToMes = $_POST ['ToMes'];
} else {
	$ToMes = date ( 'm' );
}
if (isset ( $_POST ['ToDia'] )) {
	$ToDia = $_POST ['ToDia'];
} else {
	$ToDia = date ( 'd' );
}

if (isset ( $_POST ['ToYear'] )) {
	$ToYear = $_POST ['ToYear'];
} else {
	$ToYear = date ( 'Y' );
}

if (isset ( $_POST ['FromYearMov'] )) {
	$FromYearMov = $_POST ['FromYearMov'];
} else {
	$FromYearMov = date ( 'Y' );
}
if (isset ( $_POST ['FromMesMov'] )) {
	$FromMesMov = $_POST ['FromMesMov'];
} else {
	$FromMesMov = date ( 'm' );
}

if (isset ( $_POST ['FromDiaMov'] )) {
	$FromDiaMov = $_POST ['FromDiaMov'];
} else {
	$FromDiaMov = date ( 'd' );
}

if ((isset ( $_POST ["checkBanco"] )) or ! isset ( $_POST ['ToDia'] )) {
	$checkbanco = 'checked';
}
if ((isset ( $_POST ["checkCxC"] )) or ! isset ( $_POST ['ToDia'] )) {
	$checkCxC = 'checked';
}

if ((isset ( $_POST ["checkCxP"] )) or ! isset ( $_POST ['ToDia'] )) {
	$checkCxP = 'checked';
}

if ((isset ( $_POST ["checkP"] )) or ! isset ( $_POST ['ToDia'] )) {
	$checkP = 'checked';
}

include ('includes/FlujoEfectivoFunctions_V6_0.inc');

$permisocambiafecha = Havepermission ( $_SESSION ['UserID'], 1274, $db );
$Linkdocto = '<a target="_blank" href="ABC_TypesbyReport.php?functionid=' . $funcion . '">';

$fechaini = rtrim ( $FromYear ) . '-' . rtrim ( $FromMes ) . '-' . rtrim ( $FromDia );
$fechafin = rtrim ( $ToYear ) . '-' . rtrim ( $ToMes ) . '-' . rtrim ( $ToDia );

$fechainic = mktime ( 0, 0, 0, rtrim ( $FromMes ), rtrim ( $FromDia ), rtrim ( $FromYear ) );
$fechafinc = mktime ( 23, 59, 59, rtrim ( $ToMes ), rtrim ( $ToDia ), rtrim ( $ToYear ) );

$fechaini = rtrim ( $FromYear ) . '-' . add_ceros ( rtrim ( $FromMes ), 2 ) . '-' . add_ceros ( rtrim ( $FromDia ), 2 );
$fechafin = rtrim ( $ToYear ) . '-' . add_ceros ( rtrim ( $ToMes ), 2 ) . '-' . add_ceros ( rtrim ( $ToDia ), 1 ) . ' 23:59:59';

if (! isset ( $_POST ['PrintEXCEL'] )) {
	echo "<form name='FDatosB' action='" . $_SERVER ['PHP_SELF'] . '?' . SID . "' method='POST'><table style='margin:auto;'>";
}

function splitcadena($texto){
	/*if(strpos($texto,' ') === false){
		$texto = str_replace('-', ' - ', $texto);
		$texto = str_replace('_', ' _ ', $texto);
	}*/
	$texto = substr($texto, 0, 30);
	return $texto;
	
}


/*******CAMPOS PARA MOSTRAR PROYECCIONES APARTIR DE UNA FECHA*****/
if (isset ($_POST ['FromYearProy'] )) {
	$FromYearProy = $_POST ['FromYearProy'];
} else {
	$FromYearProy = date ( 'Y' );
}
if (isset ( $_POST ['FromMesProy'] )) {
	$FromMesProy = $_POST ['FromMesProy'];
} else {
	$FromMesProy = date ( 'm' );
}
if (strlen($FromMesProy) == 1){
	$FromMesProy = "0" . $FromMesProy;
}

if (isset ( $_POST ['FromDiaProy'] )) {
	$FromDiaProy = $_POST ['FromDiaProy'];
} else {
	$FromDiaProy = date ( 'd' );
}
if (strlen($FromDiaProy) == 1){
	$FromDiaProy = "0" . $FromDiaProy;
}

$FechaProy = $FromYearProy . "-" . $FromMesProy . "-" . $FromDiaProy . " 00:00:00.000";



if (! isset ( $_POST ['PrintEXCEL'] )) {
	$title = $funcion . ' - ' . _ ( 'Administracion de flujo de efectivo' );	
	
	echo '<p class="page_title_text">' . $Linkdocto . '<img src="' . $rootpath . '/images/imgs/flujo.png" title="' . _ ( 'Flujo Efectivo' ) . '" alt="" height=16 width=16 ></a>' . ' ' . $title . '<br>';
	echo '<table border="0" style="margin:auto;">';
	echo '<tr>';
	echo '<td colspan=2>';
	echo '&nbsp;</td>
		</tr>';
	echo '<tr>';
	echo '<td colspan=2>
	      <table border="0" style="margin:auto;">
	    	<tr>';
	echo '<td>' . _ ( 'Desde:' ) . '</td>
		  <td><select Name="FromDia">';
	$sql = "SELECT * FROM cat_Days";
	$dias = DB_query ( $sql, $db );
	while ( $myrowdia = DB_fetch_array ( $dias, $db ) ) {
		$diabase = $myrowdia ['DiaId'];
		if (rtrim ( intval ( $FromDia ) ) == rtrim ( intval ( $diabase ) )) {
			echo '<option  VALUE="' . $myrowdia ['DiaId'] . '" selected>' . $myrowdia ['Dia'] . '</option>';
		} else {
			echo '<option  VALUE="' . $myrowdia ['DiaId'] . '">' . $myrowdia ['Dia'] . '</option>';
		}
	}
	echo '</select></td>';
	echo '<td><select Name="FromMes">';
	$sql = "SELECT * FROM cat_Months";
	$Meses = DB_query ( $sql, $db );
	while ( $myrowMes = DB_fetch_array ( $Meses, $db ) ) {
		$Mesbase = $myrowMes ['u_mes'];
		if (rtrim ( intval ( $FromMes ) ) == rtrim ( intval ( $Mesbase ) )) {
			echo '<option  VALUE="' . $myrowMes ['u_mes'] . '  " selected>' . $myrowMes ['mes'] . '</option>';
		} else {
			echo '<option  VALUE="' . $myrowMes ['u_mes'] . '" >' . $myrowMes ['mes'] . '</option>';
		}
	}
	echo '</select>';
	echo '&nbsp;<input name="FromYear" type="text" size="4" value=' . $FromYear . '>';
	echo '</td><td> &nbsp;</td>
		  <td>' . _ ( 'Hasta:' ) . '</td>';
	echo '<td><select Name="ToDia">';
	$sql = "SELECT * FROM cat_Days";
	$Todias = DB_query ( $sql, $db );
	
	while ( $myrowTodia = DB_fetch_array ( $Todias, $db ) ) {
		$Todiabase = $myrowTodia ['DiaId'];
		if (rtrim ( intval ( $ToDia ) ) == rtrim ( intval ( $Todiabase ) )) {
			echo '<option  VALUE="' . $myrowTodia ['DiaId'] . '  " selected>' . $myrowTodia ['Dia'] . '</option>';
		} else {
			echo '<option  VALUE="' . $myrowTodia ['DiaId'] . '" >' . $myrowTodia ['Dia'] . '</option>';
		}
	}
	echo '</select></td>';
	echo '<td>';
	echo '<select Name="ToMes">';
	$sql = "SELECT * FROM cat_Months";
	$ToMeses = DB_query ( $sql, $db );
	
	while ( $myrowToMes = DB_fetch_array ( $ToMeses, $db ) ) {
		$ToMesbase = $myrowToMes ['u_mes'];
		if (rtrim ( intval ( $ToMes ) ) == rtrim ( intval ( $ToMesbase ) )) {
			echo '<option  VALUE="' . $myrowToMes ['u_mes'] . '  " selected>' . $myrowToMes ['mes'] . '</option>';
		} else {
			echo '<option  VALUE="' . $myrowToMes ['u_mes'] . '" >' . $myrowToMes ['mes'] . '</option>';
		}
	}
	echo '</select>';
	echo '&nbsp;<input name="ToYear" type="text" size="4" value=' . $ToYear . '>';
	echo '</td>';
	echo '</tr>';
	echo '</table>';
	echo '</td>';
	echo '</tr>';
	
	echo '<tr>';
	echo '<td colspan=2>';
	echo '&nbsp;</td></tr>';
	// Select the razon social
	echo '<tr><td><b>' . _ ( 'X Razon Social:' ) . '</b><td>
			<select name="legalid">';
	echo "<option selected value='0'>Todas las Razones";
	// /Pinta las razones sociales
	$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
	$SQL = $SQL . " FROM sec_unegsxuser u,tags t
						JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid";
	$SQL = $SQL . " WHERE u.tagref = t.tagref ";
	$SQL = $SQL . " and u.userid = '" . $_SESSION ['UserID'] . "'
			  	GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY t.legalid";
	$result = DB_query ( $SQL, $db );
	while ( $myrow = DB_fetch_array ( $result ) ) {
		if (isset ( $_POST ['legalid'] ) and $_POST ['legalid'] == $myrow ["legalid"]) {
			echo '<option selected value=' . $myrow ['legalid'] . '>' . $myrow ['legalid'] . ' - ' . $myrow ['legalname'];
		} else {
			echo '<option value=' . $myrow ['legalid'] . '>' . $myrow ['legalid'] . ' - ' . $myrow ['legalname'];
		}
	}
	echo '</select>';
	echo " <input type=submit name='selLegalid' VALUE='" . _ ( '->' ) . "'>";
	echo '</td>';
	// End select Razon Social
	
	/* SELECCION DE LA UNIDAD DE NEGOCIO DE PRODUCTOS */
	echo "<tr><td><b>" . _ ( 'X Unidad de Negocio' ) . ":</b></td><td>";
	echo "<select name='unidadnegocio'>";
	$SQL = "SELECT  t.tagref, CONCAT(t.tagref,' - ',t.tagdescription) as tagdescription "; // areas.areacode, areas.areadescription";
	$SQL = $SQL . " FROM sec_unegsxuser u,tags t join areas ON t.areacode = areas.areacode ";
	$SQL = $SQL . " WHERE u.tagref = t.tagref ";
	$SQL = $SQL . " and u.userid = '" . $_SESSION ['UserID'] . "'";
	
	if (isset ( $_POST ['legalid'] ) && $_POST ['legalid'] != '-1') {
		$SQL = $SQL . " AND legalid=" . $_POST ['legalid'];
	}
	$SQL = $SQL . " ORDER BY tagdescription";
	$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
	$TransResult = DB_query ( $SQL, $db, $ErrMsg );
	echo "<option selected value='-1'>Todas a las que tengo accceso...</option>";
	while ( $myrow = DB_fetch_array ( $TransResult ) ) {
		if ($myrow ['tagref'] == $_POST ['unidadnegocio']) {
			echo "<option selected value='" . $myrow ['tagref'] . "'>" . $myrow ['tagdescription'] . "</option>";
		} else {
			echo "<option value='" . $myrow ['tagref'] . "'>" . $myrow ['tagdescription'] . "</option>";
		}
	}
	echo "</select>";
	echo "</td></tr>";
	// filtro por cuenta de banco
	echo '<tr>';
	
	echo '<td align=left nowrap><b>' . _ ( 'Cuenta Bancaria' ) . ':</b></td><td ><select tabindex="1" name="BankAccount">';
	$sql = 'SELECT bankaccountname,
								bankaccounts.accountcode,
								bankaccounts.currcode
						FROM bankaccounts, chartmaster, tagsxbankaccounts, sec_unegsxuser
						WHERE bankaccounts.accountcode=chartmaster.accountcode and
							  bankaccounts.accountcode = tagsxbankaccounts.accountcode and
							  tagsxbankaccounts.tagref = sec_unegsxuser.tagref and
								sec_unegsxuser.userid = "' . $_SESSION ['UserID'] . '"';
	if (isset ( $_POST ['legalid'] ) && $_POST ['legalid'] != '-1') {
		$sql = $sql . " AND tagsxbankaccounts.tagref in (select tagref from tags where legalid=" . $_POST ['legalid'] . " )";
	}
	$sql = $sql . ' GROUP BY bankaccountname,
					bankaccounts.accountcode,
					bankaccounts.currcode';
	$resultBankActs = DB_query ( $sql, $db );
	echo "<option selected value='-1'>Todas las cuentas...</option>";
	while ( $myrow = DB_fetch_array ( $resultBankActs ) ) {
		if (isset ( $_POST ['BankAccount'] ) and $myrow ["accountcode"] == $_POST ['BankAccount']) {
			echo "<option selected Value='" . $myrow ['accountcode'] . "'>" . $myrow ['bankaccountname'];
		} else {
			echo "<option Value='" . $myrow ['accountcode'] . "'>" . $myrow ['bankaccountname'];
		}
	}
	
	echo '</select>';
	echo '<font  size=1px style="text-align:center" color=red>(*) Aplica solo para Bancos</font>';
	
	echo " </td>";
	echo '</tr>';
	//aqui agregue
	echo "<tr><td><b>" . _ ( 'X Subcategoria' ) . ":</b></td>";
	echo "<td>";
	$SQL = "SELECT sc.subcat_id, sc.subcat_name, ca.cat_name, ca.cat_id FROM fjoSubCategory sc
			left join fjoCategory ca ON sc.cat_id = ca.cat_id
			ORDER BY ca.order, sc.order, sc.subcat_name";
	$result=DB_query($SQL,$db);
	echo '<select name="subcategoria_">
	<option selected value="-1">Selecciona una Subcategoria..</option>';
	$catant="";
	while ($myrow=DB_fetch_array($result)){
		//echo '<option selected value=' . $myrow['subcat_id'] . '>' . $myrow['subcat_id'] . ' - ' . substr($myrow['subcat_name'],0,40) . '</option>';
		if($catant != $myrow['cat_id']){
			echo '<option value=' . $myrow['subcat_id'] . '>*****' . substr($myrow['cat_name'],0,40) . '*****</option>';
			$catant = $myrow['cat_id'];
		}
		if ($_POST['subcategoria_']==$myrow['subcat_id']){
			echo '<option selected value=' . $myrow['subcat_id'] . '>' . $myrow['subcat_id'] . ' - ' . substr($myrow['subcat_name'],0,40) . '</option>';
		} else {
			echo '<option value=' . $myrow['subcat_id'] . '>' . $myrow['subcat_id'] . ' - ' . substr($myrow['subcat_name'],0,40)  . '</option>';
		}
		
		
		
		
		
	}
			
			echo '</select>';
			
			
/********/
	//echo "<br>" . $FechaProy . "<br>";
	echo '<input type="hidden" name="FromYearProy" value="' . $FromYearProy . '">';
	echo '<input type="hidden" name="FromMesProy" value="' . $FromMesProy . '">';
	echo '<input type="hidden" name="FromDiaProy" value="' . $FromDiaProy . '">';
			
/*****************************************************************/
						
	echo "</td>
	</tr>";
	//***********		
	echo "<tr>";
	echo "<td colspan='2'>";
	echo "<table border='0' style='margin:auto;'>";
	echo "<tr>";
	echo "<td><input tabindex='6' type='submit' name='ReportePantalla' value='" . _ ( 'Mostrar en Pantalla' ) . "'></td>";
	echo "<td><input tabindex='7' type='hidden' name='PrintPDF' value='" . _ ( 'Imprime Archivo PDF' ) . "'></td>";
	echo "<td><input tabindex='7' type='submit' name='PrintEXCEL' value='" . _ ( 'Exportar a Excel' ) . "'></td>";
	echo "<td><input tabindex='7' type='submit' name='AltaMovimiento' value='" . _ ( 'Alta Movimiento Proyeccion' ) . "'></td>";
	echo "<td><input tabindex='7' type='submit' name='ActualizaFechaNula' value='" . _ ( 'Actualiza Documentos Sin Fecha' ) . "'></td>";
	
	echo "</tr>";
	echo "</table>";
	echo "</td>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td colspan='2'>";
	echo '<table>';
	echo '<tr>';
	
	echo '<td style="background-color:lightgreen;text-align:right;">
			<input type="checkbox" ' . $checkbanco . ' name="checkBanco">
		  <td>';
	echo '<td>' . _ ( '(B) Banco' ) . '<td>';
	
	echo '<td style="background-color:#F3F781;text-align:right;">
			<input type="checkbox" ' . $checkCxC . ' name="checkCxC">
		 <td>';
	echo '<td>' . _ ( '(CxC) Cuenta x Cobrar' ) . '<td>';
	
	echo '<td style="background-color:#Fa9090;text-align:right;">
			<input type="checkbox" ' . $checkCxP . ' name="checkCxP">
		  <td>';
	echo '<td>' . _ ( '(CxP) Cuenta x Pagar' ) . '<td>';
	
	echo '<td style="background-color:#FAAC58;text-align:right;">
			<input type="checkbox" ' . $checkP . ' name="checkP">
		  <td>';
	echo '<td>' . _ ( '(P) Proyeccion' ) . '<td>';
	
	echo '</tr>';
	echo '</table>';
	echo "</td>";
	echo "</tr>";
	
	echo '</table>';
} // fin de formulario para extraer datos

if (isset ( $_POST ['ReportePantalla'] ) or isset ( $_POST ['PrintEXCEL'] )) {
	if (isset ( $_POST ['PrintEXCEL'] )) {
            header ( "Content-type: application/ms-excel" );
            // replace excelfile.xls with whatever you want the filename to default to
            header ( "Content-Disposition: attachment; filename=Reportedeflujov2.xls" );
            header ( "Pragma: no-cache" );
            header ( "Expires: 0" );
            echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
                    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

            echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
            echo '<link rel="shortcut icon" href="' . $rootpath . '/favicon.ico" />';
            echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';
	}
        
	// mostrar movimientos de banco
	$SQLBanco = "SELECT  banktrans.banktransid AS id,
                            banktrans.type as tipo,
                            banktrans.transno as transno,
                            case when accountname is null then banktrans.bankact else (accountname) end as cuentacontable,
                            banktrans.bankact as codigoctacontable,
                            CONCAT(banktrans.banktranstype,'=>',banktrans.ref) as referencias,
                            SUM(banktrans.amount*-1) as subtotal,
                            0 as iva,
                            banktrans.currcode as moneda,
                            banktrans.chequeno as cheque,
                            banktrans.beneficiary as beneficiario,
                            banktrans.transdate as fechaemision,
                            banktrans.transdate as fechapromesa,
                            tags.tagdescription,
                            tags.tagref,
                            systypescat.typename,
                            'B' as tipomovimiento,
                            SUM(banktrans.amount*-1) as saldo,
                            'lightgreen' as color,
                            0 as modificar,
                            0 AS eliminar,
                            IFNULL(banktrans_ext.subcat_id, 0) as subcategoria,
                            CASE WHEN banktrans.type = 22 THEN supptrans.settled ELSE 1 END as aplicado
			FROM banktrans
                        LEFT JOIN banktrans_ext ON banktrans.banktransid = banktrans_ext.banktransid   
                        INNER JOIN tagsxbankaccounts ON tagsxbankaccounts.accountcode = banktrans.bankact
                            AND tagsxbankaccounts.tagref = banktrans.tagref
                            AND tagsxbankaccounts.tagref in (select tagref from tags where (legalid=" . $_POST ['legalid'] . " or '" . $_POST ['legalid'] . "' = '0' ))
                        left join supptrans ON banktrans.type = supptrans.type and banktrans.transno = supptrans.transno   
                        INNER JOIN tags ON tags.tagref=banktrans.tagref
                        INNER JOIN systypescat ON systypescat.typeid=banktrans.type
                        INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
                        INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION ['UserID'] . "'
                        LEFT JOIN chartmaster on chartmaster.accountcode=banktrans.bankact
			WHERE banktrans.transdate >= '" . $fechaini . "' AND banktrans.transdate <= '" . $fechafin . "'
                            AND abs(banktrans.amount)!=0	
                            /*AND banktrans.ref NOT LIKE '%Cance%'*/";
	if (isset ( $_POST ['legalid'] ) && $_POST ['legalid'] != '0') {
		$SQLBanco = $SQLBanco . " AND tags.legalid=" . $_POST ['legalid'];
	}
	if (isset ( $_POST ['unidadnegocio'] ) && $_POST ['unidadnegocio'] != '-1') {
		$SQLBanco = $SQLBanco . " AND tags.tagref=" . $_POST ['unidadnegocio'];
	}
	if (isset ( $_POST ['BankAccount'] ) && $_POST ['BankAccount'] != '-1') {
		$SQLBanco = $SQLBanco . " AND banktrans.bankact='" . $_POST ['BankAccount'] . "'";
	}
	if (isset ( $_POST ['subcategoria_'] ) && $_POST ['subcategoria_'] != '-1') {
		$SQLBanco = $SQLBanco . " AND IFNULL(banktrans_ext.subcat_id,0)=" . $_POST ['subcategoria_'];
	}
	
	$SQLBanco = $SQLBanco . " GROUP BY banktrans.type,
	                            banktrans.transno,
	                            case when accountname is null then banktrans.bankact else (accountname) end,
	                            banktrans.bankact,
	                            banktrans.banktranstype,
	                            banktrans.currcode,
	                            banktrans.chequeno,
	                            banktrans.beneficiary,
	                            banktrans.transdate,
	                            banktrans.transdate,
	                            tags.tagdescription,
	                            tags.tagref,
	                            systypescat.typename,
	                            IFNULL(banktrans_ext.subcat_id, 0),
	                            CASE WHEN banktrans.type = 22 THEN supptrans.settled ELSE 1 END
                            HAVING SUM(banktrans.amount*-1) <> 0";
                            //echo "<br>SQL: " . $SQLBanco;
	/// Consulta movimientos de cxp
	$SQLCxP = "	SELECT  supptrans.id AS id,
					supptrans.type as tipo,
					supptrans.transno as transno,
					'' as cuentacontable,
					'' as codigoctacontable,
					supptrans.suppreference as referencias,
					supptrans.ovamount as subtotal,
					supptrans.ovgst  as iva,
					supptrans.currcode as moneda,
					'' as cheque,
					concat(suppliers.supplierid,' - ',suppname) as beneficiario,
					supptrans.trandate as fechaemision,
					supptrans.promisedate as fechapromesa,
					tags.tagdescription,
					tags.tagref,
					systypescat.typename,
					'CXP' as tipomovimiento,
					(((supptrans.ovamount+supptrans.ovgst)-alloc)/rate) as saldo,
					'#Fa9090' as color,
					1 as modificar,
					0 AS eliminar,
					IFNULL(supptrans_ext.subcat_id, 0) as subcategoria,
					1 as aplicado
					
			FROM supptrans
                                                                LEFT JOIN (SELECT * FROM banktrans WHERE ref LIKE '%cance%') banco ON supptrans.type= banco.type AND supptrans.transno= banco.transno
				LEFT JOIN supptrans_ext ON supptrans.id = supptrans_ext.id 
				INNER JOIN systypesbyreport ON systypesbyreport.typedoc=supptrans.type and systypesbyreport.functionid=" . $funcion . "
				INNER JOIN suppliers ON supptrans.supplierno=suppliers.supplierid 			
				INNER JOIN tags ON tags.tagref=supptrans.tagref
				INNER JOIN systypescat ON systypescat.typeid=supptrans.type
				INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
			  	INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION ['UserID'] . "'
			WHERE supptrans.promisedate >= '" . $fechaini . "' AND supptrans.promisedate <= '" . $fechafin . "'
					AND abs(((supptrans.ovamount+supptrans.ovgst)-alloc))>0.99	

		 ";
	if (isset ( $_POST ['legalid'] ) && $_POST ['legalid'] != '0') {
		$SQLCxP = $SQLCxP . " AND tags.legalid=" . $_POST ['legalid'];
	}
	if (isset ( $_POST ['unidadnegocio'] ) && $_POST ['unidadnegocio'] != '-1') {
		$SQLCxP = $SQLCxP . " AND tags.tagref=" . $_POST ['unidadnegocio'];
	}
	if (isset ( $_POST ['subcategoria_'] ) && $_POST ['subcategoria_'] != '-1') {
		$SQLCxP = $SQLCxP . " AND IFNULL(supptrans_ext.subcat_id,0)=" . $_POST ['subcategoria_'];
	}
                               $SQLCxP = $SQLCxP .' AND banco.type IS NULL ';
	// consulta movimientos de cxc
	$SQLCxC = "	SELECT  debtortrans.id AS id,
					debtortrans.type as tipo,
					debtortrans.transno as transno,
					'' as cuentacontable,
					'' as codigoctacontable,
					debtortrans.folio as referencias,
					debtortrans.ovamount as subtotal,
					debtortrans.ovgst  as iva,
					debtortrans.currcode as moneda,
					'' as cheque,
					concat(debtorsmaster.debtorno,' - ',debtorsmaster.name) as beneficiario,
					debtortrans.origtrandate as fechaemision,
					debtortrans.duedate as fechapromesa,
					tags.tagdescription,
					tags.tagref,
					systypescat.typename,
					'CXC' as tipomovimiento,
					((case when debtortrans.ovamount+debtortrans.ovgst>0 
						then ((debtortrans.ovamount+debtortrans.ovgst)-alloc) *-1 
						else ((debtortrans.ovamount+debtortrans.ovgst)-alloc)*-1 end) / debtortrans.rate) as saldo,
					'#F3F781' as color,
					1 as modificar,
					0 AS eliminar,
					IFNULL(debtortrans_ext.subcat_id, 0) as subcategoria,
					1 as aplicado
					
			FROM debtortrans
                                                                LEFT JOIN (SELECT * FROM banktrans WHERE ref LIKE '%cance%') banco ON debtortrans.type= banco.type AND debtortrans.transno= banco.transno
				LEFT JOIN debtortrans_ext ON debtortrans.id = debtortrans_ext.id 
				INNER JOIN systypesbyreport ON systypesbyreport.typedoc=debtortrans.type and systypesbyreport.functionid=" . $funcion . "
				INNER JOIN debtorsmaster ON debtortrans.debtorno=debtorsmaster.debtorno
				INNER JOIN tags ON tags.tagref=debtortrans.tagref
				INNER JOIN systypescat ON systypescat.typeid=debtortrans.type
				INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
			  	INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION ['UserID'] . "'
			WHERE debtortrans.duedate >= '" . $fechaini . "' 
					AND debtortrans.duedate <= '" . $fechafin . "'
					AND debtortrans.type <> 200
					AND abs(((debtortrans.ovamount+debtortrans.ovgst)-alloc))>.99	
		 ";
	if (isset ( $_POST ['legalid'] ) && $_POST ['legalid'] != '0') {
		$SQLCxC = $SQLCxC . " AND tags.legalid=" . $_POST ['legalid'];
	}
	if (isset ( $_POST ['unidadnegocio'] ) && $_POST ['unidadnegocio'] != '-1') {
		$SQLCxC = $SQLCxC . " AND tags.tagref=" . $_POST ['unidadnegocio'];
	}
	if (isset ( $_POST ['subcategoria_'] ) && $_POST ['subcategoria_'] != '-1') {
		$SQLCxC = $SQLCxC . " AND IFNULL(debtortrans_ext.subcat_id,0)=" . $_POST ['subcategoria_'];
	}
                            $SQLCxC = $SQLCxC .' AND banco.type IS NULL ';
	
	// consulta movimientos de proyeccion
	$SQLProy = "	SELECT  id AS id,
					id as tipo,
					id as transno,
					'' as cuentacontable,
					'' as codigoctacontable,
					referencia as referencias,
					amount as subtotal,
					0  as iva,
					fjo_Movimientos.currcode as moneda,
					'' as cheque,
					concepto as beneficiario,
					fjo_Movimientos.fecha as fechaemision,
					fjo_Movimientos.fechapromesa  as fechapromesa,
					tags.tagdescription,
					tags.tagref,
					'Proyeccion' as typename,
					'P' as tipomovimiento,
					amount as saldo,
					'#FAAC58' as color,
					1 as modificar,
					1 as eliminar,
					fjo_Movimientos.subcategoria,
					1 as aplicado
			FROM fjo_Movimientos
				INNER JOIN tags ON tags.tagref=fjo_Movimientos.tagref
				INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
			  	INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION ['UserID'] . "'
			WHERE fjo_Movimientos.fechapromesa >= '" . $fechaini . "' 
					AND fjo_Movimientos.fechapromesa <= '" . $fechafin . "'
					AND fjo_Movimientos.fechapromesa >= '" . $FechaProy . "' 
					AND abs(amount)!=0
                                                                                
		 ";
	if (isset ( $_POST ['legalid'] ) && $_POST ['legalid'] != '0') {
		$SQLProy = $SQLProy . " AND tags.legalid=" . $_POST ['legalid'];
	}
	if (isset ( $_POST ['unidadnegocio'] ) && $_POST ['unidadnegocio'] != '-1') {
		$SQLProy = $SQLProy . " AND tags.tagref=" . $_POST ['unidadnegocio'];
	}
	
	if (isset ( $_POST ['subcategoria_'] ) && $_POST ['subcategoria_'] != '-1') {
		$SQLProy = $SQLProy . " AND IFNULL(fjo_Movimientos.subcategoria,0)=" . $_POST ['subcategoria_'];
	}
	
	$unionCXC = ' UNION ';
	if ($checkbanco != 'checked') {
		$SQLBanco = '';
		$unionCXC = '';
	}
	$unionCXP = ' UNION ';
	if ($checkCxC != 'checked') {
		$SQLCxC = '';
		$unionCXP = '';
	}
	$unionP = ' UNION ';
	if ($checkCxP != 'checked') {
		$SQLCxP = '';
		$unionP = '';
	}
	if ($checkP != 'checked') {
		$SQLProy = '';
	}
	//echo '<pre>sql:<br>'.$SQLCxP;
	$SQL = $SQLBanco . $unionCXC . $SQLCxC . $unionCXP . $SQLCxP . $unionP . $SQLProy;
	$validaunion = left ( strrev ( $SQL ), 6 );
	if (trim ( $validaunion ) == 'NOINU') {
		$sqllen = strlen ( $SQL ) - 6;
		$SQL = left ( $SQL, $sqllen );
	}
	$SQL = $SQL . ' ORDER BY fechapromesa ASC';
	//echo '<pre>sql:<br>'.$SQL;
	$ReportResult = DB_query ( $SQL, $db, '', '', False, False ); /* dont trap errors */
	if (DB_error_no ( $db ) != 0) {
		$title = _ ( 'Estado General de Inventarios' ) . ' - ' . _ ( 'Reporte de Problema' );
		// include("includes/header.inc");
		prnMsg ( _ ( 'Los detalles del inventarios no se pudieron recuperar porque el SQL fallo' ) . ' ' . DB_error_msg ( $db ), 'error' );
		
		if ($debug == 1) {
			//
		}
		exit ();
	}
	echo '<table border=1 style=’table-layout:fixed; style="border-collapse:collapse;border-color:#f6f6f6">';
	
	echo '<tr><td colspan=15>';
	
	echo '</td>
			  </tr>';
	
	$saldoinicial = Traesaldoinicialflujo ( $fechaini, $_POST ['legalid'], $_POST ['unidadnegocio'], $funcion, $checkbanco, $checkCxC, $checkCxP, $checkP, $_POST ['BankAccount'],$_POST ['subcategoria_'], $FechaProy, $db );
	// echo 'saldo:'.$saldoinicial;
	echo "<tr>";
		echo "<th colspan='12' class='pie_tabla' style='font-size:14px;text-align:right;'>" . _ ( "Saldo Inicial" ) . "&nbsp;</th>";
		echo "<th class='pie_tabla' style='font-size:14px;text-align:right;'>" . number_format ( $saldoinicial, 2 ) . "</th>";
		echo "<th colspan='2' class='pie_tabla' style='font-size:14px;text-align:right;'></th>";
	echo "</tr>";
	
	$encabezadogeneral = '
				<tr>';
				if (!isset($_POST ['PrintEXCEL'])){
					$type = 'checkbox';
                                }else{
                                    	$type = 'hidden';
                                }
				echo '<td class="titulos_principales"><b>' . _ ( '#' ).' <input type="'.$type.'" name="chkall" onclick="selAll(this);"></b></td>
					<td class="titulos_principales"><b>' . _ ( 'Tipo' ) . '</b></td>
					<td class="titulos_principales"><b>' . _ ( 'Fecha<br>Promesa' ) . '</b></td>
					<td class="titulos_principales"><b>' . _ ( 'U.N.' ) . '</b></td>
					<td class="titulos_principales"><b>' . _ ( 'T.<br>Docto' ) . '</b></td>
					<td class="titulos_principales"><b>' . _ ( 'N.<br>Docto' ) . '</b></td>
					<td class="titulos_principales"><b>' . _ ( 'No<br>Cheque' ) . '</b></td>
					<td class="titulos_principales"><b>' . _ ( 'Inf. Adicional' ) . '</b></td>
					<td class="titulos_principales"><b>' . _ ( 'Referencia' ) . '</b></td>
				    <td class="titulos_principales"><b>' . _ ( 'Moneda' ) . '</b></td>
					<td class="titulos_principales"><b>' . _ ( 'Cargo' ) . '</b></td>
					<td class="titulos_principales"><b>' . _ ( 'Abono' ) . '</b></td>
					<td class="titulos_principales"><b>' . _ ( 'Saldo Acumulado' ) . '</b></td>
					<td class="titulos_principales"><b>' . _ ( 'Cuenta' ) . '</b></td>
					<td class="titulos_principales"><b>' . _ ( 'Subcategoria' ) . '</b></td>
				</tr>';
	echo $encabezadogeneral;
	$indice = 0;
	$saldoacumulado = 0;
	$saldoacumulado = $saldoinicial;
	$cuenta = 0;
	while ( $ReportRow = DB_fetch_array ( $ReportResult ) ) {
		$indice = $indice + 1;
		
		$cuenta = $cuenta + 1;
		if ($cuenta > 20) {
			if (!isset($_POST ['PrintEXCEL'])){
				echo $encabezadogeneral;
			}
			$cuenta = 1;
		}
		
		if ($k == 1) {
			echo '<tr style="background-color:#FFFFFF">';
			$k = 0;
		} else {
			echo '<tr style="background-color:#f6f6f6">';
			$k = 1;
		}
		
		echo '<td style="text-align:center" nowrap>';
		if (!isset($_POST ['PrintEXCEL'])){
			echo '<input type="checkbox" name="item_' . $indice . '" id="item_' . $indice . '">';
			echo '<input type="hidden" name="nodocto_' . $indice . '" value="' . $ReportRow ['id'] . '">';
			echo '<input type="hidden" name="tipodocto_' . $indice . '" value="' . $ReportRow ['tipomovimiento'] . '">';
		}
		echo '</td>';
		echo '<td style="text-align:center;background-color:' . $ReportRow ['color'] . '"><b>' . $ReportRow ['tipomovimiento'] . '</b></td>';
		if ($permisocambiafecha == 1 && $ReportRow ['modificar'] == 1) {
			if (!isset($_POST ['PrintEXCEL'])){
				echo '<td style="text-align:center" nowrap><span title="' . $ReportRow['fechaemision'] . '">';
					echo '	<input size=12 type="text" ' . $class . ' alt="Y/m/d" class="date" name="Fechaprom_' . $indice . '" Value="' . str_replace ( '-', '/', $ReportRow ['fechapromesa'] ) . '"  onblur="seleccionaCheckBox(' . $indice . ')">';
				echo '</span></td>';
			}else{
				echo '<td style="text-align:center" nowrap>' . $ReportRow ['fechapromesa'] . '</td>';
			}
		} else {
			echo '<td style="text-align:center" nowrap><span title="' . $ReportRow['fechaemision'] . '">' . $ReportRow ['fechapromesa'] . '</span></td>';
		}
		echo '<td style="text-align:center; font-size:x-small;"><span title="' . $ReportRow['tagdescription'] . '">' . $ReportRow ['tagref'] . '</span></td>';
		echo '<td style="text-align:center; font-size:x-small;"><span title="' . $ReportRow['typename'] . '">' . $ReportRow ['tipo'] . '</span></td>';
		echo '<td style="text-align:center; font-size:x-small;">' . $ReportRow ['transno'] . '</td>';
		
		echo '<td style="text-align:center; font-size:x-small;">' . $ReportRow ['cheque'] . '</td>';
		
		if ($permisocambiafecha == 1 && $ReportRow ['eliminar'] == 1) {
			if (!isset($_POST ['PrintEXCEL'])){
				echo '<td><resize: none onblur="seleccionaCheckBox(' . $indice . ')" name="concepto_' . $indice . '">' . $ReportRow ['beneficiario'] . '</textarea></td>';
			}else{
				echo '<td nowrap style="font-size:x-small;">' . substr($ReportRow ['beneficiario'],0,30) . '</td>';
			}
		} else {
			echo '<td nowrap style="font-size:x-small;"><span title="' . $ReportRow['beneficiario'] . '">' . substr($ReportRow ['beneficiario'],0,30) . '</span></td>';
		}
		
		if ($permisocambiafecha == 1 && $ReportRow ['eliminar'] == 1) {
			if (!isset($_POST ['PrintEXCEL'])){
				echo '<td><input width=15 size=18 name="referencia_' . $indice . '" value="' . $ReportRow ['referencias'] . '" onblur="seleccionaCheckBox(' . $indice . ')" ></td>';
			}else{
				echo '<td style="width:150px; font-size:x-small;">' . substr($ReportRow['referencias'],0,250) . '</td>';
			}
		} else {
			echo '<td style="width:150px; font-size:x-small;"><span title="' . $ReportRow['referencias'] . '" style="width:150px;">' . substr($ReportRow['referencias'],0,250) . '</span></td>';
		}
		
		
		echo '<td style="font-size:x-small; text-align:center">' . $ReportRow ['moneda'] . '</td>';
		if ($ReportRow['aplicado'] == '0'){
			$underline = 'border-bottom: medium solid #CC0000';
		}else{
			$underline = '';
		}
		
		if ($ReportRow ['saldo'] < 0) {
			echo '<td style="text-align:center"></td>';
			if ($permisocambiafecha == 1 && $ReportRow ['eliminar'] == 1) {
				if (!isset($_POST ['PrintEXCEL'])){
					echo '<td style="text-align:center"><input size=8 onblur="seleccionaCheckBox(' . $indice . ')" type="text" name="cargo_' . $indice . '" value="' . number_format ( abs ( $ReportRow ['saldo'] ), 2 ) . '" class="number" ></td>';
				}else{
					echo '<td style="text-align:center; font-size:x-small;">' . number_format ( abs($ReportRow ['saldo']), 2 ) . '</td>';
				}
			} else {
				echo '<td style="text-align:center; font-size:x-small;"><span style="' . $underline . ';">' . number_format ( abs($ReportRow ['saldo']), 2 ) . '</span></td>';
			}
			$totalabonos = $totalabonos + $ReportRow ['saldo'];
			//echo "<pre>totalabonos --->" .$totalabonos;
		} else {
			
			if ($permisocambiafecha == 1 && $ReportRow ['eliminar'] == 1) {
				if (!isset($_POST ['PrintEXCEL'])){
					echo '<td style="text-align:center"><input size=8 onblur="seleccionaCheckBox(' . $indice . ')" class="number" type="text" name="abono_' . $indice . '" value="' . number_format( abs ( $ReportRow ['saldo'] ), 2 ) . '"></td>';
				}else{
					echo '<td style="text-align:center; font-size:x-small;">' . number_format ( abs ( $ReportRow ['saldo'] ), 2 ) . '</td>';
				}
			} else {
				echo '<td style="text-align:center; font-size:x-small;"><span style="' . $underline . ';">' . number_format ( abs ( $ReportRow ['saldo'] ), 2 ) . '</span></td>';
			}
			echo '<td style="text-align:center"></td>';
			$totalcargos = $totalcargos + $ReportRow ['saldo'];
			//echo "<pre>totalcargos --->" .$totalcargos;
			
		}
		$saldoacumulado = $saldoacumulado - $ReportRow ['saldo'];
		//echo "<pre>saldoacumulado --->".$saldoacumulado;
		echo '<td style="text-align:center; font-size:x-small;">' . number_format ( $saldoacumulado, 2 ) . '</td>';
		echo '<td style="text-align:center"><span title="' . $ReportRow['cuentacontable'] . '">' . $ReportRow ['codigoctacontable'] . '</span></td>';
		//codigoctacontable
	if ($ReportRow['subcategoria'] =='0'){
		echo '<td style="background-color:yellow;">';
	}else{
		echo '<td>';
	}
	
	//Imprime las razones sociales
	$SQL = "SELECT sc.subcat_id, sc.subcat_name, ca.cat_name, ca.cat_id
				FROM fjoSubCategory sc
					left join fjoCategory ca ON sc.cat_id = ca.cat_id
					ORDER BY ca.order, sc.order, sc.subcat_name";
	
	$result=DB_query($SQL,$db);
	/* NO PODER SELECCIONAR TODAS PUES ESTA REFERENCIA SE USA PARA EL ALTA DE UN MOVIMIENTO */
	if (! isset ( $_POST ['PrintEXCEL'] )) {
		echo '<select name="subcategoria_' . $indice . '" onchange="seleccionaCheckBox(' . $indice . ')">;
				<option selected value="-1">Selecciona una Subcategoria..</option>';
		$catant = "";
		while ($myrow=DB_fetch_array($result)){
			if($catant != $myrow['cat_id']){
				echo '<option value=' . $myrow['subcat_id'] . '>*****' . substr($myrow['cat_name'],0,40) . '*****</option>';
				$catant = $myrow['cat_id'];
			}
			if ($ReportRow['subcategoria']==$myrow['subcat_id']){
				echo '<option selected value=' . $myrow['subcat_id'] . '>' . $myrow['subcat_id'] . ' - ' . substr($myrow['subcat_name'],0,40) . '</option>';
			} else {
				echo '<option value=' . $myrow['subcat_id'] . '>' . $myrow['subcat_id'] . ' - ' . substr($myrow['subcat_name'],0,40)  . '</option>';
			}
		}
		echo '</select>';
	}else{
		$subcategoria = "";
		while ($myrow=DB_fetch_array($result)){
			if ($ReportRow['subcategoria'] == $myrow['subcat_id']){
				$subcategoria = $myrow['subcat_name']; 	
			}
		}
		echo "<br>" . $subcategoria;
	}
	echo '</td>';
		
		
	}
	
	echo "<tr>";
	echo "<th colspan='10' class='pie_tabla' style='font-size:14px;text-align:right;'>" . _ ( "TOTALES :" ) . "&nbsp;&nbsp;
    		<input type=hidden name='Contador' id=Contador value='" . $indice . "'></th>";
	echo "<th class='pie_tabla' style='font-size:14px;text-align:right;'>" . number_format ( abs ( $totalcargos ), 2 ) . "</th>";
	echo "<th class='pie_tabla' style='font-size:14px;text-align:right;'>" . number_format ( abs ( $totalabonos ), 2 ) . "</th>";
	echo "<th class='pie_tabla' style='font-size:14px;text-align:right;'>" . number_format ( $saldoacumulado, 2 ) . "</th>";
	echo "<th colspan='2' class='pie_tabla' style='font-size:14px;text-align:right;'></th>";
	echo "</tr>";
	if (! isset ( $_POST ['PrintEXCEL'] )) {
		if ($permisocambiafecha == 1) {
			echo "<tr>";
			echo "<th colspan='16' class='titulos_principales' style='text-align:left'>";
			
			// echo '<input type=submit name="Process" value="' . _('Procesar Cambios') . '">';
			echo _ ( 'Operacion:' );
			
			echo "<SELECT name='Oper'>";
			echo "<OPTION value='ActualizaInf'>" . _ ( 'Actualiza Informacion' ) . "</OPTION>";
			echo "<OPTION value='ActualizaMasiva'>" . _ ( 'Mueve a dia Directo -->' ) . "</OPTION>";
			echo "<OPTION value='EliminaInf'>" . _ ( 'Elimina Informacion' ) . "</OPTION>";
			echo "<OPTION value='DuplicaSemana'>" . _ ( 'Copiar Movimientos + 1 Semana' ) . "</OPTION>";
			echo "<OPTION value='DuplicaMes'>" . _ ( 'Copiar Movimientos + 1 Mes' ) . "</OPTION>";
			echo '</select>';
			$fechahoy = Date ( 'Y-m-d' );
			echo '	<input size=12 type="text" ' . $class . ' alt="Y/m/d" class="date" name="Fechapromesagral" Value="' . str_replace ( '-', '/', $fechahoy ) . '">';
			echo '<input type=submit name="Process" value="' . _ ( 'Procesar Cambios' ) . '">';
			
			echo '</td>';
			echo "</tr>";
		}
	}
	
	echo '</table>';
} elseif (isset ( $_POST ['AltaMovimiento'] )) {
	// Captura movimiento proyeccion
	echo '<br>';
	echo '<table border=1 style="border-collapse:collapse;border-color:#f6f6f6">';
	echo '<tr>';
	echo '<td colspan=2 class="titulos_principales"><b>' . _ ( 'Alta Movimientos Proyeccion' ) . '</b></td>
		</tr>';
	echo '<tr>';
	echo '<td colspan=2>
	     <table border="0" style="margin:auto;">
	    	<tr>';
	echo '<td style="font-size:12px;text-align:right;">' . _ ( 'Fecha Movimiento' ) . '</td>
			  <td><select Name="FromDiaMov">';
	$sql = "SELECT * FROM cat_Days";
	$dias = DB_query ( $sql, $db );
	while ( $myrowdia = DB_fetch_array ( $dias, $db ) ) {
		$diabase = $myrowdia ['DiaId'];
		if (rtrim ( intval ( $FromDiaMov ) ) == rtrim ( intval ( $diabase ) )) {
			echo '<option  VALUE="' . $myrowdia ['DiaId'] . '" selected>' . $myrowdia ['Dia'] . '</option>';
		} else {
			echo '<option  VALUE="' . $myrowdia ['DiaId'] . '">' . $myrowdia ['Dia'] . '</option>';
		}
	}
	echo '</select></td>';
	echo '<td><select Name="FromMesMov">';
	$sql = "SELECT * FROM cat_Months";
	$Meses = DB_query ( $sql, $db );
	while ( $myrowMes = DB_fetch_array ( $Meses, $db ) ) {
		$Mesbase = $myrowMes ['u_mes'];
		if (rtrim ( intval ( $FromMesMov ) ) == rtrim ( intval ( $Mesbase ) )) {
			echo '<option  VALUE="' . $myrowMes ['u_mes'] . '  " selected>' . $myrowMes ['mes'] . '</option>';
		} else {
			echo '<option  VALUE="' . $myrowMes ['u_mes'] . '" >' . $myrowMes ['mes'] . '</option>';
		}
	}
	echo '</select>';
	echo '&nbsp;<input name="FromYearMov" type="text" size="4" value=' . $FromYearMov . '>';
	echo '</td>';
	echo '</tr>';
	echo '</table>';
	echo '</td>';
	echo '</tr>';
	// Select the razon social
	echo '<tr><td style="font-size:12px;text-align:right;">' . _ ( 'Razon Social:' ) . '<td>
			<select name="legalidMov">';
	echo "<option selected value='0'>Todas las Razones";
	// /Pinta las razones sociales
	$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
	$SQL = $SQL . " FROM sec_unegsxuser u,tags t
						JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid";
	$SQL = $SQL . " WHERE u.tagref = t.tagref ";
	$SQL = $SQL . " and u.userid = '" . $_SESSION ['UserID'] . "'
			  	GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY t.legalid";
	$result = DB_query ( $SQL, $db );
	while ( $myrow = DB_fetch_array ( $result ) ) {
		if (isset ( $_POST ['legalidMov'] ) and $_POST ['legalidMov'] == $myrow ["legalid"]) {
			echo '<option selected value=' . $myrow ['legalid'] . '>' . $myrow ['legalid'] . ' - ' . $myrow ['legalname'];
		} else {
			echo '<option value=' . $myrow ['legalid'] . '>' . $myrow ['legalid'] . ' - ' . $myrow ['legalname'];
		}
	}
	echo '</select>';
	echo " <input type=submit name='selLegalid' VALUE='" . _ ( '->' ) . "'>";
	echo " <input type=hidden name='AltaMovimiento' VALUE='" . _ ( 'AltaMovimiento' ) . "'>";
	echo '</td>';
	// End select Razon Social
	
	/* SELECCION DE LA UNIDAD DE NEGOCIO DE PRODUCTOS */
	echo "<tr><td style='font-size:12px;text-align:right;'>" . _ ( 'Unidad de Negocio' ) . ":</td><td>";
	echo "<select name='unidadnegocioMov'>";
	$SQL = "SELECT  t.tagref, CONCAT(t.tagref,' - ',t.tagdescription) as tagdescription "; // areas.areacode, areas.areadescription";
	$SQL = $SQL . " FROM sec_unegsxuser u,tags t join areas ON t.areacode = areas.areacode ";
	$SQL = $SQL . " WHERE u.tagref = t.tagref ";
	$SQL = $SQL . " and u.userid = '" . $_SESSION ['UserID'] . "'";
	
	if (isset ( $_POST ['legalidMov'] ) && $_POST ['legalidMov'] != '-1') {
		$SQL = $SQL . " AND legalid=" . $_POST ['legalidMov'];
	}
	$SQL = $SQL . " ORDER BY tagdescription";
	$ErrMsg = _ ( 'No transactions were returned by the SQL because' );
	$TransResult = DB_query ( $SQL, $db, $ErrMsg );
	
	echo "<option selected value='-1'>Todas a las que tengo accceso...</option>";
	while ( $myrow = DB_fetch_array ( $TransResult ) ) {
		if ($myrow ['tagref'] == $_POST ['unidadnegocioMov']) {
			echo "<option selected value='" . $myrow ['tagref'] . "'>" . $myrow ['tagdescription'] . "</option>";
		} else {
			echo "<option value='" . $myrow ['tagref'] . "'>" . $myrow ['tagdescription'] . "</option>";
		}
	}
	echo "</select>";
	echo "</td></tr>";
	
	
	echo '<tr><td style="text-align:right">'._('Subcategoria :').'</td><td>';
	///Imprime las razones sociales
	$SQL = "SELECT fjoSubCategory.subcat_id, fjoSubCategory.subcat_name
                FROM fjoSubCategory
                ORDER BY subcat_name";
	
	$result=DB_query($SQL,$db);
	/* NO PODER SELECCIONAR TODAS PUES ESTA REFERENCIA SE USA PARA EL ALTA DE UN MOVIMIENTO */
	echo "<select name='subcategoria'>
                <option selected value='0'>Selecciona una Subcategoria..</option>";
	
	while ($myrow=DB_fetch_array($result)){
            if (($myrow['subcategoria'] == $myrow['subcat_id'])){
                echo '<option selected value=' . $myrow['subcat_id'] . '>' . $myrow['subcat_id'].' - ' .$myrow['subcat_name'];
            } else {
                echo '<option value=' . $myrow['subcat_id'] . '>' . $myrow['subcat_id'].' - ' .$myrow['subcat_name'];
            }
	}
	
	
	echo '</select></td></tr>';
	
	echo "<tr><td style='font-size:12px;text-align:right;'>" . _ ( 'Referencia' ) . ":</td><td>";
	echo "<input type='text' size='12' name='factura'>";
	echo "</td></tr>";
	
	echo "<tr><td style='font-size:12px;text-align:right;'>" . _ ( 'Concepto' ) . ":</td><td>";
	echo '<textarea name=Concepto cols=40 rows=2></textarea>';
	echo '</td></tr>';
	
	echo "<tr><td style='font-size:12px;text-align:right;'>" . _ ( 'Cargo' ) . ":</td><td>";
	echo "<input type='text' size='7' name=Cargo>";
	echo "</td></tr>";
	
	echo "<tr><td style='font-size:12px;text-align:right;'>" . _ ( 'Abono' ) . ":</td><td>";
	echo "<input type='text' size='7' name='abono'><br><br>";
	echo "</td></tr>";
	
	echo "<tr><td colspan=2 class='titulos_principales' style='font-size:12px;text-align:center;'>";
	echo '<input tabindex="7" type=submit name="ProcessAlta" value="' . _ ( 'Procesar Alta' ) . '">';
	echo "</td></tr>";
	
	echo '</table>';
}
if (isset ( $_POST ['PrintEXCEL'] )) {
	exit ();
}
echo '</form>';

include ('includes/footer.inc');

?>

<script LANGUAGE="JavaScript"> 
function seleccionaCheckBox(indice) {
      //var answer = confirm("chk"+indice);  
     // alert("entro");
      var md = document.getElementById("item_"+indice);
      
      md.checked = true;
} 
function selAll(obj){
	var j = document.getElementById('Contador').value;

	//alert("valor de :" + j);

	for(i=1; i<=j; i++){
		concatenar = "item_" + i;
		chkobj = document.getElementById(concatenar);
		if(chkobj != null){
			chkobj.checked = obj.checked;
		}
		
	}
	
}



function SelectCheckAuto(a)
{
	//alert(a);
	if (a==true){
		for (i=0;i<document.FDatosB.elements.length;i++) {
		      if(document.FDatosB.elements[i].type == "checkbox") {
		       tipo =document.FDatosB.elements[i].getAttribute('name');
		       if(tipo!='condetalle'){
				document.FDatosB.elements[i].checked=1;
		       }
		      }
		}
	}else{
		for (i=0;i<document.FDatosB.elements.length;i++) {
		      if(document.FDatosB.elements[i].type == "checkbox") {
		       tipo =document.FDatosB.elements[i].getAttribute('name');
			document.FDatosB.elements[i].checked=0;
		      }
		}	
	}
}
</script>
