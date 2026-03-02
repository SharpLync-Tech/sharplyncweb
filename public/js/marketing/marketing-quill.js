/* public/js/marketing/marketing-quill.js */
/* SharpLync Marketing Quill Setup */

(function () {
    var editorEl = document.getElementById('marketing-quill-editor');
    var toolbarEl = document.getElementById('marketing-quill-toolbar');
    var hiddenInput = document.getElementById('body_html');

    if (!editorEl || !toolbarEl || !hiddenInput || typeof Quill === 'undefined') {
        return;
    }

    var quill = new Quill('#marketing-quill-editor', {
        theme: 'snow',
        modules: {
            toolbar: '#marketing-quill-toolbar'
        }
    });

    if (hiddenInput.value) {
        quill.root.innerHTML = hiddenInput.value;
    }

    quill.on('text-change', function () {
        hiddenInput.value = quill.root.innerHTML;
    });

    function uploadImage(file) {
        var formData = new FormData();
        formData.append('image', file);

        var token = document.querySelector('meta[name=\"csrf-token\"]');
        var csrf = token ? token.getAttribute('content') : '';

        return fetch('/marketing/admin/uploads', {
            method: 'POST',
            headers: csrf ? { 'X-CSRF-TOKEN': csrf } : {},
            body: formData
        })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (!data || !data.url) {
                throw new Error('Upload failed');
            }
            var range = quill.getSelection(true);
            quill.insertEmbed(range.index, 'image', data.url);
        });
    }

    var toolbar = quill.getModule('toolbar');
    toolbar.addHandler('image', function () {
        var input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.click();

        input.onchange = function () {
            var file = input.files && input.files[0];
            if (file) {
                uploadImage(file);
            }
        };
    });

    quill.root.addEventListener('drop', function (event) {
        if (!event.dataTransfer || !event.dataTransfer.files || event.dataTransfer.files.length === 0) {
            return;
        }
        event.preventDefault();
        var file = event.dataTransfer.files[0];
        if (file && file.type && file.type.indexOf('image/') === 0) {
            uploadImage(file);
        }
    }, false);
})();
