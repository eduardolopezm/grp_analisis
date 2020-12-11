<?php
  
// xxx 
include('includes/session.inc');
$title = _('Altas, Bajas y Modificaciones de CSD');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include_once realpath(dirname(__FILE__)) . '/xml_v2/lib/factory/ComprobanteFactory.class.php';
include_once realpath(dirname(__FILE__)) . '/xml_v2/lib/validators/SatStampValidator.class.php';
 
$funcion = 1445;
include('includes/SecurityFunctions.inc');

$InputError = 0;
$certPath = 'xml_v2/lib/sat/certificados/';
$num_reg = 150;
if (isset($_POST['num_reg'])) {
	$num_reg = $_POST['num_reg'];
}

$estatus = array(
	'A' => 'Activo',
	'R' => 'Revocado',
	'C' => 'Cancelado',
);

$id = null;
if (isset($_GET['id'])) {
	$id = $_GET['id'];
} else if (isset($_POST['id'])) {
	$id = $_POST['id'];
}

if (empty($_GET['num_serie_search']) == false) {
	$_POST['num_serie_search'] = $_GET['num_serie_search'];
}

if (empty($_POST['num_serie_search_hidden']) == false) {
	$_POST['num_serie_search'] = $_POST['num_serie_search_hidden'];
}

if (empty($_GET['rfc_search']) == false) {
	$_POST['rfc_search'] = $_GET['rfc_search'];
}

if (empty($_POST['rfc_search_hidden']) == false) {
	$_POST['rfc_search'] = $_POST['rfc_search_hidden'];
}

if (empty($_GET['Delete']) == false) {
	if (empty($id) == false) {
		DB_query("DELETE FROM certificaciones WHERE idcsd = '$id'", $db);
		prnMsg(_('Se ha eliminado el CSD.'), 'info');
		$id = null;
	}
}

if (empty($_GET['Regenerate']) == false) {
	if (empty($_GET['num_serie']) == false) {
		$certificado = ComprobanteFactory::buildCertificate($_GET['num_serie']);
		if (empty($certificado)) {
			prnMsg(_('El CSD no pudo obtenerse desde el servidor FTP del SAT'), 'error');
		} else {
			prnMsg(_('CSD generado correctamente desde el servidor FTP del SAT'), 'success');
		}
	}
}

function setCSDData($name, $certPath) {
	
	$f = fopen($certPath . $name, "r");
	$csd = fread($f, 8192);
	fclose($f);
		
	$cert = '-----BEGIN CERTIFICATE-----' . PHP_EOL;
	$cert .= chunk_split(base64_encode($csd), 64, PHP_EOL);
	$cert .= '-----END CERTIFICATE-----' . PHP_EOL;
		
	$data = openssl_x509_parse($cert, 0);
		
	$rfc = explode('/', $data['subject']['x500UniqueIdentifier']);
	$rfc = trim($rfc[0]);
		
	$fileParts = pathinfo($name);
	$_POST['num_serie'] = $fileParts['filename'];
	$_POST['fecha_inicial'] = date('d-m-Y', $data['validFrom_time_t']);
	$_POST['fecha_final'] = date('d-m-Y', $data['validTo_time_t']);
	$_POST['estatus'] = "C";
	$_POST['rfc'] = $rfc;
		
	$today = strtotime(date('Y-m-d'));
	$initialDate = strtotime(date('Y-m-d', $data['validFrom_time_t']));
	$finalDate = strtotime(date('Y-m-d', $data['validTo_time_t']));
	if ($today <= $finalDate && $today >= $initialDate) {
		$_POST['estatus'] = "A";
	}	
}

