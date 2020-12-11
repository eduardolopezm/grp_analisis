<?php

include('includes/session.inc');
$title = _('Altas, Bajas y Modificaciones de Clases de Productos');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');


if (isset($_POST['clase'])) {
	$clase= $_POST['clase'];
} else {
	$clase="";
}	


//esta es la variable con la que nos guiamos para saber ssi es la primera vez que entran
//y para poder mostrar los datos del seleccionado cuando van a modificar
if (isset($_GET['idclassproduct'])) {
	$idclass = $_GET['idclassproduct'];
} elseif (isset($_POST['idclassproduct'])) {
	$idclass = $_POST['idclassproduct'];
}

$InputError = 0;
if (isset($_POST['enviar']) || isset($_GET['borrar']) || isset($_POST['modificar']) ) {
		if (isset($_POST['description']) && strlen($_POST['description'])<3){
		$InputError = 1;
		prnMsg(_('El nombre de la Clase debe ser de al menos 3 caracteres de longitud'),'error');
		}
		if (isset($_POST['idclass']) && strlen($_POST['idclass'])<1){
		$InputError = 2;
		prnMsg(_('El valor del Codigo debe ser de al menos 1 caracter de longitud'),'error');
		}
	unset($sql);
	
	if (isset($_POST['modificar'])and ($InputError != 1) and ($InputError != 2)) {
		
		$sql = "UPDATE classproduct
		SET classdescription='" .$_POST['description']." '
		WHERE idclassproduct='" .$_POST['idclass']."'";
		
		$ErrMsg = _('La actualización de la Clase fracaso porque');
		
		prnMsg( _('La Clase').' ' .$_POST['description'] . ' ' . _(' se ha actualizado.'),'info');
		
	} elseif (isset($_GET['borrar'])and ($InputError != 1) and ($InputError != 2)) {
			/*$sql= "select count(*) from classproduct where idclassproduct='".$_POST['idclass']."'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
			if ($myrow[0]>0) {
				prnMsg( _('No se da de alta la Clase por que ya hay un registro guardado'),'error');
			}else{*/
				$sql="DELETE FROM classproduct WHERE idclassproduct='" . $_GET['idclassproduct']."'";
				prnMsg(_('La Clase a sido eliminada ') . '!','info');
			}
	 elseif (isset($_POST['enviar'])and ($InputError != 1) and ($InputError != 2)) {
			$sql= "select count(*) from classproduct where idclassproduct='".$_POST['idclass']."'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('No se da de alta la Clase por que ya hay un registro guardado'),'error');
		} else {
			$sql = "INSERT INTO classproduct (idclassproduct,classdescription)
			VALUES ('".$_POST['idclass']."','".$_POST['description']."')";
			$ErrMsg = _('La inserccion de la Clase fracaso porque');
			
			prnMsg( _('La Clase').' ' .$_POST['description'] . ' ' . _('se ha creado.'),'info');
		}
	}
	
}
	unset($_POST['description']);
	unset($_POST['idclass']);
	//unset($idclass);
	//unset($clase);
if (isset($sql) && $InputError != 1 ) {
	//ejecucion de la consulta correspondiente
	$result = DB_query($sql,$db,$ErrMsg);
}

echo "<form method='post' action=". $_SERVER['PHP_SELF'] . "?". SID . ">";

