<?php
/*
ARCHIVO CREADO POR: RUBIO OLGUIN LUCERO
FECHA DE MODIFICACION: 07/JULIO/2010
ARCHIVO MODIFICADO POR: MARIA ISABEL ESTRADA VELAZQUEZ
FECHA DE MODIFICACION: 11/AGOSTO/2010
DESCRIPCION: REALIZA ALTAS BAJAS Y MODIFICACIONES DE RUTAS
*/
$funcion=650;
$PageSecurity = 3;
include('includes/session.inc');
$title = _('Altas, Bajas y Consulta de rutas');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

//si variable encargada de busqueda viene llena desde la forma
if (isset($_POST['rutab'])) {
	//almacenaje de valores a varible local
	$rutab=$_POST['rutab'];
}
if (isset($_POST['area'])) {
	//almacenaje de valores a varible local
	$area=$_POST['area'];
}

//variable delimitadora de registros por pantalla
$num_reg=10;
//si variable contador de registros viene llena desde la forma
if (isset($_POST['num_reg'])) {
	//almacenaje de valores a variable local
	$num_reg = $_POST['num_reg'];
}

//si variable identificador viene llena desde la url
if (isset($_GET['rutaid'])) {
	//almacenaje de valores en variable local
	$rutaid= $_GET['rutaid'];
}
//si variable identificador viene llena desde la forma
elseif (isset($_POST['rutaid'])) {
	//almacenaje de valores a variable local
	$rutaid = $_POST['rutaid'];
}

//variable identificadora de  error
$InputError = 0;

    //para eliminar la ruta seleccionada
    if (isset($_POST['si'])){
            //echo "elimina: " . $_POST['id'];
            }
    elseif (isset($_POST['no'])){
            // redireccionamiento
    header("Location:" . $rootpath . "/ABCrutas.php");
    }
    if (isset($_GET['tmpborrar']) && !isset($_POST['si'])){
        echo "<form name='form1' method='post'>";
        echo "<table border='1' width='20%' cellpadding='10' cellspacing='10'align='center'>";
        echo "<tr>";
                echo "<td style='text-align:center'>";
                echo "<br>";
                $msg= "ELIMINAR LA RUTA: <p>'".$_GET['elimina']."'";
                echo "<br>";
        echo $msg;
                echo "</td>";
        echo "</tr>";
        echo "<tr>";
                echo "<td>";
                echo "<center><input type='submit' name='si' value='  si '>";
                echo "<input type='hidden' name='id' value='" . $_GET['rutaid'] . "'>";
        
        echo "      ";
                echo "<input type='submit' name='no' value=' no '>";
                echo "<input type='hidden' name='id' value='" . $_GET['rutaid'] . "'>";
                echo "</td>";
        echo "</tr>";
        echo "</table>";
        echo "</form>";
	include('includes/footer.inc');
        exit;
    }

//if (isset($_POST['enviar']) || isset($_GET['borrar']) || isset($_POST['modificar']) ) {
if (isset($_POST['enviar']) || isset($_POST['si']) || isset($_POST['modificar']) ) {

		if (isset($_POST['ruta']) && strlen($_POST['ruta'])<3){
		$InputError = 1;
		prnMsg(_('Debe llenar la ruta con al menos 3 caracteres'),'error');
		}
		else{
			$ruta=trim($_POST['ruta']);
		}
		if (isset($_POST['arear']) && $_POST['arear']==0){
			$InputError = 1;
			prnMsg(_('Debe Seleccionar un area'),'error');
		}
	unset($sql);
	if (isset($_POST['modificar'])and ($InputError != 1)) {
		$sql = "UPDATE rutas
                        SET rutaid='".$_POST['rutaid']."',
                            ruta='" .$ruta." ',
			    areacode='".$_POST['arear']."'
                        WHERE rutaid='".$rutaid."'";
		$ErrMsg = _('La actualización de la Ruta fracaso porque');
		prnMsg( _('La ruta').' ' .$_POST['ruta'] . ' ' . _(' se ha actualizado.'),'info');
		
	} elseif (isset($_POST['si'])and ($InputError != 1)) {
			$sql="DELETE FROM rutas
                                WHERE rutaid='" . $_GET['rutaid']."'";
			prnMsg(_('La ruta a sido eliminada ') . '!','info');
	}
	 elseif (isset($_POST['enviar'])and ($InputError != 1)) {
			$sql="SELECT COUNT(*)
                                FROM rutas
                                WHERE ruta='".$ruta."'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('No se da de alta la ruta por que ya hay un registro guardado'),'error');
		} else {
			$sql = "INSERT INTO rutas (rutaid, ruta, areacode)
                                VALUES ('".$ruta."','".$_POST['ruta']."','".$_POST['arear']."')";
			$ErrMsg = _('La inserccion de la ruta fracaso porque');
			prnMsg( _('La ruta').' ' .$_POST['ruta'] . ' ' . _('se ha creado.'),'info');
		}
	}
	unset($_POST['rutaid']);
	unset($_POST['ruta']);
	unset($_POST['arear']);
	unset($rutaid);	
}

