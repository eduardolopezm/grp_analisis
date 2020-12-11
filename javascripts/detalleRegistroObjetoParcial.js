/**
 * @fileOverview Libreria con funciones de utilidad
 *
 * @author Jesús Reyes Santos 
 * @version 0.1
 */
var url = "modelo/detalleRegistroObjetoParcialModelo.php";
var proceso = "";
var anyo = "";

// objetos de configuración
window.uePorUsuario = new Array();
window.cuentasTotales = new Array();
window.cuentasBancarias = new Array();

if(typeof(Storage)!=="undefined"){
    window.uePorUsuario = ( typeof(window.localStorage.uePorUsuario)!=="undefined" ? JSON.parse(window.localStorage.uePorUsuario) : window.uePorUsuario );
    //window.cuentasTotales = ( typeof(window.localStorage.cuentasTotales)!=="undefined" ? JSON.parse(window.localStorage.cuentasTotales) : window.cuentasTotales );
    //window.cuentasBancarias = ( typeof(window.localStorage.cuentasBancarias)!=="undefined" ? JSON.parse(window.localStorage.cuentasBancarias) : window.cuentasBancarias );
}
window.busquedaPorUsuario = false;
window.listadoHabilitado = true;

window.buscadores = [ "txtCuentaCargo", "txtCuentaAbono","txtCuentaCargoBuscador", "txtCuentaAbonoBuscador", "txtClavePresupuestal" ];
window.buscadoresConfiguracion = {};
$.each(window.buscadores,function(index,valor){
    window.buscadoresConfiguracion[valor] = {};
});
window.buscadoresConfiguracion.txtCuentaCargo.origenDatos = "cuentasTotales";
window.buscadoresConfiguracion.txtCuentaAbono.origenDatos = "cuentasTotales";
window.buscadoresConfiguracion.txtCuentaCargo.cuentasAprobadas = ['1','2'];
window.buscadoresConfiguracion.txtCuentaAbono.cuentasAprobadas = ['1','2'];


//Busqueda Filtro

window.buscadoresConfiguracion.txtCuentaCargoBuscador.origenDatos = "cuentasTotales";
window.buscadoresConfiguracion.txtCuentaAbonoBuscador.origenDatos = "cuentasTotales";
window.buscadoresConfiguracion.txtCuentaCargoBuscador.cuentasAprobadas = ['1','2'];
window.buscadoresConfiguracion.txtCuentaAbonoBuscador.cuentasAprobadas = ['1','2'];



window.propiedadesResize = {
                                widthsEspecificos: {
                                    categoryid:		"7%",
                                    nu_tipo_gasto:	"5%",
                                    modificar:		"7%",
                                    eliminar: "7%"
                                },
                                encabezadosADosRenglones: {
                                    categoryid:			"Clasificador <br>Rubro Ingreso",
                                    nu_tipo_gasto:		"Tipo<br />Gasto"
                                },
                                camposConWidthAdicional: window.buscadores
                            };


