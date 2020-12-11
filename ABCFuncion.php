<?php

include('includes/session.inc');
$title = _('Altas, Bajas y Modificaciones de Funciones');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$funcion=1460; 
include('includes/SecurityFunctions.inc');

if (isset($_POST['category'])) {
	$category= $_POST['category'];
} else {
	$category="";
}	

if (isset($_POST['UserTask'])) {
	$UserTask= $_POST['UserTask'];
} else {
	$UserTask="";
}

if (isset($_POST['Proyecto'])) {
	$Proyecto= $_POST['Proyecto'];
} else {
	$Proyecto="";
}


$num_reg=10;

if (isset($_POST['num_reg'])) {
	$num_reg = $_POST['num_reg'];
}

//esta es la variable con la que nos guiamos para saber ssi es la primera vez que entran
//y para poder mostrar los datos del seleccionado cuando van a modificar
if (isset($_GET['id'])) {
	$catcode = $_GET['id'];
} elseif (isset($_POST['id'])) {
	$catcode = $_POST['id'];
}

$InputError = 0;
if (isset($_POST['enviar']) || isset($_GET['borrar']) || isset($_POST['modificar']) ) {
		
		if (isset($_POST['desline']) && strlen($_POST['desline'])<3){
			$InputError = 1;
			prnMsg(_('El nombre del category debe ser de al menos 3 caracteres de longitud'),'error');
		}
		if (isset($_POST['catcode']) && strlen($_POST['catcode'])<1){
			$InputError = 2;
			prnMsg(_('El valor del Codigo debe ser de al menos 1 caracter de longitud'),'error');
		}
		if (isset($_POST['precio']) && !is_numeric($_POST['precio'])){
			$InputError = 3;
			prnMsg(_('El valor del precio unitario debe ser numerico'),'error');
		}
	unset($sql);
	
	if (isset($_POST['modificar'])and ($InputError != 1) and ($InputError != 2)) {
		
		$sql = "UPDATE g_cat_funcion
		SET id_funcion='" .$_POST['id']." ',id_finalidad='" .$_POST['id_finalidad']." ',desc_fun='" .$_POST['desc_funcion']." '
		WHERE id_fun='$catcode'";
		
		$ErrMsg = _('La actualizaci�n del category fracaso porque');
		prnMsg( _('El Concepto').' ' .$_POST['concepto'] . ' ' . _(' se ha actualizado.'),'info');
		
	} elseif (isset($_GET['borrar'])and ($InputError != 1) and ($InputError != 2)) {
			$sql= "select count(*) from g_cat_funcion where id_fun='".$_GET['id']."'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0]>0) {
				prnMsg( _('No se da de baja la funcion por que ya hay ') .$myrow[0]._(' registros guardado').'<br>'.$sql,'error');
			}else{
				$sql="DELETE FROM g_cat_funcion WHERE id_fun='" . $_GET['id']."'";
				prnMsg(_('El category a sido eliminada ') . '!','info');
			}
	} elseif (isset($_POST['enviar'])and ($InputError != 1) and ($InputError != 2)) {
			$sql= "select count(*) from g_cat_funcion where id_fun='".$_POST['id']."'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('No se da de alta la funcion por que ya existe registro guardado con misma clave'),'error');
		} else {
			$sql = "INSERT INTO g_cat_funcion (id_funcion,id_finalidad,desc_fun)
			VALUES ('".$_POST['id']."','".$_POST['id_finalidad']."','".$_POST['desc_funcion']."')";
			$ErrMsg = _('La inserccion del category fracaso porque');
			prnMsg( _('La funcion').' ' .$_POST['desc_funcion'] . ' ' . _('se ha creado.'),'info');
		}
	}
	unset($_POST['desline']);
	unset($_POST['id']);
	unset($catcode);	
}

