(function(cash) { "use strict";
				 
    /* ------ MENU ------ */
				 
    $('.dropdown a > .fa').on( "click", function() {
        var LinkThis = $(this).parent().parent();
        if (LinkThis.find('.dropdown-menu').hasClass('slideUp')) {
            LinkThis.find('.dropdown-menu').removeClass('slideUp');
        }else {
            $('.dropdown .dropdown-menu').removeClass('slideUp');
            LinkThis.find('.dropdown-menu').addClass('slideUp');
        }
        return false;
    });

	/* ------ VOLVER A INICIO ------ */

	$(window).scroll(function(){
	 if($(window).scrollTop() > 1000){
		  $("#back-to-top");
		} else{
		  $("#back-to-top");
		}
	});
	$('#back-to-top, .back-to-top').click(function() {
		  $('html, body').animate({ scrollTop:0 }, '3000');
		  return false;
	});


})(jQuery);



	function SendFilter(){
		var funcion = document.FDatosB.funcion.value;
		var filter = document.FDatosB.filterlist.value;
		if (filter!=""){
			window.open("SendFilterToUser.php?filtername="+filter+"&funcion="+funcion,"USUARIOS","width=400,height=300,scrollbars=NO");
		}
		else
			alert('Debe seleccionar un filtro para utilizar esta opcion');
	}


	function DeleteFilter(){
		if (document.FDatosB.filterlist.value!=""){

			if (confirm('Esta seguro de eliminar el filtro seleccionado ?')){
				document.FDatosB.opcdelfilter.value="1";
				document.FDatosB.submit();
			}
		}
		else
			alert('Debe seleccionar un filtro para utilizar esta opcion');
	}

	function HideWhereSelect(){
			document.getElementById("idwherecond").style.display="none";
	}

	function actualizaGroupBy(obj){

		for (i=0;i<document.FDatosB.groupby.length;i++){
       		if (document.FDatosB.groupby[i].value==obj.name){
          		document.FDatosB.groupby[i].checked=true;
				break;
			}
    	}


	}
	
	function actualizaGroupByBusqueda(obj){
		for (i=0;i<document.FDatosB.groupby.length;i++){
       		if (document.FDatosB.groupby[i].value==obj.name){
          		document.FDatosB.groupby[i].checked=true;
				break;
			}
    	}
		document.getElementById('level1').value = 'beneficiario';
	}
	
	function actualizaGroupBySecond(obj){

		for (i=0;i<document.FDatosB.groupbysecond.length;i++){
       		if (document.FDatosB.groupbysecond[i].value==obj.name){
          		document.FDatosB.groupbysecond[i].checked=true;
				break;
			}
    	}


	}

	function MuestraSegundaDimension(obj){
		document.getElementById("segundadim").style.display="block";
		document.getElementById("verdim").style.display="none";
		document.getElementById("txtver").value=1;

	}

	function OcultaSegundaDimension(obj){


		document.getElementById("segundadim").style.display="none";
		document.getElementById("verdim").style.display="block";
		document.getElementById("txtver").value=0;
		
	}

	
	function saveNewFilter(){
		if (document.getElementById("namenewfilter").value!=""){
			document.FDatosB.opcsavefilter.value="1";
			document.FDatosB.submit();
		}
		else
			alert('Debe introducir un nombre para el filtro');

	}

	

    function ExportaExcel(){

        window.open("dwh_ReporteVentasV2Excel.php");

    }

    function Linkpagina(pagina,condicion,cond){
		alert(555);
     }
    function selAll(obj){
        var I = document.getElementById('totrows').value;

        //alert("valor de :" + I);

        for(i=1; i<=I; i++) {
            var concatenar = "chk" + i;
            chkobj = document.getElementById(concatenar);
            if(chkobj != null) {
                chkobj.checked = obj.checked;
            }
        }
    }
    
    function mostrarOcultar(obj) {
    	namesearch.style.visibility = (obj.checked) ? 'visible' : 'hidden';
	}  


