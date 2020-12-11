<?php
        if(isset ($_POST['estatus']))
        {
            include 'includes/estadoCliente.php';

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
            <legend>cliente</legend>
        <form method="post" action="estado.php">
            <label>RFC</label><input type="text" name="rfc" id="rfc" value="<?php if(isset($_POST['rfc'])){echo $_POST['rfc'];} ?>" /><br />            
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
                <legend>Información</legend>
                <label>Assigned Invoices</label><input type="text" id="assigned" name="assigned" disabled value="<?php if(isset ($assignedEstatus)) echo $assignedEstatus; ?>"/><br>
                <label>Remaining Invoices</label><input type="text" id="remaining" name="Remaining" disabled value="<?php if(isset ($remainingEstatus)) echo $remainingEstatus; ?>"/><br>
                <label>Used Invoices</label><input type="text" id="used" name="used" disabled value="<?php if(isset ($usedEstatus)) echo $usedEstatus; ?>"/><br>
                <label>Start Date</label><input type="text" id="startDate" name="startDate" disabled value="<?php if(isset ($startDateEstatus)) echo $startDateEstatus; ?>"/><br>
                <label>End Date</label><input type="text" id="endDate" name="endDate" disabled value="<?php if(isset ($endDateEstatus)) echo $endDateEstatus; ?>"/><br>
                <label>Description</label><input type="text" id="description" name="description" disabled value="<?php if(isset ($descriptionEstatus)) echo $descriptionEstatus; ?>"/>
            </fieldset>


        </fieldset>
    </body>
</html>
