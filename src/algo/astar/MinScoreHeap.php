<?php
/**
 * Created by PhpStorm.
 * User: hikoqiu
 * Date: 2017/6/27
 */

namespace src\algo\astar;

use src\grid\Node;

/**
 * 节点的 f(n) 小顶堆
 * 用于存放 OpenList 的各节点
 * Class MinHeap
 * @package src\struct
 */
class MinScoreHeap extends \SplHeap
{

    /**
     * @param Node $value1
     * @param Node $value2
     * @return int
     */
    protected function compare($node1, $node2)
    {
        if ($node1->getFScore() == $node2->getFScore()) {
            return 0;
        }

        return ($node1->getFScore() < $node2->getFScore()) ? 1 : -1;
    }
}