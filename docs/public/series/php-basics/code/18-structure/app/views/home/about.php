<h1><?= htmlspecialchars($title) ?></h1>
<p><?= htmlspecialchars($content) ?></p>

<h2>Project Structure</h2>
<pre>
project/
├── app/
│   ├── Controllers/    # Request handlers
│   ├── Models/         # Database interaction
│   ├── views/          # HTML templates
│   └── Router.php      # URL routing
├── config/
│   └── config.php      # Configuration
├── public/
│   └── index.php       # Front controller
└── routes.php          # Route definitions
</pre>

<h2>Key Concepts</h2>
<ul>
    <li><strong>Model:</strong> Handles data and business logic</li>
    <li><strong>View:</strong> Presents data to the user</li>
    <li><strong>Controller:</strong> Coordinates between Model and View</li>
</ul>