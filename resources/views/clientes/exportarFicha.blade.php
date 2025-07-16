<!DOCTYPE html>
<html class="bg-white" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>

        body {
            min-width: 400px;
            display: flex;
            flex-direction: column;
            align-items: center;
            font-family: sans-serif;
            background-color: #ffffff; /* bg-slate-500 */
            color: rgb(73, 73, 73); /* dark:text-white/50 */
            font-size: .475rem; /* text-3xl */
        }

        .container {
            max-width: 1024px; /* max-w-4xl */
            margin: auto; /* mt-10 */
            background-color: #f8fafb; /* bg-slate-100 */
            font-family: sans-serif;
        }

        .title {
            color: #dc2626;
            font-size: 1.5rem; /* text-3xl */
            font-weight: bold; /* font-bold */
            margin: 0.1rem; /* m-5 */
        }

        .info-box {
            margin: .1rem; /* m-5 */
            padding: .1rem; /* p-5 */
            border: 1px solid #d1d5db; /* border-gray-300 */
            border-radius: 0.375rem; /* rounded-md */
        }

        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem; /* space-y-4 */
        }

        .label {
            font-size: 0.875rem; /* text-sm */
            font-weight: 500; /* font-medium */
            color: #374151; /* text-gray-700 */
        }

        .value {
            font-size: 0.875rem; /* text-sm */
            color: #111827; /* text-gray-900 */
        }

        .status {
            font-size: 0.875rem; /* text-sm */
        }

        .suspended {
            color: #dc2626; /* text-red-600 */
        }

        .subtitle {
            font-size: 1rem; /* text-xl */
            font-weight: bold; /* font-bold */
            margin-top: 1rem; /* mt-4 */
            color: #1c0c77; /* text-red-600 */
        }
    </style>
    <title>La Marina V3</title>
    <link rel="icon" href="{{ url('images/favicon.png') }}" type="image/png">
</head>

<body class="body-style">
    <div class="container">
        <h1 class="title">Ficha del Cliente</h1>
        <div class="info-box">
            <div class="info-item">
                <label class="label">Fecha de Creación:</label>
                <span class="value">{{ $cliente->created_at ?? 'No disponible' }}</span>
            </div>
            <div class="info-item">
                <label class="label">Modificado el:</label>
                <span class="value">{{ $cliente->updated_at ?? 'No disponible' }}</span>
            </div>
            <div class="info-item">
                <label class="label">DNI:</label>
                <span class="value">{{ $cliente->dni }}</span>
            </div>
            <div class="info-item">
                <label class="label">Nombre:</label>
                <span class="value">{{ $cliente->nombre }}</span>
                <span class="status {{ $cliente->suspendido ? 'suspended' : '' }}">{{ $cliente->suspendido ? 'CLIENTE SUSPENDIDO' : '' }}</span>
            </div>
            <div class="info-item">
                <label class="label">Banco:</label>
                <span class="value">{{ $cliente->banco }}</span>
            </div>
            <div class="info-item">
                <label class="label">CBU:</label>
                <span class="value">{{ $cliente->cbu }}</span>
            </div>
            <div class="info-item">
                <label class="label">Teléfono 1:</label>
                <span class="value">{{ $cliente->tel1 }}</span>
            </div>
            <div class="info-item">
                <label class="label">Teléfono 2:</label>
                <span class="value">{{ $cliente->tel2 }}</span>
            </div>
            <div class="info-item">
                <label class="label">Email:</label>
                <span class="value">{{ $cliente->email }}</span>
            </div>
            <div class="info-item">
                <label class="label">Dirección:</label>
                <span class="value">{{ $cliente->direccion }}</span>
            </div>
            <div class="info-item">
                <label class="label">Provincia:</label>
                <span class="value">{{ $cliente->localidad->provincia->nombre ?? 'No disponible' }}</span>
            </div>
            <div class="info-item">
                <label class="label">Localidad:</label>
                <span class="value">{{ $cliente->localidad->nombre ?? 'No disponible' }}</span>
            </div>
            <div class="info-item">
                <label class="label">Tipo de Haber:</label>
                <span class="value">{{ $cliente->haber->nombre }}</span>
            </div>
            <div class="info-item">
                <label class="label">Saldo:</label>
                <span class="value">{{ $cliente->saldo }}</span>
            </div>
            <div class="info-item">
                <label class="label">Límite:</label>
                <span class="value">{{ $cliente->limite }}</span>
            </div>
            <div class="info-item">
                <label class="label">Sucursal:</label>
                <span class="value">{{ $cliente->sucursal }}</span>
            </div>
            <div class="info-item">
                <label class="label">Máximo de Cuotas:</label>
                <span class="value">{{ $cliente->max_cuotas }}</span>
            </div>
            <div class="info-item">
                <label class="label">Observaciones:</label>
                <p class="value">{{ $cliente->obs }}</p>
            </div>
            <br><hr/>
            <div>
                <h2 class="subtitle">Familiar de Contacto:</h2>
                <div class="info-item">
                    <label class="label">Tipo de Familiar:</label>
                    <span class="value">{{ $cliente->tipo_familiar }}</span>
                </div>
                <div class="info-item">
                    <label class="label">Familiar:</label>
                    <span class="value">{{ $cliente->familiar }}</span>
                </div>
                <div class="info-item">
                    <label class="label">Dirección:</label>
                    <span class="value">{{ $cliente->direccion_familiar }}</span>
                </div>
                <div class="info-item">
                    <label class="label">Localidad:</label>
                    <span class="value">{{ $cliente->localidadFamiliar->nombre ?? 'No disponible' }}</span>
                </div>
                <div class="info-item">
                    <label class="label">Provincia:</label>
                    <span class="value">{{ $cliente->localidadFamiliar->provincia->nombre ?? 'No disponible' }}</span>
                </div>
                <div class="info-item">
                    <label class="label">Email:</label>
                    <span class="value">{{ $cliente->email_familiar }}</span>
                </div>
                <div class="info-item">
                    <label class="label">Teléfono:</label>
                    <span class="value">{{ $cliente->tel_familiar }}</span>
                </div>
            </div>
            <br><hr/>
            <div>
                <h2 class="subtitle">Lugar de Trabajo:</h2>
                <div class="info-item">
                    <label class="label">Trabajo:</label>
                    <span class="value">{{ $cliente->trabajo }}</span>
                </div>
                <div class="info-item">
                    <label class="label">Dirección:</label>
                    <span class="value">{{ $cliente->direccion_trabajo }}</span>
                </div>
                <div class="info-item">
                    <label class="label">Localidad:</label>
                    <span class="value">{{ $cliente->localidadTrabajo->nombre ?? 'No disponible' }}</span>
                </div>
                <div class="info-item">
                    <label class="label">Provincia:</label>
                    <span class="value">{{ $cliente->localidadTrabajo->provincia->nombre ?? 'No disponible' }}</span>
                </div>
                <div class="info-item">
                    <label class="label">Teléfono Trabajo:</label>
                    <span class="value">{{ $cliente->tel_trabajo }}</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>