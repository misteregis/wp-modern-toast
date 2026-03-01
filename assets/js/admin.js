jQuery(document).ready(function($) {
    // Color Picker
    $(".modern-toast-color").wpColorPicker({
        palettes: true
    });

    // CodeMirror CSS Editor
    if (typeof wp !== "undefined" && wp.codeEditor) {
        const editorSettings = ModernToastAdmin.codeEditorSettings;

        wp.codeEditor.initialize(
            document.getElementById("modern-toast-custom-css"),
            editorSettings
        );
    }
});