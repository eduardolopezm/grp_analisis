/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Arturo Lopez Peña 
 * @version 0.1
 */
var modelo = "modelo/principal.php";
var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';

function fnRegresar() {
    window.open("SelectSupplier.php", "_self");
}





    
    


/*
 * PARA LOS CONVENIOS
 */
$(function(){
    $('#btnSaveConvenio').click(function(){
    
    var convenios = new Object();
    var ue = $("#ueSel").val();
    var tipoConvenio = $("#idConvenioSel").val();
    var descripcion = $("#descripcion").val();
    var estatus = $("#estatus").val();
    var fechaInicio = $("#fechaInicio").val();
    var fechaFin = $("#fechaFin").val();
    var sncc = $("#sncc").val();
    
    if(ue == 0){
        $("#valMsgErrorConvenio").append("Seleccione una unidad ejecutora.");
        $("#valMsgErrorConvenio").show(400).delay(3500);
        //muestraModalGeneral(4, titulo, "Seleccione una unidad ejecutora.");
    }else{
         if(tipoConvenio == 0){
             $("#valMsgErrorConvenio").append("Seleccione un tipo de convenio.");
             $("#valMsgErrorConvenio").show(400).delay(3500);
              //muestraModalGeneral(4, titulo, "Seleccione un tipo de convenio.");
         }else{
             if(descripcion == ""){
                 $("#valMsgErrorConvenio").append("Ingrese una descripción para el convenio.");
                 $("#valMsgErrorConvenio").show(400).delay(3500);
                 //muestraModalGeneral(4, titulo, "Ingrese una descripción para el convenio.");
             }else{
                 if(estatus == 0){
                     $("#valMsgErrorConvenio").append("Seleccione un estatus.");
                     $("#valMsgErrorConvenio").show(400).delay(3500);
                     //muestraModalGeneral(4, titulo, "Seleccione un estatus.");
                 }else{
                     if(fechaInicio == "" || fechaFin == ""){
                         $("#valMsgErrorConvenio").append("Seleccione un estatus.");
                         $("#valMsgErrorConvenio").show(400).delay(3500);
                         //muestraModalGeneral(4, titulo, "Seleccione una fecha.");
                     }else{
                         if(sncc == ""){
                             $("#valMsgErrorConvenio").append("Ingrese un septimo nivel de cuenta para el convenio");
                             $("#valMsgErrorConvenio").show(400).delay(3500);
                         }else{
                                var datosForm = String($("#conveniosAlta").serialize()).split("&");
                                for (var i in datosForm) {
                                    var propValue = datosForm[i].split("=");
                                    var aux;
                                    if (propValue[0] == "ramo" || propValue[0] == "ur") {
                                        aux = String(propValue[1]).split("-");
                                        convenios[propValue[0]] = aux[0];
                                    } else {
                                        convenios[propValue[0]] = propValue[1];
                                    }
                                }
                                convenios["tipo"] = 3;
                                $.ajax({
                                    url: modelo,
                                    type: "post",
                                    dataType: "json",
                                    data: convenios
                                })
                                        .done(function (data) {
                                            if (data.result) {
                                                info = data.contenido;
                                                //muestraModalGeneral(4, titulo, data.Mensaje);
                                                $("#valMsgSuccessConvenio").append(data.Mensaje);
                                                $("#valMsgSuccessConvenio").show(400).delay(3500);
                                                fnMostrarDatosConvenio();
                                                //$("#btnGuardarSupp").attr('onclick', 'fnModificar()');
                                            }
                                        })
                                        .fail(function (result) {
                                            console.log("ERROR");
                                            console.log(result);
                                            //muestraModalGeneral(4, titulo, "Hubo un error al guardar los datos del convenio");
                                            $("#valMsgErrorConvenio").append("Hubo un error al guardar los datos del convenio");
                                            $("#valMsgErrorConvenio").show(400).delay(3500);
                                        });
                         }
                 }
             }
         }    
    }
    }
    
});
    
               

//function fnGuardarDos(){

$('#btnSaveTipoConvenio').click(function(){
    var tipoCon = {};
    
    var tipoConvenio = $("tipo_convenio").val();
    var descripcion = $("#descripcion").val();
    
    if(descripcion == ""){
        //muestraModalGeneral(4, titulo, "Es necesario ingresar una descripción para el convenio");
        $("#valMsgErrorTipoConvenio").append("Es necesario ingresar una descripción para el convenio");
        $("#valMsgErrorTipoConvenio").show(400).delay(3500);
    }else{
        if(tipoConvenio == ""){
            $("#valMsgErrorTipoConvenio").append("Es necesario ingresar un tipo de convenio");
            $("#valMsgErrorTipoConvenio").show(400).delay(3500);
        }else{
            var datosForm = String($("#tipoConvenioAlta").serialize()).split("&");
            
            for (var i in datosForm) {
                var propValue = datosForm[i].split("=");
                tipoCon[propValue[0]] = propValue[1];
            }
            tipoCon["tipo"] = 1;
            
            $.ajax({
                url: 'modelo/principal.php',
                type: "post",
                dataType: "json",
                data: tipoCon
                })
                        .done(function (data) {
                            if (data.result) {
                                info = data.contenido;
                                //muestraModalGeneral(4, titulo, data.Mensaje);
                                //ocultaCargandoGeneral();
                                $("#valMsgSuccessTipoConvenio").append(data.Mensaje);
                                $("#valMsgSuccessTipoConvenio").show(400).delay(3500);
                                fnMostrarDatosTipoConvenio();
                            }
                        })
                        .fail(function (result) {
                            console.log("ERROR");
                            console.log(result);
                            //muestraModalGeneral(4, titulo, "Hubo un error al guardar los datos del convenio");
                            $("#valMsgErrorTipoConvenio").append("Hubo un error al guardar los datos del convenio");
                            $("#valMsgErrorTipoConvenio").show(400).delay(3500);
                        });
        }
    }
});
    



$('#btnSaveComponentePresupuestal').click(function(){    
    var componentePresupuestal = new Object();
    
    var cp = $("#cp").val();
    var descripcion = $("#descripcion").val();
    
    if(cp == ""){
        //muestraModalGeneral(4, titulo, "Es necesario ingresar el componente presupuestal");
        $("#valMsgErrorComponentePresupuestal").append("Es necesario ingresar el componente presupuestal");
        $("#valMsgErrorComponentePresupuestal").show(400).delay(3500);
    }else{
        if(descripcion == ""){
            //muestraModalGeneral(4, titulo, "Es necesario ingresar una descripción para el convenio");
            $("#valMsgErrorComponentePresupuestal").append("Es necesario ingresar una descripción para el convenio");
            $("#valMsgErrorComponentePresupuestal").show(400).delay(3500);
        }else{
            var datosForm = String($("#componentePresupuestalAlta").serialize()).split("&");
            for (var i in datosForm) {
                var propValue = datosForm[i].split("=");
                componentePresupuestal[propValue[0]] = propValue[1];
            }
            componentePresupuestal["tipo"] = 2;
            $.ajax({
                url: modelo,
                type: "post",
                dataType: "json",
                data: componentePresupuestal
            })
                    .done(function (data) {
                        if (data.result) {
                            info = data.contenido;
                            //muestraModalGeneral(4, titulo, data.Mensaje);
                            //ocultaCargandoGeneral();
                            $("#valMsgSuccessComponentePresupuestal").append(data.Mensaje);
                            $("#valMsgSuccessComponentePresupuestal").show(400).delay(3500);
                            fnMostrarDatosComponentePresupuestal();
                            //$("#btnGuardarSupp").attr('onclick', 'fnModificar()');
                        }
                    })
                    .fail(function (result) {
                        console.log("ERROR");
                        console.log(result);
                        //muestraModalGeneral(4, titulo, "Hubo un error al guardar el componente presupuestal");
                        $("#valMsgErrorComponentePresupuestal").append("Hubo un error al guardar el componente presupuestal");
                        $("#valMsgErrorComponentePresupuestal").show(400).delay(3500);
                    });
        }
        
    }
});
    
    
    
    
    $('#btnConvenios').click(function () {
//    $('#ModalCuentaBank').draggable();
    $('#ModalConvenio_Titulo').empty();
    $('#ModalConvenio_Titulo').append("Agregar Convenio");
    $('#ModalConvenio').modal('show');
    $("#valMsgConvenio").empty();
});

/*
 * PARA LOS COMPONENTES PRESUP
 */
$('#btnComponentesPresup').click(function () {
    $('#ModalComponentesPresup').draggable();
    $('#ModalComponentesPresup_Titulo').empty();
    $('#ModalComponentesPresup_Titulo').append("Agregar Componente Presupuestal");
    $('#ModalComponentesPresup').modal('show');
    $("#valMsgCompPresup").empty();
});


/*
 * PARA LOS TIPO CONVENIOS
 */
$('#btnTipoConvenio').click(function () {
    
    $('#ModalTipoConvenio').draggable();
    $('#ModalTipoConvenio_Titulo').empty();
    $('#ModalTipoConvenio_Titulo').append("Agregar Tipo de Convenio");
    $('#ModalTipoConvenio').modal('show');
    $("#valMsgTipoConvenio").empty();
});


})







