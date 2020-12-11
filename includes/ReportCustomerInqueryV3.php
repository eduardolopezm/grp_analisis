<?php
/*
 ARCHIVO MODIFICADO POR: Desarrollador
  FECHA DE MODIFICACION: 19-DIC-2011
 CAMBIOS: 
	1. SE AGREGO LINK PARA QUE PERMITA LA CANCELCION DE NOTAS DE CANCELACION
 FIN DE CAMBIOS
 
FECHA DE MODIFICACION: 12-Dic-2011
 CAMBIOS:
   1. SE AGREGO FILTRO PARA QUE NO CONSIDERE FECHAS EN ESTADO DE CUENTA
FIN DE CAMBIOS

 FECHA DE MODIFICACION: 01-DIC-2011
 CAMBIOS: 
	1. SE AGREGO EXCEL DETALLE DE FACTURAS
 FIN DE CAMBIOS
*/
include('includes/SQL_CommonFunctions.inc');
$PageSecurity = 1;
include('includes/session.inc');

$title = _('Estado de Cuenta del Cliente');

$funcion=578;
include('includes/SecurityFunctions.inc');

// always figure out the SQL required from the inputs available
if(!isset($_GET['CustomerID']) AND !isset($_SESSION['CustomerID'])){
	prnMsg(_('To display the enquiry a customer must first be selected from the customer selection screen'),'info');
	echo "<br><div class='centre'><a href='". $rootpath . "/SelectCustomer.php?" . SID . "'>" . _('Select a Customer to Inquire On') . '</a><br></div>';
	include('includes/footer.inc');
	exit;
} else {
	if (isset($_GET['CustomerID'])){
		$_SESSION['CustomerID'] = $_GET['CustomerID'];
	}
	$CustomerID = $_SESSION['CustomerID'];
}
//echo 'aaaaa'.$_SESSION['CustomerID'];

if (!isset($_POST['TransAfterDate'])) {
	$_POST['TransAfterDate'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date('m'),Date('d'),Date('Y')));
	//$_POST['TransAfterDate'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,1,1,2000));
}

if (!isset($_POST['TransDateSince'])) {
	#$_POST['TransAfterDate'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date('m')-6,Date('d'),Date('Y')));
	$_POST['TransDateSince'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,1,1,2000));
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
    
    if (isset($_GET['sucursal'])){
	$_POST['sucursal'] = $_GET['sucursal'];
    }elseif (!isset($_POST['sucursal'])){
	$_POST['sucursal'] = 0;
    }
    
    if (isset($_GET['departamento'])){
	$_POST['departamento'] = $_GET['departamento'];
    }elseif (!isset($_POST['departamento'])){
	$_POST['departamento'] = 0;
    }
    
    if (isset($_GET['razonsocial'])){
	$_POST['razonsocial'] = $_GET['razonsocial'];
    }elseif (!isset($_POST['razonsocial'])){
	$_POST['razonsocial'] = "0";
    }
    
    
     $fechaini= rtrim($FromYear).'-'.rtrim($FromMes).'-'.rtrim($FromDia);
     
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
     
     if(isset($_POST['PrintExcelDetalle'])){
	
	
	header("Content-type: application/ms-excel");
	# replace excelfile.xls with whatever you want the filename to default to
	header("Content-Disposition: attachment; filename=estadoCuentaDetallado.xls");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
	
	echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
	echo '<link rel="shortcut icon" href="'. $rootpath . '/favicon.ico" />';
	echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';
	$razonsocial=$_POST['razonsocial'];
        $todaslasunidades=$_POST['todaslasunidades'];
	if(strlen($todaslasunidades)==0){
		$todaslasunidades=0;
	}
        $sucursal=$_POST['sucursal'];
        $departamento=$_POST['departamento'];
	//$solosaldo=$_POST['solosaldo'];
	$mabonoscargos=$_POST['mabonoscargos'];
	$FromDia=$_POST['FromDia'];
      	$FromMes=$_POST['FromMes'];
	$ToDia=$_POST['ToDia'];
	$ToMes=$_POST['ToMes'];
	
	
	$SQL = "SELECT
            debtortrans.type,
            debtortrans.transno,
            debtortrans.folio as nofactura,
            debtortrans.origtrandate as fechafactura,
            debtortrans.ovgst as ivafactura,
            debtortrans.currcode as moneda,
            tags.tagname as centrodenegocio,
            (1/rate) as tipocambio,
            sum((ovamount+ovgst)) as totalfactura,
            sum((ovamount+ovgst) - alloc) as saldo,
            IFNULL(datealloc,'') as fechaultimopago,
            case when (IFNULL(datealloc,'1900-01-01') > debtortrans.trandate) then 0 else 1 end as pagoatiempo,
            debtortrans.trandate as fechavence,
            debtortrans.debtorno,
            debtorsmaster.name,
	    debtortrans.emails
	FROM debtortrans
            LEFT JOIN (SELECT transid_allocto, max(datealloc) as datealloc from custallocns group by transid_allocto) as cu ON debtortrans.id = cu.transid_allocto
            LEFT JOIN custbranch ON debtortrans.branchcode = custbranch.branchcode
            LEFT JOIN tags ON debtortrans.tagref = tags.tagref
            LEFT JOIN departments ON tags.u_department=departments.u_department
            LEFT JOIN legalbusinessunit ON tags.legalid = legalbusinessunit.legalid
            JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
            LEFT JOIN areas ON tags.areacode = areas.areacode
            LEFT JOIN regions ON areas.regioncode = regions.regioncode
            LEFT JOIN debtorsmaster ON debtortrans.debtorno = debtorsmaster.debtorno
            LEFT JOIN debtortype ON debtorsmaster.typeid = debtortype.typeid
	WHERE debtortrans.type in (10,110,11,14,119,410,109,66) and debtortrans.invtext not  like '%CANCELADA%'
            and (tags.legalid = " . $razonsocial . " or " . $razonsocial . "=0)
            and (areas.areacode = " . $sucursal. " or " . $sucursal . "=0)
            and (departments.u_department = " . $departamento. " or " . $departamento . "=0)
            and (debtortrans.tagref = " . $todaslasunidades . " or " .$todaslasunidades . "=0)";
	if (isset($fechaini) AND isset($fechafin) and !isset($_POST['historial'])) {
		$SQL .= " AND debtortrans.origtrandate between '".$fechaini."' and '".$fechafin."'";				
	}
            //and debtortrans.origtrandate >= '" . $fechaini . "' and debtortrans.origtrandate <= '" . $fechafin . "'
        $SQL .= " and (debtorsmaster.debtorno = '" . $CustomerID ."')";
    if (isset($_POST['solosaldo'])){
		$SQL .= " AND (debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount-alloc)>0";
    }
    $SQL=$SQL."	GROUP BY debtortrans.type, debtortrans.folio, debtortrans.origtrandate, debtorsmaster.name, debtortrans.currcode,tags.tagname
                        
            ORDER BY debtortrans.folio";
    //echo "<pre>" . $SQL;
    $result = DB_query($SQL,$db);
    if (DB_num_rows($result) > 0)
    {
            $cont=0;
	    $sumatotal=0;
	    $sumapagos=0;
	    $sumasaldo=0;
	    $totalcargos = 0;
	    $sumasaldo2=0;
	    $total=0;
            $FontSize = 12;
            $FontSizes = 10;
            $PageNumber = 0;
            $line_height = 12;
            $myrow2 = DB_fetch_array($result);
           // include('includes/PDFReportCustomerInqueryV3.inc');
	    echo '<table border=1>';
		echo '<tr>';
		    echo '<th colspan=11 >';
			echo _('Estado de Cuenta Detallado');
		    echo '</th>';
		echo '</tr>';
		echo '<tr>';
		    echo '<th colspan=10>';
			echo "<font face='arial' size=1 ><b>". _('Cliente:').$CustomerID.' - '.$myrow2['name'].'</b></font>';
		    echo '</th>';
		        echo '<th >';
			echo "<font face='arial' size=1 >" . _('Fecha Impresion:').Date('Y-m-d').'</font>';
		    echo '</th>';
		echo '</tr>';
		echo "<tr>
			<th ><b><font face='arial' size=1 >" . _('Folio') . "</th>
			<th ><b><font size=1 face='arial' >" . _('Fecha') . "</th>
			<th ><b><font size=1 face='arial' >" . _('Concepto Factura') . "</th>
			<th ><b><font size=1 face='arial'>" . _('Subtotal') . "</th>
			<th ><b><font size=1 face='arial' >" . _('IVA') . "</th>
			<th ><b><font size=1 face='arial' >" . _('Total') . "</th>
			<th ><b><font size=1 face='arial' >" . _('Moneda') . "</th>
			<th ><b><font size=1 face='arial' >" . _('Status Factura') . "</th>
			<th ><b><font size=1 face='arial' >" . _('Fecha Pago') . "</th>
			<th ><b><font size=1 face='arial' >" . _('Saldo') . "</th>
			<th ><b><font size=1 face='arial'>" . _('Fecha<br>Vencimiento') . "</th>";
		echo '</tr>';
	$result = DB_query($SQL,$db);
	while ($myrow = DB_fetch_array($result)){
            if ($myrow['saldo'] < 0.01){
                    if ($myrow['pagoatiempo'] == 0){
                            $statusfactura = "PAGADA CON RETRASO";
                    }else{
                            $statusfactura = "PAGADA A TIEMPO";
                            
                    }
                    
            }else{
                    $statusfactura = "PENDIENTE DE PAGO";
            }
	    //extrae los movimientos del documento de stockmoves
            $SQLPro="Select sum(((stockmoves.price*(stockmoves.qty*-1))-(stockmoves.totaldescuento))) as subtotal,
				sum(stockmovestaxes.taxrate*(((stockmoves.price*(stockmoves.qty*-1))-(stockmoves.totaldescuento)))) as iva,
                                stockmoves.narrative
                    from stockmoves LEFT JOIN stockmovestaxes ON stockmoves.stkmoveno = stockmovestaxes.stkmoveno and taxrate>0
                    where stockmoves.type=".$myrow['type']." and stockmoves.transno=".$myrow['transno']."
                    group by stockmoves.stockid,stockmoves.narrative";
            $resultprod = DB_query($SQLPro,$db);
	    $cuentaregistros=DB_num_rows($resultprod);
	    $resultprod = DB_query($SQLPro,$db);
	    $cuentaregistros=1;
	    echo '<tr>';
	    echo '<td rowspan='.$cuentaregistros.' >'.str_replace('|','',$myrow['nofactura']).'</td>';
	    echo '<td rowspan='.$cuentaregistros.' >'.$myrow['fechafactura'].'</td>';
	    $cuenta=0;
	    echo '<td colspan=2>';
	    if($cuentaregistros>0){
		echo '<table border=1>';
		
		while ($myrowprod = DB_fetch_array($resultprod)){
		  /*  if($cuenta==($cuentaregistros-1)){
			    
			    echo '<td rowspan='.$cuentaregistros.'>'.'$'.number_format($myrow['ivafactura'],2).'</td>';
			    echo '<td rowspan='.$cuentaregistros.'>'.'$'.number_format($myrow['totalfactura'],2).'</td>';
			    echo '<td rowspan='.$cuentaregistros.'>'.$myrow['moneda'].'</td>';
			    echo '<td rowspan='.$cuentaregistros.'>'.$statusfactura.'</td>';
			    echo '<td rowspan='.$cuentaregistros.'>'.$myrow['fechaultimopago'].'</td>';
			    echo '<td rowspan='.$cuentaregistros.'>'.'$'.number_format($myrow['saldo'],2).'</td>';
			    echo '<td rowspan='.$cuentaregistros.'>'.$myrow['fechavence'].'</td>';
			    echo '</tr>';
			    
			    echo '<tr>';
		    }*/
		    // echo '<td colspan=2></td>';
		      echo '<tr>';
		     if(strpos($myrowprod['narrative'],'\n')>0){
			    $myrowprod['narrative']=str_replace('&amp;quot;','"',$myrowprod['narrative']);
			    $myrowprod['narrative']=str_replace('&amp;amp;quot;','"',$myrowprod['narrative']);
			    $descrnarrative=str_replace('\r\n',' ',$myrowprod['narrative']);
			    $descrnarrative=str_replace('#%#','    ',$descrnarrative);
		    }else{
			    $myrowprod['narrative']=str_replace('&amp;quot;','"',$myrowprod['narrative']);
			    $myrowprod['narrative']=str_replace('&amp;amp;quot;','"',$myrowprod['narrative']);
			    $descrnarrative=$myrowprod['narrative'];
			    $descrnarrative=str_replace('#%#','   ',$descrnarrative);
		    }
		    echo '<td >'.$descrnarrative.'</td>';
		    echo '<td >'.'$'.number_format($myrowprod['subtotal'],2).'</td>';
		    /*if($cuenta<$cuentaregistros and $cuenta>=0){
			    echo '</tr>';
		    }*/
			echo '</tr>';
		    $cuenta=$cuenta+1;
		}
		echo '</table>';
		echo '</td>';
		if($cuentaregistros==1){
			echo '<td >'.'$'.number_format($myrow['ivafactura'],2).'</td>';
			echo '<td >'.'$'.number_format($myrow['totalfactura'],2).'</td>';
			echo '<td >'.$myrow['moneda'].'</td>';
			echo '<td >'.$statusfactura.'</td>';
			echo '<td >'.$myrow['fechaultimopago'].'</td>';
			echo '<td >'.'$'.number_format($myrow['saldo'],2).'</td>';
			echo '<td >'.$myrow['fechavence'].'</td>';
			echo '</tr>';
		}
		
	    }else{
		echo '<td ></td>';
		echo '<td ></td>';
		echo '<td >'.'$'.number_format($myrow['ivafactura'],2).'</td>';
		echo '<td >'.'$'.number_format($myrow['totalfactura'],2).'</td>';
		echo '<td >'.$myrow['moneda'].'</td>';
		echo '<td >'.$statusfactura.'</td>';
		echo '<td >'.$myrow['fechaultimopago'].'</td>';
		echo '<td >'.'$'.number_format($myrow['saldo'],2).'</td>';
		echo '<td >'.$myrow['fechavence'].'</td>';
		echo '</tr>';
	    }
        }
        echo '</table>';
	exit;
    }else{
        //echo $SQL;
        exit;
    }
	
      }
