###
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
###

#cc.SpriteBatchNode.extend = cc.Class.extend unless cc.SpriteBatchNode.extend

cc.PREVENT_FREEZE_TIME = 100

cc.SafeRelease = (p) ->
  p.release() if p

cc.textureNull = -> new cc.Texture2D()

cc.copySpriteBatchNode = (from, to) ->
  return unless to instanceof cc.SpriteBatchNode and from instanceof cc.SpriteBatchNode

  to.removeAllChildren(true)
  to.setTexture(from.getTexture())

  for child in from.getChildren()
    zoneSprite = cc.Sprite.create to.getTexture(), child.getTextureRect()
    zoneSprite.setAnchorPoint(child.getAnchorPoint())
    zoneSprite.setPosition(child.getPosition())
    to.addChild(zoneSprite)

  to.setContentSize from.getContentSize()
  to.setAnchorPoint cc.p(0.5, 0.5)

cc.copySprite = (sprite) ->
  return cc.Sprite.create(sprite.getTexture(), sprite.getTextureRect())


cc.copyFirstSpriteBatchNode = (sprite) ->
  for child in sprite.getChildren()
    return cc.Sprite.create(child.getTexture(), child.getTextureRect())

  null

###
Shuffle array
@function
@param {Array} arr
@return {Array}
###
cc.ArrayShuffle = (arr) ->
  currentIndex = arr.length
  temporaryValue = undefined
  randomIndex = undefined

  # While there remain elements to shuffle...
  while 0 isnt currentIndex

    # Pick a remaining element...
    randomIndex = Math.floor(Math.random() * currentIndex)
    currentIndex -= 1

    # And swap it with the current element.
    temporaryValue = arr[currentIndex]
    arr[currentIndex] = arr[randomIndex]
    arr[randomIndex] = temporaryValue
  arr

cc.ArrayClear = (arr) ->
  arr.splice(0, arr.length);
  arr

cc.ObjectHas = (obj, key) -> obj.hasOwnProperty key

cc.ObjectKeys = (obj) ->
  throw new TypeError("Invalid object") if obj isnt Object(obj)

  keys = []
  for key of obj
    keys.push key if cc.ObjectHas(obj, key)
  keys

cc.ObjectValues = (obj) ->
  throw new TypeError("Invalid object") if obj isnt Object(obj)

  values = []
  for key, value of obj
    values.push value if cc.ObjectHas(obj, key)
  values

cc.ObjectSize = (obj) ->
  return 0 unless obj?
  if obj.length is +obj.length then obj.length else cc.ObjectKeys(obj).length

cc.Dictionary = cc.Class.extend(
  _keyMapTb: null
  _valueMapTb: null
  __currId: 0

  ctor: ->
    @_keyMapTb = {}
    @_valueMapTb = {}
    @__currId = 2 << (0 | (Math.random() * 10))

  __getKey: ->
    @__currId++
    "key_" + @__currId

  setObject: (value, key) ->
    return  unless key?
    keyId = @__getKey()
    @_keyMapTb[keyId] = key
    @_valueMapTb[keyId] = value

  object: (key) ->
    return null  unless key?
    locKeyMapTb = @_keyMapTb
    for keyId of locKeyMapTb
      return @_valueMapTb[keyId]  if locKeyMapTb[keyId] is key
    null

  value: (key) ->
    @object key

  removeObject: (key) ->
    return  unless key?
    locKeyMapTb = @_keyMapTb
    for keyId of locKeyMapTb
      if locKeyMapTb[keyId] is key
        delete @_valueMapTb[keyId]

        delete locKeyMapTb[keyId]

        return

  removeObjects: (keys) ->
    return  unless keys?
    i = 0

    while i < keys.length
      @removeObject keys[i]
      i++

  allKeys: ->
    keyArr = []
    locKeyMapTb = @_keyMapTb
    for key of locKeyMapTb
      keyArr.push locKeyMapTb[key]
    keyArr

  removeAllObjects: ->
    @_keyMapTb = {}
    @_valueMapTb = {}

  count: ->
    @allKeys().length
)

cc.MenuItemSprite.createWithSprite = (sprite) ->
  normalSprite = cc.copySprite sprite
  selectedSprite = cc.copySprite sprite

  cc.MenuItemSprite.create normalSprite, selectedSprite

cc.MenuItemSprite.createWithSpriteAndCallback = (sprite, callback, target) ->
  ret = cc.MenuItemSprite.createWithSprite sprite
  ret.setCallback callback, target
  ret
