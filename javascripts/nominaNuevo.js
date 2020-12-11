//configuraciones y ejecuciones principales
var url = window.location.href.split('/');
var trFile = 0; 
var validarOrdinaria  = false;
url.splice(url.length - 1);
window.url = url.join('/');
window.msgTime = 3000;
window.linea = 0;
window.renglon = 1;
window.municipios = [];
window.municipioEntidad = [];
window.clavesGeneral = [];
window.clavesCombustibles = [];
window.peajeTransporte = [];
window.cantPernoctaOld = 0;
window.datosPaises = [];
window.tipoDivisa = 'MX';
window.filesRoot = [];

$( window ).load(function() {
    $("#txtFechaCaptura").prop( "disabled", true );
   $("#guardarProd").hide(); 
    $("#Totales").hide(); 
    $("#tipoNomina").change(function(){
        $("#extraordinaria").hide();
        if ( $("#tipoNomina").val() == 1){
            $("#extraordinaria").show();
            fnSelectsExtraordinaria ( $("#noQuincena").val())
        }
           
    })
    $("#noQuincena").change(function(){
        var quincena = $("#noQuincena").val();
        layFecha     = $("#txtFechaFin").val().split("-");
        var anio     = layFecha[2]; 
        
        fnIncializarFechas (quincena, anio);
           
    })

    
    $("#guardarProd").click(function(){
        var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
        muestraModalGeneralConfirmacion(3, titulo, '¿Está seguro de procesar la información?', '', 'fnProcesar()');
         
    })

    $("#selectMes").change(function(){
        $("#noQuincena").empty();
        $("#noQuincena").multiselect('rebuild');
        fnMesPeriodo ();    
    })
    fnObtenerInformacion();
    $("#btnBusqueda").click(function() {
        fnObtenerInformacion();
    })

    $("#noQuincena").change(function(){
        if ( $("#tipoNomina").val() == 1){
            $("#extraordinaria").show();
            fnSelectsExtraordinaria ( $("#noQuincena").val())
        }   
    })
    
    // comportamiento de la pre carga del archivo CSV
    $('#inpt-upload').on('change', fnMuestraArchivosASubir);

    // comportamiento de la pre carga de archivos
    $('#inpt-upload-exp').on('change',fnMuestraArchivosASubirExp);
    
    // comportamiento de la eliminación de un archivo para su carga
	$(document).on('click','.rm-file',function(){
		var $linea = $(this).parents('tr[id]').eq(0), $spanContent = $('<span>'), $tabla = $(this).parents('table[id]'),
			$btnCancel = $('<button>',{ class:'btn botonVerde',html:'Cancelar','data-dismiss':'modal' });
		var $pie = $('<button>',{ class:'btn botonVerde',html:'Aceptar','data-dismiss':'modal',click:function(){
				var id = $linea.attr('id'), idTabla = $tabla.attr('id');
				$linea.remove();
				if(idTabla == 'tbl-filesToUp'){ delete(window.filesRoot[id]); }
			}});
		$spanContent.append($pie).append($btnCancel);
		muestraModalGeneralConfirmacion(3,window.tituloGeneral,'¿Realmente desea retirar el documento?',$spanContent);
    })
    
});

/******************
 *    Metodos     *
* ***************/

/**
 * Obtener datos para llenar los select
 * @param  string prefix prefijo de la url
 * @return jquery
 */
function getSelects(prefix) {
    muestraCargandoGeneral();
    params = {
        method: 'getSelects'
    }
    $.ajax({
        method: "POST",
       // dataType: "JSON",
        url: prefix + '/modelo/ABC_Jerarquias_Modelo.php',
        data: params
    }).done(function(res) {
        console.log("variable res de los getSelects: "+res)
        if (res.success) {
          console.log(res.content); 
         // fnFormatoSelectGeneral('#jerarquia'); 
          fnFormatoSelectGeneral('#tipoGasto');   
          fnFormatoSelectGeneral('#tipoSol');   
         // fnCrearSelectCero(res.content.jerarquias, '#jerarquia', 'Se', 1); 
          fnCrearDatosSelect(res.content.zonas, '#tipoGasto', 0, 1);  
          fnCrearDatosSelect(res.content.tipoSol, '#tipoSol', 0, 1);      
        } else {
            muestraMensajeTiempo(res.msg, 3, 'mensajes', msgTime);
        }
        ocultaCargandoGeneral();
    }).fail( function( jqXHR, textStatus, errorThrown ) {
      console.log(res)
      muestraMensajeTiempo(res.msg, 3, 'mensajes', msgTime);
      ocultaCargandoGeneral();
      });
}


