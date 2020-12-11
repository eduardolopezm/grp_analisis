// asignación de eventos


var isValidProduct = false;
var fechaIni;
var fechaFin;
var contratoID;


$(document).ready(function() {
	$("#isRango").change(function(){
		$('#importe').val('');
		$('#rangoFinal').val('');
		$('#rangoInicial').val('');
	});
	// fnBusquedaContribuyente();
	if($('#isUnique').val() == ''){
		$('#continuar').css('display','none');
		$('#regresar').css('display','none');

	}
	contratoID = $('#contratosID').val();

	$('#continuar').on('click',function(){

		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p>Asegúrese de haber generado el adeudo antes de avanzar</p>';
		muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "parent.reload(3);");
		// if($('#isUnique').val()== 'true')
		// if($('#isUnique').val()== 'true')
			
	});
	$('#regresar').on('click',function(){
		var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
		var mensaje = '<p>¿Estas seguro de que quieres regresar?</p>';
		muestraModalGeneralConfirmacion(4, titulo, mensaje, "", "parent.reload(1);");
		// if($('#isUnique').val()== 'true')
			
	});
	
	// llamado de función inicial
	inicioPanel();
	// comportamiento del botón nuevo
	$('#nuevo').on('click',function(){
		fbCamposABloquear(false);
		AlinearSelectsDerecha();
		$.post(modelo, { method:'edit', identificador: contratoID })
		.then(function(res){
			// declaración de variables
			var titulo = 'Error de Datos', $spanContent = $('<span>');
			// comprobación de éxito
			if(res.success){
				titulo = 'Datos Guardados';
				cargaInicial();
			}
			muestraModalGeneral(3,titulo,res.msg);
				$('#modalUsoGeneral').modal('hide');
		});

		
	});
	// comportamiento del botón de guardado
	$('#guardar').on('click',function(){
			var params = getParams(idForma), campos = getMatchets(idForma), msg = '',
				nombreCampo = {
					'importe':'Valor',
					'rangoInicial':'Rango Inicial',
					'rangoFinal':'Rango Final'
				},
				method = params.hasOwnProperty('identificador')?'update':'store';
			$.extend(params,{method:method,'valid':1});
			// $.each(campos,function(index, el) {
			// 	////if(el=='ind_activo'&&params[el]!=-1){ return; } // Línea adicional para este módulo
			// 	// if($("#"+el).is(":disabled")){ return; }
			// 	if(el=="selectUnidadEjecutora"&&$("#"+el).val()=="00"){ return; } // Línea adicional para este módulo
			// 	if(!params.hasOwnProperty(el)){ return; }
			// 	// if(esepcion.indexOf(el)!==-1){ return; }
			// 	if(params[el]!=0&&params[el]!=-1){ return; }
			// 	if(nombreCampo[el]===undefined){ return; }
			// 	if($("#"+el).val()!= '' && $("#"+el).val()!= 0 ){ return; } // Línea adicional para este módulo
			// 	if($("#isRango").is(':checked')){
			// 		if([el] == 'importe'){ return;}
			// 	}else{
			// 		if([el] == 'rangoFinal'){ return;}
			// 		if([el] == 'rangoInicial'){ return;}

			// 	}
			// 	msg += 'El campo '+nombreCampo[el]+' no puede ir vacío y debe ser siempre mayor a 0.<br />';

				
			// });
			// if($("#isRango").is(':checked')){
			// 	if(parseFloat($("#rangoFinal").val()) < parseFloat($("#rangoInicial").val()))
			// 		msg += 'El rango final debe ser mayor al rango inicial.<br />';
			// }
			// if(msg!=''){ muestraModalGeneral(3,'Error de datos',msg); return;}
			$.post(modelo, params).then(function(res){
				var titulo=res.success?'Operación Exitosa':'Error de Datos';
				if(res.success){
					fnLimpiarCamposFormConHidden(idForma);
				}
				// llenaTabla(res.content);
				muestraModalGeneral(3,titulo,res.msg);
				console.log('saved',res.msg);
			});
			$('#modalUsoGeneral').modal('hide');
			$('#productDescription').text('Sin Producto/Servicio seleccionado');
			$('#productDescription').css('color', 'black');

		
	});
	// comportamiento de modificación de registro
	$(document).on('cellselect','#tablaGrid',function(e){
		var CamposABloquear = false; // Línea adicional para este módulo
		// declaración de variables
		var index = e.args.rowindex, campo = e.args.datafield, currentTarget = e.currentTarget;
		// confirmación de evento a lanzar
		if(campo != 'modificar'){ return false; }
		// declaración de variables secundarias para evitar carga innecesaria
		var row = $(this).jqxGrid('getrowdata', index);
		// se extrae la información de los datos a modificar de base de datos
		$.post(modelo, { method:'edit', identificador: contratoID })
		.then(function(res){
			// declaración de variables
			var titulo = 'Error de Datos', $spanContent = $('<span>');
			// comprobación de éxito
			if(res.success){
				fnLimpiarCamposFormConHidden(idForma);
				console.log(res);
				$.each(res.content, function(index, val) {
					if(index == 'identificador'){
						$('#forma').append('<input type="text" name="identificador" id="identificador" value="'+val+'" class="hidden"/>');
						CamposABloquear = true; // Línea adicional para este módulo
					}else{
						if($('#'+index).is("select")){
							$('#'+index).multiselect('select', val);
							$('#'+index).multiselect('rebuild');
							$('#'+index).trigger('change')

							if(index == 'selectUnidadNegocio'){
								$('#'+index).val(val);
								$('#'+index).multiselect('rebuild');
								$('#'+index).trigger('change')
								$('#'+index).multiselect('disable');
							} 
							if(index == 'selectUnidadEjecutora'){
								$('#'+index).multiselect('disable');
							}
							if(index == 'selectContratos'){
								$('#'+index).val(val);
								$('#'+index).multiselect('rebuild');
								$('#'+index).multiselect('disable');
								
							}
 
							
							
						}else{
							if(index == 'contribuyente'){
								$('#'+index).attr('readonly', true);
							}
							$('#'+index).val(val);
						}

						if(index == 'selectObjetosParciales'){
							// var defaults = [2,5,6,4,3,6,8,9];

							// $('#'+index).val(function(i) { return defaults[i]; });

							res.content.objetosParciales.forEach(function(valToSelect) {
								$('#'+index).find('option[value="' + valToSelect + '"]').prop('selected', true);
							  });
							  $('#'+index).multiselect('rebuild');

						}
						
					}
				});
				fbCamposABloquear(CamposABloquear);
				AlinearSelectsDerecha();
				$('#modalUsoGeneral #tituloModal').html('<i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Edición de Modalidad - Contrato de Contribuyente');
				$('#modalUsoGeneral').modal('show');
				llenaTabla();

			}
		});
	})
	// comportamiento de eliminación del registro
	.on('cellselect','#tablaGrid',function(e){
		// declaración de variables
		var index = e.args.rowindex, campo = e.args.datafield, currentTarget = e.currentTarget;
		// validación de evento a lanzar
		if(campo != 'eliminar'){ return false; }
		// declaración de variables secundarias para evitar carga innecesaria
		var row = $(this).jqxGrid('getrowdata', index), $spanContent = $('<span>'),
			content = '¿Realmente desea eliminar el elemento <strong>'+row.identificador+'</strong>?',
			btnElimina = $('<button>',{ class : 'btn btn-primary btn-sm bgc8', html : 'Aceptar',
				click : function(){
					$.post(modelo, {method:'destroy', identificador:row.identificador})
					.then(function(res){
						var titulo = 'Error de Datos';
						if(res.success){
							titulo = 'Operación Exitosa';
							llenaTabla(res.content);
						}
						muestraModalGeneral(3, titulo, res.msg);
					});
				}
			});
		// agregado de los botones de acción necesarios
		$spanContent.append(btnElimina).append(btnCancel);
		// ejecución de render de Vue en caso de que se tengan componentes
		muestraModalGeneralConfirmacion(3, 'Confirmación', content, $spanContent);
	});
	// Estilo para que los elementos se desplieguen bien en vista colapsada, y que además los multi select sobresalagan de los modales en la vista completa
	$('html > head').append( '<style type="text/css"> .OverdeSelectsenModales{ overflow-y: auto; overflow-y: auto; } @media (min-width: 992px){ .OverdeSelectsenModales{ overflow-x: visible !important; overflow-y: visible !important; } } </style>' );

	$("#isRango").click(function() {  
		this.value = this.checked ? 1 : 0;
		if($("#isRango").is(':checked')) { 
			$('#rangoInicial').val('');
			$('#rangoFinal').val('');
			$('#rangoInicial').removeAttr('readonly');
			$('#rangoFinal').removeAttr('readonly');
			$('#importe').attr('readonly', true);
		} else {  
			$('#rangoInicial').attr('readonly', true);
			$('#rangoFinal').attr('readonly', true);
			$('#importe').removeAttr('readonly');
			$('#importe').val('');
		}  
   });  
   

});
/*********************************** FUNCIONES DE EJECUCCIÓN ***********************************/
/**
 * Función de configuración inicial, donde se generan e inician las variables que serán
 * usadas en el programa
 */
