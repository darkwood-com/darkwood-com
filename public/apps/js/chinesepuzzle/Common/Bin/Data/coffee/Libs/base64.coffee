#
# * Copyright (c) 2010 Nick Galbreath
# * http://code.google.com/p/stringencoders/source/browse/#svn/trunk/javascript
# *
# * Permission is hereby granted, free of charge, to any person
# * obtaining a copy of this software and associated documentation
# * files (the "Software"), to deal in the Software without
# * restriction, including without limitation the rights to use,
# * copy, modify, merge, publish, distribute, sublicense, and/or sell
# * copies of the Software, and to permit persons to whom the
# * Software is furnished to do so, subject to the following
# * conditions:
# *
# * The above copyright notice and this permission notice shall be
# * included in all copies or substantial portions of the Software.
# *
# * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
# * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
# * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
# * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
# * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
# * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
# * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
# * OTHER DEALINGS IN THE SOFTWARE.
# 

# base64 encode/decode compatible with window.btoa/atob
# *
# * window.atob/btoa is a Firefox extension to convert binary data (the "b")
# * to base64 (ascii, the "a").
# *
# * It is also found in Safari and Chrome.  It is not available in IE.
# *
# * if (!window.btoa) window.btoa = base64.encode
# * if (!window.atob) window.atob = base64.decode
# *
# * The original spec's for atob/btoa are a bit lacking
# * https://developer.mozilla.org/en/DOM/window.atob
# * https://developer.mozilla.org/en/DOM/window.btoa
# *
# * window.btoa and base64.encode takes a string where charCodeAt is [0,255]
# * If any character is not [0,255], then an DOMException(5) is thrown.
# *
# * window.atob and base64.decode take a base64-encoded string
# * If the input length is not a multiple of 4, or contains invalid characters
# *   then an DOMException(5) is thrown.
# 
base64 = {}
base64.PADCHAR = "="
base64.ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/"
base64.makeDOMException = ->

  # sadly in FF,Safari,Chrome you can't make a DOMException
  e = undefined
  tmp = undefined
  try
    return new DOMException(DOMException.INVALID_CHARACTER_ERR)
  catch tmp

  # not available, just passback a duck-typed equiv
  # https://developer.mozilla.org/en/Core_JavaScript_1.5_Reference/Global_Objects/Error
  # https://developer.mozilla.org/en/Core_JavaScript_1.5_Reference/Global_Objects/Error/prototype
    ex = new Error("DOM Exception 5")

    # ex.number and ex.description is IE-specific.
    ex.code = ex.number = 5
    ex.name = ex.description = "INVALID_CHARACTER_ERR"

    # Safari/Chrome output format
    ex.toString = ->
      "Error: " + ex.name + ": " + ex.message

    return ex
  return

base64.getbyte64 = (s, i) ->

  # This is oddly fast, except on Chrome/V8.
  #  Minimal or no improvement in performance by using a
  #   object with properties mapping chars to value (eg. 'A': 0)
  idx = base64.ALPHA.indexOf(s.charAt(i))
  throw base64.makeDOMException()  if idx is -1
  idx

base64.decode = (s) ->

  # convert to string
  s = "" + s
  getbyte64 = base64.getbyte64
  pads = undefined
  i = undefined
  b10 = undefined
  imax = s.length
  return s  if imax is 0
  throw base64.makeDOMException()  if imax % 4 isnt 0
  pads = 0
  if s.charAt(imax - 1) is base64.PADCHAR
    pads = 1
    pads = 2  if s.charAt(imax - 2) is base64.PADCHAR

    # either way, we want to ignore this last block
    imax -= 4
  x = []
  i = 0
  while i < imax
    b10 = (getbyte64(s, i) << 18) | (getbyte64(s, i + 1) << 12) | (getbyte64(s, i + 2) << 6) | getbyte64(s, i + 3)
    x.push String.fromCharCode(b10 >> 16, (b10 >> 8) & 0xff, b10 & 0xff)
    i += 4
  switch pads
    when 1
      b10 = (getbyte64(s, i) << 18) | (getbyte64(s, i + 1) << 12) | (getbyte64(s, i + 2) << 6)
      x.push String.fromCharCode(b10 >> 16, (b10 >> 8) & 0xff)
    when 2
      b10 = (getbyte64(s, i) << 18) | (getbyte64(s, i + 1) << 12)
      x.push String.fromCharCode(b10 >> 16)
  x.join ""

base64.getbyte = (s, i) ->
  x = s.charCodeAt(i)
  throw base64.makeDOMException()  if x > 255
  x

base64.encode = (s) ->
  throw new SyntaxError("Not enough arguments")  if arguments.length isnt 1
  padchar = base64.PADCHAR
  alpha = base64.ALPHA
  getbyte = base64.getbyte
  i = undefined
  b10 = undefined
  x = []

  # convert to string
  s = "" + s
  imax = s.length - s.length % 3
  return s  if s.length is 0
  i = 0
  while i < imax
    b10 = (getbyte(s, i) << 16) | (getbyte(s, i + 1) << 8) | getbyte(s, i + 2)
    x.push alpha.charAt(b10 >> 18)
    x.push alpha.charAt((b10 >> 12) & 0x3F)
    x.push alpha.charAt((b10 >> 6) & 0x3f)
    x.push alpha.charAt(b10 & 0x3f)
    i += 3
  switch s.length - imax
    when 1
      b10 = getbyte(s, i) << 16
      x.push alpha.charAt(b10 >> 18) + alpha.charAt((b10 >> 12) & 0x3F) + padchar + padchar
    when 2
      b10 = (getbyte(s, i) << 16) | (getbyte(s, i + 1) << 8)
      x.push alpha.charAt(b10 >> 18) + alpha.charAt((b10 >> 12) & 0x3F) + alpha.charAt((b10 >> 6) & 0x3f) + padchar
  x.join ""