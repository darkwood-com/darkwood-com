/*
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
*/

cpz.GridCoord = cc.Class.extend({
  i: 0,
  j: 0,
  ctor: function() {},
  encode: function() {
    return {
      i: this.i,
      j: this.j
    };
  },
  decode: function(data) {
    this.i = data['i'];
    this.j = data['j'];
    return this;
  }
});

cpz.GridCoord.decode = function(data) {
  var obj;
  obj = new cpz.GridCoord();
  obj.decode(data);
  return obj;
};

cpz.gc = function(i, j) {
  var coord;
  coord = new cpz.GridCoord();
  coord.i = i;
  coord.j = j;
  return coord;
};

cpz.MoveCoord = cc.Class.extend({
  from: null,
  to: null,
  ctor: function() {},
  encode: function() {
    return {
      from: this.from.encode(),
      to: this.to.encode()
    };
  },
  decode: function(data) {
    this.from = cpz.GridCoord.decode(data['from']);
    this.to = cpz.GridCoord.decode(data['to']);
    return this;
  }
});

cpz.MoveCoord.decode = function(data) {
  var obj;
  obj = new cpz.MoveCoord();
  obj.decode(data);
  return obj;
};

cpz.mv = function(from, to) {
  var coord;
  coord = new cpz.MoveCoord();
  coord.from = from;
  coord.to = to;
  return coord;
};
