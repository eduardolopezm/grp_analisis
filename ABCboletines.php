<?php

$funcion=650;// POR CAMBIAR FUNCION
$PageSecurity = 3;
include('includes/session.inc');
$title = _('Altas, Bajas y Consulta Boletines');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

//si variable encargada de busqueda viene llena desde la forma
if (isset($_POST['edofuncion'])) {
	//almacenaje de valores a varible local
	$edofuncion=$_POST['edofuncion'];
}
if (isset($_POST['funcionb'])) {
	//almacenaje de valores a varible local
	$funcionb=$_POST['funcionb'];
}
if (isset($_POST['funcion'])) {
	//almacenaje de valores a varible local
	$funcion=$_POST['funcion'];
}

//variable delimitadora de registros por pantalla
$num_reg=10;
//si variable contador de registros viene llena desde la forma
if (isset($_POST['num_reg'])) {
	//almacenaje de valores a variable local
	$num_reg = $_POST['num_reg'];
}

//si variable identificador viene llena desde la url
if (isset($_GET['id_boletin'])) {
	//almacenaje de valores en variable local
	$id_boletin= $_GET['id_boletin'];
}
//si variable identificador viene llena desde la forma
elseif (isset($_POST['id_boletin'])) {
	//almacenaje de valores a variable local
	$id_boletin = $_POST['id_boletin'];
}

//variable identificadora de  error
$InputError = 0;

    /****** PARA ELIMINAR EL BOLETIN SELECCIONADO ******/
    if (isset($_POST['si'])){
            
            }
    elseif (isset($_POST['no'])){
            // redireccionamiento
    header("Location:" . $rootpath . "/ABCboletines.php");
    }
    if (isset($_GET['tmpborrar']) && !isset($_POST['si'])){
        echo "<form name='form1' method='post'>";
        echo "<table border='1' width='20%' cellpadding='10' cellspacing='10'align='center'>";
        echo "<tr>";
                echo "<td style='text-align:center'>";
                echo "<br>";
                $msg= "ELIMINAR BOLETIN: <p>'".$_GET['id_boletin']."'<br>FUNCION:<p>'".$_GET['elimina']."'";
                echo "<br>";
        echo $msg;
                echo "</td>";
        echo "</tr>";
        echo "<tr>";
                echo "<td>";
                echo "<center><input type='submit' name='si' value='  si '>";
                echo "<input type='hidden' name='id_boletin' value='" . $_GET['id_boletin'] . "'>";
        
        echo "      ";
                echo "<input type='submit' name='no' value=' no '>";
                echo "<input type='hidden' name='id_boletin' value='" . $_GET['id_boletin'] . "'>";
                echo "</td>";
        echo "</tr>";
        echo "</table>";
        echo "</form>";
	include('includes/footer_Index.inc');
        exit;
    }

//if (isset($_POST['enviar']) || isset($_GET['borrar']) || isset($_POST['modificar']) ) {
if (isset($_POST['enviar']) || isset($_POST['si']) || isset($_POST['modificar']) ) {

		if (isset($_POST['des']) && strlen($_POST['des'])==''){
		$InputError = 1;
		prnMsg(_('La descripcion no puede ir vacia'),'error');
		}
		else{
			$des=trim($_POST['des']);
		}
		if (isset($_POST['estado']) &&  strlen($_POST['estado'])==''){
			$InputError = 1;
			prnMsg(_('Debe Seleccionar un estado'),'error');
			
		}
	unset($sql);
	if (isset($_POST['modificar'])and ($InputError != 1)) {
		$sql = "UPDATE boletines
                        SET funcion='".$funcion."',
                            descripcion='".$des."',
			    estado='".$_POST['estado']."',
			    fecha=now()
			    where id_boletin='".$id_boletin."'
                        ";
			
		$ErrMsg = _('La actualización fracaso porque');
		prnMsg(_('El boletin se ha actualizado.'),'info');
		
	} elseif (isset($_POST['si'])and ($InputError != 1)) {
			$sql="DELETE FROM boletines
                                WHERE id_boletin='" . $_GET['id_boletin']."'";
			prnMsg(_('El boletin a sido eliminado ') . '!','info');
	}
	 elseif (isset($_POST['enviar'])and ($InputError != 1)) {
			
			$sql = "INSERT INTO boletines (funcion,descripcion,estado,usuario,fecha)
                                VALUES ('".$funcion."','".$_POST['des']."','".$_POST['estado']."','" . $_SESSION['UserID'] . "',now())";
			$ErrMsg = _('La inserccion fracaso porque');
			prnMsg( _('La insercion se ha creado.'),'info');
		
		
	}
	
	unset($funcion);
	unset($_POST['funcion']);
	unset($_POST['des']);
	unset($des);
	unset($id_boletin);
	unset($_POST['estado']);
		
}

