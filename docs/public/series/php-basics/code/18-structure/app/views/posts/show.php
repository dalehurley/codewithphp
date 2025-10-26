<article>
    <h1><?= htmlspecialchars($post['title']) ?></h1>

    <p><small>By <?= htmlspecialchars($post['author']) ?> on <?= $post['created_at'] ?></small></p>

    <div style="margin: 2rem 0; line-height: 1.8;">
        <?= nl2br(htmlspecialchars($post['content'])) ?>
    </div>

    <p>
        <a href="/posts" class="btn">Back to Posts</a>
        <a href="/posts/<?= $post['id'] ?>/edit" class="btn">Edit</a>
    <form action="/posts/<?= $post['id'] ?>/delete" method="POST" style="display: inline;">
        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
    </form>
    </p>
</article>