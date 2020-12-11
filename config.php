<?php
//un comentario de prueba
$rutaAbsoluta = substr(dirname(__FILE__), 0, strrpos(dirname(__FILE__),"/"));
// Paso adicional porque el nivel superior de la ubicación de config.php, todavía forma parte de la carpeta pública
$rutaAbsoluta = substr($rutaAbsoluta, 0, strrpos($rutaAbsoluta,"/"));
include_once("$rutaAbsoluta/datosConexion.php");

$DefaultLanguage = 'sp_MX';

$allow_demo_mode = false;

$Version = '1.0';
$host = datosConexion::host();

$dbType = 'mysqli';
$dbuser = datosConexion::user();
$dbpassword = datosConexion::password();
$mysqlport = 3306;
$dbpass = $dbpassword;

$confJasper = ['host'=>$host,'dbuser'=>$dbuser,'dbpassword'=>$dbpassword];
$dbsocket = '';
putenv('America/Chicago');
$getdir = explode('.', ($_SERVER ['HTTP_HOST']));
$getdir1 = $getdir [0];

if ($getdir1 == 'erp') {
    $AllowCompanySelectionBox = false;
    //setcookie("defaultempresa", 'erpgubernamental');
    setcookie("defaultempresa", 'erpgubernamental',time()+3600, "/", null, null, true);
} else {
    $AllowCompanySelectionBox = false;
    //setcookie("defaultempresa", $getdir1);
    setcookie("defaultempresa", $getdir1,time()+3600, "/", null, null, true);
}
$DefaultCompany = 'ap_grp_de';
$SessionLifeTime = 7200;
$MaximumExecutionTime = 120;
$CryptFunction = 'sha1';
$DefaultClock = 12;
$rootpath = dirname($_SERVER ['PHP_SELF']);

if (isset($DirectoryLevelsDeep)) {
    for ($i = 0; $i < $DirectoryLevelsDeep; $i ++) {
        $rootpath = substr($rootpath, 0, strrpos($rootpath, '/'));
    }
}
if ($rootpath == '/' or $rootpath == '\\') {
    ;
    $rootpath = '';
}
