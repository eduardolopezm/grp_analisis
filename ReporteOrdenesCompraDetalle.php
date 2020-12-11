<?php
/*
 * AHA
* 5-Nov-2014
* Cambio de ingles a espa–ol los mensajes de usuario de tipo error,info,warning, y success.
*/

$PageSecurity = 2;
include('includes/session.inc');

$funcion=357;
include('includes/SecurityFunctions.inc');
if (!isset($_POST['btnExcel'])){
	$title = _('Busqueda de Ordenes de Compra X almacen');
	include('includes/header.inc');
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/sales.png" title="' . _('ORDENES DE COMPRA') . '" alt="">' . ' ' . _('BUSQUEDA DE ORDENES DE COMPRA X ALMACEN') . '</p> ';
}

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
	
     $fechaini= rtrim($FromYear).'-'.rtrim($FromMes).'-'.rtrim($FromDia);
     $fechafin= rtrim($ToYear).'-'.rtrim($ToMes).'-'.rtrim($ToDia);
     
     $fechainic=mktime(0,0,0,rtrim($FromMes),rtrim($FromDia),rtrim($FromYear));
     $fechafinc=mktime(23,59,59,rtrim($ToMes),rtrim($ToDia),rtrim($ToYear));
     
     $fechaini= rtrim($FromYear).'-'.add_ceros(rtrim($FromMes),2).'-'.add_ceros(rtrim($FromDia),2);
     $fechafin= rtrim($ToYear).'-'.add_ceros(rtrim($ToMes),2).'-'.add_ceros(rtrim($ToDia),1) . ' 23:59:59';
     
     $InputError = 0;
     if ($fechainic > $fechafinc){
          $InputError = 1;
     prnMsg(_('La fecha inicial no puede ser mayor a la fecha final! Por favor seleccionar nuevamente los periodos de fecha'),'error');
     } else {
          $InputError = 0;
     }	

 if (!isset($_POST['btnExcel'])){
    if (isset($_POST['printpdf']) && $_POST['printpdf'] != "") {
		echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . "/PDF_OrdenesCompraDetalle.php?codeAlmacen=" . $_POST['codeAlmacen'] . "&fechaini=" . $fechaini . "&fechafin=" . $fechafin . "&estatus=" . $_POST['estatus'] . " '>";
		exit;
    }	
} 	

