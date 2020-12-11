<?php
/* Elaboro Jesus Guadalupe Vargas Montes
 * Fecha Creacion 14 Noviembre 2014
 * 1. Se creo el mantenimiento de organo
*/
include('includes/session.inc');
$title = _('Altas, Bajas y Modificaciones de Organo Superior');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$num_reg=50;

if (isset($_POST['num_reg'])) {
	$num_reg = $_POST['num_reg'];
}

if (isset($_GET['id_organo'])) {
	$_POST['id_organo'] = $_GET['id_organo'];
} elseif (isset($_POST['id_organo'])) {
	$_POST['id_organo'] = $_POST['id_organo'];
}

if(isset($_POST['cve_organosuperior'])) {
	$_POST['cve_organosuperior'] = $_POST['cve_organosuperior'];
}elseif(isset ($_GET['cve_organosuperior'])){
    $_POST['cve_organosuperior'] = $_GET['cve_organosuperior'];
}

if (isset($_POST['organosuperior'])) {
	$_POST['organosuperior'] = $_POST['organosuperior'];
}elseif(isset ($_GET['organosuperior'])){
    $_POST['organosuperior'] = $_GET['organosuperior'];
}

if (isset($_GET['organo'])) {
	$_POST['organo'] = $_GET['organo'];
} elseif (isset($_POST['organo'])) {
	$_POST['organo'] = $_POST['organo'];
}

$FromYear=date('Y');

$InputError = 0;
if (isset($_POST['enviar']) || isset($_GET['borrar']) || isset($_POST['modificar']) ) {
    if (isset($_POST['cve_organosuperior']) && strlen($_POST['cve_organosuperior']) ==""){
        $InputError = 1;
        prnMsg(_('Debe agregar la clave del organo superior'),'error');
    }
    if (isset($_POST['organosuperior']) && strlen($_POST['organosuperior']) ==""){
        $InputError = 2;
        prnMsg(_('Debe agregar el nombre del Organo Superior'),'error');
    }
    unset($sql);
    if (isset($_POST['modificar'])and ($InputError != 1) and ($InputError != 2)){
        $sql = "UPDATE g_cat_organo_superior
                SET cve_organosuperior='" .$_POST['cve_organosuperior']." ',
                    organosuperior='" .$_POST['organosuperior']." ',
                    anio= '".$FromYear."'
                WHERE id_organo='".$_POST['id_organo']."'";
        $ErrMsg = _('La actualizacion del organo superior fallo porque');
        prnMsg( _('El Organo Superior ').' ' .$_POST['cve_organosuperior'] . ' ' .$_POST['organosuperior'].' '. _(' se ha actualizado.'),'info');
		
    }elseif (isset($_GET['borrar'])){
        $sql="DELETE FROM g_cat_organo_superior WHERE id_organo='" . $_GET['id_organo']."'";
        prnMsg(_('El Organo Superior ha sido eliminado ') . '!','info');
    }elseif (isset($_POST['enviar'])and ($InputError != 1) and ($InputError !=2)) {
        $sql = "INSERT INTO g_cat_organo_superior (cve_organosuperior, 
                                                    organosuperior,
                                                    anio)
                VALUES ('".$_POST['cve_organosuperior']."',
                        '".$_POST['organosuperior']."',
                        '".anio."')";
        $ErrMsg = _('La inserccion del objetivo fracaso porque');
        prnMsg( _('El Organo Superior').' ' .$_POST['cve_organosuperior'] . ' ' .$_POST['organosuperior'].' ' . _('se ha creado exitosamente...!'),'info');
    }
    unset($_POST['id_organo']);
    unset($_POST['cve_organosuperior']);
    unset($_POST['organosuperior']);
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

if (isset($_POST['Prev'])) {
	$Offset = $_POST['previous'];
}

echo "<form method='post' action=". $_SERVER['PHP_SELF'] . "?". SID . ">";

