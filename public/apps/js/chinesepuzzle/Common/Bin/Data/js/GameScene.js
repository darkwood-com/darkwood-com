/*
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
*/

cpz.GameSceneZOrder = {
  BG: 0,
  Game: 1,
  Menu: 2
};

cpz.GameSceneBGMusicTheme = {
  ThemeNone: 0,
  Theme1: 1,
  Theme2: 2,
  Theme3: 3
};

cpz.GameScene = cc.Scene.extend({
  _bgMusicTheme: null,
  _conf: null,
  _background: null,
  _game: null,
  _menu: null,
  ctor: function() {
    var lang;
    this._super();
    this._bgMusicTheme = cpz.GameSceneBGMusicTheme.ThemeNone;
    lang = cc.Lang.getInstance();
    lang.setLang(cc.sys.language);
    lang.addLang('lang');
    this._conf = new cpz.GameConfig();
    this._conf.init();
    return this._conf.load();
  },
  onEnter: function() {
    this._super();
    this.ignoreAnchorPointForPosition(false);
    this.reshape(function() {
      this.playBackgroundMusic(this._conf.getIsSoundOn());
      this._game = null;
      this._menu = null;
      this._background = cpz.Background.create(this);
      this.addChild(this._background, cpz.GameSceneZOrder.BG);
      this.game();
      this.reshape();
      return this.layout();
    }, this);
    return true;
  },
  onExit: function() {
    cc.SafeRelease(this._game);
    cc.SafeRelease(this._menu);
    return this._super();
  },
  game: function() {
    if (this._menu) {
      this.removeChild(this._menu, true);
      cc.SafeRelease(this._menu);
      this._menu = null;
    }
    if (this._game === null) {
      this._game = cpz.Game.create(this);
      this._game.retain();
    }
    if (this._game.getParent() === null) {
      this.addChild(this._game, cpz.GameSceneZOrder.Game);
    }
    return this._game;
  },
  menu: function() {
    return this.menuWithLayout(cpz.MenuLayoutType.None);
  },
  menuWithLayout: function(layout) {
    if (this._menu === null) {
      this._menu = cpz.Menu.create(this, layout);
      this._menu.retain();
    }
    if (this._menu.getParent() === null) {
      this.addChild(this._menu, cpz.GameSceneZOrder.Menu);
    }
    return this._menu;
  },
  newGame: function() {
    var game;
    game = this.game();
    game.newGame();
    this.playSound('shuffle');
    return this;
  },
  retryGame: function() {
    var game;
    game = this.game();
    game.retryGame();
    this.playSound('shuffle');
    return this;
  },
  setResolution: function(resolution, selector, target) {
    if (selector == null) {
      selector = null;
    }
    if (target == null) {
      target = null;
    }
    this._conf.preload(function() {
      this._conf.setResolution(resolution);
      this.setContentSize(this._conf.getResolutionSize());
      this.layout(false);
      this._conf.save();
      if (selector) {
        return selector.call(target);
      }
    }, this, resolution);
    return this;
  },
  setTheme: function(theme, selector, target) {
    if (selector == null) {
      selector = null;
    }
    if (target == null) {
      target = null;
    }
    this._conf.preload(function() {
      var _this = this;
      this._conf.setTheme(theme);
      this._conf.save();
      setTimeout(function() {
        return _this.layout();
      }, cc.PREVENT_FREEZE_TIME);
      if (selector) {
        return selector.call(target);
      }
    }, this, null, theme);
    return this;
  },
  playSound: function(soundName) {
    var audio;
    audio = cc.audioEngine;
    if (this._conf.getIsSoundOn()) {
      audio.playEffect(cpz.CommonPath + 'sound/' + soundName + '.mp3');
    }
    return this;
  },
  playBackgroundMusic: function(play) {
    var audio;
    audio = cc.audioEngine;
    if (play && this._conf.getIsSoundOn()) {
      audio.setMusicVolume(0.5);
      switch (this._bgMusicTheme) {
        case cpz.GameSceneBGMusicTheme.Theme1:
          audio.playMusic(cpz.CommonPath + 'sound/bgm2.mp3', true);
          return this._bgMusicTheme = cpz.GameSceneBGMusicTheme.Theme2;
        case cpz.GameSceneBGMusicTheme.Theme2:
          audio.playMusic(cpz.CommonPath + 'sound/bgm3.mp3', true);
          return this._bgMusicTheme = cpz.GameSceneBGMusicTheme.Theme3;
        default:
          audio.playMusic(cpz.CommonPath + 'sound/bgm1.mp3', true);
          return this._bgMusicTheme = cpz.GameSceneBGMusicTheme.Theme1;
      }
    } else if (this._bgMusicTheme !== cpz.GameSceneBGMusicTheme.ThemeNone) {
      return audio.stopMusic();
    }
  },
  reshapeViewWithSize: function(view, size, selector, target) {
    var designSize;
    if (size == null) {
      size = null;
    }
    if (selector == null) {
      selector = null;
    }
    if (target == null) {
      target = null;
    }
    if (size == null) {
      size = view.getFrameSize();
    }
    view.setFrameSize(size.width, size.height);
    designSize = cpz.GameConfig.bestSize(size);
    view.setDesignResolutionSize(designSize.width, designSize.height, cc.ResolutionPolicy.SHOW_ALL);
    return this.reshape(selector, target);
  },
  reshape: function(selector, target) {
    var autoRes, newRes, oldRes, res, wincenter, winsize, _i, _len, _ref;
    if (selector == null) {
      selector = null;
    }
    if (target == null) {
      target = null;
    }
    winsize = cc.director.getWinSizeInPixels();
    wincenter = cc.pMult(cc.p(winsize.width, winsize.height), 0.5);
    this.setPosition(wincenter);
    if (this._background) {
      this._background.setContentSize(winsize);
    }
    autoRes = null;
    _ref = cpz.GameConfig.getResolutions();
    for (_i = 0, _len = _ref.length; _i < _len; _i++) {
      res = _ref[_i];
      newRes = cpz.GameConfig.parseResolution(res);
      oldRes = cpz.GameConfig.parseResolution(autoRes);
      if (autoRes === null || ((oldRes.width < newRes.width && oldRes.height < newRes.height) && (newRes.width <= winsize.width && newRes.height <= winsize.height))) {
        autoRes = res;
      }
    }
    if (autoRes) {
      return this.setResolution(autoRes, selector, target);
    }
  },
  layout: function(anim) {
    var confcenter, confsize;
    if (anim == null) {
      anim = true;
    }
    if (this._game) {
      this._game.layout(anim);
    }
    if (this._menu) {
      this._menu.layout(anim);
    }
    confsize = this._conf.getResolutionSize();
    confcenter = cc.pMult(cc.p(confsize.width, confsize.height), 0.5);
    this.setContentSize(confsize);
    if (this._background) {
      this._background.setPosition(confcenter);
    }
    return this;
  },
  getConf: function() {
    return this._conf;
  },
  getGame: function() {
    return this._game;
  },
  getMenu: function() {
    return this._menu;
  }
});

cpz.GameScene.create = function() {
  var obj;
  obj = new cpz.GameScene();
  if (obj && obj.init()) {
    return obj;
  }
  return null;
};
