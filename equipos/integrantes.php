 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluacion Web</title>
    <link rel="stylesheet" href="../assets/libs/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/maincss.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<?php include '../assets/header.php'?> 
<main>
    <div class="container mt-5">
        <h1 class="text-center">Asignar Integrantes al Equipo</h1>
        <?php
        include '../db/conexion.php';

        $id_equipo = isset($_GET['id']) ? intval($_GET['id']) : null;
        $mensaje = '';

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'guardar') {
            $id_empleados_seleccionados = isset($_POST['empleados']) ? $_POST['empleados'] : [];
            try {
                $datos = ['opcion' => 'listar_integrantes', 'id_equipo' => $id_equipo];
                $json_datos = json_encode($datos);
                $sql = "CALL sp_gestion_equipos(:json_datos)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':json_datos', $json_datos, PDO::PARAM_STR);
                $stmt->execute();
                $integrantes_actuales = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt->closeCursor();

                $ids_actuales = array_column($integrantes_actuales, 'id');

                foreach ($ids_actuales as $id_empleado) {
                    if (!in_array($id_empleado, $id_empleados_seleccionados)) {
                        $datos_eliminar = ['opcion' => 'eliminar_integrante', 'id_equipo' => $id_equipo, 'id_empleado' => $id_empleado];
                        $json_datos_eliminar = json_encode($datos_eliminar);
                        $stmt = $conn->prepare("CALL sp_gestion_equipos(:json_datos)");
                        $stmt->bindParam(':json_datos', $json_datos_eliminar, PDO::PARAM_STR);
                        $stmt->execute();
                        $stmt->closeCursor();
                    }
                }

                foreach ($id_empleados_seleccionados as $id_empleado) {
                    if (!in_array($id_empleado, $ids_actuales)) {
                        $datos_agregar = ['opcion' => 'agregar_integrante', 'id_equipo' => $id_equipo, 'id_empleado' => intval($id_empleado)];
                        $json_datos_agregar = json_encode($datos_agregar);
                        $stmt = $conn->prepare("CALL sp_gestion_equipos(:json_datos)");
                        $stmt->bindParam(':json_datos', $json_datos_agregar, PDO::PARAM_STR);
                        $stmt->execute();
                        $stmt->closeCursor();
                    }
                }

                header("Location: editar.php?id=$id_equipo");
                exit();
            } catch (PDOException $e) {
                $mensaje = 'Error al guardar cambios: ' . $e->getMessage();
            }
        }

        $empleados_disponibles = [];
        try {
            $datos = ['opcion' => 'listar'];
            $json_datos = json_encode($datos);
            $sql = "CALL sp_gestion_empleados(:json_datos)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':json_datos', $json_datos, PDO::PARAM_STR);
            $stmt->execute();
            $empleados_disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
        } catch (PDOException $e) {
            $mensaje = 'Error al cargar empleados: ' . $e->getMessage();
        }

        $integrantes_actuales = [];
        if ($id_equipo) {
            try {
                $datos = ['opcion' => 'listar_integrantes', 'id_equipo' => $id_equipo];
                $json_datos = json_encode($datos);
                $sql = "CALL sp_gestion_equipos(:json_datos)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':json_datos', $json_datos, PDO::PARAM_STR);
                $stmt->execute();
                $integrantes_actuales = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt->closeCursor();
            } catch (PDOException $e) {
                $mensaje = 'Error al cargar integrantes: ' . $e->getMessage();
            }
        }
        ?>
        <?php if ($mensaje): ?>
            <div class="alert alert-danger"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="hidden" name="action" value="guardar">
            <div class="table-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Departamento</th>
                            <th>Seleccionar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($empleados_disponibles as $empleado): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($empleado['nombre'] . ' ' . $empleado['apellido_paterno'] . ' ' . $empleado['apellido_materno']); ?></td>
                                <td><?php echo htmlspecialchars($empleado['departamento']); ?></td>
                                <td><input type="checkbox" name="empleados[]" value="<?php echo $empleado['id']; ?>" <?php echo in_array($empleado['id'], array_column($integrantes_actuales, 'id')) ? 'checked' : ''; ?>></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary btn-custom">Guardar</button>
                <a href="editar.php?id=<?php echo $id_equipo; ?>" class="btn btn-secondary btn-custom">Cancelar</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
</main>
 <?php include '../assets/footer.php'?> 