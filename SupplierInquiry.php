<?php

/* $Revision: 1.21 $ */
// This is already linked from the menu
//echo "<a href='" . $rootpath . '/SelectSupplier.php?' . SID . "'>" . _('Back to Suppliers') . '</a><br>';

// always figure out the SQL required from the inputs available

if(!isset($_GET['SupplierID']) AND !isset($_SESSION['SupplierID'])){
	/*echo '<br>' . _('Para mostrar el estado de cuenta del proveedor necesita seleccionarlo') . 
		  "<br><div class='centre'>><a href='". $rootpath . "/SelectSupplier.php'>" . _('Select a Supplier to Inquire On') . '</a></div>';	  */

	require_once("SelectSupplier.php");
	die();

} else {
	include('includes/SQL_CommonFunctions.inc');

	$PageSecurity=2;

	include('includes/session.inc');
	$title = _('Supplier Inquiry');
	include('includes/header.inc');

	$funcion=178;
	include('includes/SecurityFunctions.inc');

	if (isset($_GET['SupplierID'])){
		$_SESSION['SupplierID'] = $_GET['SupplierID'];
	}
	$SupplierID = $_SESSION['SupplierID'];
}

if (isset($_GET['FromDate']) AND !isset($_POST['TransAfterDate'])){
	$_POST['TransAfterDate']=$_GET['FromDate'];
}

if (!isset($_POST['TransAfterDate']) OR !Is_Date($_POST['TransAfterDate'])) {

	#$_POST['TransAfterDate'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date("m")-12,Date("d"),Date("Y")));
	$_POST['TransAfterDate'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date("m"),1,Date("Y")));
}


if (isset($_GET['BeforeDate']) AND !isset($_POST['TransBeforeDate'])){
	$_POST['TransBeforeDate']=$_GET['BeforeDate'];
}

if (!isset($_POST['TransBeforeDate']) OR !Is_Date($_POST['TransBeforeDate'])) {

	$_POST['TransBeforeDate'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date("m"),31,Date("Y")));
}

if (isset($_POST['FechaPromesaPago'])) {
	$FechaPromesaPago = $_POST['FechaPromesaPago'];
}
else{
	$FechaPromesaPago = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date("m")-12,Date("d"),Date("Y")));
}

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
			unset($_POST['trandateant'.$z]);
			unset($_POST['trandate'.$z]);
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

$SQL = 'SELECT suppliers.suppname, 
		currencies.currency, 
		paymentterms.terms,
		SUM(supptrans.ovamount + supptrans.ovgst - supptrans.alloc) AS balance,
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
	     

		if (isset($_POST['solosaldo'])){
		  $SQL .= " AND (supptrans.ovamount + supptrans.ovgst - supptrans.alloc)>0";
		};


		
		 $SQL .= " GROUP BY suppliers.suppname,
      			currencies.currency,
      			paymentterms.terms,
      			paymentterms.daysbeforedue,
      			paymentterms.dayinfollowingmonth";


$ErrMsg = _('The supplier details could not be retrieved by the SQL because');
$DbgMsg = _('The SQL that failed was');

$SupplierResult = DB_query($SQL, $db, $ErrMsg, $DbgMsg);

