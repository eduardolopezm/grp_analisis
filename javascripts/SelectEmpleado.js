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
        window.open("ABC_Empleados.php", "_self");
    });
 $('#btnCuenta').click(function(){
    fnCuentas();
 });

 // $('#btnSaveCC').click(function(){
    
 // });

 $('#filtrar').click(function(){
     fnMostrarDatos();
 });

  $('#btnSaveCC').click(function(){

if( ($('#cuentasCon').val()!=0) &&($('#diotSel').val()!=0 ) &&($('#conceptocc').val()!='') ){
        fnCuentasGuardar();
         $('#messageError').hide();
    }else{
        //muestraModalGeneral(4, titulo,"Seleccione al menos un empleado");
        $("#messageError").fadeIn("slow");
    }

  });


});
var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
var url ="modelo/SelectEmpleado_Modelo.php"
function fnMostrarDatos(){
    console.log("mostrarEmpleados");

    //Opcion para operacion
    dataObj = { 
            proceso: 'mostrarEmpleados',
            ur:$('#selectUnidadNegocio').val(),
            ue:$('#selectUnidadEjecutora').val(),
            claveempleado:$('#claveempleado').val(),
            nombrecompleto:$('#nombreCompleto').val(),
            nombre:$('#nombre').val(),
            apPat:$('#apPat').val(),
            apMat:$('#apMat').val(),
            rfc:$('#rfc').val(),
            curp:$('#curp').val(),
            estatus:$('#estatus').val()
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

            fnLimpiarTabla('tablaEmp', 'datosEmp');

            columnasNombres = '';
            columnasNombres += "[";
            columnasNombres += "{ name: 'id', type: 'string' },";
            //columnasNombres += "{ name: 'checkEmpl', type: 'bool'},";
            columnasNombres += "{ name: 'idEmp', type: 'string' },";
            columnasNombres += "{ name: 'ur', type: 'string'},";
            columnasNombres += "{ name: 'ue', type: 'string'},";
            columnasNombres += "{ name: 'claveempleado', type: 'string'},";
            columnasNombres += "{ name: 'nombre', type: 'string'},";
            columnasNombres += "{ name: 'rfc', type: 'string'},";
            columnasNombres += "{ name: 'curp', type: 'string'},";
            columnasNombres += "{ name: 'estatus', type: 'string'},";
            columnasNombres += "{ name: 'ver', type: 'string' },";
            columnasNombres += "{ name: 'Modificar', type: 'string' },";
            //columnasNombres += "{ name: 'Eliminar', type: 'string' },";

         
            columnasNombres += "]";
            //Columnas para el GRID
            columnasNombresGrid = '';
            columnasNombresGrid += "[";
            columnasNombresGrid += " { text: '', datafield: 'id', width: '8%', align: 'center',hidden: true,cellsalign: 'center' },";
           	// columnasNombresGrid += " { text: '', datafield: 'checkEmpl', width: '4%', cellsalign: 'center', align: 'center',columntype: 'checkbox',hidden: false },";
           	//columnasNombresGrid += " { text: 'Código', datafield: 'idEmp', width: '8%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'UR', datafield: 'ur', width: '5%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'UE', datafield: 'ue', width: '5%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Clave de empleado', datafield: 'claveempleado', width: '10%', align: 'center',hidden: false,cellsalign: 'center' },";
			columnasNombresGrid += " { text: 'Nombre', datafield: 'nombre', width: '29%', align: 'center',hidden: false,cellsalign: 'left' },";
			columnasNombresGrid += " { text: 'RFC', datafield: 'rfc', width: '15%', align: 'center',hidden: false,cellsalign: 'center' },";
			columnasNombresGrid += " { text: 'CURP', datafield: 'curp', width: '15%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Estatus', datafield: 'estatus', width: '7%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Ver', datafield: 'ver', width: '7%', align: 'center',hidden: false,cellsalign: 'center' },"; 
            columnasNombresGrid += " { text: 'Modificar', datafield: 'Modificar', width: '7%', align: 'center',hidden: false,cellsalign: 'center' },";
           	//columnasNombresGrid += " { text: 'Eliminar',datafield: 'Eliminar', width: '7%', align: 'center',hidden: false,cellsalign: 'center' },";
       

            
           
            columnasNombresGrid += "]";

            var columnasExcel =    [1,2,3,4,5,6];
            var columnasVisuales = [1,2,3,4,5,6,7,8,9];
            nombreExcel = data.contenido.nombreExcel;
            nombreExcel = tituloExcel;

            fnAgregarGrid_Detalle(data.contenido.datosEmpleado, columnasNombres, columnasNombresGrid, 'datosEmp', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);
           ocultaCargandoGeneral();

        }
    })
    .fail(function(result) {
         muestraModalGeneral(4, titulo,"Hubo un error al mostrar los datos de empleados"); 
        console.log("ERROR");
        console.log( result );
    });
}





