/*
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
*/

cpz.Menu = cc.Layer.extend({
  _touchListener: null,
  _nav: [],
  _gs: null,
  _ml: null,
  ctor: function() {
    return this._super();
  },
  onExit: function() {
    while (this.popNav()) {}
    return this._super();
  },
  initWithGameSceneAndLayout: function(gs, layout) {
    var _this = this;
    if (!this.init()) {
      return false;
    }
    this._gs = gs;
    this._ml = new cpz.MenuLayout(this);
    this._ml.initWithType(layout);
    cc.eventManager.addListener(cc.EventListener.create({
      event: cc.EventListener.TOUCH_ONE_BY_ONE,
      swallowTouches: true,
      onTouchBegan: function(touch, event) {
        var mc;
        if (_this._nav.length > 0) {
          mc = _this._nav[_this._nav.length - 1];
          mc.onTouchBegan(touch, event);
        }
        return true;
      },
      onTouchMoved: function(touch, event) {
        var mc;
        if (_this._nav.length > 0) {
          mc = _this._nav[_this._nav.length - 1];
          return mc.onTouchMoved(touch, event);
        }
      },
      onTouchEnded: function(touch, event) {
        var mc;
        if (_this._nav.length > 0) {
          mc = _this._nav[_this._nav.length - 1];
          return mc.onTouchEnded(touch, event);
        }
      },
      onTouchCancelled: function(touch, event) {
        var mc;
        if (_this._nav.length > 0) {
          mc = _this._nav[_this._nav.length - 1];
          return mc.onTouchCancelled(touch, event);
        }
      }
    }), this);
    this.layout();
    this.schedule(this.step);
    return true;
  },
  step: function(dt) {},
  layout: function(anim) {
    if (anim == null) {
      anim = true;
    }
    return this._ml.layout(anim);
  },
  pushNav: function(mBox) {
    if (this._nav.length > 0) {
      this.removeChild(this._nav[this._nav.length - 1], true);
    }
    this._nav.push(mBox);
    this.addChild(mBox);
    return this.getGameScene().playSound('menu_push');
  },
  popNav: function() {
    var mBox;
    if (this._nav.length > 0) {
      mBox = this._nav[this._nav.length - 1];
      cc.arrayRemoveObject(this._nav, mBox);
      this.removeChild(mBox, true);
      if (this._nav.length > 0) {
        this.addChild(this._nav[this._nav.length - 1]);
      } else {
        this.getGameScene().playSound('menu_pop');
      }
      return mBox;
    }
    return null;
  },
  okMenu: function(item) {
    this.popNav();
    if (this._nav.length === 0) {
      return this._gs.game();
    }
  },
  getGameScene: function() {
    return this._gs;
  },
  getLayout: function() {
    return this._ml;
  }
});

cpz.Menu.create = function(gs, layout) {
  var obj;
  obj = new cpz.Menu();
  if (obj && obj.initWithGameSceneAndLayout(gs, layout)) {
    return obj;
  }
  return null;
};
