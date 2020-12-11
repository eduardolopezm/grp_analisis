<?php

include('includes/session.inc');
$title = _('Altas, Bajas y Modificaciones de Matrizes');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$funcion=310;
include('includes/SecurityFunctions.inc');


if (isset($_POST['edodes'])) {
	$edodes= $_POST['edodes'];
} else {
	$edodes="";
}	

$num_reg=50;

if (isset($_POST['num_reg'])) {
	$num_reg = $_POST['num_reg'];
}
//esta es la variable con la que nos guiamos para saber ssi es la primera vez que entran
//y para poder mostrar los datos del seleccionado cuando van a modificar
if (isset($_GET['Prodlineid'])) {
	$Prodlineid = trim($_GET['Prodlineid']);
} elseif (isset($_POST['Prodlineid'])) {
	$Prodlineid = trim($_POST['Prodlineid']);
}

//esta es la variable que guarda el error
$InputError = 0;
// Verifica si el usuario ya dio click en el boton de Enviar o en el link de modificar o borrar registro
if (isset($_POST['enviar']) || isset($_GET['borrar']) || isset($_POST['modificar']) ) {
		if (isset($_POST['desline']) && strlen($_POST['desline'])<3){
		$InputError = 1;
		prnMsg(_('El nombre de la matriz debe ser de al menos 3 caracteres de longitud'),'error');
		}
	unset($sql);
//aqui empieza a realizar las acciones que corresponda de acuerdo al boton o link seleccionado
	if (isset($_POST['modificar'])and ($InputError != 1)) {
		$sql = "UPDATE regions SET name='" .$_POST['desline']." ' where regioncode='".$Prodlineid."'";
		$ErrMsg = _('La actualización de la Matriz fracaso porque');
		prnMsg( _('La Matriz').' ' .$_POST['desline'] . ' ' . _(' se ha actualizado.'),'info');
	} elseif (isset($_GET['borrar'])and ($InputError != 1)) {
			$sql= "select count(*) from areas where regioncode='".$_GET['Prodlineid']."'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('No se da de baja la Matriz por que hay areas unidas a ella'),'error');
		} else {
			$sql="DELETE FROM regions WHERE regioncode='" . $_GET['Prodlineid']."'";
			prnMsg(_('La Matriz a sido eliminada ') . '!','info');
		}
	} elseif (isset($_POST['enviar'])and ($InputError != 1)) {
			$sql= "select count(*) from regions where name='".$_POST['desline']."'
			or regioncode='".trim($_POST['Prodlineid'])."'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('No se da de alta la Matriz por que ya hay un registro guardado'),'error');
		} else {
			$sql = "INSERT INTO regions (regioncode,name)
			VALUES ('".trim($_POST['Prodlineid'])."','".$_POST['desline']."')";
			$ErrMsg = _('La inserccion de la Matriz fracaso porque');
			prnMsg( _('La Matriz').' ' .$_POST['desline'] . ' ' . _('se ha creado.'),'info');
		}
	}
//aqui se inicializan las variables en vacio
	unset($_POST['desline']);
	unset($_POST['Prodgroupid']);
	unset($_POST['activo']);
	unset($_POST['Prodlineid']);
	unset($Prodlineid);	
}

if (isset($sql) && $InputError != 1 ) {
	$result = DB_query($sql,$db,$ErrMsg);
	if ($pagina=='Stock' and isset($_POST['enviar'])) {
		echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath ."/StockCategories.php?" . SID . "'>";	
	}
}

if (isset($_POST['Go1'])) {
	$Offset = $_POST['Offset1'];
	$_POST['Go1'] = '';
}

if (!isset($_POST['Offset'])) {
	$_POST['Offset'] = 0;
} else {
	if ($_POST['Offset']==0) {
		$_POST['Offset'] = 0;
	}
}

if (isset($_POST['Next'])) {
	$Offset = $_POST['nextlist'];
}

if (isset($_POST['Prev'])) {
	$Offset = $_POST['previous'];
}

echo "<form method='post' action=". $_SERVER['PHP_SELF'] . "?". SID . ">";