if (isset($sql) && $InputError != 1 ) {
	$result = DB_query($sql,$db,$ErrMsg);
	if ($pagina=='Stock' and isset($_POST['enviar'])) {
		echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath ."/ABCboletines.php?" . SID . "'>";
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

echo "<div class='centre'><b><font  color='Purple'>" ._('ABC DE BOLETINES'). "</b></font></div><br>";

	    
        /****** LISTADO DE FUNCIONES ******/    
	if (!isset($id_boletin)) {
		echo "<div class='centre'><font  color='Purple'>" ._('LISTADO DE FUNCIONES'). "</font></div><br>";
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
	/****** LISTADO DE TODAS LAS FUNCIONES ******/
	echo '<table border=1 width=70%>';
	echo "<tr>
	<th>" . _('# Boletin') . "</th>
	<th>" . _('Función') . "</th>
	<th>" . _('Descripción') . "</th>
	<th>" . _('Estado') . "</th>
	<th></th>
	<th></th>
        </tr>";
	$Sql = "SELECT *
		FROM boletines
		" ;
	$result1 = DB_query($Sql, $db);
	$k=0; //row colour counter
	while ($myrow = DB_fetch_array($result1)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		
		printf("
		        <td>%s</td>
		        <td>%s</td>
			<td>%s</td>
			<td>%s</td>
				<td style='text-align:center;'><a href=\"%s&id_boletin=%s\">Modificar</a></td>
				<td style='text-align:center;'><a href=\"%s&id_boletin=%s&tmpborrar=1&elimina=%s\">Eliminar</a></td>
			</tr>",
			$myrow['id_boletin'],
			$myrow['funcion'],
			$myrow['descripcion'],
			$myrow['estado'],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow['id_boletin'],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow['id_boletin'],
			$myrow['funcion']
			);
		$numfuncion=$numfuncion+1;
	} 
	echo '</table>';

if (isset($id_boletin)) {
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Funciones existentes') . '</a></div>';
}

/****** DATOS DEL REGISTRO SELECCIONADO A MODIFICAR ******/

if (isset($id_boletin)) {
	$sql = "SELECT *
		FROM boletines
		where id_boletin='".$id_boletin."'" ;
	$result = DB_query($sql, $db);
	if ( DB_num_rows($result) == 0 ) {
		prnMsg( _('No hay registros.'),'warn');
	} else {
		$myrow = DB_fetch_array($result);
		$_POST['id_boletin']=$myrow['id_boletin'];
		$_POST['funcion']=$myrow['funcion'];
               	$_POST['des']=$myrow['descripcion'];
		$_POST['estado']=$myrow['estado'];
	}
	if(isset($_POST['id_boletin'])) {
	echo "<input type=hidden name='id_boletin' VALUE='" . $_POST['id_boletin'] . "'>";
}
}

echo '<br>';

/******* ALTA DE BOLETINES *******/
echo "<div class='centre'><hr width=60%><font  color='Purple'>" ._('ALTA/MODIFICACION DE BOLETINES'). "</div></font><br>";
	echo '<table>
	<tr>';
	echo '<tr>
		<td>'
			._('Función:').'
		</td>
		<td>';
			//funciones existentes activas
			$SQL = "SELECT functionid,title,shortdescription,active
				FROM sec_functions
				WHERE active = 1";
					
			echo '<select name="funcion">';
			echo "<option selected value='0'>Seleccionar función...</option>";
			//echo $SQL;
			$result=DB_query($SQL,$db);
			while ($myrow=DB_fetch_array($result)){
				if (isset($_POST['funcion']) and $_POST['funcion']==$myrow["functionid"]){
					echo '<option selected value=' . $myrow['functionid'] . '>' . $myrow['functionid'].' - '.$myrow['shortdescription'];
				} else {
					echo '<option value=' . $myrow['functionid'] . '>' . $myrow['functionid'].' - ' .$myrow['shortdescription'];
				}
			}
			echo '</select>
		</td>';
	echo '</tr>';
	echo'<tr><td>' . _('Descripción') . ":</td>
	<td><textarea name='des' size=40 maxlength=100 cols='30' rows='5'>".$_POST['des']."</textarea></td></td>";
	echo '<tr>
		<td>'
			._('Estado:').'
		</td>
		<td>';
			//funciones existentes activas
			$SQL = "SELECT id_edo,estado
				FROM edofuncion";
					
			echo '<select name="estado">';
			echo "<option selected value='0'>Seleccionar...</option>";
			//echo $SQL;
			$result=DB_query($SQL,$db);
			while ($myrow=DB_fetch_array($result)){
				if (isset($_POST['estado']) and $_POST['estado']==$myrow["estado"]){
					echo '<option selected value=' . $myrow['estado'] . '>' . $myrow['id_edo'].' - '.$myrow['estado'];
				} else {
					echo '<option value=' . $myrow['estado'] . '>' . $myrow['id_edo'].' - ' .$myrow['estado'];
				}
			}
			echo '</select>
		</td>';
	echo '</tr>';
	echo '</table>';
        /****** FIN ALTA DE BOLETINES ******/
	if (!isset($id_boletin)) {
	//aqui verifica si esta vacia la variable si lo esta el valor de la accion sera enviar 
		echo "<br><br><br><div class='centre'><input type='Submit' name='enviar' value='" . _('Procesar') . "'></div>";
	}
	//aqui verifica que este llena la variable si lo esta el valor de la accion sera modificar
	elseif (isset($id_boletin)) {
		echo "<div class='centre'><input type='Submit' name='modificar' value='" . _('Actualizar') . "'></div>";
	}
	echo '</form>';
	echo '<br>';
include('includes/footer_Index.inc');
?>