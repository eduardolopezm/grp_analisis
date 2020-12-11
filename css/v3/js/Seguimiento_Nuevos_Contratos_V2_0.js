/**
 * @name        Seguimiento_Nuevos_Contratos_V2_0.js
 * @version     2.0
 * @author      Uber Crisostomo Garcia
 * Fecha creación: 03-07-2017
 *
 * Descripción: archivo utilizado para control del frontend del panel
 */
var msgConexion = 'Verifique su conexión a internet o contacte a un administrador';
var arrayStatus = Array();
var pParcialidades = Array();
//esta variable sera utilizada para obtener los datos del combo de unidades 
//de negcio cuando se este en la pantalla principal
var ur = 0; //0-> obtener todas las unidades de negocio 
//asignar fecha por defecto en los campos fecha
var today = new Date();
var dd = (today.getDate() < 10 ? '0' : '') + today.getDate();
var mm = ((today.getMonth() + 1) < 10 ? '0' : '') + (today.getMonth() + 1);
// 1970, 1971, ... 2015, 2016, ...
// 
//mostrar mensajes del log
var debug = true;
var yyyy = today.getFullYear();
if (debug) {
   console.log(today);
   console.log(dd);
   console.log(mm);
   console.log(yyyy);
}
var fechaIni = dd + '-' + mm + '-' + yyyy;
var fechaFin = dd + '-' + mm + '-' + yyyy;
$(document).ready(function() {
   if (debug) {
      console.log('documento listo');
   }
   //cuando de inicie una peticion ajax bloquear la pagina
   $(document).ajaxStart($.blockUI({
      message: '<i class="fa fa-cog fa-spin fa-3x fa-fw"></i><span class="sr-only">Cargando...</span>'
   })).ajaxStop($.unblockUI);
   //activar datepicker en los campos de fechas
   $.datepicker.setDefaults($.datepicker.regional["es"]);
   $('#datePickerFI, #datePickerFF').datepicker({
      autoclose: true,
      format: 'dd-mm-yyyy'
   }).on('changeDate', function(ev) {
      //$('#fMyModal').formValidation('revalidateField', 'date'); //validar campo
      //console.log(date);
   });
});
var comments_table = files_table = bill_table = '';
comments_table = $('#comments-table').DataTable({
   scrollY: 200,
   scrollX: true,
   language: {
      url: 'javascripts/DataTables/Spanish.json'
   },
   ordering: false
});
files_table = $('#files-table').DataTable({
   scrollY: 200,
   scrollX: true,
   language: {
      url: 'javascripts/DataTables/Spanish.json'
   },
   ordering: false
});
bill_table = $('#bills-table').DataTable({
   scrollY: 200,
   scrollX: true,
   language: {
      url: 'javascripts/DataTables/Spanish.json'
   },
   ordering: false
});
$(window).load(function() {
   if (debug) {
      console.log('pagina cargada');
      console.log('version: 060720171347');
   }
   //obtener datos de los combos
   //llenar los combos de la pantalla principal
   getUnidadesR(0);
   getUnidadesNegocio(0, ur);
   getTipoContratos(0);
   getClasificacionContratos(0);
   getStatus(0);

   
   //funcionalidad para seleccionar o deseleccionar todos los estatus
   $(document).on('click', '#todos_estatus', function() {
      if ($(this).is(':checked')) {
         $(this).parent().parent().find('input:checkbox').prop('checked', true);
      } else {
         $(this).parent().parent().find('input:checkbox').prop('checked', false);
      }
   });
   $(document).on('change', '.item-option', function() {
      $(this).parent().parent().find('#todos_estatus').prop('checked', false);
   });

   //si el id de unidades responsables cambia
   $('#IdUR').on('change', function() {
      ur = this.value;
      if (ur != -1) {
         getUnidadesNegocio(0, ur);
      } else {
         getUnidadesNegocio(0, 0);
      }
   });

   //alta de contratos
   $('#IdURContrato').on('change', function() {
      urc = this.value;
      if (urc != -1) {
         getUnidadesNegocio(1, urc);
      } else {
         getUnidadesNegocio(1, 0);
      }
   });

$('#btnUploadCancel').click(function(event){
   event.preventDefault();
   $('#upload-avatar').modal('toggle');
});



   //visualizar parcialidades
   $('#parcialidades').on('change',function(){
      parcialidad = this.value;
      if(debug)console.log(parcialidad);
      if(parcialidad == 1){
         //mostrar campo de numero de parcialidades
         $('#noParcialidad').val(0);
         $('#parcialidadesc').show();
         $("#tableParcialidades").show();
      }else{
         $('#noParcialidad').val(0);
         $('#parcialidadesc').hide();
         $('#noParcialidades').val('');
         $("#tableParcialidades").hide();
         
      }
   });

   $("#noParcialidades").focusout(function() {
    //tomar el valor del campo para poder agregar todos los campos
    var totalCampos=$("#noParcialidades").val();

    $("#tableParcialidades tbody").remove();
    $("#tableParcialidades").append("<tbody ><tr></tr></tbody>");
     for (var i = 1; i <= totalCampos; i++) {
         $("#tableParcialidades> tbody >tr:last").after('<tr id="row-'+i+'">'
            +'<td>'+i+'</td>'
            +'<td>'
            +'<div class="input-group input-append date" id="dateFRev'+i+'">'
            +'<input type="text" class="form-control" value=""  id="pfechaRev'+i+'" name="pfechaRev'+i+'"  placeholder="DD-MM-YYYY">'
            +'<label class="input-group-addon"><span class="fa fa-calendar open-datetimepicker"></span></label>'
            +'</div>'
            +'</td>'
            +'<td>'
            +'<div class="input-group input-append date" id="dateFVen'+i+'">'
            +'<input type="text" class="form-control" value=""  id="pfechaVen'+i+'" name="pfechaVen'+i+'"  placeholder="DD-MM-YYYY">'
            +'<label class="input-group-addon"><span class="fa fa-calendar open-datetimepicker"></span></label>'
            +'</div>'
            +'</td>'            
            +'<td><select id="ppen'+i+'"><option value="1">SI</option><option value="0">NO</option></select></td>'
            +'<td><select id="ppentipo'+i+'"><option value="1">A</option><option value="2">B</option></select></td>'
            +'<td><select id="pded'+i+'"><option value="1">SI</option><option value="0">NO</option></select></td>'
            +'<td><select id="pdedtipo'+i+'"><option value="1">A</option><option value="2">B</option></select></td>'
            +'<td><input type="text" id="pmonto'+i+'" value="0"></td>'
            +'</tr>');

         
         $.datepicker.setDefaults($.datepicker.regional["es"]);
         $('#dateFRev'+i+', #dateFVen'+i).datepicker({
            autoclose: true,
            format: 'dd-mm-yyyy'
         }).on('changeDate', function(ev) {
         });

      } 
      $("#tableParcialidades").show();

      
     
});
/*
   $('#noParcialidad')
      .focusout(function() {
         console.log('perdi el foco');
      $("#tableParcialidades tbody").remove();
      $("#tableParcialidades").append("<tbody ><tr></tr></tbody>");
      $("#tableParcialidades> tbody >tr:last").after('<tr><td><input id="1" val="hola"></td></tr>');
      $("#tableParcialidades").show();
  });

*/


   /*
    $.datepicker.setDefaults($.datepicker.regional["es"]);
   $('#description-file2').datepicker({
      autoclose: true,
      format: 'MM yyyy',
      viewMode: 'months',
      minViewMode: 'months'
   });

   getUnidadesR(0); //Obtener unicades responsables para filtro principal
*/
   /*
   getTags(0);
   getTags(1);
   
   
   getStatus(1);
   */
});
/**
 * Acciones de botones
 */