function fnCrearSelectCero(dataJson, elementoClase = "", valor = "", valorInicial = 1) {
  var contenido = "";
  if (valorInicial == 1) {
      var contenido = "<option value='Se'>Seleccionar...</option>";
  }
  for (var info in dataJson) {
      var selected = "";
      if (dataJson[info].value == valor) {
          selected = "selected";
      }
      contenido += "<option value='" + dataJson[info].value + "' " + selected + ">" + dataJson[info].texto + "</option>";
  }
  if (elementoClase == "") {
      return contenido;
  } else {
      // Si trae nombre para los datos
      $(elementoClase).empty();
      $(elementoClase).append(contenido);
      $(elementoClase).multiselect('rebuild');
  }
}


function fnObtenerInformacion() {

    muestraCargandoGeneral();

    //Opcion para operacion
	dataObj = { 
	      option: 'obtenerInformacion',
	     
	    };
	
	$.ajax({
	    method: "POST",
	    dataType:"json",
	    url: "modelo/nominaNuevoModelo.php",
	    data:dataObj
	})
	.done(function( data ) {
		//console.log("Bien");
		if(data.result){
			//Si trae informacion
			ocultaCargandoGeneral();
			dataJson = data.contenido.datos;
			columnasNombres = data.contenido.columnasNombres;
			columnasNombresGrid = data.contenido.columnasNombresGrid;
			dataJsonNoCaptura = data.contenido.datos;
			
			console.log( "dataJson: " + JSON.stringify(dataJsonNoCaptura) );
			fnLimpiarTabla('divTabla', 'divContenidoTabla');
			//fnAgregarGrid(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', '', 1);

			var nombreExcel = data.contenido.nombreExcel;
			var columnasExcel= [ 0, 1, 2, 3,4];
            var columnasVisuales= [ 0, 1, 2, 3,4,5,6,7];

			fnAgregarGrid_Detalle(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);
			// fnEjecutarVueGeneral();
            //$('#divTabla > #divContenidoTabla').jqxGrid({columnsheight:'50px'});
            $("#toolbardivContenidoTabla").hide();
		}else{
            ocultaCargandoGeneral();
            $("#inpt-upload").val("");
            console.log ("data", data)
            if ( dataJson == 'repetido'){
                muestraMensaje('La quincena '+$("#nQuincena").val() + ' ya existe', 3, 'divMensajeOperacion', 5000);  
                return false;
            }else{
                muestraMensaje('Es necesario registrar la ordinaria '+$("#noQuincena").val() , 3, 'divMensajeOperacion', 5000); 
                return false;
            }
		}
	})
	.fail(function(result) {
	  ocultaCargandoGeneral();
	  // console.log("ERROR");
	  // console.log( result );
	});
}

function eliminar (idMonto,descripction,jerarquia,zonaEconomica){
    var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-danger" aria-hidden="true"></i> Información</p></h3>';
    muestraModalGeneralConfirmacion(3, titulo, '¿Desea eliminar la jerarquía '+descripction+'?', '', 'fnEliminarClave(\''+idMonto+'\',\''+descripction+'\','+jerarquia+',\''+zonaEconomica+'\')');
}

