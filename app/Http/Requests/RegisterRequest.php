<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\pL\s]+$/u' // Solo letras y espacios
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns', // Validación estricta de email con verificación DNS
                'max:255',
                'unique:users'
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:50',
                'regex:/[a-z]/',      // debe contener al menos una letra minúscula
                'regex:/[A-Z]/',      // debe contener al menos una letra mayúscula
                'regex:/[0-9]/',      // debe contener al menos un número
                'regex:/[@$!%*#?&]/', // debe contener al menos un carácter especial
                'confirmed'           // requiere campo password_confirmation
            ]
        ];
    }

    public function messages()
    {
        return [
            'name.regex' => 'El nombre solo puede contener letras y espacios',
            'email.email' => 'El correo electrónico debe ser una dirección válida',
            'email.unique' => 'Este correo electrónico ya está registrado',
            'password.regex' => 'La contraseña debe contener al menos una letra minúscula, una mayúscula, un número y un carácter especial',
            'password.confirmed' => 'La confirmación de contraseña no coincide'
        ];
    }
}

class ProjectRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:3'
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000'
            ]
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre del proyecto es obligatorio',
            'name.min' => 'El nombre del proyecto debe tener al menos 3 caracteres',
            'description.max' => 'La descripción no puede exceder los 1000 caracteres'
        ];
    }
}

class TaskRequest extends FormRequest
{
    public function rules()
    {
        $taskId = $this->route('task');
        $minDate = $taskId ? null : now()->startOfDay();

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                'min:3'
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'due_date' => [
                'required',
                'date',
                'after_or_equal:' . $minDate,
            ],
            'status' => [
                'required',
                'string',
                'in:pendiente,en progreso,completada'
            ],
            'priority' => [
                'nullable',
                'string',
                'in:baja,media,alta'
            ],
            'estimated_hours' => [
                'nullable',
                'numeric',
                'min:0.5',
                'max:100'
            ]
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'El título de la tarea es obligatorio',
            'title.min' => 'El título debe tener al menos 3 caracteres',
            'due_date.required' => 'La fecha de vencimiento es obligatoria',
            'due_date.after_or_equal' => 'La fecha de vencimiento debe ser igual o posterior a hoy',
            'status.in' => 'El estado debe ser: pendiente, en progreso o completada',
            'priority.in' => 'La prioridad debe ser: baja, media o alta',
            'estimated_hours.min' => 'Las horas estimadas deben ser al menos 0.5',
            'estimated_hours.max' => 'Las horas estimadas no pueden exceder 100'
        ];
    }
}
?>