$('#btnConsulta').click(function() {
   $('#fprincipal').submit(); //validar formulario de consulta
});
$('#btnNuevoContrato').click(function() {
   $('#myModal').modal('show'); //mostrar modal
   $('#myModalTitle').html('Buscar proveedor');
   document.getElementById("fMyModal").reset();
   //ocultar capa que contiene formulario de alta
   //-limpiar la tabla de proveedores
   //-Mostrar input de busqueda (datos)
   $(".datosContrato").hide();
   $('#tableProveedores').html('');
   $("#tableProveedores").show();
   $("#search").show();
   $("#inputSearch").show();
   $('#btnBackSearchProvider').hide();
   $('#btnBuscarProveedor').show();
   $("#nombreProveedortxt").hide();
   $('#myModalTitle').html('Buscar proveedor');
});
$('#btnBuscarProveedor').click(function() {
   datos = $("#inputSearch").val();
   var parametros = {
      datos: datos
   };
   $("#tableProveedores tbody").remove();
   $("#tableProveedores").append("<tbody ><tr></tr></tbody>");
   $.post("buscar_proveedor_.php", parametros, function(datos, estatus) {
      if (estatus == "success") {
         $(JSON.parse(datos)).each(function(indice, valor) {
            if (valor.typeid == 3) {
               $("#tableProveedores> tbody >tr:last").after("<tr><td>" + valor.suppname + "</td><td>" + valor.taxid + "</td><td>" + valor.curp + "</td><td><i class=\"fa fa-check fa-lg cursor\" onclick=\"seleccionarProveedor(" + valor.supplierid + ",1)\"></i></td></tr>");
            }
         });
         $("#tableProveedores").show();
         $('#myModalTitle').html('Seleccionar Proveedor');
      }
      if (estatus == "error") {
         muestraMensaje('No se pudo obtener la lista de proveedores', 2, 'notificaciones', 5000);
      }
   });
});

$('#btnBackSearchProvider').click(function() {

   $(".datosContrato").hide();
   $("#tableProveedores").show();
   $("#search").show();
   $("#inputSearch").show();
   $('#btnBackSearchProvider').hide();
   $('#btnBuscarProveedor').show();
   $("#nombreProveedortxt").hide();
   $('#myModalTitle').html('Buscar proveedor');
});

/**
 * funciones
 */

function cierraModalSuper(){
   $('#upload-avatar').modal('hide');
}


function seleccionarProveedor(idProveedor, op) {
   //limpiar el formulario antes de cargar los nuevos datos del proveedor
   //y campos por default
   $("#fMyModal")[0].reset();
   Obj = {
         action: 'getSupplier',
         info: {
            idProveedor: idProveedor
         }
      }
      //a continuacion obtengo los datos del proveedor seleccionado
   $('#fMyModal').data('formValidation').resetForm();
   arrayStatus.length = 0;
   $("input[type=checkbox]:checked").each(function() {
      arrayStatus.push($(this).val());
   });
   if (debug) console.log(Obj);
   $.ajax({
      url: "Seguimiento_Nuevos_Contratos_Ajax_V2_0.php",
      method: "POST",
      dataType: "json",
      data: Obj
   }).done(function(response) {
      if (response.succesFlag) {
         //si se encuentran datos del proveedor 
         //ocultar:
         //- la tabla de proveedores
         //- el inputSearch
         //- mostra formulario de captura
         //- en la etiqueta nombreProveedortxt colocar el nombre del proveedor seleccionado y mostrar esta etiqueta
         //- ocultar el boton Buscar proveedor
         //- asignar el id del proveedor al input 
         $("#tableProveedores").hide();
         $("#search").hide();
         $("#inputSearch").hide();
         $(".datosContrato").show();
         //$("#nombreProveedortxt").text(response.content);
         //$("#nombreProveedortxt").show();
         $('#btnBuscarProveedor').hide();
         $("#proveedor").val(idProveedor);
         
         if (op == 1) {
            //para cuando se trata de una captura
            //- mostrar el boton regresar a seleccionar proveedor
            //- cambiar titulo del modal
            //- y resetear la validacion del formulari y los datos
            $('#btnBackSearchProvider').show();
            $('#myModalTitle').html('Agregar contrato a '+ response.content);
            $('#fMyModal').data('formValidation').resetForm();
            $("#fMyModal")[0].reset();

            //llenar datos por defaul del formulario de captura
            getUnidadesR(1);
            getUnidadesNegocio(1, 0);
            getTipoContratos(1);
            getClasificacionContratos(1);
            getStatus(1);

            $('#ejercicio').val(yyyy);
            $('#FechaInicio').val(fechaIni);
            $('#FechaFin').val(fechaFin);


         } else {
            //para cuando se trata de una modificacion
            //- ccambair titulo del modal
            //- ocualtar el boton de regresar
            $('#myModalTitle').html('Modificar proveedor');
            $('#btnBackSearchProvider').hide();
         }
      } else {
         muestraMensaje(response.message + ' ' + response.content, 2, 'notificaciones', 5000);
      }
   }).fail(function(response) {
      console.log(response);
      muestraMensaje(msgConexion +' getSupplier()', 2, 'notificaciones', 5000)
   });
}
//obtencion de combos
function obtenerNombreArchivo(inputName, inputFile) {
   $('#' + inputName).val($('#' + inputFile).val());
}
//obtener la unidades responsables
function getUnidadesR(opc) {
   dataObj = {
      action: 'getUnidadesR',
      info: {
         opc: opc
      }
   }
   $.ajax({
      method: "POST",
      dataType: "json",
      url: "Seguimiento_Nuevos_Contratos_Ajax_V2_0.php",
      data: dataObj
   }).done(function(response) {
      if(debug)console.log(response)
      if (response.succesFlag == true) {
         if (opc == 0) {
            //combo en pantalla principal
            $('#IdUR').html(response.content);
         } else {
            $('#IdURContrato').html(response.content);
         }
      } else {
         muestraMensaje(response.message, 2, 'notificaciones', 5000)
      }
   }).fail(function(response) {
      muestraMensaje(msgConexion + ' getUnidadesR()', 2, 'notificaciones', 5000)
   });
}
/**
 * Obtener las areas de trabajo agrupadas por unidad de negocio
 * @return js
 */
