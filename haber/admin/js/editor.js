// editor.js
let editors = {};

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all content editors on page load
    document.querySelectorAll('.content-editor').forEach(element => {
        initializeEditor(element.id);
    });
});

function initializeEditor(elementId) {
    // Return if editor already exists or element not found
    if (editors[elementId] || !document.getElementById(elementId)) return;

    ClassicEditor
        .create(document.getElementById(elementId), {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 
                     '|', 'outdent', 'indent', '|', 'blockQuote', 'insertTable', 'undo', 'redo'],
            language: 'tr',
            removePlugins: ['Title'],
            placeholder: 'İçerik giriniz...'
        })
        .then(editor => {
            editors[elementId] = editor;
            
            // Auto-update corresponding hidden input
            editor.model.document.on('change:data', () => {
                const hiddenInput = document.getElementById(`${elementId}Input`);
                if (hiddenInput) {
                    hiddenInput.value = editor.getData();
                }
            });
        })
        .catch(error => {
            console.error(`CKEditor initialization error for ${elementId}:`, error);
        });
}

// Generic helper functions
function getEditorContent(elementId) {
    return editors[elementId] ? editors[elementId].getData() : '';
}

function setEditorContent(elementId, content) {
    if (editors[elementId]) editors[elementId].setData(content);
}

function clearEditorContent(elementId) {
    if (editors[elementId]) editors[elementId].setData('');
}

function destroyEditor(elementId) {
    if (editors[elementId]) {
        editors[elementId].destroy()
            .then(() => delete editors[elementId])
            .catch(error => console.error(`CKEditor destroy error for ${elementId}:`, error));
    }
}