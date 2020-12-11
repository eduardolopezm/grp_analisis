<?php
/*
 * Elaboro Jesus Guadalupe Vargas Montes 
 * Fecha Creacion 03 Diciembre 2014 
 * 1. Se creo el mantenimiento de Codigo Agrupador
 *///
// 1430//
include ('includes/session.inc');
$title = _ ( 'Altas, Bajas y Modificaciones de Codigo Agrupador' );
include ('includes/header.inc');
include ('includes/SQL_CommonFunctions.inc');

$num_reg = 50;

if (isset ( $_POST ['num_reg'] )) {
	$num_reg = $_POST ['num_reg'];
}

if (isset ( $_GET['idgruopcuenta'] ) and $_GET['idgruopcuenta'] != "") {
	$idgruopcuenta = $_GET['idgruopcuenta'];
} elseif (isset ( $_POST['idgruopcuenta'] ) and $_POST['idgruopcuenta'] != "") {
	$idgruopcuenta = $_POST['idgruopcuenta'];
}

if (isset ( $_POST ['codigoagrupador'] )) {
	$codigoagrupador = $_POST ['codigoagrupador'];
} else {
	$codigoagrupador = "";
}

if (isset ( $_POST ['nombreagrupador'] )) {
	$nombreagrupador = $_POST ['nombreagrupador'];
} else {
	$nombreagrupador = "";
}

if (isset ( $_POST ['filcodigoagrupador'] )) {
	$filcodigoagrupador = $_POST ['filcodigoagrupador'];
} else {
	$filcodigoagrupador = "";
}

if (isset ( $_POST ['filnombreagrupador'] )) {
	$filnombreagrupador = $_POST ['filnombreagrupador'];
} else {
	$filnombreagrupador = "";
}

if (isset ( $_POST ['nivel'] )) {
	$nivel = $_POST ['nivel'];
} else if (isset ( $_GET ['nivel'] )) {
	$nivel = $_GET ['nivel'];
}

if (isset ( $_POST ['tiponivel'] )) {
	$tiponivel = $_POST ['tiponivel'];
} else if (isset ( $_GET ['tiponivel'] )) {
	$tiponivel = $_GET ['tiponivel'];
}

if (isset ( $_POST ['idgruopcuenta'] )) {
	$gruopcuenta = $_POST ['idgruopcuenta'];
} else {
	$gruopcuenta = "";
}


