/* public/js/support/support-quill.js */
/* SharpLync – Customer Support Quill Setup */

// -----------------------------------------------------
// 1. Load custom SharpLync SVG icons (optional override)
// Icons must exist in the DOM (inserted via icons.svg include)
// -----------------------------------------------------
try {
    const QuillIcons = Quill.import('ui/icons');

    QuillIcons.bold       = document.querySelector('#ql-icon-bold')      || QuillIcons.bold;
    QuillIcons.italic     = document.querySelector('#ql-icon-italic')    || QuillIcons.italic;
    QuillIcons.underline  = document.querySelector('#ql-icon-underline') || QuillIcons.underline;
    QuillIcons.strike     = document.querySelector('#ql-icon-strike')    || QuillIcons.strike;

    QuillIcons.blockquote = document.querySelector('#ql-icon-blockquote') || QuillIcons.blockquote;
    QuillIcons['code-block'] = document.querySelector('#ql-icon-code-block') || QuillIcons['code-block'];

    QuillIcons.header     = document.querySelector('#ql-icon-header')     || QuillIcons.header;

    QuillIcons.list = QuillIcons.list || {};
    QuillIcons.list.ordered = document.querySelector('#ql-icon-list-ordered') || QuillIcons.list.ordered;
    QuillIcons.list.bullet  = document.querySelector('#ql-icon-list-bullet')  || QuillIcons.list.bullet;

    QuillIcons.link    = document.querySelector('#ql-icon-link')    || QuillIcons.link;
    QuillIcons.image   = document.querySelector('#ql-icon-image')   || QuillIcons.image;
    QuillIcons.video   = document.querySelector('#ql-icon-video')   || QuillIcons.video;
    QuillIcons.formula = document.querySelector('#ql-icon-formula') || QuillIcons.formula;
    QuillIcons.clean   = document.querySelector('#ql-icon-clean')   || QuillIcons.clean;

    QuillIcons.color      = document.querySelector('#ql-icon-color')      || QuillIcons.color;
    QuillIcons.background = document.querySelector('#ql-icon-background') || QuillIcons.background;

} catch (e) {
    console.warn("Quill icons could not be customised:", e);
}

// -----------------------------------------------------
// 2. Initialise Quill Editor
// -----------------------------------------------------
var quill = new Quill('#quill-editor', {
    theme: 'snow',
    modules: {
        toolbar: '#quill-toolbar',
        "emoji-toolbar": true,
        "emoji-textarea": false,
        "emoji-shortname": true,
    }
});

// -----------------------------------------------------
// 3. Sync Quill HTML output into hidden <textarea>
// -----------------------------------------------------
var hiddenInput = document.querySelector('#message');

quill.on('text-change', function () {
    hiddenInput.value = quill.root.innerHTML;
});
