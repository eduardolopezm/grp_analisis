<?php

/*
 
 ARCHIVO MODIFICADO POR: Desarrollador
 FECHA DE MODIFICACION: 08-FEB-2010
 CAMBIOS: 
	1. SE AGREGO COMBO PARA CAPTURA DE USUARIO DE TAREA DE PRODUCCION
 FIN DE CAMBIOS
 
*/

/*ini_set('display_errors', 1); 
ini_set('log_errors', 1); 
ini_set('error_log', dirname(__FILE__) . '/error_log.txt'); 
error_reporting(E_ALL);*/

include('includes/session.inc');
$title = _('Altas, Bajas y Modificaciones de Entidades');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$funcion=659;//
include('includes/SecurityFunctions.inc');




if (isset($_POST['legalid'])) {
	$legalid= $_POST['legalid'];
} else {
	$legalid="0";
}	

if (isset($_POST['Nombre'])) {
	$Nombre= $_POST['Nombre'];
} else {
	$Nombre="";
}

if (isset($_POST['Campo1'])) {
	$Campo1= $_POST['Campo1'];
} else {
	$Campo1="";
}

if (isset($_POST['Campo2'])) {
	$Campo2= $_POST['Campo2'];
} else {
	$Campo2="";
}

if (isset($_POST['Campo3'])) {
	$Campo3= $_POST['Campo3'];
} else {
	$Campo3="";
}

if (isset($_POST['int_1'])) {
	$int_1= $_POST['int_1'];
} else {
	$int_1=0;
}
if (isset($_POST['int_2'])) {
	$int_2= $_POST['int_2'];
} else {
	$int_2=0;
}
if (isset($_POST['int_3'])) {
	$int_3 = $_POST['int_3'];
} else {
	$int_3 =0;
}
if (isset($_POST['doble1'])) {
	$doble1 = $_POST['doble1'];
} else {
	$doble1 =0;
}
if (isset($_POST['doble2'])) {
	$doble2 = $_POST['doble2'];
} else {
	$doble2 =0;
}
if (isset($_POST['doble3'])) {
	$doble3 = $_POST['doble3'];
} else {
	$doble3 =0;
}
$num_reg=50;

if (isset($_POST['num_reg'])) {
	$num_reg = $_POST['num_reg'];
}

//esta es la variable con la que nos guiamos para saber ssi es la primera vez que entran
//y para poder mostrar los datos del seleccionado cuando van a modificar
if (isset($_GET['u_entidad'])) {
	$u_entidad = $_GET['u_entidad'];
} elseif (isset($_POST['u_entidad'])) {
	$u_entidad = $_POST['u_entidad'];
}

$InputError = 0;
if (isset($_POST['enviar']) || isset($_GET['borrar']) || isset($_POST['modificar']) ) {
	
	if (!isset($_GET['borrar'])){	
		if (strlen($_POST['Nombre'])<3){
			$InputError = 1;
			prnMsg(_('El nombre de la Entidad debe ser de al menos 3 caracteres de longitud'),'error');
		}
		
		if ($_POST['legalid'] == '0'){
			$InputError = 1;
			prnMsg(_('Seleccione Razon Social...'),'error');
		}
	}
	unset($sql);
	
	if (isset($_POST['modificar'])and ($InputError != 1) and ($InputError != 2)) {
		
		$sql = "UPDATE usrEntidades
		SET legalid='" . $_POST['legalid'] . "',
			Nombre = '" . $_POST['Nombre'] . "',
			Campo1 = '" . $_POST['Campo1'] . "',
			Campo2 = '" . $_POST['Campo2'] . "',
			Campo3 = '" . $_POST['Campo3'] . "',
			int_1 = '" . $_POST['int1'] . "',
			int_2 = '" . $_POST['int2'] . "',
			int_3 = '" . $_POST['int3'] . "',
			doble1 = '" . $_POST['doble1'] . "',
			doble2 = '" . $_POST['doble2'] . "',
			doble3 = '" . $_POST['doble3'] . "',
			fecha1 = Now(),
			fecha2 = Now(),
			fecha3 = Now()
		WHERE u_entidad = '" . $_POST['u_entidad'] . "'";
		
		//echo $sql;
		
		$ErrMsg = _('La actualización de la Entidad fracaso porque');
		prnMsg( _('El Entidad').' ' .$_POST['Nombre'] . ' ' . _(' se ha actualizado.'),'info');
		
	} elseif (isset($_GET['borrar'])and ($InputError != 1) and ($InputError != 2)) {
			
		$sql="DELETE FROM usrEntidades WHERE u_entidad = '" . $_GET['u_entidad']."'";
		prnMsg(_('La Entidad ha sido eliminada ') . '!','info');
			
	} elseif (isset($_POST['enviar'])and ($InputError != 1) and ($InputError != 2)) {
			$sql= "select count(*) from usrEntidades where u_entidad = '" . $_POST['u_entidad'] . "'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('No se da de alta la Entidad por que ya hay un registro guardado'),'error');
		} else {
			$sql = "INSERT INTO usrEntidades (legalid, Nombre, Campo1, Campo2, Campo3, int_1, int_2, int_3, doble1, doble2, doble3,fecha1,fecha2,fecha3)
			VALUES ('" . $_POST['legalid'] . "','" . $_POST['Nombre'] . "','" . $_POST['Campo1'] . "','" . $_POST['Campo2'] . "','" . $_POST['Campo3'] . "','" .
			$_POST['int_1'] . "','" . $_POST['int_2'] . "','" . $_POST['int_3'] . "','" . $_POST['doble1'] . "','" . $_POST['doble2'] . "','" .
			$_POST['doble3'] . "',Now(),Now(),Now())";
			$ErrMsg = _('La inserccion del Area fracaso porque');
			prnMsg( _('La Entidad').' ' .$_POST['Nombre'] . ' ' . _('se ha creado.'),'info');
		}
	}
	unset($_POST['legalid']);
	unset($_POST['Nombre']);
	unset($_POST['Campo1']);
	unset($_POST['Campo2']);
	unset($_POST['Campo3']);
	unset($_POST['int_1']);
	unset($_POST['int_2']);
	unset($_POST['int_3']);
	unset($_POST['doble1']);
	unset($_POST['doble2']);
	unset($_POST['doble3']);
	unset($u_entidad);	
}


