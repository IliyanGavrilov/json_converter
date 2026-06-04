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
        readOnly:     true,
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

if (document.getElementById('copy-output') && outputEditor) {
    document.getElementById('copy-output').addEventListener('click', function () {
        var btn = this;
        navigator.clipboard.writeText(outputEditor.getValue()).then(function () {
            btn.textContent = 'Copied!';
            setTimeout(function () { btn.textContent = 'Copy'; }, 1500);
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
