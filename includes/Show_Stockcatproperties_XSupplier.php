<?php

/***************************************************************************************/
/* AQUI VAMOS A DESPLEGAR LOS CAMPOS EXTRAS REQUERIDOS POR LA CATEGORIA DE INVENTARIOS */

//echo DATE('d/m/Y');
$sql = "SELECT distinct stockcatproperties.stkcatpropid,
				label,
				controltype,
				stockcatSelTypes.objectType,
				stockcatSelTypes.classname,
				defaultvalue,
				reqatsalesorder,
				stockcatSelTypes.selTableName as queryofvals,
				stockcatproperties.addresslink,
				stockcatproperties.addressref,
				stockcatproperties.requiredtoprocess,
				stockcatproperties.requiredtoday
				reqatprint	
		FROM stockcatproperties join stockcatSelTypes on stockcatproperties.controltype = stockcatSelTypes.seltypeid
				/*left join salesstockproperties on
				salesstockproperties.stkcatpropid=stockcatproperties.stkcatpropid
				and salesstockproperties.orderno=".$ordenexiste."
				and salesstockproperties.typedocument in (".$typesales.")
				and salesstockproperties.orderlineno=". $lineaordeny."*/,
				stockmaster
			WHERE stockcatproperties.categoryid=stockmaster.categoryid
				AND stockmaster.stockid = '" . $StockID_Prop . "' and
				reqatsalesorder = 2
			ORDER BY stockcatproperties.Ordenar, stkcatpropid";