if (!isset($_POST['btnExcel'])){
	echo '<form action=' . $_SERVER['PHP_SELF'] .'?' .SID . ' method=post>';
		echo '<input type=hidden name="fechaini" VALUE="' . $fechaini . '">
		      <input type=hidden name="fechafin" VALUE="' . $fechafin . '">';
		      
	
	if (isset($_POST['ResetPart'])){
	     unset($_REQUEST['SelectedStockItem']);
	}
	if(isset($_SESSION['ExistingOrder'])){
	     unset($_SESSION['ExistingOrder']);
	}
	echo '<p><div class="centre">';
	
	//aqui empieza la seleccion de periodo desde hasta
	     echo '<table>
		       <tr>
			    <td>' . _('Desde:') . '</td>
			    <td><select Name="FromDia">';
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
		            </td>
		            <td>' . _('Hasta:') . '</td>';
			    echo'<td><select Name="ToDia">';
				      $sql = "SELECT * FROM cat_Days";
				      $Todias = DB_query($sql,$db);
				      while ($myrowTodia=DB_fetch_array($Todias,$db)){
					  $Todiabase=$myrowTodia['DiaId'];
					  if (rtrim(intval($ToDia))==rtrim(intval($Todiabase))){ 
					      echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '  " selected>' .$myrowTodia['Dia'];
					  }else{
					      echo '<option  VALUE="' . $myrowTodia['DiaId'] .  '" >' .$myrowTodia['Dia'];
					  }
				      }
			    echo '</td>';
			    echo'<td>';
				 echo'<select Name="ToMes">';
				 $sql = "SELECT * FROM cat_Months";
				 $ToMeses = DB_query($sql,$db);
				 while ($myrowToMes=DB_fetch_array($ToMeses,$db)){
				     $ToMesbase=$myrowToMes['u_mes'];
				     if (rtrim(intval($ToMes))==rtrim(intval($ToMesbase))){ 
					 echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '  " selected>' .$myrowToMes['mes'];
				     }else{
					 echo '<option  VALUE="' . $myrowToMes['u_mes'] .  '" >' .$myrowToMes['mes'];
				     }
				 }
				 echo '</select>';
				 echo '&nbsp;<input name="ToYear" type="text" size="4" value='.$ToYear.'>';
				 
			    echo'</td>
		       </tr>
		  </table>';
	echo "<table>";
	echo "<tr>";
	echo "<td>"._('Almacen') . "</td>";
	echo "<td><select name='codeAlmacen'> ";
	$sql = "SELECT   t.tagref, t.tagdescription , l.locationname , l.loccode
		FROM     tags t, sec_unegsxuser uxu,locations l
		WHERE    t.tagref=uxu.tagref
			 and l.tagref = t.tagref
			 AND uxu.userid='".$_SESSION['UserID']."'
			 ORDER BY l.locationname";
	
	    $resultTags = DB_query($sql,$db);
	    echo "<option selected Value=''>" . "TODAS...";
	    while ($myrow=DB_fetch_array($resultTags))
	    {
		      if ($myrow['loccode'] == $_POST['codeAlmacen'])
		      {
			   echo "<option selected Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'] .'</option>';
		      }
		      else
		      {
			   echo "<option Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'] .'</option>';
		      }
	    }
	echo '</select></td>';		
	
	
	echo "<td>" . _('X Estatus') . ":</td>";
	echo"<td><select name='estatus'> ";
	
	    echo "<option selected Value=''>" . "TODAS...";
	
	    if ($_POST['estatus'] == '1')
		$idestatus1 = "selected";
	
	    if ($_POST['estatus'] == '2')
		$idestatus2 = "selected";
	
	    if ($_POST['estatus'] == '3')
		$idestatus3 = "selected";
	
	    if ($_POST['estatus'] == '4')
		$idestatus4 = "selected";
	
	    
	    echo "<option " . $idestatus1 . "   Value='1'>" . "ORDENADAS Y NO RECIBIDAS";
	    echo "<option " . $idestatus2 . "   Value='2'>" . "ORDENADAS Y RECIBIDAS";
	    echo "<option " . $idestatus3 . "   Value='3'>" . "ORDENADAS Y RECIBIDAS Y FACTURADAS";
	    echo "<option " . $idestatus4 . "   Value='4'>" . "ORDENADAS Y RECIBIDAS Y NO FACTURADAS";
	    echo '</select></td></tr>';
	    if (!isset($_POST['claveproveedor'])) {
		    $_POST['claveproveedor'] = '*';  
		} 
	/* SELECCION DEL CLIENTE */
		echo "<tr><td>" . _('X Clave de Proveedor') . ":</td>";
				echo "<td><input type=text name='claveproveedor' value='".$_POST['claveproveedor']."'>:* para todos.";
		echo "	</td>";
		echo "<td>"._('Usuario Autorizo').": </td>";
		$sql = " SELECT www_users.userid,
						www_users.realname
				 FROM www_users";
		$resultus = DB_query($sql, $db);
		echo "<td><select name=useridauto>";
		echo "<option selected value='*'>Todos los usuarios</option>";
		while ($myrowus = DB_fetch_array($resultus)){
			if($_POST['useridauto'] == $myrowus['userid']){
				echo "<option selected value='".$myrowus['userid']."'>".$myrowus['realname']."</option>";
			}else{
				echo "<option value='".$myrowus['userid']."'>".$myrowus['realname']."</option>";
			}
		}
		echo "</select></td></tr>";
		/*FIN CLAVE CLIENTE*/
		echo "<tr><td><input type=submit name='SearchOrders' VALUE='" . _('Imprime en Pantalla') . "'></td>";
	echo "<td><input type=submit name='printpdf' VALUE='" . _('Imprime a PDF') . "'></td>
			<td><input type=submit name='btnExcel' VALUE='" . _('Exportar Excel') . "'></td></tr>";
	//echo "</table><hr>";
	echo '</table>';
}
       
