<?php
/*function ActualizaPurchOrderDetails($stockid, $orderno, $CantidadRecibida, $standardCost, $completed){
	$SQL = "UPDATE purchorderdetails
			SET quantityrecd = quantityrecd +". $CantidadRecibida . ",
				stdcostunit=".$standardCost .",
				completed=".$completed."
				WHERE purchorderdetails.orderno = ".$orderno."
					AND purchorderdetails.itemcode = '".$stockid ."'";
	$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('El detalle de la orden de compra no se realizo');
	$DbgMsg = _('El SQL utilizado es');
	$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);
	return 1;
}

function ActualizaGRNS ($GRN,$podetailitempv,$stockid,$descripcion,$DefaultReceivedDate,$CantidadRecibida,$supplierid,$rate,$CurrentStandardCostCompra){
	$SQL = "INSERT INTO grns (grnbatch,
															podetailitem,
															itemcode,
															itemdescription,
															deliverydate,
															qtyrecd,
															supplierid,
															rategr,
															stdcostunit)
											VALUES (" . $GRN . ",
													" . $podetailitempv . ",
													'" . $stockid . "',
													'" . $descripcion . "',
													'" . $DefaultReceivedDate . "',
													" . $CantidadRecibida . ",
													'" . $supplierid . "',
													'" . $rate . "',
													" . $CurrentStandardCostCompra . ')';
	//echo '<br><pre>sql:<br>'.$SQL;
	$ErrMsg =  _('ERROR CRITICO') . '! ' . _('ANOTE EL ERROR') . ': ' . _('No se realizo la transaccion');
	$DbgMsg =  _('El SQL utilizado es');
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
	return 1;
}*/

