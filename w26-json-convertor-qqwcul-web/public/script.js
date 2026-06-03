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

function lineFormatter(style) {
    return style === 'roman' ? function(n) { return toRoman(n); } : null;
}

var useRoman = false;

var inputEditor = CodeMirror.fromTextArea(document.getElementById('input_content'), {
    lineNumbers:         true,
    mode:                getModeForFormat(document.getElementById('from_format').value),
    indentWithTabs:      false,
    tabSize:             2,
    lineWrapping:        true,
    lineNumberFormatter: null
});

inputEditor.setSize(null, 350);

var outputEditor = null;
if (document.getElementById('output-editor')) {
    outputEditor = CodeMirror(document.getElementById('output-editor'), {
        value:               outputContent,
        mode:                getModeForFormat(outputFormat),
        lineNumbers:         true,
        readOnly:            true,
        lineWrapping:        true,
        lineNumberFormatter: null
    });
    outputEditor.setSize(null, 350);
}

document.getElementById('from_format').addEventListener('change', function () {
    inputEditor.setOption('mode', getModeForFormat(this.value));
});

if (document.getElementById('to_format') && outputEditor) {
    document.getElementById('to_format').addEventListener('change', function () {
        outputEditor.setOption('mode', getModeForFormat(this.value));
    });
}

document.getElementById('line-number-style').addEventListener('change', function () {
    useRoman = this.value === 'roman';
    var formatter = useRoman ? function(n) { return toRoman(n); } : null;
    inputEditor.setOption('lineNumberFormatter', formatter);
    if (outputEditor) outputEditor.setOption('lineNumberFormatter', formatter);
});
