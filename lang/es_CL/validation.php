<?php

return [
    'required' => 'El campo :attribute es obligatorio.',
    'email' => 'El campo :attribute debe ser un correo válido.',
    'unique' => 'El :attribute ya está registrado.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'min' => [
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'attributes' => [
        'first_name' => 'nombre',
        'last_name' => 'apellido',
        'rut' => 'RUT',
        'email' => 'correo electrónico',
        'password' => 'contraseña',
        'role' => 'rol',
        'current_password' => 'contraseña actual',
    ],
];
