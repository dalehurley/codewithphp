<h1><?= htmlspecialchars($title) ?></h1>

<div style="background: #f8f9fa; padding: 2rem; border-radius: 8px;">
    <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    <p><strong>Joined:</strong> <?= $user['created_at'] ?? 'Unknown' ?></p>
</div>

<p style="margin-top: 1rem;">
    <a href="/" class="btn">Back to Home</a>
</p>