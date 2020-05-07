<h3><img src="<?= $this->url->dir() ?>plugins/Gotify/Asset/gotify-icon.png"/>&nbsp;Gotify</h3>
<div class="panel">

    <p class="form-help"><a href="https://gotify.net" target="_blank"><?= t('Help on Gotify integration') ?></a></p>

    <?= $this->form->label(t('Gotify URL'), 'gotify_url') ?>
    <?= $this->form->text('gotify_url', $values) ?>
    <p class="form-help"><?= t('Gotify URL') ?></p>

    <?= $this->form->label(t('Gotify Token'), 'gotify_token') ?>
    <?= $this->form->text('gotify_token', $values) ?>
    <p class="form-help"><?= t('Gotify Token') ?></p>

    <?= $this->form->label(t('Gotify Priority'), 'gotify_priority') ?>
    <?= $this->form->text('gotify_priority', $values) ?>
    <p class="form-help"><?= t('Gotify Priority') ?></p>

    <p class="form-help"><a href="https://github.com/stratmaster/kanboard-plugin-gotify" target="_blank"><?= t('Help on Gotify integration') ?></a></p>

    <div class="form-actions">
        <input type="submit" value="<?= t('Save') ?>" class="btn btn-blue">
    </div>
</div>