function getUnidadesNegocio(opc, ur) {
   dataObj = {
      action: 'getUnidadesNegocio',
      info: {
         opc: opc,
         ur: ur
      }
   }
   console.log(dataObj);
   $.ajax({
      method: "POST",
      dataType: "json",
      url: "Seguimiento_Nuevos_Contratos_Ajax_V2_0.php",
      data: dataObj
   }).done(function(data) {
      if(debug)console.log(data);
      if (data.succesFlag == true) {
         switch (opc) {
            case 0:
               //llenar filtro de unidades reso
               $('#IdArea').html(data.content);
               break
            case 1:
               $('#IdAreaContrato').html(data.content);
               break;
            case 2:
               $('#IdAreaContrato').html(data.content);
               break;
            default:
               break;
         }
      } else {
         muestraMensaje(data.message, 2, 'notificaciones', 5000);
      }
   }).fail(function(result) {
      muestraMensaje(msgConexion + ' getUnidadesNegocio()', 2, 'notificaciones', 5000)
   });
}

function getTipoContratos(opc) {
   dataObj = {
      action: 'getTipoContratos',
      info: {
         opc: opc
      }
   }
   console.log(dataObj);
   $.ajax({
      method: "POST",
      dataType: "json",
      url: "Seguimiento_Nuevos_Contratos_Ajax_V2_0.php",
      data: dataObj
   }).done(function(data) {
      if (data.succesFlag == true) {
         switch (opc) {
            case 0:
               //llenar filtro de unidades reso
               $('#IdTipoContrato').html(data.content);
               break
            case 1:
               //llenar filtro de unidades reso
               $('#id_TipoContrato').html(data.content);
               break
            case 2:
               //llenar filtro de unidades reso
               $('#id_TipoContrato').html(data.content);
               break
            default:
               break;
         }
      } else {
         muestraMensaje(data.message, 2, 'notificaciones', 5000);
      }
   }).fail(function(result) {
      muestraMensaje(msgConexion + ' getTipoContratos()', 2, 'notificaciones', 5000)
   });
}

function getClasificacionContratos(opc) {
   dataObj = {
      action: 'getClasificacionContratos',
      info: {
         opc: opc
      }
   }
   console.log(dataObj);
   $.ajax({
      method: "POST",
      dataType: "json",
      url: "Seguimiento_Nuevos_Contratos_Ajax_V2_0.php",
      data: dataObj
   }).done(function(data) {
      if (data.succesFlag == true) {
         switch (opc) {
            case 0:
               //llenar filtro de unidades reso
               $('#IdTipoClasificacion').html(data.content);
               break
            case 1:
               //llenar filtro de unidades reso
               $('#idClasificacionContrato').html(data.content);
               break
            case 2:
               //llenar filtro de unidades reso
               $('#idClasificacionContrato').html(data.content);
               break
            default:
               break;
         }
      } else {
         muestraMensaje(data.message, 2, 'notificaciones', 5000);
      }
   }).fail(function(result) {
      muestraMensaje(msgConexion + ' getClasificacionContratos()', 2, 'notificaciones', 5000)
   });
}
/**
 * Obtener lista status de contratos
 * @return js
 */
function getStatus(opc) {
   dataObj = {
      action: 'getStatus',
      info: {
         opc: opc
      }
   }
   if(debug)console.log(dataObj);
   $.ajax({
      method: "POST",
      dataType: "json",
      url: "Seguimiento_Nuevos_Contratos_Ajax_V2_0.php",
      data: dataObj
   }).done(function(data) {
      if(debug)console.log(data);
      if (data.succesFlag == true) {
         if (opc == 0) {
            $('#estatusc').html(data.content);
         } else {
            $('#estatus').html(data.content);
         }
      } else {
         muestraMensaje(data.message, 2, 'notificaciones', 5000);
      }
   }).fail(function(result) {
      muestraMensaje(msgConexion + ' getStatus', 2, 'notificaciones', 5000)
   });
}
//procesar consulta principal
function processRequest() {
   //obtner los check seleccionados
   arrayStatus.length = 0;
   $("input[type=checkbox]:checked").each(function() {
      arrayStatus.push($(this).val());
   });
   console.log(arrayStatus);
   Obj = {
      action: 'getContracts',
      info: {
         IdUR: $('select[id=IdUR]').val(),
         IdArea: $('select[id=IdArea]').val(),
         IdTipoContrato: $('select[id=IdTipoContrato]').val(),
         IdTipoClasificacion: $('select[id=IdTipoContrato]').val(),
         folioContrato: $('#folioContrato').val(),
         nombreContrato: $('#princialnombreContrato').val(),
         IdContrato: $('#IdContrato').val(),
         IdSubproyecto: 0, //$('#IdSubproyecto').val(),
         status: arrayStatus,
         IdTipo: 3
      }
   }
   console.log(Obj);
   $.ajax({
      url: "Seguimiento_Nuevos_Contratos_Ajax_V2_0.php",
      method: "POST",
      dataType: "json",
      data: Obj
   }).done(function(response) {
      if (response.succesFlag) {
         if (response.content == 0 || response.content == '') {
            $('#panelc').html('');
            muestraMensaje(response.message, 2, 'notificaciones', 5000);
         } else {
            $('#panelc').html(response.content);
         }
         // $('#step-assigned-accordion .collapse').on('hidden.bs.collapse', toggleChevron);
         // $('#step-assigned-accordion .collapse').on('shown.bs.collapse', toggleChevron);
      } else {
         $('#panelc').html('');
         muestraMensaje(response.message + ' ' + response.content, 2, 'notificaciones', 5000);
      }
   }).fail(function(response) {
      console.log(response);
      muestraMensaje(msgConexion + ' processRequest()', 2, 'notificaciones', 5000)
   });
}
$('#myModal').on('shown.bs.modal', function() {
   $('#myModalTitle').html('...');
});
//administrador de contratos
//
function addComment(typeContratos, idContrato) {
   $('#addFiles').fadeOut();
   $('#addBills').fadeOut();
   $('#addComments').fadeIn();
   $('#actionType').val(1);
   $('#tipoContratomm').val(typeContratos);
   $('#idContratomm').val(idContrato);
   $('#multiModalTitle').html('Comentarios');
   $('#commet').val(''); //limpiar campo comentario al iniciar
   $('#multiModal').modal('show');
   setTimeout(function() {
      updateComment(typeContratos, idContrato);
   }, 500);
}

