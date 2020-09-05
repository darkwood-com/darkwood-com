/*
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
*/

cpz.MenuGridScrollLayerState = {
  Idle: 0,
  Sliding: 1
};

cpz.MenuGrid = cc.Node.extend({
  _themesGrid: null,
  _resetGrid: function() {
    var a, coord, k, p, pageMax, pageMin, theme, _i, _j, _len, _ref;
    _ref = this._themesGrid.allKeys();
    for (_i = 0, _len = _ref.length; _i < _len; _i++) {
      coord = _ref[_i];
      theme = this._themesGrid.object(coord);
      this._clip.removeChild(theme, true);
    }
    this._themesGrid.removeAllObjects();
    a = this._gridSize.width * this._gridSize.height;
    pageMin = a * (this._page - 1);
    pageMax = a * (this._page + 2) - 1;
    for (k = _j = pageMin; pageMin <= pageMax ? _j <= pageMax : _j >= pageMax; k = pageMin <= pageMax ? ++_j : --_j) {
      if (k >= 0 && k < this._themes.length) {
        theme = this._themes[k];
        theme.setAnchorPoint(cc.p(0.5, 0.5));
        this._clip.addChild(theme);
        p = Math.floor(k / a);
        coord = cc.p(p * this._gridSize.width + k % this._gridSize.width, this._gridSize.height - 1 - Math.floor((k - p * a) / this._gridSize.width));
        this._themesGrid.setObject(theme, coord);
      }
    }
    return this.layout();
  },
  _startSwipe: 0,
  _state: cpz.MenuGridScrollLayerState.Idle,
  _scrollTouch: null,
  _swipeToPage_dt: 0,
  _swipeToPage_start: 0,
  _swipeToPage_end: 0,
  _swipeToPage: function(dt) {
    var delta, duration, swipe;
    this._swipeToPage_dt += dt;
    duration = 0.3;
    delta = this._swipeToPage_end - this._swipeToPage_start;
    swipe = this._swipeToPage_end - delta * (1 - this._swipeToPage_dt / duration);
    this.setSwipe(swipe);
    if (this._swipeToPage_dt >= duration) {
      this.setPage(this.getPage() - Math.round(this.getSwipe() / this.getContentSize().width));
      if (this._delegate) {
        this._delegate.scrollLayerScrolledToPageNumber(this, this._page);
      }
      return this.unschedule(this._swipeToPage);
    }
  },
  _getMaxPage: function() {
    return Math.ceil(this._themes.length / (this._gridSize.width * this._gridSize.height));
  },
  _selectedItem: null,
  _themes: [],
  _gridSize: null,
  _offsetSwipe: null,
  _size: null,
  _page: null,
  _delegate: null,
  _minimumTouchLengthToSlide: null,
  _minimumTouchLengthToChangePage: null,
  _stencil: null,
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
  ctor: function() {
    this._super();
    this._themesGrid = new cc.Dictionary();
    this._gridSize = cc.size(0, 0);
    this._page = 0;
    this._delegate = null;
    this._startSwipe = 0;
    this._offsetSwipe = 0;
    this._scrollTouch = null;
    this._selectedItem = null;
    return this._state = cpz.MenuGridScrollLayerState.Idle;
  },
  init: function() {
    this._themes = [];
    this._minimumTouchLengthToSlide = 10.5;
    this._minimumTouchLengthToChangePage = 100.5;
    this._clip = cc.ClippingNode.create();
    this.addChild(this._clip);
    return true;
  },
  initWithContentSize: function(size) {
    if (this.init()) {
      this.setContentSize(size);
      this._minimumTouchLengthToChangePage = size.width / 8;
      this._setStencil(this._clip, size);
      return true;
    }
    return false;
  },
  onExit: function() {
    var theme, _i, _len, _ref;
    cc.SafeRelease(this._stencil);
    _ref = this._themes;
    for (_i = 0, _len = _ref.length; _i < _len; _i++) {
      theme = _ref[_i];
      theme.release();
    }
    this.removeChild(this._clip);
    return this._super();
  },
  addTheme: function(theme) {
    theme.retain();
    this._themes.push(theme);
    this._resetGrid();
    return this;
  },
  getGridSize: function() {
    return this._gridSize;
  },
  setGridSize: function(_gridSize) {
    this._gridSize = _gridSize;
    this._resetGrid();
    return this;
  },
  getSwipe: function() {
    return this._offsetSwipe;
  },
  setSwipe: function(_offsetSwipe) {
    this._offsetSwipe = _offsetSwipe;
    this.layout();
    return this;
  },
  setContentSize: function(size) {
    this._super(size);
    this.layout();
    return this;
  },
  layout: function(anim) {
    var coord, origin, pad, size, theme, _i, _len, _ref, _results;
    if (anim == null) {
      anim = true;
    }
    size = this.getContentSize();
    if (this._clip) {
      this._setStencil(this._clip, size);
    }
    if (this._gridSize.width > 0 && this._gridSize.height > 0) {
      pad = cc.size(size.width / this._gridSize.width, size.height / this._gridSize.height);
      origin = cc.p(size.width / (2 * this._gridSize.width) - this._page * size.width, size.height / (2 * this._gridSize.height));
      _ref = this._themesGrid.allKeys();
      _results = [];
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        coord = _ref[_i];
        theme = this._themesGrid.object(coord);
        _results.push(theme.setPosition(cc.pAdd(origin, cc.p(coord.x * pad.width + this._offsetSwipe, coord.y * pad.height))));
      }
      return _results;
    }
  },
  getPage: function() {
    return this._page;
  },
  setPage: function(page) {
    if (page >= 0 && page < this._getMaxPage()) {
      this._page = page;
      this.setSwipe(0);
      this._resetGrid();
    }
    return this;
  },
  updateTweenAction: function(value, key) {
    if (key === 'swipe') {
      return this.setSwipe(value);
    }
  },
  swipeToPage: function(page) {
    if (page >= 0 && page < this._getMaxPage()) {
      this._swipeToPage_dt = 0;
      this._swipeToPage_start = this.getSwipe();
      this._swipeToPage_end = (this._page - page) * this.getContentSize().width;
      return this.schedule(this._swipeToPage);
    }
  },
  getDelegate: function() {
    return this._delegate;
  },
  setDelegate: function(_delegate) {
    this._delegate = _delegate;
    return this;
  },
  getMinimumTouchLengthToSlide: function() {
    return this._minimumTouchLengthToSlide;
  },
  setMinimumTouchLengthToSlide: function(_minimumTouchLengthToSlide) {
    this._minimumTouchLengthToSlide = _minimumTouchLengthToSlide;
    return this;
  },
  getMinimumTouchLengthToChangePage: function() {
    return this._minimumTouchLengthToChangePage;
  },
  setMinimumTouchLengthToChangePage: function(_minimumTouchLengthToChangePage) {
    this._minimumTouchLengthToChangePage = _minimumTouchLengthToChangePage;
    return this;
  },
  onTouchBegan: function(touch, event) {
    var child, local, r, touchPoint, _i, _len, _ref;
    touchPoint = touch.getLocation();
    if (this._themes && this._themes.length > 0) {
      _ref = this._themes;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        child = _ref[_i];
        if (child instanceof cc.MenuItem && child.isVisible() && child.isEnabled()) {
          local = child.convertToNodeSpace(touchPoint);
          r = child.rect();
          r.x = 0;
          r.y = 0;
          if (cc.rectContainsPoint(r, local)) {
            if (this._selectedItem) {
              this._selectedItem.unselected();
            }
            this._selectedItem = child;
            this._selectedItem.selected();
            this._selectedItem.activate();
            this._selectedItem.runAction(cc.Sequence.create([cc.EaseIn.create(cc.ScaleTo.create(0.1, 0.75), 2.5), cc.EaseOut.create(cc.ScaleTo.create(0.1, 1.0), 2.5)]));
          }
        }
      }
    }
    if (!this._scrollTouch) {
      this._scrollTouch = touch;
    } else {
      return false;
    }
    this._startSwipe = touchPoint.x;
    this._state = cpz.MenuGridScrollLayerState.Idle;
    return true;
  },
  onTouchMoved: function(touch, event) {
    var touchPoint;
    if (!this._scrollTouch) {
      return;
    }
    touchPoint = touch.getLocation();
    if (this._state !== cpz.MenuGridScrollLayerState.Sliding && Math.abs(touchPoint.x - this._startSwipe) >= this._minimumTouchLengthToSlide) {
      this._state = cpz.MenuGridScrollLayerState.Sliding;
      this._startSwipe = touchPoint.x;
      if (this._delegate) {
        this._delegate.scrollLayerScrollingStarted(this);
      }
    }
    if (this._state === cpz.MenuGridScrollLayerState.Sliding) {
      return this.setSwipe(touchPoint.x - this._startSwipe);
    }
  },
  onTouchEnded: function(touch, event) {
    var selectedPage, swipe, touchPoint;
    if (!this._scrollTouch) {
      return;
    }
    this._scrollTouch = null;
    touchPoint = touch.getLocation();
    selectedPage = this.getPage();
    swipe = touchPoint.x - this._startSwipe;
    this.setSwipe(swipe);
    if (swipe > 0 && swipe >= this._minimumTouchLengthToChangePage) {
      selectedPage -= ((this.getSwipe() - this._minimumTouchLengthToChangePage) / this.getContentSize().width) + 1;
    } else if (swipe < 0 && swipe <= -this._minimumTouchLengthToChangePage) {
      selectedPage += -((this.getSwipe() + this._minimumTouchLengthToChangePage) / this.getContentSize().width) + 1;
    }
    if (selectedPage < 0) {
      selectedPage = 0;
    }
    if (selectedPage >= this._getMaxPage()) {
      selectedPage = this._getMaxPage() - 1;
    }
    return this.swipeToPage(selectedPage);
  },
  onTouchCancelled: function(touch, event) {
    this._scrollTouch = null;
    return this.setPage(this._page);
  }
});
