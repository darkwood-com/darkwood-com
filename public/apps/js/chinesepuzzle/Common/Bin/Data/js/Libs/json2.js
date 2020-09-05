var JSON;

if (typeof JSON !== "object") {
  JSON = {};
}

(function() {
  var cx, escapable, f, gap, indent, meta, quote, rep, str;
  f = function(n) {
    if (n < 10) {
      return "0" + n;
    } else {
      return n;
    }
  };
  quote = function(string) {
    escapable.lastIndex = 0;
    if (escapable.test(string)) {
      return "\"" + string.replace(escapable, function(a) {
        var c;
        c = meta[a];
        if (typeof c === "string") {
          return c;
        } else {
          return "\\u" + ("0000" + a.charCodeAt(0).toString(16)).slice(-4);
        }
      }) + "\"";
    } else {
      return "\"" + string + "\"";
    }
  };
  str = function(key, holder) {
    var gap, i, k, length, mind, partial, v, value;
    i = void 0;
    k = void 0;
    v = void 0;
    length = void 0;
    mind = gap;
    partial = void 0;
    value = holder[key];
    if (value && typeof value === "object" && typeof value.toJSON === "function") {
      value = value.toJSON(key);
    }
    if (typeof rep === "function") {
      value = rep.call(holder, key, value);
    }
    switch (typeof value) {
      case "string":
        return quote(value);
      case "number":
        if (isFinite(value)) {
          return String(value);
        } else {
          return "null";
        }
      case "boolean":
      case "null":
        return String(value);
      case "object":
        if (!value) {
          return "null";
        }
        gap += indent;
        partial = [];
        if (Object.prototype.toString.apply(value) === "[object Array]") {
          length = value.length;
          i = 0;
          while (i < length) {
            partial[i] = str(i, value) || "null";
            i += 1;
          }
          v = (partial.length === 0 ? "[]" : (gap ? "[\n" + gap + partial.join(",\n" + gap) + "\n" + mind + "]" : "[" + partial.join(",") + "]"));
          gap = mind;
          return v;
        }
        if (rep && typeof rep === "object") {
          length = rep.length;
          i = 0;
          while (i < length) {
            if (typeof rep[i] === "string") {
              k = rep[i];
              v = str(k, value);
              if (v) {
                partial.push(quote(k) + (gap ? ": " : ":") + v);
              }
            }
            i += 1;
          }
        } else {
          for (k in value) {
            if (Object.prototype.hasOwnProperty.call(value, k)) {
              v = str(k, value);
              if (v) {
                partial.push(quote(k) + (gap ? ": " : ":") + v);
              }
            }
          }
        }
        v = (partial.length === 0 ? "{}" : (gap ? "{\n" + gap + partial.join(",\n" + gap) + "\n" + mind + "}" : "{" + partial.join(",") + "}"));
        gap = mind;
        return v;
    }
  };
  "use strict";
  if (typeof Date.prototype.toJSON !== "function") {
    Date.prototype.toJSON = function() {
      if (isFinite(this.valueOf())) {
        return this.getUTCFullYear() + "-" + f(this.getUTCMonth() + 1) + "-" + f(this.getUTCDate()) + "T" + f(this.getUTCHours()) + ":" + f(this.getUTCMinutes()) + ":" + f(this.getUTCSeconds()) + "Z";
      } else {
        return null;
      }
    };
    String.prototype.toJSON = Number.prototype.toJSON = Boolean.prototype.toJSON = function() {
      return this.valueOf();
    };
  }
  cx = void 0;
  escapable = void 0;
  gap = void 0;
  indent = void 0;
  meta = void 0;
  rep = void 0;
  if (typeof JSON.stringify !== "function") {
    escapable = /[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;
    meta = {
      "\b": "\\b",
      "\t": "\\t",
      "\n": "\\n",
      "\f": "\\f",
      "\r": "\\r",
      "\"": "\\\"",
      "\\": "\\\\"
    };
    JSON.stringify = function(value, replacer, space) {
      var i;
      i = void 0;
      gap = "";
      indent = "";
      if (typeof space === "number") {
        i = 0;
        while (i < space) {
          indent += " ";
          i += 1;
        }
      } else {
        if (typeof space === "string") {
          indent = space;
        }
      }
      rep = replacer;
      if (replacer && typeof replacer !== "function" && (typeof replacer !== "object" || typeof replacer.length !== "number")) {
        throw new Error("JSON.stringify");
      }
      return str("", {
        "": value
      });
    };
  }
  if (typeof JSON.parse !== "function") {
    cx = /[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;
    JSON.parse = function(text, reviver) {
      var j, walk;
      walk = function(holder, key) {
        var k, v, value;
        k = void 0;
        v = void 0;
        value = holder[key];
        if (value && typeof value === "object") {
          for (k in value) {
            if (Object.prototype.hasOwnProperty.call(value, k)) {
              v = walk(value, k);
              if (v !== undefined) {
                value[k] = v;
              } else {
                delete value[k];
              }
            }
          }
        }
        return reviver.call(holder, key, value);
      };
      j = void 0;
      text = String(text);
      cx.lastIndex = 0;
      if (cx.test(text)) {
        text = text.replace(cx, function(a) {
          return "\\u" + ("0000" + a.charCodeAt(0).toString(16)).slice(-4);
        });
      }
      if (/^[\],:{}\s]*$/.test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, "@").replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, "]").replace(/(?:^|:|,)(?:\s*\[)+/g, ""))) {
        j = eval_("(" + text + ")");
        return (typeof reviver === "function" ? walk({
          "": j
        }, "") : j);
      }
      throw new SyntaxError("JSON.parse");
    };
  }
})();