if (DB_num_rows($SupplierResult) == 0){

	/*Because there is no balance - so just retrieve the header information about the Supplier - the choice is do one query to get the balance and transactions for those Suppliers who have a balance and two queries for those who don't have a balance OR always do two queries - I opted for the former */

	$NIL_BALANCE = True;

	$SQL = "SELECT suppliers.suppname, 
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

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/supplier.png" title="' . 
	_('Supplier') . '" alt="">' . ' ' . _('Proveedor') . ' : ' . $SupplierRecord['suppname'] . ' - (' . _('Todos los montos en ') . 
	  ' ' . $SupplierRecord['currency'] . ')<br><br>' . _('Condiciones de pago') . ': ' . $SupplierRecord['terms'] . '</p>';

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

echo "<table WIDTH=90% BORDER=1><tr><th>" . _('Saldo Total') . 
	  "</th><th>" . _('Actual') . 
	  "</th><th>" . _('Vencido') . 
	  "</th><th>" . $_SESSION['PastDueDays1'] . '-' . $_SESSION['PastDueDays2'] . 
	  ' ' . _('Días Vencido') . 
	  "</th><th>" . _('Más de') . ' ' . $_SESSION['PastDueDays2'] . ' ' . _('Días Vencido') . '</th></tr>';

echo '<tr><td style="text-align:right;">$ ' . number_format($SupplierRecord['balance'],2) . 
	  '</td><td style="text-align:right;">$ ' . number_format(($SupplierRecord['balance'] - $SupplierRecord['due']),2) . 
	  '</td><td style="text-align:right;">$ ' . number_format(($SupplierRecord['due']-$SupplierRecord['overdue1']),2) . 
	  '</td><td style="text-align:right;">$ ' . number_format(($SupplierRecord['overdue1']-$SupplierRecord['overdue2']) ,2) . 
	  '</td><td style="text-align:right;">$ ' . number_format($SupplierRecord['overdue2'],2) . '</td></tr></table>';

echo "<br><div class='centre'><form action='" . $_SERVER['PHP_SELF'] . "?" . SID . "' method=post>";

echo "<input type='checkbox' name='todaslasunidades' value=1 ";
	if (isset($_POST['todaslasunidades'])){
	  echo " checked";
	}

echo ">"._('Mostrar documentos de <b>TODAS</b> las Unidades de Negocio ') . "<br>";

echo "<input type='checkbox' name=solosaldo value=1 ";
	if (isset($_POST['solosaldo'])){
	  echo " checked";
	}

echo ">"._('Mostrar solo documentos con saldo ') . "<br><br>";

	echo _('Selecciona Unidad de Negocio:') .'<select name="tag">';

	/******************************************/
	//Pinta las unidades de negocio por usuario	
	$SQL = "SELECT t.tagref,t.tagdescription";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t ";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "' ORDER BY t.tagref";
		
	$result=DB_query($SQL,$db);
	
	echo '<option selected value=0>Todas a las que tengo acceso...';
	
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['tag']) and $_POST['tag']==$myrow['tagref']){
			echo '<option selected value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'];
		} else {
			echo '<option value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'];
		}
	}
	echo '</select><br><br>';

echo _('Mostrar todas las operaciones después del ') . ': ' ."<input type=text class='date' alt='".$_SESSION['DefaultDateFormat']."' name='TransAfterDate' VALUE='" . 
	  $_POST['TransAfterDate'] . "' MAXLENGTH =10 size=10> hasta".
     _(' y antes del ') . ': ' ."<input type=text class='date' alt='".$_SESSION['DefaultDateFormat']."' name='TransBeforeDate' VALUE='" . 
	  $_POST['TransBeforeDate'] . "' MAXLENGTH =10 size=10><br>
	  
	  <input type=submit name='RefreshInquiry' VALUE='" . _('Mostrar') . "'><br>";
echo '</div>';


