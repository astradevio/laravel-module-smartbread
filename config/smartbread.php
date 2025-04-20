<?php

return [
    'templates' => [
        'Livewire - Volt - Bread Web & API with SmartForms' => 'stubs/smartbread/livewire-volt-full-smartforms',
        'Livewire - Volt - Bread Web & API' => 'stubs/smartbread/livewire-volt-full',
    ],
    'ignore_files' => ['module.json'],
    'use_singular' => 'true',
    'routes_pathnames' => [ 'routes/api.php', 'routes/web.php' ],
    'append_module_to_tablename' => true,
];