$InputError = 0;
if (isset ( $_POST ['enviar'] ) || isset ( $_GET ['borrar'] ) || isset ( $_POST ['modificar'] )) {
	if (isset ( $_POST ['codigoagrupador'] ) && strlen ( $_POST ['codigoagrupador'] ) == "") {
		$InputError = 1;
		prnMsg ( _ ( 'Debe agregar el Codigo Agrupador' ), 'error' );
	}
	if (isset ( $_POST ['nombreagrupador'] ) && strlen ( $_POST ['nombreagrupador'] ) == "") {
		$InputError = 2;
		prnMsg ( _ ( 'Debe Agregar un nombre al codigo agrupador' ), 'error' );
	}
	unset ( $sql );
	if (isset ( $_POST ['modificar'] ) and ($InputError != 1) and ($InputError != 2)) {
		$sql = "UPDATE GroupChartmasterSat
				SET codigoagrupador='" . $_POST ['codigoagrupador'] . " ',
					nombreagrupador='" . $_POST ['nombreagrupador'] . "',
					nivel= '" . $_POST ['nivel'] . "',
					tiponivel='" . $_POST ['tiponivel'] . "',
                                        codagruppadre = '".$_POST['codagruppadre']."',
                                        levelsat = '".$_POST['levelsat']."'
				WHERE idgruopcuenta='$idgruopcuenta'";
		
		$ErrMsg = _ ( 'La actualizaci�n del codigo agrupador fallo porque' );
		prnMsg ( _ ( 'El codigo agrupador' ) . ' ' . $_POST ['nombreagrupador'] . ' ' . _ ( ' se ha actualizado.' ), 'info' );
	} elseif (isset ( $_GET ['borrar'] )) {
		
			$sql = "DELETE 
                                FROM GroupChartmasterSat 
                                WHERE idgruopcuenta='" . $_GET ['idgruopcuenta'] . "'";
			prnMsg ( _ ( 'El codigo agrupador a sido eliminado ' ) . '!', 'info' );
		
	} elseif (isset ( $_POST ['enviar'] ) and ($InputError != 1) and ($InputError != 2)) {
		$FechaAltaObjetivo = date ( "Y" ) . "-" . date ( "m" ) . "-" . date ( "d" );
                
                //die($_POST ['codigoagrupador'].'--'.$_POST['codagruppadre']);
		$sql = "INSERT INTO GroupChartmasterSat (codigoagrupador, 
                                                            nombreagrupador,
                                                            nivel,
                                                            tiponivel,
                                                            levelsat,
                                                            codagruppadre
                                                            )
			VALUES ('" . $_POST ['codigoagrupador'] . "',
                                '" . $_POST ['nombreagrupador'] . "',
                                '" . $_POST ['nivel'] . "',
                                '" . $_POST ['tiponivel'] . "',
                               '".$_POST['levelsat']."',
                                '".$_POST['codagruppadre']."'
                                )";
		$ErrMsg = _ ( 'La inserccion del codigo agrupador fracaso porque' );
		prnMsg ( _ ( 'El codigo agrupador' ) . ' ' . $_POST ['nombreagrupador'] . ' ' . _ ( 'se ha creado exitosamente...!' ), 'info' );
		// echo $sql;
	}
	unset ( $_POST ['codigoagrupador'] );
	unset ( $_POST ['nombreagrupador'] );
	unset ( $_POST ['nivel'] );
	unset ( $_POST ['tiponivel'] );
        unset ( $_POST ['codagruppadre']);
        unset($_POST['levelsat']);
        unset($idgruopcuenta);
}