$result = DB_query($sql, $db,$ErrMsg,$DbgMsg,true);
echo '<table width=40% border=0 style="align:left" valign="top"  >';
$PropertyCounter=0;
$treewiew=false;
while ($myrowc = DB_fetch_array($result)) {
	$label=$myrowc['label'];
	$stkcatpropid=$myrowc['stkcatpropid'];
	$tipoobjeto=$myrowc['objectType'];
	$tipocontrol = $myrowc['controltype'];
	$requiredtoprocess = $myrowc['requiredtoprocess'];
	$requiredtoday = $myrowc['requiredtoday'];
	$labelprop = $myrowc['label'];
	$reqatprint=$myrowc['reqatprint'];
		
	$requiredHtml = "";
	if($requiredtoprocess == 1) {
		$requiredHtml = "<span style='color:#CC3232'>*</span>";
	}
		
	if($tipoobjeto=='treeview' and $treewiew==false ){
		$treewiew=true;
			
	}
	$classname = $myrowc['classname'];
	$class="";
	if ($classname != "")
		$class='class="'.$classname.'"';
		
	$valorobjeto=$myrowc['valor'];
	$lineaorden='_'.$lineaordenx;
	if (is_null($valorobjeto)){
		$valorobjeto="";
	}
	if($valorobjeto==""){
		$valorobjeto=$myrowc['defaultvalue'];
	}
	if($tipoobjeto!='treeview'){
		echo '<tr style="align:left" >';
		echo '<td nowrap  style="text-align:right">'. $requiredHtml . ' ' . $label.':</td>';
		$sqlDet = $myrowc['queryofvals'];
		$sqlDet1=explode(" ",$sqlDet);
		$sqlDet1=$sqlDet1[1];
		$sqlDetsales=$myrowc['queryofvals'];
		if($tipocontrol==5){
			$sqlDet = $sqlDet . " AND tags.tagref = '" .$_SESSION['Items'.$identifier]->Tagref . "' ORDER BY disp";
		}
			
		//echo $sqlDet;
		echo '<td >';
		echo '	<input type="hidden" name="PropDefaultval'.$lineaorden.'_'.$PropertyCounter.'" value="'.$stkcatpropid. '">
						<input type="hidden" name="tipoobjeto'.$lineaorden.'_'.$PropertyCounter.'" value="'.$tipoobjeto. '">
						<input type="hidden" name="reqatprint'.$lineaorden.'_'.$PropertyCounter.'" value="'.$reqatprint. '">		
						<input type="hidden" name="consulta'.$lineaorden.'_'.$PropertyCounter.'" value="'.$sqlDetsales. '">
						<input type="hidden" name="campo'.$lineaorden.'_'.$PropertyCounter.'" value="'.$sqlDet1. '">
						<input type="hidden" name="class'.$lineaorden.'_'.$PropertyCounter.'" value="'.$classname.'">
						<input type="hidden" name="required'.$lineaorden.'_'.$PropertyCounter.'" value="'.$requiredtoprocess.'">
						<input type="hidden" name="label'.$lineaorden.'_'.$PropertyCounter.'" value="'.$labelprop.'">
						<input type="hidden" name="requiredtoday'.$lineaorden.'_'.$PropertyCounter.'" value="'.$requiredtoday.'">';
		if (strlen($myrowc['queryofvals']) == 0) {
			if($tipoobjeto == 'checkbox'){
				if($valorobjeto == "SI"){
					echo '	<input type="'.$tipoobjeto.'" '.$class.' checked="checked"  alt="Y/m/d" name="PropDefault'.$lineaorden.'_'. $PropertyCounter .'" Value="'.$valorobjeto.'">';
				}else{
					echo '	<input type="'.$tipoobjeto.'" '.$class.' alt="Y/m/d" name="PropDefault'.$lineaorden.'_'. $PropertyCounter .'" Value="'.$valorobjeto.'">';
				}
			}else{
					
				$idprop = "";
				if($myrowc['addressref'] != 0) {
					list($idlabel) = explode(" ", $label);
					$idlabel = strtolower($idlabel);
					$idprop = 'id="' . $lineaorden . $myrowc['addressref'] . $idlabel. '"';
				}
				//
				echo '	<input ' . $idprop . ' type="'.$tipoobjeto.'" '.$class.' alt="Y/m/d" name="PropDefault'.$lineaorden.'_'. $PropertyCounter .'" Value="'.$valorobjeto.'">';
				
				if($myrowc['addresslink'] == 1) {
					$trabsel = $lineaorden . $myrowc['addressref'];
					echo '<img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _('Direcciones Sepomex') . '" alt=""> ';
					echo "<a style='display:inline' href='#' onclick='javascript:var win = window.open(\"SepomexSearch.php?idOpener=$trabsel\", \"sepomex\", \"width=500,height=500,scrollbars=1,left=200,top=150\"); win.focus();'>" . _("Seleccionar direccion") . "</a>";
				}
				}
					
					
		} else {
		$trabsel='PropDefault'.$lineaorden.'_'.$PropertyCounter;
		$trabselect=$_POST[$trabsel];
			
		$resultDet = DB_query($sqlDet, $db,$ErrMsg,$DbgMsg,true);
		echo '<select name="PropDefault'.$lineaorden.'_'.$PropertyCounter.'">';
		echo '<option value=0>Sin Valor Asignado</option>';
		
						while ($myrowDet = DB_fetch_array($resultDet)) {
						if ($myrowc['valor']==$myrowDet['value'] or $myrowDet['value']==$trabselect){
						echo '<option selected value="' . $myrowDet['value'] . '">' . $myrowDet['disp'];
						} else {
						echo '<option value="' . $myrowDet['value'] . '">' . $myrowDet['disp'];
						}
						}
						echo '</select>';
							
						}
							
						echo '</td>';
						echo '<td>';
							
						echo '</td>';
						echo '</tr>';
		}
			
		$PropertyCounter=$PropertyCounter+1;
	}
	$i+=1;
		
	echo '<tr><td>';
	if($treewiew==true){
	// Concateno los valores que previamente habia guardado
		$sql = "SELECT distinct stockcatproperties.stkcatpropid,
						label,
						controltype,
						stockcatSelTypes.objectType,
						stockcatSelTypes.classname,
						defaultvalue,
						reqatsalesorder,
						stockcatSelTypes.selTableName as queryofvals,
						reqatprint
					FROM stockcatproperties join stockcatSelTypes on stockcatproperties.controltype = stockcatSelTypes.seltypeid
						/*JOIN salesstockproperties ON salesstockproperties.stkcatpropid=stockcatproperties.stkcatpropid
							AND salesstockproperties.orderno=".$ordenexiste."
						AND salesstockproperties.typedocument in (".$typesales.")
						and reqatsalesorder = 2
						    AND salesstockproperties.orderlineno=".$lineaordeny."*/,stockmaster
					WHERE stockcatproperties.categoryid=stockmaster.categoryid
							AND stockmaster.stockid = '" .$StockID_Prop . "'
							AND stockcatproperties.controltype=4
		ORDER BY stkcatpropid";
					$result = DB_query($sql, $db,$ErrMsg,$DbgMsg,true);
					$default="/";
					$contadordef=0;
					$defaulttree='';
					while ($myrowc = DB_fetch_array($result)) {
					$label=$myrowc['label'];
					$stkcatpropid=$myrowc['stkcatpropid'];
					$defaulttree=$defaulttree.$default.$stkcatpropid.'.'.$label;
						
					}
					/***Recursion de lista de Categorias**/
					echo '
					<link rel="stylesheet" href="css/listaCategorias/style.css" type="text/css" />
				<ul id="nav">
					<li><a href="#">Categorias</a>';
						addline(0,$db,'',$lineaordenx);
				echo '
					</li>
					</ul>';
					echo '</td>
					<td>
					<input type="text" size=80 name="txtRuta'.$lineaorden.'" id="txtRuta'.$lineaorden.'" value="'.$defaulttree.'"/>
					<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js" type="text/javascript" charset="utf-8"></script>
				<script src="http://jquery-ui.googlecode.com/svn/tags/latest/ui/jquery.effects.core.js" type="text/javascript"></script>
				<script type="text/javascript" src="javascripts/scripts.js"></script>';
				/***Recursion de lista de Categorias**/
				echo '</td></tr>';
	}
	
					echo ' <input type="hidden" name="TotalPropDefault_'.$lineaordenx.'" value="' . $PropertyCounter . '">
							
				</table>';
				/***************************************************************************************/



?>
