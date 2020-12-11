<?php
$PageSecurity = 4;
include('includes/session.inc');
$title = _('Corrección Credito S/Contabilidad');
include('includes/header.inc');
$funcion=795;
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


if (isset($_POST['tipodocumento'])){
  $tipodocumento = $_POST['tipodocumento'];
}else{
  $tipodocumento = $_GET['tipodocumento'];
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
/* OBTENGO FECHAS*/
if (isset($_POST['FromYear'])) {
	$FromYear=$_POST['FromYear'];
} else {
	$FromYear=date('Y');
}
if (isset($_POST['FromMes'])) {
    $FromMes=$_POST['FromMes'];
} else {
    $FromMes=date('m');
}
if (isset($_POST['FromDia'])) {
    $FromDia=$_POST['FromDia'];
} else {
    $FromDia=date('d');
}


if ($monto=='')
  $monto=0;

if (isset($_POST['tipocambio'])){
  $tipocambio = $_POST['tipocambio'];
}else{
  $tipocambio = 1;  
}


if ($tipodocumento <> "0"){
  $systype_doc=$tipodocumento;  
}else{
  $systype_doc=116;
}



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
    
    /*if ($cuenta_notacredito=='')
    {
        prnMsg( _('Especifique la cuenta de credito a la que va a afectar la nota de credito'),'error');
        $InputError=1;
    }*/

   
    if ($InputError!=1)
      {
        
        # Obtiene el trans no que le corsesponde en base al tagref y al $systype_doc
        $transno = GetNextTransNo($systype_doc,$db);
	$Result = DB_Txn_Begin($db);
	$rate=1;
        $monedanota="MXN";
        /*$sqlmoneda="SELECT *
		FROM currencies
		WHERE currabrev='" . $Moneda."'";
        $Result = DB_query($sqlmoneda,$db);
        $myrow = DB_fetch_row($Result);*/
        $rate = 1/($_POST['tipocambio']);
        //$monedanota=$myrow[1];
        
	$taxrate = 0;
	$montoiva=0;
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
	}
	//calcula iva y desglosa de iva
	//$montoiva=$monto*($taxrate);
	//$monto=$monto-$montoiva;
        $montosiniva=$monto/(1+$taxrate);
	$montoiva=$monto- $montosiniva;
        

        # Datos del Periodo y fecha ***
        $DefaultDispatchDate=$FromDia.'/'.$FromMes.'/'.$FromYear;
	$fechaini= rtrim($FromYear).'-'.rtrim($FromMes).'-'.rtrim($FromDia);
	$diae=rtrim($FromDia);
	$mese = rtrim($FromMes);
	$anioe = rtrim($FromYear);
	$horax = date('H:i:s');
	$horax = strtotime($horax);
	$hora=date(H)-1;
	$minuto=date('i');
	$segundo=date('s');
	$fechainic=mktime($hora,$minuto,$segundo,rtrim($mese),rtrim($diae),rtrim($anioe));
	$fechaemision=date("Y-m-d H:i:s",$fechainic);
        $PeriodNo = GetPeriod($DefaultDispatchDate, $db, $tagref);
        $DefaultDispatchDate = FormatDateForSQL($DefaultDispatchDate);

        # Realiza el insert en la tabla de debtortrans
	
		$SQL = 'INSERT INTO supptrans ( type,
						transno,
						tagref,
						supplierno,
						suppreference,
						trandate,
						duedate,
						currcode,
						ovamount,
						ovgst,
						rate,
						transtext,
						origtrandate
						)
			VALUES ('. $systype_doc . ',
				'. $transno . ',
				' . $tagref . ",
				'" . $SupplierID . "',
				
				'" . $fechaini . "',
				'" . $fechaini . "',
				'" . $fechaini . "',
				'" . $Moneda . "',
				" . round(-($montosiniva),2) . ',
				' .round(-($montoiva),2) . ',
				' . $rate . ",
				'" . $concepto . "', '".$fechaemision."' )";
		$ErrMsg = _('ERROR CRITICO') . '! ' . _('TOME NOTA') . ': ' . _('La base de datos no soporta las transacciones');
		$DbgMsg = _('El SQL utilizado es');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
		$SuppTransID = DB_Last_Insert_ID($db,'supptrans','id');
                //echo "consulta:".$SQL;
		/* Insert the tax totals for each tax authority where tax was charged on the invoice */
		/*if (isset($_POST['tipocambio']) and $_POST['tipocambio']!=""){
			$SQL = 'INSERT INTO supptranstaxes (supptransid,
							taxauthid,
							taxamount)
				VALUES (' . $SuppTransID . ',
					' . $_POST['tipocambio'] . ',
					' . (($montoiva/$rate)*-1) . ')';
		
			$ErrMsg = _('ERROR CRITICO') . '! ' . _('TOME NOTA') . ': ' . _('La base de datos no soporta las transacciones');
			$DbgMsg = _('El SQL utilizado es');
 			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                        echo "<br>consulta2".$SQL;
		}*/
        # ***************************************************************************************************
        # **** AFECTACIONES CONTABLES ****
	# ***************************************************************************************************
            
            # Obtiene la cuentas contables que se afectarán
            # *****************************************
            # Se afecta la cuenta de CxC
            # *****************************************
            $cuenta_cxc=$_SESSION['CompanyRecord']['creditorsact'];
	    
                    $Result = DB_Txn_Commit($db);
		    //rnMsg(_($msgexito),success);
		    	
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
              $msgexito = '<b>LA NOTA DE CREDITO SE HA GENERADO EXITOSAMENTE...';
              prnMsg(_($msgexito),success);
        # ***************************************************************************************************
        # ***************************************************************************************************
        # ***************************************************************************************************       
      }
}
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
      
      echo '<td style="text-align:right" width=40%>' . _('Fecha de Aplicacion:') . '</td>';
      echo '<td colspan=2>
         <table>
		<tr>';
		       
		       echo '<td><select Name="FromDia">';
			    $sql = "SELECT * FROM cat_Days";
			    $dias = DB_query($sql,$db);
			    while ($myrowdia=DB_fetch_array($dias,$db)){
				$diabase=$myrowdia['DiaId'];
				if (rtrim(intval($FromDia))==rtrim(intval($diabase))){ 
				    echo '<option  VALUE="' . $myrowdia['DiaId'] .  '  " selected>' .$myrowdia['Dia'];
				}else{
				    echo '<option  VALUE="' . $myrowdia['DiaId'] .  '" >' .$myrowdia['Dia'];
				}
			    }
			    
		       echo'</td>'; 
		       echo '<td><select Name="FromMes">';
				 $sql = "SELECT * FROM cat_Months";
				 $Meses = DB_query($sql,$db);
				 while ($myrowMes=DB_fetch_array($Meses,$db)){
				     $Mesbase=$myrowMes['u_mes'];
				     if (rtrim(intval($FromMes))==rtrim(intval($Mesbase))){ 
					 echo '<option  VALUE="' . $myrowMes['u_mes'] .  '  " selected>' .$myrowMes['mes'];
				     }else{
					 echo '<option  VALUE="' . $myrowMes['u_mes'] .  '" >' .$myrowMes['mes'];
				     }
				 }
				 
				 echo '</select>';
				 echo '&nbsp;<input name="FromYear" type="text" size="4" value='.$FromYear.'>';
					 
			 echo '</td>
		       <td>
			    &nbsp;
		       </td>';
		    
		   echo '</tr>';
	   echo '</table>';			
      echo '</td>';	
  echo '</tr>';
      /* SELECCIONA TIPO DE DOCUMENTO */
      echo '<tr>';
        echo '<td style="text-align:right" width=40%>';
          echo '<font size=2 face="arial">';
            echo _('Tipo de Documento'). ' :';
          echo '</font>';
        echo '</td>';
        echo '<td>';
          echo '<select Name="tipodocumento">';
            $sql='SELECT typeid, typename';
            $sql.=' FROM systypescat
		WHERE typeid in (116,480)';
            $result = DB_query($sql ,$db);
	    echo "<option value='0'>" . _('Selecciona Tipo') . "</option>";
            while ($myrow=DB_fetch_array($result,$db)){
	      echo '<option  VALUE="' . $myrow[0] .  '"';
	      if ($tagref==$myrow[0]){
		echo ' selected';
              }
              echo '>' .$myrow[1] . '</option>';
            }
          echo '</select>';
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
            echo _('Monto'). ' :';
          echo '</font>';
        echo '</td>';
        echo '<td>';
          echo '<input class="number" type="text" name="monto" class="Number" style="text-align:right" value="'.$monto.'" size=15 maxlength=15>';
        echo '</td>';
      echo '</tr>';
     echo '<tr><td style="text-align:right">';
          echo '<font size=2 face="arial">';
            echo _('Tipo de Cambio'). ' :';
          echo '</font>';
        echo '</td>';
     echo '<td>';
          echo '<input class="number" type="text" name="tipocambio" class="Number" style="text-align:right" value="'.$tipocambio.'" size=10 maxlength=10>';
        echo '</td>';
      echo '</tr>';
     
      echo '<tr>';
        echo '<td></td><td>';
          echo '<input type="submit" name="procesar" value=" PROCESAR ">';
        echo '</td>';
      echo '</tr>';  
      
    echo '</table>';
    echo '</form>';
}



?>