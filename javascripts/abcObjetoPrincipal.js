// asignación de eventos


var isValidProduct = false;

$(document).ready(function() {

	// llamado de función inicial
	inicioPanel();
	// comportamiento del botón nuevo
	$('#nuevo').on('click',function(){
		fbCamposABloquear(false);
		AlinearSelectsDerecha();
		$('#tituloModal').html('<i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Nuevo Objeto Principal');
		$('#modalUsoGeneral').modal('show');
		fnTraeInformacionProducto();
	});
	// comportamiento del botón de guardado
	$('#guardar').on('click',function(){
		if(isValidProduct){
			var params = getParams(idForma), campos = getMatchets(idForma), msg = '',
				nombreCampo = {
					// 'selectUnidadNegocio':'UR',
					// 'selectUnidadEjecutora':'UE',
					'LocCode':'Código de Objeto Principal',
					'status' :'Estatus',
					'txtNombre' : 'Producto/Servicio',
					'selectUnidad' : 'Unidad Medida',
					'selectPago' : 'Método de Pago'
				}, 
				method = params.hasOwnProperty('identificador')?'update':'store';
			$.extend(params,{method:method,'valid':1});
			$.each(campos,function(index, el) {
				////if(el=='ind_activo'&&params[el]!=-1){ return; } // Línea adicional para este módulo
				if($("#"+el).is(":disabled")){ return; }
				if(el=="selectUnidadEjecutora"&&$("#"+el).val()=="00"){ return; } // Línea adicional para este módulo
				if(!params.hasOwnProperty(el)){ return; }
				// if(esepcion.indexOf(el)!==-1){ return; }
				if(params[el]!=0&&params[el]!=-1){ return; }
				if(nombreCampo[el]===undefined){ return; }
				msg += 'El campo '+nombreCampo[el]+' no puede ir vacío.<br />';
			});
			if(msg!=''){ muestraModalGeneral(3,'Información',msg); return;}
			$.post(modelo, params).then(function(res){
				var titulo=res.success?'Operación Exitosa':'Información';
				if(res.success){
					fnLimpiarCamposFormConHidden(idForma);
				}
				// llenaTabla(res.content);
				muestraModalGeneral(3,titulo,res.msg);
			});
			$('#modalUsoGeneral').modal('hide');
			$('#productDescription').text('Sin Producto/Servicio seleccionado');
			$('#productDescription').css('color', 'black');

		}else{
			$('#productDescription').css('color', 'red');
		}
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
		console.log(row);
		console.log('id:' ,row.identificador);
		// se extrae la información de los datos a modificar de base de datos
		$.post(modelo, { method:'edit', identificador: row.identificador })
		.then(function(res){
			// declaración de variables
			var titulo = 'Información', $spanContent = $('<span>');
			// comprobación de éxito
			if(res.success){
				fnLimpiarCamposFormConHidden(idForma);
				$.each(res.content, function(index, val) {
					if(index == 'identificador'){
						$('#forma').append('<input type="text" name="identificador" id="identificador" value="'+val+'" class="hidden"/>');
						CamposABloquear = true; // Línea adicional para este módulo
					}else{
						if($('#'+index).is("select")){
							$('#'+index).multiselect('select', val);
						}else{
							$('#'+index).val(val);
						}
					}
				});
				fbCamposABloquear(CamposABloquear);
				AlinearSelectsDerecha();
				$('#modalUsoGeneral #tituloModal').html('<i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Edición de Objeto Principal');
				$('#modalUsoGeneral').modal('show');
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
			content = '¿Realmente desea eliminar el elemento <strong>'+row.loccode+" - "+row.descripcion+'</strong>?',
			btnElimina = $('<button>',{ class : 'btn btn-primary btn-sm bgc8', html : 'Aceptar',
				click : function(){
					$.post(modelo, {method:'destroy', identificador:row.identificador})
					.then(function(res){
						var titulo = 'Información';
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
	this.modelo = this.url+'/modelo/abcObjetoPrincipalModelo.php';
	this.modeloComponentes = this.url+'/modelo/componentes_modelo.php';
	this.idForma = 'forma';
	this.btnCancel = $('<button>',{ class:'btn btn-primary btn-sm bgc8',html:'Cancelar','data-dismiss':'modal' });
	this.baseOption = '<option value="0">Seleccione una opción</option>';
	// funciones principales del sistema
	llenaTabla();
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
	// Líneas del archivo original
	cargaSelects();
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
		, tblObj, tblTitulo, tblExcel=[0,1,2,3,4,5,6,], tblVisual=[0,1,2,3,4,5,6,7,8];
	tblObj = [

		{ name: 'loccode', type: 'string'},// 0
		{ name: 'descripcion', type: 'string'},// 1
		{ name: 'urdescription', type: 'string'},// 2
		{ name: 'uedescription', type: 'string'},// 2
		{ name: 'leyenda', type: 'string'},// 3
		{ name: 'id_producto', type: 'string'},// 4
		{ name: 'id_unidad', type: 'string'},// 5
		{ name: 'id_pago', type: 'string'},// 5
		{ name: 'estatus', type: 'string'},// 6
		{ name: 'modificar', type: 'string'},// 7
		{ name: 'eliminar', type: 'string'},// 8
		{ name: 'identificador', type: 'string'},// 9
	];
	tblTitulo = [
		{ text: 'Clave', datafield: 'loccode', editable: false, width: '6%', cellsalign: 'center', align: 'center' },// 0
		{ text: 'UR', datafield: 'urdescription', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 2
		{ text: 'UE', datafield: 'uedescription', editable: false, width: '10%', cellsalign: 'center', align: 'center' },// 2
		{ text: 'Descripción', datafield: 'descripcion', editable: false, width: '30%', cellsalign: 'center', align: 'center' },// 1
		{ text: 'Leyenda', datafield: 'leyenda', editable: false, width: '26%', cellsalign: 'center', align: 'center' },// 3
		{ text: 'Producto', datafield: 'id_producto', editable: false, width: '6%', cellsalign: 'center', align: 'center' },// 4
		{ text: 'Unidad', datafield: 'id_unidad', editable: false, width: '6%', cellsalign: 'center', align: 'center' },// 5
		{ text: 'Pago', datafield: 'id_pago', editable: false, width: '6%', cellsalign: 'center', align: 'center' },// 5
		{ text: 'Estatus', datafield: 'estatus', editable: false, width: '7%', cellsalign: 'center', align: 'center' },// 6
		{ text: 'Modificar', datafield: 'modificar', editable: false, width: '7%', cellsalign: 'center', align: 'center' },// 7
		{ text: 'Eliminar', datafield: 'eliminar', editable: false, width: '6%', cellsalign: 'center', align: 'center' },// 8
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
	$.post(this.modelo, {method:'show'}).then(function(res){
		llenaTabla(res.content);
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
                        //console.log(item);

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