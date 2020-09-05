/*
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
*/

cpz.MenuBoxTag = {
  Title: 0
};

cpz.MenuBox = cc.Node.extend({
  _bg: null,
  _validBtn: null,
  _titleLabel: null,
  _state: null,
  _itemForTouch: function(touch) {
    var child, local, menuItems, r, touchLocation, _i, _len;
    touchLocation = touch.getLocation();
    menuItems = this.getItems().slice();
    menuItems.push(this._validBtn);
    if (menuItems && menuItems.length > 0) {
      for (_i = 0, _len = menuItems.length; _i < _len; _i++) {
        child = menuItems[_i];
        if (child instanceof cc.MenuItem && child.isVisible() && child.isEnabled()) {
          local = child.convertToNodeSpace(touchLocation);
          r = child.rect();
          r.x = 0;
          r.y = 0;
          if (cc.rectContainsPoint(r, local)) {
            return child;
          }
        }
      }
    }
    return null;
  },
  _selectedItem: null,
  _layoutFontFile: null,
  _titlePosition: null,
  _validPosition: null,
  _items: [],
  ctor: function() {
    this._super();
    this._titlePosition = cc.p(0, 0);
    return this._validPosition = cc.p(0, 0);
  },
  initWithConf: function(conf) {
    var spriteBg, spriteNodeBg, spriteNodeValidBtn, spriteValidBtn;
    this._items = [];
    this._state = cc.MENU_STATE_WAITING;
    this._selectedItem = null;
    spriteNodeBg = cc.SpriteBatchNode.create(cc.textureNull());
    conf.getNodeUiPath('menuContainer', spriteNodeBg);
    spriteBg = cc.copyFirstSpriteBatchNode(spriteNodeBg);
    this._bg = new cc.DecoratedBox();
    this._bg.initWithTexture(spriteBg.getTexture(), spriteBg.getTextureRect(), this.getContentSize());
    this._bg.setAnchorPoint(cc.p(0.5, 0.5));
    this.addChild(this._bg);
    spriteNodeValidBtn = cc.SpriteBatchNode.create(cc.textureNull());
    conf.getNodeUiPath('menuItemOk', spriteNodeValidBtn);
    spriteValidBtn = cc.copyFirstSpriteBatchNode(spriteNodeValidBtn);
    this._validBtn = cc.MenuItemSprite.createWithSprite(spriteValidBtn);
    this._validBtn.setAnchorPoint(cc.p(0.5, 0.5));
    this._validBtn.setScale(0.75);
    this.addChild(this._validBtn);
    return true;
  },
  initWithConfAndContentSize: function(conf, size) {
    if (this.initWithConf(conf)) {
      this.setContentSize(size);
      return true;
    }
    return false;
  },
  getTitle: function() {
    if (this._titleLabel) {
      return this._titleLabel.getString();
    } else {
      return null;
    }
  },
  setTitle: function(title, fontFile) {
    if (this._titleLabel !== null && this._layoutFontFile === fontFile) {
      this._titleLabel.setString(title);
    } else {
      this.removeChildByTag(cpz.MenuBoxTag.Title, true);
      this._titleLabel = cc.LabelBMFont.create(title, cpz.GameConfig.getFontPath(fontFile));
      this._titleLabel.setAnchorPoint(cc.p(0.0, 1.0));
      this.addChild(this._titleLabel, 0, cpz.MenuBoxTag.Title);
      this._layoutFontFile = fontFile;
    }
    return this.layout();
  },
  getTitlePosition: function() {
    return this._titlePosition;
  },
  setTitlePosition: function(_titlePosition) {
    this._titlePosition = _titlePosition;
    return this;
  },
  getValidPosition: function() {
    return this._validPosition;
  },
  setValidPosition: function(_validPosition) {
    this._validPosition = _validPosition;
    return this;
  },
  setItems: function(items) {
    var child, _i, _j, _len, _len1, _ref, _ref1;
    if (this._items && this._items.length > 0) {
      _ref = this._items;
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        child = _ref[_i];
        this.removeChild(child, true);
      }
    }
    this._items = items;
    if (this._items.length > 0) {
      _ref1 = this._items;
      for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
        child = _ref1[_j];
        this.addChild(child);
      }
    }
    return this;
  },
  getItems: function() {
    return this._items;
  },
  addItem: function(child, zOrder, tag) {
    this._items.push(child);
    return this.addChild(child, zOrder, tag);
  },
  removeItemByTag: function(tag, cleanup) {
    var child;
    if (tag === cc.NODE_TAG_INVALID) {
      cc.log("cc.MenuBox.removeChildByTag(): argument tag is an invalid tag");
    }
    child = this.getChildByTag(tag);
    if (child === null) {
      cc.log('cocos2d: removeItemByTag: child not found!');
    } else {
      cc.arrayRemoveObject(this._items, child);
    }
    return this.removeChildByTag(tag, cleanup);
  },
  setContentSize: function(size) {
    this._super(size);
    return this.layout();
  },
  layout: function(anim) {
    var size;
    if (anim == null) {
      anim = true;
    }
    size = this.getContentSize();
    if (this._titleLabel) {
      this._titleLabel.setPosition(cc.p(this._titlePosition.x, size.height - this._titlePosition.y));
    }
    if (this._validBtn) {
      this._validBtn.setPosition(cc.p(size.width - this._validPosition.x, size.height - this._validPosition.y));
    }
    if (this._bg !== null) {
      this._bg.setPosition(cc.p(size.width / 2, size.height / 2));
      return this._bg.setContentSize(cc.size(size.width, size.height));
    }
  },
  setOkTarget: function(selector, rec) {
    return this._validBtn.setCallback(selector, rec);
  },
  onTouchBegan: function(touch, event) {
    var c;
    if (this._state !== cc.MENU_STATE_WAITING || !this.isVisible()) {
      return false;
    }
    c = this._parent;
    while (c != null) {
      if (!c.isVisible()) {
        return false;
      }
      c = c.getParent();
    }
    this._selectedItem = this._itemForTouch(touch);
    if (this._selectedItem) {
      this._state = cc.MENU_STATE_TRACKING_TOUCH;
      this._selectedItem.selected();
      return true;
    }
    return false;
  },
  onTouchMoved: function(touch, event) {
    var currentItem;
    if (this._state !== cc.MENU_STATE_TRACKING_TOUCH) {
      cc.log("cc.Menu.onTouchMoved(): invalid state");
      return;
    }
    currentItem = this._itemForTouch(touch);
    if (currentItem !== this._selectedItem) {
      if (this._selectedItem) {
        this._selectedItem.unselected();
      }
      this._selectedItem = currentItem;
      if (this._selectedItem) {
        return this._selectedItem.selected();
      }
    }
  },
  onTouchEnded: function(touch, event) {
    if (this._state !== cc.MENU_STATE_TRACKING_TOUCH) {
      cc.log("cc.Menu.onTouchEnded(): invalid state");
      return;
    }
    if (this._selectedItem) {
      this._selectedItem.unselected();
      this._selectedItem.activate();
    }
    return this._state = cc.MENU_STATE_WAITING;
  },
  onTouchCancelled: function(touch, event) {
    if (this._state !== cc.MENU_STATE_TRACKING_TOUCH) {
      cc.log("cc.Menu.onTouchCancelled(): invalid state");
      return;
    }
    if (this._selectedItem) {
      this._selectedItem.unselected();
    }
    return this._state = cc.MENU_STATE_WAITING;
  }
});
