<?php
/**
 * Created by PhpStorm.
 * User: hikoqiu
 * Date: 2017/6/27
 */

namespace src\grid;

/**
 * 表格地图
 * Class Grid
 * @package src\algo\astar
 */
class Grid
{
    // 表格的最大 x, y
    private $_maxX, $_maxY;

    // x 轴方向的边的 cost, y 轴方向的边的 cost
    private $_xScore = 10;
    private $_yScore = 10;

    /**
     * 二维数组
     * @var array
     * e.g: [[Node1, Node2, Node3 ...], [Node4, Node5, Node6 ...], ...]
     */
    private $_grid = [];

    /**
     * 障碍物的点
     * @var array
     */
    private $_blocks = [];

    /**
     * Grid constructor.
     * @param $x
     * @param $y
     * @param array $blocks
     * $blocks e.g: [[x1,y1], [x2, y2], [x3, y3]...]
     */
    public function __construct($x, $y, $blocks = [])
    {
        $this->_maxX   = $x;
        $this->_maxY   = $y;
        $this->_blocks = $blocks;
        $this->_initGrid();
    }

    /**
     * 初始化表格
     */
    private function _initGrid()
    {
        if ($this->_maxX <= 0 || $this->_maxY <= 0) {
            throw new \Exception('表格参数错误, x_num: ' . $this->_maxX . ', y_num: ' . $this->_maxX, 1002);
        }

        for ($x = 0; $x < $this->_maxX; $x++) {
            for ($y = 0; $y < $this->_maxY; $y++) {
                $this->_grid[$x][$y] = new Node($x, $y);
            }
        }

        $this->setBlocks($this->_blocks);
    }

    /**
     * 设置障碍物
     * @param array $blocks
     * $blocks e.g: [[x1,y1], [x2, y2], [x3, y3]...]
     */
    public function setBlocks(array $blocks)
    {
        foreach ($blocks as $b) {
            if (count($b) != 2) { // [x, y]
                throw new \Exception('无效障碍点: [' . implode(',', $blocks) . ']');
            }

            $blockNode = $this->_grid[intval($b[0])][intval($b[1])];
            if ($blockNode instanceof Node) {
                $blockNode->setIsBlock(true);
            }
        }
    }

    public function setXScore($score)
    {
        $this->_xScore = intval($score);
    }

    public function setYScore($score)
    {
        $this->_yScore = intval($score);
    }

    /**
     *  通过坐标点获取 Node
     * @param $x
     * @param $y
     * @return Node
     */
    public function getNodeByXY($x, $y)
    {
        if (isset($this->_grid[$x][$y])) {
            return $this->_grid[$x][$y];
        }

        return false;
    }

    /**
     * 通过坐标点数组, 获取各自对应的节点
     * @param array $points
     * @return array
     */
    public function getNodesByPoints(array $points)
    {
        $nodes = [];
        foreach ($points as $p) {
            $tmpNode = $this->getNodeByXY(intval($p[0]), intval($p[1]));
            $tmpNode && $nodes[] = $tmpNode;
        }

        return $nodes;
    }

    /**
     * 获取相邻节点
     * @param Node $node
     * @param bool|false $diagonal // 是否取斜角的四个点
     * @return array
     */
    public function getAdjacentNodes(Node $node, $diagonal = false)
    {
        $nodeX = $node->getX();
        $nodeY = $node->getY();

        $targetPoints = [
            [$nodeX, $nodeY + 1],
            [$nodeX, $nodeY - 1],
            [$nodeX - 1, $nodeY],
            [$nodeX + 1, $nodeY],
        ];

        if ($diagonal) {
            $targetPoints[] = [$nodeX - 1, $nodeY - 1];
            $targetPoints[] = [$nodeX - 1, $nodeY + 1];
            $targetPoints[] = [$nodeX + 1, $nodeY - 1];
            $targetPoints[] = [$nodeX + 1, $nodeY + 1];
        }

        return $this->getNodesByPoints($targetPoints);
    }

    /**
     * 获取需要参与判断 f(n) 的节点
     * @param Node $node
     * @param bool|false $diagonal
     * @return array
     */
    public function getJudgeAdjacentNodes(Node $node, $diagonal = false)
    {
        $list = $this->getAdjacentNodes($node, $diagonal);
        foreach ($list as $i => $node) {
            if ($this->_needFilter($node)) {
                unset($list[$i]);
                continue;
            }
        }

        return array_values($list);
    }

    /**
     * 判断是否需要过滤掉节点, 不参与判断 f(n)
     * 备注: 也就是排除已经关闭和障碍节点
     * @param Node $node
     * @return bool
     */
    private function _needFilter(Node $node)
    {
        return $node->isBlock() || $node->isClosed();
    }

    /**
     * 计算两个相邻节点间的分数(或者说消耗)
     * 相邻: 可以是水平或垂直, 也可以是斜线
     * @param Node $node1
     * @param Node $node2
     * @return int
     */
    public function calculateScore(Node $node1, Node $node2)
    {
        $intervalX = abs($node1->getX() - $node2->getX()) * $this->_xScore;
        $intervalY = abs($node1->getY() - $node2->getY()) * $this->_yScore;
        if ($node1->getX() == $node2->getX() || $node1->getY() == $node2->getY()) {
            return $intervalX + $intervalY;
        }

        return intval(sqrt(pow($intervalX, 2) + pow($intervalY, 2)));
    }

    /**
     * 把表格转成命令行的字符串
     * @return string
     * @throws \Exception
     */
    public function toString()
    {
        $gridStr = '';
        for ($y = 0; $y < $this->_maxY; $y++) {
            $rowStr = '| ';
            for ($x = 0; $x < $this->_maxX; $x++) {
                $node = $this->_grid[$x][$y];
                if (!($node instanceof Node)) {
                    throw new \Exception('无效节点: ' . $node);
                }

                if ($node->isBlock()) {
                    $rowStr .= '[*****]';
                } else {
                    $rowStr .= sprintf('(%d , %d)', $node->getX(), $node->getY());
                }
                $rowStr .= ' | ';
            }

            $gridStr .= $rowStr . "\n";
        }

        return $gridStr;
    }

}