if (!isset($_POST['PrintEXCEL'])) {
	include('includes/header.inc');
	$debug = 1;
	echo '<form action=' . $_SERVER['PHP_SELF'] .'?' .SID . ' method=post>';
	
	
	///FIN FECHA
	
	
	
	if (isset($_POST['ModifyPay']) and isset($_POST['cuenta']))
	{
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
			    or $ovgst!=$ovgstant)
			{
				$idtrans=$_POST['idtrans'.$z];
				$typetrans=$_POST['typetrans'.$z];
				//$ovamount=($ovamount*$typetrans);
				//$ovgst=($ovgst*$typetrans);
				$ovamount=$ovamount;
				//$ovgst=$ovgst;
				
				$Result = DB_Txn_Begin($db);
				
				$separa = explode('/',$trandateact);
				$mesvence = $separa[1];
				$aniovence = $separa[2];
				$diavence=$separa[0];
				$fechavence=$aniovence.'/'.$mesvence.'/'.$diavence;
				$pagoactual=str_replace(",","",$pagoactual);
				$SQL="UPDATE debtortrans
				      SET alloc=".$pagoactual.",
					  tagref=".$tagactual.",
					  trandate='".$fechavence."',
					  ovgst=".$ovgst.",
					  ovamount=".$ovamount."
				      WHERE id=".$idtrans;
				//echo $SQL;
				$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La actualizacion no fue posible');
				$DbgMsg = _('El SQL utilizado es:');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				$Result = DB_Txn_Commit($db);
				unset($_POST['pagos'.$z]);
				unset($_POST['pagosant'.$z]);
				unset($_POST['idtrans'.$z]);
				unset($_POST['uniant'.$z]);
				unset($_POST['UnidNeg'.$z]);
				unset($_POST['trandateant'.$z]);
				unset($_POST['trandate'.$z]);
				unset($_POST['ovgst'.$z]);
				
				unset($_POST['ovamount'.$z]);
				unset($_POST['ovamountant'.$z]);
				unset($_POST['ovgstant'.$z]);
				unset($_POST['ModifyPay']);
				unset($_POST['cuenta']);
				unset($_POST['cuenta']);
				break ;
			}
		}	
	}
	if (isset($_POST['enviar'])) {
			$SQL = "UPDATE  debtorsmaster SET coments='". $_POST['coments']."'
				WHERE debtorno ='". $_SESSION['CustomerID'] ."'";
					$result = DB_query($SQL,$db,$ErrMsg);			
		}
	$SQL = 'SELECT debtorsmaster.name,
				debtorsmaster.address1,
				debtorsmaster.coments,
				currencies.currency,
				paymentterms.terms,
				debtorsmaster.creditlimit,
				holdreasons.dissallowinvoices,
				holdreasons.reasondescription,
				SUM(round(debtortrans.ovamount,2) + round(debtortrans.ovgst,2) + round(debtortrans.ovfreight,2) + round(debtortrans.ovdiscount,2) - round(debtortrans.alloc,2)) AS balance,
				SUM(
					CASE WHEN (paymentterms.daysbeforedue > 0) THEN
						CASE WHEN (TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate)) >= paymentterms.daysbeforedue THEN
							round(debtortrans.ovamount,2) + round(debtortrans.ovgst,2) + round(debtortrans.ovfreight,2) + round(debtortrans.ovdiscount,2)  - round(debtortrans.alloc,2)
						ELSE 0 END
					ELSE
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, '. INTERVAL('1', 'MONTH') . '), ' . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))',	'DAY') . ')) >= 0
						THEN round(debtortrans.ovamount,2) + round(debtortrans.ovgst,2) + round(debtortrans.ovfreight,2) + round(debtortrans.ovdiscount,2) - round(debtortrans.alloc,2) ELSE 0 END
					END) AS due,
				
				SUM(
					CASE WHEN (paymentterms.daysbeforedue > 0) THEN
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + ' . $_SESSION['PastDueDays1'] . ')
							THEN round(debtortrans.ovamount,2) + round(debtortrans.ovgst,2) + round(debtortrans.ovfreight,2) + round(debtortrans.ovdiscount,2) - round(debtortrans.alloc,2)
						ELSE 0 END
					ELSE
						CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, ' . INTERVAL('1', 'MONTH') . '), ' . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))','DAY') . ')) >= ' . $_SESSION['PastDueDays1'] . ')
						THEN round(debtortrans.ovamount,2) + round(debtortrans.ovgst,2) + round(debtortrans.ovfreight,2) + round(debtortrans.ovdiscount,2) - round(debtortrans.alloc,2) ELSE 0 END
					END) AS overdue1,
			
				SUM(
					CASE WHEN (paymentterms.daysbeforedue > 0) THEN
						CASE WHEN TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) > paymentterms.daysbeforedue AND TO_DAYS(Now()) - TO_DAYS(debtortrans.trandate) >= (paymentterms.daysbeforedue + ' . $_SESSION['PastDueDays2'] . ')
							THEN round(debtortrans.ovamount,2) + round(debtortrans.ovgst,2) + round(debtortrans.ovfreight,2) + round(debtortrans.ovdiscount,2) - round(debtortrans.alloc,2)
						ELSE 0 END
					ELSE
						CASE WHEN (TO_DAYS(Now()) - TO_DAYS(DATE_ADD(DATE_ADD(debtortrans.trandate, ' . INTERVAL('1','MONTH') . '), ' . INTERVAL('(paymentterms.dayinfollowingmonth - DAYOFMONTH(debtortrans.trandate))','DAY') . ')) >= ' .$_SESSION['PastDueDays2'] . ")
							THEN round(debtortrans.ovamount,2) + round(debtortrans.ovgst,2) + round(debtortrans.ovfreight,2) + round(debtortrans.ovdiscount,2) - round(debtortrans.alloc,2)
					ELSE 0 END
					END) AS overdue2,
				debtortrans.currcode
			
			FROM debtorsmaster,paymentterms,holdreasons,currencies,debtortrans
			WHERE  debtorsmaster.paymentterms = paymentterms.termsindicator
				AND debtorsmaster.currcode = currencies.currabrev
				AND debtorsmaster.holdreason = holdreasons.reasoncode
				AND debtorsmaster.debtorno = '" . $CustomerID . "'
				AND debtorsmaster.debtorno = debtortrans.debtorno";
			
				if (!isset($_POST['todaslasunidades'])){
					$SQL .= " AND debtortrans.tagref  in (select tagref from sec_unegsxuser where userid='".$_SESSION['UserID']. "')";
				};	     
				if (isset($_POST['solosaldo'])){
					$SQL .= " AND (debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount-alloc)>0";
				};
			$SQL .= " GROUP BY debtorsmaster.name,
					debtorsmaster.address1,
					debtorsmaster.coments,
					currencies.currency,
					paymentterms.terms,
					paymentterms.daysbeforedue,
					paymentterms.dayinfollowingmonth,
					debtorsmaster.creditlimit,
					holdreasons.dissallowinvoices,
					holdreasons.reasondescription,
					debtortrans.currcode";
				
	$ErrMsg = _('The customer details could not be retrieved by the SQL because');
	$CustomerResult = DB_query($SQL,$db,$ErrMsg);
	
	if (DB_num_rows($CustomerResult)==0){
		/*Because there is no balance -
		so just retrieve the header information about the customer - the choice is do one query to get the balance and transactions for those customers
		who have a balance and two queries for those who don't have a balance OR always do two queries - I opted for the former */
		$NIL_BALANCE = True;
		$SQL = "SELECT debtorsmaster.name,debtorsmaster.address1,debtorsmaster.coments,custbranch.taxid AS rfc, currencies.currency, paymentterms.terms,
				debtorsmaster.creditlimit, holdreasons.dissallowinvoices, holdreasons.reasondescription,custbranch.phoneno AS tel 
			FROM debtorsmaster,
			     paymentterms,
			     holdreasons,
			     currencies,
			     custbranch
			WHERE
			     debtorsmaster.paymentterms = paymentterms.termsindicator
			     AND debtorsmaster.currcode = currencies.currabrev
			     AND debtorsmaster.debtorno = custbranch.debtorno
			     AND debtorsmaster.holdreason = holdreasons.reasoncode
			     AND debtorsmaster.debtorno = '" . $CustomerID . "'";
		$ErrMsg =_('The customer details could not be retrieved by the SQL because');
		$CustomerResult = DB_query($SQL,$db,$ErrMsg);
	} else {
		$SQL = "SELECT debtorsmaster.name,debtorsmaster.address1,debtorsmaster.coments,custbranch.taxid AS rfc, currencies.currency, paymentterms.terms,
		debtorsmaster.creditlimit, holdreasons.dissallowinvoices, holdreasons.reasondescription,custbranch.phoneno AS tel 
		FROM debtorsmaster,
		     paymentterms,
		     holdreasons,
		     currencies,
		     custbranch
		WHERE
		     debtorsmaster.paymentterms = paymentterms.termsindicator
		     AND debtorsmaster.currcode = currencies.currabrev
		     AND debtorsmaster.debtorno = custbranch.debtorno
		     AND debtorsmaster.holdreason = holdreasons.reasoncode
		     AND debtorsmaster.debtorno = '" . $CustomerID . "'";
		     
		$ErrMsg =_('The customer details could not be retrieved by the SQL because');
		$CustomerResult2 = DB_query($SQL,$db,$ErrMsg);
		$CustomerRecord2= DB_fetch_array($CustomerResult2);
		$CustomerResult3 = DB_query($SQL,$db,$ErrMsg);
		$CustomerRecord3= DB_fetch_array($CustomerResult3);
		
		$NIL_BALANCE = False;
	}
	//$CustomerRecord = DB_fetch_array($CustomerResult);
	//round($CustomerRecord['overdue2'],2);
	if ($NIL_BALANCE==True){
		$CustomerRecord['balance']=0;
		$CustomerRecord['due']=0;
		$CustomerRecord['overdue1']=0;
		$CustomerRecord['overdue2']=0;
		$telefono=$CustomerRecord['tel'];
		$rfc=$CustomerRecord['rfc'];
	}else{
		$telefono=$CustomerRecord2['tel'];
		$rfc=$CustomerRecord2['rfc'];
		
	}
	
	
	echo '<table width="80%" border="0" align="center" ><tr><td>';
	
	echo '<p align="center">';
	echo '<a href="SelectCustomer.php?modulosel=2">';
	echo '<font size=2 face="arial">';
	echo _('Regresar a Opciones del Cliente');
	echo '</font>';
	echo '</a>';
	echo "<form action='" . $_SERVER['PHP_SELF'] . "' method=post>";
		
		/*Espacio a la izquierda de margen*/
		//echo "nombre".$CustomerRecord['address1'];
	$i = 0;	
	while ($CustomerRecord = DB_fetch_array($CustomerResult)){
		$i = $i + 1;
		if ($i == 1){
			echo "<table border='1' align='center' cellpadding='1' cellspacing='0' width='100%' style='border-style: groove; border-color: #F8F8F8;'>";
				echo "<tr>";
					echo "<th colspan='6'><img src='" . $rootpath . "/css/" . $theme . "/images/customer.png' title='" . _ ('Customer') . "' alt=''>&nbsp;&nbsp;<b>" . $CustomerID,' - ' . $CustomerRecord['name'] . "</b></th>";
				echo "</tr>";
				echo "<tr>";
					echo "<td><b>" . _(' RFC') . ":</b></td>";
					echo "<td>" . $rfc . "</td>";
					echo "<td><b>" . _(' Direccion') . ":</b></td>";
					echo "<td>" . $CustomerRecord['address1'] . "</td>";
					echo "<td><b>" . _(' Telefono') . ":</b></td>";
					echo "<td>" . $telefono . "</td>";
				echo "</tr>";
				echo "<tr>";
					echo "<td><b>" . _('Condiciones de Pago') . " : </b></td>";
					echo "<td>" . $CustomerRecord['terms'] . "</td>";
					echo "<td><b>" . _('Límite de Crédito') . " : </b></td>";
					echo "<td>" . number_format($CustomerRecord['creditlimit'],0) . "</td>";
					echo "<td><b>" . _('Historial Crediticio') . " : </b></td>";
					echo "<td>" . $CustomerRecord['reasondescription'] . "</td>";
				echo "</tr>";
				echo "<tr>";
					echo "<td><b>" . _(' Comentarios') . ":</b></td>";
					echo "<td colspan='4'><textarea name='coments' cols='60' rows='3 '>" . $CustomerRecord['coments'] . "</textarea></td>";
					echo "<td colspan='1'><input type='Submit' name='enviar' value='" . _('Guardar') . "'>";
				echo "</tr>";
				
				/*
				echo  "<tr>";
					$sqldoc="SELECT count(*)
					FROM prddocumentos 
					WHERE propietarioid='" . $CustomerID . "' AND tipopropietarioid=1";
					$doc= DB_query($sqldoc,$db);
					$myrowdoc=DB_fetch_row($doc,$db);
				
					if($myrowdoc[0]>0){
						echo "<td style='text-align:center; background-color:white;border:thin;'>
							<a href='#' onclick='javascript:window.open(\"prdABCReporteDocumentos.php?propietarioid=" . $CustomerID . "&tipopropietarioid=1\", \"\",
							\"toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550\"); return false'>
							<img src='images/conarchivos.gif'  title='Con archivos'></a></td>";   
					}else{
						echo "<td style='text-align:center; background-color:white; border:thin;'>
							<a href='#' onclick='javascript:window.open(\"prdABCReporteDocumentos.php?propietarioid=" . $CustomerID . "&tipopropietarioid=1\", \"\",
							\"toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550\"); return false'>
							"._('subir archivo')."</a></td>"; 
					}
				echo "</tr>";
				*/
				echo "<tr>";
					echo "<td colspan='6'>";
						if ($CustomerRecord['dissallowinvoices']!=0){
							echo '<br><font color=RED size=4><b>' . _('CUENTA RESTRINGIDA PARA CREDITO !') . '</font></b><br>';
						}
					echo "</td>";
				echo "</tr>";
				echo "<tr>";
					echo "<td colspan='6'>";
						echo "<table class='table2' border='1' cellpadding='4' cellspacing='0' align='center' width=100%>";
							echo "<tr>";
								echo "<th><b>" . _('Moneda') . "</b></th>";
								echo "<th><b>" . _('Saldo Total') . "</b></th>";
								echo "<th><b>" . _('Al Corriente') . "</b></th>";
								echo "<th><b>" . _('Vencido') . "</b></th>";
								echo "<th><b>" . $_SESSION['PastDueDays1'] . "-" . $_SESSION['PastDueDays2'] . ' ' . _('Días') . "</b></th>";
								echo "<th><b>" . _('Más de ') . " " . $_SESSION['PastDueDays2'] . " " . _('Días') . "</b></th></tr>";
					
		}
		echo "<tr>";
			echo "<td>" . $CustomerRecord['currcode'] . "</td>";
			echo "<td class=number>$" . number_format($CustomerRecord['balance'],2) . "</td>";
			echo "<td class=number>$" . number_format(($CustomerRecord['balance'] - $CustomerRecord['due']),2) . "</td>";
			echo "<td class=number>$" . number_format(($CustomerRecord['due']-$CustomerRecord['overdue1']),2) . "</td>";
			echo "<td class=number>$" . number_format(($CustomerRecord['overdue1']-$CustomerRecord['overdue2']) ,2) . "</td>";
			echo "<td class=number>$" . number_format($CustomerRecord['overdue2'],2) . "</td>";
		echo "</tr>";
	}
	
		echo "</table>";
			echo "</td>";
		echo "</tr>";
			echo "</table>";
	
	echo "</table>";
	
	
	echo "<table border='0' cellpadding='3' cellspacing='1' width='80%' style='text-align:center'>";
		echo "<tr height='20'>";
			echo "<td colspan='6'></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td colspan='2' width='50%' style='text-align:center'>" . _('Desde:');
			
				echo "<select Name='FromDia'>";
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
				echo "</select>";
				echo "&nbsp;";
				echo "<select Name='FromMes'>";
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
				  
				echo "</select>";
				echo "&nbsp;<input name='FromYear' type='text' size='4' value='" . $FromYear . "'>";
			echo "</td>";
			echo "<td colspan='2' style='text-align:center'>" . _('Hasta:');
				
					echo "<select Name='ToDia'>";
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
					echo "</select>";
					echo "&nbsp;";
					echo"<select Name='ToMes'>";
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
					echo "</select>";
					echo "&nbsp;<input name='ToYear' type='text' size='4' value=" . $ToYear . ">";
			echo "</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td style='text-align:right;'>";
				echo "<b></b>"._('X Razon Social: ') . "&nbsp;&nbsp;";
			echo "</td>";
			echo "<td>";
				echo "<select name='razonsocial'>";
					echo "<option selected value='0'>Todas Las Razones S.</option>";
					$sql = "SELECT distinct l.legalid, l.legalname
						FROM tags t, legalbusinessunit l, sec_unegsxuser uxu
						WHERE t.tagref=uxu.tagref
						AND uxu.userid='".$_SESSION['UserID']."'
						AND t.legalid = l.legalid
						ORDER BY tagdescription";
					$resultTags = DB_query($sql,$db,'','');
					while ($xmyrow=DB_fetch_array($resultTags)){
						if ($xmyrow['legalid'] == $_POST['razonsocial']) {
							echo "<option selected Value='" . $xmyrow['legalid'] . "'>" . $xmyrow['legalname'] .'</option>';
						}
						else {
							echo "<option Value='" . $xmyrow['legalid'] . "'>" . $xmyrow['legalname'] .'</option>';
						}
					}
				echo "</select></td>";
				
			echo "<td colspan='2' style='text-align:center'>";
				echo "<input type='checkbox' name='solosaldo' value='1'";
				if (isset($_POST['solosaldo'])){
					echo " checked";
				}
				echo ">&nbsp;&nbsp;"._('Mostrar solo documentos con saldo') . "";
			echo "</td>";
		echo "</tr>";
				
		echo "<tr>";
			echo "<td style='text-align:right;'>";
				echo "<b></b>"._('Sucursal: ') . "&nbsp;&nbsp;";
			echo "</td>";
			echo "<td>";
				echo "<select name='sucursal'>";
					echo "<option selected value='0'>Todas Las Sucursales</option>";
					$ssql = "SELECT areas.areacode, CONCAT(areas.areacode,' - ',areas.areadescription) as name
						FROM areas 
						JOIN tags ON tags.areacode = areas.areacode JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
						WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
						GROUP BY areas.areacode, areas.areadescription";
					$sresult = DB_query($ssql,$db,'','');
		    
					while ($smyrow=DB_fetch_array($sresult)){
						if ($smyrow['areacode'] == $_POST['sucursal']) {
							echo "<option selected Value='" . $smyrow['areacode'] . "'>" . $smyrow['name'] .'</option>';
						}
						else {
							echo "<option Value='" . $smyrow['areacode'] . "'>" . $smyrow['name'] .'</option>';
						}
					}
				echo "</select></td>";
			echo "<td  colspan='2' style='text-align:center'>";
				echo "<input type='checkbox' name=mabonoscargos value=1";
				if (isset($_POST['mabonoscargos'])){
				  echo " checked";
				}
				echo ">&nbsp;&nbsp;"._('Mostrar abonos y despues cargos') . "&nbsp;&nbsp;";
			echo "</td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td style='text-align:right;'>";
				echo "<b></b>"._('X Departamento: ') . "&nbsp;&nbsp;";
			echo "</td>";
			echo "<td>";
				$sql = "SELECT departments.u_department,departments.department as name
					FROM departments 
					    JOIN tags ON tags.u_department = departments.u_department
					    JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref
					WHERE sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
					GROUP BY department
					ORDER BY department";
				$dresult=DB_query($sql,$db);
		
				echo "<select name='departamento'>";
				echo '<option selected Value="0">Todos Los Departamentos</option>';
				while ($dmyrow=DB_fetch_array($dresult)){
					if ($dmyrow['u_department'] == $_POST['departamento']) {
						echo "<option selected Value='" . $dmyrow['u_department'] . "'>" . $dmyrow['name'] .'</option>';
					}
					else {
						echo "<option Value='" . $dmyrow['u_department'] . "'>" . $dmyrow['name'] .'</option>';
					}
				}
				echo '</select></td>';
				echo "<td colspan='2'   style='text-align:center'>";
				echo "<input type='checkbox' name=historial value=1";
				if (isset($_POST['historial'])){
				  echo " checked";
				}
				echo ">&nbsp;&nbsp;"._('Mostrar historial completo ') . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			echo "</td>";
		echo "</tr>";
			
		echo "<tr>";
			echo "<td style='text-align:right;'>";
				echo "<b></b>"._('X Unidad de Negocio: ') . "&nbsp;&nbsp;";
			echo "</td>";
			echo "<td>";
				$sql = "SELECT t.tagref, t.tagdescription
					FROM tags t, sec_unegsxuser uxu
					WHERE t.tagref=uxu.tagref
					AND uxu.userid='".$_SESSION['UserID']."'
					ORDER BY tagdescription";
				$resultTags = DB_query($sql,$db,'','');
				echo "<select name='todaslasunidades'>";
				echo'<option selected Value="">Todas Las Unidades</option>';
				while ($xmyrow=DB_fetch_array($resultTags)){
					if ($xmyrow['tagref'] == $_POST['todaslasunidades']) {
						echo "<option selected Value='" . $xmyrow['tagref'] . "'>" . $xmyrow['tagdescription'] .'</option>';
					}
					else {
						echo "<option Value='" . $xmyrow['tagref'] . "'>" . $xmyrow['tagdescription'] .'</option>';
					}
				}
				echo '</select></td>';
			
		echo "</tr>";
		echo "<tr height='10'>";
			echo "<td colspan='6'></td>";
		echo "</tr>";
		echo "<tr>";
			echo "<td colspan='4'>";
				echo "<table border='0' cellspacing='0' cellpadding='0' width='80%'>";
					echo "<tr>";
						echo "<td style='text-align:center'><input type='submit' name='Refresh Inquiry' value='" . _('Mostrar en pantalla') . "'></td>";
						echo "<td style='text-align:center'><input type='submit' name='PrintEXCEL' value='" . _('Exportar a Excel') . "'></td>";
						echo "<td style='text-align:center'><input type='hidden' name='PrintPDF' value='" . _('Imprime PDF') . "'></td>";
						echo "<td style='text-align:center'><input type='submit' name='PrintExcelDetalle' value='" . _('Exportar Detalle a Excel ') . "'></td>";
					echo "</tr>";
				echo "</table>";
			echo "</td>";
		echo "</tr>";
	echo "</table>";

	
}
echo '<p align="center">';	
	echo "<table border='0' cellpadding='2' cellspacing='2'>";
                        echo "<tr>";
                        echo "<td width='2'><img src='images/red_flag_16.png' width='16' height='16'></td>";
                        echo "<td style='font-size:8pt; font-weight:normal;'>Saldo Pendiente Vencido</td>";
                        echo "</tr>";
                echo "</table>";
		
