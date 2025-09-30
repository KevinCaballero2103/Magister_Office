<?php
// Procesamiento de datos al inicio
include_once "../db.php";

$estado = isset($_GET['estado']) ? $_GET['estado'] : "99";
$tipo_busqueda = isset($_GET['tipo_busqueda']) ? $_GET['tipo_busqueda'] : "todos";
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : "";

// Construir condiciones WHERE
$condiciones = array();

// Filtro por estado
if ($estado !== "99") {
    $condiciones[] = "c.estado_cliente = " . intval($estado);
}

// Filtro por búsqueda
if (!empty($busqueda) && $tipo_busqueda !== "todos") {
    switch ($tipo_busqueda) {
        case "nombre":
            $condiciones[] = "c.nombre_cliente LIKE '%" . $busqueda . "%'";
            break;
        case "apellido":
            $condiciones[] = "c.apellido_cliente LIKE '%" . $busqueda . "%'";
            break;
        case "ci_ruc":
            $condiciones[] = "c.ci_ruc_cliente LIKE '%" . $busqueda . "%'";
            break;
        case "telefono":
            $condiciones[] = "c.telefono_cliente LIKE '%" . $busqueda . "%'";
            break;
        case "correo":
            $condiciones[] = "c.correo_cliente LIKE '%" . $busqueda . "%'";
            break;
    }
}

// Construir WHERE clause
$where_clause = "";
if (!empty($condiciones)) {
    $where_clause = "WHERE " . implode(" AND ", $condiciones);
}

