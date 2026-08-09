<?php
// views/components/FormComponent.php

class FormComponent {
    
    /**
     * Render a text/email/password input field
     */

    public static function renderForm(array $config = []) {
        $action = $config['action'] ?? '';
        $method = strtoupper($config['method'] ?? 'POST');
        $fields = $config['fields'] ?? [];
        $submit = $config['submit'] ?? ['text' => 'Envoyer'];
        $enctype = $config['enctype'] ?? null;
        $class = $config['class'] ?? '';
        
        ob_start();
        ?>
        <form action="<?= htmlspecialchars($action) ?>"
            method="<?= htmlspecialchars($method) ?>"
            <?= $enctype ? 'enctype="'.htmlspecialchars($enctype).'"' : '' ?>
            class="<?= htmlspecialchars($class) ?>">
            
            <?php foreach ($fields as $field): ?>
                <?php
                switch ($field['type']) {
                    case 'textarea':
                        self::renderTextarea($field);
                        break;

                    case 'select':
                        self::renderSelect($field);
                        break;

                    case 'checkbox':
                        self::renderCheckbox($field);
                        break;

                    case 'file':
                        self::renderFile($field);
                        break;

                    default:
                        self::renderInput($field);
                }
                ?>
            <?php endforeach; ?>

            <div class="form-actions" style="margin-top: 24px;">
                <button type="submit" class="btn primary">
                    <?php if (!empty($submit['icon'])): ?>
                        <i class="<?= htmlspecialchars($submit['icon']) ?>"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($submit['text'] ?? 'Envoyer') ?>
                </button>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }
    public static function renderInput(array $config = []) {
        $label = $config['label'] ?? '';
        $name = $config['name'] ?? '';
        $type = $config['type'] ?? 'text';
        $value = $config['value'] ?? '';
        $placeholder = $config['placeholder'] ?? '';
        $icon = $config['icon'] ?? null;
        $required = $config['required'] ?? false;
        $autofocus = $config['autofocus'] ?? false;
        $disabled = $config['disabled'] ?? false;
        $readonly = $config['readonly'] ?? false;
        $class = $config['class'] ?? '';
        $id = $config['id'] ?? $name;
        $help = $config['help'] ?? '';
        $error = $config['error'] ?? '';
        
        ?>
        <div class="form-group <?= $error ? 'has-error' : '' ?>">
            <?php if ($label): ?>
                <label for="<?= htmlspecialchars($id) ?>" class="form-label">
                    <?= htmlspecialchars($label) ?>
                    <?php if ($required): ?>
                        <span class="required">*</span>
                    <?php endif; ?>
                </label>
            <?php endif; ?>
            
            <div class="input-wrapper <?= $icon ? 'has-icon' : '' ?>">
                <?php if ($icon): ?>
                    <i class="<?= htmlspecialchars($icon) ?> input-icon"></i>
                <?php endif; ?>
                
                <input 
                    type="<?= htmlspecialchars($type) ?>"
                    id="<?= htmlspecialchars($id) ?>"
                    name="<?= htmlspecialchars($name) ?>"
                    value="<?= htmlspecialchars($value) ?>"
                    placeholder="<?= htmlspecialchars($placeholder) ?>"
                    class="form-control <?= htmlspecialchars($class) ?>"
                    <?= $required ? 'required' : '' ?>
                    <?= $autofocus ? 'autofocus' : '' ?>
                    <?= $disabled ? 'disabled' : '' ?>
                    <?= $readonly ? 'readonly' : '' ?>
                >
            </div>
            
            <?php if ($help): ?>
                <small class="form-help"><?= htmlspecialchars($help) ?></small>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <small class="form-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </small>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render a textarea field
     */
    public static function renderTextarea(array $config = []) {
        $label = $config['label'] ?? '';
        $name = $config['name'] ?? '';
        $value = $config['value'] ?? '';
        $placeholder = $config['placeholder'] ?? '';
        $rows = $config['rows'] ?? 4;
        $required = $config['required'] ?? false;
        $disabled = $config['disabled'] ?? false;
        $class = $config['class'] ?? '';
        $id = $config['id'] ?? $name;
        $help = $config['help'] ?? '';
        $error = $config['error'] ?? '';
        
        ?>
        <div class="form-group <?= $error ? 'has-error' : '' ?>">
            <?php if ($label): ?>
                <label for="<?= htmlspecialchars($id) ?>" class="form-label">
                    <?= htmlspecialchars($label) ?>
                    <?php if ($required): ?>
                        <span class="required">*</span>
                    <?php endif; ?>
                </label>
            <?php endif; ?>
            
            <textarea
                id="<?= htmlspecialchars($id) ?>"
                name="<?= htmlspecialchars($name) ?>"
                placeholder="<?= htmlspecialchars($placeholder) ?>"
                rows="<?= (int)$rows ?>"
                class="form-control <?= htmlspecialchars($class) ?>"
                <?= $required ? 'required' : '' ?>
                <?= $disabled ? 'disabled' : '' ?>
            ><?= htmlspecialchars($value) ?></textarea>
            
            <?php if ($help): ?>
                <small class="form-help"><?= htmlspecialchars($help) ?></small>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <small class="form-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </small>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render a select dropdown
     */
    public static function renderSelect(array $config = []) {
        $label = $config['label'] ?? '';
        $name = $config['name'] ?? '';
        $options = $config['options'] ?? [];
        $selected = $config['selected'] ?? '';
        $required = $config['required'] ?? false;
        $disabled = $config['disabled'] ?? false;
        $class = $config['class'] ?? '';
        $id = $config['id'] ?? $name;
        $help = $config['help'] ?? '';
        $error = $config['error'] ?? '';
        $placeholder = $config['placeholder'] ?? '-- Sélectionner --';
        
        ?>
        <div class="form-group <?= $error ? 'has-error' : '' ?>">
            <?php if ($label): ?>
                <label for="<?= htmlspecialchars($id) ?>" class="form-label">
                    <?= htmlspecialchars($label) ?>
                    <?php if ($required): ?>
                        <span class="required">*</span>
                    <?php endif; ?>
                </label>
            <?php endif; ?>
            
            <select
                id="<?= htmlspecialchars($id) ?>"
                name="<?= htmlspecialchars($name) ?>"
                class="form-control <?= htmlspecialchars($class) ?>"
                <?= $required ? 'required' : '' ?>
                <?= $disabled ? 'disabled' : '' ?>
            >
                <?php if ($placeholder): ?>
                    <option value=""><?= htmlspecialchars($placeholder) ?></option>
                <?php endif; ?>
                
                <?php foreach ($options as $value => $label): ?>
                    <option 
                        value="<?= htmlspecialchars($value) ?>"
                        <?= $selected == $value ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <?php if ($help): ?>
                <small class="form-help"><?= htmlspecialchars($help) ?></small>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <small class="form-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </small>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render a checkbox
     */
    public static function renderCheckbox(array $config = []) {
        $label = $config['label'] ?? '';
        $name = $config['name'] ?? '';
        $checked = $config['checked'] ?? false;
        $value = $config['value'] ?? '1';
        $disabled = $config['disabled'] ?? false;
        $class = $config['class'] ?? '';
        $id = $config['id'] ?? $name;
        $help = $config['help'] ?? '';
        
        ?>
        <div class="form-group form-check">
            <input 
                type="checkbox"
                id="<?= htmlspecialchars($id) ?>"
                name="<?= htmlspecialchars($name) ?>"
                value="<?= htmlspecialchars($value) ?>"
                class="form-check-input <?= htmlspecialchars($class) ?>"
                <?= $checked ? 'checked' : '' ?>
                <?= $disabled ? 'disabled' : '' ?>
            >
            <label for="<?= htmlspecialchars($id) ?>" class="form-check-label">
                <?= htmlspecialchars($label) ?>
            </label>
            
            <?php if ($help): ?>
                <small class="form-help"><?= htmlspecialchars($help) ?></small>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render a file upload field
     */
    public static function renderFile(array $config = []) {
        $label = $config['label'] ?? '';
        $name = $config['name'] ?? '';
        $accept = $config['accept'] ?? '';
        $required = $config['required'] ?? false;
        $disabled = $config['disabled'] ?? false;
        $class = $config['class'] ?? '';
        $id = $config['id'] ?? $name;
        $help = $config['help'] ?? '';
        $error = $config['error'] ?? '';
        $preview = $config['preview'] ?? null;
        
        ?>
        <div class="form-group <?= $error ? 'has-error' : '' ?>">
            <?php if ($label): ?>
                <label for="<?= htmlspecialchars($id) ?>" class="form-label">
                    <?= htmlspecialchars($label) ?>
                    <?php if ($required): ?>
                        <span class="required">*</span>
                    <?php endif; ?>
                </label>
            <?php endif; ?>
            
            <input 
                type="file"
                id="<?= htmlspecialchars($id) ?>"
                name="<?= htmlspecialchars($name) ?>"
                accept="<?= htmlspecialchars($accept) ?>"
                class="form-control <?= htmlspecialchars($class) ?>"
                <?= $required ? 'required' : '' ?>
                <?= $disabled ? 'disabled' : '' ?>
            >
            
            <?php if ($preview): ?>
                <div class="file-preview">
                    <?php if (is_string($preview)): ?>
                        <img src="<?= htmlspecialchars($preview) ?>" alt="Preview" style="max-width: 100px; max-height: 100px; margin-top: 8px;">
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($help): ?>
                <small class="form-help"><?= htmlspecialchars($help) ?></small>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <small class="form-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </small>
            <?php endif; ?>
        </div>
        <?php
    }
}
?>