function inicioPanel() {
	// variables globales del sistema
	this.root = window;
	this.rootFi = 0;
	this.rootFu = 0;
	this.url = getUrl();
	this.modelo = this.url+'/modelo/infoPanelContratosModelo.php';
	this.modeloComponentes = this.url+'/modelo/componentes_modelo.php';
	this.idForma = 'forma';
	this.btnCancel = $('<button>',{ class:'btn btn-primary btn-sm bgc8',html:'Cancelar','data-dismiss':'modal' });
	this.baseOption = '<option value="0">Seleccione una opción</option>';
	llenaTabla();
	cargaInicial();

	// funciones principales del sistema
	// cargaInicial();
	// cargaProductos();
	// colocación de estilos en mensaje de modal
	$('#'+this.idForma).css({ 'max-height':'600px', 'min-height':'200px' });
	// comportamiento de apertura y cierre de la modal de captura
	$('#modalUsoGeneral').on('hidden.bs.modal',function(){
		//var limpiarSelects = ['partida','tg','ff'];
		//$.each(limpiarSelects,function(index,el){ $('#'+el).multiselect('select',0).multiselect('rebuild'); });
		if($('#modalUsoGeneral').find('#identificador').size()){
			fnLimpiarCamposFormConHidden(idForma);
		}
		$('#modalUsoGeneral').find('#identificador').remove();
	});
	fechaIni =$('#dtFechaInicio').val();
	fechaFin =$('#dtFechaVigencia').val();


	// Líneas del archivo original
	// cargaSelects();
}
/**
 * Función para el llenado de la tabla principal con los datos
 * enviados como parámetro
 * @param {Array} data Contenido que será cargado en la tabla
 */
