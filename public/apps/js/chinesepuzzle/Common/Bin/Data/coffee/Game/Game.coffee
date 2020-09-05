###
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
###

cpz.CheckMove =
  From: 0
  To: 1
  ToBefore: 2
  Ko: 3
  Ok: 4

cpz.Game = cc.Layer.extend(
  _touchListener: null,

  _board: {} #board game that reference to the deck cards

  _gs: null
  _gl: null
  _gc: null

  #input touches/mouse
  _lastTouchLocation: null
  _dragCard: null
  _dragCardCoord: null
  _touchLastCard: null #visible hint last touched card, for semi-auto move
  _hintCard: null #card play hint on over card board
  _switchBoardCard: null #board card used for switch

  _makeMoveEnd: ->
    @_switchBoardCard.setVisible false

    if @_dragCard
      @reorderChild @_dragCard, cpz.GameZOrder.Card
      @_dragCard = null

    @_hintMove()

  _makeMoveSound: ->
    @getGameScene().playSound 'card_move'

  _makeMoveUndoSound: ->
    @getGameScene().playSound 'card_undo'

  _hintMove: ->
    if @_dragCard
      cTo = @_gc.checkRectNode @_dragCard, cpz.Game.filterCardBoard
      if cTo
        if @_hintCard and @_hintCard isnt cTo
          @_hintCard.setState cpz.CardBoardState.Empty
        @_hintCard = cTo

        coord = @_gl.getPositionInGridCoord cTo.getPosition()
        move = cpz.mv @_dragCardCoord, coord
        if @checkMoveCoord(move) is cpz.CheckMove.Ok
          @_hintCard.setState cpz.CardBoardState.Yes
        else
          @_hintCard.setState cpz.CardBoardState.No
      else if @_hintCard
        @_hintCard.setState cpz.CardBoardState.Empty
        @_hintCard = null
    else if @_hintCard
      @_hintCard.setState cpz.CardBoardState.Empty
      @_hintCard = null

  _randInitBoard: ->
    deck = []

    for k in [1..2]
      for color in cc.ObjectValues(cpz.CardPlayColor)
        for rank in cc.ObjectValues(cpz.CardPlayRank)
          deck.push
            color: color
            rank: rank

    deck = cc.ArrayShuffle deck

    initBoard = @_gs.getConf().getInitBoard()
    initBoard.removeAllObjects()

    k = 0
    for i in [0..7]
      for j in [1..13]
        coord = cpz.gc i, j

        initBoard.setObject deck[k], coord
        k++

  _loadBoard: ->
    conf = @_gs.getConf()

    cardBoards = []
    cardPlays = []
    for i in [0..7]
      for j in [0..13]
        card = @_board[i][j]

        if card instanceof cpz.CardPlay
          cardPlays.push card
        else if card instanceof cpz.CardBoard
          cardBoards.push card

    for i in [0..7]
      card = null
      for c in cardBoards
        card = c
        cc.arrayRemoveObject(cardBoards, c)
        break
      unless card
        card = cpz.CardBoard.createWithConf conf

      coord = cpz.gc i, 0
      @_board[coord.i][coord.j] = card

    initBoard = @_gs.getConf().getInitBoard()
    for coord in initBoard.allKeys()
      data = initBoard.object(coord)

      card = null
      for c in cardPlays
        if data.color is c.getColor() and data.rank is c.getRank()
          card = c
          cc.arrayRemoveObject(cardPlays, c)
          break
      unless card
        card = cpz.CardPlay.decode(conf, data)

      card.setIsLocked false
      @_board[coord.i][coord.j] = card

    for move in @_gs.getConf().getMoves()
      cSwitch = @getCard move.to
      @_board[move.to.i][move.to.j] = @_board[move.from.i][move.from.j]
      @_board[move.from.i][move.from.j] = cSwitch

    for i in [0..7]
      for j in [0..13]
        coord = cpz.gc i, j
        card = @getCard coord

        unless card.getParent()
          @_gc.addNode card
          @addChild card, cpz.GameZOrder.Card
          card.setPosition @_gl.getPositionInBoardPoint(coord)

  ctor: ->
    @_super()

    for i in [0..7]
      @_board[i] = {}
      for j in [0..13]
        @_board[i][j] = null

  initWithGameScene: (gs) ->
    return false unless @init()

    @_gs = gs
    @_gl = new cpz.GameLayout(@)
    #@_gc = new cpz.GameControlChipmunk()
    @_gc = new cpz.GameControlNode()

    cc.eventManager.addListener(cc.EventListener.create(
      event: cc.EventListener.TOUCH_ONE_BY_ONE
      swallowTouches: true

      onTouchBegan: (touch, event) =>
        location = touch.getLocation()
        @tapDownAt(location)
        true

      onTouchMoved: (touch, event) =>
        location = touch.getLocation()
        @tapMoveAt(location)

      onTouchEnded: (touch, event) =>
        location = touch.getLocation()
        @tapUpAt(location)

      onTouchCancelled: (touch, event) =>
        location = touch.getLocation()
        @tapUpAt(location)
    ), @)

    @layout()

    initBoard = @_gs.getConf().getInitBoard()
    if initBoard.count() is 0
      @_randInitBoard()

    @_loadBoard()

    @layout()
    @schedule(@step)

    true

  newGame: ->
    @_randInitBoard()
    @retryGame()

  retryGame: ->
    @_gs.getConf().clearMoves()

    @_loadBoard()

    @layout()

    @_gs.getConf().save() #save conf state

  step: (dt) ->
    #update game step
    @_gc.step(dt)

  layout: (anim = true) ->
    @_gl.layout anim

    conf = @_gs.getConf()

    unless @_touchLastCard
      @_touchLastCard = new cpz.Card()
      @_touchLastCard.initWithTexture cc.textureNull(), 4
      @_touchLastCard.setVisible false
      @addChild @_touchLastCard, cpz.GameZOrder.HintCard
    conf.getNodeThemePath 'cardtouched', @_touchLastCard

    unless @_switchBoardCard
      @_switchBoardCard = new cpz.CardBoard()
      @_switchBoardCard.initWithTexture cc.textureNull(), 4
      @_switchBoardCard.setVisible false
      @addChild @_switchBoardCard, cpz.GameZOrder.Board
    @_switchBoardCard.setConf conf

    for i in [0..7]
      for j in [0..13]
        coord = cpz.gc i, j

        card = @getCard coord
        if card
          card.setConf conf

          if card instanceof cpz.CardBoard
            card.setPosition @_gl.getPositionInBoardPoint coord
          else if card instanceof cpz.CardPlay
            coordPos = @_gl.getPositionInBoardPoint coord
            if anim
              #card animation
              actions = []
              actions.push cc.DelayTime.create(0.05 * (7 - coord.i + coord.j - 1))
              unless cc.pointEqualToPoint(card.getPosition(), coordPos)
                actions.push cc.MoveTo.create(1.0, coordPos)
              unless card.getIsFaceUp()
                actions.push cc.OrbitCamera.create(0.1, 1, 0, 0, 90, 0, 0)
                actions.push cc.CallFunc.create card.setIsFaceUp, card, true
                actions.push cc.OrbitCamera.create(0.1, 1, 0, 270, 90, 0, 0)
              card.runAction cc.Sequence.create(actions)
            else
              card.setPosition coordPos
              card.setIsFaceUp true

      @lockLine i

  isBusy: ->
    for i in [0..7]
      for j in [0..13]
        if @_board[i][j] and @_board[i][j].getNumberOfRunningActions() > 0
          return true
    false

  getCard: (coord) ->
    if 0 <= coord.i and coord.i < 8 and 0 <= coord.j and coord.j < 14 then @_board[coord.i][coord.j] else null

  checkMoveCoord: (move) ->
    cFrom = @getCard move.from
    return cpz.CheckMove.From unless cFrom instanceof cpz.CardPlay
    cTo = @getCard move.to
    return cpz.CheckMove.From unless cTo instanceof cpz.CardBoard
    
    toBefore = cpz.gc move.to.i, move.to.j
    toBefore.j--
    
    if toBefore.j is -1
      return cpz.CheckMove.Ok if cFrom.getRank() is cpz.CardPlayRank.Ace
    else
      cToBefore = @getCard(toBefore)
      return cpz.CheckMove.ToBefore unless cToBefore instanceof cpz.CardPlay
      return cpz.CheckMove.Ok if cFrom.isNextToCardPlay(cToBefore)

    cpz.CheckMove.Ko

  checkMoveCard: (from, to) ->
    @checkMoveCoord(cpz.mv(@_gl.getPositionInGridCoord(from), @_gl.getPositionInGridCoord(to)))

  makeMoveCoord: (move) ->
    check = @checkMoveCoord(move)
    
    cFrom = @getCard move.from
    if check is cpz.CheckMove.Ok
      #drop is valid : apply changes and switch
      cSwitch = @getCard(move.to)
      @_board[move.to.i][move.to.j] = @_board[move.from.i][move.from.j]
      @_board[move.from.i][move.from.j] = cSwitch
      cFrom.runAction cc.Sequence.create([
        cc.MoveTo.create(0.5, @_gl.getPositionInBoardPoint(move.to)),
        cc.CallFunc.create(@_makeMoveEnd, @),
        cc.CallFunc.create(@_makeMoveSound, @)
      ])

      if(cSwitch)
        cSwitch.setPosition @_gl.getPositionInBoardPoint(move.from)

        if cSwitch instanceof cpz.CardBoard
          cSwitch.setState cpz.CardBoard.Empty

      @_switchBoardCard.setPosition @_gl.getPositionInBoardPoint(move.to)
  
      #check and set lock for line cards
      @lockLine(move.to.i)
  
      @_gs.getConf().pushMove(move)
      @_gs.getConf().save() #save conf state
    else if(cFrom)
      #drop is invalid : undo changes
      cFrom.runAction cc.Sequence.create([
        cc.MoveTo.create(0.5, @_gl.getPositionInBoardPoint(move.from)),
        cc.CallFunc.create(@_makeMoveEnd, @)
      ])
    else
      @makeMoveEnd()

    check

  makeMoveCard: (from, to) ->
    @makeMoveCoord(cpz.mv(@_gl.getPositionInGridCoord(from), @_gl.getPositionInGridCoord(to)))

  undoMove: ->
    return if @_gs.getConf().getMoves().length is 0 or @isBusy()
    move = @_gs.getConf().popMove()

    cTo = @getCard move.to
    cSwitch = @getCard move.from

    @_board[move.from.i][move.from.j] = @_board[move.to.i][move.to.j]
    @_board[move.to.i][move.to.j] = cSwitch
    cTo.runAction cc.Sequence.create([
      cc.MoveTo.create(0.5, @_gl.getPositionInBoardPoint(move.from)),
      cc.CallFunc.create(@_makeMoveEnd, @),
      cc.CallFunc.create(@_makeMoveUndoSound, @)
    ])

    if cSwitch
      cSwitch.setPosition @_gl.getPositionInBoardPoint(move.to)

      if cSwitch instanceof cpz.CardBoard
        cSwitch.setState cpz.CardBoardState.Empty

    @_switchBoardCard.setPosition @_gl.getPositionInBoardPoint(move.from)
    @_switchBoardCard.setVisible true

    #check and set lock for line cards
    @lockLine(move.from.i)
    @lockLine(move.to.i)

    move

  lockLine: (i) ->
    return 0 if i not in [0..7]

    nb = 0

    for j in [0..13]
      card = @_board[i][j]
      continue unless card instanceof cpz.CardPlay

      cBefore = if j is 0 then null else @_board[i][j - 1]

      if j is 0 and card.getRank() is cpz.CardPlayRank.Ace or cBefore instanceof cpz.CardPlay and cBefore.getIsLocked() and card.isNextToCardPlay(cBefore)
        card.setIsLocked true
        nb++
      else
        card.setIsLocked false

    nb

  tapDownAt: (location) ->
    return if @_gl.tapDownAt(location)

    if not @_dragCard and not @isBusy()
      tapCard = @_gc.checkPoint(location)

      if tapCard instanceof cpz.CardBoard and @_touchLastCard.isVisible() and @checkMoveCard(@_touchLastCard, tapCard) is cpz.CheckMove.Ok
        @_switchBoardCard.setPosition @_touchLastCard.getPosition()
        @_switchBoardCard.setVisible true
        @makeMoveCard @_touchLastCard, tapCard

      if tapCard instanceof cpz.CardPlay and not tapCard.getIsLocked()
        @_dragCardCoord = @_gl.getPositionInGridCoord tapCard.getPosition()
        @_dragCard = tapCard
        dragCardPos = @_gl.getPositionInBoardPoint @_dragCardCoord

        @_switchBoardCard.setPosition dragCardPos
        @_switchBoardCard.setVisible true
        @_touchLastCard.setPosition dragCardPos

        @reorderChild @_dragCard, cpz.GameZOrder.MoveCard

      @_touchLastCard.setVisible false

    @_hintMove()
    @_lastTouchLocation = cc.p(location)

  tapMoveAt: (location) ->
    return if @_gl.tapMoveAt(location)

    @_touchLastCard.setVisible false

    movePos = cc.pAdd location, cc.pNeg(@_lastTouchLocation)
    if(@_dragCard)
      @_dragCard.setPosition cc.pAdd(@_dragCard.getPosition(), movePos)

    @_hintMove()
    @_lastTouchLocation = cc.p(location)

  tapUpAt: (location) ->
    return if @_gl.tapUpAt(location)

    if(@_dragCard)
      #check drop
      cToCard = @_gc.checkRectNode @_dragCard, cpz.Game.filterCardBoard
      cToCard = @_dragCard unless cToCard
      coord = @_gl.getPositionInGridCoord cToCard.getPosition()
      move = cpz.mv @_dragCardCoord, coord
      check = @makeMoveCoord move
      if check isnt cpz.CheckMove.Ok and cc.pointEqualToPoint @_dragCard.getPosition(), @_touchLastCard.getPosition()
        @_touchLastCard.setVisible true

      @_dragCard = null

    @_lastTouchLocation = cc.p(location)

  getGameScene: -> @_gs
  getLayout: -> @_gl
  getControl: -> @_gc
)

cpz.Game.create = (gs) ->
  obj = new cpz.Game()
  return obj if obj and obj.initWithGameScene(gs)
  null

cpz.Game.filterCardBoard = (node) ->
  return node instanceof cpz.CardBoard
