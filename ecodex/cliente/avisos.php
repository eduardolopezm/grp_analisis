<?php
        if(isset ($_POST['estatus']))
        {
            include 'includes/avisosCliente.php';

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
            <legend>Cliente</legend>
        <form method="post" action="avisos.php">
            <label>RFC</label><input type="text" name="rfc" id="rfc" value="<?php if(isset($_POST['rfc'])){echo $_POST['rfc'];} ?>" /><br />
            <input type="submit" name="estatus" id="estatus" value="Obtener Avisos" />
        </form>
            <fieldset>
                <legend>Log</legend>
                <?php
                    if(isset ($log))
                    echo $log;
                ?>
            </fieldset>
            <fieldset>
                <legend>Avisos</legend>
                
                <textarea rows="10" cols="100"><?php if(isset ($displayAvisos)) echo $displayAvisos; ?></textarea>
            </fieldset>


        </fieldset>
    </body>
</html>
