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

require $_SERVER['DOCUMENT_ROOT'] . "/AWOSVS/main/firebase-php-jwt/vendor/autoload.php";
$con = new Conexion(array(
  "tipo"       => "mysql",
  "servidor"   => "46.28.42.226",
  "bd"         => "u760464709_24005224_bd",
  "usuario"    => "u760464709_24005224_usr",
  "contrasena" => "8PEd!gd5x+Sb"
));

/*
$con = new Conexion(array(
  "tipo"       => "mysql",
  "servidor"   => "localhost",
  "bd"         => "clima_db",
  "usuario"    => "root",
  "contrasena" => ""
));
/*
echo "conexion_ok";
exit;
*/
//--------------------------------------------------------//

$headers = getallheaders();
$token = "";
if (isset($headers["Authorization"])) {
  $token = str_replace("Bearer ", "", $headers["Authorization"]);
}

try {
  $decoded = Firebase\JWT\JWT::decode($token, new Firebase\JWT\Key("Test12345-----------------------------------------------", "HS256"));
  $usuario = explode("/", $decoded->sub);
  $id_usuario = $usuario[0];
  $usr        = $usuario[1];
  $tipo       = $usuario[2];
  $login = true;
}
catch (Exception $e) {
  $login = false;
  $id_usuario = null;
}
$esAdmin = $login && $tipo == "1";

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

elseif (isset($_GET["consultas"])) {

    $select = $con->select(
        "view_consultas_detalle",
        "*"
    );

        if (!empty($_GET["buscar"])) {
            $select->where("descripcion", "LIKE", "%" . $_GET["buscar"] . "%");
        }

        $select->orderby("id_consulta DESC");
        $select->limit(10);

        header("Content-Type: application/json");
        echo json_encode($select->execute());
}


elseif (isset($_GET["eliminarConsulta"])&& $esAdmin) {

    $id = $_POST["txtId"];

    $sql = "CALL eliminarConsulta(:id, @eliminado)";
    $stmt = $con->prepare($sql);

    if ($stmt->execute([':id' => $id])) {
    echo "correcto";
    } else {
        echo "error";
    }
}

elseif (isset($_GET["ciudadesCombo"])) {

    $select = $con->select("ciudades", "id_ciudad, nombre, pais");
    $select->orderby("nombre ASC");

    header("Content-Type: application/json");
    echo json_encode($select->execute());
}

elseif (isset($_GET["usuariosCombo"])) {

    $select = $con->select("usuarios", "id_usuario, nombre, email");
    $select->orderby("nombre ASC");

    header("Content-Type: application/json");
    echo json_encode($select->execute());
}


elseif (isset($_GET["agregarConsulta"])) {

    $usuario = $_POST["cboUsuario"];
    $ciudad = $_POST["cboCiudad"];
    $temperatura = $_POST["txtTemperatura"];
    $descripcion = $_POST["txtDescripcion"];
    $humedad = $_POST["txtHumedad"]; 

    $sql = "CALL agregarConsulta(:usuario, :ciudad, :temp, :descripcion, :humedad, @nuevoId, @tempOut, @descOut, @humOut)";
    $stmt = $con->prepare($sql);

    $stmt->execute([
        ':usuario' => $usuario,
        ':ciudad' => $ciudad,
        ':temp' => $temperatura,
        ':descripcion' => $descripcion,
        ':humedad' => $humedad
    ]);

    echo "correcto";
}



elseif (isset($_GET["obtenerConsulta"])) {

    $select = $con->select("view_consultas_detalle", "*");
    $select->where("id_consulta", "=", $_GET["id"]);

    header("Content-Type: application/json");
    echo json_encode($select->execute());
}



elseif (isset($_GET["modificarConsulta"]) && $esAdmin) {

    $id = $_POST["txtIdConsulta"];
    $usuario = $_POST["cboUsuario"];
    $ciudad = $_POST["cboCiudad"];
    $temperatura = $_POST["txtTemperatura"];
    $descripcion = $_POST["txtDescripcion"];
    $humedad = $_POST["txtHumedad"];

    $sql = "CALL modificarConsulta(:id, :usuario, :ciudad, :temp, :descripcion, :humedad, 
            @idMod, @userMod, @cityMod, @tempMod, @descMod, @humMod)";

    $stmt = $con->prepare($sql);

    if ($stmt->execute([
    ':id' => $id,
    ':usuario' => $usuario,
    ':ciudad' => $ciudad,
    ':temp' => $temperatura,
    ':descripcion' => $descripcion,
    ':humedad' => $humedad
    ])) {
        $stmt->closeCursor();
        echo "correcto";
    } else {
        print_r($stmt->errorInfo());
    }
}

?>
