<?php
    $errors = get_user_meta(get_current_user_id(), 'errors', true);
    $successful = get_user_meta(get_current_user_id(), 'success', true);
?>


<?php if (!empty($errors)): ?>
    <div class="notice notice-error">
        <ul class="errors">
    <?php foreach ($errors as $postId => $errormsg): ?>
            <li><?= sprintf(__('Error with post %s: %s', 'woo-pensopay'), $postId, $errormsg) ?></li>
    <?php endforeach; ?>
        </ul>
    </div>
    <?php delete_user_meta(get_current_user_id(), 'errors'); ?>
<?php endif; ?>

<?php if (!empty($successful)): ?>
    <div class="notice notice-success"><?= sprintf(__('Successfully ran on %s payments', 'woo-pensopay'), $successful) ?></div>
    <?php delete_user_meta(get_current_user_id(), 'success'); ?>
<?php endif; ?>