$(document).ready(function() {
   //Mostrar Catalogo
   fnClaveFuncion();
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
function fnMostrarDatos(id_nu_objeto_detalle,stockid,loccode) {

   dataObj = {
	   option: 'mostrarCatalogo',
	   id_nu_objeto_detalle: id_nu_objeto_detalle,
	   stockid: stockid,
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
		   var columnasExcel= [0, 1, 2, 3, 4, 5, 6, 7];
		   var columnasVisuales= [0, 1, 2, 3, 4, 5, 6, 7, 8];
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
   anyo = $('#ano').val();

   $('#selectObjetoPrincipal').val("");
   $("#selectObjetoPrincipal").multiselect('rebuild');
   $('#selectObjetoParcial').val("");
   $("#selectObjetoParcial").multiselect('rebuild');
   $("#txtAno").val(""+anyo);
   $('#txtClavePresupuestal').val("");
   $("#txtClavePresupuestal").multiselect('rebuild');
   $('#txtCuentaBanco').val("");
   $("#txtCuentaBanco").multiselect('rebuild');
   $('#txtCuentaAbono').val("");
   $('#txtCuentaCargo').val("");
   
   var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Agregar Detalle Maestro del Objeto Parcial</h3>';
   $('#ModalUR_Titulo').empty();
   $('#ModalUR_Titulo').append(titulo);
   $('#ModalUR').modal('show');
}
/** Agregar nuevo registro validando que no existan campos vacios */
function fnAgregar() {
   var idDetalle = $('#txtIdDetalle').val();
   var finalidad = $('#selectObjetoPrincipal').val();
   var funcion = $('#selectObjetoParcial').val();
   var clave = $('#txtAno').val();
   var descripcion = $('#txtClavePresupuestal').val();
   var banco = $('#txtCuentaBanco').val();
   var abono = $('#txtCuentaAbono').val();
   var cargo = $('#txtCuentaCargo').val();
   var estatus = $('#txtEstatus').val();
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
   var fecha = new Date();
   var anyo = fecha.getFullYear();

   if (finalidad == "" || finalidad == null || finalidad == 0 || finalidad == "0" || finalidad === "undefined" || funcion == "" || funcion == null || funcion == 0 || funcion == "0" || funcion === "undefined"|| clave == ""  || abono!="" && descripcion != "" || cargo!="" && descripcion != "" || abono=="" && descripcion == "" || cargo=="" && descripcion == "" || banco == "" || banco == null || banco == 0 || banco == "0" || banco === "undefined" || estatus=="") {
	   if (finalidad == "" || finalidad == null || finalidad == 0 || finalidad == "0" || finalidad === "undefined" ) {
		   msg += '<p>Falta capturar la clave del objeto principal</p>';
	   }
	   if (funcion == "" || funcion == null || funcion == 0 || funcion == "0" || funcion === "undefined"  ) {
		   msg += '<p>Falta capturar la clave del objeto parcial </p>';
	   }
	   if (clave == "" ) {
		   msg += '<p>Falta capturar el año</p>';
	   }
		if (abono=="" && descripcion == "") {
			msg += '<p>Falta capturar la cuenta de abono </p>';
			}
		
		if (abono!="" && descripcion != "") {
			msg += '<p>Si existe una clave presupuestal no puede llevar cuenta de abono </p>';
			}
			
		if (cargo=="" && descripcion == "" ) {
		msg += '<p>Falta capturar la cuenta de cargo</p>';
		}

		if (cargo!="" && descripcion != "" ) {
			msg += '<p>Si existe una clave presupuestal no puede llevar cuenta de cargo</p>';
			}
	   
	   
	   if (banco == "" || banco == null || banco == 0 || banco == "0" || banco === "undefined" ) {
		msg += '<p>Falta capturar la cuenta de banco</p>';
	   }
	   
		if (estatus=="" ) {
			msg += '<p>Falta capturar el estatus </p>';
		}
		
	   

	   $('#divMensajeOperacion').removeClass('hide');
	   $('#divMensajeOperacion').empty();
	   $('#divMensajeOperacion').append('<div class="alert alert-danger alert-dismissable">' + msg + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
	   
   }else{
	   $('#ModalUR').modal('hide');
	   dataObj = {
		   option: 'AgregarCatalogo',
		   idDetalle: idDetalle,
		   funcion: funcion,
		   finalidad: finalidad,
		   clave: clave,
		   descripcion: descripcion,
		   banco: banco,
		   abono: abono,
		   cargo: cargo,
		   estatus: estatus,
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
function fnModificar(id_detalle,ano,clave_presupuestal,cuenta_banco,cuenta_abono,cuenta_cargo,estatus,loccode,locationname,stockid,description) {
   $('#divMensajeOperacion').addClass('hide');
    proceso = "Modificar";
	// console.log(id_detalle,ano,clave_presupuestal,cuenta_banco,cuenta_abono,cuenta_cargo,estatus,loccode,locationname,stockid,description);
//    dataObj = {
// 	   option: 'mostrarCatalogo',
// 	   id_detalle: id_detalle,
// 	   ano: ano,
// 	   clave_presupuestal: clave_presupuestal,
// 	   cuenta_banco: cuenta_banco,
// 	   cuenta_abono: cuenta_abono,
// 	   cuenta_cargo: cuenta_cargo,
// 	   estatus: estatus,
// 	   loccode: loccode,
// 	   locationname: locationname,
// 	   stockid: stockid,
// 	   description: description
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
			   
			   $("#selectObjetoParcial").val(""+stockid);
			   $("#selectObjetoParcial").multiselect('rebuild');
			   $("#selectObjetoParcial").multiselect('disable');
			   //$(".multiselect").attr('disabled', 'disabled');
			
			   $("#txtIdDetalle").val(""+id_detalle);
			   $("#txtAno").val(""+ano);
			   $("#txtAno").trigger('change')
			   $("#txtClavePresupuestal").val(""+clave_presupuestal);
			   $("#txtClavePresupuestal").multiselect('rebuild');
			   $("#txtClavePresupuestal").multiselect('enable');
			   $("#txtCuentaBanco").val(""+cuenta_banco);
			   $("#txtCuentaBanco").multiselect('rebuild');
			   $("#txtCuentaBanco").multiselect('enable');
			   $("#txtCuentaAbono").val(""+cuenta_abono);
			   $("#textoVisible__txtCuentaAbono").val(""+cuenta_abono);
			   $("#txtCuentaCargo").val(""+cuenta_cargo);
			   $("#textoVisible__txtCuentaCargo").val(""+cuenta_cargo);
			   $("#txtEstatus").val(""+estatus);
			   $("#txtEstatus").multiselect('rebuild');
			   
		//    }
		   var titulo = '<h3><span class="glyphicon glyphicon-info-sign"></span> Modificar Fuente de Financiamiento</h3>';
		   $('#ModalUR_Titulo').empty();
		   $('#ModalUR_Titulo').append(titulo);
		   $('#ModalUR').modal('show');
// 	   }
//    })
//    .fail(function(result) {
// 	   console.log("ERROR");
// 	   console.log(result);
//    });
}

function fnFinalidad() {
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

function fnClaveFuncion() {
	var id_identificacion = $("#ano").val();
	// var loccode = $("#selectObjetoPrincipal").val();
	// var accountcode = $("#txtCuentaBanco").val();
	dataObj = {
		option: 'mostrarClaveFuncion',
		id_identificacion: id_identificacion,
		// loccode: loccode,
		// accountcode: accountcode, 
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
				contenido += "<option value='" + dataJson[info].id_fuente + "'>" + dataJson[info].descripcion + "  " +dataJson[info].nombr + "</option>";
			}
			
			$('#txtClavePresupuestal').empty();
			$('#txtClavePresupuestal').append('<option value="">Seleccione un año...</option>'+contenido);
			$('#txtClavePresupuestal').multiselect('rebuild');


		} else {
			console.log("ERROR Modelo");
			console.log( JSON.stringify(data) ); 
		}
	}).fail(function(result) {
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
	tipoAnio: $("#txtAnio").val(),
	tipoParcial: $("#selectParcial").val(),
	tipoAlmacen: tipoAlmacen
  };
  console.log('tipoAlmacen'+tipoAlmacen);
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
	var columnasExcel= [0, 1, 2, 3, 4, 5, 6, 7];
	var columnasVisuales= [0, 1, 2, 3, 4, 5, 6, 7, 8];
	fnAgregarGrid_Detalle(dataFuncionJason, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);
}
})
.fail(function(result) {
console.log("ERROR");
console.log( result ); 
});	

}
