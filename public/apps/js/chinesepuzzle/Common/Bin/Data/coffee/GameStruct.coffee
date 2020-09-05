###
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
###

cpz.GridCoord = cc.Class.extend(
  i: 0
  j: 0

  ctor: ->

  encode: ->
    i: @i
    j: @j

  decode: (data) ->
    @i = data['i']
    @j = data['j']
    @
)

cpz.GridCoord.decode = (data) ->
  obj = new cpz.GridCoord()
  obj.decode(data)
  return obj

cpz.gc = (i, j) ->
  coord = new cpz.GridCoord()
  coord.i = i
  coord.j = j
  coord

cpz.MoveCoord = cc.Class.extend(
  from: null
  to: null

  ctor: ->

  encode: ->
    from: @from.encode()
    to: @to.encode()

  decode: (data) ->
    @from = cpz.GridCoord.decode(data['from'])
    @to = cpz.GridCoord.decode(data['to'])
    @
)

cpz.MoveCoord.decode = (data) ->
  obj = new cpz.MoveCoord()
  obj.decode(data)
  return obj

cpz.mv = (from, to) ->
  coord = new cpz.MoveCoord()
  coord.from = from
  coord.to = to
  coord
