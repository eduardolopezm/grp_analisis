<?php

/**
 * Declaracion de Funciones y Procedimientos para la pagina de autorizacion de pagos
 */

/**
 * 
 * @param unknown $fechaini
 * @param unknown $fechafin
 * @param unknown $razonsocial
 * @param unknown $unidadnegocio
 * @param unknown $tipodocumento
 * @param unknown $folio
 * @param unknown $nombreproveedor
 * @param unknown $codigoproveedor
 * @param Array $lista_estatus - Listado de estatus seleccionados
 * @return resultado de la consulta
 */
function TraeCuentasPorPagar($fechaini, $fechafin, $razonsocial, $unidadnegocio, $tipodocumento, $folio, $nombreproveedor, $codigoproveedor, $lista_estatus)
{
	global $db;
	
	$ErrMsg = _('Los recursos no se pudieron recuperar porque');  
	$DbgMsg = _('El SQL utilizado para recuperar los recursos fue'); 
	
	$cadena_in= "";
	foreach ($lista_estatus as $estatus) {
		$cadena_in.= "'".$estatus."',";
	}
	$cadena_in.= "'99'";
	
	$consulta = "Select tags.tagdescription as name, systypescat.typename, supptrans.supplierno,
		       suppliers.suppname, suppliers.taxid, suppliers.lastpaiddate, lastpaid,
		       supptrans.trandate, supptrans.origtrandate, supptrans.id,
		       supptrans.duedate, supptrans.promisedate,
		       supptrans.ovamount/supptrans.rate as ovamount,
		       supptrans.ovgst/supptrans.rate as ovgst, supptrans.alloc/supptrans.rate as alloc,
		       suppliers.bankact,suppliers.bankpartics, supptrans.suppreference, supptrans.transno,
		       supptrans.hold,
		       CASE WHEN supptrans.promisedate = '0000-00-00' THEN -1 ELSE DATEDIFF(supptrans.promisedate,supptrans.duedate) END as atraso,
			CASE WHEN supptrans.duedate < '".$fechaini."' THEN 0
			ELSE CASE WHEN supptrans.duedate >= '".$fechaini."' and supptrans.duedate <= '".$fechafin."' THEN 1
			ELSE 2 END END as EstadoVencimiento,
			supptrans.tagref,
			supptrans.rate as rate,
			supptrans.diffonexch as diffonexch, autstatus.nombre as nombre_estatus, supptrans.currcode
		From supptrans JOIN systypescat ON supptrans.type = systypescat.typeid
		      JOIN tags ON supptrans.tagref = tags.tagref
		      JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
		      JOIN areas ON tags.areacode = areas.areacode
		      JOIN regions ON areas.regioncode = regions.regioncode
		      JOIN suppliers ON supptrans.supplierno = suppliers.supplierid
		      Inner Join autorizacionpagos_estatus autstatus On supptrans.hold= autstatus.idstatus
		Where (abs((supptrans.ovamount + supptrans.ovgst) - supptrans.alloc) > .01) 
		      and supptrans.type in ('470','20','34','117','121')";
	
	// Complementos de consulta con validaciones de acuerdo a los filtros de busqueda
	if (!empty($codigoproveedor)) {
		$consulta.= " and suppliers.supplierid = '".$codigoproveedor."'";
	} elseif (!empty($nombreproveedor)){
		$consulta.= " and suppliers.suppname like '%".$nombreproveedor."%'";
	} else {
		$consulta.= " and supptrans.duedate >= '".$fechaini."' and supptrans.duedate <= '".$fechafin."'";
	}
	
	if (!empty($razonsocial) && $razonsocial != "-1") {
		$consulta.= " and tags.legalid = '".$razonsocial."'";
	}
	
	if (!empty($unidadnegocio) && $unidadnegocio != "-1") {
		$consulta.= " and supptrans.tagref = '".$unidadnegocio."'";
	}
	
	if ($tipodocumento == 1) {
		$consulta.= " and supptrans.ovamount > 0 ";
	} else {
		$consulta.= " and supptrans.ovamount < 0 ";
	}
	
	if (!empty($folio)) {
		$consulta.= " and supptrans.suppreference = '".$folio."'";
	}
		      
	$consulta.= " and supptrans.hold In (".$cadena_in.")";
	$consulta.= " Order by EstadoVencimiento, atraso desc, supptrans.duedate asc, (supptrans.ovamount+supptrans.ovgst-supptrans.alloc/supptrans.rate) desc";
	
	$resultado= DB_query($consulta, $db, $ErrMsg, $DbgMsg);
	 //echo "sql:".$consulta;
	
	return $resultado;
}


