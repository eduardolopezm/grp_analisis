<?php
/* Elaboro Jesus Guadalupe Vargas Montes
 * Fecha Creacion 18 Junio 2014
 * 1.Mantenimiento de clave presupuestal
*/
/*
 * AHA
* 7-Nov-2014
* Cambio de ingles a espa–ol los mensajes de usuario de tipo error,info,warning, y success.
*/
include('includes/session.inc');
$title = _('Altas, Bajas y Modificaciones de Clave Presupuestal');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$num_reg=50;

if (isset($_POST['num_reg'])) {
	$num_reg = $_POST['num_reg'];
}

if (isset($_GET['accountcodepres'])) {
	$accountcodepres = $_GET['accountcodepres'];
} elseif (isset($_POST['accountcodepres'])) {
	$accountcodepres = $_POST['accountcodepres'];
}

if (isset($_GET['accountnamepres'])) {
	$accountnamepres = $_GET['accountnamepres'];
} elseif (isset($_POST['accountnamepres'])) {
	$accountnamepres = $_POST['accountnamepres'];
}

if (isset($_GET['nombre'])) {
	$nombre = $_GET['nombre'];
} elseif (isset($_POST['nombre'])) {
	$nombre = $_POST['nombre'];
}

if (isset($_GET['accountcode'])) {
	$accountcode = $_GET['accountcode'];
} elseif (isset($_POST['accountcode'])) {
	$accountcode = $_POST['accountcode'];
}

$InputError = 0;
if (isset($_POST['accountcodepres']) && strlen($_POST['accountcodepres']) ==""){
	$InputError = 1;
	prnMsg(_('Debe agregar el codigo presupuestal'),'error');
}

if (isset($_POST['accountnamepres']) && strlen($_POST['accountnamepres']) ==""){
	$InputError = 1;
	prnMsg(_('Debe agregar el nombre de codigo presupuestal'),'error');
}

if (isset($_POST['enviar']) || isset($_GET['borrar']) || isset($_POST['modificar']) ) {
	unset($sql);
	if (isset($_GET['borrar'])){
			
				$sql="DELETE 
						FROM chartmasterclavepresupuestal 
						WHERE chartmasterclavepresupuestal.accountcodepres='" . $_GET['accountcodepres']."'";
				prnMsg(_('La clave presupuestal se ha eliminado de manera correcta') . '!','info');
				
	}elseif (isset($_POST['enviar'])and ($InputError != 1)) {
			$sql = "INSERT INTO chartmasterclavepresupuestal (chartmasterclavepresupuestal.accountcodepres, 
												chartmasterclavepresupuestal.accountnamepres)
					VALUES ('".$_POST['accountcodepres']."', 
							'".$_POST['accountnamepres']."')";
			
			$ErrMsg = _('La inserccion de la clave presupuestal  fracaso porque');
			prnMsg( _('La clave presupuestal').' ' .$_POST['accountnamepres'] . ' ' . _('se ha creado exitosamente...!'),'info');
			
	}elseif (isset($_POST['modificar'])and ($InputError != 1)) {
		
			$sql = "UPDATE chartmasterclavepresupuestal SET
							chartmasterclavepresupuestal.accountcodepres = '".$_POST['accountcodepres']."',
							chartmasterclavepresupuestal.accountnamepres = '".$_POST['accountnamepres']."'
					WHERE chartmasterclavepresupuestal.accountcodepres ='".$_POST['accountcodepres']."'";
		//	echo $sql;
			$ErrMsg = _('La actualizacion de la clave presupuestal  fracaso porque');
			prnMsg( _('La clave presupuestal ').' ' .$_POST['accountnamepres'] . ' ' . _('se ha actualizdo exitosamente...!'),'info');
			//echo $sql;
	}
	unset($_POST['accountcodepres']);

}