function fnTImeEliminar(idMonto,descripction,jerarquia,zonaEconomica){
    dataObj = { 
        option: 'eliminarInformacion',
        idMonto: idMonto,  
        jerarquia: jerarquia,
        zonaEconomica:zonaEconomica
    };
  
  $.ajax({
      method: "POST",
      dataType:"json",
      url: "modelo/ABC_Jerarquias_Modelo.php",
      data:dataObj
  })
  .done(function( data ) {
      if(data.result){
          //Si trae informacion
          ocultaCargandoGeneral();
          var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
          muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-plus-sing text-danger" aria-hidden="true"></i>Se eliminó el registro '+descripction+' del Catálogo Jerarquías con éxito </p>');
          //setTimeout(function(){ $('.modal-backdrop').remove();}, 1500);
          fnObtenerInformacion();
        }else{
          
          muestraMensaje('No es posible eliminar la jerarquía '+descripction+' ya que se encuentra vinculada a otro registro', 3, 'divMensajeOperacion', 5000); 
      }
  })
  .fail(function(result) {
    ocultaCargandoGeneral();
  });

}

/**************************************** FUNCIONES DE ARCHIVOS ****************************************/

function fnMuestraArchivosASubir() {
    var validarSelect =fnValdarselects(); 
    if (!validarSelect){
        return false; 
    }
    
    var $el = $(this), $tbl = $('#tbl-filesToUp'), len = 0, files = [], $tbody;
    files = $el.prop('files');
    len = files.length;
    $tbody = $tbl.find('tbody');
    $('#showFiles').removeClass('hidden');
    if(len){
        // agregado a variable global
        var flag = 0, msg = 'Los siguientes documentos no cumplen con el formato. <ul>';
        $.each(files, function(index, val) {
            var aceptedType = [ 'application/vnd.ms-excel','text/csv'], type = val.type; 
            var name        =val.name; 
            console.log("log",val.type)
        
            if ( name.indexOf('.xls') != -1){
                console.log("val",name.indexOf('.xls'))
                flag++;
                msg += '<li>'+val.name+'</li>';
            }

            // if(aceptedType.indexOf(val.type)!==-1){ type = 'xml'; }
            if(aceptedType.indexOf(val.type)===-1){
                flag++;
                msg += '<li>'+val.name+'</li>';
            }
        });
        msg += '</ul>';
        if(flag!=0){
            muestraModalGeneral(3,window.tituloGeneral,msg);
        }else{
            cargarArchivo();
        
        }
    }  
}
function fnMuestraArchivosASubirExp() {
	var $el = $(this), $tbl = $('#tbl-filesToUp'), len = 0, files = [], $tbody;
		files = $el.prop('files');
		len = files.length;
        $tbody = $tbl.find('tbody');
        trFile++
	$('#showFiles').removeClass('hidden');
	if(len){
		// agregado a variable global
		var flag = 0, msg = 'Los siguientes documentos no cumplen con el formato. <ul>';
		$.each(files, function(index, val) {
            console.log("index",index)
			var aceptedType = ['text/plain', 'application/octet-stream', 'application/pdf', 'text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'image/bmp', 'image/gif', 'image/jpeg', 'image/png'], type = val.type;
			// if(aceptedType.indexOf(val.type)!==-1){ type = 'xml'; }
			if(aceptedType.indexOf(val.type)===-1){
				flag++;
				msg += '<li>'+val.name+'</li>';
			}else{
				filesRoot[trFile] = val;
				var row = [
					generateItem('td',{html:val.name}),
					generateItem('td',{html:val.size}),
					generateItem('td',{html:type}),
					generateItem('span',{class:'btn btn-sm btn-primary bgc8 rm-file'},generateItem('span',{class:'glyphicon glyphicon-remove'}))
				];
				$tbody.append(generateItem('tr',{id:trFile,class:"renglon"},row));
			}
		});
		msg += '</ul>';
		if(flag!=0){
			muestraModalGeneral(3,window.tituloGeneral,msg);
		}
		
		fnFormatoSelectGeneral(".anexo");
	}
}
function leerArchivo(formData){
    muestraCargandoGeneral(); 
    $.ajax({
        method: "POST",
        dataType:"json",
        url: "modelo/nominaNuevoModelo.php",
        cache: false,
        contentType: false,
        processData: false,
        data: formData,
        type: 'post'
    })
    .done(function( data ) {
        if(data.result){
              //Si trae informacion
              validarTabla();
             ocultaCargandoGeneral(); 
        }else{
            $("#inpt-upload").val("");
            
            if (data.contenido == 'repetido'){
                muestraMensaje('La quincena '+$("#noQuincena").val() + ' ya existe en el sistema', 3, 'divMensajeOperacion', 5000);
                return false; 
            }else{
                muestraMensaje('Es necesario registrar la ordinaria '+$("#noQuincena").val() , 3, 'divMensajeOperacion', 5000); 
                return false;
            }
           
        }
    })
    .fail(function(result) {
      ocultaCargandoGeneral();
      // console.log("ERROR");
      // console.log( result );
    });  


}
function cargarArchivo(){
    
   var imptFiles = $('#inpt-upload').val()
   var formData = new FormData(document.getElementById("loadFile"))
   files = window.filesRoot;
   formData.append('method', 'infoArchivo');
   formData.append('option', 'infoArchivo');
   formData.append('quincena', $("#noQuincena").val());
   formData.append ( 'tipoNomina',$("#tipoNomina").val()),
   leerArchivo(formData) 
}
function validarStore(){
    let mes         =$("#selectMes").val();
    muestraCargandoGeneral();
    dataObj = { 
        option: 'validarstore',
        mes : mes
       
      };
  
    $.ajax({
        method: "POST",
        dataType:"json",
        url: "modelo/nominaNuevoModelo.php",
        data:dataObj
    })
    .done(function( data ) {
        //console.log("Bien");
        if(data.result){
            console.log("aui ando")
            //Si trae informacion 
            mostrarInformacion(); 
            $("#guardarProd").show(); 
            ocultaCargandoGeneral(); 
        }else{
            
            var error =  data.contenido.datos[0].value; 
            if ( error == 'Error en las partidas y la clave de concepto del Layout'){
                muestraMensaje(error, 3, 'divMensajeOperacion', 5000); 
                $("#inpt-upload").val("");
            }
            else{
                var partidas = data.contenido.datos;
                var msg      = '<b>Las siguientes partidas no cuentan con disponible</b> <br>';
                for (var info in partidas) {
                   msg += partidas[info].value ; 
                   msg += "<br>"
                }
                $("#inpt-upload").val("");
                muestraMensaje(msg, 3, 'divMensajeOperacion', 5000); 
            }
            ocultaCargandoGeneral(); 
        }
    })
    .fail(function(result) {
        ocultaCargandoGeneral();
        // console.log("ERROR");
        // console.log( result );
    }); 
}
function validarTabla(){
    muestraCargandoGeneral();
    dataObj = { 
        option: 'validarTabla',

      };
  
  $.ajax({
      method: "POST",
      dataType:"json",
      url: "modelo/nominaNuevoModelo.php",
      data:dataObj
  })
  .done(function( data ) {
      //console.log("Bien");
      if(data.result){
          //Si trae informacion
          var error =  data.contenido.datos[0].value; 
          if ( error == 'sin error'){
            validarStore();
          }else{
            $("#inpt-upload").val("");
            muestraMensaje('Error en la suma de percepciones y deducciones', 3, 'divMensajeOperacion', 5000); 
          }
         
      }else{
        $("#inpt-upload").val("");
        muestraMensaje(error, 3, 'divMensajeOperacion', 5000); 
        ocultaCargandoGeneral(); 
      }
  })
  .fail(function(result) {
    ocultaCargandoGeneral();
    // console.log("ERROR");
    // console.log( result );
  });
  ocultaCargandoGeneral(); 
}

function mostrarInformacion(){
    muestraCargandoGeneral();

    //Opcion para operacion
	dataObj = { 
	      option: 'obtenerInformacionTabla',
	     
	    };
	
	$.ajax({
	    method: "POST",
	    dataType:"json",
	    url: "modelo/nominaNuevoModelo.php",
	    data:dataObj
	})
	.done(function( data ) {
        //console.log("Bien");
        console.log("data", data)
		if(data.result){
            //Si trae informacion
            $("#selectMes").multiselect('disable');
            $("#tipoNomina").multiselect('disable');
            $("#noQuincena").multiselect('disable');
			dataJson = data.contenido.datos;
			columnasNombres = data.contenido.columnasNombres;
			columnasNombresGrid = data.contenido.columnasNombresGrid;
			dataJsonNoCaptura = data.contenido.datos;
			
			fnLimpiarTabla('divTabla', 'divContenidoTabla');
			//fnAgregarGrid(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', '', 1);

			var nombreExcel = data.contenido.nombreExcel;
			var columnasExcel= [ 0, 1, 2, 3,4];
			var columnasVisuales= [ 0, 1, 2, 3,4,5,6,7];
			fnAgregarGrid_Detalle(dataJson, columnasNombres, columnasNombresGrid, 'divContenidoTabla', ' ', 1, columnasExcel, true, false, '', columnasVisuales, nombreExcel);
			// fnEjecutarVueGeneral();
            //$('#divTabla > #divContenidoTabla').jqxGrid({columnsheight:'50px'});
            $("#Totales").show(); 
            $("#toolbardivContenidoTabla").hide();
            $("#totalPercepciones").val(data.contenido.totalPercepciones);
            $("#totalPercepciones").css('background-color','#1B693F');
            $("#totalDeducciones").val(data.contenido.totalDeducciones);
            $("#totalDeducciones").css('background-color','#1B693F');
            $("#totalNeto").val(data.contenido.totalNeto);
            $("#totalNeto").css('background-color','#1B693F');

            ocultaCargandoGeneral();
		}else{
			ocultaCargandoGeneral();
			muestraMensaje('No se obtuvo la información', 3, 'divMensajeOperacion', 5000); 
		}
	})
	.fail(function(result) {
	  ocultaCargandoGeneral();
	  // console.log("ERROR");
	  // console.log( result );
	});
}

function fnMesPeriodo(){
    var mesNomina    = $("#selectMes").val();
    var layFecha     = []; 
    switch (mesNomina) {
        case '01':
            $('#noQuincena').append('<option value="1">1</option>');
            $('#noQuincena').append('<option value="2">2</option>');
            break;
        case '02':
            $('#noQuincena').append('<option value="3">3</option>');
            $('#noQuincena').append('<option value="4">4</option>');
            break;
        case '02':
            $('#noQuincena').append('<option value="3">3</option>');
            $('#noQuincena').append('<option value="4">4</option>');
            break;
        case '03':
            $('#noQuincena').append('<option value="5">5</option>');
            $('#noQuincena').append('<option value="6">6</option>');
            break;
        case '04':
            $('#noQuincena').append('<option value="7">7</option>');
            $('#noQuincena').append('<option value="8">8</option>');
            break;
        case '05':
            $('#noQuincena').append('<option value="9">9</option>');
            $('#noQuincena').append('<option value="10">10</option>');
            break;
        case '06':
            $('#noQuincena').append('<option value="11">11</option>');
            $('#noQuincena').append('<option value="12">12</option>');
            break;
        case '07':
            $('#noQuincena').append('<option value="13">13</option>');
            $('#noQuincena').append('<option value="14">14</option>');
            break;
        case '08':
            $('#noQuincena').append('<option value="15">15</option>');
            $('#noQuincena').append('<option value="16">16</option>');
            break;
        case '09':
            $('#noQuincena').append('<option value="17">17</option>');
            $('#noQuincena').append('<option value="18">18</option>');
            break;
        case '10':
            $('#noQuincena').append('<option value="19">19</option>');
            $('#noQuincena').append('<option value="20">20</option>');
            break;
        case '11':
            $('#noQuincena').append('<option value="21">21</option>');
            $('#noQuincena').append('<option value="22">22</option>');
            break;
        case '12':
            $('#noQuincena').append('<option value="23">23</option>');
            $('#noQuincena').append('<option value="24">24</option>');
            $('#noQuincena').append('<option value="25">25</option>');
            $('#noQuincena').append('<option value="26">26</option>');
            break;
                }
    $("#noQuincena").multiselect('rebuild');

    var quincena = $("#noQuincena").val();
    layFecha     = $("#txtFechaFin").val().split("-");
    var anio     = layFecha[2]; 
    
    fnIncializarFechas (quincena, anio);

}

function fnValdarselects(){
    let validar          = true; 
    if ($("#selectMes").val() == "-1"){
        muestraModalGeneral(3,window.tituloGeneral,'Favor de agregar el mes a procesar');
        $("#inpt-upload").val("");
       return false; 
    }
    if ($("#tipoNomina").val() === ''){
        muestraModalGeneral(3,window.tituloGeneral,'Favor de agregar el tipo de nómina');
        $("#inpt-upload").val("");
        return false; 
    }
    if ($("#noQuincena").val() === null){
        muestraModalGeneral(3,window.tituloGeneral,'Favor de agregar el número de quincena');
        $("#inpt-upload").val("");
        return false; 
    }
    return validar;
}
function fnProcesar(){
    fechaCaptura   = $("#txtFechaCaptura").val(); 
    noQuincena     = $("#noQuincena").val(); 
    tipoNomina     = $("#tipoNomina").val();
    mes            = $("#selectMes option:selected").text();
    fechaInicio    = $("#txtFechaInicio").val(); 

    muestraCargandoGeneral();
    dataObj = { 
        option: 'procesarNomina',
        fechaCaptura: fechaCaptura,
        noQuincena: noQuincena,
        tipoNomina:tipoNomina,
        mes:mes,
        fechaInicio:fechaInicio,
      };
  
  $.ajax({
      method: "POST",
      dataType:"json",
      url: "modelo/nominaNuevoModelo.php",
      data:dataObj
  })
  .done(function( data ) {
      //console.log("Bien");
      if(data.result){
          //Si trae informacion
          muestraModalGeneral(3,window.tituloGeneral,'Se completo el proceso de nómina');
          ocultaCargandoGeneral(); 
      }else{
        $("#inpt-upload").val("");
        muestraMensaje(error, 3, 'divMensajeOperacion', 5000); 
        ocultaCargandoGeneral(); 
      }
  })
  .fail(function(result) {
      //el metodo GeneraMovimientoContablePresupuesto genera un warning, por lo que se manda el mensaje en esta parte
    muestraModalGeneral(3,window.tituloGeneral,'Se completo el proceso de nómina');
    $("#guardarProd").hide();
    ocultaCargandoGeneral();
    // console.log("ERROR");
    // console.log( result );
  });
}


function fnvalidarOrdinaria(quincena){
    muestraCargandoGeneral(); 
    ordinaria = false;
    dataObj = { 
        option: 'validarOrdinaria',
        quincena: quincena
      };
  
  $.ajax({
      method: "POST",
      dataType:"json",
      url: "modelo/nominaNuevoModelo.php",
      data:dataObj,
      async : false
  })
  .done(function( data ) {
      //console.log("Bien");
      if(data.result){
          //Si trae informacion
          dataJson = data.contenido.datos;
          console.log("data", dataJson)
          if ( dataJson > 0){
            ordinaria = true;
            validarOrdinaria = true; 
           console.log ("que pasa ordi", validarOrdinaria)
          }
      }else{
        $("#inpt-upload").val("");
        
      }
  })
  .fail(function(result) {
    // console.log("ERROR");
    // console.log( result );
  });  
 ocultaCargandoGeneral(); 
  return ordinaria; 
}
function fnSelectsExtraordinaria( quincena ){

    $("#noExtraordinaria").empty();
    $("#noExtraordinaria").multiselect('rebuild');

    muestraCargandoGeneral();
    console.log("valida")
    dataObj = { 
        option: 'selectExtraordinaria',
        quincena: quincena,
      };
  
  $.ajax({
      method: "POST",
      dataType:"json",
      url: "modelo/nominaNuevoModelo.php",
      data:dataObj
  })
  .done(function( data ) {
      //console.log("Bien");
      if(data.result){
          //Si trae informacion
          dataJson = data.contenido.datos;
          dataJson++; 
          console.log("jsondata", dataJson)
          $('#noExtraordinaria').append('<option value="'+dataJson+'">'+dataJson+'</option>'); 
          $("#noExtraordinaria").multiselect('rebuild');
          $("#inpt-upload").val("");
          ocultaCargandoGeneral(); 
      }else{
        $("#inpt-upload").val("");
        
        ocultaCargandoGeneral(); 
      }
  })
  .fail(function(result) {
    ocultaCargandoGeneral();
    // console.log("ERROR");
    // console.log( result );
  }); 
  return false;  
}

function fnIncializarFechas(quincena, anio){
    
    switch (quincena) {
        case "1":
        console.log("netro",anio)
            $("#txtFechaInicio").val('01-01-'+anio)
            $("#txtFechaFin").val('15-01-'+anio)
        break;
        case "2":
            $("#txtFechaInicio").val('16-01-'+anio)
            $("#txtFechaFin").val('31-01-'+anio)
        break;
        case "3":
            $("#txtFechaInicio").val('01-02-'+anio)
            $("#txtFechaFin").val('15-02-'+anio)
        break;
        case "4":
            $("#txtFechaInicio").val('16-02-'+anio)
            $("#txtFechaFin").val('28-02-'+anio)
        break;
        case "5":
            $("#txtFechaInicio").val('01-03-'+anio)
            $("#txtFechaFin").val('15-03-'+anio)
        break;
        case "6":
            $("#txtFechaInicio").val('16-03-'+anio)
            $("#txtFechaFin").val('31-03-'+anio)
        break;
        case "7":
            $("#txtFechaInicio").val('01-04-'+anio)
            $("#txtFechaFin").val('15-04-'+anio)
        break;
        case "8":
            $("#txtFechaInicio").val('16-04-'+anio)
            $("#txtFechaFin").val('30-04-'+anio)
        break;
        case "9":
            $("#txtFechaInicio").val('01-05-'+anio)
            $("#txtFechaFin").val('15-05-'+anio)
        break;
        case "10":
            $("#txtFechaInicio").val('16-05-'+anio)
            $("#txtFechaFin").val('31-05-'+anio)
        break;
        case "11":
            $("#txtFechaInicio").val('01-06-'+anio)
            $("#txtFechaFin").val('15-06-'+anio)
        break;
        case "12":
            $("#txtFechaInicio").val('16-06-'+anio)
            $("#txtFechaFin").val('30-06-'+anio)
        break;
        case "13":
            $("#txtFechaInicio").val('01-07-'+anio)
            $("#txtFechaFin").val('15-07-'+anio)
        break;
        case "14":
            $("#txtFechaInicio").val('16-07-'+anio)
            $("#txtFechaFin").val('31-07-'+anio)
        break;
        case "15":
            $("#txtFechaInicio").val('01-08-'+anio)
            $("#txtFechaFin").val('15-08-'+anio)
        break;
        case "16":
            $("#txtFechaInicio").val('16-08-'+anio)
            $("#txtFechaFin").val('31-08-'+anio)
        break;
        case "17":
            $("#txtFechaInicio").val('01-09-'+anio)
            $("#txtFechaFin").val('15-09-'+anio)
        break;
        case "18":
            $("#txtFechaInicio").val('16-09-'+anio)
            $("#txtFechaFin").val('30-09-'+anio)
        break;
        case "19":
            $("#txtFechaInicio").val('01-10-'+anio)
            $("#txtFechaFin").val('15-10-'+anio)
        break;
        case "20":
            $("#txtFechaInicio").val('16-10-'+anio)
            $("#txtFechaFin").val('31-10-'+anio)
        break;
        case "21":
            $("#txtFechaInicio").val('01-11-'+anio)
            $("#txtFechaFin").val('15-11-'+anio)
        break;
        case "22":
            $("#txtFechaInicio").val('16-11-'+anio)
            $("#txtFechaFin").val('30-11-'+anio)
        break;
        case "23":
            $("#txtFechaInicio").val('01-12-'+anio)
            $("#txtFechaFin").val('15-12-'+anio)
        break;
        case "24":
            $("#txtFechaInicio").val('16-12-'+anio)
            $("#txtFechaFin").val('31-12-'+anio)
        break;
        case "25":
            $("#txtFechaInicio").val('16-12-'+anio)
            $("#txtFechaFin").val('31-12-'+anio)
        break;
        case "26":
            $("#txtFechaInicio").val('16-12-'+anio)
            $("#txtFechaFin").val('31-12-'+anio)
        break;
        
    }
    
}