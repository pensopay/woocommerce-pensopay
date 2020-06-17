<div class="wrap">
    <h2><?= __('PensoPay Virtual Terminal', 'woo-pensopay') ?></h2>
    <a href="/wp-admin/post-new.php?post_type=<?= WC_PensoPay_VirtualTerminal_Payment::POST_TYPE ?>"
       class="page-title-action"><?= __('Create Payment', 'woo-pensopay') ?></a>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <form method="post">
                        <?php
                        $this->prepare_items();
                        $this->display();
                        ?>
                    </form>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
</div>