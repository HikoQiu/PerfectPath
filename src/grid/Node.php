<?php
/**
 * Created by PhpStorm.
 * User: hikoqiu
 * Date: 2017/6/27
 */

namespace src\grid;

/**
 * 表格的节点
 * Class Node
 * @package src\algo\astar
 */
class Node
{
    // (x, y) 坐标
    private $_x, $_y;

    /**
     * f(n) = g(n) + h(n)
     * @var int
     */
    private $_fScore = 0;

    /**
     * 该节点通过父节点, 到达起点的实际分数(或消耗)
     * @var int
     */
    private $_gScore = 0;

    /**
     * 该节点与终点的理想距离(中间没有任何障碍)
     * 默认计算方式: 曼哈顿距离
     * @var int
     */
    private $_hScore = 0;

    /**
     * 是否已访问过
     * @var bool
     */
    private $_isVisited = false;

    /**
     * 是否关闭
     * @var bool
     */
    private $_isClosed = false;

    /**
     * 是否是障碍物
     * @var bool
     */
    private $_isBlock = false;

    /**
     * @var Node
     */
    private $_pNode = null;

    public function __construct($x, $y, $isBlock = false)
    {
        $this->_x       = $x;
        $this->_y       = $y;
        $this->_isBlock = $isBlock;
    }

    /**
     * 节点 x 坐标
     * @return int
     */
    public function getX()
    {
        return $this->_x;
    }

    /**
     * 节点 y 坐标
     * @return int
     */
    public function getY()
    {
        return $this->_y;
    }

    /**
     * f(n) = g(n) + h(n)
     * $this->_fScore = $this->_gScore + $this->_hScore;
     * @return int
     */
    public function getFScore()
    {
        return $this->_fScore;
    }

    /**
     * 起点到当前节点的距离
     * @return int
     */
    public function getGScore()
    {
        return $this->_gScore;
    }

    /**
     * 当前节点到终点的启发式距离(比如: 曼哈顿距离)
     * @return int
     */
    public function getHScore()
    {
        return $this->_hScore;
    }

    public function isVisited()
    {
        return $this->_isVisited;
    }

    public function isClosed()
    {
        return $this->_isClosed;
    }

    public function isBlock()
    {
        return $this->_isBlock;
    }

    /**
     * 该节点的父节点
     * @return Node
     */
    public function getPNode()
    {
        return $this->_pNode;
    }

    public function visit()
    {
        $this->_isVisited = true;
    }

    public function close()
    {
        $this->_isClosed = true;
    }

    /**
     * 设置该节点的父节点
     * @param Node $pNode
     */
    public function setPNode(Node $pNode)
    {
        $this->_pNode = $pNode;
    }

    /**
     * 设置是否是障碍物
     * @param bool $isBlock
     */
    public function setIsBlock($isBlock)
    {
        $this->_isBlock = $isBlock;
    }

    public function setFScore($score)
    {
        $this->_fScore = $score;
    }

    public function setGScore($score)
    {
        $this->_gScore = $score;
    }

    public function setHScore($score)
    {
        $this->_hScore = $score;
    }

    /**
     * 把 Node 数组转为坐标点数组
     * @param array $nodes
     * @return array
     */
    public static function toPoints(array $nodes)
    {
        $points = [];
        foreach ($nodes as $n) {
            $points[] = [$n->getX(), $n->getY()];
        }

        return $points;
    }

    public function toString()
    {
        return sprintf('(%d , %d)' . "\n", $this->getX(), $this->getY());
    }
}
