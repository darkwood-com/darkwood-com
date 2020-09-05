###
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
###

cpz.Resources = [
  #image

  #plist
  cpz.CommonPath + 'lang-de.json',
  cpz.CommonPath + 'lang-en.json',
  cpz.CommonPath + 'lang-fr.json',

  #fnt
  cpz.GameConfig.getFontPath('arial16.fnt'),
  #cpz.GameConfig.getFontPath('arial16.png'),
  cpz.GameConfig.getFontPath('arial32.fnt'),
  #cpz.GameConfig.getFontPath('arial32.png'),

  #tmx

  #png
  cpz.GameConfig.getRootPath('bgPattern.png'),

  cpz.GameConfig.getRootPath("switch/switch-mask.png"),
  cpz.GameConfig.getRootPath("switch/switch-on.png"),
  cpz.GameConfig.getRootPath("switch/switch-off.png"),
  cpz.GameConfig.getRootPath("switch/switch-thumb.png")
  #effect
  

  #audio
  #cpz.CommonPath + 'sound/bgm1.mp3',
  #cpz.CommonPath + 'sound/bgm2.mp3',
  #cpz.CommonPath + 'sound/bgm3.mp3',
  #cpz.CommonPath + 'sound/card_move.mp3',
  #cpz.CommonPath + 'sound/card_undo.mp3',
  #cpz.CommonPath + 'sound/menu_pop.mp3',
  #cpz.CommonPath + 'sound/menu_push.mp3',
  #cpz.CommonPath + 'sound/menu_select.mp3',
  #cpz.CommonPath + 'sound/shuffle.mp3',
]

#for resolution in cpz.GameConfig.getResolutions()
#  for theme in cpz.GameConfig.getThemes()
#    cpz.Resources.push cpz.GameConfig.getResolutionPath('themes/' + theme + '.plist', resolution)
#    cpz.Resources.push cpz.GameConfig.getResolutionPath('themes/' + theme + '.png', resolution)