if ( isset($_POST['codeAlmacen']) ){  

	if ($_POST['codeAlmacen'] != ''){
	$SQL ="
		SELECT purchorders.orderno as numeroorden ,
				purchorders.intostocklocation as clavealmacen ,
				purchorders.orddate as fechaorden ,
				purchorders.supplierno as claveproveedor ,
				suppliers.suppname as nombreproveedor ,
				purchorderdetails.itemcode as claveproducto ,
				purchorderdetails.itemdescription as nombreproducto ,
				purchorderdetails.qtyinvoiced as cantidadproductosF ,
				purchorderdetails.quantityord as cantidadproductosO ,
				purchorderdetails.quantityrecd as cantidadproductosR,
				purchorderdetails.unitprice as preciounitario,
				purchorders.supplierorderno,
				purchorders.wo,
				purchorders.autorizausuario,
				purchorders.requisitionno,
				IF(stockmaster.stockid IS NULL, 1, 0) AS noinventariable
		FROM    purchorders
			JOIN suppliers on purchorders.supplierno = suppliers.supplierid
			JOIN purchorderdetails on purchorders.orderno = purchorderdetails.orderno
			LEFT JOIN stockmaster ON stockmaster.stockid = purchorderdetails.itemcode
		WHERE   purchorders.orddate >= '" . $fechaini . "' and purchorders.orddate <= '" . $fechafin . "'
		        and purchorders.intostocklocation = '" . $_POST['codeAlmacen'] . "' ";
		if (isset($_POST['claveproveedor']) and $_POST['claveproveedor']!='*'){
			$SQL=$SQL." AND purchorders.supplierno='".$_POST['claveproveedor']."'";
		}
		if(isset($_POST['useridauto']) and $_POST['useridauto'] <> '*'){
			$SQL = $SQL. " AND purchorders.autorizausuario = '".$_POST['useridauto']."'";
		}
		if ($_POST['estatus'] == '')
			$FiltroEstatus = "  ";
			//
		if ($_POST['estatus'] == '1')
			$FiltroEstatus = " and purchorderdetails.quantityord > 0 and purchorderdetails.quantityrecd =0 ";
		
		if ($_POST['estatus'] == '2')
			$FiltroEstatus = " and purchorderdetails.quantityord > 0 and purchorderdetails.quantityrecd >0 ";

		if ($_POST['estatus'] == '3')
			$FiltroEstatus = " and purchorderdetails.quantityord > 0 and purchorderdetails.quantityrecd > 0  and purchorderdetails.qtyinvoiced > 0 ";

		if ($_POST['estatus'] == '4')
			$FiltroEstatus = " and purchorderdetails.quantityord > 0 and purchorderdetails.quantityrecd > 0 and purchorderdetails.qtyinvoiced = 0 ";
		
		$SQL = $SQL . $FiltroEstatus;		
		
		
		$SQL = $SQL . " ORDER BY purchorders.orderno,purchorders.intostocklocation";
		//echo $SQL;	       	
		
	}
	if (isset($_POST['btnExcel'])){
		header("Content-type: application/ms-excel");
		# replace excelfile.xls with whatever you want the filename to default to
		header("Content-Disposition: attachment; filename=ReportOrdenesCompra.xls");
		header("Pragma: no-cache");
		header("Expires: 0");
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
				"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
		
		echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
		echo '<link rel="shortcut icon" href="'. $rootpath . '/favicon.ico" />';
		echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';
	}
	if ($_POST['codeAlmacen'] == ''){
	$SQL ="
		SELECT purchorders.orderno as numeroorden ,
				purchorders.intostocklocation as clavealmacen ,
				purchorders.orddate as fechaorden ,
				purchorders.supplierno as claveproveedor ,
				suppliers.suppname as nombreproveedor ,
				purchorderdetails.itemcode as claveproducto ,
				purchorderdetails.itemdescription as nombreproducto ,
				purchorderdetails.qtyinvoiced as cantidadproductosF ,
				purchorderdetails.quantityord as cantidadproductosO ,
				purchorderdetails.quantityrecd as cantidadproductosR,
				purchorderdetails.unitprice as preciounitario,
				purchorders.supplierorderno,
				purchorders.wo,
				purchorders.autorizausuario,
				purchorders.requisitionno,
				IF(stockmaster.stockid IS NULL, 1, 0) AS noinventariable
		FROM    purchorders
			JOIN suppliers on purchorders.supplierno = suppliers.supplierid
			JOIN purchorderdetails on purchorders.orderno = purchorderdetails.orderno
			LEFT JOIN stockmaster ON stockmaster.stockid = purchorderdetails.itemcode
		WHERE   purchorders.orddate >= '" . $fechaini . "' and purchorders.orddate <= '" . $fechafin . "' ";
		if (isset($_POST['claveproveedor']) and $_POST['claveproveedor']!='*'){
			$SQL=$SQL." AND purchorders.supplierno='".$_POST['claveproveedor']."'";
		}
		if(isset($_POST['useridauto']) and $_POST['useridauto'] <> '*'){
			$SQL = $SQL. " AND purchorders.autorizausuario = '".$_POST['useridauto']."'";
		}
		if ($_POST['estatus'] == '')
			$FiltroEstatus = "  ";
			
		if ($_POST['estatus'] == '1')
			$FiltroEstatus = " and purchorderdetails.quantityord > 0 and purchorderdetails.quantityrecd =0 ";
		
		if ($_POST['estatus'] == '2')
			$FiltroEstatus = " and purchorderdetails.quantityord > 0 and purchorderdetails.quantityrecd >0 ";

		if ($_POST['estatus'] == '3')
			$FiltroEstatus = " and purchorderdetails.quantityord > 0 and purchorderdetails.quantityrecd > 0  and purchorderdetails.qtyinvoiced > 0 ";

		if ($_POST['estatus'] == '4')
			$FiltroEstatus = " and purchorderdetails.quantityord > 0 and purchorderdetails.quantityrecd > 0 and purchorderdetails.qtyinvoiced = 0 ";
		
		
		$SQL = $SQL . $FiltroEstatus;

		
		$SQL = $SQL . " ORDER BY purchorders.orderno,purchorders.intostocklocation";
		//echo $SQL;	       	
		    
	}
	
	//echo '<pre>'.$SQL;
				
              
     $ErrMsg = _('No hay facturas en este periodo');
     $SalesOrdersResult = DB_query($SQL,$db,$ErrMsg);
     
     /*show a table of the orders returned by the SQL */
     echo '<table cellpadding=2 colspan=7 WIDTH=100%>';	
     $fecha_actual = strtotime(date("d-m-Y H:i:00",time()));

     $tableheader = "<tr>
	<th nowrap>" . _('Fecha Orden / Almacen') . "</th>
	<th nowrap>" . _('Numero Orden') . "</th>
	<th nowrap>" . _('Proveedor / Producto') . "</th>
	<th nowrap>" . _('Usuario Auto.') . "</th>
	<th nowrap>" . _('Orden Trabajo') . "</th>
	<th nowrap>" . _('Pedido Venta') . "</th>
	<th nowrap>" . _('O.C. Proveedor') . "</th>
	<th nowrap>" . _('Cant Fact') . "</th>
	<th nowrap>" . _('Cant Ord') . "</th>
	<th nowrap>" . _('Cant Reci') . "</th>
	<th nowrap>" . _('Importe') . "</th>				
	<th nowrap>" . _('Iva') . "</th>
	<th nowrap>" . _('Total') . "</th>";
     $tableheader=$tableheader."</tr>";
	
     echo $tableheader;
     $fechanio=date('Y');
     $fechames=date('m');
     $fechadia=date('d');
     $fechahoy=$fechanio.'-'.$fechames.'-'.$fechadia;
     $j = 1;
     $k=0; //row colour counter
     $numdoc = 0;
     while ($myrow=DB_fetch_array($SalesOrdersResult)) {
     
	$numeroorden=$myrow['numeroorden'];
	$clavealmacen=$myrow['clavealmacen'];
	$fechaorden = $myrow['fechaorden'];
	$claveproveedor =$myrow['claveproveedor'];
	$nombreproveedor=$myrow['nombreproveedor'];
	$claveproducto=$myrow['claveproducto'];
	$nombreproducto = $myrow['nombreproducto'];
	$cantidadproductosF=$myrow['cantidadproductosF'];
	$cantidadproductosO=$myrow['cantidadproductosO'];
	$cantidadproductosR=$myrow['cantidadproductosR'];
	$preciounitario = number_format($myrow['preciounitario'],2);
	
	$usuarioauto = $myrow['autorizausuario'];
	$ot = $myrow['wo'];
	$pv = $myrow['requisitionno'];
	$ocp = $myrow['supplierorderno'];
 	
	$arrayFecha=explode("-",$fechaorden);
	$dia_pedido = $arrayFecha[2];
	$mes_pedido = $arrayFecha[1];
	$anio_pedido = $arrayFecha[0];		
	$fechaorden =  $anio_pedido . "-" . $mes_pedido . "-" . substr($dia_pedido,0,2);

	
		
	if ($numeroordenAnterior != $myrow['numeroorden'] || $numeroordenAnterior = ''){
		
		/*importe total de la orden de compra*/
		$Sql = "SELECT  sum(purchorderdetails.qtyinvoiced * purchorderdetails.unitprice) as importe
			FROM    purchorders JOIN purchorderdetails on purchorders.orderno = purchorderdetails.orderno
			        AND purchorders.orderno = " . $numeroorden;

		 $SalesOrdersResultOrden = DB_query($Sql,$db,$ErrMsg);
		 while ($myrowTotalOrden=DB_fetch_array($SalesOrdersResultOrden)) {
			$importe=$myrowTotalOrden['importe'];
		 }	
		///////////////////////////////////////

		/*obtengo iva por producto*/
		$sqltaxe ="SELECT  (purchorderdetails.qtyinvoiced * purchorderdetails.unitprice),
			(purchorderdetails.qtyinvoiced * purchorderdetails.unitprice) * taxauthrates.taxrate as ivaxitem
			,purchorderdetails.itemcode
			,taxauthrates.taxrate
			FROM    purchorders
			JOIN purchorderdetails on purchorders.orderno = purchorderdetails.orderno
			JOIN stockmaster on stockmaster.stockid = purchorderdetails.itemcode
			JOIN taxauthrates on taxauthrates.taxcatid = stockmaster.taxcatid
			AND purchorders.orderno =". $numeroorden;		
		
		$taxrateXOrden = 0;	
		$querytaxes = DB_query($sqltaxe,$db,$ErrMsg);
		while ($myrowTaxes=DB_fetch_array($querytaxes)) {
		       $taxrateXOrden= $taxrateXOrden + $myrowTaxes['ivaxitem'];
		}
		
		$taxrateXOrden = $taxrateXOrden;
		
		if ($myrow['noinventariable'] == 1) {
			$ivaNoInv = $_SESSION['NoInventariableIVA'];
			if (empty($ivaNoInv) == false) {
				$taxrateXOrden = $importe * $ivaNoInv;
			}
		}
		
		
		////////////////////////////////		
		
		$totalOrdendeCompra = $importe + $taxrateXOrden;
		
		/*AGRUPA POR FECHA*/
		if ($fechaordenAnterior != $myrow['fechaorden'])
			$imprimefecha = $fechaorden;
		else
			$imprimefecha = "&nbsp;";	
		////////////////////
		
		echo " <tr class=OddTableRows>";
			 echo "<td nowrap style='font-size:13px;color:red'><b>" .$imprimefecha . "</b></td>";
			 echo "<td style='text-align:center;'><b>" .$numeroorden . "</b></td>";
			 echo "<td><b>" . $nombreproveedor . "</b></td>";
			 echo "<td><b>" . $usuarioauto . "</b></td>";
			 echo "<td><b>" . $ot . "</b></td>";
			 echo "<td><b>" . $pv . "</b></td>";
			 echo "<td><b>" . $ocp . "</b></td>";
			 echo "<td>&nbsp;</td>";
			 echo "<td>&nbsp;</td>";
			 echo "<td>&nbsp;</td>";
			 echo "<td style='text-align:right;'><b>$" . number_format($importe,2) . "</b></td>";
			 echo "<td style='text-align:right;'><b>$" .number_format($taxrateXOrden,2). "</b></td>";
			 echo "<td style='text-align:right;'><b>$" . number_format($totalOrdendeCompra,2) . "</b></td>";
			 		
		echo "</tr>";
		
		echo " <tr style='background-color:#F2F5A9'>";
			 echo "<td><b>[" . $clavealmacen . "]</b></td>";
			 echo "<td>&nbsp;</td>";
			 echo "<td style='text-align:left;'>[" .$claveproducto . "] " . $nombreproducto .  "</td>";
			 echo "<td>&nbsp;</td>";
			 echo "<td>&nbsp;</td>";
			 echo "<td>&nbsp;</td>";
			 echo "<td>&nbsp;</td>";
			 echo "<td style='text-align:right;'>" .$cantidadproductosF . "</td>";
			 echo "<td style='text-align:right;'>" .$cantidadproductosO . "</td>";
			 echo "<td style='text-align:right;'>" .$cantidadproductosR . "</td>";
			 echo "<td style='text-align:right;'>$" . $preciounitario . "</td>";
			 echo "<td style='text-align:right;'>&nbsp;</td>";
			 echo "<td>&nbsp;</td>";		
		echo "</tr>";		
	
	}
	else{
		echo " <tr style='background-color:#F2F5A9'>";
			 echo "<td>&nbsp;</td>";
			 echo "<td>&nbsp;</td>";
			 echo "<td style='text-align:left;'>[" .$claveproducto . "] " . $nombreproducto . "</td>";
			 echo "<td><b>" . $usuarioauto . "</b></td>";
			 echo "<td><b>" . $ot . "</b></td>";
			 echo "<td><b>" . $pv . "</b></td>";
			 echo "<td><b>" . $ocp . "</b></td>";
			 echo "<td style='text-align:right;'>" .$cantidadproductosF . "</td>";
			 echo "<td style='text-align:right;'>" .$cantidadproductosO . "</td>";
			 echo "<td style='text-align:right;'>" .$cantidadproductosR . "</td>";
			 echo "<td style='text-align:right;'>$" .$preciounitario . "</td>";
			 echo "<td style='text-align:right;'>&nbsp;</td>";		
			 
			 echo "<td>&nbsp;</td>";		
		echo "</tr>";		
		
	}
	$numeroordenAnterior = $numeroorden;
	$fechaordenAnterior = $myrow['fechaorden'];	
	$numdoc =  $numdoc + 1;
	$TotalImporte =  $TotalImporte + $importe;
	$TotalIva =  $TotalIva + $taxrateXOrden;
	//$TotalOrdenT = $TotalOrden + ($importe + $taxrateXOrden);
	////sumariza totales
	//
	 	
      }
      
      /////////////totales
        $TotalOrdenT = $TotalImporte + $TotalIva;
	echo " <tr style='background-color:#BE81F7'>";
		echo "<td colspan='10'><b>TOTALES</b></td>";
		echo "<td style='text-align:right;'><b>$" .number_format($TotalImporte,2) . "</b></td>";
		echo "<td style='text-align:right;'><b>$" .number_format($TotalIva,2) . "</b></td>";
		echo "<td style='text-align:right;'><b>$" .number_format($TotalOrdenT ,2) . "</b></td>";
	
	echo "</tr>";
	////////////////
      
      
}      
      
if (!isset($_POST['btnExcel'])){    
	include('includes/footer.inc');
}
echo "</form>";
/*<script>

function openpdf(){

document.getElementById('imprimepdf').value="yes";
alert(document.getElementById('imprimepdf').value);
//window.location = "ReporteOrdenesCompraDetalle.php?printpdf=yes";
document.forms[0].submit();
}

</script>*/
?>




