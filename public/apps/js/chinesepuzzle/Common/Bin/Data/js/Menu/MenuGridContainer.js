/*
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
*/

cpz.MenuGridContainer = cpz.MenuBox.extend({
  _container: null,
  _switchControl: null,
  _switchControlSelector: null,
  _switchControlTarget: null,
  _switchControlOn: null,
  _switchControlOff: null,
  _gridSize: null,
  _margin: null,
  _page: null,
  _minimumTouchLengthToSlide: null,
  _minimumTouchLengthToChangePage: null,
  ctor: function() {
    return this._super();
  },
  initWithConf: function(conf) {
    if (!this._super(conf)) {
      return false;
    }
    this._container = new cpz.MenuGrid();
    this._container.init();
    this._container.setAnchorPoint(cc.p(0.5, 0.5));
    this.addChild(this._container);
    this._margin = cc.size(0, 0);
    return true;
  },
  getGridSize: function() {
    return this._container.getGridSize();
  },
  setGridSize: function(gridSize) {
    this._container.setGridSize(gridSize);
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
  getPage: function() {
    return this._container.getPage();
  },
  setPage: function(page) {
    this._container.setPage(page);
    return this;
  },
  getMinimumTouchLengthToSlide: function() {
    return this._container.getMinimumTouchLengthToSlide();
  },
  setMinimumTouchLengthToSlide: function(length) {
    this._container.setMinimumTouchLengthToSlide(length);
    return this;
  },
  getMinimumTouchLengthToChangePage: function() {
    return this._container.getMinimumTouchLengthToChangePage();
  },
  setMinimumTouchLengthToChangePage: function(length) {
    this._container.setMinimumTouchLengthToChangePage(length);
    return this;
  },
  getSwitchControl: function() {
    return this._switchControl;
  },
  setSwitchControl: function(maskSprite, onSprite, offSprite, thumbSprite, onLabel, offLabel, bool, selector, target) {
    var offLabelControl, onLabelControl;
    if (this._switchControl) {
      this.removeChild(this._switchControl);
    }
    onLabelControl = cc.LabelTTF.create("On", "Arial-BoldMT", 16);
    offLabelControl = cc.LabelTTF.create("Off", "Arial-BoldMT", 16);
    this._switchControl = cc.ControlSwitch.create(maskSprite, onSprite, offSprite, thumbSprite, onLabelControl, offLabelControl);
    this._switchControl.addTargetWithActionForControlEvents(this, this.switchControlValueChanged, cc.CONTROL_EVENT_VALUECHANGED);
    this._switchControlSelector = selector;
    this._switchControlTarget = target;
    this.addChild(this._switchControl);
    if (this._switchControlOn) {
      this.removeChild(this._switchControlOn);
    }
    this._switchControlOn = onLabel;
    if (this._switchControlOn.addLoadedEventListener) {
      this._switchControlOn.addLoadedEventListener(this.layout, this);
    }
    this.addChild(this._switchControlOn);
    if (this._switchControlOff) {
      this.removeChild(this._switchControlOff);
    }
    this._switchControlOff = offLabel;
    if (this._switchControlOff.addLoadedEventListener) {
      this._switchControlOff.addLoadedEventListener(this.layout, this);
    }
    this.addChild(this._switchControlOff);
    return this._switchControl.setOn(bool);
  },
  switchControlValueChanged: function(sender, controlEvent) {
    if (sender.isOn()) {
      this._switchControlOn.setVisible(true);
      this._switchControlOff.setVisible(false);
    } else {
      this._switchControlOn.setVisible(false);
      this._switchControlOff.setVisible(true);
    }
    if (this._switchControlSelector) {
      this._switchControlSelector.call(this._switchControlTarget, sender.isOn());
    }
    return this.layout();
  },
  addTheme: function(theme) {
    this._container.addTheme(theme);
    return this;
  },
  layout: function(anim) {
    var size, switchWidth;
    if (anim == null) {
      anim = true;
    }
    this._super(anim);
    size = this.getContentSize();
    switchWidth = this._margin.width;
    if (this._switchControl) {
      switchWidth += this._switchControl.getContentSize().width;
    }
    if (this._switchControlOn && this._switchControlOn.isVisible()) {
      switchWidth += this._switchControlOn.getContentSize().width;
      this._switchControlOn.setAnchorPoint(cc.p(0.5, 0.5));
      this._switchControlOn.setPosition(cc.p((size.width - switchWidth + this._switchControlOn.getContentSize().width) / 2, size.height / 8));
    }
    if (this._switchControlOff && this._switchControlOff.isVisible()) {
      switchWidth += this._switchControlOff.getContentSize().width;
      this._switchControlOff.setAnchorPoint(cc.p(0.5, 0.5));
      this._switchControlOff.setPosition((size.width - switchWidth + this._switchControlOff.getContentSize().width) / 2, size.height / 8);
    }
    if (this._switchControl) {
      this._switchControl.setAnchorPoint(cc.p(0.5, 0.5));
      this._switchControl.setPosition((size.width + switchWidth - this._switchControl.getContentSize().width) / 2, size.height / 8);
    }
    if (this._container) {
      this._container.setPosition(cc.p(size.width / 2, size.height / 2));
      return this._container.setContentSize(cc.size(size.width - 2 * this._margin.width, size.height - 2 * this._margin.height));
    }
  },
  onTouchBegan: function(touch, event) {
    if (this._super(touch, event)) {
      return false;
    }
    if (this._switchControl && this._switchControl.onTouchBegan && this._switchControl.onTouchBegan(touch, event)) {
      return true;
    }
    return this._container.onTouchBegan(touch, event);
  },
  onTouchMoved: function(touch, event) {
    this._super(touch, event);
    if (this._switchControl && this._switchControl.onTouchMoved && this._switchControl.isTouchInside(touch) && this._switchControl.onTouchMoved(touch)) {
      this._switchControl.onTouchMoved(touch, event);
    }
    return this._container.onTouchMoved(touch, event);
  },
  onTouchEnded: function(touch, event) {
    this._super(touch, event);
    if (this._switchControl && this._switchControl.onTouchEnded && this._switchControl.isTouchInside(touch) && this._switchControl.onTouchEnded(touch)) {
      this._switchControl.onTouchEnded(touch, event);
    }
    return this._container.onTouchEnded(touch, event);
  },
  onTouchCancelled: function(touch, event) {
    this._super(touch, event);
    if (this._switchControl && this._switchControl.onTouchCancelled && this._switchControl.isTouchInside(touch) && this._switchControl.onTouchCancelled(touch)) {
      this._switchControl.onTouchCancelled(touch, event);
    }
    return this._container.onTouchCancelled(touch, event);
  }
});
