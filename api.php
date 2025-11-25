<?php
header('Content-Type: application/json; charset=utf-8');

//
// Zona horaria
//
date_default_timezone_set('America/Argentina/Buenos_Aires');

// 
// CONFIGURACION BD
// 
require_once __DIR__ . '/db/config.php';

// 
// FUNCIONES AUXILIARES
// 
function responder_json(array $datos, int $codigo_http = 200): void
{
    http_response_code($codigo_http);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function limpiar_cadena(string $valor): string
{
    $valor = trim($valor);
    $valor = stripslashes($valor);
    return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function limpiar_para_sql(mysqli $conexion, string $valor): string
{
    $valor = limpiar_cadena($valor);
    return mysqli_real_escape_string($conexion, $valor);
}

// 
// LISTADO/FILTROS/PAGINACION (GET)
// 
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $busqueda = isset($_GET['busqueda']) ? limpiar_cadena($_GET['busqueda']) : '';
    $album = isset($_GET['album']) ? limpiar_cadena($_GET['album']) : '';
    $pagina = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;

    if ($pagina < 1)
        $pagina = 1;

    $por_pagina = 4;
    $offset = ($pagina - 1) * $por_pagina;

    $condiciones = [];
    if ($busqueda !== '') {
        $busqueda_sql = limpiar_para_sql($conexion, $busqueda);
        $condiciones[] = "titulo LIKE '%{$busqueda_sql}%'";
    }

    if ($album !== '') {
        $album_sql = limpiar_para_sql($conexion, $album);
        $condiciones[] = "album = '{$album_sql}'";
    }

    $where = '';
    if (!empty($condiciones))
        $where = 'WHERE ' . implode(' AND ', $condiciones);

    // Total de registros (para paginacion)
    $sql_total = "SELECT COUNT(*) AS total FROM canciones {$where}";
    $resultado_total = mysqli_query($conexion, $sql_total);
    $total_registros = 0;

    if ($resultado_total) {
        $fila_total = mysqli_fetch_assoc($resultado_total);
        $total_registros = (int) $fila_total['total'];
        mysqli_free_result($resultado_total);
    }

    $paginas_totales = $total_registros > 0 ? (int) ceil($total_registros / $por_pagina) : 1;
    if ($pagina > $paginas_totales) {
        $pagina = $paginas_totales;
        $offset = ($pagina - 1) * $por_pagina;
    }

    // Consulta principal
    $sql = "SELECT id, titulo, album, descripcion, imagen
                FROM canciones
                    {$where}
                ORDER BY id DESC
                LIMIT {$offset}, {$por_pagina}";

    $resultado = mysqli_query($conexion, $sql);

    $canciones = [];
    if ($resultado) {
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $canciones[] = $fila;
        }

        mysqli_free_result($resultado);
    }

    // Lista de albumes para el select
    $sql_albumes = "SELECT DISTINCT album FROM canciones ORDER BY album ASC";
    $resultado_albumes = mysqli_query($conexion, $sql_albumes);

    $albumes = [];
    if ($resultado_albumes) {
        while ($fila = mysqli_fetch_assoc($resultado_albumes)) {
            $albumes[] = $fila['album'];
        }

        mysqli_free_result($resultado_albumes);
    }

    // respuesta al front
    $respuesta = [
        'exito' => true,
        'fecha_consulta' => date('Y-m-d H:i:s'),
        'filtros' => [
            'busqueda' => $busqueda,
            'album' => $album,
        ],
        'paginacion' => [
            'pagina_actual' => $pagina,
            'paginas_totales' => $paginas_totales,
            'por_pagina' => $por_pagina,
            'total_registros' => $total_registros,
        ],
        'albumes' => $albumes,
        'canciones' => $canciones,
    ];

    responder_json($respuesta);
}

