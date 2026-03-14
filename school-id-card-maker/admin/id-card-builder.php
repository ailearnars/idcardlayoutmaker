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
    .builder-container {
        display: flex;
        gap: 24px;
        margin-top: 24px;
    }
    .builder-sidebar {
        width: 300px;
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid var(--saas-border);
        box-shadow: var(--saas-shadow);
    }
    .builder-canvas-wrapper {
        flex: 1;
        background: #e5e7eb;
        padding: 40px;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        border-radius: 8px;
        border: 1px solid var(--saas-border);
        overflow: auto;
    }
    .builder-canvas {
        background: #fff;
        position: relative;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .builder-canvas.horizontal {
        width: 350px;
        height: 220px;
    }
    .builder-canvas.vertical {
        width: 220px;
        height: 350px;
    }
    .draggable-element {
        position: absolute;
        cursor: grab;
        padding: 5px;
        border: 1px dashed transparent;
        user-select: none;
    }
    .draggable-element:hover {
        border-color: #4F46E5;
    }
    .draggable-element.selected {
        border-color: #4F46E5;
        background: rgba(79, 70, 229, 0.05);
    }
    .builder-tool-group {
        margin-bottom: 20px;
    }
    .builder-tool-group h3 {
        margin-top: 0;
        font-size: 14px;
        border-bottom: 1px solid var(--saas-border);
        padding-bottom: 8px;
        margin-bottom: 12px;
    }
    .tool-btn {
        display: block;
        width: 100%;
        margin-bottom: 8px;
        text-align: left;
        background: #f9fafb;
        border: 1px solid var(--saas-border);
        padding: 8px 12px;
        border-radius: 4px;
        cursor: pointer;
    }
    .tool-btn:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
    }
    .properties-panel {
        display: none;
        background: #f9fafb;
        padding: 12px;
        border-radius: 4px;
        border: 1px solid var(--saas-border);
        margin-top: 20px;
    }
    .properties-panel.active {
        display: block;
    }
</style>

