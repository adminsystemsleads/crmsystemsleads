<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Calculadora de Ganancias GoHighLevel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }

        .calculator-container {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
            padding: 24px;
        }

        h1 {
            margin-top: 0;
            font-size: 1.6rem;
            color: #111827;
        }

        p {
            color: #4b5563;
            font-size: 0.95rem;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
        }

        input, select {
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            font-size: 0.95rem;
            outline: none;
        }

        input:focus, select:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 1px rgba(79, 70, 229, 0.1);
        }

        .toggle-group {
            display: flex;
            gap: 12px;
            align-items: center;
            font-size: 0.9rem;
        }

        .toggle-group input {
            width: auto;
        }

        .results {
            margin-top: 24px;
            padding: 16px;
            border-radius: 12px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }

        .results h2 {
            margin-top: 0;
            font-size: 1.1rem;
            color: #111827;
        }

        .result-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
            margin-top: 12px;
        }

        .stat {
            padding: 10px 12px;
            border-radius: 10px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .stat-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #111827;
            margin-top: 4px;
        }

        .highlight {
            background: #ecfdf5;
            border-color: #6ee7b7;
        }

        .highlight .stat-value {
            color: #15803d;
        }

        .small {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .footer-note {
            margin-top: 12px;
            font-size: 0.8rem;
            color: #6b7280;
        }
    </style>
</head>
<body>
<div class="calculator-container">
    <h1>Calculadora de Ganancias – GoHighLevel White Label</h1>
    <p>
        Ajusta los valores según cuánto cobras a tus clientes. El cálculo se hace
        automáticamente para mostrar tu <strong>ganancia mensual estimada</strong>.
    </p>

    <!-- PLAN Y COSTO GHL -->
    <div class="grid">
        <div class="field">
            <label for="plan">Plan que pagas a GoHighLevel</label>
            <select id="plan">
                <option value="97">Starter – $97 / mes</option>
                <option value="297" selected>Unlimited – $297 / mes</option>
                <option value="497">Pro (SaaS) – $497 / mes</option>
            </select>
            <span class="small">Costo fijo que tú pagas a GoHighLevel.</span>
        </div>

        <div class="field">
            <label for="numClientes">Número de clientes</label>
            <input type="number" id="numClientes" value="10" min="0" />
            <span class="small">Cuentas activas a las que les revendes el CRM.</span>
        </div>
    </div>

    <!-- PRECIOS POR CLIENTE -->
    <h2 style="margin-top: 24px; font-size: 1.1rem; color:#111827;">Ingresos por cliente</h2>
    <div class="grid">
        <div class="field">
            <label for="precioCRM">Precio base del CRM por cliente (USD)</label>
            <input type="number" id="precioCRM" value="150" min="0" step="1" />
            <span class="small">Ej: acceso a la plataforma, embudos, automatizaciones, etc.</span>
        </div>
        <div class="field">
            <label for="precioWhatsApp">Extra mensual por WhatsApp (USD)</label>
            <input type="number" id="precioWhatsApp" value="0" min="0" step="1" />
            <span class="small">Si incluyes WhatsApp Cloud / API como servicio adicional.</span>
        </div>
        <div class="field">
            <label for="precioAdmin">Fee por administración de campañas (USD)</label>
            <input type="number" id="precioAdmin" value="50" min="0" step="1" />
            <span class="small">Gestión / optimización de campañas sin creación desde cero.</span>
        </div>
        <div class="field">
            <label for="precioCreacion">Fee por creación de campañas (USD)</label>
            <input type="number" id="precioCreacion" value="100" min="0" step="1" />
            <span class="small">Armado de campañas, estructura, copys, etc.</span>
        </div>
    </div>

    <!-- OPCIONES DE SERVICIO -->
    <h2 style="margin-top: 24px; font-size: 1.1rem; color:#111827;">Opciones de servicio por cliente</h2>
    <div class="grid">
        <div class="field">
            <label>¿Incluir WhatsApp?</label>
            <div class="toggle-group">
                <input type="checkbox" id="incluyeWhatsApp" />
                <span>Sumar el extra de WhatsApp al precio final</span>
            </div>
        </div>
        <div class="field">
            <label>Tipo de campaña</label>
            <select id="tipoCampana">
                <option value="ninguna">Sin campañas</option>
                <option value="admin" selected>Solo administración</option>
                <option value="creacion">Con creación de campañas</option>
            </select>
        </div>
    </div>

    <!-- RESULTADOS -->
    <div class="results" id="resultados">
        <h2>Resultados estimados mensuales</h2>
        <div class="result-grid">
            <div class="stat">
                <div class="stat-label">Ingreso por cliente</div>
                <div class="stat-value" id="ingresoPorCliente">$0</div>
                <div class="small" id="detallePorCliente"></div>
            </div>
            <div class="stat">
                <div class="stat-label">Ingreso total (todos los clientes)</div>
                <div class="stat-value" id="ingresoTotal">$0</div>
            </div>
            <div class="stat">
                <div class="stat-label">Costo de tu plan GoHighLevel</div>
                <div class="stat-value" id="costoPlan">$0</div>
            </div>
            <div class="stat highlight">
                <div class="stat-label">Ganancia neta mensual</div>
                <div class="stat-value" id="gananciaNeta">$0</div>
            </div>
        </div>

        <p class="footer-note">
            *Esto es solo una estimación. No incluye gastos de publicidad, impuestos ni otros costos operativos.
        </p>
    </div>
</div>

<script>
    const plan = document.getElementById("plan");
    const numClientes = document.getElementById("numClientes");
    const precioCRM = document.getElementById("precioCRM");
    const precioWhatsApp = document.getElementById("precioWhatsApp");
    const precioAdmin = document.getElementById("precioAdmin");
    const precioCreacion = document.getElementById("precioCreacion");
    const incluyeWhatsApp = document.getElementById("incluyeWhatsApp");
    const tipoCampana = document.getElementById("tipoCampana");

    const ingresoPorClienteEl = document.getElementById("ingresoPorCliente");
    const detallePorClienteEl = document.getElementById("detallePorCliente");
    const ingresoTotalEl = document.getElementById("ingresoTotal");
    const costoPlanEl = document.getElementById("costoPlan");
    const gananciaNetaEl = document.getElementById("gananciaNeta");

    function formatUSD(value) {
        return "$" + value.toFixed(2);
    }

    function calcular() {
        const costoPlan = parseFloat(plan.value) || 0;
        const clientes = parseInt(numClientes.value) || 0;
        const baseCRM = parseFloat(precioCRM.value) || 0;
        const extraWhats = parseFloat(precioWhatsApp.value) || 0;
        const feeAdmin = parseFloat(precioAdmin.value) || 0;
        const feeCreacion = parseFloat(precioCreacion.value) || 0;

        let ingresoPorCliente = baseCRM;
        let detalle = baseCRM > 0 ? `CRM: ${formatUSD(baseCRM)}` : "";

        if (incluyeWhatsApp.checked && extraWhats > 0) {
            ingresoPorCliente += extraWhats;
            detalle += detalle ? ` + WhatsApp: ${formatUSD(extraWhats)}` : `WhatsApp: ${formatUSD(extraWhats)}`;
        }

        if (tipoCampana.value === "admin" && feeAdmin > 0) {
            ingresoPorCliente += feeAdmin;
            detalle += detalle ? ` + Admin campañas: ${formatUSD(feeAdmin)}` : `Admin campañas: ${formatUSD(feeAdmin)}`;
        } else if (tipoCampana.value === "creacion" && feeCreacion > 0) {
            ingresoPorCliente += feeCreacion;
            detalle += detalle ? ` + Creación campañas: ${formatUSD(feeCreacion)}` : `Creación campañas: ${formatUSD(feeCreacion)}`;
        }

        const ingresoTotal = ingresoPorCliente * clientes;
        const gananciaNeta = ingresoTotal - costoPlan;

        ingresoPorClienteEl.textContent = formatUSD(ingresoPorCliente);
        detallePorClienteEl.textContent = detalle || "Sin cargos configurados.";
        ingresoTotalEl.textContent = formatUSD(ingresoTotal);
        costoPlanEl.textContent = formatUSD(costoPlan);
        gananciaNetaEl.textContent = formatUSD(gananciaNeta);
    }

    // Escuchar cambios en todos los campos
    [
        plan,
        numClientes,
        precioCRM,
        precioWhatsApp,
        precioAdmin,
        precioCreacion,
        incluyeWhatsApp,
        tipoCampana,
    ].forEach((el) => el.addEventListener("input", calcular));

    // cálculo inicial
    calcular();
</script>
</body>
</html>
