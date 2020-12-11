/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jesús Reyes Santos 
 * @version 0.1
 */
var url = "modelo/abcDescuentosModelo.php";
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
		   var columnasVisuales= [0, 1, 2, 3, 4, 5, 6, 7];
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
   

   $('#selectObjetoPrincipal').val("");
   $("#selectObjetoPrincipal").multiselect('rebuild');
   $('#selectObjetoParcial').val("");
   $("#selectObjetoParcial").multiselect('rebuild');
   $('#nuPorcentaje').val("");


   var descuentos ="";
   $('#selectDescuento').change(function(){

	descuentos = $(this).val();

	if(descuentos == 'dia'){
	// 	// $('#fechaFinal').hide(); //oculto mediante id
	// 	// $('#fechaInicio').hide();
	document.getElementById('fechaFinal').style.display = 'none';
	document.getElementById('fechaInicio').style.display = 'none';
	document.getElementById('opcionDia').style.display = 'block';
	$('#txtFechaInicial').val("");
	$('#txtFechaFinal').val("");	
	}
			
	if(descuentos == 'campaña'){
		// 	// $('#fechaFinal').hide(); //oculto mediante id
		// 	// $('#fechaInicio').hide();
	document.getElementById('fechaFinal').style.display = 'block';
	document.getElementById('fechaInicio').style.display = 'block';
	document.getElementById('opcionDia').style.display = 'none';
	$('#txtFechaInicial').val(""+fechaIni);
	$('#txtFechaFinal').val(""+fechaFin);	
	}
	
   });

   
   var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Agregar Descuento</h3>';
   $('#ModalUR_Titulo').empty();
   $('#ModalUR_Titulo').append(titulo);
   $('#ModalUR').modal('show');
}
/** Agregar nuevo registro validando que no existan campos vacios */
function fnAgregar() {
   var id_descuentos = $('#txtIdDescuentos').val();
   var loccode = $('#selectObjetoPrincipal').val();
   var id_parcial = $('#selectObjetoParcial').val();
   var porcentaje = $('#nuPorcentaje').val();
   var dtm_inicio = $('#txtFechaInicial').val();
   var dtm_fin = $('#txtFechaFinal').val();
   var tipo_descuento = $('#selectDescuento').val();
   var numDias = $('#numDias').val();
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

   if (tipo_descuento == "" || tipo_descuento == null || tipo_descuento == 0 || tipo_descuento == "0" || tipo_descuento === "undefined" || porcentaje < 1 || porcentaje > 100 || porcentaje == "" || porcentaje == null || porcentaje == 0 || porcentaje == "0" || porcentaje === "undefined" || loccode == "" || loccode == null || loccode == 0 || loccode == "0" || loccode === "undefined" || id_parcial == "" || id_parcial == null || id_parcial == 0 || id_parcial == "0" || id_parcial === "undefined") {
	   if (loccode == "" || loccode == null || loccode == 0 || loccode == "0" || loccode === "undefined" ) {
		   msg += '<p>Falta capturar la clave del objeto principal</p>';
	   }
	   if (id_parcial == "" || id_parcial == null || id_parcial == 0 || id_parcial == "0" || id_parcial === "undefined"  ) {
		   msg += '<p>Falta capturar la clave del objeto parcial </p>';
	   }
	   if (porcentaje < 1 || porcentaje > 100 || porcentaje == "" || porcentaje == null || porcentaje == 0 || porcentaje == "0" || porcentaje === "undefined" ) {
		   msg += '<p>Falta capturar el porcentaje o el porcentaje debe se de 1 a 100%</p>';
	   }
			
		if (tipo_descuento == "" || tipo_descuento == null || tipo_descuento == 0 || tipo_descuento == "0" || tipo_descuento === "undefined") {
			msg += '<p>Falta capturar el tipo de descuento</p>';
		}
	   

	   $('#divMensajeOperacion').removeClass('hide');
	   $('#divMensajeOperacion').empty();
	   $('#divMensajeOperacion').append('<div class="alert alert-danger alert-dismissable">' + msg + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
	   
   }else{
	   $('#ModalUR').modal('hide');
	   dataObj = {
		   option: 'AgregarCatalogo',
		   id_descuentos: id_descuentos,
		   loccode: loccode,
		   id_parcial: id_parcial,
		   porcentaje: porcentaje,
		   dtm_inicio: dtm_inicio,
		   dtm_fin: dtm_fin,
		   tipo_descuento: tipo_descuento,
		   numDias: numDias,
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
			   fnMostrarDatos('');
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
function fnModificar(id_descuentos,loccode,id_parcial,porcentaje,dtm_inicio,dtm_fin,tipo_descuento,numDias) {
   $('#divMensajeOperacion').addClass('hide');
   proceso = "Modificar";
//    //alert(idSubfuncion+ ' - ' +descSubfuncion+ ' - ' +idFuncion+ ' - ' +descFuncion+ ' - ' +idFinalidad+ ' - ' +descFinalidad);
//    proceso = "Modificar";

//    dataObj = {
// 	   option: 'mostrarCatalogo',
// 	   id_descuentos: id_descuentos,
// 	   loccode: loccode,
// 	   id_parcial: id_parcial,
// 	   porcentaje: porcentaje,
// 	   dtm_inicio: dtm_inicio,
// 	   dtm_fin: dtm_fin,
// 	   tipo_descuento: tipo_descuento,
// 	   numDias: numDias,
// 	   proceso: proceso
//    };
//    $.ajax({
// 	   async:false,
// 	   cache:false,
// 	   method: "POST",
// 	   dataType: "json",
// 	   url: url,
// 	   data: dataObj
//    }).done(function(data) {
// 	   if (data.result) {
// 		   info = data.contenido.datos;
// 		   for (key in info) {
			   $("#selectObjetoPrincipal").val(""+loccode);
			   $("#selectObjetoPrincipal").multiselect('rebuild');
			   $("#selectObjetoPrincipal").multiselect('disable');
			   $("#selectObjetoPrincipal").trigger('change')
			   //$(".multiselect").attr('disabled', 'disabled');
			   
			   $("#selectObjetoParcial").val(""+id_parcial);
			   $("#selectObjetoParcial").multiselect('rebuild');
			   $("#selectObjetoParcial").multiselect('disable');
			   //$(".multiselect").attr('disabled', 'disabled');
			   
			   $("#txtIdDescuentos").val(""+id_descuentos);
			   $("#nuPorcentaje").val(""+porcentaje);
			   $("#txtFechaInicial").val(""+dtm_inicio);
			   $("#txtFechaFinal").val(""+dtm_fin);
			   $("#numDias").val(""+numDias);
			   
			   
			  if(tipo_descuento == 'dia'){
				document.getElementById('fechaFinal').style.display = 'none';
				document.getElementById('fechaInicio').style.display = 'none';
				document.getElementById('opcionDia').style.display = 'block';
				$("#selectDescuento").val(""+tipo_descuento);
			   $("#selectDescuento").multiselect('rebuild');	
			  }
			  if(tipo_descuento == 'campaña'){
				document.getElementById('fechaFinal').style.display = 'block';
				document.getElementById('fechaInicio').style.display = 'block';
				document.getElementById('opcionDia').style.display = 'none';
				$("#selectDescuento").val(""+tipo_descuento);
			   $("#selectDescuento").multiselect('rebuild');	
			  }

			   var descuentos ="";
			   $('#selectDescuento').change(function(){
			
				descuentos = $(this).val();
			
				if(descuentos == 'dia'){
				// 	// $('#fechaFinal').hide(); //oculto mediante id
				// 	// $('#fechaInicio').hide();
				document.getElementById('fechaFinal').style.display = 'none';
				document.getElementById('fechaInicio').style.display = 'none';
				document.getElementById('opcionDia').style.display = 'block';	
				}
						
				if(descuentos == 'campaña'){
					// 	// $('#fechaFinal').hide(); //oculto mediante id
					// 	// $('#fechaInicio').hide();
				document.getElementById('fechaFinal').style.display = 'block';
				document.getElementById('fechaInicio').style.display = 'block';
				document.getElementById('opcionDia').style.display = 'none';	
				}
				
			   });
			   
		//    }
		   var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Modificar Fuente de Financiamiento</h3>';
		   $('#ModalUR_Titulo').empty();
		   $('#ModalUR_Titulo').append(titulo);
		   $('#ModalUR').modal('show');
	//    }
//    }).fail(function(result) {
// 	   console.log("ERROR");
// 	   console.log(result);
//    });
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

function fnEliminar(id_descuentos){
	//$("button").removeAttr('disabled');
	$("#nu_id_descuentos").val(""+id_descuentos);

	var mensaje = 'Desea eliminar el descuento ';
	$('#ModalUREliminar_Mensaje').empty();
    $('#ModalUREliminar_Mensaje').append(mensaje);
	$('#ModalUREliminar').modal('show');
}

function fnEliminarEjecuta(){

	var id_descuentos = $('#nu_id_descuentos').val();
	$('#ModalUREliminar').modal('hide');
		dataObj = { 
	        option: 'eliminarUR',
	        id_descuentos: id_descuentos
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
	tipoParcial: $("#selectParcial").val(),
	tipoAlmacen: tipoAlmacen
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
	var columnasVisuales= [0, 1, 2, 3, 4, 5, 6, 7];
	fnAgregarGrid_Detalle(dataFuncionJason, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);
}
})
.fail(function(result) {
console.log("ERROR");
console.log( result ); 
});	


}