if (isset($_POST['enviar']) || isset($_POST['modificar']) ) {
	
	if (isset($_POST['num_serie']) && strlen($_POST['num_serie']) < 3 && empty($_FILES['filecsd']['name'])) {
		$InputError = 1;
		prnMsg(_('El numero de serie del CSD debe ser de al menos 3 caracteres de longitud'), 'error');
	}
	
	if (isset($_POST['enviar']) && $InputError != 1) {
		
		if (empty($_FILES['filecsd']['name']) == false) {
			
			$fileParts = pathinfo($_FILES['filecsd']['name']);
			
			if ($fileParts['extension'] == 'cer') {
				if (move_uploaded_file($_FILES['filecsd']['tmp_name'], $certPath . $_FILES['filecsd']['name'])) {
					prnMsg(_('El archivo' . ' ' . $_FILES['filecsd']['name'] . ' ' . 'se subio con exito'), 'success');
					setCSDData($_FILES['filecsd']['name'], $certPath);			
				} else {
					prnMsg(_('El archivo' . ' ' . $_FILES['filecsd']['name'] . ' ' . 'no se subio con exito'), 'error');
					$InputError = 1;
				}
			} else {
				prnMsg(_('La extension del archivo no es valida'), 'error');
				$InputError = 1;
			}
			
		} else {
			$certificado = ComprobanteFactory::buildCertificate($_POST['num_serie']);
			if (empty($certificado)) {
				$InputError = 1;
				prnMsg(_('El CSD no pudo obtenerse desde el servidor FTP del SAT'), 'error');
			} else {
				setCSDData($_POST['num_serie'] . '.cer', $certPath);
				prnMsg(_('CSD generado correctamente desde el servidor FTP del SAT'), 'success');
			}
		}
	}
	
	unset($sql);

	
	$_POST['fecha_inicial'] = date_create_from_format('d-m-Y', $_POST['fecha_inicial']);
	$_POST['fecha_inicial'] = date_format($_POST['fecha_inicial'], 'Y-m-d');
	
	$_POST['fecha_final'] = date_create_from_format('d-m-Y', $_POST['fecha_final']);
	$_POST['fecha_final'] = date_format($_POST['fecha_final'], 'Y-m-d');
	
	if (isset($_POST['modificar']) AND ($InputError != 1)) {
		$sql = "UPDATE certificaciones SET 
			num_serie = '".$_POST['num_serie']."', 
			fecha_inicial = '" . $_POST['fecha_inicial'] . "',
			fecha_final = '" . $_POST['fecha_final'] . "',
			rfc = '" . $_POST['rfc'] . "',
			estatus = '" . $_POST['estatus'] . "' 
			WHERE idcsd = " . $id;
		
		$ErrMsg = _('La actualizacion fracaso porque');
		prnMsg( _('El CSD con el numero de serie') . ' ' . $_POST['num_serie'] . ' ' . _(' se ha actualizado.'), 'info');

	} else if (isset($_POST['enviar']) AND ($InputError != 1)) {
		
			$sql = "SELECT COUNT(*) FROM certificaciones WHERE num_serie = '" . $_POST['num_serie'] . "'";
			$result = DB_query($sql, $db);
			$myrow = DB_fetch_row($result);
			
		if ($myrow[0] > 0) {
			prnMsg(_('No se da de alta el CSD por que ya hay un registro guardado'), 'error');
		} else {
			$sql = "INSERT INTO certificaciones (num_serie, fecha_inicial, fecha_final, rfc, estatus)
				VALUES (
				'" . trim($_POST['num_serie']) . "',
				'" . trim($_POST['fecha_inicial']) . "', 
				'" . trim($_POST['fecha_final']) . "',
				'" . trim($_POST['rfc']) . "',
				'" . trim($_POST['estatus']) . "')";
			
			$ErrMsg = _('La insercion del CSD fracaso porque');
			prnMsg( _('El CSD con numero de serie') . ' ' . $_POST['num_serie'] . ' ' . _('se ha creado.'), 'info');
		}
	}
	
	unset($_POST['num_serie']);
	unset($_POST['fecha_inicial']);
	unset($_POST['fecha_final']);
	unset($_POST['rfc']);
	unset($_POST['estatus']);
	unset($_POST['id']);
	unset($id);
}

if (isset($sql) && $InputError != 1) {
	$result = DB_query($sql, $db);
}

