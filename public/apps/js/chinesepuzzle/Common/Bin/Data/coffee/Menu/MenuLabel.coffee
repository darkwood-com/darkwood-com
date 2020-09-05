###
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
###

cpz.MenuLabelScrollLayerState =
  Idle: 0
  Sliding: 1

cpz.MenuLabel = cc.Node.extend(
  _label: null

  _clip: null
  _setStencil: (@_clip, size) ->
    lastStencil = @_clip.getStencil()

    stencil = cc.DrawNode.create()
    rectangle = [cc.p(0, 0),cc.p(size.width, 0),
                 cc.p(size.width, size.height),
                 cc.p(0, size.height)]

    white = cc.color(1, 1, 1, 1)
    stencil.drawPoly(rectangle, white, 1, white)
    stencil.retain()
    @_clip.setStencil(stencil)

    if lastStencil
      lastStencil.cleanup()
      #cc.SafeRelease lastStencil

  _startSwipe: null
  _state: null
  _scrollTouch: null

  _offsetSwipe: null
  _offsetScroll: null

  ctor: ->
    @_super()

    @_label = null
    @_startSwipe = 0
    @_offsetSwipe = 0
    @_offsetScroll = 0
    @_scrollTouch = null
    @_state = cpz.MenuLabelScrollLayerState.Idle

  initWithContentSizeAndFntFile: (size, fntFile) ->
    @setContentSize size

    @_clip = cc.ClippingNode.create()
    @_setStencil(@_clip, size)

    @_label = new cc.LabelBMFont()
    @_label.initWithString("", fntFile, 0, cc.TEXT_ALIGNMENT_LEFT)
    @_label.setAnchorPoint(cc.p(0.5, 1.0))

    @_clip.addChild(@_label)
    @addChild @_clip
    
    return true

  onExit: ->
    cc.SafeRelease @_stencil
    @removeChild @_clip

    @_super()

  getString: -> @_label.getString()
  setString: (str) ->
    @_label.setString(str)
    @

  getWidth: -> @_label.getContentSize().width
  setWidth: (width) ->
    @_label.setBoundingWidth(width - 20)
    @
    
  setAlignment: (alignment) ->
    @_label.setAlignment(alignment)
    @
    
  getSwipe: -> @_offsetSwipe
  setSwipe: (offsetSwipe) ->
    @_offsetSwipe = offsetSwipe
    
    @layout()
    @
    
  getScroll: -> @_offsetScroll
  setScroll: (@_offsetScroll) -> @
  
  setContentSize: (size) ->
    @_super(size)
    
    @layout()
    @

  layout: (anim = true) ->
    size = @getContentSize()

    if @_clip
      @_setStencil(@_clip, size)
    
    if @_label
      @_label.setPosition(cc.pAdd(cc.p(size.width / 2, size.height), cc.p(0, @_offsetScroll + @_offsetSwipe)))

  onTouchBegan: (touch, event) ->
    if not @_scrollTouch
      @_scrollTouch = touch
    else
      return false
    
    touchPoint = touch.getLocation()
    
    @_startSwipe = touchPoint.y
    @_state = cpz.MenuLabelScrollLayerState.Idle
    
    true
    
  onTouchMoved: (touch, event) ->
    return unless @_scrollTouch

    touchPoint = touch.getLocation()
    
    # If finger is dragged for more distance then minimum - start sliding and cancel pressed buttons.
    # Of course only if we not already in sliding mode
    if @_state isnt cpz.MenuLabelScrollLayerState.Sliding
      @_state = cpz.MenuLabelScrollLayerState.Sliding
      
      # Avoid jerk after state change.
      @_startSwipe = touchPoint.y
    
    if @_state is cpz.MenuLabelScrollLayerState.Sliding
      @setSwipe(touchPoint.y - @_startSwipe)
    
  onTouchEnded: (touch, event) ->
    return unless @_scrollTouch
    
    @_scrollTouch = null
    
    touchPoint = touch.getLocation()
    @setScroll(@getScroll() + touchPoint.y - @_startSwipe)
    @setSwipe(0)
    
  onTouchCancelled: (touch, event) ->
    @_scrollTouch = null
)