if (!isset($idclass) ) {
	echo '<table><tr>
	<td>
		' . _('Clase') . '<br><input type="text" name="clase" value="'.$clase.'" size=25 maxlength=55>
	</td>
	
	<td valign=bottom>
		<input type=submit name=buscar value=' . _('Buscar') . '>
	</td></tr></table>';
	echo "<div class='centre'><hr width=50%></div><br>";
	echo "</form>";
//esta parte sirve para mostrar la primera tabla con todos los registros existentes

	$sql = "SELECT 	*
		FROM classproduct
		WHERE idclassproduct <>''";
	if (strlen($clase)>=1) {
		$sql=$sql.' and classdescription like "%'.$clase.'%"';
	}
	$result = DB_query($sql,$db);
	
	
	$sql = "SELECT 	*
		FROM classproduct
		WHERE idclassproduct <>'' ";
	if (strlen($clase)>=1) {
		$sql=$sql." and (classdescription like '%".$clase."%')";
	
		if ( DB_num_rows($result) == 0 ) {
		
		prnMsg( _('No hay registros con esa Busqueda.'),'warn');
		}
	}
	$result = DB_query($sql,$db);
	
	
	
/// fin consulta join	
	
	echo "<form  method='post' action=". $_SERVER['PHP_SELF'] . "?". SID . ">";
	if (!isset($idclass)) {
		echo "<div class='centre'>" ._('LISTADO DE CLASES DE PRODUCTO'). "</div>";
	}
	
	echo '<table border=1 width=50%>';
	echo "<tr><th>" . _('Codigo') . "</th>
	<th>" . _('Nombre de la Clase') . "</th>
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
			<td style='text-align:center;'><a href=\"%s&idclassproduct=%s\">Modificar</a></td>
			<td style='text-align:center;'><a href=\"%s&idclassproduct=%s&borrar=1\">Eliminar</a></td>
			</tr>",
			$myrow['idclassproduct'],
			$myrow['classdescription'],
			
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow['idclassproduct'],
			
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow['idclassproduct']
			);
		//$numfuncion=$numfuncion+1;
	} 
	echo '</table>';
	echo '</form>';
}


if (isset($idclass)) {
            $sql = "SELECT *
		FROM classproduct
		WHERE idclassproduct='".$idclass."'";
		$result = DB_query($sql, $db);
                $myrow = DB_fetch_array($result);
		$_POST['idclass']=$myrow['idclassproduct'];
		$_POST['description'] = $myrow['classdescription'];
		}
if (isset($idclass)) {
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Clases existentes') . '</a></div>';
}
echo "<form  method='post' action=". $_SERVER['PHP_SELF'] . "?". SID . ">";

echo '<br>';
/*if(isset($_POST['idclass'])) {
	echo "<input type=hidden name='idclass' VALUE='" . $_POST['idclass'] . "'>";
}
*/if (!isset($idclass)){
echo "<div class='centre'><hr width=60%>" ._('ALTA/MODIFICACION DE CLASES'). "</div><br>";
	echo '<table>';
	echo'<tr><td>' . _('Codigo de Clase') . ":</td>
	<td><input type='text' name='idclass'  size=40 maxlength=100 VALUE='" .$_POST['idclass'] . "'></td></tr>
	<tr>";
	echo '<tr><td>' . _('Nombre de la Clase') . ":</td>
	<td><input type='text' name='description' size=40 maxlength=100 VALUE='" .$_POST['description'] . "'></td></tr>";
	
	echo '</tr></table>';
	//aqui verifica si esta vacia la variable si lo esta el valor de la accion sera enviar
	
	 
		echo "<div class='centre'><input type='Submit' name='enviar' value='" . _('Enviar') . "'></div>";
	}
	//aqui verifica que este llena la variable si lo esta el valor de la accion sera modificar
	elseif (isset($idclass)) {
		echo '<table>';
	echo'<tr><td>' . _('Codigo de Clase') . ":</td>
	<td><input type='text' name='idclass' disabled=true size=40 maxlength=100 VALUE='" .$_POST['idclass'] . "'></td></tr>
	<tr>";
	echo '<tr><td>' . _('Nombre de la Clase') . ":</td>
	<td><input type='text' name='description' size=40 maxlength=100 VALUE='" .$_POST['description'] . "'></td></tr>";
		
		echo "<div class='centre'><input type='Submit' name='modificar' value='" . _('Actualizar') . "'></div>";
		
	}
echo '</form>';
include('includes/footer_Index.inc');
?>