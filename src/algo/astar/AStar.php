<?php
/**
 * Created by PhpStorm.
 * User: hikoqiu
 * Date: 2017/6/27
 */

namespace src\algo\astar;

use src\algo\heuristic\IHeuristic;
use src\algo\heuristic\Manhattan;
use src\grid\Grid;
use src\grid\Node;

class AStar
{

    /**
     * 表格地图
     * @var Grid
     */
    private $_grid;

    /**
     * 开放列表
     * @var MinScoreHeap
     */
    private $_openList;

    /**
     * 计算启发式路径的实例
     * 默认: 曼哈顿距离
     * @var IHeuristic
     */
    private $_heuristic;

    /**
     * 是否可以直接走斜角的节点
     * @var bool
     */
    private $_diagonal = false;

    public function __construct(Grid $grid)
    {
        $this->_grid = $grid;
    }

    /**
     * 设置计算启发式距离的对象
     * @param IHeuristic $h
     */
    public function setHeuristic(IHeuristic $h)
    {
        $this->_heuristic = $h;
    }

    public function setDiagonal($diagonal)
    {
        $this->_diagonal = $diagonal;
    }

    private function _check(Node $start, Node $end)
    {
        if ($start->isBlock() || $end->isBlock()) {
            throw new \Exception('起始节点不允许是障碍点', 1003);
        }
    }

    private function _init()
    {
        empty($this->_heuristic) && $this->_heuristic = new Manhattan();
        $this->_openList = new MinScoreHeap();
    }

    /**
     * 计算两个节点间的最短路径
     * @param Node $start
     * @param Node $end
     * @return array
     */
    public function calculatePath(Node $start, Node $end)
    {
        // 1.1 检查并初始化基本参数
        $this->_check($start, $end);
        $this->_init();

        // 2.1 设置初始值(从起点开始)
        $this->_openList->insert($start);

        // 3.1 开始遍历
        $curNode = $this->_traverse($start, $end);
        if ($curNode != $end) {
            return [];
        }

        // 4.1 获取完整路径
        return $this->_reversePath($curNode);
    }

    /**
     * 取最小 f(n) 的节点遍历, 遍历
     * @param Node $curNode
     * @param Node $end
     * @return mixed
     */
    private function _traverse(Node $curNode, Node $end)
    {
        // 1.1 "开放列表" 中还有节点, 则从中获取 f(n) 最小的节点
        while ($this->_openList->valid()) {
            $curNode = $this->_openList->extract();
            $curNode->close();

            // 1.2 获取所有需要进行判断的相邻节点 & 判断和更新
            $judgeNodes = $this->_grid->getJudgeAdjacentNodes($curNode, $this->_diagonal);
            foreach ($judgeNodes as $node) {
                $this->_judgeAdjacentNode($curNode, $node, $end);
            }

            // 2.1 到达目的节点
            if ($curNode == $end) {
                return $end;
            }
        }

        return $curNode;
    }

    /**
     * 情况一: 该相邻节点之前已访问过(已存在的父节点), 则需要判断假设新父节点的情况下, g(n) 会不会更小;
     *       如果更小, 则这路径更优, 选择 当前节点($curNode) 作为父节点; 相反则忽略.
     *
     * 情况二: 该相邻节点未访问过, 则直接设置;
     *
     * @param Node $curNode // 当前节点
     * @param Node $node // 当前节点中的某一个相邻节点
     */
    private function _judgeAdjacentNode(Node $curNode, Node $node, Node $end)
    {
        // 假设以当前节点($curNode)为父节点, 对比新 g(n) 与 旧 g(n) 的大小
        $score = $curNode->getGScore() + $this->_grid->calculateScore($curNode, $node);
        if ($node->isVisited() && $score >= $node->getGScore()) {
            return;
        }

        // 更新该相邻节点的相关数据
        $node->setPNode($curNode);
        $node->setGScore($score);
        $node->setHScore($this->_heuristic->distance($node, $end));
        $node->setFScore($node->getGScore() + $node->getHScore());
        if (!$node->isVisited()) {
            $node->visit();
            $this->_openList->insert($node);
        }
    }

    /**
     * 通过节点的父节点, 反推完整路径
     * @param Node $node
     * @return array
     */
    private function _reversePath(Node $node)
    {
        $list = [];
        while ($node->getPNode()) {
            $list[] = $node;
            $node   = $node->getPNode();
        }
        $list[] = $node;
        return array_reverse($list);
    }
}