function addFile(typeContratos, idContrato, fechaInicio, fechaFin) {
   $('#addComments').fadeOut(); //ocultar comentarios
   $('#addBills').fadeOut(); //ocultar facturas
   $('#addFiles').fadeIn(); //mostar archivos
   $('#actionType').val(2);
   $('#tipoContratomm').val(typeContratos);
   $('#idContratomm').val(idContrato);
   $('#fechaIniciomm').val(fechaInicio);
   $('#FechaFinalmm').val(fechaFin);
   $('#multiModalTitle').html('Archivos Adjuntos');
   $('#multiModal').modal('show');
   $.datepicker.setDefaults($.datepicker.regional["es"]);
   $('#description-file2').datepicker({
      autoclose: true,
      format: 'MM yyyy',
      viewMode: 'months',
      minViewMode: 'months'
   });
   setTimeout(function() {
      updateFiles(9, typeContratos, idContrato);
   }, 500);

}

function addBill(idContrato, tagref, tipo_contrato) {
   $('#addComments').fadeOut();
   $('#addFiles').fadeOut();
   $('#addBills').fadeIn();
   $('#actionType').val(3);
   $('#tipoContratomm').val(tipo_contrato);
   $('#target').val(tagref);
   $('#idContratomm').val(idContrato);
   $('#multiModalTitle').html('Facturas');
   //obtener datos para el modal
   dataObj = {
      action: 'getDataModalBill',
      info: {
         idContrato: idContrato,
         tagref: tagref,
         tipoContrato: tipo_contrato
      }
   }
   if(debug)console.log(dataObj);
   $.ajax({
      method: "POST",
      dataType: "json",
      url: "Seguimiento_Nuevos_Contratos_Ajax_V2_0.php",
      data: dataObj
   }).done(function(data) {
      if(debug)console.log(data);
      if (data.succesFlag == true) {
         $('#propietarioidxml').val(idContrato);
         $('#tagrefxml').val(tagref);
         $('#tipopropietarioidxml').val(tipo_contrato);
         $('#idSolicitud').val(idContrato);
         $('#idSolicitud_des').val(data.content[5]['folio']);
         $('#desSolicitud').val(data.content[0]['realname']);
         $('#idSolicitudxml').val(idContrato);
         $('#desSolicitudxml').val(data.content[0]['realname']);
         setTimeout(function() {
            updateFactura(idContrato, tagref, tipo_contrato);
         }, 500);
      } else {
         muestraMensaje('No es posible actualizar los datos del modal. ' + data.message, 2, 'notificacionesMultimodal', 5000)
      }
   }).fail(function(result) {
      console.log(result);
      muestraMensaje('Imposible obtener información para el modal, ' + result.message, 3, 'notificacionesMultimodal', 5000)
   });
   $('#multiModal').modal('show');
   $(document).keyup(function(event) {
      if (event.which == 27) {
         $("#multiModal").modal("hide");
      }
   });
}

function updateComment(typeContratos, idContrato) {
   dataObj = {
      action: 'updateComment',
      info: {
         typeContratos: typeContratos,
         idContrato: idContrato
      }
   }
   $.ajax({
      method: "POST",
      dataType: "json",
      url: "Seguimiento_Nuevos_Contratos_Ajax_V2_0.php",
      data: dataObj
   }).done(function(data) {
      if (data.succesFlag == true) {
         //actualizar tabla.
         comments_table.destroy();
         $('#comments-body').html(data.content)
         comments_table = $('#comments-table').DataTable({
            scrollY: 200,
            scrollX: true,
            language: {
               url: 'javascripts/DataTables/Spanish.json'
            },
            ordering: false
         });
      } else {
         muestraMensaje('No es posible actualizar los comentarios. ' + data.message, 2, 'notificacionesMultimodal', 5000)
      }
   }).fail(function(result) {
      console.log(result);
      muestraMensaje('Imposible obtener la lista de comentarios, ' + msgConexion, 2, 'notificacionesMultimodal', 5000)
   });
}

function updateFiles(tipoPropietario, tipoContrato, idContrato) {
   console.log('entro a actulizar archivos', tipoPropietario + '-' + tipoContrato + '-' + idContrato);
   dataObj = {
      action: 'updateFiles',
      info: {
         tipoPropietario: tipoPropietario,
         tipoContrato: tipoContrato,
         idContrato: idContrato
      }
   }
   $.ajax({
      method: "POST",
      dataType: "json",
      url: "Seguimiento_Nuevos_Contratos_Ajax_V2_0.php",
      data: dataObj
   }).done(function(data) {
      console.log(data);
      if (data.succesFlag == true) {
         //actualizar tabla.
         $('#files-body').html(data.content)
         files_table.destroy();
         files_table = $('#files-table').DataTable({
            scrollY: 200,
            scrollX: true,
            language: {
               url: 'javascripts/DataTables/Spanish.json'
            },
            ordering: false
         });
      } else {
         muestraMensaje('No es posible actualizar los adjuntos. ' + data.message, 2, 'notificacionesMultimodal', 5000)
      }
   }).fail(function(result) {
      console.log(result);
      muestraMensaje('Imposible obtener la lista de adjuntos, ' + result.message, 2, 'notificacionesMultimodal', 5000)
   });
}

function updateFactura(idContrato, tagref, tipo_contrato) {
   dataObj = {
      action: 'updateBills',
      info: {
         idPropietario: $('#propietarioidxml').val(),
         idTipoPropietario: $('#tipopropietarioidxml').val(),
         cfuserID: $('#cfuserIDxml').val(),
         cffiltro: $('#cffiltroxml').val(),
         tagref: $('#tagrefxml').val()
      }
   }
   console.log(dataObj);
   $.ajax({
      method: "POST",
      dataType: "json",
      url: "Seguimiento_Nuevos_Contratos_Ajax_V1_0.php",
      data: dataObj
   }).done(function(data) {
      console.log(data);
      if (data.succesFlag == true) {
         var datos = JSON.parse(data.content);
         var $table = $('#table');
         $table.bootstrapTable("load", datos);
         //console.log(data.content);
         //var datos = $.parseJSON(data.content);
         //console.log(datos);
         //var $table = $('#table');
         //console.log($table);
         //$table.bootstrapTable("load", datos);
         //$('#tablaxml').bootstrapTable({data: datos});
         /*  
           rows = [];
          for (var i = 0; i < 10; i++) {
              rows.push({
                  fecha: i,
                  id: i,
                  mesFac: i,
                  monto: i,
                  nombre: i,
                  uudi: i,
                  usuario: i,
                  estadoVal1: i,
                  estadoVal2: i,
                  accion1: i,
                  xml: i,
                  pdf: i,
                  eliminar: i


              });

              console.log(rows);
              $table.bootstrapTable('load', rows);
              
          }
           */
         // array = [];
         // for (key in datos) {
         //    array.push(datos[key]);
         // }
         // console.log(array);
         // $('#tablaxml').bootstrapTable('destroy');
         //  var datos = JSON.parse(data.content);
         //  console.log(datos); 
         // $('#tablaxml').bootstrapTable({data: datos});
      } else {
         muestraMensaje('No es posible actualizar los adjuntos. ' + data.message, 2, 'notificacionesMultimodal', 5000)
      }
   }).fail(function(data) {
      console.log(data);
      muestraMensaje('Imposible obtener la lista de facturas. ' + data.message, 3, 'notificacionesMultimodal', 5000)
   });
}
//Cuado se muestre el modal realizar la siguientes acciones
$('#multiModal').on('shown.bs.modal', function() {
   option = $('#actionType').val();
   if (option == 1) //cometarios
   {
      comments_table.destroy();
      comments_table = $('#comments_table').DataTable({
         scrollY: 200,
         scrollX: true,
         language: {
            url: 'javascripts/DataTables/Spanish.json'
         },
         ordering: false
      });
   }
   if (option == 2) //archivos
   {
      files_table.destroy();
      files_table = $('#files_table').DataTable({
         scrollY: 200,
         scrollX: true,
         language: {
            url: 'javascripts/DataTables/Spanish.json'
         },
         ordering: false
      });
   }
   if (option == 3) //facturas
   {
      $('#tablaxml').bootstrapTable('destroy')
   }
});
$('#multiModal').on('hidden.bs.modal', function(e) {
   //ocultar el contenedor de secciones
   //y hacer que el tipo de accion sea = 0
   $('#multiModal .section-container-modal').fadeOut();
   $('#actionType').val('0');
});

