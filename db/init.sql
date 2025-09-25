CREATE DATABASE impulsora;

  use impulsora;

CREATE TABLE empleados (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL,
    apellido_paterno VARCHAR(30) NOT NULL,
    apellido_materno VARCHAR(30) NOT NULL,
    correo VARCHAR(30) NOT NULL,
    departamento VARCHAR(50) NOT NULL,
    estatus CHAR(1) NOT NULL
);

CREATE TABLE equipos (
    numero_serie INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre_equipo VARCHAR(30) NOT NULL,
    tipo VARCHAR(30) NOT NULL,
    estatus CHAR(1) NOT NULL
);

CREATE TABLE equipo_empleados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_equipo INT UNSIGNED NOT NULL,
    id_empleado INT UNSIGNED NOT NULL,
    estatus CHAR(1) DEFAULT '1',
    FOREIGN KEY (id_equipo) REFERENCES equipos(numero_serie),
    FOREIGN KEY (id_empleado) REFERENCES empleados(id)
);


DELIMITER //

CREATE PROCEDURE sp_gestion_empleados(IN datos JSON)
BEGIN
    DECLARE v_opcion VARCHAR(50);
    DECLARE v_nombre, v_apellido_paterno, v_apellido_materno, v_correo, v_departamento VARCHAR(255);
    DECLARE v_id INT;
    
    SET v_opcion = JSON_UNQUOTE(JSON_EXTRACT(datos, '$.opcion'));

    IF v_opcion = 'crear' THEN
        SET v_nombre = JSON_UNQUOTE(JSON_EXTRACT(datos, '$.nombre'));
        SET v_apellido_paterno = JSON_UNQUOTE(JSON_EXTRACT(datos, '$.apellido_paterno'));
        SET v_apellido_materno = JSON_UNQUOTE(JSON_EXTRACT(datos, '$.apellido_materno'));
        SET v_correo = JSON_UNQUOTE(JSON_EXTRACT(datos, '$.correo'));
        SET v_departamento = JSON_UNQUOTE(JSON_EXTRACT(datos, '$.departamento'));

        INSERT INTO empleados (nombre, apellido_paterno, apellido_materno, correo, departamento, estatus)
        VALUES (v_nombre, v_apellido_paterno, v_apellido_materno, v_correo, v_departamento, '1');

        SELECT 'A' AS mensaje;

    ELSEIF v_opcion = 'editar' THEN
        SET v_nombre = JSON_UNQUOTE(JSON_EXTRACT(datos, '$.nombre'));
        SET v_apellido_paterno = JSON_UNQUOTE(JSON_EXTRACT(datos, '$.apellido_paterno'));
        SET v_apellido_materno = JSON_UNQUOTE(JSON_EXTRACT(datos, '$.apellido_materno'));
        SET v_correo = JSON_UNQUOTE(JSON_EXTRACT(datos, '$.correo'));
        SET v_departamento = JSON_UNQUOTE(JSON_EXTRACT(datos, '$.departamento'));
        SET v_id = JSON_EXTRACT(datos, '$.id');

        UPDATE empleados 
        SET nombre = v_nombre, 
            apellido_paterno = v_apellido_paterno, 
            apellido_materno = v_apellido_materno, 
            correo = v_correo, 
            departamento = v_departamento, 
            estatus = '1' 
        WHERE id = v_id;

        SELECT 'A' AS mensaje;

    ELSEIF v_opcion = 'eliminar' THEN
        SET v_id = JSON_EXTRACT(datos, '$.id');

        UPDATE empleados 
        SET estatus = '0' 
        WHERE id = v_id;

        SELECT 'A' AS mensaje;

    ELSEIF v_opcion = 'listar' THEN
        SELECT id, nombre, apellido_paterno, apellido_materno, correo, departamento
        FROM empleados
        WHERE estatus = '1';

    ELSEIF v_opcion = 'obtener' THEN
        SET v_id = JSON_EXTRACT(datos, '$.id');

        SELECT nombre, 
               apellido_paterno, 
               apellido_materno, 
               correo, 
               departamento, 
               id
        FROM empleados 
        WHERE id = v_id AND estatus = '1';

    ELSE
        SELECT 'B' AS mensaje;
    END IF;
END //

DELIMITER ;


DELIMITER //

