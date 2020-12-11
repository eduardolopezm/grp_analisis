<?php
include ('includes/session.inc');
$title = _ ( 'Altas, Bajas y Modificaciones de Subfunciones' );
include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');
$funcion = '';
//include ('includes/SecurityFunctions.inc');

// Variable de error
$InputError = 0;

// Variable numero de registros que se muestran
$num_reg = 20;
if (isset ( $_POST ['num_reg'] )) {
	$num_reg = $_POST ['num_reg'];
} else if (isset ( $_GET ['num_reg'] )) {
	$num_reg = $_GET ['num_reg'];
}

// Id de la tabla
$id = null;
if (isset ( $_GET ['id'] )) {
	$id = $_GET ['id'];
} else if (isset ( $_POST ['id'] )) {
	$id = $_POST ['id'];
}

// Borrar registro
if (empty ( $_GET ['Delete'] ) == false) {
	if (empty ( $id ) == false) {
		DB_query ( "DELETE FROM g_cat_sub_funcion WHERE id_subfuncion = '$id'", $db );
		prnMsg ( _ ( 'Se ha eliminado el registro.' ), 'info' );
		$id = null;
	}
}

// Insertar o actualizar registro
if (isset ( $_POST ['enviar'] ) || isset ( $_POST ['modificar'] )) {
	
	if (isset ( $_POST ['nombre'] ) and strlen ( $_POST ['nombre'] ) < 3) {
		$InputError = 1;
		prnMsg ( _ ( 'La funcion debe ser de al menos 3 caracteres de longitud' ), 'error' );
	}
	
	unset ( $sql );
	
	if (isset ( $_POST ['modificar'] ) and ($InputError != 1)) {
		
		$sql = "UPDATE g_cat_sub_funcion SET desc_subfun='{$_POST['nombre']}', id_finalidad='{$_POST['id_finalidad']}', id_funcion='{$_POST['id_funcion']}' WHERE id_subfuncion = $id";
		//echo $sql;exit;
                $ErrMsg = _ ( 'La actualizacion fracaso porque' );
		prnMsg ( _ ( 'La subfuncion' ) . ' ' . $_POST ['nombre'] . ' ' . _ ( ' se ha actualizado.' ), 'info' );
	} else if (isset ( $_POST ['enviar'] ) and ($InputError != 1)) {
		
                $sql = "SELECT COUNT(*) FROM g_cat_sub_funcion WHERE desc_subfun='{$_POST['nombre']}' AND id_finalidad='{$_POST['id_finalidad']}' AND id_funcion='{$_POST['id_funcion']}'";
		$result = DB_query ( $sql, $db );
		$myrow = DB_fetch_row ( $result );
		if ($myrow [0] > 0) {
			prnMsg ( _ ( 'No se da de alta la funcion por que ya hay un registro guardado' ), 'error' );
		} else {
			$sql = "INSERT INTO g_cat_funcion (id_finalidad,id_funcion, desc_fun, anhofiscal)
				VALUES ('" . $_POST ['id_finalidad'] . "','" . $_POST ['id_funcion'] . "','" . trim ( $_POST ['nombre'] ) . "',  DATE_FORMAT(NOW(),'%Y') )";
			$ErrMsg = _ ( 'La insercion de la Funcion fracaso porque' );
			prnMsg ( _ ( 'La subuncion ' ) . ' ' . $_POST ['nombre'] . ' ' . _ ( 'se ha creado.' ), 'info' );
		}
	}
	
	unset ( $_POST ['nombre'] );
	unset ( $_POST ['id_finalidad'] );
        unset ( $_POST ['id_funcion']);
	unset ( $_POST ['id'] );
	unset ( $id );
}

if (isset ( $sql ) && $InputError != 1) {
	$result = DB_query ( $sql, $db );
}

if (! isset ( $_POST ['Offset'] )) {
	if (isset ( $_GET ['Offset'] )) {
		$_POST ['Offset'] = $_GET ['Offset'];
	} else {
		$_POST ['Offset'] = 0;
	}
} else {
	if ($_POST ['Offset'] == 0) {
		$_POST ['Offset'] = 0;
	}
}

if (isset ( $_POST ['Offset'] )) {
	$Offset = $_POST ['Offset'];
}

if (isset ( $_POST ['Go1'] )) {
	$Offset = $_POST ['Offset1'];
	$_POST ['Go1'] = '';
}

if (isset ( $_POST ['Next'] )) {
	$Offset = $_POST ['nextlist'];
}

if (isset ( $_POST ['Prev'] )) {
	$Offset = $_POST ['previous'];
}

