<?php
$PageSecurity = 11;

include('includes/session.inc');

$title = _('Mantenimiento de Categorias de Inventario');
if($_GET['exportExcel']!='yes'){
include('includes/header.inc');}
$funcion=137;
include('includes/SecurityFunctions.inc');

if (isset($_POST['deductibleflag'])){
	$_POST['deductibleflag'] = 1;
}else{
	$_POST['deductibleflag'] = 0;
}

if (isset($_GET['SelectedCategory'])){
	$SelectedCategory = strtoupper($_GET['SelectedCategory']);
} else if (isset($_POST['SelectedCategory'])){
	$SelectedCategory = strtoupper($_POST['SelectedCategory']);
}

if (isset($_GET['DeleteProperty'])){
	
	$ErrMsg = _('No se pudo eliminar la propiedad') . ' ' . $_GET['DeleteProperty'] . ' ' . _('porque');
	$sql = "DELETE FROM stockitemproperties WHERE stkcatpropid=" . $_GET['DeleteProperty'];
	$result = DB_query($sql,$db,$ErrMsg);
	$sql = "DELETE FROM stockcatproperties WHERE stkcatpropid=" . $_GET['DeleteProperty'];
	$result = DB_query($sql,$db,$ErrMsg);
	prnMsg(_('Eliminar la propiedad') . ' ' . $_GET['DeleteProperty'],'success');
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	$_POST['CategoryID'] = strtoupper($_POST['CategoryID']);

	if ( strlen($_POST['CategoryID']) > 6 ) {
		$InputError = 1;
		prnMsg(_('El codigo de Categoria de Inventario debe ser maximo de seis caracteres o menos'),'error');
	} elseif ( strlen($_POST['CategoryID'])==0 ) {
		$InputError = 1;
		prnMsg(_('El codigo de Categoria de Inventario debe ser por lo menos un caracter y menos de siete'),'error');
	} elseif ( strlen($_POST['CategoryDescription']) > 50 ) {
		$InputError = 1;
		prnMsg(_('La descripcion de la Categoria de Inventario debe de ser de menos de cincuenta caracteres o menos'),'error');
	} elseif ( $_POST['StockType'] !='D' AND $_POST['StockType'] !='L' AND $_POST['StockType'] !='F' AND $_POST['StockType'] !='M' ) {
		$InputError = 1;
		prnMsg(_('El tipo de inventario debe ser uno de ') . ' "D" - ' . _('Dummy item') . ', "L" - ' . _('Labour stock item') . ', "F" - ' . _('Finished product') . ' ' . _('or') . ' "M" - ' . _('Raw Materials'),'error');
	}
	
	/*Validacion para saber si el valor de "afecto a inventario" fue habilitado o no*/
	if ($_POST['afecto']!= ""){
		$afecto = 1;	
	}else {
		$afecto = 0;
	}
	if ($_POST['margensales']== ""){
		$_POST['margensales'] = 0;	
	}
	if ($_POST['flujo']!= ""){
		$flujo = $_POST['flujo'];	
	}else {
		$flujo = 0;
	}
	
	if ($_POST['cashdiscount']!= ""){
		$cashdiscount = $_POST['cashdiscount'];	
	}else {
		$cashdiscount = 0;
	}
	
	if ($_POST['changeprecio']!= ""){
		$changeprecio = 1;	
	}else {
		$changeprecio = 0;
	}
	
	if ($_POST['warrantycost']== ""){
		$_POST['warrantycost'] = 0;	
	}
	
	$discountInPriceListOnPrice = 0;
	$discountInComercialOnPrice = 0;
	if ($_POST['descLPonPrice'])
		$discountInPriceListOnPrice = 1;
	
	if ($_POST['descCOMonPrice'])
		$discountInComercialOnPrice = 1;
	
	
	$_POST['image'] = '';
	if(empty($_FILES['image']['name']) == FALSE) {
		if($InputError != 1) {
			if((($_FILES["image"]["type"] == "image/gif")
			|| ($_FILES["image"]["type"] == "image/jpeg")
			|| ($_FILES["image"]["type"] == "image/png")
			|| ($_FILES["image"]["type"] == "image/pjpeg"))) {
				
				$dir = "./images";
				include "includes/UploadClass.php";
				$upload = new Upload();
				$upload->set_max_size(999000);
				$upload->set_directory($dir);
				$upload->set_tmp_name($_FILES['image']['tmp_name']);
				$upload->set_file_size($_FILES['image']['size']);
				$upload->set_file_type($_FILES['image']['type']);
				$upload->set_file_name($_FILES['image']['name']);
				$upload->start_copy();
				$upload->resize(150, 150);
				if($upload->is_ok()) {
					$_POST['image'] = $upload->user_full_name;
				} else {
					prnMsg(_($upload->error()), 'error');
				}	
			} else {
				prnMsg(_("El archivo que est� intentando subir no es una imagen"), 'error');
			}
		}
	}
	
	if ($SelectedCategory AND $InputError !=1) {
		
		/*SelectedCategory could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/
		
		$updateImageSQL = "";
		if(empty($_POST['image']) == FALSE) {
			$updateImageSQL = "image='" . $_POST['image'] . "',";
		}
		
		$sql = "UPDATE stockcategory SET stocktype = '" . $_POST['StockType'] . "',
									 glcodebydelivery = '" . $_POST['glcodebydelivery'] . "',
                                     categorydescription = '" . $_POST['CategoryDescription'] . "',
                                     textimage = '" . $_POST['textimage'] . "',
                                     $updateImageSQL
                                     stockact = '" . $_POST['StockAct'] . "',
                                     adjglact = '" . $_POST['AdjGLAct'] . "',
                                     purchpricevaract = '" . $_POST['PurchPriceVarAct'] . "',
                                     materialuseagevarac = '" . $_POST['MaterialUseageVarAc'] . "',
								     internaluse = '" . $_POST['UseInternal'] . "',
				                     wipact = '" . $_POST['WIPAct'] . "',
								     allowNarrativePOLine = ". $_POST['allowNarrativePOLine'] .",
								     margenaut = ". $_POST['margenaut'] .",
								     margenautcost = ". $_POST['margenautcost'] .",		
								     warrantycost = ". $_POST['warrantycost'] .",
								     minimummarginsales = ". $_POST['margensales'] .",
								     prodLineId=". $_POST['Prodlineid'].",
								     redinvoice=".$afecto.",
								     disabledprice=".$changeprecio.",
								     idflujo=".$flujo.",
								     cashdiscount = ".$cashdiscount.",
								     deductibleflag = " . $_POST['deductibleflag'] . ",
								     u_typeoperation = " . $_POST['u_typeoperation'] . ",
								     typeoperationdiot = " . $_POST['typeoperationdiot'] . ",
								     discountInPriceListOnPrice = ".$discountInPriceListOnPrice .",
									 discountInComercialOnPrice	= ".$discountInComercialOnPrice.",
									 cattipodescripcion = '".$_POST['cattipodescripcion']."',
									 generaPublicacionAutomatica = '".$_POST['generaPublicacionAutomatica']."'	
                          WHERE
                                categoryid = '$SelectedCategory'";
		//echo $sql;
		/*if($_SESSION['UserID'] == "admin"){
			echo '<pre>'.$sql;
		}*/
        $ErrMsg = _('No se pudo actualizar la Categoria de Inventario') . $_POST['CategoryDescription'] . _('porque');
        $result = DB_query($sql,$db,$ErrMsg);

        for ($i=0;$i<=$_POST['PropertyCounter'];$i++){

        	if (isset($_POST['PropReqSO' .$i]) and $_POST['PropReqSO' .$i] == true){
        			$_POST['PropReqSO' .$i] =1;
        	} else {
        			$_POST['PropReqSO' .$i] =0;
        	}
        	if ($_POST['PropID' .$i] =='NewProperty' AND strlen($_POST['PropLabel'.$i])>0){
        		$sql = "INSERT INTO stockcatproperties (categoryid,
        								label,
        								controltype,
        								defaultvalue,
        								reqatsalesorder,
        								idPadre)
        							VALUES ('" . $SelectedCategory . "',
        								'" . $_POST['PropLabel' . $i] . "',
        								" . $_POST['PropControlType' . $i] . ",
        								'" . $_POST['PropDefault' .$i] . "',
        								" . $_POST['PropReqSO' .$i] . ','
        								. $_POST['PropControlTypePadre'] .')';
        		$ErrMsg = _('No se pudo insertar la propiedad de la categoria de inventario por') . $_POST['PropLabel' . $i];
        		$result = DB_query($sql,$db,$ErrMsg);
        	} elseif ($_POST['PropID' .$i] !='NewProperty') { //we could be amending existing properties
        		$sql = "UPDATE stockcatproperties SET label ='" . $_POST['PropLabel' . $i] . "',
        											  controltype = " . $_POST['PropControlType' . $i] . ",
        											  defaultvalue = '"	. $_POST['PropDefault' .$i] . "',
        											  reqatsalesorder = " . $_POST['PropReqSO' .$i] . "
        				WHERE stkcatpropid =" . $_POST['PropID' .$i];
        		$ErrMsg = _('Se actualizo la propiedad de la Categoria de Inventario para') . ' ' . $_POST['PropLabel' . $i];
        		$result = DB_query($sql,$db,$ErrMsg);
        	}

        } //end of loop round properties
        // Se agrega validacion de listas de precios
        
        $SQL = 'DELETE FROM  salespricesbycategory WHERE categoryid="'.$SelectedCategory.'"';
        //echo $SQL;
        $Result = DB_query($SQL,$db);
        
        $sql="select typeabbrev
					from salestypes";
        $Resultprice = DB_query($sql,$db);
        while ($myrowprices=DB_fetch_array($Resultprice)){
        	if ($_POST['lista_' . $myrowprices['typeabbrev']] == 1){
        
        		$IncrementPercentage=$_POST[$myrowprices['typeabbrev']]/100;
        		if ($IncrementPercentage>0){
        			$SQL = "INSERT INTO  salespricesbycategory(categoryid,percent,typeabbrev)
	        				VALUES('".$SelectedCategory."','".$IncrementPercentage."','".$myrowprices['typeabbrev']."')";
        			//echo $SQL;
        			$Result = DB_query($SQL,$db);
        			 
        		}
        	}
        }
        prnMsg(_('Se actualizo el registro de la Categoria de Inventario para') . ' ' . $_POST['CategoryDescription'],'success');

	} elseif ($InputError !=1) {

	/*Selected category is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new stock category form */
	    if(isset($_POST['margenaut'])&& $_POST['margenaut']!=""){
		$sql = "INSERT INTO stockcategory (categoryid,
                                       stocktype,
                                       categorydescription,
									   textimage,
				 					   image,
                                       stockact,
                                       adjglact,
                                       purchpricevaract,
                                       materialuseagevarac,
				       internaluse,
                                       wipact,
				       allowNarrativePOLine,
				       margenaut,
					   margenautcost,
				       prodLineId,
				       minimummarginsales,
				       redinvoice,
				       warrantycost,
				       disabledprice ,
				       idflujo,
				       cashdiscount,
				       deductibleflag,
				       u_typeoperation,
				       typeoperationdiot,
						discountInPriceListOnPrice,
						discountInComercialOnPrice,
						cattipodescripcion,
						glcodebydelivery,
						generaPublicacionAutomatica
				       )
                                       VALUES (
                                       '" . $_POST['CategoryID'] . "',
                                       '" . $_POST['StockType'] . "',
                                       '" . $_POST['CategoryDescription'] . "',
                                       '" . $_POST['textimage'] . "',
                                       '" . $_POST['image'] . "',
                                       '" . $_POST['StockAct'] . "',
                                       '" . $_POST['AdjGLAct'] . "',
                                       '" . $_POST['PurchPriceVarAct'] . "',
                                       '" . $_POST['MaterialUseageVarAc'] . "',
				       '" . $_POST['UseInternal'] . "',
                                       '" . $_POST['WIPAct'] . "',
				       " . $_POST['allowNarrativePOLine'] . ",
				       " . $_POST['margenaut'] . ",
				       " . $_POST['margenautcost'] . ",		
				       " . $_POST['Prodlineid'].",
				       " . $_POST['margensales'].",
				       " .$afecto.",
				       " . $_POST['warrantycost'].",
				       " .$changeprecio.",
				       " .$flujo.",
				       " .$cashdiscount.",
				       " .$_POST['deductibleflag'].",
				       " .$_POST['u_typeoperation'].",
				       " .$_POST['typeoperationdiot'].",
				       ".$discountInPriceListOnPrice .",
					   ".$discountInComercialOnPrice."	,
					   ".$_POST['cattipodescripcion'].",
					   '" . $_POST['glcodebydelivery'] ."',
					   	'".$_POST['generaPublicacionAutomatica']."'
				       )";
        $ErrMsg = _('No se pudo insertar la nueva Categoria de Inventario') . $_POST['CategoryDescription'] . _('porque');
        $result = DB_query($sql,$db,$ErrMsg);
		prnMsg(_('Un nuevo registro de Categoria de Inventario se ha agregado para') . ' ' . $_POST['CategoryDescription'],'success');

		}else{
			$sql = "INSERT INTO stockcategory (categoryid,
                                       stocktype,
                                       categorydescription,
									   textimage,
				 					   image,
                                       stockact,
                                       adjglact,
                                       purchpricevaract,
                                       materialuseagevarac,
								       internaluse,
				                                       wipact,
								       allowNarrativePOLine,
								       margenaut,
									   margenautcost,
								       prodLineId,
								       minimummarginsales,
								       warrantycost,
								       redinvoice,
								       disabledprice ,
								       idflujo,
								       cashdiscount,
								       deductibleflag,
								       u_typeoperation,
								       typeoperationdiot,
										discountInPriceListOnPrice,
										 discountInComercialOnPrice,
										cattipodescripcion,
										glcodebydelivery
										)
                                       VALUES (
                                       '" . $_POST['CategoryID'] . "',
                                       '" . $_POST['StockType'] . "',
                                       '" . $_POST['CategoryDescription'] . "',
                                       '" . $_POST['textimage'] . "',
                                       '" . $_POST['image'] . "',
                                       '" . $_POST['StockAct'] . "',
                                       '" . $_POST['AdjGLAct'] . "',
                                       '" . $_POST['PurchPriceVarAct'] . "',
                                       '" . $_POST['MaterialUseageVarAc'] . "',
								       '" . $_POST['UseInternal'] . "',
				                                       '" . $_POST['WIPAct'] . "',
								       " . $_POST['allowNarrativePOLine'] . ",
								       " . 0 . ",
								       ". $_POST['margenautcost'].",		
								       " . $_POST['Prodlineid'].",
								       " . $_POST['margensales'].",
								       " . $_POST['warrantycost'].",
								       " .$afecto.",
								       " .$changeprecio.",
								       " .$flujo.",
								       " .$cashdiscount.",
								       " .$_POST['deductibleflag'].",
								       " .$_POST['u_typeoperation'].",
								       " .$_POST['typeoperationdiot'].",
								        ".$discountInPriceListOnPrice .",
									  ".$discountInComercialOnPrice.",
									  '".$_POST['cattipodescripcion']."',
									  '" . $_POST['glcodebydelivery'] . "'			
				       )";
        $ErrMsg = _('No se pudo insertar la nueva Categoria de Inventario') . $_POST['CategoryDescription'] . _('porque');
        $result = DB_query($sql,$db,$ErrMsg);
		prnMsg(_('Un nuevo registro de Categoria de Inventario se ha agregado para') . ' ' . $_POST['CategoryDescription'],'success');

		}
		// Se agrega validacion de listas de precios
		
		$SQL = 'DELETE FROM  salespricesbycategory WHERE categoryid="'.$_POST['CategoryID'].'"';
		//echo $SQL;
		$Result = DB_query($SQL,$db);
		
		$sql="select typeabbrev
					from salestypes";
		$Resultprice = DB_query($sql,$db);
		while ($myrowprices=DB_fetch_array($Resultprice)){
			if ($_POST['lista_' . $myrowprices['typeabbrev']] == 1){
		
				$IncrementPercentage=$_POST[$myrowprices['typeabbrev']]/100;
				if ($IncrementPercentage>0){
					$SQL = "INSERT INTO  salespricesbycategory(categoryid,percent,typeabbrev)
	        				VALUES('".$_POST['CategoryID']."','".$IncrementPercentage."','".$myrowprices['typeabbrev']."')";
					//echo $SQL;
					$Result = DB_query($SQL,$db);
					 
				}
			}
		}
		
	}
	//run the SQL from either of the above possibilites
	
	unset($_POST['CategoryID']);
	unset($_POST['StockType']);
	unset($_POST['CategoryDescription']);
	unset($_POST['textimage']);
	unset($_POST['image']);
	unset($_POST['StockAct']);
	unset($_POST['AdjGLAct']);
	unset($_POST['PurchPriceVarAct']);
	unset($_POST['MaterialUseageVarAc']);
	unset($_POST['WIPAct']);
	unset($_POST['allowNarrativePOLine']);
	unset($_POST['Prodlineid']);
	unset($_POST['UseInternal']);
	unset($_POST['margensales']);
	unset($_POST['warrantycost']);
	unset($_POST['deductibleflag']);
	unset($_POST['u_typeoperation']);
	unset($_POST['typeoperationdiot']);
	unset($_POST['cattipodescripcion']);
	unset($_POST['glcodebydelivery']);


} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'StockMaster'

	$sql= "SELECT COUNT(*) FROM stockmaster WHERE stockmaster.categoryid='$SelectedCategory'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg(_('No se pudo eliminar esta Categoria de Inventario porque Productos han sido creados usando esta categoria') .
			'<br> ' . _('Existen') . ' ' . $myrow[0] . ' ' . _('productos haciendo referencia a esta Categoria de Inventarios'),'warn');

	} else {
		$sql = "SELECT COUNT(*) FROM salesglpostings WHERE stkcat='$SelectedCategory'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg(_('No se puede eliminar esta Categoria de Inventarios porque se esta utilizando por VENTAS') . ' - ' . _('Integracion Contable') . '. ' . _('Elimine los registros en la Interfase Contable de Ventas que utilizan esta Categoria de Inventarios primero'),'warn');
		} else {
			$sql = "SELECT COUNT(*) FROM cogsglpostings WHERE stkcat='$SelectedCategory'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0]>0) {
				prnMsg(_('No se puede eliminar esta Categoria de Inventarios porque esta utilizandose por COSTO DE VENTAS') . ' - ' . _('Integracion Contable') . '. ' . _('Elimine los registros en la Interfase Contable de Costos que utilizan esta Categoria de Inventarios primero'),'warn');
			} else {
				$sql="DELETE FROM stockcategory WHERE categoryid='$SelectedCategory'";
				$result = DB_query($sql,$db);
				prnMsg(_('La Categoria de Inventarios') . ' ' . $SelectedCategory . ' ' . _('ha sido eliminada') . ' !','success');
				unset ($SelectedCategory);
			}
		}
		
	} //end if stock category used in debtor transactions
}