function llenaTabla(data) {
	// declaración de variables principales

	var data = data||[], el = 'contenedorTabla', 
			tabla = 'tablaGrid', 
				nameExcel = 'Almacenes'
		, tblObj, tblTitulo, tblExcel=[0,1,2,3], tblVisual=[0,1,2,3,4,5];
	tblObj = [
		{ name: 'periodoCal', type: 'string'},// 3
		{ name: 'objetoPrincipal', type: 'string'},// 1
		{ name: 'objetoParcial', type: 'string'},// 4
		{ name: 'cantidad', type: 'string'},// 7
		{ name: 'total', type: 'string'},// 0
		{ name: 'modificar', type: 'string'},// 8
		// { name: 'descuento', type: 'string'},// 8
		{ name: 'eliminar', type: 'string'},// 8
		{ name: 'identificador', type: 'string'}// 9
	];
	tblTitulo = [
		{ text: 'Periodo', datafield: 'periodoCal', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Objeto Principal', datafield: 'objetoPrincipal', editable: false, width: '20%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Objeto Parcial', datafield: 'objetoParcial', editable: false, width: '40%', cellsalign: 'center', align: 'center' },// 0
		{ text: 'Cantidad', datafield: 'cantidad', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 4
		// { text: 'Descuento', datafield: 'descuento', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 4
		{ text: 'Total', datafield: 'total', editable: false, width: '15%', cellsalign: 'center', align: 'center' },// 7

	];
	// llamado de limpieza de la tabla
	fnLimpiarTabla(el,tabla);
	// render de la tabla
	fnAgregarGrid_Detalle_nostring(data, tblObj, tblTitulo, tabla, ' ', 1, tblExcel, false, true, "", tblVisual, nameExcel);
}
/**
 * Función de búsqueda de datos inicial
 */
function cargaInicial() {
	// solicitud al servidor de la información para la configuración
	$.post(this.modelo, {method:'show', contrato_id : contratoID}).then(function(res){
		llenaTabla(res.content);
		console.log(res.content[0].unidadNegocio);
		$('#tipoPeriodo').val(res.content[0].periodo);
		$('#tipoPeriodo').attr('readonly', true);

		$('#periodicidad').val(res.content[0].periodicidad);
		$('#periodicidad').attr('readonly', true);

		
		$('#contribuyenteFiltro').val(res.content[0].contribuyente);
		$('#contribuyenteFiltro').attr('readonly', true);

		$('#contrato').val(res.content[0].contrato);
		$('#contrato').attr('readonly', true);

		
		// $('#txtFechaInicial').val(res.content[0].fechaInicial);
		// $('#txtFechaInicial').attr('readonly', true);

		// $('#txtFechaFinal').val(res.content[0].fechaFinal);
		// $('#txtFechaFinal').attr('readonly', true);

	
	});
}
/************************* HELPERS *************************/
/**
 * Función para obtener la base de la url sin importar
 * en donde se encuentra el sistema
 * @return {String} Url encontrada según el proceso de filtrado
 */
function getUrl() {
	// declaración de variables principales
	var url = this.location.href.split('/');
	url.splice(url.length - 1);
	// retorno de información
	return url.join('/');
}

/* Funciones del archivo original */
function fbCamposABloquear(tipoAccion){
	/*
			'selectUnidadNegocio':'UR',
			'selectUnidadEjecutora':'UE',
	*/
	var	CamposABloquear = [{
			'LocCode':'Código de Almacén'
		}];

	$.each(CamposABloquear[0],function(index, el) {
		if($('#'+index).is("select")){
			$('#'+index).
			multiselect( tipoAccion ? "disable" : "enable" );
		}else{
			//$('#'+index).prop('readonly', tipoAccion);
			$('#'+index).attr('readonly', tipoAccion);
			if(!tipoAccion){
				$('#'+index).removeAttr("readonly");
			}
		}
	});
}

$('#btnBuscar').click(function(){
	fnMostrarDatos();
});

function fnMostrarDatos(){
	$.post(this.modelo, {method:'show', info: getParams('frmFiltroActivos')}).then(function(res){
		llenaTabla(res.content);
	});
}

function cargaProductos(){
	$.post(this.modelo, {method:'cargaProductos'}).then(function(res){

            console.log(res.content);
            if(res.success)
            {
               
                $( "#txtNombre").autocomplete({
                    source: res.content,
                    select: function( event, ui ) {
                        
                        $( this ).val( ui.item.value + " (" + ui.item.label + ")");
                        $( "#stockID" ).val( ui.item.value );

                        return false;
                    }
                })
                .autocomplete( "instance" )._renderItem = function( ul, item ) {

					return $( "<li>" )
					.append( "<a>" + item.value + " | "+ item.label+"</a>" )
					.appendTo( ul );

                };  
            }
           
       
	});
}

function cargaSelects(){
	var Elemento = "";
	$.post(this.modelo, {method:'datosselectImpuestos'}).then(function(res){
		$.each(res.content, function(index, val) {
			$('#TaxProvince').append(new Option(val.label, val.value));
		});
		fnFormatoSelectGeneral(".TaxProvince");
	});
}



function AlinearSelectsDerecha(){
	if($("#ColumnaDerecha").find("ul[class='multiselect-container dropdown-menu']").size()){
		$("#ColumnaDerecha").find("ul[class='multiselect-container dropdown-menu']").addClass("pull-right");
	}
}

function fnValidaDatos() {
	var mensaje= "";
	var notifica= false;

	if ($("#CategoryID").val() == "" || $("#CategoryID").val() == "0") {
		mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp; Debe capturar código de categoria para poder continuar.</p>'; 

		notifica= true;
	}

	if ($("#CategoryDescription").val() == "" || $("#CategoryDescription").val() == "0") {
		mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp; Debe capturar descripción de categoria para poder continuar.</p>'; 
		notifica= true;
	}

	if ($("#StockAct").val() == $("#accountegreso").val()) {
		mensaje+= '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i>&nbsp;&nbsp; Las cuentas de cargo y abono no pueden ser iguales.</p>'; 

		notifica= true;
	}

	if (notifica) {
		muestraModalGeneral(3, '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>', mensaje);	

		return false;
	} else {
		return true;
	}
}


function fnTraeInformacionProducto() {
	// Obtiene la descripcion de la partida generica
	var idProducto = $('#txtNombre').val();
	var	descripcionPartida = $('#productDescription');
	dataObj = { 
		option: 'muestraDescripcionProducto',
		idProducto: idProducto
	};
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType:"json",
		url: "modelo/componentes_modelo.php",
		data:dataObj
	})
	.done(function( data ) {
		if(data.result){
			$('#productDescription').css('color', 'black');
			descripcionPartida.text( data.contenido);
			isValidProduct = true;
			
		}else{
			descripcionPartida.text('Producto o Servicio no valido');
			isValidProduct = false;
		}
	})
	.fail(function(result) {
		//console.log("ERROR");
		//console.log( result );
	});

}

