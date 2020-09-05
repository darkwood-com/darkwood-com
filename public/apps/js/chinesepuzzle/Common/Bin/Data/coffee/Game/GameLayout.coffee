###
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
###

cpz.GameZOrder =
  BG: 0
  UI: 1
  Board: 2
  Card: 3
  MoveCard: 4
  HintCard: 5

cpz.GameLayout = cc.Class.extend(
  _layoutRes: (key) ->
    if cpz.GameLayout._res is null
      cpz.GameLayout._res =
        '480x320':
          gridCardSize: cc.size(26,36)
          gridSpaceSize: cc.size(4, 4)
          gridPosition: cc.p(4, 0)
          newBtn: cc.p(451,290)
          retryBtn: cc.p(451,238)
          undoBtn: cc.p(451,186)
          hintBtn: cc.p(451,134)
          soundBtn: cc.p(451,82)
          themeBtn: cc.p(451,30)
        '960x640':
          gridCardSize: cc.size(52,72)
          gridSpaceSize: cc.size(8, 8)
          gridPosition: cc.p(8, 0)
          newBtn: cc.p(902,580)
          retryBtn: cc.p(902,476)
          undoBtn: cc.p(902,372)
          hintBtn: cc.p(902,268)
          soundBtn: cc.p(902,164)
          themeBtn: cc.p(902,60)
        '1024x768':
          gridCardSize: cc.size(56,78)
          gridSpaceSize: cc.size(10, 10)
          gridPosition: cc.p(10, 32)
          newBtn: cc.p(977,714)
          retryBtn: cc.p(977,582)
          undoBtn: cc.p(977,450)
          hintBtn: cc.p(977,318)
          soundBtn: cc.p(977,186)
          themeBtn: cc.p(977,56)
        '1280x800':
          gridCardSize: cc.size(56,78)
          gridSpaceSize: cc.size(10, 10)
          gridPosition: cc.p(118, 48)
          newBtn: cc.p(1220,740)
          retryBtn: cc.p(1220,604)
          undoBtn: cc.p(1220,468)
          hintBtn: cc.p(1220,332)
          soundBtn: cc.p(1220,196)
          themeBtn: cc.p(1220,60)
        '1280x1024':
          gridCardSize: cc.size(66,92)
          gridSpaceSize: cc.size(12, 12)
          gridPosition: cc.p(24, 96)
          newBtn: cc.p(1210,952)
          retryBtn: cc.p(1210,776)
          undoBtn: cc.p(1210,600)
          hintBtn: cc.p(1210,424)
          soundBtn: cc.p(1210,248)
          themeBtn: cc.p(1210,72)
        '1366x768':
          gridCardSize: cc.size(56,78)
          gridSpaceSize: cc.size(10, 10)
          gridPosition: cc.p(158, 32)
          newBtn: cc.p(1303,714)
          retryBtn: cc.p(1303,582)
          undoBtn: cc.p(1303,450)
          hintBtn: cc.p(1303,318)
          soundBtn: cc.p(1303,186)
          themeBtn: cc.p(1303,56)
        '1440x900':
          gridCardSize: cc.size(66,92)
          gridSpaceSize: cc.size(12, 12)
          gridPosition: cc.p(104, 34)
          newBtn: cc.p(1370,830)
          retryBtn: cc.p(1370,678)
          undoBtn: cc.p(1370,526)
          hintBtn: cc.p(1370,374)
          soundBtn: cc.p(1370,222)
          themeBtn: cc.p(1370,70)
        '1680x1050':
          gridCardSize: cc.size(73,100)
          gridSpaceSize: cc.size(20, 20)
          gridPosition: cc.p(99, 45)
          newBtn: cc.p(1590,960)
          retryBtn: cc.p(1590,786)
          undoBtn: cc.p(1590,612)
          hintBtn: cc.p(1590,438)
          soundBtn: cc.p(1590,264)
          themeBtn: cc.p(1590,90)
        '1920x1080':
          gridCardSize: cc.size(73,100)
          gridSpaceSize: cc.size(20, 20)
          gridPosition: cc.p(214, 60)
          newBtn: cc.p(1825,985)
          retryBtn: cc.p(1825,807)
          undoBtn: cc.p(1825,629)
          hintBtn: cc.p(1825,451)
          soundBtn: cc.p(1825,273)
          themeBtn: cc.p(1825,95)
        '1920x1200':
          gridCardSize: cc.size(73,100)
          gridSpaceSize: cc.size(20, 20)
          gridPosition: cc.p(214, 120)
          newBtn: cc.p(1825,1105)
          retryBtn: cc.p(1825,903)
          undoBtn: cc.p(1825,701)
          hintBtn: cc.p(1825,499)
          soundBtn: cc.p(1825,297)
          themeBtn: cc.p(1825,95)

    res = @_game.getGameScene().getConf().getResolution()
    cpz.GameLayout._res[res][key]
    
  _actionBtn: (btn) ->
    if btn is @_newBtn
      @_game.getGameScene().menuWithLayout cpz.MenuLayoutType.NewGame
    else if btn is @_retryBtn
      @_game.getGameScene().menuWithLayout cpz.MenuLayoutType.RetryGame
    else if btn is @_undoBtn
      @_game.undoMove()
      @_game.getGameScene().getConf().save()
    else if btn is @_hintBtn
      @_game.getGameScene().menuWithLayout cpz.MenuLayoutType.Hint
    else if btn is @_soundBtn
      conf = @_game.getGameScene().getConf()
      conf.setIsSoundOn !conf.getIsSoundOn()
      conf.save()

      isSoundOn = conf.getIsSoundOn()
      @_game.getGameScene().playBackgroundMusic isSoundOn
      if isSoundOn
        conf.getNodeThemePath 'soundOnBtn', @_soundBtn
      else
        conf.getNodeThemePath 'soundOffBtn', @_soundBtn
    else if btn is @_themeBtn
      @_game.getGameScene().menuWithLayout cpz.MenuLayoutType.Theme

  _game: null
  _activesBtn: []

  _bg: null

  #button layout
  _newBtn: null
  _retryBtn: null
  _hintBtn: null
  _undoBtn: null
  _soundBtn: null
  _themeBtn: null

  #card grid layout stuff
  _gridCardSize: null
  _gridSpaceSize: null
  _gridPosition: null

  ctor: (game) ->
    @_game = game
    @_gridCardSize = cc.size(0, 0)
    @_gridSpaceSize = cc.size(0, 0)
    @_gridPosition = cc.size(0, 0)

  layout: (anim = true) ->
    conf = @_game.getGameScene().getConf()

    @_gridCardSize = @_layoutRes 'gridCardSize'
    @_gridSpaceSize = @_layoutRes 'gridSpaceSize'
    @_gridPosition = @_layoutRes 'gridPosition'

    if @_bg is null
      @_bg = cc.SpriteBatchNode.create cc.textureNull()
      @_game.addChild @_bg, cpz.GameZOrder.BG
    conf.getNodeThemePath 'bg', @_bg
    @_bg.setPosition cc.p(0, 0)
    @_bg.setAnchorPoint cc.p(0, 0)

    if @_newBtn is null
      @_newBtn = cc.SpriteBatchNode.create cc.textureNull()
      @_game.addChild @_newBtn, cpz.GameZOrder.UI
      @_activesBtn.push @_newBtn
    conf.getNodeThemePath 'newBtn', @_newBtn
    @_newBtn.setPosition @_layoutRes('newBtn')
    @_newBtn.setScale 0.75

    if @_retryBtn is null
      @_retryBtn = cc.SpriteBatchNode.create cc.textureNull()
      @_game.addChild @_retryBtn, cpz.GameZOrder.UI
      @_activesBtn.push @_retryBtn
    conf.getNodeThemePath 'retryBtn', @_retryBtn
    @_retryBtn.setPosition @_layoutRes('retryBtn')
    @_retryBtn.setScale 0.75

    if @_undoBtn is null
      @_undoBtn = cc.SpriteBatchNode.create cc.textureNull()
      @_game.addChild @_undoBtn, cpz.GameZOrder.UI
      @_activesBtn.push @_undoBtn
    conf.getNodeThemePath 'undoBtn', @_undoBtn
    @_undoBtn.setPosition @_layoutRes('undoBtn')
    @_undoBtn.setScale 0.75

    if @_hintBtn is null
      @_hintBtn = cc.SpriteBatchNode.create cc.textureNull()
      @_game.addChild @_hintBtn, cpz.GameZOrder.UI
      @_activesBtn.push @_hintBtn
    conf.getNodeThemePath 'hintBtn', @_hintBtn
    @_hintBtn.setPosition @_layoutRes('hintBtn')
    @_hintBtn.setScale 0.75

    if @_soundBtn is null
      @_soundBtn = cc.SpriteBatchNode.create cc.textureNull()
      @_game.addChild @_soundBtn, cpz.GameZOrder.UI
      @_activesBtn.push @_soundBtn
    if conf.getIsSoundOn()
      conf.getNodeThemePath 'soundOnBtn', @_soundBtn
    else
      conf.getNodeThemePath 'soundOffBtn', @_soundBtn
    @_soundBtn.setPosition @_layoutRes('soundBtn')
    @_soundBtn.setScale 0.75

    if @_themeBtn is null
      @_themeBtn = cc.SpriteBatchNode.create cc.textureNull()
      @_game.addChild @_themeBtn, cpz.GameZOrder.UI
      @_activesBtn.push @_themeBtn
    conf.getNodeThemePath 'themeBtn', @_themeBtn
    @_themeBtn.setPosition @_layoutRes('themeBtn')
    @_themeBtn.setScale 0.75

  tapDownAt: (location) ->
    size = 2

    for btn in @_activesBtn
      local = btn.convertToNodeSpace location
      rect = btn.getBoundingBox()
      rect.x = rect.width * (1 - size) / 2
      rect.y = rect.height * (1 - size) / 2
      rect.width = rect.width * size
      rect.height = rect.height * size

      if cc.rectContainsPoint rect, local
        btn.runAction cc.Sequence.create [
          cc.EaseIn.create(cc.ScaleTo.create(0.1, 1.0), 2.0),
          cc.CallFunc.create(@_actionBtn, @, btn),
          cc.EaseOut.create(cc.ScaleTo.create(0.1, 0.75), 2.0),
        ]

    false

  tapMoveAt: (location) -> false
  tapUpAt: (location) -> false

  getPositionInBoardPoint: (coord) ->
    cc.p @_gridPosition.x + (0.5 + coord.j) * (@_gridCardSize.width + @_gridSpaceSize.width),
         @_gridPosition.y + (0.5 + coord.i) * (@_gridCardSize.height + @_gridSpaceSize.height)

  getPositionInGridCoord: (point) ->
    cpz.gc Math.floor((point.y - @_gridPosition.y - 0.5) / (@_gridCardSize.height + @_gridSpaceSize.height)),
           Math.floor((point.x - @_gridPosition.x - 0.5) / (@_gridCardSize.width + @_gridSpaceSize.width))
)

cpz.GameLayout._res = null