function ActualizaPropiedades($tagref,$STOCKID,$GRN,$podetailitempv,$lineaorden,$totalcampos){
	if($_SESSION['UserID'] == "admin"){
		echo 'entra';
	}
	$campouno='';
	$campodos='';
	$tipodefacturacion=25;
	if($_SESSION['UserID'] == "admin"){
		echo '<br>$totalcampos '.$totalcampos.' <br>';
	}
	if ($totalcampos>0){
			
		for ($i=0;$i<$totalcampos;$i++){
			$stockid=$_POST['PropDefaultval'.$lineaorden.'_'.$i];
			$valorstock=$_POST['PropDefault'.$lineaorden.'_'.$i];
			$tipoobj=$_POST['tipoobjeto'.$lineaorden.'_'.$i];
			$consulta=$_POST['consulta'.$lineaorden.'_'.$i];
			$campo=$_POST['campo'.$lineaorden.'_'.$i];
			$classe = $_POST['class'.$lineaorden.'_'.$i];
			$required = $_POST['required'.$lineaorden.'_'.$i];
			$requiredtoday = $_POST['requiredtoday'.$lineaorden.'_'.$i];
			$labelprop = $_POST['label'.$lineaorden.'_'.$i];
			$reqatprint= $_POST['reqatprint'.$lineaorden.'_'.$i];
			if($_SESSION['UserID'] == "admin"){
				echo '<br>$stockid '.$_POST['PropDefaultval'.$lineaorden.'_'.$i].' <br>';
				echo '<br>$valorstock '.$_POST['PropDefaultval'.$lineaorden.'_'.$i].' <br>';
				echo '<br>$tipoobj '.$_POST['tipoobjeto'.$lineaorden.'_'.$i].' <br>';
				echo '<br>$$consulta '.$_POST['consulta'.$lineaorden.'_'.$i].' <br>';
				echo '<br>$$campo '.$_POST['campo'.$lineaorden.'_'.$i].' <br>';
				echo '<br>$$classe '.$_POST['class'.$lineaorden.'_'.$i].' <br>';
				echo '<br>$$required '.$_POST['required'.$lineaorden.'_'.$i].' <br>';
				echo '<br>$$requiredtoday '.$_POST['requiredtoday'.$lineaorden.'_'.$i].' <br>';
				echo '<br>$$reqatprint '.$_POST['reqatprint'.$lineaorden.'_'.$i].' <br>';
				
			}
	
			if ($tipoobj=='checkbox'){
				if(isset($_POST['PropDefault'.$lineaorden.'_'.$i])){
					$valorstock="SI";
				}else{
					$valorstock="NO";
				}
			}
			$valorbase=$valorstock;
			if (strlen($consulta)>5){
				$sqlcampos=$consulta.' and '. $campo.' = "'.$valorstock.'"';
				//echo $sqlcampos;
				$DbgMsg = _('El SQL utilizado para obtener el valor del campo es');
				$ErrMsg = _('No se pudo obtener el valor, por que');
				$Result = DB_query($sqlcampos,$db,$ErrMsg,$DbgMsg,true);
				$Rowcampos = DB_fetch_array($Result);
				$valorbase=$Rowcampos[1];
			}
			if ($valorstock=="0" and strlen($consulta)>5 and $tipoobjeto == 5){
				$sqlcampos=$consulta.' and salesmanname like "%sin trabajador%" and tags.tagref= '.$tagref.' limit 1';
				$DbgMsg = _('El SQL utilizado para obtener el valor del campo es');
				$ErrMsg = _('No se pudo obtener el valor, por que');
				$Result = DB_query($sqlcampos,$db,$ErrMsg,$DbgMsg,true);
				$Rowcampos = DB_fetch_array($Result);
				$valorbase=$Rowcampos[1];
				$valorstock=$Rowcampos[0];
			}
			if ($valorstock!="0" and $valorstock != ""){
				//$existeprop=ValidaSalesProperty($stockid,$_SESSION['PO'.$identifier]->OrderNo,$POLine->PODetailRec,trim($valorstock),$tipodefacturacion,$db);
				$existeprop=0;
				if ($existeprop==0){
						
					if($reqatprint==1){
						if($campouno==''){
							$campouno=$valorbase;
						}
	
						if($campodos==''){
							$campodos=$valorbase;
						}
					}
						
					if($_SESSION['UserID'] == "admin"){
						echo '<br>entro<br>';
					}	
					$SQL = "INSERT INTO salesstockproperties (
									stkcatpropid,
									orderno,
									orderlineno,
									valor,
									InvoiceValue,
									typedocument
								)
								VALUES
								(
									". $stockid . ",
									" . $GRN. ",
									" . $podetailitempv . ",
									'" . trim($valorstock) . "',
									'" . trim($valorbase) . "',
									". $tipodefacturacion . "
								)";
					$ErrMsg="Error al insertar los valores extra";
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
					//echo '<br>sql:'.$SQL;
				}
					
			}
			//***********************+Campo de Categoria********************************
			$stockid1 = explode("/",$_POST['txtRuta'.$StockItem->LineNumber]);
			foreach($stockid1 as $arrCategorias){
				$categoriadetail=explode(".",$arrCategorias);
				if($categoriadetail[0]!=''){
					$SQL = "INSERT INTO salesstockproperties (
									stkcatpropid,
									orderno,
									orderlineno,
									valor,
									InvoiceValue,
									typedocument
	
								)
								VALUES
								(
									". $categoriadetail[0]. ",
									" . $GRN. ",
									" . $podetailitempv. ",
									'" . trim($categoriadetail[1]) . "',
									'" . trim($categoriadetail[1]) . "',
									". $tipodefacturacion . "
								)";
					$ErrMsg="Error al insertar los valores extra";
					$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				}//Fin de insert de propiedades en menu desplegable
			}//Fin de categorias de producto en menu desplegable
		} //Fin de recorrido de partidas de pedido
	}
	$SQL = "UPDATE grns
			SET textgr = '".$campouno."',
				textgr1 = '".$campodos."'
			WHERE grnbatch = '".$GRN."',
				AND	podetailitem = '".$podetailitempv."'
				AND itemcode = '".$STOCKID."'";
	$Result = DB_query($SQL, $db);
	return 1;
}
?>