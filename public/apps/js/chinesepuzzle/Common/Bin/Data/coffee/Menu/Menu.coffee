###
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
###

cpz.Menu = cc.Layer.extend(
  _touchListener: null,

  _nav: []

  _gs: null
  _ml: null

  ctor: ->
    @_super()

  onExit: ->
    while @popNav() then

    @_super()

  initWithGameSceneAndLayout: (gs, layout) ->
    return false unless @init()

    @_gs = gs
    @_ml = new cpz.MenuLayout(@)
    @_ml.initWithType layout

    cc.eventManager.addListener(cc.EventListener.create(
      event: cc.EventListener.TOUCH_ONE_BY_ONE
      swallowTouches: true

      onTouchBegan: (touch, event) =>
        if @_nav.length > 0
          mc = @_nav[@_nav.length - 1]
          mc.onTouchBegan(touch, event)

        return true

      onTouchMoved: (touch, event) =>
        if @_nav.length > 0
          mc = @_nav[@_nav.length - 1]
          mc.onTouchMoved(touch, event)

      onTouchEnded: (touch, event) =>
        if @_nav.length > 0
          mc = @_nav[@_nav.length - 1]
          mc.onTouchEnded(touch, event)

      onTouchCancelled: (touch, event) =>
        if @_nav.length > 0
          mc = @_nav[@_nav.length - 1]
          mc.onTouchCancelled(touch, event)
    ), @)

    @layout()
    @schedule(@step)

    true

  step: (dt) ->

  layout: (anim = true) ->
    @_ml.layout anim

  pushNav: (mBox) ->
    if @_nav.length > 0
      @removeChild @_nav[@_nav.length - 1], true

    @_nav.push mBox
    @addChild mBox

    @getGameScene().playSound 'menu_push'

  popNav: ->
    if @_nav.length > 0
      mBox = @_nav[@_nav.length - 1]
      cc.arrayRemoveObject @_nav, mBox
      @removeChild mBox, true
      
      if @_nav.length > 0
        @addChild @_nav[@_nav.length - 1]
      else
        @getGameScene().playSound 'menu_pop'

      return mBox
      
    return null

  okMenu: (item) ->
    @popNav()

    @_gs.game() if @_nav.length is 0

  getGameScene: -> @_gs
  getLayout: -> @_ml
)

cpz.Menu.create = (gs, layout) ->
  obj = new cpz.Menu()
  return obj if obj and obj.initWithGameSceneAndLayout(gs, layout)
  null
