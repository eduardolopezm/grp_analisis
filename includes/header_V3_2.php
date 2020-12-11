<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="Irene Abigail Pescador Villanueva">
   <!--  <link rel="icon" href="../../favicon.ico"> -->

    <title><?php echo $titulo; ?></title>
    
    <?php 
      //obtenerRecursos($db, 'css', $funcion);
      //$xajax->printJavascript('ajaxresponse/xajax/');
      
    ?>
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link href="<?=$PathPrefix?>css/css_lh.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="<?=$PathPrefix?>v3/librerias/jquery-ui/jquery-ui-1.11.4/jquery-ui.min.css">
    <link rel="stylesheet" type="<?=$PathPrefix?>text/css" href="v3/librerias/bootstrap-3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" type="<?=$PathPrefix?>text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css">
    <link rel="stylesheet" type="<?=$PathPrefix?>text/css" href="v3/css/estilos_V3_0.css">

    <!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
   
  </head>

  <body style="overflow-y:scroll;">
    <header>
      <!-- Fixed navbar -->
      <nav class="navbar navbar-inverse navbar-static-top">
        <div class="col-md-12 menu-usuario">
          <div>
            <i class="fa fa-user icono-usuario" aria-hidden="true"></i> &nbsp; <span id="username"><?php echo stripslashes($_SESSION['UsersRealName']); ?></span>
            <a href="<?php echo $rootpath . '/UserSettings.php?' . SID; ?>"> &nbsp; <i class="fa fa-cog" aria-hidden="true" style="color: white;"></i> </a>
          </div>
        </div>

        <div class="container">
          <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#"><img src="v2/logo_SAGARPA.png" style="height: 55px;" class="img-responsive" alt="SAGARPA" title="SAGARPA" /></a>
          </div>
          <div id="navbar" class="navbar-collapse collapse">
            <div class="nav navbar-nav">
              <div class="title-header"><?php echo $titulo; ?></div>
            </div>
            <ul class="nav navbar-nav navbar-right">
              <li><a href="javascript:;" onclick="mostrarOcultarFavoritos(event);"> <i class="fa fa-star-o" aria-hidden="true"></i></a> </li>
              <li><a href="/proyectose/index.php?modulosel=1"><i class="fa fa-home" aria-hidden="true"></i></a></li>
              <li><a href="/proyectose/Logout.php?modulosel=6;"><i class="fa fa-sign-out" aria-hidden="true"></i></a></li>
            </ul>
          </div><!--/.nav-collapse -->
        </div>
        
      </nav>

      <!-- menu favoritos -->
      <div style="background: rgb(136, 136, 136); margin-top: -20px;" >
          <div class="linea-verde"></div>
          <div class="container" style="padding:5px; display:none;" id="NavPanel1">
              <div>
              <?php 
                    //BUSCAR OPCIONES PREFERENTES PARA ESTE USUARIO DE TRANSACCIONES
                    $sql = "SELECT sec_functions.shortdescription, sec_functions.functionid, sec_functions.url
                                    FROM sec_favxuser JOIN sec_functions_new as sec_functions ON sec_favxuser.functionid = sec_functions.functionid
                                    WHERE sec_favxuser.userid = '".$_SESSION['UserID']."' and sec_favxuser.type = 1
                                    ORDER BY sec_functions.title";
                    $ReFuntion = DB_query($sql, $db);

                    if (DB_num_rows($ReFuntion)>0 ) {

                      echo '<div class="col-md-6 col-sm-6" style="padding: 0px;"> ';
                      while($ResFuntion = DB_fetch_array($ReFuntion)) {
                        echo '<div class="col-md-4" style="color:#FFF; font-size:12px;">
                                <i class="fa fa-exchange" title="' . _('Eliminar de Favoritos') . '" onclick="location.href=\'index.php?Oper=Eliminar&functionfav=' . $ResFuntion['functionid'] . '\'" style="cursor:pointer"></i>&nbsp;
                                <a href="'.$ResFuntion['url'].'" class="" style="color:#FFF; font-size:12px;">'.$ResFuntion['shortdescription'].'</a>
                            </div>';
                      }
                      echo '</div>';
                    }


                    //BUSCAR OPCIONES PREFERENTES PARA ESTE USUARIO DE TRANSACCIONES 
                    $sql = "SELECT sec_functions.shortdescription, sec_functions.functionid, sec_functions.url
                            FROM sec_favxuser JOIN sec_functions_new as sec_functions ON sec_favxuser.functionid = sec_functions.functionid
                            WHERE sec_favxuser.userid = '".$_SESSION['UserID']."' and sec_favxuser.type = 2
                            ORDER BY sec_functions.title";
                    $ReFuntion = DB_query($sql, $db);

                    $index = 0;
                    $bordeLinea = '';
                    if (DB_num_rows($ReFuntion)>0 ) {
                        echo '<div class="col-md-6 col-sm-6" style="border-left: 1px solid #f5f5f5; padding: 0px;"> ';
                        while($ResFuntion = DB_fetch_array($ReFuntion)) {
                            
                            echo '<div class="col-md-4" style="color:#FFF; font-size:12px;">
                                    <i class="fa fa-line-chart" title="' . _('Eliminar de Favoritos') . '" onclick="location.href=\'index.php?Oper=Eliminar&functionfav=' . $ResFuntion['functionid'] . '\'" style="cursor:pointer"></i>&nbsp;
                                    <a href="'.$ResFuntion['url'].'" class="" style="color:#FFF; font-size:12px;">'.$ResFuntion['shortdescription'].'</a>
                                 </div>';

                            $index++;
                        }
                        echo '</div>';
                    }
                ?>
              </div>
            </div>
        </div>
        <!-- menu favoritos  -->
    </header>
    

    <div class="container">
      <div class="col-md-12 main-container">