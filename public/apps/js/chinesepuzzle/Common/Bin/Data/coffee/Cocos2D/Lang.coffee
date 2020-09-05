###
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
###

cc.Lang = cc.Class.extend(
  _data: null
  _lang: null

  ctor: ->
    @_data = {}

  getLang: ->
    @_lang

  setLang: (lang) ->
    @_lang = lang

  get: (key) ->
    @_data[key] or key

  set: (key, value) ->
    @_data[key] = value

  addLang: (fileName) ->
    filePath = cpz.CommonPath + fileName

    switch @_lang
      when cc.sys.LANGUAGE_FRENCH then filePath += '-fr'
      when cc.sys.LANGUAGE_GERMAN then filePath += '-de'
      when cc.sys.LANGUAGE_ENGLISH then filePath += '-en'
      else filePath += '-en'

    dict = cc.loader.getRes filePath + '.json'

    for key, value of dict
      @set key, value if value

    @
)

cc.Lang.s_sharedLang = null

cc.Lang.getInstance = ->
  unless @s_sharedLang
    @s_sharedLang = new cc.Lang()
  @s_sharedLang
