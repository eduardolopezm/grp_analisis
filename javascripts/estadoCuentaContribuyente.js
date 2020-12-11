
$(document).ready(function() {
    fnBusquedaContribuyente();

    $('#btnBuscarContribuyenteFiltro').click(function(){

		lookUpData($('#contribuyenteFiltro'));
    });

    $('#contribuyenteFiltro').keypress(function (e)
    {
       if (e.which == 13)
       {
          lookUpData($(this));
       }
       else
       {
           $('#contribuyenteFiltro').autocomplete("disable");
       }
   	});
   	function lookUpData(autocompleteField)
	{
		autocompleteField.autocomplete("enable");
		autocompleteField.autocomplete("search");
	}
    
   
	$('#printStatus').click(function(){
		var atributo = $('#contribuyenteIDFiltro').val();
		var fechaIni = $('#txtFechaInicialFilter').val();
        var fechaFin = $('#txtFechaFinal').val();
        var tipoDescarga = $('#tipoDescarga').val();
		if(atributo != '-1')
			window.open(getUrl()+'/PDFEstadoCuentaContribuyente.php?contribuyenteID='+atributo+'&fechaInicio='+fechaIni+'&fechaFin='+fechaFin+'&tipoDescarga='+tipoDescarga, '_blank');
		else{
			muestraModalGeneral(3, 'No se puede mostrar', '<p>No se ha ingresado ningun contribuyente</p>');

		}
	});

});

function fnBusquedaContribuyente() {

    dataObj = { 
            option: 'mostrarContribuyentes'
          };
    $.ajax({
      async:false,
      cache:false,
      method: "POST",
      dataType:"json",
      url: "modelo/componentes_modelo.php",
      data: dataObj
    })
    .done(function( data ) {
        //console.log(data);
        if(data.result) {
			fnBusquedaFiltroFormato(data.contenido.datos);
        }else{
            var titulo = '<h3><p><i class="glyphicon glyphicon-info-sign text-success" aria-hidden="true"></i> Información</p></h3>';
            muestraModalGeneral(3, titulo, '<p><i class="glyphicon glyphicon-remove-sign text-danger" aria-hidden="true"></i> No se Obtuvieron los Contribuyentes</p>');
        }
    })
    .fail(function(result) {
        console.log( result );
    });
}

function fnBusquedaFiltroFormato(jsonData) {
	
	$( "#contribuyenteFiltro").autocomplete({
		minLength: 4,
		source: jsonData,
		disabled: true,
        select: function( event, ui ) {
            
            $( this ).val( ui.item.value + "");
			$( "#contribuyenteFiltro" ).val( ui.item.value );
            $( "#contribuyenteIDFiltro" ).val( ui.item.texto );
			

            return false;
        }
    })
    .autocomplete( "instance" )._renderItem = function( ul, item ) {

        return $( "<li>" )
        .append( "<a>" + item.value + "</a>" )
        .appendTo( ul );

    };
}
function getUrl() {
	// declaración de variables principales
	var url = this.location.href.split('/');
	url.splice(url.length - 1);
	// retorno de información
	return url.join('/');
}