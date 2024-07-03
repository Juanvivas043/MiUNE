<?php

ini_set('include_path', '../../library/');
require_once("Zend/Session.php");

$session = new Zend_Session_Namespace('Zend_Auth');

$ci = $session->userId;
$conn  = pg_connect("user=MiUNE password=M1UN3@OWNER:k5p9q6vv4xklmu709vz dbname=MiUNE host=localhost");
$query = pg_query($conn, "SELECT foto FROM tbl_usuarios where pk_usuario = '" . $ci . "';");
$row   = pg_fetch_row($query);
if(!isset($row[0])){

    $data  = file_get_contents('profile.png');
    header("Content-type: image/jpeg");
    echo $data;


}else {

    $image = pg_unescape_bytea($row[0]);
    header("Content-type: image/jpeg");
    echo $image;

}




pg_close($conn);


?>