function guardarAcciones() {
   option = $('#actionType').val();
   tipoContrato = $('#tipoContratomm').val(),
      idContrato = $('#idContratomm').val()
   fechaInicio = $('#fechaIniciomm').val();
   FechaFinal = $('#FechaFinalmm').val();
   if (option == 1) //cometarios
   {
      dataObj = {
         action: 'saveComment',
         info: {
            comment: $('#comment').val(),
            tipoContrato: tipoContrato,
            idContrato: idContrato
         }
      }
      if ($('#comment').val().trim() == '') {
         muestraMensaje('El campo comentario es requerido.', 2, 'notificacionesMultimodal', 5000)
         $('#comment').parent().addClass('has-error');
         quitarClaseError();
      } else {
         $.ajax({
            method: "POST",
            dataType: "json",
            url: "Seguimiento_Nuevos_Contratos_Ajax_V2_0.php",
            data: dataObj
         }).done(function(data) {
            if (data.succesFlag == true) {
               muestraMensaje('El comentario se registro correctamente', 1, 'notificacionesMultimodal', 5000);
               $('#comment').val('');
               setTimeout(function() {
                  updateComment(tipoContrato, idContrato);
               }, 500);
            } else {
               //muestraMensaje('Ocurrio un erro al intentar registrar los datos. ' + data.message, 3, 'notificacionesMultimodal', 5000)
            }
         }).fail(function(result) {
            console.log(result);
            muestraMensaje('No se pudo obtener la lista unidades de negocio, ' + msgConexion, 3, 'notificacionesMultimodal', 5000)
         });
      }
   }
   if (option == 2) {
      //guardar adjuntos
      if ($('#file-name-input2').val().trim() == '') {
         muestraMensaje('Debe seleccionar un archivo.', 2, 'notificacionesMultimodal', 5000)
         $('#file-name-input2').parent().addClass('has-error');
         quitarClaseError();
      }else if($('#description-file2').val().trim() == ''){
         muestraMensaje('Debe seleccionar el mes a donde aplicara el archivo.', 2, 'notificacionesMultimodal', 5000)
         $('#fdescription-file2').parent().addClass('has-error');
      } else {
         subirArchivo("formFiles", "file-to-upload-modal2", "description-file2", idContrato, tipoContrato, 'progress-upload-file2', 'text-progress2', 'mensajes-acciones', 0);
         //limpiar formularios
         $('.section-container-modal input').val(''); //limpiar los imputs
         $('.section-container-modal textarea').val(''); //limbiar los textarea
      }
   }
   if (option == 3) {
      //guardar Factura
      var msgAux = '';
      if ($('#archivoxml1').val().trim() == '') {
         msgAux = 'Debe seleccionar un archivo.';
         $('#archivoxml1').parent().addClass('has-error');
      }
      if ($('#idSolicitud').val().trim() == '') {
         msgAux = 'No fue posible obtener el folio de la solicitud.';
         $('#idSolicitud').parent().addClass('has-error');
      }
      if (msgAux == '') {
         var target = $('#tagrefxml').val();
         quitarClaseError();
         subirFactura('formBills', idContrato, tipoContrato, target);
         //limpiar formularios
         //$('.section-container-modal input').val('');
         $('.section-container-modal textarea').val('');
      } else {
         muestraMensaje(msgAux, 3, 'notificacionesMultimodal', 5000)
      }
   }
}

function subirArchivo(formID, fileID, descFileID, ownerID, typeOwner, progressBarID, textBarID, messageID, actualizar) {
   var formData = new FormData(document.getElementById(formID));
   formData.append("archivo1", $('#' + fileID)[0].files[0]);
   formData.append("descripcion1", $('#' + descFileID).val());
   formData.append("propietarioid", ownerID);
   formData.append("tipopropietarioid", 9);
   formData.append("tipo_contrato", typeOwner);
   formData.append("id_contrato", ownerID);
   //cambiar las diagonales por guion medio
   var cadauxIni = $('#fechaIniciomm').val();
   var cadauxFin = $('#FechaFinalmm').val();
   var patron = /\//g;
   var nuevoValor = "-";
   formData.append("fechaInicio", cadauxIni.replace(patron, nuevoValor));
   formData.append("FechaFinal", cadauxFin.replace(patron, nuevoValor));
   $.ajax({
      xhr: function() {
         var xhr = new window.XMLHttpRequest();
         //Upload progress
         xhr.upload.addEventListener("progress", function(evt) {
            if (evt.lengthComputable) {
               var percentComplete = evt.loaded / evt.total;
               //Do something with upload progress
               console.log('progreso subida ', percentComplete);
               progreso = percentComplete * 100;
               $('#' + textBarID).html('Subiendo&hellip; ' + progreso + '%');
               $('#' + progressBarID).val(percentComplete);
            }
         }, false);
         //Download progress
         xhr.addEventListener("progress", function(evt) {
            if (evt.lengthComputable) {
               var percentComplete = evt.loaded / evt.total;
               //Do something with download progress
               console.log(percentComplete);
            }
         }, false);
         return xhr;
      },
      url: "task_cargarArchivo_v1_0.php",
      type: "POST",
      data: formData,
      cache: false,
      contentType: false,
      processData: false
   }).done(function(msg) {
      console.log(msg);
      $("#text-progress2").html(msg).show();
      $("#text-progress2").delay(2500).fadeOut("slow");
      //actualizar adjuntos
      //console.log('actualizo archivos.'+typeOwner+' - '+ownerID);
      setTimeout(function() {
         updateFiles(9, typeOwner, ownerID);
      }, 2000);
   }).fail(function(msg) {
      console.log('error: ', msg);
      muestraMensaje(msg, 3, 'notificacionesMultimodal', 5000)
   });
}