if (isset($sql) && $InputError != 1 && ($InputError != 2)) {
	//echo $sql;
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

if (isset($_POST['Prev'])) {
	$Offset = $_POST['previous'];
}

echo "<form method='post' action=". $_SERVER['PHP_SELF'] . "?". SID . ">";

if (!isset($u_entidad) ) {
	echo '<table style="margin:auto;"><tr>
	<td>
		' . _('Razon Social') . '<br>
		<select name="slegalid">
			<option selected value="0">Todas</option>';
			///Pinta las razones sociales
			$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
			$SQL = $SQL .	" FROM sec_unegsxuser u,tags t JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid";
			$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
			$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "'
					  GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY t.tagref";		
			$result=DB_query($SQL,$db);
			while ($myrow=DB_fetch_array($result)){
				if (isset($_POST['legalid']) and $_POST['legalid']==$myrow["legalid"]){
					echo '<option selected value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
				} else {
					echo '<option value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
				}
			}
		echo '</select>
		
		
		
	</td>
	<td>
		' . _('Nombre') . '<br><input type="text" name="sNombre" value="'. $_POST['sNombre'] .'" size=25 maxlength=55>
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
	$Offsetpagina=1;
	
	
//esta parte sirve para mostrar la primera tabla con todos los registros existentes

	if (!isset($_POST['slegalid'])){
		$_POST['slegalid'] = 0;
	}

	
	$sql = "SELECT e.u_entidad, e.Nombre, l.legalname
		FROM usrEntidades e
			LEFT JOIN  legalbusinessunit l ON e.legalid = l.legalid
			WHERE (e.legalid = '" . $_POST['slegalid'] . "' or '" .  $_POST['slegalid'] . "' = '0')" ;	
	
	
	if (strlen($_POST['sNombre'])>=1) {
		$sql=$sql." AND (e.Nombre like '%" . $_POST['sNombre'] . "%')";
	}
	
	$sql=$sql." ORDER BY l.legalname, e.Nombre";
	//echo "<br>" . $sql;
	$result = DB_query($sql,$db);
	$ListCount=DB_num_rows($result);
	
	if ( DB_num_rows($result) == 0 ) {
		
		prnMsg( _('No hay registros con esa Busqueda.'),'warn');
	}
		
	
	$sql = $sql . " LIMIT ".$num_reg." OFFSET ". ($Offset * $num_reg) ;
	//echo "<br>" . $sql;
	$result = DB_query($sql,$db);
	
	//$ListCount=DB_num_rows($result);
	//echo "<br>ListCount: " . $ListCount;
	//echo "<br>NumReg: " . $num_reg;
	$ListPageMax=ceil($ListCount/$num_reg);
	
	
/// fin consulta join	
	
	//echo "<form  method='post' action=". $_SERVER['PHP_SELF'] . "?". SID . ">";
	if (!isset($areacode)) {
		echo "<div class='centre'>" ._('LISTADO DE ENTIDADES'). "</div>";
		echo '<table width=80% style="margin:auto;">';
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
	
	echo '<table border="1" width="80%" style="margin:auto;">';
	echo "<tr><th>" . _('Codigo') . "</th>
	<th>" . _('Razon Social') . "</th>
	<th>" . _('Entidad') . "</th>
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
		
		printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td style='text-align:center;'><a href=\"%s&u_entidad=%s\">Modificar</a></td>
			<td style='text-align:center;'><a href=\"%s&u_entidad=%s&borrar=1\">Eliminar</a></td>
			</tr>",
			$myrow['u_entidad'],
			$myrow['legalname'],
			$myrow['Nombre'],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow['u_entidad'],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow['u_entidad']
			);
		$numfuncion=$numfuncion+1;
	} 
	echo '</table>';
	echo '</form>';
}


