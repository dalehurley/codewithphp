<h1><?= htmlspecialchars($title) ?></h1>

<form action="/posts/<?= $post['id'] ?>/update" method="POST" style="max-width: 600px;">
    <div style="margin-bottom: 1rem;">
        <label for="title" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Title:</label>
        <input type="text" id="title" name="title" value="<?= htmlspecialchars($post['title']) ?>" required
            style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
    </div>

    <div style="margin-bottom: 1rem;">
        <label for="content" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Content:</label>
        <textarea id="content" name="content" rows="10" required
            style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; resize: vertical;"><?= htmlspecialchars($post['content']) ?></textarea>
    </div>

    <div>
        <button type="submit" class="btn">Update Post</button>
        <a href="/posts/<?= $post['id'] ?>" class="btn" style="background: #95a5a6;">Cancel</a>
    </div>
</form>