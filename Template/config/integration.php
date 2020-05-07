<h3><img src="<?= $this->url->dir() ?>plugins/Gotify/Asset/gotify-icon.png"/>&nbsp;Gotify</h3>
<div class="panel">

    <?= $this->form->label(t('Gotify URL'), 'gotify_url') ?>
    <?= $this->form->text('gotify_url', $values) ?>
    <p class="form-help"><?= t('URL of Gotify server') ?></p>

    <?= $this->form->label(t('Gotify Token'), 'gotify_token') ?>
    <?= $this->form->text('gotify_token', $values) ?>
    <p class="form-help"><?= t('Token of Gotify Application') ?></p>

    <?= $this->form->label(t('Gotify Priority'), 'gotify_priority') ?>
    <?= $this->form->text('gotify_priority', $values) ?>
    <p class="form-help"><?= t('Set Notification Priority') ?></p>

    <p class="form-help"><a href="https://github.com/stratmaster/kanboard-plugin-gotify" target="_blank"><?= t('Help on Gotify integration') ?></a> | <a href="https://gotify.net" target="_blank"><?= t('Gotify website') ?></a></p>

    <div class="form-actions">
        <input type="submit" value="<?= t('Save') ?>" class="btn btn-blue">
    </div>
</div>
