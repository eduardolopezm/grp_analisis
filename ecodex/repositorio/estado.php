<?php
        if(isset ($_POST['estatus']))
        {
            include 'includes/estadoRepositorio.php';
            
        }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <fieldset id="status">
            <legend>Estado de Comprobante</legend>
        <form method="post" action="estado.php">
            <label>RFC</label><input type="text" name="rfc" id="rfc" value="<?php if(isset($_POST['rfc'])){echo $_POST['rfc'];} ?>" /><br />
            <label>UUID</label><input type="text" name="UUID" id="UUID" value="<?php if(isset($_POST['UUID'])){echo $_POST['UUID'];} ?>" /><br />
            <label>Transaction ID</label><input type="text" name="trsID" id="trsID" value="<?php if(isset($_POST['trsID'])){echo $_POST['trsID'];} ?>" /><br />
            <input type="submit" name="estatus" id="estatus" value="Obtener Estado" />
        </form>
            <fieldset>
                <legend>Log</legend>
                <?php
                    if(isset ($log))
                    echo $log;
                ?>
            </fieldset>        
            <fieldset>
                <legend>Estado</legend>
                <label>Código</label><input type="text" id="codigo" name="codigo" disabled value="<?php if(isset ($codigoEstatus)) echo $codigoEstatus; ?>"/>
                <label>Descripción</label><input type="text" id="description" name="description" disabled value="<?php if(isset ($descripcionEstatus)) echo $descripcionEstatus; ?>"/>
            </fieldset>
            

        </fieldset>        
    </body>
</html>
