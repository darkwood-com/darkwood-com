###
This file is part of the ChinesePuzzle package.

(c) Mathieu Ledru

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
###

cpz.GameControlNode = cpz.GameControl.extend(
  _nodes: []

  step: (dt) ->
  draw: ->

  addNode: (node) ->
    if @_nodes.indexOf(node) is -1
      @_nodes.push node

  removeNode: (node) ->
    if @_nodes.indexOf(node) > -1
      for i in [0..@_nodes.length]
        if @_nodes[i] is node
          @_nodes.splice(i, 1)

  updateNode: (node) ->


  checkPoint: (point) ->
    for n in @_nodes
      local = n.convertToNodeSpace point
      rect = n.getBoundingBox()
      rect.x = 0
      rect.y = 0

      if cc.rectContainsPoint rect, local then return n

    null

  checkPointNode: (node) ->
    for n in @_nodes
      continue if n is node

      point = node.getPosition()
      rectNode = n.getBoundingBox()

      if cc.rectContainsPoint rectNode, point then return node

    null

  checkRect: (rect, filter) ->
    nodeRes = null
    minDist = -1

    for n in @_nodes
      continue unless filter(n)

      local = n.convertToWorldSpace(cc.PointZero())
      rectNode = n.getBoundingBox()
      rectNode.x = local.x
      rectNode.y = local.y

      if cc.rectIntersectsRect rectNode, rect
        vect = cc.p rect.x - rectNode.x, rect.y - rectNode.y
        dist = vect.x * vect.x + vect.y * vect.y
        if minDist is -1 or dist < minDist
          minDist = dist
          nodeRes = n

    nodeRes

  checkRectNode: (node, filter) ->
    nodeRes = null
    minDist = -1

    for n in @_nodes
      continue unless filter(n)

      rect = node.getBoundingBox()
      rectNode = n.getBoundingBox()

      if cc.rectIntersectsRect rectNode, rect
        vect = cc.p rect.x - rectNode.x, rect.y - rectNode.y
        dist = vect.x * vect.x + vect.y * vect.y
        if minDist is -1 or dist < minDist
          minDist = dist
          nodeRes = n

    nodeRes
)