if (!isset($Prodlineid) ) {
	/*echo '<table><tr>
	<td>
		' . _('Matriz') . '<br><input type="text" name="edodes" value="'.$edodes.'" size=25 maxlength=55>
	</td>
	<td valign=bottom>
		<input type=submit name=buscar value=' . _('Buscar') . '>
	</td></tr></table>';
	echo "<div class='centre'><hr width=50%></div><br>";
	if ($Offset==0) {
		$numfuncion=1;
	} else {
		$numfuncion=$num_reg*$Offset+1;
	}
	$Offsetpagina=1;*/
//esta parte sirve para mostrar la primera tabla con todos los registros existentes
	$sql = "SELECT 	r.regioncode,
			r.name as name
		FROM regions as r";
	/*if (strlen($edodes)>=1) {
	$sql=$sql.' where r.areadescription='.$edodes;
	}
	if (strlen($descrip)>=1) {
	$sql=$sql.' and l.Description like "%'.$descrip.'%"';
	} 
	$sql=$sql.' order by '.$ordenpri.','.$ordenseg;	*/
	$result = DB_query($sql,$db);
	$ListCount=DB_num_rows($result);
	$ListPageMax=ceil($ListCount/$num_reg);

	$sql = "SELECT 	r.regioncode,
			r.name as name
		FROM regions as r";
	/*if (strlen($edodes)>=1) {
	$sql=$sql.' where r.areadescription='.$edodes;
	}
	if (strlen($descrip)>=1) {
	$sql=$sql.' and l.Description like "%'.$descrip.'%"';
	} 
	$sql=$sql.' order by '.$ordenpri.','.$ordenseg;*/
	$sql = $sql . " LIMIT ".$num_reg." OFFSET ". ($Offset * $num_reg) ;
	$result = DB_query($sql,$db);
	
	if (!isset($Prodlineid)) {
		echo "<div class='centre'>" ._('LISTADO DE MATRIZES'). "</div>";
		echo '<table width=50%>';
		echo '	<tr>';
		if ($ListPageMax >1) {
			if ($Offset==0) {
				$Offsetpagina=1;	
			} else {
				$Offsetpagina=$Offset+1;
		            }
			echo '<td>'.$Offsetpagina. ' ' . _('de') . ' ' . $ListPageMax . ' ' . _('Paginas') . '. ' . _('Ir a la Pagina') . ':';
			echo '<select name="Offset1">';
		        $ListPage=0;
					while($ListPage < $ListPageMax) {
						
						if ($ListPage == $Offset) {
							echo '<option VALUE=' . $ListPage . ' selected>' . ($ListPage+1) . '</option>';
						} else {
							echo '<option VALUE=' . $ListPage . '>' . ($ListPage+1) . '</option>';
						}
						$ListPage++;
						$Offsetpagina=$Offsetpagina+1;
					}
			echo '</select></td>
			<td><input type="text" name="num_reg" size=1 value="' .$num_reg. '"></td>
			<td>
				<input type=submit name="Go1" VALUE="' . _('Ir') . '">
			</td>';
			if ($Offset>0){
			echo '<td align=center cellpadding=3 >
				<input type="hidden" name="previous" value='.number_format($Offset-1).'>
				<input tabindex='.number_format($j+7).' type="submit" name="Prev" value="'._('Anterior').'">
			</td>';
			};
			if ($Offset<>$ListPageMax-1) {
			echo'<td style="text-align:right">
				<input type="hidden" name="nextlist" value='.number_format($Offset+1).'>
				<input tabindex='.number_format($j+9).' type="submit" name="Next" value="'._('Siguiente').'">
			</td>';
			}
		}
		echo'</tr>
		</table>';
	}
	
	echo '<table border=1 width=50%>';
	echo "<tr><th>" . _('Codigo') . "</th>
        <th>" . _('Matriz') . "</th>
	<th></th>
	<th></th>
        </tr>";

	$k=0; //row colour counter
	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		if ($myrow['activo']==1) {
			$myrow['activo']='Si';
		} else {
			$myrow['activo']='No';
		}
		printf("<td>%s</td>
			<td>%s</td>
			<td style='text-align:center;'><a href=\"%s&Prodlineid=%s\">Modificar</a></td>
			<td style='text-align:center;'><a href=\"%s&Prodlineid=%s&borrar=1\">Eliminar</a></td>
			</tr>",
			$myrow['regioncode'],
			$myrow['name'],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow['regioncode'],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow['regioncode']
			);
		$numfuncion=$numfuncion+1;
	} 
	echo '</table>';
}
if (isset($Prodlineid)) {
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Matrizes existentes') . '</a></div>';
}

//esta parte sirve para mostrar los datos del registro seleccionado para modificar
if (isset($Prodlineid)) {
	$sql = "SELECT *
		FROM regions
		WHERE regioncode='". $Prodlineid . "'";
		
	$result = DB_query($sql, $db);
	if ( DB_num_rows($result) == 0 ) {
		prnMsg( _('No hay registros.'),'warn');
	} else {
		$myrow = DB_fetch_array($result);
		$_POST['Prodlineid']=$myrow['regioncode'];
		$_POST['desline'] = $myrow['name'];		
	}
}

echo '<br>';
if(isset($_POST['Prodlineid'])) {
	echo "<input type=hidden name='Prodlineid' VALUE='" . $_POST['Prodlineid'] . "'>";
}
echo "<div class='centre'><hr width=60%>" ._('ALTA/MODIFICACION DE MATRIZES'). "</div><br>";
	echo '<table>';
	echo'<tr><td>' . _('Codigo de la Matriz') . ":</td>
	<td><input type='text' name='Prodlineid' size=40 maxlength=100 VALUE='" .$_POST['Prodlineid'] . "'></td></tr>
	<tr>";
	echo '<tr><td>' . _('Nombre de la Matriz') . ":</td>
	<td><input type='text' name='desline' size=40 maxlength=100 VALUE='" .$_POST['desline'] . "'></td></tr>
	<tr>";
	echo '</tr></table>';
	//aqui verifica si esta vacia la variable si lo esta el valor de la accion sera enviar 
	if (!isset($Prodlineid)) {
		echo "<div class='centre'><input type='Submit' name='enviar' value='" . _('Enviar') . "'></div>";
	}
	//aqui verifica que este llena la variable si lo esta el valor de la accion sera modificar
	elseif (isset($Prodlineid)) {
		echo "<div class='centre'><input type='Submit' name='modificar' value='" . _('Actualizar') . "'></div>";
	}
echo '</form>';
include('includes/footer_Index.inc');
?>