<?php
/*
	$Revision: 1.4
	$Desarrollado por : desarrollo
	$Modificado por: desarrollo
	$FechaModificacion: 13 Nov 2009
	$CAMBIOS: Pagina para alta de funciones por perfil
*/

$PageSecurity=15;
include('includes/session.inc');
$title = _('Mantenimiento de usuario');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/group_add.png" title="' . _('Buscar') . '" alt="">' . ' ' . $title.'<br>';

// Make an array of the security roles
$sql = 'SELECT secroleid, 
		secrolename 
	FROM securityroles ORDER BY secroleid';
$Sec_Result = DB_query($sql, $db);
$SecurityRoles = array();
// Now load it into an a ray using Key/Value pairs
while( $Sec_row = DB_fetch_row($Sec_Result) ) {
	$SecurityRoles[$Sec_row[0]] = $Sec_row[1];
}
DB_free_result($Sec_Result);
    $sql = "SELECT * FROM sec_modules s where active=1 ";
    $Result = DB_query($sql, $db);
    echo '<table width=80% align=center>';
    echo "<tr><td colspan=2 align=center>"._('Funciones X Usuario')."</td></tr>";
    
    if (DB_num_rows($Result)>0 ) {
            echo "<tr><td colspan=2 ><hr></td></tr>";
    }
    $k=0; //row colour counter
    $j=0;
    while($AvailRow = DB_fetch_array($Result)) {
                            
            if ($k==1){
                    echo '<tr class="EvenTableRows">';
                    $k=0;
            } else {
                    echo '<tr class="OddTableRows">';
                    $k=1;
            }
            
            $moduleid=$AvailRow['moduleid'];
            $namemodule=$AvailRow['title'];
            
            echo "<td colspan=2><li>$namemodule</li></td>";
            echo '</tr>';
            /* trae submodulos*/
            $y=0;
            $a=0;
            $sql = "SELECT * FROM sec_submodules s where active=1 and moduleid=".$moduleid;
            $ReSubmodule = DB_query($sql, $db);
            $condmodule=false;
            if (DB_num_rows($ReSubmodule)>0 ) {
                    echo "<tr><td colspan=2><table  width=90% align=center>";
                    $condmodule=true;
            }
            while($submoduleRow = DB_fetch_array($ReSubmodule)) {
                    if ($y==1){
                            echo '<tr class="EvenTableRows">';
                            $y=0;
                    } else {
                            echo '<tr class="OddTableRows">';
                            $y=1;
                    }
                    $submoduleid=$submoduleRow['submoduleid'];
                    $namesubmodule=$submoduleRow['title'];
                    echo '<td colspan=2><li>'.$namesubmodule.'</li></td>';
                    /* trae funciones*/
                    $z=0;
                    $sql = "SELECT * FROM sec_Functions s where active=1 and SubModuleid=".$submoduleid;
                    $ReFuntion = DB_query($sql, $db);
                    $condfuntion=false;
                    if (DB_num_rows($ReFuntion)>0 ) {
                            echo "<tr><td colspan=2><table  width=80% align=center>";
                            $condfuntion=true;
                    }
                    while($ResFuntion = DB_fetch_array($ReFuntion)) {
                            if ($y==1){
                                    echo '<tr class="EvenTableRows">';
                                    $y=0;
                            } else {
                                    echo '<tr class="OddTableRows">';
                                    $y=1;
                            }
                            $Funtionid=$ResFuntion['Funtionid'];
                            $nameFuntion=$ResFuntion['title'];
                            echo '<td ><li>'.$nameFuntion.'</li></td>';
                            
                            echo '<td><input type=radio name=funcion'.$z.' value=1>'._('Agregar');
                            echo '<input type=radio name=funcion'.$z.' value=2>'._('Quitar');
                            echo '<input type=radio name=funcion'.$z.' value=3 checked>'._('Mantener Perfil');
                            echo '</td>';
                            $z=$z+1;
                    }
                    if ($condfuntion==true){
                            echo "</table></td></tr>";
                    }	
                    
            }
            if ($condmodule==true){
                    echo "</table></td></tr>";
            }
            
            $j=$j+1;
            $submoduleid=0;
            
    }
    echo '</table>';

    include('includes/footer.inc');
    exit ;
	
?>