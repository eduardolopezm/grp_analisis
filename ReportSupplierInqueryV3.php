<?php
/*
 *	15/SEPT/2012  - desarrollo- Agregue conversion a dolares en saldo documento en abonos
 *	09/AGOSTO/2012 -desarrollo- Agregue el tipo de moneda a las aplicaciones...
	08/AGOSTO/2012 -desarrollo- Agregue etiquetas de montos en dolares y subtotales
	01/JUNIO/2012 -desarrollo- Correcciones de formato y agregue saldo a la ultima fecha del estado de cuenta...
	
*/
//echo 'ok';
include('includes/SQL_CommonFunctions.inc');

$PageSecurity=2;

include('includes/session.inc');
$title = _('Estado de Cuenta de Proveedor');


$funcion=956;
include('includes/SecurityFunctions.inc');//
// This is already linked from the menu
//echo "<a href='" . $rootpath . '/SelectSupplier.php?' . SID . "'>" . _('Back to Suppliers') . '</a><br>';

// always figure out the SQL required from the inputs available

if(!isset($_GET['SupplierID']) AND !isset($_SESSION['SupplierID'])){
	echo '<br>' . _('To display the enquiry a Supplier must first be selected from the Supplier selection screen') . 
		  "<br><div class='centre'>><a href='". $rootpath . "/SelectSupplier.php'>" . _('Select a Supplier to Inquire On') . '</a></div>';
	exit;
} else {
	if (isset($_GET['SupplierID'])){
		$_SESSION['SupplierID'] = $_GET['SupplierID'];
	}
	$SupplierID = $_SESSION['SupplierID'];
}

if (isset($_GET['FromDate']) AND !isset($_POST['TransAfterDate'])){
	$_POST['TransAfterDate']=$_GET['FromDate'];
}


if (isset($_POST['FechaPromesaPago'])) {
	$FechaPromesaPago = $_POST['FechaPromesaPago'];
}
else{
	$FechaPromesaPago = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date("m")-12,Date("d"),Date("Y")));
}

/// FECHA DESDE -HASTA
	if (isset($_POST['FromYear'])) {
        $FromYear=$_POST['FromYear'];
	} elseif(isset($_GET['FromYear'])) {
		$FromYear=$_GET['FromYear'];
	}else{
		$FromYear=date('Y');
	}

    if (isset($_POST['FromMes'])) {
        $FromMes=$_POST['FromMes'];
    } elseif(isset($_GET['FromMes'])) {
	$FromMes=$_GET['FromMes'];
    }else{
        $FromMes=date('m');
    }
    if (isset($_GET['FromDia'])) {
        $FromDia=$_GET['FromDia'];
    }elseif(isset($_POST['FromDia'])) {
	$FromDia=$_POST['FromDia'];
    }else{
        $FromDia=1;
    }

    if (isset($_POST['ToYear'])) {
        $ToYear=$_POST['ToYear'];
    } elseif(isset($_GET['ToYear'])) {
	$ToYear=$_GET['ToYear'];
    }else{
        $ToYear=date('Y');
    }

    if (isset($_POST['ToMes'])) {
        $ToMes=$_POST['ToMes'];
    } elseif(isset($_GET['ToMes'])) {
	$ToMes=$_GET['ToMes'];
    }else{
        $ToMes=date('m');
    }
    if (isset($_GET['ToDia'])) {
        $ToDia=$_GET['ToDia'];
    } elseif(isset($_POST['ToDia'])) {
	$ToDia=$_POST['ToDia'];
    }else{
        $ToDia=date('d');
    }
    if (isset($_POST['UnidNeg'])) {
        $UnidNeg=$_POST['UnidNeg'];
    } elseif(isset($_GET['UnidNeg'])) {
	$UnidNeg=$_GET['UnidNeg'];
    }	
     $fechaini= rtrim($FromYear).'-'.rtrim($FromMes).'-'.rtrim($FromDia);
     $fechafinsinhoras= rtrim($ToYear).'-'.add_ceros(rtrim($ToMes),2).'-'.add_ceros(rtrim($ToDia),1) ;
     
     //$fechafin= rtrim($ToYear).'-'.rtrim($ToMes).'-'.rtrim($ToDia);
     $fechafin= rtrim($ToYear).'-'.add_ceros(rtrim($ToMes),2).'-'.add_ceros(rtrim($ToDia),1) . ' 23:59:59';
     $fechainic=mktime(0,0,0,rtrim($FromMes),rtrim($FromDia),rtrim($FromYear));
     $fechafinc=mktime(0,0,0,rtrim($ToMes),rtrim($ToDia),rtrim($ToYear));
     $InputError = 0;
     if ($fechainic > $fechafinc){
          $InputError = 1;
     prnMsg(_('La fecha inicial no puede ser mayor a la fecha final! Por favor seleccionar nuevamente los periodos de fecha'),'error');
     } else {
          $InputError = 0;
     }	

if (!isset($_POST['PrintEXCEL'])) {
include('includes/header.inc');
$debug = 1;
echo '<form action=' . $_SERVER['PHP_SELF'] .'?' .SID . ' method=post>';


///FIN FECHA



if (isset($_POST['procesar']))
{
    if (isset($_POST['TotalDoctos'])){
            $totaldoctos=$_POST['TotalDoctos'];
            for ( $doc=0 ; $doc <= $totaldoctos ; $doc++) {
                    if ($_POST['doctosel'.$doc]==TRUE){
                            $docto=$_POST['docto'.$doc];

                            $sql="UPDATE supptrans set promisedate='".FormatDateForSQL($FechaPromesaPago)."' WHERE transno=".$docto;
                            $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
                    }
            }
	    
	echo '<meta http-equiv="Refresh" content="0; url=' . $_SERVER['PHP_SELF'] . '?' . SID .'SupplierID='.$SupplierID . '">';
	exit;


	    
    }
};


if (isset($_POST['quitar']))
{
    if (isset($_POST['TotalDoctos'])){
            $totaldoctos=$_POST['TotalDoctos'];
            for ( $doc=0 ; $doc <= $totaldoctos ; $doc++) {
                    if ($_POST['doctosel'.$doc]==TRUE){
                            $docto=$_POST['docto'.$doc];

                            $sql="UPDATE supptrans set promisedate='0000-00-00' WHERE transno=".$docto;
                            $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
                    }
            }
	    
	echo '<meta http-equiv="Refresh" content="0; url=' . $_SERVER['PHP_SELF'] . '?' . SID .'SupplierID='.$SupplierID . '">';
	exit;


	    
    }
};

if (isset($_POST['ModifyPay']) and isset($_POST['cuenta'])) {
	$totalcuenta=$_POST['cuenta'];
	for ($z=1;$z<=$totalcuenta;$z++){
		$pagoanterior=$_POST['pagosant'.$z];
		$pagoactual=$_POST['pagos'.$z];
		$taganterior=$_POST['uniant'.$z];
		$tagactual=$_POST['UnidNeg'.$z];
		$trandateant=trim($_POST['trandateant'.$z]);
		$trandateact=trim($_POST['trandate'.$z]);
		$ovgst=$_POST['ovgst'.$z];
		$ovamount=$_POST['ovamount'.$z];
		$ovamountant=$_POST['ovamountant'.$z];
		$ovgstant=$_POST['ovgstant'.$z];
		
		
		if ($pagoanterior!=$pagoactual or $taganterior!=$tagactual
		    or $trandateant!=$trandateact
		    or $ovamount!=$ovamountant
		    or $ovgst!=$ovgstant
		    
		    ){
			$idtrans=$_POST['idtrans'.$z];
			$typetrans=$_POST['typetrans'.$z];
			//$ovamount=($ovamount*$typetrans);
			//$ovgst=($ovgst*$typetrans);
			$ovamount=$ovamount;
			$ovgst=$ovgst;
			
			$Result = DB_Txn_Begin($db);
			
			$separa = explode('/',$trandateact);
			$mesvence = $separa[1];
			$aniovence = $separa[2];
			$diavence=$separa[0];
			$fechavence=$aniovence.'/'.$mesvence.'/'.$diavence;
			$pagoactual=str_replace(",","",$pagoactual);
			$SQL="UPDATE supptrans
			      SET alloc=".$pagoactual.",
				  tagref=".$tagactual.",
				  trandate='".$fechavence."',
				  ovgst=".$ovgst.",
				  ovamount=".$ovamount."
			      WHERE id=".$idtrans;
			$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La actualizacion no fue posible');
			$DbgMsg = _('El SQL utilizado es:');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			$Result = DB_Txn_Commit($db);
			unset($_POST['pagos'.$z]);
			unset($_POST['pagosant'.$z]);
			unset($_POST['idtrans'.$z]);
			unset($_POST['uniant'.$z]);
			unset($_POST['UnidNeg'.$z]);
			//unset($fechaini);
			//unset($fechafin);
			unset($_POST['ovgst'.$z]);
			
			unset($_POST['ovamount'.$z]);
			unset($_POST['ovamountant'.$z]);
			unset($_POST['ovgstant'.$z]);
			unset($_POST['ModifyPay']);
			unset($_POST['cuenta']);
			break ;
		}
	}	
}
/*if (isset($_POST['enviar'])) {
		$SQL = "INSERT INTO  comentsupplier(coments,id_supplier,trandate)
		VALUES('". $_POST['coments']."',
			'" . $SupplierID . "',
			now())";
		
		$result = DB_query($SQL,$db,$ErrMsg);
		
	}*/



$SQL = 'SELECT suppliers.suppname,
		suppliers.address1,
		suppliers.address2,
		suppliers.taxid,
		currencies.currency, 
		paymentterms.terms,
		SUM(((supptrans.ovamount + supptrans.ovgst) - supptrans.alloc)/supptrans.rate) AS balance,
		SUM(CASE WHEN paymentterms.daysbeforedue > 0 THEN
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) >= paymentterms.daysbeforedue
			THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		ELSE 
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, ' . INTERVAL('1', 'MONTH') . '), ' . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ')) >= 0 THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		END) AS due,
		SUM(CASE WHEN paymentterms.daysbeforedue > 0  THEN 
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) > paymentterms.daysbeforedue 
					AND (TO_DAYS(Now()) - TO_DAYS(supptrans.trandate)) >= (paymentterms.daysbeforedue + ' . $_SESSION['PastDueDays1'] . ')
			THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		ELSE
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, ' . INTERVAL('1','MONTH') . '), ' . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') .')) >= ' . $_SESSION['PastDueDays1'] . ')
			THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		END) AS overdue1,
		Sum(CASE WHEN paymentterms.daysbeforedue > 0 THEN
			CASE WHEN TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(supptrans.trandate) >= (paymentterms.daysbeforedue + ' . $_SESSION['PastDueDays2'] . ') 
			THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		ELSE
			CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(supptrans.trandate, ' . INTERVAL('1','MONTH') . '), ' . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(supptrans.trandate))', 'DAY') . ')) >= ' . $_SESSION['PastDueDays2'] . ")
			THEN supptrans.ovamount + supptrans.ovgst - supptrans.alloc ELSE 0 END
		END ) AS overdue2
		FROM suppliers,
     			paymentterms,
     			currencies,
     			supptrans
		WHERE suppliers.paymentterms = paymentterms.termsindicator
     		AND suppliers.currcode = currencies.currabrev
     		AND suppliers.supplierid = '" . $SupplierID . "'
     		AND suppliers.supplierid = supptrans.supplierno";
		

		if (!isset($_POST['todaslasunidades'])){
		  $SQL .= " AND supptrans.tagref  in (select tagref from sec_unegsxuser where userid='".$_SESSION['UserID']. "')";
		};	     
		
		 $SQL .= " GROUP BY suppliers.suppname,
      			currencies.currency,
      			paymentterms.terms,
      			paymentterms.daysbeforedue,
      			paymentterms.dayinfollowingmonth";