echo "<form method='post' name='forma' action=" . $_SERVER ['PHP_SELF'] . "?" . SID . ">";
if (! isset ( $id ) and $_POST ['id'] == '') {
	
	$Offsetpagina = 1;
	
	$sql = "SELECT COUNT(*) FROM g_cat_sub_funcion";
	$result = DB_query ( $sql, $db );
	$ListCount = 0;
	if ($row = DB_fetch_row ( $result )) {
		$ListCount = $row [0];
	}
	
	$ListPageMax = ceil ( $ListCount / $num_reg );
	
	$sql = "SELECT * FROM g_cat_sub_funcion 
                LEFT JOIN g_cat_funcion 
                ON g_cat_funcion.id_funcion=g_cat_sub_funcion.id_funcion 
                LEFT JOIN g_cat_finalidad 
                ON g_cat_funcion.id_finalidad=g_cat_finalidad.id_finalidad 
                ORDER BY desc_subfun ASC LIMIT " . $num_reg . " OFFSET " . ($Offset * $num_reg);

	$result = DB_query ( $sql, $db );
	//echo "".$sql;
	if (! isset ( $id )) {
		
		echo "<table border=0 align='center'; width:800px; background-color:#ffff;' border='0' nowrap>";
		echo '<tr>';
		echo '<td align="center" colspan=2 class="texto_lista">';
		echo '<p align="center">';
		echo '<img src="images/imgs/configuracion.png" height=25" width="25" title="' . _ ( 'Altas, Bajas y Modificaciones de Funciones' ) . '" alt="">' . ' ' . $title . '<br />';
		echo '</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table align="center" style="width:500px">';
		echo '<tr>';
		
//Paginar
		if ($ListPageMax >= 0) {
			if ($Offset == 0) {
				$Offsetpagina = 1;
			} else {
				$Offsetpagina = $Offset + 1;
			}
			echo '<td>' . $Offsetpagina . ' ' . _ ( 'de' ) . ' ' . $ListPageMax . ' ' . _ ( 'Paginas' ) . '. ' . _ ( 'Ir a la Pagina' ) . ':';
			echo '<select name="Offset1">';
			$ListPage = 0;
			while ( $ListPage < $ListPageMax ) {
				if ($ListPage == $Offset) {
					echo '<option VALUE=' . $ListPage . ' selected>' . ($ListPage + 1) . '</option>';
				} else {
					echo '<option VALUE=' . $ListPage . '>' . ($ListPage + 1) . '</option>';
				}
				$ListPage ++;
				$Offsetpagina = $Offsetpagina + 1;
			}
			echo '</select></td>
				<td><input type="text" name="num_reg" size=1 value="' . $num_reg . '"></td>
				<td>
				<input type=submit name="Go1" VALUE="' . _ ( 'Buscar' ) . '">
				</td>';
			
			if ($Offset > 0) {
				echo '<td align=center cellpadding=3 >
					<input type="hidden" name="previous" value=' . number_format ( $Offset - 1 ) . '>
					<input tabindex=' . number_format ( $j + 7 ) . ' type="submit" name="Prev" value="' . _ ( 'Anterior' ) . '">
	                </td>';
			}
			;
			if ($Offset != $ListPageMax - 1) {
				echo '<td style="text-align:right">
					<input type="hidden" name="nextlist" value=' . number_format ( $Offset + 1 ) . '>
					<input tabindex=' . number_format ( $j + 9 ) . ' type="submit" name="Next" value="' . _ ( 'Siguiente' ) . '">
	                </td>';
			}
		}
		
		echo "</tr>";
		echo "</table>";
	}//Fin de paginar
	
	echo '<table width="80%" cellspacing="0" border="1" bordercolor="lightgray" cellpadding="0" colspan="0" style="margin-top:0">';
	
	echo "<tr>";
	echo "<td colspan='6' class='titulo_azul'>";
	echo _ ( "Listado de Subfunciones" );
	echo "</td>";
	echo "</tr>";
	
	echo "<tr>";
	
	// Columna id
	echo "<td class='titulos_principales'>";
	echo _ ( 'ID' );
	echo "</td>";
	
	// Columna subfuncion
	echo "<td class='titulos_principales'>";
	echo _ ( 'Subfuncion' );
	echo "</td>";
        
        // Columna funcion
	echo "<td class='titulos_principales'>";
	echo _ ( 'Funcion' );
	echo "</td>";
	
	// Columna finalidad
	echo "<td class='titulos_principales'>";
	echo _ ( 'Finalidad' );
	echo "</td>";
        
        // Columna anho
	echo "<td class='titulos_principales'>";
	echo _ ( 'Año' );
	echo "</td>";
	
	// Columna modificar
	echo "<td class='titulos_principales'>";
	echo "</td>";
	
	// Columna borrar
	echo "<td class='titulos_principales'>";
	echo "</td>";
	
	echo "</tr>";
	
	$k = 0; // row colour counter
	
	while ( $myrow = DB_fetch_array ( $result ) ) {
		
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
		
		
		printf ( "<td class='numero_normal'>%s</td>
        	<td >%s</td>
            <td >%s</td>
            <td >%s</td>
            <td class='texto_normal'>%s</td>
            <td class='numero_normal'><a href=\"%s&id=%s&Offset=%s&num_reg=%s\">" . _ ( "Modificar" ) . "</a></td>
            <td class='numero_normal'><a onclick='borrar(this.href); return false;' href=\"%s&id=%s&Offset=%s&num_reg=%s&Delete=1\">" . _ ( "Eliminar" ) . "</a></td>
            </tr>", $myrow ['id_subfuncion'], $myrow ['desc_subfun'], $myrow ['desc_fun'], $myrow ['desc_fin'], $myrow ['anhofiscal'],$_SERVER ['PHP_SELF'] . '?' . SID, $myrow ['id_subfuncion'], $Offset, $num_reg, $_SERVER ['PHP_SELF'] . '?' . SID, $myrow ['id_subfuncion'], $Offset, $num_reg );
	}
	
	echo "</table>";
}

if (isset ( $id )) {
	echo "<div class='centre'><a href='" . $_SERVER ['PHP_SELF'] . "?" . SID . "'>" . _ ( 'Funciones Existentes' ) . '</a></div>';
}

// Esta parte sirve para mostrar los datos del registro seleccionado para modificar
if (isset ( $id ) and strlen ( $id ) > 0) {
	
	$sql = "SELECT *
		FROM g_cat_sub_funcion WHERE id_funcion = $id";
	
	$result = DB_query ( $sql, $db );
	if (DB_num_rows ( $result ) == 0) {
		prnMsg ( _ ( 'No hay registros.' ), 'warn' );
	} else {
		$myrow = DB_fetch_array ( $result );
		$_POST ['id'] = $myrow ['id_subfuncion'];
		$_POST ['nombre'] = $myrow ['desc_subfun'];
		$_POST ['id_funcion'] = $myrow ['id_funcion'];
		$_POST ['id_finalidad'] = $myrow ['id_finalidad'];
	}
}

echo '<br />';
if (isset ( $_POST ['id'] )) {
	echo "<input type='hidden' name='id' value='" . $_POST ['id'] . "'>";
	echo "<input type='hidden' name='Offset' value='" . $_POST ['Offset'] . "'>";
	echo "<input type='hidden' name='num_reg' value='" . $num_reg . "'>";
}

echo "<div class='texto_status'>" . _ ( 'Alta/Modificacion de Funciones' ) . "</div><br />";
echo "<table style='text-align:center; margin:0 auto;'>";

// Campo clave 
/*
echo "<tr>";
echo "<td class='texto_lista'>" . _ ( 'Clave:' ) . "</td>";
echo "<td><input type='text' name='nombre' size='40' maxlength='100' value='" . $_POST ['id_funcion'] . "'></td>";
echo "</tr>";
*/

// Campo nombre 
echo "<tr>";
echo "<td class='texto_lista'>" . _ ( 'Nombre:' ) . "</td>";
echo "<td><input type='text' name='nombre' size='40' maxlength='100' value='" . $_POST ['nombre'] . "'></td>";
echo "</tr>";

// Campo id_funcion
echo "<tr>";
echo "<td class='texto_lista'>" . _ ( 'Funcion:' ) . "</td>";
echo "<td><select name='id_funcion'>";
    $sql_fin="SELECT * FROM g_cat_funcion ORDER BY desc_fun";
    $res=  DB_query($sql_fin, $db);
    while ($myrows = DB_fetch_array($res)) {
        if($myrows['id_funcion']==$_POST['id_funcion']){
            echo "<option selected value='".$myrows['id_funcion']."'>".$myrows['desc_fun']."</option>";
        }
        else {
            echo "<option value='".$myrows['id_funcion']."'>".$myrows['desc_fun']."</option>";
        }
    }
echo "</select>";
echo "</td>";
echo "</tr>";

// Campo id_finalidad
echo "<tr>";
echo "<td class='texto_lista'>" . _ ( 'Finalidad:' ) . "</td>";
echo "<td><select name='id_finalidad'>";
    $sql_fin="SELECT * FROM g_cat_finalidad ORDER BY desc_fin";
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

echo "<tr>";
echo "<td colspan='2'>";
if (! isset ( $id )) {
	
	echo "<div class='centre'><input type='Submit' name='enviar' value='" . _ ( 'Enviar' ) . "'></div>";
} else if (isset ( $id )) {
	
	echo "<div class='centre'><input type='Submit' name='modificar' value='" . _ ( 'Actualizar' ) . "'></div>";
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
include ('includes/footer.inc');
?>