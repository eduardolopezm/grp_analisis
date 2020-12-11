<?php
        if(isset ($_POST['cancel']))
        {
            include 'includes/cancelaTimbrado.php';
        }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <fieldset id="cancel">
            <legend>Cancelar Timbrado</legend>
        <form method="post" action="cancela.php">
            <label>RFC</label><input type="text" name="rfc" id="rfc" value="<?php if(isset($_POST['rfc'])){echo $_POST['rfc'];} ?>" /><br />
            <label>UUID</label><input type="text" name="UUID" id="UUID" value="<?php if(isset($_POST['UUID'])){echo $_POST['UUID'];} ?>" /><br />            
            <input type="submit" name="cancel" id="cancel" value="Cancelar Timbrado" />
        </form>
            <fieldset>
                <legend>Log</legend>
                <?php
                if(isset ($log))
                    echo $log;
                ?>
            </fieldset>
            <fieldset>
                <legend>Resultado</legend>                
                <input type="text" id="result" name="result" disabled value="<?php if(isset ($cancelaResultado)) echo $cancelaResultado; ?>"/>
            </fieldset>


        </fieldset>
    </body>
</html>
