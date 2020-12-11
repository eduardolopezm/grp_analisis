<?php

/* Eduardo Ramirez Chavez; Correcciones Generales Update Delete Insert filtros de busqueda Formato
 * 27/enero/2015
*/

include('includes/session.inc');
$title = _('Altas, Bajas y Modificaciones de Programa');
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


$num_reg=15;

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
if(!isset($_POST['catmod']))
{
    $_POST['catmod']=$catcode;
}

$InputError = 0;
if (isset($_POST['enviar']) || isset($_GET['borrar']) || isset($_POST['modificar']) ) {
    
                 //Validaciones 
                $sql= "select count(*) from g_cat_programa where id_programa='".$_POST['id']."'";
                $result = DB_query($sql,$db);
                $myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('No se da de alta el programa por que ya existe registro guardado con la misma clave'),'error');
                        $InputError=1;
		} 
                $sql= "select count(*) from g_cat_programa where desc_programa='".$_POST['desc_programa']."'";
                $result = DB_query($sql,$db);
                $myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('No se da de alta el programa por que ya existe registro guardado con la misma descripcion'),'error');
                        $InputError=1;
		}
                if (empty($_POST['id'])) {
                    prnMsg( _('Indique una clave/id antes de continuar'),'error');
                        $InputError=1;
                }
                if (empty($_POST['desc_programa'])) {
                    prnMsg( _('Indique una descripcion antes de continuar'),'error');
                        $InputError=1;
                }
		
		if (isset($_POST['desline']) && strlen($_POST['desline'])<3){
			$InputError = 1;
			prnMsg(_('El nombre del category debe ser de al menos 3 caracteres de longitud'),'error');
		}
		if (isset($_POST['catcode']) && strlen($_POST['catcode'])<1){
			$InputError = 2;
			prnMsg(_('El valor del Codigo debe ser de al menos 1 caracter de longitud'),'error');
		}
	unset($sql);
	$_POST['btnnew']="true";
	if (isset($_POST['modificar'])and ($InputError != 1) and ($InputError != 2)) {
		if ($InputError==0){
                    $sql = "UPDATE g_cat_programa
                    SET desc_programa='" .$_POST['desc_programa']."',
                        id_programa='" .$_POST['id']."'
                    WHERE id_programa='".$_POST['catmod']."'";
                    $ErrMsg = _('La actualizaci&oacute;n del programa fracaso porque');
                    prnMsg( _('El Concepto').' ' .$_POST['concepto'] . ' ' . _(' se ha actualizado.'),'info');
                    unset($_POST['btnnew']);
                }
		
	} elseif (isset($_GET['borrar'])and ($InputError != 1) and ($InputError != 2)) { 
				$sql="DELETE FROM g_cat_programa WHERE id_programa='" . $_GET['id']."'";
				prnMsg(_('El programa a sido eliminada ') . '!','info');
	} elseif (isset($_POST['enviar'])and ($InputError != 1) and ($InputError != 2)) {
                
                if ($InputError==0)
                {
			$sql = "INSERT INTO g_cat_programa (id_programa,desc_programa)
			VALUES ('".$_POST['id']."','".$_POST['desc_programa']."')";
			$ErrMsg = _('La inserccion del category fracaso porque');
			prnMsg( _('El programa').' ' .$_POST['id'] . ' ' . _('se ha creado.'),'info');
                        unset($_POST['btnnew']);
		}
	}
	unset($_POST['desline']);
	unset($_POST['id']);
	unset($catcode);	
}

if (isset($sql) && $InputError != 1 && ($InputError != 2)) {
    
	$result = DB_query($sql,$db,$ErrMsg);
}

if (isset ( $_POST ['Go1'] )) {
	$Offset = $_POST ['Offset1'];
	$_POST ['Go1'] = '';
}

if (! isset ( $_POST ['Offset'] )) {
	$_POST ['Offset'] = 0;
} else {
	if ($_POST ['Offset'] == 0) {
		$_POST ['Offset'] = 0;
	}
}

if (isset ( $_POST ['Next'] )) {
	$Offset = $_POST ['nextlist'];
}

if (isset ( $_POST ['Prev'] )) {
	$Offset = $_POST ['previous'];
}


echo "<form method='post' action=". $_SERVER['PHP_SELF'] . "?". SID . ">";

