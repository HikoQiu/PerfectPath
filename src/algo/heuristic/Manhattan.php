<?php
/**
 * Created by PhpStorm.
 * User: hikoqiu
 * Date: 2017/6/27
 */

namespace src\algo\heuristic;

use src\grid\Node;

class Manhattan implements IHeuristic
{

    /**
     * 计算两个节点的距离
     * @param $start
     * @param $end
     * @return mixed
     */
    public function distance(Node $start, Node $end)
    {
        return abs($start->getX() - $end->getX()) + abs($start->getY() - $end->getY());
    }
}