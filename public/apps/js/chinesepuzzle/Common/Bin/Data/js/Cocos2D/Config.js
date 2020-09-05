/*
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
*/

cc.PREVENT_FREEZE_TIME = 100;

cc.SafeRelease = function(p) {
  if (p) {
    return p.release();
  }
};

cc.textureNull = function() {
  return new cc.Texture2D();
};

cc.copySpriteBatchNode = function(from, to) {
  var child, zoneSprite, _i, _len, _ref;
  if (!(to instanceof cc.SpriteBatchNode && from instanceof cc.SpriteBatchNode)) {
    return;
  }
  to.removeAllChildren(true);
  to.setTexture(from.getTexture());
  _ref = from.getChildren();
  for (_i = 0, _len = _ref.length; _i < _len; _i++) {
    child = _ref[_i];
    zoneSprite = cc.Sprite.create(to.getTexture(), child.getTextureRect());
    zoneSprite.setAnchorPoint(child.getAnchorPoint());
    zoneSprite.setPosition(child.getPosition());
    to.addChild(zoneSprite);
  }
  to.setContentSize(from.getContentSize());
  return to.setAnchorPoint(cc.p(0.5, 0.5));
};

cc.copySprite = function(sprite) {
  return cc.Sprite.create(sprite.getTexture(), sprite.getTextureRect());
};

cc.copyFirstSpriteBatchNode = function(sprite) {
  var child, _i, _len, _ref;
  _ref = sprite.getChildren();
  for (_i = 0, _len = _ref.length; _i < _len; _i++) {
    child = _ref[_i];
    return cc.Sprite.create(child.getTexture(), child.getTextureRect());
  }
  return null;
};

/*
Shuffle array
@function
@param {Array} arr
@return {Array}
*/


cc.ArrayShuffle = function(arr) {
  var currentIndex, randomIndex, temporaryValue;
  currentIndex = arr.length;
  temporaryValue = void 0;
  randomIndex = void 0;
  while (0 !== currentIndex) {
    randomIndex = Math.floor(Math.random() * currentIndex);
    currentIndex -= 1;
    temporaryValue = arr[currentIndex];
    arr[currentIndex] = arr[randomIndex];
    arr[randomIndex] = temporaryValue;
  }
  return arr;
};

cc.ArrayClear = function(arr) {
  arr.splice(0, arr.length);
  return arr;
};

cc.ObjectHas = function(obj, key) {
  return obj.hasOwnProperty(key);
};

cc.ObjectKeys = function(obj) {
  var key, keys;
  if (obj !== Object(obj)) {
    throw new TypeError("Invalid object");
  }
  keys = [];
  for (key in obj) {
    if (cc.ObjectHas(obj, key)) {
      keys.push(key);
    }
  }
  return keys;
};

cc.ObjectValues = function(obj) {
  var key, value, values;
  if (obj !== Object(obj)) {
    throw new TypeError("Invalid object");
  }
  values = [];
  for (key in obj) {
    value = obj[key];
    if (cc.ObjectHas(obj, key)) {
      values.push(value);
    }
  }
  return values;
};

cc.ObjectSize = function(obj) {
  if (obj == null) {
    return 0;
  }
  if (obj.length === +obj.length) {
    return obj.length;
  } else {
    return cc.ObjectKeys(obj).length;
  }
};

cc.Dictionary = cc.Class.extend({
  _keyMapTb: null,
  _valueMapTb: null,
  __currId: 0,
  ctor: function() {
    this._keyMapTb = {};
    this._valueMapTb = {};
    return this.__currId = 2 << (0 | (Math.random() * 10));
  },
  __getKey: function() {
    this.__currId++;
    return "key_" + this.__currId;
  },
  setObject: function(value, key) {
    var keyId;
    if (key == null) {
      return;
    }
    keyId = this.__getKey();
    this._keyMapTb[keyId] = key;
    return this._valueMapTb[keyId] = value;
  },
  object: function(key) {
    var keyId, locKeyMapTb;
    if (key == null) {
      return null;
    }
    locKeyMapTb = this._keyMapTb;
    for (keyId in locKeyMapTb) {
      if (locKeyMapTb[keyId] === key) {
        return this._valueMapTb[keyId];
      }
    }
    return null;
  },
  value: function(key) {
    return this.object(key);
  },
  removeObject: function(key) {
    var keyId, locKeyMapTb;
    if (key == null) {
      return;
    }
    locKeyMapTb = this._keyMapTb;
    for (keyId in locKeyMapTb) {
      if (locKeyMapTb[keyId] === key) {
        delete this._valueMapTb[keyId];
        delete locKeyMapTb[keyId];
        return;
      }
    }
  },
  removeObjects: function(keys) {
    var i, _results;
    if (keys == null) {
      return;
    }
    i = 0;
    _results = [];
    while (i < keys.length) {
      this.removeObject(keys[i]);
      _results.push(i++);
    }
    return _results;
  },
  allKeys: function() {
    var key, keyArr, locKeyMapTb;
    keyArr = [];
    locKeyMapTb = this._keyMapTb;
    for (key in locKeyMapTb) {
      keyArr.push(locKeyMapTb[key]);
    }
    return keyArr;
  },
  removeAllObjects: function() {
    this._keyMapTb = {};
    return this._valueMapTb = {};
  },
  count: function() {
    return this.allKeys().length;
  }
});

cc.MenuItemSprite.createWithSprite = function(sprite) {
  var normalSprite, selectedSprite;
  normalSprite = cc.copySprite(sprite);
  selectedSprite = cc.copySprite(sprite);
  return cc.MenuItemSprite.create(normalSprite, selectedSprite);
};

cc.MenuItemSprite.createWithSpriteAndCallback = function(sprite, callback, target) {
  var ret;
  ret = cc.MenuItemSprite.createWithSprite(sprite);
  ret.setCallback(callback, target);
  return ret;
};