$ErrMsg = _('The supplier details could not be retrieved by the SQL because');
$DbgMsg = _('The SQL that failed was');
//echo "<pre>" . $SQL;
$SupplierResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

if (DB_num_rows($SupplierResult) == 0){

	/*Because there is no balance - so just retrieve the header information about the Supplier - the choice is do one query to get the balance and transactions for those Suppliers who have a balance and two queries for those who don't have a balance OR always do two queries - I opted for the former */

	$NIL_BALANCE = True;

	$SQL = "SELECT suppliers.suppname,
			suppliers.address1,
			suppliers.taxid,
			currencies.currency, 
			paymentterms.terms
		FROM suppliers,
	     		paymentterms,
	     		currencies
		WHERE suppliers.paymentterms = paymentterms.termsindicator
		AND suppliers.currcode = currencies.currabrev
		AND suppliers.supplierid = '" . $SupplierID . "'";

	$ErrMsg = _('The supplier details could not be retrieved by the SQL because');
	$DbgMsg = _('The SQL that failed was');

	$SupplierResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

} else {
	$NIL_BALANCE = False;
}

$SupplierRecord = DB_fetch_array($SupplierResult);

if ($NIL_BALANCE == True){
	$SupplierRecord['balance'] = 0;
	$SupplierRecord['due'] = 0;
	$SupplierRecord['overdue1'] = 0;
	$SupplierRecord['overdue2'] = 0;
}


/*echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/supplier.png" title="' . 
	_('Supplier') . '" alt="">' . ' ' . _('Proveedor') . ' : ' . $SupplierRecord['suppname'] . ' - (' . _('Todos los montos en ') . 
	  ' ' . $SupplierRecord['currency'] . ')<br><br>' . _('Condiciones de pago') . ': ' . $SupplierRecord['terms'] . '</p>';
*/
echo "<form action='" . $_SERVER['PHP_SELF'] . "' method=post>";
if (isset($_GET['HoldType']) AND isset($_GET['HoldTrans'])){

	if ($_GET['HoldStatus'] == _('Hold')){
		$SQL = 'UPDATE supptrans SET hold=1 WHERE type=' . $_GET['HoldType'] . ' AND transno=' . $_GET['HoldTrans'];
	} elseif ($_GET['HoldStatus'] == _('Release')){
		$SQL = 'UPDATE supptrans SET hold=0 WHERE type=' . $_GET['HoldType'] . ' AND transno=' . $_GET['HoldTrans'];
	}

	$ErrMsg = _('The Supplier Transactions could not be updated because');
	$DbgMsg = _('The SQL that failed was');
	$UpdateResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

}
echo '<p align="center">';
echo '<a href="SelectSupplier.php?modulosel=4">';
echo '<font size=2 face="arial">';
echo _('Regresar a Opciones del Proveedor');
echo '</font>';
echo '</a>';
///
$address=$SupplierRecord['address1'].' '.$SupplierRecord['address2'];

echo '<table border=1 align=center style="margin:auto;"><tr>
	<th colspan=5>' . _('DATOS GENERALES') . '</th></td>';
	echo '<tr><td><img src="'.$rootpath.'/css/'.$theme.'/images/supplier.png" title="'._('Supplier') . '" alt="">' . ' ' . _('Proveedor') . ' : </td>';
	echo '<td colspan=3>'.$SupplierID,'-' . $SupplierRecord['suppname']. '</td</tr>';
	echo '<tr><td><b>' . _(' Direccion') . ':</b></td><td>' . $address . '</td>';
	echo '<td><b>' . _('Condiciones de Pago') . ' : </b></td><td>' . $SupplierRecord['terms'] . '</td></tr>';
	echo '<tr><td><b>' . _(' RFC') . ':</b></td><td>' . $SupplierRecord['taxid'] . '</td>';
	/*echo '<tr><td><b>' . _(' Comentarios') . ':</b></td><td colspan=3><textarea name=coments cols=80 rows=2 >'. $SupplierComentarios['coments'] .'</textarea></td></tr>';
	$sqldoc="SELECT count(*)
		FROM prddocumentos 
		WHERE propietarioid='" . $SupplierID . "' AND tipopropietarioid=2";
	$doc= DB_query($sqldoc,$db);
	$myrowdoc=DB_fetch_row($doc,$db);
	
	if($myrowdoc[0]>0)
		{
			echo "<tr><td style='text-align:center; background-color:white;
			border:thin;'><a href='#' onclick='javascript:
			window.open(\"prdABCReporteDocumentos.php?propietarioid=" . $SupplierID . "&tipopropietarioid=2\", \"\",
			\"toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550\"); return false'>
			<img src='images/conarchivos.gif'  title='Con archivos'></a></td>";   
		}else{
			echo "<tr><td style='text-align:center; background-color:white; border:thin;'><a href='#' onclick='javascript:
			window.open(\"prdABCReporteDocumentos.php?propietarioid=" . $SupplierID . "&tipopropietarioid=2\", \"\",
			\"toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550\"); return false'>
			"._('subir archivo')."</a></td>"; 
		}
	echo "<td colspan=3><div class='centre'><input type='Submit' name='enviar' value='" . _('Guardar Comentarios') . "'></div>";*/
	echo  "</tr>";

////
echo "<tr><td colspan=5 width=100%>";
echo "<table  width=100% border=1><tr><th>" . _('Saldo a HOY') . 
	  "</th><th>" . _('Al Corriente') . 
	  "</th><th>" . _('Vencido') . 
	  "</th><th>" . $_SESSION['PastDueDays1'] . '-' . $_SESSION['PastDueDays2'] . 
	  ' ' . _('D�as Vencido') . 
	  "</th><th>" . _('M�s de') . ' ' . $_SESSION['PastDueDays2'] . ' ' . _('D�as Vencido') . '</th></tr>';

echo '<tr><td style="text-align:right;">$ ' . number_format($SupplierRecord['balance'],2) . 
	  '</td><td style="text-align:right;">$ ' . number_format(($SupplierRecord['balance'] - $SupplierRecord['due']),2) . 
	  '</td><td style="text-align:right;">$ ' . number_format(($SupplierRecord['due']-$SupplierRecord['overdue1']),2) . 
	  '</td><td style="text-align:right;">$ ' . number_format(($SupplierRecord['overdue1']-$SupplierRecord['overdue2']) ,2) . 
	  '</td><td style="text-align:right;">$ ' . number_format($SupplierRecord['overdue2'],2) . '</td></tr></table>
	  
	  </td></tr>';
	  
 $ssql='Select SUM((supptrans.ovamount + supptrans.ovgst)/supptrans.rate) as tot';
		    $ssql.= ' FROM supptrans JOIN tags ON supptrans.tagref = tags.tagref';
		    $ssql.= ' WHERE supplierno="'.$_SESSION['SupplierID'].'" and supptrans.trandate <= "'.$fechafinsinhoras.'" and tags.legalid = "'.$_POST['razonsocial'].'"';
		    $resultffin = DB_query($ssql,$db,$ErrMsg);
		    $myrowffin = DB_fetch_array($resultffin);
		    $saldoafechafin = $myrowffin[0];
		    
echo "<tr><td colspan=5 width=100%>";
echo "<table  width=100% border=0><tr><th colspan=5></th></tr>";

echo '<tr><td style="text-align:right;">' . _('Saldo al:  ') . $fechafinsinhoras . 
	  ' segun la selaccion de criterios de abajo:   $ ' . number_format($saldoafechafin,2) . '</td></tr></table>
	  </td></tr>';
	  
echo '</table>';
					
					/******* Seleccion de filtros *******/
					
echo "<br><div class='centre'><form action='" . $_SERVER['PHP_SELF'] . "?" . SID . "' method=post>";

echo "<table border='0' cellpadding='1' cellspacing='1' align='center' style='margin:auto;'>";
echo "<tr>";
echo "<td colspan=4><br>
	<div class='centre' style='font-size:8pt;'>";
	echo "&nbsp;&nbsp;&nbsp;<b>***</b>"._('Razon Social ') . "";
	echo "<select name='razonsocial' style='font-size:7pt;'>";
	echo "<option selected value=''>Todas Las Razones S.</option>";
	$sql = "SELECT distinct l.legalid, l.legalname
		FROM tags t, legalbusinessunit l, sec_unegsxuser uxu
		WHERE t.tagref=uxu.tagref
		AND uxu.userid='".$_SESSION['UserID']."'
		AND t.legalid = l.legalid
		ORDER BY l.legalname";//
	$resultTags = DB_query($sql,$db,'','');

	while ($xmyrow=DB_fetch_array($resultTags))
	{
		if ($xmyrow['legalid'] == $_POST['razonsocial']) {
			echo "<option selected Value='" . $xmyrow['legalid'] . "'>" . $xmyrow['legalname'] .'</option>';
		}
		else {
			echo "<option Value='" . $xmyrow['legalid'] . "'>" . $xmyrow['legalname'] .'</option>';
		}
	}
	echo "</select></td>";
echo "<td colspan=4><br>
        <div class='centre' style='font-size:8pt;'>";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>***</b>"._('Documentos Por Unidad de Negocio ') . "";
	$sql = "SELECT t.tagref, t.tagdescription
		FROM tags t, sec_unegsxuser uxu
		WHERE t.tagref=uxu.tagref
		AND uxu.userid='".$_SESSION['UserID']."'
		ORDER BY tagdescription";
	$resultTags = DB_query($sql,$db,'','');
	echo "<select name='todaslasunidades' style='font-size:7pt;'>";
	echo'<option selected Value="">Todas Las Unidades</option>';
	while ($xmyrow=DB_fetch_array($resultTags))
	{
		if ($xmyrow['tagref'] == $_POST['todaslasunidades']) {
			echo "<option selected Value='" . $xmyrow['tagref'] . "'>" . $xmyrow['tagdescription'] .'</option>';
		}
		else {
			echo "<option Value='" . $xmyrow['tagref'] . "'>" . $xmyrow['tagdescription'] .'</option>';
		}
	}
	echo '</select></td>';
	
echo "<tr><td style='font-size:8pt;' colspan=4>";
	echo "&nbsp;&nbsp;&nbsp;<b>***</b><input type='checkbox' name=solosaldo value=1";
	if (isset($_POST['solosaldo'])){
	  echo " checked";
	}
	echo ">"._('Mostrar documentos ya saldados') . "";
echo "</td>";

echo "<td colspan=4>";
	echo "&nbsp;&nbsp;&nbsp;<b>***</b><input type='checkbox' name=mabonoscargos value=1";
	if (isset($_POST['mabonoscargos'])){
	  echo " checked";
	}
	echo ">&nbsp;&nbsp;"._('Mostrar abonos y despues cargos') . "";
echo "</td>";

