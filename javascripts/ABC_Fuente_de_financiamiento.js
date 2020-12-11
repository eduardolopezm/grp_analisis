/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jesús Reyes Santos 
 * @version 0.1
 */
var url = "modelo/ABC_Fuente_de_financiamiento_modelo.php";
var proceso = "";

$(document).ready(function() {
   //Mostrar Catalogo
   fnMostrarDatos('','','');
   $("#txtClave").blur(function(){
	   var addCero = $("#txtClave").val();
	   if (addCero.length == 1){
		   $("#txtClave").val("0"+addCero)
	   } 
   });
});

/**
* Muestra la información del catalogo completo o de forma individual
* @param  {String} ur Código del Registro para obtener la información
*/
function fnMostrarDatos(id_financiamiento,id_fuente,id_identificacion) {

   dataObj = {
	   option: 'mostrarCatalogo',
	   id_financiamiento: id_financiamiento,
	   id_fuente: id_fuente,
	   id_identificacion: id_identificacion
   };
   $.ajax({
	   async:false,
	   cache:false,
	   method: "POST",
	   dataType: "json",
	   url: url,
	   data: dataObj
   }).done(function(data) {
	   if (data.result) {
		   
		   dataSubfuncionJason = data.contenido.datos;
		   columnasNombres = data.contenido.columnasNombres;
		   columnasNombresGrid = data.contenido.columnasNombresGrid;
		   
		   fnLimpiarTabla('divTabla', 'divContenidoTabla');

		   var nombreExcel = data.contenido.nombreExcel;
		   var columnasExcel= [0, 1, 2, 3, 4];
		   var columnasVisuales= [0, 1, 2, 3, 4, 5, 6];
		   fnAgregarGrid_Detalle(dataSubfuncionJason, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);
	   }
   }).fail(function(result) {
	   console.log("ERROR");
	   console.log(result);
   });
}
/** Muestra formulario para agregar un nuevo registro */
function fnAgregarCatalogoModal() {
   $('#divMensajeOperacion').addClass('hide');
   proceso = "Agregar";

   $("#selectIdentificador option[value=0]").attr("selected",true);
   $("#selectIdentificador").multiselect('rebuild');
   $("#selectFuente option[value=0]").attr("selected",true);
   $("#selectFuente").multiselect('rebuild');
   $("#txtClave").prop("readonly", false);
   $('#txtClave').val("");
   $('#txtDescripcion').val("");
   var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Agregar Fuente de Financiamiento</h3>';
   $('#ModalUR_Titulo').empty();
   $('#ModalUR_Titulo').append(titulo);
   $('#ModalUR').modal('show');
}
/** Agregar nuevo registro validando que no existan campos vacios */
function fnAgregar() {
   var finalidad = $('#selectIdentificador').val();
   var funcion = $('#selectFuente').val();
   var clave = $('#txtClave').val();
   var descripcion = $('#txtDescripcion').val();
   var msg = "";

   /*if (clave == "" || descripcion == "" || funcion == "0" || funcion == "" || finalidad == "0" || finalidad == "") {

	   $('#ModalUR').modal('hide');
	   muestraMensaje('Agregar Subfunción y Descripción para realizar el proceso', 3, 'msjValidacion', 5000);
	   return false;
   }*/
   /*if (clave == "" && descripcion == "") {
	   
	   $('#ModalUR').modal('hide');
	   muestraMensaje('Debe agregar Subfunción y Descripción para realizar el proceso', 3, 'msjValidacion', 5000);
	   return false;
	   
	   }else if (clave == "") {
		   $('#ModalUR').modal('hide');
		   muestraMensaje('Debe agregar Subfunción para realizar el proceso', 3, 'msjValidacion', 5000);
		   return false;
	   }else if (descripcion == "") {
		   $('#ModalUR').modal('hide');
		   muestraMensaje('Debe agregar Descripción para realizar el proceso', 3, 'msjValidacion', 5000);
		   
		   return false;
   }*/
   if (finalidad == "" || finalidad == null || finalidad == 0 || finalidad == "0" || finalidad === "undefined" || funcion == "" || funcion == null || funcion == 0 || funcion == "0" || funcion === "undefined"|| clave == "" || descripcion == "") {
	   if (finalidad == "" || finalidad == null || finalidad == 0 || finalidad == "0" || finalidad === "undefined" ) {
		   msg += '<p>Falta capturar la clave Identificación del Recurso</p>';
	   }
	   if (funcion == "" || funcion == null || funcion == 0 || funcion == "0" || funcion === "undefined"  ) {
		   msg += '<p>Falta capturar la clave Fuente del Recurso </p>';
	   }
	   if (clave == "" ) {
		   msg += '<p>Falta capturar la clave</p>';
	   }
	   if (descripcion=="" ) {
		   msg += '<p>Falta capturar el Descripción </p>';
	   }

	   $('#divMensajeOperacion').removeClass('hide');
	   $('#divMensajeOperacion').empty();
	   $('#divMensajeOperacion').append('<div class="alert alert-danger alert-dismissable">' + msg + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
	   
   }else{
	   $('#ModalUR').modal('hide');
	   dataObj = {
		   option: 'AgregarCatalogo',
		   funcion: funcion,
		   finalidad: finalidad,
		   clave: clave,
		   descripcion: descripcion,
		   proceso: proceso
	   };
	   $.ajax({
		   async:false,
		   cache:false,
		   method: "POST",
		   dataType: "json",
		   url: url,
		   data: dataObj
	   }).done(function(data) {
		   if (data.result) {
			   muestraMensaje(data.contenido, 1, 'divMensajeOperacion', 5000);
			   fnLimpiarTabla('divTabla', 'divContenidoTabla');
			   fnMostrarDatos('','','');
		   } else {
			   muestraMensaje(data.contenido, 3, 'divMensajeOperacion', 5000);
		   }
	   }).fail(function(result) {
		   console.log("ERROR");
		   console.log(result);
	   });
   }
}
/**
* Muestra formulario para modificar la información del registro seleccionado
* @param  {String} ur Código del Registro para obtener la información
*/
function fnModificar(id_financiamiento,descSubfuncion,id_fuente,descFuncion,id_identificacion,descFinalidad) {
   $('#divMensajeOperacion').addClass('hide');
   //alert(idSubfuncion+ ' - ' +descSubfuncion+ ' - ' +idFuncion+ ' - ' +descFuncion+ ' - ' +idFinalidad+ ' - ' +descFinalidad);
   proceso = "Modificar";

   dataObj = {
	   option: 'mostrarCatalogo',
	   id_financiamiento: id_financiamiento,
	   descSubfuncion: descSubfuncion,
	   id_fuente: id_fuente,
	   descFuncion: descFuncion,
	   id_identificacion: id_identificacion,
	   descFinalidad: descFinalidad
   };
   $.ajax({
	   async:false,
	   cache:false,
	   method: "POST",
	   dataType: "json",
	   url: url,
	   data: dataObj
   }).done(function(data) {
	   if (data.result) {
		   info = data.contenido.datos;
		   for (key in info) {
			   $("#selectIdentificador").val(""+id_identificacion);
			   $("#selectIdentificador").multiselect('rebuild');
			   $("#selectIdentificador").multiselect('disable');
			   $("#selectIdentificador").trigger('change')
			   //$(".multiselect").attr('disabled', 'disabled');
			   
			   $("#selectFuente").val(""+id_fuente);
			   $("#selectFuente").multiselect('rebuild');
			   $("#selectFuente").multiselect('disable');
			   //$(".multiselect").attr('disabled', 'disabled');

			   $("#txtClave").val("" + info[key].Clave);
			   $("#txtClave").prop("readonly", true);
			   $("#txtDescripcion").val("" + info[key].Subfuncion);
		   }
		   var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Modificar Fuente de Financiamiento</h3>';
		   $('#ModalUR_Titulo').empty();
		   $('#ModalUR_Titulo').append(titulo);
		   $('#ModalUR').modal('show');
	   }
   }).fail(function(result) {
	   console.log("ERROR");
	   console.log(result);
   });
}
/** Muestra mensaje de confirmación para eliminar registro */
function fnEliminar(idSubfuncion,descSubfuncion,idFuncion,idFinalidad) {
   $("#txtFinEliminar").val("" + idFinalidad);
   $("#txtFunEliminar").val("" + idFuncion);
   $("#txtClaveEliminar").val("" + idSubfuncion);
   
   var mensaje = 'Desea eliminar la Fuente de Financiamiento ' + idSubfuncion + ' - '+descSubfuncion;
   $('#ModalUREliminar_Mensaje').empty();
   $('#ModalUREliminar_Mensaje').append(mensaje);
   $('#ModalUREliminar').modal('show');
}
/** Elimina el registro al confirmar la eliminación */
function fnEliminarEjecuta() {
   var idFinalidad = $('#txtFinEliminar').val();
   var idFuncion = $('#txtFunEliminar').val();
   var idSubfuncion = String($('#txtClaveEliminar').val());
   var descripcion = $('#txtDescripcion').val();
   if(idSubfuncion < 10){
	   idSubfuncion = '0'+idSubfuncion;
   }
   console.log(idSubfuncion);
   $('#ModalUREliminar').modal('hide');
   dataObj = {
	   option: 'eliminarUR',
	   descripcion: descripcion,
	   idSubfuncion: idSubfuncion,
	   idFuncion: idFuncion,
	   idFinalidad: idFinalidad
   };
   $.ajax({
	   async:false,
	   cache:false,
	   method: "POST",
	   dataType: "json",
	   url: url,
	   data: dataObj
   }).done(function(data) {
	   if (data.result) {
		   muestraMensaje(data.contenido, 1, 'divMensajeOperacion', 5000);
		   fnLimpiarTabla('divTabla', 'divContenidoTabla');
		   fnMostrarDatos('','','');
	   } else {
		   muestraMensaje(data.contenido, 3, 'divMensajeOperacion', 5000);
	   }
   }).fail(function(result) {
	   console.log("ERROR");
	   console.log(result);
   });
}

function fnFinalidadFuncion() {
   var id_identificacion = $("#selectIdentificador").val();
   dataObj = {
	   option: 'mostrarFuncion',
	   id_identificacion: id_identificacion
   };
   $.ajax({
	   async:false,
	   cache:false,
	   method: "POST",
	   dataType: "json",
	   url: url,
	   data: dataObj
   }).done(function(data) {
	   if (data.result) {
		   dataJson = data.contenido.datos;
		   var contenido = "";
		   for (var info in dataJson) {
			   contenido += "<option value='" + dataJson[info].id_fuente + "'>" + dataJson[info].fuentedescription + "</option>";
		   }
		   $('#selectFuente').empty();
		   $('#selectFuente').append('<option value="0">Sin selección...</option>'+contenido);
		   $('#selectFuente').multiselect('rebuild');
	   } else {
		   console.log("ERROR Modelo");
		   console.log( JSON.stringify(data) ); 
	   }
   }).fail(function(result) {
	   console.log("ERROR");
	   console.log( result );
   });
}



