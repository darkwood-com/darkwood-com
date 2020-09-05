/*
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
*/

cpz.MenuLabelContainer = cpz.MenuBox.extend({
  _container: null,
  _margin: null,
  ctor: function() {
    this._super();
    return this._container = null;
  },
  initWithConfAndContentSizeAndFntFile: function(conf, size, fntFile) {
    if (!this.initWithConfAndContentSize(conf, size)) {
      return false;
    }
    this._container = new cpz.MenuLabel();
    this._container.initWithContentSizeAndFntFile(size, fntFile);
    this._container.setAnchorPoint(cc.p(0.5, 0.5));
    this.addChild(this._container);
    this._margin = cc.size(0, 0);
    return true;
  },
  getString: function() {
    return this._container.getString();
  },
  setString: function(str) {
    this._container.setString(str);
    return this;
  },
  getWidth: function() {
    return this._container.getWidth();
  },
  setWidth: function(width) {
    this._container.setWidth(width);
    return this;
  },
  setAlignment: function(alignment) {
    this._container.setAlignment(alignment);
    return this;
  },
  getMargin: function() {
    return this._margin;
  },
  setMargin: function(margin) {
    this._margin = margin;
    this.layout();
    return this;
  },
  layout: function(anim) {
    var size;
    if (anim == null) {
      anim = true;
    }
    this._super(anim);
    size = this.getContentSize();
    if (this._container) {
      this._container.setPosition(cc.p(size.width / 2, size.height / 2));
      this._container.setContentSize(cc.size(size.width - 2 * this._margin.width, size.height - 2 * this._margin.height));
      return this._container.setWidth(size.width - 2 * this._margin.width);
    }
  },
  onTouchBegan: function(touch, event) {
    if (this._super(touch, event)) {
      return false;
    }
    return this._container.onTouchBegan(touch, event);
  },
  onTouchMoved: function(touch, event) {
    this._super(touch, event);
    return this._container.onTouchMoved(touch, event);
  },
  onTouchEnded: function(touch, event) {
    this._super(touch, event);
    return this._container.onTouchEnded(touch, event);
  },
  onTouchCancelled: function(touch, event) {
    this._super(touch, event);
    return this._container.onTouchCancelled(touch, event);
  }
});
