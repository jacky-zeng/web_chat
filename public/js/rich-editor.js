/**html编辑器 begin**/
var browser = {};

var ua = navigator.userAgent.toLowerCase();

browser.firefox = (/firefox\/([\d.]+)/).test(ua);

function RichEditor(container)
{
    container.innerHTML = '<iframe frameborder="0" style="padding: 0px; width: 595px;height:75px;"></iframe>';

    var editor = container.childNodes[0];

    var editorDoc = editor.contentWindow.document;

    if (browser.firefox)
    {
        editor.onload = function()
        {
            editorDoc.designMode = "on";
        }
    }
    else
    {
        editorDoc.designMode = "on";
    }

    editorDoc.open();
    editorDoc.write("<html><head></head><body style='margin:0px; padding: 0px;word-break:break-all; word-wrap:break-word;'></body></html>");
    editorDoc.close();
}