/*
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
*/

cpz.Background = cc.Layer.extend({
  _bgPattern: null,
  _gs: null,
  initWithGameScene: function(gs) {
    var texParams, texture;
    if (!this.init()) {
      return false;
    }
    this._bgPattern = cc.Sprite.create(cpz.GameConfig.getRootPath('bgPattern.png'));
    texture = this._bgPattern.getTexture();
    if (cc._renderContext !== void 0) {
      texParams = {
        minFilter: cc._renderContext.LINEAR,
        magFilter: cc._renderContext.LINEAR,
        wrapS: cc._renderContext.REPEAT,
        wrapT: cc._renderContext.REPEAT
      };
      texture.setTexParameters(texParams['minFilter'], texParams['magFilter'], texParams['wrapS'], texParams['wrapT']);
    } else {
      texParams = {
        minFilter: gl.LINEAR,
        magFilter: gl.LINEAR,
        wrapS: gl.REPEAT,
        wrapT: gl.REPEAT
      };
      texture.setTexParameters(texParams['minFilter'], texParams['magFilter'], texParams['wrapS'], texParams['wrapT']);
    }
    this._bgPattern.setAnchorPoint(cc.p(0.5, 0.5));
    this.addChild(this._bgPattern, cpz.GameSceneZOrder.BG);
    this._gs = gs;
    this.setContentSize(this._gs.getConf().getResolutionSize());
    return true;
  },
  setContentSize: function(size) {
    var rect;
    this._super(size);
    if (this._bgPattern) {
      rect = cc.rect(0, 0, size.width, size.height);
      return this._bgPattern.setTextureRect(rect);
    }
  },
  getGameScene: function() {
    return this._gs;
  }
});

cpz.Background.create = function(gs) {
  var obj;
  obj = new cpz.Background();
  if (obj && obj.initWithGameScene(gs)) {
    return obj;
  }
  return null;
};
