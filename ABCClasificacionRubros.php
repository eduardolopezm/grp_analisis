<?php
//header('Content-Type: text/html; charset=UTF-8'); 
echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
include('includes/session.inc');
$title = _('Clasificación de Rubros');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$funcion = 1554;
include('includes/SecurityFunctions.inc');

$InputError = 0;

//variable delimitadora de registros por pantalla
$num_reg = 20;
//si variable contador de registros viene llena desde la forma
if (isset($_POST['num_reg'])) {
	//almacenaje de valores a variable local
	$num_reg = $_POST['num_reg'];
}

//si variable identificador viene llena desde la url
if (isset($_GET['id'])) {
	//almacenaje de valores en variable local
	$id = $_GET['id'];
}
//si variable identificador viene llena desde la forma
elseif (isset($_POST['id'])) {
	//almacenaje de valores a variable local
	$id = $_POST['id'];
}
//variable identificadora de  error
$InputError = 0;

if (empty($_GET['Delete']) == false) {
	if (empty($id) == false) {
		DB_query("DELETE FROM clasrubro WHERE id = '$id'", $db);
		prnMsg( _('Se ha eliminado la clasificacion.'), 'info');
		$id = NULL;
	}
}


//si variable enviar viene llena desde la forma o variable borrar viene llena desde la url o variable modificar viene llena desde la forma
if (isset($_POST['enviar']) || isset($_POST['modificar']) ) {
	if (isset($_POST['name']) && strlen($_POST['name']) < 3) {
		$InputError = 1;
		prnMsg(_('El nombre debe ser de al menos 3 caracteres de longitud'), 'error');
	}
	unset($sql);
//aqui empieza a realizar las acciones que corresponda de acuerdo al boton o link seleccionado
	if (isset($_POST['modificar'])and ($InputError != 1)) {
		$sql = "UPDATE clasrubro SET clave='".$_POST['clave']."', name='".$_POST['name']."', idtiporubro='".$_POST['tipo']."', active=".$_POST['active']." WHERE id=" . $id;
		$ErrMsg = _('La actualizacion fracaso porque');
		prnMsg( _('LA clasificacion').' ' .$_POST['nombre'] . ' ' . _(' se ha actualizado.'),'info');

	} elseif (isset($_POST['enviar'])and ($InputError != 1)) {
			$sql= "select count(*) from clasrubro where name='".$_POST['name']."'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('No se da de alta la clasificación por que ya hay un registro guardado'), 'error');
		} else {
			$sql = "INSERT INTO clasrubro (clave, name, idtiporubro,active)
				VALUES ('" . trim($_POST['clave'])."', '" . $_POST['name'] . "', '" . $_POST['tiporubro'] . "',".$_POST['active'].")";
			$ErrMsg = _('La insercion de la Clasificacion fracaso porque');
			prnMsg( _('La clasificcacion').' ' .$_POST['nombre'] . ' ' . _('se ha creado.'),'info');
		}
	}
//aqui se inicializan las variables en vacio
	unset($_POST['name']);
	unset($_POST['clave']);
	unset($_POST['tipo']);
        unset($_POST['active']);
	unset($id);
}

//si variable sql esta llena y mensaje de error es diferente de uno
if (isset($sql) && $InputError != 1) {
	//ejecucion de la consulta correspondiente
	$result = DB_query($sql, $db);
}
//si variable
if (isset($_POST['Go1'])) {
	$Offset = $_POST['Offset1'];
	$_POST['Go1'] = '';
}
//
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
//si variable del boton next viene llena desde la forma
if (isset($_POST['Next'])) {
	//almacenaje de valores en variable local
	$Offset = $_POST['nextlist'];
}
//si variable del boton prev viene llena desde la forma
if (isset($_POST['Prev'])) {
	//almacenaje de valores en variable local
	$Offset = $_POST['previous'];
}