function subirFactura(formID, idContrato, tipoContrato, target) {
   var formBill = document.getElementById(formID);
   formData = new FormData(formBill);
   $.ajax({
      xhr: function() {
         var xhr = new window.XMLHttpRequest();
         //Upload progress
         xhr.upload.addEventListener("progress", function(evt) {
            if (evt.lengthComputable) {
               //console.log("progreso de loaded ", evt.loaded);
               //console.log("progreso de total ", evt.total);
               var percentComplete = evt.loaded / evt.total;
               //proceso de carga
               //console.log("progreso de validacion ", percentComplete);
               progreso = percentComplete * 100;
               //console.log(progreso);
               $('#text-progress_xml').html('Proceso de carga&hellip; ' + progreso + '%');
               $('#progress-upload-xml').val(percentComplete);
               $('div.multimodal').block({
                  message: '<i class="fa fa-cog fa-spin fa-3x fa-fw"></i><span>Validando...</span>',
                  css: {
                     border: '1px solid #a00'
                  }
               });
            }
         }, false);
         //Download progress
         xhr.addEventListener("progress", function(evt) {
            if (evt.lengthComputable) {
               var percentComplete = evt.loaded / evt.total;
               //porcentaje de descarga
               console.log(percentComplete);
            }
         }, false);
         return xhr;
      },
      url: "task_cargarArchivoXML2024.php",
      type: "POST",
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json"
   }).done(function(response) {
      if (response.succesFlag == false) {
         muestraMensaje(response.message, 3, 'notificacionesMultimodal', 9000);
         $('div.multimodal').unblock();
      } else {
         $('div.multimodal').unblock();
         //updateFactura(idContrato, tagref, tipo_contrato);
         $("#descripcionxml1").val("");
         $("#file-name-inputxml").val("");
         $("#archivoxml1").val("");
         setTimeout(function() {
            $('div.multimodal').unblock();
            muestraMensaje(response.message, 1, 'notificacionesMultimodal', 9000);
            $("#progress-upload-xml").val(0);
            $("#text-progress_xml").empty();
         }, 500);
      }
   }).fail(function(response) {
      $('div.multimodal').unblock();
      muestraMensaje('Ocurrio un problema al intentar validar la factura. Consulte con el administrador.', 3, 'notificacionesMultimodal', 9000);
   });
}
$('#btnExpXLS').click(function() {});
//al dar click en nuevo contrato se despliega el modal myModal

$('#btnsaveContract').click(function(event) {
   event.preventDefault();
   if(obtenerParcialidades()){
      $('#fMyModal').submit(); //validar formulario
   }else{
      muestraMensaje('Es neceseario Indicar el numero de parcialidades', 2, 'notificacionesMyModal', 5000);
      //$('#noParcialidades').parent().addClass('has-error');
   }
   
});
function obtenerParcialidades(){
   pParcialidades.length=0;
   var totalCampos=$("#noParcialidades").val();
   if(totalCampos >=1){
      //$('#noParcialidades').parent().removeClass('has-error');
      for (var i = 1; i <= totalCampos; i++) {
         $('#row-1').find('#pfechaRev1').val()

         pfecharev = $('#row-'+i).find('#pfechaRev'+i).val();
         pfechaVen = $('#row-'+i).find('#pfechaVen'+i).val();
         ppen = $('#row-'+i).find('#ppen'+i).val();
         ppentipo = $('#row-'+i).find('#ppentipo'+i).val();
         pded = $('#row-'+i).find('#pded'+i).val();
         pdedtipo = $('#row-'+i).find('#pdedtipo'+i).val();
         pmonto = $('#row-'+i).find('#pmonto'+i).val();

         response=true;
         if(pfecharev == ''){
            $('#pfechaRev'+i).parent().addClass('has-error');
            response=false;
         }
         else{
            $('#pfechaRev'+i).parent().removeClass('has-error');
         }

         if(pfechaVen == ''){
            response=false;
            $('#pfechaVen'+i).parent().addClass('has-error');
         }
         else
            $('#pfechaVen'+i).parent().removeClass('has-error');

         if(ppen == ''){
            response=false;
            $('#ppen'+i).parent().addClass('has-error');
         }
         else
            $('#ppen'+i).parent().removeClass('has-error');

         if(ppentipo == ''){
            response=false;
            $('#ppentipo'+i).parent().addClass('has-error');
         }
         else
            $('#ppentipo'+i).parent().removeClass('has-error');
         if(pded == ''){
            response=false;
            $('#pded'+i).parent().addClass('has-error');
         }
         else
            $('#pded'+i).parent().removeClass('has-error');

         if(pdedtipo == ''){
            response=false;
            $('#pdedtipo'+i).parent().addClass('has-error');
         }
         else
            $('#pdedtipo'+i).parent().removeClass('has-error');

         if(pmonto == ''){
            response=false;
            $('#pmonto'+i).parent().addClass('has-error');
         }
         else
            $('#pmonto'+i).parent().removeClass('has-error');

         
         pParcialidades.push(
         {
            pfecharev: pfecharev,
            pfechaVen: pfechaVen,
            ppen: ppen,
            ppentipo: ppentipo,
            pded: pded,
            pdedtipo: pdedtipo,
            pmonto: pmonto
         });
      }
   }else{
      response=false
   }
   if(debug)console.log('lectura de tabla de parcialidades');
   if(debug)console.log(pParcialidades);
   return response;
}
//modificar contrato
function edit(idContrato, idtarget, tipoContrato) {
   $('#myModal').modal('show');
   $('#myModalTitle').html('Modificar proveedor');
   document.getElementById("fMyModal").reset();
   //obtener datos del contrato
   Obj = {
      action: 'getContractEdit',
      info: {
         idContrato: idContrato,
         idtarget: idtarget,
         tipoContrato: tipoContrato
      }
   }
   console.log(Obj);
   $.ajax({
      url: "Seguimiento_Nuevos_Contratos_Ajax_V1_0.php",
      method: "POST",
      dataType: "json",
      data: Obj
   }).done(function(response) {
      if (response.succesFlag) {
         //llenar formulario
         seleccionarProveedor(response.content.supplierid, 2);
         setTimeout(function() {
            //console.log(response);
            var strFechaIni = response.content.fechainicio;
            var resFI = strFechaIni.split("-");
            var strFechafin = response.content.fechafin;
            var resFF = strFechafin.split("-");
            //console.log(resFI);
            //console.log(resFF);
            $('#FechaInicio').val(resFI[0] + '-' + resFI[1] + '-' + resFI[2]);
            $('#FechaFin').val(resFF[0] + '-' + resFF[1] + '-' + resFF[2]);
            $('#proveedor').val(response.content.supplierid);
            $('#nombreContrato').val(response.content.nombre);
            $("#IdURContrato").val(response.content.tagref);
            getAreas(2, response.content.tagref);
            setTimeout(function() {
               $("#IdAreaContrato").val(response.content.u_department);
            }, 1000);
            //$("#IdAreaContrato").val(response.content.u_department);
            $('#folio').val(response.content.folio);
            $('#ejercicio').val(response.content.ejercicio_fiscal);
            $('#monto').val(response.content.monto);
            $("#estatus").val(response.content.idestatus);
            $("#IdTipoContratacion").val(response.content.tipo_contrato);
            $("#crearUsuario").val(2);
            $("#idContratoM").val(idContrato);
         }, 99);
      } else {
         //se encontro un problema al ejecutar la funcion
         muestraMensaje(response.message, 3, 'notificacionesMyModal', 5000);
      }
      console.log(response);
   }).fail(function(response) {
      console.log(response);
      muestraMensaje('No se pudo procesar la consulta,' + msgConexion, 2, 'notificacionesMyModal', 5000)
   });
}

