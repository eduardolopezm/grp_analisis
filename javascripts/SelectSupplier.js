/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Arturo Lopez Peña 
 * @version 0.1
 */
var today = new Date();
var dd = today.getDate();
var mm = today.getMonth()+1; //January is 0!

var yyyy = today.getFullYear();
if(dd<10){
    dd='0'+dd;
} 
if(mm<10){
    mm='0'+mm;
} 
var today = dd+'-'+mm+'-'+yyyy;

$( document ).ready(function() {
    //Mostrar Catalogo
    
    $('#dateDesde').val(today);
    $('#dateHasta').val(today);
    
    //fnMostrarDatos();


$('#btnAgregar').click(function() {
    window.open("Suppliers.php", "_self");
});

 $('#filtrar').click(function(){
     fnMostrarDatos();
 });

  $('#btnSaveCC').click(function(){
    if( ($('#cuentasCon').val()!=0) &&($('#diotSel').val()!=0 ) &&($('#conceptocc').val()!='') ){
        fnCuentasGuardar();
         $('#messageError').hide();
    }else{
        //muestraModalGeneral(4, titulo,"Seleccione al menos un proveedor");
        $("#messageError").fadeIn("slow");
    }
  });


});
var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
var url ="modelo/SelectSupplierModelo.php"

// Funcion que muestra los datos en GRID considerando los diferentes filtros de informacion
function fnMostrarDatos(){
    //Opcion para operacion
    dataObj = { 
        proceso: 'mostrarProveedores',
        rfc:$('#rfc').val(),
        regimen: $("#regimen").val(),
        codigo: $("#codigo").val(),
        descripcion: $("#txtDescripcion").val(),
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

            fnLimpiarTabla('tablaSupp', 'datosSupp');

            columnasNombres = '';
            columnasNombres += "[";
            //columnasNombres += "{ name: 'checkProv', type: 'bool'},";
            columnasNombres += "{ name: 'idSupp', type: 'string' },";
            columnasNombres += "{ name: 'nombre', type: 'string'},";
            columnasNombres += "{ name: 'tipoPersona', type: 'string'},";
            //tipo persona
            columnasNombres += "{ name: 'rfc', type: 'string'},";
            columnasNombres += "{ name: 'nombretipo', type: 'string'},";
            columnasNombres += "{ name: 'estatus', type: 'string'},";
            /*columnasNombres += "{ name: 'ad4', type: 'string' },";
            columnasNombres += "{ name: 'ad3', type: 'string' },";
            columnasNombres += "{ name: 'ad2', type: 'string' },";
            columnasNombres += "{ name: 'ad1', type: 'string'},";
            columnasNombres += "{ name: 'ad5', type: 'string' },";*/
            //columnasNombres += "{ name: 'email', type: 'string'},";
           // columnasNombres += "{ name: 'desdeSupp', type: 'string' },";
            // columnasNombres += "{ name: 'ad6', type: 'string' },";
            // columnasNombres += "{ name: 'ad6', type: 'string' },";
            //columnasNombres += "{ name: 'Cuentas', type: 'string' },";
            //columnasNombres += "{ name: 'fecha', type: 'string' },";
            columnasNombres += "{ name: 'ver', type: 'string' },";
            columnasNombres += "{ name: 'Modificar', type: 'string' },";
            //columnasNombres += "{ name: 'Eliminar', type: 'string' },";
            columnasNombres += "{ name: 'id', type: 'string' },";
            columnasNombres += "]";

            //Columnas para el GRID
            columnasNombresGrid = '';
            columnasNombresGrid += "[";
            // columnasNombresGrid += " { text: '', datafield: 'checkProv', width: '4%', cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
            columnasNombresGrid += " { text: 'Código',datafield: 'idSupp', width: '8%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Nombre/Razón Social',datafield: 'nombre', width: '32%', align: 'left', hidden: false, cellsalign: 'left' },";
            columnasNombresGrid += " { text: 'Tipo Persona',datafield: 'tipoPersona', width: '8%', align: 'center',hidden: false,cellsalign: 'center' },";
            
            //tipo persona
            columnasNombresGrid += " { text: 'RFC',datafield: 'rfc', width: '15%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Tipo Proveedor',datafield: 'nombretipo', width: '15%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Estatus', datafield: 'estatus', width: '8%', align: 'center',hidden: false,cellsalign: 'center' },";
            //curp
            /*columnasNombresGrid += " { text: 'Estado', datafield: 'ad4', width: '8%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Municipio', datafield: 'ad3', width: '8%', align: 'center',hidden: false,cellsalign: 'center' },"
            //columnasNombresGrid += " { text: 'Ciudad', datafield: 'ad3', width: '8%', align: 'center',hidden: false,cellsalign: 'center' },";
            
            columnasNombresGrid += " { text: 'Colonia',datafield: 'ad2', width: '8%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Calle',  datafield: 'ad1', width: '8%', align: 'center',hidden: false,cellsalign: 'center' },";
            
            columnasNombresGrid += " { text: 'C.P',datafield: 'ad5', width: '8%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Correo', datafield: 'email', width: '15%', cellsalign: 'center', align: 'center',hidden: false },"; */
            //columnasNombresGrid += " { text: 'País',datafield: 'ad6', width: '8%', align: 'center',hidden: false,cellsalign: 'center' },";

           // columnasNombresGrid += " { text: 'Proveedor desde',datafield: 'desdeSupp', width: '8%', align: 'center',hidden: false,cellsalign: 'center' },";
           // columnasNombresGrid += " { text: '',datafield: 'Cuentas', width: '8%', align: 'center',hidden: false,cellsalign: 'center' },";
            //tel
            //email
            // columnasNombresGrid += " { text: 'Fecha captura',datafield: 'fecha', width: '7%', align: 'center',hidden: true,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Ver',datafield: 'ver', width: '7%', align: 'center',hidden: false,cellsalign: 'center' },"; 
            columnasNombresGrid += " { text: 'Modificar',datafield: 'Modificar', width: '7%', align: 'center',hidden: false,cellsalign: 'center' },";
           
           // columnasNombresGrid += " { text: '',datafield: 'Eliminar', width: '7%', align: 'center',hidden: false,cellsalign: 'center' },";
            //columnasNombresGrid += " { text: '',datafield: 'id', width: '8%', align: 'center',hidden: true,cellsalign: 'center' },";
       
            columnasNombresGrid += "]";

            var columnasExcel = [0,1,2,3,4,5];
            var columnasVisuales = [0,1,2,3,4,5,6,7];
            nombreExcel = "ReporteProveedores";

            fnAgregarGrid_Detalle(data.contenido.datosProveedor, columnasNombres, columnasNombresGrid, 'datosSupp', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);
            ocultaCargandoGeneral();
        }
    })
    .fail(function(result) {
         muestraModalGeneral(4, titulo,"Hubo un error al mostrar los datos de proveedores"); 
        console.log("ERROR");
        console.log( result );
    });
}