// Funcion para el proceso que cambia de estatus los elementos
/**
 * 
 * @param Array $listapago - Lista de elementos que va a modificar
 * @param integer $estatus - numero de estatus a modificar
 */
function AutorizaPago($listapago, $estatus)
{
	global $db;
	
	// Validar si el parametro es un arreglo de datos
	if (!is_array($listapago)) {
		prnMsg("La lista de pago no es un arreglo, verifique el codigo !!!", "info");
		return false;
	}
	
	$transaccion= DB_Txn_Begin($db);  // inicio del bloque de transacciones
	
	// Ciclo que recorre los elementos seleccionados
	foreach ($listapago as $movimiento_id)
	{
		$codproveedor = $_POST['supplierid'][$movimiento_id];
		$saldo = $_POST['saldo'][$movimiento_id];
		$unidadnegocio = $_POST['tagref'][$movimiento_id];
		$ratefactura = $_POST['rate'][$movimiento_id];
		$idfactura = $_POST['idfactura'][$movimiento_id];
		$rfcproveedor = $_POST['taxid'][$movimiento_id];
		$fechamovimiento= date('Y-m-d');
		$tipomovimiento= 124;   // tipo de movimiento para la autorizacion del pago
		
		// Actualizar tabla de proveedores con el estatus nuevo
		$sql = "UPDATE supptrans
				SET hold = ".$estatus."
				WHERE id = '". $movimiento_id ."'";
	
		$transaccion = DB_query($sql, $db);
		
		if ($estatus == "2") 
		{
			$transno = GetNextTransNo($tipomovimiento, $db);  // Generacion de folio interno
			$periodo = GetPeriod($fechamovimiento, $db, $unidadnegocio);  // numero del periodo contable
			
			$cuentacargo= $_SESSION['CompanyRecord']['gllink_presupuestalegresodevengado'];  // cuenta contable de cargo devengado
			$cuentaabono= $_SESSION['CompanyRecord']['gllink_presupuestalegresoejercido'];  // cuenta contable de abono ejercido
			$narrative = $codproveedor . "-" . "Cambio de Estatus Autorizacion@" . ($saldo);
			
			// Insertar el movimiento contable de Cargo
			$ISQL = Insert_Gltrans($tipomovimiento, $transno, $fechamovimiento, $periodo, $cuentacargo, $narrative, 
						$unidadnegocio, $_SESSION['UserID'], $ratefactura, '', '', '', 0, 0, '', 0, $codproveedor, 0, 
						$saldo, $db, $transno, 'CUENTA CONTABLE', 0, '', $rfcproveedor, '');
			
			$transaccion = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
			
			// Insertar el movimiento contable de Abono
			$ISQL = Insert_Gltrans($tipomovimiento, $transno, $fechamovimiento, $periodo, $cuentaabono, $narrative,
					$unidadnegocio, $_SESSION['UserID'], $ratefactura, '', '', '', 0, 0, '', 0, $codproveedor, 0,
					($saldo*-1), $db, $transno, 'CUENTA CONTABLE', 0, '', $rfcproveedor, '');
				
			$transaccion = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
		}
		prnMsg(_("Se cambio el movimiento ").$movimiento_id._(" al Estatus ").$estatus, "info");
	}
	
	$transaccion= DB_Txn_Commit($db);
	
	return true;
}


// Funcion para el proceso que genera el cheque de pago
/**
 * 
 * @param Array $listaelementos
 * @param Date $fechachequeperiod
 * @param string $cuentabanco
 * @param string $tipopago
 * @param Array $listamoneda
 * @param Array $listaestatus
 * @param string $bancodestino
 * @param string $cuentadestino
 */
