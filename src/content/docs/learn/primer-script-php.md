---
title: "Escribe y ejecuta tu primer script en PHP"
description: "Aprende a escribir tu primer script en PHP y cambia el mensaje para ver el resultado en el navegador."
prev: false
next: false
head:
  - tag: meta
    attrs:
      property: og:locale
      content: es_ES
---

<div lang="es">

Vas a ejecutar un programa corto en PHP y cambiar el saludo que muestra. Para empezar, solo necesitas un navegador.

## Prueba el ejemplo

Abre [el ejemplo en 3v4l](https://3v4l.org/sCEqf). Encontrarás este código en el editor.

```php
<?php

echo 'Hello, World!', PHP_EOL;
```

`<?php` indica dónde empieza el código PHP. `echo` muestra el texto entre comillas y `PHP_EOL` añade un salto de línea. En la pestaña **Output**, debajo del editor, verás el resultado `Hello, World!`.

## Cambia el saludo

1. En el editor, sustituye solo el texto entre comillas por `¡Hola, PHP!`.
2. Selecciona el botón **`eval();`** para ejecutar el código.
3. Comprueba que en **Output** aparece `¡Hola, PHP!`. Prueba con tu nombre o con otro saludo.

Si aparece un error de sintaxis, revisa las dos comillas simples, la coma antes de `PHP_EOL` y el punto y coma al final de la instrucción.

## Ejecuta el script en tu equipo

Si ya tienes PHP instalado, guarda el código con tu saludo en un archivo llamado `hola.php`. Abre una terminal en la carpeta donde guardaste el archivo y ejecuta este comando.

```sh
php hola.php
```

Verás el mismo saludo en la terminal. En [la lección completa en inglés](/series/php-basics/chapters/01-your-first-php-script/) aprenderás a abrir el archivo desde el navegador con el servidor de desarrollo de PHP.

[Comenta la traducción en GitHub](https://github.com/dalehurley/codewithphp/discussions/35) si encuentras una frase que podríamos aclarar.

</div>