//'tablaSupp', 'datosSupp'
$(document).on('cellselect', '#tablaSupp > #datosSupp', function(event) {
    solicitudEnlace = event.args.datafield;
     fila = event.args.rowindex;
    if (solicitudEnlace == 'Modificar') {
            
            enlace = $('#tablaSupp > #datosSupp').jqxGrid('getcellvalue', fila, 'Modificar');
       
            href = $('<div>').append(enlace).find('a').prop('id');
            console.log(id);
            console.log(enlace," href", href);

            fnModificar(href);
          
         
    }else if(solicitudEnlace == 'Eliminar'){
            fila = event.args.rowindex;
            enlace = $('#tablaSupp > #datosSupp').jqxGrid('getcellvalue', fila, 'Eliminar');
            href = $('<div>').append(enlace).find('a').prop('id');
          
            muestraModalGeneralConfirmacion(3, titulo, '¿Desea eliminar el proveedor?', '', 'fnEliminarProv('+"'"+href+"'"+')');
             
    } else if(solicitudEnlace == 'ver'){
            enlace = $('#tablaSupp > #datosSupp').jqxGrid('getcellvalue', fila, 'ver');
            href = $('<div>').append(enlace).find('a').prop('id');
            fnModificar(href,1);
          
           // fnCuentasProv(href);
             
    }
});