function saveContract() {
   var idContratoM = $('#idContratoM').val();
   var action = '';
   if (idContratoM == 0) {
      action = 'saveContract';
   } else {
      action = 'updateContract';
   }

   var tParc=$('#parcialidades :selected').val();
   if(tParc == 1){
      //si se selecciono parcialidades obtener siguientes campos
      var noParcialidades=$('#noParcialidades').val();
      var tpParcialidades=pParcialidades;
   }else{
      //var noParcialidades=$('#noParcialidad').val();
      var noParcialidades=0;
      var tpParcialidades=0;
   }

   Obj = {
      action: action,
      info: {
         nombreContrato: $('#nombreContrato').val(),
         folio: $('#folio').val(),
         IdURContrato: $('#IdURContrato :selected').val(),
         IdAreaContrato: $('#IdAreaContrato :selected').val(),
         parcialidades: noParcialidades,
         ejercicio: $('#ejercicio').val(),
         FechaInicio: $('#FechaInicio').val(),
         monto: $('#monto').val(),
         FechaFin: $('#FechaFin').val(),
         id_TipoContrato: $('#id_TipoContrato :selected').val(),
         idClasificacionContrato:$('#idClasificacionContrato :selected').val(),
         estatus: $('#estatus :selected').val(),
         crearUsuario: $('#crearUsuario :selected').val(),
         tpParcialidades:tpParcialidades,
         idProveedor: $('#proveedor').val(),
         IdContrato: $('#IdContrato').val(),
         tipoC: $('#tipoC').val(),
         idContratoM: $('#idContratoM').val()
      }
   }
   if(debug)console.log('Guardando..');
   if(debug)console.log(Obj);
   
   $.ajax({
      url: "Seguimiento_Nuevos_Contratos_Ajax_V2_0.php",
      method: "POST",
      dataType: "json",
      data: Obj
   }).done(function(response) {
      if (response.succesFlag) {
         muestraMensaje(response.message, 1, 'notificaciones', 5000);
         //ocular el modal y ejecuar la funcion processRequest();
         $('#myModal').modal('hide');
         processRequest();
      } else {
         //se encontro un problema al ejecutar la funcion
         muestraMensaje(response.message, 3, 'notificacionesMyModal', 5000);
      }
      console.log(response);
   }).fail(function(response) {
      console.log(response);
      muestraMensaje('No se pudo procesar la consulta,' + msgConexion, 2, 'notificacionesMyModal', 5000)
   });
   
}
/**
 * Validacion de formularios
 */
//******validacion de formularios
$('#fprincipal').formValidation({
   framework: 'bootstrap',
   // button: {
   //     selector: '#btnConsulta',
   //     disabled: 'disabled'
   // },
   icon: {
      //required: 'text-danger fa fa-asterisk fa-fw',
      valid: 'fa fa-check fa-fw',
      invalid: 'fa fa-times fa-fw',
      validating: 'fa fa-refresh fa-fw'
   },
   fields: {
      'status[]': {
         validators: {
            notEmpty: {
               message: 'Por favor especifique un estatus'
            }
         }
      }
   }
}).on('success.form.fv', function(e) {
   processRequest();
   //limpiar seccion donde se muestran los datos
   /*
   // Prevent form submission
   e.preventDefault();
   // Some instances you can use are
   var $form = $(e.target), // The form instance
      fv = $(e.target).data('formValidation');
   $form.data('formValidation').resetForm();
   // $('#formInfo').data('formValidation').resetForm();
   // $('#formTelefono').data('formValidation').resetForm();
   // $('#formDir').data('formValidation').resetForm();
   //$('#formCuenta').data('formValidation').resetForm();
   xajax_buscarBeneficiario(xajax.getFormValues('formSearchCurp'));
   */
}).on('err.form.fv', function(e, data) {
   muestraMensaje('Es neceseario llenar o seleccionar los campos marcados en rojo', 2, 'notificaciones', 5000);
});
$('#fMyModal')
   // IMPORTANT: You must declare .on('init.field.fv')
   // before calling .formValidation(options)
   .on('init.field.fv', function(e, data) {
      // data.fv      --> The FormValidation instance
      // data.field   --> The field name
      // data.element --> The field element
      var $icon = data.element.data('fv.icon'),
         options = data.fv.getOptions(), // Entire options
         validators = data.fv.getOptions(data.field).validators; // The field validators
      if (validators.notEmpty && options.icon && options.icon.required) {
         // The field uses notEmpty validator
         // Add required icon
         $icon.addClass(options.icon.required).show();
      }
   }).formValidation({
      framework: 'bootstrap',
      icon: {
         //required: 'text-danger fa fa-asterisk fa-fw',
         valid: 'fa fa-check fa-fw',
         invalid: 'fa fa-times fa-fw',
         validating: 'fa fa-refresh fa-fw'
      },
      fields: {
         'nombreContrato': {
            validators: {
               notEmpty: {
                  message: 'El campo es requerido'
               },
               stringLength: {
                  min: 1,
                  max: 200,
                  message: 'El campo debe contener entre 1 y 200 caracteres.'
               }
            }
         },
         'IdURContrato': {
            validators: {
               notEmpty: {
                  message: 'El campo es requerido'
               }
            }
         },
         'IdAreaContrato': {
            validators: {
               notEmpty: {
                  message: 'El campo es requerido'
               }
            }
         },
         'folio': {
            validators: {
               notEmpty: {
                  message: 'El campo es requerido'
               }
            }
         },
         'ejercicio': {
            validators: {
               notEmpty: {
                  message: 'El campo es requerido'
               },
               numeric: {
                  message: 'El valor debe ser númerico'
               },
               regexp: {
                  regexp: /^\d{4,4}$/,
                  message: 'El campo debe contener 4 digitos'
               },
               numeric: {
                  message: 'El valor no puede ser mayor al año actual',
                  max: yyyy
               }
            }
         },
         'monto': {
            validators: {
               notEmpty: {
                  message: 'El campo es requerido'
               },
               numeric: {
                  message: 'El valor tecleado no valido',
                  thousandsSeparator: '',
                  decimalSeparator: '.'
               }
            }
         },
         'noParcialidades':{
            validators: {
               notEmpty: {
                  message: 'El campo es requerido'
               },
               integer: {
                   message: 'El valor debe ser numerico',
                   // The default separators
                   thousandsSeparator: '',
                   decimalSeparator: '.'
               },
               numeric: {
                  message: 'El valor no puede ser mayor a 6 parcialidades',
                  max: 6
               },
               regexp: {
                  regexp: /^\d{1,1}$/,
                  message: 'El campo debe contener 1 digitos'
               },
            }
         },
         'FechaInicio': {
            validators: {
               notEmpty: {
                  message: 'El campo es requerido'
               }
               // ,
               // date: {
               //     format: 'MM-DD-YYYY',
               //     max: 'FechaFin',
               //     message: 'La fecha de inicio no pude ser mayor a la fecha de termino'
               // }
            }
         },
         'FechaFin': {
            validators: {
               notEmpty: {
                  message: 'El campo es requerido'
               }
               // ,
               // date: {
               //     format: 'MM-DD-YYYY',
               //     min: 'FechaInicio',
               //     message: 'La fecha de termino no pude ser menor a la fecha de inicio'
               // }
            }
         },
         'estatus': {
            validators: {
               notEmpty: {
                  message: 'El campo es requerido'
               }
            }
         },
         'tipoC': {
            validators: {
               notEmpty: {
                  message: 'El campo es requerido'
               }
            }
         },
         'id_TipoContrato': {
            validators: {
               notEmpty: {
                  message: 'El campo es requerido'
               }
            }
         },
         'idClasificacionContrato': {
            validators: {
               notEmpty: {
                  message: 'El campo es requerido'
               }
            }
         },
         'crearUsuario': {
            validators: {
               notEmpty: {
                  message: 'El campo es requerido'
               }
            }
         }
      }
   }).on('success.form.fv', function(e) {
      saveContract();
   }).on('err.form.fv', function(e, data) {
      muestraMensaje('Es neceseario llenar o seleccionar los campos marcados en rojo', 2, 'notificacionesMyModal', 5000);
   });

