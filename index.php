<?php   
// 1. Iniciamos sesión al principio de todo
session_start(); 

// 2. Inicializamos la matriz de productos si no existe
if (!isset($_SESSION["productos"])) {
    $_SESSION["productos"] = [];
}

// --- FUNCIONES DE LÓGICA ---

function validarProducto($id, $nombre, $descripcion, $precio, $stock, $categoria, $esEdicion = false) {
    $errores = [];
    if (empty($id) || empty($nombre) || empty($descripcion) || $precio === "" || $stock === "" || empty($categoria)) {
        $errores[] = "Todos los campos son obligatorios.";
    }
    if (!$esEdicion && isset($_SESSION['productos'][$id])) {
        $errores[] = "El ID ya existe. Por favor use uno diferente.";
    }
    if (!is_numeric($precio) || $precio < 0) {
        $errores[] = "El precio debe ser un número positivo.";
    }
    if (!is_numeric($stock) || $stock < 0) {
        $errores[] = "El stock debe ser un número entero no negativo.";
    }
    return $errores;
}

function realizarVenta($id, $cantidad = 1) {
    if (isset($_SESSION["productos"][$id])) {
        if ($_SESSION["productos"][$id]["stock"] >= $cantidad) {
            $_SESSION["productos"][$id]["stock"] -= $cantidad; 
            return true;
        }
    }
    return false;
}

// --- PROCESAMIENTO DE ACCIONES ---

$mensaje = "";
$prodEditar = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion'])) {
    
    // GUARDAR O ACTUALIZAR
    if ($_POST['accion'] == 'guardar') {
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $desc = $_POST['descripcion'];
        $precio = $_POST['precio'];
        $stock = $_POST['stock'];
        $cat = $_POST['categoria'];
        $esEdicion = isset($_POST['es_edicion']) && $_POST['es_edicion'] == "1";

        $errores = validarProducto($id, $nombre, $desc, $precio, $stock, $cat, $esEdicion);

        if (empty($errores)) {
            $_SESSION['productos'][$id] = [
                'id' => $id,
                'nombre' => $nombre,
                'descripcion' => $desc,
                'precio' => (float)$precio,
                'stock' => (int)$stock,
                'categoria' => $cat
            ];
            $mensaje = $esEdicion ? "✅ Producto actualizado con éxito." : "✅ Producto guardado con éxito.";
        } else {
            $mensaje = "❌ " . implode(" ", $errores);
        }
    }

    // ELIMINAR
    if($_POST["accion"] == "eliminar") {
        $idEliminar = $_POST["id"];
        unset($_SESSION["productos"][$idEliminar]);
        $mensaje = "🗑️ Producto eliminado exitosamente";
    }
    
    // VENDER
    if ($_POST["accion"] == "vender") {
        $idVenta = $_POST["id"];
        if (realizarVenta($idVenta)) {
            $mensaje = "💰 Venta realizada. Stock actualizado.";
        } else {
            $mensaje = "⚠️ Error: Stock insuficiente.";
        }
    }
}

// DETECTAR MODO EDICIÓN (GET)
if(isset($_GET["editar"])) {
    $idEditar = $_GET["editar"];
    if(isset($_SESSION["productos"][$idEditar])) {
        $prodEditar = $_SESSION["productos"][$idEditar];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inventario</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>

<div class="main-container">
    <h1>Gestión de Productos</h1>  

    <?php if ($mensaje): ?>
        <div class="alerta"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <section class="card">
        <h2><?php echo $prodEditar ? "✏️ Editar Producto" : "➕ Registrar Nuevo Producto"; ?></h2>
        <form method="POST">    
            <input type="hidden" name="accion" value="guardar">
            
            <div class="form-grid">
                <?php if ($prodEditar): ?>
                    <input type="hidden" name="es_edicion" value="1">
                    <input type="hidden" name="id" value="<?php echo $prodEditar['id']; ?>">
                    <p>Editando ID: <strong><?php echo $prodEditar['id']; ?></strong></p>
                <?php else: ?>
                    <input type="text" name="id" placeholder="ID del Producto (ej: P001)" required>
                <?php endif; ?>

                <input type="text" name="nombre" placeholder="Nombre del producto" value="<?php echo $prodEditar ? $prodEditar['nombre'] : ''; ?>" required>
                
                <textarea name="descripcion" placeholder="Descripción breve" rows="2"><?php echo $prodEditar ? $prodEditar['descripcion'] : ''; ?></textarea>
                
                <div style="display: flex; gap: 10px;">
                    <input type="number" step="0.01" name="precio" placeholder="Precio ($)" value="<?php echo $prodEditar ? $prodEditar['precio'] : ''; ?>" required>
                    <input type="number" name="stock" placeholder="Stock inicial" value="<?php echo $prodEditar ? $prodEditar['stock'] : ''; ?>" required>
                </div>
                
                <select name="categoria">
                    <option value="" disabled <?php echo !$prodEditar ? 'selected' : ''; ?>>Seleccione Categoría</option>
                    <option value="Electronica" <?php echo ($prodEditar && $prodEditar['categoria'] == 'Electronica') ? 'selected' : ''; ?>>Electrónica</option>
                    <option value="Ropa" <?php echo ($prodEditar && $prodEditar['categoria'] == 'Ropa') ? 'selected' : ''; ?>>Ropa</option>
                    <option value="Hogar" <?php echo ($prodEditar && $prodEditar['categoria'] == 'Hogar') ? 'selected' : ''; ?>>Hogar</option>
                </select>

                <button type="submit" class="btn-submit">
                    <?php echo $prodEditar ? "Guardar Cambios" : "Añadir al Inventario"; ?>
                </button>

                <?php if ($prodEditar): ?>
                    <a href="index.php" style="display: block; text-align: center; margin-top: 10px; color: var(--secondary);">Cancelar Edición</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="card">
        <h2>Inventario Actual</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Stock</th>
                    <th>Precio</th>
                    <th style="text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($_SESSION['productos'])): ?>
                    <tr><td colspan="6" style="text-align: center; padding: 40px; color: var(--secondary);">No hay productos registrados aún.</td></tr>
                <?php else: ?>
                    <?php foreach ($_SESSION['productos'] as $prod): ?>
                        <tr>
                            <td><strong><?php echo $prod['id']; ?></strong></td>
                            <td>
                                <div><?php echo $prod['nombre']; ?></div>
                                <small style="color: #64748b; font-size: 0.8rem;"><?php echo $prod['descripcion']; ?></small>
                            </td>
                            <td><span class="badge"><?php echo $prod['categoria']; ?></span></td>
                            <td>
                                <span style="color: <?php echo $prod['stock'] < 5 ? 'var(--danger)' : 'inherit'; ?>; font-weight: bold;">
                                    <?php echo $prod['stock']; ?>
                                </span>
                            </td>
                            <td>$<?php echo number_format($prod['precio'], 2); ?></td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 5px; justify-content: center;">
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                                        <input type="hidden" name="accion" value="vender">
                                        <button type="submit" class="btn-vender" <?php echo $prod['stock'] <= 0 ? 'disabled' : ''; ?> title="Vender uno">💰</button>
                                    </form>

                                    <a href="index.php?editar=<?php echo $prod['id']; ?>" class="btn-editar" title="Editar">✏️</a>

                                    <form method="POST" onsubmit="return confirm('¿Eliminar este producto?');">
                                        <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <button type="submit" class="btn-eliminar" title="Eliminar">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>

</body>
</html>
