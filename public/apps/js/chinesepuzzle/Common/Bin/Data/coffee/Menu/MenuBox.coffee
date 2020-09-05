###
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
###

cpz.MenuBoxTag =
  Title: 0

cpz.MenuBox = cc.Node.extend(
  _bg: null
  _validBtn: null
  _titleLabel: null

  _state: null
  _itemForTouch: (touch) ->
    touchLocation = touch.getLocation()

    menuItems = @getItems().slice()
    menuItems.push @_validBtn
    if menuItems and menuItems.length > 0
      for child in menuItems
        if child instanceof cc.MenuItem and child.isVisible() and child.isEnabled()
          local = child.convertToNodeSpace(touchLocation)
          r = child.rect()
          r.x = 0
          r.y = 0

          if cc.rectContainsPoint(r, local)
            return child

    null

  _selectedItem: null

  _layoutFontFile: null

  _titlePosition: null
  _validPosition: null

  _items: []

  ctor: ->
    @_super()

    @_titlePosition = cc.p(0, 0)
    @_validPosition = cc.p(0, 0)

  initWithConf: (conf) ->
    @_items = []

    @_state = cc.MENU_STATE_WAITING
    @_selectedItem = null

    spriteNodeBg = cc.SpriteBatchNode.create cc.textureNull()
    conf.getNodeUiPath 'menuContainer', spriteNodeBg
    spriteBg = cc.copyFirstSpriteBatchNode spriteNodeBg

    @_bg = new cc.DecoratedBox()
    @_bg.initWithTexture spriteBg.getTexture(), spriteBg.getTextureRect(), @getContentSize()
    @_bg.setAnchorPoint cc.p(0.5, 0.5)
    @addChild @_bg

    spriteNodeValidBtn = cc.SpriteBatchNode.create cc.textureNull()
    conf.getNodeUiPath 'menuItemOk', spriteNodeValidBtn
    spriteValidBtn = cc.copyFirstSpriteBatchNode spriteNodeValidBtn

    @_validBtn = cc.MenuItemSprite.createWithSprite spriteValidBtn
    @_validBtn.setAnchorPoint cc.p(0.5, 0.5)
    @_validBtn.setScale 0.75
    @addChild @_validBtn

    true

  initWithConfAndContentSize: (conf, size) ->
    if @initWithConf(conf)
      @setContentSize size

      return true

    false

  getTitle: -> return if @_titleLabel then @_titleLabel.getString() else null
  setTitle: (title, fontFile) ->
    if @_titleLabel isnt null and @_layoutFontFile is fontFile then @_titleLabel.setString(title)
    else
      @removeChildByTag cpz.MenuBoxTag.Title, true
      @_titleLabel = cc.LabelBMFont.create title, cpz.GameConfig.getFontPath(fontFile)
      @_titleLabel.setAnchorPoint cc.p(0.0, 1.0)
      @addChild @_titleLabel, 0, cpz.MenuBoxTag.Title
  
      @_layoutFontFile = fontFile

    @layout()

  getTitlePosition: -> @_titlePosition
  setTitlePosition: (@_titlePosition) -> @
  getValidPosition: -> @_validPosition
  setValidPosition: (@_validPosition) -> @

  setItems: (items) ->
    if(@_items and @_items.length > 0)
      for child in @_items
        @removeChild child, true

    @_items = items

    if(@_items.length > 0)
      for child in @_items
        @addChild child

    @

  getItems: -> @_items
  addItem: (child, zOrder, tag) ->
    @_items.push(child)
    @addChild(child, zOrder, tag)

  removeItemByTag: (tag, cleanup) ->
    if tag is cc.NODE_TAG_INVALID
      cc.log("cc.MenuBox.removeChildByTag(): argument tag is an invalid tag")

    child = @getChildByTag(tag)

    if child is null
      cc.log('cocos2d: removeItemByTag: child not found!')
    else
      cc.arrayRemoveObject @_items, child

    @removeChildByTag tag, cleanup

  setContentSize: (size) ->
    @_super(size)

    @layout()

  layout: (anim = true) ->
    size = @getContentSize()

    if @_titleLabel then @_titleLabel.setPosition cc.p(@_titlePosition.x, size.height - @_titlePosition.y)
    if @_validBtn then @_validBtn.setPosition cc.p(size.width - @_validPosition.x, size.height - @_validPosition.y)
    if @_bg isnt null
      @_bg.setPosition(cc.p(size.width / 2, size.height / 2))
      @_bg.setContentSize(cc.size(size.width, size.height))

  setOkTarget: (selector, rec) ->
    @_validBtn.setCallback(selector, rec)

  onTouchBegan: (touch, event) ->
    return false if @_state isnt cc.MENU_STATE_WAITING or not @isVisible()

    c = @_parent
    while c?
      return false unless c.isVisible()
      c = c.getParent()

    @_selectedItem = @_itemForTouch(touch)
    if @_selectedItem
      @_state = cc.MENU_STATE_TRACKING_TOUCH
      @_selectedItem.selected()
      return true
    false

  onTouchMoved: (touch, event) ->
    if @_state isnt cc.MENU_STATE_TRACKING_TOUCH
      cc.log "cc.Menu.onTouchMoved(): invalid state"
      return

    currentItem = @_itemForTouch(touch)
    unless currentItem is @_selectedItem
      @_selectedItem.unselected()  if @_selectedItem
      @_selectedItem = currentItem
      @_selectedItem.selected()  if @_selectedItem

  onTouchEnded: (touch, event) ->
    if @_state isnt cc.MENU_STATE_TRACKING_TOUCH
      cc.log "cc.Menu.onTouchEnded(): invalid state"
      return

    if @_selectedItem
      @_selectedItem.unselected()
      @_selectedItem.activate()
    @_state = cc.MENU_STATE_WAITING
    
  onTouchCancelled: (touch, event) ->
    if @_state isnt cc.MENU_STATE_TRACKING_TOUCH
      cc.log "cc.Menu.onTouchCancelled(): invalid state"
      return

    @_selectedItem.unselected()  if @_selectedItem
    @_state = cc.MENU_STATE_WAITING
)