if (!isset($_POST['Offset'])) {
	if(isset($_GET['Offset'])) {
		$_POST['Offset'] = $_GET['Offset'];
	} else {
		$_POST['Offset'] = 0;
	}
} else {
	if ($_POST['Offset'] == 0) {
		$_POST['Offset'] = 0;
	}
}
if(isset($_POST['Offset'])) {
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

echo "<script type='text/javascript' src='javascripts/datepicker/jsDatePick.min.1.3.js'></script>";
echo "<link rel='stylesheet' type='text/css' media='all' href='javascripts/datepicker/jsDatePick_ltr.min.css' />";
echo "<form method='post' name='forma' action=" . $_SERVER['PHP_SELF'] . "?" . SID . " enctype='multipart/form-data'>";
if (!isset($id) AND $_POST['id'] == '') {
	if ($Offset == 0) {
		$numfuncion = 1;
	} else {
		$numfuncion = $num_reg * $Offset + 1;
	}

	$Offsetpagina = 1;

	$sql = "SELECT COUNT(*) FROM certificaciones WHERE 1 ";
	
	if (empty($_POST['num_serie_search']) == false) {
		$sql .= " AND num_serie = '" .  $_POST['num_serie_search'] . "'";
	}
	
	if (empty($_POST['rfc_search']) == false) {
		$sql .= " AND rfc = '" .  $_POST['rfc_search'] . "'";
	}
	
	$result = DB_query($sql, $db);
	$ListCount = 0;
	if ($row = DB_fetch_row($result)) {
		$ListCount = $row[0];
	}
	
	$ListPageMax = ceil($ListCount / $num_reg);
	
	$sql = "SELECT idcsd, num_serie, DATE_FORMAT(fecha_inicial, '%d-%m-%Y') as fecha_inicial, DATE_FORMAT(fecha_final, '%d-%m-%Y') as fecha_final, rfc, estatus 
		FROM certificaciones WHERE 1 ";
	
	if (empty($_POST['num_serie_search']) == false) {
		$sql .= " AND num_serie = '" .  $_POST['num_serie_search'] . "'";
	}
	
	if (empty($_POST['rfc_search']) == false) {
		$sql .= " AND rfc = '" .  $_POST['rfc_search'] . "'";
	}
 	
	$sql .= " LIMIT " . $num_reg . " OFFSET " . ($Offset * $num_reg);
	
	$result = DB_query($sql, $db);

	if (!isset($id)) {
		
		echo "<table border=0 align='center'; width:800px; background-color:#ffff;' border='0' nowrap>";
		echo '<tr>';
		echo '<td align="center" colspan=2 class="texto_lista">';
		echo '<p align="center">';
		echo '<img src="images/imgs/configuracion.png" height=25" width="25" title="' . _('Altas, Bajas y Modificaciones de CSD') . '" alt="">' . ' ' . $title . '<br />';
		echo '</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table align="center" style="width:500px">';
		
		echo '<tr>';
		echo '<td class="texto_lista" colspan="5">' . _('Numero de Serie:');
		echo "<input type='text' name='num_serie_search' size='40' maxlength='100' VALUE = '" . $_POST['num_serie_search'] . "'></td>";
		echo '</tr>';
		
		echo '<tr>';
		echo '<td class="texto_lista" colspan="5">' . _('Rfc:');
		echo "<input type='text' name='rfc_search' size='40' maxlength='100' VALUE = '" . $_POST['rfc_search'] . "'></td>";
		echo '</tr>';
		
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
					<input type="hidden" name="previous" value=' . ($Offset-1) . '>
					<input tabindex=' . number_format($j + 7) . ' type="submit" name="Prev" value="' . _('Anterior') . '">
	                </td>';
			};
			if ($Offset <> $ListPageMax - 1) {
				echo '<td style="text-align:right">
					<input type="hidden" name="nextlist" value=' . ($Offset+1) . '>
					<input tabindex=' . number_format($j+9) . ' type="submit" name="Next" value="' . _('Siguiente') . '">
	                </td>';
			}
		}
		
		echo'</tr></table>';
	}
	
	echo '<table width=100% cellspacing=0 border=1 bordercolor=lightgray cellpadding=0 colspan=0 style="margin-top:0">';
		echo "<tr>
				<td colspan='6' class='titulo_azul' style='text-align:center'>
					<strong>Listado de CSD</strong>
				</td>
			  </tr>";
			echo "<tr>
				<td class='titulos_principales'>
				" . _('Id') . "
				</td>
				<td class='titulos_principales'>
				" . _('Serie') . "
				</td>
          		<td class='titulos_principales'>
				" . _('Fecha Inicial') . "
				</td>
		  		<td class='titulos_principales'>
				" . _('Fecha Final') . "
				</td>
          		<td class='titulos_principales'>
				" . _('Rfc') . "
				</td>
				<td class='titulos_principales'>
				" . _('Estado') . "
				</td>
          		<td class='titulos_principales'>
				</td>
          		<td class='titulos_principales'>
				</td>
				<td class='titulos_principales'>
				</td>
          		</tr>";

	$k = 0;
	while ($myrow = DB_fetch_array($result)) {
		
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}

		$csdFile = $certPath . $myrow['num_serie'] . ".cer";
		if (file_exists($csdFile)) {
			$csdFile = "<br /><a href='$csdFile'>" . _("Archivo CSD") . "</a>";
		} else {
			$csdFile = "<br />No existe archivo CSD";
		}
		
		printf("<td class='numero_normal'>%s</td>
        	<td class='texto_normal'>%s</td>
            <td class='texto_normal'>%s</td>
            <td class='texto_normal'>%s</td>
			<td class='texto_normal'>%s</td>
			<td class='texto_normal'>%s</td>
            <td class='texto_normal'><a href=\"%s&id=%s&Offset=%s&num_serie_search=%s&rfc_search=%s\">Modificar</a></td>
            <td class='texto_normal'><a onclick='borrar(this.href); return false;' href=\"%s&id=%s&Offset=%s&num_serie_search=%s&rfc_search=%s&Delete=1\">Eliminar</a></td>
			<td class='texto_normal'><a href=\"%s&Offset=%s&num_serie_search=%s&rfc_search=%s&num_serie=%s&Regenerate=1\">Regenerar CSD</a>%s</td>
          	</tr>",
        	$myrow['idcsd'],
            $myrow['num_serie'],
            $myrow['fecha_inicial'],
            $myrow['fecha_final'],
            $myrow['rfc'],
            $estatus[$myrow['estatus']],
            $_SERVER['PHP_SELF'] . '?' . SID,
            $myrow['idcsd'],
            $Offset,
            $_POST['num_serie_search'],
            $_POST['rfc_search'],
            $_SERVER['PHP_SELF'] . '?' . SID,
            $myrow['idcsd'],
            $Offset,
            $_POST['num_serie_search'],
            $_POST['rfc_search'],
            $_SERVER['PHP_SELF'] . '?' . SID,
            $Offset,
            $_POST['num_serie_search'],
            $_POST['rfc_search'],
            $myrow['num_serie'],
            $csdFile
		);
		$numfuncion += 1;
	}
	echo '</table>';
}
if (isset($id)) {
	echo "<div class='centre' style='text-align:center;margin:0 auto;'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('CSD Existentes') . '</a></div>';
}
 
