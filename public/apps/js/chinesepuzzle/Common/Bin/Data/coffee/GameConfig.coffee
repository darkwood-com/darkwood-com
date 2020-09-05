###
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
###

cpz.XML_FILE_NAME = "chinesepuzzle.data"

cpz.GameConfig = cc.Class.extend(
  _getNodePath: (mode, file, sprite) ->
    path = cpz.CommonPath + @_resolution + '/' + (if mode is 'theme' then 'themes/' + @_theme else 'ui')
    plistPath = path + '.plist'
    texturePath = path + '.png'
    isCardLayout = if @_isCardLayout then '1' else '0'
    nodePath = path + ':' + isCardLayout + ':' + file

    node = cpz.GameConfig._configPaths[nodePath]
    unless node
      spriteFrameCache = cc.spriteFrameCache
      spriteFrameCache.removeSpriteFramesFromFile(plistPath)
      spriteFrameCache.addSpriteFrames(plistPath)

      sprites = {}
      if mode is 'ui'
        sprites['menuMask'] =                  'auto'
        sprites['menuContainer'] =             'auto'
        sprites['menuItemYes'] =               'auto'
        sprites['menuItemNo'] =                'auto'
        sprites['menuItemOk'] =                'auto'
        sprites['menuItemThemeClassic'] =      'auto'
        sprites['menuItemThemeChinese'] =      'auto'
        sprites['menuItemThemeCircle'] =       'auto'
        sprites['menuItemThemePolkadots'] =    'auto'
        sprites['menuItemThemeSeamless'] =     'auto'
        sprites['menuItemThemeSkullshearts'] = 'auto'
        sprites['menuItemThemeSplash'] =       'auto'
        sprites['menuItemThemeSpring'] =       'auto'
        sprites['menuItemThemeStripes'] =      'auto'
        sprites['menuItemThemeVivid'] =        'auto'
      else if mode is 'theme'
        sprites['bg'] =					               'auto'
        sprites['cardplaybg'] =			           'auto'
        sprites['cardboardempty'] =		         'auto'
        sprites['cardboardyes'] =  			       'auto'
        sprites['cardboardno'] =		  	       'auto'
        sprites['cardtouched'] =			         'auto'
        sprites['newBtn'] =				             'auto'
        sprites['retryBtn'] =				           'auto'
        sprites['hintBtn'] =				           'auto'
        sprites['soundOnBtn'] =			           'auto'
        sprites['soundOffBtn'] =  			       'auto'
        sprites['themeBtn'] =		  		         'auto'
        sprites['undoBtn'] =				           'auto'

        cardBGSprite = cc.Sprite.create '#cardbg.png'
        box = cardBGSprite.getBoundingBox()

        colors = ['D', 'S', 'H', 'C']
        ranks = ['A','2','3','4','5','6','7','8','9','10','J','Q','K']
        for color in colors
          for rank in ranks
            if (@_isCardLayout and rank is "A")
              sprites['card_' + color + rank] = [
                from: 'cardbg'
                to: [0, 0]
              ,
                from: 'rank_' + color + rank
                to: [box.width / 4, 3 * box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'rank_' + color + rank
                to: [ 3 * box.width / 4, box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [2 * box.width / 4, 2 * box.height / 4]
                anchor: [0.5, 0.5]
              ]
            else if (@_isCardLayout and rank is "2")
              sprites['card_' + color + rank] = [
                from: 'cardbg'
                to: [0, 0]
              ,
                from: 'small_' + color
                to: [2 * box.width / 4, box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [2 * box.width / 4, 3 * box.height / 4]
                anchor: [0.5, 0.5]
              ]
            else if (@_isCardLayout and rank is "3")
              sprites['card_' + color + rank] = [
                from: 'cardbg'
                to: [0, 0]
              ,
                from: 'small_' + color
                to: [2 * box.width / 4, box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [2 * box.width / 4, 2 * box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [2 * box.width / 4, 3 * box.height / 4]
                anchor: [0.5, 0.5]
              ]
            else if (@_isCardLayout and rank is "4")
              sprites['card_' + color + rank] = [
                from: 'cardbg'
                to: [0, 0]
              ,
                from: 'small_' + color
                to: [box.width / 4, box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [box.width / 4, 3 * box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [3 * box.width / 4, box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [3 * box.width / 4, 3 * box.height / 4]
                anchor: [0.5, 0.5]
              ]
            else if (@_isCardLayout and rank is "5")
              sprites['card_' + color + rank] = [
                from: 'cardbg'
                to: [0, 0]
              ,
                from: 'small_' + color
                to: [box.width / 4, box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [box.width / 4, 3 * box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [3 * box.width / 4, box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [3 * box.width / 4, 3 * box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [2 * box.width / 4, 2 * box.height / 4]
                anchor: [0.5, 0.5]
              ]
            else if (@_isCardLayout and rank is "6")
              sprites['card_' + color + rank] = [
                from: 'cardbg'
                to: [0, 0]
              ,
                from: 'small_' + color
                to: [box.width / 4, box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [box.width / 4, 2 * box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [box.width / 4, 3 * box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [3 * box.width / 4, box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [3 * box.width / 4, 2 * box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [3 * box.width / 4, 3 * box.height / 4]
                anchor: [0.5, 0.5]
              ]
            else if (@_isCardLayout and rank is "7")
              sprites['card_' + color + rank] = [
                from: 'cardbg'
                to: [0, 0]
              ,
                from: 'small_' + color
                to: [box.width / 4, box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [box.width / 4, 2 * box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [box.width / 4, 3 * box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [3 * box.width / 4, box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [3 * box.width / 4, 2 * box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [3 * box.width / 4, 3 * box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [2 * box.width / 4, 2 * box.height / 4]
                anchor: [0.5, 0.5]
              ]
            else if (@_isCardLayout and rank is "8")
              sprites['card_' + color + rank] = [
                from: 'cardbg'
                to: [0, 0]
              ,
                from: 'small_' + color
                to: [box.width / 4, box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [box.width / 4, 2 * box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [box.width / 4, 3 * box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [3 * box.width / 4, box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [3 * box.width / 4, 2 * box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [3 * box.width / 4, 3 * box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [2 * box.width / 4, 1.5 * box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [2 * box.width / 4, 2.5 * box.height / 4]
                anchor: [0.5, 0.5]
              ]
            else if (@_isCardLayout and rank is "9")
              sprites['card_' + color + rank] = [
                from: 'cardbg'
                to: [0, 0]
              ,
                from: 'small_' + color
                to: [box.width / 4, box.height / 5]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [box.width / 4, 2 * box.height / 5]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [box.width / 4, 3 * box.height / 5]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [box.width / 4, 4 * box.height / 5]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [3 * box.width / 4, box.height / 5]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [3 * box.width / 4, 2 * box.height / 5]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [3 * box.width / 4, 3 * box.height / 5]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [3 * box.width / 4, 4 * box.height / 5]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [2 * box.width / 4, 1.5 * box.height / 5]
                anchor: [0.5, 0.5]
              ]
            else if (@_isCardLayout and rank is "10")
              sprites['card_' + color + rank] = [
                from: 'cardbg'
                to: [0, 0]
              ,
                from: 'small_' + color
                to: [box.width / 4, box.height / 5]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [box.width / 4, 2 * box.height / 5]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [box.width / 4, 3 * box.height / 5]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [box.width / 4, 4 * box.height / 5]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [3 * box.width / 4, box.height / 5]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [3 * box.width / 4, 2 * box.height / 5]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [3 * box.width / 4, 3 * box.height / 5]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [3 * box.width / 4, 4 * box.height / 5]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [2 * box.width / 4, 1.5 * box.height / 5]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [2 * box.width / 4, 3.5 * box.height / 5]
                anchor: [0.5, 0.5]
              ]
            else if (@_isCardLayout and (rank is "J" or rank is "Q" or rank is "K"))
              sprites['card_' + color + rank] = [
                from: 'cardbg'
                to: [0, 0]
              ,
                from: 'small_' + color
                to: [box.width / 4, box.height / 2]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [3 * box.width / 4, box.height / 2]
                anchor: [0.5, 0.5]
              ,
                from: 'rank_' + color + rank
                to: [box.width / 4, 4 * box.height / 5]
                anchor: [0.5, 0.5]
              ,
                from: 'rank_' + color + rank
                to: [3 * box.width / 4, box.height / 5]
                anchor: [0.5, 0.5]
              ]
            else
              sprites['card_' + color + rank] = [
                from: 'cardbg'
                to: [0, 0]
              ,
                from: 'rank_' + color + rank
                to: [box.width / 4, 3 * box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'big_' + color
                to: [2 * box.width / 4, box.height / 4]
                anchor: [0.5, 0.5]
              ,
                from: 'small_' + color
                to: [3 * box.width / 4, 3 * box.height / 4]
                anchor: [0.5, 0.5]
              ]

      for k, s of sprites
        if s is'auto'
          s = [
            from: k
            to: [0, 0]
          ]

        node = cc.SpriteBatchNode.create texturePath

        nodeSize = cc.size(0, 0)
        for zone in s
          unless zone['anchor']
            zone['anchor'] = [0, 0]

          zonePosition = cc.p(zone['to'][0], zone['to'][1])
          zoneAnchor = cc.p(zone['anchor'][0], zone['anchor'][1])

          zoneSprite = cc.Sprite.create('#' + zone['from'] + '.png')
          zoneSprite.setAnchorPoint zoneAnchor
          zoneSprite.setPosition zonePosition
          node.addChild zoneSprite

          box = zoneSprite.getBoundingBox()
          nodeSize.width = Math.max box.x + box.width, nodeSize.width
          nodeSize.height = Math.max box.y + box.height, nodeSize.height

        node.setContentSize(nodeSize)

        cpz.GameConfig._configPathsSet(path + ':' + isCardLayout + ':' + k, node)

      node = cpz.GameConfig._configPaths[nodePath]

    cc.copySpriteBatchNode(node, sprite) if node

  _resolution: ''
  _theme: ''
  _isCardLayout: false
  _isSoundOn: false
  _moves: []
  _initBoard: null

  ctor: ->
    @_initBoard = new cc.Dictionary()

  init: ->
    @_resolution = @defaultResolution()
    @_theme = @defaultTheme()
    @_isSoundOn = true

    true

  getNodeUiPath: (file, sprite) -> @_getNodePath 'ui', file, sprite
  getNodeThemePath: (file, sprite) -> @_getNodePath 'theme', file, sprite

  getResolutionSize: ->
    cpz.GameConfig.parseResolution @_resolution

  defaultResolution: -> '480x320'
  defaultTheme: -> 'chinese'

  getResolution: -> @_resolution
  setResolution: (@_resolution) -> @
  getTheme: -> @_theme
  setTheme: (@_theme) -> @
  getIsCardLayout: -> @_isCardLayout
  setIsCardLayout: (@_isCardLayout) -> @
  getIsSoundOn: -> @_isSoundOn
  setIsSoundOn: (@_isSoundOn) -> @
  getMoves: -> @_moves
  clearMoves: ->
    cc.ArrayClear(@_moves)
    @
  pushMove: (move) ->
    @_moves.push move
    @
  popMove: ->
    @_moves.pop()
  getInitBoard: -> @_initBoard

  encode: ->
    data =
      #resolution: @_resolution
      theme: @_theme
      isCardLayout: @_isCardLayout
      isSoundOn: @_isSoundOn
      moves: []
      board: []

    for coord in @_initBoard.allKeys()
      card = @_initBoard.object(coord)

      data['board'].push
        coord: coord.encode()
        card: card

    for move in @_moves
      data['moves'].push(move.encode())

    data

  decode: (data) ->
    @clearMoves()
    @_initBoard.removeAllObjects()

    #@_resolution = data['resolution']
    @_theme = data['theme']
    @_isCardLayout = data['isCardLayout']
    @_isSoundOn = data['isSoundOn']

    for board in data['board']
      card = board.card
      coord = cpz.GridCoord.decode(board.coord)
      @_initBoard.setObject card, coord

    for move in data['moves']
      @_moves.push(cpz.MoveCoord.decode(move))
    @

  save: ->
    data = @encode()
    data = base64.encode(JSON.stringify(data))
    cc.sys.localStorage.setItem(cpz.XML_FILE_NAME, data);

    @
    
  load: ->
    data = cc.sys.localStorage.getItem(cpz.XML_FILE_NAME);
    if data
      data = JSON.parse(base64.decode(data))
      @decode(data)

    @

  preload: (selector, target, resolution = null, theme = null) ->
    resolution = @_resolution unless resolution
    theme = @_theme unless theme

    plistThemePath = cpz.CommonPath + resolution + '/themes/' + theme  + '.plist'
    textureThemePath = cpz.CommonPath + resolution + '/themes/' + theme + '.png'
    plistUIPath = cpz.CommonPath + resolution + '/ui' + '.plist'
    textureUIPath = cpz.CommonPath + resolution + '/ui' + '.png'

    cc.loader.load [
      plistThemePath,
      textureThemePath,
      plistUIPath,
      textureUIPath
    ], => selector.call(target)
)

cpz.GameConfig._configPaths = {};

cpz.GameConfig._configPathsSet = (key, node) ->
  if cpz.GameConfig._configPaths[key]
    cpz.GameConfig._configPaths[key].release()
    cpz.GameConfig._configPaths[key] = null
  cpz.GameConfig._configPaths[key] = node
  node.retain()

cpz.GameConfig.getRootPath = (file) -> cpz.CommonPath + file
cpz.GameConfig.getResolutionPath = (file, resolution) -> cpz.CommonPath + resolution + '/' + file
cpz.GameConfig.getUiPath = (file, resolution) -> cpz.CommonPath + resolution + '/ui/' + file
cpz.GameConfig.getThemePath = (file, resolution, theme) -> cpz.CommonPath + resolution + '/themes/' + theme + '/' + file
cpz.GameConfig.getFontPath = (file) -> cpz.CommonPath + 'fonts/' + file

cpz.GameConfig.parseResolution = (res) ->
  return cc.size(0, 0) unless res
  m = res.match /([0-9]+)x([0-9]+)/
  if m then cc.size(parseInt(m[1]), parseInt(m[2])) else cc.size(0, 0)

cpz.GameConfig.compareResolution = (res1, res2) ->
  size1 = @parseResolution res1
  size2 = @parseResolution res2

  @compareSize size1, size2

cpz.GameConfig.compareSize = (size1, size2) ->
  if (size1.width < size2.width) and (size1.height < size2.height)
    min: size1
    max: size2
  else if (size1.width >= size2.width) and (size1.height >= size2.height)
    min: size2
    max: size1
  else
    min: null
    max: null

cpz.GameConfig.bestSize = (size) ->
  bestSize = cc.size 0, 0

  for res in @getResolutions()
    sizeRes = @parseResolution res

    compareSize = @compareSize size, sizeRes
    if compareSize.min
      compareSize = @compareSize compareSize.min, bestSize
      bestSize = compareSize.max if compareSize.max

  sizeA = cc.size bestSize.width, bestSize.width * size.height / size.width
  sizeB = cc.size bestSize.height * size.width / size.height, bestSize.height

  compareSize = @compareSize sizeA, sizeB
  bestSize = compareSize.max
  bestSize

cpz.GameConfig.getResolutions = ->
  [
    '480x320',
    '960x640',
    '1024x768',
    '1280x800',
    '1280x1024',
    '1366x768',
    '1440x900',
    '1680x1050',
    '1920x1080',
    '1920x1200',
  ]

cpz.GameConfig.getThemes = ->
  [
    'chinese',
    'circle',
    'classic',
    'polkadots',
    'seamless',
    'shullshearts',
    'splash',
    'spring',
    'stripes',
    'vivid',
  ]