<?php
function Traesaldoinicialflujo($fecha,$legalid,$tagref,$funcion,$checkbanco,$checkCxC,$checkCxP,$checkP,$BankAccount,$db){
	
	$SQLBanco="	SELECT 
					sum(banktrans.amount*-1) as saldo
			FROM banktrans
				INNER JOIN tags ON tags.tagref=banktrans.tagref
				INNER JOIN systypescat ON systypescat.typeid=banktrans.type
				INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
			  	INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			WHERE banktrans.transdate < '" . $fecha."'
				AND abs(banktrans.amount)!=0
		 ";
	if(isset($legalid) && $legalid!='0'){
		$SQLBanco = $SQLBanco ." AND tags.legalid=".$legalid;
	}
	if(isset($tagref) && $tagref!='-1'){
		$SQLBanco = $SQLBanco ." AND tags.tagref=".$tagref;
	}
	if(isset($BankAccount) && $BankAccount!='-1'){
		$SQLBanco = $SQLBanco ." AND banktrans.bankact='".$BankAccount."'";
	}
	//echo '<pre>sql:'.$SQLBanco;
	// Consulta movimientos de cxp
	$SQLCxP="	SELECT 
					sum((((supptrans.ovamount+supptrans.ovgst)-alloc))) as saldo
					
			FROM supptrans
				INNER JOIN systypesbyreport ON systypesbyreport.typedoc=supptrans.type and systypesbyreport.functionid=".$funcion."
				INNER JOIN suppliers ON supptrans.supplierno=suppliers.supplierid
				INNER JOIN tags ON tags.tagref=supptrans.tagref
				INNER JOIN systypescat ON systypescat.typeid=supptrans.type
				INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
			  	INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			WHERE supptrans.promisedate < '" . $fecha . "' 
					AND abs(((supptrans.ovamount+supptrans.ovgst)-alloc))!=0
		 ";
	if(isset($legalid) && $legalid!='0'){
		$SQLCxP = $SQLCxP ." AND tags.legalid=".$legalid;
	}
	if(isset($tagref) && $tagref!='-1'){
		$SQLCxP = $SQLCxP ." AND tags.tagref=".$tagref;
	}
	// consulta movimientos de cxc
	$SQLCxC="	SELECT 
			sum(case when debtortrans.ovamount+debtortrans.ovgst>0 then ((debtortrans.ovamount+debtortrans.ovgst)-alloc) *-1 
			else  ((debtortrans.ovamount+debtortrans.ovgst)-alloc)*-1 end ) as saldo
					
					/*sum((debtortrans.ovamount+debtortrans.ovgst)-alloc) as saldo*/
			FROM debtortrans
				INNER JOIN systypesbyreport ON systypesbyreport.typedoc=debtortrans.type and systypesbyreport.functionid=".$funcion."
				INNER JOIN debtorsmaster ON debtortrans.debtorno=debtorsmaster.debtorno
				INNER JOIN tags ON tags.tagref=debtortrans.tagref
				INNER JOIN systypescat ON systypescat.typeid=debtortrans.type
				INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
			  	INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			WHERE debtortrans.duedate < '" . $fecha. "'
					AND abs(((debtortrans.ovamount+debtortrans.ovgst)-alloc))!=0
		 ";
	if(isset($legalid) && $legalid!='0'){
		$SQLCxC = $SQLCxC ." AND tags.legalid=".$legalid;
	}
	if(isset($tagref) && $tagref!='-1'){
		$SQLCxC = $SQLCxC ." AND tags.tagref=".$tagref;
	}
	

	// consulta movimientos de proyeccion
	$SQLProy="	SELECT  
					sum(amount) as saldo
					
			FROM fjo_Movimientos
				INNER JOIN tags ON tags.tagref=fjo_Movimientos.tagref
				INNER JOIN legalbusinessunit ON legalbusinessunit.legalid=tags.legalid
			  	INNER JOIN sec_unegsxuser ON sec_unegsxuser.tagref = tags.tagref AND sec_unegsxuser.userid = '" . $_SESSION['UserID'] . "'
			WHERE fjo_Movimientos.fechapromesa < '" . $fechaini . "' /* AND fjo_Movimientos.fechapromesa <= '" . $fechafin . "' */
					AND abs(amount)!=0
		 ";
	if(isset($_POST['legalid']) && $_POST['legalid']!='0'){
		$SQLProy = $SQLProy ." AND tags.legalid=".$_POST['legalid'];
	}
	if(isset($_POST['unidadnegocio']) && $_POST['unidadnegocio']!='-1'){
		$SQLProy = $SQLProy ." AND tags.tagref=".$_POST['unidadnegocio'];
	}
	
	
	$unionCXC=' UNION ';
	if($checkbanco!='checked'){
		$SQLBanco='';
		$unionCXC='';
	}
	$unionCXP=' UNION ';
	if($checkCxC!='checked'){
		$SQLCxC='';
		$unionCXP='';
			
	}
	$unionP=' UNION ';
	if($checkCxP!='checked'){
		$SQLCxP='';
		$unionP='';
	}
	if($checkP!='checked'){
		$SQLProy='';
	}
	$SQL=$SQLBanco.$unionCXC.$SQLCxC.$unionCXP.$SQLCxP.$unionP.$SQLProy;
	$validaunion=left(strrev($SQL), 6);
	if(trim($validaunion)=='NOINU'){
		$sqllen=strlen($SQL)-6;
		$SQL=left($SQL, $sqllen);
	}
	
	
	$SQLSaldo="select sum(saldo) as saldofin from (";
	$SQLSaldo=$SQLSaldo.$SQL;
	$SQLSaldo=$SQLSaldo.") as saldofin";

	//echo '<pre>sql:'.$SQLSaldo.'<br><br>';
	$Result = DB_query($SQLSaldo, $db);
	$Row = DB_fetch_row($Result);
	return $Row[0];
}
// Verifica si viene de procesar cambio de fecha en documentos
if (isset($_POST['Process'])){
	
	$Oper=$_POST['Oper'];
	$contador=$_POST['Contador'];
	$Result = DB_Txn_Begin($db);
	for($doc=0;$doc<=$contador;$doc++){
		//verifica si lleva check activo
		$checkactivo=$_POST['item_' . $doc];
		if ($checkactivo==true){
			$tipomov=$_POST['tipodocto_' . $doc];
			$iddocto=$_POST['nodocto_' . $doc];
			$newfecha=$_POST['Fechaprom_' . $doc];
			// variables solo para mov de proyeccion
			$concepto=$_POST['concepto_' . $doc];
			$referencia=$_POST['referencia_' . $doc];
			$cargo=$_POST['cargo_' . $doc];
			$abono=$_POST['abono_' . $doc];
			
			if($Oper=='ActualizaInf'){
				switch ($tipomov)
				{
					case 'CXC' :
						$SQL="UPDATE debtortrans
							  SET duedate='".$newfecha."'
							  WHERE id=".$iddocto;
						$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						break;
					case 'CXP' :
						$SQL="UPDATE supptrans
							  SET promisedate='".$newfecha."'
							  WHERE id=".$iddocto;
						$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						break;
					case 'P' :
						if(abs($abono)!=0){
							$monto=($abono*-1);
						}else{
							$monto=$cargo;
						}
						$SQL="UPDATE fjo_Movimientos
							  SET fechapromesa='".$newfecha."',
							  		amount='".$monto."',
							  		referencia='".DB_escape_string($referencia)."',
							  		concepto='".DB_escape_string($concepto)."'				
							  WHERE id=".$iddocto;
						$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						break;
				}
			}//fin de update informacion
			elseif($Oper=='ActualizaInf'){
				if($tipomov=='P'){
					$SQL="DELETE FROM  fjo_Movimientos WHERE id=".$iddocto;
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				}
			}// fin de elimina mov de proyeccion
			elseif($Oper=='DuplicaSemana'){
				if($tipomov=='P'){
					$SQL="INSERT fjo_Movimientos(tagref,referencia,concepto,amount,userregister,fecha,fechapromesa)
							SELECT  tagref,referencia,concepto,amount,'".$_SESSION['UserID']."', ADDDATE(fecha,7), ADDDATE(fechapromesa,7) 
							FROM  fjo_Movimientos WHERE id=".$iddocto;
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				}
			}elseif($Oper=='DuplicaMes'){
				if($tipomov=='P'){
					$SQL="INSERT fjo_Movimientos(tagref,referencia,concepto,amount,userregister,fecha,fechapromesa)
							SELECT  tagref,referencia,concepto,amount,'".$_SESSION['UserID']."', ADDDATE(fecha,30), ADDDATE(fechapromesa,30) 
							FROM  fjo_Movimientos WHERE id=".$iddocto;
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				}
			}elseif($Oper=='ActualizaMasiva'){
				$newfecha=$_POST['Fechapromesagral'];
				switch ($tipomov)
				{
					case 'CXC' :
						$SQL="UPDATE debtortrans
							  SET duedate='".$newfecha."'
							  WHERE id=".$iddocto;
						$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						break;
					case 'CXP' :
						$SQL="UPDATE supptrans
							  SET promisedate='".$newfecha."'
							  WHERE id=".$iddocto;
						$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						break;
					case 'P' :
						if(abs($abono)!=0){
							$monto=($abono*-1);
						}else{
							$monto=$cargo;
						}
						$SQL="UPDATE fjo_Movimientos
							  SET fechapromesa='".$newfecha."',
							  		amount='".$monto."',
							  		referencia='".DB_escape_string($referencia)."',
							  		concepto='".DB_escape_string($concepto)."'
							  WHERE id=".$iddocto;
						$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
						break;
				}
				
				
			}// fin de if oper
		}
	}
	$Result = DB_Txn_Commit($db);
	$_POST['ReportePantalla']=_('Mostrar en Pantalla');
}
//verifica alta de movimientos de proyeccion

