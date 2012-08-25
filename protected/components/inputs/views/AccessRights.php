<dl>
<?php foreach ($controls as $control) { ?>

    <dt><?php echo $control['label']; ?></dt>
    <dd>
        <?php echo $control['content']; ?>
    </dd>

<?php } ?>
</dl>

<?php if ($instantSave) { ?>
<script type="text/javascript">

    $('.rightsselect').die('change').live('change', function() {
        if ($(this).data('aco_class') != undefined) {

            cmsAjaxSave('/?r=admin/rightsAcoUpdate', {
                'aco_class': $(this).data('aco_class'),
                'aco_key': $(this).data('aco_key'),
                'aco_value': $(this).data('aco_value'),
                'operation': $(this).data('operation'),
                'is_deny': $(this).data('is_deny'),
                'value': $(this).val()
            }, 'GET');

        }

    });

</script>
<?php } ?>