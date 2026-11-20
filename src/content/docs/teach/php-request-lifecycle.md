---
title: "How a PHP Request Works, from Browser to SQLite"
description: "Trace a PHP form submission through the web server, PHP runtime, PDO SQLite, redirect, and HTML response in a runnable reading list."
prev: false
next: false
---

When you click **Add book** in the [PHP and SQLite reading list](/series/php-basics/sqlite-starter/), your browser makes two HTTP requests. The first saves the title. The second gets the updated page.

## Follow the submission

```text
Browser -- POST / --> Development web server
                            |
                            v
                      PHP (index.php)
                            | PDO INSERT
                            v
                       SQLite file
                            |
                      PHP sends 303
                            |
Browser <-- Location: / --- Server

Browser -- GET / ---> Development web server
                            |
                       PHP runs SELECT
                            |
Browser <-- 200 HTML -------+
```

1. The **browser** sends `POST /` with the title, form action, and session token.
2. The **web server** receives the request and invokes `public/index.php`. The local PHP development server handles both roles in one process.
3. The **PHP runtime** checks the submitted token against the session and validates the title. PDO runs an INSERT with the title as a bound parameter. **SQLite** stores the book in a file on disk.
4. After saving, PHP sends **303 See Other** with `Location: /`. The server passes that response to the browser.
5. The **browser** follows with `GET /`. PHP runs a SELECT, escapes the saved titles, and returns **200 OK** with HTML. Refreshing the page repeats the GET.

## Watch it happen

1. Run the app from the [starter instructions](/series/php-basics/sqlite-starter/#download-and-run) and open the reading list at `http://127.0.0.1:8000/`.
2. Open your browser’s developer tools and select **Network**. Enable **Preserve log**, clear the request list, and add a book with a title such as *The PHP Field Guide*.
3. Select the request with method **POST** and status **303**. In its headers, find `Location: /`.
4. Select the following **GET** with status **200**. The response contains the rendered list and your new title.
5. Refresh. Check for another GET and confirm your title appears once.

Try to predict which HTTP request you would inspect if the book never reached the database, or if it was saved but the page displayed it incorrectly. The [PDO lesson](/series/php-basics/chapters/14-interacting-with-databases-using-pdo/) explains the database calls. The [HTTP router lesson](/series/php-basics/chapters/17-building-a-basic-http-router/) shows how a multi-page app chooses different PHP handlers for different requests.
