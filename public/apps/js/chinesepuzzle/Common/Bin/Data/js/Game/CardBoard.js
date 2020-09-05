/*
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
*/

cpz.CardBoardState = {
  Empty: 1,
  Yes: 2,
  No: 3
};

cpz.CardBoard = cpz.Card.extend({
  _emptySprite: null,
  _yesSprite: null,
  _noSprite: null,
  _state: null,
  ctor: function() {
    this._super();
    return this._state = cpz.CardBoardState.Empty;
  },
  onExit: function() {
    cc.SafeRelease(this._emptySprite);
    cc.SafeRelease(this._yesSprite);
    cc.SafeRelease(this._noSprite);
    return this._super();
  },
  initWithConf: function(conf) {
    if (!this.initWithTexture(cc.textureNull(), 1)) {
      return false;
    }
    this.setConf(conf);
    return true;
  },
  getState: function() {
    return this._state;
  },
  setState: function(state, force) {
    if (force == null) {
      force = false;
    }
    if (this._state !== state || force) {
      switch (state) {
        case cpz.CardBoardState.Yes:
          this.setSpriteBatchNode(this._yesSprite);
          break;
        case cpz.CardBoardState.No:
          this.setSpriteBatchNode(this._noSprite);
          break;
        default:
          this.setSpriteBatchNode(this._emptySprite);
      }
      return this._state = state;
    }
  },
  setConf: function(conf) {
    if (!this._emptySprite) {
      this._emptySprite = cc.SpriteBatchNode.create(cc.textureNull(), 1);
      this._emptySprite.retain();
    }
    if (!this._yesSprite) {
      this._yesSprite = cc.SpriteBatchNode.create(cc.textureNull(), 1);
      this._yesSprite.retain();
    }
    if (!this._noSprite) {
      this._noSprite = cc.SpriteBatchNode.create(cc.textureNull(), 1);
      this._noSprite.retain();
    }
    conf.getNodeThemePath('cardboardempty', this._emptySprite);
    conf.getNodeThemePath('cardboardyes', this._yesSprite);
    conf.getNodeThemePath('cardboardno', this._noSprite);
    return this.setState(this.getState, true);
  }
});

cpz.CardBoard.createWithConf = function(conf) {
  var obj;
  obj = new cpz.CardBoard();
  if (obj && obj.initWithConf(conf)) {
    return obj;
  }
  return null;
};
