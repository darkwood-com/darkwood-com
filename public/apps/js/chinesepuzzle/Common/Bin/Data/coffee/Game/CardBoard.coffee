###
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
###

cpz.CardBoardState =
  Empty: 1
  Yes: 2
  No: 3

cpz.CardBoard = cpz.Card.extend(
  _emptySprite: null
  _yesSprite: null
  _noSprite: null

  _state: null

  ctor: ->
    @_super()

    @_state = cpz.CardBoardState.Empty

  onExit: ->
    cc.SafeRelease @_emptySprite
    cc.SafeRelease @_yesSprite
    cc.SafeRelease @_noSprite
    @_super()

  initWithConf: (conf) ->
    return false unless @initWithTexture cc.textureNull(), 1

    @setConf conf

    true

  getState: -> @_state
  setState: (state, force = false) ->
    if @_state isnt state or force
      switch state
        when cpz.CardBoardState.Yes then @setSpriteBatchNode @_yesSprite
        when cpz.CardBoardState.No then @setSpriteBatchNode @_noSprite
        else @setSpriteBatchNode @_emptySprite

      @_state = state

  setConf: (conf) ->
    unless @_emptySprite
      @_emptySprite = cc.SpriteBatchNode.create cc.textureNull(), 1
      @_emptySprite.retain()
    unless @_yesSprite
      @_yesSprite = cc.SpriteBatchNode.create cc.textureNull(), 1
      @_yesSprite.retain()
    unless @_noSprite
      @_noSprite = cc.SpriteBatchNode.create cc.textureNull(), 1
      @_noSprite.retain()

    conf.getNodeThemePath 'cardboardempty', @_emptySprite
    conf.getNodeThemePath 'cardboardyes', @_yesSprite
    conf.getNodeThemePath 'cardboardno', @_noSprite

    @setState @getState, true
)

cpz.CardBoard.createWithConf = (conf) ->
  obj = new cpz.CardBoard()
  return obj if obj and obj.initWithConf(conf)
  null