//'tablaEmp', 'datosEmp'
$(document).on('cellselect', '#tablaEmp > #datosEmp', function(event) {
    solicitudEnlace = event.args.datafield;
     fila = event.args.rowindex;
    if (solicitudEnlace == 'Modificar') {
            
            enlace = $('#tablaEmp > #datosEmp').jqxGrid('getcellvalue', fila, 'Modificar');
       
            href = $('<div>').append(enlace).find('u').prop('id');
            fnModificar(href);
         
        
    }else if(solicitudEnlace == 'Eliminar'){
            fila = event.args.rowindex;
            enlace = $('#tablaEmp > #datosEmp').jqxGrid('getcellvalue', fila, 'Eliminar');
            href = $('<div>').append(enlace).find('u').prop('id');
          
            muestraModalGeneralConfirmacion(3, titulo, '¿Desea eliminar el empleado?', '', 'fnEliminarEmp('+"'"+href+"'"+')');
             
    } else if(solicitudEnlace == 'ver'){
            enlace = $('#tablaEmp > #datosEmp').jqxGrid('getcellvalue', fila, 'ver');
            href = $('<div>').append(enlace).find('u').prop('id');
            fnModificar(href,1);
          
           // fnCuentasProv(href);
             
    }
});



$(document).on('cellbeginedit', '#datosEmp', function(event) {
    $(this).jqxGrid('setcolumnproperty', 'idEmp', 'editable', false);
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
    form.setAttribute("action", "ABC_Empleados.php");
    form.setAttribute("target", "_self");

    var hiddenField = document.createElement("input");
    hiddenField.setAttribute("type", "hidden");
    hiddenField.setAttribute("name", "idEmp");
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
    hiddenField.setAttribute("name", "idEmp");
    hiddenField.setAttribute("value", id);
    form.appendChild(hiddenField);

    document.body.appendChild(form);
    //window.open('', '_self');
    form.submit();
    //window.open(,"_self");
}


function  fnEliminarEmp(id){

    dataObj = { 
            proceso: 'eliminarEmp',
           idEmp: id
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

function  fnCuentas(){
	/*
 ids=fnChecarSeleccionadosEmp();
 if(ids.length>0){
  dataObj = { 
            proceso: 'cuentasContables'
         
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
       
        
        cuentas=data.contenido.cuentas;
        diot=data.contenido.diot;
        // html='<div id="parteSelec" class=" row clearfix text-left">'+
        //     ' <div class="col-xs-12 col-md-12">  <div class="col-xs-3 col-md-3" style="vertical-align: middle;"> '+
        //     '<span><label>Cuenta contable: </label></span>'+
        //     '</div>' +
        //     '<div class="col-xs-9 col-md-9"><select id="cuentasCon" required></select></div><br> </div>'+ 


        //     '<div class="col-xs-12 col-md-12"> <br>  <div class="col-xs-3 col-md-3" style="vertical-align: middle;"> '+
        //     '<span><label>Tipo de operación DIOT: </label></span>'+
        //     '</div>' +
        //     '<div class="col-xs-9 col-md-9"><select id="diotSel" required></select></div> <br></div> <br> <br>'+ 
        //     '<div class="col-xs-12 col-md-12 text-left ml5"> <br> <component-text-label label="Concepto a desplegar :" id="conceptocc" name="conceptocc"  placeholder="Concepto a desplegar" title="conceptocc" maxlength="70" value=""></component-text-label> </div><br>'+
        //     //'<div class="col-xs-12 col-md-12 text-center"><br> <button class="btn botonVerde">Guardar </button> </div>'
        //     '</div>';
        //  //muestraModalGeneral(4, titulo,html); 
        

        optionsCuentas=[{label:' Seleccionar...', title: ' Seleccionar...', value:'0'}];
        
        $.each(cuentas, function(index, val) { optionsCuentas.push({ label:val.texto, title: val.texto, value:val.value }); });
        

          $('#'+"cuentasCon").multiselect({
                enableFiltering: true,
                filterBehavior: 'text',
                enableCaseInsensitiveFiltering: true,
                buttonWidth: '100%',
                numberDisplayed: 1,
                includeSelectAllOption: true
            });
            $('#'+"cuentasCon").multiselect('dataprovider',optionsCuentas);
            
            $('.multiselect-container').css({ 'max-height': "220px" });
            $('.multiselect-container').css({ 'overflow-y': "scroll" });



        optionsDiot=[{label:' Seleccionar...', title: ' Seleccionar...', value:'0'}];
        
        $.each(diot, function(index, val) { optionsDiot.push({ label:val.texto, title: val.texto, value:val.value }); });
        

          $('#'+"diotSel").multiselect({
                enableFiltering: true,
                filterBehavior: 'text',
                enableCaseInsensitiveFiltering: true,
                buttonWidth: '100%',
                numberDisplayed: 1,
                includeSelectAllOption: true
            });
            $('#'+"diotSel").multiselect('dataprovider',optionsDiot);
            
            $('.multiselect-container').css({ 'max-height': "220px" });
            $('.multiselect-container').css({ 'overflow-y': "scroll" });

    
            //fnEjecutarVueGeneral('parteSelec');
             
            $( '<button class="btn  botonVerde glyphicon glyphicon-save" id="btnGuardar">Guardar</button>' ).insertBefore( "#btnCerrarModalGeneral" );
        $('#ModalCuentaCon').draggable();  
        $('#ModalCuentaCon_Titulo').empty();
        $('#ModalCuentaCon_Titulo').append(titulo);
        $('#ModalCuentaCon').modal('show');
            //fnMostrarDatos();
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
    }else{

           muestraModalGeneral(4, titulo,"Seleccione al menos un empleado");

    }
	*/
}


function fnChecarSeleccionadosEmp(){
   
    var arrayId=[];
    var  contadorSelecc=0;
      var griddata = $('#tablaEmp > #datosEmp').jqxGrid('getdatainformation');
        for (var i = 0; i < griddata.rowscount; i++) {
           // enlace = $('#tablaEmp > #datosEmp').jqxGrid('getcellvalue', fila, 'Modificar');
            empSel = $('#tablaEmp > #datosEmp').jqxGrid('getcellvalue', i, 'checkEmpl');
            if(empSel==true){
                id = $('#tablaEmp > #datosEmp').jqxGrid('getcellvalue', i, 'id');
                
                arrayId.push(id); //0
                contadorSelecc++;
                
            }

              
        }

   return arrayId;//solicitudes;
}


// function  fnCuentasGuardar(){
//  ids=fnChecarSeleccionadosEmp();
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

//            muestraModalGeneral(4, titulo,"Seleccione al menos un empleado");

//     }
// }
$(document).ready(function(){
	fnMostrarDatos();
    fnFormatoSelectGeneral(".estatus");
    $('#nombre').width( $('#selectUnidadNegocio').width() );
});