if (!isset($SelectedCategory) or $_GET['exportExcel']=='yes') {

	if ($_GET['exportExcel']=='yes') {
		header("Content-type: application/ms-excel");
		# replace excelfile.xls with whatever you want the filename to default to
		header("Content-Disposition: attachment; filename=MantenimientoCategoriasInventario.xls");
		header("Pragma: no-cache");
		header("Expires: 0");
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
				"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
	
	
	
	}
	
/* It could still be the second time the page has been run and a record has been selected for modification - SelectedCategory will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of stock categorys will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT categoryid,
			categorydescription,
			stocktype,
			stockact,
			adjglact,
			purchpricevaract,
			materialuseagevarac,
			wipact,
			adjglacttransf,
			allowNarrativePOLine,
			margenaut,
			stockcategory.prodLineId,
			ProdLine.Description,
			redinvoice,
			prdflujos.flujo as flujo,
			disabledprice,
			internaluse,
			cashdiscount,
			minimummarginsales,
			warrantycost,
			case when deductibleflag = 1 then 'SI' else 'NO' end as deductibleflag,
			stockcategory.u_typeoperation,
			accountingtransactiontype.typeoperation,
			IFNULL(typeoperationdiot.typeoperation,'') as typeoperationdiot,
			ProdLine.textimage,
			ProdLine.image
			
		FROM stockcategory
		left join typeoperationdiot ON stockcategory.typeoperationdiot = typeoperationdiot.u_typeoperation
		left join ProdLine on stockcategory.prodLineId=ProdLine.Prodlineid
		left join prdflujos on stockcategory.idflujo=prdflujos.idflujo
		left join accountingtransactiontype ON stockcategory.u_typeoperation = accountingtransactiontype.u_typeoperation
		WHERE stocktype<>'".'A'."'";
	//echo $sql;
	$result = DB_query($sql,$db);

	echo "<br><table border=1 style='text-align:center; margin:0 auto;'>\n";
	echo '<tr><th>' . _('Codigo') . '</th>
            <th>' . _('Descripcion') . '</th>
            <th>' . _('Tipo') . '</th>
            <th>' . _('Cta INVENTARIO') . '</th>
            <th>' . _('Cta AJUSTES INVENTARIO') . '</th>
            <th>' . _('Cta FACT USO INTERNO') . '</th>
	    <th>' . _('Linea Producto') . '</th>
            <th>' . _('Texto en Pedido Venta') . '</th>
	    <th>' . _('Margen Aut') . '</th>
	    <th>' . _('Facturar s/exist') . '</th>
	    <th>' . _('Mod. Precio') . '</th>
	    <th>' . _('Flujo') . '</th>
	     <th>' . _('Margen Ventas') . '</th>
	     <th>' . _('Aplica<br>IETU') . '</th>
	     <th>' . _('Deduccion<br>Autorizada') . '</th>
	     <th>' . _('Tipo<br>Operacion<br>DIOT') . '</th>
	     <th>' . _('Texto Imagen') . '</th>
	     <th>' . _('Imagen') . '</th>
	      <th></th>
	    </tr>';

	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		//=$myrow['idflujo'];
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		if($myrow[13]==1){
			$redinvoice="Si";
		}else{
			$redinvoice="No";
		}
		if($myrow[14]!= ''){
			$flujo=$myrow[14];
		}else{
			$flujo="No";
		}
		if($myrow[15]== 1){
			$changeprecio="Si";
		}else{
			$changeprecio="No";
		}
		if($_GET['exportExcel']!='yes'){
			$mod="<td><a href=\"%sSelectedCategory=%s\">" . _('Modificar') . "</td>";
		}else{
			$mod='<td></td>';
		}
		printf("<td>%s</td>
            		<td>%s</td>
            		<td>%s</td>
            		<td align=right>%s</td>
            		<td align=right>%s</td>
            		<td align=right>%s</td>
			<td align=right>%s</td>
			<td align=right>%s</td>
			<td style='text-align:center'>%s</td>
			<td style='text-align:center'>%s</td>
			<td align=right>%s</td>
			<td align=right>%s</td>
			<td style='text-align:center'>%s</td>
			<td style='text-align:center'>%s</td>
			<td style='text-align:center'>%s</td>
			<td style='text-align:center'>%s</td>
			<td>%s</td>
			<td>%s</td>
            		".$mod."
            		</tr>",
            		$myrow[0],
            		$myrow[1],
            		$myrow[2],
            		$myrow['stockact'],
            		$myrow['adjglact'],
            		$myrow['internaluse'],
			$myrow[12],
			$myrow[9],
			$myrow[10].' %',
			$redinvoice,
			$changeprecio,
			$flujo,
			$myrow[18].' %',
			$myrow[20],
			$myrow[22],
			$myrow[23],
			$myrow[24],
			empty($myrow[25]) ? 'Sin Imagen' : "<a target='_blank' href='" . $myrow[25] . "'>Imagen</a>",
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow[0]
            		);
            		#$_SERVER['PHP_SELF'] . '?' . SID,
            		#$myrow[0]);
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!

?>

<p>
<?php
if (isset($SelectedCategory)) {  ?>
	<div class='centre'><a href="<?php echo $_SERVER['PHP_SELF'] . '?' . SID;?>"><?php echo _('Muestra todas las Categorias de Inventario'); ?></a></div>
<?php } ?>

<p>

<?php

if (!isset($_GET['delete']) and $_GET['exportExcel']!='yes') {

	echo '<form name="CategoryForm" method="post" enctype="multipart/form-data" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';

	if (isset($SelectedCategory)) {
		//editing an existing stock category
		if (!isset($_POST['UpdateTypes'])) {
			$sql = "SELECT categoryid,
                   	stocktype,
                   	categorydescription,
 					textimage,
 					image,
                   	stockact,
                   	adjglact,
                   	purchpricevaract,
                   	materialuseagevarac,
                   	wipact,
					allowNarrativePOLine,
					margenaut,
 					margenautcost,
					prodLineId,
					redinvoice,
					idflujo,
					disabledprice,
					internaluse,
					cashdiscount,
					minimummarginsales,
					warrantycost,
					deductibleflag,
					u_typeoperation,
 					discountInPriceListOnPrice,
					discountInComercialOnPrice,
 					cattipodescripcion,
 					glcodebydelivery,
 					generaPublicacionAutomatica
                   FROM stockcategory
                   WHERE categoryid='" . $SelectedCategory . "'";

			$result = DB_query($sql, $db);
			$myrow = DB_fetch_array($result);

			$_POST['CategoryID'] = $myrow['categoryid'];
			$_POST['StockType']  = $myrow['stocktype'];
			$_POST['CategoryDescription']  = $myrow['categorydescription'];
			$_POST['textimage']  = $myrow['textimage'];
			$_POST['image']  = $myrow['image'];
			$_POST['StockAct']  = $myrow['stockact'];
			$_POST['AdjGLAct']  = $myrow['adjglact'];
			$_POST['PurchPriceVarAct']  = $myrow['purchpricevaract'];
			$_POST['MaterialUseageVarAc']  = $myrow['materialuseagevarac'];
			$_POST['WIPAct']  = $myrow['wipact'];
			$_POST['allowNarrativePOLine']  = $myrow['allowNarrativePOLine'];
			$_POST['margenaut']  = $myrow['margenaut'];
			$_POST['margenautcost']  = $myrow['margenautcost'];
			$_POST['Prodlineid'] = $myrow['prodLineId'];
			$_POST['afecto']= $myrow['redinvoice'];
			$_POST['changeprecio']= $myrow['disabledprice'];
			$_POST['flujo']= $myrow['idflujo'];
			$_POST['UseInternal']= $myrow['internaluse'];
			$_POST['cashdiscount']= $myrow['cashdiscount'];
			$_POST['margensales']= $myrow['minimummarginsales'];
			$_POST['warrantycost']= $myrow['warrantycost'];
			$_POST['deductibleflag']= $myrow['deductibleflag'];
			$_POST['u_typeoperation']= $myrow['u_typeoperation'];
			$discountInPriceListOnPrice = $myrow['discountInPriceListOnPrice'];
			$discountInComercialOnPrice = $myrow['discountInComercialOnPrice'];
			$_POST['cattipodescripcion'] = $myrow['cattipodescripcion'];
			$_POST['glcodebydelivery'] = $myrow['glcodebydelivery'];
			$_POST['generaPublicacionAutomatica'] = $myrow['generaPublicacionAutomatica'];
			
		}
		echo '<input type=hidden name="SelectedCategory" value="' . $SelectedCategory . '">';
		echo '<input type=hidden name="CategoryID" value="' . $_POST['CategoryID'] . '">';
		echo '<table style="text-align:center; margin:0 auto;"><tr><td>' . _('Codigo de Categoria de Inv.') . ':</td><td>' . $_POST['CategoryID'] . '</td></tr>';

	} else { //end of if $SelectedCategory only do the else when a new record is being entered
		if (!isset($_POST['CategoryID'])) {
			$_POST['CategoryID'] = '';
		}
		echo '<table border="1" style="text-align:center; margin:0 auto;"><tr><td>' . _('Codigo de Categoria de Inv.') . ':</td>
                             <td><input type="Text" name="CategoryID" size=7 maxlength=6 value="' . $_POST['CategoryID'] . '"></td></tr>';
	}

	//SQL to poulate account selection boxes
	$sql = "SELECT accountcode,
                 concat(accountcode,' - ',accountname) as accountname 
                 FROM chartmaster,
                      accountgroups
                 WHERE chartmaster.group_=accountgroups.groupname and
                       accountgroups.pandl=0
                 ORDER BY accountcode";

	$BSAccountsResult = DB_query($sql,$db);

	$sql = "SELECT accountcode,
                 concat(accountcode,' - ',accountname) as accountname 
                 FROM chartmaster,
                      accountgroups
                 WHERE chartmaster.group_=accountgroups.groupname and
                       accountgroups.pandl!=0
                 ORDER BY accountcode";

	$PnLAccountsResult = DB_query($sql,$db);

	if (!isset($_POST['CategoryDescription'])) {
		$_POST['CategoryDescription'] = '';
	}
	
	echo '<tr><td>' . _('Descripcion') . ':</td>
            <td><input type="Text" name="CategoryDescription" size=32 maxlength=50 value="' . $_POST['CategoryDescription'] . '"></td></tr>';
	
	
	echo '<tr><td>' . _('Texto Imagen') . ':</td>
            <td><textarea name="textimage" cols="30">' . $_POST['textimage'] . '</textarea></td></tr>';
	
	echo '<tr><td>' . _('Imagen') . ':</td>
            <td>';
	
	if(empty($_POST['image']) == FALSE) {
		echo '<img src="' . $_POST['image'] . '" alt="imagen" /><br />';
	}
		
	echo '<input type="file" name="image" size="32" /></td></tr>';

	echo '<tr><td>' . _('Margen Automatico') . ':</td>
            <td><input type="Text" name="margenaut" size=10 maxlength=20 value="' . $_POST['margenaut'] . '">'._(' (Valores del 0 al 100)').'</td></tr>';

	echo '<tr><td>' . _('Margen Automatico en costo') . ':</td>
            <td><input type="Text" name="margenautcost" size=10 maxlength=20 value="' . $_POST['margenautcost'] . '">'._(' (Valores del 0 al 100)').'</td></tr>';
	
	
	echo '<tr><td>' . _('Margen de Venta Minimo') . ':</td>
            <td><input type="Text" name="margensales" size=10 maxlength=20 value="' . $_POST['margensales'] . '">'._(' (Valores del 0 al 100)').'</td></tr>';
	echo '<tr><td>' . _('Descuento De Remision') . ':</td>
            <td><input type="Text" name="cashdiscount" size=10 maxlength=20 value="' . $_POST['cashdiscount'] . '">'._(' (Valores del 0 al 100)').'</td></tr>';
	echo '<tr><td>' . _('Incremento de Costo Por Garantia') . ':</td>
            <td><input type="Text" name="warrantycost" size=10 maxlength=20 value="' . $_POST['warrantycost'] . '">'._(' (Valores del 0 al 100)').'</td></tr>';
	    
	echo '<tr><td>' . _('Tipo Producto') . ':</td>
            <td><select name="StockType" onChange="ReloadForm(CategoryForm.UpdateTypes)" >';
		if (isset($_POST['StockType']) and $_POST['StockType']=='F') {
			echo '<option selected value="F">' . _('Productos Terminados');
		} else {
			echo '<option value="F">' . _('Productos Terminados');
		}
		if (isset($_POST['StockType']) and $_POST['StockType']=='M') {
			echo '<option selected value="M">' . _('Materia Prima');
		} else {
			echo '<option value="M">' . _('Materia Prima');
		}
		if (isset($_POST['StockType']) and $_POST['StockType']=='D') {
			echo '<option selected value="D">' . _('Dummy Item - (No Movements)');
		} else {
			echo '<option value="D">' . _('Dummy Item - (No Movements)');
		}
		if (isset($_POST['StockType']) and $_POST['StockType']=='L') {
			echo '<option selected value="L">' . _('Servicios');
		} else {
			echo '<option value="L">' . _('Servicios');
		}

	echo '</select></td></tr>';
	
	echo '<td>' . _('Selecciona la Linea:') . '</td>
	<td><select Name="Prodlineid">';
	  echo '<option VALUE="0"></option>';
	$sql = "SELECT * FROM ProdLine order by Description";
	$categoria = DB_query($sql,$db);
	while ($myrowcategoria=DB_fetch_array($categoria,$db)){
            $categoria_base=$myrowcategoria['Prodlineid'];
            if ($_POST['Prodlineid']==$categoria_base){ 
                echo '<option  VALUE="' . $myrowcategoria['Prodlineid'] .  '  " selected>' .ucwords(strtolower($myrowcategoria['Description']));
            }else{
                echo '<option  VALUE="' . $myrowcategoria['Prodlineid'] .  '" >' .ucwords(strtolower($myrowcategoria['Description']));
            }
	}
	echo '</select>';
	if (!isset($Prodlineid)){
	echo ' <a href="ABCProductLines.php?pagina=Stock">Agregar nueva Linea</a>';
	}
	echo '</td></tr>';
	echo '<tr><td>' . _('Descripcion en Pedidos Venta') . ':</td>
            <td><select name="allowNarrativePOLine">';
		if (isset($_POST['allowNarrativePOLine']) and $_POST['allowNarrativePOLine']==1) {
			echo '<option selected value=1>' . _('Permitir Texto Narrativo en Pedido Venta');
		} else {
			echo '<option value=1>' . _('Permitir Texto Narrativo en Pedido Venta');
		}
		if (isset($_POST['allowNarrativePOLine']) and $_POST['allowNarrativePOLine']==0) {
			echo '<option selected value=0>' . _('Sin Texto Narrativo');
		} else {
			echo '<option value=0>' . _('Sin Texto Narrativo');
		}

	echo '</select></td></tr>';
	echo '<tr><td>'._('Tipo descripcion del producto a mostrar:').':</td>';
	echo '<td><select name="cattipodescripcion">';
	if($_POST['cattipodescripcion'] == 0 ){
		echo '<option selected value = 0>Descripcion Corta</option>';
		echo '<option value = 1>Descripcion Larga</option>';
	}elseif($_POST['cattipodescripcion'] == 1){
		echo '<option selected value = 1>Descripcion Larga</option>';
		echo '<option  value = 0>Descripcion Corta</option>';
	}else{
		echo '<option selected value = 0>Descripcion Corta</option>';
		echo '<option value = 1>Descripcion Larga</option>';
	}
	echo '<input type="submit" name="UpdateTypes" style="visibility:hidden;width:1px" value="Not Seen">';
	if (isset($_POST['StockType']) and $_POST['StockType']=='L') {
		$Result = $PnLAccountsResult;
		echo '<tr><td>' . _('Cuenta de Inventarios');//Cuenta de Recuperacion
	} else {
		$Result = $BSAccountsResult;
		echo '<tr><td>' . _('Cuenta de Inventarios');
	}
	echo ':</td><td><select name="StockAct">';
	
	while ($myrow = DB_fetch_array($Result)){
	
		if (isset($_POST['StockAct']) and $myrow['accountcode']==$_POST['StockAct']) {
			echo '<option selected value=';
		} else {
			echo '<option value=';
		}
		echo $myrow['accountcode'] . '>' . $myrow['accountname']; // . ' ('.$myrow['accountcode'].')';desarrolloLO QUITE PUES YA AGREGAMOS A DESCRIPCION EL CODIGO
	} //end while loop
	DB_data_seek($PnLAccountsResult,0);
	DB_data_seek($BSAccountsResult,0);
	echo '</select></td></tr>';
	echo '<tr><td>' . _('Cuenta de Trabajos en Proceso') . ':</td><td><select name="WIPAct">';

	while ($myrow = DB_fetch_array($BSAccountsResult)) {
	
		if (isset($_POST['WIPAct']) and $myrow['accountcode']==$_POST['WIPAct']) {
			echo '<option selected value=';
		} else {
			echo '<option value=';
		}
		echo $myrow['accountcode'] . '>' . $myrow['accountname']; // . ' ('.$myrow['accountcode'].')';desarrolloLO QUITE PUES YA AGREGAMOS A DESCRIPCION EL CODIGO

	} //end while loop
	echo '</select></td></tr>';
	
	DB_data_seek($BSAccountsResult,0);

	echo '<tr><td>' . _('Cuenta de Ajustes de Inventario') . ':</td>
            <td><select name="AdjGLAct">';

	while ($myrow = DB_fetch_array($PnLAccountsResult)) {
		if (isset($_POST['AdjGLAct']) and $myrow['accountcode']==$_POST['AdjGLAct']) {
			echo '<option selected value=';
		} else {
			echo '<option value=';
		}
		echo $myrow['accountcode'] . '>' . $myrow['accountname']; // . ' ('.$myrow['accountcode'].')';desarrolloLO QUITE PUES YA AGREGAMOS A DESCRIPCION EL CODIGO

	} //end while loop
	DB_data_seek($PnLAccountsResult,0);
	echo '</select></td></tr>';
	
	echo '<tr><td>' . _('Cuenta de Variaciones de Costo') ;
          
          
           echo' </td><td style="text-align:center;font-size:1px">';
           echo '(Utilizada en Ordenes de Trabajo y
               cuando el precio de la factura de compra es diferente que
                       el costo de la recepcion de compra)' . '
            <br>';
           echo '<select name="PurchPriceVarAct">';

	while ($myrow = DB_fetch_array($PnLAccountsResult)) {
		if (isset($_POST['PurchPriceVarAct']) and $myrow['accountcode']==$_POST['PurchPriceVarAct']) {
			echo '<option selected value=';
		} else {
			echo '<option value=';
		}
		echo $myrow['accountcode'] . '>' . $myrow['accountname']; // . ' ('.$myrow['accountcode'].')';desarrolloLO QUITE PUES YA AGREGAMOS A DESCRIPCION EL CODIGO

	} //end while loop

	DB_data_seek($PnLAccountsResult,0);

	echo '</select></td></tr><tr><td>';
	if (isset($_POST['StockType']) and $_POST['StockType']=='L') {
		echo  _('Cuenta de Variaciones de Eficiencia de Mano de Obra');
	} else {
		echo  _('Cuenta de Variaciones de Uso Manufactura');
	}
	echo ':</td><td><select name="MaterialUseageVarAc">';

	while ($myrow = DB_fetch_array($PnLAccountsResult)) {
		if (isset($_POST['MaterialUseageVarAc']) and $myrow['accountcode']==$_POST['MaterialUseageVarAc']) {
			echo '<option selected value=';
		} else {
			echo '<option value=';
		}
		echo $myrow['accountcode'] . '>' . $myrow['accountname']; // . ' ('.$myrow['accountcode'].')';desarrolloLO QUITE PUES YA AGREGAMOS A DESCRIPCION EL CODIGO

	} //end while loop
	//DB_free_result($PnLAccountsResult);
	echo '</select></td></tr>';
	
	// Cuentas de gastos de uso interno
	DB_data_seek($PnLAccountsResult,0);
	echo '<tr><td>'	;
	echo  _('Cuenta de Facturas de Uso Interno');
	
	echo ':</td><td><select name="UseInternal">';
	
	while ($myrow = DB_fetch_array($PnLAccountsResult)) {
		if (isset($_POST['UseInternal']) and $myrow['accountcode']==$_POST['UseInternal']) {
			echo '<option selected value=';
		} else {
			echo '<option value=';
		}
		echo $myrow['accountcode'] . '>' . $myrow['accountname']; // . ' ('.$myrow['accountcode'].')';desarrolloLO QUITE PUES YA AGREGAMOS A DESCRIPCION EL CODIGO

	} //end while loop
	//DB_free_result($PnLAccountsResult);
	echo '</select></td></tr>';
	
	// Cuentas de gastos de uso interno
	DB_data_seek($PnLAccountsResult,0);
	echo '<tr><td>'	;
	echo  _('Cuenta de Material por Entregar');
	
	echo ':</td><td><select name="glcodebydelivery">';
	
	while ($myrow = DB_fetch_array($PnLAccountsResult)) {
		if (isset($_POST['glcodebydelivery']) and $myrow['accountcode']==$_POST['glcodebydelivery']) {
			echo '<option selected value=';
		} else {
			echo '<option value=';
		}
		echo $myrow['accountcode'] . '>' . $myrow['accountname']; // . ' ('.$myrow['accountcode'].')';desarrolloLO QUITE PUES YA AGREGAMOS A DESCRIPCION EL CODIGO
	
	} //end while loop
	DB_free_result($PnLAccountsResult);
	echo '</select></td></tr>';
	
	
	echo '<tr><td>' . _('Afecta en Inventario') . '</td>';
	echo '<td>';
		if($_POST['afecto']!="" and $_POST['afecto']=="1"){
			echo '<input type="checkbox" name="afecto" value="1" checked>';	
		}else{
			echo '<input type="checkbox" name="afecto" value="0">';
		}
	echo '</td></tr>';
	/*permite modificar precios*/
	echo '<tr><td>' . _('Permite Modificar Precios en Venta') . '</td>';
	echo '<td>';
		if($_POST['changeprecio']!="" and $_POST['changeprecio']=="1"){
			echo '<input type="checkbox" name="changeprecio" value="1" checked>';	
		}else{
			echo '<input type="checkbox" name="changeprecio" value="0">';
		}
	echo '</td></tr>';
	/// LISTADO DE FLUJOS EXISTENTES ////
	echo '</select></td></tr>';
	echo '<tr><td>' . _('Flujo:') . '</td>';
	echo'<td><select Name="flujo"><br>';
	$sql= "SELECT idflujo, flujo FROM prdflujos";
	$selectflujo = DB_query($sql,$db);
	echo '<option  VALUE="" selected>Ninguno ';
	while ($myrowflujo=DB_fetch_array($selectflujo,$db)){
           $idflujo=$myrowflujo['idflujo'];
            if ($_POST['flujo']==$idflujo){ 
                echo '<option  VALUE="' . $myrowflujo['idflujo'] .  '  " selected>' .$myrowflujo['flujo'];
            }else{
                echo '<option  VALUE="' . $myrowflujo['idflujo'] .  '" >' .$myrowflujo['flujo'];
            }
	}
	echo '</td></tr>';
	//// FIN  LISTADO DE FLUJOS EXISTENTES/////
	
	/***FCC 09-AGOSTO-2011
	SECCION CONFIGURACION PARA IETU**/
	
	
	
	echo "<td>" . _('Deducible IETU') . ":</td>
		<td><input type='checkbox' name='deductibleflag' " ;
		if($_POST['deductibleflag'] == 1){echo 'checked' ;}
	echo "></td></tr>";
	
	echo '<tr>
		<td>' . _('Deducciones Autorizadas') . ' :</td>
		<td>';
			//Cuentas Contables
			$SQL = "SELECT *
				FROM accountingtransactiontype
				ORDER BY typeoperation";
				
			echo '<select name="u_typeoperation">';
			echo "<option selected value='0'>SELECCIONA...</option>";
			//echo $SQL;
			$result=DB_query($SQL,$db);
			while ($myrow=DB_fetch_array($result)){
				
				if (isset($_POST['u_typeoperation']) and $_POST['u_typeoperation']==$myrow["u_typeoperation"]){
					echo '<option selected value=' . $myrow['u_typeoperation'] . '>'. $myrow['typeoperation'] . '</option>';
				} else {
					echo '<option value=' . $myrow['u_typeoperation'] . '>'.$myrow['typeoperation'] . '</option>';
				}
			}
			echo '</select>
		</td>';
	echo '</tr>';
	
	echo '<tr>
		<td>' . _('Tipo de Operaci�n DIOT') . ' :</td>
		<td>';
			//Cuentas Contables
			$SQL = "SELECT *
				FROM typeoperationdiot
				ORDER BY u_typeoperation";
				
			echo '<select name="typeoperationdiot">';
			echo "<option selected value='0'>SELECCIONA...</option>";
			//echo $SQL;
			$result=DB_query($SQL,$db);
			while ($myrow=DB_fetch_array($result)){
				
				if (isset($_POST['typeoperationdiot']) and $_POST['typeoperationdiot']==$myrow["u_typeoperation"]){
					echo '<option selected value=' . $myrow['u_typeoperation'] . '>'. $myrow['typeoperation'] . '</option>';
				} else {
					echo '<option value=' . $myrow['u_typeoperation'] . '>'.$myrow['typeoperation'] . '</option>';
				}
			}
			echo '</select>
		</td>';
	echo '</tr>';
	
	$chk = "";
	if ($discountInPriceListOnPrice==1)
		$chk = "checked";
	
	echo '<tr>
			<td> '._('Descuento de Lista de Precio sobre el precio').'</td>
			<td><input '.$chk.' type="checkbox" name="descLPonPrice"></td>			
		</tr>';
	
	$chk = "";
	if ($discountInComercialOnPrice==1)
		$chk = "checked";
	
	
	echo '<tr>
			<td> '._('Descuento Comercial sobre el precio').'</td>
			<td><input '.$chk.' type="checkbox" name="descCOMonPrice"></td>
		</tr>';
	
	if($_POST['generaPublicacionAutomatica'] == 1){
		$chk = "checked";
	}else{
		$chk = "";
	}
	echo '<tr>';
	echo '<td>'._('Generacion Automatica').'</td>';
	echo '<td>';
	
		echo'<input type="checkbox" '.$chk.' name="generaPublicacionAutomatica" value="1">';

	echo '</tr>';
	
	
	/***FIN FCC 09-AGOSTO-2011
	SECCION CONFIGURACION PARA IETU**/
	$SQL = 'SELECT salestypes.sales_type, salestypes.typeabbrev,salespricesbycategory.percent*100 as percent,salespricesbycategory.typeabbrev as valida
			FROM salestypes left join salespricesbycategory
			ON salespricesbycategory.typeabbrev=salestypes.typeabbrev
			AND salespricesbycategory.categoryid="'.$SelectedCategory.'"
	
	order by  sales_type asc ';
	//echo $SQL;
	$PricesResult = DB_query($SQL,$db);
	
	echo '<tr>
                            <td colspan=2 style=text-align:center;>
						<b>' . _('Listas de precios Aumento Porcentual (positivo) o decremento (negativo) de 0 a 100') .'</b>';
	echo '<table border=1 align=center>';
	echo '<tr align=center style="background-color:#f2fcbd;text-align:center;">';
	while ($PriceLists=DB_fetch_array($PricesResult)){
		echo "<td style='text-align:center;' title='".$PriceLists['sales_type']."'>";
		if(is_null($PriceLists['valida'])) {
			echo "<input type='checkbox' name='lista_" . $PriceLists['typeabbrev'] . "' value='1' >";
		}else{
			echo "<input type='checkbox' name='lista_" . $PriceLists['typeabbrev'] . "' value='1' checked>";
		}
		echo $PriceLists['typeabbrev'];
		echo "</td>";
	}
	DB_data_seek($PricesResult,0);
	echo '</tr>';
	echo '<tr>';
	while ($PriceLists=DB_fetch_array($PricesResult)){
		if (!isset($_POST[$PriceLists['typeabbrev']]))
		{
			$_POST[$PriceLists['typeabbrev']]=0;
		}
		$_POST[$PriceLists['typeabbrev']]=$PriceLists['percent'];
		echo "<td><input type=text name='".$PriceLists['typeabbrev']."' class=number size=4 maxlength=4 VALUE=" . number_format($_POST[$PriceLists['typeabbrev']],2) . "></td>";
	}
	DB_data_seek($PricesResult,0);
	echo '</tr>';
	
	
	echo '</table>';
	echo '</td></tr>';
	echo '</table>';

	if (isset($SelectedCategory)) {
		//editing an existing stock category

		$sql = "SELECT stkcatpropid,
				label,
				controltype,
				defaultvalue,
				reqatsalesorder
                   FROM stockcatproperties
                   WHERE categoryid='" . $SelectedCategory . "'
                   ORDER BY stkcatpropid";

		$result = DB_query($sql, $db);

/*		echo '<br>Number of rows returned by the sql = ' . DB_num_rows($result) .
			'<br>The SQL was:<br>' . $sql;
*/
		echo '<hr><table style="text-align:center; margin:0 auto;">';
		$TableHeader = '<tr><th>' . _('Etiqueta de Propiedad') . '</th>
						<th>' . _('Tipo de Control') . '</th>
						<th>' . _('Valor por Omision') . '</th>
						<th>' . _('Requerido en Orden de Venta') . '</th>
					</tr>';
		echo $TableHeader;
		$PropertyCounter =0;
		$HeadingCounter =0;
		while ($myrow = DB_fetch_array($result)) {
			if ($HeadingCounter>15){
				echo $TableHeader;
				$HeadingCounter=0;
			} else {
				$HeadingCounter++;
			}
			echo '<input type="hidden" name="PropID' . $PropertyCounter .'" value=' . $myrow['stkcatpropid'] . '>';
			echo '<tr><td><input type="textbox" name="PropLabel' . $PropertyCounter . '" size="50" maxlength="100" value="' . $myrow['label'] . '"></td>';
				  
				  
			$sql2 = "SELECT seltypeid,
					selTitle,
					selTableName
			   FROM stockcatSelTypes";
	
			$result2 = DB_query($sql2, $db);
			
			echo '<td><select name="PropControlType' . $PropertyCounter . '">';
			while ($myrow2 = DB_fetch_array($result2)) {
				if ($myrow['controltype']==$myrow2['seltypeid']){
					echo '<option selected value='. $myrow2['seltypeid'] .'>' . $myrow2['selTitle'] . '</option>';
				} else {
					echo '<option value='. $myrow2['seltypeid'] .'>' . $myrow2['selTitle'] . '</option>';
				}	
			}			
			echo '</select></td>
				<td><input type="hidden" name="PropDefault' . $PropertyCounter . '" value="' . $myrow['defaultvalue'] . '"></td>
				<td align="center"><input type="checkbox" name="PropReqSO' . $PropertyCounter .'"';

			if ($myrow['reqatsalesorder']==1){
				echo 'checked';
			} else {
				echo '';
			}

			echo '></td>
					<td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&DeleteProperty=' . $myrow['stkcatpropid'] .'&SelectedCategory=' . $SelectedCategory . '" onclick=\'return confirm("' . _('Estas seguro de querer borrar esta propiedad? Todas las propiedades de este tipo para todos los productos seran eliminadas.') . '");\'>' . _('Eliminar') . '</td></tr>';

			$PropertyCounter++;
		} //end loop around defined properties for this category
		
		/* LINEA VACIA PARA AGREGAR MAS PROPIEDADES ESPECIFICAS PARA ESTA CATEGORIA */
		echo '<input type="hidden" name="PropID' . $PropertyCounter .'" value="NewProperty">';
		
		echo '<tr><td><input type="textbox" name="PropLabel' . $PropertyCounter . '" size="50" maxlength="100"></td>';
		
			$sql2 = "SELECT seltypeid,
					selTitle,
					selTableName
			   FROM stockcatSelTypes";
	
			$result2 = DB_query($sql2, $db);
			
			echo '<td><select onchange="f(this);" name="PropControlType' . $PropertyCounter . '">';
			while ($myrow2 = DB_fetch_array($result2)) {
				if ($myrow['controltype']==$myrow2['seltypeid']){
					echo '<option selected value='. $myrow2['seltypeid'] .'>' . $myrow2['selTitle'] . '</option>';
				} else {
					echo '<option value='. $myrow2['seltypeid'] .'>' . $myrow2['selTitle'] . '</option>';
				}	
			}			
			echo '</select>';
			
			
			
				/*****Lista de categorias para asignar padre****/
			
			$sql3 = "select * from stockcatproperties where controltype=8";
			
			$result3 = DB_query($sql3, $db);
				
			echo '<select style="display:none" id="PropControlTypePadre" name="PropControlTypePadre">';
					echo '<option selected value="0">Raiz</option>';	
			while ($myrow3 = DB_fetch_array($result3)) {
				//if ($myrow['controltype']==$myrow3['seltypeid']){
					echo '<option  value='. $myrow3['stkcatpropid'] .'>' . $myrow3['label'] . '</option>';
			/*	} else {
					echo '<option value='. $myrow2['seltypeid'] .'>' . $myrow2['selTitle'] . '</option>';
				}*/
			}
			echo '</select></td>';
			
			   
				/***********fin***********************/
			echo'<td><input type="textbox" name="PropDefault' . $PropertyCounter . '"></td>
				<td align="center"><input type="checkbox" name="PropReqSO' . $PropertyCounter .'"></td></tr>';
		echo '</table>';
		echo '<input type=hidden name="PropertyCounter" value=' . $PropertyCounter . '>';

	} /* end if there is a category selected */
	    
	
	
	echo '<div class="centre"><input type="Submit" name="submit" value="' . _('Procesa Informacion') . '"></div>';
 

	echo '</form>';

} //end if record deleted no point displaying form to add record

if($_GET['exportExcel']!='yes'){
include('includes/footer.inc');
} 
?>
<script type="text/javascript">
function f(elemento){
	
	if(parseInt(elemento.value)==8) {
		document.getElementById('PropControlTypePadre').style.display = "block";
		
	}
	else {
		document.getElementById('PropControlTypePadre').style.display = "none";}

}
</script>