function verExcel() {
   arrayStatus.length = 0;
   $("input[type=checkbox]:checked").each(function() {
      arrayStatus.push($(this).val());
   });
   IdUrFiltro = $('select[id=IdUrFiltro]').val();
   IdArea = $('select[id=IdArea]').val();
   IdTipo = $('select[id=IdTipo]').val();
   folio = $('#folio').val();
   nombre = $('#nombre').val();
   IdContrato = $('#IdContrato').val();
   status = arrayStatus.toString();
   window.open("Excel_Contratos_Proveedor_V1_0.php?IdUrFiltro=" + IdUrFiltro + "&IdArea=" + IdArea + "&IdTipo=" + IdTipo + "&folio=" + folio + "&nombre=" + nombre + "&IdContrato=" + IdContrato + "&status=" + status);
}

function verPDF() {
   arrayStatus.length = 0;
   $("input[type=checkbox]:checked").each(function() {
      arrayStatus.push($(this).val());
   });
   IdUrFiltro = $('select[id=IdUrFiltro]').val();
   IdArea = $('select[id=IdArea]').val();
   IdTipo = $('select[id=IdTipo]').val();
   folio = $('#folio').val();
   nombre = $('#nombre').val();
   IdContrato = $('#IdContrato').val();
   status = arrayStatus.toString();
   window.open("PDF_Contratos_Proveedor_V1_0.php?IdUrFiltro=" + IdUrFiltro + "&IdArea=" + IdArea + "&IdTipo=" + IdTipo + "&folio=" + folio + "&nombre=" + nombre + "&IdContrato=" + IdContrato + "&status=" + status);
}
/**
 * Para mostrar u ocultar el elemento con id=panel1
 * 
 * @param  String capa nombre del div a mostrar u ocultar..
 * @return 
 */
function showdiv(capa) {
   var estado = $(capa).css('display');
   var icono = '';
   if (estado == 'none') {
      $(capa).slideDown("medium");
      icono = String('fa fa-angle-up fa-lg');
   } else {
      $(capa).slideUp("medium");
      icono = String('fa fa-angle-down fa-lg');
   }
   $(iconc).html('<i class="' + icono + '" onclick="showdiv(panel1);" style="cursor:pointer"></i>');
};

function Show1(selected, uclick) { // Menu año
   var objShow = document.getElementById(selected);
   var estado = $(selected).css('display');
   //var selector= $(uclick)document.getElementById;
   var selector = uclick.toString();
   var selectora = '#';
   var res = selectora.concat(selector);
   //alert(uclick);
   if (estado == 'none') {
      $(selected).slideDown("medium");
      $(res).addClass('selected');
   } else {
      $(selected).slideUp("medium");
      $(res).removeClass('selected');
   }
};
//funcion para abrir una ventana externa
function abrirVentana(Id, solicitudId, unidaNegocio, tipopropietarioid, doctoid, url, opc) {
   window.open('DespliegaValidacionXml_2024.php?URL=' + url, 'name', 'height=400,width=800');
}
/*
-Funciones auxiliares
 */
function muestraMensaje(mensaje, opc, site, time) {
   //mensajeOK
   if (opc == 1) {
      notificacion = '<div class="alert alert-success alert-dismissable">' + '<button type="button" class="close" data-dismiss="alert">&times;</button>' + '<p>' + mensaje + '</p>' + '</div>';
   }
   //mensaje alerta
   if (opc == 2) {
      notificacion = '<div class="alert alert-warning alert-dismissable">' + '<button type="button" class="close" data-dismiss="alert">&times;</button>' + '<p>' + mensaje + '</p>' + '</div>';
   }
   //mensaje error
   if (opc == 3) {
      notificacion = '<div class="alert alert-danger alert-dismissable">' + '<button type="button" class="close" data-dismiss="alert">&times;</button>' + '<p>' + mensaje + '</p>' + '</div>';
   }
   $('#' + site).html(notificacion);
   //$("#notificaciones").append(notificacion);
   $('#' + site).show();
   $('#' + site).delay(time).fadeOut("slow");
}

function justNumbers(e) {
   var key = window.event ? window.event.keyCode : e.which;
   if (key <= 13 || (key >= 48 && key <= 57) || key == 46) return true;
   return /\d/.test(String.fromCharCode(key));
}

function validar_num() {
   num = $("#monto").val();
   if (isNaN(num)) {
      num = $("#monto").val("");
   }
}

function quitarClaseError() {
   setTimeout(function() {
      $('div').removeClass('has-error');
   }, 2500);
}