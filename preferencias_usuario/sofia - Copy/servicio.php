header("Content-Type: application/json");
$id_usuario = $_SESSION['id_usuario']; // Usa el ID de la sesión
$db = new Conexion();
$accion = $_POST['accion'] ?? '';

// Obtener preferencias
if($accion === 'obtener_preferencias') {
    $stmt = $db->ejecutar(
        "SELECT unidad_temperatura, tema 
         FROM preferencias_usuario 
         WHERE id_usuario = ?",
        [$id_usuario]
    );
    $pref = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'unidad_temperatura' => $pref['unidad_temperatura'] ?? 'C',
        'tema' => $pref['tema'] ?? 'claro'
    ]);
    exit;
}

// Guardar preferencias
if($accion === 'guardar_preferencias') {
    $unidad = $_POST['unidad'] === 'F' ? 'F' : 'C';
    $tema = $_POST['tema'] === 'oscuro' ? 'oscuro' : 'claro';
    
    // Verificar si ya existen preferencias
    $stmt = $db->ejecutar(
        "SELECT id_usuario FROM preferencias_usuario WHERE id_usuario = ?",
        [$id_usuario]
    );
    
    if($stmt->rowCount() > 0) {
        // Actualizar
        $db->ejecutar(
            "UPDATE preferencias_usuario 
             SET unidad_temperatura = ?, tema = ? 
             WHERE id_usuario = ?",
            [$unidad, $tema, $id_usuario]
        );
    } else {
        // Insertar
        $db->ejecutar(
            "INSERT INTO preferencias_usuario 
             (id_usuario, unidad_temperatura, tema) 
             VALUES (?, ?, ?)",
            [$id_usuario, $unidad, $tema]
        );
    }
    
    echo json_encode(['status' => 'ok']);
    exit;
}

// Si no se reconoce la acción
echo json_encode(['error' => 'Acción no válida']);