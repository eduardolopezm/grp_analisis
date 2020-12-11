// asiganacion de eventos
$(document).ready(function() {
    // llamado de funcion inicial
    inicioPanel();
    // comportamiento del boton nuevo
    $('#nuevo').on('click',function(){
        $('#tituloModal').html('Nueva Estructura Económica');
        $('#modalEconomica').modal('show');
    });
    // comportamiento del boton de guardado
    $('#guardar').on('click',function(){
        var params = getParams(idForma), campos=getMatchets(idForma), msg=''
            ,nombreCampo={'partida':'Partida','tg':'TG','ff':'FF'}
            ,method=params.hasOwnProperty('identificador')?'update':'store';
        $.extend(params,{method:method,'valid':1});
        $.each(campos,function(index, el) {
           if(!params.hasOwnProperty(el)){ return; }
           // if(esepcion.indexOf(el)!==-1){ return; }
           if(params[el]!=0){ return; }
           msg += 'El campo '+nombreCampo[el]+' no puede ir vacio.<br />';
        });
        if(msg!=''){ muestraModalGeneral(3,'Error de datos',msg); return;}
        $.post(modelo, params).then(function(res){
            var titulo=res.success?'Operación Exitosa':'Error de Datos';
            llenaTabla(res.content);
            muestraModalGeneral(3,titulo,res.msg);
        });
    });
    // comportamiento de eliminacion de registro
    $(document).on('cellselect','#tablaGrid',function(e){
        var index = e.args.rowindex, campo = e.args.datafield, currentTarget = e.currentTarget;
        // confirmación de evento a lanzar
        if(campo != 'modificar'){ return false; }
        // declaración de variables secundarias para evitar carga inecesaria
        var row = $(this).jqxGrid('getrowdata', index);
        // se extrae la información de los datos a modificar de base de datos
        $.post(modelo, { method:'edit', identificador: row.identificador })
        .then(function(res){
            // declaración d evariables
            var titulo = 'Error de Datos', $spanContent = $('<span>');
            // comprobacion de exito
            if(res.success){
                $.each(res.content, function(index, val) {
                    if(index == 'identificador'){
                        $('#forma').append('<input type="text" name="identificador" id="identificador" value="'+val+'" class="hidden"/>');
                    }else{
                        $('#'+index).multiselect('select', val);
                    }
                });
                $('#modalEconomica #tituloModal').html("Edición Estructura Económica");
                $('#modalEconomica').modal('show');
            }
        });
    })
    // comportamienteo de eliminacion del registro
    .on('cellselect','#tablaGrid',function(e){
        // declaración de variables
        var index = e.args.rowindex, campo = e.args.datafield, currentTarget = e.currentTarget;
        // validación de evento a lanzar
        if(campo != 'eliminar'){ return false; }
        // declaración de variables secundarias para evitar carga inecesaria
        var row = $(this).jqxGrid('getrowdata', index), $spanContent = $('<span>'),
            content = '¿Realmente desea eliminar el elemento <strong>'+row.descripcion+'</strong>?',
            btnElimina = $('<button>',{ class : 'btn btn-primary btn-sm bgc8', html  : 'Aceptar',
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
});
/*********************************** FUNCIONES DE EJECUCCIÓN ***********************************/
/**
 * Función de configuración inicial, donde se generarn e inician las variables que seran
 * usadas en el programa
 */
function inicioPanel() {
	// variables globales del sistema
	this.root = window;
    this.rootFi = 0;
    this.rootFu = 0;
	this.url = getUrl();
	this.modelo = this.url+'/modelo/abcEstructuraEconomicaModelo.php';
	this.modeloComponentes = this.url+'/modelo/componentes_modelo.php';
	this.idForma = 'forma';
	this.btnCancel = $('<button>',{ class:'btn btn-primary btn-sm bgc8',html:'Cancelar','data-dismiss':'modal' });
    this.baseOption = '<option value="0">Seleccione una opcion</option>';
	// funciones principales del sistema
	llenaTabla();
	cargaInicial();
	// colocación de estilos en mensaje de modal
	$('#'+this.idForma).css({ 'max-height':'600px', 'min-height':'200px', 'overflow-y': 'auto' });
    // comportamiento de apertura y sierre de la modal de captura
    $('#modalEconomica').on('hidden.bs.modal',function(){
        var limpiarSelects = ['partida','tg','ff'];
        $.each(limpiarSelects,function(index,el){ $('#'+el).multiselect('select',0).multiselect('rebuild'); });
        $('#modalEconomica').find('#identificador').remove();
    });
	// mensaje de confirmación de inicio
	console.log('listo el panel estructura programatica');
}
/**
 * Funcion para el llenado de la tabla prinsipal con los datos
 * enviados como parametro
 * @param  {Array} data Contenido que sera cargado en la tabla
 */
function llenaTabla(data) {
	// declaración de variables prinsipales
	var data = data||[], el = 'contenedorTabla', tabla = 'tablaGrid', nameExcel = 'Estructura Económica'
        , tblObj, tblTitulo, tblExcel=[0,1,2], tblVisual=[0,1,2,3]; //,4
    tblObj = [
        { name: 'partida', type: 'string'},// 0
        { name: 'tg', type: 'string'},// 1
        { name: 'ff', type: 'string'},// 2
        { name: 'descripcion', type: 'string'},// 3
        //{ name: 'modificar', type: 'string'},// 4
        { name: 'eliminar', type: 'string'},// 5
        { name: 'identificador', type: 'string'},// 6
    ];
    tblTitulo = [
        { text: 'Partida', datafield: 'partida', editable: false, width: '31%', cellsalign: 'center', align: 'center' },// 0
        { text: 'TG', datafield: 'tg', editable: false, width: '31%', cellsalign: 'center', align: 'center' },// 1
        { text: 'FF', datafield: 'ff', editable: false, width: '31%', cellsalign: 'center', align: 'center' },// 2
        //{ text: 'Modificar', datafield: 'modificar', editable: false, width: '20%', cellsalign: 'center', align: 'center' },// 3
        { text: 'Eliminar', datafield: 'eliminar', editable: false, width: '7%', cellsalign: 'center', align: 'center' },// 4 antes width: '20%' en todas
    ];
	// llamado de limpiesa de la tabla
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
	// declaración de variables prinsipales
	var url = this.location.href.split('/');
    url.splice(url.length - 1);
	// retorno de información
    return url.join('/');
}
