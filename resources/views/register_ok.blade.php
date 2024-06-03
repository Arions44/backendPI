<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8"> <!-- utf-8 works for most cases -->
  <meta name="viewport" content="width=device-width"> <!-- Forcing initial-scale shouldn't be necessary -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge"> <!-- Use the latest (edge) version of IE rendering engine -->
  <meta name="x-apple-disable-message-reformatting">  <!-- Disable auto-scale in iOS 10 Mail entirely -->
  <title></title>
</head>
<body>
    <img src="https://allsites.es/logoMindCare.png" alt="logo" />
    <p style="font-family: Arial, sans-serif">
        <br>¡Enhorabuena <strong> Tutor</strong>!
        <br><br>Estos son los alumnos que se han dado de alta, junto con sus códigos<br><br>

    </p>
    <table>
		@forelse($nuevosUsuariosArray as $nuevoUsuario)
			<tr>
				<td>
					{{ $nuevoUsuario->email }}
				</td>
				<td>
					{{ $nuevoUsuario->secret_code }}
				</td>
			</tr>
    	@empty
			<div>

			</div>
    	@endforelse
    </table>
	<br><br>Gracias por utilizar MindCare
</body>
</html>
