<?php
include('includes/session.inc');
$title = _('Altas, Bajas y Modificaciones de Mantenimiento de Autorizacion de Pagos');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$funcion = 1346;
include('includes/SecurityFunctions.inc');

// Variable de error
$InputError = 0;

// Variable numero de registros que se muestran
$num_reg = 20;
if (isset($_POST['num_reg'])) {
	$num_reg = $_POST['num_reg'];
} else if (isset($_GET['num_reg'])) {
	$num_reg = $_GET['num_reg'];
}

// Id de la tabla
$id = null;
if (isset($_GET['id'])) {
	$id = $_GET['id'];
} else if (isset($_POST['id'])) {
	$id = $_POST['id'];
}

// Borrar registro
if (empty($_GET['Delete']) == false) {
	if (empty($id) == false) {
		DB_query("DELETE FROM autorizacionpagos_estatus WHERE idstatus = '$id'", $db);
		prnMsg(_('Se ha eliminado el registro.'), 'info');
		$id = null;
	}
}

// Insertar o actualizar registro
if (isset($_POST['enviar']) || isset($_POST['modificar']) ) {
	
	// Validaciones
	
	if (isset($_POST['nombre']) AND strlen($_POST['nombre']) < 3) {
		$InputError = 1;
		prnMsg(_('El nombre debe tener al menos 3 caracteres de longitud'), 'error');
	}
	
	// Subir imagen
	$logoName = "";
	$exts = array('gif', 'jpg', 'png', 'jpeg', 'bmp');
	
	if (!empty($_FILES['logo']['name'])) {
		
		$logoName = $_FILES['logo']['name'];
		$type = $_FILES['logo']['type'];
		$tmpName = $_FILES['logo']['tmp_name'];
		
		$ext = pathinfo($logoName, PATHINFO_EXTENSION);
		if (in_array($ext, $exts)) {
			if (move_uploaded_file($tmpName, "images/$logoName")) {
				prnMsg(_('La imagen ha sido enviada al servidor.'), 'success');
			} else {
				prnMsg(_('La imagen no puede ser enviada al servidor.'), 'error');
				$InputError = 1;
			}
		} else {
			prnMsg(_('El archivo no tiene una extension valida.'), 'error');
			$InputError = 1;
		}
	}
	
	unset($sql);
	
	if (isset($_POST['modificar']) AND ($InputError != 1)) {
	
		$sql = "UPDATE autorizacionpagos_estatus 
				SET nombre='{$_POST['nombre']}', 
					logo='$logoName',
					orden='{$_POST['orden']}', 
					active='{$_POST['active']}', 
					marcainicial='" . (empty($_POST['marcainicial']) ? 0 : 1) . "',
					permiso='{$_POST['permiso']}',
					flagpresupuesto='" . (empty($_POST['flagpresupuesto']) ? 0 : 1) . "' 
				WHERE idstatus = $id";
		if (empty($logoName)) {
			$sql = "UPDATE autorizacionpagos_estatus
					SET nombre='{$_POST['nombre']}',
						orden='{$_POST['orden']}',
						active='{$_POST['active']}',
						marcainicial='" . (empty($_POST['marcainicial']) ? 0 : 1) . "',
						permiso='{$_POST['permiso']}',
						flagpresupuesto='" . (empty($_POST['flagpresupuesto']) ? 0 : 1) . "'
					WHERE idstatus = $id";
		}
		$ErrMsg = _('La actualizacion fracaso porque');
		prnMsg(_('La autorizacion ') . ' ' . $_POST['nombre'] . ' ' . _(' se ha actualizado.'), 'info');

	} else if (isset($_POST['enviar']) AND ($InputError != 1)) {
		
		$sql = "SELECT COUNT(*) FROM autorizacionpagos_estatus WHERE nombre='{$_POST['nombre']}'";
		$result = DB_query($sql, $db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0] > 0) {
			prnMsg(_('No se da de alta la autorizaacion por que ya hay un registro guardado'), 'error');
		} else {
			$sql = "INSERT INTO autorizacionpagos_estatus (nombre, logo, orden, active, marcainicial, permiso, flagpresupuesto)
				VALUES ('". trim($_POST['nombre'])."', 
						'".$logoName."', 
						'".$_POST['orden']."', 
						'".$_POST['active']."', 
						'".$_POST['marcainicial']."',
						'".$_POST['permiso']."',
						'".$_POST['flagpresupuesto']."') ";
			$ErrMsg = _('La insercion de la autorizacion fracaso porque');
			prnMsg(_('La autorizacion') . ' ' . $_POST['nombre'] . ' ' . _('se ha creado.'), 'info');
		}
	}
	
	unset($_POST['nombre']);
	unset($_POST['orden']);
	unset($_POST['marcainicial']);
	unset($_POST['active']);
	unset($_POST['permiso']);
	unset($_POST['flagpresupuesto']);
	unset($_POST['id']);
	unset($id);
}

