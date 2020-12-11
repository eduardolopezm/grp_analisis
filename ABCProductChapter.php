<?php
/* 24 - SEP - 2014 *************************
 * ABC de capitulos
 * Reberiano RamÃ­rez ***********************/
/*
* AHA
* 7-Nov-2014
* Cambio de ingles a espa–ol los mensajes de usuario de tipo error,info,warning, y success.
*/

include('includes/session.inc');
$title = _('Altas, Bajas y Modificaciones de Capitulos de Productos');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

/*$funcion=13;
include('includes/SecurityFunctions.inc');
*/
if (isset($_GET['pagina'])){
	$pagina = $_GET['pagina'];
}

if (isset($_POST['edogrupo'])) {
	$edogrupo= $_POST['edogrupo'];
} else {
	$edogrupo = "";
}

if (isset($_POST['descrip'])) {
	$descrip= $_POST['descrip'];
} else {
	$descrip="";
}	

if (isset($_POST['linea'])) {
	$linea= $_POST['linea'];
} else {
	$linea ="";	
}

if (isset($_GET['ordenseg'])) {
	$ordenseg = $_GET['ordenseg'];
} else {

	$ordenseg = "Description";
}

$num_reg=10;

if (isset($_POST['num_reg'])){
	$num_reg = $_POST['num_reg'];
}
//esta es la variable con la que nos guiamos para saber ssi es la primera vez que entran
//y para poder mostrar los datos del seleccionado cuando van a modificar
if (isset($_GET['Prodgroupid'])) {
	$Prodgroupid = $_GET['Prodgroupid'];
} elseif (isset($_POST['Prodgroupid'])) {
	$Prodgroupid = $_POST['Prodgroupid'];
}

//esta es la variable que guarda el error
$InputError = 0;
// Verifica si el usuario ya dio click en el boton de Enviar o en el link de modificar o borrar registro
if (isset($_POST['enviar']) || isset($_GET['borrar']) || isset($_POST['modificar']) ) {
		if (isset($_POST['desline']) && strlen($_POST['desline'])<3){
		//si fue menor a tres letras se llena la variable de error y muestra mensaje			
		$InputError = 1;
		prnMsg(_('La descripcion debe ser de al menos 3 caracteres de longitud'),'error');
		}
	unset($sql);
//aqui empieza a realizar las acciones que corresponda de acuerdo al boton o link seleccionado
	if (isset($_POST['modificar'])and ($InputError != 1)) { 
		$sql = "UPDATE prodchapter SET description='" .$_POST['desline']." ' where prodchapterid=".$Prodgroupid;
		$ErrMsg = _('La actualizacion del capitulo del producto fracaso porque');
		prnMsg( _('El grupo').' ' .$_POST['desline'] . ' ' . _(' se ha actualizado.'),'info');
	} elseif (isset($_GET['borrar'])and ($InputError != 1)) {
		//aqui si no hubo registros guardados borra la zona de la tabla
			$sql="DELETE FROM prodchapter WHERE prodchapterid=" . $_GET['Prodgroupid'];
			prnMsg(_('El capitulo ha sido eliminado ') . '!','info');
	} elseif (isset($_POST['enviar'])and ($InputError != 1)) {
		//aqui verifica que no exista otro tipo de vehiculo con el mismo nombre
		//si existe le manda mensaje de error y no lo deja insertar
			$sql= "select count(*) from prodchapter where description='".$_POST['desline']."'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
		if ($myrow[0]>0){
			prnMsg( _('No se da de alta el capitulo por que ya hay un registro guardado'),'error');
		} else {
		//si no da de alta el registro			
			$sql = "INSERT INTO prodchapter (Description)
			VALUES ('".$_POST['desline']."')";
			$ErrMsg = _('La inserccion del capitulo fracaso porque');
			prnMsg( _('El grupo').' ' .$_POST['desline'] . ' ' . _('se ha creado.'),'info');
		}
	}
//aqui se inicializan las variables en vacio
	unset($_POST['desline']);
	unset($_POST['activo']);
	unset($_POST['Prodgroupid']);
	unset($Prodgroupid);	
}

