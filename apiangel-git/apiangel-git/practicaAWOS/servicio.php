<?php

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL & ~E_DEPRECATED);

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Allow: GET, POST, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
  http_response_code(200);
  exit;
}

if (isset($_GET["PING"])) {
  exit;
}

date_default_timezone_set("America/Matamoros");

if (isset($_GET["DATETIME"])) {
  echo date("Y-m-d H:i:s");
  exit;
}


// ------------------------------------------------------
// ------------------------------------------------------
// Debajo de este comentario irá la configuración a la BD
// y las funciones del servicio para la aplicación móvil.

require "conexion.php";
require "enviarCorreo.php";

$con = new Conexion(array(
  "tipo"       => "mysql",
  "servidor"   => "46.28.42.226",
  "bd"         => "u760464709_24005224_bd",
  "usuario"    => "u760464709_24005224_usr",
  "contrasena" => "8PEd!gd5x+Sb"
));

if (isset($_GET["iniciarSesion"])) {
  $select = $con->select("usuarios", "id");
  $select->where("usuario", "=", $_POST["usuario"]);
  $select->where_and("contrasena", "=", $_POST["contrasena"]);

  if (count($select->execute())) {
    echo "correcto";
  }
  else {
    echo "error";
  }
}
elseif (isset($_GET["usuarios"])) {
  $select = $con->select("view_usr_busquedas");
  //$select->innerjoin("categorias ON categorias.id = usuarios.categoria");
  $select->orderby("id_usuario DESC");
  $select->limit(10);

  header("Content-Type: application/json");
  echo json_encode($select->execute());
}
elseif (isset($_GET["editarUsuario"])) {
  $id = $_GET["id"];

  $select = $con->select("usuarios", "*");
  $select->where("id_usuario", "=", $id);

  header("Content-Type: application/json");
  echo json_encode($select->execute());
}
elseif (isset($_GET["categoriasCombo"])) {
  $select = $con->select("categorias", "id AS value, nombre AS label");
  $select->orderby("nombre ASC");
  $select->limit(10);

  $array = array(array("index" => 0, "value" => "", "label" => "Selecciona una opción"));

  foreach ($select->execute() as $x => $categoria) {
      $array[] = array("index" => $x + 1, "value" => $categoria["value"],  "label" => $categoria["label"]);
  }

  header("Content-Type: application/json");
  echo json_encode($array);
}
elseif (isset($_GET["eliminarUsuario"])) {
  $prepare = $con->prepare("CALL eliminarUsuario(:id_usuario)");
  $prepare->bindParam(":id_usuario",$_POST["txtId"]);

  if ($prepare->execute()) {
    echo "correcto";
  }
  else {
    echo "error";
  }
}
elseif (isset($_GET["agregarUsuario"])) {

    $prepare = $con->prepare("CALL insertarUsuario(:nombre, :email, :password)");

    #$password = password_hash($_POST["txtContrasena"], PASSWORD_DEFAULT);

    $prepare->bindParam(":nombre", $_POST["txtNombre"]);
    $prepare->bindParam(":email", $_POST["txtEmail"]);
    $prepare->bindParam(":password", $_POST["txtContrasena"]);
    
    $prepare->execute();

    echo "correcto";
}
elseif (isset($_GET["modificarUsuario"])) {
  $prepare = $con->prepare("CALL modificarUsuario(:id_usuario,:nombre,:email,:password)");
  $prepare->bindParam(":id_usuario",$_POST["txtId"]);
  $prepare->bindParam(":nombre",$_POST["txtNombre"]);
  $prepare->bindParam(":email",$_POST["txtEmail"]);
  $prepare->bindParam(":password",$_POST["txtContrasena"]);
  if ($prepare->execute()) {
    echo "correcto";
  }
  else {
    echo "error";
  }
}

?>
