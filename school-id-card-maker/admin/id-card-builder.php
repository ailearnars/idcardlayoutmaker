<?php
if (!defined('ABSPATH')) {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_custom_template'])) {
    if (!isset($_POST['builder_nonce']) || !wp_verify_nonce($_POST['builder_nonce'], 'save_builder_template')) {
        wp_die('Security check failed');
    }

    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    $orientation = sanitize_text_field($_POST['orientation']);
    if (!in_array($orientation, ['horizontal', 'vertical'])) {
        wp_die('Invalid orientation.');
    }

    // Because this is a builder that inherently requires inline positional styles
    // we use a custom ksés array or check unfiltered_html.
    $raw_html = wp_unslash($_POST['template_html']);

    if (current_user_can('unfiltered_html')) {
        $html_content = $raw_html;
    } else {
        $allowed_html = wp_kses_allowed_html('post');
        $allowed_html['div']['style'] = true;
        $allowed_html['span']['style'] = true;
        $allowed_html['p']['style'] = true;
        $allowed_html['h1']['style'] = true;
        $allowed_html['h2']['style'] = true;
        $allowed_html['h3']['style'] = true;
        $allowed_html['img']['style'] = true;
        $html_content = wp_kses($raw_html, $allowed_html);
    }

    $template_id = 'custom-' . time();

    // Wrap with the ID Card container required by DOMPDF CSS
    $full_html = '<div class="id-card ' . $orientation . ' ' . $template_id . '" style="position: relative; background: #fff;">' . $html_content . '</div>';

    // Save as option to avoid arbitrary file writing / RCE vulnerabilities
    $custom_templates = get_option('school_id_card_custom_templates', array());
    if (!is_array($custom_templates)) {
        $custom_templates = array();
    }

    $custom_templates[$template_id] = array(
        'id' => $template_id,
        'orientation' => $orientation,
        'html' => $full_html
    );

    update_option('school_id_card_custom_templates', $custom_templates);

    echo '<div class="saas-notice saas-notice-success"><p>Custom Template saved successfully! It is now available in the generator.</p></div>';
}
?>

