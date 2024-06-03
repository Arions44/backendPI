# La librería Selenium permite obtener información de una URL,
#  cómo haría una persona, usando un navegador

# Lo primero que hay que hacer es instalar ChromeDriver
#  https://chromedriver.chromium.org/downloads

# Y luego vamos a instalar Selenium
#  https://selenium-python.readthedocs.io/installation.html
#  Para ello ejecutamos el comando 'pip install selenium'

# Importamos las librerías que vamos a necesitar
import os
import time

# Primero leemos el fichero para saber que fecha y hora nos interesa
with open('cambio.txt', 'r') as archivo:
    lineas = archivo.readlines()
    # Itera sobre las líneas del archivo
    # for linea in archivo:

# Elimina los saltos de línea (\n) al final de cada línea
lineas = [linea.rstrip('\n') for linea in lineas]
if (len(lineas)>0):
    dia = lineas[0]
    hora = lineas[1]
    print ('Día: ' + dia)

    with open('salida.txt', 'w') as archivo:
        # Escribe datos en el archivo
        archivo.write('Día: ' + dia + '\n')
        archivo.write('Hora: ' + hora)
else:
    with open('salida.txt', 'w') as archivo:
        # Escribe datos en el archivo
        archivo.write('falta algún dato')