if (isset($sql) && $InputError != 1 ) {
	$result = DB_query($sql,$db,$ErrMsg);
	if ($pagina=='Stock' and isset($_POST['enviar'])) {
		echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath ."/ABCrutas.php?" . SID . "'>";
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

if (!isset($rutaid) ) {
	//CREACION DE LA TABLA DE BUSQUEDA
	echo '<table><tr>
	<td style="text-align:left;">
		' . _('Ruta: ') . '<input type="text" name="rutab" value="'.$rutab.'" size=25 maxlength=55>
	</td>';
	echo '
		<td style="text-align:left;">'
			._('Área:').'
		</td>
		<td style="text-align:left;">';
			//Areas					  
			$SQL = "SELECT DISTINCT areas.areacode, areas.areadescription
				FROM areas JOIN tags t ON areas.areacode=t.areacode
				JOIN sec_unegsxuser u ON t.tagref=u.tagref
				WHERE u.userid = '" . $_SESSION['UserID'] . "'
				ORDER BY areas.areacode";
			
			$SQL = "SELECT areas.areacode, areadescription
				FROM areas
				ORDER BY areacode";		
			echo '<select name="area">';
			echo "<option selected value='0'>Todas las áreas...</option>";
			//echo $SQL;
			$result=DB_query($SQL,$db);
			while ($myrow=DB_fetch_array($result)){
				if (isset($_POST['area']) and $_POST['area']==$myrow["areacode"]){
					echo '<option selected value=' . $myrow['areacode'] . '>' . $myrow['areacode'].' - ' .$myrow['areadescription'];
				} else {
					echo '<option value=' . $myrow['areacode'] . '>' . $myrow['areacode'].' - ' .$myrow['areadescription'];
				}
			}
			echo '</select>
		</td>';
	echo '
	<td valign=bottom style="text-align:center;">
		<input type="submit" name="buscar" value=' . _('Buscar') . '>
	</td></tr></table>';
        
	echo "<div class='centre'><hr width=50%></div><br>";
	if ($Offset==0) {
		$numfuncion=1;
	} else {
		$numfuncion=$num_reg*$Offset+1;
	}
	$Offsetpagina=1;
	
	//esta parte sirve para mostrar la primera tabla con todos los registros existentes
	$sql="SELECT DISTINCT rutas.rutaid, rutas.ruta, areas.areadescription
		FROM rutas JOIN areas ON rutas.areacode=areas.areacode
		JOIN tags t ON areas.areacode=t.areacode
		JOIN sec_unegsxuser u ON t.tagref=u.tagref
		WHERE u.userid = '" . $_SESSION['UserID'] . "'
		AND rutas.ruta<>''";
	
	$sql = "SELECT *
		FROM rutas LEFT JOIN areas ON rutas.areacode=areas.areacode
		WHERE ruta<>''";
	
            if (strlen($rutab)>=1) {
            $sql=$sql.' and ruta like"%'.$rutab.'%"';
            }
	    If ($_POST['area']<>'' and $_POST['area']!=0){
			$sql=$sql." AND rutas.areacode = '".$_POST['area']."'";
	    }
            $result = DB_query($sql,$db);
            $ListCount=DB_num_rows($result);
            $ListPageMax=ceil($ListCount/$num_reg);
	$sql="SELECT DISTINCT rutas.rutaid, rutas.ruta, areas.areadescription
		FROM rutas JOIN areas ON rutas.areacode=areas.areacode
		WHERE rutas.ruta<>''";
        
        if (strlen($rutab)>=1) {
            $sql=$sql.' and ruta like "%'.$rutab.'%"';
            }
	    If ($_POST['area']<>'' and $_POST['area']!=0){
			$sql=$sql." AND rutas.areacode = '".$_POST['area']."'";
	    }
            $sql = $sql . " LIMIT ".$num_reg." OFFSET ". ($Offset * $num_reg) ;
            $result = DB_query($sql,$db);
            
	if (!isset($rutaid)) {
		echo "<div class='centre'>" ._('LISTADO DE RUTAS'). "</div>";
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
	echo "<tr><th>" . _('Id') . "</th>
	<th>" . _('Ruta') . "</th>
	<th>" . _('Area') . "</th>
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
			<td>%s</td>
				<td style='text-align:center;'><a href=\"%s&rutaid=%s\">Modificar</a></td>
				<td style='text-align:center;'><a href=\"%s&rutaid=%s&tmpborrar=1&elimina=%s\">Eliminar</a></td>
			</tr>",
			$myrow['rutaid'],
			$myrow['ruta'],
			$myrow['areadescription'],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow['rutaid'],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow['rutaid'],
			$myrow['ruta']
			);
		$numfuncion=$numfuncion+1;
	} 
	echo '</table>';
}
if (isset($rutaid)) {
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('rutas existentes') . '</a></div>';
}

//esta parte sirve para mostrar los datos del registro seleccionado para modificar
if (isset($rutaid)) {
	$sql = "SELECT *
		FROM rutas
		WHERE rutaid='". $rutaid."'" ;
	$result = DB_query($sql, $db);
	if ( DB_num_rows($result) == 0 ) {
		prnMsg( _('No hay registros.'),'warn');
	} else {
		$myrow = DB_fetch_array($result);
		$_POST['rutaid']=$myrow['rutaid'];
                $_POST['ruta']=trim($myrow['ruta']);
		$_POST['arear']=$myrow['areacode'];
	}
}

echo '<br>';
if(isset($_POST['rutaid'])) {
	echo "<input type=hidden name='rutaid' VALUE='" . $_POST['rutaid'] . "'>";
}
echo "<div class='centre'><hr width=60%>" ._('ALTA/MODIFICACION DE RUTAS'). "</div><br>";
	echo '<table>';
	echo'<tr><td>' . _('Ruta') . ":</td>
	<td><input type='text' name='ruta' size=40 maxlength=100 VALUE='" .$_POST['ruta'] . "'></td></tr>
	<tr>";
	echo '<tr>
		<td>'
			._('Área:').'
		</td>
		<td>';
			//Areas
			$SQL = "SELECT DISTINCT areas.areacode, areas.areadescription
				FROM areas JOIN tags t ON areas.areacode=t.areacode
				JOIN sec_unegsxuser u ON t.tagref=u.tagref
				WHERE u.userid = '" . $_SESSION['UserID'] . "'
				ORDER BY areas.areacode";
			
			$SQL = "SELECT areacode, areadescription
				FROM areas
				ORDER BY areacode";	
			echo '<select name="arear">';
			echo "<option selected value='0'>Todas las áreas...</option>";
			//echo $SQL;
			$result=DB_query($SQL,$db);
			while ($myrow=DB_fetch_array($result)){
				if (isset($_POST['arear']) and $_POST['arear']==$myrow["areacode"]){
					echo '<option selected value=' . $myrow['areacode'] . '>' . $myrow['areacode'].' - ' .$myrow['areadescription'];
				} else {
					echo '<option value=' . $myrow['areacode'] . '>' . $myrow['areacode'].' - ' .$myrow['areadescription'];
				}
			}
			echo '</select>
		</td>';
	echo '</tr>';
	echo '</table>';
        
	if (!isset($rutaid)) {
	//aqui verifica si esta vacia la variable si lo esta el valor de la accion sera enviar 
		echo "<div class='centre'><input type='Submit' name='enviar' value='" . _('Enviar') . "'></div>";
	}
	//aqui verifica que este llena la variable si lo esta el valor de la accion sera modificar
	elseif (isset($rutaid)) {
		echo "<div class='centre'><input type='Submit' name='modificar' value='" . _('Actualizar') . "'></div>";
	}
	echo '</form>';
	echo '<br>';
include('includes/footer.inc');
?>