//echo '</td></tr></table>';

    
    if (isset($_POST['PrintEXCEL'])) {
	
	header("Content-type: application/ms-excel");
	# replace excelfile.xls with whatever you want the filename to default to
	header("Content-Disposition: attachment; filename=ReportCustomer.xls");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
	
	echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
	echo '<link rel="shortcut icon" href="'. $rootpath . '/favicon.ico" />';
	echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';
    }



$DateAfterCriteria = FormatDateForSQL($_POST['TransAfterDate']);
//echo $DateAfterCriteria;
$DateSinceCriteria = FormatDateForSQL($_POST['TransDateSince']);
$_SESSION['natcontable'] = array();
$SQL = "SELECT * FROM systypescat";
$ErrMsg = _('No transactions were returned by the SQL because');
$TransResult = DB_query($SQL,$db,$ErrMsg);
while ($myrow=DB_fetch_array($TransResult)) {
	$_SESSION['natcontable'][$myrow['typeid']] = $myrow['naturalezacontable'];
	
}

///********************************************** 1era. Función *****************************************///
if(isset($_POST['PrintEXCEL'])){
echo '<br><table cellspacing=0 border=1 bordercolor=DarkBlue cellpadding=2 colspan="7">';
		echo "<tr>
			<th style='background-color:#f2fcbd;'><b><font face='arial' size=1 color='#5b5202'>" . _(' ') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('#') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Unidad<br>Negocio') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Fecha') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Folios') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Concepto') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Cargo') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('IVA') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Total') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Abono') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Saldo<br>Pendiente') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('T.C.') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Importe Pesos') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Fecha<br>Vencimiento') . "</th>";
			
		

}else{
echo '<br><table cellspacing=0 border=1 bordercolor=DarkBlue cellpadding=2 colspan="7">';
    echo "<tr>
			<th style='background-color:#f2fcbd;'><b><font face='arial' size=1 color='#5b5202'>" . _(' ') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('#') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Unidad<br>Negocio') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'></th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Folios') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'></th>	
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Concepto') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Cargo') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('IVA') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Total') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Abono') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Saldo<br>Pendiente') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('T.C.') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Importe Pesos') . "</th>
			<th style='background-color:#f2fcbd;'><b><font size=1 face='arial' color='#5b5202'>" . _('Fecha<br>Vencimiento') . "</th>";
			if (Havepermission($_SESSION['UserID'],349, $db)==1){
			echo "<th  colspan=2 style='background-color:#f2fcbd;'><b><font size=1 color='#5b5202'>" . _('Corregir') . "</th>";
			}
			echo "<th colspan=7  style='background-color:#f2fcbd;'><b><font size=1 color='#5b5202'>" . _('Más Información') . "</th>
			</tr>";
}
session_start();