function fnFinalidadFuncion (type) {
	var id_identificacion = $("#selectObjetoPrincipal"+type).val();
	dataObj = {
		option: 'mostrarObjetoParcial',
		id_identificacion: id_identificacion
	};
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType: "json",
		url: "modelo/componentes_modelo.php",
		data: dataObj
	}).done(function(data) {
		if (data.result) {
			dataJson = data.contenido.datos;
			var contenido = "";
			for (var info in dataJson) {
				contenido += "<option value='" + dataJson[info].id_fuente + "'>" + dataJson[info].fuentedescription + "</option>";
			}
			$('#selectObjetoParcial'+type).empty();
			$('#selectObjetoParcial'+type).append('<option value="-1">Sin Selección...</option>'+contenido);
			$('#selectObjetoParcial'+type).multiselect('rebuild');
		} else {
			console.log("ERROR Modelo");
			console.log( JSON.stringify(data) ); 
		}
	}).fail(function(result) {
		console.log("ERROR");
		console.log( result );
	});
 }



//  function fnBusquedaContribuyente() {

//     dataObj = { 
//             option: 'mostrarContribuyentes'
//           };
//     $.ajax({
//       async:false,
//       cache:false,
//       method: "POST",
//       dataType:"json",
//       url: "modelo/componentes_modelo.php",
//       data: dataObj
//     })
//     .done(function( data ) {
//         //console.log(data);
//         if(data.result) {
// 			console.log(data);
// 			fnBusquedaFiltroFormato(data.contenido.datos);
//         }else{
//             var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
//             muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se Obtuvieron los Contribuyentes</p>');
//         }
//     })
//     .fail(function(result) {
//         console.log( result );
//     });
// }