//
// CREAR/EDITAR/ELIMINAR (POST)
//
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $accion = isset($_POST['accion']) ? $_POST['accion'] : 'crear';

    // 
    // CREAR
    // 
    if ($accion === 'crear') {
        $titulo = isset($_POST['titulo']) ? limpiar_cadena($_POST['titulo']) : '';
        $album = isset($_POST['album']) ? limpiar_cadena($_POST['album']) : '';
        $descripcion = isset($_POST['descripcion']) ? limpiar_cadena($_POST['descripcion']) : '';
        $imagen = isset($_POST['imagen']) ? limpiar_cadena($_POST['imagen']) : '';

        $errores = [];
        if (empty($titulo))
            $errores['titulo'] = 'El título es obligatorio.';

        if (empty($album))
            $errores['album'] = 'El álbum es obligatorio.';

        if (empty($descripcion))
            $errores['descripcion'] = 'La descripción es obligatoria.';

        if (empty($imagen))
            $errores['imagen'] = 'La URL de la imagen es obligatoria.';

        if (!empty($errores)) {
            responder_json([
                'exito'   => false,
                'mensaje' => 'Hay errores de validación.',
                'errores' => $errores
            ], 400);
        }

        $titulo_sql = limpiar_para_sql($conexion, $titulo);
        $album_sql = limpiar_para_sql($conexion, $album);
        $descripcion_sql = limpiar_para_sql($conexion, $descripcion);
        $imagen_sql = limpiar_para_sql($conexion, $imagen);

        $sql_insert = "INSERT INTO canciones (titulo, album, descripcion, imagen)
                       VALUES ('{$titulo_sql}', '{$album_sql}', '{$descripcion_sql}', '{$imagen_sql}')";

        $resultado = mysqli_query($conexion, $sql_insert);

        if ($resultado) {
            $nuevo_id = mysqli_insert_id($conexion);

            $sql_nueva = "SELECT id, titulo, album, descripcion, imagen
                FROM canciones  
                WHERE id = {$nuevo_id}";

            $res_nueva = mysqli_query($conexion, $sql_nueva);
            $cancion_nueva = mysqli_fetch_assoc($res_nueva);
            mysqli_free_result($res_nueva);

            responder_json([
                'exito'   => true,
                'mensaje' => 'La canción se creó correctamente.',
                'cancion' => $cancion_nueva
            ]);
        } else {
            responder_json([
                'exito'   => false,
                'mensaje' => 'Error al insertar la canción: ' . mysqli_error($conexion)
            ], 500);
        }
    }

    // 
    // EDITAR
    // 
    if ($accion === 'editar') {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $titulo = isset($_POST['titulo']) ? limpiar_cadena($_POST['titulo']) : '';
        $album = isset($_POST['album']) ? limpiar_cadena($_POST['album']) : '';
        $descripcion = isset($_POST['descripcion']) ? limpiar_cadena($_POST['descripcion']) : '';
        $imagen = isset($_POST['imagen']) ? limpiar_cadena($_POST['imagen']) : '';

        $errores = [];
        if ($id <= 0)
            $errores['id'] = 'ID inválido.';

        if (empty($titulo))
            $errores['titulo'] = 'El título es obligatorio.';

        if (empty($album))
            $errores['album'] = 'El álbum es obligatorio.';

        if (empty($descripcion))
            $errores['descripcion'] = 'La descripción es obligatoria.';

        if (empty($imagen))
            $errores['imagen'] = 'La URL de la imagen es obligatoria.';

        if (!empty($errores)) {
            responder_json([
                'exito'   => false,
                'mensaje' => 'Hay errores de validación.',
                'errores' => $errores
            ], 400);
        }

        $titulo_sql = limpiar_para_sql($conexion, $titulo);
        $album_sql = limpiar_para_sql($conexion, $album);
        $descripcion_sql = limpiar_para_sql($conexion, $descripcion);
        $imagen_sql = limpiar_para_sql($conexion, $imagen);

        $sql_update = "UPDATE canciones 
            SET titulo = '{$titulo_sql}', 
                album = '{$album_sql}', 
                descripcion = '{$descripcion_sql}', 
                imagen = '{$imagen_sql}'
            WHERE id = {$id}";

        $resultado = mysqli_query($conexion, $sql_update);

        if ($resultado) {
            responder_json([
                'exito'   => true,
                'mensaje' => 'La canción se actualizó correctamente.'
            ]);
        } else {
            responder_json([
                'exito'   => false,
                'mensaje' => 'Error al actualizar la canción: ' . mysqli_error($conexion)
            ], 500);
        }
    }

    // 
    // ELIMINAR
    // 
    if ($accion === 'eliminar') {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

        if ($id <= 0) {
            responder_json([
                'exito'   => false,
                'mensaje' => 'ID inválido.'
            ], 400);
        }

        $sql_delete = "DELETE FROM canciones WHERE id = {$id}";
        $resultado = mysqli_query($conexion, $sql_delete);

        if ($resultado) {
            responder_json([
                'exito'   => true,
                'mensaje' => 'La canción se eliminó correctamente.'
            ]);
        } else {
            responder_json([
                'exito'   => false,
                'mensaje' => 'Error al eliminar la canción: ' . mysqli_error($conexion)
            ], 500);
        }
    }

    // Accion no valida
    responder_json([
        'exito'   => false,
        'mensaje' => 'Acción no reconocida.'
    ], 400);
}

// Si llega aca, metodo no permitido
responder_json([
    'exito'   => false,
    'mensaje' => 'Método HTTP no permitido.'
], 405);
