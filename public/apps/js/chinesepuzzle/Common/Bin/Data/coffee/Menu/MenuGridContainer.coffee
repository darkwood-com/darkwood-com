###
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
###

cpz.MenuGridContainer = cpz.MenuBox.extend(
  _container: null
  _switchControl: null
  _switchControlSelector: null
  _switchControlTarget: null
  _switchControlOn: null
  _switchControlOff: null

  _gridSize: null
  _margin: null
  _page: null
  _minimumTouchLengthToSlide: null
  _minimumTouchLengthToChangePage: null

  ctor: ->
    @_super()

  initWithConf: (conf) ->
    return false unless @_super conf

    @_container = new cpz.MenuGrid()
    @_container.init()
    @_container.setAnchorPoint cc.p(0.5, 0.5)
    @addChild @_container

    @_margin = cc.size(0, 0)

    true

  getGridSize: -> @_container.getGridSize()
  setGridSize: (gridSize) ->
    @_container.setGridSize(gridSize)
    @
  
  getMargin: -> @_margin
  setMargin: (margin) ->
    @_margin = margin

    @layout()
    @
    
  getPage: -> @_container.getPage()
  setPage: (page) ->
    @_container.setPage(page)
    @
    
  getMinimumTouchLengthToSlide: -> @_container.getMinimumTouchLengthToSlide()
  setMinimumTouchLengthToSlide: (length) ->
    @_container.setMinimumTouchLengthToSlide(length)
    @
  
  getMinimumTouchLengthToChangePage: -> @_container.getMinimumTouchLengthToChangePage()
  setMinimumTouchLengthToChangePage: (length) ->
    @_container.setMinimumTouchLengthToChangePage(length)
    @

  getSwitchControl: -> return @_switchControl
  setSwitchControl: (maskSprite, onSprite, offSprite, thumbSprite, onLabel, offLabel, bool, selector, target) ->
    @removeChild(@_switchControl) if @_switchControl
    onLabelControl = cc.LabelTTF.create("On", "Arial-BoldMT", 16)
    offLabelControl = cc.LabelTTF.create("Off", "Arial-BoldMT", 16)
    @_switchControl = cc.ControlSwitch.create(maskSprite, onSprite, offSprite, thumbSprite, onLabelControl, offLabelControl)
    @_switchControl.addTargetWithActionForControlEvents(@, @switchControlValueChanged, cc.CONTROL_EVENT_VALUECHANGED)
    @_switchControlSelector = selector
    @_switchControlTarget = target
    @addChild(@_switchControl)

    @removeChild(@_switchControlOn) if @_switchControlOn
    @_switchControlOn = onLabel
    @_switchControlOn.addLoadedEventListener(@layout, @) if @_switchControlOn.addLoadedEventListener
    @addChild(@_switchControlOn)

    @removeChild(@_switchControlOff) if @_switchControlOff
    @_switchControlOff = offLabel
    @_switchControlOff.addLoadedEventListener(@layout, @) if @_switchControlOff.addLoadedEventListener
    @addChild(@_switchControlOff)

    @_switchControl.setOn(bool)

  switchControlValueChanged: (sender, controlEvent) ->
    if sender.isOn()
      @_switchControlOn.setVisible true
      @_switchControlOff.setVisible false
    else
      @_switchControlOn.setVisible false
      @_switchControlOff.setVisible true

    @_switchControlSelector.call(@_switchControlTarget, sender.isOn()) if @_switchControlSelector

    @layout()

  addTheme: (theme) ->
    @_container.addTheme(theme)
    @

  layout: (anim = true) ->
    @_super anim
    
    size = @getContentSize()

    switchWidth = @_margin.width
    if @_switchControl
      switchWidth += @_switchControl.getContentSize().width

    if @_switchControlOn and @_switchControlOn.isVisible()
      switchWidth += @_switchControlOn.getContentSize().width
      @_switchControlOn.setAnchorPoint cc.p(0.5, 0.5)
      @_switchControlOn.setPosition(cc.p(
        (size.width - switchWidth + @_switchControlOn.getContentSize().width) / 2,
        size.height / 8,
      ))

    if @_switchControlOff and @_switchControlOff.isVisible()
      switchWidth += @_switchControlOff.getContentSize().width
      @_switchControlOff.setAnchorPoint cc.p(0.5, 0.5)
      @_switchControlOff.setPosition(
        (size.width - switchWidth + @_switchControlOff.getContentSize().width) / 2,
        size.height / 8,
      )

    if @_switchControl
      @_switchControl.setAnchorPoint cc.p(0.5, 0.5)
      @_switchControl.setPosition(
        (size.width + switchWidth - @_switchControl.getContentSize().width) / 2,
        size.height / 8,
      )

    if @_container
      @_container.setPosition(cc.p(size.width / 2, size.height / 2))
      @_container.setContentSize(cc.size(size.width - 2 * @_margin.width, size.height - 2 * @_margin.height))

  onTouchBegan: (touch, event) ->
    return false if @_super(touch, event)
    return true if @_switchControl and @_switchControl.onTouchBegan and @_switchControl.onTouchBegan(touch, event)

    return @_container.onTouchBegan(touch, event)
    
  onTouchMoved: (touch, event) ->
    @_super(touch, event)

    @_switchControl.onTouchMoved(touch, event) if @_switchControl and @_switchControl.onTouchMoved and @_switchControl.isTouchInside(touch) and @_switchControl.onTouchMoved(touch)
    @_container.onTouchMoved(touch, event)
  
  onTouchEnded: (touch, event) ->
    @_super(touch, event)

    @_switchControl.onTouchEnded(touch, event) if @_switchControl and @_switchControl.onTouchEnded and @_switchControl.isTouchInside(touch) and @_switchControl.onTouchEnded(touch)
    @_container.onTouchEnded(touch, event)
    
  onTouchCancelled: (touch, event) ->
    @_super(touch, event)

    @_switchControl.onTouchCancelled(touch, event) if @_switchControl and @_switchControl.onTouchCancelled and @_switchControl.isTouchInside(touch) and @_switchControl.onTouchCancelled(touch)
    @_container.onTouchCancelled(touch, event)
)
