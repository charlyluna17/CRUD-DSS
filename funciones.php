<?php   
//Iniciamos la sesion para usar $_SESSION y guardar los productos para que los datos 
//persistan en el servidor
session_start(); 

//si la matriz de producto no existe ,la inicializamos vacia
if (!isset($_SESSION["productos"])) {
    $_SESSION["productos"] = [];
}

//FUNCION QUE VALIDA LOS DATOS DEL FORMULARIO
function validarProducto($id, $nombre, $descripcion, $precio, $stock, $categoria, $esEdicion = false) {
    $errores = [];

    // Validacion de campos vacios para avisar que no se pueden dejar vacios
    if (empty($id) || empty($nombre) || empty($descripcion) || empty($precio) || empty($stock) || empty($categoria)) {
        $errores[] = "Todos los campos son obligatorios.";
    }

    // Validar que el ID no se repita para los productos nuevos
    if (!$esEdicion && isset($_SESSION['productos'][$id])) {
        $errores[] = "El ID ya existe. Por favor use uno diferente.";
    }

    // Validar valores numéricos y no negativos 
    if (!is_numeric($precio) || $precio < 0) {
        $errores[] = "El precio debe ser un numero positivo.";
    }
    if (!is_numeric($stock) || $stock < 0) {
        $errores[] = "El stock debe ser un numero entero no negativo.";
    }

    return $errores;
}

// FUNCION PARA REALIZAR UNA VENTA 
function realizarVenta($id, $cantidad = 1) {
    if (isset($_SESSION["productos"][$id])) {
        // Verificar si hay stock suficiente 
        if ($_SESSION["productos"][$id]["stock"] >= $cantidad) {
            $_SESSION["productos"][$id]["stock"] -= $cantidad; 
            return true;
        }
    }
    return false;
}

//FUNCION ACTUALIZAR UN PRODUCTO YA EXISTENTE EN LA TABLA
function actualizarProducto($id, $nombre, $descripcion, $precio, $stock, $categoria) {
    if (isset($_SESSION['productos'][$id])) {
        $_SESSION['productos'][$id] = [
            'id' => $id,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'stock' => $stock,
            'categoria' => $categoria
        ];
        return true;
    }
    return false;
}