// Funcion que muestra los datos en GRID considerando los diferentes filtros de informacion
function fnMostrarDatosTipoConvenio(){
    //Opcion para operacion
    dataObj = { 
        tipo: '1.1'
    };

    //Obtener datos de las bahias
    $.ajax({
          method: "POST",
          dataType:"json",
          url:'modelo/principal.php',
          data:dataObj
      })
    .done(function( data ) {
        if(data.result){

            fnLimpiarTabla('tablaTipoConvenios', 'datosTipoConvenios');

            columnasNombres = '';
            columnasNombres += "[";
            //columnasNombres += "{ name: 'checkProv', type: 'bool'},";
            columnasNombres += "{ name: 'Tipo', type: 'string' },";
            columnasNombres += "{ name: 'Descripcion', type: 'string'},";
            columnasNombres += "{ name: 'Modificar', type: 'string'},";
            columnasNombres += "{ name: 'Eliminar', type: 'string'},";
            columnasNombres += "]";

            //Columnas para el GRID
            columnasNombresGrid = '';
            columnasNombresGrid += "[";
            
            columnasNombresGrid += " { text: 'Tipo',datafield: 'Tipo', width: '8%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Descripción',datafield: 'Descripcion', width: '32%', align: 'center', hidden: false, cellsalign: 'left' },";
            
            
            columnasNombresGrid += " { text: 'Modificar',datafield: 'Modificar', width: '7%', align: 'center',hidden: false,cellsalign: 'center' },"; 
            columnasNombresGrid += " { text: 'Eliminar',datafield: 'Eliminar', width: '7%', align: 'center',hidden: false,cellsalign: 'center' },";
       
            columnasNombresGrid += "]";

            var columnasExcel = [0,1,2,3,4];
            var columnasVisuales = [0,1,2,3,4];
            nombreExcel = "ReporteTipoConvenios";

            fnAgregarGrid_Detalle(data.contenido.datos, columnasNombres, columnasNombresGrid, 'datosTipoConvenios', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);
            ocultaCargandoGeneral();
        }
    })
    .fail(function(result) {
         muestraModalGeneral(4, titulo,"Hubo un error al mostrar los datos de los tipos de convenios"); 
        console.log("ERROR");
        console.log( result );
    });
}