$(document).on('cellbeginedit', '#datosSupp', function(event) {
    $(this).jqxGrid('setcolumnproperty', 'idSupp', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'nombre', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'email', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'ad1', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'ad2', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'ad3', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'ad4', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'ad5', 'editable', false);
    
    $(this).jqxGrid('setcolumnproperty', 'ad6', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'desdeSupp', 'editable', false);
       $(this).jqxGrid('setcolumnproperty', 'Cuentas', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'Modificar', 'editable', false);
    $(this).jqxGrid('setcolumnproperty', 'Eliminar', 'editable', false);
     $(this).jqxGrid('setcolumnproperty', 'ver', 'editable', false);
});


function fnModificar(id,mod=0){

    var form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", "Suppliers.php");
    form.setAttribute("target", "_self");

    var hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "idSupp");
    hiddenField.setAttribute("value", id);
    form.appendChild(hiddenField);

     var hiddenField2 = document.createElement("input");
    hiddenField2.setAttribute("type", "hidden");
    hiddenField2.setAttribute("name", "mod");
    hiddenField2.setAttribute("value", mod);
    form.appendChild(hiddenField2);

    document.body.appendChild(form);
    //window.open('', '_self');
    form.submit();
    //window.open(,"_self");
}

function fnCuentasProv(id){
   console.log(id);
    var form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", "ABCCuentasxProveedor.php");
    form.setAttribute("target", "_self");

    var hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "idSupp");
    hiddenField.setAttribute("value", id);
    form.appendChild(hiddenField);

    document.body.appendChild(form);
    //window.open('', '_self');
    form.submit();
    //window.open(,"_self");
}


function fnEliminarProv(id){

    dataObj = { 
            proceso: 'eliminarProv',
           idSupp: id
          };
      // 
    muestraCargandoGeneral();
    $.ajax({
          method: "POST",
          dataType:"json",
          url: url,
          data:dataObj
      })
    .done(function( data ) {
        if(data.result){
       

             muestraModalGeneral(4, titulo,data.contenido); 
            
            fnMostrarDatos();
            ocultaCargandoGeneral();
        }else{
            ocultaCargandoGeneral();
        }
    })
    .fail(function(result) {
        console.log("ERROR");
        console.log( result );
        ocultaCargandoGeneral();
    });
}

function fnChecarSeleccionadosProv(){
   
    var arrayId=[];
    var  contadorSelecc=0;
      var griddata = $('#tablaSupp > #datosSupp').jqxGrid('getdatainformation');
        for (var i = 0; i < griddata.rowscount; i++) {
           // enlace = $('#tablaSupp > #datosSupp').jqxGrid('getcellvalue', fila, 'Modificar');
            provSel = $('#tablaSupp > #datosSupp').jqxGrid('getcellvalue', i, 'checkProv');
            if(provSel==true){
                id = $('#tablaSupp > #datosSupp').jqxGrid('getcellvalue', i, 'id');
                
                arrayId.push(id); //0
                contadorSelecc++;
                
            }
              
        }

   return arrayId;//solicitudes;
}


// function  fnCuentasGuardar(){
//  ids=fnChecarSeleccionadosProv();
//  if(ids.length>0){
//   dataObj = { 
//             proceso: 'guardarCC',
//             ids: ids,
//             cuenta:$("#cuentasCon").val(),
//             diot:$("#diotSel").val(),
//             concepto: $('#conceptocc').val()
         
//           };
//       // 
//     muestraCargandoGeneral();
//     $.ajax({
//           method: "POST",
//           dataType:"json",
//           url: url,
//           data:dataObj
//       })
//     .done(function( data ) {
//         if(data.result){
       
//            $('#ModalCuentaCon').modal('hide');


//            muestraModalGeneral(4, titulo,data.contenido); 
     
//             ocultaCargandoGeneral();
//         }else{
//             ocultaCargandoGeneral();
//         }
//     })
//     .fail(function(result) {
//         console.log("ERROR");
//         console.log( result );
//         ocultaCargandoGeneral();
//     });
//     }else{

//            muestraModalGeneral(4, titulo,"Seleccione al menos un proveedor");

//     }
// }
$(document).ready(function(){

    //fnMostrarDatos();
});
