<?php
$PageSecurity = 4;
include('includes/session.inc');
$title = _('Nota de Credito Directa al Saldo');
include('includes/header.inc');
$funcion=383;
include('includes/SecurityFunctions.inc');
include('includes/SQL_CommonFunctions.inc');
include ('Numbers/Words.php');
//para cuentas referenciadas
include('includes/Functions.inc');
include('includes/XSAInvoicing.inc');
require_once('lib/nusoap.php');
 
if (isset($_POST['SupplierID']))
{
  $SupplierID = $_POST['SupplierID'];
}
else
{
  $SupplierID = $_GET['SupplierID'];
}

if (isset($_POST['tagref']))
{
  $tagref = $_POST['tagref'];
}
else
{
  $tagref = $_GET['tagref'];
}

if (isset($_POST['concepto']))
{
  $concepto = trim($_POST['concepto']);
}
else
{
  $concepto = trim($_GET['concepto']);
}

if (isset($_POST['monto']))
{
  $monto = $_POST['monto'];
}
else
{
  $monto = $_GET['monto'];
}

if (isset($_POST['GLCode']))
{
  $cuenta_notacredito = $_POST['GLCode'];
}
else
{
  $cuenta_notacredito = $_GET['GLCode'];
}

if (isset($_POST['Moneda']))
{
  $Moneda= $_POST['Moneda'];
}
else
{
  $Moneda = $_GET['Moneda'];
}

if (isset($_POST['tipocambio'])){
  $tipocambio = $_POST['tipocambio'];
}else{
  $tipocambio = 1;  
}




if ($monto=='')
  $monto=0;

if (isset($_GET['aplicadiot'])) {
	$aplicadiot = $_GET['aplicadiot'];
} elseif (isset($_POST['aplicadiot'])) {
	$aplicadiot = $_POST['aplicadiot'];
}


$systype_doc=32;

$sql='SELECT suppname as name';
$sql.=' FROM suppliers';
$sql.=' WHERE supplierid="'.$SupplierID.'"';
$result = DB_query($sql ,$db);
$myrow = DB_fetch_row($result);

$nombrecliente=$myrow[0];


