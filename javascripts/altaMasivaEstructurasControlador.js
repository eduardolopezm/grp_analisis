$(document).ready(function() {
	console.log('listo altas masivas');
	/* CONFIGURACIONES BASE */
	var url = window.location.href.split('/');
	url.splice(url.length-1);
	var prefix = url.join('/')+'/masterController.php',
		params = { class: 'altaMasivaEstructurasModelo' };
	// COMPORTAMIENTO DE BOTON DE CARGA
	$('#btn-carga').on('click',function(e){
		e.preventDefault();
		var files = $('#archivos').prop('files'), forma = new FormData(),
			pie = $('<button>',{
				class : 'btn btn-primary btn-green',
				html  : 'Cerrar',
				'data-dismiss' : 'modal'
			});;
		if(files.length!=0){
			$.each(files, function(index, val) { forma.append('archivos[]',val); });
			params = $.extend(params,{ method:'uploadFiles' });
			$.each(params, function(index, val) { forma.append(index,val); });
			$.ajax({
				url: prefix,
				type: 'POST',
				processData: false,
				contentType: false,
				data: forma
			})
			.done(function(res) {
				var $el = $('#mensaje'), cls='danger';
				$el.removeClass('alert-danger alert-success');
				if(res.success){ cls='success'; }
				$el.html('<h3>'+res.msg+'</h3>').addClass('alert-'+cls);
				console.log(res);
			});
		}else{
			muestraModalGeneral(2,'Error de Datos','Es necesario que seleccione minimo un archivo',pie);
		}
	});
});
