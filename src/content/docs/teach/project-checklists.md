---
title: "PHP Project Finish-Line Checklists"
description: "Check a PHP and SQLite reading list, the PHP Basics blog, and Laravel CRM projects against practical results before moving on."
prev: false
next: false
---

Use the checklist for the project you built. Follow it with sample data in your local learning environment so you can repeat each check.

## PHP and SQLite reading list

Try these checks after completing the [reading list starter](/series/php-basics/sqlite-starter/).

- [ ] Add a book with an ampersand in its title. The list renders the whole title.
- [ ] Submit a title containing only spaces. The form displays its validation message and adds no row.
- [ ] Mark the first book as read. It shows **(Read)** after the page reloads.
- [ ] Restart the server and reopen the list. The title and its read status persist.

## PHP Basics blog

Start with the [Simple Blog project](/series/php-basics/chapters/19-project-building-a-simple-blog/).

- [ ] Open `/posts`, click **Create New Post**, and submit a title and body. The list contains the new post once.
- [ ] Open the title. The detail page at `/posts/{id}` shows the full body. A post number absent from the database returns HTTP 404.
- [ ] Enter a title and a body containing only spaces. The form shows a validation message, keeps your title, and creates no post.
- [ ] Restart the server. The original post still appears and opens from the list.

If you completed the optional [editing and deleting exercises](/series/php-basics/chapters/19-project-building-a-simple-blog/#exercises), edit a title and check that the same detail URL shows the change. Delete that test post and check that it disappears from the list.

## Laravel CRM with React and Inertia

Use the [Laravel CRM course’s main project](/series/build-crm-laravel-12/#crm-modules) and two test teams.

- [ ] Sign in as a member of a test team. Create a company and a contact associated with it. Reopen the company and verify the relationship.
- [ ] Create a deal, move it to another pipeline stage, and reload. It stays in the new stage.
- [ ] Create a task with a due date and mark it complete. Refresh and check that the completed state persists.
- [ ] Switch to another test team. Its contact list only shows that team’s data.

## Laravel CRM API and queue lab

Complete the [contacts API lab](/series/build-crm-laravel-12/chapters/20-building-restful-api-crm/) and [background jobs lesson](/series/build-crm-laravel-12/chapters/29-queues-background-jobs/) first.

- [ ] From the lab directory, run `php artisan test --filter=CrmLabTest`. The API, token, and export checks pass.
- [ ] Queue a demo export and run the local worker for the connection you configured. Open the file at the path printed by `php artisan crm-lab:export` under `storage/app/private/`. It contains the test contacts as JSON.
- [ ] If you continued to [the Redis and Horizon lab](/series/build-crm-laravel-12/chapters/30-monitoring-queues-horizon/), dispatch another export with Horizon running. The export appears under **Completed Jobs** in the local dashboard.

Once your checks pass, [choose a next PHP project](/teach/next-php-projects/).
