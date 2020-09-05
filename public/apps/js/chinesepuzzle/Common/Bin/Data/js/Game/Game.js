/*
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
*/

var __indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

cpz.CheckMove = {
  From: 0,
  To: 1,
  ToBefore: 2,
  Ko: 3,
  Ok: 4
};

cpz.Game = cc.Layer.extend({
  _touchListener: null,
  _board: {},
  _gs: null,
  _gl: null,
  _gc: null,
  _lastTouchLocation: null,
  _dragCard: null,
  _dragCardCoord: null,
  _touchLastCard: null,
  _hintCard: null,
  _switchBoardCard: null,
  _makeMoveEnd: function() {
    this._switchBoardCard.setVisible(false);
    if (this._dragCard) {
      this.reorderChild(this._dragCard, cpz.GameZOrder.Card);
      this._dragCard = null;
    }
    return this._hintMove();
  },
  _makeMoveSound: function() {
    return this.getGameScene().playSound('card_move');
  },
  _makeMoveUndoSound: function() {
    return this.getGameScene().playSound('card_undo');
  },
  _hintMove: function() {
    var cTo, coord, move;
    if (this._dragCard) {
      cTo = this._gc.checkRectNode(this._dragCard, cpz.Game.filterCardBoard);
      if (cTo) {
        if (this._hintCard && this._hintCard !== cTo) {
          this._hintCard.setState(cpz.CardBoardState.Empty);
        }
        this._hintCard = cTo;
        coord = this._gl.getPositionInGridCoord(cTo.getPosition());
        move = cpz.mv(this._dragCardCoord, coord);
        if (this.checkMoveCoord(move) === cpz.CheckMove.Ok) {
          return this._hintCard.setState(cpz.CardBoardState.Yes);
        } else {
          return this._hintCard.setState(cpz.CardBoardState.No);
        }
      } else if (this._hintCard) {
        this._hintCard.setState(cpz.CardBoardState.Empty);
        return this._hintCard = null;
      }
    } else if (this._hintCard) {
      this._hintCard.setState(cpz.CardBoardState.Empty);
      return this._hintCard = null;
    }
  },
  _randInitBoard: function() {
    var color, coord, deck, i, initBoard, j, k, rank, _i, _j, _k, _l, _len, _len1, _ref, _ref1, _results;
    deck = [];
    for (k = _i = 1; _i <= 2; k = ++_i) {
      _ref = cc.ObjectValues(cpz.CardPlayColor);
      for (_j = 0, _len = _ref.length; _j < _len; _j++) {
        color = _ref[_j];
        _ref1 = cc.ObjectValues(cpz.CardPlayRank);
        for (_k = 0, _len1 = _ref1.length; _k < _len1; _k++) {
          rank = _ref1[_k];
          deck.push({
            color: color,
            rank: rank
          });
        }
      }
    }
    deck = cc.ArrayShuffle(deck);
    initBoard = this._gs.getConf().getInitBoard();
    initBoard.removeAllObjects();
    k = 0;
    _results = [];
    for (i = _l = 0; _l <= 7; i = ++_l) {
      _results.push((function() {
        var _m, _results1;
        _results1 = [];
        for (j = _m = 1; _m <= 13; j = ++_m) {
          coord = cpz.gc(i, j);
          initBoard.setObject(deck[k], coord);
          _results1.push(k++);
        }
        return _results1;
      })());
    }
    return _results;
  },
  _loadBoard: function() {
    var c, cSwitch, card, cardBoards, cardPlays, conf, coord, data, i, initBoard, j, move, _i, _j, _k, _l, _len, _len1, _len2, _len3, _m, _n, _o, _p, _ref, _ref1, _results;
    conf = this._gs.getConf();
    cardBoards = [];
    cardPlays = [];
    for (i = _i = 0; _i <= 7; i = ++_i) {
      for (j = _j = 0; _j <= 13; j = ++_j) {
        card = this._board[i][j];
        if (card instanceof cpz.CardPlay) {
          cardPlays.push(card);
        } else if (card instanceof cpz.CardBoard) {
          cardBoards.push(card);
        }
      }
    }
    for (i = _k = 0; _k <= 7; i = ++_k) {
      card = null;
      for (_l = 0, _len = cardBoards.length; _l < _len; _l++) {
        c = cardBoards[_l];
        card = c;
        cc.arrayRemoveObject(cardBoards, c);
        break;
      }
      if (!card) {
        card = cpz.CardBoard.createWithConf(conf);
      }
      coord = cpz.gc(i, 0);
      this._board[coord.i][coord.j] = card;
    }
    initBoard = this._gs.getConf().getInitBoard();
    _ref = initBoard.allKeys();
    for (_m = 0, _len1 = _ref.length; _m < _len1; _m++) {
      coord = _ref[_m];
      data = initBoard.object(coord);
      card = null;
      for (_n = 0, _len2 = cardPlays.length; _n < _len2; _n++) {
        c = cardPlays[_n];
        if (data.color === c.getColor() && data.rank === c.getRank()) {
          card = c;
          cc.arrayRemoveObject(cardPlays, c);
          break;
        }
      }
      if (!card) {
        card = cpz.CardPlay.decode(conf, data);
      }
      card.setIsLocked(false);
      this._board[coord.i][coord.j] = card;
    }
    _ref1 = this._gs.getConf().getMoves();
    for (_o = 0, _len3 = _ref1.length; _o < _len3; _o++) {
      move = _ref1[_o];
      cSwitch = this.getCard(move.to);
      this._board[move.to.i][move.to.j] = this._board[move.from.i][move.from.j];
      this._board[move.from.i][move.from.j] = cSwitch;
    }
    _results = [];
    for (i = _p = 0; _p <= 7; i = ++_p) {
      _results.push((function() {
        var _q, _results1;
        _results1 = [];
        for (j = _q = 0; _q <= 13; j = ++_q) {
          coord = cpz.gc(i, j);
          card = this.getCard(coord);
          if (!card.getParent()) {
            this._gc.addNode(card);
            this.addChild(card, cpz.GameZOrder.Card);
            _results1.push(card.setPosition(this._gl.getPositionInBoardPoint(coord)));
          } else {
            _results1.push(void 0);
          }
        }
        return _results1;
      }).call(this));
    }
    return _results;
  },
  ctor: function() {
    var i, j, _i, _results;
    this._super();
    _results = [];
    for (i = _i = 0; _i <= 7; i = ++_i) {
      this._board[i] = {};
      _results.push((function() {
        var _j, _results1;
        _results1 = [];
        for (j = _j = 0; _j <= 13; j = ++_j) {
          _results1.push(this._board[i][j] = null);
        }
        return _results1;
      }).call(this));
    }
    return _results;
  },
  initWithGameScene: function(gs) {
    var initBoard,
      _this = this;
    if (!this.init()) {
      return false;
    }
    this._gs = gs;
    this._gl = new cpz.GameLayout(this);
    this._gc = new cpz.GameControlNode();
    cc.eventManager.addListener(cc.EventListener.create({
      event: cc.EventListener.TOUCH_ONE_BY_ONE,
      swallowTouches: true,
      onTouchBegan: function(touch, event) {
        var location;
        location = touch.getLocation();
        _this.tapDownAt(location);
        return true;
      },
      onTouchMoved: function(touch, event) {
        var location;
        location = touch.getLocation();
        return _this.tapMoveAt(location);
      },
      onTouchEnded: function(touch, event) {
        var location;
        location = touch.getLocation();
        return _this.tapUpAt(location);
      },
      onTouchCancelled: function(touch, event) {
        var location;
        location = touch.getLocation();
        return _this.tapUpAt(location);
      }
    }), this);
    this.layout();
    initBoard = this._gs.getConf().getInitBoard();
    if (initBoard.count() === 0) {
      this._randInitBoard();
    }
    this._loadBoard();
    this.layout();
    this.schedule(this.step);
    return true;
  },
  newGame: function() {
    this._randInitBoard();
    return this.retryGame();
  },
  retryGame: function() {
    this._gs.getConf().clearMoves();
    this._loadBoard();
    this.layout();
    return this._gs.getConf().save();
  },
  step: function(dt) {
    return this._gc.step(dt);
  },
  layout: function(anim) {
    var actions, card, conf, coord, coordPos, i, j, _i, _j, _results;
    if (anim == null) {
      anim = true;
    }
    this._gl.layout(anim);
    conf = this._gs.getConf();
    if (!this._touchLastCard) {
      this._touchLastCard = new cpz.Card();
      this._touchLastCard.initWithTexture(cc.textureNull(), 4);
      this._touchLastCard.setVisible(false);
      this.addChild(this._touchLastCard, cpz.GameZOrder.HintCard);
    }
    conf.getNodeThemePath('cardtouched', this._touchLastCard);
    if (!this._switchBoardCard) {
      this._switchBoardCard = new cpz.CardBoard();
      this._switchBoardCard.initWithTexture(cc.textureNull(), 4);
      this._switchBoardCard.setVisible(false);
      this.addChild(this._switchBoardCard, cpz.GameZOrder.Board);
    }
    this._switchBoardCard.setConf(conf);
    _results = [];
    for (i = _i = 0; _i <= 7; i = ++_i) {
      for (j = _j = 0; _j <= 13; j = ++_j) {
        coord = cpz.gc(i, j);
        card = this.getCard(coord);
        if (card) {
          card.setConf(conf);
          if (card instanceof cpz.CardBoard) {
            card.setPosition(this._gl.getPositionInBoardPoint(coord));
          } else if (card instanceof cpz.CardPlay) {
            coordPos = this._gl.getPositionInBoardPoint(coord);
            if (anim) {
              actions = [];
              actions.push(cc.DelayTime.create(0.05 * (7 - coord.i + coord.j - 1)));
              if (!cc.pointEqualToPoint(card.getPosition(), coordPos)) {
                actions.push(cc.MoveTo.create(1.0, coordPos));
              }
              if (!card.getIsFaceUp()) {
                actions.push(cc.OrbitCamera.create(0.1, 1, 0, 0, 90, 0, 0));
                actions.push(cc.CallFunc.create(card.setIsFaceUp, card, true));
                actions.push(cc.OrbitCamera.create(0.1, 1, 0, 270, 90, 0, 0));
              }
              card.runAction(cc.Sequence.create(actions));
            } else {
              card.setPosition(coordPos);
              card.setIsFaceUp(true);
            }
          }
        }
      }
      _results.push(this.lockLine(i));
    }
    return _results;
  },
  isBusy: function() {
    var i, j, _i, _j;
    for (i = _i = 0; _i <= 7; i = ++_i) {
      for (j = _j = 0; _j <= 13; j = ++_j) {
        if (this._board[i][j] && this._board[i][j].getNumberOfRunningActions() > 0) {
          return true;
        }
      }
    }
    return false;
  },
  getCard: function(coord) {
    if (0 <= coord.i && coord.i < 8 && 0 <= coord.j && coord.j < 14) {
      return this._board[coord.i][coord.j];
    } else {
      return null;
    }
  },
  checkMoveCoord: function(move) {
    var cFrom, cTo, cToBefore, toBefore;
    cFrom = this.getCard(move.from);
    if (!(cFrom instanceof cpz.CardPlay)) {
      return cpz.CheckMove.From;
    }
    cTo = this.getCard(move.to);
    if (!(cTo instanceof cpz.CardBoard)) {
      return cpz.CheckMove.From;
    }
    toBefore = cpz.gc(move.to.i, move.to.j);
    toBefore.j--;
    if (toBefore.j === -1) {
      if (cFrom.getRank() === cpz.CardPlayRank.Ace) {
        return cpz.CheckMove.Ok;
      }
    } else {
      cToBefore = this.getCard(toBefore);
      if (!(cToBefore instanceof cpz.CardPlay)) {
        return cpz.CheckMove.ToBefore;
      }
      if (cFrom.isNextToCardPlay(cToBefore)) {
        return cpz.CheckMove.Ok;
      }
    }
    return cpz.CheckMove.Ko;
  },
  checkMoveCard: function(from, to) {
    return this.checkMoveCoord(cpz.mv(this._gl.getPositionInGridCoord(from), this._gl.getPositionInGridCoord(to)));
  },
  makeMoveCoord: function(move) {
    var cFrom, cSwitch, check;
    check = this.checkMoveCoord(move);
    cFrom = this.getCard(move.from);
    if (check === cpz.CheckMove.Ok) {
      cSwitch = this.getCard(move.to);
      this._board[move.to.i][move.to.j] = this._board[move.from.i][move.from.j];
      this._board[move.from.i][move.from.j] = cSwitch;
      cFrom.runAction(cc.Sequence.create([cc.MoveTo.create(0.5, this._gl.getPositionInBoardPoint(move.to)), cc.CallFunc.create(this._makeMoveEnd, this), cc.CallFunc.create(this._makeMoveSound, this)]));
      if (cSwitch) {
        cSwitch.setPosition(this._gl.getPositionInBoardPoint(move.from));
        if (cSwitch instanceof cpz.CardBoard) {
          cSwitch.setState(cpz.CardBoard.Empty);
        }
      }
      this._switchBoardCard.setPosition(this._gl.getPositionInBoardPoint(move.to));
      this.lockLine(move.to.i);
      this._gs.getConf().pushMove(move);
      this._gs.getConf().save();
    } else if (cFrom) {
      cFrom.runAction(cc.Sequence.create([cc.MoveTo.create(0.5, this._gl.getPositionInBoardPoint(move.from)), cc.CallFunc.create(this._makeMoveEnd, this)]));
    } else {
      this.makeMoveEnd();
    }
    return check;
  },
  makeMoveCard: function(from, to) {
    return this.makeMoveCoord(cpz.mv(this._gl.getPositionInGridCoord(from), this._gl.getPositionInGridCoord(to)));
  },
  undoMove: function() {
    var cSwitch, cTo, move;
    if (this._gs.getConf().getMoves().length === 0 || this.isBusy()) {
      return;
    }
    move = this._gs.getConf().popMove();
    cTo = this.getCard(move.to);
    cSwitch = this.getCard(move.from);
    this._board[move.from.i][move.from.j] = this._board[move.to.i][move.to.j];
    this._board[move.to.i][move.to.j] = cSwitch;
    cTo.runAction(cc.Sequence.create([cc.MoveTo.create(0.5, this._gl.getPositionInBoardPoint(move.from)), cc.CallFunc.create(this._makeMoveEnd, this), cc.CallFunc.create(this._makeMoveUndoSound, this)]));
    if (cSwitch) {
      cSwitch.setPosition(this._gl.getPositionInBoardPoint(move.to));
      if (cSwitch instanceof cpz.CardBoard) {
        cSwitch.setState(cpz.CardBoardState.Empty);
      }
    }
    this._switchBoardCard.setPosition(this._gl.getPositionInBoardPoint(move.from));
    this._switchBoardCard.setVisible(true);
    this.lockLine(move.from.i);
    this.lockLine(move.to.i);
    return move;
  },
  lockLine: function(i) {
    var cBefore, card, j, nb, _i;
    if (__indexOf.call([0, 1, 2, 3, 4, 5, 6, 7], i) < 0) {
      return 0;
    }
    nb = 0;
    for (j = _i = 0; _i <= 13; j = ++_i) {
      card = this._board[i][j];
      if (!(card instanceof cpz.CardPlay)) {
        continue;
      }
      cBefore = j === 0 ? null : this._board[i][j - 1];
      if (j === 0 && card.getRank() === cpz.CardPlayRank.Ace || cBefore instanceof cpz.CardPlay && cBefore.getIsLocked() && card.isNextToCardPlay(cBefore)) {
        card.setIsLocked(true);
        nb++;
      } else {
        card.setIsLocked(false);
      }
    }
    return nb;
  },
  tapDownAt: function(location) {
    var dragCardPos, tapCard;
    if (this._gl.tapDownAt(location)) {
      return;
    }
    if (!this._dragCard && !this.isBusy()) {
      tapCard = this._gc.checkPoint(location);
      if (tapCard instanceof cpz.CardBoard && this._touchLastCard.isVisible() && this.checkMoveCard(this._touchLastCard, tapCard) === cpz.CheckMove.Ok) {
        this._switchBoardCard.setPosition(this._touchLastCard.getPosition());
        this._switchBoardCard.setVisible(true);
        this.makeMoveCard(this._touchLastCard, tapCard);
      }
      if (tapCard instanceof cpz.CardPlay && !tapCard.getIsLocked()) {
        this._dragCardCoord = this._gl.getPositionInGridCoord(tapCard.getPosition());
        this._dragCard = tapCard;
        dragCardPos = this._gl.getPositionInBoardPoint(this._dragCardCoord);
        this._switchBoardCard.setPosition(dragCardPos);
        this._switchBoardCard.setVisible(true);
        this._touchLastCard.setPosition(dragCardPos);
        this.reorderChild(this._dragCard, cpz.GameZOrder.MoveCard);
      }
      this._touchLastCard.setVisible(false);
    }
    this._hintMove();
    return this._lastTouchLocation = cc.p(location);
  },
  tapMoveAt: function(location) {
    var movePos;
    if (this._gl.tapMoveAt(location)) {
      return;
    }
    this._touchLastCard.setVisible(false);
    movePos = cc.pAdd(location, cc.pNeg(this._lastTouchLocation));
    if (this._dragCard) {
      this._dragCard.setPosition(cc.pAdd(this._dragCard.getPosition(), movePos));
    }
    this._hintMove();
    return this._lastTouchLocation = cc.p(location);
  },
  tapUpAt: function(location) {
    var cToCard, check, coord, move;
    if (this._gl.tapUpAt(location)) {
      return;
    }
    if (this._dragCard) {
      cToCard = this._gc.checkRectNode(this._dragCard, cpz.Game.filterCardBoard);
      if (!cToCard) {
        cToCard = this._dragCard;
      }
      coord = this._gl.getPositionInGridCoord(cToCard.getPosition());
      move = cpz.mv(this._dragCardCoord, coord);
      check = this.makeMoveCoord(move);
      if (check !== cpz.CheckMove.Ok && cc.pointEqualToPoint(this._dragCard.getPosition(), this._touchLastCard.getPosition())) {
        this._touchLastCard.setVisible(true);
      }
      this._dragCard = null;
    }
    return this._lastTouchLocation = cc.p(location);
  },
  getGameScene: function() {
    return this._gs;
  },
  getLayout: function() {
    return this._gl;
  },
  getControl: function() {
    return this._gc;
  }
});

cpz.Game.create = function(gs) {
  var obj;
  obj = new cpz.Game();
  if (obj && obj.initWithGameScene(gs)) {
    return obj;
  }
  return null;
};

cpz.Game.filterCardBoard = function(node) {
  return node instanceof cpz.CardBoard;
};