if (isset($_POST['procesar']))
{
    $InputError = 0;
    if (strlen($SupplierID)==0 or $SupplierID=='')
    {
        prnMsg( _('Seleccione el Proveedor al que se le aplicara la nota de credito'),'error');
        $InputError=1;
    }
    
    if ($concepto=='')
    {
        prnMsg( _('El concepto de la Nota de Credito no puede ir vacío. Capturar Concepto'),'error');
        $InputError=1;
    }
    if ($Moneda=='')
    {
        prnMsg( _('Especifique la moneda con la que se realizara la transaccion'),'error');
        $InputError=1;
    }
    
    if ($monto<=0)
    {
        prnMsg( _('El monto de la Nota de Credito no puede ser menor o igual a CERO. Capturar un monto mayor a CERO'),'error');
        $InputError=1;
    }
    
    if ($cuenta_notacredito=='')
    {
        prnMsg( _('Especifique la cuenta de credito a la que va a afectar la nota de credito'),'error');
        $InputError=1;
    }

   
    if ($InputError!=1)
	{
        
        # Obtiene el trans no que le corsesponde en base al tagref y al $systype_doc
        $transno = GetNextTransNo($systype_doc,$db);
		$Result = DB_Txn_Begin($db);
		$rate=1;
        $monedanota="MXN";
        $sqlmoneda="SELECT *
		FROM currencies
		WHERE currabrev='" . $Moneda."'";
        $Result = DB_query($sqlmoneda,$db);
        $myrow = DB_fetch_row($Result);
        //$rate = $myrow[4];
        $monedanota=$myrow[1];
        
		$taxrate = 0;
		$montoiva=0;
        $rate = (1/$tipocambio);
        
		//echo "iva:".$_POST['TaxCat'];
		if (isset($_POST['TaxCat']) and $_POST['TaxCat']!=""){
		  
			$sqliva="SELECT taxauthrates.taxrate as taxrate,purchtaxglaccount,purchtaxglaccountPaid
				 FROM taxauthrates, taxauthorities
				WHERE taxauthrates.taxauthority=taxauthorities.taxid
				AND taxauthrates.taxcatid =" . $_POST['TaxCat'];
			$Result = DB_query($sqliva,$db);
			$myrow = DB_fetch_row($Result);
			$taxrate = $myrow[0];
			$taxglcode=$myrow[1];
			$taxglcodepaid=$myrow[2];
			
			$SQL = 'select * from taxcategories where taxcatid='.$_POST['TaxCat'];
			$result2 = DB_query($SQL,$db);
			$myrow = DB_fetch_row($result2);
			$taxcatname = $myrow[1];
		
		}
	//calcula iva y desglosa de iva
	//$montoiva=$monto*($taxrate);
	//$monto=$monto-$montoiva;
        $montosiniva=$monto/(1+$taxrate);
	$montoiva=$monto- $montosiniva;
        

        # Datos del Periodo y fecha ***
          $DefaultDispatchDate=Date($_SESSION['DefaultDateFormat'],CalcEarliestDispatchDate());
          $PeriodNo = GetPeriod($DefaultDispatchDate, $db,$tagref);
        
          $DefaultDispatchDate = FormatDateForSQL($DefaultDispatchDate);
        # *****************************
                

        # Realiza el insert en la tabla de debtortrans
          
		$SQL = 'INSERT INTO supptrans ( type,
						transno,
						tagref,
						supplierno,
						suppreference,
						trandate,
						duedate,
						ovamount,
						ovgst,
						rate,
						transtext,
						typeoperationdiot,
						origtrandate,currcode
						)
			VALUES ('. $systype_doc . ',
				'. $transno . ',
				' . $tagref . ",
				'" . $SupplierID . "',
				'" . $SupplierID . "',
				'" . $DefaultDispatchDate . "',
				'" . $DefaultDispatchDate . "',
				" . round(-($montosiniva),2) . ',
				' .round(-($montoiva),2) . ',
				' . $rate . ",
				'" . $concepto . "',
				'" . $aplicadiot . "',
				 now(),'" . $Moneda . "' )";
		$ErrMsg = _('ERROR CRITICO') . '! ' . _('TOME NOTA') . ': ' . _('La base de datos no soporta las transacciones');
		$DbgMsg = _('El SQL utilizado es');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
		$SuppTransID = DB_Last_Insert_ID($db,'supptrans','id');
		/* Insert the tax totals for each tax authority where tax was charged on the invoice */
		if (isset($_POST['TaxCat']) and $_POST['TaxCat']!=""){
			$SQL = 'INSERT INTO supptranstaxes (supptransid,
							taxauthid,
							taxamount)
				VALUES (' . $SuppTransID . ',
					' . $_POST['TaxCat'] . ',
					' . (($montoiva/$rate)*-1) . ')';
		
			$ErrMsg = _('ERROR CRITICO') . '! ' . _('TOME NOTA') . ': ' . _('La base de datos no soporta las transacciones');
			$DbgMsg = _('El SQL utilizado es');
 			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}
        # ***************************************************************************************************
        # **** AFECTACIONES CONTABLES ****
	# ***************************************************************************************************
          
          
            # Obtiene la cuentas contables que se afectarán
            # *****************************************
            # Se afecta la cuenta de CxC
            # *****************************************
            $cuenta_cxc=$_SESSION['CompanyRecord']['creditorsact'];
	     // if ($montoiva!=0) {
		      $SQL = "INSERT INTO gltrans (
				type,
				typeno,
				trandate,
				periodno,
				account,
				narrative,
				amount,
				tag
				)
			VALUES (
				" . $systype_doc . ",
				" . $transno . ",
				'" . $DefaultDispatchDate . "',
				" . $PeriodNo . ",
				" . $taxglcode . ",
				'" . $SupplierID. " No. de Nota de Credito: ". $transno . " @".$nombrecliente." ',
				" . -($montoiva/$rate) . ",
				" . $tagref . "
			)";
			//$Result = DB_query($SQL ,$db);    
			
		      $Narrative= $SupplierID. " No. de Nota de Credito: ". $transno . " @".$nombrecliente;
		      $montocontable= -($montoiva/$rate);
		      // ejecuta funcion para insertar cargo en tabla de contabilidad
		      $ISQL = Insert_Gltrans($systype_doc,$transno,$DefaultDispatchDate,$PeriodNo, $taxglcode,$Narrative,$tagref ,$_SESSION['UserID'],
		      		$rate,'','','',0,0,'',0,$SupplierID,0, $montocontable,$db,$ChequeNum,'IVA '.$taxcatname);
		      $DbgMsg = _('El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:');
		      $ErrMsg = _('No se pudo insertar la Transaccion Contable para la cuenta puente de caja');
		      $Result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
		      
		//} 
		//OBTENGO NUMERO DE CUENTA X TIPO DE PROVEEDOR
        $tipoproveedor = ExtractTypeSupplier($SupplierID,$db);
		$ctaxtipoproveedor = SupplierAccount($tipoproveedor,"gl_accountsreceivable",$db);
		# Obtiene la cuentas contables que se afectarán
		#$cuenta_notacredito=$_SESSION['CompanyRecord']['gllink_creditors'];
                # *****************************************
                # Se afecta la cuenta de CxC
                # *****************************************
                    $SQL = "INSERT INTO gltrans (
                            type,
                            typeno,
                            trandate,
                            periodno,
                            account,
                            narrative,
                            amount,
                            tag
                            )
                    VALUES (
                            " . $systype_doc . ",
                            " . $transno . ",
                            '" . $DefaultDispatchDate . "',
                            " . $PeriodNo . ",
                            " . $ctaxtipoproveedor . ",
                            '" . $SupplierID. " No. de Nota de Credito: ". $transno . " @".$nombrecliente." ',
                            " . (($monto/$rate)) . ",
                            " . $tagref . "
                    )";
                    #echo $SQL;
                    //$Result = DB_query($SQL ,$db);
                    
                    $Narrative= $SupplierID. " No. de Nota de Credito: ". $transno . " @".$nombrecliente;
                    $montocontable= (($monto/$rate));
                    // ejecuta funcion para insertar cargo en tabla de contabilidad
                    $ISQL = Insert_Gltrans($systype_doc,$transno,$DefaultDispatchDate,$PeriodNo, $ctaxtipoproveedor,$Narrative,$tagref ,$_SESSION['UserID'],
                    		$rate,'','','',0,0,'',0,$SupplierID,0, $montocontable,$db,$ChequeNum,'PROVEEDOR');
                    $DbgMsg = _('El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:');
                    $ErrMsg = _('No se pudo insertar la Transaccion Contable para la cuenta puente de caja');
                    $Result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
                                        
                # *****************************************
                # Se afecta la cuenta de Notas de Credito
                # *****************************************
                  // afecta cuentas de notas de credito    
                    $SQL = "INSERT INTO gltrans (
                            type,
                            typeno,
                            trandate,
                            periodno,
                            account,
                            narrative,
                            amount,
                            tag
                            )
                    VALUES (
                            " . $systype_doc . ",
                            " . $transno . ",
                            '" . $DefaultDispatchDate . "',
                            " . $PeriodNo . ",
                            " . $cuenta_notacredito . ",
                            '" . $SupplierID . " No. de Nota de Credito: ". $transno . " @".$nombrecliente." ',
                            " . -($montosiniva/$rate) . ",
                            " . $tagref . "
                    )";
                    $msgexito = '<b>LA NOTA DE CREDITO SE HA GENERADO EXITOSAMENTE...';
                   // $Result = DB_query($SQL ,$db,$msgexito);
                    $Narrative= $SupplierID. " No. de Nota de Credito: ". $transno . " @".$nombrecliente;
                    $montocontable= -($montosiniva/$rate);
                    // ejecuta funcion para insertar cargo en tabla de contabilidad
                    $ISQL = Insert_Gltrans($systype_doc,$transno,$DefaultDispatchDate,$PeriodNo, $cuenta_notacredito,$Narrative,$tagref ,$_SESSION['UserID'],
                    		$rate,'','','',0,0,'',0,$SupplierID,0, $montocontable,$db,$ChequeNum,'CXP');
                    $DbgMsg = _('El SQL fallo al insertar la transaccion de Contabilidad para las cuentas puentes de caja:');
                    $ErrMsg = _('No se pudo insertar la Transaccion Contable para la cuenta puente de caja');
                    $Result = DB_query($ISQL,$db,$ErrMsg,$DbgMsg,true);
                    /*
		    if ($montoiva!=0) {
			$SQL = "INSERT INTO gltrans (
				type,
				typeno,
				trandate,
				periodno,
				account,
				narrative,
				amount,
				tag
				)
			VALUES (
				" . $systype_doc . ",
				" . $transno . ",
				'" . $DefaultDispatchDate . "',
				" . $PeriodNo . ",
				" . $taxglcodepaid . ",
				'" . $SupplierID . " No. de Nota de Credito: ". $transno . " @".$nombrecliente." ',
				" . (($montoiva/$rate)*-1) . ",
				" . $tagref . "
			)";
			$msgexito = '<b>LA NOTA DE CREDITO SE HA GENERADO EXITOSAMENTE...';
			$Result = DB_query($SQL ,$db,$msgexito);  
		      
		    }
                    */
                    $Result = DB_Txn_Commit($db);
			prnMsg(_($msgexito),'success');
                    
                    $liga = "PDFCreditDirectSupplier.php";
		    $liga='<p><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Imprimir') . '" alt="">' . ' ' . '<a  target="_blank" href="' . $rootpath . '/'. $liga . SID . '?tipo='.$systype_doc.'&area='.$area.'&legal='.$legaid.'&TransNo=' . $transno .'&Tagref='.$tagref.'">'. _('Imprimir Nota de Credito') . ' (' . _('Laser') . ')' .'</a>';
                
                    echo '<p><div align="center">';
                      echo $liga;
                    echo '</div>'; 
		    	
	      /*
  		echo '<p><div align="center">';
		  echo $liga;
		echo '</div>'; 
		//Actualizar el documento para folio
		$SQL="UPDATE debtortrans
		      SET folio='" . $serie.'|'.$folio . "'
		      WHERE transno=".$transno." and type=".$systype_doc;
		$ErrMsg =_('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('La Actualizacion para saldar la factura, no se pudo realizar');
		$DbgMsg = _('El SQL utilizado para el registro de la fatura es:');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	      */    
        # ***************************************************************************************************
        # ***************************************************************************************************
        # ***************************************************************************************************       
	}
}   // fin de condicion de procesar