if ( isset($_POST['RefreshInquiry']) ) {
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
	
	/*show a table of the transactions returned by the SQL */
	
	
	echo '<table cellspacing=0 border=1 bordercolor=DarkBlue cellpadding=5 colspan="7">';
	echo "<tr BGCOLOR =#800000>";
	echo "<th rowspan=2 style='background-color:#f2fcbd;'><b><font size=1 color='#5b5202'>" . _('Unidad<br>Negocio') ."</th>";
	echo "<th rowspan=2 style='background-color:#f2fcbd;'><b><font size=1 color='#5b5202'>Seleccionar</th>";
	echo "<th rowspan=2 style='background-color:#f2fcbd;'><b><font size=1 color='#5b5202'>" . _('#') ."</th>";
	echo "<th rowspan=2 style='background-color:#f2fcbd;'><b><font size=1 color='#5b5202'>" . _('Folio'). "</th>"; 
	echo "<th rowspan=2 style='background-color:#f2fcbd;'><b><font size=1 color='#5b5202'>" . _('Tipo') . "</th>";
	echo "<th rowspan=2 style='background-color:#f2fcbd;'><b><font size=1 color='#5b5202'>" . _('Referencia') . "</th>"; 
	echo "<th rowspan=2 style='background-color:#f2fcbd;'><b><font size=1 color='#5b5202'>" . _('Fecha') . '<br>'. _('Documento') . "</th>";
	echo "<th rowspan=2 style='background-color:#f2fcbd;'><b><font size=1 color='#5b5202'>" . _('Fecha') . '<br>'. _('Vencimiento') ."</th>";
	echo "<th rowspan=2 style='background-color:#f2fcbd;'><b><font size=1 color='#5b5202'>" . _('Fecha') . '<br>'. _('Promesa Pago') ."</th>";
	echo "<th colspan=2 style='background-color:#f2fcbd;'><b><font size=1 color='#5b5202'>" . _('Total') . "</th>";
	echo "<th rowspan=2 style='background-color:#f2fcbd;'><b><font size=1 color='#5b5202'>" . _('Pagos') . "</th>";
	echo "<th rowspan=2 style='background-color:#f2fcbd;'><b><font size=1 color='#5b5202'>" . _('Saldo') . "</th>";
	echo "<th rowspan=2 style='background-color:#f2fcbd;'><b><font size=1 color='#5b5202'>" . _('Comentarios') . "</th>";
	echo "<th rowspan=2 style='background-color:#f2fcbd;' colspan=3><b><font size=1 color='#5b5202'>" . _('Más Información') . "</th>";
	echo "</tr>";
	echo "<tr BGCOLOR =#800000>";
		//echo "<th colspan=7 style='background-color:#f2fcbd;'><b><font size=1 color='#5b5202'></th>";
		echo "<th style='background-color:#f2fcbd;'><b><font size=1 color='#5b5202'>" . _('Subtotal') . "</th>";
		echo "<th style='background-color:#f2fcbd;'><b><font size=1 color='#5b5202'>" . _('IVA') . "</th>";
	echo "</tr>";
	$j = 1;
	$k = 0; //row colour counter
	$cont=0;
	
	$sumatotal=0;
	$sumapagos=0;
	$sumasaldo=0;
	$sumatotalp=0;
	while ($myrow=DB_fetch_array($TransResult)) {
		$cont=$cont+1;
		if ($myrow['hold'] == 0 AND $myrow['settled'] == 0){
			$HoldValue = _('Hold');
		} elseif ($myrow['settled'] == 1) {
			$HoldValue = '';
		}else {
			$HoldValue = _('Release');
		}
		
		$tagref=$myrow['tagref'];
		$idtrans=$myrow['id'];
		$typetrans=$myrow['type'];
		echo "<tr>";
		
		$sumatotal = doubleval($sumatotal) + doubleval($myrow['ovamount']);
		$sumapagos = doubleval($sumapagos) + doubleval($myrow['ovgst']);
		$sumatotalp= doubleval($sumatotalp) + doubleval($myrow['allocated']);
		$sumasaldo = doubleval($sumasaldo) + doubleval($myrow['totalamount']-$myrow['allocated']);	
		
		$FormatedTranDate = ConvertSQLDate($myrow['trandate']);
		
		if (ConvertSQLDate($myrow['promisedate'])=="00/00/0000")
		{
			$FecPromesa= "&nbsp;";
			$bcolor = "";
		}
		else{
			$FecPromesa = ConvertSQLDate($myrow['promisedate']);
			$bcolor = "#E6FEED";
		}
		if ($myrow['type'] == 22){
			if (($_SESSION['EnvioXSA'] != 0)){
			$liga = $rootpath . "/PrintCheque.php?ChequeNum=&TransNo=" . $myrow['transno'] . "&Currency=" . $myrow['currcode'] . "&SuppName=" . $myrow['suppname'];
			
			}else{
				$liga = GetUrlToPrint($myrow['tagref'],22,$db);
				$liga = $rootpath . "/" . $liga . "&ChequeNum=&TransNo=" . $myrow['transno'] . "&Currency=" . $myrow['currcode'] . "&SuppName=" . $myrow['suppname'];
			}
			$reimprimir = "<a TARGET='_blank' href='" . $liga . "'><font size=1>" . _('ReImprimir') . "</a>";
		}else{
			$reimprimir = "";
		}
		
		
		
		if (Havepermission($_SESSION['UserID'],402, $db)==1){
			echo "<td style='text-align:center;'>";
			$sql="SELECT t.tagref, t.tagdescription
				 FROM tags t, sec_unegsxuser uxu
				 WHERE t.tagref=uxu.tagref
				      AND uxu.userid='".$_SESSION['UserID']."'
				ORDER BY tagdescription";
			    $resultTags = DB_query($sql,$db,'','');
			    echo "<select name='UnidNeg".$cont."' style='font-size:7pt;'>";
			    while ($xmyrow=DB_fetch_array($resultTags))
			    {
				if ($xmyrow['tagref'] == $tagref)
				{
				     echo "<option selected Value='" . $xmyrow['tagref'] . "'>" . $xmyrow['tagdescription'] .'</option>';
				}
				else
				{
				     echo "<option Value='" . $xmyrow['tagref'] . "'>" . $xmyrow['tagdescription'] .'</option>';
				}
			    }
			    echo '</select>';
			    echo "<br><input type=hidden name='uniant".$cont."' class='number' size='8' value='".$tagref."'>";
			echo "</td>";
		}else{
			echo "<td style='text-align:center'><font size=1>".$myrow['tagdescription']."</td>";
		}
		
		echo "<td style='text-align:center'><input type=checkbox name='doctosel".$cont."'><input type='hidden' name='docto".$cont."' value='".$myrow['transno']."'></td>";
		echo "<td style='text-align:center'><font size=1>".$cont."</td>";
		echo "<td><font size=1>".$myrow['transno'].'('. $myrow['folio'] .')'."</td>";
		echo "<td nowrap><font size=1>".$myrow['typename']."</td>";
		echo "<td nowrap><font size=1>".$myrow['suppreference']."</td>";
		
		if (Havepermission($_SESSION['UserID'],402, $db)==1){
			echo "<td><font size=1>
				<input type=text name='trandate".$cont."' class='date' alt='d/m/Y' size='12' value='".ConvertSQLDate($myrow['trandate'])."'>
				<input type=hidden name='trandateant".$cont."'  size='12' value='".ConvertSQLDate($myrow['trandate'])."'>
				
			</td>";
			
		}else{
			echo "<td><font size=1>".ConvertSQLDate($myrow['trandate'])."</td>";
		}
		
		echo "<td><font size=1>".ConvertSQLDate($myrow['duedate'])."</td>";
		
		echo "<td style='background-color:".$bcolor.";'><font size=1>".$FecPromesa."</td>";
		if (Havepermission($_SESSION['UserID'],402, $db)==1){
			echo "<td nowrap style='text-align:right;'>
				<input type=text name='ovamount".$cont."' class='number' size='8' value='".number_format($myrow['ovamount'],2,'.','')."'>
				<input type=hidden name='ovamountant".$cont."' class='number' size='8' value='".number_format($myrow['ovamount'],2,'.','')."'>
			</td>";
			echo "<td nowrap style='text-align:right;'><font size=1>$ 
				<input type=text name='ovgst".$cont."' class='number' size='8' value='".number_format($myrow['ovgst'],2,'.','')."'>
				<input type=hidden name='ovgstant".$cont."' class='number' size='8' value='".number_format($myrow['ovgst'],2,'.','')."'>
			</td>";
		}else{
			echo "<td nowrap style='text-align:right;'><font size=1>$ ".number_format($myrow['ovamount'],2)."</td>";
			echo "<td nowrap style='text-align:right;'><font size=1>$ ".number_format($myrow['ovgst'],2)."</td>";
		}
		if (Havepermission($_SESSION['UserID'],402, $db)==1){
			
			echo "<td nowrap style='text-align:right;'><font size=1>$
				<input type=text name='pagos".$cont."' class='number' size='8' value='".number_format($myrow['allocated'],2,'.','')."'>
				<input type=hidden name='pagosant".$cont."' class='number' size='8' value='".number_format($myrow['allocated'],2,'.','')."'>
				<input type=hidden name='idtrans".$cont."'  value='".intval($idtrans)."'>
				<input type=hidden name='typetrans".$cont."'  value='".$typetrans."'><br>
				<input type=submit class='peque' name='ModifyPay' VALUE='" . _('Modificar') . "'>
			</td>";
		}else{
			echo "<td nowrap style='text-align:right;'><font size=1>$ ".number_format($myrow['allocated'],2)."</td>";
		}
		echo "<td nowrap style='text-align:right;background-color:#FEFFE1;'><font size=1>$ ".number_format($myrow['totalamount']-$myrow['allocated'],2)."</td>";
		
		echo "<td align=left><font size=1>". (1/$myrow['rate']) . "<br>" . $myrow['transtext']."&nbsp;</td>";
		
		$reimprimirconta = "<a TARGET='_blank' href='" . $rootpath. "/PDFGLTransInquiry.php?". SID. "&TypeID=".$myrow['type']."&TransNo=".$myrow['transno'] . "'><img src='".$rootpath."/css/".$theme."/images/printer.png' title='" . _('Imprimir Contabilidad') . "' alt=''></a>";		
		
		if ($myrow['type'] == 20){
			if ($_SESSION['CompanyRecord']['gllink_creditors'] == True){
				if ($myrow['totalamount'] - $myrow['allocated'] == 0){
					echo "<td nowrap>".$reimprimirconta."<a TARGET='_blank' href='".$rootpath."/GLTransInquiryV2.php?".SID."&TypeID=".$myrow['type']."&TransNo=".$myrow['transno']."'><font size=1>" . _('Ver Contabilidad') . "</a></td>";
					
				}else{
					
					//echo "<td nowrap>".$reimprimirconta."<a TARGET='_blank' href='".$rootpath."/GLTransInquiry.php?".SID."&TypeID=".$myrow['type']."&TransNo=".$myrow['transno']."'><font size=1>" . _('Ver Contabilidad') . "</a></td>";
					
					echo "<td nowrap>".$reimprimirconta."<a href='".$_SERVER['PHP_SELF']."?".SID."&HoldType=".$myrow['type']."&HoldTrans=".$myrow['transno']."&HoldStatus=".$HoldValue."&FromDate=".$_POST['TransAfterDate']."'><font size=1>".$HoldValue."</a></td>";
				}
			}else{
				if ($myrow['totalamount'] - $myrow['allocated'] == 0){
					
					echo "<td nowrap>".$reimprimirconta."<a TARGET='_blank' href='".$rootpath."/GLTransInquiry.php?".SID."&TypeID=".$myrow['type']."&TransNo=".$myrow['transno']."'><font size=1>" . _('Ver Contabilidad') . "</a></td>";
				}else{
					echo "<td nowrap>".$reimprimirconta."<a href='".$_SERVER['PHP_SELF']."?".SID."&HoldType=".$myrow['type']."&HoldTrans=".$myrow['transno']."&HoldStatus=".$HoldValue."&FromDate=".$_POST['TransAfterDate']."'><font size=1>".$HoldValue."</a></td>";
				}
			}
			echo "<td><a href='".$_SERVER['PHP_SELF']."/PaymentAllocations.php?".SID."SuppID=".$myrow['supplierno']."&InvID=".$myrow['suppreference']."'><font size=1>" . _('Ver Pagos') . "</a></td>";
		}else{
			if ($_SESSION['CompanyRecord']['gllink_creditors'] == True){
				echo "<td nowrap>".$reimprimirconta."<a TARGET='_blank' href='".$rootpath."/GLTransInquiry.php?".SID."&TypeID=".$myrow['type']."&TransNo=".$myrow['transno']."'><font size=1>" . _('Ver Contabilidad') . "</a></td>";
				echo "<td nowrap><a href='".$rootpath."/PaymentAllocations.php?".SID."SuppID=".$myrow['supplierno']."&InvID=".$myrow['suppreference']."'><font size=1>" . _('Ver Pagos') . "</a></td>";
			}else{
				echo "<td nowrap><a href='".$rootpath."/PaymentAllocations.php?".SID."SuppID=".$myrow['supplierno']."&InvID=".$myrow['suppreference']."'><font size=1>" . _('Ver Pagos') . "</a></td>";
				
			}
			
			echo "<td align=left><font size=1>".$reimprimir."</td>";	
		}
		
							  
		echo " </tr>";
	//end of page full new headings if
	
	}
	
		echo "<td colspan=9 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
		echo "<td nowrap class=number><font size=1>".number_format($sumatotal,2)."</td>";
		echo "<td nowrap class=number><font size=1>".number_format($sumapagos,2)."</td>";
		echo "<td nowrap class=number><font size=1>".number_format($sumatotalp,2)."</td>";	
		echo "<td nowrap class=number><font size=1>".number_format($sumasaldo,2)."</td>";
		
		//echo "<td colspan=8 nowrap style='text-align:center;'><font size=1>&nbsp;</td>";
		echo "<td colspan=4 nowrap style='text-align:center;'><input type=hidden name='cuenta'  value='".intval($cont)."'><font size=1>&nbsp;</td>";
	//end of while loop
	
	echo '</table>';
} else {
	echo '<br><br><div align=center>SELECCIONAR EL PERIODO DE FECHAS Y LA UNIDAD DE NEGOCIOS PARA MOSTRAR LAS TRANSACCIONES...<br></div>';
}

echo '<input type="hidden" name="TotalDoctos" value="'.$cont.'">';

echo '<br><br><br>';
echo "<div align=center><font size=2><b>Fecha Promesa de Pago : </b><input type=text class='date' alt='".$_SESSION['DefaultDateFormat']."' name='FechaPromesaPago' VALUE='" . 
	  $FechaPromesaPago . "' MAXLENGTH =10 size=10>";
echo '&nbsp;<input type="submit" name="procesar" value="Procesar">';
echo '<br><br><input type="submit" name="quitar" style="background-color:#FFE8DE;" value="Quitar Promesa de Pago a Documentos Seleccionados">';
echo '<br><br><br>';
echo '</form>';

include('includes/footer.inc');
?>
