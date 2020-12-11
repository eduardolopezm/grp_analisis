<?php

include('includes/session.inc');
$title = _('Altas, Bajas y Modificaciones de Departamentos');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');


$InputError = 0;


//si variable identificador viene llena desde la url
if (isset($_GET['u_department'])) {
	//almacenaje de valores en variable local
	$u_department = $_GET['u_department'];
}
//si variable identificador viene llena desde la forma
elseif (isset($_POST['u_department'])) {
	//almacenaje de valores a variable local
	$u_department = $_POST['u_department'];
}
//variable identificadora de  error
$InputError = 0;
//si variable enviar viene llena desde la forma o variable borrar viene llena desde la url o variable modificar viene llena desde la forma
if (isset($_POST['enviar']) || isset($_GET['borrar']) || isset($_POST['modificar']) ) {
	if (isset($_POST['department']) && strlen($_POST['department'])<3){
		$InputError = 1;
		prnMsg(_('El nombre del Departamento debe ser de al menos 3 caracteres de longitud'),'error');
		}
	unset($sql);
//aqui empieza a realizar las acciones que corresponda de acuerdo al boton o link seleccionado
	if (isset($_POST['modificar'])and ($InputError != 1)) {
		$sql = "UPDATE departments SET department='".$_POST['department']."' where u_department=".$u_department;
		$ErrMsg = _('La actualización del Departamento fallo porque');
		prnMsg( _('El departamento').' ' .$_POST['department'] . ' ' . _(' se ha actualizado.'),'info');
	} elseif (isset($_GET['borrar'])and ($InputError != 1)) {
			$sql="DELETE FROM departments WHERE u_department='" . $_GET['u_department']."'";
                
			prnMsg(_('El Departamento a sido eliminado ') . '!','info');
		
	} elseif (isset($_POST['enviar'])and ($InputError != 1)) {
			$sql= "select count(*) from departments where department='".$_POST['department']."'";
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('No se da de alta el Departamento por que ya hay un registro guardado'),'error');
		} else {
			$sql = "INSERT INTO departments (department)
			VALUES ('".$_POST['department']."')";
			$ErrMsg = _('La inserccion del departamento fracaso porque');
			prnMsg( _('El Departamento').' ' .$_POST['department'] . ' ' . _('se ha creado.'),'info');
		}
	}
//aqui se inicializan las variables en vacio
	unset($_POST['department']);
        unset($_POST['u_department']);
        unset($u_department);		
}
//si variable sql esta llena y mensaje de error es diferente de uno

if (isset($sql) && $InputError != 1 ) {
	//ejecucion de la consulta correspondiente
	$result = DB_query($sql,$db,$ErrMsg);
}
	
//creacion de la forma
echo "<form method='post' name='forma' action=". $_SERVER['PHP_SELF'] . "?". SID . ">";
//si variabale identificadora viene vacia
if (!isset($u_department) and $_POST['u_department']=='') {
//muestra la primera tabla con todos los registros existentes
	$sql = "SELECT 	u_department,
			department
		FROM departments";
	$result = DB_query($sql,$db);
	
	$sql = "SELECT 	u_department,
                department
		FROM departments";
	
        $result = DB_query($sql,$db);
	
if (!isset($u_department)) {
    
	echo "<div class='centre'>" ._('LISTADO DE DEPARTAMENTOS'). "</div>";
 //creacion de la tabla que muestra el listado de los registros existentes
	echo '<table border=1 width=50%>';
	echo "<tr><th>" . _('Codigo') . "</th>
        <th>" . _('Departamento') . "</th>
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
			<td style='text-align:center;'><a href=\"%s&u_department=%s\">Modificar</a></td>
			<td style='text-align:center;'><a href=\"%s&u_department=%s&borrar=1\">Eliminar</a></td>
			</tr>",
			$myrow['u_department'],
			$myrow['department'],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow['u_department'],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow['u_department']
			);
                } 
	echo '</table>';
}
}
if (isset($u_department)) {
echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Departamentos existentes') . '</a></div>';
}

//esta parte sirve para mostrar los datos del registro seleccionado para modificar
if (isset($u_department) and strlen($u_department)>0) {
	
	$sql = "SELECT *
		FROM departments
		WHERE u_department='". $u_department."'" ;
		
	$result = DB_query($sql, $db);
	if ( DB_num_rows($result) == 0 ) {
		prnMsg( _('No hay registros.'),'warn');
	} else {
		$myrow = DB_fetch_array($result);
		$_POST['u_department']=$myrow['u_department'];
		$_POST['department']=$myrow['department'];
		}
}

echo '<br>';
if(isset($_POST['u_department'])) {
	echo "<input type=hidden name='u_department' VALUE='" . $_POST['u_department'] . "'>";
}
echo "<div class='centre'><hr width=60%>" ._('ALTA/MODIFICACION DE DEPARTAMENTOS'). "</div><br>";
	echo '<table>';
	echo'<tr><td>' . _('Nombre del Departamento') . ":</td>
	<td><input type='text' name='department' size=40 maxlength=100 VALUE='" .$_POST['department'] . "'></td></tr>
	<tr>";
	echo '</tr></table>';
	//aqui verifica si esta vacia la variable si lo esta el valor de la accion sera enviar 
	if (!isset($u_department)) {
		echo "<div class='centre'><input type='Submit' name='enviar' value='" . _('Enviar') . "'></div>";
	}
	//aqui verifica que este llena la variable si lo esta el valor de la accion sera modificar
	elseif (isset($u_department)) {
		echo "<div class='centre'><input type='Submit' name='modificar' value='" . _('Actualizar') . "'></div>";
	}
echo '</form>';       
include('includes/footer_Index.inc');
?>