if (isset($id) AND strlen($id) > 0) {

	$sql = "SELECT idcsd, num_serie, DATE_FORMAT(fecha_inicial, '%d-%m-%Y') as fecha_inicial, DATE_FORMAT(fecha_final, '%d-%m-%Y') as fecha_final, rfc, estatus 
		FROM certificaciones WHERE idcsd = $id";

	$result = DB_query($sql, $db);
	if (DB_num_rows($result) == 0) { 
		prnMsg(_('No hay registros.'), 'warn');
	} else {
		$myrow = DB_fetch_array($result);
		$_POST['id'] = $myrow['idcsd'];
		$_POST['fecha_inicial'] = $myrow['fecha_inicial'];
		$_POST['fecha_final'] = $myrow['fecha_final'];
		$_POST['rfc'] = $myrow['rfc'];
		$_POST['estatus'] = $myrow['estatus'];
		$_POST['num_serie'] = $myrow['num_serie'];
	}
}

echo '<br />';
if (isset($_POST['id'])) {
	echo "<input type=hidden name='id' VALUE='" . $id . "'>";
	echo "<input type=hidden name='Offset' VALUE='" . $_POST['Offset'] . "'>";
	echo "<input type=hidden name='num_serie_search_hidden' VALUE='" . $_POST['num_serie_search'] . "'>";
	echo "<input type=hidden name='rfc_search_hidden' VALUE='" . $_POST['rfc_search'] . "'>";
}

