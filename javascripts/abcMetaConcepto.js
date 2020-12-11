/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jesús Reyes Santos 
 * @version 0.1
 */
var url = "modelo/abcMetaConceptoModelo.php";
var proceso = "";
 
var fechaIni;
var fechaFin;



$(document).ready(function() {
   //Mostrar Catalogo
   fechaIni =$('#txtFechaInicial').val();
   fechaFin =$('#txtFechaFinal').val();
   fnMostrarDatos('');

});

/**
* Muestra la información del catalogo completo o de forma individual
* @param  {String} ur Código del Registro para obtener la información
*/
function fnMostrarDatos(loccode) {

   dataObj = {
	   option: 'mostrarCatalogo',
	   loccode: loccode
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
		   var columnasExcel= [0, 1, 2, 3, 4, 5];
		   var columnasVisuales= [0, 1, 2, 3, 4, 5];
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
   $('#txtIdMeta').val("");
   $('#porMeta').val("");
   $('#selectObjetoPrincipalUsuarios').val("");
   $("#selectObjetoPrincipalUsuarios").multiselect('rebuild');

   $('#selectMes').val("");
   $("#selectMes").multiselect('rebuild');


   var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Agregar Concepto por Meta</h3>';
   $('#ModalUR_Titulo').empty();
   $('#ModalUR_Titulo').append(titulo);
   $('#ModalUR').modal('show');
}
/** Agregar nuevo registro validando que no existan campos vacios */
function fnAgregar() {
	muestraCargandoGeneral();
   var id_meta = $('#txtIdMeta').val();
   var loccode = $('#selectObjetoPrincipalUsuarios').val();
   var nu_mes = $('#selectMes').val();
   var anio = $('#nuAnio').val();
   var meta = $('#porMeta').val();
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

   if (anio == "" || anio == null || anio == 0 || anio == "0" || anio === "undefined" || nu_mes == "" || nu_mes == null || nu_mes == 0 || nu_mes == "0" || nu_mes === "undefined" || loccode == "" || loccode == null || loccode == 0 || loccode == "0" || loccode === "undefined" || meta < 1 || meta > 100 || meta == "" || meta == null || meta == 0 || meta == "0" || meta === "undefined") {
	   if (loccode == "" || loccode == null || loccode == 0 || loccode == "0" || loccode === "undefined" ) {
		   msg += '<p>Falta capturar la clave del objeto principal</p>';
	   }
	   if ( meta < 1 || meta > 100 || meta == "" || meta == null || meta == 0 || meta == "0" || meta === "undefined"  ) {
		   msg += '<p>Falta capturar el porcentaje meta, el rango debe ser de 1 a 100%</p>';
	   }
	   if (anio == "" || anio == null || anio == 0 || anio == "0" || anio === "undefined" ) {
		   msg += '<p>Falta capturar el año</p>';
	   }
			
		if (nu_mes == "" || nu_mes == null || nu_mes == 0 || nu_mes == "0" || nu_mes === "undefined") {
			msg += '<p>Falta capturar el mes</p>';
		}
	   

	   $('#divMensajeOperacion').removeClass('hide');
	   $('#divMensajeOperacion').empty();
	   $('#divMensajeOperacion').append('<div class="alert alert-danger alert-dismissable">' + msg + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
	   ocultaCargandoGeneral();
   }else{
	   $('#ModalUR').modal('hide');
	   dataObj = {
		   option: 'AgregarCatalogo',
		   id_meta: id_meta,
		   loccode: loccode,
		   meta: meta,
		   anio: anio,
		   nu_mes: nu_mes,
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
		ocultaCargandoGeneral();
		   if (data.result) {
			   muestraMensaje(data.contenido, 1, 'divMensajeOperacion', 5000);
			   fnLimpiarTabla('divTabla', 'divContenidoTabla');
			   fnMostrarDatos('');
		   } else {
			   muestraMensaje(data.contenido, 3, 'divMensajeOperacion', 5000);
		   }
	   }).fail(function(result) {
		ocultaCargandoGeneral();
		   console.log("ERROR");
		   console.log(result);
	   });
   }
}
/**
* Muestra formulario para modificar la información del registro seleccionado
* @param  {String} ur Código del Registro para obtener la información
*/
function fnModificar(id_meta,loccode,nu_mes,anio,meta) {
   $('#divMensajeOperacion').addClass('hide');
   proceso = "Modificar";
			   $("#selectObjetoPrincipalUsuarios").val(""+loccode);
			   $("#selectObjetoPrincipalUsuarios").multiselect('rebuild');
			   $("#selectMes").val(""+nu_mes);
			   $("#selectMes").multiselect('rebuild');
			   
			   $("#txtIdMeta").val(""+id_meta);
			   $("#nuAnio").val(""+anio);
			   $("#porMeta").val(""+meta);

		   var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Modificar Concepto por Meta</h3>';
		   $('#ModalUR_Titulo').empty();
		   $('#ModalUR_Titulo').append(titulo);
		   $('#ModalUR').modal('show');

}

function fnFinalidad() {
	var id_identificacion = $("#selectPrincipal").val();
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
			$('#selectParcial').empty();
			$('#selectParcial').append('<option value="0">Seleccione un objeto pricipal...</option>'+contenido);
			$('#selectParcial').multiselect('rebuild');
		} else {
			console.log("ERROR Modelo");
			console.log( JSON.stringify(data) ); 
		}
	}).fail(function(result) {
		console.log("ERROR");
		console.log( result );
	});
 }

