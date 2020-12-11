<?php
/**
 * Reporte Catálogo de Pruductos
 *
 * @category Reporte
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 02/08/2017
 * Fecha Modificación: 02/08/2017
 * Reporte de Reporte Catálogo de Pruductos
 */

$PageSecurity = 2;
include('includes/session.inc');
$title = _('REPORTE CATALOGO DE PRODUCTOS');

$debug = 1;
if (!isset($_POST['PrintEXCEL'])) {
	include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Buscar') . '" alt="">' . ' ' . $title.'<br>';
     
     $InputError = 0;

	echo "<form action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method='POST'>";
	echo "<table style='text-align: center; margin: 0 auto'>";
	echo '<tr>
		<td colspan=2>
			<p align=center><b>** SELECCIONA EL CRITERIO DE CONSULTA</b><br><br>
		</td>';
	echo '</tr>';
	
	
	/************************************/
	/* SELECCION RAZON SOCIAL */
	echo '<tr><td>' . _('Dependencia') . ':' . "</td>
		<td><select tabindex='3' name='RazonSocial'>";
	
	$sql = 'SELECT legalid, legalname FROM legalbusinessunit';
	$result=DB_query($sql,$db);
	
	echo "<option selected value='0'>Todas las Dependencias...</option>";
	
	while ($myrow=DB_fetch_array($result)){
		if ($myrow['legalid'] == $_POST['RazonSocial']){
			echo "<option selected value='" . $myrow["legalid"] . "'>" . $myrow['legalname'];
		} else {
			echo "<option value='" . $myrow['legalid'] . "'>" . $myrow['legalname'];
		}
	}
	echo '</select></td></tr>';
	/************************************/

			echo '<tr>';
			echo '<td>' . _('Area') . ':</b></td>';
			$SQL=" SELECT areas.areacode,areas.areadescription
			       FROM areas
				   INNER JOIN regions ON areas.regioncode = regions.regioncode
			       ORDER BY areadescription";	   
			$resultarea = DB_query($SQL,$db);
			echo "<td><select name='area' >
						<option selected value=''>Seleccionar ...</option>";
			while ($myrowarea = DB_fetch_array($resultarea)) {
				if ($_POST['area']==$myrowarea['areacode']){
					echo '<option selected value="' . $myrowarea['areacode'] . '">' . $myrowarea['areadescription'].'</option>';
				} else {
					echo '<option  value="' . $myrowarea['areacode'] . '">' . $myrowarea['areadescription'].'</option>';
				}
			}
			echo '</select>&nbsp;&nbsp;';
			echo '<input type="submit" value="->" name="btnArea"></td>';
			echo '</tr>';

			$wcond="";
			if ($_POST['area'])
				$wcond = "AND t.areacode = '".$_POST['area']."'";

	
	//Select the tag
	echo '<tr><td>' . _('UR') . ':</td><td><select name="tag">';
	echo '<option selected value="">Seleccionar ...';

	///Pinta las unidades de negocio por usuario	
	$SQL = "SELECT t.tagref,t.tagdescription";
	$SQL = $SQL .	" FROM sec_unegsxuser u,tags t ";
	$SQL = $SQL .	" WHERE u.tagref = t.tagref  $wcond";
	$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "' ORDER BY t.tagdescription, t.tagref";
	
	$result=DB_query($SQL,$db);
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['tag']) and $_POST['tag']==$myrow['tagref']){
			echo '<option selected value=' . $myrow['tagref'] . '>' . $myrow['tagdescription'];
		} else {
			echo '<option value=' . $myrow['tagref'] . '>' . $myrow['tagdescription'];
		}
	}
	echo '</select></td><tr>';

	
	/************************************/
	/* SELECCION DEL GRUPO DE PRODUCTOS */
	echo '<tr><td>' . _('Del Grupo') . ':' . "</td>
		<td><select tabindex='4' name='DelGrupo'>";

	$sql = 'SELECT Prodgroupid, description FROM ProdGroup';
	$result=DB_query($sql,$db);
		
	echo "<option selected value='0'>Todos los grupos...</option>";
	
	while ($myrow=DB_fetch_array($result)){
	      if ($myrow['Prodgroupid'] == $_POST['DelGrupo']){
			echo "<option selected value='" . $myrow["Prodgroupid"] . "'>" . $myrow['description'];
	      } else {
		      echo "<option value='" . $myrow['Prodgroupid'] . "'>" . $myrow['description'];
	      }
	}
	echo '</select></td></tr>';
	/************************************/
	
	/************************************/
	/* SELECCION DEL GRUPO DE PRODUCTOS */
	echo '<tr><td>' . _('Al Grupo') . ':' . "</td>
		<td><select tabindex='4' name='AlGrupo'>";

	$sql = 'SELECT Prodgroupid, description FROM ProdGroup';
	$result=DB_query($sql,$db);
		
	echo "<option selected value='0'>Todos los grupos...</option>";
	
	while ($myrow=DB_fetch_array($result)){
	      if ($myrow['Prodgroupid'] == $_POST['AlGrupo']){
			echo "<option selected value='" . $myrow["Prodgroupid"] . "'>" . $myrow['description'];
	      } else {
		      echo "<option value='" . $myrow['Prodgroupid'] . "'>" . $myrow['description'];
	      }
	}
	echo '</select></td></tr>';
	/************************************/
	
	/************************************/
	/* SELECCION DEL LINEA DE PRODUCTOS */
	echo '<tr><td>' . _('X Linea') . ':' . "</td>
		<td><select tabindex='4' name='xLinea'>";

	$sql = 'SELECT Prodlineid, Description FROM ProdLine';
	$result=DB_query($sql,$db);
	
	echo "<option selected value='0'>Todas las lineas...</option>";
	
	while ($myrow=DB_fetch_array($result)){
	      if ($myrow['Prodlineid'] == $_POST['xLinea']){
			echo "<option selected value='" . $myrow["Prodlineid"] . "'>" . $myrow['Description'];
	      } else {
		      echo "<option value='" . $myrow['Prodlineid'] . "'>" . $myrow['Description'];
	      }
	}
	echo '</select></td></tr>';
	/************************************/
	
	/************************************/
	/* SELECCION DEL CATEGORIA DE PRODUCTOS */
	echo '<tr><td>' . _('X Categoria') . ':' . "</td>
		<td><select tabindex='4' name='xCategoria'>";

	#$sql = 'SELECT categoryid, categorydescription FROM stockcategory';
	$sql='SELECT sto.categoryid, categorydescription FROM stockcategory sto, sec_stockcategory sec WHERE sto.categoryid=sec.categoryid AND userid="'.$_SESSION['UserID'].'" ORDER BY categorydescription';
	
	$result=DB_query($sql,$db);
	
	echo "<option selected value='0'>Todas las categorias...</option>";

	while ($myrow=DB_fetch_array($result)){
	      if ($myrow['categoryid'] == $_POST['xCategoria']){
			echo "<option selected value='" . $myrow["categoryid"] . "'>" . $myrow['categorydescription'];
	      } else {
		      echo "<option value='" . $myrow['categoryid'] . "'>" . $myrow['categorydescription'];
	      }
	}
	echo '</select></td></tr>';
	/************************************/
	
	echo '<tr><td>' . _('X Marca') . ':' . "</td>
		<td><select name='xMarca'>";
		$sql='SELECT DISTINCT manufacturer
		      FROM stockmaster where manufacturer<>""
		      ORDER BY manufacturer';
		$result=DB_query($sql,$db);
		echo "<option selected value='0'>Todas las marcas...</option>";
		while ($myrow=DB_fetch_array($result)){
			if ($myrow['manufacturer'] == $_POST['xMarca']){
				echo "<option selected value='" . $myrow["manufacturer"] . "'>" . $myrow['manufacturer'];
			}else{
				echo "<option value='" . $myrow['manufacturer'] . "'>" . $myrow['manufacturer'];
			}
		}
		echo '</select></td>
	</tr>';
	
	
	echo '<tr>
		<td colspan=2>
			<p align=left><b>** BUSQUEDAS</b><br>
		</td>';
	echo '</tr>';
	
	//X Codigo de Producto
	echo "<tr>
		<td>" . _('X Código Producto') . ":</td>
		<td>";
			echo "<input type=text name='codproducto' value='".$_POST['codproducto']."'>";
	echo "	</td>";
	echo "</tr>";
	/*FIN CODIGO PRODUCTO*/
		//X Descripción de Producto
	echo "<tr>
		<td>" . _('X Descripción Producto') . ":</td>
		<td>";
			echo "<input type=text name='descproducto' value='".$_POST['descproducto']."'>";
	echo "	</td>";
	echo "</tr>";
	/*FIN DESCRIPCION DE PRODUCTO*/
	
	//X Controlado
	echo ".";
	
	//control de Selectd
	if($_POST['Controlled']==''){
		$valueAll='selected';
		$valueCC="";
		$valueSC="";
	}elseif($_POST['Controlled']=='0'){
		$valueSC='selected';
		$valueCC="";
		$valueAll="";
	}else{
		$valueCC="selected";
		$valueSC='';
		$valueAll='';
	}
	
	//control de Selectd
		echo '<tr>
					<td>' . _('X Control de lote, serie o batch') . ':</td>
					<td>
						<select name="Controlled">';
		echo 				'<option '.$valueAll.' value="">' . _('Todos'). '</option>';
		echo 				'<option '.$valueSC.' value=0>' . _('Sin Control') . '</option>';
		echo 				'<option '.$valueCC.' value=1>' . _('Controlado'). '</option>';		
		echo 			'</select>
					</td>
			</tr>';
	/*FIN CONTROLADO DE PRODUCTO*/
	
	echo '  </table>
	<br><div class="centre"><input tabindex="6" type=submit name="reporte" value="' . _('MOSTRAR CATALOGO') . '"></div>';
	echo 		'<br><div class="centre"><input tabindex="7" type=submit name="PrintEXCEL" value="' . _('Exportar a Excel') . '"></div>	
			<br><br>
			</form>';
}
	If (isset($_POST['reporte'])||isset($_POST['PrintEXCEL'])){
		# Inicio Exportar a Excel#
		if (isset($_POST['PrintEXCEL'])){
		
			header("Content-type: application/ms-excel");
			# replace excelfile.xls with whatever you want the filename to default to
			header("Content-Disposition: attachment; filename=ReporteGeneralCatalogos.xls");
			header("Pragma: no-cache");
			header("Expires: 0");
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
				"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
		
			echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
			echo '<link rel="shortcut icon" href="'. $rootpath . '/favicon.ico" />';
			echo '<link rel="icon" href="' . $rootpath . '/favicon.ico" />';
		
		}
		#Fin Exportar a Excel#
		$sqlrep="
			Select distinct stockmaster.stockid, stockmaster.description, stockmaster.longdescription,
			stockmaster.mbflag,
			stockcategory.categorydescription as categoria,
			ProdLine.Description as linea,
			ProdGroup.Description as grupo,
			ProdGroup.Prodgroupid,
			ProdLine.Prodlineid,
			stockcategory.categoryid,
			stockcostsxlegal.avgcost as costo,
			legalbusinessunit.legalname,
			stockmaster.manufacturer,
			case when stockmaster.controlled != 0 then 'SI' else 'NO' end as control,
			case when stockmaster.serialised != 0 and stockmaster.controlled != 0 then 'Serializado' else  (case when stockmaster.controlled != 0 then 'Lote' else 'No Aplica' end) end as serial
			FROM stockmaster JOIN stockcategory 
			ON stockmaster.categoryid = stockcategory.categoryid
			LEFT JOIN ProdLine 
			ON stockcategory.Prodlineid = ProdLine.Prodlineid
			LEFT JOIN ProdGroup 
			ON ProdLine.Prodgroupid = ProdGroup.Prodgroupid
			LEFT JOIN stockcostsxlegal
			ON stockmaster.stockid = stockcostsxlegal.stockid
			JOIN legalbusinessunit
			ON legalbusinessunit.legalid = stockcostsxlegal.legalid
			JOIN tags
			ON tags.legalid = legalbusinessunit.legalid
			WHERE 1=1 ";
		
		if ($_POST['tag']){
			$sqlrep.=" AND tags.tagref = '".$_POST['tag']."'";
		}

		if ($_POST['area']){
			$sqlrep.=" AND tags.areacode = '".$_POST['area']."'";
		}
		
		If($_POST['RazonSocial'] != '0') {
			$sqlrep = $sqlrep . " AND legalbusinessunit.legalid = '" . $_POST['RazonSocial'] . "'";
		}
		
		If ($_POST['DelGrupo'] != '0'){
			$sqlrep=$sqlrep." AND ProdGroup.Prodgroupid >= '".$_POST['DelGrupo']."'";
			$sqlrep=$sqlrep." AND ProdGroup.Prodgroupid <= '".$_POST['AlGrupo']."'";
		}
		
		If ($_POST['xLinea']<>'0'){
			$sqlrep=$sqlrep." AND ProdLine.Prodlineid = '".$_POST['xLinea']."'";
		}
		
		If ($_POST['xMarca']<>'0'){
			$sqlrep=$sqlrep." AND stockmaster.manufacturer = '".$_POST['xMarca']."'";
		}
		
		If ($_POST['xCategoria']<>'0'){
			$sqlrep=$sqlrep." AND stockcategory.categoryid = '".$_POST['xCategoria']."'";
		}
		
		If ($_POST['codproducto']!="" and $_POST['codproducto']!="*" and $_POST['codproducto']!="0"){
			$sqlrep=$sqlrep." AND stockmaster.stockid like '%".$_POST['codproducto']."%'";
		}
		
		
		If ($_POST['descproducto']<>'' and $_POST['descproducto']!='0' and $_POST['descproducto']<>'*'){
			$descripcion=$_POST['descproducto'];
			$sqlrep=$sqlrep." AND stockmaster.description like '%".$descripcion."%'";
		}
		//Para Control
		If ($_POST['Controlled']<>''){
			$control=$_POST['Controlled'];
			$sqlrep=$sqlrep." AND stockmaster.controlled = '".$control."'";
		}
		//fin de control
		
		$sqlrep=$sqlrep.' ORDER BY ProdGroup.Description, 
									ProdLine.Description, 
									stockcategory.categorydescription, 
									stockmaster.stockid';
		//if($_SESSION['UserID']='karla')echo "<pre>".$sqlrep;//exit;
		
		
		
		echo '<table width=100% border=1 style="text-align: center; margin: 0 auto">';
		echo 	'<tr>';
		echo 		'<th colspan=3>1								</th>';
		echo 		'<th colspan=1><b>'._('Codigo').'</b>			</th>';
		echo 		'<th colspan=1><b>'._('Descripcion').'</b>		</th>';
			echo 	'<th colspan=1><b>'._('Detalle').'</b>			</th>';
			echo 	'<th colspan=1><b>'._('mbflag').'</b>			</th>';
			echo 	'<th colspan=1><b>'._('Razon Social').'</b>		</th>';
			echo 	'<th colspan=1><b>'._('Costo').'</b>			</th>';
			echo 	'<th colspan=1><b>'._('Controlado').'</b>		</th>';
			echo 	'<th colspan=1><b>'._('Batch/Lote').'</b>		</th>';
			echo 	'<th colspan=1><b>'._('Marca/Fabricante').'</b>		</th>';
			echo '</tr>';
		$antcategoria = '';
		$antlinea = '';
		$antgrupo = '';
		
		$resultrep=DB_query($sqlrep,$db);	
		while ($myrowrep=DB_fetch_array($resultrep)){	
			if ($antgrupo != $myrowrep['Prodgroupid']) {
				echo '<tr style="background-color:yellow">';
				echo 	'<td colspan=1>';
				echo 		$myrowrep['Prodgroupid'].'
					</td>';
				echo 	'<td colspan=1></td>';
				echo 	'<td colspan=1></td>';
				echo 	'<td colspan=8>';
				echo 		$myrowrep['grupo'].'
					</td>';
				echo '</tr>';
				$antgrupo = $myrowrep['Prodgroupid'];
			}	
			if ($antlinea != $myrowrep['Prodlineid']) {
				echo '<tr style="background-color:cyan">';
				echo 	'<td colspan=1></td>';
				echo 	'<td colspan=1>';
				echo 		$myrowrep['Prodlineid'].'
					</td>';
				echo 	'<td colspan=1></td>';
				echo 	'<td colspan=8>';
				echo 		$myrowrep['linea'].'
					</td>';
				echo '</tr>';
				
				$antlinea = $myrowrep['Prodlineid'];
			}
			if ($antcategoria != $myrowrep['categoryid']) {
				echo '<tr style="background-color:gray">';
				echo 	'<td colspan=1></td>';
				echo 	'<td colspan=1></td>';
				echo 	'<td colspan=1>';
				echo 		$myrowrep['categoryid'].'
						</td>';
				echo 	'<td colspan=8>';
				echo 		$myrowrep['categoria'].'
						</td>';
				echo '</tr>';	
				$antcategoria = $myrowrep['categoryid'];
			}
			
			echo '<tr>';
			echo 	'<td colspan=3></td>';
			echo 	'<td colspan=1>';
			if (!isset($_POST['PrintEXCEL'])){
				echo '<a href="Stocks.php?&StockID='.$myrowrep['stockid'].'" target="_blank">'.$myrowrep['stockid'].'</a>';
			}else{
				echo  $myrowrep['stockid'];
			}
			//
			echo	'</td>';
			echo 	'<td colspan=1>';
			echo 		$myrowrep['description'].'
					</td>';
			echo 	'<td colspan=1>';
			echo 		$myrowrep['longdescription'].'
					</td>';
			echo 	'<td colspan=1>';
			echo		$myrowrep['mbflag'].'
					</td>';
			echo 	'<td>';
			echo 		$myrowrep['legalname'] . '
					</td>';
			echo 	'<td>$';
			echo 		number_format($myrowrep['costo'], 2) . '
					</td>';
			echo 	'<td align="center">';
			echo 		$myrowrep['control'] . '
					</td>';
			echo 	'<td align="center">';
			echo 		$myrowrep['serial'] . '
					</td>';
			echo 	'<td align="center">';
			echo 		$myrowrep['manufacturer'] . '
					</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	if (!isset($_POST['PrintEXCEL'])) {
		include('includes/footer_Index.inc');
	}

?>
