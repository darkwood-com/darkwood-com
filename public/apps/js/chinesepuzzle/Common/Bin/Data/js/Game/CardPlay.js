/*
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
*/

cpz.CardPlayColor = {
  Spade: 1,
  Club: 2,
  Heart: 3,
  Diamond: 4
};

cpz.CardPlayRank = {
  Ace: 1,
  Two: 2,
  Three: 3,
  Four: 4,
  Five: 5,
  Six: 6,
  Seven: 7,
  Eight: 8,
  Nine: 9,
  Ten: 10,
  Jack: 11,
  Queen: 12,
  King: 13
};

cpz.CardPlay = cpz.Card.extend({
  _faceSprite: null,
  _backSprite: null,
  _color: null,
  _rank: null,
  _isLocked: false,
  _isFaceUp: false,
  onExit: function() {
    cc.SafeRelease(this._faceSprite);
    cc.SafeRelease(this._backSprite);
    return this._super();
  },
  initWithConf: function(conf) {
    if (!this.initWithTexture(cc.textureNull(), 16)) {
      return false;
    }
    this.setConf(conf);
    return true;
  },
  initWithConfAndColorAndRank: function(conf, color, rank) {
    if (!this.initWithConf(conf)) {
      return false;
    }
    this._color = color;
    this._rank = rank;
    return true;
  },
  encode: function() {
    return {
      color: this._color,
      rank: this._rank
    };
  },
  decode: function(data) {
    this._color = data['color'];
    this._rank = data['rank'];
    return this;
  },
  isNextToCardPlay: function(cardPlay) {
    return cardPlay && this._color === cardPlay._color && this._rank === cardPlay._rank + 1;
  },
  setConf: function(conf) {
    if (!this._faceSprite) {
      this._faceSprite = cc.SpriteBatchNode.create(cc.textureNull());
      this._faceSprite.retain();
    }
    if (!this._backSprite) {
      this._backSprite = cc.SpriteBatchNode.create(cc.textureNull());
      this._backSprite.retain();
    }
    conf.getNodeThemePath('card_' + cpz.CardPlay.matchColor(this._color) + cpz.CardPlay.matchRank(this._rank), this._faceSprite);
    conf.getNodeThemePath('cardplaybg', this._backSprite);
    return this.setIsFaceUp(this.getIsFaceUp(), true);
  },
  getColor: function() {
    return this._color;
  },
  getRank: function() {
    return this._rank;
  },
  getIsLocked: function() {
    return this._isLocked;
  },
  setIsLocked: function(_isLocked) {
    this._isLocked = _isLocked;
    return this;
  },
  getIsFaceUp: function() {
    return this._isFaceUp;
  },
  setIsFaceUp: function(isFaceUp, force) {
    if (this._isFaceUp !== isFaceUp || force) {
      this._isFaceUp = isFaceUp;
      if (this._isFaceUp) {
        this.setSpriteBatchNode(this._faceSprite);
      } else {
        this.setSpriteBatchNode(this._backSprite);
      }
    }
    return this;
  }
});

cpz.CardPlay.createWithConfAndColorAndRank = function(conf, color, rank) {
  var obj;
  obj = new cpz.CardPlay();
  if (obj && obj.initWithConfAndColorAndRank(conf, color, rank)) {
    return obj;
  }
  return null;
};

cpz.CardPlay.decode = function(conf, data) {
  var obj;
  obj = new cpz.CardPlay();
  obj.decode(data);
  if (obj && obj.initWithConf(conf)) {
    return obj;
  }
  return null;
};

cpz.CardPlay.matchColor = function(color) {
  if (typeof color === 'string') {
    switch (color) {
      case 'S':
        return cpz.CardPlayColor.Spade;
      case 'C':
        return cpz.CardPlayColor.Club;
      case 'H':
        return cpz.CardPlayColor.Heart;
      case 'D':
        return cpz.CardPlayColor.Diamond;
      default:
        return cpz.CardPlayColor.Spade;
    }
  } else {
    switch (color) {
      case cpz.CardPlayColor.Spade:
        return 'S';
      case cpz.CardPlayColor.Club:
        return 'C';
      case cpz.CardPlayColor.Heart:
        return 'H';
      case cpz.CardPlayColor.Diamond:
        return 'D';
      default:
        return 'D';
    }
  }
};

cpz.CardPlay.matchRank = function(rank) {
  if (typeof rank === 'string') {
    switch (rank) {
      case 'A':
        return cpz.CardPlayRank.Ace;
      case '2':
        return cpz.CardPlayRank.Two;
      case '3':
        return cpz.CardPlayRank.Three;
      case '4':
        return cpz.CardPlayRank.Four;
      case '5':
        return cpz.CardPlayRank.Five;
      case '6':
        return cpz.CardPlayRank.Six;
      case '7':
        return cpz.CardPlayRank.Seven;
      case '8':
        return cpz.CardPlayRank.Eight;
      case '9':
        return cpz.CardPlayRank.Nine;
      case '10':
        return cpz.CardPlayRank.Ten;
      case 'J':
        return cpz.CardPlayRank.Jack;
      case 'Q':
        return cpz.CardPlayRank.Queen;
      case 'K':
        return cpz.CardPlayRank.King;
      default:
        return cpz.CardPlayRank.Ace;
    }
  } else {
    switch (rank) {
      case cpz.CardPlayRank.Ace:
        return 'A';
      case cpz.CardPlayRank.Two:
        return '2';
      case cpz.CardPlayRank.Three:
        return '3';
      case cpz.CardPlayRank.Four:
        return '4';
      case cpz.CardPlayRank.Five:
        return '5';
      case cpz.CardPlayRank.Six:
        return '6';
      case cpz.CardPlayRank.Seven:
        return '7';
      case cpz.CardPlayRank.Eight:
        return '8';
      case cpz.CardPlayRank.Nine:
        return '9';
      case cpz.CardPlayRank.Ten:
        return '10';
      case cpz.CardPlayRank.Jack:
        return 'J';
      case cpz.CardPlayRank.Queen:
        return 'Q';
      case cpz.CardPlayRank.King:
        return 'K';
      default:
        return 'A';
    }
  }
};
