<?php
//
  if ( isset($_GET['modal']) && ($_GET['modal']==true) ){

echo '</div>';
echo '</div>';
} else {
?>

        </div>
      </div>
    </div>


    <footer id="divGeneralFooter" name="divGeneralFooter">
      <div class="linea-verde"></div>
      <div class="footer-container">
        <div class="container">
          <div class="text-right text-rate">
            <?php

              $currentDate = date('d') . '/' . date('m') . '/' . date('Y');
              $currentDateDBFormat = date('Y') . '/' . date('m') . '/' . date('d');

              $rate = GetCurrencyRateByDate($currentDateDBFormat, 'USD', $db);
              if($rate == 0) {
                $rates = GetAllCurrencyRatesFromPeriodDLS($currentDate, $currentDate);
                $rate = number_format(1 / $rates[1]['rate'], 15);
                DB_query("INSERT INTO tipocambio VALUES(NULL, 'USD', '$rate', '$currentDateDBFormat')", $db);

                $rate = GetCurrencyRateByLastDateWRate($currentDateDBFormat, 'USD', $db);
              }

              $rate = 1 / $rate;
              
              $mes = date('m');
              $mes = $mes - 1;
              echo '<span class="tipocambio">Tipo de cambio:</span>
                    <span class="tipocambiovalor"> $'. number_format($rate,4).'</span>
                    <span class="tipocambio">
                        al '.date('d'). ' de '.$dataJsonMesesGenSistema[$mes].' del '.date('Y').
                    '</span>';
            ?>
          </div>
        </div>
      </div>
    </footer>
<?php

}
?>

    <link rel="stylesheet" type="text/css" href="css/v3/librerias/bootstrap-multiselect/bootstrap-multiselect.css">
    <!--Cargar Librerias de VUE Componentes-->
    <script src="css/vue/Vue.js"></script>
    <!-- carga de librerias independientes -->
    <script type="text/javascript" src="css/v2/js/validacion/moment.js"></script>
    <script src="css/v3/librerias/jquery.idleTimeout/store.min.js" type="text/javascript"></script>
    <!-- carga de librerias prinsipales -->
    <script src="css/v3/librerias/bootstrap-3.3.7/js/bootstrap.min.js"></script>
    <script src="css/v3/librerias/jquery.idleTimeout/jquery-idleTimeout.min.js" type="text/javascript"></script>
    <!-- carga de plugins prinsipales -->
    <script type="text/javascript" src="css/v3/librerias/datepicker/js/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="css/v3/librerias/bootstrap-multiselect/bootstrap-multiselect.js"></script>
    <script type="text/javascript" src="css/v3/librerias/bootstrap-multiselect/bootstrap-select.js"></script>
    <!-- carga de scripts personalisados -->
    <script type="text/javascript" src="javascripts/funciones_generales.js?<?php echo rand(); ?>"></script>
    <script src="javascripts/componentesVue.js?<?php echo rand(); ?>"></script>

    <script type="text/javascript">
        $('.sticky').css('min-width', '1100px');
        $('#wrap').css('min-width', '1100px');
        $('.content').css('min-width', '1100px');
        //$('.footer-container').css('min-width', '1100px');
    </script>

    <script type="text/javascript">
       
        muestraCargandoGeneral();

       
          
        /********************************* AGREGADO DE COMPORTAMIENTO A LA VENTANA *********************************/
        (function($){
            // comportamiento de reacomodo del body debido al comportamiento de la herramienta bootstrap
            // que agrega un padding-right al momento de abrir la modal y no logra cerrarlo de forma correcta
            // @date:14.03.18
            $('#ModalGeneral').on('hidden.bs.modal',function(e){ $('body[id]').css('padding-right',0); });
        })(jQuery);
        /********************************* AGREGADO DE COMPORTAMIENTO A LA VENTANA *********************************/
        // Inicio Json para Multiselect
        if (document.querySelector(".selectRazonSocial")) {
          // Inicio Razon Social
          //Opcion para operacion
          dataObj = {
                  option: 'mostrarRazonSocial'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php?",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  var contenido = "";
                  var seleccionado= "";
                  if (dataJson.length == 1) {
                    seleccionado= " selected ";
                  }
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].legalid+"' "+seleccionado+">"+dataJson[info].legalname+"</option>";
                  }
                  $('.selectRazonSocial').append(contenido);
              }else{
                  //console.log("ERROR Modelo");
                  //console.log( JSON.stringify(data) );
              }
          })
          .fail(function(result) {
              //console.log("ERROR");
              //console.log( result );
          });
          // Fin Razon Social
        }

        var unRegistroURGeneral = 0;
        // Inicio Json para Multiselect
        if (document.querySelector(".selectUnidadNegocio")) {
          // Inicio URG
          var seleccionado= "";
          var seltodos= false;
          var unidad= "";


          if (!$(".selectUnidadNegocio").prop("multiple") && $(".selectUnidadNegocio").data("todos")) {
            seltodos= true;
          }

          if ($(".selectUnidadNegocio").data("unidad") != "" &&  typeof $(".selectUnidadNegocio").data("unidad") != "undefined") {
            unidad= $(".selectUnidadNegocio").data("unidad");
          }

          //Opcion para operacion
          dataObj = {
                  option: 'mostrarUnidadNegocio'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  var contenido = ""; //"<option value='' selected>Seleccionar</option>";
                  //console.log("datos: "+dataJson.length);

                  if (dataJson.length == 1) {
                    unRegistroURGeneral = 1;
                    seleccionado= " selected ";
                  } else if (seltodos) {
                    contenido += "<option value='-1' selected>Sin seleccion</option>";
                  }
                  // console.log("seleccionado: "+seleccionado);
                  // console.log("unidad: "+unidad);
                  for (var info in dataJson) {
                    if (unidad != "" && unidad != "undefined") {
                      if (dataJson[info].tagref == unidad) {
                        contenido += "<option value='"+dataJson[info].tagref+"' selected>"+dataJson[info].tagdescription+"</option>";
                      } else {
                        contenido += "<option value='"+dataJson[info].tagref+"'>"+dataJson[info].tagdescription+"</option>";
                      }
                    } else {
                      contenido += "<option value='"+dataJson[info].tagref+"'"+seleccionado+">"+dataJson[info].tagdescription+"</option>";
                    }
                  }

                  $('.selectUnidadNegocio').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) );
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin URG
        }

        if (document.querySelector(".selectUnidadEjecutora14001")) {
          // Inicio URG
          var seleccionado= "";
          var seltodos= false;
          var unidad= "";

          var pathname =  window.location.pathname;

          // Para que en la ventana de viaticos en el select de UE aparezca la opcion "Sin seleccion" 
          if( window.location.pathname.indexOf("viaticos.php") !== -1&&window.location.pathname.indexOf("matriz_viaticos.php")==-1 ) {
             seltodos = true;
             console.log("entro pathname condicion");
          }

          if (!$(".selectUnidadEjecutora14001").prop("multiple") && $(".selectUnidadEjecutora14001").data("todos")) {
            seltodos= true;
          }

          if ($(".selectUnidadEjecutora14001").data("unidad") != "" &&  typeof $(".selectUnidadEjecutora14001").data("unidad") != "undefined") {
            unidad= $(".selectUnidadEjecutora14001").data("unidad");
          }

          var soloAlmancen = 0;
          if ($(".selectUnidadEjecutora14001").data("almacen") == "1") {
            soloAlmancen = 1;
          }

          //Opcion para operacion
          dataObj = {
                  option: 'mostrarUnidadEjecutora14001',
                  soloAlmancen: soloAlmancen
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              // console.log("data UE: "+data);
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;

            //  dataJson.sort(function (a, b) {
            //   var aValue = parseInt(a.ue);
            //   var bValue = parseInt(b.ue);
            //   // ASC
            //       return aValue == bValue ? 0 : aValue < bValue ? -1 : 1;
            //   // DESC
            //   //return aValue == bValue ? 0 : aValue > bValue ? -1 : 1;
            // });


                  var contenido = "";
                  //console.log("datos: "+dataJson.length);

                  if (dataJson.length == 1) {
                    seleccionado= " selected ";
                  } else if (seltodos) {
                    contenido += "<option value='-1' selected>Sin seleccion</option>";
                  }
                  // console.log("seleccionado: "+seleccionado); 
                  // console.log("unidad: "+unidad);
                  for (var info in dataJson) {
                    // console.log("info: "+ dataJson[info]);
                    if (unidad != "" && unidad != "undefined") {
                      if (dataJson[info].tagref == unidad) {
                        contenido += "<option value='"+dataJson[info].ue+"' selected>"+dataJson[info].uedescription+"</option>";
                      } else {
                        contenido += "<option value='"+dataJson[info].ue+"'>"+dataJson[info].uedescription+"</option>";
                      }
                    } else {
                      contenido += "<option value='"+dataJson[info].ue+"'"+seleccionado+">"+dataJson[info].uedescription+"</option>";
                    }
                  }

                  // console.log("dataJson.length: "+dataJson.length);
                  // console.log("seltodos: "+seltodos);
                  //console.log("opcionDefault: "+opcionDefault);
                  // console.log("contenido: "+contenido);

                  $('.selectUnidadEjecutora14001').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) );
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin URG
        }

        if (document.querySelector(".selectUnidadEjecutora")) {
          // Inicio URG
          var seleccionado= "";
          var seltodos= false;
          var unidad= "";

          var pathname =  window.location.pathname;

          // Para que en la ventana de viaticos en el select de UE aparezca la opcion "Sin seleccion" 
          if( window.location.pathname.indexOf("viaticos.php") !== -1&&window.location.pathname.indexOf("matriz_viaticos.php")==-1 ) {
             seltodos = true;
             console.log("entro pathname condicion");
          }

          if (!$(".selectUnidadEjecutora").prop("multiple") && $(".selectUnidadEjecutora").data("todos")) {
            seltodos= true;
          }

          if ($(".selectUnidadEjecutora").data("unidad") != "" &&  typeof $(".selectUnidadEjecutora").data("unidad") != "undefined") {
            unidad= $(".selectUnidadEjecutora").data("unidad");
          }

          var soloAlmancen = 0;
          if ($(".selectUnidadEjecutora").data("almacen") == "1") {
            soloAlmancen = 1;
          }

          //Opcion para operacion
          dataObj = {
                  option: 'mostrarUnidadEjecutora',
                  soloAlmancen: soloAlmancen
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              // console.log("data UE: "+data);
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;

            //  dataJson.sort(function (a, b) {
            //   var aValue = parseInt(a.ue);
            //   var bValue = parseInt(b.ue);
            //   // ASC
            //       return aValue == bValue ? 0 : aValue < bValue ? -1 : 1;
            //   // DESC
            //   //return aValue == bValue ? 0 : aValue > bValue ? -1 : 1;
            // });


                  var contenido = "";
                  //console.log("datos: "+dataJson.length);

                  if (dataJson.length == 1) {
                    seleccionado= " selected ";
                  } else if (seltodos) {
                    contenido += "<option value='-1' selected>Sin seleccion</option>";
                  }
                  // console.log("seleccionado: "+seleccionado); 
                  // console.log("unidad: "+unidad);
                  for (var info in dataJson) {
                    // console.log("info: "+ dataJson[info]);
                    if (unidad != "" && unidad != "undefined") {
                      if (dataJson[info].tagref == unidad) {
                        contenido += "<option value='"+dataJson[info].ue+"' selected>"+dataJson[info].uedescription+"</option>";
                      } else {
                        contenido += "<option value='"+dataJson[info].ue+"'>"+dataJson[info].uedescription+"</option>";
                      }
                    } else {
                      contenido += "<option value='"+dataJson[info].ue+"'"+seleccionado+">"+dataJson[info].uedescription+"</option>";
                    }
                  }

                  // console.log("dataJson.length: "+dataJson.length);
                  // console.log("seltodos: "+seltodos);
                  //console.log("opcionDefault: "+opcionDefault);
                  // console.log("contenido: "+contenido);

                  $('.selectUnidadEjecutora').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) );
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin URG
        }

        if (document.querySelector(".selectDocumentosPoliza")) {
          // Muestra los tipos de polizas que genera el sistema
          dataObj = {
                option: 'mostrarDocumentosPoliza'
            };
          fnSelectGeneralDatosAjax('.selectDocumentosPoliza', dataObj, 'modelo/componentes_modelo.php', 0);
        }


        if (document.querySelector(".selectTipoSuficiencia")) {
          // Muestra los tipos de polizas que genera el sistema
          dataObj = {
                option: 'mostrarTipoSuficiencia'
            };
          fnSelectGeneralDatosAjax('.selectTipoSuficiencia', dataObj, 'modelo/componentes_modelo.php', 0);
        }

        // Componente de tipo combo para mostrar los estatus de requisiciones y ordenes de compra
        if (document.querySelector(".selectEstatusRequisiciones")) {
          var idfuncion= 0;

          // validar el numero de funcion que regresa los estatus que le corresponden
          if ($(".selectEstatusRequisiciones").data("funcion")!= "" && $(".selectEstatusRequisiciones").data("funcion")!=0) {
            idfuncion= $(".selectEstatusRequisiciones").data("funcion");
          }

          //Parametros de seleccion de datos
          dataObj = {
            option: 'traeEstatusRequisiciones',
            filtro: "requisiciones",
            numeroFuncion: idfuncion
          };

          //Obtener datos
          $.ajax({
                async: false,
                cache: false,
                method: "POST",
                dataType: "json",
                url: "modelo/componentes_modelo.php",
                data: dataObj
            })

          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  var contenido = "";

                  //contenido += "<option value='-1' selected>Todos los Estatus...</option>";

                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].estatus+"'>"+dataJson[info].descripcion+"</option>";
                  }
                  $('.selectEstatusRequisiciones').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) );
              }
          })

          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin URG
        }

        if (document.querySelector(".selectEstatusGeneral")) {
          var idfuncion= 0;

          // validar el numero de funcion que regresa los estatus que le corresponden
          if ($(".selectEstatusGeneral").data("funcion")!= "" && $(".selectEstatusGeneral").data("funcion")!=0) {
            idfuncion= $(".selectEstatusGeneral").data("funcion");
          }

          //Parametros de seleccion de datos
          dataObj = {
            option: 'traeEstatusGeneral',
            numeroFuncion: idfuncion
          };

          //Obtener datos
          $.ajax({
                async: false,
                cache: false,
                method: "POST",
                dataType: "json",
                url: "modelo/componentes_modelo.php",
                data: dataObj
            })

          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  var contenido = "";

                  //contenido += "<option value='-1' selected>Todos los Estatus...</option>";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].estatus+"'>"+dataJson[info].descripcion+"</option>";
                  }

                  $('.selectEstatusGeneral').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) );
              }
          })

          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin URG
        }

        // Componente de tipo combo para mostrar los estatus de requisiciones y ordenes de compra
        if (document.querySelector(".selectAnio")) {
          //Parametros de seleccion de datos
          dataObj = {
            option: 'mostrarAnios'
          };

          //Obtener datos
          $.ajax({
                async: false,
                cache: false,
                method: "POST",
                dataType: "json",
                url: "modelo/componentes_modelo.php",
                data: dataObj
            })

          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  var contenido = "";

                  for (var info in dataJson) {

                    //si es el año actual lo selecciona
                    if ((new Date()).getFullYear() == dataJson[info].id)
                      contenido += "<option selected value='"+dataJson[info].id+"'>"+dataJson[info].descripcion+"</option>";
                    else
                      contenido += "<option value='"+dataJson[info].id+"'>"+dataJson[info].descripcion+"</option>";
                  }
                  $('.selectAnio').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) );
              }
          })

          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin URG
        }

        if (document.querySelector(".selectmostrarProgramaPresupuestario")) {
          // Inicio URG
          //Opcion para operacion
          dataObj = {
                  option: 'mostrarProgramasPresupuestario'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].cppt+"'>"+dataJson[info].descripcion+"</option>";
                  }
                  $('.selectmostrarProgramaPresupuestario').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) );
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin URG
        }

        if (document.querySelector(".selectRegiones")) {
          // Inicio Regiones
          //Opcion para operacion
          dataObj = {
                  option: 'mostrarRegion'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  var contenido = "<option value='0'>Seleccionar...</option>";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].regioncode+"'>"+dataJson[info].regionname+"</option>";
                  }
                  $('.selectRegiones').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) );
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin Regiones
        }

        if (document.querySelector(".selectArea")) {
          // Inicio Areas
          var dataArea = new Array();
          //Opcion para operacion
          dataObj = {
                  option: 'mostrarArea'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  //console.log( "dataArea: " + JSON.stringify(dataArea) );
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].areacode+"'>"+dataJson[info].areaname+"</option>";
                  }
                  $('.selectArea').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) );
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin Areas
        }

        if (document.querySelector(".selectAlmacen")) {
          // Inicio Almacen
          var dataAlmacen = new Array();
          //Opcion para operacion
          dataObj = {
                  option: 'mostrarAlmacen'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  //console.log( "dataAlmacen: " + JSON.stringify(dataAlmacen) );
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].loccode+"'>"+dataJson[info].locationname+"</option>";
                  }
                  $('.selectAlmacen').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) );
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin Almacen
        }

        if (document.querySelector(".selectDepartamentos")) {
          // Inicio Departamento
          var dataDepartamento = new Array();
          //Opcion para operacion
          dataObj = {
                  option: 'mostrarDepartamentos'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  //console.log( "dataDepartamento: " + JSON.stringify(dataDepartamento) );
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].u_department+"'>"+dataJson[info].departmentname+"</option>";
                  }
                  $('.selectDepartamentos').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) );
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin Departamento
        }

        if (document.querySelector(".selectCapitulos")) {
          // Inicio Capitulo  Partida presupuestal
          var dataCapitulo = new Array();
          //Opcion para operacion
          dataObj = {
                  option: 'mostrarCapitulos'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  //console.log( "dataJson: " + JSON.stringify(dataJson) );
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].capituloid+"'>"+dataJson[info].capituloname+"</option>";
                  }
                  $('.selectCapitulos').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) );
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin Capitulo  Partida presupuestal
        }

        if (document.querySelector(".selectCapitulosRadicacion")) {
          // Inicio Capitulo  Partida presupuestal
          var dataCapitulo = new Array();
          //Opcion para operacion
          dataObj = {
                  option: 'mostrarCapitulosRadicacion'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  //console.log( "dataJson: " + JSON.stringify(dataJson) );
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].capituloid+"'>"+dataJson[info].capituloname+"</option>";
                  }
                  $('.selectCapitulosRadicacion').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) );
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin Capitulo  Partida presupuestal
        }

        if (document.querySelector(".selectConceptos")) {
          // Inicio Capitulo  Partida presupuestal
          var dataConceptos = new Array();
          //Opcion para operacion
          dataObj = {
                  option: 'mostrarConceptos'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  //console.log( "dataJson: " + JSON.stringify(dataJson) );
                  var contenido = "";


                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].conceptoid+"'>"+dataJson[info].conceptoname+"</option>";
                    var valueToPush = new Array();
                    valueToPush[0] =dataJson[info].conceptoid;
                    valueToPush[1] =dataJson[info].conceptoname;
                    dataConceptos.push(valueToPush);
                  }
                  $('.selectConceptos').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) );
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin Capitulo  Partida presupuestal
        }

        if (document.querySelector(".selectGeografico")) {
          // Inicio Capitulo  Partida presupuestal
          var dataCapitulo = new Array();
          //Opcion para operacion
          dataObj = {
                  option: 'mostrarGeografico'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  //console.log( "dataJson: " + JSON.stringify(dataJson) );
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].cg+"'>"+dataJson[info].descripcion+"</option>";
                  }
                  $('.selectGeografico').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) );
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin Capitulo  Partida presupuestal
        }

        if (document.querySelector(".selectPartidasGenericas")) {
          // Inicio Capitulo  Partida presupuestal
          var dataPartidaGenerica = new Array();
          //Opcion para operacion
          dataObj = {
                  option: 'mostrarPartidasGenericas'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  //console.log( "dataJson: " + JSON.stringify(dataJson) );
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].partidagenericaid+"'>"+dataJson[info].partidagenericaname+"</option>";
                    var valueToPush = new Array();
                    valueToPush[0] =dataJson[info].partidagenericaid;
                    valueToPush[1] =dataJson[info].partidagenericaname;
                    dataPartidaGenerica.push(valueToPush);
                  }
                  $('.selectPartidasGenericas').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) );
              }
            })
            .fail(function(result) {
                // console.log("ERROR");
                // console.log( result );
          });
        }

        if (document.querySelector(".selectProveedor")) {
          // Inicio Finalidad

          //Opcion para operacion
          dataObj = {
                  option: 'mostrarProveedor'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  //console.log( "dataJson: " + JSON.stringify(dataJson) );
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].supplierid+"'>"+dataJson[info].proveedordescription+"</option>";
                  }
                  $('.selectProveedor').append(contenido);
                  fnFuncionSelect();
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) );
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin Finalidad
        }

    
		if (document.querySelector(".selectPago")) {
        // Inicio URG
        var seleccionado= "";
        var seltodos= false;
        var unidad= "";

        var pathname =  window.location.pathname;

        if (!$(".selectPago").prop("multiple") && $(".selectPago").data("todos")) {
        seltodos= true;
        }

        if ($(".selectPago").data("unidad") != "" &&  typeof $(".selectPago").data("unidad") != "undefined") {
        unidad= $(".selectPago").data("unidad");
        }

        //Opcion para operacion
        dataObj = {
                option: 'mostrarPago'
            };
        //Obtener datos de las bahias
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
            //Si trae informacion
            dataJson = data.contenido.datos;
            var contenido = "";
            //console.log("datos: "+dataJson.length);

            if (seltodos) {
                contenido += "<option value='-1' selected>Sin seleccion</option>";
            }
            // console.log("seleccionado: "+seleccionado);
            // console.log("unidad: "+unidad);
            for (var info in dataJson) {
                if (unidad != "" && unidad != "undefined") {
                if (dataJson[info].paymentID == unidad) {
                    contenido += "<option value='"+dataJson[info].paymentID+"' selected>"+dataJson[info].paymentDescripcion+"</option>";
                } else {
                    contenido += "<option value='"+dataJson[info].paymentID+"'>"+dataJson[info].paymentDescripcion+"</option>";
                }
                } else {
                contenido += "<option value='"+dataJson[info].paymentID+"'"+seleccionado+">"+dataJson[info].paymentDescripcion+"</option>";
                }
            }

            $('.selectPago').append(contenido);
            }else{
                // console.log("ERROR Modelo");
                // console.log( JSON.stringify(data) );
            }
        })
        .fail(function(result) {
            // console.log("ERROR");
            // console.log( result );
        });
        // Fin URG
    }
		if (document.querySelector(".selectStock")) {
        // Inicio URG
        var seleccionado= "";
        var seltodos= false;
        var unidad= "";

        var pathname =  window.location.pathname;

        if (!$(".selectStock").prop("multiple") && $(".selectStock").data("todos")) {
        seltodos= true;
        }

        if ($(".selectStock").data("unidad") != "" &&  typeof $(".selectStock").data("unidad") != "undefined") {
        unidad= $(".selectStock").data("unidad");
        }

        //Opcion para operacion
        dataObj = {
                option: 'mostrarStock'
            };
        //Obtener datos de las bahias
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
            //Si trae informacion
            dataJson = data.contenido.datos;
            var contenido = "";
            //console.log("datos: "+dataJson.length);

            if (seltodos) {
                contenido += "<option value='-1' selected>Sin seleccion</option>";
            }
            // console.log("seleccionado: "+seleccionado);
            // console.log("unidad: "+unidad);
            for (var info in dataJson) {
                if (unidad != "" && unidad != "undefined") {
                if (dataJson[info].paymentID == unidad) {
                    contenido += "<option value='"+dataJson[info].stockID+"' selected>"+dataJson[info].stockDescripcion+"</option>";
                } else {
                    contenido += "<option value='"+dataJson[info].stockID+"'>"+dataJson[info].stockDescripcion+"</option>";
                }
                } else {
                contenido += "<option value='"+dataJson[info].stockID+"'"+seleccionado+">"+dataJson[info].stockDescripcion+"</option>";
                }
            }

            $('.selectStock').append(contenido);
            }else{
                // console.log("ERROR Modelo");
                // console.log( JSON.stringify(data) );
            }
        })
        .fail(function(result) {
            // console.log("ERROR");
            // console.log( result );
        });
        // Fin URG
    }
        if (document.querySelector(".selectUnidad")) {
        // Inicio URG
        var seleccionado= "";
        var seltodos= false;
        var unidad= "";

        var pathname =  window.location.pathname;

        if (!$(".selectUnidad").prop("multiple") && $(".selectUnidad").data("todos")) {
        seltodos= true;
        }

        if ($(".selectUnidad").data("unidad") != "" &&  typeof $(".selectUnidad").data("unidad") != "undefined") {
        unidad= $(".selectUnidad").data("unidad");
        }

        console.log($(".selectUnidad").data("unidad"));

        //Opcion para operacion
        dataObj = {
                option: 'mostrarUnidad'
            };
        //Obtener datos de las bahias
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
            //Si trae informacion
            dataJson = data.contenido.datos;
            var contenido = "";
            //console.log("datos: "+dataJson.length);

            if (seltodos) {
                contenido += "<option value='-1' selected>Sin seleccion</option>";
            }
            // console.log("seleccionado: "+seleccionado);
            // console.log("unidad: "+unidad);
            for (var info in dataJson) {
                if (unidad != "" && unidad != "undefined") {
                if (dataJson[info].unitID == unidad) {
                    contenido += "<option value='"+dataJson[info].unitID+"' selected>"+dataJson[info].unitDescripcion+"</option>";
                } else {
                    contenido += "<option value='"+dataJson[info].unitID+"'>"+dataJson[info].unitDescripcion+"</option>";
                }
                } else {
                contenido += "<option value='"+dataJson[info].unitID+"'"+seleccionado+">"+dataJson[info].unitDescripcion+"</option>";
                }
            }

            $('.selectUnidad').append(contenido);
            }else{
                // console.log("ERROR Modelo");
                // console.log( JSON.stringify(data) );
            }
        })
        .fail(function(result) {
            // console.log("ERROR");
            // console.log( result );
        });
        // Fin URG
    }
    if (document.querySelector(".selectGlAccount")) {
        // Inicio URG
        var seleccionado= "";
        var seltodos= false;
        var unidad= "";

        var pathname =  window.location.pathname;

        if (!$(".selectGlAccount").prop("multiple") && $(".selectGlAccount").data("todos")) {
        seltodos= true;
        }

        if ($(".selectGlAccount").data("unidad") != "" &&  typeof $(".selectGlAccount").data("unidad") != "undefined") {
        unidad= $(".selectGlAccount").data("unidad");
        }

        console.log($(".selectGlAccount").data("unidad"));

        //Opcion para operacion
        dataObj = {
                option: 'mostrarGlAccounts'
            };
        //Obtener datos de las bahias
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
            //Si trae informacion
            dataJson = data.contenido.datos;
            var contenido = "";
            //console.log("datos: "+dataJson.length);

            if (seltodos) {
                contenido += "<option value='-1' selected>Sin seleccion</option>";
            }
            // console.log("seleccionado: "+seleccionado);
            // console.log("unidad: "+unidad);
            for (var info in dataJson) {
                if (unidad != "" && unidad != "undefined") {
                if (dataJson[info].unitID == unidad) {
                    contenido += "<option value='"+dataJson[info].accountcode+"' selected>"+dataJson[info].accountcode+' - '+dataJson[info].accountname+"</option>";
                } else {
                    contenido += "<option value='"+dataJson[info].accountcode+"'>"+dataJson[info].accountcode+' - '+dataJson[info].accountname+"</option>";
                }
                } else {
                contenido += "<option value='"+dataJson[info].accountcode+"'"+seleccionado+">"+dataJson[info].accountcode+' - '+dataJson[info].accountname+"</option>";
                }
            }

            $('.selectGlAccount').append(contenido);
            }else{
                // console.log("ERROR Modelo");
                // console.log( JSON.stringify(data) );
            }
        })
        .fail(function(result) {
            // console.log("ERROR");
            // console.log( result );
        });
        // Fin URG
    }
    if (document.querySelector(".selectBank")) {
        // Inicio URG
        var seleccionado= "";
        var seltodos= false;
        var unidad= "";

        var pathname =  window.location.pathname;

        if (!$(".selectBank").prop("multiple") && $(".selectBank").data("todos")) {
        seltodos= true;
        }

        if ($(".selectBank").data("unidad") != "" &&  typeof $(".selectBank").data("unidad") != "undefined") {
        unidad= $(".selectBank").data("unidad");
        }

        console.log($(".selectBank").data("unidad"));

        //Opcion para operacion
        dataObj = {
                option: 'mostrarBankAccounts'
            };
        //Obtener datos de las bahias
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
            //Si trae informacion
            dataJson = data.contenido.datos;
            var contenido = "";
            //console.log("datos: "+dataJson.length);

            if (seltodos) {
                contenido += "<option value='-1' selected>Sin seleccion</option>";
            }
            // console.log("seleccionado: "+seleccionado);
            // console.log("unidad: "+unidad);
            for (var info in dataJson) {
                if (unidad != "" && unidad != "undefined") {
                if (dataJson[info].unitID == unidad) {
                    contenido += "<option value='"+dataJson[info].accountcode+"' selected>"+dataJson[info].accountcode+' - '+dataJson[info].accountname+"</option>";
                } else {
                    contenido += "<option value='"+dataJson[info].accountcode+"'>"+dataJson[info].accountcode+' - '+dataJson[info].accountname+"</option>";
                }
                } else {
                contenido += "<option value='"+dataJson[info].accountcode+"'"+seleccionado+">"+dataJson[info].accountcode+' - '+dataJson[info].accountname+"</option>";
                }
            }

            $('.selectBank').append(contenido);
            }else{
                // console.log("ERROR Modelo");
                // console.log( JSON.stringify(data) );
            }
        })
        .fail(function(result) {
            // console.log("ERROR");
            // console.log( result );
        });
        // Fin URG
    }
            if (document.querySelector(".selectContrato")) {
                // Inicio URG
                var seleccionado= "";
                var seltodos= false;
                var unidad= "";

                var pathname =  window.location.pathname;

                if (!$(".selectContrato").prop("multiple") && $(".selectContrato").data("todos")) {
                seltodos= true;
                }

                if ($(".selectContrato").data("unidad") != "" &&  typeof $(".selectContrato").data("unidad") != "undefined") {
                unidad= $(".selectContrato").data("unidad");
                }

                console.log($(".selectContrato").data("unidad"));

                //Opcion para operacion
                dataObj = {
                        option: 'mostrarContratos'
                    };
                //Obtener datos de las bahias
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
                    //Si trae informacion
                    dataJson = data.contenido.datos;

                    var seleccionado = "";
                    var seleccionadoDefault = " selected ";
                    if (dataJson.length == 1) {
                        seleccionado = " selected ";
                        seleccionadoDefault = "";
                    }

                    var contenido = "<option value='-1' "+seleccionadoDefault+">Sin seleccion</option>";
                    //console.log("datos: "+dataJson.length);

                    if (seltodos) {
                        contenido += "<option value='-1' "+seleccionadoDefault+">Sin seleccion</option>";
                    }
                    // console.log("seleccionado: "+seleccionado);
                    // console.log("unidad: "+unidad);
                    for (var info in dataJson) {
                        if (unidad != "" && unidad != "undefined") {
                        if (dataJson[info].valor == unidad) {
                            contenido += "<option value='"+dataJson[info].value+"' selected>"+dataJson[info].texto+"</option>";
                        } else {
                            contenido += "<option value='"+dataJson[info].value+"'>"+dataJson[info].texto+"</option>";
                        }
                        } else {
                        contenido += "<option value='"+dataJson[info].value+"'"+seleccionado+">"+dataJson[info].texto+"</option>";
                        }
                    }

                    $('.selectContrato').append(contenido);
                    }else{
                        // console.log("ERROR Modelo");
                        // console.log( JSON.stringify(data) );
                    }
                })
                .fail(function(result) {
                    // console.log("ERROR");
                    // console.log( result );
                });
                // Fin URG
            }

            if (document.querySelector(".selectEstado")) {
                // Inicio URG
                var seleccionado= "";
                var seltodos= false;
                var unidad= "";

                var pathname =  window.location.pathname;

                if (!$(".selectEstado").prop("multiple") && $(".selectEstado").data("todos")) {
                seltodos= true;
                }

                if ($(".selectEstado").data("unidad") != "" &&  typeof $(".selectEstado").data("unidad") != "undefined") {
                unidad= $(".selectEstado").data("unidad");
                }

                console.log($(".selectEstado").data("unidad"));

                //Opcion para operacion
                dataObj = {
                        option: 'mostrarEstado'
                    };
                //Obtener datos de las bahias
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
                    //Si trae informacion
                    dataJson = data.contenido.datos;
                    var contenido = "";
                    //console.log("datos: "+dataJson.length);

                    if (seltodos) {
                        contenido += "<option value='-1' selected>Sin seleccion</option>";
                    }
                    // console.log("seleccionado: "+seleccionado);
                    // console.log("unidad: "+unidad);
                    for (var info in dataJson) {
                        if (unidad != "" && unidad != "undefined") {
                        if (dataJson[info].state == unidad) {
                            contenido += "<option value='"+dataJson[info].state+"' selected>"+dataJson[info].state+"</option>";
                        } else {
                            contenido += "<option value='"+dataJson[info].state+"'>"+dataJson[info].state+"</option>";
                        }
                        } else {
                        contenido += "<option value='"+dataJson[info].state+"'"+seleccionado+">"+dataJson[info].state+"</option>";
                        }
                    }

                    $('.selectEstado').append(contenido);
                    }else{
                        // console.log("ERROR Modelo");
                        // console.log( JSON.stringify(data) );
                    }
                })
                .fail(function(result) {
                    // console.log("ERROR");
                    // console.log( result );
                });
                // Fin URG
            }

            if (document.querySelector(".selectPais")) {
                // Inicio URG
                var seleccionado= "";
                var seltodos= false;
                var unidad= "";

                var pathname =  window.location.pathname;

                if (!$(".selectPais").prop("multiple") && $(".selectPais").data("todos")) {
                seltodos= true;
                }

                if ($(".selectPais").data("unidad") != "" &&  typeof $(".selectPais").data("unidad") != "undefined") {
                unidad= $(".selectPais").data("unidad");
                }

                console.log($(".selectPais").data("unidad"));

                //Opcion para operacion
                dataObj = {
                        option: 'mostrarPais'
                    };
                //Obtener datos de las bahias
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
                    //Si trae informacion
                    dataJson = data.contenido.datos;
                    var contenido = "";
                    //console.log("datos: "+dataJson.length);

                    if (seltodos) {
                        contenido += "<option value='-1' selected>Sin seleccion</option>";
                    }
                    // console.log("seleccionado: "+seleccionado);
                    // console.log("unidad: "+unidad);
                    for (var info in dataJson) {
                        if (unidad != "" && unidad != "undefined") {
                        if (dataJson[info].pais == unidad) {
                            contenido += "<option value='"+dataJson[info].pais+"' selected>"+dataJson[info].pais+"</option>";
                        } else {
                            contenido += "<option value='"+dataJson[info].pais+"'>"+dataJson[info].pais+"</option>";
                        }
                        } else {
                        contenido += "<option value='"+dataJson[info].pais+"'"+seleccionado+">"+dataJson[info].pais+"</option>";
                        }
                    }

                    $('.selectPais').append(contenido);
                    }else{
                        // console.log("ERROR Modelo");
                        // console.log( JSON.stringify(data) );
                    }
                })
                .fail(function(result) {
                    // console.log("ERROR");
                    // console.log( result );
                });
                // Fin URG
                $('.selectPais').val('México');

            }

            if (document.querySelector(".selectRegimenFiscal")) {
                // Inicio URG
                var seleccionado= "";
                var seltodos= false;
                var unidad= "";

                var pathname =  window.location.pathname;

                if (!$(".selectRegimenFiscal").prop("multiple") && $(".selectRegimenFiscal").data("todos")) {
                seltodos= true;
                }

                if ($(".selectRegimenFiscal").data("unidad") != "" &&  typeof $(".selectRegimenFiscal").data("unidad") != "undefined") {
                unidad= $(".selectRegimenFiscal").data("unidad");
                }

                console.log($(".selectRegimenFiscal").data("unidad"));

                //Opcion para operacion
                dataObj = {
                        option: 'mostrarRegimenFiscal'
                    };
                //Obtener datos de las bahias
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
                    //Si trae informacion
                    dataJson = data.contenido.datos;
                    var contenido = "";
                    //console.log("datos: "+dataJson.length);

                    if (seltodos) {
                        contenido += "<option value='-1' selected>Sin seleccion</option>";
                    }
                    // console.log("seleccionado: "+seleccionado);
                    // console.log("unidad: "+unidad);
                    for (var info in dataJson) {
                        if (unidad != "" && unidad != "undefined") {
                        if (dataJson[info].idRegimen == unidad) {
                            contenido += "<option value='"+dataJson[info].idRegimen+"' selected>"+dataJson[info].regDescripcion+"</option>";
                        } else {
                            contenido += "<option value='"+dataJson[info].idRegimen+"'>"+dataJson[info].regDescripcion+"</option>";
                        }
                        } else {
                        contenido += "<option value='"+dataJson[info].idRegimen+"'"+seleccionado+">"+dataJson[info].regDescripcion+"</option>";
                        }
                    }

                    $('.selectRegimenFiscal').append(contenido);
                    }else{
                        // console.log("ERROR Modelo");
                        // console.log( JSON.stringify(data) );
                    }
                })
                .fail(function(result) {
                    // console.log("ERROR");
                    // console.log( result );
                });
                // Fin URG
            }

            if (document.querySelector(".selectCFDI")) {
        // Inicio URG
        var seleccionado= "";
        var seltodos= false;
        var unidad= "";

        var pathname =  window.location.pathname;

        if (!$(".selectCFDI").prop("multiple") && $(".selectCFDI").data("todos")) {
        seltodos= true;
        }

        if ($(".selectCFDI").data("unidad") != "" &&  typeof $(".selectCFDI").data("unidad") != "undefined") {
        unidad= $(".selectCFDI").data("unidad");
        }

        console.log($(".selectCFDI").data("unidad"));

        //Opcion para operacion
        dataObj = {
                option: 'mostrarCFDI'
            };
        //Obtener datos de las bahias
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
            //Si trae informacion
            dataJson = data.contenido.datos;
            var contenido = "";
            //console.log("datos: "+dataJson.length);

            if (seltodos) {
                contenido += "<option value='-1' selected>Sin seleccion</option>";
            }
            // console.log("seleccionado: "+seleccionado);
            // console.log("unidad: "+unidad);
            for (var info in dataJson) {
                if (unidad != "" && unidad != "undefined") {
                if (dataJson[info].idCdfi == 'P01') {
                    contenido += "<option value='"+dataJson[info].idCdfi+"' selected>"+dataJson[info].cfdiDescripcion+"</option>";
                } else {
                    contenido += "<option value='"+dataJson[info].idCdfi+"'>"+dataJson[info].cfdiDescripcion+"</option>";
                }
                } else {
                contenido += "<option value='"+dataJson[info].idCdfi+"'"+seleccionado+">"+dataJson[info].cfdiDescripcion+"</option>";
                }
            }

            $('.selectCFDI').append(contenido);
			$('.selectCFDI').val('P01');

            }else{
                // console.log("ERROR Modelo");
                // console.log( JSON.stringify(data) );
            }
        })
        .fail(function(result) {
            // console.log("ERROR");
            // console.log( result );
        });
        // Fin URG
    }

     if (document.querySelector(".selectCuentaBanco")) {
          // Inicio selectCuentaBanco
          var dataCuentaBanco = new Array();
          dataObj = {
                  option: 'mostrarCuentaBanco'
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
              
                  dataCuentaBanco = data.contenido.datos;

                  //console.log( "dataJson: " + JSON.stringify(dataCuentaBanco) );
                  var contenido = "";
                  for (var info in dataCuentaBanco) {
                    contenido += "<option value='"+dataCuentaBanco[info].loccode+"'>"+dataCuentaBanco[info].locationname+"</option>";
                  }
                  $('.selectCuentaBanco').empty();
                  $('.selectCuentaBanco').append('<option value="0">Sin selección...</option>'+contenido);
                  $('.selectCuentaBanco').multiselect('rebuild');
              }
          })
          .fail(function(result) {
               console.log("ERROR");
               console.log( result );
          });
          // Fin FuenteRecurso
        }

        /// filtro de reporte para contrata

                  if (document.querySelector(".selectReporte")) {
          // Inicio selectReporte
          var dataObjetoReporte = new Array();
          
          dataObj = {
                  option: 'mostrarReporteContrato'
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
              
                  dataObjetoReporte = data.contenido.datos;

                  //console.log( "dataJson: " + JSON.stringify(dataObjetoReporte) );
                  var contenido = "";
                  for (var info in dataObjetoReporte) {
                    contenido += "<option value='"+dataObjetoReporte[info].loccode+"'>"+dataObjetoReporte[info].locationname+"</option>";
                  }
                  $('.selectReporte').empty();
                  $('.selectReporte').append('<option value="0">Sin selección...</option>'+contenido);
                  $('.selectReporte').multiselect('rebuild');
              }
          })
          .fail(function(result) {
               console.log("ERROR");
               console.log( result );
          });
          // Fin FuenteRecurso
        }

          if (document.querySelector(".selectObjetoPrincipalUsuarios")) {
          // Inicio selectObjetoPrincipalUsuarios
          var dataObjetoPrincipal = new Array();

          
          dataObj = {
                  option: 'mostrarObjetoPrincipalUsuarios'
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
              
                  dataObjetoPrincipal = data.contenido.datos;

                  //console.log( "dataJson: " + JSON.stringify(dataObjetoPrincipal) );
                  var contenido = "";
                  for (var info in dataObjetoPrincipal) {
                    contenido += "<option value='"+dataObjetoPrincipal[info].loccode+"'>"+dataObjetoPrincipal[info].locationname+"</option>";
                  }
                  $('.selectObjetoPrincipalUsuarios').empty();
                  $('.selectObjetoPrincipalUsuarios').append('<option value="0" selected>Sin selección...</option>'+contenido);
                  // $('.selectObjetoPrincipalUsuarios').multiselect('rebuild');
              }
          })
          .fail(function(result) {
               console.log("ERROR");
               console.log( result );
          });
          // Fin FuenteRecurso
        }

          if (document.querySelector(".selectObjetoPrincipal")) {
          // Inicio selectObjetoPrincipal
          var dataObjetoPrincipal = new Array();

          
          dataObj = {
                  option: 'mostrarObjetoPrincipal'
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
              
                  dataObjetoPrincipal = data.contenido.datos;

                  //console.log( "dataJson: " + JSON.stringify(dataObjetoPrincipal) );
                  var contenido = "";
                  for (var info in dataObjetoPrincipal) {
                    contenido += "<option value='"+dataObjetoPrincipal[info].loccode+"'>"+dataObjetoPrincipal[info].locationname+"</option>";
                  }
                  $('.selectObjetoPrincipal').empty();
                  $('.selectObjetoPrincipal').append('<option value="0">Sin selección...</option>'+contenido);
                  // $('.selectObjetoPrincipal').multiselect('rebuild');
              }
          })
          .fail(function(result) {
               console.log("ERROR");
               console.log( result );
          });
          // Fin FuenteRecurso
        }

        if (document.querySelector(".selectFuenteRecurso")) {
          // Inicio selectFuenteRecurso
          var dataFuenteRecurso = new Array();
          dataObj = {
                  option: 'mostrarFuenteRecurso'
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
              
                  dataFuenteRecurso = data.contenido.datos;

                  //console.log( "dataJson: " + JSON.stringify(dataFuenteRecurso) );
                  var contenido = "";
                  for (var info in dataFuenteRecurso) {
                    contenido += "<option value='"+dataFuenteRecurso[info].id_identificacion+"'>"+dataFuenteRecurso[info].fuentedescription+"</option>";
                  }
                  $('.selectFuenteRecurso').empty();
                  $('.selectFuenteRecurso').append('<option value="0">Sin selección...</option>'+contenido);
                  $('.selectFuenteRecurso').multiselect('rebuild');
              }
          })
          .fail(function(result) {
               console.log("ERROR");
               console.log( result );
          });
          // Fin FuenteRecurso
        }

        if (document.querySelector(".selectFinalidad")) {
          // Inicio Finalidad
          var dataFinalidad = new Array();
          dataObj = {
                  option: 'mostrarFinalidad'
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
              
                  dataFinalidad = data.contenido.datos;

                  //console.log( "dataJson: " + JSON.stringify(dataFinalidad) );
                  var contenido = "";
                  for (var info in dataFinalidad) {
                    contenido += "<option value='"+dataFinalidad[info].id_finalidad+"'>"+dataFinalidad[info].finalidaddescription+"</option>";
                  }
                  $('.selectFinalidad').empty();
                  $('.selectFinalidad').append('<option value="0">Sin selección...</option>'+contenido);
                  $('.selectFinalidad').multiselect('rebuild');
              }
          })
          .fail(function(result) {
               console.log("ERROR");
               console.log( result );
          });
          // Fin Finalidad
        }

        if (document.querySelector(".selectTipo")) {
          // Inicio Finalidad
          var dataTipo = new Array();
          dataObj = {
                  option: 'mostrarTipoIngreso'
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
              
                  dataTipo = data.contenido.datos;

                  //console.log( "dataJson: " + JSON.stringify(dataTipo) );
                  var contenido = "";
                  for (var info in dataTipo) {
                    contenido += "<option value='"+dataTipo[info].clave+"'>"+dataTipo[info].tipoDescription+"</option>";
                  }
                  $('.selectTipo').empty();
                  $('.selectTipo').append('<option value="0">Sin selección...</option>'+contenido);
                  $('.selectTipo').multiselect('rebuild');
              }
          })
          .fail(function(result) {
               console.log("ERROR");
               console.log( result );
          });
          // Fin Finalidad
        }

        if (document.querySelector(".selectRubro")) {
          // Inicio Finalidad
          var dataRubro = new Array();
          dataObj = {
                  option: 'mostrarRubroIngreso'
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
              
                  dataRubro = data.contenido.datos;

                  //console.log( "dataJson: " + JSON.stringify(dataRubro) );
                  var contenido = "";
                  for (var info in dataRubro) {
                    contenido += "<option value='"+dataRubro[info].clave+"'>"+dataRubro[info].rubroDescription+"</option>";
                  }
                  $('.selectRubro').empty();
                  $('.selectRubro').append('<option value="0">Sin selección...</option>'+contenido);
                  $('.selectRubro').multiselect('rebuild');
              }
          })
          .fail(function(result) {
               console.log("ERROR");
               console.log( result );
          });
          // Fin Finalidad
        }

        // codigo agreado por Arturo Lopez Peña
        if (document.querySelector(".selectTipoDocumentoPagosaProveedores")) {

          //Opcion para operacion
          dataObj = {
                  option: 'tipoDocumentoParaPagosaProveedores'
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
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].id+"'>"+dataJson[info].id+dataJson[info].nombre+"</option>";
                  }
                    contenido += "<option value='otros'>"+"Otros"+"</option>";
                  $('.selectTipoDocumentoPagosaProveedores').append(contenido);


              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) );
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });

        }

        if (document.querySelector(".selectTipoPagoTesoreria")) {

          //Opcion para operacion
          dataObj = {
                  option: 'tipoPagoTesoreria'
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
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.TiposPago;
                  var contenido = "<option value='0'>Seleccionar.. </option>";
                  for (var info in dataJson) {

                    contenido += "<option value='"+dataJson[info].id+"'>"+dataJson[info].nombre+"</option>";
                  }

                 $('.selectTipoPagoTesoreria').append(contenido);

              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) );
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });

        }

        if (document.querySelector(".selectMeses")) {

          //Array de meses para ministracion
          var meses = [
            {id:'01', value:'Enero'},
            {id:'02', value:'Febrero'},
            {id:'03', value:'Marzo'},
            {id:'04', value:'Abril'},
            {id:'05', value:'Mayo'},
            {id:'06', value:'Junio'},
            {id:'07', value:'Julio'},
            {id:'08', value:'Agosto'},
            {id:'09', value:'Septiembre'},
            {id:'10', value:'Octubre'},
            {id:'11', value:'Noviembre'},
            {id:'12', value:'Diciembre'}
          ];
          
          var contenido="";

          if (!$(".selectMeses").prop("multiple") && $(".selectMeses").data("todos")) {
            contenido +="<option value='-1'>Selecciona...</option>";
          }

          for(info in meses){
            contenido +="<option value='"+meses[info].id+"'>"+meses[info].value+"</option>";
          }

          $('.selectMeses').append(''+contenido);

        }

        if (document.querySelector(".selectMesesRadicacion")) {

          numMesRadicado=0;

          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'obtenerMesesUltimoRadicado'}
          }).done(function(res){
              if(!res.result){return;}
              var options='';
              $.each(res.contenido,function(index, el) {
                  numMesRadicado = el.mes
              });
          }).fail(function(res){
              throw new Error('No se logro cargar la información del componente partida espesifica');
          });


          //Array de meses para ministracion
          var meses = [
            {id:'01', value:'Enero'},
            {id:'02', value:'Febrero'},
            {id:'03', value:'Marzo'},
            {id:'04', value:'Abril'},
            {id:'05', value:'Mayo'},
            {id:'06', value:'Junio'},
            {id:'07', value:'Julio'},
            {id:'08', value:'Agosto'},
            {id:'09', value:'Septiembre'},
            {id:'10', value:'Octubre'},
            {id:'11', value:'Noviembre'},
            {id:'12', value:'Diciembre'}
          ];
          
          var contenido="";

          if (!$(".selectMesesRadicacion").prop("multiple") && $(".selectMesesRadicacion").data("todos")) {
            contenido +="<option value='-1'>Selecciona...</option>";
          }

          for(info in meses){
            console.log(info + ' ' + numMesRadicado);
            if(info >= parseInt(numMesRadicado)){
              contenido +="<option value='"+meses[info].id+"'>"+meses[info].value+"</option>";
            }
          }

          $('.selectMesesRadicacion').append(''+contenido);

        }

        // fin codigo agregado por Arturo Lopez Peña */

        if (document.querySelector(".selectFuncion")) {
          // Inicio Función
          var dataFuncion = new Array();
          //Opcion para operacion
          dataObj = {
                  option: 'mostrarFuncion'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  //console.log( "dataJson: " + JSON.stringify(dataJson) );
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].id_funcion+"'>"+dataJson[info].funciondescription+"</option>";
                  }
                  $('.selectFuncion').append('<option value="0">Sin selección...</option>'+contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) );
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin Función
        }

        if (document.querySelector(".selectActivoInactivo")) {
          // Inicio Función
          var contenido = "<option value='1'>Activo</option>";
          contenido += "<option value='0'>Inactivo</option>";
          $('.selectActivoInactivo').append(contenido);
          // Fin Función
        }
        // Fin Json para Multiselect

        // Funcion para validar entrada del RFC
        $(".fnRFC").on("input", function(){
          var rfc= this.value.trim().toUpperCase();

          if (rfc.length>=12){
            var rfcCorrecto = rfcValido(rfc);   // se comprueba el RFC

            if (rfcCorrecto) {
              //muestraMensaje(rfcCorrecto, 1, "lblMensajeRFC");
            } else {
              muestraMensaje("El RFC es incorrecto... Verifique !!!", 3, "lblMensajeRFC");
            }
          }
        });

        /**
         * Busqueda y asignación de la  información del componente perteneciente
         * a las sub funciones conforme al estandar establecido
         * @date:26.03.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectSubFuncion")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'muestraSubFuncion'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                $.each(res.contenido,function(index, el) {
                    options += `<option value="${el.val}">${el.text}</option>`;
                });
                $('.selectSubFuncion').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información del componente sub función');
            });
        }

        /**
         * Busqueda y asignación de la  información del componente perteneciente
         * a la reasignacion conforme al estandar establecido
         * @date:26.03.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectReasignacion")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'muestraReasignacion'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                $.each(res.contenido,function(index, el) {
                    options += `<option value="${el.val}">${el.text}</option>`;
                });
                $('.selectReasignacion').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información del componente reasignación');
            });
        }

        /**
         * Busqueda y asignación de la  información del componente perteneciente
         * a las actividades institucionales conforme al estandar establecido
         * @date:26.03.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectActividadInstitusional")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'muestraActividadInstitusional'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                $.each(res.contenido,function(index, el) {
                    options += `<option value="${el.val}">${el.text}</option>`;
                });
                $('.selectActividadInstitusional').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información del componente reasignación');
            });
        }

        /**
         * Busqueda y asignación de la  información del componente perteneciente
         * al programa presupuestario conforme al estandar establecido
         * @date:26.03.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectProgramaPresupuestario")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'muestraProgramaPresupuestario'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                $.each(res.contenido,function(index, el) {
                    options += `<option value="${el.val}">${el.text}</option>`;
                });
                $('.selectProgramaPresupuestario').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información del componente reasignación');
            });
        }

        /**
         * Busqueda y asignación de la  información del componente perteneciente
         * al programa extra presupuestario conforme al estandar establecido
         * @date:26.03.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectProgramaExtraPresupuestario")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'muestraProgramaExtraPresupuestario'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                $.each(res.contenido,function(index, el) {
                    options += `<option value="${el.val}">${el.text}</option>`;
                });
                $('.selectProgramaExtraPresupuestario').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información del componente reasignación');
            });
        }

        /**
         * Busqueda y asignación de la  información del componente perteneciente
         * al auxiliar 3 conforme al estandar establecido
         * @date:26.03.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectComponentePresupuestario")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'muestraComponentePresupuestario'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                $.each(res.contenido,function(index, el) {
                    options += `<option value="${el.val}">${el.text}</option>`;
                });
                $('.selectComponentePresupuestario').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información del componente reasignación');
            });
        }

        /**
         * Busqueda y asignación de la  información del componente perteneciente
         * para la partida espesifica
         * @date:29.03.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectPartidaEspesifica")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'muestraComponentePartidaEspesifica'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                $.each(res.contenido,function(index, el) {
                    options += `<option value="${el.val}">${el.text}</option>`;
                });
                $('.selectPartidaEspesifica').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información del componente partida espesifica');
            });
        }

        /**
         * Busqueda y asignación de la  información del componente perteneciente
         * para el tipo de gasto
         * @date:29.03.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectTipoGasto")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'muestraComponenteTipoGasto'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                $.each(res.contenido,function(index, el) {
                    options += `<option value="${el.val}">${el.text}</option>`;
                });
                $('.selectTipoGasto').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información del componente partida espesifica');
            });
        }

        /**
         * Busqueda y asignación de la  información del componente perteneciente
         * para la fuente de dinanciamiento
         * @date:29.03.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectFuenteFinanciamiento")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'muestraComponenteFuenteFinanciamiento'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                $.each(res.contenido,function(index, el) {
                    options += `<option value="${el.val}">${el.text}</option>`;
                });
                $('.selectFuenteFinanciamiento').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información del componente partida espesifica');
            });
        }

        /**
         * Busqueda y asignación de la  información del componente perteneciente
         * para las entidades federativas
         * @date:29.03.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectEntidadFederativa")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'muestraComponenteEntidadesFederativas'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                $.each(res.contenido,function(index, el) {
                    options += `<option value="${el.val}">${el.text}</option>`;
                });
                $('.selectEntidadFederativa').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información del componente partida espesifica');
            });
        }

        /**
         * Busqueda y asignación de la  información del componente perteneciente
         * para los pp
         * @date:29.03.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectAnexoUno")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'muestraAnexoUno',ur:$('.selectUnidadNegocio').eq(0).val()}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                $.each(res.contenido,function(index, el) {
                    options += `<option value="${el.val}">${el.text}</option>`;
                });
                $('.selectAnexoUno').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información del componente partida espesifica');
            });
        }

        /**
         * Busqueda y asignación de la  información del componente perteneciente
         * para los pp
         * @date:02.04.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectEmpleados")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'muestraEmpleados'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                
                $.each(res.contenido,function(index, el) {
                    options += '<option value="'+el.val+'">'+el.text+'</option>';
                });
                $('.selectEmpleados').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información del componente empleados');
            });
        }

        /**
         * Busqueda y asignación de la  información del componente perteneciente
         * para los pp
         * @date:02.04.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectPatrimonio")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'muestraPatrimonio'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                options += "<option value='-1' selected>Sin seleccion</option>";
                $.each(res.contenido,function(index, el) {
                    options += `<option value="${el.val}">${el.text}</option>`;
                });
                $('.selectPatrimonio').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información del componente partida espesifica');
            });
        }

        /**
         * Busqueda de los activos fijos
         * @date:02.04.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectPatrimonioGeneral")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'muestraPatrimonio_General'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                
                $.each(res.contenido,function(index, el) {
                    options += `<option value="${el.val}">${el.text}</option>`;
                });
                $('.selectPatrimonioGeneral').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información del componente partida espesifica');
            });
        }

        /**
         * Busqueda de categorias para patrimonio
         * combo para categoria patrimonio
         * @date:07.04.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectCategoriaActivo")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'muestraCategoriaActivo'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                if (!$(".selectCategoriaActivo").prop("multiple")) {
                  options = '<option value="-1">Seleccionar...</option>';
                }
                $.each(res.contenido,function(index, el) {
                    options += '<option value="'+ el.val +'">'+el.text+'</option>';
                });
                $('.selectCategoriaActivo').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información del componente partida espesifica');
            });
        }
        
        /**
         * Busqueda de estatus para patrimonio
         * combo para estatus de  patrimonio
         * @date:08.04.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectEstatusPatrimonio")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'muestraEstatusPatrimonio'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                
                $.each(res.contenido,function(index, el) {
                    options += '<option value="'+ el.val +'">'+ el.text +'</option>';
                });
                $('.selectEstatusPatrimonio').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información del componente partida espesifica');
            });
        }

        /**
         * Busqueda de estatus para radicado
         * combo para estatus de  estatus radicado
         * @date:18.06.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectEstatusRadicado")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'muestraEstatusRadicado'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                
                $.each(res.contenido,function(index, el) {
                    options += '<option value="'+ el.val +'">'+ el.text +'</option>';
                });
                $('.selectEstatusRadicado').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información del componente estatus radicado');
            });
        }

        /**
         * Busqueda de estatus para radicado
         * combo para estatus de  estatus radicado
         * @date:18.06.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectTipoProveedor")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'traeTiposProveedores'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                
                $.each(res.contenido,function(index, el) {
                    options += '<option value="'+ el.id +'">'+ el.nombretipo +'</option>';
                });
                $('.selectTipoProveedor').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información del componente tipo de proveedores');
            });
        }

        /**
         * Busqueda de estatus de ministracion
         * combo para estatus de ministracion
         * @date: 28.06.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectEstatusMinistracion")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'obtenerEstatusMinistracion'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                
                $.each(res.contenido,function(index, el) {
                    options += '<option value="'+ el.id +'">'+ el.estatus +'</option>';
                });
                $('.selectEstatusMinistracion').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información del componente del estatus de ministracion');
            });
        }

        /**
         * Busqueda de bancos configurado
         * combo para bancos
         * @date: 28.06.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectCuentasBancos")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'obtenerCuentasBancos'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                
                $.each(res.contenido,function(index, el) {
                    options += '<option value="'+ el.id +'">'+ el.descripcion +'</option>';
                });
                $('.selectCuentasBancos').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información del componente de bancos');
            });
        }

        if (document.querySelector(".selectCapitulosMinistracion")) {
          // Inicio Capitulo  Partida presupuestal
          var dataCapitulo = new Array();
          //Opcion para operacion
          dataObj = {
                  option: 'mostrarCapitulosMinistracion'
                };
          //Obtener datos de las bahias
          $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:dataObj
            })
          .done(function( data ) {
              //console.log("Bien");
              if(data.result){
                  //Si trae informacion
                  dataJson = data.contenido.datos;
                  //console.log( "dataJson: " + JSON.stringify(dataJson) );
                  var contenido = "";
                  for (var info in dataJson) {
                    contenido += "<option value='"+dataJson[info].capituloid+"' data-identificador ='"+dataJson[info].identificador+"'>"+dataJson[info].capituloname+"</option>";
                  }
                  $('.selectCapitulosMinistracion').append(contenido);
              }else{
                  // console.log("ERROR Modelo");
                  // console.log( JSON.stringify(data) );
              }
          })
          .fail(function(result) {
              // console.log("ERROR");
              // console.log( result );
          });
          // Fin Capitulo  Partida presupuestal
        }

        /**
         * Busqueda de tipo de bien , mueble, inmueble, vehiculo
         * combo para tipo bien
         * @date: 28.06.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectTipoBien")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'obtenerTipoBien'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                
                $.each(res.contenido,function(index, el) {
                    options += '<option value="'+ el.id +'">'+ el.descripcion +'</option>';
                });
                $('.selectTipoBien').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información del tipo de bien');
            });
        }

        /**
         * Busqueda de tipo de disposicion final 
         * combo disposicion final 
         * @date: 28.06.18
         * @author: Desarrollo
         */
         
        if(document.querySelector(".selectDisposicionFinal")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'obtenerDisposicionFinal'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                
                $.each(res.contenido,function(index, el) {
                    options += '<option value="'+ el.id +'">'+ el.descripcion +'</option>';
                });
                $('.selectDisposicionFinal').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información del disposicion final');
            });
        }


        /**
         * Busqueda de tipo de bien , mueble, inmueble, vehiculo
         * combo para tipo bien
         * @date: 28.06.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectTipoPropietario")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'obtenerTipoPropietario'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                
                $.each(res.contenido,function(index, el) {
                    options += '<option value="'+ el.id +'">'+ el.descripcion +'</option>';
                });
                $('.selectTipoPropietario').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información del tipo de propietario');
            });
        }

        /**
         * Busqueda de estatus de poliza
         * combo para estaus poliza
         * @date: 28.06.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectEstatusPoliza")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'obtenerTipoPoliza'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                options = "<option value='0'>Seleccionar...</option>";
                $.each(res.contenido,function(index, el) {
                    options += '<option value="'+ el.id +'">'+ el.descripcion +'</option>';
                });
                $('.selectEstatusPoliza').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información de estatus de poliza');
            });
        }

        /**
         * Busqueda de estatus de mantenimiento de patrimonio
         * combo para estaus de mantenimiento
         * @date: 28.06.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectEstatusMantenimiento")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'obtenerEstatusMantenimiento'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                //options = "<option value='0'>Seleccionar...</option>";
                $.each(res.contenido,function(index, el) {
                    options += '<option value="'+ el.id +'">'+ el.descripcion +'</option>';
                });
                $('.selectEstatusMantenimiento').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información de estatus de mantenimiento');
            });
        }
        
        /**
         * Busqueda de tipo de mantenimiento de patrimonio
         * combo para tipo de mantenimiento
         * @date: 21.07.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectTipoMantenimiento")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'obtenerTipoMantenimiento'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                //options = "<option value='0'>Seleccionar...</option>";
                $.each(res.contenido,function(index, el) {
                    options += '<option value="'+ el.id +'">'+ el.descripcion +'</option>';
                });
                $('.selectTipoMantenimiento').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información de tipo de mantenimiento');
            });
        }

        /**
         * Busqueda de estatus resguardo
         * combo para tipo de mantenimiento
         * @date: 21.07.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectEstatusResguardo")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'obtenerEstatusResguardo'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                //options = "<option value='0'>Seleccionar...</option>";
                $.each(res.contenido,function(index, el) {
                    options += '<option value="'+ el.id +'">'+ el.descripcion +'</option>';
                });
                $('.selectEstatusResguardo').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información de estatus resguardo ');
            });
        }

        /**
         * Busqueda de estatus usuario
         * combo para tipo de mantenimiento
         * @date: 21.07.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectEstatusUsuario")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'obtenerEstatusUsuario'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                //options = "<option value='0'>Seleccionar...</option>";
                $.each(res.contenido,function(index, el) {
                    options += '<option value="'+ el.id +'">'+ el.descripcion +'</option>';
                });
                $('.selectEstatusUsuario').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información de estatus usuarios ');
            });
        }

        /**
         * Busqueda de perfiles usuario
         * combo para tipo de mantenimiento
         * @date: 21.07.18
         * @author: Desarrollo
         */
        if(document.querySelector(".selectPerfilUsuario")) {
            $.ajax({
                async:false,
                cache:false,
                method: "POST",
                dataType:"json",
                url: "modelo/componentes_modelo.php",
                data:{option:'obtenerPerfilUsuario'}
            }).done(function(res){
                if(!res.result){return;}
                var options='';
                //options = "<option value='0'>Seleccionar...</option>";
                $.each(res.contenido,function(index, el) {
                    options += '<option value="'+ el.id +'">'+ el.descripcion +'</option>';
                });
                $('.selectPerfilUsuario').append(options);
            }).fail(function(res){
                throw new Error('No se logro cargar la información de perfiles usuarios ');
            });
        }

        /**
         * Tipo de Presupuesto
         * Combo para el tipo del presupuesto
         * @date: 09.11.19
         * @author: Desarrollo
         */
        if(document.querySelector(".selectTipoPresupuesto")) {
          $('.selectTipoPresupuesto').append('<option value="2">Egreso</option>');
          $('.selectTipoPresupuesto').append('<option value="1">Ingreso</option>');
        }
        
        function fnFuncionSelect(){
            var selectedAll = false;
          $('.selectBank,.selectGlAccount,.selectObjetoPrincipal,.selectContrato,.selectPais,.selectRegion,.selectEstado,.selectCFDI,.selectRegimenFiscal,.selectStock,.selectUnidad,.selectPago, .selectRazonSocial, .selectUnidadNegocio, .selectUnidadEjecutora, .selectRegiones, .selectArea, .selectAlmacen, .selectDepartamentos, .selectCapitulos, .selectConceptos, .selectGeografico, .selectPartidasGenericas, .selectCvePartidaEspecifica, selectDescPartidaEspecifica, .selectPartidaEspecificaCvePresupuestal, .selectCvePartidaEspecificaServ, selectDescPartidaEspecificaServ, .selectPartidaEspecificaCvePresupuestalServ, .selectFinalidad, .selectFuncion, .selectProveedor, .selectTipoDocumentoPagosaProveedores,.selectTipoPagoTesoreria, .selectTipoDocumentoAdecuaciones, .selectActivoInactivo, .selectmostrarProgramaPresupuestario, .selectEstatusAdecuaciones, .selectTipoAdecuacionReduccion, .selectEstatusRequisiciones, .selectAnio, .selectConfiguracionClave, .selectRamo, .selectTipoSolicitud, .selectGeneral, .selectEstatusGeneral, .selectSubFuncion, .selectReasignacion, .selectActividadInstitusional, .selectProgramaPresupuestario, .selectProgramaExtraPresupuestario, .selectComponentePresupuestario, .selectPartidaEspesifica, .selectTipoGasto, .selectFuenteFinanciamiento, .selectEntidadFederativa, .selectAnexoUno, .selectEmpleados, .selectPatrimonio, .selectPatrimonioGeneral, .selectCategoriaActivo, .selectEstatusPatrimonio, .selectEstatusActivo,.selectEstatusRadicado, .selectEstatusMinistracion, .selectMeses,.selectMesesRadicacion , .cmbTipoBalanza,.idAccounttype,.NivelDesagregacion,.ToPeriod,.FromPeriod,.idNoZeroes, .selectTipoProveedor,.selectEstatusMinistracion,.selectReportesFirmas,.selectUsuarioFirma,.clasfPrograma,.selectCuentasBancos,.selectCapitulosRadicacion,.selectBeneficiario,.selectClabeConcentradora,.selectCapitulosMinistracion,.selectFirmaMinistracion,.tipoNomina,.noQuincena,.selectTipoBien,.selectTipoPropietario,.selectEstatusPoliza,.selectEstatusMantenimiento,.selectTipoMantenimiento,.selectDisposicionFinal, .selectEstatusResguardo, .selectFromPeriod, .selectTipoolizaMega, .selectEstatusUsuario, .selectPerfilUsuario, .selectTipoPresupuesto').multiselect({
             enableFiltering: true,
             filterBehavior: 'text',
             enableCaseInsensitiveFiltering: true,
             buttonWidth: '100%',
             numberDisplayed: 1,
             includeSelectAllOption: true,
            onSelectAll: function() {
                if(selectedAll){
                    $('.selectUnidadNegocio').multiselect('deselectAll');
                }
                selectedAll = !selectedAll;
            }
          });

          // Estilos para el diseño del select
          $('.multiselect-container').css({ 'max-height': "200px" });
          $('.multiselect-container').css({ 'overflow-y': "scroll" });

          if (unRegistroURGeneral == 1) {
            $('.selectUnidadNegocio').multiselect('disable');
          }
        }

        // Confiruación Multiselect
        //setTimeout(function (){
          fnFuncionSelect();
          ocultaCargandoGeneral();
        //}, 2000);

        $("#ModalGeneral").draggable({
          handle: ".modal-dialog"
        });
        $("#ModalUR").draggable({
          handle: ".modal-dialog"
        });
        $("#ModalUREliminar").draggable({
          handle: ".modal-dialog"
        });

        //Se agrega funcion para desbloqueo de pantalla
        // $.unblockUI();
        
        $(".blockMsg").css({"background-color": "","border":"0px"});
        $(document).keypress(function(event) {
            var tecla = (document.all) ? event.keyCode : event.which;
            // Patron de entrada, en este evita los siguientes. simboloes
            var patron =/^[^*\"<>[\]{}`\\'&]+$/; 
            var tecla_final = String.fromCharCode(tecla);
            x=patron.test(tecla_final);
            //console.log(x);
           return  x; 
        });<?php
          echo "\n";
          if(isset($mensajeToken)){
            echo ( $mensajeToken ? "$mensajeToken\n" : "" );
          }
        ?>
    </script>

  </body>
</html>
