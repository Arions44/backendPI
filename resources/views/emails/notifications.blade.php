<!DOCTYPE html>
<html>
<head>
    <title>Notificación</title>
</head>
<body>
    <h1>Hola {{ $recipient->name }}</h1>
    <p>El alumno {{ $user->name }} ha tenido varias emociones negativas del tipo "{{ $moodDescription }}". Puedes mirar a ver por qué puede pasar consultándole o pidiendo una cita al orientador.</p>
</body>
</html>