<?php   
require_once("funciones.php");

$mensaje = "";
$prodEditar = null;

//LOGICA PARA EL FORMULARIO DE "agregar producto"

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion'])) {
    //ACTUALIZAR O GUARDAR
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
            $mensaje = $esEdicion ? "Producto actualizado con éxito." : "Producto guardado con éxito.";
            // Limpiamos la variable de edición para que el formulario vuelva a ser de registro 
            $prodEditar = null; 
        } else {
            $mensaje = implode("<br>", $errores);
        }

        
    }

    //ACCION PARA ELIMINAR UN PRODUCTO
    if($_POST["accion"] == "eliminar") {
        $idEliminar = $_POST["id"];
        unset($_SESSION["productos"][$idEliminar]);
        $mensaje = "Producto eliminado exitosamente";
    }
    
    //ACCIONES PARA VENDER UN PRODUCTO
    if ($_POST["accion"] == "vender") {
        $idVenta = $_POST["id"];
        if (realizarVenta($idVenta)) {
            $mensaje = "Venta realizada. Stock actualizado.";
        } else {
            $mensaje = "Error: Stock insuficiente.";
        }
    }
    
   
}

//detectar modo editor
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
    <title>Gestión de Productos </title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Gestión de Productos</h1>  
    <?php if ($mensaje): ?>
        <p class="alerta" style="color: blue; font-weight: bold;"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <section>
        <h2><?php echo $prodEditar ? "Editar Producto" : "Registrar Nuevo Producto"; ?></h2>
        <form method="POST">    
            <input type="hidden" name="accion" value="guardar">
            
            <?php if ($prodEditar): ?>
                <input type="hidden" name="es_edicion" value="1">
                <input type="hidden" name="id" value="<?php echo $prodEditar['id']; ?>">
                <p>Editando ID: <strong><?php echo $prodEditar['id']; ?></strong></p>
            <?php else: ?>
                <input type="text" name="id" placeholder="ID del Producto" required>
            <?php endif; ?>

            <input type="text" name="nombre" placeholder="Nombre" value="<?php echo $prodEditar ? $prodEditar['nombre'] : ''; ?>" required>
            <textarea name="descripcion" placeholder="Descripción"><?php echo $prodEditar ? $prodEditar['descripcion'] : ''; ?></textarea>
            <input type="number" step="0.01" name="precio" placeholder="Precio" value="<?php echo $prodEditar ? $prodEditar['precio'] : ''; ?>" required>
            <input type="number" name="stock" placeholder="Stock" value="<?php echo $prodEditar ? $prodEditar['stock'] : ''; ?>" required>
            
            <select name="categoria">
                <option value="Electronica" <?php echo ($prodEditar && $prodEditar['categoria'] == 'Electronica') ? 'selected' : ''; ?>>Electrónica</option>
                <option value="Ropa" <?php echo ($prodEditar && $prodEditar['categoria'] == 'Ropa') ? 'selected' : ''; ?>>Ropa</option>
                <option value="Hogar" <?php echo ($prodEditar && $prodEditar['categoria'] == 'Hogar') ? 'selected' : ''; ?>>Hogar</option>
            </select>

            <button type="submit"><?php echo $prodEditar ? "Actualizar Cambios" : "Registrar Producto"; ?></button>
            <?php if ($prodEditar): ?>
                <a href="index.php">Cancelar Edición</a>
            <?php endif; ?>
        </form>
    </section>

    <hr>

    <table border="1" width="100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Stock</th>
                <th>Precio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($_SESSION['productos'])): ?>
                <tr><td colspan="6" style="text-align: center;">No hay productos registrados.</td></tr>
            <?php else: ?>
                <?php foreach ($_SESSION['productos'] as $prod): ?>
                    <tr>
                        <td><?php echo $prod['id']; ?></td>
                        <td><?php echo $prod['nombre']; ?></td>
                        <td><?php echo $prod['categoria']; ?></td>
                        <td><?php echo $prod['stock']; ?></td>
                        <td>$<?php echo number_format($prod['precio'], 2); ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                                <input type="hidden" name="accion" value="vender">
                                <button type="submit" <?php echo $prod['stock'] <= 0 ? 'disabled' : ''; ?>>Vender</button>
                            </form>

                            <a href="index.php?editar=<?php echo $prod['id']; ?>"><button>Editar</button></a>

                            <form method="POST" style="display:inline;" onsubmit="return confirm('¿Está seguro de eliminar este producto?');">
                                <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                                <input type="hidden" name="accion" value="eliminar">
                                <button type="submit" style="color: red;">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
    