CREATE PROCEDURE sp_gestion_equipos(IN datos JSON)
BEGIN
    DECLARE v_opcion VARCHAR(50);
    DECLARE v_nombre_equipo, v_tipo VARCHAR(255);
    DECLARE v_id, v_id_empleado INT;
    
    SET v_opcion = JSON_UNQUOTE(JSON_EXTRACT(datos, '$.opcion'));

    IF v_opcion = 'crear' THEN
        SET v_nombre_equipo = JSON_UNQUOTE(JSON_EXTRACT(datos, '$.nombre_equipo'));
        SET v_tipo = JSON_UNQUOTE(JSON_EXTRACT(datos, '$.tipo'));

        INSERT INTO equipos (nombre_equipo, tipo, estatus)
        VALUES (v_nombre_equipo, v_tipo, '1');

        SELECT 'A' AS mensaje;

    ELSEIF v_opcion = 'editar' THEN
        SET v_nombre_equipo = JSON_UNQUOTE(JSON_EXTRACT(datos, '$.nombre_equipo'));
        SET v_tipo = JSON_UNQUOTE(JSON_EXTRACT(datos, '$.tipo'));
        SET v_id = JSON_EXTRACT(datos, '$.id');

        UPDATE equipos 
        SET nombre_equipo = v_nombre_equipo, 
            tipo = v_tipo
        WHERE numero_serie = v_id AND estatus = '1';

        SELECT 'A' AS mensaje;

    ELSEIF v_opcion = 'eliminar' THEN
        SET v_id = JSON_EXTRACT(datos, '$.numero_serie');
        UPDATE equipos 
        SET estatus = '0' 
        WHERE numero_serie = v_id AND estatus = '1';
        SELECT 'A' AS mensaje;

    ELSEIF v_opcion = 'listar' THEN
        SELECT numero_serie AS id, nombre_equipo, tipo
        FROM equipos 
        WHERE estatus = '1';

    ELSEIF v_opcion = 'obtener' THEN
        SET v_id = JSON_EXTRACT(datos, '$.id');

        SELECT numero_serie AS id, nombre_equipo, tipo
        FROM equipos 
        WHERE numero_serie = v_id AND estatus = '1';

    ELSEIF v_opcion = 'listar_integrantes' THEN
        SET v_id = JSON_EXTRACT(datos, '$.id_equipo');

        SELECT e.id, CONCAT(e.nombre, ' ', e.apellido_paterno, ' ', e.apellido_materno) AS nombre_completo, e.departamento
        FROM equipo_empleados ee
        INNER JOIN empleados e ON ee.id_empleado = e.id
        WHERE ee.id_equipo = v_id AND e.estatus = '1' AND ee.estatus = '1';

    ELSEIF v_opcion = 'listar_integrantes' THEN
        SET v_id = JSON_EXTRACT(datos, '$.id_equipo');
        SELECT e.id, CONCAT(e.nombre, ' ', e.apellido_paterno, ' ', e.apellido_materno) AS nombre_completo, e.departamento
        FROM equipo_empleados ee
        INNER JOIN empleados e ON ee.id_empleado = e.id
        WHERE ee.id_equipo = v_id AND e.estatus = '1' AND ee.estatus = '1';

    ELSEIF v_opcion = 'agregar_integrante' THEN
        SET v_id = JSON_EXTRACT(datos, '$.id_equipo');
        SET v_id_empleado = JSON_EXTRACT(datos, '$.id_empleado');

        IF EXISTS (
            SELECT 1 FROM equipo_empleados
            WHERE id_equipo = v_id AND id_empleado = v_id_empleado
        ) THEN
            UPDATE equipo_empleados
            SET estatus = '1'
            WHERE id_equipo = v_id AND id_empleado = v_id_empleado;
        ELSE
            INSERT INTO equipo_empleados (id_equipo, id_empleado, estatus)
            VALUES (v_id, v_id_empleado, '1');
        END IF;
        SELECT 'A' AS mensaje;

    ELSEIF v_opcion = 'eliminar_integrante' THEN
        SET v_id = JSON_EXTRACT(datos, '$.id_equipo');
        SET v_id_empleado = JSON_EXTRACT(datos, '$.id_empleado');
        UPDATE equipo_empleados
        SET estatus = '0'
        WHERE id_equipo = v_id AND id_empleado = v_id_empleado;
        SELECT 'A' AS mensaje;

    END IF;
END //

DELIMITER ;