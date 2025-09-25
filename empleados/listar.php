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
<body>
   <div class="container mt-5">
        <h1 class="text-center">Lista de Empleados</h1>
        <h2 class="text-center">Empleados</h2>
        <div class="mb-3">
            <input type="text" id="busqueda" class="form-control" placeholder="Buscar por nombre, correo o departamento...">
        </div>
        <div class="mb-3 d-flex justify-content-between flex-wrap">
            <button onclick="window.location.href='crear.php'" class="btn btn-primary mb-2 me-2">Registrar Empleado</button>
            <button onclick="window.location.href='../index.php'" class="btn btn-secondary mb-2">Regresar al Menu</button>
        </div>
        <div class="table-responsive">
            <table id="tablaEmpleados" class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Departamento</th>
                        <th>Opcion</th>
                    </tr>
                </thead>
                <tbody id="tablaBody"></tbody>
            </table>
            <div id="paginacion" class="mt-3 d-flex justify-content-center gap-2"></div>
        </div>
    </div>

    <?php
    include '../db/conexion.php';

    try {
        $datos = ['opcion' => 'listar'];
        $json_datos = json_encode($datos);

        $sql = "CALL sp_gestion_empleados(:json_datos)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':json_datos', $json_datos, PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<p class='text-center text-danger'>Error al cargar datos: " . $e->getMessage() . "</p>";
    }

    $conn = null;
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.4/dist/sweetalert2.all.min.js"></script>
    <script>
        const empleados = <?php echo json_encode($result); ?>;
        const filasPorPagina = 10;
        let paginaActual = 1;

        function mostrarPagina(pagina) {
            const tablaBody = document.getElementById('tablaBody');
            tablaBody.innerHTML = '';

            const inicio = (pagina - 1) * filasPorPagina;
            const fin = inicio + filasPorPagina;
            const datosPagina = empleados.slice(inicio, fin);

            datosPagina.forEach(empleado => {
                const nombreCompleto = `${empleado.nombre} ${empleado.apellido_paterno} ${empleado.apellido_materno}`;
                const fila = document.createElement('tr');
                fila.innerHTML = `
                    <td>${nombreCompleto}</td>
                    <td>${empleado.correo}</td>
                    <td>${empleado.departamento}</td>
                    <td>
                        <a href="editar.php?id=${empleado.id}" class="btn btn-warning btn-sm me-1">Editar</a>
                        <a href="#" class="btn btn-danger btn-sm eliminar-btn" data-id="${empleado.id}" data-nombre="${nombreCompleto}" data-departamento="${empleado.departamento}">Eliminar</a>
                    </td>
                `;
                tablaBody.appendChild(fila);
            });

            actualizarPaginacion();
        }

        function actualizarPaginacion() {
            const totalPaginas = Math.ceil(empleados.length / filasPorPagina);
            const paginacion = document.getElementById('paginacion');
            paginacion.innerHTML = '';

            const btnAnterior = document.createElement('button');
            btnAnterior.className = 'btn btn-secondary';
            btnAnterior.innerHTML = '&laquo; Anterior';
            btnAnterior.disabled = paginaActual === 1;
            btnAnterior.addEventListener('click', () => {
                if (paginaActual > 1) {
                    paginaActual--;
                    mostrarPagina(paginaActual);
                }
            });
            paginacion.appendChild(btnAnterior);

            for (let i = 1; i <= totalPaginas; i++) {
                const btnPagina = document.createElement('button');
                btnPagina.className = 'btn btn-outline-primary' + (i === paginaActual ? ' active' : '');
                btnPagina.innerText = i;
                btnPagina.addEventListener('click', () => {
                    paginaActual = i;
                    mostrarPagina(paginaActual);
                });
                paginacion.appendChild(btnPagina);
            }

            const btnSiguiente = document.createElement('button');
            btnSiguiente.className = 'btn btn-secondary';
            btnSiguiente.innerHTML = 'Siguiente &raquo;';
            btnSiguiente.disabled = paginaActual === totalPaginas;
            btnSiguiente.addEventListener('click', () => {
                if (paginaActual < totalPaginas) {
                    paginaActual++;
                    mostrarPagina(paginaActual);
                }
            });
            paginacion.appendChild(btnSiguiente);
        }

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('eliminar-btn')) {
                e.preventDefault();
                const id = e.target.getAttribute('data-id');
                const nombre = e.target.getAttribute('data-nombre');
                const departamento = e.target.getAttribute('data-departamento');
                Swal.fire({
                    icon: 'warning',
                    title: 'Confirmar Eliminación',
                    text: `El empleado ${nombre} del departamento ${departamento} va a ser eliminado. ¿Está seguro que desea continuar con la decisión?`,
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí',
                    cancelButtonText: 'No'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const url = `eliminar.php?id=${encodeURIComponent(id)}&nombre=${encodeURIComponent(nombre)}&departamento=${encodeURIComponent(departamento)}`;
                        window.location.href = url;
                    }
                });
            }
        });

        const inputBusqueda = document.getElementById('busqueda');
        inputBusqueda.addEventListener('keyup', function() {
            const filtro = inputBusqueda.value.toLowerCase();
            const tablaBody = document.getElementById('tablaBody');
            tablaBody.innerHTML = '';

            const datosFiltrados = empleados.filter(empleado => {
                const nombreCompleto = `${empleado.nombre} ${empleado.apellido_paterno} ${empleado.apellido_materno}`.toLowerCase();
                return nombreCompleto.includes(filtro) || empleado.correo.toLowerCase().includes(filtro) || empleado.departamento.toLowerCase().includes(filtro);
            });

            const inicio = (paginaActual - 1) * filasPorPagina;
            const fin = inicio + filasPorPagina;
            const datosPagina = datosFiltrados.slice(inicio, fin);

            datosPagina.forEach(empleado => {
                const nombreCompleto = `${empleado.nombre} ${empleado.apellido_paterno} ${empleado.apellido_materno}`;
                const fila = document.createElement('tr');
                fila.innerHTML = `
                    <td>${nombreCompleto}</td>
                    <td>${empleado.correo}</td>
                    <td>${empleado.departamento}</td>
                    <td>
                        <a href="editar.php?id=${empleado.id}" class="btn btn-warning btn-sm me-1">Editar</a>
                        <a href="#" class="btn btn-danger btn-sm eliminar-btn" data-id="${empleado.id}" data-nombre="${nombreCompleto}" data-departamento="${empleado.departamento}">Eliminar</a>
                    </td>
                `;
                tablaBody.appendChild(fila);
            });

            actualizarPaginacion(datosFiltrados);
        });

        mostrarPagina(paginaActual);
    </script>
 <?php include '../assets/footer.php'?> 