<?php
/*
ARCHIVO MODIFICADO POR: Desarrollador
FECHA DE MODIFICACION: 17/JUNIO/2011
DESCRIPCION: REALIZA ALTAS BAJAS Y MODIFICACIONES DE MARCAS
FIN DE CAMBIOS
*/
include('includes/session.inc');
$title = _('Altas, Bajas y Modificaciones de Marcas de Productos');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
$funcion=304;
include('includes/SecurityFunctions.inc');
$InputError = 0;
//si variable identificador viene llena desde la url
if (isset($_GET['manufacturerid'])) {
	//almacenaje de valores en variable local
	$manufacturerid = $_GET['manufacturerid'];
}
//si variable identificador viene llena desde la forma
elseif (isset($_POST['manufacturerid'])) {
	//almacenaje de valores a variable local
	$manufacturerid = $_POST['manufacturerid'];
}
//variable identificadora de  error
$InputError = 0;
//si variable enviar viene llena desde la forma o variable borrar viene llena desde la url o variable modificar viene llena desde la forma
if (isset($_POST['enviar']) || isset($_GET['borrar']) || isset($_POST['modificar']) ) {
	if (isset($_POST['manufacturer']) && strlen($_POST['manufacturer'])<3){
		$InputError = 1;
		prnMsg(_('La Marca debe ser de al menos 3 caracteres de longitud'),'error');
		}
	unset($sql);
//aqui empieza a realizar las acciones que corresponda de acuerdo al boton o link seleccionado
	if (isset($_POST['modificar'])and ($InputError != 1)) {
		$sql = "UPDATE stockmanufacturer SET manufacturer='".$_POST['manufacturer']."' where manufacturerid='".$_POST['manufacturerid']."'";
		$ErrMsg = _('La actualización del Departamento fallo porque');
		prnMsg( _('El departamento').' ' .$_POST['manufacturer'] . ' ' . _(' se ha actualizado.'),'info');
	} elseif (isset($_GET['borrar'])and ($InputError != 1)) {
			$sqlC= "select count(*) from stockmaster where manufacturer='".trim($manufacturerid)."'";
			$result = DB_query($sqlC,$db);
			$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			
			prnMsg(_('La Marca ha sido asignado al menos a un producto') . '!','info');
		}else{
			$sql="DELETE FROM stockmanufacturer WHERE manufacturerid='" . $_GET['manufacturerid']."'";
			prnMsg(_('La Marca ha sido eliminada ') . '!','info');
		}
                
		
	} elseif (isset($_POST['enviar'])and ($InputError != 1)) {
			$sql= "select count(*) from stockmanufacturer where manufacturer='".trim($_POST['manufacturerid'])."'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('No se da de alta La Marca por que ya hay un registro guardado'),'error');
		} else {
			$sql = "INSERT INTO stockmanufacturer (manufacturerid,manufacturer)
			VALUES ('".$_POST['manufacturerid']."','".$_POST['manufacturer']."')";
			$ErrMsg = _('La inserccion del departamento fracaso porque');
			prnMsg( _('La Marca').' ' .$_POST['manufacturer'] . ' ' . _('se ha creado.'),'info');
		}
	}
//aqui se inicializan las variables en vacio
	unset($_POST['manufacturer']);
        unset($_POST['manufacturerid']);
        unset($manufacturerid);		
}
//si variable sql esta llena y mensaje de error es diferente de uno

if (isset($sql) && $InputError != 1 ) {
	//ejecucion de la consulta correspondiente
	$result = DB_query($sql,$db,$ErrMsg);
}
	
//creacion de la forma
echo "<form method='post' name='forma' action=". $_SERVER['PHP_SELF'] . "?". SID . ">";
//si variabale identificadora viene vacia
if (!isset($manufacturerid) and $_POST['manufacturerid']=='') {
//muestra la primera tabla con todos los registros existentes
	$sql = "SELECT 	manufacturerid,
			manufacturer
		FROM stockmanufacturer";
	$result = DB_query($sql,$db);
	
	$sql = "SELECT 	manufacturerid,
                manufacturer
		FROM stockmanufacturer";
	
        $result = DB_query($sql,$db);
	
if (!isset($manufacturerid)) {
    
	echo "<div class='centre'>" ._('LISTADO DE MARCAS'). "</div>";
 //creacion de la tabla que muestra el listado de los registros existentes
	echo '<table border=1 width=50%>';
	echo "<tr><th>" . _('Codigo') . "</th>
        <th>" . _('Marca') . "</th>
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
			<td style='text-align:center;'><a href=\"%s&manufacturerid=%s\">Modificar</a></td>
			<td style='text-align:center;'><a href=\"%s&manufacturerid=%s&borrar=1\">Eliminar</a></td>
			</tr>",
			$myrow['manufacturerid'],
			$myrow['manufacturer'],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow['manufacturerid'],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow['manufacturerid']
			);
                } 
	echo '</table>';
}
}
if (isset($manufacturerid)) {
echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Departamentos existentes') . '</a></div>';
}

//esta parte sirve para mostrar los datos del registro seleccionado para modificar
if (isset($manufacturerid) and strlen($manufacturerid)>0) {
	
	$sql = "SELECT *
		FROM stockmanufacturer
		WHERE manufacturerid='". $manufacturerid."'" ;
		
	$result = DB_query($sql, $db);
	if ( DB_num_rows($result) == 0 ) {
		prnMsg( _('No hay registros.'),'warn');
	} else {
		$myrow = DB_fetch_array($result);
		$_POST['manufacturerid']=$myrow['manufacturerid'];
		$_POST['manufacturer']=$myrow['manufacturer'];
		}
}

echo '<br>';
if(isset($_POST['manufacturerid'])) {
	echo "<input type=hidden name='manufacturerid' VALUE='" . $_POST['manufacturerid'] . "'>";
}
echo "<div class='centre'><hr width=60%>" ._('ALTA/MODIFICACION DE MARCAS PARA PRODUCTOS'). "</div><br>";
	echo '<table>';
	echo "<tr><td>"._('Codigo')."</td>";
	if (isset($manufacturerid)) {
		echo "<td><input type='hidden' name='manufacturerid' size=40 maxlength=100 VALUE='" .$_POST['manufacturerid'] . "'>".$_POST['manufacturerid']."</td>";
	}else{
		echo "<td><input type='text' name='manufacturerid' size=40 maxlength=100 VALUE='" .$_POST['manufacturerid'] . "'></td>";
	}
	
	echo'<tr><td>' . _('Nombre de Marca') . ":</td>
	<td><input type='text' name='manufacturer' size=40 maxlength=100 VALUE='" .$_POST['manufacturer'] . "'></td></tr>
	<tr>";
	echo '</tr></table>';
	//aqui verifica si esta vacia la variable si lo esta el valor de la accion sera enviar 
	if (!isset($manufacturerid)) {
		echo "<div class='centre'><input type='Submit' name='enviar' value='" . _('Enviar') . "'></div>";
	}
	//aqui verifica que este llena la variable si lo esta el valor de la accion sera modificar
	elseif (isset($manufacturerid)) {
		echo "<div class='centre'><input type='Submit' name='modificar' value='" . _('Actualizar') . "'></div>";
	}
echo '</form>';       
include('includes/footer.inc');
?>