//creacion de la forma
echo "<form method='post' name='forma' action=". $_SERVER['PHP_SELF'] . "?" . SID . ">";
//si variabale identificadora viene vacia
if (!isset($id) ) {
	//si variable contadora de pagina es igual a cero
	if ($Offset == 0) {
		//inicializacion de variable
		$numfuncion = 1;
	}
	//si no
	else {
		//iniziaclizacion de variables
		$numfuncion = $num_reg * $Offset + 1;
	}

	$Offsetpagina = 1;

	//esta parte sirve para mostrar la primera tabla con todos los registros existentes
	$sql = "SELECT * FROM clasrubro";
	$result = DB_query($sql, $db);
	$ListCount = DB_num_rows($result);
	$ListPageMax = ceil($ListCount / $num_reg);
	
	$sql= "SELECT clasrubro.id, clasrubro.clave, clasrubro.name ,clasrubro.active, clasrubrotipo.name as tipo
                FROM clasrubro
                LEFT JOIN clasrubrotipo ON clasrubro.idtiporubro=clasrubrotipo.id ";
	$sql = $sql . " LIMIT " . $num_reg . " OFFSET " . ($Offset * $num_reg);
	
	$result = DB_query($sql,$db);

	if (!isset($id)) {
		echo "<table border=0 align='center'; width:0; background-color:#ffff;' border=0 width=100% nowrap>";
			echo '<tr>
					<td align="center" colspan=2 class="texto_lista">
						<p align="center">
						<img src="images/imgs/configuracion.png" height=25" width="25" title="' . $title . '" alt="">' . ' ' . $title . '<br>
					</td>
			 	 </tr>';
		echo '</table>';
		echo '<table align="center" width="60%">';
		echo '	<tr>';
		if ($ListPageMax > 1) {
			if ($Offset == 0) {
				$Offsetpagina = 1;
			} else {
				$Offsetpagina = $Offset + 1;
		    }
			echo '<td>' . $Offsetpagina . ' ' . _('de') . ' ' . $ListPageMax . ' ' . _('Paginas') . '. ' . _('Ir a la Pagina') . ':';
			echo '<select name="Offset1">';
		    $ListPage = 0;
            while($ListPage < $ListPageMax) {
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
				<input type=submit name="Go1" VALUE="' . _('Ir') . '">
			</td>';
			if($Offset > 0) {
			echo '<td align=center cellpadding=3 >
				 <input type="hidden" name="previous" value='.number_format($Offset-1).'>
				 <input tabindex=' . number_format($j+7) . ' type="submit" name="Prev" value="' . _('Anterior') . '">
                 </td>';
			};
			if($Offset <> $ListPageMax - 1) {
			echo '<td style="text-align:right">
				 <input type="hidden" name="nextlist" value=' . number_format($Offset+1) . '>
				 <input tabindex=' . number_format($j+9) . ' type="submit" name="Next" value="' . _('Siguiente') . '">
                 </td>';
			}
		}
		echo'</tr>
		</table>';
	}
	//creacion de la tabla que muestra el listado de los registros existentes
	echo '<table width=60% cellspacing=0 border=1 bordercolor=lightgray cellpadding=0 colspan=0 style="margin-top:0">';
		echo "<tr>
				<td colspan='7' class='titulo_azul'>
					Listado de Rubros
				</td>
			  </tr>";
			echo "<tr>
					<td class='titulos_principales'>
						" . _('ID') . "
					</td>
		  				<td class='titulos_principales'>
								" . _('Tipo') . "
						</td>
						<td class='titulos_principales'>
										" . _('Nombre') . "
								</td>
							<td class='titulos_principales'>
										" . _('Rubro') . "
								</td>
                                                        <td class='titulos_principales'>
										" . _('Activo') . "
								</td>
          						<td class='titulos_principales'>
								</td>
          							<td class='titulos_principales'>
									</td>
          		  </tr>";

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
		printf(
            "<td class='numero_normal'>
            	%s
             </td>
             	<td class='numero_normal'>
            		%s
            	</td>
	            		<td class='texto_normal2'>
	            			%s
	            		</td>
                                <td class='numero_normal'>
	            			%s
	            		</td>
                                <td class='numero_normal'>
	            			%s
	            		</td>
	             			<td class='numero_normal'>
	            				<a href=\"%s&id=%s&Offset=%s\" action='desocultar()'>
	            					Modificar
	            				</a>
	            			</td>
	             				<td class='numero_normal'>
	            					<a onclick='borrar(this.href); return false;' href=\"%s&id=%s&Offset=%s&Delete=1\">
	            						Eliminar
	            					</a>
	            				</td>
	           		 </tr>",
             $myrow['id'],
             $myrow['clave'],
             $myrow['name'],
             $myrow['tipo'],
             $myrow['active'],
             $_SERVER['PHP_SELF'] . '?' . SID,
             $myrow['id'],
             $Offset,
             $_SERVER['PHP_SELF'] . '?' . SID,
             $myrow['id'],
             $Offset
		);
		$numfuncion = $numfuncion + 1;
	}
	echo '</table>';
}

if (isset($id)) {
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'><u>" . _('Clasificaciones Existentes') . '</u></a></div>';
}
 
