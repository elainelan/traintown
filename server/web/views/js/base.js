
var langBase = $.cookie('langBase');
var platid = $.cookie('platid');

/**
 * 获取服务端API
 * @param module
 * @returns {String}
 */
function getApiUrl(module) {
    return apiBase + "apic.php?cmd=" + module;
}

function getExport(file) {
    return apiBase + file;
}

/**
 * 获取baseUrl
 * @param module
 * @returns {String}
 */
function getBaseUrl(module) {
    return "./" + module;
}





function getLangUrl(file) {
    getLang();
    return "lang/" + langBase + "/"+file;
}
function getLang() {
    if (!langBase) {
        //ie 
        if (navigator.browserLanguage != "undefined" && navigator.browserLanguage != null) {
            langBase = navigator.systemLanguage;
        } 
        //firefox、chrome,360 
        else {
            langBase = navigator.language;
        }
    }
    
    switch (langBase)
    {
        case "zh-CN": // 简体中文
        case "zh-TW": // 繁體中文
        case "en-US": // 英语
            break;
        default:
            // 其他没有配置的语言，默认简体中文
            langBase = "zh-CN";
            break;
    }
    console_log("语言包代码：langBase", langBase);
}
function setLang(lang) {
    $.cookie('langBase', lang);
    langBase = lang;
}

function console_log(title, obj) {
    console.log('====== '+title+' ======');
    console.log(obj);
}