function estadocuenta($id,$CustomerID,$nivel,$tiponaturaleza,$TipoCambio,$fechaini,$fechafin,$db){
	$DateAfterCriteria = FormatDateForSQL($_POST['TransAfterDate']);
	$DateSinceCriteria = FormatDateForSQL($_POST['TransDateSince']);

	if ($id == 0)
	{
		//echo "tipo C".$TipoCambio;
		$SQL = "SELECT systypescat.typename,
			debtortrans.id,
			debtortrans.type,
			debtortrans.debtorno,
			debtortrans.order_,
			debtortrans.transno,
			debtortrans.branchcode,
			debtortrans.origtrandate,
			debtortrans.trandate,
			debtortrans.reference,
			debtortrans.invtext,
			debtortrans.order_,
			debtortrans.rate,
			debtortrans.folio,
			round((debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount),2) AS totalamount,";
						
			if ($TipoCambio == "MXN"){
				$SQL .=  " round((debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount)- alloc,2) AS totalamount2,";	
			}elseif ($TipoCambio == "USD"){
				$SQL .= " round(((debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount) - alloc),2) AS totalamount2,";
			}
			$SQL .= "
			debtortrans.ovamount as monto,
			debtortrans.ovgst as iva,
			debtortrans.alloc AS allocated,
			tags.tagdescription,
			tags.tagref,
			debtortrans.folio,
			CASE WHEN (
				DATEDIFF(now(),debtortrans.trandate)>=0 and (((debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount))
										-debtortrans.alloc) > 0.9)
				THEN '#fb8888' ELSE
				CASE WHEN (DATEDIFF(now(),debtortrans.trandate)>=-11 AND(((debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight
						+ debtortrans.ovdiscount)) -debtortrans.alloc) > 0.9) THEN '#f2fcbd' ELSE '#ffffff' END END AS color,
			IFNULL(salesmanname,'') AS vendedor,
			CASE WHEN(debtortrans.type='70') THEN (select debtortrans.folio  from debtortrans where debtortrans.id=custallocns.transid_allocto)
			ELSE '' END as foliofactura,
			debtortrans.emails
			FROM systypescat,tags
			LEFT JOIN areas a ON tags.areacode = a.areacode
			LEFT JOIN departments d ON tags.u_department = d.u_department
			,debtortrans
			LEFT JOIN salesorders s ON debtortrans.order_=s.orderno AND debtortrans.debtorno=s.debtorno
			    AND debtortrans.branchcode=s.branchcode
			LEFT JOIN salesman t ON s.salesman=t.salesmancode
			left JOIN custallocns on debtortrans.id=custallocns.transid_allocfrom
			
			WHERE debtortrans.type = systypescat.typeid";
			
			if ($TipoCambio == "MXN"){
				$SQL .= " and debtortrans.rate = 1 "; 	
			}elseif ($TipoCambio == "USD"){
				$SQL .= " and debtortrans.rate <> 1 ";
			}
			
			$SQL .= "AND tags.tagref=debtortrans.tagref
			AND debtortrans.type in (10,20,70,21,110,113,400,410,440,560,119,96,109,66)
			AND debtortrans.debtorno = '" . $CustomerID . "'
			AND (tags.legalid = '" . $_POST['razonsocial'] . "' or '" . $_POST['razonsocial'] ."' = '0')";
		if (isset($fechaini) AND isset($fechafin) and !isset($_POST['historial'])) {
		    $SQL .= " AND debtortrans.origtrandate between '".$fechaini."' and '".$fechafin."'";				
		}
		
		
		if (isset($_POST['todaslasunidades']) and strlen($_POST['todaslasunidades'])>0) {
			$SQL .= " AND debtortrans.tagref=".$_POST['todaslasunidades'];
		}
		
		if (isset($_POST['sucursal']) and $_POST['sucursal'] != 0) {
			$SQL .= " AND a.areacode=" . $_POST['sucursal'];
		}
		
		if (isset($_POST['departamento']) and $_POST['departamento'] != 0) {
			$SQL .= " AND d.u_department = " . $_POST['departamento'];
		}
		
		if (!isset($_POST['todaslasunidades'])){
		  $SQL .= " AND debtortrans.tagref  in (select tagref from sec_unegsxuser where userid='".$_SESSION['UserID']."')";
		};	     
		if (isset($_POST['solosaldo'])){
		  $SQL .= " AND (debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount-alloc)>0";
		};
	
		$SQL .= " ORDER BY origtrandate, trandate";
		//echo "<br>Cargos1:".$SQL;
	}elseif($id>0){//Abonos
        //echo "<br>id:".$id;
		
		$SQL =     "SELECT systypescat.typename,
				debtortrans.id,
				debtortrans.type,
				debtortrans.debtorno,
				debtortrans.order_,
				debtortrans.transno,
				debtortrans.branchcode,
				debtortrans.origtrandate,
				debtortrans.trandate,
				debtortrans.reference,
				debtortrans.invtext,
				debtortrans.order_,
				debtortrans.rate,
				debtortrans.folio,
				round(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount,2) AS totalamount,
				round((debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount)- alloc,2) AS totalamount2,
				debtortrans.ovamount as monto,
				debtortrans.ovgst as iva,
				debtortrans.alloc AS allocated,
				tags.tagdescription,
				tags.tagref,
				debtortrans.folio,
				custallocns.transid_allocto,
				custallocns.transid_allocfrom ,
				custallocns.amt,
				CASE WHEN ( DATEDIFF(now(),debtortrans.trandate)>=0
				AND (((debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount)) -debtortrans.alloc) > 0.9) THEN '#fb8888' ELSE
				CASE WHEN (DATEDIFF(now(),debtortrans.trandate)>=-11
				AND(((debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount)) -debtortrans.alloc) > 0.9)
				THEN '#f2fcbd' ELSE '#ffffff' END END AS color, IFNULL(salesmanname,'') AS vendedor,
				debtortrans.emails
				FROM systypescat,tags,debtortrans";
				if($tiponaturaleza==0){
				$SQL .= " INNER JOIN custallocns ON debtortrans.id=custallocns.transid_allocfrom";	
				}
				if($tiponaturaleza==1){
				$SQL .= " INNER JOIN custallocns ON debtortrans.id=custallocns.transid_allocto";
				}
				$SQL .="
				LEFT JOIN salesorders s ON debtortrans.order_=s.orderno
				AND debtortrans.debtorno=s.debtorno
				AND debtortrans.branchcode=s.branchcode
				LEFT JOIN salesman t ON s.salesman=t.salesmancode
				WHERE debtortrans.type = systypescat.typeid
				AND tags.tagref=debtortrans.tagref
				AND debtortrans.debtorno = '" . $CustomerID . "'
				/*AND debtortrans.trandate between '".$DateSinceCriteria."' and '".$DateAfterCriteria."'*/
				AND (tags.legalid = '" . $_POST['razonsocial'] . "' or '" . $_POST['razonsocial'] ."' = '0')";
				if($tiponaturaleza==0){
				$SQL .= " AND custallocns.transid_allocto='" . $id . "'";
				}
				if($tiponaturaleza==1){
				$SQL .= " AND custallocns.transid_allocfrom='" . $id . "'";	
				}
				
				if (!isset($_POST['todaslasunidades'])){
				$SQL .= " AND debtortrans.tagref  in (select tagref from sec_unegsxuser where userid='".$_SESSION['UserID']. "')";
				}
				
				/*if ($id ==7593){
					echo $SQL;
					
				}*/
						//$SQL .= "GROUP BY debtorno,folio";
						//echo "<br>abonos:".$SQL;
							
	}elseif($id== -1){//Abonos no aplicados
	
		$SQL = 	"SELECT systypescat.typename,
				debtortrans.id,
				debtortrans.type,
				debtortrans.debtorno,
				debtortrans.order_,
				debtortrans.transno,
				debtortrans.branchcode,
				debtortrans.origtrandate,
				debtortrans.trandate,
				debtortrans.reference,
				debtortrans.invtext,
				debtortrans.order_,
				debtortrans.rate,
				debtortrans.folio,
				round(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount,2) AS totalamount,";
				if ($TipoCambio == "MXN"){
				$SQL .=  "round((debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount)- alloc,2) AS totalamount2,";	
				}elseif ($TipoCambio == "USD"){
				$SQL .= " round(((debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount)) - alloc,2) AS totalamount2,";
				}
				$SQL .=  "
				debtortrans.ovamount as monto,
				debtortrans.ovgst as iva,
				debtortrans.alloc AS allocated,
				tags.tagdescription,
				tags.tagref,
				custallocns.amt,
				CASE WHEN (
					DATEDIFF(now(),debtortrans.trandate)>=0 and (((debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount))
											-debtortrans.alloc) > 0.9)
					THEN '#fb8888' ELSE
					CASE WHEN (DATEDIFF(now(),debtortrans.trandate)>=-11 AND(((debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight
							+ debtortrans.ovdiscount)) -debtortrans.alloc) > 0.9) THEN '#f2fcbd' ELSE '#ffffff' END END AS color,
				IFNULL(salesmanname,'') AS vendedor,
				debtortrans.emails
				FROM systypescat,tags,debtortrans
				LEFT JOIN salesorders s ON debtortrans.order_=s.orderno
				LEFT JOIN custallocns ON debtortrans.id=custallocns.transid_allocto
				AND debtortrans.debtorno=s.debtorno
				AND debtortrans.branchcode=s.branchcode
				LEFT JOIN salesman t ON s.salesman=t.salesmancode
				WHERE debtortrans.type = systypescat.typeid
				AND tags.tagref=debtortrans.tagref
				/*AND debtortrans.origtrandate between '".$DateSinceCriteria."' and '".$DateAfterCriteria."'*/
				/*and debtortrans.alloc=0*/
				AND debtortrans.type in (11,13,80,112,200,420,430, 450,460,560,14,65)
				AND abs((debtortrans.ovamount + debtortrans.ovgst ) - alloc)>=.01";
				
				if ($TipoCambio == "MXN"){
				$SQL .= " and debtortrans.rate = 1 "; 	
				}elseif ($TipoCambio == "USD"){
				$SQL .= " and debtortrans.rate <> 1 ";
				}
				$SQL .= "
				AND debtortrans.debtorno = '" . $CustomerID . "'
				
				AND (tags.legalid = '" . $_POST['razonsocial'] . "' or '" . $_POST['razonsocial'] ."' = '0')";
				if (isset($fechaini) AND isset($fechafin) and !isset($_POST['historial'])) {
					$SQL .= " AND debtortrans.origtrandate between '".$fechaini."' and '".$fechafin."'";				
				}
				
				if (isset($_POST['todaslasunidades']) and strlen($_POST['todaslasunidades'])>0) {
					$SQL .= " AND debtortrans.tagref=".$_POST['todaslasunidades'];
				}
				if (!isset($_POST['todaslasunidades'])){
				  $SQL .= " AND debtortrans.tagref  in (select tagref from sec_unegsxuser where userid='".$_SESSION['UserID']."')";
				};	   
				/*  
				if (isset($_POST['solosaldo'])){
				  $SQL .= " AND (debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount-alloc)>0";
				};*/
				
				/*$SQL .= "GROUP BY debtorno,folio";*/
				/*$SQL .= " ORDER BY origtrandate, trandate";*/
				//echo "<br>consulta abono2".$SQL;
				
	
	}elseif($id== -2){
	    //echo "entra aqui<br>";
	    $SQL = 	"SELECT systypescat.typename,
				debtortrans.id,
				debtortrans.type,
				debtortrans.debtorno,
				debtortrans.order_,
				debtortrans.transno,
				debtortrans.branchcode,
				debtortrans.origtrandate,
				debtortrans.trandate,
				debtortrans.reference,
				debtortrans.invtext,
				debtortrans.order_,
				debtortrans.rate,
				debtortrans.folio,
				round(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight+ debtortrans.ovdiscount,2) AS totalamount,";
				if ($TipoCambio == "MXN"){
				$SQL .=  " round((debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount)- alloc) AS totalamount2,";	
				}elseif ($TipoCambio == "USD"){
				$SQL .= " round(((debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount)) - alloc,2) AS totalamount2,";
				}
				$SQL .=  "
				debtortrans.ovamount as monto,
				debtortrans.ovgst as iva,
				debtortrans.alloc AS allocated,
				tags.tagdescription,
				tags.tagref,
				custallocns.amt,
				CASE WHEN (
					DATEDIFF(now(),debtortrans.trandate)>=0 and (((debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount))
											-debtortrans.alloc) > 0.9)
					THEN '#fb8888' ELSE
					CASE WHEN (DATEDIFF(now(),debtortrans.trandate)>=-11 AND(((debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight
							+ debtortrans.ovdiscount)) -debtortrans.alloc) > 0.9) THEN '#f2fcbd' ELSE '#ffffff' END END AS color,
				IFNULL(salesmanname,'') AS vendedor,
				debtortrans.emails
				FROM systypescat,tags,debtortrans
				LEFT JOIN salesorders s ON debtortrans.order_=s.orderno
				LEFT JOIN custallocns ON debtortrans.id=custallocns.transid_allocfrom
				AND debtortrans.debtorno=s.debtorno
				AND debtortrans.branchcode=s.branchcode
				LEFT JOIN salesman t ON s.salesman=t.salesmancode
				WHERE debtortrans.type = systypescat.typeid
				AND tags.tagref=debtortrans.tagref
				/*AND debtortrans.origtrandate between '".$DateSinceCriteria."' and '".$DateAfterCriteria."'*/
				/*and debtortrans.alloc=0*/
				AND debtortrans.type in (11,13,80,112,200,420,430, 450,460,560,12,70,14,65)";
				
				if ($TipoCambio == "MXN"){
				$SQL .= " and debtortrans.rate = 1 "; 	
				}elseif ($TipoCambio == "USD"){
				$SQL .= " and debtortrans.rate <> 1 ";
				}
				$SQL .= "
				AND debtortrans.debtorno = '" . $CustomerID . "'
				
				AND (tags.legalid = '" . $_POST['razonsocial'] . "' or '" . $_POST['razonsocial'] ."' = '0')";
				if (isset($fechaini) AND isset($fechafin) and !isset($_POST['historial'])) {
				$SQL .= " AND debtortrans.origtrandate between '".$fechaini."' and '".$fechafin."'";				
				}
				
				if (isset($_POST['todaslasunidades']) and strlen($_POST['todaslasunidades'])>0) {
					$SQL .= " AND debtortrans.tagref=".$_POST['todaslasunidades'];
				}
				if (!isset($_POST['todaslasunidades'])){
				  $SQL .= " AND debtortrans.tagref  in (select tagref from sec_unegsxuser where userid='".$_SESSION['UserID']."')";
				};	     
				if (isset($_POST['solosaldo'])){
				  $SQL .= " AND (debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount-alloc)>0";
				};
	    //echo '<pre>'.$SQL.'<br>';
	    
	}elseif($id== -3){
	    
	    
	    	//echo "tipo C".$TipoCambio;
		$SQL = "SELECT systypescat.typename,
			debtortrans.id,
			debtortrans.type,
			debtortrans.debtorno,
			debtortrans.order_,
			debtortrans.transno,
			debtortrans.branchcode,
			debtortrans.origtrandate,
			debtortrans.trandate,
			debtortrans.reference,
			debtortrans.invtext,
			debtortrans.order_,
			debtortrans.rate,
			debtortrans.folio,
			round(debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount,2) AS totalamount,";
						
			if ($TipoCambio == "MXN"){
				$SQL .=  " round((debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount)- alloc,2) AS totalamount2,";	
			}elseif ($TipoCambio == "USD"){
				$SQL .= " round(((debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount) - alloc),2) AS totalamount2,";
			}
			$SQL .= "
			debtortrans.ovamount as monto,
			debtortrans.ovgst as iva,
			debtortrans.alloc AS allocated,
			tags.tagdescription,
			tags.tagref,
			debtortrans.folio,
			CASE WHEN (
				DATEDIFF(now(),debtortrans.trandate)>=0 and (((debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount))
										-debtortrans.alloc) > 0.9)
				THEN '#fb8888' ELSE
				CASE WHEN (DATEDIFF(now(),debtortrans.trandate)>=-11 AND(((debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight
						+ debtortrans.ovdiscount)) -debtortrans.alloc) > 0.9) THEN '#f2fcbd' ELSE '#ffffff' END END AS color,
			IFNULL(salesmanname,'') AS vendedor,
			CASE WHEN(debtortrans.type='70') THEN (select debtortrans.folio  from debtortrans where debtortrans.id=custallocns.transid_allocto)
			ELSE '' END as foliofactura,
			debtortrans.emails
			FROM systypescat,tags,debtortrans
			LEFT JOIN salesorders s ON debtortrans.order_=s.orderno
			AND debtortrans.debtorno=s.debtorno
			AND debtortrans.branchcode=s.branchcode
			LEFT JOIN salesman t
			ON s.salesman=t.salesmancode
			left JOIN custallocns on debtortrans.id=custallocns.transid_allocfrom
			WHERE debtortrans.type = systypescat.typeid";
			
			if ($TipoCambio == "MXN"){
				$SQL .= " and debtortrans.rate = 1 "; 	
			}elseif ($TipoCambio == "USD"){
				$SQL .= " and debtortrans.rate <> 1 ";
			}
			
			$SQL .= "AND tags.tagref=debtortrans.tagref
			AND debtortrans.type in (10,20,70,21,110,113,400,410,440,560,96,109,66)
			AND custallocns.transid_allocto is null
			AND custallocns.transid_allocfrom is null
			AND debtortrans.debtorno = '" . $CustomerID . "'
			AND (tags.legalid = '" . $_POST['razonsocial'] . "' or '" . $_POST['razonsocial'] ."' = '0')";
			if (isset($fechaini) AND isset($fechafin) and !isset($_POST['historial'])) {
			    $SQL .= " AND debtortrans.origtrandate between '".$fechaini."' and '".$fechafin."'";				
			}
		
		
		if (isset($_POST['todaslasunidades']) and strlen($_POST['todaslasunidades'])>0) {
			$SQL .= " AND debtortrans.tagref=".$_POST['todaslasunidades'];
		}
		if (!isset($_POST['todaslasunidades'])){
		  $SQL .= " AND debtortrans.tagref  in (select tagref from sec_unegsxuser where userid='".$_SESSION['UserID']."')";
		};	     
		if (isset($_POST['solosaldo'])){
		  $SQL .= " AND (debtortrans.ovamount + debtortrans.ovgst + debtortrans.ovfreight + debtortrans.ovdiscount-alloc)>0";
		};
	
		$SQL .= " ORDER BY origtrandate, trandate";
	    
	    
	    }		
if($_SESSION['UserID']=='admin'){
	echo "<pre>".$SQL.'<br><br>';
}
//exit;
	$result = DB_query($SQL,$db);

	//echo "<br><br>" . $SQL;	
	if (DB_num_rows($result) > 0)
	{
		
			//$_SESSION['cont'] = 0;
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
			
		while ($myrow = DB_fetch_array($result))
		{
				//echo "<br>" . $myrow['folio'];
				$idtrans=$myrow['id'];
				$_SESSION['cont'] = $_SESSION['cont'] + 1;
				$tipo = $myrow['typename'];
				$origtrandate = ConvertSQLDate($myrow['origtrandate']);
				$fechaemision=$myrow['origtrandate'];
				$trandate = ConvertSQLDate($myrow['trandate']);
				$transno = $myrow['transno'];
				$order = $myrow['order_'];
				$transno2 = $myrow['transno'];
				$referencia = $myrow['reference'];
				$iva = $myrow['iva'];
				$monto = $myrow['monto'];
				$alloc = $myrow['allocated'];
				$sumatotal = doubleval($sumatotal) + doubleval(abs($myrow['totalamount']));
				$sumapagos = doubleval($sumapagos) + doubleval(abs($myrow['allocated']));
				$comentarios=$myrow['invtext'];
				$amt=$myrow['amt'];
				$total2=$myrow['totalamount2'];
				$totalamount=$myrow['totalamount'];
				$iddocto=$myrow['id'];
				$foliox=$myrow['folio'];
				$foliofactura=$myrow['foliofactura'];
				$type=$myrow['type'];
				$_SESSION['$tipocambio']=(1/$myrow['rate']);
				
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
				
				$vendedor=$myrow['vendedor'];
					if ($_SESSION['natcontable'][$myrow['type']] < 0 ){
					$sumasaldo = doubleval($sumasaldo) - doubleval(abs($myrow['totalamount'])-abs($myrow['allocated']));
					$_POST['sumasaldo']=$sumasaldo;
				}else{
					$sumasaldo = doubleval($sumasaldo) + doubleval(abs($myrow['totalamount'])-abs($myrow['allocated']));
					$_POST['sumasaldo']=$sumasaldo;
				}
				
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
				if ($tipofac=='10' or $tipofac=='110' or $tipofac=='11' or $tipofac=='13' or $tipofac=='21' or $tipofac == '410' or $tipofac == '109' or $tipofac == '66')
				{
					$verfact=1;
				}
				
				$SQL=" SELECT l.taxid,l.keysend,t.tagdescription,t.typeinvoice,t.datechange,l.legalname
					FROM legalbusinessunit l, tags t
					WHERE l.legalid=t.legalid AND tagref='".$tagref."'";
				//	echo $SQL;
				 $Result= DB_query($SQL,$db);
				if (DB_num_rows($Result)==1)
				{
					 $myrowtags = DB_fetch_array($Result);
					 $rfc=trim($myrowtags['taxid']);
					 $keyfact=$myrowtags['keysend'];
					 $nombre=$myrowtags['tagdescription'];
					 $tipofacturacionxtag=$myrowtags['typeinvoice'];
					 $fechacambio=$myrowtags['datechange'];
					  $legalname=$myrowtags['legalname'];
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
				
				
				if (isset($_POST['PrintEXCEL'])) {
					echo "<td style='text-align:left; font-size:7pt; font-family:arial;'>".$unidadnegocio."</td>";
					echo "<td style='text-align:left; font-size:7pt; font-family:arial;'>".$origtrandate."</td>";
				}else{
					echo "<td style='text-align:left; font-size:7pt; font-family:arial;'><b>".$origtrandate."</b><br>".$unidadnegocio."</td>";	
				}	
				if($nivel==1 and $type=='70'){
					if (isset($_POST['PrintEXCEL'])) {
						echo "<td nowrap style='text-align:center;'><font size=1 face='arial'>" . $foliox . "<br>FACT.:" . str_replace("|","",$foliofactura) . "<br></b></font></td>";

					}else{
						echo "<td nowrap style='text-align:center;'><font size=1 face='arial'></td>";
						echo "<td nowrap style='text-align:center;'><font size=1 face='arial'>ERP:".$transno. "<br>SAT:".$foliox."<br>
							<font style='text-align:left; font-size:7pt; font-family:arial;'><b>ID:".$idtrans. "<br>FACT.:" . str_replace("|","",$foliofactura) . "<br></b></font></td>";
						echo "<td nowrap style='text-align:center;'><font size=1 face='arial'></td>";	
					}
					
				}else{
					if (isset($_POST['PrintEXCEL'])) {
						echo "<td nowrap style='text-align:center;'><font size=1 face='arial'>" . str_replace("|","",$foliox) . "</font></td>";
					}else{
						echo "<td nowrap style='text-align:center;'><font size=1 face='arial'></td>";
						echo "<td nowrap style='text-align:center;'><font size=1 face='arial'>ERP:".$transno. "<br>SAT:". str_replace("|","",$foliox) ."<br>
						    <font style='text-align:left; font-size:7pt; font-family:arial;'><b>ID:".$idtrans. "<br></b></font></td>";
						echo "<td nowrap style='text-align:center;'><font size=1 face='arial'></td>";
					}
				}
				
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
				if (isset($_POST['PrintEXCEL'])) {
					echo "<td nowrap><font style='font-size:7pt; font-family:arial;'>" . $espacio . "" . $image . "" . $espacio2 . "" . strtoupper($tipo);
				}else{
					echo "<td nowrap><font style='font-size:7pt; font-family:arial;'><a href='#' style='font-size:8pt; font-family:arial;' title='".strtoupper($comentarios)."' >".$espacio."".$image."".$espacio2."".strtoupper($tipo)."</a>";
					if ($vendedor != ""){
						echo "<br><span style='font-size:6pt;'>V: " . strtoupper($vendedor) . "</span></td>";	
					}
				}
				if ($tiponaturaleza == 1 ){
				
					$signo ="-";
					
					$_SESSION['sumapagos']= ($_SESSION['sumapagos']) + ($amt);
					
					$typetrans=-1;
					if($nivel <= 1){
						if($_SESSION['$tipocambio'] == 1){
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";
							echo "<td nowrap class=number><font size=1 face='arial'>$" . $signo . number_format($total,2) ."</td>";
							echo "<td nowrap class=number><font size=1 face='arial'><b>$". number_format($total2,2)."</b></td>";
							$_SESSION['totalsaldo'] = $_SESSION['totalsaldo'] + $total2;
							$_SESSION['totalsaldoconrate']=$_SESSION['totalsaldoconrate']+(($total2)*$_SESSION['$tipocambio']);
						}else{
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";
							echo "<td nowrap class=number><font size=1 face='arial'>US$" . $signo . number_format($total,2) ."</td>";
							echo "<td nowrap class=number><font size=1 face='arial'><b>$". number_format($total2,2)."</b></td>";
							$_SESSION['totalsaldo'] = $_SESSION['totalsaldo'] + $total2;
							$_SESSION['totalsaldoconrate']=$_SESSION['totalsaldoconrate']+(($total2)*$_SESSION['$tipocambio']);
						}
					}
					if($nivel > 1){
						if($_SESSION['$tipocambio'] == 1){
							$alloc=$alloc * $myrow['rate'];
							echo "<td nowrap class=number><font size=1 face='arial' >$" . $signo . number_format($alloc,2) ."</td>";
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";
							//echo "<td nowrap class=number><font size=1 face='arial' >" . $signo . number_format($total,2) ."</td>";
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";
						}else{
							$alloc=$alloc * $myrow['rate'];
							echo "<td nowrap class=number><font size=1 face='arial' >US$" . $signo . number_format($alloc,2) ."</td>";
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";
							//echo "<td nowrap class=number><font size=1 face='arial' >" . $signo . number_format($total,2) ."</td>";
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";	
						}
					}	
					
					//echo "<td nowrap class=number><font size=1>".number_format($alloc,2) ."</td>";					//echo "<td nowrap class=number><font size=1 face='arial'>0.00</td>";
					//echo "<td nowrap class=number><font size=1 face='arial'>". $signo . number_format($monto,2) ."</td>";
				}elseif($tiponaturaleza == 0 ){
					$signo = " ";
					//$_SESSION['totalcargos'] = $_SESSION['totalcargos'] + abs($total);
					$typetrans=1;
					if($nivel <= 1){
						if($_SESSION['$tipocambio'] == 1){
							$alloc=$alloc * $myrow['rate'];
							if (isset($_POST['PrintEXCEL'])) {
								$prefijo = "";
							}else{
								$prefijo = "$&nbsp;";
							}
							echo "<td nowrap class=number><font size=1 face='arial' >" . $prefijo . $signo . number_format($total-$iva,2) ."</td>";
							echo "<td nowrap class=number><font size=1 face='arial' >" . $prefijo . number_format($iva,2)."</td>";
							echo "<td nowrap class=number><font size=1 face='arial' >" . $prefijo . number_format($total,2)."</td>";
							echo "<td nowrap class=number><font size=1>" . $prefijo . number_format($alloc,2) ."</td>";
							echo "<td nowrap class=number><font size=1 face='arial'><b>" . $prefijo . $signo.number_format($total2,2)."</b></td>";
							$_SESSION['totalsaldo'] = $_SESSION['totalsaldo'] + $total2;
							$_SESSION['totalsaldoconrate']=$_SESSION['totalsaldoconrate']+(($total2)*$_SESSION['$tipocambio']);
						}else{
						//$alloc=$alloc * $myrow['rate'];
							if (isset($_POST['PrintEXCEL'])) {
								$prefijo = "";
							}else{
								$prefijo = "US$&nbsp;";
							}
							echo "<td nowrap class=number><font size=1 face='arial'>" . $prefijo . $signo . number_format($total-$iva,2) ."</td>";
							echo "<td nowrap class=number><font size=1 face='arial'>" . $prefijo . number_format($iva,2)."</td>";
							echo "<td nowrap class=number><font size=1 face='arial'>" . $prefijo . number_format($total,2)."</td>";
							echo "<td nowrap class=number><font size=1>" . $prefijo . number_format($alloc,2) ."</td>";
							echo "<td nowrap class=number><font size=1 face='arial'><b>" . $prefijo . $signo.number_format($total2,2)."</b></td>";
							$_SESSION['totalsaldo'] = $_SESSION['totalsaldo'] + $total2;
							$_SESSION['totalsaldoconrate'] = $_SESSION['totalsaldoconrate']+(($total2)*$_SESSION['$tipocambio']);
						}
					}
					if($nivel > 1){
						$signo ="-";
						//echo "<td nowrap class=number><font size=1 face='arial' >" . $signo . number_format($total,2) ."</td>";
						if($_SESSION['$tipocambio'] == 1){
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";
							$amt=$amt*$myrow['rate'];
							if (($amt < 0) and ($signo == '-')){
								echo "<td nowrap class=number><font size=1>$".number_format(abs($amt),2) ."</td>";	
							}else{
								echo "<td nowrap class=number><font size=1>$".$signo.number_format($amt,2) ."</td>";
							}
							
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";
						}else{
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";
							//$amt=$amt*$myrow['rate'];
							echo "<td nowrap class=number><font size=1>US$".$signo.number_format($amt,2) ."</td>";
							echo "<td nowrap class=number><font size=1>&nbsp;</td>";	
						}
					}
				}
					//$_SESSION['totalsaldoconrate']=$_SESSION['totalsaldoconrate']+			
								
				echo "<td nowrap style='text-align:center;'><font size=1>" . number_format($_SESSION['$tipocambio'],4)."</td>";
				echo "<td nowrap style='text-align:center;'><font size=1>$" . number_format(($total2)*$_SESSION['$tipocambio'],2)."</td>";
				
				echo "<td nowrap style='text-align:center;'><font size=1>".$trandate."</td>";
				
			    if (isset($_POST['PrintEXCEL'])) {
			    
			    }else{
				if (Havepermission($_SESSION['UserID'],349, $db)==1 and $myrow['type']>110){
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
				    }
			    }
				/// COMENTARIOS ///
				
					    if (Havepermission($_SESSION['UserID'],579, $db)==1)
					    {
						$sqldoc1="SELECT debtortranscomments.userid as userid , accountstatus.accountstatus as status
							FROM debtortranscomments JOIN accountstatus ON debtortranscomments.idstatus = accountstatus.idstatus
							WHERE debtortranscomments.id='".$iddocto."'
							ORDER BY creationtime desc
							LIMIT 1";
							//echo '<br>ll'.$sql;
						$resultdoc1 = DB_query($sqldoc1, $db);
						if ($doc=DB_fetch_array($resultdoc1,$db)){
							
								echo "<td style='text-align:left; background-color:#FDF770'>";
								echo "<font size=1 color='#5b5202'>";
								echo "<b>";
								echo '&nbsp;';
								$pagina='ABC_CommentsXDocument.php'.'?ID='.$iddocto.'&folio='.$foliox;
								echo "<a target='_blank' href='".$pagina."'><font size=1>".$doc['status']."</a><br>
									".$doc['userid']." ";
								echo "</b>";
								echo "</font>";
								echo "</td>";
								
						} elseif (isset($_POST['PrintEXCEL'])) {
						
						
						    }else {
								echo "<td style='text-align:left;' nowrap>";
								echo "<font size=1>";
								echo "<b>";
								echo '&nbsp;';
								$pagina='ABC_CommentsXDocument.php'.'?ID='.$iddocto.'&folio='.$foliox;
								echo "<a target='_blank' href='".$pagina."'><font size=1>+ Comentario</a>";
								echo "</b>";
								echo "</font>";
								echo "</td>";
							    }
						
					    }else {
						echo "<td style='text-align:center' nowrap>";
						echo "<font size=1 color='#5b5202'>";
						echo "<b>";
						echo '&nbsp;';
						echo "</b>";
						echo "</font>";
						echo "</td>";
					    }
					 
				if(isset($_POST['PrintEXCEL'])) {
				
				}else{
				echo "<td nowrap style='text-align:center;'><a href='GLTransInquiryV2.php?TypeID=".$myrow['type']."
					&TransNo=".$myrow['transno']."&tagref=".$tagref."'><font size=1>". _('Contable') . "</a></td>";
				echo "<td nowrap >&nbsp;";
				}
				#if ($myrow['type']=="10")
				if (isset($_POST['PrintEXCEL'])) {
				    
				}else{
				    if (abs($myrow['totalamount']) > 0 and ($myrow['type']==10 or $myrow['type']==110 or $myrow['type']==119 or $myrow['type']==410 or $myrow['type']==11 or $myrow['type']==14))
				    {
					//$liga = GetUrlToPrint($tagref,$tipofac,$db);
					//$PrintDispatchNote = $rootpath . '/' . $liga  . '&' . SID . 'TransNo=' . $transno;
					if($myrow['type']==11 or $myrow['type']==14){
						if (Havepermission($_SESSION['UserID'],290, $db)==1){
							/*$existenota=ExistsNoteCredit($transno,$myrow['type'],$tagref,$db);
							if($existenota==0){
								echo "<a href='SelectNoteItemsCancel.php?CreditNumber=".$transno."&CreditType=".$myrow['type']."'><font size=1>". _('Cancelacion<br>NC') . "</a></td>";
							}else{
								echo "<font size=1>". _('Cancelacion<br>NC') . "</td>";
							}*/	
						}
					}else{
						if (Havepermission($_SESSION['UserID'],290, $db)==1){
							$existenota=ExistsNote($transno,$myrow['type'],$tagref,$db);
							//if($existenota==0){
								echo "<a href='SelectNoteItems.php?InvoiceNumber=".$transno."&InvoiceType=".$myrow['type']."'><font size=1>". _('Nota<br>Credito') . "</a></td>";
							//}else{
								//echo "<font size=1>". _('Nota<br>Credito') . "</td>";
							//}	
						}
					}
				    }
				}
				echo "<td>";
				if (isset($_POST['PrintEXCEL'])) {
				    
				}else{
				    if  ($myrow['type']=="10" or $myrow['type']=="110" or $myrow['type']=="109" or $myrow['type']=="119" or $myrow['type']=="410" or $myrow['type']=="66"){
					if ($tipofacturacionxtag==1){	
					    
					    if ($folio != ''){
						echo '<p ><img src="images/PRINT.gif"  title="' . _('Imprimir Factura') . '" alt="">';
						echo '<a  target="_blank" href="'.$_SESSION['XSA'].'xsamanager/downloadCfdWebView?
						serie='.$serie.'&folio='.$folio.'&tipo=PDF&rfc='.$rfc.'&key='.$keyfact.'"><font size=1>'. _('Factura') .'</a>';	
					    }
					    
					} elseif ($tipofacturacionxtag==2) {
					    if ($fechaemision < $fechacambio){
						if($_SESSION['EnvioXSA']==0){
							//echo "entra aquiiiiiiiiii";
						    $liga = GetUrlToPrint($tagref,10,$db);
						    echo '<p ><img src="images/PRINT.gif" title="' . _('Imprimir Factura') . '" alt="">';
						    //echo '<a  target="_blank" href="'.$liga.'&TransNo='.$order.'&Tagref='.$tagref.'"><font size=1>'. _('Imprimir Factura') .'</a>';
						     if($liga == ''){//------biviana
							//echo "entra aquiiiiiiiiii";
								$liga="PDFInvoice.php";
								echo '<a  target="_blank" href="'.$liga.'?OrderNo='.$order.'&TransNo='.$transno.'&Tagref='.$tagref.'&Type='.$myrow['type'].'"><font size=1>'. _(' Factura') .'</a>';    
							}else{//-------
						    	echo '<a  target="_blank" href="'.$liga.'&TransNo='.$order.'&Tagref='.$tagref.'"><font size=1>'. _(' Factura') .'</a>';    
							} 
						}else{//echo"entra";
						    echo '<p><img src="images/PRINT.gif" title="' . _('Imprimir Factura') . '" alt="">';
						    echo '<a  target="_blank" href="'.$_SESSION['XSA'].'xsamanager/downloadCfdWebView?
							serie='.$serie.'&folio='.$folio.'&tipo=PDF&rfc='.$rfc.'&key='.$keyfact.'">'. _('Factura') .'</a>';
						}
					    }elseif($tipofacturacionxtag==3){
							if ($typedeb!=119){
							    $typeinvoice=10;
							}else{
							    $typeinvoice=119;
							}
							$liga = GetUrlToPrint($tagref,$typeinvoice,$db);
							echo '<p><img src="images/PRINT.gif" title="' . _('Imprimir') . '" alt="">';
							echo '<a  target="_blank" href="'.$liga.'?TransNo='.$transno.'&Tagref='.$tagref.'&OrderNo='.$order.'&Type='.$myrow['type'].'">'. _('Imprimir Factura') .'</a>';    
					    }else{
						    $liga="PDFInvoice.php";
						    echo '<p><img src="images/PRINT.gif" title="' . _('Imprimir Factura') . '" alt="">';
						    echo '<a  target="_blank" href="'.$liga.'?TransNo='.$transno.'&Tagref='.$tagref.'&OrderNo='.$order.'&Type='.$myrow['type'].'">'. _('Imprimir Factura') .'</a>';    
					    }
					}else{
						$liga = GetUrlToPrint($tagref,10,$db);
						echo '<p ><img src="images/PRINT.gif" title="' . _('Imprimir Factura') . '" alt="">';
						echo '<a  target="_blank" href="'.$liga.'&TransNo='.$order.'&Tagref='.$tagref.'"><font size=1>'. _('Imprimir Factura') .'</a>';    
					}
				    } else {
					echo "&nbsp;";
					};
				}
				//columna para agregar la opcion de imprimir venta pagare
				//PDFVentaPagarePage.php?PrintPDF=Yes&type=590&TransNo=1
				if (isset($_POST['PrintEXCEL'])) {
				    
				}else{
					echo "<td style='text-align:left; font-size:7pt; font-family:arial;'>";
					if ($myrow['type']=="560"){
						$liga = "PDFVentaPagarePage.php?";
						echo '<p ><img src="images/PRINT.gif" title="' . _('Imprimir Factura') . '" alt="">';
						echo '<a  target="_blank" href="'.$liga.'PrintPDF=Yes&type='.$myrow['type'].'&TransNo='.$transno.'"><font size=1>'. _('Impresion<br> Venta Pagares') .'</a>';    
					}else{
						echo "&nbsp;";
					};
				}
				
				//------------
				
				echo "<td style='text-align:left; font-size:7pt; font-family:arial;'>";
				if (isset($_POST['PrintEXCEL'])) {
				    
				}else{
				
					if ($facturacredito==1 and $tienepagares<>'0')
					{
						if ($tipofacturacionxtag!=0){
							echo'<img src="./images/PRINT.gif" title="' . _('Pagares') . '"
							alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/PDFPagarePage.php?' . SID .'identifier='.$identifier . '
							&PrintPDF=Yes&TransNo=' . $transno . '"><font size=1>'. _('Pagares') .'</a>';
						}else{
						$liga = GetUrlToPrint($tagref,70,$db);
						echo '<p>';
							echo'<img src="./images/PRINT.gif" title="' . _('Pagares') . '"
							alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/'.$liga.'&'. SID .'identifier='.$identifier . '
							&PrintPDF=Yes&TransNo=' . $transno . '"><font size=1>'. _('Pagares') .'</a>';
						}
					}elseif ($myrow['type']=="11"){
						if ($tipofacturacionxtag==1){
						    echo '<p><img src="./images/PRINT.gif" title="' . _('Imprimir NC') . '" alt="">';
						    echo '<a  target="_blank" href="'.$_SESSION['XSA'].'xsamanager/downloadCfdWebView?
							serie='.$serie.'&folio='.$folio.'&tipo=PDF&rfc='.$rfc.'&key='.$keyfact.'"><font size=1>'. _('Imprimir NC') .'</a>';
						}elseif ($tipofacturacionxtag==2){
						    if ($fechaemision<$fechacambio){
							if($_SESSION['EnviaXSA']==0){
							    $liga = GetUrlToPrint($tagref,11,$db);
							    echo '<p><img src="'.$rootpath.'/css/'.$theme.'/images/PRINT.gif" title="' . _('Imprimir NC') . '" alt="">';
							    echo '<a  target="_blank" href="'.$liga.'&TransNo='.$order.'&Tagref='.$tagref.'">'. _('Imprimir NC') .'</a>';  
							}else{
							    echo '<p><img src="'.$rootpath.'/css/'.$theme.'/images/PRINT.gif" title="' . _('Imprimir NC') . '" alt="">';
							    echo '<a  target="_blank" href="'.$_SESSION['XSA'].'xsamanager/downloadCfdWebView?
								serie='.$serie.'&folio='.$folio.'&tipo=PDF&rfc='.$rfc.'&key='.$keyfact.'">'. _('Imprimir NC') .'</a>';
							}
						    }else{
							$liga="PDFNoteCreditDirect.php?";
							echo '<p><img src="'.$rootpath.'/css/'.$theme.'/images/PRINT.gif" title="' . _('Imprimir NC') . '" alt="">';
							echo '<a  target="_blank" href="'.$liga.'&OrderNo='.$order.'&Tagref='.$tagref.'&TransNo='.$myrow['transno'].'">'. _('Imprimir NC') .'</a>';
						    }
						}else{
						    $liga = GetUrlToPrint($tagref,11,$db);
						    echo '<p><img src="./images/PRINT.gif" title="' . _('Imprimir NC') . '" alt="">';
						    echo '<a  target="_blank" href="'.$liga.'&TransNo='.$order.'&Tagref='.$tagref.'"><font size=1>'. _('Imprimir NC') .'</a>';     
						}
					}elseif(($myrow['type']=="13") or ($myrow['type']=="21")){
						if ($tipofacturacionxtag==1){
						    echo '<p><img src="./images/PRINT.gif" title="' . _('Imprimir NC') . '" alt="">';
						    echo '<a  target="_blank" href="'.$_SESSION['XSA'].'xsamanager/downloadCfdWebView?
							serie='.$serie.'&folio='.$folio.'&tipo=PDF&rfc='.$rfc.'&key='.$keyfact.'"><font size=1>'. _('Imprimir NC') .'</a>';
						}elseif ($tipofacturacionxtag==2){
						   /* $liga = GetUrlToPrint($tagref,$myrow['type'],$db);
						    echo '<p><img src="./images/PRINT.gif" title="' . _('Imprimir NC') . '" alt="">';
						    echo '<a  target="_blank" href="'.$liga.'&OrderNo='.$order.'&Tagref='.$tagref.'&TransNo='.$myrow['transno'].'">'. _('Imprimir NC') .'</a>';
						    */
						    if ($fechaemision<$fechacambio){
							if($_SESSION['EnviaXSA']==0){
							    $liga = GetUrlToPrint($myrow['tagref'],$myrow['type'],$db);
							    $liga = $rootpath . '/' . $liga  . '&' . SID . 'PrintPDF=Yes&TransNo='.$myrow['orderno'].'&Tagref='.$tagref;
							}else{
							    $liga=$_SESSION['XSA']."xsamanager/downloadCfdWebView?serie=".$serie."&folio=".$folio."&tipo=PDF&rfc=".$rfc."&key=".$keyfact;
							}
						    }else{
							if ($myrow['type']==21){
							    $liga = "PDFDebitDirect.php";
							}else{
							    $liga = "PDFCreditDirect.php";
							}
							$liga =  $liga . "?TransNo=" . $myrow['transno'] . "&Tagref=" . $myrow['tagref'];
						    }
						    echo '<p><img src="'.$rootpath.'/css/'.$theme.'/images/PRINT.gif" title="' . _('Imprimir NC') . '" alt="">';
						    echo '<a  target="_blank" href="'.$liga.'&Type='.$myrow['type'].'&Tagref='.$tagref.'">'. _('Imprimir NC') .'</a>';
						}else{
						    $liga = GetUrlToPrint($tagref,$myrow['type'],$db);
						    echo '<p><img src="./images/PRINT.gif" title="' . _('Imprimir NC') . '" alt="">';
						    echo '<a  target="_blank" href="'.$liga.'&TransNo='.$transno.'&Tagref='.$tagref.'"><font size=1>'. _('Imprimir NC') .'</a>';    
						}
						}else{
						echo '&nbsp;';
					     }
				}
					echo '</td>';
				if (isset($_POST['PrintEXCEL'])) {
				    
				}else{
					
					if ($tipofacturacionxtag==1){
					echo "<td style='font-size:7pt;'>";
						if (Havepermission($_SESSION['UserID'],331, $db)==1  and $verfact==1)
						{
							
							$pagina=$_SESSION['XSA']."xsamanager/downloadCfdWebView?serie=" . $serie . "&
								folio=" . $folio . "&tipo=XML&rfc=" . $rfc . "&key=" . $keyfact ;
							echo "<a href='".$pagina."'>";
							echo "XML</a>";
							
							//if (strlen($myrow['emails'])>0){
									$EnviopageXML= './SendInvoiceByMail.php?id='.$idtrans;
									$EnvioXML="&nbsp;&nbsp;&nbsp;<font style='font-size:5pt;'><a target='_blank'  href='".$EnviopageXML."'><img src='part_pics/Mail-Forward.png' alt='Enviar X Mail' border=0>"._('Envio X Email')."</a></font>";		
									echo '<br><br>'.$EnvioXML;
							//}
							
						}
					echo "</td>";
					}
					if ($tipofacturacionxtag==2 OR $tipofacturacionxtag==2 OR $tipofacturacionxtag==4){
					    echo "<td style='font-size:7pt;'>";
						    if (Havepermission($_SESSION['UserID'],331, $db)==1  and $verfact==1)
						    {
							if ($fechaemision<$fechacambio){
								
								$pagina=$_SESSION['XSA']."xsamanager/downloadCfdWebView?serie=" . $serie . "&
									folio=" . $folio . "&tipo=XML&rfc=" . $rfc . "&key=" . $keyfact ;
								echo "<a href='".$pagina."'>";
								echo "XML</a>";
							
							}else{
								if ($tipofac=='12'){
									$folder="Recibo";
								}elseif($tipofac=='10' or $tipofac=='110' or $tipofac=='109' or $tipofac=='66' ){
									$folder="Facturas";
								}elseif($tipofac=='13'){
									$folder="NCreditoDirect";
								}elseif($tipofac=='21'){
									$folder="NCargo";
								}else{
									$folder="NCredito";
								}
							
								    $direccion="/erpdistribucion/companies/".$_SESSION['DatabaseName']."/SAT/".str_replace(',','',str_replace('.','',str_replace(' ','',$legalname)))."/";
								    $pagina=$direccion.'XML/'.$folder.'/'.$serie.$folio.'.xml';
echo $pagina;
								    echo "<a target=_blank href='".$pagina."'>";
								    echo "XML </a>";
							}
							
							$EnviopageXML= './SendInvoiceByMail.php?id='.$idtrans;
							$EnvioXML="&nbsp;&nbsp;&nbsp;<font style='font-size:5pt;'><a target='_blank'  href='".$EnviopageXML."'><img src='part_pics/Mail-Forward.png' alt='Enviar X Mail' border=0>"._('Envio X Email')."</a></font>";		
							echo '<br><br><br>'.$EnvioXML;
							
						    }
				    echo "&nbsp;</td>";
					}
				}
				echo "</tr>";
				//echo "&nbsp;<br>".$tipofacturacionxtag;
				//echo 'sss'.$tiponaturaleza.' sssss '.$myrow['id'];
			
			if ($nivel <=1){
				estadocuenta($myrow['id'],$myrow['debtorno'],$nivel+1,$tiponaturaleza,$TipoCambio,$fechaini,$fechafin,$db);
				
			}
						
		}
	}
			
			
}
			
	//nivel($espacio);
	$_SESSION['totalsaldo']=0;
	$_SESSION['totalsaldoconrate']=0;
	$Abonocargos=$_POST['mabonoscargos'];
	$moneda = 'MXN';
	//echo 'sss';
	//echo $Abonocargos;
	if ($Abonocargos==1){
	    if($moneda = 'MXN'){
		    $_SESSION['impresion']=0;
		    if($_SESSION['impresion']== 0){
			if(isset($_POST['PrintEXCEL'])) {     
			    $_SESSION['totalsaldo']=0;
			    $_SESSION['totalsaldoconrate']=0;
			    echo "<tr><td style='background-color:#F7F8E0;' colspan=24 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
			    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DOCUMENTOS EN PESOS &nbsp;</td></tr>";
			    
			    estadocuenta(-2,$CustomerID,1,1,$moneda,$fechaini,$fechafin,$db);
			   
			    echo "<tr><td colspan=7 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    //echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
			    //echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
			    //$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    $sumasaldo=abs($_SESSION['totalsaldo']);
			    echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo,2)."</td>";//$_SESSION['totalsaldoconrate'];
			    echo "<td nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>$".number_format( $_SESSION['totalsaldoconrate'],2)."</td>";
			}else{
			    $_SESSION['totalsaldo']=0;
			    $_SESSION['totalsaldoconrate']=0;
			    echo "<tr><td style='background-color:#F7F8E0;' colspan=24 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
			    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DOCUMENTOS EN PESOS&nbsp;</td></tr>";
			    
			    estadocuenta(-2,$CustomerID,1,1,$moneda,$fechaini,$fechafin,$db);
			    
			    echo "<tr><td colspan=7 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    //echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
			    //echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
			    //$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    $sumasaldo=abs($_SESSION['totalsaldo']);
			    echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo,2)."</td>";//cargos no aplicados
			    echo "<td nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>$".number_format( $_SESSION['totalsaldoconrate'],2)."</td>";
			}
		    }
	    }
	    $_SESSION['totalsaldo']=0;
	    if($moneda = 'MXN'){
		    $_SESSION['impresion']=1;
		    if($_SESSION['impresion']== 1 ){
			if(isset($_POST['PrintEXCEL'])) {
				// echo 'entra';
			    echo "<tr><td style='background-color:#f2fcbd;' colspan=24 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
			    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;CARGOS SIN PAGOS EN PESOS&nbsp;</td></tr>";
			    unset($_SESSION['totalsaldo']);
			    $_SESSION['totalsaldoconrate']=0;
			    $moneda = 'MXN';
			    estadocuenta(-3,$CustomerID,1,1,$moneda,$fechaini,$fechafin,$db);
			    
			    echo "<tr><td colspan=7 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    //echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
			    //echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
			    //$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    $sumasaldo2=$_SESSION['totalsaldo'];
			    echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo2,2)."</td>";
			    echo "<td nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>$".number_format( $_SESSION['totalsaldoconrate'],2)."</td>";
			    $saldopesos=$sumasaldo2+$sumasaldo;
			    echo "<tr><td  colspan=9 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#8A0808'  align='center'>SALDO TOTAL EN PESOS </td><td class=number><font size=1>$".number_format($saldopesos,2)."</td></tr>";
			}else{
				
			    echo "<tr><td style='background-color:#f2fcbd;' colspan=24 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
			    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;CARGOS SIN PAGOS EN PESOS&nbsp;</td></tr>";
			    unset($_SESSION['totalsaldo']);
			    $_SESSION['totalsaldoconrate']=0;
			    $moneda = 'MXN';
			    estadocuenta(-3,$CustomerID,1,1,$moneda,$fechaini,$fechafin,$db);
			    
			    echo "<tr><td colspan=7 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    //echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
			    //echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
			    //$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    $sumasaldo2=$_SESSION['totalsaldo'];
			    echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo2,2)."</td>";
			    echo "<td nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>$".number_format( $_SESSION['totalsaldoconrate'],2)."</td>";
			    
			    $saldopesos=$sumasaldo2+$sumasaldo;
			    echo "<tr><td  colspan=10 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#8A0808'  align='center'>SALDO TOTAL EN PESOS </td><td class=number><font size=1>$".number_format($saldopesos,2)."</td></tr>";
			}
		    }
	    }
	    
	    $_SESSION['totalsaldo']=0;
	    $_SESSION['totalsaldoconrate']=0;
	    $moneda = 'USD';
	    if($moneda = 'USD'){
	    //DOLARES
		    $_SESSION['impresion']=0;
		    if($_SESSION['impresion']== 0){
			if(isset($_POST['PrintEXCEL'])) {     
			    echo "<tr><td style='background-color:#F7F8E0;' colspan=24 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
			    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DOCUMENTOS EN DOLARES&nbsp;</td></tr>";
			    
			    estadocuenta(-2,$CustomerID,1,1,$moneda,$fechaini,$fechafin,$db);
			    
			    echo "<tr><td colspan=7 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    $sumasaldo=abs($_SESSION['totalsaldo']);
			    echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo,2)."</td>";
			    echo "<td nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>$".number_format( $_SESSION['totalsaldoconrate'],2)."</td>";
			}else{
			    echo "<tr><td style='background-color:#F7F8E0;' colspan=24 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
			    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DOCUMENTOS EN DOLARES&nbsp;</td></tr>";
			    
			    estadocuenta(-2,$CustomerID,1,1,$moneda,$fechaini,$fechafin,$db);
			    
			    echo "<tr><td colspan=7 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    $sumasaldo=abs($_SESSION['totalsaldo']);
			    echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo,2)."</td>";
			    echo "<td nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>$".number_format( $_SESSION['totalsaldoconrate'],2)."</td>";
			}
		    }
	    }
	    
	    $_SESSION['impresion']=1;
		    if($_SESSION['impresion']== 1 ){
			if(isset($_POST['PrintEXCEL'])) {    
			    $moneda = 'USD';
			    echo "<tr><td style='background-color:#f2fcbd;' colspan=24 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
			    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;CARGOS SIN PAGOS EN DOLARES&nbsp;</td></tr>";
			    unset($_SESSION['totalsaldo']);
			    estadocuenta(-3,$CustomerID,1,1,$moneda,$fechaini,$fechafin,$db);
			    
			    echo "<tr><td colspan=7 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    $sumasaldo2=$_SESSION['totalsaldo'];
			    echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo,2)."</td>";
			    echo "<td nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>$".number_format( $_SESSION['totalsaldoconrate'],2)."</td>";
			}else{
			    $moneda = 'USD';
			    echo "<tr><td style='background-color:#f2fcbd;' colspan=24 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
			    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;CARGOS SIN PAGOS EN DOLARES&nbsp;</td></tr>";
			    unset($_SESSION['totalsaldo']);
			    estadocuenta(-3,$CustomerID,1,1,$moneda,$fechaini,$fechafin,$db);
			    
			    echo "<tr><td colspan=7 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    $sumasaldo2=$_SESSION['totalsaldo'];
			    echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo,2)."</td>";
			    echo "<td nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>$".number_format( $_SESSION['totalsaldoconrate'],2)."</td>";
			}
		    }
	    
	    //
	}else{
	    if($moneda == 'MXN'){
		    $_SESSION['impresion']=0;
		    if($_SESSION['impresion']== 0){
			if(isset($_POST['PrintEXCEL'])) {    
			    echo "<tr><td style='background-color:#F7F8E0;' colspan=24 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
			    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DOCUMENTOS EN PESOS sssss&nbsp;</td></tr>";
			    
			    estadocuenta(0,$CustomerID,1,0,$moneda,$fechaini,$fechafin,$db);
			    
			    echo "<tr><td colspan=7 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    //echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
			    //echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
			    //$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    $sumasaldo=abs($_SESSION['totalsaldo']);
			    echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo,2)."</td>";
			    echo "<td nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>$".number_format( $_SESSION['totalsaldoconrate'],2)."</td>";
			}else{
				// echo 'entra';
			    echo "<tr><td style='background-color:#F7F8E0;' colspan=24 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
			    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DOCUMENTOS EN PESOS&nbsp;</td></tr>";
			    
			    estadocuenta(0,$CustomerID,1,0,$moneda,$fechaini,$fechafin,$db);
			    
			    echo "<tr><td colspan=7 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    //echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
			    //echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
			    //$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    $sumasaldo=abs($_SESSION['totalsaldo']);
			    echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo,2)."</td>";
			    echo "<td nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>$".number_format( $_SESSION['totalsaldoconrate'],2)."</td>";
			}
		    }
	    }
	    if($moneda == 'MXN'){
		    $_SESSION['impresion']=1;
		    if($_SESSION['impresion']== 1 ){
			if(isset($_POST['PrintEXCEL'])) {
			    echo "<tr><td style='background-color:#f2fcbd;' colspan=24 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
			    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ABONOS NO APLICADOS EN PESOS&nbsp;</td></tr>";
			    unset($_SESSION['totalsaldo']);
			    $_SESSION['totalsaldoconrate']=0;
			    $moneda = 'MXN';
			    estadocuenta(-1,$CustomerID,1,1,$moneda,$fechaini,$fechafin,$db);
			    
			    echo "<tr><td colspan=7 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    //echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
			    //echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
			    //$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    $sumasaldo2=$_SESSION['totalsaldo'];
			    echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo2,2)."</td>";
			    echo "<td nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>$".number_format( $_SESSION['totalsaldoconrate'],2)."</td>";
			    $saldopesos=$sumasaldo2+$sumasaldo;
			    echo "<tr><td  colspan=10 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#8A0808'  align='center'>SALDO TOTAL EN PESOS </td><td class=number><font size=1>$".number_format($saldopesos,2)."</td></tr>";
			}else{
			    echo "<tr><td style='background-color:#f2fcbd;' colspan=24 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
			    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ABONOS NO APLICADOS EN PESOS&nbsp;</td></tr>";
			    unset($_SESSION['totalsaldo']);
			    $_SESSION['totalsaldoconrate']=0;
			    $moneda = 'MXN';
			    estadocuenta(-1,$CustomerID,1,1,$moneda,$fechaini,$fechafin,$db);
			    
			    echo "<tr><td colspan=7 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    //echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
			    //echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
			    //$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    $sumasaldo2=$_SESSION['totalsaldo'];
			     echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo2,2)."</td>";
			    echo "<td nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>$".number_format( $_SESSION['totalsaldoconrate'],2)."</td>";
			    $saldopesos=$sumasaldo2+$sumasaldo;
			    echo "<tr><td  colspan=10 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#8A0808'  align='center'>SALDO TOTAL EN PESOS </td><td class=number><font size=1>$".number_format($saldopesos,2)."</td></tr>";
			}
		    }
	    }
	    $_SESSION['totalsaldo']=0;
	    $_SESSION['totalsaldoconrate']=0;
	    $moneda = 'USD';
	    if($moneda = 'USD'){
	    //DOLARES
		    $_SESSION['impresion']=0;
		    if($_SESSION['impresion']== 0){
			if(isset($_POST['PrintEXCEL'])) {    
			    echo "<tr><td style='background-color:#F7F8E0;' colspan=24 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
			    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DOCUMENTOS EN DOLARES&nbsp;</td></tr>";
			    
			    estadocuenta(0,$CustomerID,1,0,$moneda,$fechaini,$fechafin,$db);
			    
			    echo "<tr><td colspan=7 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    //echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
			    //echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
			    //$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			     echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    $sumasaldo=abs($_SESSION['totalsaldo']);
			    echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo,2)."</td>";
			    echo "<td nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>$".number_format( $_SESSION['totalsaldoconrate'],2)."</td>";
			}else{
			    echo "<tr><td style='background-color:#F7F8E0;' colspan=24 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
			    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DOCUMENTOS EN DOLARES&nbsp;</td></tr>";
			    
			    estadocuenta(0,$CustomerID,1,0,$moneda,$fechaini,$fechafin,$db);
			    
			    echo "<tr><td colspan=7 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    //echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
			    //echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
			    //$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			     echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    $sumasaldo=abs($_SESSION['totalsaldo']);
			    echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo,2)."</td>";
			    echo "<td nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>$".number_format($_SESSION['totalsaldoconrate'],2)."</td>";
			}
		    }
		    $_SESSION['impresion']=1;
		    if($_SESSION['impresion']== 1 ){
			if(isset($_POST['PrintEXCEL'])) {
			    $moneda = 'USD';
			    echo "<tr><td style='background-color:#f2fcbd;' colspan=24 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
			    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ABONOS NO APLICADOS EN DOLARES&nbsp;</td></tr>";
			    unset($_SESSION['totalsaldo']);
			    estadocuenta(-1,$CustomerID,1,1,$moneda,$fechaini,$fechafin,$db);
			    
			    echo "<tr><td colspan=7 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    //echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
			    //echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
			    //$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			     echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    $sumasaldo2=$_SESSION['totalsaldo'];
			    echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo2,2)."</td>";
			    echo "<td nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>$".number_format( $_SESSION['totalsaldoconrate'],2)."</td>";
			}else{
			    $moneda = 'USD';
			    echo "<tr><td style='background-color:#f2fcbd;' colspan=24 nowrap 'text-align:center;'><b><font face='arial' size=1 color='#5b5202' align='center'>
			    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ABONOS NO APLICADOS EN DOLARES&nbsp;</td></tr>";
			    unset($_SESSION['totalsaldo']);
			    estadocuenta(-1,$CustomerID,1,1,$moneda,$fechaini,$fechafin,$db);
			    
			    echo "<tr><td colspan=7 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    //echo "<td  nowrap class=number><font size=1>$".number_format($_SESSION['totalcargos'],2)."</td>";
			    //echo "<td  nowrap class=number><font size=1>-$".number_format($_SESSION['sumapagos'],2)."</td>";
			    //$sumasaldo=($_SESSION['$totalcargos']-$_SESSION['$sumapagos']);
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			     echo "<td nowrap class=number><font size=1>&nbsp;</td>";
			    $sumasaldo2=$_SESSION['totalsaldo'];
			    echo "<td nowrap class=number><font size=1>$".number_format($sumasaldo2,2)."</td>";
			    echo "<td nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
			    echo "<td nowrap class=number><font size=1>$".number_format( $_SESSION['totalsaldoconrate'],2)."</td>";
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
	
     if(isset($_POST['PrintPDF'])){
        $razonsocial=$_POST['razonsocial'];
        $todaslasunidades=$_POST['todaslasunidades'];
        $sucursal=$_POST['sucursal'];
        $departamento=$_POST['departamento'];
		if (isset($_POST['solosaldo'])){
			$solosaldo=1;
		}
		//$solosaldo=$_POST['solosaldo'];
		$mabonoscargos=$_POST['mabonoscargos'];
		$FromDia=$_POST['FromDia'];
			$FromMes=$_POST['FromMes'];
		$ToDia=$_POST['ToDia'];
		$ToMes=$_POST['ToMes'];
		  
		/*	echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath ."/PDFReportCustomerInqueryV4.php?
		CustomerID=".$CustomerID."&razonsocial=".$razonsocial."&todaslasunidades=".$todaslasunidades."&sucursal=".$sucursal."&departamento=".$departamento."
		&solosaldo=".$solosaldo."&mabonoscargos=".$mabonoscargos."&FromDia=".$FromDia."&FromMes=".$FromMes."&FromYear=".$FromYear."
		&ToDia=".$ToDia."&ToMes=".$ToMes."&ToYear=".$ToYear."'>";
		*/
      }else
include('includes/footer.inc');
?>