if (isset($sql) && $InputError != 1) {
	$result = DB_query($sql, $db);
}

if (!isset($_POST['Offset'])) {
	if (isset($_GET['Offset'])) {
		$_POST['Offset'] = $_GET['Offset'];
	} else {
		$_POST['Offset'] = 0;
	}
} else {
	if ($_POST['Offset'] == 0) {
		$_POST['Offset'] = 0;
	}
}

if (isset($_POST['Offset'])) {
	$Offset = $_POST['Offset'];
}

if (isset($_POST['Go1'])) {
	$Offset = $_POST['Offset1'];
	$_POST['Go1'] = '';
}

if (isset($_POST['Next'])) {
	$Offset = $_POST['nextlist'];
}

if (isset($_POST['Prev'])) {
	$Offset = $_POST['previous'];
}

echo "<form method='post' enctype='multipart/form-data' name='forma' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
if (!isset($id) AND $_POST['id'] == '') {

	$Offsetpagina = 1;

	$sql = "SELECT COUNT(*) FROM autorizacionpagos_estatus";
	$result = DB_query($sql, $db);
	$ListCount = 0;
	if ($row = DB_fetch_row($result)) {
		$ListCount = $row[0];
	}
	
	$ListPageMax = ceil($ListCount / $num_reg);
	
	$sql= "SELECT idstatus, nombre, logo, orden,active, marcainicial, permiso, flagpresupuesto 
		FROM autorizacionpagos_estatus ORDER BY idstatus DESC LIMIT " . $num_reg . " OFFSET " . ($Offset * $num_reg);
	
	$result = DB_query($sql, $db);

	if (!isset($id)) {
		
		echo "<table border=0 align='center'; width:800px; background-color:#ffff;' border='0' nowrap>";
		echo '<tr>';
		echo '<td align="center" colspan=2 class="texto_lista">';
		echo '<p align="center">';
		echo '<img src="images/imgs/configuracion.png" height=25" width="25" title="' . _('Altas, Bajas y Modificaciones de Autorizaciones de Pagos') . '" alt="">' . ' ' . $title . '<br />';
		echo '</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table align="center" style="width:500px">';
		echo '<tr>';
		
		if ($ListPageMax >= 0) {
			if ($Offset == 0) {
				$Offsetpagina = 1;
			} else {
				$Offsetpagina = $Offset + 1;
		    }
			echo '<td>' . $Offsetpagina . ' ' . _('de') . ' ' . $ListPageMax . ' ' . _('Paginas') . '. ' . _('Ir a la Pagina') . ':';
			echo '<select name="Offset1">';
		    $ListPage = 0;
            while ($ListPage < $ListPageMax) {
                if ($ListPage == $Offset) {
                    echo '<option VALUE=' . $ListPage . ' selected>' . ($ListPage + 1) . '</option>';
                } else {
                    echo '<option VALUE=' . $ListPage . '>' . ($ListPage + 1) . '</option>';
                }
                $ListPage++;
                $Offsetpagina = $Offsetpagina + 1;
            }
			echo '</select></td>
				<td><input type="text" name="num_reg" size=1 value="' . $num_reg . '"></td>
				<td>
				<input type=submit name="Go1" VALUE="' . _('Buscar') . '">
				</td>';
			
			if ($Offset > 0) {
				echo '<td align=center cellpadding=3 >
					<input type="hidden" name="previous" value='.number_format($Offset-1).'>
					<input tabindex=' . number_format($j + 7) . ' type="submit" name="Prev" value="' . _('Anterior') . '">
	                </td>';
			};
			if ($Offset <> $ListPageMax - 1) {
				echo '<td style="text-align:right">
					<input type="hidden" name="nextlist" value=' . number_format($Offset+1) . '>
					<input tabindex=' . number_format($j+9) . ' type="submit" name="Next" value="' . _('Siguiente') . '">
	                </td>';
			}
		}
		
		echo "</tr>";
		echo "</table>";
	}
	
	echo '<table width="100%" cellspacing="0" border="1" bordercolor="lightgray" cellpadding="0" colspan="0" style="margin-top:0">';
	
	echo "<tr>";
	echo "<td colspan='10' class='titulo_azul'>";
	echo _("Listado de Autorizaciones de Pago");
	echo "</td>";
	echo "</tr>";
	
	echo "<tr>";
	
	// Columna id
	echo "<td class='titulos_principales'>";
	echo _('ID');
	echo "</td>";
	
	// Columna nombre
	echo "<td class='titulos_principales'>";
	echo _('Nombre');
	echo "</td>";
	
	// Columna logo
	echo "<td class='titulos_principales'>";
	echo _('Logo');
	echo "</td>";
    
	// Columna orden
	echo "<td class='titulos_principales'>";
	echo _('Orden');
	echo "</td>";
    
	// Columna Permiso
	echo "<td class='titulos_principales'>";
	echo _('Permiso');
	echo "</td>";
	
	// Columna Marca Inicial
	echo "<td class='titulos_principales'>";
	echo _('Marca Inicial');
	echo "</td>";
	
	// Columna Presupuesto
	echo "<td class='titulos_principales'>";
	echo _('Presupuesto');
	echo "</td>";
	
	// Columna activo
	echo "<td class='titulos_principales'>";
	echo _('Activo');
	echo "</td>";
	
	// Columna modificar
	echo "<td class='titulos_principales'>";
	echo "</td>";
    
	// Columna borrar
	echo "<td class='titulos_principales'>";
	echo "</td>";
	
	echo "</tr>";
	
	$k = 0; //row colour counter
	
	while ($myrow = DB_fetch_array($result)) {
		
		if ($k == 1){
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
		
		if ($myrow['active'] == 1) {
			$myrow['active'] = 'Si';
		} else {
			$myrow['active'] = 'No';
		}
		
		if ($myrow['marcainicial'] == 1) {
			$myrow['marcainicial'] = 'Si';
		} else {
			$myrow['marcainicial'] = 'No';
		}
		
		if ($myrow['flagpresupuesto'] == 1) {
			$myrow['flagpresupuesto'] = 'Si';
		} else {
			$myrow['flagpresupuesto'] = 'No';
		}
		
		printf("<td class='numero_normal'>%s</td>
        	<td class='texto_normal'>%s</td>
            <td class='texto_normal'>%s</td>
			<td class='texto_normal'>%s</td>
			<td class='texto_normal'>%s</td>
			<td class='texto_normal'>%s</td>
			<td class='texto_normal'>%s</td>
			<td class='texto_normal'>%s</td>
            <td class='numero_normal'><a href=\"%s&id=%s&Offset=%s&num_reg=%s\">" . _("Modificar") . "</a></td>
            <td class='numero_normal'><a onclick='borrar(this.href); return false;' href=\"%s&id=%s&Offset=%s&num_reg=%s&Delete=1\">" . _("Eliminar") . "</a></td>
            </tr>",
	        $myrow['idstatus'],
	        $myrow['nombre'],
	        $myrow['logo'],
	        $myrow['orden'],
	        $myrow['permiso'],
	        $myrow['marcainicial'],
	        $myrow['flagpresupuesto'],
	        $myrow['active'],
	        $_SERVER['PHP_SELF'] . '?' . SID,
	        $myrow['idstatus'],
	        $Offset,
	        $num_reg,
	        $_SERVER['PHP_SELF'] . '?' . SID,
	        $myrow['idstatus'],
	        $Offset,
	        $num_reg
		);
	}
	
	echo "</table>";
}

if (isset($id)) {
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Autorizaciones Existentes') . '</a></div>';
}
	
// Esta parte sirve para mostrar los datos del registro seleccionado para modificar
if (isset($id) AND strlen($id) > 0) {

	$sql = "SELECT idstatus, nombre, logo, orden, active, marcainicial, permiso, flagpresupuesto 
		FROM autorizacionpagos_estatus WHERE idstatus = $id";

	$result = DB_query($sql, $db);
	if(DB_num_rows($result) == 0) {
		prnMsg( _('No hay registros.'), 'warn');
	} else {
		$myrow = DB_fetch_array($result);
		$_POST['id'] = $myrow['idstatus'];
		$_POST['nombre'] = $myrow['nombre'];
		$_POST['logo'] = $myrow['logoname'];
		$_POST['orden'] = $myrow['orden'];
		$_POST['active'] = $myrow['active'];
		$_POST['marcainicial'] = $myrow['marcainicial'];
		$_POST['permiso'] = $myrow['permiso'];
		$_POST['flagpresupuesto'] = $myrow['flagpresupuesto'];
	}
}
	
echo '<br />';
if (isset($_POST['id'])) {
	echo "<input type='hidden' name='id' value='" . $_POST['id'] . "'>";
	echo "<input type='hidden' name='Offset' value='" . $_POST['Offset'] . "'>";
	echo "<input type='hidden' name='num_reg' value='" . $num_reg . "'>";
}
	
echo "<div class='texto_status'>" ._('Alta/Modificacion de Autorizaciones de Pago'). "</div><br />";
echo "<table style='text-align:center; margin:0 auto;'>";

// Campo nombre
echo "<tr>";
echo "<td class='texto_lista'>" . _('Nombre:') . "</td>";
echo "<td><input type='text' name='nombre' size='40' maxlength='100' value='" . $_POST['nombre'] . "'></td>";
echo "</tr>";

// Campo logo
echo "<tr>";
echo "<td class='texto_lista'>" . _('Logo:') . "</td>";
echo "<td><input type='file' name='logo' /></td>";
echo "</tr>";

// Campo activo (Lista desplegable)
echo "<tr>";
echo "<td class='texto_lista'>" . _('Activo:') . "</td>";
echo "<td><select name='active'>";
if (!isset($_POST['active'])) {
	$_POST['active'] = 1;
}
if ($_POST['active'] == 1) {
	echo "<option selected='selected' value='1'>" . _('Activo') . "</option>";
	echo "<option  value='0'>" . _('Inactivo') . "</option>";
} else if ($_POST['active'] == 0){
	echo "<option value='1'>" . _('Activo') . "</option>";
	echo "<option selected='selected'  value='0'>" . _('Inactivo') . "</option>";
} else {
	echo "<option selected value='1'>" . _('Activo') . "</option>";
	echo "<option value='0'>" . _('Inactivo') . "</option>";
}
echo "</select>";
echo "</td>";
echo "</tr>";

// Campo orden
echo "<tr>";
echo "<td class='texto_lista'>" . _('Orden:') . "</td>";
echo "<td><input class='number' type='text' name='orden' size='11' maxlength='30' value='" . $_POST['orden'] . "'></td>";
echo "</tr>";

// Campo permiso
echo "<tr>";
echo "<td class='texto_lista'>" . _('No. de Permiso:') . "</td>";
echo "<td><input type='text' class='number' name='permiso' size='11' maxlength='30' value='" . $_POST['marcainicial'] . "'></td>";
echo "</tr>";

// Campo marca inicial
echo "<tr>";
echo "<td class='texto_lista'>" . _('Marca Inicial:') . "</td>";
if (empty($_POST['marcainicial'])) {
	echo "<td><input type='checkbox' name='marcainicial' value='1'></td>";
}
else{
	echo "<td><input type='checkbox' name='marcainicial' value='1' checked='checked'></td>";
}
echo "</tr>";


// Campo Presupuesto
echo "<tr>";
echo "<td class='texto_lista'>" . _('Aplica presupuesto:') . "</td>";
if (empty($_POST['flagpresupuesto'])) {
	echo "<td><input type='checkbox' name='flagpresupuesto' value='1'></td>";
}
else{
	echo "<td><input type='checkbox' name='flagpresupuesto' value='1' checked='checked'></td>";
}
//echo "<td><input type='checkbox' name='flagpresupuesto' value='1'></td>";
echo "</tr>";


echo "<tr>";
echo "<td colspan='2'>";
if (!isset($id)) {
	
	echo "<div class='centre'><input type='Submit' name='enviar' value='" . _('Enviar') . "'></div>";
	
} else if (isset($id)) {
	
	echo "<div class='centre'><input type='Submit' name='modificar' value='" . _('Actualizar') . "'></div>";
}
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</form>";
?>
<script type="text/javascript">
function borrar(href) {
	if(confirm("Desea borrar el registro?")) {
		window.location = href;
	}
	return false;
}
</script>
<?php
include('includes/footer.inc');
?>