if (isset($_POST['ProcessAlta'])){
	$fechapromesa= rtrim($FromYearMov).'-'.rtrim($FromMesMov).'-'.rtrim($FromDiaMov);
	if($_POST['unidadnegocioMov']=='-1'){
		prnMsg(_('No ha seleecionado la unidad de negocio para el movimiento'), 'error');
		$inputError=1;
	}
	if(strlen($_POST['Cargo'])==0 and strlen($_POST['abono'])==0 ){
		prnMsg(_('No ha proporcionado el monto para el movimiento'), 'error');
		$inputError=1;
	}
	if(strlen($_POST['Cargo'])>0 and strlen($_POST['abono'])>0 ){
		prnMsg(_('El movimiento solo puede ser cargo o abono, verifique'), 'error');
		$inputError=1;
	}
	
	if($_POST['Cargo']>0){
		$monto=$_POST['Cargo'];
	}elseif($_POST['abono']>0){
		$monto=$_POST['abono']*-1;
	}
	
	if($inputError==0){
		$Result = DB_Txn_Begin($db);
		// agrega informacion a tabla de movimientos fjo_Movimientos
		$SQL = "INSERT INTO fjo_Movimientos (
								 tagref,
								 referencia,
								 concepto,
								 amount,
								 userregister,
								 fecha,
								 fechapromesa
								 )
						  VALUES (
							  '" . $_POST['unidadnegocioMov'] . "',
							  '" . DB_escape_string($_POST['factura']). "',
							  '" . DB_escape_string($_POST['Concepto']). "',
							  '" .$monto . "',
							  '" . $_SESSION['UserID']. "',
							  now(),
							  '" . $fechapromesa ."')";
		
		$ErrMsg = _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El numero de serie del stock no se puede insertar por que');
		$DbgMsg = _('El siguiente SQL para insertar el numero de serie del stock es:');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
		prnMsg(_('El movimiento se proceso con exito!'), 'sucess');
		$_POST['ReportePantalla']=_('Mostrar en Pantalla');
		$Result = DB_Txn_Commit($db);
	}
	
	
}
// Actualiza fechas promesa nullas
if (isset($_POST['ActualizaFechaNula'])){
	$Result = DB_Txn_Begin($db);
	$SQL="UPDATE debtortrans
		  SET duedate=trandate
		  WHERE duedate is null or year(duedate)='0000'";
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

	$SQL="UPDATE supptrans
		  SET promisedate=duedate
		 WHERE promisedate is null or year(promisedate)='0000'";
	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	$Result = DB_Txn_Commit($db);
	prnMsg(_('El proceso de actualizacion se realizo con exito!'), 'sucess');
}
 