echo "</table>";
echo "<table style='margin:auto;' border='0-'>";
echo '</tr>
	       <tr>
		    <td>' . _('Movimientos Desde:') . '</td>
		    <td><select Name="FromDia">';
			 $sql = "SELECT * FROM cat_Days";
			 $dias = DB_query($sql,$db,'','');
			 while ($myrowdia=DB_fetch_array($dias,$db)){
			     $diabase=$myrowdia['DiaId'];
			     if (rtrim(intval($FromDia))==rtrim(intval($diabase))){ 
				 echo '<option  VALUE="' . $myrowdia['DiaId'] .  '" selected>' .$myrowdia['Dia'] . "</option>";
			     }else{
				 echo '<option  VALUE="' . $myrowdia['DiaId'] .  '">' .$myrowdia['Dia'] . "</option>";
			     }
			 }
	            echo'</td>'; 
		    echo '<td align="rigth"><select Name="FromMes">';
			      $sql = "SELECT * FROM cat_Months";
			      $Meses = DB_query($sql,$db,'','');
			      while ($myrowMes=DB_fetch_array($Meses,$db)){
				  $Mesbase=$myrowMes['u_mes'];
				  if (rtrim(intval($FromMes))==rtrim(intval($Mesbase))){ 
				      echo '<option  VALUE="' . $myrowMes['u_mes'] .  '" selected>' .$myrowMes['mes'] . "</option>";
				  }else{
				      echo '<option  VALUE="' . $myrowMes['u_mes'] .  '">' .$myrowMes['mes'] . "</option>";
				  }
			      }
			      
			      echo '</select>';
			      echo '&nbsp;<input name="FromYear" type="text" size="4" value='.$FromYear.'>';
				      
		      echo '</td>
		    <td>
			 &nbsp;
	            </td>
	            <td>' . _('Hasta:') . '</td>';
		    echo'<td><select Name="ToDia">';
			      $sql = "SELECT * FROM cat_Days";
			      $Todias = DB_query($sql,$db,'','');
			      while ($myrowTodia=DB_fetch_array($Todias,$db)){
				  $Todiabase=$myrowTodia['DiaId'];
				  if (rtrim(intval($ToDia))==rtrim(intval($Todiabase))){ 
				      echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '" selected>' .$myrowTodia['Dia'] . "</option>";
				  }else{
				      echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '">' .$myrowTodia['Dia'] . "</option>";
				  }
			      }
		    echo '</td>';
		    echo'<td>';
			 echo'<select Name="ToMes">';
			 $sql = "SELECT * FROM cat_Months";
			 $ToMeses = DB_query($sql,$db,'','');
			 
			 while ($myrowToMes=DB_fetch_array($ToMeses,$db)){
			     $ToMesbase=$myrowToMes['u_mes'];
			     if (rtrim(intval($ToMes))==rtrim(intval($ToMesbase))){ 
				 echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '" selected>' .$myrowToMes['mes'] . "</option>";
			     }else{
				 echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '">' .$myrowToMes['mes'] . "</option>";
			     }
			 }
			 echo '</select>';
			 echo '&nbsp;<input name="ToYear" type="text" size="4" value='.$ToYear.'>';
			 
		    echo'</td>
		</tr>';
		echo "<tr height='10'><td colspan='7'></td></tr>";
		echo "<tr><td colspan=3><center><input tabindex=2 type=submit name='Refresh Inquiry' value='" . _('Mostrar') . "'></center></td>";
		echo "<td></td>";
		echo '<td colspan=3><center><input tabindex="7" type=submit name="PrintEXCEL" value="' . _('Exportar a Excel') . '"></center></td>';
		echo "</tr></table>";


echo '</div>';
}
//fin de filtros
/*if ( isset($_POST['RefreshInquiry']) ) {
	$DateAfterCriteria = FormatDateForSQL($_POST['TransAfterDate']);
	$DateBeforeCriteria = FormatDateForSQL($_POST['TransBeforeDate']);
	
	$SQL = "SELECT supptrans.id, 
			systypescat.typename, 
			supptrans.type, 
			supptrans.transno, 
			supptrans.trandate,
			supptrans.duedate,
			promisedate, 
			supptrans.suppreference, 
			supptrans.rate, 
			(supptrans.ovamount + supptrans.ovgst) AS totalamount,
			supptrans.ovamount,
			supptrans.ovgst,
			supptrans.alloc AS allocated, 
			supptrans.hold, 
			supptrans.settled, 
			supptrans.transtext,
			supptrans.supplierno,
			tagdescription,
			suppliers.suppname,
			suppliers.currcode,
			supptrans.tagref,
			supptrans.folio
		FROM supptrans, 
			systypescat, tags t, suppliers 
		WHERE supptrans.type = systypescat.typeid
		AND t.tagref=supptrans.tagref
		AND supptrans.supplierno = '" . $SupplierID . "' 
		AND supptrans.trandate >= '".$DateAfterCriteria ."'
		AND supptrans.trandate <= '".$DateBeforeCriteria ."'
		AND suppliers.supplierid = supptrans.supplierno";
		
	//echo "SQL:<br>".$SQL;

	if (!isset($_POST['todaslasunidades'])){
		$SQL .= " AND supptrans.tagref  in (select tagref from sec_unegsxuser where userid='".$_SESSION['UserID']. "')";
	};	     
     

	if (isset($_POST['solosaldo'])){
		$SQL .= " AND (supptrans.ovamount + supptrans.ovgst - supptrans.alloc)>0";
	};
	
	if (isset($_POST['tag']) AND $_POST['tag']!='0'){
		$SQL .= " AND supptrans.tagref='".$_POST['tag']."'";
	}
	
	$SQL .= " ORDER BY supptrans.trandate";
		 
	$ErrMsg = _('No transactions were returned by the SQL because');
	$DbgMsg = _('The SQL that failed was');
	
	$TransResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);
	
	if (DB_num_rows($TransResult) == 0){
		echo '<p><div class="centre">' . _('No hay transacciones que desplegar desde') . ' ' . $_POST['TransAfterDate'];
		echo '</div>';
		include('includes/footer.inc');
		exit;
	}
}*/

if (isset($_POST['PrintEXCEL'])) {

	header("Content-type: application/ms-excel");
	# replace excelfile.xls with whatever you want the filename to default to
	header("Content-Disposition: attachment; filename=ReportSupplier.xls");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

	echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
	echo '<link rel="shortcut icon" href="'. $rootpath . '/favicon.ico" />';
	echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';
 }
	    
$DateAfterCriteria = FormatDateForSQL($_POST['TransAfterDate']);

$_SESSION['natcontable'] = array();
$SQL = "SELECT * FROM systypescat";
$ErrMsg = _('No transactions were returned by the SQL because');
$TransResult = DB_query($SQL,$db,$ErrMsg);

while ($myrow=DB_fetch_array($TransResult)) {
	$_SESSION['natcontable'][$myrow['typeid']] = $myrow['naturalezacontable'];
	
}

///********************************************** 1era. Funci�n *****************************************///
if(isset($_POST['PrintEXCEL'])){
echo '<br><table cellspacing=0 border=1 bordercolor=DarkBlue cellpadding=2 colspan="7" style="margin:auto;">';
		echo "<tr>
			<th style='background-color:#f2fcbd;'><b><font face='arial' size=1 color='#5b5202'>" . _(' ') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('#') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Fecha') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Unidad<br>Negocio') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'></th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Folios') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'></th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Referencia') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Concepto') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Cargo') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Abono') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Saldo<br>Pendiente') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('T.C.') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Fecha<br>Vencimiento') . "</th>";
			/*if (Havepermission($_SESSION['UserID'],349, $db)==1){
			echo "<th  colspan=2 style='background-color:#f2fcbd;'><b><font size=1 color='#5b5202'>" . _('Corregir') . "</th>";
			}*/
			
}else{
	$newlink = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a target='_blank' href='PDFdocumentosProveedor.php?legalid=".$_POST['razonsocial']."&supplierno=$SupplierID&fechai=$fechaini&fechaf=$fechafin&proveedor=".$SupplierID."-" . $SupplierRecord['suppname']."&todaslasunidades=".$_POST['todaslasunidades']."&solosaldo=".$_POST['solosaldo']."&mabonoscargos=".$_POST['mabonoscargos']."'><img src='images/PDF.gif' border=0>"._('Nuevo PDF')."</a>";
	echo '<br><table cellspacing=0 border=1 bordercolor=DarkBlue cellpadding=2 colspan="7" style="margin:auto;">';
	echo "<tr><td colspan=25 style='text-align:center'>
		  <!-- <a target='_blank' href='".$_SERVER['PHP_SELF']."?pdf=1&fi=$fechaini&ff=$fechafin&cliente=".$CustomerID,'-' . $CustomerRecord['name']."'><img src='images/PDF.gif' border=0>"._('Ver en PDF')."</a> -->
		  $newlink
		  </td></tr>";
		echo "<tr>
			<th style='background-color:#f2fcbd;'><b><font face='arial' size=1 color='#5b5202'>" . _(' ') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('#') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Fecha') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Unidad<br>Negocio') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'></th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Folios') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'></th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Referencia') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Concepto') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Cargo') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Abono') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Saldo<br>Pendiente') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('T.C.') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Fecha<br>Vencimiento') . "</th>";
			/*if (Havepermission($_SESSION['UserID'],349, $db)==1){
			echo "<th  colspan=2 style='background-color:#f2fcbd;'><b><font size=1 color='#5b5202'>" . _('Corregir') . "</th>";
			}*/
		if($_SESSION['subirxmlprov'] == 1){
			echo "<th colspan=5  style='background-color:#f2fcbd;'><b><font size=1 color='#5b5202'>" . _('M�s Informaci�n') . "</th>";
		}else{
			echo "<th colspan=4  style='background-color:#f2fcbd;'><b><font size=1 color='#5b5202'>" . _('M�s Informaci�n') . "</th>";
		}
			
			echo "</tr>";
}		

session_start();