function GeneraChequePago($listaelementos, $fechachequeperiod, $cuentabanco, $tipopago, $listamoneda, $listaestatus, $bancodestino= "", $cuentadestino= "") 
{
	global $db;
	
	$PeriodNo = GetPeriod($fechachequeperiod,$db);
	
	$bankaccount = $cuentabanco;
	$Tipopago = $tipopago;
	$ChequeNum = "";
	$moneda= 'MXN';
	$fechacheque= $fechachequeperiod;
		
	/*Get the bank account currency and set that too */
	$ErrMsg = _('No pude obtener la moneda de la cuenta del banco seleccionada');
	$result = DB_query("SELECT currcode FROM bankaccounts WHERE accountcode = '" . $bankaccount . "'",$db,$ErrMsg);
	$myrow = DB_fetch_row($result);
	$monedabanco = $myrow[0];
	

	/*********FUNCIONES PARA OBTENER RATE****/
	/*Get the exchange rate between the functional currency and the payment currency*/
	$result = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $monedabanco . "'",$db);
	$myrow = DB_fetch_row($result);
	$tableExRate = $myrow[0]; //this is the rate of exchange between the functional currency and the payment currency
		
	if ($moneda == $monedabanco){
		$_POST['ExRate']=1;
		$SuggestedExRate=1;
	}
	
	if ($monedabanco==$_SESSION['CompanyRecord']['currencydefault']){
		$_POST['FunctionalExRate']=1;
		$SuggestedFunctionalExRate =1;
		$SuggestedExRate = $tableExRate;
	} else {
		/*Get suggested FunctionalExRate */
		//echo "<br>1.- " . "SELECT rate FROM currencies WHERE currabrev='" . $monedabanco . "'";
		$result = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $monedabanco . "'",$db);
		$myrow = DB_fetch_row($result);
		$SuggestedFunctionalExRate = $myrow[0];
			
		/*Get the exchange rate between the functional currency and the payment currency*/
		//echo "<br>2.- " . "select rate FROM currencies WHERE currabrev='" . $moneda . "'";
		$result = DB_query("select rate FROM currencies WHERE currabrev='" . $moneda . "'",$db);
		$myrow = DB_fetch_row($result);
		$tableExRate = $myrow[0]; //this is the rate of exchange between the functional currency and the payment currency
		/*Calculate cross rate to suggest appropriate exchange rate between payment currency and account currency */
		$SuggestedExRate = $tableExRate/$SuggestedFunctionalExRate;
	}
	
	if ($monedabanco != $moneda AND isset($monedabanco)){
		if ($_POST['ExRate']==1 AND isset($SuggestedExRate)){
			$_POST['ExRate'] = $SuggestedExRate;
		}
	}
		
	if ($monedabanco != $_SESSION['CompanyRecord']['currencydefault'] AND isset($monedabanco)){
		if ($_POST['FunctionalExRate']==1 AND isset($SuggestedFunctionalExRate)){
			$_POST['FunctionalExRate'] = $SuggestedFunctionalExRate;
		}
	}
		
	$inisupplierid = "";
	$initransno = "";
	$prevdiffonexch = 0;
	
	//echo "<br>" . count($_POST['selMovimiento']);
	for ($i=0;$i<=count($listaelementos)-1; $i++) 
	{
		$umovto = $listaelementos[$i];
		$estatus = $listaestatus[$umovto];
		
		// validar si el estatus es autorizado
		if ($estatus==2)
		{
			$saldo = $_POST['saldo'][$umovto];
			$tagref = $_POST['tagref'][$umovto];
			$supplierid = $_POST['supplierid'][$umovto];
			$ratefactura = $_POST['rate'][$umovto];
			$idfactura = $_POST['idfactura'][$umovto];
			$diffonexch = $_POST['diffonexch'][$umovto];
			$rfcproveedor = $_POST['taxid'][$umovto];
			$inisupplierid = $supplierid;
			$saldofactura =  ($saldo / $ratefactura);
			$bankaccount = $cuentabanco;
			$narrative  = "Pago de factura";
			$Transtype = 22;
			
			$transaccion= DB_Txn_Begin($db);  // INICIO DE BLOQUE DE TRANSACCIONES
			
			$PeriodNo = GetPeriod($fechachequeperiod,$db,$tagref);
			$TransNo = GetNextTransNo(22, $db);
			$initransno = $TransNo;
			
			if ($tipopago == 'Cheque'){
					$ChequeNum = GetNextChequeNo($cuentabanco, $db);
			} else {
				$ChequeNum = $tipopago;
			}
			
			/* CREAR UN REGISTRO DEL PAGO DE PROVEEDOR           */
			/* Create a SuppTrans entry for the supplier payment */
			$ratecheque = ($_POST['ExRate']/$_POST['FunctionalExRate']);
			$saldocheque = ($saldo / ($_POST['ExRate']/$_POST['FunctionalExRate']));
			
			$SQL = "INSERT INTO supptrans (transno,
								type,
								supplierno,
								trandate,
								suppreference,
								rate,
								ovamount,
								 transtext,
								tagref,
								origtrandate,
								ref1,
								alloc,
								settled
								) ";
			$SQL = $SQL . 'VALUES (' . $TransNo . ",
								22,
								'" . $supplierid . "',
								'" . $fechacheque . "',
								'" . $ChequeNum . "',
								'" . ($_POST['ExRate']/$_POST['FunctionalExRate']) . "',
								" . (-1)*$saldo . ",
								'" . $narrative . "',
								" . $tagref . ",
								now(),
								'" . $ChequeNum . "',
								" . (-1)*$saldo . ",
								" . "1" . "
							)";
			
			$ErrMsg =  _('No pude insertar transaccion de pago contra el proveedor porque');
			$DbgMsg = _('No pude insertar transaccion de pago contra el proveedor usando el SQL');
			
			$transaccion = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			
			$prevdiffonexch = $prevdiffonexch +  ($_POST['ExRate']/$_POST['FunctionalExRate']);
			$idcheque = DB_Last_Insert_ID($db,'supptrans','id');
		
			/* ACTUALIZA EL REGISTRO DEL PROVEEDOR CON EL ULTIMO PAGO Y LA FECHA DE PAGO   */
			/*Update the supplier master with the date and amount of the last payment made */
			$SQL = "UPDATE suppliers 
					SET lastpaiddate = '" . $fechacheque . "',
						lastpaid=" . $saldo ."
					WHERE suppliers.supplierid='" . $supplierid . "'";
		
			$ErrMsg = _('No pude actualizar el registro del proveedor con la ultima fecha y monteo de pago porque');
			$DbgMsg = _('No pude actualizar el registro del proveedor con la ultima fecha y monteo de pago utilizando el SQL');
			$transaccion = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				
			$SQL = "INSERT INTO suppallocs (datealloc, amt, transid_allocfrom, transid_allocto)
					VALUES ('" . $fechacheque . "',
							" . $saldo . ',
							' . $idcheque . ',
							' . $idfactura . ')';
		
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' .  _('The supplier allocation record for') . ' ' . $AllocnItem->TransType . ' ' .  $AllocnItem->TypeNo . ' ' ._('could not be inserted because');
			$DbgMsg = _('The following SQL to insert the allocation record was used');
			$transaccion=DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
			
			$SQL = 'UPDATE supptrans
					SET diffonexch=' . $diffonexch . ',
						alloc = ' .  $saldo . ',
						settled = ' . "1" . '
					WHERE id = ' . $idfactura;
		
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction record could not be modified for the allocation against it because');
			$DbgMsg = _('The following SQL to update the debtor transaction record was used');
			$transaccion = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
		
			
			if ($_SESSION['CompanyRecord']['gllink_creditors']==1)
			{
				//OBTENGO NUMERO DE CUENTA X TIPO DE PROVEEDOR
				if ($inisupplierid != '')
				{
					$tipoproveedor = ExtractTypeSupplier($inisupplierid,$db);
					$ctaxtipoproveedor = SupplierAccount($tipoproveedor,"gl_accountsreceivable",$db);
				}else{
					$ctaxtipoproveedor = $_SESSION['CompanyRecord']['creditorsact'];
				}
					
				$ErrMsg = _('Cannot insert a GL transaction for the creditors account debit because');
				$DbgMsg = _('Cannot insert a GL transaction for the creditors account debit using the SQL');
				
				// Insertar Los movimientos Contables de pago
				// Insertar movimiento de Cargo
				$Narrative = $inisupplierid . "-" . "PAGO DE FACTURAS@" . ($saldofactura);
				$ISQL = Insert_Gltrans($Transtype,$initransno,FormatDateForSQL($fechacheque),$PeriodNo, $ctaxtipoproveedor,$Narrative, $tagref ,$_SESSION['UserID'],
						$_POST['FunctionalExRate'],'','','',0,0,'',0,$inisupplierid,0, $saldofactura,$db,$ChequeNum,'CUENTA CONTABLE', 0, $bancodestino, $rfcproveedor, $cuentadestino);
				
				$transaccion = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
				
				// Insertar movimiento de Abono
				$Narrative = $inisupplierid . "-" . "PAGO DE FACTURAS@" . (-$saldofactura);
				$ISQL = Insert_Gltrans($Transtype,$initransno,FormatDateForSQL($fechacheque),$PeriodNo, $ctaxtipoproveedor,$Narrative, $tagref ,$_SESSION['UserID'],
						$_POST['FunctionalExRate'],'','','',0,0,'',0,$inisupplierid,0, -1*($saldocheque), $db,$ChequeNum,'CUENTA CONTABLE', 0, $bancodestino, $rfcproveedor, $cuentadestino);
				
				$transaccion = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
				// FIN Insertar Los movimientos Contables de pago
				
				
				// Genera los movimientos Contables de Presupuesto
				$cuentacargo= $_SESSION['CompanyRecord']['gllink_presupuestalegresoejercido'];  // cuenta contable de cargo ejercido
				$cuentaabono= $_SESSION['CompanyRecord']['gllink_presupuestalegresopagado'];  // cuenta contable de abono pagoado
				$narrative = $inisupplierid . "-" . "PAGO DE FACTURAS@" . ($saldofactura);
					
				// Insertar el movimiento contable de Cargo
				$ISQL = Insert_Gltrans($Transtype, $initransno, FormatDateForSQL($fechacheque), $PeriodNo, $cuentacargo, $Narrative,
						$tagref, $_SESSION['UserID'], $_POST['FunctionalExRate'], '', '', '', 0, 0, '', 0, $inisupplierid, 0,
						$saldofactura, $db, $ChequeNum, 'CUENTA CONTABLE', 0, $bancodestino, $rfcproveedor, $cuentadestino);
				
				$transaccion = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
					
				// Insertar el movimiento contable de Abono
				$ISQL = Insert_Gltrans($Transtype, $initransno, FormatDateForSQL($fechacheque), $PeriodNo, $cuentaabono, $Narrative,
						$tagref, $_SESSION['UserID'], $_POST['FunctionalExRate'], '', '', '', 0, 0, '', 0, $inisupplierid, 0,
						($saldocheque*-1), $db, $ChequeNum, 'CUENTA CONTABLE', 0, $bancodestino, $rfcproveedor, $cuentadestino);
				
				$transaccion = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
				// Fin los movimientos Contables de Presupuesto
				
		
				/*********INICIO PERDIDA CAMBIARIA***************/
				$utilidadperdida = $saldofactura - $saldocheque;
				
				if (abs($utilidadperdida) > .1)
				{
					if ($utilidadperdida < 0)
					{
						$perdida = abs($utilidadperdida);
						$ctautilidadperdida = $_SESSION['CompanyRecord']['purchasesexchangediffact'];
						$Narrative = $supplierid . "@UTIL/PERD CAMBIARIA@" . $perdida;
						
						/*
						$SQL_up = 'INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount,
											tag,
											chequeno
											)
										VALUES (22,
										 ' . $initransno . ",
										 '" . $fechacheque . "',
										 " . $PeriodNo . ',
										 ' . $ctautilidadperdida . ",
										 '". $reference . "',
										 " . $perdida . ",
										 '" . $tagref . "',
										 '" . $ChequeNum . "')"; */
		
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' .
							 _('The GL entry for the difference on exchange arising out of this allocation could not be inserted because');
						$DbgMsg = _('The following SQL to insert the GLTrans record was used');
						
						$ISQL = Insert_Gltrans($Transtype,$initransno,FormatDateForSQL($fechacheque),$PeriodNo, $ctautilidadperdida,$Narrative, $tagref ,$_SESSION['UserID'],
								$_POST['FunctionalExRate'],'','','',0,0,'',0,$inisupplierid,0, $perdida, $db,$ChequeNum,'CUENTA CONTABLE', 0, $bancodestino, $rfcproveedor, $cuentadestino);
						
						$transaccion = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
					} else {
				 		$utilidad = abs($utilidadperdida);
				 		$ctautilidadperdida = $_SESSION['CompanyRecord']['gllink_purchasesexchangediffactutil'];
				 		$Narrative = $supplierid . "@UTIL/PERD CAMBIARIA@" . $utilidad;
						
						/*
						$SQL_up = 'INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount,
											tag,
											chequeno
											)
										VALUES (22,
											' . $initransno . ",
										 	'" . $fechacheque . "',
											" . $PeriodNo . ',
											' . $ctautilidadperdida . ",
											'". $reference . "',
											" . $utilidad . ",
											'" . $tagref . "',
											'" . $ChequeNum . "')";*/
		
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' .
								_('The GL entry for the difference on exchange arising out of this allocation could not be inserted because');
						$DbgMsg = _('The following SQL to insert the GLTrans record was used');
						
						$ISQL = Insert_Gltrans($Transtype,$initransno,FormatDateForSQL($fechacheque),$PeriodNo, $ctautilidadperdida,$Narrative, $tagref ,$_SESSION['UserID'],
								$_POST['FunctionalExRate'],'','','',0,0,'',0,$inisupplierid,0, $utilidad, $db,$ChequeNum,'CUENTA CONTABLE', 0, $bancodestino, $rfcproveedor, $cuentadestino);
						
						$transaccion = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
					}
				}
				/********FIN PERDIDA CAMBIARIA***************/
		
				/**************************************************/
				/*MOVIMIENTOS DE IVA POR PAGAR A PAGADO*/
				$saldo = $_POST['saldo'][$umovto];
				$taxrate = .16;
				$CreditorTotal = ($saldo/$_POST['ExRate'])/$_POST['FunctionalExRate'];
				
				$SQL = 'select * from taxauthorities where taxid=1';
				$result2 = DB_query($SQL,$db);
				
				if ($TaxAccs = DB_fetch_array($result2))
				{
					$taximpuesto=($CreditorTotal / (1 + $taxrate));
					$taximpuesto=$CreditorTotal-$taximpuesto;
					$Narrative = $supplierid . "@IMPUESTOA@" . (($taximpuesto*-1));
					/*
					$SQL="INSERT INTO gltrans ( type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount,
									tag,
									chequeno) ";
					$SQL=$SQL . "VALUES (
									22,
									" . $initransno . ",
									'" . $fechacheque . "',
									" . $PeriodNo . ",
									" . $TaxAccs['purchtaxglaccount'] . ",
									'" . $narrative . "',
									" . ($taximpuesto*-1) . ",
									" . $tagref . ",
									'" . $ChequeNum . "'
								)";*/
					
					$ErrMsg = _('No se puede realizar la transaccion en GLtrans por que');
					$DbgMsg = _('El SQL utilizado fue');
					
					$ISQL = Insert_Gltrans($Transtype,$initransno,FormatDateForSQL($fechacheque),$PeriodNo, $TaxAccs['purchtaxglaccount'],$Narrative, $tagref ,$_SESSION['UserID'],
								$_POST['FunctionalExRate'],'','','',0,0,'',0,$inisupplierid,0, ($taximpuesto*-1), $db,$ChequeNum,'CUENTA CONTABLE', 0, $bancodestino, $rfcproveedor, $cuentadestino);
						
					$transaccion = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
					
					
					$Narrative = $supplierid . "@IMPUESTOA@" . ($taximpuesto);
					/*
					$SQL="INSERT INTO gltrans ( type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount,
									tag,
									chequeno) ";
					$SQL=$SQL . "VALUES (
									22,
									" . $initransno . ",
									'" . $fechacheque . "',
									" . $PeriodNo . ",
									" . $TaxAccs['purchtaxglaccountPaid'] . ",
									'" . $narrative . "',
									" . $taximpuesto . ",
									" . $tagref . ",
									'" . $ChequeNum . "'
								)";*/
					
					$ErrMsg = _('No se puede realizar la transaccion en GLtrans por que');
					$DbgMsg = _('El SQL utilizado fue');
							
					$ISQL = Insert_Gltrans($Transtype,$initransno,FormatDateForSQL($fechacheque),$PeriodNo, $TaxAccs['purchtaxglaccountPaid'],$Narrative, $tagref ,$_SESSION['UserID'],
								$_POST['FunctionalExRate'],'','','',0,0,'',0,$inisupplierid,0, $taximpuesto, $db,$ChequeNum,'CUENTA CONTABLE', 0, $bancodestino, $rfcproveedor, $cuentadestino);
						
					$transaccion = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
				} //IF EN DONDE ENCONTRO LAS CUENTAS DE IVAS
		
				$SQL= "SELECT suppname
						FROM suppliers
						WHERE supplierid='" . $supplierid . "'";
				
				$Result = DB_query($SQL, $db);
	
				if (DB_num_rows($Result)==0)
				{
					prnMsg( _('El codigo de Proveedor con el que esta pagina fue llamada, no existe en base de datos de Proveedores') . '. ' . _('Si esta pagina es llamada desde la pagina de Proveedores, esto garantiza que el proveedor existe!'),'warn');
					include('includes/footer.inc');
					exit;
				} else {
					/*CODIGO DE PROVEEDOR VALIDO*/
					$myrow = DB_fetch_array($Result);
					$Beneficiario = $myrow['suppname'];
					$narrative = $supplierid . "@" . (($saldo) * (-1));
	
					$SQL="INSERT INTO banktrans (transno,
									type,
									bankact,
									ref,
									exrate,
									functionalexrate,
									transdate,
									banktranstype,
									amount,
									currcode,
									tagref,
									beneficiary,
									chequeno) ";
					$SQL= $SQL . "VALUES (" . $initransno . ",
										" . $Transtype . ",
										" . $bankaccount . ",
										'" . $narrative . "',
										" . $_POST['ExRate'] . " ,
										" . $_POST['FunctionalExRate'] . ",
										Now(),
										'" . $Tipopago. "',";
										$SQL .=	($saldo) * (-1) . ",
										'" . $moneda . "',
										" . $tagref . ",
										'" . $Beneficiario . "',
										'" . $ChequeNum . "'
									)";
										
					$ErrMsg = _('No pude insertar la transaccion bancaria porque');
					$DbgMsg = _('No pude insertar la transaccion bancaria usando el SQL');
					$transaccion = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				}
			
				/***** INICIO IMPRESION DE CHEQUE******************/
				prnMsg(_('Pago') . ' ' . $TransNo . ' ' . _('al proveedor') . ' ' . $Beneficiario . ' ' . _('ha sido exitosamente procesado'),'success');
								
				/* BUSCA SI CHEQUERA UTILIZA UN FORMATO ESPECIAL DE IMPRESION */
				$sql="Select *
					FROM bankaccounts
					WHERE accountcode='" . $bankaccount . "'";
	
				$Result = DB_query($sql, $db);
				$myrow = DB_fetch_array($Result);
				$pdfprefix=$myrow['pdfprefix'];
				
				if ($pdfprefix == null)
					$pdfprefix = "";
				 			
				/*Set up a newy in case user wishes to enter another */
				$link="PrintJournalCh.php?FromCust=1&ToCust=1&PrintPDF=Yes&type=" . $Transtype. "&TransNo=" . $initransno ;
				$liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir')
					. '" alt="">' . ' ' .'<a  target="_blank" href="'.$link.'">'._('Imprimir Cheque usando formato pre-impreso').'</a>';
				echo '<br>'.$liga.'<br>';
			
				// actualiza estatus del documento a Ejecutado
				$sql = "UPDATE supptrans
				 		SET hold = 3
						WHERE id = '". $umovto ."'";
				
				$transaccion = DB_query($sql,$db);
				
				$transaccion= DB_Txn_Commit($db);  // FIN DE BLOQUE DE TRANSACCIONES
																						
				/***** FIN IMPRESION DE CHEQUE ******/
			} else {
				prnMsg(_('Debe seleccionar un movimiento Autorizado') .  ' ' . $_POST['selMovimiento'][$i],'error');
			}
		} else {
			prnMsg(_('El movimiento') .  ' ' . $listaelementos[$i]." debe estar Autorizado.",'error');
		}
	}  // Fin de Ciclo que recorre los elementos seleccionados
	
	echo '<br><a href="AutorizacionDePagosGenerales_V1.php?' . SID . '">' . _('Revision y Aprobacion PagosCxP') . '</a><br>';
	exit;
}


/**
 * 
 * @param elemento $objeto
 * @param string $nombre
 * @param string $valor
 * @param string $estilo
 */
function InsertaElemento($objeto, $nombre = 'textbox[]', $valor= "", $estilo= "") {
	if ($objeto == 'texto') {
		echo "<input type='textbox' name='".$nombre."' value='".$valor."' style='".$estilo."'>";
	}
}

?>