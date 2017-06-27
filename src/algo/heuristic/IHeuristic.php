<?php
/**
 * Created by PhpStorm.
 * User: hikoqiu
 * Date: 2017/6/27
 */

namespace src\algo\heuristic;


use src\grid\Node;

interface IHeuristic
{

    /**
     * 计算两个节点的距离
     * @param Node $start
     * @param Node $end
     * @return mixed
     */
    public function distance(Node $start, Node $end);

}