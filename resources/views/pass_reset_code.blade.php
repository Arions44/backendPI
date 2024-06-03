<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8"> <!-- utf-8 works for most cases -->
  <meta name="viewport" content="width=device-width"> <!-- Forcing initial-scale shouldn't be necessary -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge"> <!-- Use the latest (edge) version of IE rendering engine -->
  <meta name="x-apple-disable-message-reformatting">  <!-- Disable auto-scale in iOS 10 Mail entirely -->
  <title></title>
  <style>
    body {
      text-align: center !important;
      margin-top: 5% !important;
      padding: 0 !important;
      height: 100% !important;
      width: 100% !important;
    }

    img {
      width: 200px; /* Ancho del logo */
      margin-bottom: 20px;
    }

    p {
      margin-bottom: 30px;
    }

    button {
      margin-top: 30px;
      background-color: #4CAF50;
      color: white;
      padding: 10px 20px;
      font-size: 16px;
      border: none;
      border-radius: 30px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    button:hover {
      background-color: #45a049;
    }

    button:active {
      transform: scale(0.95);
    }
  </style>

</head>
<body style="width: 100%; background: #EFF3FC;">
  <img src="https://tarjetafanky.com/static/media/Logo_Fanky_2_0.109bb598f93959bf6469795a1a24d66b.svg" alt="Logo de la empresa">
  <h1 style="margin: 0 0 10px 0; font-family: Open Sans, Roboto, Helvetina Neue, sans-serif; font-size: 32px; line-height: 42px; color: #333333; font-weight: 500; text-align:center; opacity:0.95">Email de Confirmación&nbsp;</h1>
  <p style="margin: 0; text-align:center; font-size: 16px; opacity: 0.9; line-height: 1.5; padding-left: 20px; padding-right: 20px;">
    Hola {{ $name }}
    <br><br>Tu código para modificar la contraseña es:&nbsp;
  </p>
  <p style="margin: 0; text-align:center; font-size: 24px; opacity: 0.9; line-height: 1.5; padding-left: 20px; padding-right: 20px;">
    <br><strong>{{ $reset_pass_code }}</strong>
  </p>
  <p style="padding: 30px 0px 20px 0px;width: 100%;font-size: 0.85em; font-family: Open Sans, Roboto, Helvetina Neue, sans-serif; text-align: center; color: #353535;" >
    Enviado por el equipo de <b>Fanky | <a href="http://www.tarjetafanky.com" style="color: #3CA2FF; line-height:1.5em">www.tarjetafanky.com<a>
    <br>
  </p>
</body>
</html>