if (isset($sql) && $InputError != 1 ) {
	$result = DB_query($sql,$db,$ErrMsg);
	//este sirve para el redireccionamiento a la pagina de donde llego
	if ($pagina=='linea' and isset($_POST['enviar'])) {
		echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath ."/ABCProductLines.php?" . SID . "'>";	
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

if (!isset($Prodgroupid) ) {
	echo '<table><tr>
		<td>
			' . _('Descripcion') . '<br><input type="text" name="descrip" value="'.$descrip.'" size=25 maxlength=55>
		</td>
		<td valign=bottom>
			<input type=submit name=buscar value=' . _('Buscar') . '>
		</td>
	</tr></table>';
	echo "<div class='centre'><hr width=50%></div><br>";
	if ($Offset==0) {
		$numfuncion=1;
	} else {
		$numfuncion=$num_reg*$Offset+1;
	}
	$Offsetpagina=1;
//esta parte sirve para mostrar la primera tabla con todos los registros existentes
	$sql = "select g.prodchapterid,
			g.description as grupo
		from prodchapter as g";
	if (strlen($descrip)>=1) {
	$sql=$sql.' where g.description like "%'.$descrip.'%"';
	} 
	$sql=$sql.' order by '.$ordenseg;	
	$result = DB_query($sql,$db);
	$ListCount=DB_num_rows($result);
	$ListPageMax=ceil($ListCount/$num_reg);

	$sql = "select g.prodchapterid,
			g.description as grupo
		from prodchapter as g";
	if (strlen($descrip)>=1) {
	$sql=$sql.' where g.description like "%'.$descrip.'%"';
	} 
	$sql=$sql.' order by '.$ordenseg;
	$sql = $sql . " LIMIT ".$num_reg." OFFSET ". ($Offset * $num_reg) ;
	$result = DB_query($sql,$db);
	
	if (!isset($Prodgroupid)) {
		echo "<div class='centre'>" ._('LISTADO DE CAPITULOS'). "</div>";
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
			if ($Offset>0) {
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
	echo "<tr>
		<th>" . _('No') . "</th>
        <th>" . _('Codigo') . "</th>
		<th>" . _('Descripcion') . "</th>
	<th></th>
        </tr>";

	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		
		if ($k==1) {
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		printf("<td>%s</td>
				<td>%s</td>
		        <td nowrap>%s</td>
			<td style='text-align:center;'><a href=\"%s&Prodgroupid=%s\">Modificar</a></td>
			</tr>",
			$numfuncion,
            $myrow['prodchapterid'],
			$myrow['grupo'],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow['prodchapterid']
			);
		$numfuncion=$numfuncion+1;

	} 
	echo '</table>';
	
}

if (isset($Prodgroupid)) {
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Grupos existentes') . '</a></div>';
}

//esta parte sirve para mostrar los datos del registro seleccionado para modificar
if (isset($Prodgroupid)) {
	$sql = "SELECT *
		FROM prodchapter
		WHERE prodchapterid=". $Prodgroupid ;
		
	$result = DB_query($sql, $db);
    if ( DB_num_rows($result) == 0 ) {
		prnMsg( _('No hay registros.'),'warn');
	} else {
		$myrow = DB_fetch_array($result);
		$_POST['Prodgroupid']=$myrow['prodchapterid'];
		$_POST['desline'] = $myrow['description'];
		
	  }
}
echo '<br>';
if(isset($_POST['Prodgroupid'])) {
	echo "<input type=hidden name='Prodgroupid' VALUE='" . $_POST['Prodgroupid'] . "'>";
}
echo "<div class='centre'><hr width=60%>" ._('ALTA/MODIFICACION DE CAPITULOS'). "</div><br>";
	echo '<table>
	<tr><td>' . _('Descripcion del Capitulo') . ":</td>
	<td><input type='text' name='desline' size=40 maxlength=100 VALUE='" .$_POST['desline'] . "'></td></tr>
	<tr>";
        echo '</tr></table>';
	//aqui verifica si esta vacia la variable si lo esta el valor de la accion sera enviar 
	if (!isset($Prodgroupid)) {
		echo "<div class='centre'><input type='Submit' name='enviar' value='" . _('Enviar') . "'></div>";
	}
	//aqui verifica que este llena la variable si lo esta el valor de la accion sera modificar
	elseif (isset($Prodgroupid)) {
		echo "<div class='centre'><input type='Submit' name='modificar' value='" . _('Actualizar') . "'></div>";
	}
echo '</form>';
include('includes/footer.inc');
?>