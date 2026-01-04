<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Listado de reglas de validación
    */
    private array $rules = [
        'name' => 'required|string|max:255|unique:users,name',
        'email' => 'required|string|email|max:255|unique:users,email',
        'password' => 'required|string|min:8',
    ];

    /**
     * Listado de mensajes de validación
    */
    private array $messages = [
        'name.required' => 'El nombre del usuario es obligatorio.',
        'name.string' => 'El nombre del usuario debe ser una cadena de texto.',
        'name.max' => 'El nombre del usuario no debe exceder los 255 caracteres.',
        'name.unique' => 'El nombre del usuario ya está en uso.',

        'email.required' => 'El correo electrónico es obligatorio.',
        'email.string' => 'El correo electrónico debe ser una cadena de texto.',
        'email.email' => 'El correo electrónico debe tener un formato válido.',
        'email.max' => 'El correo electrónico no debe exceder los 255 caracteres.',
        'email.unique' => 'El correo electrónico ya está en uso.',

        'password.required' => 'La contraseña es obligatoria.',
        'password.string' => 'La contraseña debe ser una cadena de texto.',
        'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        'password.alpha_num' => 'La contraseña debe contener solo caracteres alfanuméricos',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Titulo principal
        $this->alert('Create a New User');

        // Solicitar Nombre
        $name = $this->ask('Enter the name');

        // Solicitar Correo
        $email = $this->ask('Enter the email');

        // Solicitar Contraseña
        $password = $this->secret('Enter the password');

        // Crear validador
        $validator = Validator::make(
            compact('name', 'email', 'password'),
            $this->rules, 
            $this->messages
        );

        // Mostrar errores si existen
        if($validator->fails()){
            foreach($validator->errors()->all() as $error){
                $this->error($error);
            }
            return;
        }

        // Obtener datos validados de forma segura
        $data = $validator->safe()->only(['name', 'email', 'password']);

        // Insertar en la DB
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        /* Visualizar la información del usuario creado */
        $this->info('User ID: ' . $user->id);
        $this->info('Name: ' . $user->name);
        $this->info('Email: ' . $user->email);

        $this->alert('User created successfully.');
    }
}
