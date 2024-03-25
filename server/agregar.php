<?php
//validación de datos
function validator ($dni, $apellido) {
    if (empty($dni) || empty($apellido)) {
        echo json_encode(
            [
                'status' => 'error',
                'message' => 'debe completar todos los campos'
            ]
        );
        return false;
    } else if (strlen($dni) > 8) {
        echo json_encode(
            [
                'status' => 'error',
                'message' => 'el DNI no debe superar los 8 dígitos'
            ]
        );
        return false;
    } else if (!is_numeric($dni)) {
        echo json_encode(
            [
                'status' => 'error',
                'message' => 'el DNI debe contener sólo dígitos numéricos'
            ]
        );
        return false;
    } else {
        return true;
    }
}

//main function
function store ($dni, $apellido) {
    // Ruta de la base de datos csv
    $route_db = 'db/usuarios.csv';

    if (($db = fopen($route_db, 'a+')) !== false) {
        $found = false;
        // Recorrer el archivo usuarios.csv
        while (($fila = fgetcsv($db, 100, ';')) !== false) {
            foreach($fila as $dato) {
                if ($dato === $dni) {
                    $found = true;
                }
            }
        }
        
        // Si el DNI existe en usuarios.csv devolver mensaje a JS
        if ($found === true) {
            http_response_code(400);
            echo json_encode(
                [
                    'status' => 'error',
                    'message' => 'Usuario ya existente'
                ] 
            );
        } else {
            // Si el DNI NO existe en usuarios.csv agregarlo, con el apelillido, separado por ";" 
            // y devolver respuesta a JS
            fwrite($db,  "\n" . $dni . ";" . $apellido);
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode(
                [
                    'status' => 'succes',
                    'message' => 'Usuario creado exitosamente'
                ]
            ); 
        }

        fclose($db);
    } else {
        echo json_encode(
            [
                'status' => 'error',
                'message' => 'error al leer la base de datos'
            ]
        );
    }
}

// TOMAR LOS DATOS QUE ENVIASTE DESDE index.html
$dataFromBody = file_get_contents('php://input');

// Convierto los datos JSON en un array asociativo y los guardo en la variable $data.
$data = json_decode($dataFromBody, true);
$dni = $data['dni'];
$apellido = $data['apellido'];
$data_validated = validator($dni, $apellido);

if ($data_validated === true) {
    store($dni, $apellido);
}