if (isset($sql) && $InputError != 1 && ($InputError != 2)) {
    
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

echo "<table border=0 style='text-align:center;margin-left:auto;margin-right:auto;' border='0' nowrap> ";
		echo '<tr>';
		echo '<td align="center" colspan=2 class="texto_lista">';
		echo '<p align="center">';
		echo '<img src="images/imgs/configuracion.png" height=25" width="25" title="' . _ ( 'Altas, Bajas y Modificaciones de Funciones' ) . '" alt="">' . ' ' . $title . '<br />';
		echo '</td>';
		echo '</tr>';
echo '</table>';

if (!isset($catcode) ) {
            echo '<table width="80%">';
                echo '<tr><td>';
                echo '<fieldset width=auto>';
                echo '<legend>Opciones de buqueda:</legend>';
                    echo '<table width="80%" border=0 style="text-align:center;margin-left:auto;margin-right:auto;border-color:lightgray;">';

                    echo '<tr>';
                        echo '<td align="rigth" width=""  style="text-align:left;">'.("Clave").'</td>';
                        echo '<td align="left" width=""  style="text-align:left;"><input type="text" name="searchid" maxlength=45 size=45 value="'.$_POST['searchid'].'"></td>';
                    echo '</tr>';
                    echo '<tr>';
                        echo '<td align="rigth" width=""  style="text-align:left;">'.("Nombre").'</td>';
                        echo '<td align="left" width=""  style="text-align:left;"><input type="text" name="searchdesc" maxlength=45 size=45 value="'.$_POST['searchdesc'].'"></td>';
                    echo '</tr>';

                    echo '<tr align=center>';
                        echo '<td colspan=2 align="left" width=""  style="text-align:left;"><input type="submit" name="btnsearch" value="'._('Busqueda').'">';
                        echo '<input type="submit" name="btnnew" value="'._('Nuevo').'"></td>';
                    echo '</tr>';
                    echo '</table>';
                echo '</fieldset>';
            echo '<table>';
	if ($Offset==0) {
		$numfuncion=1;
	} else {
		$numfuncion=$num_reg*$Offset+1;
	}
	$Offsetpagina=1;
	
	
//esta parte sirve para mostrar la primera tabla con todos los registros existentes

	
	$sql = "SELECT *
		FROM g_cat_funcion
		 " ;	
	if (strlen($category)>=1) {
		$sql=$sql." WHERE (desc_fun like '%".$category."%')";
	}
	
	
	$sql=$sql." ORDER BY desc_fun";
	$result = DB_query($sql,$db);
	
	$sql = "SELECT g_cat_funcion.id_finalidad,desc_fin,id_funcion, desc_fun
		FROM g_cat_funcion  JOIN g_cat_finalidad on g_cat_funcion.id_finalidad=g_cat_finalidad.id_finalidad " ;	
	if (strlen($category)>=1) {
		$sql=$sql." AND (desc_fun like '%".$category."%')";
	}
	$sql=$sql." ORDER BY desc_fun asc";
	
	if ( DB_num_rows($result) == 0 ) {	
		prnMsg( _('No hay registros con esa Busqueda.'),'warn');
	}
		
	$sql = $sql . " LIMIT ".$num_reg." OFFSET ". ($Offset * $num_reg) ;
	$result = DB_query($sql,$db);
	
	$ListCount=DB_num_rows($result);
	$ListPageMax=ceil($ListCount/$num_reg);
	
	/// fin consulta join		
	//echo "<form  method='post' action=". $_SERVER['PHP_SELF'] . "?". SID . ">";
	if (!isset($catcode)) {
		echo "<div class='center'>" ._('LISTADO DE FUNCIONES'). "</div>";
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
	echo "<tr>
        <th>" . _('#ID') . "</th>
        <th>" . _('Clave') . "</th>
	<th>" . _('Finalidad') . "</th>
        <th>" . _('Descripcion') . "</th>
	<th></th>
	<th></th>
        </tr>";
	
	$k=0; //row colour counter
        $l=1; // "ele "Contador de registros
	while ($myrow = DB_fetch_array($result)) {
		/*if ($k==1){
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
			<td>%s</td>
			<td>%s</td>
                        <td>%s</td>
			<td style='text-align:center;'><a href=\"%s&catcode=%s\">Modificar</a></td>
			<td style='text-align:center;'><a href=\"%s&catcode=%s&borrar=1\">Eliminar</a></td>
			</tr>",*/
            echo '<tr><td style="background-color:gray">'.$l.'</td>'
                    . '<td>'.$myrow['id_funcion'].'</td>'
                    .'<td>'.$myrow['desc_fin'].'</td>'
                    .'<td>'.$myrow['desc_fun'].'</td>'
                    .'<td><a href="'.$_SERVER['PHP_SELF'].'?id='.$myrow['id_fun'].'&modificar=1">Modificar</a></td>'
                     .'<td><a href="'.$_SERVER['PHP_SELF'].'?id='.$myrow['id_fun'].'&borrar=1">Eliminar</a></td>'
                    . '</tr>';
			/*number_format($myrow['preciounitario'],2),
			$myrow['fproductividad'],
			$myrow['visualizacion'],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow['catcode'],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow['catcode']*/
			
		$numfuncion=$numfuncion+1;
                $l++;
	} 
	echo '</table>';
	echo '</form>';
}


if (isset($catcode)) {
            $sql = "SELECT id_fun, g_cat_funcion.id_finalidad,desc_fin,id_funcion, desc_fun
		FROM g_cat_funcion  JOIN g_cat_finalidad on g_cat_funcion.id_finalidad=g_cat_finalidad.id_finalidad
		AND id_fun='". $catcode."'" ;
		$result = DB_query($sql, $db);
                $myrow = DB_fetch_array($result);
		
		$_POST['id']=$myrow['id_funcion'];
		$_POST['id_finalidad'] = $myrow['id_finalidad'];
		$_POST['desc_funcion']=$myrow['desc_fun'];
		$_POST['partida_gen']=$myrow['partida_gen'];
		$_POST['partida_esp']=$myrow['partida_esp'];
		$_POST['nombre']=$myrow['nombre'];
		
}
if (isset($catcode)) {
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Funciones existentes') . '</a></div>';
}
if (isset($catcode) or isset($_POST['btnnew']))
{
echo "<form  method='post' action=". $_SERVER['PHP_SELF'] . "?". SID . ">";

echo '<br>';
/*if(isset($_POST['catcode'])) {
	echo "<input type=hidden name='catcode' VALUE='" . $_POST['catcode'] . "'>";
}*/
echo "<div class='centre'><hr width=60%>" ._('ALTA/MODIFICACION DE FUNCIONES'). "</div><br>";
	echo '<table>';
            echo'<tr class="texto_lista"><td>' . _('Clave') . ":</td>
                    <td><input type='text' name='id' size=40 maxlength=100 VALUE='" .$_POST['id'] . "'></td></tr>
                <tr>";
            echo '<tr class="texto_lista"><td>' . _('Nombre') . ":</td>
                  <td><input type='text' name='desc_funcion' size=40 maxlength=100 VALUE='" .$_POST['desc_funcion'] . "'></td></tr>";
            echo '</tr>';
	
            echo "<tr>";
                    echo "<td class='texto_lista'>" . _ ( 'Finalidad:' ) . "</td>";
                    echo "<td><select name='id_finalidad'>";
                        $sql_fin="SELECT * FROM g_cat_finalidad";
                        $res=  DB_query($sql_fin, $db);
                        while ($myrows = DB_fetch_array($res)) {
                            if($myrows['id_finalidad']==$_POST['id_finalidad']){
                                echo "<option selected value='".$myrows['id_finalidad']."'>".$myrows['desc_fin']."</option>";
                            }
                            else {
                                echo "<option value='".$myrows['id_finalidad']."'>".$myrows['desc_fin']."</option>";
                            }
                        }
                    echo "</select>";
                    echo "</td>";
            echo "</tr>";
	echo'</table>';
	//aqui verifica si esta vacia la variable si lo esta el valor de la accion sera enviar
	
	if (!isset($catcode)) {
		echo "<div class='centre'><input type='Submit' name='enviar' value='" . _('Enviar') . "'></div>";
	}
	//aqui verifica que este llena la variable si lo esta el valor de la accion sera modificar
	elseif (isset($catcode)) {
		
		echo "<div class='centre'><input type='Submit' name='modificar' value='" . _('Actualizar') . "'></div>";
		
	}
echo '</form>';
}
include('includes/footer.inc');
?>