function estadocuenta($id,$SupplierID,$nivel,$tiponaturaleza,$TipoCambio,$fechaini,$fechafin,$db, &$saldoTotalTransacciones){
	
	$saldoTotalTransacciones = 0;
	
	$DateAfterCriteria = FormatDateForSQL($_POST['TransAfterDate']);
	if ($id == 0)
	{
		//echo "tipo C".$TipoCambio;
		$SQL = "SELECT systypescat.typename,
			supptrans.id,
			supptrans.type,
			supptrans.supplierno,
			supptrans.order_,
			supptrans.transno,
			supptrans.suppreference,
			supptrans.origtrandate,
			supptrans.trandate,
			supptrans.duedate,
			supptrans.transtext,
			supptrans.rate,
			supptrans.folio,
			(supptrans.ovamount + supptrans.ovgst ) AS totalamount,";
						
			if ($TipoCambio == "MXN"){
				$SQL .=  " ((supptrans.ovamount + supptrans.ovgst)- supptrans.alloc) AS totalamount2,";	
			}elseif ($TipoCambio == "USD"){
				$SQL .= " ((supptrans.ovamount + supptrans.ovgst - supptrans.alloc)/ supptrans.rate) AS totalamount2,";
				
			}
			$SQL .= "
			supptrans.ovamount as monto,
			supptrans.ovgst as iva,
			supptrans.alloc AS allocated,
			tags.tagdescription,
			tags.tagref,
			supptrans.folio,
			CASE WHEN (
				DATEDIFF(now(),supptrans.trandate)>=0 and (((supptrans.ovamount + supptrans.ovgst))
										-supptrans.alloc) > 0.9)
				THEN '#fb8888' ELSE
				CASE WHEN (DATEDIFF(now(),supptrans.trandate)>=-11 AND(((supptrans.ovamount + supptrans.ovgst
					)) -supptrans.alloc) > 0.9) THEN '#f2fcbd' ELSE '#ffffff' END END AS color,
			supptrans.rate as cargorate
			
			FROM systypescat,tags,supptrans
			LEFT JOIN purchorders s ON supptrans.order_=s.orderno
			AND supptrans.supplierno=s.supplierno
			WHERE supptrans.type = systypescat.typeid";
			
			if ($TipoCambio == "MXN"){
				$SQL .= " and supptrans.rate = 1 "; 	
			}elseif ($TipoCambio == "USD"){
				$SQL .= " and supptrans.rate <> 1 ";
			}
			
			$SQL .= "	AND tags.tagref=supptrans.tagref
					AND supptrans.type in (20,21,27,34,124,160,117,121,470,440,410,495,160,801,803)
					AND supptrans.supplierno = '" . $SupplierID . "'
					AND (tags.legalid = '" . $_POST['razonsocial'] . "' or '" . $_POST['razonsocial'] ."' = '')
					AND case when 	 supptrans.type in (27,801) then ovamount>0 else abs(ovamount)>0 end	
							";
			
			if (isset($fechaini) AND isset($fechafin)) {
			    $SQL .= " AND supptrans.origtrandate between '".$fechaini."' and '".$fechafin."'";				
			}
			/*if (isset($DateAfterCriteria)) {
				$SQL .= " AND supptrans.trandate >='". $DateAfterCriteria. "'";
			}*/
			if (isset($_POST['todaslasunidades']) and strlen($_POST['todaslasunidades'])>0) {
				$SQL .= " AND supptrans.tagref=".$_POST['todaslasunidades'];
			}
			
			if (!isset($_POST['todaslasunidades'])){
			  $SQL .= " AND supptrans.tagref  in (select tagref from sec_unegsxuser where userid='".$_SESSION['UserID']."')";
			};
			
			if (!isset($_POST['solosaldo'])){
				$SQL .= " AND abs(supptrans.ovamount + supptrans.ovgst-supptrans.alloc)>".$_SESSION['MaxDecimalcxc'];
			}
		
			$SQL .= " ORDER BY origtrandate, trandate";
			
			if ($_SESSION["UserID"] == "admin"){
				//echo "<br><pre>Cargos1:".$SQL;
			}
			
	}elseif($id>0){
        
		$SQL =     "SELECT systypescat.typename,
				supptrans.id,
				supptrans.type,
				supptrans.supplierno,
				supptrans.order_,
				supptrans.transno,
				supptrans.origtrandate,
				supptrans.trandate,
				supptrans.duedate,
				supptrans.suppreference,
				supptrans.transtext,
				supptrans.rate,
				supptrans.folio,
				(supptrans.ovamount + supptrans.ovgst) AS totalamount,
				((supptrans.ovamount + supptrans.ovgst)- supptrans.alloc) AS totalamount2,
				supptrans.ovamount as monto,
				supptrans.ovgst as iva,
				supptrans.alloc AS allocated,
				tags.tagdescription,
				tags.tagref,
				tags.legalid,
				supptrans.folio,
				suppallocs.transid_allocto,
				suppallocs.transid_allocfrom,
				suppallocs.amt,
				CASE WHEN ( DATEDIFF(now(),supptrans.trandate)>=0
				AND (((supptrans.ovamount + supptrans.ovgst)) -supptrans.alloc) > 0.9) THEN '#fb8888' ELSE
				CASE WHEN (DATEDIFF(now(),supptrans.trandate)>=-11
				AND(((supptrans.ovamount + supptrans.ovgst)) -supptrans.alloc) > 0.9)
				THEN '#f2fcbd' ELSE '#ffffff' END END AS color,
				supptrans.rate as cargorate
				FROM systypescat,tags,supptrans";
				if($tiponaturaleza==0){
				$SQL .= " INNER JOIN suppallocs ON supptrans.id=suppallocs.transid_allocfrom";	
				}
				if($tiponaturaleza==1){
				$SQL .= " INNER JOIN suppallocs ON supptrans.id=suppallocs.transid_allocto";
				}
				$SQL .="
				
				LEFT JOIN purchorders s ON supptrans.supplierno=s.supplierno
				AND supptrans.supplierno=s.supplierno
				WHERE supptrans.type = systypescat.typeid
				AND tags.tagref=supptrans.tagref
				AND supptrans.supplierno = '" . $SupplierID . "'
				
				AND (tags.legalid = '' or '' = '')";
				
				if($tiponaturaleza==0){
				$SQL .= " AND suppallocs.transid_allocto='" . $id . "'";
				}
				if($tiponaturaleza==1){
				$SQL .= " AND suppallocs.transid_allocfrom='" . $id . "'";	
				}
				if (isset($_POST['todaslasunidades']) and strlen($_POST['todaslasunidades'])>0) {
				$SQL .= " AND supptrans.tagref=".$_POST['todaslasunidades'];
				}
		
				if (!isset($_POST['todaslasunidades'])){
				$SQL .= " AND supptrans.tagref  in (select tagref from sec_unegsxuser where userid='".$_SESSION['UserID']."')";
				};
				$SQL .= " GROUP BY transno,folio";
				
				
							//echo "<br>abonos:".$SQL;
						//	exit;
	}elseif($id== -1){
		//		(((supptrans.ovamount + supptrans.ovgst)/ supptrans.rate) - alloc) AS totalamount2,
		$SQL = 	"SELECT systypescat.typename,
				supptrans.id,
				supptrans.type,
				supptrans.supplierno,
				supptrans.order_,
				supptrans.transno,
				supptrans.origtrandate,
				supptrans.trandate,
				supptrans.duedate,
				supptrans.suppreference,
				supptrans.transtext,
				supptrans.rate,
				supptrans.folio,
				(supptrans.ovamount + supptrans.ovgst) AS totalamount,";
				if ($TipoCambio == "MXN"){
				$SQL .=  " ((supptrans.ovamount + supptrans.ovgst)- supptrans.alloc) AS totalamount2,";	
				}elseif ($TipoCambio == "USD"){
				$SQL .= " ((supptrans.ovamount + supptrans.ovgst - supptrans.alloc)/ supptrans.rate) AS totalamount2,";
				}
				$SQL .=  "
				supptrans.ovamount as monto,
				supptrans.ovgst as iva,
				supptrans.alloc AS allocated,
				tags.tagdescription,
				tags.tagref,
				suppallocs.amt,
				CASE WHEN (
					DATEDIFF(now(),supptrans.trandate)>=0 and (((supptrans.ovamount + supptrans.ovgst))
											-supptrans.alloc) > 0.9)
					THEN '#fb8888' ELSE
					CASE WHEN (DATEDIFF(now(),supptrans.trandate)>=-11 AND(((supptrans.ovamount + supptrans.ovgst)) -supptrans.alloc) > 0.9) THEN '#f2fcbd' ELSE '#ffffff' END END AS color,
				supptrans.rate as cargorate
				FROM systypescat,tags,supptrans
				LEFT JOIN purchorders s ON supptrans.order_=s.orderno
				LEFT JOIN suppallocs ON supptrans.id=suppallocs.transid_allocto
				AND supptrans.supplierno=s.supplierno
				AND supptrans.supplierno=s.supplierno
				WHERE supptrans.type = systypescat.typeid
				AND tags.tagref=supptrans.tagref
				/*AND supptrans.trandate between '".$DateSinceCriteria."' and '".$DateAfterCriteria."'*/
				/*and debtortrans.alloc=0*/
				AND supptrans.type in (22,32,33,37,121,116,480,24,490,27,801,501)
						AND case when supptrans.type in (27,801) then ovamount<0 else abs(ovamount)>0 end	
				AND abs((supptrans.ovamount + supptrans.ovgst ) - supptrans.alloc)>=.01";
				if ($TipoCambio == "MXN"){
				$SQL .= " and supptrans.rate = 1 "; 	
				}elseif ($TipoCambio == "USD"){
				$SQL .= " and supptrans.rate <> 1 ";
				}
				$SQL .= "
				AND supptrans.supplierno = '" . $SupplierID . "'
				
				AND (tags.legalid = '" . $_POST['razonsocial'] . "' or '" . $_POST['razonsocial'] ."' = '')";
				if (isset($fechaini) AND isset($fechafin)) {
				$SQL .= " AND supptrans.origtrandate between '".$fechaini."' and '".$fechafin."'";				
				}
				if (isset($_POST['todaslasunidades']) and strlen($_POST['todaslasunidades'])>0) {
					$SQL .= " AND supptrans.tagref=".$_POST['todaslasunidades'];
				}
				if (!isset($_POST['todaslasunidades'])){
				  $SQL .= " AND supptrans.tagref  in (select tagref from sec_unegsxuser where userid='".$_SESSION['UserID']."')";
				};
				
				if (!isset($_POST['solosaldo'])){
					$SQL .= " AND abs(supptrans.ovamount + supptrans.ovgst-alloc)>".$_SESSION['MaxDecimalcxc'];
				}
				/*				
				if (isset($_POST['solosaldo'])){
				  $SQL .= " AND abs(supptrans.ovamount + supptrans.ovgst -alloc)>".$_SESSION['MaxDecimalcxc'];
				};
				*/
				/*$SQL .= "GROUP BY debtorno,folio";*/
				/*$SQL .= " ORDER BY origtrandate, trandate";*/
				
				
				
	
	}elseif($id== -2){
	
		$SQL = 	"SELECT systypescat.typename,
				supptrans.id,
				supptrans.type,
				supptrans.supplierno,
				supptrans.order_,
				supptrans.transno,
				supptrans.origtrandate,
				supptrans.trandate,
				supptrans.duedate,
				supptrans.suppreference,
				supptrans.transtext,
				supptrans.rate,
				supptrans.folio,
				(supptrans.ovamount + supptrans.ovgst) AS totalamount,";
				if ($TipoCambio == "MXN"){
				$SQL .=  " ((supptrans.ovamount + supptrans.ovgst)- supptrans.alloc) AS totalamount2,";	
				}elseif ($TipoCambio == "USD"){
				$SQL .= " ((supptrans.ovamount + supptrans.ovgst - supptrans.alloc)/ supptrans.rate) AS totalamount2,";
				}
				$SQL .=  "
				supptrans.ovamount as monto,
				supptrans.ovgst as iva,
				supptrans.alloc AS allocated,
				tags.tagdescription,
				tags.tagref,
				suppallocs.amt,
				CASE WHEN (
					DATEDIFF(now(),supptrans.trandate)>=0 and (((supptrans.ovamount + supptrans.ovgst))
											-supptrans.alloc) > 0.9)
					THEN '#fb8888' ELSE
					CASE WHEN (DATEDIFF(now(),supptrans.trandate)>=-11 AND(((supptrans.ovamount + supptrans.ovgst)) -supptrans.alloc) > 0.9) THEN '#f2fcbd' ELSE '#ffffff' END END AS color,
					
				doctocargo.rate as cargorate
				FROM systypescat,tags,supptrans
				LEFT JOIN purchorders s ON supptrans.order_=s.orderno
				LEFT JOIN suppallocs ON supptrans.id=suppallocs.transid_allocfrom
				AND supptrans.supplierno=s.supplierno
				LEFT JOIN supptrans as doctocargo ON doctocargo.id=suppallocs.transid_allocto
				WHERE supptrans.type = systypescat.typeid
				AND tags.tagref=supptrans.tagref
				
				AND supptrans.type in (22,32,33,37,121,116,480,24,490,501)
				AND case when 	 supptrans.type in (27,801) then ovamount<0 else abs(ovamount)>0 end			
						";
				
				if ($TipoCambio == "MXN"){
				$SQL .= " and supptrans.rate = 1 "; 	
				}elseif ($TipoCambio == "USD"){
				$SQL .= " and supptrans.rate <> 1 ";
				}
				$SQL .= " 
				AND supptrans.supplierno = '" . $SupplierID . "'
				
				AND (tags.legalid = '" . $_POST['razonsocial'] . "' or '" . $_POST['razonsocial'] ."' = '')";
				if (isset($fechaini) AND isset($fechafin)) {
				$SQL .= " AND supptrans.origtrandate between '".$fechaini."' and '".$fechafin."'";				
				}
				if (isset($_POST['todaslasunidades']) and strlen($_POST['todaslasunidades'])>0) {
					$SQL .= " AND supptrans.tagref=".$_POST['todaslasunidades'];
				}
				if (!isset($_POST['todaslasunidades'])){
				  $SQL .= " AND supptrans.tagref  in (select tagref from sec_unegsxuser where userid='".$_SESSION['UserID']."')";
				};
				
				if (!isset($_POST['solosaldo'])){
					$SQL .= " AND abs(supptrans.ovamount + supptrans.ovgst-supptrans.alloc)>".$_SESSION['MaxDecimalcxc'];
				}
				
				/*
				if (isset($_POST['solosaldo'])){
				  $SQL .= " AND abs(supptrans.ovamount + supptrans.ovgst -alloc)>".$_SESSION['MaxDecimalcxc'];
				};
				*/
				
				/*$SQL .= "GROUP BY debtorno,folio";*/
				/*$SQL .= " ORDER BY origtrandate, trandate";*/
				
				
	
	}elseif($id== -3){
		$SQL = "SELECT systypescat.typename,
			supptrans.id,
			supptrans.type,
			supptrans.supplierno,
			supptrans.order_,
			supptrans.transno,
			supptrans.suppreference,
			supptrans.origtrandate,
			supptrans.trandate,
			supptrans.duedate,
			supptrans.transtext,
			supptrans.rate,
			supptrans.folio,
			(supptrans.ovamount + supptrans.ovgst ) AS totalamount,";
						
			if ($TipoCambio == "MXN"){
				$SQL .=  " ((supptrans.ovamount + supptrans.ovgst)- supptrans.alloc) AS totalamount2,";	
			}elseif ($TipoCambio == "USD"){
				$SQL .= " (((supptrans.ovamount + supptrans.ovgst) - supptrans.alloc)/ supptrans.rate) AS totalamount2,";
			}
			$SQL .= "
			supptrans.ovamount as monto,
			supptrans.ovgst as iva,
			supptrans.alloc AS allocated,
			tags.tagdescription,
			tags.tagref,
			supptrans.folio,
			CASE WHEN (
				DATEDIFF(now(),supptrans.trandate)>=0 and (((supptrans.ovamount + supptrans.ovgst))
										-supptrans.alloc) > 0.9)
				THEN '#fb8888' ELSE
				CASE WHEN (DATEDIFF(now(),supptrans.trandate)>=-11 AND(((supptrans.ovamount + supptrans.ovgst
					)) -supptrans.alloc) > 0.9) THEN '#f2fcbd' ELSE '#ffffff' END END AS color,
			supptrans.rate as cargorate
			
			FROM systypescat,tags,supptrans
			LEFT JOIN purchorders s ON supptrans.order_=s.orderno
			AND supptrans.supplierno=s.supplierno
			WHERE supptrans.type = systypescat.typeid";
			
			if ($TipoCambio == "MXN"){
				$SQL .= " and supptrans.rate = 1 "; 	
			}elseif ($TipoCambio == "USD"){
				$SQL .= " and supptrans.rate <> 1 ";
			}
			
			$SQL .= "AND tags.tagref=supptrans.tagref
			AND supptrans.type in (20,21,34,32,33,37,117,121,470,440,480,116,410,495,501)
			
			AND supptrans.supplierno = '" . $SupplierID . "'
			AND (tags.legalid = '" . $_POST['razonsocial'] . "' or '" . $_POST['razonsocial'] ."' = '')";
			if (isset($fechaini) AND isset($fechafin)) {
			    $SQL .= " AND supptrans.origtrandate between '".$fechaini."' and '".$fechafin."'";				
			}
			/*if (isset($DateAfterCriteria)) {
				$SQL .= " AND supptrans.trandate >='". $DateAfterCriteria. "'";
			}*/
			if (isset($_POST['todaslasunidades']) and strlen($_POST['todaslasunidades'])>0) {
				$SQL .= " AND supptrans.tagref=".$_POST['todaslasunidades'];
			}
			
			if (!isset($_POST['todaslasunidades'])){
			  $SQL .= " AND supptrans.tagref  in (select tagref from sec_unegsxuser where userid='".$_SESSION['UserID']."')";
			};
			
			if (!isset($_POST['solosaldo'])){
				$SQL .= " AND abs(supptrans.ovamount + supptrans.ovgst-supptrans.alloc)>".$_SESSION['MaxDecimalcxc'];
			}
			
			$SQL .= " ORDER BY origtrandate, trandate";
		
		
		
		
		}
        
	$result = DB_query($SQL,$db);

		
	if (DB_num_rows($result) > 0)
	{
		
		//$_SESSION['cont'] = 0; echo
		$cont=0;
		$sumatotal=0;
		$sumapagos=0;
		$sumasaldo=0;
		$totalcargos = 0;
		$sumasaldo2=0;
		//$_SESSION['totalcargos'] = 0;
		//$_SESSION['sumapagos'] = 0;
		//$_SESSION['totalsaldo'] = 0;
		$total=0;
			
		$saldoTotalTransacciones = 0;
		
		while ($myrow = DB_fetch_array($result))
		{
				
				$idtrans=$myrow['id'];
				$_SESSION['cont'] = $_SESSION['cont'] + 1;
				$tipo = $myrow['typename'];
				$origtrandate = ConvertSQLDate($myrow['origtrandate']);	
				$trandate = ConvertSQLDate($myrow['trandate']);
				$duedate = ConvertSQLDate($myrow['duedate']);
				$transno = $myrow['transno'];
				$order = $myrow['order_'];
				$transno2 = $myrow['transno'];
				$referencia = $myrow['suppreference'];
				$iva = $myrow['iva'];
				$monto = $myrow['monto'];
				$alloc = $myrow['allocated'];
				$sumatotal = doubleval($sumatotal) + doubleval(abs($myrow['totalamount']));
				$sumapagos = doubleval($sumapagos) + doubleval(abs($myrow['allocated']));
				$comentarios=$myrow['transtext'];
				$amt=$myrow['amt'];
				$total2=$myrow['totalamount2'];
				$totalamount=$myrow['totalamount'];
				$iddocto=$myrow['id'];
				$foliox=$myrow['folio'];
				$_SESSION['$tipocambio']=(1/$myrow['rate']);
				$cargorate = $myrow['cargorate'];
				
				$saldoTotalTransacciones = $saldoTotalTransacciones + $myrow['totalamount2'];
				
				//$colorvence=$myrow['color'];
				//echo $colorvence;
				/////////////////////////////////////////////////////////////////////ASIGNA VALOR A $colorvence PARA IMPRIMIR EN LA TABLA
				
				if (isset($_POST['PrintEXCEL'])) {
				    if ($myrow['color']== '#fb8888'){
					$colorvence='*';
				    }
				}elseif ($myrow['color']== '#fb8888'){
					$colorvence='<img src="./images/red_flag_16.png" title="" alt="">';
				}else{
					$colorvence='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				}
				
				/*$vendedor=$myrow['vendedor'];
					if ($_SESSION['natcontable'][$myrow['type']] < 0 ){
					$sumasaldo = doubleval($sumasaldo) - doubleval(abs($myrow['totalamount'])-abs($myrow['allocated']));
					$_POST['sumasaldo']=$sumasaldo;
				}else{
					$sumasaldo = doubleval($sumasaldo) + doubleval(abs($myrow['totalamount'])-abs($myrow['allocated']));
					$_POST['sumasaldo']=$sumasaldo;//
				}*/
				
				$total = abs($myrow['totalamount']);
				$pagos = $myrow['allocated'];
				$saldo = abs($myrow['totalamount'])-abs($myrow['allocated']);
				$unidadnegocio = $myrow['tagdescription'];
				$tagref= $myrow['tagref'];
				$tipofac=$myrow['type'];
				$verfact=0;
				$facturacredito=0;
				if ($myrow['type']=='10')
				{
					$facturacredito=1;
				}
				if ($tipofac=='10' or $tipofac=='110' or $tipofac=='12')
				{
					$verfact=1;
				}
				
				$SQL=" SELECT l.taxid,l.address5,t.tagdescription
					FROM legalbusinessunit l, tags t
					WHERE l.legalid=t.legalid AND tagref='".$tagref."'";
				 $Result= DB_query($SQL,$db);
				if (DB_num_rows($Result)==1)
				{
					 $myrowtags = DB_fetch_array($Result);
					 $rfc=trim($myrowtags['taxid']);
					 $keyfact=$myrowtags['address5'];
					 $nombre=$myrowtags['tagdescription'];
				}
				$foliox=" "; 
				$foliox=$myrow['folio'];
				$separa = explode('|',$foliox);
				if ($tipofac=='12'){
					$serie = $separa[1];
					$folio = $separa[0];
				}else{
					$serie = $separa[0];
					$folio = $separa[1];
				}
			
				$sqltienepagares = "SELECT generatecreditnote
					FROM salesorders, paymentterms
					WHERE paytermsindicator = paymentterms.termsindicator and orderno=" . $order;
					
				  $Resulttienepagares = DB_query($sqltienepagares,$db,$ErrMsg);
				  if (DB_num_rows($Resulttienepagares)>0)
				  {
				       $myrowTienePagares=DB_fetch_row($Resulttienepagares);
				       $tienepagares=$myrowTienePagares[0];
				  }     
				  else
				  {
				       $terminopago='0';
				  }
				  
				if($totalamount == 0){
				echo '<tr style="background-color:#E6E6E6;">';	
				}else{
				echo '<tr>';
				}
				
				echo "<td nowrap style='text-align:center;'>$colorvence<font size=1></td>";/*//////////////////*///////////////////////////////////////////////////2
				
				echo "<td nowrap style='text-align:center; '><font size=1 face='arial'>".$_SESSION['cont']."</td>";
				echo "<td style='text-align:left; font-size:7pt; font-family:arial;'><b>".$trandate."</b><br></td>";
				echo "<td style='text-align:left; font-size:7pt; font-family:arial;'><b>".$unidadnegocio."</b><br></td>";
				
				echo "<td nowrap style='text-align:center;'><font size=1 face='arial'></td>";
				echo "<td nowrap style='text-align:center;'><font size=1 face='arial'>SAT:".$foliox."<br>ID:".$idtrans. "<br>
				<font style='text-align:left; font-size:7pt; font-family:arial;'><b>FACT.:".$foliofactura."<br>ERP:".$transno. "<br></b></font></td>";

				echo "<td nowrap style='text-align:center;'><font size=1 face='arial'></td>";
				
				if(isset($_POST['PrintEXCEL'])) {
					if($nivel==1){
					
					$image='+';
					$espacio2="&nbsp;&nbsp;";
					
				
					
					}elseif($nivel==2){
					
						$espacio="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
						$image='-';
						$espacio2="&nbsp;&nbsp;";
					}
				}else{
				if($nivel==1){
					
					$image='<img src="./images/circulo13x13.jpg" title="Abono" alt="">';
					$espacio2="&nbsp;&nbsp;";
					
				
					
				}elseif($nivel==2){
					
					$espacio="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
					$image='<img src="./images/circulo13x13_2.jpg" title="Abono" alt="">';
					$espacio2="&nbsp;&nbsp;";
				}
				}
				
				$comentariotmp = "";
				$allowedTypes = empty($_SESSION['ShowSuppCommentTypes']) ? '' : $_SESSION['ShowSuppCommentTypes'];
				$allowedTypes = explode(',', $allowedTypes);
				if(in_array($tipofac, $allowedTypes)) {
					$comentariotmp = "<br />" . _("Comentarios") . ": $comentarios";
				}
								
				echo "<td style='text-align:left; font-size:8pt; font-family:arial;'>".$referencia.$comentariotmp."</td>";
				echo "<td nowrap><font style='font-size:7pt; font-family:arial;'><a href='#' style='font-size:8pt; font-family:arial;' title='".strtoupper($comentarios)."' >".$espacio."".$image."".$espacio2."".strtoupper($tipo)."</a>";
				if ($vendedor != ""){
					echo "<br><span style='font-size:6pt;'>V: " . strtoupper($vendedor) . "</span></td>";	
				}
				
				if ($tiponaturaleza == 1 ){
				
					$signo ="-";
					
					$_SESSION['sumapagos']= ($_SESSION['sumapagos']) + ($amt);
					
					$typetrans=-1;
					if($nivel <= 1){
						if($_SESSION['$tipocambio'] == 1){
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";
							echo "<td nowrap class=number><font size=1 face='arial'> $" . $signo . number_format($total,2) ."</td>";
							echo "<td nowrap class=number><font size=1 face='arial'><b>$". number_format($total2,2)."</b></td>";
							$_SESSION['totalsaldo'] = $_SESSION['totalsaldo'] + $total2;
						}else{
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";
							echo "<td nowrap class=number><font size=1 face='arial'> US$" . $signo . number_format($total,2) ."<br>
											MXN $".$signo . number_format($total*$_SESSION['$tipocambio'],2) ."</td>";
							echo "<td nowrap class=number><font size=1 face='arial'><b>US$" . number_format($total2/$_SESSION['$tipocambio'],2) ."<br>
											MXN $". number_format($total2,2)."</b></td>";
							$_SESSION['totalsaldo'] = $_SESSION['totalsaldo'] + $total2;
							$_SESSION['totalsaldoUSD'] = $_SESSION['totalsaldoUSD'] + $total2/$_SESSION['$tipocambio']; //$total;	
						}
					}
					if($nivel > 1){
						if ($_POST['mabonoscargos']==1){
							if($_SESSION['$tipocambio'] == 1){
								$amt=$amt * $myrow['rate'];
								echo "<td nowrap class=number><font size=1 face='arial' >$" . $signo . number_format($amt,2) ."</td>";
								echo "<td nowrap class=number><font size=1>&nbsp;</td>";
								//echo "<td nowrap class=number><font size=1 face='arial' >" . $signo . number_format($total,2) ."</td>";
								echo "<td nowrap class=number><font size=1>&nbsp;</td>";
							}else{
								$amt=$amt * $myrow['rate'];
								echo "<td nowrap class=number><font size=1 face='arial' > US$" . $signo . number_format($amt,2) ."<br>
											MXN $".$signo . number_format($amt*$_SESSION['$tipocambio'],2) ."</td>";
								echo "<td nowrap class=number><font size=1>&nbsp;</td>";
								//echo "<td nowrap class=number><font size=1 face='arial' >" . $signo . number_format($total,2) ."</td>";
								echo "<td nowrap class=number><font size=1>&nbsp;</td>";	
							}
						}else{
							if($_SESSION['$tipocambio'] == 1){
								$alloc=$alloc * $myrow['rate'];
								echo "<td nowrap class=number><font size=1 face='arial' >$" . $signo . number_format($alloc,2) ."</td>";
								echo "<td nowrap class=number><font size=1>&nbsp;</td>";
								//echo "<td nowrap class=number><font size=1 face='arial' >" . $signo . number_format($total,2) ."</td>";
								echo "<td nowrap class=number><font size=1>&nbsp;</td>";
							}else{
								//$alloc=$alloc * $myrow['rate'];
								echo "<td nowrap class=number><font size=1 face='arial' > US$" . $signo . number_format($amt,2) ."<br>
											MXN $".$signo . number_format($amt*$_SESSION['$tipocambio'],2) ."</td>";
								echo "<td nowrap class=number><font size=1>&nbsp;</td>";
								//echo "<td nowrap class=number><font size=1 face='arial' >" . $signo . number_format($total,2) ."</td>";
								echo "<td nowrap class=number><font size=1>&nbsp;</td>";	
							}
							
						}
					}	
					
					//echo "<td nowrap class=number><font size=1>".number_format($alloc,2) ."</td>";					//echo "<td nowrap class=number><font size=1 face='arial'>0.00</td>";
					//echo "<td nowrap class=number><font size=1 face='arial'>". $signo . number_format($monto,2) ."</td>";
				}elseif($tiponaturaleza == 0 ){
					$signo = " ";
					//$_SESSION['totalcargos'] = $_SESSION['totalcargos'] + abs($total);
					$typetrans=1;
					if($nivel <= 1 ){
						if($_SESSION['$tipocambio'] == 1){
							$alloc=$alloc * $myrow['rate'];
							echo "<td nowrap class=number><font size=1 face='arial' > $" . $signo . number_format($total,2) ."</td>";
							echo "<td nowrap class=number><font size=1>".number_format($alloc,2) ."</td>";
							echo "<td nowrap class=number><font size=1 face='arial'><b>$". $signo.number_format($total2,2)."</b></td>";
							$_SESSION['totalsaldo'] = $_SESSION['totalsaldo'] + $total2;
						}else{
						//$alloc=$alloc * $myrow['rate'];
							echo "<td nowrap class=number><font size=1 face='arial' > US$" . $signo . number_format($total,2) ."<br>
											MXN $".$signo . number_format($total*$_SESSION['$tipocambio'],2) ."</td>";
							echo "<td nowrap class=number><font size=1>US$ ".number_format($alloc,2) ."<br>
											MXN $".$signo . number_format($alloc*$_SESSION['$tipocambio'],2) ."</td>";
							echo "<td nowrap class=number><font size=1 face='arial'><b> US$". $signo.number_format($total-$alloc,2)."<br>
									MXN $". $signo.number_format($total2,2)."</b></td>";
							$_SESSION['totalsaldo'] = $_SESSION['totalsaldo'] + $total2;
							$_SESSION['totalsaldoUSD'] = $_SESSION['totalsaldoUSD'] + $total-$alloc;	
						}
					}
					if($nivel > 1){
						$signo ="-";
						//echo "<td nowrap class=number><font size=1 face='arial' >" . $signo . number_format($total,2) ."</td>";
						if($_SESSION['$tipocambio'] == 1){
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";
							$amt=$amt*$myrow['rate'];
							echo "<td nowrap class=number><font size=1>".$TipoCambio."$".$signo.number_format($amt,2) ."</td>";
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";
						}else{
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";
							//$amt=$amt*$myrow['rate'];
							echo "<td nowrap class=number><font size=1> US$".$signo.number_format($amt,2) ."<br>
											MXN $".$signo . number_format($amt*$_SESSION['$tipocambio'],2) ."</td>";
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";	
						}
						//echo "<td nowrap class=number><font size=1 face='arial'>". $signo.number_format($total2,2)."</td>";
						//echo "<td nowrap class=number><font size=1 face='arial'>".number_format($total2,2)."ggggg</td>";
					}
					//echo "<td nowrap class=number><font size=1>". number_format($total,2) ."</td>";
					//echo "<td nowrap class=number><font size=1 face='arial' >". number_format($monto,2) ."</td>";
				}
								
								
				echo "<td nowrap style='text-align:center;'><font size=1>".number_format($_SESSION['$tipocambio'],2)."</td>";
				echo "<td nowrap style='text-align:center;'><font size=1>".$duedate."</td>";
				
				/*if (Havepermission($_SESSION['UserID'],349, $db)==1 and $myrow['type']>110){
				if ($_SESSION['natcontable'][$myrow['type']]>0 and $myrow['type']>110 ){
					echo "<td colspan=2  nowrap style='text-align:center;'><a href='CreditNote_DirectMagic.php?
						DebtorNo=".$myrow['debtorno']."&iddocto=".$myrow['id']."&tagref=".$tagref."'>
						<font size=1>". _('Corr Credito') . "</a></td>";
				}elseif($myrow['type']>110){
					echo "<td colspan=2  nowrap style='text-align:center;'><a href='DebitNote_DirectMagicxDocto.php?
						DebtorNo=".$myrow['debtorno']."&iddocto=".$myrow['id']."&tagref=".$tagref."'>
						<font size=1>". _('Corr Cargo') . "</a></td>";
					}
				}
				else{
						echo "<td colspan=2>&nbsp;</td>";
					}*/
				/// COMENTARIOS ///
				
					    if (Havepermission($_SESSION['UserID'],579, $db)==1)
					    {
						$sqldoc1="SELECT suppliercoments.userid as userid , accountstatussup.acountstatus as status
							FROM suppliercoments JOIN accountstatussup ON suppliercoments.idstatus = accountstatussup.idstatus
							WHERE suppliercoments.id='".$iddocto."'
							ORDER BY creationtime desc
							LIMIT 1";
							//echo '<br>ll'.$sql;
						$resultdoc1 = DB_query($sqldoc1, $db);
						if ($doc=DB_fetch_array($resultdoc1,$db)){
							
								echo "<td style='text-align:left; background-color:#FDF770'>";
								echo "<font size=1 color='#5b5202'>";
								echo "<b>";
								echo '&nbsp;';
								$pagina='ABC_CommentsXDocumentSupplier.php'.'?ID='.$iddocto.'&folio='.$foliox;
								echo "<a target='_blank' href='".$pagina."'><font size=1>".$doc['status']."</a><br>
									".$doc['userid']." ";
								echo "</b>";
								echo "</font>";
								echo "</td>";
						} elseif (isset($_POST['PrintEXCEL'])) {
							}else{
								echo "<td style='text-align:left;' nowrap>";
								echo "<font size=1>";
								echo "<b>";
								echo '&nbsp;';
								$pagina='ABC_CommentsXDocumentSupplier.php'.'?ID='.$iddocto.'&folio='.$foliox;
								echo "<a target='_blank' href='".$pagina."'><font size=1>+ Comentario</a>";
								echo "</b>";
								echo "</font>";
								echo "</td>";
							}
					    } else {
						echo "<td style='text-align:center' nowrap>";
						echo "<font size=1 color='#5b5202'>";
						echo "<b>";
						echo '&nbsp;';
						echo "</b>";
						echo "</font>";
						echo "</td>";
					    }
					 
				if(isset($_POST['PrintEXCEL'])) {
				//
				}else{
					echo "<td nowrap style='text-align:center;'><a href='GLTransInquiryV2.php?TypeID=".$myrow['type']."
						&TransNo=".$myrow['transno']."&tagref=".$tagref."'><font size=1>". _('Contable4') . "</a></td>";
				
				if($myrow['type'] == 20){
					$ligafact = "PDFSuppliersInvoice.php?iddocto=".$myrow['id']."&area=&legal=&TransNo=&Tagref=".$tagref;
					$img2 = '<img src="images/printer.png" title="' . _('Imprimir') . '" alt="">';
					echo "<td nowrap style='text-align:center;'><a TARGET='_blank' href='" . $ligafact . "'>".$img2."</a></td>";
				}else{
					echo "<td></td>";
				}	
					////
				if($_SESSION['subirxmlprov'] == 1){	
					$liga2="proveedor_ABCDocumentos.php?debtorno=".$myrow['supplierno']."&propietarioid=".$myrow['id']."&tipopropietarioid=6&muetraarchivos=1";
					$sqldoc="SELECT Count(doctoid) as documentos
					FROM suppliers_documents
					WHERE propietarioid = '".$myrow['id']."'";
					$resultdoc = DB_query($sqldoc, $db);
					$myrowdoc = DB_fetch_array($resultdoc);
					if($myrowdoc['documentos'] > 0){//
						$img = "<img width=20 height=20 src='images/conarchivos.gif'  border='1' title='"._('XML')."'><br>Doc.".$myrowdoc['documentos'];
					}else{
						$img = "<img width=20 height=20 src='images/sinarchivo.jpg'  border='1' title='"._('XML')."'>";
					}
					echo "<td nowrap style='text-align:center;'><a TARGET='_blank' href='" . $liga2 . "'>".$img."</a></td>";
					
				}//
				echo "<td nowrap >&nbsp;";
				}
				#if ($myrow['type']=="10")
				if ($myrow['totalamount'] > 0 and ($myrow['type']==10 or $myrow['type']==110))
				{
					//$liga = GetUrlToPrint($tagref,$tipofac,$db);
					//$PrintDispatchNote = $rootpath . '/' . $liga  . '&' . SID . 'TransNo=' . $transno;
					if (Havepermission($_SESSION['UserID'],290, $db)==1){
						//$existenota=ExistsNote($transno,$myrow['type'],$tagref,$db);
						if($existenota==0){
							echo "<a href='SelectNoteItems.php?InvoiceNumber=".$transno."&InvoiceType=".$myrow['type']."'><font size=1>". _('Nota<br>Credito') . "</a></td>";
						}else{
							echo "<font size=1>". _('Nota<br>Credito') . "</td>";
						}	
					}
				}
				
				if (isset($_POST['PrintEXCEL'])) {
								
				    if(($myrow['type']=="32") or ($myrow['type']=="34" or $myrow['type']=="33")){
					//echo "entra";
					if ($myrow['type']=="33"){
						$transno=$order;
						$liga = "PDFNoteOrdersSuppliers.php";
					}else{
						$liga = "PDFCreditDirectSupplier.php";
					}
					
					/*$liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' .
					'<a  target="_blank" href="'.$liga . '?tipo='.$myrow['type'].'&area='.$area.'&legal='.$legaid.'&TransNo=' . $transno .'&Tagref='.$tagref.'">'. _('Imprimir NC') .'</a>';
					echo $liga;*/
                
				    }
				}else{
					if(($myrow['type']=="32") or ($myrow['type']=="34" or $myrow['type']=="33" or $myrow['type']=="37" or $myrow['type']=="160")){
					
					if ($myrow['type']=="33"){
						$transno=$order;
						$liga = "PDFNoteOrdersSuppliers.php";
					}else{
						$transno=$transno2;
						$liga = "PDFCreditDirectSupplier.php";
					}
					
						if ($myrow['type']=="160"){
							$liga='<p><img src="./images/printer.png" title="' . _('Imprimir Factura') . '" alt="">';
							$liga.= '<a  target="_blank" href="GeneralAccountsPayableAuthProcV3.php?pdffact=1&transno='.$transno.'&type=160">'. _('Imprimir Factura') .'</a><br>';
							$liga2='<p><img src="./images/printer.png" title="' . _('Imprimir Relacion Obra') . '" alt="">';
							$liga2.= '<a  target="_blank" href="GeneralAccountsPayableUnidadNegocio.php?pdffact=1&transno='.$transno.'&type=160">'. _('Imprimir Relacion Obra') .'</a>';
						
						} else {
							$liga='<p><img src="'.$rootpath.'/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' .
							'<a  target="_blank" href="'.$liga . '?tipo='.$myrow['type'].'&area='.$area.'&legal='.$legaid.'&TransNo=' . $transno .'&Tagref='.$tagref.'">'. _('Imprimir NC') .'</a>';
							echo $liga;
						}
						
						echo $liga;
						if($liga2 <> ""){
							echo $liga2;
						}
				    }
				}
				
				//echo "<td>";
				
				/*if ($myrow['type']=="10" or $myrow['type']=="110"){
					if ($_SESSION['EnvioXSA']==1){	
					    echo '<p ><img src="./images/printer.png"  title="' . _('Imprimir Factura') . '" alt="">';
					    echo '<a  target="_blank" href="'.$_SESSION['XSA'].'xsamanager/downloadCfdWebView?
						serie='.$serie.'&folio='.$folio.'&tipo=PDF&rfc='.$rfc.'&key='.$keyfact.'"><font size=1>'. _('Factura') .'</a>';	
					} else {
						$liga = GetUrlToPrint($tagref,10,$db);
						echo '<p ><img src="./images/printer.png" title="' . _('Imprimir Factura') . '" alt="">';
						echo '<a  target="_blank" href="'.$liga.'&TransNo='.$order.'&Tagref='.$tagref.'"><font size=1>'. _('Imprimir Factura') .'</a>';    
					}
				} else {
					echo "&nbsp;";
					};
			*/
				//echo "<td style='text-align:left; font-size:7pt; font-family:arial;'>";
				/*if ($facturacredito==1 and $tienepagares<>'0')
				{
					if ($_SESSION['EnvioXSA']==1){
						echo'<img src="./images/printer.png" title="' . _('Pagares') . '"
						alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/PDFPagarePage.php?' . SID .'identifier='.$identifier . '
						&PrintPDF=Yes&TransNo=' . $transno . '"><font size=1>'. _('Pagares') .'</a>';
					}else{
					$liga = GetUrlToPrint($tagref,70,$db);
					echo '<p>';
						echo'<img src="./images/printer.png" title="' . _('Pagares') . '"
						alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/'.$liga.'&'. SID .'identifier='.$identifier . '
						&PrintPDF=Yes&TransNo=' . $transno . '"><font size=1>'. _('Pagares') .'</a>';
					}
				}elseif ($myrow['type']=="11"){
					if ($_SESSION['EnvioXSA']==1){
					    echo '<p><img src="./images/printer.png" title="' . _('Imprimir NC') . '" alt="">';
					    echo '<a  target="_blank" href="'.$_SESSION['XSA'].'xsamanager/downloadCfdWebView?
						serie='.$serie.'&folio='.$folio.'&tipo=PDF&rfc='.$rfc.'&key='.$keyfact.'"><font size=1>'. _('Imprimir NC') .'</a>';
					}else {
					    $liga = GetUrlToPrint($tagref,11,$db);
					    echo '<p><img src="./images/printer.png" title="' . _('Imprimir NC') . '" alt="">';
					    echo '<a  target="_blank" href="'.$liga.'&TransNo='.$order.'&Tagref='.$tagref.'"><font size=1>'. _('Imprimir NC') .'</a>';    
					}
				}elseif(($myrow['type']=="13") or ($myrow['type']=="21")){
					if ($_SESSION['EnvioXSA']==1){
					    echo '<p><img src="./images/printer.png" title="' . _('Imprimir NC') . '" alt="">';
					    echo '<a  target="_blank" href="'.$_SESSION['XSA'].'xsamanager/downloadCfdWebView?
						serie='.$serie.'&folio='.$folio.'&tipo=PDF&rfc='.$rfc.'&key='.$keyfact.'"><font size=1>'. _('Imprimir NC') .'</a>';
					}else {
					    $liga = GetUrlToPrint($tagref,$myrow['type'],$db);
					    echo '<p><img src="./images/printer.png" title="' . _('Imprimir NC') . '" alt="">';
					    echo '<a  target="_blank" href="'.$liga.'&TransNo='.$transno.'&Tagref='.$tagref.'"><font size=1>'. _('Imprimir NC') .'</a>';    
					}
				}else{
					echo '&nbsp;';
				     }*/
				//echo '</td>';
				/*if ($_SESSION['EnvioXSA']==1){
				echo "<td style='font-size:7pt;'>";
					if (Havepermission($_SESSION['UserID'],331, $db)==1  and $verfact==1)
					{
						
						$pagina=$_SESSION['XSA']."xsamanager/downloadCfdWebView?serie=" . $serie . "&
							folio=" . $folio . "&tipo=XML&rfc=" . $rfc . "&key=" . $keyfact ;
						echo "<a href='".$pagina."'>";
						echo "XML</a>";
					}
				echo "&nbsp;</td>";
				}*/
				echo "<tr>";
			//echo $nivel.'sss'.$tiponaturaleza;
			if ($nivel <=1){
				//echo 'entra';
				
				//$esteSaldoTotal = $saldoTotalTransacciones;
				estadocuenta($myrow['id'],$myrow['supplierno'],$nivel+1,$tiponaturaleza,$TipoCambio,$fechaini,$fechafin,$db, $esteSaldoTotal);
				//$saldoTotalTransacciones = $saldoTotalTransacciones + $esteSaldoTotal;
				
			}
						
		}
	}
			
			
}
			
	//nivel($espacio);
	$_SESSION['totalsaldo']=0;
	$_SESSION['totalsaldoUSD']=0;
	$Abonocargos=$_POST['mabonoscargos'];
	$moneda = 'MXN';
	$GranTotalSaldo = 0;
	$GranTotalSaldoUSD = 0;
	
	
	if ($Abonocargos==1){
		if($moneda = 'MXN'){
			$_SESSION['impresion']=0;
			if($_SESSION['impresion']== 0){
				if(isset($_POST['PrintEXCEL'])) {
					echo "<tr><td style='background-color:#F7F8E0;' colspan=13 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DOCUMENTOS EN PESOS&nbsp;</td></tr>";
				
					estadocuenta(-2,$SupplierID,1,1,$moneda,$fechaini,$fechafin,$db,$GranTotalSaldo);
				
					echo "<tr><td colspan=7 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
					//echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
					//echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
					//$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
					echo "<td nowrap class=number><font size=1>&nbsp;</td>";
					echo "<td nowrap class=number><font size=1>&nbsp;</td>";
					$sumasaldo=abs($_SESSION['totalsaldo']);
					echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo*-1,2)."</td>";
				}else{
					echo "<tr><td style='background-color:#F7F8E0;' colspan=16 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DOCUMENTOS EN PESOS&nbsp;</td></tr>";
				
					estadocuenta(-2,$SupplierID,1,1,$moneda,$fechaini,$fechafin,$db,$GranTotalSaldo);
				
					echo "<tr><td colspan=7 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
					//echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
					//echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
					//$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
					echo "<td nowrap class=number><font size=1>&nbsp;</td>";
					echo "<td nowrap class=number><font size=1>&nbsp;</td>";
					$sumasaldo=abs($_SESSION['totalsaldo']);
					echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo*-1,2)."</td>";
				}
			}
		}
		$_SESSION['totalsaldo']=0;
		$_SESSION['totalsaldoUSD']=0;
		if($moneda = 'MXN'){
			$_SESSION['impresion']=0;
			if($_SESSION['impresion']== 0){
				if(isset($_POST['PrintEXCEL'])) {
					echo "<tr><td style='background-color:#F7F8E0;' colspan=13 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DOCUMENTOS EN PESOS&nbsp;</td></tr>";
				
					estadocuenta(-3,$SupplierID,1,1,$moneda,$fechaini,$fechafin,$db,$GranTotalSaldo);
				
					echo "<tr><td colspan=7 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
					//echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
					//echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
					//$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
					echo "<td nowrap class=number><font size=1>&nbsp;</td>";
					echo "<td nowrap class=number><font size=1>&nbsp;</td>";
					$sumasaldo=abs($_SESSION['totalsaldo']);
					echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo,2)."</td>";
				}else{
					
					echo "<tr><td style='background-color:#F7F8E0;' colspan=16 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DOCUMENTOS EN PESOS&nbsp;</td></tr>";
				
					estadocuenta(-3,$SupplierID,1,1,$moneda,$fechaini,$fechafin,$db,$GranTotalSaldo);
				
					echo "<tr><td colspan=7 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
					//echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
					//echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
					//$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
					echo "<td nowrap class=number><font size=1>&nbsp;</td>";
					echo "<td nowrap class=number><font size=1>&nbsp;</td>";
					$sumasaldo=abs($_SESSION['totalsaldo']);
					echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo,2)."</td>";
				}
			}
		}
		$_SESSION['totalsaldo']=0;
		$moneda = 'USD';
		if($moneda = 'USD'){
		//DOLARES
			$_SESSION['impresion']=0;
			if($_SESSION['impresion']== 0){
				if(isset($_POST['PrintEXCEL'])) {
					echo "<tr><td style='background-color:#F7F8E0;' colspan=13 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DOCUMENTOS EN DOLARES&nbsp;</td></tr>";
				
					estadocuenta(-2,$SupplierID,1,0,$moneda,$fechaini,$fechafin,$db,$GranTotalSaldo);
				
					echo "<tr><td colspan=7 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
					//echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
					//echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
					//$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
					echo "<td nowrap class=number><font size=1>&nbsp;</td>";
					echo "<td nowrap class=number><font size=1>&nbsp;</td>";
					$sumasaldo=abs($_SESSION['totalsaldo']);
					echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo,2)."</td>";
				}else{
					echo "<tr><td style='background-color:#F7F8E0;' colspan=16 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DOCUMENTOS EN DOLARES&nbsp;</td></tr>";
				
					estadocuenta(-2,$SupplierID,1,0,$moneda,$fechaini,$fechafin,$db,$GranTotalSaldo);
				
					echo "<tr><td colspan=7 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
					//echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
					//echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
					//$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
					echo "<td nowrap class=number><font size=1>&nbsp;</td>";
					echo "<td nowrap class=number><font size=1>&nbsp;</td>";
					$sumasaldo=abs($_SESSION['totalsaldo']);
					echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo,2)."</td>";
				}
			}
		}
		$moneda = 'USD';
		if($moneda = 'USD'){
		//DOLARES
			$_SESSION['impresion']=0;
			if($_SESSION['impresion']== 0){
				if(isset($_POST['PrintEXCEL'])) {
					echo "<tr><td style='background-color:#F7F8E0;' colspan=13 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DOCUMENTOS EN DOLARES&nbsp;</td></tr>";
				
					estadocuenta(0,$SupplierID,1,0,$moneda,$fechaini,$fechafin,$db,$GranTotalSaldo);
				
					echo "<tr><td colspan=9 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
					//echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
					//echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
					//$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
					echo "<td nowrap class=number><font size=1>&nbsp;</td>";
					echo "<td nowrap class=number><font size=1>" . _('TOTAL EN PESOS') . ":&nbsp;</td>";
					$sumasaldo=abs($_SESSION['totalsaldo']);
					echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo,2)."</td>";
				}else{
					echo "<tr><td style='background-color:#F7F8E0;' colspan=16 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DOCUMENTOS EN DOLARES&nbsp;</td></tr>";
				
					estadocuenta(0,$SupplierID,1,0,$moneda,$fechaini,$fechafin,$db,$GranTotalSaldo);
				
					echo "<tr><td colspan=9 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
					//echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
					//echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
					//$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
					echo "<td nowrap class=number><font size=1>&nbsp;</td>";
					echo "<td nowrap class=number><font size=1>" . _('TOTAL EN PESOS') . ":&nbsp;</td>";
					$sumasaldo=abs($_SESSION['totalsaldo']);
					echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo,2)."</td>";
				}
			}
		}

	
	
	}else{
		if($moneda == 'MXN'){
			$_SESSION['impresion'] = 0;
			if($_SESSION['impresion']== 0){
				if(isset($_POST['PrintEXCEL'])) {
					echo "<tr><td style='background-color:#F7F8E0;' colspan=13 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DOCUMENTOS EN PESOS&nbsp;</td></tr>";
				
					estadocuenta(0,$SupplierID,1,0,$moneda,$fechaini,$fechafin,$db,$GranTotalSaldo);
				
					echo "<tr><td colspan=9 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
					//echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
					//echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
					//$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
					echo "<td nowrap class=number><font size=1>&nbsp;</td>";
					echo "<td nowrap class=number><font size=1>" . _('TOTAL EN PESOS') . ":&nbsp;</td>";
					$sumasaldo=abs($_SESSION['totalsaldo']);
					echo "<td nowrap class=number><font size=1>$" . number_format($sumasaldo,2) . "</td>";
					//"/".number_format($GranTotalSaldo,2)
					
				}else{
					echo "<tr><td style='background-color:#F7F8E0;' colspan=16 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DOCUMENTOS EN PESOS&nbsp;</td></tr>";
				
					estadocuenta(0,$SupplierID,1,0,$moneda,$fechaini,$fechafin,$db,$GranTotalSaldo);
				
					echo "<tr><td colspan=9 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
					//echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
					//echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
					//$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
					echo "<td nowrap class=number><font size=1>&nbsp;</td>";
					echo "<td nowrap class=number><font size=1>" . _('TOTAL EN PESOS') . ":&nbsp;</td>";
					$sumasaldo=abs($_SESSION['totalsaldo']);
					echo "<td nowrap class=number><font size=1> $" . number_format($sumasaldo,2) . "</td>";
					//number_format($GranTotalSaldo,2)
				}
			}
		}
		
		if($moneda == 'MXN'){
			$_SESSION['impresion']=1;
			if($_SESSION['impresion']== 1 ){
				if(isset($_POST['PrintEXCEL'])) {
					echo "<tr><td style='background-color:#f2fcbd;' colspan=13 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ABONOS NO APLICADOS EN PESOS&nbsp;</td></tr>";
					unset($_SESSION['totalsaldo']);
					$moneda = 'MXN';
					estadocuenta(-1,$SupplierID,1,1,$moneda,$fechaini,$fechafin,$db,$GranTotalSaldo);
				
					echo "<tr><td colspan=9 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
					//echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
					//echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
					//$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
					echo "<td nowrap class=number><font size=1>&nbsp;</td>";
					echo "<td nowrap class=number><font size=1>" . _('TOTAL EN PESOS') . ":&nbsp;</td>";
					$sumasaldo2=$_SESSION['totalsaldo'];
					echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo2,2)."</td>";
				}else{
					echo "<tr><td style='background-color:#f2fcbd;' colspan=16 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ABONOS NO APLICADOS EN PESOS&nbsp;</td></tr>";
					unset($_SESSION['totalsaldo']);
					$moneda = 'MXN';
					estadocuenta(-1,$SupplierID,1,1,$moneda,$fechaini,$fechafin,$db,$GranTotalSaldo);
				
					echo "<tr><td colspan=9 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
					//echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
					//echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
					//$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
					echo "<td nowrap class=number><font size=1>&nbsp;</td>";
					echo "<td nowrap class=number><font size=1>" . _('TOTAL EN PESOS') . ":&nbsp;</td>";
					$sumasaldo2=$_SESSION['totalsaldo'];
					echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo2,2) . "</td>";
					//"/".number_format($GranTotalSaldo,2).
				}
			}
		}
		
		$_SESSION['totalsaldo']=0;
		$moneda = 'USD';
		if($moneda == 'USD'){
		//DOLARES
			$_SESSION['impresion']=0;
			if($_SESSION['impresion']== 0){
				if(isset($_POST['PrintEXCEL'])) {
					echo "<tr><td style='background-color:#F7F8E0;' colspan=13 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DOCUMENTOS EN DOLARES&nbsp;</td></tr>";
				
					estadocuenta(0,$SupplierID,1,0,$moneda,$fechaini,$fechafin,$db,$GranTotalSaldo);
					
					echo "<tr><td colspan=9 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
					//echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
					//echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
					//$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
					echo "<td nowrap class=number><font size=1>&nbsp;</td>";
					echo "<td nowrap class=number><font size=1>" . _('TOTAL EN PESOS') . ":&nbsp;</td>";
					$sumasaldo=abs($_SESSION['totalsaldo']);
					echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo,2)."<br>
								US$ ".number_format($_SESSION['totalsaldoUSD'],2)."</td>";
								
				}else{
					
					echo "<tr><td style='background-color:#F7F8E0;' colspan=16 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DOCUMENTOS EN DOLARES&nbsp;</td></tr>";
				
					estadocuenta(0,$SupplierID,1,0,$moneda,$fechaini,$fechafin,$db,$GranTotalSaldo);
				
					echo "<tr><td colspan=9 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
					//echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
					//echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
					//$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
					echo "<td nowrap class=number><font size=1>&nbsp;</td>";
					echo "<td nowrap class=number><font size=1>" . _('TOTAL EN PESOS') . ":&nbsp;</td>";
					$sumasaldo=abs($_SESSION['totalsaldo']);
					echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo,2)."<br>
								US$ ".number_format($_SESSION['totalsaldoUSD'],2)."</td>";	
				}
			}
			$_SESSION['impresion']=1;
			if($_SESSION['impresion']== 1 ){
				if(isset($_POST['PrintEXCEL'])) {
					$moneda = 'USD';
					echo "<tr><td style='background-color:#f2fcbd;' colspan=13 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ABONOS NO APLICADOS EN DOLARES&nbsp;</td></tr>";
					unset($_SESSION['totalsaldo']);
					unset($_SESSION['totalsaldoUSD']);
					estadocuenta(-1,$SupplierID,1,1,$moneda,$fechaini,$fechafin,$db,$GranTotalSaldo);
				
					echo "<tr><td colspan=9 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
					//echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
					//echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
					//$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
					echo "<td nowrap class=number><font size=1>&nbsp;</td>";
					echo "<td nowrap class=number><font size=1>" . _('TOTAL EN PESOS') . ":&nbsp;</td>";
					$sumasaldo2=$_SESSION['totalsaldo'];
					echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo2,2)."<br>
								US$ ".number_format($_SESSION['totalsaldoUSD'],2)."</td>";	
				}else{
					$moneda = 'USD';
					echo "<tr><td style='background-color:#f2fcbd;' colspan=16 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ABONOS NO APLICADOS EN DOLARES&nbsp;</td></tr>";
					unset($_SESSION['totalsaldo']);
					unset($_SESSION['totalsaldoUSD']);
					estadocuenta(-1,$SupplierID,1,1,$moneda,$fechaini,$fechafin,$db,$GranTotalSaldo);
				
					echo "<tr><td colspan=9 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
					//echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
					//echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
						//$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
					echo "<td nowrap class=number><font size=1>&nbsp;</td>";
					echo "<td nowrap class=number><font size=1>" . _('TOTAL EN PESOS') . ":&nbsp;</td>";
					$sumasaldo2=$_SESSION['totalsaldo'];
					echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo2,2)."<br>
								US$ ".number_format($_SESSION['totalsaldoUSD'],2)."</td>";		
				}
			}
		}
		
	}
	if(isset($_POST['PrintEXCEL'])) {
	        echo "<td><input type=hidden name='cuenta'  value='".intval($cont)."'><font size=1>&nbsp;</td>";
	}else{
	echo "<td colspan=9 nowrap style='text-align:center;'><input type=hidden name='cuenta'  value='".intval($cont)."'><font size=1>&nbsp;</td>";
	echo '</table></form>';
	echo '<p><p><p>';
	unset($_SESSION['totalcargos']);
	unset($_SESSION['totalsaldo']);
	unset($_SESSION['sumapagos']);
	//unset($_SESSION['totalsaldo']);
	unset($_SESSION['cont']);
	unset($sumasaldo);
	unset($sumasaldo2);
	//unset($_SESSION['$tipocambio']);
	unset($total);
	}
	if (isset($_POST['PrintEXCEL'])) {
		exit;
	}

	
include('includes/footer.inc');
?>