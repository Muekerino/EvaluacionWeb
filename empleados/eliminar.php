<?php
include '../db/conexion.php';

if (isset($_GET['id']) && isset($_GET['nombre']) && isset($_GET['departamento'])) {
    $id = intval($_GET['id']);
    $nombre = $_GET['nombre'];
    $departamento = $_GET['departamento'];

    try {
        $datos = [
            'opcion' => 'eliminar',
            'id' => $id
        ];
        $json_datos = json_encode($datos);

        $sql = "CALL sp_gestion_empleados(:json_datos)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':json_datos', $json_datos, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            ?>
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Eliminando...</title>
                <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.min.css" rel="stylesheet">
            </head>
            <body>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'El empleado <?php echo addslashes($nombre); ?> del departamento <?php echo addslashes($departamento); ?> ha sido eliminado correctamente',
                        confirmButtonText: 'Aceptar',
                        timer: 3000,
                        timerProgressBar: true
                    }).then(() => {
                        window.location.href = 'listar.php';
                    });
                </script>
            </body>
            </html>
            <?php
            exit();
        } else {
            throw new PDOException('No se encontró el empleado o no se pudo actualizar el estatus');
        }
    } catch (PDOException $e) {
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Error</title>
            <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.min.css" rel="stylesheet">
        </head>
        <body>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al eliminar empleado: <?php echo addslashes($e->getMessage()); ?>',
                    confirmButtonText: 'Aceptar',
                    timer: 5000,
                    timerProgressBar: true
                }).then(() => {
                    window.location.href = 'listar.php';
                });
            </script>
        </body>
        </html>
        <?php
        exit();
    }
} else {
    header("Location: listar.php");
    exit();
}

$conn = null;
?>