if (!isset($catcode) and !isset($_POST['btnnew'])) {
    	if ($Offset == 0) {
		$numfuncion = 1;
	} else {
		$numfuncion = $num_reg * $Offset + 1;
	}
        //ZZZ
        echo "<table border=0 style='text-align:center;margin-left:auto;margin-right:auto;' border='0' nowrap> ";
		echo '<tr>';
		echo '<td align="center" colspan=2 class="texto_lista">';
		echo '<p align="center">';
		echo '<img src="images/imgs/configuracion.png" height=25" width="25" title="' . _ ( 'Altas, Bajas y Modificaciones de Programas' ) . '" alt="">' . ' ' . $title . '<br />';
		echo '</td>';
		echo '</tr>';
		echo '</table>';
        
        //Seccion de Bisqueda
	$Offsetpagina = 1;
            echo '<table width="65%"><tr><td>';
            echo '<fieldset width=auto>';
            echo '<legend>Opciones de buqueda:</legend>';
                echo '<table width="80%" border=0 style="text-align:center;margin-left:auto;margin-right:auto;border-color:lightgray;">';
                
                echo '<tr>';
                    echo '<td align="right" width=""  style="text-align:left;">'.("ID").'</td>';
                    echo '<td align="left" width=""  style="text-align:left;"><input type="text" name="searchid" maxlength=30 size=30 value="'.$_POST['searchid'].'"></td>';
                echo '</tr>';
                echo '<tr>';
                    echo '<td align="right" width=""  style="text-align:left;">'.("Descripcion").'</td>';
                    echo '<td align="left" width=""  style="text-align:left;"><input type="text" name="searchdesc" maxlength=30 size=30 value="'.$_POST['searchdesc'].'"></td>';
                echo '</tr>';
                echo '<tr>';
                    echo '<td colspan=2 align="center" width=""  style="text-align:center;"><input type="submit" name="btnsearch" value="'._('Busqueda').'">';
                    echo '<input type="submit" name="btnnew" value="'._('Nuevo Programa').'"></td>';
                echo '</tr>';
                echo '</table>';
            echo '</fieldset>';
            echo '<table></tr></td>';
	
//esta parte sirve para mostrar la primera tabla con todos los registros existentes
	$sql = "SELECT *
		FROM g_cat_programa as a
		 " ;	
	//if (strlen($category)>=1) {
		$sql=$sql." WHERE 1=1 ";
	//}
	if (!empty($_POST['searchdesc'])) {
            $sql.=" AND desc_programa like '%".$_POST['searchdesc']."%'";
        }
        if (!empty($_POST['searchid'])) {
            $sql.=" AND id_programa like '".$_POST['searchid']."%'";
        }
	$result = DB_query ( $sql, $db );
	$ListCount = DB_num_rows ( $result );
	$ListPageMax = ceil ( $ListCount / $num_reg );
	$sql=$sql." ORDER BY a.desc_programa";
	$sql = $sql . " LIMIT " . $num_reg . " OFFSET " . ($Offset * $num_reg);
	
	$result = DB_query ( $sql, $db );
	if ( DB_num_rows($result) == 0 ) {	
		prnMsg( _('No hay registros con esa Busqueda.'),'warn');
	}
        //PAginaciones
		echo '<table width=50%>';
		echo '	<tr>';
		if ($ListPageMax > 1) {
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
			<td><input type="text" name="num_reg" size=2 value="' . $num_reg . '"></td>
			<td>
				<input type=submit name="Go1" VALUE="' . _ ( 'Ir' ) . '">
			</td>
			<td align=center cellpadding=3 >
				<input type="hidden" name="previous" value=' . number_format ( $Offset - 1 ) . '>
				<input tabindex=' . number_format ( $j + 7 ) . ' type="submit" name="Prev" value="' . _ ( 'Anterior' ) . '">
			</td>
			<td style="text-align:right">
				<input type="hidden" name="nextlist" value=' . number_format ( $Offset + 1 ) . '>
				<input tabindex=' . number_format ( $j + 9 ) . ' type="submit" name="Next" value="' . _ ( 'Siguiente' ) . '">
			</td>';
		}
		echo '</tr>
		</table>';
	
	
	
	echo '<table width="100%" cellspacing="0" border="1" bordercolor="lightgray" cellpadding="0" colspan="0" style="margin-top:0">';
        echo '<tr><td colspan="5" class="titulo_azul">Listado de Programas</td></tr>';
	echo "<tr><td>" . _('') . "</th>
              <th class='titulos_principales'>" . _('Codigo') . "</th>
              <th class='titulos_principales'>" . _('Descripcion') . "</th>
              <th class='titulos_principales'></th>
              <th class='titulos_principales'></th>
        </tr>";
	
	$k=0; //row colour counter
        $controw=0;
	while ($myrow = DB_fetch_array($result)) {
            $controw=$controw+1;
		if ($k==1){ 
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		
            echo '<td style="background-color:white;">'.$controw.'</td>'
            . '<td>'.$myrow['id_programa'].'</td>'
                    . '<td>'.$myrow['desc_programa'].'</td>'
                    .'<td><a href="'.$_SERVER['PHP_SELF'].'?id='.$myrow['id_programa'].'&modificar=1">Modificar</a></td>'
                     .'<td><a href="'.$_SERVER['PHP_SELF'].'?id='.$myrow['id_programa'].'&borrar=1">Eliminar</a></td>'
                    . '</tr>';
			
		$numfuncion=$numfuncion+1;
	} 
	echo '</table>';
	echo '</form>';
}


if (isset($catcode)) {
            $sql = "SELECT *
		FROM g_cat_programa
		WHERE id_programa='". $catcode."'" ;
		$result = DB_query($sql, $db);
                $myrow = DB_fetch_array($result);
		
		$_POST['id_programa']=$myrow['id_programa'];
		$_POST['desc_programa'] = $myrow['desc_programa'];
		$_POST['concepto']=$myrow['concepto'];
		$_POST['partida_gen']=$myrow['partida_gen'];
		$_POST['partida_esp']=$myrow['partida_esp'];
		$_POST['nombre']=$myrow['nombre'];
		
}
if (isset($catcode)) {
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Programas existentes') . '</a></div>';
}
if (isset($catcode) or isset($_POST['btnnew']))
{
echo "<form  method='post' action=". $_SERVER['PHP_SELF'] . "?". SID . ">";

echo '<br>';
if(isset($_POST['catmod'])) {
	echo "<input type=hidden name='catmod' VALUE='" . $_POST['catmod'] . "'>";
}
echo "<div class='centre'><hr width=60%>" ._('ALTA/MODIFICACION DE PROGRAMAS'). "</div><br>";
	echo '<table>';
	echo'<tr><td>' . _('IDPrograma') . ":</td>
	<td><input type='text' name='id' size=40 maxlength=100 VALUE='" .$catcode . "'></td></tr>
	<tr>";
	echo '<tr><td>' . _('Descripcion') . ":</td>
	<td><input type='text' name='desc_programa' size=40 maxlength=100 VALUE='" .$_POST['desc_programa'] . "'></td></tr>
	";
	echo '</tr>';
	
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