function fnMostrarDatosConvenio(){
    //Opcion para operacion
    dataObj = { 
        tipo: '3.1'
    };

    //Obtener datos de las bahias
    $.ajax({
          method: "POST",
          dataType:"json",
          url:'modelo/principal.php',
          data:dataObj
      })
    .done(function( data ) {
        if(data.result){

            fnLimpiarTabla('tablaConvenios', 'datosConvenios');

            columnasNombres = '';
            columnasNombres += "[";
            //columnasNombres += "{ name: 'checkProv', type: 'bool'},";
            columnasNombres += "{ name: 'Anio', type: 'integer' },";
            columnasNombres += "{ name: 'Ramo', type: 'string'},";
            columnasNombres += "{ name: 'UR', type: 'string' },";
            columnasNombres += "{ name: 'UE', type: 'string'},";
            columnasNombres += "{ name: 'Clave', type: 'string' },";
            columnasNombres += "{ name: 'SNCC', type: 'string'},"
            columnasNombres += "{ name: 'PP', type: 'string'},";
            columnasNombres += "{ name: 'CP', type: 'string' },";
            columnasNombres += "{ name: 'Descripcion', type: 'string'},";
            columnasNombres += "{ name: 'Tipo Convenio', type: 'string' },";
            columnasNombres += "{ name: 'Desde', type: 'string'},";
            columnasNombres += "{ name: 'Hasta', type: 'string' },";
            columnasNombres += "{ name: 'Estatus', type: 'integer'},";
            columnasNombres += "]";

            //Columnas para el GRID
            columnasNombresGrid = '';
            columnasNombresGrid += "[";
            columnasNombresGrid += " { text: 'Año',datafield: 'Anio', width: '5%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Ramo',datafield: 'Ramo', width: '5%', align: 'center', hidden: false, cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'UR',datafield: 'UR', width: '5%', align: 'center',hidden: false,cellsalign: 'center' },"; 
            columnasNombresGrid += " { text: 'UE',datafield: 'UE', width: '5%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Clave',datafield: 'Clave', width: '16%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'SNCC', datafield: 'SNCC', width: '5%', align: 'center', hidden: false, cellsalign: 'center'},";
            columnasNombresGrid += " { text: 'PP',datafield: 'PP', width: '10%', align: 'center', hidden: false, cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'CP',datafield: 'CP', width: '5%', align: 'center',hidden: false,cellsalign: 'center' },"; 
            columnasNombresGrid += " { text: 'Descripción',datafield: 'Descripcion', width: '18%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Tipo Convenio',datafield: 'Tipo Convenio', width: '7%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Desde',datafield: 'Desde', width: '7%', align: 'center', hidden: false, cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Hasta',datafield: 'Hasta', width: '7%', align: 'center',hidden: false,cellsalign: 'center' },"; 
            columnasNombresGrid += " { text: 'Estatus',datafield: 'Estatus', width: '5%', align: 'center',hidden: false,cellsalign: 'center' },";
       
            columnasNombresGrid += "]";

            var columnasExcel = [0,1,2,3,4,5,6,7,8,9,10,11];
            var columnasVisuales = [0,1,2,3,4,5,6,7,8,9,10,11];
            nombreExcel = "ReporteConvenios";
            
            console.log(data.contenido.datos);

            fnAgregarGrid_Detalle(data.contenido.datos, columnasNombres, columnasNombresGrid, 'datosConvenios', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);
            ocultaCargandoGeneral();
        }
    })
    .fail(function(result) {
         muestraModalGeneral(4, titulo,"Hubo un error al mostrar los datos de convenios"); 
        console.log("ERROR");
        console.log( result );
    });
}


