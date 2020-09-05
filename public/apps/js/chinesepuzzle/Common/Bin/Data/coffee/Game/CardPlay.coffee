###
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
###

cpz.CardPlayColor =
  Spade: 1
  Club: 2
  Heart: 3
  Diamond: 4

cpz.CardPlayRank =
  Ace: 1
  Two: 2
  Three: 3
  Four: 4
  Five: 5
  Six: 6
  Seven: 7
  Eight: 8
  Nine: 9
  Ten: 10
  Jack: 11
  Queen: 12
  King: 13

cpz.CardPlay = cpz.Card.extend(
  _faceSprite: null
  _backSprite: null

  _color: null
  _rank: null
  _isLocked: false
  _isFaceUp: false

  onExit: ->
    cc.SafeRelease @_faceSprite
    cc.SafeRelease @_backSprite
    @_super()

  initWithConf: (conf) ->
    return false unless @initWithTexture cc.textureNull(), 16

    @setConf conf

    true

  initWithConfAndColorAndRank: (conf, color, rank) ->
    return false unless @initWithConf conf

    @_color = color
    @_rank = rank

    true

  encode: ->
    color: @_color
    rank: @_rank

  decode: (data) ->
    @_color = data['color']
    @_rank = data['rank']
    @

  isNextToCardPlay: (cardPlay) ->
    cardPlay and @_color is cardPlay._color and @_rank is cardPlay._rank + 1

  setConf: (conf) ->
    unless @_faceSprite
      @_faceSprite = cc.SpriteBatchNode.create cc.textureNull()
      @_faceSprite.retain()
    unless @_backSprite
      @_backSprite = cc.SpriteBatchNode.create cc.textureNull()
      @_backSprite.retain()

    conf.getNodeThemePath 'card_' + cpz.CardPlay.matchColor(@_color) + cpz.CardPlay.matchRank(@_rank), @_faceSprite
    conf.getNodeThemePath 'cardplaybg', @_backSprite

    @setIsFaceUp @getIsFaceUp(), true

  getColor: -> @_color
  getRank: -> @_rank
  getIsLocked: -> @_isLocked
  setIsLocked: (@_isLocked) -> @
  getIsFaceUp: -> @_isFaceUp
  setIsFaceUp: (isFaceUp, force) ->
    if @_isFaceUp isnt isFaceUp or force
      @_isFaceUp = isFaceUp

      if @_isFaceUp
        @setSpriteBatchNode(@_faceSprite)
      else
        @setSpriteBatchNode(@_backSprite)

    @
)

cpz.CardPlay.createWithConfAndColorAndRank = (conf, color, rank) ->
  obj = new cpz.CardPlay()
  return obj if obj and obj.initWithConfAndColorAndRank(conf, color, rank)
  null

cpz.CardPlay.decode = (conf, data) ->
  obj = new cpz.CardPlay()
  obj.decode(data)
  return obj if obj and obj.initWithConf(conf)
  null

cpz.CardPlay.matchColor = (color) ->
  if typeof color is 'string'
    switch color
      when 'S' then cpz.CardPlayColor.Spade
      when 'C' then cpz.CardPlayColor.Club
      when 'H' then cpz.CardPlayColor.Heart
      when 'D' then cpz.CardPlayColor.Diamond
      else cpz.CardPlayColor.Spade
  else
    switch color
      when cpz.CardPlayColor.Spade then 'S'
      when cpz.CardPlayColor.Club then 'C'
      when cpz.CardPlayColor.Heart then 'H'
      when cpz.CardPlayColor.Diamond then 'D'
      else 'D'

cpz.CardPlay.matchRank = (rank) ->
  if typeof rank is 'string'
    switch rank
      when 'A' then cpz.CardPlayRank.Ace
      when '2' then cpz.CardPlayRank.Two
      when '3' then cpz.CardPlayRank.Three
      when '4' then cpz.CardPlayRank.Four
      when '5' then cpz.CardPlayRank.Five
      when '6' then cpz.CardPlayRank.Six
      when '7' then cpz.CardPlayRank.Seven
      when '8' then cpz.CardPlayRank.Eight
      when '9' then cpz.CardPlayRank.Nine
      when '10' then cpz.CardPlayRank.Ten
      when 'J' then cpz.CardPlayRank.Jack
      when 'Q' then cpz.CardPlayRank.Queen
      when 'K' then cpz.CardPlayRank.King
      else cpz.CardPlayRank.Ace
  else
    switch rank
      when cpz.CardPlayRank.Ace then 'A'
      when cpz.CardPlayRank.Two then '2'
      when cpz.CardPlayRank.Three then '3'
      when cpz.CardPlayRank.Four then '4'
      when cpz.CardPlayRank.Five then '5'
      when cpz.CardPlayRank.Six then '6'
      when cpz.CardPlayRank.Seven then '7'
      when cpz.CardPlayRank.Eight then '8'
      when cpz.CardPlayRank.Nine then '9'
      when cpz.CardPlayRank.Ten then '10'
      when cpz.CardPlayRank.Jack then 'J'
      when cpz.CardPlayRank.Queen then 'Q'
      when cpz.CardPlayRank.King then 'K'
      else 'A'
