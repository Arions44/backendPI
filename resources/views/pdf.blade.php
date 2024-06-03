<!DOCTYPE html>
<html lang="es">
<head>
    <title>
        Etadisticas ofertas
    </title>
    <style>
        p {
            margin-right: 70px;
        }
        .logo {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="logo">
        <img src="https://tarjetafanky.com.es/fankyapiapp/resources/images/Logo_fanky.jpg" style="width:200px;" alt="Fanky"/>
    </div>

    <h1>Estadísticas del uso de la App</h1>
    <h2>Número de logins en la App: {{ $usersCount }}</h2><br><br>

    <h1>Estadísticas de las ofertas</h1>
    <table>
        <tr>
            <td style="font-weight: bold;font-size:25px;">Empresa</td>
            <td style="font-weight: bold;font-size:25px;">Oferta</td>
            <td style="font-weight: bold;font-size:25px;">Clicks</td>
        </tr>

        @foreach($offers as $offer)
            <tr>
                <td>{{ $offer->business->name }}</td>
                <td>{{ $offer->name }}</td>
                <td>{{ $offer->counter }}</td>
            </tr>
            <br>
        @endforeach
    </table>
</body>
</html>
