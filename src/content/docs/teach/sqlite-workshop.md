---
title: "45-Minute PHP and SQLite Workshop"
description: "Teach a local PHP and SQLite reading list in 45 minutes. Includes a preflight check, timed teaching plan, learner exercises, and solutions."
prev: false
next: false
---

In 45 minutes, learners run a reading list, follow one form submission through PHP and SQLite, and change how the app lists books. They should be able to edit a file, open a terminal, and follow a short command. The workshop uses one local PHP file and a browser.

## Prepare before the session

Send learners the [PHP and SQLite setup instructions](/series/php-basics/sqlite-starter/#what-you-need) and ask them to run the starter before the session. Try the same steps on a machine with the PHP installation you’ll demonstrate.

- Check the driver with `php -r 'print_r(PDO::getAvailableDrivers());'` and look for `sqlite`.
- Start the app with `php -S 127.0.0.1:8000 -t public` from the starter project folder. Open `http://127.0.0.1:8000/`.
- Keep a copy of the starter file for pairs who need to reset their code. To give a pair a blank reading list, save the file as `public/index.php` in a new project folder and start the server from there.
- Let people with a setup blocker work in pairs. The instructor can follow the [missing-driver guide](/series/php-basics/troubleshooting/#pdo-cannot-find-the-database-driver) after the session.

## Run the lesson

| Time | Activity | What learners should see |
| --- | --- | --- |
| 0–5 min | Run the starter and open the page. | The reading list page loads. |
| 5–12 min | Add *First Book* and *Second Book*. Mark *Second Book* as read. | The list shows both books. *Second Book* has a **(Read)** label. |
| 12–22 min | Follow [the request diagram](/teach/php-request-lifecycle/). Add a third title with Network open. | The POST returns 303, then the GET returns 200 and renders the title. |
| 22–34 min | Complete exercise 1 below. Compare results in pairs. | Unread titles come first, then read titles. |
| 34–42 min | Complete exercise 2 or explain the query change to a partner. | A visible count matches the unread titles. |
| 42–45 min | Stop and restart the server. Refresh and share one sentence about what SQLite stores. | Books and their read status persist. |

## Exercise 1 — Put unread books first

Find the query containing `ORDER BY id DESC` near the end of the PHP block. Keep all the rows, but change the order so unread books come before read books. Keep newest first inside each group.

Mark the newest book as read to check your result. It moves below the unread books and keeps its **(Read)** label.

<details>
<summary>Show the solution</summary>

```sql
SELECT id, title, is_read FROM books ORDER BY is_read ASC, id DESC
```

SQLite stores `0` for unread and `1` for read in this app. Sorting by `is_read` puts the zeroes first. `id DESC` orders each group separately.

</details>

## Exercise 2 — Count unread books

Find the line that assigns `$books`. After that line, count books with `is_read` equal to zero. In the HTML, display the count below the **Books** heading.

Check the count by marking an unread book as read. The count falls by one. When all books are read, it shows zero.

<details>
<summary>Show the solution</summary>

Add the loop after the SELECT.

```php
$unread = 0;
foreach ($books as $book) {
    if ((int) $book['is_read'] === 0) {
        $unread++;
    }
}
```

Print the count below the `<h2>Books</h2>` heading.

```php
<p>Unread books: <?= $unread ?></p>
```

</details>

## Check understanding

Ask each learner to point to the form, the INSERT, the SELECT, and the part of the HTML that prints a title. Then ask which request saves a change and which displays it. A complete answer names the POST and its redirect, the SQLite file, and the follow-up GET. Learners who finish early can [choose a next project](/teach/next-php-projects/).