<style>
    /* Canva-like Editor Layout */
    .builder-layout {
        display: grid;
        grid-template-columns: 280px 1fr 300px;
        gap: 0;
        height: calc(100vh - 150px);
        min-height: 600px;
        background: var(--saas-bg);
        border: 1px solid var(--saas-border);
        border-radius: var(--saas-radius);
        overflow: visible;
        margin-top: 20px;
        box-shadow: var(--saas-shadow);
    }

    /* Left Sidebar: Tools & Elements */
    .builder-sidebar-left {
        background: #ffffff;
        border-right: 1px solid var(--saas-border);
        display: flex;
        flex-direction: column;
    }

    .sidebar-header {
        padding: 16px;
        border-bottom: 1px solid var(--saas-border);
        background: #f9fafb;
    }

    .sidebar-header h3 { margin: 0; font-size: 14px; font-weight: 600; color: #111827; }

    .elements-list {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .tool-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        text-align: left;
        background: #fff;
        border: 1px solid var(--saas-border);
        padding: 10px 12px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.15s ease;
        font-family: inherit;
        font-size: 13px;
        color: #374151;
    }

    .tool-btn:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .tool-btn .dashicons {
        color: var(--saas-primary);
        font-size: 18px;
        width: 18px;
        height: 18px;
    }

    /* Center: Canvas Area */
    .builder-workspace {
        background: #e5e7eb;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        overflow: auto;
        background-image: linear-gradient(45deg, #d1d5db 25%, transparent 25%), linear-gradient(-45deg, #d1d5db 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #d1d5db 75%), linear-gradient(-45deg, transparent 75%, #d1d5db 75%);
        background-size: 20px 20px;
        background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
    }

    .builder-canvas {
        background: #fff;
        position: relative;
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
        overflow: visible;
        transition: width 0.3s ease, height 0.3s ease;
    }

    .builder-canvas.horizontal { width: 350px; height: 220px; }
    .builder-canvas.vertical { width: 220px; height: 350px; }

    .draggable-element {
        position: absolute;
        cursor: grab;
        padding: 5px;
        border: 2px solid transparent;
        user-select: none;
        line-height: 1.2;
    }

    .draggable-element:active { cursor: grabbing; }
    .draggable-element:hover { border-color: rgba(79, 70, 229, 0.4); }

    .draggable-element.selected {
        border-color: var(--saas-primary);
        background: rgba(79, 70, 229, 0.05);
    }

    /* Right Sidebar: Properties & Settings */
    .builder-sidebar-right {
        background: #ffffff;
        border-left: 1px solid var(--saas-border);
        display: flex;
        flex-direction: column;
    }

    .properties-panel {
        padding: 20px;
        flex: 1;
        overflow-y: auto;
    }

    .properties-panel h3 {
        margin-top: 0;
        font-size: 14px;
        border-bottom: 1px solid var(--saas-border);
        padding-bottom: 12px;
        margin-bottom: 20px;
        color: #111827;
    }

    .no-selection {
        color: #6B7280;
        text-align: center;
        padding: 40px 20px;
        font-size: 13px;
        background: #f9fafb;
        border-radius: 8px;
        border: 1px dashed #d1d5db;
        margin-bottom: 20px;
    }

    .element-settings { display: none; }
    .element-settings.active { display: block; }

    .save-panel {
        padding: 20px;
        background: #f9fafb;
        border-top: 1px solid var(--saas-border);
    }
</style>

<div class="wrap saas-wrap">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h1 style="border: none; margin: 0; padding: 0;">
            ID Card Designer
            <span style="font-size:11px; vertical-align: middle; font-weight:600; background:var(--saas-primary); color:#fff; padding:3px 8px; border-radius:12px; margin-left: 8px;">Pro Builder</span>
        </h1>
        <a href="?page=school-id-card-maker-templates" class="saas-btn saas-btn-secondary">Library</a>
    </div>

    <div class="builder-layout">
        <!-- Left Sidebar: Elements -->
        <div class="builder-sidebar-left">
            <div class="sidebar-header">
                <h3>Drag & Drop Elements</h3>
            </div>
            <div class="elements-list">
                <button type="button" class="tool-btn" onclick="addTextElement('School Name', '{{SCHOOL_NAME}}', 16, 'bold', '#000000')"><span class="dashicons dashicons-building"></span> School Header</button>
                <button type="button" class="tool-btn" onclick="addTextElement('School Address', '{{SCHOOL_ADDRESS}}', 10, 'normal', '#333333')"><span class="dashicons dashicons-location"></span> School Address</button>
                <button type="button" class="tool-btn" onclick="addTextElement('School Contact', '{{SCHOOL_CONTACT}} | {{SCHOOL_EMAIL}}', 10, 'normal', '#333333')"><span class="dashicons dashicons-phone"></span> School Contact</button>
                <button type="button" class="tool-btn" onclick="addPhotoElement()"><span class="dashicons dashicons-format-image"></span> Student Photo</button>
                <button type="button" class="tool-btn" onclick="addSignatureElement()"><span class="dashicons dashicons-welcome-write-blog"></span> Principal Signature</button>
                <button type="button" class="tool-btn" onclick="addTextElement('Student Name', '{{STUDENT_NAME}}', 18, 'bold', '#4F46E5')"><span class="dashicons dashicons-admin-users"></span> Student Name</button>
                <button type="button" class="tool-btn" onclick="addTextElement('Class Info', 'Class: {{CLASS_INFO}}', 12, 'normal', '#333333')"><span class="dashicons dashicons-welcome-learn-more"></span> Class</button>
                <button type="button" class="tool-btn" onclick="addTextElement('Roll No Info', 'Roll No: {{ROLL_NO}}', 12, 'normal', '#333333')"><span class="dashicons dashicons-editor-ol"></span> Roll No</button>
                <button type="button" class="tool-btn" onclick="addTextElement('DOB Info', 'DOB: {{DOB}}', 12, 'normal', '#333333')"><span class="dashicons dashicons-calendar-alt"></span> Date of Birth</button>
                <button type="button" class="tool-btn" onclick="addTextElement('Blood Group', 'Blood Group: {{BLOOD_GROUP}}', 12, 'normal', '#333333')"><span class="dashicons dashicons-heart"></span> Blood Group</button>
                <button type="button" class="tool-btn" onclick="addTextElement('Admission No', 'Admission No: {{ADMISSION_NO}}', 12, 'normal', '#333333')"><span class="dashicons dashicons-id"></span> Admission No</button>
                <button type="button" class="tool-btn" onclick="addTextElement('Father Name', 'Father: {{FATHER_NAME}}', 12, 'normal', '#333333')"><span class="dashicons dashicons-groups"></span> Father Name</button>
                <button type="button" class="tool-btn" onclick="addTextElement('Mother Name', 'Mother: {{MOTHER_NAME}}', 12, 'normal', '#333333')"><span class="dashicons dashicons-groups"></span> Mother Name</button>
                <button type="button" class="tool-btn" onclick="addTextElement('Phone Number', 'Phone: {{PHONE}}', 12, 'normal', '#333333')"><span class="dashicons dashicons-smartphone"></span> Phone</button>
                <button type="button" class="tool-btn" onclick="addTextElement('Student Address', '{{ADDRESS}}', 10, 'normal', '#333333')"><span class="dashicons dashicons-location-alt"></span> Home Address</button>
                <button type="button" class="tool-btn" onclick="addTextElement('Custom Text', 'Custom Text Here', 12, 'normal', '#000000')"><span class="dashicons dashicons-editor-textcolor"></span> Static Text</button>
            </div>
        </div>

        <!-- Center: Workspace -->
        <div class="builder-workspace" id="workspace">
            <div id="canvas" class="builder-canvas horizontal" style="background-color: #ffffff;">
                <!-- Droppable elements -->
            </div>
        </div>

        <!-- Right Sidebar: Properties -->
        <div class="builder-sidebar-right">
            <div class="properties-panel">
                <h3>Global Layout</h3>
                <div class="saas-form-group">
                    <label>Orientation</label>
                    <select id="canvas-orientation" class="saas-select" onchange="changeOrientation()">
                        <option value="horizontal">Horizontal Card</option>
                        <option value="vertical">Vertical Card</option>
                    </select>
                </div>
                <div class="saas-form-group">
                    <label>Card Background</label>
                    <input type="color" id="canvas-bg" value="#ffffff" onchange="changeCanvasBg()" style="width: 100%; height: 40px; padding: 2px; border: 1px solid var(--saas-border); border-radius: 4px; cursor: pointer;">
                </div>

                <div style="margin: 24px 0; border-top: 1px solid var(--saas-border);"></div>

                <h3>Element Settings</h3>

                <div id="no-selection-msg" class="no-selection">
                    <span class="dashicons dashicons-edit-large" style="font-size: 24px; width: 24px; height: 24px; color: #9CA3AF; margin-bottom: 8px;"></span><br>
                    Select an element on the canvas to edit its properties.
                </div>

                <div id="properties-panel" class="element-settings">
                    <div class="saas-form-group">
                        <label>Font Size (px)</label>
                        <input type="number" id="prop-fontsize" class="saas-input" oninput="updateElement()">
                    </div>
                    <div class="saas-form-group">
                        <label>Text Color</label>
                        <input type="color" id="prop-color" onchange="updateElement()" style="width: 100%; height: 40px; padding: 2px; border: 1px solid var(--saas-border); border-radius: 4px; cursor: pointer;">
                    </div>
                    <button type="button" class="saas-btn saas-btn-danger" style="width:100%; margin-top: 10px;" onclick="deleteSelectedElement()">
                        <span class="dashicons dashicons-trash" style="margin-right: 5px;"></span> Delete Element
                    </button>
                </div>
            </div>

            <div class="save-panel">
                <form method="post" id="save-template-form" onsubmit="prepareTemplateSave()">
                    <?php wp_nonce_field('save_builder_template', 'builder_nonce'); ?>
                    <input type="hidden" name="orientation" id="save-orientation" value="horizontal">
                    <input type="hidden" name="template_html" id="save-html" value="">
                    <button type="submit" name="save_custom_template" class="saas-btn saas-btn-primary" style="width:100%; padding: 12px; font-size: 15px;">
                        <span class="dashicons dashicons-cloud-saved" style="margin-right: 8px;"></span> Save & Publish
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let selectedElement = null;
    let zIndexCounter = 10;

    function changeOrientation() {
        const val = document.getElementById('canvas-orientation').value;
        const canvas = document.getElementById('canvas');
        document.getElementById('save-orientation').value = val;

        if (val === 'horizontal') {
            canvas.className = 'builder-canvas horizontal';
        } else {
            canvas.className = 'builder-canvas vertical';
        }
    }

    function changeCanvasBg() {
        document.getElementById('canvas').style.backgroundColor = document.getElementById('canvas-bg').value;
    }

    function addTextElement(label, content, fontSize, fontWeight, color) {
        const el = document.createElement('div');
        el.className = 'draggable-element text-element';
        el.style.left = '20px';
        el.style.top = '20px';
        el.style.fontSize = fontSize + 'px';
        el.style.fontWeight = fontWeight;
        el.style.color = color;
        el.style.zIndex = zIndexCounter++;
        el.innerHTML = content;

        makeDraggable(el);
        document.getElementById('canvas').appendChild(el);
        selectElement(el);
    }

    function addPhotoElement() {
        const el = document.createElement('div');
        el.className = 'draggable-element photo-element';
        el.style.left = '20px';
        el.style.top = '50px';
        el.style.width = '80px';
        el.style.height = '100px';
        el.style.backgroundColor = '#eeeeee';
        el.style.border = '2px solid #dddddd';
        el.style.zIndex = zIndexCounter++;

        // Safe string placeholder for replacing later
        el.innerHTML = `{{STUDENT_PHOTO}}`;

        makeDraggable(el);
        document.getElementById('canvas').appendChild(el);
        selectElement(el);
    }

    function addSignatureElement() {
        const el = document.createElement('div');
        el.className = 'draggable-element signature-element';
        el.style.left = '100px';
        el.style.top = '150px';
        el.style.width = '80px';
        el.style.height = '30px';
        el.style.backgroundColor = '#f0fdf4';
        el.style.border = '2px dashed #4ade80';
        el.style.zIndex = zIndexCounter++;
        el.style.display = 'flex';
        el.style.alignItems = 'center';
        el.style.justifyContent = 'center';
        el.style.fontSize = '10px';
        el.style.color = '#166534';

        // Safe string placeholder for replacing later
        el.innerHTML = `{{PRINCIPAL_SIGNATURE}}`;

        makeDraggable(el);
        document.getElementById('canvas').appendChild(el);
        selectElement(el);
    }

    function selectElement(el) {
        if (selectedElement) {
            selectedElement.classList.remove('selected');
        }
        selectedElement = el;
        el.classList.add('selected');

        const props = document.getElementById('properties-panel');
        const noSelection = document.getElementById('no-selection-msg');

        if (el.classList.contains('text-element')) {
            noSelection.style.display = 'none';
            props.classList.add('active');
            document.getElementById('prop-fontsize').value = parseInt(window.getComputedStyle(el).fontSize);

            // Convert rgb to hex for color picker
            const rgb = window.getComputedStyle(el).color;
            document.getElementById('prop-color').value = rgbToHex(rgb);
        } else if (el.classList.contains('photo-element') || el.classList.contains('signature-element')) {
            noSelection.style.display = 'none';
            props.classList.remove('active');
        } else {
            noSelection.style.display = 'block';
            props.classList.remove('active');
        }
    }

    function updateElement() {
        if (!selectedElement || !selectedElement.classList.contains('text-element')) return;
        selectedElement.style.fontSize = document.getElementById('prop-fontsize').value + 'px';
        selectedElement.style.color = document.getElementById('prop-color').value;
    }

    function deleteSelectedElement() {
        if (selectedElement) {
            selectedElement.remove();
            selectedElement = null;
            document.getElementById('properties-panel').classList.remove('active');
        }
    }

    function makeDraggable(elmnt) {
        let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;

        elmnt.onmousedown = dragMouseDown;

        function dragMouseDown(e) {
            e = e || window.event;
            e.preventDefault();
            selectElement(elmnt);

            pos3 = e.clientX;
            pos4 = e.clientY;
            document.onmouseup = closeDragElement;
            document.onmousemove = elementDrag;
        }

        function elementDrag(e) {
            e = e || window.event;
            e.preventDefault();
            pos1 = pos3 - e.clientX;
            pos2 = pos4 - e.clientY;
            pos3 = e.clientX;
            pos4 = e.clientY;

            elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
            elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
        }

        function closeDragElement() {
            document.onmouseup = null;
            document.onmousemove = null;
        }
    }

    // Helper: rgb(0,0,0) to #000000
    function rgbToHex(rgb) {
        let match = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
        if (!match) return '#000000';
        function hex(x) { return ("0" + parseInt(x).toString(16)).slice(-2); }
        return "#" + hex(match[1]) + hex(match[2]) + hex(match[3]);
    }

    function prepareTemplateSave() {
        if (selectedElement) selectedElement.classList.remove('selected'); // remove borders before save
        const canvas = document.getElementById('canvas');

        // We clone it so we can strip out UI builder specific classes without affecting the UI
        const clone = canvas.cloneNode(true);
        clone.style.boxShadow = 'none'; // remove shadow for final PDF export compat

        const elements = clone.querySelectorAll('.draggable-element');
        elements.forEach(el => {
            el.classList.remove('draggable-element', 'text-element', 'photo-element', 'signature-element', 'selected');
            el.style.border = 'none';
            el.style.cursor = 'default';
            el.style.backgroundColor = 'transparent';
        });

        document.getElementById('save-html').value = clone.innerHTML;
    }

    // Deselect if clicking canvas directly
    document.getElementById('workspace').addEventListener('mousedown', function(e) {
        if(e.target === this || e.target === document.getElementById('canvas')) {
            if (selectedElement) selectedElement.classList.remove('selected');
            selectedElement = null;
            document.getElementById('properties-panel').classList.remove('active');
            document.getElementById('no-selection-msg').style.display = 'block';
        }
    });
</script>