//esta parte sirve para mostrar los datos del registro seleccionado para modificar
if (isset($id) ) {

	$sql = "SELECT  clasrubro.id, 
                        clasrubro.clave, 
                        clasrubro.name, 
                        idtiporubro,
                        clasrubro.active,
                        clasrubrotipo.id as idtipo,
                        clasrubrotipo.name as tipo
                FROM clasrubro
                	LEFT JOIN clasrubrotipo on clasrubro.idtiporubro=clasrubrotipo.id
                WHERE clasrubro.id = $id";

	$result = DB_query($sql, $db);
	if(DB_num_rows($result) == 0) { 
		prnMsg( _('No hay registros.'), 'warn');
	} else {
		$myrow = DB_fetch_array($result);
		$_POST['id'] = $myrow['id'];
		$_POST['clave'] = $myrow['clave'];
		$_POST['name'] = $myrow['name'];
		$_POST['tipo'] = $myrow['idtipo'];
                $_POST['active'] = $myrow['active'];
	}
}

echo '<br />';
if(isset($_POST['id'])) {
	echo "<input type=hidden name='id' VALUE='" . $_POST['id'] . "'>";
	echo "<input type=hidden name='Offset' VALUE='" . $_POST['Offset'] . "'>";
}

echo "<div class='texto_status' id='modificarDatos'> Alta/Modificacion " .$title. "</div><br>";
echo '<table  width="300" style="text-align:center; margin:0 auto;">';
	echo '<tr class="centre">';
			echo '<td class="texto_lista">
					' . _('Tipo:') . "
				  </td>
				  	<td>
						<input type='text' name='clave' id='clave' size=15 maxlength=100 VALUE='" . $_POST['clave'] . "'>
					</td>
			</tr>";
			echo "<tr><td class='texto_lista'>
					" . _('Nombre:') ."
				  	</td>		
					<td>
						<input type='text' name='name' id='name' size=30 maxlength=100 VALUE='" . $_POST['name'] . "'>
					</td>
					</tr>";
			echo "<tr><td class='texto_lista'>
					" . _('Rubro') .":
				  	</td>
					<td>";
                                            echo '<select name="tipo" id="tipo" style="width:250px">';
                                                $SQLgr="SELECT id,name FROM clasrubrotipo ORDER BY name;";
                                                $resultgr=  DB_query($SQLgr, $db);
                                                echo '<option value="*">-Seleccione....</option>';
                                                while ($myrowgr=  DB_fetch_array($resultgr)) {
                                                    if ($myrowgr['id']==$_POST['tipo']) {
                                                        echo '<option selected value="'.$myrowgr['id'].'">'.$myrowgr['name'].'</option>';
                                                    }else{
                                                        echo '<option value="'.$myrowgr['id'].'">'.$myrowgr['name'].'</option>';
                                                    }
                                                }
                                            echo '</select>';
					echo "</td>
					</tr>";
                                echo "<tr><td class='texto_lista'>
					" . _('Activo:') ."
				  	</td>
					<td>";
                                            echo '<select name="active" id="active" style="width:250px">';
                                                echo '<option value="*">-Seleccione....</option>';
                                                    if ($_POST['active']==0) {
                                                        echo '<option selected value=0>Inactivo</option>';
                                                    }else{
                                                        echo '<option value=0>Inactivo</option>';
                                                    }
                                                    
                                                    if ($_POST['active']==1){
                                                        echo '<option selected value=1>Activo</option>';
                                                    }else{
                                                        echo '<option value=1>Activo</option>';
                                                    }
                                            echo '</select>';
					echo "</td>
					</tr>";
                                        
				    echo "<tr class='centre'><td colspan='2' style='text-align:center'>";
				    if (!isset($id)) {
				    	echo "<input type='Submit' name='enviar' value='" . _('Guardar') . "'>";
				    }
				    //aqui verifica que este llena la variable si lo esta el valor de la accion sera modificar
				    elseif (isset($id)) {
				    	echo "<input type='Submit' name='modificar' value='" . _('Actualizar') . "'>";
				    }
				    echo "</td>
		  			</tr>";
echo '</table>';
    
//aqui verifica si esta vacia la variable si lo esta el valor de la accion sera enviar

	
echo '</form>';


include('includes/footer.inc');



echo '<script type="text/javascript">';

echo '
	function borrar(href) {
		if(confirm("Desea borrar el registro?")) {
			window.location = href;
		}
		return false;
	}';


echo '</script>';
?>