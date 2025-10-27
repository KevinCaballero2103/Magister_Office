<?php
// Calcular rutas dinámicas
$levels = substr_count(dirname($_SERVER['SCRIPT_NAME']), '/') - 1;
$prefix = str_repeat('../', $levels);

// Detectar página actual para indicador
$currentPage = basename($_SERVER['SCRIPT_NAME']);
$currentDir = basename(dirname($_SERVER['SCRIPT_NAME']));

function getCurrentModule($currentDir, $currentPage) {
    if ($currentPage === 'index.php') return 'Inicio';
    if ($currentDir === 'proveedores') return 'Proveedores';
    if ($currentDir === 'clientes') return 'Clientes';
    if ($currentDir === 'productos') return 'Productos';
    if ($currentDir === 'servicios') return 'Servicios';
    if ($currentDir === 'compras') return 'Compras';
    if ($currentDir === 'ventas') return 'Ventas';
    if ($currentDir === 'caja') return 'Caja';
    if ($currentDir === 'auditoria') return 'Auditoría';
    return 'Sistema';
}

$currentModule = getCurrentModule($currentDir, $currentPage);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo $prefix; ?>css/sidebar-menu.css">
</head>
<body>
    <div class="main-container">
        <!-- Botón menú móvil -->
        <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">☰</button>
        
        <!-- Barra lateral -->
        <nav class="sidebar" id="sidebar">
            <!-- Logo -->
            <div class="logo-container">
                <img src="<?php echo $prefix; ?>img/logo.png" alt="Logo">
                <span class="logo-text">Magister Office</span>
            </div>

            <!-- Indicador página actual -->
            <div class="current-page">
                <div class="current-page-text">Estás en:</div>
                <div class="current-page-title"><?php echo $currentModule; ?></div>
            </div>

            <!-- Menú de navegación -->
            <div class="nav-menu">
                <!-- Inicio -->
                <div class="nav-item">
                    <a href="<?php echo $prefix; ?>" class="nav-link">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2L3 9v11h4v-6h6v6h4V9l-7-7z"/>
                        </svg>
                        <span class="nav-text">Inicio</span>
                    </a>
                </div>

                <!-- Proveedores -->
                <div class="nav-item">
                    <div class="nav-link" onclick="toggleSubmenu(this)">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 21 21">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="nav-text">Proveedores</span>
                        <svg class="nav-arrow" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 12l-4-4h8l-4 4z"/>
                        </svg>
                    </div>
                    <div class="submenu">
                        <a href="<?php echo $prefix; ?>proveedores/frm_guardar_proveedor.php" class="submenu-item">Registrar</a>
                        <a href="<?php echo $prefix; ?>proveedores/listado_proveedor.php" class="submenu-item">Listar</a>
                    </div>
                </div>

                <!-- Clientes -->
                <div class="nav-item">
                    <div class="nav-link" onclick="toggleSubmenu(this)">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                        </svg>
                        <span class="nav-text">Clientes</span>
                        <svg class="nav-arrow" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 12l-4-4h8l-4 4z"/>
                        </svg>
                    </div>
                    <div class="submenu">
                        <a href="<?php echo $prefix; ?>clientes/frm_guardar_cliente.php" class="submenu-item">Registrar</a>
                        <a href="<?php echo $prefix; ?>clientes/listado_cliente.php" class="submenu-item">Listar</a>
                    </div>
                </div>

                <!-- Productos -->
                <div class="nav-item">
                    <div class="nav-link" onclick="toggleSubmenu(this)">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                        </svg>
                        <span class="nav-text">Productos</span>
                        <svg class="nav-arrow" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 12l-4-4h8l-4 4z"/>
                        </svg>
                    </div>
                    <div class="submenu">
                        <a href="<?php echo $prefix; ?>productos/frm_guardar_producto.php" class="submenu-item">Registrar</a>
                        <a href="<?php echo $prefix; ?>productos/listado_producto.php" class="submenu-item">Listar</a>
                    </div>
                </div>

                
                <!-- Servicios -->
                <div class="nav-item">
                    <div class="nav-link" onclick="toggleSubmenu(this)">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                        </svg>
                        <span class="nav-text">Servicios</span>
                        <svg class="nav-arrow" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 12l-4-4h8l-4 4z"/>
                        </svg>
                    </div>
                    <div class="submenu">
                        <a href="<?php echo $prefix; ?>servicios/frm_guardar_servicio.php" class="submenu-item">Registrar</a>
                        <a href="<?php echo $prefix; ?>servicios/listado_servicio.php" class="submenu-item">Listar</a>
                    </div>
                </div>

                <!-- COMPRAS (NUEVO) -->
                <div class="nav-item">
                    <div class="nav-link" onclick="toggleSubmenu(this)">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                        </svg>
                        <span class="nav-text">Compras</span>
                        <svg class="nav-arrow" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 12l-4-4h8l-4 4z"/>
                        </svg>
                    </div>
                    <div class="submenu">
                        <a href="<?php echo $prefix; ?>compras/frm_registrar_compra.php" class="submenu-item">Registrar Compra</a>
                        <a href="<?php echo $prefix; ?>compras/listado_compras.php" class="submenu-item">Historial</a>
                    </div>
                </div>

                <!-- VENTAS (NUEVO) -->
                <div class="nav-item">
                    <div class="nav-link" onclick="toggleSubmenu(this)">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                        </svg>
                        <span class="nav-text">Ventas</span>
                        <svg class="nav-arrow" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 12l-4-4h8l-4 4z"/>
                        </svg>
                    </div>
                    <div class="submenu">
                        <a href="<?php echo $prefix; ?>ventas/frm_registrar_venta.php" class="submenu-item">Registrar Venta</a>
                        <a href="<?php echo $prefix; ?>ventas/listado_ventas.php" class="submenu-item">Historial</a>
                    </div>
                </div>

                <!-- Caja (ACTUALIZADO) -->
                <div class="nav-item">
                    <div class="nav-link" onclick="toggleSubmenu(this)">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zM14 6a2 2 0 012 2v8a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h8zM6 8a2 2 0 012-2h4a2 2 0 012 2v8a2 2 0 01-2 2H8a2 2 0 01-2-2V8z"/>
                        </svg>
                        <span class="nav-text">Caja</span>
                        <svg class="nav-arrow" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 12l-4-4h8l-4 4z"/>
                        </svg>
                    </div>
                    <div class="submenu">
                        <a href="<?php echo $prefix; ?>caja/abrir_caja.php" class="submenu-item">Abrir Caja</a>
                        <a href="<?php echo $prefix; ?>caja/cerrar_caja.php" class="submenu-item">Cerrar Caja</a>
                        <a href="<?php echo $prefix; ?>caja/balance.php" class="submenu-item">Balance General</a>
                        <a href="<?php echo $prefix; ?>caja/registrar_movimiento.php" class="submenu-item">Registrar Movimiento</a>
                        <a href="<?php echo $prefix; ?>caja/historial_movimientos.php" class="submenu-item">Historial</a>
                    </div>
                </div>


                <!-- Auditoría -->
                <div class="nav-item">
                    <div class="nav-link" onclick="toggleSubmenu(this)">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm7 5a1 1 0 00-1-1H8a1 1 0 00-1 1v2a1 1 0 001 1h2a1 1 0 001-1v-2z" clip-rule="evenodd"/>
                        </svg>
                        <span class="nav-text">Auditoría</span>
                        <svg class="nav-arrow" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 12l-4-4h8l-4 4z"/>
                        </svg>
                    </div>
                    <div class="submenu">
                        <a href="<?php echo $prefix; ?>auditoria/log_actividades.php" class="submenu-item">Log de Actividades</a>
                        <a href="<?php echo $prefix; ?>auditoria/historial_stock.php" class="submenu-item">Historial de Stock</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Contenido principal -->
        <main class="main-content" id="main-content">
            <!-- Aquí va el contenido de cada página -->
        </main>
    </div>

    <script>
        function toggleSubmenu(element) {
            // Cerrar otros submenús
            const allItems = document.querySelectorAll('.nav-item');
            allItems.forEach(item => {
                if (item !== element.parentElement) {
                    item.classList.remove('active');
                }
            });
            
            // Toggle del submenú actual
            element.parentElement.classList.toggle('active');
        }

        function toggleMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('mobile-active');
        }

        // Cerrar menú móvil al hacer clic fuera
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.mobile-menu-toggle');
            
            if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                sidebar.classList.remove('mobile-active');
            }
        });
    </script>
</body>
</html>