// function fnBusquedaFiltroFormato(jsonData) {
//     console.log("busqueda fnBusquedaCog");
//     console.log("jsonData: "+JSON.stringify(jsonData));
//     $( "#contribuyente").autocomplete({
//         source: jsonData,
//         select: function( event, ui ) {
            
//             $( this ).val( ui.item.value + "");
// 			$( "#contribuyente" ).val( ui.item.value );
//             $( "#contribuyenteID" ).val( ui.item.texto );
			

//             return false;
//         }
//     })
//     .autocomplete( "instance" )._renderItem = function( ul, item ) {

//         return $( "<li>" )
//         .append( "<a>" + item.value + "</a>" )
//         .appendTo( ul );

// 	};
	
// 	$( "#contribuyenteFiltro").autocomplete({
//         source: jsonData,
//         select: function( event, ui ) {
            
//             $( this ).val( ui.item.value + "");
// 			$( "#contribuyenteFiltro" ).val( ui.item.value );
//             $( "#contribuyenteIDFiltro" ).val( ui.item.texto );
			

//             return false;
//         }
//     })
//     .autocomplete( "instance" )._renderItem = function( ul, item ) {

//         return $( "<li>" )
//         .append( "<a>" + item.value + "</a>" )
//         .appendTo( ul );

//     };
// }

 function ChangeObjetosParciales(){
	var idConfigContratos = $('#selectContratos').val();
	

	dataObj = {
		option: 'mostrarObjetosParcialesContrato',
		id_configContratos: idConfigContratos
	};
	$.ajax({
		async:false,
		cache:false,
		method: "POST",
		dataType: "json",
		url: "modelo/componentes_modelo.php",
		data: dataObj
	}).done(function(data) {
		if (data.result) {
			dataJson = data.contenido.datos;
			var contenido = "";

			for (var info in dataJson) {
				contenido += "<option value='" + dataJson[info].clave + "'>" + dataJson[info].nombre + "</option>";
			}
			$('#selectObjetosParciales').empty();
			$('#selectObjetosParciales').append(contenido);
			$('#selectObjetosParciales').multiselect('rebuild');
		} else {
			console.log("ERROR Modelo");
			console.log( JSON.stringify(data) ); 
		}
	}).fail(function(result) {
		console.log("ERROR");
		console.log( result );
	});
 }

 function openModalIframe(idContrato, idconfContrato,contribuyente,configuracion){

	console.log(contribuyente);
	
	var ruta = "abcPropiedadesAtributos.php?modal=true&id_contratos="+idContrato+"&id_configuracion="+idconfContrato+"&name="+contribuyente+"&conf="+configuracion;
	var contenido = '<div style="width: 100%; height: 400;"> <iframe className="" src="'+ruta+'" width="100%" height="400" frameBorder="0"></iframe> </div>';

	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Atributos del contrato</p></h3>';
	muestraModalGeneral(4, titulo, contenido);
}

function openModalIframeDos(idContrato, idconfContrato, contribuyente){
	
	var ruta = "abcObjetosParcialesContrato.php?modal=true&id_contratos="+idContrato+"&id_configuracion="+idconfContrato+"&name="+contribuyente;
	var contenido = '<div style="width: 100%; height: 400;"> <iframe className="" src="'+ruta+'" width="100%" height="400" frameBorder="0"></iframe> </div>';

	var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Detalle de cobro</p></h3>';
	muestraModalGeneral(4, titulo, contenido);
}


