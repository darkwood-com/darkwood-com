###
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
###

cpz.MenuGridScrollLayerState =
  Idle: 0
  Sliding: 1

cpz.MenuGrid = cc.Node.extend(
  _themesGrid: null #themes (=3x gridSize)

  _resetGrid: ->
    for coord in @_themesGrid.allKeys()
      theme = @_themesGrid.object(coord)
      @_clip.removeChild(theme, true)
    @_themesGrid.removeAllObjects()

    a = @_gridSize.width * @_gridSize.height
    pageMin = a * (@_page - 1)
    pageMax = a * (@_page + 2) - 1
    for k in [pageMin..pageMax]
      if(k >= 0 and k < @_themes.length)
        theme = @_themes[k]
        theme.setAnchorPoint cc.p(0.5, 0.5)
        @_clip.addChild(theme)

        p = Math.floor(k / a)
        coord = cc.p(p * @_gridSize.width + k % @_gridSize.width, @_gridSize.height - 1 - Math.floor((k - p * a) / @_gridSize.width))
        @_themesGrid.setObject theme, coord

    @layout()

  #The x coord of initial point the user starts their swipe.
  _startSwipe: 0

  #Internal state of scroll (scrolling or idle).
  _state: cpz.MenuGridScrollLayerState.Idle

  #Holds the touch that started the scroll
  _scrollTouch: null

  _swipeToPage_dt: 0
  _swipeToPage_start: 0
  _swipeToPage_end: 0
  _swipeToPage: (dt) ->

    @_swipeToPage_dt += dt
    duration = 0.3
    delta = (@_swipeToPage_end - @_swipeToPage_start)
    swipe = @_swipeToPage_end - delta * (1 - @_swipeToPage_dt / duration)
    @setSwipe(swipe)

    if(@_swipeToPage_dt >= duration)
      @setPage(@getPage() - Math.round(@getSwipe() / @getContentSize().width))

      @_delegate.scrollLayerScrolledToPageNumber(@, @_page) if @_delegate
      @unschedule @_swipeToPage
    
  _getMaxPage: ->
    return Math.ceil(@_themes.length / (@_gridSize.width * @_gridSize.height))

  _selectedItem: null

  _themes: []
  _gridSize: null
  _offsetSwipe: null
  _size: null

  _page: null
  _delegate: null
  _minimumTouchLengthToSlide: null
  _minimumTouchLengthToChangePage: null

  _stencil: null

  #clip
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

  ctor: ->
    @_super()

    @_themesGrid = new cc.Dictionary()
    @_gridSize = cc.size 0, 0
    @_page = 0
    @_delegate = null
    @_startSwipe = 0
    @_offsetSwipe = 0
    @_scrollTouch = null
    @_selectedItem = null
    @_state = cpz.MenuGridScrollLayerState.Idle

  init: ->
    @_themes = []

    # Set default minimum touch length to scroll.
    @_minimumTouchLengthToSlide = 10.5
    @_minimumTouchLengthToChangePage = 100.5

    @_clip = cc.ClippingNode.create()
    @addChild @_clip

    true

  initWithContentSize: (size) ->
    if @init()
      @setContentSize(size)
  
      # Set default minimum touch length to scroll.
      @_minimumTouchLengthToChangePage = size.width / 8

      @_setStencil(@_clip, size)

      return true

    false

  onExit: ->
    cc.SafeRelease @_stencil

    for theme in @_themes
      theme.release()

    @removeChild @_clip

    @_super()

  addTheme: (theme) ->
    theme.retain()
    @_themes.push theme

    @_resetGrid()
    @
  getGridSize: -> @_gridSize
  setGridSize: (@_gridSize) ->
    @_resetGrid()
    @
  getSwipe: -> @_offsetSwipe
  setSwipe: (@_offsetSwipe) ->
    @layout()
    @
  setContentSize: (size) ->
    @_super(size)
    @layout()
    @

  layout: (anim = true) ->
    size = @getContentSize()

    if @_clip
      @_setStencil(@_clip, size)

    if @_gridSize.width > 0 and @_gridSize.height > 0
      pad = cc.size(size.width / @_gridSize.width, size.height / @_gridSize.height)
      origin = cc.p(size.width / (2 * @_gridSize.width) - @_page * size.width, size.height / (2 * @_gridSize.height))

      for coord in @_themesGrid.allKeys()
        theme = @_themesGrid.object(coord)
        theme.setPosition(cc.pAdd(origin, cc.p(coord.x * pad.width + @_offsetSwipe, coord.y * pad.height)))

  getPage: -> @_page
  setPage: (page) ->
    if page >= 0 and page < @_getMaxPage()
      @_page = page
  
      @setSwipe(0)
      @_resetGrid()

    @

  updateTweenAction: (value, key) ->
    if key is 'swipe'
      @setSwipe(value)

  # Moves scrollLayer to page with given number & invokes delegate
  # method scrollLayer:scrolledToPageNumber: at the end of CCMoveTo action.
  # Does nothing if number >= totalScreens or < 0.
  swipeToPage: (page) ->
    if page >= 0 and page < @_getMaxPage()
      @_swipeToPage_dt = 0
      @_swipeToPage_start = @getSwipe()
      @_swipeToPage_end = (@_page - page) * @getContentSize().width
      @schedule @_swipeToPage
      #@runAction(cc.Sequence.create([
      #  cc.ActionTween.create(0.3, 'swipe', @getSwipe(), (@_page - page) * @getContentSize().width)
      #  cc.CallFunc.create @._swipeToPageEnded, @
      #]))

  getDelegate: -> @_delegate
  setDelegate: (@_delegate) -> @
  getMinimumTouchLengthToSlide: -> @_minimumTouchLengthToSlide
  setMinimumTouchLengthToSlide: (@_minimumTouchLengthToSlide) -> @
  getMinimumTouchLengthToChangePage: -> @_minimumTouchLengthToChangePage
  setMinimumTouchLengthToChangePage: (@_minimumTouchLengthToChangePage) -> @

  onTouchBegan: (touch, event) ->
    touchPoint = touch.getLocation()
    
    if @_themes and @_themes.length > 0
      for child in @_themes
        if child instanceof cc.MenuItem and child.isVisible() and child.isEnabled()
          local = child.convertToNodeSpace(touchPoint)
          r = child.rect()
          r.x = 0
          r.y = 0

          if cc.rectContainsPoint(r, local)
            if @_selectedItem
              @_selectedItem.unselected()
            
            @_selectedItem = child
            @_selectedItem.selected()
            @_selectedItem.activate()
            @_selectedItem.runAction(cc.Sequence.create([
              cc.EaseIn.create(cc.ScaleTo.create(0.1, 0.75), 2.5),
              cc.EaseOut.create(cc.ScaleTo.create(0.1, 1.0), 2.5),
            ]))
    
    unless @_scrollTouch
      @_scrollTouch = touch
    else
      return false
    
    @_startSwipe = touchPoint.x
    @_state = cpz.MenuGridScrollLayerState.Idle
    
    return true
    
  onTouchMoved: (touch, event) ->
    return unless @_scrollTouch
    
    touchPoint = touch.getLocation()
    
    # If finger is dragged for more distance then minimum - start sliding and cancel pressed buttons.
    # Of course only if we not already in sliding mode
    if @_state isnt cpz.MenuGridScrollLayerState.Sliding and Math.abs(touchPoint.x - @_startSwipe) >= @_minimumTouchLengthToSlide
      @_state = cpz.MenuGridScrollLayerState.Sliding
      
      # Avoid jerk after state change.
      @_startSwipe = touchPoint.x

      @_delegate.scrollLayerScrollingStarted(@) if @_delegate
    
    if @_state is cpz.MenuGridScrollLayerState.Sliding
      @setSwipe(touchPoint.x - @_startSwipe)
    
  onTouchEnded: (touch, event) ->
    return unless @_scrollTouch
    
    @_scrollTouch = null
    
    touchPoint = touch.getLocation()
    
    selectedPage = @getPage()
    swipe = touchPoint.x - @_startSwipe
    
    @setSwipe(swipe)
    if swipe > 0 and swipe >= @_minimumTouchLengthToChangePage
      selectedPage -= ((@getSwipe() - @_minimumTouchLengthToChangePage) / @getContentSize().width) + 1
    else if(swipe < 0 and swipe <= -@_minimumTouchLengthToChangePage)
      selectedPage += - ((@getSwipe() + @_minimumTouchLengthToChangePage) / @getContentSize().width) + 1
    if selectedPage < 0 then selectedPage = 0
    if selectedPage >= @_getMaxPage() then selectedPage = @_getMaxPage() - 1

    @swipeToPage(selectedPage)

  onTouchCancelled: (touch, event) ->
    @_scrollTouch = null
    @setPage(@_page)
)
