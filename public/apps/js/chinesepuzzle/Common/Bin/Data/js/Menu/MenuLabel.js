/*
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
*/

cpz.MenuLabelScrollLayerState = {
  Idle: 0,
  Sliding: 1
};

cpz.MenuLabel = cc.Node.extend({
  _label: null,
  _clip: null,
  _setStencil: function(_clip, size) {
    var lastStencil, rectangle, stencil, white;
    this._clip = _clip;
    lastStencil = this._clip.getStencil();
    stencil = cc.DrawNode.create();
    rectangle = [cc.p(0, 0), cc.p(size.width, 0), cc.p(size.width, size.height), cc.p(0, size.height)];
    white = cc.color(1, 1, 1, 1);
    stencil.drawPoly(rectangle, white, 1, white);
    stencil.retain();
    this._clip.setStencil(stencil);
    if (lastStencil) {
      return lastStencil.cleanup();
    }
  },
  _startSwipe: null,
  _state: null,
  _scrollTouch: null,
  _offsetSwipe: null,
  _offsetScroll: null,
  ctor: function() {
    this._super();
    this._label = null;
    this._startSwipe = 0;
    this._offsetSwipe = 0;
    this._offsetScroll = 0;
    this._scrollTouch = null;
    return this._state = cpz.MenuLabelScrollLayerState.Idle;
  },
  initWithContentSizeAndFntFile: function(size, fntFile) {
    this.setContentSize(size);
    this._clip = cc.ClippingNode.create();
    this._setStencil(this._clip, size);
    this._label = new cc.LabelBMFont();
    this._label.initWithString("", fntFile, 0, cc.TEXT_ALIGNMENT_LEFT);
    this._label.setAnchorPoint(cc.p(0.5, 1.0));
    this._clip.addChild(this._label);
    this.addChild(this._clip);
    return true;
  },
  onExit: function() {
    cc.SafeRelease(this._stencil);
    this.removeChild(this._clip);
    return this._super();
  },
  getString: function() {
    return this._label.getString();
  },
  setString: function(str) {
    this._label.setString(str);
    return this;
  },
  getWidth: function() {
    return this._label.getContentSize().width;
  },
  setWidth: function(width) {
    this._label.setBoundingWidth(width - 20);
    return this;
  },
  setAlignment: function(alignment) {
    this._label.setAlignment(alignment);
    return this;
  },
  getSwipe: function() {
    return this._offsetSwipe;
  },
  setSwipe: function(offsetSwipe) {
    this._offsetSwipe = offsetSwipe;
    this.layout();
    return this;
  },
  getScroll: function() {
    return this._offsetScroll;
  },
  setScroll: function(_offsetScroll) {
    this._offsetScroll = _offsetScroll;
    return this;
  },
  setContentSize: function(size) {
    this._super(size);
    this.layout();
    return this;
  },
  layout: function(anim) {
    var size;
    if (anim == null) {
      anim = true;
    }
    size = this.getContentSize();
    if (this._clip) {
      this._setStencil(this._clip, size);
    }
    if (this._label) {
      return this._label.setPosition(cc.pAdd(cc.p(size.width / 2, size.height), cc.p(0, this._offsetScroll + this._offsetSwipe)));
    }
  },
  onTouchBegan: function(touch, event) {
    var touchPoint;
    if (!this._scrollTouch) {
      this._scrollTouch = touch;
    } else {
      return false;
    }
    touchPoint = touch.getLocation();
    this._startSwipe = touchPoint.y;
    this._state = cpz.MenuLabelScrollLayerState.Idle;
    return true;
  },
  onTouchMoved: function(touch, event) {
    var touchPoint;
    if (!this._scrollTouch) {
      return;
    }
    touchPoint = touch.getLocation();
    if (this._state !== cpz.MenuLabelScrollLayerState.Sliding) {
      this._state = cpz.MenuLabelScrollLayerState.Sliding;
      this._startSwipe = touchPoint.y;
    }
    if (this._state === cpz.MenuLabelScrollLayerState.Sliding) {
      return this.setSwipe(touchPoint.y - this._startSwipe);
    }
  },
  onTouchEnded: function(touch, event) {
    var touchPoint;
    if (!this._scrollTouch) {
      return;
    }
    this._scrollTouch = null;
    touchPoint = touch.getLocation();
    this.setScroll(this.getScroll() + touchPoint.y - this._startSwipe);
    return this.setSwipe(0);
  },
  onTouchCancelled: function(touch, event) {
    return this._scrollTouch = null;
  }
});
