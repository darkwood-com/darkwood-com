/*
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
*/

cpz.XML_FILE_NAME = "chinesepuzzle.data";

cpz.GameConfig = cc.Class.extend({
  _getNodePath: function(mode, file, sprite) {
    var box, cardBGSprite, color, colors, isCardLayout, k, node, nodePath, nodeSize, path, plistPath, rank, ranks, s, spriteFrameCache, sprites, texturePath, zone, zoneAnchor, zonePosition, zoneSprite, _i, _j, _k, _len, _len1, _len2;
    path = cpz.CommonPath + this._resolution + '/' + (mode === 'theme' ? 'themes/' + this._theme : 'ui');
    plistPath = path + '.plist';
    texturePath = path + '.png';
    isCardLayout = this._isCardLayout ? '1' : '0';
    nodePath = path + ':' + isCardLayout + ':' + file;
    node = cpz.GameConfig._configPaths[nodePath];
    if (!node) {
      spriteFrameCache = cc.spriteFrameCache;
      spriteFrameCache.removeSpriteFramesFromFile(plistPath);
      spriteFrameCache.addSpriteFrames(plistPath);
      sprites = {};
      if (mode === 'ui') {
        sprites['menuMask'] = 'auto';
        sprites['menuContainer'] = 'auto';
        sprites['menuItemYes'] = 'auto';
        sprites['menuItemNo'] = 'auto';
        sprites['menuItemOk'] = 'auto';
        sprites['menuItemThemeClassic'] = 'auto';
        sprites['menuItemThemeChinese'] = 'auto';
        sprites['menuItemThemeCircle'] = 'auto';
        sprites['menuItemThemePolkadots'] = 'auto';
        sprites['menuItemThemeSeamless'] = 'auto';
        sprites['menuItemThemeSkullshearts'] = 'auto';
        sprites['menuItemThemeSplash'] = 'auto';
        sprites['menuItemThemeSpring'] = 'auto';
        sprites['menuItemThemeStripes'] = 'auto';
        sprites['menuItemThemeVivid'] = 'auto';
      } else if (mode === 'theme') {
        sprites['bg'] = 'auto';
        sprites['cardplaybg'] = 'auto';
        sprites['cardboardempty'] = 'auto';
        sprites['cardboardyes'] = 'auto';
        sprites['cardboardno'] = 'auto';
        sprites['cardtouched'] = 'auto';
        sprites['newBtn'] = 'auto';
        sprites['retryBtn'] = 'auto';
        sprites['hintBtn'] = 'auto';
        sprites['soundOnBtn'] = 'auto';
        sprites['soundOffBtn'] = 'auto';
        sprites['themeBtn'] = 'auto';
        sprites['undoBtn'] = 'auto';
        cardBGSprite = cc.Sprite.create('#cardbg.png');
        box = cardBGSprite.getBoundingBox();
        colors = ['D', 'S', 'H', 'C'];
        ranks = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];
        for (_i = 0, _len = colors.length; _i < _len; _i++) {
          color = colors[_i];
          for (_j = 0, _len1 = ranks.length; _j < _len1; _j++) {
            rank = ranks[_j];
            if (this._isCardLayout && rank === "A") {
              sprites['card_' + color + rank] = [
                {
                  from: 'cardbg',
                  to: [0, 0]
                }, {
                  from: 'rank_' + color + rank,
                  to: [box.width / 4, 3 * box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'rank_' + color + rank,
                  to: [3 * box.width / 4, box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [2 * box.width / 4, 2 * box.height / 4],
                  anchor: [0.5, 0.5]
                }
              ];
            } else if (this._isCardLayout && rank === "2") {
              sprites['card_' + color + rank] = [
                {
                  from: 'cardbg',
                  to: [0, 0]
                }, {
                  from: 'small_' + color,
                  to: [2 * box.width / 4, box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [2 * box.width / 4, 3 * box.height / 4],
                  anchor: [0.5, 0.5]
                }
              ];
            } else if (this._isCardLayout && rank === "3") {
              sprites['card_' + color + rank] = [
                {
                  from: 'cardbg',
                  to: [0, 0]
                }, {
                  from: 'small_' + color,
                  to: [2 * box.width / 4, box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [2 * box.width / 4, 2 * box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [2 * box.width / 4, 3 * box.height / 4],
                  anchor: [0.5, 0.5]
                }
              ];
            } else if (this._isCardLayout && rank === "4") {
              sprites['card_' + color + rank] = [
                {
                  from: 'cardbg',
                  to: [0, 0]
                }, {
                  from: 'small_' + color,
                  to: [box.width / 4, box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [box.width / 4, 3 * box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [3 * box.width / 4, box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [3 * box.width / 4, 3 * box.height / 4],
                  anchor: [0.5, 0.5]
                }
              ];
            } else if (this._isCardLayout && rank === "5") {
              sprites['card_' + color + rank] = [
                {
                  from: 'cardbg',
                  to: [0, 0]
                }, {
                  from: 'small_' + color,
                  to: [box.width / 4, box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [box.width / 4, 3 * box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [3 * box.width / 4, box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [3 * box.width / 4, 3 * box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [2 * box.width / 4, 2 * box.height / 4],
                  anchor: [0.5, 0.5]
                }
              ];
            } else if (this._isCardLayout && rank === "6") {
              sprites['card_' + color + rank] = [
                {
                  from: 'cardbg',
                  to: [0, 0]
                }, {
                  from: 'small_' + color,
                  to: [box.width / 4, box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [box.width / 4, 2 * box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [box.width / 4, 3 * box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [3 * box.width / 4, box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [3 * box.width / 4, 2 * box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [3 * box.width / 4, 3 * box.height / 4],
                  anchor: [0.5, 0.5]
                }
              ];
            } else if (this._isCardLayout && rank === "7") {
              sprites['card_' + color + rank] = [
                {
                  from: 'cardbg',
                  to: [0, 0]
                }, {
                  from: 'small_' + color,
                  to: [box.width / 4, box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [box.width / 4, 2 * box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [box.width / 4, 3 * box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [3 * box.width / 4, box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [3 * box.width / 4, 2 * box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [3 * box.width / 4, 3 * box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [2 * box.width / 4, 2 * box.height / 4],
                  anchor: [0.5, 0.5]
                }
              ];
            } else if (this._isCardLayout && rank === "8") {
              sprites['card_' + color + rank] = [
                {
                  from: 'cardbg',
                  to: [0, 0]
                }, {
                  from: 'small_' + color,
                  to: [box.width / 4, box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [box.width / 4, 2 * box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [box.width / 4, 3 * box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [3 * box.width / 4, box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [3 * box.width / 4, 2 * box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [3 * box.width / 4, 3 * box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [2 * box.width / 4, 1.5 * box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [2 * box.width / 4, 2.5 * box.height / 4],
                  anchor: [0.5, 0.5]
                }
              ];
            } else if (this._isCardLayout && rank === "9") {
              sprites['card_' + color + rank] = [
                {
                  from: 'cardbg',
                  to: [0, 0]
                }, {
                  from: 'small_' + color,
                  to: [box.width / 4, box.height / 5],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [box.width / 4, 2 * box.height / 5],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [box.width / 4, 3 * box.height / 5],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [box.width / 4, 4 * box.height / 5],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [3 * box.width / 4, box.height / 5],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [3 * box.width / 4, 2 * box.height / 5],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [3 * box.width / 4, 3 * box.height / 5],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [3 * box.width / 4, 4 * box.height / 5],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [2 * box.width / 4, 1.5 * box.height / 5],
                  anchor: [0.5, 0.5]
                }
              ];
            } else if (this._isCardLayout && rank === "10") {
              sprites['card_' + color + rank] = [
                {
                  from: 'cardbg',
                  to: [0, 0]
                }, {
                  from: 'small_' + color,
                  to: [box.width / 4, box.height / 5],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [box.width / 4, 2 * box.height / 5],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [box.width / 4, 3 * box.height / 5],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [box.width / 4, 4 * box.height / 5],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [3 * box.width / 4, box.height / 5],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [3 * box.width / 4, 2 * box.height / 5],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [3 * box.width / 4, 3 * box.height / 5],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [3 * box.width / 4, 4 * box.height / 5],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [2 * box.width / 4, 1.5 * box.height / 5],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [2 * box.width / 4, 3.5 * box.height / 5],
                  anchor: [0.5, 0.5]
                }
              ];
            } else if (this._isCardLayout && (rank === "J" || rank === "Q" || rank === "K")) {
              sprites['card_' + color + rank] = [
                {
                  from: 'cardbg',
                  to: [0, 0]
                }, {
                  from: 'small_' + color,
                  to: [box.width / 4, box.height / 2],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [3 * box.width / 4, box.height / 2],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'rank_' + color + rank,
                  to: [box.width / 4, 4 * box.height / 5],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'rank_' + color + rank,
                  to: [3 * box.width / 4, box.height / 5],
                  anchor: [0.5, 0.5]
                }
              ];
            } else {
              sprites['card_' + color + rank] = [
                {
                  from: 'cardbg',
                  to: [0, 0]
                }, {
                  from: 'rank_' + color + rank,
                  to: [box.width / 4, 3 * box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'big_' + color,
                  to: [2 * box.width / 4, box.height / 4],
                  anchor: [0.5, 0.5]
                }, {
                  from: 'small_' + color,
                  to: [3 * box.width / 4, 3 * box.height / 4],
                  anchor: [0.5, 0.5]
                }
              ];
            }
          }
        }
      }
      for (k in sprites) {
        s = sprites[k];
        if (s === 'auto') {
          s = [
            {
              from: k,
              to: [0, 0]
            }
          ];
        }
        node = cc.SpriteBatchNode.create(texturePath);
        nodeSize = cc.size(0, 0);
        for (_k = 0, _len2 = s.length; _k < _len2; _k++) {
          zone = s[_k];
          if (!zone['anchor']) {
            zone['anchor'] = [0, 0];
          }
          zonePosition = cc.p(zone['to'][0], zone['to'][1]);
          zoneAnchor = cc.p(zone['anchor'][0], zone['anchor'][1]);
          zoneSprite = cc.Sprite.create('#' + zone['from'] + '.png');
          zoneSprite.setAnchorPoint(zoneAnchor);
          zoneSprite.setPosition(zonePosition);
          node.addChild(zoneSprite);
          box = zoneSprite.getBoundingBox();
          nodeSize.width = Math.max(box.x + box.width, nodeSize.width);
          nodeSize.height = Math.max(box.y + box.height, nodeSize.height);
        }
        node.setContentSize(nodeSize);
        cpz.GameConfig._configPathsSet(path + ':' + isCardLayout + ':' + k, node);
      }
      node = cpz.GameConfig._configPaths[nodePath];
    }
    if (node) {
      return cc.copySpriteBatchNode(node, sprite);
    }
  },
  _resolution: '',
  _theme: '',
  _isCardLayout: false,
  _isSoundOn: false,
  _moves: [],
  _initBoard: null,
  ctor: function() {
    return this._initBoard = new cc.Dictionary();
  },
  init: function() {
    this._resolution = this.defaultResolution();
    this._theme = this.defaultTheme();
    this._isSoundOn = true;
    return true;
  },
  getNodeUiPath: function(file, sprite) {
    return this._getNodePath('ui', file, sprite);
  },
  getNodeThemePath: function(file, sprite) {
    return this._getNodePath('theme', file, sprite);
  },
  getResolutionSize: function() {
    return cpz.GameConfig.parseResolution(this._resolution);
  },
  defaultResolution: function() {
    return '480x320';
  },
  defaultTheme: function() {
    return 'chinese';
  },
  getResolution: function() {
    return this._resolution;
  },
  setResolution: function(_resolution) {
    this._resolution = _resolution;
    return this;
  },
  getTheme: function() {
    return this._theme;
  },
  setTheme: function(_theme) {
    this._theme = _theme;
    return this;
  },
  getIsCardLayout: function() {
    return this._isCardLayout;
  },
  setIsCardLayout: function(_isCardLayout) {
    this._isCardLayout = _isCardLayout;
    return this;
  },
  getIsSoundOn: function() {
    return this._isSoundOn;
  },
  setIsSoundOn: function(_isSoundOn) {
    this._isSoundOn = _isSoundOn;
    return this;
  },
  getMoves: function() {
    return this._moves;
  },
  clearMoves: function() {
    cc.ArrayClear(this._moves);
    return this;
  },
  pushMove: function(move) {
    this._moves.push(move);
    return this;
  },
  popMove: function() {
    return this._moves.pop();
  },
  getInitBoard: function() {
    return this._initBoard;
  },
  encode: function() {
    var card, coord, data, move, _i, _j, _len, _len1, _ref, _ref1;
    data = {
      theme: this._theme,
      isCardLayout: this._isCardLayout,
      isSoundOn: this._isSoundOn,
      moves: [],
      board: []
    };
    _ref = this._initBoard.allKeys();
    for (_i = 0, _len = _ref.length; _i < _len; _i++) {
      coord = _ref[_i];
      card = this._initBoard.object(coord);
      data['board'].push({
        coord: coord.encode(),
        card: card
      });
    }
    _ref1 = this._moves;
    for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
      move = _ref1[_j];
      data['moves'].push(move.encode());
    }
    return data;
  },
  decode: function(data) {
    var board, card, coord, move, _i, _j, _len, _len1, _ref, _ref1;
    this.clearMoves();
    this._initBoard.removeAllObjects();
    this._theme = data['theme'];
    this._isCardLayout = data['isCardLayout'];
    this._isSoundOn = data['isSoundOn'];
    _ref = data['board'];
    for (_i = 0, _len = _ref.length; _i < _len; _i++) {
      board = _ref[_i];
      card = board.card;
      coord = cpz.GridCoord.decode(board.coord);
      this._initBoard.setObject(card, coord);
    }
    _ref1 = data['moves'];
    for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
      move = _ref1[_j];
      this._moves.push(cpz.MoveCoord.decode(move));
    }
    return this;
  },
  save: function() {
    var data;
    data = this.encode();
    data = base64.encode(JSON.stringify(data));
    cc.sys.localStorage.setItem(cpz.XML_FILE_NAME, data);
    return this;
  },
  load: function() {
    var data;
    data = cc.sys.localStorage.getItem(cpz.XML_FILE_NAME);
    if (data) {
      data = JSON.parse(base64.decode(data));
      this.decode(data);
    }
    return this;
  },
  preload: function(selector, target, resolution, theme) {
    var plistThemePath, plistUIPath, textureThemePath, textureUIPath,
      _this = this;
    if (resolution == null) {
      resolution = null;
    }
    if (theme == null) {
      theme = null;
    }
    if (!resolution) {
      resolution = this._resolution;
    }
    if (!theme) {
      theme = this._theme;
    }
    plistThemePath = cpz.CommonPath + resolution + '/themes/' + theme + '.plist';
    textureThemePath = cpz.CommonPath + resolution + '/themes/' + theme + '.png';
    plistUIPath = cpz.CommonPath + resolution + '/ui' + '.plist';
    textureUIPath = cpz.CommonPath + resolution + '/ui' + '.png';
    return cc.loader.load([plistThemePath, textureThemePath, plistUIPath, textureUIPath], function() {
      return selector.call(target);
    });
  }
});

cpz.GameConfig._configPaths = {};

cpz.GameConfig._configPathsSet = function(key, node) {
  if (cpz.GameConfig._configPaths[key]) {
    cpz.GameConfig._configPaths[key].release();
    cpz.GameConfig._configPaths[key] = null;
  }
  cpz.GameConfig._configPaths[key] = node;
  return node.retain();
};

cpz.GameConfig.getRootPath = function(file) {
  return cpz.CommonPath + file;
};

cpz.GameConfig.getResolutionPath = function(file, resolution) {
  return cpz.CommonPath + resolution + '/' + file;
};

cpz.GameConfig.getUiPath = function(file, resolution) {
  return cpz.CommonPath + resolution + '/ui/' + file;
};

cpz.GameConfig.getThemePath = function(file, resolution, theme) {
  return cpz.CommonPath + resolution + '/themes/' + theme + '/' + file;
};

cpz.GameConfig.getFontPath = function(file) {
  return cpz.CommonPath + 'fonts/' + file;
};

cpz.GameConfig.parseResolution = function(res) {
  var m;
  if (!res) {
    return cc.size(0, 0);
  }
  m = res.match(/([0-9]+)x([0-9]+)/);
  if (m) {
    return cc.size(parseInt(m[1]), parseInt(m[2]));
  } else {
    return cc.size(0, 0);
  }
};

cpz.GameConfig.compareResolution = function(res1, res2) {
  var size1, size2;
  size1 = this.parseResolution(res1);
  size2 = this.parseResolution(res2);
  return this.compareSize(size1, size2);
};

cpz.GameConfig.compareSize = function(size1, size2) {
  if ((size1.width < size2.width) && (size1.height < size2.height)) {
    return {
      min: size1,
      max: size2
    };
  } else if ((size1.width >= size2.width) && (size1.height >= size2.height)) {
    return {
      min: size2,
      max: size1
    };
  } else {
    return {
      min: null,
      max: null
    };
  }
};

cpz.GameConfig.bestSize = function(size) {
  var bestSize, compareSize, res, sizeA, sizeB, sizeRes, _i, _len, _ref;
  bestSize = cc.size(0, 0);
  _ref = this.getResolutions();
  for (_i = 0, _len = _ref.length; _i < _len; _i++) {
    res = _ref[_i];
    sizeRes = this.parseResolution(res);
    compareSize = this.compareSize(size, sizeRes);
    if (compareSize.min) {
      compareSize = this.compareSize(compareSize.min, bestSize);
      if (compareSize.max) {
        bestSize = compareSize.max;
      }
    }
  }
  sizeA = cc.size(bestSize.width, bestSize.width * size.height / size.width);
  sizeB = cc.size(bestSize.height * size.width / size.height, bestSize.height);
  compareSize = this.compareSize(sizeA, sizeB);
  bestSize = compareSize.max;
  return bestSize;
};

cpz.GameConfig.getResolutions = function() {
  return ['480x320', '960x640', '1024x768', '1280x800', '1280x1024', '1366x768', '1440x900', '1680x1050', '1920x1080', '1920x1200'];
};

cpz.GameConfig.getThemes = function() {
  return ['chinese', 'circle', 'classic', 'polkadots', 'seamless', 'shullshearts', 'splash', 'spring', 'stripes', 'vivid'];
};
