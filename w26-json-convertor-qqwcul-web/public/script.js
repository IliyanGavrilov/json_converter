function getModeForFormat(format) {
    var modes = {
        'json':       'application/json',
        'yaml':       'text/x-yaml',
        'xml':        'application/xml',
        'properties': 'text/x-properties',
        'csv':        'text/plain',
        'ini':        'text/x-ini'
    };
    return modes[format] || 'text/plain';
}

function toRoman(num) {
    var vals = [1000,900,500,400,100,90,50,40,10,9,5,4,1];
    var syms = ['M','CM','D','CD','C','XC','L','XL','X','IX','V','IV','I'];
    var result = '';
    for (var i = 0; i < vals.length; i++) {
        while (num >= vals[i]) { result += syms[i]; num -= vals[i]; }
    }
    return result;
}

var inputEditor = CodeMirror.fromTextArea(document.getElementById('input_content'), {
    lineNumbers:  true,
    mode:         getModeForFormat(document.getElementById('from_format').value),
    indentWithTabs: false,
    tabSize:      2,
    lineWrapping: true
});
inputEditor.setSize('100%', 350);

var outputEditor = null;
if (document.getElementById('output-editor')) {
    outputEditor = CodeMirror(document.getElementById('output-editor'), {
        value:        outputContent,
        mode:         getModeForFormat(outputFormat),
        lineNumbers:  true,
        readOnly:     'nocursor',
        lineWrapping: true
    });
    outputEditor.setSize('100%', 350);
}

document.getElementById('from_format').addEventListener('change', function () {
    inputEditor.setOption('mode', getModeForFormat(this.value));
});

if (document.getElementById('to_format') && outputEditor) {
    document.getElementById('to_format').addEventListener('change', function () {
        outputEditor.setOption('mode', getModeForFormat(this.value));
    });
}

document.getElementById('swap-formats').addEventListener('click', function () {
    var fromSel = document.getElementById('from_format');
    var toSel   = document.getElementById('to_format');
    var tmp     = fromSel.value;
    fromSel.value = toSel.value;
    toSel.value   = tmp;
    inputEditor.setOption('mode', getModeForFormat(fromSel.value));
    if (outputEditor) outputEditor.setOption('mode', getModeForFormat(toSel.value));
});

if (document.getElementById('use-as-input')) {
    document.getElementById('use-as-input').addEventListener('click', function () {
        var content = outputEditor.getValue();
        var toFmt   = document.getElementById('to_format').value;
        inputEditor.setValue(content);
        document.getElementById('from_format').value = toFmt;
        inputEditor.setOption('mode', getModeForFormat(toFmt));
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

var SVG_COPY  = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M0 6.75C0 5.784.784 5 1.75 5h1.5a.75.75 0 0 1 0 1.5h-1.5a.25.25 0 0 0-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 0 0 .25-.25v-1.5a.75.75 0 0 1 1.5 0v1.5A1.75 1.75 0 0 1 9.25 16h-7.5A1.75 1.75 0 0 1 0 14.25Z"/><path d="M5 1.75C5 .784 5.784 0 6.75 0h7.5C15.216 0 16 .784 16 1.75v7.5A1.75 1.75 0 0 1 14.25 11h-7.5A1.75 1.75 0 0 1 5 9.25Zm1.75-.25a.25.25 0 0 0-.25.25v7.5c0 .138.112.25.25.25h7.5a.25.25 0 0 0 .25-.25v-7.5a.25.25 0 0 0-.25-.25Z"/></svg>';
var SVG_CHECK = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 16 16" fill="currentColor"><path d="M13.78 4.22a.75.75 0 0 1 0 1.06l-7.25 7.25a.75.75 0 0 1-1.06 0L2.22 9.28a.751.751 0 0 1 .018-1.042.751.751 0 0 1 1.042-.018L6 10.94l6.72-6.72a.75.75 0 0 1 1.06 0Z"/></svg>';

if (document.getElementById('copy-output') && outputEditor) {
    document.getElementById('copy-output').addEventListener('click', function () {
        var btn = this;
        navigator.clipboard.writeText(outputEditor.getValue()).then(function () {
            btn.innerHTML = SVG_CHECK;
            setTimeout(function () { btn.innerHTML = SVG_COPY; }, 1500);
        });
    });
}

document.getElementById('line-number-style').addEventListener('change', function () {
    var style = this.value;
    var editors = [inputEditor, outputEditor].filter(Boolean);

    if (style === 'roman') {
        var fmt = function(n) { return toRoman(n); };
        editors.forEach(function(e) {
            e.getWrapperElement().classList.remove('ln-hidden');
            e.setOption('lineNumberFormatter', fmt);
        });
    } else if (style === 'none') {
        editors.forEach(function(e) {
            e.getWrapperElement().classList.add('ln-hidden');
        });
    } else {
        editors.forEach(function(e) {
            e.getWrapperElement().classList.remove('ln-hidden');
            e.setOption('lineNumberFormatter', function(n) { return n; });
        });
    }
});
