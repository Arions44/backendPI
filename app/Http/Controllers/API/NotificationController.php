<?php

namespace App\Http\Controllers\API;

use App\Models\Notification;
use App\Models\User;
use App\Models\Mood; // Asegúrate de tener el modelo Mood o como se llame en tu aplicación
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class NotificationController extends BaseController
{
    public function store(Request $request)
    {
        $userId = $request->query('user_id');
        $moodId = $request->query('mood_id');
        
        // Validar que los parámetros existan
        if (!$userId || !$moodId) {
            return response()->json(['message' => 'user_id and mood_id are required'], 400);
        }

        // Obtener la emoción
        $mood = Mood::find($moodId);

        // Verificar si el parámetro negative es 1
        if ($mood->negative != 1) {
            return response()->json(['message' => 'This is not a negative emotion'], 400);
        }

        // Obtener la notificación actual para el user_id y mood_id
        $notification = Notification::where('user_id', $userId)
                                    ->where('mood_id', $moodId)
                                    ->first();
        
        if ($notification) {
            // Incrementar el count
            $notification->count += 1;

            // Obtener el usuario y el destinatario
            $user = User::find($userId);
            $recipient = $this->findRecipient($user);

            if ($recipient) {
                // Obtener el límite de notificaciones del destinatario
                $notificationLimit = $recipient->notification_limit ?? 5; // Por defecto 5 si no está definido

                if ($notification->count == $notificationLimit) {
                    $this->sendEmailNotification($user, $recipient, $mood->description);

                    // Eliminar la notificación
                    $notification->delete();

                    return response()->json([
                        'message' => 'Email sent and notification deleted',
                        'email' => $recipient->email,
                        'sender' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'school_year' => $user->school_year
                        ],
                        'recipient' => [
                            'id' => $recipient->id,
                            'name' => $recipient->name,
                            'email' => $recipient->email,
                            'type' => $recipient->type,
                            'school_year' => $recipient->school_year,
                            'notification_limit' => $recipient->notification_limit
                        ]
                    ]);
                } else {
                    $notification->save();
                    return response()->json([
                        'message' => 'Count incremented',
                        'count' => $notification->count
                    ]);
                }
            } else {
                return response()->json([
                    'message' => 'No recipient found with type "t" and the same school year'
                ], 404);
            }
        } else {
            // Crear una nueva notificación con count = 1
            Notification::create([
                'user_id' => $userId,
                'mood_id' => $moodId,
                'count' => 1,
            ]);

            return response()->json([
                'message' => 'Notification created',
                'count' => 1
            ]);
        }
    }

    protected function findRecipient($user)
    {
        return User::where('type', 't')
                    ->where('school_year', $user->school_year)
                    ->first();
    }

    protected function sendEmailNotification($user, $recipient, $moodDescription)
    {
        $email = $recipient->email;
        $data = [
            'user' => $user,
            'recipient' => $recipient,
            'moodDescription' => $moodDescription
        ];

        Mail::send('emails.notifications', $data, function ($message) use ($email, $recipient) {
            $message->to($email)->subject('Has recibido ' . ($recipient->notification_limit ?? 5) . ' notificaciones!');
        });
    }

    public function setNotificationLimit(Request $request)
    {
        $userId = $request->query('user_id');
        $notificationLimit = $request->query('notification_limit');
        
        // Validar que los parámetros existan
        if (!$userId || !$notificationLimit) {
            return response()->json(['message' => 'user_id and notification_limit are required'], 400);
        }

        // Obtener el usuario
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Actualizar el atributo notification_limit
        $user->notification_limit = $notificationLimit;
        $user->save();

        return response()->json(['message' => 'Notification limit updated successfully']);
    }
}