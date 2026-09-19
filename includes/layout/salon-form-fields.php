<?php

declare(strict_types=1);

/**
 * Shared field markup for the Add/Edit salon modals.
 * @var array<string, mixed> $formValues
 * @var array<string, string> $formErrors
 * @var string $fieldIdSuffix unique per modal instance so ids don't collide when several modals are on the page
 */

$fieldIdSuffix ??= '';
?>
<div class="form-grid">
    <div class="form-field">
        <label for="owner_name<?= e($fieldIdSuffix) ?>">Owner Name</label>
        <div class="form-field__control">
            <?= icon('user') ?>
            <input type="text" id="owner_name<?= e($fieldIdSuffix) ?>" name="owner_name" placeholder="e.g. Amina Diriye" value="<?= e($formValues['owner_name'] ?? '') ?>" required>
        </div>
        <?php if (!empty($formErrors['owner_name'])): ?><span class="form-error"><?= e($formErrors['owner_name']) ?></span><?php endif; ?>
    </div>
    <div class="form-field">
        <label for="owner_phone<?= e($fieldIdSuffix) ?>">Owner Number</label>
        <div class="form-field__control">
            <?= icon('phone') ?>
            <input type="tel" id="owner_phone<?= e($fieldIdSuffix) ?>" name="owner_phone" placeholder="+252 6XX XXX XXX" value="<?= e($formValues['owner_phone'] ?? '') ?>" required>
        </div>
        <?php if (!empty($formErrors['owner_phone'])): ?><span class="form-error"><?= e($formErrors['owner_phone']) ?></span><?php endif; ?>
    </div>

    <div class="form-field">
        <label for="email<?= e($fieldIdSuffix) ?>">Owner Email</label>
        <div class="form-field__control">
            <?= icon('mail') ?>
            <input type="email" id="email<?= e($fieldIdSuffix) ?>" name="email" placeholder="owner@example.com" value="<?= e($formValues['email'] ?? '') ?>" required>
        </div>
        <?php if (!empty($formErrors['email'])): ?><span class="form-error"><?= e($formErrors['email']) ?></span><?php endif; ?>
    </div>
    <div class="form-field">
        <label for="name<?= e($fieldIdSuffix) ?>">Saloon Name</label>
        <div class="form-field__control">
            <?= icon('shop') ?>
            <input type="text" id="name<?= e($fieldIdSuffix) ?>" name="name" placeholder="e.g. Bella Hair Studio" value="<?= e($formValues['name'] ?? '') ?>" required>
        </div>
        <?php if (!empty($formErrors['name'])): ?><span class="form-error"><?= e($formErrors['name']) ?></span><?php endif; ?>
    </div>

    <div class="form-field">
        <label for="contact_phone<?= e($fieldIdSuffix) ?>">Saloon Contact</label>
        <div class="form-field__control">
            <?= icon('phone') ?>
            <input type="tel" id="contact_phone<?= e($fieldIdSuffix) ?>" name="contact_phone" placeholder="+252 6XX XXX XXX" value="<?= e($formValues['contact_phone'] ?? '') ?>" required>
        </div>
        <?php if (!empty($formErrors['contact_phone'])): ?><span class="form-error"><?= e($formErrors['contact_phone']) ?></span><?php endif; ?>
    </div>
    <div class="form-field">
        <label for="contact_phone_secondary<?= e($fieldIdSuffix) ?>">Saloon Contact 2 <span class="form-field__optional">(optional)</span></label>
        <div class="form-field__control">
            <?= icon('phone') ?>
            <input type="tel" id="contact_phone_secondary<?= e($fieldIdSuffix) ?>" name="contact_phone_secondary" placeholder="+252 6XX XXX XXX" value="<?= e($formValues['contact_phone_secondary'] ?? '') ?>">
        </div>
    </div>
</div>

<div class="form-field">
    <label for="address<?= e($fieldIdSuffix) ?>">Address</label>
    <div class="form-field__control">
        <?= icon('map-pin') ?>
        <input type="text" id="address<?= e($fieldIdSuffix) ?>" name="address" placeholder="Street, district, city" value="<?= e($formValues['address'] ?? '') ?>" required>
    </div>
    <?php if (!empty($formErrors['address'])): ?><span class="form-error"><?= e($formErrors['address']) ?></span><?php endif; ?>
</div>

<div class="form-field">
    <label for="description<?= e($fieldIdSuffix) ?>">Description <span class="form-field__optional">(optional)</span></label>
    <textarea id="description<?= e($fieldIdSuffix) ?>" name="description" rows="2" placeholder="A short note about this salon..."><?= e($formValues['description'] ?? '') ?></textarea>
</div>

<div class="form-field">
    <label for="logo<?= e($fieldIdSuffix) ?>">Logo <span class="form-field__optional">(optional)</span></label>
    <label class="file-upload" for="logo<?= e($fieldIdSuffix) ?>">
        <?= icon('image') ?>
        <span class="file-upload__text" data-file-upload-text><?= !empty($formValues['logo_path']) ? 'Replace current image · JPG, PNG or WEBP, max 2MB' : 'Choose an image · JPG, PNG or WEBP, max 2MB' ?></span>
    </label>
    <input type="file" id="logo<?= e($fieldIdSuffix) ?>" name="logo" class="file-upload__input" accept="image/png,image/jpeg,image/webp" data-file-upload-input>
    <?php if (!empty($formErrors['logo'])): ?><span class="form-error"><?= e($formErrors['logo']) ?></span><?php endif; ?>
</div>
