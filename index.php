<?php
    include 'sistema/bd/bd.php'; 

    if(isset($_POST['acceder'])){
        $email = trim($_POST['username']);
        $password = trim($_POST['Password']);
        
    if(empty($email) || empty($password))  {
        $_SESSION['error'] = 'Completar todos los campos'; 
    }  else {
        try{
            $query = "SELECT u.id, u.username, u.email, u.password_hash, u.nombres, u.apellidos, u.almacenes_asignados, u.rol as rol_id, tr.descripcion as rol_descripcion, u.estado, u.ultimo_acceso 
            FROM usuario u
            INNER JOIN tipo_rol tr on u.rol = tr.id
            WHERE u.email = :email and u.estadao = '1'";
            $stmt = $conn->prepare($query); 
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $usurarios = $stmt->fetch(PDO::FETCH_ASSOC);

            if($usurarios){
                $password_valido = false;
                if (password_verify($password, $usurarios['password_hash'])) {
                    $password_valido = true;
                }
                // Opción 2: Para pruebas temporales según el rol
                elseif($password === 'admin123' && $usuario['rol_nombre'] === 'admin') {
                    $password_valido = true;
                }

                if($password_valido){
                    $query2 = "update usuario set ultimo_acceso = NOW() where id = :id";
                    $stmt2 = $conn->prepare($query2);
                    $stmt2->bindParam(':id', $usurarios['id']);
                    $stmt2->execute();

                 //Crear variables del sesionstart
                    $_SESSION['user_id'] = $usurarios['id'];
                    $_SESSION['username'] = $usurarios['username'];
                    $_SESSION['email'] = $usurarios['email'];
                    $_SESSION['nombres'] = $usurarios['nombres'];
                    $_SESSION['apellidos'] = $usurarios['apellidos'];
                    $_SESSION["nombre_completo"] = $usurarios['nombres'] . ' ' . $usurarios['apellidos'];

                    $_SESSION['rol_id'] = $usurarios['rol_id'];
                    $_SESSION['rol'] = $usurarios['rol_nombre'];
                    $_SESSION['rol_nombre'] = $usurarios['rol_nombre'];
                    $_SESSION['rol_descripcion'] = $usurarios['rol_descripcion'];

                    $_SESSION['almacenes_asignados'] = json_decode($usuario['almacenes_asignados'], true);
                    $_SESSION['login_time'] = date('Y-m-d H:i:s');

                    $query3 = "insert into logs (usuario_id, accion, descripcion, ip_address, user_agent) values (:usuario_id, 'LOGIN', CONCAT('Usuario inició sesión exitosamente - Rol: ', :rol_nombre), :ip, :user_agent)";
                    
                    $stmt3 = $conn->prepare($query3);
                    $stmt3 ->bindParam(':usuario_id', $usuario['id']);
                    $stmt3 ->bindParam(':rol_nombre', $usuario['rol_nombre']);
                    $stmt3 ->bindParam(':ip',$_SERVER['REMOTE_ADDR']);
                    $stmt3 ->bindParam(':user_agent',$_SERVER['HTTP_USER_AGENT']);
                    $stmt3 ->execute();
                }                


            }
            
                

        } catch (PDOException $e){
                $_SESSION['error'] = 'Error de conexión. Intente nuevamente.';
            error_log("Error de login: " . $e->getMessage());
        }  
    }

    }

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Etiqueta para mostrar un icono en la pestaña -->
    <link
        rel="icon"
        href=""
        type="image/x-icon" />

    <!-- Etiqueta para cambiarle el nombre a la pestaña -->
    <title></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        :root {
            --primary-color: #845642;
            --primary-hover: #444458;
            --bg-gradient: linear-gradient(135deg, #e3f2fd 0%, #f5fbff 100%);
            --card-shadow: 0 10px 30px rgba(0, 80, 130, 0.18);
            --input-bg: rgba(249, 251, 253, 0.9);
            --text-primary: #2d3a4a;
            --text-secondary: #5a6a7a;
            --border-radius: 12px;
            --transition-smooth: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background-image: url('fondo-hospital.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-blend-mode: overlay;
            background-color: rgba(235, 245, 255, 0.6);
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .card {
            width: 100%;
            border: none;
            border-radius: var(--border-radius);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            box-shadow: var(--card-shadow), 0 1px 5px rgba(0, 0, 0, 0.05);
            padding: 35px;
            transition: var(--transition-smooth);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 80, 130, 0.25);
        }

        .logo-container {
            position: relative;
            width: 140px;
            height: 140px;
            margin: 0 auto 20px;
            perspective: 1000px;
        }

        .logo {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 50%;
            border: 4px solid rgba(0, 119, 182, 0.8);
            padding: 4px;
            background-color: white;
            box-shadow: 0 8px 20px rgba(0, 119, 182, 0.25);
            transition: var(--transition-smooth);
        }

        .title {
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
            font-size: 1.7rem;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
        }

        .subtitle {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 25px;
            letter-spacing: 0.2px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.9rem;
            margin-bottom: 6px;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #d1e3f5;
            border-radius: 10px;
            font-size: 1rem;
            transition: var(--transition-smooth);
            background: var(--input-bg);
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
            color: var(--text-primary);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 119, 182, 0.15);
            background: white;
        }

        .btn-primary {
            width: 100%;
            padding: 12px 15px;
            border-radius: 10px;
            background: linear-gradient(135deg, #0077b6 0%, #005f92 100%);
            border: none;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.5px;
            margin-top: 10px;
            transition: var(--transition-smooth);
            box-shadow: 0 4px 15px rgba(0, 119, 182, 0.25);
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 119, 182, 0.35);
            background: linear-gradient(135deg, #0088cc 0%, #0077b6 100%);
        }

        .btn-primary:active {
            transform: translateY(1px);
            box-shadow: 0 2px 10px rgba(0, 119, 182, 0.2);
        }

        .btn-primary::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }

        .btn-primary:hover::after {
            left: 100%;
        }

        .footer-text {
            text-align: center;
            margin-top: 20px;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .footer-text a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition-smooth);
        }

        .footer-text a:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }

        .alert-danger {
            border-radius: 10px;
            border-left: 4px solid #dc3545;
            padding: 12px 15px;
            margin-bottom: 20px;
            background: rgba(220, 53, 69, 0.1);
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        @media (max-width: 480px) {
            .card {
                padding: 25px;
            }
            
            .logo-container {
                width: 110px;
                height: 110px;
            }
            
            .title {
                font-size: 1.5rem;
            }
            
            .form-control {
                padding: 10px 14px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="text-center">
                <div class="logo-container">
                    <img src="" alt="Logo" class="logo" id="logo">
                </div>
                <h4 class="title"></h4>
                <p class="subtitle"></p>
            </div>

            <!-- Mostrar error si existe -->
            <?php
            if(isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error:</strong> ' . $_SESSION['error'] . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>';
                unset($_SESSION['error']); // Limpiar el error después de mostrarlo
            }
            ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="username" class="form-label">Correo electrónico</label>
                    <input type="email" class="form-control" id="username" placeholder="ejemplo@correo.com" name="username" required>
                </div>
                <div class="form-group">
                    <label for="Password" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="Password" placeholder="Ingrese su contraseña" name="Password">
                </div>
                <button type="submit" class="btn btn-primary" name="acceder">Iniciar sesión</button>
            </form>
            <div class="footer-text">
                <span>¿Olvidaste tu contraseña? <a href="#">Recuperar</a></span>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Efecto 3D suave para el logo
        const logo = document.getElementById('logo');
        const logoContainer = document.querySelector('.logo-container');
        
        document.addEventListener('mousemove', (e) => {
            if (!logo) return;
            
            // Calcular la posición relativa del mouse
            const containerRect = logoContainer.getBoundingClientRect();
            const containerCenterX = containerRect.left + containerRect.width / 2;
            const containerCenterY = containerRect.top + containerRect.height / 2;
            
            // Calcular la distancia desde el centro (entre -1 y 1)
            const maxRotation = 10; // Grados máximos de rotación
            const maxMovement = 5; // Píxeles máximos de movimiento
            
            // Solo aplicar efecto si el mouse está cerca del logo
            const distanceX = e.clientX - containerCenterX;
            const distanceY = e.clientY - containerCenterY;
            const distance = Math.sqrt(distanceX * distanceX + distanceY * distanceY);
            
            if (distance < containerRect.width * 2) {
                const rotateY = maxRotation * (distanceX / containerRect.width);
                const rotateX = -maxRotation * (distanceY / containerRect.height);
                
                requestAnimationFrame(() => {
                    logo.style.transform = `
                        perspective(1000px)
                        rotateX(${rotateX}deg)
                        rotateY(${rotateY}deg)
                        scale(1.05)
                    `;
                });
            } else {
                // Regresar a posición normal cuando el mouse está lejos
                logo.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale(1)';
            }
        });
        
        // Regresar a estado normal cuando el mouse sale de la ventana
        document.addEventListener('mouseleave', () => {
            if (!logo) return;
            logo.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale(1)';
        });

                document.getElementById('username').focus();

        //Submit con Enter
        document.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                document.querySelector('form').submit();
            }
        });

        document.getElementById('username').focus();

        document.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                document.querySelector('form').submit();
            }
        });
    </script>
</body>
</html>