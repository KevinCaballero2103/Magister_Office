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
    if ($currentDir === 'caja') return 'Caja';
    if ($currentDir === 'facturar') return 'Facturar';
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
                        <a href="<?php echo $prefix; ?>productos/gestionar_stock.php" class="submenu-item">Gestionar Stock</a>
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


                <!-- Caja -->
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
                        <a href="<?php echo $prefix; ?>caja/ingresos.php" class="submenu-item">Ingresos</a>
                        <a href="<?php echo $prefix; ?>caja/egresos.php" class="submenu-item">Egresos</a>
                        <a href="<?php echo $prefix; ?>caja/balance.php" class="submenu-item">Balance</a>
                    </div>
                </div>

                <!-- Facturar -->
                <div class="nav-item">
                    <div class="nav-link" onclick="toggleSubmenu(this)">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"/>
                        </svg>
                        <span class="nav-text">Facturar</span>
                        <svg class="nav-arrow" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 12l-4-4h8l-4 4z"/>
                        </svg>
                    </div>
                    <div class="submenu">
                        <a href="<?php echo $prefix; ?>facturar/crear_factura.php" class="submenu-item">Crear Factura</a>
                        <a href="<?php echo $prefix; ?>facturar/historial_facturas.php" class="submenu-item">Historial</a>
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