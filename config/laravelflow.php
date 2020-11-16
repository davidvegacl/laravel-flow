<?php

return [
    /*
     * URL de la API Flow
     *
     * Pruebas / testing: https://sandbox.flow.cl/api
     * Producción: https://www.flow.cl/api
     */
    'api_url' => env('FLOW_API_URL', 'https://sandbox.flow.cl/api'),

    /*
     * Flow API key
     * En caso de usar una misma key en todo el sistema
     * Se puede especificar para cada transacción.
     */
    'api_key' => env('FLOW_API_KEY', ''),

    /*
     * Flow secret key
     * En caso de usar una misma key en todo el sistema
     * Se puede especificar para cada transacción.
     */
    'api_key' => env('FLOW_SECRET_KEY', ''),
];
