<h1><?= htmlspecialchars($title) ?></h1>

<p><a href="/posts/create" class="btn">Create New Post</a></p>

<?php if (empty($posts)): ?>
    <p>No posts found. <a href="/posts/create">Create the first post!</a></p>
<?php else: ?>
    <?php foreach ($posts as $post): ?>
        <article style="border: 1px solid #ddd; padding: 1.5rem; margin: 1rem 0; border-radius: 4px;">
            <h2>
                <a href="/posts/<?= $post['id'] ?>" style="color: #2c3e50; text-decoration: none;">
                    <?= htmlspecialchars($post['title']) ?>
                </a>
            </h2>
            <p><?= htmlspecialchars(substr($post['content'], 0, 200)) ?>...</p>
            <p>
                <small>By <?= htmlspecialchars($post['author']) ?> on <?= $post['created_at'] ?></small>
            </p>
            <p>
                <a href="/posts/<?= $post['id'] ?>" class="btn">Read More</a>
                <a href="/posts/<?= $post['id'] ?>/edit" class="btn">Edit</a>
            </p>
        </article>
    <?php endforeach; ?>
<?php endif; ?>