function fnMostrarDatosComponentePresupuestal(){
    //Opcion para operacion
    dataObj = { 
        tipo: '2.1'
    };

    //Obtener datos de las bahias
    $.ajax({
          method: "POST",
          dataType:"json",
          url:'modelo/principal.php',
          data:dataObj
      })
    .done(function( data ) {
        if(data.result){

            fnLimpiarTabla('tablaComponentePresupuestal', 'datosComponentePresupuestal');

            columnasNombres = '';
            columnasNombres += "[";
            //columnasNombres += "{ name: 'checkProv', type: 'bool'},";
            columnasNombres += "{ name: 'CP', type: 'string' },";
            columnasNombres += "{ name: 'Descripcion', type: 'string'},";
            columnasNombres += "{ name: 'Modificar', type: 'string'},";
            columnasNombres += "{ name: 'Eliminar', type: 'string'},";
            columnasNombres += "]";

            //Columnas para el GRID
            columnasNombresGrid = '';
            columnasNombresGrid += "[";
            
            columnasNombresGrid += " { text: 'CP',datafield: 'CP', width: '15%', align: 'center',hidden: false,cellsalign: 'center' },";
            columnasNombresGrid += " { text: 'Descripción',datafield: 'Descripcion', width: '60%', align: 'center', hidden: false, cellsalign: 'left' },";
            
            
            columnasNombresGrid += " { text: 'Modificar',datafield: 'Modificar', width: '10%', align: 'center',hidden: false,cellsalign: 'center' },"; 
            columnasNombresGrid += " { text: 'Eliminar',datafield: 'Eliminar', width: '10%', align: 'center',hidden: false,cellsalign: 'center' },";
       
            columnasNombresGrid += "]";

            var columnasExcel = [0,1];
            var columnasVisuales = [0,1];
            nombreExcel = "ReporteComponentePresupuestal";

            fnAgregarGrid_Detalle(data.contenido.datos, columnasNombres, columnasNombresGrid, 'datosComponentePresupuestal', ' ', 1, columnasExcel, false, true, '', columnasVisuales, nombreExcel);
            ocultaCargandoGeneral();
        }
    })
    .fail(function(result) {
         muestraModalGeneral(4, titulo,"Hubo un error al mostrar los datos de los componentes Presupuestales"); 
        console.log("ERROR");
        console.log( result );
    });
}




