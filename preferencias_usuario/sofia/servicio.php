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

if (isset($_GET["PING"])) exit;

date_default_timezone_set("America/Matamoros");

if (isset($_GET["DATETIME"])) {
  echo date("Y-m-d H:i:s");
  exit;
}

require "conexion.php";
require $_SERVER['DOCUMENT_ROOT'] . "/AWOSVS/main/firebase-php-jwt/vendor/autoload.php";
$con = new Conexion(array(
  "tipo"       => "mysql",
  "servidor"   => "46.28.42.226",
  "bd"         => "u760464709_24005224_bd",
  "usuario"    => "u760464709_24005224_usr",
  "contrasena" => "8PEd!gd5x+Sb"
));

// =============================================
// VALIDAR JWT (igual que tu maestro)
// =============================================
$headers = getallheaders();
$token = "";
if (isset($headers["Authorization"])) {
  $token = str_replace("Bearer ", "", $headers["Authorization"]);
}

try {
  $decoded = Firebase\JWT\JWT::decode($token, new Firebase\JWT\Key("Test12345-----------------------------------------------", "HS256"));
  $usuario = explode("/", $decoded->sub);
  $id_usuario = $usuario[0];  // ← esto reemplaza $_SESSION['id_usuario']
  $usr        = $usuario[1];
  $tipo       = $usuario[2];
  $login = true;
}
catch (Exception $e) {
  $login = false;
  $id_usuario = null;
}

// =============================================
// ENDPOINTS
// =============================================

// OBTENER PREFERENCIAS
if (isset($_GET["obtener_preferencias"]) && $login) {

    $stmt = $con->ejecutar(
        "SELECT id_usuario, nombre, preferencias
         FROM vista_preferencias_completo
         WHERE id_usuario = ?",
        [$id_usuario]  
    );

    $pref = $stmt->fetch(PDO::FETCH_ASSOC);

    header("Content-Type: application/json");
    echo json_encode([
        'id_usuario'   => $pref['id_usuario']   ?? null,
        'nombre'       => $pref['nombre']        ?? null,
        'preferencias' => $pref['preferencias']  ?? null,
        'logueado'     => true
    ]);
    exit;
}

// OBTENER PREFERENCIAS sin login → respuesta vacía
elseif (isset($_GET["obtener_preferencias"]) && !$login) {
    header("Content-Type: application/json");
    echo json_encode(['unidad_temperatura' => 'Celsius', 'logueado' => false]);
    exit;
}


elseif (isset($_GET["preferencias"])&& $login) {
    $select = $con->select("vista_preferencias_completo");
    header("Content-Type: application/json");
    echo json_encode($select->execute());
}

// ELIMINAR
elseif (isset($_GET["eliminarpreferencia"]) && $login) {
    $idUsuario = $_POST["id_usuario"];
    $prepare = $con->prepare("CALL eliminar_preferencias(:p_id_usuario)");
    $prepare->bindParam(":p_id_usuario", $idUsuario);
    $prepare->execute();

    echo $prepare->rowCount() > 0 ? "correcto" : "error";
    exit;
}

// GUARDAR PREFERENCIAS
elseif (isset($_GET["guardar_preferencias"]) && $login) {

    $unidad = ($_POST['unidad'] === 'Fahrenheit') ? 'Fahrenheit' : 'Celsius';
    $tema   = ($_POST['tema']   === 'oscuro')     ? 'oscuro'     : 'claro';

    $stmt = $con->ejecutar(
        "SELECT id_usuario FROM preferencias_usuario WHERE id_usuario = ?",
        [$id_usuario]  // ← ya no usas $_SESSION
    );

    if ($stmt->rowCount() > 0) {
        $con->ejecutar(
            "UPDATE preferencias_usuario SET unidad_temperatura = ?, tema = ? WHERE id_usuario = ?",
            [$unidad, $tema, $id_usuario]
        );
    } else {
        $con->ejecutar(
            "INSERT INTO preferencias_usuario (id_usuario, unidad_temperatura, tema) VALUES (?, ?, ?)",
            [$id_usuario, $unidad, $tema]
        );
    }

    echo "correcto";
    exit;
}

// AGREGAR CON SP
elseif (isset($_GET["agregar_preferencia_sp"]) && $login) {
    $prepare = $con->prepare("CALL insertar_preferencias(:p_id_usuario, :p_unidad_temperatura, :p_tema)");
    $prepare->bindParam(":p_id_usuario", $_POST["id_usuario"]);
    $prepare->bindParam(":p_unidad_temperatura", $_POST["unidad"]);
    $prepare->bindParam(":p_tema", $_POST["tema"]);
    $prepare->execute();

    echo "correcto";
    exit;
}

// MODIFICAR CON SP
elseif (isset($_GET["modificar_preferencia"]) && $login) {
    $prepare = $con->prepare("CALL actualizar_preferencias(:p_id_usuario, :p_unidad_temperatura, :p_tema)");
    $prepare->bindParam(":p_id_usuario", $_POST["id_usuario"]);
    $prepare->bindParam(":p_unidad_temperatura", $_POST["unidad"]);
    $prepare->bindParam(":p_tema", $_POST["tema"]);
    $prepare->execute();

    echo "correcto";
    exit;
}
?>