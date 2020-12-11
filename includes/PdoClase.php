<?php
/**
 * Created by PhpStorm.
 * User: Arturo Lopez Peña
 * Date: 11/08/17
 * Time: 00:45
 */

class PdoClase{

    //inicio metódo conexión
    public function fnConexion($host , $dbusuario, $dbpass, $dbnombrebasededatos, $mysqlport, $dbsocket='')
    {
        $dsn="";
        try
        {


            //$dsn = ("mysql:host=".$host.";dbname=".$dbnombrebasededatos.";port=".$mysqlport);
            //$dsn = 'mysql:host=localhost;dbname=testdb';
            //$conexion = new PDO($dsn, $dbusuario, $dbpass);

            //$conexion = new PDO("mysql:dbname=$dbnombrebasededatos;host=$host",$dbusuario, $dbpass);
            $conexion = new PDO('mysql:host='.$host.';dbname='.$dbnombrebasededatos.';port='.$mysqlport,$dbusuario,$dbpass);
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

             //echo "conexion hecha";
            return $conexion;
           
        }
        catch (PDOException $e)
        {
            //echo "Conexión fallida: " ;//. $e->getMessage();
        }


    } //fin metodo conexión

    public function fnPrueba($algo)
    {
        echo  $algo;
    }

} //fin clase PdoClase




?>