function fnFinalidadFuncion() {
   var id_identificacion = $("#selectObjetoPrincipal").val();
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
		   $('#selectObjetoParcial').empty();
		   $('#selectObjetoParcial').append('<option value="0">Seleccione un objeto pricipal...</option>'+contenido);
		   $('#selectObjetoParcial').multiselect('rebuild');
	   } else {
		   console.log("ERROR Modelo");
		   console.log( JSON.stringify(data) ); 
	   }
   }).fail(function(result) {
	   console.log("ERROR");
	   console.log( result );
   });
}

function fnEliminar(id_meta){
	//$("button").removeAttr('disabled');
	$("#nu_id_meta").val(""+id_meta);

	var mensaje = '¿Desea eliminar el concepto?';
	$('#ModalUREliminar_Mensaje').empty();
    $('#ModalUREliminar_Mensaje').append(mensaje);
	$('#ModalUREliminar').modal('show');
}

function fnEliminarEjecuta(){

	var id_meta = $('#nu_id_meta').val();
	$('#ModalUREliminar').modal('hide');
		dataObj = { 
	        option: 'eliminarUR',
	        id_meta: id_meta
	     };
		$.ajax({
			  async:false,
		      cache:false,
		      method: "POST",
		      dataType:"json",
		      url: url,
		      data:dataObj
		  })
		.done(function( data ) {
		    if(data.result){
				muestraMensaje(data.contenido, 1, 'divMensajeOperacion', 5000);
				fnLimpiarTabla('divTabla', 'divContenidoTabla');
		    	fnMostrarDatos('','');
		    }else{
		    	muestraMensaje(data.contenido, 3, 'divMensajeOperacion', 5000);
		    }
		})
		.fail(function(result) {
			console.log("ERROR");
		    console.log( result );
		});
}
 
 
 function fnBuscar() {
	
	var tipoAlmacen = ""; 
	//var tipoParcial = ""; 
var selectTipoAlmacen = document.getElementById('selectPrincipal');
for ( var i = 0; i < selectTipoAlmacen.selectedOptions.length; i++) {
	//console.log( unidadesnegocio.selectedOptions[i].value);
	if (i == 0) {
		tipoAlmacen = "'"+selectTipoAlmacen.selectedOptions[i].value+"'";
	}else{
		tipoAlmacen = tipoAlmacen+", '"+selectTipoAlmacen.selectedOptions[i].value+"'";
	}
}
/*var selectTipoParcial = document.getElementById('selectParcial');
for ( var i = 0; i < selectTipoParcial.selectedOptions.length; i++) {
	//console.log( unidadesnegocio.selectedOptions[i].value);
	if (i == 0) {
		tipoParcial = "'"+selectTipoParcial.selectedOptions[i].value+"'";
	}else{
		tipoParcial = tipoParcial+", '"+selectTipoParcial.selectedOptions[i].value+"'";
	}
}*/


//console.log("fnMostrarDatos");
//$("button").removeAttr('disabled');
dataObj = { 
	option: 'obtenerInformacion',
	nu_mes: $("#selectM").val(),
	nu_anio: $("#nuAn").val(),
	objPrincipal: tipoAlmacen
  };
  
  //console.log('tipoParcial'+tipoParcial);
//Obtener datos de las bahias
$.ajax({
  async:false,
  cache:false,
  method: "POST",
  dataType:"json",
  url: url,
  data:dataObj
})
.done(function( data ) {
//console.log("Bien");
if(data.result){
	//Si trae informacion
	dataFuncionJason = data.contenido.datos;
	columnasNombres = data.contenido.columnasNombres;
	columnasNombresGrid = data.contenido.columnasNombresGrid;
	
	fnLimpiarTabla('divTabla', 'divContenidoTabla');

	var nombreExcel = data.contenido.nombreExcel;
	var columnasExcel= [0, 1, 2, 3, 4, 5,];
	var columnasVisuales= [0, 1, 2, 3, 4, 5];
	fnAgregarGrid_Detalle(dataFuncionJason, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);
}
})
.fail(function(result) {
console.log("ERROR");
console.log( result ); 
});	


}