echo "<div class='texto_status'>" . _('Alta/Modificacion de CSD') . "</div><br />";
echo '<table style="text-align:center; margin:0 auto; width: 500px">';

if (!isset($id)) {
	echo '<tr>';
	echo '<td class="texto_lista">' . _('Archivo CSD (Opcional, si no se especifica trata de bajarlo de los servidores del SAT):') . "</td>";
	echo "<td style='text-align: left'><input type='file' name='filecsd' /></td>";
	echo '</tr>';
}

echo '<tr>';
echo '<td class="texto_lista">' . _('Numero de Serie:') . "</td>";
echo "<td style='text-align: left'><input type='text' name='num_serie' size='40' maxlength='100' VALUE = '" . $_POST['num_serie'] . "'></td>";
echo '</tr>';

echo '<tr>';
echo '<td class="texto_lista">' . _('Fecha Inicial:') . "</td>";
echo "<td style='text-align: left'><input type='text' name='fecha_inicial' id='fecha_inicial' size='40' maxlength='100' VALUE = '" . $_POST['fecha_inicial'] . "'></td>";
echo '</tr>';

echo '<tr>';
echo '<td class="texto_lista">' . _('Fecha Final:') . "</td>";
echo "<td style='text-align: left'><input type='text' name='fecha_final' id='fecha_final' size='40' maxlength='100' VALUE = '" . $_POST['fecha_final'] . "'></td>";
echo '</tr>';

echo '<tr>';
echo '<td class="texto_lista">' . _('Rfc:') . "</td>";
echo "<td style='text-align: left'><input type='text' name='rfc' size='40' maxlength='100' VALUE = '" . $_POST['rfc'] . "'></td>";
echo '</tr>';

echo '<tr>';
echo '<td class="texto_lista">' . _('Estado:') . "</td>";
echo "<td style='text-align: left'><select name='estatus'>";
foreach ($estatus as $key => $value) {
	if ($_POST['estatus'] == $key) {
		echo "<option selected='selected' value='$key'>$value</option>";
	} else {
		echo "<option value='$key'>$value</option>";
	}
}
echo "</select></td>";
echo '</tr>';

echo "<tr>";
echo "<td coslpan='2' style='text-align: right'>";
if (!isset($id)) {
	echo "<input type='Submit' name='enviar' value='" . _('Enviar') . "'>";
} else if (isset($id)) {
   	echo "<input type='Submit' name='modificar' value='" . _('Actualizar') . "'>";
}
echo "</td> </tr>";
echo '</table>';	
echo '</form>';
echo '<script type="text/javascript">';
?>
g_l["MONTHS"] = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
g_l["DAYS_3"] = ["Dom", "Lun", "Mar", "Mie", "Jue", "Vi", "Sab"];
g_l["MONTH_FWD"] = "Mover un mes adelante";
g_l["MONTH_BCK"] = "Mover un mes atras";
g_l["YEAR_FWD"] = "Mover un a\u00f1o adelante";
g_l["YEAR_BCK"] = "Mover un a\u00f1o atras";
g_l["CLOSE"] = "Cerrar calendario";
g_l["ERROR_2"] = g_l["ERROR_1"] = "Objeto fecha no valido!";
g_l["ERROR_4"] = g_l["ERROR_3"] = "Referencia no valida";

window.onload = function(){
	new JsDatePick({
		useMode: 2,
		target: "fecha_inicial",
		dateFormat: "%d-%m-%Y"
	});
	new JsDatePick({
		useMode: 2,
		target: "fecha_final",
		dateFormat: "%d-%m-%Y"
	});
} 

function borrar(href) {
	if (confirm("Desea borrar el registro?")) {
		window.location = href;
	}
	return false;
}
<?php
echo '</script>';
include('includes/footer.inc');
?>