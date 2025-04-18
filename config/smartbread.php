<?php

return [
    'templates' => [
        'Livewire - Volt - Bread Web & API' => 'stubs/module-smartbread/livewire-volt-full',
        /*
        'Breeze - Blade - CRUD Web only' => 'stubs/module-generator/breeze-crud-web',
        'Breeze - Blade - CRUD API only' => 'stubs/module-generator/breeze-crud-api'
        */
    ],
    'ignore_files' => ['module.json'],
    'use_singular' => 'true',
    'routes_pathnames' => [ 'routes/api.php', 'routes/web.php' ],
    'append_module_to_tablename' => true,
];