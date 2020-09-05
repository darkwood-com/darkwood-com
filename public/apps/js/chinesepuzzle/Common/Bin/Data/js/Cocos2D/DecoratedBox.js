/*
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
*/

cc.DecoratedBox = cc.Node.extend({
  _batchNode: null,
  _cell: null,
  _boxWidth: null,
  _boxHeight: null,
  getCell: function() {
    return this._cell;
  },
  getBoxWidth: function() {
    return this._boxWidth;
  },
  getBoxHeight: function() {
    return this._boxHeight;
  },
  ctor: function() {
    this._super();
    return this._cell = cc.rect();
  },
  initWithTexture: function(texture, rect, size) {
    this._batchNode = cc.SpriteBatchNode.create(texture, 9);
    if (!this._batchNode) {
      return false;
    }
    this.addChild(this._batchNode);
    this._cell.x = rect.x;
    this._cell.y = rect.y;
    this._cell.width = rect.width / 3;
    this._cell.height = rect.height / 3;
    this.setAnchorPoint(cc.p(0.5, 0.5));
    this.setContentSize(size);
    return true;
  },
  initWithFile: function(filename, size) {
    var textureSize;
    this._batchNode = cc.SpriteBatchNode.createWithFile(filename, 9);
    if (!this._batchNode) {
      return false;
    }
    this.addChild(this._batchNode);
    textureSize = this._batchNode.getTextureAtlas().getTexture().getContentSize();
    this._cell.x = 0;
    this._cell.y = 0;
    this._cell.width = textureSize.width / 3;
    this._cell.height = textureSize.height / 3;
    this.setAnchorPoint(cc.p(0.5, 0.5));
    this.setContentSize(size);
    return true;
  },
  setContentSize: function(size) {
    var b, i, j, offh, offw, rect, uh, uw, _i, _ref, _results;
    if (this.getContentSize().width === size.width && this.getContentSize().height === size.height) {
      return;
    }
    this._batchNode.removeAllChildren(true);
    this._super(size);
    uw = Math.ceil(size.width / this._cell.width);
    uh = Math.ceil(size.height / this._cell.height);
    offw = size.width % this._cell.width;
    offh = size.height % this._cell.height;
    _results = [];
    for (j = _i = 0, _ref = uh - 1; 0 <= _ref ? _i <= _ref : _i >= _ref; j = 0 <= _ref ? ++_i : --_i) {
      _results.push((function() {
        var _j, _ref1, _results1;
        _results1 = [];
        for (i = _j = 0, _ref1 = uw - 1; 0 <= _ref1 ? _j <= _ref1 : _j >= _ref1; i = 0 <= _ref1 ? ++_j : --_j) {
          rect = cc.rect();
          if (i === (uw - 2) && j === (uw - 2)) {
            rect = cc.rect(this._cell.width, this._cell.height, offw, offh);
          } else if (i === (uw - 2)) {
            if (j === (uh - 1)) {
              rect = cc.rect(this._cell.width, 0, offw, this._cell.height);
            } else if (j === 0) {
              rect = cc.rect(this._cell.width, this._cell.height * 2, offw, this._cell.height);
            } else {
              rect = cc.rect(this._cell.width, this._cell.height, offw, this._cell.height);
            }
          } else if (j === (uw - 2)) {
            if (i === (uh - 1)) {
              rect = cc.rect(this._cell.width * 2, this._cell.height, this._cell.width, offh);
            } else if (i === 0) {
              rect = cc.rect(0, this._cell.height, this._cell.width, offh);
            } else {
              rect = cc.rect(this._cell.width, this._cell.height, this._cell.width, offh);
            }
          } else if (i === 0) {
            if (j === (uh - 1)) {
              rect = cc.rect(0, 0, this._cell.width, this._cell.height);
            } else if (j === 0) {
              rect = cc.rect(0, this._cell.height * 2, this._cell.width, this._cell.height);
            } else {
              rect = cc.rect(0, this._cell.height, this._cell.width, this._cell.height);
            }
          } else if (i === (uw - 1)) {
            if (j === (uh - 1)) {
              rect = cc.rect(this._cell.width * 2, 0, this._cell.width, this._cell.height);
            } else if (j === 0) {
              rect = cc.rect(this._cell.width * 2, this._cell.height * 2, this._cell.width, this._cell.height);
            } else {
              rect = cc.rect(this._cell.width * 2, this._cell.height, this._cell.width, this._cell.height);
            }
          } else if (j === (uh - 1)) {
            rect = cc.rect(this._cell.width, 0, this._cell.width, this._cell.height);
          } else if (j === 0) {
            rect = cc.rect(this._cell.width, this._cell.height * 2, this._cell.width, this._cell.height);
          } else {
            rect = cc.rect(this._cell.width, this._cell.height, this._cell.width, this._cell.height);
          }
          rect.x += this._cell.x;
          rect.y += this._cell.y;
          b = cc.Sprite.create(this._batchNode.getTexture(), rect);
          b.setAnchorPoint(cc.p(0, 0));
          if (j === (uh - 1) && i === (uw - 1)) {
            b.setPosition(cc.p((i - 1) * this._cell.width + offw, (j - 1) * this._cell.height + offh));
          } else if (j === (uh - 1)) {
            b.setPosition(cc.p(i * this._cell.width, (j - 1) * this._cell.height + offh));
          } else if (i === (uw - 1)) {
            b.setPosition(cc.p((i - 1) * this._cell.width + offw, j * this._cell.height));
          } else {
            b.setPosition(cc.p(i * this._cell.width, j * this._cell.height));
          }
          b.setTag(j * this._cell.height + i);
          _results1.push(this._batchNode.addChild(b));
        }
        return _results1;
      }).call(this));
    }
    return _results;
  }
});

cc.DecoratedBox.createWithTexture = function(texture, rect, size) {
  var box;
  box = new cc.DecoratedBox();
  if (box && box.initWithTexture(texture, rect, size)) {
    return box;
  }
  return null;
};

cc.DecoratedBox.createWithFile = function(filename, size) {
  var box;
  box = new cc.DecoratedBox();
  if (box && box.initWithFile(filename, size)) {
    return box;
  }
  return null;
};
