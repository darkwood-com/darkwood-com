var base64;

base64 = {};

base64.PADCHAR = "=";

base64.ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";

base64.makeDOMException = function() {
  var e, ex, tmp;
  e = void 0;
  tmp = void 0;
  try {
    return new DOMException(DOMException.INVALID_CHARACTER_ERR);
  } catch (_error) {
    tmp = _error;
    ex = new Error("DOM Exception 5");
    ex.code = ex.number = 5;
    ex.name = ex.description = "INVALID_CHARACTER_ERR";
    ex.toString = function() {
      return "Error: " + ex.name + ": " + ex.message;
    };
    return ex;
  }
};

base64.getbyte64 = function(s, i) {
  var idx;
  idx = base64.ALPHA.indexOf(s.charAt(i));
  if (idx === -1) {
    throw base64.makeDOMException();
  }
  return idx;
};

base64.decode = function(s) {
  var b10, getbyte64, i, imax, pads, x;
  s = "" + s;
  getbyte64 = base64.getbyte64;
  pads = void 0;
  i = void 0;
  b10 = void 0;
  imax = s.length;
  if (imax === 0) {
    return s;
  }
  if (imax % 4 !== 0) {
    throw base64.makeDOMException();
  }
  pads = 0;
  if (s.charAt(imax - 1) === base64.PADCHAR) {
    pads = 1;
    if (s.charAt(imax - 2) === base64.PADCHAR) {
      pads = 2;
    }
    imax -= 4;
  }
  x = [];
  i = 0;
  while (i < imax) {
    b10 = (getbyte64(s, i) << 18) | (getbyte64(s, i + 1) << 12) | (getbyte64(s, i + 2) << 6) | getbyte64(s, i + 3);
    x.push(String.fromCharCode(b10 >> 16, (b10 >> 8) & 0xff, b10 & 0xff));
    i += 4;
  }
  switch (pads) {
    case 1:
      b10 = (getbyte64(s, i) << 18) | (getbyte64(s, i + 1) << 12) | (getbyte64(s, i + 2) << 6);
      x.push(String.fromCharCode(b10 >> 16, (b10 >> 8) & 0xff));
      break;
    case 2:
      b10 = (getbyte64(s, i) << 18) | (getbyte64(s, i + 1) << 12);
      x.push(String.fromCharCode(b10 >> 16));
  }
  return x.join("");
};

base64.getbyte = function(s, i) {
  var x;
  x = s.charCodeAt(i);
  if (x > 255) {
    throw base64.makeDOMException();
  }
  return x;
};

base64.encode = function(s) {
  var alpha, b10, getbyte, i, imax, padchar, x;
  if (arguments.length !== 1) {
    throw new SyntaxError("Not enough arguments");
  }
  padchar = base64.PADCHAR;
  alpha = base64.ALPHA;
  getbyte = base64.getbyte;
  i = void 0;
  b10 = void 0;
  x = [];
  s = "" + s;
  imax = s.length - s.length % 3;
  if (s.length === 0) {
    return s;
  }
  i = 0;
  while (i < imax) {
    b10 = (getbyte(s, i) << 16) | (getbyte(s, i + 1) << 8) | getbyte(s, i + 2);
    x.push(alpha.charAt(b10 >> 18));
    x.push(alpha.charAt((b10 >> 12) & 0x3F));
    x.push(alpha.charAt((b10 >> 6) & 0x3f));
    x.push(alpha.charAt(b10 & 0x3f));
    i += 3;
  }
  switch (s.length - imax) {
    case 1:
      b10 = getbyte(s, i) << 16;
      x.push(alpha.charAt(b10 >> 18) + alpha.charAt((b10 >> 12) & 0x3F) + padchar + padchar);
      break;
    case 2:
      b10 = (getbyte(s, i) << 16) | (getbyte(s, i + 1) << 8);
      x.push(alpha.charAt(b10 >> 18) + alpha.charAt((b10 >> 12) & 0x3F) + alpha.charAt((b10 >> 6) & 0x3f) + padchar);
  }
  return x.join("");
};
