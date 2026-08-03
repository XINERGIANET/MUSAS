<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Impresión Remota</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
        }
        
        .container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        h1 {
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .status {
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            font-weight: bold;
        }
        
        .success {
            background: rgba(76, 175, 80, 0.3);
            border: 1px solid #4CAF50;
        }
        
        .error {
            background: rgba(244, 67, 54, 0.3);
            border: 1px solid #f44336;
        }
        
        .info {
            background: rgba(33, 150, 243, 0.3);
            border: 1px solid #2196F3;
        }
        
        .warning {
            background: rgba(255, 193, 7, 0.3);
            border: 1px solid #FFC107;
            color: #333;
        }
        
        .loading {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .config-section {
            margin: 20px 0;
            padding: 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .config-section h3 {
            margin-top: 0;
            color: #FFD700;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 16px;
        }
        
        input[type="text"]::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        button {
            background: linear-gradient(45deg, #FF6B6B, #4ECDC4);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            margin: 5px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        button:disabled {
            background: rgba(255, 255, 255, 0.2);
            cursor: not-allowed;
            transform: none;
        }
        
        .printer-list {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
        }
        
        .printer-item {
            padding: 10px;
            margin: 5px 0;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
            border-left: 4px solid #4ECDC4;
        }
        
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 16px;
        }
        
        select option {
            background: #333;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🖨️ Prueba de Impresión Remota</h1>
        
        <div class="config-section">
            <h3>⚙️ Configuración</h3>
            <label for="ipRemota">IP de la computadora con impresora:</label>
            <input type="text" id="ipRemota" value="192.168.100.52" placeholder="Ej: 192.168.1.100">
            <br><br>
            <button onclick="actualizarConfiguracion()">🔄 Actualizar IP</button>
            <button onclick="iniciarPruebas()">🚀 Ejecutar Pruebas</button>
        </div>
        
        <div id="resultados"></div>
        
        <div id="impresoras-section" style="display: none;">
            <div class="config-section">
                <h3>🖨️ Impresoras Disponibles</h3>
                <select id="impresora-select">
                    <option value="">Seleccionar impresora...</option>
                </select>
                <br><br>
                <button onclick="imprimirPrueba()">🖨️ Imprimir Prueba</button>
            </div>
        </div>
    </div>

    <script>
        class PruebaImpresionRemota {
            constructor() {
                this.URL_PLUGIN_POR_DEFECTO = "http://192.168.18.46:8000";
                this.resultadosDiv = document.getElementById('resultados');
                this.impresoras = [];
            }
            
            actualizarIP(nuevaIP) {
                this.URL_PLUGIN_POR_DEFECTO = `http://${nuevaIP}:8000`;
                this.log(`🔧 IP actualizada a: ${this.URL_PLUGIN_POR_DEFECTO}`, 'info');
            }
            
            log(mensaje, tipo = 'info') {
                const div = document.createElement('div');
                div.className = `status ${tipo}`;
                
                if (tipo === 'loading') {
                    div.innerHTML = `<div class="loading"><div class="spinner"></div>${mensaje}</div>`;
                } else {
                    const iconos = {
                        success: '✅',
                        error: '❌',
                        info: 'ℹ️',
                        warning: '⚠️'
                    };
                    div.innerHTML = `${iconos[tipo]} ${mensaje}`;
                }
                
                this.resultadosDiv.appendChild(div);
                this.resultadosDiv.scrollTop = this.resultadosDiv.scrollHeight;
            }
            
            async sleep(ms) {
                return new Promise(resolve => setTimeout(resolve, ms));
            }
            
            async probarConexionLocal() {
                this.log('Probando conexión al plugin local...', 'loading');
                try {
                    const respuesta = await fetch('http://localhost:8000/version', {
                        method: 'GET',
                        signal: AbortSignal.timeout(5000)
                    });
                    
                    if (respuesta.ok) {
                        const version = await respuesta.json();
                        this.log(`Plugin local conectado. Versión: ${version.version || 'N/A'}`, 'success');
                        return true;
                    } else {
                        this.log(`Plugin local respondió con error: ${respuesta.status}`, 'error');
                        return false;
                    }
                } catch (error) {
                    this.log(`No se pudo conectar al plugin local: ${error.message}`, 'error');
                    this.log('Asegúrate de que el plugin esté ejecutándose en localhost:8000', 'warning');
                    return false;
                }
            }
            
            async probarConexionRemota() {
                this.log('Probando conexión a la computadora remota...', 'loading');
                try {
                    const hostRemoto = `${this.URL_PLUGIN_POR_DEFECTO}/version`;
                    const respuesta = await fetch(`http://localhost:8000/reenviar?host=${hostRemoto}`, {
                        method: 'GET',
                        signal: AbortSignal.timeout(10000)
                    });
                    
                    if (respuesta.ok) {
                        const version = await respuesta.json();
                        this.log(`Computadora remota conectada. Versión: ${version.version || 'N/A'}`, 'success');
                        return true;
                    } else {
                        this.log(`Error conectando a computadora remota: ${respuesta.status}`, 'error');
                        return false;
                    }
                } catch (error) {
                    this.log(`No se pudo conectar a la computadora remota: ${error.message}`, 'error');
                    this.log('Verifica: IP correcta, firewall, plugin remoto activo', 'warning');
                    return false;
                }
            }
            
            async obtenerImpresoras() {
                this.log('Obteniendo lista de impresoras remotas...', 'loading');
                try {
                    const hostRemoto = `${this.URL_PLUGIN_POR_DEFECTO}/impresoras`;
                    const respuesta = await fetch(`http://localhost:8000/reenviar?host=${hostRemoto}`, {
                        method: 'GET',
                        signal: AbortSignal.timeout(10000)
                    });
                    
                    if (respuesta.ok) {
                        this.impresoras = await respuesta.json();
                        
                        if (this.impresoras.length > 0) {
                            this.log(`Se encontraron ${this.impresoras.length} impresoras:`, 'success');
                            
                            const select = document.getElementById('impresora-select');
                            select.innerHTML = '<option value="">Seleccionar impresora...</option>';
                            
                            this.impresoras.forEach(impresora => {
                                this.log(`  📍 ${impresora}`, 'info');
                                const option = document.createElement('option');
                                option.value = impresora;
                                option.textContent = impresora;
                                select.appendChild(option);
                            });
                            
                            document.getElementById('impresoras-section').style.display = 'block';
                            return true;
                        } else {
                            this.log('No se encontraron impresoras en la computadora remota', 'warning');
                            return false;
                        }
                    } else {
                        this.log(`Error obteniendo impresoras: ${respuesta.status}`, 'error');
                        return false;
                    }
                } catch (error) {
                    this.log(`Error obteniendo impresoras: ${error.message}`, 'error');
                    return false;
                }
            }
            
            async imprimirPrueba(nombreImpresora) {
                if (!nombreImpresora) {
                    this.log('Selecciona una impresora primero', 'warning');
                    return false;
                }
                
                this.log(`Enviando documento de prueba a: ${nombreImpresora}...`, 'loading');
                
                try {
                    const hostRemoto = `${this.URL_PLUGIN_POR_DEFECTO}/imprimir`;
                    const operaciones = [
                        {
                            nombre: "EscribirTexto",
                            argumentos: ["=== PRUEBA DE IMPRESION REMOTA ===\\n"]
                        },
                        {
                            nombre: "EscribirTexto", 
                            argumentos: [`Fecha: ${new Date().toLocaleString()}\\n`]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: [`IP Remota: ${this.URL_PLUGIN_POR_DEFECTO}\\n`]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: [`Impresora: ${nombreImpresora}\\n`]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: ["\\n¡Conexion exitosa!\\n"]
                        },
                        {
                            nombre: "EscribirTexto",
                            argumentos: ["================================\\n\\n"]
                        }
                    ];
                    
                    const payload = {
                        serial: "",
                        operaciones: operaciones,
                        nombreImpresora: nombreImpresora,
                    };
                    
                    const respuesta = await fetch(`http://localhost:8000/reenviar?host=${hostRemoto}`, {
                        method: "POST",
                        body: JSON.stringify(payload),
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        signal: AbortSignal.timeout(15000)
                    });
                    
                    const resultado = await respuesta.json();
                    
                    if (resultado.ok) {
                        this.log(`¡Documento enviado exitosamente a ${nombreImpresora}!`, 'success');
                        return true;
                    } else {
                        this.log(`Error al imprimir: ${resultado.message}`, 'error');
                        return false;
                    }
                } catch (error) {
                    this.log(`Error enviando documento: ${error.message}`, 'error');
                    return false;
                }
            }
            
            async ejecutarPruebasCompletas() {
                this.resultadosDiv.innerHTML = '';
                this.log('🚀 Iniciando pruebas de conectividad...', 'info');
                
                await this.sleep(500);
                
                // Paso 1: Probar plugin local
                const localOK = await this.probarConexionLocal();
                if (!localOK) {
                    this.log('❌ Pruebas detenidas. Soluciona el problema del plugin local primero.', 'error');
                    return;
                }
                
                await this.sleep(1000);
                
                // Paso 2: Probar conexión remota
                const remotoOK = await this.probarConexionRemota();
                if (!remotoOK) {
                    this.log('❌ Pruebas detenidas. No se puede conectar a la computadora remota.', 'error');
                    return;
                }
                
                await this.sleep(1000);
                
                // Paso 3: Obtener impresoras
                const impresorasOK = await this.obtenerImpresoras();
                
                await this.sleep(500);
                
                if (localOK && remotoOK && impresorasOK) {
                    this.log('🎉 ¡Todas las pruebas pasaron! Sistema listo para imprimir.', 'success');
                } else {
                    this.log('⚠️  Algunas pruebas fallaron. Revisa los mensajes anteriores.', 'warning');
                }
            }
        }
        
        // Instancia global
        const prueba = new PruebaImpresionRemota();
        
        // Funciones para los botones
        function actualizarConfiguracion() {
            const nuevaIP = document.getElementById('ipRemota').value.trim();
            if (nuevaIP) {
                prueba.actualizarIP(nuevaIP);
            } else {
                alert('Ingresa una IP válida');
            }
        }
        
        function iniciarPruebas() {
            prueba.ejecutarPruebasCompletas();
        }
        
        function imprimirPrueba() {
            const impresora = document.getElementById('impresora-select').value;
            if (impresora) {
                prueba.imprimirPrueba(impresora);
            } else {
                alert('Selecciona una impresora primero');
            }
        }
        
        // Ejecutar pruebas automáticamente al cargar la página
        window.addEventListener('load', () => {
            setTimeout(() => {
                prueba.log('🔄 Ejecutando pruebas automáticas...', 'info');
                prueba.ejecutarPruebasCompletas();
            }, 1000);
        });
    </script>
</body>
</html>