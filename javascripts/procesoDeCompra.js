$(document).ready(function() {
	inicioPanel();

	// comportamiento de guardado
	$('#btnSearch').on('click', function(){
		fnObtenerInformacionTablas();
	});

	$("#fechaIni").parent().on('dp.change', fnCambioDeFecha);
	$("#fechaFin").parent().on('dp.change', fnCambioDeFecha);
	fnForzarCambioDeFecha('fechaIni');
});

function inicioPanel(){
	window.modelo = 'modelo/procesoDeCompraModelo.php';
	window.home = 'procesoDeCompra.php'
	window.tituloGeneral = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';

	window.propiedadesResize = {
									widthsEspecificos: {
										check:			"5%",
										ur:				"10%",
										ue:				"10%",
										//imprimir:		"7%"
									},
									encabezadosADosRenglones: {
										fechaCaptura:		"Fecha<br />Requisición",
										folioRequisicion:	"Folio<br />Requisición",
										folio:				"Folio",
										fechaConvocatoria:	"Fecha<br />Convocatoria",
										fechaFirma:			"Fecha<br />Firma",
										tipoExpediente:		"Tipo de<br>Expediente",
										montoTotal:			"Monto<br />Total"
									},
									camposConWidthAdicional: [ "descripcion" ]
								};

	cargaSelects();
	fnObtenerInformacionTablas();
	fnObtenerBotones_Funcion('areaBotones',nuFuncion);
	$(document).on('click','#Rechazar, #Avanzar, #Autorizar, #Cancelar, #Solicitar, #Reversar',changeNewStatus);
}