if (isset($u_entidad)) {
        $sql = "SELECT *
		FROM usrEntidades
		WHERE u_entidad='" . $u_entidad  ."'" ;
	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);
	$_POST['legalid']=$myrow['legalid'];
	$_POST['Nombre'] = $myrow['Nombre'];
	$_POST['Campo1']=$myrow['Campo1'];
	$_POST['Campo2']=$myrow['Campo2'];
	$_POST['Campo3']=$myrow['Campo3'];
	$_POST['int_1']=$myrow['int_1'];
	$_POST['int_2']=$myrow['int_2'];
	$_POST['int_3']=$myrow['int_3'];
	$_POST['doble1']=$myrow['doble1'];
	$_POST['doble2']=$myrow['doble2'];
	$_POST['doble3']=$myrow['doble3'];
}

if (isset($u_entidad)) {
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Entidades existentes') . '</a></div>';
}
echo "<form  method='post' action=". $_SERVER['PHP_SELF'] . "?". SID . ">";

echo '<br>';
/*if(isset($_POST['areacode'])) {
	echo "<input type=hidden name='areacode' VALUE='" . $_POST['areacode'] . "'>";
}*/
echo "<div class='centre'><hr width=60%>" ._('ALTA/MODIFICACION DE ENTIDADES'). "</div><br>";
	echo '<table style="margin:auto;">';
	echo'<tr><td>' . _('Razon Social') . ':</td>
		<td><select name="legalid">
			<option selected value="0">Todas</option>';
			///Pinta las razones sociales
			$SQL = "SELECT legalbusinessunit.legalid,legalbusinessunit.legalname";
			$SQL = $SQL .	" FROM sec_unegsxuser u,tags t JOIN legalbusinessunit ON t.legalid = legalbusinessunit.legalid";
			$SQL = $SQL .	" WHERE u.tagref = t.tagref ";
			$SQL = $SQL .	" and u.userid = '" . $_SESSION['UserID'] . "'
					  GROUP BY legalbusinessunit.legalid,legalbusinessunit.legalname ORDER BY t.tagref";		
			$result=DB_query($SQL,$db);
			while ($myrow=DB_fetch_array($result)){
				if (isset($_POST['legalid']) and $_POST['legalid']==$myrow["legalid"]){
					echo '<option selected value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
				} else {
					echo '<option value=' . $myrow['legalid'] . '>' . $myrow['legalid'].' - ' .$myrow['legalname'];
				}
			}
		echo "</select>
		</td></tr>
	<tr>";
	echo '<tr><td>' . _('Nombre del Area') . ":</td>
	<td><input type='text' name='Nombre' size=40 maxlength=100 VALUE='" .$_POST['Nombre'] . "'></td></tr>
	<tr>";
	echo '<tr><td>' . _('Vendedor') . ":</td>
	<td><input type='text' name='Campo1' size=40 maxlength=100 VALUE='" .$_POST['Campo1'] . "'></td></tr>
	<tr>";
	echo '<tr><td>' . _('Comprador') . ":</td>
	<td><input type='text' name='Campo2' size=40 maxlength=100 VALUE='" .$_POST['Campo2'] . "'></td></tr>
	<tr>";
	echo '<tr><td>' . _('Monto') . ":</td>
	<td><input type='text' name='doble1' size=15 maxlength=15 VALUE='" .$_POST['doble1'] . "' class='number'></td></tr>
	<tr>";
	echo "<td>";
		echo "<input type='hidden' name='Campo3' VALUE='" .$_POST['Campo3'] . "'>";
		echo "<input type='hidden' name='int_1' VALUE='" .$_POST['int_1'] . "'>";
		echo "<input type='hidden' name='int_2' VALUE='" .$_POST['int_2'] . "'>";
		echo "<input type='hidden' name='int_3' VALUE='" .$_POST['int_3'] . "'>";
		echo "<input type='hidden' name='doble2' VALUE='" .$_POST['doble2'] . "'>";
		echo "<input type='hidden' name='doble3' VALUE='" .$_POST['doble3'] . "'>";
		echo "<input type='hidden' name='u_entidad' value='" . $u_entidad . "'>";
	echo "</td>";
	echo "</tr>";
	echo'</table>';
	//aqui verifica si esta vacia la variable si lo esta el valor de la accion sera enviar
	
	if (!isset($u_entidad)) {
		echo "<div class='centre'><input type='Submit' name='enviar' value='" . _('Enviar') . "'></div>";
	}
	//aqui verifica que este llena la variable si lo esta el valor de la accion sera modificar
	elseif (isset($u_entidad)) {
		
		echo "<div class='centre'><input type='Submit' name='modificar' value='" . _('Actualizar') . "'></div>";
		
	}
echo '</form>';
include('includes/footer.inc');
?>