if (isset ( $sql )) {
	$result = DB_query ( $sql, $db, $ErrMsg );
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

echo "<form method='post' action=" . $_SERVER ['PHP_SELF'] . "?" . SID . ">";
// echo 'codigoagrupador'.$codigoagrupador;
// if (!isset( $_POST ['codigoagrupador'] ) and $_POST ['codigoagrupador'] == "") {
// if (!isset($_POST ['codigoagrupador']) or (isset( $_POST ['codigoagrupador'] ) and $_POST ['codigoagrupador'] == "") ) {
if (!isset($_POST ['codigoagrupador']) or isset( $_POST ['codigoagrupador'] )  ) {
// if (!isset( $_POST ['codigoagrupador'] ) ) {
     
	echo '<table><tr>
	<td>
		' . _ ( 'Codigo' ) . '<br><input type="text" name="filcodigoagrupador" value="' . $filcodigoagrupador . '" size=25 maxlength=55>
	</td>
	<td>
		' . _ ( 'Nombre' ) . '<br><input type="text" name="filnombreagrupador" value="' . $filnombreagrupador . '" size=25 maxlength=55>
	</td>
	<td valign=bottom>
		<input type=submit name=buscar value=' . _ ( 'Buscar' ) . '>
	</td></tr></table>';
	echo "<div class='centre'><hr width=50%></div><br>";
	if ($Offset == 0) {
		$numfuncion = 1;
	} else {
		$numfuncion = $num_reg * $Offset + 1;
	}
	$Offsetpagina = 1;
	
	// esta parte sirve para mostrar la primera tabla con todos los registros existentes
        //padre.nombreagrupador as nombrepadre,
	$sql = "SELECT GroupChartmasterSat.idgruopcuenta,
						GroupChartmasterSat.codigoagrupador,
                       GroupChartmasterSat.nombreagrupador,
                       GroupChartmasterSat.nivel,
                       nivelesgrupochartmaster.nombrenivel,
                       GroupChartmasterSat.codagruppadre,
                       GroupChartmasterSat.levelsat
		FROM GroupChartmasterSat
                INNER JOIN nivelesgrupochartmaster ON GroupChartmasterSat.tiponivel = nivelesgrupochartmaster.codigonivel
                ";//LEFT JOIN GroupChartmasterSat as padre ON padre.codagruppadre = GroupChartmasterSat.codigoagrupador
	if (strlen ( $filcodigoagrupador ) >= 1) {
		$sql = $sql . " WHERE GroupChartmasterSat.codigoagrupador ='" . $filcodigoagrupador."'";
		if (strlen ( $filnombreagrupador ) >= 1) {
			$sql = $sql . " AND padre.nombreagrupador like '%" . $filnombreagrupador . "%'";
		}
	} Elseif (strlen ( $filnombreagrupador ) >= 1) {
		$sql = $sql . " WHERE padre.nombreagrupador like '%" . $filnombreagrupador . "%'";
		
	}
        $sql = $sql ." ORDER BY GroupChartmasterSat.codigoagrupador ASC ";
       // echo $sql;
	$result = DB_query ( $sql, $db );
	
	if (DB_num_rows ( $result ) == 0) {
		
		prnMsg ( _ ( 'No hay registros con esa Busqueda.' ), 'warn' );
	}
	$sqlnum = DB_query($sql, $db);
        $ListCount = DB_num_rows ( $sqlnum );
	$sql = $sql . " LIMIT " . $num_reg . " OFFSET " . ($Offset * $num_reg);
	$result = DB_query ( $sql, $db );
	//$ListCount = DB_num_rows ( $result );
	$ListPageMax = ceil ( $ListCount / $num_reg );
	//echo '->'.$ListCount.'/'.$num_reg;
	// / fin consulta join
	
	if (! isset ( $idgruopcuenta )) {
		echo "<div class='centre'>" . _ ( 'LISTADO DE CODIGO AGRUPADOR' ) . "</div>";
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
			<td><input type="text" name="num_reg" size=1 value="' . $num_reg . '"></td>
			<td>
				<input type=submit name="Go1" VALUE="' . _ ( 'Ir' ) . '">
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
		echo '</tr>
		</table>';
	}
	echo '<table border=1 width=70%>';
	echo "<tr><th>" . _ ( 'Codigo' ) . "</th>
			  <th>" . _ ( 'Nombre' ) . "</th>
			  <th>" . _ ( 'nivel' ) . "</th>
                          <th>"._('Nivel Equiv')."</th>
			  <th>" . _ ( 'tiponivel' ) . "</th>
                          <th>" ._('Grupo Padre')."</th>   
			  <th></th>
        	  </tr>";
	$k = 0; // row colour counter
	while ( $myrow = DB_fetch_array ( $result ) ) {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
                $sqlPadre = "Select nombreagrupador from GroupChartmasterSat where codigoagrupador ='".$myrow ['codagruppadre']."' ";
                $ResultPadre = DB_query($sqlPadre, $db);
                list($nombrePadre)=  DB_fetch_array($ResultPadre);
                
		echo "<td>".$myrow ['codigoagrupador']."</td>";
                echo "<td>".$myrow ['nombreagrupador']."</td>";
                echo "<td>".$myrow ['nivel']."</td>";
                echo "<td>".$myrow ['levelsat']."</td>";
                echo "<td>".$myrow ['nombrenivel']."</td>";
                echo "<td>".$nombrePadre."</td>";
                $paginaActualiza = "<a href='" . $_SERVER ['PHP_SELF'] . "?" ."&idgruopcuenta=".$myrow ['idgruopcuenta']."'>Modificar</a>";
                $paginaBorrar = "<a href='" . $_SERVER ['PHP_SELF'] . "?" ."&idgruopcuenta=".$myrow ['idgruopcuenta']."&borrar=1'>Eliminar</a>";
                echo "<td>".$paginaActualiza."</td>";
                echo "<td>".$paginaBorrar."</td>";
		$numfuncion = $numfuncion + 1;
	}
	echo '</table>';
	echo '</form>';
}
if (isset ( $idgruopcuenta )) {
	$sql = "SELECT *
			FROM GroupChartmasterSat
			WHERE idgruopcuenta ='" . $idgruopcuenta . "'";
	$result = DB_query ( $sql, $db );
	$myrow = DB_fetch_array ( $result );
	$_POST ['idgruopcuenta'] = $myrow ['idgruopcuenta'];
	$_POST ['codigoagrupador'] = $myrow ['codigoagrupador'];
	$_POST ['nombreagrupador'] = $myrow ['nombreagrupador'];
	$_POST ['nivel'] = $myrow ['nivel'];
        $_POST ['tiponivel'] = $myrow ['tiponivel'];
        $_POST ['codagruppadre'] = $myrow ['codagruppadre'];
        $_POST['levelsat'] = $myrow['levelsat'];
}
if (isset ( $idgruopcuenta )) {
	echo "<div class='centre'><a href='" . $_SERVER ['PHP_SELF'] . "?" . SID . "'>" . _ ( 'Codigo Agrupador  existentes' ) . '</a></div>';
}
echo "<form  method='post' action=" . $_SERVER ['PHP_SELF'] . "?" . SID . ">";
echo '<br>';
echo "<div class='centre'><hr width=60%>" . _ ( 'ALTA/MODIFICACION DE CODIGO AGRUPADOR' ) . "</div><br>";
echo '<table>';
echo '<tr><td><input type="hidden" name="idgruopcuenta" size="40" maxlength="100" VALUE="' . $_POST ['idgruopcuenta'] . '"></td></tr>';
echo '</td>';
echo '<tr><td>' . _ ( 'Codigo Agrupador' ) . ":</td>";
if(isset($_POST['idgruopcuenta']) and $_POST['idgruopcuenta'] <> ""){
    echo "<td><input type=hidden name=codigoagrupador value='" . $_POST ['codigoagrupador'] . "'>" . $_POST ['codigoagrupador'] . "</td></tr>";
}else{
    echo '<td><input type=text name=codigoagrupador value="' . $_POST ['codigoagrupador'] . '"></td></tr>';
}

echo '<tr><td>' . _ ( 'Nombre' ) . ":</td>";
echo "<td><input type=text name=nombreagrupador value='" . $_POST ['nombreagrupador'] . "'></td></tr>";
echo '<tr><td>' . _ ( 'Nivel' ) . ":</td>";
if(!isset($_POST ['nivel']) and $_POST ['nivel'] == ""){
    $_POST ['nivel'] = 1;
}

echo "<td><select name=nivel>";
if($_POST ['nivel'] == 1){
    echo"<option selected value=1>1</option>";
    echo"<option value=2>2</option>";
    echo"<option value=3>3</option>";
    echo"<option value=4>4</option>";
}elseif($_POST ['nivel'] == 2){
   echo"<option value=1>1</option>";
   echo"<option selected value=2>2</option>";
   echo"<option value=3>3</option>";
   echo"<option value=4>4</option>";
}elseif($_POST ['nivel'] == 3){
    echo"<option value=1>1</option>";
    echo"<option value=2>2</option>";
    echo"<option selected value=3>3</option>"; 
    echo"<option value=4>4</option>";
}elseif($_POST ['nivel'] == 4){
    echo"<option value=1>1</option>";
    echo"<option value=2>2</option>";
    echo"<option value=3>3</option>"; 
    echo"<option selected value=4>4</option>";
}
echo "</select>";
echo "<input type=submit name=levelsat value='->'>";
echo "</td></tr>";

echo '<tr><td>' . _ ( 'NivelEquiv. SAT' ) . ":</td>";
if(!isset($_POST ['levelsat']) and $_POST ['levelsat'] == ""){
    $_POST ['levelsat'] = 0;
}

echo "<td><select name=levelsat>";
if($_POST ['levelsat'] == 1){
    echo"<option selected value=1>1</option>";
    echo"<option value=2>2</option>";
    echo"<option value=3>3</option>";
    echo"<option value=4>4</option>";
    echo"<option value=0>0</option>";
}elseif($_POST ['levelsat'] == 2){
   echo"<option value=1>1</option>";
   echo"<option selected value=2>2</option>";
   echo"<option value=3>3</option>";
   echo"<option value=4>4</option>";
   echo"<option value=0>0</option>";
}elseif($_POST ['levelsat'] == 3){
    echo"<option value=1>1</option>";
    echo"<option value=2>2</option>";
    echo"<option selected value=3>3</option>"; 
    echo"<option value=4>4</option>";
    echo"<option value=0>0</option>";
}elseif($_POST ['levelsat'] == 4){
    echo"<option value=1>1</option>";
    echo"<option value=2>2</option>";
    echo"<option value=3>3</option>"; 
    echo"<option selected value=4>4</option>";
    echo"<option value=0>0</option>";
}elseif($_POST ['levelsat'] == 0){
    echo"<option value=1>1</option>";
    echo"<option value=2>2</option>";
    echo"<option value=3>3</option>"; 
    echo"<option value=>4</option>";
    echo"<option selected value=0>0</option>";
}
echo "</select>";
echo "</td></tr>";
echo '<tr><td>' . _ ( 'Tipo de Nivel' ) . ":</td>";
$sql = "SELECT codigonivel,
		nombrenivel
        FROM nivelesgrupochartmaster";
$result = DB_query($sql, $db);
echo "<td><select name=tiponivel>";
while($myrow = DB_fetch_array($result)){
    if($_POST['tiponivel'] == $myrow['codigonivel']){
        echo"<option selected value='".$myrow['codigonivel']."'>".$myrow['nombrenivel']."</option>";
    }else{
        echo"<option value='".$myrow['codigonivel']."'>".$myrow['nombrenivel']."</option>";
    }
}
echo "</select>";

echo "</td></tr>";

echo '<tr><td>' . _ ( 'Codigo Agrupador Padre' ) . ":</td>";
$sql = "SELECT  codigoagrupador,
		nombreagrupador
        FROM GroupChartmasterSat";
if(isset($_POST['nivel'])){
	$nivelsearch = 0;
	if($_POST['nivel'] > 1) {
		$nivelsearch = $_POST['nivel'] - 1;
	}
    $sql = $sql."  WHERE nivel = ".$nivelsearch;
}
       
$result = DB_query($sql, $db);
echo "<td><select name=codagruppadre>";
echo '<option value="">Mayor General</option>';
while($myrow = DB_fetch_array($result)){
    if($_POST['codagruppadre'] == $myrow['codigoagrupador']){
        echo"<option selected value='".$myrow['codigoagrupador']."'>".$myrow['nombreagrupador']."</option>";
    }else{
        echo"<option value='".$myrow['codigoagrupador']."'>".$myrow['nombreagrupador']."</option>";
    }
}
echo "</select></td></tr>";
echo '</table>';
// aqui verifica si esta vacia la variable si lo esta el valor de la accion sera enviar

if (! isset ( $idgruopcuenta )) {
	echo "<div class='centre'><input type='Submit' name='enviar' value='" . _ ( 'Enviar' ) . "'></div>";
} // aqui verifica que este llena la variable si lo esta el valor de la accion sera modificar
elseif (isset ( $idgruopcuenta )) {
	
	echo "<div class='centre'><input type='Submit' name='modificar' value='" . _ ( 'Actualizar' ) . "'></div>";
}
echo '</form>';
// var_dump($sql);
include ('includes/footer.inc');
?>