if (!isset($_POST['id_organo']) ) {
    echo '<table><tr>
        <td>' . _('Clave') . '<br><input type="text" name="cve_organosuperior" value="'.$_POST['cve_organosuperior'].'" size=25 maxlength=55>
	</td>
	<td>
		' . _('Nombre') . '<br><input type="text" name="organosuperior" value="'.$_POST['organosuperior'].'" size=25 maxlength=55>
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
	$sql = "SELECT *
		FROM g_cat_organo_superior";
	if (strlen($_POST['cve_organosuperior'])>=1) {
		$sql=$sql ." WHERE cve_organosuperior =". $_POST['cve_organosuperior'];
		if (strlen($_POST['cve_organosuperior'])>=1) {
			$sql=$sql." AND organosuperior like '%".$_POST['organosuperior']."%'";
		}
	} Elseif (strlen($_POST['organosuperior'])>=1) {
            $sql=$sql." WHERE organosuperior like '%".$_POST['organosuperior']."%'";
        }
	$result = DB_query($sql,$db);
	
	if ( DB_num_rows($result) == 0 ) {
	
		prnMsg( _('No hay registros con esa Busqueda.'),'warn');
	}
	
	$sql = $sql . " LIMIT ".$num_reg." OFFSET ". ($Offset * $num_reg) ;
	$result = DB_query($sql,$db);
	$ListCount=DB_num_rows($result);
	$ListPageMax=ceil($ListCount/$num_reg);
	
	/// fin consulta join
	
	if (!isset($prdtpodocid)) {
		echo "<div class='centre'>" ._('LISTADO DE ORGANOS SUPERIORES '). "</div>";
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
		echo '<table border=1 width=70%>';
		echo "<tr><th>" . _('') . "</th>
			  <th>" . _('Clave') . "</th>
			  <th>" . _('Organo') . "</th>
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
				<td style='text-align:center;'><a href=\"%s&id_organo=%s\">Modificar</a></td>
				<td style='text-align:center;'><a href=\"%s&id_organo=%s&borrar=1\">Eliminar</a></td>
				</tr>",
					$myrow['id_organo'],
					$myrow['cve_organosuperior'],
					$myrow['organosuperior'],
					$_SERVER['PHP_SELF'] . '?' . SID,
					$myrow['id_organo'],
					$_SERVER['PHP_SELF'] . '?' . SID,
					$myrow['id_organo']
			);
			$numfuncion=$numfuncion+1;
		}
		echo '</table>';
		echo '</form>';
}
if (isset($_POST['id_organo'])) {
	$sql = "SELECT *
		FROM g_cat_organo_superior
		WHERE id_organo ='". $_POST['id_organo']."'" ;
	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);
	$_POST['id_organo']=$myrow['id_organo'];
	$_POST['cve_organosuperior'] = $myrow['cve_organosuperior'];
	$_POST['organosuperior'] = $myrow['organosuperior'];
}
if (isset($_POST['id_organo'])) {
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Organos Superiores Existentes') . '</a></div>';
}
echo "<form  method='post' action=". $_SERVER['PHP_SELF'] . "?". SID . ">";
echo '<br>';
echo "<div class='centre'><hr width=60%>" ._('ALTA/MODIFICACION DE Organos Superiores'). "</div><br>";
echo '<table>';
echo '<tr><td><input type="hidden" name="id_organo" size="40" maxlength="100" VALUE="' .$_POST['id_organo'] . '"></td></tr>';
echo '</td>';
echo '<tr><td>' . _('Clave') . ":</td>";
echo "<td><input type='text' name='cve_organosuperior' size=40 maxlength=100 VALUE='" .$_POST['cve_organosuperior'] . "'></td></tr>";
echo '<tr><td>' . _('Nombre') . ":</td>";
echo "<td><input type='text' name='organosuperior' size=40 maxlength=100 VALUE='" .$_POST['organosuperior'] . "'></td></tr>";
echo'</table>';
//aqui verifica si esta vacia la variable si lo esta el valor de la accion sera enviar

if (!isset($_POST['id_organo'])) {
	echo "<div class='centre'><input type='Submit' name='enviar' value='" . _('Enviar') . "'></div>";
}
//aqui verifica que este llena la variable si lo esta el valor de la accion sera modificar
elseif (isset($_POST['id_organo'])) {

	echo "<div class='centre'><input type='Submit' name='modificar' value='" . _('Actualizar') . "'></div>";

}
echo '</form>';
include('includes/footer.inc');
?>