<div class="wrap saas-wrap">
    <h1>ID Card Builder <span style="font-size:12px; font-weight:normal; background:#4F46E5; color:#fff; padding:2px 8px; border-radius:12px;">Beta</span></h1>
    <p>Design your own custom template using drag and drop. Once saved, it will be available in the template library.</p>

    <div class="builder-container">
        <!-- Sidebar -->
        <div class="builder-sidebar">
            <div class="builder-tool-group">
                <h3>Card Setup</h3>
                <div class="saas-form-group">
                    <label>Orientation</label>
                    <select id="canvas-orientation" class="saas-select" onchange="changeOrientation()">
                        <option value="horizontal">Horizontal (350x220)</option>
                        <option value="vertical">Vertical (220x350)</option>
                    </select>
                </div>
                <div class="saas-form-group">
                    <label>Background Color</label>
                    <input type="color" id="canvas-bg" value="#ffffff" onchange="changeCanvasBg()">
                </div>
            </div>

            <div class="builder-tool-group" style="max-height: 250px; overflow-y: auto; padding-right: 5px;">
                <h3>Add Elements</h3>
                <button type="button" class="tool-btn" onclick="addTextElement('School Name', '{{SCHOOL_NAME}}', 16, 'bold', '#000000')">+ School Name Header</button>
                <button type="button" class="tool-btn" onclick="addTextElement('School Address', '{{SCHOOL_ADDRESS}}', 10, 'normal', '#333333')">+ School Address</button>
                <button type="button" class="tool-btn" onclick="addTextElement('School Contact', '{{SCHOOL_CONTACT}} | {{SCHOOL_EMAIL}}', 10, 'normal', '#333333')">+ School Contact</button>
                <button type="button" class="tool-btn" onclick="addTextElement('Student Name', '{{STUDENT_NAME}}', 18, 'bold', '#4F46E5')">+ Dynamic Student Name</button>
                <button type="button" class="tool-btn" onclick="addTextElement('Class Info', 'Class: {{CLASS_INFO}}', 12, 'normal', '#333333')">+ Dynamic Class</button>
                <button type="button" class="tool-btn" onclick="addTextElement('Roll No Info', 'Roll No: {{ROLL_NO}}', 12, 'normal', '#333333')">+ Dynamic Roll No</button>
                <button type="button" class="tool-btn" onclick="addTextElement('DOB Info', 'DOB: {{DOB}}', 12, 'normal', '#333333')">+ Dynamic DOB</button>
                <button type="button" class="tool-btn" onclick="addTextElement('Blood Group', 'Blood Group: {{BLOOD_GROUP}}', 12, 'normal', '#333333')">+ Dynamic Blood Group</button>
                <button type="button" class="tool-btn" onclick="addTextElement('Admission No', 'Admission No: {{ADMISSION_NO}}', 12, 'normal', '#333333')">+ Dynamic Admission No</button>
                <button type="button" class="tool-btn" onclick="addTextElement('Father Name', 'Father: {{FATHER_NAME}}', 12, 'normal', '#333333')">+ Dynamic Father Name</button>
                <button type="button" class="tool-btn" onclick="addTextElement('Mother Name', 'Mother: {{MOTHER_NAME}}', 12, 'normal', '#333333')">+ Dynamic Mother Name</button>
                <button type="button" class="tool-btn" onclick="addTextElement('Phone Number', 'Phone: {{PHONE}}', 12, 'normal', '#333333')">+ Dynamic Phone</button>
                <button type="button" class="tool-btn" onclick="addTextElement('Student Address', '{{ADDRESS}}', 12, 'normal', '#333333')">+ Dynamic Student Address</button>
                <button type="button" class="tool-btn" onclick="addPhotoElement()">+ Dynamic Student Photo</button>
                <button type="button" class="tool-btn" onclick="addTextElement('Custom Text', 'Custom Text Here', 12, 'normal', '#000000')">+ Static Text</button>
            </div>

            <div class="properties-panel" id="properties-panel">
                <h3>Edit Element</h3>
                <div class="saas-form-group">
                    <label>Font Size (px)</label>
                    <input type="number" id="prop-fontsize" class="saas-input" oninput="updateElement()">
                </div>
                <div class="saas-form-group">
                    <label>Text Color</label>
                    <input type="color" id="prop-color" onchange="updateElement()">
                </div>
                <button type="button" class="saas-btn saas-btn-danger" style="width:100%; margin-top:10px;" onclick="deleteSelectedElement()">Delete Element</button>
            </div>

            <div style="margin-top: 30px;">
                <form method="post" id="save-template-form" onsubmit="prepareTemplateSave()">
                    <?php wp_nonce_field('save_builder_template', 'builder_nonce'); ?>
                    <input type="hidden" name="orientation" id="save-orientation" value="horizontal">
                    <input type="hidden" name="template_html" id="save-html" value="">
                    <button type="submit" name="save_custom_template" class="saas-btn saas-btn-primary" style="width:100%;">Save Template</button>
                </form>
            </div>
        </div>

        <!-- Canvas -->
        <div class="builder-canvas-wrapper">
            <div id="canvas" class="builder-canvas horizontal" style="background-color: #ffffff;">
                <!-- Droppable elements will go here -->
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

    function selectElement(el) {
        if (selectedElement) {
            selectedElement.classList.remove('selected');
        }
        selectedElement = el;
        el.classList.add('selected');

        const props = document.getElementById('properties-panel');
        if (el.classList.contains('text-element')) {
            props.classList.add('active');
            document.getElementById('prop-fontsize').value = parseInt(window.getComputedStyle(el).fontSize);

            // Convert rgb to hex for color picker
            const rgb = window.getComputedStyle(el).color;
            document.getElementById('prop-color').value = rgbToHex(rgb);
        } else {
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
            el.classList.remove('draggable-element', 'text-element', 'photo-element', 'selected');
            el.style.border = 'none';
            el.style.cursor = 'default';
        });

        document.getElementById('save-html').value = clone.innerHTML;
    }

    // Deselect if clicking canvas directly
    document.getElementById('canvas').addEventListener('mousedown', function(e) {
        if(e.target === this) {
            if (selectedElement) selectedElement.classList.remove('selected');
            selectedElement = null;
            document.getElementById('properties-panel').classList.remove('active');
        }
    });
</script>
