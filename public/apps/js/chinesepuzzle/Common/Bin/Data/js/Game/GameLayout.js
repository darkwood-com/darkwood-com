/*
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
*/

cpz.GameZOrder = {
  BG: 0,
  UI: 1,
  Board: 2,
  Card: 3,
  MoveCard: 4,
  HintCard: 5
};

cpz.GameLayout = cc.Class.extend({
  _layoutRes: function(key) {
    var res;
    if (cpz.GameLayout._res === null) {
      cpz.GameLayout._res = {
        '480x320': {
          gridCardSize: cc.size(26, 36),
          gridSpaceSize: cc.size(4, 4),
          gridPosition: cc.p(4, 0),
          newBtn: cc.p(451, 290),
          retryBtn: cc.p(451, 238),
          undoBtn: cc.p(451, 186),
          hintBtn: cc.p(451, 134),
          soundBtn: cc.p(451, 82),
          themeBtn: cc.p(451, 30)
        },
        '960x640': {
          gridCardSize: cc.size(52, 72),
          gridSpaceSize: cc.size(8, 8),
          gridPosition: cc.p(8, 0),
          newBtn: cc.p(902, 580),
          retryBtn: cc.p(902, 476),
          undoBtn: cc.p(902, 372),
          hintBtn: cc.p(902, 268),
          soundBtn: cc.p(902, 164),
          themeBtn: cc.p(902, 60)
        },
        '1024x768': {
          gridCardSize: cc.size(56, 78),
          gridSpaceSize: cc.size(10, 10),
          gridPosition: cc.p(10, 32),
          newBtn: cc.p(977, 714),
          retryBtn: cc.p(977, 582),
          undoBtn: cc.p(977, 450),
          hintBtn: cc.p(977, 318),
          soundBtn: cc.p(977, 186),
          themeBtn: cc.p(977, 56)
        },
        '1280x800': {
          gridCardSize: cc.size(56, 78),
          gridSpaceSize: cc.size(10, 10),
          gridPosition: cc.p(118, 48),
          newBtn: cc.p(1220, 740),
          retryBtn: cc.p(1220, 604),
          undoBtn: cc.p(1220, 468),
          hintBtn: cc.p(1220, 332),
          soundBtn: cc.p(1220, 196),
          themeBtn: cc.p(1220, 60)
        },
        '1280x1024': {
          gridCardSize: cc.size(66, 92),
          gridSpaceSize: cc.size(12, 12),
          gridPosition: cc.p(24, 96),
          newBtn: cc.p(1210, 952),
          retryBtn: cc.p(1210, 776),
          undoBtn: cc.p(1210, 600),
          hintBtn: cc.p(1210, 424),
          soundBtn: cc.p(1210, 248),
          themeBtn: cc.p(1210, 72)
        },
        '1366x768': {
          gridCardSize: cc.size(56, 78),
          gridSpaceSize: cc.size(10, 10),
          gridPosition: cc.p(158, 32),
          newBtn: cc.p(1303, 714),
          retryBtn: cc.p(1303, 582),
          undoBtn: cc.p(1303, 450),
          hintBtn: cc.p(1303, 318),
          soundBtn: cc.p(1303, 186),
          themeBtn: cc.p(1303, 56)
        },
        '1440x900': {
          gridCardSize: cc.size(66, 92),
          gridSpaceSize: cc.size(12, 12),
          gridPosition: cc.p(104, 34),
          newBtn: cc.p(1370, 830),
          retryBtn: cc.p(1370, 678),
          undoBtn: cc.p(1370, 526),
          hintBtn: cc.p(1370, 374),
          soundBtn: cc.p(1370, 222),
          themeBtn: cc.p(1370, 70)
        },
        '1680x1050': {
          gridCardSize: cc.size(73, 100),
          gridSpaceSize: cc.size(20, 20),
          gridPosition: cc.p(99, 45),
          newBtn: cc.p(1590, 960),
          retryBtn: cc.p(1590, 786),
          undoBtn: cc.p(1590, 612),
          hintBtn: cc.p(1590, 438),
          soundBtn: cc.p(1590, 264),
          themeBtn: cc.p(1590, 90)
        },
        '1920x1080': {
          gridCardSize: cc.size(73, 100),
          gridSpaceSize: cc.size(20, 20),
          gridPosition: cc.p(214, 60),
          newBtn: cc.p(1825, 985),
          retryBtn: cc.p(1825, 807),
          undoBtn: cc.p(1825, 629),
          hintBtn: cc.p(1825, 451),
          soundBtn: cc.p(1825, 273),
          themeBtn: cc.p(1825, 95)
        },
        '1920x1200': {
          gridCardSize: cc.size(73, 100),
          gridSpaceSize: cc.size(20, 20),
          gridPosition: cc.p(214, 120),
          newBtn: cc.p(1825, 1105),
          retryBtn: cc.p(1825, 903),
          undoBtn: cc.p(1825, 701),
          hintBtn: cc.p(1825, 499),
          soundBtn: cc.p(1825, 297),
          themeBtn: cc.p(1825, 95)
        }
      };
    }
    res = this._game.getGameScene().getConf().getResolution();
    return cpz.GameLayout._res[res][key];
  },
  _actionBtn: function(btn) {
    var conf, isSoundOn;
    if (btn === this._newBtn) {
      return this._game.getGameScene().menuWithLayout(cpz.MenuLayoutType.NewGame);
    } else if (btn === this._retryBtn) {
      return this._game.getGameScene().menuWithLayout(cpz.MenuLayoutType.RetryGame);
    } else if (btn === this._undoBtn) {
      this._game.undoMove();
      return this._game.getGameScene().getConf().save();
    } else if (btn === this._hintBtn) {
      return this._game.getGameScene().menuWithLayout(cpz.MenuLayoutType.Hint);
    } else if (btn === this._soundBtn) {
      conf = this._game.getGameScene().getConf();
      conf.setIsSoundOn(!conf.getIsSoundOn());
      conf.save();
      isSoundOn = conf.getIsSoundOn();
      this._game.getGameScene().playBackgroundMusic(isSoundOn);
      if (isSoundOn) {
        return conf.getNodeThemePath('soundOnBtn', this._soundBtn);
      } else {
        return conf.getNodeThemePath('soundOffBtn', this._soundBtn);
      }
    } else if (btn === this._themeBtn) {
      return this._game.getGameScene().menuWithLayout(cpz.MenuLayoutType.Theme);
    }
  },
  _game: null,
  _activesBtn: [],
  _bg: null,
  _newBtn: null,
  _retryBtn: null,
  _hintBtn: null,
  _undoBtn: null,
  _soundBtn: null,
  _themeBtn: null,
  _gridCardSize: null,
  _gridSpaceSize: null,
  _gridPosition: null,
  ctor: function(game) {
    this._game = game;
    this._gridCardSize = cc.size(0, 0);
    this._gridSpaceSize = cc.size(0, 0);
    return this._gridPosition = cc.size(0, 0);
  },
  layout: function(anim) {
    var conf;
    if (anim == null) {
      anim = true;
    }
    conf = this._game.getGameScene().getConf();
    this._gridCardSize = this._layoutRes('gridCardSize');
    this._gridSpaceSize = this._layoutRes('gridSpaceSize');
    this._gridPosition = this._layoutRes('gridPosition');
    if (this._bg === null) {
      this._bg = cc.SpriteBatchNode.create(cc.textureNull());
      this._game.addChild(this._bg, cpz.GameZOrder.BG);
    }
    conf.getNodeThemePath('bg', this._bg);
    this._bg.setPosition(cc.p(0, 0));
    this._bg.setAnchorPoint(cc.p(0, 0));
    if (this._newBtn === null) {
      this._newBtn = cc.SpriteBatchNode.create(cc.textureNull());
      this._game.addChild(this._newBtn, cpz.GameZOrder.UI);
      this._activesBtn.push(this._newBtn);
    }
    conf.getNodeThemePath('newBtn', this._newBtn);
    this._newBtn.setPosition(this._layoutRes('newBtn'));
    this._newBtn.setScale(0.75);
    if (this._retryBtn === null) {
      this._retryBtn = cc.SpriteBatchNode.create(cc.textureNull());
      this._game.addChild(this._retryBtn, cpz.GameZOrder.UI);
      this._activesBtn.push(this._retryBtn);
    }
    conf.getNodeThemePath('retryBtn', this._retryBtn);
    this._retryBtn.setPosition(this._layoutRes('retryBtn'));
    this._retryBtn.setScale(0.75);
    if (this._undoBtn === null) {
      this._undoBtn = cc.SpriteBatchNode.create(cc.textureNull());
      this._game.addChild(this._undoBtn, cpz.GameZOrder.UI);
      this._activesBtn.push(this._undoBtn);
    }
    conf.getNodeThemePath('undoBtn', this._undoBtn);
    this._undoBtn.setPosition(this._layoutRes('undoBtn'));
    this._undoBtn.setScale(0.75);
    if (this._hintBtn === null) {
      this._hintBtn = cc.SpriteBatchNode.create(cc.textureNull());
      this._game.addChild(this._hintBtn, cpz.GameZOrder.UI);
      this._activesBtn.push(this._hintBtn);
    }
    conf.getNodeThemePath('hintBtn', this._hintBtn);
    this._hintBtn.setPosition(this._layoutRes('hintBtn'));
    this._hintBtn.setScale(0.75);
    if (this._soundBtn === null) {
      this._soundBtn = cc.SpriteBatchNode.create(cc.textureNull());
      this._game.addChild(this._soundBtn, cpz.GameZOrder.UI);
      this._activesBtn.push(this._soundBtn);
    }
    if (conf.getIsSoundOn()) {
      conf.getNodeThemePath('soundOnBtn', this._soundBtn);
    } else {
      conf.getNodeThemePath('soundOffBtn', this._soundBtn);
    }
    this._soundBtn.setPosition(this._layoutRes('soundBtn'));
    this._soundBtn.setScale(0.75);
    if (this._themeBtn === null) {
      this._themeBtn = cc.SpriteBatchNode.create(cc.textureNull());
      this._game.addChild(this._themeBtn, cpz.GameZOrder.UI);
      this._activesBtn.push(this._themeBtn);
    }
    conf.getNodeThemePath('themeBtn', this._themeBtn);
    this._themeBtn.setPosition(this._layoutRes('themeBtn'));
    return this._themeBtn.setScale(0.75);
  },
  tapDownAt: function(location) {
    var btn, local, rect, size, _i, _len, _ref;
    size = 2;
    _ref = this._activesBtn;
    for (_i = 0, _len = _ref.length; _i < _len; _i++) {
      btn = _ref[_i];
      local = btn.convertToNodeSpace(location);
      rect = btn.getBoundingBox();
      rect.x = rect.width * (1 - size) / 2;
      rect.y = rect.height * (1 - size) / 2;
      rect.width = rect.width * size;
      rect.height = rect.height * size;
      if (cc.rectContainsPoint(rect, local)) {
        btn.runAction(cc.Sequence.create([cc.EaseIn.create(cc.ScaleTo.create(0.1, 1.0), 2.0), cc.CallFunc.create(this._actionBtn, this, btn), cc.EaseOut.create(cc.ScaleTo.create(0.1, 0.75), 2.0)]));
      }
    }
    return false;
  },
  tapMoveAt: function(location) {
    return false;
  },
  tapUpAt: function(location) {
    return false;
  },
  getPositionInBoardPoint: function(coord) {
    return cc.p(this._gridPosition.x + (0.5 + coord.j) * (this._gridCardSize.width + this._gridSpaceSize.width), this._gridPosition.y + (0.5 + coord.i) * (this._gridCardSize.height + this._gridSpaceSize.height));
  },
  getPositionInGridCoord: function(point) {
    return cpz.gc(Math.floor((point.y - this._gridPosition.y - 0.5) / (this._gridCardSize.height + this._gridSpaceSize.height)), Math.floor((point.x - this._gridPosition.x - 0.5) / (this._gridCardSize.width + this._gridSpaceSize.width)));
  }
});

cpz.GameLayout._res = null;
