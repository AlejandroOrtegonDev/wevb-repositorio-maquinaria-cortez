<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor, completa todos los campos correctamente.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'subject' => $request->subject,
                'message' => $request->message,
            ];

            // Enviar email al administrador
            Mail::send('emails.contact', $data, function ($message) use ($data) {
                $message->to('maquinariaaracortes@gmail.com')
                        ->subject('Nuevo mensaje de contacto: ' . $data['subject'])
                        ->from($data['email'], $data['name']);
            });

            // Email de confirmación al usuario
            Mail::send('emails.contact-confirmation', $data, function ($message) use ($data) {
                $message->to($data['email'])
                        ->subject('Gracias por contactarnos - Maquinaria Cortes CQ SAS')
                        ->from('maquinariaaracortes@gmail.com', 'Maquinaria Cortes CQ SAS');
            });

            return response()->json([
                'success' => true,
                'message' => 'Mensaje enviado exitosamente. Nos pondremos en contacto contigo pronto.'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error al enviar email de contacto: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Hubo un error al enviar el mensaje. Por favor, intenta nuevamente o contáctanos directamente.'
            ], 500);
        }
    }
}