$sentencia = $conexion->prepare("
    SELECT c.* 
    FROM clientes c
    $where_clause
    ORDER BY c.nombre_cliente ASC
");

$sentencia->execute();
$clientes = $sentencia->fetchAll(PDO::FETCH_OBJ);

// Convertir a JSON para JavaScript
$clientesJSON = json_encode($clientes);
$estadoActual = isset($_GET['estado']) ? $_GET['estado'] : '99';
$tipoBusquedaActual = isset($_GET['tipo_busqueda']) ? $_GET['tipo_busqueda'] : 'todos';
$busquedaActual = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Clientes</title>
    <link href="../css/bulma.min.css" rel="stylesheet">
    <style>
        /* Override del fondo principal */
        .main-content {
            background: #2c3e50 !important;
            color: white;
        }

        /* Container principal */
        .list-container {
            background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            margin: 20px auto;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Título */
        .list-title {
            color: #f1c40f;
            font-size: 2.5rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        /* Container de filtros */
        .filter-container {
            background: rgba(0,0,0,0.2);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            border: 1px solid rgba(241, 196, 15, 0.2);
        }

        .filter-container label {
            color: #f1c40f !important;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 10px;
            display: block;
        }

        .select select {
            background: rgba(236, 240, 241, 0.1) !important;
            border: 2px solid rgba(241, 196, 15, 0.3) !important;
            color: white !important;
            font-size: 1rem;
            padding: 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .select select:focus {
            background: rgba(236, 240, 241, 0.15) !important;
            border-color: #f1c40f !important;
            box-shadow: 0 0 0 0.125em rgba(241, 196, 15, 0.25) !important;
        }

        /* Botón de búsqueda */
        .button {
            background: linear-gradient(45deg, #f39c12, #f1c40f) !important;
            border: none !important;
            color: #2c3e50 !important;
            font-weight: bold !important;
            border-radius: 8px !important;
            transition: all 0.3s ease !important;
            padding: 10px 20px !important;
            font-size: 1rem !important;
        }

        .button:hover {
            background: linear-gradient(45deg, #e67e22, #f39c12) !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(243, 156, 18, 0.4) !important;
            color: #2c3e50 !important;
        }

        /* Tabla personalizada */
        .custom-table {
            background: rgba(0,0,0,0.3) !important;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }

        .custom-table thead {
            background: linear-gradient(45deg, #f39c12, #f1c40f) !important;
        }

        .custom-table thead th {
            color: #2c3e50 !important;
            font-weight: bold !important;
            border: none !important;
            padding: 15px 10px !important;
            text-align: center;
            font-size: 0.9rem;
        }

        .custom-table tbody tr {
            background: rgba(255,255,255,0.05) !important;
            transition: all 0.3s ease;
        }

        .custom-table tbody tr:nth-child(even) {
            background: rgba(255,255,255,0.08) !important;
        }

        .custom-table tbody tr:hover {
            background: rgba(241, 196, 15, 0.15) !important;
            transform: scale(1.01);
            box-shadow: 0 3px 10px rgba(241, 196, 15, 0.2);
        }

        .custom-table tbody td {
            color: #ecf0f1 !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            padding: 12px 8px !important;
            text-align: center;
            font-size: 0.9rem;
            word-wrap: break-word;
        }

        /* Enlaces de editar */
        .edit-link {
            background: linear-gradient(45deg, #3498db, #2980b9) !important;
            color: white !important;
            font-weight: bold;
            text-decoration: none !important;
            padding: 6px 12px;
            border-radius: 6px;
            transition: all 0.3s ease;
            display: inline-block;
            font-size: 0.8rem;
        }

        .edit-link:hover {
            background: linear-gradient(45deg, #2980b9, #3498db) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
            color: white !important;
        }

        /* Estado badges */
        .status-active {
            background: linear-gradient(45deg, #27ae60, #2ecc71);
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .status-inactive {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: bold;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Buscador mejorado */
        .search-controls {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }

        .search-field {
            display: flex;
            flex-direction: column;
        }

        .search-field label {
            color: #f1c40f !important;
            font-weight: 600;
            margin-bottom: 8px !important;
            font-size: 0.9rem;
            text-align: left;
        }

        .search-input {
            background: rgba(236, 240, 241, 0.1) !important;
            border: 2px solid rgba(241, 196, 15, 0.3) !important;
            color: white !important;
            padding: 10px !important;
            border-radius: 8px !important;
            font-size: 1rem !important;
            transition: all 0.3s ease;
            min-width: 150px;
        }

        .search-input:focus {
            background: rgba(236, 240, 241, 0.15) !important;
            border-color: #f1c40f !important;
            box-shadow: 0 0 0 0.125em rgba(241, 196, 15, 0.25) !important;
            outline: none;
        }

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.6) !important;
        }

        /* Corregir dropdown en modo oscuro */
        .select select {
            background: rgba(236, 240, 241, 0.1) !important;
            border: 2px solid rgba(241, 196, 15, 0.3) !important;
            color: white !important;
            font-size: 1rem;
            padding: 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23f1c40f' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
            background-position: right 10px center !important;
            background-repeat: no-repeat !important;
            background-size: 16px !important;
            padding-right: 40px !important;
        }

        .select select option {
            background: #2c3e50 !important;
            color: white !important;
            padding: 8px !important;
        }

        .select select:focus {
            background: rgba(236, 240, 241, 0.15) !important;
            border-color: #f1c40f !important;
            box-shadow: 0 0 0 0.125em rgba(241, 196, 15, 0.25) !important;
        }

        /* Mensaje sin resultados */
        .no-results {
            text-align: center;
            color: #f1c40f !important;
            font-size: 1.2rem;
            font-weight: bold;
            padding: 30px;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .custom-table {
                font-size: 0.8rem;
            }
            
            .custom-table th,
            .custom-table td {
                padding: 8px 4px !important;
            }
        }

        @media (max-width: 768px) {
            .list-container {
                margin: 10px;
                padding: 20px;
            }
            
            .list-title {
                font-size: 2rem;
            }
            
            .filter-container {
                padding: 15px;
            }
            
            .custom-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>
    <?php include '../menu.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.querySelector('.main-content');
            const clientes = <?php echo $clientesJSON; ?>;
            const estadoActual = '<?php echo $estadoActual; ?>';
            
            let clientesHTML = '';
            if (clientes && clientes.length > 0) {
                clientes.forEach(cliente => {
                    const estado = cliente.estado_cliente == 1 
                        ? '<span class="status-active">ACTIVO</span>' 
                        : '<span class="status-inactive">INACTIVO</span>';
                    
                    clientesHTML += `
                        <tr>
                            <td><strong>${cliente.id}</strong></td>
                            <td>${cliente.nombre_cliente || '-'}</td>
                            <td>${cliente.apellido_cliente || '-'}</td>
                            <td>${cliente.ci_ruc_cliente || '-'}</td>
                            <td>${cliente.telefono_cliente || '-'}</td>
                            <td>${cliente.correo_cliente || '-'}</td>
                            <td>${cliente.direccion_cliente || '-'}</td>
                            <td>${estado}</td>
                            <td>
                                <a href='frm_editar_cliente.php?id=${cliente.id}' class='edit-link'>
                                    EDITAR
                                </a>
                            </td>
                        </tr>
                    `;
                });
            } else {
                clientesHTML = `
                    <tr>
                        <td colspan="9" class="no-results">
                            📋 No se encontraron clientes con los criterios seleccionados
                        </td>
                    </tr>
                `;
            }

            const contentHTML = `
                <div class='list-container'>
                    <h1 class='list-title'>📋 Listado de Clientes</h1>
                    
                    <div class='filter-container'>
                        <form method='GET' action=''>
                            <label class='label'>🔍 Buscar Clientes</label>
                            <div class='search-controls'>
                                <div class='search-field' style='flex: 0 0 200px;'>
                                    <label>Buscar por:</label>
                                    <div class='select'>
                                        <select name='tipo_busqueda' class='search-input'>
                                            <option value='todos'>-- TODOS --</option>
                                            <option value='nombre'>Nombre</option>
                                            <option value='apellido'>Apellido</option>
                                            <option value='ci_ruc'>CI / RUC</option>
                                            <option value='telefono'>Teléfono</option>
                                            <option value='correo'>Correo</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class='search-field'>
                                    <label>Término de búsqueda:</label>
                                    <input type='text' name='busqueda' class='search-input' placeholder='Escribe aquí para buscar...'>
                                </div>
                                
                                <div class='search-field' style='flex: 0 0 150px;'>
                                    <label>Estado:</label>
                                    <div class='select'>
                                        <select name='estado' class='search-input'>
                                            <option value='99' ${estadoActual == '99' ? 'selected' : ''}> -- TODOS --</option>
                                            <option value='1' ${estadoActual == '1' ? 'selected' : ''}>ACTIVO</option>
                                            <option value='0' ${estadoActual == '0' ? 'selected' : ''}>INACTIVO</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class='search-field' style='flex: 0 0 auto;'>
                                    <button type='submit' class='button' style='margin-top: 20px; padding: 12px 20px;'>
                                        🔎 Buscar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div style='overflow-x: auto;'>
                        <table class='table is-fullwidth custom-table'>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>NOMBRE</th>
                                    <th>APELLIDO</th>
                                    <th>CI / RUC</th>
                                    <th>TELÉFONO</th>
                                    <th>CORREO</th>
                                    <th>DIRECCIÓN</th>
                                    <th>ESTADO</th>
                                    <th>ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${clientesHTML}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
            
            mainContent.innerHTML = contentHTML;
        });
    </script>
</body>
</html>