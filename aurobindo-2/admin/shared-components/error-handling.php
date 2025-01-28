<?php if (!empty($errors)): ?>
    <div class="alert alert-danger my-3 text-center">
        <p>
            <?php foreach ($errors as $error): ?>
                <?php echo htmlspecialchars($error); ?>
            <?php endforeach; ?>
        </p>
    </div>
<?php endif; ?>