function fnCambiarEstatus(){}
function changeNewStatus(){
	var el = $(this), selections = getSelects('tablaCompras'), $flag = 0;
	var mensajeEstandarizado = ( selections.length>1 ? 'los' : 'el' )+' proceso'+( selections.length>1 ? 's' : '' )+' de compra seleccionado'+( selections.length>1 ? 's' : '' );
	if(selections.length!=0){
		// autorizar
		if(el.attr('id') == 'Autorizar'){
			var noChange = [3,4];
			$.each(selections, function(ind, row) {
				if(noChange.indexOf(row.idStatus)>0){
					$flag++;
					muestraModalGeneral(3,window.tituloGeneral,'Los registros con estatus Concluido y Cancelado no pueden ser Autorizados.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
				}
			});
			if($flag == 0){
				var titulo = 'Confirmación', mensaje = '¿Estás seguro de autorizar '+mensajeEstandarizado+'?';
				muestraModalGeneralConfirmacion(3,titulo,mensaje,'','autoriza()');
			}
		}
		// cancelar
		else if(el.attr('id') == 'Cancelar'){
			var noCancel = [3].concat(window.noPermitidos), $flag=0;
			$.each(selections, function(ind, row) {
				if(noCancel.indexOf(row.idStatus)>0){
					$flag++;
					muestraModalGeneral(3,window.tituloGeneral,'No cuenta con los permisos para cancelar los anexos solicitados.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
				}
			});
			if($flag == 0){
				var titulo = 'Confirmación', mensaje = '¿Estás seguro de cancelar '+mensajeEstandarizado+'?';
				muestraModalGeneralConfirmacion(3,titulo,mensaje,'','cancela()');
			}
		}
		// rechazar
		else if(el.attr('id') == 'Rechazar'){
			var noChange = window.noPermitidos, flag = 0;
			noChange.push(1);
			$.each(selections, function(index, row) {
				if(noChange.indexOf(row.idStatus)>0){
					flag++;
					muestraModalGeneral(3,window.tituloGeneral,'No puede rechazar '+mensajeEstandarizado+'.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
				}
			});
			if(flag==0){
				var titulo = 'Confirmación', mensaje = '¿Estás seguro de rechazar '+mensajeEstandarizado+'?';
				muestraModalGeneralConfirmacion(3,titulo,mensaje,'','rechaza()');
				noChange.splice(noChange.indexOf(1),1);
			}
		}
		// avanzar
		else if(el.attr('id') == 'Avanzar'){
			var noChange = window.noPermitidos, flag = 0;
			$.each(selections, function(index, row) {
				if(noChange.indexOf(row.idStatus)>0){
					flag++;
					muestraModalGeneral(3,window.tituloGeneral,'No puede avanzar '+mensajeEstandarizado+'.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
				}
			});
			if(flag==0){
				var titulo = 'Confirmación', mensaje = '¿Estás seguro de avanzar '+mensajeEstandarizado+'?';
				muestraModalGeneralConfirmacion(3,titulo,mensaje,'','avanza()');
			}
		}
		// reversar
		else if(el.attr('id') == 'Reversar'){
			var noChange = [3,4], flag = 0;
			$.each(selections, function(index, row) {
				if(noChange.indexOf(row.idStatus)>0){
					flag++;
					muestraModalGeneral(3,window.tituloGeneral,'No puede reversar '+mensajeEstandarizado+'.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
				}
			});
			if(flag==0){
				var titulo = 'Confirmación', mensaje = '¿Estás seguro de reversar '+mensajeEstandarizado+'?';
				muestraModalGeneralConfirmacion(3,titulo,mensaje,'','reversa()');
			}
		}
	}else{
		muestraModalGeneral(3,window.tituloGeneral,'No ha seleccionado ningún elemento.','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
	}
}

function reversa(){
	var selections = getSelects('tablaCompras');
	/////muestraPrevioCargandoGeneral()
	var estotusPerfil = window.profile==11 ? 1 : (window.profile==10 ? 1 : 1 );
	$.ajaxSetup({async: false});
	$.post(window.modelo, {method:'reversar', rows:selections})
	.then(function(res){
		/////ocultaCargandoGeneral();
		/////$('div').removeClass("modal-backdrop");
		var mensaje = '';
		if(res.success){
			fnObtenerInformacionTablas();
			muestraModalGeneral(3,'Operación Exitosa',res.msg,'<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
		}else{
			muestraModalGeneral(3,window.tituloGeneral,res.msg,'<button type="button" class="btn botonVerde" data-dismiss="modal" onClick="delFadein()">Aceptar</button>');
		}
	});
}

function rechaza(){
	var selections = getSelects('tablaCompras');
	/////muestraPrevioCargandoGeneral()
	var estotusPerfil = window.profile==11 ? 1 : (window.profile==10 ? 1 : 1 );
	$.ajaxSetup({async: false});
	$.post(window.modelo, {method:'updateStatus', rows:selections, type:estotusPerfil})
	.then(function(res){
		/////ocultaCargandoGeneral();
		/////$('div').removeClass("modal-backdrop");
		var mensaje = '';
		if(res.success){
			selections.forEach(function(el){
				mensaje += 'Se ha rechazado la solicitud '+el.folioTexto+' '+(window.profile==11?'al Validador.':window.profile==10?'al Capturista':'')+'<br />';
			});
			fnObtenerInformacionTablas();
			muestraModalGeneral(3,'Operación Exitosa',mensaje,'<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
		}else{
			muestraModalGeneral(3,window.tituloGeneral,res.msg,'<button type="button" class="btn botonVerde" data-dismiss="modal" onClick="delFadein()">Aceptar</button>');
		}
	});
}

function avanza(){
	var selections = getSelects('tablaCompras');
	/////muestraPrevioCargandoGeneral();
	var estotusPerfil = window.profile==9 ? 2 : ( window.profile==10 ? 2 : ( window.profile==11 ? 2 : 2 ) );
	$.ajaxSetup({async: false});
	$.post(window.modelo, {method:'updateStatus', rows:selections, type:estotusPerfil})
	.then(function(res){
		/////ocultaCargandoGeneral();
		/////$('div').removeClass("modal-backdrop");
		if(res.success){
			var tipoAl = 1, mensaje='';
			selections.forEach(function(el){
				mensaje += 'Se avanzó la solicitud '+el.folioTexto+' '+(window.profile==9?'al Validador.':window.profile==10?'al Autorizador':'')+'<br />';
			});
			fnObtenerInformacionTablas();
			muestraModalGeneral(3,'Operación Exitosa',mensaje,'<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
		}else{
			muestraModalGeneral(3,window.tituloGeneral,res.msg,'<button type="button" class="btn botonVerde" data-dismiss="modal" onClick="delFadein()">Aceptar</button>');
		}
	});
}

function autoriza(){
	var selections = getSelects('tablaCompras');
	/////muestraPrevioCargandoGeneral();
	$.ajaxSetup({async: false});
	$.post(window.modelo, {method:'updateStatus', rows:selections, type: 3 })
	.then(function(res){
		/////ocultaCargandoGeneral();
		/////$('div').removeClass("modal-backdrop");
		if(res.success){
			var mensaje='';
			selections.forEach(function(el){
				mensaje += 'La solicitud '+el.folioTexto+' ha sido autorizada. <br />';
			});
			fnObtenerInformacionTablas();
			// muestraModalGeneral(3,'Operacion Exitosa','Se realizaron los cambios solicitados','<button type="button" class="btn botonVerde" data-dismiss="modal">Aceptar</button>');
			muestraModalGeneral(3,'Operación Exitosa',mensaje,'<button type="button" class="btn botonVerde" data-dismiss="modal" onClick="delFadein()">Aceptar</button>');
		}else{
			muestraModalGeneral(3,window.tituloGeneral,res.msg,'<button type="button" class="btn botonVerde" data-dismiss="modal" onClick="delFadein()">Aceptar</button>');
		}
	});
}

function cancela(){
	var selections = getSelects('tablaCompras');
	/////muestraPrevioCargandoGeneral();
	$.ajaxSetup({async: false});
	$.post(window.modelo, {method:'updateStatus', rows:selections, type: 4})
	.then(function(res){
		/////ocultaCargandoGeneral();
		/////$('div').removeClass("modal-backdrop");
		if(res.success){
			var mensaje='';
			selections.forEach(function(el){
				mensaje += 'Se ha cancelado la solicitud '+el.folioTexto+'. <br />';
			});
			fnObtenerInformacionTablas();
			muestraModalGeneral(3,'Operación Exitosa',mensaje,'<button type="button" class="btn botonVerde" data-dismiss="modal" onClick="delFadein()">Aceptar</button>');
		}else{
			muestraModalGeneral(3,window.tituloGeneral,res.msg,'<button type="button" class="btn botonVerde" data-dismiss="modal" onClick="delFadein()">Aceptar</button>');
		}
	});
}

function getSelects(tbl,filedata='check') {
	var $tbl = $('#'+tbl), rows = [], len = i=0, infTbl;
	infTbl = $tbl.jqxGrid('getdatainformation');
	len = infTbl.rowscount;
	for (;i<len;i++) {
		var data = $tbl.jqxGrid('getrowdata',i);
		if(data[filedata]){ rows.push(data); }
	}
	return rows;
}

function getNombreSeleccion(datos, filedata='id') {
	var nombres = '';
	$.each(datos, function(index, val) {
		nombres += (index!=0?', ':'') + val[filedata];
	});
	return nombres;
}

function cargaSelects(){
	$.ajaxSetup({async: false});
	$.post('modelo/oficioCompraModelo.php', {method:'cargaSelects'}).then(function(res){
		if(!!res.selectExpediente){
			$.each(res.selectExpediente, function(index, val) {
				$('#tipoExpediente').append(new Option(val.label, val.value));
			});
		}
		fnFormatoSelectGeneral(".tipoExpediente");
	});
	fnFormatoSelectGeneral(".selectEstatus");
}

function fnObtenerInformacionTablas(){
	var params = getParams('form-search');

	params.method = 'listaDeDocumentos';

	$.ajax({
		type:			'POST',
		dataType:		'JSON',
		url:			window.modelo,
		data:			params,
		async:			false
	})
	.then(function(res){
		if(res.success){
			fnLlenaTabla('tablaCompras',res.content);
		}else{
			fnLlenaTabla();
		}
		window.noPermitidos = res.noPermitidos;
		window.profile = res.perfil; 
	});
}

function fnLlenaTabla(tabla='tablaCompras',datos=[]){
	// declaración de variables principales
	var contenedor = 'datosCompras', nombreExcel = 'Compras',
		tblObj, tblTitulo, tblExcel=[1,2,3,4,6,7,8,9,10,11], tblVisual=[0,1,2,3,4,5,6,7,8,9,10,11,12];
	tblObj = [
		{ name: 'check', type: 'bool'},// 0
		{ name: 'ur', type: 'string'},// 1
		{ name: 'ue', type: 'string'},// 2
		{ name: 'fechaCaptura', type: 'string'},// 3
		{ name: 'folioRequisicion', type: 'string'},// 4 
		{ name: 'folio', type: 'string'},// 5
		{ name: 'folioTexto', type: 'string'},// 6
		{ name: 'fechaConvocatoria', type: 'string'},// 7
		{ name: 'fechaFirma', type: 'string'},// 8
		{ name: 'tipoExpediente', type: 'string'},// 9
		{ name: 'estatus', type: 'string'},// 10
		{ name: 'descripcion', type: 'string'},// 11
		{ name: 'montoTotal', type: 'string'},// 12
		//{ name: 'imprimir', type: 'string'},// 13
		{ name: 'idStatus', type: 'string'},// 14
		{ name: 'folioTexto', type: 'string'},// 15
		{ name: 'identificador', type: 'string'},// 16
	];
	tblTitulo = [
		{ text:'Sel', datafield:'check', columntype: 'checkbox', width: '5%', cellsalign: 'center', align: 'center' },// 0
		{ text: 'UR', datafield: 'ur', editable: false, width: '11%', cellsalign: 'center', align: 'center' },// 1
		{ text: 'UE', datafield: 'ue', editable: false, width: '11%', cellsalign: 'center', align: 'center' },// 2
		{ text: 'Fecha Requisición', datafield: 'fechaCaptura', editable: false, width: '5%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Folio Requisición', datafield: 'folioRequisicion', editable: false, width: '11%', cellsalign: 'center', align: 'center' },// 4
		{ text: 'Folio Compra', datafield: 'folio', editable: false, width: '11%', cellsalign: 'center', align: 'center' },// 5
		{ text: 'Folio Compra', datafield: 'folioTexto', editable: false, width: '11%', cellsalign: 'center', align: 'center', hidden:true },// 6
		{ text: 'Fecha Convocatoria', datafield: 'fechaConvocatoria', editable: false, width: '5%', cellsalign: 'center', align: 'center' },// 7
		{ text: 'Fecha Firma', datafield: 'fechaFirma', editable: false, width: '5%', cellsalign: 'center', align: 'center' },// 8
		{ text: 'Tipo de Expediente', datafield: 'tipoExpediente', editable: false, width: '11%', cellsalign: 'center', align: 'center' },// 9
		{ text: 'Estatus', datafield: 'estatus', editable: false, width: '11%', cellsalign: 'center', align: 'center' },// 10
		{ text: 'Descripción', datafield: 'descripcion', editable: false, width: '18%', cellsalign: 'center', align: 'center' },// 11
		{ text: 'Monto Total', datafield: 'montoTotal', editable: false, width: '7%', cellsalign: 'center', align: 'center' },// 12
		//{ text: 'Imprimir', datafield: 'imprimir', editable: false, width: '7%', cellsalign: 'center', align: 'center' },// 13
	];
	// llamado de limpieza de la tabla
	fnLimpiarTabla(contenedor,tabla);
	// render de la tabla
	fnAgregarGrid_Detalle_nostring(datos, tblObj, tblTitulo, tabla, "", 1, tblExcel, false, true, "", tblVisual, nombreExcel);

	// resize del ancho de las columnas del Grid cada vez que hay cambio de página
	$("#"+tabla).bind("pagechanged", function (event) {
		if(typeof window.propiedadesResize != "undefined"){
			fnGridResize(tabla,window.propiedadesResize);
		}
	});
	if(typeof window.propiedadesResize != "undefined"){
		fnGridResize(tabla,window.propiedadesResize);
	}
}

/**************************************** FUNCIONES GENERALES ****************************************/

function fnCambioDeFecha(e){
	var campoActivo = $(this).find('input').prop("id");

	if(campoActivo=="fechaIni"){
		// Se forza que la fecha mínima del campo fechaFin sea la fecha del campo fechaIni, sólo cuando fechaIni tuvo cambios
		$('#fechaFin').parent().data('DateTimePicker').minDate($('#'+campoActivo).val());
	}
	if(fnFechaMenorQue($('#fechaFin').val(),$('#fechaIni').val())){
		// Se hacen los ajustes necesarios si el campo fechaFin tiene una fecha menor a la indicada en fechaIni
		$('#fechaFin').val($('#fechaIni').val());
	}
}

// Funciones de fechas
// Convierte strings dd-mm-YYYY, dd/mm/YYYY, dd mm YYYY, YYYY-mm-dd, YYYY/mm/dd y YYYY mm dd a variables Date
function fnCambiaFechaDeStringAVariableDate(fecha){
	fecha = ( fecha.split("-").length==3 ? fecha.split("-") : ( fecha.split("/").length==3 ? fecha.split("/") : fecha.split(" ") ) );
	if(fecha.length!=3){
		return false;
	}
	fecha = ( fecha[0].length==4 ? fecha.join("-") : ( fecha[2].length==4 ? fecha.reverse().join("-") : false ) );

	return ( fecha!=false ? new Date( fecha ) : fecha );
}

// Compara fechas en formato string, regresa true, false o -1 
function fnFechaMenorQue(fecha1,fecha2){
	fecha1 = fnCambiaFechaDeStringAVariableDate(fecha1);
	fecha2 = fnCambiaFechaDeStringAVariableDate(fecha2);
	if(fecha1==false||fecha2==false){
		return -1;
	}

	return fecha1<fecha2;
}

// Regresa los días naturales entre dos fechas dadas
function fnDiasEntreFechas(fechaIni,fechaFin){
	fechaIni = fnCambiaFechaDeStringAVariableDate(fechaIni);
	fechaFin = fnCambiaFechaDeStringAVariableDate(fechaFin);
	if(fechaIni==false||fechaFin==false){
		return false;
	}
	dias = ( (
				Date.UTC(fechaFin.getFullYear(), fechaFin.getMonth(), fechaFin.getDate()) -
				Date.UTC(fechaIni.getFullYear(), fechaIni.getMonth(), fechaIni.getDate())
			) / 86400000 ) + 1;

	return dias;
}

// Cambia la fecha del elemento
function fnForzarCambioDeFecha(id){
	$("#"+id).val(fnAyer()).trigger('change');
	$("#"+id).val(fnHoy()).trigger('change');
}

// Regresa la fecha del día de hoy en formato dd-mm-YYYY
function fnHoy(hoy=new Date()){
	var	dia = hoy.toString().substr(8,2),
		mes = (hoy.getMonth()+1),
		anio = hoy.getFullYear();

	return dia+'-'+( mes<10 ? "0" : "" )+mes+"-"+anio;
}

// Regresa la fecha del día de ayer en formato dd-mm-YYYY
function fnAyer(){
	return fnHoy(new Date(Date.now() - 86400000));
}
