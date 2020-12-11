<?php


/**
 * Operaciones de Proveedor
 *
 * @category Proceso
 * @package ap_grp
 * @author Jonathan Cendejas Torres <[<email address>]>
 * @license [<url>] [name]
 * @version GIT: [<description>]
 * @link(target, link)
 * Fecha Creación: 27/11/2017
 * Fecha Modificación: 27/11/2017
 * Muestra las opciones e información de los proveedores
 */


$PageSecurity = 2;
include 'includes/session.inc';

//$title = _('Altas y Bajas de Cuentas de Proveedores');
$funcion = 983;
$title= traeNombreFuncion($funcion,$db);
include 'includes/header.inc';

//include 'includes/SecurityFunctions.inc';
include 'includes/SQL_CommonFunctions.inc';

//Librerias GRID
include 'javascripts/libreriasGrid.inc';

?>


<div name="tablaCuentas" id="tablaCuentas">
  <div name="datosCuentas" id="datosCuentas"></div>
</div>



<?php
include 'includes/footer_Index.inc';
?>

<?php
if(isset($_POST['idSupp'])){ 
?>
<script type="text/javascript">
$( document ).ready(function() {
   
    var url ="modelo/SelectSupplierModelo.php";
    console.log("mostrarInfoProv");
  
    //Opcion para operacion
    dataObj = { 
            proceso: 'cuentasProv',
            idSupp: <?php echo "'".$_POST['idSupp']."'"; ?>
          };
    //Obtener datos de las bahias
    $.ajax({
          method: "POST",
          dataType:"json",
          url:url,
          data:dataObj
      })
    .done(function( data ) {
        if(data.result){
            datos=data.contenido.cuentasProv;


            fnLimpiarTabla('tablaCuentas', 'datosCuentas');

            columnasNombres = '';
            columnasNombres += "[";
            columnasNombres += "{ name: '', type: 'bool'},";
            columnasNombres += "{ name: 'cuenta', type: 'string' },";
            columnasNombres += "{ name: 'concepto', type: 'string'},";
            columnasNombres += "{ name: 'diot', type: 'string'},";
        
            columnasNombres += "]";


             columnasNombresGrid = '';
            columnasNombresGrid += "[";
            columnasNombresGrid += " { text: '', datafield: '', width: '6%', cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
              columnasNombresGrid += " { text: 'Cuenta',datafield: 'cuenta', width: '22%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Concepto',datafield: 'concepto', width: '50%', align: 'center',hidden: false,cellsalign: 'center' },";
              columnasNombresGrid += " { text: 'Diot',datafield: 'diot', width: '22%', align: 'center',hidden: false,cellsalign: 'center' },";
          
            columnasNombresGrid += "]";

            var columnasExcel = [ 1,2,3,4];
            var columnasVisuales = [0,1,2,3,4];
            nombreExcel = data.contenido.nombreExcel;

            fnAgregarGrid_Detalle(datos, columnasNombres, columnasNombresGrid, 'datosCuentas', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);

            $('#tablaCuentas > #datosCuentas').jqxGrid({width:'40%'});
          


        }
    })
    .fail(function(result) {
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
         muestraModalGeneral(4, titulo,"Hubo un error al mostrar los datos del proveedor"); 
        console.log("ERROR");
        console.log( result );
    });

});
</script>

<?php
}else{?>
 <script type="text/javascript">
     window.open("SelectSupplier.php", "_self");
 </script>
?>

<?php
}


?>