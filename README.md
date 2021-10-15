# UDECDash Backend

UDECDash Backend es un componente de UDECDash, visite la wiki del repositorio para encontrar más información sobre este y otros componentes.

````
Copyright (C) <2021> <IDECLab>

Esta biblioteca es software gratuito; puedes redistribuirlo y/o
modificarlo bajo los términos del GNU Lesser General Public
Licencia publicada por la Free Software Foundation; cualquiera
versión 2.1 de la Licencia, o (a su elección) cualquier versión posterior.
Esta biblioteca se distribuye con la esperanza de que sea útil,
pero SIN NINGUNA GARANTÍA; sin siquiera la garantía implícita de
COMERCIABILIDAD o APTITUD PARA UN PROPÓSITO EN PARTICULAR. Ver el GNU
Licencia pública general menor para obtener más detalles.
````

## Requisitos
- Php 8.0
- Postgresql 13

UDECDash backend está desarrollada en laravel 8, por lo cual, hereda todos los requisitos de instalación de una aplicación laravel tradicional ([Ver requisitos aquí](https://laravel.com/docs/8.x/deployment#server-requirements "Requisitos de laravel")). Sin embarago, las versiones especificadas se deben respetar debido al uso de sintaxis y funciones exclusivas para dichas versiones. 
El motor de base de datos está limitado a postgresql, el restos de los driver pueden ser modificados según su preferencia. 

Se recomienda utilizar Redis como driver de caché.


## Instalación

1) Descarga el proyecto

`git clone https://github.com/ideclab/udecdash-backend.git`

2) Instala las dependencias

`composer update`

3) Crea una base de datos en postgresql y ejecuta en ella el script DDL ubicado en:

`/LoadCanvasData/canvas_data_portal_ddl.sql`

4) Copia la plantilla del fichero de entorno **.env.example** y renombra la copia por **.env**

5) Modifica el fichero de entorno **.env** (Ve el apartado de configuración para entender las nuevas claves)

6) Ejecuta las migraciones

`php artisan migrate`

## Configuración
Una vez que tengamos nuestro fichero de entorno podremos ver que posee nuevas claves en comparación a un fichero de entorno tradicional de laravel, para saber que valores asignar se explicará que hace cada apartado.

 

------------


Habilita el modo de debug para no notificar a usuarios finales y define un correo en donde recibirás sus notificaciones.
````
NOTIFICATIONS_DEBUG_MODE=true 
NOTIFICATIONS_DEBUG_EMAIL=example@domain.cl
````

------------



La aplicación procesará en lotes (chunk) las interacciones, define una cantidad acorde a sus capacidades de procesamiento.

````
PROCESS_REQUETS_CHUNK_SIZE=5000000
````


------------


Limita la cantidad de solicitudes de actualización de reportes

**Observación:** Actualmente canvas data portal tiene un desfase de 48 Horas y una limitancia de actualización de datos de 24 Hrs. Tener un tiempo de actualización menor consumirá procesamiento y no reflejará datos diferentes.
````
COURSE_UPDATE_HOURS_LIMIT=24
````

------------


Asignación de cursos para cada cola de trabajo según la cantidad de estudiantes. Si usted quiere trabajar con menos colas de trabajo puede asignar ````0```` para ignorar dicha cola.

````
MAX_MEMBERS_FIRST_QUEUE=12
MAX_MEMBERS_SECOND_QUEUE=20
MAX_MEMBERS_THIRD_QUEUE=30
MAX_MEMBERS_FOURTH_QUEUE=40
MAX_MEMBERS_FIFTH_QUEUE=50
MAX_MEMBERS_SIXTH_QUEUE=60
MAX_MEMBERS_SEVENTH_QUEUE=70
MAX_MEMBERS_EIGHTH_QUEUE=85
MAX_MEMBERS_NINETH_QUEUE=110
MAX_MEMBERS_TENTH_QUEUE=3000
````

------------


Define el dominio donde se encontrará el frontend
````
FRONTEND_URL= https://www.example.com
````

------------


Agrega la url de tu instancia de Canvas LMS. Registra una nueva aplicación OAuth en Canvas LMS y agrega el id y secreto que te proporcionará. 

**Observación:** La instancia Oauth creada en Canvas LMS es la misma que se utilizará en UDECDash Frontend e UDECDash Backend, si ya haz creado una deberás reutilizar las credenciales.
`
````
CANVAS_URL=https://your_instance.instructure.com
CANVAS_CLIENT_ID= 
CANVAS_CLIENT_SECRET= 
````
