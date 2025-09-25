<?php
include '../db/conexion.php';

if (isset($_GET['numero_serie'])) {
    $numero_serie = intval($_GET['numero_serie']);
    try {
        $datos = ['opcion' => 'eliminar', 'numero_serie' => $numero_serie];
        $json_datos = json_encode($datos);
        error_log("JSON enviado a SP: " . $json_datos);
        $sql = "CALL sp_gestion_equipos(:json_datos)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':json_datos', $json_datos, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($result && $result['mensaje'] === 'A') {
            header("Location: listar.php");
            exit();
        } else {
            throw new PDOException('El SP no devolvió "A" como esperado');
        }
    } catch (PDOException $e) {
        error_log("Error en eliminar.php: " . $e->getMessage());
        ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo eliminar el equipo: <?php echo addslashes($e->getMessage()); ?>',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.location.href = 'listar.php';
            });
        </script>
        <?php
        exit();
    }
} else {
    header("Location: listar.php");
    exit();
}
?>