echo '<p><div align="center">';
  echo '<a href="SelectSupplier.php?modulosel=4">';
  echo '<font size=2 face="arial">';
    echo _('Regresar a Opciones del Proveedor');
  echo '</font>';
  echo '</a></div>';
  
if (!isset($_POST['procesar']))
{
    echo "<form action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method=post>";
    echo '<input type=hidden name="SupplierID" value="'.$SupplierID.'">';
    echo '<table width=80% border=0 cellpadding=4 align=center>';
      echo '<tr>';
        echo '<td style="text-align:center" colspan=2>';
          echo '<font size=3 face="arial">';
            echo '<b>'. _('NOTA DE CREDITO - ') . $nombrecliente .'</b>';
          echo '</font>';
        echo '</td>';
      echo '</tr>';
      echo '<tr>';
        echo '<td style="text-align:right" width=40%>';
          echo '<font size=2 face="arial">';
            echo _('Unidad de Negocio'). ' :';
          echo '</font>';
        echo '</td>';
        echo '<td>';
          echo '<select Name="tagref">';
            $sql='SELECT t.tagref, tagdescription';
            $sql.=' FROM tags t, sec_unegsxuser uxu';
            $sql.=' WHERE t.tagref=uxu.tagref';
            $sql.=' AND userid="'.$_SESSION['UserID'].'"';
            $sql.=' Order by t.tagdescription';
            
            $result = DB_query($sql ,$db);
            
            while ($myrow=DB_fetch_array($result,$db))
            {
              echo '<option  VALUE="' . $myrow[0] .  '"';
              
              if ($tagref==$myrow[0])
              {
                echo ' selected';
              }
              echo '>' .$myrow[1] . '</option>';
            }
          echo '</select>';
        echo '</td>';
      echo '</tr>';
      echo '<tr>';
        echo '<td style="text-align:right">';
          echo '<font size=2 face="arial">';
            echo _('Concepto'). ' :';
          echo '</font>';
        echo '</td>';
        echo '<td>';
          echo '<textarea name="concepto" rows="4" cols="50"></textarea>';
        echo '</td>';
      echo '</tr>';
      
      echo '<tr>';
        echo '<td style="text-align:right">';
          echo '<font size=2 face="arial">';
            echo _('Monto'). ' :';
          echo '</font>';
        echo '</td>';
        echo '<td>';
          echo '<input class="number" type="text" name="monto" class="Number" style="text-align:right" value="'.$monto.'" size=15 maxlength=15>';
        echo '</td>';
      echo '</tr>';
      
      echo '<tr>';
        echo '<td style="text-align:right">';
          echo '<font size=2 face="arial">';
            echo _('Moneda'). ' :';
          echo '</font>';
        echo '</td>';
        echo '<td>';
            echo '<select Name="Moneda">';
            $sql='SELECT * ';
            $sql.=' FROM currencies order by rate desc';
            $result = DB_query($sql ,$db);
            while ($myrow=DB_fetch_array($result,$db))
            {
              echo '<option  VALUE="' . $myrow[1] .  '"';
              
              if ($Moneda==$myrow[1])
              {
                echo ' selected';
              }
              echo '>' .$myrow[0] . '</option>';
            }
          echo '</select>';
          
        echo '</td>';
      echo '</tr>';
      
      
      echo '<tr>';
        echo '<td style="text-align:right">';
          echo '<font size=2 face="arial">';
            echo _('Tipo de Cambio'). ' :';
          echo '</font>';
        echo '</td>';
        echo '<td>';
          echo "<input class='number' type='text' name='tipocambio' style='text-align:right' value='".$tipocambio."' size='5' maxlength='5'>";
        echo '</td>';
      echo '</tr>';
      
      
      echo '<tr><td style="text-align:right">' . _('Categoria Impuestos') . ':</td><td><select name="TaxCat">';
	$sql = 'SELECT taxcatid, taxcatname FROM taxcategories ORDER BY taxcatname';
	$result = DB_query($sql, $db);
	if (!isset($_POST['TaxCat'])){
		$_POST['TaxCat'] = $_SESSION['DefaultTaxCategory'];
	}
	while ($myrow = DB_fetch_array($result)) {
		if ($_POST['TaxCat'] == $myrow['taxcatid']){
			echo '<option selected value=' . $myrow['taxcatid'] . '>' . $myrow['taxcatname'] . '</option>';
		} else {
			echo '<option value=' . $myrow['taxcatid'] . '>' . $myrow['taxcatname'] . '</option>';
		}
	} //end while loop
      echo '</select></td></tr>';
      echo '<tr><td style="text-align:right">' . _('Selecciona la Cuenta') . ':</td>
		<td><select name="GLCode" onChange="return assignComboToInput(this,'.'GLManualCode'.')">';
	
        $SQL = 'SELECT accountcode,
			concept AS accountname
			FROM chartbridge_supp
			ORDER BY accountcode';

	$result=DB_query($SQL,$db);
	if (DB_num_rows($result)==0){
		echo '</select></td></tr>';
		prnMsg(_('No se an configurado las cuentas contables todavia') . ' - ' . _('pagos no se pueden analizar contra cuentas si no estan dadas de alta'),'error');
	} else {
		while ($myrow=DB_fetch_array($result)){
			if (isset($_POST['GLCode']) and $_POST['GLCode']==$myrow["accountcode"]){
				echo '<option selected value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'] . ' - ' . $myrow['accountname'];
			} else {
				echo '<option value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'] . ' - ' . $myrow['accountname'];
			}
		}
		echo '</select></td></tr>';
	}
	
	echo '<tr><td  style="text-align:right">' . _('Aplica Diot') . ":</td>";
	echo '<td><select name="aplicadiot">';
	if(!isset($_POST['aplicadiot'])){
		$_POST['aplicadiot'] = 1;
	}
	if($_POST['aplicadiot'] == 1){
		echo '<option selected value="1">Si</option>';
		echo '<option  value="0">No</option>';
	}elseif($_POST['aplicadiot'] == 0){
		echo '<option value="1">Si</option>';
		echo '<option selected  value="0">No</option>';
	}else{
		echo '<option selected value="1">Si</option>';
		echo '<option value="0">No</option>';
	}
	echo '</select>';
	echo "</td></tr>";
      echo '<tr>';
        echo '<td></td><td>';
          echo '<input type="submit" name="procesar" value=" PROCESAR ">';
        echo '</td>';
      echo '</tr>';  
      
    echo '</table>';
    echo '</form>';
}



?>