<html>
    <head>
        <!-- \$Revision: 1.5 $ -->
        <title>GRP Seguridad</title>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo _('ISO-8859-1'); ?>" />
        <link rel="stylesheet" href="css/silverwolf/login.css" type="text/css" />

        <link rel="stylesheet" type="text/css" href="css/v3/librerias/bootstrap-3.3.7/css/bootstrap.min.css">
    </head>

    <body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
        <div id="container">
            <div id="login_logo"></div>
            <div id="login_box"></div>
            <div class="container-fluid" align="center">
                <form action=" <?php echo $rootpath;?>/index.php" name="loginform" method="post">
                    <label> <?= ( isset($userBlocked) ? "El Usuario se encuentra bloqueado" : "" ); ?> </label>
                    <label> <?= ( isset($userBlocked) ? "Contacté al administrador para desbloquear el usuario" : "" ); ?> </label>
                    <!-- Too many failed login attempts -->
                    <label> <?= ( !isset($crearToken) ? "" : ( !$crearToken ? "Ya existe una sesión con este usuario" : "" ) ); ?> </label>
                    <!-- You will have to see an authorised person to obtain access to the system -->
                    <label> <?= ( !isset($crearToken) ? "" : ( !$crearToken ? "Espere unos minutos antes de volver a intentar acceder al sistema" : "" ) ); ?> </label>
                    <br />
                    <button type="submit" id="SubmitUser" name="SubmitUser" class="btn btn-default" value="<?php echo _('Login'); ?>">&nbsp;Volver</button>
                    <button type="submit" id="SubmitResetUser" name="SubmitResetUser" class="btn btn-default" value="<?php echo _('ClearSession'); ?>">&nbsp;Cerrar Sesión</button>
                </form>
            </div>
        </div>
        <script>

        </script>
    </body>
</html>
