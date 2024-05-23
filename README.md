# ChessGame

## Plataforma para jugar al ajedrez por correspondencia (React + JS + PHP)

![Pantalla de Inicio](src/assets/inicio.PNG)

Para hacer uso de la VERSIÓN DEMO es necesario activar en:

<ul>
<li>Chrome:</li>
<p style="color:red;">chrome://flags --> allow invalid resources from localhost --> ENABLED </p>
<li>Firefox:</li>
<p style="color:red;">about://config --> privacy.file_unique_origin --> FALSE </p>
<li>Edge:</li>
<p style="color:red;">edge://flags --> block insecure private network requests --> DISABLED </p>
</ul>

**VERSIÓN DEMO:** --> [ChessGame](https://chessg4me.000webhostapp.com) <--

![Partida](src/assets/partida.PNG)

Para iniciar el proyecto en local es necesario:

Importar la base de datos --> chessdb.sql

<ul>
  <li>npm install</li>
  <li>npm install axios</li>
  <li>npm install bootstrap</li>
  <li>npm install bootstrap-icons</li>
  <li>npm install react-router-dom</li>
  <li>npm install @material-ui/core</li>
  <li>npm install @material-ui/icons --force</li>
</ul>

**Importante:** Modificar get_connection() para que apunte a tu base de datos (user,pass,params,etc) en el api.php y las rutas en src/services/ApiService.js

Leer el manual de usuario para el uso correcto de la aplicación. Si tiene alguna pregunta o sugerencia sobre la aplicación, pongase en contacto conmigo en la siguiente dirección de correo electrónico: micckey1198@gmail.com
