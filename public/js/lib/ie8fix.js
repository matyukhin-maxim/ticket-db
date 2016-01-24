/**
 * Created by fellix on 22.01.16.
 */


//if (!Array.prototype.indexOf)
//{
//    Array.prototype.indexOf = function(elt /*, from*/)
//    {
//        var len = this.length >>> 0;
//
//        var from = Number(arguments[1]) || 0;
//        from = (from < 0)
//            ? Math.ceil(from)
//            : Math.floor(from);
//        if (from < 0)
//            from += len;
//
//        for (; from < len; from++)
//        {
//            if (from in this &&
//                this[from] === elt)
//                return from;
//        }
//        return -1;
//    };
//}

var a = 1;

if (!Array.prototype.indexOf) {
    Array.prototype.indexOf = function(val) {
        return jQuery.inArray(val, this);
    };
};

var getStyle = function (el, prop) {
    if (typeof getComputedStyle !== 'undefined') {
        return getComputedStyle(el, null).getPropertyValue(prop);
    } else {
        return el.currentStyle[prop];
    }
};
