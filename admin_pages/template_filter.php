<?PHP 
global $wpdb;
$filters = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'custom_block_template limit 0,3;'); 
?>
<div class="wrap">
    <h2><?= __('Targeting Templates', 'custom-blocks-free'); ?>
        <a href="admin.php?page=custom-blocks-template-filter&action=update&id=0" class="add-new-h2"><?= __('Add New Template', 'custom-blocks-free'); ?></a>
    </h2>
    <form method="get">
        <input type="hidden" value="custom-blocks-template-filter" name="page">
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <label for="bulk-action-selector-top" class="screen-reader-text"><?= __('Choose a bulk action', 'custom-blocks-free'); ?></label>
                <select name="action" id="bulk-action-selector-top" data-cip-id="cIPJQ342845639">
                    <option value="-1" selected="selected"><?= __('Bulk Actions', 'custom-blocks-free'); ?></option>
                    <option value="delete"><?= __('Delete', 'custom-blocks-free'); ?></option>
                </select>
                <input type="submit" name="" id="doaction" class="button action" value="<?= __('Apply', 'custom-blocks-free'); ?>">
            </div>
        </div>
        <table class="wp-list-table widefat plugins">
            <thead>
                <tr>
                    <th scope="col" id="cb" class="manage-column column-cb check-column" style="">
                        <label class="screen-reader-text" for="cb-select-all-1"><?= __('Select All', 'custom-blocks-free'); ?></label>
                        <input id="cb-select-all-1" type="checkbox">
                    </th>
                    <th scope="col" id="name" class="manage-column column-name" style=""><?= __('Title', 'custom-blocks-free'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?PHP if ($filters) :  ?>
                    <?PHP foreach ($filters as $filter) : ?>
                        <tr id="cuty-code-blocks" class="inactive">
                            <th scope="row" class="check-column">
                                <input type="checkbox" name="id[]" value="<?=$filter->id;?>" id="checkbox_<?=$filter->id;?>">
                            </th>
                            <td class="plugin-title">
                                <strong><b><?=$filter->name; ?></b></strong>
                                <div class="row-actions visible">
                                    <span class="edit"><a href="admin.php?page=custom-blocks-template-filter&action=update&id=<?= $filter->id ?>" title="<?= __('Edit Block', 'custom-blocks-free'); ?>" class="edit"><?= __('Edit', 'custom-blocks-free'); ?></a> | </span>
                                    <span class="delete">
                                        <a onclick="return confirm('<?= __('Are you sure you want to delete this block?', 'custom-blocks-free'); ?>')" href="admin.php?page=custom-blocks-template-filter&action=delete&id[]=<?= $filter->id ?>" title="<?= __('Delete Block', 'custom-blocks-free'); ?>" class="delete">
                                                <?= __('Delete', 'custom-blocks-free'); ?></a> |
                                    </span>
                                    <span class="copy">
                                        <a href="admin.php?page=custom-blocks-template-filter&action=copy&id=<?= $filter->id ?>" title="<?= __('Copy Block', 'custom-blocks-free'); ?>" class="copy">
                                            <?= __('Copy Block', 'custom-blocks-free'); ?>
                                        </a>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    <?PHP endforeach; ?>
                <?PHP endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th scope="col" class="manage-column column-cb check-column" style="">
                        <label class="screen-reader-text" for="cb-select-all-2"><?= __('Select All', 'custom-blocks-free'); ?></label>
                        <input id="cb-select-all-2" type="checkbox">
                    </th>
                    <th scope="col" class="manage-column column-name" style=""><?= __('Title', 'custom-blocks-free'); ?></th>
                </tr>
            </tfoot>
        </table>
    </form>
</div>