if (isset($sql)) {
	$result = DB_query($sql,$db,$ErrMsg);
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
//
if (isset($_POST['Prev'])) {
	$Offset = $_POST['previous'];
}

echo "<form method='post' action=". $_SERVER['PHP_SELF'] . "?". SID . ">";
echo "<table border=0 align='center'; width:0; background-color:#ffff;' border=0 width=100% nowrap>";
echo '<tr>
			<td align="center" colspan=2 class="texto_lista">
				<p align="center">
				<img src="images/imgs/configuracion.png" height=25" width="25" title="' . _('Altas, Bajas y Modificaciones de Agrupaciones') . '" alt="">' . ' ' . $title . '<br>
			</td>
		  </tr>';
echo '</table>';
if (!isset($accountcodepres)) {
	echo '<table align="center" width="100%">
			<tr><td height=30></td></tr>
			<tr>
				<td width="25%" class="texto_lista">
					' . _('Clave Presupuestal:') . '
					<input type="text" name="accountcode" value="'.$accountcode.'" size=25 maxlength=55>
				</td>
					<td width="25%" class="texto_lista">
						' . _('Nombre:') . '
						<input type="text" name="nombre" value="'.$nombre.'" size=25 maxlength=55>
					</td>';
					echo '<td width="25%" valign=bottom>&nbsp;&nbsp;
						  	<input type=submit name=buscar value=' . _('Buscar') . '>
						  </td>
			</tr>
		  </table>';
	//echo "<div class='centre'><hr width=50%></div><br>";
	if ($Offset==0) {
		$numfuncion=1;
	} else {
		$numfuncion=$num_reg*$Offset+1;
	}
	$Offsetpagina=1;
	
	//esta parte sirve para mostrar la primera tabla con todos los registros existentes
	$sql = "Select *
			From chartmasterclavepresupuestal
			WHERE 1";
	if (strlen($accountcode)>=1) {
		$sql=$sql ." AND chartmasterclavepresupuestal.accountcodepres = '". $accountcode."'";
		
	}elseif(strlen($nombre)>=1) {
			
		$sql=$sql." AND chartmasterclavepresupuestal.accountnamepres like '%".$nombre."%'";
		
	}
			
	$result = DB_query($sql,$db);
	
	if ( DB_num_rows($result) == 0 ) {
	
		prnMsg( _('No hay registros con esa Busqueda.'),'warn');
	}
	$sql = $sql . " ORDER BY chartmasterclavepresupuestal.accountnamepres ";
	$sql = $sql . " LIMIT ".$num_reg." OFFSET ". ($Offset * $num_reg) ;
	//echo '<pre>'.$sql;
	$result = DB_query($sql,$db);
	$ListCount=DB_num_rows($result);
	$ListPageMax=ceil($ListCount/$num_reg);
	
	/// fin consulta join
	
	if (!isset($accountcodepres)) {
		//echo "<div class='centre'>" ._('LISTADO DE CAPITULOS'). "</div>";
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
		echo '<table width=100% cellspacing=0 border=1 bordercolor=lightgray cellpadding=0 colspan=0 style="margin-top:0">';
		echo "<tr>
				<td colspan='6' class='titulo_azul'>
					" ._('Listado de Cuentas Contables'). "
				</td>";
		echo '</tr>';
			echo "<tr>
					<td class='titulos_principales'>" . _('Clave') . "</td>
			  		<td class='titulos_principales'>" . _('Nombre') . "</td>
			  		<td class='titulos_principales'></td>		
			  		<td class='titulos_principales'></td>
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

			printf("<td class='numero_normal'>%s</td>
					<td class='texto_normal'>%s</td>
					<td class='numero_normal'><a href=\"%s&accountcodepres=%s\">Modificar</a></td>
					<td class='numero_normal'><a href=\"%s&accountcodepres=%s&borrar=1\">Eliminar</a></td>
					</tr>",
					$myrow['accountcodepres'],
					$myrow['accountnamepres'],
					$_SERVER['PHP_SELF'] . '?' . SID,
					$myrow['accountcodepres'],
					$_SERVER['PHP_SELF'] . '?' . SID,
					$myrow['accountcodepres']
			);
			$numfuncion=$numfuncion+1;
		}
		echo '</table>';
		echo '</form>';
}
if (isset($accountcodepres)) {
	
	$sql = "Select chartmasterclavepresupuestal.accountcodepres,
					chartmasterclavepresupuestal.accountnamepres
			From chartmasterclavepresupuestal
			WHERE chartmasterclavepresupuestal.accountcodepres = '".$accountcodepres."'";
			$result = DB_query($sql, $db);
			$myrow = DB_fetch_array($result);
			$_POST['accountcodepres']=$myrow['accountcodepres'];
			$_POST['accountnamepres'] = $myrow['accountnamepres'];
}
if (isset($accountcodepres)) {
	echo "<div class='texto_status'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'><u>" . _(' Clave Presupuestal') . '</u></a></div>';
}
echo "<form  method='post' action=". $_SERVER['PHP_SELF'] . "?". SID . ">";
echo '<br>';
echo "<div class='texto_status'>" ._('Alta/Modificacion Clave Presupuestal'). "</div><br>";
echo '<table align="center" border=0 width=95% style="text-align:center">';
echo '<tr>';
echo '<td class="texto_lista">'._('Clave Presupuestal:') . "</td>";
echo '<td><input type="text" name="accountcodepres" value="'.$_POST['accountcodepres'].'">';
echo "</tr>";
echo '<tr>';
echo '<td class="texto_lista">'._('Nombre:') . "</td>";
echo '<td><input type="text" size=40 name="accountnamepres" value="'.$_POST['accountnamepres'].'"></td>';
echo "</tr>";
echo "<tr>";
echo "<td colspan=2>";	  			

if (!isset($accountcodepres)) {
	echo "<input type='Submit' name='enviar' value='" . _('Enviar') . "'>";
}elseif(isset($accountcodepres) and !isset($_GET['borrar'])){
	echo "<input type='Submit' name='modificar' value='" . _('Actualizar') . "'>";
}
echo "</td></tr>";
echo'</table>';
echo '</form>';
include('includes/footer.inc');
?>